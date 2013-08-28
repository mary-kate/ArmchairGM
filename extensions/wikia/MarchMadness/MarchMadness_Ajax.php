<?php
/*
 * Ajax Functions used by Pick Game
 */
 

$wgAjaxExportList [] = 'wfCreateMadnessGroup';

function wfCreateMadnessGroup() {
	global $IP, $wgUser, $wgOut, $wgMemc;
	
	if (isset($_POST['group_name']) && isset($_POST['tournament_id']) && isset($_POST['is_private']) && isset($_POST['entry_name']) && isset($_POST['password']) && isset($_POST['group_desc'])) {
		$group_name = $_POST['group_name'];
		$tournament_id = $_POST['tournament_id'];
		$private = $_POST['is_private'];
		$entry_name = $_POST['entry_name'];
		$password = $_POST['password'];
		$group_description = $_POST['group_desc'];
	}
	else {
		return "not ok";
	}
	
	$tournament_info = MarchMadness::get_tournament_info_from_db($tournament_id);
	$tournament_started = MarchMadness::tournament_started($tournament_id);
	
	
	if ($tournament_started) {
		return "not ok - tournament started";
	}
	
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
				'password' => $password,
				'creator_name' =>$current_user_name,
				'group_description' => $group_description
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
		
		$sql = "DELETE FROM madness_tournament_scoring_round WHERE group_id={$group_id}";
			
		$res = $dbr->query( $sql );
		
		$level_points = explode(",", $tournament_info["default_levels"]);
		
		foreach($level_points as $round=>$points) {
			if($round > 3) {
				$insert_round = $round+1;
			}
			else {
				$insert_round = $round;
			}
			$dbr->insert( '`madness_tournament_scoring_round`',
			array(
				'group_id' => $group_id,
				'round' => $insert_round+2,
				'points' => $points,
				), __METHOD__
			);
		}
		
		$key = wfMemcKey( 'marchmadness', 'usergroups', $current_user_id );		  
		$wgMemc->delete($key);

		$key = wfMemcKey( 'marchmadness', 'grouplist', $tournament_id );		  
		$wgMemc->delete($key);
		
		
		
		$sql = "SELECT MAX(group_id) as group_id FROM madness_tournament_group WHERE creator={$current_user_id} and tournament_id={$tournament_id}";
		$res = $dbr->query( $sql );
		
		if ($row = $dbr->fetchObject( $res ) ) {
			$group_id = $row->group_id;
			return "__" . $group_id;
		}
		else {
			return "ok";
		}

	//}
	
	//return "No Game ID passed";
}

/*
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
*/

$wgAjaxExportList[] = 'wfSendMadnessInvite';
function wfSendMadnessInvite() {
	
	global $wgEmailFrom, $wgUser;
	
	if (isset($_POST['user_names']) && isset($_POST['email_addresses']) && isset($_POST['group_id']) && isset($_POST['group_name'])) {
		$user_names = $_POST['user_names'];
		$email_addresses = $_POST['email_addresses'];
		$group_id = $_POST['group_id'];
		$group_name = $_POST['group_name'];
	}
	else {
		return "Not all paramters are being set properly";
	}
	
	$current_user_name = $wgUser->getName();
	
	$sent_text = "";
	$sent_count = 0;
	$unsent_text = "";
	$unsent_count = 0;
	
	$group_info = MarchMadness::get_group_info_from_db($group_id);
	$tournament_started = MarchMadness::tournament_started(0, $group_id);
	$tournament_info = MarchMadness::get_tournament_info_from_db($group_info["tournament_id"]);
	
	if ($tournament_started) {
		return "not ok - tournament started";
	}

	$link = MarchMadnessGroupJoin::march_madness_join_link($group_id);
	/*
	$message = "This is a test email that we are sending to join the group {$group_name}\n";
	$message .= "This is a group that was set up for the {$group_info["tournament_name"]}\n";
	$message .= "Please visit " . $link . " to join.\n";
	
	
	if ($group_info["private"]) {
		$message .= "\nThis is a private group, so you will need a password to enter\n";
		$message .= "Password: {$group_info["password"]}\n";
	}
	*/
	
	$subject = "{$current_user_name} wants you to join a {$tournament_info["tournament_name"]} Picks Group on ArmchairGM";
	
	if ($user_names != "") {
		$users = explode(";",$user_names);
		
		foreach($users as $user_name){
			$user_name = trim($user_name);
			$user = User::newFromName($user_name);
			if($user->isEmailConfirmed()) {
				$message = MarchMadnessGroupInvite::inviteMessage($tournament_info["tournament_name"], $tournament_info["tournament_desc"], $user_name, $current_user_name, $link, $group_info["password"]);
				$to = $user->getEmail();
				mail($to, $subject, $message, "From: $wgEmailFrom");
				$sent_text .= $user_name . ", ";
				$sent_count++;
			}
			else {
				$unsent_text .= $user_name . ", ";
				$unsent_count++;
			}
		}
	}
	if ($email_addresses != "") {
		$addresses = explode(";",$email_addresses);
		
		foreach($addresses as $address){
			$to = trim($address);
			if (MarchMadness::valid_email($to)) {
				$message = MarchMadnessGroupInvite::inviteMessage($to, $current_user_name, $link, $group_info["password"]);
				mail($to, $subject, $message, "From: $wgEmailFrom");
				
				$sent_text .= $address . ", ";
				$sent_count++;
			}
			else {
				$unsent_text .= $address . ", ";
				$unsent_count++;
			}
		}
	}
	
	$return_message = "";
	if ($sent_count) {
		$sent_text = substr($sent_text, 0, strlen($sent_text)-1);
		$return_message .= "Emails sent to: {$sent_text}";
	}
	if ($unsent_count) {
		if($sent_count) {
			$return_message .= "<br/>";
		}
		$unsent_text = substr($unsent_text, 0, strlen($unsent_text)-1);
		$return_message .= "Emails not sent to: {$unsent_text}";
	}
	
	return $return_message;

}

$wgAjaxExportList [] = 'wfJoinMadnessGroup';

function wfJoinMadnessGroup() {
	global $IP, $wgUser, $wgOut, $wgMemc;
	
	if (isset($_POST['needs_password']) && isset($_POST['password']) && isset($_POST['entry_name']) && isset($_POST['group_id'])  && isset($_POST['tournament_id'])) {
		$needs_password = $_POST['needs_password'];
		$password = $_POST['password'];
		$entry_name = $_POST['entry_name'];
		$group_id = $_POST['group_id'];
		$tournament_id = $_POST['tournament_id'];
		$can_continue = true;
	}
	else {
		return "not ok - not all params set";
	}
	
	$tournament_started = MarchMadness::tournament_started($tournament_id);
	
	if ($tournament_started) {
		return "not ok - tournament started";
	}

	
	if ($needs_password) {
		/*
		$dbr =& wfGetDB( DB_SLAVE );
		$sql = "SELECT password AS password FROM madness_tournament_group WHERE madness_tournament_group.group_id={$group_id};";
		$res = $dbr->query($sql);
		if($row = $dbr->fetchObject( $res ) ) {
			$group_password = $row->password;
		}
		*/
		
		$group_password = MarchMadness::get_group_password_from_db($group_id);
		
		if (!$group_password) {
			return "not ok - password not grabbed";
		}
		
		if ($password != $group_password) {
			$can_continue = false;
			return "can not continue - password is incorrect";
		}
	}
	
	if($can_continue) {
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);

		
		$dbr =& wfGetDB( DB_MASTER );

		$dbr->insert( '`madness_tournament_entry`',
				array(
					'group_id' => $group_id,
					'entry_name' => $entry_name,
					'tournament_id' => $tournament_id,
					'user_id' => $current_user_id,
					'user_name' => $current_user_name,
					), __METHOD__
					);
					
		$key = wfMemcKey( 'marchmadness', 'usergroups', $current_user_id );
		$wgMemc->delete( $key );
		$key = wfMemcKey( 'marchmadness', 'groupmembers', $group_id );
		$wgMemc->delete( $key );
		$key = wfMemcKey( 'marchmadness', 'groupstandings', $group_id );
		$wgMemc->delete( $key );
		
		return "ok";

	}
	else {
		return "could not continue - unspecified";
	}
}

$wgAjaxExportList [] = 'wfUpdateGameWinner';

