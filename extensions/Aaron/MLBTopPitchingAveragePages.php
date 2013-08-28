<?php

$wgExtensionFunctions[] = 'wfMLBTopPitchingAveragePages';

function wfMLBTopPitchingAveragePages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBTopPitchingAveragePages extends SpecialPage {

	
	function MLBTopPitchingAveragePages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBTopPitchingAveragePages");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$pitch_cat_text = array(
				"ERA"=>"earned run average",
				"WHIP"=>"WHIP",
				"k_per_nine"=>"K/9",
				"winning_percentage"=>"winning percentage"
		);
		
		$sql_year = "SELECT DISTINCT year FROM mlb_batting_stats WHERE year ORDER BY year ASC";
		$res_year = $dbr->query($sql_year);
		
		while ( $row_year = $dbr->fetchObject( $res_year ) ) {
			
			$baseball_year = $row_year->year;
		
			if ($baseball_year<=1880){
				$innings_limit=100;
			} else if (($baseball_year<=1960)&&($baseball_year>=1881)) {
				$innings_limit=154;
			} else {
				$innings_limit=162;
			}
			if($baseball_year==1981)$innings_limit = 107;
			if($baseball_year==1994)$innings_limit = 113;
			if($baseball_year==1995)$innings_limit = 144;
		
		foreach ($pitch_cat_text as $stat=>$stat_title) {
		
				//database calls
				
				if ($stat=="ERA") {
					$sql="SELECT ERA, mlb_players_info.player_name, player_id FROM (SELECT stats_id, player_id, ERA, IP FROM mlb_pitching_stats WHERE year={$baseball_year} and player_id not in (SELECT player_id from mlb_pitching_stats_total WHERE year={$baseball_year}) UNION SELECT max(stats_id) as stats_id, player_id, ERA, IP FROM mlb_pitching_stats_total WHERE year={$baseball_year} GROUP BY player_id, ERA, IP) as the_counts JOIN mlb_players_info on the_counts.player_id = mlb_players_info.player_id WHERE IP>={$innings_limit} ORDER BY ERA ASC LIMIT 0,25";
				} else if ($stat=="WHIP"){
					$sql="SELECT WHIP, mlb_players_info.player_name, player_id FROM (SELECT stats_id, player_id, (BB+H)/IP as WHIP, IP FROM mlb_pitching_stats WHERE year={$baseball_year} and player_id not in (SELECT player_id from mlb_pitching_stats_total WHERE year={$baseball_year}) UNION SELECT max(stats_id) as stats_id, player_id, (BB+H)/IP as WHIP, IP FROM mlb_pitching_stats_total WHERE year={$baseball_year} GROUP BY player_id, WHIP, IP) as the_counts JOIN mlb_players_info on the_counts.player_id = mlb_players_info.player_id WHERE IP>={$innings_limit} ORDER BY WHIP ASC LIMIT 0,25";
				} else if ($stat=="k_per_nine") {
					$sql="SELECT k_per_nine, mlb_players_info.player_name, player_id FROM (SELECT stats_id, player_id, SO*9/IP as k_per_nine, IP FROM mlb_pitching_stats WHERE year={$baseball_year} and player_id not in (SELECT player_id from mlb_pitching_stats_total WHERE year={$baseball_year}) UNION SELECT max(stats_id) as stats_id, player_id, SO*9/IP as k_per_nine, IP FROM mlb_pitching_stats_total WHERE year={$baseball_year} GROUP BY player_id, k_per_nine, IP) as the_counts JOIN mlb_players_info on the_counts.player_id = mlb_players_info.player_id WHERE IP>={$innings_limit} ORDER BY k_per_nine DESC LIMIT 0,25";
				} else if ($stat="winning_percentage") {
					$sql="SELECT winning_percentage, mlb_players_info.player_name, player_id FROM (SELECT stats_id, player_id, W/(W+L) as winning_percentage, IP FROM mlb_pitching_stats WHERE year={$baseball_year} and player_id not in (SELECT player_id from mlb_pitching_stats_total WHERE year={$baseball_year}) UNION SELECT max(stats_id) as stats_id, player_id, W/(W+L) as winning_percentage, IP FROM mlb_pitching_stats_total WHERE year={$baseball_year} GROUP BY player_id, winning_percentage, IP) as the_counts JOIN mlb_players_info on the_counts.player_id = mlb_players_info.player_id WHERE IP>={$innings_limit} ORDER BY winning_percentage DESC LIMIT 0,25";
				}
				
				$res = $dbr->query($sql);
				
				$stat_header=$stat;
				if($stat=="k_per_nine")$stat_header="K/9";
				if($stat=="winning_percentage")$stat_header="W%";
				
				$title_string = "";
				$article_text="";
				
				$direction="lowest";
				if(($stat=="k_per_nine") || ($stat=="winning_percentage"))$direction="highest";
				
				$title_string .= "List of players with the {$direction} {$stat_title} for the {$baseball_year} baseball season";

				$article_text .= "";


				$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n|'''{$stat_header}'''\n";

				
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
						$article_text .= "|[[$player_name]]\n|".(($stat=="ERA")?MLBStats::formatERA($player_stat):MLBStats::formatPercentageStat($player_stat))."\n";

					$x++;
					
					$player_stat_check = $player_stat;

				}
				$dbr->freeResult( $res );
				
				$article_text .= "|}\n\n\n";
				
				$categories = array( "Lists", "Statistical Lists", "Baseball Lists","Pitching Lists","MLB Lists", "{$stat} Lists", "{$baseball_year} Lists");
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

SpecialPage::addPage( new MLBTopPitchingAveragePages );

 


}

?>