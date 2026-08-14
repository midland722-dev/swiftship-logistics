<?php
/**
 * Update Tracking — SEPARATE module from Edit Shipment.
 *
 * Every submission APPENDS a new tracking event to the shipment timeline
 * (shipment_status_history_v2 + tracking_history). It never overwrites or
 * deletes previous events. The shipment's current status is updated to reflect
 * the latest event, but historical entries remain immutable (Super Admin may
 * delete an entry for correction).
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';

$page_title = 'Update Tracking - ' . SITE_NAME;
$db = getDB();
requirePermission('update_tracking');
ensureShipmentColumns($db);
ensureCourierTables($db);
ensureShipmentStatusEnum($db);
ensureTrackingHistory($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) {
    echo '<div class="alert alert-danger m-4">Shipment not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$GLOBALS['shipment'] = $shipment;

$superAdmin = isSuperAdmin();
$message = '';
$message_type = '';

// Load carrier tracking info for this shipment.
$carrierInfo = null;
try {
    require_once __DIR__ . '/../includes/carrier_tracking.php';
    $carrierInfo = getCarrierTrackingInfo($db, $id);
} catch (Exception $e) { /* module may not be installed yet */ }

// Delete a tracking event (Super Admin only).
if ($superAdmin && isset($_GET['delete_event']) && isset($_GET['csrf'])) {
    if (hash_equals($_SESSION['csrf_token'] ?? '', (string)$_GET['csrf'])) {
        $eid = intval($_GET['delete_event']);
        $db->prepare("DELETE FROM shipment_status_history_v2 WHERE id = :id AND shipment_id = :sid")
            ->execute([':id' => $eid, ':sid' => $id]);
        $db->prepare("DELETE FROM tracking_history WHERE id = :id AND shipment_id = :sid")
            ->execute([':id' => $eid, ':sid' => $id]);
        $message = 'Tracking event removed (Super Admin).';
        $message_type = 'success';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } else {
        $status           = trim($_POST['status'] ?? '');
        $location         = trim($_POST['location'] ?? '');
        $country          = trim($_POST['country'] ?? '');
        $state            = trim($_POST['state'] ?? '');
        $city             = trim($_POST['city'] ?? '');
        $facility         = trim($_POST['facility'] ?? '');
        $date             = trim($_POST['event_date'] ?? '');
        $time             = trim($_POST['event_time'] ?? '');
        $remarks          = trim($_POST['remarks'] ?? '');
        $notify           = !empty($_POST['notify_customer']);
        $transitLocation  = trim($_POST['transit_location'] ?? '');
        $customsProcedure = trim($_POST['customs_procedure'] ?? '');

        if (!array_key_exists($status, commonTrackingStatuses())) {
            $message = 'Please select a valid tracking status.';
            $message_type = 'danger';
        } else {
            $validationErrors = validateTrackingUpdate($status, $transitLocation, $customsProcedure);
            if (!empty($validationErrors)) {
                $message = implode(' ', $validationErrors);
                $message_type = 'danger';
            } else {
                $occurred = '';
                if ($date !== '') {
                    $occurred = $date . ($time !== '' ? ' ' . $time : ' 00:00:00');
                } else {
                    $occurred = date('Y-m-d H:i:s');
                }

                // Build a human-readable location from the structured fields.
                $locParts = array_filter([$facility, $city, $state, $country]);
                if ($location === '' && !empty($locParts)) {
                    $location = implode(', ', $locParts);
                }

                $adminName = $_SESSION['admin_name'] ?? 'Admin';
                $adminId   = $_SESSION['admin_id'] ?? null;

                try {
                    $db->beginTransaction();

                    // Append to canonical admin history (never overwrites prior rows).
                    $db->prepare("
                        INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, customs_procedure, event_notes, occurred_by)
                        VALUES (:sid, :s, :at, :l, :r, :cp, :en, :by)
                    ")->execute([
                        ':sid' => $id, ':s' => $status, ':at' => $occurred,
                        ':l' => $location ?: null, ':r' => $remarks ?: null,
                        ':cp' => $customsProcedure ?: null, ':en' => null,
                        ':by' => $adminId,
                    ]);

                    // Append to public tracking store (used by customer page / API).
                    $db->prepare("
                        INSERT INTO tracking_history (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at, transit_location, customs_procedure)
                        VALUES (:sid, :tn, :s, :l, :d, :at, :by, NOW(), :tl, :cp)
                    ")->execute([
                        ':sid' => $id, ':tn' => $shipment['tracking_number'], ':s' => $status,
                        ':l' => $location ?: null, ':d' => $remarks ?: null, ':at' => $occurred, ':by' => $adminName,
                        ':tl' => $transitLocation ?: null, ':cp' => $customsProcedure ?: null,
                    ]);

                    // Update the shipment's current status + location + transit/procedure (latest wins).
                    $upd = "UPDATE shipments SET status = :s, updated_at = NOW()";
                    $params = [':s' => $status, ':id' => $id];
                    if ($city !== '') { $upd .= ", current_city = :cc"; $params[':cc'] = $city; }
                    if ($country !== '') { $upd .= ", current_country = :co"; $params[':co'] = $country; }
                    if ($transitLocation !== '') { $upd .= ", transit_location = :tl"; $params[':tl'] = $transitLocation; }
                    if ($customsProcedure !== '') { $upd .= ", customs_procedure = :cp"; $params[':cp'] = $customsProcedure; }
                    $upd .= " WHERE id = :id";
                    $db->prepare($upd)->execute($params);

                    $db->commit();
                    clearDashboardCache();

                    if ($notify) {
                        notifyTrackingEvent($db, $id, $shipment['tracking_number'], $status, $location, $remarks ?: statusLabel($status));
                    } else {
                        // Always keep an in-app customer notification in sync (no email).
                        try {
                            $db->prepare("INSERT INTO notifications (user_id, type, title, message, action_url, icon, created_at)
                                VALUES (:uid, :type, :title, :msg, :url, 'box', NOW())")
                                ->execute([
                                    ':uid' => $shipment['customer_id'] ?? null,
                                    ':type' => trackingNotificationType($status),
                                    ':title' => 'Shipment ' . $shipment['tracking_number'] . ' — ' . statusLabel($status),
                                    ':msg' => ($location ? 'Location: ' . $location . '. ' : '') . ($remarks ?: statusLabel($status)),
                                    ':url' => '/shp/track.php?cons_no=' . urlencode($shipment['tracking_number']),
                                ]);
                        } catch (Exception $e) { /* ignore */ }
                    }

                    $message = 'Tracking event added to timeline.';
                    $message_type = 'success';
                    // Reload so the timeline reflects the new event.
                    $stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
                    $stmt->execute([':id'=>$id]);
                    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Timeline (chronological, oldest first for display; newest highlighted).
$history = $db->prepare("SELECT * FROM shipment_status_history_v2 WHERE shipment_id = :id ORDER BY occurred_at ASC, id ASC")
    ->fetchAll(PDO::FETCH_ASSOC);
$nowDate = date('Y-m-d');
$nowTime = date('H:i');
$v = function($k, $d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
?>
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show m-3" role="alert">
    <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            <span class="ms-2 fw-semibold"><?php echo $v('tracking_number'); ?></span>
            <?php echo statusBadge($shipment['status']); ?>
        </div>
        <a href="edit_shipment.php?id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Shipment Info</a>
    </div>

    <div class="row g-4">
        <!-- Update form -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Add Tracking Event</div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Tracking Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="">— Select status —</option>
                                <?php foreach (commonTrackingStatuses() as $c => $l): ?>
                                    <option value="<?php echo $c; ?>" <?php echo ($shipment['status']===$c?'selected':''); ?>><?php echo $l; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">Date</label><input type="date" name="event_date" class="form-control" value="<?php echo $nowDate; ?>" required></div>
                            <div class="col-6"><label class="form-label">Time</label><input type="time" name="event_time" class="form-control" value="<?php echo $nowTime; ?>" required></div>
                        </div>
                        <div class="mb-3 mt-3"><label class="form-label">Facility or Hub</label><input name="facility" class="form-control" placeholder="e.g. Chicago Distribution Center"></div>
                        <div class="row g-2">
                            <div class="col"><label class="form-label">City</label><input name="city" class="form-control" value="<?php echo $v('current_city'); ?>"></div>
                            <div class="col"><label class="form-label">State/Province</label><input name="state" class="form-control"></div>
                        </div>
                        <div class="mb-3 mt-3"><label class="form-label">Country</label><input name="country" class="form-control" value="<?php echo $v('current_country'); ?>"></div>
                        <div class="mb-3"><label class="form-label">Current Location (override)</label><input name="location" class="form-control" placeholder="Leave blank to auto-build from above"></div>
                        <div class="row g-2">
                            <div class="col">
                                <label class="form-label">Transit Location</label>
                                <select name="transit_location" class="form-select">
                                    <option value="">— Optional —</option>
                                    <?php foreach (transitLocationOptions() as $c => $l): ?>
                                        <option value="<?php echo $c; ?>"><?php echo $l; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Customs Procedure (USA)</label>
                                <select name="customs_procedure" class="form-select">
                                    <option value="">— Optional —</option>
                                    <?php foreach (customsProcedureOptions() as $c => $l): ?>
                                        <option value="<?php echo $c; ?>"><?php echo $l; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3" placeholder="Optional details about this scan..."></textarea></div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="notify_customer" id="nc" checked>
                            <label class="form-check-label" for="nc">Notify customer (email + in-app)</label>
                        </div>
                        <div class="alert alert-light small mb-3">Updated by: <strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></strong> (automatic)</div>
                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Append this tracking event to the timeline?');"><i class="bi bi-plus-lg"></i> Add Tracking Event</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Carrier Sync Panel -->
        <div class="col-lg-5">
            <div class="card border-info">
                <div class="card-header bg-info text-white"><i class="bi bi-truck me-2"></i>Carrier Tracking</div>
                <div class="card-body">
                    <?php if ($carrierInfo && !empty($carrierInfo['carrier_tracking_number'])): ?>
                        <div class="mb-3">
                            <strong>Carrier:</strong> <?php echo htmlspecialchars($carrierInfo['provider'] ?? $carrierInfo['carrier_name'] ?? 'N/A'); ?><br>
                            <strong>Tracking #:</strong> <code><?php echo htmlspecialchars($carrierInfo['carrier_tracking_number']); ?></code><br>
                            <strong>Last Sync:</strong> <?php echo $carrierInfo['last_carrier_sync_at'] ? date('M d, Y H:i', strtotime($carrierInfo['last_carrier_sync_at'])) : '<span class="text-warning">Never</span>'; ?>
                        </div>
                        <form method="POST" action="../carrier_tracking.php" onsubmit="return confirm('Sync tracking from carrier?');">
                            <input type="hidden" name="action" value="sync_carrier">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="shipment_id" value="<?php echo $id; ?>">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-arrow-clockwise"></i> Sync from Carrier
                            </button>
                        </form>
                        <form method="POST" action="../carrier_tracking.php" class="mt-2" onsubmit="return confirm('Unlink carrier tracking?');">
                            <input type="hidden" name="action" value="unlink_carrier">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="shipment_id" value="<?php echo $id; ?>">
                            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                <i class="bi bi-link-45deg"></i> Unlink Carrier
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted mb-2">No carrier tracking linked to this shipment.</p>
                        <a href="carrier_tracking.php" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-truck"></i> Go to Carrier Tracking
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-2"></i>Tracking Timeline (<?php echo count($history); ?>)</span>
                    <?php if ($superAdmin): ?><span class="badge bg-warning text-dark">Super Admin: can delete</span><?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($history)): ?>
                        <p class="text-muted">No tracking events yet.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $i => $r): ?>
                                <div class="timeline-item <?php echo $i === count($history) - 1 ? 'active' : ''; ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars(statusLabel($r['status_code'])); ?></h6>
                                            <?php if ($superAdmin): ?>
                                                <a href="update_tracking.php?id=<?php echo $id; ?>&delete_event=<?php echo $r['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-sm btn-outline-danger py-0" onclick="return confirm('Delete this tracking event? This cannot be undone.');" title="Delete event (Super Admin)"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($r['remarks'] ?? ''); ?></p>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($r['occurred_at'])); ?>
                                            <?php if (!empty($r['location'])): ?> — <?php echo htmlspecialchars($r['location']); ?><?php endif; ?>
                                            <?php
                                            $by = $r['occurred_by'];
                                            if ($by) {
                                                $name = $by;
                                                try { $stmt = $db->prepare("SELECT full_name FROM manager_admin WHERE id = :id LIMIT 1"); $stmt->execute([':id'=>$by]); if ($u = $stmt->fetch(PDO::FETCH_ASSOC)) $name = $u['full_name']; } catch (Exception $e) {}
                                                echo ' · by ' . htmlspecialchars($name);
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$shipment = $GLOBALS['shipment'];
require_once __DIR__ . '/includes/quick_actions.php';
require_once __DIR__ . '/includes/footer.php'; ?>
