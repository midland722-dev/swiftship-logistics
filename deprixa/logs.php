<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/Logger.php';

$page_title = 'System Logs - ' . SITE_NAME;
$db = getDB();

$logger = new Logger();
$errors = [];

$log_type = $_GET['type'] ?? 'application';
$log_date = $_GET['date'] ?? date('Y-m-d');
$log_level = $_GET['level'] ?? '';
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page_num - 1) * $per_page;

$valid_types = ['application', 'database', 'auth', 'shipment', 'payment'];
if (!in_array($log_type, $valid_types)) {
    $log_type = 'application';
}

$all_logs = $logger->read($log_type, $log_date, $log_level, 1000);
$total_logs = count($all_logs);
$total_pages = max(1, (int)ceil($total_logs / $per_page));
$page_num = min($page_num, $total_pages);
$logs = array_slice($all_logs, $offset, $per_page);

$stats = $logger->getStats(7);
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">System Logs</li>
                    </ol>
                </nav>
                <h1 class="mb-0">System Logs</h1>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="?type=<?php echo $log_type; ?>&date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </a>
            </div>
        </div>
    </div>
</div>

<section class="section" style="background: #F8FAFC;">
    <div class="container">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon blue"><i class="bi bi-file-text"></i></div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase tracking-wide">Total Entries</div>
                                <div class="fw-bold fs-5 text-primary mb-0"><?php echo number_format($stats['total_entries']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase tracking-wide">Errors</div>
                                <div class="fw-bold fs-5 text-danger mb-0"><?php echo number_format($stats['errors']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon orange"><i class="bi bi-exclamation-circle"></i></div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase tracking-wide">Warnings</div>
                                <div class="fw-bold fs-5 text-warning mb-0"><?php echo number_format($stats['warnings']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon green"><i class="bi bi-info-circle"></i></div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase tracking-wide">Info</div>
                                <div class="fw-bold fs-5 text-success mb-0"><?php echo number_format($stats['info']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Log Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($valid_types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo $log_type === $type ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($log_date); ?>" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            <option value="ERROR" <?php echo $log_level === 'ERROR' ? 'selected' : ''; ?>>Error</option>
                            <option value="WARNING" <?php echo $log_level === 'WARNING' ? 'selected' : ''; ?>>Warning</option>
                            <option value="INFO" <?php echo $log_level === 'INFO' ? 'selected' : ''; ?>>Info</option>
                            <option value="DEBUG" <?php echo $log_level === 'DEBUG' ? 'selected' : ''; ?>>Debug</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="?type=<?php echo $log_type; ?>" class="btn btn-secondary w-100">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> 
                    <?php echo ucfirst($log_type); ?> Logs - <?php echo $log_date; ?>
                    <span class="text-muted">(<?php echo number_format($total_logs); ?> entries)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="text-center py-5 text-muted">No logs found for this selection</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="180">Timestamp</th>
                                    <th width="80">Level</th>
                                    <th>Message</th>
                                    <th width="150">User</th>
                                    <th width="120">IP Address</th>
                                    <th width="100">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                        $level_color = match(strtoupper($log['level'] ?? 'INFO')) {
                                            'ERROR' => 'danger',
                                            'WARNING' => 'warning',
                                            'DEBUG' => 'secondary',
                                            default => 'info'
                                        };
                                    ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars($log['timestamp']); ?></small></td>
                                        <td>
                                            <span class="badge bg-<?php echo $level_color; ?>">
                                                <?php echo htmlspecialchars($log['level'] ?? 'INFO'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['message']); ?></strong>
                                            <?php if (!empty($log['context']) && count($log['context']) > 0): ?>
                                                <div class="mt-1">
                                                    <?php foreach ($log['context'] as $key => $value): ?>
                                                        <small class="text-muted d-block">
                                                            <strong><?php echo htmlspecialchars($key); ?>:</strong> 
                                                            <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?>
                                                        </small>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($log['user_name'] ?? 'guest'); ?>
                                                <?php if ($log['user_id']): ?>
                                                    (ID: <?php echo $log['user_id']; ?>)
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></small></td>
                                        <td>
                                            <?php if (!empty($log['url']) && $log['url'] !== 'cli'): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($log['url']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($page_num > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page_num - 1])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page_num ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page_num < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page_num + 1])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.stat-icon.blue { background: #DBEAFE; color: #2563EB; }
.stat-icon.green { background: #D1FAE5; color: #059669; }
.stat-icon.orange { background: #FEF3C7; color: #D97706; }
.stat-icon.red { background: #FEE2E2; color: #DC2626; }
.stat-icon.teal { background: #CCFBF1; color: #0D9488; }
.stat-icon.indigo { background: #E0E7FF; color: #4F46E5; }

.page-header {
    background: white;
    border-bottom: 1px solid #E5E7EB;
    padding: 1.5rem 0;
    margin-bottom: 1.5rem;
}
.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.section {
    padding: 2rem 0;
    min-height: calc(100vh - 200px);
}

.tracking-wide {
    letter-spacing: 0.08em;
}

.table td {
    vertical-align: middle;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds
    setTimeout(() => {
        window.location.reload();
    }, 30000);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
