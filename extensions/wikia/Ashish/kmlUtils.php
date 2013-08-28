<?php

require_once("utils.php");

// do something about when a file only contains a network link
// absolute path to save the file

// global $wgArticle;

$SAVEPATH = "/usr/openserving/conf/docroots/sports/";
$SALT = "ee38a7e7da58bac";

// pull stuff off the request URL
$file = $_REQUEST["file"];
$action = $_REQUEST["action"];
$key = $_REQUEST["docKey"];
$editKey = $_REQUEST["editKey"];
$placemarkNum = $_REQUEST["num"];
$timestamp = $_REQUEST["timestamp"];

$ID = $_REQUEST["ID"];
$hash = $_REQUEST["hash"];
$points = $_REQUEST['point'];
$kmlFiles = $_REQUEST['kml'];
	
switch($action){

	// gets info about a kml file (number of placemarks, name, description) *
case "gMapsGetCount":
	getCount($file);
	break;

	// gets individual placemark data *
case "gMapsGetData":
	getData($file, $key, $placemarkNum);
	break;
	
	// saves the KML and point data *
case "gMapsSave":
	doSave($ID, $hash, $points, $kmlFiles, $editKey);
	break;
	
	// fetches data when recalling an old map *
case "gMapsRecallData":
	getRecallData($file, $placemarkNum);
	break;
	
	// gets an edit key to let someone edit the map
case "gMapsGetEditKey":
	getEditKey($ID, $hash, $timestamp);
	break;
	
default:
	// do nothing
	break;	
}

// generates an edit key to prevent destructive edits
function getEditKey($ID, $hash, $timestamp){
	global $SALT, $SAVEPATH;
	
	if($hash != md5($ID . $SALT))
		die("Save Error: Hash doesn't match!");
	
	$now = time();
	$editKey = md5($SALT . $ID . $now);
	
	// need to do something if the map has changed since the user loaded the page
	if(is_numeric($timestamp) && ( filemtime($SAVEPATH . $ID . ".kml") > $timestamp ) ){
		print 'alert("Someone edited the map. After the reload try and edit again.");
		        window.location.reload( true );';
		exit();
	}
	
	// a lock allready exists - check if its expired
	if( file_exists("/tmp/" . $hash . ".lock") ){
		$modtime = filemtime("/tmp/" . $hash . ".lock");
		if($now - $modtime < 180){ // CHANGE ME TO SOMETHING REASONABLE
			print 'alert("Sorry someone is editing the map. Try again in a few minutes.");';
			exit();
		}
	}
	
	// everything is good - tell the user
	file_put_contents("/tmp/" . $hash . ".lock", $editKey);
	
	print 'alert("Have fun editing!");
	       window.setTimeout("expireEditKey()", 180000);
	       Effect.Fade("editLockHref");
	       Effect.Appear("gMapsEditBox");
	       $("gMapEditKey").value = "' . $editKey . '";';
	
}

// caches a remote KML and returns the # of placemarks in the file 
function getCount($file){
	
	// create a hash for this file
	// download it and save it using the hash
	$key = md5( $file . time() );
	
	// save the file ect
	$data = file_get_contents($file); 
	$result = file_put_contents("/tmp/" . $key . ".kml", $data);
	chmod("/tmp/" . $key . ".kml", 0644);
	
	// fire up a KMLDocument
	try{
		$kml = new kmlDocument();
		$kml->loadData($data);
	}catch(Exception $e){
		die($e->getMessage());
	}
	
	// fetch info about the KML doc
	$docName = $kml->getDocName();
	$docDesc = $kml->getDocDesc();
	$numPlacemarks = $kml->getNumPlacemarks();	
	
	// $result = $kml->readPlacemarkInfo();
	$placemarkArray = $kml->getPlacemarkInfo();
	
	$placemarkInfo = null;
	
	// go through each placemark and snag the names and descriptions
	$i = 0;
	
	foreach($placemarkArray as $currPlacemark){
		if($i > 0)
			$placemarkInfo .= ",";
		
		$placemarkInfo .= '["' . $currPlacemark->name . '","' . $currPlacemark->desc . '"]';
		
		$i++;
	}
	
	// output a JSON object for the data
	print '
	{"docName": "' . addslashes($docName) . '",
		"docDesc": "' . addslashes($docDesc) . '",
		"docKey": "' . $key . '",
		"numPlacemarks":"' . $numPlacemarks . '",
	"placemarkInfo":[' . $placemarkInfo . ']}';		
}

/* used to split a XML/KML file up so that
Google maps can add each placemark individusally
returns the style info for each placemark */

function getData($file, $key, $placemarkNum){
	
	// check if we've grabbed this file allready
	// if we have there should be a key variable
	// if the file isnt there just cache it again
	
	if($key != "" && file_exists("/tmp/" . $key . ".kml")){
		$data = file_get_contents("/tmp/" . $key . ".kml");
	}else{
		
		$key = md5( $file . time() );
		$data = file_get_contents($file); 
		$result = file_put_contents("/tmp/" . $key . ".kml", $data);
		chmod("/tmp/" . $key . ".kml", 0644);
		
	}
	
	// load a kmldoc object
	try{
		$kml = new kmlDocument();
		$kml->loadData($data);
	}catch(Exception $e){
		die($e->getMessage());
	}
		
	// set the correct content
	header('Content-Type: application/vnd.google-earth.kml+xml');
	header('Pragma: no-cache');
	header('Cache-Control: no-cache');
	
	$numPlacemarks = $kml->getNumPlacemarks();
	
	if(!is_numeric($placemarkNum) || $placemarkNum > $numPlacemarks)
		die("Fatal Error: num is invalid!");
	
	print '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
   <Document>';
	
	// get the placemark the JS wants to load
	$placemarkData = $kml->getPlacemarkXML($placemarkNum);
	print $placemarkData;
	
	print "
	</Document>
	</kml>";
	
	// if we've done then unlink the file
	if($placemarkNum == $numPlacemarks)
		unlink("/tmp/". $key . ".kml");
	
}

