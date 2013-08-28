<?php
/**
 * See skin.txt
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die();
	
/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */  

 function get_dates_from_elapsed_days($number_of_days){
	$dates[date("F j, Y", time() )] = 1; //gets today's date string
	for($x=1;$x<=$number_of_days;$x++){
		$time_ago = time() - (60 * 60 * 24 * $x);
		$date_string = date("F j, Y", $time_ago);
		$dates[$date_string] = 1;
	}
	return $dates;
}  

global $IP;
require_once("$IP/skins/MagazineShell.php");
require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");


class SkinSports extends SkinMagazineShell {
  
  #set stylesheet
  function getStylesheet() {
    return "http://images.wikia.com/common/wikiany/css/armchairgm/Sports.css";
  }
  
  #set skinname
  function getSkinName() {
    return "Sports";
  }
  
  
	function pageTitle() {
		global $wgOut, $wgSupressPageTitle;
		if( !$wgSupressPageTitle ){
			$s = '<h1 class="pagetitle">' . htmlspecialchars( $wgOut->getPageTitle() ) . '</h1>';
			return $s;
		}
	}
	
	
		# get the user/site-specific stylesheet, SkinTemplate loads via RawPage.php (settings are cached that way)
	function getUserStylesheet() {
		global $wgStylePath, $wgRequest, $wgContLang, $wgSquidMaxage, $wgStyleVersion;
		$sheet = $this->getStylesheet();
		$s = "@import \"http://images.wikia.com/common/wikiany/css/armchairgm/common.css?$wgStyleVersion\";\n";
		$s .= "@import \"$sheet?$wgStyleVersion\";\n";
		if($wgContLang->isRTL()) $s .= "@import \"$wgStylePath/common/common_rtl.css?$wgStyleVersion\";\n";

		$query = "usemsgcache=yes&action=raw&ctype=text/css&smaxage=$wgSquidMaxage";
		//$s .= '@import "' . self::makeNSUrl( 'Common.css', $query, NS_MEDIAWIKI ) . "\";\n" .
		//	'@import "' . self::makeNSUrl( ucfirst( $this->getSkinName() . '.css' ), $query, NS_MEDIAWIKI ) . "\";\n";

		$s .= $this->doGetUserStyles();
		return $s."\n";
	}
	
