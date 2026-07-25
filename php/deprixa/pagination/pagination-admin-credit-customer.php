<?php
require_once('database.php');
require_once('library.php');
function generatePagination(){
	$per_page = 13;
	//Calculating no of pages
	$sql = "SELECT * FROM courier";
	$result = mysql_query($sql);
	$count = mysql_num_rows($result);
	$pages = ceil($count/$per_page);
	$pageno = "<ul id=\"pagination\">";
	for($i=1; $i<=$pages; $i++)	{
		$pageno .= "<a class=\"pageno\" title=\"Page #. $i\" href=\"?page=$i\"><li id=\".$i.\">".$i."</li></a> ";
	}
	$pageno .= 	"</ul>";
	return $pageno;
}

function getPageData(){
	$per_page = 13;
	if($_GET) { $page=$_GET['page'];}
	else {$page = 1;}
	$start = ($page-1)*$per_page;
	$sql = "SELECT * FROM courier WHERE status = 'In Transit'  AND book_mode ='ToPay'   AND cc ='001' ORDER BY cid DESC LIMIT $start, $per_page";

	$result = mysql_query($sql);
	$records = array();
	while($row = mysql_fetch_array($result)){
	
	
		extract($row);
		$records[] = array("cid" => $cid,
			"cons_no" => $cons_no,
			"invice_no" => $invice_no,
			"pick_time" => $pick_time,
			"pick_date" => $pick_date,
			"status" => $status);

	}//while
	return $records;
}



?>