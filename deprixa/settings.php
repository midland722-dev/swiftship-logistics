<?php
require_once __DIR__ . '/includes/header.php';

$page_title = 'System Settings - ' . SITE_NAME;
$db = getDB();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    $settings = $_POST['settings'] ?? [];
    $changed_by = $_SESSION['admin_id'] ?? 1;
    
    try {
        $db->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $value = trim($value);
            
            $stmt = $db->prepare("
                SELECT id, value, data_type, description 
                FROM system_config 
                WHERE config_key = :key
            ");
            $stmt->execute([':key' => $key]);
            $setting = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$setting) continue;
            
            $old_value = $setting['value'];
            
            if ($setting['data_type'] === 'json') {
                $decoded = json_decode($value, true);
                if ($value !== '' && $decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Invalid JSON for setting: $key");
                }
            } elseif ($setting['data_type'] === 'integer') {
                if ($value !== '' && !ctype_digit($value)) {
                    throw new Exception("Invalid integer for setting: $key");
                }
            } elseif ($setting['data_type'] === 'float') {
                if ($value !== '' && !is_numeric($value)) {
                    throw new Exception("Invalid number for setting: $key");
                }
            } elseif ($setting['data_type'] === 'boolean') {
                $value = in_array(strtolower($value), ['1', 'true', 'yes', 'on']) ? '1' : '0';
            }
            
            $stmt = $db->prepare("
                UPDATE system_config 
                SET value = :value, version = version + 1 
                WHERE config_key = :key
            ");
            $stmt->execute([':value' => $value ?: null, ':key' => $key]);
            
            $stmt = $db->prepare("
                INSERT INTO setting_changes 
                (setting_key, old_value, new_value, changed_by, change_reason, created_at)
                VALUES (:key, :old, :new, :by, :reason, NOW())
            ");
            $stmt->execute([
                ':key' => $key,
                ':old' => $old_value,
                ':new' => $value,
                ':by' => $changed_by,
                ':reason' => 'Updated via settings page'
            ]);
        }
        
        $db->commit();
        $message = 'Settings updated successfully.';
        $message_type = 'success';
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
        $message_type = 'danger';
    }
}

$categories = [];
try {
    $stmt = $db->query("
        SELECT * FROM system_config 
        ORDER BY category, config_key
    ");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($settings as $setting) {
        $cat = $setting['category'] ?? 'general';
        if (!isset($categories[$cat])) {
            $categories[$cat] = [];
        }
        $categories[$cat][] = $setting;
    }
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
                    $message = 'An error occurred. Please try again later.';
    $message_type = 'danger';
}

$page_title = 'System Settings - ' . SITE_NAME;
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-gear"></i> System Configuration</h5>
        <span class="text-muted">Manage application settings. Changes take effect immediately.</span>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_settings">
            
            <?php foreach ($categories as $category => $category_settings): ?>
                <div class="mb-5">
                    <h5 class="text-primary mb-3">
                        <i class="bi bi-folder"></i> 
                        <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Setting</th>
                                    <th style="width: 40%;">Value</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 10%;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category_settings as $setting): ?>
                                    <?php
                                        $key = htmlspecialchars($setting['config_key']);
                                        $value = htmlspecialchars($setting['value'] ?? '');
                                        $desc = htmlspecialchars($setting['description'] ?? '');
                                        $type = htmlspecialchars($setting['data_type']);
                                        $is_encrypted = $setting['is_encrypted'];
                                        $is_system = $setting['is_system'];
                                        $disabled = $is_system ? 'disabled' : '';
                                        $lock_icon = $is_system ? ' <i class="bi bi-lock-fill text-muted"></i>' : '';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $key; ?></strong>
                                            <?php echo $lock_icon; ?>
                                            <?php if ($is_encrypted): ?>
                                                <br><small class="text-muted">Encrypted</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($type === 'boolean'): ?>
                                                <select name="settings[<?php echo $key; ?>]" class="form-select" <?php echo $disabled; ?>>
                                                    <option value="1" <?php echo $value === '1' ? 'selected' : ''; ?>>Enabled</option>
                                                    <option value="0" <?php echo $value === '0' ? 'selected' : ''; ?>>Disabled</option>
                                                </select>
                                            <?php elseif ($type === 'json'): ?>
                                                <textarea name="settings[<?php echo $key; ?>]" class="form-control font-monospace" rows="3" <?php echo $disabled; ?>><?php echo $value; ?></textarea>
                                            <?php else: ?>
                                                <input type="<?php echo $type === 'integer' || $type === 'float' ? 'number' : 'text'; ?>"
                                                       name="settings[<?php echo $key; ?>]"
                                                       class="form-control"
                                                       value="<?php echo $value; ?>"
                                                       step="<?php echo $type === 'float' ? '0.01' : '1'; ?>"
                                                       <?php echo $disabled; ?>>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo $desc; ?></small></td>
                                        <td><span class="badge bg-secondary"><?php echo $type; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5><i class="bi bi-clock-history"></i> Recent Changes</h5>
    </div>
    <div class="card-body p-0">
        <?php
            $stmt = $db->query("
                SELECT sc.*, u.name as changed_by_name 
                FROM setting_changes sc
                LEFT JOIN users u ON sc.changed_by = u.id
                ORDER BY sc.created_at DESC
                LIMIT 10
            ");
            $recent_changes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (empty($recent_changes)): ?>
            <div class="text-center py-4 text-muted">No recent changes</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                            <th>Changed By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_changes as $change): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($change['setting_key']); ?></strong></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($change['old_value'] ?? 'null'); ?></small></td>
                                <td><small><?php echo htmlspecialchars($change['new_value']); ?></small></td>
                                <td><?php echo htmlspecialchars($change['changed_by_name'] ?? 'System'); ?></td>
                                <td><small><?php echo date('M d, Y H:i', strtotime($change['created_at'])); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (can('manage_integrations')): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="bi bi-plug-fill"></i> Integrations</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Connect external carriers, payment gateways, and notification providers. All credentials are stored encrypted.</p>
        <a href="integrations.php" class="btn btn-primary"><i class="bi bi-plug-fill"></i> Manage Integrations</a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
