<?php
/**
 * Floating Quick Actions panel for shipment pages.
 * Expects a $shipment array (with id, tracking_number, status) in scope.
 * Renders a fixed button that opens a responsive offcanvas listing all
 * quick actions, gated by role permissions.
 */
if (!isset($shipment) || empty($shipment['id'])) { return; }
$qaId = (int)$shipment['id'];
$qaTn = $shipment['tracking_number'] ?? '';
$qaStatus = $shipment['status'] ?? '';
$back = 'shipment_details.php?id=' . $qaId;
?>
<button class="btn btn-primary rounded-circle quick-actions-fab" type="button"
        data-bs-toggle="offcanvas" data-bs-target="#qaCanvas" aria-controls="qaCanvas"
        title="Quick Actions">
    <i class="bi bi-lightning-charge-fill"></i>
</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="qaCanvas" aria-labelledby="qaCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="qaCanvasLabel"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-muted small mb-3"><?php echo htmlspecialchars($qaTn); ?> · <?php echo htmlspecialchars(statusLabel($qaStatus)); ?></p>
        <div class="d-grid gap-2">
            <?php if (can('edit_shipment')): ?><a href="edit_shipment.php?id=<?php echo $qaId; ?>" class="btn btn-outline-primary text-start"><i class="bi bi-pencil me-2"></i>Edit Shipment</a><?php endif; ?>
            <?php if (can('update_tracking')): ?><a href="update_tracking.php?id=<?php echo $qaId; ?>" class="btn btn-outline-warning text-start"><i class="bi bi-clock-history me-2"></i>Update Tracking</a><?php endif; ?>
            <?php if (can('assign_courier')): ?><a href="assign_courier.php?id=<?php echo $qaId; ?>" class="btn btn-outline-info text-start"><i class="bi bi-person-badge me-2"></i>Assign Courier</a><?php endif; ?>
            <a href="print_label.php?id=<?php echo $qaId; ?>" target="_blank" class="btn btn-outline-secondary text-start"><i class="bi bi-upc me-2"></i>Print Shipping Label</a>
            <a href="billing.php?id=<?php echo $qaId; ?>" class="btn btn-outline-secondary text-start"><i class="bi bi-receipt me-2"></i>Print Invoice</a>
            <a href="receipt_pdf.php?id=<?php echo $qaId; ?>&download=1" class="btn btn-outline-secondary text-start"><i class="bi bi-download me-2"></i>Download Receipt</a>
            <a href="documents_manager.php?id=<?php echo $qaId; ?>" class="btn btn-outline-secondary text-start"><i class="bi bi-upc-scan me-2"></i>Generate Barcode / QR</a>
            <?php if (can('send_notifications')): ?><a href="notifications.php?id=<?php echo $qaId; ?>" class="btn btn-outline-secondary text-start"><i class="bi bi-bell me-2"></i>Send Notification</a><?php endif; ?>

            <hr>
            <?php if (can('hold_shipment')): ?>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('Hold this shipment?');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="hold"><input type="hidden" name="redirect" value="<?php echo $back; ?>">
                    <button class="btn btn-outline-dark text-start w-100"><i class="bi bi-pause-fill me-2"></i>Hold Shipment</button>
                </form>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('Resume this shipment?');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="resume"><input type="hidden" name="redirect" value="<?php echo $back; ?>">
                    <button class="btn btn-outline-success text-start w-100"><i class="bi bi-play-fill me-2"></i>Resume Shipment</button>
                </form>
            <?php endif; ?>
            <?php if (can('return_shipment')): ?>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('Return this shipment to sender?');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="return"><input type="hidden" name="redirect" value="<?php echo $back; ?>">
                    <button class="btn btn-outline-warning text-start w-100"><i class="bi bi-arrow-return-left me-2"></i>Return to Sender</button>
                </form>
            <?php endif; ?>
            <?php if (can('cancel_shipment')): ?>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('Cancel this shipment? This will be recorded in the timeline.');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="cancel"><input type="hidden" name="redirect" value="<?php echo $back; ?>">
                    <button class="btn btn-outline-danger text-start w-100"><i class="bi bi-x-circle me-2"></i>Cancel Shipment</button>
                </form>
            <?php endif; ?>
            <?php if (can('archive_shipment')): ?>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('Archive this shipment?');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="archive"><input type="hidden" name="redirect" value="<?php echo $back; ?>">
                    <button class="btn btn-outline-secondary text-start w-100"><i class="bi bi-archive me-2"></i>Archive Shipment</button>
                </form>
            <?php endif; ?>
            <?php if (can('delete_shipment')): ?>
                <hr>
                <form method="POST" action="shipment_action.php" onsubmit="return confirm('PERMANENTLY delete this shipment and all related records?');">
                    <?php echo csrfInput(); ?><input type="hidden" name="id" value="<?php echo $qaId; ?>"><input type="hidden" name="act" value="delete"><input type="hidden" name="redirect" value="shipments.php">
                    <button class="btn btn-danger text-start w-100"><i class="bi bi-trash me-2"></i>Delete Shipment</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
.quick-actions-fab {
    position: fixed; bottom: 1.5rem; right: 1.5rem; width: 56px; height: 56px;
    z-index: 1040; box-shadow: 0 8px 24px rgba(30,60,114,.35); font-size: 1.3rem;
}
</style>
