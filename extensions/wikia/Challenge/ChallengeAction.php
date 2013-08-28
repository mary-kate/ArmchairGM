<?php
$wgExtensionFunctions[] = "wfStats";

function wfStats() {
    global $wgParser ,$wgOut;
    $wgParser->setHook( "stats", "DisplayStats" );
}

function DisplayStats( $input ){
	return "";
	
}

$wgExtensionFunctions[] = 'wfSpecialChallengeAction';


function wfSpecialChallengeAction(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ChallengeAction extends SpecialPage {
	
		function ChallengeAction(){
			SpecialPage::SpecialPage("ChallengeAction");
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP;
			require_once("$IP/extensions/wikia/Challenge/ChallengeClass.php");
			require_once ("$IP/extensions/UserStats/UserStatsClass.php");
			$c = new Challenge();
			
			switch ($_GET["action"]) {
			case 1:
				$c->updateChallengeStatus($_POST["id"],$_POST["status"]);
			   break;
			case 2:
				//if ( $wgUser->isAllowed('protect') ) {
					$c->updateChallengeWinner($_POST["id"],$_POST["userid"]);
					$c->updateChallengeStatus($_POST["id"],3);
					
				//}
			   break;
				case 3:
					//update Stats for both users involved in challenge
					$stats = new UserStatsTrack(1,$_POST["loser_userid"], $_POST["loser_username"]);
					if($_POST["challenge_rate"]==1)$stats->incStatField("challenges_rating_positive");
					if($_POST["challenge_rate"]==-1)$stats->incStatField("challenges_rating_negative");
					
					$fname = 'ChallengeRate::addToDatabase';
					$dbw =& wfGetDB( DB_MASTER );
					$dbw->insert( '`challenge_rate`',
						array(
							'challenge_id' => $_POST["id"],
							'challenge_rate_submitter_user_id' => $wgUser->getID(),
							'challenge_rate_submitter_username' => $wgUser->getName(),
							'challenge_rate_user_id' => $_POST["loser_userid"],
							'challenge_rate_username' => $_POST["loser_username"],
							'challenge_rate_date' =>  date("Y-m-d H:i:s"),
							'challenge_rate_score' =>  $_POST["challenge_rate"],
							'challenge_rate_comment' =>  $_POST["rate_comment"]
						), $fname
					);
					
			   break;
			}
			$wgOut->setArticleBodyOnly(true);
		}
	}

	SpecialPage::addPage( new ChallengeAction );
	global $wgMessageCache,$wgOut;
	$wgMessageCache->addMessage( 'challengeaction', 'Challenge Standings' );
}

?>