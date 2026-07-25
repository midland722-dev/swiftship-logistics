<?php
/**
 * Integrations catalog — list, enable/disable, test, link to edit + logs.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/integration_helpers.php';
require_once __DIR__ . '/includes/carrier_helpers.php';
require_once __DIR__ . '/../includes/integrations/IntegrationManager.php';

$page_title = 'Integrations - ' . SITE_NAME;
requirePermission('manage_integrations');

$db = getDB();

// Toggle active state.
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)($_GET['csrf'] ?? ''))) {
        $db->prepare("UPDATE api_integrations SET is_active = 1 - is_active, updated_at = NOW() WHERE id = :id")
            ->execute([':id' => (int)$_GET['toggle']]);
        header('Location: integrations.php');
        exit;
    }
}

$rows = $db->query("SELECT * FROM api_integrations ORDER BY integration_type, provider")->fetchAll(PDO::FETCH_ASSOC);
$typeLabels = integrationTypeOptions();
?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0"><i class="bi bi-plug-fill me-2"></i>Integrations</h2>
        <a href="integrations_edit.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Integration</a>
    </div>

    <div class="row g-3">
        <?php foreach ($rows as $r): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-hdd-network me-2"></i><?php echo htmlspecialchars($r['integration_name']); ?></span>
                        <?php echo integrationHealthBadge($r); ?>
                    </div>
                    <div class="card-body small">
                        <div class="mb-1"><strong>Provider:</strong> <?php echo htmlspecialchars($r['provider']); ?></div>
                        <div class="mb-1"><strong>Type:</strong> <?php echo htmlspecialchars($typeLabels[$r['integration_type']] ?? $r['integration_type']); ?></div>
                        <div class="mb-1"><strong>Endpoint:</strong> <code><?php echo htmlspecialchars($r['endpoint_url']); ?></code></div>
                        <div class="mb-1"><strong>Auth:</strong> <?php echo htmlspecialchars($r['auth_type']); ?></div>
                        <div class="mb-1"><strong>Last sync:</strong> <?php echo $r['last_sync_at'] ? htmlspecialchars($r['last_sync_at']) : 'never'; ?></div>
                        <?php if ($r['integration_type'] === 'tracking'): ?>
                            <div class="mb-1"><strong>Webhook URL:</strong> <code><?php echo htmlspecialchars(webhookUrlForIntegration($r['id'])); ?></code></div>
                            <div class="mb-1"><strong>Inbound Secret:</strong> <code><?php echo !empty($r['inbound_secret_encrypted']) ? '••••••••' : '(not set)'; ?></code></div>
                        <?php endif; ?>
                        <?php if (!empty($r['last_error'])): ?>
                            <div class="text-danger mb-1"><strong>Last error:</strong> <?php echo htmlspecialchars(substr($r['last_error'], 0, 120)); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer d-flex flex-wrap gap-2">
                        <a href="integrations_edit.php?id=<?php echo $r['id']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                        <a href="integrations_logs.php?id=<?php echo $r['id']; ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-list-ul"></i> Logs</a>
                        <a href="integrations.php?toggle=<?php echo $r['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-outline-<?php echo $r['is_active'] ? 'warning' : 'success'; ?> btn-sm">
                            <?php echo $r['is_active'] ? 'Disable' : 'Enable'; ?>
                        </a>
                        <button class="btn btn-outline-primary btn-sm" onclick="testIntegration(<?php echo $r['id']; ?>, this)"><i class="bi bi-lightning"></i> Test</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
            <div class="col-12"><div class="alert alert-light">No integrations yet. Click <strong>New Integration</strong> to connect a carrier, payment gateway, or notification provider.</div></div>
        <?php endif; ?>
    </div>
</div>

<script>
function testIntegration(id, btn) {
    var old = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    fetch('../api/v1/integrations.php?id=' + id + '&test=1', {method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: 'csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'})
        .then(r => r.json()).then(d => {
            alert(d.ok ? 'Connection OK (HTTP ' + d.status + ')' : 'Test failed: ' + (d.error || d.status));
        }).catch(e => alert('Test error: ' + e)).finally(() => { btn.disabled = false; btn.innerHTML = old; });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
