<?php
  $wgExtensionFunctions[] = "wfskype";
 
 function wfSkype() {
     global $wgParser;
     $wgParser->setHook( "skype", "renderSkype" );
 }
 
 # The callback function for converting the input text to HTML output
 function renderSkype( $input, $argv ) {
     # $argv is an array containing any arguments passed to the extension like <example argument="foo" bar>..
     $output = '<!-- Skype "My status" button http://www.skype.com/go/skypebuttons -->';
     $output .= '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>';
     $output .= '<a href="skype:';
     $output .= $input ;
     $output .= '?call">';
     $output .= '<img src="http://mystatus.skype.com/smallclassic/';
     $output .= $input ;
     $output .= '" style="border: none;" alt="My status" /></a>';

     return $output;
 }
?>