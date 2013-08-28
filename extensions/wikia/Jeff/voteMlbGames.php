<?php
$wgExtensionFunctions[] = "wfVoteMlbGames";

function wfVoteMlbGames() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "voteMlbGames", "displayGamesForVoting" );
}

function displayGamesForVoting($input, $args, $parser) {
	
	global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
	
	$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Jeff/voteMlbGames.css?{$wgStyleVersion}\"/>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Jeff/voteGame.js?{$wgStyleVersion}\"></script>\n");
	


	// define user_name and user_id
	$user_name = $wgRequest->getVal('user');
	
	//No UserName Then Assume Current User			
	if(!$user_name)$user_name = $wgUser->getName();
	$user_id = User::idFromName($user_name);

	// determine which sport we are voting on based on input in hook
	$sport_id = $input;
	
	// load sport_specific parameters based on sport_id
	$sport_specifics = pickgame_getSportSpecifics($sport_id);



	// define variables to hold the outputs to page
	// and total amounts calculated across the page 
	$output = "";
	$wager_js_output = "<script type=\"text/javascript\">var wager_amounts = new Array();";
	$wager_total = 0;
	$wager_won = 0;
	$wager_lost = 0;
	$wager_result = 0;
	
	
	// create variables to hold date related info
	//  |**** number of seconds in 1 day, today's date, and determine the date we are looking for
	//  |**** whether it is today or the date passed in from the querystring
	$oneDay = 60*60*24;	
	$today = date("Ymd");	

	if ($wgRequest->getVal($sport_specifics["param"]) == "") {
		
		$curDateUnix = time();
		
		//$curGetDay = $curDateUnix + $oneDay;
		$curGetDay = $curDateUnix;
		$curDate = date("Ymd", $curGetDay);
		$current_category = get_category_from_date($curDate, $sport_specifics);		
		
	}
	else {
		//$curDate = $wgRequest->getVal($sport_specific["param"]);
		$current_category = $wgRequest->getVal($sport_specifics["param"]);
		$curDate = get_date_from_category($current_category, $sport_specifics);
	}
	
	
	// call functions to load info for all games and load all votes for each game
	//$games = getGamesForVoting($curDate, $sport_id, $user_name);
	//$votes = pickgame_getVoteTallies($curDate, true);

	$games = getGamesForVoting($current_category, $sport_id, $user_name);
	$votes = pickgame_getVoteTallies($current_category, true);
	
	$first_game_date = (sizeof($games)>0) ?  date("Ymd", $games[0]["gameDate"]) : $curDate;

	// determine if the page that we are on is from before today
	// if so, call function to get the winners that have been entered into the db for that day
	
	//$pastDate = ($today > $curDate) ? true : false;
	$pastDate = ($today > $first_game_date) ? true : false;
	
	if($pastDate) {
		$winners = getDaysWinners($curDate, $sport_id);		
	}
	else {
		$winners = array();
	}
	
	// begin outputting div for date navigation
	//$output .= "<div id=\"pick-date-navigation\">";

	// setup date string for grabbing individual year, month, day
	// for driving date navigation at the top of the page
	$curDateString = strval($curDate);	
	$curEastTimeStamp= getCurrentEastTimestamp();
	
	
	$can_proceed = true;
	
	// check that the date is in an acceptable format (only checking length for now)
	if ($sport_specifics["nav_type"] == "date") {
		if (strlen($curDateString) != 8) {
			$can_proceed = false;
		}
	}
	
	if ($can_proceed) {
		
		$output .= get_nav_display($current_category, $sport_specifics);
		
	/*	
		// pull the year, month and day from the date string
		$year = substr($curDateString, 0, 4);
		$month = substr($curDateString, 4, 2);
		$day = substr($curDateString, 6, 2);
		
		// display date range from 2 days before to 2 days after and make all but current day links
		$curDateTime = mktime(0, 0, 0, $month, $day, $year);
		//$dateLinks = array();
		for ($i=-2; $i<3; $i++) {
			//$dateLinks[] = date("Ymd", $curDateTime + ($i*$oneDay));
			if ($i !== 0) {
				$output  .= "<a href=\"index.php?title=" . $wgRequest->getVal("title") . "&date=" . date("Ymd", $curDateTime + ($i*$oneDay)) . "\">" . date("m/d/Y", $curDateTime + ($i*$oneDay)) . "</a> ";
			}
			else {
				$output  .=  date("m/d/Y", $curDateTime + ($i*$oneDay)) . " ";
			}
		}
		
		// end outputting div for date navigation	
		$output .= "</div>";
		
		*/
		
		// begin outputting div for holding all picks
		$output .= "<div class=\"pick-container\">";
		
		
		// check if there are games this day...
		// if not, display a message saying so...
		// either way, instantiate the output variable for the games section
		if (sizeof($games) > 0) {
			$gamesOutput = "";
		}
		else {
			$gamesOutput = "There are no games today.  Don't worry, select another date.";
		}

		// |*********
		// Begin cycling through all games for the day for output
		// |*********
		
		for ($i=0; $i<sizeof($games); $i++) {
			
			// create arrays and variables for all items that affect the display of each game box
			// params for game data, for display particulars, for vote tallies, and for wager selection display
			$game_params = array();
			$display_params = array();
			$vote_params = array();
			$wager_params = array();
			$wager_params[0] = array();
			$wager_params[1] = array();
			$wager_params[2] = array();
			$wager_params[3] = array();
			$wager_counts = array();
			
			/* can change once other variable names change */
			// set all parameters for the game_params based on what is stored in the game array
			$game_params["game_id"] = $games[$i]["gameID"];
			$game_params["vis_team_abbr"] = $games[$i]["visTeamAbbr"];
			$game_params["vis_team_name"] = $games[$i]["visTeamName"];
			$game_params["vis_team_addl"] = $games[$i]["visTeamAddl"];
			$game_params["home_team_abbr"] = $games[$i]["homeTeamAbbr"];
			$game_params["home_team_name"] = $games[$i]["homeTeamName"];
			$game_params["home_team_addl"] = $games[$i]["homeTeamAddl"];
			$game_params["game_date"] = $games[$i]["gameDate"];
			
			// set a variable determining whether this game started
			// $games[$i]["gameDate"] is the unix timestamp representation of the eastern time that the game starts
			// curEastTimeStamp is the current time in eastern time
			$gameStarted = ($curEastTimeStamp >= $games[$i]["gameDate"]) ? true : false;
			
			// default all values to their default "off" position
			$display_params["div_class"] = "";
			$display_params["up_img_src"] = "images/common/up-arrow.gif";
			$display_params["up_img_onclick"] = "";
			$display_params["up_img_mouse"] = "";
			$display_params["up_img_style"] = "";
			$display_params["down_img_src"] = "images/common/down-arrow.gif";
			$display_params["down_img_onclick"] = "";
			$display_params["down_img_mouse"] = "";
			$display_params["down_img_style"] = "";
			$display_params["text_class"] = "";
			$display_params["wager_class"] = "-started";
			
			// set default values for which wager has been selected
			//(always starts at 0 before game is voted on and after vote before wager is changed)
			$selected_wager = 0;
			$selected_wager_level = 0;
			$selected_wager_levels = "0";
			
			//set default mouseover title for what is displayed on wager section before pick is made
			$wager_params["title"] = "title=\"You must make a pick before you can wager.\"";
			
			// for each of the 4 items in the wager selection list
			// default the display params and mouse/click params for  
			for ($wager_count=0; $wager_count<4; $wager_count++) {
				$wager_params[$wager_count]["mouse"] = "";
				$wager_params[$wager_count]["on_click"] = "";
				$wager_params[$wager_count]["text_class"] = "-started";
				
				// by default, each wager level is a 10 pt step up
				$wager_params[$wager_count]["wager_level"] = $wager_count * 10;
				$wager_counts[$wager_count] = $wager_count * 10;
				// append each wager count to the semi-colon delimted list of options
				if ($wager_count != 0) {
					$selected_wager_levels .= ";{$wager_params[$wager_count]["wager_level"]}";
				}
			}
			
			// set defaults for number of votes for each option and total votes
			$vis_votes = "";
			$home_votes = "";
			$total_votes = 0;
			
			// if there is a number of votes for either team in the votes array
			// set the variable for that team, and add it to the total
			$vis_votes = (isset($votes[$games[$i]["visTeamAbbr"] . "_" . $games[$i]["gameID"]])) ? $votes[$games[$i]["visTeamAbbr"] . "_" . $games[$i]["gameID"]] : 0;
			$total_votes += $vis_votes;
			$home_votes = (isset($votes[$games[$i]["homeTeamAbbr"] . "_" . $games[$i]["gameID"]])) ? $votes[$games[$i]["homeTeamAbbr"] . "_" . $games[$i]["gameID"]] : 0;
			$total_votes += $home_votes;
			
			// set the display params of votes for home, visitor and total, and append the word "votes"
			$vote_params["vis_votes"] = " ({$vis_votes} votes)";
			$vote_params["home_votes"] = " ({$home_votes} votes)";
			$vote_params["total_votes"] = " ({$total_votes} votes)";
	

			
			if (is_null($games[$i]["pick_choice"])) {
				
				$display_params["up_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["visTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$selected_wager_levels}', 'pick-display-{$games[$i]["gameID"]}')\"";
				$display_params["up_img_mouse"] = "onmouseover=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',1)\" onmouseout=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',0)\"";
				$display_params["down_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["homeTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$selected_wager_levels}', 'pick-display-{$games[$i]["gameID"]}')\"";
				$display_params["down_img_mouse"] = "onmouseover=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',1)\" onmouseout=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',0)\"";
				//$display_params["vis_votes"] = "";
				//$display_params["home_votes"] = "";
				//$display_params["total_votes"] = "";
				$vote_params["vis_votes"] = "";
				$vote_params["home_votes"] = "";
				$vote_params["total_votes"] = "";

				
			}
			else {
				
				$display_params["div_class"] = "-hovered";
				
				$display_params["wager_class"] = "";
				
				$selected_wager = $games[$i]["pick_wager"];
				$wager_total += $selected_wager;
				$wager_js_output .= "wager_amounts[{$games[$i]["gameID"]}]={$selected_wager};";
				
				if (!$gameStarted) {
					
				if($games[$i]["pick_wager_choices"] != "-") {
					$selected_wager_levels = $games[$i]["pick_wager_choices"];
					$wager_counts = split(";", $selected_wager_levels);
				}

					for ($wager_count=0; $wager_count<4; $wager_count++) {
						if($games[$i]["pick_wager_level"] != $wager_count) {
							$wager_params[$wager_count]["mouse"] = " onmouseover=\"doWagerHover('wager-level-{$games[$i]["gameID"]}-{$wager_count}')\" onmouseout=\"endWagerHover('wager-level-{$games[$i]["gameID"]}-{$wager_count}')\"";
							$wager_params[$wager_count]["on_click"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["pick_choice"]}', {$wager_counts[$wager_count]}, {$wager_count}, '{$selected_wager_levels}', 'pick-display-{$games[$i]["gameID"]}')\"";
							$wager_params[$wager_count]["wager_level"] = $wager_counts[$wager_count];
							$wager_params[$wager_count]["text_class"] = "";
						}
						else {
							$wager_params[$wager_count]["text_class"] = "-selected";
							$selected_wager = $games[$i]["pick_wager"];
							$wager_params[$wager_count]["wager_level"] = $wager_counts[$wager_count];
							$selected_wager_level = $wager_count;
						}
					}
				}
				
				
				$wager_params["title"] = "";

		
				if($games[$i]["pick_choice"] === $games[$i]["visTeamAbbr"]) {
					$display_params["up_img_src"]= "images/common/up-arrow-on.gif";
					$display_params["up_img_style"] = " style=\"cursor: default;\" ";
					$display_params["down_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["homeTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$selected_wager_levels}', 'pick-display-{$games[$i]["gameID"]}')\"";
					$display_params["down_img_mouse"] = "onmouseover=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',1)\" onmouseout=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',0)\"";

				}
				else {
					$display_params["up_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["visTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$selected_wager_levels}', 'pick-display-{$games[$i]["gameID"]}')\"";
					$display_params["up_img_mouse"] = "onmouseover=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',1)\" onmouseout=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',0)\"";
					$display_params["down_img_src"]= "images/common/down-arrow-on.gif";
					$display_params["down_img_style"] = " style=\"cursor: default;\" ";
				}
				
							

			}
			
			if($gameStarted) {
				$display_params["text_class"] = "-started";
				$display_params["up_img_onclick"] = "";
				$display_params["up_img_mouse"] = "";
				$display_params["down_img_style"] = "";
				$display_params["down_img_onclick"] = "";
				$display_params["down_img_mouse"] = "";
				$display_params["down_img_style"] = "";
				$display_params["wager_class"] = "-started";
				$wager_params["title"] = "";
				if (!is_null($games[$i]["pick_wager"])) {
					$wager_params["started_text"] = "You wagered {$games[$i]["pick_wager"]} points on this game.";
				}
				else {
					$wager_params["started_text"] = "You did not make a pick for this game.";
				}


			}
			
			if(!is_null($winners[$games[$i]["gameID"]]) && !is_null($games[$i]["pick_choice"])) {
				if ($winners[$games[$i]["gameID"]]["game_winner_abbr"] == $games[$i]["pick_choice"]) {
					$display_params["div_class"] = "-won";
					$wager_params["started_text"] = "You won {$games[$i]["pick_wager"]} points on this game.";
					$wager_won += $games[$i]["pick_wager"];
				}
				else {
					$display_params["div_class"] = "-lost";
					$wager_params["started_text"] = "You lost {$games[$i]["pick_wager"]} points on this game.";
					$wager_lost += $games[$i]["pick_wager"];
				}
				
			}
			
			$gamesOutput .= pickgame_displayGame($game_params, $vote_params, $display_params, $wager_params, $sport_specifics, $winners[$games[$i]["gameID"]], $gameStarted, $i, true);
			
			/*
			
			$gamesOutput .= "<div id=\"pick-display-{$games[$i]["gameID"]}\" class=\"pick-display{$div_class}\">
					<div class=\"pick-title{$text_class}\">
						{$games[$i]["visTeamName"]} @ {$games[$i]["homeTeamName"]} - ".date("g:i A", $games[$i]["gameDate"])."
					</div>
					<div class=\"pic-team-1{$text_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">
					<img src=\"{$up_img_src}\" id=\"up-arrow-{$games[$i]["gameID"]}\" {$up_img_style} {$up_img_mouse} {$up_img_onclick} />  
						{$games[$i]["visTeamAbbr"]} - {$games[$i]["visTeamAddl"]} <span id=\"vis-votes-{$games[$i]["gameID"]}\" class=\"pick-vote-span{$text_class}\">{$vis_votes}</span>
					</div>
					<div class=\"pic-team-2{$text_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">
						<img src=\"{$down_img_src}\" id=\"down-arrow-{$games[$i]["gameID"]}\" {$down_img_style} {$down_img_mouse} {$down_img_onclick} />
						{$games[$i]["homeTeamAbbr"]} - {$games[$i]["homeTeamAddl"]} <span id=\"home-votes-{$games[$i]["gameID"]}\" class=\"pick-vote-span{$text_class}\">{$home_votes}</span>
					</div>
				</div>";
				
			*/
		
		}
		
		$wager_text = "<div id=\"total-wagers-text\" class=\"total-wagers-text\">";
		$no_wager_text = "You have no wagers today...  Come on, live a little!";
		$wager_js_output .= "var no_wager_text=\"{$no_wager_text}\";";
		
		if ($wager_total > 0) {
			if ($wager_won > 0 || $wager_lost > 0) {
				$wager_result = $wager_won - $wager_lost;
				if ($wager_won > $wager_lost) {
					$wager_text .= "Congratulations! You won {$wager_result} points today.  (total wagers: {$wager_total}, won: {$wager_won}, lost: {$wager_lost})<br/>Don't quit while you are ahead, place some more wagers to increase your winnings!";
				}
				elseif ($wager_won < $wager_lost) {
					$wager_text .= "Bummer. You lost ". abs($wager_result) ." points today. (total wagers: {$wager_total}, won: {$wager_won}, lost: {$wager_lost})<br/>Keep trying! You can win these points back by trying again";
				}
				else {
					$wager_text .= "You broke even today. (total wagers: {$wager_total}, won: {$wager_won}, lost: {$wager_lost})<br/>Thats boring. Why don't you try again to see if you can actually win some points. ";
				}
			}
			else {
				$wager_text .= "Your wagers for this day total <span id=\"total-wagers\">{$wager_total}</span> points.";
			}
		}
		else {
			$wager_text .= $no_wager_text;
		}
		$wager_js_output .= "</script>";
		
		$wager_text .= "</div>";
		
		$output .= "{$wager_text}{$wager_js_output}{$gamesOutput}
		</div>";
	
	}
    
	return $output;

    
}

