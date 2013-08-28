<?php
/*
 * Ajax Functions used by Pick Game
 */
 

$wgAjaxExportList [] = 'wfMakePick';
function wfMakePick($user_id, $game_id, $pick_choice, $wager_value, $wager_level, $wager_choices, $user_name) { 
	global $IP, $wgUser, $wgOut;
	
	$pick_date = date("Y-m-d H:i:s");
	
	
	//get database
	$dbr =& wfGetDB( DB_MASTER );
	
	
	//check if pick is new or changing
	
	if (!pick_exists($user_name, $game_id)) {
		//write new data to pick_games_pick
		$dbr->insert( '`pick_games_picks`',
		array(
			'pick_user_id' => $user_id,
			'game_id' => $game_id,
			'pick_choice' => $pick_choice,
			'pick_date' => $pick_date,
			'pick_status' => 0,
			'pick_correct' => 0,
			'pick_username' => $user_name,
			'pick_wager_choices' => $wager_choices
			), __METHOD__
		);
	}
	else {
		$dbr->update( '`pick_games_picks`',
		array(
			'pick_choice' => $pick_choice,
			'pick_date' => $pick_date,
			'pick_wager' => $wager_value,
			'pick_wager_level' => $wager_level,
			'pick_wager_choices' => $wager_choices
			),
		array(
			'pick_username' => $user_name,
			'game_id' => $game_id
		)
			, __METHOD__
		);
	}
	

	
	$games = getSingleGameInfo($game_id);

	$i = 0;

	// load sport_specific parameters based on sport_id
	$sport_specifics = PickGame::pickgame_getSportSpecifics($games[$i]["sport_id"]);

	
	$game_params = array();
	$display_params = array();
	$vote_params = array();
	$wager_params = array();
	$wager_params[0] = array();
	$wager_params[1] = array();
	$wager_params[2] = array();
	$wager_params[3] = array();
	$wager_counts = array();

	
	$game_params["game_id"] = $games[$i]["gameID"];
	$game_params["vis_team_abbr"] = $games[$i]["visTeamAbbr"];
	$game_params["vis_team_name"] = $games[$i]["visTeamName"];
	$game_params["vis_team_addl"] = $games[$i]["visTeamAddl"];
	$game_params["home_team_abbr"] = $games[$i]["homeTeamAbbr"];
	$game_params["home_team_name"] = $games[$i]["homeTeamName"];
	$game_params["home_team_addl"] = $games[$i]["homeTeamAddl"];
	$game_params["game_date"] = $games[$i]["gameDate"];

	
	
	$display_params["div_class"] = "-hovered";
	$display_params["up_img_src"] = "images/common/up-arrow.gif";
	$display_params["up_img_onclick"] = "";
	$display_params["up_img_mouse"] = "";
	$display_params["up_img_style"] = "";
	$display_params["down_img_src"] = "images/common/down-arrow.gif";
	$display_params["down_img_onclick"] = "";
	$display_params["down_img_mouse"] = "";
	$display_params["down_img_style"] = "";
	$display_params["text_class"] = "";
	$display_params["wager_class"] = "";
	
	$selected_wager = $wager_value;
	$selected_wager_level = $wager_level;
	//$selected_wager_levels = $wager_choices;

	$wager_params["title"] = "";
	

	for ($wager_count=0; $wager_count<4; $wager_count++) {
		$wager_params[$wager_count]["mouse"] = "";
		$wager_params[$wager_count]["on_click"] = "";
		$wager_params[$wager_count]["text_class"] = "-started";
		//$wager_params[$wager_count]["wager_level"] = $wager_count * 10;
		//if ($wager_count != 0) {
		//	$selected_wager_levels .= ";{$wager_params[$wager_count]["wager_level"]}";
		//}
	}
	
	$wager_counts = split(";", $wager_choices);



	$vis_votes = "";
	$home_votes = "";
	$total_votes = 0;
	
	$votes = PickGame::pickgame_getVoteTallies($game_id, false, "0");
	
	$vis_votes = (isset($votes[$games[$i]["visTeamAbbr"] . "_" . $game_id])) ? $votes[$games[$i]["visTeamAbbr"] . "_" . $game_id] : 0;
	$total_votes += $vis_votes;
	$home_votes = (isset($votes[$games[$i]["homeTeamAbbr"] . "_" . $game_id])) ? $votes[$games[$i]["homeTeamAbbr"] . "_" . $game_id] : 0;
	$total_votes += $home_votes;
	
	$vote_params["vis_votes"] = " ({$vis_votes} votes)";
	$vote_params["home_votes"] = " ({$home_votes} votes)";
	$vote_params["total_votes"] = " ({$total_votes} votes)";
	
	
		
	
	if($pick_choice === $games[$i]["visTeamAbbr"]) {
		$display_params["up_img_src"] = "images/common/up-arrow-on.gif";
		$display_params["up_img_style"] = " style=\"cursor: default;\" ";
		$display_params["down_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["homeTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$wager_choices}', 'pick-display-{$games[$i]["gameID"]}')\"";
		$display_params["down_img_mouse"] = "onmouseover=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',1)\" onmouseout=\"imageSwap('down-arrow-{$games[$i]["gameID"]}','down-arrow',0)\"";

	}
	else {
		$display_params["up_img_onclick"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$games[$i]["visTeamAbbr"]}', {$selected_wager}, {$selected_wager_level}, '{$wager_choices}', 'pick-display-{$games[$i]["gameID"]}')\"";
		$display_params["up_img_mouse"] = "onmouseover=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',1)\" onmouseout=\"imageSwap('up-arrow-{$games[$i]["gameID"]}','up-arrow',0)\"";
		$display_params["down_img_src"] = "images/common/down-arrow-on.gif";
		$display_params["down_img_style"] = " style=\"cursor: default;\" ";
	}
	
	
	for ($wager_count=0; $wager_count<4; $wager_count++) {
		if($wager_level != $wager_count) {
			$wager_params[$wager_count]["mouse"] = " onmouseover=\"doWagerHover('wager-level-{$games[$i]["gameID"]}-{$wager_count}')\" onmouseout=\"endWagerHover('wager-level-{$games[$i]["gameID"]}-{$wager_count}')\"";
			$wager_params[$wager_count]["on_click"] = "onclick=\"makeGamePick({$user_id}, '{$user_name}', {$games[$i]["gameID"]}, '{$pick_choice}', {$wager_counts[$wager_count]}, {$wager_count}, '{$wager_choices}', 'pick-display-{$games[$i]["gameID"]}')\"";
			$wager_params[$wager_count]["wager_level"] = $wager_counts[$wager_count];
			$wager_params[$wager_count]["text_class"] = "";
		}
		else {
			$wager_params[$wager_count]["text_class"] = "-selected";
			$wager_params[$wager_count]["wager_level"] = $wager_counts[$wager_count];
			//$wager_params[$wager_count]["mouse"] = "";
			//$wager_params[$wager_count]["on_click"] = "";


		}
	}

	
	$output .= PickGame::pickgame_displayGame($game_params, $vote_params, $display_params, $wager_params, $sport_specifics, null, false, $i, false);
	
	/*
				
	$output .= "<div class=\"pick-title{$text_class}\">
				{$games[$i]["visTeamName"]} @ {$games[$i]["homeTeamName"]} - ".date("g:i A", $games[$i]["gameDate"])."
			</div>
			<div class=\"pic-team-1{$text_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">
			<img src=\"{$up_img_src}\" id=\"up-arrow-{$games[$i]["gameID"]}\" {$up_img_style} {$up_img_mouse} {$up_img_onclick} />  
			{$games[$i]["visTeamAbbr"]} - {$games[$i]["visTeamAddl"]} <span id=\"vis-votes-{$games[$i]["gameID"]}\" class=\"pick-vote-span{$text_class}\">{$vis_votes}</span>
			</div>
			<div class=\"pic-team-2{$text_class}\" id=\"pick-group-{$i}-vis\" name=\"pick-group-{$i}-vis\">
				<img src=\"{$down_img_src}\" id=\"down-arrow-{$games[$i]["gameID"]}\" {$down_img_style} {$down_img_mouse} {$down_img_onclick} />
				{$games[$i]["homeTeamAbbr"]} - {$games[$i]["homeTeamAddl"]} <span id=\"home-votes-{$games[$i]["gameID"]}\" class=\"pick-vote-span{$text_class}\">{$home_votes}</span>
			</div>";*/
			
			


	/*
	$output = "<div class=\"pick-display\">
	inserting a pick for user: {$user_id} <br/>
	team: {$pick_choice} <br/>
	date: {$pick_date}<br/>
	game: {$game_id}
		</div>";
	*/
	
	return $output;

}

