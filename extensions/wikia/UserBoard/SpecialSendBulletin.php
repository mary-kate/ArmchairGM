<?php

$wgExtensionFunctions[] = 'wfSpecialSendBulletin2';

function wfSpecialSendBulletin2(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class SendBulletin extends SpecialPage {

	
	function SendBulletin(){
		UnlistedSpecialPage::UnlistedSpecialPage("SendBulletin");
	}
	
	function execute($user_name){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $IP;

		require_once("$IP/extensions/wikia/UserBoard/UserBoardClass.php");
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		
		$user_id = User::idFromName($user_name);
		if($user_id==0){
			$wgOut->addHTML("No username specified");
			return "";
		}
		
		$message = "We've rolled out a new feature today -- the [[Special:QuizGameHome|Never Ending Quiz Game]].   Tons of sports questions -- answer them quickly and you can earn up to 30 points per question.   '''[http://www.armchairgm.com/index.php?title=Special:QuizGameHome&questionGameAction=launchGame Start playing]'''!\n\n\nIf you have some knowledge to share, create a question -- and earn 35 points each!  '''[http://www.armchairgm.com/index.php?title=Special:QuizGameHome&questionGameAction=createForm Click here to create a question]'''!";
		
		
		$rel = new UserRelationship($user_name);
		$b = new UserBoard();
		
		$friends_count = 0;
		$relationships = $rel->getRelationshipList(1);
		foreach($relationships as $relationship){
			$b->sendBoardMessage($user_id,$user_name,$relationship["user_id"],$relationship["user_name"], $message, 1);
			$output .= " {$user_name} ({$user_id}) is sending to {$relationship["user_name"]} ({$relationship["user_id"]}) - is a friend<br>";
			$friends_count++;
		}
		$foe_count=0;
		$relationships = $rel->getRelationshipList(2);
		foreach($relationships as $relationship){
			$b->sendBoardMessage($user_id,$user_name,$relationship["user_id"],$relationship["user_name"], $message, 1);
			$output .= " {$user_name} ({$user_id}) is sending to {$relationship["user_name"]} ({$relationship["user_id"]}) is a foe<br>";
			$foe_count++;
		}
		
		$output .= "Sent to {$friends_count} friends and {$foe_count} foes";
		$wgOut->addHTML($output);

		
	}
	

	
	
}


SpecialPage::addPage( new SendBulletin );



}

?>
