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
$cid= (int)$_GET['cid'];

$sql = "SELECT *
		FROM courier_online
		WHERE cid = $cid";	
$result = dbQuery($sql);		
while($row = dbFetchAssoc($result)) {
extract($row);
}
$company=mysql_fetch_array(mysql_query("SELECT * FROM company"));
$fecha=date('Y-m-d');
?>

<!DOCTYPE html>
<html>
  <head>

    <title>DEPRIXA | Invoice</title>
	
	<!-- Define Charset -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<!-- Page Description and Author -->
	<meta name="description" content="Courier Deprixa V2.5 "/>
	<meta name="keywords" content="Courier DEPRIXA-Integral Web System" />
	<meta name="author" content="Jaomweb">	
	
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="css/print-invoice.min.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<script src="barcode.js"></script>
  </head>
  <body onload="window.print();">
    <div class="wrapper">

      <!-- Main content -->
      <section class="invoice">
        <!-- title row -->
        <div class="row">
          <div class="col-xs-12">
            <h2 class="page-header">
			  <span><img src="../image_logo.php?id=1"></span>
              <small class="pull-right"><?php  echo date('l j \of F Y h:m:s A');  ?></small>
            </h2>
          </div><!-- /.col -->
        </div>
        <!-- info row -->
        <div class="row invoice-info">
          <div class="col-sm-4 invoice-col">
            From
            <address>
              <h4><strong><?php echo $ship_name; ?></strong></h4><br>

               <b>Phone:</b>  <?php echo $s_phone; ?><br/>
              <b>Address:</b> <?php echo $s_add; ?><br/>
			  <b>Origin Office:</b> <?php echo $fromcity; ?>
            </address>
          </div><!-- /.col -->
          <div class="col-sm-4 invoice-col">
            To
            <address>
              <h4><strong><?php echo $rev_name; ?></strong></h4><br>
              
              <b>Phone:</b> <?php echo $r_phone; ?><br/>
			  <b>Address:</b> <?php echo $r_add; ?><br/>
              <b>Destination Office:</b> <?php echo $tocity; ?>
            </address>
          </div><!-- /.col -->
          <div class="col-sm-4 invoice-col">
		  <table>
                                        	<tr>
                                                <td>
                                                    <center>
                                                        <img src="barcode.php?text=testing" alt="testing" /><br>
                                                        <?php echo $cons_no; ?><br>
                                                    </center>
                                                </td>
                                                
                                            </tr>
                                        </table>
			<br/>
            <b>Order ID:</b>&nbsp;&nbsp;<?php echo $cid; ?><br/>
            <b>Payment Due:</b>&nbsp;<?php echo $date; ?><br/>
			<b>Type of Shipment:</b> <small class="label label-danger"><i class="fa fa-money"></i>&nbsp;&nbsp;<?php echo $book_mode; ?></small><br/>
          </div><!-- /.col -->		 
        </div><!-- /.row -->

        <!-- Table row -->
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Qty</th>
                  <th>Product</th>
                  <th>Status</th>
                  <th>Description</th>
				  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $Qnty; ?></td>
                  <td><?php echo $type; ?></td>
                  <td><small class="label label-success"><?php echo $status; ?></small></td>
                  <td><?php echo $note; ?></td>
				  <td><?php echo $freight; ?></td>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal; ?></td>
                </tr>               
              </tbody>
            </table>
          </div><!-- /.col -->
        </div><!-- /.row -->
		<br>
		<br>
        <div class="row">
          <!-- accepted payments column -->
          <div class="col-xs-6">
            <p class="lead">Payment Methods:</p>
            <img src="../images/credit/securepayment.png" alt="Methods payments" />           
            <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
              For your convenience we have DEPRIXA several payment reliable, fast, secure.
            </p>
          </div><!-- /.col -->
          <div class="col-xs-6">
            <p class="lead">Amount Due </p>
            <div class="table-responsive">
              <table class="table">
                <tr>
                  <th style="width:50%">Subtotal:</th>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal; ?></td>
                </tr>
                <tr>
                  <th>Shipping:</th>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal-$freight; ?></td>
                </tr>
                <tr>
                  <th>Total:</th>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal; ?></td>
                </tr>
              </table>
            </div>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->
    </div><!-- ./wrapper -->

    <!-- AdminLTE App -->
    <script src="js/app.min.js" type="text/javascript"></script>
  </body>
</html>