function pick_exists($user_name, $game_id) {

	$dbr =& wfGetDB( DB_MASTER );
	$s = $dbr->selectRow( 'pick_games_picks', array( 'pick_id' ), array( 'pick_username' => $user_name, 'game_id' => $game_id ),"" );
	if ( $s !== false ) {
		return true;
	} else {
		return false;
	}

}

function getSingleGameInfo($game_id) {

	//$sport_id = 1;


	$dbr =& wfGetDB( DB_MASTER );
	
	/*
	$sql = "SELECT pick_vis_abbr, pick_home_abbr, pick_game_visitor, pick_game_home, pick_visitor_addl, pick_home_addl, pick_game_id, UNIX_TIMESTAMP(pick_game_date) as pick_game_timestamp
		FROM pick_games 
		WHERE pick_game_id = {$game_id}
		ORDER BY pick_game_date
		";
		*/
		
	$games = array();
	//$s = $dbr->selectRow($sql);
	$s = $dbr->selectRow( 'pick_games', array( 'pick_vis_abbr', 'pick_home_abbr', 'pick_game_visitor', 'pick_game_home', 'pick_visitor_addl', 'pick_home_addl', 'pick_game_id', 'UNIX_TIMESTAMP(pick_game_date) as pick_game_timestamp', 'pick_sport_id', 'pick_identifier'),
		array( 'pick_game_id' => $game_id ),"");
	if ( $s !== false ) {
		 $games[0] = array(
			 "visTeamAbbr"=>$s->pick_vis_abbr,
			 "homeTeamAbbr"=>$s->pick_home_abbr,
			 "visTeamName"=>$s->pick_game_visitor,
			 "homeTeamName"=>$s->pick_game_home,
			 "visTeamAddl"=>$s->pick_visitor_addl,
			 "homeTeamAddl"=>$s->pick_home_addl,
			 "gameDate"=>$s->pick_game_timestamp,
			 "gameID"=>$s->pick_game_id,			 
			 "sport_id"=>$s->pick_sport_id,
			 "identifier"=>$s->pick_identifier			 
			 );
	}
	else {
		 $games[0] = array(
			 "visTeamAbbr"=>"-",
			 "homeTeamAbbr"=>"-",
			 "visTeamName"=>"-",
			 "homeTeamName"=>"-",
			 "visTeamAddl"=>"-",
			 "homeTeamAddl"=>"-",
			 "gameDate"=>"-",
			 "gameID"=>"-",
			 "sport_id"=>"-",
			 "identifier"=>"-"
			 );
	}
	
	return $games;

}


