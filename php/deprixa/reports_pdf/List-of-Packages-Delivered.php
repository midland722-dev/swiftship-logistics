<?php

if(strlen($_GET['desde'])>0 and strlen($_GET['hasta'])>0){
	$desde = $_GET['desde'];
	$hasta = $_GET['hasta'];

	$verDesde = date('d/m/Y', strtotime($desde));
	$verHasta = date('d/m/Y', strtotime($hasta));
}else{
	$desde = '1111-01-01';
	$hasta = '9999-12-30';

	$verDesde = '__/__/____';
	$verHasta = '__/__/____';
	
}
	
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
 
session_start();
require('../fpdf/fpdf.php');
require_once('../database.php');
require_once('../library.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(140, 0, '', 0);
$pdf->Cell(15, 0, '"Courier DEPRIXA.COM"', 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(35, 10, 'Day: '.date('d-m-Y').'', 0);
$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, '', 0);
$pdf->Cell(100, 8, 'List of Packages Delivered', 0);
$pdf->Ln(10);
$pdf->Cell(60, 8, '', 0);
$pdf->Ln(23);

$pdf->SetFont('Arial', 'B',  10);

$pdf->Cell(23, 8, 'Tracking No', 0);
$pdf->Cell(25, 8, 'Origin', 0);
$pdf->Cell(27, 8, 'Destination', 0);
$pdf->Cell(26, 8, 'Shipping Date', 0);
$pdf->Cell(25, 8, 'Shipper', 0);
$pdf->Cell(29, 8, 'Receiver', 0);
$pdf->Cell(20, 8, 'Phone', 0);
$pdf->Cell(22, 8, 'Status', 0);
$pdf->Ln(8);
$pdf->SetFont('Arial', '', 8);

//CONSULTA
$result = mysql_query("SELECT * FROM courier WHERE  status ='Delivered' AND officename='".$_SESSION["user_type"]."' AND book_date  BETWEEN '$desde' AND '$hasta' ");
while($row = mysql_fetch_array($result)){
	
	$pdf->Cell(23, 8, $row['cons_no'], 0);
	$pdf->Cell(25, 8, $row['invice_no'], 0);
	$pdf->Cell(27, 8, $row['pick_time'], 0);
	$pdf->Cell(26, 8, $row['book_date'], 0);
	$pdf->Cell(25, 8, $row['ship_name'], 0);
	$pdf->Cell(29, 8, $row['rev_name'], 0);
	$pdf->Cell(20, 8, $row['r_phone'], 0);
	$pdf->Cell(22, 8, $row['status'], 0);
	$pdf->Ln(8);
}

$pdf->Output('reports_pdf/List-of-Packages-Delivered.pdf','D');
?>