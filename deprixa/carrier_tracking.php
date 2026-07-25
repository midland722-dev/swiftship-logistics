<?php
/**
 * Carrier Tracking Management Page
 *
 * Lists all shipments that have a linked carrier tracking number,
 * shows raw carrier events, and allows manual re-sync.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/carrier_tracking.php';

$page_title = 'Carrier Tracking - ' . SITE_NAME;
$db = getDB();
ensureCarrierTrackingColumns($db);
ensureCarrierTrackingTable($db);

$message = '';
$message_type = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Manual sync action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync_carrier') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $sid = intval($_POST['shipment_id'] ?? 0);
        if ($sid > 0) {
            $result = syncCarrierTracking($db, $sid);
            if ($result['success']) {
                $message = "Carrier sync complete. {$result['synced']} new event(s) processed.";
                $message_type = 'success';
            } else {
                $message = 'Sync failed: ' . ($result['error'] ?? 'Unknown error');
                $message_type = 'danger';
            }
        }
    }
}

// Bulk sync action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_sync_carrier') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $ids = array_map('intval', $_POST['shipment_ids'] ?? []);
        $ids = array_filter($ids);
        if (empty($ids)) {
            $message = 'No shipments selected.';
            $message_type = 'warning';
        } else {
            $totalSynced = 0;
            $errors = 0;
            foreach ($ids as $sid) {
                $result = syncCarrierTracking($db, $sid);
                if ($result['success']) {
                    $totalSynced += $result['synced'];
                } else {
                    $errors++;
                }
            }
            $message = "Bulk sync complete. $totalSynced event(s) synced, $errors error(s).";
            $message_type = $errors > 0 ? 'warning' : 'success';
        }
    }
}

// Unlink action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlink_carrier') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $sid = intval($_POST['shipment_id'] ?? 0);
        if ($sid > 0) {
            $result = unlinkCarrierTracking($db, $sid);
            if ($result['success']) {
                $message = 'Carrier tracking unlinked.';
                $message_type = 'success';
            } else {
                $message = 'Unlink failed: ' . ($result['error'] ?? 'Unknown error');
                $message_type = 'danger';
            }
        }
    }
}

// Fetch shipments with carrier tracking linked.
$search = trim($_GET['search'] ?? '');
$integrationFilter = trim($_GET['integration_id'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

$where = ["s.carrier_tracking_number IS NOT NULL", "s.carrier_tracking_number != ''"];
$params = [];
if ($search !== '') {
    $where[] = "(s.tracking_number LIKE :search OR s.carrier_tracking_number LIKE :search OR s.carrier_name LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($integrationFilter !== '') {
    $where[] = "s.carrier_integration_id = :iid";
    $params[':iid'] = (int)$integrationFilter;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$countSql = "SELECT COUNT(*) FROM shipments s $whereSql";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "
    SELECT s.id, s.tracking_number, s.carrier_tracking_number, s.carrier_name,
           s.carrier_integration_id, s.last_carrier_sync_at, s.status,
           s.origin_city, s.destination_city, s.created_at,
           ai.provider, ai.integration_name
    FROM shipments s
    LEFT JOIN api_integrations ai ON ai.id = s.carrier_integration_id
    $whereSql
    ORDER BY s.last_carrier_sync_at DESC, s.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active integrations for filter dropdown.
$integrations = $db->query("
    SELECT id, integration_name, provider, integration_type
    FROM api_integrations WHERE is_active = 1 AND integration_type = 'tracking'
    ORDER BY provider, integration_name
")->fetchAll(PDO::FETCH_ASSOC);

function sortUrl($col, $current_sort, $current_dir) {
    $dir = ($current_sort === $col && $current_dir === 'ASC') ? 'desc' : 'asc';
    return '?' . http_build_query(array_merge($_GET, ['sort' => $col, 'dir' => $dir]));
}
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-0"><i class="bi bi-truck"></i> Carrier-Linked Shipments</h5>
                <small class="text-muted"><?php echo number_format($total); ?> total linked shipments</small>
            </div>
            <div class="d-flex gap-2">
                <a href="shipments.php" class="btn btn-outline-secondary">
                    <i class="bi bi-box-seam"></i> All Shipments
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Tracking, carrier ref, carrier name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="integration_id" class="form-select">
                    <option value="">All Carriers</option>
                    <?php foreach ($integrations as $i): ?>
                        <option value="<?php echo $i['id']; ?>" <?php echo $integrationFilter == $i['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($i['provider'] . ' (' . $i['integration_name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="carrier_tracking.php" class="btn btn-secondary w-100">Clear</a>
            </div>
        </form>

        <?php if (empty($shipments)): ?>
            <div class="text-center py-5 text-muted">No shipments with linked carrier tracking found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="36"><input type="checkbox" class="form-check-input" id="selectAllCarrier"></th>
                            <th>Shipment</th>
                            <th>Carrier</th>
                            <th>Carrier Tracking #</th>
                            <th>Integration</th>
                            <th>Last Sync</th>
                            <th>Status</th>
                            <th>Route</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $sh): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input carrier-checkbox" name="shipment_ids[]" value="<?php echo $sh['id']; ?>"></td>
                                <td>
                                    <strong>
                                        <a href="shipment_details.php?id=<?php echo $sh['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($sh['tracking_number']); ?>
                                        </a>
                                    </strong>
                                    <br><small class="text-muted">ID #<?php echo $sh['id']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($sh['carrier_name'] ?? 'N/A'); ?></td>
                                <td><code><?php echo htmlspecialchars($sh['carrier_tracking_number']); ?></code></td>
                                <td><?php echo htmlspecialchars($sh['provider'] ?? ($sh['integration_name'] ?? 'N/A')); ?></td>
                                <td>
                                    <?php if ($sh['last_carrier_sync_at']): ?>
                                        <small><?php echo date('M d, Y H:i', strtotime($sh['last_carrier_sync_at'])); ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo statusBadge($sh['status']); ?></td>
                                <td><small><?php echo htmlspecialchars($sh['origin_city'] ?? '?'); ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?php echo htmlspecialchars($sh['destination_city'] ?? '?'); ?></small></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Sync tracking from carrier?');">
                                            <input type="hidden" name="action" value="sync_carrier">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="shipment_id" value="<?php echo $sh['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Sync from carrier">
                                                <i class="bi bi-arrow-clockwise"></i> Sync
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Unlink carrier tracking? This does not affect tracking history.');">
                                            <input type="hidden" name="action" value="unlink_carrier">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="shipment_id" value="<?php echo $sh['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Unlink carrier">
                                                <i class="bi bi-link-45deg"></i> Unlink
                                            </button>
                                        </form>
                                        <a href="tracking_history.php?id=<?php echo $sh['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Tracking History">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <!-- Bulk Sync -->
            <form method="POST" id="bulkSyncForm" class="mt-3">
                <input type="hidden" name="action" value="bulk_sync_carrier">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Sync tracking from carrier for all selected shipments?');">
                    <i class="bi bi-arrow-clockwise"></i> Sync Selected
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('selectAllCarrier')?.addEventListener('change', function() {
    document.querySelectorAll('.carrier-checkbox').forEach(cb => cb.checked = this.checked);
});
document.querySelectorAll('.carrier-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
        document.getElementById('bulkSyncForm').style.display =
            document.querySelectorAll('.carrier-checkbox:checked').length > 0 ? 'block' : 'none';
    });
});
document.getElementById('bulkSyncForm')?.addEventListener('submit', function(e) {
    const selected = document.querySelectorAll('.carrier-checkbox:checked');
    if (!selected.length) { e.preventDefault(); alert('Select at least one shipment.'); return; }
    selected.forEach(cb => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = 'shipment_ids[]'; i.value = cb.value;
        this.appendChild(i);
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