  /**
  * This gets called shortly before the \</body\> tag.
  * @return String HTML-wrapped JS code to be put before \</body\> 
  */
  
function bottomScripts() {
		
		global $wgJsMimeType;
		
		$r = "";
		//$r .= '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>' . "\n";
		//$r .= '<script type="text/javascript">' . "\n";
		//$r .= '_uacct="UA-1328449-1";' . "\n";
		//$r .= '_userv=2;' . "\n";
		//$r .= 'urchinTracker();' . "\n";
		//$r .= '</script>' . "\n";
		$r .= '<!-- Start Quantcast tag -->' . "\n";
		$r .= '<script type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>' . "\n";
		$r .= '<script type="text/javascript">_qacct="p-8bG6eLqkH6Avk";quantserve();</script>' . "\n";
		$r .= '<noscript>' . "\n";
		$r .= '<a href="http://www.quantcast.com/p-8bG6eLqkH6Avk" target="_blank"><img src="http://pixel.quantserve.com/pixel/p-8bG6eLqkH6Avk.gif" style="display: none;" border="0" height="1" width="1" alt="Quantcast"/></a>' . "\n";
		$r .= '</noscript>' . "\n";
		$r .= '<!-- End Quantcast tag -->' . "\n";
		$r .= "\n\t\t<script type=\"$wgJsMimeType\">if (window.runOnloadHook) runOnloadHook();</script>\n";


	
		//$r .= "<!-- FM Tracking Pixel -->\n<script type='text/javascript' src='http://static.fmpub.net/site/ArmchairGM'></script>\n<!-- FM Tracking Pixel -->\n";
		
		return $r;
}

function editMenu() {
	
	global $wgTitle, $wgOut, $wgUser;
	
	//safe urls
	$title = Title::makeTitle( $wgTitle->getNameSpace(), $wgTitle->getText() );
	$move = Title::makeTitle(NS_SPECIAL,"Movepage");
	$upload_file = Title::makeTitle(NS_SPECIAL,"Upload");
	$what_links_here = Title::makeTitle(NS_SPECIAL,"Whatlinkshere");
	
	$edit_menu = "<div id=\"edit-menu\">
		<div id=\"edit-button\">";
			if(!$wgOut->isArticleRelated())$edit_menu.="<a href=\"".$title->escapeFullURL('action=edit')."\" rel=\"nofollow\" class=\"edit-action-menu\">Locked</a>";
			if($wgTitle->userCan('edit')) {
				$edit_menu.="<a href=\"".$title->escapeFullURL('action=edit')."\" rel=\"nofollow\" class=\"edit-action-menu\">Edit</a>";
			} else {
				$edit_menu.="<a href=\"".$title->escapeFullURL('action=edit')."\" rel=\"nofollow\" class=\"edit-action-menu\">Locked</a>";
			}
			$edit_menu .= "<a href=\"javascript:editMenuToggle();\" rel=\"nofollow\" class=\"edit-menu-arrow\"></a>
			<div class=\"cleared\"></div>";
		$edit_menu .= "</div>
		<div class=\"cleared\"></div>";

		//sub menu
		$edit_menu .= "<div class=\"edit-sub-menu\" id=\"edit-sub-menu-id\" style=\"display:none;\">
			<a href=\"".$wgTitle->escapeFullURL('action=history')."\" rel=\"nofollow\" class=\"page-history-action\">Page history</a>
			<a href=\"" . $title->getTalkPage()->escapeFullURL() . "\" rel=\"nofollow\" class=\"discuss-action\">Discuss page</a>";
			if($wgTitle->userCanMove())$edit_menu.="<a href=\"".$move->escapeFullURL('target='.$wgTitle->getPrefixedURL())."\" rel=\"nofollow\" class=\"move-action\">Move this page</a>";
			$edit_menu .= "<a href=\"{$what_links_here->escapeFullURL()}/{$wgTitle->getPrefixedURL()}\" rel=\"nofollow\" class=\"what-links-here-action\">What links here</a>";
			if($wgUser->isAllowed('protect'))$edit_menu .= "<a href=\"".$wgTitle->escapeFullURL('action=protect')."\" rel=\"nofollow\" class=\"protect-action\">Protect</a>";
			if($wgUser->isAllowed('delete'))$edit_menu .= "<a href=\"".$wgTitle->escapeFullURL('action=delete')."\" rel=\"nofollow\" class=\"delete-action\">Delete</a>";
			if($wgUser->isLoggedIn())$edit_menu .= "<a href=\"".$wgTitle->escapeFullURL('action=watch')."\" rel=\"nofollow\" class=\"watch-action\">Watch this page</a>
			<a href=\"{$upload_file->escapeFullURL()}\" rel=\"nofollow\" class=\"upload-file-action\">Upload File</a>";
			if( $wgTitle->getNamespace() == NS_BLOG && in_array('staff', $wgUser->getGroups() ) ) $edit_menu .= "<a href=\"".Title::makeTitle(NS_SPECIAL, "UpdateFeatured")->escapeFullURL()."/{$wgTitle->getArticleID()}\" rel=\"nofollow\" class=\"move-action\">Feature This</a>";
		$edit_menu .= "</div>
	</div>";
	 
	
	return $edit_menu;
}

function footer() {
	
	global $IP, $wgUser, $wgTitle, $wgOut,$wgUploadPath, $wgMemc, $wgServer;
	
	$title = Title::makeTitle($wgTitle->getNamespace(),$wgTitle->getText());
	$page_title_id = $wgTitle->getArticleID();
	$main_page = Title::makeTitle(NS_MAIN,"Main Page");
	$about = Title::makeTitle(NS_MAIN,"About");
	$special = Title::makeTitle(NS_SPECIAL,"Specialpages");
	$help = Title::makeTitle(NS_MAIN,"UserRegister");
	
	$footer_show = @array(NS_VIDEO,NS_MAIN,NS_IMAGE);
	
	//edit button
	$footer = "";
	if (in_array($wgTitle->getNamespace(), $footer_show) && ($wgTitle->getText()!="Main Page")) {
	
		$key = wfMemcKey( 'recenteditors', 'list', $page_title_id );
		$data = $wgMemc->get( $key );
		$editors = array();
		if(!$data ) {
			wfDebug( "loading recent editors for page {$page_title_id} from db\n" );
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT DISTINCT rev_user, rev_user_text FROM revision WHERE rev_page = {$page_title_id} and rev_user <> 0 and rev_user_text<>'Mediawiki Default' and rev_user_text<>'MLB Stats Bot' ORDER BY rev_user_text ASC LIMIT 0,6";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				$editors[] = array( "user_id" => $row->rev_user, "user_name" => $row->rev_user_text);
			}
			$wgMemc->set( $key, $editors, 60 * 5 );
		} else {
			wfDebug( "loading recent editors for page {$page_title_id} from cache\n" );
			$editors = $data;
		}

		if (count($editors)>0) {
		
			$footer .= "<div id=\"footer\">
				<div id=\"footer-actions\">
					<h2>Contribute</h2>
					<p>
						ArmchairGM's pages can be edited.<br/>
						Is this page incomplete?  Is there anything wrong?<br/>
						<b>Change it!</b>
					</p>
					<a href=\"".$title->escapeFullURL('action=edit')."\" rel=\"nofollow\" class=\"edit-action\">Edit this page</a>
					<a href=\"".$title->escapeFullURL('action=edit')."\" rel=\"nofollow\" class=\"discuss-action\">Discuss this page</a>
					<a href=\"".$title->escapeFullURL('action=history')."\" rel=\"nofollow\" class=\"page-history-action\">Page history</a>";
				$footer.="</div>
				<div id=\"footer-contributors\">
					<h2>Recent contributors to this page</h2>
					<p>
						The following people recently contributed to this article.
					</p>";

					foreach($editors as $editor) {
						$avatar = new wAvatar($editor["user_id"],"m");
						$user_title = Title::makeTitle(NS_USER,$editor["user_name"]);

						$footer .= "<a href=\"{$user_title->escapeFullURL()}\" rel=\"nofollow\">
							<img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\"/>
						</a>";

					}

				$footer .= "</div>
				<div id=\"footer-widget\">
				<h2>Embed this on your site</h2><p><input type='text' size='40' onclick='this.select();' value='" . '<object width="300" height="450" id="content_widget" align="middle"> <param name="movie" value="content_widget.swf" /><embed src="' . $wgServer . '/extensions/wikia/ContentWidget/widget.swf?page=' . urlencode($title->getFullText()) . '" quality="high" bgcolor="#ffffff" width="300" height="450" name="content_widget"type="application/x-shockwave-flash" /> </object>' . "' />";
				$footer .= "</p></div>
				<div class=\"cleared\"></div>
			</div>";
			
		}

		
		
	}
		$footer .= '
<style>
#spotlight_footer{font-size:x-small;margin-bottom:20px;overflow:hidden;}#spotlight_footer table{width:100%;}#spotlight_footer table td{padding:5px;text-align:center;}
</style>
<div id="spotlight_footer"><table><tr>
<td><div style="width: 200px; height: 75px;margin:0 auto;"><script type="text/javascript">GA_googleFillSlot("FOOTER_SPOTLIGHT_LEFT")</script></div></td>
<td><div style="width: 200px; height: 75px;margin:0 auto;"><script type="text/javascript">GA_googleFillSlot("FOOTER_SPOTLIGHT_MIDDLE")</script></div>
<td><div style="width: 200px; height: 75px;margin:0 auto;"><script type="text/javascript">GA_googleFillSlot("FOOTER_SPOTLIGHT_RIGHT")</script></div></td></tr></table></div>';

