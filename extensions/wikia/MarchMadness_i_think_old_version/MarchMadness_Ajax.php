<?php
/*
 * Ajax Functions used by Pick Game
 */
 

$wgAjaxExportList [] = 'wfCreateMadnessGroup';
function wfCreateMadnessGroup($group_name, $tournament_id, $private, $entry_name) {
	global $IP, $wgUser, $wgOut;
	
	$current_user_name = $wgUser->getName();
	$current_user_id = User::idFromName($current_user_name);
	
	//if (!is_null($game_id) && $game_id != "" && $game_id != "0") {
		
		//get database
		$dbr =& wfGetDB( DB_MASTER );
		
		$dbr->insert( '`madness_tournament_group`',
			array(
				'group_name' => $group_name,
				'private' => $private,
				'tournament_id' => $tournament_id,
				'creator' => $current_user_id,
				), __METHOD__
			);

		$temp_group_name = str_replace("'", "''", $group_name);
		$sql = "SELECT group_id FROM madness_tournament_group WHERE group_name='{$temp_group_name}' AND private={$private} AND tournament_id={$tournament_id} AND creator={$current_user_id}"; 
		
		$res = $dbr->query($sql);
		if ($row = $dbr->fetchObject( $res ) ) {
		  
			  $group_id = $row->group_id;
		   
		  }
		 
		  $dbr->insert( '`madness_tournament_entry`',
			array(
				'group_id' => $group_id,
				'entry_name' => $entry_name,
				'tournament_id' => $tournament_id,
				'user_id' => $current_user_id,
				'user_name' => $current_user_name,
				), __METHOD__
			);
		  

		return "ok";


	//}
	
	//return "No Game ID passed";
}

?>
