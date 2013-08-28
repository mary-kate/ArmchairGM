<?php

$wgExtensionFunctions[] = 'wfMLBTopBattingCategoryPages';

function wfMLBTopBattingCategoryPages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBTopBattingCategoryPages extends SpecialPage {

	
	function MLBTopBattingCategoryPages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBTopBattingCategoryPages");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$pitch_cat_text = array(
				"AB"=>"at bats",
				"G"=>"games",
				"R"=>"runs scored",
				"H"=>"hits",
				"2B"=>"doubles",
				"3B"=>"triples",
				"HR"=>"home runs",
				"RBI"=>"runs batted in",
				"SB"=>"stolen bases",
				"CS"=>"times caught stealing",
				"BB"=>"walks",
				"SO"=>"strikeouts"
		);
		
		$sql_year = "SELECT DISTINCT year FROM mlb_batting_stats WHERE year ORDER BY year ASC";
		$res_year = $dbr->query($sql_year);
		
		while ( $row_year = $dbr->fetchObject( $res_year ) ) {
			
			$baseball_year = $row_year->year;
		
		foreach ($pitch_cat_text as $stat=>$stat_title) {
		
				//database calls
				
				$sql = "SELECT DISTINCT player_name, {$stat}, player_id FROM (SELECT mlb_players_info.player_id, mlb_players_info.player_name, Year, {$stat} FROM (SELECT mlb_batting_stats.* FROM mlb_batting_stats UNION SELECT mlb_batting_stats_total.* FROM mlb_batting_stats_total WHERE Year <> 9999) as all_pitching JOIN mlb_players_info ON mlb_players_info.player_id=all_pitching.player_id ORDER BY {$stat} DESC, player_name ASC) AS the_counts WHERE Year={$baseball_year} and {$stat}>0 LIMIT 0,25";
				$res = $dbr->query($sql);
				
				
				$title_string = "";
				$article_text="";
				
				
				$title_string .= "List of players with the most {$stat_title} during the {$baseball_year} baseball season";

				$article_text .= "";

				$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n|'''Total {$stat}s'''\n";

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
						$article_text .= "|[[$player_name]]\n|".number_format($player_stat)."\n";

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
				
				//$output .= $title_string . $article_text;
				
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


SpecialPage::addPage( new MLBTopBattingCategoryPages );

 


}

?>