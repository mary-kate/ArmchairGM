<?php

$wgExtensionFunctions[] = 'wfSpecialRemoveFan';


function wfSpecialRemoveFan(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class RemoveFan extends SpecialPage {
	
		function RemoveFan(){
			UnlistedSpecialPage::UnlistedSpecialPage("RemoveFan");
		}
		
		function execute(){
			
			global $wgUser, $wgOut, $wgRequest, $wgTitle, $IP;
		
			
			$output = "";
			
			/*/
			/* Get querystring variables
			/*/				

			$sport_id =  $wgRequest->getVal('sport_id');
			$team_id =  $wgRequest->getVal('team_id');

			/*/
			/* Error message for URL with no team and sport specified
			/*/			
			if(!$team_id && !$sport_id){
				$wgOut->setPagetitle( wfMsgForContent( 'st_network_woops_title' ) );
				$out .= "<div class=\"relationship-request-message\">" . wfMsgForContent( 'st_network_woops_text' ) . "</div>";
				$out .= "<div class=\"relationship-request-buttons\">";
				$out .= "<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_main_page' ) ."\" onclick=\"window.location='" . Title::makeTitle(NS_MAIN, "Main_Page")->escapeFullUrl() ."'\"/>";
				if ($wgUser->isLoggedIn()) {
					$out .= " <input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_your_profile' ) . "\" onclick=\"window.location='" . Title::makeTitle(NS_USER, $wgUser->getName())->escapeFullUrl() ."'\"/>";
				}
			  	$out .= "</div>";
				$wgOut->addHTML($out);
				return false;
			}	

			if($team_id){
				$team = SportsTeams::getTeam($team_id);
				$name = $team["name"];
			}else{
				$sport = SportsTeams::getSport($sport_id);
				$name = $sport["name"];
			}
				
			if($wgRequest->wasPosted()  ){
				$s = new SportsTeams();
				$s->removeFavorite($wgUser->getID(),$wgRequest->getVal("s_id"),$wgRequest->getVal("t_id"));
			 
				$view_fans_title = Title::makeTitle( NS_SPECIAL  , "ViewFans" );
				$invite_title = Title::makeTitle( NS_SPECIAL  , "InviteContacts" );
				
				$wgOut->setPagetitle( wfMsgForContent('st_network_no_longer_member', $name) );
				$output .= "<div class=\"give-gift-message\">
						<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_main_page') ."\" onclick=\"window.location='" . Title::makeTitle(NS_MAIN, "Main_Page")->escapeFullUrl() ."'\"/>
						<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_your_profile') ."\" onclick=\"window.location='" . Title::makeTitle(NS_USER, $wgUser->getName())->escapeFullUrl() ."'\"/>
						</div>";
						
			}else{
				/*/
				/* Error message if not a fan
				/*/			
				
				if( !SportsTeams::isFan($wgUser->getID(),$sport_id,$team_id)==true ){
					
					$wgOut->setPagetitle( wfMsgForContent( 'st_network_not_member', $name) );
					//out .= "<div class=\"relationship-request-message\">" . wfMsgForContent( 'st_network_no_need_join') ."</div>";
					$out .= "<div class=\"relationship-request-buttons\">";
					$out .= "<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_main_page') ."\" onclick=\"window.location='" . Title::makeTitle(NS_MAIN, "Main_Page")->escapeFullUrl() ."'\"/>";
					if ($wgUser->isLoggedIn()) {
						$out .= " <input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_your_profile') ."\" onclick=\"window.location='" . Title::makeTitle(NS_USER, $wgUser->getName())->escapeFullUrl() ."'\"/>";
					}
					$out .= "</div>";
					$wgOut->addHTML($out);
					return false;
				}
				$output .= $wgOut->setPagetitle( wfMsgForContent( 'st_network_leave', $name) );
				
				
				$output .= "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" name=\"form1\">
					 
					<div class=\"give-gift-message\" style=\"margin:0px 0px 0px 0px;\">
						" . wfMsgForContent( 'st_network_leave_are_you_sure', $name) ."
					</div>
							
					<div class=\"cleared\"></div>
					<div class=\"give-gift-buttons\">
					<input type=\"hidden\" name=\"s_id\" value=\"{$sport_id}\">
					<input type=\"hidden\" name=\"t_id\" value=\"{$team_id}\">
					<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_remove_me') ."\" size=\"20\" onclick=\"document.form1.submit()\" />
					<input type=\"button\" class=\"site-button\" value=\"" . wfMsg( 'cancel' ) . "\" size=\"20\" onclick=\"history.go(-1)\" />
					</div>
				  </form>";
				
			}

		
			
			$wgOut->addHTML($output);
			
		}
		

	}

	SpecialPage::addPage( new RemoveFan );

}

?>