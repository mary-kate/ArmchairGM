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




class SkinHalodave extends Skin {
	
	private $navmenu;
  
	#set stylesheet
	function getStylesheet() {
		return "common/Halo.css";
	}
  
	#set skinname
	function getSkinName() {
		return "Halodave";
	}
  
	function getHeadScripts() {
		global $wgStylePath, $wgUser, $wgAllowUserJs, $wgJsMimeType, $wgStyleVersion, $wgServer;

		$r = self::makeGlobalVariablesScript( array( 'skinname' => $this->getSkinName() ) );

		$r .= "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/wikibits.js?$wgStyleVersion\"></script>\n";
		$r .= "<script type=\"{$wgJsMimeType}\" src=\"{$wgServer}/extensions/wikia/onejstorule.js?{$wgStyleVersion}\"></script>\n";
		//$r .= "<script type=\"{$wgJsMimeType}\" src=\"{$wgServer}/extensions/wikia/container_core-min.js?{$wgStyleVersion}\"></script>\n";
		//$r .= "<script type=\"{$wgJsMimeType}\" src=\"{$wgServer}/extensions/wikia/menu-min.js?{$wgStyleVersion}\"></script>\n";
		
		global $wgUseSiteJs;
		if ($wgUseSiteJs) {
			if ($wgUser->isLoggedIn()) {
				$r .= "<script type=\"$wgJsMimeType\" src=\"".htmlspecialchars(self::makeUrl('-','action=raw&smaxage=0&gen=js'))."\"><!-- site js --></script>\n";
			} else {
				$r .= "<script type=\"$wgJsMimeType\" src=\"".htmlspecialchars(self::makeUrl('-','action=raw&gen=js'))."\"><!-- site js --></script>\n";
			}
		}
		if( $wgAllowUserJs && $wgUser->isLoggedIn() ) {
			$userpage = $wgUser->getUserPage();
			$userjs = htmlspecialchars( self::makeUrl(
				$userpage->getPrefixedText().'/'.$this->getSkinName().'.js',
				'action=raw&ctype='.$wgJsMimeType));
			$r .= '<script type="'.$wgJsMimeType.'" src="'.$userjs."\"></script>\n";
		}
		return $r;
	}
	
	function searchForm( $label = "" ) {
	   
	 	global $wgRequest, $wgUploadPath;

	    $search = $wgRequest->getText( 'search' );
	    $action = $this->escapeSearchLink();

	    $search = "<form method=\"get\" action=\"$action\" name=\"search_form\">";

	    if ( "" != $label ) { $s .= "{$label}: "; }
		$search .= "<input type=\"text\" class=\"search-field\" name=\"search\" value=\"enter search\" onclick=\"this.value=''\"/>
		<input type=\"image\" src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/search_button.gif\" class=\"search-button\" value=\"go\"/>";
	    $search .= "</form>
		<div class=\"cleared\"></div>";

	    return $search;
	
	  }
	  
	  /**
	 * Parse MediaWiki-style messages called 'v3sidebar' to array of links, saving
	 * hierarchy structure.
	 * Message parsing is limited to first 150 lines only.
	 *
	 * @author Inez Korczynski <inez@wikia.com>
	 */
	private function getNavigationMenu() {
		$message_key = 'sidebar';
		$message = trim(wfMsg($message_key));

		if(wfEmptyMsg($message_key, $message)) {
			return array();
		}

		$lines = array_slice(explode("\n", $message), 0, 150);

		if(count($lines) == 0) {
			return array();
		}

		$nodes = array();
		$nodes[] = array();
		$lastDepth = 0;
		$i = 0;
		foreach($lines as $line) {

			$node = $this->parseItem($line);
			$node['depth'] = strrpos($line, '*') + 1;

			if($node['depth'] == $lastDepth) {
				$node['parentIndex'] = $nodes[$i]['parentIndex'];
			} else if ($node['depth'] == $lastDepth + 1) {
				$node['parentIndex'] = $i;
			} else {
				for($x = $i; $x >= 0; $x--) {
					if($x == 0) {
						$node['parentIndex'] = 0;
						break;
					}
					if($nodes[$x]['depth'] == $node['depth'] - 1) {
						$node['parentIndex'] = $x;
						break;
					}
				}
			}

			$nodes[$i+1] = $node;
			$nodes[$node['parentIndex']]['children'][] = $i+1;
			$lastDepth = $node['depth'];
			$i++;
		}
		return $nodes;
	}
	
