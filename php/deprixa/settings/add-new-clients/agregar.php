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


	$name = $_POST['name'];
	$company = $_POST['company'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$address=$_POST['address'];
	$country = $_POST['country'];
	$state = $_POST['state'];
	$zipcode = $_POST['zipcode'];
	$password = $_POST['password'];
	
	// verificamos si esta marcado el check box activo
	if(isset($_POST['estado']))
	$estado = $_POST['estado'];
	else
	$estado = 0;

	$sql1 =mysql_query("SELECT email FROM tbl_clients WHERE email='$email' AND name='$name'");
			if($row=mysql_fetch_array($sql1)){							
				 echo "<script type=\"text/javascript\">
						alert(\"The email $email already is are registered in the database, by Please enter data different, thank you.\");
						window.location = \"../../customer.php\"
					</script>"; 							
			}else{
				$sql1="INSERT INTO tbl_clients (name, address,email, phone, password, company, country, state, zipcode, estado,date) VALUES 	
				('$name','$address', '$email', '$phone', '$password', '$company', '$country', '$state', '$zipcode',  '$estado',curdate())";
			}
	dbQuery($sql1);
	
	echo "<script type=\"text/javascript\">
						alert(\"Thank you very much for registering.\");
						window.location = \"../../customer.php\"
					</script>"; 




// insertamos en la base de datos - hacemos una consulta SQL
$consulta = "INSERT INTO tbl_clients (name, password, address, email, phone, estado)
			VALUES ('$name','$password', '$address', '$email', '$phone', '$estado')";
$con->query($consulta); // enviamos la consulta al método query
// retornamos un mensaje de confirmación
echo json_encode(array('msg' => 'ok'));

?>