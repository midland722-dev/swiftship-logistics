<?php
/**
 * API integration logs viewer for a single integration.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/integration_helpers.php';

$page_title = 'Integration Logs - ' . SITE_NAME;
requirePermission('manage_integrations');

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$integration = null;
if ($id) {
    $stmt = $db->prepare("SELECT id, integration_name, provider FROM api_integrations WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $integration = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$integration) {
    echo '<div class="alert alert-danger m-4">Integration not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$logs = $db->prepare("
    SELECT id, endpoint_hit, http_method, response_code, duration_ms, error_message, started_at
    FROM api_integration_logs
    WHERE integration_id = :id
    ORDER BY id DESC LIMIT 200
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid py-3">
    <a href="integrations.php" class="btn btn-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back</a>
    <h2 class="h4 mb-3"><i class="bi bi-list-ul me-2"></i>Logs — <?php echo htmlspecialchars($integration['integration_name']); ?></h2>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
                <tr><th>#</th><th>Time</th><th>Method</th><th>Endpoint</th><th>Code</th><th>ms</th><th>Error</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td><?php echo $l['id']; ?></td>
                        <td><?php echo htmlspecialchars($l['started_at']); ?></td>
                        <td><code><?php echo htmlspecialchars($l['http_method']); ?></code></td>
                        <td><small><?php echo htmlspecialchars($l['endpoint_hit']); ?></small></td>
                        <td>
                            <?php
                            $c = (int)$l['response_code'];
                            $cls = $c >= 200 && $c < 300 ? 'success' : ($c >= 400 ? 'danger' : 'warning');
                            echo '<span class="badge bg-' . $cls . '">' . ($c ?: 'ERR') . '</span>';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($l['duration_ms'] ?? ''); ?></td>
                        <td class="text-danger small"><?php echo htmlspecialchars($l['error_message'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-muted">No logs yet for this integration.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
