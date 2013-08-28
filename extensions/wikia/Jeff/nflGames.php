<?php

$wgExtensionFunctions[] = "wfNflGames";

function wfNflGames() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "nflGames", "getNflGames" );
}



$file = "/usr/openserving/conf/mediawiki/wiki2/extensions/wikia/Jeff/2007NFLsched.xml";
$character_data_on = false;
$tag_complete = true;
$games = array();
$current_tag = "";
$current_game = 0;
$game_tag = "game";
$in_game_tag = false;


$abbrs = array(
"Atlanta" => "ATL",
"Arizona" => "ARI",
"Baltimore" => "BAL",
"Buffalo" => "BUF",
"Carolina" => "CAR",
"Chicago" => "CHI",
"Cincinnati" => "CIN",
"Cleveland" => "CLE",
"Dallas" => "DAL",
"Denver" => "DEN",
"Detroit" => "DET",
"Green Bay" => "GB",
"Houston" => "HOU",
"Indianapolis" => "IND",
"Jacksonville" => "JAC",
"Kansas City" => "KC",
"Miami" => "MIA",
"Minnesota" => "MIN",
"New England" => "NE",
"New Orleans" => "NO",
"N.Y. Giants" => "NYG",
"N.Y. Jets" => "NYJ",
"Oakland" => "OAK",
"Philadelphia" => "PHI",
"Pittsburgh" => "PIT",
"St. Louis" => "STL",
"San Diego" => "SD",
"San Francisco" => "SF",
"Seattle" => "SEA",
"Tampa Bay" => "TB",
"Tennessee" => "TEN",
"Washington" => "WAS");

$nicks = array(
"Atlanta" => "Falcons",
"Arizona" => "Cardinals",
"Baltimore" => "Ravens",
"Buffalo" => "Bills",
"Carolina" => "Panthers",
"Chicago" => "Bears",
"Cincinnati" => "Bengals",
"Cleveland" => "Browns",
"Dallas" => "Cowboys",
"Denver" => "Broncos",
"Detroit" => "Lions",
"Green Bay" => "Packers",
"Houston" => "Texans",
"Indianapolis" => "Colts",
"Jacksonville" => "Jaguars",
"Kansas City" => "Chiefs",
"Miami" => "Dolphins",
"Minnesota" => "Vikings",
"New England" => "Patriots",
"New Orleans" => "Saints",
"N.Y. Giants" => " ",
"N.Y. Jets" => " ",
"Oakland" => "Raiders",
"Philadelphia" => "Eagles",
"Pittsburgh" => "Steelers",
"St. Louis" => "Rams",
"San Diego" => "Chargers",
"San Francisco" => "49ers",
"Seattle" => "Seahawks",
"Tampa Bay" => "Buccaneers",
"Tennessee" => "Titans",
"Washington" => "Redskins");




function getNflGames($input, $args, $parser) {

	global $file;
	global $character_data_on;
	global $tag_complete;
	global $games;
	global $current_tag;
	global $current_game;
	global $game_tag;
	global $in_game_tag;
	global $abbrs;
	global $nicks;
	
	$output = "";
	$sport_id = 2;
	
	$xml_parser = xml_parser_create();
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	if (!($fp = fopen($file, "r"))) {
	    die("could not open XML input");
	}
	
	//echo "<pre>";
	while ($file_content = fread($fp, 4096)) {
	    if (!xml_parse($xml_parser, $file_content, feof($fp))) {
		die(sprintf("XML error: %s at line %d",
			    xml_error_string(xml_get_error_code($xml_parser)),
			    xml_get_current_line_number($xml_parser)));
	    }
	}
	//echo "</pre>";
	xml_parser_free($xml_parser);
	
	for ($i=0; $i<sizeof($games); $i++) {
		
		$identifier = createIdentifier($abbrs[$games[$i]["home"]], $abbrs[$games[$i]["away"]], $games[$i]["datetime"]);
	 
	 //$output .= "{$i} - {$games[$i]["away"]} ({$abbrs[$games[$i]["away"]]}) @ {$games[$i]["home"]} ({$abbrs[$games[$i]["home"]]}) - {$games[$i]["week"]} - {$games[$i]["datetime"]} <br/>";
	 $output .= "{$i} - {$games[$i]["away"]} {$nicks[$games[$i]["away"]]} ({$abbrs[$games[$i]["away"]]}) @ {$games[$i]["home"]} {$nicks[$games[$i]["home"]]} ({$abbrs[$games[$i]["home"]]}) - {$games[$i]["week"]} - {$games[$i]["datetime"]} -  {$identifier}<br/>";
	 //do_nfl_insert($games[$i]["datetime"], $games[$i]["home"], $games[$i]["away"], $identifier, $abbrs[$games[$i]["home"]], $abbrs[$games[$i]["away"]], $nicks[$games[$i]["home"]], $nicks[$games[$i]["away"]], $games[$i]["week"], $sport_id);
	 //do_nfl_update($games[$i]["datetime"], $games[$i]["home"], $games[$i]["away"], $identifier, $abbrs[$games[$i]["home"]], $abbrs[$games[$i]["away"]], $nicks[$games[$i]["home"]], $nicks[$games[$i]["away"]], $games[$i]["week"], $sport_id);
	 }

	 return $output;

}

