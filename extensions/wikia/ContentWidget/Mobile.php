<?php

$wgAjaxExportList [] = 'wfGetMobileText';

//http://www.armchairgm.com/?title=index.php&action=ajax&amp;rs=wfGetSectionDisplay&page=Benny_Agbayani&amp;section=biography&striphtml=1&amp;chars=495

function wfGetMobileText() {
	
	global $wgRequest, $wgOut, $wgTitle, $wgStyleVersion, $wgParser, $wgSitename, $wgServer;
	
	//$page = "Article:The_Best_Quarterback_Ever_Tournament...Sweet_Sixteen_P2";
	$page = $wgRequest->getVal("page");
	if($page=="") {
		$page = "David Wright";
	}
	
	$page_title = Title::makeTitleSafe( NS_MAIN, $page );
	
	$w_edit = $wgServer . $page_title->getEditUrl();
	
	$w_edit = str_replace("%27", "__apos__", $w_edit);
	$w_edit = str_replace("&", "__amp__", $w_edit);
	
	$w_title = $page_title->getText();
	

	$w_site = "<font color='#002BB8'><u><a href='" . Title::newMainPage()->getFullURL() . "' target='_blank'>{$wgSitename}</a></u></font>";

	$w_text = getUsableContent($page);
	
	$w_text_parsed = $wgOut->parse( $w_text, false );
	$w_text_stripped = doFlashFormatting_m($w_text_parsed);
	
	//build string
	//$w_string = "WidgetTitle={$w_title}&WidgetSite={$w_site}&WidgetEdit={$w_edit}&WidgetText={$w_text_stripped}";

	return $w_text_stripped;
	
}

function getUsableContent_m($page) {

	$article_html = "";
	$page_title = Title::makeTitleSafe( NS_MAIN, $page );


	$blocked_sections = array(
		'statistics',
		);
		
	$blocked_text = array();
	$remove_templates = true;
	$remove_categories = true;
	
	//$type = "bio";
	
	$template_to_readd = false;	
	$templates_to_readd = array("{{Player Profile Image}}"=>"{{Player Profile Image Widget|" . $page_title->getFullText() . "}}");
	
	
	$article = new Article($page_title);
	$does_exist = $article->exists();
	if ($does_exist) {
		$article_html = $article->getContent();
			
		foreach($templates_to_readd as $match=>$replace) {
			$found = false;
			$found = strpos($article_html, $match);
			if ($found && $found >= 0) {
				//$template_to_readd = "{{Player Profile Image Widget|" . $page_title->getFullText() . "}}\n";
				$template_to_readd = $replace . "\n";
				break;
			}
		}

		if (sizeof($blocked_sections)) {
			$count = 1;
			$current_article = $article->getSection($article->getContent(), $count);
			while ($current_article) {
				$section = 0;
				$text_in_section = 0;
				$re_pattern = "/==([^=]*)==/iU";
				preg_match($re_pattern, $current_article, $matches);
				if (sizeof($matches) > 0) {
					$section = strtolower($matches[1]);
					$temp_article = $current_article;
					if ($remove_templates) {
						$temp_article = preg_replace("/\{\{[^\}]*\}\}/U", "", $temp_article);
					}
					if ($remove_categories) {
						$temp_article = preg_replace("/\[\[Category\:[\s]+[^\]]+\]\]/i", "\n", $temp_article);
					}
					
					$temp_article = preg_replace("/\n+/", "", $temp_article);
					
					$text_in_section = strlen($temp_article);
					//echo $count . ":" . $text_in_section . ":" . (strlen($section)+4) . "<br/>";
						
						
					
					
				}
				if (($section && in_array($section, $blocked_sections))) {
					$blocked_text[] = $current_article;
				}
				
				else if ($text_in_section == strlen($section) + 4) {
					$blocked_text[] = $current_article;
				}
				
				
				$count++;
				$current_article = $article->getSection($article->getContent(), $count);
			}
			foreach($blocked_text as $value) {
				$article_html = str_replace($value, '', $article_html); 
			}
			
		}
	}
	else {
		$article_html = "Page Not Provided or Does Not Exist";
	}
	
	if ($remove_templates) {
		$article_html = preg_replace("/\{\{[^\}]*\}\}/U", "", $article_html);
	}
	$article_html = str_replace("__TOC__", "", $article_html);
	$article_html .= "__NOTOC__";
	
	$article_html = preg_replace("/\n{2,}/", "\n\n", $article_html);
	
	$article_html = trim($article_html);
	
	if ($template_to_readd) {
		$article_html = $template_to_readd . $article_html;
	}

	return $article_html;
}

