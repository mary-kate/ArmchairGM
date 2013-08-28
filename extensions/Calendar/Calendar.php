<?php

/* 

Simple wiki calendar 
Copyright (C) 2005 Christof Damian

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

require_once("WikiCalendarClass.php");

$wgExtensionFunctions[] = "wfCalendarExtension";
$wgExtensionCredits['parserhook'][] = array
  (
   'name' => 'wikicalendar',
   'author' => 'Christof Damian',
   'url' => 'http://meta.wikimedia.org/wiki/User:Cdamian/calendar'
   );

function wfCalendarExtension() {
    global $wgParser;
    $wgParser->setHook( "calendar", "renderCalendar" );
}

/**
 * Method to clear MediaWiki Cache on different versions
 * 
 * @version 1.1
 * @author Daniel Simon
 */
function clearCache() {
  global $wgVersion;
  if (version_compare($wgVersion,'1.5','>=')) {
    global $wgParser;
    $wgParser->disableCache();
  } elseif (version_compare($wgVersion,'1.4','>=')) {
    global $wgTitle;
    $dbw =& wfGetDB( DB_MASTER );
    $dbw->update( 'cur', array( 'cur_touched' => $dbw->timestamp( time() + 120 ) ), 
                  array( 
                        'cur_namespace' => $wgTitle->getNamespace(), 
                        'cur_title' => $wgTitle->getDBkey() 
                        ), 'CalendarExtension' 
                  );
  } elseif (version_compare($wgVersion,'1.3','>=')) {
    $wgOut->enableClientCache(false);
  }
}

function renderCalendar( $paramstring )
{
	
  global $wgTitle,$wgParser,$wgUser,$wgOut,$wgVersion;

  //clearCache();
  
  $p = array(
             "view"  => "year",
             "day"   => 0,
             "month" => 0,
             "year"  => 0,
             "days"  => 7,
             "weekstart" => 1,
             "formattitle" => "%j.%n.%Y %l"
             );
  
  preg_match_all('/([\w]+)\s*=\s*(?:"([^"]+)"|([^"\s]+))/', $paramstring, $matches);
  for ($i=0; $i< count($matches[0]); $i++) {
    $p[$matches[1][$i]] = $matches[2][$i].$matches[3][$i];
  }
  
  if (!isset($p['name'])) { 
    $p['name'] = 'calendar'; 
  };
  if (!isset($p['format'])) { 
    $p['format'] = $p['name'].'_%year_%month_%day'; 
  };

  if (isset($p['date'])) {
    $time = strtotime($p['date']);
    $p['day']   = date('d',$time);
    $p['month'] = date('m',$time);
    $p['year']  = date('Y',$time);
  }

  $i = 1;
  $merge = array();
  while (isset($p["merge$i"])) {
    $merge[$i] = $p["merge$i"];
    $i++;
  }
  
  $cal = new WikiCalendarClass($p["year"],$p["month"],$p["day"]);
  $cal->format = $p['format'];
  $cal->formattitle = $p['formattitle'];
  $cal->name = $p['name'];
  $cal->weekstart = $p['weekstart']; 
  $cal->merge = $merge;

  switch ($p["view"]) {
  case "week": 
    $calstr = $cal->displayWeek(); 
    break;
  case "month": 
    $calstr = $cal->displayMonth(); 
    break;
   case "today":
    $calstr = $cal->displayToday(); 
    break;
  case "threemonths":
    $calstr = $cal->displayThreeMonths(); 
    break;
  case "days":
    $calstr = $cal->displayDays($p["days"]);
    break;
  default: 
    $calstr = $cal->displayYear(); 
  }
  $p = new Parser();
  $o = $p->parse($calstr,$wgTitle,$wgOut->parserOptions(),true );

  return $o->getText();
  
}

?>
