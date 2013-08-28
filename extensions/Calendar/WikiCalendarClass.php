<?php

/* 

Simple wiki calendar class
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

require_once("CalendarClass.php");

class WikiCalendarClass extends CalendarClass { 
  
  function _format($day,$month,$year,$r) {
    $r = str_replace('%day',$day,$r);
    $r = str_replace('%month',$month,$r);
    $r = str_replace('%year',$year,$r);
    $r = str_replace('%name',$this->name,$r);

    $time = mktime(0,0,0,$month,$day,$year);
    while (preg_match('/%([A-Za-z])/',$r,$matches)>0) {
      $r = str_replace('%'.$matches[1],date($matches[1],$time),$r);
    }
    return $r;
  }

  function formatdate($day,$month,$year) {
    return $this->_format($day,$month,$year,$this->format);
  }

  function formattitle($day,$month,$year) {
    return $this->_format($day,$month,$year,$this->formattitle);
  }

  function displayDay($day, $month, $year) {
    $text = $this->formatdate($day,$month,$year);
    $title = Title::newFromText($text);
    if ($title and $title->getArticleID()==0) {
      return '[['.$text.'|'.$day.']]';
    } else {
      return "'''[[$text|$day]]'''";
    }
  }

  function displayWeekday($day,$month,$year,$dow) {
    $text = $this->formatdate($day,$month,$year);
    $heading = $this->formattitle($day,$month,$year);

    $r = "\n<b><span style=\"font-size:120%\">[[$text|$heading]]</span></b>\n\n";
    $c = 0;
    
    $title = Title::newFromText($text);
    if ($title and $title->getArticleID()!=0) {
      $r .= "{{:$text}}\n";
      $c++;
    }

    foreach ($this->merge as $i) {
      $merge = $this->_format($day,$month,$year,$i);
      $title = Title::newFromText($merge);
      if ($title and $title->getArticleID()!=0) {
        $r .= "<br/>{{:$merge}}";
        $c++;
      }
    }
    
    if ($c==0) {
      $r .= "<small>No entries for this date. Please [[$text|feel free to add entries]].</small>"; 
    }
    return $r;
  }
}

?>
