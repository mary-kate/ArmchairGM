<?php

$wgExtensionFunctions[] = 'wfSpecialPickGameGetNCAAF';

function wfSpecialPickGameGetNCAAF(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PickGameGetNCAAF extends SpecialPage {

	
	function PickGameGetNCAAF(){
		UnlistedSpecialPage::UnlistedSpecialPage("PickGameGetNCAAF");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;

			$sport_id=6;
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
		$handle = fopen("http://www.sportsline.com/collegefootball/scoreboard/top25/2007/week" . $current_category, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		
		
		$date_startpos = strpos($contents, "<td class=\"date\">");
		$endpos = strpos($contents, "<!-- from content-end-rail-none.html -->");
		$contents = substr($contents, $date_startpos, $endpos-$date_startpos);

		$days_games = array();
		$games = array();
		//$games_identifiers = array();
		
		$output = "";
		$dboutput = "";
		
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
				$startpos = strpos($contents, "document.write(formatTime(");
				$time_start = strpos($contents, ", ", $startpos) + strlen(", ");
				$endpos =  strpos($contents, "))") - $time_start;
				$timestamp = substr($contents, $time_start, $endpos);
				$contents = substr($contents, $endpos);
				
				$vis_team_row_start = strpos($contents, "<tr  height=17  class=bg2");
				$contents = substr($contents, $vis_team_row_start);
				$vis_team_row_end = strpos($contents, "</tr>") + strlen("</tr>");
				$vis_team_row = substr($contents, 0, $vis_team_row_end);
				$contents = substr($contents, $vis_team_row_end);

				$home_team_row_start = strpos($contents, "<tr  height=17  class=bg2");
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
				$vtrec_start = strpos($vis_team_row, "<td  class=scorerow align=center>") + strlen("<td  class=scorerow align=center>");
				$vtrec_end = strpos($vis_team_row, "</td>", $vtrec_start);
				$vis_team_record = substr($vis_team_row, $vtrec_start, $vtrec_end-$vtrec_start);
				$vis_team_row = substr($vis_team_row, $vtrec_end + strlen("</td>"));
	
				
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
				$htrec_start = strpos($home_team_row, "<td  class=scorerow align=center>") + strlen("<td  class=scorerow align=center>");
				$htrec_end = strpos($home_team_row, "</td>", $htrec_start);
				$home_team_record = substr($home_team_row, $htrec_start, $htrec_end-$htrec_start);
				$home_team_row = substr($home_team_row, $htrec_end + strlen("</td>"));
				
				$vis_team_addl = "<b>{$vis_team_rank}</b> ({$vis_team_record})";
				$home_team_addl = "<b>{$home_team_rank}</b> ({$home_team_record})";
				
				$game_date = date("Y-m-d H:i:s", $timestamp-$theOffset);
				$identifier_date = date("Ymd", $timestamp-$theOffset);
				$game_identifier = "NCAAF_{$identifier_date}_{$vis_team_abbr}@{$home_team_abbr}";
				
				//echo "&nbsp;&nbsp;&nbsp;".$current_category."&nbsp;&nbsp;&nbsp;".date("Y-m-d H:i:s", $timestamp)."&nbsp;&nbsp;&nbsp;".date("Ymd", $timestamp)."&nbsp;&nbsp;&nbsp;{$vis_team_name} ({$vis_team_abbr}) {$vis_team_rank} ({$vis_team_record}) @ {$home_team_name} ({$home_team_abbr}) {$home_team_rank} ({$home_team_record})<br/>";
				$output .= "&nbsp;&nbsp;&nbsp;".$current_category."&nbsp;&nbsp;&nbsp;{$game_date}&nbsp;&nbsp;&nbsp;{$game_identifier}&nbsp;&nbsp;&nbsp;{$vis_team_name} ({$vis_team_abbr}) {$vis_team_addl} @ {$home_team_name} ({$home_team_abbr}) {$home_team_addl}<br/>";
				
				$games[] = array(
					"game_date"=>$current_category,
					"gameTime"=>$game_date,
					"gameIdentifier"=>$game_identifier,
					"visTeamName"=>$vis_team_name,
					"visTeam"=>$vis_team_abbr,
					"visTeamAddl"=>$vis_team_addl,
					"homeTeamName"=>$home_team_name,
					"homeTeam"=>$home_team_abbr,
					"homeTeamAddl"=>$home_team_addl,
					"gameTime"=>$timestamp
					);
					
					
				
				$startpos = strpos($contents, "<div class=SLTables1>");
				//echo "&nbsp;&nbsp;&nbsp;{$timestamp}<br/>";
			}
		}
		

			for ($i=0; $i<sizeof($games); $i++) {
			
			    if (!PickGameGetMLB::MLBgame_exists($games[$i]["gameIdentifier"])) {
				PickGameGetMLB::do_MLBgame_insert($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $games[$i]["homeTeamAddl"], $games[$i]["visTeamAddl"], $games[$i]["game_date"], 6);
				$dbOutput .= "Inserted " . $games[$i]["gameIdentifier"] . "<br/>";
				    
			    }
			    else {
				    PickGameGetMLB::do_MLBgame_update($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $games[$i]["homeTeamAddl"], $games[$i]["visTeamAddl"], $games[$i]["game_date"], 6);
				    $dbOutput .= "Updated " . $games[$i]["gameIdentifier"] . "<br/>";		    
			    }
			    
			}
		
			$title = "Get NCAAF games";
		
			$wgOut->setPageTitle($title);
			$wgOut->addHTML($dbOutput . "<br/><br/>" . $output);

		
		
	}
	
		
}

	
	



SpecialPage::addPage( new PickGameGetNCAAF );



}

?>
