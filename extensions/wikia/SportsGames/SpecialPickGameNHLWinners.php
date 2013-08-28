<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameNHLWinners';

function wfSpecialPickGameNHLWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameNHLWinners extends SpecialPage {

	
	function PickGameNHLWinners(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameNHLWinners");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
			
	
	    //if (isset($_GET["date"])) {
	    
		global $wgRequest;
		$oneDay = 60*60*24;
		
		// just for hockey
		$sport_id = 4;
	
	
		if ($wgRequest->getVal("date") == "") {
			
			$curDateUnix = time();
			
			$curGetDay = $curDateUnix - $oneDay;
			$curDate = date("Ymd", $curGetDay);
		}
		else {
			$curDate = $wgRequest->getVal("date");
		}
		
		$current_category = $curDate;
	
		    
		    $abbr_translation = array(
			"NJD"=>"NJ",
			"TAM"=>"TB",
			"CLS"=>"CLB",
			"LOS"=>"LA",
			"SAN"=>"SJ"
			);
	    
		
		$handle = fopen("http://scores.espn.go.com/nhl/scoreboard?date=" . $current_category, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		$games = array();
		//$game_count = 0;
		$output = "";
		
		//$re_pattern = "/\<div[\s]+class=SLTables1>\<table[\s]+width=100\%[\s]+cellpadding=2[\s]+cellspacing=1[\s]+border=0>\<tr[\s]+height=17[\s]+id=head[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+class=columnrow[\s]+align=left[\s]+width=50\%>\<b>([^\<]+)\<\/b>\<\/td>.*\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+align=left>\<a href=\"\/nba\/teams\/page\/([^\"]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[\s]+class=finalscore[\s]+align=middle>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+align=left>\<a href=\"\/nba\/teams\/page\/([^\<]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[\s]+class=finalscore[\s]+align=middle>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<\/table>\<\/div>/i";
		$re_pattern = "/\<div[\s]+class=\"gameContainer\"[\s]+id=\"game-[0-9]+\">\n\<div[\s]+class=\"teams\">\n\<table>\<tr>\<td[\s]+class=\"teamTop_inGame\"[\s]+id=\"[^\"]+\">([^\<]+)\<\/td>\<\/tr>\<tr>\<td[\s]+class=\"teamLine\">\<a[\s]+href=\"\/nhl\/clubhouse\?team=([^\"]+)\">([^\<]+)\<\/a>[\s]+\<span id=\"[^\"]+\">.+\<\/span>\<\/td>\<\/tr>\<tr>\<td class=\"teamLine\">\<a href=\"\/nhl\/clubhouse\?team=([^\"]+)\">([^\<]+)\<\/a>[\s]+\<span id=\"[^\"]+\">.+\<\/span>\<\/td>\<\/tr>\<\/table>\<\/div>.*\<td[\s]+class=\"tScoreLine\"[\s]+id=\"[^a]+atot\">([^\<]+)\<\/td>\<\/tr>\<tr>\<td[\s]+class=\"tScoreLine\"[\s]+id=\"[^h]+htot\">([^\<]+)\<\/td>\<\/tr>\<\/table>\<\/div>/i";
		$total_matches = preg_match_all($re_pattern, $contents, $matches);
		
		$output .= $total_matches . "<br/><br/>";
		
		
		for ($i=0; $i<$total_matches; $i++) {
			$home_abbr = isset($abbr_translation[strtoupper($matches[4][$i])]) ? $abbr_translation[strtoupper($matches[4][$i])] : strtoupper($matches[4][$i]);
			$vis_abbr = isset($abbr_translation[strtoupper($matches[2][$i])]) ? $abbr_translation[strtoupper($matches[2][$i])] : strtoupper($matches[2][$i]);
			$game_identifier = "NHL_{$current_category}_{$vis_abbr}@{$home_abbr}";
	
			if ($matches[7][$i] > $matches[6][$i]) {
				$winner = $matches[5][$i];
				$winnerAbbr = $home_abbr;
				$statusCode = 1;
			}
			else if ($matches[7][$i] < $matches[6][$i]) {
				$winner = $matches[3][$i];
				$winnerAbbr = $vis_abbr;
				$statusCode = 1;
			}
			else {
				$winner = "-";
				$winnerAbbr = "-";				
				$statusCode = 0;			
			}
	
		
			$games[] = array(
				"category"=>$current_category,
				"gameIdentifier"=>$game_identifier,
				"winner"=>$winner,
				"winnerAbbr"=>$winnerAbbr,
				"gameStatus"=>$matches[1][$i],
				"statusCode"=>$statusCode,
				"visTeam"=>$matches[3][$i],
				"visTeamScore"=>$matches[6][$i],
				"visTeamAbbr"=>$vis_abbr,
				"homeTeam"=>$matches[5][$i],
				"homeTeamScore"=>$matches[7][$i],
				"homeTeamAbbr"=>$home_abbr
				);
			
			//$output .= "&nbsp;&nbsp;&nbsp;{$current_category}&nbsp;&nbsp;&nbsp;{$gameIdentifier}&nbsp;&nbsp;&nbsp;{$vis_team_name} ({$vis_team_abbr}) ({$vis_team_score}) @ {$home_team_name} ({$home_team_abbr}) ({$home_team_score}) : {$winner} - {$winnerAbbr} -- {$game_status} -- {$statusCode}<br/>";
	
			
		}
		
		foreach ($games as $id=>$game) {
			foreach($game as $key=>$value) {
				//$output .= "{$key}: {$value}; ";
				$output .= "{$value} ";
			}
			$output .= "<br/>";
		}
		$output .= "<br/>";

	
		for ($i=0; $i<sizeof($games); $i++) {
			
			//$output .= "{$i}<br/>";
			
			
			$result_info = PickGameMLBWinners::MLBresult_exists($games[$i]["gameIdentifier"]);
			$transaction_result = "";
			
			if ($result_info["game_id"] >=0 && !$result_info["was_found"]) {
				PickGameMLBWinners::do_MLBresult_insert($result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "inserting - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
				
			}
			else if ($result_info["game_id"] >=0 && $result_info["was_found"]) {
				PickGameMLBWinners::do_MLBresult_update($result_info["result_id"], $result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "updating - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
				
			}
			else {
				//$output .= "error in getting the result check for {$games[$i]["gameIdentifier"]} <br/>";
				$output .= "error - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} <br/>"; 
			}
			
			
		}

	
	
	    
		$title = "NHL results for {$curDate}";
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);	    
	}

}

	
	



SpecialPage::addPage( new PickGameNHLWinners );



}

?>
