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
session_start();
require_once('../../database.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$submitted_token = $_POST['csrf_token'] ?? '';
$session_token   = $_SESSION['csrf_token'] ?? '';

if (!hash_equals($session_token, $submitted_token)) {
    echo json_encode(array('msg' => 'Invalid security token. Please refresh and try again.'));
    exit;
}

$name_parson = $_POST['name_parson'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$office = $_POST['office'];
$role = $_POST['role'];
$pwd = $_POST['pwd'];

if(isset($_POST['estado']))
$estado = $_POST['estado'];
else
$estado = 0;

if(isset($_POST['type']))
$type = $_POST['type'];
else
$type = 0;

$hashedPassword = password_hash($pwd, PASSWORD_DEFAULT);

$checkStmt = $dbConn->prepare("SELECT email,name FROM manager_user WHERE email=:email OR name=:name");
$checkStmt->execute([':email' => $email, ':name' => $name]);
if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(array('msg' => 'exists'));
    exit;
}

$insertStmt = $dbConn->prepare("INSERT INTO manager_user (name_parson,name,email,phone,office,role,pwd,estado,type,date) VALUES (:name_parson,:name,:email,:phone,:office,:role,:pwd,:estado,:type,CURDATE())");
$insertStmt->execute([
    ':name_parson' => $name_parson,
    ':name' => $name,
    ':email' => $email,
    ':phone' => $phone,
    ':office' => $office,
    ':role' => $role,
    ':pwd' => $hashedPassword,
    ':estado' => $estado,
    ':type' => $type
]);

echo json_encode(array('msg' => 'ok'));

?>