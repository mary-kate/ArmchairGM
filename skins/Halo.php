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




class SkinHalo extends Skin {
	
	private $navmenu;
	private $navmenu_array;
  
	#set stylesheet
	function getStylesheet() {
		return "http://images2.wikia.com/common/wikiany/css/Halo.css";
	}
  
	#set skinname
	function getSkinName() {
		return "Halo";
	}

	# get the user/site-specific stylesheet, SkinTemplate loads via RawPage.php (settings are cached that way)
	function getUserStylesheet() {
		global $wgStylePath, $wgRequest, $wgContLang, $wgSquidMaxage, $wgStyleVersion;
		$sheet = $this->getStylesheet();
		//$s = "@import \"$wgStylePath/common/common.css?$wgStyleVersion\";\n";
		$s .= "@import \"$sheet?$wgStyleVersion\";\n";
		if($wgContLang->isRTL()) $s .= "@import \"$wgStylePath/common/common_rtl.css?$wgStyleVersion\";\n";

		$query = "usemsgcache=yes&action=raw&ctype=text/css&smaxage=$wgSquidMaxage";
		$s .= '@import "' . self::makeNSUrl( 'Common.css', $query, NS_MEDIAWIKI ) . "\";\n" .
			'@import "' . self::makeNSUrl( ucfirst( $this->getSkinName() . '.css' ), $query, NS_MEDIAWIKI ) . "\";\n";

		$s .= $this->doGetUserStyles();
		return $s."\n";
	}

	function bottomScripts() {

			global $wgJsMimeType;
			
			$r = "";
			$r .= "<div id=\"top-ad\">
				{$this->getLeaderboard()}	
			</div>";

			$r .= '<!-- Start for GA_Urchin, page_view -->
                       <script type="text/javascript" src="http://www.google-analytics.com/urchin.js"></script>
                       <script type="text/javascript">_uff=0;_uacct="UA-288915-1";urchinTracker();</script><!-- Wikia Main -->
                       <script type="text/javascript">_uff=0;_uacct="UA-1328449-1";urchinTracker();</script> <!-- New York account -->
                       <script type="text/javascript">_uff=0;_uacct="UA-288915-8";urchinTracker();</script> <!-- Halo -->
                       <!-- Start for GA_Urchin, hub -->
                       <script type="text/javascript">_uff=0;_uacct="UA-288915-2";urchinTracker("/Gaming");</script>
                       <!-- Start for QuantServe, page_view -->
                       <script type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>
                       <script type="text/javascript">_qacct="p-8bG6eLqkH6Avk";</script><script type="text/javascript">quantserve();</script>';
			
			$r .= "\n\t\t<script type=\"$wgJsMimeType\">if (window.runOnloadHook) runOnloadHook();</script>\n";
		
			return $r;
	}
	
	function getUserStyles() {
		$s = "<style type='text/css'>\n";
		$s .= "/*/*/ /*<![CDATA[*/\n"; # <-- Hide the styles from Netscape 4 without hiding them from IE/Mac
		$s .= $this->getUserStylesheet();
		$s .= "/*]]>*/ /* */\n";
		$s .= "</style>\n";
		
		$s .= "<!--[if IE]><style type=\"text/css\" media=\"all\">@import \"http://images2.wikia.com/common/wikiany/css/Halo_IE.css\";</style><![endif]-->\n";
		return $s;
		
	}

