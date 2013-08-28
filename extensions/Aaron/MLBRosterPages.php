<?php

class MLBStats {
	
	static public function formatPercentageStat($stat){
		if($stat==1){
			return "1.000";
		}
		if($stat!= "1.00" && $stat != "1.000"){
			$stat = number_format($stat,3);
			$stat = str_replace("0.",".", $stat );
		}
		return $stat;
	}
	
	static public function formatERA($era){
		$era = number_format($era,2);
		$era = str_replace("0.",".", $era );
		return $era;
	}
	
	static function get_team_name($abbr, $year) {
	
		$team_names = array(
		"ALT U"=>"Altoona Mountain Citys",
		"ANA A"=>"Anaheim Angels",
		"ARI N"=>"Arizona Diamondbacks",
		"ATH n"=>"Philadelphia Athletics",
		"ATL n"=>"Brooklyn Atlantics",
		"ATL N"=>"Atlanta Braves",
		"BAL a"=>"Baltimore Orioles",
		"BAL A"=>"Baltimore Orioles",
		"BAL F"=>"Baltimore Terrapins",
		"BAL n"=>"Baltimore Canaries",
		"BAL N"=>"Baltimore Orioles",
		"BAL U"=>"Baltimore Unions",
		"BOS a"=>"Boston Reds",
		"BOS A"=>array(
				"1907"=>"Boston Americans",
				"9999"=>"Boston Red Sox"
				),
		"BOS n"=>"Boston Red Stockings",
		"BOS N"=>array(
				"1882"=>"Boston Red Caps",
				"1906"=>"Boston Beaneaters",
				"1910"=>"Boston Doves",
				"1911"=>"Boston Rustlers",
				"1952"=>"Boston Braves"
				),
		"BOS P"=>"Boston Reds",
		"BOS U"=>"Boston Reds",
		"BRO a"=>array(
				"1887"=>"Brooklyn Trolley Dodgers",
				"1889"=>"Brooklyn Bridegrooms",
				"1890"=>"Brooklyn Gladiators"
				),
		"BRO F"=>"Brooklyn Tip-Tops",
		"BRO N"=>array(
				"1898"=>"Brooklyn Bridegrooms",
				"1917"=>"Brooklyn Superbas",
				"1931"=>"Brooklyn Robins",
				"1957"=>"Brooklyn Dodgers"
				),
		"BRO P"=>"Brooklyn Wonders",
		"BUF F"=>"Buffalo Blues",
		"BUF N"=>"Buffalo Bisons",
		"BUF P"=>"Buffalo Bisons",
		"CAL A"=>"California Angels",
		"CEN n"=>"Philadelphia Centennials",
		"CHI A"=>"Chicago White Sox",
		"CHI F"=>"Chicago Whales",
		"CHI n"=>"Chicago White Stockings",
		"CHI N"=>array(
				"1889"=>"Chicago White Stockings",
				"1897"=>"Chicago Colts",
				"1901"=>"Chicago Orphans",
				"9999"=>"Chicago Cubs"
				),
		"CHI P"=>"Chicago Pirates",
		"CHI U"=>"Chicago Browns",
		"CIN a"=>array(
				"1889"=>"Cincinnati Reds",
				"1891"=>"Cincinnati Porkers"
				),
		"CIN N"=>"Cincinnati Reds",
		"CIN U"=>"Cincinnati Outlaw Reds",
		"CLE a"=>"Cleveland Spiders",
		"CLE A"=>array(
				"1901"=>"Cleveland Blues",
				"1902"=>"Cleveland Broncos",
				"1914"=>"Cleveland Naps",
				"9999"=>"Cleveland Indians"
				),
		"CLE n"=>"Cleveland Forest Citys",
		"CLE N"=>"Cleveland Spiders",
		"CLE P"=>"Cleveland Infants",
		"COL a"=>"Columbus Colts",
		"COL N"=>"Colorado Rockies",
		"DET A"=>"Detroit Tigers",
		"DET N"=>"Detroit Wolverines",
		"ECK n"=>"Brooklyn Eckfords",
		"FLA N"=>"Florida Marlins",
		"HAR n"=>"Hartford Dark Blues",
		"HAR N"=>"Hartford Dark Blues",
		"HOU N"=>array(
				"1964"=>"Houston Colt .45s",
				"9999"=>"Houston Astros"
				),
		"IND a"=>"Indianapolis Blues",
		"IND F"=>"Newark Peppers",
		"IND N"=>array(
				"1878"=>"Indianapolis Blues",
				"1889"=>"Indianapolis Hoosiers"
				),
		"KC  a"=>"Kansas City Cowboys",
		"KC  A"=>array(
				"1967"=>"Kansas City Athletics",
				"9999"=>"Kansas City Royals"
				),
		"KC  F"=>"Kansas City Packers",
		"KC  N"=>"Kansas City Cowboys",
		"KC  U"=>"Kansas City Unions",
		"KEK n"=>"Ft. Wayne Kekiongas",
		"LA  A"=>"Los Angeles Angels",
		"LA  N"=>"Los Angeles Dodgers",
		"LOU a"=>"Louisville Colonels",
		"LOU N"=>array(
				"1877"=>"Louisville Grays",
				"1899"=>"Louisville Colonels"
				),
		"MAN n"=>"Middletown Mansfields",
		"MAR n"=>"Baltimore Marylands",
		"MIL a"=>"Milwaukee Brewers",
		"MIL A"=>"Milwaukee Brewers",
		"MIL N"=>array(
				"1878"=>"Milwaukee Cream Citys",
				"1965"=>"Milwaukee Braves",
				"9999"=>"Milwaukee Brewers"
				),
		"MIL U"=>"Milwaukee Grays",
		"MIN A"=>"Minnesota Twins",
		"MON N"=>"Montreal Expos",
		"MUT n"=>"New York Mutuals",
		"NAT n"=>"Washington Nationals",
		"NEW F"=>"Newark Peppers",
		"NH  n"=>"New Haven Elm Citys",
		"NY  a"=>"New York Metropolitans",
		"NY  A"=>array(
				"1912"=>"New York Highlanders",
				"9999"=>"New York Yankees"
				),
		"NY  N"=>array(
				"1876"=>"New York Mutuals",
				"1888"=>"New York Gothams",
				"1957"=>"New York Giants",
				"9999"=>"New York Mets"
				),
		"NY  P"=>"New York Giants",
		"OAK A"=>"Oakland Athletics",
		"OLY n"=>"Washington Olympics",
		"PHI a"=>"Philadelphia Athletics",
		"PHI A"=>"Philadelphia Athletics",
		"PHI n"=>"Philadelphia White Stockings",
		"PHI N"=>array(
				"1876"=>"Philadelphia Athletics",
				"1884"=>"Philadelphia Quakers",
				"9999"=>"Philadelphia Phillies"
				),
		"PHI P"=>"Philadelphia Athletics",
		"PHI U"=>"Philadelphia Keystones",
		"PIT a"=>"Pittsburgh Pirates",
		"PIT F"=>"Pittsburgh Rebels",
		"PIT N"=>"Pittsburgh Pirates",
		"PIT P"=>"Pittsburgh Burghers",
		"PRO N"=>"Providence Grays",
		"RES n"=>"Elizabeth Resolutes",
		"RIC a"=>"Richmond Virginias",
		"ROC a"=>"Rochester Hop Bitters",
		"ROK n"=>"Rockford Forest Citys",
		"RS  n"=>"St. Louis Red Stockings",
		"SD  N"=>"San Diego Padres",
		"SEA A"=>array(
				"1969"=>"Seattle Pilots",
				"9999"=>"Seattle Mariners"
				),
		"SF  N"=>"San Francisco Giants",
		"STL a"=>"St. Louis Browns",
		"STL A"=>"St. Louis Browns",
		"STL F"=>"St. Louis Terriers",
		"STL n"=>"St. Louis Brown Stockings",
		"STL N"=>array(
				"1877"=>"St. Louis Brown Stockings",
				"1886"=>"St. Louis Maroons",
				"1899"=>"St. Louis Perfectos",
				"9999"=>"St. Louis Cardinals"
				),
		"STL U"=>"St. Louis Maroons",
		"STP U"=>"St. Paul Saints",
		"SYR N"=>"Syracuse Stars",
		"TB  A"=>"Tampa Bay Devil Rays",
		"TEX A"=>"Texas Rangers",
		"TOL a"=>array(
				"1884"=>"Toledo Blue Stockings",
				"1890"=>"Toledo Maumees"
				),
		"TOR A"=>"Toronto Blue Jays",
		"TRO n"=>"Troy Haymakers",
		"TRO N"=>"Troy Trojans",
		"WAS a"=>array(
				"1884"=>"Washington Nationals",
				"1891"=>"Washington Senators"
				),
		"WAS A"=>array(
				"1960"=>"Washington Senators",
				"1971"=>"Washington Senators"
				),
		"WAS n"=>array(
				"1873"=>"Washington Nationals",
				"1875"=>"Washington Nationals"
				),
		"WAS N"=>array(
				"1899"=>"Washington Senators",
				"9999"=>"Washington Nationals"
				),
		"WAS U"=>"Washington Nationals",
		"WES n"=>"Keokuk Westerns",
		"WIL U"=>"Wilmington Quicksteps",
		"WOR N"=>"Worcester Ruby Legs"
		);
	
		if (isset($team_names[$abbr])) {
			if(!is_array($team_names[$abbr])) {
				return $team_names[$abbr];
			}
			else {
				foreach($team_names[$abbr] as $end_year=>$team_name) {
					if (intval($year) <= intval($end_year)) {
						return $team_name;
					}
				}
			}
		}
		else {
			return "";
		}
		return "No Team Name Found";
	
		
	}

}