// fetches the javascript to load the saved map
function getRecallCode($ID){
	global $SAVEPATH, $SCRIPTNAME;
	
	if(!file_exists($SAVEPATH . $ID . ".kml"))
		return "";
	
	$output = null;
	
	$file = $SAVEPATH . $ID . ".kml";
	
	// load a kmldoc object
	try{
		$kml = new kmlDocument();
		$kml->loadFile($file);
	}catch(Exception $e){
		die($e->getMessage());
	}
	
	// count up the number of placemarks
	$numPlacemarks = $kml->getNumPlacemarks();	
	$placemarkArray = $kml->getPlacemarkInfo();
	
	if($numPlacemarks == 0)
		return "";
	
	$index = 0;
	
	foreach($placemarkArray as $currPlacemark){
		
		$name = str_replace("\n", "", $currPlacemark->name);
		$desc = str_replace("\n", "", $currPlacemark->desc);
		
		$desc = str_replace('"', "'", $desc);
		$output .= '
		
		var index = kmlFiles.length;
		kmlFiles[index] = new Array();
		
		kmlFiles[index].placemarks = new Array();
		
		kmlFiles[index].file = "' . $SCRIPTNAME . '?action=gMapsRecallData&file=' . $ID . '&num=' . $index .'";
		kmlFiles[index].name = "' . $name .'";
		kmlFiles[index].desc = "' . $desc . '";
		
		var geoXml = null;
		geoXml = new GGeoXml("' . $SCRIPTNAME . '?action=gMapsRecallData&file=' . $ID . '&num=' . $index .'");
		
		map.addOverlay(geoXml);
		
		var tagData = new Object;
			
		tagData.name = "' . $name .'";
		tagData.desc = "' . $desc . '";
		tagData.obj = geoXml;
			
		kmlFiles[index].placemarks[0] = tagData;';
		
		$index ++;
	}
	
	return $output;
	
}

// fetches individual placemarks from the saved map
// used by the JS to load up the saved map

function getRecallData($file, $placemarkNum){
	global $SAVEPATH;
	
	if(!file_exists($SAVEPATH . $file . ".kml"))
		return "";
	
	$file = $SAVEPATH . $file . ".kml";
	// load the kml file
	try{
		$kml = new kmlDocument();
		$kml->loadFile($file);
	}catch(Exception $e){
		die($e->getMessage());
	}
		
	$numPlacemarks = $kml->getNumPlacemarks();	
	
	if(!is_numeric($placemarkNum) || $placemarkNum > $numPlacemarks)
		die("Fatal Error: num is invalid!");
	
	// set the correct content
	header('Content-Type: application/vnd.google-earth.kml+xml');
	header('Pragma: no-cache');
	header('Cache-Control: no-cache');
	
	echo '<?xml version="1.0" encoding="UTF-8"?> 
<kml xmlns="http://earth.google.com/kml/2.1">
   <Document>';
	
	// print the XML for the placemark we want
	$placemarkData = $kml->getPlacemarkXML($placemarkNum);
	print $placemarkData;
	
	print "
	</Document>
	</kml>";
}

// saves the current map
// figure out some way to "lock" the map
function doSave($ID, $hash, $points, $kmlFiles, $editKey){
	global $SAVEPATH, $SALT;
	
	$docName = null;
	$docDesc = null;
	$numPlacemarks = null;
	$userKml = "";
	
	if($hash != md5($ID . $SALT))
		die("Save Error: Hash doesn't match!");
	
	if(file_get_contents("/tmp/" . $hash . ".lock") != $editKey)
		die("Your edit session has expired!");		
	
	unlink("/tmp/" . $hash . ".lock");
	
	$userKml .= '<?xml version="1.0" encoding="UTF-8"?>
	<kml xmlns="http://earth.google.com/kml/2.0">
	<Document>
	';
	
	// get the KML for each file loaded on the map
	for($i = 0; $i < sizeof($kmlFiles); $i++){
		
		try{
			$kml = new kmlDocument();
			$kml->loadFile($kmlFiles[$i]);
		}catch(Exception $e){
			print $e->getMessage();
		}
		
		$numPlacemarks = $kml->getNumPlacemarks();
		
		for($j = 0; $j < $numPlacemarks; $j++){	
			$userKml .= $kml->getPlacemarkXML($j);
		}
		
	}
	
	// save the individual points
	for($i = 0; $i < sizeof($points); $i++){
		
		$point = $points[$i];
		
		$point[0] = str_replace("(", "", $point[0]);
		$point[0] = str_replace(")", "", $point[0]);
			
		list($long, $lat) = explode(",", $point[0]);
		$userKml .= '
			<Placemark>
			<description>' . $point[2] . '</description>
			<name>' . $point[1] . '</name>
			<View>
			<longitude>' . $long . '</longitude>
			<latitude>' . $lat . '</latitude>
			</View>
			<visibility>1</visibility>
			<Point>
			<coordinates>' . $lat .',' . $long . ',300</coordinates>
			</Point>
			</Placemark>
		';
	}
	
	
	
	$userKml .= "</Document></kml>";
	
	// save the file
	$result = file_put_contents($SAVEPATH . $ID . ".kml", $userKml);
	
	if(result == false)
		print "Error: Could not save map!";
	else
		print "Save succesfull!";
	
	exit();
	
}

?>
