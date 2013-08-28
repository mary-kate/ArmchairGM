<?php

$wgExtensionFunctions[] = 'wfSpecialFacebookAddViewOpinions';


function wfSpecialFacebookAddViewOpinions(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class FacebookAddViewOpinions extends UnlistedSpecialPage {
	
		function FacebookAddViewOpinions(){
			UnlistedSpecialPage::UnlistedSpecialPage("FacebookAddViewOpinions");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP;
			
			$wgOut->setPagetitle( "Add Facebook Application" );
		
			require_once "$IP/extensions/wikia/Facebook/appinclude.php";
			$facebook = new Facebook($appapikey, $appsecret);
			$user = $facebook->require_login();
			//$facebook->api_client->auth_getSession("HLV1KI");
			//echo $facebook->api_client->session_key;
			try{
				$facebook2 = new Facebook($appapikey, $appsecret);
				$facebook2->api_client->session_key = $infinite_session_key;
				$fql_array = $facebook2->api_client->fql_query("SELECT name, pic FROM user WHERE uid=$user");
				$name = $fql_array[0]["name"];
			}catch(exception $ex){
				//$wgOut->addHTML("An error has occured linking your Facebook account.");
				//return false;
			}
			
			 
			if($wgUser->getID() == 0 ){
				$wgOut->addHTML("You must be logged in to link your Facebook account. If you don't have an account yet, you will first have to register.");
				return false;
			}
				
			if($wgRequest->wasPosted()){
				$dbr =& wfGetDB( DB_MASTER );
				$fname = 'fb_link_view_opinions::addToDatabase';
				$dbr->insert( '`fb_link_view_opinions`',
				array(
					'fb_user_id_wikia' => $wgUser->getID(),
					'fb_user_name_wikia' => $wgUser->getName(),
					'fb_user_id' => $user,
					'fb_user_session_key' => $facebook->api_client->session_key,
					'fb_link_date' => date("Y-m-d H:i:s")
					), $fname
				);
				$facebook->set_user(null, null);
				$facebook->redirect($appcallbackurl);
			}else{
				
				$dbr =& wfGetDB( DB_MASTER );
				$s = $dbr->selectRow( '`fb_link_view_opinions`', array( 'fb_user_id_wikia' ), array( 'fb_user_id_wikia' => $wgUser->getID() ), $fname );
				if ( $s === false ) {
					
					
					$out = "<div class=\"fb-install\">You are about to link the wikia account: <b>{$wgUser->getName()}</b>  to the Facebook account for <b>{$name}</b>.</div>
					<p>
					<div class=\"fb-form\"><form action=\"\" method=\"POST\" name=\"fb_form\"><input type=\"hidden\" name=\"auth\" value\"" . $wgRequest->getVal("auth_token") . "\"><input type=\"button\" value=\"Link Wikia and Facebook accounts\" onclick=\"document.fb_form.submit()\">
					</form></div>
					";
					$wgOut->addHTML($out);			
				}
			}
	
		 
		}
		
	}
	
	SpecialPage::addPage( new FacebookAddViewOpinions );
	global $wgMessageCache,$wgOut;
}

?>