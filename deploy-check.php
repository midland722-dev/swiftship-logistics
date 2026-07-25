<?php
/**
 * Shared hosting deployment checklist and diagnostic.
 *
 * Visit this file in your browser after upload to verify PHP is working.
 * DELETE THIS FILE after confirming — it exposes server configuration.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== American Shipping & Logistics — Deployment Diagnostic ===\n\n";

echo "1. PHP Version:\n";
echo '   ' . phpversion() . "\n\n";

echo "2. PDO MySQL Extension:\n";
echo '   ' . (extension_loaded('pdo_mysql') ? 'LOADED' : 'MISSING — contact host') . "\n\n";

echo "3. mbstring Extension:\n";
echo '   ' . (extension_loaded('mbstring') ? 'LOADED' : 'MISSING — contact host') . "\n\n";

echo "4. JSON Extension:\n";
echo '   ' . (extension_loaded('json') ? 'LOADED' : 'MISSING — contact host') . "\n\n";

echo "5. Database Connection Test:\n";
try {
    require_once __DIR__ . '/php/config/db.php';
    $pdo = db();
    echo '   SUCCESS — connected to ' . DB_NAME . '@' . DB_HOST . "\n\n";
} catch (Exception $e) {
    echo '   FAILED: ' . $e->getMessage() . "\n\n";
}

echo "6. File Permissions:\n";
$writable = [
    'logs/' => is_writable(__DIR__ . '/logs'),
];
foreach ($writable as $dir => $ok) {
    echo '   ' . $dir . ': ' . ($ok ? 'WRITABLE' : 'NOT WRITABLE — chmod 755 or 775') . "\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Set DB credentials in .htaccess:\n";
echo "   SetEnv DB_HOST localhost\n";
echo "   SetEnv DB_NAME shipping_db\n";
echo "   SetEnv DB_USER dbuser\n";
echo "   SetEnv DB_PASS dbpassword\n\n";
echo "2. Run migrations via browser:\n";
echo "   Visit: https://yourdomain.com/database/migrations/apply_0001.php\n";
echo "   Then apply_0002.php through apply_0016.php in order\n\n";
echo "3. Run seeders:\n";
echo "   Visit: https://yourdomain.com/database/seeders/001_services.php\n";
echo "   Then 002_company.php and 003_support_categories.php\n\n";
echo "4. DELETE this file after successful setup!\n";
