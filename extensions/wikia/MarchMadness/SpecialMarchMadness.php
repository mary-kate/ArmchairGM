<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadness';
$wgExtensionFunctions[] = 'wfBracketsReadLang';

function wfSpecialMarchMadness(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadness extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSilliness");

	}
	
	function march_madness_link($query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadness")->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSilliness")->escapeFullUrl($query);
	}
	
	function madness_use_cache() {
		return true;
	}

	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		/*
		if (isset($_POST["bracket-picks-submitted"]) &&  $_POST["bracket-picks-submitted"] == "yes" ) {
			MarchMadness::process_picks();
		}
		*/
		//else {
			$output_array = MarchMadness::load_picks();
			if(isset($output_array["title"])) {
				$wgOut->setPageTitle($output_array["title"]);
			}
			if(isset($output_array["text"])) {
				$wgOut->addHTML($output_array["text"]);
			}
		//}
	}
		
	function load_picks() {
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		if (!$wgUser->isLoggedIn()) {
			/*
			$page_title = "Not Logged In";
			$output = "You must be logged in. Please log in and come back here.";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
			*/
			
			return MarchMadness::not_logged_in();
		}
		
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
			//$entry_id = 1;
			//$admin_link = $link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin")->escapeFullUrl();
			$admin_link = $link = MarchMadnessGroupAdmin::march_madness_group_link();
			header("Location: " . $admin_link);
			die("Redirecting");
		}
		$info = MarchMadness::get_entry_info_from_db($entry_id);
		
		if (!sizeof($info)) {
			//$output = "Until the tournament starts, you cannot see another entry's picks.<br/>";
			$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
		
			
			$output_array["title"] = "This is not a valid entry";
			$output_array["text"] = $output;
			return $output_array;
		}
		
		$is_current_user = (($current_user_id == $info["user_id"]) ? true : false);
		//$tournament_started =  (intval(time()) > intval($info["start_date"]) ? true : false);
		
		$tournament_id=$info["tournament_id"];

		$tournament_started = MarchMadness::tournament_started($tournament_id);
		
		if (!$is_current_user && !$tournament_started) {
			$admin_link = MarchMadnessGroupAdmin::march_madness_group_link($info["group_id"]);
			$output = "Until the tournament starts, you cannot see another entry's picks.<br/>";
			$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$admin_link'\" value=\"Back To Standings\">";
		
			
			$output_array["title"] = "You cannot view these picks";
			$output_array["text"] = $output;
			return $output_array;
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
		
		if (!sizeof($teams)) {
			$admin_link = MarchMadnessGroupAdmin::march_madness_group_link();
			$group_link = MarchMadnessGroupAdmin::march_madness_group_link($info["group_id"]);
			$output = "You may not access your bracket until the teams are announced.<br/>";
			$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$admin_link'\" value=\"" . wfMsg("mm_back_to_home") . "\"> ";
			$output .= " <input type=\"button\" class=\"site-button\" onclick=\"location.href='$group_link'\" value=\"" . wfMsg("mm_back_to_group") . "\">";
		
			
			$output_array["title"] = "You can not make your picks yet";
			$output_array["text"] = $output;
			return $output_array;
		}
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
						if(!($picks_full[$w_game_info["which_game"]]["skip_calculate"])) {
							
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
		/*
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
		*/
		
		foreach($picks_full as $picks_full_game=>$picks_full_game_info) {
			$under_pos = strpos($picks_full_game_info["which_pick"], "_");
			$temp_which_pick = substr($picks_full_game_info["which_pick"], $under_pos+1);
			if ($picks_full_game_info["bracket_id"] == intval($finals_info["bracket_id"])) {
				//echo "adding " . $scoring_levels[$picks_full_game_info["round"]-2]. "<br/>";
				$round_to_check = $picks_full_game_info["round"]-2;
			}
			else {
				//echo "adding " . $scoring_levels[$picks_full_game_info["round"]-2]. "<br/>";
				$round_to_check = $picks_full_game_info["round"]-1;
			}
			if (!isset($winners[$picks_full_game_info["bracket_id"]][$round_to_check][$temp_which_pick])) {
			//if(!isset($winners_indiv[$picks_full_game_info["which_pick"]])) {
				if (!(isset($losers_indiv[$picks_full_game_info["pick"]]) && $losers_indiv[$w_game_info["loser"]]<=($round_to_check))) {
					//if(!$picks_full[$w_game_info["which_game"]]["skip_calculate"]) {
						//echo intval($picks_full_game_info["bracket_id"]) . " " . intval($finals_info["bracket_id"]) . "<br/>";
					
					
					//if (!$winners[$picks_full_game_info["bracket_id"]][$picks_full_game_info["round"]-1][$temp_which_pick]["skip_calculate"] && !($info["has_finals"] && (intval($picks_full_game_info["bracket_id"])==intval($finals_info["bracket_id"])) && $picks_full_game_info["round"]-1==$finals_round_preserve)) {
					if (!$winners[$picks_full_game_info["bracket_id"]][$round_to_check][$temp_which_pick]["skip_calculate"] && !$picks_full_game_info["skip_calculate"]) {
						//echo $winners[$picks_full_game_info["bracket_id"]][$picks_full_game_info["round"]-1][$temp_which_pick]["which_pick"] . "<BR/>";
						
						//echo $picks_full_game_info["bracket_id"] . " " . $picks_full_game_info["which_pick"] . " " . $round_to_check . " + " . $scoring_levels[$round_to_check] . "<br/>";
						
						
						$bracket_max[$picks_full_game_info["bracket_id"]] += $scoring_levels[$round_to_check];
						$total_max += $scoring_levels[$round_to_check];
					}
				}
			}
			else {
				//echo $picks_full_game_info["which_pick"] . "<br/>";
			}
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
		
		
		//$games_order = array(1,8,5,4,6,3,7,2);
		$games_order = explode(",", $info["games_order"]);
	
		
			
		foreach($teams as $team_bracket=>$team_picks) {
			$team_vals[$team_bracket] = array_flip($team_picks);
		}
		
	
		//$output .= "<div id=\"march-madness\">";
		$page_title .= $info["entry_name"]." - (" . $info["group_name"] . ") (" . $info["tournament_name"] . ")";
		
		$group_info = MarchMadness::get_group_info_from_db($info["group_id"]);
		
		$output .= "<div id=\"group-actions\" class=\"madness-nav-link madness-bracket-actions\">";
		$output .= "<a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">" . wfMsg("mm_back_to_home") . "</a>";
		$output .= " - <a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link($info["group_id"]) . "\">" . wfMsg("mm_view_standings") . "</a>";
		if(!MarchMadness::tournament_started($tournament_id) && (!$group_info["private"] || ($group_info["private"] && $group_info["creator"]==$current_user_id))){
			$invite_link = MarchMadnessGroupInvite::march_madness_group_link($info["group_id"]);
			$output .= " - <a href=\"{$invite_link}\">" . wfMsg("mm_invite_others") . "</a>";
		}
		$output .= " - <a href=\"" . MarchMadnessUpdate::march_madness_update($entry_id) . "\">" . wfMsg("mm_edit_entry") . "</a>";
		$output .= " - <a href=\"" . MarchMadnessGroupPoints::march_madness_points_link($info["group_id"]) . "\">" . wfMsg("mm_view_scoring") . "</a>";
		$output .= " - <a href=\"/index.php?title=index.php&action=ajax&rs=wfGetPrintableBracket&entry_id={$entry_id}\">" . wfMsg("mm_printable_version") . "</a>";
		$output .= "</div>";
	
		
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
				$link = MarchMadness::march_madness_link($query);
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
				$link = MarchMadness::march_madness_link($query);
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
				
				$next_query = "bracket=" . urlencode($temp_bracket_done) . "&entry_id={$entry_id}";
				$next_link = MarchMadness::march_madness_link($next_query);
				$output .= "<input type=\"hidden\" id=\"next_link\" value=\"{$next_link}\" />";
				
				
				$has_set_next = true;
				$whats_next = $temp_bracket_done;
			}
		}
		if (!$has_set_next) {
			$whats_next = $next_bracket;
			$output .= "<input type='hidden' id='next_bracket' value='{$next_bracket}' />";
			
			$next_query = "bracket=" . urlencode($next_bracket) . "&entry_id={$entry_id}";
			$next_link = MarchMadness::march_madness_link($next_query);
			$output .= "<input type=\"hidden\" id=\"next_link\" value=\"{$next_link}\" />";
		}
		
		
		$output .= "<div class=\"cleared\"></div>";

		
		$game_divs = array();
		for($team_count=0; $team_count<($num_teams); $team_count++) {
			$game_divs[$team_count] = array();
		}
		
		//$output = "rounds: " . $rounds . "<br/>";
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
//echo $top . "<BR>";
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
				
						$team_1 = $games_order[$i];
echo $team_1 . "<br>";
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
				
				
				if ($r < $rounds) {
					if ($is_current_user && !$tournament_started) {
					//if ($is_current_user) {
						$on_click_1 = "onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_1_spot}\", {$next}, \"{$team_2_spot}\", {$num_teams});'";
						$on_click_2 = "onclick='make_pick(\"{$bracket_lower}\", {$g}, \"{$team_2_spot}\", {$next}, \"{$team_1_spot}\", {$num_teams});'";
						//$on_mouseover = " onmouseover=\"\$(this.id).className='pick-button-on'\";";
						//$on_mouseout = " onmouseout=\"\$(this.id).className='pick-button-off'\";";
						$on_mouseover = " onmouseover=\"swapClass(this.id, true, '{$next}', '{$bracket_lower}')\";";
						$on_mouseout = " onmouseout=\"swapClass(this.id, false, '{$next}', '{$bracket_lower}')\";";
						$started_class = "";
						//echo "here";
					}
					else {
						$on_click_1 = "";
						$on_click_2 = "";
						$on_mouseover = "";
						$on_mouseout = "";
						$started_class = " started ";
					}
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
					$game_divs[$which_row][$r-1] = "<div class='{$team_1_correct_class}{$started_class}' team='{$team_1_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_1_spot}' {$on_click_1} {$on_mouseover} {$on_mouseout}>" . ($info["tournament_id"]==1 ? $display_seed_1 : "") . $team_1_name . "</div>";
					$which_row = (($game_in_round) * pow(2, $r-1));
					$game_divs[$which_row][$r-1] = "<div class='{$team_2_correct_class}{$started_class}' team='{$team_2_code}' round='{$output_r}' bracket='{$bracket}' id='{$team_2_spot}' {$on_click_2} {$on_mouseover} {$on_mouseout}>" . ($info["tournament_id"]==1 ? $display_seed_2 : "") . $team_2_name . "</div>";
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
					//echo "which:".$which_row . "<BR>";
					//$game_divs[$which_row][$r-1] = "<div id='" . $game . "'><div team='{$team_1_code}' round='{$output_r}' id='{$team_1_spot}' class='{$team_1_correct_class}' >" . $display_seed_1 . $team_1_name . "</div></div>";
					$game_divs[$which_row][$r-1] = "<div team='{$team_1_code}' round='{$output_r}' id='{$team_1_spot}' class='{$team_1_correct_class}{$started_class}' {$on_mouseover} {$on_mouseout}>" . ($info["tournament_id"]==1 ? $display_seed_1 : "") . $team_1_name . "</div>";
				}
				$game_in_round++;
				$g++;
			}
				
			
				
			
			//$output .= "</div>";
			//$output .= "<form method=\"POST\" action=\"bracket_ajax.php\" id=\"bracket-picks\" name=\"bracket-picks\">";
			
			
		}
		
		$output .= "<div id=\"madness-messages\" class=\"madness-points\">";
		if($tournament_started) {
			$output .= "{$bracket} points: {$bracket_points[$bracket_id]} (max: " . ($bracket_points[$bracket_id] + $bracket_max[$bracket_id]) . ") , Total points: {$total_points} (max: " . ($total_points + $total_max) . ")";
		}
		
		$output .= "</div>";
		
		$output .= "<div class=\"silliness-container\">";
		$output .= "<div class=\"silliness-left bracket-left\">";


		$output .= "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"picks-table\">\n";
		for($which_row=0; $which_row<sizeof($game_divs); $which_row++) {
			$output .= "<tr><!--{$which_row}-->\n";
			foreach($game_divs[$which_row] as $round_id=>$game_output) {
				$output .= "<td rowspan=\"" . pow(2, $round_id) . "\">{$game_output}</td>\n";
			}
			$output .= "</tr>\n";
		}
		$output .= "</table>\n";
		
		if ($group_info["tiebreaker"] && $has_finals && $bracket == $finals_bracket_name) {
			
			if ($is_current_user && !$tournament_started) {
				$disable_tiebreak = "";
				$display_tiebreak = true;
			}
			else {
				$disable_tiebreak = "disabled=\"true\"";
				$display_tiebreak = false;
			}

			$output .= "<br/>";
			$output .= "<div class=\"madness-tiebreaker\">";
			$output .= "Tiebreaker: <input type=\"text\" {$disable_tiebreak} id=\"pick-tiebreaker\" name=\"pick-tiebreaker\" value=\"{$info["tiebreaker"]}\" size=\"3\" /> " . ($display_tiebreak ? "<input type=\"button\" class=\"site-button\" value=\"Set Tiebreaker\" onclick=\"set_tiebreak($entry_id);\"/>" : "" ) . " (total score for both teams in finals)";
			$output .= "</div>";
		}
		
		//$output .= MarchMadness::get_mini_standings($info["group_id"]);
		
		/*
		$group_standings = MarchMadness::get_group_standings_from_db($info["group_id"]);
		$count = 0;
		$mini_standings_limit = 5;
		$output .= "<div class=\"madness-bracket-info\">";
				$output .= "<h2>".wordwrap($info["group_name"], 25, "<br/>\n", true)."</h2>";
				$output .= "<div class=\"mini-standings-entry header\">Entry</div><div class=\"mini-standings-points header\">Points</div><div class=\"cleared\"></div>";
				foreach ($group_standings as $member_entry=>$member) {
					$query = "entry_id={$member_entry}";
					$member_link = MarchMadness::march_madness_link($query);
					//$member = User::newFromID($member_id);
					$output .= "<div class=\"mini-standings-entry\"><a href=\"{$member_link}\">{$member["entry_name"]}</a></div><div class=\"mini-standings-points\">{$member["points"]}</div><div class=\"cleared\"></div>";
					$count++;
					if($count>=$mini_standings_limit) {
						break;
					}
				}
				$output .= "<br/><div class=\"mini-standings-desc\">Top " . ($mini_standings_limit<sizeof($group_standings) ? $mini_standings_limit : sizeof($group_standings)) . " of " . sizeof($group_standings) . " entries. (<a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link($info["group_id"]) . "\">Full Standings</a>)</div><div class=\"cleared\"></div>";
				//$output .= "<br/><a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">Back To Admin Page</a>";
		$output .= "</div>";
		*/
		
		$query = "entry_id={$entry_id}&bracket={$whats_next}";
		$submit_link = MarchMadness::march_madness_link($query);
		
		$output .= "<form method=\"POST\" action =\"{$submit_link}\" id=\"bracket-picks\" name=\"bracket-picks\">";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-field\" id= \"bracket-picks-field\" value=\"\" />";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-return\" id= \"bracket-picks-return\" value=\"\" />";
		$output .= "<input type=\"hidden\" name=\"bracket-picks-submitted\" id= \"bracket-picks-submitted\" value=\"yes\" />";
		$output .= "</form>";

		$output .= "</div>";
		$output .= "<div class=\"silliness-right" . ($num_teams>16 ? "-wide" : "") . "\">";
		$output .= MarchMadness::get_mini_standings($info["group_id"]);
		$output .= MarchMadness::get_right_column("200px;");
		$output .= "</div>";
		$output .= "<div class=\"cleared\"></div>";
		$output .= "</div>";
		//$output .= "<div style=\"clear:both;\"></div>";
		
		$output_array = array("title"=>$page_title, "text"=>$output);
		return $output_array;
	}
	
	
	
		
	function get_teams_from_db($tournament_id) {
		
		global $wgMemc;
		
		$teams = array();
		
		$key = wfMemcKey( 'marchmadness', 'teams', 'tournament' , $tournament_id );
			//$wgMemc->delete( $key );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache()){
			wfDebug( "Cache Hit - Got teams for tournament {$tournament_id} from cache (size: " . sizeof($data) . "\n" );
			$teams = $data;
		}else{
		
			wfDebug( "Cache Miss - Got teams for tournament {$tournament_id} from db\n" );
			$dbr = wfGetDB( DB_SLAVE );
			
			//$conn = madness_mysql_connect_select();
			$sql = "SELECT madness_tournament_teams.tournament_id, team_id, team_name, bracket_name as bracket, seed, sort_order FROM madness_tournament_teams inner join madness_tournament_brackets on madness_tournament_teams.bracket=madness_tournament_brackets.bracket_id WHERE madness_tournament_teams.tournament_id={$tournament_id} ORDER BY sort_order ASC, seed ASC";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  if (!isset($teams[$row->bracket])) {
					  $teams[$row->bracket] = array();
				  }
				  
				  $teams[$row->bracket][$row->seed] = $row->team_name; 
			   
			  }
			  
			  $wgMemc->set( $key, $teams);
			
			//mysql_close($conn);
		}
		
		return $teams;
	}
	
	function get_full_picks_from_db($entry_id, $bracket_id=0, $finals=false) {
		
		global $wgMemc;
		
		$finals_key = ($finals ? "1" : "0");
		
		$picks = array();

		
		//$key = wfMemcKey( 'marchmadness', 'fullpicks', $entry_id, $bracket_id, $finals_key );
		$key = wfMemcKey( 'marchmadness', 'fullpicks', $entry_id, $bracket_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data  && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got fullpicks ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$picks = $data;
		}else{
		
			wfDebug( "Cache Miss - Got fullpicks ({$key}) from db\n" );
		
		
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT which_pick, pick, bracket_name,  madness_tournament_picks.bracket as bracket_id, team_name, pick_correct, pick_points, skip_calculate, round FROM madness_tournament_teams, madness_tournament_brackets, madness_tournament_picks, madness_tournament_entry WHERE madness_tournament_teams.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_teams.tournament_id=madness_tournament_entry.tournament_id AND madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_picks.entry_id={$entry_id} AND madness_tournament_picks.pick=madness_tournament_teams.team_id AND madness_tournament_picks.bracket=madness_tournament_brackets.bracket_id " . (!$finals ? "AND madness_tournament_picks.bracket=madness_tournament_teams.bracket " : "") . ($bracket_id ? "AND madness_tournament_picks.bracket=" . $bracket_id : "") . " ORDER BY sort_order ASC, team_name ASC";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {			   
				$picks[$row->which_pick] = array("which_pick"=>$row->which_pick, "pick"=>$row->pick, "bracket_name"=>$row->bracket_name, "team_name"=>$row->team_name, "pick_correct"=>$row->pick_correct, "pick_points"=>$row->pick_points, "skip_calculate"=>$row->skip_calculate, "bracket_id"=>$row->bracket_id, "round"=>$row->round);
				wfDebug( "got from db: {$row->which_pick}, {$row->pick}, {$row->bracket_name}, {$row->team_name}, {$row->bracket_id}, {$row->round}\n");
			}
			
			$wgMemc->set( $key, $picks, 60 * 120 );
			  
		}
		return $picks;
	}
	/*
	function get_picks_from_db($entry_id, $bracket_id=0, $finals=false, $skipcache=false) {
		global $wgMemc;
		
		$finals_key = ($finals ? "1" : "0");
		
		$picks = array();

		
		$key = wfMemcKey( 'marchmadness', 'picks', $entry_id, $bracket_id, $finals_key );
		
		$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && !$skipcache){
			wfDebug( "Cache Hit - Got picks ({$key}) from cache\n" );
			$picks = $data;
		}else{
		
			wfDebug( "Cache Miss - Got picks ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT which_pick, pick, bracket_name, team_name FROM madness_tournament_teams, madness_tournament_brackets, madness_tournament_picks, madness_tournament_entry WHERE madness_tournament_teams.tournament_id=madness_tournament_brackets.tournament_id AND madness_tournament_teams.tournament_id=madness_tournament_entry.tournament_id AND madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_picks.entry_id={$entry_id} AND madness_tournament_picks.pick=madness_tournament_teams.team_id AND madness_tournament_picks.bracket=madness_tournament_brackets.bracket_id " . (!$finals ? "AND madness_tournament_picks.bracket=madness_tournament_teams.bracket " : "") . ($bracket_id ? "AND madness_tournament_picks.bracket=" . $bracket_id : "") . " ORDER BY sort_order ASC, team_name ASC";
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
					  $picks[$row->which_pick] = $row->team_name;
				  }
			}
			$wgMemc->set( $key, $picks );
		}
		return $picks;
	}
	*/
	
	
	function get_winners_from_db($tournament_id, $for_calculation=false) {
		
		global $wgMemc;
		
		$winners = array();
		
		$key = wfMemcKey( 'marchmadness', 'winners', $tournament_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got winners ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$winners = $data;
		}else{
			
			wfDebug( "Cache Miss - Got winners ({$key}) from db\n" );

	
			$dbr =& wfGetDB( DB_SLAVE );
	
			$sql = "SELECT which_game, next_game, winner, winner_name, winner_score, loser, loser_name, loser_score, round, tournament_id, bracket, skip_calculate FROM madness_tournament_winners WHERE madness_tournament_winners.tournament_id={$tournament_id} " . ($for_calculation ? " AND skip_calculate=0 " : "") . " ORDER BY round ASC, which_game ASC";
	
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  if (!isset($winners[$row->bracket])) {
					  $winners[$row->bracket] = array();
				  }
				  
				  if (!isset($winners[$row->bracket][$row->round])) {
					  $winners[$row->bracket][$row->round] = array();
				  }
				  
				  //$bracket_lower = str_replace(" ", "-", strtolower($row->bracket));
				  
				  $under_pos = strpos($row->which_game, "_");
				  $game_key = substr($row->which_game, $under_pos+1);
				  
				  $winners[$row->bracket][$row->round][$game_key] = array(
					"which_game" => $row->which_game,
					"winner" => $row->winner,
					"winner_name" => $row->winner_name,
					"winner_score" => $row->winner_score,
					"loser" => $row->loser,
					"loser_name" => $row->loser_name,
					"loser_score" => $row->loser_score,
					"round" => $row->round,
					"tournament_id" => $row->tournament_id,
					"bracket" => $row->bracket,
					"next_game" => $row->next_game,
					"skip_calculate" => $row->skip_calculate,
					);
			   
			  }

			  $wgMemc->set( $key, $winners );

		}
		return $winners;
	}
	
	
	function get_brackets_from_db($tournament_id) {

		global $wgMemc;

		$brackets = array();
		
		$key = wfMemcKey( 'marchmadness', 'brackets', $tournament_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got brackets ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$brackets = $data;
		}else{
	
			wfDebug( "Cache Miss - Got brackets ({$key}) from db\n" );

			
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY sort_order ASC";
			//$result = mysql_query("SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY bracket_name ASC");
			//$result = mysql_query("SELECT bracket_id, bracket_name FROM madness_tournament_brackets WHERE madness_tournament_brackets.tournament_id={$tournament_id} ORDER BY sort_order ASC");
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $brackets[$row->bracket_name] = $row->bracket_id; 
			   
			}
			
			$wgMemc->set( $key, $brackets );
		}
		
		return $brackets;
	}
	
	function get_entry_info_from_db($entry_id) {
	
		
		global $wgMemc;

		$info = array();

		$key = wfMemcKey( 'marchmadness', 'entryinfo', $entry_id );

		//$wgMemc->delete($key);
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got entry info ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$info = $data;
		}else{
	
			wfDebug( "Cache Miss - Got entry info ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			//$conn = madness_mysql_connect_select();
			//$sql = "SELECT madness_tournament_setup.tournament_id, tournament_name, tournament_desc, games_order,  UNIX_TIMESTAMP(start_date) as start_date, has_finals, finals_bracket, num_teams, num_brackets, madness_tournament_group.group_id, group_name, private as is_private, creator, madness_tournament_entry.entry_id, entry_name, user_id from madness_tournament_setup, madness_tournament_group, madness_tournament_entry WHERE madness_tournament_setup.tournament_id=madness_tournament_group.tournament_id AND madness_tournament_group.group_id=madness_tournament_entry.group_id AND madness_tournament_entry.entry_id={$entry_id}";
			$sql = "SELECT q1.*, scoring FROM ((SELECT madness_tournament_setup.tournament_id, tournament_name, tournament_desc, games_order,  UNIX_TIMESTAMP(start_date) as start_date, has_finals, finals_bracket, num_teams, num_brackets, madness_tournament_group.group_id, group_name, private as is_private, default_levels, creator, madness_tournament_entry.entry_id, entry_name, user_id, madness_tournament_entry.tiebreaker from madness_tournament_setup, madness_tournament_group, madness_tournament_entry WHERE madness_tournament_setup.tournament_id=madness_tournament_group.tournament_id AND madness_tournament_group.group_id=madness_tournament_entry.group_id AND madness_tournament_entry.entry_id={$entry_id}) as q1 LEFT OUTER JOIN madness_tournament_scoring ON q1.group_id=madness_tournament_scoring.group_id)";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $info["tournament_id"] = $row->tournament_id;
				  $info["tournament_name"] = $row->tournament_name;
				  $info["tournament_desc"] = $row->tournament_desc;
				  $info["games_order"] = $row->games_order;
				  $info["start_date"] = $row->start_date;
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
				  $info["tiebreaker"] = $row->tiebreaker;
				  
				  if($row->scoring) {
					  $info["scoring"] = $row->scoring;
				  }
				  else {
					  $info["scoring"] = $row->default_levels;
				  }
			   
			  }
			  
			  $wgMemc->set( $key, $info );

		}
		//mysql_close($conn);
		
		return $info;
		
	}
	
	function get_tournament_info_from_db($tournament_id) {
		
		global $wgMemc;

		$info = array();

		$key = wfMemcKey( 'marchmadness', 'tournamentinfo', $tournament_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got tournament info ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$info = $data;
		}else{
	
			wfDebug( "Cache Miss - Got tournament info ({$key}) from db\n" );

			$dbr =& wfGetDB( DB_SLAVE );
			//$conn = madness_mysql_connect_select();
			$sql = "SELECT madness_tournament_setup.tournament_id, tournament_name, tournament_desc, games_order, UNIX_TIMESTAMP(start_date) as start_date, has_finals, finals_bracket, num_teams, num_brackets, default_levels, tiebreaker FROM madness_tournament_setup WHERE madness_tournament_setup.tournament_id={$tournament_id}";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $info["tournament_id"] = $row->tournament_id;
				  $info["tournament_name"] = $row->tournament_name;
				  $info["tournament_desc"] = $row->tournament_desc;
				  $info["games_order"] = $row->games_order;
				  $info["start_date"] = $row->start_date;
				  $info["has_finals"] = $row->has_finals;
				  $info["num_teams"] = $row->num_teams;
				  $info["num_brackets"] = $row->num_brackets;
				  $info["finals_bracket"] = $row->finals_bracket;
				  $info["default_levels"] = $row->default_levels;
				  $info["tiebreaker"] = $row->default_levels;
				  
			   
			  }

			  $wgMemc->set( $key, $info );
		
		}
		//mysql_close($conn);
		
		return $info;
		
	}
	
	function get_group_info_from_db($group_id) {
		
		global $wgMemc;

		$info = array();

		$key = wfMemcKey( 'marchmadness', 'groupinfo', $group_id );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group info ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$info = $data;
		}else{
	
			wfDebug( "Cache Miss - Got group info ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			//$conn = madness_mysql_connect_select();
			//$sql = "SELECT group_id, group_name, private as is_private, creator, creator_name, madness_tournament_group.tournament_id, password, tournament_name, group_description FROM madness_tournament_group, madness_tournament_setup WHERE madness_tournament_group.group_id={$group_id} AND madness_tournament_group.tournament_id=madness_tournament_setup.tournament_id";
			$sql = "SELECT q1.*, scoring FROM (SELECT group_id, group_name, private as is_private, creator, creator_name, madness_tournament_group.tournament_id, password, tournament_name, group_description, UNIX_TIMESTAMP(start_date) as start_date, default_levels, tiebreaker FROM madness_tournament_group, madness_tournament_setup WHERE madness_tournament_group.group_id={$group_id} AND madness_tournament_group.tournament_id=madness_tournament_setup.tournament_id) as q1 LEFT OUTER JOIN madness_tournament_scoring ON q1.group_id=madness_tournament_scoring.group_id";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $info["group_id"] = $row->group_id;
				  $info["group_name"] = $row->group_name;
				  $info["private"] = $row->is_private;
				  $info["creator"] = $row->creator;
				  $info["creator_name"] = $row->creator_name;
				  $info["tournament_id"] = $row->tournament_id;
				  $info["tournament_name"] = $row->tournament_name;
				  $info["password"] = $row->password;
				  $info["group_description"] = $row->group_description;
				  $info["start_date"] = $row->start_date;
				  $info["tiebreaker"] = $row->tiebreaker;
				  if($row->scoring) {
					  $info["scoring"] = $row->scoring;
				  }
				  else {
					  $info["scoring"] = $row->default_levels;
				  }
					
				  /*
				  $info["group_name"] = $row->group_name;
				  $info["private"] = $row->is_private;
				  $info["creator"] = $row->creator;
				  $info["entry_id"] = $row->entry_id;
				  $info["entry_name"] = $row->entry_name;
				  $info["user_id"] = $row->user_id;
				  */
			   
			  }
			  
			  $wgMemc->set( $key, $info );

		}
		//mysql_close($conn);
		
		return $info;
		
	}
	
	function get_bracket_sort_order($bracket_id) {

		global $wgMemc;

		$order = 0;

		$key = wfMemcKey( 'marchmadness', 'bracketsortorder', $bracket_id );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got bracket order ({$key}) from cache (size: " .$data. ")\n" );
			$order = $data;
		}else{
	
			wfDebug( "Cache Miss - Got bracket order ({$key}) from db\n" );
			
			$dbr =& wfGetDB( DB_SLAVE );
			//$conn = madness_mysql_connect_select();
			
			$sql = "SELECT sort_order, bracket_id from madness_tournament_brackets where bracket_id={$bracket_id}";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $order = $row->sort_order;
			   
			  }
			  $wgMemc->set( $key, $order );
		}
		//mysql_close($conn);
		
		return $order;
		
	}
	
	function get_finals_info($tournament_id) {
		global $wgMemc;

		$info = array();

		$key = wfMemcKey( 'marchmadness', 'finalsinfo', $tournament_id );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got finals info ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$info = $data;
		}else{
	
			wfDebug( "Cache Miss - Got finals info ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
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
			  
			  $wgMemc->set( $key, $info );

		}
		
		return $info;
	}
	
	function get_group_members_from_db($entry_id, $group_id=0) {
		
		if (!$group_id) {
			$entry_info = MarchMadness::get_entry_info_from_db($entry_id);
			$group_id = $entry_info["group_id"];
		}
		
		global $wgMemc;

		$members = array();

		$key = wfMemcKey( 'marchmadness', 'groupmembers', $group_id );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group members ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$members = $data;
		}else{
	
			wfDebug( "Cache Miss - Got group members ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
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
			  $wgMemc->set( $key, $members );
		}
		return $members;
		
	}
	
	function get_group_password_from_db($group_id) {

		global $wgMemc;

		$group_password = "_"; 

		$key = wfMemcKey( 'marchmadness', 'grouppassword', $group_id );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group password  ({$key}) from cache\n" );
			$group_password = $data;
		}else{
	
			wfDebug( "Cache Miss - Got group password ({$key}) from db\n" );
		
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT password AS password FROM madness_tournament_group WHERE madness_tournament_group.group_id={$group_id}";
			$res = $dbr->query($sql);
			if($row = $dbr->fetchObject( $res ) ) {
				$group_password = $row->password;
			}
			  
			$wgMemc->set( $key, $group_password );

		}
		return $group_password;
	}
	
	function get_number_group_entries($group_id) {

		$key = wfMemcKey( 'marchmadness', 'numgroupentries', $group_id );

		wfDebug( "Cache No-try - Got number of group entries ({$key}) from db\n" );
		
		$num_entries = 0;
		
		$dbr =& wfGetDB( DB_SLAVE );
		$sql = "SELECT count(entry_id) AS num_entries FROM madness_tournament_entry WHERE madness_tournament_entry.group_id={$group_id}";
		$res = $dbr->query($sql);
		if($row = $dbr->fetchObject( $res ) ) {
			$num_entries = $row->num_entries;
		}
		
		return $num_entries;
	}
	
	function get_tournament_list_from_db() {
		
		global $wgMemc;

		$info = array();

		$key = wfMemcKey( 'marchmadness', 'tournamentlist' );
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got tournament list ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$info = $data;
		}else{
	
			wfDebug( "Cache Miss - Got tournament list ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			//$conn = madness_mysql_connect_select();
			$sql = "SELECT madness_tournament_setup.tournament_id, tournament_name, tournament_desc, games_order, start_date, has_finals, finals_bracket, num_teams, num_brackets FROM madness_tournament_setup order by tournament_id ASC";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				$info[$row->tournament_id] = array(
					"tournament_id" => $row->tournament_id,
					"tournament_name" => $row->tournament_name,
					"tournament_desc" => $row->tournament_desc,
					"games_order" => $row->games_order,
					"start_date" => $row->start_date,
					"has_finals" => $row->has_finals,
					"num_teams" => $row->num_teams,
					"num_brackets" => $row->num_brackets,
					"finals_bracket" => $row->finals_bracket,
				  );
				
				  $wgMemc->set( $key, $info );
			  
			   
			  }
		}
		//mysql_close($conn);
		
		return $info;
		
	}
	
	function get_group_standings_from_db($group_id) {
	
		
		
		global $wgMemc;
		
		$key = wfMemcKey( 'marchmadness', 'groupstandings', $group_id );

		//wfDebug( "Cache No-try - Got group list ({$key}) from db\n" );
		//$wgMemc->delete($key);
		$standings = array();
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group standings ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$standings = $data;
		}else{
		
			wfDebug( "Cache Miss - Got group standings ({$key}) from db\n" );
			
			$group_info = MarchMadness::get_group_info_from_db($group_id);
			//$tournament_started =  (intval(time()) > intval($group_info["start_date"]) ? true : false);
			
			$winners = MarchMadness::get_winners_from_db($group_info["tournament_id"]);
			
			$dbr =& wfGetDB( DB_SLAVE );
			if (sizeof($winners)) {
				//$sql = "SELECT entry_id, entry_name, user_name, user_id, SUM(points) as points FROM (SELECT madness_tournament_entry.entry_id, entry_name, user_name, user_id, points FROM madness_tournament_entry, madness_tournament_picks, madness_tournament_scoring_round WHERE madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_entry.group_id={$group_id} AND madness_tournament_scoring_round.round = madness_tournament_picks.round AND madness_tournament_picks.pick_correct=1 AND madness_tournament_picks.skip_calculate=0 UNION SELECT entry_id, entry_name, user_name, user_id, 0 as points FROM madness_tournament_entry WHERE group_id={$group_id} AND entry_id NOT IN (SELECT distinct entry_id from madness_tournament_picks where group_id={$group_id}) ) as q1 GROUP BY entry_id ORDER BY points DESC, entry_name ASC";
				$sql = "SELECT * FROM (SELECT madness_tournament_entry.entry_id, entry_name, user_name, user_id, SUM(points) as points FROM madness_tournament_entry, madness_tournament_picks, madness_tournament_scoring_round WHERE madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_entry.group_id={$group_id} AND madness_tournament_scoring_round.round = madness_tournament_picks.round AND madness_tournament_picks.pick_correct=1 AND madness_tournament_picks.skip_calculate=0 AND madness_tournament_scoring_round.group_id=madness_tournament_entry.group_id GROUP BY entry_id ORDER BY points DESC, entry_name ASC) as q1 UNION SELECT entry_id, entry_name, user_name, user_id, 0 as points FROM madness_tournament_entry WHERE group_id={$group_id} AND entry_id NOT IN (SELECT distinct entry_id from madness_tournament_picks where group_id={$group_id})";
				
			}
			else {
				$sql = "SELECT madness_tournament_entry.entry_id, entry_name, user_name, user_id, SUM(pick_points) as points FROM madness_tournament_entry LEFT OUTER JOIN madness_tournament_picks on madness_tournament_entry.entry_id=madness_tournament_picks.entry_id WHERE madness_tournament_entry.group_id={$group_id} GROUP BY entry_id ORDER BY points DESC, entry_name ASC";
				//$sql_2 = "";
			}
			
			$sql_2 = "SELECT madness_tournament_entry.entry_id, SUM(points) as points FROM madness_tournament_entry, madness_tournament_picks, madness_tournament_scoring_round WHERE madness_tournament_entry.entry_id=madness_tournament_picks.entry_id AND madness_tournament_entry.group_id={$group_id} AND madness_tournament_scoring_round.round = madness_tournament_picks.round AND (madness_tournament_picks.pick_correct=1 OR madness_tournament_picks.pick_correct='-') AND madness_tournament_picks.skip_calculate=0 AND madness_tournament_scoring_round.group_id=madness_tournament_entry.group_id GROUP BY entry_id ORDER BY points DESC, entry_name ASC";
			
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				$points = 0;
				if ($row->points) {
					$points = $row->points;
				}
		
				
				$standings[$row->entry_id] = array(
					"entry_id" => $row->entry_id,
					"entry_name" => $row->entry_name,
					"points" => $points,
					"user_name" => $row->user_name,
					"user_id" => $row->user_id,
					"max_points" => 0,
					
				  );

				  
			   
			  }
			
			  $dbr2 =& wfGetDB( DB_MASTER );
			 
			  if ($sql_2 != "") {
				  $res = $dbr2->query($sql_2);
				while ($row = $dbr2->fetchObject( $res ) ) {
				  
					$points = 0;
					if ($row->points) {
						$points = $row->points;
					}
					
					if (isset($standings[$row->entry_id])) {
						$standings[$row->entry_id]["max_points"] = $row->points;
					}
				   
				  }
			  }
			  
			  $wgMemc->set( $key, $standings );

		}
		
		//mysql_close($conn);
		
		return $standings;
		
	}
	
	function get_all_groups_from_db($tournament_id) {
		
		global $wgMemc;
		
		$key = wfMemcKey( 'marchmadness', 'grouplist', $tournament_id );

		//wfDebug( "Cache No-try - Got group list ({$key}) from db\n" );
		//$wgMemc->delete($key);
		$groups = array();
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group list ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$groups = $data;
		}else{
	
			wfDebug( "Cache Miss - Got group list ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT group_id, group_name FROM madness_tournament_group WHERE tournament_id={$tournament_id} ORDER BY group_id ASC";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				$groups[$row->group_id] = $row->group_name;
			}
			
			$wgMemc->set( $key, $groups );
			
		}
		return $groups;
		
	}
	
	function tournament_started($tournament_id, $group_id=0) {
		if ($group_id) {
			$info = MarchMadness::get_group_info_from_db($group_id);
		}
		else {
			$info = MarchMadness::get_tournament_info_from_db($tournament_id);
		}
		$tournament_started =  (intval(time()) > intval($info["start_date"]) ? true : false);
		
		return $tournament_started;
	}
	
	function truncate_text($text, $limit=20, $ellipsis=true) {
		if (strlen($text) > $limit) {
			$text =  substr($text, 0, $limit) . ($ellipsis ? "..." : "");
		}
		return $text;
	}
	
	function get_mini_standings($group_id, $limit=5) {
		$group_info = MarchMadness::get_group_info_from_db($group_id);
		$group_standings = MarchMadness::get_group_standings_from_db($group_id);
		$count = 0;
		$mini_standings_limit = $limit;
		$output = "<div class=\"madness-bracket-info\">";
				$output .= "<h2>".wordwrap($group_info["group_name"], 25, "<br/>\n", true)."</h2>";
				$output .= "<div class=\"mini-standings-line\"><div class=\"mini-standings-entry header\">Entry</div><div class=\"mini-standings-points header\">Points</div><div class=\"cleared\"></div></div>";
				
				foreach ($group_standings as $member_entry=>$member) {
					$output .= "<div class=\"mini-standings-line\">";
					$query = "entry_id={$member_entry}";
					$member_link = MarchMadness::march_madness_link($query);
					//$member = User::newFromID($member_id);
					$output .= "<div class=\"mini-standings-entry\"><a href=\"{$member_link}\">" . MarchMadness::truncate_text($member["entry_name"]) . "</a></div><div class=\"mini-standings-points\">{$member["points"]}</div><div class=\"cleared\"></div>";
					$output .= "</div>";
					$count++;
					if($count>=$mini_standings_limit) {
						break;
					}
				}
				$output .= "<br/><div class=\"mini-standings-desc\">Top " . ($mini_standings_limit<sizeof($group_standings) ? $mini_standings_limit : sizeof($group_standings)) . " of " . sizeof($group_standings) . " entries. (<a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link($group_info["group_id"]) . "\">Full Standings</a>)</div><div class=\"cleared\"></div>";
				//$output .= "<br/><a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">Back To Admin Page</a>";
		$output .= "</div>";
		
		return $output;
	}
	
	function get_skyscraper_ad() {
		$output = "<div class=\"bracket-ad\">
		
			<!-- FM Skyscraper Zone -->\n
			<script type='text/javascript'>\n
			var federated_media_section = '';\n
			</script>\n
			<script type='text/javascript' src='http://static.fmpub.net/zone/817'></script>\n
			<!-- FM Skyscraper Zone -->\n

			<script type='text/javascript'>\n
			var federated_media_section = '';\n
			</script>\n
			<script type='text/javascript' src='http://static.fmpub.net/zone/859'></script>\n
		
		
		</div>";
		return $output;
	}
	
	function getRandomCasualGame(){
		global $wgBlogPageDisplay, $IP, $wgMemc;
		return wfGetRandomGameUnit();
	}
	
	function getDontMiss(){
		global $wgOut, $wgBlogPageDisplay, $wgMemc, $wgBlogCategory;
		
		$listpages = "<listpages>
				category=Opinions
				order=PublishedDate
				Published=Yes
				Level=1
				count=5
				showblurb=off
				showstats=no
				showdate=no
				ShowPicture=No
				Nav=No
			</listpages>";
			
		$output = "<div class=\"blog-container\">
			<h2>Don't Miss</h2>
			<div>".$wgOut->parse($listpages, false)."</div>
		</div>";
		
		return $output;	
	}
	
	function get_right_column($fixed_width="", $ad=false, $game=true, $dont_miss=true) {
		
		$output .= "<div class=\"bracket-right-column\"" . ($fixed_width != "" ? " style=\"width: {$fixed_width} !important;\"" : "" ) . ">";
		if ($dont_miss) {
			$output .= MarchMadness::getDontMiss();
		}
		if ($game) {
			$output .= MarchMadness::getRandomCasualGame();
		}
		/*
		if ($ad) {
			$output .= MarchMadness::get_skyscraper_ad();
		}
		*/

		$output .= "</div>";
		
		
		return $output;
	}
	
	function not_logged_in() {
		global $wgOut;
		$login_link = Title::makeTitle(NS_SPECIAL, "Login");
		$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
		
		$page_title = "Whoops! You need to Log In or Register";
		$output = "You must be a registered users of ArmchairGM and logged in to use the Tournament Manager. Please register or log in to continue.";
		/*$output .= "<div class=\"madness-button\">
		</div>";*/
		$output .= "<div class=\"madness-nav-link\">";
			/*<a href=\"" . $login_link->escapeFullURL() . "\">Log In</a> - 
			<a href=\"" . $register_link->escapeFullURL() . "\">Register</a>*/
		$output .= "<input type=\"button\" value=\"Log In\" class=\"site-button\" onclick=\"document.location='".$login_link->escapeFullURL()."'\"/>
			<input type=\"button\" value=\"Register\" class=\"site-button\" onclick=\"document.location='".$register_link->escapeFullURL()."'\"/>";

		$output .= "</div>";
		$wgOut->setPageTitle($page_title);
		$wgOut->addHTML($output);
		return "";
	}
	
	function valid_email($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
			return false;
			}
		}  
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
				return false;
				}
			}
		}
		return true;
	} 
	
	
}


SpecialPage::addPage( new MarchMadness );

//read in localisation messages
function wfBracketsReadLang(){
	//global $wgMessageCache, $IP, $wgPickGameDirectory;
	global $wgMessageCache, $IP;
	$wgBracketsDirectory = "{$IP}/extensions/wikia/MarchMadness";
	require_once ( "$wgBracketsDirectory/Brackets.i18n.php" );
	foreach( efWikiaBrackets() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}



}

?>
