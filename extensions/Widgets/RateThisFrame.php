<?php

$wgExtensionFunctions[] = 'wfSpecialWidgetRateThisFrame';


function wfSpecialWidgetRateThisFrame(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class WidgetRateThisFrame extends SpecialPage {
	
		function WidgetRateThisFrame(){
			UnlistedSpecialPage::UnlistedSpecialPage("WidgetRateThisFrame");
		}
		
		function getPageImage($pageid){
			$dbr =& wfGetDB( DB_MASTER);
			$sqlc = "select il_to from {$dbr->tableName( 'imagelinks' )} where il_from=" . $pageid . " limit 1";
			$res = $dbr->query($sqlc);
			$row = $dbr->fetchObject( $res );
			if($row){
				$il_to = $row->il_to;
			}
			return $il_to;
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP, $wgVoteDirectory;
			require_once ("$wgVoteDirectory/VoteClass.php");
			require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");
			$output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
			  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			 <head>
			  <title>Rate This</title>
			  
			  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			  
			$output .= "\n<script type=\"text/javascript\" src=\"extensions/Prototype/prototype.js\"></script>\n";
			$output .= "<script type=\"text/javascript\" src=\"extensions/Scriptaculous/scriptaculous.js\"></script>\n";
			$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/VoteStars.js\"></script>\n";
			$output .= "<script type=\"text/javascript\" src=\"extensions/ListPages/ListPages.js\"></script>\n";			
			$sk = $wgUser->getSkin();
			$output .= $sk->getUserStyles();
		
			$output .= '<script type="text/javascript">';
			$output .= "	function widgetToggleArticles(){
				Effect.SwitchOff('rating-image',{afterFinish:function(){Effect.Appear('rating-articles',{duration:2.0} )} });
							
							
					}
				</script>";
				
			$output .= '</head>';	
			$output .= '</head>';	
			$output .= '<body style="background:#fff">';
			
			
			$title = Title::newFromText($wgRequest->getVal("t"));

			//$output .= "<div class=\"rate-header\">rate {$_GET["t"]}</div>";
			$output .= '<div class="rating-widget"><div id="navbar" >';
			$output .= '<div id="leftNavLeft">';
			$output .= '<img src="images/sports/leftNavLeft.png" alt="logo" border="0"/>';
			$output .= '</div>';
			$output .= '<div id="leftNavMiddle">';
			$output .= '<ul>';
			$output .= '<li><a href="index.php?title=' . $title->getText() . '">Rate ' . $title->getText() . '</a></li>';
			$output .= '</ul>';
			$output .= '</div>';
			$output .= '<div id="leftNavRight">';
			$output .= '<img src="images/sports/leftNavRight.png" alt="logo" border="0"/>';
			$output .= '</div></div><div class="rating-clear"></div>';
			 
			$output .= "<div class=\"rating-nav\">";
			$output .= "<ul>";
			$output .= "<li><a href=\"{$title->getFullURL()}\">view profile</a></li>";
			$output .= "<li><a href=\"javascript:widgetToggleArticles()\">show articles</a></li>";
			$output .= "<li>another option</li>";
			$output .= "</ul>";
			$output .= "</div>";
			
			if($title!=NULL){   
				$page_id = $title->getArticleID();
				$user_id = $wgUser->getID();
				$user_name = $wgUser->getName();
				
				$PageImage = $this->getPageImage($page_id);
				if($PageImage){
					$img = Image::newFromName($PageImage);
					$img_tag = '<img src="' . $img->getURL() . '" alt="' . $PageImage . '" width="65"/>';
					$output .=  '<div id="rating-image" class="rating-image"><p class="rating-image">' . $img_tag . '</p></div>';
				}
				$list = new ListPages();
				$list->setCategory("{$title->getText()} News, {$title->getText()} Opinions");
				$list->setShowCount(5);
				$list->setOrder("New");
				$list->setBool("ShowDate","NO");
				$list->setBool("ShowStats","NO");
				$list->setBool("ShowNav","NO");
				
				$output .= '<div id="rating-articles" style="display:none">';
				$output .= $list->DisplayList();
				$output .= '</div>';
				$output .= "<div class='rating-clear'></div>";
				
				
				$vote = new VoteStars($page_id);
				$vote->setUser($user_name,$user_id);
				$output .= $vote->display();
			}
			
			$output .= "<div class='widget-powered-by'>powered by <a href='http://www.armchairgm.com'><img src='images/sports/logo.png' width='110' alt='logo' /></a></div>";
			
			
			$output .= "<script type='text/javascript'>var widgetCode='<scri' + 'pt type=\"text/javascript\" src=\"http://sports.box8.tpa.wikia-inc.com/index.php?title=WidgetRateThis&t=" . $title->getText() . "\"></scri' + 'pt>';</script><div class=\"widgetcode\"><a href='javascript:document.test.widget.select();'>Get This Widget!:</a><script type=\"text/javascript\">document.write('<form name=\"test\" style=\"margin:0px\"><input name=widget value=\''+ widgetCode +'\' style=\"/width:50px;_width:100px;\"/></form>');</script></div>";
			
			
  
			$output .= '</div></body>';
			$output .='</html>';
			$wgOut->setArticleBodyOnly(true);
			echo $output; 
			
		}
	}

	SpecialPage::addPage( new WidgetRateThisFrame );
	global $wgMessageCache,$wgOut;
	
	$wgMessageCache->addMessage( 'widgetratethis', 'just a test extension' );
}

?>