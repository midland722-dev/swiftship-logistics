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
// recuperamos el cid del off_name enviado por ajax
$id = mysql_real_escape_string($_POST['id']);
// recuperamos y asignamos a variables los campos enviados por ajax metodo POST
$name = mysql_real_escape_string($_POST['name']);
$address = mysql_real_escape_string($_POST['address']);
$phone = mysql_real_escape_string($_POST['phone']);
$email = mysql_real_escape_string($_POST['email']);
// verificamos si esta marcado el check box activo
if(isset($_POST['estado']))
$estado = mysql_real_escape_string($_POST['estado']);
else
$estado = 0;


// Cotroles Basicos, evitar campos vacios
if(empty($name)){
	echo json_encode(array('msg' => 'nomvacio')); //retornamos mensaje de error
	exit(); // salimos de la ejecución
}
elseif(empty($address)){
	echo json_encode(array('msg' => 'telvacio'));
	exit();
}
elseif(empty($phone)){
	echo json_encode(array('msg' => 'usuvacio'));
	exit();
}
elseif(empty($email)){
	echo json_encode(array('msg' => 'emavacio'));
	exit();
}

else{	
	// actualizamos la información
	$consulta = "UPDATE tbl_clients SET name='$name',  address='$address', email='$email', phone='$phone', estado='$estado' WHERE id='$id'";
}

// enviamos la consulta al método query
$con->query($consulta);
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>