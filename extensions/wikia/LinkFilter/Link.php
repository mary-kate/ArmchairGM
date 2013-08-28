<?php
define( 'NS_LINK', 700 );
define( 'LINK_APPROVED_STATUS', 1 );
define( 'LINK_OPEN_STATUS', 0 );
define( 'LINK_REJECTED_STATUS', 2 );

//default setup for displaying sections
$wgLinkPageDisplay['leftcolumn'] = true;
$wgLinkPageDisplay['rightcolumn'] = true;
$wgLinkPageDisplay['author'] = true;
$wgLinkPageDisplay['author_articles'] = true;
$wgLinkPageDisplay['recent_editors'] = true;
$wgLinkPageDisplay['recent_voters'] = true;
$wgLinkPageDisplay['left_ad'] = true;
$wgLinkPageDisplay['popular_articles'] = true;
$wgLinkPageDisplay['in_the_news'] = true;
$wgLinkPageDisplay['comments_of_day'] = true;
$wgLinkPageDisplay['games'] = true;
$wgLinkPageDisplay['new_links'] = true;

$wgGroupPermissions['linkadmin']["read"]  = true;
$wgGroupPermissions['no_link_submit']["read"]  = true;

$wgLinkFilterAdminPointsRequirement = 75000;
$wgHooks['ArticleFromTitle'][] = 'wfLinkFromTitle';

//ArticleFromTitle
//Calls VideoPage instead of standard article
function wfLinkFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgSupressPageTitle, $wgSupressSubTitle, $wgStyleVersion,$wgMessageCache;
	
	if ( NS_LINK == $title->getNamespace()  ) {
		
		$wgOut->enableClientCache(false);
		
		$wgSupressPageTitle = true;
		$wgSupressSubTitle = true;
		require_once( "$IP/extensions/wikia/LinkFilter/LinkClass.php" );
		require_once( "$IP/extensions/wikia/LinkFilter/LinkPage.php" );
		require_once ( "$IP/extensions/wikia/LinkFilter/LinkFilter.i18n.php" );
		foreach( efWikiaLinkFilter() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/LinkFilter/LinkFilter.css?{$wgStyleVersion}\"/>\n");
		
		$article = new LinkPage(&$title);
	}

	return true;
}
?>