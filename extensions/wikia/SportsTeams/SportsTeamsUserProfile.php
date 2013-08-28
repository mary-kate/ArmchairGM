<?php
$wgHooks['UserProfileBeginLeft'][] = 'wfUserProfileLatestThought';
$wgHooks['UserProfileBeginLeft'][] = 'wfUserProfileFavoriteTeams';

function wfUserProfileFavoriteTeams($user_profile){
	global $wgUser, $wgOut, $wgTitle, $IP, $wgUploadPath;
	
	$output = "";
	$user_id = $user_profile->user_id;
	
	$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/SportsTeams/SportsTeamsUserProfile.js\"></script>\n");
	
	$add_networks_title = Title::makeTitle(NS_SPECIAL, "UpdateFavoriteTeams");
	
	$favs = SportsTeams::getUserFavorites($user_id);
	
	if ($favs) {
	
		$output .= "<div class=\"user-section-heading\">
			<div class=\"user-section-title\">
				" . wfMsgForContent( 'st_profile_networks' ) . "
			</div>
			<div class=\"user-section-actions\">
				<div class=\"action-right\">";
				if ($wgUser->getName()==$wgTitle->getText())$output .= "<a href=\"".$add_networks_title->escapeFullURL()."\">" . wfMsgForContent( 'st_profile_add_network' ) . "</a>";
				$output .= "</div>
				<div class=\"cleared\"></div>
			</div>
		</div>
		<div class=\"network-container\">";

		foreach($favs as $fav) {
			$homepage_title = Title::makeTitle(NS_SPECIAL,"FanHome");

			if ($wgUser->getID()==$user_id) {
				$status_link = " <span class=\"status-message-add\"> - <a href=\"javascript:void(0);\" onclick=\"show_message_box({$fav["order"]},{$fav["sport_id"]},{$fav["team_id"]})\" rel=\"nofollow\">" . wfMsgForContent( 'st_profile_add_thought' ) . "</a></span>";
			}

			$network_update_message = "";

			if ($user_updates[$fav["sport_id"]."-".$fav["team_id"]]) {
				$network_update_message =  $user_updates[$fav["sport_id"]."-".$fav["team_id"]];
			}

			if ($fav["team_name"]) {
				$display_name = $fav["team_name"];
				$logo = "<img src=\"{$wgUploadPath}/team_logos/" . SportsTeams::getTeamLogo($fav["team_id"],"s") . "\" border=\"0\" alt=\"\" />";

			} else {
				$display_name = $fav["sport_name"];
				$logo = "<img src=\"{$wgUploadPath}/sport_logos/" . SportsTeams::getSportLogo($fav["sport_id"],"s") . "\" border=\"0\" alt=\"\" />";
			}

			$output .= "<div class=\"network\">
				{$logo}
				<a href=\"".$homepage_title->escapeFullURL('sport_id='.$fav["sport_id"].'&team_id='.$fav["team_id"])."\" rel=\"nofollow\">{$display_name}</a>
				$status_link
			</div>

			<div class=\"status-update-box\" id=\"status-update-box-{$fav["order"]}\" style=\"display:none\"></div>";	

		}

		$output .= "<div class=\"cleared\"></div>
		</div>";
		
	} else if ($wgUser->getName()==$wgTitle->getText()){
		$output .= "<div class=\"user-section-heading\">
			<div class=\"user-section-title\">
				" . wfMsgForContent( 'st_profile_networks' ) . "
			</div>
			<div class=\"user-section-actions\">
				<div class=\"action-right\">
					<a href=\"".$add_networks_title->escapeFullURL()."\">" . wfMsgForContent( 'st_profile_add_network' ) . "</a>
				</div>
				<div class=\"cleared\"></div>
			</div>
		</div>
		<div class=\"no-info-container\">
			" . wfMsgForContent( 'st_profile_no_networks' ) . "
		</div>";
	}
	
	

	
	$wgOut->addHTML( $output );
	return true;
}
	
function wfUserProfileLatestThought($user_profile){
	global $wgUser, $wgOut, $wgTitle, $IP;
	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	
	$user_id = $user_profile->user_id;
	$s = new UserStatus();
	$user_update = $s->getStatusMessages($user_id,0,0,1,1);
	$user_update = $user_update[0];
	
	//safe urls
	$more_thoughts_link =  Title::makeTitle( NS_SPECIAL, "UserStatus");
	$thought_link =  Title::makeTitle( NS_SPECIAL, "ViewThought");
	
	if ($user_update) {
		
		$output .= "<div class=\"user-section-heading\">
			<div class=\"user-section-title\">
				" . wfMsgForContent( 'st_profile_latest_thought' ) . "
			</div>
			<div class=\"user-section-actions\">
				<div class=\"action-right\">
					<a href=\"" . $more_thoughts_link->escapeFullURL("user=".$user_profile->user_name) . "\" rel=\"nofollow\">" . wfMsgForContent( 'st_profile_view_all' ) . "</a>
				</div>
				<div class=\"cleared\"></div>
			</div>
		</div>";
		
		if( $wgUser->getName()==$user_update["user_name"]){
			$vote_count = wfMsgExt( 'st_profile_num_agree', 'parsemag', $user_update["plus_count"]);
		}
			
		$view_thought_link = "<a href=\"" . $thought_link->escapeFullURL("id={$user_update["id"]}") . "\" rel=\"nofollow\">{$vote_count}</a>";
				
		if( $wgUser->isLoggedIn() && $wgUser->getName()!=$user_update["user_name"]){
			if( !$user_update["voted"] ){
				$vote_link = "<a href=\"javascript:void(0);\" onclick=\"vote_status({$user_update["id"]},1)\" rel=\"nofollow\">" . wfMsgForContent( 'st_profile_do_you_agree' ) . "</a>";
			}else{
				//$vote_link = $user_update["plus_count"] . " ". (($user_update["plus_count"]==1)?"person":"people") . " agree" . (($user_update["plus_count"]==1)?"s":"");
				$vote_count = wfMsgExt( 'st_profile_num_agree', 'parsemag', $user_update["plus_count"]);
			}
		}
		$output .= "<div class=\"status-container\" id=\"status-update\">
			<div id=\"status-update\" class=\"status-message\">".
				SportsTeams::getLogo($user_update["sport_id"],$user_update["team_id"],"s")."
				{$user_update["text"]}
			</div>
			<div class=\"user-status-profile-vote\">
				<span class=\"user-status-date\">
					".get_time_ago($user_update["timestamp"])." " . wfMsgForContent( 'st_profile_ago' ) . " 
				</span>
				{$vote_link} {$view_thought_link} 
			</div>
		</div>"; 
	}
	else {
		$output .= "<script type=\"text/javascript\">var __thoughts_text__ = \"" . wfMsgForContent( 'st_profile_latest_thought' ) . "\"; var __view_all__ = \"" . wfMsgForContent( 'st_profile_view_all' ) . "\"; var __more_thoughts_url__ = \"". $more_thoughts_link->escapeFullURL("user=".$user_profile->user_name) . "\";</script>";
	}
	
	$wgOut->addHTML( $output );
	return true;
	
}

?>
