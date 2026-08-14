<?php
/**
 * Customer Notifications center — send Email / SMS / Push from templates or a
 * custom message, and keep a permanent history per shipment.
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/shipment_helpers.php';
require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/../includes/mailer.php';

requirePermission('send_notifications');

$page_title = 'Notifications - ' . SITE_NAME;
$db = getDB();
ensureModuleTables($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) { echo '<div class="alert alert-danger m-4">Shipment not found.</div>'; require_once __DIR__.'/includes/footer.php'; exit; }
$GLOBALS['shipment'] = $shipment;

$templates = [
    'created'      => 'Shipment Created',
    'pickup'       => 'Pickup Scheduled',
    'picked_up'    => 'Picked Up',
    'in_transit'   => 'In Transit',
    'customs'      => 'Customs Clearance',
    'out_for_delivery' => 'Out for Delivery',
    'delivered'    => 'Delivered',
    'delayed'      => 'Shipment Delayed',
    'returned'     => 'Returned to Sender',
];

$msg = ''; $msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msg = 'Invalid security token.'; $msgType='danger';
    } else {
        $channel = in_array($_POST['channel'] ?? '', ['email','sms','push']) ? $_POST['channel'] : 'email';
        $template = trim($_POST['template'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $recipient = trim($_POST['recipient'] ?? '');
        if ($recipient === '') { $recipient = $shipment['receiver_email'] ?? $shipment['sender_email'] ?? ''; }
        if ($body === '') { $msg='Message body is required.'; $msgType='danger'; }
        else {
            $status = 'sent';
            if ($channel === 'email' && $recipient !== '') {
                $mailSent = sendMail($recipient, $subject ?: 'Update about your shipment '.$shipment['tracking_number'], $body);
                if (!$mailSent) { $status = 'failed'; }
            }
            // SMS / Push are logged; actual dispatch would use a provider queue.
            $db->prepare("INSERT INTO shipment_notifications (shipment_id, channel, template, recipient, subject, body, status, sent_by, created_at)
                VALUES (:s,:c,:t,:r,:su,:b,:st,:u,NOW())")
                ->execute([':s'=>$id,':c'=>$channel,':t'=>$template?:null,':r'=>$recipient?:null,':su'=>$subject?:null,':b'=>$body,':st'=>$status,':u'=>$_SESSION['admin_id']??null]);
            logShipmentAction($db, 'notification_sent', $id, "Sent $channel notification" . ($template?" (template: $template)":''), ['channel'=>$channel,'recipient'=>$recipient]);
            $msg = ucfirst($channel).' notification sent and recorded.'; $msgType='success';
        }
    }
}

$stmt = $db->prepare("SELECT * FROM shipment_notifications WHERE shipment_id=:id ORDER BY created_at DESC, id DESC");
$stmt->execute([':id' => $id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications — <?php echo $v('tracking_number'); ?></h4>
        <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card"><div class="card-header">Compose Notification</div><div class="card-body">
                <form method="POST">
                    <?php echo csrfInput(); ?>
                    <div class="mb-3"><label class="form-label">Channel</label>
                        <select name="channel" class="form-select" onchange="document.getElementById('chHint').textContent = this.value==='email' ? 'Delivered via SMTP/mail.' : (this.value==='sms' ? 'Queued to SMS gateway.' : 'Queued to push service.');">
                            <option value="email">Email</option><option value="sms">SMS</option><option value="push">Push Notification</option>
                        </select>
                        <small class="text-muted" id="chHint">Delivered via SMTP/mail.</small>
                    </div>
                    <div class="mb-3"><label class="form-label">Template</label>
                        <select name="template" class="form-select" id="tpl" onchange="applyTpl()">
                            <option value="">— Custom message —</option>
                            <?php foreach ($templates as $k=>$l): ?><option value="<?php echo $k; ?>"><?php echo $l; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Recipient</label><input name="recipient" class="form-control" value="<?php echo $v('receiver_email'); ?>" placeholder="email / phone"></div>
                    <div class="mb-3"><label class="form-label">Subject</label><input name="subject" class="form-control" id="subj" placeholder="Optional"></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="body" class="form-control" rows="5" id="body" required placeholder="Type your message..."></textarea></div>
                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Send this notification?');"><i class="bi bi-send"></i> Send Notification</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Notification History (<?php echo count($history); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>Channel</th><th>Template</th><th>Recipient</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($history)): ?><tr><td colspan="5" class="text-center text-muted py-4">No notifications sent.</td></tr>
                    <?php else: foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($h['created_at'])); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo strtoupper($h['channel']); ?></span></td>
                            <td><?php echo htmlspecialchars($h['template'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($h['recipient'] ?? '—'); ?></td>
                            <td><?php echo $h['status']==='sent' ? '<span class="badge bg-success">Sent</span>' : '<span class="badge bg-danger">'.htmlspecialchars($h['status']).'</span>'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div></div>
        </div>
    </div>
</div>
<script>
const tplText = <?php echo json_encode($templates); ?>;
function applyTpl() {
    const k = document.getElementById('tpl').value;
    if (!k) return;
    const tn = '<?php echo addslashes($shipment['tracking_number'] ?? ''); ?>';
    const trackUrl = '/shp/track.php?cons_no=' + encodeURIComponent(tn);
    document.getElementById('subj').value = 'Update: ' + (tplText[k] || k) + ' — ' + tn;
    document.getElementById('body').value = 'Hello, your shipment ' + tn + ' status: ' + (tplText[k] || k) + '. Track at ' + trackUrl;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