	function getHeadScripts() {
		global $wgStylePath, $wgUser, $wgAllowUserJs, $wgJsMimeType, $wgStyleVersion, $wgServer;

		$r = self::makeGlobalVariablesScript( array( 'skinname' => $this->getSkinName() ) );
		$r .= "<script type= \"text/javascript\">\n var wgCityId = \"462\";\n var wgID = 462;\n var wgWikiaAdvertiserCategory = \"GAMI\";\n</script>\n";

		$r .= "<script type=\"{$wgJsMimeType}\" src=\"http://images1.wikia.com/common/wikiany/js/wikibits.js?$wgStyleVersion\"></script>\n";
		$r .= "<script type=\"{$wgJsMimeType}\" src=\"http://images1.wikia.com/common/wikiany/js/onejstorule.js?{$wgStyleVersion}\"></script>\n";
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
	
	function getLeaderboard_taken_out(){
		$output = "<!--/* Openads Javascript Tag v2.4.2 */-->

		<script type='text/javascript'><!--//<![CDATA[
		  var m3_u =
		(location.protocol=='https:'?'https://wikia-ads.wikia.com/www/delivery/ajs.php':'http://wikia-ads.wikia.com/www/delivery/ajs.php');
		  var m3_r = Math.floor(Math.random()*99999999999);
		  if (!document.MAX_used) document.MAX_used = ',';
		  document.write (\"<scr\"+\"ipt type='text/javascript' src='\"+m3_u);
		  document.write (\"?zoneid=488\");
		  document.write ('&amp;cb=' + m3_r);
		  if (document.MAX_used != ',') document.write (\"&amp;exclude=\" +
		document.MAX_used);
		  document.write (\"&amp;loc=\" + escape(window.location));
		  if (document.referrer) document.write (\"&amp;referer=\" +
		escape(document.referrer));
		  if (document.context) document.write (\"&context=\" +
		escape(document.context));
		  if (document.mmm_fo) document.write (\"&amp;mmm_fo=1\");
		  document.write (\"'><\/scr\"+\"ipt>\");
		//]]>--></script>";
		
		return $output;
	}
	
	function getLeaderboard(){
		global $wgTitle;
		if( $wgTitle->getText() == "Main Page" ){
			return "<script src=\"http://ad.doubleclick.net/adj/wka.gaming/_halo/home;s1=_halo;s2=home;pform=Xbox360;gnre=Action;sgnre=FPS;esrb=mature;artid=1;pos=HOME_TOP_LEADERBOARD;dcopt=ist;sz=728x90;tile=1;endtag=\$;ord=123\" type=\"text/javascript\"></script>";
		}else{
			return "<script src=\"http://ad.doubleclick.net/adj/wka.gaming/_halo/article;s1=_halo;s2=article;pform=Xbox360;gnre=Action;sgnre=FPS;esrb=mature;artid=1;pos=TOP_LEADERBOARD;dcopt=ist;sz=728x90;tile=1;endtag=\$;ord=234\" type=\"text/javascript\"></script>";
		}
	}
	
	function searchForm( $label = "" ) {
	   
	 	global $wgRequest, $wgUploadPath;

	    $search = $wgRequest->getText( 'search' );
	    $action = $this->escapeSearchLink();

	    $search = "<form method=\"get\" action=\"$action\" name=\"search_form\">";

	    if ( "" != $label ) { $s .= "{$label}: "; }
		$search .= "<input type=\"text\" class=\"search-field\" name=\"search\" value=\"enter search\" onclick=\"this.value=''\"/>
		<input type=\"image\" src=\"{$wgUploadPath}/common/new/search_button.gif?1\" class=\"search-button\" value=\"go\"/>";
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
	
	private function buildMoreGaming(){
		$message_key = 'moregaming';
		$message = trim(wfMsg($message_key));

		if(wfEmptyMsg($message_key, $message)) {
			return array();
		}

		$lines = array_slice(explode("\n", $message), 0, 150);

		if(count($lines) == 0) {
			return array();
		}

		
		foreach($lines as $line) {
			$more_gaming[] = $this->parseItem($line);
		}
		
		return $more_gaming;
	}
	
	private function printMenu($id, $last_count='', $level=0) {
		global $wgUploadPath;
		$menu_output = "";
		$script_output = "";
		$count = 1;
		if(isset($this->navmenu[$id]['children'])) {
			$script_output .= '<script type="text/javascript">/*<![CDATA[*/';
			if ($level) {
				$menu_output .= '<div class="sub-menu" id="sub-menu' . $last_count . '" style="display:none;" >';
				$script_output .= 'submenu_array["sub-menu' . $last_count . '"] = "' . $last_count . '";';
				$script_output .= '$("sub-menu' . $last_count . '").onmouseout = clearMenu;if ($("sub-menu' . $last_count . '").captureEvents) $("sub-menu' . $last_count . '").captureEvents(Event.MOUSEOUT);';
			}
			foreach($this->navmenu[$id]['children'] as $child) {
				
				$mouseover = ' onmouseover="' . ($level ? 'sub_' : '') . 'menuItemAction(\'' . ($level ? $last_count . '_' : '_') .$count . '\');"';
				$mouseout = ' onmouseout="clearBackground(\'_' . $count . '\')"';
				$menu_output .='<div class="' . ($level ? 'sub-' : '') . 'menu-item' . (($count==sizeof($this->navmenu[$id]['children'])) ? ' border-fix' : '') . '" id="' . ($level ? 'sub-' : '') . 'menu-item' . ($level ? $last_count . '_' : '_') .$count . '">';
				$menu_output .= '<a id="' . ($level ? 'a-sub-' : 'a-') . 'menu-item' . ($level ? $last_count . '_' : '_') .$count . '" href="'.(!empty($this->navmenu[$child]['href']) ? htmlspecialchars($this->navmenu[$child]['href']) : '#').'">';
				if(!$level) {
					
					$script_output .= 'menuitem_array["menu-item' . $last_count . '_' .$count .'"] = "' . $last_count . '_' .$count . '";';
					$script_output .= '$("menu-item' . $last_count . '_' .$count .'").onmouseover = menuItemAction;if ($("menu-item' . $last_count . '_' .$count .'").captureEvents) $("menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';
					$script_output .= '$("menu-item' . $last_count . '_' .$count .'").onmouseout = clearBackground;if ($("menu-item' . $last_count . '_' .$count .'").captureEvents) $("menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOUT);';
					
					$script_output .= '$("a-menu-item' . $last_count . '_' .$count .'").onmouseover = menuItemAction;if ($("a-menu-item' . $last_count . '_' .$count .'").captureEvents) $("a-menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';

					/*
					$script_output .= 'menuitem_array["d-menu-item' . $last_count . '_' .$count .'"] = "' . $last_count . '_' .$count . '";';
					$script_output .= '$("d-menu-item' . $last_count . '_' .$count .'").onmouseover = menuItemAction;if ($("d-menu-item' . $last_count . '_' .$count .'").captureEvents) $("d-menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';
					$script_output .= '$("d-menu-item' . $last_count . '_' .$count .'").onmouseout = clearBackground;if ($("d-menu-item' . $last_count . '_' .$count .'").captureEvents) $("d-menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOUT);';
					*/
				}
				else {
					$script_output .= 'submenuitem_array["sub-menu-item' . $last_count . '_' .$count .'"] = "' . $last_count . '_' .$count . '";';
					$script_output .= '$("sub-menu-item' . $last_count . '_' .$count .'").onmouseover = sub_menuItemAction;if ($("sub-menu-item' . $last_count . '_' .$count .'").captureEvents) $("sub-menu-item' . $last_count . '_' .$count .'").captureEvents(Event.MOUSEOVER);';
				}
				$menu_output .= $this->navmenu[$child]['text'];
				if (sizeof($this->navmenu[$child]['children'])) {
					//$menu_output .= '<div class="sub-menu-button"><img src="http://fp029.sjc.wikia-inc.com/images/halo/new/right_arrow.gif" alt="" border="0" /></div>';
					$menu_output .= '<img src="' . $wgUploadPath . '/common/new/right_arrow.gif?1" alt="" border="0" class="sub-menu-button" />';
				}
				$menu_output .= '</a>';
				//$menu_output .= $id . ' ' . sizeof($this->navmenu[$child]['children']) . ' ' . $child . ' '; 
				$menu_output .= $this->printMenu($child, $last_count . '_' . $count, $level+1);
				//$menu_output .= "last";
				$menu_output .= '</div>';
				$count++;
			}
			if ($level) {
				$menu_output .= '</div>';
			}
			$script_output .= '/*]]>*/</script>';
		}
		
		if ($menu_output.$script_output!="") {
			
			$output .= "<div id=\"menu{$last_count}\">";
				$output .= $menu_output . $script_output;
			$output .= "</div>";
			
		}
		
			
		
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
			$classes[] = ' new';
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
					$moveTitle = SpecialPage::getTitleFor( 'Movepage', $wgTitle->getPrefixedDbKey() );
					$content_actions['move'] = array(
						'class' => $wgTitle->isSpecial( 'Movepage' ) ? 'selected' : false,
						'text' => wfMsg('move'),
						'href' => $moveTitle->getLocalUrl()
					);
				}
				
				$whatlinkshereTitle = SpecialPage::getTitleFor( 'Whatlinkshere', $wgTitle->getPrefixedDbKey() );
				$content_actions['whatlinkshere'] = array(
					'class' => $wgTitle->isSpecial( 'Whatlinkshere' ) ? 'selected' : false,
					'text' => wfMsg('whatlinkshere'),
					'href' => $whatlinkshereTitle->getLocalURL()
				);
				
			} else {
				//article doesn't exist or is deleted
				if( $wgUser->isAllowed( 'delete' ) ) {
					if( $n = $wgTitle->isDeleted() ) {
						$undelTitle = SpecialPage::getTitleFor( 'Undelete' );
						$content_actions['undelete'] = array(
							'class' => false,
							'text' => wfMsgExt( 'undelete_short', array( 'parsemag' ), $n ),
							'href' => $undelTitle->getLocalUrl( 'target=' . urlencode( $wgTitle->getPrefixedDbKey() ) )
							#'href' => self::makeSpecialUrl( "Undelete/$this->thispage" )
						);
					}
				}
			}
		}else{
			
			/* show special page tab */
			if( $wgTitle->getText() == "QuizGameHome" && $wgRequest->getVal( 'questionGameAction' ) == "editItem" ){
				global $wgQuizID;
				$quiz = Title::makeTitle( NS_SPECIAL, "QuizGameHome");
				$content_actions[$wgTitle->getNamespaceKey()] = array(
					'class' => 'selected',
					'text' => wfMsg('nstab-special'),
					'href' => $quiz->getFullURL("questionGameAction=renderPermalink&permalinkID=" . $wgQuizID), 
				);
			}else{
				$content_actions[$wgTitle->getNamespaceKey()] = array(
					'class' => 'selected',
					'text' => wfMsg('nstab-special'),
					'href' => $wgRequest->getRequestURL(), // @bug 2457, 2510
				);
			}
			
			if( $wgTitle->getText() == "QuizGameHome" && $wgUser->isAllowed( 'protect' ) ){
				global $wgQuizID;
				$quiz = Title::makeTitle( NS_SPECIAL, "QuizGameHome");
				$content_actions["edit"] = array(
					'class' => ($wgRequest->getVal("questionGameAction") == 'editItem') ? 'selected' : false,
					'text' => wfMsg('edit'),
					'href' => $quiz->getFullURL("questionGameAction=editItem&quizGameId=".$wgQuizID), // @bug 2457, 2510
				);
			}
			if( $wgTitle->getText() == "PictureGameHome" && $wgUser->isAllowed( 'protect' ) ){
				global $wgPictureGameID;
				$quiz = Title::makeTitle( NS_SPECIAL, "PictureGameHome");
				$content_actions["edit"] = array(
					'class' => ($wgRequest->getVal("picGameAction") == 'editPanel') ? 'selected' : false,
					'text' => wfMsg('edit'),
					'href' => $quiz->getFullURL("picGameAction=editPanel&id=".$wgPictureGameID), // @bug 2457, 2510
				);
			}			
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
	
		global $wgUser, $wgTitle, $wgUploadPath;
		
		$full_title = Title::makeTitle( $wgTitle->getNameSpace(), $wgTitle->getText() );
		
		$output .= "<div id=\"action-bar\">";
			if ($wgUser->isLoggedIn() && $this->isContent() ) {
	
				$output .= "<div id=\"article-controls\">
				<img src=\"{$wgUploadPath}/common/new/plus.gif?1\" alt=\"\" border=\"0\">";
	
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
						/*$output .= "<a href=\"".htmlspecialchars($val['href'])."\" class=\"".(($val['class']!="selected")?"tab-off":"tab-on")."\" rel=\"nofollow\">
							<span>" . ucfirst($val['text']) . "</span>
						</a>";*/
						$output .= "<a href=\"".htmlspecialchars($val['href'])."\" class=\"".((strpos($val['class'], "selected")===0)?"tab-on":"tab-off"). (strpos($val['class'], "new") && (strpos($val['class'], "new")>0)?" tab-new":"")."\" rel=\"nofollow\">
							<span>" . ucfirst($val['text']) . "</span>
						</a>";
				}
				
				if (count($moreLinks)>0) {
					
					$output .=  "<script type=\"text/javascript\">/*<![CDATA[*/
						var _shown = false;
						var _hide_timer;
						function show_actions(el, type) {
	
							if (type==\"show\") {
								clearTimeout(_hide_timer);
								if (!_shown) {
									\$D.replaceClass('more-tab','more-tab-off','more-tab-on');
									YAHOO.widget.Effects.Show(\$(el));
									_shown = true;
								}
							} else {
								\$D.replaceClass('more-tab','more-tab-on','more-tab-off');
				
								YAHOO.widget.Effects.Hide(\$(el));
								_shown = false;
							}
	
						}
						
						function delay_hide(el) {
							_hide_timer = setTimeout (function() {show_actions(el, 'hide');}, 500);
						}
	
					
					/*]]>*/
					</script>
					
	
					<div class=\"more-tab-off\" id=\"more-tab\" onmouseover=\"show_actions('article-more-container', 'show');\" onmouseout=\"delay_hide('article-more-container');\">
						<span>More Actions</span>";
					
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
  		global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, 
			$wgSiteView, $wgArticle, $IP, $wgMemc, $wgSupressPageTitle,$wgSupressSubTitle, $wgUploadPath;	
		

		$output .= "<div id=\"container\">
			<div id=\"wikia-header\">
				<div id=\"wikia-logo\">
				<a href=\"http://www.wikia.com/wiki/Gaming\"><img src=\"{$wgUploadPath}/common/new/wikia_logo.gif?1\" alt=\"\" border=\"0\"/></a> 
					<span id=\"wikia-category\">Gaming</span>
				</div>
				<div id=\"wikia-more-category\" onclick=\"show_more_category('wikia-more-menu')\">
					<div class=\"positive-button\"><span>More Gaming</span></div>
				</div>
				<div id=\"wikia-more-menu\" style=\"display:none;\">\n";
					
					$more_gaming = $this->buildMoreGaming();

					$x = 1;
					foreach( $more_gaming as $link ){
						$output .= "<a href=\"{$link["href"]}\"" . (($x==count($more_gaming))?" class=\"border-fix\"":"") . ">{$link["text"]}</a>\n"; //<a href=\"#\">EQ2i</a>
						if ( $x > 1 && $x % 2 == 0 )$output .= "<div class=\"cleared\"></div>\n";
						$x++;
					}
					
				$output .= "</div>";
				
				//login safe title
				$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
				$login_link = Title::makeTitle(NS_SPECIAL, "Login");
				$logout_link = Title::makeTitle(NS_SPECIAL, "Userlogout");
				$profile_link = Title::makeTitle(NS_USER, $wgUser->getName());
				$main_page_link = Title::makeTitle(NS_MAIN, "Main Page");
				
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
					<a href=\"".$main_page_link->escapeFullURL()."\" rel=\"nofollow\"><img src=\"{$wgUploadPath}/common/new/site_logo.gif?1\" border=\"0\" alt=\"\"/></a>
				</div>
			</div>";

	
			$output .= "<div id=\"side-bar\">";
			 
				
				
				$random_page_link = Title::makeTitle(NS_SPECIAL, "RandomPage");
				$recent_changes_link = Title::makeTitle(NS_SPECIAL, "Recentchanges");
				$top_fans_link = Title::makeTitle(NS_SPECIAL, "TopUsers");
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
					$this->navmenu_array = array();
					$this->navmenu = $this->getNavigationMenu();
					$output .= $this->printMenu(0);
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
					".$this->searchForm() . "
					<div class=\"bottom-left-nav\">";
					
					if ($wgTitle->getNamespace() == NS_BLOG) {
							
							global $wgBlogCategory;
							require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");

							$output .= '<div class="bottom-left-nav-container bottom-left-listpage-fix">';

								$output .= '<h2>Popular Blog Posts</h2>';
								$list = new ListPages();
								$list->setCategory("News, {$wgBlogCategory},Questions");
								$list->setShowCount(10);
								$list->setOrder("PublishedDate");
								$list->setShowPublished("YES");
								$list->setBool("ShowVoteBox","NO");
								$list->setBool("ShowDate","NO");
								$list->setBool("ShowStats","NO");						
								$list->setBool("ShowNav","NO");
								$output .= $list->DisplayList();
							
					
							$output .= '</div>
							<div class="bottom-left-nav-container bottom-left-listpage-fix">';
								$output .= '<h2>New Blog Posts</h2>';
					
								$list = new ListPages();
								$list->setCategory("News, {$wgBlogCategory},Questions");
								$list->setShowCount(10);
								$list->setOrder("New");
								$list->setShowPublished("NO");
								$list->setBool("ShowVoteBox","NO");
								$list->setBool("ShowDate","NO");
								$list->setBool("ShowStats","NO");
								$list->setBool("ShowNav","NO");
								$output .= $list->DisplayList();

							$output .= '</div>';
						}
					
						if ($wgTitle->getNamespace() == NS_COMMENT_FORUM) {
							
							global $wgForumCategory;
							require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");

							$output .= '<div class="bottom-left-nav-container bottom-left-listpage-fix">';
		
								$output .= '<h2>New Forum Topics</h2>';
								$list = new ListPages();
								$list->setCategory("{$wgForumCategory}");
								$list->setShowCount(5);
								$list->setOrder("NEW");
								$list->setShowPublished("NO");
								$list->setBool("ShowVoteBox","NO");
								$list->setBool("ShowDate","NO");
								$list->setBool("ShowStats","NO");						
								$list->setBool("ShowNav","NO");
								$output .= $list->DisplayList();
					
							$output .= '</div>';
					
							$output .= "<h2>latest top forum comments<h2>";
							$output .= ForumPage::getCommentsOfTheDay();
						
						}
			
						$output .=	wfGetRandomGameUnit();
						
						$output .= "<div class=\"bottom-left-nav-container\">
							<h2>Did You Know</h2>
							".$wgOut->parse("{{Didyouknow}}")."
						</div>";
						
						$random_image = $wgOut->parse("<randomimagebycategory width=\"200\" categories=\"Featured Image\"></randomimagebycategory>", false);	
						
						if( $random_image ){
							$output .= "<div class=\"bottom-left-nav-container\">
								<h2>Featured Image</h2>
								{$random_image}
							</div>";
						}
						
						$random_user = $wgOut->parse("<randomfeatureduser period=\"weekly\"></randomfeatureduser>", false);
						
						if( $random_user ){
							$output .= "<div class=\"bottom-left-nav-container\">
								<h2>Featured User</h2>
								{$random_user}
							</div>";
						}
						
						
					$output .= "</div>
				</div>
			</div>
			<div id=\"body-container\">";
				
				 
				
			$site_notice = wfGetSiteNotice();
			if( $site_notice){
				$site_notice_html = "<div id=\"siteNotice\">{$site_notice}</div>";
			}
			
			$output .= $this->actionBar()."
			
			<div id=\"article\">
				
				<div id=\"article-body\">
				
				{$site_notice_html}
				
					<div id=\"article-text\" class=\"clearfix\">";
					
					if( !$wgSupressPageTitle ){ 
						$output .= $this->pageTitle();
					}
					if( $wgTitle->getText()=="Contributions" || ( $this->isContent() &&  !$wgSupressSubTitle) ){
						$output .= $this->pageSubtitle();
					}
			
			

  		return $output;
  
	}
 
	
	 function doAfterContent() {
 
		global $wgOut, $wgUser, $wgTitle, $wgSupressPageCategories;
		
					if( !$wgSupressPageCategories ){
						//get categories
						$cat=$this->getCategoryLinks();
						if($cat){
							$output.="
							<div id=\"catlinks\">
								$cat
							</div>";
						}
					}
					$output .= "</div>
				</div>
			</div>
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
				$sql = "SELECT DISTINCT rev_user, rev_user_text FROM revision WHERE rev_page = {$page_title_id} and rev_user <> 0 and rev_user_text<>'Mediawiki Default' and rev_user_text<>'MLB Stats Bot' ORDER BY rev_user_text ASC LIMIT 0,8";
				$res = $dbr->query($sql);
				while ($row = $dbr->fetchObject( $res ) ) {
					$editors[] = array( "user_id" => $row->rev_user, "user_name" => $row->rev_user_text);
				}
				$wgMemc->set( $key, $editors, 60 * 5 );
			} else {
				wfDebug( "loading recent editors for page {$page_title_id} from cache\n" );
				$editors = $data;
			}
		
			$x=1;
			$per_row=4;
		
			if (count($editors)>0) {
			
				$footer .= "<div id=\"footer-container\">
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

								$footer .= "<a href=\"{$user_title->escapeFullURL()}\" rel=\"nofollow\"><img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\"/></a>";

								if($x==count($editors) || $x!=1 && $x%$per_row ==0) {
									$footer .= "<br/>";
								}

								$x++;

							}
					$footer .= "</div>
				</div>";
			}
		}
		
		$footer .= "<div id=\"footer-bottom\">
			<a href=\"{$main_page->escapeLocalURL()}\" rel=\"nofollow\">Main Page</a>
			<a href=\"{$about->escapeLocalURL()}\" rel=\"nofollow\">About</a>
			<a href=\"{$special->escapeLocalURL()}\" rel=\"nofollow\">Special Pages</a>
			<a href=\"{$help->escapeLocalURL()}\" rel=\"nofollow\">Help</a>
			<a href=\"http://www.wikia.com/wiki/Terms_of_use\" rel=\"nofollow\">Terms of Use</a>
			<a href=\"http://www.federatedmedia.net/authors/wikia\" rel=\"nofollow\">Advertise</a>
		</div>";
		
		return $footer;
	}

}

?>
