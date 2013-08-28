<?php


$wgExtensionFunctions[] = "wfPrototype";


function wfPrototype() {
        global $wgOut;
	//$wgOut->addScript("<script type=\"text/javascript\" src=\"extensions/Prototype/prototype.js\"></script>\n");
	
	
	$wgOut->addScript("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.3.1/build/utilities/utilities.js\"></script> \n");
	//$wgOut->addScript("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.3.1/build/animation/animation-min.js\"></script> \n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"extensions/Prototype/Prototype_helper.js\"></script>\n");
	
	
	//$wgOut->addScript("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.3.1/build/event/event-min.js\"></script>\n");
	//$wgOut->addScript("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.3.1/build/connection/connection-min.js\"></script> \n");
	//$wgOut->addScript("<link rel=\"stylesheet\" type=\"text/css\" href=\"http://yui.yahooapis.com/2.3.1/build/assets/skins/sam/skin.css\"> \n");
}

?>