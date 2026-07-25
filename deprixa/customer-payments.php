<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Customer Payments - ' . SITE_NAME;
$db = getDB();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = intval($_GET['customer_id'] ?? $_GET['cid'] ?? 0);
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

$payments = [];
try {
    $stmt = $db->prepare("
        SELECT p.*, s.tracking_number, s.status as shipment_status
        FROM payments p
        LEFT JOIN shipments s ON p.shipment_id = s.id
        WHERE p.customer_id = :id
        ORDER BY p.created_at DESC
        LIMIT 200
    ");
    $stmt->execute([':id' => $customerId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Query failed: ' . $e->getMessage();
    $message_type = 'danger';
}

$totalPaid = 0.0;
$totalPending = 0.0;
foreach ($payments as $p) {
    $amount = (float)($p['amount'] ?? 0);
    if (strtolower((string)($p['status'] ?? '')) === 'completed') {
        $totalPaid += $amount;
    } else {
        $totalPending += $amount;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="bi bi-credit-card"></i> <?php echo htmlspecialchars($customer['name']); ?>'s Payments
        </h1>
        <p class="text-muted mb-0">Customer ID: #<?php echo (int)$customer['id']; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="customer_details.php?id=<?php echo (int)$customer['id']; ?>" class="btn btn-outline-primary">
            <i class="bi bi-person"></i> Customer Profile
        </a>
        <a href="customers.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Total Paid</h5>
                <p class="card-text display-6">$<?php echo number_format($totalPaid, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock"></i> Pending</h5>
                <p class="card-text display-6">$<?php echo number_format($totalPending, 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
            <div class="text-center py-5 text-muted">No payments found for this customer.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Tracking #</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Shipment Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['reference_number']); ?></td>
                                <td><?php echo htmlspecialchars($p['tracking_number'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format((float)($p['amount'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $p['payment_method'] ?? 'N/A'))); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo strtolower((string)($p['status'])) === 'completed' ? 'success' : (strtolower((string)($p['status'])) === 'pending' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($p['status'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower(str_replace('_', '-', $p['shipment_status'] ?? 'N/A')); ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $p['shipment_status'] ?? 'N/A')); ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