	/**
	 * Parse one line form MediaWiki-style message as array of 'text' and 'href'
	 *
	 * @author Inez Korczynski <inez@wikia.com>
	 */
	private function parseItem($line) {
		$line_temp = explode('|', trim($line, '* '), 2);
		if(count($line_temp) > 1) {
			$line = $line_temp[1];
			$link = wfMsgForContent( $line_temp[0] );
		} else {
			$line = $line_temp[0];
			$link = $line_temp[0];
		}

		if (wfEmptyMsg($line, $text = wfMsg($line))) {
			$text = $line;
		}

		if($link != null) {
			if (wfEmptyMsg($line_temp[0], $link)) {
				$link = $line_temp[0];
			}
			if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
				$href = $link;
			} else {
				$title = Title::newFromText( $link );
				if($title) {
					$title = $title->fixSpecialName();
					$href = $title->getLocalURL();
				} else {
					$href = 'INVALID-TITLE';
				}
			}
		}
		return array('text' => $text, 'href' => $href);
	}
	
	private function printMenu($id, $last_count='', $level=0) {
		$menu_output = "";
		$script_output = "";
		$count = 1;
		if(isset($this->navmenu[$id]['children'])) {
			$script_output .= '<script type="text/javascript">';
			if ($level) {
				$menu_output .= '<div class="sub-menu" id="sub-menu' . $last_count . '" style="display:none;" >';
				$script_output .= 'submenu_array["sub-menu' . $last_count . '"] = "' . $last_count . '";';
				$script_output .= '$("sub-menu' . $last_count . '").onmouseout = clearMenu;if ($("sub-menu' . $last_count . '").captureEvents) $("sub-menu' . $last_count . '").captureEvents(Event.MOUSEOUT);';
			}
			foreach($this->navmenu[$id]['children'] as $child) {
				
				$mouseover = ' onmouseover="' . ($level ? 'sub_' : '') . 'menuItemAction(\'' . ($level ? $last_count . '_' : '_') .$count . '\');"';
				$mouseout = ' onmouseout="clearBackground(\'_' . $count . '\')"';
				$menu_output .='<div class="' . ($level ? 'sub-' : '') . 'menu-item' . (($id == $this->navmenu[$child]['depth']) ? ' border-fix' : '') . '" id="menu-item' . ($level ? $last_count . '_' : '_') .$count . '">';
				$menu_output .= '<a style="display:block;" id="' . ($level ? 'sub-' : '') . 'menu-item' . ($level ? $last_count . '_' : '_') .$count . '" href="'.(!empty($this->navmenu[$child]['href']) ? htmlspecialchars($this->navmenu[$child]['href']) : '#').'">';
				if(!$level) {
					$script_output .= 'menuitem_array["menu-item' . $last_count . '_' .$count .'"] = "' . $last_count . '_' .$count . '";';
					$script_output .= '$("menu-item' . $last_count . '_' .$count .'").onmouseover = menuItemAction;if ($("menu-item' . $last_count . '_' .$count .'").captureEvents) $("menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';
					$script_output .= '$("menu-item' . $last_count . '_' .$count .'").onmouseout = clearBackground;if ($("menu-item' . $last_count . '_' .$count .'").captureEvents) $("menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOUT);';
				}
				else {
					$script_output .= 'submenuitem_array["sub-menu-item' . $last_count . '_' .$count .'"] = "' . $last_count . '_' .$count . '";';
					$script_output .= '$("sub-menu-item' . $last_count . '_' .$count .'").onmouseover = sub_menuItemAction;if ($("sub-menu-item' . $last_count . '_' .$count .'").captureEvents) $("sub-menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';
				}
				$menu_output .= $this->navmenu[$child]['text'];
				$menu_output .= '</a>';
				if (sizeof($this->navmenu[$child]['children'])) {
					$menu_output .= '<div class="sub-menu-button"><img src="http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif" alt="" border="0" /></div>';
				}
				$menu_output .= $this->printMenu($child, $last_count . '_' . $count, $level+1);
				$menu_output .= '</div>';
				$count++;
			}
			if ($level) {
				$menu_output .= '</div>';
			}
			$script_output .= '</script>';
		}
		
		$output .= "<div id=\"menu\">";
			$output .= $menu_output . $script_output;
		$output .= "</div>";
		
		return $output;
	}
	
	function isContent(){
		global $wgTitle;
		return ($wgTitle->getNamespace() != NS_SPECIAL );
	}
	
	function tabAction( $title, $message, $selected, $query='', $checkEdit=false ) {
		$classes = array();
		if( $selected ) {
			$classes[] = 'selected';
		}	
		if( $checkEdit && $title->getArticleId() == 0 ) {
			$query = 'action=edit';
		}

		$text = wfMsg( $message );
		if ( wfEmptyMsg( $message, $text ) ) {
			global $wgContLang;
			$text = $wgContLang->getFormattedNsText( Namespace::getSubject( $title->getNamespace() ) );
		}

		return array(
			'class' => implode( ' ', $classes ),
			'text' => $text,
			'href' => $title->getLocalUrl( $query ) );
	}
	
	function buildActionBar(){
		global $wgRequest, $wgTitle, $wgOut, $wgUser;
		
		$action = $wgRequest->getText( 'action' );
		$section = $wgRequest->getText( 'section' );
		$content_actions = array();
		
		if( $this->isContent()) {
			$subjpage = $wgTitle->getSubjectPage();
			$talkpage = $wgTitle->getTalkPage();
			$nskey = $wgTitle->getNamespaceKey();
			
			$content_actions[$nskey] = $this->tabAction(
				$subjpage,
				$nskey,
				!$wgTitle->isTalkPage() && !$prevent_active_tabs,
				'', true);
	
			$content_actions['talk'] = $this->tabAction(
				$talkpage,
				'talk',
				$wgTitle->isTalkPage() && !$prevent_active_tabs,
				'',
				true);
			
			if ( $wgTitle->quickUserCan( 'edit' ) && ( $wgTitle->exists() || $wgTitle->quickUserCan( 'create' ) ) ) {
				$istalk = $wgTitle->isTalkPage();
				$istalkclass = $istalk?' istalk':'';
				$content_actions['edit'] = array(
					'class' => ((($action == 'edit' or $action == 'submit') and $section != 'new') ? 'selected' : '').$istalkclass,
					'text' => wfMsg('edit'),
					'href' => $wgTitle->getLocalUrl( $this->editUrlOptions() )
				);
	
				if ( $istalk || $wgOut->showNewSectionLink() ) {
					$content_actions['addsection'] = array(
						'class' => $section == 'new'?'selected':false,
						'text' => wfMsg('addsection'),
						'href' => $wgTitle->getLocalUrl( 'action=edit&section=new' )
					);
				}
			} else {
				$content_actions['viewsource'] = array(
					'class' => ($action == 'edit') ? 'selected' : false,
					'text' => wfMsg('viewsource'),
					'href' => $wgTitle->getLocalUrl( $this->editUrlOptions() )
				);
			}
			
			if ( $wgTitle->getArticleId() ) {
	
				$content_actions['history'] = array(
					'class' => ($action == 'history') ? 'selected' : false,
					'text' => wfMsg('history_short'),
					'href' => $wgTitle->getLocalUrl( 'action=history')
				);
	
				if ( $wgTitle->getNamespace() !== NS_MEDIAWIKI && $wgUser->isAllowed( 'protect' ) ) {
					if(!$wgTitle->isProtected()){
						$content_actions['protect'] = array(
							'class' => ($action == 'protect') ? 'selected' : false,
							'text' => wfMsg('protect'),
							'href' => $wgTitle->getLocalUrl( 'action=protect' )
						);
	
					} else {
						$content_actions['unprotect'] = array(
							'class' => ($action == 'unprotect') ? 'selected' : false,
							'text' => wfMsg('unprotect'),
							'href' => $wgTitle->getLocalUrl( 'action=unprotect' )
						);
					}
				}
				if($wgUser->isAllowed('delete')){
					$content_actions['delete'] = array(
						'class' => ($action == 'delete') ? 'selected' : false,
						'text' => wfMsg('delete'),
						'href' => $wgTitle->getLocalUrl( 'action=delete' )
					);
				}
				if ( $wgTitle->quickUserCan( 'move' ) ) {
					$moveTitle = SpecialPage::getTitleFor( 'Movepage', $this->thispage );
					$content_actions['move'] = array(
						'class' => $wgTitle->isSpecial( 'Movepage' ) ? 'selected' : false,
						'text' => wfMsg('move'),
						'href' => $moveTitle->getLocalUrl()
					);
				}
			} else {
				//article doesn't exist or is deleted
				if( $wgUser->isAllowed( 'delete' ) ) {
					if( $n = $wgTitle->isDeleted() ) {
						$undelTitle = SpecialPage::getTitleFor( 'Undelete' );
						$content_actions['undelete'] = array(
							'class' => false,
							'text' => wfMsgExt( 'undelete_short', array( 'parsemag' ), $n ),
							'href' => $undelTitle->getLocalUrl( 'target=' . urlencode( $this->thispage ) )
							#'href' => self::makeSpecialUrl( "Undelete/$this->thispage" )
						);
					}
				}
			}
		}else{
			/* show special page tab */
			$content_actions[$wgTitle->getNamespaceKey()] = array(
				'class' => 'selected',
				'text' => wfMsg('nstab-special'),
				'href' => $wgRequest->getRequestURL(), // @bug 2457, 2510
			);
		}
		
		return $content_actions;
	}
	
	function getActionBarLinks() {
		global $wgTitle;
		
		$left = array($wgTitle->getNamespaceKey(), "edit","talk","viewsource","addsection","history");
		$actions = $this->buildActionBar();
		
		foreach($actions as $action => $value){
			if ( in_array( $action, $left ) ){
				$leftLinks[$action] = $value;
			}else{
				$moreLinks[$action] = $value;
			}
		}
	
		return array( $leftLinks, $moreLinks );
	}	
	
	function actionBar() {
	
		global $wgUser, $wgTitle;
		
		$full_title = Title::makeTitle( $wgTitle->getNameSpace(), $wgTitle->getText() );
		
		$output .= "<div id=\"action-bar\">";
			if ($wgUser->isLoggedIn() && $this->isContent() ) {
	
				$output .= "<div id=\"article-controls\">
					<img src=\"http://alpha.search.wikia.com/images/wikia/plus.gif\" alt=\"\" border=\"0\">";
	
					if (!$wgTitle->userIsWatching()) {
						$output .= "<a href=\"".$full_title->escapeFullURL('action=watch')."\">
							".wfMsg('watch')."
						</a>";
					} else {
						$output .= "<a href=\"".$full_title->escapeFullURL('action=unwatch')."\">
							".wfMsg('unwatch')."
						</a>";
					}
				$output .= "</div>";
			}
			$output .= "<div id=\"article-tabs\">";
				
				list( $leftLinks, $moreLinks ) = $this->getActionBarLinks();
				
				foreach ($leftLinks as $key => $val) {
						$output .= "<a href=\"".htmlspecialchars($val['href'])."\" class=\"".(($val['class']!="selected")?"tab-off":"tab-on")."\" rel=\"nofollow\">
							<span>" . ucfirst($val['text']) . "</span>
						</a>";
				}
				
				if (count($moreLinks)>0) {
					
					$output .=  "<script>
						var _shown = false;
						var _hide_timer;
						function show_actions(el, type) {
	
							if (type==\"show\") {
								clearTimeout(_hide_timer);
								if (!_shown) {
									//\$('more-arrow').src = 'http://alpha.search.wikia.com/images/wikia/down_arrow_on.gif';
								
									\$D.replaceClass('more-tab','more-tab-off','more-tab-on');
									YAHOO.widget.Effects.Show(\$(el));
									_shown = true;
								}
							} else {
								//\$('more-arrow').src = 'http://alpha.search.wikia.com/images/wikia/down_arrow_off.gif';
								\$D.replaceClass('more-tab','more-tab-on','more-tab-off');
				
								YAHOO.widget.Effects.Hide(\$(el));
								_shown = false;
							}
	
						}
						
						function delay_hide(el) {
							_hide_timer = setTimeout (function() {show_actions(el, 'hide');}, 500);
						}
	
					
					
					</script>
					
	
					<div class=\"more-tab-off\" id=\"more-tab\" onmouseover=\"show_actions('article-more-container', 'show');\" onmouseout=\"delay_hide('article-more-container');\">
						<span>More Actions <img src=\"http://alpha.search.wikia.com/images/wikia/down_arrow_off.gif\" id=\"more-arrow\" alt=\"\" border=\"0\"/></span>";
					
						$output .= "<div class=\"article-more-actions\" id=\"article-more-container\" style=\"display:none\" onmouseover=\"clearTimeout(_hide_timer);\" onmouseout=\"show_actions('article-more-container', 'hide');\">";
						
						$more_links_count = 1;
						
						foreach ($moreLinks as $key => $val) {
							
							if (count($moreLinks)==$more_links_count) {
								$border_fix = "class=\"border-fix\"";
							} else {
								$border_fix = "";
							}
							
							$output .= "<a href=\"".htmlspecialchars( $val['href'] )."\" {$border_fix} rel=\"nofollow\">
								".ucfirst($val['text'])."
							</a>";
							
							$more_links_count++;
						}
					$output .= "</div>
					</div>";
				}
			
				$output .= 	"<div class=\"cleared\"></div>
			</div>
		</div>";
		
		return $output;
		
	}

	#main page before wiki content
	function doBeforeContent() {
	
  		##global variables
  		global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP, $wgMemc;	
		

		$output .= "<div id=\"container\">
			<div id=\"wikia-header\">
				<div id=\"wikia-logo\">
					<a href=\"http://www.wikia.com\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/wikia_logo.gif\" alt=\"\" border=\"0\"/></a> 
					<span id=\"wikia-category\">Gaming</span>
				</div>
				<div id=\"wikia-more-category\">
					<a class=\"positive-button\"><span>More Gaming</span></a>
				</div>
				<div id=\"wikia-more-menu\" style=\"display:none\">
					<a href=\"#\">EQ2i</a>
					<a href=\"#\">Egamia</a>
					<a href=\"#\">City of Heroes</a>
					<a href=\"#\">The Witcher</a>
					<a href=\"#\">Mass Effect</a>
					<a href=\"#\">Wow Wiki</a>
					<a href=\"#\">Halopedia</a>
				</div>";
				
				//login safe title
				$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
				$login_link = Title::makeTitle(NS_SPECIAL, "UserLogin");
				$logout_link = Title::makeTitle(NS_SPECIAL, "Userlogout");
				$profile_link = Title::makeTitle(NS_USER, $wgUser->getName());
				
				$output .= "<div id=\"wikia-login\">";
				
					if ($wgUser->isLoggedIn()) {
						$output .= "<div id=\"login-message\">
							Welcome <b>{$wgUser->getName()}</b>
						</div> 
						<a class=\"positive-button\" href=\"".$profile_link->escapeFullURL()."\" rel=\"nofollow\"><span>Profile</span></a> 
						<a class=\"negative-button\" href=\"".$logout_link->escapeFullURL()."\"><span>Log Out?</span></a>";
					} else {
						$output .= "<a class=\"positive-button\" href=\"".$register_link->escapeFullURL()."\" rel=\"nofollow\"><span>Sign Up</span></a>
						<a class=\"positive-button\" href=\"".$login_link->escapeFullURL()."\"><span>Login</span></a>";
					}
					
				
				$output .= "</div>
			</div>
			<div id=\"site-header\">
				<div id=\"site-logo\">
					<img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/site_logo.gif\" border=\"0\" alt=\"\"/>
				</div>
				<div id=\"top-ad\">
					<img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/ad_unit.png\" border=\"0\" alt=\"\"/>
				</div>
			</div>";

	
			$output .= "<div id=\"side-bar\">";
			 
				
				
				$random_page_link = Title::makeTitle(NS_SPECIAL, "RandomPage");
				$recent_changes_link = Title::makeTitle(NS_SPECIAL, "Recentchanges");
				$top_fans_link = Title::makeTitle(NS_SPECIAL, "TopFans");
				$special_pages_link = Title::makeTitle(NS_SPECIAL, "Specialpages");
				$help_link = Title::makeTitle(NS_HELP, "Contents");
				$images_link = Title::makeTitle(NS_SPECIAL, "ImageRating");
				$articles_home_link = Title::makeTitle(NS_SPECIAL, "ArticlesHome");
				$main_page_link = Title::makeTitle(NS_MAIN, "Main Page");
				$site_scout_link = Title::makeTitle(NS_SPECIAL, "SiteScout");
				$move = Title::makeTitle(NS_SPECIAL,"Movepage");
				$upload_file = Title::makeTitle(NS_SPECIAL,"Upload");
				$what_links_here = Title::makeTitle(NS_SPECIAL,"Whatlinkshere");
				$full_title = Title::makeTitle( $wgTitle->getNameSpace(), $wgTitle->getText() );
				$main_title = Title::makeTitle( NS_MAIN, $wgTitle->getText() );
				$preferences_link = Title::makeTitle (NS_SPECIAL, "Preferences");
				$watchlist_link = Title::makeTitle (NS_SPECIAL, "Watchlist");
				
				
				$output .= "
				
				<div id=\"navigation\">
					<div id=\"navigation-title\">
						Navigation
					</div>";
					$output .= '<script type="text/javascript">var submenu_array = new Array();var menuitem_array = new Array();var submenuitem_array = new Array();</script>';
					$this->navmenu = $this->getNavigationMenu();
					$output .= $this->printMenu(0);
					
					/*
					<div id=\"menu\">
						<div class=\"menu-item\" id=\"menu-item-1\" onmouseover=\"menuItemAction(1, false)\" onmouseout=\"clearBackground(1)\">
							<a href=\"".$main_page_link->escapeFullURL()."\">Main Page</a>
						</div>
						<div class=\"menu-item\" id=\"menu-item-2\" onmouseover=\"menuItemAction(2, false)\" onmouseout=\"clearBackground(2)\">
							<a href=\"".$site_scout_link->escapeFullURL()."\">Site Scout</a>
						</div>
						<div class=\"menu-item\" id=\"menu-item-3\" onmouseover=\"menuItemAction(3, true)\" onmouseout=\"clearBackground(3)\">
							<a href=\"".$articles_home_link->escapeFullURL()."\">Articles</a>
							<div class=\"menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"/></div>
							<div class=\"sub-menu\" id=\"sub-menu-3\" style=\"display:none\" onmouseout=\"clearMenu(3, 'sub-menu')\">
								<div class=\"sub-menu-item\" onmouseover=\"subMenuItemAction(3, true, 3)\"  onmouseout=\"clearMenu(3, 'sub-sub-menu')\">
									<a href=\"".$articles_home_link->escapeFullURL()."\"onmouseover=\"subMenuItemAction(3, true, 3)\">Popular Articles</a> 
									<div class=\"sub-menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"  onmouseover=\"subMenuItemAction(3, true, 3)\" /></div>
								</div>
								<div class=\"sub-menu-item border-fix\"><a href=\"$articles_home_link->escapeFullURL()/New\">New Articles</a></div>
							</div>
							<div class=\"sub-sub-menu\" id=\"sub-sub-menu-3\" style=\"display:none\" onmouseover=\"subMenuItemAction(3, true, 3)\"  onmouseout=\"clearMenu(3, 'sub-sub-menu')\">
								<div class=\"sub-sub-menu-item\"><a href=\"#\">Most Votes</a></div>
								<div class=\"sub-sub-menu-item\"><a href=\"#\">Most Comments</a></div>
								<div class=\"sub-sub-menu-item border-fix\"><a href=\"#\">Most Comments</a></div>
							</div>
						</div>
						<div class=\"menu-item\" id=\"menu-item-4\" onmouseover=\"menuItemAction(4, false)\" onmouseout=\"clearBackground(4)\">
							<a href=\"".$images_link->escapeFullURL()."\">Images</a>
						</div>
						<div class=\"menu-item\" id=\"menu-item-5\" onmouseover=\"menuItemAction(5, true)\" onmouseout=\"clearBackground(5)\">
							<a href=\"#\">Games</a>
							<div class=\"menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"/></div>
							<div class=\"sub-menu\" id=\"sub-menu-5\" style=\"display:none\" onmouseout=\"clearMenu(5, 'sub-menu')\">
								<div class=\"sub-menu-item\"><a href=\"#\">Halo: Combat Evolved</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo 2</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo 3</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo Wars</a></div>
								<div class=\"sub-menu-item border-fix\"><a href=\"#\">Halo Chronicles</a></div>
							</div>
						</div>
						<div class=\"menu-item\" id=\"menu-item-6\" onmouseover=\"menuItemAction(6, true)\" onmouseout=\"clearBackground(6)\">
							<a href=\"#\">Community</a>
							<div class=\"menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"/></div>
							<div class=\"sub-menu\" id=\"sub-menu-6\" style=\"display:none\" onmouseout=\"clearMenu(6, 'sub-menu')\">
								<div class=\"sub-menu-item\"><a href=\"#\">Halopedia Forums</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halopedia Warz</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Live Chat</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">List of Halopedians</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Project Userbox</a></div>
								<div class=\"sub-menu-item border-fix\"><a href=\"#\">Featured Articles</a></div>
							</div>
						</div>
						<div class=\"menu-item\" id=\"menu-item-7\" onmouseover=\"menuItemAction(7, true)\" onmouseout=\"clearBackground(7)\">
							<a href=\"#\">Related Sites</a>
							<div class=\"menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"/></div>
							<div class=\"sub-menu\" id=\"sub-menu-7\" style=\"display:none\" onmouseout=\"clearMenu(7, 'sub-menu')\">
								<div class=\"sub-menu-item\"><a href=\"#\">Halo Fanon Wikia</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo Machinima Wikia</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo Mods Wikia</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Halo Conflict Wikia</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Red vs. Blue Wikia</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Bungie Wikia</a></div>
								<div class=\"sub-menu-item border-fix\"><a href=\"#\">Marathon Wikia</a></div>
							</div>

						    
						</div>
						<div class=\"menu-item border-fix\" id=\"menu-item-8\" onmouseover=\"menuItemAction(8, true)\" onmouseout=\"clearBackground(8)\">
							<a href=\"".$random_page_link->escapeFullURL()."\">Random</a>
							<div class=\"menu-button\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif\" alt=\"\" border=\"0\"/></div>
							<div class=\"sub-menu\" id=\"sub-menu-8\" style=\"display:none\" onmouseout=\"clearMenu(8, 'sub-menu')\">
								<div class=\"sub-menu-item\"><a href=\"#\">Article</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Image</a></div>
								<div class=\"sub-menu-item\"><a href=\"#\">Video</a></div>
								<div class=\"sub-menu-item border-fix\"><a href=\"#\">Member</a></div>
							</div>
						</div>
					</div>*/
				$output .= "
					<div id=\"other-links-container\">
						<div id=\"other-links\">
							<a href=\"".$top_fans_link->escapeFullURL()."\">Top Users</a>
							<a href=\"".$recent_changes_link->escapeFullURL()."\">Recent Changes</a>
							<div class=\"cleared\"></div>";
							if ($wgUser->isLoggedIn()) {
								$output .= "<a href=\"".$watchlist_link->escapeFullURL()."\">Watchlist</a>
								<a href=\"".$preferences_link->escapeFullURL()."\">Preferences</a>
								<div class=\"cleared\"></div>";
							}
							$output .= "<a href=\"".$help_link->escapeFullURL()."\">Help</a>
							<a href=\"".$special_pages_link->escapeFullURL()."\">Special Pages</a>
							<div class=\"cleared\"></div>
						</div>
					</div>
				</div>";
				
				
				$output .= "<div id=\"search-box\">
					<div id=\"search-title\">
						Search
					</div>
					".$this->searchForm()."
					
				</div>
			</div>
			<div id=\"body-container\">
				<div id=\"body\">";
				
				 
					
			$output .= "
			".$this->actionBar()."
			<div id=\"article\">
				<div id=\"article-text\">
					";
					
					global $wgSupressPageTitle;
					if( !$wgSupressPageTitle ){ 
						$output .= $this->pageTitle();
						$output .= $this->pageSubtitle();
					}
			
			

  		return $output;
  
	}
 
		function pageTitle() {
		global $wgOut;
		$s = '<h1 class="pagetitle">' . htmlspecialchars( $wgOut->getPageTitle() ) . '</h1>';
		return $s;
		
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
		
		$output .= "</div>
		{$this->footer()}
		</div>";
		
		return $output;
	 }
 
	function footer() {
	
		global $IP, $wgUser, $wgTitle, $wgOut,$wgUploadPath, $wgMemc, $wgSitename;
		
		$title = Title::makeTitle($wgTitle->getNamespace(),$wgTitle->getText());
		$page_title_id = $wgTitle->getArticleID();
		$main_page = Title::makeTitle(NS_MAIN,"Main Page");
		$about = Title::makeTitle(NS_MAIN,"About");
		$special = Title::makeTitle(NS_SPECIAL,"Specialpages");
		$help = Title::makeTitle(NS_MAIN,"UserRegister");
		
		$footer_show = array(NS_VIDEO,NS_MAIN,NS_IMAGE);
		
		//edit button
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
							{$wgSitename}'s pages can be edited.<br/>
							Is this page incomplete?  Is there anything wrong?<br/>
							<b>Change it!</b>
						</p>
						<a href=\"".$title->escapeFullURL( $this->editUrlOptions() )."\" rel=\"nofollow\" class=\"edit-action\">Edit this page</a>
						<a href=\"".$title->getTalkPage()->escapeFullURL()."\" rel=\"nofollow\" class=\"discuss-action\">Discuss this page</a>
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
								<img src=\"http://fp029.sjc.wikia-inc.com/images/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\"/>
							</a>";
		
						}
		
					$footer .= "</div>
					<div class=\"cleared\"></div>
				</div>";
				
			}
		}
		
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
	
	function doAfterContentXXX() {
 
		global $wgOut, $wgUser, $wgTitle;
	
						$output .= "</div>
					<div class=\"cleared\"></div>
				</div>
				<div id=\"article-bottom\">
					<div id=\"contribute\">
						<div id=\"contribute-title\">
							Contribute
						</div>
						<div id=\"contribute-message\">
							Halopedia's pages can be edited.
							Is this page incomplete? Is there anything wrong?<br/>
							<b>Change it!</b>
						</div>
						<div id=\"contribute-links\">
							<a href=\"#\">Edit this page</a>
						</div>
					</div>
					<div id=\"rc\">
						<div id=\"rc-title\">
							Recent contributors to this article
						</div>
						<div id=\"rc-message\">
							The following users have recently improved this page.
						</div>
						<div id=\"rc-users\">
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_1.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_2.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_3.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_4.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_1.jpg\" alt=\"\" border=\"0\"/></a>
							<div class=\"cleared\"></div>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_4.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_3.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_1.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_2.jpg\" alt=\"\" border=\"0\"/></a>
							<a href=\"#\"><img src=\"http://fp029.sjc.wikia-inc.com/images/halo/new/avatar_3.jpg\" alt=\"\" border=\"0\"/></a>
						</div>
					</div>
					<div class=\"cleared\"></div>
				</div>
				<div id=\"footer-links\">
					<a href=\"#\">About Wikia</a> 
					<a href=\"#\">Terms of Use</a> 
					<a href=\"#\">Contact Wikia</a> 
					<a href=\"#\">Advertise Here</a> 
					<a href=\"#\">Create a FREE Wiki</a> 
					<a href=\"#\">Developer API</a>
					<a href=\"#\">GFDL</a>
					<a href=\"#\">MediaWiki</a>
					<div id=\"trademark\">
						Wikia&reg; is a registered service mark.
					</div>
				</div>
			</div>
			
			
		</div>
	</div>";
		
  		return $output;
	}

}

?>
