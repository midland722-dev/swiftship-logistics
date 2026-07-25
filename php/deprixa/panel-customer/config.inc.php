<?php 
session_start();
require_once('../database.php');
require_once('../funciones.php');

$action = $_GET['action'];

switch($action) {
	
	 case 'update-bank':	
	updateBank();	
	break;
}

//le dimos click al boton grabar?
if (isset($_POST['guardar']))
{
$nombre = $_FILES['imagen']['name'];
$imagen_temporal = $_FILES['imagen']['tmp_name'];
$type = $_FILES['imagen']['type'];
//archivo temporal en binario
$itmp = fopen($imagen_temporal, 'r+b');
$imagen = fread($itmp, filesize($imagen_temporal));
fclose($itmp);
//escapando los caracteres
$imagen = mysql_real_escape_string($imagen);
$cons_no= mysql_real_escape_string($_POST['cons_no'] ?? '');
$office= mysql_real_escape_string($_POST['office'] ?? '');
													
$respuesta = mysql_query("INSERT INTO upload_image_bank(cons_no,nombre_imagen,imagen,tipo,office,date) VALUES('$cons_no','$nombre','$imagen','$type','$office',curdate())", $dbConn);

//redireccionamos
header("Location: paybill.php?".($respuesta ? 'ok' : 'error'));
}
//guardado OK
if (isset($_GET['ok']))
{
echo '<p>Saved successfully</p>';}
//si no se guardo de manera correcta?
if (isset($_GET['error']))
{
echo '<p>Occurred an error when it comes to the inclusion...</p>';}


function updateBank(){

$cid = (int)$_GET['cid'];
$sql = "UPDATE courier_online SET payment='OK', paymode='Bank' WHERE cid = '$cid'";	
dbQuery($sql);

}

?>