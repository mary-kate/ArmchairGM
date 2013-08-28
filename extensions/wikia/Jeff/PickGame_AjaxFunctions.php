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
	$sport_specifics = pickgame_getSportSpecifics($games[$i]["sport_id"]);

	
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
	
	$votes = pickgame_getVoteTallies($game_id, false, "0");
	
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

	
	$output .= pickgame_displayGame($game_params, $vote_params, $display_params, $wager_params, $sport_specifics, null, false, $i, false);
	
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
	$s = $dbr->selectRow( 'pick_games', array( 'pick_vis_abbr', 'pick_home_abbr', 'pick_game_visitor', 'pick_game_home', 'pick_visitor_addl', 'pick_home_addl', 'pick_game_id', 'UNIX_TIMESTAMP(pick_game_date) as pick_game_timestamp', 'pick_sport_id'),
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
			 "sport_id"=>$s->pick_sport_id			 
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
			 "sport_id"=>"-"
			 );
	}
	
	return $games;

}


?>
