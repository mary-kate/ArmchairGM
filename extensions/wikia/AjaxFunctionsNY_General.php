<?php
/*
 * Ajax Functions used by Wikia extensions
 */
 
$wgAjaxExportList [] = 'wfPageTitleExists';
function wfPageTitleExists( $page_name ){ 	
	//load mediawiki objects to check if article exists
	$page_title =  Title::newFromText( $page_name );
	$article = new Article( $page_title );
	if( $article->exists() ){ 
		return "Page exists";
	} else {
		return "OK";
	}
}

$wgAjaxExportList [] = 'wfUsernameExists';
function wfUsernameExists( $user_name ){ 	
	$user_id = User::idFromName($user_name);
	if( $user_id > 0 ){ 
		return "exists";
	} else {
		return "OK";
	}
}

$wgAjaxExportList [] = 'wfCaptchaCheck';
function wfCaptchaCheck( $captcha_id, $answer ){
	global $wgCaptchaSecret;
	$info = $_SESSION['captcha' . $captcha_id];
	
	$digest = $wgCaptchaSecret . $info['salt'] . $answer . $wgCaptchaSecret . $info['salt'];
	$answerHash = substr( md5( $digest ), 0, 16 );
		
	if( $answerHash == $info['hash'] ) {
		return "OK";
	}else{
		return "false";
	}
}

$wgAjaxExportList [] = 'wfSlideShow';
function wfSlideShow($pic_num, $user, $direction) {
	$dbr =& wfGetDB( DB_MASTER );
	
	$sql_total = "SELECT count(*) as count FROM image INNER JOIN
		categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name) WHERE img_user_text = '" . addslashes($user) . "' AND cl_to = 'Profile_Pictures'
	";
    $res_total = $dbr->query($sql_total);
	$row = $dbr->fetchObject($res_total);
	$total = $row->count;
	$next = $pic_num + 1;
	$previous = $pic_num - 1;
	
	if ($next == ($total)) {
		$next = 0;
	}
	
	if ($next==1) {
		$previous = ($total-1);
	}
	
	//image directions
	if ($direction == "next") {
		$pic_num = $next;
		
	} else if ($direction == "previous") {
		$pic_num = $previous;
	}
	
	
	$sql = "SELECT img_name, img_user, img_user_text, img_timestamp FROM image
	INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
	WHERE img_user_text = '".addslashes($user)."' 
	AND cl_to = 'Profile_Pictures'
	ORDER BY img_timestamp DESC LIMIT {$pic_num},1";
	
	$res = $dbr->query($sql);
	
	if ($pic_num < $total) {
		$sql_preload = "SELECT img_name, img_user, img_width, img_user_text, img_timestamp FROM image
		INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user)."' 
		AND cl_to = 'Profile_Pictures'
		ORDER BY img_timestamp DESC LIMIT ".($pic_num+1).",3";
	}
	
	$res1 = $dbr->query($sql_preload);
	
	$row = $dbr->fetchObject($res);
	$row1 = $dbr->fetchObject($res1);
	
	if ($row) {
		
		$image_path = $row->img_name;
		$render_image = Image::newFromName ($image_path);
		$thumb_image = $render_image->getThumbNail(600,0,true);
		$thumbnail = $thumb_image->toHtml( array("id"=>"user-image", "onmouseover"=>"doHover('user-image')", "onmouseout"=>"endHover('user-image')") );
		$user_name = addslashes($user);
		$picture_counter = $pic_num + 1;
				
		$divcontent = "
		<div class=\"user-image\">
			<p>
				Photo {$picture_counter} of $total
			</p>
			<p>
				<a href=\"javascript:loadImage('{$pic_num}', '".addslashes($user_name)."', 'next');\">{$thumbnail}</a>
			</p>
		</div>
		
		<div class=\"slide-show-bottom\">
			<ul>
				<li><a href=\"javascript:loadImage('{$pic_num}', '".addslashes($user_name)."', 'previous');\">Previous</a></i> 
				<li><a href=\"javascript:loadImage('{$pic_num}', '".addslashes($user_name)."', 'next');\">Next</a></li>
			</ul> 
		</div>";
		
		
		$output .= "<div style=\"display:none\">";

		while ($row1 = $dbr->fetchObject($res1)) {
			$image_path_preload = $row1->img_name;
			$render_image_preload = Image::newFromName ($image_path_preload);
			$thumb_image_preload = $render_image_preload->getThumbNail(600,0,true);
			$thumbnail_preload = $thumb_image_preload->toHtml();

			$output .= "<p>{$thumbnail_preload}</p>";

		}

		$output .= "</div>";
		
		
		
		return $divcontent;
	} else {
		return false;
	}
	
}

