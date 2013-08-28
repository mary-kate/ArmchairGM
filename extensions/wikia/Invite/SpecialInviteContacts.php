<?php

$wgExtensionFunctions[] = 'wfSpecialInviteContacts';
$wgExtensionFunctions[] = 'wfInviteContactsReadLang';

	//read in localisation messages
function wfInviteContactsReadLang(){
	//global $wgMessageCache, $IP, $wgPickGameDirectory;
	global $wgMessageCache, $IP;
	$wgInviteContactsDirectory = "{$IP}/extensions/wikia/Invite";
	require_once ( "$wgInviteContactsDirectory/SpecialInviteContacts.i18n.php" );
	foreach( efWikiaInviteContacts() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}

function wfSpecialInviteContacts(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class InviteContacts extends UnlistedSpecialPage {
	
		function InviteContacts(){
			UnlistedSpecialPage::UnlistedSpecialPage("InviteContacts");
		}
		


		function execute(){
			global $wgUser, $wgOut, $wgStyleVersion, $wgRequest,$wgSitename,$wgEmailFrom, $wgServer; 
			
			/*/
			/* Redirect Non-logged in users to Login Page
			/* It will automatically return them to the ViewGifts page
			/*/
			if ($wgUser->getID() == 0) {
				$login =  Title::makeTitle( NS_SPECIAL  , "UserLogin"  );
				$wgOut->redirect( $login->getFullURL() . "&returnto=Special:InviteContacts" );
				return false;
			}
			
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Invite/invite.css?{$wgStyleVersion}\"/>\n");

			
			if($wgEmailFrom) {
				$from = $wgEmailFrom;
			}else{
				$from = wfMsgForContent( 'invite_email_from');
			}
			$register =  Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
			
			if(count($_POST) && $_SESSION["alreadysubmitted"] == false){
				$_SESSION["alreadysubmitted"] = true;
				
				$html_format = "no";  //IMPORTANT: change this to "yes"  ONLY if you are sending html format email
				$title = Title::makeTitle( NS_USER , $wgUser->getName()  );
				$user_label = $wgUser->getRealName();
				if(!trim($user_label))$user_label = $wgUser->getName();
				
				$subject = wfMsg( 'invite_subject',
					$user_label
					);

				
				$body = wfMsg( 'invite_body',
					$_POST['sendersemail'],
					$user_label,
					$title->getFullURL(),
					$register->getFullURL() . "&from=1&referral=" . urlencode($title->getDBKey())
					);


$message = <<<EOF

$body

EOF;
				
				$sendersemail = $_POST['sendersemail'];
				
				$confirm = "";
				foreach($_POST['list'] as $to) {
					$confirm .= "<div class=\"invite-email-sent\">$to</div>";
					if ($html_format == "yes"){
						$headers = "From: $from\n";
						$headers .= "Reply-To: $from\n";
						$headers .= "Return-Path: $from\n";
						$headers .= "MIME-Version: 1.0\n";
						$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
						mail($to,$subject,$message,$headers);
					}else{
						mail($to, $subject, $message, "From: $from\r\nReply-To:$sendersemail");
					}
				}
				
				
				$mail = new UserEmailTrack($wgUser->getID(),$wgUser->getName());
				$mail->track_email(1,count($_POST['list']));
					
				$wgOut->setPagetitle( wfMsgForContent( 'invite_page_title' ) );
				
				$output = "";
				$output .= "<div><span class=\"profile-on\">" . wfMsgForContent( 'invite_email_list_header' )  . "</span></div>
						<p>
						<div>" . $confirm."</div>";
				if($wgRequest->getVal("from") == "register"){
					$output .= "<p><p><div ><a href=\"" . $wgUser->getUserPage()->getFullURL() . "\" style=\"font-size:18px;font-weight:800;\">" . wfMsgForContent( 'invite_go_to_profile' )  . "</a></div>";
				}
				
				$wgOut->addHTML($output);
			}else{
				$_SESSION["alreadysubmitted"] = false;
				
				$waiting_msg = "<script type=\"text/javascript\"> var __contact_importer_waiting_message__ = \"" . wfMsgForContent( 'contact_importer_waiting_message' ) . "\";</script>";
				$wgOut->addScript($waiting_msg);
				
				$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Invite/GetContacts.js?{$wgStyleVersion}\"></script>\n");
				$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/getmycontacts/js/ahah.js?{$wgStyleVersion}\"></script>\n");
			
				$wgOut->setPagetitle( wfMsgForContent( 'invite_add_friends_page_title', $wgSitename ) );
			
				// GET NETWORK TO IMPORT FROM
				$get = $_GET["domain"];
				if (empty($get)){
					$script = "mygmail.php";
					$img = "mygmail.gif";
				}else{
					$script = $get.'.php';
					$img = $get.'.gif';
				}
				
				if($get=="myoutlook" || $get=="myexpress" || $get=="mythunderbird"){
					$useCSV = true;
				}
				
				if($useCSV)$formEnc = " enctype=\"multipart/form-data\" ";
				
				if($wgRequest->getVal("from") == "register"){
					if ( isset( $_COOKIE['sports_sid'] ) ) {
						$sport_id = $_COOKIE['sports_sid'];
						$team_id = $_COOKIE['sports_tid'];
						if($team_id){
							$team = SportsTeams::getTeam($team_id);
							$network = $team["name"];
						}else{
							$sport = SportsTeams::getSport($sport_id);
							$network = $sport["name"];
						}
						$wgOut->setPagetitle( wfMsgForContent( 'invite_network_fans', $network) );
					
					}
					
				}
				
					
				
				$invite_message = wfMsgForContent( 'invite_message', $wgSitename) . "<br/>
						<b>" . wfMsgForContent( 'invite_privacy_message' ) . "</b>";
				
					

				if ($wgRequest->getVal("from") !== "register") {
					$out .= "
					<div class=\"invite-message\">
						{$invite_message}
					</div>";
				} else {
					$out .= "<div class=\"invite-message\">
						<b>" . wfMsgForContent( 'invite_privacy_message' ) . "</b>
					</div>";
				}
					
					$out .= "<div id=\"target\">";
				if($useCSV != true){
				$out .= "<div class=\"invite-left\">";
					$out .= "<div class=\"invite-icons\">
							<img src=\"/extensions/wikia/getmycontacts/images/myyahoo.gif\" border=\"0\" alt=\"Yahoo!\">
							<img src=\"/extensions/wikia/getmycontacts/images/mygmail.gif\" border=\"0\" alt=\"Gmail\">
							<img src=\"/extensions/wikia/getmycontacts/images/myhotmail.gif\" border=\"0\" alt=\"Hotmail\">
							<img src=\"/extensions/wikia/getmycontacts/images/myaol.gif\" border=\"0\" alt=\"AOL\">";					
					$out .= "</div>";
					$out .= "<div class=\"invite-form\">";
					$out .= "<form name=emailform action=\"javascript:submit('{$wgServer}/extensions/wikia/getmycontacts/{$script}', 'POST');\" {$formEnc}  method=post onSubmit=\"return getMailAccount(this.username.value);\">";
							$out .= "<p class=\"invite-form-title\">" . wfMsgForContent( 'invite_form_email' ) . "</p>";
							$out .= "<p class=\"invite-form-input\"><input type=\"text\" name=\"username\" size=\"34\" value=\"{$wgUser->getEmail()}\"></p>";
							$out .= "<div class=\"cleared\"></div>";
							$out .= "<p class=\"invite-form-title\">" . wfMsgForContent( 'invite_form_password' ) . "</p>";
							$out .= "<p class=\"invite-form-input\"><input type=\"password\" name=\"password\" size=\"34\"></p>";
							$out .= "<div class=\"cleared\"></div>";
							$out .= "<p><input type=\"submit\" class=\"site-button\" value=\"" . wfMsgForContent( 'invite_btn_addfriends' ) . "\"></p>";
						$out .= "</form>";
					$out .= "</div>";
					$out .= "<p>" . wfMsgForContent( 'invite_no_webmail' ) . "  <a href=\"" . Title::makeTitle(NS_SPECIAL, "InviteEmail" . $which)->escapeFullUrl() . "\">" . wfMsgForContent( 'invite_click_here' ) . "</a>";
				$out .= "</div>";
				//$out .= "<div class=\"invite-right\">";
				        //$out .= "<h1>Don't have webmail? No problem, upload your contacts file!</h1>";
					//$out .= "<p class=\"invite-right-image\">
							//<a href=\"index.php?title=Special:InviteContactsCSV\"><img src=\"extensions/wikia/getmycontacts/images/myoutlook.gif\" border=\"0\"></a>
							//<a href=\"index.php?title=Special:InviteContactsCSV\"><img src=\"extensions/wikia/getmycontacts/images/mythunderbird.gif\" border=\"0\"></a>
						//</p>";
					//$out .= "<div class=\"cleared\"></div>";
					//$out .= "<p  align=\"center\"><input type=\"button\" class=\"site-button\" value=\"Upload Your Contacts\" onclick=\"window.location='index.php?title=Special:InviteContactsCSV'\"/></p>";
				//$out .= "</div>";
				$out .= "<div class=\"cleared\"></div>";
				
				if($wgRequest->getVal("from") == "register"){
					$out .= "<div style=\"margin-top:10px;\"><a href=\"" . $wgUser->getUserPage()->getFullURL() . "\" style=\"font-size:10px;\">" . wfMsgForContent( 'invite_skip_step' ) . "</a></div>";
				}
				
				} else {
					$out .= "<div class=\"invite-left\">";
						$out .= "<div class=\"invite-icons\">
								<img src=\"extensions/wikia/getmycontacts/images/myoutlook.gif\" border=\"0\" alt=\"Outlook\">
								<img src=\"extensions/wikia/getmycontacts/images/mythunderbird.gif\" border=\"0\" alt=\"Thunderbird\">
							</div>
							<div class=\"invite-form\">
								<form name=emailform action=\"javascript:submit('{$wgServer}/extensions/wikia/getmycontacts/{$script}', 'POST');\" {$formEnc}  method=post>
								<p>" . wfMsgForContent( 'invite_csv_size_limit' ) . "</p>
								<p class=\"invite-form-title\">" . wfMsgForContent( 'invite_csv_select_file' ) . "</p>
								<p class=\"invite-form-input\"><input name=\"ufile\" type=\"file\" id=\"ufile\" size=\"28\" /></p>
								<p><input type=\"submit\" class=\"site-button\" value=\"" . wfMsgForContent( 'invite_csv_btn_upload' ) . "\"></p>
							</div>";
					$out .= "</div>";
					$out .= "<div class=\"invite-right\">";
				        $out .= "<h1>" . wfMsgForContent( 'invite_csv_have_webmail' ) . "</h1>";
					$out .= "<p class=\"invite-right-image\">
							<a href=\"" . Title::makeTitle(NS_SPECIAL, "InviteContacts" . $which)->escapeFullUrl() . "\"><img src=\"extensions/wikia/getmycontacts/images/myyahoo.gif\" border=\"0\" alt=\"Yahoo!\"></a>
							<a href=\"" . Title::makeTitle(NS_SPECIAL, "InviteContacts" . $which)->escapeFullUrl() . "\"><img src=\"extensions/wikia/getmycontacts/images/mygmail.gif\" border=\"0\" alt=\"Gmail\"></a>
						</p>
						<p class=\"invite-right-image\">
							<a href=\"" . Title::makeTitle(NS_SPECIAL, "InviteContacts" . $which)->escapeFullUrl() . "\"><img src=\"extensions/wikia/getmycontacts/images/myhotmail.gif\" border=\"0\" alt=\"Hotmail\"></a>
							<a href=\"" . Title::makeTitle(NS_SPECIAL, "InviteContacts" . $which)->escapeFullUrl() . "\"><img src=\"extensions/wikia/getmycontacts/images/myaol.gif\" border=\"0\" alt=\"AOL\"></a>
						</p>";
					$out .= "<div class=\"cleared\"></div>";
					$out .= "<p><input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'invite_btn_addfriends' ) . "\" onclick=\"window.location='" . Title::makeTitle(NS_SPECIAL, "InviteContacts" . $which)->escapeFullUrl() . "'\"/></p>";
				$out .= "</div>";
				$out .= "<div class=\"cleared\"></div>";
						
				}
				
				

            $out .= "</div></form>";

			$wgOut->addHTML($out);
			}
		}
		
	}
	
	SpecialPage::addPage( new InviteContacts );
	global $wgMessageCache,$wgOut;
}

?>