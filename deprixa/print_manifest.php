<?php
/** Print a delivery manifest for one or more shipments. */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$db = getDB();
$ids = array_filter(array_map('intval', explode(',', $_GET['id'] ?? '')));
if (empty($ids)) { $ids = [intval($_GET['id'] ?? 0)]; }
$ids = array_filter($ids);
if (empty($ids)) { die('No shipment specified.'); }

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("
    SELECT s.*, d.name as driver_name, v.registration_number, l.name as branch_name
    FROM shipments s
    LEFT JOIN drivers d ON s.driver_id = d.id
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    LEFT JOIN locations l ON s.branch_id = l.id
    WHERE s.id IN ($placeholders)
    ORDER BY s.tracking_number ASC
    ");
$stmt->execute($ids);
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8">
<title>Delivery Manifest</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { padding: 2rem; }
    @media print { .no-print { display: none; } }
    table { font-size: 0.85rem; }
</style>
<script>window.onload = function(){ /* auto-print optional */ }; </script>
</head><body>
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4 class="mb-0">Delivery Manifest</h4>
    <div>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <a href="shipments.php" class="btn btn-secondary btn-sm">Close</a>
    </div>
</div>
<p class="text-muted">Generated: <?php echo date('Y-m-d H:i'); ?> | Total shipments: <?php echo count($shipments); ?></p>
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>#</th><th>Tracking</th><th>Reference</th><th>Sender</th><th>Recipient</th>
            <th>Route</th><th>Driver</th><th>Vehicle</th><th>Branch</th><th>Status</th><th>Signature</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($shipments as $i => $s): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($s['tracking_number']); ?></td>
            <td><?php echo htmlspecialchars($s['reference_number'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($s['sender_name'] ?? ''); ?><br><small><?php echo htmlspecialchars($s['sender_phone'] ?? ''); ?></small></td>
            <td><?php echo htmlspecialchars($s['receiver_name'] ?? ''); ?><br><small><?php echo htmlspecialchars($s['receiver_phone'] ?? ''); ?></small></td>
            <td><small><?php echo htmlspecialchars(($s['origin_city'] ?? '') . ' → ' . ($s['destination_city'] ?? '')); ?></small></td>
            <td><?php echo htmlspecialchars($s['driver_name'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($s['registration_number'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($s['branch_name'] ?? '—'); ?></td>
            <td><?php echo statusBadge($s['status']); ?></td>
            <td style="width:120px;border-bottom:1px solid #000;">&nbsp;</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body></html>
