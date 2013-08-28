<?php
$wgAjaxExportList [] = 'wfGetFlickrPhotos';
function wfGetFlickrPhotos($page,$search){ 
	global $wgIFI_FlickrAPIKey, $IP;
	
	require_once("$IP/extensions/wikia/FlickrImport/FlickrImportClass.php");
	require_once("$IP/extensions/wikia/FlickrImport/phpFlickr-2.1.0/phpFlickr.php");
	require_once("$IP/extensions/wikia/FlickrImport/FlickrImport.i18n.php" );
	# Add messages
	global $wgMessageCache, $wgFlickrImportMessages;
	foreach( $wgFlickrImportMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgFlickrImportMessages[$key], $key );
	}
	
	$search = urldecode($search);
	$f = new FlickrImport($wgIFI_FlickrAPIKey); 
	$output = $f->getPhotos( $page,$search );
	
	return $output;
}
?>