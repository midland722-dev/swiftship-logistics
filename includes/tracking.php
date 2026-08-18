<?php
/**
 * Shared Tracking Domain Layer
 * ----------------------------
 * Single source of truth for the customer-facing shipment tracking feature.
 *
 * Design notes:
 *  - The canonical public tracking store is the `tracking_history` table
 *    (defined in the migration). For backwards compatibility with shipments
 *    created before this table existed, public lookups also fall back to the
 *    legacy `tracking_logs` table, and the two are merged/deduplicated by time.
 *  - This module NEVER exposes customer PII (name, email, phone, address,
 *    payment details, internal notes). Only route-level and status data is
 *    returned to the public page / API.
 *  - All database access uses prepared statements.
 */

require_once __DIR__ . '/shipment_status.php';
require_once __DIR__ . '/carrier_tracking.php';

/**
 * Ensure the `tracking_history` table has any missing columns and seed from
 * the legacy `tracking_logs` table (only when empty) so historical shipments
 * are immediately visible on the customer page.
 *
 * The table itself is created by migration 0001_tracking_history.sql.
 */
function ensureTrackingHistory(PDO $db) {
    $cols = ['customs_procedure', 'event_notes', 'transit_location'];
    foreach ($cols as $c) {
        try { $db->query("SELECT `$c` FROM `tracking_history` LIMIT 1"); }
        catch (Exception $e) {
            try {
                if ($c === 'event_notes') {
                    $db->exec("ALTER TABLE `tracking_history` ADD COLUMN `$c` text DEFAULT NULL");
                } elseif ($c === 'transit_location') {
                    $db->exec("ALTER TABLE `tracking_history` ADD COLUMN `$c` varchar(255) DEFAULT NULL AFTER `location`");
                } else {
                    $db->exec("ALTER TABLE `tracking_history` ADD COLUMN `$c` varchar(255) DEFAULT NULL");
                }
            } catch (Exception $e2) {}
        }
    }

    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `tracking_history`")->fetchColumn();
        if ($count === 0) {
            $db->exec("
                INSERT INTO `tracking_history`
                    (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
                SELECT COALESCE(shipment_id, 0), tracking_number, status, location, description, occurred_at, 'System', created_at
                FROM `tracking_logs`
                WHERE is_public = 1 AND tracking_number != ''
            ");
        }
    } catch (Exception $e) {
        // Table may not exist yet or be inaccessible; seed will be retried on next call.
    }
}

/**
 * Public tracking URL for a shipment (used in emails, QR codes, webhooks).
 */
function trackingUrl($trackingNumber) {
    $baseUrl = rtrim(getenv('APP_URL') ?: '', '/');
    return $baseUrl . '/track.php?id=' . urlencode($trackingNumber);
}

/**
 * Map a status code to a notifications.type enum value (best effort).
 */
function trackingNotificationType($status) {
    $map = [
        'pending' => 'shipment_created',
        'processing' => 'shipment_created',
        'picked_up' => 'picked_up',
        'at_warehouse' => 'shipment_created',
        'in_transit' => 'in_transit',
        'at_hub' => 'in_transit',
        'out_for_delivery' => 'out_for_delivery',
        'delivered' => 'delivered',
        'returned' => 'cancelled',
        'cancelled' => 'cancelled',
    ];
    return $map[strtolower($status)] ?? 'system';
}

/**
 * Notify the customer about a tracking event (asynchronous, resilient).
 *  - In-app notification is written synchronously (cheap, no external call).
 *  - Email + webhook delivery are enqueued as jobs so they never block the
 *    tracking write and are retried automatically by the worker.
 */
function notifyTrackingEvent(PDO $db, $shipmentId, $trackingNumber, $status, $location, $description) {
    try {
        require_once __DIR__ . '/queue.php';
        require_once __DIR__ . '/mailer.php';

        $row = $db->prepare("
            SELECT s.tracking_number, s.customer_id, u.email, u.name
            FROM shipments s
            LEFT JOIN users u ON s.customer_id = u.id
            WHERE s.id = :id
            LIMIT 1
        ");
        $row->execute([':id' => $shipmentId]);
        $info = $row->fetch(PDO::FETCH_ASSOC);
        if (!$info) {
            return;
        }

        $label = statusLabel($status);
        $url = trackingUrl($trackingNumber);

        // In-app notification (synchronous, only when a customer is linked).
        if (!empty($info['customer_id'])) {
            try {
                $db->prepare("
                    INSERT INTO notifications (user_id, type, title, message, action_url, icon, created_at)
                    VALUES (:uid, :type, :title, :message, :url, 'box', NOW())
                ")->execute([
                    ':uid' => $info['customer_id'],
                    ':type' => trackingNotificationType($status),
                    ':title' => 'Shipment ' . $trackingNumber . ' — ' . $label,
                    ':message' => ($location ? 'Location: ' . $location . '. ' : '') . ($description ?: $label),
                    ':url' => rtrim(getenv('APP_URL') ?: '', '/') . '/track.php?cons_no=' . $trackingNumber,
                ]);
            } catch (Exception $e) {
                getLogger()->warning('In-app notification insert failed: ' . $e->getMessage());
            }
        }

        // Enqueue email job (delivered by the worker).
        if (!empty($info['email'])) {
            enqueueJob($db, 'email', [
                'to'          => $info['email'],
                'name'        => $info['name'] ?? 'Customer',
                'tracking_number' => $trackingNumber,
                'status'      => $status,
                'location'    => $location,
                'description' => $description,
                'url'         => $url,
            ]);
        }

        // Enqueue webhook dispatch job.
        enqueueJob($db, 'webhook', [
            'event'          => 'tracking.' . $status,
            'shipment_id'    => $shipmentId,
            'tracking_number' => $trackingNumber,
            'status'         => $status,
            'location'       => $location,
            'description'    => $description,
            'updated_by'     => 'System',
            'event_timestamp'=> date('c'),
        ]);
    } catch (Exception $e) {
        // Never break the tracking write.
        getLogger()->error('notifyTrackingEvent failed: ' . $e->getMessage());
    }
}

/**
 * Idempotently create the webhook_subscriptions table (used for outbound
 * tracking events to 3rd-party / multi-courier integrations).
 */
function ensureWebhookTable(PDO $db) {
    try {
        $db->query("SELECT 1 FROM webhook_subscriptions LIMIT 1");
    } catch (Exception $e) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS webhook_subscriptions (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL,
                target_url varchar(500) NOT NULL,
                event_filter varchar(100) DEFAULT '*',
                secret varchar(255) DEFAULT NULL,
                is_active tinyint(1) DEFAULT 1,
                created_at timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (id),
                KEY is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

/**
 * Dispatch a tracking event to all active webhook subscribers (fire-and-forget,
 * short timeout, best-effort). Each delivery is recorded in webhook_events.
 */
function dispatchTrackingWebhooks(PDO $db, array $payload) {
    try {
        ensureWebhookTable($db);
        $event = $payload['event'] ?? '';
        $subs = $db->prepare("
            SELECT * FROM webhook_subscriptions
            WHERE is_active = 1 AND (event_filter = '*' OR event_filter = :ev)
        ");
        $subs->execute([':ev' => $event]);
        $body = json_encode($payload);
        foreach ($subs->fetchAll(PDO::FETCH_ASSOC) as $sub) {
            $status = 'pending';
            $code = null;
            $resp = null;
            $err = null;
            $url = $sub['target_url'];
            $sig = $sub['secret'] ? hash_hmac('sha256', $body, $sub['secret']) : '';
            $vparts = parse_url($url);
            $vscheme = strtolower($vparts['scheme'] ?? '');
            $vhost = strtolower($vparts['host'] ?? '');
            $visLocal = $vhost === 'localhost' || $vhost === '127.0.0.1'
                || preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $vhost);
            $urlOk = ($vscheme === 'http' || $vscheme === 'https') && !$visLocal;
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" . ($sig ? "X-Signature: sha256=$sig\r\n" : ''),
                    'content' => $body,
                    'timeout' => 2,
                    'ignore_errors' => true,
                ],
            ]);
            if ($urlOk) {
                $result = @file_get_contents($url, false, $ctx);
                if ($result !== false) {
                    $status = 'sent';
                    $resp = substr($result, 0, 500);
                } else {
                    $status = 'failed';
                    $err = 'Delivery failed';
                }
                if (isset($http_response_header) && is_array($http_response_header)) {
                    foreach ($http_response_header as $h) {
                        if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $h, $m)) {
                            $code = (int)$m[1]; break;
                        }
                    }
                }
            } else {
                $status = 'failed';
                $err = 'Disallowed webhook target URL';
            }
            $db->prepare("
                INSERT INTO webhook_events (event_type, payload, target_url, status, response_code, response_body, error_message, created_at)
                VALUES (:ev, :payload, :url, :status, :code, :resp, :err, NOW())
            ")->execute([
                ':ev' => $event, ':payload' => $body, ':url' => $url,
                ':status' => $status, ':code' => $code, ':resp' => $resp, ':err' => $err,
            ]);
        }
    } catch (Exception $e) {
        getLogger()->warning('dispatchTrackingWebhooks failed: ' . $e->getMessage());
    }
}