function pickgame_displayGame($game_params, $vote_params, $display_params, $wager_params, $sport_specifics, $results_params, $gameStarted, $i, $output_container) {
	
	$vis_score = "";
	$home_score = "";
	$game_status_desc = "";
	$is_result = false;
	$status_class = "";
	
	if (!is_null($results_params)) {
		$vis_score = $results_params["vis_score"];
		$home_score = $results_params["home_score"];
		$game_status_desc = $results_params["game_status_desc"];
		$is_result = true;
		$status_class = "-over";
	}
	
	
	$gamesOutput = "";

	if ($output_container) { 
		$gamesOutput .= "<div id=\"pick-display-{$game_params["game_id"]}\" class=\"pick-display{$display_params["div_class"]}\">";
	}
	
	$gamesOutput .=	"<div class=\"pick-title{$display_params["text_class"]}\">
				{$game_params["vis_team_name"]} @ {$game_params["home_team_name"]} - ".date($sport_specifics["game_date_display"], $game_params["game_date"])."
				</div>";
			
			if($is_result) {
				$gamesOutput .= "<div class=\"pick-status\">{$game_status_desc}</div>";
			}
				
			
			$gamesOutput .= "<div class=\"teams-container\">
			<div class=\"pic-team-1{$display_params["text_class"]}{$status_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">";
			if(!$is_result) {
				$gamesOutput .= "<img src=\"{$display_params["up_img_src"]}\" id=\"up-arrow-{$game_params["game_id"]}\" {$display_params["up_img_style"]} {$display_params["up_img_mouse"]} {$display_params["up_img_onclick"]} />
				{$game_params[$sport_specifics["vote_display_field_vis"]]}{$sport_specifics["additional_delimiter"]}{$game_params["vis_team_addl"]} <span id=\"vis-votes-{$game_params["game_id"]}\" class=\"pick-vote-span{$display_params["text_class"]}\">{$vote_params["vis_votes"]}</span>";
			}
			else {
				$gamesOutput .= "{$game_params[$sport_specifics["score_display_field_vis"]]}{$sport_specifics["additional_delimiter"]}{$vis_score} <span id=\"vis-votes-{$game_params["game_id"]}\" class=\"pick-vote-span{$display_params["text_class"]}\">{$vote_params["vis_votes"]}</span>";
			}
			$gamesOutput .= "</div>
			<div class=\"pic-team-2{$display_params["text_class"]}{$status_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">";
			if(!$is_result) {
				$gamesOutput .=	"<img src=\"{$display_params["down_img_src"]}\" id=\"down-arrow-{$game_params["game_id"]}\" {$display_params["down_img_style"]} {$display_params["down_img_mouse"]} {$display_params["down_img_onclick"]} />
				{$game_params[$sport_specifics["vote_display_field_home"]]}{$sport_specifics["additional_delimiter"]}{$game_params["home_team_addl"]} <span id=\"	-{$game_params["game_id"]}\" class=\"pick-vote-span{$display_params["text_class"]}\">{$vote_params["home_votes"]}</span>";
			}
			else {
				$gamesOutput .= "{$game_params[$sport_specifics["score_display_field_home"]]}{$sport_specifics["additional_delimiter"]}{$home_score} <span id=\"vis-votes-{$game_params["game_id"]}\" class=\"pick-vote-span{$display_params["text_class"]}\">{$vote_params["vis_votes"]}</span>";
			}
			
			$gamesOutput .= "</div>
			</div>
			<div id=\"pick-wager-{$game_params["game_id"]}\" class=\"pick-wager{$display_params["wager_class"]}\" {$wager_params["title"]}>";
			if (!$gameStarted) {
				$gamesOutput .= "wager 
					<div id=\"wager-level-{$game_params["game_id"]}-0\" class=\"pick-wager-level{$wager_params[0]["text_class"]}\" {$wager_params[0]["mouse"]} {$wager_params[0]["on_click"]}> {$wager_params[0]["wager_level"]}</div> <div class=\"pick-wager-spacer{$wager_params[0]["text_class"]}\"> | </div>
					<div id=\"wager-level-{$game_params["game_id"]}-1\" class=\"pick-wager-level{$wager_params[1]["text_class"]}\" {$wager_params[1]["mouse"]} {$wager_params[1]["on_click"]}>{$wager_params[1]["wager_level"]}</div> <div class=\"pick-wager-spacer{$wager_params[1]["text_class"]}\"> | </div>
					<div id=\"wager-level-{$game_params["game_id"]}-2\" class=\"pick-wager-level{$wager_params[2]["text_class"]}\" {$wager_params[2]["mouse"]} {$wager_params[2]["on_click"]}>{$wager_params[2]["wager_level"]}</div> <div class=\"pick-wager-spacer{$wager_params[2]["text_class"]}\"> | </div>
					<div id=\"wager-level-{$game_params["game_id"]}-3\" class=\"pick-wager-level{$wager_params[3]["text_class"]}\" {$wager_params[3]["mouse"]} {$wager_params[3]["on_click"]}>{$wager_params[3]["wager_level"]}</div>";
			}
			else {
				$gamesOutput .= "<div class=\"wager-started-text\">{$wager_params["started_text"]}</div>";
			}
			$gamesOutput .= "</div>
			<div class=\"cleared\"></div>";
			
	if ($output_container) {
		$gamesOutput .= "</div>";
	}
		
	return $gamesOutput;
				

}


