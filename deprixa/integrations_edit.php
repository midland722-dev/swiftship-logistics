<?php
/**
 * Create / edit an integration. Uses the REST endpoint for writes so the
 * encryption + validation logic lives in one place.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/integration_helpers.php';
require_once __DIR__ . '/../includes/integrations/IntegrationManager.php';

$page_title = 'Edit Integration - ' . SITE_NAME;
requirePermission('manage_integrations');

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$row = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM api_integrations WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

$type = $row['integration_type'] ?? ($_GET['type'] ?? 'tracking');
$provider = $row['provider'] ?? '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
    $post = array_merge($_POST, ['csrf_token' => $_SESSION['csrf_token']]);
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($post),
        'timeout' => 15,
    ]]);
    $resp = @file_get_contents('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/shp/api/v1/integrations.php' . ($id ? '?id=' . $id : ''), false, $ctx);
    $dec = json_decode($resp ?: '', true);
    if (!empty($dec['ok']) || !empty($dec['id'])) {
        $message = 'Integration saved.';
        $messageType = 'success';
        if (!$id && !empty($dec['id'])) {
            header('Location: integrations_edit.php?id=' . (int)$dec['id']);
            exit;
        }
        $stmt = $db->prepare("SELECT * FROM api_integrations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)($dec['id'] ?? $id)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $type = $row['integration_type'];
        $provider = $row['provider'];
    } else {
        $message = 'Error: ' . htmlspecialchars($dec['error'] ?? 'unknown');
        $messageType = 'danger';
    }
}

$typeLabels = integrationTypeOptions();
$authLabels = authTypeOptions();
$providers = providerOptions($type);
?>
<div class="container-fluid py-3" style="max-width: 880px;">
    <a href="integrations.php" class="btn btn-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back</a>
    <h2 class="h4 mb-3"><i class="bi bi-plug-fill me-2"></i><?php echo $id ? 'Edit' : 'New'; ?> Integration</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Integration Name *</label>
                <input name="integration_name" class="form-control" required value="<?php echo htmlspecialchars($row['integration_name'] ?? ''); ?>" placeholder="e.g. FedEx Production">
            </div>
            <div class="col-md-3">
                <label class="form-label">Type *</label>
                <select name="integration_type" id="typeSel" class="form-select" required>
                    <?php foreach ($typeLabels as $k => $l): ?>
                        <option value="<?php echo $k; ?>" <?php echo $type === $k ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Provider *</label>
                <select name="provider" id="providerSel" class="form-select" required>
                    <?php foreach ($providers as $k => $l): ?>
                        <option value="<?php echo $k; ?>" <?php echo strtolower($provider) === $k ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Endpoint URL *</label>
                <input name="endpoint_url" class="form-control" required value="<?php echo htmlspecialchars($row['endpoint_url'] ?? ''); ?>" placeholder="https://api.fedex.com">
            </div>
            <div class="col-md-4">
                <label class="form-label">Auth Type</label>
                <select name="auth_type" class="form-select">
                    <?php foreach ($authLabels as $k => $l): ?>
                        <option value="<?php echo $k; ?>" <?php echo ($row['auth_type'] ?? 'api_key') === $k ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">API Key</label>
                <input name="api_key" type="password" class="form-control" autocomplete="new-password" placeholder="<?php echo $id ? '(unchanged)' : ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">API Secret</label>
                <input name="api_secret" type="password" class="form-control" autocomplete="new-password" placeholder="<?php echo $id ? '(unchanged)' : ''; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Inbound Webhook Secret <small class="text-muted">(for inbound tracking/payment)</small></label>
                <input name="inbound_secret" type="password" class="form-control" autocomplete="new-password" placeholder="<?php echo $id ? '(unchanged)' : ''; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Webhook URL / From address</label>
                <input name="webhook_url" class="form-control" value="<?php echo htmlspecialchars($row['webhook_url'] ?? ''); ?>" placeholder="https://.../api/v1/webhooks/inbound.php?id=<?php echo $id ?: 'N'; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rate limit / min</label>
                <input name="rate_limit_per_minute" type="number" min="1" class="form-control" value="<?php echo (int)($row['rate_limit_per_minute'] ?? 60); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Timeout (s)</label>
                <input name="timeout_seconds" type="number" min="1" class="form-control" value="<?php echo (int)($row['timeout_seconds'] ?? 30); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Retries</label>
                <input name="retry_count" type="number" min="0" class="form-control" value="<?php echo (int)($row['retry_count'] ?? 3); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Retry delay (s)</label>
                <input name="retry_delay_seconds" type="number" min="1" class="form-control" value="<?php echo (int)($row['retry_delay_seconds'] ?? 5); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Request format</label>
                <select name="request_format" class="form-select">
                    <option value="json" <?php echo ($row['request_format'] ?? 'json') === 'json' ? 'selected' : ''; ?>>JSON</option>
                    <option value="form_data" <?php echo ($row['request_format'] ?? '') === 'form_data' ? 'selected' : ''; ?>>Form Data</option>
                    <option value="xml" <?php echo ($row['request_format'] ?? '') === 'xml' ? 'selected' : ''; ?>>XML</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Response format</label>
                <select name="response_format" class="form-select">
                    <option value="json" <?php echo ($row['response_format'] ?? 'json') === 'json' ? 'selected' : ''; ?>>JSON</option>
                    <option value="xml" <?php echo ($row['response_format'] ?? '') === 'xml' ? 'selected' : ''; ?>>XML</option>
                </select>
            </div>
            <div class="col-12 form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="activeChk" <?php echo empty($row) || !empty($row['is_active']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="activeChk">Active</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Save Integration</button>
    </form>
</div>

<script>
// Reload provider list when type changes.
document.getElementById('typeSel').addEventListener('change', function() {
    var type = this.value;
    fetch('../api/v1/integrations.php').then(r => r.json()).catch(() => []);
    // Simplest: navigate to same page with ?type= to refresh provider options.
    if (!<?php echo $id ? 'true' : 'false'; ?>) {
        window.location.search = '?type=' + encodeURIComponent(type);
    }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
