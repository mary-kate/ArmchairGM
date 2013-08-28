<?php

$wgHooks['ArticleFromTitle'][] = 'wfMainPageFromTitle';

//ArticleFromTitle
//Calls MainPage instead of standard article
function wfMainPageFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgTitle, $wgStyleVersion, $wgMessageCache;
	
	if ( $wgTitle->getText() == "Main Page"  ) {
		
		require_once( "$IP/extensions/wikia/CustomMainPage/MainPage_Page.php" );
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/CustomMainPage/MainPage.css?{$wgStyleVersion}\"/>\n");
		
		require_once ( "$IP/extensions/wikia/CustomMainPage/MainPage.i18n.php" );
		foreach( efWikiaMainPage() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		$wgOut->addMeta ( 'verify-v1',"ZvYieNToniVzGWRHNdIvCSOrR7mmoDXlFLvjtXjWjZI=" );
		$article = new MainPage(&$title);
		
		
	}

	return true;
}
/*
//testing new hooks
$wgHooks['UserProfileBeginLeft'][] = 'wfUserProfileBeginTest';
function wfUserProfileBeginTest($user_profile) {
	global $wgOut;
	//$wgOut->addHTML("Cosby kids");
	return true;
}

$wgHooks['UserProfileBeginLeft'][] = 'wfUserProfilePoop';
function wfUserProfilePoop($user_profile) {
	global $wgOut;
	//$wgOut->addHTML("dropped off");
	return true;
}

//testing new hooks
$wgHooks['UserProfileEndLeft'][] = 'wfUserProfileBeginTest2';
function wfUserProfileBeginTest2($user_profile) {
	global $wgOut;
	//$wgOut->addHTML("this was inserted at the left end from the hook [profile:{$user_profile->user_name}]");
	return true;
}
//testing new hooks
$wgHooks['UserProfileBeginRight'][] = 'wfUserProfileBeginTest3';
function wfUserProfileBeginTest3($user_profile) {
	global $wgOut;
	//$wgOut->addHTML("this was inserted at the right beginning from the hook [profile:{$user_profile->user_name}]");
	return true;
}
//testing new hooks
$wgHooks['UserProfileEndRight'][] = 'wfUserProfileBeginTest4';
function wfUserProfileBeginTest4($user_profile) {
	global $wgOut;
	//$wgOut->addHTML("this was inserted at the right end from the hook [profile:{$user_profile->user_name}]");
	return true;
}

//read in localisation messages
function wfUserProfileReadLang(){
	global $wgMessageCache, $IP;
	require_once ( "$IP/extensions/wikia/UserProfile/UserProfile.i18n.php" );
	foreach( efWikiaUserProfile() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}
*/
?>