function getCurrentEastTimestamp() {

	//$localOffset = date("Z");
	
	if (date("I") == "0") {
		$eastOffset = -14400;				
	}
	else {
		$eastOffset = -18000;
	}
	
	//$theDate = date("g:i A", gmdate("U") + $eastOffset);
	$theDate = gmdate("U") + $eastOffset;
	return $theDate;
	
	//$theOffset = $localOffset - $eastOffset;

}


function getDaysWinners($category, $sport_id) {

	//$sport_id = 1;


	$dbr =& wfGetDB( DB_MASTER );

	//$sql = "SELECT * FROM pick_games_results WHERE sport_id={$sport_id} AND game_identifier LIKE '%{$curDate}%' ORDER BY game_identifier ASC"; 	
	$sql = "SELECT pick_games_results.*, pick_games.pick_category FROM pick_games_results, pick_games WHERE pick_games_results.pick_game_id=pick_games.pick_game_id AND sport_id={$sport_id} AND pick_games.pick_category={$category} ORDER BY game_identifier ASC";
	
	$games = array();
	$res = $dbr->query($sql);
	while ($row = $dbr->fetchObject( $res ) ) {
		 $games[$row->pick_game_id] = array(
			 "game_id"=>$row->pick_game_id,
			 "home_score"=>$row->home_score,
			 "vis_score"=>$row->vis_score,
			 "game_winner"=>$row->game_winner,
			 "game_winner_abbr"=>$row->game_winner_abbr,
			 "game_status"=>$row->game_status,
			 "game_status_desc"=>$row->game_status_desc
			 );
	}
	
	return $games;

}


