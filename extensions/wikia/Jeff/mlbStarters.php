<?php
$wgExtensionFunctions[] = "wfMlbStarters";

function wfMlbStarters() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "mlbStarters", "getStarters" );
}

function getStarters($input, $args, $parser) {
	

    //if (isset($_GET["date"])) {
	    
	    $oneDay = 60*60*24;
	    $curDateUnix = time();
	    $countDays=1;
	    $maxCountDays=7;
	    $output = "";
	    $dbOutput = "";

	while($countDays <= $maxCountDays) {	    
		    $curGetDay = $curDateUnix + ($countDays * $oneDay);
		    $curDate = date("Ymd", $curGetDay);

	$output .= "<br/>date: " . $curDate . "<br/>";
	$dbOutput .= "<br/><br/>";

        //$handle = fopen("http://www.sportsline.com/mlb/scoreboard/" . $_GET["date"], "r");
	$handle = fopen("http://www.sportsline.com/mlb/scoreboard/" . $curDate, "r");
        $contents = stream_get_contents($handle);
        fclose($handle);
        $startpos = strpos($contents, "All times are US/Eastern");
        $contents = substr($contents, $startpos, strlen($contents)-$startpos);
        
        $startpos = strpos($contents, "<div class=SLTables1>");
        $endpos = strpos($contents, "<!-- from content-end-rail-none.html -->");
        $contents = substr($contents, $startpos, $endpos-$startpos);
        
        //$startpos = strpos($contents, "<td id=plays");
        //$startpos = strpos($contents, "<a href=\"/mlb/teams/page/");
        $startpos = strpos($contents, "document.write(formatTime(");

        //echo strlen($contents) . "<br/>";
        //echo $contents . "<br/>";
        $games = array();
        $gameIdentifiers = array();
        
        
        
        
        while ($startpos > 0) {
         
         $contents = substr($contents, $startpos);
         
         //$startpos = 37;
         $startpos = strpos($contents, ",") + 2;
         
         $endpos = strpos($contents, "))", $startpos);
         $gameTime = substr($contents, $startpos, $endpos - $startpos);
         //echo $startpos . " - " . $endpos . " - " . $gameTime . "<br/>";
         
         $contents = substr($contents, $endpos);
         
		


         $startpos = strpos($contents, "<a href=\"/mlb/teams/page/");

            //$endpos = strpos($contents, "</table>", $startpos);

            $startpos = $startpos + strlen("<a href='/mlb/teams/page/");
            $endpos = strpos($contents, "\"", $startpos);

            //get visiting team abbr from url
            $visTeamAbbr = substr($contents, $startpos, $endpos - $startpos);

            $contents = substr($contents, $endpos);

            $startpos = strpos($contents, "<b>") + strlen("<b>");
            $endpos = strpos($contents, "</b>");

            //get visiting team name from link
            $visTeamName = substr($contents, $startpos, $endpos-$startpos);

            $contents = substr($contents, $endpos);


            $startpos = strpos($contents, "<a href=\"/mlb/teams/page/") + strlen("<a href='/mlb/teams/page/");
            $endpos = strpos($contents, "\"", $startpos);

            //get home team abbr from url
            $homeTeamAbbr = substr($contents, $startpos, $endpos - $startpos);

            $contents = substr($contents, $endpos);

            $startpos = strpos($contents, "<b>") + strlen("<b>");
            $endpos = strpos($contents, "</b>");

            //get home team name from link
            $homeTeamName = substr($contents, $startpos, $endpos-$startpos);

            $contents = substr($contents, $endpos);

            
            
            $startpos = strpos($contents, "<td id=plays");
            //$contents = substr($contents, $startpos);



        
            $endpos = strpos($contents, "</td>", $startpos) + strlen("</td>");
            $chunk = substr($contents, $startpos, $endpos-$startpos);
            //echo strlen($chunk) . "<br/>";
        
            $contents = substr($contents, $endpos);
        
            $starters = substr($chunk, strpos($chunk, "<B>"), strpos($chunk, "</div>") - strpos($chunk, "<B>"));

            //echo $chunk . "<br/>";
        
        
            //get visiting team
            $visTeam = substr($starters, strlen("<B>"), strpos($starters, ":") - strlen("<B>"));

            $visTeam = checkLASwitch($visTeam);
            
            //set working string to after visiting team
            $starters = substr($starters, strpos($starters,"</B> ") + strlen("</B> "));
        
            //get visiting pitcher
            $visPitcher = substr($starters, 0, strpos($starters, "(") - 1);

            if ($visPitcher=="") {
                $visPitcher = "TBD";
            }
            
            //set working string to after visiting pitcher
            $starters = substr($starters, strpos($starters,"(") + strlen("("));
        
            //get visiting pitcher wins
            $visPitcherWins = substr($starters, 0, strpos($starters, "-"));

            if ($visPitcherWins=="") {
                $visPitcherWins = "-";
            }

            
            //set working string to after visiting pitcher wins
            $starters = substr($starters, strpos($starters,"-") + strlen("-"));
        
            //get visiting pitcher losses
            $visPitcherLosses = substr($starters, 0, strpos($starters, ","));
            
            if ($visPitcherLosses=="") {
                $visPitcherLosses = "-";
            }

            //set working string to after visiting pitcher losses
            $starters = substr($starters, strpos($starters,", ") + strlen(", "));
        
            //get visiting pitcher era
            $visPitcherEra = substr($starters, 0, strpos($starters, ")"));

            if ($visPitcherEra=="") {
                $visPitcherEra = "-";
            }

            
            //set working string to after visiting pitcher era
            $starters = substr($starters, strpos($starters,")") + strlen(")"));
        
            //-----
        
            $starters = substr($starters, strpos($starters, "<B>"));
        
            //-----
        
            //get home team
            $homeTeam = substr($starters, strlen("<B>"), strpos($starters, ":") - strlen("<B>"));
            $homeTeam = checkLASwitch($homeTeam);
            
            //set working string to after home team
            $starters = substr($starters, strpos($starters,"</B> ") + strlen("</B> "));
        
            //get home pitcher
            $homePitcher = substr($starters, 0, strpos($starters, "(") - 1);
            if ($homePitcher=="") {
                $homePitcher = "TBD";
            }

            
            //set working string to after home pitcher
            $starters = substr($starters, strpos($starters,"(") + strlen("("));
        
            //get home pitcher wins
            $homePitcherWins = substr($starters, 0, strpos($starters, "-"));

            if ($homePitcherWins=="") {
                $homePitcherWins = "-";
            }

            
            //set working string to after home pitcher wins
            $starters = substr($starters, strpos($starters,"-") + strlen("-"));
        
            //get home pitcher losses
            $homePitcherLosses = substr($starters, 0, strpos($starters, ","));

            if ($homePitcherLosses=="") {
                $homePitcherLosses = "-";
            }

            
            //set working string to after home pitcher losses
            $starters = substr($starters, strpos($starters,", ") + strlen(", "));
        
            //get home pitcher era
            $homePitcherEra = substr($starters, 0, strpos($starters, ")"));

            if ($homePitcherEra=="") {
                $homePitcherEra = "-";
            }

            
            //set working string to after home pitcher era
            $starters = substr($starters, strpos($starters,")") + strlen(")"));
        
        
        
        
            //echo $starters . "<br/><br/>";
        
            //$startpos = strpos($contents, "<td id=plays");
            //$startpos = strpos($contents, "<a href=\"/mlb/teams/page/");
            
            $startpos = strpos($contents, "document.write(formatTime(");

        
            //echo strlen($contents) . " - " . $startpos . "<br/><br/>";
        
            //echo $visTeam . "-" . $visPitcher . "-" . $visPitcherWins . "-" . $visPitcherLosses . "-" . $visPitcherEra . "<br/>";
            //echo $homeTeam . "-" . $homePitcher . "-" . $homePitcherWins . "-" . $homePitcherLosses . "-" . $homePitcherEra . "<br/>";
            //echo "<br/>";

            if ($visTeam != $visTeamAbbr) {
                $homePitcher = $visPitcher;
                $homePitcherWins = $visPitcherWins;
                $homePitcherLosses = $visPitcherLosses;
                $homePitcherEra = $visPitcherEra;
                $visPitcher = "TBD";
                $visPitcherWins = "-";
                $visPitcherLosses = "-"; 
                $visPitcherEra = "-";
                
            }
            
            $gameIdentifier = "MLB_" . $curDate . "_" . $visTeamAbbr . "@" . $homeTeamAbbr;
            if (!isset($gameIdentifiers[$gameIdentifier])) {
				$gameIdentifiers[$gameIdentifier] = 1;
			}
			else {
				$gameIdentifier = $gameIdentifier . "_2";
				$gameIdentifiers[$gameIdentifier] = 1;				
			}
			
            
            
            

        
            $games[sizeof($games)] = array("gameTime"=>$gameTime, "visTeam" => $visTeamAbbr, "visTeamName" => $visTeamName, "visPitcher"=> $visPitcher, "visPitcherWins"=>$visPitcherWins, "visPitcherLosses"=>$visPitcherLosses, "visPitcherEra"=>$visPitcherEra, "homeTeam" => $homeTeamAbbr, "homeTeamName" => $homeTeamName,  "homePitcher"=> $homePitcher, "homePitcherWins"=>$homePitcherWins, "homePitcherLosses"=>$homePitcherLosses, "homePitcherEra"=>$homePitcherEra, "gameIdentifier"=>$gameIdentifier, "game_date"=>$curDate);
        
        
        }
        
        $output .= "<table><tbody><tr><td>game</td><td>Game time</td><td>Visting Team</td><td>vis. Starter</td><td>wins</td><td>Losses</td><td>era</td><td>&nbsp;</td><td>Home Team</td><td>Home. Starter</td><td>wins</td><td>Losses</td><td>era</td><td>Game Identifier</td><td>date</td></tr>";
        
        for ($i=0; $i<sizeof($games); $i++) {
        
            $output .= "<tr><td>" . $games[$i]["visTeam"] . " @ " . $games[$i]["homeTeam"] . "</td>";
		
			$localOffset = date("Z");
	
		if (date("I") == "0") {
			$eastOffset = -14400;				
		}
		else {
			$eastOffset = -18000;
		}
	
			
			$theOffset = $localOffset - $eastOffset;
	    $output .= "<td>" .  date("g:i A", $games[$i]["gameTime"]-$theOffset) . "</td>";
            $output .= "<td>" .  $games[$i]["visTeamName"] . "</td>";
            $output .= "<td>" .  $games[$i]["visPitcher"] . "</td>";
            $output .= "<td>" . $games[$i]["visPitcherWins"] . "</td>";
            $output .= "<td>" . $games[$i]["visPitcherLosses"] . "</td>";
            $output .= "<td>" . $games[$i]["visPitcherEra"] . "</td>";
            $output .= "<td>&nbsp;</td>";
            $output .= "<td>" .  $games[$i]["homeTeamName"] . "</td>";
            $output .= "<td>" .  $games[$i]["homePitcher"] . "</td>";
            $output .= "<td>" . $games[$i]["homePitcherWins"] . "</td>";
            $output .= "<td>" . $games[$i]["homePitcherLosses"] . "</td>";
            $output .= "<td>" . $games[$i]["homePitcherEra"] . "</td>";
            $output .= "<td>" . $games[$i]["gameIdentifier"] . "</td>";
             $output .= "<td>" . $games[$i]["game_date"] . "</td>";
              
            $output .= "</tr>";
	    
	    $home_addl = $games[$i]["homePitcher"] . " (" . $games[$i]["homePitcherWins"] . "-" . $games[$i]["homePitcherLosses"] . ") " . $games[$i]["homePitcherEra"];
	    $vis_addl = $games[$i]["visPitcher"] . " (" . $games[$i]["visPitcherWins"] . "-" . $games[$i]["visPitcherLosses"] . ") " . $games[$i]["visPitcherEra"];
	    
	    /*	    
	    try {
		    do_game_insert($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $home_addl, $vis_addl, 1);
		    $dbOutput .= "Inserted " . $games[$i]["gameIdentifier"] . "<br/>";
	    }
	    catch (Exception $e) {
		    $dbOutput .= $e->getMessage() . "<br/>";
		    do_game_update($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $home_addl, $vis_addl, 1);
		    $dbOutput .= "Updated " . $games[$i]["gameIdentifier"] . "<br/>";
	    }
	    */
	    
	    
	    if (!game_exists($games[$i]["gameIdentifier"])) {
		do_game_insert($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $home_addl, $vis_addl, $games[$i]["game_date"], 1);
		$dbOutput .= "Inserted " . $games[$i]["gameIdentifier"] . "<br/>";
		    
	    }
	    else {
		    do_game_update($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $home_addl, $vis_addl, $games[$i]["game_date"], 1);
		    $dbOutput .= "Updated " . $games[$i]["gameIdentifier"] . "<br/>";		    
	    }
	    
        
        
        }
        
        $output .= "</tbody></table>";
    
    
    
    //}
    	$countDays = $countDays + 1;
	}
 	return $dbOutput . "<br/><br/>" . $output;
  
}

