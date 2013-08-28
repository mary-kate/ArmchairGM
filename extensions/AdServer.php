<?php

  /* Wikia ad serving and ad rotation support.
   * Copyright 2006 Wikia, Inc.  All rights reserved.
   * Use is subject to license terms.
   */

define( 'ADSERVER_ZONE_DEFAULT', 1); /* default simple Google AdWords zone */
define( 'ADSERVER_ZONE_ANALYTICS', 15); //google analytics zone

define('ADSERVER_ZONE_SIGFIGS_LIMIT', 5);
define('ADSERVER_ZONE_LIMIT', pow(10,ADSERVER_ZONE_SIGFIGS_LIMIT)); # no more than 100,000 ad campaign zones
define('ADSERVER_DOMAIN_MAXLEN', 500); # subdomain community name must be < 255 chars

define('ADSERVER_ZONE_EXPR', 'zone');
define('ADSERVER_POS_EXPR', 'pos');

define('ADSERVER_LANG_DEFAULT', 'en');

define('ADSERVER_POS_TOPLEFT', 'tl');
define('ADSERVER_POS_TOP', 't');
define('ADSERVER_POS_TOPRIGHT', 'tr');
define('ADSERVER_POS_LEFT', 'l');
define('ADSERVER_POS_RIGHT', 'r');
define('ADSERVER_POS_BOTLEFT', 'bl');
define('ADSERVER_POS_BOT', 'b');
define('ADSERVER_POS_BOTRIGHT', 'br');
define('ADSERVER_POS_BOTBOT', 'bb');
define('ADSERVER_POS_BOTBOT2', 'bb2');
define('ADSERVER_POS_BOTBOT3', 'bb3');
define('ADSERVER_POS_BOTBOT4', 'bb4');
define('ADSERVER_POS_BOTBOT5', 'bb5');

define('ADSERVER_POS_JS_TOP1', 'js_top1');
define('ADSERVER_POS_JS_TOP2', 'js_top2');
define('ADSERVER_POS_JS_TOP3', 'js_top3');
define('ADSERVER_POS_JS_BOT1', 'js_bot1');
define('ADSERVER_POS_JS_BOT2', 'js_bot2');
define('ADSERVER_POS_JS_BOT3', 'js_bot3');

function isValidAdPos($pos)
{
    return (
         $pos == ADSERVER_POS_TOP || $pos == ADSERVER_POS_TOPLEFT || $pos == ADSERVER_POS_TOPRIGHT ||
         $pos == ADSERVER_POS_LEFT || $pos == ADSERVER_POS_RIGHT ||
         $pos == ADSERVER_POS_BOTLEFT || $pos == ADSERVER_POS_BOT || $pos == ADSERVER_POS_BOTRIGHT ||
	 $pos == ADSERVER_POS_BOTBOT ||
	 $pos == ADSERVER_POS_JS_BOT1 || $pos == ADSERVER_POS_JS_BOT2 || $pos == ADSERVER_POS_JS_BOT3 ||
	 $pos == ADSERVER_POS_JS_TOP1 || $pos == ADSERVER_POS_JS_TOP2 || $pos == ADSERVER_POS_JS_TOP3);
}


function getAdServerHTMLForZone($adZone, $adKeywords, $usePageRedirect)
{
  global $wgServer, $wgGoogleAnalyticsID, $wgAdServerPath, $wgUser;
  if ($adZone && $adZone > 0 && @include( $wgAdServerPath . 'phpadsnew.inc.php')) {
    if (!isset($phpAds_context)) $phpAds_context = array();
    $userType = ( $wgUser->isLoggedIn() ) ? 'USER' : 'VISITOR';
    $phpAds_raw = view_raw ("zone:$adZone", 0, '', $source, '0', $phpAds_context, true, array($userType));

    $adHTML = $phpAds_raw['html'];

    $adHTML = str_replace("ADSERVER_KW_PLACEHOLDER", $adKeywords, $adHTML);
    $adHTML = str_replace("ADSERVER_URL_PLACEHOLDER", "$wgServer/wiki/" . wfMsgForContent('mainpage'), $adHTML);

    if ( $wgGoogleAnalyticsID )
      $adHTML = str_replace("ADSERVER_ANALYTICSID_PLACEHOLDER", $wgGoogleAnalyticsID, $adHTML);

    if ( ! $usePageRedirect )
      $adHTML = preg_replace("/google_page_url(.*)\;/i", "", $adHTML);

    return $adHTML;
  }

  return '';
}

