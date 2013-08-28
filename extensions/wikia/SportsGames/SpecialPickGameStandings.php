<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameStandings';
$wgExtensionFunctions[] = 'wfPickGameReadLang';

function wfSpecialPickGameStandings(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameStandings extends SpecialPage {

	
	function PickGameStandings(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameStandings");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsGames/SpecialPickGameStandings.css?{$wgStyleVersion}\"/>\n");
		
		$user_name = $wgUser->getName();
		$user_id = User::idFromName($user_name);
		
		$nav = "";
		$category = 0;

		if(!$value) {
		
			$standings = $this->pick_game_standings(0,0,0);
			$title = wfMsgForContent('pickgame_leaderboard_overall_title');
		}
		else {
			$sport_specifics = PickGame::pickgame_getSportSpecifics($value);
			if ($wgRequest->getVal($sport_specifics["param"]) != "") {
				$category = $wgRequest->getVal($sport_specifics["param"]);
				$current_category = $category;
			}
			else {
				$category = 0;
							
				$curDateUnix = time();
				$curGetDay = $curDateUnix;
				$curDate = date("Ymd", $curGetDay);
				$current_category = PickGame::get_category_from_date($curDate, $sport_specifics);		
			}
				$current_category = PickGame::check_first_game($current_category, $sport_specifics);
			
			$nav = PickGame::get_nav_display($current_category, $sport_specifics);
			
			
			if ($category === 0) {
				$nav_startpos = strpos($nav, "<div class=\"pick-navigation-day-title\">") + strlen("<div class='pick-navigation-day-title'>");
				$nav_start = substr($nav, 0, $nav_startpos);
				$nav_end = substr($nav, strpos($nav, "</div>", $nav_startpos));
				$nav = "{$nav_start}" . wfMsgForContent('pickgame_leaderboard_specific_view') . " {$sport_specifics["param"]}{$nav_end}";
				$title_end = wfMsgForContent('pickgame_leaderboard_title_end');
			}
			else {
				$nav_startpos = strpos($nav, "<div class=\"pick-navigation-day-title\">") + strlen("<div class='pick-navigation-day-title'>");
				$nav_start = substr($nav, 0, $nav_startpos);
				$nav_end = substr($nav, strpos($nav, " ", $nav_startpos));
				$nav = "{$nav_start}" . wfMsgForContent('pickgame_leaderboard_standings') . "{$nav_end}";
				$title_end = "";
			}

			
			$standings = $this->pick_game_standings($value,$category,0);
			$title = "{$sport_specifics["page_title"]} " . wfMsgForContent('pickgame_leaderboard_leaderboard') . "{$title_end}";
		}
		
		
			$output .= "<div class=\"standings-container\">";
			$output .= "<div class=\"pickgame-standings\">";
			$output .= $nav;
			
			if (sizeof($standings) > 0) {
			
				$output .= "<div class=\"pickgame-standings-heading\">";
				$output .= "<div class=\"pickgame-standings-rank\">&nbsp;</div>";
				$output .= "<div class=\"pickgame-standings-username\">&nbsp;</div>";
				$output .= "<div class=\"pickgame-standings-correct\">" . wfMsgForContent('pickgame_leaderboard_won') . "</div>";
				$output .= "<div class=\"pickgame-standings-incorrect\">" . wfMsgForContent('pickgame_leaderboard_lost') . "</div>";
				$output .= "<div class=\"pickgame-standings-points\">" . wfMsgForContent('pickgame_leaderboard_points') . "</div>";
				$output .= "<div class=\"pickgame-standings-winpct\">" . wfMsgForContent('pickgame_leaderboard_pct') . "</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
			}
			else {
				$output .= "<div class=\"no-standings-text\">";
				if(!$value) {
					$output .= wfMsgForContent('pickgame_leaderboard_no_results', 'Pick Game');
				}
				else {
					if ($category === 0) {
						$results_end = "";
					}
					else {
						$results_end = wfMsgForContent('pickgame_leaderboard_no_results_period');
					}
					$output .= wfMsgForContent('pickgame_leaderboard_no_results', $sport_specifics["page_title"] . $results_end);
				}
				$output .= "</div>";
			}
			
			for ($i=0; $i<sizeof($standings); $i++) {
				
				if ($i % 2 == 0) {
					$bg_style = "-alternate";
				}
				else {
					$bg_style = "";
				}
				
				//$user_name = $row->stats_user_name;
				$user_title = Title::makeTitle( NS_USER  , $standings[$i]["user_name"]  );
				$avatar = new wAvatar($standings[$i]["user_id"],"m");
				$avatarImage = $avatar->getAvatarImage();
				
				
				$output .= "<div class=\"pickgame-standings-user{$bg_style}\">";
				$output .= "<div class=\"pickgame-standings-rank\">".($i+1).".</div>";
				$output .= "<div class=\"pickgame-standings-username\"><img src='images/avatars/" . $avatarImage . "' alt='' border=''> <a href='" . $user_title->getFullURL() . "' >" . $standings[$i]["user_name"] . "</a></div>";
				$output .= "<div class=\"pickgame-standings-correct\">{$standings[$i]["won"]}</div>";
				$output .= "<div class=\"pickgame-standings-incorrect\">{$standings[$i]["lost"]}</div>";
				$output .= "<div class=\"pickgame-standings-points\">{$standings[$i]["points"]}</div>";
				$output .= "<div class=\"pickgame-standings-winpct\">{$standings[$i]["win_pct"]}</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
			}
			
			$output .= "</div>";
			
			//$output .= "<div class=\"standings-menu\">
			//<h2>Standings</h2>";
			//$output .= $this->pick_menu($value);
			$output .= PickGame::pickgame_display_menu($value, $user_id, $user_name, false, true);


			//$output .= "</div>";

		
		
		
		
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);

		
	}
	
	
	function pick_game_standings($sport_id, $category, $limit) {
		
		if(!$limit) {
			$limit = 36;
		}
		
		$dbr =& wfGetDB( DB_MASTER );
		
		if($category > 0) {
			$category = " AND pick_game_category={$category} ";
		}
		else {
			$category = "";
		}
		
		if (!$sport_id) {
			
			$sql = "SELECT user_name, user_id, SUM(picks_correct) as won, SUM(picks_incorrect) as lost, SUM(pick_game_points) as points, (SUM(picks_correct)/(SUM(picks_correct)+SUM(picks_incorrect))) as win_pct FROM pick_games_updates WHERE pick_games_updates.user_id <> 0 {$category} GROUP BY user_name, user_id ORDER BY points DESC, win_pct DESC LIMIT 0, {$limit}";
		}
		else {
			$sql = "SELECT user_name, user_id, SUM(picks_correct) as won, SUM(picks_incorrect) as lost, SUM(pick_game_points) as points, (SUM(picks_correct)/(SUM(picks_correct)+SUM(picks_incorrect))) as win_pct FROM pick_games_updates WHERE pick_games_updates.user_id <> 0  {$category} AND pick_games_updates.sport_id={$sport_id} GROUP BY user_name, user_id ORDER BY points DESC, win_pct DESC LIMIT 0, {$limit}";
			
		}
		$returnVals = array();
		
		$i = 0;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals[$i]["user_name"] = $row->user_name;
			 $returnVals[$i]["user_id"] = $row->user_id;
			 $returnVals[$i]["won"] = $row->won;
			 $returnVals[$i]["lost"] = $row->lost;
			 $returnVals[$i]["points"] = $row->points;
			 $returnVals[$i]["win_pct"] = $row->win_pct;
			 $i++;
		}
		
		return $returnVals;
	
	
		
	}
	/*
	function pick_menu($sport_number) {
		
		$sports_list = PickGame::sports_list();
		
		if (!$sport_number) {
				$output .= "<p><b>Overall</b></p>";
		} 
		else {
				$output .= "<p><a href=\"index.php?title=Special:PickGameStandings\">Overall</a></p>";
		}
		
		foreach ($sports_list as $sport=>$sport_name) {
		
			if ($sport_number == $sport) {
				$output .= "<p><b>{$sport_name}</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:PickGameStandings/{$sport}\">{$sport_name}</a></p>";
			}
		
		}
		
		return $output;
		
	}
	*/
	
	
}


SpecialPage::addPage( new PickGameStandings );



}

?>
