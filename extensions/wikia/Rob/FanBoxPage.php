<?php

class FanBoxPage extends Article{


	var $title = null;
	var $authors = array();
	
	function __construct (&$title){
		parent::__construct(&$title);
	}

	
	function view(){
		global $wgOut, $wgUser, $wgRequest, $wgTitle, $wgContLang, $wgFanBoxPageDisplay;
		
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Rob/FanBoxes.js\"></script>\n");

		
		$output= $wgOut->setHTMLTitle( $wgTitle->getText() );
		$output.= $wgOut->setPageTitle($wgTitle->getText());


		$this->fan = new FanBox( $this->getTitle() );		
		$output .= "<h1>". wfMsgForContent( 'fanbox_namespace' ).$wgTitle->getText()."</h1>";
				
		$output .= $this->fan->outputFanBox();
		$output .= "<div id=\"show-message-container\">";
		
		if($wgUser->isLoggedIn()){
			$check = $this->fan->checkIfUserHasFanBox();
			if ($check == 0){
				$output .= $this->fan->outputIfUserDoesntHaveFanBox();

			}
			else $output .= $this->fan->outputIfUserHasFanBox();
		}
		else {
			$output .= $this->fan->outputIfUserNotLoggedIn();
		}
			
		$output .= "</div>";
				
		$output .= $this->getEmbedThisTag();

				
		$output.= "<h2>". wfMsgForContent( 'fanbox_users_with_fanbox' )."</h2>".$this->fanBoxHolders();
		
		$wgOut->addHTML($output);
		if( $wgFanBoxPageDisplay['comments'] == true ){
			$wgOut->addWikiText("<comments></comments>");
		}
		parent::view();	
	
	}
	
		
	
	//two functions get users who have the fanbox and display their avatars
	function getFanBoxHolders(){
		global $wgOut, $wgUser, $wgRequest, $wgTitle;
		
		$page_title_id = $wgTitle->getArticleID();
		
		$fanboxholders = array();
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT DISTINCT userft_user_name, userft_user_id FROM user_fantag INNER JOIN fantag 
		WHERE user_fantag.userft_fantag_id = fantag.fantag_id and fantag.fantag_pg_id = {$page_title_id}";
		$res = $dbr->query($sql);
		
		while ($row = $dbr->fetchObject( $res ) ) {
			$fanboxholders[] = array( "userft_user_name" => $row->userft_user_name, "userft_user_id" => $row->userft_user_id);
		};
		
		return $fanboxholders;
	
	}
	
	function fanBoxHolders (){
		global $IP, $wgUser, $wgTitle, $wgOut,$wgUploadPath, $wgMemc;
		
		$fanboxholders = $this->getFanBoxHolders();

		foreach($fanboxholders as $fanboxholder){
				$userftusername = $fanboxholder["userft_user_name"];
				$userftuserid = $fanboxholder["userft_user_id"];				
				$user_title = Title::makeTitle( NS_USER, $fanboxholder["userft_user_name"] );
				$avatar = new wAvatar($fanboxholder["userft_user_id"],"m");

				$output .= "<a href=\"".$user_title->escapeFullURL()."\"><img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\" /></a>";

			};
		
		return $output;		
	}
	
	public function getEmbedThisTag(){
		$code = $this->fan->getEmbedThisCode();
		$code = preg_replace('/[\n\r\t]/','',$code); // replace any non-space whitespace with a space
		//$code = addslashes($code)
		;
		return "<br/><br/><table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"><tr><td><b>" . wfMsgForContent( 'fan_embed') . "</b> </td><td><form name=\"embed_fan\"><input name='embed_code' style='width:300px;font-size:10px;' type='text' value='{$code}'  onClick='javascript:document.embed_fan.embed_code.focus();document.embed_fan.embed_code.select();' readonly='true'></form></td></tr></table>";
	}


	
	
}


?>
