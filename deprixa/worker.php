<?php
/**
 * Queue worker (CLI).
 *
 * Processes pending jobs for the tracking subsystem: sends queued emails and
 * dispatches webhook events. Run from a scheduler, e.g.:
 *   * * * * * php /path/to/admin/worker.php >/dev/null 2>&1
 *
 * It is safe to run concurrently; jobs are claimed with SELECT ... FOR UPDATE
 * and marked 'processing' so two workers won't double-process.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/queue.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/../includes/integrations/Poller.php';

$db = getDB();

$handlers = [
    'email' => function (PDO $db, array $p) {
        $subject = 'Shipment ' . ($p['tracking_number'] ?? '') . ' — ' . statusLabel($p['status'] ?? '');
        $body = "Hello " . ($p['name'] ?? 'Customer') . ",\n\n"
            . "Your shipment " . ($p['tracking_number'] ?? '') . " has been updated.\n\n"
            . "Status:   " . statusLabel($p['status'] ?? '') . "\n"
            . "Location: " . ($p['location'] ?: 'N/A') . "\n"
            . (!empty($p['description']) ? "Details:  " . $p['description'] . "\n" : '')
            . "Track:    " . ($p['url'] ?? '') . "\n\n"
            . "Thank you for using our service.\n";
        sendMail($p['to'] ?? '', $subject, $body);
    },
    'webhook' => function (PDO $db, array $p) {
        dispatchTrackingWebhooks($db, $p);
    },
];

$res = runIntegrationLayer($db, $handlers);
echo '[' . date('c') . '] worker run: ' . json_encode($res['queue']) . ' (polled=' . $res['polled'] . ")\n";
exit(0);
