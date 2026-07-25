<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/permissions.php';
require_once __DIR__ . '/includes/carrier_helpers.php';
require_once __DIR__ . '/includes/carrier_sync_ui.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/carrier_tracking.php';

$page_title = 'Carrier Management - ' . SITE_NAME;
$db = getDB();
ensureCarrierTrackingColumns($db);

$message = '';
$message_type = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        requirePermission('manage_integrations');

        if ($action === 'sync_integration') {
            $iid = isset($_POST['integration_id']) ? (int)$_POST['integration_id'] : 0;
            if ($iid > 0) {
                require_once __DIR__ . '/../includes/CarrierSyncScheduler.php';
                $scheduler = new CarrierSyncScheduler($db, 200);
                $result = $scheduler->run($iid);
                $message = 'Sync complete. Processed ' . $result['events_processed'] . ' event(s) across ' . $result['shipments_scanned'] . ' shipment(s).';
                $message_type = 'success';
                if (!empty($result['errors'])) {
                    $message .= ' Errors: ' . $result['errors'];
                    $message_type = 'warning';
                }
            }
        }
    }
}

$integrations = $db->query("
    SELECT id, provider, integration_name, integration_type, is_active, last_sync_at, last_error,
           created_at, updated_at
    FROM api_integrations
    WHERE integration_type IN ('tracking', 'rating', 'shipping')
    ORDER BY integration_type, provider, integration_name
")->fetchAll(PDO::FETCH_ASSOC);

$statsPerIntegration = [];
foreach ($integrations as $i) {
    if ($i['integration_type'] === 'tracking') {
        $statsPerIntegration[$i['id']] = carrierTrackingSyncHealth($db, $i['id']);
    }
}
?>
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : ($message_type === 'warning' ? 'exclamation-triangle-fill' : 'exclamation-triangle-fill'); ?>"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Carrier Integrations & Sync Health</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Provider</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Last Sync</th>
                        <th>Linked Shipments</th>
                        <th>Health</th>
                        <th>Last Error</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($integrations as $i): ?>
                        <?php
                        $stats = $statsPerIntegration[$i['id']] ?? [];
                        $totalLinked = (int)($stats['total_linked'] ?? 0);
                        $healthBadge = $i['integration_type'] === 'tracking' ? syncHealthBadge($stats) : '<span class="badge bg-secondary">N/A</span>';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(ucfirst($i['provider'])); ?></strong></td>
                            <td><?php echo htmlspecialchars($i['integration_name']); ?></td>
                            <td><?php echo htmlspecialchars($i['integration_type']); ?></td>
                            <td><?php echo $i['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                            <td><small><?php echo $i['last_sync_at'] ? date('M d, Y H:i', strtotime($i['last_sync_at'])) : '—'; ?></small></td>
                            <td><?php echo number_format($totalLinked); ?></td>
                            <td><?php echo $healthBadge; ?></td>
                            <td><small class="text-danger"><?php echo htmlspecialchars($i['last_error'] ?? '—'); ?></small></td>
                            <td class="text-end">
                                <form method="POST" class="d-inline" onsubmit="return confirm('Sync all linked shipments for this integration?');">
                                    <input type="hidden" name="action" value="sync_integration">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="integration_id" value="<?php echo $i['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Sync now">
                                        <i class="bi bi-arrow-clockwise"></i> Sync
                                    </button>
                                </form>
                                <a href="integrations.php" class="btn btn-sm btn-outline-secondary ms-1" title="Edit integration">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($integrations)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No integrations configured yet. Add one in Integrations.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
