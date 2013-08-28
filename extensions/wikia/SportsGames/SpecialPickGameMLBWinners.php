<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameMLBWinners';

function wfSpecialPickGameMLBWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameMLBWinners extends SpecialPage {

	
	function PickGameMLBWinners(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameMLBWinners");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
			
	
	    //if (isset($_GET["date"])) {
	    
		global $wgRequest;
		$oneDay = 60*60*24;
		
		// just for baseball
		$sport_id = 1;
	
	
		if ($wgRequest->getVal("date") == "") {
			
			$curDateUnix = time();
			
			$curGetDay = $curDateUnix - $oneDay;
			$curDate = date("Ymd", $curGetDay);
		}
		else {
			$curDate = $wgRequest->getVal("date");
		}
	
		
		$output = "date: " . $curDate . "<br/>";
	
	       
		//$handle = fopen("http://www.sportsline.com/mlb/scoreboard/" . $_GET["date"], "r");
		$handle = fopen("http://www.sportsline.com/mlb/scoreboard/" . $curDate, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		$startpos = strpos($contents, "All times are US/Eastern");
		$contents = substr($contents, $startpos, strlen($contents)-$startpos);
		
		$startpos = strpos($contents, "<div class=SLTables1>");
		$endpos = strpos($contents, "<!-- from content-end-rail-none.html -->");
		$contents = substr($contents, $startpos, $endpos-$startpos);
		
		$startpos = strpos($contents, "<td  class=columnrow align=left width=25%><b>")+ strlen("<td  class=columnrow align=left width=25%><b>");
			$endpos = strpos($contents, "</b>", $startpos);
		
			$gameStatus = substr($contents, $startpos, $endpos - $startpos);
			//echo $gameStatus . "<br/>";
			
			$contents = substr($contents, $endpos);
			
		
		//$startpos = strpos($contents, "<td id=plays");
		//$startpos = strpos($contents, "<a href=\"/mlb/teams/page/");
		$startpos = strpos($contents, "<a href=/mlb/teams/page/");
		//echo strlen($contents) . "<br/>";
		//echo $startpos . "<br/>";
		//echo $contents . "<br/>";
		$games = array();
		$gameIdentifiers = array();
		
		
		
		
		while ($startpos > 0) {
	
		    //$endpos = strpos($contents, "</table>", $startpos);
	
		    $startpos = $startpos + strlen("<a href=/mlb/teams/page/");
		    $endpos = strpos($contents, " ", $startpos);
	
		    //get visiting team abbr from url
		    $visTeamAbbr = substr($contents, $startpos, $endpos - $startpos);
	
		    $contents = substr($contents, $endpos);
	
		    $startpos = strpos($contents, "<b>") + strlen("<b>");
		    $endpos = strpos($contents, "</b>");
	
		    //get visiting team name from link
		    $visTeamName = substr($contents, $startpos, $endpos-$startpos);
	
		    $contents = substr($contents, $endpos);
		    
		    if (strpos($gameStatus, "Final") === 0) {
		    
			    //get visiting team score from boxscore
					$startpos = strpos($contents, "<td  class=finalscore align=center><b>") + strlen("<td  class=finalscore align=center><b>");
					$endpos = strpos($contents, "</b>", $startpos);
						   
			    $visTeamScore = substr($contents, $startpos, $endpos-$startpos);
			    
			    $contents = substr($contents, $endpos);
		    }
		else {
		    $endpos = strpos($contents, "</tr>");
		    $contents = substr($contents, $endpos);
	
			$visTeamScore = "0";
			}
	
				 
		    //get home team abbr from url
	
		    $startpos = strpos($contents, "<a href=/mlb/teams/page/") + strlen("<a href=/mlb/teams/page/");
		    $endpos = strpos($contents, " ", $startpos);
	
		    $homeTeamAbbr = substr($contents, $startpos, $endpos - $startpos);
	
		    $contents = substr($contents, $endpos);
	
		    $startpos = strpos($contents, "<b>") + strlen("<b>");
		    $endpos = strpos($contents, "</b>");
	
		    //get home team name from link
		    $homeTeamName = substr($contents, $startpos, $endpos-$startpos);
	
		    $contents = substr($contents, $endpos);
		    
		    if (strpos($gameStatus, "Final") === 0) {
		    
		    //get home team score from boxscore
				$startpos = strpos($contents, "<td  class=finalscore align=center><b>") + strlen("<td  class=finalscore align=center><b>");
				$endpos = strpos($contents, "</b>", $startpos);
					   
		    $homeTeamScore = substr($contents, $startpos, $endpos-$startpos);
		    
		    $contents = substr($contents, $endpos);
		    }
			else {
			 $endpos = strpos($contents, "</tr>");
			 $contents = substr($contents, $endpos);
			$homeTeamScore = "0";
			}
	
	
	
		    
		    
		    $startpos = strpos($contents, "<td id=plays");
		    //$contents = substr($contents, $startpos);
	
	
	
		
		    $endpos = strpos($contents, "</td>", $startpos) + strlen("</td>");
		    $chunk = substr($contents, $startpos, $endpos-$startpos);
		    //echo strlen($chunk) . "<br/>";
		
		    $contents = substr($contents, $endpos);
		    
		    //
		
		    //$games[sizeof($games)] = array("visTeam" => $visTeamAbbr, "visTeamName" => $visTeamName, "visPitcher"=> $visPitcher, "visPitcherWins"=>$visPitcherWins, "visPitcherLosses"=>$visPitcherLosses, "visPitcherEra"=>$visPitcherEra, "homeTeam" => $homeTeamAbbr, "homeTeamName" => $homeTeamName,  "homePitcher"=> $homePitcher, "homePitcherWins"=>$homePitcherWins, "homePitcherLosses"=>$homePitcherLosses, "homePitcherEra"=>$homePitcherEra);
		    
		    
		    $gameIdentifier = "MLB_" . $curDate . "_" . $visTeamAbbr . "@" . $homeTeamAbbr;
		    if (!isset($gameIdentifiers[$gameIdentifier])) {
					$gameIdentifiers[$gameIdentifier] = 1;
			}
			else {
				$gameIdentifier = $gameIdentifier . "_2";
				$gameIdentifiers[$gameIdentifier] = 1;				
			}
			
			if ($homeTeamScore > $visTeamScore) {
				$winner = $homeTeamName;
				$winnerAbbr = $homeTeamAbbr;
				$statusCode = 1;
			}
			else if ($homeTeamScore < $visTeamScore) {
				$winner = $visTeamName;
				$winnerAbbr = $visTeamAbbr;				
				$statusCode = 1;
			}
			else {
				$winner = "-";
				$winnerAbbr = "-";				
				$statusCode = 0;			
			}
		    
		
		$games[sizeof($games)] = array("visTeam" => $visTeamAbbr, "visTeamName" => $visTeamName, "visTeamScore"=> $visTeamScore, "homeTeam" => $homeTeamAbbr, "homeTeamName" => $homeTeamName,  "homeTeamScore"=> $homeTeamScore, "gameStatus" => $gameStatus, "gameIdentifier" => $gameIdentifier, "winner"=> $winner, "winnerAbbr" => $winnerAbbr, "statusCode" => $statusCode);
		
			    $contents = substr($contents, strpos($contents, "<span id=board"));
		    
			$startpos = strpos($contents, "<td  class=columnrow align=left width=25%><b>")+ strlen("<td  class=columnrow align=left width=25%><b>");
			$endpos = strpos($contents, "</b>", $startpos);
		
			$gameStatus = substr($contents, $startpos, $endpos - $startpos);
			
			$contents = substr($contents, $endpos);
	
		    $startpos = strpos($contents, "<a href=/mlb/teams/page/");
	
		
		}
		
		   //$output .= "<table><tbody><tr><td>game</td><td>Visting Team</td><td>Vis Team Score</td><td>&nbsp;</td><td>Home Team</td><td>Home Team Score</td><td>Winner</td></tr>";
		   
		   
	      
		for ($i=0; $i<sizeof($games); $i++) {
			
			$result_info = $this->MLBresult_exists($games[$i]["gameIdentifier"]);
			$transaction_result = "";
			
			if ($result_info["game_id"] >=0 && !$result_info["was_found"]) {
				$this->do_MLBresult_insert($result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "inserting - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
				
			}
			else if ($result_info["game_id"] >=0 && $result_info["was_found"]) {
				$this->do_MLBresult_update($result_info["result_id"], $result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "updating - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
				
			}
			else {
				//$output .= "error in getting the result check for {$games[$i]["gameIdentifier"]} <br/>";
				$output .= "error - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
			}
			
			/*
							    $output .= "<tr><td>" . $games[$i]["visTeam"] . " @ " . $games[$i]["homeTeam"] . "</td>";
		    $output .= "<td>" .  $games[$i]["visTeamName"] . "</td>";
		    $output .= "<td align='center'>" . $games[$i]["visTeamScore"] . "</td>";
		    $output .= "<td>&nbsp;</td>";
		    $output .= "<td>" .  $games[$i]["homeTeamName"] . "</td>";
		     $output .= "<td align='center'>" . $games[$i]["homeTeamScore"] . "</td>";
		     $output .= "<td>" . $games[$i]["winnerAbbr"] . "</td>";
				
		$output .= "<td>" . $games[$i]["gameIdentifier"] . "</td>";
	
		*/
				
			
		    //$output .= "</tr>";
		    
		}      
		//$output .= "</tbody></table>";
	    
		$title = "MLB results for {$curDate}";
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);	    
	}
	
	function do_MLBresult_insert($game_id,$vis_score,$home_score,$winner,$winner_abbr,$status,$status_desc,$sport_id,$identifier) {
		global $wgUser;
		$dbr =& wfGetDB( DB_MASTER );
		$dbr->insert( '`pick_games_results`',
		array(
			'pick_game_id' => $game_id,
			'home_score' => $home_score,
			'vis_score' => $vis_score,
			'game_winner' => $winner,
			'game_winner_abbr' => $winner_abbr,
			'game_status' => $status,
			'game_status_desc' => $status_desc,
			'sport_id' => $sport_id,
			'game_identifier' => $identifier,
			'update_date' => date("Y-m-d H:i:s")
			), __METHOD__
		);	
		return $dbr->insertId();
	}
	
	function do_MLBresult_update($result_id, $game_id,$vis_score,$home_score,$winner,$winner_abbr,$status,$status_desc,$sport_id,$identifier) {
		global $wgUser;
		$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`pick_games_results`',
		array(
			'pick_game_id' => $game_id,
			'home_score' => $home_score,
			'vis_score' => $vis_score,
			'game_winner' => $winner,
			'game_winner_abbr' => $winner_abbr,
			'game_status' => $status,
			'game_status_desc' => $status_desc,
			'sport_id' => $sport_id,
			'game_identifier' => $identifier,
			'update_date' => date("Y-m-d H:i:s")
			),
		array(
			'game_result_id' => $result_id,
		)
			, __METHOD__
		);	
		//return $dbr->insertId();
	}
	
	
	function MLBresult_exists($pick_identifier) {
	
		$dbr =& wfGetDB( DB_MASTER );
		//$s = $dbr->selectRow( 'pick_games', array( 'pick_identifier' ), array( 'pick_identifier' => $pick_identifier ),"" );
		
		$sql = "SELECT pick_games.pick_game_id, pick_games_results.game_result_id FROM pick_games LEFT OUTER JOIN pick_games_results ON pick_games.pick_game_id = pick_games_results.pick_game_id WHERE pick_games.pick_identifier = '{$pick_identifier}'";
	
	
		$returnVals = array();
		
		
		$res = $dbr->query($sql);
		if ($row = $dbr->fetchObject( $res ) ) {
			
			 $returnVals["game_id"] = $row->pick_game_id;
			 if (!is_null($row->game_result_id)) {
				 $returnVals["was_found"] = true;
				 $returnVals["result_id"] = $row->game_result_id;
			 }
			 else {
				 $returnVals["was_found"] = false;
				 $returnVals["result_id"] = -1;
			 }
	
	
		}
		else {
			$returnVals["game_id"] = -1;
			$returnVals["was_found"] = false;
			$returnVals["result_id"] = -1;
	
		}
		
		return $returnVals;
	
	}		
}

	
	



SpecialPage::addPage( new PickGameMLBWinners );



}

?>
