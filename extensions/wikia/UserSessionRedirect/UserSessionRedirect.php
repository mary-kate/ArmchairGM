<?php
$wgExtensionFunctions[] = 'fnUserSessionRedirect';

function fnUserSessionRedirect(){
	global $wgUser, $wgOut, $wgRequest, $wgArticle, $wgSessionRedirectList, $wgMemc, $wgDBname;
	
	if(!$wgUser->isLoggedIn() && $wgRequest->getVal("title") != "Main_Page")return "";
	
	$session_count = UserSessionTrack::getCount();
	$session_count = 5;
	
	//found a redirect
	if( array_key_exists( $session_count , $wgSessionRedirectList ) && $_SESSION['wsUserSessionViewed'] != 1 ){
		
		//load user stats array
		$stats = new UserStats($wgUser->getID() ,$wgUser->getName() );
		$stats_data = $stats->getUserStats();
		
		//there exists a stat field for the type defined in the settings
		if( isset($stats_data[ $wgSessionRedirectList[$session_count]["type"] ])  ){
	
			//the user has a count of this stat below the threshold defined in the settings
			if(   $wgSessionRedirectList[$session_count]["on_type_count"]  > str_replace(",","",$stats_data[ $wgSessionRedirectList[$session_count]["type"] ]) ){
				
				//mark that the user viewed the latest redirect
				$_SESSION['wsUserSessionViewed'] = 1;
				
				$redirect = Title::makeTitle(NS_MAIN, $wgSessionRedirectList[$session_count]["redirect"]);
				//$wgOut->redirect( $redirect->getFullURL() );
			
			}
		}
		
	}
	return "";
	
}

?>