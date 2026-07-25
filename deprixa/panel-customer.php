<?php
/**
 * Unified Customer Panel
 * ----------------------
 * Replaces the legacy deprixa/panel-customer/customer.php with a version
 * that uses the main application's authentication and database layer.
 *
 * For backward compatibility, this page still supports the old deprixa
 * query parameters and redirect patterns.
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$page_title = 'Customer Panel - ' . SITE_NAME;
$db = getDB();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$adminId = (int)$_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

$message = '';
$message_type = '';

$customerId = intval($_GET['cid'] ?? $_GET['customer_id'] ?? 0);
if ($customerId <= 0) {
    $customerId = $adminId;
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

$searchTracking = trim((string)($_GET['cons'] ?? $_GET['tracking'] ?? ''));
$shipments = [];

try {
    $sql = "SELECT s.*, u.name as customer_name
            FROM shipments s
            LEFT JOIN users u ON s.customer_id = u.id
            WHERE s.customer_id = :cid";
    $params = [':cid' => $customerId];

    if ($searchTracking !== '') {
        $sql .= " AND s.tracking_number LIKE :tn";
        $params[':tn'] = '%' . $searchTracking . '%';
    }

    $sql .= " ORDER BY s.created_at DESC LIMIT 100";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Query failed: ' . $e->getMessage();
    $message_type = 'danger';
}

$stats = [
    'total' => count($shipments),
    'in_transit' => 0,
    'delivered' => 0,
    'pending' => 0,
];
foreach ($shipments as $s) {
    $st = strtolower((string)($s['status'] ?? ''));
    if (in_array($st, ['in_transit', 'picked_up', 'out_for_delivery', 'at_hub'], true)) {
        $stats['in_transit']++;
    } elseif ($st === 'delivered') {
        $stats['delivered']++;
    } else {
        $stats['pending']++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($customer['name']); ?>'s Panel
        </h1>
        <p class="text-muted mb-0">Customer ID: #<?php echo (int)$customer['id']; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="customers.php" class="btn btn-outline-secondary">
            <i class="bi bi-people"></i> All Customers
        </a>
        <a href="customer_details.php?id=<?php echo (int)$customer['id']; ?>" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i> Full Profile
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
    <div class="col-md-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Shipments</h5>
                <p class="card-text display-6"><?php echo number_format($stats['total']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-truck"></i> In Transit</h5>
                <p class="card-text display-6"><?php echo number_format($stats['in_transit']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Delivered</h5>
                <p class="card-text display-6"><?php echo number_format($stats['delivered']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="cid" value="<?php echo (int)$customerId; ?>">
            <div class="col-md-8">
                <label for="cons" class="form-label">Search by Tracking Number</label>
                <input type="text" class="form-control" id="cons" name="cons"
                       value="<?php echo htmlspecialchars($searchTracking); ?>"
                       placeholder="Enter tracking number...">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="panel-customer.php?cid=<?php echo (int)$customerId; ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-list-ul"></i> Shipments</h5>
        <a href="create_shipment.php?customer_id=<?php echo (int)$customerId; ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> New Shipment
        </a>
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
                            <th>Service</th>
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
                                <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $s['service_type'] ?? 'N/A'))); ?></td>
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
