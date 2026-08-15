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
// recuperamos el id del off_name enviado por ajax
$cid = mysql_real_escape_string($_POST['cid']);
// recuperamos y asignamos a variables los campos enviados por ajax metodo POST
$name = mysql_real_escape_string($_POST['name']);
$email = mysql_real_escape_string($_POST['email']);
$phone = mysql_real_escape_string($_POST['phone']);
$office = mysql_real_escape_string($_POST['office']);
$role = mysql_real_escape_string($_POST['role']);
$pwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
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

else{	
	// actualizamos la información
	$consulta = "UPDATE manager_user SET name='$name', email='$email', phone='$phone', office='$office', role='$role', password='$pwd', estado='$estado' WHERE cid='$cid'";
}

// enviamos la consulta al método query
$con->query($consulta);
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>