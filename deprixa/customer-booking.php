<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Customer Booking - ' . SITE_NAME;
$db = getDB();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$branches = [];
try {
    $branches = $db->query("SELECT id, name, code, city, country FROM locations WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $branches = [];
}

$services = [];
try {
    $services = $db->query("SELECT id, code, name, base_price, price_per_kg FROM services WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $customerId = intval($_POST['customer_id'] ?? 0);
        $senderName = trim($_POST['sender_name'] ?? '');
        $senderPhone = trim($_POST['sender_phone'] ?? '');
        $senderEmail = trim($_POST['sender_email'] ?? '');
        $senderCity = trim($_POST['sender_city'] ?? '');
        $senderCountry = strtoupper(trim($_POST['sender_country'] ?? 'US'));
        $receiverName = trim($_POST['receiver_name'] ?? '');
        $receiverPhone = trim($_POST['receiver_phone'] ?? '');
        $receiverCity = trim($_POST['receiver_city'] ?? '');
        $receiverCountry = strtoupper(trim($_POST['receiver_country'] ?? 'US'));
        $weight = (float)($_POST['weight'] ?? 0);
        $serviceType = strtolower(trim($_POST['service_type'] ?? 'standard'));
        $totalAmount = (float)($_POST['total_amount'] ?? 0);

        if ($customerId <= 0 || $senderName === '' || $receiverName === '' || $weight <= 0) {
            $message = 'Please fill in all required fields.';
            $message_type = 'danger';
        } else {
            try {
                $trackingNumber = 'ASC' . str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
                $stmt = $db->prepare("
                    INSERT INTO shipments (
                        tracking_number, status, service_type, customer_id,
                        sender_name, sender_phone, sender_email, sender_city, sender_country,
                        receiver_name, receiver_phone, receiver_city, receiver_country,
                        total_weight, total_amount, currency, created_at, updated_at
                    ) VALUES (
                        :tracking_number, 'pending', :service_type, :customer_id,
                        :sender_name, :sender_phone, :sender_email, :sender_city, :sender_country,
                        :receiver_name, :receiver_phone, :receiver_city, :receiver_country,
                        :total_weight, :total_amount, 'USD', NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    ':tracking_number' => $trackingNumber,
                    ':service_type' => $serviceType ?: 'standard',
                    ':customer_id' => $customerId,
                    ':sender_name' => $senderName,
                    ':sender_phone' => $senderPhone ?: null,
                    ':sender_email' => $senderEmail ?: null,
                    ':sender_city' => $senderCity ?: null,
                    ':sender_country' => $senderCountry ?: 'US',
                    ':receiver_name' => $receiverName,
                    ':receiver_phone' => $receiverPhone ?: null,
                    ':receiver_city' => $receiverCity ?: null,
                    ':receiver_country' => $receiverCountry ?: 'US',
                    ':total_weight' => $weight,
                    ':total_amount' => $totalAmount ?: null,
                ]);

                $shipmentId = (int)$db->lastInsertId();
                clearDashboardCache();
                $message = 'Shipment created successfully. Tracking: ' . htmlspecialchars($trackingNumber);
                $message_type = 'success';
            } catch (Exception $e) {
                error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
                $message_type = 'danger';
            }
        }
    }
}

$customers = [];
try {
    $customers = $db->query("SELECT id, name, email FROM users WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $customers = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-calendar-plus"></i> New Booking</h1>
        <p class="text-muted mb-0">Create a new shipment booking for a customer.</p>
    </div>
    <a href="panel-customer.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Panel
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="col-md-6">
                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                    <option value="">Select customer...</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>">
                            <?php echo htmlspecialchars($c['name'] . ' (' . $c['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="service_type" class="form-label">Service Type</label>
                <select class="form-select" id="service_type" name="service_type">
                    <?php foreach (['standard' => 'Standard', 'express' => 'Express', 'overnight' => 'Overnight', 'economy' => 'Economy', 'same_day' => 'Same Day'] as $k => $v): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($v); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <hr class="my-3">
            <h6 class="text-muted"><i class="bi bi-box-arrow-up-right"></i> Sender</h6>
            <div class="col-md-6">
                <label for="sender_name" class="form-label">Sender Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sender_name" name="sender_name" required>
            </div>
            <div class="col-md-6">
                <label for="sender_phone" class="form-label">Sender Phone</label>
                <input type="text" class="form-control" id="sender_phone" name="sender_phone">
            </div>
            <div class="col-md-6">
                <label for="sender_email" class="form-label">Sender Email</label>
                <input type="email" class="form-control" id="sender_email" name="sender_email">
            </div>
            <div class="col-md-6">
                <label for="sender_city" class="form-label">Sender City</label>
                <input type="text" class="form-control" id="sender_city" name="sender_city">
            </div>
            <div class="col-md-6">
                <label for="sender_country" class="form-label">Sender Country</label>
                <input type="text" class="form-control" id="sender_country" name="sender_country" value="US">
            </div>
            <hr class="my-3">
            <h6 class="text-muted"><i class="bi bi-box-arrow-down-left"></i> Receiver</h6>
            <div class="col-md-6">
                <label for="receiver_name" class="form-label">Receiver Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="receiver_name" name="receiver_name" required>
            </div>
            <div class="col-md-6">
                <label for="receiver_phone" class="form-label">Receiver Phone</label>
                <input type="text" class="form-control" id="receiver_phone" name="receiver_phone">
            </div>
            <div class="col-md-6">
                <label for="receiver_city" class="form-label">Receiver City</label>
                <input type="text" class="form-control" id="receiver_city" name="receiver_city">
            </div>
            <div class="col-md-6">
                <label for="receiver_country" class="form-label">Receiver Country</label>
                <input type="text" class="form-control" id="receiver_country" name="receiver_country" value="US">
            </div>
            <hr class="my-3">
            <h6 class="text-muted"><i class="bi bi-box"></i> Package</h6>
            <div class="col-md-4">
                <label for="weight" class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="weight" name="weight" required>
            </div>
            <div class="col-md-4">
                <label for="total_amount" class="form-label">Total Amount ($)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="total_amount" name="total_amount">
            </div>
            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Shipment
                </button>
                <a href="panel-customer.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
