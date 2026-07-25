<?php
/**
 * Carrier Tracking Domain Layer
 * -----------------------------
 * Bridges external carrier integrations with the internal tracking store.
 *
 * Responsibilities:
 *  - Link a shipment to an external carrier tracking number.
 *  - Ingest raw carrier payloads (from webhooks or manual sync) into
 *    carrier_tracking_events, map provider status -> canonical status,
 *    and append to tracking_history via addTrackingEvent().
 *  - Deduplicate carrier events so re-delivery of the same payload does
 *    not create duplicate timeline entries.
 *
 * All database access uses prepared statements.
 */

require_once __DIR__ . '/shipment_status.php';
require_once __DIR__ . '/tracking.php';

/**
 * Ensure carrier_tracking_events table exists.
 * Safe to call on every request.
 */
function ensureCarrierTrackingTable(PDO $db) {
    try {
        $db->query("SELECT 1 FROM carrier_tracking_events LIMIT 1");
    } catch (Exception $e) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `carrier_tracking_events` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `shipment_id` int(11) NOT NULL,
                `integration_id` int(11) NOT NULL,
                `carrier_status` varchar(100) NOT NULL,
                `canonical_status` varchar(50) NOT NULL,
                `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`raw_payload`)),
                `location` varchar(255) DEFAULT NULL,
                `event_timestamp` datetime DEFAULT NULL,
                `processed` tinyint(1) DEFAULT 0,
                `processed_at` timestamp NULL DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `shipment_id` (`shipment_id`),
                KEY `integration_id` (`integration_id`),
                KEY `processed` (`processed`),
                KEY `event_timestamp` (`event_timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

/**
 * Ensure carrier linkage columns exist on shipments.
 * Safe to call on every request.
 */
function ensureCarrierTrackingColumns(PDO $db) {
    $cols = [
        'carrier_tracking_number' => 'varchar(120) DEFAULT NULL AFTER `tracking_number`',
        'carrier_name'            => 'varchar(100) DEFAULT NULL AFTER `carrier_tracking_number`',
        'carrier_integration_id'  => 'int(11) DEFAULT NULL AFTER `carrier_name`',
        'last_carrier_sync_at'    => 'timestamp NULL DEFAULT NULL AFTER `carrier_integration_id`',
    ];
    try {
        $countRow = $db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
        if ((int)$countRow > 10000) {
            return;
        }
    } catch (Exception $e) {
        return;
    }
    foreach ($cols as $col => $def) {
        try {
            $db->query("SELECT `$col` FROM shipments LIMIT 1");
        } catch (Exception $e) {
            try {
                $db->exec("ALTER TABLE shipments ADD COLUMN `$col` $def");
            } catch (Exception $e2) { /* ignore */ }
        }
    }

    // Indexes
    try {
        $db->query("SELECT 1 FROM shipments LIMIT 1");
        $db->exec("ALTER TABLE shipments ADD UNIQUE KEY `uq_carrier_tracking` (`carrier_tracking_number`, `carrier_integration_id`)");
    } catch (Exception $e) { /* ignore if exists */ }
    try {
        $db->exec("ALTER TABLE shipments ADD KEY `idx_carrier_sync` (`carrier_integration_id`, `last_carrier_sync_at`)");
    } catch (Exception $e) { /* ignore if exists */ }
    try {
        $db->exec("ALTER TABLE shipments ADD KEY `idx_carrier_tn` (`carrier_tracking_number`)");
    } catch (Exception $e) { /* ignore if exists */ }
}

/**
 * Link a shipment to an external carrier tracking number.
 *
 * @param PDO    $db
 * @param int    $shipmentId
 * @param string $carrierTrackingNumber
 * @param int    $integrationId
 * @param string $carrierName  Human-readable carrier name (e.g. "DHL", "FedEx")
 * @return array ['success' => bool, 'error' => string|null]
 */
function linkCarrierTracking(PDO $db, $shipmentId, $carrierTrackingNumber, $integrationId, $carrierName = '') {
    ensureCarrierTrackingColumns($db);
    $shipmentId = (int)$shipmentId;
    $integrationId = (int)$integrationId;
    $carrierTrackingNumber = trim((string)$carrierTrackingNumber);

    if ($carrierTrackingNumber === '') {
        return ['success' => false, 'error' => 'Carrier tracking number is required.'];
    }
    if ($shipmentId <= 0) {
        return ['success' => false, 'error' => 'Invalid shipment ID.'];
    }
    if ($integrationId <= 0) {
        return ['success' => false, 'error' => 'Invalid integration ID.'];
    }

    // Verify shipment exists.
    $stmt = $db->prepare("SELECT id, tracking_number FROM shipments WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $shipmentId]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$shipment) {
        return ['success' => false, 'error' => 'Shipment not found.'];
    }

    // Verify integration exists and is a tracking integration.
    $stmt = $db->prepare("SELECT id, provider, integration_type FROM api_integrations WHERE id = :id AND is_active = 1 LIMIT 1");
    $stmt->execute([':id' => $integrationId]);
    $integration = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$integration) {
        return ['success' => false, 'error' => 'Integration not found or inactive.'];
    }

    $carrierName = $carrierName ?: $integration['provider'];

    $dupStmt = $db->prepare("
        SELECT id, tracking_number FROM shipments
        WHERE carrier_tracking_number = :ctn AND carrier_integration_id = :iid AND id != :id
        LIMIT 1
    ");
    $dupStmt->execute([':ctn' => $carrierTrackingNumber, ':iid' => $integrationId, ':id' => $shipmentId]);
    $dup = $dupStmt->fetch(PDO::FETCH_ASSOC);
    if ($dup) {
        return ['success' => false, 'error' => "Carrier tracking number already linked to shipment {$dup['tracking_number']}."];
    }

    try {
        $db->prepare("
            UPDATE shipments
            SET carrier_tracking_number = :ctn,
                carrier_name = :cname,
                carrier_integration_id = :iid,
                updated_at = NOW()
            WHERE id = :id
        ")->execute([
            ':ctn'  => $carrierTrackingNumber,
            ':cname' => $carrierName,
            ':iid'  => $integrationId,
            ':id'   => $shipmentId,
        ]);

        return [
            'success' => true,
            'tracking_number' => $shipment['tracking_number'],
            'carrier_tracking_number' => $carrierTrackingNumber,
            'carrier_name' => $carrierName,
        ];
    } catch (Exception $e) {
        // Unique key violation or other DB error.
        return ['success' => false, 'error' => 'Failed to link carrier tracking: ' . $e->getMessage()];
    }
}

