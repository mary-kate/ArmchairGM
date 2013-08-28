<?php

$wgExtensionFunctions[] = 'wfSpecialSystemGiftManager';


function wfSpecialSystemGiftManager(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");

	class SystemGiftManager extends SpecialPage {
	
		function SystemGiftManager(){
			UnlistedSpecialPage::UnlistedSpecialPage("SystemGiftManager");
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			
			$wgOut->setPagetitle( "System Gifts Manager" );
			if(! in_array('staff',($wgUser->getGroups())) ){
				//$wgOut->addHTML("Invalid Page");
				//return;
			}
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/ListRatings/ListRatings.js\"></script>\n");
		
			//$g = new SystemGifts();
			//$g ->update_system_gifts();
			//exit();
			
			/*
			require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
			///$l = new ListRatings("Pean");
			//echo $l->displayList("MLB Players",10,0,"vote_avg","desc",false);
			
			$l = UserStats::getFriendsRelativeToPoints( $wgUser->getID(), 436700 , $limit=3, $condition=-1 ) ;
			print_r($l);
			*/
			//check if there is an associated System Gift for this category/threshold
			require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");

			$css = "<style>
			.view-form {font-weight:800;font-size:12px;font-color:#666666}
			.view-status {font-weight:800;font-size:12px;background-color:#FFFB9B;color:#666666;padding:5px;margin-bottom:5px;}
			</style>";
			$wgOut->addHTML($css);
			
	 		if($wgRequest->wasPosted()){
				$g = new SystemGifts();
	
				if(!($_POST["id"])){
					$gift_id = $g->addGift(
							$wgRequest->getVal("gift_name"),$wgRequest->getVal("gift_description"),
							$wgRequest->getVal("gift_category"),$wgRequest->getVal("gift_threshold")
							);
					$wgOut->addHTML("<span class='view-status'>The gift has been created</span><br><br>");
				}else{
					$gift_id = $wgRequest->getVal("id");
					$g->updateGift($gift_id,
							$wgRequest->getVal("gift_name"),$wgRequest->getVal("gift_description"),
							$wgRequest->getVal("gift_category"),$wgRequest->getVal("gift_threshold")
							);
					$wgOut->addHTML("<span class='view-status'>The gift has been saved</span><br><br>");
				}
				
				$wgOut->addHTML($this->displayForm($gift_id));
			}else{
				$gift_id = $wgRequest->getVal( 'id' );
				if($gift_id || $wgRequest->getVal( 'method' )=="edit"){
					$wgOut->addHTML($this->displayForm($gift_id));
				}else{
					$wgOut->addHTML("<div><b><a href=\"index.php?title=Special:SystemGiftManager&method=edit\">+ Add New Gift</a></b></div><p>");
					$wgOut->addHTML($this->displayGiftList());
				}
			}
		}
		
		function displayGiftList(){
			$gifts = SystemGifts::getGiftList($per_page,$page);
			if($gifts){
				foreach ($gifts as $gift) {
					$output .= "<div class=\"Item\" >
							<a href=\"index.php?title=Special:SystemGiftManager&amp;id={$gift["id"]}\">{$gift["gift_name"]}</a>
						</div>\n";
				}
			}
			$output .= "</div>";
			return "<div id=\"views\">" . $output . "</div>";
		}
	
		
		function displayForm($gift_id){
			$form .= "<div><b><a href=\"index.php?title=Special:SystemGiftManager\">View Gift List</a></b></div><p>";
			
			if($gift_id)$gift = SystemGifts::getGift($gift_id);

			
			$form .=  '<form action="" method="POST" enctype="multipart/form-data" name="gift">';
			
			$form .= '<table border="0" cellpadding="5" cellspacing="0" width="500">';
			
			$form .=  '<tr>
			<td width="200" class="view-form">gift name</td>
			<td width="695"><input type="text" size="45" class="createbox" name="gift_name" value="'. $gift["gift_name"] . '"/></td>
			</tr>
			<tr>
			<td width="200" class="view-form" valign="top">gift description</td>
			<td width="695"><textarea class="createbox" name="gift_description" rows="2" cols="30">'. $gift["gift_description"] . '</textarea></td>
			</tr>
			<tr>
			<td width="200" class="view-form">gift type</td>
			<td width="695">
				<select name="gift_category">';
				$g = new SystemGifts();
				foreach($g->categories as $category => $id){
					$form .= '<option ' . (($gift["gift_category"]==$id)?"selected":"") . " value=\"{$id}\">{$category}</option>";
				}
				$form .= '</select>
			<tr>
			<td width="200" class="view-form">threshold</td>
			<td width="695"><input type="text" size="25" class="createbox" name="gift_threshold" value="'. $gift["gift_threshold"] . '"/></td>
			</tr>
			<tr>';
		
			if($gift_id){
				$gift_image = "<img src=\"/images/awards/" . SystemGifts::getGiftImage($gift_id,"l") . "\" border=\"0\" alt=\"gift\" />";
				$form .=  '<tr>
				<td width="200" class="view-form" valign="top">gift image</td>
				<td width="695">' . $gift_image . '
				<p>
				<a href="index.php?title=Special:SystemGiftManagerLogo&gift_id=' . $gift_id . '">add/replace image</a>
				</td>
				</tr>';
			}
			
			$form .=  '<tr>
			<td colspan="2">
			<input type=hidden name="id" value="' . $gift["gift_id"] . '">
			<input type="button" class="createbox" value="' . (($gift["gift_id"])?"edit":"create gift") . '" size="20" onclick="document.gift.submit()" />
			<input type="button" class="createbox" value="cancel" size="20" onclick="history.go(-1)" />
			</td>
			</tr>
			</table>
			
			</form>';
			return $form;
		}
	}

	SpecialPage::addPage( new SystemGiftManager );
}

?>