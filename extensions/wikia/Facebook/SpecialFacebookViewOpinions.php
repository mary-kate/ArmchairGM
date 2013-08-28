<?php

$wgExtensionFunctions[] = 'wfSpecialFacebookViewOpinions';


function wfSpecialFacebookViewOpinions(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class FacebookViewOpinions extends UnlistedSpecialPage {
	
		function FacebookViewOpinions(){
			UnlistedSpecialPage::UnlistedSpecialPage("FacebookViewOpinions");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP, $wgSitename;
			
			$wgOut->setArticleBodyOnly(true);
			
			require_once("$IP/extensions/ListPages/ListPagesClass.php");
			require_once "$IP/extensions/wikia/Facebook/appinclude.php";
			$facebook = new Facebook($appapikey, $appsecret);
			$user = $facebook->require_login();
			
			//catch the exception that gets thrown if the cookie has an invalid session_key in it
			try {
			  if (!$facebook->api_client->users_isAppAdded()) {
			    $facebook->redirect($facebook->get_add_url());
			  }
			} catch (Exception $ex) {
			  //this will clear cookies for your app and redirect them to a login prompt
			  
			  $facebook->set_user(null, null);
			  $facebook->redirect($appcallbackurl);
			}
			
			$facebook->api_client->fbml_refreshRefUrl("http://sports.box8.tpa.wikia-inc.com/index.php?title=Special:FacebookGetOpinions&id={$user}");
		
			$out = "<fb:ref url=\"http://sports.box8.tpa.wikia-inc.com/index.php?title=Special:FacebookGetOpinions&id={$user}\" />";
			
			$facebook->api_client->profile_setFBML($out, $user);
			
			$facebook->redirect($facebook->get_facebook_url() . '/profile.php');
		}
		
	}
	
	SpecialPage::addPage( new FacebookViewOpinions );
	global $wgMessageCache,$wgOut;
}

?>