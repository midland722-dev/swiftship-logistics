<?php
/**
 * Serve the shipment receipt as a real PDF.
 *
 * - Default: opens the PDF inline in the browser (for printing).
 * - ?download=1 or ?regenerate=1: forces a download (Content-Disposition: attachment)
 *   and regenerates the receipt from current shipment data.
 *
 * The receipt is always (re)generated from the latest shipment record, so the
 * stored PDF stays in sync after edits. The persisted path is updated when the
 * `pdf_receipt_path` column exists.
 */
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

// Always regenerate from current data so the receipt reflects the latest info.
$path = $gen->generatePDFReceipt($shipment);
if (!is_file($path)) {
    http_response_code(500);
    echo 'Unable to generate receipt PDF.';
    exit;
}

$download = isset($_GET['download']) || isset($_GET['regenerate']);

$firstBytes = @file_get_contents($path, false, null, 0, 5);
$isHtml = ($firstBytes !== false && stripos($firstBytes, '<!doctype') !== false) || preg_match('/\.html$/i', $path);

if ($isHtml) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . basename($path) . '"');
    readfile($path);
    exit;
}

// Persist the path for future access when the column exists.
if ($gen->columnExists('shipments', 'pdf_receipt_path')) {
    try {
        $rel = 'uploads/receipts/' . basename($path);
        $db->prepare("UPDATE shipments SET pdf_receipt_path = :p WHERE id = :id")
            ->execute([':p' => $rel, ':id' => $shipment['id']]);
    } catch (Exception $e) { /* best effort */ }
}

$download = isset($_GET['download']) || isset($_GET['regenerate']);
header('Content-Type: application/pdf');
header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=0, must-revalidate');
flush();
readfile($path);
exit;
