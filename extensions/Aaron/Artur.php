<?php


$wgExtensionFunctions[] = "wfArtur";


function wfArtur() {
        global $wgParser;
        $wgParser->setHook( "artur", "renderArtur" );
}
function renderArtur( ) {

$output = "";
$output .= "<p><img src=\"https://www.websitepulse.com/reports/charts/last.http.php?id=23904&;loc=9\"></p>";
$output .= "<p><img src=\"https://www.websitepulse.com/reports/charts/last.http.php?id=23904&;loc=9\"></p>";

return $output;
}


?>
