<?php
require_once __DIR__ . '/includes/header.php';

$page_title = 'Support Tickets - ' . SITE_NAME;
$db = getDB();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $new_status = trim($_POST['new_status'] ?? '');
    $assigned_to = intval($_POST['assigned_to'] ?? 0);
    $resolution = trim($_POST['resolution'] ?? '');
    
    if ($action === 'update_status' && $ticket_id > 0 && !empty($new_status)) {
        try {
            $updates = ['status = :status', 'updated_at = NOW()'];
            $params = [':status' => $new_status, ':id' => $ticket_id];
            
            if (!empty($resolution) && in_array($new_status, ['resolved', 'closed'])) {
                $updates[] = 'resolution = :resolution';
                $updates[] = 'resolved_at = NOW()';
                $params[':resolution'] = $resolution;
            }
            
            if ($new_status === 'closed') {
                $updates[] = 'closed_at = NOW()';
            }
            
            $stmt = $db->prepare("
                UPDATE support_tickets 
                SET " . implode(', ', $updates) . " 
                WHERE id = :id
            ");
            $stmt->execute($params);
            
            $message = "Ticket updated successfully.";
            $message_type = 'success';
        } catch (Exception $e) {
            error_log('Ticket update failed: ' . $e->getMessage());
            $message = "An error occurred while updating the ticket. Please try again.";
            $message_type = 'danger';
        }
    }
}

$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$priority_filter = trim($_GET['priority'] ?? '');

$sql = "
    SELECT t.*, 
           CONCAT(u.name) as customer_name,
           u.email as customer_email,
           CONCAT(a.name) as assigned_to_name
    FROM support_tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN users a ON t.assigned_to = a.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (t.ticket_number LIKE :search OR t.subject LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $sql .= " AND t.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($priority_filter)) {
    $sql .= " AND t.priority = :priority";
    $params[':priority'] = $priority_filter;
}

$sql .= " ORDER BY t.created_at DESC LIMIT 50";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$valid_statuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed', 'reopened'];
$valid_priorities = ['low', 'medium', 'high', 'urgent'];

$page_title = 'Support Tickets - ' . SITE_NAME;
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
        <a href="create_shipment.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> New Shipment</a>
        <form method="GET" class="d-flex gap-2 align-items-center flex-grow-1">
            <input type="text" name="search" class="form-control" style="max-width: 300px;" 
                   placeholder="Search tickets..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-select" style="max-width: 150px;">
                <option value="">All Statuses</option>
                <?php foreach ($valid_statuses as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                        <?php echo ucwords(str_replace('_', ' ', $status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="priority" class="form-select" style="max-width: 150px;">
                <option value="">All Priorities</option>
                <?php foreach ($valid_priorities as $priority): ?>
                    <option value="<?php echo $priority; ?>" <?php echo $priority_filter === $priority ? 'selected' : ''; ?>>
                        <?php echo ucfirst($priority); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            <a href="tickets.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($tickets)): ?>
            <div class="text-center py-5 text-muted">No tickets found</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Customer</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($ticket['subject']); ?>
                                    <?php if ($ticket['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($ticket['description'], 0, 80)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($ticket['customer_name'] ?? 'N/A'); ?>
                                    <?php if ($ticket['customer_email']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($ticket['customer_email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $ticket['priority'] === 'urgent' ? 'danger' : 
                                            ($ticket['priority'] === 'high' ? 'warning' : 
                                            ($ticket['priority'] === 'medium' ? 'info' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $ticket['status'] === 'open' ? 'warning' : 
                                            ($ticket['status'] === 'in_progress' ? 'info' : 
                                            ($ticket['status'] === 'resolved' ? 'success' : 'secondary')); 
                                    ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                <td><small><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></small></td>
                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <select name="new_status" class="form-select form-select-sm" style="width: auto;">
                                            <?php foreach ($valid_statuses as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo $ticket['status'] === $status ? 'selected' : ''; ?>>
                                                    <?php echo ucwords(str_replace('_', ' ', $status)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
