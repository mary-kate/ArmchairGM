<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupEmails';

function wfSpecialMarchMadnessGroupEmails(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupEmails extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessGroupEmails");

	}
	
	function march_madness_group_email_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessGroupEmails" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}
	
	function standings_page_size() {
		return 50;
	}

	function execute($value) {

		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $wgEmailFrom, $wgMemc;
		
		
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");

		$user_groups = $wgUser->getGroups();
		$user_groups = array_flip($user_groups);
		
		if (!isset($user_groups['staff'])) {
			$page_title = "Permission Denied";
			$output = "You must be staff to view this page";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
		}
		
		if (!$value) {
			
			$page_title = "You need to have a group id to send emails to";
			$output .= "<h2>{$page_title}</h2>";
			
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
		}
		else {
			set_time_limit(0);
			$group_id=$value;
			$group_standings = MarchMadness::get_group_standings_from_db($group_id);
			$confirmed_count = 0;
			$unconfirmed_count = 0;
			$unconfirmed_with_email_count = 0;
			$blank_count = 0;
			$email_addresses = array();
			
			$key = wfMemcKey( 'marchmadness', 'groupemails', $group_id );

			//wfDebug( "Cache No-try - Got group list ({$key}) from db\n" );
			//$wgMemc->delete($key);
			$data = $wgMemc->get( $key );
			if( $data && MarchMadness::madness_use_cache() ){
				wfDebug( "Cache Hit - Got group emails ({$key}) from cache (size: " .sizeof($data). ")\n" );
				$output .= "got from cache<br/>";
				$email_addresses = $data;
			}else{

				wfDebug( "Cache Miss - Got group emails ({$key}) from db\n" );
				$output .= "got from db<br/>";
				foreach($group_standings as $entry_id=>$entry_info) {
					$user_name = trim($entry_info["user_name"]);
					$user = User::newFromName($user_name);
					if($user && $user->isEmailConfirmed()) {
						$message = MarchMadnessGroupInvite::inviteMessage($tournament_info["tournament_name"], $tournament_info["tournament_desc"], $user_name, $current_user_name, $link, $group_info["password"]);
						//$to = $user->getEmail();
						//$confirmed .= "\"{$user_name}\" " . $user->getEmail() . ", ";
						$email = $user->getEmail();
						if (trim($email) != "") {
							$email_addresses[$user_name] = $user->getEmail();
							$confirmed_count++;
						}
						else {
							$blank_count++;
							$blank .= "{$user_name}, ";
						}
					}
					else {
						if ($user) { 
							//$unconfirmed_with_email .= "\"{$user_name}\" " . $user->getEmail() . ", ";
							$email = $user->getEmail();
							if (trim($email) != "") {
								$email_addresses[$user_name] = $user->getEmail();
								$unconfirmed_with_email_count++;
							}
							else {
								$blank_count++;
								$blank .= "{$user_name}, ";
							}
						}
						else {
							$unconfirmed .= "{$user_name}, ";
							$unconfirmed_count++;
						}
					}
				}
				$wgMemc->set( $key, $email_addresses );
			}
			$output .= "";
			//$output .= "{$confirmed_count} confirmed emails; {$unconfirmed_with_email_count} unconfirmed_with_emails; {$unconfirmed_count} unconfirmed without emails.<br>";
			//$output .= "Confirmed:<br/> {$confirmed} <br/>";
			//$output .= "Unconfirmed with emails:<br/> {$unconfirmed_with_email} <br/>";
			
			$subject = "The 32 Hottest Sports Wives Tourney is Waiting for Your Vote!";
			
			$email_group_size = 100;
			
			if(sizeof($email_addresses) > $email_group_size) {
				if ($wgRequest->getVal("page")) {
					$page = intval($wgRequest->getVal("page"));
				}
				else {
					$page = 1;
				}
				
				$email_chunk_array = array_chunk($email_addresses, $email_group_size, true);
				if (isset($email_chunk_array[$page-1])) {
					$email_addresses = $email_chunk_array[$page-1];
				}
				else {
					$email_addresses = $email_chunk_array[0];
				}
			}
			
			foreach($email_addresses as $username=>$email) {
				$message =  $this->getEmailMessage($username);
				$to = $email;
				//mail($to, $subject, $message, "From: $wgEmailFrom");
				$output .= "Sent to {$username} - {$email}<br/>";
				//echo "Sent to {$username} - {$email}<br/>";
			}
			$subject = $message = "Done page {$page}";
			$output .= $subject;
			//mail("jeffrey.tierney@gmail.com", $subject, $message, "From: $wgEmailFrom");
			//$output .= "Unconfirmed:<br/> {$unconfirmed} <br/>";
			
			$page_title = "Group Emails";
			
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
		}
	}

	function getEmailMessage($username) {
		global $wgServer;
		$message = "Hi {$username}:\n\n";
			
		$message .= "Thanks for signing up at ArmchairGM's 32 Hottest Sports Wives\n";
		$message .= "competition.  You can go vote for your favorites at\n\n";
			
		$message .= "{$wgServer}/Article:2008_Hottest_Sports_Wife_Tournament_-_Third_Round_Voting\n\n";
			
		$message .= "Voting closes Monday at 9 AM EDT.\n\n";
			
		$message .= "Thanks,\n\n";
			
		$message .= "The ArmchairGM Team";
		
		return $message;
	}

}


SpecialPage::addPage( new MarchMadnessGroupEmails );
	
}

?>
