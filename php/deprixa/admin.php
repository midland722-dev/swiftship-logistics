<?php
/**
 * deprixa/admin.php
 *
 * Admin dashboard — shipment overview, quick stats, and shortcuts.
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/library.php';

if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

$legacyRole = strtolower((string)($_SESSION['user_type'] ?? ''));
if (!in_array($legacyRole, ['admin', 'staff'], true)) {
    header('Location: index.php?error=' . urlencode('You do not have permission to access that page.'));
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../php/validation/TrackingValidator.php';
require_once __DIR__ . '/../php/services/TrackingService.php';

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $feedback = 'Invalid security token. Please refresh and try again.';
    } else {
        $input = [
            'shipment_id'      => (int)($_POST['shipment_id'] ?? 0),
            'tracking_number'  => trim((string)($_POST['tracking_number'] ?? '')),
            'status'           => trim((string)($_POST['status'] ?? '')),
            'location'         => trim((string)($_POST['location'] ?? '')),
            'description'      => trim((string)($_POST['description'] ?? '')),
            'event_timestamp'  => trim((string)($_POST['event_timestamp'] ?? '')),
        ];

        $errors = TrackingValidator::validateStatusUpdate($input);
        if ($errors) {
            $feedback = implode(' ', $errors);
        } else {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            try {
                TrackingService::updateStatus($input, $userId);
                $feedback = 'Status updated successfully.';
            } catch (Exception $e) {
                $feedback = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$company = db_fetch_one('SELECT * FROM company WHERE id = 1');

$totalShipments     = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM shipments')['cnt'] ?? 0);
$totalOnline        = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM courier_online')['cnt'] ?? 0);
$totalCustomers     = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM users WHERE role = "customer" AND is_active = 1')['cnt'] ?? 0);
$inTransit          = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM shipments WHERE status = "in_transit"')['cnt'] ?? 0);
$delivered          = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM shipments WHERE status = "delivered"')['cnt'] ?? 0);
$pending            = (int)(db_fetch_one('SELECT COUNT(*) AS cnt FROM shipments WHERE status = "pending"')['cnt'] ?? 0);

$recentShipments = db_fetch_all(
    'SELECT s.id, s.tracking_number, s.status, s.service_type, s.origin_city, s.destination_city, s.created_at
     FROM shipments s
     ORDER BY s.created_at DESC
     LIMIT 10'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deprixa — Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #1e2433; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #FFCC00; text-decoration: none; font-weight: 600; }
        main { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .card h3 { margin: 0 0 .5rem; font-size: .875rem; text-transform: uppercase; color: #6b7280; }
        .card .value { font-size: 2rem; font-weight: 700; }
        .card .sub { font-size: .875rem; color: #6b7280; margin-top: .25rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-top: 1.5rem; }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: .875rem; }
        th { background: #f9fafb; font-weight: 600; }
        .badge { display: inline-block; padding: .25rem .5rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-transit { background: #dbeafe; color: #1e40af; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .actions { display: flex; gap: .5rem; margin-top: 1.5rem; }
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 6px; font-size: .875rem; font-weight: 600; text-decoration: none; }
        .btn-primary { background: #D62B2B; color: #fff; }
        .btn-secondary { background: #fff; color: #1a1a1a; border: 1px solid #d1d5db; }
    </style>
</head>
<body>
    <header>
        <span>Deprixa Admin — <?= h($_SESSION['user_display_name'] ?? $_SESSION['user_name'] ?? 'User') ?></span>
        <a href="process.php?action=logOut">Logout</a>
    </header>
    <main>
        <div class="grid">
            <div class="card">
                <h3>Total Shipments</h3>
                <div class="value"><?= number_format($totalShipments) ?></div>
                <div class="sub"><?= number_format($pending) ?> pending</div>
            </div>
            <div class="card">
                <h3>In Transit</h3>
                <div class="value"><?= number_format($inTransit) ?></div>
                <div class="sub">Active shipments</div>
            </div>
            <div class="card">
                <h3>Delivered</h3>
                <div class="value"><?= number_format($delivered) ?></div>
                <div class="sub">Completed</div>
            </div>
            <div class="card">
                <h3>Customers</h3>
                <div class="value"><?= number_format($totalCustomers) ?></div>
                <div class="sub">Active accounts</div>
            </div>
            <div class="card">
                <h3>Online Bookings</h3>
                <div class="value"><?= number_format($totalOnline) ?></div>
                <div class="sub">All time</div>
            </div>
            <div class="card">
                <h3>Company</h3>
                <div class="value" style="font-size:1rem;"><?= h($company['cname'] ?? 'N/A') ?></div>
                <div class="sub"><?= h($company['email'] ?? '') ?></div>
            </div>
        </div>

        <div class="actions">
            <a href="add-courier.php" class="btn btn-primary">+ New Shipment</a>
            <a href="online-bookings.php" class="btn btn-secondary">Online Bookings</a>
            <a href="../index.php" class="btn btn-secondary">View Site</a>
        </div>

        <h2 style="margin-top: 2rem; font-size: 1.25rem; font-weight: 700;">Recent Shipments</h2>

        <?php if ($feedback !== ''): ?>
            <div style="margin-top: 1rem; padding: .75rem 1rem; border-radius: 6px; background: <?= str_contains($feedback, 'failed') || str_contains($feedback, 'Error') ? '#fee2e2' : '#d1fae5' ?>; color: <?= str_contains($feedback, 'failed') || str_contains($feedback, 'Error') ? '#991b1b' : '#065f46' ?>; font-size: .875rem;">
                <?= h($feedback) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['ok']) && isset($_GET['tracking'])): ?>
            <div style="margin-top: 1rem; padding: .75rem 1rem; border-radius: 6px; background: #d1fae5; color: #065f46; font-size: .875rem;">
                Shipment created successfully. Tracking number: <strong><?= h($_GET['tracking']) ?></strong>
                &nbsp;|&nbsp;
                <a href="/php/process/generate_receipt.php?tracking_number=<?= urlencode($_GET['tracking']) ?>" target="_blank" style="color: #065f46; font-weight: 700; text-decoration: underline;">Download receipt</a>
            </div>
        <?php endif; ?>

        <form method="post" style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06);">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="shipment_id" value="">
            <div style="display: flex; flex-direction: column; gap: .25rem;">
                <label style="font-size: .75rem; font-weight: 600; color: #6b7280;">Tracking #</label>
                <input type="text" name="tracking_number" required placeholder="e.g. ASC000000000000" style="padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .875rem; min-width: 180px;">
            </div>
            <div style="display: flex; flex-direction: column; gap: .25rem;">
                <label style="font-size: .75rem; font-weight: 600; color: #6b7280;">Status</label>
                <select name="status" required style="padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .875rem; min-width: 160px;">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="picked_up">Picked Up</option>
                    <option value="at_warehouse">At Warehouse</option>
                    <option value="in_transit">In Transit</option>
                    <option value="at_hub">At Hub</option>
                    <option value="customs_inspection">Customs Inspection</option>
                    <option value="customs_clearance">Customs Clearance</option>
                    <option value="out_for_delivery">Out for Delivery</option>
                    <option value="delivered">Delivered</option>
                    <option value="returned">Returned</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: .25rem;">
                <label style="font-size: .75rem; font-weight: 600; color: #6b7280;">Location</label>
                <input type="text" name="location" placeholder="City, Country" style="padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .875rem; min-width: 160px;">
            </div>
            <div style="display: flex; flex-direction: column; gap: .25rem;">
                <label style="font-size: .75rem; font-weight: 600; color: #6b7280;">Description</label>
                <input type="text" name="description" placeholder="Optional note" style="padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .875rem; min-width: 200px;">
            </div>
            <div style="display: flex; flex-direction: column; gap: .25rem;">
                <label style="font-size: .75rem; font-weight: 600; color: #6b7280;">Event time</label>
                <input type="datetime-local" name="event_timestamp" style="padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .875rem; min-width: 180px;">
            </div>
            <button type="submit" class="btn btn-primary" style="height: 38px;">Update Status</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tracking #</th>
                    <th>Status</th>
                    <th>Service</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Created</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentShipments)): ?>
                    <tr><td colspan="8" style="text-align:center; color:#6b7280;">No shipments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentShipments as $s): ?>
                        <?php
                            $status = h($s['status']);
                            $badgeClass = 'badge-pending';
                            if (in_array($status, ['in_transit', 'at_hub', 'out_for_delivery'], true)) $badgeClass = 'badge-transit';
                            if ($status === 'delivered') $badgeClass = 'badge-delivered';
                        ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td><?= h($s['tracking_number']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>
                            <td><?= h($s['service_type'] ?? '—') ?></td>
                            <td><?= h($s['origin_city'] ?? '—') ?></td>
                            <td><?= h($s['destination_city'] ?? '—') ?></td>
                            <td><?= h($s['created_at'] ?? '—') ?></td>
                            <td><a href="/php/process/generate_receipt.php?tracking_number=<?= urlencode($s['tracking_number']) ?>" target="_blank" style="color:#D62B2B; font-weight:600; text-decoration:none;">Download</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
