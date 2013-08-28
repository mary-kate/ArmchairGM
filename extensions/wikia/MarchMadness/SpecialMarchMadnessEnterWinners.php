<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessEnterWinners';

function wfSpecialMarchMadnessEnterWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessEnterWinners extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessEnterWinners");

	}
	
	function march_madness_enter_winners_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessEnterWinners" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessEnterWinners" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$user_groups = $wgUser->getGroups();
		$user_groups = array_flip($user_groups);
		
		if (!isset($user_groups['staff'])) {
			$page_title = "Permission Denied";
			$output = "You must be staff to view this page";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
		}
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");

		
		if (!$value) {

			$tournaments = MarchMadness::get_tournament_list_from_db();
			
			$output .= "<h2>Tournaments</h2>";
				foreach ($tournaments as $tournament_id=>$tournament_info) {
					$tourney_link = $this->march_madness_enter_winners_link($tournament_id);
					$output .= "<a href=\"{$tourney_link}\">{$tournament_info["tournament_name"]}</a><br/>";
				}
			
			$page_title = "Select a tournament to enter winners for";
			
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
						
		}
		else {
			
			$info = MarchMadness::get_tournament_info_from_db($value);
			if (!sizeof($info)) {
				$wgOut->setPageTitle("This is not a valid tournament id");
				$wgOut->addHTML("This is not a valid tournament id");
				return "";
			}
			
			
			if($wgRequest->getVal("round") == "") {
				$round = 1;
			}
			else {
				$round = $wgRequest->getVal("round");
			}
			
			
			$page_title = "Entering winners for {$info["tournament_name"]}";
			
			$tournament_id=$info["tournament_id"];

			$brackets = MarchMadness::get_brackets_from_db($tournament_id);
			
			$brackets_keys = array_keys($brackets);
						
			$teams = MarchMadness::get_teams_from_db($tournament_id);
			$winners = MarchMadness::get_winners_from_db($tournament_id);
			
			$games_order = explode(",", $info["games_order"]);
			
			$finals_bracket_id = $brackets[$info["finals_bracket"]];
			
						
		
			$output = "";
			
			$output .= "<div class=\"madness-nav-link\">";
			for ($i=1; $i<7; $i++) {
				if ($round==$i) {
					$output .= " Round {$i} ";
				}
				else {
					$output .= " <a href=\"" . MarchMadnessEnterWinners::march_madness_enter_winners_link($value, "round={$i}") . "\">Round {$i}</a> ";
				}
				if ($i<6) {
					$output .= " - ";
				}
				
			}
			
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;After entering winners - click here: <a href=\"" . MarchMadnessUpdatePicks::march_madness_update_picks_link($value) . "\">Update User Picks</a>";
			$tournament_info = MarchMadness::get_tournament_info_from_db($tournament_id);
			$output .= "<br/>Now: " . date("Y-m-d H:i:s") . " (" . time() . ") | Start Date: <input type=\"text\" id=\"tournament-start-date\" value=\"" . date("Y-m-d H:i:s", $tournament_info["start_date"]) . "\" /> <input type=\"button\" class=\"site-button\" value=\"set start time\" onclick=\"set_start_time($tournament_id)\" /> (" . $tournament_info["start_date"] . ")<br/>";
			$output .= "<div id=\"madness-enterwinners-messages\"</div>";
			$output .= "</div>";
			
			
						
			foreach($brackets as $bracket_name=>$bracket_id) {
				$bracket_lower = str_replace(" ", "-", strtolower($bracket_name));
				
				$num_brackets = sizeof($teams);			
			
				if ($bracket_id == $finals_bracket_id) {
					$num_teams = $num_brackets;
				}
				else {
					$num_teams = sizeof($teams[$bracket_name]);
				}
				
				$total = $num_teams+1;
				$rounds = 0;
				$temp = $num_teams;
				while($temp >= 1) {
					$temp = $temp/2;
					$rounds++;
				}
				
				
				$output .= "<div id=\"{$bracket_lower}_winners\" style=\"float:left;\">";
				//if (sizeof($teams[$bracket_name])) {
				$output .= "{$bracket_name}<br/>";
				//}
				
				$g = 1;
				$g_1 = 1;
				$g_finals = 1;
				$g_finals_1 = 1;
				$temp = $num_teams;
				$temp_1 = $num_teams;
				$temp_2 = $num_teams;
				$temp_3 = $num_teams;
				for ($gg=1; $gg<$round; $gg++) {
					$temp = $temp/2;
					$g += $temp;
				}
				for ($gg=1; $gg<$round-1; $gg++) {
					$temp_1 = $temp_1/2;
					$g_1 += $temp_1;
				}
				
				if($bracket_id == $finals_bracket_id) {
					$finals_round = 0;
					$num_earlier_rounds = sizeof($teams[$brackets_keys[0]]);
					while($num_earlier_rounds > 1) {
						$num_earlier_rounds = $num_earlier_rounds/2;
						$finals_round++;
					}
					//echo ($round-$finals_round) . " " . $temp_2 . "<br/>";
					if (($round-$finals_round) == 1) {
						$g_finals += $temp_2;
					}
					for ($ggg=1; $ggg<($round-$finals_round); $ggg++) {
						$temp_2 = ($temp_2/2);
						$g_finals += $temp_2;
					}
					
					for ($ggg=1; $ggg<($round-$finals_round-1); $ggg++) {
						$temp_3 = ($temp_3/2);
						$g_finals_1 += $temp_3;
					}
					//echo $temp_2 . " " . $g_finals . "<br/>";
					
					if($temp_2 > 1) {
						$temp2 = $temp_2/2;
					}
					
				}
				
				if ($temp>2) {
					$next = $temp/2;
				}
				else {
					$next = 0;
				}
				
				if($round == 1) {
					
					if (sizeof($teams[$bracket_name])) {
						foreach($games_order as $seed) {
							$next_game = ($next>0 ? $bracket_lower . "_" . ($g+$next) : "");
							/*
							$output .= "<div id=\"{$bracket_lower}_winner_" . $seed ."\" style=\"border: 1px black solid; width: 200px;\">";
							$output .= "{$seed} <span id=\"team_name_{$bracket_lower}_{$seed}\">{$teams[$bracket_name][$seed]}</span> <input type=\"text\" id=\"{$bracket_lower}_team_{$seed}\"  size=\"3\"/><br/>";
							$output .= ($total - $seed) . " <span id=\"team_name_{$bracket_lower}_" . ($total - $seed) . "\">{$teams[$bracket_name][$total - $seed]}</span> <input type=\"text\" id=\"{$bracket_lower}_team_" . ($total - $seed) . "\"  size=\"3\"/><br/>";
							$output .= "<input type=\"button\" class=\"site-button\" value=\"Update Score\" onclick=\"submit_winner('{$bracket_lower}', {$seed}, " . ($total-$seed) . ", {$round}, {$g}, {$tournament_id}, '{$next_game}')\" />";
							$output .= "</div>";
							*/
							
							$team_1_score = "";
							$team_2_score = "";
							
							//$bracket_id = "" . $bracket_id . "";
							/*
							if(isset($winners[$bracket_id][$round])) {
								foreach($winners[$bracket_id][$round] as $key=>$val) {
									echo $key . "<br/>";
								}
							}
							*/
							
							if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$round])) {
								if (isset($winners[$bracket_id][$round][$g])) {
									$team_1_score = ($bracket_lower . "_" . $seed == $winners[$bracket_id][$round][$g]["winner"] ? $winners[$bracket_id][$round][$g]["winner_score"] : $winners[$bracket_id][$round][$g]["loser_score"]);
									$team_2_score = ($bracket_lower . "_" . ($total-$seed) == $winners[$bracket_id][$round][$g]["winner"] ? $winners[$bracket_id][$round][$g]["winner_score"] : $winners[$bracket_id][$round][$g]["loser_score"]);
								}
							}
							if ($team_1_score != "" && $team_2_score != "") {
								$output .= $this->output_entered_game($bracket_lower, $seed, ($total-$seed), $teams[$bracket_name][$seed], $teams[$bracket_name][$total - $seed], $tournament_id, $g, $next_game, $round, $brackets[$bracket_name], $finals_bracket_id, $team_1_score, $team_2_score );
							}
							else {
								$output .= $this->output_unentered_game($bracket_lower, $seed, ($total-$seed), $teams[$bracket_name][$seed], $teams[$bracket_name][$total - $seed], $tournament_id, $g, $next_game, $round, $brackets[$bracket_name], $finals_bracket_id,  $team_1_score, $team_2_score );
							}
							
							$g++;
						}
					}
				}
				else {
					if ($bracket_id == $finals_bracket_id) {
						$g_1 = $g_finals_1;
						//$start = $g_finals + $temp_2;
						if (($round-$finals_round) > 1) {
							$start = $g_finals+$num_teams;
							$g=$g_finals+$temp_2;
						}
						else {
							$start = $g_finals;
							$g=$g_finals;
						}
						if($temp_2 == 1) {
							$next = 0;
						}
						else {
							$next = ($temp_2/2);
						}
						//$g = $g_finals;
						//$g=$g_finals;
					}
					else {
						$start = $g;
					}
					$end = $g + $next;
					$last_start = $g_1;
					
					//echo $g_1;
					if (isset($winners[$bracket_id]) && isset($winners[$bracket_id][$round-1])) {
						$winners_array_1 = $winners[$bracket_id][$round-1];
						ksort($winners_array_1);
						
						if (isset($winners[$bracket_id][$round])) {
							$winners_array = $winners[$bracket_id][$round];
							ksort($winners_array);
						}
						else {
							$winners_array = array();
						}
						
						//$games_info = array();
						//foreach($winners_array_1 as $which_game=>$game_info) {
							
							//echo $g_1 . " " . $g_finals . " " . $start . " " . $next ."<br/>";
							
							/*
							if(isset($winners[$bracket_id][$round-1])) {
								foreach($winners[$bracket_id][$round-1] as $key=>$val) {
									echo $key . "<br/>";
								}
							}
							*/
							
							
							for ($ii=$g_1; $ii<$start; $ii+=2) {
								$next_game = ($next>0 ? $bracket_lower . "_" . ($g+$next) : "");
								if($next_game == "" && $info["has_finals"]) {
									if($bracket_id != $finals_bracket_id) {
										$next_game=str_replace(" ", "-", strtolower($info["finals_bracket"])) . "_" . ($g+$next);
									}
								}
								
								if (isset($winners_array_1[$ii]) && isset($winners_array_1[$ii+1])) {
									$under_pos = strpos($winners_array_1[$ii]["winner"], "_");

									if($bracket_id == $finals_bracket_id) {
										$team_1 = $winners_array_1[$ii]["winner"];
									}
									else {
										$team_1 = substr($winners_array_1[$ii]["winner"], $under_pos+1);
									}
									$team_1_name = $winners_array_1[$ii]["winner_name"];
									
									$under_pos = strpos($winners_array_1[$ii+1]["winner"], "_");
									if($bracket_id == $finals_bracket_id) {
										$team_2 = $winners_array_1[$ii+1]["winner"];
									}
									else {
										$team_2= substr($winners_array_1[$ii+1]["winner"], $under_pos+1);
									}
									$team_2_name= $winners_array_1[$ii+1]["winner_name"];
									
									
									$team_1_score = "";
									$team_2_score = "";
									
									if (isset($winners_array[$g])) {
										//if (isset($winners_array[$g])) {
											if($bracket_id == $finals_bracket_id) {
												$team_1_to_check = $team_1;
												$team_2_to_check = $team_2;
											}
											else {
												$team_1_to_check = $bracket_lower . "_" . $team_1;
												$team_2_to_check = $bracket_lower . "_" . $team_2;
											}
											$team_1_score = ($team_1_to_check == $winners_array[$g]["winner"] ? $winners_array[$g]["winner_score"] : $winners_array[$g]["loser_score"]);
											$team_2_score = ($team_2_to_check == $winners_array[$g]["winner"] ? $winners_array[$g]["winner_score"] : $winners_array[$g]["loser_score"]);
										//}
									}
									
									if ($team_1_score != "" && $team_2_score != "") {
										$output .= $this->output_entered_game($bracket_lower, $team_1, $team_2, $team_1_name, $team_2_name, $tournament_id, $g, $next_game, $round, $brackets[$bracket_name], $finals_bracket_id,  $team_1_score, $team_2_score );
									}
									else {
										$output .= $this->output_unentered_game($bracket_lower, $team_1, $team_2, $team_1_name, $team_2_name, $tournament_id, $g, $next_game, $round, $brackets[$bracket_name], $finals_bracket_id,  $team_1_score, $team_2_score );
									}
				
								}
								$g++;
							}
							
							/*
							if (intval($which_game)%2 == 1) {
								$output .= "{$game_info["winner_name"]}<br/>";
							}
							else {
								$output .= "{$game_info["winner_name"]}<br/><br/>";
							}
							*/
							
							
							
						//}
					}
					else {
						$output .= "There are no games for round {$round}<br/>";
						
					}
				}
				$output .= "</div>";
			}
			$output .= "<div class=\"cleared\"></div>";
			
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
		}
		

	}
	
	
	function output_unentered_game($bracket_lower, $team_1, $team_2, $team_1_name, $team_2_name, $tournament_id, $game, $next_game, $round, $bracket_id, $finals_bracket_id,  $team_1_score="", $team_2_score="" ) {
		//$next_game = ($next>0 ? $bracket_lower . "_" . ($g+$next) : "");
		$output .= "<div id=\"{$bracket_lower}_winner_" . $game ."\" style=\"border: 1px black solid; width: 200px;\">";
		$output .= "{$team_1} <span id=\"team_name_{$bracket_lower}_{$team_1}\">{$team_1_name}</span> <input type=\"text\" id=\"{$bracket_lower}_team_{$team_1}\"  size=\"3\" value=\"{$team_1_score}\"/><br/>";
		$output .= ($team_2) . " <span id=\"team_name_{$bracket_lower}_" . ($team_2) . "\">{$team_2_name}</span> <input type=\"text\" id=\"{$bracket_lower}_team_" . ($team_2) . "\"  size=\"3\" value=\"{$team_2_score}\"/><br/>";
		$output .= "<input type=\"button\" class=\"site-button\" value=\"Update Score\" id=\"submit_button_{$bracket_lower}_{$game}\" onclick=\"submit_winner('{$bracket_lower}', '{$team_1}', '{$team_2}', {$round}, {$game}, {$tournament_id}, '{$next_game}', {$bracket_id}, {$finals_bracket_id} )\" />";
		$output .= "</div>";
		//$g++;
		return $output;
	}
	
	function output_entered_game($bracket_lower, $team_1, $team_2, $team_1_name, $team_2_name, $tournament_id, $game, $next_game, $round, $bracket_id, $finals_bracket_id,  $team_1_score, $team_2_score ) {
		//$next_game = ($next>0 ? $bracket_lower . "_" . ($g+$next) : "");
		$output .= "<div id=\"{$bracket_lower}_winner_" . $game ."\" style=\"border: 1px black solid; width: 200px;\">";
		$output .= "{$team_1} <span id=\"team_name_{$bracket_lower}_{$team_1}\">{$team_1_name}</span> <input type=\"text\" id=\"{$bracket_lower}_team_{$team_1}\"  size=\"3\" value=\"{$team_1_score}\" disabled=\"true\"/><br/>";
		$output .= ($team_2) . " <span id=\"team_name_{$bracket_lower}_" . ($team_2) . "\">{$team_2_name}</span> <input type=\"text\" id=\"{$bracket_lower}_team_" . ($team_2) . "\"  size=\"3\" value=\"{$team_2_score}\" disabled=\"true\"/><br/>";
		$output .= "<input type=\"button\" class=\"site-button\" value=\"Update Score\" id=\"submit_button_{$bracket_lower}_{$game}\" onclick=\"submit_winner('{$bracket_lower}', '{$team_1}', '{$team_2}', {$round}, {$game}, {$tournament_id}, '{$next_game}', {$bracket_id},  {$finals_bracket_id} )\" style=\"display:none;\" />";
		$output .= "<span id=\"enable_button_{$bracket_lower}_{$game}\"><a onclick=\"enableThis('{$bracket_lower}', {$game}, '{$team_1}', '{$team_2}')\">Update this game</a></span>";
		$output .= "</div>";
		//$g++;
		return $output;
	}
	
	
	
}


SpecialPage::addPage( new MarchMadnessEnterWinners );


}

?>
