<?php
/**
 * Documents Manager — upload / replace / preview / download / delete shipment
 * documents across all required categories. Delete is restricted to admins.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('manage_documents');

$page_title = 'Documents Manager - ' . SITE_NAME;
$db = getDB();
ensureAttachmentDocType($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id, tracking_number FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { echo '<div class="alert alert-danger m-4">Shipment not found.</div>'; require_once __DIR__.'/includes/footer.php'; exit; }
$tn = $shipment['tracking_number'];

$canDelete = can('delete_documents');
$msg = ''; $msgType = '';

$uploadDir = __DIR__ . '/../uploads/attachments/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
$allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','csv','zip','txt'];

// Upload / replace
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doc_type'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msg = 'Invalid security token.'; $msgType = 'danger';
    } else {
        $dt = $_POST['doc_type'];
        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['file'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) { $msg = 'Unsupported file type.'; $msgType='danger'; }
            else {
                $safe = $tn . '_' . $dt . '_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $uploadDir . $safe)) {
                    $rel = 'uploads/attachments/' . $safe;
                    $db->prepare("INSERT INTO attachments (entity_type, entity_id, filename, original_name, file_path, mime_type, file_size, doc_type, uploaded_by, access_level, created_at)
                        VALUES ('shipment', :eid, :fn, :on, :fp, :mt, :fs, :dt, :ub, 'internal', NOW())")
                        ->execute([':eid'=>$id,':fn'=>$safe,':on'=>$f['name'],':fp'=>$rel,':mt'=>$f['type'],':fs'=>$f['size'],':dt'=>$dt,':ub'=>$_SESSION['admin_id']??null]);
                    logShipmentAction($db, 'documents_uploaded', $id, "Uploaded document: $dt");
                    $msg = 'Document uploaded.'; $msgType='success';
                } else { $msg='Upload failed.'; $msgType='danger'; }
            }
        } else { $msg='No file selected.'; $msgType='danger'; }
    }
}

// Delete
if (isset($_GET['delete']) && $canDelete && isset($_GET['csrf'])) {
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['csrf'])) {
        $aid = intval($_GET['delete']);
        $stmt = $db->prepare("SELECT file_path FROM attachments WHERE id=:id AND entity_type='shipment' AND entity_id=:eid LIMIT 1");
        $stmt->execute([':id'=>$aid, ':eid'=>$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) { @unlink(__DIR__ . '/../' . $row['file_path']); $db->prepare("DELETE FROM attachments WHERE id=:id")->execute([':id'=>$aid]); $msg='Document deleted.'; $msgType='success'; }
    }
}

$types = [
    'shipping_label' => 'Shipping Label',
    'barcode' => 'Barcode',
    'qr_code' => 'QR Code',
    'commercial_invoice' => 'Commercial Invoice',
    'packing_list' => 'Packing List',
    'customs_documents' => 'Customs Documents',
    'delivery_receipt' => 'Delivery Receipt',
    'proof_of_delivery' => 'Proof of Delivery',
];
$stmt = $db->prepare("SELECT * FROM attachments WHERE entity_type='shipment' AND entity_id=:id ORDER BY created_at DESC");
$stmt->execute([':id' => $id]);
$att = $stmt->fetchAll(PDO::FETCH_ASSOC);
$byType = [];
foreach ($att as $a) { $byType[$a['doc_type'] ?? 'other'][] = $a; }
$isImg = fn($m) => in_array(strtolower(pathinfo($m ?? '', PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif']);
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-paperclip me-2"></i>Documents — <?php echo htmlspecialchars($tn); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="row g-4">
        <?php foreach ($types as $dk => $label): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100"><div class="card-header d-flex justify-content-between"><span><?php echo $label; ?></span>
                <span class="badge bg-secondary"><?php echo count($byType[$dk] ?? []); ?></span></div>
                <div class="card-body">
                    <?php if (!empty($byType[$dk])): foreach ($byType[$dk] as $a): ?>
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                            <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                <?php if ($isImg($a['file_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($a['file_path']); ?>" style="width:42px;height:42px;object-fit:cover;border-radius:6px;">
                                <?php else: ?><i class="bi bi-file-earmark fs-3"></i><?php endif; ?>
                                <div style="min-width:0;"><div class="text-truncate" style="max-width:180px;"><?php echo htmlspecialchars($a['original_name']); ?></div>
                                <small class="text-muted"><?php echo number_format(($a['file_size']??0)/1024,1); ?> KB</small></div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="download.php?id=<?php echo $a['id']; ?>" class="btn btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
                                <?php if ($canDelete): ?><a href="documents_manager.php?id=<?php echo $id; ?>&delete=<?php echo $a['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this document?');" title="Delete"><i class="bi bi-trash"></i></a><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted small">No documents.</p><?php endif; ?>
                    <?php if ($dk === 'barcode' || $dk === 'qr_code'): ?>
                        <a href="print_label.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 mb-2"><i class="bi bi-upc-scan"></i> Generate via Label</a>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="mt-2">
                        <?php echo csrfInput(); ?><input type="hidden" name="doc_type" value="<?php echo $dk; ?>">
                        <div class="input-group input-group-sm">
                            <input type="file" name="file" class="form-control" accept=".<?php echo implode(',.',$allowed); ?>" required>
                            <button class="btn btn-primary" type="submit" onclick="return confirm('Upload / replace this document?');"><i class="bi bi-upload"></i></button>
                        </div>
                    </form>
                </div></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
