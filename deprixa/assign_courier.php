<?php
/**
 * Assign Courier module — assign driver/vehicle/route/distribution center/
 * warehouse/pickup, reassign, and show immutable assignment history.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

requirePermission('assign_courier');

$page_title = 'Assign Courier - ' . SITE_NAME;
$db = getDB();
ensureCourierTables($db);
ensureShipmentStatusEnum($db);
ensureModuleTables($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { echo '<div class="alert alert-danger m-4">Shipment not found.</div>'; require_once __DIR__.'/includes/footer.php'; exit; }
$GLOBALS['shipment'] = $shipment;

$drivers = fetchDrivers($db);
$vehicles = fetchVehicles($db);
$branches = fetchBranches($db);
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msg = 'Invalid security token.'; $msgType = 'danger';
    } else {
        $driver_id = !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : null;
        $vehicle_id = !empty($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
        $branch_id = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
        $route = trim($_POST['route'] ?? '');
        $dc = trim($_POST['distribution_center'] ?? '');
        $wh = trim($_POST['warehouse'] ?? '');
        $pickup = trim($_POST['pickup_date'] ?? null) ?: null;
        try {
            $db->beginTransaction();
            $db->prepare("UPDATE shipments SET driver_id=:d, vehicle_id=:v, branch_id=:b, route=:r, distribution_center=:dc, warehouse=:w, pickup_date=:p, updated_at=NOW() WHERE id=:id")
                ->execute([':d'=>$driver_id,':v'=>$vehicle_id,':b'=>$branch_id,':r'=>$route,':dc'=>$dc,':w'=>$wh,':p'=>$pickup,':id'=>$id]);
            if ($branch_id) {
                $stmt = $db->prepare("SELECT name, city FROM locations WHERE id=:id LIMIT 1");
                $stmt->execute([':id'=>$branch_id]);
                $b = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($b) $db->prepare("UPDATE shipments SET current_branch=:c WHERE id=:id")->execute([':c'=>$b['name'].' ('.$b['city'].')',':id'=>$id]);
            }
            // Record assignment history (each save = a new row).
            $db->prepare("INSERT INTO courier_assignments (shipment_id, driver_id, vehicle_id, branch_id, route, distribution_center, warehouse, pickup_date, assigned_by, created_at)
                VALUES (:s,:d,:v,:b,:r,:dc,:w,:p,:u,NOW())")
                ->execute([':s'=>$id,':d'=>$driver_id,':v'=>$vehicle_id,':b'=>$branch_id,':r'=>$route,':dc'=>$dc,':w'=>$wh,':p'=>$pickup,':u'=>$_SESSION['admin_id']??null]);
            $db->commit();
            clearDashboardCache();
            $dn = $driver_id ? lookupName($drivers, $driver_id) : 'Unassigned';
            logShipmentAction($db, 'courier_assigned', $id, "Assigned driver $dn" . ($vehicle_id ? ', vehicle '.lookupName($vehicles,$vehicle_id) : ''), ['driver_id'=>$driver_id,'vehicle_id'=>$vehicle_id,'branch_id'=>$branch_id]);
            $msg = 'Courier assignment saved.'; $msgType = 'success';
            $stmt = $db->prepare("SELECT * FROM shipments WHERE id=:id LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $db->rollBack(); error_log('Error: ' . $e->getMessage());
            $msg = 'An error occurred. Please try again later.'; $msgType='danger'; }
    }
}

$history = $db->prepare("SELECT a.*, d.name AS driver_name, v.registration_number AS vehicle_name, l.name AS branch_name
    FROM courier_assignments a
    LEFT JOIN drivers d ON a.driver_id = d.id
    LEFT JOIN vehicles v ON a.vehicle_id = v.id
    LEFT JOIN locations l ON a.branch_id = l.id
    WHERE a.shipment_id = :id ORDER BY a.created_at DESC, a.id DESC");
$history->execute([':id'=>$id]);
$history = $history->fetchAll(PDO::FETCH_ASSOC);
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
$sel = function($k,$val) { return ($GLOBALS['shipment'][$k] ?? '') == $val ? 'selected' : ''; };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Assign Courier — <?php echo $v('tracking_number'); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card"><div class="card-header">Assignment</div><div class="card-body">
                <form method="POST">
                    <?php echo csrfInput(); ?>
                    <div class="mb-3"><label class="form-label">Driver / Courier</label><select name="driver_id" class="form-select"><option value="0">— None —</option><?php foreach($drivers as $d): ?><option value="<?php echo $d['id']; ?>" <?php echo ($shipment['driver_id']??0)==$d['id']?'selected':''; ?>><?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Vehicle Number</label><select name="vehicle_id" class="form-select"><option value="0">— None —</option><?php foreach($vehicles as $ve): ?><option value="<?php echo $ve['id']; ?>" <?php echo ($shipment['vehicle_id']??0)==$ve['id']?'selected':''; ?>><?php echo htmlspecialchars($ve['registration_number']); ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Branch / Hub</label><select name="branch_id" class="form-select"><option value="0">— None —</option><?php foreach($branches as $b): ?><option value="<?php echo $b['id']; ?>" <?php echo ($shipment['branch_id']??0)==$b['id']?'selected':''; ?>><?php echo htmlspecialchars($b['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Warehouse</label><input name="warehouse" class="form-control" value="<?php echo $v('warehouse'); ?>"></div>
                    <div class="mb-3"><label class="form-label">Distribution Center</label><input name="distribution_center" class="form-control" value="<?php echo $v('distribution_center'); ?>"></div>
                    <div class="mb-3"><label class="form-label">Delivery Route</label><input name="route" class="form-control" value="<?php echo $v('route'); ?>" placeholder="e.g. NYC → BOS"></div>
                    <div class="mb-3"><label class="form-label">Pickup Assignment Date</label><input type="date" name="pickup_date" class="form-control" value="<?php echo $v('pickup_date'); ?>"></div>
                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Save this assignment?');"><i class="bi bi-check-lg"></i> Save / Reassign</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Assignment History (<?php echo count($history); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>Driver</th><th>Vehicle</th><th>Branch</th><th>Route</th><th>By</th></tr></thead>
                    <tbody>
                    <?php if (empty($history)): ?><tr><td colspan="6" class="text-center text-muted py-4">No assignments yet.</td></tr>
                    <?php else: foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($h['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($h['driver_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($h['vehicle_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($h['branch_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($h['route'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($h['assigned_by'] ?? 'Sys'); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div></div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
