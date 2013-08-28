<?php

$wgExtensionFunctions[] = 'wfSpecialFacebookRemoveViewOpinions';


function wfSpecialFacebookRemoveViewOpinions(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class FacebookRemoveViewOpinions extends UnlistedSpecialPage {
	
		function FacebookRemoveViewOpinions(){
			UnlistedSpecialPage::UnlistedSpecialPage("FacebookRemoveViewOpinions");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP;
			
			$wgOut->setPagetitle( "Add Facebook Application" );
		
			require_once "$IP/extensions/wikia/Facebook/appinclude.php";
			$facebook = new Facebook($appapikey, $appsecret);
			$user = $facebook->require_login();
			
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "DELETE FROM fb_link_view_opinions WHERE fb_user_id={$user}";
			$res = $dbr->query($sql);
			$facebook->redirect($facebook->get_facebook_url() . '/profile.php');
		 
		}
		
	}
	
	SpecialPage::addPage( new FacebookRemoveViewOpinions );
	global $wgMessageCache,$wgOut;
}

?>