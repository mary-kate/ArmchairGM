<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessUpdate';

function wfSpecialMarchMadnessUpdate(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessUpdate extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessUpdate");

	}

	function march_madness_update($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessUpdate" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessUpdate" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value) {

		global $wgUser, $wgOut, $wgRequest, $wgFriendingEnabled, $IP, $wgStyleVersion;
		
		if (!$wgUser->isLoggedIn()) {
			/*
			$page_title = "Not Logged In";
			$output = "You must be logged in. Please log in and come back here.";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
			*/
			return MarchMadness::not_logged_in();
		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
		

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		$output = "";
		
		$link = MarchMadnessGroupAdmin::march_madness_group_link();
		
		if (!$value) {
			
			$output .= "<div id=\"group-list-div\">";
			$output .= "No Entry Specified - Please go back to your group admin page.<br/>";
			//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\"><br/><br/>";
			$output .= "<div class=\"madness-nav-link\"><a href=\"{$link}\">" . wfMsg("mm_back_to_home") . "</a></div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
		
			
			$wgOut->setPageTitle("Oops, no group specified");
			$wgOut->addHTML($output);
		}
		else {
			$entry_info = MarchMadness::get_entry_info_from_db($value);
			
			//if (isset($group_info["creator"]) && $group_info["creator"] == $current_user_id) {
				$output .= "";
				
				if ($entry_info["user_id"] != $current_user_id) { 
					$output .= "<div id=\"group-list-div\">";
					$output .= "You can only change the name of your own entry<br/>";
					//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\"><br/><br/>";
					$output .= "<div class=\"madness-nav-link\"><a href=\"{$link}\">" . wfMsg("mm_back_to_home") . "</a></div>";
					$output .= "<div class=\"cleared\"></div>";
					$output .= "</div>";
				
					
					$wgOut->setPageTitle("Permission Denied");
					$wgOut->addHTML($output);
					return "";
				}
				$output .= "<div id=\"madness-group-update-container\">";
				
				$output .= "<div id=\"madness-group-update-errors\" style=\"display:none; color:red; font-weight:bold;\"></div>";
								
				
				
				//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$output .= "<div class=\"madness-nav-link\">";
				$output .= "<a href=\"{$link}\">" . wfMsg("mm_back_to_home") . "</a>";
				
				$group_link = MarchMadnessGroupAdmin::march_madness_group_link($entry_info["group_id"]);
				
				$output .= " - <a href=\"{$group_link}\">" . wfMsg("mm_back_to_group") . "</a>";
				$output .= "</div>";
				$output .= "<div class=\"cleared\"></div>";
				
				$output .= "<input type=\"text\" id=\"entry_name\" value=\"{$entry_info["entry_name"]}\" maxlength=\"50\" /> ";
				$page_title = "Edit your entry name in {$entry_info["group_name"]}";
				$output .= "<input type=\"button\" class=\"site-button\" value=\"update name\" onclick=\"update_entry_name({$value});\" /><br/>";

				
				$output .= "</div>";
				
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
				/*
			}
			else {
				$output = "You must be the creator of the group to modify these settings<br/>
				Please return to <a href=\"{$link}\">your home page</a>";
				$page_title = "Permission Denied";
				$wgOut->setPageTitle($page_title);
				$wgOut->addHTML($output);
			}
			*/
		
			
		}
	}
	
}


SpecialPage::addPage( new MarchMadnessUpdate );
	
}

?>
