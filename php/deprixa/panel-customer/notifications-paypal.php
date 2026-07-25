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
require_once('../library.php');	
require_once('../database.php');
isUser();	
?>
<!DOCTYPE html>
<html>
 
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Page Description and Author -->
	<meta name="description" content="Courier Deprixa V2.5 "/>
	<meta name="keywords" content="Courier DEPRIXA-Integral Web System" />
	<meta name="author" content="Jaomweb">

	<!-- App Favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">

	<!-- App title -->
	<title>DEPRIXA | Paypal Notifications </title>
	
	<!-- Switchery css -->
	<link href="assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

	<!-- App CSS -->
	<link href="assets/css/style.css" rel="stylesheet" type="text/css" />
	
		<!-- DataTables -->
	<link href="assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<!-- Responsive datatable examples -->
	<link href="assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	
	<link rel="stylesheet" href="../bower_components/bootstrap/dist/css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="../bower_components/animate.css/animate.css" type="text/css" />
    <link rel="stylesheet" href="../bower_components/font-awesome/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="../bower_components/simple-line-icons/css/simple-line-icons.css" type="text/css" />
    <script type= "text/javascript" src="../../process/countries.js"></script> 
	
</head>
<body>
	<?php
		include("header_customer.php");
	?>							

		<!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="wrapper">
            <div class="container">

				<!-- Page-Title -->
				<?php
				include("../icon_settings.php");
				?>
				<div class="row">
                    <div class="col-sm-12">
                        <div class="card-box table-responsive">
                            <h4 class="m-t-0 header-title"><b>Paypall Notifications</b></h4>
								<br><br>
									<table border="0" align="center">
										<tbody>	
											<?php
												// For data security
												class vpb_rc4_algorithm
												{
												   public function vpb_encodes($a,$b){
													  for($i,$c;$i<256;$i++)$c[$i]=$i;
													  for($i=0,$d,$e,$g=strlen($a);$i<256;$i++){
														 $d=($d+$c[$i]+ord($a[$i%$g]))%256;
														 $e=$c[$i];
														 $c[$i]=$c[$d];
														 $c[$d]=$e;
													  }
													  for($y,$i,$d=0,$f;$y<strlen($b);$y++){
														 $i=($i+1)%256;
														 $d=($d+$c[$i])%256;
														 $e=$c[$i];
														 $c[$i]=$c[$d];
														 $c[$d]=$e;
														 $f.=chr(ord($b[$y])^$c[($c[$i]+$c[$d])%256]);
													  }
													  return $f;
												   }
												   public function vpb_decodes($a,$b){return vpb_rc4_algorithm::vpb_encodes($a,$b);}
												}
												function vpb_decrpt_url_data($data)
												{
													$key  = 'a7e88837b63bf2941ef819dc8ca282';
													$v_data = base64_decode($data);
													$plain_text = vpb_rc4_algorithm::vpb_decodes($key,$v_data);
													return $plain_text;
												}
												if(isset($_GET["crypt"]) && !empty($_GET["crypt"]))
												{
													$crypted_data = vpb_decrpt_url_data(strip_tags($_GET["crypt"]));
													list($payment_status, $user_email) = explode(';', $crypted_data, 2);
													
													if($payment_status == "user-paid-successfully")
													{																												
														$sql = "UPDATE courier_online SET payment='OK', paymode='Paypal' WHERE office='$user_email' ";
														dbQuery($sql);

														
														echo '<div class="col-md-6">
															  <div class="templatemo-alerts">
																<div class="row">
																  <div class="col-md-12">
																	<div class="alert alert-success alert-dismissible" role="alert">
																	  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
																	  <strong>You have made payment successfully and your verified email is <b>'.$user_email.'</b>.
																	</div>
																   
																			  
																  </div>  
																</div>            
															  </div>              
															</div>';
													}
													elseif($payment_status == "user-canceled-payment")
													{
															
														echo '<div class="col-md-6">
															  <div class="templatemo-alerts">
																<div class="row">
																  <div class="col-md-12">
																	<div class="alert alert-success alert-dismissible" role="alert">
																	  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
																	 You seem to have canceled your payment process and so, no payment has been made yet and your email address as set in the system is <b>'.$user_email.'           </div>
																   
																			  
																  </div>  
																</div>            
															  </div>              
															</div>';
													}
													else
													{
															echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
																	<button type="button" class="close" data-dismiss="alert"
																			aria-label="Close">
																		<span aria-hidden="true">&times;</span>
																	</button>
																	<strong> Sorry!</strong> there was an error with the payment notification and so, the process has been terminated.
																</div>';
													}
												}
												else
												{
												  echo '<div class="col-md-6">
															  <div class="templatemo-alerts">
																<div class="row">
																  <div class="col-md-12">
																	<div class="alert alert-success alert-dismissible" role="alert">
																	  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
																	 Sorry, there was an error with the payment notification and so, the process has been terminated.</div>
																   
																			  
																  </div>  
																</div>            
															  </div>              
															</div>';
												}
												?>
											</tbody>	
									</table>
		   
 							</div>
                    </div><!-- end col-->
                </div>
                <!-- end row -->

        <!-- Footer -->
		<?php
			include("../footer.php");
		?>
		<!-- End Footer -->

         </div> <!-- container -->
        </div> <!-- End wrapper -->


        <script>
            var resizefunc = [];
        </script>

        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
        <script src="assets/plugins/switchery/switchery.min.js"></script>

        <!-- App js -->
        <script src="assets/js/jquery.core.js"></script>
        <script src="assets/js/jquery.app.js"></script>

        <!-- Page specific js -->
        <script src="assets/pages/jquery.dashboard.js"></script>

        <!-- Required datatable js -->
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/plugins/datatables/jszip.min.js"></script>
        <script src="assets/plugins/datatables/pdfmake.min.js"></script>
        <script src="assets/plugins/datatables/vfs_fonts.js"></script>
        <script src="assets/plugins/datatables/buttons.html5.min.js"></script>
        <script src="assets/plugins/datatables/buttons.print.min.js"></script>
        <script src="assets/plugins/datatables/buttons.colVis.min.js"></script>
        <!-- Responsive examples -->
        <script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#datatable').DataTable();

                //Buttons examples
                var table = $('#datatable-buttons').DataTable({
                    lengthChange: false,
                    buttons: ['copy', 'excel', 'pdf', 'colvis']
                });

                table.buttons().container()
                        .appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');
            } );

        </script>

    </body>
</html>