if (!function_exists('validateTrackingUpdate')) {
    function validateTrackingUpdate($status, $transitLocation, $customsProcedure) {
        $errors = [];

        $allowedTransit = [
            'origin_facility', 'export_warehouse', 'departure_airport', 'departure_seaport',
            'in_transit', 'regional_transit_hub', 'arrival_airport_usa', 'arrival_seaport_usa',
            'us_customs', 'domestic_distribution', 'local_delivery', 'out_for_delivery_loc', 'delivered_loc'
        ];
        $allowedCustoms = [
            'shipment_info_received', 'export_doc_verified', 'export_customs_cleared',
            'international_transit', 'transit_processing', 'awaiting_customs',
            'customs_inspection', 'customs_cleared', 'released_domestic',
            'ready_for_delivery', 'final_delivery', 'delivery_completed'
        ];

        if ($transitLocation !== '' && !in_array($transitLocation, $allowedTransit, true)) {
            $errors[] = 'Invalid transit location.';
        }
        if ($customsProcedure !== '' && !in_array($customsProcedure, $allowedCustoms, true)) {
            $errors[] = 'Invalid customs procedure.';
        }

        $terminalCustoms = ['released_domestic', 'ready_for_delivery', 'final_delivery', 'delivery_completed', 'customs_cleared'];
        $earlyTransit = ['origin_facility', 'export_warehouse', 'departure_airport', 'departure_seaport', 'in_transit'];
        if (in_array($transitLocation, $earlyTransit, true) && in_array($customsProcedure, $terminalCustoms, true)) {
            $errors[] = 'Cannot set customs as completed while the parcel is still at an early transit location.';
        }

        $lateTransit = ['domestic_distribution', 'local_delivery', 'out_for_delivery_loc', 'delivered_loc'];
        $earlyCustoms = ['shipment_info_received', 'export_doc_verified', 'export_customs_cleared', 'international_transit', 'transit_processing', 'awaiting_customs'];
        if (in_array($transitLocation, $lateTransit, true) && in_array($customsProcedure, $earlyCustoms, true)) {
            $errors[] = 'Cannot set an early customs procedure while the parcel is already at a domestic or delivery stage.';
        }

        return $errors;
    }
}

