<?php

$wgExtensionFunctions[] = 'wfSpecialPageCopy';

$team_translations = array();


function wfSpecialPageCopy(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class PageCopy extends SpecialPage {
	
	
	function PageCopy(){
		UnlistedSpecialPage::UnlistedSpecialPage("PageCopy");
	}
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $wgParser;
			
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Jeff/pageCopy.js?{$wgStyleVersion}\"></script>\n");
			$redirect = "";
			
			$action = $wgRequest->getVal("pageCopyAction");
	
			switch($action){
			case "display":
				$page_title = $wgRequest->getVal("pageToCopy");
				$this->displayWikiPage($page_title);
				break;
			case "preview":
				$content = $wgRequest->getVal("preview_text");
				$this->displayPreview($content);
				break;
			default:
				$this->displayCopyFacility();
				break;
			}
	}
	
	function displayWikiPage($title) {
		global $wgOut, $wgParser;
		
		$page_output = "<div=\"page_copy_container_div\">";
		if ($title != "") {
			
			$page_title = Title::makeTitleSafe( NS_MAIN, $title );
			$article = new Article($page_title);
			$title = "Copying page {$title}";
			$revision = $article->getRevIdFetched();
			$raw_text = new RawPage($article, false);
			$does_exist = $article->exists();
			if ($does_exist) {
				$page_output .= $raw_text->getArticleText();
			}
			else {
				$page_output .= "###";
			}
		}
		else {
			$page_output .= "***";
		}
		
		$page_output .= "<!--end_page_copy_content--></div>";
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($page_output);
		//echo $page_output;
	}
	
	function displayCopyFacility() {
		global $wgOut, $wgParser;

		$output  = "<form id=\"page_copy_form\">";
		$output .= "Enter the url of the page that you want to copy:<br/>";
		$output .= "site url:<input type=\"text\" id=\"site\" name=\"site\" /><br/>";
		$output .= "additional:<input type=\"text\" id=\"url_struct\" name=\"url_struct\" /><br/>";
		$output .= "page_title:<input type=\"text\" id=\"page_title\" name=\"page_title\" /><br/>";
		$output .= "<input type=\"button\" id=\"getpage\" name=\"getpage\" value=\"get page\" onclick=\"getOtherPage(document.getElementById('site').value, document.getElementById('url_struct').value, document.getElementById('page_title').value, 'page_to_copy')\"/><br/>";
		$output .= "<input type=\"hidden\" id=\"preview_text\" name=\"preview_text\" /><br/>";
		$output .= "<input type=\"button\" id=\"preview_old\" name=\"preview_old\" value=\"Preview\" onclick=\"viewPreview(document.getElementById('preview_div'), 'page_to_copy', location.href)\"/><br/>";		
		$output .= "<textarea id=\"page_to_copy\" name=\"page_to_copy\" cols=\"60\" rows=\"25\"></textarea>";
		$output .= "</form>";
		
		$output .= "<a name=\"view_preview\"></a><br/>";
		$output .= "<div id=\"preview_div\"></div>";
		
		$wgOut->setPageTitle("Copying Page");
		$wgOut->addHTML($output);
		
	}
	
	function displayPreview($content) {
		global $wgOut, $wgParser;
		
		$popts = $wgOut->parserOptions();
		$popts->setTidy(true);
		$page_title = Title::makeTitleSafe( NS_MAIN, "page_copy_preview" );
		
		$p_output = $wgParser->parse($content, &$page_title, $popts, true, true, null);
		
		$page_output = "<div=\"page_copy_container_div\">";
		$page_output .= $p_output->getText();
		//$page_output .= $content;
		$page_output .= "<!--end_page_copy_content--></div>";
		
		$wgOut->addHTML($page_output);
		

	}

	function get_page($base_url, $page_title) {
		$handle = fopen($base_url . $page_title, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		return $contents;

	}

}

SpecialPage::addPage( new PageCopy );



}

?>
