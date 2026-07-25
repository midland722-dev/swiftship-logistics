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

 $qname = $_SESSION['user_name']; 
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
				<div class="row">
                    <div class="col-sm-12">
                        <div class="card-box table-responsive">
								
								<div class="bg-light lter b-b wrapper-md hidden-print">

									<td><a href class="pull-right" onClick="window.print();">
									<img src="print.png" border="0" height="57" width="100"></a></td>
									 
									  <?php
											require_once('../database.php');
											$cons_no= (int)$_GET['cid'];
											$sql_2 = "SELECT * FROM courier_online WHERE cid='$cons_no'";
											$result5 = dbQuery($sql_2);		
												while($data = dbFetchAssoc($result5)) {
												extract($data);
													  
										?>
									  <a href="payments.php?shipping_subtotal=<?php echo $shipping_subtotal; ?>" class="pull-right">
									  <img src="checkout-with-paypal.png" border="0" height="58" width="180"></a>
									<?php } ?>
									
									<a href="#myModal<?php echo $row[cid]; ?>" data-toggle="modal" class="pull-right">
									  <img src="bank-tranfers.png" border="0" height="58" width="150"></a>
									<br><br>
								</div>
								<?php  
									require_once('../database.php');
									$sql = "SELECT * FROM company WHERE id='1'";
									$result4 = dbQuery($sql);		
									while($data = dbFetchAssoc($result4)) {
									extract($data);
								?>
								<div class="wrapper-md">
								  <div>
									<i><img src="../image_logo.php?id=1"></i>
										<br>
										<div class="row">
										  <div class="col-xs-6">
											<p><?php echo $website;?></p>
											<p><?php echo $cname;?><br>
											  <?php echo $caddress;?><br>
											  
											</p>
											<p>
											  <strong>Telephone:</strong>  <?php echo $cphone;?><br>
											  <strong>Mail:</strong>  <?php echo $cemail;?>
											</p>
										  </div>
										  <?php } ?>

										<?php  
											require_once('../database.php');
											$cons_no= (int)$_GET['cid'];
												$sql_1 = "SELECT * FROM courier_online WHERE cid = '$cons_no' ";
												$result4 = dbQuery($sql_1);		
											while($data = dbFetchAssoc($result4)) {
												extract($data);
										?>			  
										  <div class="col-xs-6 text-right">
										   
											<img src="../print-invoice/barcode.php?text=testing" alt="testing" />
											</br>
											<p><?php echo $cons_no; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
															
											<br><br>										
											<p align="right">DATE : <?php echo date("F j, Y"); ?></p>           
										  </div>
										  <?php } ?>
										</div>
	 
											<?php  					
											   $result5 = mysql_query("SELECT * FROM tbl_clients where email='$qname'");
												while($row = mysql_fetch_array($result5)) {							
											?>	
											<div class="col-xs-6">
											  <strong>TO:</strong>
											  <h4><?php echo $row ["name"]; ?></h4>
											  <p>
												<strong>Address:</strong><?php echo $row ["address"]; ?><br>
												<strong>Phone:</strong><?php echo $row ["phone"]; ?><br>
												<strong>Email:</strong><?php echo $row ["email"]; ?>><br>
											  </p>
											</div>
											<?php } ?>
									
										<?php  					
										   $result3 = mysql_query("SELECT * FROM courier_online  where status='In-Transit' and cid='$cid'");
											while($row = mysql_fetch_array($result3)) {							
										?>
										<p class="m-t m-b">
											Order status: <span class="label bg-success"><?php echo $row ["status"]; ?></span><br>
											Order ID: <strong><?php echo $row ["cid"]; ?></strong>
										</p>
										<?php } ?>
										<?php  					
										   $result4 = mysql_query("SELECT * FROM courier_online  where cid='$cid'");
											while($row = mysql_fetch_array($result4)) {							
										?>  
										<p class="m-t m-b">
											Order Date: <strong><?php echo $row ["deliverydate"]; ?></strong>
										</p>
										<?php } ?>								
										<table class="table table-striped bg-white b-a">
											<thead>
												<tr>
												  <th>QUANTITY</th>
												  <th>WEIGHT</th>
												  <th>1 KG/ $</th>
												  <th>SHIPMENT ID</th>
												  <th>DESCRIPTION</th>
												   <th>PRICE</th>
												  <th>SHIPPED TO</th>										 
												  <th>SUBTOTAL</th>
												</tr>
											</thead>
											<tbody>
											  <?php
												require_once('../database.php');
												$company=mysql_fetch_array(mysql_query("SELECT * FROM company"));
												$result1 =  mysql_query("SELECT * FROM courier_online where s_add='$qname' AND payment='Pending' AND cid = '$cid'");
												while($row = mysql_fetch_array($result1)) {		

												?>
												<tr>
												  <td><?php echo $row ["Qnty"]; ?></td>
												  <td><?php echo $row ["weight"]; ?></td>
												   <td><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["variable"]; ?></td>
												  <td><?php echo $row ["cons_no"]; ?></td>
												  <td><?php echo $row ["note"]; ?></td>
												  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["freight"]; ?></td>
												  <td><?php echo $row ["tocity"]; ?></td>										  
												  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["shipping_subtotal"]; ?></td>
												</tr>				
												<tr>
												  <td colspan="7" class="text-right"><strong>Subtotal</strong></td>
												  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["shipping_subtotal"]; ?></td>
												</tr>
												<tr>
												  <td colspan="7" class="text-right no-border"><strong>Shipping</strong></td>
												  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal-$freight; ?></td>
												</tr>
												<tr>
												  <td colspan="7" class="text-right no-border"><strong>Total</strong></td>
												  <td><strong><?php echo $company['currency']; ?>&nbsp;<?php echo $row ["shipping_subtotal"]; ?></strong></td>
												</tr>
												  <?php } ?>
											</tbody>
										</table> 
										<div class="col-xs-6">
										<p class="lead">Payment Methods:</p>
										<img src="../images/credit/securepayment.png" alt="Methods payments" />           									
									  </div>
								  </div>
								</div>
 							</div>
                    </div><!-- end col-->
                </div>
                <!-- end row -->
				
				<!-- sample modal content -->
				<div id="myModal<?php echo $row['cid']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">DETAILS OF THE BANK ACCOUNT</h4>
								<br>
								<hr>
								<h5>Make a payment to our bank account</h5></br>
								Name of the Bank: <strong>ROYAL BANK</strong></br>
								Name of the account: <strong>Johan Osorio M.</strong> </br>
								Account number: <strong>6542XXXXXXXX</strong></p>
								<hr>
							</div>
							<div class="modal-body">
								<h4 class="header-title m-t-0 m-b-20">SEND PAYMENT SUPPORT</h4>
								<br>								
								<div class="text-xs-center">
								
									<div class="p-10">
										<div class="form-group clearfix">
											<form action="config.inc.php?action=update-bank&cid=<?php echo $cid; ?>" enctype="multipart/form-data" method="post">
											<fieldset class="form-group">
												<?php  
														require_once('../database.php');
														$cons_no= (int)$_GET['cid'];
															$sql_2 = "SELECT * FROM courier_online WHERE cid = '$cons_no' ";
															$result5 = dbQuery($sql_2);		
														while($data = dbFetchAssoc($result5)) {
															extract($data);
												?>	
											    <div class="form-group">
													<label for="address" class="col-sm-2 control-label"># Shipping</label>
													<div class="col-sm-5">
													  <input type="text"  name="cons_no" class="form-control cons_no" value="<?php echo $cons_no; ?>" readonly="true">
													</div>
													<div class="col-sm-5">
													  <input class="form-control office" name="office" value="<?php echo $_SESSION['user_name'] ;?>" readonly="true">																  
													</div>
												</div>
												<?php } ?>
											</fieldset>
											<label for="zipcode" class="control-label"><i class="fa fa-upload icon text-default-lter"></i>&nbsp;UPLOAD FILE</label>
											<input type="file" name="imagen" id="imagen" class="form-control" />
											<br><br>
											
											<div class="modal-footer">
											<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">CLOSE</button>
											<button type="submit" name="guardar" class="btn btn-info-outline waves-effect waves-light">UPLOAD FILE</button>
											</div>
											<br><br>
											</form>
										</div>
									</div>
									<br>									
								</div>
							</div>
								
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->

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
		<script type="text/javascript">
			function printInfo(ele) {
			var prtContent = document.getElementById("printarea");
			var WinPrint = window.open('', '', 'letf=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
			WinPrint.document.write(prtContent.innerHTML);
			WinPrint.document.close();
			WinPrint.focus();
			WinPrint.print();
			WinPrint.close();
			}
		</script>

    </body>
</html>