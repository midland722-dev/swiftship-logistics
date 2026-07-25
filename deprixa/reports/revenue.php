<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Revenue Report - ' . SITE_NAME;
$db = getDB();

ensureShipmentColumns($db);

$dateFrom = trim((string)($_GET['from'] ?? ''));
$dateTo = trim((string)($_GET['to'] ?? ''));

if ($dateFrom === '') {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
}
if ($dateTo === '') {
    $dateTo = date('Y-m-d');
}

$summary = [
    'total_revenue' => 0.0,
    'total_shipments' => 0,
    'avg_order_value' => 0.0,
    'revenue_by_service' => [],
    'revenue_by_route' => [],
];

try {
    $stmt = $db->prepare("
        SELECT
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COUNT(*) as total_shipments,
            ROUND(COALESCE(AVG(total_amount), 0), 2) as avg_order_value
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $summary = array_merge($summary, $stmt->fetch(PDO::FETCH_ASSOC) ?: []);
} catch (Exception $e) {
    $message = 'Query failed: ' . $e->getMessage();
    $message_type = 'danger';
}

try {
    $stmt = $db->prepare("
        SELECT service_type, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
        GROUP BY service_type
        ORDER BY revenue DESC
        LIMIT 20
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $summary['revenue_by_service'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $summary['revenue_by_service'] = [];
}

try {
    $stmt = $db->prepare("
        SELECT origin_country, destination_country, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
        GROUP BY origin_country, destination_country
        ORDER BY revenue DESC
        LIMIT 20
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $summary['revenue_by_route'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $summary['revenue_by_route'] = [];
}

$dailyRevenue = [];
try {
    $stmt = $db->prepare("
        SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as shipments
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 60
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $dailyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $dailyRevenue = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-currency-dollar"></i> Revenue Report</h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($dateFrom); ?> to <?php echo htmlspecialchars($dateTo); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="reports.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
        <a href="reports.php?type=revenue&from=<?php echo urlencode($dateFrom); ?>&to=<?php echo urlencode($dateTo); ?>&export=csv" class="btn btn-outline-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text display-6">$<?php echo number_format((float)($summary['total_revenue'] ?? 0), 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Total Shipments</h5>
                <p class="card-text display-6"><?php echo number_format((int)($summary['total_shipments'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Avg Order Value</h5>
                <p class="card-text display-6">$<?php echo number_format((float)($summary['avg_order_value'] ?? 0), 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5>Revenue by Service Type</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($summary['revenue_by_service'])): ?>
                    <div class="text-center py-5 text-muted">No data available.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Shipments</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary['revenue_by_service'] as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $row['service_type'] ?? 'N/A'))); ?></td>
                                        <td><?php echo number_format((int)($row['count'] ?? 0)); ?></td>
                                        <td>$<?php echo number_format((float)($row['revenue'] ?? 0), 2); ?></td>
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
                <h5>Revenue by Route</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($summary['revenue_by_route'])): ?>
                    <div class="text-center py-5 text-muted">No data available.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th>Shipments</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary['revenue_by_route'] as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['origin_country'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['destination_country'] ?? 'N/A'); ?></td>
                                        <td><?php echo number_format((int)($row['count'] ?? 0)); ?></td>
                                        <td>$<?php echo number_format((float)($row['revenue'] ?? 0), 2); ?></td>
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

<div class="card">
    <div class="card-header">
        <h5>Daily Revenue</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($dailyRevenue)): ?>
            <div class="text-center py-5 text-muted">No data available.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shipments</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyRevenue as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['date']); ?></td>
                                <td><?php echo number_format((int)($d['shipments'] ?? 0)); ?></td>
                                <td>$<?php echo number_format((float)($d['revenue'] ?? 0), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
