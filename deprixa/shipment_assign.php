<?php
/**
 * Assign Driver / Branch / Vehicle to a shipment (transit management).
 * Used by the assignment modal in shipments.php and shipment_details.php.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/shipment_helpers.php';

$db = getDB();
ensureCourierTables($db);
ensureShipmentStatusEnum($db);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shipments.php');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
    header('Location: shipments.php?msg=csrf');
    exit;
}

$shipment_id = intval($_POST['shipment_id'] ?? 0);
$driver_id   = intval($_POST['driver_id'] ?? 0);
$branch_id   = intval($_POST['branch_id'] ?? 0);
$vehicle_id  = intval($_POST['vehicle_id'] ?? 0);

if (!$shipment_id) {
    header('Location: shipments.php?msg=invalid');
    exit;
}

try {
    $db->beginTransaction();
    $db->prepare("
        UPDATE shipments
        SET driver_id = :d, branch_id = :b, vehicle_id = :v, updated_at = NOW()
        WHERE id = :id
    ")->execute([
        ':d' => $driver_id ?: null,
        ':b' => $branch_id ?: null,
        ':v' => $vehicle_id ?: null,
        ':id' => $shipment_id,
    ]);

    // Keep current_branch denormalised for quick display / filtering.
    if ($branch_id) {
        $b = $db->prepare("SELECT name, city FROM locations WHERE id = :id LIMIT 1");
        $b->execute([':id' => $branch_id]);
        $branch = $b->fetch(PDO::FETCH_ASSOC);
        if ($branch) {
            $db->prepare("UPDATE shipments SET current_branch = :c WHERE id = :id")
               ->execute([':c' => $branch['name'] . ' (' . $branch['city'] . ')', ':id' => $shipment_id]);
        }
    }
    $db->commit();
    clearDashboardCache();
} catch (Exception $e) {
    $db->rollBack();
    header('Location: shipment_details.php?id=' . $shipment_id . '&msg=error');
    exit;
}

header('Location: shipment_details.php?id=' . $shipment_id . '&msg=assigned');
exit;
