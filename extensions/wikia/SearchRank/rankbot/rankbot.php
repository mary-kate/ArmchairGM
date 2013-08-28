#!/usr/bin/php
<?php
/**
 * Search Rank tracker bot
 *
 * @package RankBot
 *
 * @author http://ardoino.altervista.org/blog/index.php?c=WebMaster - ideas only
 * @author Tomasz Klim <tomek@wikia.com> - ideas, implementation
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
create table rank_groups (
    grp_id int not null auto_increment primary key,
    grp_url varchar(255) not null,
    grp_keywords varchar(255) not null,
    grp_tm timestamp default now(),
    grp_active int not null default 1
);
create table rank_results (
    res_id int not null auto_increment primary key,
    res_id_grp int not null,
    res_engine int not null,
    res_position int not null,
    res_tm timestamp default now()
);
 *
 */


$dbhost  = 'buffy.sjc.wikia-inc.com';
$dbuser  = 'wikicities';
$dbpass  = 'w1k14u';
$dbname  = 'fp002';
$logfile = '/tmp/sql_error_rankbot.log';

$user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322)';

set_time_limit(0);
ini_set('include_path', '.');
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('log_errors', 0);
ini_set('error_log', '');
ini_set('display_errors', 1);

define('MEDIAWIKI', 1);
require_once('../../WikiAltDbm/WikiAltDbm.php');
require_once('../../WikiCurl/WikiCurl.php');

// end of configuration


define('RANK_GOOGLE', 0);
define('RANK_YAHOO', 1);
define('RANK_MSN', 2);
define('RANK_ALTAVISTA', 3);


// TODO: consider replacing this with database layer similar to the one in Wikia Widgets
$dbf = new WikiAltDbm($dbhost, $dbuser, $dbpass, $dbname, $logfile);
if (!$dbf->connect()) {
    echo "Cannot connect to $dbname [$dbuser@$dbhost]: ".$dbf->error()."\n";
    die();
}

while (1) {
    $sql = "select grp_id as id, grp_url as ur, grp_keywords as kw from rank_groups where grp_active=1 order by grp_id";
    $res = $dbf->query($sql);

    while ($row = $dbf->fetchObject($res)) {

	$pos = checkGoogle( $row->id, $row->ur, $row->kw );
        $sql="insert into rank_results (res_id_grp,res_engine,res_position) values ($row->id, ".RANK_GOOGLE.", $pos)";
        $dbf->query($sql);

	$pos = checkYahoo( $row->id, $row->ur, $row->kw );
        $sql="insert into rank_results (res_id_grp,res_engine,res_position) values ($row->id, ".RANK_YAHOO.", $pos)";
        $dbf->query($sql);

	$pos = checkMsn( $row->id, $row->ur, $row->kw );
        $sql="insert into rank_results (res_id_grp,res_engine,res_position) values ($row->id, ".RANK_MSN.", $pos)";
        $dbf->query($sql);

	$pos = checkAltavista( $row->id, $row->ur, $row->kw );
        $sql="insert into rank_results (res_id_grp,res_engine,res_position) values ($row->id, ".RANK_ALTAVISTA.", $pos)";
        $dbf->query($sql);
    }
    $dbf->freeResult($res);

    // TODO: place a trigger here, that will pause the bot for a specified amount of time, and start again
    break;
}

echo "Search Rank tracker bot has finished its operations.\n";
unset($dbf);
die();


function checkGoogle( $id, $url, $keywords ) {
    $found = 0;
    $count = 0;
    $offset = 0;

    $handler = new WikiCurl();
    $handler->setTimeout = 90;
    $handler->setReferer = 'http://www.google.com/';
    $handler->setCookies = 'cookies';
    $handler->setAgent   = $user_agent;

    while (1) {
        $ret = $handler->get('http://www.google.com/search',  array('q'     => $keywords,
								    'num'   => '100',
								    'start' => $offset,
								    'hl'    => 'en',
								    'lr'    => '',
								    'sa'    => 'N'));

	// first version, stopped working after Google search results site has changed
	// preg_match_all('!<a class=l href="(.*?)">!s', $ret, $links, PREG_SET_ORDER);

	// second version, working as of 2007-02-19
	preg_match_all('!<h2 class=r><a href="(.*?)" class=l!s', $ret, $links, PREG_SET_ORDER);

	foreach ($links as $link) {
	    $count++;

            // filter out trailing slashes
	    $filter = (substr($link[1],-1,1) == '/' ? substr($link[1],0,-1) : $link[1]);

	    if ($filter == "http://$url") {
		$found = $count;
		echo "g $count $url $filter\n";
	    }
	}

	$offset += 100;
	if ( $found > 0 || $offset >= 1000 ) {  break;  }  // we've already found matching URL, or ran out of Google results
    }
    unset($handler);
    return $found;
}