function wfUpdateGameWinner($tournament_id, $bracket, $round, $which_game, $winner, $winner_name, $winner_score, $loser, $loser_name, $loser_score, $next_game, $team_1, $team_2, $bracket_id, $finals_bracket_id) {
	global $IP, $wgUser, $wgOut, $wgMemc;
	/*
	$dbr =& wfGetDB( DB_SLAVE );
	$sql = "SELECT * FROM madness_tournament_winners WHERE madness_tournament_winners.tournament_id={$tournament_id} AND madness_tournament_winners.which_game='{$which_game}' AND round={$round} AND bracket={$bracket_id}";
	$res = $dbr->query($sql);
	if($row = $dbr->fetchObject( $res ) ) {
		$does_exist = true;
	}
	else {
		$does_exist = false;
	}
	*/
	
	$winners = MarchMadness::get_winners_from_db($tournament_id);
	$game_key_under_pos = strpos($which_game, "_");
	$game_key = substr($which_game, $game_key_under_pos+1);
	if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$round])  && isset($winners[$bracket_id][$round][$game_key])) {
		$does_exist = true;
	}
	else {
		$does_exist = false;
		 if (!isset($winners[$bracket_id])) {
			  $winners[$bracket_id] = array();
		  }
		  
		  if (!isset($winners[$bracket_id][$round])) {
			  $winners[$bracket_id][$round] = array();
		  }
	}
	
	if ($next_game != "") {
		$under_pos = strpos($next_game, "_");
		$next_bracket = substr($next_game, 0, $under_pos);
		$next_which_game = $next_bracket . "_" . MarchMadness::get_bracket_sort_order($bracket_id);
		//$next_which_game = $next_bracket . "_" . $bracket_id;
		$next_game_key = substr($next_which_game, $under_pos+1);

	}
	else {
		$next_bracket = $bracket;
	}
	
	$dbr =& wfGetDB( DB_MASTER );
	if ($does_exist) {
		$dbr->update( '`madness_tournament_winners`',
		array(
			'bracket' => $bracket_id,
			'round' => $round,
			'winner' => $winner,
			'winner_name' => $winner_name,
			'winner_score' => $winner_score,
			'loser' => $loser,
			'loser_name' => $loser_name,
			'loser_score' => $loser_score,
			'next_game' => $next_game,
			),
			array(
				'tournament_id' => $tournament_id,
				'which_game' => $which_game,
				'bracket' => $bracket_id,
				'round'=> $round,
			), __METHOD__
			);
			
			$winners[$bracket_id][$round][$game_key] = array(
				'bracket' => $bracket_id,
				'round' => $round,
				'winner' => $winner,
				'winner_name' => $winner_name,
				'winner_score' => $winner_score,
				'loser' => $loser,
				'loser_name' => $loser_name,
				'loser_score' => $loser_score,
				'next_game' => $next_game,
				'tournament_id' => $tournament_id,
				'which_game' => $which_game,
				'skip_calculate' => 0
				);
					
			if ($next_bracket != $bracket) {
				$dbr->update( '`madness_tournament_winners`',
			array(
				'bracket' => $finals_bracket_id,
				'round' => $round,
				'winner' => $winner,
				'winner_name' => $winner_name,
				'winner_score' => $winner_score,
				'loser' => $loser,
				'loser_name' => $loser_name,
				'loser_score' => $loser_score,
				'next_game' => $next_game,
				'skip_calculate'=>1,
				),
				array(
					'tournament_id' => $tournament_id,
					'which_game' => $next_which_game,
					'bracket' => $finals_bracket_id,
					'round'=> $round,
				), __METHOD__
				);
				
				$winners[$finals_bracket_id][$round][$next_game_key] = array(
				'bracket' => $finals_bracket_id,
				'round' => $round,
				'winner' => $winner,
				'winner_name' => $winner_name,
				'winner_score' => $winner_score,
				'loser' => $loser,
				'loser_name' => $loser_name,
				'loser_score' => $loser_score,
				'next_game' => $next_game,
				'tournament_id' => $tournament_id,
				'which_game' => $next_which_game,
				'skip_calculate' => 1
				);
			}
	}
	else {
		
		$dbr->insert( '`madness_tournament_winners`',
		array(
			'tournament_id' => $tournament_id,
			'bracket' => $bracket_id,
			'round' => $round,
			'which_game' => $which_game,
			'winner' => $winner,
			'winner_name' => $winner_name,
			'winner_score' => $winner_score,
			'loser' => $loser,
			'loser_name' => $loser_name,
			'loser_score' => $loser_score,
			'next_game' => $next_game,
			), __METHOD__
			);
			
			$winners[$bracket_id][$round][$game_key] = array(
				'bracket' => $bracket_id,
				'round' => $round,
				'winner' => $winner,
				'winner_name' => $winner_name,
				'winner_score' => $winner_score,
				'loser' => $loser,
				'loser_name' => $loser_name,
				'loser_score' => $loser_score,
				'next_game' => $next_game,
				'tournament_id' => $tournament_id,
				'which_game' => $which_game,
				'skip_calculate' => 0
				);
			
		if ($next_bracket != $bracket) {
			$dbr->insert( '`madness_tournament_winners`',
		array(
			'tournament_id' => $tournament_id,
			'bracket' => $finals_bracket_id,
			'round' => $round,
			'which_game' => $next_which_game,
			'winner' => $winner,
			'winner_name' => $winner_name,
			'winner_score' => $winner_score,
			'loser' => $loser,
			'loser_name' => $loser_name,
			'loser_score' => $loser_score,
			'next_game' => $next_game,
			'skip_calculate'=>1,
			), __METHOD__
			);
			
			if (!isset($winners[$finals_bracket_id])) {
				$winners[$finals_bracket_id] = array();
				$winners[$finals_bracket_id][$round] = array();

			}
			$winners[$finals_bracket_id][$round][$next_game_key] = array(
				'bracket' => $finals_bracket_id,
				'round' => $round,
				'winner' => $winner,
				'winner_name' => $winner_name,
				'winner_score' => $winner_score,
				'loser' => $loser,
				'loser_name' => $loser_name,
				'loser_score' => $loser_score,
				'next_game' => $next_game,
				'tournament_id' => $tournament_id,
				'which_game' => $next_which_game,
				'skip_calculate' => 1
				);
			}

	}
	
	$key = wfMemcKey( 'marchmadness', 'winners', $tournament_id );

	$wgMemc->delete( $key );
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $winners );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($winners) . "\n" );
	}

	
	$team_1_name = ($winner==$team_1 ? $winner_name : $loser_name);
	$team_1_score = ($winner==$team_1 ? $winner_score : $loser_score);
	if ($bracket_id!=$finals_bracket_id) {
		$team_1 = substr($team_1, strlen($bracket)+1);
	}
	
	$team_2_name = ($winner==$team_2 ? $winner_name : $loser_name);
	$team_2_score = ($winner==$team_2 ? $winner_score : $loser_score);
	if ($bracket_id!=$finals_bracket_id) {
		$team_2 = substr($team_2, strlen($bracket)+1);
	}
	$game = substr($which_game, strlen($bracket)+1);
	
	return MarchMadnessEnterWinners::output_entered_game($bracket, $team_1, $team_2, $team_1_name, $team_2_name, $tournament_id, $game, $next_game, $round, $bracket_id, $finals_bracket_id, $team_1_score, $team_2_score);
	
	//return "ok";
		

}


$wgAjaxExportList [] = 'wfUpdateGroupScoring';

function wfUpdateGroupScoring($levels, $group_id, $tournament_id) {
	global $IP, $wgUser, $wgOut, $wgMemc;
	
	$group_info = MarchMadness::get_group_info_from_db($group_id);
	
	$tournament_started = MarchMadness::tournament_started(0, $group_id);
	
	if ($tournament_started) {
		return "not ok - tournament started";
	}
	
	$dbr =& wfGetDB( DB_SLAVE );
	$sql = "SELECT * FROM madness_tournament_scoring WHERE madness_tournament_scoring.group_id={$group_id}";
	$res = $dbr->query($sql);
	if($row = $dbr->fetchObject( $res ) ) {
		$does_exist = true;
	}
	else {
		$does_exist = false;
	}
	
	$dbr2 =& wfGetDB( DB_MASTER );
	if ($does_exist) {
		$dbr2->update( '`madness_tournament_scoring`',
		array(
			'scoring' => $levels,
			
			),
			array(
				'group_id' => $group_id,
			), __METHOD__
			);
			
	}
	else {
		
		$dbr2->insert( '`madness_tournament_scoring`',
		array(
			'scoring' => $levels,
			'tournament_id' => $tournament_id,
			'group_id' => $group_id,
			), __METHOD__
			);
	}
	
	//$sql = "DELETE FROM madness_tournament_scoring_round WHERE group_id={$group_id}";
			
	//$res = $dbr2->query( $sql );
	
	$level_points = explode(",", $levels);
	
	foreach($level_points as $round=>$points) {
		if($round > 3) {
			$insert_round = $round+1;
		}
		else {
			$insert_round = $round;
		}

		$dbr2->update( '`madness_tournament_scoring_round`',
		array(
			'points' => $points,
			),
			array(
			'group_id' => $group_id,
			'round' => $insert_round+2,
			), __METHOD__
		);
	}
	
	$group_info["scoring"] = $levels;
	$key = wfMemcKey( 'marchmadness', 'groupstandings', $group_id );
	$wgMemc->delete($key);
	$key = wfMemcKey( 'marchmadness', 'groupinfo', $group_id );
	$wgMemc->delete($key);
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $group_info );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($group_info) . "\n" );
	}
	return "Scoring System Updated";
		

}

