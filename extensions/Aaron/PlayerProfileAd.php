<?php


$wgExtensionFunctions[] = "wfPlayerProfileAd";


function wfPlayerProfileAd() {
        global $wgParser;
        $wgParser->setHook( "PlayerProfileAd", "renderPlayerProfileAd" );
}
function renderPlayerProfileAd() {

$output .= "<script type='text/javascript'>\nvar federated_media_section = '';\n</script>\n<script type='text/javascript' src='http://static.fmpub.net/zone/817'></script>\n<script type='text/javascript'>\nvar federated_media_section = '';\n</script>\n<script type='text/javascript' src='http://static.fmpub.net/zone/859'></script>\n";

return $output;
}


?>