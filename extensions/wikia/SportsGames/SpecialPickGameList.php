<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameList';
$wgExtensionFunctions[] = 'wfPickGameReadLang';

function wfSpecialPickGameList(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameList extends SpecialPage {

	
	function PickGameList(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameList");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsGames/SpecialPickGameList.css?{$wgStyleVersion}\"/>\n");
		
		// define user_name and user_id
		$user_name = $wgRequest->getVal('user');
		
		//No UserName Then Assume Current User			
		if(!$user_name)$user_name = $wgUser->getName();
		$user_id = User::idFromName($user_name);
		
		$user_stats = $this->pick_game_userstats($user_name);
		
		$output .= "<div class=\"pickgame-results-container\">";
		$output .= "<div class=\"pickgame-results-data\">";
		
		$output .= "<div class=\"pickgame-results-navigation\">
			<a href=\"" . Title::makeTitle( NS_USER, $user_name)->escapeFullUrl() . "\">" . wfMsgForContent('pickgame_list_back_to_profile', $user_name) ."</a>
		</div>";
		
		$output .= "<div class=\"pickgame-results-wonlost\">
			<h2>" . wfMsgForContent('pickgame_list_overall_stats') ."</h2>
			<p><b>" . wfMsgForContent('pickgame_list_correct') ."</b> {$user_stats["won"]}</p> 
			<p><b>" . wfMsgForContent('pickgame_list_incorrect') ."</b> {$user_stats["lost"]}</p>
			<p><span class=\"profile-on\"><b>" . wfMsgForContent('pickgame_list_total_points') ."</b> = {$user_stats["points"]}</span></p>
		</div>";
		
		$output .= "<h2>" . wfMsgForContent('pickgame_list_last_five_days') ."</h2>";
		
		$num_days = 5;
		$sports_list = PickGame::sports_list();
		//for ($sport_count = 1; $sport_count<5; $sport_count++) {
			
			foreach($sports_list as $sport_count=>$sport_name) {
			
			$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_count);
			
			$days_results = $this->pickgame_getDaysResultsBySport($user_name, $sport_count, $num_days, "", $sport_specifics);
			
			for ($result_count = 0; $result_count < sizeof($days_results); $result_count++) {
				if ($result_count == 0) {			
					$output .= "<h3><a href=\"" . PickGame::pick_game_link("/{$sport_count}") . "\">{$sport_specifics["page_title"]}</a></h3>";
				}
				
				$output .= "<div class=\"pickgame-results-row\">
					<p><a href=\"" . PickGame::pick_game_link("/{$sport_count}", "{$sport_specifics["param"]}={$days_results[$result_count]["category"]}") ."\">". PickGame::get_display_date_from_category($days_results[$result_count]["category"], $sport_specifics) . "</a></p>
					<p><b>" . wfMsgForContent('pickgame_list_correct') ."</b> {$days_results[$result_count]["won"]}</p>
					<p><b>" . wfMsgForContent('pickgame_list_incorrect') ."</b> {$days_results[$result_count]["lost"]}</p>
					<p><b>" . wfMsgForContent('pickgame_list_total_points') ."</b> {$days_results[$result_count]["points"]}</p>
					<div class=\"cleared\"></div>
				</div>";
			}
		}
		
		$output .= "<h2>" . wfMsgForContent('pickgame_list_upcoming_picks') ."</h2>";
		
		//for ($sport_count = 1; $sport_count<5; $sport_count++) {
						
			foreach($sports_list as $sport_count=>$sport_name) {


			$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_count);

			$upcoming_picks = $this->pickgame_getUpcomingPicks($user_name, $sport_count, $num_days, "", $sport_specifics);

			
			for ($result_count = 0; $result_count < sizeof($upcoming_picks); $result_count++) {
				if ($result_count == 0) {			
					$output .= "<h3><a href=\"" . PickGame::pick_game_link("/{$sport_count}") . "\">{$sport_specifics["page_title"]}</a></h3>";
				}
				
				$output .= "<div class=\"pickgame-results-row\">
					<p><a href=\"" . PickGame::pick_game_link("/{$sport_count}", "{$sport_specifics["param"]}={$upcoming_picks[$result_count]["category"]}") . "\">". date("D, M j - g:i A", $upcoming_picks[$result_count]["game_date"]) . "</a></p>
					<p>{$upcoming_picks[$result_count]["vis"]} @ {$upcoming_picks[$result_count]["home"]}</p>
					<p><b>" . wfMsgForContent('pickgame_list_your_choice') ."</b> {$upcoming_picks[$result_count]["choice"]}</p>
					<p><b>" . wfMsgForContent('pickgame_list_wager') ."</b> {$upcoming_picks[$result_count]["wager"]}</p>
					<div class=\"cleared\"></div>
				</div>";
			}
		}
		$output .= "</div>";
		
		$output .= PickGame::pickgame_display_menu($value, $user_id, $user_name, false, false);
		
		$output .= "</div>
		<div class=\"cleared\"></div>";

		
		$title = wfMsgForContent('pickgame_list_results_for', $user_stats["user_name"]);
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);

		
	}
	
	
	function pick_game_userstats($user_name) {
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT user_name,  SUM(picks_correct) as won, SUM(picks_incorrect) as lost, SUM(pick_game_points) as points FROM pick_games_updates WHERE user_name='{$user_name}' GROUP BY user_name";
		
		$returnVals = array();
		
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals["user_name"] = $row->user_name;
			 $returnVals["won"] = $row->won;
			 $returnVals["lost"] = $row->lost;
			 $returnVals["points"] = $row->points;
	
		}
		
		return $returnVals;
	
	
		
	}
	
	function pickgame_getDaysResultsBySport($user_name, $sport_id, $num_days, $current_day, $sport_specifics) {

		//$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);

		if (!$num_days || $num_days == "") {
			$num_days = 5;
		}
		
		/*
		$curDateUnix = time();
		$temp_current_day = date("Ymd", $curDateUnix);

		
		if(!$current_day || $current_day == "") {
			$current_day = $temp_current_day - (24*60*60);			
		}
		else {
			if ($current_day == $temp_current_day) {
				$current_day = $temp_current_day - (24*60*60);
			}
		}
		
		
		$current_category = PickGame::get_category_from_date($current_day, $sport_specifics);
		
		$category_list = strval($current_day);
		
		for ($i=0; $i<$num_days; $i++) {
			
			$category_list .= "," . strval($current_day - ($i * sport_specifics["iterator"]));
			
		}
		*/


		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT user_name, pick_game_category, picks_incorrect, picks_correct, pick_game_points FROM pick_games_updates WHERE user_name='{$user_name}' AND sport_id={$sport_id} ORDER BY pick_game_category DESC LIMIT 0, {$num_days}";
		
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals[$i]["user_name"] = $row->user_name;
			 $returnVals[$i]["category"] = $row->pick_game_category;
			 $returnVals[$i]["won"] = $row->picks_correct;
			 $returnVals[$i]["lost"] = $row->picks_incorrect;
			 $returnVals[$i]["points"] = $row->pick_game_points;
			 
			 $i++;
	
		}
		
		return $returnVals;

	
	}
	
	
	function pickgame_getUpcomingPicks($user_name, $sport_id, $num_picks, $current_day, $sport_specifics) {

		//$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);

		if (!$num_days || $num_days == "") {
			$num_days = 5;
		}
		
		
		$curDateUnix = time();
		$temp_current_day = date("Ymd", $curDateUnix);

		
		if($current_day == "") {
			$current_day = $temp_current_day;			
		}
		
		$current_category = PickGame::get_category_from_date($current_day, $sport_specifics);
		
		/*
		$category_list = strval($current_day);
		
		for ($i=0; $i<$num_days; $i++) {
			
			$category_list .= "," . strval($current_day - ($i * sport_specifics["iterator"]));
			
		}
		*/
		


		$dbr =& wfGetDB( DB_MASTER );
		
		//$sql = "SELECT pick_games_picks.user_name, pick_games.pick_category, pick_games.pick_home_abbr, pick_games.pick_vis_abbr, pick_games_picks.pick_choice, pick_games_picks.pick_wager FROM pick_games, pick_games_picks WHERE pick_games_picks.game_id=pick_games.pick_game_id AND pick_games_picks.user_name='{$user_name}' AND sport_id={$sport_id} ORDER BY pick_game_category DESC LIMIT 0, {$num_days}";
		$sql = "SELECT pick_games_picks.pick_username, pick_games.pick_category, UNIX_TIMESTAMP(pick_games.pick_game_date) as pick_game_date, pick_games.pick_home_abbr, pick_games.pick_vis_abbr, pick_games_picks.pick_choice, pick_games_picks.pick_wager FROM pick_games, pick_games_picks WHERE pick_games_picks.game_id=pick_games.pick_game_id AND pick_games_picks.pick_username='{$user_name}' AND pick_games.pick_sport_id={$sport_id} AND pick_games.pick_category >= {$current_category} ORDER BY pick_games.pick_game_date ASC LIMIT 0, {$num_picks}";
		
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals[$i]["user_name"] = $row->user_name;
			 $returnVals[$i]["category"] = $row->pick_category;
			 $returnVals[$i]["home"] = $row->pick_home_abbr;
			 $returnVals[$i]["vis"] = $row->pick_vis_abbr;
			 $returnVals[$i]["choice"] = $row->pick_choice;
			 $returnVals[$i]["wager"] = $row->pick_wager;
			 $returnVals[$i]["game_date"] = $row->pick_game_date;
			 
			 $i++;
	
		}
		
		return $returnVals;

	
	}

	
	
}


SpecialPage::addPage( new PickGameList );



}

?>
