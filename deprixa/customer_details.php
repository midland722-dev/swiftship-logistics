<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$page_title = 'Customer Details - ' . SITE_NAME;
$db = getDB();

$customerId = intval($_GET['id'] ?? 0);
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

ensureShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);
ensureTrackingHistory($db);

$message = '';
$message_type = '';

$stats = [
    'total_shipments' => 0,
    'active_shipments' => 0,
    'delivered_shipments' => 0,
    'total_spent' => 0.0,
    'pending_pickup' => 0,
    'in_transit' => 0,
    'delivered' => 0,
    'failed' => 0,
];

try {
    $stmt = $db->prepare("
        SELECT
            COUNT(*) AS total_shipments,
            SUM(CASE WHEN status IN ('in_transit','out_for_delivery','picked_up') THEN 1 ELSE 0 END) AS active_shipments,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_shipments,
            COALESCE(SUM(total_amount), 0) AS total_spent,
            SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS pending_pickup,
            SUM(CASE WHEN status IN ('in_transit','at_hub','out_for_delivery','picked_up') THEN 1 ELSE 0 END) AS in_transit,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered,
            SUM(CASE WHEN status IN ('cancelled','returned','customs_seized','held') THEN 1 ELSE 0 END) AS failed
        FROM shipments
        WHERE customer_id = :id
    ");
    $stmt->execute([':id' => $customerId]);
    $stats = array_merge($stats, $stmt->fetch(PDO::FETCH_ASSOC) ?: []);
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
    $message_type = 'danger';
}

$shipments = [];
try {
    $stmt = $db->prepare("
        SELECT s.*, u.name as customer_name
        FROM shipments s
        LEFT JOIN users u ON s.customer_id = u.id
        WHERE s.customer_id = :id
        ORDER BY s.created_at DESC
        LIMIT 100
    ");
    $stmt->execute([':id' => $customerId]);
    $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
    $message_type = 'danger';
}

$payments = [];
try {
    $stmt = $db->prepare("
        SELECT p.*, s.tracking_number
        FROM payments p
        LEFT JOIN shipments s ON p.shipment_id = s.id
        WHERE p.customer_id = :id
        ORDER BY p.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([':id' => $customerId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $payments = [];
}

$notifications = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM notifications
        WHERE user_id = :id
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([':id' => $customerId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $notifications = [];
}

$f = function($k, $d='N/A') { return htmlspecialchars($customer[$k] ?? $d); };
$yesno = function($v) { return !empty($customer[$v]) ? 'Yes' : 'No'; };
$currency = $customer['currency'] ?? 'USD';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($customer['name']); ?>
        </h1>
        <p class="text-muted mb-0">Customer ID: #<?php echo (int)$customer['id']; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="customers.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
        <a href="customer-shipments.php?customer_id=<?php echo (int)$customer['id']; ?>"
           class="btn btn-outline-primary">
            <i class="bi bi-box-seam"></i> View Shipments
        </a>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Shipments</h5>
                <p class="card-text display-6"><?php echo number_format((int)($stats['total_shipments'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-truck"></i> Active Deliveries</h5>
                <p class="card-text display-6"><?php echo number_format((int)($stats['active_shipments'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Delivered</h5>
                <p class="card-text display-6"><?php echo number_format((int)($stats['delivered_shipments'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Total Spent</h5>
                <p class="card-text display-6">$<?php echo number_format((float)($stats['total_spent'] ?? 0), 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-person"></i> Profile</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Name</td><td><?php echo $f('name'); ?></td></tr>
                    <tr><td class="text-muted">Email</td><td><?php echo $f('email'); ?></td></tr>
                    <tr><td class="text-muted">Phone</td><td><?php echo $f('phone', 'N/A'); ?></td></tr>
                    <tr><td class="text-muted">Company</td><td><?php echo $f('company', 'N/A'); ?></td></tr>
                    <tr><td class="text-muted">Address</td><td><?php echo $f('address', 'N/A'); ?></td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-<?php echo !empty($customer['is_active']) ? 'success' : 'secondary'; ?>">
                                <?php echo !empty($customer['is_active']) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr><td class="text-muted">Joined</td><td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-box-seam"></i> Recent Shipments</h5>
                <a href="customer-shipments.php?customer_id=<?php echo (int)$customer['id']; ?>" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($shipments)): ?>
                    <div class="text-center py-5 text-muted">No shipments found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Route</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $s): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <a href="shipment_details.php?id=<?php echo (int)$s['id']; ?>"
                                                   target="_blank" style="color: var(--primary); text-decoration: none;">
                                                    <?php echo htmlspecialchars($s['tracking_number']); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($s['origin_city'] ?? 'N/A'); ?> &rarr;
                                            <?php echo htmlspecialchars($s['destination_city'] ?? 'N/A'); ?>
                                        </td>
                                        <td>$<?php echo number_format((float)($s['total_amount'] ?? 0), 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower(str_replace('_', '-', $s['status'])); ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $s['status'])); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($s['created_at'])); ?></small></td>
                                        <td>
                                            <a href="shipment_details.php?id=<?php echo (int)$s['id']; ?>"
                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-credit-card"></i> Recent Payments</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($payments)): ?>
                    <div class="text-center py-5 text-muted">No payments found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Tracking #</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['reference_number']); ?></td>
                                        <td><?php echo htmlspecialchars($p['tracking_number'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format((float)($p['amount'] ?? 0), 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $p['status'] === 'completed' ? 'success' : ($p['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($p['status'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($p['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-bell"></i> Recent Notifications</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5 text-muted">No notifications found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Read</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $n): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($n['type']); ?></td>
                                        <td><?php echo htmlspecialchars($n['title']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo !empty($n['is_read']) ? 'success' : 'warning'; ?>">
                                                <?php echo !empty($n['is_read']) ? 'Read' : 'Unread'; ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($n['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
