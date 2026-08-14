<?php
/**
 * Edit Shipment — comprehensive courier-grade editor (DHL / FedEx / UPS style).
 *
 * Design rules (per requirements):
 *  - Editing a shipment MODIFIES SHIPMENT INFORMATION ONLY. It never appends or
 *    overwrites tracking history. Tracking is handled by update_tracking.php.
 *  - Tracking Number is editable ONLY by Super Admin; other roles see it locked.
 *  - Four save actions are provided: Save Changes, Save & Continue Editing,
 *    Save and Notify Customer, Cancel.
 *  - All 14 information sections are organized in responsive Bootstrap tabs.
 *  - Documents (6 categories) can be uploaded, replaced, previewed and deleted.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/lib/ShipmentGenerator.php';
require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/validation.php';

$page_title = 'Edit Shipment - ' . SITE_NAME;
$db = getDB();
requirePermission('edit_shipment');
ensureShipmentColumns($db);
ensureAdvancedShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);
ensureAttachmentDocType($db);
$shipmentGenerator = new ShipmentGenerator($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) {
    echo '<div class="alert alert-danger m-4">Shipment not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$stmt = $db->prepare("SELECT * FROM packages WHERE shipment_id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$GLOBALS['shipment'] = $shipment;

$drivers = fetchDrivers($db);
$vehicles = fetchVehicles($db);
$branches = fetchBranches($db);
$docTypes = shipmentDocumentTypes();

// Existing documents grouped by doc_type.
$attachments = $db->prepare("SELECT * FROM attachments WHERE entity_type='shipment' AND entity_id=:id ORDER BY created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);
$docsByType = [];
foreach ($attachments as $a) {
    $k = $a['doc_type'] ?? 'other';
    $docsByType[$k][] = $a;
}

$superAdmin = isSuperAdmin();
$message = '';
$message_type = '';

// ---- Delete a single document (GET with csrf) ----
if (isset($_GET['delete_doc']) && isset($_GET['csrf'])) {
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['csrf'])) {
        $aid = intval($_GET['delete_doc']);
        $stmt = $db->prepare("SELECT file_path FROM attachments WHERE id=:id AND entity_type='shipment' AND entity_id=:eid LIMIT 1");
        $stmt->execute([':id' => $aid, ':eid' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            @unlink(__DIR__ . '/../' . $row['file_path']);
            $db->prepare("DELETE FROM attachments WHERE id=:id")->execute([':id' => $aid]);
            header('Location: edit_shipment.php?id=' . $id . '&tab=docs&deleted=1');
            exit;
        }
    }
}
if (isset($_GET['deleted'])) {
    $message = 'Document removed.';
    $message_type = 'success';
}

// ---- Handle POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $errors = [];
        $fields = [
            'reference_number'   => trim($_POST['reference_number'] ?? ''),
            'shipment_type'      => trim($_POST['shipment_type'] ?? 'parcel'),
            'service_type'       => trim($_POST['service_type'] ?? 'standard'),
            'priority'           => trim($_POST['priority'] ?? 'standard'),
            'shipment_date'      => trim($_POST['shipment_date'] ?? null) ?: null,
            'pickup_date'        => trim($_POST['pickup_date'] ?? null) ?: null,
            'estimated_delivery' => trim($_POST['estimated_delivery'] ?? null) ?: null,
            'sender_name'        => trim($_POST['sender_name'] ?? ''),
            'sender_company'     => trim($_POST['sender_company'] ?? ''),
            'sender_phone'       => trim($_POST['sender_phone'] ?? ''),
            'sender_email'       => trim($_POST['sender_email'] ?? ''),
            'sender_address'     => trim($_POST['sender_address'] ?? ''),
            'sender_city'        => trim($_POST['sender_city'] ?? ''),
            'sender_state'       => trim($_POST['sender_state'] ?? ''),
            'sender_postal'      => trim($_POST['sender_postal'] ?? ''),
            'sender_country'     => trim($_POST['sender_country'] ?? 'US'),
            'receiver_name'      => trim($_POST['receiver_name'] ?? ''),
            'receiver_company'   => trim($_POST['receiver_company'] ?? ''),
            'receiver_phone'     => trim($_POST['receiver_phone'] ?? ''),
            'receiver_email'     => trim($_POST['receiver_email'] ?? ''),
            'receiver_address'   => trim($_POST['receiver_address'] ?? ''),
            'receiver_city'      => trim($_POST['receiver_city'] ?? ''),
            'receiver_state'     => trim($_POST['receiver_state'] ?? ''),
            'receiver_postal'    => trim($_POST['receiver_postal'] ?? ''),
            'receiver_country'   => trim($_POST['receiver_country'] ?? 'US'),
            'package_name'       => trim($_POST['package_name'] ?? ''),
            'item_category'      => trim($_POST['item_category'] ?? ''),
            'package_description'=> trim($_POST['package_description'] ?? ''),
            'declared_value'     => max(0, floatval($_POST['declared_value'] ?? 0)),
            'total_weight'       => max(0, floatval($_POST['total_weight'] ?? 0)),
            'length'             => max(0, floatval($_POST['length'] ?? 0)),
            'width'              => max(0, floatval($_POST['width'] ?? 0)),
            'height'             => max(0, floatval($_POST['height'] ?? 0)),
            'volumetric_weight'  => max(0, floatval($_POST['volumetric_weight'] ?? 0)),
            'contents'           => trim($_POST['contents'] ?? ''),
            'pieces'             => max(1, intval($_POST['pieces'] ?? 1)),
            'is_fragile'         => isset($_POST['is_fragile']) ? 1 : 0,
            'is_hazardous'       => isset($_POST['is_hazardous']) ? 1 : 0,
            'is_insured'         => isset($_POST['is_insured']) ? 1 : 0,
            'insurance_amount'   => max(0, floatval($_POST['insurance_amount'] ?? 0)),
            'cod_amount'         => max(0, floatval($_POST['cod_amount'] ?? 0)),
            'currency'           => trim($_POST['currency'] ?? 'USD'),
            'origin_country'     => trim($_POST['sender_country'] ?? 'US'),
            'origin_city'        => trim($_POST['sender_city'] ?? ''),
            'destination_country'=> trim($_POST['receiver_country'] ?? 'US'),
            'destination_city'   => trim($_POST['receiver_city'] ?? ''),
            'shipping_cost'      => max(0, floatval($_POST['shipping_cost'] ?? 0)),
            'additional_charges' => max(0, floatval($_POST['additional_charges'] ?? 0)),
            'discount'           => max(0, floatval($_POST['discount'] ?? 0)),
            'tax'                => max(0, floatval($_POST['tax'] ?? 0)),
            'total_amount'       => max(0, floatval($_POST['total_amount'] ?? 0)),
            'route'              => trim($_POST['route'] ?? ''),
            'warehouse'          => trim($_POST['warehouse'] ?? ''),
            'distribution_center'=> trim($_POST['distribution_center'] ?? ''),
            'driver_id'          => !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : null,
            'vehicle_id'         => !empty($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null,
            'branch_id'          => !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null,
            'current_city'       => trim($_POST['current_city'] ?? ''),
            'current_country'    => trim($_POST['current_country'] ?? ''),
            'delivery_date'      => trim($_POST['delivery_date'] ?? null) ?: null,
            'delivery_time'      => trim($_POST['delivery_time'] ?? null) ?: null,
            'payment_method'     => trim($_POST['payment_method'] ?? 'cash'),
            'payment_status'     => trim($_POST['payment_status'] ?? 'pending'),
            'transaction_id'     => trim($_POST['transaction_id'] ?? ''),
            'invoice_number'     => trim($_POST['invoice_number'] ?? ''),
            'signature_required' => isset($_POST['signature_required']) ? 1 : 0,
            'contact_before_delivery' => isset($_POST['contact_before_delivery']) ? 1 : 0,
            'leave_at_door'      => isset($_POST['leave_at_door']) ? 1 : 0,
            'weekend_delivery'   => isset($_POST['weekend_delivery']) ? 1 : 0,
            'preferred_delivery_time' => trim($_POST['preferred_delivery_time'] ?? ''),
            'special_instructions' => trim($_POST['special_instructions'] ?? ''),
            'customer_notes'     => trim($_POST['customer_notes'] ?? ''),
            'internal_notes'     => trim($_POST['internal_notes'] ?? ''),
            'customs_declaration_number' => trim($_POST['customs_declaration_number'] ?? ''),
            'hs_code'            => trim($_POST['hs_code'] ?? ''),
            'country_of_origin'  => trim($_POST['country_of_origin'] ?? null) ?: null,
            'import_duty'        => trim($_POST['import_duty'] ?? '') === '' ? null : max(0, floatval($_POST['import_duty'])),
            'customs_documents'  => trim($_POST['customs_documents'] ?? ''),
            'tax_info'           => trim($_POST['tax_info'] ?? ''),
            'transit_location'   => trim($_POST['transit_location'] ?? ''),
            'customs_procedure'  => trim($_POST['customs_procedure'] ?? ''),
            'is_active'          => isset($_POST['is_active']) ? 1 : 0,
            'is_on_hold'         => isset($_POST['is_on_hold']) ? 1 : 0,
            'return_to_sender'   => isset($_POST['return_to_sender']) ? 1 : 0,
            'is_cancelled'       => isset($_POST['is_cancelled']) ? 1 : 0,
            'is_archived'        => isset($_POST['is_archived']) ? 1 : 0,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        // Super Admin may edit the tracking number.
        if ($superAdmin) {
            $newTn = strtoupper(trim($_POST['tracking_number'] ?? ''));
            if ($newTn === '') {
                $errors[] = 'Tracking Number is required.';
            } else {
                $dup = $db->prepare("SELECT id FROM shipments WHERE tracking_number = :t AND id <> :id LIMIT 1");
                $dup->execute([':t' => $newTn, ':id' => $id]);
                $dup = $dup->fetch(PDO::FETCH_ASSOC);
                if ($dup) {
                    $errors[] = 'Tracking Number already exists for another shipment.';
                } else {
                    $fields['tracking_number'] = $newTn;
                }
            }
        }

        // Validation.
        $centralValidation = validateShipmentData($fields, true);
        if (!$centralValidation['valid']) {
            $errors = array_merge($errors, $centralValidation['errors']);
        }
        if ($fields['sender_name'] === '') { $errors[] = 'Sender Full Name is required.'; }
        if ($fields['receiver_name'] === '') { $errors[] = 'Receiver Full Name is required.'; }
        if ($fields['sender_email'] !== '' && !filter_var($fields['sender_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Sender Email is invalid.';
        }
        if ($fields['receiver_email'] !== '' && !filter_var($fields['receiver_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Receiver Email is invalid.';
        }
        if ($fields['receiver_email'] !== '' && !filter_var($fields['receiver_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Receiver Email is invalid.';
        }
        if ($fields['receiver_email'] !== '' && !filter_var($fields['receiver_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Receiver Email is invalid.';
        }

        if (!empty($errors)) {
            $message = implode(' ', $errors);
            $message_type = 'danger';
            $shipment = array_merge($shipment, $fields);
        } else {
            try {
                $db->beginTransaction();

                // Tracking number may have changed (super admin only) — update directly.
                if ($superAdmin && isset($fields['tracking_number'])) {
                    $db->prepare("UPDATE shipments SET tracking_number = :t, updated_at = NOW() WHERE id = :id")
                        ->execute([':t' => $fields['tracking_number'], ':id' => $id]);
                }

                $shipmentColumns = [
                    'tracking_number','reference_number','status','service_type','priority',
                    'origin_country','origin_city','destination_country','destination_city',
                    'total_weight','total_volume','declared_value','currency','pieces',
                    'is_fragile','is_insured','insurance_amount','payment_status','payment_method','total_amount',
                    'notes','special_instructions','estimated_delivery','actual_delivery',
                    'shipment_type','shipment_date','pickup_date','sender_name','sender_company','sender_phone','sender_email','sender_address','sender_city','sender_state','sender_postal','sender_country',
                    'receiver_name','receiver_company','receiver_phone','receiver_email','receiver_address','receiver_city','receiver_state','receiver_postal','receiver_country',
                    'package_name','item_category','package_description','volumetric_weight','contents',
                    'cod_amount','driver_id','vehicle_id','branch_id','current_city','current_country','current_branch','delivery_date','delivery_time','pod_photo','internal_notes',
                    'customs_declaration_number','country_of_origin','import_duty','customs_documents','tax_info','is_active','is_on_hold','return_to_sender','is_cancelled','is_archived','updated_at',
                    'shipping_cost','additional_charges','discount','tax','route','warehouse','distribution_center',
                    'signature_required','contact_before_delivery','leave_at_door','weekend_delivery','preferred_delivery_time','customer_notes',
                    'hs_code','transit_location','customs_procedure'
                ];
                $cols = array_values(array_intersect(array_keys($fields), $shipmentColumns));
                $sql = "UPDATE shipments SET " . implode(', ', array_map(fn($c) => "$c = :$c", $cols)) . " WHERE id = :id";
                $params = [':id' => $id];
                foreach ($cols as $c) { $params[":$c"] = $fields[$c]; }
                $db->prepare($sql)->execute($params);

                $pkgCols = $db->query("SHOW COLUMNS FROM packages LIKE 'weight'")->fetchColumn();
                if ($pkgCols) {
                    $vol = ($fields['length'] * $fields['width'] * $fields['height']) / 5000;
                if ($package) {
                    $db->prepare("
                        UPDATE packages SET weight = :w, length = :l, width = :wi, height = :h, volume_weight = :v, description = :d, updated_at = NOW()
                        WHERE shipment_id = :id
                    ")->execute([
                        ':w' => $fields['total_weight'], ':l' => $fields['length'], ':wi' => $fields['width'],
                        ':h' => $fields['height'], ':v' => $vol, ':d' => $fields['package_description'], ':id' => $id,
                    ]);
                } else {
                    $db->prepare("
                        INSERT INTO packages (shipment_id, package_number, weight, length, width, height, volume_weight, description, status, created_at)
                        VALUES (:id, :pn, :w, :l, :wi, :h, :v, :d, 'pending', NOW())
                    ")->execute([
                        ':id' => $id, ':pn' => 'PKG-' . str_pad($id, 6, '0', STR_PAD_LEFT),
                        ':w' => $fields['total_weight'], ':l' => $fields['length'], ':wi' => $fields['width'],
                        ':h' => $fields['height'], ':v' => $vol, ':d' => $fields['package_description'],
                    ]);
                }
                }

                // Handle document uploads (6 categories).
                $uploadDir = __DIR__ . '/../uploads/attachments/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','csv','zip'];
                foreach ($docTypes as $dk => $dlabel) {
                    if (!empty($_FILES['doc_' . $dk]) && $_FILES['doc_' . $dk]['error'] === UPLOAD_ERR_OK) {
                        $f = $_FILES['doc_' . $dk];
                        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowed, true)) {
                            $safe = $shipment['tracking_number'] . '_' . $dk . '_' . time() . '.' . $ext;
                            $dest = $uploadDir . $safe;
                            if (move_uploaded_file($f['tmp_name'], $dest)) {
                                $rel = 'uploads/attachments/' . $safe;
                                $db->prepare("
                                    INSERT INTO attachments (entity_type, entity_id, filename, original_name, file_path, mime_type, file_size, doc_type, uploaded_by, access_level, created_at)
                                    VALUES ('shipment', :eid, :fn, :on, :fp, :mt, :fs, :dt, :ub, 'internal', NOW())
                                ")->execute([
                                    ':eid' => $id, ':fn' => $safe, ':on' => $f['name'], ':fp' => $rel,
                                    ':mt' => $f['type'], ':fs' => $f['size'], ':dt' => $dk,
                                    ':ub' => $_SESSION['admin_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                $db->commit();
                clearDashboardCache();

                // Reload fresh data.
                $stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT * FROM packages WHERE shipment_id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                // Regenerate receipt from the updated shipment data.
                try {
                    require_once __DIR__ . '/lib/ShipmentGenerator.php';
                    $gen = new ShipmentGenerator($db);
                    $shipment['receipt_number'] = $shipment['receipt_number'] ?? ('REC-' . $shipment['id']);
                    $path = $gen->generatePDFReceipt($shipment);
                    if ($gen->columnExists('shipments', 'pdf_receipt_path')) {
                        $rel = 'uploads/receipts/' . basename($path);
                        $db->prepare("UPDATE shipments SET pdf_receipt_path = :p WHERE id = :id")
                            ->execute([':p' => $rel, ':id' => $shipment['id']]);
                    }
                } catch (Exception $e) {
                    // best-effort; receipt can be regenerated from print_receipt.php
                }

                // Notify customer if requested (with receipt PDF attached).
                $notify = ($_POST['save_action'] ?? '') === 'notify';
                if ($notify) {
                    $cust = $db->prepare("SELECT u.email, u.name FROM shipments s LEFT JOIN users u ON s.customer_id = u.id WHERE s.id = :id LIMIT 1")
                        ->fetch(PDO::FETCH_ASSOC);
                    if (!empty($cust['email'])) {
                        try {
                            require_once __DIR__ . '/lib/ShipmentGenerator.php';
                            $gen = new ShipmentGenerator($db);
                            $shipment['receipt_number'] = $shipment['receipt_number'] ?? ('REC-' . $shipment['id']);
                            $gen->emailReceipt($shipment, $cust['email']);
                        } catch (Exception $e) {
                            @sendMail($cust['email'], 'Your shipment ' . $shipment['tracking_number'] . ' has been updated', 'Shipment updated. Track at ' . ($_SERVER['HTTP_ORIGIN'] ?? '') . '/shp/track.php?cons_no=' . urlencode($shipment['tracking_number']));
                        }
                    }
                    try {
                        $db->prepare("INSERT INTO notifications (user_id, type, title, message, action_url, icon, created_at)
                            VALUES (:uid, 'system', :title, :msg, :url, 'pencil-square', NOW())")
                            ->execute([
                                ':uid' => $shipment['customer_id'] ?? null,
                                ':title' => 'Shipment ' . $shipment['tracking_number'] . ' updated',
                                ':msg' => 'Your shipment details were updated by our team.',
                                ':url' => '/shp/track.php?cons_no=' . urlencode($shipment['tracking_number']),
                            ]);
                    } catch (Exception $e) { /* ignore */ }
                }

                $message = $notify ? 'Shipment updated and customer notified.' : 'Shipment updated successfully.';
                $message_type = 'success';

                $action = $_POST['save_action'] ?? 'save';
                if ($action === 'save') {
                    header('Location: shipment_details.php?id=' . $id . '&msg=updated');
                    exit;
                }
                // 'continue' / 'notify' => stay on page.
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Edit shipment failed: ' . $e->getMessage());
                $message = 'An error occurred while updating the shipment. Please try again.';
                $message_type = 'danger';
                $shipment = array_merge($shipment, $fields);
            }
        }
    }
}

