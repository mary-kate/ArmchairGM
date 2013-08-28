<?php
/*
 * Ajax Functions used by Pick Game
 */
 

$wgAjaxExportList [] = 'wfShowUpdaterPreview';
function wfShowUpdaterPreview($text) {
	global $IP, $wgUser, $wgOut, $wgParser;
	
	if ($text != "") {
		$popts = $wgOut->parserOptions();
		$popts->setTidy(true);
		$page_title = Title::makeTitleSafe( NS_MAIN, "Player Updater Preview" );
		
		$p_output = $wgParser->parse(urldecode($text), &$page_title, $popts, true, true, null);
		
		return $p_output->getText();
		//return $text;
	}
	else {
		return "nothing";
	}
	
	//return $page_title_passed;

}

$wgAjaxExportList [] = 'wfCreatePageFromUpdater';
function wfCreatePageFromUpdater($text, $title) {
	global $IP, $wgUser, $wgOut, $wgParser;
	
	if ($text != "" && $title != "") {
		
		/*
		$popts = $wgOut->parserOptions();
		$popts->setTidy(true);
		$page_title = Title::makeTitleSafe( NS_MAIN, "Player Updater Preview" );
		
		$p_output = $wgParser->parse($text, &$page_title, $popts, true, true, null);
		
		return $p_output->getText();
		*/
		
		$page_title = Title::makeTitleSafe( NS_MAIN, urldecode($title) );
		$article = new Article($page_title);
		$article->doEdit( urldecode($text), "MLB Player Pages");
		
		$re_pattern = "/\[\[Category\:[\s]+([^\]]+)\]\]/i";
		//$re_pattern = "/(Category)\:/i";
		
		$total_matches = preg_match_all($re_pattern, urldecode($text), $matches);
		//$categories = "";
		for ($i=0; $i<$total_matches; $i++) {

			if ($matches[1][$i]) {
				//$categories .= $matches[1][$i] . "; ";
				MLBRosterPages::makeCategory($matches[1][$i]);
			}
		}

		
		return "Created page for: " . urldecode($title) . "(<a href=\"/index.php?title={$title}\">link</a>)";
		//return $total_matches .": " . $categories;
	}
	else {
		return "nothing";
	}
	
	//return $page_title_passed;

}

?>
