<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessSearch';

function wfSpecialMarchMadnessSearch(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessSearch extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessSearch");

	}
	
	function march_madness_search_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessSearch" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessSearch" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value) {

		global $wgUser, $wgOut, $wgRequest, $wgFriendingEnabled, $IP, $wgStyleVersion;

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		
		if (!$wgUser->isLoggedIn()) {
			/*
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
			*/
			return MarchMadness::not_logged_in();

		}
		
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);

		$output = "";
		$output .= "<div class=\"silliness-container\">";
		$output .= "<div class=\"silliness-left\">";

		$link = MarchMadnessGroupAdmin::march_madness_group_link();
		
		$list_all_link = $this->march_madness_search_link(0, "q=*");
		
		$output .= "<div class=\"madness-nav-link\">
			<a href=\"{$link}\" rel=\"nofollow\">" . wfMsg("mm_back_to_home") . "</a> -
			<a href=\"{$list_all_link}\" rel=\"nofollow\">" . wfMsg("mm_view_groups") . "</a>
		</div>
		<div class=\"group-search-div\">
		<form method=\"GET\" action=\"" . $this->march_madness_search_link() . "\">
			Search for a group: <input type=\"text\" id=\"q\" name=\"q\" value=\"" . $wgRequest->getVal("q") . "\" />
			<input type=\"submit\" class=\"site-button\" value=\"Search\" />
			<br/>	
		</form>";
			
		if ($wgRequest->getVal("q")) {
			$search_term = $wgRequest->getVal("q");
		}
		else {
			$search_term = "*";
		}
			
			$search_results = $this->doSearch($search_term);
			
			$output .= "<h2>Search Results</h2>";
			if(!sizeof($search_results)) {
				$output .= "No results found";
			}
			else {
				$output .= "<div class=\"search-result-header\">";
				$output .= "<div class=\"search-result-link header\">Group Name:</div>";
				$output .= "<div class=\"search-result-members header\">Size:</div>";
				$output .= "<div class=\"search-result-creator header\">Created By:</div>";
				$output .= "<div class=\"search-result-type header\">Type:</div>";
				$output .= "<div class=\"search-result-description header\">Description:</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
			}
			$count = 0;
			foreach($search_results as $group_id=>$group_info) {
				
				$count++;
				if ($count == sizeof($search_results)) {
					$bottomfix = " search-result-bottomfix";
				}
				else {
					$bottomfix = "";
				}
					
				$user = User::newFromName($group_info["creator_name"]);
				$user_link = $user->getUserPage();
				
				$output .= "<div class=\"search-result-item{$bottomfix}\">";
				$output .= "<div class=\"search-result-link\"><a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link($group_id) . "\" title=\"{$group_info["group_name"]}\">" . MarchMadness::truncate_text($group_info["group_name"], 25) . "</a></div>";
				$output .= "<div class=\"search-result-members\">{$group_info["members"]}</div>";
				$output .= "<div class=\"search-result-creator\"><a href=\"" . $user_link->getFullUrl() . "\">" . MarchMadness::truncate_text($group_info["creator_name"], 15) . "</a></div>";
				$output .= "<div class=\"search-result-type\">" . ($group_info["private"] ? "Private" : "Public") . "</div>";
				$output .= "<div class=\"search-result-description\">" . MarchMadness::truncate_text($group_info["group_description"], 75) . "</div>";
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
			}
			
		
			
			
		$output .= "</div>";
		$output .= "</div>";
			
		$output .= "<div class=\"silliness-right\">";
		$output .= MarchMadness::get_right_column();
		$output .= "</div>";
		$output .= "<div class=\"cleared\"></div>";
		$output .= "</div>";
			
		$wgOut->setPageTitle("Search For A Group To Join");
		$wgOut->addHTML($output);
			
		
	}
	
	
	function doSearch($q) {
		
		$search_results = array();
		
		$dbr =& wfGetDB( DB_SLAVE );
		if ($q == "*" || trim($q) == "") {
			$sql = "SELECT madness_tournament_group.*, count(entry_id) as members FROM madness_tournament_group, madness_tournament_entry WHERE madness_tournament_group.group_id=madness_tournament_entry.group_id GROUP BY group_id ORDER BY group_name ASC";
		}
		else {
			$sql = "SELECT madness_tournament_group.*, count(entry_id) as members FROM madness_tournament_group, madness_tournament_entry WHERE (group_name LIKE '%{$q}%' OR group_description LIKE '%{$q}%' OR creator_name LIKE '%{$q}%') AND madness_tournament_group.group_id=madness_tournament_entry.group_id GROUP BY group_id ORDER BY group_name ASC";
		}
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			$search_results[$row->group_id] = array(
				"group_id"=>$row->group_id,
				"group_name"=>$row->group_name,
				"creator"=>$row->creator,
				"creator_name"=>$row->creator_name,
				"group_description"=>$row->group_description,
				"members"=>$row->members,
				"private"=>$row->private,
				);
		}
		return $search_results;
	}
	
	
}


SpecialPage::addPage( new MarchMadnessSearch );
	
}

?>
