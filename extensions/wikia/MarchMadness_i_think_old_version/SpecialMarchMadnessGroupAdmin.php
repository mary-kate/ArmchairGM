<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupAdmin';

function wfSpecialMarchMadnessGroupAdmin(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupAdmin extends SpecialPage {
	
	function MarchMadnessGroupAdmin(){
		UnlistedSpecialPage::UnlistedSpecialPage("MarchMadnessGroupAdmin");

	}
	
	function march_madness_group_link($value=0, $query="") {
		return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupAdmin" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value) {

		global $wgUser, $wgOut;
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);

		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");
		
		if (!$value) {
			$link = Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupCreate")->escapeFullUrl();
			$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Create New Group\"><br/><br/>";
			
			$groups = $this->get_user_groups($current_user_id);
			$output .= "<div id=\"group-list-div\">";
			foreach($groups as $tournament_id=>$tournament_groups) {
				$output .= $tournament_groups[0]["tournament_name"] . "<br/>";
				foreach($tournament_groups as $temp_id=>$group_info) {
					$link = $this->march_madness_group_link($group_info["id"]);
					$output .= "<a href=\"{$link}\">{$group_info["name"]}</a><br/>";
				}
			}
			$output .= "</div>";
			$wgOut->setPageTitle("{$current_user_name}'s Groups");
			$wgOut->addHTML($output);
		}
		else {
			$group_members = MarchMadness::get_group_members_from_db("", $value);
			$group_name = $this->get_group_name_from_db($value);
			
			$output .= "<div id=\"group-nav\">";
			//$output .= "<h2>" . $group_name . "</h2>"; 
			foreach ($group_members as $member_entry=>$member) {
				$query = "entry_id={$member_entry}";
				$member_link = MarchMadness::march_madness_link($query);
				//$member = User::newFromID($member_id);
				$output .= "<a href=\"{$member_link}\">{$member}</a><br/>";
			}
			$output .= "</div>";
			
			$wgOut->setPageTitle("{$group_name} Members");
			$wgOut->addHTML($output);
			
		}
	}
	
	function get_user_groups($current_user_id) {
		$groups = array();
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT group_name, madness_tournament_group.group_id, madness_tournament_setup.tournament_id, tournament_name, creator, private as is_private FROM madness_tournament_group, madness_tournament_setup, madness_tournament_entry WHERE madness_tournament_group.tournament_id=madness_tournament_setup.tournament_id AND madness_tournament_group.group_id=madness_tournament_entry.group_id AND madness_tournament_entry.user_id={$current_user_id} ORDER BY tournament_id ASC, group_id ASC";
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			  $groups[$row->tournament_id][] = array("name"=>$row->group_name,"id"=>$row->group_id,"tournament_name"=>$row->tournament_name,"tournament_id"=>$row->tournament_id,"creator"=>$row->creator,"private"=>$row->is_private);
			  
		  }
		
		//mysql_close($conn);
		
		return $groups;
	}
	
	function get_group_name_from_db($group_id) {
		$group_name = "";
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT group_name FROM madness_tournament_group WHERE madness_tournament_group.group_id={$group_id}";
		
		$res = $dbr->query($sql);
		if ($row = $dbr->fetchObject( $res ) ) {
		  
			  $group_name = $row->group_name;
		   
		  }
		
		//mysql_close($conn);
		
		return $group_name;
		
	}
}


SpecialPage::addPage( new MarchMadnessGroupAdmin );
	
}

?>
