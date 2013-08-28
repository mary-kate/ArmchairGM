<?php

$wgExtensionFunctions[] = 'wfSpecialMLBPlayerSearch';

$team_translations = array();


function wfSpecialMLBPlayerSearch(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBPlayerSearch extends SpecialPage {
	
	
	function MLBPlayerSearch(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBPlayerSearch");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		 $wgUser = User::newFromName( "MLB Stats Bot" );
		 $wgUser->addGroup( 'bot' );
		
		if ($wgRequest->getVal("first") != "" || $wgRequest->getVal("last") != "") {
			$output_end = $this->get_player_stats($wgRequest->getVal("first"), $wgRequest->getVal("last"), false);
		}
		elseif ($wgRequest->getVal("playerid") != ""){
			$output_end = $this->get_player_stats(false, false, $wgRequest->getVal("playerid"));
		}
		else {
			$output_end = "";
		}
		
		$output = "<form name=\"form1\">
			<input type=\"hidden\" name=\"title\" id=\"title\" value=\"Special:MLBPlayerSearch\" />
			First: <input type=\"text\" name=\"first\" id=\"first\" /> 
			Last: <input type=\"text\" name=\"last\" id=\"last\" /> 
			<input type=\"submit\" value=\"Lookup Stats\" />
			</form>
			<br/><br/>
			<div id=\"stats\">
			<?php echo do_get_player_stats()  ?>
			</div>";
			
			$output .= "{$output_end}";


		$title_string = "Player Stats";
		
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Jeff/SpecialMLBPlayerSearch.css?{$wgStyleVersion}\"/>\n");

		
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);

		
	}
	