/**
 * Append a new tracking event. Never updates or overwrites previous events.
 *
 * @param int    $shipmentId
 * @param string $trackingNumber
 * @param string $status   DB status code
 * @param string $location Free-text location
 * @param string $description
 * @param string $updatedBy 'System' or admin identifier
 * @param float|null $lat
 * @param float|null $lng
 * @param string|null $transitLocation
 * @param string|null $customsProcedure
 * @return int New event id
 */
function addTrackingEvent(PDO $db, $shipmentId, $trackingNumber, $status, $location, $description, $updatedBy = 'System', $lat = null, $lng = null, $transitLocation = null, $customsProcedure = null) {
    $stmt = $db->prepare("
        INSERT INTO `tracking_history`
            (shipment_id, tracking_number, status, location, description, latitude, longitude, event_timestamp, updated_by, created_at, transit_location, customs_procedure)
        VALUES
            (:shipment_id, :tracking_number, :status, :location, :description, :lat, :lng, NOW(), :updated_by, NOW(), :transit_location, :customs_procedure)
    ");
    $stmt->execute([
        ':shipment_id'    => (int)$shipmentId,
        ':tracking_number'=> $trackingNumber,
        ':status'         => $status,
        ':location'       => $location ?: null,
        ':description'    => $description ?: null,
        ':lat'            => $lat,
        ':lng'            => $lng,
        ':updated_by'     => $updatedBy,
        ':transit_location' => $transitLocation ?: null,
        ':customs_procedure' => $customsProcedure ?: null,
    ]);
    $eventId = (int)$db->lastInsertId();

    // Notify the customer (in-app now; email + webhook enqueued as jobs).
    // Best-effort and never blocks the tracking write.
    notifyTrackingEvent($db, $shipmentId, $trackingNumber, $status, $location, $description);

    return $eventId;
}

/**
 * Derive a human, location-aware label for a tracking event based on the
 * status and the shipment's route. This lets the customer timeline show
 * movement FROM location TO location without requiring the operator to
 * type a location for every status change. The explicit-location override
 * (via the API / future dashboard field) always wins when provided.
 */
function deriveTrackingLocation($status, $shipment = []) {
    if (!is_array($shipment)) {
        $shipment = [];
    }
    $status = strtolower((string)$status);
    $origin = trim((string)($shipment['origin_city'] ?? $shipment['sender_city'] ?? ''));
    $dest = trim((string)($shipment['destination_city'] ?? $shipment['receiver_city'] ?? ''));

    switch ($status) {
        case 'pending':
        case 'processing':
            return $origin !== '' ? $origin . ' (Order Received)' : 'Order Received';
        case 'picked_up':
            return $origin !== '' ? $origin . ' — Picked Up' : 'Picked Up';
        case 'at_warehouse':
            return $origin !== '' ? $origin . ' Sorting Center' : 'Sorting Center';
        case 'in_transit':
            return $origin !== '' && $dest !== '' && $origin !== $dest
                ? 'In Transit: ' . $origin . ' → ' . $dest
                : 'In Transit';
        case 'at_hub':
            return $dest !== '' ? $dest . ' Destination Hub' : 'Destination Hub';
        case 'customs_inspection':
        case 'customs_clearance':
        case 'customs_delayed':
        case 'customs_seized':
        case 'held':
            return 'Customs Clearance';
        case 'out_for_delivery':
            return $dest !== '' ? 'Out for Delivery — ' . $dest : 'Out for Delivery';
        case 'delivered':
            return $dest !== '' ? 'Delivered — ' . $dest : 'Delivered';
        case 'returned':
            return $origin !== '' ? 'Returned to ' . $origin : 'Returned to Sender';
        case 'cancelled':
            return 'Cancelled';
        default:
            return $origin !== '' ? $origin : '';
    }
}

/**
 * Validate and normalize a tracking number from user input.
 * Returns the cleaned value or false if invalid.
 */
function normalizeTrackingNumber($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') {
        return false;
    }
    // Remove spaces (users often paste with spaces) and normalize case.
    $raw = strtoupper(str_replace(' ', '', $raw));
    if (!preg_match('/^[A-Za-z0-9\-]{4,64}$/', $raw)) {
        return false;
    }
    return $raw;
}

