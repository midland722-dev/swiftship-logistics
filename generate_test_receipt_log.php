<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/deprixa/lib/ShipmentGenerator.php';

$db = getDB();
$shipmentId = 13;

$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $shipmentId]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipment) {
    die("Shipment not found\n");
}

$gen = new ShipmentGenerator($db);
$shipment['receipt_number'] = $shipment['receipt_number'] ?? ('REC-' . $shipment['id']);

$path = $gen->generatePDFReceipt($shipment);

$log = [];
$log[] = "Shipment: " . $shipment['tracking_number'];
$log[] = "Path: $path";
$log[] = "Exists: " . (is_file($path) ? 'YES' : 'NO');
if (is_file($path)) {
    $log[] = "Size: " . filesize($path) . " bytes";
    $firstBytes = @file_get_contents($path, false, null, 0, 5);
    $log[] = "First bytes: " . bin2hex($firstBytes);
    $log[] = "Is PDF: " . (strpos($firstBytes, '%PDF-') === 0 ? 'YES' : 'NO');
}

file_put_contents('receipt_test_result.log', implode("\n", $log) . "\n");
