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
	<title>DEPRIXA | Panel Customer </title>
	
	<!-- Switchery css -->
	<link href="assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

	<!-- App CSS -->
	<link href="assets/css/style.css" rel="stylesheet" type="text/css" />
	
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
                            <h4 class="m-t-0 header-title"><b>Add Shipping Customer</b></h4>

								<table border="0" align="center" width="100%">
				
									<!-- START Checkout form -->
									<form action="booking.php" method="post" id="nl-form" name="nl-form">

													<div class="row">
													  <div class="col-md-4 col-sm-4 col-xs-12 ">
													  <label  class="control-label">ORIGIN<span class="required-field"><i class="fa fa-map-marker"></i></span></label>
															<div class="input-group">															
																<span id="inter_origin" style="display: block;">     
																<select onchange="print_state('state', this.selectedIndex);" id="country" required  name ="scountry" class="form-control"></select>
																</span>   
																<span id="domestic_origin" >
																<select  name ="sstate" required  id ="state" class="form-control"><option value="">Select state</option></select>
																</span>
																<script language="javascript">print_country("country");</script>	
															</div>
													  </div>
													  <div class="col-md-4 col-sm-4 col-xs-12 ">
													  <label  class="control-label">DESTINATION<span class="required-field"><i class="fa fa-map-marker"></i></span></label>
															<div class="input-group"> 
																<span id="inter_dest" style="display: block;">
																<select onchange="print_state('state1', this.selectedIndex);" id="country1" name ="dcountry" required class="form-control"></select>
																</span>
																<span id="domestic_dest">
																<select  name ="dstate" required  id ="state1" class="fa-glass booking_form_dropdown form-control"><option value="">Select state</option></select>
																</span>
																<script language="javascript">print_country("country1");</script>	
															</div>
													  </div>
													  <div class="col-md-4 col-sm-4 col-xs-12 ">
													  <label  class="control-label">SERVICES<span class="required-field"><i class="fa fa-cog"></i></span></label>
														<div class="input-group">
																<select name="service" class="fa-glass booking_form_dropdown form-control" id="service" onclick="clear_service();">
																	<option value="" selected="selected">Please Select Service</option>
																	<option value="Normal" >Normal</option>
																	<option value="Express" >Express</option>
																	</select>
																	<select name="type" required  class="fa-glass booking_form_dropdown form-control" id="service" onclick="clear_service();">
																	<option value="" selected="selected">Please Select Type</option>
																	<option value="Parcel">Parcel</option>
																	<option value="Pallate">Pallate</option><option value="Other">Other</option><option value="Documents">Documents</option>
																	</select>
																</div>
															  </div>
															</div>
														   <br>
														<br>
													<div class="row">
													<?php  
														require_once('../database.php');
														$sql = "SELECT * FROM tbl_clients where email='$qname' LIMIT 1";
														$result = dbQuery($sql);		
														while($data = dbFetchAssoc($result)) {
														extract($data);
													?> 
														<!-- START Receiver info  -->
														<fieldset class="col-md-6">
															<legend>SENDER :</legend>
															
															<!-- Name -->
															<div class="form-group">
																<label  class="control-label">CUSTOMER NAME<span class="required-field">*</span></label>
																 <input name="name" required  type="text" class="form-control" value="<?php echo $name;?>"  placeholder="Full name" readonly>
																
															</div>
															
															<!-- Adress and Phone -->
															<div class="row">
																<div class="col-sm-4 form-group">
																	<label  class="control-label">Email <span class="required-field">*</span></label>
																	<input name="email" value="<?php echo $_SESSION['user_name']; ?>"  class="form-control" type="text" required  placeholder="yourname@gmail.com" readonly>
																</div>
																
																<div class="col-sm-4 form-group">
																	<label  class="control-label"><i class="fa fa-phone icon text-default-lter"></i>&nbsp;PHONE</label>
																	<input name="phone" class="form-control" required  type="text"  value="<?php echo $phone;?>" placeholder="(054)-828 0085" readonly>
																</div>
																<div class="col-sm-4 form-group">
																	<label  class="control-label"><i class="fa fa-phone icon text-default-lter"></i>&nbsp;Adress</label>
																	<input name="address" class="form-control" required  type="text" value="<?php echo $address;?>"  placeholder="address" readonly>
																</div>																																
															</div>													
														</fieldset>
														<?php } ?>
														<fieldset class="col-md-6">
															<legend>Shipping Information:</legend>
															
															<!-- Name -->
															<div class="row" >
																<div class="col-sm-12 form-group">
																		<label  class="control-label"><i class="fa fa-user icon text-default-lter"></i>&nbsp;Customer Name<span class="required-field">*</span></label>
																		<input type="text"  name="officename" id="officename" value="<?php echo $_SESSION['user_name'] ;?>" class="form-control"  readonly="true" >
																  </div>
															</div>
															
															<div class="row" >
																<div class="col-sm-3 form-group">
																	<label  class="control-label">Quantity of Packages<span class="required-field">*</span></label>
																	<input name="Qnty" type="text" class="form-control"  placeholder="Quantity of Packages" required>
																</div>
																<div class="col-sm-2 form-group">
																	<label  class="control-label">Weight<span class="required-field">*</span></label>
																	<input name="weight" type="text" class="form-control"  placeholder="  Kg" required>
																</div>
																
																<div class="col-sm-2 form-group">
																	<label  class="control-label">Length<span class="required-field">*</span></label>
																	 <input name="length" class="form-control" type="text" placeholder="  Length in cm">
																</div>
																
																<div class="col-sm-2 form-group">
																	<label  class="control-label">Width<span class="required-field">*</span></label>
																	<input name="width" class="form-control" type="text" placeholder="  Width in cm">
																</div>
																
																<div class="col-sm-2 form-group">
																	<label class="control-label">Height<span class="required-field">*</span></label>
																	<input name="height" class="form-control" type="text" placeholder="  Height in cm">
																</div>
															</div>
															<!-- Name -->
															<div class="row" >
																<div class="col-sm-12 form-group">
																	<label class="control-label">Details of the Shipment</i></label>
																	<textarea name="note" class="form-control" type="text"  placeholder="Details of the Shipment" required></textarea>																	
																</div>
															</div>

														</fieldset>
														<fieldset class="col-md-6">
															<legend>RECEIVER :</legend>
															
															<!-- Name -->
															<div class="form-group">
																<label  class="control-label">RECEIVER NAME<span class="required-field">*</span></label>
																 <input name="name_delivery" required  type="text" class="form-control" placeholder="Full name" >
																
															</div>
															
															<!-- Adress and Phone -->
															<div class="row">
																<div class="col-sm-6 form-group">
																	<label  class="control-label">Address <span class="required-field">*</span></label>
																	<input name="address_delivery"  class="form-control" type="text" required  placeholder="Adress">
																</div>
																
																<div class="col-sm-6 form-group">
																	<label  class="control-label"><i class="fa fa-phone icon text-default-lter"></i>&nbsp;PHONE</label>
																	<input name="phone_delivery" class="form-control" required  type="text" placeholder="(054)-828 0085">
																</div>
															</div>													
														</fieldset>
														<div class="col-md-6 text-left">
															<br>
															<br>
															<input class="btn btn-primary" name="Submit" type="submit"  value="SAVE TRACKING">
														</div>
													</div>					
												</article>				
											  <div class="clearfix"></div>
										   </div>
										  </div>
										 </tbody>
									</form>				
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


        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
        <script src="assets/plugins/switchery/switchery.min.js"></script>
		

        <!--Morris Chart-->
		<script src="assets/plugins/morris/morris.min.js"></script>
		<script src="assets/plugins/raphael/raphael-min.js"></script>

        <!-- Counter Up  -->
        <script src="assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
        <script src="assets/plugins/counterup/jquery.counterup.min.js"></script>

        <!-- App js -->
        <script src="assets/js/jquery.core.js"></script>
        <script src="assets/js/jquery.app.js"></script>

        <!-- Page specific js -->
        <script src="assets/pages/jquery.dashboard.js"></script>
		



    </body>

</html>