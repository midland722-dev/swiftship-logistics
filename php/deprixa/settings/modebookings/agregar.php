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
// recuperamos y asignamos a variables los campos enviados por ajax metodo POST
$name = $_POST['name'];
$services = $_POST['services'];
$deliverytime = $_POST['deliverytime'];
$observations = $_POST['observations'];
// verificamos si esta marcado el check box activo
if(isset($_POST['estado']))
$estado = $_POST['estado'];
else
$estado = 0;


// Cotroles Basicos, evitar campos vacios
if(empty($name)){
	echo json_encode(array('msg' => 'nomvacio')); //retornamos mensaje de error
	exit(); // salimos de la ejecución
}
elseif(empty($services)){
	echo json_encode(array('msg' => 'apevacio'));
	exit();
}
elseif(empty($deliverytime)){
	echo json_encode(array('msg' => 'telvacio'));
	exit();
}
elseif(empty($observations)){
	echo json_encode(array('msg' => 'emavacio'));
	exit();
}




// insertamos en la base de datos - hacemos una consulta SQL
$consulta = "INSERT INTO mode_bookings (name,services,deliverytime,observations,estado) VALUES('$name','$services','$deliverytime','$observations','$estado')";
$con->query($consulta); // enviamos la consulta al método query
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>