function get_player_stats($first_name, $last_name, $player_id) {
	
	global $wgRequest, $team_translations;

	$output = "";
	$b_output = "";
	$f_output = "";
	$p_output = "";
	$wiki_output = "";
	$wiki_b_output = "";
	$wiki_f_output = "";
	$wiki_p_output = "";
	$wiki_mid_output = "";
	
	$dbr =& wfGetDB( DB_MASTER );
	
	if (!$player_id) {
		//$sql = "SELECT players_info.*, count(distinct Year) as mlb_experience from players_info, fielding_stats WHERE (player_name LIKE '{$first_name}%' AND player_name LIKE '% {$last_name}%') OR (player_fullname LIKE '{$first_name}%' AND player_fullname LIKE '% {$last_name}%') AND players_info.player_id=fielding_stats.player_id  GROUP BY players_info.player_id ORDER BY player_name ASC";
		$sql = "SELECT mlb_players_info.*, count(distinct Year) as mlb_experience from mlb_players_info JOIN mlb_fielding_stats ON mlb_players_info.player_id=mlb_fielding_stats.player_id WHERE (player_name LIKE '{$first_name}%' AND player_name LIKE '% {$last_name}%') OR (player_fullname LIKE '{$first_name}%' AND player_fullname LIKE '% {$last_name}%') GROUP BY mlb_players_info.player_id ORDER BY player_name ASC";
	}
	else {
		$sql = "SELECT mlb_players_info.*, count(distinct Year) as mlb_experience from mlb_players_info, mlb_fielding_stats WHERE mlb_players_info.player_id = {$player_id}  AND mlb_players_info.player_id=mlb_fielding_stats.player_id GROUP BY mlb_players_info.player_id ORDER BY player_name ASC";		
	}
        
        $result = mysql_query($sql);
	
	$players = array();

        while($row = mysql_fetch_array($result)) {
		$players[] = $row; 
        }
	mysql_free_result($result);
	

//***positions sql
//*** SELECT sum(G) as games, pos  FROM `baseball`.`fielding_stats_total` WHERE player_id=18050 group by pos order by games desc
//***mlb experience sql
//***SELECT count(distinct Year) as Years FROM `baseball`.`fielding_stats` where player_id=3481
	
	if (sizeof($players)) {
		if (sizeof($players) > 1) {
			for ($i=0; $i<sizeof($players); $i++) {
				$output .= "<a href=\"/index.php?title=". $wgRequest->getVal("title") . "&playerid={$players[$i]["player_id"]}\">{$players[$i]["player_fullname"]} ({$players[$i]["player_name"]}) {$players[$i]["birthday"]} {$players[$i]["bats"]} {$players[$i]["throws"]} {$players[$i]["height"]} {$players[$i]["weight"]}</a><br/>";
			}
			
			return $output;
			//mysql_close($conn);

		}
		else {
			
			$sql_teams = "SELECT *  FROM mlb_team order by team_abrev desc";
			//$conn = baseball_mysql_connect_select();
		
			$result_teams = mysql_query($sql_teams);
			while($row_teams = mysql_fetch_array($result_teams)) {
				$team_translations[$row_teams["team_abrev"]] = $row_teams["team_name"];				
			}
			mysql_free_result($result_teams);
			
			$sql = "SELECT sum(G) as games, pos  FROM mlb_fielding_stats_total WHERE player_id={$players[0]["player_id"]} group by pos order by games desc";
			//$conn = baseball_mysql_connect_select();
		
			$result = mysql_query($sql);
			if (mysql_num_rows($result) == 0) {
				$sql = "SELECT sum(G) as games, pos  FROM mlb_fielding_stats WHERE player_id={$players[0]["player_id"]} group by pos order by games desc";
				$result = mysql_query($sql);			
			}
			
			$batting_stats = array();
			
			$total_games = 0;
			$games = array();
			$positions = "";
			$is_pitcher = false; 
			while($row = mysql_fetch_array($result)) {
				if ($total_games ==0 && $row["pos"] == "P") {
					$is_pitcher = true;
					wfDebug("pitcher - totalgames={$total_games} and row[pos]={$row["pos"]}");
				}
				else {
					wfDebug("not - totalgames={$total_games} and row[pos]={$row["pos"]}");
				}
				if (!isset($games[$row["pos"]])) {
					$games[$row["pos"]] = $row["games"];
				}
				else {
					$games[$row["pos"]] += $row["games"];
				}
				$total_games += $row["games"];
			}
			mysql_free_result($result);
			
			foreach($games as $key=>$value) {
				
				if ($value/$total_games > .10) {
					$positions .= "{$key} ";
				}
				
			}
			
			$positions = str_replace(" ", ",", trim($positions));
			

			$player = $players[0];
			
			if(strpos($player["player_fullname"], ", Sr.")) {
				$player_fullname = substr($player["player_fullname"], 0, strpos($player["player_fullname"], ", Sr.")) . " " . substr($player["player_fullname"], strpos($player["player_fullname"], ", Sr.") + strlen(", Sr.")) . ", Sr.";
				$player["player_fullname"] = $player_fullname;
			}
			else if(strpos($player["player_fullname"], ", Jr.")) {
				$player_fullname = substr($player["player_fullname"], 0, strpos($player["player_fullname"], ", Jr.")) . " " . substr($player["player_fullname"], strpos($player["player_fullname"], ", Jr.") + strlen(", Jr.")) . ", Jr.";
				$player["player_fullname"] = $player_fullname;
			}

			
			$output .= "
			<div class=\"player-page-container\">
			<div class=\"player-info-div\">
				<div class=\"player-image-div\">
				<img src=\"/images/avatars/default_l.gif\" /><br/>
				(Upload New)
				</div>
				<div class=\"player-info-right\">
				<div class=\"player-info-top\">
					<div class=\"player-info-short-name\">
					{$player["player_name"]}, {$positions}</div>
					{$player["player_fullname"]}
				</div>
				<div class=\"cleared\"></div>
				<div class=\"player-info-bottom-left\">";
				
				$wiki_output .= "{| cellpadding=\"0\" cellspacing=\"0\" class=\"player-profile-top\"\n";
				$wiki_output .= "|-\n";
				$wiki_output .= "| class=\"player-profile-image\" | {{Player Profile Image}}\n";
				$wiki_output .= "| valign=\"top\" |\n";
				$wiki_output .= "{| cellpadding=\"0\" cellspacing=\"0\" class=\"player-profile-information\"\n";
				$wiki_output .= "|-\n";
				$wiki_output .= "| '''Full Name:''' {$player["player_fullname"]}\n";
				$wiki_output .= "| '''Primary Position:''' {$positions}\n";
				//$wiki_output .= "|-\n";
					
				
			if ($player["height"] != "Unknown") {
				$height_weight = "Height";
				$hw_values = $player["height"];
			}
			else {
				$hw_values = "";
				$height_weight = "";
			}
			if ($player["weight"] != "Unknown") {
				if ($height_weight != "") {
					$height_weight .= "/Weight:";
					$hw_values .= "/{$player["weight"]}";
				}
				else {
					$height_weight .= "Weight:";
					$hw_values .= "{$player["weight"]}";
				}
			}
			else {
				$height_weight .= ":";
			}
			
			$output .= "<strong>{$height_weight}</strong> {$hw_values}<br/>
					<strong>Birthdate:</strong> {$player["birthday"]}<br/>
					<strong>Birthplace:</strong> {$player["birthplace"]}<br/>
					<strong>Bat/Throw:</strong> {$player["bats"]}/{$player["throws"]}<br/>";
					
			$output .="</div>
				<div class=\"player-info-bottom-right\">";
				
			if ($player["first_game"] != "") {
				$output .= "<strong>First Game:</strong> {$player["first_game"]}<br/>";
			}
			if ($player["final_game"] != "") {
				$output .= "<strong>Final Game:</strong> {$player["final_game"]}<br/>";
			}
			$output .= "<strong>MLB Experience:</strong> {$player["mlb_experience"]}<br/>";
			
			if ($player["died"] != "") {
				$output .= "<strong>Died:</strong> {$player["died"]}";
				if ($player["deathplace"] != "") {
					$output .= ", {$player["deathplace"]}";
				}
				$output .= "<br/>";
			}
			
			$wiki_output .= "|-\n";
			$wiki_output .= "| '''{$height_weight}''' {$hw_values}\n";
			$wiki_output .= "| '''First Game:''' {$player["first_game"]}\n";
			$wiki_output .= "|-\n";
			$wiki_output .= "| '''Birthdate:''' {$player["birthday"]}\n";
			if ($player["final_game"] != "") {
				$wiki_output .= "| '''Final Game:''' {$player["final_game"]}\n";
			}
			else {
				$wiki_output .= "| '''MLB Experience:''' {$player["mlb_experience"]} ";
				if ($player["mlb_experience"] == "1") {
					$wiki_output .= "year\n";
				}
				else {
					$wiki_output .= "years\n";
				}
			}
			$wiki_output .= "|-\n";
			$wiki_output .= "| '''Birthplace:''' {$player["birthplace"]}\n";
			if ($player["final_game"] != "") {
				$wiki_output .= "| '''MLB Experience:''' {$player["mlb_experience"]} ";
				if ($player["mlb_experience"] == "1") {
					$wiki_output .= "year\n";
				}
				else {
					$wiki_output .= "years\n";
				}

			}
			$wiki_output .= "|-\n";
			
			if ($player["died"] != "") {
				$wiki_output .= "| '''Died:''' {$player["died"]}\n";
				$wiki_output .= "|-\n";
				$wiki_output .= "| '''Deathplace:''' {$player["deathplace"]}\n";
				$wiki_output .= "|-\n";
			}
			
			$wiki_output .= "| '''Bat/Throw:''' {$player["bats"]}/{$player["throws"]}\n";
			$wiki_output .= "|}\n";
			$wiki_output .= "| width=\"220\" valign=\"top\" | {{Player Profile Rating Box}}\n";
			$wiki_output .= "|}\n";
			$wiki_output .= "<div style=\"float:right;margin:0px 0px 10px 10px; width:160px;\">\n";
			$wiki_output .= "__TOC__\n";
			$wiki_output .= "{{Player Profile Ad}}\n";
			$wiki_output .= "</div>\n";
			$wiki_output .= "==Biography==\n";
			
			$wiki_mid_output .= "\n";
			if ($player["final_game"] == "") {
				$wiki_mid_output .= "==Scouting Report==\n";
				$wiki_mid_output .= "\n";
			}
			$wiki_mid_output .= "==Statistics==\n";
			$wiki_mid_output .= "\n";




			$output .= "</div>";
			//$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
			$output .= "</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "<div class=\"player-page-stats-container\">";
				
				
			$output .= "<br/>";
			//$sql = "SELECT * FROM ((SELECT * from batting_stats WHERE player_id = {$player["player_id"]} ORDER BY Year ASC) UNION (SELECT * from batting_stats WHERE player_id = {$player["player_id"]} ORDER BY Year ASC)) ORDER BY YEAR ASC";
			$sql = "SELECT mlb_batting_stats.*, 1 as table_order from mlb_batting_stats WHERE player_id = {$player["player_id"]} UNION SELECT mlb_batting_stats_total.*, 2 as table_order from mlb_batting_stats_total WHERE player_id = {$player["player_id"]} ORDER BY YEAR ASC, table_order ASC, team DESC";

			//$conn = baseball_mysql_connect_select();
		
			$result = mysql_query($sql);
			
			$batting_stats = array();
			
			$count = 0;
			while($row = mysql_fetch_array($result)) {
				
				//$batting_stats[] = $row;
				//$batting_stats[$count]["player_id"] =$row["player_id"];
				if($row["Year"] == "9999") {
					$batting_stats[$count]["Year"] = "Total";
				}
				else {
					$batting_stats[$count]["Year"] =$row["Year"];
				}
				$batting_stats[$count]["Team"] =$row["Team"];
				$batting_stats[$count]["G"] =$row["G"];
				$batting_stats[$count]["AB"] =$row["AB"];
				$batting_stats[$count]["R"] =$row["R"];
				$batting_stats[$count]["H"] =$row["H"];
				$batting_stats[$count]["HR"] =$row["HR"];
				$batting_stats[$count]["RBI"] =$row["RBI"];
				$batting_stats[$count]["AVG"] = MLBStats::formatPercentageStat($row["AVG"]);
				$batting_stats[$count]["OBP"] = MLBStats::formatPercentageStat($row["OBP"]);
				$batting_stats[$count]["SLG"] = MLBStats::formatPercentageStat($row["SLG"]);
				$batting_stats[$count]["2B"] =$row["2B"];
				$batting_stats[$count]["3B"] =$row["3B"];
				$batting_stats[$count]["BB"] =$row["BB"];
				$batting_stats[$count]["SO"] =$row["SO"];
				$batting_stats[$count]["HBP"] =$row["HBP"];
				$batting_stats[$count]["SH"] =$row["SH"];
				$batting_stats[$count]["SB"] =$row["SB"];
				$batting_stats[$count]["IBB"] =$row["IBB"];
				//$batting_stats[$count]["BFW"] =$row["BFW"];
				//$batting_stats[$count]["SF"] =$row["SF"];
				//$batting_stats[$count]["XI"] =$row["XI"];
				//$batting_stats[$count]["ROE"] =$row["ROE"];
				$batting_stats[$count]["GDP"] =$row["GDP"];
				
				$count++;
			}
			mysql_free_result($result);
			
			if (sizeof($batting_stats) > 0) {
				$b_output.= "<div class=\"stats-block\" id=\"batting-stats\">
				<div class=\"stats-block-title\"><h2>Batting Stats:</h2></div>";
				$b_output .= "<div class=\"stats-block-header\">";
				
				$wiki_b_output .= "===Batting Stats===\n";
				$wiki_b_output .= "{| border=\"1\" bordercolor=\"#dcdcdc\" cellpadding=\"2\" cellspacing=\"0\" class=\"player-profile-stats\"\n";
				$wiki_b_output .= "|- class=\"player-profile-stats-header\"\n";
				foreach ($batting_stats[0] as $key=>$value) {
					$b_output .= "<div class=\"stats-batting-column-header\" id=\"{$key}\">{$key}</div>";
					if($key == "Team") {
						//$wiki_b_output .= "|'''{$key}'''\n";
						//$wiki_b_output .= "|\n";
						$wiki_b_output .= "!{$key}\n";
						$wiki_b_output .= "!\n";
					}
					else {
						//$wiki_b_output .= "|'''{$key}'''\n";
						$wiki_b_output .= "!{$key}\n";
					}
				}
				$b_output .= "<div class=\"cleared\"></div></div>";
				for ($i=0; $i<sizeof($batting_stats); $i++) {
					//$count = 0;
					if ($i%2 == 1) {
						$div_style = "-alternate";
					}
					else {
						$div_style = "";
					}
					$b_output .= "<div class=\"stats-block-row{$div_style}\">";
					if ($batting_stats[$i]["Year"] == "Total") {
						 $wiki_b_output .= "|- class=\"player-profile-stats-total\"\n";
					}
					else {
						$wiki_b_output .= "|-\n";
					}
					foreach ($batting_stats[$i] as $key=>$value) {
						/*
						if (trim($value) == "") {
							$value = "&nbsp;";
						}
						*/
						$b_output .= "<div class=\"stats-batting-column\" id=\"{$key}\">{$value}</div>";
						if ($key =="Team") {
							if (strpos($value, " ")) {
								$split_team = substr($value, 0, strpos($value, " "));
								$split_league = substr($value, strpos($value, " ")+1);
							}
							else {
								$split_team = $value;
								$split_league = "";
							}
						
							$wiki_b_output .= "|{$split_team}\n";
							$wiki_b_output .= "|{$split_league}\n";
							
						}
						else {
							$wiki_b_output .= "|{$value}\n";
						}
					}
					$b_output .= "<div class=\"cleared\"></div></div>";
					//$wiki_b_output .= "|-";
				}
				$b_output .= "</div>";
				$wiki_b_output .= "|}\n";
			}
			
			//$sql = "SELECT * from fielding_stats WHERE player_id = {$player["player_id"]} ORDER BY Year ASC";
			$sql = "SELECT mlb_fielding_stats.*, 1 as table_order from mlb_fielding_stats WHERE player_id = {$player["player_id"]} UNION SELECT mlb_fielding_stats_total.*, 2 as table_order from mlb_fielding_stats_total WHERE player_id = {$player["player_id"]} ORDER BY YEAR ASC, table_order ASC, team DESC";


			//$conn = baseball_mysql_connect_select();
		
			$result = mysql_query($sql);
			
			$fielding_stats = array();
			
			$count = 0;
			
			while($row = mysql_fetch_array($result)) {
				
				//$batting_stats[] = $row;
				//$batting_stats[$count]["player_id"] =$row["player_id"];
				if($row["Year"] == "9999") {
					$fielding_stats[$count]["Year"] = "Total";
				}
				else {
					$fielding_stats[$count]["Year"] =$row["Year"];
				}
				$fielding_stats[$count]["Team"] =$row["Team"];
				$fielding_stats[$count]["POS"] =$row["POS"];
				$fielding_stats[$count]["G"] =$row["G"];
				$fielding_stats[$count]["GS"] =$row["GS"];
				$fielding_stats[$count]["INN"] =$row["INN"];
				$fielding_stats[$count]["PO"] =$row["PO"];
				$fielding_stats[$count]["A"] =$row["A"];
				$fielding_stats[$count]["ERR"] =$row["ERR"];
				$fielding_stats[$count]["DP"] =$row["DP"];
				$fielding_stats[$count]["TP"] =$row["TP"];
				$fielding_stats[$count]["PB"] =$row["PB"];
				$fielding_stats[$count]["SB"] =$row["SB"];
				$fielding_stats[$count]["CS"] =$row["CS"];
				$fielding_stats[$count]["PkO"] =$row["PkO"];
				$fielding_stats[$count]["AVG"] = MLBStats::formatPercentageStat($row["AVG"]);
				//$fielding_stats[$count]["LF_G"] =$row["LF_G"];
				//$fielding_stats[$count]["CF_G"] =$row["CF_G"];
				//$fielding_stats[$count]["RF_G"] =$row["RF_G"];
				
				$count++;
			}
			mysql_free_result($result);

			if (sizeof($fielding_stats) > 0) {
				$output.= "<div class=\"stats-block\" id=\"fielding-stats\">
				<div class=\"stats-block-title\"><h2>Fielding Stats:</h2></div>";
				$output .= "<div class=\"stats-block-header\">";
				
				$wiki_f_output .= "===Fielding Stats===\n";
				$wiki_f_output .= "{| border=\"1\" bordercolor=\"#dcdcdc\" cellpadding=\"2\" cellspacing=\"0\" class=\"player-profile-stats\"\n";
				$wiki_f_output .= "|- class=\"player-profile-stats-header\"\n";
				foreach ($fielding_stats[0] as $key=>$value) {
					$output .= "<div class=\"stats-batting-column-header\" id=\"{$key}\">{$key}</div>";
					if($key == "Team") {
						//$wiki_f_output .= "|'''{$key}'''\n";
						//$wiki_f_output .= "|\n";
						$wiki_f_output .= "!{$key}\n";
						$wiki_f_output .= "!\n";
					}
					else {
						//$wiki_f_output .= "|'''{$key}'''\n";
						$wiki_f_output .= "!{$key}\n";
					}
				}
				$output .= "<div class=\"cleared\"></div></div>";
				for ($i=0; $i<sizeof($fielding_stats); $i++) {
					//$count = 0;
					if ($i%2 == 1) {
						$div_style = "-alternate";
					}
					else {
						$div_style = "";
					}
					$output .= "<div class=\"stats-block-row{$div_style}\">";
					if ($fielding_stats[$i]["Year"] == "Total") {
						 $wiki_f_output .= "|- class=\"player-profile-stats-total\"\n";
					}
					else {
						$wiki_f_output .= "|-\n";
					}
					foreach ($fielding_stats[$i] as $key=>$value) {
						/*
						if (trim($value) == "") {
							$value = "&nbsp;";
						}
						*/
						$output .= "<div class=\"stats-batting-column\" id=\"{$key}\">{$value}</div>";
						if ($key =="Team") {
							if (strpos($value, " ")) {
								$split_team = substr($value, 0, strpos($value, " "));
								$split_league = substr($value, strpos($value, " ")+1);
							}
							else {
								$split_team = $value;
								$split_league = "";
							}
						
							$wiki_f_output .= "|{$split_team}\n";
							$wiki_f_output .= "|{$split_league}\n";
							
						}
						else {
							$wiki_f_output .= "|{$value}\n";
						}
					}
					$output .= "<div class=\"cleared\"></div></div>";
					//$wiki_f_output .= "|-";
				}
				$output .= "</div>";
				$wiki_f_output .= "|}\n";
			}
			
			//$sql = "SELECT * from pitching_stats WHERE player_id = {$player["player_id"]} ORDER BY Year ASC";
			$sql = "SELECT mlb_pitching_stats.*, 1 as table_order from mlb_pitching_stats WHERE player_id = {$player["player_id"]} UNION SELECT mlb_pitching_stats_total.*, 2 as table_order from mlb_pitching_stats_total WHERE player_id = {$player["player_id"]} ORDER BY YEAR ASC, table_order ASC, team DESC";


			//$conn = baseball_mysql_connect_select();
		
			$result = mysql_query($sql);
			
			$pitching_stats = array();
			
			$count = 0;
			
			while($row = mysql_fetch_array($result)) {
				
				//$batting_stats[] = $row;
				//$batting_stats[$count]["player_id"] =$row["player_id"];
				if($row["Year"] == "9999") {
					$pitching_stats[$count]["Year"] = "Total";
				}
				else {
					$pitching_stats[$count]["Year"] =$row["Year"];
				}
				$pitching_stats[$count]["Team"] =$row["Team"];
				$pitching_stats[$count]["G"] =$row["G"];
				$pitching_stats[$count]["GS"] =$row["GS"];
				$pitching_stats[$count]["W"] =$row["W"];
				$pitching_stats[$count]["L"] =$row["L"];
				$pitching_stats[$count]["ERA"] = MLBStats::formatERA($row["ERA"]);
				$pitching_stats[$count]["K"] =$row["SO"];
				$pitching_stats[$count]["R"] =$row["R"];
				$pitching_stats[$count]["ER"] =$row["ER"];
				$pitching_stats[$count]["CG"] =$row["CG"];
				$pitching_stats[$count]["SHO"] =$row["SHO"];
				//$pitching_stats[$count]["GF"] =$row["GF"];
				$pitching_stats[$count]["SV"] =$row["SV"];
				$pitching_stats[$count]["IP"] =$row["IP"];
				$pitching_stats[$count]["H"] =$row["H"];
				//$pitching_stats[$count]["BFP"] =$row["BFP"];
				$pitching_stats[$count]["HR"] =$row["HR"];
				$pitching_stats[$count]["BB"] =$row["BB"];
				$pitching_stats[$count]["IB"] =$row["IB"];
				//$pitching_stats[$count]["SH"] =$row["SH"];
				//$pitching_stats[$count]["SF"] =$row["SF"];
				$pitching_stats[$count]["WP"] =$row["WP"];
				$pitching_stats[$count]["HBP"] =$row["HBP"];
				//$pitching_stats[$count]["BK"] =$row["BK"];
				//$pitching_stats[$count]["2B"] =$row["2B"];
				//$pitching_stats[$count]["3B"] =$row["3B"];
				//$pitching_stats[$count]["GDP"] =$row["GDP"];
				//$pitching_stats[$count]["ROE"] =$row["ROE"];
				//$pitching_stats[$count]["PW"] =$row["PW"];
				//$pitching_stats[$count]["RS"] =$row["RS"];
				
				$count++;
			}
			mysql_free_result($result);

			if (sizeof($pitching_stats) > 0) {
				$p_output.= "<div class=\"stats-block\" id=\"pitching-stats\">
				<div class=\"stats-block-title\"><h2>Pitching Stats:</h2></div>";
				$p_output .= "<div class=\"stats-block-header\">";
				
				$wiki_p_output .= "===Pitching Stats===\n";
				$wiki_p_output .= "{| border=\"1\" bordercolor=\"#dcdcdc\" cellpadding=\"2\" cellspacing=\"0\" class=\"player-profile-stats\"\n";
				$wiki_p_output .= "|- class=\"player-profile-stats-header\"\n";
				foreach ($pitching_stats[0] as $key=>$value) {
					$p_output .= "<div class=\"stats-batting-column-header\" id=\"{$key}\">{$key}</div>";
					if($key == "Team") {
						//$wiki_p_output .= "|'''{$key}'''\n";
						//$wiki_p_output .= "|\n";
						$wiki_p_output .= "!{$key}\n";
						$wiki_p_output .= "!\n";
					}
					else {
						//$wiki_p_output .= "|'''{$key}'''\n";
						$wiki_p_output .= "!{$key}\n";
					}
				}
				$p_output .= "<div class=\"cleared\"></div></div>";
				for ($i=0; $i<sizeof($pitching_stats); $i++) {
					//$count = 0;
					if ($i%2 == 1) {
						$div_style = "-alternate";
					}
					else {
						$div_style = "";
					}
					$p_output .= "<div class=\"stats-block-row{$div_style}\">";
					if ($pitching_stats[$i]["Year"] == "Total") {
						 $wiki_p_output .= "|- class=\"player-profile-stats-total\"\n";
					}
					else {
						$wiki_p_output .= "|-\n";
					}
					foreach ($pitching_stats[$i] as $key=>$value) {
						/*
						if (trim($value) == "") {
							$value = "&nbsp;";
						}
						*/
						$p_output .= "<div class=\"stats-batting-column\" id=\"{$key}\">{$value}</div>";
						if ($key =="Team") {
							if (strpos($value, " ")) {
								$split_team = substr($value, 0, strpos($value, " "));
								$split_league = substr($value, strpos($value, " ")+1);
							}
							else {
								$split_team = $value;
								$split_league = "";
							}
						
							$wiki_p_output .= "|{$split_team}\n";
							$wiki_p_output .= "|{$split_league}\n";
							
						}
						else {
							$wiki_p_output .= "|{$value}\n";
						}
					}
					$p_output .= "<div class=\"cleared\"></div></div>";
					//$wiki_p_output .= "|-";
				}
				$p_output .= "</div>";
				$wiki_p_output .= "|}\n";
			}

			$biography = $this->createBiography($player, $batting_stats, $fielding_stats, $pitching_stats, $games, $total_games, $is_pitcher);
			$wiki_output .= $biography;
	
			$wiki_output .= $wiki_mid_output;
			
			if($is_pitcher) {
				$wiki_output .= $wiki_p_output . $wiki_f_output . $wiki_b_output;
				$output .= $p_output . $f_output . $b_output;
			}
			else {
				$wiki_output .= $wiki_b_output . $wiki_f_output . $wiki_p_output;
				$output .= $b_output . $f_output . $p_output;
			}

			
			$sql = "SELECT * from mlb_transactions WHERE player_id = {$player["player_id"]} ORDER BY transaction_id ASC";

			//$conn = baseball_mysql_connect_select();
		
			$result = mysql_query($sql);
			
			$transactions = array();
			
			while($row = mysql_fetch_array($result)) {
				$transactions[] = $row; 
			}
			mysql_free_result($result);

			$output .= "<div class=\"stats-block\" id=\"transactions\">";
			$output .= "<div class=\"stats-block-title\"><h2>Transactions:</h2></div>";
			
			$wiki_output .= "==Transactions==\n";
			for ($i=0; $i<sizeof($transactions); $i++) {
				$output .= "<div class=\"transaction\">{$transactions[$i]["transaction"]}</div>";
				$wiki_output .= "*{$transactions[$i]["transaction"]}\n";
			}
			$wiki_output .="\n";
			$output .= "</div>";

			//mysql_close($conn);
		}
	}
	else {
		
		//mysql_close($conn);

	}
	
	$output .= "</div>";
	$output .= "</div>";
	//return $output;
	
	$wiki_output .= "==Trivia==\n";
	$wiki_output .= "\n";
	$wiki_output .= "{{Player Profile Media}}\n";
	$wiki_output .= "\n";
	$wiki_output .= "==See Also==\n";
	$wiki_output .= "\n";
	$wiki_output .= "\n";
	$wiki_output .= "==Categories==\n";
	$wiki_output .= "\n";	
	$wiki_output .= "{{DEFAULTSORT:".$player["lastname"].", ".$player["firstname"]."}}\n";
	
	$categories = $this->createPlayerCategories($player, $batting_stats, $fielding_stats, $pitching_stats, $games, $total_games);
	
	foreach($categories as $key=>$value) {
		$wiki_output .= "[[Category: {$value}]]\n";
		MLBRosterPages::makeCategory($value);
	}
	
	/*
	-------------------------------------------------
	*/
	$title_string = $player["player_name"];
	$article_text = $wiki_output;
	
	$title = Title::makeTitleSafe( NS_MAIN, $title_string );
	$article = new Article($title);
	$article->doEdit( $article_text, "MLB Player Pages");
	/*
	-------------------------------------------------
	*/
	
	
	return $wiki_output;
	
	


	

}

