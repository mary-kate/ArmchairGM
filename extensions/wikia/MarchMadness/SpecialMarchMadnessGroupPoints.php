<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupPoints';

function wfSpecialMarchMadnessGroupPoints(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupPoints extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessGroupPoints");

	}

	function march_madness_points_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupPoints" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessGroupPoints" . (($value)?"/".$value:""))->escapeFullUrl($query);
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
			$output .= "No Group Specified - Please go back to your group admin page.";
			//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Create New Group\"><br/><br/>";
			$output .= "<div class=\"madness-nav-link\"><a href=\"{$link}\">" . wfMsg("mm_create_group") . "</a></div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
		
			
			$wgOut->setPageTitle("Oops, no group specified");
			$wgOut->addHTML($output);
		}
		
		else {
			$group_info = MarchMadness::get_group_info_from_db($value);
			
			if (!sizeof($group_info)) {
				//$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$output = "<div class=\"madness-nav-link\"><a href=\"{$link}\">" . wfMsg("mm_create_group") . "</a></div>";
				$output .= "<div class=\"cleared\"></div>";
				
				$wgOut->setPageTitle("This is not a valid group");
				$wgOut->addHTML($output);
				return "";
		}
			
			//if (isset($group_info["creator"]) && $group_info["creator"] == $current_user_id) {
				$output .= "";
				$group_name = $group_info["group_name"];
				
				$tournament_started =  (intval(time()) > intval($group_info["start_date"]) ? true : false);
				//$tournament_started = false;
								
				$output .= "<div class=\"madness-nav-link\">";
				$output .= "<a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">" . wfMsg("mm_back_to_home") . "</a>";
				$output .= " - <a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link($value) . "\">" . wfMsg("mm_back_to_group") . "</a>";
				$output .= "</div>";
				
				if (!isset($group_info["creator"]) || $group_info["creator"] != $current_user_id) {
					$tournament_started = true;
					$output .= "<div class=\"madness-group-points-text\">Only the tournament creator can modify the scoring system for this group.</div>";
				}
				
				$output .= "<div id=\"madness-group-points-container\">";

				//$output .= time() . " - " . intval($group_info["start_date"]) . "<br/>";
				
				$output .= "<div id=\"madness-group-points-errors\" style=\"display:none; color:red; font-weight:bold;\"></div>";
								
				$level_points = explode(",", $group_info["scoring"]);
				
				for($count=0; $count < sizeof($level_points); $count++) {
					$output .= "Round " . ($count+1) . ": <input type=\"text\" ". ($tournament_started ? "disabled=\"true\"" : "") . " id=\"scoring-level-" . ($count+1) . "\" value=\"{$level_points[$count]}\" size=\"3\" /><br/>";
				}
				
				$output .= "<input type=\"hidden\" id=\"num-scoring-levels\" value=\"" . sizeof($level_points) . "\" size=\"3\" /><br/>";
				
				if(!$tournament_started) {
					$page_title = "Modify Scoring Levels for {$group_name}";
					$output .= "<input type=\"button\" class=\"site-button\" onclick=\"update_scoring_levels({$value}, {$group_info["tournament_id"]})\" value=\"" . wfMsg("mm_update_scoring") . "\"/>";
				}
				else {
					$page_title = "Scoring Levels for {$group_name}";
				}

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


SpecialPage::addPage( new MarchMadnessGroupPoints );
	
}

?>
