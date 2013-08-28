<?php
$wgAjaxExportList [] = 'wfPollTitleExists';
function wfPollTitleExists($page_name){ 
	
	//construct page title object to convert to Database Key
	$page_title =  Title::makeTitle( NS_MAIN  , urldecode($page_name) );
	$db_key = $page_title->getDBKey();
	
	//Database key would be in page title if the page already exists
	$dbr =& wfGetDB( DB_MASTER );
	$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key , 'page_namespace'=>NS_POLL),"" );
	if ( $s !== false ) {
		return "Page exists";
	} else {
		return "OK";
	}
}

$wgAjaxExportList [] = 'wfPollVote';
function wfPollVote($poll_id,$choice_id){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/Poll/PollClass.php");
	 
	$p = new Poll();
	if(! $p->user_voted( $wgUser->getName(), $poll_id ) ){
		$p->add_poll_vote($poll_id,$choice_id);
	}
	return "ok";
}

$wgAjaxExportList [] = 'wfGetRandomPoll';
function wfGetRandomPoll(){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/Poll/PollClass.php");
	 
	$p = new Poll();

	$poll_page = $p->get_random_poll_url( $wgUser->getName() );
	return $poll_page;
}

$wgAjaxExportList [] = 'wfUpdatePollStatus';
function wfUpdatePollStatus($poll_id,$status){ 
	global $IP, $wgMemc, $wgUser;
	require_once("$IP/extensions/wikia/Poll/PollClass.php");
	 
	$p = new Poll();
	if($p->does_user_own_poll($wgUser->getID(),$poll_id) || $wgUser->isAllowed("protect") ){
		$p->update_poll_status($poll_id,$status );
		return "Status successfully changed";
	}else{
		return "error";
	}
}
?>