function createPlayerCategories($player, $batting_stats, $fielding_stats, $pitching_stats, $games, $total_games) {
	
	$position_names = array(
		"P" => "Pitchers",
		"C" => "Catchers",
		"1B" => "First Basemen",
		"2B" => "Second Basemen",
		"3B" => "Third Basemen",
		"SS" => "Shortstops",
		"OF" => "Outfielders",
		"RF" => "Right Fielders",
		"CF" => "Center Fielders",
		"LF" => "Left Fielders",
		"DH" => "Designated Hitters"
	);
	
	$categories = array();
	//Athlete categories
	$categories[] = "Athletes";
	$categories[] = "Baseball Players";
	if ($player["final_game"] != "") {
		$categories[] = "Retired Athletes";
		$categories[] = "Retired Baseball Players";
	}
	// position categories
	foreach ($games as $key=>$value) {
		$categories[] = isset($position_names[$key]) ? $position_names[$key] : $key;
	}
	
	if ($player["lastname"] != "") {
		$categories[] = "Athletes with the Last Name {$player["lastname"]}";
		$categories[] = "Baseball Players with the Last Name {$player["lastname"]}";
	}
	if ($player["firstname"] != "") {
		$categories[] = "Athletes with the First Name {$player["firstname"]}";
		$categories[] = "Baseball Players with the First Name {$player["firstname"]}";
	}
	
	if ($player["birthday"] != "") {
		$birthday = $this->breakDownDate($player["birthday"]);
		if ($birthday["month"]) {
			$categories[] = "Athletes Born in {$birthday["month"]}";
			$categories[] = "Baseball Players Born in {$birthday["month"]}";
			if ($birthday["day"]) {
				$categories[] = "Athletes Born on {$birthday["month"]} {$birthday["day"]}";
				$categories[] = "Baseball Players Born on {$birthday["month"]} {$birthday["day"]}";
			}
		}
		if ($birthday["year"]) {
			$categories[] = "Athletes Born in {$birthday["year"]}";
			$categories[] = "Baseball Players Born in {$birthday["year"]}";
			if ($birthday["month"]) {
				$categories[] = "Athletes Born in {$birthday["month"]} {$birthday["year"]}";
				$categories[] = "Baseball Players Born in {$birthday["month"]} {$birthday["year"]}";
					if ($birthday["day"]) {
						$categories[] = "Athletes Born on {$birthday["month"]} {$birthday["day"]}, {$birthday["year"]}";
						$categories[] = "Baseball Players Born on {$birthday["month"]} {$birthday["day"]}, {$birthday["year"]}";
					}
			}
	
		}
	}
	
	if ($player["birthplace"] != "") {
		
		$categories[] = "Athletes Born in {$player["birthplace"]}";
		$categories[] = "Baseball Players Born in {$player["birthplace"]}";

		$start = strpos($player["birthplace"], ", ");
		if ($start) {
			$state = substr($player["birthplace"], $start+ strlen(", "));
			$categories[] = "Athletes Born in {$state}";
			$categories[] = "Baseball Players Born in {$state}";
		}
		
	}
	
	//---------------
	/*
	Need to doteam translations
	*/
	//---------------
	$teams = array();
	$teams_years = array ();
	$count = 0;
	$first_team;
	foreach ($fielding_stats as $key=>$value) {
		if (strtolower(substr($value["Team"], 0, 3)) != "tot" && strtolower(substr($value["Year"], 0, 3)) != "tot") {
			if($count == 0)	$first_team = MLBStats::get_team_name($value["Team"], $value["Year"]);
			
			$teams[$value["Team"]] = MLBStats::get_team_name($value["Team"], $value["Year"]);
			$teams_years[$value["Year"]."_".$value["Team"]] = $value["Year"]." ".MLBStats::get_team_name($value["Team"], $value["Year"]);
			$count++;
		}
	}
	
	foreach ($teams as $key=>$value) {
		$categories[] = "{$value} Players";
	}
	foreach ($teams_years as $key=>$value) {
		$categories[] = "{$value} Players";
	}
	
	/*
	if Hitter -->
	[[Category: Hitters Who Bat <<Left/Right>>]]
	[[Category: Hitters Who Throw <<Left/Right>>]]
	
	if Pitcher -->
	[[Category: Pitchers Who Bat <<Left/Right>>]]
	[[Category: Pitchers Who Throw <<Left/Right>>]]
	
	*/
	
	if ($player["bats"] != "" && $player["bats"]!= "Unknown") {
		if ($player["bats"] == "Both") {
			$categories[] = "Baseball Players who switch hit.";
		}
		else {
			$categories[] = "Baseball Players who bat {$player["bats"]} Handed";
		}
	}

	if ($player["throws"]!= "" && $player["throws"]!= "Unknown") $categories[] = "Baseball Players who throw {$player["throws"]} Handed";
	
	/*
	if Hitter -->
	[[Category: Hitters Who Bat <<Left/Right>>]]
	[[Category: Hitters Who Throw <<Left/Right>>]]
	
	if Pitcher -->
	[[Category: Pitchers Who Bat <<Left/Right>>]]
	[[Category: Pitchers Who Throw <<Left/Right>>]]
	
	*/
	
	if ($player["first_game"] != "") {
		$firstgame = $this->breakDownDate($player["first_game"]);
		if ($firstgame["month"]) {
			$categories[] = "Athletes Who Debuted in {$firstgame["month"]}";
			$categories[] = "Baseball Players Who Debuted in {$firstgame["month"]}";
			if ($firstgame["day"]) {
				$categories[] = "Athletes Who Debuted on {$firstgame["month"]} {$firstgame["day"]}";
				$categories[] = "Baseball Players Who Debuted on {$firstgame["month"]} {$firstgame["day"]}";
			}
		}
		if ($firstgame["year"]) {
			$categories[] = "Athletes Who Debuted in {$firstgame["year"]}";
			$categories[] = "Baseball Players Who Debuted in {$firstgame["year"]}";
			if ($firstgame["month"]) {
				$categories[] = "Athletes Who Debuted in {$firstgame["month"]} {$firstgame["year"]}";
				$categories[] = "Baseball Players Who Debuted in {$firstgame["month"]} {$firstgame["year"]}";
					if ($firstgame["day"]) {
						$categories[] = "Athletes Who Debuted on {$firstgame["month"]} {$firstgame["day"]}, {$firstgame["year"]}";
						$categories[] = "Baseball Players Who Debuted on {$firstgame["month"]} {$firstgame["day"]}, {$firstgame["year"]}";
					}
			}
	
		}
	}
	
	if ($player["final_game"] != "") {
		$finalgame = $this->breakDownDate($player["final_game"]);
		if ($finalgame["month"]) {
			$categories[] = "Athletes Who Played Their Last Game in {$finalgame["month"]}";
			$categories[] = "Baseball Players Who Played Their Last Game in {$finalgame["month"]}";
			if ($finalgame["day"]) {
				$categories[] = "Athletes Who Played Their Last Game on {$finalgame["month"]} {$finalgame["day"]}";
				$categories[] = "Baseball Players Who Played Their Last Game on {$finalgame["month"]} {$finalgame["day"]}";
			}
		}
		if ($finalgame["year"]) {
			$categories[] = "Athletes Who Played Their Last Game in {$finalgame["year"]}";
			$categories[] = "Baseball Players Who Played Their Last Game in {$finalgame["year"]}";
			if ($finalgame["month"]) {
				$categories[] = "Athletes Who Played Their Last Game in {$finalgame["month"]} {$finalgame["year"]}";
				$categories[] = "Baseball Players Who Played Their Last Game in {$finalgame["month"]} {$finalgame["year"]}";
					if ($finalgame["day"]) {
						$categories[] = "Athletes Who Played Their Last Game on {$finalgame["month"]} {$finalgame["day"]}, {$finalgame["year"]}";
						$categories[] = "Baseball Players Who Played Their Last Game on {$finalgame["month"]} {$finalgame["day"]}, {$finalgame["year"]}";
					}
			}
	
		}
	}

	
	/*
	[[Category: Baseball Players Who Debuted with <<Team>>]]
	[[Category: Baseball Players Who Debuted at age <<Age they debuted>>]]
	*/
	
	$categories[] = "Baseball Players Who Debuted with {$first_team}";
	
	$experience_criteria = array(
		"mlb_experience"=>array(5,10,15,20,25,30)
	);
	
	foreach ($experience_criteria as $field=>$nums) {
		foreach($nums as $key=>$value) {
			if ($player[$field] >= $value) {
				$categories[] = "Players with {$value} years experience in the Major Leagues";
				$categories[] = "Baseball Players with {$value} years experience in the Major Leagues";
			}
		}
	}
	
	if (sizeof($batting_stats) > 0) {
		
		$bat_cat_text = array(
			"AB"=>"At Bats",
			"G"=>"Games",
			"R"=>"Runs Scored",
			"H"=>"Hits",
			"1B"=>"Singles",
			"2B"=>"Doubles",
			"3B"=>"Triples",
			"HR"=>"Home Runs",
			"RBI"=>"RBI",
			"SB"=>"Stolen Bases",
			"CS"=>"Times Caught Stealing",
			"BB"=>"Walks",
			"HBP"=>"Times Hit By Pitch",
			"GDP"=>"Times Grounding Into a Double Play",
			"SO"=>"Strikeouts"
		);
		$batting_criteria = array(
			"AB"=>array(500,1000,1500,2000,2500,3000,3500,4000,5000,7500,10000),
			"G"=>array(100,200,300,400,500,750,1000,1500,2000,2500,3000),
			"R"=>array(100,200,300,400,500,750,1000,1500,2000),
			"H"=>array(100,200,300,400,500,750,1000,1500,2000,2500,3000,3500,4000),
			"1B"=>array(100,200,300,400,500,750,1000,1500,2000,2500,3000),
			"2B"=>array(100,200,300,400,500,600,700),
			"3B"=>array(100,200,300),
			"HR"=>array(100,200,300,400,500,600,700,800),
			"RBI"=>array(100,200,300,400,500,750,1000,1500,2000),
			"SB"=>array(100,200,300,400,500,750,1000),
			"CS"=>array(100,200,300),
			"BB"=>array(100,200,300,400,500,750,1000,1500,2000,2500),
			"HBP"=>array(50,100,150,200,250),
			"GDP"=>array(50,100,150,200,250),
			"SO"=>array(100,200,300,400,500,750,1000,1500,2000)
		);
		
		$batting_stats_test = $batting_stats[sizeof($batting_stats)-1];
		$batting_stats_test["1B"] = $batting_stats_test["H"] - ($batting_stats_test["2B"] + $batting_stats_test["3B"] + $batting_stats_test["HR"]);
		
		foreach ($batting_criteria as $field=>$nums) {
			foreach($nums as $key=>$value) {
				if ($batting_stats_test[$field] >= $value) {
					$categories[] = "Players with at least {$value} {$bat_cat_text[$field]}";
					//$categories[] = "Baseball Players with {$key} {$value} in the Major Leagues";
				}
			}
		}
	}
	
	if (sizeof($pitching_stats) > 0) {
		$pitch_cat_text = array(
			"W"=>"Wins",
			"L"=>"Losses",
			"SO"=>"Strikeouts",
			"SHO"=>"Shutouts",
			"SV"=>"Saves",
			"HR"=>"Home Runs Allowed",
			"CG"=>"Complete Games",
			"BB"=>"Walks",
			"GS"=>"Games Started",
			"IP"=>"Innings Pitched",
			"WP"=>"Wild Pitches",
			"HBP"=>"Hit Batsman",
			"ER"=>"Earned Runs Allowed",
			"R"=>"Runs Allowed",
			"H"=>"Hits Allowed",
			"G"=>"Games"
		);
	
		$pitching_criteria = array(
			"W"=>array(50,100,150,200,250,300,400,500),			
			"L"=>array(50,100,150,200,250,300,400,500),
			"SO"=>array(100,200,500,1000,1500,2000,3000,4000,5000),
			"SHO"=>array(5,10,20,30,40,50,60,75,100),
			"SV"=>array(50,100,150,200,250,300,400,500),
			"HR"=>array(50,100,150,200,250,300,400,500),
			"CG"=>array(25,50,100,150,200,250,300,400,500,600,700),
			"BB"=>array(100,200,300,400,500,750,1000,1500,2000,2500),
			"GS"=>array(50,100,150,200,250,300,400,500,600,700,800),
			"IP"=>array(162,250,500,1000,1500,2000,3000,4000,5000,6000,7000),
			"WP"=>array(25,50,100,150,200,250,300),
			"HBP"=>array(25,50,100,150,200),
			"ER"=>array(100,200,500,1000,1500,2000),
			"R"=>array(100,200,500,1000,1500,2000,2500),
			"H"=>array(500,1000,1500,2000,3000,4000,5000,6000,7000),
			"G"=>array(50,100,150,200,250,300,400,500,600,700,800,900,1000)
		);
		
		$pitching_stats_test = $pitching_stats[sizeof($pitching_stats)-1];
		
		
		foreach ($pitching_criteria as $field=>$nums) {
			foreach($nums as $key=>$value) {
				if ($pitching_stats_test[$field] >= $value) {
					$categories[] = "Pitchers with at least {$value} {$pitch_cat_text[$field]}";
				}
			}
		}
	}

	$debut_age = $this->determineAge($player["birthday"], $player["first_game"]);
	if ($debut_age) {
		$categories[] = "Baseball Players Who Debuted at age ". floor($debut_age);
	}
	
	return $categories;	
}