function getGamesForVoting($category, $sport_id, $user_name) {

	//$sport_id = 1;


	$dbr =& wfGetDB( DB_MASTER );
	/*
	$sql = "SELECT pick_games.pick_vis_abbr, pick_games.pick_home_abbr, 
			pick_games.pick_game_visitor, pick_games.pick_game_home,
			pick_games.pick_visitor_addl, pick_games.pick_home_addl, 
			pick_games.pick_game_id, UNIX_TIMESTAMP(pick_games.pick_game_date) as pick_game_timestamp, 
			pick_games_picks.pick_choice, pick_games_picks.pick_status, pick_games_picks.pick_wager,
			pick_games_picks.pick_wager_level, pick_games_picks.pick_wager_choices
		FROM pick_games LEFT OUTER JOIN 
			(SELECT pick_choice, pick_status, game_id, pick_wager, pick_wager_level, pick_wager_choices 
			FROM pick_games_picks 
			WHERE pick_games_picks.pick_username= '{$user_name}') 
			as pick_games_picks 
		ON pick_games.pick_game_id = pick_games_picks.game_id
		WHERE pick_games.pick_identifier LIKE '%{$curDate}%' AND pick_games.pick_sport_id = {$sport_id}
		ORDER BY pick_games.pick_game_date";
	*/
	
	$sql = "SELECT pick_games.pick_vis_abbr, pick_games.pick_home_abbr, 
			pick_games.pick_game_visitor, pick_games.pick_game_home,
			pick_games.pick_visitor_addl, pick_games.pick_home_addl, 
			pick_games.pick_game_id, UNIX_TIMESTAMP(pick_games.pick_game_date) as pick_game_timestamp,
			pick_games.pick_category, pick_games_picks.pick_choice, pick_games_picks.pick_status, pick_games_picks.pick_wager,
			pick_games_picks.pick_wager_level, pick_games_picks.pick_wager_choices
		FROM pick_games LEFT OUTER JOIN 
			(SELECT pick_choice, pick_status, game_id, pick_wager, pick_wager_level, pick_wager_choices 
			FROM pick_games_picks 
			WHERE pick_games_picks.pick_username= '{$user_name}') 
			as pick_games_picks 
		ON pick_games.pick_game_id = pick_games_picks.game_id
		WHERE pick_games.pick_category ={$category} AND pick_games.pick_sport_id = {$sport_id}
		ORDER BY pick_games.pick_game_date";

	$games = array();
	$res = $dbr->query($sql);
	while ($row = $dbr->fetchObject( $res ) ) {
		 $games[] = array(
			 "visTeamAbbr"=>$row->pick_vis_abbr,
			 "homeTeamAbbr"=>$row->pick_home_abbr,
			 "visTeamName"=>$row->pick_game_visitor,
			 "homeTeamName"=>$row->pick_game_home,
			 "visTeamAddl"=>$row->pick_visitor_addl,
			 "homeTeamAddl"=>$row->pick_home_addl,
			 "gameDate"=>$row->pick_game_timestamp,
			 "gameID"=>$row->pick_game_id,
			 "pick_choice"=>$row->pick_choice,
			 "pick_status"=>$row->pick_status,
			 "pick_wager"=>$row->pick_wager,
			 "pick_wager_level"=>$row->pick_wager_level,
			 "pick_wager_choices"=>$row->pick_wager_choices
			 );
	}
	
	return $games;

}
function pickgame_getVoteTallies($search_param, $get_all) {
	
	$dbr =& wfGetDB( DB_MASTER );
	
	if(!$get_all) {
		$sql = "SELECT concat(pick_choice, concat('_', game_id)) as pick_choice, count(pick_choice) as theCount, game_id FROM `armchairgm`.`pick_games_picks` where game_id={$search_param} group by pick_choice";
	}
	else {
		//$sql = "SELECT concat(pick_choice, concat('_', game_id)) as pick_choice, count(pick_choice) as theCount, game_id FROM `armchairgm`.`pick_games_picks` where game_id in (select pick_game_id FROM `armchairgm`.`pick_games` where pick_identifier LIKE '%{$search_param}%') group by pick_choice";
		$sql = "SELECT concat(pick_choice, concat('_', game_id)) as pick_choice, count(pick_choice) as theCount, game_id FROM `armchairgm`.`pick_games_picks` where game_id in (select pick_game_id FROM `armchairgm`.`pick_games` where pick_category={$search_param}) group by pick_choice";
	}
	
	$votes = array();
	$res = $dbr->query($sql);
	while ($row = $dbr->fetchObject( $res ) ) {
		 $votes[$row->pick_choice] = $row->theCount;
	}
	
	return $votes;
	
	
}



