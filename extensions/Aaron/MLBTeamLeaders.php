<?php

$wgExtensionFunctions[] = 'wfMLBTeamLeaders';

function wfMLBTeamLeaders(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MLBTeamLeaders extends SpecialPage {

	
	function MLBTeamLeaders(){
		UnlistedSpecialPage::UnlistedSpecialPage("MLBTeamLeaders");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		set_time_limit(0);
		$wgUser = User::newFromName( "MLB Stats Bot" );
		$wgUser->addGroup( 'bot' );
		
		
		$batting_cat_text = array (
			
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
		
		$teams = array ( 
			
			"Anaheim Angels"=>"(team='LA  A' AND year>=1961 AND year<=1964) OR (team='CAL A' AND year>=1965 AND year<=1996) OR (team='ANA A' AND year>=1997 AND year<=9999)",
			"Arizona Diamondbacks"=>"(team='ARI N' AND year>=1998 AND year<=9999)",
			"Atlanta Braves"=>"(team='BOS N' AND year=1911) OR (team='BOS n' AND year>=1871 AND year<=1875) OR (team='BOS N' AND year>=1876 AND year<=1882) OR (team='BOS N' AND year>=1883 AND year<=1952) OR (team='MIL N' AND year>=1953 AND year<=1965) OR (team='ATL N' AND year>=1966 AND year<=9999)",
			"Baltimore Orioles"=>"(team='MIL A' AND year=1901) OR (team='STL A' AND year>=1902 AND year<=1953) OR (team='BAL A' AND year>=1954 AND year<=9999)",
			"Boston Red Sox"=>"(team='BOS A' AND year>=1901 AND year<=1907) OR (team='BOS A' AND year>=1908 AND year<=9999)",
			"Chicago Cubs"=>"(team='CHI n' AND year>=1871 AND year<=1873) OR (team='CHI N' AND year>=1874 AND year<=1889) OR (team='CHI N' AND year>=1890 AND year<=1897) OR (team='CHI N' AND year>=1898 AND year<=1901) OR (team='CHI N' AND year>=1902 AND year<=9999)",
			"Chicago White Sox"=>"(team='CHI A' AND year>=1901 AND year<=9999)",
			"Cincinnati Reds"=>"(team='CIN a' AND year>=1882 AND year<=1889) OR (team='CIN N' AND year>=1890 AND year<=9999)",
			"Cleveland Indians"=>"(team='CLE A' AND year=1901) OR (team='CLE A' AND year=1902) OR (team='CLE A' AND year>=1903 AND year<=1914) OR (team='CLE A' AND year>=1915 AND year<=9999)",
			"Colorado Rockies"=>"(team='COL N' AND year>=1993 AND year<=9999)",
			"Detroit Tigers"=>"(team='DET A' AND year>=1901 AND year<=9999)",
			"Florida Marlins"=>"(team='FLA N' AND year>=1993 AND year<=9999)",
			"Houston Astros"=>"(team='HOU N' AND year>=1962 AND year<=1964) OR (team='HOU N' AND year>=1965 AND year<=9999)",
			"Kansas City Royals"=>"(team='KC  A' AND year>=1969 AND year<=9999)",
			"Los Angeles Dodgers"=>"(team='BRO a' AND year>=1884 AND year<=1887) OR (team='BRO a' AND year>=1888 AND year<=1889) OR (team='BRO N' AND year>=1890 AND year<=1898) OR (team='BRO N' AND year>=1899 AND year<=1917) OR (team='BRO N' AND year>=1918 AND year<=1931) OR (team='BRO N' AND year>=1932 AND year<=1957) OR (team='LA  N' AND year>=1958 AND year<=9999)",
			"Milwaukee Brewers"=>"(team='SEA A' AND year=1969) OR (team='MIL A' AND year>=1970 AND year<=1997) OR (team='MIL N' AND year>=1998 AND year<=9999)",
			"Minnesota Twins"=>"(team='WAS A' AND year>=1901 AND year<=1960) OR (team='MIN A' AND year>=1961 AND year<=9999)",
			"New York Mets"=>"(team='NY  N' AND year>=1962 AND year<=9999)",
			"New York Yankees"=>"(team='BAL A' AND year>=1901 AND year<=1902) OR (team='NY  A' AND year>=1903 AND year<=1912) OR (team='NY  A' AND year>=1913 AND year<=9999)",
			"Oakland Athletics"=>"(team='PHI A' AND year>=1901 AND year<=1954) OR (team='KC  A' AND year>=1955 AND year<=1967) OR (team='OAK A' AND year>=1968 AND year<=9999)",
			"Philadelphia Phillies"=>"(team='PHI N' AND year>=1883 AND year<=9999)",
			"Pittsburgh Pirates"=>"(team='PIT a' AND year>=1882 AND year<=1886) OR (team='PIT N' AND year>=1887 AND year<=9999)",
			"San Diego Padres"=>"(team='SD  N' AND year>=1969 AND year<=9999)",
			"San Francisco Giants"=>"(team='NY  N' AND year>=1883 AND year<=1888) OR (team='NY  N' AND year>=1889 AND year<=1957) OR (team='SF  N' AND year>=1958 AND year<=9999)",
			"Seattle Mariners"=>"(team='SEA A' AND year>=1977 AND year<=9999)",
			"St. Louis Cardinals"=>"(team='STL a' AND year>=1882 AND year<=1891) OR (team='STL N' AND year>=1892 AND year<=1898) OR (team='STL N' AND year=1899) OR (team='STL N' AND year>=1900 AND year<=9999)",
			"Tampa Bay Devil Rays"=>"(team='TB  A' AND year>=1998 AND year<=9999)",
			"Texas Rangers"=>"(team='WAS A' AND year>=1961 AND year<=1971) OR (team='TEX A' AND year>=1972 AND year<=9999)",
			"Toronto Blue Jays"=>"(team='TOR A' AND year>=1977 AND year<=9999)",
			"Washington Nationals"=>"(team='MON N' AND year>=1969 AND year<=2004) OR (team='WAS N' AND year>=1972 AND year<=9999)"
			
			);
		
		
		foreach ($batting_cat_text as $stat=>$stat_title) {
		
			foreach ($teams as $team_name=>$team_query) {
			
				$dbr =& wfGetDB( DB_MASTER );
				
				$sql = "SELECT * FROM (SELECT mlb_batting_stats.player_id, sum({$stat}) as statistic, player_name FROM mlb_batting_stats JOIN mlb_players_info ON mlb_batting_stats.player_id=mlb_players_info.player_id WHERE ($team_query) GROUP BY player_id, player_name) as data ORDER BY statistic DESC LIMIT 0,25";
				$res = $dbr->query($sql);
				
				
				$title_string = "";
				$article_text="";
				
				
				$title_string .= "List of {$team_name} players with the most {$stat_title}";

				$article_text .= "";

				$article_text .= "{| border=\"1\" cellpadding=\"3\" cellspacing=\"0\" width=\"500\" class=\"player-profile-stats\"\n|- class=\"player-profile-stats-header\"\n|width=\"25\"|'''Rank'''\n|'''Player Name'''\n|'''{$stat}s'''\n";

				$x=1;
				$player_stat_check=0;

				
				while ( $row = $dbr->fetchObject( $res ) ) {

					$player_name = $row->player_name;
					$player_stat = $row->statistic;
					
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
			
				$categories = array( "Lists", "Statistical Lists", "Baseball Lists","Batting Lists","MLB Lists", "{$team_name} Lists", "{$stat} Lists");
				foreach($categories as $ctg){
					$article_text .= "[[Category:{$ctg}]]\n";
					$this->makeCategory($ctg);
				}
				
				$title = Title::makeTitleSafe( NS_MAIN, $title_string );
				$article = new Article($title);
				$article->doEdit( $article_text, "MLB Team Leader Lists");
				
				
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
			sleep(2);
		}
	}
	
}

SpecialPage::addPage( new MLBTeamLeaders );

 


}

?>