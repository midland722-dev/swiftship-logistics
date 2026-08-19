<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$security = new LoginSecurity(getDB());
$error = '';
$success = '';
$remaining_attempts = null;
$lockout_info = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!$security->validateCsrfToken($csrf_token)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username/email and password.';
        } else {
            $lockout = $security->isLockedOut($username);
            if ($lockout['locked']) {
                $error = "Account temporarily locked due to too many failed attempts. Please try again in {$lockout['remaining']} minutes.";
                $lockout_info = $lockout;
            } else {
                $db = getDB();
                $user = null;
                
                try {
                    $tables = $db->query("SHOW TABLES LIKE 'manager_admin'")->fetchAll(PDO::FETCH_COLUMN);
                    $has_manager_admin = count($tables) > 0;
                    
                    if ($has_manager_admin) {
                        $sql = "SELECT id, username, email, password, full_name, role, is_active FROM manager_admin WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$username, $username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    
                    if (!$user) {
                        $users_columns = $db->query("SHOW COLUMNS FROM users LIKE 'password_hash'")->fetchAll();
                        $password_column = count($users_columns) > 0 ? 'password_hash' : 'password';
                        
                        $name_fields = count($db->query("SHOW COLUMNS FROM users LIKE 'first_name'")->fetchAll()) > 0 
                            ? "CONCAT(first_name, ' ', last_name)" 
                            : "name";
                        
                        $sql = "SELECT id, email as username, email, $password_column as user_password, $name_fields as full_name, role, is_active FROM users WHERE email = ? AND role IN ('admin', 'staff') AND is_active = 1 LIMIT 1";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user) {
                            $user['password'] = $user['user_password'];
                            unset($user['user_password']);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Login DB error: " . $e->getMessage());
                    $error = 'Authentication system error. Please contact support.';
                }
                
                if ($user && password_verify($password, $user['password'])) {
                    $security->clearAttempts($username);
                    $security->clearAttempts($ip_address);
                    $security->regenerateSession();
                    
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['full_name'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['ip_address'] = $ip_address;
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    
                    if (isset($_POST['remember'])) {
                        $secureFlag = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                        setcookie('admin_login', (string)$user['id'], time() + (86400 * 30), '/', '', $secureFlag, true);
                    }
                    
                    try {
                        $db->prepare("
                            INSERT INTO activity_logs (user_id, action, entity_type, description, ip_address, user_agent, created_at)
                            VALUES (:uid, 'login_success', 'auth', :desc, :ip, :ua, NOW())
                        ")->execute([
                            ':uid' => $user['id'],
                            ':desc' => "Successful login for user: {$user['username']}",
                            ':ip' => $ip_address,
                            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
                        ]);
                    } catch (Exception $e) {
                        error_log("Activity log failed: " . $e->getMessage());
                    }
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $remaining = $security->recordFailedAttempt($username);
                    $remaining_attempts = max(0, $remaining);
                    
                    $error = 'Invalid username or password.';
                    if ($remaining_attempts <= 2) {
                        $error .= " <strong>Warning: {$remaining_attempts} attempts remaining before lockout.</strong>";
                    }
                    
                    try {
                        $db->prepare("
                            INSERT INTO activity_logs (user_id, action, entity_type, description, ip_address, user_agent, created_at)
                            VALUES (NULL, 'login_failed', 'auth', :desc, :ip, :ua, NOW())
                        ")->execute([
                            ':desc' => "Failed login attempt for: $username from IP: $ip_address",
                            ':ip' => $ip_address,
                            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
                        ]);
                    } catch (Exception $e) {
                        error_log("Activity log failed: " . $e->getMessage());
                    }
                }
            }
        }
    }
}

$csrf_token = $security->generateCsrfToken();

$page_title = 'Admin Login - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="css/admin.css" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h2><?php echo htmlspecialchars(SITE_NAME); ?></h2>
                <p>Admin Dashboard Login</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username or Email" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" autocomplete="username">
                        <label for="username"><i class="bi bi-person"></i> Username or Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <label for="password"><i class="bi bi-lock"></i> Password</label>
                        <div id="passwordStrength" class="mt-1" style="font-size: 0.8rem;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="submitBtn">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </button>
                </form>
                
                <div class="back-link">
                    <a href="../index.php"><i class="bi bi-arrow-left"></i> Back to Website</a>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded d-none" id="defaultCredsBox">
                    <small class="text-muted">
                        <strong>Setup Complete:</strong> Use your configured admin credentials to log in. Remove this login page in production.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const loginForm = document.getElementById('loginForm');
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('passwordStrength');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('at least 8 characters');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('uppercase letter');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('lowercase letter');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('number');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('special character');
            
            const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
            const colors = ['danger', 'warning', 'warning', 'info', 'success', 'success'];
            
            return {
                score: strength,
                label: levels[strength] || 'Very Weak',
                color: colors[strength] || 'danger',
                feedback: feedback
            };
        }
        
        passwordInput.addEventListener('input', function() {
            const result = checkPasswordStrength(this.value);
            strengthIndicator.innerHTML = '<span class="text-' + result.color + '">' + result.label + '</span>';
            if (result.feedback.length > 0 && this.value.length > 0) {
                strengthIndicator.innerHTML += ' <small class="text-muted">(Add: ' + result.feedback.slice(0, 2).join(', ') + ')</small>';
            }
        });
        
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';
        });
    </script>
</body>
</html>