function doFlashFormatting_m($text) {
	
	$text = removeAllContentFromTags_m($text);
	$text = changeFormattingForFlash_m($text);
	$text = removeUnallowedTags_m($text);
	$text = removeEmptySections_m($text);
	$text = setDefaultParams_m($text);
	$text = removeLinkFromImagesAndResize_m($text);
	$text = updateEditLinks_m($text);
	$text = resolveRelativeLinks_m($text);
	
	$text = str_replace("&", "__amp__", $text);
	$text = str_replace("%27", "__apos__", $text);
	$text = preg_replace("/\n{2,}/", "\n", $text);
	
	//$text = substr($text, 13);
	
	return $text;
	
}

function updateEditLinks_m($text) {

	$edit_link_font_size = 10;
	
	$re_pattern = "/(<span class='editsection'\>)(\[[^\]]*\])(<\/span\>)/iU";
	$total_matches = preg_match_all($re_pattern, $text, $matches);
	
	for($i=0; $i<$total_matches; $i++) {
		$replace_text = $matches[1][$i] . (!$i ? "<br/>" : "") . "<font size='{$edit_link_font_size}' color='#002BB8'>" . $matches[2][$i] . "</font>" . $matches[3][$i];
		$text = str_replace($matches[0][$i], $replace_text, $text);
	}
	
	return $text;
}

function removeUnallowedTags_m($text) {
	$allowed_tags = array(
		'a'=>array('href','target'),
		'b',
		'br',
		'font'=>array('color','face','size'),
		'i',
		'img'=>array('src','id','width','height','align','hspace','vspace'),
		'li',
		'p'=>array('align','class'),
		'span'=>array('class'),
		'textformat'=>array('blockindent','indent','leading','leftmargin','rightmargin','tabtops'),
		'u'
		);
	
	foreach($allowed_tags as $tag=>$attr) {
		if (!is_array($attr)) {
			$tag = $attr;
			$attr = 0;
		}
		
		$text = preg_replace('/<' . $tag . ' /i', '___*' . $tag . ' ', $text);
		$text = preg_replace('/<' . $tag . '\>/i', '___*' . $tag . '>', $text);
		$text = preg_replace('/<\/' . $tag . '\>/i', '___*/' . $tag .'>', $text);
		
	}
	
	$text = strip_tags($text);
	
	//$text = preg_replace('/<[^\/]+\/\>/', '', $text);
	
	foreach($allowed_tags as $tag=>$attr) {
		if (!is_array($attr)) {
			$tag = $attr;
			$attr = array('____');
			//$attr = 0;
		}
		$text = preg_replace('/___\*' . $tag . '/i', '<' . $tag, $text);
		$text = preg_replace('/___\*\/' . $tag . '/i', '</' . $tag, $text);
		
		if ($attr) {
			//echo 'in here';
			$re_pattern='/(<' . $tag . ' [^\>]+\>)/i';
			$total_matches = preg_match_all($re_pattern, $text, $matches);
			for ($i=0; $i<$total_matches; $i++) {
				//echo $i;
				$attr_match = array();			
				foreach($attr as $value) {
					$sub_re_pattern='/' . $value . '=(.)/i';
					preg_match($sub_re_pattern, $matches[1][$i], $sub_matches);
					if (sizeof($sub_matches)) {
						$quot = $sub_matches[1];
						if ($quot=="'" || $quot == "\"") {
							$end_quot = $quot;
						}
						else {
							$quot = "";
							$end_quot = " ";
						}
						$attr_grab_re = "/" . $value . "=" . $quot . "([^\{$end_quot}]+)" . $end_quot . "/i";
						preg_match($attr_grab_re, $matches[1][$i], $grab_matches);
						if (sizeof($grab_matches)) {
							$attr_match[] = $value . "='" . $grab_matches[1] . "'";
						}
					}
				}
				
				
				$replace_str = "<" . $tag;
				foreach ($attr_match as $key=>$replace_attr) {
					$replace_str .= " " . $replace_attr;
				}
				// Maybe figure out how to check for />
				if (strpos($matches[1][$i], "/>")) {
					$replace_str .= "/";
				}
				$replace_str .= ">";
				$text = str_replace($matches[1][$i], $replace_str, $text);
				
			}
		}
		
	}
	
	
	return $text;
}

