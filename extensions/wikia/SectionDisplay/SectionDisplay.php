<?php
$wgAjaxExportList [] = 'wfGetSectionDisplay';

function wfGetSectionDisplay() {
	header ("Content-type: text/xml; charset=utf-8");
	
	global $wgRequest, $wgOut, $wgStyleVersion, $wgParser, $wgServer;

	$output = "";
	
	$page = $wgRequest->getVal("page");
	$section = $wgRequest->getVal("section"); 
	$char_limit = $wgRequest->getVal("chars");
	$strip_tags = $wgRequest->getVal("striphtml"); 
	
	if (!$page) {
		$page = "No Page Provided";
	}
		
	$page_title = Title::makeTitleSafe( NS_MAIN, $page );
	$article = new Article($page_title);
	$does_exist = $article->exists();
	$found_section = false;
	$timestamp = "";
	$url = "";
	$exists = "0";
	
	if ($does_exist) {
		
		$timestamp = Revision::getTimestampFromID($article->getRevIDFetched());
		$url = $page_title->getFullUrl();
		$exists = "1";
		
		$count = 1;
		$current_article = $article->getSection($article->getContent(), $count);
		while ($current_article) {
			if ($section && strpos(strtolower($current_article), "==" . strtolower($section) ."==") === 0) {
				$article_html = $wgOut->parse( $current_article, false );
				if (strpos(strtolower($article_html), strtolower($section) . "</span></h2>") !== 0) {
					$output = substr($article_html, strpos(strtolower($article_html), strtolower($section)."</span></h2>") + strlen("{$section}</span></h2>"));
				}
				else {
					$output = $article_html;
				}
				if ($strip_tags) {
					$output = strip_tags($output);
				}
				if ($char_limit && intval($char_limit) > 0) {
					$output = substr($output, 0, $char_limit);
				}
				$found_section = true;
				break;
			}
			$count++;
			$current_article = $article->getSection($article->getContent(), $count);
		}
		
		if(!$found_section) {
			$article_html = $wgOut->parse( $article->getContent(), false );
			$output = $article_html;
			if ($strip_tags) {
				$output = strip_tags($output);
			}
						
		}
		
		
	}
	else {
		$output = "Page Not Provided or Does Not Exist";
		$url = $wgServer . $page_title->getEditUrl(); 
	}
	
	$xml_output .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml_output .= "<page>";
	$xml_output .= "<exists>$exists</exists>";
	$xml_output .= "<title><![CDATA[" . $page_title->getText() . "]]></title>";
	$xml_output .= "<timestamp>" . $timestamp . "</timestamp>";
	$xml_output .= "<url><![CDATA[" . $url . "]]></url>";
	$xml_output .= "<content><![CDATA[" . $output . "]]></content>";
	$xml_output .= "</page>";
	
	//echo $xml_output;
	//$output = $output;
	return $xml_output;
	

}



?>
