<?php

$wgExtensionFunctions[] = 'wfSpecialViewRelationshipRequests';


function wfSpecialViewRelationshipRequests(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ViewRelationshipRequests extends SpecialPage {
	
		function ViewRelationshipRequests(){
			SpecialPage::SpecialPage("ViewRelationshipRequests");
		}
		
		function execute(){
			
			global $wgUser, $wgOut, $wgTitle, $wgRequest, $IP, $wgMessageCache, $wgStyleVersion;
		


			/*/
			/* Redirect Non-logged in users to Login Page
			/* It will automatically return them to the ViewRelationshipRequests page
			/*/
			if($wgUser->getID() == 0){
				$wgOut->setPagetitle( "Woops!" );
				$login =  Title::makeTitle(NS_SPECIAL,"UserLogin");
				$wgOut->redirect( $login->getFullURL('returnto=Special:ViewRelationshipRequests') );
				return false;
			}

			global $wgMemc;
			$key = wfMemcKey( 'user_relationship', 'open_request', 1, $wgUser->getID() );
			$wgMemc->delete($key);
			
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/UserRelationship/UserRelationship.js?{$wgStyleVersion}\"></script>\n");
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserRelationship/UserRelationship.css?{$wgStyleVersion}\"/>\n");
			require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
			
			//language messages
			require_once ( "$IP/extensions/wikia/UserRelationship/UserRelationship.i18n.php" );
			foreach( efWikiaUserRelationship() as $lang => $messages ){
				$wgMessageCache->addMessages( $messages, $lang );
			}
			
			$rel = new UserRelationship($wgUser->getName() );
			$friend_request_count = $rel->getOpenRequestCount($wgUser->getID(),1);
		    $foe_request_count = $rel->getOpenRequestCount($wgUser->getID(),2);
			
		
				$output = "";
				$plural="";
				
				$output .= $wgOut->setPagetitle( wfMsg("ur-requests-title") );
				$requests = $rel->getRequestList(0);
				
				if ($requests) {
					
					foreach ($requests as $request) {
						
						if ($request["type"]=="Foe") {
							$label = wfMsg("ur-foe");
						} else {
							$label = wfMsg("ur-friend");
						}
						
						$user_from =  Title::makeTitle(NS_USER,$request["user_name_from"]);
						$avatar = new wAvatar($request["user_id_from"],"l");
						$avatar_img = $avatar->getAvatarURL();
						
						$message = $wgOut->parse( trim($request["message"]), false );
						
						$output .= "<div class=\"relationship-action black-text\" id=\"request_action_{$request["id"]}\">
						  	{$avatar_img}
							".wfMsg('ur-requests-message', $user_from->escapeFullURL(), $request["user_name_from"], $label);
							if ($request["message"]) {
								$output .= "<div class=\"relationship-message\">\"{$message}\"</div>";
							}
							$output .= "<div class=\"cleared\"></div>
							<div class=\"relationship-buttons\">
								<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-accept")."\" onclick=\"javascript:requestResponse(1,{$request["id"]})\"> 
								<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-reject")."\" onclick=\"javascript:requestResponse(-1,{$request["id"]})\">
							</div>
						</div>";
								
					}
					
				} else {
					
					$invite_link = Title::makeTitle(NS_SPECIAL, "InviteContacts");
					$output = wfMsg("ur-no-request-message", $invite_link->escapeFullURL());	
				}
				
				$wgOut->addHTML($output);
				
			
		}
	}

	SpecialPage::addPage( new ViewRelationshipRequests );
	global $wgMessageCache,$wgOut;
	$wgMessageCache->addMessage( 'viewrelationshiprequests', 'View Relationship Requests' );
}

?>
