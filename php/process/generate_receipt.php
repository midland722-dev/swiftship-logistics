<?php
/**
 * generate_receipt.php
 *
 * Generates a PDF receipt/shipment document for a given shipment.
 * Uses the existing FPDF library (deprixa/fpdf/fpdf.php).
 *
 * GET/POST param: shipment_id or tracking_number
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/error-handler.php';
register_error_handler();

$shipmentId = isset($_REQUEST['shipment_id']) ? (int)$_REQUEST['shipment_id'] : 0;
$trackingNumber = trim((string)($_REQUEST['tracking_number'] ?? ''));

if ($shipmentId > 0) {
    $shipment = db_fetch_one('SELECT * FROM shipments WHERE id = :id LIMIT 1', [':id' => $shipmentId]);
} elseif ($trackingNumber !== '') {
    $shipment = db_fetch_one('SELECT * FROM shipments WHERE tracking_number = :tn LIMIT 1', [':tn' => $trackingNumber]);
} else {
    http_response_code(400);
    exit('Missing shipment_id or tracking_number.');
}

if (!$shipment) {
    $online = db_fetch_one(
        'SELECT cid AS id, cons_no AS tracking_number, status, type AS service_type, ship_name AS sender_name, s_phone AS sender_phone, s_add AS sender_address, rev_name AS receiver_name, r_phone AS receiver_phone, r_add AS receiver_address, date AS created_at
         FROM courier_online
         WHERE cons_no = :tn
         LIMIT 1',
        [':tn' => $trackingNumber]
    );

    if ($online) {
        $shipment = [
            'tracking_number' => $online['tracking_number'],
            'status' => $online['status'],
            'service_type' => $online['service_type'] ?? 'standard',
            'origin_city' => $online['sender_name'] ?? '',
            'origin_country' => '',
            'destination_city' => $online['receiver_name'] ?? '',
            'destination_country' => '',
            'total_weight' => null,
            'pieces' => 1,
            'currency' => 'USD',
            'created_at' => $online['created_at'] ?? date('Y-m-d H:i:s'),
            'sender_name' => $online['sender_name'] ?? '',
            'sender_phone' => $online['sender_phone'] ?? '',
            'sender_address' => $online['sender_address'] ?? '',
            'receiver_name' => $online['receiver_name'] ?? '',
            'receiver_phone' => $online['receiver_phone'] ?? '',
            'receiver_address' => $online['receiver_address'] ?? '',
        ];
    }
}

if (!$shipment) {
    http_response_code(404);
    exit('Shipment not found.');
}

$fpdfPath = __DIR__ . '/../deprixa/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    http_response_code(500);
    exit('PDF library not found at: ' . $fpdfPath);
}

if (!defined('FPDF_FONTPATH')) {
    $fontDir = dirname($fpdfPath) . '/font/';
    if (is_dir($fontDir)) {
        define('FPDF_FONTPATH', $fontDir);
    }
}

$prevErrorReporting = error_reporting(E_ALL & ~E_WARNING);
require_once $fpdfPath;
error_reporting($prevErrorReporting);

$prevErrorReporting = error_reporting(E_ALL & ~E_WARNING);
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
error_reporting($prevErrorReporting);

$tn = $shipment['tracking_number'];
$status = ucwords(str_replace('_', ' ', $shipment['status']));
$origin = trim(($shipment['origin_city'] ?? '') . ', ' . ($shipment['origin_country'] ?? ''));
$destination = trim(($shipment['destination_city'] ?? '') . ', ' . ($shipment['destination_country'] ?? ''));
$weight = $shipment['total_weight'] ?? '';
$pieces = $shipment['pieces'] ?? 1;
$service = $shipment['service_type'] ?? 'standard';
$date = $shipment['created_at'] ? date('Y-m-d', strtotime($shipment['created_at'])) : date('Y-m-d');

$pdf->Cell(0, 10, 'American Shipping & Logistics', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Shipment Receipt', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(5);

$pdf->Cell(0, 8, 'Tracking Number: ' . $tn, 0, 1);
$pdf->Cell(0, 8, 'Date: ' . $date, 0, 1);
$pdf->Cell(0, 8, 'Status: ' . $status, 0, 1);
$pdf->Cell(0, 8, 'Service: ' . $service, 0, 1);
$pdf->Ln(5);

$pdf->Cell(0, 8, 'Route', 0, 1);
$pdf->Cell(0, 8, 'From: ' . ($origin ?: '—'), 0, 1);
$pdf->Cell(0, 8, 'To: ' . ($destination ?: '—'), 0, 1);
$pdf->Ln(5);

$pdf->Cell(0, 8, 'Details', 0, 1);
$pdf->Cell(0, 8, 'Weight: ' . ($weight !== null && $weight !== '' ? $weight . ' kg' : '—'), 0, 1);
$pdf->Cell(0, 8, 'Pieces: ' . (int)$pieces, 0, 1);

if (!empty($shipment['sender_name'])) {
    $pdf->Ln(5);
    $pdf->Cell(0, 8, 'Sender: ' . $shipment['sender_name'], 0, 1);
    if (!empty($shipment['sender_phone'])) {
        $pdf->Cell(0, 8, 'Phone: ' . $shipment['sender_phone'], 0, 1);
    }
}

if (!empty($shipment['receiver_name'])) {
    $pdf->Ln(5);
    $pdf->Cell(0, 8, 'Receiver: ' . $shipment['receiver_name'], 0, 1);
    if (!empty($shipment['receiver_phone'])) {
        $pdf->Cell(0, 8, 'Phone: ' . $shipment['receiver_phone'], 0, 1);
    }
}

if (!empty($shipment['delivered_at'])) {
    $pdf->Ln(5);
    $pdf->Cell(0, 8, 'Delivered: ' . date('Y-m-d H:i', strtotime($shipment['delivered_at'])), 0, 1);
    if (!empty($shipment['delivered_signature_by'])) {
        $pdf->Cell(0, 8, 'Signed by: ' . $shipment['delivered_signature_by'], 0, 1);
    }
}

if (!empty($shipment['signature_image'])) {
    $pdf->Ln(5);
    $pdf->Cell(0, 8, 'Signature:', 0, 1);
    $sigPath = __DIR__ . '/../../uploads/signatures/' . basename($shipment['signature_image']);
    if (file_exists($sigPath)) {
        $pdf->Image($sigPath, $pdf->GetX(), $pdf->GetY(), 60);
        $pdf->Ln(30);
    }
}

if (!empty($shipment['stamp_image'])) {
    $pdf->Ln(5);
    $pdf->Cell(0, 8, 'Stamp:', 0, 1);
    $stampPath = __DIR__ . '/../../uploads/stamps/' . basename($shipment['stamp_image']);
    if (file_exists($stampPath)) {
        $pdf->Image($stampPath, $pdf->GetX(), $pdf->GetY(), 40);
        $pdf->Ln(25);
    }
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 8, 'Generated by American Shipping & Logistics — ' . date('Y-m-d H:i'), 0, 1, 'C');

$filename = 'receipt_' . $tn . '.pdf';
$pdf->Output($filename, 'D');
exit;
