<?php   
# Google Maps Extension: wiki maps made easy
# Copyright Evan Miller, emiller@wso.williams.edu
# Version 0.5.5, 9 Sep 2006

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or 
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html

$wgExtensionFunctions[] = 'googleMaps_Install';
$wgHooks['EditPage::showEditForm:initial'][] = 'googleMaps_EditForm';

function googleMaps_EditForm($form)
{
    global $wgOut, $wgProxyKey, $wgJsMimeType, $wgGoogleMapsKey, $wgGoogleMapsDidInsertEssentialJs;
    $mapHeight = '300';
    $mapWidth = '660';
    $labelHeight = '170';
    $showMapText = 'show map';
    $hideMapText = 'hide map';
    $insertMapText = 'insert map centered here';
    $addPointText = 'add point here';
    $o = googleMaps_GetDefaults();
    $output = '';
    if (!$wgGoogleMapsDidInsertEssentialJs) {
    $output .= <<<END
    <script src="http://maps.google.com/maps?file=api&v=2&key=$wgGoogleMapsKey" type="$wgJsMimeType"></script>
    <script type="$wgJsMimeType">
//<![CDATA[

END;
    $output .= googleMaps_GetEssentialJS();
    $output .= "\n//]]>\n</script>";
    $wgGoogleMapsDidInsertEssentialJs = true;
    }
    $output .= <<<END
    <script type="$wgJsMimeType">
//<![CDATA[
var map_editor;
function insertGoogleMapPoint(lat, lon) { insertTags(lat+','+lon+',', "\\r\\n", 'label goes here'); } 
function insertGoogleMapLinks() { 
    var links = document.createElement('div'); 
    links.style.fontSize = "10px"; 
    links.innerHTML = '<a id="google_maps_show_link" href="javascript:showGoogleMap()">$showMapText</a> <a id="google_maps_toggle_link" href="javascript:toggleGoogleMap()" style="display: none;">$hideMapText</a>';
   document.getElementById('toolbar').appendChild(links); 
} 
function toggleGoogleMap() { 
    var map = document.getElementById("map_editor"); 
    link = document.getElementById("google_maps_toggle_link"); 
    if (map.style.display == "") { 
    map.style.display = "none"; 
    link.innerHTML = "$showMapText"; 
    } else { map.style.display = ""; 
    link.innerHTML = "$hideMapText"; 
    } 
}
function showGoogleMap() { 
    var map_div = document.createElement("div"); 
    map_div.setAttribute("id", "map_editor"); 
    map_div.style.width = "{$mapWidth}px"; 
    map_div.style.height = "{$mapHeight}px"; 
    document.getElementById('toolbar').appendChild(map_div);
    map_editor = new GMap2(map_div);
    map_editor.addControl(new GLargeMapControl()); 
    map_editor.addControl(new GMapTypeControl()); 
    map_editor.setCenter(new GLatLng({$o['lat']}, {$o['lon']}), {$o['zoom']}, {$o['type']});
    GEvent.addListener(map_editor, 'click', 
    function(overlay, point) { 
        if (overlay == null) { 
        map_editor.clearOverlays(); 
        } 
        map_editor.addOverlay(createMarker(point, '<div style="line-height: 20px; width: {$labelHeight}px;"><a href="javascript:insertGoogleMap('+point.lat()+', '+point.lng()+')">{$insertMapText}</a><br /><a href="javascript:insertGoogleMapPoint('+point.lat()+', '+point.lng()+')">{$addPointText}</a>')); 
    }); 
    document.getElementById("google_maps_show_link").style.display = "none"; 
    document.getElementById("google_maps_toggle_link").style.display = ""; 
    document.editform.wpTextbox1.focus(); 
    } 
    function insertGoogleMap(lat, lon) { 
    insertTags('<googlemap lat="'+lat+'" lon="'+lon+'" zoom="'+map_editor.getZoom()+'" type="'+map_editor.getCurrentMapType().getName(false).toLowerCase()+"\">\\r", "\\n\\r\\n</googlemap>", ""); 
    } 
    addLoadEvent(insertGoogleMapLinks); 
//]]>
</script>
END;
    $wgOut->addHTML($output);
    return true;
}

function googleMaps_GetOptionsDict() {
    return array(
    "type" => array("map" => "G_NORMAL_MAP", "normal" => "G_NORMAL_MAP", "hybrid" => "G_HYBRID_MAP", "satellite" => "G_SATELLITE_MAP" ),
    "controls" => array("small" => "GSmallZoomControl", "medium" => "GSmallMapControl", "large" => "GLargeMapControl"));
}

function googleMaps_GetDefaults() {
    global $wgGoogleMapsDefaults;
    # our defaults, in case $wgGoogleMapsDefaults isn't specified.
    $o = array("width" => 740, "height" => 600,
    "lat" => 42.711618, "lon" => -73.205112,
    "zoom" => 2, "type" => "G_HYBRID_MAP", "controls" => "GSmallMapControl" );

    # a dictionary for validating and interpreting some options.
    $dict = googleMaps_GetOptionsDict();

    # Go through the options and set it to the default in $wgGoogleMapsDefault if it's valid,
    # and then set it to the value in $argv if it's valid.
    foreach(array_keys($o) as $key) {
        # use the same tests for all numeric options
        if (is_numeric($o[$key])) {
            if (isset($wgGoogleMapsDefaults) && is_numeric($wgGoogleMapsDefaults[$key])) {
                $o[$key] = $wgGoogleMapsDefaults[$key];
            }
        }
        # add other tests here
    # Check $dict if this is an option with discrete possible values
    elseif ($dict[$key]) {
        if (isset($wgGoogleMapsDefaults) && $dict[$key][$wgGoogleMapsDefaults[$key]]) {
        $o[$key] = $dict[$key][$wgGoogleMapsDefaults[$key]];
        }
    }
    # add other tests here
    }
    return $o;
}


