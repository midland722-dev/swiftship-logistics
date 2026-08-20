<?php
/**
 * Tracking History — dedicated, permanent, chronological view of all tracking
 * events. Supports search, status filter, date filter, PDF/Excel export and
 * print. Events are never editable; Super Admin may delete an entry.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$page_title = 'Tracking History - ' . SITE_NAME;
$db = getDB();
ensureShipmentStatusEnum($db);
ensureTrackingHistory($db);
ensureCourierTrackingEnhancements($db);

$superAdmin = isSuperAdmin();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$logger = null;
try {
    $logger = getLogger();
} catch (Exception $e) {
    $logger = null;
}

$hasCustomsProcedure = false;
try {
    $db->query("SELECT customs_procedure FROM shipment_status_history_v2 LIMIT 1");
    $hasCustomsProcedure = true;
} catch (Exception $e) {
    $hasCustomsProcedure = false;
}

$historySelect = $hasCustomsProcedure ? 'h.*' : 'h.id, h.shipment_id, h.status_code, h.occurred_at, h.location, h.remarks, h.occurred_by, h.created_at';

function safeRedirect($msg = '') {
    $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => $msg ?: 'Invalid request.'];
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

function validateCsrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        safeRedirect('Invalid security token.');
    }
}

function validateAdmin() {
    if (empty($_SESSION['admin_id'])) {
        safeRedirect('Authentication required.');
    }
}

function sanitizeTrackingInput($value, $maxLength = 255) {
    return trim(substr((string)$value, 0, $maxLength));
}

function validateDateTime($date, $time) {
    if (empty($date) || empty($time)) {
        return null;
    }
    $ts = strtotime($date . ' ' . $time);
    if ($ts === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $ts);
}

function checkRateLimit($action, $limit = 10, $window = 60) {
    $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + $window];
    }
    if (time() > $_SESSION[$key]['reset']) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + $window];
    }
    $_SESSION[$key]['count']++;
    return $_SESSION[$key]['count'] <= $limit;
}

// Filters
$id = intval($_GET['id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

// Super Admin delete + reorder
if ($superAdmin && isset($_GET['delete_event']) && isset($_GET['csrf'])) {
    validateCsrf();
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['csrf'])) {
        $eid = intval($_GET['delete_event']);
        if ($eid > 0) {
            $db->prepare("DELETE FROM shipment_status_history_v2 WHERE id = :id")->execute([':id' => $eid]);
            $db->prepare("DELETE FROM tracking_history WHERE id = :id")->execute([':id' => $eid]);
            if ($logger) {
                $logger->info('Tracking event deleted', ['event_id' => $eid, 'admin_id' => $_SESSION['admin_id'] ?? null]);
            }
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if ($superAdmin && isset($_GET['reorder_event']) && isset($_GET['csrf']) && isset($_GET['direction'])) {
    validateCsrf();
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['csrf'])) {
        $eid = intval($_GET['reorder_event']);
        $direction = $_GET['direction'] === 'up' ? 'up' : 'down';
        if ($eid > 0) {
            $stmt = $db->prepare("SELECT id, shipment_id, occurred_at FROM shipment_status_history_v2 WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $eid]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($event) {
                $sid = (int)$event['shipment_id'];
                if ($direction === 'up') {
                    $stmt = $db->prepare("SELECT id, occurred_at FROM shipment_status_history_v2 WHERE shipment_id = :sid AND occurred_at < :oa ORDER BY occurred_at DESC, id DESC LIMIT 1");
                    $stmt->execute([':sid' => $sid, ':oa' => $event['occurred_at']]);
                    $swap = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $db->prepare("SELECT id, occurred_at FROM shipment_status_history_v2 WHERE shipment_id = :sid AND occurred_at > :oa ORDER BY occurred_at ASC, id ASC LIMIT 1");
                    $stmt->execute([':sid' => $sid, ':oa' => $event['occurred_at']]);
                    $swap = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                if ($swap) {
                    $tmp = $swap['occurred_at'];
                    $db->prepare("UPDATE shipment_status_history_v2 SET occurred_at = :swap WHERE id = :eid")->execute([':swap' => $swap['occurred_at'], ':eid' => $eid]);
                    $db->prepare("UPDATE shipment_status_history_v2 SET occurred_at = :oa WHERE id = :sid2")->execute([':oa' => $event['occurred_at'], ':sid2' => $swap['id']]);
                    logShipmentAction($db, 'reorder', $sid, 'Reordered tracking event #' . $eid . ' ' . $direction, ['event_id' => $eid, 'direction' => $direction, 'swapped_with' => $swap['id']]);
                    if ($logger) {
                        $logger->info('Tracking event reordered', ['event_id' => $eid, 'direction' => $direction, 'swapped_with' => $swap['id'], 'admin_id' => $_SESSION['admin_id'] ?? null]);
                    }
                }
            }
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Inline edit + transit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inline_edit' && !empty($_POST['shipment_id'])) {
    validateCsrf();
    validateAdmin();
    if (!checkRateLimit('inline_edit', 10, 60)) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Too many requests. Please try again later.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    try {
        $shipment_id = intval($_POST['shipment_id']);
        if ($shipment_id <= 0) {
            throw new Exception("Invalid shipment ID.");
        }

        $stmt = $db->prepare("SELECT id FROM shipments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $shipment_id]);
        $shipmentExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shipmentExists) {
            throw new Exception("Shipment not found.");
        }

        $sender_name = sanitizeTrackingInput($_POST['sender_name'] ?? '', 255);
        $sender_phone = sanitizeTrackingInput($_POST['sender_phone'] ?? '', 90);
        $receiver_name = sanitizeTrackingInput($_POST['receiver_name'] ?? '', 255);
        $receiver_phone = sanitizeTrackingInput($_POST['receiver_phone'] ?? '', 90);
        $origin_city = sanitizeTrackingInput($_POST['origin_city'] ?? '', 100);
        $destination_city = sanitizeTrackingInput($_POST['destination_city'] ?? '', 100);
        $weight = sanitizeTrackingInput($_POST['weight'] ?? '', 50);
        $status = sanitizeTrackingInput($_POST['status'] ?? '', 50);
        $location = sanitizeTrackingInput($_POST['location'] ?? '', 255);
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($status)) {
            throw new Exception("Status is required.");
        }

        $db->beginTransaction();

        $upd = "
            UPDATE shipments SET
                sender_name = :sender_name,
                sender_phone = :sender_phone,
                receiver_name = :receiver_name,
                receiver_phone = :receiver_phone,
                origin_city = :origin_city,
                destination_city = :destination_city,
                total_weight = :weight,
                status = :status,
                updated_at = NOW()
        ";
        $params = [
            ':sender_name' => $sender_name,
            ':sender_phone' => $sender_phone,
            ':receiver_name' => $receiver_name,
            ':receiver_phone' => $receiver_phone,
            ':origin_city' => $origin_city,
            ':destination_city' => $destination_city,
            ':total_weight' => $weight,
            ':status' => $status,
            ':id' => $shipment_id,
        ];
        if ($location !== '' && $location !== 'N/A') {
            $upd .= ", current_city = :cc";
            $locDisplay = $location;
            $transitOpts = transitLocationOptions();
            if (isset($transitOpts[$location])) {
                $locDisplay = $transitOpts[$location];
            }
            $params[':cc'] = $locDisplay;
        }
        $upd .= " WHERE id = :id";

        $stmt = $db->prepare($upd);
        $stmt->execute($params);

        $tracking_number = '';
        if ($location !== '' || $remarks !== '') {
            $tn = $db->prepare("SELECT tracking_number FROM shipments WHERE id = :id");
            $tn->execute([':id' => $shipment_id]);
            $tracking_number = $tn->fetchColumn();

            $checkDuplicate = $db->prepare("
                SELECT COUNT(*) FROM shipment_status_history_v2
                WHERE shipment_id = :sid AND status_code = :status AND location = :location AND remarks = :remarks
                AND occurred_at >= NOW() - INTERVAL 1 MINUTE
            ");
            $checkDuplicate->execute([
                ':sid' => $shipment_id,
                ':status' => $status,
                ':location' => $location ?: 'N/A',
                ':remarks' => $remarks ?: 'Shipment updated via tracking history'
            ]);
            $isDuplicate = ((int)$checkDuplicate->fetchColumn()) > 0;

            if (!$isDuplicate) {
                $stmt = $db->prepare("
                    INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                    VALUES (:shipment_id, :status, NOW(), :location, :remarks, :admin_id)
                ");
                $stmt->execute([
                    ':shipment_id' => $shipment_id,
                    ':status' => $status,
                    ':location' => $location ?: 'N/A',
                    ':remarks' => $remarks ?: 'Shipment updated via tracking history',
                    ':admin_id' => $_SESSION['admin_id'] ?? null,
                ]);

                if ($tracking_number) {
                    try {
                        ensureTrackingHistory($db);
                        $updatedBy = ($_SESSION['admin_id'] ?? null) ? ('Admin #' . $_SESSION['admin_id']) : 'Admin';
                        addTrackingEvent($db, $shipment_id, $tracking_number, $status, $location ?: 'N/A', $remarks ?: 'Shipment updated via tracking history', $updatedBy);
                    } catch (Exception $e) { /* non-fatal */ }
                }
            }
        }

        $db->commit();
        clearDashboardCache();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Shipment updated successfully.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        if ($logger) {
            $logger->error('Inline edit failed', ['shipment_id' => $_POST['shipment_id'] ?? 0, 'error' => $e->getMessage(), 'admin_id' => $_SESSION['admin_id'] ?? null]);
        }
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error updating shipment. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event' && !empty($_POST['shipment_id'])) {
    validateCsrf();
    validateAdmin();
    if (!checkRateLimit('add_event', 10, 60)) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Too many requests. Please try again later.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    try {
        $shipment_id = intval($_POST['shipment_id']);
        if ($shipment_id <= 0) {
            throw new Exception("Invalid shipment ID.");
        }

        $stmt = $db->prepare("SELECT id, tracking_number FROM shipments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $shipment_id]);
        $shipmentExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shipmentExists) {
            throw new Exception("Shipment not found.");
        }

        $new_status = sanitizeTrackingInput($_POST['new_status'] ?? '', 50);
        $location = sanitizeTrackingInput($_POST['location'] ?? '', 255);
        $location_custom = sanitizeTrackingInput($_POST['location_custom'] ?? '', 255);
        $procedure = sanitizeTrackingInput($_POST['customs_procedure'] ?? '', 255);
        $procedure_custom = sanitizeTrackingInput($_POST['procedure_custom'] ?? '', 255);
        $remarks = trim($_POST['remarks'] ?? '');
        $event_notes = trim($_POST['event_notes'] ?? '');
        $event_date = sanitizeTrackingInput($_POST['event_date'] ?? '', 10);
        $event_time = sanitizeTrackingInput($_POST['event_time'] ?? '', 8);

        if (empty($new_status)) {
            throw new Exception("Status is required.");
        }
        if (!isValidStatus($new_status)) {
            throw new Exception("Invalid status: " . htmlspecialchars($new_status));
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
            throw new Exception("Invalid date format.");
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $event_time)) {
            throw new Exception("Invalid time format.");
        }

        $occurredAt = validateDateTime($event_date, $event_time);
        if ($occurredAt === null) {
            throw new Exception("Invalid date/time combination.");
        }

        $finalLocation = $location_custom !== '' ? $location_custom : $location;
        $finalProcedure = $procedure_custom !== '' ? $procedure_custom : $procedure;

        $checkDuplicate = $db->prepare("
            SELECT COUNT(*) FROM shipment_status_history_v2
            WHERE shipment_id = :sid AND status_code = :status AND occurred_at = :oa AND location = :loc
        ");
        $checkDuplicate->execute([
            ':sid' => $shipment_id,
            ':status' => $new_status,
            ':oa' => $occurredAt,
            ':loc' => $finalLocation ?: 'N/A'
        ]);
        $isDuplicate = ((int)$checkDuplicate->fetchColumn()) > 0;

        if (!$isDuplicate) {
            $db->beginTransaction();

            $upd = "UPDATE shipments SET status = :status, updated_at = NOW()";
            $params = [':status' => $new_status, ':id' => $shipment_id];
            if ($finalLocation !== '' && $finalLocation !== 'N/A') {
                $upd .= ", current_city = :cc";
                $locDisplay = $finalLocation;
                $transitOpts = transitLocationOptions();
                if (isset($transitOpts[$finalLocation])) {
                    $locDisplay = $transitOpts[$finalLocation];
                }
                $params[':cc'] = $locDisplay;
            }
            $upd .= " WHERE id = :id";
            $db->prepare($upd)->execute($params);

            $tracking_number = $shipmentExists['tracking_number'] ?? '';

            if ($hasCustomsProcedure) {
                $stmt = $db->prepare("
                    INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, customs_procedure, event_notes, occurred_by)
                    VALUES (:shipment_id, :status, :occurred_at, :location, :remarks, :customs_procedure, :event_notes, :admin_id)
                ");
                $stmt->execute([
                    ':shipment_id' => $shipment_id,
                    ':status' => $new_status,
                    ':occurred_at' => $occurredAt,
                    ':location' => $finalLocation ?: 'N/A',
                    ':remarks' => $remarks ?: 'Tracking event added from history page',
                    ':customs_procedure' => $finalProcedure ?: null,
                    ':event_notes' => $event_notes ?: null,
                    ':admin_id' => $_SESSION['admin_id'] ?? null,
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                    VALUES (:shipment_id, :status, :occurred_at, :location, :remarks, :admin_id)
                ");
                $stmt->execute([
                    ':shipment_id' => $shipment_id,
                    ':status' => $new_status,
                    ':occurred_at' => $occurredAt,
                    ':location' => $finalLocation ?: 'N/A',
                    ':remarks' => $remarks ?: 'Tracking event added from history page',
                    ':admin_id' => $_SESSION['admin_id'] ?? null,
                ]);
            }

            if ($tracking_number) {
                try {
                    ensureTrackingHistory($db);
                    $updatedBy = ($_SESSION['admin_id'] ?? null) ? ('Admin #' . $_SESSION['admin_id']) : 'Admin';
                    addTrackingEvent($db, $shipment_id, $tracking_number, $new_status, $finalLocation ?: 'N/A', $remarks ?: 'Tracking event added from history page', $updatedBy);
                } catch (Exception $e) { /* non-fatal */ }
            }

            $db->commit();
            clearDashboardCache();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tracking event added successfully.'];
            if ($logger) {
                $logger->info('Tracking event added', ['shipment_id' => $shipment_id, 'status' => $new_status, 'location' => $finalLocation, 'admin_id' => $_SESSION['admin_id'] ?? null]);
            }
        } else {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Duplicate event prevented.'];
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($logger) {
            $logger->error('Add tracking event failed', ['shipment_id' => $_POST['shipment_id'] ?? 0, 'error' => $e->getMessage(), 'admin_id' => $_SESSION['admin_id'] ?? null]);
        }
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error adding event. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_event' && !empty($_POST['shipment_id']) && !empty($_POST['event_id'])) {
    validateCsrf();
    validateAdmin();
    if (!checkRateLimit('edit_event', 10, 60)) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Too many requests. Please try again later.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    try {
        $shipment_id = intval($_POST['shipment_id']);
        $event_id = intval($_POST['event_id']);
        if ($shipment_id <= 0 || $event_id <= 0) {
            throw new Exception("Invalid IDs.");
        }

        $stmt = $db->prepare("SELECT id, tracking_number FROM shipments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $shipment_id]);
        $shipmentExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shipmentExists) {
            throw new Exception("Shipment not found.");
        }

        $stmt = $db->prepare("SELECT id FROM shipment_status_history_v2 WHERE id = :eid AND shipment_id = :sid LIMIT 1");
        $stmt->execute([':eid' => $event_id, ':sid' => $shipment_id]);
        $eventExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$eventExists) {
            throw new Exception("Event not found.");
        }

        $new_status = sanitizeTrackingInput($_POST['new_status'] ?? '', 50);
        $location = sanitizeTrackingInput($_POST['location'] ?? '', 255);
        $location_custom = sanitizeTrackingInput($_POST['location_custom'] ?? '', 255);
        $procedure = sanitizeTrackingInput($_POST['customs_procedure'] ?? '', 255);
        $procedure_custom = sanitizeTrackingInput($_POST['procedure_custom'] ?? '', 255);
        $remarks = trim($_POST['remarks'] ?? '');
        $event_notes = trim($_POST['event_notes'] ?? '');
        $event_date = sanitizeTrackingInput($_POST['event_date'] ?? '', 10);
        $event_time = sanitizeTrackingInput($_POST['event_time'] ?? '', 8);

        if (empty($new_status)) {
            throw new Exception("Status is required.");
        }
        if (!isValidStatus($new_status)) {
            throw new Exception("Invalid status: " . htmlspecialchars($new_status));
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
            throw new Exception("Invalid date format.");
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $event_time)) {
            throw new Exception("Invalid time format.");
        }

        $occurredAt = validateDateTime($event_date, $event_time);
        if ($occurredAt === null) {
            throw new Exception("Invalid date/time combination.");
        }

        $finalLocation = $location_custom !== '' ? $location_custom : $location;
        $finalProcedure = $procedure_custom !== '' ? $procedure_custom : $procedure;

        $db->beginTransaction();

        $upd = "UPDATE shipments SET status = :status, updated_at = NOW()";
        $params = [':status' => $new_status, ':id' => $shipment_id];
        if ($finalLocation !== '' && $finalLocation !== 'N/A') {
            $upd .= ", current_city = :cc";
            $locDisplay = $finalLocation;
            $transitOpts = transitLocationOptions();
            if (isset($transitOpts[$finalLocation])) {
                $locDisplay = $transitOpts[$finalLocation];
            }
            $params[':cc'] = $locDisplay;
        }
        $upd .= " WHERE id = :id";
        $stmt = $db->prepare($upd);
        $stmt->execute($params);

        if ($hasCustomsProcedure) {
            $stmt = $db->prepare("
                UPDATE shipment_status_history_v2 SET
                    status_code = :status,
                    occurred_at = :occurred_at,
                    location = :location,
                    remarks = :remarks,
                    customs_procedure = :customs_procedure,
                    event_notes = :event_notes,
                    occurred_by = :admin_id
                WHERE id = :eid AND shipment_id = :shipment_id
            ");
            $stmt->execute([
                ':status' => $new_status,
                ':occurred_at' => $occurredAt,
                ':location' => $finalLocation ?: 'N/A',
                ':remarks' => $remarks ?: 'Tracking event updated',
                ':customs_procedure' => $finalProcedure ?: null,
                ':event_notes' => $event_notes ?: null,
                ':admin_id' => $_SESSION['admin_id'] ?? null,
                ':eid' => $event_id,
                ':shipment_id' => $shipment_id,
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE shipment_status_history_v2 SET
                    status_code = :status,
                    occurred_at = :occurred_at,
                    location = :location,
                    remarks = :remarks,
                    occurred_by = :admin_id
                WHERE id = :eid AND shipment_id = :shipment_id
            ");
            $stmt->execute([
                ':status' => $new_status,
                ':occurred_at' => $occurredAt,
                ':location' => $finalLocation ?: 'N/A',
                ':remarks' => $remarks ?: 'Tracking event updated',
                ':admin_id' => $_SESSION['admin_id'] ?? null,
                ':eid' => $event_id,
                ':shipment_id' => $shipment_id,
            ]);
        }

        $tracking_number = $shipmentExists['tracking_number'] ?? '';

        if ($tracking_number) {
            try {
                $stmt = $db->prepare("
                    UPDATE tracking_history SET
                        status = :status,
                        location = :location,
                        description = :description,
                        event_timestamp = :event_timestamp,
                        updated_by = :updated_by
                    WHERE shipment_id = :shipment_id
                      AND status = :old_status
                      AND event_timestamp = (SELECT MAX(event_timestamp) FROM tracking_history WHERE shipment_id = :shipment_id)
                    LIMIT 1
                ");
                $stmt->execute([
                    ':status' => $new_status,
                    ':location' => $finalLocation ?: 'N/A',
                    ':description' => $remarks ?: 'Tracking event updated',
                    ':event_timestamp' => $occurredAt,
                    ':updated_by' => $_SESSION['admin_name'] ?? 'Admin',
                    ':shipment_id' => $shipment_id,
                    ':old_status' => $eventExists ? ($shipmentExists['status'] ?? '') : '',
                ]);
            } catch (Exception $e) { /* non-fatal */ }
        }

        $db->commit();
        clearDashboardCache();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tracking event updated successfully.'];
        if ($logger) {
            $logger->info('Tracking event updated', ['event_id' => $event_id, 'shipment_id' => $shipment_id, 'status' => $new_status, 'admin_id' => $_SESSION['admin_id'] ?? null]);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($logger) {
            $logger->error('Edit tracking event failed', ['event_id' => $_POST['event_id'] ?? 0, 'shipment_id' => $_POST['shipment_id'] ?? 0, 'error' => $e->getMessage(), 'admin_id' => $_SESSION['admin_id'] ?? null]);
        }
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error updating event. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Fetch shipment data for modals
$shipment = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Export handlers
$export = $_GET['export'] ?? '';
if ($export === 'excel' || $export === 'pdf') {
    list($ewhere, $eparams) = trackingWhere($id, $search, $statusFilter, $from, $to);
    $esql = "SELECT " . $historySelect . " FROM shipment_status_history_v2 h" . ($ewhere ? ' WHERE ' . implode(' AND ', $ewhere) : '')
          . " ORDER BY h.occurred_at DESC, h.id DESC LIMIT 100000";
    $estmt = $db->prepare($esql);
    foreach ($eparams as $k => $v) { $estmt->bindValue($k, $v); }
    $estmt->execute();
    $erows = $estmt->fetchAll(PDO::FETCH_ASSOC);

    $eAdminIds = [];
    foreach ($erows as $r) {
        if (!empty($r['occurred_by'])) {
            $eAdminIds[(int)$r['occurred_by']] = true;
        }
    }
    $eAdminNames = [];
    if (!empty($eAdminIds)) {
        $placeholders = implode(',', array_fill(0, count($eAdminIds), '?'));
        $stmt = $db->prepare("SELECT id, full_name FROM manager_admin WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($eAdminIds));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $admin) {
            $eAdminNames[(int)$admin['id']] = $admin['full_name'];
        }
    }

    $eShipmentIds = array_unique(array_filter(array_map(function($r) { return (int)($r['shipment_id'] ?? 0); }, $erows)));
    $eShipmentCustomerMap = [];
    if (!empty($eShipmentIds)) {
        $placeholders = implode(',', array_fill(0, count($eShipmentIds), '?'));
        $stmt = $db->prepare("SELECT id, customer_id FROM shipments WHERE id IN ($placeholders)");
        $stmt->execute($eShipmentIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $eShipmentCustomerMap[(int)$s['id']] = (int)($s['customer_id'] ?? 0);
        }
    }

    $eNotifiedShipments = [];
    if (!empty($eShipmentIds)) {
        $now = date('Y-m-d H:i:s', strtotime('-30 days'));
        $customerIds = array_unique(array_filter(array_values($eShipmentCustomerMap)));
        if (!empty($customerIds)) {
            $namedPlaceholders = [];
            $inParams = [':ts' => $now];
            foreach ($customerIds as $idx => $cid) {
                $name = ':uid' . $idx;
                $namedPlaceholders[] = $name;
                $inParams[$name] = $cid;
            }
            $stmt = $db->prepare("SELECT DISTINCT user_id FROM notifications WHERE created_at >= :ts AND user_id IN (" . implode(',', $namedPlaceholders) . ")");
            $stmt->execute($inParams);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                $eNotifiedShipments[(int)$uid] = true;
            }
        }
    }

    foreach ($erows as &$r) {
        $by = $r['occurred_by'];
        $r['updated_by_name'] = $by ? ($eAdminNames[(int)$by] ?? '') : '';
        $sid = (int)($r['shipment_id'] ?? 0);
        $customerId = $eShipmentCustomerMap[$sid] ?? 0;
        $r['notify'] = (!empty($eNotifiedShipments[$customerId])) ? 1 : 0;
    }
    unset($r);
    if ($export === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tracking_history_' . date('Ymd') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date','Time','Status','Location','Customs Procedure','Tracking Update','Event Notes','Updated By','Customer Notified']);
        foreach ($erows as $r) {
            fputcsv($out, [
                date('Y-m-d', strtotime($r['occurred_at'])),
                date('H:i:s', strtotime($r['occurred_at'])),
                statusLabel($r['status_code']), $r['location'] ?? '', $r['customs_procedure'] ?? '', $r['remarks'] ?? '', $r['event_notes'] ?? '',
                $r['updated_by_name'] ?? '', !empty($r['notify']) ? 'Yes' : 'No',
            ]);
        }
        fclose($out);
        exit;
    }
    require_once __DIR__ . '/../deprixa/fpdf/fpdf.php';
    try {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'Tracking History' . ($id ? ' - #' . $id : ''), 0, 1);
        $headers = ['Date/Time', 'Status', 'Location', 'Customs Procedure', 'Remarks', 'Event Notes', 'By', 'Notified'];
        $widths  = [32, 26, 40, 34, 48, 48, 28, 18];
        $trunc = function (string $s, int $n): string {
            $s = (string)$s;
            return mb_strlen($s) > $n ? mb_substr($s, 0, $n - 1) . '…' : $s;
        };
        $pdf->SetFont('Arial', 'B', 8);
        foreach ($headers as $i => $h) { $pdf->Cell($widths[$i], 7, $h, 1); }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 8);
        foreach ($erows as $r) {
            $cells = [
                date('Y-m-d H:i', strtotime($r['occurred_at'])),
                $trunc(statusLabel($r['status_code']), 18),
                $trunc($r['location'] ?? '', 26),
                $trunc(canonicalStepLabel($r['customs_procedure'] ?? ''), 22),
                $trunc($r['remarks'] ?? '', 32),
                $trunc($r['event_notes'] ?? '', 32),
                $trunc($r['updated_by_name'] ?? '', 18),
                !empty($r['notify']) ? 'Yes' : 'No',
            ];
            foreach ($cells as $i => $c) { $pdf->Cell($widths[$i], 7, $c, 1); }
            $pdf->Ln();
        }
        $pdf->Output('tracking_history_' . date('Ymd') . '.pdf', 'D');
        exit;
    } catch (Exception $e) {
        error_log('Tracking history PDF export failed: ' . $e->getMessage());
    }
}

$perPage = 50;
$page = max(1, intval($_GET['page'] ?? 1));

function trackingWhere($id, $search, $statusFilter, $from, $to) {
    $where = []; $params = [];
    if ($id) { $where[] = "h.shipment_id = :id"; $params[':id'] = $id; }
    if ($statusFilter) { $where[] = "h.status_code = :st"; $params[':st'] = $statusFilter; }
    if ($search) {
        $searchCond = "(h.remarks LIKE :s OR h.location LIKE :s";
        if ($GLOBALS['hasCustomsProcedure'] ?? false) {
            $searchCond .= " OR h.customs_procedure LIKE :s OR h.event_notes LIKE :s";
        }
        $searchCond .= ")";
        $where[] = $searchCond;
        $params[':s'] = '%' . $search . '%';
    }
    if ($from) { $where[] = "h.occurred_at >= :f"; $params[':f'] = $from . ' 00:00:00'; }
    if ($to) { $where[] = "h.occurred_at <= :t"; $params[':t'] = $to . ' 23:59:59'; }
    return [$where, $params];
}

list($where, $params) = trackingWhere($id, $search, $statusFilter, $from, $to);
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $db->prepare("SELECT COUNT(*) FROM shipment_status_history_v2 h" . $whereSql);
foreach ($params as $k => $v) { $countStmt->bindValue($k, $v); }
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$rows = [];
{
    $sql = "SELECT " . $historySelect . " FROM shipment_status_history_v2 h";
    $where = []; $params = [];
    if ($id) { $where[] = "h.shipment_id = :id"; $params[':id'] = $id; }
    if ($statusFilter) { $where[] = "h.status_code = :st"; $params[':st'] = $statusFilter; }
    if ($search) {
        $searchCond = "(h.remarks LIKE :s OR h.location LIKE :s";
        if ($hasCustomsProcedure) {
            $searchCond .= " OR h.customs_procedure LIKE :s OR h.event_notes LIKE :s";
        }
        $searchCond .= ")";
        $where[] = $searchCond;
        $params[':s'] = '%' . $search . '%';
    }
    if ($from) { $where[] = "h.occurred_at >= :f"; $params[':f'] = $from . ' 00:00:00'; }
    if ($to) { $where[] = "h.occurred_at <= :t"; $params[':t'] = $to . ' 23:59:59'; }
    $sql .= $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $sql .= " ORDER BY h.occurred_at DESC, h.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage);
    $st = $db->prepare($sql);
    foreach ($params as $k => $v) { $st->bindValue($k, $v); }
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $adminIds = [];
    foreach ($rows as $r) {
        if (!empty($r['occurred_by'])) {
            $adminIds[(int)$r['occurred_by']] = true;
        }
    }
    $adminNames = [];
    if (!empty($adminIds)) {
        try {
            $placeholders = implode(',', array_fill(0, count($adminIds), '?'));
            $stmt = $db->prepare("SELECT id, full_name FROM manager_admin WHERE id IN ($placeholders)");
            $stmt->execute(array_keys($adminIds));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $admin) {
                $adminNames[(int)$admin['id']] = $admin['full_name'];
            }
        } catch (Exception $e) {
            error_log("tracking_history: manager_admin query failed: " . $e->getMessage());
        }
    }

    $shipmentIds = array_unique(array_filter(array_map(function($r) { return (int)($r['shipment_id'] ?? 0); }, $rows)));
    $shipmentCustomerMap = [];
    if (!empty($shipmentIds)) {
        $placeholders = implode(',', array_fill(0, count($shipmentIds), '?'));
        $stmt = $db->prepare("SELECT id, customer_id FROM shipments WHERE id IN ($placeholders)");
        $stmt->execute(array_values($shipmentIds));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $shipmentCustomerMap[(int)$s['id']] = (int)($s['customer_id'] ?? 0);
        }
    }

    $notifiedShipments = [];
    if (!empty($shipmentIds)) {
        $now = date('Y-m-d H:i:s', strtotime('-30 days'));
        $namedPlaceholders = [];
        $inParams = [':ts' => $now];
        foreach ($shipmentIds as $idx => $sid) {
            $name = ':uid' . $idx;
            $namedPlaceholders[] = $name;
            $inParams[$name] = $sid;
        }
        $stmt = $db->prepare("SELECT DISTINCT user_id FROM notifications WHERE created_at >= :ts AND user_id IN (" . implode(',', $namedPlaceholders) . ")");
        $stmt->execute($inParams);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $uid) {
            $notifiedShipments[(int)$uid] = true;
        }
    }

    foreach ($rows as &$r) {
        $by = $r['occurred_by'];
        $r['updated_by_name'] = $by ? ($adminNames[(int)$by] ?? '') : '';
        $sid = (int)($r['shipment_id'] ?? 0);
        $customerId = $shipmentCustomerMap[$sid] ?? 0;
        $r['notify'] = (!empty($notifiedShipments[$customerId])) ? 1 : 0;
    }
    unset($r);
}
$qs = http_build_query(array_filter(['id'=>$id,'search'=>$search,'status'=>$statusFilter,'from'=>$from,'to'=>$to], function($v) { return $v !== '' && $v !== null; }));
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php
$courierTimelineSteps = [
    'shipment_info_received' => 'Shipment Information Received',
    'package_received'       => 'Package Received',
    'export_doc_verified'    => 'Export Documentation Verified',
    'export_customs_cleared' => 'Export Customs Cleared',
    'departed_origin_airport'=> 'Departed Origin Airport',
    'international_transit'  => 'International Transit',
    'regional_transit_hub'   => 'Regional Transit Hub',
    'arrived_usa'            => 'Arrived in USA',
    'customs_inspection'     => 'Customs Inspection',
    'customs_cleared'        => 'Customs Cleared',
    'domestic_distribution'  => 'Domestic Distribution Center',
    'local_delivery'         => 'Local Delivery Facility',
    'out_for_delivery'       => 'Out for Delivery',
    'delivered'              => 'Delivered',
    'final_delivery'         => 'Final Delivery',
    'ready_for_delivery'     => 'Ready for Delivery',
    'delivery_completed'     => 'Delivery Completed',
    'released_domestic'      => 'Released for Domestic Transport',
    'transit_processing'     => 'Transit Processing',
    'awaiting_customs'       => 'Awaiting Customs Inspection',
];
$timelineSteps = $courierTimelineSteps;
$completedStepKeys = [];
if ($id && $shipment) {
    $sth = $db->prepare("SELECT DISTINCT customs_procedure FROM shipment_status_history_v2 WHERE shipment_id = :id AND customs_procedure IS NOT NULL AND customs_procedure != ''");
    $sth->execute([':id' => $id]);
    $procedureKeys = array_map('strval', $sth->fetchAll(PDO::FETCH_COLUMN));

    $sth2 = $db->prepare("SELECT DISTINCT status_code FROM shipment_status_history_v2 WHERE shipment_id = :id AND status_code IS NOT NULL AND status_code != ''");
    $sth2->execute([':id' => $id]);
    $statusCodes = $sth2->fetchAll(PDO::FETCH_COLUMN);

    $statusStepMap = [
        'picked_up' => 'package_received',
        'received_origin' => 'package_received',
        'in_transit' => 'departed_origin_airport',
        'at_hub' => 'regional_transit_hub',
        'customs_inspection' => 'customs_inspection',
        'customs_clearance' => 'customs_cleared',
        'out_for_delivery' => 'out_for_delivery',
        'delivered' => 'delivered',
        'sorted' => 'domestic_distribution',
        'at_warehouse' => 'export_warehouse',
        'pending' => 'shipment_info_received',
        'processing' => 'shipment_info_received',
        'created' => 'shipment_info_received',
        'pending_pickup' => 'shipment_info_received',
    ];

    $statusSteps = array_map(function($st) use ($statusStepMap) {
        $st = strtolower($st);
        return $statusStepMap[$st] ?? null;
    }, $statusCodes);

    $completedStepKeys = array_values(array_unique(array_filter(array_merge($procedureKeys, $statusSteps))));
    $currentStepKey = canonicalStepKey($shipment['status'] ?? '');
}
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Tracking History<?php echo $id ? ' — #' . $id : ''; ?></h4>
        <div class="d-flex gap-2">
            <?php if ($id && $shipment): ?>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal"><i class="bi bi-plus-circle"></i> Add Tracking Event</button>
            <?php endif; ?>
            <a href="?<?php echo $qs; ?>&export=pdf" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
            <a href="?<?php echo $qs; ?>&export=excel" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
            <a href="shipments.php" class="btn btn-light btn-sm">Close</a>
        </div>
    </div>

    <?php if ($id && $shipment): ?>
    <!-- ===================== Courier-style Timeline ===================== -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-diagram-3 me-2"></i>Shipment Progress</span>
            <small class="text-muted"><?php echo htmlspecialchars($shipment['tracking_number'] ?? ''); ?></small>
        </div>
        <div class="card-body">
            <div class="tracking-timeline">
                <?php foreach ($timelineSteps as $key => $label):
                    $isCompleted = in_array($key, $completedStepKeys, true);
                    $isCurrent = $currentStepKey === $key;
                    $stateClass = $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending');
                ?>
                <div class="timeline-step <?php echo $stateClass; ?>">
                    <div class="step-marker">
                        <?php if ($isCompleted): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php elseif ($isCurrent): ?>
                            <i class="bi bi-circle-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="step-label"><?php echo htmlspecialchars($label); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===================== Quick Add Tracking Event ===================== -->
    <?php if ($id && $shipment): ?>
    <form method="POST" class="card mb-3" id="quickAddForm">
        <div class="card-header py-2"><strong><i class="bi bi-plus-circle me-2"></i>Quick Add Tracking Event</strong></div>
        <div class="card-body row g-2 align-items-end">
            <input type="hidden" name="action" value="add_event">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="shipment_id" value="<?php echo (int)$shipment['id']; ?>">
            <div class="col-md-2">
                <label class="form-label small">Date</label>
                <input type="date" name="event_date" class="form-control form-control-sm" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Time</label>
                <input type="time" name="event_time" class="form-control form-control-sm" required value="<?php echo date('H:i'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="new_status" class="form-select form-select-sm" required>
                    <option value="">Select status</option>
                    <?php foreach (allShipmentStatuses() as $c => $l): ?>
                        <option value="<?php echo $c; ?>"><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Transit Location</label>
                <select name="location" class="form-select form-select-sm">
                    <option value="">— Select or type —</option>
                    <?php foreach (transitLocationOptions() as $val => $lbl): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="location_custom" class="form-control form-control-sm mt-1" placeholder="Or custom location">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Customs Procedure</label>
                <select name="customs_procedure" class="form-select form-select-sm">
                    <option value="">— Select or type —</option>
                    <?php foreach (customsProcedureOptions() as $val => $lbl): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="procedure_custom" class="form-control form-control-sm mt-1" placeholder="Or custom procedure">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tracking Update</label>
                <textarea name="remarks" class="form-control form-control-sm" rows="1" required placeholder="Describe the update..."></textarea>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Add Event</button>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- ===================== Search / Filters ===================== -->
    <form method="GET" class="card mb-3"><div class="card-body row g-2 align-items-end">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="col-md-3"><label class="form-label small">Search</label><input name="search" id="trackingSearch" class="form-control form-control-sm" value="<?php echo htmlspecialchars($search); ?>" placeholder="Remarks / location / customs procedure"></div>
        <div class="col-md-3"><label class="form-label small">Status</label><select name="status" id="trackingStatus" class="form-select form-select-sm">
            <option value="">All</option><?php foreach (allShipmentStatuses() as $c=>$l): ?><option value="<?php echo $c; ?>" <?php echo $statusFilter===$c?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" id="trackingFrom" class="form-control form-control-sm" value="<?php echo htmlspecialchars($from); ?>"></div>
        <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" id="trackingTo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to); ?>"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary btn-sm">Filter</button></div>
    </form>

    <!-- ===================== Shipment Travel History ===================== -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-airplane me-2"></i>Shipment Travel History <span class="badge bg-secondary" id="eventCount"><?php echo (int)$total; ?></span></span>
            <?php if($superAdmin): ?><span class="badge bg-warning text-dark">Super Admin: delete enabled</span><?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle tracking-table" id="trackingTable">
                    <caption class="visually-hidden">Shipment Travel History — tracking events in reverse chronological order</caption>
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-col" scope="col">Date &amp; Time</th>
                            <th scope="col">Transit Location</th>
                            <th scope="col">Customs Procedure</th>
                            <th scope="col">Tracking Update</th>
                            <th class="text-end" scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No tracking events found.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <?php
                            $occurred = strtotime($r['occurred_at']);
                            $dateStr = date('M d, Y', $occurred);
                            $timeStr = date('H:i', $occurred);
                            $statusLabelText = statusLabel($r['status_code']);
                            $statusBadgeClass = statusBadgeClass($r['status_code']);
                            $locationDisplay = $r['location'] ?: 'N/A';
                            $procedureDisplay = ($r['customs_procedure'] ?? '') ? canonicalStepLabel($r['customs_procedure'] ?? '') : '—';
                            $remarksDisplay = $r['remarks'] ?: '—';
                            $transitDisplay = $locationDisplay !== 'N/A' ? $statusLabelText . ' – ' . $locationDisplay : $statusLabelText;
                        ?>
                        <tr class="tracking-row" data-event-id="<?php echo (int)$r['id']; ?>" data-status="<?php echo htmlspecialchars($r['status_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-occurred-at="<?php echo htmlspecialchars($r['occurred_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="sticky-col">
                                <?php
                                    $sc = strtolower($r['status_code'] ?? '');
                                    $iconClass = 'info-circle-fill text-info';
                                    if ($sc === 'delivered') $iconClass = 'check-circle-fill text-success';
                                    elseif (in_array($sc, ['in_transit', 'picked_up', 'out_for_delivery'])) $iconClass = 'truck text-primary';
                                    elseif (in_array($sc, ['pending', 'processing'])) $iconClass = 'clock text-warning';
                                    elseif (in_array($sc, ['cancelled', 'returned'])) $iconClass = 'x-circle-fill text-danger';
                                    elseif (in_array($sc, ['held', 'customs_hold'])) $iconClass = 'pause-circle-fill text-warning';
                                    elseif ($sc === 'customs_seized') $iconClass = 'exclamation-triangle-fill text-danger';
                                ?>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-<?php echo $iconClass; ?>" aria-hidden="true"></i>
                                    <div class="fw-bold"><?php echo htmlspecialchars($dateStr); ?></div>
                                </div>
                                <div class="text-muted small"><?php echo htmlspecialchars($timeStr); ?></div>
                                <div class="mt-1"><?php echo '<span class="badge ' . $statusBadgeClass . '">' . htmlspecialchars($statusLabelText) . '</span>'; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($transitDisplay); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($procedureDisplay); ?></span></td>
                            <td class="tracking-update-cell"><?php echo nl2br(htmlspecialchars($remarksDisplay)); ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php if ($superAdmin): ?>
                                    <a href="?<?php echo $qs; ?>&reorder_event=<?php echo $r['id']; ?>&direction=up&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-outline-secondary btn-icon" onclick="return confirm('Move this event earlier?');" title="Move earlier" aria-label="Move event earlier"><i class="bi bi-arrow-up"></i></a>
                                    <a href="?<?php echo $qs; ?>&reorder_event=<?php echo $r['id']; ?>&direction=down&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-outline-secondary btn-icon" onclick="return confirm('Move this event later?');" title="Move later" aria-label="Move event later"><i class="bi bi-arrow-down"></i></a>
                                    <?php endif; ?>
                                     <button type="button" class="btn btn-sm btn-outline-primary btn-icon edit-event-btn" data-id="<?php echo (int)$r['id']; ?>" data-shipment-id="<?php echo (int)$r['shipment_id']; ?>" data-status="<?php echo htmlspecialchars($r['status_code'], ENT_QUOTES, 'UTF-8'); ?>" data-location="<?php echo htmlspecialchars($r['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-procedure="<?php echo htmlspecialchars($r['customs_procedure'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-remarks="<?php echo htmlspecialchars($r['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-event-notes="<?php echo htmlspecialchars($r['event_notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-occurred-at="<?php echo htmlspecialchars($r['occurred_at'], ENT_QUOTES, 'UTF-8'); ?>" data-operator="<?php echo htmlspecialchars($r['occurred_by'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Edit event" aria-label="Edit tracking event">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($superAdmin): ?>
                                    <a href="?<?php echo $qs; ?>&delete_event=<?php echo $r['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-sm btn-outline-danger btn-icon" onclick="return confirm('Delete this event?');" title="Delete event" aria-label="Delete tracking event">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr class="tracking-row-details" data-event-id="<?php echo (int)$r['id']; ?>" style="display:none;">
                            <td colspan="5" class="p-0 border-0">
                                <div class="tracking-details-panel">
                                    <div class="row g-3">
                                        <div class="col-md-4"><strong>Event ID:</strong> #<?php echo (int)$r['id']; ?></div>
                                        <div class="col-md-4"><strong>Status Code:</strong> <code><?php echo htmlspecialchars($r['status_code']); ?></code></div>
                                        <div class="col-md-4"><strong>Occurred At:</strong> <?php echo htmlspecialchars($r['occurred_at']); ?></div>
                                        <div class="col-md-4"><strong>Location Raw:</strong> <?php echo htmlspecialchars($r['location'] ?? ''); ?></div>
                                        <div class="col-md-4"><strong>Procedure Raw:</strong> <?php echo htmlspecialchars($r['customs_procedure'] ?? ''); ?></div>
                                        <div class="col-md-4"><strong>Event Notes Raw:</strong> <?php echo htmlspecialchars($r['event_notes'] ?? ''); ?></div>
                                        <div class="col-12"><strong>Remarks Raw:</strong><br><?php echo nl2br(htmlspecialchars($r['remarks'] ?? '')); ?></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3"><ul class="pagination justify-content-center">
                <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $page-1; ?>">Prev</a></li><?php endif; ?>
                <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?><li class="page-item <?php echo $i===$page?'active':''; ?>"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                <?php if ($page < $totalPages): ?><li class="page-item"><a class="page-link" href="?<?php echo $qs; ?>&page=<?php echo $page+1; ?>">Next</a></li><?php endif; ?>
            </ul></nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($id && $shipment): ?>
<!-- ===================== Add / Edit Tracking Event Modal ===================== -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content" id="eventForm">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle"><i class="bi bi-plus-circle me-2"></i>Add Tracking Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="eventAction" value="add_event">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="shipment_id" value="<?php echo (int)$shipment['id']; ?>">
                <input type="hidden" name="event_id" id="eventId" value="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="event_date" id="eventDate" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time</label>
                        <input type="time" name="event_time" id="eventTime" class="form-control" required value="<?php echo date('H:i'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shipment Status</label>
                        <select name="new_status" id="eventStatus" class="form-select" required><?php foreach (allShipmentStatuses() as $c => $l): ?><option value="<?php echo $c; ?>"><?php echo $l; ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transit Location</label>
                        <select name="location" id="eventLocation" class="form-select">
                            <option value="">— Select or type custom —</option>
                            <?php foreach (transitLocationOptions() as $val => $lbl): ?>
                                <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="location_custom" id="eventLocationCustom" class="form-control mt-2" placeholder="Or enter custom location">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customs Procedure</label>
                        <select name="customs_procedure" id="eventProcedure" class="form-select">
                            <option value="">— Select or type custom —</option>
                            <?php foreach (customsProcedureOptions() as $val => $lbl): ?>
                                <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="procedure_custom" id="eventProcedureCustom" class="form-control mt-2" placeholder="Or enter custom procedure">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tracking Update</label>
                        <textarea name="remarks" id="eventRemarks" class="form-control" rows="3" required placeholder="Describe the tracking update, delivery status, or transit activity..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Event Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="event_notes" id="eventNotes" class="form-control" rows="2" placeholder="Internal notes, delay reasons, handling details..."></textarea>
                    </div>
                </div>
                <div class="alert alert-light small mt-3 mb-0">Saving will update the shipment status, append a tracking event, and refresh the progress timeline.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="eventSubmitBtn"><i class="bi bi-check-lg"></i> Save Event</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    .no-print,
    .btn,
    .page-header,
    footer,
    nav,
    .sidebar {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    .tracking-table thead th {
        position: static;
        background: #fff !important;
        color: #000 !important;
    }
    .tracking-table .sticky-col {
        position: static;
        background: #fff !important;
    }
    [data-theme="dark"] .tracking-table thead th,
    [data-theme="dark"] .tracking-table .sticky-col {
        background: #fff !important;
        color: #000 !important;
    }
    .tracking-row-details {
        display: none !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
}
</style>
<script>
(function() {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const title = document.getElementById('eventModalTitle');
    const actionInput = document.getElementById('eventAction');
    const eventIdInput = document.getElementById('eventId');
    const dateInput = document.getElementById('eventDate');
    const timeInput = document.getElementById('eventTime');
    const statusInput = document.getElementById('eventStatus');
    const locationSelect = document.getElementById('eventLocation');
    const locationCustom = document.getElementById('eventLocationCustom');
    const procedureSelect = document.getElementById('eventProcedure');
    const procedureCustom = document.getElementById('eventProcedureCustom');
    const remarksInput = document.getElementById('eventRemarks');
    const notesInput = document.getElementById('eventNotes');
    const submitBtn = document.getElementById('eventSubmitBtn');

    function resetForm() {
        form.reset();
        eventIdInput.value = '';
        actionInput.value = 'add_event';
        title.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Tracking Event';
        submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save Event';
        submitBtn.classList.remove('btn-warning');
        submitBtn.classList.add('btn-primary');
        dateInput.value = '<?php echo date('Y-m-d'); ?>';
        timeInput.value = '<?php echo date('H:i'); ?>';
        locationCustom.value = '';
        procedureCustom.value = '';
    }

    document.querySelectorAll('.edit-event-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            eventIdInput.value = this.getAttribute('data-id') || '';
            statusInput.value = this.getAttribute('data-status') || '';
            const loc = this.getAttribute('data-location') || '';
            const proc = this.getAttribute('data-procedure') || '';
            const rem = this.getAttribute('data-remarks') || '';
            const notes = this.getAttribute('data-event-notes') || '';
            const occurredAt = this.getAttribute('data-occurred-at') || '';

            locationSelect.value = '';
            locationCustom.value = loc;
            procedureSelect.value = '';
            procedureCustom.value = proc;
            remarksInput.value = rem;
            notesInput.value = notes;

            if (occurredAt) {
                const dt = new Date(occurredAt.replace(' ', 'T'));
                if (!isNaN(dt)) {
                    dateInput.value = dt.toISOString().slice(0, 10);
                    timeInput.value = dt.toTimeString().slice(0, 5);
                }
            }

            actionInput.value = 'edit_event';
            title.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Tracking Event';
            submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Update Event';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-warning');

            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) { bsModal.hide(); }
            setTimeout(function() {
                const m = new bootstrap.Modal(modal);
                m.show();
            }, 300);
        });
    });

    modal.addEventListener('hidden.bs.modal', resetForm);

    if (locationCustom) {
        locationCustom.addEventListener('input', function() {
            if (this.value.trim() !== '') { locationSelect.value = ''; }
        });
    }
    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            if (this.value.trim() !== '') { locationCustom.value = ''; }
        });
    }
    if (procedureCustom) {
        procedureCustom.addEventListener('input', function() {
            if (this.value.trim() !== '') { procedureSelect.value = ''; }
        });
    }
    if (procedureSelect) {
        procedureSelect.addEventListener('change', function() {
            if (this.value.trim() !== '') { procedureCustom.value = ''; }
        });
    }

    const searchInput = document.getElementById('trackingSearch');
    const filterStatus = document.getElementById('trackingStatus');
    const filterFrom = document.getElementById('trackingFrom');
    const filterTo = document.getElementById('trackingTo');
    const tableBody = document.querySelector('#trackingTable tbody');

    function filterTable() {
        const q = (searchInput ? searchInput.value : '').toLowerCase();
        const st = filterStatus ? filterStatus.value : '';
        const f = filterFrom ? filterFrom.value : '';
        const t = filterTo ? filterTo.value : '';
        const rows = tableBody ? tableBody.querySelectorAll('tr.tracking-row') : [];
        let visibleCount = 0;
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const dateAttr = row.getAttribute('data-occurred-at') || '';
            const matchSearch = !q || text.includes(q) || dateAttr.includes(q);
            const rowStatus = row.getAttribute('data-status') || '';
            const matchStatus = !st || rowStatus === st;
            const rowDate = dateAttr.slice(0, 10);
            const matchFrom = !f || rowDate >= f;
            const matchTo = !t || rowDate <= t;
            const show = matchSearch && matchStatus && matchFrom && matchTo;
            row.style.display = show ? '' : 'none';
            const eid = row.getAttribute('data-event-id');
            if (eid) {
                const details = tableBody.querySelector('.tracking-row-details[data-event-id="' + eid + '"]');
                if (details) details.style.display = show ? '' : 'none';
            }
            if (show) visibleCount++;
        });
        const countEl = document.getElementById('eventCount');
        if (countEl) {
            countEl.textContent = visibleCount;
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (filterStatus) filterStatus.addEventListener('change', filterTable);
    if (filterFrom) filterFrom.addEventListener('change', filterTable);
    if (filterTo) filterTo.addEventListener('change', filterTable);

    document.querySelectorAll('.tracking-row').forEach(function(row) {
        row.style.cursor = 'pointer';
        row.setAttribute('role', 'button');
        row.setAttribute('tabindex', '0');
        row.setAttribute('aria-expanded', 'false');
        row.addEventListener('click', function(e) {
            if (e.target.closest('a, button, select, input, textarea')) return;
            const eid = this.getAttribute('data-event-id');
            const details = document.querySelector('.tracking-row-details[data-event-id="' + eid + '"]');
            if (details) {
                const isHidden = details.style.display === 'none';
                details.style.display = isHidden ? 'table-row' : 'none';
                this.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                const panel = details.querySelector('.tracking-details-panel');
                if (panel) {
                    panel.style.opacity = isHidden ? '1' : '0';
                    panel.style.transform = isHidden ? 'translateY(0)' : 'translateY(-4px)';
                }
            }
        });
        row.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
        });
    });

    document.querySelectorAll('.tracking-row-details').forEach(function(row) {
        const panel = row.querySelector('.tracking-details-panel');
        if (panel) {
            panel.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(-4px)';
        }
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
