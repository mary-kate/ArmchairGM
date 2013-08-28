<?php
$wgExtensionFunctions[] = "wfNbaGames";

$no_nba_games = array(
"1001" => 1,
"1002" => 1,
"1224" => 1,
"1225" => 1,
"0125" => 1,
"0126" => 1,
"0127" => 1,
"0128" => 1
);

$nba_nicks = array(
"New Jersey" => "Devils",
"N.Y. Islanders" => " ",
"N.Y. Rangers" => " ",
"Philadelphia" => "Flyers",
"Pittsburgh" => "Penguins",
"Boston" => "Bruins",
"Buffalo" => "Sabres",
"Montreal" => "Canadiens",
"Ottawa" => "Senators",
"Toronto" => "Maple Leafs",
"Atlanta" => "Thrashers",
"Carolina" => "Hurricanes",
"Florida" => "Panthers",
"Tampa Bay" => "Lightning",
"Washington" => "Capitals",
"Chicago" => "Blackhawks",
"Columbus" => "Blue Jackets",
"Detroit" => "Red Wings",
"Nashville" => "Predators",
"St. Louis" => "Blues",
"Calgary" => "Flames",
"Colorado" => "Avalanche",
"Edmonton" => "Oilers",
"Minnesota" => "Wild",
"Vancouver" => "Canucks",
"Anaheim" => "Ducks",
"Dallas" => "Stars",
"Los Angeles" => "Kings",
"Phoenix" => "Coyotes",
"San Jose" => "Sharks"
);

function wfNbaGames() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "nbaGames", "getNbaGames" );
}

