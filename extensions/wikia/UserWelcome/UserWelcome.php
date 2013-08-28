<?php
$wgExtensionFunctions[] = "wfWelcomeUser";
$wgExtensionFunctions[] = 'wfWelcomeUserReadLang';

function wfWelcomeUser() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "welcomeUser", "getWelcomeUser" );
}
//read in localisation messages
function wfWelcomeUserReadLang(){
	global $wgMessageCache, $IP;
	require_once ( "UserWelcome.i18n.php" );
	foreach( efWikiaUserWelcome() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}

function getWelcomeUser($input, $args, $parser) {
	
	$output = "";
	$output .= getWelcome();
	
	return $output;
	
}

function getWelcome() {

	global $wgUser, $IP, $wgUploadPath;
		
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		
		//get votes, edit, comment count
		$dbr =& wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
		$vote_count = number_format($s->count);
		$s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
		$comment_count = number_format($s->count);
		$edits_count = number_format(SiteStats::edits());
		$good_count = number_format(SiteStats::articles());
		
		//get stats and user level
		$stats = new UserStats($wgUser->getID(), $wgUser->getName());
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		
		//safe links
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		$avatar_link = Title::makeTitle(NS_SPECIAL,"UploadAvatar");
		$invite_link = Title::makeTitle(NS_SPECIAL,"InviteContacts");
		
		//make an avatar
		$avatar = new wAvatar($wgUser->getID(),"l");
		
		$output = "";
		
		//PROFILE TOP IMAGES/POINTS

		$output .= "<div class=\"mp-welcome-logged-in\">
			<h2>".wgGetWelcomeMessage()."</h2>
			<div class=\"mp-welcome-image\">
			<a href=\"". $wgUser->getUserPage()->escapeFullURL(). "\" rel=\"nofollow\"><img src=\"{$wgUploadPath}/avatars/" . $avatar->getAvatarImage() . "\" alt=\"\" border=\"0\"/></a>";
				if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
					$output .= "<div><a href=\"".$avatar_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_welcome_upload")."</a></div>";
				} else {
					$output .= "<div><a href=\"".$avatar_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_welcome_edit")."</a></div>";
				}
			$output .= "</div>
			<div class=\"mp-welcome-points\">
				<div class=\"points-and-level\">
					<div class=\"total-points\">".wfMsg("mp_welcome_points", $stats_data["points"])."</div>
					<div class=\"honorific-level\"><a href=\"".$level_link->escapeFullURL()."\">({$user_level->getLevelName()})</a></div>
				</div>
				<div class=\"cleared\"></div>
				<div class=\"needed-points\">
					".wfMsg("mp_welcome_needed_points", $level_link->escapeFullURL(), $user_level->getNextLevelName(), $user_level->getPointsNeededToAdvance())."
				</div>
			</div>
			<div class=\"cleared\"></div>";
			
			$output .= getRequests();
			
			$output .= "</div>";
		  
		return $output;
}

function getRequests() {
		
		//get requests
		$requests = getNewMessagesLink() . getRelationshipRequestLink() . getNewGiftLink() . getNewSystemGiftLink();
		
		if ($requests) {
			
			$output .= "<div class=\"mp-requests\">
				<h3>".wfMsg("mp_requests_title")."</h3>
				<div class=\"mp-requests-message\">
					".wfMsg("mp_requests_message")."
				</div>
				$requests
			</div>";

		 }
		
		return $output;
		
}

	function getRelationshipRequestLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		$friend_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),1);
		$foe_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),2);
		$relationship_request_link = Title::makeTitle(NS_SPECIAL, "ViewRelationshipRequests");
		
		$rel_title = Title::makeTitle(NS_SPECIAL,"ViewRelationshipRequests");
		$output = "";
		if ($friend_request_count) {
			
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/addedFriendIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$relationship_request_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_friend", "parsemag", $friend_request_count)."</a>
			</p>";
			
		 }
		if ($foe_request_count) {
			  $output .= "<p>
				<img src=\"{$wgUploadPath}/common/addedFoeIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$relationship_request_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_foe", "parsemag", $foe_request_count)."</a>
			</p>";
		 }
		 return $output;
	}

	function getNewGiftLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
		$gift_count = UserGifts::getNewGiftCount($wgUser->getID());
		$gifts_title = Title::makeTitle(NS_SPECIAL,"ViewGifts");
		$output = "";
		if ($gift_count) {
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/icon_package_get.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$gifts_title->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_gift", "parsemag", $gift_count)."</a>
			</p>";
		 }
		 return $output;		
	}

	function getNewSystemGiftLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
		$gift_count = UserSystemGifts::getNewSystemGiftCount($wgUser->getID());
		$gifts_title = Title::makeTitle(NS_SPECIAL,"ViewSystemGifts");
		$output = "";
		
		if ($gift_count) {
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/awardIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$gifts_title->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_award", "parsemag", $gift_count)."</a>
			</p>";
		 }
		
		 return $output;		
	}
	
	function getNewMessagesLink(){
		global $wgUser, $wgUploadPath;
		$new_messages = UserBoard::getNewMessageCount($wgUser->getID());
		if ( $new_messages ) {
			$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/emailIcon.gif\" alt=\"email icon\" border=\"\"/> 
				<a href=\"".$board_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_request_new_message")."</a>
			</p>";
		}
		return $output;
	}

?>
