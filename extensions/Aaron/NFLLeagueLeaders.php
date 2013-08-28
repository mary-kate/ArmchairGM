<?php

$wgExtensionFunctions[] = 'wfNFLLeagueLeaders';

function wfNFLLeagueLeaders(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class NFLLeagueLeaders extends SpecialPage {

	
	function NFLLeagueLeaders(){
		UnlistedSpecialPage::UnlistedSpecialPage("NFLLeagueLeaders");
	}

	function page_content ($stat_type, $statistic, $year, $league, $statistic_name) {
		
		global $wgRequest, $IP, $wgOut, $wgUser;
		$wgUser = User::newFromName( "NFL Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$title_string="";
		$article_text="";

		//get number of players 

		if ($year=='9999') {
			if ($league=='none') {
				$sql_count = "SELECT COUNT(*) as the_count FROM nfl_{$stat_type}_stats WHERE year='{$year}' and $statistic>0;";
				$sql = "SELECT nfl_{$stat_type}_stats.player_id, firstname, lastname, SUM($statistic) as player_stat FROM nfl_{$stat_type}_stats INNER JOIN nfl_players_info ON nfl_{$stat_type}_stats.player_id=nfl_players_info.player_id WHERE year='{$year}' GROUP BY nfl_{$stat_type}_stats.player_id ORDER BY player_stat DESC, lastname ASC LIMIT 0,50;";
			} else {
				$sql_count = "SELECT COUNT(*) as the_count FROM nfl_{$stat_type}_stats WHERE year='{$year}' and league='{$league}'and $statistic>0;";
				$sql = "SELECT nfl_{$stat_type}_stats.player_id, firstname, lastname, $statistic as player_stat FROM nfl_{$stat_type}_stats INNER JOIN nfl_players_info ON nfl_{$stat_type}_stats.player_id=nfl_players_info.player_id WHERE year='{$year}' and $statistic>0 and league='{$league}' GROUP BY nfl_{$stat_type}_stats.player_id ORDER BY player_stat DESC, lastname ASC LIMIT 0,50;";
			}
			
		} else {
			$sql_count = "SELECT COUNT(*) as the_count FROM nfl_{$stat_type}_stats WHERE year='{$year}' and $statistic>0;";
			$sql = "SELECT nfl_{$stat_type}_stats.player_id, firstname, lastname, $statistic as player_stat, name FROM nfl_{$stat_type}_stats INNER JOIN nfl_players_info ON nfl_{$stat_type}_stats.player_id=nfl_players_info.player_id INNER JOIN nfl_teams ON abbr=team WHERE year='{$year}' and $statistic>0 GROUP BY nfl_{$stat_type}_stats.player_id ORDER BY player_stat DESC, lastname ASC LIMIT 0,50;";
		}

		$res_count = $dbr->query($sql_count);
		$row_count = $dbr->fetchObject( $res_count );
		$player_count = $row_count->the_count;
		$res = $dbr->query($sql);
		
		if ($player_count>0) {
			
			if ($league)

			if ($year=='9999') {
				if ($league=='none') {
					$title_string .= "List of football players with the most {$statistic_name}";
				} else {
					$title_string .= "List of {$league} football players with the most {$statistic_name}";
				}
				
			} else {
				$title_string .= "List of {$league} football players with the most {$statistic_name} in {$year}";
			}
			

			$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n";
			
			if($year!='9999')$article_text .= "|'''Team'''\n";
			
			$article_text .= "|'''Total ".ucwords($statistic_name)."'''\n";	

			$x=1;
			$player_stat_check=0;

			while ( $row = $dbr->fetchObject( $res ) ) {

				$first_name = $row->firstname;
				$last_name = $row->lastname;
				$player_stat = $row->player_stat;
				$team_name = $row->name;

				$article_text .= "|-\n";

					if ($player_stat_check==$player_stat) {
						$article_text .= "|'''{$x_last}'''\n";
					} else {
						$article_text .= "|'''{$x}'''\n";
						$x_last=$x;
					}
					$article_text .= "|[[{$first_name} {$last_name}]]\n";
					if($year!='9999')$article_text .= "|[[{$team_name}]]\n";
					$article_text .= "|".number_format($player_stat)."\n";

				$x++;

				$player_stat_check = $player_stat;

			}

			$article_text .= "|}";
			
			$dbr->freeResult( $res );
			
			$categories = array( "Lists", "Statistical Lists", "Football Lists", ucwords($statistic_name)." Lists");
			
			
			if ($year!='9999') {
				$categories[]="{$year} Lists";
				$categories[]="{$year} Football Lists";
			}

			if ($league!='none') {
				$categories[]="{$league} Lists";
			}
			
			foreach($categories as $ctg){
				$article_text .= "[[Category:{$ctg}]]\n";
				$this->makeCategory($ctg);
			}
			
			$title = Title::makeTitleSafe( NS_MAIN, $title_string );
			$article = new Article($title);
			$article->doEdit( $article_text, "NFL Leader Lists");
			
			sleep(1);
			
			$content .= "<b>{$title_string}</b> complete - <a href=\"index.php?title={$title_string}\">(link)</a><br>";
			
		}
		
			
		return $content;
		
	}
	
	function get_stats($year, $league) {
		
		$stat = array ( 
			
			/*
			"passing"=> array (
				"ATT"=>"passing attempts",
				"CMP"=>"completion",
				"YDS"=>"passing yards",
				"TD"=>"passing touchdowns",
				"'INT'"=>"interception",
				"SKD"=>"times sacked",
				"SKY"=>"sack yards lost"
			),
			"receiving"=> array (
				"REC"=>"receptions",
				"YDS"=>"receiving yards",
				"TD"=>"receiving touchdowns"
			),
			"rushing"=> array (
				"ATT"=>"rushing attempts",
				"YDS"=>"rushing yards",
				"TD"=>"rushing touchdowns"
			),
			"defense"=> array (
				"SK"=>"sacks"
			),
			"interceptions"=>array (
				"'INT'"=>"defensive interceptions",
				"YDS"=>"interception return yards",
				"TD"=>"interceptions returned for a touchdown"
			),
			"kickreturn"=> array (
				"RET"=>"kick returns",
				"YDS"=>"kick return yards",
				"TD"=>"kick return touchdowns"
			),
			"puntreturn"=> array (
				"RET"=>"punt returns",
				"YDS"=>"punt return yards"
			),
			"punting"=> array (
				"PT"=>"punts",
				"YDS"=>"punting yards",
				"BLK"=>"punts blocked",
				"I20"=>"punts over twenty yards",	
			),
			*/
			"kicking"=>array (
				"FGM"=>"field goals made",
				"`1-19M`"=>"field goals made under twenty yards",
				"`1-19A`"=>"field goals attempted under twenty yards",
				"FGA"=>"field goals attempted",
				"XPM"=>"extra points made",
				"XPA"=>"extra points attempted",
				"`20-29M`"=>"field goals made 20-29 yards",
				"`20-29A`"=>"field goals attempted 20-29 yards",
				"`30-39M`"=>"field goals made 30-39 yards",
				"`30-39A`"=>"field goals attempted 30-39 yards",
				"`40-49M`"=>"field goals made 40-49 yards",
				"`40-49A`"=>"field goals attempted 40-49 yards",
				"`50+M`"=>"field goals made over fifty yards",
				"`50+A`"=>"field goals attempted over fifty yards"
				
			)
			
			
		);
		
		/*
		
		
		*/
		
		return $stat;
	}
	
	function execute() {
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		
		$list_type = array (
			"League",
			"Overall"
		);
		
		$league_name = array (
			"APFA",
			"AFLG",
			"AAFC",
			"NFL",
			"AFL"
		);
		
		
		
		foreach ($list_type as $list) {

			$dbr =& wfGetDB( DB_MASTER );
			
			if ($list=="League") {
				
				//get league info
				foreach ($league_name as $league) {
					
					$sql_league_years = "SELECT DISTINCT league, year FROM nfl_fumbles_stats WHERE league='{$league}' ORDER BY year ASC";
					$res_league_years = $dbr->query($sql_league_years);

					while ( $row_league_years = $dbr->fetchObject( $res_league_years ) ) {
					
						$year = $row_league_years->year;
						$league = $row_league_years->league;
						
						$stat = $this->get_stats($year, $league);
						
						foreach ($stat as $stat_type=>$stat_type_array) {

							foreach ($stat_type_array as $statistic=>$statistic_name) {		
									
									$output .= $this->page_content($stat_type, $statistic, $year, $league, $statistic_name);
									
							}

						}
					
					}
					
				}
				
			} else {
				
				foreach ($stat as $stat_type=>$stat_type_array) {

					foreach ($stat_type_array as $statistic=>$statistic_name) {		

							$output .= $this->page_content($stat_type, $statistic, '9999', 'none', $statistic_name);

					}

				}
				
			}
			
			
		}

		$wgOut->addHTML($output);
	
	}
  
 	function makeCategory($ctg){
		$title = Title::makeTitleSafe( NS_CATEGORY, $ctg );
		$db_key = $title->getDBKey();
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key, 'page_namespace' => NS_CATEGORY ),"" );
		if ( $s === false ) {
			$article = new Article($title);
			$article->doEdit( "Below is a list of pages in the category {$ctg}.", "New Category Page");
		}
	}
	
}

SpecialPage::addPage( new NFLLeagueLeaders );

 


}

?>