function checkYahoo( $id, $url, $keywords ) {
    $found = 0;
    $count = 0;
    $offset = 1;

    $handler = new WikiCurl();
    $handler->setTimeout = 90;
    $handler->setReferer = 'http://www.yahoo.com/';
    $handler->setCookies = 'cookies';
    $handler->setAgent   = $user_agent;

    while (1) {
        $ret = $handler->get('http://search.yahoo.com/search', array('p'      => $keywords,
								     'sm'     => 'Yahoo! Search',
								     'fr'     => 'FP-tab-web-t',
								     'b'      => $offset,
								     'toggle' => '1',
								     'cop'    => 'mss',
								     'ei'     => 'UTF-8'));

	// in Yahoo mode, we're not scanning real links, but the green, human-readable addresses
	$ret = str_replace('<wbr>','',$ret);
	$ret = str_replace('</b>','',$ret);
	$ret = str_replace('<b>','',$ret);

	preg_match_all('!<em class=yschurl>(.*?)</em>!s', $ret, $links, PREG_SET_ORDER);
	foreach ($links as $link) {
	    $count++;
	    if ($link[1] == $url) {
		$found = $count;
		echo "y $count $url ".$link[1]."\n";
	    }
	}

	$offset += 10;
	if ( $found > 0 || $offset >= 1000 ) {  break;  }  // we've already found matching URL, or ran out of Yahoo results
    }
    unset($handler);
    return $found;
}


function checkMsn( $id, $url, $keywords ) {
    $found = 0;
    $count = 0;
    $offset = 1;

    $handler = new WikiCurl();
    $handler->setTimeout = 90;
    $handler->setReferer = 'http://www.msn.com/';
    $handler->setCookies = 'cookies';
    $handler->setAgent   = $user_agent;

    while (1) {
        $ret = $handler->get('http://search.msn.com/results.aspx', array('q'     => $keywords,
									 'first' => $offset,
								         'FORM'  => 'PERE'));

	preg_match_all('!<h3><a href="(.*?)" gping!s', $ret, $links, PREG_SET_ORDER);
	foreach ($links as $link) {
	    $count++;

            // filter out trailing slashes
	    $filter = (substr($link[1],-1,1) == '/' ? substr($link[1],0,-1) : $link[1]);

	    if ($filter == "http://$url") {
		$found = $count;
		echo "m $count $url $filter\n";
	    }
	}

	$offset += 10;
	if ( $found > 0 || $offset >= 820 ) {  break;  }  // we've already found matching URL, or ran out of MSN results
    }
    unset($handler);
    return $found;
}


function checkAltavista( $id, $url, $keywords ) {
    $found = 0;
    $count = 0;
    $offset = 0;

    $handler = new WikiCurl();
    $handler->setTimeout = 90;
    $handler->setReferer = 'http://www.altavista.com/';
    $handler->setCookies = 'cookies';
    $handler->setAgent   = $user_agent;

    while (1) {
        $ret = $handler->get('http://www.altavista.com/web/results',  array('q'    => $keywords,
									    'kgs'  => '0',
									    'kls'  => '0',
									    'stq'  => $offset,
								    	    'itag' => 'ody'));

	preg_match_all('!<span class=ngrn>(.*?) </span>!s', $ret, $links, PREG_SET_ORDER);
	foreach ($links as $link) {
	    $count++;
	    if ($link[1] == $url) {
		$found = $count;
		echo "a $count $url ".$link[1]."\n";
	    }
	}

	$offset += 10;
	if ( $found > 0 || $offset >= 1000 ) {  break;  }  // we've already found matching URL, or ran out of AltaVista results
    }
    unset($handler);
    return $found;
}


?>
