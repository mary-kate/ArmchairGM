<?php

$wgExtensionFunctions[] = 'wfUserRelationshipAction';


function wfUserRelationshipAction(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class UserRelationshipAction extends SpecialPage {

  function UserRelationshipAction(){
    UnlistedSpecialPage::UnlistedSpecialPage("UserRelationshipAction");
  }

  function execute(){
	global $wgUser, $wgOut, $IP, $wgMessageCache; 
	
	require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
	$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserRelationship/UserRelationship.css?{$wgStyleVersion}\"/>\n");
	
	//language messages
	require_once ( "$IP/extensions/wikia/UserRelationship/UserRelationship.i18n.php" );
	foreach( efWikiaUserRelationship() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
	
	$rel = new UserRelationship($wgUser->getName() );
	
	if($_GET["action"] == 1 && $rel->verifyRelationshipRequest($_POST["id"]) == true ){
		$request = $rel->getRequest($_POST["id"]);
		$user_name_from = $request[0]["user_name_from"];
		$user_id_from = $request[0]["user_id_from"];
		$rel_type = strtolower($request[0]["type"]);
		
		$avatar = new wAvatar($user_id_from,"l");
		$avatar_img = $avatar->getAvatarURL();
		
		$rel->updateRelationshipRequestStatus($_POST["id"],$_POST["response"]);
		
		
		if($_POST["response"]==1)$rel->addRelationship($_POST["id"]);
		if($_POST["response"]==1) {
			echo "<div class=\"relationship-action\">
				{$avatar}
				{$user_name_from} has been added as your {$rel_type}
				<div class=\"cleared\"></div>
				<div class=\"relationship-buttons\">
			  		<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-main-page")."\" onclick=\"window.location='index.php?title=Main_Page'\"/>
			  		<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-your-profile")."\" onclick=\"window.location='index.php?title=User:{$wgUser->getName()}'\"/>
			  </div>
			</div>";
		} else {
			echo "<div class=\"relationship-action\">
				{$avatar}
				You have rejected {$user_name_from} as your {$rel_type}
				<div class=\"cleared\"></div>
				<div class=\"relationship-buttons\">
			  		<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-main-page")."\" onclick=\"window.location='index.php?title=Main_Page'\"/>
			  		<input type=\"button\" class=\"site-button\" value=\"".wfMsg("ur-your-profile")."\" onclick=\"window.location='index.php?title=User:{$wgUser->getName()}'\"/>
			  </div>
			</div>";
		}
		$rel->deleteRequest($_POST["id"]);
	} 
 	$wgOut->setArticleBodyOnly(true);
  }

}

 SpecialPage::addPage( new UserRelationshipAction );
 global $wgMessageCache,$wgOut;
 $wgMessageCache->addMessage( 'voteaction', 'just a test extension' );
 


}

?>
