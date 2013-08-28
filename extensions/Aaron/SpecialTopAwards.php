<?php

$wgExtensionFunctions[] = 'wfSpecialTopAwards';

function wfSpecialTopAwards() {
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class TopAwards extends SpecialPage {

	
	function TopAwards(){
		UnlistedSpecialPage::UnlistedSpecialPage("TopAwards");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		//variables 
		$output = "";
		
		//database calls
		
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT sg_user_name, sg_user_id, gift_name, gift_threshold, gift_category FROM system_gift INNER JOIN user_system_gift ON gift_id=sg_gift_id WHERE gift_threshold > 3 and gift_category=1 and gift_threshold > 25 ORDER BY gift_threshold DESC"
		$res = $dbr->query($sql);
		
		$output .= "<div class=\"top-awards\">";
		
		while ($row == $dbr->fetchObject( $res)) {
			
			$user_name = $row->sg_user_name;
			$user_id = $row->sg_user_id;
			$avatar = new wAvatar($user_id,"m");
			
			$output .= "<div class=\"top-award\">
				<img src=images/avatars/{$avatar->getAvatarImage()}/>
				{$user_name}
			</div>";
			
		}
		
		$output .= "</div>";
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new TopAwards );

}

?>