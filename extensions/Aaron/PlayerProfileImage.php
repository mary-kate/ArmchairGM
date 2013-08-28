<?php


$wgExtensionFunctions[] = "wfPlayerProfileImage";


function wfPlayerProfileImage() {
        global $wgParser;
        $wgParser->setHook( "PlayerProfileImage", "renderPlayerProfileImage" );
}
function renderPlayerProfileImage() {
	
	global $wgTitle, $wgUser, $wgUploadPath, $wgOut;
 
	$profile_image = Image::newFromName( "wikia_rofile_page_" . $wgTitle->getDBKey() . ".jpg" );
	if($profile_image->exists()){	
		//$profile_thumb = $img_two->createThumb(128);
		//$imgTwo = '<img width="' . ($img_two->getWidth() >= 128 ? 128 : $img_two->getWidth()) . '" alt="" src="' . $thumb_two_url . '?' . time() . '"/>';
	}else{
		$output .= "<img src=\"images/common/upload-an-image-profile.gif\" style=\"border:1px solid #dcdcdc\"><br>
			<a href=\"#\">(upload an image)</a>";
	}

	

	return $output;
}


?>