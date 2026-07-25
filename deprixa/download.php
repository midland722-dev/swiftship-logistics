<?php
/**
 * admin/download.php
 *
 * Secure file download proxy for shipment attachments.
 * Usage: /shp/admin/download.php?id=<attachment_id>
 *
 * Streams the file with Content-Disposition: attachment so the browser
 * prompts for download instead of displaying inline.
 */

session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo 'Authentication required.';
    exit;
}

require_once __DIR__ . '/includes/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid attachment ID.';
    exit;
}

$stmt = $db->prepare("
    SELECT a.*, s.tracking_number
    FROM attachments a
    LEFT JOIN shipments s ON s.id = a.entity_id
    WHERE a.id = :id AND a.entity_type = 'shipment'
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$baseDir = realpath(__DIR__ . '/../uploads/attachments/');
$filePath = realpath(__DIR__ . '/../' . $row['file_path']);
if ($filePath === false || strpos($filePath, $baseDir) !== 0 || !is_file($filePath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$filename = $row['original_name'] ?: $row['filename'];
$mimeType = $row['mime_type'] ?: 'application/octet-stream';

// Force download.
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . str_replace(['"', '\\'], '', $filename) . '"');
header('Content-Length: ' . (string)filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
flush();
readfile($filePath);
exit;
