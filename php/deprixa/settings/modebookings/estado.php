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

// asignamos la función de conexion a una variable
$con = conexion();
// recuperamos el id del usuario enviado por ajax
$id = $_POST['id'];
// recuperamos el estado del usuario usando prepared statement
$stmt = $con->prepare("SELECT estado FROM mode_bookings WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$estado = $resultado->fetch_assoc();
// verificamos si esta activo o inactivo
if($estado['estado'] == '1'){
	// Cambiamos el estado a inactivo
	$updateStmt = $con->prepare("UPDATE mode_bookings SET estado='0' WHERE id=?");
} else {
	// Cambiamos el estado a activo
	$updateStmt = $con->prepare("UPDATE mode_bookings SET estado='1' WHERE id=?");
}
$updateStmt->bind_param("i", $id);
$updateStmt->execute();
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>