function getSingleGameInfoByIdentifier($identifier) {

	//$sport_id = 1;


	$dbr =& wfGetDB( DB_MASTER );
	
	$game_id;
	//$s = $dbr->selectRow($sql);
	$s = $dbr->selectRow( 'pick_games', array( 'pick_game_id'),
		array( 'pick_identifier' => $identifier ),"");
	if ( $s !== false ) {
		 $game_id = $s->pick_game_id;
	}
	else {
		 $game_id = 0;
	}
	
	return $game_id;

}

$wgAjaxExportList [] = 'wfMakePickGameUpdate';
function wfMakePickGameUpdate($game_id, $home_score, $vis_score, $status, $status_desc, $category) { 
	global $IP, $wgUser, $wgOut;
	
	$i=0;
	$games = getSingleGameInfo($game_id);
	if ($home_score > $vis_score) {
		$winner = $games[$i]["homeTeamName"];
		$winner_abbr = $games[$i]["homeTeamAbbr"];
	}
	elseif ($home_score < $vis_score) {
		$winner = $games[$i]["visTeamName"];
		$winner_abbr = $games[$i]["visTeamAbbr"];
	}
	else {
		$winner = "-";
		$winner_abbr = "-";
	}
	
	$pick_date = date("Y-m-d H:i:s");
		
	//get database
	$dbr =& wfGetDB( DB_MASTER );
	
	if (!pickgame_result_exists($game_id)) {
		//write new data to pick_games_pick
		$dbr->insert( '`pick_games_results`',
		array(
			'pick_game_id' => $game_id,
			'home_score' => $home_score,
			'vis_score' => $vis_score,
			'game_winner' => $winner,
			'game_winner_abbr' => $winner_abbr,
			'game_status' => $status,
			'sport_id' => $games[$i]["sport_id"],
			'game_identifier' => $games[$i]["identifier"],
			'update_date' => $pick_date,
			'game_status_desc' => $status_desc
			), __METHOD__
		);
	}
	else {
		$dbr->update( '`pick_games_results`',
		array(
			'home_score' => $home_score,
			'vis_score' => $vis_score,
			'game_winner' => $winner,
			'game_winner_abbr' => $winner_abbr,
			'game_status' => $status,
			'sport_id' => $games[$i]["sport_id"],
			'game_identifier' => $games[$i]["identifier"],
			'update_date' => $pick_date,
			'game_status_desc' => $status_desc
			),
		array(
			'pick_game_id' => $game_id
		)
			, __METHOD__
		);
	}
	
	$game = array(
		"home" => $games[$i]["homeTeamName"],
		"vis" => $games[$i]["visTeamName"],
		"home_abbr" => $games[$i]["homeTeamAbbr"],
		"vis_abbr" => $games[$i]["visTeamAbbr"],
		"game_id" => $game_id,
		"timestamp" => $games[$i]["gameDate"],
		"category" => $category,
		"home_score" => $home_score,
		"vis_score" => $vis_score,
		"game_winner" => $winner,
		"game_winner_abbr" => $winner_abbr,
		"game_status" => $status,
		"game_status_desc" => $status_desc
	);
	
	return PickGameNFLUpdate::display_entered_game($game);	

}