$wgExtensionFunctions[] = 'wfMLBRosterPages';

function wfMLBRosterPages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBRosterPages extends SpecialPage {

	
	function MLBRosterPages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBRosterPages");
	}

	function getPositionGroupBatting($title,$group,$players){
		$article_text  = "";

		foreach($players as $player_name => $player_properties){
			if( $player_properties["group"] == $group ){
				$article_text .= "|-\n";
				$article_text .= "|[[$player_name]]\n";
				$article_text .= "|". $player_properties["position"] ."\n";
				$article_text .= "|". $player_properties["total_games"] ."\n";
				$article_text .= "|". $player_properties["AB"] ."\n";
				$article_text .= "|". $player_properties["runs"] ."\n";
				$article_text .= "|". $player_properties["H"] ."\n";
				$article_text .= "|". $player_properties["HR"] ."\n";
				$article_text .= "|". $player_properties["RBI"] ."\n";
				$article_text .= "|". $player_properties["SB"] ."\n";
				$article_text .= "|". $player_properties["OBP"] ."\n";
				$article_text .= "|". $player_properties["SLG"] ."\n";
				$article_text .= "|". $player_properties["OPS"] ."\n";
				$article_text .= "|". $player_properties["AVG"] ."\n";
	
			}
		}
		if($article_text){
			$article_text = "|-class=\"player-profile-stats-total\"\n|colspan=\"13\"|'''{$title}'''\n " . $article_text;
		}
	
		return $article_text;
	}
	
	function getPositionGroupPitching($title,$group,$pitchers){
		$article_text  = "";
		foreach($pitchers as $player_name => $player_properties){
			//if( $player_properties["group"] == $group ){
				$article_text .= "|-\n";
				$article_text .= "|[[$player_name]]\n";
				$article_text .= "|". $player_properties["G"] ."\n";
				$article_text .= "|". $player_properties["GS"] ."\n";
				$article_text .= "|". $player_properties["IP"] ."\n";
				$article_text .= "|". $player_properties["W"] . "\n";
				$article_text .= "|". $player_properties["L"] . "\n";
				$article_text .= "|". $player_properties["winning_percentage"] ."\n";
				$article_text .= "|". $player_properties["SV"] ."\n";
				$article_text .= "|". $player_properties["SO"] ."\n";
				$article_text .= "|". $player_properties["BB"] ."\n";
				$article_text .= "|". $player_properties["ERA"] ."\n";
				$article_text .= "|". $player_properties["WHIP"] ."\n";


			//}
		}
		if($article_text){
			$article_text = "|-class=\"player-profile-stats-total\"\n|colspan=\"15\"|'''{$title}'''\n " . $article_text;
		}
		
		return $article_text;
	}
	
	function getHighestField($players,$field, $minimum = 0, $minimum_field=""){
		
		$highest_value = 0;
		$highest_players = array();

		if(!$minimum_field)$minimum_field = $field;
			
		
		foreach($players as $player_name => $player_properties){
			if ($player_properties[$field] && $player_properties[$field] >= $highest_value 
				&& ($minimum==0 || $player_properties[$minimum_field] >= $minimum)         
				
				){
				//reset array
				if($highest_value < $player_properties[$field]){
					$highest_players = array();
				}
				$highest_players[] = $player_name;
				$highest_value = $player_properties[$field];
			}
		}
		//if($field == "SV" && $highest_value <=10)return "";
		return $highest_players;
			  
	}
	
 	function getLowestField($players,$field, $minimum = 0, $minimum_field=""){
		
		$highest_value = 999999;
		$highest_players = array();

		if(!$minimum_field)$minimum_field = $field;
			
		
		foreach($players as $player_name => $player_properties){
			if ($player_properties[$field] && $player_properties[$field] <= $highest_value 
				&& ($minimum==0 || $player_properties[$minimum_field] >= $minimum)         
				
				){
				//reset array
				if($highest_value > $player_properties[$field]){
					$highest_players = array();
				}
				$highest_players[] = $player_name;
				$highest_value = $player_properties[$field];
			}
		}
		 
		return $highest_players;
			  
	}
	
	function getSingleStatBlurb($blurbs, $players, $name, $count){
		if(!$name)return "";
		
		//get random blurb from array
		$rand_keys = array_rand($blurbs, 1);
		$blurb = $blurbs[$rand_keys];
		$blurb = str_replace("#player_name#","[[{$name}]]",$blurb);
		$blurb = str_replace("#stat_count#",$count,$blurb);
		$avg =  $players[$name]["AVG"];
	
		$blurb = str_replace("#avg#",$avg,$blurb);
		return $blurb;
	}
		
	function getLeaderRow($players,$title,$field, $minimum=0, $minimum_field="",$sort="desc"){
	
		if($sort=="desc"){
			$leaders = $this->getHighestField($players,$field,$minimum,$minimum_field);
		}else{
			$leaders = $this->getLowestField($players,$field,$minimum,$minimum_field);
		}
		
		if(count($leaders) == 0){
			return "";
		}
		
		$article_text .= "|-\n";
		$article_text .= "|{$title}\n";
		$leader_count = 0;
		$leader_text = "";
		foreach($leaders as $leader){
			$leader_count++;
			if(count($leaders) > 1){
				if($leader_text!="" && count($leaders) != 2){
					$leader_text .= ", ";
				}
				if( count($leaders) == $leader_count){
					$leader_text .= " and ";
				}
			}
			$leader_text .= "[[{$leader}]]";
		}
		$article_text .= "|". $leader_text ."\n";
		$article_text .= "|". $players[$leaders[0]][$field] ."\n";
		return $article_text;
		
	}
			
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$start = time();
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$dbr =& wfGetDB( DB_MASTER );
		
	
		$infield_positions = array("1B","2B","3B","SS");
		$outfield_positions = array("OF","LF","CF","RF");
		
		$sql = "select Team,Year from mlb_batting_stats
			 
			 
			 
			group by Team,Year order by Year DESC   ";

		$res_team = $dbr->query($sql);
		while ( $row_team = $dbr->fetchObject( $res_team ) ) {
			$year = $row_team->Year; 
			$team_abrev = $row_team->Team;
			//$team_name = $row_team->team_name;
			$team_name = MLBStats::get_team_name($team_abrev,$year);
			
			$page_title = "$year $team_name";
			
			//get infielder and of
			
			$sql = "select mlb_batting_stats.Year, mlb_batting_stats.Team, player_name,POS,mlb_fielding_stats.G as Games,mlb_batting_stats.G as TotalGames,
				AB, R as Runs, H as Hits, HR, RBI, mlb_batting_stats.AVG as AVG, OBP ,SLG, mlb_batting_stats.SB as SB,
				2B as Doubles, 3B as Triples, SH, SF, GDP, mlb_batting_stats.CS, BB, SO,
				 (AB + BB + HBP + SF + SH) as PA,
				( H + 2B + (2 * 3B) + (3 * HR) ) as TB
				from
				mlb_batting_stats
				INNER JOIN mlb_players_info on mlb_players_info.player_id=mlb_batting_stats.player_id
				LEFT JOIN mlb_fielding_stats on mlb_batting_stats.player_id = mlb_fielding_stats.player_id and mlb_fielding_stats.player_id=mlb_batting_stats.player_id
				and mlb_batting_stats.Year=mlb_fielding_stats.Year and mlb_batting_stats.Team=mlb_fielding_stats.Team
				WHERE mlb_batting_stats.year=$year  and upper(mlb_batting_stats.Team)='" . strtoupper($team_abrev) . "'";
			$res = $dbr->query($sql);	
		
			$players = array();
			while ( $row = $dbr->fetchObject( $res ) ) {
				$position = $row->POS;
				$group = "";
				if( !$players[$row->player_name] || $row->Games > $players[$row->player_name]["games"] ){
					if( in_array($position,$infield_positions) ){
						$group = "Infield";
					}else if( in_array($position,$outfield_positions) ){
						$group = "Outfield";
					}else if(  $position == "C" ){
						$group = "Catcher";
					}else if(  $position == "P" ){
						$group = "Pitcher";
					}else{
						$group = "Other";
					}
				}
				 
				if( $group ){
					$players[$row->player_name] = array(
							"group" => $group,
							"position" => $position,
							"games" => $row->Games,
							"total_games" => $row->TotalGames,
							"AB" => $row->AB,
							"PA" => $row->PA,
							"TB" => $row->TB,
							"runs" => $row->Runs,
							"H" => $row->Hits,
							"HR" => $row->HR,
							"RBI" => $row->RBI,
							"AVG" => MLBStats::formatPercentageStat($row->AVG),
							"OBP" => MLBStats::formatPercentageStat($row->OBP),
							"SLG" => MLBStats::formatPercentageStat($row->SLG),
							"OPS" => MLBStats::formatPercentageStat( $row->OBP + $row->SLG),
							"SO" => $row->SO,
							"BB" => $row->BB,
							"SB" => $row->SB,
							"Doubles" => $row->Doubles,
							"Triples" => $row->Triples,
							"SF" => $row->SF,
							"SH" => $row->SH,
							"CS" => $row->CS,
							"GDP" => $row->GDP
							);
				}
				
			}
			$dbr->freeResult( $res );
			
	
			
			//get pitchers
			
			$sql = "select player_name, G, GS, ( replace(replace(IP,'.2','.7'),'.1','.3')) as IP, W, L, ERA, SV, SO, BB, CG, SHO, WP, HBP, R, ER, HR, 2B as Doubles, 3B as Triples,H,GDP,BFP
				from mlb_pitching_stats
				INNER JOIN mlb_players_info on mlb_players_info.player_id=mlb_pitching_stats.player_id 
				WHERE mlb_pitching_stats.year=$year and upper(mlb_pitching_stats.Team)='" . strtoupper($team_abrev) . "'";
			$res = $dbr->query($sql);	
			
			$pitchers = array();
			while ( $row = $dbr->fetchObject( $res ) ) {
				$pitchers[$row->player_name] = array(
							"G" => $row->G,
							"GS" => $row->GS,
							"IP" => $row->IP,
							"W" => $row->W,
							"L" => $row->L,
							"winning_percentage" => MLBStats::formatPercentageStat($row->W / ($row->W + $row->L)),
							"descisions" => ($row->W + $row->L),
							"WHIP" => number_format((($row->BB + $row->H) / $row->IP),3),
							"SV" => $row->SV,
							"ERA" => MLBStats::formatERA($row->ERA),
							"SO" => $row->SO,
							"BB" => $row->BB,
							"CG" => $row->CG,
							"SHO" => $row->SHO,
							"WP" => $row->WP,
							"HBP" => $row->HBP,
							"R" => $row->R,
							"ER" => $row->ER,
							"HR" => $row->HR,
							"Doubles" => $row->Doubles,
							"Triples" => $row->Triples,
							"H" => $row->H,
							"GDP" => $row->GDP,
							"BF" => $row->BFP
							);
				
				
				
				
			}
			$dbr->freeResult( $res );	
			//get pitching team stats
			
			$sql = "select sum(W) as Wins, sum(L) as Losses, Sum(R) as Runs, Sum(ER) as earned_runs, Sum( replace(replace(IP,'.2','.7'),'.1','.3')) as IP,
				sum(SO) as strikeouts, sum(SV) as saves, sum(GS) as GS, sum(G) as Games, sum(BB) as BB, sum(H) as H
				from mlb_pitching_stats
				INNER JOIN mlb_players_info on mlb_players_info.player_id=mlb_pitching_stats.player_id 
				WHERE mlb_pitching_stats.year=$year and upper(mlb_pitching_stats.Team)='" . strtoupper($team_abrev) . "'
				GROUP BY Team, Year
				";
			
			$res = $dbr->query($sql);	
			while ( $row = $dbr->fetchObject( $res ) ) {
				$wins = $row->Wins;
				$losses = $row->Losses;
				$runs_allowed = $row->Runs;
				$earned_runs = $row->earned_runs;
				$innings = $row->IP;
				$strikeouts_p = $row->strikeouts;
				$saves = $row->saves;
				$starts = $row->GS;
				$games = $row->Games;
				$bb = $row->BB;
				$era = $earned_runs / $innings * 9;
				$era = MLBStats::formatERA($era);
				$whip = number_format((($bb + $row->H) / $innings),3);
				$winning_percentage = MLBStats::formatPercentageStat( $wins / ( $wins + $losses));
			}
			$dbr->freeResult( $res );
			
			$sql = "select sum(R) as Runs, sum(HR) as HR , sum(AB) as AB, sum(H) as H, sum(RBI) as RBI, sum(SB) as SB,
				sum(H + BB + HBP) as onbase, sum(AB + BB + HBP + SF + SH) as plateappearances,
				sum( H + 2B + (2 * 3B) + (3 * HR) ) as TB
				from mlb_batting_stats
				INNER JOIN mlb_players_info on mlb_players_info.player_id=mlb_batting_stats.player_id 
				WHERE mlb_batting_stats.year=$year and upper(mlb_batting_stats.Team)='" . strtoupper($team_abrev) . "'
				GROUP BY Team, Year
				";
			$res = $dbr->query($sql);	
			while ( $row = $dbr->fetchObject( $res ) ) {
				$runs_scored = $row->Runs;
				$home_runs = $row->HR;
				$AB = $row->AB;
				$hits = $row->H;
				$rbi = $row->RBI;
				$sb = $row->SB;
				$ob = MLBStats::formatPercentageStat($row->onbase / $row->plateappearances);
				$avg = MLBStats::formatPercentageStat( $hits / $AB, 3);
				$slg = MLBStats::formatPercentageStat( $row->TB / $AB, 3);
				$ops = MLBStats::formatPercentageStat( $row->onbase / $row->plateappearances + $row->TB / $AB);
			}
			$dbr->freeResult( $res );
		 
			
			$most = array();
			$done = array();
			$most_batting = array(
						"HR"=>"home runs",
						"RBI"=>"RBI",
						"H"=>"hits"
						);
			$most_pitching = array(
						"W"=>"wins",
						"SO"=>"strikeouts",
						"SV"=>"saves"
						);
						
			foreach($most_batting as $field=>$label){
				$leaders = $this->getHighestField($players,$field);
				if( $leaders ){
					$most[$field] = array(
								"type" => "batting",
								"label" => $label,
								"leaders" => $leaders ,
								"count" => count($leaders)
							);
				}
				foreach($leaders as $leader)$players[$leader]["leader_count"] = $players[$leader]["leader_count"] + 1;
			}
			
			foreach($most_pitching as $field=>$label){
				if($field=="SV"){
					$leaders = $this->getHighestField($pitchers,$field,10);
				}else{
					$leaders = $this->getHighestField($pitchers,$field);
				}
				if( $leaders  ){
					$most[$field] = array(
								"type" => "pitching",
								"label" => $label,
								"leaders" => $leaders ,
								"count" => count($leaders)
							);
				} 
				foreach($leaders as $leader){
					$pitchers[$leader]["leader_count"] = $pitchers[$leader]["leader_count"] + 1;
				}
			}	
 
		
			$stat_blurbs["RBI"] = array(
				"#player_name# knocked in #stat_count# runs",
				"#player_name# was the best run producer with #stat_count# RBI"
				);
			
			$stat_blurbs["HR"] = array(
				"#player_name# provided the power for the team with #stat_count# homers",
				"#player_name# led the team in home runs with #stat_count#"
				);
			
			$stat_blurbs["H"] = array(
				"#player_name# led the team with #stat_count# hits for a #avg# average"
				);
			
			$stat_blurbs["W"] = array(
				"#player_name# led the team with #stat_count# wins",
				"Leading the team in wins was #player_name#, with #stat_count#",
				"#player_name# was the winningest pitcher, with #stat_count# games won"
				);
			
			$stat_blurbs["SO"] = array(
				"#player_name# led the team with #stat_count# strikeouts",
				"#player_name# struck out #stat_count# batters to lead the team",
				"#player_name# racked up #stat_count# strkeouts to lead the team"
				);
			
			$stat_blurbs["SV"] = array(
				"#player_name# led the team with #stat_count# saves",
				"#player_name# was the team's best fireman, with #stat_count# saves"
				);
			
			$stat_blurbs["SB"] = array(
				"#player_name# had a great year on the basepaths with #stat_count# stolen bases"
				);
		 
			$batting_leaders_text = "";
			$pitching_leaders_text = "";
			foreach($most as $category=>$most_item){
				$leader_text = "";
				if(!in_array($category,$done)){
					if( $most_item["count"] == 1 ){
						if( $most_item["type"] == "batting" ){
							$player_array = $players;
						}else{
							$player_array = $pitchers;
						}
						
						$player_text = "{$most_item["leaders"][0]}";
						$stat_text = "{$player_array[$most_item["leaders"][0]][$category]} {$most_item["label"]}";
						$stat_count = "{$player_array[$most_item["leaders"][0]][$category]}";
						
			
						$done[] = $category;
						$leader_count = 1;
						$delim = "";
						foreach($most as $category2=>$most_item2){
							if( !in_array($category2,$done) && $category2 != $category && $most_item2["count"] == 1 && $most_item["leaders"][0]==$most_item2["leaders"][0]){
								$leader_count++;
								 
								if($leader_count < $player_array[ $most_item["leaders"][0] ]["leader_count"] && $player_array[ $most_item["leaders"][0] ]["leader_count"] > 2 ){
									$delim .= ", ";
								}
								if($leader_count == $player_array[ $most_item["leaders"][0] ]["leader_count"]){
									$delim .= " and ";
								} 
								 
								$stat_text.= "{$delim}{$player_array[$most_item["leaders"][0]][$category2]} {$most_item2["label"]}";
								$done[] = $category2;
								
							}
								
						}
						
						if($leader_count == 1){
							//get random blurb from array
							$leader_text .= $this->getSingleStatBlurb( $stat_blurbs[$category],$player_array, $player_text, $stat_count);
							
						}else{
							if( $most_item["type"] == "batting" ){
								$leader_text .= "The offense was powered by [[{$player_text}]], who led the team with {$stat_text}";
							}else{
								$leader_text .= "[[{$player_text}]] led the pitching staff with {$stat_text}";
							}
							
						}
						$leader_text .= ".  ";
					}else{
						if( $most_item["type"] == "batting" ){
							$player_array = $players;
						}else{
							$player_array = $pitchers;
						}
						$tie = "";
						$tie_count = 0;
						foreach($most_item["leaders"] as $leader){
							$tie_count++;
							if( $tie!="" && count($most_item["leaders"]) != 2){
								$tie .= ", ";
							}
							if( count($most_item["leaders"]) == $tie_count){
								$tie .= " and ";
							}
							$tie .= "[[{$leader}]]";
							
						}
						$stat_count = $player_array[$most_item["leaders"][0]][$category];
						$leader_text .=  "{$tie} were tied for the team lead in {$most_item["label"]} with {$stat_count}. ";
					}
					
					if( $most_item["type"] == "batting" ){
						$batting_leaders_text .= $leader_text;
					}else{
						$pitching_leaders_text .= $leader_text;
					}
				}
				
			}
			
			//Special Categories
			$special_batting = "";
			$sb_leaders = $this->getHighestField($players,"SB");
			if( count($sb_leaders) == 1 ){
				if( $players[ $sb_leaders[0] ]["SB"] > 30 ){
					$special_batting = $this->getSingleStatBlurb( $stat_blurbs["SB"], $players, $sb_leaders[0],$players[ $sb_leaders[0] ]["SB"]) . ".";
				}
			}
			
			$article_text = "";
			$article_text .= "{{Template:Teams Rating Box}}\n\n";
			$article_text .= "==Summary==\n\n";
			//$article_text .= "Record: '''{$wins}-{$losses}'''\n\n";
			$article_text .= "The [[{$year} Major League Baseball|{$year}]] [[{$team_name}]] finished with a record of {$wins}-{$losses}. They scored {$runs_scored} runs and belted {$home_runs} home runs to go along with a {$avg} batting average.  {$batting_leaders_text} {$special_batting}\n\n";
			$article_text .= "The team allowed {$runs_allowed} runs and had a team ERA of {$era}. {$pitching_leaders_text}\n";
			
			 
			$article_text .= "\n";
			$article_text .= "==Roster and Statistics==\n\n";
					
			$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n";
			$article_text .= "|- class=\"player-profile-stats-header\"\n";
			$article_text .= "| '''Player Name'''\n";
			$article_text .= "| '''Pos'''\n";
			$article_text .= "| '''G'''\n";
			$article_text .= "|'''AB'''\n";
	
			$article_text .= "| '''R'''\n";
			$article_text .= "| '''H'''\n";
			$article_text .= "| '''HR'''\n";
			$article_text .= "| '''RBI'''\n";
			$article_text .= "| '''SB'''\n";
			$article_text .= "| '''OBP'''\n";
			$article_text .= "| '''SLG'''\n";
			$article_text .= "| '''OPS'''\n";
			$article_text .= "| '''AVG'''\n";
			
			
			
			//$article_text .= $this->getPositionGroupBatting("Pitchers","Pitcher",$players);
			$article_text .= $this->getPositionGroupBatting("Infielders","Infield",$players);
			$article_text .= $this->getPositionGroupBatting("Outfielders","Outfield",$players);
			$article_text .= $this->getPositionGroupBatting("Catchers","Catcher",$players);
			$article_text .= $this->getPositionGroupBatting("Other","Other",$players);
			
			//total team stats
			$article_text .= "|-class=\"player-profile-stats-total\"\n";
			$article_text .= "|'''Team Totals'''\n";
			$article_text .= "| -\n";
			$article_text .= "| -\n";
			$article_text .= "|". $AB ."\n";
			$article_text .= "|". $runs_scored ."\n";
			$article_text .= "|". $hits ."\n";
			$article_text .= "|". $home_runs ."\n";
			$article_text .= "|". $rbi ."\n";
			$article_text .= "|". $sb ."\n";
			$article_text .= "|". $ob ."\n";
			$article_text .= "|". $slg ."\n";
			$article_text .= "|". $ops ."\n";
			$article_text .= "|". $avg ."\n";
			
			$article_text .= "|}\n\n\n";
			
			$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n";
			$article_text .= "|- class=\"player-profile-stats-header\"\n";
			$article_text .= "| '''Player Name'''\n";
			$article_text .= "| '''G'''\n";
			$article_text .= "| '''GS'''\n";
			$article_text .= "| '''IP'''\n";
			$article_text .= "| '''W'''\n";
			$article_text .= "| '''L'''\n";
			$article_text .= "| '''Win %'''\n";
			$article_text .= "| '''SV'''\n";
			$article_text .= "| '''SO'''\n";
			$article_text .= "| '''BB'''\n";
			$article_text .= "| '''ERA'''\n";
			$article_text .= "| '''WHIP'''\n";
			
			
			$article_text .= $this->getPositionGroupPitching("Pitchers","",$pitchers);
			//$article_text .= $this->getPositionGroupPitching("RP","",$pitchers);
			
			//total team stats
			$article_text .= "|-class=\"player-profile-stats-total\"\n";
			$article_text .= "|'''Team Totals'''\n";
			$article_text .= "|". $games ."\n";
			$article_text .= "|". $starts ."\n";
			$article_text .= "|". $innings ."\n";
			$article_text .= "|". $wins ."\n";
			$article_text .= "|". $losses ."\n";
			$article_text .= "|". $winning_percentage ."\n";
			$article_text .= "|". $saves ."\n";
			$article_text .= "|". $strikeouts_p ."\n";
			$article_text .= "|". $bb ."\n";
			$article_text .= "|". $era ."\n";
			$article_text .= "|". $whip ."\n";
			$article_text .= "|}\n\n";
			
			//Create categories
			$categories = array( "Teams by Year", "MLB Teams by Year", "{$team_name}","$year Teams","$team_name Teams");
			foreach($categories as $ctg){
				$article_text .= "[[Category:{$ctg}]]\n";
				$this->makeCategory($ctg);
			}
			//$article_text .= "[[Category:Teams by Year]]\n[[Category:MLB Teams by Year]]\n[[Category:$team_name]]\n[[Category:$year Teams]]\n[[Category:$team_name Teams]]";
		
			$article_text .= "==Batting Leaders==\n\n";
			$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n";
			$article_text .= "|-class=\"player-profile-stats-total\"\n|colspan=\"3\"";
			$article_text .= "|'''Category'''\n";
			
			$article_text .= $this->getLeaderRow($players,"Games","total_games");
			$article_text .= $this->getLeaderRow($players,"At Bats","AB");
			$article_text .= $this->getLeaderRow($players,"Plate Appearances (approx.)","PA");
			
			if ($year<=1880){
				$plate_appearance_limit=100;
			} else if (($year<=1960)&&($year>=1881)) {
				$plate_appearance_limit=478;
			} else {
				$plate_appearance_limit=503;
			}
			if($year==1981)$plate_appearance_limit = 332;
			if($year==1994)$plate_appearance_limit = 351;
			if($year==1995)$plate_appearance_limit = 447;
			
			$article_text .= $this->getLeaderRow($players,"Batting Average","AVG", $plate_appearance_limit, "PA");
			$article_text .= $this->getLeaderRow($players,"On-Base %","OBP", $plate_appearance_limit, "PA");
			$article_text .= $this->getLeaderRow($players,"Slugging %","SLG", $plate_appearance_limit, "PA");
			$article_text .= $this->getLeaderRow($players,"OPS","OPS", $plate_appearance_limit, "PA");
	
			$article_text .= $this->getLeaderRow($players,"Runs","runs");
			$article_text .= $this->getLeaderRow($players,"Hits","H");
			$article_text .= $this->getLeaderRow($players,"Total Bases","TB");
			$article_text .= $this->getLeaderRow($players,"Doubles","Doubles");
			$article_text .= $this->getLeaderRow($players,"Triples","Triples");
			$article_text .= $this->getLeaderRow($players,"Home Runs","HR");
			$article_text .= $this->getLeaderRow($players,"RBI","RBI");
			$article_text .= $this->getLeaderRow($players,"Walks","BB");
			$article_text .= $this->getLeaderRow($players,"Strikeouts","SO");
			$article_text .= $this->getLeaderRow($players,"Stolen Bases","SB");
			$article_text .= $this->getLeaderRow($players,"Caught Stealing","CS");
			$article_text .= $this->getLeaderRow($players,"Sacrifice Hits","SH");
			$article_text .= $this->getLeaderRow($players,"Sacrifice Flies","SF");
			$article_text .= $this->getLeaderRow($players,"Grounded into Double Plays","GDP");
			$article_text .= "|}\n\n";
			
			 
			$article_text .= "==Pitching Leaders==\n\n";
			$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n";
			$article_text .= "|-class=\"player-profile-stats-total\"\n|colspan=\"3\"";
			$article_text .= "|'''Category'''\n";
			
			if ($year<=1880){
				$innings_limit=100;
			} else if (($year<=1960)&&($year>=1881)) {
				$innings_limit=154;
			} else {
				$innings_limit=162;
			}
			if($year==1981)$innings_limit = 107;
			if($year==1994)$innings_limit = 113;
			if($year==1995)$innings_limit = 144;
			
			$article_text .= $this->getLeaderRow($pitchers,"Wins","W");
			$article_text .= $this->getLeaderRow($pitchers,"Losses","L");
			$article_text .= $this->getLeaderRow($pitchers,"Saves","SV");
			$article_text .= $this->getLeaderRow($pitchers,"Won-Loss %","winning_percentage",13,"descisions");
			$article_text .= $this->getLeaderRow($pitchers,"ERA","ERA",$innings_limit,"IP","asc");
			$article_text .= $this->getLeaderRow($pitchers,"WHIP","WHIP",$innings_limit,"IP","asc");
			$article_text .= $this->getLeaderRow($pitchers,"Innings Pitched","IP");
			$article_text .= $this->getLeaderRow($pitchers,"Games","G");
			$article_text .= $this->getLeaderRow($pitchers,"Games Started","GS");
			$article_text .= $this->getLeaderRow($pitchers,"Strikeouts","SO");
			$article_text .= $this->getLeaderRow($pitchers,"Complete Games","CG");
			$article_text .= $this->getLeaderRow($pitchers,"Shutouts","SHO");
			$article_text .= $this->getLeaderRow($pitchers,"Batters Faced","BF");
			$article_text .= $this->getLeaderRow($pitchers,"Earned Runs Allowed","ER");
			$article_text .= $this->getLeaderRow($pitchers,"Hits Allowed","H");
			$article_text .= $this->getLeaderRow($pitchers,"Walks Allowed","BB");
			$article_text .= $this->getLeaderRow($pitchers,"Home Runs Allowed","HR");
			$article_text .= $this->getLeaderRow($pitchers,"Doubles Allowed","Doubles");
			$article_text .= $this->getLeaderRow($pitchers,"Triples Allowed","Triples");
			$article_text .= $this->getLeaderRow($pitchers,"Wild Pitches","WP");
			$article_text .= $this->getLeaderRow($pitchers,"Hit Batsmen","HBP");
			$article_text .= $this->getLeaderRow($pitchers,"Double Plays Induced","GDP");
			$article_text .= "|}\n\n";
			 
			$do_edit = true;
			$title = Title::makeTitleSafe( NS_MAIN, $page_title );
			$db_key = $title->getDBKey();
			$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key ),"" );
			if ( $s !== false ) {
				$edits = $this->check_edits($s->page_id);
			
				if($edits["good"] == false){
					$do_edit = false;
				}
	
			} 
			if( $do_edit){
				$article = new Article($title);
				$article->doEdit( $article_text, "MLB Teams");
				$output .= "edit of <b>{$page_title}</b> complete. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
			}else{
				$skipped .= "[[{$page_title}]]\n";
				$output .= "skipped <b>{$page_title}</b> because edited by outsider. <a href=\"index.php?title={$page_title}\">(link)</a><br>";
			}
			sleep(2);
		}
		$end = time();
		$wgOut->addHTML($output . "<br><br>took " . ($start-$end) . " seconds");
	
		//make page of skipped
		if($skipped){
			$skipped_title = Title::makeTitleSafe( NS_MAIN,"MLB Teams Re-do Skipped" );
			$article_skipped = new Article($skipped_title);
			$page_text = $article_skipped->getContent();
			$article_skipped->doEdit( $page_text . "\n\n==Skipped " . time() . "==\n" . $skipped, "MLB Teams Skipped");
		}
	
	}
	
	function makeCategory($ctg){
		$title = Title::makeTitleSafe( NS_CATEGORY, $ctg );
		$db_key = $title->getDBKey();
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key, 'page_namespace' => NS_CATEGORY ),"" );
		if ( $s === false ) {
			$article = new Article($title);
			$article->doEdit( "The following pages belong to this category.", "New Category Page");
		}
	}
	
	function check_edits($page_id) {
			$routput = "";
			//Database key would be in page title if the page already exists
			$dbr =& wfGetDB( DB_MASTER );
			
			//$sql = "SELECT count(distinct rev_user_text) as user_edits FROM revision WHERE rev_page={$page_id}";
			$sql = "SELECT rev_user_text, rev_timestamp FROM revision WHERE rev_page={$page_id} group by rev_user_text, rev_timestamp order by rev_timestamp desc";
			
			$edits = array();
			$good = array("66.249.72.19","Pean","DNL","Awrigh01","Roblefko", "MLB Stats Bot", "NFL Stats Bot");
			//$good = array();
			$res = $dbr->query($sql);	
			$edits["count"] = $dbr->numRows($res);
			$edits["good"] = true;
			
			while ( $row = $dbr->fetchObject( $res ) ) {
				if( !in_array( $row->rev_user_text,$good ) ){
					$edits["good"] = false;
				}
			}  
		
			return $edits;
	}
  
 
	
}

SpecialPage::addPage( new MLBRosterPages );

 


}

?>