<?php
/**
 * Delivery Confirmation — capture receiver name, signature, multiple POD
 * photos, GPS, date/time, courier notes, customer feedback.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('delivery_confirm');

$page_title = 'Delivery Confirmation - ' . SITE_NAME;
$db = getDB();
ensureModuleTables($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { echo '<div class="alert alert-danger m-4">Shipment not found.</div>'; require_once __DIR__.'/includes/footer.php'; exit; }
$GLOBALS['shipment'] = $shipment;

$msg = ''; $msgType = '';
$uploadDir = __DIR__ . '/../uploads/attachments/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
$allowed = ['jpg','jpeg','png','gif','pdf'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$maxSigSize = 5 * 1024 * 1024;
$maxPhotoSize = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msg = 'Invalid security token.'; $msgType='danger';
    } else {
        $receiver = trim($_POST['receiver_name'] ?? '');
        $gpsLat = trim($_POST['gps_lat'] ?? '') === '' ? null : max(-90, min(90, (float)$_POST['gps_lat']));
        $gpsLng = trim($_POST['gps_lng'] ?? '') === '' ? null : max(-180, min(180, (float)$_POST['gps_lng']));
        $ddate = trim($_POST['delivery_date'] ?? null) ?: null;
        $dtime = trim($_POST['delivery_time'] ?? null) ?: null;
        $notes = trim($_POST['courier_notes'] ?? '');
        $feedback = trim($_POST['customer_feedback'] ?? '');

        // Signature upload.
        $sigPath = null;
        if (!empty($_FILES['signature']['name']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['signature']['size'] > $maxSigSize) {
                $msg = 'Signature file exceeds 5 MB limit.'; $msgType='danger';
            } else {
                $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed, true)) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($_FILES['signature']['tmp_name']);
                    if (in_array($mime, $allowedMime, true)) {
                        $safe = $shipment['tracking_number'] . '_sig_' . bin2hex(random_bytes(8)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['signature']['tmp_name'], $uploadDir . $safe)) { $sigPath = 'uploads/attachments/' . $safe; }
                    } else {
                        $msg = 'Invalid signature file type.'; $msgType='danger';
                    }
                }
            }
        }
        // Multiple POD photos.
        $photos = [];
        if (!empty($_FILES['photos']['name']) && is_array($_FILES['photos']['name'])) {
            foreach ($_FILES['photos']['name'] as $i => $nm) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    if ($_FILES['photos']['size'][$i] > $maxPhotoSize) {
                        $msg = 'POD photo exceeds 10 MB limit.'; $msgType='danger';
                        continue;
                    }
                    $ext = strtolower(pathinfo($nm, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed, true)) {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($_FILES['photos']['tmp_name'][$i]);
                        if (in_array($mime, $allowedMime, true)) {
                            $safe = $shipment['tracking_number'] . '_pod_' . bin2hex(random_bytes(8)) . '_' . $i . '.' . $ext;
                            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $uploadDir . $safe)) {
                                $photos[] = 'uploads/attachments/' . $safe;
                            }
                        }
                    }
                }
            }
        }
        try {
            $db->beginTransaction();
            // Preserve previous photos by merging.
            $stmt = $db->prepare("SELECT photos FROM delivery_confirmations WHERE shipment_id=:id ORDER BY id DESC LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $prev = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($prev && !empty($prev['photos'])) { $prevArr = json_decode($prev['photos'], true) ?: []; $photos = array_merge($prevArr, $photos); }
            $db->prepare("INSERT INTO delivery_confirmations (shipment_id, receiver_name, signature_path, gps_lat, gps_lng, delivery_date, delivery_time, courier_notes, customer_feedback, photos, confirmed_by, created_at)
                VALUES (:s,:rn,:sp,:la,:ln,:dd,:dt,:cn,:cf,:ph,:u,NOW())")
                ->execute([':s'=>$id,':rn'=>$receiver?:null,':sp'=>$sigPath,':la'=>$gpsLat,':ln'=>$gpsLng,':dd'=>$ddate,':dt'=>$dtime,':cn'=>$notes,':cf'=>$feedback,':ph'=>json_encode($photos),':u'=>$_SESSION['admin_id']??null]);
            // Reflect delivery on the shipment.
            $db->prepare("UPDATE shipments SET status='delivered', actual_delivery=NOW(), signature_image=COALESCE(:sp, signature_image), pod_photo=COALESCE(:pp, pod_photo), updated_at=NOW() WHERE id=:id")
                ->execute([':sp'=>$sigPath,':pp'=>!empty($photos)?end($photos):null,':id'=>$id]);
            $db->commit();
            logShipmentAction($db, 'shipment_delivered', $id, "Delivery confirmed by " . ($receiver ?: 'courier'));
            $msg = 'Delivery confirmation saved.'; $msgType='success';
        } catch (Exception $e) { $db->rollBack(); error_log('Error: ' . $e->getMessage());
            $msg = 'An error occurred. Please try again later.'; $msgType='danger'; }
    }
}

$stmt = $db->prepare("SELECT * FROM delivery_confirmations WHERE shipment_id=:id ORDER BY created_at DESC, id DESC");
$stmt->execute([':id' => $id]);
$confirmations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Delivery Confirmation — <?php echo $v('tracking_number'); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card"><div class="card-header">Record Confirmation</div><div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfInput(); ?>
                    <div class="mb-3"><label class="form-label">Receiver Name</label><input name="receiver_name" class="form-control" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col"><label class="form-label">Delivery Date</label><input type="date" name="delivery_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="col"><label class="form-label">Delivery Time</label><input type="time" name="delivery_time" class="form-control" value="<?php echo date('H:i'); ?>" required></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col"><label class="form-label">GPS Latitude</label><input name="gps_lat" class="form-control" step="0.0000001" placeholder="e.g. 40.7128"></div>
                        <div class="col"><label class="form-label">GPS Longitude</label><input name="gps_lng" class="form-control" step="0.0000001" placeholder="e.g. -74.0060"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Signature (image)</label><input type="file" name="signature" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label">Proof of Delivery Photos (multiple)</label><input type="file" name="photos[]" class="form-control" accept="image/*" multiple></div>
                    <div class="mb-3"><label class="form-label">Courier Notes</label><textarea name="courier_notes" class="form-control" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Customer Feedback</label><textarea name="customer_feedback" class="form-control" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Save delivery confirmation?');"><i class="bi bi-check-lg"></i> Confirm Delivery</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card"><div class="card-header">Confirmation History (<?php echo count($confirmations); ?>)</div><div class="card-body">
                <?php if (empty($confirmations)): ?><p class="text-muted">No confirmations recorded.</p>
                <?php else: foreach ($confirmations as $c): $ph = json_decode($c['photos'] ?? '[]', true) ?: []; ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between"><strong><?php echo htmlspecialchars($c['receiver_name'] ?? 'N/A'); ?></strong><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></small></div>
                        <div class="small text-muted mb-2"><?php echo htmlspecialchars($c['delivery_date'] ?? ''); ?> <?php echo htmlspecialchars($c['delivery_time'] ?? ''); ?>
                            <?php if ($c['gps_lat'] !== null): ?> · GPS <?php echo $c['gps_lat']; ?>, <?php echo $c['gps_lng']; ?><?php endif; ?></div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <?php if ($c['signature_path']): ?><a href="../<?php echo htmlspecialchars($c['signature_path']); ?>" target="_blank"><img src="../<?php echo htmlspecialchars($c['signature_path']); ?>" style="height:60px;border:1px solid #ddd;border-radius:6px;"></a><?php endif; ?>
                            <?php foreach ($ph as $p): ?><a href="../<?php echo htmlspecialchars($p); ?>" target="_blank"><img src="../<?php echo htmlspecialchars($p); ?>" style="height:60px;width:60px;object-fit:cover;border:1px solid #ddd;border-radius:6px;"></a><?php endforeach; ?>
                        </div>
                        <?php if ($c['courier_notes']): ?><p class="small mb-1"><strong>Courier:</strong> <?php echo htmlspecialchars($c['courier_notes']); ?></p><?php endif; ?>
                        <?php if ($c['customer_feedback']): ?><p class="small mb-0"><strong>Feedback:</strong> <?php echo htmlspecialchars($c['customer_feedback']); ?></p><?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div></div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
