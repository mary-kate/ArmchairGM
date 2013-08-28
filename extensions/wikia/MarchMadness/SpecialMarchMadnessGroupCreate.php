<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessGroupCreate';

function wfSpecialMarchMadnessGroupCreate(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessGroupCreate extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessGroupCreate");

	}
	
	function march_madness_group_create_link($query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessGroupCreate")->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessGroupCreate")->escapeFullUrl($query);
	}

	function execute($value) {
		global $wgUser, $wgOut, $wgTitle, $wgStyleVersion;
		
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

		$link = MarchMadnessGroupAdmin::march_madness_group_link();
		
		$output = "<div id=\"madness-group-create\">
		
			<div id=\"madness-group-create-errors\" style=\"display:none; color:red; font-weight:bold;\"></div>
			<div class=\"madness-message\">
				Fill in the form below to create your tournament group.  Its that easy.
			</div>
			<div id=\"madness-group-create-errors\" style=\"display:none; color:red; font-weight:bold;\"></div>";
			
			if ($value) {
				$tourney_select = $this->get_tournaments_dropdown("tournament_id", $value);
			}
			else {
				$tourney_select = $this->get_tournaments_dropdown("tournament_id");
			}
			
			if ($tourney_select != "") {
				$output .= $tourney_select;
			}
			else {
				$link = MarchMadnessGroupAdmin::march_madness_group_link();

				//$output = "<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"Back To Admin\">";
				$error_output .= "<div class=\"madness-nav-link\"><a href=\"" . $link . "\">" . wfMsg("mm_back_to_home") . "</a></div>";
				$error_output .= "<div class=\"cleared\"></div>";

				
				$wgOut->setPageTitle("This is not a valid tournament id");
				$wgOut->addHTML($error_output);
				return "";
			}
			
			$output .= "<div class=\"madness-title\">
				Tournament Group Name
			</div>
			<div class=\"madness-input\">
				<input type=\"text\" id=\"group_name\" name=\"group_name\" maxlength=\"50\" /> (Max 50 char.)
			</div>
			<div class=\"madness-title\">
				Group Description
			</div>
			<div class=\"madness-input\">
				<textarea id=\"group_desc\" name=\"group_desc\" ></textarea> (Max 255 char.)
			</div>
			<div class=\"madness-title\">
				Team Name
			</div>
			<div class=\"madness-input\">
				<input type=\"text\" id=\"entry_name\" name=\"entry_name\" maxlength=\"50\" /> (Max 50 char.)
			</div>
			<div class=\"madness-title\">
				Type of Group 
			</div>
			<div class=\"madness-input\">
				<select id=\"is_private\" name=\"is_private\" onchange=\"toggle_password();\"><option value=\"0\">Public</option><option value=\"1\">Private</option></select>
			</div>
			<div id=\"password-entry\"  style=\"display:none;\">
				<div class=\"madness-title\">
					Password
				</div>
				<div class=\"madness-input\">
					<input type=\"password\" id=\"password\" name=\"password\" />
				</div>
				<div class=\"madness-title\">
					Confirm
				</div>
				<div class=\"madness-input\">
					<input type=\"password\" id=\"confirm-password\" name=\"confirm-password\" />
				</div>
			</div>";
			
			
			$output .= "<div class=\"madness-button\">
				<input type=\"button\" class=\"site-button\" onclick=\"create_group();\" value=\"" . wfMsg("mm_create") . "\"> 
				<input type=\"button\" class=\"site-button\" onclick=\"location.href='$link'\" value=\"" . wfMsg("mm_back_to_home") . "\">
			</div>";
		$output .= "</div>";
		
		$wgOut->setPageTitle("Create a Tournament Group");
		$wgOut->addHTML($output);
		
	}
	
	
	function get_tournaments_dropdown($select_id, $value=0) {
		
		if ($value==0) {
			$return_select = "<div class=\"madness-title\">
							Tournament Name
						</div>
						<div class=\"madness-input\">
						<select id=\"{$select_id}\" name=\"{$select_id}\">";
			$dbr =& wfGetDB( DB_SLAVE );
			
			$sql = "SELECT tournament_id, tournament_name from madness_tournament_setup ORDER BY tournament_id ASC";
			
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
			  
				$return_select .= "<option value=\"{$row->tournament_id}\">{$row->tournament_name}</option>";
				  
			  }
			
			//mysql_close($conn);
			
			$return_select .= "</select></div>";
		}
		else {
			$dbr =& wfGetDB( DB_SLAVE );
			
			$sql = "SELECT tournament_id, tournament_name from madness_tournament_setup WHERE tournament_id={$value} ORDER BY tournament_id ASC";
			
			$res = $dbr->query($sql);
			if ($row = $dbr->fetchObject( $res ) ) {
				
				$return_select = "<input type=\"hidden\" value=\"{$row->tournament_id}\" name=\"{$select_id}\" id=\"{$select_id}\" />";
				$return_select .= "<div class=\"madness-title\">
							Tournament Name
						</div>
						<div class=\"madness-input\">
						" . $row->tournament_name . "
						</div>";
				//$return_select .= "<option value=\"{$row->tournament_id}\">{$row->tournament_name}</option>";
				  
			  }
			  else {
				  $return_select = "";
			  }
			
			
			//$return_select = "<input type=\"hidden\" value=\"1\" name=\"{$select_id}\" id=\"{$select_id}\" />";
			
		}
		
		
		
		return $return_select;
	}
	
}


SpecialPage::addPage( new MarchMadnessGroupCreate );
	
}

?>