function googleMaps_Install()
{       
    global $wgParser, $wgHooks;
    $wgParser->setHook("googlemap", 'googleMaps_Render');

    # We'll insert our JavaScript all on one line, and then format it correctly
    # with this hook.
    $wgHooks['ParserAfterTidy'][] = 'googleMaps_CommentJS';
}   
    
function googleMaps_Render($source, $argv, &$parser)
{
    global $wgTitle, $wgOut, $wgGoogleMapsDefaults, $wgProxyKey, $wgGoogleMapsKey, $wgJsMimeType, $wgGoogleMapsOnThisPage, $wgGoogleMapsDidInsertEssentialJs;
    $localParser = new Parser();
    $incompatibleBrowserText = 'In order to see the map that would go in this space, please use a <a href="http://local.google.com/support/bin/answer.py?answer=16532&topic=1499">compatible web browser</a>.';
    // Keep a count of how many <googlemap> tags were used.
    if (!isset($wgGoogleMapsOnThisPage)) {
    $wgGoogleMapsOnThisPage = 1;
    } else {
    $wgGoogleMapsOnThisPage++;
    }

    # a dictionary for validating and interpreting some options.
    $dict = googleMaps_GetOptionsDict(); 
    $o = googleMaps_GetDefaults();

    // Override the defaults with what the user specified.
    foreach(array_keys($o) as $key) {
    if (is_numeric($o[$key]) && is_numeric($argv[$key])) {
        $o[$key] = $argv[$key];
    }
    elseif ($dict[$key] && $dict[$key][$argv[$key]]) {
        $o[$key] = $dict[$key][$argv[$key]];
    }
    }

    $output = '';

    // If this is the first map on the page, insert some one-time only stuff
    if (!$wgGoogleMapsDidInsertEssentialJs) {
    $output .= '<script src="http://maps.google.com/maps?file=api&v=2&key='.$wgGoogleMapsKey.'" type="'.$wgJsMimeType.'"></script>';
    $output .= "%%BEGINJAVASCRIPT{$wgProxyKey}%%";
    $output .= googleMaps_GetEssentialJS();
    $output .= "%%ENDJAVASCRIPT{$wgProxyKey}%%";
    $wgGoogleMapsDidInsertEssentialJs = true;
    }
    $output .= '<div id="map'.$wgGoogleMapsOnThisPage.'" style="width: '.$o['width'].'px; height: '.$o['height'].'px;"></div>';
    $output .= '%%BEGINJAVASCRIPT'.$wgProxyKey.'%%';
    $output .= <<<END
    function makeMap{$wgGoogleMapsOnThisPage}() { 
    if (GBrowserIsCompatible()) { 
        var map = new GMap2(document.getElementById("map{$wgGoogleMapsOnThisPage}")); 
        map.addControl(new {$o['controls']}()); 
        map.addControl(new GMapTypeControl()); 
        map.setCenter(new GLatLng({$o['lat']}, {$o['lon']}), {$o['zoom']}, {$o['type']});
END;
    $points = explode("\n", $source);
    for($i=0; $i < sizeof($points); $i++) {
        $pieces = explode(",", $points[$i]);
        $lat = array_shift($pieces);
        $lon = array_shift($pieces);
        $label = implode(',',$pieces);
        if (is_numeric($lat) && is_numeric($lon)) {
        // This function lets us insert wiki markup into the map markers.
        // (it doesn't do all of the cleanup as the regular parse function,
        // which we eschew because it calls some hooks that we only want to run
        // once)
        $pOutput = $localParser->parse($label, $parser->mTitle, $parser->mOptions, false);
        $output .= " map.addOverlay(createMarker(new GPoint({$lon}, {$lat}), '<div style=\"width: 200px;\">".addslashes($pOutput->getText())." </div>'));";
        }
    }
    $output .= <<<END
    } else { 
    document.write('<div style="width: {$o['width']}px; height: {$o['height']}px; font-style: italic;">{$incompatibleBrowserText}</div>'); 
    }
    } 
    addLoadEvent(makeMap{$wgGoogleMapsOnThisPage});%%ENDJAVASCRIPT{$wgProxyKey}%%
END;
    return preg_replace('/[\t\n]/', '', $output);
}

function googleMaps_GetEssentialJS() {
    $js = <<<END
    function createMarker(point, label) { 
    var marker = new GMarker(point); 
    GEvent.addListener(marker, 'click', function() { marker.openInfoWindowHtml(label); }); 
    return marker; 
    } 
    function addLoadEvent(func) { 
    var oldonload = window.onload; 
    if (typeof oldonload == 'function') { 
        window.onload = function() { 
        oldonload(); 
        func(); 
        }; 
    } else { 
        window.onload = func; 
    } 
    } 
    window.unload = GUnload;
END;
    return preg_replace('/  +/', ' ', preg_replace('/[\n\t]/', '', $js));
}

function googleMaps_CommentJS(&$parser, &$text) {
    // having $wgProxyKey in the substitution makes sure that no one
    // can just put %%BEGINJAVASCRIPT%% in a wiki page and therby inject 
    // arbitrary JS into the page.
    global $wgJsMimeType, $wgProxyKey, $wgGoogleMapsOnThisPage;
    $text = preg_replace("/%%BEGINJAVASCRIPT{$wgProxyKey}%%/", "<script type=\"{$wgJsMimeType}\">\n//<![CDATA[\n", $text);
    $text = preg_replace("/%%ENDJAVASCRIPT{$wgProxyKey}%%/", "\n//]]>\n</script>\n", $text);
}

?>