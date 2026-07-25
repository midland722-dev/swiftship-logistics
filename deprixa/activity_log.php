<?php
/**
 * Shipment Activity Log — immutable, read-only audit trail of administrative
 * actions (create, edit, tracking, assignment, documents, notifications,
 * delivery, archive, delete). Never editable.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('view_activity_log');

$page_title = 'Activity Log - ' . SITE_NAME;
$db = getDB();

$id = intval($_GET['id'] ?? 0);
$action = trim($_GET['action'] ?? '');
$perPage = 50;
$page = max(1, intval($_GET['page'] ?? 1));

$where = ["entity_type = 'shipment'"];
$params = [];
if ($id) { $where[] = "entity_id = :id"; $params[':id'] = $id; }
if ($action) { $where[] = "action = :a"; $params[':a'] = $action; }
$whereSql = ' WHERE ' . implode(' AND ', $where);

$count = $db->prepare("SELECT COUNT(*) FROM activity_logs" . $whereSql);
foreach ($params as $k=>$v) { $count->bindValue($k,$v); }
$count->execute();
$total = (int)$count->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare("SELECT * FROM activity_logs" . $whereSql . " ORDER BY created_at DESC, id DESC LIMIT :perPage OFFSET :offset");
$stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)(($page - 1) * $perPage), PDO::PARAM_INT);
foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Resolve user names.
$users = [];
try { $users = $db->query("SELECT id, full_name FROM manager_admin")->fetchAll(PDO::FETCH_KEY_PAIR); } catch (Exception $e) {}
$actions = ['shipment_created','shipment_edited','tracking_updated','courier_assigned','documents_uploaded','notification_sent','shipment_delivered','shipment_archived','shipment_hold','shipment_resume','shipment_return','shipment_cancel','shipment_delete'];

$qs = http_build_query(array_filter(['id'=>$id,'action'=>$action]));
?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>Activity Log<?php echo $id ? ' — #'.$id : ''; ?></h4>
        <a href="shipments.php" class="btn btn-light btn-sm">Close</a>
    </div>
    <form method="GET" class="card mb-3"><div class="card-body row g-2 align-items-end">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="col-md-4"><label class="form-label small">Action</label><select name="action" class="form-select form-select-sm">
            <option value="">All Actions</option><?php foreach ($actions as $a): ?><option value="<?php echo $a; ?>" <?php echo $action===$a?'selected':''; ?>><?php echo ucwords(str_replace('_',' ',$a)); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary btn-sm">Filter</button></div>
    </form>

    <div class="card"><div class="card-header">Entries (<?php echo $total; ?>) — immutable</div><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Date</th><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?><tr><td colspan="6" class="text-center text-muted py-4">No log entries.</td></tr>
            <?php else: foreach ($logs as $l): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($l['created_at'])); ?></td>
                    <td><?php echo date('H:i:s', strtotime($l['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($users[$l['user_id']] ?? ('ID '.$l['user_id'] ?? 'System')); ?></td>
                    <td><span class="badge bg-light border"><?php echo htmlspecialchars($l['action']); ?></span></td>
                    <td><?php echo htmlspecialchars($l['description']); ?></td>
                    <td><small><?php echo htmlspecialchars($l['ip_address'] ?? '—'); ?></small></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table></div>
        <?php if ($totalPages > 1): ?><nav class="mt-3"><ul class="pagination justify-content-center">
            <?php if ($page>1): ?><li class="page-item"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $page-1; ?>">Prev</a></li><?php endif; ?>
            <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?><li class="page-item <?php echo $i===$page?'active':''; ?>"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
            <?php if ($page<$totalPages): ?><li class="page-item"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $page+1; ?>">Next</a></li><?php endif; ?>
        </ul></nav><?php endif; ?>
    </div></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
