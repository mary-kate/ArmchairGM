<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadness';
$wgExtensionFunctions[] = 'wfPickGameReadLang';

function wfSpecialMarchMadness(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadness extends SpecialPage {

	
	function MarchMadness(){
		UnlistedSpecialPage::UnlistedSpecialPage("MarchMadness");

	}
	
	
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		$output = "";
		$title = "Title";
		
		
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);
	
	    
	}
	
	
	
  
}

SpecialPage::addPage( new MarchMadness );




}

?>