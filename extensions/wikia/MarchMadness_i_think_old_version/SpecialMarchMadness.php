
<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadness';

function wfSpecialMarchMadness(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadness extends SpecialPage {
	
	function MarchMadness(){
		UnlistedSpecialPage::UnlistedSpecialPage("MarchMadness");

	}
	
	function march_madness_link($query="") {
		return Title::makeTitle(NS_SPECIAL, "MarchMadness")->escapeFullUrl($query);
	}

	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		if (isset($_POST["bracket-picks-submitted"]) &&  $_POST["bracket-picks-submitted"] == "yes" ) {
			$this->process_picks();
		}
		//else {
			$output_array = $this->load_picks();
			$wgOut->setPageTitle($output_array["title"]);
			$wgOut->addHTML($output_array["text"]);
		//}
	}
		
	function load_picks() {
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");

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
			$entry_id = 1;
		}
		$info = $this->get_entry_info_from_db($entry_id);
		$is_current_user = (($current_user_id == $info["user_id"]) ? true : false);
		
		$group_members = $this->get_group_members_from_db($entry_id, $info["group_id"]);
	
		if (!sizeof($info)) {
			die("Improper entry id");
		}
		$tournament_id=$info["tournament_id"];
		
		$brackets = $this->get_brackets_from_db($tournament_id);
		$brackets_done = array();
		
		if (isset($_GET["bracket"])) {
			$bracket = $_GET["bracket"];
		}
		else {
			$brackets_keys = array_keys($brackets);
			$bracket = $brackets_keys[0];
		}
		
		$teams = $this->get_teams_from_db($tournament_id);
		$picks = $this->get_picks_from_db($entry_id);
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
		
		$finals_info = $this->get_finals_info($tournament_id);
		
	
		
		if ($info["has_finals"]) {
			$has_finals = true;
			$finals_bracket_name = $finals_info["finals_bracket"];;
			$finals_picks_temp = $this->get_picks_from_db($entry_id, $finals_info["bracket_id"], true);
			$finals_picks = array();
			$finals_teams = array();
			foreach($finals_picks_temp as $finals_pick=>$finals_pick_info) {
				$finals_picks[$finals_pick] = $finals_pick_info[0];
				$finals_teams[$finals_pick] = $finals_pick_info[1];
			}
			$finals_round = 0;
			$num_earlier_rounds = $info["num_teams"]/$info["num_brackets"];
			while($num_earlier_rounds >= 1) {
				$num_earlier_rounds = $num_earlier_rounds/2;
				$finals_round++;
			}
			
		}
		else {
			$has_finals = false;
			$finals_bracket_name = "";
			$finals_picks = array();
			$finals_teams = array();
		}
		
		
		//$finals_team_vals = array_flip($finals_picks);
			
		$num_brackets = sizeof($teams);
		
		$output = "";
		
		
		
			
		
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
		
		
		$games_order = array(1,8,5,4,6,3,7,2);
		
		
		
			
		foreach($teams as $team_bracket=>$team_picks) {
			$team_vals[$team_bracket] = array_flip($team_picks);
		}
		
			
		$output .= "<div id=\"march-madness\">";
		$page_title .= $info["entry_name"]." - (" . $info["group_name"] . ") (" . $info["tournament_name"] . ")";
		foreach($teams as $team_bracket=>$team_picks) {
			//$bracket_complete = false;
			//echo $pick_counts[$team_bracket];
			if (isset($pick_counts[$team_bracket]) && $pick_counts[$team_bracket] == (sizeof($team_picks)-1)) {
				$brackets_done[$team_bracket] = true;
				$completeness_text = "(done)";
				//$bracket_complete = true;
			}
			elseif(isset($pick_counts[$team_bracket]) && $pick_counts[$team_bracket] > 0 && $pick_counts[$team_bracket] < (sizeof($team_picks)-1)) {
				$completeness_text = "(" . $pick_counts[$team_bracket] . " / " . (sizeof($team_picks)-1) . ")";
			}
			else {
				$completeness_text = "";
			}
			if ($team_bracket != $bracket) {
				$query = "bracket=" . urlencode($team_bracket) . "&entry_id={$entry_id}";
				$link = $this->march_madness_link($query);
				//****$btn_onclick = " onclick='location.href=\"bracket.php?bracket=" . $team_bracket . "&entry_id={$entry_id}\"' ";
				if (!$is_current_user) {
					$btn_onclick = " onclick='location.href=\"{$link}\"' ";
				}
				else {
					$btn_onclick = " onclick='calculateResultsAndSwitch({$num_teams}, \"" . str_replace(" ", "-", strtolower($bracket)) . "\", \"" . $link . "\" )' ";
				}
				$btn_class = "bracket-button-off";
			}
			else {
				$btn_onclick = "";
				$btn_class = "bracket-button-on";
			}
			$output .= "<div id='bracket-button-" . str_replace(" ", "-", strtolower($team_bracket)) . "' class='{$btn_class}' {$btn_onclick}>{$completeness_text} {$team_bracket}</div>"; 
		}
		if ($has_finals) {
			//$bracket_complete = false;
			//echo sizeof($finals_picks);
			if (sizeof($finals_picks)-$num_brackets == ($num_brackets)-1) {
				$brackets_done[$finals_bracket_name] = true;
				$completeness_text = "(done)";
				//$bracket_complete = true;
			}
			elseif( sizeof($finals_picks)-$num_brackets > 0 && sizeof($finals_picks)-$num_brackets <($num_brackets)-1) {
				$completeness_text = "(" . (sizeof($finals_picks)-$num_brackets) . " / " . (($num_brackets)-1) . ")";
			}
			else {
				$completeness_text = "";
			}
			if ($finals_bracket_name != $bracket) {
				$query = "bracket=" . urlencode($finals_bracket_name) . "&entry_id={$entry_id}";
				$link = $this->march_madness_link($query);
				//****$btn_onclick = " onclick='location.href=\"bracket.php?bracket=" . $finals_bracket_name . "&entry_id={$entry_id}\"' ";
				if(!$is_current_user) {
					$btn_onclick = " onclick='location.href=\"{$link}\"' ";
				}
				else {
					$btn_onclick = " onclick='calculateResultsAndSwitch({$num_teams}, \"" . str_replace(" ", "-", strtolower($bracket)) . "\", \"" . $link . "\" )' ";
				}
				$btn_class = "bracket-button-off";
			}
			else {
				$btn_onclick = "";
				$btn_class = "bracket-button-on";
			}
			$output .= "<div id='bracket-button-" . str_replace(" ", "-", strtolower($finals_bracket_name)). "' class='{$btn_class}' {$btn_onclick}>{$completeness_text} {$finals_bracket_name}</div>";
		}
		$output .= "<div id='complete-check'></div>";
		$output .= "<input type='hidden' id='tournament_id' value='{$tournament_id}' />";
		$output .= "<input type='hidden' id='entry_id' value='{$entry_id}' />";
		$output .= "<input type='hidden' id='bracket' value='{$bracket}' />";
		$output .= "<input type='hidden' id='bracket_lower' value='" . strtolower($bracket) . "' />";
		$output .= "<input type='hidden' id='bracket_id' value='{$bracket_id}' />";
		$output .= "<input type='hidden' id='any_changes' value='0' />";
		if ($has_finals && $finals_bracket_name == $bracket) {
			$output .= "<input type='hidden' id='is_finals' value='1' />";
		}
		else {
			$output .= "<input type='hidden' id='is_finals' value='0' />";
		}
		
		$has_set_next = false;
		$next_bracket;
		$brackets_done[$bracket] = true;
		$whats_next = '';
		foreach($brackets_done as $temp_bracket_done=>$is_bracket_done) {
			$next_bracket = $temp_bracket_done;
			//echo $temp_bracket_done . ":" . $is_bracket_done . ":" . $has_set_next . "<br/>";
			if (!$is_bracket_done && !$has_set_next) {
				$output .= "<input type='hidden' id='next_bracket' value='{$temp_bracket_done}' />";
				$has_set_next = true;
				$whats_next = $temp_bracket_done;
			}
		}
		if (!$has_set_next) {
			$whats_next = $next_bracket;
			$output .= "<input type='hidden' id='next_bracket' value='{$next_bracket}' />";
		}
		
		
		$output .= "<div style='clear:both;'></div>";
		
		//$output = "rounds: " . $rounds . "<br/>";
		$g = 1;
		$p = 1;
		$t = 1;
		for($r=1; $r<=$rounds; $r++) {
			if ($r == $rounds) {
				$top = ((pow(2,$r-2)-1)*29) + 13;
			}
			else {
				$top = ((pow(2,$r-1)-1)*29);
			}
			$output .= "<div id='round_{$r}' style='float:left; position:relative; top:" . $top . "px;'>";
			
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
						
					}
					else {
						$which_array = $teams[$bracket];
						$team_1 = $games_order[$i];
						$team_2 = $total-$team_1;
						$team_1_code = $team_1;
						$team_2_code = $team_2;
						//$display_seed_1 = $team_1 . ". ";
						//$display_seed_2 = $team_2 . ". ";
						$display_seed_1 = "";
						$display_seed_2 = "";
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
						
						
						//$team_1_code = "{$bracket}_{$team_1}";
						//$team_2_code = "{$bracket}_{$team_2}";
					}
					$display_seed_1 = "";
					$display_seed_2 = "";
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
				
				if ($r < $rounds) {
					if ($is_current_user) {
						$on_click_1 = "onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_1_spot}\", {$next}, \"{$team_2_spot}\", {$num_teams});'";
						$on_click_2 = "onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_2_spot}\", {$next}, \"{$team_1_spot}\", {$num_teams});'";
						//echo "here";
					}
					else {
						$on_click_1 = "";
						$on_click_2 = "";
					}
					
					$output .= "<div id='" . $game . "' class='game' style='margin-bottom: " . ((pow(2,$r-1)-1)*60) . "px;'><div class='topteam' team='{$team_1_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_1_spot}' {$on_click_1} >" . $team_1_name . "</div><div class='bottomteam' team='{$team_2_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_2_spot}' {$on_click_2} >" . $team_2_name . "</div></div>";
					//$output .= "<div id='" . $game . "' class='game' style='margin-bottom: " . ((pow(2,$r-1)-1)*60) . "px;'><div class='topteam' team='{$team_1_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_1_spot}' onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_1_spot}\", {$next}, \"{$team_2_spot}\", {$num_teams});'>" . $team_1_name . "</div><div class='bottomteam' team='{$team_2_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_2_spot}' onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_2_spot}\", {$next}, \"{$team_1_spot}\", {$num_teams});'>" . $team_2_name . "</div></div>";
				}
				else {
					$output .= "<div id='" . $game . "' class='game'><div team='{$team_1_code}' round='{$output_r}' id='{$team_1_spot}' class='champ' >" . $team_1_name . "</div></div>";
				}
				$g++;
			}
				
			
				
			
			$output .= "</div>";
			//$output .= "<form method=\"POST\" action=\"bracket_ajax.php\" id=\"bracket-picks\" name=\"bracket-picks\">";
			
			
		}
		
		$output .= "<div id=\"group-nav\" style=\"float:right; width:300px\">";
		$output .= "<h2>" . $info["group_name"] . "</h2>"; 
		foreach ($group_members as $member_entry=>$member) {
			if ($member_entry != $entry_id) {
				$query = "entry_id={$member_entry}";
				$member_link = $this->march_madness_link($query);
				//$member = User::newFromID($member_id);
				$output .= "<a href=\"{$member_link}\">{$member}</a><br/>";
			}
			else {
				//$member = User::newFromID($member_id);
				$output .= "{$member}<br/>";
			}
		}
		$output .= "</div>";
		
		$query = "entry_id={$entry_id}&bracket={$whats_next}";
		$submit_link = $this->march_madness_link($query);
		
		$output .= "<form method=\"POST\" action =\"{$submit_link}\" id=\"bracket-picks\" name=\"bracket-picks\">";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-field\" id= \"bracket-picks-field\" value=\"\" />";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-return\" id= \"bracket-picks-return\" value=\"\" />";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-submitted\" id= \"bracket-picks-submitted\" value=\"yes\" />";
		$output .= "</form>";
		
		$output .= "</div>";
		$output .= "<div style=\"clear:both;\"></div>";
		
		$output_array = array("title"=>$page_title, "text"=>$output);
		return $output_array;
	}
	
	function process_picks() {
		
		//global $wgRequest;
		if(isset($_POST["bracket-picks-field"])) {
			$picks = $_POST["bracket-picks-field"];
		}
		else {
			$picks = "";
		}
		
		if(isset($_POST["bracket-picks-return"])) {
			$return = $_POST["bracket-picks-return"];
		}
		else {
			//$query = "bracket={$finals_bracket_name}&entry_id={$entry_id}";
			$link = $this->march_madness_link();
			$return = $link;
		}
		
		//get database
		$dbr =& wfGetDB( DB_MASTER );

		
		$picks_array = array();
		$temp_picks_array = explode(";", $picks);
		if (sizeof($temp_picks_array)) {
			$temp_single = explode(":", $temp_picks_array[0]);
			$entry_id = $temp_single[4];
			$bracket_id = $temp_single[3];
			$tournament_info = $this->get_entry_info_from_db($entry_id);
			$finals_info = $this->get_finals_info($tournament_info["tournament_id"]);
			if ($finals_info["has_finals"] && $bracket_id == $finals_info["bracket_id"]) {
				$entered_picks_temp = $this->get_picks_from_db($entry_id, $bracket_id, true);
				foreach($entered_picks_temp as $finals_pick=>$finals_pick_info) {
					$entered_picks[$finals_pick] = $finals_pick_info[0];
					
				}
			}
			else {
				$entered_picks = $this->get_picks_from_db($entry_id, $bracket_id);
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
						), __METHOD__
					);
				}
				
			}
			
			
			if ($finals_info["has_finals"] && $bracket_id != $finals_info["bracket_id"]) {
				$sort_order = $this->get_bracket_sort_order($bracket_id);
				//$entered_picks = get_picks_from_db($entry_id, $finals_info["bracket_id"]);
				$entered_picks = array();
				$entered_picks_temp = $this->get_picks_from_db($entry_id, $finals_info["bracket_id"], true);
				foreach($entered_picks_temp as $finals_pick=>$finals_pick_info) {
					$entered_picks[$finals_pick] = $finals_pick_info[0];
					
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
						), __METHOD__
					);
				}
			}
			
		}
		
		//header("Location:{$return}");
		//die();
		
	}
	
		
	function get_teams_from_db($tournament_id) {
		$teams = array();
		
		$dbr =& wfGetDB( DB_MASTER );
		
		//$conn = madness_mysql_connect_select();
		$sql = "SELECT madness_tournament_teams.tournament_id, team_id, team_name, bracket_name as bracket, seed, sort_order FROM madness_tournament_teams inner join madness_tournament_brackets on madness_tournament_teams.bracket=madness_tournament_brackets.bracket_id WHERE madness_tournament_teams.tournament_id={$tournament_id} ORDER BY sort_order ASC, seed ASC";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  if (!isset($teams[$row->bracket])) {
				  $teams[$row->bracket] = array();
			  }
			  
			  $teams[$row->bracket][$row->seed] = $row->team_name; 
		   
		  }
		
		//mysql_close($conn);
		
		return $teams;
	}
	
	function get_picks_from_db($entry_id, $bracket_id=0, $finals=false) {
		$picks = array();
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		
		//$result = mysql_query("SELECT * FROM madness_tournament_teams inner_join madness_tournament_brackets on madness_tournament_teams.bracket=madness_tournament_brackets.bracket_id WHERE madness_tournament_teams.tournament_id={$tournament_id} ORDER BY sortorder ASC, seed ASC");
		//$sql = "SELECT * FROM madness_tournament_teams, madness_tournament_brackets, madness_tournament_picks, madness_tournament_entry WHERE madness_tournament_teams.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_teams.tournament_id=madness_tournament_entry.tournament_id AND madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_picks.entry_id={$entry_id} " . ($bracket_id ? "AND madness_tournament_picks.bracket=" . $bracket_id : "") . " ORDER BY sort_order ASC, team_name ASC";
		$sql = "SELECT which_pick, pick, bracket_name, team_name FROM madness_tournament_teams, madness_tournament_brackets, madness_tournament_picks, madness_tournament_entry WHERE madness_tournament_teams.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_teams.tournament_id=madness_tournament_entry.tournament_id AND madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_picks.entry_id={$entry_id} AND madness_tournament_picks.pick=madness_tournament_teams.team_id AND madness_tournament_picks.bracket=madness_tournament_brackets.bracket_id " . (!$finals ? "AND madness_tournament_picks.bracket=madness_tournament_teams.bracket " : "") . ($bracket_id ? "AND madness_tournament_picks.bracket=" . $bracket_id : "") . " ORDER BY sort_order ASC, team_name ASC";
		//madness_tournament_picks.bracket=madness_tournament_teams.bracket AND
		//echo $sql . "<br/>";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  if ($bracket_id && !$finals) {
				  $picks[$row->which_pick] = $row->pick;
			  }
			  elseif($bracket_id && $finals) {
				$picks[$row->which_pick] = array($row->pick, $row->team_name);
			  }
			  else {
				  if (!isset($picks[$row->bracket_name])) {
					  $picks[$row->bracket_name] = array();
				  }
				  
				  //$pick = intval(substr($row["which_pick"], strpos($row["which_pick"], "_")+1));
				  
				  //$picks[$row["bracket_name"]][$row[$pick]] = $row["team_name"];
				  $picks[$row->which_pick] = $row->team_name;
			  }
		   
		  }
		
		//mysql_close($conn);
		//echo sizeof($picks);
		return $picks;
	}
	
	function get_brackets_from_db($tournament_id) {
		$brackets = array();
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		$sql = "SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY sort_order ASC";
		//$result = mysql_query("SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY bracket_name ASC");
		//$result = mysql_query("SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY sort_order ASC");
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  $brackets[$row->bracket_name] = $row->bracket_id; 
		   
		  }
		
		//mysql_close($conn);
		
		return $brackets;
	}
	
	function get_entry_info_from_db($entry_id) {
		
		$info = array();
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		$sql = "SELECT madness_tournament_setup.tournament_id, tournament_name, tournament_desc, has_finals, finals_bracket, num_teams, num_brackets, madness_tournament_group.group_id, group_name, private as is_private, creator, madness_tournament_entry.entry_id, entry_name, user_id from madness_tournament_setup, madness_tournament_group, madness_tournament_entry WHERE madness_tournament_setup.tournament_id=madness_tournament_group.tournament_id AND madness_tournament_group.group_id=madness_tournament_entry.group_id AND madness_tournament_entry.entry_id={$entry_id}";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  $info["tournament_id"] = $row->tournament_id;
			  $info["tournament_name"] = $row->tournament_name;
			  $info["tournament_desc"] = $row->tournament_desc;
			  $info["has_finals"] = $row->has_finals;
			  $info["num_teams"] = $row->num_teams;
			  $info["num_brackets"] = $row->num_brackets;
			  $info["finals_bracket"] = $row->finals_bracket;
			  $info["group_id"] = $row->group_id;
			  $info["group_name"] = $row->group_name;
			  $info["private"] = $row->is_private;
			  $info["creator"] = $row->creator;
			  $info["entry_id"] = $row->entry_id;
			  $info["entry_name"] = $row->entry_name;
			  $info["user_id"] = $row->user_id;
		   
		  }
		
		//mysql_close($conn);
		
		return $info;
		
	}
	
	function get_bracket_sort_order($bracket_id) {
		
		$order = 0;
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		
		$sql = "SELECT sort_order, bracket_id from madness_tournament_brackets where bracket_id={$bracket_id}";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  $order = $row->sort_order;
		   
		  }
		
		//mysql_close($conn);
		
		return $order;
		
	}
	
	function get_finals_info($tournament_id) {
		$info = array();
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		$sql = "SELECT has_finals, finals_bracket, bracket_id from madness_tournament_setup, madness_tournament_brackets WHERE madness_tournament_setup.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_brackets.bracket_name=madness_tournament_setup.finals_bracket AND madness_tournament_setup.tournament_id={$tournament_id}";
		
		//$result = mysql_query("SELECT has_finals, finals_bracket, bracket_id from madness_tournament_setup, madness_tournament_brackets WHERE madness_tournament_setup.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_brackets.bracket_name=madness_tournament_setup.finals_bracket AND madness_tournament_setup.tournament_id={$tournament_id}");
		$res = $dbr->query($sql);
		if ($row = $dbr->fetchObject( $res ) ) {
		  
			  $info["has_finals"] = $row->has_finals;
			  $info["finals_bracket"] = $row->finals_bracket;
			  $info["bracket_id"] = $row->bracket_id;
		   
		  }
		  else {
			  $info["has_finals"] = false;
			  $info["finals_bracket"] = "";
			  $info["bracket_id"] = "";
		  }
		
		//mysql_close($conn);
		
		return $info;
	}
	
	function get_group_members_from_db($entry_id, $group_id=0) {
		$members = array();
		$dbr =& wfGetDB( DB_MASTER );
		//$conn = madness_mysql_connect_select();
		/*
		if ($group_id) {
			$sql = "SELECT username FROM madness_tournament_entry, user WHERE madness_tournament_entry.group_id={$group_id} AND user.user_id=madness_tournament_entry.user_id ORDER BY entry_id ASC";
		}
		else {
		}
		*/
		$sql = "SELECT user_name, entry_id FROM madness_tournament_entry WHERE madness_tournament_entry.group_id" . ($group_id ? "=" . $group_id : " IN (SELECT group_id from madness_tournament_entry WHERE entry_id={$entry_id})") . " ORDER BY entry_id ASC";
		
		//$result = mysql_query("SELECT has_finals, finals_bracket, bracket_id from madness_tournament_setup, madness_tournament_brackets WHERE madness_tournament_setup.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_brackets.bracket_name=madness_tournament_setup.finals_bracket AND madness_tournament_setup.tournament_id={$tournament_id}");
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  $members[$row->entry_id] = $row->user_name;
		   
		  }
		
		//mysql_close($conn);
		
		return $members;
		
	}
	
	
}


SpecialPage::addPage( new MarchMadness );


}

?>

