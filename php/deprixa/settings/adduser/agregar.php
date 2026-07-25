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

	$sql1 =mysql_query("SELECT email,name FROM manager_user WHERE email='$email' OR name='$name'");
			if($row=mysql_fetch_array($sql1)){							
				 echo "<script type=\"text/javascript\">
						alert(\"The email $email and name of user $name already is are registered in the database, by Please enter data different, thank you.\");
						window.location = \"../../add-new-users.php\"
					</script>"; 							
			}else{
				$sql1="INSERT INTO manager_user (name_parson,name,email,phone,office,role,pwd,estado,type,date) VALUES 	
				('$name_parson','$name','$email','$phone','$office','$role','$pwd','$estado','$type',curdate())";
			}
	dbQuery($sql1);
	
	echo "<script type=\"text/javascript\">
						alert(\"Thank you very much for registering.\");
						window.location = \"../../add-new-users.php\"
					</script>"; 

?>