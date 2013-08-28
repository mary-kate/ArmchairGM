<?php

require_once( "UserBoard.i18n.php" );

$wgExtensionFunctions[] = 'wfSpecialSendBulletin';

function wfSpecialSendBulletin(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class BoardBlast extends SpecialPage {

	
	function BoardBlast(){
		UnlistedSpecialPage::UnlistedSpecialPage("SendBoardBlast");
	}
	
	function execute( ){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $IP;

		# Add messages
		global $wgMessageCache, $wgUserBoardMessages;
		foreach( $wgUserBoardMessages as $key => $value ) {
			$wgMessageCache->addMessages( $wgUserBoardMessages[$key], $key );
		}
		
		require_once("$IP/extensions/wikia/UserBoard/UserBoardClass.php");
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/UserBoard/BoardBlast.css?{$wgStyleVersion}\"/>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/UserBoard/BoardBlast.js?{$wgStyleVersion}\"></script>\n");
		
		$output = "";
	
		if( $wgUser->isBlocked() ){
			$wgOut->blockedPage( false );
			return false;
		}
	
		if(!$wgUser->isLoggedIn()){
			$wgOut->setPageTitle( wfMsgForContent( 'boardblastlogintitle' ) );
			$output = wfMsgForContent( 'boardblastlogintext' );
			$wgOut->addHTML($output);
			return "";
		}
			
		
		if( $wgRequest->wasPosted() ){
			$wgOut->setPagetitle( wfMsgForContent( 'messagesenttitle' ) );
			$b = new UserBoard();
			
			$count = 0;
			$user_ids_to = explode(",",$wgRequest->getVal("ids"));
			foreach($user_ids_to as $user_id){
				$user = User::newFromId($user_id );
				$user->loadFromId();
				$user_name = $user->getName();
				//echo $user_id . "-" . $user_name . "<BR>";
				$b->sendBoardMessage($wgUser->getID(),$wgUser->getName(),$user_id,$user_name, $wgRequest->getVal("message"), 1);
				$count++;
			}
			$output .= wfMsgForContent( 'messagesentsuccess' );
		}else{
			$wgOut->setPagetitle( wfMsgForContent( 'boardblasttitle' ) );
			$output .= $this->displayForm();	
		}

		$wgOut->addHTML($output);

		
	}
	
	function displayForm(){
		global $wgUser;
		
		$stats = new UserStats($wgUser->getID(), $wgUser->getName() );
		$stats_data = $stats->getUserStats();
				
		$output .= "<div class=\"board-blast-message-form\">

				<h2>" . wfMsgForContent( 'boardblaststep1' ) . "</h2>
			
				<form method=\"post\" name=\"blast\" action=\"\">
					
					<input type=\"hidden\" name=\"ids\" id=\"ids\">
					
					<div class=\"blast-message-text\">
						" . wfMsgForContent( 'boardblastprivatenote' ) . "
					</div>
					
					<textarea name=\"message\" id=\"message\" cols=\"63\" rows=\"4\"/></textarea>
				
				</form>
			
		</div>
		
		
		<div class=\"blast-nav\">
		
				<h2>" . wfMsgForContent( 'boardblaststep2' ) . "</h2>
				
				<div class=\"blast-nav-links\">
					<a href=\"javascript:void(0);\" onclick=\"javascript:select_all()\">" . wfMsgForContent( 'boardlinkselectall' ) . "</a> -
					<a href=\"javascript:void(0);\" onclick=\"javascript:unselect_all()\">" . wfMsgForContent( 'boardlinkunselectall' ) . "</a> ";
					
					if( $stats_data["friend_count"] > 0 && $stats_data["foe_count"] > 0 ){
						$output .= "- <a href=\"javascript:void(0);\" onclick=\"javascript:toggle_friends(1)\">" . wfMsgForContent( 'boardlinkselectfriends' ) . "</a> -";
						$output .= "<a href=\"javascript:void(0);\" onclick=\"javascript:toggle_friends(0)\">" . wfMsgForContent( 'boardlinkunselectfriends' ) . "</a>";
					}
					
					if( $stats_data["foe_count"] > 0 && $stats_data["friend_count"] > 0){
						$output .= "- <a href=\"javascript:void(0);\" onclick=\"javascript:toggle_foes(1)\">" . wfMsgForContent( 'boardlinkselectfoes' ) . "</a> -";
						$output .= "<a href=\"javascript:void(0);\" onclick=\"javascript:toggle_foes(0)\">" . wfMsgForContent( 'boardlinkunselectfoes' ) . "</a>";
					}
				$output .= "</div>
		
		</div>";
				
		$rel = new UserRelationship( $wgUser->getName() );
		$relationships = $rel->getRelationshipList( );	

		$output .= "<div id=\"blast-friends-list\" class=\"blast-friends-list\">";
		
		$x = 1;
		$per_row = 3;
		if( count($relationships) > 0 ){
			foreach($relationships as $relationship){
				$output .= "<div class=\"blast-" . (($relationship["type"]==1)?"friend":"foe") . "-unselected\" id=\"user-{$relationship["user_id"]}\" onclick=\"javascript:toggle_user({$relationship["user_id"]})\">
						{$relationship["user_name"]}
					</div>";
					if($x==count($relationships) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
				 $x++;
			}
			 
			$output .= "</div>
			
			<div class=\"cleared\"></div>";
		}else{
			$output .= "<div>" . wfMsgForContent( 'boardnofriends' ) . "</div>";
		}
	 
		$output .= "<div class=\"blast-message-box-button\">
			<input type=\"button\" value=\"" . wfMsgForContent( 'boardsendbutton' ) . " \" class=\"site-button\" onclick=\"javascript:send_messages();\">
		</div>";
	
		return $output;
	
	}
	

}


SpecialPage::addPage( new BoardBlast );

}

?>
