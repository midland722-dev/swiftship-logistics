<?php
/**
 * Integration Poller — part of the unified integration layer.
 *
 * Driven by admin/worker.php (or run directly via CLI). For every active
 * carrier integration that uses polling (no inbound webhook), it fetches the
 * latest tracking status for linked shipments and ingests any change.
 *
 * Also drains the new 'carrier_sync' / 'notification_send' / 'payment_capture'
 * queue handlers so outbound integration work is retried automatically.
 *
 * Safe to run concurrently; queue claims are atomic (see includes/queue.php).
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/queue.php';
require_once __DIR__ . '/../../includes/tracking.php';
require_once __DIR__ . '/IntegrationManager.php';

/** Poll all carrier integrations for linked shipments. */
function pollCarrierIntegrations(PDO $db) {
    $clients = IntegrationClient::allActive($db, 'tracking');
    $clients = array_merge($clients, IntegrationClient::allActive($db, 'rating'));
    $clients = array_merge($clients, IntegrationClient::allActive($db, 'shipping'));
    $processed = 0;

    foreach ($clients as $client) {
        $adapter = IntegrationManager::adapter($db, $client->integration);
        if (!$adapter || !($adapter instanceof CarrierAdapter)) {
            continue;
        }
        // Shipments linked to this carrier (by external id) OR by polling all
        // non-terminal shipments when the carrier is the configured default.
        $shipments = $db->prepare("
            SELECT id, tracking_number, external_shipment_id, status
            FROM shipments
            WHERE carrier_integration_id = :i
              AND status NOT IN ('delivered','returned','cancelled','lost','damaged')
            LIMIT 200
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($shipments as $s) {
            $trackNo = $s['external_shipment_id'] ?: $s['tracking_number'];
            if (!$trackNo) { continue; }
            try {
                $remote = $adapter->pollTracking($trackNo);
                if (!$remote) { continue; }
                // Normalize the remote payload into events.
                $events = $adapter->parseInboundWebhook($remote);
                foreach ($events as $ev) {
                    if (!empty($ev['status'])) {
                        $adapter->ingestTracking($s['id'], $s['tracking_number'], $ev['status'], $ev);
                        $processed++;
                    }
                }
            } catch (Exception $e) {
                $client->markFailure('poll: ' . $e->getMessage());
            }
        }
    }
    return $processed;
}

/**
 * Queue handlers for outbound integration work.
 */
$integrationHandlers = [
    'carrier_sync' => function (PDO $db, array $p) {
        $client = IntegrationClient::load($db, (int)($p['integration_id'] ?? 0));
        if (!$client) { return; }
        $adapter = IntegrationManager::adapter($db, $client->integration);
        if ($adapter instanceof CarrierAdapter && !empty($p['tracking_number'])) {
            $remote = $adapter->pollTracking($p['tracking_number']);
            $events = $adapter->parseInboundWebhook($remote);
            foreach ($events as $ev) {
                if (!empty($ev['status'])) {
                    $adapter->ingestTracking((int)$p['shipment_id'], $p['tracking_number'], $ev['status'], $ev);
                }
            }
        }
    },
    'notification_send' => function (PDO $db, array $p) {
        $client = IntegrationClient::load($db, (int)($p['integration_id'] ?? 0));
        if (!$client) { return; }
        $adapter = IntegrationManager::adapter($db, $client->integration);
        if ($adapter instanceof NotificationAdapter) {
            $adapter->send($p['to'] ?? '', $p['subject'] ?? '', $p['body'] ?? '', $p['opts'] ?? []);
        }
    },
    'payment_capture' => function (PDO $db, array $p) {
        $client = IntegrationClient::load($db, (int)($p['integration_id'] ?? 0));
        if (!$client) { return; }
        $adapter = IntegrationManager::adapter($db, $client->integration);
        if ($adapter instanceof PaymentAdapter && !empty($p['invoice'])) {
            $adapter->createIntent($p['invoice']);
        }
    },
];

/**
 * Run the integration layer: poll carriers + drain the integration queues.
 * Composes with the existing worker (pass its base handlers to merge).
 *
 * @return array ['polled'=>int, 'queue'=>array]
 */
function runIntegrationLayer(PDO $db, array $baseHandlers = []) {
    $handlers = array_merge($baseHandlers, $integrationHandlers);
    $polled = pollCarrierIntegrations($db);
    $counts = processQueue($db, $handlers, 100);
    return ['polled' => $polled, 'queue' => $counts];
}

// When executed directly (CLI), run standalone.
if (defined('PHP_SAPI') && PHP_SAPI === 'cli' && realpath($_SERVER['PHP_SELF']) === realpath(__FILE__)) {
    $db = getDB();
    $res = runIntegrationLayer($db);
    echo '[' . date('c') . '] integration poller: polled=' . $res['polled'] . ' queue=' . json_encode($res['queue']) . "\n";
}
