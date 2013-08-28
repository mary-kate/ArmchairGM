<?php
$wgExtensionFunctions[] = 'wfSpecialChallengeView';


function wfSpecialChallengeView(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ChallengeView extends SpecialPage {
	
		function ChallengeView(){
			UnlistedSpecialPage::UnlistedSpecialPage("ChallengeView");
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest;
			$wgOut->setPagetitle( "View Challenge" );	
			$id = $_GET["id"];
			if($id == ""){
				$wgOut->addHTML("No challenge specified");
			}else{
		 		$wgOut->addHTML($this->displayChallenge($id));
			}
		}
	
		function displayChallenge($id){
			global $wgUser, $wgOut, $IP, $wgStyleVersion;
			
			$css = "<style>
				.challenge-title{
					font-weight:800;
					font-size:15px;
					color:#78BA5D;
					padding-bottom:10px;
				}
				.challenge-user-link{
					font-weight:800;
					font-size:14px;
				}
				.challenge-status-text{
					font-weight:800;
					font-size:14px;
					color:#666666;
				}	
				
				.challenge-form{
					font-size:12px;
					font-weight:400;
				}
				
				</style>";
				
			$wgOut->addHTML($css);
			
			$out = "";
			
			require_once("$IP/extensions/wikia/Challenge/ChallengeClass.php");
			
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Challenge/Challenge.js?{$wgStyleVersion}\"></script>\n");
			
			$c = new Challenge();
			$challenge = $c->getChallenge($id);
		
			$avatar1 = new wAvatar($challenge["user_id_1"],"l");
			$avatar2 = new wAvatar($challenge["user_id_2"],"l");
			$title1 = Title::makeTitle( NS_USER  , $challenge["user_name_1"] );
			$title2 = Title::makeTitle( NS_USER  , $challenge["user_name_2"] );
			
			$challenge_history_title = Title::makeTitle( NS_SPECIAL  , "ChallengeHistory"  ); 
			$challenge_user_title = Title::makeTitle( NS_SPECIAL  , "ChallengeUser"  ); 
			
			$out .=  "<table cellpadding=8 bgcolor=#eeeeee cellspacing=0 style='border:1px solid #cccccc'><tr>
				<td>
					<img src='images/avatars/{$avatar1->getAvatarImage()} alt='' border=''>
				</td>
				<td>
					<span class=\"challenge-user-title\"><a href=\"{$title1->getFullURL()}\" class=\"challenge-user-link\">{$title1->getText()}</a></span> ({$c->getUserChallengeRecord($challenge["user_id_1"])})
					<br><a href=\"{$challenge_history_title->getFullURL()}&user={$title1->getDBKey()}\" style=\"font-size:11px\">view challenge history</a>
					<br><a href=\"{$challenge_user_title->getFullURL()}&user={$title1->getDBKey()}\" style=\"font-size:11px\">issue challenge</a>
					</td>
				<td>
					<b>vs.</b> 
				</td>
				<td>
					<img src='images/avatars/{$avatar2->getAvatarImage()} alt='' border=''></td><td><span class=\"challenge-user-link\">
					<a href=\"{$title2->getFullURL()}\" class=\"challenge-user-title\">{$title2->getText()}</a> </span> ({$c->getUserChallengeRecord($challenge["user_id_2"])})
					<br><a href=\"{$challenge_history_title->getFullURL()}&user={$title2->getDBKey()}\" style=\"font-size:11px\">view challenge history</a>
					<br><a href=\"{$challenge_user_title->getFullURL()}&user={$title1->getDBKey()}\" style=\"font-size:11px\">issue challenge</a>
				</td>
			</tr>
			</table><br>";
			
			$out .= "<table>
					<tr>
						<td>
							<b>Event:</b> <span class=\"challenge-event\">{$challenge["info"]} [{$challenge["date"]}]</span>
							<br><b>{$challenge["user_name_1"]}'s description: </b><span class=challenge-description>{$challenge["description"]}</span>
						</td>
					</tr>
				</table>";
			
			 
			$out .= "</td></tr></table><br>
			
				<table cellpadding=0 cellspacing=0 ><tr>
					<td valign=top>
						<span class=\"challenge-title\">if {$challenge["user_name_1"]} wins, {$challenge["user_name_2"]} has to . . . </span>
						<table cellpadding=0 cellspacing=0 class=challenge-terms width=300><tr><td>{$challenge["win_terms"]}</td></tr></table><br>
					</td>
					<td width=20>&nbsp;</td>
					<td valign=top>
						<span class=\"challenge-title\">if {$challenge["user_name_2"]} wins, {$challenge["user_name_1"]} has to . . . </span>
						<table cellpadding=0 cellspacing=0 class=\"challenge-terms\" width=300><tr><td>{$challenge["lose_terms"]}</td></tr></table>
					</td>
				</tr></table>";
			
			if ( $wgUser->isAllowed('protect') && $challenge["user_id_2"] != $wgUser->getID() && $challenge["user_id_1"] != $wgUser->getID() ) {
				$out .= "<a href=javascript:challengeCancel({$challenge["id"]}) style='color:#990000'>Admin Cancel Challenge Due to Abuse</a>";
			}
			$out .= "<div style=\"border-bottom:1px solid #cccccc;width:800px;margin-bottom:15px;\"></div>
				<span class=\"challenge-title\">Challenge Status</span><br><div class=\"challenge-status-text\"><span id=\"challenge-status\">
			";
			
			switch ($challenge["status"]) {
			case 0:
				if($wgUser->getID() != $challenge["user_id_2"]){
					$out .= "Awaiting Acceptance";
				}else{
					$out .= "This challenge has been sent to you.  Please choose your response<br><br><select id=challenge_action>
							<option value=1>Accept</option>
							<option value=-1>Reject</option>
							<soption value=2>Counter Terms</soption>
							</select>
							<input type=hidden id=status value={$challenge["status"]}>
							<input type=hidden id=challenge_id value={$challenge["id"]}>
							<input type=button value=Submit onclick=javascript:challengeResponse()>";
				}
			   break;
			case 1:
				if ( 1==2){//!$wgUser->isAllowed('protect') || $challenge["user_id_1"] == $wgUser->getID() || $challenge["user_id_2"] == $wgUser->getID()) {
					 $out .= "In Progress -- Awaiting Completion of Event and Admin Approval";
				 }else{
					 $out .= "You are an admin, so you can pick the winner if the Event has been completed<br>Who won the bet?<br><br><select id=challenge_winner_userid>
					 	<option value={$challenge["user_id_1"]}>{$challenge["user_name_1"]}</option>
						<option value={$challenge["user_id_2"]}>{$challenge["user_name_2"]}</option>
						<option value=-1>push</option>
						</select>
						<input type=hidden id=status value={$challenge["status"]}>
						<input type=hidden id=challenge_id value={$challenge["id"]}>
						<input type=button value=Submit onclick=javascript:challengeApproval()>";
				 }
				 
			   break;
			case -1:
			  $out .=  "Rejected";
			   break;
			 case -2:
			  $out .=  "Removed due to violation of rules";
			   break;
			case 3:
			 	if($challenge["winner_user_id"] != -1){
					$out .= "Challenge won by <b>{$challenge["winner_user_name"]}</b><br><br>";
					if($challenge["rating"]){
						 $out .= "<span class=\"challenge-title\">Challenge Rating</span><br>
						 	by <b>{$challenge["winner_user_name"]}</b> 
							<br><br><b>rating</b>: <span class=\"challenge-rating-{$c->rating_names[$challenge["rating"]]}\">{$c->rating_names[$challenge["rating"]]}</span>
							<br><b>comment</b>: {$challenge["rating_comment"]}";
					}else{
						if( $wgUser->getID() == $challenge["winner_user_id"]  ){
							$out .= "<span class=\"challenge-title\">Challenge Rating</span><br>
								<span class=\"challenge-won\">You won the challenge!</span><br><br><span class=\"challenge-form\">Please rate the loser's end of the bargain</span><br><select id=challenge_rate>
								<option value=1>Positive</option>
								<option value=-1>Negative</option>
								<option value=0>Neutral</option>
								</select>
								<input type=hidden id=status value={$challenge["status"]}>
								<input type=hidden id=challenge_id value={$challenge["id"]}>";
							if($challenge["winner_user_id"] == $challenge["user_id_1"]){
								$loser_id = $challenge["user_id_2"];
								$loser_username = $challenge["user_name_2"];
							}else{
								$loser_id = $challenge["user_id_1"];
								$loser_username = $challenge["user_name_1"];
							}
							$out .= "<input type=hidden id=loser_userid value={$loser_id}>
								<input type=hidden id=loser_username value='{$loser_username}'>
							<br><br><span class=\"challenge-form\">Additional Comments (ex: He did a lousy job completing the task)</span><br>
								<textarea class='createbox' rows=2 cols=50 id=rate_comment></textarea><br><br>
								<input type=button value=Submit onclick=javascript:challengeRate()>
								";
						}else{
							$out .= "This challenge has not yet been rated by the winner";
						}
					}
				}else{
					$out .= "Challenge was a push!<br><br>";
				}
			   break;
			}

			$out .= "</span></div><span id=status2></span>";
			return $out;
		}

	}

	SpecialPage::addPage( new ChallengeView );
	global $wgMessageCache,$wgOut;
	$wgMessageCache->addMessage( 'challengeview', 'Challenge View' );
}

?>