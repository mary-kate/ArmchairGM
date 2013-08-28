<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameGamesUpdate';

function wfSpecialPickGameGamesUpdate(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameGamesUpdate extends SpecialPage {

	
	function PickGameGamesUpdate(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameGamesUpdate");
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

			
			$games = $this->pickgame_getEnteredGames($current_category, $sport_id);
			
			for ($i=0; $i<sizeof($games); $i++) {
				//$output .= "-{$games[$i]["game_status"]}-";
				$output .= "<div id=\"pickgame-update-game-{$games[$i]["game_id"]}\">";

				/*
				if ($games[$i]["game_status"] != "") {
					$output .= $this->display_entered_game($games[$i]);
				}
				else {
					$output .= $this->display_unentered_game($games[$i], $value);
				}
				*/
				$output .= $this->display_entered_game_entry($games[$i]);
				$output .= "</div>";
			}
			$new_empty = array("game_id"=>0);
			$output .= $this->display_empty_game_entry($new_empty, 0, $sport_id, 0);

			
		}

		$wgOut->addHTML($output);

		
	}
	
	function display_entered_game_entry($game) {
		//$output = "<div id=\"pickgame-update-game-{$game["game_id"]}\">";
		$output = "<div id=\"pickgame-update-entered-game-{$game["game_id"]}\">";
		$output .= "{$game["vis_abbr"]} {$game["game_visitor"]} {$game["visitor_addl"]} @ {$game["home_abbr"]} {$game["game_home"]} {$game["home_addl"]} - {$game["game_date"]} - {$game["category"]} - {$game["identifier"]}";
		$output .= "<input type=\"button\" value=\"edit\" onclick=\"editGameEntry({$game["game_id"]}, {$game["category"]}, 'pickgame-update-entered-game-{$game["game_id"]}');\" />";
		$output .= "<input type=\"button\" value=\"delete\" onclick=\"removeGameEntry({$game["game_id"]}, 'pickgame-update-entered-game-{$game["game_id"]}');\" />";
		$output .= "</div>";
		//$output .= "</div>";
		return $output;
	}
	
	function display_unentered_game_entry($game, $sport_id) {

		$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);

		$output = "<div id=\"pickgame-update-unentered-game-{$game["game_id"]}\">";
		$output .= "V_abbr:<input type=\"text\" id=\"vis-abbr-{$game["game_id"]}\" value=\"{$game["vis_abbr"]}\" size=\"5\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> Visitor:<input type=\"text\" id=\"visitor-{$game["game_id"]}\" value=\"{$game["game_visitor"]}\"> Visitor addl:<input type=\"text\" id=\"visitor-addl-{$game["game_id"]}\" value=\"{$game["visitor_addl"]}\"> @ ";
		$output .= "H_abbr:<input type=\"text\" id=\"home-abbr-{$game["game_id"]}\" value=\"{$game["home_abbr"]}\" size=\"5\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> Home:<input type=\"text\" id=\"home-{$game["game_id"]}\" value=\"{$game["game_home"]}\"> Home addl:<input type=\"text\" id=\"home-addl-{$game["game_id"]}\" value=\"{$game["home_addl"]}\"> @ ";
		$output .= "- GameDate:<input type=\"text\" id=\"game-date-{$game["game_id"]}\" value=\"{$game["game_date"]}\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> - Category:<input type=\"text\" id=\"category-{$game["game_id"]}\" value=\"{$game["category"]}\">  - Identifier:<input type=\"text\" id=\"identifier-{$game["game_id"]}\" value=\"{$game["identifier"]}\"> sport id:{$sport_id}";
		$output .= "<input type=\"button\" value=\"update\" onclick=\"updateGameEntry({$game["game_id"]}, {$sport_id}, 0, 'pickgame-update-unentered-game-{$game["game_id"]}');\" />";
		$output .= "</div>";
		
		
		return $output;
	}


	function display_empty_game_entry($game, $empty_count, $sport_id, $passed_category) {
		global $wgRequest;
		$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);
		
		if ($wgRequest->getVal($sport_specifics["param"]) == "") {
			
			if($passed_category === 0) {
				$curDateUnix = time();
				
				//$curGetDay = $curDateUnix + $oneDay;
				$curGetDay = $curDateUnix;
				$curDate = date("Ymd", $curGetDay);
				$current_category = PickGame::get_category_from_date($curDate, $sport_specifics);
			}
			else {
				$current_category = $passed_category;
				
			}
							
			$curDate = PickGame::get_date_from_category($current_category, $sport_specifics);

			/*
			$year = substr($curDate, 0, 4);
			$month = substr($curDate, 4, 2);
			$day = substr($curDate, 6, 2);
			$curGetDay = mktime(19,0,0,$month,$day,$year);
			$curDateDisplay = date("Y-m-d H:i:s", $curGetDay);
			*/

			
		}
		else {
			//$curDate = $wgRequest->getVal($sport_specific["param"]);
			$current_category = $wgRequest->getVal($sport_specifics["param"]);
			$curDate = PickGame::get_date_from_category($current_category, $sport_specifics);
			/*
			$year = substr($curDate, 0, 4);
			$month = substr($curDate, 4, 2);
			$day = substr($curDate, 6, 2);
			$curGetDay = mktime(19,0,0,$month,$day,$year);
			$curDateDisplay = date("Y-m-d H:i:s", $curGetDay);
			*/

		}
			
			$year = substr($curDate, 0, 4);
			$month = substr($curDate, 4, 2);
			$day = substr($curDate, 6, 2);
			$curGetDay = mktime(19,0,0,$month,$day,$year);
			$curDateDisplay = date("Y-m-d H:i:s", $curGetDay);

			
			$current_category = PickGame::check_first_game($current_category, $sport_specifics);

		//$output = "<div id=\"pickgame-update-unentered-game-{$game["game_id"]}\"></div>";
		$output = "<div id=\"pickgame-update-empty-game-{$empty_count}\">";
		//$output .= "<input type=\"hidden\" id=\"identifier-date-{$game["game_id"]}\" value=\"{$curDate}\" />";
		$output .= "V_abbr:<input type=\"text\" id=\"vis-abbr-{$game["game_id"]}\" value=\"\" size=\"5\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> Visitor:<input type=\"text\" id=\"visitor-{$game["game_id"]}\" value=\"\"> Visitor addl:<input type=\"text\" id=\"visitor-addl-{$game["game_id"]}\" value=\"\"> @ ";
		$output .= "H_abbr:<input type=\"text\" id=\"home-abbr-{$game["game_id"]}\" value=\"\" size=\"5\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> Home:<input type=\"text\" id=\"home-{$game["game_id"]}\" value=\"\"> Home addl:<input type=\"text\" id=\"home-addl-{$game["game_id"]}\" value=\"\"> @ ";
		$output .= "- GameDate:<input type=\"text\" id=\"game-date-{$game["game_id"]}\" value=\"{$curDateDisplay}\" onchange=\"generateIdentifier({$game["game_id"]}, '{$sport_specifics["sport"]}');\"> - Category:<input type=\"text\" id=\"category-{$game["game_id"]}\" value=\"{$current_category}\">  - Identifier:<input type=\"text\" id=\"identifier-{$game["game_id"]}\" value=\"\"> sport id:{$sport_id}";
		$output .= "<input type=\"button\" value=\"insert\" onclick=\"updateGameEntry({$game["game_id"]}, {$sport_id}, {$empty_count}, 'pickgame-update-empty-game-{$empty_count}');\" />";
		$output .= "</div>";
		
		
		return $output;
	}
	
	
	function pickgame_getEnteredGames($category, $sport_id) {

		$dbr =& wfGetDB( DB_MASTER );
		/*
		$sql = "SELECT pick_games.pick_vis_abbr, pick_games.pick_home_abbr, 
			pick_games.pick_game_visitor, pick_games.pick_game_home,
			pick_games.pick_game_id, UNIX_TIMESTAMP(pick_games.pick_game_date) as pick_game_timestamp,
			pick_games.pick_category, pick_games_results.home_score, pick_games_results.vis_score, pick_games_results.game_winner, 
			pick_games_results.game_winner_abbr, pick_games_results.game_status, pick_games_results.game_status_desc
		FROM pick_games LEFT OUTER JOIN pick_games_results 
		ON pick_games.pick_game_id = pick_games_results.pick_game_id
		WHERE pick_games.pick_category = {$category} AND pick_games.pick_sport_id = {$sport_id}
		ORDER BY pick_games.pick_game_date";
		*/
		
		$sql = "SELECT * FROM pick_games
			WHERE pick_games.pick_category = {$category} AND pick_games.pick_sport_id = {$sport_id}
			ORDER BY pick_games.pick_game_date";
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			$returnVals[$i]["game_id"] = $row->pick_game_id;
			$returnVals[$i]["sport_id"] = $row->pick_sport_id;
			$returnVals[$i]["game_date"] = $row->pick_game_date;
			$returnVals[$i]["game_visitor"] = $row->pick_game_visitor;
			$returnVals[$i]["game_home"] = $row->pick_game_home;
			$returnVals[$i]["visitor_addl"] = $row->pick_visitor_addl;
			$returnVals[$i]["home_addl"] = $row->pick_home_addl;
			$returnVals[$i]["identifier"] = $row->pick_identifier;
			$returnVals[$i]["home_abbr"] = $row->pick_home_abbr;
			$returnVals[$i]["vis_abbr"] = $row->pick_vis_abbr;
			$returnVals[$i]["category"] = $row->pick_category;
			
			$i++;
		}
		
		return $returnVals;
	
	}

	/*
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
	*/
	
}


SpecialPage::addPage( new PickGameGamesUpdate );



}

?>
