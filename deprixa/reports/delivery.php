<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Delivery Performance - ' . SITE_NAME;
$db = getDB();

ensureShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);

$dateFrom = trim((string)($_GET['from'] ?? ''));
$dateTo = trim((string)($_GET['to'] ?? ''));

if ($dateFrom === '') {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
}
if ($dateTo === '') {
    $dateTo = date('Y-m-d');
}

$stats = [
    'total' => 0,
    'delivered' => 0,
    'in_transit' => 0,
    'failed' => 0,
    'success_rate' => 0,
    'avg_delivery_days' => 0,
];

try {
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status IN ('in_transit','out_for_delivery','picked_up') THEN 1 ELSE 0 END) as in_transit,
            SUM(CASE WHEN status IN ('cancelled','returned','customs_seized','held') THEN 1 ELSE 0 END) as failed,
            ROUND(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as success_rate,
            ROUND(AVG(CASE WHEN status = 'delivered' AND actual_delivery IS NOT NULL THEN DATEDIFF(actual_delivery, created_at) ELSE NULL END), 1) as avg_delivery_days
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $stats = array_merge($stats, $stmt->fetch(PDO::FETCH_ASSOC) ?: []);
} catch (Exception $e) {
    $message = 'Query failed: ' . $e->getMessage();
    $message_type = 'danger';
}

$daily = [];
try {
    $stmt = $db->prepare("
        SELECT
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            ROUND(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as success_rate
        FROM shipments
        WHERE created_at >= :from AND created_at <= :to
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 60
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $daily = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-check-circle"></i> Delivery Performance</h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($dateFrom); ?> to <?php echo htmlspecialchars($dateTo); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="reports.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
        <a href="reports.php?type=delivery&from=<?php echo urlencode($dateFrom); ?>&to=<?php echo urlencode($dateTo); ?>&export=csv" class="btn btn-outline-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Shipments</h5>
                <p class="card-text display-6"><?php echo number_format((int)($stats['total'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Delivered</h5>
                <p class="card-text display-6"><?php echo number_format((int)($stats['delivered'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Success Rate</h5>
                <p class="card-text display-6"><?php echo number_format((float)($stats['success_rate'] ?? 0), 1); ?>%</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Avg Delivery Days</h5>
                <p class="card-text display-6"><?php echo number_format((float)($stats['avg_delivery_days'] ?? 0), 1); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Daily Breakdown</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($daily)): ?>
            <div class="text-center py-5 text-muted">No data available.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Delivered</th>
                            <th>In Transit</th>
                            <th>Failed</th>
                            <th>Success Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['date']); ?></td>
                                <td><?php echo number_format((int)($d['total'] ?? 0)); ?></td>
                                <td><?php echo number_format((int)($d['delivered'] ?? 0)); ?></td>
                                <td><?php echo number_format((int)($d['in_transit'] ?? 0)); ?></td>
                                <td><?php echo number_format((int)($d['failed'] ?? 0)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo (float)($d['success_rate'] ?? 0) >= 80 ? 'success' : 'warning'; ?>">
                                        <?php echo number_format((float)($d['success_rate'] ?? 0), 1); ?>%
                                    </span>
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
