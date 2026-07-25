<?php
/**
 * Contact form handler.
 * Validates input, checks CSRF, then inserts into contact_messages.
 * Redirects back to contact.php with a success or error flag.
 */

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/rate-limit.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php');
    exit;
}

// Rate limit: 5 messages per hour per IP
if (!rate_limit('contact_form', 5, 3600)) {
    header('Location: ../contact.php?error=' . urlencode('Too many submissions. Please wait an hour and try again.'));
    exit;
}

// ---- CSRF validation ----
$submitted_token = $_POST['csrf_token'] ?? '';
$session_token   = $_SESSION['csrf_token'] ?? '';

if (!hash_equals($session_token, $submitted_token)) {
    header('Location: ../contact.php?error=' . urlencode('Invalid security token. Please try again.'));
    exit;
}
// Rotate CSRF token after successful validation
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ---- Input sanitisation & validation ----
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$company = trim($_POST['company'] ?? '');
$phone   = trim($_POST['phone']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$category = trim($_POST['category'] ?? 'general');

$errors = [];

if ($name === '') {
    $errors[] = 'Name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if ($subject === '') {
    $errors[] = 'Subject is required.';
}
if (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
}

// Allowed categories (matches support_categories slugs + 'general')
$allowed_cats = ['general','shipment-issue','billing','technical','customs','feedback','complaint','partnership'];
if (!in_array($category, $allowed_cats, true)) {
    $category = 'general';
}

if (!empty($errors)) {
    header('Location: ../contact.php?error=' . urlencode(implode(' ', $errors)));
    exit;
}

// ---- Rate limiting (simple IP-based, 5 messages per hour) ----
$ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$cnt = db_fetch_one(
    'SELECT COUNT(*) AS c FROM contact_messages WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)',
    [':ip' => $ip]
);
if ((int)($cnt['c'] ?? 0) >= 5) {
    header('Location: ../contact.php?error=' . urlencode('Too many submissions. Please wait an hour and try again.'));
    exit;
}

// ---- Insert into contact_messages ----
try {
    db_execute(
        'INSERT INTO contact_messages
            (name, email, phone, subject, message, category, status, ip_address, user_agent, created_at, updated_at)
         VALUES
            (:name, :email, :phone, :subject, :message, :category, "new", :ip, :ua, NOW(), NOW())',
        [
            ':name'     => mb_substr($name,    0, 255),
            ':email'    => mb_substr($email,   0, 255),
            ':phone'    => mb_substr($phone,   0, 50),
            ':subject'  => mb_substr($subject, 0, 255),
            ':message'  => mb_substr($message, 0, 5000),
            ':category' => $category,
            ':ip'       => mb_substr($ip, 0, 45),
            ':ua'       => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]
    );
} catch (Exception $e) {
    // Log internally; show generic error to user
    error_log('contact_submit error: ' . $e->getMessage());
    header('Location: ../contact.php?error=' . urlencode('Something went wrong. Please try again later.'));
    exit;
}

// Also optionally create a support ticket
try {
    $ticket_number = 'TKT-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    db_execute(
        'INSERT INTO support_tickets
            (ticket_number, subject, description, status, priority, category, created_at, updated_at)
         VALUES
            (:tn, :subject, :desc, "open", "medium", :cat, NOW(), NOW())',
        [
            ':tn'      => $ticket_number,
            ':subject' => mb_substr($subject, 0, 255),
            ':desc'    => mb_substr($message, 0, 5000),
            ':cat'     => $category,
        ]
    );
} catch (Exception $e) {
    // Non-fatal — ticket creation failing is OK
    error_log('support_ticket creation error: ' . $e->getMessage());
}

// ---- Done ----
header('Location: ../contact.php?sent=1');
exit;