/**
 * Compute the progress stepper state for a given status.
 * Returns an array of steps each with: key, label, icon, state
 * (one of 'complete' | 'current' | 'pending').
 */
function getTrackingProgress($status) {
    $workflow = unserialize(TRACKING_WORKFLOW);
    $terminal = unserialize(TRACKING_TERMINAL);
    $status = strtolower((string)$status);

    if (isset($terminal[$status])) {
        // Terminal outcome: mark the linear flow as far as delivered, then show terminal.
        foreach ($workflow as &$step) {
            $step['state'] = ($step['key'] === 'delivered') ? 'complete' : 'complete';
        }
        $workflow[] = [
            'key'    => $status,
            'label'  => $terminal[$status]['label'],
            'icon'   => $terminal[$status]['icon'],
            'state'  => 'current',
        ];
        return $workflow;
    }

    $currentIndex = null;
    foreach ($workflow as $i => $step) {
        if (in_array($status, $step['statuses'], true)) {
            $currentIndex = $i;
            break;
        }
    }
    // Unknown status -> treat as earliest step.
    if ($currentIndex === null) {
        $currentIndex = 0;
    }

    $result = [];
    foreach ($workflow as $i => $step) {
        $state = $i < $currentIndex ? 'complete' : ($i === $currentIndex ? 'current' : 'pending');
        $result[] = [
            'key'   => $step['key'],
            'label' => $step['label'],
            'icon'  => $step['icon'],
            'state' => $state,
        ];
    }
    return $result;
}

