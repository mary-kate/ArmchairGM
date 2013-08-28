<?php
$wgHooks['AddNewAccount'][] = 'fnMailingListSubscribe';
 

function fnMailingListSubscribe(){
	global $wgUser;
	
	$dbr =& wfGetDB( DB_MASTER );
	$dbr->insert( '`user_mailing_list`',
	array(
		'um_user_id' => $wgUser->getID(),
		'um_user_name' => $wgUser->getName()
		), $fname
	);
	return true;
}

?>