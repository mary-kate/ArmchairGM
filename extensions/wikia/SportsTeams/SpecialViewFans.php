<?php

$wgExtensionFunctions[] = 'wfSpecialViewFans';


function wfSpecialViewFans(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ViewFans extends SpecialPage {
	
		function ViewFans(){
			UnlistedSpecialPage::UnlistedSpecialPage("ViewFans");
		}
		
		function execute(){
			
			global $wgUser, $wgOut, $wgRequest, $wgTitle, $IP;
		
			
			$output = "";
			
			/*/
			/* Get querystring variables
			/*/				

			$page =  $wgRequest->getVal('page');
			$sport_id =  $wgRequest->getVal('sport_id');
			$team_id =  $wgRequest->getVal('team_id');

			/*/
			/* Error message for username that does not exist (from URL)
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
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsTeams/SportsTeams.css?{$wgStyleVersion}\"/>\n");
			
			$relationships = array();
			$friends = array();
			$foes = array();
			if($wgUser->isLoggedIn()){
				$friends = $this->getRelationships(1);
				$foes = $this->getRelationships(2);
				$relationships = array_merge($friends,$foes);
			}
			/*/
			/* Set up config for page / default values
			/*/	
			if(!$page)$page=1;
			$per_page = 50;
			$per_row = 2;
			
			if($team_id){
				$team = SportsTeams::getTeam($team_id);
				$this->network = $team["name"];
				$team_image = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($team_id,"l") . "\" border=\"0\" alt=\"" . wfMsgForContent( 'st_network_alt_logo' ) . "\" />";
				
			}else{
				$sport = SportsTeams::getSport($sport_id);
				$this->network = $team["name"];
				$team_image = "<img src=\"images/team_logos/" . SportsTeams::getSportLogo($sport_id,"l") . "\" border=\"0\" alt=\"" . wfMsgForContent( 'st_network_alt_logo' ) . "\" />";
			}
			$homepage_title = Title::makeTitle( NS_SPECIAL  , "FanHome" );
			
			$total = SportsTeams::getUserCount($sport_id,$team_id);
			

			
			/*
			Get all fans
			*/
			 
			 
			$fans = SportsTeams::getUsersByFavorite($sport_id,$team_id,$per_page,$page);
			
			$output .= $wgOut->setPagetitle( wfMsgForContent('st_network_network_fans', $this->network) );
			
			
			$output .= "<div class=\"friend-links\">";
			 $output .= "<a href=\"". $homepage_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}") ."\">" . wfMsgForContent('st_network_back_to_network', $this->network) . "</a>";		  
			 $output .= "</div>";
			
			/*
			show total fan count
			*/
			$output .= "<div class=\"friend-message\">
					" . wfMsgExt('st_network_num_fans', 'parsemag', $this->network, $total, Title::MakeTitle(NS_SPECIAL, "InviteContacts")->escapeFullUrl() );
			$output .= "</div>";
			
			
	
			if($fans){
				$x = 1;
				foreach ($fans as $fan) {
					
					$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
					$avatar = new wAvatar($fan["user_id"],"l");
					$avatar_img = $avatar->getAvatarURL();
					
					$output .= "<div class=\"relationship-item\">
					    <div class=\"relationship-image\"><a href=\"{$user->getFullURL()}\">{$avatar_img}</a></div>
					      <div class=\"relationship-info\">
					      <div class=\"relationship-name\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a>";
					      
					      $output .= "</div>
					      <div class=\"relationship-actions\">";
					      if(in_array($fan["user_id"],$friends)){
							$output .= "	<span class=\"profile-on\">" . wfMsgForContent( 'st_your_friend' ) . "</span> ";		
					      }
					      if(in_array($fan["user_id"],$foes)){
							$output .= "	<span class=\"profile-on\">" . wfMsgForContent( 'st_your_foe' ) . "</span> ";		
					      }
					      if($fan["user_name"]!=$wgUser->getName()){
						      if(!in_array($fan["user_id"],$relationships) ){
							$output .= "<a href=\"" . Title::makeTitle(NS_SPECIAL, "AddRelationship")->escapeFullUrl("user={$fan["user_name"]}&rel_type=1") . "\">" . wfMsgForContent( 'st_add_as_friend' ) . "</a> | ";
							$output .= "<a href=\"" . Title::makeTitle(NS_SPECIAL, "AddRelationship")->escapeFullUrl("user={$fan["user_name"]}&rel_type=2") . "\">" . wfMsgForContent( 'st_add_as_foe' ) . "</a> | ";
							
						      }
						      $output .= "<a href=\"" . Title::makeTitle(NS_SPECIAL, "GiveGift")->escapeFullUrl("user={$fan["user_name"]}") . "\">" . wfMsgForContent( 'st_give_a_gift' ) . "</a> ";
						      //$output .= "<p class=\"relationship-link\"><a href=\"index.php?title=Special:ChallengeUser&user={$fan["user_name"]}\"><img src=\"images/common/challengeIcon.png\" border=\"0\" alt=\"issue challenge\"/> issue challenge</a></p>";
						      $output .= "<div class=\"cleared\"></div>";
						     
						      if(in_array($fan["user_id"],$relationships) && $user_name == $wgUser->getName()){
							      $output .= " | <a href=\"" . Title::makeTitle(NS_SPECIAL, "RemoveRelationship")->escapeFullUrl("user={$user->getText()}") . "\">" . wfMsgForContent( 'st_remove_relationship', $label ) . "</a>";
						      }
					      }
					      $output .= "</div>";
					 
					  $output .= "<div class=\"cleared\"></div></div>";
					  
					$output .= "</div>";
					 if($x==count($fans) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
					$x++;
				}
			}

			/**/
			/*BUILD NEXT/PREV NAV
			**/
			$numofpages = $total / $per_page; 
	
			if($numofpages>1){
				$output .= "<div class=\"page-nav\">";
				if($page > 1){ 
					$output .= "<a href=\"" . $wgTitle->escapeFullURL("page=" . ($page-1) . "&sport_id={$sport_id}&team_id={$team_id}") . "\">" . wfMsgForContent( 'st_prev' ) . "</a> ";
				}
				
				
				if(($total % $per_page) != 0)$numofpages++;
				if($numofpages >=9)$numofpages=9+$page;
				
				for($i = 1; $i <= $numofpages; $i++){
					if($i == $page){
					    $output .=($i." ");
					}else{
					    $output .="<a href=\"" . $wgTitle->escapeFullURL("page=" . ($i) . "&sport_id={$sport_id}&team_id={$team_id}") . "\">$i</a> ";
					}
				}
		
				if(($total - ($per_page * $page)) > 0){
					$output .=" <a href=\"" . $wgTitle->escapeFullURL("page=" . ($page+1) . "&sport_id={$sport_id}&team_id={$team_id}") . "\">" . wfMsgForContent( 'st_next' ) . "</a>"; 
				}
				$output .= "</div>";
			}
			/**/
			/*BUILD NEXT/PREV NAV
			**/
			
			$wgOut->addHTML($output);
			
		}
		
		function getRelationships($rel_type){
			global $wgUser, $IP;
			require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
			$rel = new UserRelationship($wgUser->getName());
			$relationships = $rel->getRelationshipIDs($rel_type);
			return $relationships;		
		}

	}

	SpecialPage::addPage( new ViewFans );

}

?>
