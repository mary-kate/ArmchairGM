<?php

$wgExtensionFunctions[] = 'wfSpecialWidgetRateThisListFrame';


function wfSpecialWidgetRateThisListFrame(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class WidgetRateThisListFrame extends SpecialPage {
	
		function WidgetRateThisListFrame(){
			UnlistedSpecialPage::UnlistedSpecialPage("WidgetRateThisListFrame");
		}
		
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP, $wgVoteDirectory, $wgStyleVersion;
			require_once ("$wgVoteDirectory/VoteClass.php");
			require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");
			
			$output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
			  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			 <head>
			 	<script>
					function autofitIframe(id){
						alert(id)
						if (!window.opera && !document.mimeType && document.all && document.getElementById){
							parent.document.getElementById(id).style.height=(this.document.body.offsetHeight+35)+"px";
						}
						else if(document.getElementById) {
							parent.document.getElementById(id).style.height=(this.document.body.scrollHeight+35)+"px"
						}
					}
				</script>
		  <title>Rate This</title>
			  
			  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			  
			$output .= "\n<script type=\"text/javascript\" src=\"extensions/Prototype/prototype.js\"></script>\n";
			$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>\n";
			$output .= "<script type=\"text/javascript\" src=\"extensions/ListPages/ListPages.js?{$wgStyleVersion}\"></script>\n";			
			$sk = $wgUser->getSkin();
			$output .= $sk->getUserStyles();
	
			$output .= '</head>';	
			$output .= '</head>';	
			$output .= '<body style="background:#fff" donload="autofitIframe(\'widget-frame\')">';
			
			
			$category = $wgRequest->getVal("t");
			$show_count = $wgRequest->getVal("c");
			$width = $wgRequest->getVal("w");
			$widget_header = $wgRequest->getVal("hd");
			
			$output .= '<div class="rating-widget">
					<div class="rating-widget-title">
						<a target="_top" href="index.php?title=Category:' . $category . '">' . $widget_header . '</a>
						</div>
						<div class="widget-powered-by">powered by <a href="http://www.armchairgm.com" style="text-decoration:none"><span style="color:#285C98">armchair</span><span style="color:#78BA5D">gm</span></a></div>
						';

			
			$list = new ListPages();
			$list->setCategory($category);
			$list->setShowCount($show_count);
			$list->setOrder("Vote Average");
			$list->setBool("ShowRating","YES");
			$list->setBool("ShowDate","NO");
			$list->setBool("ShowStats","NO");
			$list->setBool("ShowNav","YES");
			
			$output .= '<div class="rating-articles">';
			$output .= str_replace("a href=","a target='_top' href=",$list->DisplayList());
			$output .= '</div>';
			$output .= "<div class='rating-clear'></div>";
		
			$widget_title = Title::makeTitle( NS_SPECIAL , "WidgetRateThis"  );
			$output .= "<script type='text/javascript'>var widgetCode='<scri' + 'pt type=\"text/javascript\" src=\"" . $widget_title->getFullURL() . "&p=WidgetRateThisListFrame&t=" . $category . "&hd={$widget_header}&c=$show_count&w=$width\"></scri' + 'pt><div style=\"color:#666666;font-size:11px;padding-left:7px;font-family:Arial\">powered by <a href=\"http://www.armchairgm.com\" style=\"text-decoration:none;font-size:12px;font-weight:800;\"><span style=\"color:#285C98\">armchair</span><span style=\"color:#78BA5D\">gm</span></a></div></div>';</script><div class=\"widgetcode\"><a href='javascript:document.test.widget.select();'>Get This Widget:</a><script type=\"text/javascript\">document.write('<form name=\"test\" style=\"margin:0px\"><input name=widget value=\''+ widgetCode +'\' style=\"/width:width:150px;\"/></form>');</script></div>";

			$output .= '</div></body>';
			$output .='</html>';
			$wgOut->setArticleBodyOnly(true);
			echo $output; 
			
		}
	}

	SpecialPage::addPage( new WidgetRateThisListFrame );
	global $wgMessageCache,$wgOut;
	
	$wgMessageCache->addMessage( 'widgetratethis', 'just a test extension' );
}

?>