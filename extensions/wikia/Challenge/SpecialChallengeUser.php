<?php

$wgExtensionFunctions[] = 'wfSpecialChallengeUser';


function wfSpecialChallengeUser(){
	global $wgUser,$IP,$wgOut;
	include_once("includes/SpecialPage.php");


	class ChallengeUser extends SpecialPage {
	
		function ChallengeUser(){
			UnlistedSpecialPage::UnlistedSpecialPage("ChallengeUser");
		}
		
		function execute(){
			
			global $wgUser, $wgOut, $wgRequest, $IP;
		
						$css = "<style>
			 
						.challenge-user-top{
							padding-bottom:15px;
						}
			.challenge-user-title{
				font-weight:800;
				font-size:15px;
				color:#78BA5D;
				padding-bottom:4px;
			}
			
			.challenge-field{
				padding-bottom:10px;
			}
			
			.challenge-label{
				color:#666666;
			}
			
			.challenge-user-info{
				width:400px;
				padding-bottom:20px;
			}
			
			.challenge-info{
				width:200px;
				float:right;
				padding:5px;
				border:1px solid #cccccc;
			}
			.challenge-user-top a{
				font-size:12px;
				text-decoration:none;
				font-weight:800;
			}
			.challenge-user-avatar{
				width:75px;
				float:left;
			}
			.challenge-user-stats{
				float:right;
			}
			</style>";
			$wgOut->addHTML($css);
			
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Challenge/Challenge.js\"></script>\n");
			
			require_once("$IP/extensions/wikia/Challenge/ChallengeClass.php");
			
			$usertitle = Title::newFromDBkey($wgRequest->getVal('user'));
			if(!$usertitle){
				$wgOut->addHTML($wgOut->addHTML($this->displayFormNoUser()));
				return false;	
			}
			
			$this->user_name_to = $usertitle->getText();
			$this->user_id_to = User::idFromName($this->user_name_to);

			
			if($wgUser->getID()== $this->user_id_to){
				$wgOut->setPagetitle( "Woops!" );
				$wgOut->addHTML("You cannot challenge yourself");
				
			}else if($this->user_id_to == 0){
				$wgOut->setPagetitle( "Woops!" );
				$wgOut->addHTML("The user you are trying to challenge does not exist.");
				
			}else if($wgUser->getID() == 0){
				$wgOut->setPagetitle( "Woops!" );
				$wgOut->addHTML("You must be logged in to challenge other users.");
				
			}else{
				
		 		if(count($_POST) && $_SESSION["alreadysubmitted"] == false){
					
					$_SESSION["alreadysubmitted"] = true;
					$c = new Challenge();
					$c->AddChallenge($this->user_name_to,$_POST["info"], $_POST["date"], $_POST["description"], $_POST["win"], $_POST["lose"]);
					
					$out .= $wgOut->setPagetitle( "You Have Issued a challenge to {$this->user_name_to}!" );
					
					$out .= '<div class="challenge-links">';
			                  //$out .= "<a href=\"index.php?title=User:{$this->user_name_to}\">< {$this->user_name_to}'s User Page</a>";
			                 // $out .= " - <a href=\"index.php?title=Special:ViewGifts&user={$this->user_name_to}\">View All of {$this->user_name_to}'s Gifts</a>";
			                  if ( $wgUser->isLoggedIn() ) {
			                   // $out .= " - <a href=\"index.php?title=Special:ViewGifts&user={$wgUser->getName()}\">View All of Your Gifts</a>";
			                  }
			                $out .= "</div>";
					
					$out .= "<div class=\"challenge-sent-message\">The challenge has been sent, and is awaiting acceptance by {$this->user_name_to}</div>";
					
					$out .= "<div class=\"cleared\"></div>";
					
					$wgOut->addHTML($out);
				}else{
					$_SESSION["alreadysubmitted"] = false;
					$wgOut->addHTML($this->displayForm());
					
				}
				
			}
		}
	
		function displayFormNoUser(){
			global $wgUser, $wgOut, $wgRequest, $wgFriendingEnabled, $IP;
			$output =  "";
			$output .= '<form action="" method="GET" enctype="multipart/form-data" name="gift">
			<input type="hidden" name="title" value="' .$wgRequest->getVal("title") . '">';
			$output .= $wgOut->setPagetitle( "Who would you like to challenge?" );
			$output .= '<div class="give-gift-message">Challenges are a fun way to put your wiki where your mouth is!</div>';
			if($wgFriendingEnabled){
				require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
				$rel = new UserRelationship($wgUser->getName());
				$friends = $rel->getRelationshipList(1);
				if($friends){
					$output .= '<div class="give-gift-title">Select from your list of friends</div>
						<div class="give-gift-selectbox">
						<select onchange="javascript:chooseFriend(this.value)">';
						  $output .= "<option value=\"#\" selected>select a friend</option>";
						foreach($friends as $friend){
							$output .= "<option value=\"{$friend["user_name"]}\">{$friend["user_name"]}</option>";
						}
						$output .= "</select>
						</div>
						";	
				}
			}
			
			$output .= '<p style="margin:10px 0px 10px 0px;">or</p>';
			$output .= '<div class="give-gift-title">If you know the name of the user, type it in below</div>';
			$output .= '<div class="give-gift-textbox">
			  <input type="text" width="85" name="user" value="">
			  <input class="give-gift-button" type="button" value="start challenge" onclick="document.gift.submit()">
			</div>';
			
			return $output;
		}
		
		function displayForm(){
			global $wgUser, $wgOut;
			$wgOut->setPagetitle( "Challenge user {$this->user_name_to}" );
			
			$user_title = Title::makeTitle( NS_USER  , $this->user_name_to );
			$challenge_history_title = Title::makeTitle( NS_SPECIAL  , "ChallengeHistory"  ); 
			$avatar = new wAvatar($this->user_id_to,"l");
			
			$form = "";
			$form .= "
				<div class=\"challenge-user-top\">
					<a href=\"{$user_title->getFullURL()}\">View {$this->user_name_to}'s Userpage</a> - 
					<a href=\"{$challenge_history_title->getFullURL()}&user=" . $this->user_name_to . "\">Complete Challenge History</a> -
					<a href=\"{$challenge_history_title->getFullURL()}\">View All Challenges</a>
					</div>";
			
			$pos = Challenge::getUserFeedbackScoreByType(1,$this->user_id_to);
			$neg = Challenge::getUserFeedbackScoreByType(-1,$this->user_id_to);
			$neu = Challenge::getUserFeedbackScoreByType(0,$this->user_id_to);
			$total = ($pos + $neg + $neu);
		
		    
			$form .= "
				<div class=\"challenge-info\">
					<div class=\"challenge-user-title\">What are Challenges?</div>
					Challenges are a great way to prove your sports knowledge to the community, as well as get others to build your content.
				</div>
				<div class=\"challenge-user-info\">
					<div class=\"challenge-user-avatar\"><img src='images/avatars/{$avatar->getAvatarImage()} alt='' border='' align=\"middle\"></div>
					<div class=\"challenge-user-stats\">
						<div class=\"challenge-user-title\">{$this->user_name_to}'s Challenge Stats</div>
						<div class=\"challenge-user-record\">record: <b>" . Challenge::getUserChallengeRecord($this->user_id_to) . "</b></div>
						<div class=\"challenge-user-feedback\">feedback score: <b>" . $total . "</b> (" . $pos . " positive | " . $neg . " negative | " . $neu . " neutral)</div>
					</div>
				</div>	
					<div class=\"cleared\"></div>
				";
		 
		
		   $form .= "	<div class=\"challenge-user-title\">Enter Challenge Information</div>
		   		<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" name=\"challenge\">
		   		<div class=\"challenge-field\">
					<div class=\"challenge-label\">the event (ex: Giants vs. Eagles)</div>
					<div class=\"challenge-form\">
						<input type=\"text\" class=\"createbox\" size=\"35\" name=\"info\" id=\"info\" value=\"\" />
					</div>
				</div>
				<div class=\"challenge-field\">
					<div class=\"challenge-label\">the event date (mm/dd/yyyy)</div>
					<div class=\"challenge-form\">
						<input type=\"text\" class=\"createbox\" size=\"10\" name=\"date\" id=\"date\" value=\"\" />
					</div>
				</div>
				<div class=\"challenge-field\">
					<div class=\"challenge-label\">description (ex: I'm taking the Eagles w/ the spread (+3))</div>
					<div class=\"challenge-form\">
						<input type=\"text\" class=\"createbox\" size=\"50\" name=\"description\" id=\"description\" value=\"\" />
					</div>
				</div>
				
				<div class=\"challenge-field\">
					<div class=\"challenge-label\">win terms (ex: My opponent must fill out the 1991 roster page)</div>
					<div class=\"challenge-form\">
						<textarea class=\"createbox\" name=\"win\" id=\"win\" rows=\"2\" cols=\"50\"></textarea>
					</div>
				</div>
				
				<div class=\"challenge-field\">
					<div class=\"challenge-label\">lose terms (ex: I am willing to edit the 2005 team results page)</div>
					<div class=\"challenge-form\">
						<textarea class=\"createbox\" name=\"lose\" id=\"lose\" rows=\"2\" cols=\"50\"></textarea>
					</div>
				</div>
				<div class=\"challenge-buttons\">
				<input type=\"button\" class=\"createbox\" value=\"submit\" size=\"20\" onclick=\"javascript:challengeSend()\" />
				</div>
				<div class=\"cleared\"></div>
			</form>";
	return $form;
	}
	
	}

	SpecialPage::addPage( new ChallengeUser );
}

?>