<?php

//$wgHooks['RandomGameUnit'][] = 'wfPickGameUnit';

function wfPickGameUnit( &$random_games, &$custom_fallback ){
	
	$random_games[] = "custom";
	$custom_fallback = "wfDisplayPickGameUnit";
	
	return true;
}

function wfDisplayPickGameUnit(){
	
	global $wgMemc, $wgUser, $wgUploadPath;
		
		$output = "";
		$games_output = "";                                                                                  
		$sports_list = PickGame::sports_list();
		$todays_games = array();

		$key = wfMemcKey( 'upcoming', 'games', 'randomlist' );
		$data = $wgMemc->get( $key );
		
		//no cache, load from db
		if(!$data){
			wfDebug( "loading random pick game data from db\n" );

			$list = array();

			foreach($sports_list as $sport_key=>$value) {
				$sport_count++;
				$i = $sport_key;
				$sport_specifics = PickGame::pickgame_getSportSpecifics($i);
				$current_category = PickGame::get_category_from_date(date("Ymd"), $sport_specifics);
				$current_category = PickGame::check_first_game($current_category, $sport_specifics);
				
					$sql = "SELECT pick_games.pick_game_id, pick_games.pick_sport_id, UNIX_TIMESTAMP(pick_game_date) as pick_game_timestamp, pick_game_visitor, pick_game_home, pick_visitor_addl, pick_home_addl, pick_identifier, pick_home_abbr, pick_vis_abbr, pick_category, count(pick_id) as votes from pick_games left outer join pick_games_picks on pick_games.pick_game_id=pick_games_picks.game_id WHERE pick_category>={$current_category} AND pick_sport_id={$i} GROUP BY pick_game_id ORDER BY pick_category ASC, votes DESC, pick_game_timestamp ASC LIMIT 0,3";
					$dbr =& wfGetDB( DB_MASTER );
		
					$res = $dbr->query($sql);
					while ($row = $dbr->fetchObject( $res ) ) {
						$list[] = array("sport_id"=>$i, "data"=>$row);
						//wfDebug( "got game " . $list[sizeof($list)-1]["data"]->pick_identifier ."\n" );
					}			
			}
			
			$wgMemc->set( $key, $list, 60 * 60 );

		}
		else{
				wfDebug( "loading random pick game data from cache\n" );
				$list = $data;
		}
			
			$games_output .= "<div class=\"game-unit-container\">";
				


		if (sizeof($list) && sizeof($list) > 0) {
			
			$j = array_rand($list);
			
			if ($wgUser->isLoggedIn()){
				//$link_location = "/index.php?title=Special:PickGame/{$i}";
				$link_location = Title::MakeTitle(NS_SPECIAL, "PickGame/{$list[$j]["sport_id"]}")->escapeFullUrl();
			}
			else {
				//$link_location = "/index.php?title=Special:UserRegister";
				$link_location = Title::MakeTitle(NS_SPECIAL, "UserRegister")->escapeFullUrl();
	
			}

			
			$row = $list[$j]["data"];
			$sport_specifics = PickGame::pickgame_getSportSpecifics($list[$j]["sport_id"]);

			 $games_output .= "<h2>" . $sport_specifics["page_title"] . "</h2>";
			 $games_output .= "<div class=\"pickem-unit-game\">";
			 $games_output .= "<div class=\"pickem-unit-date\">" . date("D, M j - g:i A", $row->pick_game_timestamp) . " ({$row->votes} picks)</div>";
			 $games_output .= "<div class=\"pickem-unit-matchup\">
			 <div class=\"pickem-unit-visitor\" onclick=\"window.location='{$link_location}'\"  onmouseover=\"imageSwap('todays-games-up-arrow-{$row->pick_game_id}','up-arrow',1,'$wgUploadPath')\" onmouseout=\"imageSwap('todays-games-up-arrow-{$row->pick_game_id}','up-arrow',0, '$wgUploadPath')\"><img id=\"todays-games-up-arrow-{$row->pick_game_id}\" src=\"{$wgUploadPath}/common/up-arrow.gif\">{$row->pick_game_visitor}{$sport_specifics["additional_delimiter"]}{$row->pick_visitor_addl}</div>
			 <div class=\"pickem-unit-home\" onclick=\"window.location='{$link_location}'\" onmouseover=\"imageSwap('todays-games-down-arrow-{$row->pick_game_id}','down-arrow',1,'$wgUploadPath')\" onmouseout=\"imageSwap('todays-games-down-arrow-{$row->pick_game_id}','down-arrow',0,'$wgUploadPath')\"><img id=\"todays-games-down-arrow-{$row->pick_game_id}\" src=\"{$wgUploadPath}/common/down-arrow.gif\">{$row->pick_game_home}{$sport_specifics["additional_delimiter"]}{$row->pick_home_addl}</div>
			 </div></div>";
			 $count++;
			 
		}
		else {
			$games_output .= "<div class=\"pickem-unit-nogame\">There are no upcoming games</div>";
		}

		$games_output .= "</div>";
		
		$output .= $games_output;
		
		return $output;
	
	//return "a random pick game will go here";
}
?>
