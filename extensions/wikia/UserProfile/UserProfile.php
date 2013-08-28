<?php
//GLOBAL VIDEO NAMESPACE REFERENCE
define( 'NS_USER_PROFILE', 202 );
define( 'NS_USER_WIKI', 200 );

//default setup for displaying sections
$wgUserProfileDisplay['friends'] = true;
$wgUserProfileDisplay['foes'] = true;
$wgUserProfileDisplay['gifts'] = true;
$wgUserProfileDisplay['awards'] = true;
$wgUserProfileDisplay['activity'] = true;
$wgUserProfileDisplay['profile'] = true;
$wgUserProfileDisplay['board'] = true;
$wgUserProfileDisplay['pictures'] = true;
$wgUserProfileDisplay['games'] = true;
$wgUserProfileDisplay['stats'] = true;
$wgUserProfileDisplay['interests'] = true;
$wgUserProfileDisplay['custom'] = true;
$wgUserProfileDisplay['articles'] = true;
$wgUserProfileDisplay['personal'] = true;
$wgHooks['ArticleFromTitle'][] = 'wfUserProfileFromTitle';

//ArticleFromTitle
//Calls UserProfilePage instead of standard article
function wfUserProfileFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgTitle, $wgSupressPageTitle,$wgSupressSubTitle, $wgMemc, $wgUserPageChoice, $wgParser;

	if ( strpos($title->getText(), "/" ) === false && NS_USER == $title->getNamespace() || NS_USER_PROFILE == $title->getNamespace()  ) {
		if( !$wgRequest->getVal("action") ){
			$wgSupressPageTitle = true;
		}

		$wgSupressPageTitle = true;
		require_once( "$IP/extensions/wikia/UserProfile/UserProfilePage.php" );
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		if( $wgUserPageChoice ){
			
			$profile = new UserProfile( $title->getText() );
			$profile_data = $profile->getProfile();
			
			//If they want regular page, ignore this hook
			if( $profile_data["user_id"] && $profile_data["user_page_type"] == 0 ){
				$show_user_page = true;
			}
		}
		
		if( ! $show_user_page ){
			//prevents editing of userpage
			if( $wgTitle->getText() == $wgUser->getName() && $wgRequest->getVal("action") == "edit" ){
				$wgOut->redirect( Title::makeTitle(NS_SPECIAL, "UpdateProfile")->getFullURL() );
			}
		}else{
			$wgOut->enableClientCache(false);
			$wgParser->disableCache();
		}
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserProfile/UserProfile.css?{$wgStyleVersion}\"/>\n");
			
		$article = new UserProfilePage(&$title);
		
	}
	
	return true;
}

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

$wgExtensionFunctions[] = 'wfUserProfileReadLang';

//read in localisation messages
function wfUserProfileReadLang(){
	global $wgMessageCache, $IP;
	require_once ( "$IP/extensions/wikia/UserProfile/UserProfile.i18n.php" );
	foreach( efWikiaUserProfile() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}
?>