?>
<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=<?php print $MAPSKEY ?>" type="text/javascript"></script>

<script language="JavaScript">

/* This file contains all the javascript for the map module */

/* google maps API include
     the script is loading v=2.x which is the stable experimental release
     loading v=2 makes KML imports not work so well - popups don't work.
     From Google:
        The v parameter within the http://maps.google.com/maps?file=api&v=2 
        URL refers to the version number of the Google Maps API to use. 
        Most users of the API will want to use the stable "Version 2" 
        API by passing the v=2 parameter within that URL. 
        You may instead obtain the latest release (including the latest features) 
        by passing v=2.x instead. However, be aware that the latest release may not be as stable as the v=2 release.*/

    // evil globals
    var map = null;
        
    var geoTags = new Array(); // holds the added placemarks
    var kmlFiles = new Array(); // holds the kml files and the saved map data
    
    // make sure to clean things up
    window.onbeforeunload = GUnload();
    
        // two different loads - one when its a clean map and one to load existing stuff
        // load the map into the "map" div
        // place the markers from the arguments
     function load() {
        if (GBrowserIsCompatible()) {
        
            map = new GMap2( document.getElementById("map") ); 
            map.setCenter(new GLatLng(<?php print $lat ?>,<?php print $long ?>), <?php print $zoom ?>);
                
            map.addControl(new GLargeMapControl());
            map.addControl(new GMapTypeControl());
            map.addControl(new GOverviewMapControl());
                
        }
        
	// get the code to put saved data back on the map
	
        <?php print $recallCode ?>
     }
    
    // helper function - create the map markers
    function createMarker(point, caption) {
        var marker = new GMarker(point);
        caption = caption.replace(/<script>/i, "script");
        
        GEvent.addListener(marker, "click", 
               function() { marker.openInfoWindowHtml(caption);} );
        
        return marker;
    }
    
    // shift the center of the map
    function moveCenter(){
        var geocoder = new GClientGeocoder();
        address = $F("centerAddr");
        
        geocoder.getLatLng(address, 
                function(point) {   if (!point) {   
                                        alert("Could not geocode address!");    
                                    } else {
                                        map.panTo(point);
                                        }
                } );
        
        $("centerAddr").value = "";
        
	Effect.Fade("recenterBox");
    }
    
    // loads data about a KML file into the main array
    function loadKMLData(kmlData, kmlFile){
        
        var index = kmlFiles.length;
        kmlFiles[index] = new Array();
        kmlFiles[index] = new Object;
        
        
        kmlFiles[index].file = kmlFile;
        kmlFiles[index].name = kmlData.docName;
        kmlFiles[index].desc = kmlData.docDesc;
        
        kmlFiles[index].placemarks = new Array();
        
        for(var i = 0; i < kmlData.numPlacemarks; i++){
            var tagData = new Object;
            
            tagData.name = kmlData.placemarkInfo[i][0];
            tagData.desc = kmlData.placemarkInfo[i][1];
            tagData.obj = kmlData.placemarkInfo[i][2];
            tagData.url = kmlData.placemarkInfo[i][3];
            
            kmlFiles[index].placemarks[i] = tagData;
        }
        
        $("KMLAddr").value = "";
        Effect.Fade("KMLBox");
        
        }
    
    // load a KML file onto the map
    function loadKMLFile(){
        
        new Ajax.Request("<?php print $SCRIPTNAME ?>?action=gMapsGetCount&file=" + encodeURIComponent($F("KMLAddr")), 
            {method: "get", onComplete: 
                function(transport) {
                    // check whats going on when this fails
                    var kmlData = eval("(" + transport.responseText + ")");
                    
                    for(var i = 0; i < kmlData.numPlacemarks; i++){
                        
                        var geoXml = null;
                        do{
                            // not sure why this needs to be an absolute link
                            geoXml = new GGeoXml("<?php print $SCRIPTNAME?>?action=gMapsGetData&docKey="+kmlData.docKey+"&num=" + i + "&file=" + $F("KMLAddr"));
                        }while(geoXml == null);
            
                        map.addOverlay(geoXml);
                        
                        kmlData.placemarkInfo[i][2] = geoXml;
                        kmlData.placemarkInfo[i][3] = "<?php print $SCRIPTNAME ?>?action=gMapsGetData&docKey="+kmlData.docKey+"&num=" + i + "&file=" + $F("KMLAddr");
                    }
                    
                    loadKMLData(kmlData, $F("KMLAddr"));
                }	
        }
        );
        
    } 
    
    // remove a KML element from the map
    function deleteKml(ID, IDJ){
        
        if(ID < kmlFiles.length && IDJ < kmlFiles[ID].placemarks.length){
            
            map.removeOverlay(kmlFiles[ID].placemarks[IDJ].obj);
        
            kmlFiles[ID].placemarks[IDJ] = null;
            
            kmlFiles[ID].placemarks = kmlFiles[ID].placemarks.compact();
        
            if(kmlFiles[ID].placemarks.length == 0){
                kmlFiles[ID] = null;
                kmlFiles = kmlFiles.compact();
            }
            
            map.closeInfoWindow();
            
            viewTags();
        }
    }
    
    // stick new tags into the array
    function addToArray(point, address, caption, marker){
        var index = geoTags.length;
        
        geoTags[index] = new Array();
        
        geoTags[index].point = point;
        geoTags[index].addr = address;
        geoTags[index].caption = caption;
        geoTags[index].marker = marker;
        
    }
    
    // remove a placemark from the map and array
    function removeTag(ID){
        if(ID < geoTags.length){
            map.closeInfoWindow();
            
            map.removeOverlay(geoTags[ID].marker);
        
            geoTags[ID] = null;
            
            geoTags = geoTags.compact();
            
            viewTags();
        }
    }
    
    // add a new marker onto the map
    function addGeotag(){
        
        var geocoder = new GClientGeocoder();
        address = $F("tagAddr");
        caption = $F("tagText");
        
        // geocode and address into lat+long coordinates
        // add the marker onto the map and into the array
        geocoder.getLatLng(address, 
            function(point) {
                if (point) {
                    var gMark = createMarker(point, caption);
                    map.addOverlay( gMark );
                    addToArray(point, address, caption, gMark);
                }else{
                    alert("Could not geocode address!");
                }
            }
        );
        
        
        $("tagAddr").value = "";
        $("tagText").value = "";
        
    }
    
    // save all the data on the map
    // basically submits the placemarks + KML elements to a PHP script
    function gMapsSaveTags(){
        
        var url = "<?php print $SCRIPTNAME ?>";
        var params = "ID=<?php print $docID ?>&hash=<?php print $docHash ?>&action=gMapsSave&editKey=" + $F("gMapEditKey") + "&";
        
        if(geoTags.length > 0){
            params += "point[0][0]=" + encodeURIComponent(geoTags[0].point) +
            "&point[0][1]=" + encodeURIComponent(geoTags[0].addr) +
            "&point[0][2]=" + encodeURIComponent(geoTags[0].caption);
        }
        
        for(var i = 1; i < geoTags.length; i++){
            params += "&point["+i+"][0]=" + encodeURIComponent(geoTags[i].point) +
                "&point["+i+"][1]=" + encodeURIComponent(geoTags[i].addr) +
                "&point["+i+"][2]=" + encodeURIComponent(geoTags[i].caption);
        }
        
        var index = 0;
        
        for(var i = 0; i < kmlFiles.length; i++){
            
            if(kmlFiles[i].placemarks.length > 1){
                for(var j = 0; j < kmlFiles[i].placemarks.length; j++){
                    params += "&kml[" + index + "]=" + encodeURIComponent(kmlFiles[i].placemarks[j].url);
                    index++;
                }
            }else{
                params += "&kml[" + index + "]=" + encodeURIComponent(kmlFiles[i].file);
                index++;
            }
        }

        new Ajax.Request(url, {method: "post", postBody: params, onComplete: function(transport) {	alert(transport.responseText);	} });
	
	Effect.Appear("editLockHref");
	Effect.Fade("gMapsEditBox");
    }
    
    // displays the interface to manage tags
    function viewTags(){
	Effect.Appear("viewAllTags");
        var output = "<a href=\'javascript:hideTagsBox()\'>Hide</a> <br /><font size=\'3\'>Created Tags:</font><br />";
        
        for(var i = 0; i < geoTags.length; i++){
            output += geoTags[i].point + " -- " + geoTags[i].addr + " --" + geoTags[i].caption + " &nbsp; &nbsp; &nbsp; <a href=\'javascript:removeTag("+i+")\'>Delete</a> <br />";	
        }
        
        output += "<font size=\'3\'>Loaded KML files:</font><br />";
        
        for(var i = 0; i < kmlFiles.length; i++){
            output += "<strong>" + kmlFiles[i].file + " : " + kmlFiles[i].name + "</strong><br>";
            for(var j = 0; j < kmlFiles[i].placemarks.length; j++){
                output += kmlFiles[i].placemarks[j].name + " &nbsp; &nbsp; &nbsp; <a href = \'javascript:deleteKml(" + i + "," + j + ")\'> Delete </a> <br />";
            }
        }
        
        $("viewAllTags").innerHTML = output;
    }
    
    // called after an edit key is expired
    function expireEditKey(){
	alert("Your edit session has expired!");
	Effect.Appear("editLockHref");
	Effect.Fade("gMapsEditBox");
    }
    
    // gets a "lock" on the map so edits can be made
    function getEditLock(){
	    
	    new Ajax.Request("<?php print $SCRIPTNAME ?>", {
			    method: "post", 
			    postBody: "ID=<?php print $docID ?>&hash=<?php print $docHash ?>&timestamp=<?php print $mapModTime?>&action=gMapsGetEditKey", onComplete: 
	    	function(transport) {
			
			eval(transport.responseText);
			
			/* if(transport.responseText != "-1"){
				alert("Have fun editing!");
				Effect.Fade("editLockHref");
				Effect.Appear("gMapsEditBox");
				$("gMapEditKey").value = transport.responseText;
			}else{
				alert("Sorry someone is editing the map. Try again in a few minutes.");	
			} */
	    	} });
    }
    
    // Show and Hide helper functions
    function showCenter(){ Effect.Appear('recenterBox'); }
    
    function showGeotag(){ Effect.Appear('addTagBox'); }
    
    function showKML(){ Effect.Appear('KMLBox'); };
    
    function hideGeoTag(){ Effect.Fade('addTagBox'); }
    
    function hideTagsBox(){ Effect.Fade('viewAllTags'); }
    
    </script>
