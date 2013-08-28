<?php
$wgExtensionFunctions[] = "wfSkinForce";

function wfSkinForce() {
        global $wgUser, $wgDefaultSkin, $wgSiteView, $wgValidSkinNames, $wgSitename, $wgRequest;
	
	if ($wgRequest->getVal( 'printable' ) == 'yes') {
		return false;
	}
	
	//if( $wgUser->getName()=="Pean" && $wgSitename == "Halo")$wgDefaultSkin = "Halodave";
	
	$wgValidSkinNames[$wgDefaultSkin] = $wgDefaultSkin;
	
        $wgUser->setOption( 'skin', $wgDefaultSkin );

}
?>