/**
 * Unlink carrier tracking from a shipment (set fields to NULL).
 *
 * @return array ['success' => bool]
 */
function unlinkCarrierTracking(PDO $db, $shipmentId) {
    $shipmentId = (int)$shipmentId;
    if ($shipmentId <= 0) {
        return ['success' => false, 'error' => 'Invalid shipment ID.'];
    }
    try {
        $db->prepare("
            UPDATE shipments
            SET carrier_tracking_number = NULL,
                carrier_name = NULL,
                carrier_integration_id = NULL,
                last_carrier_sync_at = NULL,
                updated_at = NOW()
            WHERE id = :id
        ")->execute([':id' => $shipmentId]);
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Fetch raw carrier events for a shipment.
 *
 * @return array<int, array>
 */
function fetchCarrierEvents(PDO $db, $shipmentId, $limit = 100) {
    $shipmentId = (int)$shipmentId;
    $limit = (int)$limit;
    $stmt = $db->prepare("
        SELECT cte.*, ai.provider, ai.integration_name
        FROM carrier_tracking_events cte
        LEFT JOIN api_integrations ai ON ai.id = cte.integration_id
        WHERE cte.shipment_id = :sid
        ORDER BY cte.event_timestamp DESC, cte.created_at DESC
        LIMIT $limit
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if a carrier event is a duplicate (same shipment + carrier_status + event_timestamp).
 *
 * @return bool
 */
function isDuplicateCarrierEvent(PDO $db, $shipmentId, $carrierStatus, $eventTimestamp) {
    $shipmentId = (int)$shipmentId;
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM carrier_tracking_events
        WHERE shipment_id = :sid
          AND carrier_status = :status
          AND event_timestamp = :ts
    ");
    $stmt->execute([
        ':sid'   => $shipmentId,
        ':status' => $carrierStatus,
        ':ts'    => $eventTimestamp,
    ]);
    return ((int)$stmt->fetchColumn()) > 0;
}

/**
 * Store a raw carrier event in the audit table.
 *
 * @return int|false New event ID or false on failure.
 */
function storeCarrierEvent(PDO $db, $shipmentId, $integrationId, $carrierStatus, $canonicalStatus, $rawPayload, $location = null, $eventTimestamp = null) {
    ensureCarrierTrackingTable($db);
    $shipmentId = (int)$shipmentId;
    $integrationId = (int)$integrationId;

    $payloadJson = is_string($rawPayload) ? $rawPayload : json_encode($rawPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ts = $eventTimestamp ?: date('Y-m-d H:i:s');

    // Deduplication.
    if (isDuplicateCarrierEvent($db, $shipmentId, $carrierStatus, $ts)) {
        return false;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO carrier_tracking_events
                (shipment_id, integration_id, carrier_status, canonical_status, raw_payload, location, event_timestamp)
            VALUES
                (:shipment_id, :integration_id, :carrier_status, :canonical_status, :raw_payload, :location, :event_timestamp)
        ");
        $stmt->execute([
            ':shipment_id'    => $shipmentId,
            ':integration_id' => $integrationId,
            ':carrier_status' => $carrierStatus,
            ':canonical_status' => $canonicalStatus,
            ':raw_payload'    => $payloadJson,
            ':location'       => $location,
            ':event_timestamp'=> $ts,
        ]);
        return (int)$db->lastInsertId();
    } catch (Exception $e) {
        getLogger()->error('storeCarrierEvent failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Process a carrier webhook payload: validate, map status, store, and
 * append to tracking_history + shipment_status_history_v2.
 *
 * @param PDO    $db
 * @param int    $integrationId
 * @param string $trackingNumber   Carrier's tracking number (from payload or header)
 * @param array  $payload          Decoded carrier JSON payload
 * @param string $providerStatus   Raw status string from carrier
 * @param string $location         Optional location from carrier
 * @param string $eventTimestamp   Optional ISO-8601 timestamp from carrier
 * @return array ['success' => bool, 'event_id' => int|null, 'error' => string|null]
 */
function processCarrierEvent(PDO $db, $integrationId, $trackingNumber, array $payload, $providerStatus, $location = null, $eventTimestamp = null) {
    ensureCarrierTrackingTable($db);
    ensureTrackingHistory($db);

    $integrationId = (int)$integrationId;
    $trackingNumber = trim((string)$trackingNumber);

    if ($trackingNumber === '') {
        return ['success' => false, 'error' => 'Tracking number is required.', 'event_id' => null];
    }

    $canonicalStatus = canonicalizeCarrierStatus($db, $integrationId, $providerStatus);
    if (!isValidStatus($canonicalStatus)) {
        $canonicalStatus = 'in_transit';
    }

    $stmt = $db->prepare("
        SELECT id, tracking_number, status, customer_id
        FROM shipments
        WHERE carrier_tracking_number = :ctn AND carrier_integration_id = :iid
        LIMIT 1
    ");
    $stmt->execute([':ctn' => $trackingNumber, ':iid' => $integrationId]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shipment) {
        $stmt = $db->prepare("
            SELECT id, tracking_number, status, customer_id
            FROM shipments
            WHERE tracking_number = :tn
            LIMIT 1
        ");
        $stmt->execute([':tn' => $trackingNumber]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$shipment) {
        return ['success' => false, 'error' => 'No shipment linked to carrier tracking number.', 'event_id' => null];
    }

    $shipmentId = (int)$shipment['id'];

    if (function_exists('insertCarrierEvent')) {
        $dedupResult = insertCarrierEvent($db, [
            'shipment_id'            => $shipmentId,
            'integration_id'         => $integrationId,
            'carrier_tracking_number' => $trackingNumber,
            'carrier_status'         => $providerStatus,
            'canonical_status'       => $canonicalStatus,
            'raw_payload'            => $payload,
            'location'               => $location,
            'event_timestamp'        => $eventTimestamp,
        ]);
        if ($dedupResult['duplicate']) {
            return ['success' => false, 'error' => 'Duplicate or failed to store carrier event.', 'event_id' => $dedupResult['event_id']];
        }
        $rawEventId = $dedupResult['event_id'];
    } else {
        $rawEventId = storeCarrierEvent($db, $shipmentId, $integrationId, $providerStatus, $canonicalStatus, $payload, $location, $eventTimestamp);
        if (!$rawEventId) {
            return ['success' => false, 'error' => 'Duplicate or failed to store carrier event.', 'event_id' => null];
        }
    }

    // Append to public tracking store (append-only, never blocks webhook).
    $description = 'Carrier update: ' . $providerStatus . ($location ? ' @ ' . $location : '');
    try {
        $eventId = addTrackingEvent(
            $db,
            $shipmentId,
            $shipment['tracking_number'],
            $canonicalStatus,
            $location ?: '',
            $description,
            'Carrier Sync',
            null,
            null
        );
    } catch (Exception $e) {
        getLogger()->error('processCarrierEvent: addTrackingEvent failed: ' . $e->getMessage());
        $eventId = null;
    }

    // Update shipment status if this event is newer than the last tracking_history entry.
    try {
        $stmt = $db->prepare("
            SELECT event_timestamp FROM tracking_history
            WHERE shipment_id = :sid
            ORDER BY event_timestamp DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':sid' => $shipmentId]);
        $lastTs = $stmt->fetchColumn();

        $newTs = $eventTimestamp ?: date('Y-m-d H:i:s');
        if (!$lastTs || $newTs > $lastTs) {
            $db->prepare("
                UPDATE shipments SET status = :status, updated_at = NOW()
                WHERE id = :id
            ")->execute([':status' => $canonicalStatus, ':id' => $shipmentId]);

            // Mirror in admin history store.
            try {
                $db->prepare("
                    INSERT INTO shipment_status_history_v2
                        (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                    VALUES
                        (:shipment_id, :status, :ts, :location, :remarks, :by)
                ")->execute([
                    ':shipment_id' => $shipmentId,
                    ':status'      => $canonicalStatus,
                    ':ts'          => $newTs,
                    ':location'    => $location ?: null,
                    ':remarks'     => $description,
                    ':by'          => 'Carrier Sync',
                ]);
            } catch (Exception $e) {
                getLogger()->warning('processCarrierEvent: shipment_status_history_v2 mirror failed: ' . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        getLogger()->warning('processCarrierEvent: status update check failed: ' . $e->getMessage());
    }

    // Mark raw event as processed.
    try {
        $db->prepare("
            UPDATE carrier_tracking_events
            SET processed = 1, processed_at = NOW()
            WHERE id = :id
        ")->execute([':id' => $rawEventId]);
    } catch (Exception $e) {
        getLogger()->warning('processCarrierEvent: processed_at update failed: ' . $e->getMessage());
    }

    return [
        'success'    => true,
        'event_id'   => $rawEventId,
        'tracking_number' => $shipment['tracking_number'],
        'status'     => $canonicalStatus,
    ];
}

/**
 * Trigger a manual carrier sync for a shipment.
 * Loads the integration config, fetches latest tracking from the carrier,
 * and processes each event.
 *
 * @param PDO    $db
 * @param int    $shipmentId
 * @return array ['success' => bool, 'synced' => int, 'error' => string|null]
 */
function syncCarrierTracking(PDO $db, $shipmentId) {
    ensureCarrierTrackingTable($db);
    ensureTrackingHistory($db);

    $shipmentId = (int)$shipmentId;
    if ($shipmentId <= 0) {
        return ['success' => false, 'synced' => 0, 'error' => 'Invalid shipment ID.'];
    }

    $stmt = $db->prepare("
        SELECT id, tracking_number, carrier_tracking_number, carrier_integration_id
        FROM shipments
        WHERE id = :id LIMIT 1
    ");
    $stmt->execute([':id' => $shipmentId]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$shipment) {
        return ['success' => false, 'synced' => 0, 'error' => 'Shipment not found.'];
    }

    $carrierTn = $shipment['carrier_tracking_number'] ?? '';
    $integrationId = $shipment['carrier_integration_id'] ?? 0;
    if (!$carrierTn || !$integrationId) {
        return ['success' => false, 'synced' => 0, 'error' => 'No carrier tracking number linked.'];
    }

    // Load integration client.
    $client = null;
    try {
        require_once __DIR__ . '/integrations/IntegrationManager.php';
        $client = IntegrationClient::load($db, $integrationId);
    } catch (Exception $e) {
        getLogger()->error('syncCarrierTracking: IntegrationClient load failed: ' . $e->getMessage());
    }
    if (!$client) {
        return ['success' => false, 'synced' => 0, 'error' => 'Failed to load carrier integration.'];
    }

    $adapter = null;
    try {
        $adapter = IntegrationManager::adapter($db, $client->integration);
    } catch (Exception $e) {
        getLogger()->error('syncCarrierTracking: adapter load failed: ' . $e->getMessage());
    }
    if (!$adapter || !($adapter instanceof CarrierAdapter)) {
        return ['success' => false, 'synced' => 0, 'error' => 'Integration does not support carrier tracking.'];
    }

    // Fetch tracking events from carrier.
    $events = [];
    try {
        $events = $adapter->fetchTrackingEvents($carrierTn);
    } catch (Exception $e) {
        getLogger()->error('syncCarrierTracking: fetchTrackingEvents failed: ' . $e->getMessage());
        return ['success' => false, 'synced' => 0, 'error' => 'Carrier fetch failed: ' . $e->getMessage()];
    }

    if (empty($events)) {
        return ['success' => true, 'synced' => 0, 'error' => null, 'message' => 'No new events from carrier.'];
    }

    $synced = 0;
    foreach ($events as $evt) {
        $result = processCarrierEvent(
            $db,
            $integrationId,
            $carrierTn,
            $evt['payload'] ?? [],
            $evt['status'] ?? $evt['carrier_status'] ?? 'unknown',
            $evt['location'] ?? null,
            $evt['timestamp'] ?? $evt['event_timestamp'] ?? null
        );
        if ($result['success']) {
            $synced++;
        }
    }

    // Update last sync timestamp.
    try {
        $db->prepare("
            UPDATE shipments SET last_carrier_sync_at = NOW() WHERE id = :id
        ")->execute([':id' => $shipmentId]);
    } catch (Exception $e) { /* non-fatal */ }

    return ['success' => true, 'synced' => $synced, 'error' => null];
}

/**
 * Get carrier tracking info for a shipment (for display in admin UI).
 *
 * @return array|null
 */
function getCarrierTrackingInfo(PDO $db, $shipmentId) {
    $shipmentId = (int)$shipmentId;
    $stmt = $db->prepare("
        SELECT s.carrier_tracking_number, s.carrier_name, s.carrier_integration_id,
               s.last_carrier_sync_at,
               ai.provider, ai.integration_name, ai.integration_type
        FROM shipments s
        LEFT JOIN api_integrations ai ON ai.id = s.carrier_integration_id
        WHERE s.id = :id LIMIT 1
    ");
    $stmt->execute([':id' => $shipmentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