$wgAjaxExportList [] = 'wfUpdateEntryInfo';

function wfUpdateEntryInfo($entry_name, $entry_id) {
	
	global $wgMemc, $wgUser;
	
	$entry_info = MarchMadness::get_entry_info_from_db($entry_id);
	
	$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`madness_tournament_entry`',
		array(
			'entry_name' => $entry_name,
			
			),
			array(
				'entry_id' => $entry_id,
			), __METHOD__
			);
			
	$entry_info["entry_name"] = $entry_name;
	
	$key = wfMemcKey( 'marchmadness', 'entryinfo', $entry_id );
	$wgMemc->delete( $key );
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $entry_info );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($entry_info) . "\n" );
	}
	
	
	if ( MarchMadness::madness_use_cache() ) {
		
		$key = wfMemcKey( 'marchmadness', 'groupstandings', $entry_info["group_id"] );
		$group_standings = $wgMemc->get( $key );
		if($group_standings && isset($group_standings[$entry_id])) {
			$group_standings[$entry_id]["entry_name"] = $entry_name;
			$wgMemc->delete( $key );
			$wgMemc->set( $key, $group_standings );
			wfDebug( "reset {$key} with new array of size: " . sizeof($group_standings) . "\n" );
		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
		
		$key = wfMemcKey( 'marchmadness', 'usergroups', $current_user_id );
		$group_list = $wgMemc->get( $key );
		if($group_list) {
			foreach($group_list[$entry_info["tournament_id"]] as $group=>$group_info) {
				if ($group_info["entry_id"]== $entry_id) {
					$group_list[$entry_info["tournament_id"]][$group]["entry_name"] = $entry_name;
				}
			}
			$wgMemc->delete( $key );
			$wgMemc->set( $key, $group_list );
			wfDebug( "reset {$key} with new array of size: " . sizeof($group_standings) . "\n" );
		}
	
		
	}
	
	return "Entry Name Changed To {$entry_name}";
		

}

$wgAjaxExportList [] = 'wfUpdateGroupInfo';

function wfUpdateGroupInfo() {
	
	global $wgMemc, $wgUser;
	
	if (isset($_POST['group_id']) && isset($_POST['group_name']) && isset($_POST['group_desc']) && isset($_POST['entry_id'])) {
		$group_id = $_POST['group_id'];
		$group_name = $_POST['group_name'];
		$group_desc = $_POST['group_desc'];
		$entry_id = $_POST['entry_id'];
	}
	else {
		return "not ok - not all params set";
	}

	
	$group_info = MarchMadness::get_group_info_from_db($group_id);
	$entry_id = MarchMadness::get_entry_info_from_db($entry_id);
	
	$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`madness_tournament_group`',
		array(
			'group_name' => $group_name,
			'group_description' => $group_desc,
			
			),
			array(
				'group_id' => $group_id,
			), __METHOD__
			);
			
	$group_info["group_name"] = $group_name;
	$group_info["group_description"] = $group_desc;
	
	$key = wfMemcKey( 'marchmadness', 'groupinfo', $group_id );
	$wgMemc->delete( $key );
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $group_info );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($group_info) . "\n" );
	}
	
	
	if ( MarchMadness::madness_use_cache() ) {
		
		$key = wfMemcKey( 'marchmadness', 'groupname', $group_id );
		
		$wgMemc->delete( $key );
		$wgMemc->set( $key, $group_name );
		wfDebug( "reset {$key} with: " .$group_name . "\n" );
		
		/*
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
		
		$key = wfMemcKey( 'marchmadness', 'usergroups', $current_user_id );
		$group_list = $wgMemc->get( $key );
		if($group_list) {
			foreach($group_list[$entry_info["tournament_id"]] as $group=>$group_info) {
				if ($group_info["entry_id"]== $entry_id) {
					$group_list[$entry_info["tournament_id"]][$group]["group_name"] = $group_name;
				}
			}
			$wgMemc->delete( $key );
			$wgMemc->set( $key, $group_list );
			wfDebug( "reset {$key} with new array of size: " . sizeof($group_list) . "\n" );
		}
		*/
		
		$key = wfMemcKey( 'marchmadness', 'groupstandings', $group_id );
		$group_standings = $wgMemc->get( $key );
		if($group_standings) {
			foreach($group_standings as $entry_id=>$standings_info) {
				$key = wfMemcKey( 'marchmadness', 'usergroups', $standings_info["user_id"] );
				$wgMemc->delete( $key );
				wfDebug( "deleted {$key}\n" );
				$key = wfMemcKey( 'marchmadness', 'entryinfo', $entry_id );
				$wgMemc->delete( $key );
				wfDebug( "deleted {$key}\n" );
			}
		}
		
		
	
		
	}
	
	return "Group info updated";
		

}


$wgAjaxExportList [] = 'wfSetTiebreaker';

function wfSetTiebreaker($entry_id, $tiebreaker) {
	
	$entry_info=MarchMadness::get_entry_info_from_db($entry_id);
	
	global $wgMemc;
	
	$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`madness_tournament_entry`',
		array(
			'tiebreaker' => $tiebreaker,
			
			),
			array(
				'entry_id' => $entry_id,
			), __METHOD__
			);
			
	$entry_info["tiebreaker"] = $tiebreaker;
	
	$key = wfMemcKey( 'marchmadness', 'entryinfo', $entry_id );
	$wgMemc->delete( $key );
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $entry_info );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($entry_info) . "\n" );
	}
	return "Your tiebreaker has been set to {$tiebreaker}.";
		

}

$wgAjaxExportList [] = 'wfSetStartDate';

function wfSetStartDate($tournament_id, $start_date) {
	
	$tournament_info = MarchMadness::get_tournament_info_from_db($tournament_id);
	
	global $wgMemc;
	
	$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`madness_tournament_setup`',
		array(
			'start_date' => $start_date,
			
			),
			array(
				'tournament_id' => $tournament_id,
			), __METHOD__
			);
				
	$key = wfMemcKey( 'marchmadness', 'tournamentinfo', $tournament_id );
	$wgMemc->delete( $key );
	/*
	if ( MarchMadness::madness_use_cache() ) {
		$wgMemc->set( $key, $tournament_info );
		
		wfDebug( "reset {$key} with new array of size: " . sizeof($tournament_info) . "\n" );
	}
	*/
	$tournament_info_2 = MarchMadness::get_tournament_info_from_db($tournament_id);
	return "The startdate has been set to {$start_date} - (" . $tournament_info_2["start_date"] .")";
		

}


$wgAjaxExportList [] = 'wfProcessPicks';

