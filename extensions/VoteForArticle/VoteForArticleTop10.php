<?php
// inez@wikia.com

if ( ! defined( 'MEDIAWIKI' ) ) {
	die();
}

/* WIKIA */
$wgSpecialPages['Top10'] = array('SpecialPage','Top10');
/* WIKIA */
	
/* MEDIA WIKI */
#require_once($IP . '/includes/SpecialPage.php');
#SpecialPage::AddPage(new SpecialPage('Top10'));
/* MEDIA WIKI */

function wfSpecialTop10() {
	global $wgRequest, $wgUser, $wgOut, $wgTitle, $wgTopVoted, $wgTopVotedResultCount;

	$wgOut->setPagetitle( "Top10" );

	if( $wgRequest->getVal("wgTopVoted") == "true" ) {
		$wgTopVoted = true;
	} else if( $wgRequest->getVal("wgTopVoted") == "false" ) {
		$wgTopVoted = false;
	}

	if ( $wgTopVoted ) { // show most voted articles
		$query = "SELECT p.page_title, SUM(v.vote) as votessum FROM votecounter as v, page as p WHERE p.page_id = v.article_id GROUP BY v.article_id ORDER BY votessum DESC";
	} else { // show last voted articles
		$query = "SELECT p.page_title, SUM(v.vote) as votessum FROM votecounter as v, page as p WHERE p.page_id = v.article_id GROUP BY v.article_id ORDER BY max(v.time) DESC";
	}

	$dbr = & wfGetDB( DB_SLAVE);
	$query = $dbr->limitResult( $query, $wgTopVotedResultCount, 0 );
	$res = $dbr->query($query);
	$links = '';

	while( $row = $dbr->fetchObject($res) ) {
		$title = Title::makeTitleSafe( NS_MAIN, $row->page_title );
		$links .= '<a href="' . $title->getFullUrl(). '">' . $row->page_title . ': ' . $row->votessum . ' vote(s)</a><br/>';
	}
	$dbr->freeResult( $res );
	$wgOut->addHtml( $links );
}
?>
