<?php

$wgExtensionFunctions[] = 'wfMLBPitchingPages';

function wfMLBPitchingPages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBPitchingPages extends SpecialPage {

	
	function MLBPitchingPages(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBPitchingPages");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		$stat = array ( 
			"W"=>array ("50"=>"wins","100"=>"wins","150"=>"wins","200"=>"wins","250"=>"wins","300"=>"wins","400"=>"wins","500"=>"wins"),
			"L"=>array ("50"=>"losses","100"=>"losses","150"=>"losses","200"=>"losses","250"=>"losses","300"=>"losses"),
			"SO"=>array ("100"=>"strikeouts","200"=>"strikeouts","500"=>"strikeouts","1000"=>"strikeouts","1500"=>"strikeouts","2000"=>"strikeouts", "3000"=>"strikeouts","4000"=>"strikeouts","5000"=>"strikeouts"),
			"SH"=>array ("25"=>"shutouts","50"=>"shutouts","75"=>"shutouts","100"=>"shutouts","150"=>"shutouts","200"=>"shutouts","300"=>"shutouts","400"=>"shutouts","500"=>"shutouts"),
			"SV"=>array ("50"=>"saves","100"=>"saves","150"=>"saves","200"=>"saves","250"=>"saves","300"=>"saves", "400"=>"saves"),
			"HR"=>array ("50"=>"home runs allowed","100"=>"home runs allowed","150"=>"home runs allowed","200"=>"home runs allowed","250"=>"home runs allowed","300"=>"home runs allowed", "400"=>"home runs allowed","500"=>"home runs allowed"),
			"CG"=>array ("25"=>"complete games","50"=>"complete games","100"=>"complete games","150"=>"complete games","200"=>"complete games","250"=>"complete games","300"=>"complete games","400"=>"complete games","500"=>"complete games","600"=>"complete games","700"=>"complete games")
			
			);
		
		foreach ($stat as $each_stat=>$each_stat_array) {
		
			foreach ($each_stat_array as $stat_milestone=>$stat_title) {

				//database calls
				$dbr =& wfGetDB( DB_MASTER );
				$sql = "SELECT {$each_stat}, player_name FROM mlb_pitching_stats_total INNER JOIN mlb_players_info ON mlb_pitching_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC";
				$res = $dbr->query($sql);

				$sql_count = "SELECT COUNT(*) as stat_count FROM mlb_pitching_stats_total WHERE {$each_stat}>={$stat_milestone} and year=9999 and team=''";
				$res_count = $dbr->query($sql_count);
				$row_count = $dbr->fetchObject( $res_count );
				$total_for_stat = $row_count->stat_count;
				$limit_total_for_stat = $total_for_stat-1;
				
				$sql_first = "SELECT {$each_stat}, player_name FROM mlb_pitching_stats_total INNER JOIN mlb_players_info ON mlb_pitching_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC LIMIT 0,1";
				$res_first = $dbr->query($sql_first);
				$row_first = $dbr->fetchObject( $res_first );
				$first_name = $row_first->player_name;
				$first_stat = $row_first->$each_stat;
				
				$sql_last = "SELECT {$each_stat}, player_name FROM mlb_pitching_stats_total INNER JOIN mlb_players_info ON mlb_pitching_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}>={$stat_milestone} and year=9999 and team='' ORDER BY {$each_stat} DESC";
				
				if($total_for_stat>0) {
					$sql_last .= " LIMIT {$limit_total_for_stat}, {$total_for_stat}";
				}
				
				$res_last = $dbr->query($sql_last);
				$row_last = $dbr->fetchObject( $res_last );
				$last_name = $row_last->player_name;
				$last_stat = $row_last->$each_stat;
				
				$sql_last_count = "SELECT COUNT(*) as last_count FROM mlb_pitching_stats_total INNER JOIN mlb_players_info ON mlb_pitching_stats_total.player_id=mlb_players_info.player_id  WHERE {$each_stat}={$stat_milestone} and year=9999 and team=''";
				$res_last_count = $dbr->query($sql_last_count);
				$row_last_count = $dbr->fetchObject( $res_last_count);
				
				
				$title_string = "";
				$article_text="";
				
				
				$title_string .= "List of pitchers with ".number_format($stat_milestone)." or more {$stat_title}";

				$article_text .= "";
				
				if ($total_for_stat>1) {
					$article_text = "There are ".number_format($total_for_stat)." pitchers who have ".number_format($stat_milestone)." or more {$stat_title}.  [[{$first_name}]] leads all pitchers with ".number_format($first_stat)." {$stat_title}.  ";
					
					if (($row_last_count->last_count>1) && (($stat_milestone-$last_stat)<10)) {
						$article_text .= "[[$last_name]], and ".($row_last_count->last_count-1)." other ".((($row_last_count->last_count-1)==1)?"player":"players").", barely reached this plateau with {$last_stat} {$stat_title}.  "; 
					} else {
					    $article_text .= "[[{$last_name}]] just reached this milestone with ".number_format($last_stat)." total {$stat_title}.  ";	
					}
					
				} else {
					$article_text = "Only one pitcher, [[{$first_name}]], has more than " .number_format($stat_milestone)." {$stat_title}.  This is one of the more difficult milestones to reach in baseball.";
				}
				
				$article_text .= "\n\n\n\n";

				$article_text .= "{| table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|-class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n|'''Number of {$each_stat}s'''\n";

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

SpecialPage::addPage( new MLBPitchingPages );

 


}

?>