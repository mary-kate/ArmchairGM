<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameNBAWinners';

function wfSpecialPickGameNBAWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameNBAWinners extends SpecialPage {

	
	function PickGameNBAWinners(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameNBAWinners");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
			
	
	    //if (isset($_GET["date"])) {
	    
		global $wgRequest;
		$oneDay = 60*60*24;
		
		// just for basketball
		$sport_id = 3;
	
	
		if ($wgRequest->getVal("date") == "") {
			
			$curDateUnix = time();
			
			$curGetDay = $curDateUnix - $oneDay;
			$curDate = date("Ymd", $curGetDay);
		}
		else {
			$curDate = $wgRequest->getVal("date");
		}
		
		$current_category = $curDate;
	
		    
		    		$handle = fopen("http://www.sportsline.com/nba/scoreboard/" . $current_category, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		$games = array();
		//$game_count = 0;
		$output = "";
		
		//$re_pattern = "/\<div[\s]+class=SLTables1>\<table[\s]+width=100\%[\s]+cellpadding=2[\s]+cellspacing=1[\s]+border=0>\<tr[\s]+height=17[\s]+id=head[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+class=columnrow[\s]+align=left[\s]+width=50\%>\<b>([^\<]+)\<\/b>\<\/td>.*\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+align=left>\<a href=\"\/nba\/teams\/page\/([^\"]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[\s]+class=finalscore[\s]+align=middle>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[\s]+align=left>\<a href=\"\/nba\/teams\/page\/([^\<]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[\s]+class=finalscore[\s]+align=middle>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<\/table>\<\/div>/i";
		$re_pattern = "/\<div[\s]+class=SLTables1>\<table[\s]+width=100\%[\s]+cellpadding=2[\s]+cellspacing=1[\s]+border=0>\<tr[\s]+height=17[\s]+id=head[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[^>]+>\<b>([^\<]+)\<\/b>\<\/td>.*\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[^>]+>\<a href=\"\/nba\/teams\/page\/([^\"]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[^>]+>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<tr[\s]+height=17[\s]+id=final[\s]+class=bg2[\s]+align=right[\s]+valign=middle>\<td[^>]+>\<a href=\"\/nba\/teams\/page\/([^\<]+)\">\<b>([^\<]+)\<\/b>\<\/a>.*\<td[^>]+>\<b>([^\<]+)\<\/b>\<\/td>\<\/tr>\<\/table>\<\/div>/i";

		
		$total_matches = preg_match_all($re_pattern, $contents, $matches);
		
		$output .= $total_matches . "<br/><br/>";
		
		
		for ($i=0; $i<$total_matches; $i++) {

			$game_identifier = "NBA_{$current_category}_{$matches[2][$i]}@{$matches[5][$i]}";

			if ($matches[7][$i] > $matches[4][$i]) {
				$winner = $matches[6][$i];
				$winnerAbbr = $matches[5][$i];
				$statusCode = 1;
			}
			else if ($matches[7][$i] < $matches[4][$i]) {
				$winner = $matches[3][$i];
				$winnerAbbr = $matches[2][$i];
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
				"visTeamScore"=>$matches[4][$i],
				"visTeamAbbr"=>$matches[2][$i],
				"homeTeam"=>$matches[6][$i],
				"homeTeamScore"=>$matches[7][$i],
				"homeTeamAbbr"=>$matches[5][$i]
				);
/*
			for ($j=1; $j<sizeof($matches); $j++) {
				output .= $matches[$j][$i] . " ";
			}
			*/
		}
		
		
		foreach ($games as $id=>$game) {
			foreach($game as $key=>$value) {
				//$output .= "{$key}: {$value}; ";
				$output .= "{$value} ";
			}
			$output .= "<br/>";
		}
		$output .= "<br/>";

		//echo $output;
	
	
		for ($i=0; $i<sizeof($games); $i++) {
			
			//$output .= "{$i}<br/>";
			
			
			$result_info = PickGameMLBWinners::MLBresult_exists($games[$i]["gameIdentifier"]);
			$transaction_result = "";
			
			if ($result_info["game_id"] >=0 && !$result_info["was_found"]) {
				PickGameMLBWinners::do_MLBresult_insert($result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "inserting - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} - {$games[$i]["statusCode"]}  <br/>"; 
				
			}
			else if ($result_info["game_id"] >=0 && $result_info["was_found"]) {
				PickGameMLBWinners::do_MLBresult_update($result_info["result_id"], $result_info["game_id"],$games[$i]["visTeamScore"],$games[$i]["homeTeamScore"],$games[$i]["winner"],$games[$i]["winnerAbbr"],$games[$i]["statusCode"],$games[$i]["gameStatus"],$sport_id,$games[$i]["gameIdentifier"]);
				$output .= "updating - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} - {$games[$i]["statusCode"]}  <br/>"; 
				
			}
			else {
				//$output .= "error in getting the result check for {$games[$i]["gameIdentifier"]} <br/>";
				$output .= "error - {$games[$i]["visTeam"]} {$games[$i]["visTeamScore"]} {$games[$i]["homeTeam"]} {$games[$i]["homeTeamScore"]} | {$games[$i]["gameStatus"]} - {$games[$i]["statusCode"]} <br/>"; 
			}
			
			
		}

	
	
	    
		$title = "NBA results for {$curDate}";
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);	    
	}

}

	
	



SpecialPage::addPage( new PickGameNBAWinners );



}

?>