function getNbaGames($input, $args, $parser) {
	
 global $nba_nicks, $no_nba_games;

	    $oneDay = 60*60*24;
	    $curDateUnix = mktime ( 0, 0, 0, 4, 6, 2007);
	    $countDays=0;
	    $maxCountDays=0;
	    $output = "";
	    $dbOutput = "";
	    $sport_id = 4;
	    			$output .= "<table><tbody><tr><td>game</td><td>Game time</td><td>Visting Team</td><td>Visiting Team NickName</td><td>&nbsp;</td><td>Home Team</td><td>Home Team NickName</td><td>Game Identifier</td><td>Game Date</td></tr>";


	while($countDays <= $maxCountDays) {
	 
	 //$output = "";	    
		    $curGetDay = $curDateUnix + ($countDays * $oneDay);
		    $curDateCategory = date("Ymd", $curGetDay);
		    $curDate  = date("md", $curGetDay);
		    
		    if (!isset($no_nba_games[$curDate])) {

			//$output .= "<br/>date: " . $curDate . "<br/>";
			$dbOutput .= "<br/><br/>";
			
			//$handle = fopen("http://www.sportsline.com/mlb/scoreboard/" . $_GET["date"], "r");
			$handle = fopen("http://www.sportsline.com/nhl/schedules/day/" . $curDate, "r");
			$contents = stream_get_contents($handle);
			fclose($handle);
			$startpos = strpos($contents, "All times are US/Eastern");
			$contents = substr($contents, $startpos, strlen($contents)-$startpos);
			
			$startpos = strpos($contents, "<div class=SLTables1>");
			//$endpos = strpos($contents, "<!-- from content-end-rail-none.html -->");
//			$contents = substr($contents, $startpos, $endpos-$startpos);
			$contents = substr($contents, $startpos);
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
         $startpos = strpos($contents, ",") + 1;
         
         $endpos = strpos($contents, "))", $startpos);
         $gameTime = substr($contents, $startpos, $endpos - $startpos);
         //echo $startpos . " - " . $endpos . " - " . $gameTime . "<br/>";
         
         $contents = substr($contents, $endpos);
         
		


         $startpos = strpos($contents, "<a href=\"/nhl/teams/schedule/");

            //$endpos = strpos($contents, "</table>", $startpos);

            $startpos = $startpos + strlen("<a href='/nhl/teams/schedule/");
            $endpos = strpos($contents, "\"", $startpos);

            //get visiting team abbr from url
            $visTeamAbbr = substr($contents, $startpos, $endpos - $startpos);

            $contents = substr($contents, $endpos);

            $startpos = strpos($contents, ">") + strlen(">");
            $endpos = strpos($contents, "</a>");

            //get visiting team name from link
            $visTeamName = substr($contents, $startpos, $endpos-$startpos);

            $contents = substr($contents, $endpos);


            $startpos = strpos($contents, "<a href=\"/nhl/teams/schedule/") + strlen("<a href='/nhl/teams/schedule/");
            $endpos = strpos($contents, "\"", $startpos);

            //get home team abbr from url
            $homeTeamAbbr = substr($contents, $startpos, $endpos - $startpos);

            $contents = substr($contents, $endpos);

            $startpos = strpos($contents, ">") + strlen(">");
            $endpos = strpos($contents, "</a>");

            //get home team name from link
            $homeTeamName = substr($contents, $startpos, $endpos-$startpos);

            $contents = substr($contents, $endpos);

            
            $startpos = strpos($contents, "document.write(formatTime(");

        
            $gameIdentifier = "NHL_" . $curDateCategory . "_" . $visTeamAbbr . "@" . $homeTeamAbbr;
            if (!isset($gameIdentifiers[$gameIdentifier])) {
				$gameIdentifiers[$gameIdentifier] = 1;
			}
			else {
				$gameIdentifier = $gameIdentifier . "_2";
				$gameIdentifiers[$gameIdentifier] = 1;				
			}
			
            
            
            

        
            $games[] = array("gameTime"=>$gameTime, "visTeam" => $visTeamAbbr, "visTeamName" => $visTeamName, "visTeamAddl" => $nba_nicks[$visTeamName], "homeTeam" => $homeTeamAbbr, "homeTeamName" => $homeTeamName, "homeTeamAddl" => $nba_nicks[$homeTeamName],"gameIdentifier"=>$gameIdentifier, "game_date"=>$curDateCategory);
        
        
        }
        
        //$output .= "<table><tbody><tr><td>game</td><td>Game time</td><td>Visting Team</td><td>&nbsp;</td><td>Home Team</td><td>Game Identifier</td><td>Game Date</td></tr>";
        
        for ($i=0; $i<sizeof($games); $i++) {
        
            $output .= "<tr><td>" . $games[$i]["visTeam"] . " @ " . $games[$i]["homeTeam"] . "</td>";			
			$localOffset = date("Z");
	
			if (date("I", $curGetDay) == "0") {
				$eastOffset = -14400;				
			}
			else {
				$eastOffset = -18000;
			}
			
			//$theOffset = $localOffset - $eastOffset - ((60*60*3) + (60*17)) ;
		$theOffset = $localOffset - $eastOffset;
			
            $output .= "<td>" .  date("g:i A", $games[$i]["gameTime"]-$theOffset) . "</td>";
			//$output .= "<td>" .  date("g:i A", $games[$i]["gameTime"]) . "</td>";			
			
			
            $output .= "<td>" .  $games[$i]["visTeamName"] . "</td>";
            $output .= "<td>" .  $games[$i]["visTeamAddl"] . "</td>";
            $output .= "<td>&nbsp;</td>";
            $output .= "<td>" .  $games[$i]["homeTeamName"] . "</td>";
            $output .= "<td>" .  $games[$i]["homeTeamAddl"] . "</td>";
            $output .= "<td>" . $games[$i]["gameIdentifier"] . "</td>";
            $output .= "<td>" . $games[$i]["game_date"] . "</td>";
              
            $output .= "</tr>";
        
           do_nba_insert($games[$i]["gameTime"]-$theOffset, $games[$i]["homeTeamName"], $games[$i]["visTeamName"], $games[$i]["gameIdentifier"], $games[$i]["homeTeam"], $games[$i]["visTeam"], $games[$i]["homeTeamAddl"], $games[$i]["visTeamAddl"], $games[$i]["game_date"], $sport_id);
     
        }
        
        }
          $countDays++;
  
    
    }
    
            $output .= "</tbody></table>";
        
        return $output;

    
}


function do_nba_insert($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$game_date,$sport_id) {
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

function do_nba_update($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$game_date,$sport_id) {
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
