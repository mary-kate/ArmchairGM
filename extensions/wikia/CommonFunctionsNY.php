<?php
$wgExtensionFunctions[] = 'wfCommonReadLang';
//read in localisation messages
function wfCommonReadLang(){
	global $wgMessageCache, $IP, $wgVoteDirectory;
	require_once ( "CommonNY.i18n.php" );
	foreach( efWikiaCommon() as $lang => $messages ){
		$wgMessageCache->addMessages( $messages, $lang );
	}
}

$max_link_text_length = 20;
function cut_link_text($matches){
	global $max_link_text_length;

	$tag_open = $matches[1];
	$link_text = $matches[2];
	$tag_close = $matches[3];

	$image = preg_match("/<img src=/i", $link_text );
	$is_url = ereg("^(http|https|ftp)://(www\.)?.*$", $link_text );

	//$max_link_text_length = 50;
	if( $is_url && !$image && strlen($link_text ) > $max_link_text_length){
		$start = substr($link_text,0,($max_link_text_length/2)-3 );
		$end = substr($link_text,strlen($link_text ) - ($max_link_text_length/2) +3,($max_link_text_length/2)-3);
		$link_text = trim($start) . "..." . trim($end);
	}
	return $tag_open . $link_text . $tag_close;
}

function get_dates_from_elapsed_days($number_of_days){
	$dates[date("F j, Y", time() )] = 1; //gets today's date string
	for($x=1;$x<=$number_of_days;$x++){
		$time_ago = time() - (60 * 60 * 24 * $x);
		$date_string = date("F j, Y", $time_ago);
		$dates[$date_string] = 1;
	}
	return $dates;
}

function date_diff($dt1, $dt2) {
	
	$date1 = $dt1; //(strtotime($dt1) != -1) ? strtotime($dt1) : $dt1;
	$date2 = $dt2; //(strtotime($dt2) != -1) ? strtotime($dt2) : $dt2;
	
	$dtDiff = $date1 - $date2;

	$totalDays = intval($dtDiff/(24*60*60));
	$totalSecs = $dtDiff-($totalDays*24*60*60);
	$dif['w'] = intval($totalDays/7);
	$dif['d'] = $totalDays;
	$dif['h'] = $h = intval($totalSecs/(60*60));
	$dif['m'] = $m = intval(($totalSecs-($h*60*60))/60);
	$dif['s'] = $totalSecs-($h*60*60)-($m*60);
	
	return $dif;
}

function get_time_offset($time,$timeabrv,$timename){
	if($time[$timeabrv]>0){
		$timeStr = wfMsgExt( "time_{$timename}", "parsemag", $time[$timeabrv] );
		//$timeStr = $time[$timeabrv] . " " . $timename;
		//if($time[$timeabrv]>1)$timeStr .= "s";
	}
	if($timeStr)$timeStr .= " ";
	return $timeStr;
}

function get_time_ago($time){
	$timeArray =  date_diff(time(),$time  );
	$timeStr = "";
	$timeStrD = get_time_offset($timeArray,"d","days");
	$timeStrH = get_time_offset($timeArray,"h","hours");
	$timeStrM = get_time_offset($timeArray,"m","minutes");
	$timeStrS = get_time_offset($timeArray,"s","seconds");
	$timeStr = $timeStrD;
	if($timeStr<2){
		$timeStr.=$timeStrH;
		$timeStr.=$timeStrM;
		if(!$timeStr)$timeStr.=$timeStrS;
	}
	if(!$timeStr)$timeStr = wfMsgExt( "time_seconds", "parsemag", 1);
	return $timeStr;
}

/**
 * Author: Inez Korczy�ski
 */
function GetLinksArrayFromMessage($messagename) { // feel free to suggest better name for this function
	global $parserMemc, $wgEnableSidebarCache;
	global $wgLang, $wgContLang;

	wfProfileIn("GetLinksArrayFromMessage");
	$key = wfMemcKey($messagename);

	$cacheSidebar = $wgEnableSidebarCache &&
		($wgLang->getCode() == $wgContLang->getCode());

	if ($cacheSidebar) {
		$cachedsidebar = $parserMemc->get( $key );
		if ($cachedsidebar!="") {
			wfProfileOut("GetLinksArrayFromMessage");
			return $cachedsidebar;
		}
	}

	$bar = array();
	$lines = explode( "\n", wfMsgForContent( $messagename ) );
	foreach ($lines as $line) {
		if (strpos($line, '*') !== 0)
			continue;
		if (strpos($line, '**') !== 0) {
			$line = trim($line, '* ');
			$heading = $line;
		} else {
			if (strpos($line, '|') !== false) { // sanity check
				$line = explode( '|' , trim($line, '* '), 2 );
				$link = wfMsgForContent( $line[0] );
				if ($link == '-')
					continue;
				if (wfEmptyMsg($line[1], $text = wfMsg($line[1])))
					$text = $line[1];
				if (wfEmptyMsg($line[0], $link))
					$link = $line[0];
					if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
					$href = $link;
				} else {
					$title = Title::newFromText( $link );
					if ( $title ) {
						$title = $title->fixSpecialName();
						$href = $title->getLocalURL();
					} else {
						$href = 'INVALID-TITLE';
					}
				}
					$bar[$heading][] = array(
					'text' => $text,
					'href' => $href,
					'id' => 'n-' . strtr($line[1], ' ', '-'),
					'active' => false
				);
			} else { continue; }
		}
	}
	if ($cacheSidebar)
		$parserMemc->set( $key, $bar, 86400 );
	wfProfileOut("GetLinksArrayFromMessage");
	return $bar;
}

function shorten_text( $text, $chars=25 ) {
	if( strlen( $text ) <= $chars )
		return $text;

	$text = $text . " ";
	$text = substr( $text, 0, $chars );
	if( strrpos( $text, ' ') || strrpos( $text, '/' ) ){
	    $text = substr( $text, 0, max( strrpos( $text, ' '), strrpos( $text, '/' ) ) );
	}
	
	$text = $text . "...";

	return $text;
}

/**
 * create table name for shared database
 *
 * @access public
 * @author eloy@wikia
 *
 * @param $table string - table name
 *
 * @return string - table name with additional shared database
 */

function wfSharedTable( $table ){
    global $wgSharedDB, $wgExternalSharedDB;

	if (!empty( $wgExternalSharedDB )) {
		return "`$wgExternalSharedDB`.`$table`";
	} elseif (!empty( $wgSharedDB )) {
		return "`$wgSharedDB`.`$table`";
	} else
		return "`$table`";

}
?>