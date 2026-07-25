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
$cid = $_POST['cid'];
// recuperamos los datos del usuario hacemos una consulta SQL
$q = "SELECT * FROM manager_user WHERE cid=$cid";
// enviamos la consulta al método query
$result = $con->query($q);
// creamos una variable del tipo array la cual almacena todos los datos del usuario
$datos = array();
while ($row=$result->fetch_assoc()) {
	$datos[]=$row; 
}
// convertimos el array al formato json y mostramos
echo json_encode($datos);

?>