<?php

$wgExtensionFunctions[] = 'wfSpecialInviteContactsCSV';


function wfSpecialInviteContactsCSV(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class InviteContactsCSV extends UnlistedSpecialPage {
	
		function InviteContactsCSV(){
			UnlistedSpecialPage::UnlistedSpecialPage("InviteContactsCSV");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP; 
			
			/*/
			/* Redirect Non-logged in users to Login Page
			/* It will automatically return them to the ViewGifts page
			/*/
			if($wgUser->getID() == 0){
				$login =  Title::makeTitle( NS_SPECIAL  , "UserLogin"  );
				$wgOut->redirect( $login->getFullURL() . "&returnto=Special:InviteContacts" );
				return false;
			}
			
			$register =  Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
			
			$this->domain = $wgRequest->getVal("domain");
			
			$output = "";
			$output .= "<div class=\"invite-links\">
					<span class=\"profile-on\"><a href=\"index.php?title=Special:InviteContacts\">Find Your Friends</a></span>
					- <a href=\"index.php?title=Special:InviteEmail\">Invite Your Friends</a>
			</div>";
			
			if(count($_POST) ){
				
				//UPLOADED CSV FILE
				if($_POST["upload_csv"] == 1){
					$wgOut->setPagetitle( "Invite Your Friends" );
					$output .= '<form id="form_id" name="myform" method="post" action=""><input type="hidden" name="sendersemail" value="' . $_POST["sendersemail"] . '">';

					$output .= '<div class="invite-message">ArmchairGM is more funny with your friends.  Invite all of them. Enter your e-mail and password below to load your contacts. 
						<b>We are serious about keeping your private information private.  We do not store the e-mail address or password provided to us</b>.
						</div>
						<h1>Your contacts</h1>
						<p class="contacts-message">
							<span class="profile-on">Share ArmchairGM with your friends.  They will thank you.  The more friends you invite, the less bored you will be.</span>
						</p>
						<p class="contacts-message">
							<input type="submit" class="invite-form-button" value="Invite Your Friends" name="B1" /> <a href="javascript:toggleChecked()">uncheck all</a>
						</p>
							<div class="contacts-title-row">
								<p class="contacts-checkbox"></p>
								<p class="contacts-title">
									Friend\'s Name
								</p>
								<p class="contacts-title">
									Email
									</p>
									<div class="cleared"></div>
							</div>
							<div class="contacts">';

							//echo filetype($wgRequest->getFileTempname( 'ufile' ));
							//exit();
						
						if($wgRequest->getFileSize( 'ufile' ) > 20000){
							//$wgOut->addHTML("<div class=\"upload-csv-error\">The file you uploaded is too large</div>");
							//$wgOut->addHMTL($this->displayForm());
						}
								
						//OPEING CSV FILE FOR PROCESSING
						$fp = fopen ($wgRequest->getFileTempname( 'ufile' ),"r");
						while (!feof($fp)){
							$data = fgetcsv ($fp, 0, ","); //this uses the fgetcsv function to store the quote info in the array $data
							
							switch($wgRequest->getVal("email_client")){
								case "outlook":
									$dataname = $data[1];
									if( !empty($dataname) && $data[3] )$dataname = $data[1] . " " . $data[3];
									if(empty($dataname))$dataname = $data[3];
									$email = $data[57]; 
									break;
								case "outlook_express":
									$email = $data[1];
									$dataname = $data[0];
									break;
								case "thunderbird":
									$email = $data[4];
									$dataname = $data[2];
									if(empty($dataname) && ($data[0] || $data[1]))$dataname = $data[0] . " " . $data[1];
									break;
									
							}
							if(empty($dataname))$dataname = $email;
							if (!empty($email) && $data[0]!="First Name" && $data[0]!="Name" && $data[1]!="First Name"){  //Skip table if email is blank
								$addresses[] = array("name"=>$dataname,"email"=>$email);
							}
						}
						
						if($addresses){
							usort($addresses, 'sortCSVContacts');
							
							foreach ($addresses as $address){
								$output .= '<div class="contacts-row">
									<p class="contacts-checkbox">
										<input type="checkbox" name="list[]" value="'.$address["email"].'" checked>
									</p>
									<p class="contacts-cell">
										'.$address["name"].'
									</p>
									<p class="contacts-cell">
										'.$address["email"].'
									</p>
									<div class="cleared"></div>
								</div>';
							}
						}
		
						$output .= '</div>';
						$output .= '<p>
						<input type="submit" class="invite-form-button" value="Invite Your Friends" name="B1" /> <a href="javascript:toggleChecked()">uncheck all</a>
						</p>
						</form>';
						$wgOut->addHTML($output);
	
				}else{ //USER CLICKED TO SEND EMAIL TO CONTACTS
					$html_format = "no";  //IMPORTANT: change this to "yes"  ONLY if you are sending html format email
					$title = Title::makeTitle( NS_USER , $wgUser->getName()  );
					$register->getFullURL() . "&from=1&referral=" . $title->getDBKey();
					$subject = wfMsg( 'invite_subject',
						$wgUser->getName()
						);
	
					
					$body = wfMsg( 'invite_body',
						$_POST['sendersemail'],
						$wgUser->getName(),
						$title->getFullURL(),
						$register->getFullURL() . "&from=1&referral=" . $title->getDBKey()
						);
	
	
$message = <<<EOF

$body

EOF;
					
					$from = "community@wikia.com";
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
					$mail->track_email(2,count($_POST['list']));
					
					$wgOut->setPagetitle( "Messages Sent!" );
					$wgOut->addHTML("Emails went out to the following addresses" . $confirm);
				}
			}else{
				$wgOut->setPagetitle( "Invite Your Friends" );
				$wgOut->addHTML($this->displayForm());
			}
		}
		
		function displayForm(){
			global $wgUser, $wgOut, $wgRequest, $IP; 
			$out = "";
			$out .= "<script>
			function uploadCSV(){
				if(!\$F('sendersemail')){
					alert('You must enter an email address');
				}else{
					document.emailform.submit();
				}
			}
			</script>";
				$out .= "<div class=\"invite-links\">
						<a href=\"index.php?title=Special:InviteContacts\">Find Your Friends</a>
						- <span class=\"profile-on\"><a href=\"index.php?title=Special:InviteEmail\">Invite Your Friends</a></span>
				</div>
				<div class=\"invite-message\">ArmchairGM is more fun with your friends.  Invite all of them. Enter your e-mail and password below to load your contacts. 
					<b>We are serious about keeping your private information private.  We do not store the e-mail address or password provided to us</b>.
				</div>
				<div id=\"target\">";
			
				$out .= "<div class=\"invite-left\">";
					$out .= "<div class=\"invite-icons\">
							<img src=\"getmycontacts/images/myoutlook.gif\" border=\"0\">
							<img src=\"getmycontacts/images/myexpress.gif\" border=\"0\">
							<img src=\"getmycontacts/images/mythunderbird.gif\" border=\"0\">
						</div>
						<div class=\"invite-form\">
							<form name=emailform action=\"\" enctype=\"multipart/form-data\"  method=post>
							<input type=\"hidden\" name=\"upload_csv\" value=\"1\">
							<p>There is a 2MB limit for your .csv file</p>
							<p class=\"invite-form-title\">Select Email Client</p>
							<p class=\"invite-form-input\">
								<select name=\"email_client\">
									<option value=\"outlook\">Outlook</option>
									<option value=\"outlook_express\">Outlook Express</option>
									<option value=\"thunderbird\">Thunderbird</option>
								</select>
							</p>
							<div class=\"cleared\"></div>
							<p class=\"invite-form-title\">Select File</p>
							<p class=\"invite-form-input\"><input name=\"ufile\" type=\"file\" id=\"ufile\" size=\"28\" /></p>
							<div class=\"cleared\"></div>
							<p class=\"invite-form-title\">Verify Your Email Address</p>
							<p class=\"invite-form-input\"><input name=\"sendersemail\" type=\"text\" id=\"sendersemail\" size=\"28\" value=\"{$wgUser->getEmail()}\"/></p>
							<p><input type=\"button\" onclick=\"javascript:uploadCSV()\" class=\"invite-form-button\" value=\"Upload Your Contacts\"></p>
						</div>";
				$out .= "</div>";
				$out .= "<div class=\"invite-right\">";
				$out .= "<h1>Have webmail?</h1>";
				$out .= "<p class=\"invite-right-image\">
						<a href=\"index.php?title=Special:InviteContacts\"><img src=\"getmycontacts/images/myyahoo.gif\" border=\"0\"></a>
						<a href=\"index.php?title=Special:InviteContacts\"><img src=\"getmycontacts/images/mygmail.gif\" border=\"0\"></a>
					</p>
					<p class=\"invite-right-image\">
						<a href=\"index.php?title=Special:InviteContacts\"><img src=\"getmycontacts/images/myhotmail.gif\" border=\"0\"></a>
						<a href=\"index.php?title=Special:InviteContacts\"><img src=\"getmycontacts/images/myaol.gif\" border=\"0\"></a>
					</p>";
				$out .= "<div class=\"cleared\"></div>";
				$out .= "<p  align=\"center\"><input type=\"button\" class=\"invite-form-button\" value=\"Invite Your Friends\" onclick=\"window.location='index.php?title=Special:InviteContacts'\"/></p>";
			$out .= "</div>";
			$out .= "<div class=\"cleared\"></div>";
			$out .= "</div></form>";
			return $out;
		}
		
	
		
	}
	
	SpecialPage::addPage( new InviteContactsCSV );
	global $wgMessageCache,$wgOut;
}

function sortCSVContacts($x, $y){
	if ( strtoupper($x["name"]) == strtoupper($y["name"]) )
	 return 0;
	else if ( strtoupper($x["name"]) < strtoupper($y["name"]) )
	 return -1;
	else
	 return 1;
}
?>