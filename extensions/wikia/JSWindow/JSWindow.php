<?php

$wgExtensionFunctions[] = "wfJSWindow";


function wfJSWindow() {
        global $wgOut;
	$wgOut->addScript("<script type=\"text/javascript\" src=\"extensions/JSWindow/JSWindow.js?3\"></script>\n");
}