/**
 * Strip PII / internal fields from a shipment row before exposing publicly.
 */
function publicShipmentView(array $shipment) {
    $allowed = [
        'id', 'tracking_number', 'reference_number', 'status', 'service_type',
        'priority', 'origin_country', 'origin_city', 'destination_country',
        'destination_city', 'total_weight', 'pieces', 'estimated_delivery',
        'actual_delivery', 'created_at', 'updated_at', 'currency',
    ];
    $out = [];
    foreach ($allowed as $k) {
        if (array_key_exists($k, $shipment)) {
            $out[$k] = $shipment[$k];
        }
    }
    return $out;
}

/**
 * Geocode a city name to [lat, lng] using a small built-in table of common
 * hubs (no external dependency). Returns null when unknown so callers can
 * gracefully degrade. Extend this table as new routes are added; a future
 * upgrade can back this with the Nominatim API + disk cache.
 */
function geocodeCity($city) {
    $table = [
        'camden' => [39.9253, -75.1196], 'new york' => [40.7128, -74.0060],
        'los angeles' => [34.0522, -118.2437], 'chicago' => [41.8781, -87.6298],
        'houston' => [29.7604, -95.3698], 'phoenix' => [33.4484, -112.0740],
        'philadelphia' => [39.9526, -75.1652], 'san antonio' => [29.4241, -98.4936],
        'san diego' => [32.7157, -117.1611], 'dallas' => [32.7767, -96.7970],
        'san jose' => [37.3382, -121.8863], 'austin' => [30.2672, -97.7431],
        'jacksonville' => [30.3322, -81.6557], 'fort worth' => [32.7555, -97.3308],
        'columbus' => [39.9612, -82.9988], 'charlotte' => [35.2271, -80.8431],
        'indianapolis' => [39.7684, -86.1581], 'seattle' => [47.6062, -122.3321],
        'denver' => [39.7392, -104.9903], 'boston' => [42.3601, -71.0589],
        'miami' => [25.7617, -80.1918], 'atlanta' => [33.7490, -84.3880],
        'washington' => [38.9072, -77.0369], 'long beach' => [33.7701, -118.1937],
        'zurich' => [47.3769, 8.5417], 'frankfurt' => [50.1109, 8.6821],
        'london' => [51.5074, -0.1278], 'paris' => [48.8566, 2.3522],
        'berlin' => [52.5200, 13.4050], 'amsterdam' => [52.3676, 4.9041],
        'dubai' => [25.2048, 55.2708], 'tokyo' => [35.6762, 139.6503],
        'singapore' => [1.3521, 103.8198], 'sydney' => [-33.8688, 151.2093],
        'toronto' => [43.6532, -79.3832], 'mexico city' => [19.4326, -99.1332],
    ];
    $key = strtolower(trim((string)$city));
    return $table[$key] ?? null;
}

/**
 * Build an OpenStreetMap embed iframe showing the shipment route.
 * Uses origin/destination city coordinates (future-ready: event lat/lng can
 * be passed instead). Returns an <iframe> string, or '' when no coordinates
 * are available (caller then shows the placeholder).
 */
function trackingMapEmbed($originCity, $destCity) {
    $o = geocodeCity($originCity);
    $d = geocodeCity($destCity);
    if (!$o && !$d) {
        return '';
    }
    $points = array_filter([$o, $d]);
    $lats = array_column($points, 0);
    $lngs = array_column($points, 1);
    $minLat = min($lats); $maxLat = max($lats);
    $minLng = min($lngs); $maxLng = max($lngs);
    // Pad the bounding box so markers are not on the edge.
    $padLat = max(0.5, ($maxLat - $minLat) * 0.3);
    $padLng = max(0.5, ($maxLng - $minLng) * 0.3);
    $bbox = implode(',', [
        round($minLng - $padLng, 4), round($minLat - $padLat, 4),
        round($maxLng + $padLng, 4), round($maxLat + $padLat, 4),
    ]);
    $url = 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik';
    if ($o) { $url .= '&marker=' . $o[0] . ',' . $o[1]; }
    if ($d) { $url .= '&marker=' . $d[0] . ',' . $d[1]; }
    return '<iframe class="lx-map-iframe" loading="lazy" referrerpolicy="no-referrer-when-downgrade"'
        . ' src="' . htmlspecialchars($url, ENT_QUOTES) . '" title="Shipment route map"></iframe>';
}

