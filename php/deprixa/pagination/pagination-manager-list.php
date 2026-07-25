<?php
require_once("database.php");

function generatePagination(){
	$per_page = 8;
	//Calculating no of pages
	$sql = "SELECT * FROM courier_officers";
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
	$per_page = 8;
	if($_GET) { $page=$_GET['page'];}
	else {$page = 1;}
	$start = ($page-1)*$per_page;
	$sql = "SELECT * FROM courier_officers ORDER BY cid LIMIT $start, $per_page";
	$result = mysql_query($sql);
	$records = array();
	while($row = mysql_fetch_array($result)){
		extract($row);
		$records[] = array("cid" => $cid,
			"officer_name" => $officer_name,
			"address" => $address,
			"email" => $email,
			"ph_no" => $ph_no,
			"office" => $office);
	}//while
	return $records;
}


?>