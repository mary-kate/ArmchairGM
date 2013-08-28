<?php
require_once( 'JSON.php' );
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/* JSON response to retrieve Mediawiki article text
   via the url structure of http://en.wikipedia.org/w/index.php?title=UnitedHealth_Group&printable=yes
  
*/

function get_mediawiki_js_variable( $variable_name, $mediawiki_text){
	preg_match("/var {$variable_name} = \"(.*?)\";/si", $mediawiki_text, $matches );
	return $matches[1];
}

function get_mediawiki_article($article_title){
	
	$article_title = urldecode($article_title);
	
	$url = "http://en.wikipedia.org/w/index.php?title={$article_title}&printable=yes";
	
	$article_text = file_get_contents( $url );

	//get mediawiki page variables (used to get actual URL, title)
	$wgServer = get_mediawiki_js_variable( "wgServer", $article_text) ;	
	$wgArticlePath = get_mediawiki_js_variable( "wgArticlePath" , $article_text) ;
	$wgTitle = get_mediawiki_js_variable( "wgTitle", $article_text) ;	
	$wgPageName = get_mediawiki_js_variable( "wgPageName" , $article_text) ;
	$url = $wgServer . str_replace('$1', $wgPageName, $wgArticlePath);
	
	if( !$url ){
		return "error";
	}
	
	//get just in between <body> tags
	preg_match("/<body[^>]*?>(.*?)<\/body>/si", $article_text, $matches );
	$body_text = $matches[1];
	
	if( !$body_text ){
		return "error";
	}	
	
	//strip out <script> tags
	$body_text =  preg_replace('/<script[^>]*?>.*?<\/script>/si', '', $body_text);
	
	//construct array for json_encode
	$article["html"] = $body_text;
	$article["url"] = $url;
	$article["title"] = $wgTitle;	
	
	//Change JSON encode here
	$json = new Services_JSON();
	return $json->encode( $article );
}

echo get_mediawiki_article( $_POST["title"] );
//echo get_mediawiki_article( "UnitedHealth_Group" );
?>