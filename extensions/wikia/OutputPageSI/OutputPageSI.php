<?php

	function SIStripText(&$parser, &$text, &$strip_state) {
		global $wgDefaultSkin, $wgTitle, $wgSuppressToc;
		if ($wgDefaultSkin == "SportsSI") {
			
			$remove_top_section_array = array("Statistics","Scouting Report",);
			$remove_template_array = array("Player Profile Image","Player Profile Rating Box","Player Profile Ad","Player Profile Media");
			
			
			
			$total_matches = preg_match_all("/(==[^=]+==\n)/iU", $text, $matches);			
			if ($total_matches) {
				for($i=0; $i<$total_matches; $i++) {
					$text = str_replace($matches[1][$i], "|" . $matches[1][$i], $text);
				}
			}
			
			foreach($remove_top_section_array as $item_to_remove) {
				$re_pattern = "/(\|==" . $item_to_remove . "==[^\r]+)\|==[^=]*==/iU";
				preg_match($re_pattern, $text, $matches);
				if (sizeof($matches)) {
					$text = str_replace($matches[1], "", $text);
				}
			}
			
			$total_matches = preg_match_all("/(\|==[^=]+==\n)/iU", $text, $matches);			
			if ($total_matches) {
				for($i=0; $i<$total_matches; $i++) {
					$text = str_replace($matches[1][$i], substr($matches[1][$i],1), $text);
				}
			}
			
			foreach($remove_template_array as $item_to_remove) {
				$text = str_replace("{{" . $item_to_remove . "}}", "", $text);
			}
			
			if ($wgSuppressToc) {
				$text = str_replace("__TOC__", "", $text);
				$text = str_replace("__NOTOC__", "", $text);
				$text .= "__NOTOC__";
				/*
				$has_toc = strpos($text, "__TOC__");
				if ($has_toc || $has_toc===0) {
					$text = str_replace("__TOC__", "__NOTOC__", $text);
				}
				else {
					$text .= "__NOTOC__";
				}
				*/
			}
			
		}
		return true;
	}

?>
