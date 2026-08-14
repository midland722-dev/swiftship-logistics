<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/includes/carrier_helpers.php';
require_once __DIR__ . '/../includes/carrier_tracking.php';
require_once __DIR__ . '/../includes/shipment_status.php';

$page_title = 'Manage Shipments - ' . SITE_NAME;
$db = getDB();
require_once __DIR__ . '/../includes/tracking.php';
ensureShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);

$message = '';
$message_type = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------------------------------------------------------------------------
// POST actions (status update / delete / bulk delete) — preserved behaviour
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } elseif (!in_array($_SESSION['admin_role'] ?? 'staff', ['super_admin', 'admin', 'operations'], true)) {
        $message = 'Access denied.';
        $message_type = 'danger';
    } else {
        $admin_id = $_SESSION['admin_id'] ?? null;

        if ($action === 'update_status' && !empty($_POST['shipment_id']) && !empty($_POST['new_status'])) {
            try {
                $shipment_id = intval($_POST['shipment_id']);
                $new_status = trim($_POST['new_status']);
                $location_input = trim($_POST['location'] ?? '');
                $remarks_input = trim($_POST['remarks'] ?? '');

                $db->beginTransaction();

                $stmt = $db->prepare("UPDATE shipments SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':status' => $new_status, ':id' => $shipment_id]);

                $tn = $db->prepare("SELECT tracking_number, origin_city, destination_city FROM shipments WHERE id = :id");
                $tn->execute([':id' => $shipment_id]);
                $meta = $tn->fetch(PDO::FETCH_ASSOC);

                $location = $location_input !== '' ? $location_input : deriveTrackingLocation($new_status, $meta ?: []);
                $remarks = $remarks_input !== '' ? $remarks_input : ('Status updated to ' . statusLabel($new_status) . ($location_input !== '' ? ' @ ' . $location_input : ''));

                $stmt = $db->prepare("
                    INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                    VALUES (:shipment_id, :status, NOW(), :location, :remarks, :admin_id)
                ");
                $stmt->execute([
                    ':shipment_id' => $shipment_id,
                    ':status' => $new_status,
                    ':location' => $location,
                    ':remarks' => $remarks,
                    ':admin_id' => $admin_id,
                ]);

                $db->commit();
                clearDashboardCache();

                try {
                    ensureTrackingHistory($db);
                    if ($meta) {
                        $updatedBy = $admin_id ? ('Admin #' . $admin_id) : 'Admin';
                        addTrackingEvent(
                            $db, $shipment_id, $meta['tracking_number'], $new_status, $location,
                            $remarks,
                            $updatedBy
                        );
                    }
                } catch (Exception $e) { /* non-fatal */ }

                $message = "Status updated successfully.";
                $message_type = 'success';
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Shipment status update failed: ' . $e->getMessage());
                $message = "An error occurred while updating the shipment status. Please try again.";
                $message_type = 'danger';
            }
        } elseif ($action === 'delete_shipment' && !empty($_POST['shipment_id'])) {
            requirePermission('delete_shipment');
            try {
                $shipment_id = intval($_POST['shipment_id']);
                $db->beginTransaction();
                $stmt = $db->prepare("SELECT tracking_number FROM shipments WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $shipment_id]);
                $tracking = $stmt->fetchColumn();
                if (!$tracking) { throw new Exception("Shipment not found."); }
                $db->prepare("DELETE FROM packages WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM tracking_logs WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM tracking_history WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM shipment_status_history_v2 WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM payments WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM delivery_attempts WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM courier_assignments WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM delivery_confirmations WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM refunds WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM shipment_notifications WHERE shipment_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM attachments WHERE entity_type = 'shipment' AND entity_id = :id")->execute([':id' => $shipment_id]);
                $db->prepare("DELETE FROM shipments WHERE id = :id")->execute([':id' => $shipment_id]);
                $db->commit();
                clearDashboardCache();
                $message = "Shipment #$tracking deleted successfully.";
                $message_type = 'success';
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Shipment delete failed: ' . $e->getMessage());
                $message = "An error occurred while deleting the shipment. Please try again.";
                $message_type = 'danger';
            }
        } elseif ($action === 'bulk_delete' && !empty($_POST['shipment_ids'])) {
            requirePermission('delete_shipment');
            try {
                $ids = array_map('intval', $_POST['shipment_ids']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->beginTransaction();
                $db->prepare("DELETE FROM packages WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM tracking_logs WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM tracking_history WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM shipment_status_history_v2 WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM payments WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM delivery_attempts WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM courier_assignments WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM delivery_confirmations WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM refunds WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM shipment_notifications WHERE shipment_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM attachments WHERE entity_type = 'shipment' AND entity_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM shipments WHERE id IN ($placeholders)")->execute($ids);
                $db->commit();
                clearDashboardCache();
                $message = count($ids) . " shipment(s) deleted successfully.";
                $message_type = 'success';
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Bulk delete shipments failed: ' . $e->getMessage());
                $message = "An error occurred while deleting shipments. Please try again.";
                $message_type = 'danger';
            }
        } elseif ($action === 'inline_edit' && !empty($_POST['shipment_id'])) {
            requirePermission('edit_shipment');
            try {
                $shipment_id = intval($_POST['shipment_id']);
                $sender_name = trim($_POST['sender_name'] ?? '');
                $sender_phone = trim($_POST['sender_phone'] ?? '');
                $receiver_name = trim($_POST['receiver_name'] ?? '');
                $receiver_phone = trim($_POST['receiver_phone'] ?? '');
                $origin_city = trim($_POST['origin_city'] ?? '');
                $destination_city = trim($_POST['destination_city'] ?? '');
                $weight = trim($_POST['weight'] ?? '');
                $status = trim($_POST['status'] ?? '');
                if ($status !== '' && !isValidStatus($status)) {
                    throw new Exception("Invalid status: " . htmlspecialchars($status));
                }
                $location = trim($_POST['location'] ?? '');
                $location_custom = trim($_POST['location_custom'] ?? '');
                $procedure = trim($_POST['customs_procedure'] ?? '');
                $procedure_custom = trim($_POST['procedure_custom'] ?? '');
                $remarks = trim($_POST['remarks'] ?? '');
                $event_notes = trim($_POST['event_notes'] ?? '');

                $db->beginTransaction();

                $stmt = $db->prepare("
                    UPDATE shipments SET
                        sender_name = :sender_name,
                        sender_phone = :sender_phone,
                        receiver_name = :receiver_name,
                        receiver_phone = :receiver_phone,
                        origin_city = :origin_city,
                        destination_city = :destination_city,
                        total_weight = :weight,
                        status = :status,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':sender_name' => $sender_name,
                    ':sender_phone' => $sender_phone,
                    ':receiver_name' => $receiver_name,
                    ':receiver_phone' => $receiver_phone,
                    ':origin_city' => $origin_city,
                    ':destination_city' => $destination_city,
                    ':weight' => $weight,
                    ':status' => $status,
                    ':id' => $shipment_id,
                ]);

                $tn = $db->prepare("SELECT tracking_number FROM shipments WHERE id = :id");
                $tn->execute([':id' => $shipment_id]);
                $tracking_number = $tn->fetchColumn();

                $finalLocation = $location_custom !== '' ? $location_custom : $location;
                $finalProcedure = $procedure_custom !== '' ? $procedure_custom : $procedure;

                $hasCustomsProcedure = false;
                try {
                    $db->query("SELECT customs_procedure FROM shipment_status_history_v2 LIMIT 1");
                    $hasCustomsProcedure = true;
                } catch (Exception $e) {
                    $hasCustomsProcedure = false;
                }

                if ($hasCustomsProcedure) {
                    $stmt = $db->prepare("
                        INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, customs_procedure, event_notes, occurred_by)
                        VALUES (:shipment_id, :status, NOW(), :location, :remarks, :customs_procedure, :event_notes, :admin_id)
                    ");
                    $stmt->execute([
                        ':shipment_id' => $shipment_id,
                        ':status' => $status,
                        ':location' => $finalLocation ?: 'N/A',
                        ':remarks' => $remarks ?: 'Shipment updated via inline edit',
                        ':customs_procedure' => $finalProcedure ?: null,
                        ':event_notes' => $event_notes ?: null,
                        ':admin_id' => $_SESSION['admin_id'] ?? null,
                    ]);
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                        VALUES (:shipment_id, :status, NOW(), :location, :remarks, :admin_id)
                    ");
                    $stmt->execute([
                        ':shipment_id' => $shipment_id,
                        ':status' => $status,
                        ':location' => $finalLocation ?: 'N/A',
                        ':remarks' => $remarks ?: 'Shipment updated via inline edit',
                        ':admin_id' => $_SESSION['admin_id'] ?? null,
                    ]);
                }

                if ($tracking_number) {
                    try {
                        ensureTrackingHistory($db);
                        $updatedBy = ($_SESSION['admin_id'] ?? null) ? ('Admin #' . $_SESSION['admin_id']) : 'Admin';
                        addTrackingEvent($db, $shipment_id, $tracking_number, $status, $finalLocation ?: 'N/A', $remarks ?: 'Shipment updated via inline edit', $updatedBy);
                    } catch (Exception $e) { /* non-fatal */ }
                }

                $db->commit();
                clearDashboardCache();
                $message = "Shipment updated successfully.";
                $message_type = 'success';
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Shipment update failed: ' . $e->getMessage());
                $message = "An error occurred while updating the shipment. Please try again.";
                $message_type = 'danger';
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Filters
// ---------------------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$customer_filter = intval($_GET['customer_id'] ?? 0);
$driver_filter = intval($_GET['driver_id'] ?? 0);
$branch_filter = intval($_GET['branch_id'] ?? 0);
$service_filter = trim($_GET['service_type'] ?? '');
$payment_filter = trim($_GET['payment_status'] ?? '');
$destination_filter = trim($_GET['destination'] ?? '');
$carrier_filter = isset($_GET['carrier']) ? (int)$_GET['carrier'] : 0;
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$sort_by = trim($_GET['sort'] ?? 'created_at');
$sort_dir = strtolower(trim($_GET['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

$allowed_sort = ['tracking_number', 'status', 'total_amount', 'created_at', 'origin_city', 'destination_city', 'sender_name', 'receiver_name'];
if (!in_array($sort_by, $allowed_sort)) { $sort_by = 'created_at'; }

$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(s.tracking_number LIKE :search OR s.reference_number LIKE :search OR s.sender_name LIKE :search OR s.receiver_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($status_filter !== '') { $where[] = "s.status = :status"; $params[':status'] = $status_filter; }
if ($customer_filter > 0) { $where[] = "s.customer_id = :customer"; $params[':customer'] = $customer_filter; }
if ($driver_filter > 0) { $where[] = "s.driver_id = :driver"; $params[':driver'] = $driver_filter; }
if ($branch_filter > 0) { $where[] = "s.branch_id = :branch"; $params[':branch'] = $branch_filter; }
if ($service_filter !== '') { $where[] = "s.service_type = :service"; $params[':service'] = $service_filter; }
if ($payment_filter !== '') { $where[] = "s.payment_status = :payment"; $params[':payment'] = $payment_filter; }
if ($destination_filter !== '') { $where[] = "s.destination_city LIKE :dest"; $params[':dest'] = "%$destination_filter%"; }
if ($carrier_filter > 0) { $where[] = "s.carrier_integration_id = :carrier"; $params[':carrier'] = $carrier_filter; }
if ($date_from !== '') { $where[] = "DATE(s.created_at) >= :date_from"; $params[':date_from'] = $date_from; }
if ($date_to !== '') { $where[] = "DATE(s.created_at) <= :date_to"; $params[':date_to'] = $date_to; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$count_sql = "SELECT COUNT(*) FROM shipments s LEFT JOIN users u ON s.customer_id = u.id $whereSql";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_shipments = (int)$count_stmt->fetchColumn();

$total_pages = max(1, (int)ceil($total_shipments / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "
    SELECT s.*,
           CONCAT(u.name) as customer_name,
           u.email as customer_email
    FROM shipments s
    LEFT JOIN users u ON s.customer_id = u.id
    $whereSql
    ORDER BY s.$sort_by $sort_dir
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$valid_statuses = array_keys(allShipmentStatuses());
$stats = shipmentStats($db);
$drivers = fetchDrivers($db);
$vehicles = fetchVehicles($db);
$branches = fetchBranches($db);
ensureCarrierTrackingColumns($db);
$carrierIntegrations = carrierTrackingOptions($db);

function sortUrl($col, $current_sort, $current_dir) {
    $dir = ($current_sort === $col && $current_dir === 'ASC') ? 'desc' : 'asc';
    return '?' . http_build_query(array_merge($_GET, ['sort' => $col, 'dir' => $dir]));
}
function sortIcon($col, $current_sort, $current_dir) {
    if ($current_sort !== $col) return '';
    return '<i class="bi bi-caret-' . ($current_dir === 'ASC' ? 'up' : 'down') . '"></i>';
}
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ===================== Dashboard Summary Cards ===================== -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Total', 'value' => $stats['total'], 'icon' => 'box-seam', 'cls' => 'bg-dark'],
        ['label' => 'Pending Pickup', 'value' => $stats['pending_pickup'], 'icon' => 'hourglass-split', 'cls' => 'bg-warning text-dark'],
        ['label' => 'Picked Up', 'value' => $stats['picked_up'], 'icon' => 'hand-index', 'cls' => 'bg-info text-dark'],
        ['label' => 'In Transit', 'value' => $stats['in_transit'], 'icon' => 'truck', 'cls' => 'bg-primary'],
        ['label' => 'At Hub', 'value' => $stats['at_hub'], 'icon' => 'building', 'cls' => 'bg-primary'],
        ['label' => 'Out for Delivery', 'value' => $stats['out_for_delivery'], 'icon' => 'shipping-fast', 'cls' => 'bg-primary'],
        ['label' => 'Delivered', 'value' => $stats['delivered'], 'icon' => 'check-circle', 'cls' => 'bg-success'],
        ['label' => 'Failed Deliveries', 'value' => $stats['delivery_failed'], 'icon' => 'x-circle', 'cls' => 'bg-danger'],
        ['label' => 'Returned', 'value' => $stats['returned'], 'icon' => 'arrow-return-left', 'cls' => 'bg-danger'],
        ['label' => 'Cancelled', 'value' => $stats['cancelled'], 'icon' => 'slash-circle', 'cls' => 'bg-secondary'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card <?php echo $c['cls']; ?> text-white h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fs-4 fw-bold"><?php echo number_format($c['value']); ?></div>
                        <div class="small opacity-75"><?php echo $c['label']; ?></div>
                    </div>
                    <i class="bi bi-<?php echo $c['icon']; ?> fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Shipments</h5>
                <small class="text-muted"><?php echo number_format($total_shipments); ?> total shipments</small>
            </div>
            <div class="d-flex gap-2">
                <a href="create_shipment.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Create Shipment
                </a>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal" id="bulkDeleteBtn" disabled>
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- ===================== Advanced Search / Filters ===================== -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Tracking, Ref, Sender, Recipient..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    <?php
                    try {
                        $custList = $db->query("SELECT id, name, email FROM users WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($custList as $c):
                    ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo $customer_filter === (int)$c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name'] . ' (' . $c['email'] . ')'); ?></option>
                    <?php
                        endforeach;
                    } catch (Exception $e) {}
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (allShipmentStatuses() as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo $status_filter === $code ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="driver_id" class="form-select">
                    <option value="">All Drivers</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo $driver_filter === (int)$d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $branch_filter === (int)$b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="service_type" class="form-select">
                    <option value="">All Services</option>
                    <?php foreach (serviceTypeOptions() as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo $service_filter === $code ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select">
                    <option value="">Any Payment</option>
                    <?php foreach (['pending','paid','partial','refunded','cancelled'] as $p): ?>
                        <option value="<?php echo $p; ?>" <?php echo $payment_filter === $p ? 'selected' : ''; ?>><?php echo ucfirst($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="destination" class="form-control" placeholder="Destination city" value="<?php echo htmlspecialchars($destination_filter); ?>">
            </div>
            <div class="col-md-2">
                <select name="carrier" class="form-select">
                    <option value="">All Carriers</option>
                    <?php foreach ($carrierIntegrations as $ci): ?>
                        <option value="<?php echo $ci['id']; ?>" <?php echo (isset($_GET['carrier']) && $_GET['carrier'] == $ci['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($ci['provider'] . ' - ' . $ci['integration_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>" title="From date">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>" title="To date">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Search</button>
                <a href="shipments.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <?php if (empty($shipments)): ?>
            <div class="text-center py-5 text-muted">No shipments found</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                            <tr>
                                <th width="36"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th><a href="<?php echo sortUrl('tracking_number', $sort_by, $sort_dir); ?>" class="text-decoration-none text-dark">Tracking # <?php echo sortIcon('tracking_number', $sort_by, $sort_dir); ?></a></th>
                                <th>Sender</th>
                                <th>Recipient</th>
                                <th>Route</th>
                                <th>Service</th>
                                <th><a href="<?php echo sortUrl('status', $sort_by, $sort_dir); ?>" class="text-decoration-none text-dark">Status <?php echo sortIcon('status', $sort_by, $sort_dir); ?></a></th>
                                <th>Carrier</th>
                                <th>Payment</th>
                                <th><a href="<?php echo sortUrl('created_at', $sort_by, $sort_dir); ?>" class="text-decoration-none text-dark">Date <?php echo sortIcon('created_at', $sort_by, $sort_dir); ?></a></th>
                                <th>Action</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $sh): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input shipment-checkbox" name="shipment_ids[]" value="<?php echo $sh['id']; ?>"></td>
                                <td>
                                    <strong><a href="shipment_details.php?id=<?php echo $sh['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($sh['tracking_number']); ?></a></strong>
                                    <?php if (!empty($sh['reference_number'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($sh['reference_number']); ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($sh['sender_name'] ?? $sh['customer_name'] ?? 'N/A'); ?>
                                    <?php if (!empty($sh['sender_phone'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($sh['sender_phone']); ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($sh['receiver_name'] ?? 'N/A'); ?>
                                    <?php if (!empty($sh['receiver_phone'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($sh['receiver_phone']); ?></small><?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($sh['origin_city'] ?? '?'); ?> <i class="bi bi-arrow-right text-muted mx-1"></i> <?php echo htmlspecialchars($sh['destination_city'] ?? '?'); ?></small></td>
                                <td><?php echo ucfirst($sh['service_type'] ?? 'Standard'); ?></td>
                                <td><?php echo statusBadge($sh['status']); ?></td>
                                <td>
                                    <?php if (!empty($sh['carrier_tracking_number'])): ?>
                                        <span class="badge bg-info text-dark" title="<?php echo htmlspecialchars($sh['carrier_name'] ?? 'Carrier'); ?>">
                                            <?php echo htmlspecialchars($sh['carrier_name'] ?? 'Carrier'); ?>
                                        </span>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($sh['carrier_tracking_number']); ?></small>
                                        <?php if (!empty($sh['last_carrier_sync_at'])): ?>
                                            <br><small class="text-muted">Sync: <?php echo date('M d H:i', strtotime($sh['last_carrier_sync_at'])); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    $<?php echo number_format($sh['total_amount'] ?? 0, 2); ?>
                                    <br><small class="text-muted"><?php echo ucfirst($sh['payment_status'] ?? 'n/a'); ?></small>
                                </td>
                                <td><small><?php echo date('M d, Y', strtotime($sh['created_at'])); ?></small></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="shipment_details.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-eye me-2"></i>View Shipment</a></li>
                                            <li><a class="dropdown-item" href="panel-customer.php?cid=<?php echo (int)($sh['customer_id'] ?? 0); ?>" target="_blank"><i class="bi bi-person-badge me-2"></i>View as Customer</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="edit_shipment.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-pencil me-2"></i>Edit Shipment (Full)</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#inlineEditModal"
                                                data-id="<?php echo $sh['id']; ?>"
                                                data-sender-name="<?php echo htmlspecialchars($sh['sender_name'] ?? $sh['customer_name'] ?? ''); ?>"
                                                data-sender-phone="<?php echo htmlspecialchars($sh['sender_phone'] ?? ''); ?>"
                                                data-receiver-name="<?php echo htmlspecialchars($sh['receiver_name'] ?? ''); ?>"
                                                data-receiver-phone="<?php echo htmlspecialchars($sh['receiver_phone'] ?? ''); ?>"
                                                data-origin="<?php echo htmlspecialchars($sh['origin_city'] ?? ''); ?>"
                                                data-destination="<?php echo htmlspecialchars($sh['destination_city'] ?? ''); ?>"
                                                 data-weight="<?php echo htmlspecialchars($sh['total_weight'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($sh['status']); ?>"
                                                ><i class="bi bi-pencil-square me-2"></i>Quick Edit &amp; Transit</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#quickUpdateModal" data-id="<?php echo $sh['id']; ?>" data-tn="<?php echo htmlspecialchars($sh['tracking_number']); ?>" data-status="<?php echo htmlspecialchars($sh['status']); ?>"><i class="bi bi-truck me-2"></i>Quick Transit Update Only</a></li>
                                            <li><a class="dropdown-item" href="update_tracking.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-clock-history me-2"></i>Update Tracking</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#assignModal" data-id="<?php echo $sh['id']; ?>" data-driver="<?php echo (int)($sh['driver_id'] ?? 0); ?>" data-branch="<?php echo (int)($sh['branch_id'] ?? 0); ?>" data-vehicle="<?php echo (int)($sh['vehicle_id'] ?? 0); ?>"><i class="bi bi-person-badge me-2"></i>Assign Driver/Branch/Vehicle</a></li>
                                            <li><a class="dropdown-item" href="print_label.php?id=<?php echo $sh['id']; ?>" target="_blank"><i class="bi bi-upc me-2"></i>Print Shipping Label</a></li>
                                            <li><a class="dropdown-item" href="print_receipt.php?id=<?php echo $sh['id']; ?>" target="_blank"><i class="bi bi-receipt me-2"></i>Print Receipt</a></li>
                                            <li><a class="dropdown-item" href="receipt_pdf.php?id=<?php echo $sh['id']; ?>&download=1" target="_blank"><i class="bi bi-download me-2"></i>Download Receipt (PDF)</a></li>
                                            <li><a class="dropdown-item" href="print_manifest.php?id=<?php echo $sh['id']; ?>" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Print Manifest</a></li>
                                            <li><a class="dropdown-item" href="shipment_documents.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-paperclip me-2"></i>Upload Documents</a></li>
                                            <li><a class="dropdown-item" href="documents_manager.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-folder2-open me-2"></i>Documents Manager</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#carrierLinkModal"
                                                data-id="<?php echo $sh['id']; ?>"
                                                data-tn="<?php echo htmlspecialchars($sh['tracking_number']); ?>"
                                                data-carrier-tn="<?php echo htmlspecialchars($sh['carrier_tracking_number'] ?? ''); ?>"
                                                data-carrier-name="<?php echo htmlspecialchars($sh['carrier_name'] ?? ''); ?>"
                                                data-carrier-integration="<?php echo htmlspecialchars($sh['carrier_integration_id'] ?? ''); ?>"
                                                ><i class="bi bi-truck me-2"></i>Link Carrier Tracking</a></li>
                                            <li><a class="dropdown-item" href="billing.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-receipt me-2"></i>Billing</a></li>
                                            <li><a class="dropdown-item" href="customs.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-globe me-2"></i>Customs</a></li>
                                            <li><a class="dropdown-item" href="delivery_confirmation.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-check2-circle me-2"></i>Delivery Confirmation</a></li>
                                            <li><a class="dropdown-item" href="notifications.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-bell me-2"></i>Send Notification</a></li>
                                            <li><a class="dropdown-item" href="package_contents.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-box-seam me-2"></i>Package Contents</a></li>
                                            <li><a class="dropdown-item" href="tracking_history.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-clock-history me-2"></i>Tracking History</a></li>
                                            <li><a class="dropdown-item" href="activity_log.php?id=<?php echo $sh['id']; ?>"><i class="bi bi-journal-text me-2"></i>Activity Log</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal_<?php echo $sh['id']; ?>"><i class="bi bi-trash me-2"></i>Delete Shipment</button></li>
                                        </ul>
                                    </div>
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal_<?php echo $sh['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Delete shipment <strong><?php echo htmlspecialchars($sh['tracking_number']); ?></strong>?</p>
                                                    <p class="text-danger mb-0">This permanently removes the shipment and all related packages, tracking logs, status history, payments and delivery attempts.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_shipment">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                        <input type="hidden" name="shipment_id" value="<?php echo $sh['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Delete Shipment</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
        <?php endif; ?>
    </div>
</div>

<!-- ===================== Inline Edit & Transit ===================== -->
<div class="modal fade" id="inlineEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Quick Edit &amp; Transit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="inline_edit">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="shipment_id" id="ieShipmentId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sender Name</label>
                        <input name="sender_name" id="ieSenderName" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sender Phone</label>
                        <input name="sender_phone" id="ieSenderPhone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receiver Name</label>
                        <input name="receiver_name" id="ieReceiverName" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receiver Phone</label>
                        <input name="receiver_phone" id="ieReceiverPhone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Origin City</label>
                        <input name="origin_city" id="ieOrigin" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Destination City</label>
                        <input name="destination_city" id="ieDestination" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Weight</label>
                        <input name="weight" id="ieWeight" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" id="ieStatus" class="form-select" required><?php foreach (allShipmentStatuses() as $c => $l): ?><option value="<?php echo $c; ?>"><?php echo $l; ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tracking / Transit Location</label>
                        <select name="location" id="ieLocation" class="form-select">
                            <option value="">— Select or type custom —</option>
                            <?php foreach (transitLocationOptions() as $val => $lbl): ?>
                                <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="location_custom" id="ieLocationCustom" class="form-control mt-2" placeholder="Or enter custom location">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customs Procedure</label>
                        <select name="customs_procedure" id="ieProcedure" class="form-select">
                            <option value="">— Select or type custom —</option>
                            <?php foreach (customsProcedureOptions() as $val => $lbl): ?>
                                <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="procedure_custom" id="ieProcedureCustom" class="form-control mt-2" placeholder="Or enter custom procedure">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Event Notes <span class="text-muted">(optional)</span></label>
                        <input name="event_notes" id="ieEventNotes" class="form-control" placeholder="Internal notes, delay reasons...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Transit Activity / Remarks / Notes</label>
                        <textarea name="remarks" id="ieRemarks" class="form-control" rows="3" placeholder="Describe the transit activity, delay reason, handling notes, or any updates..."></textarea>
                    </div>
                </div>
                <div class="alert alert-light small mt-3 mb-0">Saving will update parcel details and append a new tracking event to the timeline.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Save parcel changes and transit update?');"><i class="bi bi-check-lg"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Quick Transit Update ===================== -->
<div class="modal fade" id="quickUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Quick Transit Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="shipment_id" id="quShipmentId">
                <div class="mb-2"><strong>Tracking:</strong> <span id="quTn"></span></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="new_status" id="quStatus" class="form-select" required><?php foreach (allShipmentStatuses() as $c => $l): ?><option value="<?php echo $c; ?>"><?php echo $l; ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Location / Activity</label><input name="location" id="quLocation" class="form-control" placeholder="e.g. Arrived at Chicago Hub"></div>
                <div class="mb-3"><label class="form-label">Remarks / Notes</label><textarea name="remarks" id="quRemarks" class="form-control" rows="3" placeholder="Describe the transit activity, delay reason, or handling notes..."></textarea></div>
                <div class="alert alert-light small mb-0">This will append a new tracking event to the shipment timeline and update the current status.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Save this transit update?');"><i class="bi bi-check-lg"></i> Save Update</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Assign Driver / Branch / Vehicle ===================== -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="shipment_assign.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Assign Driver, Branch &amp; Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="shipment_id" id="assignShipmentId">
                <div class="mb-3">
                    <label class="form-label">Assigned Driver / Courier</label>
                    <select name="driver_id" id="assignDriver" class="form-select">
                        <option value="0">— None —</option>
                        <?php foreach ($drivers as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name'] . ($d['employee_code'] ? ' (' . $d['employee_code'] . ')' : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assigned Branch / Hub</label>
                    <select name="branch_id" id="assignBranch" class="form-select">
                        <option value="0">— None —</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name'] . ' — ' . $b['city']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assigned Vehicle</label>
                    <select name="vehicle_id" id="assignVehicle" class="form-select">
                        <option value="0">— None —</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['registration_number'] . ($v['make'] ? ' (' . $v['make'] . ' ' . $v['model'] . ')' : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Carrier Tracking Link ===================== -->
<div class="modal fade" id="carrierLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="api/v1/admin/tracking/link-carrier.php" class="modal-content" id="carrierLinkForm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-truck me-2"></i>Link Carrier Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="link_carrier">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="shipment_id" id="clShipmentId">
                <div class="mb-3">
                    <label class="form-label">Internal Tracking #</label>
                    <input type="text" id="clTrackingNumber" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Carrier <span class="text-muted">(Integration)</span></label>
                    <select name="carrier_integration_id" id="clIntegration" class="form-select" required>
                        <option value="">— Select carrier integration —</option>
                        <?php foreach ($carrierIntegrations as $i): ?>
                            <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['provider'] . ' - ' . $i['integration_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Carrier Tracking Number</label>
                    <input type="text" name="carrier_tracking_number" id="clCarrierTn" class="form-control" required placeholder="e.g. 123456789012">
                </div>
                <div class="mb-3">
                    <label class="form-label">Carrier Name <span class="text-muted">(optional, overrides provider)</span></label>
                    <input type="text" name="carrier_name" id="clCarrierName" class="form-control" placeholder="e.g. FedEx">
                </div>
                <div class="alert alert-light small mb-0">Linking lets you sync tracking events from the carrier and ingest them automatically via webhook.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-link-45deg"></i> Link Tracking</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Bulk Delete ===================== -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Bulk Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Delete the selected shipments? This permanently removes all related data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="bulkDeleteForm">
                    <input type="hidden" name="action" value="bulk_delete">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-danger">Delete Selected</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.shipment-checkbox').forEach(cb => cb.checked = this.checked);
    document.getElementById('bulkDeleteBtn').disabled = !this.checked;
});
document.querySelectorAll('.shipment-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
        document.getElementById('bulkDeleteBtn').disabled = document.querySelectorAll('.shipment-checkbox:checked').length === 0;
    });
});
document.getElementById('bulkDeleteForm')?.addEventListener('submit', function(e) {
    const selected = document.querySelectorAll('.shipment-checkbox:checked');
    if (!selected.length) { e.preventDefault(); alert('Select at least one shipment.'); return; }
    selected.forEach(cb => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = 'shipment_ids[]'; i.value = cb.value;
        this.appendChild(i);
    });
});
// Populate carrier link modal from row data
const carrierLinkModal = document.getElementById('carrierLinkModal');
carrierLinkModal?.addEventListener('show.bs.modal', function(e) {
    const t = e.relatedTarget;
    document.getElementById('clShipmentId').value = t.getAttribute('data-id');
    document.getElementById('clTrackingNumber').value = t.getAttribute('data-tn') || '';
    document.getElementById('clCarrierTn').value = t.getAttribute('data-carrier-tn') || '';
    document.getElementById('clCarrierName').value = t.getAttribute('data-carrier-name') || '';
    const integ = t.getAttribute('data-carrier-integration') || '';
    if (integ) {
        document.getElementById('clIntegration').value = integ;
    } else {
        document.getElementById('clIntegration').selectedIndex = 0;
    }
});

// Add "Link Carrier" dropdown item to each row dynamically (fallback for rows
// rendered without it).
document.querySelectorAll('tr[data-shipment-id]').forEach(row => {
    const dropdown = row.querySelector('.dropdown-menu');
    if (dropdown && !dropdown.querySelector('[data-action="link-carrier"]')) {
        const li = document.createElement('li');
        li.innerHTML = '<a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#carrierLinkModal" data-action="link-carrier">'
            + '<i class="bi bi-truck me-2"></i>Link Carrier Tracking</a>';
        dropdown.insertBefore(li, dropdown.querySelector('.dropdown-divider') || null);
    }
});
// Populate inline edit modal from row data
const inlineEditModal = document.getElementById('inlineEditModal');
inlineEditModal?.addEventListener('show.bs.modal', function(e) {
    const t = e.relatedTarget;
    document.getElementById('ieShipmentId').value = t.getAttribute('data-id');
    document.getElementById('ieSenderName').value = t.getAttribute('data-sender-name') || '';
    document.getElementById('ieSenderPhone').value = t.getAttribute('data-sender-phone') || '';
    document.getElementById('ieReceiverName').value = t.getAttribute('data-receiver-name') || '';
    document.getElementById('ieReceiverPhone').value = t.getAttribute('data-receiver-phone') || '';
    document.getElementById('ieOrigin').value = t.getAttribute('data-origin') || '';
    document.getElementById('ieDestination').value = t.getAttribute('data-destination') || '';
    document.getElementById('ieWeight').value = t.getAttribute('data-weight') || '';
    document.getElementById('ieStatus').value = t.getAttribute('data-status') || 'in_transit';
    document.getElementById('ieLocation').value = '';
    document.getElementById('ieLocationCustom').value = '';
    document.getElementById('ieProcedure').value = '';
    document.getElementById('ieProcedureCustom').value = '';
    document.getElementById('ieEventNotes').value = '';
    document.getElementById('ieRemarks').value = '';
});
// Populate quick transit update modal from row data
const quickUpdateModal = document.getElementById('quickUpdateModal');
quickUpdateModal?.addEventListener('show.bs.modal', function(e) {
    const t = e.relatedTarget;
    document.getElementById('quShipmentId').value = t.getAttribute('data-id');
    document.getElementById('quTn').textContent = t.getAttribute('data-tn') || '';
    document.getElementById('quStatus').value = t.getAttribute('data-status') || 'in_transit';
    document.getElementById('quLocation').value = '';
    document.getElementById('quRemarks').value = '';
});

// Inline edit modal dropdown/custom mutual exclusivity
const ieLocation = document.getElementById('ieLocation');
const ieLocationCustom = document.getElementById('ieLocationCustom');
const ieProcedure = document.getElementById('ieProcedure');
const ieProcedureCustom = document.getElementById('ieProcedureCustom');
ieLocation?.addEventListener('change', function() { if (this.value) ieLocationCustom.value = ''; });
ieLocationCustom?.addEventListener('input', function() { if (this.value.trim()) ieLocation.value = ''; });
ieProcedure?.addEventListener('change', function() { if (this.value) ieProcedureCustom.value = ''; });
ieProcedureCustom?.addEventListener('input', function() { if (this.value.trim()) ieProcedure.value = ''; });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

