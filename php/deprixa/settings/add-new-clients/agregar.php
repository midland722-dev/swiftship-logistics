<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA -  logistics Worldwide Software                               *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: osorio2380@yahoo.es                                            *
// * Website: http://www.jaom.info                                         *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************
 
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once('../../database.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$submitted_token = $_POST['csrf_token'] ?? '';
$session_token   = $_SESSION['csrf_token'] ?? '';

if (!hash_equals($session_token, $submitted_token)) {
    echo json_encode(array('msg' => 'Invalid security token. Please refresh and try again.'));
    exit;
}

$name = $_POST['name'];
$company = $_POST['company'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$country = $_POST['country'];
$state = $_POST['state'];
$zipcode = $_POST['zipcode'];
$password = $_POST['password'];

if(isset($_POST['estado']))
$estado = $_POST['estado'];
else
$estado = 0;

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$checkStmt = $dbConn->prepare("SELECT email FROM tbl_clients WHERE email=:email AND name=:name");
$checkStmt->execute([':email' => $email, ':name' => $name]);
if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(array('msg' => 'exists'));
    exit;
}

$insertStmt = $dbConn->prepare("INSERT INTO tbl_clients (name, address, email, phone, password, company, country, state, zipcode, estado, date) VALUES (:name, :address, :email, :phone, :password, :company, :country, :state, :zipcode, :estado, CURDATE())");
$insertStmt->execute([
    ':name' => $name,
    ':address' => $address,
    ':email' => $email,
    ':phone' => $phone,
    ':password' => $hashedPassword,
    ':company' => $company,
    ':country' => $country,
    ':state' => $state,
    ':zipcode' => $zipcode,
    ':estado' => $estado
]);

echo json_encode(array('msg' => 'ok'));
?>