function checkLASwitch($abbr) {
    if ($abbr == "LAD") {
        return "LA";
    }
    elseif ($abbr == "LAA") {
            return "ANA";
    }
    else {
        return $abbr;
    }
}

function game_exists($pick_identifier) {

	$dbr =& wfGetDB( DB_MASTER );
	$s = $dbr->selectRow( 'pick_games', array( 'pick_identifier' ), array( 'pick_identifier' => $pick_identifier ),"" );
	if ( $s !== false ) {
		return true;
	} else {
		return false;
	}

}

function do_game_insert($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$game_date,$sport_id) {
	global $wgUser;
	$dbr =& wfGetDB( DB_MASTER );
	$dbr->insert( '`pick_games`',
	array(
		'pick_sport_id' => $sport_id,
		'pick_game_date' => date("Y-m-d H:i:s", $date),
		'pick_game_visitor' => $vis,
		'pick_game_home' => $home,
		'pick_visitor_addl' => $vis_addl,
		'pick_home_addl' => $home_addl,
		'pick_vis_abbr' => $vis_abbr,
		'pick_home_abbr' => $home_abbr,
		'pick_identifier' => $identifier,
		'pick_category' => $game_date
		), __METHOD__
	);	
	return $dbr->insertId();
}

function do_game_update($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$game_date,$sport_id) {
	global $wgUser;
	$dbr =& wfGetDB( DB_MASTER );
	$dbr->update( '`pick_games`',
	array(
		'pick_sport_id' => $sport_id,
		'pick_game_date' => date("Y-m-d H:i:s", $date),
		'pick_game_visitor' => $vis,
		'pick_game_home' => $home,
		'pick_visitor_addl' => $vis_addl,
		'pick_home_addl' => $home_addl,
		'pick_vis_abbr' => $vis_abbr,
		'pick_home_abbr' => $home_abbr,
		'pick_identifier' => $identifier,
		'pick_category' => $game_date
		),
	array(
		'pick_identifier' => $identifier
	)
		, __METHOD__
	);	
	//return $dbr->insertId();
}

/*
function returnTheDate() {
        
    if (isset($_GET["date"])) {
            echo $_GET["date"];
    }

}
*/

?>
