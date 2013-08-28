<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameNFLUpdate';

function wfSpecialPickGameNFLUpdate(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameNFLUpdate extends SpecialPage {

	
	function PickGameNFLUpdate(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameNFLUpdate");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;

		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/SportsGames/PickGameUpdate.js?{$wgStyleVersion}\"></script>\n");
		
		$output = "";
		
		if ($value) {
			$sport_id=$value;
			$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);
			
			if ($wgRequest->getVal($sport_specifics["param"]) == "") {
			
				$curDateUnix = time();
				
				//$curGetDay = $curDateUnix + $oneDay;
				$curGetDay = $curDateUnix;
				$curDate = date("Ymd", $curGetDay);
				$current_category = PickGame::get_category_from_date($curDate, $sport_specifics);		
				
			}
			else {
				//$curDate = $wgRequest->getVal($sport_specific["param"]);
				$current_category = $wgRequest->getVal($sport_specifics["param"]);
				$curDate = PickGame::get_date_from_category($current_category, $sport_specifics);
			}
			
			$current_category = PickGame::check_first_game($current_category, $sport_specifics);

			
			$games = $this->pickgame_getGamesForUpdating($current_category, $sport_id);
			
			for ($i=0; $i<sizeof($games); $i++) {
				//$output .= "-{$games[$i]["game_status"]}-";
				$output .= "<div id=\"pickgame-update-game-{$games[$i]["game_id"]}\">";

				if ($games[$i]["game_status"] != "") {
					$output .= $this->display_entered_game($games[$i]);
				}
				else {
					$output .= $this->display_unentered_game($games[$i], $value);
				}
				$output .= "</div>";
			}
			
		}

		$wgOut->addHTML($output);

		
	}
	
	function display_entered_game($game) {
		//$output = "<div id=\"pickgame-update-game-{$game["game_id"]}\">";
		$output = "<div id=\"pickgame-update-entered-game-{$game["game_id"]}\">";
		$output .= "{$game["vis"]} {$game["vis_score"]} @ {$game["home"]} {$game["home_score"]} - {$game["game_status_desc"]}";
		$output .= "<input type=\"button\" value=\"edit\" onclick=\"editGameUpdate({$game["game_id"]}, {$game["home_score"]}, {$game["vis_score"]}, {$game["game_status"]}, '{$game["game_status_desc"]}',{$game["category"]}, 'pickgame-update-game-{$game["game_id"]}');\" />";
		$output .= "<input type=\"button\" value=\"delete\" onclick=\"removeGameUpdate({$game["game_id"]}, {$game["category"]}, 'pickgame-update-game-{$game["game_id"]}');\" />";
		$output .= "</div>";
		//$output .= "</div>";
		return $output;
	}
	
	function display_unentered_game($game, $sport_id) {
		//$output = "<div id=\"pickgame-update-game-{$game["game_id"]}\">";
		$output = "<div id=\"pickgame-update-unentered-game-{$game["game_id"]}\">";		
		$output .= "{$game["vis"]} <input type=\"text\" id=\"vis-score-{$game["game_id"]}\" value=\"{$game["vis_score"]}\" size=\"3\"> @ {$game["home"]} <input type=\"text\" id=\"home-score-{$game["game_id"]}\" value=\"{$game["home_score"]}\" size=\"3\">";
		$output .= " <select id=\"game-status-{$game["game_id"]}\">";
		$output .= PickGameNFLUpdate::get_status_options($sport_id);
		$output .= "</select>";
		$output .= "<input type=\"button\" value=\"update\" onclick=\"makeGameUpdate({$game["game_id"]}, 'home-score-{$game["game_id"]}', 'vis-score-{$game["game_id"]}', 'game-status-{$game["game_id"]}', {$game["category"]}, 'pickgame-update-game-{$game["game_id"]}');\" />";
		$output .= "</div>";
		//$output .= "</div>";
		return $output;
	}
	
	function pickgame_getGamesForUpdating($category, $sport_id) {

		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT pick_games.pick_vis_abbr, pick_games.pick_home_abbr, 
			pick_games.pick_game_visitor, pick_games.pick_game_home,
			pick_games.pick_game_id, UNIX_TIMESTAMP(pick_games.pick_game_date) as pick_game_timestamp,
			pick_games.pick_category, pick_games_results.home_score, pick_games_results.vis_score, pick_games_results.game_winner, 
			pick_games_results.game_winner_abbr, pick_games_results.game_status, pick_games_results.game_status_desc
		FROM pick_games LEFT OUTER JOIN pick_games_results 
		ON pick_games.pick_game_id = pick_games_results.pick_game_id
		WHERE pick_games.pick_category = {$category} AND pick_games.pick_sport_id = {$sport_id}
		ORDER BY pick_games.pick_game_date";
		
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			$returnVals[$i]["home"] = $row->pick_game_home;
			$returnVals[$i]["vis"] = $row->pick_game_visitor;
			$returnVals[$i]["home_abbr"] = $row->pick_home_abbr;
			$returnVals[$i]["vis_abbr"] = $row->pick_vis_abbr;
			$returnVals[$i]["game_id"] = $row->pick_game_id;
			$returnVals[$i]["timestamp"] = $row->pick_game_timestamp;
			$returnVals[$i]["category"] = $row->pick_category;
			$returnVals[$i]["home_score"] = $row->home_score;
			$returnVals[$i]["vis_score"] = $row->vis_score;
			$returnVals[$i]["game_winner"] = $row->game_winner;
			$returnVals[$i]["game_winner_abbr"] = $row->game_winner_abbr;
			$returnVals[$i]["game_status"] = $row->game_status;
			$returnVals[$i]["game_status_desc"] = $row->game_status_desc;
			
			$i++;
		}
		
		return $returnVals;
	
	}
	
	function get_status_options($sport_id) {
		$options = "<option value=\"1\">Final</option>";
		$options .= "<option value=\"0\">Postponed</option>";
		switch ($sport_id) {
			case 1:
			for($i=10; $i<21; $i++) {
				$options .= "<option value=\"1\">Final {$i}th</option>";
			}
			$options .= "<option value=\"1\">Final 21st</option>";
			$options .= "<option value=\"1\">Final 22nd</option>";
			$options .= "<option value=\"1\">Final 23rd</option>";
			for($i=24; $i<31; $i++) {
				$options .= "<option value=\"1\">Final {$i}th</option>";
			}
				break;
			default:
				$options .= "<option value=\"1\">Final OT</option>";
				break;
				
		}
		
		return $options;
	}
	
}


SpecialPage::addPage( new PickGameNFLUpdate );



}

?>
