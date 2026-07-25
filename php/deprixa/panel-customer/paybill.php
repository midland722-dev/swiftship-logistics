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
require_once('../database.php');
require_once('../library.php');
isUser();
 $qname = $_SESSION['user_name']; 
 $company=mysql_fetch_array(mysql_query("SELECT * FROM company"));
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
	<title>DEPRIXA | Pay Bill </title>
	
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
	<style> .Returned { background: #363C56; } .Delayed { background: #F76063; } .Shipment-arrived { background: #FFC734; } .Landed { background: #FF8A4B; } .label{padding: 5px;} .In-Transit { background:#00D96D; } .OK { background:#00D96D; } .Pending { background: #FF5D48; }</style>
	
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
                            <h4 class="m-t-0 header-title"><b>Pay Bill</b></h4>
								<br><br>
							  <table id="datatable-buttons" class="table table-striped table-bordered" cellspacing="0" width="100%">

								<thead>
								  <tr>
									  <th>
										  PAY BILL
									  </th>					  					  
									  <th>
										  # Tracking
									  </th>
									  <th>
										  Origin
									  </th>
									  <th>
										  Destination
									  </th>
									  <th>
										  Shipping Date / Time
									  </th>
									  <th>
										  Subtotal
									  </th>									  
									  <th>
										  Payments
									  </th>
									  <th>
										  Customer
									  </th>
									  <th>
										  Shipment status
									  </th>
								  </tr>
								</thead>
								
								<tbody>
									<?php  					
										$result3 = mysql_query("SELECT * FROM courier_online WHERE status='In-Transit' OR status='Shipment-arrived' OR status='Returned' and office='".$_SESSION["user_name"]."' ");
										while($row = mysql_fetch_array($result3)) {					
									?>	
								  <tr>
									  <td align="center">					
									  <a href="customer_invoice.php?cid=<?php echo $row ["cid"]; ?>">
									  <img src="paybill.png"  height="30" width="28"></a></td>						  					  
									  <td><font color="#000"><?php echo $row ["cons_no"]; ?></font></td>
									  <td><?php echo $row ["fromcity"]; ?></td>
									  <td><?php echo $row ["tocity"]; ?></td>
									  <td><?php echo $row ["deliverydate"]; ?></td>	
									  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["shipping_subtotal"]; ?></td>									  
									  <td align="center"><span class="label <?php echo $row['payment']; ?> label-large"><?php echo $row['payment']; ?></span></td>
									   <td><?php echo $row ["office"]; ?></td>
									  <td><span class="label <?php echo $row ["status"]; ?> label-large"><?php echo $row ["status"]; ?></span></td>									 
								  </tr>
								<?php } ?> 
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