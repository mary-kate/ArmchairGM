<?php

$wgExtensionFunctions[] = 'wfMLBBattingPages';

function wfMLBBattingPages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBBattingPages extends SpecialPage {

	
	function MLBBattingPages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBBattingPages");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$stat = array ( 
			"HR"=>array ("200"=>"homeruns","300"=>"homeruns","400"=>"homeruns","500"=>"homeruns","600"=>"homeruns", "700"=>"homeruns"),
			"SB"=>array ("100"=>"stolen bases","200"=>"stolen bases","300"=>"stolen bases","400"=>"stolen bases","500"=>"stolen bases","600"=>"stolen bases","750"=>"stolen bases","1000"=>"stolen bases"),
			"AB"=>array ("500"=>"at bats","1000"=>"at bats","1500"=>"at bats","2000"=>"at bats","2500"=>"at bats","3000"=>"at bats", "4000"=>"at bats","5000"=>"at bats","7500"=>"at bats","10000"=>"at bats"),
			"G"=>array ("100"=>"games played","200"=>"games played","300"=>"games played","400"=>"games played","500"=>"games played","750"=>"games played","1000"=>"games played","1500"=>"games played","2000"=>"games played","2500"=>"games played","3000"=>"games played"),
			"R"=>array ("100"=>"runs scored","200"=>"runs scored","300"=>"runs scored","400"=>"runs scored","500"=>"runs scored","750"=>"runs scored", "1000"=>"runs scored","1500"=>"runs scored","2000"=>"runs scored"),
			"H"=>array ("100"=>"hits","200"=>"hits","300"=>"hits","400"=>"hits","500"=>"hits","750"=>"hits", "1000"=>"hits","1500"=>"hits","2000"=>"hits","2500"=>"hits","3000"=>"hits","3500"=>"hits","4000"=>"hits"),
			"2B"=>array ("100"=>"doubles","200"=>"doubles","300"=>"doubles","400"=>"doubles","500"=>"doubles","600"=>"doubles","700"=>"doubles"),
			"3B"=>array ("100"=>"triples","200"=>"triples","300"=>"triples"),
			"RBI"=>array ("100"=>"runs batted in","200"=>"runs batted in","300"=>"runs batted in","400"=>"runs batted in","500"=>"runs batted in","750"=>"runs batted in","1000"=>"runs batted in","1500"=>"runs batted in","2000"=>"runs batted in"),
			"CS"=>array ("100"=>"times caught stealing","200"=>"times caught stealing","300"=>"times caught stealing"),
			"BB"=>array ("100"=>"walks","200"=>"walks","300"=>"walks","400"=>"walks","500"=>"walks","750"=>"walks","1000"=>"walks","1500"=>"walks","2000"=>"walks","2500"=>"walks"),
			"SO"=>array ("100"=>"strikeouts","200"=>"strikeouts","300"=>"strikeouts","400"=>"strikeouts","500"=>"strikeouts","750"=>"strikeouts","1000"=>"strikeouts","1500"=>"strikeouts","2000"=>"strikeouts")
		
		);
		
		foreach ($stat as $each_stat=>$each_stat_array) {
		
			foreach ($each_stat_array as $stat_milestone=>$stat_title) {

				//database calls
				$dbr =& wfGetDB( DB_MASTER );
				$sql = "SELECT {$each_stat}, player_name FROM mlb_batting_stats_total INNER JOIN mlb_players_info ON mlb_batting_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC";
				$res = $dbr->query($sql);

				$sql_count = "SELECT COUNT(*) as stat_count FROM mlb_batting_stats_total WHERE {$each_stat}>={$stat_milestone} and year=9999 and team=''";
				$res_count = $dbr->query($sql_count);
				$row_count = $dbr->fetchObject( $res_count );
				$total_for_stat = $row_count->stat_count;
				$limit_total_for_stat = $total_for_stat-1;
				
				$sql_first = "SELECT {$each_stat}, player_name FROM mlb_batting_stats_total INNER JOIN mlb_players_info ON mlb_batting_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC LIMIT 0,1";
				$res_first = $dbr->query($sql_first);
				$row_first = $dbr->fetchObject( $res_first );
				$first_name = $row_first->player_name;
				$first_stat = $row_first->$each_stat;
				
				$sql_last = "SELECT {$each_stat}, player_name FROM mlb_batting_stats_total INNER JOIN mlb_players_info ON mlb_batting_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC";
				
				if($total_for_stat>0) {
					$sql_last .= " LIMIT {$limit_total_for_stat}, {$total_for_stat}";
				}
				
				$res_last = $dbr->query($sql_last);
				$row_last = $dbr->fetchObject( $res_last );
				$last_name = $row_last->player_name;
				$last_stat = $row_last->$each_stat;
				
				$sql_last_count = "SELECT COUNT(*) as last_count FROM mlb_batting_stats_total WHERE {$each_stat}={$stat_milestone} and year=9999 and team=''";
				$res_last_count = $dbr->query($sql_last_count);
				$row_last_count = $dbr->fetchObject( $res_last_count);
				
				$title_string = "";
				$article_text="";
				
				$title_string .= "List of baseball players with more than ".number_format($stat_milestone)." {$stat_title}";

				if ($total_for_stat>1) {
					$article_text = "There are ".number_format($total_for_stat)." baseball players who have ".number_format($stat_milestone)." or more {$stat_title}.  [[{$first_name}]] leads all baseball players with ".number_format($first_stat)." {$stat_title}.  ";
					
					if (($row_last_count->last_count>1) && (($stat_milestone-$last_stat)<10)) {
						$article_text .= "[[$last_name]], and ".($row_last_count->last_count-1)." other ".((($row_last_count->last_count-1)==1)?"player":"players").", barely reached this plateau with {$last_stat} {$stat_title}.  "; 
					} else {
					    $article_text .= "[[{$last_name}]] just reached this milestone with ".number_format($last_stat)." total {$stat_title}.  ";	
					}
					
				} else {
					$article_text = "Only one baseball player, [[{$first_name}]], has more than " .number_format($stat_milestone)." {$stat_title}.  This is one of the more difficult milestones to reach in baseball.";
				}
				
				$article_text .= "\n\n\n\n";
				
				$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n| width=\"25\" | '''Rank'''\n| '''Player Name'''\n| '''Number of {$each_stat}s'''\n";

				$x=1;
				$player_stat_check=0;

				while ( $row = $dbr->fetchObject( $res ) ) {

					$player_name = $row->player_name;
					$player_stat = $row->$each_stat;
					
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
				
				$article_text .= "|}\n\n";
				
				$categories = array( "Lists", "Statistical Lists", "Baseball Lists","Batting Lists","MLB Lists", "{$each_stat} Lists", "{$baseball_year} Lists");
				foreach($categories as $ctg){
					$article_text .= "[[Category:{$ctg}]]\n";
					$this->makeCategory($ctg);
				}
				
				
				$title = Title::makeTitleSafe( NS_MAIN, $title_string );
				$article = new Article($title);
				$article->doEdit( $article_text, "MLB Lists");
				
				$output .= "<b>{$title_string}</b> complete. <a href=\"index.php?title={$title_string}\">(link)</a><br>";
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

SpecialPage::addPage( new MLBBattingPages );

 


}

?>