$wgAjaxExportList [] = 'wfSendBoardMessage';
function wfSendBoardMessage($user_name,$message,$message_type,$count){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/UserBoard/UserBoardClass.php");
	$user_name = stripslashes($user_name);
	$user_name = urldecode($user_name);
	$user_id_to = User::idFromName($user_name);
	$b = new UserBoard();
	
	$m = $b->sendBoardMessage($wgUser->getID(),$wgUser->getName(),$user_id_to, $user_name, urldecode($message),$message_type);
	
	return $b->displayMessages($user_id_to,0,$count);

}

$wgAjaxExportList [] = 'wfDeleteBoardMessage';
function wfDeleteBoardMessage($ub_id){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/UserBoard/UserBoardClass.php");
	 
	$b = new UserBoard();
	if( $b->doesUserOwnMessage($wgUser->getID(),$ub_id) ){
		$b->deleteMessage($ub_id);
	}
	return "ok";

}

$wgAjaxExportList [] = 'wfAddUserStatusProfile';
function wfAddUserStatusProfile($sport_id, $team_id, $text,$count){ 
	global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;
	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	$text = urldecode($text);
	$b = new UserStatus();
	$m = $b->addStatus($sport_id, $team_id,$text);
	
	$output .=  "<div class=\"status-message\">".
		SportsTeams::getLogo($sport_id,$team_id,"s").
		$b->formatMessage($text).
	"</div>
	<div class=\"user-status-profile-vote\">
		<div class=\"user-status-date\">
			just added
		</div>
	</div>";

	return $output;

}

$wgAjaxExportList [] = 'wfAddUserStatusNetwork';
function wfAddUserStatusNetwork($sport_id, $team_id, $text,$count){ 
	global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;
	//return $sport_id;
	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	 
	$b = new UserStatus();
	$m = $b->addStatus($sport_id, $team_id,urldecode($text));
	
	return $b->displayStatusMessages(0,$sport_id,$team_id,$count,1);

}

$wgAjaxExportList [] = 'wfGetUserStatusProfile';
function wfGetUserStatusProfile($user_id, $num){ 
	global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;

	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	 
	$b = new UserStatus();
	
	$update = $b->getStatusMessages($user_id,0,0,1,$num);
	$update = $update[0];
	return  SportsTeams::getLogo($update["sport_id"],$update["team_id"],"s") . "<img src=\"images/common/quoteIcon.png\" border=\"0\"  style=\"margin-left:5px;\"/>{$update["text"]} <img src=\"images/common/endQuoteIcon.png\" border=\"0\"/><span class=\"user-status-date\">
						".get_time_ago($update["timestamp"])." ago
					</span>";
	
}

$wgAjaxExportList [] = 'wfVoteUserStatus';
function wfVoteUserStatus($us_id, $vote){ 
	global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;

	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	 
	$b = new UserStatus();
	$update = $b->addStatusVote($us_id, $vote);
	$votes = $b->getStatusVotes($us_id);
	
	$output = $votes["plus"] . " ". (($votes["plus"]==1)?"person":"people") . " agree" . (($votes["plus"]==1)?"s":"");
	return  $output;
	
}

$wgAjaxExportList [] = 'wfDeleteUserStatus';
function wfDeleteUserStatus($us_id){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
	 
	$b = new UserStatus();
	if( $b->doesUserOwnStatusMessage($wgUser->getID(),$us_id) ){
		$b->deleteStatus($us_id);
	}
	return "ok";

}

$wgAjaxExportList [] = 'wfUpdateStatus';
function wfUpdateStatus($user_id, $user_name, $text, $date, $next_row) { 
	global $IP, $wgUser, $wgOut;
	
	//get database
	$dbr =& wfGetDB( DB_MASTER );
	
	//write new data to user_status
	$dbr->insert( '`user_status`',
	array(
		'us_user_id' => $user_id,
		'us_user_name' => $user_name,
		'us_text' => $text,
		'us_date' => $date,
		), __METHOD__
	);
	
	//grab all rows from user_status
	$sql = "SELECT us_user_id, us_user_name, us_text,UNIX_TIMESTAMP(us_date) as unix_time FROM user_status WHERE us_id = {$next_row}";
	$res = $dbr->query($sql);

	$x = 1;

	while ($row = $dbr->fetchObject( $res ) ) {
		$db_user_id = $row->us_user_id;
		$db_user_name = $row->us_user_name;
		$db_status_text = $row->us_text;
		$user_status_date = $row->unix_time;
		$avatar = new wAvatar($db_user_id,"ml");

		$output .= "<div class=\"user-status-row\">
			<img src=images/avatars/{$avatar->getAvatarImage()}\"/>
			<a href=\"index.php?title=User:{$db_user_name}\"><b>{$db_user_name}</b></a> {$db_status_text}
			<span class=\"user-status-date\">
				just added
			</span>
		</div>";

		$x++;
	}
	
	return $output;

}


?>