$wgAjaxExportList [] = 'wfEditPickGameUpdate';
function wfEditPickGameUpdate($game_id, $home_score, $vis_score, $status, $status_desc, $category) { 
	global $IP, $wgUser, $wgOut;
	
	$i=0;
	$games = getSingleGameInfo($game_id);
	if ($home_score > $vis_score) {
		$winner = $games[$i]["homeTeamName"];
		$winner_abbr = $games[$i]["homeTeamAbbr"];
	}
	elseif ($home_score < $vis_score) {
		$winner = $games[$i]["visTeamName"];
		$winner_abbr = $games[$i]["visTeamAbbr"];
	}
	else {
		$winner = "-";
		$winner_abbr = "-";
	}
	
	
	$game = array(
		"home" => $games[$i]["homeTeamName"],
		"vis" => $games[$i]["visTeamName"],
		"home_abbr" => $games[$i]["homeTeamAbbr"],
		"vis_abbr" => $games[$i]["visTeamAbbr"],
		"game_id" => $game_id,
		"timestamp" => $games[$i]["gameDate"],
		"category" => $category,
		"home_score" => $home_score,
		"vis_score" => $vis_score,
		"game_winner" => $winner,
		"game_winner_abbr" => $winner_abbr,
		"game_status" => $status,
		"game_status_desc" => $status_desc
	);
	
	return PickGameNFLUpdate::display_unentered_game($game, $games[$i]["sport_id"]);	

}

function pickgame_result_exists($game_id) {

	$dbr =& wfGetDB( DB_MASTER );
	$s = $dbr->selectRow( 'pick_games_results', array( 'game_result_id' ), array( 'pick_game_id' => $game_id ),"" );
	if ( $s !== false ) {
		return true;
	} else {
		return false;
	}

}

$wgAjaxExportList [] = 'wfRemovePickGameResult';
function wfRemovePickGameResult($game_id, $category) {
	global $IP, $wgUser, $wgOut;
	
	if (!is_null($game_id) && $game_id != "") {
		
		//get database
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "DELETE FROM pick_games_results where pick_game_id={$game_id}";
		
		$dbr->query($sql);
		
		$game_info = getSingleGameInfo($game_id);
		
		$game = array(
			"home" => $game_info[0]["homeTeamName"],
			"vis" => $game_info[0]["visTeamName"],
			"home_abbr" => $game_info[0]["homeTeamAbbr"],
			"vis_abbr" => $game_info[0]["visTeamAbbr"],
			"game_id" => $game_id,
			"timestamp" => $game_info[0]["gameDate"],
			"category" => $category,
			"home_score" => "",
			"vis_score" => "",
			"game_winner" => "",
			"game_winner_abbr" => "",
			"game_status" => "",
			"game_status_desc" => ""
		);
		
		return PickGameNFLUpdate::display_unentered_game($game, $game_info[0]["sport_id"]);


	}
	
	return "No Game ID passed";
}

