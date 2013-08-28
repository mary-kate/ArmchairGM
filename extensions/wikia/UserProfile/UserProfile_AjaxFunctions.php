<?php
/*
 * Ajax Functions used by Wikia extensions
 */
 
$wgAjaxExportList [] = 'wfGetSportTeams';
function wfGetSportTeams($sport_id){
	global $wgUser, $wgOut, $IP; 
	
	$dbr =& wfGetDB( DB_MASTER );
	
	$sql = "SELECT team_id,team_name FROM sport_team WHERE team_sport_id ={$sport_id} order by team_name";
	$res = $dbr->query($sql);
	
	$x = 0;
	$out = "{ \"options\": [";
	while ($row = $dbr->fetchObject( $res ) ) {
		if($x!=0)$out.= ",";
		$out .= " {\"id\":{$row->team_id},\"name\":\"{$row->team_name}\"}";
		$x++;
	}
	$out .= " ] }";
	return $out;
}



?>
