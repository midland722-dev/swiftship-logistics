<?php
/** Upload & manage shipment attachments (parcel photos, label, invoice, POD, customs). */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

$db = getDB();
$shipment_id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id, tracking_number FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $shipment_id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { die('Shipment not found.'); }

$uploadDir = __DIR__ . '/../uploads/attachments/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

$categories = [
    'parcel_photo' => 'Parcel Photo',
    'shipping_label' => 'Shipping Label',
    'invoice' => 'Invoice',
    'pod' => 'Proof of Delivery',
    'customs' => 'Customs Document',
    'other' => 'Other',
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $message = 'Invalid security token.';
    } elseif ($_POST['action'] === 'upload' && !empty($_FILES['file'])) {
        $cat = $_POST['category'] ?? 'other';
        if (!isset($categories[$cat])) { $cat = 'other'; }
        $f = $_FILES['file'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx'];
            if (!in_array($ext, $allowed, true)) {
                $message = 'Unsupported file type.';
            } else {
                $safeName = $shipment['tracking_number'] . '_' . $cat . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $safeName;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $rel = 'uploads/attachments/' . $safeName;
                    $db->prepare("
                        INSERT INTO attachments (entity_type, entity_id, filename, original_name, file_path, mime_type, file_size, uploaded_by, access_level, created_at)
                        VALUES ('shipment', :eid, :fn, :on, :fp, :mt, :fs, :ub, 'internal', NOW())
                    ")->execute([
                        ':eid' => $shipment_id, ':fn' => $safeName, ':on' => $f['name'],
                        ':fp' => $rel, ':mt' => $f['type'], ':fs' => $f['size'],
                        ':ub' => $_SESSION['admin_id'] ?? null,
                    ]);
                    $message = 'File uploaded.';
                } else {
                    $message = 'Upload failed.';
                }
            }
        } else {
            $message = 'Upload error.';
        }
    } elseif ($_POST['action'] === 'delete' && !empty($_POST['attachment_id'])) {
        $aid = intval($_POST['attachment_id']);
$stmt = $db->prepare("SELECT file_path FROM attachments WHERE id = :id AND entity_type = 'shipment' AND entity_id = :eid LIMIT 1");
$stmt->execute([':id' => $aid, ':eid' => $shipment_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            @unlink(__DIR__ . '/../' . $row['file_path']);
            $db->prepare("DELETE FROM attachments WHERE id = :id")->execute([':id' => $aid]);
            $message = 'Attachment deleted.';
        }
    }
}

$stmt = $db->prepare("
    SELECT * FROM attachments WHERE entity_type = 'shipment' AND entity_id = :id ORDER BY created_at DESC
");
$stmt->execute([':id' => $shipment_id]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid py-4">
    <a href="shipment_details.php?id=<?php echo $shipment_id; ?>" class="btn btn-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back to Shipment</a>
    <h4><i class="bi bi-paperclip"></i> Documents — <?php echo htmlspecialchars($shipment['tracking_number']); ?></h4>

    <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Upload Document</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <?php foreach ($categories as $k => $v): ?>
                                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" required>
                        </div>
                        <button class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Attachments (<?php echo count($attachments); ?>)</div>
                <div class="card-body">
                    <?php if (empty($attachments)): ?>
                        <p class="text-muted">No documents uploaded yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($attachments as $a): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark me-2"></i>
                                        <a href="download.php?id=<?php echo $a['id']; ?>" title="Download <?php echo htmlspecialchars($a['original_name']); ?>"><?php echo htmlspecialchars($a['original_name']); ?></a>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($a['mime_type']); ?> · <?php echo number_format($a['file_size']/1024, 1); ?> KB</small>
                                    </div>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="attachment_id" value="<?php echo $a['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
