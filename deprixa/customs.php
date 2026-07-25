<?php
/**
 * Customs Management — international shipment customs info: status, clearance
 * office, import duties, taxes, HS code, documents, remarks, clearance date.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('manage_customs');

$page_title = 'Customs Management - ' . SITE_NAME;
$db = getDB();

// Additional customs columns.
$addCols = [
    'customs_status'   => "varchar(50) DEFAULT 'pending'",
    'clearance_office' => 'varchar(255) DEFAULT NULL',
    'clearance_date'   => 'date DEFAULT NULL',
    'customs_remarks'  => 'text DEFAULT NULL',
];
$countRow = $db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
if ((int)$countRow <= 10000) {
    foreach ($addCols as $n => $def) {
        try { $db->query("SELECT `$n` FROM shipments LIMIT 1"); }
        catch (Exception $e) { try { $db->exec("ALTER TABLE shipments ADD COLUMN `$n` $def"); } catch (Exception $e2) {} }
    }
}

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { echo '<div class="alert alert-danger m-4">Shipment not found.</div>'; require_once __DIR__.'/includes/footer.php'; exit; }
$GLOBALS['shipment'] = $shipment;

$msg=''; $msgType='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) { $msg='Invalid token.'; $msgType='danger'; }
    else {
        $fields = [
            'customs_declaration_number' => trim($_POST['customs_declaration_number'] ?? ''),
            'hs_code' => trim($_POST['hs_code'] ?? ''),
            'country_of_origin' => trim($_POST['country_of_origin'] ?? '') ?: null,
            'import_duty' => trim($_POST['import_duty'] ?? '') === '' ? null : max(0, floatval($_POST['import_duty'])),
            'tax_info' => trim($_POST['tax_info'] ?? ''),
            'customs_documents' => trim($_POST['customs_documents'] ?? ''),
            'customs_status' => trim($_POST['customs_status'] ?? 'pending'),
            'clearance_office' => trim($_POST['clearance_office'] ?? ''),
            'clearance_date' => trim($_POST['clearance_date'] ?? null) ?: null,
            'customs_remarks' => trim($_POST['customs_remarks'] ?? ''),
        ];
        $cols = array_keys($fields);
        $sql = "UPDATE shipments SET " . implode(', ', array_map(fn($c)=>"$c=:$c", $cols)) . ", updated_at=NOW() WHERE id=:id";
        $params = [':id'=>$id]; foreach ($cols as $c) { $params[":$c"] = $fields[$c]; }
        $db->prepare($sql)->execute($params);
        logShipmentAction($db, 'shipment_edited', $id, 'Updated customs information');
        $msg='Customs information saved.'; $msgType='success';
        $stmt = $db->prepare("SELECT * FROM shipments WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
$sel = function($k,$val) { return ($GLOBALS['shipment'][$k] ?? '') == $val ? 'selected' : ''; };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-globe me-2"></i>Customs — <?php echo $v('tracking_number'); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="card"><div class="card-body row g-3">
        <form method="POST" class="row g-3">
            <?php echo csrfInput(); ?>
            <div class="col-md-4"><label class="form-label">Customs Status</label><select name="customs_status" class="form-select">
                <?php foreach (['pending','under_review','cleared','held','seized'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $sel('customs_status',$s); ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Clearance Office</label><input name="clearance_office" class="form-control" value="<?php echo $v('clearance_office'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Clearance Date</label><input type="date" name="clearance_date" class="form-control" value="<?php echo $v('clearance_date'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Customs Declaration Number</label><input name="customs_declaration_number" class="form-control" value="<?php echo $v('customs_declaration_number'); ?>"></div>
            <div class="col-md-4"><label class="form-label">HS Code</label><input name="hs_code" class="form-control" value="<?php echo $v('hs_code'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Country of Origin</label><input name="country_of_origin" class="form-control" value="<?php echo $v('country_of_origin'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Import Duties</label><input type="number" step="0.01" name="import_duty" class="form-control" value="<?php echo $v('import_duty'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Taxes</label><input name="tax_info" class="form-control" value="<?php echo $v('tax_info'); ?>" placeholder="e.g. VAT 20%"></div>
            <div class="col-12"><label class="form-label">Customs Documents</label><textarea name="customs_documents" class="form-control" rows="2"><?php echo $v('customs_documents'); ?></textarea></div>
            <div class="col-12"><label class="form-label">Customs Remarks</label><textarea name="customs_remarks" class="form-control" rows="2"><?php echo $v('customs_remarks'); ?></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-primary" onclick="return confirm('Save customs information?');"><i class="bi bi-check-lg"></i> Save Customs Info</button></div>
        </form>
    </div></div>
    <div class="mt-2"><a href="documents_manager.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-paperclip"></i> Manage Customs Documents</a></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