function changeFormattingForFlash_m($text) {
	$replace_tags = array(
		'a'=>array("<u><font color='#002BB8'>_replace_</font></u>"=>1),
		'h1'=>array("<font size='19'><b>_replace_</b></font>"=>0),
		'h2'=>array("<font size='17'><b>_replace_</b></font>"=>0),
		'h3'=>array("<font size='14'><b>_replace_</b></font>"=>0),
		'h4'=>array("<font size='12'><b>_replace_</b></font>"=>0),
		//'img'=>array("hspace='3';vspace='3';align='right';width='75';height='75'"=>2),
		//'p'=>"\n_replace_\n"
		);
		
	foreach($replace_tags as $tag=>$replace_opts) {
		
		foreach($replace_opts as $replace_text=>$inject) {
			$re_pattern = "/(<{$tag}[^\>]*\>)([^\r]*)(<\/{$tag}\>)/iU";
			$total_matches = preg_match_all($re_pattern, $text, $matches);
			
			if (!$total_matches) {
				$re_pattern = "/(<{$tag}[^\>]*\>)/iU";
				$total_matches = preg_match_all($re_pattern, $text, $matches);
			}
		
			for($i=0; $i<$total_matches; $i++) {
				if ($inject) {
					$replace_str = $matches[1][$i] .  str_replace("_replace_", $matches[2][$i], $replace_text) . $matches[3][$i];
				}
				
				else {
					$replace_str = str_replace("_replace_", $matches[2][$i], $replace_text);
				}
				$text = str_replace($matches[0][$i], $replace_str, $text);
			}
		}
		
	}
	
	return $text;
}

function setDefaultParams_m($text) {
	
	$replace_tags = array(
		'img'=>array("hspace='3';vspace='3';align='right';width='75';height='75'"=>1),
		//'a'=>array("target='_wikialink'"=>1),
		);
	foreach($replace_tags as $tag=>$replace_opts) {
	
		foreach($replace_opts as $replace_text=>$inject) {
			$re_pattern = "/(<{$tag}[^\>]*\>)/iU";
			$total_matches = preg_match_all($re_pattern, $text, $matches);
				
			for($i=0; $i<$total_matches; $i++) {
	
				if ($inject) {
			
					$replace_str = $matches[0][$i];
					$replace_str = preg_replace("/\/\s*\>/", " />", $replace_str);
					if (strpos($replace_str, "/>")) {
						$end_char = "/>";
					}
					else {
						$end_char = ">";
					}
			
					$replace_strs = split(";", $replace_text);
					foreach($replace_strs as $pair) {
						$keyvalue = split("=", $pair);
						$replace_str = preg_replace("/" . $keyvalue[0] . "='[^']*'/iU", " ", $replace_str);
						//if ($
						$replace_str = str_replace($end_char, " " . $pair . " " . $end_char, $replace_str);
					}
					$replace_str = preg_replace("/\s+/", " ", $replace_str);
				}
				$text = str_replace($matches[0][$i], $replace_str, $text);
			}
		}
	}
	return $text;
}

