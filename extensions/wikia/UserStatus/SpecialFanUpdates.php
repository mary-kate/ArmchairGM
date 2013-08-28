<?php

$wgExtensionFunctions[] = 'wfViewFanUpdates';

function wfViewFanUpdates(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class ViewFanUpdates extends SpecialPage {

	
	function ViewFanUpdates(){
		UnlistedSpecialPage::UnlistedSpecialPage("FanUpdates");
	}
	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
		
		$messages_show = 25;
		$output = "";
		$sport_id = $wgRequest->getVal("sport_id");
		$team_id = $wgRequest->getVal("team_id");
		$page =  $wgRequest->getVal('page');
	
		if($team_id){
			$team = SportsTeams::getTeam($team_id);
			$network_name = $team["name"];
		}else{
			$sport = SportsTeams::getSport($sport_id);
			$network_name = $sport["name"];
		}
		/*/
		/* Config for the page
		/*/
		$wgOut->setPagetitle( wfMsgForContent( 'us_network_thoughts', $network_name) );
		
		$per_page = $messages_show;
		if(!$page)$page=1;
		
 
		
		
		$s = new UserStatus();
		$total = $s->getNetworkUpdatesCount($sport_id,$team_id);
		$messages = $s->getStatusMessages(0,$sport_id,$team_id,$messages_show,$page);
		

		$output .= '<div class="gift-links">';
		$output .= "<a href=\"" . SportsTeams::getNetworkURL($sport_id,$team_id) . "\">" . wfMsgForContent( 'us_back_to_network') . "</a>";
		$output .= '</div>';
		
		if($page==1){
			$start = 1;
		}else{
			$start = ($page-1) * $per_page + 1;
		}
		$end = $start + ( count($messages) ) - 1;
		
		if( $total ){
			$output .= "<div class=\"user-page-message-top\">
			<span class=\"user-page-message-count\" style=\"font-size:11px;color:#666666;\">" . wfMsgExt('us_showing_thoughts', 'parsemag', $start, $end, $total) . ".</span> </span> 
				</div>";
		}
		
		/**/
		/*BUILD NEXT/PREV NAV
		**/

		$numofpages = $total / $per_page; 

		if($numofpages>1){
			$output .= "<div class=\"page-nav\">";
			if($page > 1){ 
				$output .= "<a href=\"" . SportsTeams::getFanUpdatesURL($sport_id,$team_id) . "&page=" . ($page-1) . "{$qs}\">". wfMsgForContent( 'us_prev' ) ."</a> ";
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
				    $output .="<a href=\"" . SportsTeams::getFanUpdatesURL($sport_id,$team_id) . "&page=$i{$qs}\">$i</a> ";
				}
			}
	
			if(($total - ($per_page * $page)) > 0){
				$output .=" <a href=\"" . SportsTeams::getFanUpdatesURL($sport_id,$team_id) . "&page=" . ($page+1) . "{$qs}\">". wfMsgForContent( 'us_next' ) ."</a>"; 
			}
			$output .= "</div><p>";
		}
		/**/
		/*BUILD NEXT/PREV NAV
		**/
		
		
		//style
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserStatus/UserStatus.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type='text/javascript' src=\"/extensions/wikia/UserStatus/UserStatus.js?{$wgStyleVersion}\"></script>\n");

	
		if( $wgUser->isLoggedIn() ){
			$output .= "<script>
			var __sport_id__ = {$sport_id};
			var __team_id__ = {$team_id};
			var __updates_show__ = \"{$updates_show}\";
			var __redirect_url__ = \"" . str_replace("&amp;","&",SportsTeams::getFanUpdatesURL($sport_id,$team_id))  . "\";
			
			</script>";
			
			$output .= "<div class=\"user-status-form\">
			<span class=\"user-name-top\">{$wgUser->getName()}</span> <input type=\"text\" name=\"user_status_text\" id=\"user_status_text\" size=\"40\"/>
			<input type=\"button\" value=\"" . wfMsgForContent( 'us_btn_add' ) ."\" class=\"site-button\" onclick=\"add_status()\" />
			</div>";
		}
				
		$output .= "<div class=\"user-status-container\">";
		if($messages){
			foreach ($messages as $message) {
				$user =  Title::makeTitle( NS_USER  , $message["user_name"]  );
				$avatar = new wAvatar($message["user_id"],"m");
				
				$messages_link = "<a href=\"" . UserStatus::getUserUpdatesURL($message["user_name"])."\">" . wfMsgForContent( 'us_view_all_updates', $message["user_name"] ) ."</a>";
				$delete_link = "";
				if($wgUser->getName()==$message["user_name"]){
					$delete_link = "<span class=\"user-board-red\">
							<a href=\"javascript:void(0);\" onclick=\"javascript:delete_message({$message["id"]})\">" . wfMsgForContent( 'us_delete') ."</a>
						</span>";
				}
				
				$max_link_text_length = 50;
				$message_text = preg_replace_callback( "/(<a[^>]*>)(.*?)(<\/a>)/i",'cut_link_text',$message["text"]);
		
				$output .= "<div class=\"user-status-row\">
					<a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a>
					<a href=\"{$user->getFullURL()}\"><b>{$message["user_name"]}</b></a> {$message_text}
					<span class=\"user-status-date\">
						".get_time_ago($message["timestamp"])." " . wfMsgForContent( 'us_ago' ) . "
					</span>
				</div>";
			}
		}else{
			$output .= "<p>" . wfMsgForContent( 'us_no_updates' ) . "</p>";
		
		}	 
		$output .= "</div>";
			
	 
		
		$wgOut->addHTML($output);
	
	}
  
}

SpecialPage::addPage( new ViewFanUpdates );

}

?>