<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$page_title = 'Customer Shipments - ' . SITE_NAME;
$db = getDB();

$customerId = intval($_GET['customer_id'] ?? 0);
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

$statusFilter = trim((string)($_GET['status'] ?? ''));
$serviceFilter = trim((string)($_GET['service'] ?? ''));

$where = ['s.customer_id = :cid'];
$params = [':cid' => $customerId];

if ($statusFilter !== '') {
    $where[] = 's.status = :status';
    $params[':status'] = $statusFilter;
}
if ($serviceFilter !== '') {
    $where[] = 's.service_type = :service';
    $params[':service'] = $serviceFilter;
}

$shipments = [];
try {
    $sql = "SELECT s.*, u.name as customer_name
            FROM shipments s
            LEFT JOIN users u ON s.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY s.created_at DESC
            LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
    $message_type = 'danger';
}

$serviceTypes = [];
try {
    $serviceTypes = $db->query("
        SELECT DISTINCT service_type FROM shipments
        WHERE customer_id = " . (int)$customerId . " AND service_type IS NOT NULL
        ORDER BY service_type ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $serviceTypes = [];
}

$statuses = [
    '' => 'All',
    'pending' => 'Pending',
    'processing' => 'Processing',
    'picked_up' => 'Picked Up',
    'in_transit' => 'In Transit',
    'at_hub' => 'At Hub',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'returned' => 'Returned',
    'cancelled' => 'Cancelled',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="bi bi-box-seam"></i> <?php echo htmlspecialchars($customer['name']); ?>'s Shipments
        </h1>
        <p class="text-muted mb-0">Customer ID: #<?php echo (int)$customer['id']; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="customers.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
        <a href="customer_details.php?id=<?php echo (int)$customer['id']; ?>" class="btn btn-outline-primary">
            <i class="bi bi-person"></i> Customer Profile
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="customer_id" value="<?php echo (int)$customerId; ?>">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <?php foreach ($statuses as $k => $v): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>" <?php echo $statusFilter === $k ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="service" class="form-label">Service Type</label>
                <select class="form-select" id="service" name="service">
                    <option value="">All</option>
                    <?php foreach ($serviceTypes as $st): ?>
                        <option value="<?php echo htmlspecialchars($st); ?>" <?php echo $serviceFilter === $st ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $st))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="customer-shipments.php?customer_id=<?php echo (int)$customerId; ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($shipments)): ?>
            <div class="text-center py-5 text-muted">No shipments found for this customer.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Reference</th>
                            <th>Route</th>
                            <th>Service</th>
                            <th>Weight</th>
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
                                <td><?php echo htmlspecialchars($s['reference_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($s['origin_city'] ?? 'N/A'); ?> &rarr;
                                    <?php echo htmlspecialchars($s['destination_city'] ?? 'N/A'); ?>
                                </td>
                                <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $s['service_type'] ?? 'N/A'))); ?></td>
                                <td><?php echo htmlspecialchars($s['total_weight'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format((float)($s['total_amount'] ?? 0), 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower(str_replace('_', '-', $s['status'])); ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $s['status'])); ?>
                                    </span>
                                </td>
                                <td><small><?php echo date('M d, Y', strtotime($s['created_at'])); ?></small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="shipment_details.php?id=<?php echo (int)$s['id']; ?>"
                                           class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="../track.php?cons_no=<?php echo urlencode($s['tracking_number']); ?>"
                                           class="btn btn-sm btn-outline-info" target="_blank" title="Track">
                                            <i class="bi bi-search"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