function pickgame_getSportSpecifics($sport_id) {

	$sport_specifics = array();
	switch ($sport_id) {
		case 1:
		    $sport_specifics["nav_type"] = "date";
		    $sport_specifics["param"] = "date";
		    $sport_specifics["nav_prefix"] = "";
		    $sport_specifics["nav_suffix"] = "";
		    $sport_specifics["score_display_field_vis"] = "vis_team_name";
		    $sport_specifics["score_display_field_home"] = "home_team_name";
		    $sport_specifics["vote_display_field_vis"] = "vis_team_abbr";
		    $sport_specifics["vote_display_field_home"] = "home_team_abbr";
		    $sport_specifics["additional_delimiter"] = " - ";
		    $sport_specifics["game_date_display"] = "g:i A";
		    break;
		case 2:
		    $sport_specifics["nav_type"] = "category";
		    $sport_specifics["param"] = "week";
		    $sport_specifics["nav_prefix"] = "Week ";
		    $sport_specifics["nav_suffix"] = "";
		    $sport_specifics["score_display_field_vis"] = "vis_team_name";
		    $sport_specifics["score_display_field_home"] = "home_team_name";
		    $sport_specifics["vote_display_field_vis"] = "vis_team_name";
		    $sport_specifics["vote_display_field_home"] = "home_team_name";
		    $sport_specifics["additional_delimiter"] = " ";
		    $sport_specifics["game_date_display"] = "D, M j - g:i A";
		    $sport_specifics["date_range"] = array(
		    	1 => 20070911,
			2 => 20070918,
			3 => 20070925,
			4 => 20071002,
			5 => 20071009,
			6 => 20071016,
			7 => 20071023,
			8 => 20071030,
		    	9 => 2007106,
			10 => 20071113,
			11 => 20071120,
			12 => 20071127,
			13 => 20071204,
			14 => 20071211,
			15 => 20071218,
			16 => 20071225,
			17 => 20080911
			);
		    break;
		default:
		    $sport_specifics["nav_type"] = "date";
		    $sport_specifics["param"] = "date";
		    $sport_specifics["nav_prefix"] = "";
		    $sport_specifics["nav_suffix"] = "";
		    $sport_specifics["score_display_field_vis"] = "vis_team_name";
		    $sport_specifics["score_display_field_home"] = "home_team_name";
		    $sport_specifics["vote_display_field_vis"] = "vis_team_abbr";
		    $sport_specifics["vote_display_field_home"] = "home_team_abbr";
		    $sport_specifics["additional_delimiter"] = " - ";
		    $sport_specifics["game_date_display"] = "g:i A";
		    break;
	}
	
	return $sport_specifics;

}

