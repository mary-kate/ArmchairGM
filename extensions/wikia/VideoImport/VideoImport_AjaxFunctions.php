<?php
$wgAjaxExportList [] = 'wfGetYouTubeVideos';
function wfGetYouTubeVideos($page,$search){ 
	global  $IP;
	
	require_once("$IP/extensions/wikia/VideoImport/YouTubeImportClass.php");
	require_once("$IP/extensions/wikia/VideoImport/VideoImport.i18n.php" );
	# Add messages
	global $wgMessageCache, $wgVideoImportMessages;
	foreach( $wgVideoImportMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgVideoImportMessages[$key], $key );
	}
	
	$search = urldecode($search);

	$f = new YoutubeImport( ); 
	$output = $f->getVideos( $page,$search );
	
	return $output;
}
?>