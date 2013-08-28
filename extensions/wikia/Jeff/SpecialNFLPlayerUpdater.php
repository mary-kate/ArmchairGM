<?php

$wgExtensionFunctions[] = 'wfSpecialNFLPlayerUpdater';

$team_translations = array();


function wfSpecialNFLPlayerUpdater(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class NFLPlayerUpdater extends SpecialPage {
	
	
	function NFLPlayerUpdater(){
		UnlistedSpecialPage::UnlistedSpecialPage("NFLPlayerUpdater");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $wgParser;
		
		/*
		//if ($value && strlen($value) == 1) {
		if ($value && $value == "ErrorUpdate") {
			$output = "";
			
			$wgUser = User::newFromName( "MLB Stats Bot" );
			$wgUser->addGroup( 'bot' );
			
			$dbr =& wfGetDB( DB_MASTER );

			
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
					
					$article_text = $this->get_player_stats($row["firstname"], $row["lastname"], $row["player_id"]);
					/*
					$article = new Article($title);
					$article->doEdit( $article_text, "MLB Player Pages", EDIT_SUPPRESS_RC);
					$complete_output .= "edit of <b>{$page_title}</b> complete. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
					$complete_output_wiki .= "edit of <b>{$page_title}</b> complete. [[{$page_title}]]<br>";
					sleep(2);
					
				}
				else{
					//$skipped .= "[[{$page_title}]]\n";
					$outsider_output .= "skipped <b>{$page_title}</b> because edited by outsider. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
					$outsider_output_wiki .= "skipped <b>{$page_title}</b> because edited by outsider. [[{$page_title}]]<br>";
				}
				
			
			//mysql_free_result($result);
			$title = "Generated Stats for {$first} {$last}";

			$wgOut->setPageTitle($title);
			$wgOut->addHTML($output);
			
		}
		*/
		
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Jeff/playerUpdater.js?{$wgStyleVersion}\"></script>\n");
			$redirect = "";
		
			//if ($wgRequest->getVal("first") != "" || $wgRequest->getVal("last") != "") {
			if (isset($_POST["action"]) && $_POST["action"] == "create") {
				$title = $_POST["player-name"];
				$current_article = $_POST["left"];				
				
				$page_title = Title::makeTitleSafe( NS_MAIN, $title );
				$article = new Article($page_title);
				$article->doEdit( $current_article, "NFL Player Pages");
				
				$re_pattern = "/\[\[Category\:[\s]+([^\]]+)\]\]/i";
				//$re_pattern = "/(Category)\:/i";
				
				$total_matches = preg_match_all($re_pattern, $current_article, $matches);
				//$categories = "";
				for ($i=0; $i<$total_matches; $i++) {
		
					if ($matches[1][$i]) {
						//$categories .= $matches[1][$i] . "; ";
						
						if(strpos($matches[1][$i], "|")) {
							$cur_match = substr($matches[1][$i], 0, strpos($matches[1][$i], "|"));
						}
						else {
							$cur_match = $matches[1][$i];
						}
						
						MLBRosterPages::makeCategory($cur_match);
						
					}
				}
				header("Location:index.php?title={$title}");
				
			}
			else if(isset($_POST["right"])) {
				
				$output_array = array("wiki"=>$_POST["left"],"regular"=>false,"name"=>$_POST["player-name"]);
				$which_preview = $_POST["action"];
				$current_article = $_POST["right"];
				if ($wgRequest->getVal("disambigname") != "") {
					$name_display = "text";
				}
				$redirect .= "<script type=\"text/javascript\">location.href=\"#view_preview\";</script>";
				$title = "Generated Stats for {$output_array["name"]}";
				
			}
			else {
				if ($wgRequest->getVal("playername") != "") {	
					$output_array = $this->getNFLPlayerStats($wgRequest->getVal("playername"), false, false);
					$page_title = Title::makeTitleSafe( NS_MAIN, $output_array["name"] );
					$title = "Generated Stats for {$output_array["name"]}";
					$name_display = "hidden";
					$article = new Article($page_title);
					$revision = $article->getRevIdFetched();
					$raw_text = new RawPage($article, false);
					$does_exist = $article->exists();
					$current_article = $raw_text->getArticleText();
					$which_preview = "right";
					//$current_article = str_replace("&ndash;", "-", $raw_text->getArticleText());
					//$current_article = rawurldecode(str_replace("%E2%80%93", "-", rawurlencode($current_article)));

					
				}
				elseif ($wgRequest->getVal("playerid") != ""){
					$output_array = $this->getNFLPlayerStats(false, $wgRequest->getVal("playerid"), false);
					$page_title = Title::makeTitleSafe( NS_MAIN, $output_array["name"] );
					$title = "Generated Stats for {$output_array["name"]}";
					if ($wgRequest->getVal("disambigname") != "") {
						$output_array["name"] = $wgRequest->getVal("disambigname");
						$name_display = "text";

					}
					$article = new Article($page_title);
					$revision = $article->getRevIdFetched();
					$raw_text = new RawPage($article, false);
					$does_exist = $article->exists();
					$current_article = $raw_text->getArticleText();
					$which_preview = "right";
					//$current_article = str_replace("&ndash;", "-", $raw_text->getArticleText());
					//$current_article = rawurldecode(str_replace("%E2%80%93", "-", rawurlencode($current_article)));

				}
				else {
					$output_array = array("wiki"=>false,"regular"=>false,"name"=>false);
					$title = "Search for the stats for a player";
					$name_display = "hidden";
					$revision = false;
				}
			}
			/*
			$output = "<form name=\"form1\">
				<input type=\"hidden\" name=\"title\" id=\"title\" value=\"Special:MLBPlayerUpdater\" />
				Player name: <input type=\"text\" name=\"playername\" id=\"first\" /> 
				<input type=\"submit\" value=\"Lookup Stats\" />
				</form>
				
				<br/><br/>";
			*/	
			$output = "<form name=\"form1\" id=\"form1\" method=\"post\">";
				if ($output_array["wiki"]) {
					/*
					if ($does_exist) {
						$output .= "This article exists.  Current Revision ID = {$revision}<br><br>";
					}
					else {
						$output .= "This article does not exist<br/>";
					}
					*/
					$output .= "<a name=\"editing\"></a>";
					$output .= "<h2>Wiki Text</h2><br/>";
					$output .= "<div id=\"replace-popup\" style=\"position:absolute; top:400px; left:400px; border: 1px solid black; background-color:lightgrey; height:200px; width:300px; visible:hidden; display:none;\"></div>";
					$output .= "<div id=\"category-popup\" style=\"position:absolute; top:400px; left:400px; border: 1px solid black; background-color:lightgrey; height:250px; width:300px; visible:hidden; display:none; overflow: auto;\">";
						
						$output .= "<table width=\"90%\"><tr><td><a style=\"cursor:pointer;\" onclick=\"copyCategory('left', document.form1.categorylist, 'category-popup');\">&lt;&lt; Copy Checked Categories</a></td>";
						$output .= "<td align=right><a style=\"cursor:pointer;\" onClick=\"closeBox('category-popup');\">[x] cancel</a></td></tr>";
						$output .= "<tr><td colspan=2>(<a style=\"cursor:pointer;\" onClick=\"checkAll(document.form1.categorylist);\">check all</a> | <a style=\"cursor:pointer;\" onClick=\"uncheckAll(document.form1.categorylist);\">uncheck all</a>)</table>";
						$re_pattern = "/\[\[Category\:[\s]*([^\]]+)\]\]/i";
						//$re_pattern = "/(Category)\:/i";
						
						$total_matches = preg_match_all($re_pattern, $current_article, $matches);
						//$categories = "";
						//$output .= $total_matches . "<br/>";
						for ($i=0; $i<$total_matches; $i++) {
				
							if (strpos($matches[1][$i], "|")) {
								$this_category = substr($matches[1][$i], 0, strpos($matches[1][$i], "|"));
							}
							else {
								$this_category = $matches[1][$i];
							}
							//$output .= "<a style=\"cursor:pointer;\" onclick=\"copyCategory('left', '{$this_category}');\">{$this_category}</a><br/>";
							$output .= "<input type=\"checkbox\" name=\"categorylist\" id=\"categorylist\" value=\"{$this_category}\">{$this_category}<br/>";
						}
							//$output .= "<input type=\"checkbox\" name=\"categorylist\" id=\"categorylist\" value=\"{$this_category}\">{$this_category}<br/>";
							
						

					$output .= "</div>";
					$output .= "<table><tr><td>";
					//$output .= "<input type=\"button\" id=\"leftPreview\" value=\"Preview\" onClick=\"showUpdaterPreview('left', 'preview');\" />";
					/*if ($name_display == "text") {
						$output .= "<br/>";
					}*/
					$output .= "<input type=\"button\" id=\"leftPreview\" value=\"Preview\" onClick=\"setAction('action', 'left', 'form1');\" />";
					$output .= "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"right\" />";
					$output .= "<span id=\"create-report\">";
					if (trim($output_array["wiki"]) == trim($current_article)) {
						$output .= "Stats Bot version is the current version.<script type='text/javascript'>changeable=true;</script>";
					}
					else {
						//$output .= "<input type=\"button\" id=\"create\" value=\"create\" onClick=\"createPage('left', 'player-name', 'create-report', 'right');\" />";
						$output .= "<input type=\"button\" id=\"create\" value=\"create\" onClick=\"setAction('action', 'create', 'form1');\" />";
					}
					$output .= "</span>";
					if ($name_display == "text") {
						$output .= "Page Title:";
					}
					$output .= "<input type=\"{$name_display}\" id=\"player-name\" name=\"player-name\" value=\"{$output_array["name"]}\" />";
					$output .= "<br/>";
					$output .= "<textarea id=\"left\" name=\"left\" cols=\"60\" rows=\"25\" onchange=\"setCreateButton('create-report');\">{$output_array["wiki"]}</textarea>";
					//$output .= "</td><td><input type=\"button\" id=\"copy\" value=\"<<\" onClick=\"promptOldToNew('left', 'right', 'replace-popup');\" /></td><td>";
					$output .= "</td><td>";
					//$output .= "<input type=\"button\" id=\"rightPreview\" value=\"Preview\" onClick=\"showUpdaterPreview('right', 'preview');\" /><br/>";
					$output .= "<input type=\"button\" id=\"rightPreview\" value=\"Preview\" onClick=\"setAction('action', 'right', 'form1');\" />";
					$output .= "<input type=\"button\" id=\"copy\" value=\"choose categories to copy\" onClick=\"showCategoryPopup('category-popup');\" /><br/>";
					$output .= "<textarea id=\"right\" name=\"right\" cols=\"60\" rows=\"25\">";
					//$output .= $raw_text->getArticleText();
					$output .= $current_article;
					$output .= "</textarea>";
					$output .= "</td></tr></table><br/><br/><a name=\"view_preview\"></a><h2>Display Preview</h2><br><a href=\"#editing\">Back to editing</a><input type=\"button\" id=\"create\" value=\"create\" onClick=\"setAction('action', 'create', 'form1');\" /><div id=\"preview\">";
					
					$popts = $wgOut->parserOptions();
					$popts->setTidy(true);
					if ($which_preview == "left") {
						//$p_output = $wgParser->parse($output_array["wiki"], &$page_title, $popts, true, true, null);
						$popts = $wgOut->parserOptions();
						$popts->setTidy(true);
						$page_title = Title::makeTitleSafe( NS_MAIN, $output_array["name"] );
						
						$p_output = $wgParser->parse($output_array["wiki"], &$page_title, $popts, true, true, null);
					}
					else {
						//$p_output = $wgParser->parse($current_article, &$page_title, $popts, true, true, null);
						$popts = $wgOut->parserOptions();
						$popts->setTidy(true);
						$page_title = Title::makeTitleSafe( NS_MAIN, $output_array["name"] );
						
						$p_output = $wgParser->parse($current_article, &$page_title, $popts, true, true, null);
					}
					//$output .= "-{$which_preview}<br/>";
					$output .= $p_output->getText();
					$output .= "</div>";
				}
				else if ($output_array["regular"]) {
					$output .= $output_array["regular"];
				}
				$output .= "</form>";
				/*
				if ($output_array["regular"]) {
					$output .= "{$output_array["regular"]}";
				}
				*/
	
			//$title_string = "Player Stats";
			
			
			//$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"extensions/wikia/Jeff/SpecialMLBPlayerSearch.css?{$wgStyleVersion}\"/>\n");
		
		

			$wgOut->setPageTitle($title);
			$wgOut->addHTML($output . $redirect);
	

		
	}

	
	function getNFLPlayerStats($player_name, $player_id, $do_create_categories) {
		global $wgRequest;

		if (!$player_id) {
			$sql = "SELECT nfl_players_info.*, sum(team) as pro_experience from nfl_players_info JOIN nfl_fumbles_stats ON nfl_players_info.player_id=nfl_fumbles_stats.player_id WHERE player_name = '". str_replace("'", "''", $player_name) ."' AND nfl_fumbles_stats.is_total=1 GROUP BY nfl_players_info.player_id ORDER BY lastname ASC, firstname ASC";
		}
		else {
			$sql = "SELECT nfl_players_info.*, sum(team) as pro_experience from nfl_players_info JOIN nfl_fumbles_stats ON nfl_players_info.player_id=nfl_fumbles_stats.player_id WHERE nfl_players_info.player_id = {$player_id} AND nfl_fumbles_stats.is_total=1 GROUP BY nfl_players_info.player_id ORDER BY lastname ASC, firstname ASC";		
		}
	
		$dbr =& wfGetDB( DB_MASTER );
	
		$result = mysql_query($sql);
		
		$players = array();
		$output = "";
	
		while($row = mysql_fetch_array($result)) {
			$players[] = $row; 
		}
		
		mysql_free_result($result);
		
		
		if (sizeof($players)) {
			if (sizeof($players) > 1) {
				for ($i=0; $i<sizeof($players); $i++) {
					$output .= "<a href=\"/index.php?title=". $wgRequest->getVal("title") . "&playerid={$players[$i]["player_id"]}\">{$players[$i]["player_fullname"]} ({$players[$i]["player_name"]})  {$players[$i]["birthday"]} {$players[$i]["college"]} {$players[$i]["highschool"]} {$players[$i]["height"]} {$players[$i]["weight"]} {$players[$i]["pro_experience"]}</a><br/>";
					$output_array = array("wiki"=>$false,"regular"=>$output,"name"=>false);
				}
				mysql_close($conn);
				return $output_array;
	
			}
			else {
				$player = $players[0];
				
				$player["birthday_array"] = $this->breakdownDate($player["birthday"]);
				$player["birthday"] = $this->buildDate($player["birthday_array"]);
				
				if (trim($player["birthplace"]) == "," || trim($player["birthplace"]) == "" || trim($player["birthplace"]) == "-") {
					$player["birthplace"] = "";       
				}
				
				
				$player["birthplace"] = $this->checkBirthplace($player["birthplace"]);

				
				$output .= "
				Name: {$player["player_name"]}<br/>
				Full Name: {$player["player_fullname"]}<br/>";
				if ($player["nicknames"] != "") {
					$output .= "Nickname: {$player["nicknames"]}<br/>";
				}
				$output .= "
				Position: {$player["position"]}<br/>
				Born: {$player["birthday"]}<br/>
				Birthplace: {$player["birthplace"]}<br/>";
				if ($player["height"] != "-") {
					$height_weight = "Height";
					$hw_values = $player["height"];
				}
				else {
					$hw_values = "";
					$height_weight = "";
				}
				if ($player["weight"] != "0") {
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
				
				$output .= "{$height_weight} {$hw_values}<br/>";
				if ($player["highschool"] != "") {
					$output .= "High School: {$player["highschool"]}<br/>";
				}
				if ($player["college"] != "-") {
					$output .= "College: {$player["college"]}<br/>";
				}
	
				$output .= "Pro Experience: {$player["pro_experience"]}<br/>";
				
				$output .= "<br/>";
				
				
				$wiki_output .= "{| cellpadding=\"0\" cellspacing=\"0\" class=\"player-profile-top\"\n";
				$wiki_output .= "|-\n";
				$wiki_output .= "| class=\"player-profile-image\" | {{Player Profile Image}}\n";
				$wiki_output .= "| valign=\"top\" |\n";
				$wiki_output .= "{| cellpadding=\"0\" cellspacing=\"0\" class=\"player-profile-information\"\n";
				$wiki_output .= "|-\n";
				$wiki_output .= "| '''Full Name:''' {$player["player_fullname"]}\n";
				$wiki_output .= "| '''Primary Position:''' {$player["position"]}\n";
				
				$wiki_output .= "|-\n";
				$wiki_output .= "| '''{$height_weight}''' {$hw_values}\n";
				if ($player["college"] != "-") {
					$wiki_output .= "| '''College:''' {$player["college"]}\n";	
				}
				else {
					$wiki_output .= "|\n";
				}
				$wiki_output .= "|-\n";

				$wiki_output .= "| '''Birthdate:''' {$player["birthday"]}\n";
				if ($player["highschool"] != "") {
					$wiki_output .= "| '''High School:''' {$player["highschool"]}\n";
				}
				else {
					$wiki_output .= "|\n";
				}

		
				$wiki_output .= "|-\n";
				$wiki_output .= "| '''Birthplace:''' {$player["birthplace"]}\n";
				
				if ($player["nicknames"] != "") {
					$wiki_output .= "| '''Nickname:''' {$player["nicknames"]}\n";
				}
				else {
					$wiki_output .= "|\n";
				}
				
				$wiki_output .= "|-\n";
				
				$wiki_output .= "| '''Pro Experience:''' {$player["pro_experience"]} ";
				if ($player["pro_experience"] == "1") {
					$wiki_output .= "year\n";
				}
				else {
					$wiki_output .= "years\n";
				}
				if ($player["hof"] == 1) {
					$wiki_output .= "|-\n";
					
					$wiki_output .= "| '''Hall of Fame'''\n";
				}
				$wiki_output .= "|}\n";
				$wiki_output .= "| width=\"220\" valign=\"top\" | {{Player Profile Rating Box}}\n";
				$wiki_output .= "|}\n";
				$wiki_output .= "<div style=\"float:right;margin:0px 0px 10px 10px; width:160px;\">\n";
				$wiki_output .= "__TOC__\n";
				$wiki_output .= "{{Player Profile Ad}}\n";
				$wiki_output .= "</div>\n";
				
				
				$table_names = array(
					"passing"=>"nfl_passing_stats",
					"rushing"=>"nfl_rushing_stats",
					"receiving"=>"nfl_receiving_stats",
					"fumbles"=>"nfl_fumbles_stats",
					"defense"=>"nfl_defense_stats",
					"interceptions"=>"nfl_interceptions_stats",
					"punts"=>"nfl_puntreturn_stats",
					"kickoffs"=>"nfl_kickreturn_stats",
					"punting"=>"nfl_punting_stats",
					"kicking"=>"nfl_kicking_stats"
				);
				
				$statcat_names = array(
					"passing"=>"Passing Stats",
					"rushing"=>"Rushing Stats",
					"receiving"=>"Receiving Stats",
					"fumbles"=>"Fumble Recovery Stats",
					"defense"=>"Sack/Safety Stats",
					"interceptions"=>"Interception Stats",
					"punts"=>"Punt Return Stats",
					"kickoffs"=>"Kick Return Stats",
					"punting"=>"Punting Stats",
					"kicking"=>"Kicking Stats"
				);

				
				$years = array(
					"cols"=>4,
					"colnames"=>array("year","team","league","games"),
				);
				$stats = array(
					"passing" => array(
						"cols"=>10,
						"colnames"=>array("ATT","CMP","PCT","YDS","YPA","TD","INT","SKD","SKY","RAT"),
						),
					"rushing" => array(
						"cols"=>5,
						"colnames"=>array("ATT","YDS","AVG","TD","LNG"),
						),
					"receiving" => array(
						"cols"=>5,
						"colnames"=>array("REC","YDS","AVG","TD","LNG"),
						),
					"fumbles" => array(
						"cols"=>5,
						"colnames"=>array("TOT","OWR","OPR","YDS","TD"),
						),
					"defense" => array(
						"cols"=>2,
						"colnames"=>array("SK","SFY"),
						),
					"interceptions" => array(
						"cols"=>4,
						"colnames"=>array("INT","YDS","LNG","TD"),
						),
					"punts" => array(
						"cols"=>6,
						"colnames"=>array("RET","YDS","AVG","FC","LNG","TD"),
						),
					"kickoffs" => array(
						"cols"=>5,
						"colnames"=>array("RET","YDS","AVG","LNG","TD"),
						),
					"punting" => array(
						"cols"=>8,
						"colnames"=>array("PT","YDS","LNG","BLK","TB","I20","NET","AVG"),
						),
					"kicking" => array(
						"cols"=>15,
						"colnames"=>array("1-19M","1-19A","20-29M","20-29A","30-39M","30-39A","40-49M","40-49A","50+M","50+A","LNG","FGM","FGA","XPM","XPA"),
						)
			
					);
					
					$stat_sort_discount_factors = array(
						"passing"=>1,
						"rushing"=>1,
						"receiving"=>1,
						"fumbles"=>.1,
						"defense"=>5,
						"interceptions"=>5,
						"punts"=>.5,
						"kickoffs"=>.5,
						"punting"=>.2,
						"kicking"=>.2
					);
	
					$player_stats = array();
					$wiki_stats = array();
					$wiki_stats_sort = array();
					foreach ($stats as $stat_cat=>$stat_array) {
						
									
								
						$sql = "SELECT * from {$table_names[$stat_cat]} WHERE player_id = {$player["player_id"]} ORDER BY is_total ASC, year ASC, league ASC, team DESC";
			
						$dbr =& wfGetDB( DB_MASTER );
					
						$result = mysql_query($sql);
						
						$player_stats[$stat_cat] = array();
						$row_count = 0;
	
						while($row = mysql_fetch_array($result)) {
							
							foreach ($years["colnames"] as $key=>$value) {
								$player_stats[$stat_cat][$row_count][$value] = $row[$value];
							}
	
							foreach ($stat_array["colnames"] as $key=>$value) {
								$player_stats[$stat_cat][$row_count][$value] = $row[$value];
							}
							$row_count++;
							
						}
						mysql_free_result($result);
					}
					foreach ($player_stats as $category=>$local_stats) {
						$wiki_b_output = "";
						$wiki_stats_sort[$category] = 0;
						if (sizeof($local_stats)) {
							$wiki_b_output .= "==={$statcat_names[$category]}===\n";
							$wiki_b_output .= "{| border=\"1\" bordercolor=\"#dcdcdc\" cellpadding=\"2\" cellspacing=\"0\" class=\"player-profile-stats\"\n";
							$wiki_b_output .= "|- class=\"player-profile-stats-header\"\n";

							$output .= "{$category} stats: <br/>";
							$output .= "<table><tr>";
							foreach ($local_stats[0] as $key=>$value) {
								$output .= "<td>{$key}</td>";
								$wiki_b_output .= "!{$key}\n";

							}
							$output .= "</tr>";
							$is_total = false;
							foreach($local_stats as $arrkey=>$arr) {
								$output .= "<tr>";
								
								if ($arr["year"] == "9999") {
									 $wiki_b_output .= "|- class=\"player-profile-stats-total\"\n";
									 $is_total = true;
								}
								else {
									$wiki_b_output .= "|-\n";
									$is_total = false;
								}
				
								$stat_row_count = 0;
								$car_years = "";
								$car_league = "";
								$total_text = "";
								
								if ($is_total) {
									$car_years = $arr["team"];
									$car_league = $arr["league"];
									$total_text = "{$car_years} year {$car_league} career";									
								}

								foreach($arr as $key=>$value) {
									if ($is_total) {
										if ($stat_row_count == 0) {
											$wiki_b_output .= "| colspan='3' | {$total_text}\n";
										}
										elseif ($stat_row_count > 2) {
											if($category != "kicking" && $stat_row_count == 4) {
												$wiki_stats_sort[$category] += $value * $stat_sort_discount_factors[$category];
											}
											elseif($category == "kicking" && ($stat_row_count == 16 || $stat_row_count == 18)) {
												$wiki_stats_sort[$category] += $value * $stat_sort_discount_factors[$category];
											}
											$output.= "<td>{$value}</td>";
											$wiki_b_output .= "| {$value}\n";
										}
									}
									else {
										$output.= "<td>{$value}</td>";
										$wiki_b_output .= "| {$value}\n";
									}
									$stat_row_count++;
								}
								$output .= "</tr>";
							}
							$output .= "</table><br/>";
							$wiki_b_output .= "|}\n";

						}
						$wiki_stats[$category] = $wiki_b_output;
					}
					
					$wiki_stats_output = "";
					
					arsort($wiki_stats_sort);
					
					foreach($wiki_stats_sort as $category=>$score) {
						if ($score > 0) {
							$wiki_stats_output .= "{$wiki_stats[$category]}";
						}
					}
					
					$wiki_mid_output .= "==Trivia==\n";
					$wiki_mid_output .= "\n";
					$wiki_mid_output .= "{{Player Profile Media}}\n";
					$wiki_mid_output .= "\n";
					$wiki_mid_output .= "==See Also==\n";
					$wiki_mid_output .= "\n";
					$wiki_mid_output .= "\n";

					
					$sql = "SELECT * FROM nfl_awards WHERE player_id={$player["player_id"]} ORDER BY year ASC, award_id ASC";
					$dbr =& wfGetDB( DB_MASTER );
				
					$result = mysql_query($sql);
					
					$player_awards = array();
					$row_count = 0;
					$cur_year = 0;
					while($row = mysql_fetch_array($result)) {
						if ($row["year"] != $cur_year) {
							$cur_year = $row["year"];
							$row_count = 0;
						}
						$player_awards[$row["year"]][$row_count] = $row["award"];
						$row_count++;
					}
					/*
					if (sizeof($player_awards)) {
						$output .= "Awards:<br/><table>";
						$wiki_mid_output .= "==Awards==\n";
						foreach ($player_awards as $year=>$awards) {
							$output .= "<tr><td>{$year}:</td><td>&nbsp</td></tr>";
							$wiki_mid_output .= "*{$year}\n";
							foreach ($awards as $key=>$award) {
								$output .= "<tr><td>&nbsp</td><td>{$award}</td></tr>";
								$wiki_mid_output .= "**{$award}\n";
							}
						}
						$output .= "</table>";
					}
					*/
					if (sizeof($player_awards)) {
						$output .= "Awards:<br/><table>";
						$wiki_mid_output .= "==Awards==\n";
						$awards_sorted = array();
						foreach ($player_awards as $year=>$awards) {
							//$output .= "<tr><td>{$year}:</td><td>&nbsp</td></tr>";
							//$wiki_mid_output .= "*{$year}\n";
							foreach ($awards as $key=>$award) {
								//$output .= "<tr><td>&nbsp</td><td>{$award}</td></tr>";
								//$wiki_mid_output .= "**{$award}\n";
								$awards_sorted[$award] .= "{$year}   ";
								
							}
						}
						foreach($awards_sorted as $award=>$years) {
							$wiki_mid_output .= "*Won the {$award} in " . $this->doTextList($years) ."\n";
						}
						
						$output .= "</table>";
					}

					$wiki_mid_output .= "==Categories==\n";
					$wiki_mid_output .= "\n";	

					//$wiki_mid_output .= "{{DEFAULTSORT:".$last_name.", ".$first_name."}}\n";
					$wiki_mid_output .= "{{DEFAULTSORT:".$player["lastname"].", ".$player["firstname"]."}}\n";

					
					$player_teams = array();
					foreach ($player_stats["fumbles"] as $key=>$value) {
						if ($value["year"] != "9999") {
							$player_teams[$this->nflTeamName($value["team"]."_".$value["league"])] = 1;
						}
					}
					
					$sql = "SELECT * FROM nfl_players_hs where player_id={$player["player_id"]}";
					$dbr =& wfGetDB( DB_MASTER );				
					$result = mysql_query($sql);
					$player_highschools = array();
					while($row = mysql_fetch_array($result)) {
						$player_highschools[] = $row["high_school"];
					}
					mysql_free_result($result);
					
					$sql = "SELECT * FROM nfl_players_college where player_id={$player["player_id"]}";
					$dbr =& wfGetDB( DB_MASTER );				
					$result = mysql_query($sql);
					$player_colleges = array();
					while($row = mysql_fetch_array($result)) {
						$player_colleges[] = $row["college"];
					}
					mysql_free_result($result);
					
					$sql = "SELECT * FROM nfl_players_pos where player_id={$player["player_id"]}";
					$dbr =& wfGetDB( DB_MASTER );				
					$result = mysql_query($sql);
					$player_pos = array();
					while($row = mysql_fetch_array($result)) {
						$player_pos[] = $row["position"];
					}
					mysql_free_result($result);
					
					$wiki_output .= "==Biography==\n";

					$wiki_output .= $this->createBiography($player, $player_stats, $wiki_stats_sort, $player_highschools, $player_colleges, $player_awards);
					$wiki_output .= "\n";
					
	
					$wiki_output .= "==Scouting Report==\n";
					$wiki_output .= "\n";
	
					$wiki_output .= "==Statistics==\n";
					$wiki_output .= "\n";

					$categories = array();
					$categories = $this->createPlayerCategories($player, $player_stats, $wiki_stats_sort, $player_highschools, $player_colleges, $player_awards, $player_pos);
					$wiki_category_output = "";
					foreach($categories as $key=>$value) {
						
						$wiki_category_output .= "[[Category: {$value}]]\n";
						//$wiki_category_output .= "{$value}\n\n";
						if ($do_create_categories) {
							//MLBRosterPages::makeCategory($value);
						}
						//**sleep(2);

					}
			}
		}
		$wiki_output .= $wiki_stats_output . $wiki_mid_output . $wiki_category_output;
		
		$output_array = array("wiki"=>$wiki_output,"regular"=>false,"name"=>$player["player_name"]);

		return $output_array;
		
	}
	
	function nflTeamName($abbr) {
		$team_translations = array(
		"AKI_NFL"=>"Akron Indians",
		"AKP_NFL"=>"Akron Pros",
		"AKR_APFA"=>"Akron Pros",
		"ATL_NFL"=>"Atlanta Falcons",
		"BAA_NFL"=>"Buffalo All-Americans",
		"BFB_NFL"=>"Buffalo Bisons",
		"BFR_NFL"=>"Buffalo Rangers",
		"BUF_APFA"=>"Buffalo All-Americans",
		"BAL_NFL"=>"Baltimore Ravens",
		"BSB_AFLG"=>"Boston Bulldogs",
		"BBL_AAFC"=>"Buffalo Bills",
		"BBS_AAFC"=>"Buffalo Bisons",
		"BKD_NFL"=>"Brooklyn Dodgers",
		"BKT_NFL"=>"Brooklyn Tigers",
		"BRK_AFLG"=>"Brooklyn Horsemen",
		"BRK_AAFC"=>"Brooklyn Dodgers",
		"BKL_NFL"=>"Brooklyn Lions",
		"BSY_NFL"=>"Boston Yanks",
		"BUF_NFL"=>"Buffalo Bills",
		"BUF_AFL"=>"Buffalo Bills",
		"C-S_NFL"=>"Cincinnati-St. Louis Reds-Gunners",
		"CAN_APFA"=>"Canton Bulldogs",
		"CAN_NFL"=>"Canton Bulldogs",
		"CAR_NFL"=>"Carolina Panthers",
		"CHI_NFL"=>"Chicago Bears",
		"CHI_AFLG"=>"Chicago Bulls",
		"CHS_APFA"=>"Chicago Staleys",
		"DEC_APFA"=>"Decatur Staleys",
		"CHH_AAFC"=>"Chicago Hornets",
		"CHR_AAFC"=>"Chicago Rockets",
		"CHT_APFA"=>"Chicago Tigers",
		"CLB_NFL"=>"Cleveland Bulldogs",
		"CLI_NFL"=>"Cleveland Indians",
		"CIN_NFL"=>"Cincinnati Bengals",
		"CIN_AFL"=>"Cincinnati Bengals",
		"CLE_NFL"=>"Cleveland Browns",
		"CLE_AAFC"=>"Cleveland Browns",
		"CLE_AFLG"=>"Cleveland Panthers",
		"CLE_APFA"=>"Cleveland Tigers",
		"CIN_APFA"=>"Cincinnati Celts",
		"CNR_NFL"=>"Cincinnati Reds",
		"CLM_APFA"=>"Columbus Panhandles",
		"CMP_NFL"=>"Columbus Panhandles",
		"CMT_NFL"=>"Columbus Tigers",
		"ARI_NFL"=>"Arizona Cardinals",
		"CHC_APFA"=>"Chicago Cardinals",
		"CHC_NFL"=>"Chicago Cardinals",
		"CHP_NFL"=>"chicago/pittsburgh",
		"PHO_NFL"=>"Phoenix Cardinals",
		"SLC_NFL"=>"St. Louis Cardinals",
		"DAL_NFL"=>"Dallas Cowboys",
		"DAY_APFA"=>"Dayton Triangles",
		"DAY_NFL"=>"Dayton Triangles",
		"DEN_NFL"=>"Denver Broncos",
		"DEN_AFL"=>"Denver Broncos",
		"DET_NFL"=>"Detroit Lions",
		"POR_NFL"=>"Portsmouth Spartans",
		"DTW_NFL"=>"Detroit wolverines",
		"DTH_APFA"=>"Detroit Heralds",
		"DTP_NFL"=>"Detroit Panthers",
		"DTT_APFA"=>"Detroit Tigers",
		"DTX_NFL"=>"Dallas Texans",
		"DLE_NFL"=>"Duluth Eskimos",
		"DLK_NFL"=>"Duluth Kelleys",
		"EVA_APFA"=>"Evansville Crimson Giants",
		"EVA_NFL"=>"Evansville Crimson Giants",
		"FRA_NFL"=>"Frankford Yellow Jackets",
		"GNB_NFL"=>"Green Bay Packers",
		"GNB_APFA"=>"Green Bay Packers",
		"HAM_APFA"=>"Hammond Pros",
		"HAM_NFL"=>"Hammond Pros",
		"HAR_NFL"=>"Hartford Blues",
		"IND_NFL"=>"Indianapolis Colts",
		"BAL_AAFC"=>"Baltimore Colts",
		"BLC_NFL"=>"Baltimore Colts",
		"MIA_AAFC"=>"Miami Seahawks",
		"JAC_NFL"=>"Jacksonville Jaguars",
		"KCB_NFL"=>"Kansas City Blues",
		"KCC_NFL"=>"Kansas City Cowboys",
		"KAN_NFL"=>"Kansas City Chiefs",
		"DAL_AFL"=>"Dallas Texans",
		"KAN_AFL"=>"Kansas City Chiefs",
		"KEN_NFL"=>"Kenosha Maroons",
		"LAB_NFL"=>"Los Angeles Buccaneers",
		"LAD_AAFC"=>"Los Angeles Dons",
		"LAW_AFLG"=>"Los Angeles Wildcats",
		"LOU_APFA"=>"Louisville Brecks",
		"LSB_NFL"=>"Louisville Brecks",
		"LSC_NFL"=>"Louisville Colonels",
		"MIA_NFL"=>"Miami Dolphins",
		"MIA_AFL"=>"Miami Dolphins",
		"MIL_NFL"=>"Milwaukee Badgers",
		"MIN_NFL"=>"Minnesota Vikings",
		"MIM_NFL"=>"Minneapolis Marines",
		"MIN_APFA"=>"Minneapolis Marines",
		"MIR_NFL"=>"Minneapolis Red Jackets",
		"MUN_APFA"=>"Muncie Flyers",
		"NYY_AAFC"=>"New York Yankees",
		"NWE_NFL"=>"New England Patriots",
		"BOS_AFL"=>"Boston Patriots",
		"BSP_NFL"=>"Boston Patriots",
		"NRK_AFLG"=>"Newark Bears",
		"NOR_NFL"=>"New Orleans Saints",
		"NYB_NFL"=>"New York Bulldogs",
		"NYB_APFA"=>"New York Giants",
		"NYK_NFL"=>"New York Yanks",
		"NYG_NFL"=>"New York Giants",
		"NYJ_NFL"=>"New York Jets",
		"NYJ_AFL"=>"New York Jets",
		"NYT_AFL"=>"New York Titans",
		"NYY_NFL"=>"New York Yankees",
		"OAK_NFL"=>"Oakland Raiders",
		"LAD_NFL"=>"Los Angeles Raiders",
		"OAK_AFL"=>"Oakland Raiders",
		"OOR_NFL"=>"Oorang Indians",
		"NRK_NFL"=>"Newark Tornadoes",
		"ORA_NFL"=>"Orange Tornadoes",
		"PHI_NFL"=>"Philadelphia Eagles",
		"PHP_NFL"=>"Philadelphia/Pittsburgh",
		"PHI_AFLG"=>"Philadelphia Quakers",
		"PIT_NFL"=>"Pittsburgh Steelers",
		"PIP_NFL"=>"Pittsburgh Pirates",
		"BBD_NFL"=>"Boston Bulldogs",
		"POT_NFL"=>"Pottsville Maroons",
		"PRO_NFL"=>"Providence Steam Roller",
		"RAL_NFL"=>"Racine Legion",
		"RAT_NFL"=>"Racine Tornadoes",
		"RII_AFLG"=>"Rock Island Independents",
		"RII_APFA"=>"Rock Island Independents",
		"RII_NFL"=>"Rock Island Independents",
		"ROC_APFA"=>"Rochester Jeffersons",
		"ROC_NFL"=>"Rochester Jeffersons",
		"SDG_NFL"=>"San Diego Chargers",
		"LAC_AFL"=>"Los Angeles Chargers",
		"SDG_AFL"=>"San Diego Chargers",
		"SEA_NFL"=>"Seattle Seahawks",
		"SFO_NFL"=>"San Francisco 49ers",
		"SFO_AAFC"=>"San Francisco 49ers",
		"SLA_NFL"=>"St. Louis All-Stars",
		"STL_NFL"=>"St. Louis Rams",
		"CLR_NFL"=>"Cleveland Rams",
		"LAM_NFL"=>"Los Angeles Rams",
		"STI_NFL"=>"Staten Island Stapletons",
		"SYR_APFA"=>"Syracuse Pros",
		"TAM_NFL"=>"Tampa Bay Buccaneers",
		"TEN_NFL"=>"Tennessee Titans",
		"HOO_NFL"=>"Houston Oilers",
		"HOU_AFL"=>"Houston Oilers",
		"TNO_NFL"=>"Tennessee Oilers",
		"HOU_NFL"=>"Houston Texans",
		"TOL_NFL"=>"Toledo Maroons",
		"TON_APFA"=>"Tonawanda Kardex",
		"WAS_NFL"=>"Washington Redskins",
		"BBR_NFL"=>"Boston Braves",
		"BSR_NFL"=>"Boston Redskins",
		"WAS_APFA"=>"Washington Senators",
		"NYY_AFLG"=>"New York Yankees"
		);
		
		
		if (isset($team_translations[$abbr])) {
			return $team_translations[$abbr];
		}
		else {
			return "Team Not Found";
		}
	}
	
	
	function createBiography($player, $player_stats, $player_stats_sort, $player_highschools, $player_colleges, $player_awards) {
		$output = "";

                $leagues = array();
                $teams = array();
		$played_2006 = false;

                foreach($player_stats["fumbles"] as $year=>$stats) {
                    if ($stats["year"] != "9999") {
			    if ($stats["year"] == "2006") {
				    $played_2006 = true;
			    }
                        $leagues[$stats["league"]] += 1;
                        $teamstr = $stats["team"]."_".$stats["league"];
                        $teams[$teamstr] += 1;
                    }
                }

                arsort($teams);
		
		$output .= "'''{$player["player_name"]}''' ({$player["player_fullname"]})";
		if ($player["birthday"] != "") {
			$output .= " was born on {$player["birthday"]}";
		}
		if (trim($player["birthplace"]) != "") {
			$output .= " in {$player["birthplace"]}.  ";
		}
		else {
			if ($player["birthday"] != "") {
				$output .= ".  ";
			}
		}
		if (sizeof($player_highschools) > 0) {
		    $output .= "After going to high school at ";
		    $hs_output = "";
		    foreach($player_highschools as $key=>$school) {
			    $hs_output .= "[[:Category: Athletes Who Attended {$school}|{$school}]]   ";
		    }
		    $hs_output = str_replace(", ", ";; ", $hs_output);
		    $hs_output = $this->doTextList($hs_output);
		    $hs_output = str_replace(";; ", ", ", $hs_output);
		    $output .= $hs_output;
		    $output .= ", ";

                }
		if (sizeof($player_colleges) > 0) {
			
  		    $output .= "{$player["lastname"]} attended ";
		    $college_output = "";
		    foreach($player_colleges as $key=>$school) {
			    if (strpos($school, "University") === 0 || strpos($school, "College") === 0) {
				    $school = "the [[{$school}]]";
			    }
			    else {
				    $school = "[[{$school}]]";
			    }
			    $college_output .= "{$school}   ";
		    }
		    $college_output = str_replace(", ", ";; ", $college_output);
		    $college_output = $this->doTextList($college_output);
		    $college_output = str_replace(";; ", ", ", $college_output);
		    $output .= $college_output;
               }
	       
	       $output .= ".  ";
	       

                $teams_output = "{$player["lastname"]} made his professional debut in the [[{$player_stats["fumbles"][0]["league"]}]] in [[{$player_stats["fumbles"][0]["year"]} {$player_stats["fumbles"][0]["league"]}|{$player_stats["fumbles"][0]["year"]}]] with the [[". $this->nflTeamName($player_stats["fumbles"][0]["team"]."_".$player_stats["fumbles"][0]["league"]) . "]].  ";
                
                $teams_output_list = "";
		$league_count = 0;
                if (sizeof($leagues) > 1) {
                   $teams_output .= "He played in the ";
                   foreach ($leagues as $league=>$years) {
			if ($league_count == 0) {
			   $teams_output .= "[[{$league}]] for {$years} years, playing for the ";
			}
			else {
			   $teams_output .= "{$player["lastname"]} also played in the [[{$league}]] for {$years} years, playing for the ";
			}
			$teams_output_list = "";
			$team_count = 0;
                        foreach ($teams as $code=>$teamyears) {
                            if (strpos($code, "_".$league)) {
				    $teams_output_list .= "[[" . $this->nflTeamName($code) . "]]   ";
				    $team_count++;
                            }
                        }
                        $teams_output_list .= ";;";
			
			   if ($team_count == 1) {
				$repl_str = " the entire time.  ";
				$teams_output .= str_replace("   ;;", $repl_str, $teams_output_list);
			   }
			    elseif ($team_count > 1) {
				    $repl_str = " over the course of his {$years} year [[{$league}]] career.  ";
				    $teams_output_list = str_replace("   ;;", $repl_str, $teams_output_list);
				    $teams_output_list = $this->doTextList($teams_output_list);
				$teams_output .= $teams_output_list;
	
			    }
			
 			$league_count++;
                    }
                }
                else {
			if ($played_2006) {
				$has_text = "has "; 
			}
			else {
				$has_text = "";
			}
			$teams_output .= "He {$has_text}played for the ";
                    
                    foreach ($teams as $code=>$teamyears) {
                        if (strpos($code, "_".$league)) {
                            $teams_output_list .= "[[" . $this->nflTeamName($code) . "]]   ";
                        }
                    }
                    $teams_output_list .= ";;";

                    if (sizeof($teams) == 1) {
                        $repl_str = " for his entire {$player["pro_experience"]} year career.  ";
                        $teams_output .= str_replace("   ;;", $repl_str, $teams_output_list);
                    }
                    elseif (sizeof($teams) > 1) {
                        $repl_str = " over the course of his {$player["pro_experience"]} year career.  ";
                        $teams_output_list = str_replace("   ;;", $repl_str, $teams_output_list);
			$teams_output_list = $this->doTextList($teams_output_list);
                       $teams_output .= $teams_output_list;

                    }
               }
                $output .= str_replace("1 years", "1 year", $teams_output);
		
		
		$stat_criteria = array();
		$stat_criteria["passing"] = array(
			"YDS"=>.05,
			"TD"=>6,
			"INT"=>-2);
		$stat_criteria["rushing"] = array(
			"YDS"=>.1,
			"TD"=>6);
		$stat_criteria["receiving"] = array(
			"YDS"=>.1,
			"TD"=>6,
			"REC"=>.1);
		$stat_criteria["interceptions"] = array(
			"INT"=>4,
			"YDS"=>.05,
			"TD"=>6);
		$stat_criteria["punts"] = array(
			"YDS"=>.04,
			"TD"=>6);
		$stat_criteria["kickoffs"] = array(
			"YDS"=>.04,
			"TD"=>6);
		$stat_criteria["defense"] = array(
			"SK"=>5,
			"SFY"=>5);
		$stat_criteria["fumbles"] = array(
			"OPR"=>1,
			"YDS"=>.1,
			"TD"=>6);
		$stat_criteria["punting"] = array(
			"I20"=>.1,
			"AVG"=>.1);
		$stat_criteria["kicking"] = array(
			"1-19M"=>3,
			"1-19A"=>-2,
			"20-29M"=>3,
			"20-29A"=>-2.,
			"30-39M"=>3,
			"30-39A"=>-1,
			"40-49M"=>4,
			"40-49A"=>-1,
			"50-59M"=>5,
			"50-59A"=>-1,
			"XPM"=>1.25,
			"XPA"=>-1);
			

			
		$high = 0;
		$year = 0;
		$total = 0;
		$year_key = 0;
		
		$rookie_year = 0;
		$rookie_year_key = 0;
		$last_cat = "";
	
		$last_year = 0;
		for ($i=0; $i<sizeof($player_stats["fumbles"]); $i++) {
			$total = 0;
			foreach($player_stats_sort as $category=>$number) {
				$cat_total = 0;
				if (isset($stat_criteria[$category])) {
					$criteria = $stat_criteria[$category];
					if (isset($player_stats[$category][$i]["year"])) {
						if (($player_stats[$category][$i]["year"] != $last_year || $category != $last_cat) && $player_stats[$category][$i]["year"] != "9999") {
							$last_year = $player_stats[$category][$i]["year"];
							$last_cat = $category;
							foreach($criteria as $key=>$value) {
								$total += $player_stats[$category][$i][$key]*$value;
								$cat_total += $player_stats[$category][$i][$key]*$value;
							}                    
						}
					}
				}
				else {
				}
				$last_cat = $category;

			}
				if ($total > $high) {
					$high = $total;
					$year = $last_year;
					$year_key = $i;
					$last_cat = $category;
				}

		}
		
		$stat_disp_thresh = array();
		$stat_disp_thresh["passing"] = array(
			"YDS"=>(isset($player_stats["passing"][$year_key]["YDS"]) && ($player_stats["passing"][$year_key]["YDS"] >= 2000)) ? "threw for {$player_stats["passing"][$year_key]["YDS"]} yards   " : "",
			"TD"=>(isset($player_stats["passing"][$year_key]["TD"]) && ($player_stats["passing"][$year_key]["TD"] >= 10)) ? "tossed {$player_stats["passing"][$year_key]["TD"]} TDs   " : "",
			"RAT"=>(isset($player_stats["passing"][$year_key]["ATT"]) &&isset($player_stats["passing"][$year_key]["RAT"]) && ($player_stats["passing"][$year_key]["RAT"] >= 85) && ($player_stats["passing"][$year_key]["ATT"] >= 200)) ? "put up a {$player_stats["passing"][$year_key]["RAT"]} passer rating   " : ""
			);
			
		$stat_disp_thresh["rushing"] = array(
			"YDS"=>(isset($player_stats["rushing"][$year_key]["YDS"]) && ($player_stats["rushing"][$year_key]["YDS"] >= 500)) ? "ran for {$player_stats["rushing"][$year_key]["YDS"]} yards   " : "",
			"TD"=>(isset($player_stats["rushing"][$year_key]["TD"]) && ($player_stats["rushing"][$year_key]["TD"] >= 4)) ? "ran in {$player_stats["rushing"][$year_key]["TD"]} TDs   " : "",
			"AVG"=>(isset($player_stats["rushing"][$year_key]["ATT"]) &&isset($player_stats["rushing"][$year_key]["AVG"]) && ($player_stats["rushing"][$year_key]["AVG"] >= 5) && ($player_stats["rushing"][$year_key]["ATT"] >= 150)) ? "ran for an average of {$player_stats["rushing"][$year_key]["AVG"]} yards per carry   " : ""
			);
			
		$stat_disp_thresh["receiving"] = array(
			"REC"=>(isset($player_stats["receiving"][$year_key]["REC"]) && ($player_stats["receiving"][$year_key]["REC"] >= 35)) ? "hauled in {$player_stats["receiving"][$year_key]["REC"]} receptions   " : "",
			"YDS"=>(isset($player_stats["receiving"][$year_key]["YDS"]) && ($player_stats["receiving"][$year_key]["YDS"] >= 500)) ? "had {$player_stats["receiving"][$year_key]["YDS"]} receiving yards   " : "",
			"TD"=>(isset($player_stats["receiving"][$year_key]["TD"]) && ($player_stats["receiving"][$year_key]["TD"] >= 4)) ? "caught {$player_stats["receiving"][$year_key]["TD"]} TD passes   " : ""
			);
			
		$stat_disp_thresh["interceptions"] = array(
			"INT"=>(isset($player_stats["interceptions"][$year_key]["INT"]) && ($player_stats["interceptions"][$year_key]["INT"] >= 3)) ? "picked off {$player_stats["interceptions"][$year_key]["INT"]} passes   " : "",
			"TD"=>(isset($player_stats["interceptions"][$year_key]["TD"]) && ($player_stats["interceptions"][$year_key]["TD"] >= 1)) ? "ran back {$player_stats["interceptions"][$year_key]["TD"]} interceptions for touchdowns   " : ""
			);
			
		$stat_disp_thresh["punts"] = array(
			"YDS"=>(isset($player_stats["punts"][$year_key]["YDS"]) && ($player_stats["punts"][$year_key]["YDS"] >= 400)) ? "had {$player_stats["punts"][$year_key]["YDS"]} punt return yards   " : "",
			"TD"=>(isset($player_stats["punts"][$year_key]["TD"]) && ($player_stats["punts"][$year_key]["TD"] >= 1)) ? "ran back {$player_stats["punts"][$year_key]["TD"]} punts for touchdowns   " : ""
			);
			
		$stat_disp_thresh["kickoffs"] = array(
			"YDS"=>(isset($player_stats["kickoffs"][$year_key]["YDS"]) && ($player_stats["kickoffs"][$year_key]["YDS"] >= 400)) ? "had {$player_stats["kickoffs"][$year_key]["YDS"]} kick return yards   " : "",
			"TD"=>(isset($player_stats["kickoffs"][$year_key]["TD"]) && ($player_stats["kickoffs"][$year_key]["TD"] >= 1)) ? "ran back {$player_stats["kickoffs"][$year_key]["TD"]} kickoffs for touchdowns   " : ""
			);
			
		$stat_disp_thresh["defense"] = array(
			"SK"=>(isset($player_stats["defense"][$year_key]["SK"]) && ($player_stats["defense"][$year_key]["SK"] >= 5)) ? "recorded {$player_stats["defense"][$year_key]["SK"]} sacks   " : ""
			);
			
		$stat_disp_thresh["fumbles"] = array(
			"OPR"=>(isset($player_stats["fumbles"][$year_key]["OPR"]) && ($player_stats["fumbles"][$year_key]["OPR"] >= 3)) ? "recovered {$player_stats["fumbles"][$year_key]["OPR"]} opponents fumbles   " : "",
			"TD"=>(isset($player_stats["fumbles"][$year_key]["TD"]) && ($player_stats["fumbles"][$year_key]["TD"] >= 1)) ? "ran back {$player_stats["fumbles"][$year_key]["TD"]} fumbles for touchdowns   " : ""
			);
			
		$stat_disp_thresh["punting"] = array(
			"I20"=>(isset($player_stats["punting"][$year_key]["I20"]) && ($player_stats["punting"][$year_key]["I20"] >= 15)) ? "dropped {$player_stats["punting"][$year_key]["I20"]} punts inside the 20   " : "",
			"AVG"=>(isset($player_stats["punting"][$year_key]["PT"]) &&isset($player_stats["punting"][$year_key]["AVG"]) && ($player_stats["punting"][$year_key]["AVG"] >= 40) && ($player_stats["punting"][$year_key]["PT"] >= 25)) ? "had a {$player_stats["punting"][$year_key]["AVG"]} yard punt average   " : ""
			);
			
		$stat_disp_thresh["kicking"] = array(
			"FGM"=>(isset($player_stats["kicking"][$year_key]["FGM"]) && ($player_stats["kicking"][$year_key]["FGM"] >= 20)) ? "made {$player_stats["kicking"][$year_key]["FGM"]} field goals   " : "",
			"50-59M"=>(isset($player_stats["kicking"][$year_key]["50-59M"]) && ($player_stats["kicking"][$year_key]["50-59M"] >= 3)) ? "nailed {$player_stats["kicking"][$year_key]["50-59M"]} fg of 50 or more yards   " : ""
			);
		
		$stat_display_output = "";
		foreach($stat_disp_thresh as $category=>$displays) {
			foreach ($displays as $stat=>$text) {
				if ($text != "") {
					$text = str_replace("ran back 1 punts for touchdowns", "ran back a punt for a touchdown", $text);
					$text = str_replace("ran back 1 kickoffs for touchdowns", "ran back a kickoff for a touchdown", $text);
					$text = str_replace("ran back 1 fumbles for touchdowns", "ran back a fumble for a touchdown", $text);
					$text = str_replace("ran back 1 interceptions for touchdowns", "ran back an interception for a touchdown", $text);
					$stat_display_output .= "{$text} ";
				}
			}
		}
		
		if ($stat_display_output != "") {
			$stat_display_output = $this->doTextList($stat_display_output);

		}
		
	
		$output .= "\n\n";
		if ($year && sizeof($player_stats["fumbles"]) > 2) {
			$output .= "Most people believe that {$year} was {$player[player_name]}'s best year";
			
			if ($stat_display_output != "") {
				$output .= ", as he {$stat_display_output}";
			}
					
			$output .= ".\n";
		}
		
		
		
		return $output;

	}
	
	function doTextList($list) {
		$list = str_replace("   ", ", ", trim($list));
			
		//$stats_display_string = strrev(str_replace(" ,", " dna ", strrev($stats_display_string)));
		//$stats_display_string = strrev(substr(strrev($stats_display_string), 0, strpos(strrev($stats_display_string), " ,")) . " dna " . substr(strrev($stats_display_string), strpos(strrev($stats_display_string), " ,") + strlen(" ,")));
		if (strpos(strrev($list), " ,")) {
			$list = strrev(substr(strrev($list), 0, strpos(strrev($list), " ,")) . " dna " . substr(strrev($list), strpos(strrev($list), " ,") + strlen(" ,")));
		}
		
		return $list;
	}
	
	function breakDownDate($date) {
		
		$month = false;
		$day = false;
		$year = false;
		
		$start = 0;
		$end = strpos($date, "/");
		if ($end) {
			$month = substr($date, $start, $end);
			$date = substr($date, $end+strlen("/"));
			$end = strpos($date, "/");
			if ($end) {
				$day = substr($date, $start, $end);
				$date = substr($date, $end+strlen("/"));
				$end = strlen($date);
				if ($end) {
					$year = $date;
				}
			}
		}
		
		if (intval($month) < 10) {
			$month = "0" . $month;
		}
		/*
		if (intval($day) < 10) {
			$day = "0" . $day;
		}
		*/
		
		$return_vals = array(
			"month"=>$month,
			"day"=>$day,
			"year"=>$year
		);
		
		return $return_vals;
	}
	
	function buildDate($date_array) {
		
		$stats_months = array(
			"01"=>"January",
			"02"=>"February",
			"03"=>"March",
			"04"=>"April",
			"05"=>"May",
			"06"=>"June",
			"07"=>"July",
			"08"=>"August",
			"09"=>"September",
			"10"=>"October",
			"11"=>"November",
			"12"=>"December"
		);
		
		if($date_array["month"] && $date_array["day"] && $date_array["year"]) {
			return "{$stats_months[$date_array["month"]]} {$date_array["day"]}, {$date_array["year"]}"; 
		}
		
		return "";
		
	}
	
	
	function createPlayerCategories($player, $player_stats, $player_stats_sort, $player_highschools, $player_colleges, $player_awards, $player_pos) {
	
		$last_year = 0;
		$leagues = array();
                $teams = array();
		$teams_years = array();
		
		$count = 0;
		$first_team;

 
		if(isset($player_stats["fumbles"][0]["year"])) {
			foreach($player_stats["fumbles"] as $key=>$stats) {
				if ($stats["year"] < 9999) {
					$last_year = $stats["year"];
					$leagues[$stats["league"]] += 1;
					$teamstr = $stats["team"]."_".$stats["league"];
					//$teams[$this->nflTeamName($teamstr)] += 1;
					$teamstr = $this->nflTeamName($teamstr);
					$teams[$teamstr] += 1;
					//$team_years_string = "{$stats["year"]} " . $this->nflTeamName($teamstr);
					$team_years_string = "{$stats["year"]} " . $teamstr;
					$teams_years[$team_years_string] = "1";
					if ($count == 0) {
						//$first_team = $this->nflTeamName($teamstr);
						$first_team = $teamstr;
					}
					$count++;
				}
			}
		}
	               
		arsort($teams);
	
		$position_names = array(		
			"E"=>array("Ends","Offensive Players"),
			"FB"=>array("Fullbacks","Running Backs","Offensive Players"),
			"HB"=>array("Halfbacks","Running Backs","Offensive Players"),
			"QB"=>array("Quarterbacks","Offensive Players"),
			"K"=>array("Kicker","Special Teams Players"),
			"RB"=>array("Running Backs","Offensive Players"),
			"DB"=>array("Defensive Backs","Defensive Players"),
			"LB"=>array("Linebackers","Defensive Players"),
			"CB"=>array("Cornerbacks","Defensive Players"),
			"S"=>array("Safeties","Defensive Players"),
			"FL"=>array("Flankers","Wide Receivers","Offensive Players"),
			"SE"=>array("Split Ends","Wide Receivers","Offensive Players"),
			"WR"=>array("Wide Receivers","Offensive Players"),
			"OT"=>array("Offensive Tackles","Offensive Linemen","Offensive Players"),
			"T"=>array("Tackles","Offensive Linemen","Offensive Players"),
			"G"=>array("Guards","Offensive Linemen","Offensive Players"),
			"DHB"=>array("Defensive Halfbacks","Defensive Backs","Defensive Players"),
			"C"=>array("Centers","Offensive Linemen","Offensive Players"),
			"NT"=>array("Nose Tackles","Defensive Linemen","Defensive Players"),
			"DT"=>array("Defensive Tackles","Defensive Linemen","Defensive Players"),
			"DE"=>array("Defensive Ends","Defensive Linemen","Defensive Players"),
			"MG"=>array("Middle Guards","Defensive Linemen","Defensive Players"),
			"OG"=>array("Offensive Guards","Offensive Linemen","Offensive Players"),
			"TE"=>array("Tight Ends","Offensive Players"),
			"B"=>array("Backs","Running Backs","Offensive Players"),
			"DG"=>array("Defensive Guards","Defensive Linemen","Defensive Players"),
			"P"=>array("Punters","Special Teams Players"),
			"OHB"=>array("Offensive Halfbacks","Halfbacks","Running Backs","Offensive Players"),
			"OE"=>array("Offensive Ends","Offensive Linemen","Offensive Players"),
			"DL"=>array("Defensive Linemen","Defensive Players"),
			"OL"=>array("Offensive Linemen","Offensive Players"),
			"SB"=>array("Slotbacks","Running Backs","Offensive Players"),
			"OB"=>array("Offensive Backs","Running Backs","Offensive Players"),
			"BK"=>array("Backs","Running Backs","Offensive Players"),
			"U"=>"",
			"NULL"=>"",
			"HBK"=>array("Halfbacks","Running Backs","Offensive Players"),
			"IR"=>"",
			"BQ"=>"",
			"NG"=>array("Nose Guards","Defensive Linemen","Defensive Players"),
			"H"=>"",
			"O"=>"",
			"WB"=>"",
			"D?"=>"",
			"EHB"=>"",
			"TB"=>"",
			"OFF"=>"",
			"QN"=>"",
			"REC"=>array("Wide Receivers","Offensive Players"),
			"DS"=>array("Safeties","Defensive Players"),
			"KR"=>array("Kick Returners","Special Teams Players"),
			"LS"=>"",
			"BB"=>"",
			"GB"=>"",
			"EFB"=>""
		);
		
		$categories = array();
		//Athlete categories
		$categories[] = "Athletes";
		$categories[] = "Football Players";
		
		foreach ($leagues as $key=>$value) {
			$categories[] = "{$key} Players";
		}
		foreach ($teams as $key=>$value) {
			$categories[] = "{$key} Players";
		}
		foreach ($teams_years as $key=>$value) {
			$categories[] = "{$key} Players";
		}

		foreach($player_highschools as $blah=>$hs) {
			$categories[] = "Athletes Who Attended {$hs}";
			$categories[] = "Football Players Who Attended {$hs}";
			foreach ($leagues as $key=>$value) {
				$categories[] = "{$key} Players Who Attended {$hs}";
			}
		}

		foreach($player_colleges as $blah=>$college) {
			if (strpos($college, "University") === 0 || strpos($college, "College") === 0) {
				$college = "The {$college}";
			}
			$categories[] = "Athletes Who Attended {$college}";
			$categories[] = "Football Players Who Attended {$college}";
			foreach ($leagues as $key=>$value) {
				$categories[] = "{$key} Players Who Attended {$college}";
			}
		}

		$award_count = array();
		foreach($player_awards as $year=>$awards) {
			foreach ($awards as $garbage=>$award) {
				$award_count[$award]++;
			}
		}
		foreach($award_count as $award=>$count) {
			for ($i=1; $i<10; $i++) {
				if ($count >= $i) {
					if ($i==1) {
						$categories[] = $award;
						$plural = "";
					}
					else {
						$plural = "s";
					}
					$categories[] = "Football Players Who Have Won The {$award} At Least {$i} Time{$plural}";
				}
			}
		}
		
		if ($last_year > 0 && $last_year < 2006) {
			$categories[] = "Retired Athletes";
			$categories[] = "Retired Football Players";
		}
		
		if ($player["hof"]) {
			$categories[] = "Athletes in the Hall of Fame";
			$categories[] = "Football Players in the Hall of Fame";
		}
		// position categories
		foreach ($player_pos as $key=>$value) {
			if (is_array($position_name[$value])) {
				foreach($position_name[$value] as $poskey=>$posvalue) {
					$categories[] = $posvalue;
				}
			}
		}
		
		if ($player["lastname"] != "") {
			$categories[] = "Athletes with the Last Name {$player["lastname"]}";
			$categories[] = "Football Players with the Last Name {$player["lastname"]}";
		}
		if ($player["firstname"] != "") {
			$categories[] = "Athletes with the First Name {$player["firstname"]}";
			$categories[] = "Football Players with the First Name {$player["firstname"]}";
		}
		$birthday = MLBPlayerUpdater::breakDownDate($player["birthday"]);
		if ($birthday["month"] && $birthday["day"] && $birthday["year"]) {
			if ($birthday["month"]) {
				$categories[] = "Athletes Born in {$birthday["month"]}";
				$categories[] = "Football Players Born in {$birthday["month"]}";
				if ($birthday["day"]) {
					$categories[] = "Athletes Born on {$birthday["month"]} {$birthday["day"]}";
					$categories[] = "Football Players Born on {$birthday["month"]} {$birthday["day"]}";
				}
			}
			if ($birthday["year"]) {
				$categories[] = "Athletes Born in {$birthday["year"]}";
				$categories[] = "Football Players Born in {$birthday["year"]}";
				if ($birthday["month"]) {
					$categories[] = "Athletes Born in {$birthday["month"]} {$birthday["year"]}";
					$categories[] = "Football Players Born in {$birthday["month"]} {$birthday["year"]}";
						if ($birthday["day"]) {
							$categories[] = "Athletes Born on {$birthday["month"]} {$birthday["day"]}, {$birthday["year"]}";
							$categories[] = "Football Players Born on {$birthday["month"]} {$birthday["day"]}, {$birthday["year"]}";
						}
				}
		
			}
		}
		
		if ($player["birthplace"] != "") {
			
			$categories[] = "Athletes Born in {$player["birthplace"]}";
			$categories[] = "Football Players Born in {$player["birthplace"]}";
	
			$start = strpos($player["birthplace"], ", ");
			if ($start) {
				$state = substr($player["birthplace"], $start+ strlen(", "));
				$categories[] = "Athletes Born in {$state}";
				$categories[] = "Football Players Born in {$state}";
			}
			
		}
		
		$categories[] = "Football Players Who Debuted with the {$first_team}";
		
		$experience_criteria = array(
			"pro_experience"=>array(5,10,15,20,25,30)
		);
		
		foreach ($experience_criteria as $field=>$nums) {
			foreach($nums as $key=>$value) {
				if ($player[$field] >= $value) {
					$categories[] = "Players with {$value} years experience in Professional Football";
					$categories[] = "Football Players with {$value} years experience in the Pros";
				}
			}
		}
		
		$category_text = array();
		$category_text["passing"] = array(
			"ATT"=>"Pass Attempts",
			"CMP"=>"Pass Completions",
			"PCT"=>"Percent Completion Percentage",
			"YDS"=>"Passing Yards",
			"TD"=>"Passing Touchdowns",
			"INT"=>"Interceptions",
			"SKD"=>"Times Sacked",
			"RAT"=>"Rating",
		);
		$category_text["rushing"] = array(
			"ATT"=>"Rushing Attempts",
			"YDS"=>"Rushing Yards",
			"TD"=>"Rushing Touchdowns"
			);
		$category_text["receiving"] = array(
			"REC"=>"Receptions",
			"YDS"=>"Receiving Yards",
			"TD"=>"Receiving Touchdowns"
			);
			
		$category_text["fumbles"] = array(
			"OPR"=>"Opponent Fumble Recoveries",
			"TD"=>"Fumble Returns for Touchdowns"
			);					
		$category_text["defense"] = array(
			"SK"=>"Sacks",
			"SFY"=>"Safeties"
			);
		$category_text["interceptions"] = array(
			"INT"=>"Interceptions",
			"TD"=>"Interceptions Returned for Touchdowns"
			);					
		$category_text["punts"] = array(
			"RET"=>"Punt Returns",
			"YDS"=>"Punt Return Yards",
			"TD"=>"Punts Returned for Touchdowns"
			);					
		$category_text["kickoffs"] = array(
			"RET"=>"Kickoff Returns",
			"YDS"=>"Kickoff Return Yards",
			"TD"=>"Kickoff Returns for Touchdowns"
			);
		$category_text["punting"] = array(
			"PT"=>"Punts",
			"YDS"=>"Punt Yards",
			"I20"=>"Punts Inside the 20",
			"AVG"=>"Average Yards Per Punt"
			);					
		$category_text["kicking"] = array(
			"50+M"=>"Field Goals Made of 50 or More Yards",
			"FGM"=>"Field Goals Made",
			"FGA"=>"Field Goals Attempted",
			"XPM"=>"Extra Points Made"
			);

		
		$category_milestones = array();
		$category_milestones["passing"] = array(
			"minimum"=>array("ATT"=>100),
			"ATT"=>array(250,500,1000,1500,2000,2500,3000,3500,4000,5000,7500,10000),
			"CMP"=>array(100,250,500,750,1000,1500,2000,2500,3000,3500,4000,4500,5000),
			"PCT"=>array(45,50,55,60,65,70,75,80),
			"YDS"=>array(10000,20000,25000,30000,35000,40000,45000,50000,55000,60000),
			"TD"=>array(10,25,50,100,150,200,250,300,350,400,450,500),
			"INT"=>array(10,25,50,100,150,200,250,300,350,500),
			"SKD"=>array(25,50,100,150,200,250,300,350,500),
			"RAT"=>array(80,90,100,110,120,130)
		);
		$category_milestones["rushing"] = array(
			"minimum"=>array("ATT"=>100),
			"ATT"=>array(100,250,500,1000,1500,2000,2500,3000,3500,4000,4500,5000),
			"YDS"=>array(1000,2500,5000,7500,10000,12500,15000,17500,20000),
			"TD"=>array(10,25,50,75,100,125,150,175)
			);
		$category_milestones["receiving"] = array(
			"minimum"=>array("REC"=>100),
			"REC"=>array(100,250,500,1000,1500,2000,2500,3000,3500,4000,4500,5000),
			"YDS"=>array(1000,2500,5000,7500,10000,12500,15000,17500,20000),
			"TD"=>array(10,25,50,75,100,125,150,175)
			);
		$category_milestones["fumbles"] = array(
			"minimum"=>array("OPR"=>1),
			"OPR"=>array(1,5,10,15,20),
			"TD"=>array(1,5,10,15,20)
			);					
		$category_milestones["defense"] = array(
			"minimum"=>array("SK"=>0),
			"SK"=>array(25,50,100,150,200),
			"SFY"=>array(1,5,10)
			);					
		$category_milestones["interceptions"] = array(
			"minimum"=>array("INT"=>1),
			"INT"=>array(10,25,40,55,70,80),
			"TD"=>array(5,10)
			);
		$category_milestones["punts"] = array(
			"minimum"=>array("RET"=>1),
			"RET"=>array(25,50,100,200,300,400),
			"YDS"=>array(1000,2000,3000,4000),
			"TD"=>array(1,5,10,15,20)
			);
		$category_milestones["kickoffs"] = array(
			"minimum"=>array("RET"=>1),
			"RET"=>array(25,50,100,200,300,400,500,600),
			"YDS"=>array(1000,2000,3000,4000,5000,7500,10000),
			"TD"=>array(1,5,10,15,20)
			);
		$category_milestones["punting"] = array(
			"minimum"=>array("PT"=>10),
			"PT"=>array(250,500,1000,1500),
			"YDS"=>array(5000,10000,20000,30000,50000),
			"I20"=>array(50,100,200,300,400),
			"AVG"=>array(35,40,45,50,55,60)
			);
		$category_milestones["kicking"] = array(
			"minimum"=>array("FGA"=>1),
			"50+M"=>array(5,10,20,30,40),
			"FGM"=>array(50,100,200,300,400,500),
			"FGA"=>array(100,200,300,400,500,600),
			"XPM"=>array(100,250,400,500,600,800)
			);
			
		//$output = "";
		foreach ($player_stats_sort as $field=>$nums) {
			foreach($player_stats[$field] as $year=>$stats) {
				if ($stats["year"] == 9999) {
					$check_stats = false;
					foreach($category_milestones[$field] as $key=>$value) {
						//$output .= $key;
						if ($key == "minimum") {
							foreach($value as $minkey=>$minvalue) {
								//$output .= $minkey;
								if ($stats[$minkey] >= $minvalue) {
									$check_stats = true;
								}
								else {
									$check_stats = false;
								}
							}
						}
						else {
							foreach($value as $mkey=>$mvalue) {
								
	
								if ($stats[$key] >= $mvalue && $check_stats) {
									$categories[] = "{$stats["league"]} Players with at least {$mvalue} {$category_text[$field][$key]}";
										//$categories[] = "Baseball Players with {$key} {$value} in the Major Leagues";
								}
							}
						}
					}
				}
			}
		}
		//$categories[] = $output;
		
		if (isset($player_stats["kicking"][0]["year"])) { 
			foreach($player_stats["kicking"] as $year=>$stats) {
				if ($stats["year"] == 9999) {
					if($stats["LNG"]>=60) {
						$categories[] = "Football Players Who Have Made a FG of 60 or more Yards";
					}
				}
			}
		}
		$rush_70 = array();
		$rush_80 = array();
		$rush_90 = array();
		if (isset($player_stats["rushing"][0]["year"])) { 
			foreach($player_stats["rushing"] as $year=>$stats) {
				if(intval(str_replace("t","",$stats["LNG"]))>=70 && !$rush_70[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Rush of 70 or more Yards";
					$rush_70[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=80 && !$rush_80[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Rush of 80 or more Yards";
					$rush_80[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=90 && !$rush_90[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Rush of 90 or more Yards";
					$rush_90[$stats["league"]] = true;
				}
			}
		}

		$rec_70 = array();
		$rec_80 = array();
		$rec_90 = array();
		if (isset($player_stats["receiving"][0]["year"])) { 
			foreach($player_stats["receiving"] as $year=>$stats) {
				if(intval(str_replace("t","",$stats["LNG"]))>=70 && !$rec_70[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Reception of 70 or more Yards";
					$rec_70[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=80 && !$rec_80[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Reception of 80 or more Yards";
					$rec_80[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=90 && !$rec_90[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Reception of 90 or more Yards";
					$rec_90[$stats["league"]] = true;
				}
			}
		}
		
		$kr_80 = array();
		$kr_90 = array();
		$kr_100 = array();
		if (isset($player_stats["kickoffs"][0]["year"])) { 
			foreach($player_stats["kickoffs"] as $year=>$stats) {
				if(intval(str_replace("t","",$stats["LNG"]))>=80 && !$kr_80[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Kickoff Return of 80 or more Yards";
					$kr_80[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=90 && !$kr_90[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Kickoff Return of 90 or more Yards";
					$kr_90[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=100 && !$kr_100[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Kickoff Return of 100 or more Yards";
					$kr_100[$stats["league"]] = true;
				}
			}
		}		

		$pr_70 = array();
		$pr_80 = array();
		$pr_90 = array();
		if (isset($player_stats["punts"][0]["year"])) { 
			foreach($player_stats["punts"] as $year=>$stats) {
				if(intval(str_replace("t","",$stats["LNG"]))>=70 && !$pr_70[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Punt Return of 70 or more Yards";
					$pr_70[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=80 && !$pr_80[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Punt Return of 80 or more Yards";
					$pr_80[$stats["league"]] = true;
				}
				if(intval(str_replace("t","",$stats["LNG"]))>=90 && !$pr_90[$stats["league"]]) {
					$categories[] = "{$stats["league"]} Players Who Had a Punt Return of 90 or more Yards";
					$pr_90[$stats["league"]] = true;
				}
			}
		}
		
		$seasonal_yardage_thresh = array(1,5,10,15,20);

		$yearly_stat_thresh_text = array(
			"rushing"=>"Rushing",
			"receiving"=>"Receiving",
			"passing"=>"Passing",
			"punts"=>"Punt Return",
			"kickoffs"=>"Kickoff Return"
			);
		$yearly_stat_threshes = array(
			"rushing"=>array(1000,1500,2000),
			"receiving"=>array(1000,1500,2000),
			"passing"=>array(3000,4000,5000),
			"punts"=>array(750,1000,1500),
			"kickoffs"=>array(750,1000,1500)
			);
		$yc_array = array();
			
		foreach($yearly_stat_threshes as $category=>$threshes) {
			foreach($threshes as $garbage=>$thresh) {
				//$yc_array[$category][$thresh] = 0;
				if (isset($player_stats[$category][0]["year"])) { 
					foreach($player_stats[$category] as $year=>$stats) {
						if ($stats["year"] < 9999) {
							if(intval(str_replace("t","",$stats["YDS"]))>=$thresh) {
								$categories[] = "{$stats["league"]} Players Who Had a {$thresh} Yard {$yearly_stat_thresh_text[$category]} Season in {$stats["year"]}";
								$yc_array[$category][$thresh][$stats["league"]]++;
			
							}
						}
					}
					if (isset($yc_array[$category][$thresh])) {
						foreach($yc_array[$category][$thresh] as $league=>$number) {
							foreach($seasonal_yardage_thresh as $blah=>$s_thresh) {
								if ($number >= $s_thresh) {
									if ($s_thresh > 1) {
										$plural = "s";
									}
									else {
										$plural = "";
									}
									$categories[] = "{$league} Players with {$s_thresh} or More {$thresh} Yard {$yearly_stat_thresh_text[$category]} Season{$plural}";
									
								}
								
							}
						}
					}
					
				}
			}
		}
		
		$yearly_td_threshes = array(
			"rushing"=>array(10,15,20,25),
			"receiving"=>array(10,15,20,25),
			"passing"=>array(20,30,40,50),
			"punts"=>array(1,5),
			"kickoffs"=>array(1,5)
			);
		$yc_array = array();

		foreach($yearly_td_threshes as $category=>$threshes) {
			foreach($threshes as $garbage=>$thresh) {
				//$yc_array[$category][$thresh] = 0;
				if (isset($player_stats[$category][0]["year"])) { 
					foreach($player_stats[$category] as $year=>$stats) {
						if ($stats["year"] < 9999) {
							if(intval(str_replace("t","",$stats["TD"]))>=$thresh) {
								if ($thresh > 1) {
									$plural_2 = "s";
								}
								else {
									$plural_2 = "";
								}

								$categories[] = "{$stats["league"]} Players With {$thresh} or More {$yearly_stat_thresh_text[$category]} Touchdown{$plural_2} in {$stats["year"]}";
								$yc_array[$category][$thresh][$stats["league"]]++;
			
							}
						}
					}
					if (isset($yc_array[$category][$thresh])) {
						foreach($yc_array[$category][$thresh] as $league=>$number) {
							foreach($seasonal_yardage_thresh as $blah=>$s_thresh) {
								if ($number >= $s_thresh) {
									if ($s_thresh > 1) {
										$plural = "s";
									}
									else {
										$plural = "";
									}
									if ($thresh > 1) {
										$plural_2 = "s";
									}
									else {
										$plural_2 = "";
									}
									
									$categories[] = "{$league} Players With {$s_thresh} or More Season{$plural} with {$thresh} or More {$yearly_stat_thresh_text[$category]} Touchdown{$plural_2}";
									
								}
								
							}
						}
					}
					
				}
			}
		}
		/*
		$debut_age = $this->determineAge($player["birthday"], $player["first_game"]);
		if ($debut_age) {
			$categories[] = "Baseball Players Who Debuted at age ". floor($debut_age);
		}
		*/
		return $categories;	
	}
	
	function checkBirthplace($birthplace) {
		
		if (strpos($birthplace, ", USA")) {
			$birthplace = substr($birthplace, 0, strpos($birthplace, ", USA"));
			if (strpos($birthplace, ", ")) {
				$state = substr($birthplace, strpos($birthplace, ", ") + strlen(", "));
				$state = $this->getStateFromAbbr($state);
				$birthplace = substr($birthplace, 0, strpos($birthplace, ", ") + strlen(", "));
				$birthplace .= $state; 
			}
			
		}
		
		return $birthplace;
	}
	
	function getStateFromAbbr($state) {
		
		$state_list = array(
			'AL'=>"Alabama",  
			'AK'=>"Alaska",  
			'AZ'=>"Arizona",  
			'AR'=>"Arkansas",  
			'CA'=>"California",  
			'CO'=>"Colorado",  
			'CT'=>"Connecticut",  
			'DE'=>"Delaware",  
			'DC'=>"District Of Columbia",  
			'FL'=>"Florida",  
			'GA'=>"Georgia",  
			'HI'=>"Hawaii",  
			'ID'=>"Idaho",  
			'IL'=>"Illinois",  
			'IN'=>"Indiana",  
			'IA'=>"Iowa",  
			'KS'=>"Kansas",  
			'KY'=>"Kentucky",  
			'LA'=>"Louisiana",  
			'ME'=>"Maine",  
			'MD'=>"Maryland",  
			'MA'=>"Massachusetts",  
			'MI'=>"Michigan",  
			'MN'=>"Minnesota",  
			'MS'=>"Mississippi",  
			'MO'=>"Missouri",  
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",  
			'OK'=>"Oklahoma",  
			'OR'=>"Oregon",  
			'PA'=>"Pennsylvania",  
			'RI'=>"Rhode Island",  
			'SC'=>"South Carolina",  
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",  
			'TX'=>"Texas",  
			'UT'=>"Utah",  
			'VT'=>"Vermont",  
			'VA'=>"Virginia",  
			'WA'=>"Washington",  
			'WV'=>"West Virginia",  
			'WI'=>"Wisconsin",  
			'WY'=>"Wyoming"
			);
			
			if (isset($state_list[$state])) {
				return $state_list[$state];
			}
			else {
				return $state;
			}

	}
	/*
	function awardDisplayName($award) {
		$awardNames = array(
			'AFL All-Star Game Defensive MVP'
			'AFL All-Star Game MVP'
			'AFL All-Star Game Offensive MVP'
			'AP Defensive Player of the Year'
			'AP Defensive Rookie of the Year'
			'AP NFL MVP'
			'AP Offensive Player of the Year'
			'AP Offensive Rookie of the Year'
			'Bert Bell MVP Trophy (Maxwell Club)'
			'Heisman Trophy Winner'
			'Joe F. Carr MVP Trophy'
			'NFL Comeback Player of the Year'
			'PFWA MVP'
			'Pro Bowl MVP'
			'Super Bowl MVP'
			'UPI AFL-AFC Defensive MVP'
			'UPI AFL-AFC MVP'
			'UPI AFL-AFC Offensive MVP'
			'UPI AFL-AFC Rookie of the Year'
			'UPI MVP'
			'UPI NFC Defensive Player of the Year'
			'UPI NFC Offensive Player of the Year'
			'UPI NFC Player of the Year'
			'UPI NFL-NFC Rookie of the Year'
	}
	*/


}




SpecialPage::addPage( new NFLPlayerUpdater );



}

?>
