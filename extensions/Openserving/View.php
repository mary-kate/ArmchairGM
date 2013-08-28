<?php


$wgExtensionFunctions[] = "wfOpenservingView";


function wfOpenservingView() {
        global $wgOut, $wgSiteView, $wgMessageCache;
	require_once("viewClass.php");
	$wgSiteView = new SiteView();
	//echo wfMsg( 'pagetitle', "dave" );
	//exit();
	//$wgMessageCache->addMessages( array ('pagetitle' => 'bbb') );
	/*
	echo "before<br><br>";
	echo "pagetitle:" . wfMsg('pagetitle') . "<BR>";
	echo "mainpage:" . wfMsg('mainpage') . "<BR><br>";
	
	$wgMessageCache->addMessage( 'pagetitle', 'poop' );
	$wgMessageCache->addMessage( 'mainpage', 'poop' );
	
	echo "after<br><br>";
	echo "pagetitle:" . wfMsg('pagetitle') . "<BR>";
	echo "mainpage:" . $wgMessageCache->get('pagetitle') . "<BR>";
	echo "extensions..";
	exit();
	*/
	if($wgSiteView->getDomainName()!=""){
		$s .= '<link rel="stylesheet" href="index.php?title=Special:ViewCSS"  type="text/css" />' . "\n";
		
	}

}



?>