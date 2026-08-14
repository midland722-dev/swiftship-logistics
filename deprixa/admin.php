<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$page_title = 'Dashboard - ' . SITE_NAME;
$db = getDB();
$pcol = paymentDateColumn($db);

$dashboard_errors = [];
$cache_file = sys_get_temp_dir() . '/shp_dashboard_cache.json';
$cache_ttl = 120;
$cache_hit = false;
$cached = null;
$range_days = isset($_GET['range']) ? (int)$_GET['range'] : 7;
if ($range_days <= 0) $range_days = 7;
if ($range_days > 365) $range_days = 365;
if (file_exists($cache_file)) {
    $cached = json_decode(file_get_contents($cache_file), true);
    if ($cached && isset($cached['ts']) && (time() - (int)$cached['ts']) < $cache_ttl) {
        $cache_hit = true;
    }
}

if (!$cache_hit) {
    $today_start = date('Y-m-d 00:00:00');
    $month_start = date('Y-m-01 00:00:00');
    $prev_range_start = date('Y-m-d 00:00:00', strtotime("-" . ($range_days + $range_days) . " days"));
    $curr_range_start = date('Y-m-d 00:00:00', strtotime("-" . $range_days . " days"));

    $stats = [
        'total_shipments' => 0,
        'in_transit' => 0,
        'delivered' => 0,
        'pending' => 0,
        'failed' => 0,
        'revenue_today' => 0,
        'revenue_month' => 0,
        'revenue_prev_month' => 0,
        'new_customers' => 0,
        'range_days' => $range_days,
        'revenue_range' => 0,
        'revenue_prev_range' => 0,
        'avg_shipment_value' => 0,
        'delivery_success_rate' => 0,
    ];

    try {
        $stats['total_shipments'] = (int)$db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
        $stats['in_transit'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status IN ('in_transit', 'out_for_delivery', 'picked_up')")->fetchColumn();
        $stats['delivered'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status = 'delivered'")->fetchColumn();
        $stats['pending'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status IN ('pending', 'processing')")->fetchColumn();
        $stats['failed'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status IN ('cancelled', 'returned', 'customs_seized', 'held')")->fetchColumn();
        $stats['new_customers'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $stats['revenue_today'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE($pcol) = CURDATE() AND status = 'completed'")->fetchColumn();
        $stats['revenue_month'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH($pcol) = MONTH(CURDATE()) AND YEAR($pcol) = YEAR(CURDATE()) AND status = 'completed'")->fetchColumn();
        $stats['revenue_prev_month'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH($pcol) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR($pcol) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND status = 'completed'")->fetchColumn();
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE $pcol >= :start AND status = 'completed'");
        $stmt->execute([':start' => $curr_range_start]);
        $stats['revenue_range'] = (float)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE $pcol >= :start AND $pcol < :curr_start AND status = 'completed'");
        $stmt->execute([':start' => $prev_range_start, ':curr_start' => $curr_range_start]);
        $stats['revenue_prev_range'] = (float)$stmt->fetchColumn();
        $stats['avg_shipment_value'] = $stats['total_shipments'] > 0 ? ($stats['revenue_month'] / $stats['total_shipments']) : 0;
        $stats['delivery_success_rate'] = $stats['total_shipments'] > 0 ? round(($stats['delivered'] / $stats['total_shipments']) * 100, 1) : 0;
    } catch (Exception $e) {
        error_log('Dashboard KPI error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $status_counts = [];
    try {
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM shipments GROUP BY status ORDER BY count DESC");
        $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard status chart error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $revenue_chart_data = [];
    try {
        $stmt = $db->prepare("
            SELECT DATE($pcol) as date, COALESCE(SUM(amount), 0) as revenue
            FROM payments
            WHERE $pcol >= :start AND status = 'completed'
            GROUP BY DATE($pcol)
            ORDER BY date ASC
        ");
        $stmt->execute([':start' => date('Y-m-d 00:00:00', strtotime("-" . $range_days . " days"))]);
        $revenue_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard revenue chart error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $recent_shipments = [];
    try {
        $stmt = $db->query("
            SELECT s.tracking_number, s.status, s.origin_city, s.destination_city, s.total_amount, s.created_at,
                   CONCAT(u.name) as customer_name
            FROM shipments s
            LEFT JOIN users u ON s.customer_id = u.id
            ORDER BY s.created_at DESC
            LIMIT 8
        ");
        $recent_shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard recent shipments error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $recent_tickets = [];
    try {
        $stmt = $db->query("
            SELECT t.ticket_number, t.subject, t.status, t.priority, t.created_at,
                   CONCAT(u.name) as customer_name
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC
            LIMIT 5
        ");
        $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard recent tickets error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $alerts = [];
    try {
        $stmt = $db->query("
            SELECT 'hold' as type, id, shipment_id, hold_type as title, severity, held_at as created_at
            FROM shipment_holds WHERE released_at IS NULL
            UNION ALL
            SELECT 'exception' as type, id, shipment_id, exception_type as title, severity, reported_at as created_at
            FROM shipment_exceptions WHERE resolved_at IS NULL
            UNION ALL
            SELECT 'flag' as type, id, shipment_id, flag_type as title, severity, flagged_at as created_at
            FROM shipment_flags WHERE resolved_at IS NULL
            ORDER BY created_at DESC
            LIMIT 8
        ");
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard alerts error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $top_routes = [];
    try {
        $stmt = $db->query("
            SELECT origin_country, destination_country, COUNT(*) as count
            FROM shipments
            GROUP BY origin_country, destination_country
            ORDER BY count DESC
            LIMIT 5
        ");
        $top_routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard top routes error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    $top_services = [];
    try {
        $stmt = $db->query("
            SELECT service_type, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
            FROM shipments
            GROUP BY service_type
            ORDER BY count DESC
            LIMIT 5
        ");
        $top_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Dashboard top services error: ' . $e->getMessage());
        $dashboard_errors[] = 'Some dashboard data could not be loaded.';
    }

    file_put_contents($cache_file, json_encode([
        'ts' => time(),
        'stats' => $stats,
        'status_counts' => $status_counts,
        'revenue_chart_data' => $revenue_chart_data,
        'recent_shipments' => $recent_shipments,
        'recent_tickets' => $recent_tickets,
        'alerts' => $alerts,
        'top_routes' => $top_routes,
        'top_services' => $top_services,
    ]));
} else {
    $stats = $cached['stats'] ?? [];
    $status_counts = $cached['status_counts'] ?? [];
    $revenue_chart_data = $cached['revenue_chart_data'] ?? [];
    $recent_shipments = $cached['recent_shipments'] ?? [];
    $recent_tickets = $cached['recent_tickets'] ?? [];
    $alerts = $cached['alerts'] ?? [];
    $top_routes = $cached['top_routes'] ?? [];
    $top_services = $cached['top_services'] ?? [];
}

$page_title = 'Dashboard - ' . SITE_NAME;
?>

<?php if (!empty($dashboard_errors)): ?>
<div class="alert alert-warning mb-3">
    <i class="bi bi-exclamation-triangle"></i> Some dashboard data could not be loaded.
    <ul class="mb-0 mt-2">
        <?php foreach ($dashboard_errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <strong>Period:</strong>
        <a href="?range=7" class="btn btn-sm <?php echo $range_days == 7 ? 'btn-primary' : 'btn-outline-primary'; ?>">7 Days</a>
        <a href="?range=30" class="btn btn-sm <?php echo $range_days == 30 ? 'btn-primary' : 'btn-outline-primary'; ?>">30 Days</a>
        <a href="?range=90" class="btn btn-sm <?php echo $range_days == 90 ? 'btn-primary' : 'btn-outline-primary'; ?>">90 Days</a>
    </div>
    <small class="text-muted">Dashboard cached for <?php echo $cache_ttl; ?>s</small>
</div>

<?php $ship_stats = shipmentStats($db); ?>
<div class="row g-2 mb-4">
    <?php
    $scards = [
        ['Total', $ship_stats['total'], 'box-seam', 'bg-dark'],
        ['Pending Pickup', $ship_stats['pending_pickup'], 'hourglass-split', 'bg-warning text-dark'],
        ['Picked Up', $ship_stats['picked_up'], 'hand-index', 'bg-info text-dark'],
        ['In Transit', $ship_stats['in_transit'], 'truck', 'bg-primary'],
        ['At Hub', $ship_stats['at_hub'], 'building', 'bg-primary'],
        ['Out for Delivery', $ship_stats['out_for_delivery'], 'shipping-fast', 'bg-primary'],
        ['Delivered', $ship_stats['delivered'], 'check-circle', 'bg-success'],
        ['Failed', $ship_stats['delivery_failed'], 'x-circle', 'bg-danger'],
        ['Returned', $ship_stats['returned'], 'arrow-return-left', 'bg-danger'],
        ['Cancelled', $ship_stats['cancelled'], 'slash-circle', 'bg-secondary'],
    ];
    foreach ($scards as $c):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card <?php echo $c[3]; ?> text-white shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fs-4 fw-bold"><?php echo number_format($c[1]); ?></div>
                        <div class="small opacity-75"><?php echo $c[0]; ?></div>
                    </div>
                    <i class="bi bi-<?php echo $c[2]; ?> fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="stat-cards">
    <div class="stat-card card-shipments">
        <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['total_shipments']); ?></div>
            <p class="stat-label">Total Shipments</p>
        </div>
    </div>
    <div class="stat-card card-revenue">
        <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
        <div class="stat-info">
            <?php
                $rev_badge = '';
                if ($stats['revenue_prev_month'] > 0) {
                    $delta = $stats['revenue_month'] - $stats['revenue_prev_month'];
                    $pct = round(($delta / $stats['revenue_prev_month']) * 100, 1);
                    $rev_badge = '<span class="badge bg-' . ($delta >= 0 ? 'success' : 'danger') . ' ms-2">' . ($delta >= 0 ? '+' : '') . $pct . '%</span>';
                }
            ?>
            <div class="stat-value">$<?php echo number_format($stats['revenue_month'], 2); ?><?php echo $rev_badge; ?></div>
            <p class="stat-label">Revenue This Month</p>
        </div>
    </div>
    <div class="stat-card card-pending">
        <div class="stat-icon orange"><i class="bi bi-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
            <p class="stat-label">Pending Pickups</p>
        </div>
    </div>
    <div class="stat-card card-delivered">
        <div class="stat-icon purple"><i class="bi bi-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['delivered']); ?></div>
            <p class="stat-label">Delivered Packages</p>
        </div>
    </div>
    <div class="stat-card card-customs">
        <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['failed']); ?></div>
            <p class="stat-label">Failed / Issues</p>
        </div>
    </div>
    <div class="stat-card card-tickets">
        <div class="stat-icon pink"><i class="bi bi-ticket-perforated"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['in_transit']); ?></div>
            <p class="stat-label">Active Deliveries</p>
        </div>
    </div>
    <div class="stat-card card-users">
        <div class="stat-icon cyan"><i class="bi bi-people"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['new_customers']); ?></div>
            <p class="stat-label">New Customers Today</p>
        </div>
    </div>
    <div class="stat-card card-revenue">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-info">
            <?php
                $today_badge = '';
                if ($stats['revenue_today'] > 0) {
                    $today_badge = '<span class="badge bg-success ms-2">Today</span>';
                }
            ?>
            <div class="stat-value">$<?php echo number_format($stats['revenue_today'], 2); ?><?php echo $today_badge; ?></div>
            <p class="stat-label">Revenue Today</p>
        </div>
    </div>
    <div class="stat-card card-success-rate">
        <div class="stat-icon teal"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['delivery_success_rate'], 1); ?>%</div>
            <p class="stat-label">Delivery Success Rate</p>
        </div>
    </div>
    <div class="stat-card card-avg-value">
        <div class="stat-icon indigo"><i class="bi bi-receipt"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?php echo number_format($stats['avg_shipment_value'], 2); ?></div>
            <p class="stat-label">Avg Shipment Value</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-graph-up"></i> Revenue Overview (Last <?php echo (int)$stats['range_days']; ?> Days)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container tall">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart"></i> Shipment Status</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($alerts)): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5><i class="bi bi-exclamation-triangle"></i> Attention Required (<?php echo count($alerts); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Shipment</th>
                                <th>Title</th>
                                <th>Severity</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alerts as $alert): ?>
                                <tr>
                                    <td><span class="badge bg-<?php echo $alert['type'] === 'hold' ? 'warning' : ($alert['type'] === 'exception' ? 'danger' : 'info'); ?>"><?php echo ucfirst($alert['type']); ?></span></td>
                                    <td>
                                        <a href="shipment_details.php?id=<?php echo htmlspecialchars($alert['shipment_id'] ?? ''); ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                            #<?php echo htmlspecialchars($alert['id'] ?? 'N/A'); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($alert['title'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($alert['severity'] ?? 'N/A'); ?></span></td>
                                    <td><small><?php echo date('M d, Y', strtotime($alert['created_at'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-map"></i> Top Routes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_routes)): ?>
                    <p class="text-muted mb-0">No route data yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Origin</th><th>Destination</th><th>Shipments</th></tr></thead>
                            <tbody>
                                <?php foreach ($top_routes as $route): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($route['origin_country'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($route['destination_country'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo number_format($route['count'] ?? 0); ?></strong></td>
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
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-truck"></i> Top Services</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_services)): ?>
                    <p class="text-muted mb-0">No service data yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Service</th><th>Shipments</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach ($top_services as $svc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($svc['service_type'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo number_format($svc['count'] ?? 0); ?></strong></td>
                                        <td>$<?php echo number_format($svc['revenue'] ?? 0, 2); ?></td>
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

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Recent Shipments</h5>
                <a href="shipments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_shipments)): ?>
                    <div class="text-center py-5 text-muted">No shipments found</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Customer</th>
                                    <th>Route</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_shipments as $shipment): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <a href="shipment_details.php?id=<?php echo htmlspecialchars($shipment['id'] ?? ''); ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                                    <?php echo htmlspecialchars($shipment['tracking_number']); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($shipment['customer_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($shipment['origin_city'] ?? 'N/A'); ?>
                                                <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                <?php echo htmlspecialchars($shipment['destination_city'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>$<?php echo number_format($shipment['total_amount'] ?? 0, 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower(str_replace('_', '-', $shipment['status'])); ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $shipment['status'])); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($shipment['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-ticket-perforated"></i> Recent Support Tickets</h5>
                <a href="tickets.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_tickets)): ?>
                    <p class="text-muted text-center mb-0">No tickets found</p>
                <?php else: ?>
                    <?php foreach ($recent_tickets as $ticket): ?>
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div>
                                <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                <p class="mb-1 text-muted small" style="margin:0; font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($ticket['subject']); ?>
                                </p>
                                <small class="text-muted"><?php echo htmlspecialchars($ticket['customer_name'] ?? 'N/A'); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?php 
                                    echo $ticket['status'] === 'open' ? 'warning' : 
                                        ($ticket['status'] === 'in_progress' ? 'info' : 
                                        ($ticket['status'] === 'resolved' ? 'success' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                                <br><small class="text-muted"><?php echo date('M d', strtotime($ticket['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-link-45deg"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <a href="create_shipment.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle"></i> Create Shipment
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="shipments.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-box-seam"></i> Manage Shipments
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="tickets.php" class="btn btn-outline-warning w-100">
                            <i class="bi bi-ticket-perforated"></i> Support Tickets
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="../php/index.php" target="_blank" class="btn btn-outline-info w-100">
                            <i class="bi bi-globe"></i> View Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (can('manage_integrations')): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-plug-fill"></i> Integration Health</h5>
                <a href="integrations.php" class="btn btn-sm btn-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php
                $integrations = [];
                try {
                    $integrations = $db->query("
                        SELECT id, integration_name, provider, integration_type, is_active, last_sync_at, last_error, consecutive_failures
                        FROM api_integrations ORDER BY integration_type, provider
                    ")->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { /* table may not exist yet */ }
                if (empty($integrations)):
                ?>
                    <p class="text-muted mb-0">No integrations configured. Connect a carrier, payment gateway, or notification provider from <a href="integrations.php">Integrations</a>.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($integrations as $it): ?>
                            <?php
                            if (empty($it['is_active'])) { $badge = '<span class="badge bg-secondary">Disabled</span>'; }
                            elseif ((int)$it['consecutive_failures'] >= 5) { $badge = '<span class="badge bg-danger">Failing</span>'; }
                            elseif ((int)$it['consecutive_failures'] > 0) { $badge = '<span class="badge bg-warning text-dark">Degraded</span>'; }
                            else { $badge = '<span class="badge bg-success">Healthy</span>'; }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-2 small d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($it['integration_name']); ?></strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($it['provider']); ?> · <?php echo htmlspecialchars($it['integration_type']); ?></span>
                                    </div>
                                    <div class="text-end"><?php echo $badge; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode($revenue_chart_data); ?>;
const revenueLabels = revenueData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});
const revenueValues = revenueData.map(d => parseFloat(d.revenue));

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue ($)',
            data: revenueValues,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => '$' + ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => '$' + value.toLocaleString()
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = <?php echo json_encode($status_counts); ?>;
const statusLabels = statusData.map(d => d.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
const statusValues = statusData.map(d => parseInt(d.count));
const statusColors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'
];

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: statusColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11 }
                }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
