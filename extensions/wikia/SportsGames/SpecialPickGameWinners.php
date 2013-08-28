<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameWinners';

function wfSpecialPickGameWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameWinners extends SpecialPage {

	
	function PickGameWinners(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameWinners");
	}
	
	function execute($value){
		
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$sport_id = $value;
		$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);
		//$category = '20070823';
		$output = "";
		$users_output = "";
		$users_array = array();
		
		$oneDay = 60*60*24;	
		$today = date("Ymd");	
	
		if ($wgRequest->getVal($sport_specifics["param"]) == "") {
			
			$curDateUnix = time();
			
			//$curGetDay = $curDateUnix + $oneDay;
			$curGetDay = $curDateUnix;
			$curDate = date("Ymd", $curGetDay);
			$category = PickGame::get_category_from_date($curDate, $sport_specifics);		
			
		}
		else {
			//$curDate = $wgRequest->getVal($sport_specific["param"]);
			$category = $wgRequest->getVal($sport_specifics["param"]);
			$curDate = PickGame::get_date_from_category($category, $sport_specifics);
		}
		
		$category = PickGame::check_first_game($category, $sport_specifics);
		$curDate = PickGame::check_first_game($curDate, $sport_specifics);
		
		
		$winners_array = $this->pick_game_get_winners($sport_id, $category);
		$pick_array = $this->pick_game_get_all_picks($sport_id, $category);
		
		$output .= "winners: " . sizeof($winners_array) . "<br/>";
		
		for ($i=0; $i<sizeof($winners_array); $i++) {
			$output .= " --- <br/>";
			if(isset($pick_array[$winners_array[$i]["game_identifier"]])) {
				
				//$output .= "yes - ";
			    foreach($pick_array[$winners_array[$i]["game_identifier"]] as $key => $value) {
				    
				    if (!isset($users_array[$value["user_name"]])) {
					    $users_array[$value["user_name"]]["user_name"] = $value["user_name"];
					    $users_array[$value["user_name"]]["correct"] = 0;
					    $users_array[$value["user_name"]]["incorrect"] = 0;
					    $users_array[$value["user_name"]]["points"] = 0;
					    $users_array[$value["user_name"]]["user_id"] = $value["user_id"];
					    $users_array[$value["user_name"]]["category"] = $category;
					    $users_array[$value["user_name"]]["sport_id"] = $sport_id;

				    }
				    
				    $output .= "{$value["pick_identifier"]} - {$value["user_name"]} picked - {$value["pick_choice"]}";
				    if ($winners_array[$i]["game_winner"] == $value["pick_choice"]) {
					    $value["pick_correct"] = 1;
					    $users_array[$value["user_name"]]["correct"]++;
					    $value["pick_wager_result"] = $value["pick_wager"];
					    $users_array[$value["user_name"]]["points"] += $value["pick_wager_result"];
					    $output .= " CORRECT ({$value["pick_wager_result"]})<br/>";
				    }
				    else {
					    $value["pick_correct"] = 0;
					    $users_array[$value["user_name"]]["incorrect"]++;
					    $value["pick_wager_result"] = -1 * $value["pick_wager"];					    
					    $users_array[$value["user_name"]]["points"] += $value["pick_wager_result"];
					    $output .= " INCORRECT ({$value["pick_wager_result"]})<br/>";
				    }
			    }
		
		
			}
		}
		
		$users_output .= "Users: " . sizeof($users_array) . "<br/>";
		foreach($users_array as $key => $value) {
			
			$users_output .= "{$value["user_name"]} - Correct: {$value["correct"]} - Incorrect: {$value["incorrect"]} - result: {$value["points"]} userID: {$value["user_id"]} - category: {$value["category"]} - sport id: {$value["sport_id"]}<br/>";
			$this->pickgame_update_pick_stats($value);
			
		}

		$wgOut->addHTML($users_output . "<br/><br/>");
		
		$wgOut->addHTML($output);
	}
	
	

	function pick_game_get_winners($sport_id, $category) {

		$dbr =& wfGetDB( DB_MASTER );
		//$s = $dbr->selectRow( 'pick_games', array( 'pick_identifier' ), array( 'pick_identifier' => $pick_identifier ),"" );
		
		//$sql = "SELECT pick_games.pick_game_id, pick_games_results.game_result_id FROM pick_games LEFT OUTER JOIN pick_games_results ON pick_games.pick_game_id = pick_games_results.pick_game_id WHERE pick_games.pick_identifier = '{$pick_identifier}'";
		$sql = "SELECT pick_games_results.*, pick_games.pick_category FROM pick_games, pick_games_results WHERE pick_games.pick_game_id=pick_games_results.pick_game_id AND pick_games.pick_category='{$category}' AND pick_games_results.sport_id={$sport_id} AND pick_games_results.game_Status=1 ORDER BY pick_games_results.game_identifier";
	
	
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals[$i]["game_identifier"] = $row->game_identifier;
			 $returnVals[$i]["game_id"] = $row->pick_game_id;
			 $returnVals[$i]["game_winner"] = $row->game_winner_abbr;
			 $returnVals[$i]["game_status"] = $row->game_status;
			 $returnVals[$i]["sport_id"] = $row->sport_id;
			 
			 $i++;
	
		}
		
		return $returnVals;

	}

	function pick_game_get_all_picks($sport_id, $category) {
		
		$dbr =& wfGetDB( DB_MASTER );
		//$s = $dbr->selectRow( 'pick_games', array( 'pick_identifier' ), array( 'pick_identifier' => $pick_identifier ),"" );
		
		//$sql = "SELECT pick_games.pick_game_id, pick_games_results.game_result_id FROM pick_games LEFT OUTER JOIN pick_games_results ON pick_games.pick_game_id = pick_games_results.pick_game_id WHERE pick_games.pick_identifier = '{$pick_identifier}'";
		$sql = "SELECT pick_games_picks.*, pick_games.pick_sport_id, pick_games.pick_category, pick_games.pick_identifier FROM pick_games, pick_games_picks WHERE pick_games.pick_game_id=pick_games_picks.game_id AND pick_games.pick_category='{$category}' AND pick_games.pick_sport_id={$sport_id} AND pick_games_picks.pick_status=0 ORDER BY pick_games.pick_identifier";
	
	
		$returnVals = array();
		
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_identifier"] = $row->pick_identifier;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_id"] = $row->pick_id;
			 $returnVals[$row->pick_identifier][$row->pick_username]["game_id"] = $row->pick_game_id;
			 $returnVals[$row->pick_identifier][$row->pick_username]["user_id"] = $row->pick_user_id;
			 $returnVals[$row->pick_identifier][$row->pick_username]["user_name"] = $row->pick_username;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_date"] = $row->pick_date;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_choice"] = $row->pick_choice;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_wager"] = $row->pick_wager;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_correct"] = $row->pick_correct;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_status"] = $row->pick_status;
			 $returnVals[$row->pick_identifier][$row->pick_username]["pick_wager_result"] = $row->pick_wager;
	
		}
		
		return $returnVals;
	
		
	}
	
	function pickgame_update_pick_stats($user) {
	
		//get database
		$dbr =& wfGetDB( DB_MASTER );
		
		$exists = $this->pickgame_update_exists($user["user_name"], $user["category"], $user["sport_id"]);
		
		$incremental = $user["points"] - $exists["points"];
		$stats = new UserStatsTrack(1, $user["user_id"], $user["user_name"]);
		//$stats_data = $stats->getUserStats();
				
		//check if pick is new or changing
		
		if (!$exists["found"]) {
			//write new data to pick_games_pick
			$dbr->insert( '`pick_games_updates`',
			array(
				'user_id' => $user["user_id"],
				'user_name' => $user["user_name"],
				'pick_game_category' => $user["category"],
				'pick_game_update_time' => date("Y-m-d H:i:s"),
				'sport_id' => $user["sport_id"],
				'pick_game_points' => $user["points"],
				'picks_correct' => $user["correct"],
				'picks_incorrect' => $user["incorrect"],
				'main_points_updated' => 0
				), __METHOD__
			);
		}
		else {
			$dbr->update( '`pick_games_updates`',
			array(
				'pick_game_update_time' => date("Y-m-d H:i:s"),
				'sport_id' => $user["sport_id"],
				'pick_game_points' => $user["points"],
				'picks_correct' => $user["correct"],
				'picks_incorrect' => $user["incorrect"],
				),
			array(
				'user_name' => $user["user_name"],
				'sport_id' => $user["sport_id"],
				'pick_game_category' => $user["category"]
			)
				, __METHOD__
			);
		}
		
		if ($incremental > 0) {		
			$stats->incStatField("currency", $incremental);			
		}
		elseif ($incremental < 0) {
			$stats->decStatField("currency", (-1*$incremental));
		}
		else {}
	}
	
	function pickgame_update_exists($user_name, $category, $sport_id) {

		$return_vals = array();
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( 'pick_games_updates', array( 'update_id', 'pick_game_points' ), array( 'user_name' => $user_name, 'sport_id' => $sport_id, 'pick_game_category' => $category ),"" );
		if ( $s !== false ) {
			$return_vals["found"] = true;
			$return_vals["points"] = $s->pick_game_points;
		} else {
			$return_vals["found"] = false;
			$return_vals["points"] = 0;
		}
		
		return $return_vals;
	}

}



SpecialPage::addPage( new PickGameWinners );



}

?>
