<?php

$wgExtensionFunctions[] = 'wfSpecialTopFanBoxes';


function wfSpecialTopFanBoxes(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class TopFanBoxes extends SpecialPage {
		
		function TopFanBoxes(){
			SpecialPage::SpecialPage("TopFanBoxes");
		}
		
		function execute(){
			global $IP, $wgOut, $wgUser, $wgTitle, $wgRequest, $wgContLang, $wgMessageCache, $wgStyleVersion, $wgUploadPath;


			require_once ( "$IP/extensions/wikia/Rob/FanBox.i18n.php" );
			foreach( efWikiaFantag() as $lang => $messages ){
				$wgMessageCache->addMessages( $messages, $lang );
			}

		
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Rob/FanBoxes.js\"></script>\n");
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Rob/FanBoxes.css\"/>\n");

			$topfanboxid = $wgRequest->getVal("id");
			
			if($topfanboxid == fantag_date){
				$wgOut->setPageTitle("Most Recent Fanboxes");
				$topfanboxes = $this->getTopFanboxes('fantag_date');

			}
			else {
				$wgOut->setPageTitle("Top Fanboxes");
				$topfanboxes = $this->getTopFanboxes('fantag_count');
			}

			
			

			
			$output = "";
			
			//make top right nav bar
			$top_title = Title::makeTitle( NS_SPECIAL  , "TopFanBoxes"  );
			
			$output .= "<div class=\"fanbox-nav\">
				<h1>" . wfMsgForContent("fanbox_nav_header") . "</h1>
				<p><a href=\"{$top_title->escapeFullURL()}\">" . wfMsgForContent("top_fanboxes_link") . "</a></p>
				<p><a href=\"" . $top_title->escapeFullURL("id=fantag_date") . "\">" . wfMsgForContent("most_recent_fanboxes_link") . "</a><p></div>";

			
			$x = 1;

				$output .= "<div class=\"top-fanboxes\">";

				
				foreach($topfanboxes as $topfanbox){
					
					$check_user_fanbox = $this->checkIfUserHasFanbox($topfanbox["fantag_id"]);
					
					if( $topfanbox["fantag_image_name"]){
						$fantag_image_width = 45;
						$fantag_image_height = 53;
						$fantag_image = Image::newFromName( $topfanbox["fantag_image_name"] );
						$fantag_image_url = $fantag_image->createThumb($fantag_image_width, $fantag_image_height);
						$fantag_image_tag = '<img alt="" src="' . $fantag_image_url . '"/>';
					};
					
					if ($topfanbox["fantag_left_text"] == ""){
						$fantag_leftside = $fantag_image_tag;
					}
					else {
						$fantag_leftside = $topfanbox["fantag_left_text"];
					}
					
					if ($topfanbox["fantag_left_textsize"] == "mediumfont"){
						$leftfontsize= "14px";
					}
					if ($topfanbox["fantag_left_textsize"] == "bigfont"){
						$leftfontsize= "20px";
					}
					
					if ($topfanbox["fantag_right_textsize"] == "smallfont"){
						$rightfontsize= "12px";
					}
					if ($topfanbox["fantag_right_textsize"] == "mediumfont"){
						$rightfontsize= "14px";
					}

					
					//get permalink
					$fantag_title =  Title::makeTitle( NS_FANTAG  , $topfanbox["fantag_title"]  );
					
					//get creator
					$userftusername = $topfanbox["fantag_user_name"];
					$userftuserid = $topfanbox["fantag_user_id"];				
					$user_title = Title::makeTitle( NS_USER, $topfanbox["fantag_user_name"] );
					$avatar = new wAvatar($topfanbox["fantag_user_id"],"m");
									
					//output fanboxes
					
					$output .= "
					<div class=\"top-fanbox-row\">
					<span class=\"top-fanbox-num\">{$x}.</span><span class=\"top-fanbox\">
					
					<div class=\"fanbox-item\">
				
					<form action=\"\" method=\"post\" name=\"form2\">
					<input type=\"hidden\" name=\"individualFantagId\" value=\"{$topfanbox["fantag_id"]}\">
				
					<div class=\"individual-fanbox\" id=\"individualFanbox".$topfanbox["fantag_id"]."\">
					<div class=\"show-message-container\" id=\"show-message-container".$topfanbox["fantag_id"]."\">
						<div class=\"permalink-container\">
						<a class=\"perma\" style=\"font-size:8px; color:".$topfanbox["fantag_right_textcolor"]."\" href=\"".$fantag_title->escapeFullURL()."\" title=\"{$topfanbox["fantag_title"]}\">perma</a>
						<table  class=\"fanBoxTable\" onclick=\"javascript:openFanBoxPopup('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}')\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" >
						<tr><td id=\"fanBoxLeftSideOutput\" style=\"color:".$topfanbox["fantag_left_textcolor"]."; font-size:$leftfontsize\" bgcolor=\"".$topfanbox["fantag_left_bgcolor"]."\">".$fantag_leftside."</td> 
						<td id=\"fanBoxRightSideOutput\" style=\"color:".$topfanbox["fantag_right_textcolor"]."; font-size:$rightfontsize\" bgcolor=\"".$topfanbox["fantag_right_bgcolor"]."\">".$topfanbox["fantag_right_text"]."</td>
						</table>
						</div>
					</div>
					</div>";
					
					if($wgUser->isLoggedIn()){
						if ($check_user_fanbox == 0){
							$output .= "
						<div class=\"fanbox-pop-up-box\" id=\"fanboxPopUpBox".$topfanbox["fantag_id"]."\">
						<table cellpadding=\"0\" cellspacing=\"0\" width=\"258px\"><tr><td align=\"center\">". wfMsgForContent( 'fanbox_add_fanbox' ) ."<tr><td align=\"center\">
						<input type=\"button\" value=\"Add\" size=\"20\" onclick=\"closeFanboxAdd('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}'); showAddRemoveMessageUserPage(1, {$topfanbox["fantag_id"]})\" />
						<input type=\"button\" value=\"Cancel\" size=\"20\" onclick=\"closeFanboxAdd('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}')\" />
						</td></table>
						</div>";
						}
						else{
							$output .= "
						<div class=\"fanbox-pop-up-box\" id=\"fanboxPopUpBox".$topfanbox["fantag_id"]."\">
						<table cellpadding=\"0\" cellspacing=\"0\" width=\"258px\"><tr><td align=\"center\">". wfMsgForContent( 'fanbox_remove_fanbox' ) ."<tr><td align=\"center\">
						<input type=\"button\" value=\"Remove\" size=\"20\" onclick=\"closeFanboxAdd('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}'); showAddRemoveMessageUserPage(2, {$topfanbox["fantag_id"]})\" />
						<input type=\"button\" value=\"Cancel\" size=\"20\" onclick=\"closeFanboxAdd('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}')\" />
						</td></table>
						</div>";
						}
					};
				
					if($wgUser->getID() == 0 ){
						$login =  Title::makeTitle( NS_SPECIAL  , "UserLogin"  );
						$output .= "<div class=\"fanbox-pop-up-box\" id=\"fanboxPopUpBox".$topfanbox["fantag_id"]."\">
						<table cellpadding=\"0\" cellspacing=\"0\" width=\"258px\"><tr><td align=\"center\">". wfMsgForContent( 'fanbox_add_fanbox_login' ) ."<a href=\"{$login->getFullURL()}\">". wfMsgForContent( 'fanbox_login' ) ."</a><tr><td align=\"center\">
						<input type=\"button\" value=\"Cancel\" size=\"20\" onclick=\"closeFanboxAdd('fanboxPopUpBox{$topfanbox["fantag_id"]}', 'individualFanbox{$topfanbox["fantag_id"]}')\" />
						</td></table>
						</div>";
					};
					
	
					
					$output .= "</form></div></span>";
					$output .= "<span class=\"top-fanbox-creator\"><table><td class=\"centerheight\"> <b> created by: <b> <td class=\"centerheight\"> <b> <a href=\"".$user_title->escapeFullURL()."\"><img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\" /></a></b> </table></span>";			
					$output .= "<span class=\"top-fanbox-users\"><table><td class=\"centerheight\"><b><a href=\"".$fantag_title->escapeFullURL()."\">" .$topfanbox["fantag_count"]. " members. </a></b></table></span>";
					$output .= "<div class=\"cleared\"></div>";
					$output .= "</div>";

					$x++;

				
				};
					$output .= "</div><div class=\"cleared\"></div>";

			

			
		
			$wgOut->addHTML($output);


		}

	
	function getTopFanboxes($orderby){
		$dbr =& wfGetDB( DB_MASTER );
				
		$sql = "SELECT fantag_id, fantag_title, fantag_left_text, fantag_left_textcolor, fantag_left_bgcolor, fantag_right_text, fantag_right_textcolor, fantag_right_bgcolor, fantag_image_name, fantag_left_textsize, fantag_right_textsize, fantag_count, fantag_user_id, fantag_user_name FROM fantag ORDER BY {$orderby} DESC LIMIT 0,50;";
		
		$res = $dbr->query($sql);
		$topfanboxes = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			 $topfanboxes[] = array(
				 "fantag_id" => $row->fantag_id, 
				 "fantag_title" => $row->fantag_title, 
				 "fantag_left_text" => $row->fantag_left_text, 
				 "fantag_left_textcolor" => $row->fantag_left_textcolor, 
				 "fantag_left_bgcolor" => $row->fantag_left_bgcolor, 
				 "fantag_right_text" => $row->fantag_right_text, 
				 "fantag_right_textcolor" => $row->fantag_right_textcolor, 
				 "fantag_right_bgcolor" => $row->fantag_right_bgcolor,
				 "fantag_image_name" => $row->fantag_image_name,
				 "fantag_left_textsize" => $row->fantag_left_textsize,
				 "fantag_right_textsize" => $row->fantag_right_textsize,
				 "fantag_count" => $row->fantag_count,
				 "fantag_user_id" => $row->fantag_user_id,
				 "fantag_user_name" => $row->fantag_user_name,

				 
				 );
		}
		
		return $topfanboxes;
	}

	
	function checkIfUserHasFanbox($userft_fantag_id){
		global $wgUser;
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as count
			FROM user_fantag
			WHERE userft_user_name = '{$wgUser->getName()}' && userft_fantag_id = {$userft_fantag_id}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		if($row){
			$check_fanbox_count=$row->count;
		}
		return $check_fanbox_count;		
	}
	
	

	
	
		
}

	SpecialPage::addPage( new TopFanBoxes );
}

?>