function getAllAds($usePageRedirect, &$ads)
{
  global $wgDBname;
  global $wgSharedDB;
  global $wgServer;

  $db =& wfGetDB(DB_SLAVE);

  $sharedDB = $wgSharedDB;
  if ( !isset($wgSharedDB) ) $sharedDB = 'wikicities';
  $res = $db->query("SELECT city_id, city_dbname FROM ".
                    "`$sharedDB`.`city_list`".
                    " WHERE city_dbname='$wgDBname';");

  $cityID = 0;
  if (($o = $db->fetchObject($res))) {
    $cityID = $o->city_id;
  }
  $sql =  "SELECT domain, ad_zone, ad_pos, ad_keywords FROM ".
           " `$sharedDB`.`city_ads`".
           " WHERE city_id=$cityID";
  $res = $db->query($sql);

  $adZone ='';
  $adKeywords = '';
  $adDomain = '';

  $adList = array();
  while ($o = $db->fetchObject($res)) {
    $adZone = $o->ad_zone;
    $adPosition = $o->ad_pos;
    $adKeywords = $o->ad_keywords;
    $adDomain = $o->domain;
    $adHTML = getAdServerHTMLForZone($adZone, $adKeywords, $usePageRedirect);
    if ( $adHTML && isValidAdPos($adPosition) )
        $adList[$adPosition] = $adHTML;
  }

  if ( count($adList) == 0 ) {
    $adList[ADSERVER_POS_TOPRIGHT] = getAdServerHTMLForZone(ADSERVER_ZONE_DEFAULT, '', false);
    $adList[ADSERVER_POS_JS_BOT1] = getAdServerHTMLForZone(ADSERVER_ZONE_ANALYTICS, '' ,false);
  }

  return $adList;
}


##
## old getAds code, used to get ads on an individual basis and db lookup from within the MonoBook skin
## the new getFullAds() code above gets the ads in aggregate, in one DB call
##

function getAds($titleObj = '', $adPosition=ADSERVER_POS_RIGHT)
{
  global $wgDBname;
  global $wgSharedDB;
  global $wgServer;
  global $wgAdServerPath;

  $db =& wfGetDB(DB_SLAVE);

  $res = $db->query("SELECT city_id, city_dbname FROM ".
                    "`$wgSharedDB`.`city_list`".
                    " WHERE city_dbname='$wgDBname';");

  $cityID = 0;
  if (($o = $db->fetchObject($res))) {
    $cityID = $o->city_id;
  }
  $sql =  "SELECT domain, ad_zone, ad_keywords FROM ".
           " `$wgSharedDB`.`city_ads`".
           " WHERE city_id=$cityID AND ad_pos='$adPosition';";
  $res = $db->query($sql);


  $adZone ='';
  $adKeywords = '';
  $adDomain = '';
  if ($o = $db->fetchObject($res)) {
    $adZone = $o->ad_zone;
    $adKeywords = $o->ad_keywords;
    $adDomain = $o->domain;
  }
  if ( ! $adZone && $adPosition == ADSERVER_POS_TOPRIGHT ) $adZone = ADSERVER_ZONE_DEFAULT;

  #echo "<!-- AdServer.php getting ads for $wgDBname, $adDomain, $adKeywords, $cityID, with defined phpadsnew zone $adZone-->\n";

  if ($adZone && @include( $wgAdServerPath . 'phpadsnew.inc.php')) {
    if (!isset($phpAds_context)) $phpAds_context = array();

    $phpAds_raw = view_raw ("zone:$adZone", 0, '', '', '0', $phpAds_context);

    #echo "phpAds_raw: $phpAds_raw\n";
    #foreach ($phpAdsRaw as $key => $value) {
    #  echo "key $key is $value\n";
    #}

    $adHTML = $phpAds_raw['html'];

    $adHTML = str_replace("ADSERVER_KW_PLACEHOLDER", $adKeywords, $adHTML);
    $adHTML = str_replace("ADSERVER_URL_PLACEHOLDER", "$wgServer/wiki/" . wfMsgForContent('mainpage'), $adHTML);

    echo $adHTML;
  }
}
?>
