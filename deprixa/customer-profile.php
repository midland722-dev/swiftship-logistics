<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Customer Profile - ' . SITE_NAME;
$db = getDB();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = intval($_GET['customer_id'] ?? $_GET['id'] ?? 0);
if ($customerId <= 0) {
    header('Location: customers.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    echo '<div class="alert alert-danger m-4">Customer not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $email === '') {
            $message = 'Name and email are required.';
            $message_type = 'danger';
        } else {
            try {
                $stmt = $db->prepare("
                    UPDATE users
                    SET name = :name, email = :email, phone = :phone, company = :company,
                        address = :address, is_active = :is_active, updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone ?: null,
                    ':company' => $company ?: null,
                    ':address' => $address ?: null,
                    ':is_active' => $isActive,
                    ':id' => $customerId,
                ]);
                clearDashboardCache();
                $message = 'Profile updated successfully.';
                $message_type = 'success';

                $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $customerId]);
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
                $message_type = 'danger';
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="bi bi-person-gear"></i> Edit Customer Profile
        </h1>
        <p class="text-muted mb-0">Customer ID: #<?php echo (int)$customer['id']; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="customer_details.php?id=<?php echo (int)$customer['id']; ?>" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i> View Details
        </a>
        <a href="customers.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
    </div>
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
                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone"
                       value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <input type="text" class="form-control" id="company" name="company"
                       value="<?php echo htmlspecialchars($customer['company'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                           value="1" <?php echo !empty($customer['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active Account</label>
                </div>
            </div>
            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
                <a href="customer_details.php?id=<?php echo (int)$customer['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
