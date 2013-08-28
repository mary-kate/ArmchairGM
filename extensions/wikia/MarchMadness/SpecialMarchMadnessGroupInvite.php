<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupInvite';

function wfSpecialMarchMadnessGroupInvite(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupInvite extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessGroupInvite");

	}
	
	function march_madness_group_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupInvite" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessGroupInvite" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value) {

		global $wgUser, $wgOut, $wgRequest, $wgFriendingEnabled, $IP, $wgStyleVersion;

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		
		if (!$wgUser->isLoggedIn()) {
			$login_link = Title::makeTitle(NS_SPECIAL, "Login");
			$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
			
			$page_title = "Whoops! You need to Log In or Register";
			$output = "You must be a registered users of ArmchairGM and logged in to use the Tournament Manager. Please register or log in to continue.";
			$output .= "<div class=\"madness-button\">
				<input type=\"button\" value=\"Log In\" class=\"site-button\" onclick=\"document.location='".$login_link->escapeFullURL()."'\"/>
				<input type=\"button\" value=\"Register\" class=\"site-button\" onclick=\"document.location='".$register_link->escapeFullURL()."'\"/>
			</div>";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);

		$output = "";
		
		$link = MarchMadnessGroupAdmin::march_madness_group_link();;
		
		if (!$value) {
			
			$groups = MarchMadnessGroupAdmin::get_user_groups($current_user_id);
			
			$output .= "<div id=\"group-list-div\">
				<div class=\"madness-message\">
					Which group do you want to invite new members to?  If you want to create a new group, click <a href=\"{$link}\">here</a>
				</div>
				<div class=\"group-invite-list\">";
			
				foreach($groups as $tournament_id=>$tournament_groups) {
					foreach($tournament_groups as $temp_id=>$group_info) {
						$link = $this->march_madness_group_link($group_info["id"]);
						$output .= "<a href=\"{$link}\">{$group_info["name"]}</a>";
					}
				}
			
				$output .= "</div>
			</div>";
		
			$wgOut->setPageTitle("Whoops! You need to select a group before you can invite others.");
			$wgOut->addHTML($output);
		} else {
			$group_members = MarchMadness::get_group_members_from_db("", $value);
			$group_name =  MarchMadnessGroupAdmin::get_group_name_from_db($value);
			
			$group_members_check = array_flip($group_members);
			
			//check if able to invite people to group
			$group_info = MarchMadness::get_group_info_from_db($value);
			
			$tournament_started = MarchMadness::tournament_started($group_info["tournament_id"]);
			
			if($tournament_started) {
				$page_title = "Whoops! You can no longer invite people.";
				$output = "The tournament has started.  You can no longer invite people.  Please return to <a href=\"{$link}\"> your group home page.";
				
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
				return "";
			}

			if ($group_info["private"] && $group_info["creator"]!=$current_user_id) {
				
				$page_title = "Whoops! You cannot invite members to this group";
				$output = "Only the creator of a private group can invite others.  Please return to <a href=\"{$link}\">your group home page</a> and select another group.";
				
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
				return "";
			}

			if (!isset($group_members_check[$current_user_name])) {
				
				$page_title = "Whoops! You cannot invite members to this group";
				$output = "You must be a member of a public group before you can invite others. Please return to <a href=\"{$link}\">your group home page</a> and select another group.";
				
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
				return "";
			}
			
			
			/*
			$output .= "<div class=\"madness-current-members\">
				<h2>".wordwrap("Members of \"".$group_name."\"", 25, "<br/>\n", true)."</h2>";
				foreach ($group_members as $member_entry=>$member) {
					$query = "entry_id={$member_entry}";
					$member_link = MarchMadness::march_madness_link($query);
					//$member = User::newFromID($member_id);
					$output .= "<a href=\"{$member_link}\">{$member}</a>";
				}
			$output .= "</div>";
			*/
			
			$output .= "<script type='text/javascript'><!--//<![CDATA[\n";
			$output .= "var already_in_group = new Array();\n";
			
			foreach($group_members as $member_id => $member) {
				$output .= "already_in_group['{$member}'] = 1;\n";
			}
			
			$output .= "var __group_name=\"{$group_name}\";\n";
			$output .= "var __group_id=\"{$value}\";\n";
			$output .= "//]]>--></script>";
			
			$output .= "<div class=\"silliness-container\">";
			$output .= "<div class=\"silliness-left\">";

			
			$output .= "<div id=\"madness-invite-return\"></div>";
			//$output .= "<div id=\"group-nav\">";
			
			$output .= "<form action=\"\" method=\"GET\" enctype=\"multipart/form-data\" name=\"invite\">
				<div class=\"madness-message\">
					Who would you like to invite into your group?  
				</div>
				<div class=\"madness-invite-container\">";
				
				if ($wgFriendingEnabled) {
				
					require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
				
					$rel = new UserRelationship($wgUser->getName());
					$friends_temp = $rel->getRelationshipList(1);
					$friends = array();
					$in_group = array_flip($group_members);
					
					foreach ($friends_temp as $friend) {
						
						if (!isset($in_group[$friend["user_name"]])) {
							$friends[] = $friend;
						}
					
					}
					
					if ($friends) {
						
						$output .= "<div class=\"madness-invite-friends\">
						<div class=\"madness-title\">Select from your list of friends</div>
						<div class=\"g-gift-select\">
							<select id=\"madness-friends-list\" multiple size=\"5\">
							<!-<option value=\"#\" selected>Select a Friend</option>-->";
							foreach ($friends as $friend) {
								$output .= "<option value=\"" . $friend["user_name"] . "\">{$friend["user_name"]}</option>";
							}
						$output .= "</select>
						</div></div>";
						/*
						<div class=\"madness-button\">
							<input type=\"button\" value=\"Select\" onclick=\"javascript:chooseFriendFromList()\">
						</div>";
						*/
					}
				}
			
				/*
				$output .= "<div class=\"g-give-title\">Enter a user name</div>
				<div class=\"g-give-textbox\">
			  		<input type=\"text\" width=\"85\" id=\"entry-tb\" name=\"entry-tb\" value=\"\">
			  		<input class=\"site-button\" type=\"button\" value=\"Add\" onclick=\"javascript:chooseFriendFromTb()\">
				</div>
				
				<div class=\"g-give-textbox\">
					<select id=\"madness-invite-list\" name=\"madness-invite-list\" multiple size=\"7\"></select>
					<input class=\"site-button\" type=\"button\" value=\"Remove Selected\" onclick=\"javascript:removeUserNames()\">
				</div>
				</div>";
				*/
				/*
				$output .= "<div class=\"madness-invite-emails\">
					<div class=\"madness-title\">Enter an email address</div>
					<div class=\"g-give-textbox\">
			  			<input type=\"text\" width=\"85\" id=\"email-entry-tb\" name=\"email-entry-tb\" value=\"\">
			  			<input type=\"button\" class=\"site-button\" value=\"Add\" onclick=\"javascript:chooseEmailFromTb()\">
					</div>
					<div class=\"g-give-textbox\">
						<select id=\"madness-email-invite-list\" name=\"madness-email-invite-list\" multiple size=\"7\"></select>
					</div>
					<div class=\"madness-button\">
						<input type=\"button\" class=\"site-button\" value=\"Remove Selected\" onclick=\"javascript:removeEmails()\">
					</div>
				
					
				</div>";*/
				
				$output .= "<div class=\"madness-invite-emails\">
					<div class=\"madness-title\">Enter email addresses</div>
					<div class=\"g-give-textbox\">
			  			<textarea id=\"madness-email-invite-list\" rows=\"10\"></textarea>
						<br/> (Comma separated list)			  			
					</div>
				
					
				</div>";
				
				$output .= "<div class=\"madness-invite-buttons\">
					<div class=\"madness-title\">Click a button</div>
					<input class=\"site-button\" type=\"button\" value=\"Send Invitations\" onclick=\"javascript:sendInviteEmails()\"> <br/><br/> 
					<input type=\"button\" class=\"site-button\" onclick=\"location.href='" . MarchMadnessGroupAdmin::march_madness_group_link() . "'\" value=\"Back To Admin\">
				</div>
				<div class=\"cleared\"></div>
			</form>";
			
		$output .= "<div class=\"madness-group-update\">";
		$output .= "<div class=\"header\">Prefer to send the email on your own? Here is what we would have sent.  Feel free to cut and paste this text to send your own email.</div><br/>";
		
		if ($group_info["private"]) {
			$password = $group_info["password"];
		}
		else {
			$password = false;
		}
		$current_user_name = $wgUser->getName();
		$link = MarchMadnessGroupJoin::march_madness_join_link($value);
		$tournament_info = MarchMadness::get_tournament_info_from_db($group_info["tournament_id"]);
		$output .= str_replace("\n", "<br/>", MarchMadnessGroupInvite::inviteMessage($tournament_info["tournament_name"], $tournament_info["tournament_desc"], "", $current_user_name, $link, $password));
		
		$output .= "</div>";
		$output .= "</div>";
		
		$output .= "</div>";
			
		$output .= "<div class=\"silliness-right\">";
		$output .= MarchMadness::get_mini_standings($value);
		//$output .= MarchMadness::get_right_column();
		$output .= "</div>";
		$output .= "<div class=\"cleared\"></div>";
		$output .= "</div>";
		
			
		$wgOut->setPageTitle("Invite More Members to \"{$group_name}\"");
		$wgOut->addHTML($output);
			
		}
	}
	
	function inviteMessage($t_name, $t_desc, $to, $from, $link, $password=false) {
		$message = "Hi {$to}:\n\n";
		$message .= "{$from} wants you to join his {$t_name} tournament group!  Click this link:\n\n";
		$message .= "{$link} \n\n";
		$message .= "and fill out a bracket, and show {$from} who's the master.\n\n";
		$message .= "Here's the description of the group:\n\n";
		$message .= $t_desc . "\n\n";
		$message .= "So get choosing!\n\n";
		
		if ($password) {
			$message .= "This is a private group.  You will need to enter the following password to join:\n{$password}\n\n";
		}
		
		$message .= "Thanks,\nArmchairGM";
		
		return $message;
	}
	
}


SpecialPage::addPage( new MarchMadnessGroupInvite );
	
}

?>
