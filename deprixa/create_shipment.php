<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/lib/ShipmentGenerator.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/validation.php';

$page_title = 'Create Shipment - ' . SITE_NAME;
$db = getDB();
ensureShipmentColumns($db);
ensureShipmentStatusEnum($db);
require_once __DIR__ . '/../includes/tracking.php';
$message = '';
$message_type = '';
$shipmentGenerator = new ShipmentGenerator($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking_number = $shipmentGenerator->generateTrackingNumber();
    $receipt_number = $shipmentGenerator->generateReceiptNumber();
    $reference_number = trim($_POST['reference_number'] ?? '');
    if ($reference_number === '') {
        $reference_number = 'REF-' . strtoupper(substr(md5($tracking_number . microtime()), 0, 10));
    }
    $shipment_date = trim($_POST['shipment_date'] ?? date('Y-m-d'));
    $expected_delivery = trim($_POST['expected_delivery'] ?? null);
    $shipment_type = trim($_POST['shipment_type'] ?? 'parcel');
    $service_type = trim($_POST['service_type'] ?? 'standard');
    $priority = trim($_POST['priority'] ?? 'standard');
    $status = trim($_POST['status'] ?? 'pending');
    
    $sender_name = trim($_POST['sender_name'] ?? '');
    $sender_company = trim($_POST['sender_company'] ?? '');
    $sender_phone = trim($_POST['sender_phone'] ?? '');
    $sender_email = trim($_POST['sender_email'] ?? '');
    $sender_address = trim($_POST['sender_address'] ?? '');
    $sender_city = trim($_POST['sender_city'] ?? '');
    $sender_state = trim($_POST['sender_state'] ?? '');
    $sender_postal = trim($_POST['sender_postal'] ?? '');
    $sender_country = trim($_POST['sender_country'] ?? 'US');
    
    $receiver_name = trim($_POST['receiver_name'] ?? '');
    $receiver_company = trim($_POST['receiver_company'] ?? '');
    $receiver_phone = trim($_POST['receiver_phone'] ?? '');
    $receiver_email = trim($_POST['receiver_email'] ?? '');
    $receiver_address = trim($_POST['receiver_address'] ?? '');
    $receiver_city = trim($_POST['receiver_city'] ?? '');
    $receiver_state = trim($_POST['receiver_state'] ?? '');
    $receiver_postal = trim($_POST['receiver_postal'] ?? '');
    $receiver_country = trim($_POST['receiver_country'] ?? 'US');
    
    $package_name = trim($_POST['package_name'] ?? '');
    $package_description = trim($_POST['package_description'] ?? '');
    $contents_raw = $_POST['contents'] ?? '';
    $contents_extra = trim($_POST['contents_extra'] ?? '');
    if (is_array($contents_raw)) {
        $contents = implode(', ', $contents_raw);
    } else {
        $contents = trim($contents_raw);
    }
    if ($contents_extra !== '') {
        $contents = $contents === '' ? $contents_extra : $contents . ', ' . $contents_extra;
    }
    $cod_amount = max(0, floatval($_POST['cod_amount'] ?? 0));
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $weight = max(0, floatval($_POST['weight'] ?? 0));
    $length = max(0, floatval($_POST['length'] ?? 0));
    $width = max(0, floatval($_POST['width'] ?? 0));
    $height = max(0, floatval($_POST['height'] ?? 0));
    
    $declared_value = max(0, floatval($_POST['declared_value'] ?? 0));
    $insurance_amount = max(0, floatval($_POST['insurance_amount'] ?? 0));
    $is_fragile = isset($_POST['is_fragile']) ? 1 : 0;
    $is_insured = isset($_POST['is_insured']) ? 1 : 0;
    $signature_required = isset($_POST['signature_required']) ? 1 : 0;
    $weekend_delivery = isset($_POST['weekend_delivery']) ? 1 : 0;
    $return_service = isset($_POST['return_service']) ? 1 : 0;
    $temperature_controlled = isset($_POST['temperature_controlled']) ? 1 : 0;
    
    $payment_method = trim($_POST['payment_method'] ?? 'cash');
    $payment_status = trim($_POST['payment_status'] ?? 'pending');
    $notes = trim($_POST['notes'] ?? '');
    $special_instructions = trim($_POST['special_instructions'] ?? '');
    
    $base_cost = max(0, floatval($_POST['base_cost'] ?? 20));
    $weight_charge = max(0, floatval($_POST['weight_charge'] ?? 0));
    $insurance_cost = max(0, floatval($_POST['insurance_cost'] ?? 0));
    $tax_amount = max(0, floatval($_POST['tax_amount'] ?? 0));
    $total_cost = max(0, floatval($_POST['total_cost'] ?? 0));
    
    $validation = validateShipmentData([
        'tracking_number' => $tracking_number,
        'sender_name' => $sender_name,
        'sender_company' => $sender_company,
        'sender_phone' => $sender_phone,
        'sender_email' => $sender_email,
        'sender_address' => $sender_address,
        'sender_city' => $sender_city,
        'sender_state' => $sender_state,
        'sender_postal' => $sender_postal,
        'sender_country' => $sender_country,
        'receiver_name' => $receiver_name,
        'receiver_company' => $receiver_company,
        'receiver_phone' => $receiver_phone,
        'receiver_email' => $receiver_email,
        'receiver_address' => $receiver_address,
        'receiver_city' => $receiver_city,
        'receiver_state' => $receiver_state,
        'receiver_postal' => $receiver_postal,
        'receiver_country' => $receiver_country,
        'origin_city' => $sender_city,
        'origin_country' => $sender_country,
        'destination_city' => $receiver_city,
        'destination_country' => $receiver_country,
        'package_name' => $package_name,
        'total_weight' => $weight,
        'pieces' => $quantity,
        'length' => $length,
        'width' => $width,
        'height' => $height,
        'declared_value' => $declared_value,
        'insurance_amount' => $insurance_amount,
        'service_type' => $service_type,
        'priority' => $priority,
        'status' => $status,
        'shipment_date' => $shipment_date,
        'estimated_delivery' => $expected_delivery,
        'payment_status' => $payment_status,
        'payment_method' => $payment_method,
        'total_amount' => $total_cost,
    ]);
    if (!$validation['valid']) {
        $message = 'Validation errors: ' . implode(' ', $validation['errors']);
        $message_type = 'danger';
    } elseif (empty($sender_name) || empty($receiver_name) || empty($package_name) || $weight <= 0) {
        $message = 'Please fill in all required fields (Sender, Receiver, Package Name, Weight).';
        $message_type = 'danger';
    } else {
        try {
            $db->beginTransaction();
            
            // Insert shipment
            $columns = [
                'tracking_number', 'reference_number', 'status', 'shipment_type', 'service_type', 'priority',
                'shipment_date', 'origin_country', 'origin_city', 'destination_country', 'destination_city',
                'sender_name', 'sender_company', 'sender_phone', 'sender_email', 'sender_address',
                'sender_state', 'sender_postal', 'sender_country',
                'receiver_name', 'receiver_company', 'receiver_phone', 'receiver_email', 'receiver_address',
                'receiver_state', 'receiver_postal', 'receiver_country',
                'package_name', 'package_description', 'contents', 'length', 'width', 'height',
                'total_weight', 'declared_value', 'cod_amount', 'currency', 'pieces',
                'is_fragile', 'is_insured', 'insurance_amount',
                'payment_status', 'payment_method', 'total_amount',
                'notes', 'special_instructions', 'estimated_delivery',
                'created_by'
            ];
            $placeholders = array_map(fn($c) => ":$c", $columns);
            $values = [
                ':tracking_number' => $tracking_number,
                ':reference_number' => $reference_number,
                ':status' => $status,
                ':shipment_type' => $shipment_type,
                ':service_type' => $service_type,
                ':priority' => $priority,
                ':shipment_date' => $shipment_date ?: null,
                ':origin_country' => $sender_country,
                ':origin_city' => $sender_city,
                ':destination_country' => $receiver_country,
                ':destination_city' => $receiver_city,
                ':sender_name' => $sender_name,
                ':sender_company' => $sender_company,
                ':sender_phone' => $sender_phone,
                ':sender_email' => $sender_email,
                ':sender_address' => $sender_address,
                ':sender_state' => $sender_state,
                ':sender_postal' => $sender_postal,
                ':sender_country' => $sender_country,
                ':receiver_name' => $receiver_name,
                ':receiver_company' => $receiver_company,
                ':receiver_phone' => $receiver_phone,
                ':receiver_email' => $receiver_email,
                ':receiver_address' => $receiver_address,
                ':receiver_state' => $receiver_state,
                ':receiver_postal' => $receiver_postal,
                ':receiver_country' => $receiver_country,
                ':package_name' => $package_name,
                ':package_description' => $package_description,
                ':contents' => $contents,
                ':length' => $length,
                ':width' => $width,
                ':height' => $height,
                ':total_weight' => $weight,
                ':declared_value' => $declared_value,
                ':cod_amount' => $cod_amount,
                ':currency' => 'USD',
                ':pieces' => $quantity,
                ':is_fragile' => $is_fragile,
                ':is_insured' => $is_insured ? 1 : 0,
                ':insurance_amount' => $insurance_amount,
                ':payment_status' => $payment_status,
                ':payment_method' => $payment_method,
                ':total_amount' => $total_cost,
                ':notes' => $notes,
                ':special_instructions' => $special_instructions,
                ':estimated_delivery' => $expected_delivery ?: null,
                ':created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Add receipt_number if column exists
            if ($shipmentGenerator->columnExists('shipments', 'receipt_number')) {
                $columns[] = 'receipt_number';
                $placeholders[] = ':receipt_number';
                $values[':receipt_number'] = $receipt_number;
            }
            
            $sql = "INSERT INTO shipments (" . implode(', ', $columns) . ", created_at) 
                    VALUES (" . implode(', ', $placeholders) . ", NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
            
            $shipment_id = $db->lastInsertId();
            
            // Insert package
            $stmt = $db->prepare("
                INSERT INTO packages (
                    shipment_id, package_number, weight, length, width, height,
                    volume_weight, description, status, created_at
                ) VALUES (
                    :shipment_id, :package_number, :weight, :length, :width, :height,
                    :volume_weight, :description, :status, NOW()
                )
            ");
            
            $volume = ($length * $width * $height) / 5000;
            $package_number = (int)str_replace('PKG-', '', 'PKG-' . str_pad($shipment_id, 6, '0', STR_PAD_LEFT));
            $stmt->execute([
                ':shipment_id' => $shipment_id,
                ':package_number' => $package_number,
                ':weight' => $weight,
                ':length' => $length,
                ':width' => $width,
                ':height' => $height,
                ':volume_weight' => $volume,
                ':description' => $package_description,
                ':status' => $status
            ]);
            
            // Insert status history
            $stmt = $db->prepare("
                INSERT INTO shipment_status_history_v2 (
                    shipment_id, status_code, occurred_at, location, remarks, occurred_by
                ) VALUES (
                    :shipment_id, :status_code, NOW(), :location, :remarks, :occurred_by
                )
            ");
            $stmt->execute([
                ':shipment_id' => $shipment_id,
                ':status_code' => $status,
                ':location' => $sender_city,
                ':remarks' => 'Shipment created',
                ':occurred_by' => $_SESSION['admin_id'] ?? null
            ]);
            
            // Insert tracking log
            $stmt = $db->prepare("
                INSERT INTO tracking_logs (
                    tracking_number, shipment_id, status, location, description, is_public, occurred_at
                ) VALUES (
                    :tracking_number, :shipment_id, :status, :location, :description, 1, NOW()
                )
            ");
            $stmt->execute([
                ':tracking_number' => $tracking_number,
                ':shipment_id' => $shipment_id,
                ':status' => $status,
                ':location' => $sender_city,
                ':description' => 'Shipment created'
            ]);

            // Canonical public tracking event so the customer can track the
            // shipment immediately, with a location-aware starting point.
            try {
                ensureTrackingHistory($db);
                $loc = deriveTrackingLocation($status, [
                    'origin_city'      => $sender_city,
                    'destination_city' => $receiver_city,
                ]);
                addTrackingEvent(
                    $db, $shipment_id, $tracking_number, $status, $loc,
                    'Shipment created' . ($status !== 'pending' ? ' (' . statusLabel($status) . ')' : ''),
                    'System'
                );
            } catch (Exception $e) {
                // best-effort: tracking store is optional for creation
            }
            
            // Reload full shipment data for document generation
            $stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $shipment_id]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$shipment) {
                throw new RuntimeException('Failed to reload created shipment.');
            }
            
            // Generate barcode and QR code
            $barcode_path = $shipmentGenerator->generateBarcode($tracking_number);
            $qr_code_path = $shipmentGenerator->generateQRCode($tracking_number);
            
            // Generate PDF receipt
            $pdf_receipt_path = $shipmentGenerator->generatePDFReceipt($shipment);
            
            // Update shipment with generated files
            $updateColumns = [];
            $updateValues = [':id' => $shipment_id];
            
            if ($shipmentGenerator->columnExists('shipments', 'barcode_path')) {
                $updateColumns[] = 'barcode_path = :barcode_path';
                $updateValues[':barcode_path'] = $barcode_path;
            }
            if ($shipmentGenerator->columnExists('shipments', 'qr_code_path')) {
                $updateColumns[] = 'qr_code_path = :qr_code_path';
                $updateValues[':qr_code_path'] = $qr_code_path;
            }
            if ($shipmentGenerator->columnExists('shipments', 'pdf_receipt_path')) {
                $updateColumns[] = 'pdf_receipt_path = :pdf_receipt_path';
                $updateValues[':pdf_receipt_path'] = $pdf_receipt_path;
            }
            
            if (!empty($updateColumns)) {
                $stmt = $db->prepare("UPDATE shipments SET " . implode(', ', $updateColumns) . " WHERE id = :id");
                $stmt->execute($updateValues);
            }
            
            $db->commit();
            
            $message = "Shipment created successfully! Tracking: $tracking_number | Receipt: $receipt_number";
            $message_type = 'success';
            
            // Redirect to shipment details with PDF download flag
            echo '<script>window.location.href = "shipment_details.php?id=' . $shipment_id . '&download_pdf=1";</script>';
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $message = "Error creating shipment: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

$next_tracking = $shipmentGenerator->generateTrackingNumber();
$next_receipt = $shipmentGenerator->generateReceiptNumber();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-plus-circle"></i> Create New Shipment</h5>
        <span>All fields marked with * are required</span>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="shipmentForm">
            
            <!-- Basic Details -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-info-circle"></i> Basic Details</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tracking Number (Auto)</label>
                    <input type="text" name="tracking_number" class="form-control bg-light" value="<?php echo htmlspecialchars($next_tracking); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Receipt Number (Auto)</label>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($next_receipt); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="Auto-generated if empty" value="<?php echo htmlspecialchars($_POST['reference_number'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipment Date <span class="text-danger">*</span></label>
                    <input type="date" name="shipment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery" class="form-control" value="<?php echo htmlspecialchars($_POST['expected_delivery'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shipment Type <span class="text-danger">*</span></label>
                    <select name="shipment_type" class="form-select" required>
                        <option value="document">Document</option>
                        <option value="parcel" selected>Parcel</option>
                        <option value="freight">Freight</option>
                        <option value="express">Express</option>
                        <option value="international">International</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-select">
                        <option value="standard" selected>Standard</option>
                        <option value="express">Express</option>
                        <option value="overnight">Overnight</option>
                        <option value="economy">Economy</option>
                        <option value="same-day">Same Day</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="standard" selected>Standard</option>
                        <option value="high">High</option>
                        <option value="express">Express</option>
                        <option value="same_day">Same Day</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <!-- Sender Information -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-person"></i> Sender Information</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sender Name <span class="text-danger">*</span></label>
                    <input type="text" name="sender_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['sender_name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="sender_company" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_company'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="sender_phone" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_phone'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="sender_email" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City <span class="text-danger">*</span></label>
                    <input type="text" name="sender_city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($_POST['sender_city'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="sender_address" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_address'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="sender_state" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_state'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="sender_postal" class="form-control" value="<?php echo htmlspecialchars($_POST['sender_postal'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <select name="sender_country" class="form-select">
                        <option value="US" selected>United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="DE">Germany</option>
                        <option value="AE">UAE</option>
                        <option value="CN">China</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <!-- Receiver Information -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-person-check"></i> Receiver Information</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Receiver Name <span class="text-danger">*</span></label>
                    <input type="text" name="receiver_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['receiver_name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="receiver_company" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_company'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="receiver_phone" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_phone'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="receiver_email" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Preferred Delivery Time</label>
                    <input type="text" name="preferred_delivery_time" class="form-control" placeholder="e.g. 9AM-5PM" value="<?php echo htmlspecialchars($_POST['preferred_delivery_time'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                    <input type="text" name="receiver_address" class="form-control" required value="<?php echo htmlspecialchars($_POST['receiver_address'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City <span class="text-danger">*</span></label>
                    <input type="text" name="receiver_city" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_city'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="receiver_state" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_state'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="receiver_postal" class="form-control" value="<?php echo htmlspecialchars($_POST['receiver_postal'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <select name="receiver_country" class="form-select">
                        <option value="US" selected>United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="DE">Germany</option>
                        <option value="AE">UAE</option>
                        <option value="CN">China</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Delivery Instructions</label>
                    <textarea name="special_instructions" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['special_instructions'] ?? ''); ?></textarea>
                </div>
            </div>

            <hr class="my-4">

            <!-- Package Information -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-box"></i> Package Information</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Package Name <span class="text-danger">*</span></label>
                    <input type="text" name="package_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['package_name'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($_POST['quantity'] ?? 1); ?>" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" name="weight" class="form-control" required value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>" id="weightInput">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Length (cm)</label>
                    <input type="number" step="0.1" name="length" class="form-control" value="<?php echo htmlspecialchars($_POST['length'] ?? ''); ?>" id="lengthInput">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Width (cm)</label>
                    <input type="number" step="0.1" name="width" class="form-control" value="<?php echo htmlspecialchars($_POST['width'] ?? ''); ?>" id="widthInput">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Height (cm)</label>
                    <input type="number" step="0.1" name="height" class="form-control" value="<?php echo htmlspecialchars($_POST['height'] ?? ''); ?>" id="heightInput">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Volumetric Weight (kg)</label>
                    <input type="text" class="form-control" id="volumetricWeight" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label">Package Description</label>
                    <textarea name="package_description" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['package_description'] ?? ''); ?></textarea>
                </div>
            </div>

            <hr class="my-4">

            <!-- Shipment Contents -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-box-seam"></i> Shipment Contents</h6>
                </div>
                <div class="col-12">
                    <div class="row">
                        <?php
                        $contents = ['Electronic Device', 'Documents', 'Clothing', 'Food', 'Medical', 'Industrial Goods', 'Other'];
                        $selected_contents = (array)($_POST['contents'] ?? []);
                        foreach ($contents as $content):
                        ?>
                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="contents[]" value="<?php echo $content; ?>" id="content_<?php echo strtolower(str_replace(' ', '_', $content)); ?>" <?php echo in_array($content, $selected_contents) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="content_<?php echo strtolower(str_replace(' ', '_', $content)); ?>">
                                        <?php echo $content; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Contents (comma separated)</label>
                    <input type="text" name="contents_extra" class="form-control" value="<?php echo htmlspecialchars($_POST['contents_extra'] ?? ''); ?>" placeholder="e.g. Electronics, Documents">
                </div>
            </div>

            <hr class="my-4">

            <!-- Delivery Options -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-truck"></i> Delivery Options</h6>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="signature_required" id="signature_required" <?php echo isset($_POST['signature_required']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="signature_required">Signature Required</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_fragile" id="is_fragile" <?php echo isset($_POST['is_fragile']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_fragile">Fragile Item</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="weekend_delivery" id="weekend_delivery" <?php echo isset($_POST['weekend_delivery']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="weekend_delivery">Weekend Delivery</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="return_service" id="return_service" <?php echo isset($_POST['return_service']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="return_service">Return Service</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="temperature_controlled" id="temperature_controlled" <?php echo isset($_POST['temperature_controlled']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="temperature_controlled">Temperature Controlled</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Insurance & Cost Calculator -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-shield-check"></i> Insurance & Cost Calculator</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Declared Value ($)</label>
                    <input type="number" step="0.01" name="declared_value" class="form-control" id="declaredValue" value="<?php echo htmlspecialchars($_POST['declared_value'] ?? 0); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Insurance Amount ($)</label>
                    <input type="number" step="0.01" name="insurance_amount" class="form-control" id="insuranceAmount" value="<?php echo htmlspecialchars($_POST['insurance_amount'] ?? 0); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Coverage Type</label>
                    <select name="is_insured" class="form-select" id="coverageType">
                        <option value="0" selected>None</option>
                        <option value="1">Basic</option>
                        <option value="2">Premium</option>
                        <option value="3">Full Coverage</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_insured" id="is_insured_check" <?php echo isset($_POST['is_insured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_insured_check">Enable Insurance</label>
                    </div>
                </div>
            </div>

            <!-- Cost Breakdown -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Base Cost ($)</label>
                    <input type="number" step="0.01" name="base_cost" class="form-control" id="baseCost" value="<?php echo htmlspecialchars($_POST['base_cost'] ?? 20); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weight Charge ($)</label>
                    <input type="number" step="0.01" name="weight_charge" class="form-control" id="weightCharge" value="<?php echo htmlspecialchars($_POST['weight_charge'] ?? 0); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Insurance ($)</label>
                    <input type="number" step="0.01" name="insurance_cost" class="form-control" id="insuranceCost" value="<?php echo htmlspecialchars($_POST['insurance_cost'] ?? 0); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax ($)</label>
                    <input type="number" step="0.01" name="tax_amount" class="form-control" id="taxAmount" value="<?php echo htmlspecialchars($_POST['tax_amount'] ?? 0); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Total Cost ($)</label>
                    <input type="number" step="0.01" name="total_cost" class="form-control fw-bold" id="totalCost" value="<?php echo htmlspecialchars($_POST['total_cost'] ?? 0); ?>" readonly style="background: #f8f9fa; font-size: 1.1rem;">
                </div>
            </div>

            <hr class="my-4">

            <!-- Payment Information -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-credit-card"></i> Payment Information</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash" selected>Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="wallet">Wallet</option>
                        <option value="invoice">Invoice Customer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="pending" selected>Pending</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partially Paid</option>
                        <option value="refunded">Refunded</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">COD Amount (if applicable)</label>
                    <input type="number" step="0.01" name="cod_amount" class="form-control" value="<?php echo htmlspecialchars($_POST['cod_amount'] ?? 0); ?>">
                </div>
            </div>

            <hr class="my-4">

            <!-- File Uploads -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-upload"></i> File Uploads</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipping Label</label>
                    <input type="file" name="label_file" class="form-control" accept=".pdf,.jpg,.png,.docx">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice</label>
                    <input type="file" name="invoice_file" class="form-control" accept=".pdf,.jpg,.png,.docx">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Package Photos</label>
                    <input type="file" name="package_photos" class="form-control" accept=".jpg,.png" multiple>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customs Documents</label>
                    <input type="file" name="customs_docs" class="form-control" accept=".pdf,.docx">
                </div>
            </div>

            <hr class="my-4">

            <!-- Internal Notes -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-journal-text"></i> Internal Notes</h6>
                </div>
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Special handling instructions, driver comments, operational notes..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3"><i class="bi bi-bell"></i> Notification Settings</h6>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_email_sender" id="notify_email_sender" checked>
                        <label class="form-check-label" for="notify_email_sender">Email Sender</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_email_receiver" id="notify_email_receiver" checked>
                        <label class="form-check-label" for="notify_email_receiver">Email Receiver</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_sms_sender" id="notify_sms_sender">
                        <label class="form-check-label" for="notify_sms_sender">SMS Sender</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_sms_receiver" id="notify_sms_receiver">
                        <label class="form-check-label" for="notify_sms_receiver">SMS Receiver</label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row g-4">
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="shipments.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save Draft</button>
                        <button type="submit" name="action" value="create" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Shipment & Generate Documents
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
// Auto-calculate volumetric weight
function calculateVolumetricWeight() {
    const length = parseFloat(document.getElementById('lengthInput').value) || 0;
    const width = parseFloat(document.getElementById('widthInput').value) || 0;
    const height = parseFloat(document.getElementById('heightInput').value) || 0;
    const volumetric = (length * width * height) / 5000;
    document.getElementById('volumetricWeight').value = volumetric.toFixed(2);
}

// Auto-calculate total cost
function calculateTotalCost() {
    const base = parseFloat(document.getElementById('baseCost').value) || 0;
    const weight = parseFloat(document.getElementById('weightCharge').value) || 0;
    const insurance = parseFloat(document.getElementById('insuranceCost').value) || 0;
    const tax = parseFloat(document.getElementById('taxAmount').value) || 0;
    const total = base + weight + insurance + tax;
    document.getElementById('totalCost').value = total.toFixed(2);
}

document.getElementById('lengthInput').addEventListener('input', calculateVolumetricWeight);
document.getElementById('widthInput').addEventListener('input', calculateVolumetricWeight);
document.getElementById('heightInput').addEventListener('input', calculateVolumetricWeight);

document.getElementById('baseCost').addEventListener('input', calculateTotalCost);
document.getElementById('weightCharge').addEventListener('input', calculateTotalCost);
document.getElementById('insuranceCost').addEventListener('input', calculateTotalCost);
document.getElementById('taxAmount').addEventListener('input', calculateTotalCost);

// Auto-calculate weight charge based on weight
document.getElementById('weightInput').addEventListener('input', function() {
    const weight = parseFloat(this.value) || 0;
    const weightCharge = weight * 2;
    document.getElementById('weightCharge').value = weightCharge.toFixed(2);
    calculateTotalCost();
});

// Auto-calculate insurance based on declared value
document.getElementById('declaredValue').addEventListener('input', function() {
    const value = parseFloat(this.value) || 0;
    const insurance = value * 0.05;
    document.getElementById('insuranceAmount').value = insurance.toFixed(2);
    document.getElementById('insuranceCost').value = insurance.toFixed(2);
    calculateTotalCost();
});

// Initial calculations
calculateVolumetricWeight();
calculateTotalCost();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
