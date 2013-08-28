<?php

$wgExtensionFunctions[] = 'wfSpecialViewFanBoxes';


function wfSpecialViewFanBoxes(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ViewFanBoxes extends SpecialPage {
		
		function ViewFanBoxes(){
			SpecialPage::SpecialPage("ViewFanBoxes");
		}
		
		function execute(){
			global $IP, $wgOut, $wgUser, $wgTitle, $wgRequest, $wgContLang, $wgMessageCache, $wgStyleVersion;

			require_once("$IP/extensions/wikia/Rob/FanBoxesClass.php");

			require_once ( "$IP/extensions/wikia/Rob/FanBox.i18n.php" );
			foreach( efWikiaFantag() as $lang => $messages ){
				$wgMessageCache->addMessages( $messages, $lang );
			}

		
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Rob/FanBoxes.js\"></script>\n");
			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Rob/FanBoxes.css\"/>\n");

	
			//code for viewing fanboxes for each user			
			$output = "";
			$user_name = $wgRequest->getVal('user');
			$page =  $wgRequest->getVal('page');
		
			 
			// Redirect Non-logged in users to Login Page
		
			
			// If no user is set in the URL, we assume its the current user
			
			if(!$user_name)$user_name = $wgUser->getName();
			$user_id = User::idFromName($user_name);
			$user =  Title::makeTitle( NS_USER  , $user_name  );
			$user_safe = urlencode($user_name);
		
	  
			// Config for the page
		
			$per_page = 10;
			if(!$page||!is_numeric($page) )$page=1;
			$per_row = 2;
						
			//Get all FanBoxes for this user into the array 
			//calls the FanBoxesClass file
			$userfan = new UserFanBoxes($user_name);
			$userfanboxes = $userfan->getUserFanboxes(0,$per_page,$page);
			$total = $userfan->getFanBoxCountByUsername($user_name);

			//page title and top part
			
					
			if ($userfanboxes) {
		
				$x = 1;

				foreach($userfanboxes as $userfanbox){
										
					$output .= "<div class=\"fa-item\">
						Hi";
					$output .= "</div>";
					if($x==count($userfanboxes) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
					
					$x++;	

				};
			
			}
			


			
			$wgOut->addHTML($output);


		}

	
	
	
		
}

	SpecialPage::addPage( new ViewFanBoxes );
}

?>
