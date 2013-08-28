<?php

$wgExtensionFunctions[] = 'wfSpecialUserStatus';
$wgExtensionFunctions[] = 'wfUserStatusReadLang';

function wfSpecialUserStatus(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class ViewUserStatus extends SpecialPage {

	
	function ViewUserStatus(){
		UnlistedSpecialPage::UnlistedSpecialPage("UserStatus");
	}
	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
		
		$messages_show = 25;
		$output = "";
		$user_name = $wgRequest->getVal('user');
		$page =  $wgRequest->getVal('page');
		
		/*/
		/* Redirect Non-logged in users to Login Page
		/* It will automatically return them to their Status page
		/*/
		if($wgUser->getID() == 0 && $user_name==""){
			$wgOut->setPagetitle( wfMsgForContent( 'us_woops' ) );
			$login =  Title::makeTitle( NS_SPECIAL  , "Login"  );
			$wgOut->redirect( $login->getFullURL() . "&returnto=Special:UserStatus" );
			return false;
		}	 
		
		/*/
		/* If no user is set in the URL, we assume its the current user
		/*/	
		
		if(!$user_name)$user_name = $wgUser->getName();
		$user_id = User::idFromName($user_name);
		$user =  Title::makeTitle( NS_USER  , $user_name  );
		$user_safe = str_replace("&","%26",$user_name);		
		
		/*/
		/* Error message for username that does not exist (from URL)
		/*/			
		if($user_id == 0){
			$wgOut->setPagetitle( wfMsgForContent( 'us_woops' ) );
			$wgOut->addHTML( wfMsgForContent( 'us_no_user' ) );
			return false;
		}	
		
		/*/
		/* Config for the page
		/*/			
		$per_page = $messages_show;
		if(!$page || !is_numeric($page) )$page=1;
		
		$stats = new UserStats($user_id, $user_name);
		$stats_data = $stats->getUserStats();
		$total = $stats_data["user_status_count"];
		
		$s = new UserStatus();
		$messages = $s->getStatusMessages($user_id,0,0,$messages_show,$page);
		
		if (!($wgUser->getName() == $user_name)) {
			$wgOut->setPagetitle( wfMsgForContent('us_user_thoughts', "{$user_name}") );
		} else {
			$wgOut->setPagetitle( wfMsgForContent('us_your_thoughts') );
		}
		
		
		$output .= '<div class="gift-links">';
		if (!($wgUser->getName() == $user_name)) {
			$output .= "<a href=\"{$user->getFullURL()}\">" . wfMsgForContent('us_back_your_profile', $user_name) . "</a>";
		} else {
			$output .= "<a href=\"" . $wgUser->getUserPage()->getFullURL() . "\">" . wfMsgForContent('us_back_your_profile') . "</a>";
		}	
		$output .= "</div>";
		
		if($page==1){
			$start = 1;
		}else{
			$start = ($page-1) * $per_page + 1;
		}
		$end = $start + ( count($messages) ) - 1;
		wfDebug("total = {$total}");
		if( $total ){
			$output .= "<div class=\"user-page-message-top\">
				<span class=\"user-page-message-count\" style=\"font-size:11px;color:#666666;\">" . wfMsgExt('us_showing_thoughts', 'parsemag', $start, $end, $total) . ".</span> 
				</div>";
		}
		
		/**/
		/*BUILD NEXT/PREV NAV
		**/

		$numofpages = $total / $per_page; 

		if($numofpages>1){
			$output .= "<div class=\"page-nav\">";
			if($page > 1){ 
				$output .= "<a href=\"" . Title::MakeTitle(NS_SPECIAL, "UserStatus")->getFullUrl("user={$user_safe}&page=" . ($page-1) . "{$qs}") ."\">". wfMsgForContent( 'us_prev' ) ."</a> ";
			}
			
			
			if(($total % $per_page) != 0)$numofpages++;
			if($numofpages >=9 && $page < $total){
				$numofpages=9+$page;
				if($numofpages >= ($total / $per_page) )$numofpages = ($total / $per_page)+1;
			}
			
			for($i = 1; $i <= $numofpages; $i++){
				if($i == $page){
				    $output .=($i." ");
				}else{
					$output .="<a href=\"" . Title::MakeTitle(NS_SPECIAL, "UserStatus")->getFullUrl("user={$user_safe}&page={$i}{$qs}") ."\">$i</a> ";
				}
			}
	
			if(($total - ($per_page * $page)) > 0){
				$output .=" <a href=\"" . Title::MakeTitle(NS_SPECIAL, "UserStatus")->getFullUrl("user={$user_safe}&page=" . ($page+1) . "{$qs}") ."\">". wfMsgForContent( 'us_next' ) ."</a>"; 
			}
			$output .= "</div><p>";
		}
		/**/
		/*BUILD NEXT/PREV NAV
		**/
		
		
		//style
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserStatus/UserStatus.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type='text/javascript' src=\"/extensions/wikia/UserStatus/UserStatus.js?{$wgStyleVersion}\"></script>\n");

		$output .= "<div class=\"user-status-container\">";
		$thought_link =  Title::makeTitle( NS_SPECIAL  , "ViewThought"  );		 
		if($messages){
			foreach ($messages as $message) {
				$user =  Title::makeTitle( NS_USER  , $message["user_name"]  );
				$avatar = new wAvatar($message["user_id"],"m");
				
				$network_link = "<a href=\"" . SportsTeams::getNetworkURL($message["sport_id"],$message["team_id"])."\">" . wfMsgForContent('us_all_team_updates', SportsTeams::getNetworkName($message["sport_id"],$message["team_id"])) . "</a>";
				$delete_link = "";
				if($wgUser->getName()==$message["user_name"]){
					$delete_link = "<span class=\"user-board-red\">
							<a href=\"javascript:void(0);\" onclick=\"javascript:delete_message({$message["id"]})\">" . wfMsgForContent('us_delete_thought_text') ."</a>
						</span>";
				}
				
				$max_link_text_length = 50;
				$message_text = preg_replace_callback( "/(<a[^>]*>)(.*?)(<\/a>)/i",'cut_link_text',$message["text"]);
				//$vote_count = $message["plus_count"] . " ". (($message["plus_count"]==1)?"person":"people") . " agree" . (($message["plus_count"]==1)?"s":"");
				$vote_count = wfMsgExt('us_num_agree', 'parsemag', $message["plus_count"]);
		
				if( $wgUser->isLoggedIn() && $wgUser->getName()!=$message["user_name"]){
					if( !$message["voted"] ){
						$vote_link = "<a href=\"javascript:void(0);\" onclick=\"vote_status({$message["id"]},1)\">[" . wfMsgForContent( 'us_agree' ) . "]</a>";
					} else {
						$vote_link = "{$vote_count}";
					}
				}
				
				$view_thought_link = "<a href=\"" . $thought_link->getFullURL() . "&id={$message["id"]}\" >[" . wfMsgForContent( 'us_see_who_agrees' ) . "]</a>";
				
				$output .= "<div class=\"user-status-row\">
					
					<div class=\"user-status-logo\">
					
						<a href=\"" .SportsTeams::getNetworkURL($message["sport_id"],$message["team_id"])  . "\">" .SportsTeams::getLogo($message["sport_id"],$message["team_id"],"m") . "</a> 
					
					</div>
					
					<div class=\"user-status-message\">
					
						{$message_text}
					
						<div class=\"user-status-date\">
							".get_time_ago($message["timestamp"])." " . wfMsgForContent('us_ago') . "
							<span class=\"user-status-vote\" id=\"user-status-vote-{$message["id"]}\">
								{$vote_link}
							</span>
							{$view_thought_link}
							<span class=\"user-status-links\"> 
									{$delete_link}
							</span>	
						</div>
						
					</div>
					
					<div class=\"cleared\"></div>
					
				</div>";
			}
		}else{
			$output .= "<p>" . wfMsgForContent( 'us_no_updates' ) . "</p>";
		
		}
		$output .= "</div>";
			
	 
		
		$wgOut->addHTML($output);
	
	}
  
}

SpecialPage::addPage( new ViewUserStatus );

	//read in localisation messages
	function wfUserStatusReadLang(){
		//global $wgMessageCache, $IP, $wgPickGameDirectory;
		global $wgMessageCache, $IP;
		$wgUserStatusDirectory = "{$IP}/extensions/wikia/UserStatus";
		require_once ( "$wgUserStatusDirectory/UserStatus.i18n.php" );
		foreach( efWikiaUserStatus() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
	}

}

?>