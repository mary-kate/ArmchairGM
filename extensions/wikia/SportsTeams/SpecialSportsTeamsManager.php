<?php

$wgExtensionFunctions[] = 'wfSpecialSportsTeamsManager';


function wfSpecialSportsTeamsManager(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");

	class SportsTeamsManager extends SpecialPage {
	
		function SportsTeamsManager(){
			UnlistedSpecialPage::UnlistedSpecialPage("SportsTeamsManager");
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$wgOut->setPagetitle( wfMsgForContent('st_team_manager_title') );
			if(! in_array('staff',($wgUser->getGroups())) ){
				//$wgOut->addHTML("Invalid Page");
				//return;
			}
		

			$css = "<style>
			.view-form {font-weight:800;font-size:12px;font-color:#666666}
			.view-status {font-weight:800;font-size:12px;background-color:#FFFB9B;color:#666666;padding:5px;margin-bottom:5px;}
			</style>";
			$wgOut->addHTML($css);
			
	 		if($wgRequest->wasPosted()){
				
	
				if(!($wgRequest->getVal("id"))){
					$dbw =& wfGetDB( DB_MASTER );					
					$dbw->insert( '`sport_team`',
						array(
							'team_sport_id' => $wgRequest->getVal("s_id"),
							'team_name' => $wgRequest->getVal("team_name")
						), __METHOD__
					);
					
					$id = $dbw->insertId();
					$wgOut->addHTML("<span class='view-status'>" . wfMsgForContent('st_team_manager_created') . "</span><br><br>");
				}else{
					$id = $wgRequest->getVal("id");
					$dbw =& wfGetDB( DB_MASTER );
					$dbw->update( '`sport_team`',
					array( /* SET */
						'team_sport_id' => $wgRequest->getVal("s_id"),
						'team_name' => $wgRequest->getVal("team_name")
						
					), array( /* WHERE */
						'team_id' => $id
					), ""
					);
					
					$wgOut->addHTML("<span class='view-status'>" . wfMsgForContent('st_team_manager_saved') . "</span><br><br>");
				}
				
				$wgOut->addHTML($this->displayForm($id));
			}else{
				$id = $wgRequest->getVal( 'id' );
				$sport_id = $wgRequest->getVal( 'sport_id' );
				if($id || $wgRequest->getVal( 'method' )=="edit"){
					$wgOut->addHTML($this->displayForm($id));
				}else{
					if(!$sport_id){
						$wgOut->addHTML($this->displaySportsList());
					}else{
						$wgOut->addHTML("<div><b><a href=\"" . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManager")->escapeFullUrl() . "\">" . wfMsgForContent('st_team_manager_view_sports') . "</a></b> | <b><a href=\"" . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManager")->escapeFullUrl("sport_id={$sport_id}&method=edit") . "\">" . wfMsgForContent('st_team_manager_add_new_team') . "</a></b></div><p>");
						$wgOut->addHTML($this->displayTeamList($sport_id));
					}
				}
			}
		}
		
		function displaySportsList(){
			$sports = SportsTeams::getSports();
			foreach ($sports as $sport) {
				$output .= "<div class=\"Item\" >
				<a href=\"" . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManager")->escapeFullUrl("sport_id={$sport["id"]}") . "\">{$sport["name"]}</a>
					</div>\n";
			}
		
			$output .= "</div>";
			return "<div id=\"views\">" . $output . "</div>";
		}

		function displayTeamList($sport_id){
			$teams = SportsTeams::getTeams($sport_id);
			foreach ($teams as $team) {
				$output .= "<div class=\"Item\" >
						<a href=\"" . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManager")->escapeFullUrl("method=edit&sport_id={$sport_id}&id={$team["id"]}") . "\">{$team["name"]}</a>
					</div>\n";
			}
		
			$output .= "</div>";
			return "<div id=\"views\">" . $output . "</div>";
		}
		
		function displayForm($id){
			global $wgUser, $wgRequest;
			$form .= "<div><b><a href=\"" . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManager")->escapeFullUrl("sport_id=" .$wgRequest->getVal("sport_id")) . "\">" . wfMsgForContent('st_team_manager_view_teams') . "</a></b></div><p>";
			
			if($id)$team = SportsTeams::getTeam($id);

			
			$form .=  '<form action="" method="POST" enctype="multipart/form-data" name="gift">';
			
			$form .= '<table border="0" cellpadding="5" cellspacing="0" width="500">';
			
			$form .=  '
		
			<tr>
			<td width="200" class="view-form">' . wfMsgForContent('st_team_manager_sport') . '</td>
			<td width="695">
				<select name="s_id">';
				$sports = SportsTeams::getSports();
				foreach($sports as $sport){
					$form .= '<option ' . (($wgRequest->getVal("sport_id")== $sport["id"] || $sport["id"] == $team["sport_id"])?"selected":"") . " value=\"{$sport["id"]}\">{$sport["name"]}</option>";
				}
				$form .= '</select>
		
			</tr><tr>
			<td width="200" class="view-form">' . wfMsgForContent('st_team_manager_teamname') . '</td>
			<td width="695"><input type="text" size="45" class="createbox" name="team_name" value="'. $team["name"] . '"/></td>
			</tr>
			';
		
			if($id){
				$team_image = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($id,"l") . "\" border=\"0\" alt=\"logo\" />";
				$form .=  '<tr>
				<td width="200" class="view-form" valign="top">' . wfMsgForContent('st_team_manager_team') . '</td>
				<td width="695">' . $team_image . '
				<p>
				<a href="' . Title::MakeTitle(NS_SPECIAL, "SportsTeamsManagerLogo")->escapeFullUrl("id={$id}") . '">' . wfMsgForContent('st_team_manager_add_replace_logo') . '</a>
				</td>
				</tr>';
			}
			
			$form .=  '<tr>
			<td colspan="2">
			<input type=hidden name="id" value="' . $id . '">
			<input type="button" class="site-button" value="' . (($id)?wfMsgForContent('st_team_manager_edit'):wfMsgForContent('st_team_manager_add_team')) . '" size="20" onclick="document.gift.submit()" />
			<input type="button" class="site-button" value="cancel" size="20" onclick="history.go(-1)" />
			</td>
			</tr>
			</table>
			
			</form>';
			return $form;
		}
	}

	SpecialPage::addPage( new SportsTeamsManager );
}

?>