<?php
/*
Author: Inez Korczyński (inez@wikia.com)

Needed changes in other files:

SpecialUndelete.php 236 after $log->addEntry( 'restore', $this->title, $reason );
wfRunHooks('UndeleteComplete', array(&$this->title, &$wgUser, $reason));

Image.php 1790 after $dbw->query( "UPDATE $site_stats SET ss_images=ss_images-1", $fname );
wfRunHooks('FileDeleteComplete', array(&$this, &$wgUser, $reason));
*/
if (!defined('MEDIAWIKI'))
{
	die();
}

define( 'NEWARTICLE', 1 );
define( 'EDIT', 2 );
define( 'MOVE', 3 );
define( 'DELETE', 4 );
define( 'FILEDELETE', 5 );
define( 'UNDELETE', 6 );

function wfNotifier_Notify( $o )
{
	global $wgDBname;

	wfNotifier_Send("{$wgDBname} {$o['id']}");
	if( $o['type'] == MOVE )
	{
		wfNotifier_Send("{$wgDBname} {$o['oldid']}");
	}

	$memc = & wfGetCache(CACHE_MEMCACHED);
	$recentChanges = $memc->get('recentChanges');

	if( ! is_array($recentChanges) )
	{
		$recentChanges = array();
	}

	while( count( $recentChanges ) >= 20 )
	{
		array_pop( $recentChanges );
	}

	$o['unixtimestamp'] = time();
	$o['dbname'] = $wgDBname;
	$recentChanges = array_merge(array($o), $recentChanges);
	$memc->set('recentChanges', $recentChanges, 60 * 60);
}

function wfNotifier_Send( $mess )
{
	global $wgSearchDebug, $wgSearchURL, $wgSearchPort;

	if ( ! $wgSearchURL || ! $wgSearchPort )
	{
		return;
	}

	if ( $wgSearchDebug == true )
	{
		$start = wfTime();
	}

	$fp = @fsockopen(gethostbyname($wgSearchURL), $wgSearchPort, $errno, $errstr, 3);
    if( $fp )
    {
	    @fwrite($fp, $mess);
	    @fclose($fp);
	}

	if ( $wgSearchDebug == true )
	{
		$delta = wfTime() - $start;
		$debug = sprintf( "wfNotifier_Send for %s took %0.2fms\n", $mess, $delta * 1000.0 );
		wfDebug( $debug );
	}
}

function ArticleSaveCompleteHandler( &$article, &$user, &$text, &$summary, &$minoredit, &$watchthis, &$sectionanchor, &$flags )
{
	$o = array();

	if( $article->mRevision == null ) {
		$o['type'] = NEWARTICLE;
	} else {
		$o['type'] = EDIT;
	}

	$o['title'] = $article->getTitle()->getPrefixedText();
	$o['url'] = $article->getTitle()->getFullURL();
	$o['user'] = $user->getName();
	$o['comment'] = $summary;
	$o['timestamp'] = $article->getTimestamp();
	$o['id'] = $article->getId();

	wfNotifier_Notify($o);

	return true;
}
$wgHooks['ArticleSaveComplete'][] = 'ArticleSaveCompleteHandler';

function TitleMoveCompleteHandler(&$title, &$newtitle, &$user, $oldid, $newid)
{
	global $wgRequest;

	$o = array();
	$o['type'] = MOVE;

	$o['title'] = $newtitle->getPrefixedText();
	$o['oldtitle'] = $title->getPrefixedText();
	$o['url'] = $newtitle->getFullURL();
	$o['oldurl'] = $title->getFullURL();
	$o['user'] = $user->getName();
	$o['comment'] = $wgRequest->getText('wpReason');

	$db =& wfGetDB( DB_MASTER );
	$pageRevision = Revision::loadFromPageId( $db, $newid );

	$o['timestamp'] = $pageRevision->getTimestamp();
	$o['id'] = $newid;
	$o['oldid'] = $oldid;

	wfNotifier_Notify($o);

	return true;
}
$wgHooks['TitleMoveComplete'][] = 'TitleMoveCompleteHandler';

function ArticleDeleteCompleteHandler(&$article, &$user, $reason)
{
	$o = array();
	$o['type'] = DELETE;
	$o['title'] = $article->getTitle()->getPrefixedText();
	$o['url'] = $article->getTitle()->getFullURL();
	$o['user'] = $user->getName();
	$o['comment'] = $reason;
	$o['timestamp'] = $article->getTimestamp();
	$o['id'] = $article->mRevision->getPage();

	wfNotifier_Notify($o);

	return true;
}
$wgHooks['ArticleDeleteComplete'][] = 'ArticleDeleteCompleteHandler';

function FileDeleteCompleteHandler(&$image, &$user, $reason)
{
	$title = $image->getTitle();

	$o = array();
	$o['type'] = FILEDELETE;
	$o['title'] = $title->getPrefixedText();
	$o['url'] = $title->getFullURL();
	$o['user'] = $user->getName();
	$o['comment'] = $reason;

	$db =& wfGetDB( DB_MASTER );
	$pageRevision = Revision::loadFromTitle( $db, $title );

	$o['timestamp'] = $pageRevision->getTimestamp();
	$o['id'] = $pageRevision->getId();

	wfNotifier_Notify($o);

	return true;
}
$wgHooks['FileDeleteComplete'][] = 'FileDeleteCompleteHandler';

function UndeleteCompleteHandler(&$title, &$user, $reason)
{
	$o = array();
	$o['type'] = UNDELETE;
	$o['title'] = $title->getPrefixedText();
	$o['url'] = $title->getFullURL();
	$o['user'] = $user->getName();
	$o['comment'] = $reason;

	$db =& wfGetDB( DB_MASTER );
	$pageRevision = Revision::loadFromTitle( $db, $title );

	$o['timestamp'] = $pageRevision->getTimestamp();
	$o['id'] = $pageRevision->getId();

	wfNotifier_Notify($o);

	return true;
}
$wgHooks['UndeleteComplete'][] = 'UndeleteCompleteHandler';
?>
