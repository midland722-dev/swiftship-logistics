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
 

include('../../database-settings.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$submitted_token = $_POST['csrf_token'] ?? '';
$session_token   = $_SESSION['csrf_token'] ?? '';

if (!hash_equals($session_token, $submitted_token)) {
    echo json_encode(array('msg' => 'Invalid security token. Please refresh and try again.'));
    exit;
}

// recuperamos y asignamos a variables los campos enviados por ajax metodo POST
$name_parson = $_POST['name_parson'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$office = $_POST['office'];
$role = $_POST['role'];
$pwd = $_POST['pwd'];
// verificamos si esta marcado el check box activo
if(isset($_POST['estado']))
$estado = $_POST['estado'];
else
$estado = 0;

if(isset($_POST['type']))
$type = $_POST['type'];
else
$type = 0;


// Cotroles Basicos, evitar campos vacios
if(empty($name_parson)){
	echo json_encode(array('msg' => 'nompavacio')); //retornamos mensaje de error
	exit(); // salimos de la ejecución
}
if(empty($name)){
	echo json_encode(array('msg' => 'nomvacio')); //retornamos mensaje de error
	exit(); // salimos de la ejecución
}
elseif(empty($email)){
	echo json_encode(array('msg' => 'apevacio'));
	exit();
}
elseif(empty($phone)){
	echo json_encode(array('msg' => 'telvacio'));
	exit();
}
elseif(empty($office)){
	echo json_encode(array('msg' => 'emavacio'));
	exit();
}
elseif(empty($role)){
	echo json_encode(array('msg' => 'usuvacio'));
	exit();
}
elseif(empty($pwd)){
	echo json_encode(array('msg' => 'pasvacio'));
	exit();
}

// insertamos en la base de datos usando prepared statement
$hashedPassword = password_hash($pwd, PASSWORD_DEFAULT);
$consulta = "INSERT INTO manager_admin (name_parson,name,email,phone,office,role,pwd,estado,type,date) VALUES(:name_parson,:name,:email,:phone,:office,:role,:pwd,:estado,:type,curdate())";
$stmt = $con->prepare($consulta);
$stmt->execute([
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

// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>