function breakDownDate($date) {
	
	$month = false;
	$day = false;
	$year = false;
	
	$start = 0;
	$end = strpos($date, " ");
	if ($end) {
		$month = substr($date, $start, $end);
		$date = substr($date, $end+strlen(" "));
		$end = strpos($date, ", ");
		if ($end) {
			$day = substr($date, $start, $end);
			$date = substr($date, $end+strlen(", "));
			$end = strlen($date);
			if ($end) {
				$year = $date;
			}
		}
	}
	
	$return_vals = array(
		"month"=>$month,
		"day"=>$day,
		"year"=>$year
	);
	
	return $return_vals;
}

function determineAge($start, $end) {
	$stats_months = array(
	"January"=>"01",
	"February"=>"02",
	"March"=>"03",
	"April"=>"04",
	"May"=>"05",
	"June"=>"06",
	"July"=>"07",
	"August"=>"08",
	"September"=>"09",
	"October"=>"10",
	"November"=>"11",
	"December"=>"12"
	);
	
	$start_date = $this->breakDownDate($start);
	if ($end) {
		$end_date = $this->breakDownDate($end);
	}
	else {
		$end_date = array(
			"month"=>date("F"),
			"day"=>date("d"),
			"year"=>date("Y")
		);
	}
	
	if ($start_date["month"] && $start_date["day"] && $start_date["year"] && $end_date["month"] && $end_date["day"] && $end_date["year"]) {
		$start_date["month"] = $stats_months[$start_date["month"]];
		if (strlen($start_date["day"]) == 1) $start_date["day"] = "0{$start_date["day"]}"; 
		$end_date["month"] = $stats_months[$end_date["month"]];
		if (strlen($end_date["day"]) == 1) $end_date["day"] = "0{$end_date["day"]}";
		
		if ($end_date["year"]<1900 || $start_date["year"]<1900) {
			$start_date["year"] += 50;
			$end_date["year"] += 50;
		}
		
		$start_time = mktime(0, 0, 0, $start_date["month"], $start_date["day"], $start_date["year"]);
		$end_time = mktime(0, 0, 0, $end_date["month"], $end_date["day"], $end_date["year"]);
		
		$diff = $end_time - $start_time;
		
		$oneyear = 60*60*24*365;
		
		$timediff = $diff/$oneyear;
	}
	else {
		$timediff = false;
	}
	
	return $timediff;
}
/*
function translateTeam($team_abbr) {
	global $team_translations;
	
	
	if (isset($team_translations[$team_abbr]) &&  $team_translations[$team_abbr] != "") {
		return $team_translations[$team_abbr];
	}
	
	return $team_abbr;
}
*/