$v = function($k, $d = '') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
$checked = function($k) { return !empty($GLOBALS['shipment'][$k]) ? 'checked' : ''; };
$sel = function($k, $val) { return ($GLOBALS['shipment'][$k] ?? '') == $val ? 'selected' : ''; };
$tab = $_GET['tab'] ?? '';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show m-3" role="alert">
    <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="edit-shipment-form" id="editForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <!-- Sticky action bar -->
    <div class="edit-actionbar">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
                <span class="ms-2 fw-semibold"><?php echo $v('tracking_number'); ?></span>
                <?php echo statusBadge($shipment['status']); ?>
                <?php if (!$superAdmin): ?><span class="badge bg-secondary ms-1" title="Tracking Number is locked for non-Super Admins"><i class="bi bi-lock-fill"></i> Tracking locked</span><?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" name="save_action" value="continue" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Save &amp; Continue</button>
                <button type="submit" name="save_action" value="notify" class="btn btn-outline-info btn-sm"><i class="bi bi-bell"></i> Save &amp; Notify</button>
                <button type="submit" name="save_action" value="save" class="btn btn-success btn-sm" onclick="return confirm('Save changes to this shipment?');"><i class="bi bi-check-lg"></i> Save Changes</button>
                <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Cancel</a>
            </div>
        </div>
    </div>

    <!-- Section tabs -->
    <ul class="nav nav-tabs mb-3 edit-tabs" id="editTabs" role="tablist">
        <li class="nav-item"><button class="nav-link <?php echo $tab===''?'active':''; ?>" data-bs-toggle="tab" data-bs-target="#t-basic" type="button">1. Basic</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-sender" type="button">2. Sender</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-receiver" type="button">3. Receiver</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-parcel" type="button">4. Parcel</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-shipping" type="button">5. Shipping</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-courier" type="button">6. Courier</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-payment" type="button">7. Payment</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-delivery" type="button">8. Delivery Prefs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-customs" type="button">9. Customs</button></li>
        <li class="nav-item"><button class="nav-link <?php echo $tab==='docs'?'active':''; ?>" data-bs-toggle="tab" data-bs-target="#t-docs" type="button">10. Documents</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-inotes" type="button">11. Internal</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-cnotes" type="button">12. Customer</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-settings" type="button">13. Settings</button></li>
    </ul>

    <div class="tab-content">
        <!-- 1. Basic -->
        <div class="tab-pane fade <?php echo $tab===''?'show active':''; ?>" id="t-basic">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-info-circle me-2"></i>Basic Shipment Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tracking Number <?php if($superAdmin): ?><span class="text-danger">*</span><?php endif; ?></label>
                        <?php if ($superAdmin): ?>
                            <input name="tracking_number" class="form-control" value="<?php echo $v('tracking_number'); ?>" required>
                        <?php else: ?>
                            <input class="form-control" value="<?php echo $v('tracking_number'); ?>" readonly tabindex="-1">
                            <small class="text-muted">Editable by Super Admin only.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4"><label class="form-label">Reference Number</label><input name="reference_number" class="form-control" value="<?php echo $v('reference_number'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Shipment Date</label><input type="date" name="shipment_date" class="form-control" value="<?php echo $v('shipment_date'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Shipping Service</label><select name="service_type" class="form-select"><?php foreach (serviceTypeOptions() as $c=>$l): ?><option value="<?php echo $c; ?>" <?php echo $sel('service_type',$c); ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Shipment Type</label><select name="shipment_type" class="form-select"><?php foreach (shipmentTypeOptions() as $c=>$l): ?><option value="<?php echo $c; ?>" <?php echo $sel('shipment_type',$c); ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Priority Level</label><select name="priority" class="form-select"><?php foreach (['low','standard','high','express'] as $p): ?><option value="<?php echo $p; ?>" <?php echo $sel('priority',$p); ?>><?php echo ucfirst($p); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Estimated Delivery Date</label><input type="date" name="estimated_delivery" class="form-control" value="<?php echo $v('estimated_delivery'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Currency</label><select name="currency" class="form-select"><?php foreach (currencyOptions() as $c): ?><option value="<?php echo $c; ?>" <?php echo $sel('currency',$c); ?>><?php echo $c; ?></option><?php endforeach; ?></select></div>
                </div>
            </div>
        </div>

        <!-- 2. Sender -->
        <div class="tab-pane fade" id="t-sender">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-person me-2"></i>Sender Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Full Name <span class="text-danger">*</span></label><input name="sender_name" class="form-control" value="<?php echo $v('sender_name'); ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Company Name</label><input name="sender_company" class="form-control" value="<?php echo $v('sender_company'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Phone Number</label><input name="sender_phone" class="form-control" value="<?php echo $v('sender_phone'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Email Address</label><input type="email" name="sender_email" class="form-control" value="<?php echo $v('sender_email'); ?>"></div>
                    <div class="col-12"><label class="form-label">Street Address</label><input name="sender_address" class="form-control" value="<?php echo $v('sender_address'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">City</label><input name="sender_city" class="form-control" value="<?php echo $v('sender_city'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">State/Province</label><input name="sender_state" class="form-control" value="<?php echo $v('sender_state'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Postal Code</label><input name="sender_postal" class="form-control" value="<?php echo $v('sender_postal'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Country</label><input name="sender_country" class="form-control" value="<?php echo $v('sender_country','US'); ?>"></div>
                </div>
            </div>
        </div>

        <!-- 3. Receiver -->
        <div class="tab-pane fade" id="t-receiver">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-person-check me-2"></i>Receiver Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Full Name <span class="text-danger">*</span></label><input name="receiver_name" class="form-control" value="<?php echo $v('receiver_name'); ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Company Name</label><input name="receiver_company" class="form-control" value="<?php echo $v('receiver_company'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Phone Number</label><input name="receiver_phone" class="form-control" value="<?php echo $v('receiver_phone'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Email Address</label><input type="email" name="receiver_email" class="form-control" value="<?php echo $v('receiver_email'); ?>"></div>
                    <div class="col-12"><label class="form-label">Delivery Address</label><input name="receiver_address" class="form-control" value="<?php echo $v('receiver_address'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">City</label><input name="receiver_city" class="form-control" value="<?php echo $v('receiver_city'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">State/Province</label><input name="receiver_state" class="form-control" value="<?php echo $v('receiver_state'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Postal Code</label><input name="receiver_postal" class="form-control" value="<?php echo $v('receiver_postal'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Country</label><input name="receiver_country" class="form-control" value="<?php echo $v('receiver_country','US'); ?>"></div>
                </div>
            </div>
        </div>

        <!-- 4. Parcel -->
        <div class="tab-pane fade" id="t-parcel">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-box-seam me-2"></i>Parcel Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Parcel Description</label><input name="package_name" class="form-control" value="<?php echo $v('package_name'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Item Category</label><select name="item_category" class="form-select"><option value="">— Select —</option><?php foreach (itemCategoryOptions() as $c=>$l): ?><option value="<?php echo $c; ?>" <?php echo $sel('item_category',$c); ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Number of Packages</label><input type="number" min="1" name="pieces" class="form-control" value="<?php echo $v('pieces',1); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Weight (kg)</label><input type="number" step="0.1" name="total_weight" class="form-control" value="<?php echo $v('total_weight'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Length (cm)</label><input type="number" step="0.1" name="length" class="form-control" value="<?php echo $v('length'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Width (cm)</label><input type="number" step="0.1" name="width" class="form-control" value="<?php echo $v('width'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Height (cm)</label><input type="number" step="0.1" name="height" class="form-control" value="<?php echo $v('height'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Volumetric Weight</label><input type="number" step="0.1" name="volumetric_weight" class="form-control" value="<?php echo $v('volumetric_weight'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Declared Value</label><input type="number" step="0.01" name="declared_value" class="form-control" value="<?php echo $v('declared_value'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Insurance Amount</label><input type="number" step="0.01" name="insurance_amount" class="form-control" value="<?php echo $v('insurance_amount'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">COD Amount</label><input type="number" step="0.01" name="cod_amount" class="form-control" value="<?php echo $v('cod_amount'); ?>"></div>
                    <div class="col-12"><label class="form-label">Contents Description</label><textarea name="package_description" class="form-control" rows="2"><?php echo $v('package_description'); ?></textarea></div>
                    <div class="col-12"><label class="form-label">Contents (comma separated)</label><input name="contents" class="form-control" value="<?php echo $v('contents'); ?>"></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_fragile" id="f1" <?php echo $checked('is_fragile'); ?>><label class="form-check-label" for="f1">Fragile</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_hazardous" id="f2" <?php echo $checked('is_hazardous'); ?>><label class="form-check-label" for="f2">Hazardous Goods</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_insured" id="f3" <?php echo $checked('is_insured'); ?>><label class="form-check-label" for="f3">Insured</label></div>
                </div>
            </div>
        </div>

        <!-- 5. Shipping Details -->
        <div class="tab-pane fade" id="t-shipping">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-truck me-2"></i>Shipping Details</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Origin</label><input class="form-control" value="<?php echo $v('sender_city'); ?>, <?php echo $v('sender_country'); ?>" readonly></div>
                    <div class="col-md-4"><label class="form-label">Destination</label><input class="form-control" value="<?php echo $v('receiver_city'); ?>, <?php echo $v('receiver_country'); ?>" readonly></div>
                    <div class="col-md-4"><label class="form-label">Pickup Date</label><input type="date" name="pickup_date" class="form-control" value="<?php echo $v('pickup_date'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Shipping Cost</label><input type="number" step="0.01" name="shipping_cost" id="shipping_cost" class="form-control" value="<?php echo $v('shipping_cost'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Additional Charges</label><input type="number" step="0.01" name="additional_charges" id="additional_charges" class="form-control" value="<?php echo $v('additional_charges'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" id="discount" class="form-control" value="<?php echo $v('discount'); ?>"></div>
                    <div class="col-md-3"><label class="form-label">Tax</label><input type="number" step="0.01" name="tax" id="tax" class="form-control" value="<?php echo $v('tax'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Total Amount (auto)</label><input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="<?php echo $v('total_amount'); ?>"></div>
                </div>
            </div>
        </div>

        <!-- 6. Assigned Courier -->
        <div class="tab-pane fade" id="t-courier">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-person-badge me-2"></i>Assigned Courier</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Assigned Driver/Courier</label><select name="driver_id" class="form-select"><option value="0">— None —</option><?php foreach($drivers as $d): ?><option value="<?php echo $d['id']; ?>" <?php echo ($shipment['driver_id']??0)==$d['id']?'selected':''; ?>><?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Vehicle Number</label><select name="vehicle_id" class="form-select"><option value="0">— None —</option><?php foreach($vehicles as $ve): ?><option value="<?php echo $ve['id']; ?>" <?php echo ($shipment['vehicle_id']??0)==$ve['id']?'selected':''; ?>><?php echo htmlspecialchars($ve['registration_number']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Warehouse</label><input name="warehouse" class="form-control" value="<?php echo $v('warehouse'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Route</label><input name="route" class="form-control" value="<?php echo $v('route'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Distribution Center</label><input name="distribution_center" class="form-control" value="<?php echo $v('distribution_center'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Branch / Hub</label><select name="branch_id" class="form-select"><option value="0">— None —</option><?php foreach($branches as $b): ?><option value="<?php echo $b['id']; ?>" <?php echo ($shipment['branch_id']??0)==$b['id']?'selected':''; ?>><?php echo htmlspecialchars($b['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Current City</label><input name="current_city" class="form-control" value="<?php echo $v('current_city'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Current Country</label><input name="current_country" class="form-control" value="<?php echo $v('current_country'); ?>"></div>
                </div>
            </div>
        </div>

        <!-- 7. Payment -->
        <div class="tab-pane fade" id="t-payment">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-credit-card me-2"></i>Payment Information</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Payment Status</label><select name="payment_status" class="form-select"><?php foreach(['pending','paid','refunded','partial','cancelled'] as $p): ?><option value="<?php echo $p; ?>" <?php echo $sel('payment_status',$p); ?>><?php echo ucfirst($p); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><?php foreach(['cash','bank_transfer','card','mobile_money','wallet','invoice'] as $m): ?><option value="<?php echo $m; ?>" <?php echo $sel('payment_method',$m); ?>><?php echo ucfirst(str_replace('_',' ',$m)); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Transaction ID</label><input name="transaction_id" class="form-control" value="<?php echo $v('transaction_id'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Invoice Number</label><input name="invoice_number" class="form-control" value="<?php echo $v('invoice_number'); ?>"></div>
                </div>
            </div>
        </div>

        <!-- 8. Delivery Preferences -->
        <div class="tab-pane fade" id="t-delivery">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-sliders me-2"></i>Delivery Preferences</div>
                <div class="card-body row g-3">
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="signature_required" id="d1" <?php echo $checked('signature_required'); ?>><label class="form-check-label" for="d1">Signature Required</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="contact_before_delivery" id="d2" <?php echo $checked('contact_before_delivery'); ?>><label class="form-check-label" for="d2">Contact Before Delivery</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="leave_at_door" id="d3" <?php echo $checked('leave_at_door'); ?>><label class="form-check-label" for="d3">Leave at Door</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="weekend_delivery" id="d4" <?php echo $checked('weekend_delivery'); ?>><label class="form-check-label" for="d4">Weekend Delivery</label></div>
                    <div class="col-md-4"><label class="form-label">Preferred Delivery Time</label><input name="preferred_delivery_time" class="form-control" value="<?php echo $v('preferred_delivery_time'); ?>" placeholder="e.g. 9AM - 12PM"></div>
                    <div class="col-12"><label class="form-label">Special Delivery Instructions</label><textarea name="special_instructions" class="form-control" rows="2"><?php echo $v('special_instructions'); ?></textarea></div>
                </div>
            </div>
        </div>

        <!-- 9. Customs -->
        <div class="tab-pane fade" id="t-customs">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-globe me-2"></i>Customs Information (International)</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Customs Declaration Number</label><input name="customs_declaration_number" class="form-control" value="<?php echo $v('customs_declaration_number'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">HS Code</label><input name="hs_code" class="form-control" value="<?php echo $v('hs_code'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Country of Origin</label><input name="country_of_origin" class="form-control" value="<?php echo $v('country_of_origin'); ?>"></div>
                    <div class="col-md-4"><label class="form-label">Import Duty</label><input type="number" step="0.01" name="import_duty" class="form-control" value="<?php echo $v('import_duty'); ?>"></div>
                    <div class="col-md-4">
                        <label class="form-label">Transit Location</label>
                        <select name="transit_location" class="form-select">
                            <option value="">— Select or type —</option>
                            <?php foreach (transitLocationOptions() as $tlv => $tll): ?>
                                <option value="<?php echo htmlspecialchars($tlv); ?>" <?php echo ($v('transit_location')===$tlv?'selected':''); ?>><?php echo htmlspecialchars($tll); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customs Procedure</label>
                        <select name="customs_procedure" class="form-select">
                            <option value="">— Select or type —</option>
                            <?php foreach (customsProcedureOptions() as $cpv => $cpl): ?>
                                <option value="<?php echo htmlspecialchars($cpv); ?>" <?php echo ($v('customs_procedure')===$cpv?'selected':''); ?>><?php echo htmlspecialchars($cpl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Customs Documents</label><textarea name="customs_documents" class="form-control" rows="2"><?php echo $v('customs_documents'); ?></textarea></div>
                    <div class="col-12"><label class="form-label">Tax Information</label><textarea name="tax_info" class="form-control" rows="2"><?php echo $v('tax_info'); ?></textarea></div>
                </div>
            </div>
        </div>

        <!-- 10. Documents -->
        <div class="tab-pane fade <?php echo $tab==='docs'?'show active':''; ?>" id="t-docs">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-paperclip me-2"></i>Documents</div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($docTypes as $dk => $dlabel): ?>
                        <div class="col-md-6">
                            <div class="doc-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><i class="bi bi-file-earmark me-1"></i><?php echo $dlabel; ?></strong>
                                </div>
                                <?php if (!empty($docsByType[$dk])): ?>
                                    <ul class="list-group list-group-flush mb-2">
                                    <?php foreach ($docsByType[$dk] as $a): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>
                                                 <a href="download.php?id=<?php echo $a['id']; ?>" target="_blank"><?php echo htmlspecialchars($a['original_name']); ?></a>
                                                <br><small class="text-muted"><?php echo number_format(($a['file_size']??0)/1024,1); ?> KB</small>
                                            </span>
                                            <span class="d-flex gap-1">
                                                 <a href="download.php?id=<?php echo $a['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
                                                <a href="edit_shipment.php?id=<?php echo $id; ?>&tab=docs&delete_doc=<?php echo $a['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this document?');" title="Remove"><i class="bi bi-trash"></i></a>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <label class="form-label small mb-1">Upload / Replace</label>
                                <input type="file" name="doc_<?php echo $dk; ?>" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.csv,.zip">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 11. Internal Notes -->
        <div class="tab-pane fade" id="t-inotes">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-lock me-2"></i>Internal Notes (Admin &amp; Staff only)</div>
                <div class="card-body">
                    <textarea name="internal_notes" class="form-control" rows="6" placeholder="Private notes visible only to administrators and staff..."><?php echo $v('internal_notes'); ?></textarea>
                </div>
            </div>
        </div>

        <!-- 12. Customer Notes -->
        <div class="tab-pane fade" id="t-cnotes">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-chat-left-text me-2"></i>Customer Notes</div>
                <div class="card-body">
                    <textarea name="customer_notes" class="form-control" rows="6" placeholder="Notes that may be shown to the customer..."><?php echo $v('customer_notes'); ?></textarea>
                </div>
            </div>
        </div>

        <!-- 13. Settings -->
        <div class="tab-pane fade" id="t-settings">
            <div class="card mb-3"><div class="card-header"><i class="bi bi-gear me-2"></i>Shipment Settings</div>
                <div class="card-body row g-3">
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_active" id="s1" <?php echo $checked('is_active'); ?>><label class="form-check-label" for="s1">Active</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_on_hold" id="s2" <?php echo $checked('is_on_hold'); ?>><label class="form-check-label" for="s2">Hold Shipment</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="return_to_sender" id="s3" <?php echo $checked('return_to_sender'); ?>><label class="form-check-label" for="s3">Return to Sender</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_cancelled" id="s4" <?php echo $checked('is_cancelled'); ?>><label class="form-check-label" for="s4">Cancel Shipment</label></div>
                    <div class="col-md-3 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_archived" id="s5" <?php echo $checked('is_archived'); ?>><label class="form-check-label" for="s5">Archive Shipment</label></div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$shipment = $GLOBALS['shipment'];
require_once __DIR__ . '/includes/quick_actions.php';
?>
<script>
// Auto-calculate Total Amount from cost / charges / discount / tax.
(function () {
    const ids = ['shipping_cost','additional_charges','discount','tax'];
    const total = document.getElementById('total_amount');
    function recalc() {
        let t = 0;
        ids.forEach(function (i) {
            const el = document.getElementById(i);
            const val = parseFloat(el && el.value ? el.value : 0) || 0;
            t += val;
        });
        // discount is subtracted: sum cost+charges+tax, minus discount
        t = (parseFloat(document.getElementById('shipping_cost').value||0)||0)
          + (parseFloat(document.getElementById('additional_charges').value||0)||0)
          + (parseFloat(document.getElementById('tax').value||0)||0)
          - (parseFloat(document.getElementById('discount').value||0)||0);
        if (total) total.value = Math.max(0, t).toFixed(2);
    }
    ids.forEach(function (i) {
        const el = document.getElementById(i);
        if (el) el.addEventListener('input', recalc);
    });
    recalc();
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
