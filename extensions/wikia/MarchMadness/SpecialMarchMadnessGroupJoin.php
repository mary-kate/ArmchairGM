<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupJoin';

function wfSpecialMarchMadnessGroupJoin(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupJoin extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessGroupJoin");

	}

	function march_madness_join_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupJoin" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessGroupJoin" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value) {

		global $wgUser, $wgOut, $wgRequest, $wgFriendingEnabled, $IP, $wgStyleVersion;
		
		if (!$wgUser->isLoggedIn()) {
			
			if ($value) {
				$group_info = MarchMadness::get_group_info_from_db($value);
				if (sizeof($group_info)) {
					
					$login_link = Title::makeTitle(NS_SPECIAL, "Login");
					$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");

					$page_title = "You need to be logged in to join {$group_info["group_name"]}";
					$output = "This is a basketball tournament bracket group called {$group_info["group_name"]}.  To join this group, <a href=\"" . $login_link->escapeFullUrl() . "\">login</a>, or <a href=\"" . $register_link->escapeFullUrl() . "\">sign up for ArmchairGM</a> and then return to this page.  Thanks!";
					$wgOut->setPageTitle($page_title);
					$wgOut->addHTML($output);
					return "";
				}
				else {
					return MarchMadness::not_logged_in();
				}
			}
			else {
				return MarchMadness::not_logged_in();
			}

		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
		
		

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		$output = "";
		
		//$link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin")->escapeFullUrl();
		$link = MarchMadnessGroupAdmin::march_madness_group_link();
		
		if (!$value) {
			
			$output .= "<div id=\"group-list-div\">";
			$output .= "No Group Specified - Please go back to your group admin page.";
			$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Create New Group\"><br/><br/>";
			$output .= "</div>";
		
			
			$wgOut->setPageTitle("Oops, no group specified");
			$wgOut->addHTML($output);
		}
		else {
			//$link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin")->escapeFullUrl();
			
			//$link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin")->escapeFullUrl();

			//$group_name =  MarchMadnessGroupAdmin::get_group_name_from_db($value);
			
			$group_info = MarchMadness::get_group_info_from_db($value);
			if (!sizeof($group_info)) {
				$link = MarchMadnessGroupAdmin::march_madness_group_link();

				//$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$output .= "<div class=\"madness-nav-link\"><a href=\"" . $link . "\">" . wfMsg("mm_back_to_home") . "</a></div>";
				$output .= "<div class=\"cleared\"></div>";

				
				$wgOut->setPageTitle("This is not a valid group");
				$wgOut->addHTML($output);
				return "";
			}
			$tournament_started = MarchMadness::tournament_started($group_info["tournament_id"]);
			if ($tournament_started) {
				$link = MarchMadnessGroupAdmin::march_madness_group_link();
				$output .= "<div id=\"group-list-div\">";
				$output .= "The tournament has already started. You may no longer join<br/>";
				$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\"><br/><br/>";
				$output .= "</div>";
			
				
				$wgOut->setPageTitle("Tournament already started");
				$wgOut->addHTML($output);
				return "";
			}

			$group_members = MarchMadness::get_group_members_from_db("", $value);
			$group_members_check = array_flip($group_members);
			
			if (!isset($group_members_check[$current_user_name])) {
				
				//$group_info = MarchMadness::get_group_info_from_db($value);
				$group_name = $group_info["group_name"];
				$page_title = "Would you like to join {$group_name}?";
				
				$output = "<div id=\"madness-group-join-container\">";
				
				$output .= "<div id=\"madness-group-join-errors\" style=\"display:none; color:red; font-weight:bold;\"></div>";
				
				$output .= "Group Creator: {$group_info["creator_name"]}<br/>";
				$output .= "Tournament: {$group_info["tournament_name"]}<br/>";
				$output .= "Number of Entries: " . sizeof($group_members) . "<br/>";
				$output .= "Group Type: " . ($group_info["private"] ? "Private (password needed) <input type=\"password\" id=\"madness-join-password\" name=\"madness-join-password\" />" : "Public") . "<br/>";
				
				$output .= "Entry Name: <input type=\"text\" id=\"madness-create-entry\" name=\"madness-create-entry\" /> <br/>";
				$output .= "<input type=\"hidden\" id=\"madness-group-join-id\" name=\"madness-group-join-id\" value=\"{$value}\" />";
				$output .= "<input type=\"hidden\" id=\"madness-group-join-tournament-id\" name=\"madness-group-join-tournament-id\" value=\"{$group_info["tournament_id"]}\" />";
				$output .= "<input type=\"hidden\" id=\"madness-group-redirect\" name=\"madness-group-redirect\" value=\"{$link}\" />";
				$output .= "<input type=\"button\" class=\"site-button\" onclick=\"join_group();\" value=\"Join This Group\"><br/><br/>";
				
				
				/*
				$output .= "<h2>Current Members of " . $group_name . "</h2>";
				foreach ($group_members as $member_entry=>$member) {
					$query = "entry_id={$member_entry}";
					$member_link = MarchMadness::march_madness_link($query);
					//$member = User::newFromID($member_id);
					$output .= "<a href=\"{$member_link}\">{$member}</a><br/>";
				}
				*/
				
				
				$output .= "</div>";
				
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
			}
			else {
				$output = "You already belong to this group, you can not join twice.<br/>
				Please return to <a href=\"{$link}\">your admin page</a>";
				$page_title = "You are already in this group";
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
			}
		
			
		}
	}
	
}


SpecialPage::addPage( new MarchMadnessGroupJoin );
	
}

?>
