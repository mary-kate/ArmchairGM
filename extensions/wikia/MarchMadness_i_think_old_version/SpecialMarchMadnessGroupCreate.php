<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupCreate';

function wfSpecialMarchMadnessGroupCreate(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupCreate extends SpecialPage {
	
	function MarchMadnessGroupCreate(){
		UnlistedSpecialPage::UnlistedSpecialPage("MarchMadnessGroupCreate");

	}
	
	function march_madness_group_create_link($query="") {
		return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupCreate")->escapeFullUrl($query);
	}

	function execute($value) {
		global $wgUser, $wgOut;
		$current_user_name = $wgUser->getName();
		$current_user_id = User::idFromName($current_user_name);
	
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/MarchMadness/MarchMadness.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/MarchMadness/MarchMadness.js?{$wgStyleVersion}\"></script>\n");

		$output = "<div id=\"madness-group-create\">";
		
		$output .= "Group Name: <input type=\"text\" id=\"group_name\" name=\"group_name\" /><br/>";
		$output .= "Tournament: " . $this->get_tournaments_dropdown("tournament_id") . "<br/>";
		$output .= "Group Access: <select id=\"is_private\" name=\"is_private\"><option value=\"0\">Public</option><option value=\"1\">Private</option></select><br/><br/>";
		
		$output .= "Entry Name: <input type=\"text\" id=\"entry_name\" name=\"entry_name\" /><br/>";
		$output .= "<input type=\"button\" class=\"site-button\" onclick=\"create_group();\" value=\"Create Group\"><br/><br/>";

		$output .= "</div>";
		
		$wgOut->setPageTitle("Create Group");
		$wgOut->addHTML($output);
		
	}
	
	
	function get_tournaments_dropdown($select_id) {
		$return_select = "<select id=\"{$select_id}\" name=\"{$select_id}\">";
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT tournament_id, tournament_name from madness_tournament_setup ORDER BY tournament_id ASC";
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
		  
			$return_select .= "<option value=\"{$row->tournament_id}\">{$row->tournament_name}</option>";
			  
		  }
		
		//mysql_close($conn);
		
		$return_select .= "</select>";
		
		return $return_select;
	}
	
}


SpecialPage::addPage( new MarchMadnessGroupCreate );
	
}

?>