function createBiography($player, $batting_stats, $fielding_stats, $pitching_stats, $games, $total_games, $is_pitcher) {
	$player_teams = array();
	$teams = "";
	$num_teams = 0;
	foreach($fielding_stats as $year=>$stats) {
		if ($stats["Team"] != "" && substr(strtolower($stats["Team"]), 0, 3) != "tot") { 
			/*
			if (!isset($player_teams[$stats["Team"]])) {
				$player_teams[$stats["Team"]] = 1;
				$teams .= "[[" . $this->translateTeam($stats["Team"]) . "]]   ";
				$num_teams++;
			}
			*/
			$team_temp = MLBStats::get_team_name($stats["Team"], $stats["Year"]);
			if (!isset($player_teams[$team_temp])) {
				$player_teams[$team_temp] = 1;
				$teams .= "[[" . $team_temp . "]]   ";
				$num_teams++;
			}

			
		}	
	}
	
	$teams = str_replace("   ", ", ", trim($teams));
	if (strpos(strrev($teams), " ,")) {
		$teams = strrev(substr(strrev($teams), 0, strpos(strrev($teams), " ,")) . " dna " . substr(strrev($teams), strpos(strrev($teams), " ,") + strlen(" ,")));
	}

	
	$output = "";
	
	if (!$is_pitcher) {
		
		$batting_weights = array(
		0=>array(
			"AB"=>.05,
			"G"=>.05,
			"R"=>.15,
			"AVG"=>60,
			"2B"=>.35,
			"3B"=>.3,
			"HR"=>.75,
			"RBI"=>.2,
			"SB"=>.2,
			"CS"=>-.1,
			"BB"=>.05,
			"HBP"=>.01,
			"GDP"=>-.25,
			"SO"=>-.05
		),
		1=>array(
			"AB"=>.05,
			"G"=>.05,
			"R"=>.15,
			"AVG"=>60,
			"2B"=>.2,
			"3B"=>.4,
			"HR"=>.5,
			"RBI"=>.2,
			"SB"=>1,
			"CS"=>-.1,
			"BB"=>.05,
			"HBP"=>.01,
			"GDP"=>-.25,
			"SO"=>-.05
		),
		2=>array(
			"AB"=>.05,
			"G"=>.05,
			"R"=>.15,
			"AVG"=>85,
			"2B"=>.25,
			"3B"=>.25,
			"HR"=>.5,
			"RBI"=>.2,
			"SB"=>.2,
			"CS"=>-.1,
			"BB"=>.05,
			"HBP"=>.01,
			"GDP"=>-.25,
			"SO"=>-.05
		)
		);

		
		$high = array(0,0,0);
		$year = array(0,0,0);
		$total = array(0,0,0);
		$year_key = array(0,0,0);
		
		//$rookie_criteria = array("AB"=>130);
		$rookie_year = 0;
		$rookie_year_key = 0;
	
		$last_year = array(0,0,0);
		//for ($i=0; $i<sizeof($batting_stats); $i++) {
		for($j=0; $j<sizeof($batting_weights); $j++) {
			for ($i=sizeof($batting_stats)-1; $i>=0; $i--) {
				if ($batting_stats[$i]["Year"] != $last_year[$j] &&$batting_stats[$i]["Year"] != "Total") {
					//$output .= "{$pitching_stats[$i]["Year"]},";
					if ($batting_stats[$i]["AB"] >= 130) {
						$rookie_year = $batting_stats[$i]["Year"];
						$rookie_year_key = $i;
						
					
						$total[$j] = 0;
						$last_year[$j] = $batting_stats[$i]["Year"];
						foreach($batting_weights[$j] as $key=>$value) {
							$total[$j] += $batting_stats[$i][$key]*$value;
						}
						if ($total[$j] > $high[$j]) {
							$high[$j] = $total[$j];
							$year[$j] = $last_year[$j];
							$year_key[$j] = $i;
						}
					}
				}
				
			}
			//$output .= "------{$high[$j]}:{$year[$j]}:{$year_key[$j]}------";		

		}
		
		
		$output .= "'''{$player["player_name"]}''' ({$player["player_fullname"]}) was born on {$player["birthday"]} in {$player["birthplace"]}. ";
		$output .= "He made his Major League debut on {$player["first_game"]}";
		if (isset($fielding_stats[0]["Team"])) {
			$output .= " for the [[" . MLBStats::get_team_name($fielding_stats[0]["Team"], $fielding_stats[0]["Year"]) . "]]. ";
		}
		else {
			$output .= ". ";
		}
		if ($rookie_year > 0) {
			$output .= "In [[{$rookie_year} Major League Baseball|{$rookie_year}]], his rookie year, he hit {$batting_stats[$rookie_year_key]["AVG"]} with {$batting_stats[$rookie_year_key]["HR"]} ";
			if ($batting_stats[$rookie_year_key]["HR"] != 1) {
				$output .= "home runs ";
			}
			else {
				$output .= "home run ";
			}
			$output .= "and {$batting_stats[$rookie_year_key]["RBI"]} RBI. ";
		}
		if ($num_teams > 1) {
			$output .= "{$player["lastname"]} played for the {$teams} over the course of his {$player["mlb_experience"]} year career.\n";
		}
		else if ($num_teams == 1) {
			$output .= "{$player["lastname"]} played for the {$teams} for his entire {$player["mlb_experience"]} year career.\n";
		}
		
		if ($player["mlb_experience"] > 3) {
			$unique_years = array();
			for($j=0; $j<sizeof($year_key); $j++) {
				//if (!isset($unique_years[$year_key[$j]]) && $year_key[$j] > 0) {
				if (!isset($unique_years[$year_key[$j]]) && $year[$j] > 0) {
					$unique_years[$year_key[$j]] = 1;
				}
				else if (isset($unique_years[$year_key[$j]]) && $year[$j] > 0){
					$unique_years[$year_key[$j]] += 1;
				}
			}
			$best_year_text = array();
			foreach($unique_years as $j=>$chosen) {

				$stat_display_criteria = array(
					"HR"=>15,
					"SB"=>25,
					"AVG"=>.275,
					"RBI"=>50
				);
				
				$hr_display = ($batting_stats[$j]["HR"] >= $stat_display_criteria["HR"]) ? "slugged {$batting_stats[$j]["HR"]} home runs   " : "";
				$sb_display = ($batting_stats[$j]["SB"] >= $stat_display_criteria["SB"]) ? "stole {$batting_stats[$j]["SB"]} bases   " : "";
				$avg_display = ($batting_stats[$j]["AVG"] >= $stat_display_criteria["AVG"]) ? "hit for a {$batting_stats[$j]["AVG"]} average   " : "";
				$rbi_display = ($batting_stats[$j]["RBI"] >= $stat_display_criteria["RBI"]) ? "knocked in {$batting_stats[$j]["RBI"]} runs   " : "";
				
				//$output .= "\n\nIn [[{$batting_stats[$j]["Year"]}]], arguably his best year, {$player["player_name"]} slugged {$batting_stats[$j]["HR"]} homers and knocked in {$batting_stats[$j]["RBI"]} runs while putting up a {$batting_stats[$j]["AVG"]} batting average.";
				//$output .= "\n\nIn [[{$batting_stats[$j]["Year"]}]], arguably his best year, {$player["player_name"]} ";
				
				//$stats_display_intro = "[[{$batting_stats[$j]["Year"]} Major League Baseball|{$batting_stats[$j]["Year"]}]], when {$player["player_name"]}  ";
				$stats_display_intro = "[[{$batting_stats[$j]["Year"]} Major League Baseball|{$batting_stats[$j]["Year"]}]], when he  ";
				$stats_display_string = $hr_display . $sb_display . $avg_display . $rbi_display;
				
				if ($stats_display_string != "") {
					
					$stats_display_string = str_replace("   ", ", ", trim($stats_display_string));
					
					//$stats_display_string = strrev(str_replace(" ,", " dna ", strrev($stats_display_string)));
					//$stats_display_string = strrev(substr(strrev($stats_display_string), 0, strpos(strrev($stats_display_string), " ,")) . " dna " . substr(strrev($stats_display_string), strpos(strrev($stats_display_string), " ,") + strlen(" ,")));
					if (strpos(strrev($stats_display_string), " ,")) {
						$stats_display_string = strrev(substr(strrev($stats_display_string), 0, strpos(strrev($stats_display_string), " ,")) . " dna " . substr(strrev($stats_display_string), strpos(strrev($stats_display_string), " ,") + strlen(" ,")));
					}

					//$output .= "{$stats_display_string}.";
					$best_year_text[$batting_stats[$j]["Year"]] = $stats_display_intro . $stats_display_string;
				}
			}
			$count = 0;
			foreach($best_year_text as $best_year=>$best_text) {
				if ($count==0 && sizeof($best_year_text) > 1) {
					$output .= "\nThere is some disagreement on what was {$player["player_name"]}'s most productive season.  ";
					$output .= "Some believe that it was {$best_text}.  ";
				}
				else if ($count==0 && sizeof($best_year_text)==1) {
					$output .= "\nMost people believe that {$player["player_name"]}'s best season was {$best_text}.  ";
				}
				else if ($count==1 && $count < sizeof($best_year_text)) {
					$output .= "However, others believe that it was {$best_text}.  ";
				}
				else if ($count==2) {
					$output .= "Another season that some believe was {$player["player_name"]}'s best was {$best_text}.  ";
				}
				$count++;
			}
		}
	}
	else {
		$pitching_weights = array(
		0=>array(
			"G"=>.1,
			"GS"=>.1,
			"W"=>2,
			"L"=>-.3,
			"ERA"=>-5,
			"K"=>.15,
			"SV"=>1.25,
			"IP"=>.025,
			"BB"=>-.1
		),
		1=>array(
			"G"=>.15,
			"GS"=>.15,
			"W"=>1.25,
			"L"=>-.25,
			"ERA"=>-15,
			"K"=>.1,
			"SV"=>.5,
			"IP"=>.02,
			"BB"=>-.05
		),
		2=>array(
			"G"=>.1,
			"GS"=>.1,
			"W"=>1,
			"L"=>-.75,
			"ERA"=>-4,
			"K"=>.25,
			"SV"=>.75,
			"IP"=>.025,
			"BB"=>-.2
		)
		);

		
		$high = array(0,0,0);
		$year = array(0,0,0);
		$total = array(0,0,0);
		$year_key = array(0,0,0);
		
		//$rookie_criteria = array("AB"=>130);
		$rookie_year = 0;
		$rookie_year_key = 0;
	
		$last_year = array(0,0,0);
		//for ($i=0; $i<sizeof($batting_stats); $i++) {
		for($j=0; $j<sizeof($pitching_weights); $j++) {
			for ($i=sizeof($pitching_stats)-1; $i>=0; $i--) {
				if ($pitching_stats[$i]["Year"] != $last_year[$j] &&$pitching_stats[$i]["Year"] != "Total") {
					//$output .= "{$pitching_stats[$i]["Year"]},";
					if ($pitching_stats[$i]["IP"] >= 50) {
						$rookie_year = $pitching_stats[$i]["Year"];
						$rookie_year_key = $i;
					
					
					
						$total[$j] = 0;
						$last_year[$j] = $pitching_stats[$i]["Year"];
						foreach($pitching_weights[$j] as $key=>$value) {
							$total[$j] += $pitching_stats[$i][$key]*$value;
						}
						if ($total[$j] > $high[$j]) {
							$high[$j] = $total[$j];
							$year[$j] = $last_year[$j];
							$year_key[$j] = $i;
						}
					}
				}
				
			}
			//$output .= "------{$high[$j]}:{$year[$j]}:{$year_key[$j]}------";		

		}
		
		
		$output .= "Born on {$player["birthday"]} in {$player["birthplace"]}, '''{$player["player_name"]}''' ({$player["player_fullname"]}) ";
		$output .= "played for the {$teams} ";
		$output .= "over the course of his {$player["mlb_experience"]} year career.  ";
		$output .= "{$player["lastname"]} broke into the bigs on {$player["first_game"]}";
		if (isset($fielding_stats[0]["Team"])) {
			$output .= " with the [[" . MLBStats::get_team_name($fielding_stats[0]["Team"], $fielding_stats[0]["Year"]) . "]]";
		}
		/*
		else {
			$output .= ", ";
		}
		*/
		if ($rookie_year > 0) {
			$output .= ", and put up a {$pitching_stats[$rookie_year_key]["ERA"]} ERA in {$pitching_stats[$rookie_year_key]["IP"]} innings pitched in {$rookie_year}, his rookie year.\n";
		}
		else {
			$output .= ".\n";
		}
		if ($player["mlb_experience"] > 3) {
			$unique_years = array();
			for($j=0; $j<sizeof($year_key); $j++) {
				//if (!isset($unique_years[$year_key[$j]]) && $year_key[$j] > 0) {
				if (!isset($unique_years[$year_key[$j]]) && $year[$j] > 0) {
					$unique_years[$year_key[$j]] = 1;
				}
				else if (isset($unique_years[$year_key[$j]]) && $year[$j] > 0){
					$unique_years[$year_key[$j]] += 1;
				}
			}

			//$count = 0;
			foreach($unique_years as $j=>$chosen) {
				//if ($pitching_stats[$j]["GS"]/$pitching_stats[$j]["G"] >= .75) {
					
									
					$stat_display_criteria = array(
						"ERA"=>5.00,
						"W"=>10,
						"SV"=>10,
						"K"=>50
					);
					
					$era_display = ($pitching_stats[$j]["ERA"] <= $stat_display_criteria["ERA"]) ? "posted a {$pitching_stats[$j]["ERA"]} ERA   " : "";
					$w_display = ($pitching_stats[$j]["W"] >= $stat_display_criteria["W"]) ? "won {$pitching_stats[$j]["W"]} games   " : "";
					$s_display = ($pitching_stats[$j]["SV"] >= $stat_display_criteria["SV"]) ? "notched {$pitching_stats[$j]["SV"]} saves   " : "";
					$k_display = ($pitching_stats[$j]["K"] >= $stat_display_criteria["K"]) ? "struck out {$pitching_stats[$j]["K"]} batters   " : "";
					
					//$stats_display_intro = "[[{$pitching_stats[$j]["Year"]} Major League Baseball|{$pitching_stats[$j]["Year"]}]], when {$player["player_name"]} ";
					$stats_display_intro = "[[{$pitching_stats[$j]["Year"]} Major League Baseball|{$pitching_stats[$j]["Year"]}]], when he ";
					$stats_display_string = $era_display . $w_display . $s_display . $k_display;

					if ($stats_display_string != "") {
					
						$stats_display_string = str_replace("   ", ", ", trim($stats_display_string));
						
						//$stats_display_string = strrev(str_replace(" ,", " dna ", strrev($stats_display_string)));
						if (strpos(strrev($stats_display_string), " ,")) {
							$stats_display_string = strrev(substr(strrev($stats_display_string), 0, strpos(strrev($stats_display_string), " ,")) . " dna " . substr(strrev($stats_display_string), strpos(strrev($stats_display_string), " ,") + strlen(" ,")));
						}
						//$output .= "{$stats_display_string}.";
						$best_year_text[$pitching_stats[$j]["Year"]] = $stats_display_intro . $stats_display_string;
					}
				//}
			}
				
			$count = 0;
			foreach($best_year_text as $best_year=>$best_text) {
				if ($count==0 && sizeof($best_year_text) > 1) {
					$output .= "\nThere is some disagreement on what was {$player["player_name"]}'s most productive season.  ";
					$output .= "Some believe that it was {$best_text}.  ";
				}
				else if ($count==0 && sizeof($best_year_text)==1) {
					$output .= "\nMost people believe that {$player["player_name"]}'s best season was {$best_text}.  ";
				}
				else if ($count==1 && $count < sizeof($best_year_text)) {
					$output .= "However, others believe that it was {$best_text}.  ";
				}
				else if ($count==2) {
					$output .= "Another season that some believe was {$player["player_name"]}'s best was {$best_text}.  ";
				}
				$count++;
			}

		}
	}
	
	return $output;
}


}

SpecialPage::addPage( new MLBPlayerSearch );



}

?>
