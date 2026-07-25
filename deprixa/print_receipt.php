<?php
/** Print / view a shipment receipt as a real, printable PDF (opens in the browser). */
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/lib/ShipmentGenerator.php';

$db = getDB();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) {
    http_response_code(404);
    echo 'Shipment not found.';
    exit;
}

$gen = new ShipmentGenerator($db);
$shipment['receipt_number'] = $shipment['receipt_number'] ?? ('REC-' . $shipment['id']);

$path = $gen->generatePDFReceipt($shipment);
if (!is_file($path)) {
    http_response_code(500);
    echo 'Unable to generate receipt PDF.';
    exit;
}

$firstBytes = @file_get_contents($path, false, null, 0, 5);
$isHtml = ($firstBytes !== false && stripos($firstBytes, '<!doctype') !== false) || preg_match('/\.html$/i', $path);

if ($isHtml) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    readfile($path);
    exit;
}

if ($gen->columnExists('shipments', 'pdf_receipt_path')) {
    try {
        $rel = 'uploads/receipts/' . basename($path);
        $db->prepare("UPDATE shipments SET pdf_receipt_path = :p WHERE id = :id")
            ->execute([':p' => $rel, ':id' => $shipment['id']]);
    } catch (Exception $e) { /* best effort */ }
}

// Open the PDF inline so the browser's print dialog can be used.
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=0, must-revalidate');
flush();
readfile($path);
exit;
