<?php

$wgExtensionFunctions[] = 'wfSpecialSimilarFans';
$wgExtensionFunctions[] = 'wfSportsTeamsReadLang';

 function cut_link_text2($matches){
	$tag_open = $matches[1];
	$link_text = $matches[2];
	$tag_close = $matches[3];
	$max = 50;
	if( strlen($link_text ) > 10){
		$start = substr($link_text,0,($max/2)-3 );
		$end = substr($link_text,($max/2)-3,($max/2)-3);
		$link_text = trim($start) . "..." . trim($end);
	}
	return $tag_open . $link_text . $tag_close;
}

function wfSpecialSimilarFans(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class SimilarFans extends SpecialPage {
	
		function SimilarFans(){
			SpecialPage::SpecialPage("SimilarFans");
		}
		
		function execute(){
			
			global $wgUser, $wgOut, $wgRequest, $wgTitle, $IP;
	
			/*/
			/* Redirect Non-logged in users to Login Page
			/* It will automatically return them to the SimilarFans page
			/*/
			if($wgUser->getID() == 0 ){
				$wgOut->setPagetitle( "Woops!" );
				$login =  Title::makeTitle( NS_SPECIAL  , "UserLogin"  );
				$wgOut->redirect( $login->getFullURL("returnto=Special:SimilarFans") );
				return false;
			}
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsTeams/SportsTeams.css?{$wgStyleVersion}\"/>\n");
			$output = "";
			
			/*/
			/* Get querystring variables
			/*/				

			$page =  $wgRequest->getVal('page');
			
			
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
			
	
			
			$total = SportsTeams::getSimilarUserCount($wgUser->getID());
			

			
			/*
			Get all fans
			*/
			 
			 
			$fans = SportsTeams::getSimilarUsers($wgUser->getID(),$per_page,$page);
			
			$output .= $wgOut->setPagetitle( wfMsgForContent( 'st_similar_fans' ) );
			
			
			//$output .= "<div class=\"friend-links\">";
			// $output .= "<a href=\"{$homepage_title->getFullURL()}&sport_id={$sport_id}&team_id={$team_id}\">< Back to Network Home</a>";		  
			// $output .= "</div>";
			
			/*
			show total fan count
			*/
			$output .= "<div class=\"relationship-count\">
					" . wfMsgForContent( 'st_num_similar', $total ) . " <a href='" . Title::makeTitle(NS_SPECIAL, "InviteContacts")->escapeFullUrl() . "'>" . wfMsgForContent( 'st_invite_friends' ) . "</a>.";
			$output .= "</div>";
			
			
	
			if($fans){
				$x = 1;
				foreach ($fans as $fan) {
					
					$username_length = strlen($fan["user_name"]);
					$username_space = stripos($fan["user_name"], ' ');
					
					if (($username_space == false || $username_space >= "30") && $username_length > 30){
						$user_name_display = substr($fan["user_name"], 0, 30)." ".substr($fan["user_name"], 30, 50);
					}
					else {
						$user_name_display = $fan["user_name"];
					};
					
					$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
					$avatar = new wAvatar($fan["user_id"],"ml");
					$avatar_img = $avatar->getAvatarURL();
					
					$output .= "<div class=\"relationship-item\">
					    <div class=\"relationship-image\"><a href=\"{$user->getFullURL()}\">{$avatar_img}</a></div>
					      <div class=\"relationship-info\">
					      <div class=\"relationship-name\"><a href=\"{$user->getFullURL()}\">{$user_name_display}</a>";
					      
					      $output .= "</div>
					      <div class=\"relationship-actions\">";
					      if(in_array($fan["user_id"],$friends)){
							$output .= " <a href=\"" . Title::makeTitle(NS_SPECIAL, "RemoveRelationship")->escapeFullUrl("user={$user->getText()}") . "\">" . wfMsgForContent( 'st_remove_as_friend', $label ) . "</a> |";		
					      }
					      if(in_array($fan["user_id"],$foes)){
							$output .= " <a href=\"" . Title::makeTitle(NS_SPECIAL, "RemoveRelationship")->escapeFullUrl("user={$user->getText()}") . "\">" . wfMsgForContent( 'st_remove_as_foe', $label ) . "</a> |";		
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
					$output .= "<a href=\"{$wgTitle->getFullURL()}&page=" . ($page-1) . "\">" . wfMsgForContent( 'st_prev' ) . "</a> ";
				}
				
				
				if(($total % $per_page) != 0)$numofpages++;
				if($numofpages >=9)$numofpages=9+$page;
				
				for($i = 1; $i <= $numofpages; $i++){
					if($i == $page){
					    $output .=($i." ");
					}else{
					    $output .="<a href=\"{$wgTitle->getFullURL()}&page=$i\">$i</a> ";
					}
				}
		
				if(($total - ($per_page * $page)) > 0){
					$output .=" <a href=\"{$wgTitle->getFullURL()}&page=" . ($page+1) . "\">" . wfMsgForContent( 'st_next' ) . "</a>"; 
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

	SpecialPage::addPage( new SimilarFans );
	global $wgMessageCache,$wgOut;
	$wgMessageCache->addMessage( 'similarfans', 'Similar Fans' );
}
//read in localisation messages
function wfSportsTeamsReadLang(){
	global $wgMessageCache, $IP;
	require_once ( "SportsTeams.i18n.php" );
	foreach( efWikiaSportsTeams() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}

?>
