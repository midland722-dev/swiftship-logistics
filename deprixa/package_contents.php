<?php
/**
 * Package Contents — manage individual line items inside a shipment
 * (item name, category, quantity, weight, declared value, serial number,
 * fragile / dangerous-goods flags).
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('edit_shipment');

$page_title = 'Package Contents - ' . SITE_NAME;
$db = getDB();
try {
    $db->exec("CREATE TABLE IF NOT EXISTS package_items (
        id int(11) NOT NULL AUTO_INCREMENT,
        shipment_id int(11) NOT NULL,
        item_name varchar(255) NOT NULL,
        category varchar(120) DEFAULT NULL,
        quantity int(11) DEFAULT 1,
        weight decimal(10,2) DEFAULT 0,
        declared_value decimal(12,2) DEFAULT 0,
        serial_number varchar(120) DEFAULT NULL,
        is_fragile tinyint(1) DEFAULT 0,
        is_dangerous tinyint(1) DEFAULT 0,
        created_at timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id),
        KEY shipment_id (shipment_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

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
        $act = $_POST['act'] ?? 'add';
        if ($act === 'add') {
            $db->prepare("INSERT INTO package_items (shipment_id, item_name, category, quantity, weight, declared_value, serial_number, is_fragile, is_dangerous, created_at)
                VALUES (:s,:n,:c,:q,:w,:v,:sn,:f,:d,NOW())")
                ->execute([
                    ':s'=>$id,':n'=>trim($_POST['item_name']??''),':c'=>trim($_POST['category']??''),
                    ':q'=>max(1,intval($_POST['quantity']??1)),':w'=>max(0,floatval($_POST['weight']??0)),
                    ':v'=>max(0,floatval($_POST['declared_value']??0)),':sn'=>trim($_POST['serial_number']??''),
                    ':f'=>isset($_POST['is_fragile'])?1:0,':d'=>isset($_POST['is_dangerous'])?1:0,
                ]);
            $msg='Item added.'; $msgType='success';
        } elseif ($act === 'delete' && !empty($_POST['item_id'])) {
            $db->prepare("DELETE FROM package_items WHERE id=:i AND shipment_id=:s")->execute([':i'=>intval($_POST['item_id']),':s'=>$id]);
            $msg='Item removed.'; $msgType='success';
        }
        logShipmentAction($db, 'shipment_edited', $id, 'Updated package contents');
    }
}

$stmt = $db->prepare("SELECT * FROM package_items WHERE shipment_id=:id ORDER BY id ASC");
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cats = itemCategoryOptions();
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Package Contents — <?php echo $v('tracking_number'); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card"><div class="card-header">Add Item</div><div class="card-body">
                <form method="POST">
                    <?php echo csrfInput(); ?><input type="hidden" name="act" value="add">
                    <div class="mb-2"><label class="form-label small">Item Name</label><input name="item_name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label small">Category</label><select name="category" class="form-select"><?php foreach ($cats as $c=>$l): ?><option value="<?php echo $c; ?>"><?php echo $l; ?></option><?php endforeach; ?></select></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label small">Qty</label><input type="number" min="1" name="quantity" class="form-control" value="1"></div>
                        <div class="col"><label class="form-label small">Weight (kg)</label><input type="number" step="0.1" name="weight" class="form-control"></div>
                        <div class="col"><label class="form-label small">Value</label><input type="number" step="0.01" name="declared_value" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label class="form-label small">Serial Number</label><input name="serial_number" class="form-control"></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="is_fragile" id="fr"><label class="form-check-label" for="fr">Fragile</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="is_dangerous" id="dg"><label class="form-check-label" for="dg">Dangerous Goods</label></div>
                    <div class="mt-3"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Add Item</button></div>
                </form>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Items (<?php echo count($items); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Item</th><th>Category</th><th>Qty</th><th>Weight</th><th>Value</th><th>Serial</th><th>Flags</th><th></th></tr></thead>
                    <tbody>
                    <?php if (empty($items)): ?><tr><td colspan="8" class="text-center text-muted py-4">No items.</td></tr>
                    <?php else: foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($it['category'] ?? ''); ?></td>
                            <td><?php echo $it['quantity']; ?></td>
                            <td><?php echo $it['weight']; ?> kg</td>
                            <td><?php echo number_format($it['declared_value'],2); ?></td>
                            <td><?php echo htmlspecialchars($it['serial_number'] ?? ''); ?></td>
                            <td><?php echo $it['is_fragile']?'<span class="badge bg-warning text-dark">Fragile</span> ':''; ?><?php echo $it['is_dangerous']?'<span class="badge bg-danger">DG</span>':''; ?></td>
                            <td><form method="POST" class="d-inline" onsubmit="return confirm('Remove item?');"><?php echo csrfInput(); ?><input type="hidden" name="act" value="delete"><input type="hidden" name="item_id" value="<?php echo $it['id']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div></div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
