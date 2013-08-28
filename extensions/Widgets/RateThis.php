<?php

$wgExtensionFunctions[] = 'wfSpecialWidgetRateThis';


function wfSpecialWidgetRateThis(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class WidgetRateThis extends SpecialPage {
	
		function WidgetRateThis(){
			UnlistedSpecialPage::UnlistedSpecialPage("WidgetRateThis");
		}
		
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView;
			
			$wgOut->setArticleBodyOnly(true);
			
			$widget_title = $wgRequest->getVal("p");
			if(!$widget_title)$widget_title = "WidgetRateThisFrame";
			$widget_frame = Title::makeTitle( NS_SPECIAL , $widget_title  );
			
			$title = $wgRequest->getVal("t");
			$height = (int) ($wgRequest->getVal("h"));
			$width = (int) ($wgRequest->getVal("w"));
			$count = (int) ($wgRequest->getVal("c"));
			$widget_header = $wgRequest->getVal("hd");
			
			
			if($height==0)$height=300;
			if($width==0)$width=300;
			if($count==0)$count=5;
			if(!$widget_header)$widget_header="Top Rated " . $title;
			
			$height = 185 + ($count*55);
			$output = "(function(){document.write(\"<iframe src='{$widget_frame->getFullURL()}&t={$title}&hd={$widget_header}&c={$count}' height='{$height}' width='{$width}' frameborder='0' scrolling='no' id='widget-frame'></iframe>\");})()";
			echo $output; 
			
		}
	}

	SpecialPage::addPage( new WidgetRateThis );
	global $wgMessageCache,$wgOut;
	
	$wgMessageCache->addMessage( 'widgetratethis', 'just a test extension' );
}

?>