<?php

$wgExtensionFunctions[] = 'wfSpecialPlayerStatsBreaker';

function wfSpecialPlayerStatsBreaker(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PlayerStatsBreaker extends SpecialPage {

	
	function PlayerStatsBreaker(){
		UnlistedSpecialPage::UnlistedSpecialPage("PlayerStatsBreaker");
	}
	
	function execute($value){
		//global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		global $wgOut, $IP;
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/CustomMainPage/MainPage.css?{$wgStyleVersion}\"/>\n");
		include_once($IP . "/extensions/wikia/FeaturedEditors/FeaturedEditorsClass.php");
		
		$authors = new FeaturedEditors(365);
		
		$output = $authors->displayFeaturedEditors();
		
		$wgOut->addHTML($output);
		
		
	}
	
}


SpecialPage::addPage( new PlayerStatsBreaker );



}

?>
