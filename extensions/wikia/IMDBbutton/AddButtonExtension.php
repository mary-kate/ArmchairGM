<?php 
  ##_____________________________________________________________________
  ##    copyright 2006 Assela Pathirana
  ##    UNDER GNU GPL
  ##____version .3_________________________________________________________________
  ##
  ##    This program is free software; you can redistribute it and/or modify
  ##    it under the terms of the GNU General Public License as published by
  ##    the Free Software Foundation; either version 2 of the License, or
  ##    (at your option) any later version.
  ##
  ##    This program is distributed in the hope that it will be useful,
  ##    but WITHOUT ANY WARRANTY; without even the implied warranty of
  ##    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  ##    GNU General Public License for more details.
  ##
  ##    You should have received a copy of the GNU General Public License
  ##    along with this program; if not, write to the Free Software
  ##    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  ##
  ##

$wgExtensionFunctions[] = "wfAddButtonExtension";
function wfAddButtonExtension () {
  global $wgParser;
  global $wgHooks; 
  $wgHooks['ParserBeforeTidy'][] = 'addbuttonDo' ;
}
function addbuttonDo (&$parser, &$text){ 
  static $done=0;
  if ( $done ) { 
    return;
  }
  $done=1;
  global $wgRequest;
  global $wgAddButtonExtensionPath;
  global $wgScriptPath;
  global $wgUser;
  global $IP;
  $sk=$wgUser->getSkin();
  $action =  $wgRequest->getVal( 'action', false );
  //Are we in a edit page AND are we allowed to edit
  if(strtolower($action) == "edit" || strtolower($action) == "submit"){  
  //strtolower^^ is not needed, but to  be safe
  //now we know that we are in the  edit dialog so add some javascript code to add a new button
  // This is done by mainfile.js
 $vars= "
  <script type='text/javascript' language='JavaScript'> ; 
  var addbuttonextension_iconpath='"."$wgScriptPath/$wgAddButtonExtensionPath"."' //global variable
  ";

  //get the button information from the file

  $jscript=file_get_contents("$IP/$wgAddButtonExtensionPath/addButtonsHere.js");
  $vars=$vars.$jscript."</script>";

  //Read in the mainfile.js

  $jscript=file_get_contents("$IP/$wgAddButtonExtensionPath/mainfile.js");
  $text=$vars."
  <!--- START AddButtonExtension JavaScript -->
  <script type='text/javascript' language='JavaScript'>
  "
        .$jscript.
  "
        </script>
  <!--- END AddButtonExtension JavaScript -->
  "
        .$text;	
  }else{
    //Either not an edit attempt, or not logged in 
    //Do nothing be silent without bothering innocent visitors. 
   $text="
  <!--- AddButtonExtension is in but inacative in the context -->
  "
        .$text;	
  }
}
?>
