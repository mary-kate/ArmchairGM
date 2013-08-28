<?php

$wgExtensionFunctions[] = 'wfSpecialPlayerStatsBreaker';

function wfSpecialPlayerStatsBreaker(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PlayerStatsBreaker extends SpecialPage {

	
	function PlayerStatsBreaker(){
		UnlistedSpecialPage::UnlistedSpecialPage("PlayerStatsBreaker");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$wgUser = User::newFromName( "NFL Stats Bot" );
		$wgUser->addGroup( 'bot' );

		$letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$output = "";
		foreach ($letters as $key=>$letter) {
			$output .= $letter . "<br/>" . $this->getStuff($letter) . "<br/><br/>";
		}
		
		$title = "Breaker";
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);

	}
	
	function getStuff($letter) {
		
		$mystr = $this->getBotReport($letter);
		
		$site = "http://fp029.sjc.wikia-inc.com";
		//$site = "http://www.armchairgm.com";
		
		if ($mystr == "Page Does Not Exist") {
			return $mystr;
		}
		
		$re_pattern = "/skipped \<b>([^\<]+)\<\/b> because edited by outsider. \[\[[^\]]+\]\]\<br>/";
		$re_pattern_ambig = "/skipped \<b>([^\<]+)\<\/b> because name is ambiguous. \([0-9]+ people\)\<br\/>/";
		
		$total_matches = preg_match_all($re_pattern, $mystr, $matches);
		$total_matches_ambig = preg_match_all($re_pattern_ambig, $mystr, $matches_ambig);
		
		$output = "";
		$output_ambig = "";
		
		for ($i=0; $i<$total_matches; $i++) {
			$output .=  $matches[1][$i] . " ([{$site}/index.php?title=Special:NFLPlayerUpdater&playername=". urlencode($matches[1][$i]) . "  update link])<br/>\n";
		}
		
		
		$dbr =& wfGetDB( DB_MASTER );
		
		
		for ($i=0; $i<$total_matches_ambig; $i++) {
			//echo $matches[1][$i] . " ([http://www.armchairgm.com/index.php?title=Special:MLBPlayerUpdater&playername=". urlencode($matches[1][$i]) . "  update link])<br/>\n";
			//$sql = "SELECT * FROM mlb_players_info, mlb_fielding_stats where player_name='{$matches[1][$i]}'";
			//$sql = "SELECT distinct(mlb_players_info.player_id), player_name, player_fullname, birthday, height, weight, year FROM mlb_players_info, mlb_fielding_stats where player_name='". str_replace("'", "''", $matches_ambig[1][$i]) ."' AND mlb_players_info.player_id=mlb_fielding_stats.player_id AND mlb_fielding_stats.year in (SELECT MIN(year) FROM mlb_fielding_stats WHERE player_id=mlb_players_info.player_id) ORDER BY year ASC";
			$sql = "SELECT distinct(nfl_players_info.player_id), player_name, player_fullname, birthday, height, weight, year FROM nfl_players_info, nfl_fumbles_stats where player_name='". str_replace("'", "''", $matches_ambig[1][$i]) ."' AND nfl_players_info.player_id=nfl_fumbles_stats.player_id AND nfl_fumbles_stats.year in (SELECT MIN(year) FROM nfl_fumbles_stats WHERE player_id=nfl_players_info.player_id) ORDER BY year ASC";
			
			$count = 0;
			$result = mysql_query($sql);
			while($row = mysql_fetch_array($result)) {
				//$players[] = $row; 
				$count++;
				//$disambig_name = "{$row["player_name"]} (". romanNumeral($count) . ")";
				$disambig_name = "{$row["player_fullname"]}";
				//echo "{$row["player_name"]} ({$row["player_fullname"]}) Born: {$row["birthday"]} Height: {$row["height"]} Weight: {$row["weight"]}[http://www.armchairgm.com/index.php?title=Special:PlayerUpdater&playerid={$row["player_id"]}&disambigname=".urlencode($row["player_fullname"])." Update Link]<br/>\n";
				$output_ambig .= "{$row["player_name"]} ({$row["player_fullname"]}) Born: {$row["birthday"]} [{$site}/index.php?title=Special:NFLPlayerUpdater&playerid={$row["player_id"]}&disambigname=".urlencode($row["player_fullname"])." Update Link]<br/>\n";
			}
			mysql_free_result($result);
			/*for ($j=0; $j<sizeof($players); $j++) {
				echo "<a href=\"/index.php?title={$matches[1][$i]}&playerid={$players[$j]["player_id"]}\">{$players[$j]["player_fullname"]} ({$players[$j]["player_name"]}) {$players[$j]["birthday"]} {$players[$j]["bats"]} {$players[$j]["throws"]} {$players[$j]["height"]} {$players[$j]["weight"]}</a><br/>";
			}*/
		
		}
		
		$this->createUpdatePage($output, $letter);
		$this->createUpdatePageAmbig($output_ambig, $letter);
		
		return $output . "<br><br>" . $output_ambig;


		
	}

	function getBotReport($letter) {
		
		$page_title = Title::makeTitleSafe( NS_MAIN, "NFL Stats Bot player generator report for " . $letter );
		$article = new Article($page_title);
		$raw_text = new RawPage($article, false);
		$does_exist = $article->exists();
		if ($does_exist) {
			return $raw_text->getArticleText();
		}
		else {
			return "Page Does Not Exist";
		}
			

	}
	
	function createUpdatePage($text, $letter) {
		$title = "NFL Player Page Update Project - " . $letter;
		$page_title = Title::makeTitleSafe( NS_MAIN, $title );
		$article = new Article($page_title);
		$article->doEdit( $text, "NFL Player Update Project", EDIT_SUPPRESS_RC);
	}
	
	function createUpdatePageAmbig($text, $letter) {
		$title = "NFL Player Page Update Project (Disambigs) - " . $letter;
		$page_title = Title::makeTitleSafe( NS_MAIN, $title );
		$article = new Article($page_title);
		$article->doEdit( $text, "NFL Player Update Project", EDIT_SUPPRESS_RC);
	}
	
}


SpecialPage::addPage( new PlayerStatsBreaker );



}

?>