function wfProcessPicks() {
		
		global $wgMemc;
		
		//global $wgRequest;
		if(isset($_POST["bracket-picks-field"])) {
			$picks = $_POST["bracket-picks-field"];
		}
		else {
			$picks = "";
		}
				
		//get database
		$dbr =& wfGetDB( DB_MASTER );

		
		$picks_array = array();
		$temp_picks_array = explode(";", $picks);
		if (sizeof($temp_picks_array)) {
			$temp_single = explode(":", $temp_picks_array[0]);
			$entry_id = $temp_single[4];
			$bracket_id = $temp_single[3];
			$hold_bracket_id = $temp_single[3];
			$tournament_info = MarchMadness::get_entry_info_from_db($entry_id);
			$finals_info = MarchMadness::get_finals_info($tournament_info["tournament_id"]);
			$brackets = MarchMadness::get_brackets_from_db($tournament_info["tournament_id"]);
			$brackets_flip = array_flip($brackets);
			$brackets_under_lower = array();
			foreach($brackets as $k=>$v) {
				$brackets_under_lower[str_replace(" ", "-", strtolower($k))] = $k;
			}
			$teams = MarchMadness::get_teams_from_db($tournament_info["tournament_id"]);
			
			//$entered_picks_total = MarchMadness::get_full_picks_from_db($entry_id);
			
			if ($finals_info["has_finals"] && $bracket_id == $finals_info["bracket_id"]) {
				//$entered_picks_temp = MarchMadness::get_picks_from_db($entry_id, $bracket_id, true, false);
				$entered_picks_temp = MarchMadness::get_full_picks_from_db($entry_id, $bracket_id, true);
				
				foreach($entered_picks_temp as $finals_pick=>$finals_pick_info) {
					//$entered_picks[$finals_pick] = $finals_pick_info[0];
					$entered_picks[$finals_pick] = $finals_pick_info["pick"];
					
				}
			}
			else {
				//$entered_picks = MarchMadness::get_picks_from_db($entry_id, $bracket_id, false, false);
				//$entered_picks_temp = MarchMadness::get_full_picks_from_db($entry_id, $bracket_id, false);
				$entered_picks_temp = MarchMadness::get_full_picks_from_db($entry_id, 0, true);
				foreach($entered_picks_temp as $temp_pick=>$temp_pick_info) {
					//$entered_picks[$finals_pick] = $finals_pick_info[0];
					$entered_picks[$temp_pick] = $temp_pick_info["pick"];
					
				}
			}
	
			
			foreach($temp_picks_array as $value) {
				
				$temp_single = explode(":", $value);
				$which_pick = $temp_single[0];
				$pick = $temp_single[1];
				$round = $temp_single[2];
				$bracket_id = $temp_single[3];
				$entry_id = $temp_single[4];
				
				if (isset($entered_picks[$which_pick])) {
					if ($entered_picks[$which_pick] != $pick) {
						//$sql = "UPDATE madness_tournament_picks SET pick='{$pick}' WHERE entry_id={$entry_id} AND which_pick='{$which_pick}'";
						//madness_mysql_nonquery($sql);
						//echo $sql . "<br/>";
						
						$dbr->update( '`madness_tournament_picks`',
						array(
							'pick' => $pick,
							),
						array(
							'entry_id' => $entry_id,
							'which_pick' => $which_pick
						)
							, __METHOD__
						);
						
						//$entered_picks_temp[$which_pick]["pick"]=$pick;
						//wfDebug( "updating in cache - {$which_pick}, {$pick}\n" );
						$bracket_name = $brackets_flip[$bracket_id];
						$under_pos = strpos($pick, "_");
						$team_seed = substr($pick, $under_pos+1);
						$team_name_key = substr($pick, 0, $under_pos);
						$team_name = $teams[$brackets_under_lower[$team_name_key]][$team_seed];
						//$team_name = $teams[$bracket_name][$team_seed];
						$entered_picks_temp[$which_pick] = array("which_pick"=>$which_pick, "pick"=>$pick, "bracket_name"=>$bracket_name, "team_name"=>$team_name, "pick_correct"=>"-", "pick_points"=>0, "skip_calculate"=>0, "bracket_id"=>$bracket_id, "round"=>$round);
						wfDebug( "updating in cache - {$which_pick}, {$pick}, {$bracket_name}, {$team_name}, -, 0, 0, {$bracket_id}, {$round}\n" );
					}
					
					
					
				}
				else {
					//$sql = "INSERT INTO madness_tournament_picks (entry_id, bracket, round, which_pick, pick) VALUES({$entry_id}, {$bracket_id}, {$round}, '{$which_pick}', '{$pick}')";
					//madness_mysql_nonquery($sql);
					//echo $sql . "<br/>";
					
					$dbr->insert( '`madness_tournament_picks`',
					array(
						'entry_id' => $entry_id,
						'bracket' => $bracket_id,
						'round' => $round,
						'which_pick' => $which_pick,
						'pick' => $pick,
						'tournament_id' => $tournament_info["tournament_id"],
						), __METHOD__
					);
					//$entered_picks[$which_pick] = $pick;
					$bracket_name = $brackets_flip[$bracket_id];
					$under_pos = strpos($pick, "_");
					$team_seed = substr($pick, $under_pos+1);					
					$team_name_key = substr($pick, 0, $under_pos);
					$team_name = $teams[$brackets_under_lower[$team_name_key]][$team_seed];
					//$team_name = $teams[$bracket_name][$team_seed];
					$entered_picks_temp[$which_pick] = array("which_pick"=>$which_pick, "pick"=>$pick, "bracket_name"=>$bracket_name, "team_name"=>$team_name, "pick_correct"=>"-", "pick_points"=>0, "skip_calculate"=>0, "bracket_id"=>$bracket_id, "round"=>$round);
					wfDebug( "adding to cache - {$which_pick}, {$pick}, {$bracket_name}, {$team_name}, -, 0, 0, {$bracket_id}, {$round}\n" );
				}
				
				
				
			}
			
			if ( MarchMadness::madness_use_cache() ) {
				$bracket_key = (($bracket_id == $finals_info["bracket_id"]) ? $finals_info["bracket_id"] : "0");
				$picks_key = wfMemcKey( 'marchmadness', 'fullpicks', $entry_id, $bracket_key );
				$wgMemc->delete( $picks_key );
				$wgMemc->set( $picks_key, $entered_picks_temp );
			
				wfDebug( "reset {$picks_key} with new array of size: " . sizeof($entered_picks_temp) . "\n" );
			}
			
			if ($finals_info["has_finals"] && $bracket_id != $finals_info["bracket_id"]) {
				$sort_order = MarchMadness::get_bracket_sort_order($bracket_id);
				//$entered_picks = get_picks_from_db($entry_id, $finals_info["bracket_id"]);
				$entered_picks = array();
				//$entered_picks_temp = MarchMadness::get_picks_from_db($entry_id, $finals_info["bracket_id"], true, false);
				$entered_picks_temp = MarchMadness::get_full_picks_from_db($entry_id, $finals_info["bracket_id"], true);
				foreach($entered_picks_temp as $finals_pick=>$finals_pick_info) {
					//$entered_picks[$finals_pick] = $finals_pick_info[0];
					$entered_picks[$finals_pick] = $finals_pick_info["pick"];
					
				}
				
				$which_pick = str_replace(" ", "-", strtolower($finals_info["finals_bracket"])) . "_" . $sort_order;
				$round++;
				if (isset($entered_picks[$which_pick])) {
					if ($entered_picks[$which_pick] != $pick) {
						$old_pick = $entered_picks[$which_pick];
						//$sql = "UPDATE madness_tournament_picks SET pick='{$pick}' WHERE entry_id={$entry_id} AND which_pick='{$which_pick}'";
						//madness_mysql_nonquery($sql);
						//echo $sql . "<br/>";
						
						$dbr->update( '`madness_tournament_picks`',
						array(
							'pick' => $pick,
							),
						array(
							'entry_id' => $entry_id,
							'which_pick' => $which_pick
						)
							, __METHOD__
						);
						/*
						$entered_picks_temp[$which_pick]["pick"]=$pick;
						wfDebug( "updating in cache - {$which_pick}, {$pick}\n" );
						*/
						
						$bracket_name = $finals_info["finals_bracket"];
						$last_bracket_name = $brackets_flip[$bracket_id];
						$under_pos = strpos($pick, "_");
						$team_seed = substr($pick, $under_pos+1);
						$team_name = $teams[$last_bracket_name][$team_seed];
						$temp_round = $entered_picks_temp[$which_pick]["round"];
						//$team_name = substr($pick, 0, $under_pos);
						$entered_picks_temp[$which_pick] = array("which_pick"=>$which_pick, "pick"=>$pick, "bracket_name"=>$bracket_name, "team_name"=>$team_name, "pick_correct"=>"-", "pick_points"=>0, "skip_calculate"=>0, "bracket_id"=>$finals_info["bracket_id"], "round"=>$temp_round);
						wfDebug( "updating in cache - {$which_pick}, {$pick}, {$bracket_name}, {$team_name}, -, 0, 0, {$finals_info["bracket_id"]}, {$temp_round}\n" );

						foreach($entered_picks as $forward_check=>$forward_check_pick) {
							if($forward_check != $which_pick && $forward_check_pick==$old_pick) {
								//$sql = "UPDATE madness_tournament_picks SET pick='{$pick}' WHERE entry_id={$entry_id} AND which_pick='{$forward_check}'";
								//madness_mysql_nonquery($sql);
								//echo $sql . "<br/>";
								$dbr->update( '`madness_tournament_picks`',
								array(
									'pick' => $pick,
									),
								array(
									'entry_id' => $entry_id,
									'which_pick' => $forward_check
								)
									, __METHOD__
								);
								/*
								$entered_picks_temp[$forward_check]["pick"]=$pick;
								wfDebug( "updating in cache - {$forward_check}, {$pick}\n" );
								*/
								$bracket_name = $finals_info["finals_bracket"];
								$last_bracket_name = $brackets_flip[$bracket_id];
								$under_pos = strpos($pick, "_");
								$team_seed = substr($pick, $under_pos+1);
								$team_name = $teams[$last_bracket_name][$team_seed];
								$temp_round = $entered_picks_temp[$forward_check]["round"];
								//$team_name = substr($pick, 0, $under_pos);
								$entered_picks_temp[$forward_check] = array("which_pick"=>$forward_check, "pick"=>$pick, "bracket_name"=>$bracket_name, "team_name"=>$team_name, "pick_correct"=>"-", "pick_points"=>0, "skip_calculate"=>0, "bracket_id"=>$finals_info["bracket_id"], "round"=>$temp_round);
								wfDebug( "updating in cache - {$forward_check}, {$pick}, {$bracket_name}, {$team_name}, -, 0, 0, {$finals_info["bracket_id"]}, {$temp_round}\n" );

							}
						}
					}
				}
				else {
					//$sql = "INSERT INTO madness_tournament_picks (entry_id, bracket, round, which_pick, pick) VALUES({$entry_id}, " . $finals_info["bracket_id"] . ", {$round}, '{$which_pick}', '{$pick}')";
					//madness_mysql_nonquery($sql);
					//echo $sql . "<br/>";
					
					$dbr->insert( '`madness_tournament_picks`',
					array(
						'entry_id' => $entry_id,
						'bracket' => $finals_info["bracket_id"],
						'round' => $round,
						'which_pick' => $which_pick,
						'pick' => $pick,
						'tournament_id' => $tournament_info["tournament_id"],
						'skip_calculate' => 1,
						), __METHOD__
					);
					
					$bracket_name = $finals_info["finals_bracket"];
					$last_bracket_name = $brackets_flip[$bracket_id];
					$under_pos = strpos($pick, "_");
					$team_seed = substr($pick, $under_pos+1);
					$team_name = $teams[$last_bracket_name][$team_seed];
					//$team_name = substr($pick, 0, $under_pos);
					$entered_picks_temp[$which_pick] = array("which_pick"=>$which_pick, "pick"=>$pick, "bracket_name"=>$bracket_name, "team_name"=>$team_name, "pick_correct"=>"-", "pick_points"=>0, "skip_calculate"=>1, "bracket_id"=>$finals_info["bracket_id"], "round"=>$round);
					wfDebug( "adding to cache - {$which_pick}, {$pick}, {$bracket_name}, {$team_name}, -, 0, 0, {$finals_info["bracket_id"]}, {$round}\n" );

				}
				
				if (MarchMadness::madness_use_cache()) {
					$picks_key = wfMemcKey( 'marchmadness', 'fullpicks', $entry_id, $finals_info["bracket_id"] );
					$wgMemc->delete( $picks_key );
					$wgMemc->set( $picks_key, $entered_picks_temp );
					
					wfDebug( "reset {$picks_key} with new array of size: " . sizeof($entered_picks_temp) . "\n" );
				}
			}
			
		}
		
		//header("Location:{$return}");
		//die();
		
		return "ok";
		
	}

	
