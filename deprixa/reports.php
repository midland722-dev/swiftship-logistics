<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Reports - ' . SITE_NAME;
$db = getDB();

ensureShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);

$reportType = trim((string)($_GET['type'] ?? 'delivery'));
$allowedTypes = ['delivery', 'revenue', 'courier'];
if (!in_array($reportType, $allowedTypes, true)) {
    $reportType = 'delivery';
}

$dateFrom = trim((string)($_GET['from'] ?? ''));
$dateTo = trim((string)($_GET['to'] ?? ''));

if ($dateFrom === '') {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
}
if ($dateTo === '') {
    $dateTo = date('Y-m-d');
}

$reportData = [];
$reportColumns = [];
$reportTitle = '';

switch ($reportType) {
    case 'delivery':
        $reportTitle = 'Delivery Performance Report';
        $reportColumns = ['Date', 'Total Shipments', 'Delivered', 'In Transit', 'Failed', 'Success Rate'];
        try {
            $stmt = $db->prepare("
                SELECT
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN status IN ('in_transit','out_for_delivery','picked_up') THEN 1 ELSE 0 END) as in_transit,
                    SUM(CASE WHEN status IN ('cancelled','returned','customs_seized','held') THEN 1 ELSE 0 END) as failed,
                    ROUND(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as success_rate
                FROM shipments
                WHERE created_at >= :from AND created_at <= :to
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 90
            ");
            $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
            $message_type = 'danger';
        }
        break;

    case 'revenue':
        $reportTitle = 'Revenue Report';
        $reportColumns = ['Date', 'Revenue', 'Shipments', 'Avg Order Value'];
        try {
            $stmt = $db->prepare("
                SELECT
                    DATE(s.created_at) as date,
                    COALESCE(SUM(s.total_amount), 0) as revenue,
                    COUNT(*) as shipments,
                    ROUND(COALESCE(AVG(s.total_amount), 0), 2) as avg_order
                FROM shipments s
                WHERE s.created_at >= :from AND s.created_at <= :to
                GROUP BY DATE(s.created_at)
                ORDER BY date DESC
                LIMIT 90
            ");
            $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
            $message_type = 'danger';
        }
        break;

    case 'courier':
        $reportTitle = 'Courier Performance Report';
        $reportColumns = ['Driver', 'Vehicle', 'Deliveries', 'Success Rate'];
        try {
            $stmt = $db->prepare("
                SELECT
                    d.name as driver,
                    v.registration_number as vehicle,
                    COUNT(*) as deliveries,
                    ROUND(SUM(CASE WHEN s.status = 'delivered' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as success_rate
                FROM shipments s
                LEFT JOIN drivers d ON s.driver_id = d.id
                LEFT JOIN vehicles v ON s.vehicle_id = v.id
                WHERE s.created_at >= :from AND s.created_at <= :to
                GROUP BY s.driver_id, s.vehicle_id
                ORDER BY deliveries DESC
                LIMIT 50
            ");
            $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
            $message_type = 'danger';
        }
        break;
}

$exportCsv = isset($_GET['export']) && $_GET['export'] === 'csv';
if ($exportCsv && !empty($reportData)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . $dateFrom . '_to_' . $dateTo . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $reportColumns);
    foreach ($reportData as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-graph-up"></i> Reports</h1>
        <p class="text-muted mb-0">Generate and export shipment, revenue, and courier reports.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($reportType); ?>">
            <div class="col-md-3">
                <label for="type" class="form-label">Report Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="delivery" <?php echo $reportType === 'delivery' ? 'selected' : ''; ?>>Delivery Performance</option>
                    <option value="revenue" <?php echo $reportType === 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                    <option value="courier" <?php echo $reportType === 'courier' ? 'selected' : ''; ?>>Courier Performance</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="from" class="form-label">From</label>
                <input type="date" class="form-control" id="from" name="from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label">To</label>
                <input type="date" class="form-control" id="to" name="to" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Generate
                </button>
                <a href="?type=<?php echo urlencode($reportType); ?>&from=<?php echo urlencode($dateFrom); ?>&to=<?php echo urlencode($dateTo); ?>&export=csv" class="btn btn-outline-success">
                    <i class="bi bi-download"></i> CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><?php echo htmlspecialchars($reportTitle); ?></h5>
        <small class="text-muted"><?php echo htmlspecialchars($dateFrom); ?> to <?php echo htmlspecialchars($dateTo); ?></small>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reportData)): ?>
            <div class="text-center py-5 text-muted">No data found for the selected period.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <?php foreach ($reportColumns as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                    <td><?php echo htmlspecialchars((string)$val); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
