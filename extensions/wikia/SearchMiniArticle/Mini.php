<?php
//GLOBAL VIDEO NAMESPACE REFERENCE
define( 'NS_MINI', 800 );

$wgHooks['ArticleFromTitle'][] = 'wfMiniFromTitle';

//ArticleFromTitle
//Calls MiniPage instead of standard article
function wfMiniFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgTitle, $wgMessageCache, $wgStyleVersion;
	
	if ( NS_MINI == $title->getNamespace()  ) {
		/*
		require_once ( "$IP/extensions/wikia/SearchMiniArticle/Mini.i18n.php" );
		foreach( efWikiaBlog() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		*/
		
		require_once( "$IP/extensions/wikia/SearchMiniArticle/MiniPage.php" );
		
		$article = new MiniPage($wgTitle);
		
	}

	return true;
}

?>