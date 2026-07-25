<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Customers - ' . SITE_NAME;
$db = getDB();

$message = '';
$message_type = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$search = trim((string)($_GET['search'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(u.name LIKE :search OR u.email LIKE :search OR u.company LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$customerStatuses = [
    'active'   => 'Active',
    'inactive' => 'Inactive',
    'pending'  => 'Pending',
];

$statusWhere = '';
if ($statusFilter !== '' && isset($customerStatuses[$statusFilter])) {
    $statusWhere = ' AND u.is_active = :status';
    $params[':status'] = $statusFilter === 'active' ? 1 : ($statusFilter === 'inactive' ? 0 : 1);
}

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.phone,
        u.company,
        u.address,
        u.is_active,
        u.created_at,
        COUNT(s.id) AS total_shipments,
        SUM(CASE WHEN s.status IN ('in_transit','out_for_delivery','picked_up') THEN 1 ELSE 0 END) AS active_shipments,
        SUM(CASE WHEN s.status = 'delivered' THEN 1 ELSE 0 END) AS delivered_shipments,
        COALESCE(SUM(s.total_amount), 0) AS total_spent
    FROM users u
    LEFT JOIN shipments s ON s.customer_id = u.id
    WHERE 1=1
";

if ($where !== []) {
    $sql .= ' AND ' . implode(' AND ', $where);
}
$sql .= $statusWhere . '
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 200
';

$customers = [];
try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Query failed: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-people-fill"></i> Customers</h1>
        <p class="text-muted mb-0">Manage customer accounts and view shipment history.</p>
    </div>
    <a href="shipments.php" class="btn btn-outline-primary">
        <i class="bi bi-box-seam"></i> All Shipments
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Name, email, or company...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach ($customerStatuses as $k => $v): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>" <?php echo $statusFilter === $k ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="customers.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5 text-muted">No customers found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Shipments</th>
                            <th>Active</th>
                            <th>Delivered</th>
                            <th>Total Spent</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><?php echo (int)$c['id']; ?></td>
                                <td>
                                    <strong>
                                        <a href="customer_details.php?id=<?php echo (int)$c['id']; ?>"
                                           style="color: var(--primary); text-decoration: none;">
                                            <?php echo htmlspecialchars($c['name']); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($c['company'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo !empty($c['is_active']) ? 'success' : 'secondary'; ?>">
                                        <?php echo !empty($c['is_active']) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format((int)($c['total_shipments'] ?? 0)); ?></td>
                                <td><?php echo number_format((int)($c['active_shipments'] ?? 0)); ?></td>
                                <td><?php echo number_format((int)($c['delivered_shipments'] ?? 0)); ?></td>
                                <td>$<?php echo number_format((float)($c['total_spent'] ?? 0), 2); ?></td>
                                <td><small><?php echo date('M d, Y', strtotime($c['created_at'])); ?></small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="customer_details.php?id=<?php echo (int)$c['id']; ?>"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="customer-shipments.php?customer_id=<?php echo (int)$c['id']; ?>"
                                           class="btn btn-sm btn-outline-info" title="Shipments">
                                            <i class="bi bi-box-seam"></i>
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