	$footer .= "<div id=\"footer-bottom\">
		<a href=\"{$main_page->escapeLocalURL()}\" rel=\"nofollow\">Main Page</a>
		<a href=\"{$about->escapeLocalURL()}\" rel=\"nofollow\">About</a>
		<a href=\"{$special->escapeLocalURL()}\" rel=\"nofollow\">Special Pages</a>
		<a href=\"{$help->escapeLocalURL()}\" rel=\"nofollow\">Help</a>
		<a href=\"http://www.wikia.com/wiki/Terms_of_use\" rel=\"nofollow\">Terms of Use</a>
		<a href=\"http://www.federatedmedia.net/authors/ArmchairGM\" rel=\"nofollow\">Advertise</a>
	</div>";
	


	return $footer;
}

function navigationBar() {
	
	global $wgTitle, $wgUser;
	
	//Menu Definition
	$menu[] = array(
			"display_name" => "Main Page",
			"page_name" =>"Main Page"
			);

	$menu[] = array(
			"display_name" => "Profile",
			"page_name" => "User:{$wgUser->getName()}"
	);
			
	$menu[] = array(
			"display_name" => "Sports",
			"page_name" => "",
			"subpages" =>
				 array("MLB" => "MLB","NFL" => "NFL","NBA" => "NBA","NHL" => "NHL",
					 "College Basketball"  => "College Basketball","College Football" => "College Football",
					 "Soccer" => "Soccer","Nascar"  => "Nascar","Other"  => "Other")
			);
	
	$menu[] = array(
			"display_name" => "Write",
			"page_name" => "Create Article",
			"subpages" =>
				 array("Article" => "Create Article","Locker Room Discussion"=> "Create Locker Room Discussion")
	);
			
	$menu[] = array(
			"display_name" => "Articles",
			"page_name" => "Special:ArticlesHome",
			"subpages" =>
				 array("All Articles" => "Special:ArticlesHome","New Articles" => "Special:ArticlesHome/New", "Today's Articles" => "Category:".date("F j, Y"))
	);		
			
			
	$menu[] = array(
			"display_name" => "Hot Links",
				"page_name" => "Special:LinksHome",
				"subpages" =>
					 array("Submit a Link" => "Special:LinkSubmit","Approve Links" => "Special:LinkApprove")
			);
					
	$menu[] = array(
			"display_name" => "Images",
			"page_name" => "Special:ImageRating"
	);
	
					
	$menu[] = array(
			"display_name" => "Locker Room",
			"page_name" => "Category:Lockerroom"
	);
	
	$menu[] = array(
			"display_name" => "Meet People",
			"page_name" => "Special:SimilarFans"
	);
	
	$menu[] = array(
			"display_name" => "Fun",
			"page_name" => "",			
			"subpages" =>
			array("Picture Game" => "Special:PictureGameHome", "Ratings" => "Ratings", "Polls" => "Special:RandomPoll", "Pick Game" => "Special:PickGame", "Quiz Game" => "Special:QuizGameHome", "Spring Silliness" => "Special:SpringSillinessHome" )
	);
		
	$menu[] = array(
			"display_name" => "Explore",
			"page_name" => "",
			"subpages" =>
			array("Random Page" => "Special:Random", "Random Image" => "Special:Random/Image", "Random Fan" => "Special:Random/User" )
	);
	
	$current_page = $wgTitle->getPrefixedText();
	$parts = explode( '/', $_SERVER['QUERY_STRING'] );
	if( count( $parts ) > 1 )$current_page.="/" . $parts[ count( $parts ) - 1 ];
	
	//Main Menu Bar
	$tab_count = 1;
	$navigation = "<div id=\"tabs\">";

		foreach($menu as $menu_item){
			if ( !isset($menu_item["subpages"]) ) $menu_item["subpages"] = array();

			if($current_page==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($current_page,$menu_item["subpages"])) ){
				$menu_class = "tab-on";
			}else{
				$menu_class = "tab-off";
			}
			//If No page_name is passed, the link needs to load the submenu via JS
			//otherwise, we contruct the MW page_title to get the proper URL
			if(!$menu_item["page_name"]){
				$menu_link = "javascript:void(0);\" onclick=\"javascript:submenu({$tab_count});";
			}else{
				$page_title = Title::makeTitle( NS_MAIN  , $menu_item["page_name"]  );
				$menu_link = $page_title->escapeFullURL();
			}

			if (!( (($menu_item["display_name"] == "Profile") || ($menu_item["display_name"] == "Locker Room")) && !($wgUser->isLoggedIn()))) {
				$navigation .= "<div class=\"{$menu_class}\" id=\"menu-{$tab_count}\"><a href=\"{$menu_link}\"><span>{$menu_item["display_name"]}</span></a></div>";
			}
			$tab_count++;
		}

		$navigation .= "<div class=\"cleared\"></div>
	</div>
	
	<div id=\"navigation\">
		
		<div id=\"sub-menu-bar\">";
			//Sub Menu Bar
			$tab_count = 1;
			
			foreach($menu as $menu_item){
				
				if( isset($menu_item["subpages"]) ) {

					//If you are on this page, or any of its subpages, the submenu should be visible on load
					if ($current_page==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($current_page,$menu_item["subpages"])) ){
						$menu_class = "display:block;";
						$navigation .= "<script>last_clicked={$tab_count};</script>";
					} else {
						$menu_class = "display:none";
					}

					$navigation .= "<div class=\"sub-menu\" style=\"{$menu_class}\" id=\"submenu-{$tab_count}\">";

					//Output each subpage link
					$x = 1;
					foreach($menu_item["subpages"] as $subpage_display_name => $subpage_page_name){

						if($current_page==$subpage_page_name){
							$sub_menu_class = "sub-menu-on";
						}else{
							$sub_menu_class = "";
						}

						//construct sub menu link
						$page_title = Title::makeTitle( NS_MAIN  , $subpage_page_name  );
						$menu_link = $page_title->escapeFullURL();

						if($x > 1)$navigation .= " - ";
						$navigation .= "<span class=\"{$sub_menu_class}\"><a href=\"{$menu_link}\" rel=\"nofollow\">{$subpage_display_name}</a></span>";
						$x++;
					}
					$navigation .= "</div>";
				}
				$tab_count++;
			}
			$navigation .= $this->searchForm();
		$navigation .= "</div>";
	
	return $navigation;
}
  
