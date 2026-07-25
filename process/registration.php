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
	require_once('../deprixa/database.php');

	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$name = $fname.' '.$lname;
	$company = $_POST['company'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$address=$_POST['address'];
	$country = $_POST['country'];
	$state = $_POST['state'];
	$zipcode = $_POST['zipcode'];
	$estado = $_POST['estado'];
	$password = $_POST['password'];

	
	$sql1 =mysql_query("SELECT email FROM tbl_clients WHERE email='$email'");
						if($row=mysql_fetch_array($sql1)){							
							 echo "<script type=\"text/javascript\">
									alert(\"$email This is registered as Mail, enter a different email, thanks.\");
									window.location = \"https://quadruple-logistics.com/user/login.php\"
								</script>"; 							
						}else{
							$sql1="INSERT INTO tbl_clients (name,email,phone,address,password,country,state,zipcode,estado,company,date) VALUES 	
							('$name','$email','$phone','$address','$password','$country','$state','$zipcode','$estado','$company',curdate())";
						}
	dbQuery($sql1);
	
	$result1 =  mysql_query("SELECT * FROM company");
	while($row = mysql_fetch_array($result1)) {
	
	$to  = $row["bemail"];
	$address  = $row["caddress"];
	$namecompany  = $row["cname"];
    // subject

    $subject = 'Welcome to '.$row["cname"].'';
	$from = $row["bemail"]; 

    // message
	$text_message    = "Hi ".$name." this is our address, <br /><br /> <strong> ".$address." Please consider your environmental responsibility. Before printing this e-mail message, ask yourself whether you really need a hard copy.</strong><br /><br /> IMPORTANT:</strong> The contents of this email and any attachments are confidential. They are intended for the named recipient(s) only. If you have received this email by mistake, please notify the sender immediately and do not disclose the contents to anyone or make copies thereof.";			   
	
	
	// HTML email starts here
	
	$message  = "<html><body>";	
	$message .= "<table width='100%' bgcolor='#e0e0e0' cellpadding='0' cellspacing='0' border='0'>";	
	$message .= "<tr><td>";	
	$message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:800px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";		
	$message .= "<thead>
				<tr height='80'>
					<th colspan='4' style='background-color:#f5f5f5; border-bottom:solid 1px #bdbdbd; font-family:Verdana, Geneva, sans-serif; color:#333; font-size:23px;' >".$namecompany."</th>
				</tr>
				</thead>";
		
	$message .= "<tbody>
				
				<tr>
					<td colspan='4' style='padding:15px;'>
						<p><img src='".$row['website']."deprixa/image_logo.php?id=1'></p>
						<br><br>
						<p style='font-size:14px;'>Customer Name: <strong>".$name."</strong></p>
						<hr/>
						<p style='font-size:14px;'>Username: <strong> ".$email."</strong></p>						
						<p style='font-size:14px;'>Password: <strong> ".$password."</strong></p>
						<br><br>
						<p><a style='background:#eee;color:#333;padding:10px;' href='".$row["website"]."login.php' >Customer Login</a></p>
						<br><br>						
						<p style='font-size:13px; font-family:Verdana, Geneva, sans-serif;'>".$text_message.".</p>
					</td>
				</tr>												
				</tbody>";				
	$message .= "</table>";	
	$message .= "</td></tr>";
	$message .= "</table>";	
	$message .= "</body></html>";

	}

    // To send HTML mail, the Content-type header must be set

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
   // Additional headers
    $headers .= 'From: '.$from."\r\n";	
    // this line checks that we have a valid email address
	mail($to, $subject, $message, $headers); //This method sends the mail.
	mail($email, $subject, $message, $headers); //This method sends the mail.

   echo "<script type=\"text/javascript\">
						alert(\"$name Thank you very much for registering.\");
						window.location = \"https://quadruple-logistics.com/user/login.php\"
					</script>"; 
   
   ?>