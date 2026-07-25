<?php
/**
 * Shipment Analytics dashboard — KPI cards + charts (Chart.js, already loaded
 * in the admin header). Supports filtering by date range, country, courier,
 * and status. All queries are indexed/aggregated for performance.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('view_analytics');

$page_title = 'Shipment Analytics - ' . SITE_NAME;
$db = getDB();

$from = trim($_GET['from'] ?? date('Y-m-01'));
$to = trim($_GET['to'] ?? date('Y-m-d'));
$country = trim($_GET['country'] ?? '');
$courier = intval($_GET['courier'] ?? 0);
$status = trim($_GET['status'] ?? '');

$where = ["DATE(s.created_at) BETWEEN :f AND :t"];
$params = [':f'=>$from, ':t'=>$to];
if ($country) { $where[] = "s.destination_country = :co"; $params[':co'] = $country; }
if ($courier) { $where[] = "s.driver_id = :cu"; $params[':cu'] = $courier; }
if ($status) { $where[] = "s.status = :st"; $params[':st'] = $status; }
$whereSql = ' WHERE ' . implode(' AND ', $where);

function qVal($db, $sql, $params) { $s=$db->prepare($sql); foreach ($params as $k=>$v) $s->bindValue($k,$v); $s->execute(); return $s->fetchColumn(); }

$total = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql", $params);
$active = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql AND s.status NOT IN ('delivered','cancelled','returned','lost')", $params);
$delivered = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql AND s.status='delivered'", $params);
$delayed = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql AND s.status IN ('on_hold','held','delayed')", $params);
$returned = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql AND s.status='returned'", $params);
$failed = qVal($db, "SELECT COUNT(*) FROM shipments s $whereSql AND s.status IN ('delivery_failed','lost','damaged')", $params);
$revenue = qVal($db, "SELECT COALESCE(SUM(total_amount),0) FROM shipments s $whereSql AND s.payment_status='paid'", $params);

// Monthly revenue.
$mr = $db->prepare("SELECT DATE_FORMAT(created_at,'%Y-%m') m, COALESCE(SUM(total_amount),0) r FROM shipments s $whereSql GROUP BY m ORDER BY m");
foreach ($params as $k=>$v) $mr->bindValue($k,$v); $mr->execute();
$months = []; $revSeries = [];
foreach ($mr->fetchAll(PDO::FETCH_ASSOC) as $row) { $months[] = $row['m']; $revSeries[] = (float)$row['r']; }

// By status.
$bs = $db->prepare("SELECT status, COUNT(*) c FROM shipments s $whereSql GROUP BY status ORDER BY c DESC");
foreach ($params as $k=>$v) $bs->bindValue($k,$v); $bs->execute();
$statusLabels = []; $statusCounts = [];
foreach ($bs->fetchAll(PDO::FETCH_ASSOC) as $row) { $statusLabels[] = statusLabel($row['status']); $statusCounts[] = (int)$row['c']; }

// By destination country.
$bc = $db->prepare("SELECT destination_country, COUNT(*) c FROM shipments s $whereSql GROUP BY destination_country ORDER BY c DESC LIMIT 8");
foreach ($params as $k=>$v) $bc->bindValue($k,$v); $bc->execute();
$countryLabels = []; $countryCounts = [];
foreach ($bc->fetchAll(PDO::FETCH_ASSOC) as $row) { $countryLabels[] = $row['destination_country'] ?: 'N/A'; $countryCounts[] = (int)$row['c']; }

// Top destinations (by city).
$td = $db->prepare("SELECT destination_city, COUNT(*) c FROM shipments s $whereSql GROUP BY destination_city ORDER BY c DESC LIMIT 8");
foreach ($params as $k=>$v) $td->bindValue($k,$v); $td->execute();
$topDest = $td->fetchAll(PDO::FETCH_ASSOC);

$successRate = $total ? round($delivered / $total * 100, 1) : 0;
// Avg delivery time (delivered only).
$avgDays = qVal($db, "SELECT COALESCE(AVG(DATEDIFF(actual_delivery, created_at)),0) FROM shipments s $whereSql AND actual_delivery IS NOT NULL", $params);

$drivers = fetchDrivers($db);
$countries = $db->query("SELECT DISTINCT destination_country FROM shipments WHERE destination_country <> '' ORDER BY destination_country")->fetchAll(PDO::FETCH_COLUMN);

$qs = http_build_query(array_filter(['from'=>$from,'to'=>$to,'country'=>$country,'courier'=>$courier,'status'=>$status]));
$json = fn($a) => json_encode($a);
?>
<div class="container-fluid py-3">
    <h4 class="mb-3"><i class="bi bi-graph-up me-2"></i>Shipment Analytics</h4>
    <form method="GET" class="card mb-3"><div class="card-body row g-2 align-items-end">
        <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($from); ?>"></div>
        <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to); ?>"></div>
        <div class="col-md-2"><label class="form-label small">Country</label><select name="country" class="form-select form-select-sm"><option value="">All</option><?php foreach($countries as $c): ?><option value="<?php echo $c; ?>" <?php echo $country===$c?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><label class="form-label small">Courier</label><select name="courier" class="form-select form-select-sm"><option value="0">All</option><?php foreach($drivers as $d): ?><option value="<?php echo $d['id']; ?>" <?php echo $courier===$d['id']?'selected':''; ?>><?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach(allShipmentStatuses() as $c=>$l): ?><option value="<?php echo $c; ?>" <?php echo $status===$c?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary btn-sm">Apply Filters</button></div>
    </form>

    <div class="row g-3 mb-3">
        <?php
        $kpis = [
            ['Total','#1e3c72',$total,'bi-box-seam'],
            ['Active','#2a5298',$active,'bi-lightning'],
            ['Delivered','#16a34a',$delivered,'bi-check2-circle'],
            ['Delayed','#d97706',$delayed,'bi-hourglass-split'],
            ['Returned','#dc2626',$returned,'bi-arrow-return-left'],
            ['Failed','#dc2626',$failed,'bi-x-circle'],
            ['Revenue','#0d9488','$'.number_format($revenue,0),'bi-cash-stack'],
            ['Success Rate','#7c3aed',$successRate.'%','bi-graph-up'],
            ['Avg Delivery','#475569',round($avgDays,1).' d','bi-stopwatch'],
        ];
        foreach ($kpis as $k): ?>
        <div class="col-xl-3 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:<?php echo $k[1]; ?>22;color:<?php echo $k[1]; ?>;"><i class="bi <?php echo $k[3]; ?>"></i></div>
                <div><div class="text-muted small"><?php echo $k[0]; ?></div><div class="fw-bold fs-5"><?php echo $k[2]; ?></div></div>
            </div></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-6"><div class="card"><div class="card-header">Monthly Revenue</div><div class="card-body"><canvas id="revChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card"><div class="card-header">Shipments by Status</div><div class="card-body"><canvas id="statusChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card"><div class="card-header">Shipments by Country</div><div class="card-body"><canvas id="countryChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card"><div class="card-header">Top Destinations</div><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>City</th><th>Count</th></tr></thead><tbody>
            <?php foreach ($topDest as $t): ?><tr><td><?php echo htmlspecialchars($t['destination_city'] ?: 'N/A'); ?></td><td><?php echo $t['c']; ?></td></tr><?php endforeach; ?>
        </tbody></table></div></div></div></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revChart'), { type:'line', data:{ labels:<?php echo $json($months); ?>, datasets:[{ label:'Revenue', data:<?php echo $json($revSeries); ?>, borderColor:'#1e3c72', backgroundColor:'#1e3c7233', fill:true, tension:.3 }] }, options:{ responsive:true } });
new Chart(document.getElementById('statusChart'), { type:'doughnut', data:{ labels:<?php echo $json($statusLabels); ?>, datasets:[{ data:<?php echo $json($statusCounts); ?>, backgroundColor:['#1e3c72','#2a5298','#16a34a','#d97706','#dc2626','#7c3aed','#0d9488','#475569'] }] }, options:{ responsive:true } });
new Chart(document.getElementById('countryChart'), { type:'bar', data:{ labels:<?php echo $json($countryLabels); ?>, datasets:[{ label:'Shipments', data:<?php echo $json($countryCounts); ?>, backgroundColor:'#2a5298' }] }, options:{ responsive:true, indexAxis:'y' } });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
