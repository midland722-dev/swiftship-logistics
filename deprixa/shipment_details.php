<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$db = getDB();
$shipment_id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { header('Location: shipments.php'); exit; }
$GLOBALS['shipment'] = $shipment;

ensureShipmentColumns($db);
ensureAdvancedShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);
ensureModuleTables($db);
try {
    $db->exec("CREATE TABLE IF NOT EXISTS package_items (
        id int(11) NOT NULL AUTO_INCREMENT,
        shipment_id int(11) NOT NULL,
        item_name varchar(255) NOT NULL,
        category varchar(120) DEFAULT NULL,
        quantity int(11) DEFAULT 1,
        weight decimal(10,2) DEFAULT 0,
        declared_value decimal(12,2) DEFAULT 0,
        serial_number varchar(120) DEFAULT NULL,
        is_fragile tinyint(1) DEFAULT 0,
        is_dangerous tinyint(1) DEFAULT 0,
        created_at timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id),
        KEY shipment_id (shipment_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

// Reload with ensured columns.
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM packages WHERE shipment_id = :id LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$stmt = $db->prepare("SELECT * FROM shipment_status_history_v2 WHERE shipment_id = :id ORDER BY occurred_at DESC");
$stmt->execute([':id' => $shipment_id]);
$tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT * FROM attachments WHERE entity_type='shipment' AND entity_id=:id ORDER BY created_at DESC");
$stmt->execute([':id' => $shipment_id]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT * FROM package_items WHERE shipment_id=:id ORDER BY id ASC");
$stmt->execute([':id' => $shipment_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$stmt = $db->prepare("SELECT * FROM delivery_confirmations WHERE shipment_id=:id ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$confirmations = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$stmt = $db->prepare("SELECT * FROM refunds WHERE shipment_id=:id ORDER BY created_at DESC");
$stmt->execute([':id' => $shipment_id]);
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$stmt = $db->prepare("SELECT * FROM shipment_notifications WHERE shipment_id=:id ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$notifications = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$driverName = 'N/A';
if (!empty($shipment['driver_id'])) {
    $stmt = $db->prepare("SELECT name FROM drivers WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $shipment['driver_id']]);
    $driverName = $stmt->fetchColumn() ?: 'N/A';
}
$vehicleName = 'N/A';
if (!empty($shipment['vehicle_id'])) {
    $stmt = $db->prepare("SELECT name FROM vehicles WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $shipment['vehicle_id']]);
    $vehicleName = $stmt->fetchColumn() ?: 'N/A';
}
$branchName = $shipment['branch_id']
    ? ($shipment['current_branch'] ?? 'N/A')
    : ($shipment['current_branch'] ?? 'N/A');

$msg = $_GET['msg'] ?? '';
$msgText = match ($msg) {
    'updated' => 'Shipment updated.', 'assigned' => 'Assignment saved.', 'deleted' => 'Shipment deleted.',
    'progress_updated' => 'Progress updated.', 'error' => 'Could not save.', default => ''
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_progress') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msgText = 'Invalid security token.'; $msg = 'error';
    } else {
        $percent = isset($_POST['progress_percent']) ? (int) $_POST['progress_percent'] : null;
        $steps = [];
        foreach ($_POST['progress_steps'] ?? [] as $key => $state) {
            if (in_array($state, ['complete','current','pending'], true)) {
                $steps[$key] = $state;
            }
        }
        $stepsJson = json_encode($steps);
        $db->prepare("UPDATE shipments SET progress_percent = :p, progress_steps = :s WHERE id = :id")
            ->execute([':p' => $percent, ':s' => $stepsJson, ':id' => $shipment_id]);
        if (function_exists('clearDashboardCache')) { clearDashboardCache(); }
        $msg = 'progress_updated';
        $msgText = 'Progress updated.';
        header('Location: shipment_details.php?id=' . $shipment_id . '&msg=progress_updated');
        exit;
    }
}

$f = function($k, $d='N/A') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
$yesno = function($v) { return !empty($GLOBALS['shipment'][$v]) ? 'Yes' : 'No'; };
$row = function($label, $val) { echo '<div class="col-md-6 col-lg-4"><span class="text-muted small d-block">'.$label.'</span><strong>'.$val.'</strong></div>'; };
$currency = $shipment['currency'] ?? 'USD';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/flash.php';
?>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <a href="shipments.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            <h4 class="d-inline ms-2 mb-0">Shipment <?php echo $f('tracking_number'); ?></h4>
            <?php echo statusBadge($shipment['status']); ?>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="edit_shipment.php?id=<?php echo $shipment_id; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            <a href="update_tracking.php?id=<?php echo $shipment_id; ?>" class="btn btn-warning btn-sm"><i class="bi bi-clock-history"></i> Update Tracking</a>
            <a href="assign_courier.php?id=<?php echo $shipment_id; ?>" class="btn btn-info btn-sm"><i class="bi bi-person-badge"></i> Assign</a>
            <a href="documents_manager.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-paperclip"></i> Docs</a>
            <a href="billing.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt"></i> Billing</a>
            <a href="customs.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-globe"></i> Customs</a>
            <a href="notifications.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bell"></i> Notify</a>
            <a href="delivery_confirmation.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-circle"></i> Delivery</a>
            <a href="package_contents.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-seam"></i> Contents</a>
            <a href="activity_log.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-text"></i> Log</a>
            <a href="print_label.php?id=<?php echo $shipment_id; ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upc"></i> Label</a>
            <a href="tracking_history.php?id=<?php echo $shipment_id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> History</a>
        </div>
    </div>

    <?php if ($msgText): ?><div class="alert alert-success alert-dismissible fade show"><?php echo $msgText; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <ul class="nav nav-tabs mb-3" id="shipTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t1">Summary</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t2">Parties</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t3">Parcel</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t4">Courier</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t5">Payment</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t6">Customs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t7">Docs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t8">Timeline</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t9">Progress</button></li>
    </ul>

    <div class="tab-content">
        <!-- Summary -->
        <div class="tab-pane fade show active" id="t1">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card mb-3"><div class="card-header">Shipment Summary</div><div class="card-body row g-2">
                        <?php $row('Tracking Number', $f('tracking_number')); ?>
                        <?php $row('Current Status', statusLabel($shipment['status'])); ?>
                        <?php $row('Current Location', htmlspecialchars($shipment['current_city'] ?? 'N/A') . ($shipment['current_country'] ? ' (' . htmlspecialchars($shipment['current_country']) . ')' : '')); ?>
                        <?php $row('Reference', $f('reference_number')); ?>
                        <?php $row('Type', ucfirst($f('shipment_type'))); ?>
                        <?php $row('Service', ucfirst($f('service_type'))); ?>
                        <?php $row('Priority', ucfirst($f('priority'))); ?>
                        <?php $row('Shipment Date', $shipment['shipment_date'] && $shipment['shipment_date']!=='N/A' ? date('M d, Y', strtotime($shipment['shipment_date'])) : 'N/A'); ?>
                        <?php $row('Estimated Delivery', !empty($shipment['estimated_delivery']) ? date('M d, Y', strtotime($shipment['estimated_delivery'])) : 'N/A'); ?>
                        <?php $row('Route', htmlspecialchars($shipment['origin_city'] ?? '?') . ' → ' . htmlspecialchars($shipment['destination_city'] ?? '?')); ?>
                        <?php $row('Active', $yesno('is_active')); ?>
                        <?php $row('On Hold', $yesno('is_on_hold')); ?>
                        <?php $row('Archived', $yesno('is_archived')); ?>
                    </div></div>
                    <div class="card"><div class="card-header">Shipping Details</div><div class="card-body row g-2">
                        <?php $row('Origin', htmlspecialchars($shipment['sender_city'] ?? '') . ', ' . htmlspecialchars($shipment['sender_country'] ?? '')); ?>
                        <?php $row('Destination', htmlspecialchars($shipment['receiver_city'] ?? '') . ', ' . htmlspecialchars($shipment['receiver_country'] ?? '')); ?>
                        <?php $row('Pickup Date', $shipment['pickup_date'] ? date('M d, Y', strtotime($shipment['pickup_date'])) : 'N/A'); ?>
                        <?php $row('Delivery Date', $shipment['delivery_date'] ? date('M d, Y', strtotime($shipment['delivery_date'])) : 'N/A'); ?>
                        <?php $row('Delivery Time', $f('delivery_time')); ?>
                        <?php $row('Route', $f('route')); ?>
                    </div></div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-3"><div class="card-header">Barcode / QR</div><div class="card-body text-center">
                        <?php $bc = $shipment['barcode_path'] ?? ''; $qr = $shipment['qr_code_path'] ?? ''; ?>
                        <?php if ($bc): ?><img src="../<?php echo htmlspecialchars($bc); ?>" class="img-fluid mb-2" style="max-height:80px;"><?php endif; ?>
                        <?php if ($qr): ?><img src="../<?php echo htmlspecialchars($qr); ?>" class="img-fluid" style="max-width:150px;"><?php endif; ?>
                        <p class="mt-2 text-muted small"><?php echo $f('tracking_number'); ?></p>
                        <a href="documents_manager.php?id=<?php echo $shipment_id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-upc-scan"></i> Manage Docs</a>
                    </div></div>
                    <div class="card"><div class="card-header">Quick Status</div><div class="card-body small">
                        Delivery: <?php echo $shipment['delivery_date'] ? date('M d, Y', strtotime($shipment['delivery_date'])) : 'N/A'; ?><br>
                        POD: <?php echo $yesno('pod_photo'); ?><br>
                        Signature: <?php echo $yesno('signature_image'); ?>
                    </div></div>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="tab-pane fade" id="t2">
            <div class="row g-3">
                <div class="col-md-6"><div class="card"><div class="card-header bg-light">Sender</div><div class="card-body small">
                    <p><strong><?php echo $f('sender_name'); ?></strong></p>
                    <p>Company: <?php echo $f('sender_company'); ?></p>
                    <p>Phone: <?php echo $f('sender_phone'); ?></p>
                    <p>Email: <?php echo $f('sender_email'); ?></p>
                    <p>Address: <?php echo $f('sender_address'); ?>, <?php echo $f('sender_city'); ?> <?php echo $f('sender_state'); ?> <?php echo $f('sender_postal'); ?> <?php echo $f('sender_country'); ?></p>
                </div></div></div>
                <div class="col-md-6"><div class="card"><div class="card-header bg-light">Receiver</div><div class="card-body small">
                    <p><strong><?php echo $f('receiver_name'); ?></strong></p>
                    <p>Company: <?php echo $f('receiver_company'); ?></p>
                    <p>Phone: <?php echo $f('receiver_phone'); ?></p>
                    <p>Email: <?php echo $f('receiver_email'); ?></p>
                    <p>Address: <?php echo $f('receiver_address'); ?>, <?php echo $f('receiver_city'); ?> <?php echo $f('receiver_state'); ?> <?php echo $f('receiver_postal'); ?> <?php echo $f('receiver_country'); ?></p>
                </div></div></div>
                <div class="col-12"><div class="card"><div class="card-header">Notes</div><div class="card-body row g-2">
                    <div class="col-md-6"><strong>Internal Notes:</strong><div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?php echo $f('internal_notes'); ?></div></div>
                    <div class="col-md-6"><strong>Customer Notes:</strong><div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?php echo $f('customer_notes'); ?></div></div>
                    <div class="col-12"><strong>Special Instructions:</strong> <?php echo $f('special_instructions'); ?></div>
                    <div class="col-12"><strong>Delivery Preferences:</strong> Signature <?php echo $yesno('signature_required'); ?> · Contact <?php echo $yesno('contact_before_delivery'); ?> · Leave at Door <?php echo $yesno('leave_at_door'); ?> · Weekend <?php echo $yesno('weekend_delivery'); ?> · <?php echo $f('preferred_delivery_time'); ?></div>
                </div></div></div>
            </div>
        </div>

        <!-- Parcel -->
        <div class="tab-pane fade" id="t3">
            <div class="card"><div class="card-header">Parcel Information</div><div class="card-body row g-2">
                <?php $row('Description', $f('package_name')); ?>
                <?php $row('Category', ucfirst($f('item_category'))); ?>
                <?php $row('Pieces', $f('pieces', 1)); ?>
                <?php $row('Weight', $f('total_weight') . ' kg'); ?>
                <?php $row('Dimensions', $f('length') . ' × ' . $f('width') . ' × ' . $f('height') . ' cm'); ?>
                <?php $row('Volumetric', $f('volumetric_weight') . ' kg'); ?>
                <?php $row('Declared Value', $currency . ' ' . number_format($shipment['declared_value'] ?? 0, 2)); ?>
                <?php $row('COD Amount', $currency . ' ' . number_format($shipment['cod_amount'] ?? 0, 2)); ?>
                <?php $row('Fragile', $yesno('is_fragile')); ?>
                <?php $row('Hazardous', $yesno('is_hazardous')); ?>
                <?php $row('Insured', $yesno('is_insured') . ($shipment['insurance_amount'] ? ' (' . $currency . ' ' . number_format($shipment['insurance_amount'],2) . ')' : '')); ?>
                <div class="col-12"><strong>Contents:</strong> <?php echo $f('contents'); ?></div>
                <div class="col-12"><strong>Description:</strong> <?php echo $f('package_description'); ?></div>
            </div></div>
            <div class="card mt-3"><div class="card-header">Package Contents (<?php echo count($items); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Item</th><th>Category</th><th>Qty</th><th>Weight</th><th>Value</th><th>Serial</th><th>Flags</th></tr></thead>
                <tbody><?php if (empty($items)): ?><tr><td colspan="7" class="text-center text-muted py-3">No line items.</td></tr>
                <?php else: foreach ($items as $it): ?><tr><td><?php echo htmlspecialchars($it['item_name']); ?></td><td><?php echo htmlspecialchars($it['category'] ?? ''); ?></td><td><?php echo $it['quantity']; ?></td><td><?php echo $it['weight']; ?> kg</td><td><?php echo number_format($it['declared_value'],2); ?></td><td><?php echo htmlspecialchars($it['serial_number'] ?? ''); ?></td><td><?php echo $it['is_fragile']?'<span class="badge bg-warning text-dark">Fragile</span> ':''; ?><?php echo $it['is_dangerous']?'<span class="badge bg-danger">DG</span>':''; ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
            </div></div>
        </div>

        <!-- Courier -->
        <div class="tab-pane fade" id="t4">
            <div class="card"><div class="card-header">Assigned Courier</div><div class="card-body row g-2">
                <?php $row('Driver', htmlspecialchars($driverName)); ?>
                <?php $row('Vehicle', htmlspecialchars($vehicleName)); ?>
                <?php $row('Branch / Hub', htmlspecialchars($branchName)); ?>
                <?php $row('Warehouse', $f('warehouse')); ?>
                <?php $row('Distribution Center', $f('distribution_center')); ?>
                <?php $row('Route', $f('route')); ?>
                <?php $row('Current City', $f('current_city')); ?>
                <?php $row('Current Country', $f('current_country')); ?>
            </div></div>
            <div class="card mt-3"><div class="card-header">Delivery Confirmation</div><div class="card-body row g-2">
                <?php if ($confirmations): ?>
                    <?php $row('Receiver', htmlspecialchars($confirmations['receiver_name'] ?? 'N/A')); ?>
                    <?php $row('Delivered', htmlspecialchars($confirmations['delivery_date'] ?? '') . ' ' . htmlspecialchars($confirmations['delivery_time'] ?? '')); ?>
                    <?php $row('GPS', ($confirmations['gps_lat'] ?? '').', '.($confirmations['gps_lng'] ?? '')); ?>
                    <div class="col-12"><strong>Courier Notes:</strong> <?php echo htmlspecialchars($confirmations['courier_notes'] ?? ''); ?></div>
                    <div class="col-12"><strong>Feedback:</strong> <?php echo htmlspecialchars($confirmations['customer_feedback'] ?? ''); ?></div>
                <?php else: ?>
                    <div class="col-12 text-muted">No delivery confirmation recorded yet.</div>
                <?php endif; ?>
            </div></div>
        </div>

        <!-- Payment -->
        <div class="tab-pane fade" id="t5">
            <div class="card"><div class="card-header">Payment & Billing</div><div class="card-body row g-2">
                <?php $row('Shipping Cost', $currency . ' ' . number_format($shipment['shipping_cost'] ?? 0,2)); ?>
                <?php $row('Additional Charges', $currency . ' ' . number_format($shipment['additional_charges'] ?? 0,2)); ?>
                <?php $row('Discount', $currency . ' ' . number_format($shipment['discount'] ?? 0,2)); ?>
                <?php $row('Tax', $currency . ' ' . number_format($shipment['tax'] ?? 0,2)); ?>
                <?php $row('Total Amount', $currency . ' ' . number_format($shipment['total_amount'] ?? 0,2)); ?>
                <?php $row('Payment Method', ucfirst(str_replace('_',' ',$f('payment_method')))); ?>
                <?php $row('Payment Status', ucfirst($f('payment_status'))); ?>
                <?php $row('Transaction ID', $f('transaction_id')); ?>
                <?php $row('Invoice Number', $f('invoice_number')); ?>
            </div></div>
            <div class="card mt-3"><div class="card-header">Refund History (<?php echo count($refunds); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Amount</th><th>Reason</th></tr></thead>
                <tbody><?php if (empty($refunds)): ?><tr><td colspan="3" class="text-center text-muted py-3">No refunds.</td></tr>
                <?php else: foreach ($refunds as $r): ?><tr><td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td><td><?php echo $currency; ?> <?php echo number_format($r['amount'],2); ?></td><td><?php echo htmlspecialchars($r['reason'] ?? ''); ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
            </div></div>
        </div>

        <!-- Customs -->
        <div class="tab-pane fade" id="t6">
            <div class="card"><div class="card-header">Customs Information</div><div class="card-body row g-2">
                <?php $row('Customs Status', ucfirst(str_replace('_',' ',$f('customs_status')))); ?>
                <?php $row('Clearance Office', $f('clearance_office')); ?>
                <?php $row('Clearance Date', !empty($shipment['clearance_date']) ? date('M d, Y', strtotime($shipment['clearance_date'])) : 'N/A'); ?>
                <?php $row('Declaration #', $f('customs_declaration_number')); ?>
                <?php $row('HS Code', $f('hs_code')); ?>
                <?php $row('Country of Origin', $f('country_of_origin')); ?>
                <?php $row('Import Duty', $currency . ' ' . number_format($shipment['import_duty'] ?? 0,2)); ?>
                <?php $row('Tax Info', $f('tax_info')); ?>
                <div class="col-12"><strong>Documents:</strong> <?php echo $f('customs_documents'); ?></div>
                <div class="col-12"><strong>Remarks:</strong> <?php echo $f('customs_remarks'); ?></div>
            </div></div>
            <a href="customs.php?id=<?php echo $shipment_id; ?>" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-pencil"></i> Edit Customs</a>
        </div>

        <!-- Docs -->
        <div class="tab-pane fade" id="t7">
            <div class="card"><div class="card-header d-flex justify-content-between"><span>Uploaded Documents (<?php echo count($attachments); ?>)</span>
                <a href="documents_manager.php?id=<?php echo $shipment_id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-upload"></i> Manage</a></div>
                <div class="card-body"><?php if (empty($attachments)): ?><p class="text-muted">None.</p>
                <?php else: ?><div class="list-group"><?php foreach ($attachments as $a): ?><div class="list-group-item"><i class="bi bi-file-earmark me-2"></i><a href="download.php?id=<?php echo $a['id']; ?>" target="_blank"><?php echo htmlspecialchars($a['original_name']); ?></a> <small class="text-muted"><?php echo htmlspecialchars($a['doc_type'] ?? ''); ?></small></div><?php endforeach; ?></div><?php endif; ?></div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="tab-pane fade" id="t8">
            <div class="card"><div class="card-header d-flex justify-content-between"><span>Tracking Timeline</span>
                <a href="tracking_history.php?id=<?php echo $shipment_id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-list-ul"></i> Full History</a></div>
                <div class="card-body"><?php if (empty($tracking)): ?><p class="text-muted">No tracking history yet.</p>
                <?php else: ?><div class="timeline"><?php foreach ($tracking as $i => $r): ?>
                    <div class="timeline-item <?php echo $i===0?'active':''; ?>"><div class="timeline-marker"></div><div class="timeline-content">
                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars(statusLabel($r['status_code'])); ?></h6>
                        <p class="mb-1"><?php echo htmlspecialchars($r['remarks'] ?? ''); ?></p>
                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($r['occurred_at'])); ?><?php if (!empty($r['location'])): ?> — <?php echo htmlspecialchars($r['location']); ?><?php endif; ?></small>
                    </div></div>
                <?php endforeach; ?></div><?php endif; ?></div>
            </div>
        </div>

        <!-- Progress -->
        <div class="tab-pane fade" id="t9">
            <div class="card"><div class="card-header">Manual Shipment Progress</div><div class="card-body">
                <form method="POST" action="" class="row g-3">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="action" value="update_progress">
                    <div class="col-md-4">
                        <label class="form-label">Overall Progress (%)</label>
                        <input type="number" name="progress_percent" class="form-control" min="0" max="100" value="<?php echo (int)($shipment['progress_percent'] ?? 0); ?>">
                        <div class="form-text">Set 0–100. Leave blank to auto-calculate from steps.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Step Status</label>
                        <?php
                        $steps = [
                            'created' => 'Shipment Created',
                            'picked_up' => 'Picked Up',
                            'sorting' => 'At Sorting Center',
                            'in_transit' => 'In Transit',
                            'customs' => 'Customs Clearance',
                            'destination_hub' => 'Arrived at Destination Hub',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                        ];
                        $currentSteps = [];
                        if (!empty($shipment['progress_steps'])) {
                            $decoded = json_decode($shipment['progress_steps'], true);
                            if (is_array($decoded)) { $currentSteps = $decoded; }
                        }
                        foreach ($steps as $key => $label):
                            $state = $currentSteps[$key] ?? 'pending';
                        ?>
                        <div class="form-check form-check-inline me-3">
                            <input class="form-check-input" type="radio" name="progress_steps[<?php echo $key; ?>]" value="complete" id="ps_<?php echo $key; ?>_c" <?php echo $state==='complete'?'checked':''; ?>>
                            <label class="form-check-label" for="ps_<?php echo $key; ?>_c"><?php echo $label; ?> ✓</label>
                        </div>
                        <div class="form-check form-check-inline me-3">
                            <input class="form-check-input" type="radio" name="progress_steps[<?php echo $key; ?>]" value="current" id="ps_<?php echo $key; ?>_n" <?php echo $state==='current'?'checked':''; ?>>
                            <label class="form-check-label" for="ps_<?php echo $key; ?>_n"><?php echo $label; ?> ⟳</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="progress_steps[<?php echo $key; ?>]" value="pending" id="ps_<?php echo $key; ?>_p" <?php echo $state==='pending'?'checked':''; ?>>
                            <label class="form-check-label" for="ps_<?php echo $key; ?>_p"><?php echo $label; ?> –</label>
                        </div>
                        <br>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Progress</button>
                        <a href="shipment_details.php?id=<?php echo $shipment_id; ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$shipment = $GLOBALS['shipment'];
require_once __DIR__ . '/includes/quick_actions.php';
require_once __DIR__ . '/includes/footer.php';
