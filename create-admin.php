<?php
/**
 * One-time admin user creator for Railway deployment.
 * 
 * Usage:
 *   1. Upload this file to your Railway PHP service root
 *   2. Visit: https://your-project.up.railway.app/create-admin.php
 *   3. Login with the created credentials
 * 4. DELETE THIS FILE AFTER USE
 * 
 * Creates an admin user in the users table.
 */

$dbUrl = getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL') ?: 'mysql://root:sMwDwdAvrsolmefBQKatNKWrEpRBZGxS@mysql.railway.internal:3306/railway';

if (!str_starts_with($dbUrl, 'mysql://')) {
    die("Error: DATABASE_URL or MYSQL_PRIVATE_URL not found.\n");
}

$url = parse_url($dbUrl);
$dbHost = $url['host'] ?? 'localhost';
$dbPort = $url['port'] ?? '3306';
$dbName = ltrim($url['path'] ?? '', '/');
$dbUser = $url['user'] ?? 'root';
$dbPass = $url['pass'] ?? '';

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? 'Admin User');
    $role = $_POST['role'] ?? 'admin';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            // Check if users table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $usersTableExists = $stmt->fetchColumn();
            
            if (!$usersTableExists) {
                $error = "Error: 'users' table does not exist. Please import the database first.";
            } else {
                // Check if user already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing user
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, role = ?, full_name = ?, is_active = 1 WHERE email = ?");
                    $stmt->execute([$passwordHash, $role, $fullName, $email]);
                    $message = "Admin user updated successfully! You can now login with:<br><strong>Email:</strong> $email<br><strong>Password:</strong> $password";
                } else {
                    // Create new admin user
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Check column names
                    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
                    $hasPasswordHash = $stmt->fetchColumn();
                    $passwordColumn = $hasPasswordHash ? 'password_hash' : 'password';
                    
                    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'first_name'");
                    $hasFirstName = $stmt->fetchColumn();
                    
                    if ($hasFirstName) {
                        $nameFields = "first_name, last_name";
                        $nameValues = explode(' ', $fullName, 2);
                        $firstName = $nameValues[0] ?? $fullName;
                        $lastName = $nameValues[1] ?? '';
                        $sql = "INSERT INTO users (email, $passwordColumn, first_name, last_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$email, $passwordHash, $firstName, $lastName, $role]);
                    } else {
                        $sql = "INSERT INTO users (email, $passwordColumn, name, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$email, $passwordHash, $fullName, $role]);
                    }
                    
                    $message = "Admin user created successfully! You can now login with:<br><strong>Email:</strong> $email<br><strong>Password:</strong> $password";
                }
            }
        } catch (Exception $e) {
            $error = "Error creating admin: " . $e->getMessage();
        }
    }
}

// Self-destruct after successful creation
if ($message && isset($_POST['confirm_delete'])) {
    $self = __FILE__;
    if (file_exists($self)) {
        unlink($self);
        $message .= "<br><br><strong>Security:</strong> This script has been deleted.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Create Admin User</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                            <form method="POST" style="display: none;">
                                <input type="hidden" name="confirm_delete" value="1">
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Create an admin user to access the PHP dashboard. This script will delete itself after use.</p>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="admin@ascl-logistics.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="text" class="form-control" id="password" name="password" required value="admin123" minlength="6">
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="Admin User">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Create Admin User</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="/admin" class="btn btn-outline-secondary w-100">Go to Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