function removeLinkFromImagesAndResize_m($text) {
	
	$new_width=75;
	
	$re_pattern = "/<a href='[^']+'\><u\><font color='#002BB8'\>(<img [^\>]+\>)<\/font\><\/u\><\/a\>/iU";
	$total_matches = preg_match_all($re_pattern, $text, $matches);
	for ($i=0; $i<$total_matches; $i++) {
		$replace_str = $matches[1][$i];
		
		preg_match("/src='([^']*)'/i", $replace_str, $matches_2);
		if (sizeof($matches_2)) {
			/*
			$src = $matches[1];
			$extension = strtolower(strrev(substr($src, 0, strpos(strrev($src, ".")))))
			$image = false;
			switch ($extension) {
				case ".jpg":
				case ".jpeg":
					$image = imagecreatefromjpeg($src);
					break;
				case ".gif":
					$image = imagecreatefromgif($src);
					break;
				case ".png":
					$image = imagecreatefrompng($src);
					break;
				default:
					break;
					
			}
			*/
			
			$img_size = getimagesize($matches_2[1]);
			if ($img_size) {
				$width = $img_size[0];
				$height = $img_size[1];
				if ($width > $new_width) {
						
					$mult = $width/$new_width;
					$new_height = floor($height / $mult);
				}
				else {
					$new_width = $width;
					$new_height = $height;
				}
				
				$replace_str = preg_replace("/\/\s*\>/", " />", $replace_str);
				if (strpos($replace_str, "/>")) {
					$end_char = "/>";
				}
				else {
					$end_char = ">";
				}
				
				$replace_strs = array("width='" . $new_width . "'","height='" . $new_height . "'");
				
				foreach($replace_strs as $pair) {
						$keyvalue = split("=", $pair);
						$replace_str = preg_replace("/" . $keyvalue[0] . "='[^']*'/iU", " ", $replace_str);
						//if ($
						$replace_str = str_replace($end_char, " " . $pair . " " . $end_char, $replace_str);
				}
				$replace_str = preg_replace("/\s+/", " ", $replace_str);
			}
			
			
			
			
		}
		
		$text = str_replace($matches[0][$i], $replace_str, $text);
	}
	
	
	
	return $text;
}

function removeAllContentFromTags_m($text) {
	$remove_tags = array(
		'script',
		//'table'
		);
		
	foreach($remove_tags as $tag) {
	
		$re_pattern = "/(<{$tag}[^\>]*\>)([^\r]*)(<\/{$tag}\>)/iU";
		$text = preg_replace($re_pattern, '', $text);
		
	}
	
	return $text;
}

function removeEmptySections_m($text) {
	
	
	return $text;
	
}
function resolveRelativeLinks_m($text) {

	global $wgServer;
	
	$text = str_replace("href='/", "href='" . $wgServer . "/", $text);
	
	$text = str_replace("<img src='/skins/common/images/magnify-clip.png' hspace='3' vspace='3' align='right' width='75' height='75' />", "", $text);
	/*
	$replace_tags = array(
		//'img'=>array("hspace='3';vspace='3';align='right';width='75';height='75'"=>1),
		'a'=>array("target='_wikialink'"=>1),
		);
	foreach($replace_tags as $tag=>$replace_opts) {
	
		foreach($replace_opts as $replace_text=>$inject) {
			$re_pattern = "/(<{$tag}[^\>]*\>)/iU";
			$total_matches = preg_match_all($re_pattern, $text, $matches);
				
			for($i=0; $i<$total_matches; $i++) {
	
				if ($inject) {
			
					$replace_str = $matches[0][$i];
					$replace_str = preg_replace("/\/\s*\>/", " />", $replace_str);
					if (strpos($replace_str, "/>")) {
						$end_char = "/>";
					}
					else {
						$end_char = ">";
					}
			
					$replace_strs = split(";", $replace_text);
					foreach($replace_strs as $pair) {
						$keyvalue = split("=", $pair);
						$replace_str = preg_replace("/" . $keyvalue[0] . "='[^']*'/iU", " ", $replace_str);
						//if ($
						$replace_str = str_replace($end_char, " " . $pair . " " . $end_char, $replace_str);
					}
					$replace_str = preg_replace("/\s+/", " ", $replace_str);
				}
				$text = str_replace($matches[0][$i], $replace_str, $text);
			}
		}
	}
	*/
	return $text;

}

?>