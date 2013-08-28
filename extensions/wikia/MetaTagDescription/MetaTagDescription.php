<?php
$wgExtensionFunctions[] = 'wfMetaTagDescription';

//wgExtensionFunctions
//new <metatagdescription> hook
function wfMetaTagDescription() {
	global $wgParser;
	$wgParser->setHook('metatagdescription', 'wfAddMetaTagDescription');
}

function wfAddMetaTagDescription($input){
	global $wgOut, $wgTitle;
	
	$tagParser = new Parser();
	$tag = $tagParser->parse( $input,  $wgTitle, $wgOut->parserOptions(),false );
	$tag = $tag->getText();
	
	$wgOut->addMeta ( 'description',$tag );
}
?>