$wgAjaxExportList [] = 'wfEditPickGameEntry';
function wfEditPickGameEntry($game_id, $category) { 
	global $IP, $wgUser, $wgOut;
	
	$i=0;
	$games = getSingleGameInfo($game_id);	
	
	$game = array(
		"game_id" => $game_id,
		"home_abbr" => $games[$i]["homeTeamAbbr"],
		"vis_abbr" => $games[$i]["visTeamAbbr"],
		"game_date" => date("Y-m-d H:i:s", $games[$i]["gameDate"]),
		"category" => $category,
		"visitor_addl" => $games[$i]["visTeamAddl"],
		"home_addl" => $games[$i]["homeTeamAddl"],
		"game_visitor" => $games[$i]["visTeamName"],
		"game_home" => $games[$i]["homeTeamName"],
		"sport_id" => $games[$i]["sport_id"],
		"identifier" => $games[$i]["identifier"]
	);
	
	return PickGameGamesUpdate::display_unentered_game_entry($game, $games[$i]["sport_id"]);	

}

$wgAjaxExportList [] = 'wfUpdatePickGameEntry';
function wfUpdatePickGameEntry($game_id, $visTeamAbbr, $visTeamName, $visTeamAddl, $homeTeamAbbr, $homeTeamName, $homeTeamAddl, $game_date, $category, $identifier, $sport_id, $empty_count) { 
	global $IP, $wgUser, $wgOut;
	
	$i=0;	
	
	$new_empty_count = $empty_count + 1;
	//$pick_date = date("Y-m-d H:i:s");
		
	//get database
	$dbr =& wfGetDB( DB_MASTER );
	
	if ($game_id=='0') {
		//write new data to pick_games_pick
		$dbr->insert( '`pick_games`',
		array(
			'pick_sport_id' => $sport_id,
			'pick_vis_abbr' => $visTeamAbbr,
			'pick_home_abbr' => $homeTeamAbbr,
			'pick_game_date' => $game_date,
			'pick_game_visitor' => $visTeamName,
			'pick_game_home' => $homeTeamName,
			'pick_visitor_addl' => $visTeamAddl,
			'pick_home_addl' => $homeTeamAddl,
			'pick_identifier' => $identifier,
			'pick_category' => $category
			), __METHOD__
		);
		$new_empty = array('game_id'=>0);
		$addl_return = PickGameGamesUpdate::display_empty_game_entry($new_empty, $new_empty_count, $sport_id, $category);
		$game_id = getSingleGameInfoByIdentifier($identifier);
	}
	else {
		$dbr->update( '`pick_games`',
		array(
			'pick_sport_id' => $sport_id,
			'pick_vis_abbr' => $visTeamAbbr,
			'pick_home_abbr' => $homeTeamAbbr,
			'pick_game_date' => $game_date,
			'pick_game_visitor' => $visTeamName,
			'pick_game_home' => $homeTeamName,
			'pick_visitor_addl' => $visTeamAddl,
			'pick_home_addl' => $homeTeamAddl,
			'pick_identifier' => $identifier,
			'pick_category' => $category
			),
		array(
			'pick_game_id' => $game_id
		)
			, __METHOD__
		);
		
		$addl_return = "";
	}
	
	$game = array(
		"game_id" => $game_id,
		"home_abbr" => $homeTeamAbbr,
		"vis_abbr" => $visTeamAbbr,
		"game_date" => $game_date,
		"category" => $category,
		"visitor_addl" => $visTeamAddl,
		"home_addl" => $homeTeamAddl,
		"game_visitor" => $visTeamName,
		"game_home" => $homeTeamName,
		"sport_id" => $sport_id,
		"identifier" => $identifier
	);
	
	$return_val = PickGameGamesUpdate::display_entered_game_entry($game). $addl_return;
	//return PickGameGamesUpdate::display_entered_game_entry($game) . $addl_return;
	return $return_val;
		

}

$wgAjaxExportList [] = 'wfRemovePickGameEntry';
function wfRemovePickGameEntry($game_id) {
	global $IP, $wgUser, $wgOut;
	
	if (!is_null($game_id) && $game_id != "" && $game_id != "0") {
		
		//get database
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "DELETE FROM pick_games where pick_game_id={$game_id}";
		
		$dbr->query($sql);
		
		return "ok";


	}
	
	return "No Game ID passed";
}

?>
