<?php
/**
 * Shipment Simulation Administration Page
 * ---------------------------------------
 * Provides UI for generating sample shipments, running simulations,
 * and managing the simulation engine.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/includes/carrier_tracking.php';
require_once __DIR__ . '/includes/tracking.php';
require_once __DIR__ . '/includes/Logger.php';
require_once __DIR__ . '/lib/ShipmentGenerator.php';
require_once __DIR__ . '/lib/SampleShipmentGenerator.php';
require_once __DIR__ . '/lib/SimulationEngine.php';
require_once __DIR__ . '/../includes/shipment_status.php';

if (!function_exists('validateAdmin')) {
    function validateAdmin() {
        if (empty($_SESSION['admin_id'])) {
            safeRedirect('Authentication required.');
        }
    }
}
function safeRedirect($message) {
    $_SESSION['error'] = $message;
    header('Location: login.php');
    exit;
}

validateAdmin();
$db = getDB();
ensureShipmentColumns($db);
ensureTrackingHistory($db);
ensureShipmentStatusEnum($db);

$action = $_GET['action'] ?? $_POST['action'] ?? 'dashboard';
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    }
}

if ($error !== '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF or validation error — stop action handlers from running
} elseif ($action === 'generate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $count = isset($_POST['count']) ? min(max((int)$_POST['count'], 1), 100) : 1;
        $autoSimulate = isset($_POST['auto_simulate']);
        $simulateFull = isset($_POST['simulate_full']);

        $generator = new SampleShipmentGenerator($db);
        $shipmentGenerator = new ShipmentGenerator($db);
        $created = 0;
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $data = $generator->generateSingle();
                $validation = validateShipmentData($data);
                if (!$validation['valid']) {
                    $errors[] = 'Attempt ' . ($i + 1) . ': ' . implode('; ', $validation['errors']);
                    continue;
                }
                $data = $validation['data'];
                $trackingValidation = validateTrackingNumber($data['tracking_number'], $db);
                if (!$trackingValidation['valid']) {
                    $data['tracking_number'] = $generator->generateTrackingNumber();
                }

                $columns = [];
                $placeholders = [];
                $values = [];
                foreach ($data as $col => $val) {
                    $columns[] = $col;
                    $placeholders[] = ':' . $col;
                    $values[':' . $col] = $val;
                }
                $columns[] = 'created_by';
                $placeholders[] = ':created_by';
                $values[':created_by'] = $_SESSION['admin_id'] ?? 0;

                $sql = "INSERT INTO shipments (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $db->prepare($sql);
                $stmt->execute($values);
                $shipmentId = (int)$db->lastInsertId();

                $trackingNumber = $data['tracking_number'];

                $initialStatus = $data['status'] ?? 'created';
                $initialDesc = $initialStatus === 'created' ? 'Shipment created via simulation' : ('Shipment generated via simulation — initial status: ' . $initialStatus);
                $initialTs = ($data['shipment_date'] ?? date('Y-m-d')) . ' 09:00:00';

                $db->prepare("
                    INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                    VALUES (:sid, :s, :ts, :loc, :r, :by)
                ")->execute([
                    ':sid' => $shipmentId,
                    ':s' => $initialStatus,
                    ':ts' => $initialTs,
                    ':loc' => ($data['origin_city'] ?? '') . ' (Order Received)',
                    ':r' => $initialDesc,
                    ':by' => 'Sample Generator',
                ]);

                $db->prepare("
                    INSERT INTO tracking_history (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
                    VALUES (:sid, :tn, :s, :loc, :desc, :ts, :by, NOW())
                ")->execute([
                    ':sid' => $shipmentId,
                    ':tn' => $trackingNumber,
                    ':s' => $initialStatus,
                    ':loc' => ($data['origin_city'] ?? '') . ' (Order Received)',
                    ':desc' => $initialDesc,
                    ':ts' => $initialTs,
                    ':by' => 'Sample Generator',
                ]);

                try {
                    $barcodePath = $shipmentGenerator->generateBarcode($trackingNumber);
                    $qrPath = $shipmentGenerator->generateQRCode($trackingNumber);
                    $db->prepare("UPDATE shipments SET barcode_path = :b, qr_code_path = :q WHERE id = :id")
                        ->execute([':b' => $barcodePath, ':q' => $qrPath, ':id' => $shipmentId]);
                } catch (Exception $e) { /* non-critical */ }

                try {
                    $stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $shipmentId]);
                    $shipmentRow = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($shipmentRow) {
                        $pdfPath = $shipmentGenerator->generatePDFReceipt($shipmentRow);
                        if ($pdfPath && file_exists($pdfPath)) {
                            $db->prepare("UPDATE shipments SET pdf_receipt_path = :p WHERE id = :id")
                                ->execute([':p' => $pdfPath, ':id' => $shipmentId]);
                        }
                    }
                } catch (Exception $e) { /* non-critical */ }

                logShipmentAction($db, 'shipment_created', $shipmentId, 'Sample shipment generated: ' . $trackingNumber, [
                    'generator' => 'SampleShipmentGenerator',
                    'count' => $count,
                ]);

                if ($autoSimulate || $simulateFull) {
                    $engine = new SimulationEngine($db);
                    if ($simulateFull) {
                        $results = $engine->simulateFull($shipmentId);
                    } else {
                        $results = [];
                        $steps = random_int(1, 3);
                        for ($s = 0; $s < $steps; $s++) {
                            $results[] = $engine->progressShipment($shipmentId);
                        }
                    }
                }

                $created++;
            } catch (Exception $e) {
                $errors[] = 'Attempt ' . ($i + 1) . ': ' . $e->getMessage();
                $logger = null;
                try { $logger = getLogger(); } catch (Exception $e2) { /* ignore */ }
                if ($logger) {
                    $logger->error('Sample shipment generation failed', [
                        'attempt' => $i + 1,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($created > 0) {
            $_SESSION['message'] = "Successfully created {$created} shipment(s)." . ($errors ? ' ' . count($errors) . ' errors occurred.' : '');
        } else {
            $_SESSION['error'] = 'No shipments created. ' . ($errors ? implode(' ', $errors) : 'Unknown error.');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Generation failed: ' . $e->getMessage();
        $logger = null;
        try { $logger = getLogger(); } catch (Exception $e2) { /* ignore */ }
        if ($logger) {
            $logger->error('Sample shipment generation batch failed', ['error' => $e->getMessage()]);
        }
    }
    header('Location: ' . str_replace(['&action=generate','?action=generate'], '', $_SERVER['REQUEST_URI']));
    exit;
}

elseif ($action === 'simulate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $shipmentId = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
        $mode = $_POST['mode'] ?? 'single';
        $engine = new SimulationEngine($db);

        if ($mode === 'full' && $shipmentId > 0) {
            $results = $engine->simulateFull($shipmentId);
            $_SESSION['message'] = "Full simulation completed. " . count($results) . " status updates applied.";
        } elseif ($mode === 'single' && $shipmentId > 0) {
            $result = $engine->progressShipment($shipmentId);
            $_SESSION['message'] = $result['message'];
            if (!$result['success']) {
                $_SESSION['error'] = $result['message'];
            }
        } elseif ($mode === 'batch') {
            $max = isset($_POST['batch_max']) ? (int)$_POST['batch_max'] : 20;
            $stats = $engine->runBatch($max);
            $_SESSION['message'] = "Batch complete: {$stats['progressed']} progressed, {$stats['terminal']} reached terminal, {$stats['errors']} errors.";
        } else {
            $_SESSION['error'] = 'Invalid simulation parameters.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Simulation failed: ' . $e->getMessage();
    }
    header('Location: ' . str_replace(['&action=simulate','?action=simulate'], '', $_SERVER['REQUEST_URI']));
    exit;
}

elseif ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $shipmentId = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
        if ($shipmentId > 0) {
            $engine = new SimulationEngine($db);
            $result = $engine->resetShipment($shipmentId);
            $_SESSION['message'] = $result['message'];
            if (!$result['success']) {
                $_SESSION['error'] = $result['message'];
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Reset failed: ' . $e->getMessage();
    }
    header('Location: ' . str_replace(['&action=reset','?action=reset'], '', $_SERVER['REQUEST_URI']));
    exit;
}

elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $shipmentId = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
        if ($shipmentId > 0) {
            $stmt = $db->prepare("SELECT tracking_number FROM shipments WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $shipmentId]);
            $trackingNumber = $stmt->fetchColumn();

            $db->prepare("DELETE FROM tracking_history WHERE shipment_id = :sid")->execute([':sid' => $shipmentId]);
            $db->prepare("DELETE FROM shipment_status_history_v2 WHERE shipment_id = :sid")->execute([':sid' => $shipmentId]);
            $db->prepare("DELETE FROM packages WHERE shipment_id = :sid")->execute([':sid' => $shipmentId]);
            $db->prepare("DELETE FROM carrier_tracking_events WHERE shipment_id = :sid")->execute([':sid' => $shipmentId]);
            $db->prepare("DELETE FROM shipments WHERE id = :id")->execute([':id' => $shipmentId]);

            logShipmentAction($db, 'shipment_delete', $shipmentId, 'Sample shipment deleted: ' . ($trackingNumber ?: 'unknown'));

            $_SESSION['message'] = 'Shipment deleted successfully.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Delete failed: ' . $e->getMessage();
    }
    header('Location: ' . str_replace(['&action=delete','?action=delete'], '', $_SERVER['REQUEST_URI']));
    exit;
}

$recentShipments = [];
try {
    $stmt = $db->prepare("
        SELECT id, tracking_number, status, current_city, origin_city, destination_city,
               sender_name, receiver_name, total_weight, service_type, created_at
        FROM shipments
        WHERE is_active = 1
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $recentShipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Failed to load shipments: ' . $e->getMessage();
}

$stats = ['total' => 0, 'active' => 0, 'delivered' => 0, 'in_transit' => 0];
try {
    $stats['total'] = (int)$db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
    $stats['active'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE is_active = 1")->fetchColumn();
    $stats['delivered'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status = 'delivered'")->fetchColumn();
    $stats['in_transit'] = (int)$db->query("SELECT COUNT(*) FROM shipments WHERE status IN ('in_transit','at_hub','out_for_delivery')")->fetchColumn();
} catch (Exception $e) {
    $error = 'Failed to load stats: ' . $e->getMessage();
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Simulation — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./css/admin.css" rel="stylesheet">
    <style>
        .stat-card { transition: transform 0.2s; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-icon { font-size: 2rem; opacity: 0.8; }
        .timeline-sim { position: relative; padding-left: 30px; }
        .timeline-sim::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e9ecef; }
        .timeline-step { position: relative; padding-bottom: 16px; }
        .timeline-step::before { content: ''; position: absolute; left: -22px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #0d6efd; border: 2px solid #fff; box-shadow: 0 0 0 2px #0d6efd; }
        .timeline-step.completed::before { background: #198754; box-shadow: 0 0 0 2px #198754; }
        .timeline-step.current::before { background: #ffc107; box-shadow: 0 0 0 2px #ffc107; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { box-shadow: 0 0 0 2px #ffc107; } 50% { box-shadow: 0 0 0 6px rgba(255,193,7,0.3); } }
        .card-sim { border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-radius: 12px; }
        .btn-sim { border-radius: 8px; font-weight: 500; }
        .status-badge { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .progress-bar-sim { height: 6px; border-radius: 3px; }
        .table-sim th { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; }
        .table-sim td { vertical-align: middle; font-size: 0.9rem; }
        .action-btns .btn { padding: 4px 8px; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i>Shipment Simulation</h2>
            <p class="text-muted mb-0">Generate sample shipments and simulate realistic delivery progress</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sim"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
            <a href="shipments.php" class="btn btn-outline-primary btn-sim"><i class="bi bi-box me-1"></i>Shipments</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-white-50 small">Total Shipments</p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <i class="bi bi-box stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-white-50 small">Active Shipments</p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['active']) ?></h3>
                    </div>
                    <i class="bi bi-arrow-up-circle stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-white-50 small">In Transit</p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['in_transit']) ?></h3>
                    </div>
                    <i class="bi bi-truck stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-white-50 small">Delivered</p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['delivered']) ?></h3>
                    </div>
                    <i class="bi bi-check-circle stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card card-sim">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle text-primary me-2"></i>Generate Sample Shipments</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?action=generate" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Count (1-100)</label>
                            <input type="number" name="count" class="form-control" value="5" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="auto_simulate" id="autoSimulate">
                                <label class="form-check-label" for="autoSimulate">Auto-simulate 1-3 steps</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="simulate_full" id="simulateFull">
                                <label class="form-check-label" for="simulateFull">Full simulation (all stages to delivered)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sim"><i class="bi bi-magic me-2"></i>Generate Shipments</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-sim">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-play-circle text-success me-2"></i>Run Simulation</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?action=simulate" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Shipment ID</label>
                            <input type="number" name="shipment_id" class="form-control" placeholder="e.g. 123" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Mode</label>
                            <select name="mode" class="form-select">
                                <option value="single">Single Step</option>
                                <option value="full">Full Simulation</option>
                                <option value="batch">Batch (all eligible)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="batchMaxField" style="display:none;">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Batch Limit</label>
                            <input type="number" name="batch_max" class="form-control" value="20" min="1" max="100">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-sim"><i class="bi bi-play me-2"></i>Run Simulation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card card-sim">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-arrow-counterclockwise text-warning me-2"></i>Reset Shipment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?action=reset" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Shipment ID</label>
                            <input type="number" name="shipment_id" class="form-control" placeholder="Reset shipment to initial state" min="1" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-warning btn-sim w-100"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-sim">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-diagram-3 text-info me-2"></i>Simulation Stages</h5>
                </div>
                <div class="card-body">
                    <div class="timeline-sim">
                        <div class="timeline-step completed"><strong>Shipment Received</strong><br><small class="text-muted">Information registered, awaiting pickup</small></div>
                        <div class="timeline-step completed"><strong>Parcel Picked Up</strong><br><small class="text-muted">Courier collected the parcel</small></div>
                        <div class="timeline-step completed"><strong>Origin Facility</strong><br><small class="text-muted">Scanning, sorting, and dispatch</small></div>
                        <div class="timeline-step current"><strong>In Transit</strong><br><small class="text-muted">Moving through the logistics network</small></div>
                        <div class="timeline-step"><strong>Destination Hub</strong><br><small class="text-muted">Arrived at regional distribution center</small></div>
                        <div class="timeline-step"><strong>Out for Delivery</strong><br><small class="text-muted">Local courier dispatched</small></div>
                        <div class="timeline-step"><strong>Delivered</strong><br><small class="text-muted">Parcel delivered to recipient</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sim">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-list-ul text-secondary me-2"></i>Recent Shipments</h5>
            <span class="badge bg-secondary"><?= count($recentShipments) ?> records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sim table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tracking Number</th>
                            <th>Status</th>
                            <th>Route</th>
                            <th>Current Location</th>
                            <th>Weight</th>
                            <th>Service</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentShipments)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No shipments found. Generate some above.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentShipments as $s): ?>
                            <?php
                                $statusLower = strtolower($s['status'] ?? '');
                                $statusClass = match(true) {
                                    $statusLower === 'delivered' => 'success',
                                    in_array($statusLower, ['in_transit','at_hub','out_for_delivery','picked_up'], true) => 'primary',
                                    in_array($statusLower, ['pending_pickup','created','processing'], true) => 'warning',
                                    in_array($statusLower, ['customs_inspection','customs_clearance','customs_delayed'], true) => 'info',
                                    in_array($statusLower, ['cancelled','returned','lost','damaged','customs_seized'], true) => 'danger',
                                    default => 'secondary',
                                };
                                $progress = match($statusLower) {
                                    'delivered' => 100,
                                    'out_for_delivery' => 90,
                                    'at_hub' => 70,
                                    'in_transit' => 50,
                                    'sorted','received_origin' => 35,
                                    'picked_up' => 20,
                                    'pending_pickup','created','processing' => 10,
                                    default => 0,
                                };
                            ?>
                            <tr>
                                <td class="fw-semibold">#<?= (int)$s['id'] ?></td>
                                <td><code class="text-primary"><?= htmlspecialchars($s['tracking_number']) ?></code></td>
                                <td><span class="badge bg-<?= $statusClass ?> status-badge"><?= htmlspecialchars($s['status'] ?? 'unknown') ?></span></td>
                                <td><small><?= htmlspecialchars($s['origin_city'] ?? '') ?> → <?= htmlspecialchars($s['destination_city'] ?? '') ?></small></td>
                                <td><small><?= htmlspecialchars($s['current_city'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($s['total_weight'] ?? '0') ?> kg</small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($s['service_type'] ?? 'standard') ?></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($s['created_at'] ?? '') ?></small></td>
                                <td class="text-end action-btns">
                                    <a href="shipment_details.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="edit_shipment.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="update_tracking.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-info" title="Tracking"><i class="bi bi-geo-alt"></i></a>
                                    <form method="POST" action="?action=simulate" class="d-inline" onsubmit="return confirm('Simulate next step for shipment #<?= (int)$s['id'] ?>?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="shipment_id" value="<?= (int)$s['id'] ?>">
                                        <input type="hidden" name="mode" value="single">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Simulate"><i class="bi bi-play"></i></button>
                                    </form>
                                    <form method="POST" action="?action=delete" class="d-inline" onsubmit="return confirm('Delete shipment #<?= (int)$s['id'] ?> permanently?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="shipment_id" value="<?= (int)$s['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('select[name="mode"]').addEventListener('change', function() {
    document.getElementById('batchMaxField').style.display = this.value === 'batch' ? 'block' : 'none';
});
</script>
</body>
</html>
