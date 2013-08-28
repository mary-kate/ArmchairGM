<?php

$wgExtensionFunctions[] = 'wfSpecialCreateRatingWidget';


function wfSpecialCreateRatingWidget(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class CreateRatingWidget extends UnlistedSpecialPage {
	
		function CreateRatingWidget(){
			UnlistedSpecialPage::UnlistedSpecialPage("CreateRatingWidget");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest;
			
			$wgOut->setPagetitle( "Add Ratings Widget To Your Site" );
			
			$widget_title = Title::makeTitle( NS_SPECIAL , "WidgetRateThis"  );
			$preview_title = Title::makeTitle( NS_SPECIAL , "WidgetRateThisListFrame"  );
			
			$category =  $wgRequest->getVal('c');
			
			$js = "<script>
			var base_url = '{$widget_title->getFullURL()}';
			var preview_url = '{$preview_title->getFullURL()}';
			function updateCode(){
				js = '<script src=\"' + base_url + '&p=WidgetRateThisListFrame&t=' + \$F(\"category\") + '&hd=' + \$F(\"widget_title\") + '&w=' + \$F(\"width\") + '&c=' + \$F(\"count\") + '\" type=\"text/javascript\"></' + 'script><div style=\"color:#666666;font-size:11px;padding-left:7px;font-family:Arial\">powered by <a href=\"http://www.armchairgm.com\" style=\"text-decoration:none;font-size:12px;font-weight:800;\"><span style=\"color:#285C98\">armchair</span><span style=\"color:#78BA5D\">gm</span></a></div></div>'
				\$(\"code\").value = js;
				\$(\"widget-frame\").width = \$F(\"width\")
				\$(\"widget-frame\").src = preview_url + '&t=' + \$F(\"category\") + '&hd=' + \$F(\"widget_title\") + '&w=' + \$F(\"width\") + '&c=' + \$F(\"count\")
				autofitIframe(\"widget-frame\");
			}
			function autofitIframe(id){
						if (!window.opera && !document.mimeType && document.all && document.getElementById){
							parent.document.getElementById(id).style.height=(185+(\$F(\"count\")*55))+\"px\";
						}
						else if(document.getElementById) {
							parent.document.getElementById(id).style.height=(185+(\$F(\"count\")*55))+\"px\"
						}
					}
			</script>";
			
			$css = "<style>
			.create-widget-title{
				font-size:14px;
				font-weight:800;
				color:#78BA5D;
				padding-bottom:10px;
			}
			
			.widget-customize-row {
				padding-bottom:10px;
			}
			</style>";
			
			$wgOut->addHTML($js);
			$wgOut->addHTML($css);
			$height = 185 + (5*55);
			$width = 300;
			$out = "
			
			<div class=\"widget-customize\">
			<div class=\"create-widget-title\">customize your widget</div>
				
			<div class=\"widget-customize-row\"><b>category</b>: <input type=\"text\" name=\"category\" id=\"category\" value=\"{$category}\" onchange=\"updateCode();\"></div>
			<div class=\"widget-customize-row\"><b>widget title</b>: <input type=\"text\" name=\"widget_title\" id=\"widget_title\" value=\"Top Rated {$category}\" size=\"40\" onchange=\"updateCode();\"></div>
			
			<div class=\"widget-customize-row\"><b>width (in pixels)</b>:	<input type=\"text\" name=\"width\" id=\"width\" size=\"2\" value=\"300\" onchange=\"updateCode();\"></div>	
			<div class=\"widget-customize-row\"><b>number to show</b>:	<input type=\"text\" name=\"count\" id=\"count\" size=\"2\" value=\"5\" onchange=\"updateCode();\"></div>
			
			</div>
			
			<div class=\"create-widget-title\">paste this code onto your site</div>
			<div class=\"widget-code\">
			<textarea name=\"code\" id=\"code\" rows=\"4\" cols=\"75\" >&lt;script src=&quot;{$widget_title->getFullURL()}&p=WidgetRateThisListFrame&t={$category}&hd=Top Rated {$category}&w=300&c=5&quot; type=&quot;text/javascript&quot;&gt;&lt;/script&gt;&lt;div style=&quot;color:#666666;font-size:11px;padding-left:7px;font-family:Arial&quot;&gt;powered by &lt;a href=&quot;http://www.armchairgm.com&quot; style=&quot;text-decoration:none;font-size:12px;font-weight:800;&quot;&gt;&lt;span style=&quot;color:#285C98&quot;&gt;armchair&lt;/span&gt;&lt;span style=&quot;color:#78BA5D&quot;&gt;gm&lt;/span&gt;&lt;/a&gt;&lt;/div&gt;&lt;/div&gt;</textarea>
			</div>
			<p>
			<div class=\"create-widget-title\">preview</div>
			<div id=\"preview\">
			<iframe id=\"widget-frame\" FRAMEBORDER=\"0\" scrolling=\"no\" style=\"overflow:hidden\" height=\"$height\" src=\"{$preview_title->getFullURL()}&t={$category}&hd=Top Rated {$category}&c=5&h={$height}&w={$width}\"></iframe>
			</div><div style=\"color:#666666;font-size:11px;padding-left:7px;font-family:Arial\">powered by <a href=\"http://www.armchairgm.com\" style=\"text-decoration:none;font-size:12px;font-weight:800;\"><span style=\"color:#285C98\">armchair</span><span style=\"color:#78BA5D\">gm</span></a></div></div>
			";
			
			$wgOut->addHTML($out);
	
		 
		}
		
	}
	
	SpecialPage::addPage( new CreateRatingWidget );
	global $wgMessageCache,$wgOut;
}

?>