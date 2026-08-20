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
		FROM courier
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

    <title><?php echo $company['cname']; ?> | Invoice</title>
	
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
	
	
<style>
	    
	    #background{
    position:absolute;
    z-index:0; 
    display:block;
    min-height:70%; 
    min-width:70%; 
}

#content{
    position:absolute;
    z-index:1;
}

#bg-text
{
    color:grey;
    font-size:36px;
    transform:rotate(300deg);
    -webkit-transform:rotate(300deg);
}
	    
	</style>
	
	
 


  </head>
  <body  style="background-color:teal;"  onload="window.print();">
   
                                        
                                        
                                        <!-- GTranslate: https://gtranslate.io/ -->


<style type="text/css">
<!--
a.gflag {vertical-align:middle;font-size:16px;padding:1px 0;background-repeat:no-repeat;background-image:url(//gtranslate.net/flags/16.png);}
a.gflag img {border:0;}
a.gflag:hover {background-image:url(//gtranslate.net/flags/16a.png);}
#goog-gt-tt {display:none !important;}
.goog-te-banner-frame {display:none !important;}
.goog-te-menu-value:hover {text-decoration:none !important;}
body {top:0 !important;}
#google_translate_element2 {display:none!important;}
-->
</style>

<br /><select onchange="doGTranslate(this);"><option value="">Select Language</option><option value="en|af">Afrikaans</option><option value="en|sq">Albanian</option><option value="en|ar">Arabic</option><option value="en|hy">Armenian</option><option value="en|az">Azerbaijani</option><option value="en|eu">Basque</option><option value="en|be">Belarusian</option><option value="en|bg">Bulgarian</option><option value="en|ca">Catalan</option><option value="en|zh-CN">Chinese (Simplified)</option><option value="en|zh-TW">Chinese (Traditional)</option><option value="en|hr">Croatian</option><option value="en|cs">Czech</option><option value="en|da">Danish</option><option value="en|nl">Dutch</option><option value="en|en">English</option><option value="en|et">Estonian</option><option value="en|tl">Filipino</option><option value="en|fi">Finnish</option><option value="en|fr">French</option><option value="en|gl">Galician</option><option value="en|ka">Georgian</option><option value="en|de">German</option><option value="en|el">Greek</option><option value="en|ht">Haitian Creole</option><option value="en|iw">Hebrew</option><option value="en|hi">Hindi</option><option value="en|hu">Hungarian</option><option value="en|is">Icelandic</option><option value="en|id">Indonesian</option><option value="en|ga">Irish</option><option value="en|it">Italian</option><option value="en|ja">Japanese</option><option value="en|ko">Korean</option><option value="en|lv">Latvian</option><option value="en|lt">Lithuanian</option><option value="en|mk">Macedonian</option><option value="en|ms">Malay</option><option value="en|mt">Maltese</option><option value="en|no">Norwegian</option><option value="en|fa">Persian</option><option value="en|pl">Polish</option><option value="en|pt">Portuguese</option><option value="en|ro">Romanian</option><option value="en|ru">Russian</option><option value="en|sr">Serbian</option><option value="en|sk">Slovak</option><option value="en|sl">Slovenian</option><option value="en|es">Spanish</option><option value="en|sw">Swahili</option><option value="en|sv">Swedish</option><option value="en|th">Thai</option><option value="en|tr">Turkish</option><option value="en|uk">Ukrainian</option><option value="en|ur">Urdu</option><option value="en|vi">Vietnamese</option><option value="en|cy">Welsh</option><option value="en|yi">Yiddish</option></select><div id="google_translate_element2"></div>
<script type="text/javascript">
function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: 'en',autoDisplay: false}, 'google_translate_element2');}
</script><script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>


<script type="text/javascript">
/* <![CDATA[ */
function GTranslateFireEvent(a, b) {
    try {
        if (document.createEvent) {
            var c = document.createEvent('HTMLEvents');
            c.initEvent(b, true, true);
            a.dispatchEvent(c);
        } else if (document.createEventObject) {
            var d = document.createEventObject();
            a.fireEvent('on' + b, d);
        }
    } catch (e) {}
}
function doGTranslate(a) {
    if (a.value) a = a.value;
    if (a === '') return;
    var b = a.split('|')[1];
    var c;
    var selects = document.getElementsByTagName('select');
    for (var i = 0; i < selects.length; i++) {
        if (selects[i].className === 'goog-te-combo') c = selects[i];
    }
    if (document.getElementById('google_translate_element2') === null
        || document.getElementById('google_translate_element2').length === 0
        || !c || c.length === 0) {
        setTimeout(function () { doGTranslate(a); }, 500);
    } else {
        c.value = b;
        GTranslateFireEvent(c, 'change');
        GTranslateFireEvent(c, 'change');
    }
}
/* ]]> */
</script>

                                        
      
	
	
    <div class="wrapper" id="background"> <p id="bg-text">Certified True Copy</p>

      <!-- Main content -->
      <section class="invoice" >
        <!-- title row -->
        <div class="row"  >
          <div class="col-xs-12">
            <h2 class="page-header">
			  <span><img src="../image_logo.php?id=1"> 
			  
			  <img class="pull-right"  src="banner.png" alt=""  height="185"/> 
			  
			  <h3 style="color:red;"><strong> Tracking Number:  <?php echo $cons_no; ?></strong>
			  </h3></span>
			  
            </h2>
          </div><!-- /.col -->
        </div>
        
        <div class="row">
          <div class="col-xs-12">
            <h2 class="page-header">
			   <center> 
			       <strong style="color:green;"><?php echo $company['cname']; ?><br>
Address: <?php echo $company['caddress']; ?><br>
Email: <?php echo $company['cemail']; ?><br>
Company Website: <?php echo $company['website']; ?></strong></center>
            </h2>
          </div><!-- /.col -->
        </div>
        
        
        <!-- info row -->
        <div class="row invoice-info">
          <div class="col-sm-4 invoice-col">
            <strong style="color:blue;">FROM (SENDER)</strong>
            <address>
              <h3><strong style="color:green;"><?php echo $ship_name; ?></strong></h3><br>

              <b>Address:</b> <?php echo $s_add; ?><br/>
			  <b>Origin Office:</b> <?php echo $invice_no; ?>
            </address>
          </div><!-- /.col -->
          <div class="col-sm-4 invoice-col">
            <strong style="color:blue;">TO (CONSIGNEE)</strong>
            <address>
              <h3><strong style="color:green;"><?php echo $rev_name; ?></strong></h3><br>
              
              <b>Phone:</b> <?php echo $r_phone; ?><br/>
			  <b>Address:</b> <?php echo $r_add; ?><br/>
              <b>Destination Office:</b> <?php echo $pick_time; ?>
            </address>
          </div><!-- /.col -->
          <div class="col-sm-4 invoice-col">
		  <table>
                                        	<tr>
                                                <td>
                                                    <center>
                                                        <img src="barcode.php?text=testing" alt="testing" /><br>
                                                        <strong><?php echo $cons_no; ?></strong><br>
                                                    </center>
                                                </td>
                                                
                                            </tr>
                                        </table>
			<br/>
            <b>Order ID:</b>&nbsp;&nbsp;<?php echo $cid; ?><br/>
            <b>Payment Due:</b>&nbsp;<?php echo $book_date; ?><br/>
			<b>Booking Mode:</b> <small class="label label-danger"><i class="fa fa-money"></i>&nbsp;&nbsp;<?php echo $book_mode; ?></small><br/> 
			<b>Insurance of the Shipment:</b>&nbsp;<?php echo $company['currency']; ?>&nbsp;<?php echo $declarate; ?>.00<br/>
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
				  <th>Shipping Cost</th>
                  <th>Clearance Cost</th>
                  <th>Total Cost</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $qty; ?></td>
                  <td><?php echo $type; ?></td>
                  <td><small class="label label-success"><?php echo $status; ?></small></td>
                  <td><?php echo $comments; ?></td>
				  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $freight; ?>.00</td>
				  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal-$freight; ?>.00</td>
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
            <p class="lead"><strong>Payment Methods:</strong></p>
            <img src="../images/credit/securepayment.png" alt="Methods payments" /> 
            <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
              For your convenience we have several payment reliable, fast, secure.
            </p>
         
          </div>
          
          <div class="col-xs-6">
            <p class="lead"><strong>Official Stamp/<?php  echo date('l j \of F Y ');  ?></strong></p>
            <img src="stamp1.png" alt="" height="100" />           
             
          </div>
          <div class="col-xs-6">
            <p class="lead">Stamp Duty:</p>
            <img src="stamp2.png" alt=""  height="100"/>           
             
          </div>
          
          
          
          <!-- /.col -->
          <div class="col-xs-6">
            <p class="lead"><strong>Amount Due </strong></p>
            <div class="table-responsive">
              <table class="table">
                <tr>
                  <th style="width:50%">SHIPPING COST:</th>
                  <th>CLEARANCE COST:</th>
                  <th>TOTAL AMOUNT:</th> 
                </tr>
                <tr>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $freight; ?>.00</td>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal-$freight; ?>.00</td>
                  <td><?php echo $company['currency']; ?>&nbsp;<?php echo $shipping_subtotal; ?>.00</td>
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
