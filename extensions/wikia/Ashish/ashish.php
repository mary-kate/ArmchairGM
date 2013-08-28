<?php

$wgExtensionFunctions[] = 'wfTest';
$SCRIPTNAME = "http://sports.box8.tpa.wikia-inc.com/extensions/wikia/Ashish/kmlUtils.php";
$ABSOLUTEPATH = "http://sports.box8.tpa.wikia-inc.com/index.php?";
$MAPSKEY = "ABQIAAAA_6icK9WzMbH5eGCPpoZKHhToWDy-c01ebj4dpClJ0MiFW-06yxTqXdb6jVMULIW1MMEewVHcQ2g3Uw";

function wfTest(){
	global $wgUser,$IP, $wgParser;
	$wgParser->setHook( "ashish", "RenderAshish" );
}

function RenderAshish($input){
	require_once("kmlUtils.php");
	
	global $wgUser, $wgOut, $IP, $SCRIPTNAME, $MAPSKEY, $SAVEPATH;
	global $wgTitle;
	
	// pull variables out of the wiki markup
	getValue($center,$input,"center");
	getValue($pointCount,$input,"num");
	getValue($zoom, $input, "zoom");
	getValue($height,$input,"height");
	getValue($width,$input,"width");
	
	// used by the save functon
	// get a GUID for the page the map is on
	// hash it with a salt to prevent malicious over-writes
	
	$docID = $wgTitle->getArticleID();
	$docHash = md5($docID . $SALT);
	
	// extract the center coords
	list($lat, $long) = explode(',', $center);
	
	// make sure the variables make sense
	if(!is_numeric($lat))
		$lat = "40.752816";
	
	if(!is_numeric($long))
		$long = "-73.984036";
	
	if(!is_numeric($num))
		$num = 0;
	
	if(!is_numeric($zoom))
		$zoom = 10;
	
	if(!is_numeric($height))
		$height = 300;
	
	if(!is_numeric($width))
		$width = 500;
	
	// get a timestamp for the last mod of the map
	if( file_exists($SAVEPATH . $docID . ".kml") ){
		$mapModTime = filemtime($SAVEPATH . $docID . ".kml");
	}else{
		$mapModTime = ""; // the map is still blank	
	}
	
	// get the jscript to recall old placemarks
	$recallCode = getRecallCode($docID);
	
	// run the javascript for the page through the php interpreter
	// theres a few spots that print $somevar
	$jsCode = file_get_contents("http://sports.box8.tpa.wikia-inc.com/extensions/wikia/Ashish/gmapcode.js");
	$jsCodeParsed = eval($jsCode);
	
	// when the page loads initiliaze the map
	$wgOut->setOnLoadHandler('load()');
	
	// drop in the javascript
	$wgOut->addScript($jsCodeParsed);
	
	// all the html
	$output = '
	
	<div id="editLockHref"> <a href="javascript:getEditLock()"> Edit Map </a> </div>
	
	<br />
	
	<div id="gMapsEditBox" style="display:none">

		<a href = "javascript:showGeotag();"> Add Geotag </a> &nbsp; &nbsp; &nbsp; &nbsp;
		<a href ="javascript:showKML();"> Load KML </a> &nbsp; &nbsp; &nbsp; &nbsp;
		<a href ="javascript:showCenter();"> Move Map Center </a> <br />
		<a href ="javascript:viewTags();"> Manage Geotags </a> &nbsp; &nbsp; &nbsp; &nbsp;
		<a href ="javascript:gMapsSaveTags();"> Save Geotags </a>
	
		<div id="recenterBox" style="display:none; height:auto; width:auto;">
			Address: <input type="text" id="centerAddr" name="centerAddr" />
			<input name="centerUpdate" type="button" onclick="javascript:moveCenter();" id="centerUpdate" value="Update" />
		</div>
		
		<div id="KMLBox" style="display:none; height:auto; width:auto;">
			KML URL: <input type="text" id="KMLAddr" name="KMLAddr" />
			<input name="KMLLoad" type="button" onclick="javascript:loadKMLFile();" id="KMLLoad" value="Load" />
		</div>
		    
		<div id="addTagBox" style="display:none; height:auto; width:auto;">
			Address: <input type="text" id="tagAddr" name="tagAddr" /> <br />
			Caption: <input type="text" id="tagText" name="tagText" />
			<input name="addTag" type="button" onclick="javascript:addGeotag();" id="addTag" value="Add" />
			<input name="doneTag" type="button" onclick="javascript:hideGeoTag();" id="addTag" value="Done" />
		</div>

		<div name="viewAllTags" id="viewAllTags" style="display:none; height:auto; width:auto;"></div>
		
		<input type="hidden" name="gMapEditKey" id="gMapEditKey" value="">
		
	</div>
	
		    
	<div id="map" style="width:' . $width . 'px; height:' . $height . 'px"></div>';
		    
	return $output;
}

?>