<?php

$wgAjaxExportList [] = 'wfGetArticleJSON';
function wfGetArticleJSON($article_title){
	global $IP, $wgOut;
	
	global $wgArticlePath, $wgServer;
	$wgArticlePath = $wgServer . $wgArticlePath;
	
	//I'm using an include taken from PEAR SERVICES_JSON
	//Change your instance here
	require_once( "$IP/extensions/wikia/Pean/JSON.php" );
	
	//construct mediawiki objects based on page title supplied
	$title = Title::newFromText($article_title);
	$article = new Article($title);
	
	if(! $article->exists() ){
		return "noarticle";
	}
	
	$article_content = $article->getContent();
	$article_html = $wgOut->parse( $article_content, false );
	
	$article_a = array();
	$article_a["url"] = $title->getFullURL();
	$article_a["title"] = $title->getText();
	$article_a["last_edited"] = $article->getTimestamp();
	$article_a["html"] = $article_html;
	
	//Change JSON encode here
	$json = new Services_JSON();
	return $json->encode( $article_a );
}
