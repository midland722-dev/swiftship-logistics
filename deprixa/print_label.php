<?php
/** Print a shipping label for a shipment (opens print dialog). */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/lib/ShipmentGenerator.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$db = getDB();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { die('Shipment not found.'); }

$gen = new ShipmentGenerator($db);
$path = $gen->generateShippingLabel($shipment);
$relative = str_replace(realpath(__DIR__ . '/../'), '', realpath($path));
$url = '/' . ltrim(str_replace('\\', '/', $relative), '/');
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8">
<title>Shipping Label - <?php echo htmlspecialchars($shipment['tracking_number']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>window.onload = function(){ window.print(); };</script>
</head><body class="p-4">
<iframe src="<?php echo htmlspecialchars($url); ?>" style="width:100%;height:90vh;border:0;"></iframe>
</body></html>
