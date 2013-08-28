<?php

$wgExtensionFunctions[] = 'wfMLBTopBattingAveragePages';

function wfMLBTopBattingAveragePages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBTopBattingAveragePages extends SpecialPage {

	
	function MLBTopBattingAveragePages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBTopBattingAveragePages");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		$dbr =& wfGetDB( DB_MASTER );
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$pitch_cat_text = array(
				"AVG"=>"batting average",
				"OBP"=>"on base percentage",
				"SLG"=>"slugging percentage",
				"OPS"=>"on base plus slugging percentage"
		);
		
		$sql_year = "SELECT DISTINCT year FROM mlb_batting_stats WHERE year ORDER BY year ASC";
		$res_year = $dbr->query($sql_year);
		
		while ( $row_year = $dbr->fetchObject( $res_year ) ) {
			
			$baseball_year = $row_year->year;
		
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
		
		foreach ($pitch_cat_text as $stat=>$stat_title) {
		
				//database calls
				
				if ($stat=="OPS") {
					$stat_calc="OBP+SLG as OPS";
				} else {
					$stat_calc=$stat;
				}
				
				$sql = "SELECT {$stat}, mlb_players_info.player_name, player_id FROM (SELECT stats_id, player_id, {$stat_calc}, AB + BB + HBP + SF as plate_appearances FROM mlb_batting_stats WHERE year={$baseball_year} and player_id not in (SELECT player_id from mlb_batting_stats_total WHERE year={$baseball_year}) UNION SELECT max(stats_id) as stats_id, player_id, {$stat_calc}, AB + BB + HBP + SF as plate_appearances FROM mlb_batting_stats_total WHERE year={$baseball_year} GROUP BY player_id, {$stat}, plate_appearances) as the_counts JOIN mlb_players_info on the_counts.player_id = mlb_players_info.player_id WHERE plate_appearances>={$plate_appearance_limit} ORDER BY {$stat} DESC LIMIT 0,25";
				$res = $dbr->query($sql);
				
				
				
				$title_string = "";
				$article_text="";
				
				
				$title_string .= "List of players with the highest {$stat_title} for the {$baseball_year} baseball season";

				$article_text .= "";

				$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n|'''{$stat}'''\n";

				$x=1;
				$player_stat_check=0;

				while ( $row = $dbr->fetchObject( $res ) ) {

					$player_name = $row->player_name;
					$player_stat = $row->$stat;
					
					$article_text .= "|-\n";
						
						if ($player_stat_check==$player_stat) {
							$article_text .= "|'''{$x_last}'''\n";
						} else {
							$article_text .= "|'''{$x}'''\n";
							$x_last=$x;
						}
						$article_text .= "|[[$player_name]]\n|".(($player_stat==1)?"1.000":MLBStats::formatPercentageStat($player_stat))."\n";

					$x++;
					
					$player_stat_check = $player_stat;

				}
				$dbr->freeResult( $res );
				
				$article_text .= "|}\n\n\n";
				
				$categories = array( "Lists", "Statistical Lists", "Baseball Lists","Batting Lists","MLB Lists", "{$stat} Lists", "{$baseball_year} Lists");
				foreach($categories as $ctg){
					$article_text .= "[[Category:{$ctg}]]\n";
					$this->makeCategory($ctg);
				}
				
				
				$title = Title::makeTitleSafe( NS_MAIN, $title_string );
				$article = new Article($title);
				$article->doEdit( $article_text, "MLB Leader Lists");
				
				
				$output .= "<b>{$title_string}</b> complete - <a href=\"index.php?title={$title_string}\">(link)</a><br>";
				sleep(5);
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

SpecialPage::addPage( new MLBTopBattingAveragePages );

 


}

?>