/**
 * Validate a partner API key (optional, higher-tier access). Keys are stored
 * hashed; the provided raw key is hashed with a static pepper before compare.
 * Returns the api_keys row or null.
 */
function validateApiKey(PDO $db, $key) {
    if (empty($key)) {
        return null;
    }
    $hash = hash('sha256', 'shp::api::' . $key);
    try {
        $stmt = $db->prepare("
            SELECT * FROM api_keys
            WHERE key_hash = :h AND is_active = 1
              AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");
        $stmt->execute([':h' => $hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Record API usage for analytics / rate-billing. Best-effort.
 */
function logApiUsage(PDO $db, $keyRow, $endpoint, $method, $code, $ms) {
    try {
        $db->prepare("
            INSERT INTO api_usage_logs (api_key_id, user_id, endpoint, method, ip_address, user_agent, response_code, response_time_ms, created_at)
            VALUES (:k, :u, :e, :m, :ip, :ua, :c, :ms, NOW())
        ")->execute([
            ':k'  => $keyRow['id'] ?? null,
            ':u'  => $keyRow['user_id'] ?? null,
            ':e'  => $endpoint,
            ':m'  => $method,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ':c'  => (int)$code,
            ':ms' => (int)$ms,
        ]);
    } catch (Exception $e) { /* ignore */ }
}

/**
 * Predict an estimated delivery date when the shipment has none stored.
 * Uses the historical average transit time (created_at -> actual_delivery)
 * for delivered shipments as a baseline, defaulting to 7 days.
 */
function predictEta(PDO $db, array $shipment) {
    if (!empty($shipment['estimated_delivery'])) {
        return $shipment['estimated_delivery'];
    }
    try {
        $stmt = $db->prepare("
            SELECT AVG(DATEDIFF(actual_delivery, created_at))
            FROM shipments
            WHERE status = 'delivered' AND actual_delivery IS NOT NULL AND created_at IS NOT NULL
        ");
        $stmt->execute();
        $days = $stmt->fetchColumn();
        $days = ($days !== false && $days !== null) ? (float)$days : 7;
        if ($days <= 0) { $days = 7; }
        return date('Y-m-d', strtotime(($shipment['created_at'] ?? 'now') . " +{$days} days"));
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Fetch full public tracking data for a tracking number.
 * Merges `tracking_history` (canonical) with legacy `tracking_logs`.
 * Also appends carrier-sourced events from `carrier_tracking_events` when
 * the shipment is linked to an external carrier integration.
 *
 * @return array|null null when not found.
 */
function getPublicTracking(PDO $db, $trackingNumber) {
    $stmt = $db->prepare("
        SELECT s.* FROM shipments s
        WHERE s.tracking_number = :tn
        LIMIT 1
    ");
    $stmt->execute([':tn' => $trackingNumber]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$shipment) {
        return null;
    }

    $shipmentId = (int)$shipment['id'];

    // Canonical history.
    $hist = [];
    try {
        $stmt = $db->prepare("
            SELECT status, location, description, event_timestamp, updated_by, customs_procedure, event_notes, transit_location
            FROM tracking_history
            WHERE shipment_id = :sid
            ORDER BY event_timestamp ASC, id ASC
        ");
        $stmt->execute([':sid' => $shipmentId]);
        $hist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $hist = [];
    }

    // Fallback / merge from legacy logs when canonical history is empty.
    if (empty($hist)) {
        $stmt = $db->prepare("
            SELECT status, location, description, occurred_at as event_timestamp, 'System' as updated_by, NULL as customs_procedure, NULL as event_notes, NULL as transit_location
            FROM tracking_logs
            WHERE shipment_id = :sid AND is_public = 1
            ORDER BY occurred_at ASC, id ASC
            LIMIT 200
        ");
        $stmt->execute([':sid' => $shipmentId]);
        $hist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Append carrier-sourced events (deduplicated by timestamp + status).
    $carrierEvents = [];
    try {
        ensureCarrierTrackingTable($db);
        $stmt = $db->prepare("
            SELECT cte.carrier_status, cte.canonical_status, cte.location, cte.event_timestamp,
                   cte.raw_payload, ai.provider
            FROM carrier_tracking_events cte
            LEFT JOIN api_integrations ai ON ai.id = cte.integration_id
            WHERE cte.shipment_id = :sid
              AND cte.processed = 1
            ORDER BY cte.event_timestamp ASC, cte.id ASC
            LIMIT 200
        ");
        $stmt->execute([':sid' => $shipmentId]);
        $carrierEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { /* table may not exist yet */ }

    if (!empty($carrierEvents)) {
        $existingKeys = [];
        foreach ($hist as $h) {
            $ts = $h['event_timestamp'] ?? '';
            $st = $h['status'] ?? '';
            $existingKeys[$ts . '|' . $st] = true;
        }
        foreach ($carrierEvents as $ce) {
            $ts = $ce['event_timestamp'] ?? '';
            $st = $ce['canonical_status'] ?? '';
            if (isset($existingKeys[$ts . '|' . $st])) {
                continue;
            }
            $provider = $ce['provider'] ?? 'Carrier';
            $hist[] = [
                'status'          => $ce['canonical_status'],
                'location'        => $ce['location'] ?? '',
                'description'     => 'Carrier update (' . $provider . '): ' . $ce['carrier_status'],
                'event_timestamp' => $ts,
                'updated_by'      => $provider,
                'customs_procedure' => null,
                'event_notes'     => null,
                'transit_location' => null,
            ];
        }
        // Re-sort by timestamp.
        usort($hist, function ($a, $b) {
            return strcmp($a['event_timestamp'] ?? '', $b['event_timestamp'] ?? '');
        });
    }

    // Final safety net: synthesize a "Shipment Created" event so the customer
    // always sees a starting point with a location, even for edge cases.
    if (empty($hist)) {
        $hist = [[
            'status'          => $shipment['status'],
            'location'        => $shipment['origin_city'] ?? null,
            'description'     => 'Shipment created',
            'event_timestamp' => $shipment['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_by'      => 'System',
            'transit_location' => $shipment['transit_location'] ?? null,
            'customs_procedure' => $shipment['customs_procedure'] ?? null,
        ]];
    }

    // Current location = latest known location, else origin city.
    $currentLocation = $shipment['origin_city'] ?? '';
    foreach ($hist as $ev) {
        if (!empty($ev['location'])) {
            $currentLocation = $ev['location'];
        }
    }

    // Current transit location = latest known transit location from history or shipment.
    $currentTransitLocation = $shipment['transit_location'] ?? '';
    foreach ($hist as $ev) {
        if (!empty($ev['transit_location'])) {
            $currentTransitLocation = $ev['transit_location'];
        }
    }

    // Current customs procedure = latest known customs procedure from history or shipment.
    $currentCustomsProcedure = $shipment['customs_procedure'] ?? '';
    foreach ($hist as $ev) {
        if (!empty($ev['customs_procedure'])) {
            $currentCustomsProcedure = $ev['customs_procedure'];
        }
    }

    // Last updated timestamp from history or shipment.
    $lastUpdated = null;
    if (!empty($hist)) {
        $lastTs = '';
        foreach ($hist as $ev) {
            $ts = $ev['event_timestamp'] ?? '';
            if ($ts > $lastTs) {
                $lastTs = $ts;
            }
        }
        $lastUpdated = $lastTs ?: null;
    }
    if (!$lastUpdated && !empty($shipment['updated_at'])) {
        $lastUpdated = $shipment['updated_at'];
    }
    if (!$lastUpdated && !empty($shipment['created_at'])) {
        $lastUpdated = $shipment['created_at'];
    }

    $workflow = unserialize(TRACKING_WORKFLOW);
    $manualPercent = !empty($shipment['progress_percent']) && $shipment['progress_percent'] !== '' ? (int)$shipment['progress_percent'] : null;
    $manualSteps = [];
    if (!empty($shipment['progress_steps'])) {
        $decoded = json_decode($shipment['progress_steps'], true);
        if (is_array($decoded)) { $manualSteps = $decoded; }
    }

    if ($manualPercent !== null || !empty($manualSteps)) {
        $result = [];
        foreach ($workflow as $step) {
            $key = $step['key'];
            if (isset($manualSteps[$key])) {
                $state = $manualSteps[$key];
            } elseif ($manualPercent !== null) {
                $idx = array_search($key, array_column($workflow, 'key'), true);
                $total = count($workflow);
                $threshold = ($idx / $total) * 100;
                $nextThreshold = (($idx + 1) / $total) * 100;
                $state = $manualPercent >= $nextThreshold ? 'complete' : ($manualPercent >= $threshold ? 'current' : 'pending');
            } else {
                $state = 'pending';
            }
            $result[] = [
                'key'   => $step['key'],
                'label' => $step['label'],
                'icon'  => $step['icon'],
                'state' => $state,
            ];
        }
        if ($manualPercent !== null) {
            $progressPercent = max(0, min(100, $manualPercent));
        } else {
            $completedSteps = 0;
            foreach ($result as $st) { if (($st['state'] ?? '') === 'complete') { $completedSteps++; } }
            $totalSteps = count($result) ?: 1;
            $progressPercent = (int) round(($completedSteps / $totalSteps) * 100);
        }
        $progress = $result;
    } else {
        $progress = getTrackingProgress($shipment['status'] ?? 'pending');
        $completedSteps = 0;
        foreach ($progress as $step) {
            if (($step['state'] ?? '') === 'complete') {
                $completedSteps++;
            }
        }
        $totalSteps = count($progress) ?: 1;
        $progressPercent = (int) round(($completedSteps / $totalSteps) * 100);
    }

    return [
        'shipment'                => publicShipmentView($shipment),
        'current_location'        => $currentLocation,
        'current_transit_location'=> $currentTransitLocation,
        'current_customs_procedure' => $currentCustomsProcedure,
        'estimated_delivery'      => predictEta($db, $shipment),
        'progress'                => $progress,
        'progress_percent'        => $progressPercent,
        'history'                 => $hist,
        'last_updated'            => $lastUpdated,
        'carrier'                 => [
            'tracking_number' => $shipment['carrier_tracking_number'] ?? null,
            'name'            => $shipment['carrier_name'] ?? null,
            'integration_id'  => $shipment['carrier_integration_id'] ?? null,
            'last_sync_at'    => $shipment['last_carrier_sync_at'] ?? null,
        ],
    ];
}

/**
 * Lightweight per-IP rate limiter (fixed window) stored in the temp dir.
 * Returns true if the request is allowed, false if throttled.
 */
function trackingRateLimit($key, $max = 30, $window = 60) {
    $file = sys_get_temp_dir() . '/shp_ratelimit_' . md5($key) . '.json';
    $now = time();
    $data = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: [];
    }
    $data = array_filter($data, function ($ts) use ($now, $window) {
        return ($now - $ts) < $window;
    });
    if (count($data) >= $max) {
        return false;
    }
    $data[] = $now;
    file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

/**
 * Bridge to the unified integration layer: ingest a carrier-sourced tracking
 * event. Delegates to CarrierAdapter so status mapping + customer
 * notification stay centralized in one place.
 */
function ingestCarrierEvent(PDO $db, $integrationId, $shipmentId, $trackingNumber, $providerStatus, array $event = []) {
    require_once __DIR__ . '/integrations/IntegrationManager.php';
    $client = IntegrationClient::load($db, $integrationId);
    if (!$client) { return false; }
    $adapter = IntegrationManager::adapter($db, $client->integration);
    if (!$adapter || !($adapter instanceof CarrierAdapter)) { return false; }
    return $adapter->ingestTracking($shipmentId, $trackingNumber, $providerStatus, $event);
}

/**
 * Map a provider status string to a canonical shipment status code.
 */
function statusFromProvider(PDO $db, $integrationId, $providerStatus) {
    require_once __DIR__ . '/integrations/IntegrationManager.php';
    $client = IntegrationClient::load($db, $integrationId);
    if (!$client) { return 'in_transit'; }
    $adapter = IntegrationManager::adapter($db, $client->integration);
    if (!$adapter || !($adapter instanceof CarrierAdapter)) { return 'in_transit'; }
    return $adapter->mapStatus($providerStatus);
}
