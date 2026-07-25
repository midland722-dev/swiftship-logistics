<?php
/**
 * Americans Shipping & Courier Logistics - Shipment Generation Utilities
 * Handles tracking numbers, receipt numbers, barcodes, QR codes, and PDF receipts
 */

class ShipmentGenerator {
    private $db;
    private $uploadDir;
    
    public function __construct($db, $uploadDir = null) {
        $this->db = $db;
        $this->uploadDir = $uploadDir ?? __DIR__ . '/../../uploads/';
    }
    
    /**
     * Check if a column exists in a table
     */
    public function columnExists($table, $column) {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate unique tracking number
     * Format: ASCXXXXXXXXXX (12 digits)
     */
    public function generateTrackingNumber() {
        $maxAttempts = 10;
        
        $hasTrackingCol = $this->columnExists('shipments', 'tracking_number');
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $number = str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
            $tracking = 'ASC' . $number;
            
            if ($hasTrackingCol) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments WHERE tracking_number = ?");
                $stmt->execute([$tracking]);
                if ($stmt->fetchColumn() == 0) {
                    return $tracking;
                }
            } else {
                return $tracking;
            }
        }
        
        return 'ASC' . str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate unique receipt number
     * Format: REC-XXXXXXXX
     */
    public function generateReceiptNumber() {
        $prefixes = ['REC', 'RCP', 'INV'];
        $maxAttempts = 10;
        
        $hasReceiptCol = $this->columnExists('shipments', 'receipt_number');
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $prefix = $prefixes[array_rand($prefixes)];
            $number = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $receipt = $prefix . '-' . $number;
            
            if ($hasReceiptCol) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments WHERE receipt_number = ?");
                $stmt->execute([$receipt]);
                if ($stmt->fetchColumn() == 0) {
                    return $receipt;
                }
            } else {
                return $receipt;
            }
        }
        
        return 'REC-' . time() . random_int(100, 999);
    }
    
    /**
     * Generate CODE128 barcode image using GD library
     */
    public function generateBarcode($trackingNumber, $savePath = null) {
        $savePath = $savePath ?? $this->uploadDir . 'barcodes/' . $trackingNumber . '.png';
        
        if (!function_exists('imagecreatetruecolor')) {
            return $this->generateBarcodeFallback($trackingNumber, $savePath);
        }
        
        // Ensure directory exists
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Simple CODE128-like barcode using GD
        $width = 400;
        $height = 80;
        $image = imagecreatetruecolor($width, $height);
        
        // Colors
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        
        // Generate barcode pattern (simplified CODE128 simulation)
        $chars = str_split($trackingNumber);
        $barWidth = 3;
        $x = 20;
        
        // Start pattern
        for ($i = 0; $i < 3; $i++) {
            imagefilledrectangle($image, $x, 10, $x + $barWidth - 1, $height - 10, $black);
            $x += $barWidth + 1;
        }
        
        // Data pattern
        foreach ($chars as $char) {
            $code = ord($char);
            $pattern = [];
            
            // Convert ASCII to binary-like pattern for barcode
            for ($bit = 0; $bit < 8; $bit++) {
                $pattern[] = ($code >> (7 - $bit)) & 1;
            }
            
            foreach ($pattern as $bit) {
                if ($bit) {
                    imagefilledrectangle($image, $x, 10, $x + $barWidth - 1, $height - 10, $black);
                }
                $x += $barWidth + ($bit ? 0 : 1);
            }
            
            $x += 2; // Gap between characters
        }
        
        // End pattern
        for ($i = 0; $i < 3; $i++) {
            imagefilledrectangle($image, $x, 10, $x + $barWidth - 1, $height - 10, $black);
            $x += $barWidth + 1;
        }
        
        // Add human-readable text
        imagestring($image, 5, 20, $height - 20, $trackingNumber, $black);
        
        // Save image
        imagepng($image, $savePath);
        imagedestroy($image);
        
        return $savePath;
    }
    
    /**
     * Fallback barcode generation when GD is not available
     */
    private function generateBarcodeFallback($trackingNumber, $savePath) {
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="80" viewBox="0 0 400 80">';
        $svg .= '<rect width="400" height="80" fill="white"/>';
        
        $x = 20;
        $barWidth = 3;
        $chars = str_split($trackingNumber);
        
        // Start pattern
        for ($i = 0; $i < 3; $i++) {
            $svg .= '<rect x="' . $x . '" y="10" width="' . $barWidth . '" height="60" fill="black"/>';
            $x += $barWidth + 1;
        }
        
        foreach ($chars as $char) {
            $code = ord($char);
            for ($bit = 0; $bit < 8; $bit++) {
                if (($code >> (7 - $bit)) & 1) {
                    $svg .= '<rect x="' . $x . '" y="10" width="' . $barWidth . '" height="60" fill="black"/>';
                }
                $x += $barWidth + ((($code >> (7 - $bit)) & 1) ? 0 : 1);
            }
            $x += 2;
        }
        
        // End pattern
        for ($i = 0; $i < 3; $i++) {
            $svg .= '<rect x="' . $x . '" y="10" width="' . $barWidth . '" height="60" fill="black"/>';
            $x += $barWidth + 1;
        }
        
        $svg .= '<text x="200" y="75" text-anchor="middle" font-family="monospace" font-size="14" fill="black">' . htmlspecialchars($trackingNumber) . '</text>';
        $svg .= '</svg>';
        
        $svgPath = preg_replace('/\.png$/', '.svg', $savePath);
        file_put_contents($svgPath, $svg);
        
        return $svgPath;
    }
    
    /**
     * Generate QR code image (simple implementation using Google Charts API fallback)
     * If API fails, generates a placeholder
     */
    public function generateQRCode($trackingNumber, $savePath = null) {
        $savePath = $savePath ?? $this->uploadDir . 'barcodes/' . $trackingNumber . '_qr.png';

        // Ensure directory exists
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $trackingUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/track.php?cons_no=' . urlencode($trackingNumber);

        // Primary: local, dependency-free QR encoder (works fully offline).
        try {
            if (!class_exists('QRCode')) {
                require_once __DIR__ . '/../../lib/QRCode.php';
            }
            $matrix = QRCode::encode($trackingUrl, 'M');
            $local = $this->renderQrMatrixPng($matrix, $savePath);
            if ($local) {
                return $local;
            }
        } catch (Throwable $e) {
            // fall through to external API
        }

        // Fallback 1: external QR Server API.
        $qrData = @file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($trackingUrl));
        if ($qrData) {
            file_put_contents($savePath, $qrData);
            return $savePath;
        }

        // Fallback 2: Generate SVG QR-like placeholder.
        return $this->generateQRFallback($trackingNumber, $savePath);
    }

    /**
     * Render a QRCode matrix (array of 0/1) to a real, scannable PNG via GD.
     */
    private function renderQrMatrixPng(array $matrix, $savePath, $scale = 6, $margin = 4) {
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }
        $size = count($matrix);
        $imgSize = ($size + 2 * $margin) * $scale;
        $img = imagecreatetruecolor($imgSize, $imgSize);
        if ($img === false) {
            return false;
        }
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (!empty($matrix[$r][$c])) {
                    imagefilledrectangle(
                        $img,
                        ($margin + $c) * $scale, ($margin + $r) * $scale,
                        ($margin + $c) * $scale + $scale - 1, ($margin + $r) * $scale + $scale - 1,
                        $black
                    );
                }
            }
        }
        $ok = @imagepng($img, $savePath);
        imagedestroy($img);
        return $ok ? $savePath : false;
    }
    
    /**
     * Fallback QR code generation when API and GD are not available
     */
    private function generateQRFallback($trackingNumber, $savePath) {
        $svgPath = preg_replace('/\.png$/', '.svg', $savePath);
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">';
        $svg .= '<rect width="200" height="200" fill="white"/>';
        
        // Generate pseudo-random pattern based on tracking number
        $seed = crc32($trackingNumber);
        $cellSize = 10;
        
        for ($x = 0; $x < 200; $x += $cellSize) {
            for ($y = 0; $y < 200; $y += $cellSize) {
                $hash = md5($seed . $x . $y);
                if (hexdec($hash[0]) > 7) {
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="black"/>';
                }
            }
        }
        
        // Add corner markers (QR style)
        $markerSize = 50;
        $markerPositions = [[10, 10], [140, 10], [10, 140]];
        
        foreach ($markerPositions as $pos) {
            $svg .= '<rect x="' . $pos[0] . '" y="' . $pos[1] . '" width="' . $markerSize . '" height="' . $markerSize . '" fill="black"/>';
            $svg .= '<rect x="' . ($pos[0] + 10) . '" y="' . ($pos[1] + 10) . '" width="' . ($markerSize - 20) . '" height="' . ($markerSize - 20) . '" fill="white"/>';
            $svg .= '<rect x="' . ($pos[0] + 20) . '" y="' . ($pos[1] + 20) . '" width="' . ($markerSize - 40) . '" height="' . ($markerSize - 40) . '" fill="black"/>';
        }
        
        $svg .= '<text x="100" y="195" text-anchor="middle" font-family="Arial" font-size="10" fill="black">' . htmlspecialchars($trackingNumber) . '</text>';
        $svg .= '</svg>';
        
        file_put_contents($svgPath, $svg);
        
        return $svgPath;
    }
    
    /**
     * Generate a professional PDF receipt.
     *
     * Primary path: the self-contained ReceiptPDF generator (no external
     * dependencies) which produces a real, printable A4 PDF. If that fails for
     * any reason we gracefully fall back to the previous behaviour (DomPDF if
     * installed, otherwise the HTML receipt) so existing functionality is
     * never broken.
     */
    public function generatePDFReceipt($shipment, $savePath = null) {
        $shipment = $this->normalizeShipmentData((array) $shipment);
        $tracking = $shipment['tracking_number'] ?? ('receipt-' . ($shipment['id'] ?? time()));
        $savePath = $savePath ?? $this->uploadDir . 'receipts/' . $tracking . '.pdf';

        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        try {
            if (!class_exists('ReceiptPDF')) {
                require_once __DIR__ . '/../../lib/ReceiptPDF.php';
            }
            $pdf = $this->buildReceiptPDF($shipment);
            if (!$pdf instanceof ReceiptPDF) {
                throw new RuntimeException('buildReceiptPDF did not return a ReceiptPDF instance');
            }
            $raw = $pdf->output();
            if (empty($raw) || strpos($raw, '%PDF-') !== 0) {
                throw new RuntimeException('ReceiptPDF produced empty or invalid output');
            }
            if (@file_put_contents($savePath, $raw) === false) {
                throw new RuntimeException('Failed to write receipt PDF to disk: ' . $savePath);
            }
            if (function_exists('clearDashboardCache')) { clearDashboardCache(); }
            return $savePath;
        } catch (Throwable $e) {
            $this->logPdfError($tracking, $e);
        }

        if (class_exists('Dompdf\Dompdf') && method_exists($this, 'generatePDFWithDomPDF')) {
            try {
                return $this->generatePDFWithDomPDF($shipment, $savePath);
            } catch (Throwable $e) {
                $this->logPdfError($tracking, $e);
            }
        }
        return $this->generateHTMLReceipt($shipment, $savePath);
    }

    /**
     * Normalize any shipment array to the canonical receipt format.
     */
    private function normalizeShipmentData(array $shipment): array {
        $aliases = [
            'pieces' => ['quantity'],
            'estimated_delivery' => ['expected_delivery'],
            'contents' => ['package_description', 'package_name'],
            'volumetric_weight' => ['vol_weight'],
            'special_instructions' => ['special_instruction', 'special_inst'],
            'customer_notes' => ['customer_note', 'cust_notes', 'notes'],
            'item_category' => ['category', 'item_cat'],
            'total_weight' => ['weight'],
            'length' => ['package_length'],
            'width' => ['package_width'],
            'height' => ['package_height'],
        ];
        $get = function($k, $d = '') use ($shipment, $aliases) {
            $x = $shipment[$k] ?? null;
            if (($x === null || $x === '') && isset($aliases[$k])) {
                foreach ($aliases[$k] as $alt) {
                    if (array_key_exists($alt, $shipment)) {
                        $x = $shipment[$alt];
                        if ($x !== null && $x !== '') break;
                    }
                }
            }
            if ($x === null || $x === '') return $d;
            if (is_array($x)) return implode(', ', array_map(function($i){ return is_array($i) ? implode(', ', $i) : $i; }, $x));
            return $x;
        };
        $weight = (float) ($shipment['total_weight'] ?? $shipment['weight'] ?? 0);
        $length = (float) ($shipment['length'] ?? $shipment['package_length'] ?? 0);
        $width  = (float) ($shipment['width'] ?? $shipment['package_width'] ?? 0);
        $height = (float) ($shipment['height'] ?? $shipment['package_height'] ?? 0);
        return [
            'tracking_number'       => $get('tracking_number'),
            'receipt_number'        => $get('receipt_number'),
            'reference_number'      => $get('reference_number'),
            'status'                => $get('status', 'pending'),
            'shipment_type'         => $get('shipment_type', 'parcel'),
            'service_type'          => $get('service_type', 'standard'),
            'priority'              => $get('priority', 'standard'),
            'shipment_date'         => $get('shipment_date') ?: date('Y-m-d H:i:s'),
            'estimated_delivery'    => $get('estimated_delivery'),
            'actual_delivery'       => $get('actual_delivery'),
            'origin_country'        => $get('origin_country'),
            'origin_city'           => $get('origin_city'),
            'destination_country'   => $get('destination_country'),
            'destination_city'      => $get('destination_city'),
            'sender_name'           => $get('sender_name'),
            'sender_company'        => $get('sender_company'),
            'sender_phone'          => $get('sender_phone'),
            'sender_email'          => $get('sender_email'),
            'sender_address'        => $get('sender_address'),
            'sender_state'          => $get('sender_state'),
            'sender_postal'         => $get('sender_postal'),
            'sender_country'        => $get('sender_country'),
            'receiver_name'         => $get('receiver_name'),
            'receiver_company'      => $get('receiver_company'),
            'receiver_phone'        => $get('receiver_phone'),
            'receiver_email'        => $get('receiver_email'),
            'receiver_address'      => $get('receiver_address'),
            'receiver_state'        => $get('receiver_state'),
            'receiver_postal'       => $get('receiver_postal'),
            'receiver_country'      => $get('receiver_country'),
            'package_name'          => $get('package_name'),
            'package_description'   => $get('package_description'),
            'contents'              => $get('contents'),
            'length'                => $length,
            'width'                 => $width,
            'height'                => $height,
            'total_weight'          => $weight,
            'volumetric_weight'     => max(0, ($length * $width * $height) / 5000),
            'declared_value'        => (float) ($shipment['declared_value'] ?? 0),
            'cod_amount'            => (float) ($shipment['cod_amount'] ?? 0),
            'currency'              => $get('currency', 'USD'),
            'pieces'                => max(1, (int) ($shipment['pieces'] ?? $shipment['quantity'] ?? 1)),
            'is_fragile'            => !empty($shipment['is_fragile']) ? 1 : 0,
            'is_hazardous'          => !empty($shipment['is_hazardous']) ? 1 : 0,
            'is_insured'            => !empty($shipment['is_insured']) ? 1 : 0,
            'insurance_amount'      => (float) ($shipment['insurance_amount'] ?? 0),
            'payment_status'        => $get('payment_status', 'pending'),
            'payment_method'        => $get('payment_method', 'cash'),
            'total_amount'          => (float) ($shipment['total_amount'] ?? 0),
            'notes'                 => $get('notes'),
            'special_instructions'  => $get('special_instructions'),
            'customer_notes'        => $get('customer_notes'),
            'created_by'            => $shipment['created_by'] ?? null,
            'created_at'            => $get('created_at') ?: date('Y-m-d H:i:s'),
            'transaction_id'        => $get('transaction_id'),
            'invoice_number'        => $get('invoice_number'),
            'signature_required'    => !empty($shipment['signature_required']) ? 1 : 0,
            'weekend_delivery'      => !empty($shipment['weekend_delivery']) ? 1 : 0,
            'contact_before_delivery' => !empty($shipment['contact_before_delivery']) ? 1 : 0,
            'leave_at_door'         => !empty($shipment['leave_at_door']) ? 1 : 0,
            'preferred_delivery_time' => $get('preferred_delivery_time'),
            'hs_code'               => $get('hs_code'),
            'country_of_origin'     => $get('country_of_origin'),
            'import_duty'           => $get('import_duty'),
            'customs_documents'     => $get('customs_documents'),
            'item_category'         => $get('item_category'),
        ];
    }

    /**
     * Log a PDF generation failure through the project logger.
     */
    private function logPdfError($tracking, $e) {
        try {
            $logger = getLogger();
            $logger->log('receipt_pdf', 'PDF receipt generation failed for ' . $tracking . ': ' . $e->getMessage());
        } catch (Throwable $t) {
            // Logging is best-effort; never let it break the request.
            error_log('ReceiptPDF error (' . $tracking . '): ' . $e->getMessage());
        }
    }
    
    /**
     * Generate comprehensive clean HTML receipt (fallback when DomPDF is not available)
     * Includes ALL entered shipment data for complete records
     */
    private function generateHTMLReceipt($shipment, $savePath) {
        $htmlPath = preg_replace('/\.pdf$/', '.html', $savePath);
        
        // Load company settings for brand consistency
        $company = ['name' => SITE_NAME, 'email' => 'info@ascl-logistics.com', 'phone' => '+12158159791', 'address' => '4500 Harbor Boulevard, Long Beach, CA 90802, USA'];
        try {
            $stmt = $this->db->query("SELECT name, email, phone, address FROM company LIMIT 1");
            $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: $company;
        } catch (Exception $e) {}
        
        $statusDisplay = ucwords(str_replace('_', ' ', $shipment['status'] ?? 'Pending'));
        $statusBadge = 'bg-warning';
        if (($shipment['status'] ?? '') === 'delivered') $statusBadge = 'bg-success';
        elseif (in_array($shipment['status'] ?? '', ['in_transit', 'picked_up', 'out_for_delivery'])) $statusBadge = 'bg-primary';
        elseif (in_array($shipment['status'] ?? '', ['cancelled', 'returned'])) $statusBadge = 'bg-danger';
        
        $shipmentDate = !empty($shipment['shipment_date']) ? date('F d, Y H:i', strtotime($shipment['shipment_date'])) : date('F d, Y H:i');
        $estimatedDelivery = !empty($shipment['estimated_delivery']) ? date('F d, Y', strtotime($shipment['estimated_delivery'])) : 'N/A';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt - ' . htmlspecialchars($shipment['tracking_number']) . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 2rem; background: #f5f7fa; color: #1F2937; }
        .receipt { max-width: 850px; margin: 0 auto; background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; border-bottom: 4px solid #0F4C81; padding-bottom: 1.5rem; margin-bottom: 2rem; }
        .logo { font-size: 2.2rem; font-weight: 800; color: #0F4C81; letter-spacing: -0.5px; }
        .subtitle { color: #6B7280; font-size: 0.95rem; margin-top: 0.25rem; }
        .section { margin-bottom: 1.75rem; padding: 1.25rem; background: #F8FAFC; border-radius: 10px; border: 1px solid #E5E7EB; }
        .section-title { font-weight: 700; color: #0F4C81; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #0F4C81; display: inline-block; }
        .info-row { display: flex; margin-bottom: 0.5rem; align-items: baseline; }
        .info-label { width: 160px; font-weight: 600; color: #4B5563; font-size: 0.9rem; flex-shrink: 0; }
        .info-value { flex: 1; color: #1F2937; font-size: 0.95rem; }
        .total-box { background: linear-gradient(135deg, #0F4C81 0%, #3FA9F5 100%); color: white; padding: 1.25rem; border-radius: 10px; text-align: right; margin-top: 1rem; }
        .total-label { font-size: 0.85rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.05em; }
        .total-amount { font-size: 2rem; font-weight: 800; margin-top: 0.25rem; }
        .footer { margin-top: 2.5rem; text-align: center; color: #6B7280; font-size: 0.85rem; border-top: 1px solid #E5E7EB; padding-top: 1.25rem; }
        .barcode { text-align: center; margin: 1rem 0; font-family: "Courier New", monospace; font-size: 1.3rem; letter-spacing: 3px; font-weight: 700; color: #0F4C81; padding: 0.75rem; background: #F8FAFC; border-radius: 8px; border: 1px dashed #0F4C81; }
        .qr-section { text-align: center; margin: 1rem 0; padding: 1rem; background: #F8FAFC; border-radius: 8px; border: 1px solid #E5E7EB; }
        .qr-section code { display: block; margin-top: 0.5rem; font-size: 0.8rem; color: #6B7280; word-break: break-all; }
        .no-print { text-align: center; margin-top: 2rem; }
        .no-print .btn { margin: 0 0.25rem; }
        @media print { body { padding: 0; background: white; } .receipt { box-shadow: none; max-width: 100%; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="logo">' . htmlspecialchars($company['name'] ?? SITE_NAME) . '</div>
            <div class="subtitle">Global Courier & Shipping Solutions</div>
            <div class="subtitle">' . htmlspecialchars($company['address'] ?? '') . '</div>
            <div class="subtitle">Tel: ' . htmlspecialchars($company['phone'] ?? '') . ' | Email: ' . htmlspecialchars($company['email'] ?? '') . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Receipt Information</div>
            <div class="info-row">
                <div class="info-label">Receipt Number:</div>
                <div class="info-value"><strong>' . htmlspecialchars($shipment['receipt_number'] ?? 'N/A') . '</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Tracking Number:</div>
                <div class="info-value"><code>' . htmlspecialchars($shipment['tracking_number']) . '</code></div>
            </div>
            <div class="info-row">
                <div class="info-label">Shipment Date:</div>
                <div class="info-value">' . $shipmentDate . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value"><span class="badge ' . $statusBadge . '">' . $statusDisplay . '</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">Service Type:</div>
                <div class="info-value">' . htmlspecialchars(ucfirst($shipment['service_type'] ?? 'Standard')) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Priority:</div>
                <div class="info-value">' . htmlspecialchars(ucfirst($shipment['priority'] ?? 'Standard')) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estimated Delivery:</div>
                <div class="info-value">' . $estimatedDelivery . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Status:</div>
                <div class="info-value">' . htmlspecialchars(ucfirst($shipment['payment_status'] ?? 'Pending')) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Method:</div>
                <div class="info-value">' . htmlspecialchars(ucfirst($shipment['payment_method'] ?? 'N/A')) . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Sender Information</div>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">' . htmlspecialchars($shipment['sender_name'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Company:</div>
                <div class="info-value">' . htmlspecialchars($shipment['sender_company'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">' . htmlspecialchars($shipment['sender_address'] ?? 'N/A') . ', ' . htmlspecialchars($shipment['sender_city'] ?? '') . ', ' . htmlspecialchars($shipment['sender_state'] ?? '') . ' ' . htmlspecialchars($shipment['sender_postal'] ?? '') . ' ' . htmlspecialchars($shipment['sender_country'] ?? '') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">' . htmlspecialchars($shipment['sender_phone'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">' . htmlspecialchars($shipment['sender_email'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Receiver Information</div>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">' . htmlspecialchars($shipment['receiver_name'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Company:</div>
                <div class="info-value">' . htmlspecialchars($shipment['receiver_company'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">' . htmlspecialchars($shipment['receiver_address'] ?? 'N/A') . ', ' . htmlspecialchars($shipment['receiver_city'] ?? '') . ', ' . htmlspecialchars($shipment['receiver_state'] ?? '') . ' ' . htmlspecialchars($shipment['receiver_postal'] ?? '') . ' ' . htmlspecialchars($shipment['receiver_country'] ?? '') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">' . htmlspecialchars($shipment['receiver_phone'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">' . htmlspecialchars($shipment['receiver_email'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Delivery Instructions:</div>
                <div class="info-value">' . htmlspecialchars($shipment['special_instructions'] ?? 'None') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Package Information</div>
            <div class="info-row">
                <div class="info-label">Package Name:</div>
                <div class="info-value">' . htmlspecialchars($shipment['package_name'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Description:</div>
                <div class="info-value">' . htmlspecialchars($shipment['package_description'] ?? 'N/A') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Weight:</div>
                <div class="info-value">' . htmlspecialchars($shipment['total_weight'] ?? 'N/A') . ' kg</div>
            </div>
            <div class="info-row">
                <div class="info-label">Dimensions (LxWxH):</div>
                <div class="info-value">' . htmlspecialchars($shipment['length'] ?? '0') . ' x ' . htmlspecialchars($shipment['width'] ?? '0') . ' x ' . htmlspecialchars($shipment['height'] ?? '0') . ' cm</div>
            </div>
            <div class="info-row">
                <div class="info-label">Volumetric Weight:</div>
                <div class="info-value">' . htmlspecialchars($shipment['volumetric_weight'] ?? 'N/A') . ' kg</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pieces / Qty:</div>
                <div class="info-value">' . htmlspecialchars($shipment['pieces'] ?? $shipment['quantity'] ?? '1') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Shipment Type:</div>
                <div class="info-value">' . htmlspecialchars(ucfirst($shipment['shipment_type'] ?? $shipment['type'] ?? 'Parcel')) . '</div>
            </div>' . (!empty($shipment['contents']) ? '
            <div class="info-row">
                <div class="info-label">Contents:</div>
                <div class="info-value">' . htmlspecialchars(is_array($shipment['contents']) ? implode(', ', $shipment['contents']) : $shipment['contents']) . '</div>
            </div>' : '') . '
        </div>
        
        <div class="section">
            <div class="section-title">Delivery & Route</div>
            <div class="info-row">
                <div class="info-label">Origin:</div>
                <div class="info-value">' . htmlspecialchars($shipment['origin_city'] ?? 'N/A') . ', ' . htmlspecialchars($shipment['origin_country'] ?? '') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Destination:</div>
                <div class="info-value">' . htmlspecialchars($shipment['destination_city'] ?? 'N/A') . ', ' . htmlspecialchars($shipment['destination_country'] ?? '') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Mode:</div>
                <div class="info-value">' . htmlspecialchars($shipment['mode'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Insurance & Declared Value</div>
            <div class="info-row">
                <div class="info-label">Declared Value:</div>
                <div class="info-value">$' . number_format($shipment['declared_value'] ?? 0, 2) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Insurance Amount:</div>
                <div class="info-value">$' . number_format($shipment['insurance_amount'] ?? 0, 2) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Coverage:</div>
                <div class="info-value">' . htmlspecialchars(($shipment['is_insured'] ?? false) ? 'Yes' : 'No') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fragile:</div>
                <div class="info-value">' . htmlspecialchars(($shipment['is_fragile'] ?? false) ? 'Yes' : 'No') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Cost Breakdown</div>
            <div class="info-row">
                <div class="info-label">Base Cost:</div>
                <div class="info-value">$' . number_format($shipment['base_cost'] ?? 0, 2) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Weight Charge:</div>
                <div class="info-value">$' . number_format($shipment['weight_charge'] ?? 0, 2) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Insurance Cost:</div>
                <div class="info-value">$' . number_format($shipment['insurance_cost'] ?? 0, 2) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tax:</div>
                <div class="info-value">$' . number_format($shipment['tax_amount'] ?? 0, 2) . '</div>
            </div>
        </div>
        
        <div class="total-box">
            <div class="total-label">Total Amount</div>
            <div class="total-amount">$' . number_format($shipment['total_amount'] ?? $shipment['total_cost'] ?? 0, 2) . '</div>
            <div style="font-size: 0.85rem; opacity: 0.9;">' . htmlspecialchars($shipment['currency'] ?? 'USD') . ' | ' . htmlspecialchars(ucfirst($shipment['payment_method'] ?? 'N/A')) . ' | ' . htmlspecialchars(ucfirst($shipment['payment_status'] ?? 'Pending')) . '</div>
        </div>
        
        ' . (!empty($shipment['notes']) ? '
        <div class="section">
            <div class="section-title">Internal Notes</div>
            <p class="mb-0" style="white-space: pre-wrap;">' . htmlspecialchars($shipment['notes']) . '</p>
        </div>' : '') . '
        
        <div class="barcode">
            || | | || | | | || || | || || | || || || | | | || || || | || ||
        </div>
        <div class="barcode" style="font-size: 1.1rem; letter-spacing: 2px; border: none; background: transparent;">
            ' . htmlspecialchars($shipment['tracking_number']) . '
        </div>
        
        <div class="qr-section">
            <strong>QR Code - Scan to Track</strong><br>
            <small class="text-muted">Track URL: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/track.php?tracking=' . urlencode($shipment['tracking_number']) . '</small>
            <code>' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/track.php?cons_no=' . urlencode($shipment['tracking_number']) . '</code>
        </div>
        
        <div class="footer">
            <p><strong>' . htmlspecialchars($company['name'] ?? SITE_NAME) . '</strong> | ' . htmlspecialchars($company['address'] ?? '') . '</p>
            <p>Tel: ' . htmlspecialchars($company['phone'] ?? '') . ' | Email: ' . htmlspecialchars($company['email'] ?? '') . '</p>
            <p class="no-print"><button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Print Receipt</button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</button></p>
        </div>
    </div>
    
    <script>
        // Auto-trigger print dialog when opened in new tab (optional)
        if (window.opener) {
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>';
        
        file_put_contents($htmlPath, $html);
        
        // Return HTML path as the generated receipt
        return $htmlPath;
    }

    /* ---------------------------------------------------------------- *
     *  Real PDF receipt builder (ReceiptPDF, no external dependencies)
     * ---------------------------------------------------------------- */

    /** Brand palette used across the receipt layout. */
    private $cNavy   = [15, 76, 129];
    private $cAccent = [63, 169, 245];
    private $cLight  = [241, 245, 249];
    private $cBorder = [225, 229, 235];
    private $cText   = [31, 41, 55];
    private $cMuted  = [107, 114, 128];

    /**
     * Build and return a configured ReceiptPDF for the given shipment.
     */
    private function buildReceiptPDF($shipment) {
        require_once __DIR__ . '/../../lib/ReceiptPDF.php';
        $pdf = new ReceiptPDF('P', 'A4');
        $company = $this->pdfCompanyInfo($shipment);
        $tracking = $shipment['tracking_number'] ?? 'N/A';
        $v = function($k, $d = 'N/A') {
            $x = $shipment[$k] ?? null;
            if ($x === null || $x === '') return $d;
            if (is_array($x)) return implode(', ', array_map(function($i){ return is_array($i) ? implode(', ', $i) : $i; }, $x));
            return $x;
        };
        $cur = $shipment['currency'] ?? 'USD';
        $money = function($n) use ($cur) { return $cur . ' ' . number_format((float) $n, 2); };

        $pdf->onHeader = function($p) use ($company) {
            $this->pdfHeader($p, $company);
        };
        $pdf->onFooter = function($p, $n, $t) use ($company) {
            $this->pdfFooter($p, $n, $t, $company);
        };

        $aliases = [
            'pieces' => ['quantity'],
            'estimated_delivery' => ['expected_delivery'],
            'contents' => ['package_description', 'package_name'],
            'volumetric_weight' => ['vol_weight'],
            'special_instructions' => ['special_instruction', 'special_inst'],
            'customer_notes' => ['customer_note', 'cust_notes'],
            'item_category' => ['category', 'item_cat'],
        ];
        $v = function($k, $d = 'N/A') use ($shipment, $aliases) {
            $x = $shipment[$k] ?? null;
            if (($x === null || $x === '') && isset($aliases[$k])) {
                foreach ($aliases[$k] as $alt) {
                    $x = $shipment[$alt] ?? null;
                    if ($x !== null && $x !== '') break;
                }
            }
            if ($x === null || $x === '') return $d;
            if (is_array($x)) return implode(', ', array_map(function($i){ return is_array($i) ? implode(', ', $i) : $i; }, $x));
            return $x;
        };
        $boxH = 30;
        $pdf->setFillColor($this->cLight[0], $this->cLight[1], $this->cLight[2]);
        $pdf->rect($pdf->marginL, $pdf->cy, $pdf->contentW, $boxH, 'F');
        $pdf->setDrawColor($this->cBorder[0], $this->cBorder[1], $this->cBorder[2]);
        $pdf->rect($pdf->marginL, $pdf->cy, $pdf->contentW, $boxH, 'D');

        $lx = $pdf->marginL + 5;
        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($lx, $pdf->cy + 7, 'RECEIPT NUMBER');
        $pdf->setFont('helvetica', 'B', 11);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $pdf->text($lx, $pdf->cy + 12, $v('receipt_number'));

        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($lx, $pdf->cy + 19, 'REFERENCE');
        $pdf->setFont('helvetica', '', 10);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $pdf->text($lx, $pdf->cy + 23.5, $v('reference_number'));

        $mx = $pdf->marginL + 95;
        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($mx, $pdf->cy + 7, 'DATE & TIME CREATED');
        $pdf->setFont('helvetica', '', 10);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $created = !empty($shipment['created_at']) ? date('M d, Y H:i', strtotime($shipment['created_at'])) : date('M d, Y H:i');
        $pdf->text($mx, $pdf->cy + 11.5, $created);
        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($mx, $pdf->cy + 19, 'CREATED BY');
        $pdf->setFont('helvetica', '', 10);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $pdf->text($mx, $pdf->cy + 23.5, $this->pdfCreatorName($shipment));

        // Tracking number (large, right aligned)
        $rx = $pdf->marginL + 175;
        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($rx, $pdf->cy + 7, 'TRACKING NUMBER');
        $pdf->setFont('helvetica', 'B', 16);
        $pdf->setTextColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
        $pdf->text($rx, $pdf->cy + 13, $tracking);
        $pdf->cy += $boxH + 4;

        /* ---- Sender / Recipient ---- */
        $this->pdfSection($pdf, 'Sender & Recipient Information');
        $this->pdfRow2($pdf,
            ['Sender Name', $v('sender_name')],
            ['Recipient Name', $v('receiver_name')]
        );
        $this->pdfRow2($pdf,
            ['Sender Phone', $v('sender_phone')],
            ['Recipient Phone', $v('receiver_phone')]
        );
        $this->pdfRow2($pdf,
            ['Sender Email', $v('sender_email')],
            ['Recipient Email', $v('receiver_email')]
        );
        $this->pdfRow2($pdf,
            ['Sender Company', $v('sender_company')],
            ['Recipient Company', $v('receiver_company')]
        );
        $this->pdfRow1($pdf, 'Sender Address',
            $v('sender_address') . ', ' . $v('sender_city') . ' ' . $v('sender_state') . ' ' . $v('sender_postal') . ' ' . $v('sender_country'));
        $this->pdfRow1($pdf, 'Delivery Address',
            $v('receiver_address') . ', ' . $v('receiver_city') . ' ' . $v('receiver_state') . ' ' . $v('receiver_postal') . ' ' . $v('receiver_country'));

        /* ---- Shipment details ---- */
        $this->pdfSection($pdf, 'Shipment Details');
        $this->pdfRow2($pdf, ['Shipment Type', ucfirst($v('shipment_type'))], ['Service Type', ucfirst($v('service_type'))]);
        $this->pdfRow2($pdf, ['Origin', $v('origin_city') . ', ' . $v('origin_country')], ['Destination', $v('destination_city') . ', ' . $v('destination_country')]);
        $this->pdfRow2($pdf,
            ['Pickup Date', !empty($shipment['shipment_date']) ? date('M d, Y', strtotime($shipment['shipment_date'])) : 'N/A'],
            ['Expected Delivery', !empty($shipment['estimated_delivery']) ? date('M d, Y', strtotime($shipment['estimated_delivery'])) : 'N/A']
        );
        $this->pdfRow2($pdf, ['Current Status', ucwords(str_replace('_', ' ', $v('status')))], ['Priority', ucfirst($v('priority'))]);

        /* ---- Parcel details ---- */
        $this->pdfSection($pdf, 'Parcel Details');
        $this->pdfRow2($pdf, ['Number of Packages', $v('pieces', 1)], ['Weight', $v('total_weight') . ' kg']);
        $this->pdfRow2($pdf,
            ['Dimensions (LxWxH)', $v('length') . ' x ' . $v('width') . ' x ' . $v('height') . ' cm'],
            ['Volumetric Weight', $v('volumetric_weight') . ' kg']
        );
        $this->pdfRow2($pdf,
            ['Declared Value', $money($shipment['declared_value'] ?? 0)],
            ['Item Category', ucfirst(str_replace('_', ' ', $v('item_category', 'Other')))]
        );
        $this->pdfRow1($pdf, 'Contents Description', $v('contents'));
        $this->pdfRow2($pdf, ['Fragile', $v('is_fragile') ? 'Yes' : 'No'], ['Hazardous', $v('is_hazardous') ? 'Yes' : 'No']);
        $this->pdfRow2($pdf, ['Insured', $v('is_insured') ? 'Yes' : 'No'], ['Insurance Value', $money($shipment['insurance_amount'] ?? 0)]);
        $this->pdfRow2($pdf, ['COD Amount', $money($shipment['cod_amount'] ?? 0)], ['Special Instructions', $v('special_instructions')]);

        /* ---- Customs & Compliance ---- */
        if (!empty($shipment['customs_declaration_number']) || !empty($shipment['hs_code']) || !empty($shipment['country_of_origin']) || !empty($shipment['import_duty'])) {
            $this->pdfSection($pdf, 'Customs & Compliance');
            $this->pdfRow2($pdf, ['Declaration No', $v('customs_declaration_number')], ['HS Code', $v('hs_code')]);
            $this->pdfRow2($pdf, ['Country of Origin', $v('country_of_origin')], ['Import Duty', $money($shipment['import_duty'] ?? 0)]);
            if (!empty($shipment['customs_documents'])) {
                $this->pdfRow1($pdf, 'Customs Documents', $v('customs_documents'));
            }
        }

        /* ---- Delivery Preferences ---- */
        $prefs = [];
        if (!empty($shipment['signature_required'])) $prefs[] = 'Signature Required';
        if (!empty($shipment['contact_before_delivery'])) $prefs[] = 'Contact Before Delivery';
        if (!empty($shipment['leave_at_door'])) $prefs[] = 'Leave at Door';
        if (!empty($shipment['weekend_delivery'])) $prefs[] = 'Weekend Delivery';
        if (!empty($shipment['preferred_delivery_time'])) $prefs[] = 'Preferred: ' . $v('preferred_delivery_time');
        if (!empty($prefs)) {
            $this->pdfSection($pdf, 'Delivery Preferences');
            $this->pdfRow1($pdf, 'Preferences', implode(', ', $prefs));
        }

        /* ---- Charges summary ---- */
        $shippingFee = (float)($shipment['shipping_cost'] ?? 0);
        if ($shippingFee <= 0) {
            $shippingFee = (float)($shipment['base_cost'] ?? 0) + (float)($shipment['weight_charge'] ?? 0);
        }
        $additional = (float)($shipment['additional_charges'] ?? 0);
        $insurance = (float)($shipment['insurance_amount'] ?? 0);
        $cod = (float)($shipment['cod_amount'] ?? 0);
        $tax = (float)($shipment['tax'] ?? $shipment['tax_amount'] ?? 0);
        $discount = (float)($shipment['discount'] ?? 0);
        $total = $shippingFee + $additional + $insurance + $cod + $tax - $discount;
        if ($total < 0) { $total = 0; }

        $this->pdfSection($pdf, 'Charges Summary');
        $charges = [];
        if ($shippingFee > 0) { $charges[] = ['Shipping Fee', $money($shippingFee)]; }
        if ($additional > 0) { $charges[] = ['Additional Charges', $money($additional)]; }
        if ($insurance > 0) { $charges[] = ['Insurance Fee', $money($insurance)]; }
        if ($cod > 0) { $charges[] = ['COD Amount', $money($cod)]; }
        if ($tax > 0) { $charges[] = ['Tax', $money($tax)]; }
        if ($discount > 0) { $charges[] = ['Discount', '-' . $money($discount)]; }
        if (empty($charges) && !empty($shipment['total_amount'])) {
            $charges[] = ['Total', $money($shipment['total_amount'])];
            $total = (float)$shipment['total_amount'];
        }
        $this->pdfChargesTable($pdf, $charges, $money($total));

        /* ---- Transaction & References ---- */
        if (!empty($shipment['transaction_id']) || !empty($shipment['invoice_number']) || !empty($shipment['reference_number'])) {
            $this->pdfSection($pdf, 'Transaction & References');
            $this->pdfRow2($pdf, ['Transaction ID', $v('transaction_id')], ['Invoice Number', $v('invoice_number')]);
            $this->pdfRow1($pdf, 'Reference Number', $v('reference_number'));
        }

        /* ---- Payment information ---- */
        $status = strtolower($v('payment_status', 'pending'));
        $paid = ($status === 'paid') ? $total : (($status === 'refunded') ? 0 : 0);
        $outstanding = $total - $paid;
        $this->pdfSection($pdf, 'Payment Information');
        $this->pdfRow2($pdf, ['Payment Method', ucfirst(str_replace('_', ' ', $v('payment_method')))], ['Payment Status', ucfirst($status)]);
        $this->pdfRow2($pdf, ['Transaction Reference', $v('transaction_id')], ['Amount Paid', $money($paid)]);
        if ($outstanding > 0) {
            $this->pdfRow1($pdf, 'Outstanding Balance', $money($outstanding));
        }

        /* ---- Customer Notes ---- */
        $customerNotes = $shipment['customer_notes'] ?? '';
        if (!empty($customerNotes)) {
            $this->pdfSection($pdf, 'Customer Notes');
            $this->pdfRow1($pdf, 'Notes', $customerNotes);
        }

        /* ---- Internal notes (admin only) ---- */
        $internal = $shipment['notes'] ?? $shipment['internal_notes'] ?? '';
        if (!empty($internal)) {
            $this->pdfSection($pdf, 'Internal Notes');
            $this->pdfRow1($pdf, 'Notes', $internal);
        }

        /* ---- Tracking section (QR as primary scannable + barcode visual) ---- */
        $this->pdfSection($pdf, 'Tracking');
        $pdf->setFont('helvetica', 'B', 9);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->writeParagraph('Tracking Number: ' . $tracking, null, 1);

        // Visual barcode (decorative; QR is the scannable element).
        $this->drawBarcode($pdf, $pdf->marginL, $pdf->cy, 110, 18, $tracking);
        $pdf->setFont('helvetica', '', 8);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $pdf->writeParagraph($tracking, 110, 2);

        // QR code — real, scannable, larger.
        $qr = $shipment['qr_code_path'] ?? '';
        $qrFile = '';
        if ($qr) {
            if (preg_match('/^([A-Z]:\\\\)/i', $qr) || str_starts_with($qr, '/')) {
                $qrFile = $qr;
            } else {
                $qrFile = $this->uploadDir . '../' . ltrim($qr, '/\\');
            }
        }
        $qrSize = 45;
        $qrX = $pdf->marginL + ($pdf->contentW - $qrSize) / 2;
        $qrY = $pdf->cy;
        $embedded = false;
        if ($qrFile && is_file($qrFile) && preg_match('/\.(png|jpe?g)$/i', $qrFile)) {
            $key = $pdf->image($qrFile, $qrX, $qrY, $qrSize, $qrSize);
            $embedded = ($key !== null);
        }
        if (!$embedded) {
            // Generate QR on-the-fly if the cached image is missing.
            try {
                if (!class_exists('QRCode')) {
                    require_once __DIR__ . '/../../lib/QRCode.php';
                }
                $trackingUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/track.php?cons_no=' . urlencode($tracking);
                $matrix = QRCode::encode($trackingUrl, 'M');
                $qrTmp = $this->uploadDir . 'barcodes/' . $tracking . '_qr_tmp.png';
                $this->renderQrMatrixPng($matrix, $qrTmp, 6, 4);
                if (is_file($qrTmp)) {
                    $pdf->image($qrTmp, $qrX, $qrY, $qrSize, $qrSize);
                    $embedded = true;
                }
            } catch (Throwable $e) {
                // fall through to faux QR
            }
        }
        if (!$embedded) {
            $this->drawFauxQR($pdf, $qrX, $qrY, $qrSize, $tracking);
        }
        $pdf->setFont('helvetica', '', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($qrX, $qrY + $qrSize + 3, 'Scan to track: ' . $tracking);
        $pdf->cy = $qrY + $qrSize + 8;

        /* ---- Terms and conditions ---- */
        $this->pdfSection($pdf, 'Terms & Conditions');
        $terms = [
            'Courier Liability: The courier\'s liability for loss or damage is limited to the declared value unless additional insurance is purchased. Claims must be submitted within 7 days of delivery.',
            'Insurance Policy: Insurance covers loss or damage up to the insured value during transit. Exclusions apply for prohibited, fragile, or improperly packaged items.',
            'Delivery Conditions: Delivery times are estimates. Risk passes to the recipient upon signature. Incorrect addresses may incur redelivery charges.',
            'Customer Acknowledgment: By accepting this shipment the customer agrees to the carrier\'s terms, accepts the declared value and confirms the goods were handed over in good condition.',
        ];
        foreach ($terms as $t) {
            $pdf->setFont('helvetica', 'B', 8.5);
            $pdf->setTextColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
            $head = '';
            if (preg_match('/^([^:]+):\s*(.*)$/', $t, $m)) { $head = $m[1] . ': '; $body = $m[2]; }
            else { $body = $t; }
            // Draw heading inline with wrapped body.
            $pdf->setFont('helvetica', 'B', 8.5);
            $pdf->writeParagraph($head . $body);
        }

        /* ---- Signature section ---- */
        $this->pdfSection($pdf, 'Acknowledgement & Signature');
        $sigColW = ($pdf->contentW - 12) / 2;
        $sigPad = 4;
        $sigBoxH = 52;
        $pdf->ensureSpace($sigBoxH + 12);
        $sigStartY = $pdf->cy;
        $sigBoxY = $sigStartY + 2;

        $pdf->setFillColor(248, 250, 252);
        $pdf->setDrawColor($this->cBorder[0], $this->cBorder[1], $this->cBorder[2]);
        $pdf->rect($pdf->marginL, $sigBoxY, $sigColW, $sigBoxH, 'FD');
        $pdf->rect($pdf->marginL + $sigColW + 12, $sigBoxY, $sigColW, $sigBoxH, 'FD');

        $pdf->setFont('helvetica', 'B', 8.5);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($pdf->marginL + $sigColW / 2 - 18, $sigBoxY + $sigPad + 3.5, 'Customer Signature');
        $pdf->text($pdf->marginL + $sigColW + 12 + $sigColW / 2 - 18, $sigBoxY + $sigPad + 3.5, 'Staff Signature');

        $sigImgY = $sigBoxY + $sigPad + 7;
        $sigImgH = 18;
        $sigImgW = $sigColW - $sigPad * 2;
        $sigLineY = $sigImgY + $sigImgH + 3;
        $pdf->setDrawColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $pdf->line($pdf->marginL + $sigPad, $sigLineY, $pdf->marginL + $sigColW - $sigPad, $sigLineY);
        $pdf->line($pdf->marginL + $sigColW + 12 + $sigPad, $sigLineY, $pdf->marginL + $sigColW + 12 + $sigColW - $sigPad, $sigLineY);

        $pdf->setFont('helvetica', '', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($pdf->marginL + $sigPad, $sigLineY + 4, 'Date: ____________________');
        $pdf->text($pdf->marginL + $sigColW + 12 + $sigPad, $sigLineY + 4, 'Date: ____________________');

        $sigLeft = $pdf->marginL + $sigPad;
        $sigRight = $pdf->marginL + $sigColW + 12 + $sigPad;
        $signatureFile = '';
        if (!empty($shipment['signature_image'])) {
            $path = ltrim($shipment['signature_image'], '/\\');
            if (preg_match('/^([A-Z]:\\\\)/i', $path) || str_starts_with($path, '/')) {
                $signatureFile = $path;
            } else {
                $signatureFile = $this->uploadDir . '../' . $path;
            }
        } elseif (!empty($shipment['id'])) {
            try {
                $stmt = $this->db->prepare("SELECT signature_path FROM delivery_confirmations WHERE shipment_id = :id ORDER BY created_at DESC, id DESC LIMIT 1");
                $stmt->execute([':id' => $shipment['id']]);
                $sigPath = $stmt->fetchColumn();
                if ($sigPath) {
                    $signatureFile = $this->uploadDir . '../' . ltrim($sigPath, '/\\');
                }
            } catch (Exception $e) { /* best effort */ }
        }
        if ($signatureFile && is_file($signatureFile)) {
            $key = $pdf->image($signatureFile, $sigLeft, $sigImgY, $sigImgW, $sigImgH);
            if ($key === null) {
                $this->drawFauxQR($pdf, $sigLeft, $sigImgY, $sigImgH, 'Signature on file');
            }
        }

        $pdf->cy = $sigBoxY + $sigBoxH + 6;

        /* ---- Official stamps ---- */
        $stampSize = 22;
        $pdf->ensureSpace($stampSize + 18);
        $stampY = $pdf->cy;
        $stamp1 = __DIR__ . '/../../assets/pdf/stamps/stamp1.png';
        $stamp2 = __DIR__ . '/../../assets/pdf/stamps/stamp2.png';
        $stampLeft = $pdf->marginL;
        $stampRight = $pdf->marginL + $pdf->contentW - $stampSize;

        $pdf->setFont('helvetica', 'B', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($stampLeft + $stampSize / 2 - 18, $stampY + 2, 'OFFICIAL STAMP');
        $pdf->text($stampRight + $stampSize / 2 - 18, $stampY + 2, 'STAMP DUTY');

        if (is_file($stamp1)) {
            $pdf->image($stamp1, $stampLeft, $stampY + 5, $stampSize, $stampSize);
        }
        if (is_file($stamp2)) {
            $pdf->image($stamp2, $stampRight, $stampY + 5, $stampSize, $stampSize);
        }

        $pdf->setFont('helvetica', '', 8);
        $pdf->text($stampLeft, $stampY + $stampSize + 7, 'Date: ' . date('M d, Y'));
        $pdf->text($stampRight, $stampY + $stampSize + 7, 'Verified & Approved');
        $pdf->cy = $stampY + $stampSize + 12;

        return $pdf;
    }

    /**
     * Load company branding from the database (with sensible defaults).
     */
    private function pdfCompanyInfo($shipment) {
        $company = [
            'name'    => defined('SITE_NAME') ? SITE_NAME : 'Courier Logistics',
            'email'   => 'info@ascl-logistics.com',
            'phone'   => '+12158159791',
            'address' => '4500 Harbor Boulevard, Long Beach, CA 90802, USA',
            'website' => 'https://www.ascl-logistics.com',
            'logo'    => '',
        ];
        try {
            $stmt = $this->db->query("SELECT name, email, phone, address, website, logo FROM company LIMIT 1");
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach (['name', 'email', 'phone', 'address', 'website', 'logo'] as $k) {
                    if (!empty($row[$k])) { $company[$k] = $row[$k]; }
                }
            }
        } catch (Exception $e) { /* use defaults */ }
        return $company;
    }

    /**
     * Resolve the creator name from the user/admin tables (best effort).
     */
    private function pdfCreatorName($shipment) {
        $id = $shipment['created_by'] ?? 0;
        if (!$id) { return 'System'; }
        $maps = [
            'users'      => 'name',
            'admins'     => 'username',
            'admin_users' => 'name',
        ];
        foreach ($maps as $table => $col) {
            try {
                $stmt = $this->db->prepare("SELECT `$col` FROM `$table` WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                if ($name = $stmt->fetchColumn()) { return $name; }
            } catch (Exception $e) { /* try next */ }
        }
        return 'Admin #' . $id;
    }

    /** Draw the repeating company header band. */
    private function pdfHeader($pdf, $company) {
        $pdf->setFillColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
        $pdf->rect(0, 0, $pdf->pageW, $pdf->headerH, 'F');
        $pdf->setFillColor($this->cAccent[0], $this->cAccent[1], $this->cAccent[2]);
        $pdf->rect(0, $pdf->headerH - 2, $pdf->pageW, 2, 'F');

        $logoX = 8;
        $logoY = 6;
        $logoW = 14;
        $logoH = 14;
        $logoDrawn = false;

        if (!empty($company['logo'])) {
            $candidates = [
                $this->uploadDir . '../' . ltrim($company['logo'], '/\\'),
                __DIR__ . '/../../uploads/' . ltrim($company['logo'], '/\\'),
                __DIR__ . '/../../' . ltrim($company['logo'], '/\\'),
            ];
        } else {
            $candidates = [
                __DIR__ . '/../../img/logo.png',
                __DIR__ . '/../../icon/logo.png',
            ];
        }
        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                $key = $pdf->image($candidate, $logoX, $logoY, $logoW, $logoH);
                if ($key !== null) { $logoDrawn = true; break; }
            }
        }

        if (!$logoDrawn) {
            $pdf->setFillColor(255, 255, 255);
            $pdf->rect($logoX, $logoY, $logoW, $logoH, 'F');
            $pdf->setFont('helvetica', 'B', 11);
            $pdf->setTextColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
            $initials = strtoupper(substr(preg_replace('/[^a-z]/i', '', $company['name']), 0, 2));
            $pdf->text($logoX + 3, $logoY + 9, $initials);
        }

        $textX = $logoX + $logoW + 5;
        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setTextColor(255, 255, 255);
        $pdf->text($textX, 12, $company['name']);
        $pdf->setFont('helvetica', '', 8.5);
        $pdf->setTextColor(214, 226, 240);
        $contact = [];
        if (!empty($company['address'])) { $contact[] = $company['address']; }
        if (!empty($company['phone'])) { $contact[] = 'Tel: ' . $company['phone']; }
        if (!empty($company['email'])) { $contact[] = $company['email']; }
        $pdf->text($textX, 18, implode('   |   ', $contact));
        if (!empty($company['website'])) {
            $pdf->text($textX, 23, $company['website']);
        }
    }

    /** Draw the repeating footer band with page numbers. */
    private function pdfFooter($pdf, $pageNum, $total, $company) {
        $y = $pdf->pageH - $pdf->marginB - $pdf->footerH + 4;
        $pdf->setDrawColor($this->cBorder[0], $this->cBorder[1], $this->cBorder[2]);
        $pdf->line($pdf->marginL, $y, $pdf->pageW - $pdf->marginR, $y);
        $pdf->setFont('helvetica', '', 8);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($pdf->marginL, $y + 4, 'Thank you for choosing ' . $company['name']);
        $support = 'Support: ' . $company['phone'];
        if (!empty($company['website'])) { $support .= '  |  ' . $company['website']; }
        if (!empty($company['email'])) { $support .= '  |  ' . $company['email']; }
        $pdf->text($pdf->marginL, $y + 8.5, $support);
        $pg = 'Page ' . $pageNum . ' of ' . $total;
        $pdf->text($pdf->pageW - $pdf->marginR - $pdf->textWidth($pg), $y + 4, $pg);
    }

    /** Section title bar. */
    private function pdfSection($pdf, $title) {
        $pdf->ensureSpace(10);
        $y = $pdf->cy;
        $h = 7;
        $pdf->setFillColor($this->cLight[0], $this->cLight[1], $this->cLight[2]);
        $pdf->setDrawColor($this->cBorder[0], $this->cBorder[1], $this->cBorder[2]);
        $pdf->rect($pdf->marginL, $y, $pdf->contentW, $h, 'FD');
        $pdf->setFont('helvetica', 'B', 11);
        $pdf->setTextColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
        $pdf->text($pdf->marginL + 4, $y + $h - 2.6, strtoupper($title));
        $pdf->cy = $y + $h + 3.5;
    }

    /** Draw a single label/value field at (x, current y) and return its height. */
    private function pdfField($pdf, $x, $w, $label, $value) {
        $labelSize = 8.5;
        $valSize = 10.5;
        $y = $pdf->cy;
        $pdf->setFont('helvetica', 'B', $labelSize);
        $pdf->setTextColor($this->cMuted[0], $this->cMuted[1], $this->cMuted[2]);
        $pdf->text($x, $y + 3.4, strtoupper($label));
        $pdf->setFont('helvetica', '', $valSize);
        $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
        $vlines = $pdf->wrapText((string) $value, $w);
        $vy = $y + 7.8;
        $vlh = $valSize / ReceiptPDF::MM_TO_PT * 1.3;
        $baseline = $vy + $vlh - ($valSize / ReceiptPDF::MM_TO_PT * 0.25);
        foreach ($vlines as $ln) {
            $pdf->text($x, $baseline, $ln);
            $baseline += $vlh;
        }
        $height = 7.8 + $vlh * count($vlines) + 1.8;
        return $height;
    }

    /** Two-column field row. */
    private function pdfRow2($pdf, $a, $b) {
        $gap = 8;
        $cw = ($pdf->contentW - $gap) / 2;
        $pdf->ensureSpace(8);
        $hA = $this->pdfField($pdf, $pdf->marginL, $cw, $a[0], $a[1]);
        $hB = $this->pdfField($pdf, $pdf->marginL + $cw + $gap, $cw, $b[0], $b[1]);
        $pdf->cy += max($hA, $hB);
    }

    /** Full-width field row. */
    private function pdfRow1($pdf, $label, $value) {
        $pdf->ensureSpace(8);
        $h = $this->pdfField($pdf, $pdf->marginL, $pdf->contentW, $label, $value);
        $pdf->cy += $h;
    }

    /** Charges table with right-aligned amounts and a total row. */
    private function pdfChargesTable($pdf, $items, $totalText) {
        $pdf->ensureSpace(10 + count($items) * 6 + 8);
        $y = $pdf->cy;
        $rowH = 6.5;
        // header
        $pdf->setFillColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
        $pdf->rect($pdf->marginL, $y, $pdf->contentW, $rowH, 'F');
        $pdf->setFont('helvetica', 'B', 9);
        $pdf->setTextColor(255, 255, 255);
        $pdf->text($pdf->marginL + 4, $y + 4.6, 'Description');
        $pdf->text($pdf->pageW - $pdf->marginR - 4 - $pdf->textWidth('Amount'), $y + 4.6, 'Amount');
        $y += $rowH;

        $alt = false;
        foreach ($items as $it) {
            if ($alt) {
                $pdf->setFillColor(248, 250, 252);
                $pdf->rect($pdf->marginL, $y, $pdf->contentW, $rowH, 'F');
            }
            $alt = !$alt;
            $pdf->setFont('helvetica', '', 9.5);
            $pdf->setTextColor($this->cText[0], $this->cText[1], $this->cText[2]);
            $pdf->text($pdf->marginL + 4, $y + 4.6, $it[0]);
            $pdf->text($pdf->pageW - $pdf->marginR - 4 - $pdf->textWidth($it[1]), $y + 4.6, $it[1]);
            $y += $rowH;
        }
        // total row
        $pdf->setFillColor($this->cNavy[0], $this->cNavy[1], $this->cNavy[2]);
        $pdf->rect($pdf->marginL, $y, $pdf->contentW, $rowH + 1, 'F');
        $pdf->setFont('helvetica', 'B', 10.5);
        $pdf->setTextColor(255, 255, 255);
        $pdf->text($pdf->marginL + 4, $y + 5.2, 'TOTAL AMOUNT');
        $pdf->text($pdf->pageW - $pdf->marginR - 4 - $pdf->textWidth($totalText), $y + 5.2, $totalText);
        $pdf->cy = $y + $rowH + 4;
    }

    /** Draw a representative barcode (consistent with the project's existing approach). */
    private function drawBarcode($pdf, $x, $y, $w, $h, $code) {
        $pdf->setFillColor(0, 0, 0);
        $narrow = 0.6;
        $wide = 1.8;
        $cx = $x;
        $chars = str_split($code);
        foreach ($chars as $ch) {
            $byte = ord($ch);
            for ($b = 7; $b >= 0; $b--) {
                $on = ($byte >> $b) & 1;
                $bw = $on ? $wide : $narrow;
                if ($on) { $pdf->rect($cx, $y, $bw, $h, 'F'); }
                $cx += $bw + 0.4;
                if ($cx > $x + $w) { break 2; }
            }
            $cx += 1.0;
        }
    }

    /** Draw a placeholder QR-like matrix when a real QR raster is unavailable. */
    private function drawFauxQR($pdf, $x, $y, $size, $seed) {
        $pdf->setFillColor(255, 255, 255);
        $pdf->rect($x - 1, $y - 1, $size + 2, $size + 2, 'F');
        $pdf->setDrawColor(0, 0, 0);
        $pdf->rect($x, $y, $size, $size, 'D');
        $cells = 21;
        $cs = $size / $cells;
        mt_srand(crc32($seed));
        for ($i = 0; $i < $cells; $i++) {
            for ($j = 0; $j < $cells; $j++) {
                if ($this->qrFinder($i, $j, $cells)) { continue; }
                if (mt_rand(0, 1)) {
                    $pdf->setFillColor(0, 0, 0);
                    $pdf->rect($x + $j * $cs, $y + $i * $cs, $cs, $cs, 'F');
                }
            }
        }
        $this->qrFinderDraw($pdf, $x, $y, $cs);
    }

    /** True for cells belonging to one of the three finder patterns. */
    private function qrFinder($i, $j, $cells) {
        $in = function($a, $b) { return $a >= 0 && $a < 7 && $b >= 0 && $b < 7; };
        return $in($i, $j) || $in($i, $j - ($cells - 7)) || $in($i - ($cells - 7), $j);
    }

    /** Draw the three 7x7 finder squares. */
    private function qrFinderDraw($pdf, $x, $y, $cs) {
        $draw = function($ox, $oy) use ($pdf, $x, $y, $cs) {
            $fx = $x + $ox * $cs;
            $fy = $y + $oy * $cs;
            $pdf->setFillColor(0, 0, 0);
            $pdf->rect($fx, $fy, $cs * 7, $cs * 7, 'F');
            $pdf->setFillColor(255, 255, 255);
            $pdf->rect($fx + $cs, $fy + $cs, $cs * 5, $cs * 5, 'F');
            $pdf->setFillColor(0, 0, 0);
            $pdf->rect($fx + $cs * 2, $fy + $cs * 2, $cs * 3, $cs * 3, 'F');
        };
        $cells = 21;
        $draw(0, 0);
        $draw(0, $cells - 7);
        $draw($cells - 7, 0);
    }

    /**
     * Generate shipping label HTML
     */
    public function generateShippingLabel($shipment, $savePath = null) {
        $savePath = $savePath ?? $this->uploadDir . 'labels/' . $shipment['tracking_number'] . '.html';
        
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Shipping Label - ' . htmlspecialchars($shipment['tracking_number']) . '</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; padding: 2rem; }
                .label { max-width: 400px; margin: 0 auto; border: 3px solid #000; padding: 1.5rem; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 1rem; margin-bottom: 1rem; }
                .tracking { font-size: 1.5rem; font-weight: bold; letter-spacing: 2px; }
                .section { margin-bottom: 1rem; }
                .section-title { font-weight: bold; font-size: 0.9rem; text-transform: uppercase; border-bottom: 1px solid #ccc; margin-bottom: 0.5rem; }
                .address { font-size: 0.95rem; line-height: 1.4; }
                .barcode { text-align: center; font-family: monospace; font-size: 1.2rem; letter-spacing: 3px; margin: 1rem 0; padding: 0.5rem; border-top: 2px solid #000; border-bottom: 2px solid #000; }
                .qr { text-align: center; margin: 0.5rem 0; }
                .footer { font-size: 0.8rem; text-align: center; margin-top: 1rem; color: #666; }
                @media print { body { padding: 0; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="label">
                <div class="header">
                    <div style="font-size: 1.2rem; font-weight: bold;">' . htmlspecialchars(SITE_NAME) . '</div>
                    <div style="font-size: 0.8rem;">Global Courier & Shipping</div>
                </div>
                
                <div class="section">
                    <div class="section-title">Tracking Number</div>
                    <div class="tracking">' . htmlspecialchars($shipment['tracking_number']) . '</div>
                    <div class="barcode">|| | | || | | | || || | || || | || || || | | | || || || | || |</div>
                </div>
                
                <div class="section">
                    <div class="section-title">From</div>
                    <div class="address">
                        <strong>' . htmlspecialchars($shipment['sender_name'] ?? 'N/A') . '</strong><br>
                        ' . htmlspecialchars($shipment['sender_address'] ?? 'N/A') . '<br>
                        ' . htmlspecialchars($shipment['sender_city'] ?? '') . ', ' . htmlspecialchars($shipment['sender_state'] ?? '') . ' ' . htmlspecialchars($shipment['sender_postal'] ?? '') . '<br>
                        Tel: ' . htmlspecialchars($shipment['sender_phone'] ?? 'N/A') . '
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">To</div>
                    <div class="address">
                        <strong>' . htmlspecialchars($shipment['receiver_name'] ?? 'N/A') . '</strong><br>
                        ' . htmlspecialchars($shipment['receiver_address'] ?? 'N/A') . '<br>
                        ' . htmlspecialchars($shipment['receiver_city'] ?? '') . ', ' . htmlspecialchars($shipment['receiver_state'] ?? '') . ' ' . htmlspecialchars($shipment['receiver_postal'] ?? '') . '<br>
                        Tel: ' . htmlspecialchars($shipment['receiver_phone'] ?? 'N/A') . '
                    </div>
                </div>
                
                <div class="section">
                    <div class="row">
                        <div class="col-6">
                            <div class="section-title">Weight</div>
                            <div>' . htmlspecialchars($shipment['total_weight'] ?? 'N/A') . ' kg</div>
                        </div>
                        <div class="col-6">
                            <div class="section-title">Service</div>
                            <div>' . ucfirst($shipment['service_type'] ?? 'Standard') . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="barcode">
                    ' . htmlspecialchars($shipment['tracking_number']) . '
                </div>
                
                <div class="qr">
                    <small>Scan to track shipment</small><br>
                    <small style="font-family: monospace;">' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/track.php?tracking=' . urlencode($shipment['tracking_number']) . '</small>
                </div>
                
                <div class="footer">
                    ' . htmlspecialchars(SITE_NAME) . ' | ' . date('Y-m-d') . ' | Page 1 of 1
                </div>
            </div>
        </body>
        </html>';
        
        file_put_contents($savePath, $html);
        return $savePath;
    }

    /** Email the receipt PDF to the customer. */
    public function emailReceipt($shipment, $to, $subject = null, $body = null) {
        $to = trim((string)$to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

        $pdfPath = $shipment['pdf_receipt_path'] ?? '';
        if (!$pdfPath || !is_file($pdfPath)) {
            $pdfPath = $this->buildReceiptPDF($shipment);
        }
        if (!$pdfPath || !is_file($pdfPath)) return false;

        $company = $this->pdfCompanyInfo($shipment);
        $subject = $subject ?? 'Your Receipt - ' . ($company['name'] ?? 'Courier Logistics');
        $tracking = $shipment['tracking_number'] ?? '';
        $name = trim(($shipment['receiver_name'] ?? '') ?: ($shipment['sender_name'] ?? 'Customer'));
        $body = $body ?? "Dear {$name},\n\nPlease find attached your shipping receipt";
        if ($tracking) { $body .= " for tracking number {$tracking}"; }
        $body .= ".\n\nTrack your shipment: https://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/track.php?tracking=" . urlencode($tracking) . "\n\nThank you,\n" . ($company['name'] ?? 'Courier Logistics');
        $attachmentName = 'Receipt_' . ($tracking ?: 'shipment') . '.pdf';

        return sendMail($to, $subject, $body, [
            'attachment_path' => $pdfPath,
            'attachment_name' => $attachmentName,
        ]);
    }
}

