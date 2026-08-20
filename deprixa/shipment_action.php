<?php
/**
 * Shared, permission-gated handler for quick shipment lifecycle actions:
 * hold, resume, return-to-sender, cancel, archive, delete.
 * Every action is audited to activity_logs and never overwrites tracking
 * history (status changes append a tracking event where appropriate).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/includes/permissions.php';

/**
 * Only allow relative, same-origin redirects. Rejects absolute URLs,
 * scheme-based URLs (http:, javascript:, etc.) and protocol-relative (//)
 * URLs to prevent open-redirect phishing via the `redirect` POST param.
 */
function isSafeInternalRedirect(string $url): bool {
    if ($url === '') {
        return false;
    }
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) {
        return false; // has a URI scheme
    }
    if (str_starts_with($url, '//')) {
        return false; // protocol-relative
    }
    return true;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = getDB();
ensureShipmentStatusEnum($db);
ensureTrackingHistory($db);

$redirect = isSafeInternalRedirect($_POST['redirect'] ?? '')
    ? $_POST['redirect']
    : ('shipment_details.php?id=' . intval($_POST['id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shipments.php'); exit;
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid security token.'];
    header('Location: ' . $redirect); exit;
}

if (empty($_SESSION['admin_id'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Authentication required.'];
    header('Location: login.php'); exit;
}

$id = intval($_POST['id'] ?? 0);
$act = $_POST['act'] ?? '';
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Shipment not found.'];
    header('Location: shipments.php'); exit;
}

$ok = false; $msg = '';
try {
    $db->beginTransaction();
    switch ($act) {
        case 'hold':
            requirePermission('hold_shipment');
            $db->prepare("UPDATE shipments SET is_on_hold = 1, status = 'on_hold', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
            addTrackingEvent($db, $id, $shipment['tracking_number'], 'on_hold', $shipment['current_city'] ?? '', 'Shipment placed on hold by admin');
            logShipmentAction($db, 'shipment_hold', $id, 'Shipment placed on hold');
            $msg = 'Shipment held.'; break;
        case 'resume':
            requirePermission('hold_shipment');
            $db->prepare("UPDATE shipments SET is_on_hold = 0, status = 'in_transit', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
            addTrackingEvent($db, $id, $shipment['tracking_number'], 'in_transit', $shipment['current_city'] ?? '', 'Shipment resumed by admin');
            logShipmentAction($db, 'shipment_resume', $id, 'Shipment resumed');
            $msg = 'Shipment resumed.'; break;
        case 'return':
            requirePermission('return_shipment');
            $db->prepare("UPDATE shipments SET return_to_sender = 1, status = 'returned', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
            addTrackingEvent($db, $id, $shipment['tracking_number'], 'returned', $shipment['current_city'] ?? '', 'Returned to sender');
            logShipmentAction($db, 'shipment_return', $id, 'Returned to sender');
            $msg = 'Marked as returned to sender.'; break;
        case 'cancel':
            requirePermission('cancel_shipment');
            $db->prepare("UPDATE shipments SET is_cancelled = 1, status = 'cancelled', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
            addTrackingEvent($db, $id, $shipment['tracking_number'], 'cancelled', $shipment['current_city'] ?? '', 'Shipment cancelled');
            logShipmentAction($db, 'shipment_cancel', $id, 'Shipment cancelled');
            $msg = 'Shipment cancelled.'; break;
        case 'archive':
            requirePermission('archive_shipment');
            $db->prepare("UPDATE shipments SET is_archived = 1, updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
            logShipmentAction($db, 'shipment_archive', $id, 'Shipment archived');
            $msg = 'Shipment archived.'; break;
        case 'delete':
            requirePermission('delete_shipment');
            $db->prepare("DELETE FROM packages WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM tracking_logs WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM shipment_status_history_v2 WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM tracking_history WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM payments WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM delivery_attempts WHERE shipment_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM attachments WHERE entity_type = 'shipment' AND entity_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM shipments WHERE id = :id")->execute([':id' => $id]);
            logShipmentAction($db, 'shipment_delete', $id, 'Shipment deleted (cascade)');
            $db->commit();
            clearDashboardCache();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Shipment ' . $shipment['tracking_number'] . ' deleted.'];
            header('Location: shipments.php?msg=deleted'); exit;
        default:
            throw new Exception('Unknown action.');
    }
    $db->commit();
    clearDashboardCache();
    $ok = true;
} catch (Exception $e) {
    if ($db->inTransaction()) { $db->rollBack(); }
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error: ' . $e->getMessage()];
    header('Location: ' . $redirect); exit;
}

if ($ok) {
    $_SESSION['flash'] = ['type' => 'success', 'msg' => $msg];
}
header('Location: ' . $redirect); exit;
