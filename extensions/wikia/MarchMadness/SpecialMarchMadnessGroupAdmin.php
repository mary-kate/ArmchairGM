<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupAdmin';

function wfSpecialMarchMadnessGroupAdmin(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupAdmin extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessHome");

	}
	
	function march_madness_group_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessHome" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}
	
	function standings_page_size() {
		return 50;
	}

	function execute($value) {

		global $wgUser, $wgOut, $wgRequest, $wgUploadPath, $wgStyleVersion;
		
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		
		if (!$wgUser->isLoggedIn()) {
			/*
			$login_link = Title::makeTitle(NS_SPECIAL, "Login");
			$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
			
			$page_title = "Whoops! You need to Log In or Register";
			$output = "You must be a registered users of ArmchairGM and logged in to use the Tournament Manager. Please register or log in to continue.";
			$output .= "<div class=\"madness-nav-link\">
				<a href=\"" . $login_link->escapeFullURL() . "\">Log In</a> - 
				<a href=\"" . $register_link->escapeFullURL() . "\">Register</a>
				
			</div>";
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
			*/
			
			return MarchMadness::not_logged_in();

		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
		
		
		if (!$value) {
			$output .= "<div class=\"silliness-container\">";
			$output .= "<div class=\"silliness-left\">";
			$link = MarchMadnessGroupCreate::march_madness_group_create_link();
			//$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Create New Group\"> ";
			$output .= "<div class=\"madness-nav-link\"><a href=\"" . $link . "\">" . wfMsg("mm_create_group") . "</a> - ";
			$link = MarchMadnessSearch::march_madness_search_link();
			//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Search Groups\"><br/><br/>";
			$output .= "<a href=\"" . $link . "\">" . wfMsg("mm_search_groups") . "</a></div>";
			
			$groups = $this->get_user_groups($current_user_id);
			$output .= "<div id=\"group-list-div\">";
			foreach($groups as $tournament_id=>$tournament_groups) {
				$output .= "<div class=\"group-list-tname\">" . $tournament_groups[0]["tournament_name"] . "</div>";
				$output .= "<div class=\"group-list-header\">";
				$output .= "<div class=\"group-list-link header\">Group Name</div>";
				$output .= "<div class=\"group-list-entry header\">Entry Name</div>";
				$output .= "<div class=\"group-list-actionset header\">Actions</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
				$count = 0;
				$tournament_started = MarchMadness::tournament_started($tournament_id);
				foreach($tournament_groups as $temp_id=>$group_info) {
					$count++;
					if ($count == sizeof($tournament_groups)) {
						$bottomfix = " group-list-bottomfix";
					}
					else {
						$bottomfix = "";
					}
					$output .= "<div class=\"group-list-group{$bottomfix}\">";
					$link = $this->march_madness_group_link($group_info["id"]);
					$output .= "<div class=\"group-list-link\"><a href=\"{$link}\">" . MarchMadness::truncate_text($group_info["name"], 25) . "</a></div>";
					$output .= "<div class=\"group-list-entry\">" . MarchMadness::truncate_text($group_info["entry_name"], 25) . "</div>";
					$output .= "<div class=\"group-list-actionset\">";
					$output .= "<div class=\"group-list-action\">[ <a href=\"{$link}\">Standings</a></div>";
					$output .= "<div class=\"group-list-action\"> | <a href=\"" . MarchMadness::march_madness_link("entry_id={$group_info["entry_id"]}") . "\">Bracket</a></div>";
					if (($group_info["creator"] == $current_user_id || !($group_info["private"])) && !$tournament_started) {
						$output .= "<div class=\"group-list-action\"> | <a href=\"" . MarchMadnessGroupInvite::march_madness_group_link($group_info["id"]) . "\">Invite</a></div>";
						$output .= "<div class=\"group-list-action\"> | <a href=\"" . MarchMadnessGroupPoints::march_madness_points_link($group_info["id"]) . "\">Scoring</a></div>";
					}
					else {
						$output .= "<div class=\"group-list-action\"> | <a href=\"" . MarchMadnessGroupPoints::march_madness_points_link($group_info["id"]) . "\">Scoring</a></div>";
					}
					$output .= "<div class=\"group-list-action\"> | <a href=\"" . MarchMadnessUpdate::march_madness_update($group_info["entry_id"]) . "\">Edit</a> ]</div>";
					$output .= "<div class=\"cleared\"></div>";
					$output .= "</div>";
					$output .= "<div class=\"cleared\"></div>";
					$output .= "</div>";
					
				}
				$output .= "<br/>";
			}
			if (!sizeof($groups)) {
				$output .= "You are not in any groups. Click one of the links above to create a new group or search for an existing group to join.";
			}
			
			$output .= "</div>";
			$output .= "</div>";
			
			$output .= "<div class=\"silliness-right\">";
			$output .= MarchMadness::get_right_column();
			$output .= "</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
			
			
			
			$wgOut->setPageTitle("{$current_user_name}'s Groups");
			$wgOut->addHTML($output);
		}
		else {
			//$group_members = MarchMadness::get_group_members_from_db("", $value);
			//$group_name = $this->get_group_name_from_db($value);
			$start_div_output .= "<div class=\"silliness-container\">";
			$start_div_output .= "<div class=\"silliness-left\">";

			$group_info = MarchMadness::get_group_info_from_db($value);
			
			if (!sizeof($group_info)) {
				$link = $this->march_madness_group_link();

				//$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$output .= "<div class=\"madness-nav-link\"><a href=\"" . $link . "\">" . wfMsg("mm_back_to_home") . "</a></div>";
				$output .= "<div class=\"cleared\"></div>";

				
				$wgOut->setPageTitle("This is not a valid group");
				$wgOut->addHTML($output);
				return "";
			}
			$group_name = $group_info["group_name"];
			$group_standings = MarchMadness::get_group_standings_from_db($value);
			
			$full_size = sizeof($group_standings);
			
			$standings_page_size = MarchMadnessGroupAdmin::standings_page_size();
			$standings_desc_output = "";
			$standings_nav_output = "";
			if(sizeof($group_standings) > $standings_page_size) {
				if ($wgRequest->getVal("page")) {
					$page = intval($wgRequest->getVal("page"));
				}
				else {
					$page = 1;
				}
				
				$standings_chunk_array = array_chunk($group_standings, $standings_page_size, true);
				if (isset($standings_chunk_array[$page-1])) {
					$group_standings = $standings_chunk_array[$page-1];
				}
				else {
					$group_standings = $standings_chunk_array[0];
				}
				$page_start = ((($page-1) * $standings_page_size)+1);
				$page_end = $page_start + (sizeof($group_standings)-1);
				$standings_desc_output .= "Entries {$page_start} - {$page_end} of {$full_size}.";
				
				$standings_nav_output .= "Page: ";
				//$limit = ($full_size/$standings_page_size) + ($full_size%$standings_page_size ? 1 : 0);
				$limit = ($full_size/$standings_page_size);
				for ($i=0; $i<$limit; $i++) {
					if($i==($page-1)) {
						$standings_nav_output .= " {$page} ";
					}
					else {
						$next_nav_link = MarchMadnessGroupAdmin::march_madness_group_link($value, "page=" . ($i+1));
						$standings_nav_output .= " <a href=\"{$next_nav_link}\">" . ($i+1) . "</a> "; 
					}
				}
				$standings_nav_output = trim($standings_nav_output);
				
			}
			
			
			
			$description_output = "<div class=\"madness-group-desc\">" . ($group_info["group_description"] ? "\"{$group_info["group_description"]}\"" : "") . "</div>";
			
			//$link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupInvite")->escapeFullUrl();
			$link = MarchMadnessGroupInvite::march_madness_group_link();
			
			/*
			$button_output .= "<div class=\"madness-nav-link\">";
			if(!MarchMadness::tournament_started($group_info["tournament_id"]) && (!$group_info["private"] || ($group_info["private"] && $group_info["creator"]==$current_user_id))){
				
				//$button_output .= "<input type=\"button\" class=\"site-button\" value=\"Invite People\" onclick=\"location.href='{$link}/{$value}'\" />					";
				$button_output .= "<a href=\"{$link}/{$value}\">" . wfMsg("mm_invite_others") . "</a> - ";
			}
			*/
			$output .= "<div class=\"group-standings-desc\">{$standings_desc_output}</div>";
			$output .= "<div id=\"group-standings\">";
			$output .= "<div class=\"group-standings-header\">";
			$output .= "<div class=\"group-standings-rank header\">&nbsp;</div>";
			$output .= "<div class=\"group-standings-link header\">Entry</div>";
			$output .= "<div class=\"group-standings-user header\">Username</div>";
			$output .= "<div class=\"group-standings-points header\">Pts</div>";
			$output .= "<div class=\"group-standings-points header\">Max</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";

			$my_entry_id=0;
			$count = 0;
			$rank_count = "";
			$last_points = 0;
			$output .= "<div id=\"group-standings-div\">";
			foreach($group_standings as $entry_id=>$entry_info) {
				
				$count++;
				
				if ($entry_info["points"] != $last_points) {
					$rank_count = $count . ". ";
					$last_points = $entry_info["points"];
				}
				else {
					$rank_count = "&nbsp;";
				}
				if ($count == sizeof($group_standings)) {
					$bottomfix = " group-list-bottomfix";
				}
				else {
					$bottomfix = "";
				}
				$output .= "<div class=\"group-standings-group{$bottomfix}\">";
				$output .= "";
				
				
					
				//echo $entry_info["user_id"] . " " . $current_user_id . "<br/>";
				/*
				if(intval($entry_info["user_id"]) == $current_user_id) {
					$my_entry_id = $entry_id;
				}
				*/
				
				$avatar = new wAvatar($entry_info["user_id"],"s");
				$avatarID = $avatar->getAvatarImage();
				
				$user = User::newFromId($entry_info["user_id"]);
				$user_link = $user->getUserPage();
				
				$query = "entry_id={$entry_id}";
				$member_link = MarchMadness::march_madness_link($query);
				$output .= "<div class=\"group-standings-rank\">{$rank_count}</div>";
				$output .= "<div class=\"group-standings-link\"><a href=\"" . $user_link->getFullUrl() . "\"><img src=\"{$wgUploadPath}/avatars/{$avatarID}\" style=\"border:1px solid #d7dee8;\"/></a> <a href=\"{$member_link}\">" . MarchMadness::truncate_text($entry_info["entry_name"], 30) . "</a></div>";
				$output .= "<div class=\"group-standings-user\"><a href=\"" . $user_link->getFullUrl() . "\">" . MarchMadness::truncate_text($entry_info["user_name"], 20) . "</a></div>";
				$output .= "<div class=\"group-standings-points\">{$entry_info["points"]}</div>";
				$output .= "<div class=\"group-standings-points\">{$entry_info["max_points"]}</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
			}
			$output .= "</div>";
			$output .= "<div class=\"standings-nav-output\">{$standings_nav_output}</div>";
			
			$user_groups = MarchMadnessGroupAdmin::get_user_groups($current_user_id);
			
			foreach($user_groups[$group_info["tournament_id"]] as $ug_tid=>$ug_info) {
				if (intval($ug_info["id"]) == intval($value)){
					$my_entry_id = intval($ug_info["entry_id"]);
				}
			}
			
			if (!$my_entry_id && $group_info["private"]) {
				$link = $this->march_madness_group_link();
				$join_link = MarchMadnessGroupJoin::march_madness_join_link($value);
				$output = "You are not a member of this group.  Please return to your home page.";
				//$output .= "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$output .= "<div class=\"madness-nav-link\">";
				$output .= "<a href=\"" . $link . "\">" . wfMsg("mm_back_to_home") . "</a>";
				$output .= " - <a href=\"" . $join_link . "\">" . wfMsg("mm_join_this_group") . "</a> (But only if you have been invited... you will be asked for a password).";
				$output .= "</div>";
				
				$wgOut->setPageTitle("You are not a member of this group");
				$wgOut->addHTML($output);
				return "";
			}
			
			/*
			//$output .= "<h2>" . $group_name . "</h2>"; 
			foreach ($group_members as $member_entry=>$member) {
				$query = "entry_id={$member_entry}";
				$member_link = MarchMadness::march_madness_link($query);
				//$member = User::newFromID($member_id);
				$output .= "<a href=\"{$member_link}\">{$member}</a><br/>";
			}
			*/
			$output .= "</div>";

/*			
			$output .= "<br/><a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">Back to group admin page</a>";
			if ($my_entry_id) {
				$output .= " | <a href=\"" . MarchMadness::march_madness_link("entry_id={$my_entry_id}") . "\">My bracket</a>";
			}
			else {
				$output .= " | <a href=\"" . MarchMadnessGroupJoin::march_madness_join_link($value) . "\">Join This Group</a>";
			}
*/
			$button_output .= "<div class=\"madness-nav-link\">";
			if(!MarchMadness::tournament_started($group_info["tournament_id"]) && $my_entry_id && (!$group_info["private"] || ($group_info["private"] && $group_info["creator"]==$current_user_id))){
				
				//$button_output .= "<input type=\"button\" class=\"site-button\" value=\"Invite People\" onclick=\"location.href='{$link}/{$value}'\" />					";
				$button_output .= "<a href=\"{$link}/{$value}\">" . wfMsg("mm_invite_others") . "</a> - ";
			}

			//$button_output .= " <input type=\"button\" class=\"site-button\" onclick=\"location.href='" . MarchMadnessGroupAdmin::march_madness_group_link() . "'\" value=\"Back To Admin\">";
			$button_output .= " <a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">" . wfMsg("mm_back_to_home") . "</a> - ";
			if ($my_entry_id) {
				//$button_output .= " <input type=\"button\" class=\"site-button\" onclick=\"location.href='" . MarchMadness::march_madness_link("entry_id={$my_entry_id}") . "'\" value=\"My Bracket\">";
				$button_output .= " <a href=\"" . MarchMadness::march_madness_link("entry_id={$my_entry_id}") . "\">" . wfMsg("mm_view_bracket") . "</a> - ";
				$button_output .= " <a href=\"" . MarchMadnessUpdate::march_madness_update($my_entry_id) . "\">" . wfMsg("mm_edit_entry") . "</a> - ";
			}
			else {
				//$button_output .= " <input type=\"button\" class=\"site-button\" onclick=\"location.href='" . MarchMadnessGroupJoin::march_madness_join_link($value) . "'\" value=\"Join This Group\">";
				$button_output .= " <a href=\"" . MarchMadnessGroupJoin::march_madness_join_link($value) . "\">" . wfMsg("mm_join_this_group") . "</a> - ";
			}
			$button_output .= " <a href=\"" . MarchMadnessGroupPoints::march_madness_points_link($value) . "\">" . wfMsg("mm_view_scoring") . "</a>";
			

			$button_output .= "</div>";
			//$output = $description_output . ($my_entry_id ? $button_output : "") . $output;
			
			$output = $start_div_output . $description_output . $button_output . $output;
	
			$output .= "</div>";
			
			$output .= "<div class=\"silliness-right\">";
			$output .= MarchMadness::get_right_column();
			$output .= "</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
			
			
			$wgOut->setPageTitle("{$group_name} Standings");
			$wgOut->addHTML($output);
			
		}
	}
	
	function get_user_groups($current_user_id) {
		global $wgMemc;
		
		$groups = array();
		
		$key = wfMemcKey( 'marchmadness', 'usergroups', $current_user_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache()){
			wfDebug( "Cache Hit - Got user groups ({$key}) from cache (size: " .sizeof($data). ")\n" );
			$groups = $data;
		}else{
			
			wfDebug( "Cache Miss - Got user groups ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			
			$sql = "SELECT group_name, madness_tournament_group.group_id, madness_tournament_setup.tournament_id, tournament_name, creator, private as is_private, entry_id, entry_name FROM madness_tournament_group, madness_tournament_setup, madness_tournament_entry WHERE madness_tournament_group.tournament_id=madness_tournament_setup.tournament_id AND madness_tournament_group.group_id=madness_tournament_entry.group_id AND madness_tournament_entry.user_id={$current_user_id} ORDER BY tournament_id ASC, group_id ASC";
			
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				  $groups[$row->tournament_id][] = array("name"=>$row->group_name,"id"=>$row->group_id,"tournament_name"=>$row->tournament_name,"tournament_id"=>$row->tournament_id,"creator"=>$row->creator,"private"=>$row->is_private,"entry_id"=>$row->entry_id,"entry_name"=>$row->entry_name);
				  
			  }
			  
			  $wgMemc->set( $key, $groups );
		}
		
		//mysql_close($conn);
		
		return $groups;
	}
	
	function get_group_name_from_db($group_id) {
		global $wgMemc;
		
		$group_name = "";
		
		$key = wfMemcKey( 'marchmadness', 'groupname', $group_id );
		
		//$wgMemc->delete( $key );
		
		$data = $wgMemc->get( $key );
		if( $data && MarchMadness::madness_use_cache() ){
			wfDebug( "Cache Hit - Got group name ({$key}) from cache (" . $data . ")\n" );
			$group_name = $data;
		}else{
			
			wfDebug( "Cache Miss - Got group name ({$key}) from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			$sql = "SELECT group_name FROM madness_tournament_group WHERE madness_tournament_group.group_id={$group_id}";
			
			$res = $dbr->query($sql);
			if ($row = $dbr->fetchObject( $res ) ) {
			  
				  $group_name = $row->group_name;
				  $wgMemc->set( $key, $group_name );

			  }
		
		}
		return $group_name;
		
	}
}


SpecialPage::addPage( new MarchMadnessGroupAdmin );
	
}

?>
