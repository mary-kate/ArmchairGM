<?php
/*
 * Ajax Functions used by Wikia extensions
 */
 
$wgAjaxExportList [] = 'wfGetListRatings';
function wfGetListRatings($user_name, $page, $show, $category){
	global $wgSiteView, $wgOut, $IP;
	
	require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
	 
	$list = new ListRatings($user_name);
	$out = $list->displayList($category,$show,$page);
	return $out;
}



?>