function doBeforeContent() {

	global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP, $wgMemc, $wgUploadPath;	 
     
	$output = "";
	
	//safe titles
	$main_page = Title::makeTitle(NS_MAIN,"Main Page");
	$invite = Title::makeTitle(NS_SPECIAL,"InviteEmail");
	$site_scout = Title::makeTitle(NS_SPECIAL,"SiteScout");
	$register = Title::makeTitle(NS_SPECIAL,"UserRegister");
	$logout = Title::makeTitle(NS_SPECIAL,"Userlogout");
	$login = Title::makeTitle(NS_SPECIAL,"Login");
	$recent_changes = Title::makeTitle(NS_SPECIAL,"Recentchanges");
	$top_fans = Title::makeTitle(NS_SPECIAL,"TopFans");
	$help = $help = Title::makeTitle(NS_MAIN,"UserRegister");
	
	$output .= "<div id=\"container\">
		<div id=\"top-ad\">
			<!-- FM Leaderboard Zone -->\n";

			/*
			<script type='text/javascript'>\n
			var federated_media_section = '';\n
			</script>\n
			<script type='text/javascript' src='http://static.fmpub.net/zone/791'></script>\n
			*/

			/*
$output .= "<script type='text/javascript'><!--//<![CDATA[
		   var m3_u = (location.protocol=='https:'?'https://wikia-ads.wikia.com/www/delivery/ajs.php':'http://wikia-ads.wikia.com/www/delivery/ajs.php');
		   var m3_r = Math.floor(Math.random()*99999999999);
		   if (!document.MAX_used) document.MAX_used = ',';
		   document.write (\"<scr\"+\"ipt type='text/javascript' src='\"+m3_u);
		   document.write (\"?zoneid=356\");
		   document.write ('&amp;cb=' + m3_r);
		   if (document.MAX_used!=',') document.write (\"&amp;exclude=\" + document.MAX_used);
		   document.write (\"&amp;loc=\" + escape(window.location));
		   if (document.referrer) document.write (\"&amp;referer=\" + escape(document.referrer));
		   if (document.context) document.write (\"&context=\" + escape(document.context));
		   if (document.mmm_fo) document.write (\"&amp;mmm_fo=1\");
		   document.write (\"'><\/scr\"+\"ipt>\");
		//]]>--></script>";
			*/

			$output .= "<!-- FM Leaderboard Zone -->\n
		</div>
		<div id=\"navigation\">
			<div class=\"navigation-top\">
				<div class=\"logo\">
					<a href=\"".$main_page->escapeFullURL()."\"><img src=\"{$wgUploadPath}/sports/logo.png\" alt=\"armchairgm\" border=\"0\"/></a>
				</div>
				<div class=\"tag-line\">
					all sports, all you
				</div>
				<div class=\"add-friends\">
					<a href=\"".(($wgUser->isLoggedIn())?$invite->escapeLocalURL():$register->escapeFullURL())."\" rel=\"nofollow\">
						+ Add Friends
					</a>
				</div>";
				if ($wgUser->isLoggedIn()) {
					$output .= "<div class=\"navigation-links\">
						<a href=\"{$site_scout->escapeFullURL()}\" rel=\"nofollow\">Site Scout</a> - 
						<a href=\"{$recent_changes->escapeFullURL()}\"  rel=\"nofollow\">Recent Changes</a> - 
						<a href=\"{$top_fans->escapeFullURL()}\" rel=\"nofollow\">Top Fans</a> -
						<a href=\"{$help->escapeFullURL()}\" rel=\"nofollow\">Help</a> -
						<a href=\"{$logout->escapeFullURL()}\" rel=\"nofollow\">Log Out</a>
					</div>";
				} else {
					$output .= "<div class=\"navigation-links logout-fix\">
						You are not logged-in.<br/>
						<a href=\"{$register->escapeFullURL()}\" rel=\"nofollow\">Sign Up</a> - <a href=\"{$login->escapeFullURL()}\" rel=\"nofollow\">Log In</a>
					</div>";
				}
				$output .= "<div class=\"cleared\"></div>
			</div>";		
			$output .= "{$this->navigationBar()}
		</div>
		
		<div id=\"main\">";
	
		//edit button
		global $wgNameSpacesWithEditMenu;
		
		if ($wgTitle->isTalkPage() || ( in_array($wgTitle->getNamespace(), $wgNameSpacesWithEditMenu) && ($wgTitle->getText()!="Main Page") )) {
			$output .= $this->editMenu();
		}
		
		if($wgTitle->getText()!="Main Page")$output.=$this->pageTitle();

  return $output;
  
}
 
 function doAfterContent() {
 
 	global $wgOut, $wgUser, $wgTitle, $wgSupressPageCategories;
  
	if( !$wgSupressPageCategories ){
		//get categories
		$cat=$this->getCategoryLinks();
		if($cat){
			$output.="<div id=\"categories\">
				$cat
			</div>";
		}
	}

  	$output = "</div>
	{$this->footer()}
  </div>";
   
  return $output;
 }
 

 
   function searchForm( $label = "" ) {
    global $wgRequest, $wgUploadPath;
  
    $search = $wgRequest->getText( 'search' );
    $action = $this->escapeSearchLink();
  
    $s = "<form method=\"get\" action=\"$action\" name=\"search_form\">";
  
    if ( "" != $label ) { $s .= "{$label}: "; }
	$s .= "<div class=\"search-form\">
		<div class=\"search-input\"><input type='text' name=\"search\" size='20' value=\"Players, Teams, Sports\" onclick=\"this.value=''\"/></div>
		<div class=\"search-button\" onclick=\"document.search_form.submit()\"></div>
	</div>";
    $s .= "</form>";
  
	//<input type='image' src='{$wgUploadPath}/common/search.png' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" onclick=\"document.search-form.submit()\"/>

    return $s;
  }
  
  
	
	
 
}

?>