$wgAjaxExportList [] = 'wfGetPrintableBracket';
function wfGetPrintableBracket() {
	
	//header("Content-type: image/png");

	global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		if (!$wgUser->isLoggedIn()) {
			$output = "You must be logged in. Please log in and come back here.";
			return $output;
		}
		
		//$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadnessPrintable.css?{$wgStyleVersion}\"/>\n");

		$output .="<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadnessPrintable.css?{$wgStyleVersion}\"/>\n";
		
		//$current_user_id = User::idFromName($wgUser->getName());
		$current_user_name = $wgUser->getName();
		//$current_user_id = $wgUser->getID();
		$current_user_id = User::idFromName($current_user_name);
		
		
		/*
		if (isset($_GET["tournament_id"])) {
			$tournament_id = $_GET["tournament_id"];
		}
		else {
			$tournament_id = 1;
		}
		*/
		if (isset($_GET["entry_id"])) {
			$entry_id = $_GET["entry_id"];
		}
		else {
			//$entry_id = 1;
			$admin_link = $link = MarchMadnessGroupAdmin::march_madness_group_link();
			header("Location: " . $admin_link);
			die("Redirecting");
		}
		$info = MarchMadness::get_entry_info_from_db($entry_id);
		
		if (!sizeof($info)) {
			
			$output = "This is not a valid entry";
			
			return $output;
		}
		
		$is_current_user = (($current_user_id == $info["user_id"]) ? true : false);
		//$tournament_started =  (intval(time()) > intval($info["start_date"]) ? true : false);
		
		$tournament_id=$info["tournament_id"];

		$tournament_started = MarchMadness::tournament_started($tournament_id);
		
		if (!$is_current_user && !$tournament_started) {
			$output = "Until the tournament starts, you cannot see another entry's picks.<br/>";
			return $output;
		}
		
		//$group_members = MarchMadness::get_group_members_from_db($entry_id, $info["group_id"]);
	
		
		
		$brackets = MarchMadness::get_brackets_from_db($tournament_id);
		$brackets_done = array();
		
		if (isset($_GET["bracket"])) {
			$bracket = $_GET["bracket"];
		}
		else {
			$brackets_keys = array_keys($brackets);
			$bracket = $brackets_keys[0];
		}
		
		$teams = MarchMadness::get_teams_from_db($tournament_id);
		//$picks = MarchMadness::get_picks_from_db($entry_id);
		$picks_full = MarchMadness::get_full_picks_from_db($entry_id, 0, true);
		$picks = array();
		foreach($picks_full as $the_pick_id=>$the_picks_info) {
			$picks[$the_pick_id] = $the_picks_info["team_name"];
		}
		
		$bracket_points = array();
		$bracket_max = array();
		foreach($brackets as $points_bracket_name=>$points_bracket_id) {
			$bracket_points[$points_bracket_id] = 0;
			$bracket_max[$points_bracket_id] = 0;
		}
		$total_points = 0;
		$total_max = 0;
		
		$scoring_levels = explode(",", "0,".$info["scoring"]);
		
		
		//$incorrect_picks
		
		$bracket_id = isset($brackets[$bracket]) ? $brackets[$bracket] : 0;
		
		$pick_counts = array();
		$brackets_lower = array();
		foreach($brackets as $bracket_temp_name=>$bracket_temp_id) {
			$brackets_done[$bracket_temp_name] = false;
			$brackets_lower[str_replace(" ", "-", strtolower($bracket_temp_name))] = $bracket_temp_name;
			$pick_counts[$bracket_temp_name] = 0;
			//echo str_replace(" ", "-", strtolower($bracket_temp_name)) . " " .$bracket_temp_name . "<br/>";
		}
		
		foreach($picks as $pick_temp_name=>$pick_temp_value) {
			$pick_temp_name = substr($pick_temp_name, 0, strpos($pick_temp_name, "_"));
			if (isset($brackets_lower[$pick_temp_name]) && isset($pick_counts[$brackets_lower[$pick_temp_name]])) {
				//echo $pick_temp_name . " " .$brackets_lower[$pick_temp_name] . "<br/>";
				$pick_counts[$brackets_lower[$pick_temp_name]]++;
			}
		}
		
		$winners = MarchMadness::get_winners_from_db($tournament_id);
		$winners_indiv = array();
		$losers_indiv = array();
		
		foreach ($winners as $w_bracket_id=>$w_bracket_winners) {
			foreach($w_bracket_winners as $w_round=>$w_round_winners) {
				foreach($w_round_winners as $w_game_id=>$w_game_info) {
					$winners_indiv[$w_game_info["which_game"]] = $w_game_info["winner"];
					
					//echo $w_game_info["which_game"] . "-" . $picks_full[$w_game_info["which_game"]]["pick"] . "-" . $w_game_info["winner"] . "<br/>";
					//echo $w_game_id . "<br/>";
					
					if(isset($picks_full[$w_game_info["which_game"]]) && $picks_full[$w_game_info["which_game"]]["pick"] == $w_game_info["winner"]) {
						if(!$picks_full[$w_game_info["which_game"]]["skip_calculate"]) {
							$bracket_points[$w_bracket_id] += $scoring_levels[$w_round];
							$total_points += $scoring_levels[$w_round];
							
							
						}
					}
					
					$losers_indiv[$w_game_info["loser"]] = $w_round;
				}
			}
		}
		
		
		
		$finals_info = MarchMadness::get_finals_info($tournament_id);
		
	
		
		if ($info["has_finals"]) {
			$has_finals = true;
			$finals_bracket_name = $finals_info["finals_bracket"];;
			//$finals_picks_temp = MarchMadness::get_picks_from_db($entry_id, $finals_info["bracket_id"], true);
			$finals_picks_temp = MarchMadness::get_full_picks_from_db($entry_id, $finals_info["bracket_id"], true);
			$finals_picks = array();
			$finals_teams = array();
			foreach($finals_picks_temp as $finals_pick=>$finals_pick_info) {
				//$finals_picks[$finals_pick] = $finals_pick_info[0];
				//$finals_teams[$finals_pick] = $finals_pick_info[1];
				$finals_picks[$finals_pick] = $finals_pick_info["pick"];
				$finals_teams[$finals_pick] = $finals_pick_info["team_name"];
			}
			$finals_round = 0;
			$num_earlier_rounds = $info["num_teams"]/$info["num_brackets"];
			while($num_earlier_rounds >= 1) {
				$num_earlier_rounds = $num_earlier_rounds/2;
				$finals_round++;
			}
			$finals_round_preserve = $finals_round;
			
		}
		else {
			$has_finals = false;
			$finals_bracket_name = "";
			$finals_picks = array();
			$finals_teams = array();
		}
		
		foreach($picks_full as $picks_full_game=>$picks_full_game_info) {
			$under_pos = strpos($picks_full_game_info["which_pick"], "_");
			$temp_which_pick = substr($picks_full_game_info["which_pick"], $under_pos+1);
			if (!isset($winners[$picks_full_game_info["bracket_id"]][$picks_full_game_info["round"]-1][$temp_which_pick])) {
			//if(!isset($winners_indiv[$picks_full_game_info["which_pick"]])) {
				if (!(isset($losers_indiv[$picks_full_game_info["pick"]]) && $losers_indiv[$w_game_info["loser"]]<=($picks_full_game_info["round"]-1))) {
					//if(!$picks_full[$w_game_info["which_game"]]["skip_calculate"]) {
						//echo intval($picks_full_game_info["bracket_id"]) . " " . intval($finals_info["bracket_id"]) . "<br/>";
					if (!$winners[$picks_full_game_info["bracket_id"]][$picks_full_game_info["round"]-1][$temp_which_pick]["skip_calculate"] && !($info["has_finals"] && (intval($picks_full_game_info["bracket_id"])==intval($finals_info["bracket_id"])) && $picks_full_game_info["round"]-1==$finals_round_preserve)) {
						
						if ($picks_full_game_info["bracket_id"] == intval($finals_info["bracket_id"])) {
							//echo "adding " . $scoring_levels[$picks_full_game_info["round"]-1]. "<br/>";
							$round_to_check = $picks_full_game_info["round"]-2;
						}
						else {
							$round_to_check = $picks_full_game_info["round"]-1;
						}
						//echo $picks_full_game_info["bracket_id"] . " " . $picks_full_game_info["which_pick"] . " " . $round_to_check . " + " . $scoring_levels[$round_to_check] . "<br/>";
						$bracket_max[$picks_full_game_info["bracket_id"]] += $scoring_levels[$round_to_check];
						$total_max += $scoring_levels[$round_to_check];
					}
				}
			}
			else {
				//echo $picks_full_game_info["which_pick"]
			}
		}
		
		//$finals_team_vals = array_flip($finals_picks);
			
		$num_brackets = sizeof($teams);
		
		$output .= "";
		
		
		
			
		
		if ($bracket == $finals_bracket_name) {
			$num_teams = $num_brackets;
		}
		else {
			$num_teams = sizeof($teams[$bracket]);;
		}
		
		$total = $num_teams+1;
		$rounds = 0;
		$temp = $num_teams;
		while($temp >= 1) {
			$temp = $temp/2;
			$rounds++;
		}
		
		//$script_output = "<script type=\"text/javascript\">var num_teams = {$num_teams};</script>";
		
		
		//$games_order = array(1,8,5,4,6,3,7,2);
		$games_order = explode(",", $info["games_order"]);
		
		
		
			
		foreach($teams as $team_bracket=>$team_picks) {
			$team_vals[$team_bracket] = array_flip($team_picks);
		}
		
			
		$output .= "<div id=\"march-madness\">";
		
		$output .= "
				<!--[if IE]>
				<style type=\"text/css\">
				table div {
					margin:0px 1px 0px 1px !important;
					line-height:10px !important;
					height: 10px !important;
				}
				
				.madness-background {
					top:300px !important;
					left:330px !important;
				}
				
				.final-four-picks {
					top: 350px !important;
					left: 295px !important;
				}
				
				.final-four-picks div{
					margin:0px 1px 0px 1px !important;
					line-height:12px !important;
					height: 12px !important;
				}
				</style>
				<![endif]-->";
		
		$page_title .= $info["entry_name"]." - (" . $info["group_name"] . ") (" . $info["tournament_name"] . ")";
		
		$group_info = MarchMadness::get_group_info_from_db($info["group_id"]);
		
		$game_divs = array();
		for($team_count=0; $team_count<($num_teams); $team_count++) {
			$game_divs[$team_count] = array();
		}
		
		//$output = "rounds: " . $rounds . "<br/>";
		$left_brackets = array();
		$right_brackets = array();
		$print_count=1;
		foreach($brackets as $bracket=>$bracket_id) {
			if(!($bracket==$finals_bracket_name)) {
				$g = 1;
				$p = 1;
				$t = 1;
				$w = 1;
				$f = 1;
				for($r=1; $r<=$rounds; $r++) {
					if ($r == $rounds) {
						$top = ((pow(2,$r-2)-1)*29) + 13;
					}
					else {
						$top = ((pow(2,$r-1)-1)*29);
					}
					//$output .= "<div id='round_{$r}'>";
					//$game_divs[$r] = array();
					
					$game_in_round = 0;
					
					$bracket_lower = str_replace(" ", "-", strtolower($bracket));
					for ($i=0; $i<$num_teams/(pow(2,$r)); $i++) { 
					
						if ($r==1) {
							if ($has_finals && $bracket == $finals_bracket_name) {
								$which_array = $finals_teams;
								
								$team_1 = $p++;
								$team_2 = $p++;
								//$team_1_code = (isset($team_vals[$bracket][$teams[$bracket][$team_1]]) ? $team_vals[$bracket][$teams[$bracket][$team_1]]:"");
								//$team_2_code = (isset($team_vals[$bracket][$teams[$bracket][$team_2]]) ? $team_vals[$bracket][$teams[$bracket][$team_2]]:"");
								//$team_1_id = $team_1;
								//$team_2_id = $team_2;
		
								
								
								$team_1 = "{$bracket_lower}_" .$team_1;
								$team_2 = "{$bracket_lower}_" .$team_2;
								
								$team_1_code = (isset($finals_picks[$team_1]) ? $finals_picks[$team_1]:"");
								$team_2_code = (isset($finals_picks[$team_2]) ? $finals_picks[$team_2]:"");
								
								$display_seed_1 = "";
								$display_seed_2 = "";
								
								if ($team_1_code != "") {
									$under_pos = strpos($team_1_code, "_");
									$display_seed_1 = substr($team_1_code, $under_pos+1) . ". ";
								}
								if ($team_2_code != "") {
									$under_pos = strpos($team_2_code, "_");
									$display_seed_2 = substr($team_2_code, $under_pos+1) . ". ";
								}
								
							}
							else {
								$which_array = $teams[$bracket];
								$team_1 = $games_order[$i];
								$team_2 = $total-$team_1;
								$team_1_code = $team_1;
								$team_2_code = $team_2;
								
								$display_seed_1 = $team_1 . ". ";
								$display_seed_2 = $team_2 . ". ";
								//$display_seed_1 = "";
								//$display_seed_2 = "";
							}
							
							
							
							
						}
						else {
							if ($has_finals && $bracket == $finals_bracket_name) {
								$which_array = $finals_teams;
								
								$team_1 = $p++;
								$team_2 = $p++;
								
								//$team_1_code = (isset($team_vals[$bracket][$teams[$bracket][$team_1]]) ? $team_vals[$bracket][$teams[$bracket][$team_1]]:"");
								//$team_2_code = (isset($team_vals[$bracket][$teams[$bracket][$team_2]]) ? $team_vals[$bracket][$teams[$bracket][$team_2]]:"");
								//$team_1_id = $team_1;
								//$team_2_id = $team_2;
								
								$team_1 = "{$bracket_lower}_" .$team_1;
								$team_2 = "{$bracket_lower}_" .$team_2;
								
								$team_1_code = (isset($finals_picks[$team_1]) ? $finals_picks[$team_1]:"");
								$team_2_code = (isset($finals_picks[$team_2]) ? $finals_picks[$team_2]:"");
								
								$display_seed_1 = "";
								$display_seed_2 = "";
								if ($team_1_code != "") {
									$under_pos = strpos($team_1_code, "_");
									$display_seed_1 = substr($team_1_code, $under_pos+1) . ". ";
								}
								if ($team_2_code != "") {
									$under_pos = strpos($team_2_code, "_");
									$display_seed_2 = substr($team_2_code, $under_pos+1) . ". ";
								}
								
								/*
								$which_array = $finals_picks;
								$team_1 = $p++;
								$team_2 = $p++;
								$team_1_code = (isset($finals_teams[$team_1]) ? $finals_team_vals[$finals_teams[$team_1]]:"");
								$team_2_code = (isset($finals_teams[$team_2]) ? $finals_team_vals[$finals_teams[$team_2]]:"");
								//$team_1_id = $team_1;
								//$team_2_id = $team_2;
								*/
							}
							else {
								//$which_array = (isset($picks[$bracket]) ? $picks[$bracket] : array());
								$which_array = $picks;
								$team_1 = $p++;
								$team_2 = $p++;
								//$team_1_code = (isset($team_vals[$bracket][$teams[$bracket][$team_1]]) ? $team_vals[$bracket][$teams[$bracket][$team_1]]:"");
								//$team_2_code = (isset($team_vals[$bracket][$teams[$bracket][$team_2]]) ? $team_vals[$bracket][$teams[$bracket][$team_2]]:"");
								//$team_1_id = $team_1;
								//$team_2_id = $team_2;
								$team_1 = "{$bracket_lower}_" .$team_1;
								$team_2 = "{$bracket_lower}_" .$team_2;
								
								$team_1_code = (isset($picks[$team_1]) && isset($team_vals[$bracket][$picks[$team_1]]) ? $team_vals[$bracket][$picks[$team_1]]:"");
								$team_2_code = (isset($picks[$team_2]) && isset($team_vals[$bracket][$picks[$team_2]]) ? $team_vals[$bracket][$picks[$team_2]]:"");
								
								$display_seed_1 = "";
								$display_seed_2 = "";
								
								//echo $team_1_code;
								
								if ($team_1_code != "") {
									/*
									$under_pos = strpos($team_1_code, "_");
									$display_seed_1 = substr($team_1_code, $under_pos+1) . ". ";
									*/
									$display_seed_1 = $team_1_code . ". ";
								}
								if ($team_2_code != "") {
									/*
									$under_pos = strpos($team_2_code, "_");
									$display_seed_2 = substr($team_2_code, $under_pos+1) . ". ";
									*/
									$display_seed_2 = $team_2_code . ". ";
								}
								
								
								
								
								
								//$team_1_code = "{$bracket}_{$team_1}";
								//$team_2_code = "{$bracket}_{$team_2}";
							}
							
							/*
							$team_1 = $p++;
							$team_2 = $p++;
							$team_1_code = (isset($team_vals[$bracket][$teams[$bracket][$team_1]]) ? $team_vals[$bracket][$teams[$bracket][$team_1]]:"");
							$team_2_code = (isset($team_vals[$bracket][$teams[$bracket][$team_2]]) ? $team_vals[$bracket][$teams[$bracket][$team_2]]:"");
							*/
						}
						
						//$next = $g + ($num_teams/(pow(2,$r-1)));
						$next = $g + $num_teams;
						if ($has_finals && $bracket == $finals_bracket_name) {
							$game = "{$bracket_lower}_{$g}";
							$output_r = $finals_round++;
						}
						else {
							$game = "{$bracket_lower}_{$g}";
							$output_r = $r;
						}
						$team_1_spot = "{$bracket_lower}_team_" . $t++;
						$team_2_spot = "{$bracket_lower}_team_" . $t++;
						$team_1_name = (isset($which_array[$team_1]) ? $which_array[$team_1]:"&nbsp;");
						$team_2_name = (isset($which_array[$team_2]) ? $which_array[$team_2] : "&nbsp;");
						
						$team_1_name = MarchMadness::truncate_text($team_1_name, 16, false);
						$team_2_name = MarchMadness::truncate_text($team_2_name, 16, false);
						
						if ($r < $rounds) {
							
							$team_1_correct_class="";
							$team_2_correct_class="";
							
							//echo $bracket_lower . "_" . $team_1_code . " " . $bracket_lower . "_" . $team_2_code . "<br/>";
							
							if ($finals_bracket_name != $bracket) {
								if ($r>1) {
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r-1]) && isset($winners[$bracket_id][$r-1][$w])) {
										if($bracket_lower . "_" . $team_1_code==$winners[$bracket_id][$r-1][$w]["winner"]) {
											$team_1_correct_class="correct-pick";
											/*
											if (!$winners[$bracket_id][$r-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r-1];
											}
											*/
										}
										else {
											$team_1_correct_class="incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$bracket_lower . "_" . $team_1_code]) && $losers_indiv[$bracket_lower . "_" . $team_1_code]<=($r-1)) {
										$team_1_correct_class="incorrect-pick-forward";
									}
									$w++;
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r-1]) && isset($winners[$bracket_id][$r-1][$w])) {
										if($bracket_lower . "_" . $team_2_code==$winners[$bracket_id][$r-1][$w]["winner"]) {
											$team_2_correct_class=" correct-pick";
											/*
											if (!$winners[$bracket_id][$r-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r-1];
											}
											*/
										}
										else {
											$team_2_correct_class=" incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$bracket_lower . "_" . $team_2_code]) && $losers_indiv[$bracket_lower . "_" . $team_2_code]<=($r-1)) {
										$team_2_correct_class=" incorrect-pick-forward";
									}
									$w++;
								}
							}
							else {
								if ($r>1) {
									$r_check = ($finals_round_preserve+$r)-1;
									
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r_check-1]) && isset($winners[$bracket_id][$r_check-1][$w])) {
										if($team_1_code==$winners[$bracket_id][$r_check-1][$w]["winner"]) {
											$team_1_correct_class="correct-pick";
											/*
											if (!$winners[$bracket_id][$r_check-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r_check-1];
											}
											*/
										}
										else {
											$team_1_correct_class="incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$team_1_code]) && $losers_indiv[$team_1_code]<=($r_check-1)) {
										$team_1_correct_class="incorrect-pick-forward";
									}
									$w++;
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r_check-1]) && isset($winners[$bracket_id][$r_check-1][$w])) {
										if($team_2_code==$winners[$bracket_id][$r_check-1][$w]["winner"]) {
											$team_2_correct_class=" correct-pick";
											/*
											if (!$winners[$bracket_id][$r_check-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r_check-1];
											}
											*/
										}
										else {
											$team_2_correct_class=" incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$team_2_code]) && $losers_indiv[$team_2_code]<=($r_check-1)) {
										$team_2_correct_class=" incorrect-pick-forward";
									}
									$w++;
								}
								else {
									$r_check = ($finals_round_preserve+$r)-1;
					
									
									
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r_check-1]) && isset($winners[$bracket_id][$r_check-1][$w])) {
										if($team_1_code==$winners[$bracket_id][$r_check-1][$w]["winner"]) {
											$team_1_correct_class="correct-pick";
											/*
											if (!$winners[$bracket_id][$r_check-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r_check-1];
											}
											*/
										}
										else {
											$team_1_correct_class="incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$team_1_code]) && $losers_indiv[$team_1_code]<=($r_check-1)) {
										$team_1_correct_class="incorrect-pick-forward";
									}
									$w++;
									if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r_check-1]) && isset($winners[$bracket_id][$r_check-1][$w])) {
										if($team_2_code==$winners[$bracket_id][$r_check-1][$w]["winner"]) {
											$team_2_correct_class=" correct-pick";
											/*
											if (!$winners[$bracket_id][$r_check-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r_check-1];
											}
											*/
										}
										else {
											$team_2_correct_class=" incorrect-pick";
										}
										
									}
									else if (isset($losers_indiv[$team_2_code]) && $losers_indiv[$team_2_code]<=($r_check-1)) {
										$team_2_correct_class=" incorrect-pick-forward";
									}
									$w++;
								}
							}
							$which_row = ($game_in_round++ * pow(2, $r-1));
							//$game_divs[$which_row][$r-1] = "<div id='" . $game . "' ><div class='{$team_1_correct_class}' team='{$team_1_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_1_spot}' {$on_click_1} >" . $display_seed_1 . $team_1_name . "</div><div class='{$team_2_correct_class}' team='{$team_2_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_2_spot}' {$on_click_2} >" . $display_seed_2 . $team_2_name . "</div></div>";
							$game_divs[$which_row][$r-1] = "<div class='{$team_1_correct_class}' team='{$team_1_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_1_spot}' >" . $display_seed_1 . $team_1_name . "</div>";
							$which_row = (($game_in_round) * pow(2, $r-1));
							$game_divs[$which_row][$r-1] = "<div class='{$team_2_correct_class}' team='{$team_2_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_2_spot}' >" . $display_seed_2 . $team_2_name . "</div>";
						}
						else {
							$team_1_correct_class = "";
							
							if ($finals_bracket_name != $bracket) {
			
								if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r-1]) && isset($winners[$bracket_id][$r-1][$w])) {
									if($bracket_lower . "_" . $team_1_code==$winners[$bracket_id][$r-1][$w]["winner"]) {
										$team_1_correct_class="correct-pick";
										/*
										if (!$winners[$bracket_id][$r-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r-1];
										}
										*/
									}
									else {
										$team_1_correct_class="incorrect-pick";
									}
									
									
								}
								else if (isset($losers_indiv[$bracket_lower . "_" . $team_1_code]) && $losers_indiv[$bracket_lower . "_" . $team_1_code]<=($r-1)) {
										$team_1_correct_class="incorrect-pick-forward";
								}
							}
							else {
								$r_check = ($finals_round_preserve+$r)-1;
					
									
								
								if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$r_check-1]) && isset($winners[$bracket_id][$r_check-1][$w])) {
									if($team_1_code==$winners[$bracket_id][$r_check-1][$w]["winner"]) {
										$team_1_correct_class="correct-pick";
										/*
										if (!$winners[$bracket_id][$r_check-1][$w]["skip_calculate"]) {
												$total_points+=$scoring_levels[$r_check-1];
										}
										*/
									}
									else {
										$team_1_correct_class="incorrect-pick";
									}
									
								}
								else if (isset($losers_indiv[$team_1_code]) && $losers_indiv[$team_1_code]<=($r_check-1)) {
									$team_1_correct_class="incorrect-pick-forward";
								}
							}
							$which_row = ($game_in_round * pow(2, $r-1));
							//$game_divs[$which_row][$r-1] = "<div id='" . $game . "'><div team='{$team_1_code}' round='{$output_r}' id='{$team_1_spot}' class='{$team_1_correct_class}' >" . $display_seed_1 . $team_1_name . "</div></div>";
							$game_divs[$which_row][$r-1] = "<div team='{$team_1_code}' round='{$output_r}' id='{$team_1_spot}' class='{$team_1_correct_class}' >" . $display_seed_1 . $team_1_name . "</div>";
						}
						$game_in_round++;
						$g++;
					}
						
					
						
					
					//$output .= "</div>";
					//$output .= "<form method=\"POST\" action=\"bracket_ajax.php\" id=\"bracket-picks\" name=\"bracket-picks\">";
					
					
				}
				
				if($print_count == 1){
					$output .= "<div class=\"printable-picks-left\">";
				}
				elseif($print_count == 3) {
					$output .= "<div class=\"printable-picks-right\">";
				}
				
				if ($print_count<3) {
					$output .= "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"picks-table\">\n";
					$output .= "<tr><td colspan=\"5\"><span>{$bracket}</span></td></tr>";
					for($which_row=0; $which_row<sizeof($game_divs); $which_row++) {
						$output .= "<tr><!--{$which_row}-->\n";
						foreach($game_divs[$which_row] as $round_id=>$game_output) {
							$output .= "<td rowspan=\"" . pow(2, $round_id) . "\">{$game_output}</td>\n";
						}
						$output .= "</tr>\n";
					}
					$output .= "</table>\n";
					$lower = str_replace(" ", "-", strtolower($bracket));
					$left_brackets[$lower] = 1;
					
				}
				else {
					$output .= "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"picks-table\">\n";
					$output .= "<tr><td colspan=\"5\" align=\"right\"><span>{$bracket}</span></td></tr>";
					for($which_row=0; $which_row<sizeof($game_divs); $which_row++) {
						$output .= "<tr><!--{$which_row}-->\n";
						$game_divs_rev[$which_row] = array_reverse($game_divs[$which_row], true);
						foreach($game_divs_rev[$which_row] as $round_id=>$game_output) {
							$output .= "<td rowspan=\"" . pow(2, $round_id) . "\">{$game_output}</td>\n";
						}
						$output .= "</tr>\n";
					}
					$output .= "</table>\n";
					$lower = str_replace(" ", "-", strtolower($bracket));
					$right_brackets[$lower] = 1;

				}
				if($print_count == 2 || $print_count ==4 ){
					$output .= "</div>";
				}
				$print_count++;
			}
			
			
		}
		$output .= "<div class=\"cleared\"></div>";

		if ($info["has_finals"]) {
			$finals_team_left = "&nbsp;";
			$finals_team_right = "&nbsp;";
			$finals_team = "&nbsp;";
			$left_correct_class= "";
			$right_correct_class= "";
			$final_correct_class= "";
	
			foreach($finals_picks_temp as $which_pick=>$which_info) {
				
				$under_pos = strpos($which_info["pick"], "_");
				$lower_bracket = substr($which_info["pick"], 0, $under_pos);
				$seed = substr($which_info["pick"], $under_pos+1);
				//echo $lower_bracket . "<br/>";
				
				
				if (intval($which_info["round"]) == 7) {
					if (isset($left_brackets[$lower_bracket])) {
						$finals_team_left = $seed . ". " .$which_info["team_name"];
						if (isset($winners[5]) && isset($winners[5][5]) && isset($winners[5][5][5])) {
							
							if ($winners[5][5][5]["winner"] == $which_info["pick"]) {
								$left_correct_class = "correct-pick";
							}
							else{
								//echo sizeof($winners) . " : " . $winners[5][5][5]["winner"] . ":" . $which_info["pick"] . "<br/>";
								$left_correct_class= "incorrect-pick";
							}
						}
						else {
							if (isset($losers_indiv[$which_info["pick"]]) && $losers_indiv[$which_info["pick"]] < 5) {
								$left_correct_class = "incorrect-pick-forward";
							}
						}
					}
					elseif (isset($right_brackets[$lower_bracket])) {
						$finals_team_right = $seed . ". " .$which_info["team_name"];
						if (isset($winners[5]) && isset($winners[5][5]) && isset($winners[5][5][6])) {
							
							if ($winners[5][5][6]["winner"] == $which_info["pick"]) {
								$right_correct_class = "correct-pick";
								//echo sizeof($winners) . " : " . $winners[5][5][6]["winner"] . ":" . $which_info["pick"] . "<br/>";
							}
							else{
								$right_correct_class= "incorrect-pick";
							}
						}
						else {
							if (isset($losers_indiv[$which_info["pick"]]) && $losers_indiv[$which_info["pick"]] < 5) {
								$right_correct_class = "incorrect-pick-forward";
							}
						}
	
					}
				}
				elseif(intval($which_info["round"]) == 8) {
					$finals_team = $seed . ". " .$which_info["team_name"];
					if (isset($winners[5]) && isset($winners[5][6]) && isset($winners[5][6][7])) {
						
						if ($winners[5][6][7]["winner"] == $which_info["pick"]) {
							$final_correct_class = "correct-pick";
							//echo sizeof($winners) . " : " . $winners[5][5][6]["winner"] . ":" . $which_info["pick"] . "<br/>";
						}
						else{
							$final_correct_class = "incorrect-pick";
						}
					}
					else {
						if (isset($losers_indiv[$which_info["pick"]]) && $losers_indiv[$which_info["pick"]] < 6) {
							$final_correct_class = "incorrect-pick-forward";
						}
					}
				}
			}
			if (sizeof($right_brackets)) {
				$output .= "<table class=\"final-four-picks\">";
				$output .= "<tr><td><div class=\"final-four-pick-left {$left_correct_class}\">{$finals_team_left}</div></td><td><div class=\"finals-pick {$final_correct_class}\">{$finals_team}</div></td><td><div class=\"final-four-pick-right {$right_correct_class}\">{$finals_team_right}</div></td></tr>";
				$output .= "</table>";
			}
			else if (sizeof($left_brackets==2)) {
				$output .= "<table class=\"final-four-picks\">";
				$output .= "<tr><td width=\"70\">&nbsp;</td><td><div class=\"finals-pick {$left_correct_class}\">{$finals_team_left}</div></td><td width=\"70\">&nbsp;</td></tr>";
				$output .= "</table>";
			}
			
			$output .= "</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "<div class=\"madness-background\"><img src=\"http://images.wikia.com/openserving/sports/images/sports/logo.png\"></div>";
		}
		return $output;
}


?>