function do_nfl_insert($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$week,$sport_id) {
	global $wgUser;
	$dbr =& wfGetDB( DB_MASTER );
	$dbr->insert( '`pick_games`',
	array(
		'pick_sport_id' => $sport_id,
		'pick_game_date' => $date,
		'pick_game_visitor' => $vis,
		'pick_game_home' => $home,
		'pick_visitor_addl' => $vis_addl,
		'pick_home_addl' => $home_addl,
		'pick_vis_abbr' => $vis_abbr,
		'pick_home_abbr' => $home_abbr,
		'pick_category' => $week,
		'pick_identifier' => $identifier
		), __METHOD__
	);	
	return $dbr->insertId();
}

function do_nfl_update($date,$home,$vis,$identifier,$home_abbr,$vis_abbr,$home_addl,$vis_addl,$week,$sport_id) {
	global $wgUser;
	$dbr =& wfGetDB( DB_MASTER );
	$dbr->update( '`pick_games`',
	array(
		'pick_sport_id' => $sport_id,
		'pick_game_date' => $date,
		'pick_game_visitor' => $vis,
		'pick_game_home' => $home,
		'pick_visitor_addl' => $vis_addl,
		'pick_home_addl' => $home_addl,
		'pick_vis_abbr' => $vis_abbr,
		'pick_home_abbr' => $home_abbr,
		'pick_category' => $week,
		'pick_identifier' => $identifier
		),
	array(
		'pick_identifier' => $identifier
	)
		, __METHOD__
	);	
	//return $dbr->insertId();
}


function startElement($parser, $name, $attrs)
{
    global $character_data_on;
    global $tag_complete;
    global $games;
    global $current_tag;
    global $current_game;
    global $game_tag;
    global $in_game_tag;
    
    $current_tag = $name;
    
    if($name == $game_tag){
		$in_game_tag = true;
		//echo "entering game tag...<br/>";
	}
   
    //echo "&lt;<font color=\"#0000cc\">$name</font>";
    //## Print the attributes ##//
    if (sizeof($attrs)) {
        while (list($k, $v) = each($attrs)) {
            //echo " <font color=\"#009900\">$k</font>=\"<font
            //       color=\"#990000\">$v</font>\"";
        }
    }
    //## Tag is still still incomplete,
    //## will be completed at either endElement or characterData ##//
    $tag_complete = false;
    $character_data_on = false;
}

function endElement($parser, $name)
{
    global $fp;
    global $character_data_on;
    global $tag_complete;
    global $games;
    global $current_tag;
    global $current_game;
    global $game_tag;
    global $in_game_tag;
	   
    //#### Test for self-closing tag ####//
    //## xml_get_current_byte_index(resource parser) when run in this
    //## function, gives the index at (indicated by *):
    //##   for self closing tag: <br />*
    //##   for individual closing tag: <div>character data*</div>
    //## So to test for self-closing tag, we can just test for the last 2
    //## characters from the index
    //###################################//
   
    if (!$character_data_on) {
        //## Record current fp position ##//
        $temp_fp = ftell($fp);
       
        //## Point fp to 2 bytes before the end element byte index ##//
        $end_element_byte_index = xml_get_current_byte_index($parser);
        fseek($fp,$end_element_byte_index-2);
       
        //## Gets the last 2 characters before the end element byte index ##//
        $validator = fgets($fp, 3);
       
        //## Restore fp position ##//
        fseek($fp,$temp_fp);
       
        //## If the last 2 character is "/>" ##//
        if ($validator=="/>") {
            //// Complete the self-closing tag ////
            //echo " /&gt";
            //// Otherwise it is an individual closing tag ////
        } else {}
		//echo "&gt&lt/<font color=\"#0000cc\">$name</font>&gt";
        $tag_complete = true;

    } else {}
	//echo "&lt/<font color=\"#0000cc\">$name</font>&gt";
	
	        if ($name == $game_tag) {
			//echo "exiting game tag...<br/>";
         $in_game_tag = false;
         $current_game++;
         }
        else {
         //echo $name;
         }
   
    $character_data_on = false;
}

function characterData($parser, $data)
{
    global $character_data_on;
    global $tag_complete;
    global $games;
    global $current_tag;
    global $current_game;
    global $game_tag;
    global $in_game_tag;
	   
    if ((!$character_data_on)&&(!$tag_complete)) {
        //echo "&gt";
        $tag_complete = true;
    }
    //echo "<b>$data</b>";
    if ($in_game_tag && ($current_tag != $game_tag) & !isset($games[$current_game][$current_tag])) {
     //echo "adding {$data} to {$current_tag} for item number {$current_game}<br/>";
    	$games[$current_game][$current_tag] = $data;
    }
    $character_data_on = true;
}

function createIdentifier($home_abbr, $away_abbr, $date_time) {
 
	$return_val = "NFL_";
	
	$date_sub = substr($date_time, 0, strpos($date_time, " "));
	
	$return_val .= str_replace("-", "", $date_sub);
	
	$return_val .= "_{$away_abbr}@{$home_abbr}"; 
	return $return_val;
 
 }


?>
