<?php
require_once("database.php");
require_once('library.php');
function generatePagination(){
	$per_page = 100;
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

function getPageDatas(){
	$per_page = 100;
	if($_GET) { $page=$_GET['page'];}
	else {$page = 1;}
	$start = ($page-1)*$per_page;
	$sql = "SELECT * FROM courier WHERE status != 'Delivered' and officename='".$_SESSION["user_type"]."' ORDER BY cid DESC LIMIT $start, $per_page";
	$result = mysql_query($sql);
	$recordss = array();
	while($row = mysql_fetch_array($result)){
		extracts($row);
		$recordss[] = array("cid" => $cid,
			"cons_no" => $cons_no,
			"book_mode" => $book_mode,
			"ship_name" => $ship_name,
			"rev_name" => $rev_name,
			"pick_date" => $pick_date,
			"pick_time" => $pick_time,
			"officename" => $officename,
			"status" => $status);
	}//while
	return $recordss;
}


?>