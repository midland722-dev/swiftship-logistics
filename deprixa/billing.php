<?php
/**
 * Payment & Billing — cost breakdown, payment method, transaction/invoice,
 * receipt, refund history, and downloadable invoice PDF.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/permissions.php';
require_once __DIR__ . '/includes/shipment_helpers.php';

requirePermission('manage_billing');

$page_title = 'Billing - ' . SITE_NAME;
$db = getDB();
ensureModuleTables($db);
ensureAdvancedShipmentColumns($db);

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$shipment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$shipment) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger m-4">Shipment not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$GLOBALS['shipment'] = $shipment;

// Invoice PDF export — generated with the bundled FPDF before any HTML is sent.
if (isset($_GET['invoice']) && $_GET['invoice'] === '1') {
    require_once __DIR__ . '/../deprixa/fpdf/fpdf.php';
    $tn = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($shipment['tracking_number'] ?? 'shipment'));
    $cur = $shipment['currency'] ?? 'USD';
    $row = function (string $label, $amount) use ($cur): array {
        return [$label, $cur . ' ' . number_format((float)$amount, 2)];
    };
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Invoice', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Tracking: ' . $shipment['tracking_number'], 0, 1);
    $pdf->Cell(0, 6, 'Invoice #: ' . ($shipment['invoice_number'] ?? '—'), 0, 1);
    $pdf->Cell(0, 6, 'Date: ' . date('Y-m-d'), 0, 1);
    $pdf->Ln(2);
    $w = [110, 70];
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($w[0], 8, 'Description', 1);
    $pdf->Cell($w[1], 8, 'Amount', 1, 1, 'R');
    $pdf->SetFont('Arial', '', 10);
    foreach ([
        $row('Shipping Cost', $shipment['shipping_cost'] ?? 0),
        $row('Additional Charges', $shipment['additional_charges'] ?? 0),
        $row('Insurance', $shipment['insurance_amount'] ?? 0),
        $row('Discount', '-' . number_format((float)($shipment['discount'] ?? 0), 2)),
        $row('Tax', $shipment['tax'] ?? 0),
    ] as [$label, $amt]) {
        $pdf->Cell($w[0], 8, $label, 1);
        $pdf->Cell($w[1], 8, $amt, 1, 1, 'R');
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($w[0], 8, 'Total', 1);
    $pdf->Cell($w[1], 8, $cur . ' ' . number_format((float)($shipment['total_amount'] ?? 0), 2), 1, 1, 'R');
    $pdf->Ln(4);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Payment Method: ' . ($shipment['payment_method'] ?? '—'), 0, 1);
    $pdf->Cell(0, 6, 'Transaction ID: ' . ($shipment['transaction_id'] ?? '—'), 0, 1);
    $pdf->Cell(0, 6, 'Payment Status: ' . ($shipment['payment_status'] ?? '—'), 0, 1);
    $pdf->Output('invoice_' . $tn . '.pdf', 'D');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$msg = ''; $msgType = '';

// Record refund.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'refund') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $msg='Invalid token.'; $msgType='danger';
    } else {
        $amount = max(0, floatval($_POST['amount'] ?? 0));
        $reason = trim($_POST['reason'] ?? '');
        $cur = $shipment['currency'] ?? 'USD';
        if ($amount <= 0) { $msg='Refund amount must be greater than 0.'; $msgType='danger'; }
        else {
            $db->prepare("INSERT INTO refunds (shipment_id, amount, currency, reason, refunded_by, created_at) VALUES (:s,:a,:c,:r,:u,NOW())")
                ->execute([':s'=>$id,':a'=>$amount,':c'=>$cur,':r'=>$reason,':u'=>$_SESSION['admin_id']??null]);
            // Mark payment status refunded if fully refunded.
            if (abs($amount - ($shipment['total_amount'] ?? 0)) < 0.01) {
                $db->prepare("UPDATE shipments SET payment_status='refunded', updated_at=NOW() WHERE id=:id")->execute([':id'=>$id]);
            }
            logShipmentAction($db, 'refund_recorded', $id, "Refund $cur $amount" . ($reason?": $reason":''));
            $msg='Refund recorded.'; $msgType='success';
            $stmt = $db->prepare("SELECT * FROM shipments WHERE id=:id LIMIT 1");
            $stmt->execute([':id'=>$id]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}


$stmt = $db->prepare("SELECT * FROM refunds WHERE shipment_id=:id ORDER BY created_at DESC, id DESC");
$stmt->execute([':id' => $id]);
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cur = $shipment['currency'] ?? 'USD';
$refundTotal = array_sum(array_column($refunds, 'amount'));
$v = function($k,$d='') { return htmlspecialchars($GLOBALS['shipment'][$k] ?? $d); };
$num = function($k) { return number_format($GLOBALS['shipment'][$k] ?? 0, 2); };
?>
<?php require_once __DIR__ . '/includes/flash.php'; ?>
<?php if ($msg): ?><div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show m-3" role="alert"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Billing — <?php echo $v('tracking_number'); ?></h4>
        <div class="d-flex gap-2">
            <a href="?id=<?php echo $id; ?>&invoice=1" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> Invoice PDF</a>
            <a href="receipt_pdf.php?id=<?php echo $id; ?>&download=1" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Receipt</a>
            <a href="shipment_details.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm">Back</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Cost Breakdown</div><div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr><td>Shipping Cost</td><td class="text-end"><?php echo $cur; ?> <?php echo $num('shipping_cost'); ?></td></tr>
                        <tr><td>Additional Charges</td><td class="text-end"><?php echo $cur; ?> <?php echo $num('additional_charges'); ?></td></tr>
                        <tr><td>Insurance</td><td class="text-end"><?php echo $cur; ?> <?php echo $num('insurance_amount'); ?></td></tr>
                        <tr><td>Discount</td><td class="text-end text-success">-<?php echo $cur; ?> <?php echo $num('discount'); ?></td></tr>
                        <tr><td>Tax</td><td class="text-end"><?php echo $cur; ?> <?php echo $num('tax'); ?></td></tr>
                        <tr class="table-active"><th>Total Amount</th><th class="text-end"><?php echo $cur; ?> <?php echo $num('total_amount'); ?></th></tr>
                    </tbody>
                </table>
                <hr>
                <div class="row g-2">
                    <div class="col-sm-6"><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_',' ',$v('payment_method'))); ?></div>
                    <div class="col-sm-6"><strong>Payment Status:</strong> <span class="badge bg-<?php echo $shipment['payment_status']==='paid'?'success':($shipment['payment_status']==='refunded'?'warning':'secondary'); ?>"><?php echo ucfirst($v('payment_status')); ?></span></div>
                    <div class="col-sm-6"><strong>Transaction ID:</strong> <?php echo $v('transaction_id') ?: '—'; ?></div>
                    <div class="col-sm-6"><strong>Invoice Number:</strong> <?php echo $v('invoice_number') ?: '—'; ?></div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card mb-3"><div class="card-header">Record Refund</div><div class="card-body">
                <form method="POST">
                    <?php echo csrfInput(); ?><input type="hidden" name="act" value="refund">
                    <div class="mb-2"><label class="form-label small">Amount (<?php echo $cur; ?>)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label small">Reason</label><input name="reason" class="form-control"></div>
                    <button class="btn btn-warning btn-sm w-100" onclick="return confirm('Record this refund?');"><i class="bi bi-arrow-counterclockwise"></i> Record Refund</button>
                </form>
            </div></div>
            <div class="card"><div class="card-header">Refund History (<?php echo count($refunds); ?>)</div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Amount</th><th>Reason</th></tr></thead>
                    <tbody>
                    <?php if (empty($refunds)): ?><tr><td colspan="3" class="text-center text-muted py-3">No refunds.</td></tr>
                    <?php else: foreach ($refunds as $r): ?>
                        <tr><td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td><td><?php echo $cur; ?> <?php echo number_format($r['amount'],2); ?></td><td><?php echo htmlspecialchars($r['reason'] ?? ''); ?></td></tr>
                    <?php endforeach; endif; ?>
                    <tr class="table-active"><th>Total Refunded</th><th colspan="2"><?php echo $cur; ?> <?php echo number_format($refundTotal,2); ?></th></tr>
                    </tbody>
                </table></div>
            </div></div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
