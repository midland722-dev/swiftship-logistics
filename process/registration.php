<?php
/**
 * Customer registration (legacy Deprixa panel).
 *
 * Secure rewrite:
 *  - Uses mysqli prepared statements (no SQL injection).
 *  - Hashes passwords with password_hash (no plaintext storage).
 *  - Validates required inputs.
 *  - Does NOT email the plaintext password.
 *  - Redirects to the correct brand domain (ascl-logistics.com).
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once __DIR__ . '/../php/deprixa/database.php';

global $dbConn;

$required = ['fname', 'lname', 'email', 'phone', 'address', 'country', 'password'];
$missing = [];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        $missing[] = $field;
    }
}
if ($missing) {
    http_response_code(400);
    echo "Missing required fields: " . htmlspecialchars(implode(', ', $missing));
    exit;
}

$fname   = trim($_POST['fname']);
$lname   = trim($_POST['lname']);
$name    = $fname . ' ' . $lname;
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$email   = trim($_POST['email']);
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = trim($_POST['address']);
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$state   = isset($_POST['state']) ? trim($_POST['state']) : '';
$zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
$estado  = isset($_POST['estado']) ? trim($_POST['estado']) : '';
$password = $_POST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Invalid email address.";
    exit;
}
if (strlen($password) < 8) {
    http_response_code(400);
    echo "Password must be at least 8 characters.";
    exit;
}

// Check for an existing email (prepared statement).
$check = $dbConn->prepare("SELECT email FROM tbl_clients WHERE email = ?");
$check->bind_param('s', $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    echo "<script type=\"text/javascript\">
        alert(\"" . htmlspecialchars($email) . " is already registered. Please use a different email.\");
        window.location = \"https://ascl-logistics.com/user/login.php\"
    </script>";
    exit;
}
$check->close();

$hash = password_hash($password, PASSWORD_BCRYPT);

$insert = $dbConn->prepare(
    "INSERT INTO tbl_clients (name, email, phone, address, password, country, state, zipcode, estado, company, date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())"
);
$insert->bind_param('ssssssssss', $name, $email, $phone, $address, $hash, $country, $state, $zipcode, $estado, $company);
if (!$insert->execute()) {
    http_response_code(500);
    echo "Registration failed. Please try again later.";
    exit;
}
$insert->close();

// Notify the company admin (no plaintext password is ever sent by email).
$companyRes = $dbConn->query("SELECT bemail, cname, caddress, website FROM company LIMIT 1");
$nameEsc    = htmlspecialchars($name, ENT_QUOTES);
$emailEsc   = htmlspecialchars($email, ENT_QUOTES);
if ($companyRes && $row = $companyRes->fetch_assoc()) {
    $to          = $row['bemail'];
    $namecompany = $row['cname'];
    $addressLine = $row['caddress'];
    $subject     = 'New customer registration - ' . $namecompany;
    $from        = $to;

    $message  = "<html><body>";
    $message .= "<p>Hello " . htmlspecialchars($namecompany, ENT_QUOTES) . ",</p>";
    $message .= "<p>A new customer has registered:</p>";
    $message .= "<p>Name: <strong>" . $nameEsc . "</strong><br />";
    $message .= "Email: <strong>" . $emailEsc . "</strong></p>";
    $message .= "<p>This is an automated notification; the customer's password was stored securely and is not included here.</p>";
    $message .= "</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $headers .= "From: " . $from . "\r\n";
    if ($to) {
        mail($to, $subject, $message, $headers);
    }
}

echo "<script type=\"text/javascript\">
    alert(\"" . $nameEsc . ", thank you very much for registering.\");
    window.location = \"https://ascl-logistics.com/user/login.php\"
</script>";
