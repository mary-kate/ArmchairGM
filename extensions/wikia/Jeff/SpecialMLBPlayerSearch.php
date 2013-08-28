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
		//if ($value && strlen($value) == 1) {
		if ($value && $value == "ErrorUpdate") {
			set_time_limit(0);
			$start = time();
			$output = "";
			$complete_output = "Complete Edits<br/>";
			$outsider_output = "Skipped because edited by outsider<br/>";
			$ambiguous_output = "Skipped because of ambiguity (or outsider)<br/>";
			$complete_output_wiki = "Complete Edits<br/>";
			$outsider_output_wiki = "Skipped because edited by outsider<br/>";
			$wgUser = User::newFromName( "MLB Stats Bot" );
			$wgUser->addGroup( 'bot' );
			
			$dbr =& wfGetDB( DB_MASTER );

			
			//$sql = "SELECT player_name, count(player_name) as numbers, firstname, lastname, player_id FROM mlb_players_info WHERE lastname like '{$value}%' group by player_name order by lastname ASC, firstname ASC";
			//$conn = baseball_mysql_connect_select();
			
			//$result = mysql_query($sql);

			//while($row = mysql_fetch_array($result)) {
			$players_to_update = $this->return_dupe_list();
			foreach ($players_to_update as $key=>$row) {	
				$page_title = $row["player_name"];
				$numbers = $row["numbers"];
				if (intval($numbers) == 1) {
					$do_edit = true;
					$title = Title::makeTitleSafe( NS_MAIN, $page_title );
					$db_key = $title->getDBKey();
					$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key ),"" );
					if ( $s !== false ) {
						$edits = MLBRosterPages::check_edits($s->page_id);
					
						if($edits["good"] == false){
							$do_edit = false;
						}
			
					} 
					if( $do_edit){
						//$article_text = $this->get_player_stats($row["firstname"], $row["lastname"], false);
						$article_text = $this->get_player_stats($row["firstname"], $row["lastname"], $row["player_id"]);
						$article = new Article($title);
						$article->doEdit( $article_text, "MLB Player Pages", EDIT_SUPPRESS_RC);
						$complete_output .= "edit of <b>{$page_title}</b> complete. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
						$complete_output_wiki .= "edit of <b>{$page_title}</b> complete. [[{$page_title}]]<br>";
						sleep(2);
					}else{
						//$skipped .= "[[{$page_title}]]\n";
						$outsider_output .= "skipped <b>{$page_title}</b> because edited by outsider. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
						$outsider_output_wiki .= "skipped <b>{$page_title}</b> because edited by outsider. [[{$page_title}]]<br>";
					}
				}
				else {
					$ambiguous_output .= "skipped <b>{$page_title}</b> because name is ambiguous. ({$numbers} people)<br/>";
				}
				
			}
			//mysql_free_result($result);
			$end = time();
			$output = "{$outsider_output} <br> {$ambiguous_output} <br> {$complete_output} <br><br>took " . ($end-$start) . " seconds";
			$output_wiki = "{$outsider_output_wiki} <br> {$ambiguous_output} <br> {$complete_output_wiki} <br><br>took " . ($end-$start) . " seconds";
			$title = "MLB Stats Bot player generator report for {$value}";
			$summary_title = Title::makeTitleSafe( NS_MAIN, $title );

			$summary_article_text = $output_wiki;
			$summary_article = new Article($summary_title);
			$summary_article->doEdit($summary_article_text, "MLB Stat Bot Reports", EDIT_SUPPRESS_RC);


			$wgOut->setPageTitle($title);
			$wgOut->addHTML($output);
			
		}
		/*
		
		else {
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
		}
		
		*/

		
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
	//$wiki_output .= "{{DEFAULTSORT:".$player["lastname"].", ".$player["firstname"]."}}\n";
	$wiki_output .= "{{DEFAULTSORT:".$last_name.", ".$first_name."}}\n";
	
	$categories = $this->createPlayerCategories($player, $batting_stats, $fielding_stats, $pitching_stats, $games, $total_games);
	
	foreach($categories as $key=>$value) {
		$wiki_output .= "[[Category: {$value}]]\n";
		MLBRosterPages::makeCategory($value);
		sleep(2);
	}
	
	/*
	-------------------------------------------------
	
	$title_string = $player["player_name"];
	$article_text = $wiki_output;
	
	$title = Title::makeTitleSafe( NS_MAIN, $title_string );
	$article = new Article($title);
	$article->doEdit( $article_text, "MLB Player Pages");
	
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
		if ($num_teams > 1) {
			$output .= " over the course of his {$player["mlb_experience"]} year career.  ";
		}
		else if ($num_teams == 1) {
			$output .= "for his entire {$player["mlb_experience"]} year career.  ";
		}

		//$output .= "over the course of his {$player["mlb_experience"]} year career.  ";
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

function return_dupe_list() {

	$players_to_update = array();
	
	$players_to_update[] = array(player_id=>2540,firstname=>"Al",lastname=>"Aber",player_name=>"Al Aber",numbers=>1);
$players_to_update[] = array(player_id=>2586,firstname=>"Joe",lastname=>"Adams",player_name=>"Joe Adams",numbers=>1);
$players_to_update[] = array(player_id=>2591,firstname=>"Rick",lastname=>"Adams",player_name=>"Rick Adams",numbers=>1);
$players_to_update[] = array(player_id=>2668,firstname=>"Matt",lastname=>"Alexander",player_name=>"Matt Alexander",numbers=>1);
$players_to_update[] = array(player_id=>2670,firstname=>"Pete",lastname=>"Alexander",player_name=>"Pete Alexander",numbers=>1);
$players_to_update[] = array(player_id=>2687,firstname=>"Ethan",lastname=>"Allen",player_name=>"Ethan Allen",numbers=>1);
$players_to_update[] = array(player_id=>2688,firstname=>"Frank",lastname=>"Allen",player_name=>"Frank Allen",numbers=>1);
$players_to_update[] = array(player_id=>2695,firstname=>"John",lastname=>"Allen",player_name=>"John Allen",numbers=>1);
$players_to_update[] = array(player_id=>2705,firstname=>"Ron",lastname=>"Allen",player_name=>"Ron Allen",numbers=>1);
$players_to_update[] = array(player_id=>2798,firstname=>"George",lastname=>"Anderson",player_name=>"George Anderson",numbers=>1);
$players_to_update[] = array(player_id=>2803,firstname=>"Jim",lastname=>"Anderson",player_name=>"Jim Anderson",numbers=>1);
$players_to_update[] = array(player_id=>2819,firstname=>"Walter",lastname=>"Anderson",player_name=>"Walter Anderson",numbers=>1);
$players_to_update[] = array(player_id=>2823,firstname=>"John",lastname=>"Andre",player_name=>"John Andre",numbers=>1);
$players_to_update[] = array(player_id=>2853,firstname=>"John",lastname=>"Antonelli",player_name=>"John Antonelli",numbers=>1);
$players_to_update[] = array(player_id=>2867,firstname=>"Jack",lastname=>"Aragon",player_name=>"Jack Aragon",numbers=>1);
$players_to_update[] = array(player_id=>2870,firstname=>"Jim",lastname=>"Archer",player_name=>"Jim Archer",numbers=>1);
$players_to_update[] = array(player_id=>2901,firstname=>"Billy",lastname=>"Arnold",player_name=>"Billy Arnold",numbers=>1);
$players_to_update[] = array(player_id=>2946,firstname=>"Lefty",lastname=>"Atkinson",player_name=>"Lefty Atkinson",numbers=>1);
$players_to_update[] = array(player_id=>2966,firstname=>"Jeff",lastname=>"Austin",player_name=>"Jeff Austin",numbers=>1);
$players_to_update[] = array(player_id=>2967,firstname=>"Jim",lastname=>"Austin",player_name=>"Jim Austin",numbers=>1);
$players_to_update[] = array(player_id=>3004,firstname=>"Michael",lastname=>"Bacsik",player_name=>"Michael Bacsik",numbers=>1);
$players_to_update[] = array(player_id=>3046,firstname=>"Doug",lastname=>"Bair",player_name=>"Doug Bair",numbers=>1);
$players_to_update[] = array(player_id=>3109,firstname=>"Jeff",lastname=>"Ball",player_name=>"Jeff Ball",numbers=>1);
$players_to_update[] = array(player_id=>3126,firstname=>"Jeff",lastname=>"Banister",player_name=>"Jeff Banister",numbers=>1);
$players_to_update[] = array(player_id=>3129,firstname=>"Bill",lastname=>"Banks",player_name=>"Bill Banks",numbers=>1);
$players_to_update[] = array(player_id=>3145,firstname=>"Red",lastname=>"Barbary",player_name=>"Red Barbary",numbers=>1);
$players_to_update[] = array(player_id=>3158,firstname=>"George",lastname=>"Barclay",player_name=>"George Barclay",numbers=>1);
$players_to_update[] = array(player_id=>3167,firstname=>"Brian",lastname=>"Bark",player_name=>"Brian Bark",numbers=>1);
$players_to_update[] = array(player_id=>3193,firstname=>"John",lastname=>"Barnes",player_name=>"John Barnes",numbers=>1);
$players_to_update[] = array(player_id=>3218,firstname=>"Jim",lastname=>"Barr",player_name=>"Jim Barr",numbers=>1);
$players_to_update[] = array(player_id=>3229,firstname=>"John",lastname=>"Barrett",player_name=>"John Barrett",numbers=>1);
$players_to_update[] = array(player_id=>3277,firstname=>"Doc",lastname=>"Bass",player_name=>"Doc Bass",numbers=>1);
$players_to_update[] = array(player_id=>3278,firstname=>"John",lastname=>"Bass",player_name=>"John Bass",numbers=>1);
$players_to_update[] = array(player_id=>3296,firstname=>"John",lastname=>"Bates",player_name=>"John Bates",numbers=>1);
$players_to_update[] = array(player_id=>3305,firstname=>"Bill",lastname=>"Batsch",player_name=>"Bill Batsch",numbers=>1);
$players_to_update[] = array(player_id=>3339,firstname=>"Harry",lastname=>"Bay",player_name=>"Harry Bay",numbers=>1);
$players_to_update[] = array(player_id=>3360,firstname=>"Bill",lastname=>"Bean",player_name=>"Bill Bean",numbers=>1);
$players_to_update[] = array(player_id=>3392,firstname=>"Rich",lastname=>"Beck",player_name=>"Rich Beck",numbers=>1);
$players_to_update[] = array(player_id=>3450,firstname=>"David",lastname=>"Bell",player_name=>"David Bell",numbers=>1);
$players_to_update[] = array(player_id=>3469,firstname=>"Rob",lastname=>"Bell",player_name=>"Rob Bell",numbers=>1);
$players_to_update[] = array(player_id=>3503,firstname=>"Henry",lastname=>"Benn",player_name=>"Henry Benn",numbers=>1);
$players_to_update[] = array(player_id=>3515,firstname=>"Joe",lastname=>"Bennett",player_name=>"Joe Bennett",numbers=>1);
$players_to_update[] = array(player_id=>3525,firstname=>"Al",lastname=>"Benton",player_name=>"Al Benton",numbers=>1);
$players_to_update[] = array(player_id=>3540,firstname=>"Dave",lastname=>"Berg",player_name=>"Dave Berg",numbers=>1);
$players_to_update[] = array(player_id=>3569,firstname=>"Dwight",lastname=>"Bernard",player_name=>"Dwight Bernard",numbers=>1);
$players_to_update[] = array(player_id=>3652,firstname=>"Ivan",lastname=>"Bigler",player_name=>"Ivan Bigler",numbers=>1);
$players_to_update[] = array(player_id=>3700,firstname=>"Bud",lastname=>"Black",player_name=>"Bud Black",numbers=>1);
$players_to_update[] = array(player_id=>3705,firstname=>"John",lastname=>"Black",player_name=>"John Black",numbers=>1);
$players_to_update[] = array(player_id=>3798,firstname=>"Red",lastname=>"Bluhm",player_name=>"Red Bluhm",numbers=>1);
$players_to_update[] = array(player_id=>3843,firstname=>"",lastname=>"Boland",player_name=>" Boland",numbers=>1);
$players_to_update[] = array(player_id=>3898,firstname=>"Dan",lastname=>"Boone",player_name=>"Dan Boone",numbers=>1);
$players_to_update[] = array(player_id=>3905,firstname=>"",lastname=>"Booth",player_name=>" Booth",numbers=>1);
$players_to_update[] = array(player_id=>3975,firstname=>"Sam",lastname=>"Bowen",player_name=>"Sam Bowen",numbers=>1);
$players_to_update[] = array(player_id=>3995,firstname=>"Elmer",lastname=>"Bowman",player_name=>"Elmer Bowman",numbers=>1);
$players_to_update[] = array(player_id=>4054,firstname=>"",lastname=>"Brady",player_name=>" Brady",numbers=>1);
$players_to_update[] = array(player_id=>4190,firstname=>"Jim",lastname=>"Britt",player_name=>"Jim Britt",numbers=>1);
$players_to_update[] = array(player_id=>4236,firstname=>"Joe",lastname=>"Brovia",player_name=>"Joe Brovia",numbers=>1);
$players_to_update[] = array(player_id=>4237,firstname=>"Scott",lastname=>"Brow",player_name=>"Scott Brow",numbers=>1);
$players_to_update[] = array(player_id=>4247,firstname=>"Bob",lastname=>"Brown",player_name=>"Bob Brown",numbers=>1);
$players_to_update[] = array(player_id=>4256,firstname=>"Curt",lastname=>"Brown",player_name=>"Curt Brown",numbers=>1);
$players_to_update[] = array(player_id=>4257,firstname=>"Curtis",lastname=>"Brown",player_name=>"Curtis Brown",numbers=>1);
$players_to_update[] = array(player_id=>4260,firstname=>"Delos",lastname=>"Brown",player_name=>"Delos Brown",numbers=>1);
$players_to_update[] = array(player_id=>4263,firstname=>"Ed",lastname=>"Brown",player_name=>"Ed Brown",numbers=>1);
$players_to_update[] = array(player_id=>4267,firstname=>"Fred",lastname=>"Brown",player_name=>"Fred Brown",numbers=>1);
$players_to_update[] = array(player_id=>4281,firstname=>"John",lastname=>"Brown",player_name=>"John Brown",numbers=>1);
$players_to_update[] = array(player_id=>4294,firstname=>"Mark",lastname=>"Brown",player_name=>"Mark Brown",numbers=>1);
$players_to_update[] = array(player_id=>4304,firstname=>"Paul",lastname=>"Brown",player_name=>"Paul Brown",numbers=>1);
$players_to_update[] = array(player_id=>4307,firstname=>"Robert",lastname=>"Brown",player_name=>"Robert Brown",numbers=>1);
$players_to_update[] = array(player_id=>4317,firstname=>"Walter",lastname=>"Brown",player_name=>"Walter Brown",numbers=>1);
$players_to_update[] = array(player_id=>4319,firstname=>"William",lastname=>"Brown",player_name=>"William Brown",numbers=>1);
$players_to_update[] = array(player_id=>4375,firstname=>"Hal",lastname=>"Bubser",player_name=>"Hal Bubser",numbers=>1);
$players_to_update[] = array(player_id=>4383,firstname=>"John",lastname=>"Buck",player_name=>"John Buck",numbers=>1);
$players_to_update[] = array(player_id=>4393,firstname=>"",lastname=>"Budd",player_name=>" Budd",numbers=>1);
$players_to_update[] = array(player_id=>4450,firstname=>"Chauncey",lastname=>"Burkam",player_name=>"Chauncey Burkam",numbers=>1);
$players_to_update[] = array(player_id=>4459,firstname=>"James",lastname=>"Burke",player_name=>"James Burke",numbers=>1);
$players_to_update[] = array(player_id=>4471,firstname=>"William",lastname=>"Burke",player_name=>"William Burke",numbers=>1);
$players_to_update[] = array(player_id=>4486,firstname=>"C.B.",lastname=>"Burns",player_name=>"C.B. Burns",numbers=>1);
$players_to_update[] = array(player_id=>4498,firstname=>"Joseph",lastname=>"Burns",player_name=>"Joseph Burns",numbers=>1);
$players_to_update[] = array(player_id=>4522,firstname=>"Frank",lastname=>"Burt",player_name=>"Frank Burt",numbers=>1);
$players_to_update[] = array(player_id=>4566,firstname=>"Frank",lastname=>"Butler",player_name=>"Frank Butler",numbers=>1);
$players_to_update[] = array(player_id=>4568,firstname=>"John",lastname=>"Butler",player_name=>"John Butler",numbers=>1);
$players_to_update[] = array(player_id=>4572,firstname=>"Rich",lastname=>"Butler",player_name=>"Rich Butler",numbers=>1);
$players_to_update[] = array(player_id=>4600,firstname=>"Al",lastname=>"Cabrera",player_name=>"Al Cabrera",numbers=>1);
$players_to_update[] = array(player_id=>4606,firstname=>"Jose",lastname=>"Cabrera",player_name=>"Jose Cabrera",numbers=>1);
$players_to_update[] = array(player_id=>4635,firstname=>"Ralph",lastname=>"Caldwell",player_name=>"Ralph Caldwell",numbers=>1);
$players_to_update[] = array(player_id=>4690,firstname=>"Bill",lastname=>"Campbell",player_name=>"Bill Campbell",numbers=>1);
$players_to_update[] = array(player_id=>4705,firstname=>"Michael",lastname=>"Campbell",player_name=>"Michael Campbell",numbers=>1);
$players_to_update[] = array(player_id=>4776,firstname=>"Fred",lastname=>"Carl",player_name=>"Fred Carl",numbers=>1);
$players_to_update[] = array(player_id=>4777,firstname=>"Lew",lastname=>"Carl",player_name=>"Lew Carl",numbers=>1);
$players_to_update[] = array(player_id=>4876,firstname=>"Sean",lastname=>"Casey",player_name=>"Sean Casey",numbers=>1);
$players_to_update[] = array(player_id=>4894,firstname=>"Jack",lastname=>"Cassini",player_name=>"Jack Cassini",numbers=>1);
$players_to_update[] = array(player_id=>5008,firstname=>"John",lastname=>"Chapman",player_name=>"John Chapman",numbers=>1);
$players_to_update[] = array(player_id=>5018,firstname=>"Ed",lastname=>"Charles",player_name=>"Ed Charles",numbers=>1);
$players_to_update[] = array(player_id=>5019,firstname=>"Frank",lastname=>"Charles",player_name=>"Frank Charles",numbers=>1);
$players_to_update[] = array(player_id=>5081,firstname=>"Mike",lastname=>"Chris",player_name=>"Mike Chris",numbers=>1);
$players_to_update[] = array(player_id=>5150,firstname=>"Dad",lastname=>"Clark",player_name=>"Dad Clark",numbers=>1);
$players_to_update[] = array(player_id=>5156,firstname=>"George",lastname=>"Clark",player_name=>"George Clark",numbers=>1);
$players_to_update[] = array(player_id=>5158,firstname=>"Glen",lastname=>"Clark",player_name=>"Glen Clark",numbers=>1);
$players_to_update[] = array(player_id=>5180,firstname=>"Will",lastname=>"Clark",player_name=>"Will Clark",numbers=>1);
$players_to_update[] = array(player_id=>5229,firstname=>"Jack",lastname=>"Clements",player_name=>"Jack Clements",numbers=>1);
$players_to_update[] = array(player_id=>5269,firstname=>"Joe",lastname=>"Cobb",player_name=>"Joe Cobb",numbers=>1);
$players_to_update[] = array(player_id=>5310,firstname=>"Dave",lastname=>"Cole",player_name=>"Dave Cole",numbers=>1);
$players_to_update[] = array(player_id=>5312,firstname=>"Ed",lastname=>"Cole",player_name=>"Ed Cole",numbers=>1);
$players_to_update[] = array(player_id=>5330,firstname=>"Ray",lastname=>"Coleman",player_name=>"Ray Coleman",numbers=>1);
$players_to_update[] = array(player_id=>5333,firstname=>"Walter",lastname=>"Coleman",player_name=>"Walter Coleman",numbers=>1);
$players_to_update[] = array(player_id=>5345,firstname=>"",lastname=>"Collins",player_name=>" Collins",numbers=>1);
$players_to_update[] = array(player_id=>5363,firstname=>"Rip",lastname=>"Collins",player_name=>"Rip Collins",numbers=>1);
$players_to_update[] = array(player_id=>5423,firstname=>"Joe",lastname=>"Connell",player_name=>"Joe Connell",numbers=>1);
$players_to_update[] = array(player_id=>5436,firstname=>"Joe",lastname=>"Connor",player_name=>"Joe Connor",numbers=>1);
$players_to_update[] = array(player_id=>5496,firstname=>"William",lastname=>"Coon",player_name=>"William Coon",numbers=>1);
$players_to_update[] = array(player_id=>5531,firstname=>"John",lastname=>"Corcoran",player_name=>"John Corcoran",numbers=>1);
$players_to_update[] = array(player_id=>5573,firstname=>"Jess",lastname=>"Cortazzo",player_name=>"Jess Cortazzo",numbers=>1);
$players_to_update[] = array(player_id=>5579,firstname=>"Ray",lastname=>"Cosey",player_name=>"Ray Cosey",numbers=>1);
$players_to_update[] = array(player_id=>5589,firstname=>"Pete",lastname=>"Cote",player_name=>"Pete Cote",numbers=>1);
$players_to_update[] = array(player_id=>5625,firstname=>"Bill",lastname=>"Cox",player_name=>"Bill Cox",numbers=>1);
$players_to_update[] = array(player_id=>5659,firstname=>"Roger",lastname=>"Craig",player_name=>"Roger Craig",numbers=>1);
$players_to_update[] = array(player_id=>5673,firstname=>"Carl",lastname=>"Crawford",player_name=>"Carl Crawford",numbers=>1);
$players_to_update[] = array(player_id=>5691,firstname=>"Connie",lastname=>"Creeden",player_name=>"Connie Creeden",numbers=>1);
$players_to_update[] = array(player_id=>5750,firstname=>"Frank",lastname=>"Cross",player_name=>"Frank Cross",numbers=>1);
$players_to_update[] = array(player_id=>5796,firstname=>"Juan",lastname=>"Cruz",player_name=>"Juan Cruz",numbers=>1);
$players_to_update[] = array(player_id=>5801,firstname=>"Tommy",lastname=>"Cruz",player_name=>"Tommy Cruz",numbers=>1);
$players_to_update[] = array(player_id=>5818,firstname=>"John",lastname=>"Cullen",player_name=>"John Cullen",numbers=>1);
$players_to_update[] = array(player_id=>5833,firstname=>"John",lastname=>"Cummings",player_name=>"John Cummings",numbers=>1);
$players_to_update[] = array(player_id=>5854,firstname=>"George",lastname=>"Curry",player_name=>"George Curry",numbers=>1);
$players_to_update[] = array(player_id=>5865,firstname=>"John",lastname=>"Curtis",player_name=>"John Curtis",numbers=>1);
$players_to_update[] = array(player_id=>5883,firstname=>"John",lastname=>"D'Acquisto",player_name=>"John D'Acquisto",numbers=>1);
$players_to_update[] = array(player_id=>5917,firstname=>"Jack",lastname=>"Dalton",player_name=>"Jack Dalton",numbers=>1);
$players_to_update[] = array(player_id=>5925,firstname=>"Bill",lastname=>"Dam",player_name=>"Bill Dam",numbers=>1);
$players_to_update[] = array(player_id=>5974,firstname=>"Harry",lastname=>"Daubert",player_name=>"Harry Daubert",numbers=>1);
$players_to_update[] = array(player_id=>5977,firstname=>"Doc",lastname=>"Daugherty",player_name=>"Doc Daugherty",numbers=>1);
$players_to_update[] = array(player_id=>5979,firstname=>"Bob",lastname=>"Daughters",player_name=>"Bob Daughters",numbers=>1);
$players_to_update[] = array(player_id=>5994,firstname=>"Andre",lastname=>"David",player_name=>"Andre David",numbers=>1);
$players_to_update[] = array(player_id=>6007,firstname=>"Ben",lastname=>"Davis",player_name=>"Ben Davis",numbers=>1);
$players_to_update[] = array(player_id=>6024,firstname=>"George",lastname=>"Davis",player_name=>"George Davis",numbers=>1);
$players_to_update[] = array(player_id=>6047,firstname=>"Mike",lastname=>"Davis",player_name=>"Mike Davis",numbers=>1);
$players_to_update[] = array(player_id=>6049,firstname=>"Otis",lastname=>"Davis",player_name=>"Otis Davis",numbers=>1);
$players_to_update[] = array(player_id=>6154,firstname=>"Jesus",lastname=>"de la Rosa",player_name=>"Jesus de la Rosa",numbers=>1);
$players_to_update[] = array(player_id=>6091,firstname=>"Harry",lastname=>"Dean",player_name=>"Harry Dean",numbers=>1);
$players_to_update[] = array(player_id=>6092,firstname=>"Paul",lastname=>"Dean",player_name=>"Paul Dean",numbers=>1);
$players_to_update[] = array(player_id=>6109,firstname=>"George",lastname=>"Decker",player_name=>"George Decker",numbers=>1);
$players_to_update[] = array(player_id=>6284,firstname=>"Paul",lastname=>"Dicken",player_name=>"Paul Dicken",numbers=>1);
$players_to_update[] = array(player_id=>6358,firstname=>"John",lastname=>"Dobb",player_name=>"John Dobb",numbers=>1);
$players_to_update[] = array(player_id=>6367,firstname=>"Larry",lastname=>"Doby",player_name=>"Larry Doby",numbers=>1);
$players_to_update[] = array(player_id=>6395,firstname=>"John",lastname=>"Donahue",player_name=>"John Donahue",numbers=>1);
$players_to_update[] = array(player_id=>6460,firstname=>"John",lastname=>"Douglas",player_name=>"John Douglas",numbers=>1);
$players_to_update[] = array(player_id=>6462,firstname=>"Phil",lastname=>"Douglas",player_name=>"Phil Douglas",numbers=>1);
$players_to_update[] = array(player_id=>6494,firstname=>"Jeff",lastname=>"Doyle",player_name=>"Jeff Doyle",numbers=>1);
$players_to_update[] = array(player_id=>6497,firstname=>"John",lastname=>"Doyle",player_name=>"John Doyle",numbers=>1);
$players_to_update[] = array(player_id=>6569,firstname=>"Pat",lastname=>"Duff",player_name=>"Pat Duff",numbers=>1);
$players_to_update[] = array(player_id=>6572,firstname=>"John",lastname=>"Duffie",player_name=>"John Duffie",numbers=>1);
$players_to_update[] = array(player_id=>6666,firstname=>"Joe",lastname=>"Dwyer",player_name=>"Joe Dwyer",numbers=>1);
$players_to_update[] = array(player_id=>6667,firstname=>"John",lastname=>"Dwyer",player_name=>"John Dwyer",numbers=>1);
$players_to_update[] = array(player_id=>6685,firstname=>"Howard",lastname=>"Earl",player_name=>"Howard Earl",numbers=>1);
$players_to_update[] = array(player_id=>6719,firstname=>"Johnny",lastname=>"Echols",player_name=>"Johnny Echols",numbers=>1);
$players_to_update[] = array(player_id=>6747,firstname=>"",lastname=>"Edwards",player_name=>" Edwards",numbers=>1);
$players_to_update[] = array(player_id=>6807,firstname=>"Claud",lastname=>"Elliott",player_name=>"Claud Elliott",numbers=>1);
$players_to_update[] = array(player_id=>6819,firstname=>"John",lastname=>"Ellis",player_name=>"John Ellis",numbers=>1);
$players_to_update[] = array(player_id=>6821,firstname=>"Rob",lastname=>"Ellis",player_name=>"Rob Ellis",numbers=>1);
$players_to_update[] = array(player_id=>6822,firstname=>"Robert",lastname=>"Ellis",player_name=>"Robert Ellis",numbers=>1);
$players_to_update[] = array(player_id=>6949,firstname=>"",lastname=>"Evans",player_name=>" Evans",numbers=>1);
$players_to_update[] = array(player_id=>6967,firstname=>"Carl",lastname=>"Everett",player_name=>"Carl Everett",numbers=>1);
$players_to_update[] = array(player_id=>6971,firstname=>"Joe",lastname=>"Evers",player_name=>"Joe Evers",numbers=>1);
$players_to_update[] = array(player_id=>7017,firstname=>"Charlie",lastname=>"Fallon",player_name=>"Charlie Fallon",numbers=>1);
$players_to_update[] = array(player_id=>7019,firstname=>"Pete",lastname=>"Falsey",player_name=>"Pete Falsey",numbers=>1);
$players_to_update[] = array(player_id=>7066,firstname=>"Joe",lastname=>"Fautsch",player_name=>"Joe Fautsch",numbers=>1);
$players_to_update[] = array(player_id=>7132,firstname=>"Willy",lastname=>"Fetzer",player_name=>"Willy Fetzer",numbers=>1);
$players_to_update[] = array(player_id=>7134,firstname=>"Neil",lastname=>"Fiala",player_name=>"Neil Fiala",numbers=>1);
$players_to_update[] = array(player_id=>7197,firstname=>"William",lastname=>"Fischer",player_name=>"William Fischer",numbers=>1);
$players_to_update[] = array(player_id=>7204,firstname=>"Charles",lastname=>"Fisher",player_name=>"Charles Fisher",numbers=>1);
$players_to_update[] = array(player_id=>7210,firstname=>"Ed",lastname=>"Fisher",player_name=>"Ed Fisher",numbers=>1);
$players_to_update[] = array(player_id=>7213,firstname=>"George",lastname=>"Fisher",player_name=>"George Fisher",numbers=>1);
$players_to_update[] = array(player_id=>7225,firstname=>"Wilbur",lastname=>"Fisher",player_name=>"Wilbur Fisher",numbers=>1);
$players_to_update[] = array(player_id=>7230,firstname=>"Charlie",lastname=>"Fitzberger",player_name=>"Charlie Fitzberger",numbers=>1);
$players_to_update[] = array(player_id=>7242,firstname=>"Ray",lastname=>"Fitzgerald",player_name=>"Ray Fitzgerald",numbers=>1);
$players_to_update[] = array(player_id=>7255,firstname=>"Pat",lastname=>"Flaherty",player_name=>"Pat Flaherty",numbers=>1);
$players_to_update[] = array(player_id=>7273,firstname=>"Les",lastname=>"Fleming",player_name=>"Les Fleming",numbers=>1);
$players_to_update[] = array(player_id=>7279,firstname=>"Frank",lastname=>"Fletcher",player_name=>"Frank Fletcher",numbers=>1);
$players_to_update[] = array(player_id=>7320,firstname=>"John",lastname=>"Flynn",player_name=>"John Flynn",numbers=>1);
$players_to_update[] = array(player_id=>7332,firstname=>"Ray",lastname=>"Foley",player_name=>"Ray Foley",numbers=>1);
$players_to_update[] = array(player_id=>7341,firstname=>"Mike",lastname=>"Fontenot",player_name=>"Mike Fontenot",numbers=>1);
$players_to_update[] = array(player_id=>7354,firstname=>"Ed",lastname=>"Ford",player_name=>"Ed Ford",numbers=>1);
$players_to_update[] = array(player_id=>7362,firstname=>"Tom",lastname=>"Ford",player_name=>"Tom Ford",numbers=>1);
$players_to_update[] = array(player_id=>7389,firstname=>"Ed",lastname=>"Foster",player_name=>"Ed Foster",numbers=>1);
$players_to_update[] = array(player_id=>7392,firstname=>"George",lastname=>"Foster",player_name=>"George Foster",numbers=>1);
$players_to_update[] = array(player_id=>7393,firstname=>"John",lastname=>"Foster",player_name=>"John Foster",numbers=>1);
$players_to_update[] = array(player_id=>7399,firstname=>"Reddy",lastname=>"Foster",player_name=>"Reddy Foster",numbers=>1);
$players_to_update[] = array(player_id=>7418,firstname=>"Bill",lastname=>"Fox",player_name=>"Bill Fox",numbers=>1);
$players_to_update[] = array(player_id=>7425,firstname=>"John",lastname=>"Fox",player_name=>"John Fox",numbers=>1);
$players_to_update[] = array(player_id=>7436,firstname=>"Earl",lastname=>"Francis",player_name=>"Earl Francis",numbers=>1);
$players_to_update[] = array(player_id=>7438,firstname=>"Ray",lastname=>"Francis",player_name=>"Ray Francis",numbers=>1);
$players_to_update[] = array(player_id=>7440,firstname=>"John",lastname=>"Franco",player_name=>"John Franco",numbers=>1);
$players_to_update[] = array(player_id=>7448,firstname=>"Fred",lastname=>"Frank",player_name=>"Fred Frank",numbers=>1);
$players_to_update[] = array(player_id=>7451,firstname=>"",lastname=>"Franklin",player_name=>" Franklin",numbers=>1);
$players_to_update[] = array(player_id=>7452,firstname=>"Jack",lastname=>"Franklin",player_name=>"Jack Franklin",numbers=>1);
$players_to_update[] = array(player_id=>7481,firstname=>"John",lastname=>"Freeman",player_name=>"John Freeman",numbers=>1);
$players_to_update[] = array(player_id=>7529,firstname=>"Larry",lastname=>"Fritz",player_name=>"Larry Fritz",numbers=>1);
$players_to_update[] = array(player_id=>7585,firstname=>"Gabe",lastname=>"Gabler",player_name=>"Gabe Gabler",numbers=>1);
$players_to_update[] = array(player_id=>7591,firstname=>"Eddie",lastname=>"Gaedel",player_name=>"Eddie Gaedel",numbers=>1);
$players_to_update[] = array(player_id=>7596,firstname=>"Ralph",lastname=>"Gagliano",player_name=>"Ralph Gagliano",numbers=>1);
$players_to_update[] = array(player_id=>7616,firstname=>"John",lastname=>"Gall",player_name=>"John Gall",numbers=>1);
$players_to_update[] = array(player_id=>7627,firstname=>"John",lastname=>"Gallagher",player_name=>"John Gallagher",numbers=>1);
$players_to_update[] = array(player_id=>7629,firstname=>"William",lastname=>"Gallagher",player_name=>"William Gallagher",numbers=>1);
$players_to_update[] = array(player_id=>7639,firstname=>"Jim",lastname=>"Galvin",player_name=>"Jim Galvin",numbers=>1);
$players_to_update[] = array(player_id=>7669,firstname=>"Alex",lastname=>"Garbowski",player_name=>"Alex Garbowski",numbers=>1);
$players_to_update[] = array(player_id=>7675,firstname=>"Daniel",lastname=>"Garcia",player_name=>"Daniel Garcia",numbers=>1);
$players_to_update[] = array(player_id=>7704,firstname=>"Bill",lastname=>"Gardner",player_name=>"Bill Gardner",numbers=>1);
$players_to_update[] = array(player_id=>7738,firstname=>"Cecil",lastname=>"Garriott",player_name=>"Cecil Garriott",numbers=>1);
$players_to_update[] = array(player_id=>7783,firstname=>"Elmer",lastname=>"Gedeon",player_name=>"Elmer Gedeon",numbers=>1);
$players_to_update[] = array(player_id=>7804,firstname=>"George",lastname=>"Genovese",player_name=>"George Genovese",numbers=>1);
$players_to_update[] = array(player_id=>7806,firstname=>"Sam",lastname=>"Gentile",player_name=>"Sam Gentile",numbers=>1);
$players_to_update[] = array(player_id=>7808,firstname=>"Harvey",lastname=>"Gentry",player_name=>"Harvey Gentry",numbers=>1);
$players_to_update[] = array(player_id=>7810,firstname=>"Alex",lastname=>"George",player_name=>"Alex George",numbers=>1);
$players_to_update[] = array(player_id=>7876,firstname=>"Robert",lastname=>"Gibson",player_name=>"Robert Gibson",numbers=>1);
$players_to_update[] = array(player_id=>7893,firstname=>"Bill",lastname=>"Gilbert",player_name=>"Bill Gilbert",numbers=>1);
$players_to_update[] = array(player_id=>7897,firstname=>"Harry",lastname=>"Gilbert",player_name=>"Harry Gilbert",numbers=>1);
$players_to_update[] = array(player_id=>7900,firstname=>"John",lastname=>"Gilbert",player_name=>"John Gilbert",numbers=>1);
$players_to_update[] = array(player_id=>7919,firstname=>"George",lastname=>"Gill",player_name=>"George Gill",numbers=>1);
$players_to_update[] = array(player_id=>7921,firstname=>"Jim",lastname=>"Gill",player_name=>"Jim Gill",numbers=>1);
$players_to_update[] = array(player_id=>7931,firstname=>"John",lastname=>"Gillespie",player_name=>"John Gillespie",numbers=>1);
$players_to_update[] = array(player_id=>7946,firstname=>"",lastname=>"Gilroy",player_name=>" Gilroy",numbers=>1);
$players_to_update[] = array(player_id=>7949,firstname=>"Hector",lastname=>"Gimenez",player_name=>"Hector Gimenez",numbers=>1);
$players_to_update[] = array(player_id=>7992,firstname=>"Roy",lastname=>"Gleason",player_name=>"Roy Gleason",numbers=>1);
$players_to_update[] = array(player_id=>8021,firstname=>"Tyrell",lastname=>"Godwin",player_name=>"Tyrell Godwin",numbers=>1);
$players_to_update[] = array(player_id=>8042,firstname=>"Stan",lastname=>"Goletz",player_name=>"Stan Goletz",numbers=>1);
$players_to_update[] = array(player_id=>8076,firstname=>"Jose",lastname=>"Gonzalez",player_name=>"Jose Gonzalez",numbers=>1);
$players_to_update[] = array(player_id=>8110,firstname=>"Jim",lastname=>"Goodwin",player_name=>"Jim Goodwin",numbers=>1);
$players_to_update[] = array(player_id=>8128,firstname=>"Herb",lastname=>"Gorman",player_name=>"Herb Gorman",numbers=>1);
$players_to_update[] = array(player_id=>8131,firstname=>"Thomas",lastname=>"Gorman",player_name=>"Thomas Gorman",numbers=>1);
$players_to_update[] = array(player_id=>8159,firstname=>"John",lastname=>"Grabow",player_name=>"John Grabow",numbers=>1);
$players_to_update[] = array(player_id=>8179,firstname=>"Bill",lastname=>"Graham",player_name=>"Bill Graham",numbers=>1);
$players_to_update[] = array(player_id=>8204,firstname=>"George",lastname=>"Grant",player_name=>"George Grant",numbers=>1);
$players_to_update[] = array(player_id=>8205,firstname=>"Jim",lastname=>"Grant",player_name=>"Jim Grant",numbers=>1);
$players_to_update[] = array(player_id=>8246,firstname=>"David",lastname=>"Green",player_name=>"David Green",numbers=>1);
$players_to_update[] = array(player_id=>8248,firstname=>"Ed",lastname=>"Green",player_name=>"Ed Green",numbers=>1);
$players_to_update[] = array(player_id=>8254,firstname=>"Jim",lastname=>"Green",player_name=>"Jim Green",numbers=>1);
$players_to_update[] = array(player_id=>8255,firstname=>"Joe",lastname=>"Green",player_name=>"Joe Green",numbers=>1);
$players_to_update[] = array(player_id=>8264,firstname=>"Adam",lastname=>"Greenberg",player_name=>"Adam Greenberg",numbers=>1);
$players_to_update[] = array(player_id=>8295,firstname=>"Paul",lastname=>"Gregory",player_name=>"Paul Gregory",numbers=>1);
$players_to_update[] = array(player_id=>8321,firstname=>"Thomas",lastname=>"Griffin",player_name=>"Thomas Griffin",numbers=>1);
$players_to_update[] = array(player_id=>8336,firstname=>"John",lastname=>"Grim",player_name=>"John Grim",numbers=>1);
$players_to_update[] = array(player_id=>8340,firstname=>"Oscar",lastname=>"Grimes",player_name=>"Oscar Grimes",numbers=>1);
$players_to_update[] = array(player_id=>8373,firstname=>"Ernest",lastname=>"Groth",player_name=>"Ernest Groth",numbers=>1);
$players_to_update[] = array(player_id=>8518,firstname=>"Chet",lastname=>"Hajduk",player_name=>"Chet Hajduk",numbers=>1);
$players_to_update[] = array(player_id=>8534,firstname=>"Al",lastname=>"Hall",player_name=>"Al Hall",numbers=>1);
$players_to_update[] = array(player_id=>8543,firstname=>"Charlie",lastname=>"Hall",player_name=>"Charlie Hall",numbers=>1);
$players_to_update[] = array(player_id=>8548,firstname=>"Herb",lastname=>"Hall",player_name=>"Herb Hall",numbers=>1);
$players_to_update[] = array(player_id=>8550,firstname=>"Jim",lastname=>"Hall",player_name=>"Jim Hall",numbers=>1);
$players_to_update[] = array(player_id=>8553,firstname=>"John",lastname=>"Hall",player_name=>"John Hall",numbers=>1);
$players_to_update[] = array(player_id=>8559,firstname=>"Tom",lastname=>"Hall",player_name=>"Tom Hall",numbers=>1);
$players_to_update[] = array(player_id=>8576,firstname=>"Ralph",lastname=>"Ham",player_name=>"Ralph Ham",numbers=>1);
$players_to_update[] = array(player_id=>8609,firstname=>"Bert",lastname=>"Hamric",player_name=>"Bert Hamric",numbers=>1);
$players_to_update[] = array(player_id=>8637,firstname=>"John",lastname=>"Hanna",player_name=>"John Hanna",numbers=>1);
$players_to_update[] = array(player_id=>8652,firstname=>"Doug",lastname=>"Hansen",player_name=>"Doug Hansen",numbers=>1);
$players_to_update[] = array(player_id=>8655,firstname=>"Roy",lastname=>"Hansen",player_name=>"Roy Hansen",numbers=>1);
$players_to_update[] = array(player_id=>8669,firstname=>"Pat",lastname=>"Hardgrove",player_name=>"Pat Hardgrove",numbers=>1);
$players_to_update[] = array(player_id=>8687,firstname=>"Gary",lastname=>"Hargis",player_name=>"Gary Hargis",numbers=>1);
$players_to_update[] = array(player_id=>8730,firstname=>"Mickey",lastname=>"Harrington",player_name=>"Mickey Harrington",numbers=>1);
$players_to_update[] = array(player_id=>8731,firstname=>"Ben",lastname=>"Harris",player_name=>"Ben Harris",numbers=>1);
$players_to_update[] = array(player_id=>8735,firstname=>"Bob",lastname=>"Harris",player_name=>"Bob Harris",numbers=>1);
$players_to_update[] = array(player_id=>8740,firstname=>"Candy",lastname=>"Harris",player_name=>"Candy Harris",numbers=>1);
$players_to_update[] = array(player_id=>8743,firstname=>"Donald",lastname=>"Harris",player_name=>"Donald Harris",numbers=>1);
$players_to_update[] = array(player_id=>8749,firstname=>"Herb",lastname=>"Harris",player_name=>"Herb Harris",numbers=>1);
$players_to_update[] = array(player_id=>8763,firstname=>"Ben",lastname=>"Harrison",player_name=>"Ben Harrison",numbers=>1);
$players_to_update[] = array(player_id=>8777,firstname=>"Bo",lastname=>"Hart",player_name=>"Bo Hart",numbers=>1);
$players_to_update[] = array(player_id=>8802,firstname=>"Greg",lastname=>"Harts",player_name=>"Greg Harts",numbers=>1);
$players_to_update[] = array(player_id=>8878,firstname=>"John",lastname=>"Hayes",player_name=>"John Hayes",numbers=>1);
$players_to_update[] = array(player_id=>8895,firstname=>"Fran",lastname=>"Healy",player_name=>"Fran Healy",numbers=>1);
$players_to_update[] = array(player_id=>8896,firstname=>"Francis",lastname=>"Healy",player_name=>"Francis Healy",numbers=>1);
$players_to_update[] = array(player_id=>8902,firstname=>"Ed",lastname=>"Hearn",player_name=>"Ed Hearn",numbers=>1);
$players_to_update[] = array(player_id=>8908,firstname=>"Jeff",lastname=>"Heath",player_name=>"Jeff Heath",numbers=>1);
$players_to_update[] = array(player_id=>8911,firstname=>"Mike",lastname=>"Heath",player_name=>"Mike Heath",numbers=>1);
$players_to_update[] = array(player_id=>9032,firstname=>"Earl",lastname=>"Henry",player_name=>"Earl Henry",numbers=>1);
$players_to_update[] = array(player_id=>9033,firstname=>"George",lastname=>"Henry",player_name=>"George Henry",numbers=>1);
$players_to_update[] = array(player_id=>9049,firstname=>"Fred",lastname=>"Herbert",player_name=>"Fred Herbert",numbers=>1);
$players_to_update[] = array(player_id=>9050,firstname=>"Ray",lastname=>"Herbert",player_name=>"Ray Herbert",numbers=>1);
$players_to_update[] = array(player_id=>9056,firstname=>"Art",lastname=>"Herman",player_name=>"Art Herman",numbers=>1);
$players_to_update[] = array(player_id=>9059,firstname=>"Al",lastname=>"Hermann",player_name=>"Al Hermann",numbers=>1);
$players_to_update[] = array(player_id=>9101,firstname=>"Tom",lastname=>"Herr",player_name=>"Tom Herr",numbers=>1);
$players_to_update[] = array(player_id=>9120,firstname=>"Earl",lastname=>"Hersh",player_name=>"Earl Hersh",numbers=>1);
$players_to_update[] = array(player_id=>9149,firstname=>"Jim",lastname=>"Hibbs",player_name=>"Jim Hibbs",numbers=>1);
$players_to_update[] = array(player_id=>9163,firstname=>"Joe",lastname=>"Hicks",player_name=>"Joe Hicks",numbers=>1);
$players_to_update[] = array(player_id=>9173,firstname=>"Bob",lastname=>"Higgins",player_name=>"Bob Higgins",numbers=>1);
$players_to_update[] = array(player_id=>9198,firstname=>"Dave",lastname=>"Hill",player_name=>"Dave Hill",numbers=>1);
$players_to_update[] = array(player_id=>9212,firstname=>"Oliver",lastname=>"Hill",player_name=>"Oliver Hill",numbers=>1);
$players_to_update[] = array(player_id=>9215,firstname=>"Shawn",lastname=>"Hill",player_name=>"Shawn Hill",numbers=>1);
$players_to_update[] = array(player_id=>9247,firstname=>"Paul",lastname=>"Hinson",player_name=>"Paul Hinson",numbers=>1);
$players_to_update[] = array(player_id=>9280,firstname=>"Ed",lastname=>"Hodge",player_name=>"Ed Hodge",numbers=>1);
$players_to_update[] = array(player_id=>9355,firstname=>"Will",lastname=>"Holland",player_name=>"Will Holland",numbers=>1);
$players_to_update[] = array(player_id=>9443,firstname=>"Rogers",lastname=>"Hornsby",player_name=>"Rogers Hornsby",numbers=>1);
$players_to_update[] = array(player_id=>9487,firstname=>"Ben",lastname=>"Howard",player_name=>"Ben Howard",numbers=>1);
$players_to_update[] = array(player_id=>9492,firstname=>"David",lastname=>"Howard",player_name=>"David Howard",numbers=>1);
$players_to_update[] = array(player_id=>9504,firstname=>"Paul",lastname=>"Howard",player_name=>"Paul Howard",numbers=>1);
$players_to_update[] = array(player_id=>9507,firstname=>"Thomas",lastname=>"Howard",player_name=>"Thomas Howard",numbers=>1);
$players_to_update[] = array(player_id=>9514,firstname=>"Steve",lastname=>"Howe",player_name=>"Steve Howe",numbers=>1);
$players_to_update[] = array(player_id=>9523,firstname=>"Red",lastname=>"Howell",player_name=>"Red Howell",numbers=>1);
$players_to_update[] = array(player_id=>9557,firstname=>"Charles",lastname=>"Hudson",player_name=>"Charles Hudson",numbers=>1);
$players_to_update[] = array(player_id=>9575,firstname=>"Ed",lastname=>"Hug",player_name=>"Ed Hug",numbers=>1);
$players_to_update[] = array(player_id=>9619,firstname=>"John",lastname=>"Humphries",player_name=>"John Humphries",numbers=>1);
$players_to_update[] = array(player_id=>9705,firstname=>"Mel",lastname=>"Ingram",player_name=>"Mel Ingram",numbers=>1);
$players_to_update[] = array(player_id=>9724,firstname=>"Walt",lastname=>"Irwin",player_name=>"Walt Irwin",numbers=>1);
$players_to_update[] = array(player_id=>9737,firstname=>"Al",lastname=>"Jackson",player_name=>"Al Jackson",numbers=>1);
$players_to_update[] = array(player_id=>9757,firstname=>"Michael",lastname=>"Jackson",player_name=>"Michael Jackson",numbers=>1);
$players_to_update[] = array(player_id=>9765,firstname=>"Sam",lastname=>"Jackson",player_name=>"Sam Jackson",numbers=>1);
$players_to_update[] = array(player_id=>9771,firstname=>"Bucky",lastname=>"Jacobs",player_name=>"Bucky Jacobs",numbers=>1);
$players_to_update[] = array(player_id=>9777,firstname=>"Ray",lastname=>"Jacobs",player_name=>"Ray Jacobs",numbers=>1);
$players_to_update[] = array(player_id=>9797,firstname=>"Art",lastname=>"James",player_name=>"Art James",numbers=>1);
$players_to_update[] = array(player_id=>9804,firstname=>"Chris",lastname=>"James",player_name=>"Chris James",numbers=>1);
$players_to_update[] = array(player_id=>9809,firstname=>"Jeff",lastname=>"James",player_name=>"Jeff James",numbers=>1);
$players_to_update[] = array(player_id=>9854,firstname=>"John",lastname=>"Jenkins",player_name=>"John Jenkins",numbers=>1);
$players_to_update[] = array(player_id=>9869,firstname=>"Dan",lastname=>"Jessee",player_name=>"Dan Jessee",numbers=>1);
$players_to_update[] = array(player_id=>9878,firstname=>"D'Angelo",lastname=>"Jimenez",player_name=>"D'Angelo Jimenez",numbers=>1);
$players_to_update[] = array(player_id=>9891,firstname=>"Tommy",lastname=>"John",player_name=>"Tommy John",numbers=>1);
$players_to_update[] = array(player_id=>9894,firstname=>"Keith",lastname=>"Johns",player_name=>"Keith Johns",numbers=>1);
$players_to_update[] = array(player_id=>9900,firstname=>"Adam",lastname=>"Johnson",player_name=>"Adam Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9901,firstname=>"Alex",lastname=>"Johnson",player_name=>"Alex Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9917,firstname=>"Charles",lastname=>"Johnson",player_name=>"Charles Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9921,firstname=>"Cliff",lastname=>"Johnson",player_name=>"Cliff Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9923,firstname=>"Dan",lastname=>"Johnson",player_name=>"Dan Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9939,firstname=>"Footer",lastname=>"Johnson",player_name=>"Footer Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9940,firstname=>"Frank",lastname=>"Johnson",player_name=>"Frank Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9952,firstname=>"John",lastname=>"Johnson",player_name=>"John Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9980,firstname=>"Ron",lastname=>"Johnson",player_name=>"Ron Johnson",numbers=>1);
$players_to_update[] = array(player_id=>9986,firstname=>"Russ",lastname=>"Johnson",player_name=>"Russ Johnson",numbers=>1);
$players_to_update[] = array(player_id=>10009,firstname=>"John",lastname=>"Johnstone",player_name=>"John Johnstone",numbers=>1);
$players_to_update[] = array(player_id=>10014,firstname=>"",lastname=>"Jones",player_name=>" Jones",numbers=>1);
$players_to_update[] = array(player_id=>10016,firstname=>"Al",lastname=>"Jones",player_name=>"Al Jones",numbers=>1);
$players_to_update[] = array(player_id=>10069,firstname=>"Jim",lastname=>"Jones",player_name=>"Jim Jones",numbers=>1);
$players_to_update[] = array(player_id=>10071,firstname=>"John",lastname=>"Jones",player_name=>"John Jones",numbers=>1);
$players_to_update[] = array(player_id=>10086,firstname=>"Rick",lastname=>"Jones",player_name=>"Rick Jones",numbers=>1);
$players_to_update[] = array(player_id=>10092,firstname=>"Sam",lastname=>"Jones",player_name=>"Sam Jones",numbers=>1);
$players_to_update[] = array(player_id=>10099,firstname=>"Tim",lastname=>"Jones",player_name=>"Tim Jones",numbers=>1);
$players_to_update[] = array(player_id=>10132,firstname=>"Felix",lastname=>"Jose",player_name=>"Felix Jose",numbers=>1);
$players_to_update[] = array(player_id=>10133,firstname=>"Kevin",lastname=>"Joseph",player_name=>"Kevin Joseph",numbers=>1);
$players_to_update[] = array(player_id=>10176,firstname=>"Bob",lastname=>"Kahle",player_name=>"Bob Kahle",numbers=>1);
$players_to_update[] = array(player_id=>10178,firstname=>"Owen",lastname=>"Kahn",player_name=>"Owen Kahn",numbers=>1);
$players_to_update[] = array(player_id=>10235,firstname=>"Charlie",lastname=>"Kavanagh",player_name=>"Charlie Kavanagh",numbers=>1);
$players_to_update[] = array(player_id=>10261,firstname=>"John",lastname=>"Keefe",player_name=>"John Keefe",numbers=>1);
$players_to_update[] = array(player_id=>10270,firstname=>"Jim",lastname=>"Keenan",player_name=>"Jim Keenan",numbers=>1);
$players_to_update[] = array(player_id=>10287,firstname=>"George",lastname=>"Kell",player_name=>"George Kell",numbers=>1);
$players_to_update[] = array(player_id=>10306,firstname=>"Frank",lastname=>"Kelliher",player_name=>"Frank Kelliher",numbers=>1);
$players_to_update[] = array(player_id=>10316,firstname=>"Bob",lastname=>"Kelly",player_name=>"Bob Kelly",numbers=>1);
$players_to_update[] = array(player_id=>10356,firstname=>"Ed",lastname=>"Kenna",player_name=>"Ed Kenna",numbers=>1);
$players_to_update[] = array(player_id=>10372,firstname=>"Ray",lastname=>"Kennedy",player_name=>"Ray Kennedy",numbers=>1);
$players_to_update[] = array(player_id=>10382,firstname=>"Jeff",lastname=>"Kent",player_name=>"Jeff Kent",numbers=>1);
$players_to_update[] = array(player_id=>10402,firstname=>"Russ",lastname=>"Kerns",player_name=>"Russ Kerns",numbers=>1);
$players_to_update[] = array(player_id=>10406,firstname=>"John",lastname=>"Kerr",player_name=>"John Kerr",numbers=>1);
$players_to_update[] = array(player_id=>10407,firstname=>"Mel",lastname=>"Kerr",player_name=>"Mel Kerr",numbers=>1);
$players_to_update[] = array(player_id=>10507,firstname=>"Jim",lastname=>"Kirby",player_name=>"Jim Kirby",numbers=>1);
$players_to_update[] = array(player_id=>10510,firstname=>"Wayne",lastname=>"Kirby",player_name=>"Wayne Kirby",numbers=>1);
$players_to_update[] = array(player_id=>10513,firstname=>"Tom",lastname=>"Kirk",player_name=>"Tom Kirk",numbers=>1);
$players_to_update[] = array(player_id=>10550,firstname=>"Bob",lastname=>"Kline",player_name=>"Bob Kline",numbers=>1);
$players_to_update[] = array(player_id=>10685,firstname=>"Mike",lastname=>"Kosman",player_name=>"Mike Kosman",numbers=>1);
$players_to_update[] = array(player_id=>10751,firstname=>"Gene",lastname=>"Krug",player_name=>"Gene Krug",numbers=>1);
$players_to_update[] = array(player_id=>10771,firstname=>"Steve",lastname=>"Kuczek",player_name=>"Steve Kuczek",numbers=>1);
$players_to_update[] = array(player_id=>10873,firstname=>"Doc",lastname=>"Land",player_name=>"Doc Land",numbers=>1);
$players_to_update[] = array(player_id=>10911,firstname=>"Rimp",lastname=>"Lanier",player_name=>"Rimp Lanier",numbers=>1);
$players_to_update[] = array(player_id=>10931,firstname=>"",lastname=>"Larkin",player_name=>" Larkin",numbers=>1);
$players_to_update[] = array(player_id=>10938,firstname=>"Stephen",lastname=>"Larkin",player_name=>"Stephen Larkin",numbers=>1);
$players_to_update[] = array(player_id=>10986,firstname=>"Ron",lastname=>"Law",player_name=>"Ron Law",numbers=>1);
$players_to_update[] = array(player_id=>11001,firstname=>"Al",lastname=>"Lawson",player_name=>"Al Lawson",numbers=>1);
$players_to_update[] = array(player_id=>11034,firstname=>"John",lastname=>"Leary",player_name=>"John Leary",numbers=>1);
$players_to_update[] = array(player_id=>11049,firstname=>"Bob",lastname=>"Lee",player_name=>"Bob Lee",numbers=>1);
$players_to_update[] = array(player_id=>11054,firstname=>"David",lastname=>"Lee",player_name=>"David Lee",numbers=>1);
$players_to_update[] = array(player_id=>11057,firstname=>"Don",lastname=>"Lee",player_name=>"Don Lee",numbers=>1);
$players_to_update[] = array(player_id=>11066,firstname=>"Roy",lastname=>"Lee",player_name=>"Roy Lee",numbers=>1);
$players_to_update[] = array(player_id=>11068,firstname=>"Terry",lastname=>"Lee",player_name=>"Terry Lee",numbers=>1);
$players_to_update[] = array(player_id=>11070,firstname=>"Tom",lastname=>"Lee",player_name=>"Tom Lee",numbers=>1);
$players_to_update[] = array(player_id=>11071,firstname=>"Travis",lastname=>"Lee",player_name=>"Travis Lee",numbers=>1);
$players_to_update[] = array(player_id=>11138,firstname=>"Danny",lastname=>"Leon",player_name=>"Danny Leon",numbers=>1);
$players_to_update[] = array(player_id=>11141,firstname=>"Jose",lastname=>"Leon",player_name=>"Jose Leon",numbers=>1);
$players_to_update[] = array(player_id=>11143,firstname=>"",lastname=>"Leonard",player_name=>" Leonard",numbers=>1);
$players_to_update[] = array(player_id=>11150,firstname=>"Joe",lastname=>"Leonard",player_name=>"Joe Leonard",numbers=>1);
$players_to_update[] = array(player_id=>11190,firstname=>"",lastname=>"Lewis",player_name=>" Lewis",numbers=>1);
$players_to_update[] = array(player_id=>11227,firstname=>"Fred",lastname=>"Liese",player_name=>"Fred Liese",numbers=>1);
$players_to_update[] = array(player_id=>11241,firstname=>"Carl",lastname=>"Lind",player_name=>"Carl Lind",numbers=>1);
$players_to_update[] = array(player_id=>11245,firstname=>"Em",lastname=>"Lindbeck",player_name=>"Em Lindbeck",numbers=>1);
$players_to_update[] = array(player_id=>11266,firstname=>"Carl",lastname=>"Linhart",player_name=>"Carl Linhart",numbers=>1);
$players_to_update[] = array(player_id=>11268,firstname=>"Fred",lastname=>"Link",player_name=>"Fred Link",numbers=>1);
$players_to_update[] = array(player_id=>11376,firstname=>"",lastname=>"Long",player_name=>" Long",numbers=>1);
$players_to_update[] = array(player_id=>11383,firstname=>"Jim",lastname=>"Long",player_name=>"Jim Long",numbers=>1);
$players_to_update[] = array(player_id=>11404,firstname=>"Al",lastname=>"Lopez",player_name=>"Al Lopez",numbers=>1);
$players_to_update[] = array(player_id=>11412,firstname=>"Javier",lastname=>"Lopez",player_name=>"Javier Lopez",numbers=>1);
$players_to_update[] = array(player_id=>11414,firstname=>"Jose",lastname=>"Lopez",player_name=>"Jose Lopez",numbers=>1);
$players_to_update[] = array(player_id=>11441,firstname=>"Tom",lastname=>"Lovelace",player_name=>"Tom Lovelace",numbers=>1);
$players_to_update[] = array(player_id=>11446,firstname=>"Mem",lastname=>"Lovett",player_name=>"Mem Lovett",numbers=>1);
$players_to_update[] = array(player_id=>11501,firstname=>"Rob",lastname=>"Lukachyk",player_name=>"Rob Lukachyk",numbers=>1);
$players_to_update[] = array(player_id=>11549,firstname=>"Thomas",lastname=>"Lynch",player_name=>"Thomas Lynch",numbers=>1);
$players_to_update[] = array(player_id=>11554,firstname=>"Jerry",lastname=>"Lynn",player_name=>"Jerry Lynn",numbers=>1);
$players_to_update[] = array(player_id=>11630,firstname=>"Gene",lastname=>"Madden",player_name=>"Gene Madden",numbers=>1);
$players_to_update[] = array(player_id=>11680,firstname=>"Frank",lastname=>"Mahar",player_name=>"Frank Mahar",numbers=>1);
$players_to_update[] = array(player_id=>11683,firstname=>"Tom",lastname=>"Maher",player_name=>"Tom Maher",numbers=>1);
$players_to_update[] = array(player_id=>11693,firstname=>"Danny",lastname=>"Mahoney",player_name=>"Danny Mahoney",numbers=>1);
$players_to_update[] = array(player_id=>11749,firstname=>"Pat",lastname=>"Malone",player_name=>"Pat Malone",numbers=>1);
$players_to_update[] = array(player_id=>11774,firstname=>"Garth",lastname=>"Mann",player_name=>"Garth Mann",numbers=>1);
$players_to_update[] = array(player_id=>11775,firstname=>"Jim",lastname=>"Mann",player_name=>"Jim Mann",numbers=>1);
$players_to_update[] = array(player_id=>11854,firstname=>"Ed",lastname=>"Mars",player_name=>"Ed Mars",numbers=>1);
$players_to_update[] = array(player_id=>11856,firstname=>"Fred",lastname=>"Marsh",player_name=>"Fred Marsh",numbers=>1);
$players_to_update[] = array(player_id=>11866,firstname=>"Keith",lastname=>"Marshall",player_name=>"Keith Marshall",numbers=>1);
$players_to_update[] = array(player_id=>11884,firstname=>"Frank",lastname=>"Martin",player_name=>"Frank Martin",numbers=>1);
$players_to_update[] = array(player_id=>11893,firstname=>"John",lastname=>"Martin",player_name=>"John Martin",numbers=>1);
$players_to_update[] = array(player_id=>11897,firstname=>"Pat",lastname=>"Martin",player_name=>"Pat Martin",numbers=>1);
$players_to_update[] = array(player_id=>11919,firstname=>"Felix",lastname=>"Martinez",player_name=>"Felix Martinez",numbers=>1);
$players_to_update[] = array(player_id=>11968,firstname=>"Walt",lastname=>"Masters",player_name=>"Walt Masters",numbers=>1);
$players_to_update[] = array(player_id=>12040,firstname=>"Bob",lastname=>"Mavis",player_name=>"Bob Mavis",numbers=>1);
$players_to_update[] = array(player_id=>12054,firstname=>"Lee",lastname=>"May",player_name=>"Lee May",numbers=>1);
$players_to_update[] = array(player_id=>12075,firstname=>"Willie",lastname=>"Mays",player_name=>"Willie Mays",numbers=>1);
$players_to_update[] = array(player_id=>12096,firstname=>"George",lastname=>"McAvoy",player_name=>"George McAvoy",numbers=>1);
$players_to_update[] = array(player_id=>12116,firstname=>"",lastname=>"McCaffery",player_name=>" McCaffery",numbers=>1);
$players_to_update[] = array(player_id=>12143,firstname=>"William",lastname=>"McCarthy",player_name=>"William McCarthy",numbers=>1);
$players_to_update[] = array(player_id=>12158,firstname=>"Pete",lastname=>"McClanahan",player_name=>"Pete McClanahan",numbers=>1);
$players_to_update[] = array(player_id=>12164,firstname=>"",lastname=>"McCloskey",player_name=>" McCloskey",numbers=>1);
$players_to_update[] = array(player_id=>12183,firstname=>"Harry",lastname=>"McCormick",player_name=>"Harry McCormick",numbers=>1);
$players_to_update[] = array(player_id=>12217,firstname=>"Michael",lastname=>"McDermott",player_name=>"Michael McDermott",numbers=>1);
$players_to_update[] = array(player_id=>12284,firstname=>"Dan",lastname=>"McGee",player_name=>"Dan McGee",numbers=>1);
$players_to_update[] = array(player_id=>12298,firstname=>"Bill",lastname=>"McGilvray",player_name=>"Bill McGilvray",numbers=>1);
$players_to_update[] = array(player_id=>12330,firstname=>"",lastname=>"McGuire",player_name=>" McGuire",numbers=>1);
$players_to_update[] = array(player_id=>12364,firstname=>"Jim",lastname=>"McKee",player_name=>"Jim McKee",numbers=>1);
$players_to_update[] = array(player_id=>12428,firstname=>"Tom",lastname=>"McMillan",player_name=>"Tom McMillan",numbers=>1);
$players_to_update[] = array(player_id=>12437,firstname=>"Carl",lastname=>"McNabb",player_name=>"Carl McNabb",numbers=>1);
$players_to_update[] = array(player_id=>12447,firstname=>"Tom",lastname=>"McNamara",player_name=>"Tom McNamara",numbers=>1);
$players_to_update[] = array(player_id=>12485,firstname=>"Bill",lastname=>"McWilliams",player_name=>"Bill McWilliams",numbers=>1);
$players_to_update[] = array(player_id=>12503,firstname=>"Ray",lastname=>"Medeiros",player_name=>"Ray Medeiros",numbers=>1);
$players_to_update[] = array(player_id=>12574,firstname=>"Luis",lastname=>"Mercedes",player_name=>"Luis Mercedes",numbers=>1);
$players_to_update[] = array(player_id=>12583,firstname=>"Art",lastname=>"Merewether",player_name=>"Art Merewether",numbers=>1);
$players_to_update[] = array(player_id=>12649,firstname=>"John",lastname=>"Michaels",player_name=>"John Michaels",numbers=>1);
$players_to_update[] = array(player_id=>12716,firstname=>"Frank",lastname=>"Miller",player_name=>"Frank Miller",numbers=>1);
$players_to_update[] = array(player_id=>12717,firstname=>"Fred",lastname=>"Miller",player_name=>"Fred Miller",numbers=>1);
$players_to_update[] = array(player_id=>12718,firstname=>"George",lastname=>"Miller",player_name=>"George Miller",numbers=>1);
$players_to_update[] = array(player_id=>12743,firstname=>"Paul",lastname=>"Miller",player_name=>"Paul Miller",numbers=>1);
$players_to_update[] = array(player_id=>12751,firstname=>"Rod",lastname=>"Miller",player_name=>"Rod Miller",numbers=>1);
$players_to_update[] = array(player_id=>12763,firstname=>"Walt",lastname=>"Miller",player_name=>"Walt Miller",numbers=>1);
$players_to_update[] = array(player_id=>12772,firstname=>"John",lastname=>"Milligan",player_name=>"John Milligan",numbers=>1);
$players_to_update[] = array(player_id=>12833,firstname=>"Fred",lastname=>"Mitchell",player_name=>"Fred Mitchell",numbers=>1);
$players_to_update[] = array(player_id=>12834,firstname=>"John",lastname=>"Mitchell",player_name=>"John Mitchell",numbers=>1);
$players_to_update[] = array(player_id=>12839,firstname=>"Mike",lastname=>"Mitchell",player_name=>"Mike Mitchell",numbers=>1);
$players_to_update[] = array(player_id=>12850,firstname=>"Bill",lastname=>"Mizeur",player_name=>"Bill Mizeur",numbers=>1);
$players_to_update[] = array(player_id=>12900,firstname=>"John",lastname=>"Monroe",player_name=>"John Monroe",numbers=>1);
$players_to_update[] = array(player_id=>12932,firstname=>"Al",lastname=>"Moore",player_name=>"Al Moore",numbers=>1);
$players_to_update[] = array(player_id=>12960,firstname=>"Jim",lastname=>"Moore",player_name=>"Jim Moore",numbers=>1);
$players_to_update[] = array(player_id=>12991,firstname=>"Al",lastname=>"Moran",player_name=>"Al Moran",numbers=>1);
$players_to_update[] = array(player_id=>12995,firstname=>"Charles",lastname=>"Moran",player_name=>"Charles Moran",numbers=>1);
$players_to_update[] = array(player_id=>13029,firstname=>"Ed",lastname=>"Morgan",player_name=>"Ed Morgan",numbers=>1);
$players_to_update[] = array(player_id=>13062,firstname=>"Jack",lastname=>"Morris",player_name=>"Jack Morris",numbers=>1);
$players_to_update[] = array(player_id=>13063,firstname=>"James",lastname=>"Morris",player_name=>"James Morris",numbers=>1);
$players_to_update[] = array(player_id=>13064,firstname=>"Jim",lastname=>"Morris",player_name=>"Jim Morris",numbers=>1);
$players_to_update[] = array(player_id=>13069,firstname=>"Walter",lastname=>"Morris",player_name=>"Walter Morris",numbers=>1);
$players_to_update[] = array(player_id=>13083,firstname=>"John",lastname=>"Morrissey",player_name=>"John Morrissey",numbers=>1);
$players_to_update[] = array(player_id=>13093,firstname=>"Moose",lastname=>"Morton",player_name=>"Moose Morton",numbers=>1);
$players_to_update[] = array(player_id=>13100,firstname=>"Arnie",lastname=>"Moser",player_name=>"Arnie Moser",numbers=>1);
$players_to_update[] = array(player_id=>13175,firstname=>"Sean",lastname=>"Mulligan",player_name=>"Sean Mulligan",numbers=>1);
$players_to_update[] = array(player_id=>13220,firstname=>"",lastname=>"Murphy",player_name=>" Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13225,firstname=>"Con",lastname=>"Murphy",player_name=>"Con Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13228,firstname=>"Dan",lastname=>"Murphy",player_name=>"Dan Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13233,firstname=>"David",lastname=>"Murphy",player_name=>"David Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13234,firstname=>"Dick",lastname=>"Murphy",player_name=>"Dick Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13251,firstname=>"Pat",lastname=>"Murphy",player_name=>"Pat Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13252,firstname=>"Rob",lastname=>"Murphy",player_name=>"Rob Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13253,firstname=>"Tom",lastname=>"Murphy",player_name=>"Tom Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13257,firstname=>"Willie",lastname=>"Murphy",player_name=>"Willie Murphy",numbers=>1);
$players_to_update[] = array(player_id=>13265,firstname=>"Ed",lastname=>"Murray",player_name=>"Ed Murray",numbers=>1);
$players_to_update[] = array(player_id=>13307,firstname=>"Henry",lastname=>"Myers",player_name=>"Henry Myers",numbers=>1);
$players_to_update[] = array(player_id=>13314,firstname=>"Richie",lastname=>"Myers",player_name=>"Richie Myers",numbers=>1);
$players_to_update[] = array(player_id=>13315,firstname=>"Rod",lastname=>"Myers",player_name=>"Rod Myers",numbers=>1);
$players_to_update[] = array(player_id=>13401,firstname=>"Bry",lastname=>"Nelson",player_name=>"Bry Nelson",numbers=>1);
$players_to_update[] = array(player_id=>13410,firstname=>"John",lastname=>"Nelson",player_name=>"John Nelson",numbers=>1);
$players_to_update[] = array(player_id=>13417,firstname=>"Rob",lastname=>"Nelson",player_name=>"Rob Nelson",numbers=>1);
$players_to_update[] = array(player_id=>13452,firstname=>"Al",lastname=>"Newman",player_name=>"Al Newman",numbers=>1);
$players_to_update[] = array(player_id=>13465,firstname=>"Don",lastname=>"Nicholas",player_name=>"Don Nicholas",numbers=>1);
$players_to_update[] = array(player_id=>13559,firstname=>"Fred",lastname=>"Norman",player_name=>"Fred Norman",numbers=>1);
$players_to_update[] = array(player_id=>13563,firstname=>"Leo",lastname=>"Norris",player_name=>"Leo Norris",numbers=>1);
$players_to_update[] = array(player_id=>13617,firstname=>"Mike",lastname=>"O'Berry",player_name=>"Mike O'Berry",numbers=>1);
$players_to_update[] = array(player_id=>13619,firstname=>"",lastname=>"O'Brien",player_name=>" O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13620,firstname=>"Billy",lastname=>"O'Brien",player_name=>"Billy O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13621,firstname=>"Bob",lastname=>"O'Brien",player_name=>"Bob O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13622,firstname=>"Buck",lastname=>"O'Brien",player_name=>"Buck O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13623,firstname=>"Charlie",lastname=>"O'Brien",player_name=>"Charlie O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13624,firstname=>"Cinders",lastname=>"O'Brien",player_name=>"Cinders O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13625,firstname=>"Dan",lastname=>"O'Brien",player_name=>"Dan O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13626,firstname=>"Darby",lastname=>"O'Brien",player_name=>"Darby O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13627,firstname=>"Dink",lastname=>"O'Brien",player_name=>"Dink O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13628,firstname=>"Eddie",lastname=>"O'Brien",player_name=>"Eddie O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13629,firstname=>"George",lastname=>"O'Brien",player_name=>"George O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13634,firstname=>"Johnny",lastname=>"O'Brien",player_name=>"Johnny O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13638,firstname=>"Ray",lastname=>"O'Brien",player_name=>"Ray O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13639,firstname=>"Syd",lastname=>"O'Brien",player_name=>"Syd O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13642,firstname=>"Tommy",lastname=>"O'Brien",player_name=>"Tommy O'Brien",numbers=>1);
$players_to_update[] = array(player_id=>13646,firstname=>"Danny",lastname=>"O'Connell",player_name=>"Danny O'Connell",numbers=>1);
$players_to_update[] = array(player_id=>13647,firstname=>"Jimmy",lastname=>"O'Connell",player_name=>"Jimmy O'Connell",numbers=>1);
$players_to_update[] = array(player_id=>13650,firstname=>"Pat",lastname=>"O'Connell",player_name=>"Pat O'Connell",numbers=>1);
$players_to_update[] = array(player_id=>13651,firstname=>"Andy",lastname=>"O'Connor",player_name=>"Andy O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13652,firstname=>"Brian",lastname=>"O'Connor",player_name=>"Brian O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13653,firstname=>"Dan",lastname=>"O'Connor",player_name=>"Dan O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13654,firstname=>"Frank",lastname=>"O'Connor",player_name=>"Frank O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13657,firstname=>"Johnny",lastname=>"O'Connor",player_name=>"Johnny O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13658,firstname=>"Mike",lastname=>"O'Connor",player_name=>"Mike O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13659,firstname=>"Paddy",lastname=>"O'Connor",player_name=>"Paddy O'Connor",numbers=>1);
$players_to_update[] = array(player_id=>13660,firstname=>"Hank",lastname=>"O'Day",player_name=>"Hank O'Day",numbers=>1);
$players_to_update[] = array(player_id=>13661,firstname=>"Ken",lastname=>"O'Dea",player_name=>"Ken O'Dea",numbers=>1);
$players_to_update[] = array(player_id=>13662,firstname=>"Paul",lastname=>"O'Dea",player_name=>"Paul O'Dea",numbers=>1);
$players_to_update[] = array(player_id=>13663,firstname=>"Billy",lastname=>"O'Dell",player_name=>"Billy O'Dell",numbers=>1);
$players_to_update[] = array(player_id=>13668,firstname=>"George",lastname=>"O'Donnell",player_name=>"George O'Donnell",numbers=>1);
$players_to_update[] = array(player_id=>13669,firstname=>"Harry",lastname=>"O'Donnell",player_name=>"Harry O'Donnell",numbers=>1);
$players_to_update[] = array(player_id=>13670,firstname=>"John",lastname=>"O'Donnell",player_name=>"John O'Donnell",numbers=>1);
$players_to_update[] = array(player_id=>13673,firstname=>"Lefty",lastname=>"O'Doul",player_name=>"Lefty O'Doul",numbers=>1);
$players_to_update[] = array(player_id=>13679,firstname=>"Bob",lastname=>"O'Farrell",player_name=>"Bob O'Farrell",numbers=>1);
$players_to_update[] = array(player_id=>13682,firstname=>"Eric",lastname=>"O'Flaherty",player_name=>"Eric O'Flaherty",numbers=>1);
$players_to_update[] = array(player_id=>13690,firstname=>"Hal",lastname=>"O'Hagan",player_name=>"Hal O'Hagan",numbers=>1);
$players_to_update[] = array(player_id=>13691,firstname=>"Greg",lastname=>"O'Halloran",player_name=>"Greg O'Halloran",numbers=>1);
$players_to_update[] = array(player_id=>13692,firstname=>"Bill",lastname=>"O'Hara",player_name=>"Bill O'Hara",numbers=>1);
$players_to_update[] = array(player_id=>13693,firstname=>"Kid",lastname=>"O'Hara",player_name=>"Kid O'Hara",numbers=>1);
$players_to_update[] = array(player_id=>13694,firstname=>"Tom",lastname=>"O'Hara",player_name=>"Tom O'Hara",numbers=>1);
$players_to_update[] = array(player_id=>13711,firstname=>"Charley",lastname=>"O'Leary",player_name=>"Charley O'Leary",numbers=>1);
$players_to_update[] = array(player_id=>13712,firstname=>"Dan",lastname=>"O'Leary",player_name=>"Dan O'Leary",numbers=>1);
$players_to_update[] = array(player_id=>13713,firstname=>"Troy",lastname=>"O'Leary",player_name=>"Troy O'Leary",numbers=>1);
$players_to_update[] = array(player_id=>13752,firstname=>"Ryan",lastname=>"O'Malley",player_name=>"Ryan O'Malley",numbers=>1);
$players_to_update[] = array(player_id=>13753,firstname=>"Tom",lastname=>"O'Malley",player_name=>"Tom O'Malley",numbers=>1);
$players_to_update[] = array(player_id=>13754,firstname=>"Ollie",lastname=>"O'Mara",player_name=>"Ollie O'Mara",numbers=>1);
$players_to_update[] = array(player_id=>13755,firstname=>"Tom",lastname=>"O'Meara",player_name=>"Tom O'Meara",numbers=>1);
$players_to_update[] = array(player_id=>13756,firstname=>"",lastname=>"O'Neal",player_name=>" O'Neal",numbers=>1);
$players_to_update[] = array(player_id=>13757,firstname=>"Randy",lastname=>"O'Neal",player_name=>"Randy O'Neal",numbers=>1);
$players_to_update[] = array(player_id=>13758,firstname=>"Skinny",lastname=>"O'Neal",player_name=>"Skinny O'Neal",numbers=>1);
$players_to_update[] = array(player_id=>13759,firstname=>"Ed",lastname=>"O'Neil",player_name=>"Ed O'Neil",numbers=>1);
$players_to_update[] = array(player_id=>13760,firstname=>"John",lastname=>"O'Neil",player_name=>"John O'Neil",numbers=>1);
$players_to_update[] = array(player_id=>13761,firstname=>"Mickey",lastname=>"O'Neil",player_name=>"Mickey O'Neil",numbers=>1);
$players_to_update[] = array(player_id=>13762,firstname=>"",lastname=>"O'Neill",player_name=>" O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13763,firstname=>"Bill",lastname=>"O'Neill",player_name=>"Bill O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13764,firstname=>"Dennis",lastname=>"O'Neill",player_name=>"Dennis O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13765,firstname=>"Emmett",lastname=>"O'Neill",player_name=>"Emmett O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13766,firstname=>"Fred",lastname=>"O'Neill",player_name=>"Fred O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13769,firstname=>"Jack",lastname=>"O'Neill",player_name=>"Jack O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13770,firstname=>"Jim",lastname=>"O'Neill",player_name=>"Jim O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13771,firstname=>"John",lastname=>"O'Neill",player_name=>"John O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13772,firstname=>"Mike",lastname=>"O'Neill",player_name=>"Mike O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13773,firstname=>"Paul",lastname=>"O'Neill",player_name=>"Paul O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13774,firstname=>"Peaches",lastname=>"O'Neill",player_name=>"Peaches O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13775,firstname=>"Steve",lastname=>"O'Neill",player_name=>"Steve O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13776,firstname=>"Tip",lastname=>"O'Neill",player_name=>"Tip O'Neill",numbers=>1);
$players_to_update[] = array(player_id=>13792,firstname=>"Don",lastname=>"O'Riley",player_name=>"Don O'Riley",numbers=>1);
$players_to_update[] = array(player_id=>13797,firstname=>"",lastname=>"O'Rourke",player_name=>" O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13798,firstname=>"Frank",lastname=>"O'Rourke",player_name=>"Frank O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13801,firstname=>"Joe",lastname=>"O'Rourke",player_name=>"Joe O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13802,firstname=>"John",lastname=>"O'Rourke",player_name=>"John O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13803,firstname=>"Mike",lastname=>"O'Rourke",player_name=>"Mike O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13804,firstname=>"Patsy",lastname=>"O'Rourke",player_name=>"Patsy O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13805,firstname=>"Queenie",lastname=>"O'Rourke",player_name=>"Queenie O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13806,firstname=>"Tim",lastname=>"O'Rourke",player_name=>"Tim O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13807,firstname=>"Tom",lastname=>"O'Rourke",player_name=>"Tom O'Rourke",numbers=>1);
$players_to_update[] = array(player_id=>13871,firstname=>"Denny",lastname=>"O'Toole",player_name=>"Denny O'Toole",numbers=>1);
$players_to_update[] = array(player_id=>13872,firstname=>"Jim",lastname=>"O'Toole",player_name=>"Jim O'Toole",numbers=>1);
$players_to_update[] = array(player_id=>13873,firstname=>"Marty",lastname=>"O'Toole",player_name=>"Marty O'Toole",numbers=>1);
$players_to_update[] = array(player_id=>13744,firstname=>"Greg",lastname=>"Olson",player_name=>"Greg Olson",numbers=>1);
$players_to_update[] = array(player_id=>13816,firstname=>"Bill",lastname=>"Ortega",player_name=>"Bill Ortega",numbers=>1);
$players_to_update[] = array(player_id=>13835,firstname=>"Bob",lastname=>"Osborn",player_name=>"Bob Osborn",numbers=>1);
$players_to_update[] = array(player_id=>13836,firstname=>"Fred",lastname=>"Osborn",player_name=>"Fred Osborn",numbers=>1);
$players_to_update[] = array(player_id=>13854,firstname=>"Red",lastname=>"Ostergard",player_name=>"Red Ostergard",numbers=>1);
$players_to_update[] = array(player_id=>13875,firstname=>"Billy",lastname=>"Ott",player_name=>"Billy Ott",numbers=>1);
$players_to_update[] = array(player_id=>13876,firstname=>"Ed",lastname=>"Ott",player_name=>"Ed Ott",numbers=>1);
$players_to_update[] = array(player_id=>13893,firstname=>"Frank",lastname=>"Owen",player_name=>"Frank Owen",numbers=>1);
$players_to_update[] = array(player_id=>13921,firstname=>"Frankie",lastname=>"Pack",player_name=>"Frankie Pack",numbers=>1);
$players_to_update[] = array(player_id=>13997,firstname=>"Harry",lastname=>"Parker",player_name=>"Harry Parker",numbers=>1);
$players_to_update[] = array(player_id=>14079,firstname=>"Gene",lastname=>"Patton",player_name=>"Gene Patton",numbers=>1);
$players_to_update[] = array(player_id=>14083,firstname=>"Lou",lastname=>"Paul",player_name=>"Lou Paul",numbers=>1);
$players_to_update[] = array(player_id=>14168,firstname=>"Ramon",lastname=>"Pena",player_name=>"Ramon Pena",numbers=>1);
$players_to_update[] = array(player_id=>14204,firstname=>"Antonio",lastname=>"Perez",player_name=>"Antonio Perez",numbers=>1);
$players_to_update[] = array(player_id=>14209,firstname=>"Eduardo",lastname=>"Perez",player_name=>"Eduardo Perez",numbers=>1);
$players_to_update[] = array(player_id=>14225,firstname=>"Tony",lastname=>"Perez",player_name=>"Tony Perez",numbers=>1);
$players_to_update[] = array(player_id=>14243,firstname=>"John",lastname=>"Perrin",player_name=>"John Perrin",numbers=>1);
$players_to_update[] = array(player_id=>14254,firstname=>"Herbert",lastname=>"Perry",player_name=>"Herbert Perry",numbers=>1);
$players_to_update[] = array(player_id=>14266,firstname=>"Chris",lastname=>"Peters",player_name=>"Chris Peters",numbers=>1);
$players_to_update[] = array(player_id=>14296,firstname=>"Ned",lastname=>"Pettigrew",player_name=>"Ned Pettigrew",numbers=>1);
$players_to_update[] = array(player_id=>14343,firstname=>"Ed",lastname=>"Phillips",player_name=>"Ed Phillips",numbers=>1);
$players_to_update[] = array(player_id=>14370,firstname=>"Charlie",lastname=>"Pick",player_name=>"Charlie Pick",numbers=>1);
$players_to_update[] = array(player_id=>14419,firstname=>"Andy",lastname=>"Pilney",player_name=>"Andy Pilney",numbers=>1);
$players_to_update[] = array(player_id=>14454,firstname=>"Ed",lastname=>"Plank",player_name=>"Ed Plank",numbers=>1);
$players_to_update[] = array(player_id=>14512,firstname=>"Ray",lastname=>"Poole",player_name=>"Ray Poole",numbers=>1);
$players_to_update[] = array(player_id=>14520,firstname=>"Bo",lastname=>"Porter",player_name=>"Bo Porter",numbers=>1);
$players_to_update[] = array(player_id=>14521,firstname=>"Bob",lastname=>"Porter",player_name=>"Bob Porter",numbers=>1);
$players_to_update[] = array(player_id=>14561,firstname=>"Bob",lastname=>"Powell",player_name=>"Bob Powell",numbers=>1);
$players_to_update[] = array(player_id=>14584,firstname=>"John",lastname=>"Powers",player_name=>"John Powers",numbers=>1);
$players_to_update[] = array(player_id=>14596,firstname=>"Frank",lastname=>"Pratt",player_name=>"Frank Pratt",numbers=>1);
$players_to_update[] = array(player_id=>14638,firstname=>"Jake",lastname=>"Propst",player_name=>"Jake Propst",numbers=>1);
$players_to_update[] = array(player_id=>14701,firstname=>"",lastname=>"Quinlan",player_name=>" Quinlan",numbers=>1);
$players_to_update[] = array(player_id=>14706,firstname=>"",lastname=>"Quinn",player_name=>" Quinn",numbers=>1);
$players_to_update[] = array(player_id=>14711,firstname=>"John",lastname=>"Quinn",player_name=>"John Quinn",numbers=>1);
$players_to_update[] = array(player_id=>14712,firstname=>"Joseph",lastname=>"Quinn",player_name=>"Joseph Quinn",numbers=>1);
$players_to_update[] = array(player_id=>14841,firstname=>"Gene",lastname=>"Ratliff",player_name=>"Gene Ratliff",numbers=>1);
$players_to_update[] = array(player_id=>14853,firstname=>"Carl",lastname=>"Ray",player_name=>"Carl Ray",numbers=>1);
$players_to_update[] = array(player_id=>14857,firstname=>"Jim",lastname=>"Ray",player_name=>"Jim Ray",numbers=>1);
$players_to_update[] = array(player_id=>14858,firstname=>"Johnny",lastname=>"Ray",player_name=>"Johnny Ray",numbers=>1);
$players_to_update[] = array(player_id=>14859,firstname=>"Ken",lastname=>"Ray",player_name=>"Ken Ray",numbers=>1);
$players_to_update[] = array(player_id=>14865,firstname=>"Claude",lastname=>"Raymond",player_name=>"Claude Raymond",numbers=>1);
$players_to_update[] = array(player_id=>14866,firstname=>"Harry",lastname=>"Raymond",player_name=>"Harry Raymond",numbers=>1);
$players_to_update[] = array(player_id=>14873,firstname=>"Leroy",lastname=>"Reams",player_name=>"Leroy Reams",numbers=>1);
$players_to_update[] = array(player_id=>14921,firstname=>"Kevin",lastname=>"Reese",player_name=>"Kevin Reese",numbers=>1);
$players_to_update[] = array(player_id=>14941,firstname=>"Hugh",lastname=>"Reid",player_name=>"Hugh Reid",numbers=>1);
$players_to_update[] = array(player_id=>14952,firstname=>"John",lastname=>"Reilly",player_name=>"John Reilly",numbers=>1);
$players_to_update[] = array(player_id=>15078,firstname=>"",lastname=>"Richardson",player_name=>" Richardson",numbers=>1);
$players_to_update[] = array(player_id=>15089,firstname=>"Tom",lastname=>"Richardson",player_name=>"Tom Richardson",numbers=>1);
$players_to_update[] = array(player_id=>15114,firstname=>"John",lastname=>"Riddle",player_name=>"John Riddle",numbers=>1);
$players_to_update[] = array(player_id=>15229,firstname=>"Jim",lastname=>"Roberts",player_name=>"Jim Roberts",numbers=>1);
$players_to_update[] = array(player_id=>15230,firstname=>"Leon",lastname=>"Roberts",player_name=>"Leon Roberts",numbers=>1);
$players_to_update[] = array(player_id=>15313,firstname=>"Francisco",lastname=>"Rodriguez",player_name=>"Francisco Rodriguez",numbers=>1);
$players_to_update[] = array(player_id=>15323,firstname=>"Luis",lastname=>"Rodriguez",player_name=>"Luis Rodriguez",numbers=>1);
$players_to_update[] = array(player_id=>15325,firstname=>"Ricardo",lastname=>"Rodriguez",player_name=>"Ricardo Rodriguez",numbers=>1);
$players_to_update[] = array(player_id=>15353,firstname=>"Jim",lastname=>"Rogers",player_name=>"Jim Rogers",numbers=>1);
$players_to_update[] = array(player_id=>15461,firstname=>"Don",lastname=>"Ross",player_name=>"Don Ross",numbers=>1);
$players_to_update[] = array(player_id=>15554,firstname=>"John",lastname=>"Russ",player_name=>"John Russ",numbers=>1);
$players_to_update[] = array(player_id=>15555,firstname=>"Allen",lastname=>"Russell",player_name=>"Allen Russell",numbers=>1);
$players_to_update[] = array(player_id=>15558,firstname=>"Jack",lastname=>"Russell",player_name=>"Jack Russell",numbers=>1);
$players_to_update[] = array(player_id=>15564,firstname=>"Lloyd",lastname=>"Russell",player_name=>"Lloyd Russell",numbers=>1);
$players_to_update[] = array(player_id=>15585,firstname=>"Jason",lastname=>"Ryan",player_name=>"Jason Ryan",numbers=>1);
$players_to_update[] = array(player_id=>15594,firstname=>"Rob",lastname=>"Ryan",player_name=>"Rob Ryan",numbers=>1);
$players_to_update[] = array(player_id=>15690,firstname=>"John",lastname=>"Sanders",player_name=>"John Sanders",numbers=>1);
$players_to_update[] = array(player_id=>15697,firstname=>"Scott",lastname=>"Sanders",player_name=>"Scott Sanders",numbers=>1);
$players_to_update[] = array(player_id=>15726,firstname=>"Rafael",lastname=>"Santo Domingo",player_name=>"Rafael Santo Domingo",numbers=>1);
$players_to_update[] = array(player_id=>15728,firstname=>"Angel",lastname=>"Santos",player_name=>"Angel Santos",numbers=>1);
$players_to_update[] = array(player_id=>15741,firstname=>"Rob",lastname=>"Sasser",player_name=>"Rob Sasser",numbers=>1);
$players_to_update[] = array(player_id=>15777,firstname=>"Johnny",lastname=>"Scalzi",player_name=>"Johnny Scalzi",numbers=>1);
$players_to_update[] = array(player_id=>15855,firstname=>"Dutch",lastname=>"Schirick",player_name=>"Dutch Schirick",numbers=>1);
$players_to_update[] = array(player_id=>15858,firstname=>"Bill",lastname=>"Schlesinger",player_name=>"Bill Schlesinger",numbers=>1);
$players_to_update[] = array(player_id=>15885,firstname=>"Hank",lastname=>"Schmulbach",player_name=>"Hank Schmulbach",numbers=>1);
$players_to_update[] = array(player_id=>15971,firstname=>"",lastname=>"Scott",player_name=>" Scott",numbers=>1);
$players_to_update[] = array(player_id=>15979,firstname=>"Gary",lastname=>"Scott",player_name=>"Gary Scott",numbers=>1);
$players_to_update[] = array(player_id=>15985,firstname=>"John",lastname=>"Scott",player_name=>"John Scott",numbers=>1);
$players_to_update[] = array(player_id=>15991,firstname=>"Milt",lastname=>"Scott",player_name=>"Milt Scott",numbers=>1);
$players_to_update[] = array(player_id=>15993,firstname=>"Rodney",lastname=>"Scott",player_name=>"Rodney Scott",numbers=>1);
$players_to_update[] = array(player_id=>15994,firstname=>"Tim",lastname=>"Scott",player_name=>"Tim Scott",numbers=>1);
$players_to_update[] = array(player_id=>16085,firstname=>"Tommy",lastname=>"Sewell",player_name=>"Tommy Sewell",numbers=>1);
$players_to_update[] = array(player_id=>16099,firstname=>"Ralph",lastname=>"Shafer",player_name=>"Ralph Shafer",numbers=>1);
$players_to_update[] = array(player_id=>16102,firstname=>"",lastname=>"Shaffer",player_name=>" Shaffer",numbers=>1);
$players_to_update[] = array(player_id=>16139,firstname=>"Bob",lastname=>"Shaw",player_name=>"Bob Shaw",numbers=>1);
$players_to_update[] = array(player_id=>16142,firstname=>"Hunky",lastname=>"Shaw",player_name=>"Hunky Shaw",numbers=>1);
$players_to_update[] = array(player_id=>16150,firstname=>"John",lastname=>"Shea",player_name=>"John Shea",numbers=>1);
$players_to_update[] = array(player_id=>16169,firstname=>"Tom",lastname=>"Sheehan",player_name=>"Tom Sheehan",numbers=>1);
$players_to_update[] = array(player_id=>16197,firstname=>"",lastname=>"Sheridan",player_name=>" Sheridan",numbers=>1);
$players_to_update[] = array(player_id=>16198,firstname=>"Neill",lastname=>"Sheridan",player_name=>"Neill Sheridan",numbers=>1);
$players_to_update[] = array(player_id=>16201,firstname=>"Ed",lastname=>"Sherling",player_name=>"Ed Sherling",numbers=>1);
$players_to_update[] = array(player_id=>16206,firstname=>"Joe",lastname=>"Sherman",player_name=>"Joe Sherman",numbers=>1);
$players_to_update[] = array(player_id=>16254,firstname=>"Ray",lastname=>"Shook",player_name=>"Ray Shook",numbers=>1);
$players_to_update[] = array(player_id=>16379,firstname=>"Roe",lastname=>"Skidmore",player_name=>"Roe Skidmore",numbers=>1);
$players_to_update[] = array(player_id=>16434,firstname=>"",lastname=>"Smith",player_name=>" Smith",numbers=>1);
$players_to_update[] = array(player_id=>16484,firstname=>"Elmer",lastname=>"Smith",player_name=>"Elmer Smith",numbers=>1);
$players_to_update[] = array(player_id=>16512,firstname=>"Jim",lastname=>"Smith",player_name=>"Jim Smith",numbers=>1);
$players_to_update[] = array(player_id=>16521,firstname=>"Ken",lastname=>"Smith",player_name=>"Ken Smith",numbers=>1);
$players_to_update[] = array(player_id=>16531,firstname=>"Michael",lastname=>"Smith",player_name=>"Michael Smith",numbers=>1);
$players_to_update[] = array(player_id=>16546,firstname=>"Pop",lastname=>"Smith",player_name=>"Pop Smith",numbers=>1);
$players_to_update[] = array(player_id=>16581,firstname=>"Clancy",lastname=>"Smyres",player_name=>"Clancy Smyres",numbers=>1);
$players_to_update[] = array(player_id=>16595,firstname=>"Roxy",lastname=>"Snipes",player_name=>"Roxy Snipes",numbers=>1);
$players_to_update[] = array(player_id=>16606,firstname=>"Charles",lastname=>"Snyder",player_name=>"Charles Snyder",numbers=>1);
$players_to_update[] = array(player_id=>16611,firstname=>"Frank",lastname=>"Snyder",player_name=>"Frank Snyder",numbers=>1);
$players_to_update[] = array(player_id=>16618,firstname=>"John",lastname=>"Snyder",player_name=>"John Snyder",numbers=>1);
$players_to_update[] = array(player_id=>16626,firstname=>"Bill",lastname=>"Sodd",player_name=>"Bill Sodd",numbers=>1);
$players_to_update[] = array(player_id=>16667,firstname=>"Bill",lastname=>"Southworth",player_name=>"Bill Southworth",numbers=>1);
$players_to_update[] = array(player_id=>16697,firstname=>"Stan",lastname=>"Spence",player_name=>"Stan Spence",numbers=>1);
$players_to_update[] = array(player_id=>16698,firstname=>"",lastname=>"Spencer",player_name=>" Spencer",numbers=>1);
$players_to_update[] = array(player_id=>16706,firstname=>"Roy",lastname=>"Spencer",player_name=>"Roy Spencer",numbers=>1);
$players_to_update[] = array(player_id=>16766,firstname=>"Heinie",lastname=>"Stafford",player_name=>"Heinie Stafford",numbers=>1);
$players_to_update[] = array(player_id=>16802,firstname=>"Fred",lastname=>"Stanley",player_name=>"Fred Stanley",numbers=>1);
$players_to_update[] = array(player_id=>16855,firstname=>"Bill",lastname=>"Stein",player_name=>"Bill Stein",numbers=>1);
$players_to_update[] = array(player_id=>16889,firstname=>"John",lastname=>"Stephens",player_name=>"John Stephens",numbers=>1);
$players_to_update[] = array(player_id=>16898,firstname=>"John",lastname=>"Stephenson",player_name=>"John Stephenson",numbers=>1);
$players_to_update[] = array(player_id=>16913,firstname=>"Robert",lastname=>"Stevens",player_name=>"Robert Stevens",numbers=>1);
$players_to_update[] = array(player_id=>16961,firstname=>"",lastname=>"Stoddard",player_name=>" Stoddard",numbers=>1);
$players_to_update[] = array(player_id=>16975,firstname=>"John",lastname=>"Stone",player_name=>"John Stone",numbers=>1);
$players_to_update[] = array(player_id=>17058,firstname=>"Moose",lastname=>"Stubing",player_name=>"Moose Stubing",numbers=>1);
$players_to_update[] = array(player_id=>17097,firstname=>"",lastname=>"Sullivan",player_name=>" Sullivan",numbers=>1);
$players_to_update[] = array(player_id=>17106,firstname=>"Dan",lastname=>"Sullivan",player_name=>"Dan Sullivan",numbers=>1);
$players_to_update[] = array(player_id=>17305,firstname=>"Bill",lastname=>"Taylor",player_name=>"Bill Taylor",numbers=>1);
$players_to_update[] = array(player_id=>17331,firstname=>"Leo",lastname=>"Taylor",player_name=>"Leo Taylor",numbers=>1);
$players_to_update[] = array(player_id=>17354,firstname=>"Dick",lastname=>"Teed",player_name=>"Dick Teed",numbers=>1);
$players_to_update[] = array(player_id=>17371,firstname=>"Tom",lastname=>"Tennant",player_name=>"Tom Tennant",numbers=>1);
$players_to_update[] = array(player_id=>17411,firstname=>"George",lastname=>"Theodore",player_name=>"George Theodore",numbers=>1);
$players_to_update[] = array(player_id=>17426,firstname=>"Blaine",lastname=>"Thomas",player_name=>"Blaine Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17427,firstname=>"Brad",lastname=>"Thomas",player_name=>"Brad Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17430,firstname=>"Carl",lastname=>"Thomas",player_name=>"Carl Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17431,firstname=>"Charles",lastname=>"Thomas",player_name=>"Charles Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17433,firstname=>"Dan",lastname=>"Thomas",player_name=>"Dan Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17438,firstname=>"Fred",lastname=>"Thomas",player_name=>"Fred Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17440,firstname=>"George",lastname=>"Thomas",player_name=>"George Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17443,firstname=>"Ira",lastname=>"Thomas",player_name=>"Ira Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17446,firstname=>"Lee",lastname=>"Thomas",player_name=>"Lee Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17448,firstname=>"Leo",lastname=>"Thomas",player_name=>"Leo Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17452,firstname=>"Ray",lastname=>"Thomas",player_name=>"Ray Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17457,firstname=>"Tom",lastname=>"Thomas",player_name=>"Tom Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17460,firstname=>"Walt",lastname=>"Thomas",player_name=>"Walt Thomas",numbers=>1);
$players_to_update[] = array(player_id=>17465,firstname=>"",lastname=>"Thompson",player_name=>" Thompson",numbers=>1);
$players_to_update[] = array(player_id=>17466,firstname=>"Andrew",lastname=>"Thompson",player_name=>"Andrew Thompson",numbers=>1);
$players_to_update[] = array(player_id=>17569,firstname=>"Jack",lastname=>"Tobin",player_name=>"Jack Tobin",numbers=>1);
$players_to_update[] = array(player_id=>17573,firstname=>"Tip",lastname=>"Tobin",player_name=>"Tip Tobin",numbers=>1);
$players_to_update[] = array(player_id=>17592,firstname=>"George",lastname=>"Tomer",player_name=>"George Tomer",numbers=>1);
$players_to_update[] = array(player_id=>17765,firstname=>"Wayne",lastname=>"Tyrone",player_name=>"Wayne Tyrone",numbers=>1);
$players_to_update[] = array(player_id=>17767,firstname=>"Turkey",lastname=>"Tyson",player_name=>"Turkey Tyson",numbers=>1);
$players_to_update[] = array(player_id=>17801,firstname=>"Dixie",lastname=>"Upright",player_name=>"Dixie Upright",numbers=>1);
$players_to_update[] = array(player_id=>17822,firstname=>"Harry",lastname=>"Vahrenhorst",player_name=>"Harry Vahrenhorst",numbers=>1);
$players_to_update[] = array(player_id=>17828,firstname=>"Roy",lastname=>"Valdes",player_name=>"Roy Valdes",numbers=>1);
$players_to_update[] = array(player_id=>17843,firstname=>"John",lastname=>"Valentin",player_name=>"John Valentin",numbers=>1);
$players_to_update[] = array(player_id=>17844,firstname=>"Jose",lastname=>"Valentin",player_name=>"Jose Valentin",numbers=>1);
$players_to_update[] = array(player_id=>17845,firstname=>"Bob",lastname=>"Valentine",player_name=>"Bob Valentine",numbers=>1);
$players_to_update[] = array(player_id=>17880,firstname=>"Fred",lastname=>"Van Dusen",player_name=>"Fred Van Dusen",numbers=>1);
$players_to_update[] = array(player_id=>17889,firstname=>"John",lastname=>"Vann",player_name=>"John Vann",numbers=>1);
$players_to_update[] = array(player_id=>18041,firstname=>"Bill",lastname=>"Wagner",player_name=>"Bill Wagner",numbers=>1);
$players_to_update[] = array(player_id=>18119,firstname=>"Joe",lastname=>"Wall",player_name=>"Joe Wall",numbers=>1);
$players_to_update[] = array(player_id=>18149,firstname=>"Jim",lastname=>"Walsh",player_name=>"Jim Walsh",numbers=>1);
$players_to_update[] = array(player_id=>18158,firstname=>"Walt",lastname=>"Walsh",player_name=>"Walt Walsh",numbers=>1);
$players_to_update[] = array(player_id=>18160,firstname=>"Gene",lastname=>"Walter",player_name=>"Gene Walter",numbers=>1);
$players_to_update[] = array(player_id=>18236,firstname=>"George",lastname=>"Washington",player_name=>"George Washington",numbers=>1);
$players_to_update[] = array(player_id=>18271,firstname=>"Frank",lastname=>"Watt",player_name=>"Frank Watt",numbers=>1);
$players_to_update[] = array(player_id=>18276,firstname=>"Gary",lastname=>"Wayne",player_name=>"Gary Wayne",numbers=>1);
$players_to_update[] = array(player_id=>18301,firstname=>"John",lastname=>"Webb",player_name=>"John Webb",numbers=>1);
$players_to_update[] = array(player_id=>18317,firstname=>"Bert",lastname=>"Weeden",player_name=>"Bert Weeden",numbers=>1);
$players_to_update[] = array(player_id=>18357,firstname=>"Ollie",lastname=>"Welf",player_name=>"Ollie Welf",numbers=>1);
$players_to_update[] = array(player_id=>18412,firstname=>"Jim",lastname=>"Westlake",player_name=>"Jim Westlake",numbers=>1);
$players_to_update[] = array(player_id=>18414,firstname=>"Al",lastname=>"Weston",player_name=>"Al Weston",numbers=>1);
$players_to_update[] = array(player_id=>18443,firstname=>"Jimmy",lastname=>"Whelan",player_name=>"Jimmy Whelan",numbers=>1);
$players_to_update[] = array(player_id=>18461,firstname=>"Charlie",lastname=>"White",player_name=>"Charlie White",numbers=>1);
$players_to_update[] = array(player_id=>18468,firstname=>"Ed",lastname=>"White",player_name=>"Ed White",numbers=>1);
$players_to_update[] = array(player_id=>18482,firstname=>"Matt",lastname=>"White",player_name=>"Matt White",numbers=>1);
$players_to_update[] = array(player_id=>18488,firstname=>"Sam",lastname=>"White",player_name=>"Sam White",numbers=>1);
$players_to_update[] = array(player_id=>18492,firstname=>"Will",lastname=>"White",player_name=>"Will White",numbers=>1);
$players_to_update[] = array(player_id=>18516,firstname=>"Art",lastname=>"Whitney",player_name=>"Art Whitney",numbers=>1);
$players_to_update[] = array(player_id=>18578,firstname=>"Bob",lastname=>"Will",player_name=>"Bob Will",numbers=>1);
$players_to_update[] = array(player_id=>18584,firstname=>"Al",lastname=>"Williams",player_name=>"Al Williams",numbers=>1);
$players_to_update[] = array(player_id=>18598,firstname=>"Dave",lastname=>"Williams",player_name=>"Dave Williams",numbers=>1);
$players_to_update[] = array(player_id=>18600,firstname=>"David",lastname=>"Williams",player_name=>"David Williams",numbers=>1);
$players_to_update[] = array(player_id=>18620,firstname=>"Jim",lastname=>"Williams",player_name=>"Jim Williams",numbers=>1);
$players_to_update[] = array(player_id=>18629,firstname=>"Mark",lastname=>"Williams",player_name=>"Mark Williams",numbers=>1);
$players_to_update[] = array(player_id=>18652,firstname=>"Walt",lastname=>"Williams",player_name=>"Walt Williams",numbers=>1);
$players_to_update[] = array(player_id=>18658,firstname=>"Howie",lastname=>"Williamson",player_name=>"Howie Williamson",numbers=>1);
$players_to_update[] = array(player_id=>18678,firstname=>"",lastname=>"Wills",player_name=>" Wills",numbers=>1);
$players_to_update[] = array(player_id=>18681,firstname=>"Frank",lastname=>"Wills",player_name=>"Frank Wills",numbers=>1);
$players_to_update[] = array(player_id=>18690,firstname=>"Art",lastname=>"Wilson",player_name=>"Art Wilson",numbers=>1);
$players_to_update[] = array(player_id=>18705,firstname=>"Don",lastname=>"Wilson",player_name=>"Don Wilson",numbers=>1);
$players_to_update[] = array(player_id=>18711,firstname=>"Frank",lastname=>"Wilson",player_name=>"Frank Wilson",numbers=>1);
$players_to_update[] = array(player_id=>18715,firstname=>"George",lastname=>"Wilson",player_name=>"George Wilson",numbers=>1);
$players_to_update[] = array(player_id=>18721,firstname=>"Icehouse",lastname=>"Wilson",player_name=>"Icehouse Wilson",numbers=>1);
$players_to_update[] = array(player_id=>18856,firstname=>"",lastname=>"Wood",player_name=>" Wood",numbers=>1);
$players_to_update[] = array(player_id=>18859,firstname=>"Fred",lastname=>"Wood",player_name=>"Fred Wood",numbers=>1);
$players_to_update[] = array(player_id=>18860,firstname=>"George",lastname=>"Wood",player_name=>"George Wood",numbers=>1);
$players_to_update[] = array(player_id=>18862,firstname=>"Jake",lastname=>"Wood",player_name=>"Jake Wood",numbers=>1);
$players_to_update[] = array(player_id=>18867,firstname=>"John",lastname=>"Wood",player_name=>"John Wood",numbers=>1);
$players_to_update[] = array(player_id=>18869,firstname=>"Kerry",lastname=>"Wood",player_name=>"Kerry Wood",numbers=>1);
$players_to_update[] = array(player_id=>18870,firstname=>"Mike",lastname=>"Wood",player_name=>"Mike Wood",numbers=>1);
$players_to_update[] = array(player_id=>18871,firstname=>"Pete",lastname=>"Wood",player_name=>"Pete Wood",numbers=>1);
$players_to_update[] = array(player_id=>18899,firstname=>"Walt",lastname=>"Woods",player_name=>"Walt Woods",numbers=>1);
$players_to_update[] = array(player_id=>18928,firstname=>"Al",lastname=>"Wright",player_name=>"Al Wright",numbers=>1);
$players_to_update[] = array(player_id=>18935,firstname=>"David",lastname=>"Wright",player_name=>"David Wright",numbers=>1);
$players_to_update[] = array(player_id=>18969,firstname=>"John",lastname=>"Wyatt",player_name=>"John Wyatt",numbers=>1);
$players_to_update[] = array(player_id=>18977,firstname=>"Bill",lastname=>"Wynne",player_name=>"Bill Wynne",numbers=>1);
$players_to_update[] = array(player_id=>18998,firstname=>"Bert",lastname=>"Yeabsley",player_name=>"Bert Yeabsley",numbers=>1);
$players_to_update[] = array(player_id=>19053,firstname=>"George",lastname=>"Young",player_name=>"George Young",numbers=>1);
$players_to_update[] = array(player_id=>19060,firstname=>"John",lastname=>"Young",player_name=>"John Young",numbers=>1);
$players_to_update[] = array(player_id=>19064,firstname=>"Michael",lastname=>"Young",player_name=>"Michael Young",numbers=>1);

	
	return $players_to_update;

}


}

SpecialPage::addPage( new MLBPlayerSearch );



}

?>
