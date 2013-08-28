<?php
 /*

  RandomSelection v2.0 -- 8/09/06

  This extension randomly displays one of the given options.

  Usage: <choose><option>A</option><option>B</option></choose>
  Optional parameter: <option weight="3"> == 3x weight given

  Author: Algorithm [http://meta.wikimedia.org/wiki/User:Algorithm]
 */

 $wgExtensionFunctions[] = "wfRandomSelection";
 $wgExtensionCredits['parserhook'][] = array(
 'name' => 'RandomSelection',
 'url' => 'http://meta.wikimedia.org/wiki/User:Algorithm/RandomSelection',
 'version' => '2.0'
 );

 function wfRandomSelection()
 {
     global $wgParser;
     $wgParser->setHook( "choose", "renderChosen" );
 }

 function renderChosen( $input, $argv, &$parser )
 {
     # Prevent caching
     $parser->disableCache();

     # Parse the options and calculate total weight
     $len = preg_match_all("/<option(?:(?:\\s[^>]*?)?\\sweight=[\"']?([^\\s>]+))?"
         . "(?:\\s[^>]*)?>([\\s\\S]*?)<\\/option>/", $input, $out);
     $r = 0;
     for($i=0; $i<$len; $i++)
     {
         if(strlen($out[1][$i])==0) $out[1][$i] = 1;
         else $out[1][$i] = intval($out[1][$i]);
         $r += $out[1][$i];
     }

     # Choose an option at random
     if($r <= 0) return "";
     $r = mt_rand(1,$r);
     for($i=0; $i<$len; $i++)
     {
         $r -= $out[1][$i];
         if($r <= 0)
         {
             $input = $out[2][$i];
             break;
         }
     }

     # Create new parser to handle rendering
     $localParser = new Parser();

     # Initialize defaults, then copy info from parent parser
     $localParser->clearState();
     $localParser->mTagHooks         = $parser->mTagHooks;
     $localParser->mTemplates        = $parser->mTemplates;
     $localParser->mTemplatePath     = $parser->mTemplatePath;
     $localParser->mFunctionHooks    = $parser->mFunctionHooks;
     $localParser->mFunctionSynonyms = $parser->mFunctionSynonyms;

     # Render the chosen option
     $output = $localParser->parse($input, $parser->mTitle,
                                   $parser->mOptions, false, false);
     return $output->getText();
 }
?>