function get_category_from_date($cur_date, $sport_specific) {
	
	if (!is_null($sport_specific["date_range"])) {
		foreach($sport_specific["date_range"] as $key => $value) {
			if ($curDate < $value) {
				return $key;
			}
		}
	}
	else {
		return $cur_date;
	}
}

function get_date_from_category($category, $sport_specifics) {
	
	if (!is_null($sport_specifics["date_range"])) {
		if (isset($sport_specifics["date_range"][$category])) {
			return $sport_specifics["date_range"][$category];		
		}
	}
	
	return $category;
}

function get_nav_display($current, $sport_specifics) {
	
	global $wgRequest;
	$oneDay = 60*60*24;
	
	// begin outputting div for date navigation
	$output = "<div id=\"pick-date-navigation\" class=\"pick-date-navigation\">";

	
	if($sport_specifics["nav_type"] == "category") {
			
		$output .= "<div class=\"pick-navigation-month\">Games for {$sport_specifics["nav_prefix"]}{$current}{$sport_specifics["nav_suffix"]}</div>";
		
		for ($i=-2; $i<3; $i++) {
			$cur_category = $current + $i;
			if ($i !== 0) {
				if ($cur_category > 0 && isset($sport_specifics["date_range"][$cur_category])) {
					//$output  .= "<a href=\"index.php?title=" . $wgRequest->getVal("title") . "&{$sport_specifics["param"]}={$cur_category}\">{$sport_specifics["nav_prefix"]}{$cur_category}{$sport_specifics["nav_suffix"]}</a> ";
					$output  .= "<div class=\"pick-navigation-day-link\"><div style=\"padding: 10px 0px 10px 0px\"><a href=\"index.php?title=" . $wgRequest->getVal("title") . "&{$sport_specifics["param"]}={$cur_category}\">{$sport_specifics["nav_prefix"]}<br/>{$cur_category}{$sport_specifics["nav_suffix"]}</a></div></div>";
				}
				else {
					$output  .=  "<div class=\"pick-navigation-day-current\" style=\"background-color: #F2F4F7\"></div>";

				}
			}
			else {
				//$output  .=  "{$sport_specifics["nav_prefix"]}{$cur_category}{$sport_specifics["nav_suffix"]}";
				$output  .=  "<div class=\"pick-navigation-day-current\"><div style=\"padding: 10px 0px 10px 0px\">{$sport_specifics["nav_prefix"]}<br/>{$cur_category}{$sport_specifics["nav_suffix"]}</div></div>";
				
			}
		}
		
		$output .="<div style=\"clear: left\"></div>";
		
	}
	else {

		
		// pull the year, month and day from the date string
		$year = substr($current, 0, 4);
		$month = substr($current, 4, 2);
		$day = substr($current, 6, 2);
		
		// display date range from 2 days before to 2 days after and make all but current day links
		$curDateTime = mktime(0, 0, 0, $month, $day, $year);
		
		$output .= "<div class=\"pick-navigation-month\">Games for " . date("l, F j", $curDateTime) . "</div>";
		
		//$dateLinks = array();
		for ($i=-2; $i<3; $i++) {
			//$dateLinks[] = date("Ymd", $curDateTime + ($i*$oneDay));
			if ($i !== 0) {
				//$output  .= "<a href=\"index.php?title=" . $wgRequest->getVal("title") . "&date=" . date("Ymd", $curDateTime + ($i*$oneDay)) . "\">" . date("m/d/Y", $curDateTime + ($i*$oneDay)) . "</a> ";
				//$output  .= "<a href=\"index.php?title=" . $wgRequest->getVal("title") . "&{$sport_specifics["param"]}=" . date("Ymd", $curDateTime + ($i*$oneDay)) . "\">{$sport_specifics["nav_prefix"]}" . date("m/d/Y", $curDateTime + ($i*$oneDay)) . "{$sport_specifics["nav_suffix"]}</a> ";
				$output  .= "<div class=\"pick-navigation-day-link\"><div style=\"padding: 0px 0px 15px 0px\">" . date("D", $curDateTime + ($i*$oneDay)) . "</div><span><a href=\"index.php?title=" . $wgRequest->getVal("title") . "&{$sport_specifics["param"]}=" . date("Ymd", $curDateTime + ($i*$oneDay)) . "\">{$sport_specifics["nav_prefix"]}" . date("m/d", $curDateTime + ($i*$oneDay)) . "{$sport_specifics["nav_suffix"]}</a></span></div>";
			}
			else {
				//$output  .=  date("m/d/Y", $curDateTime + ($i*$oneDay)) . " ";
				$output  .= "<div class=\"pick-navigation-day-current\"><div style=\"padding: 0px 0px 15px 0px\">" . date("D", $curDateTime + ($i*$oneDay)) . "</div><span>{$sport_specifics["nav_prefix"]}" . date("m/d", $curDateTime + ($i*$oneDay)) . "{$sport_specifics["nav_suffix"]}</span></div>";

			}
		}
		
		$output .="<div style=\"clear: left\"></div>";
	}
	
		
		// end outputting div for date navigation	
	$output .= "</div>";
	
	return $output;
}




?>
