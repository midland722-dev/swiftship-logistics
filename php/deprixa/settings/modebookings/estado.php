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
// asignamos la función de conexion a una variable
$con = conexion();
// recuperamos el id del usuario enviado por ajax
$id = $_POST['id'];
// recuperamos el estado del usuario hacemos una consulta SQL
$q = "SELECT estado FROM mode_bookings WHERE id='$id'";
// asignamos a una variable la consulta devuelta por el método query
$resultado = $con->query($q);
// camvertimos en array la consulta utilizando el método fetch_assoc
$estado = $resultado->fetch_assoc();
// verificamos si esta activo o inactivo
if($estado['estado'] == '1'){
	// Cambiamos el estado a inactivo
	$q = "UPDATE mode_bookings SET estado='0' WHERE id='$id'";
}
else{
	// Cambiamos el estado a activo
	$q = "UPDATE mode_bookings SET estado='1' WHERE id='$id'";
}
// pasamos la consulta según el resultado de la verificación
$con->query($q);
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>