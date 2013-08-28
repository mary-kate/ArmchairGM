<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameNCAAFWinners';

function wfSpecialPickGameNCAAFWinners(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameNCAAFWinners extends SpecialPage {

	
	function PickGameNCAAFWinners(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameNCAAFWinners");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
			
		$ncaaf_months = array(
		"January"=>"01",
		"February"=>"02",
		"March"=>"03",
		"April"=>"04",
		"May"=>"05",
		"June"=>"06",
		"July"=>"07",
		"August"=>"08",
		"September"=>"09",
		"October"=>"10",
		"November"=>"11",
		"December"=>"12"
		);
	
	    //if (isset($_GET["date"])) {
	    
		global $wgRequest;
		$oneDay = 60*60*24;
		
		// just for baseball
		$sport_id = 6;

		$sport_specifics = PickGame::pickgame_getSportSpecifics($sport_id);
		
		if ($wgRequest->getVal($sport_specifics["param"]) == "") {
		
			$curDateUnix = time();
			
			//$curGetDay = $curDateUnix + $oneDay;
			$curGetDay = $curDateUnix;
			$curDate = date("Ymd", $curGetDay);
			$current_category = PickGame::get_category_from_date($curDate, $sport_specifics);		
			
		}
		else {
			//$curDate = $wgRequest->getVal($sport_specific["param"]);
			$current_category = $wgRequest->getVal($sport_specifics["param"]);
			$curDate = PickGame::get_date_from_category($current_category, $sport_specifics);
		}
		
		$current_category = PickGame::check_first_game($current_category, $sport_specifics);	
			
		$output = "date: " . $curDate . "<br/>";
	
	       
		$handle = fopen("http://www.sportsline.com/collegefootball/scoreboard/top25/2007/week" . $current_category, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		
		
		$date_startpos = strpos($contents, "<td class=\"date\">");
		$endpos = strpos($contents, "<!-- from content-end-rail-none.html -->");
		$contents = substr($contents, $date_startpos, $endpos-$date_startpos);
		
		$output = "";

		$days_games = array();
		$games = array();
		//$games_identifiers = array();
		
		while ($date_startpos) {
			$startpos = strlen("<td class='date'>");
			$endpos = strpos($contents, "</td>");
			
			$game_date = substr($contents, $startpos, $endpos-$startpos);
			$startpos = $endpos + strlen("</td>");
			$contents = substr($contents, $startpos);
			
			$date_startpos = strpos($contents, "<td class=\"date\">");

//			$startpos = strpos($contents, "<div class=SLTables1>");
			
			if($date_startpos) {
				$days_games[$game_date] = substr($contents, $startpos, $date_startpos-$startpos);
				$contents = substr($contents, $date_startpos);
			}
			else {
				$days_games[$game_date] = $contents;
			}
		
		}
		
		$localOffset = date("Z");
	
		if (date("I") == "0") {
			$eastOffset = -14400;				
		}
		else {
			$eastOffset = -18000;
		}		
		$theOffset = $localOffset - $eastOffset;

		
		foreach($days_games as $key=>$value) {
			//echo "{$key}: " . strlen($value) . "<br/>";
			$contents = $value;
			$startpos = strpos($contents, "<div class=SLTables1>");
			while ($startpos) {
				$contents = substr($contents, $startpos);
				$startpos = strpos($contents, "<td  class=columnrow align=left width=50%>");
				$status_start = strpos($contents, "<b>", $startpos) + strlen("<b>");
				$endpos =  strpos($contents, "</b>") - $status_start;
				$game_status = substr($contents, $status_start, $endpos);
				$contents = substr($contents, $endpos);
				
				$vis_team_row_start = strpos($contents, "<tr  height=17 id=final");
				$contents = substr($contents, $vis_team_row_start);
				$vis_team_row_end = strpos($contents, "</tr>") + strlen("</tr>");
				$vis_team_row = substr($contents, 0, $vis_team_row_end);
				$contents = substr($contents, $vis_team_row_end);

				$home_team_row_start = strpos($contents, "<tr  height=17 id=final");
				$contents = substr($contents, $home_team_row_start);
				$home_team_row_end = strpos($contents, "</tr>") + strlen("</tr>");
				$home_team_row = substr($contents, 0, $home_team_row_end);
				$contents = substr($contents, $home_team_row_end);
				
				$vta_start = strpos($vis_team_row, "<a href=\"/collegefootball/teams/page/") + strlen("<a href='/collegefootball/teams/page/");
				$vta_end = strpos($vis_team_row, "\">", $vta_start);
				$vis_team_abbr = substr($vis_team_row, $vta_start, $vta_end-$vta_start);
				$vis_team_row = substr($vis_team_row, $vta_end + strlen("'>"));
				$vtn_start = strpos($vis_team_row, "<b>") + strlen("<b>");
				$vtn_end = strpos($vis_team_row, "</b>", $vtn_start);
				$vis_team_name = substr($vis_team_row, $vtn_start, $vtn_end-$vtn_start);
				$vis_team_row = substr($vis_team_row, $vtn_end + strlen("</b>"));
				$vtr_start = strpos($vis_team_row, "<font class=\"rank\">");
				if($vtr_start) {
					$vtr_start += strlen("<font class='rank'>");
					$vtr_end = strpos($vis_team_row, "</font>", $vtr_start);
					$vis_team_rank = substr($vis_team_row, $vtr_start, $vtr_end-$vtr_start);
					$vis_team_row = substr($vis_team_row, $vtr_end + strlen("</font>"));
				}
				else {
					$vis_team_rank = "";
				}
				$vtscore_start = strpos($vis_team_row, "<td  class=finalscore align=center><b>") + strlen("<td  class=finalscore align=center><b>");
				$vtscore_end = strpos($vis_team_row, "</b>", $vtscore_start);
				$vis_team_score = substr($vis_team_row, $vtscore_start, $vtscore_end-$vtscore_start);
				$vis_team_row = substr($vis_team_row, $vtscore_end + strlen("</td>"));
	
				
				$hta_start = strpos($home_team_row, "<a href=\"/collegefootball/teams/page/") + strlen("<a href='/collegefootball/teams/page/");
				$hta_end = strpos($home_team_row, "\">", $hta_start);
				$home_team_abbr = substr($home_team_row, $hta_start, $hta_end-$hta_start);
				$home_team_row = substr($home_team_row, $hta_end + strlen("'>"));
				$htn_start = strpos($home_team_row, "<b>") + strlen("<b>");
				$htn_end = strpos($home_team_row, "</b>", $htn_start);
				$home_team_name = substr($home_team_row, $htn_start, $htn_end-$htn_start);
				$home_team_row = substr($home_team_row, $htn_end + strlen("</b>"));
				$htr_start = strpos($home_team_row, "<font class=\"rank\">");
				if($htr_start) {
					$htr_start += strlen("<font class='rank'>");
					$htr_end = strpos($home_team_row, "</font>", $htr_start);
					$home_team_rank = substr($home_team_row, $htr_start, $htr_end-$htr_start);
					$home_team_row = substr($home_team_row, $htr_end + strlen("</font>"));
				}
				else {
					$home_team_rank = "";
				}
				$htscore_start = strpos($home_team_row, "<td  class=finalscore align=center><b>") + strlen("<td  class=finalscore align=center><b>");
				$htscore_end = strpos($home_team_row, "</b>", $htscore_start);
				$home_team_score = substr($home_team_row, $htscore_start, $htscore_end-$htscore_start);
				$home_team_row = substr($home_team_row, $htscore_end + strlen("</td>"));
				
				//$vis_team_addl = "<b>{$vis_team_rank}</b> ({$vis_team_record})";
				//$home_team_addl = "<b>{$home_team_rank}</b> ({$home_team_record})";
				
				//$game_date = date("Y-m-d H:i:s", $timestamp-$theOffset);
				//$identifier_date = date("Ymd", $timestamp-$theOffset);
				//$game_date = date("Y-m-d H:i:s");
				//$identifier_date = date("Ymd");
				
				$temp_key = $key;
				$month_start = strpos($temp_key, ", ") + strlen(", ");
				$temp_key = substr($temp_key, $month_start);
				$month_end = strpos($temp_key, " ");
				$key_month = substr($temp_key, 0, $month_end);
				$day_start = $month_end + strlen(" ");
				$temp_key = substr($temp_key, $day_start);
				$day_end = strpos($temp_key, ", ");
				$key_day = substr($temp_key, 0, $day_end);
				$key_day = trim($key_day);
				$year_start = $day_end + strlen(", ");
				$temp_key = substr($temp_key, $year_start);
				$key_year = trim($temp_key);
				
				if(strlen(trim($key_day)) == 1) {
					$key_day = "0" . trim($key_day);
				}
				
				$key_month = $ncaaf_months[$key_month];
				
				$identifier_date = "{$key_year}{$key_month}{$key_day}";

				$game_identifier = "NCAAF_{$identifier_date}_{$vis_team_abbr}@{$home_team_abbr}";
				
				if ($home_team_score > $vis_team_score) {
					$winner = $home_team_name;
					$winnerAbbr = $home_team_abbr;
					$statusCode = 1;
				}
				else if ($home_team_score < $vis_team_score) {
					$winner = $vis_team_name;
					$winnerAbbr = $vis_team_abbr;				
					$statusCode = 1;
				}
				else {
					$winner = "-";
					$winnerAbbr = "-";				
					$statusCode = 0;			
				}
	
				
				//echo "&nbsp;&nbsp;&nbsp;".$current_category."&nbsp;&nbsp;&nbsp;".date("Y-m-d H:i:s", $timestamp)."&nbsp;&nbsp;&nbsp;".date("Ymd", $timestamp)."&nbsp;&nbsp;&nbsp;{$vis_team_name} ({$vis_team_abbr}) {$vis_team_rank} ({$vis_team_record}) @ {$home_team_name} ({$home_team_abbr}) {$home_team_rank} ({$home_team_record})<br/>";
				$output .= "&nbsp;&nbsp;&nbsp;{$current_category}&nbsp;&nbsp;&nbsp;{$game_identifier}&nbsp;&nbsp;&nbsp;{$vis_team_name} ({$vis_team_abbr}) <b>{$vis_team_rank}</b> ({$vis_team_score}) @ {$home_team_name} ({$home_team_abbr}) <b>{$home_team_rank}</b> ({$home_team_score})<br/>";
				
				$games[] = array(
					"category"=>$current_category,
					"gameIdentifier"=>$game_identifier,
					"winner"=>$winner,
					"winnerAbbr"=>$winnerAbbr,
					"visTeam"=>$vis_team_name,
					"homeTeam"=>$home_team_name,
					"visTeamScore"=>$vis_team_score,
					"homeTeamScore"=>$home_team_score,
					"gameStatus"=>$game_status,
					"statusCode"=>$statusCode
					);
					
					
				
				$startpos = strpos($contents, "<div class=SLTables1>");
				//echo "&nbsp;&nbsp;&nbsp;{$timestamp}<br/>";
			}
		}
		
		   //$output .= "<table><tbody><tr><td>game</td><td>Visting Team</td><td>Vis Team Score</td><td>&nbsp;</td><td>Home Team</td><td>Home Team Score</td><td>Winner</td></tr>";
		   
		   
	      
		for ($i=0; $i<sizeof($games); $i++) {
			
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
	    
		$title = "NCAAF results for Week {$current_category}";
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);	    
	}
	
		
}

	
	



SpecialPage::addPage( new PickGameNCAAFWinners );



}

?>
