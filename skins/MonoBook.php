<?php
/**
 * MonoBook nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @addtogroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once('includes/SkinTemplate.php');
require_once('extensions/AdServer.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @addtogroup Skins
 */
class SkinMonoBook extends SkinTemplate {
	/** Using monobook. */
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'monobook';
		$this->stylename = 'monobook';
		$this->template  = 'MonoBookTemplate';
	}

	function &setupTemplate( $classname, $repository = false, $cache_dir = false ) {
		$tpl = new $classname();
		$tpl->set( 'wikicities-nav_urls', $this->buildWikicitiesNavUrls() );
		return $tpl;
	}

	/**
	 * build array of wikicities-specific global navigation links
	 * @return array
	 * @access private
	 */
	function buildWikicitiesNavUrls () {
		$fname = 'SkinTemplate::buildWikicitiesNavUrls';
		wfProfileIn( $fname );

		global $wgWikicitiesNavLinks;

		$result = array();
		if(isset($wgWikicitiesNavLinks) && is_array($wgWikicitiesNavLinks)) {
			foreach ( $wgWikicitiesNavLinks as $link ) {
				$text = wfMsg( $link['text'] );
				wfProfileIn( "$fname-{$link['text']}" );
				if ($text != '-') {
					$dest = wfMsgForContent( $link['href'] );
					wfProfileIn( "$fname-{$link['text']}2" );
					$result[] = array(
							'text' => $text,
							'href' => $this->makeInternalOrExternalUrl( $dest ),
							'id' => 'n-'.$link['text']
							);
					wfProfileOut( "$fname-{$link['text']}2" );
				}
				wfProfileOut( "$fname-{$link['text']}" );
			}
		}
		wfProfileOut( $fname );
		return $result;
	}
}

/**
 * @todo document
 * @addtogroup Skins
 */
class MonoBookTemplate extends QuickTemplate {
	/**
	 * Template filter callback for MonoBook skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgRequest, $wgShowAds, $wgTitle, $wgUseAdServer, $wgDotDisplay, $wgDBserver, $wgUser;

		$action = $wgRequest->getText('action');
		$this->set('use_ad_page_redirect', $wgTitle->getNamespace() == NS_SPECIAL || $action=='edit' || $action=='history');

		$this->set('ads',$wgShowAds);
		if ( $wgShowAds == false || $wgUseAdServer == false ) {
			$this->set('adserver_ads', '');
		} else if ( $wgUseAdServer == true ) {
			$this->set('adserver_ads', getAllAds($this->data['use_ad_page_redirect'], $this->data['ads']) );
		}

		$diff = $wgRequest->getVal( 'diff' );
		if( isset( $diff ) ) {
			$this->set('ads','false');
		}

		$skin = $wgUser->getSkin();

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php
	foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
		?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
	} ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>"; /*]]>*/</style>
		<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
		<link rel="stylesheet" type="text/css" media="handheld" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/handheld.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
		<!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
		<meta http-equiv="imagetoolbar" content="no" /><![endif]-->

		<?php print Skin::makeGlobalVariablesScript( $this->data ); ?>

		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
<?php	if($this->data['jsvarurl'  ]) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl'  ) ?>"><!-- site js --></script>
<?php	} ?>
<?php	if($this->data['pagecss'   ]) { ?>
		<style type="text/css"><?php $this->html('pagecss'   ) ?></style>
<?php	}
		if($this->data['usercss'   ]) { ?>
		<style type="text/css"><?php $this->html('usercss'   ) ?></style>
<?php	}
		if($this->data['userjs'    ]) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
<?php	}
		if($this->data['userjsprev']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
<?php	}
		if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
		<!-- Head Scripts -->
<?php $this->html('headscripts') ?>
	</head>
<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload'    ]) { ?>onload="<?php     $this->text('body_onload')     ?>"<?php } ?>
 class="mediawiki <?php $this->text('nsclass') ?> <?php $this->text('dir') ?> <?php $this->text('pageclass') ?>">
	<div id="globalWrapper">
		<div id="column-content">
	<?php if($this->data['ads'] || $this->data['adserver_ads'][ADSERVER_POS_TOPRIGHT]){ ?>
			<div id="content" class="content-ads">
	<?php } else { ?>
			<div id="content" class="content-noads">
	<?php } ?>

		<a name="top" id="top"></a>
		<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
		<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?>
		<?php wfRunHooks( 'AfterTitleDisplayed'); ?>
		<div id="ajaxProgressIcon" style="display: none;"><img src="/skins/wikia/images/progress-wheel.gif" /></div>
		</h1>

		<?php
		if ( $this->data['adserver_ads'] ) {
			echo $this->data['adserver_ads'][ADSERVER_POS_TOP];
		}
		?>
		<div id="bodyContent">
			<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
			<div id="contentSub"><?php $this->html('subtitle') ?></div>
			<?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
			<?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
			<?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
			<!-- start content -->
			<?php $this->html('bodytext') ?>
			<?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
			<!-- end content -->
			<div class="visualClear"></div>
		</div>
		<?php
		if ( $this->data['adserver_ads'] ) {
			echo $this->data['adserver_ads'][ADSERVER_POS_BOT];
		}
		?>
	</div>
		</div>
		<div id="column-one">
	<div id="p-cactions" class="portlet">
		<h5><?php $this->msg('views') ?></h5>
		<div class="pBody">
			<ul>
	<?php			foreach($this->data['content_actions'] as $key => $tab) { ?>
				 <li id="ca-<?php echo Sanitizer::escapeId($key) ?>"<?php
					 	if($tab['class']) { ?> class="<?php echo htmlspecialchars($tab['class']) ?>"<?php }
					 ?>><a href="<?php echo htmlspecialchars($tab['href']) ?>"<?php echo $skin->tooltipAndAccesskey('ca-'.$key) ?>><?php
					 echo htmlspecialchars($tab['text']) ?></a></li>
	<?php			 } ?>
			</ul>
		</div>
	</div>
	<div class="portlet" id="p-personal">
		<h5><?php $this->msg('personaltools') ?></h5>
		<div class="pBody">
			<ul>
<?php 			foreach($this->data['personal_urls'] as $key => $item) { ?>
				<li id="pt-<?php echo Sanitizer::escapeId($key) ?>"<?php
					if ($item['active']) { ?> class="active"<?php } ?>><a href="<?php
				echo htmlspecialchars($item['href']) ?>"<?php echo $skin->tooltipAndAccesskey('pt-'.$key) ?><?php
				if(!empty($item['class'])) { ?> class="<?php
				echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
				echo htmlspecialchars($item['text']) ?></a></li>
<?php			} ?>
			</ul>
		</div>
	</div>
	<div class="portlet" id="p-logo">
		<a style="background-image: url(<?php $this->text('logopath') ?>);" <?php
			?>href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
			echo $skin->tooltipAndAccesskey('n-mainpage') ?>></a>
	</div>
	<script type="<?php $this->text('jsmimetype') ?>"> if (window.isMSIE55) fixalpha(); </script>
	<div class='portlet' id='ads-top-left'>
	<?php
	if ( $this->data['adserver_ads'] ) {
		echo $this->data['adserver_ads'][ADSERVER_POS_TOPLEFT];
	}
	?>
	</div>
	<?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
	<div class='portlet' id='p-<?php echo Sanitizer::escapeId($bar) ?>'<?php echo $skin->tooltip('p-'.$bar) ?>>
		<h5><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h5>
		<div class='pBody'>
			<ul>
<?php 			foreach($cont as $key => $val) { ?>
				<li id="<?php echo Sanitizer::escapeId($val['id']) ?>"<?php
					if ( $val['active'] ) { ?> class="active" <?php }
				?>><a href="<?php echo htmlspecialchars($val['href']) ?>"<?php echo $skin->tooltipAndAccesskey($val['id']) ?>><?php echo htmlspecialchars($val['text']) ?></a></li>
<?php			} ?>
			</ul>
		</div>
	</div>
	<?php } ?>
	<div id="p-search" class="portlet">
		<h5><label for="searchInput"><?php $this->msg('search') ?></label></h5>
		<div id="searchBody" class="pBody">
			<form action="<?php $this->text('searchaction') ?>" id="searchform"><div>
			<?php if( file_exists("images/8/85/Search_logo.png")){ ?>
				<a href="http://www.wikia.com/">
				<img src="/images/8/85/Search_logo.png" border="0" alt="Search This Wiki" align="middle"></img></a>
			<?php } ?>
				<input id="searchInput" name="search" type="text"<?php echo $skin->tooltipAndAccesskey('search');
					if( isset( $this->data['search'] ) ) {
						?> value="<?php $this->text('search') ?>"<?php } ?> />
				<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>" />&nbsp;
				<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>" />
			</div></form>
		</div>
	</div>

<? Global $wgAjaxAutoCompleteSearch; if ($wgAjaxAutoCompleteSearch) { ?>
	<div id="fSearchChoicesId" class="autocomplete" ></div>
	<script type="text/javascript" src="http://script.aculo.us/prototype.js" ></script>
	<script type="text/javascript" src="http://script.aculo.us/scriptaculous.js" ></script>
	<script type="text/javascript" >
		var cxServer = '<?php Global $wgServer; Echo $wgServer;?>';
		var cxScript = '<?php Global $wgScriptPath; Echo $wgScriptPath;?>';
		var o = new Ajax.Autocompleter ("searchInput", "fSearchChoicesId", cxServer + cxScript + '/', {paramName: 'rsargs', minChars: '1', indicator: 'ajaxProgressIcon', parameters:'action=ajax&rs=cxSearchAutoComplete'});
	</script>
<? } ?>

	<div class="portlet" id="p-tb">
		<h5><?php $this->msg('toolbox') ?></h5>
		<div class="pBody">
			<ul>
<?php
		if($this->data['notspecialpage']) { ?>
				<li id="t-whatlinkshere"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?></a></li>
<?php
			if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
				<li id="t-recentchangeslinked"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked') ?></a></li>
<?php 		}
		}
		if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
			<li id="t-trackbacklink"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-trackbacklink') ?>><?php $this->msg('trackbacklink') ?></a></li>
<?php 	}
		if($this->data['feeds']) { ?>
			<li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
					?><span id="feed-<?php echo Sanitizer::escapeId($key) ?>"><a href="<?php
					echo htmlspecialchars($feed['href']) ?>"<?php echo $skin->tooltipAndAccesskey('feed-'.$key) ?>><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
					<?php } ?></li><?php
		}

		foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

			if($this->data['nav_urls'][$special]) {
				?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-'.$special) ?>><?php $this->msg($special) ?></a></li>
<?php		}
		}

		if(!empty($this->data['nav_urls']['print']['href'])) { ?>
				<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-print') ?>><?php $this->msg('printableversion') ?></a></li><?php
		}

		if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
				<li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
				?>"<?php echo $skin->tooltipAndAccesskey('t-permalink') ?>><?php $this->msg('permalink') ?></a></li><?php
		} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
				<li id="t-ispermalink"<?php echo $skin->tooltip('t-ispermalink') ?>><?php $this->msg('permalink') ?></li><?php
		}

		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
?>
			</ul>
		</div>
	</div>
	<div class="portlet" id="p-wikicities-nav">
		<h5><?php $this->msg('wikicities-nav') ?></h5>
		<div class="pBody">
			<ul>
				<?php foreach($this->data['wikicities-nav_urls'] as $navlink) { ?>
					<li id="<?php echo htmlspecialchars($navlink['id']) ?>"><a href="<?php echo htmlspecialchars($navlink['href']) ?>"><?php echo htmlspecialchars($navlink['text']) ?></a></li><?php } ?>
			</ul>
			<hr />
			<ul>
				<li><a href="http://www.wikia.com/wiki/Wikia_news_box">Wikia messages:</a><br /><?php global $wgOut;echo $wgOut->parse(wfMsg('shared-News_box'))?></li>
			</ul>
		</div>
	</div>

<?php
	wfRunHooks ('MonoBookTemplateTipsStart', array( &$this ) );
	wfRunHooks ('MonoBookTemplatePopularArticles', array  (&$this ));
	wfRunHooks ('MonoBookTemplatePopularWikis', array (&$this));
?>

<?php
		if( $this->data['language_urls'] ) { ?>
	<div id="p-lang" class="portlet">
		<h5><?php $this->msg('otherlanguages') ?></h5>
		<div class="pBody">
			<ul>
<?php		foreach($this->data['language_urls'] as $langlink) { ?>
				<li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
<?php		} ?>
			</ul>
		</div>
	</div>
<?php	} ?>
		</div><!-- end of the left (by default at least) column -->
<!-- right column (google ads) BEGIN -->
<?php if($this->data['ads']) {
	echo "\n<!--JASON $this->data['ads']-->\n";

	if ( $this->data['adserver_ads'] ) {
		echo "<!-- USING ad server! -->\n";
		echo "<div id='column-google'>\n";
		echo "<!-- ADSERVER top right -->\n";
		echo $this->data['adserver_ads'][ADSERVER_POS_TOPRIGHT];
		echo "<br /><!-- ADSERVER right -->\n";
		echo $this->data['adserver_ads'][ADSERVER_POS_RIGHT];
		echo "<br /><!-- ADSERVER botright -->\n";
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTRIGHT];
		echo "\n</div>\n";
	} else {
		echo "<!-- NOT using new ad server -->\n";
	?>
		<div id="column-google">
		<script type="text/javascript"><!--
		google_ad_client = "pub-4086838842346968";
		<?php global $wgServer, $wgStylePath, $wgGoogleProps;
		echo $wgGoogleProps;
		if ( $this->data['use_ad_page_redirect'] )
			echo "\ngoogle_page_url = \"$wgServer/wiki/" . wfMsgForContent('mainpage') . "\";\n";
		?>

		//--></script>
		<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
		</div>
	<?php }
	}
?>
<!-- right column (google ads) END -->
			<div class="visualClear"></div>
			<div id="footer">
<?php
		if($this->data['poweredbyico']) { ?>
				<div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div>
<?php 	}
		if($this->data['copyrightico']) { ?>
				<div id="f-copyrightico"><?php $this->html('copyrightico') ?></div>
<?php	}

		// Generate additional footer links
?>
			<ul id="f-list">
				<li id="lastmod"><?= $this->html('lastmod') ?></li>
				<li id="credits"><?= $this->html('credits') ?></li>
				<li id="about"><a href="http://www.wikia.com/wiki/About_Wikia" title="About Wikia">About Wikia</a></li>
				<li id="disclaimer"><a href="http://www.wikia.com/wiki/Terms_of_use" title="Terms of use">Terms of use</a></li>
				<li id="disclaimer"><a href="http://www.federatedmedia.net/authors/wikia" title="advertise on wikia">Advertise</a></li>
				<li id="diggs"><?= $this->html("diggs") ?></li>
				<li id="delicious"><?= $this->html("delicious") ?></li>
			</ul>
			<!-- wgDBserver: <?= $wgDBserver ?> -->
			<div id="f-hosting"><i>Wikia</i> is a service mark of Wikia, Inc. All rights reserved.</div>
		</div>

	<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
</div>
<?php $this->html('reporttime') ?>
<?php if ( $this->data['debug'] ): ?>
<!-- Debug output:
<?php $this->text( 'debug' ); ?>

-->
<?php endif;
	if ( $this->data['adserver_ads'] ) {
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTBOT];
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTBOT2];
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTBOT3];
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTBOT4];
		echo $this->data['adserver_ads'][ADSERVER_POS_BOTBOT5];
	}
	global $wgServer;
	//TAH - overruling JQS, instead loading javascript in three locations from the adserver
	if ( $this->data['adserver_ads'] ) {
		echo "<!-- adserver on, injecting bottom JS.. " . count($this->data['adserver_ads']) . "-->\n";
		echo $this->data['adserver_ads'][ADSERVER_POS_JS_BOT1];
		echo $this->data['adserver_ads'][ADSERVER_POS_JS_BOT2];
		echo $this->data['adserver_ads'][ADSERVER_POS_JS_BOT3];
	}
	//Emil - display GoogleAnalytics for wikis that don't use adserver
	elseif ( preg_match("/wikia.com/",$wgServer) ) {
?>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
_udn="wikia.com";
_uacct = "UA-288915-1";
urchinTracker();
</script>
<?php
	}
?>
</div><!-- end of globalWrapper? -->
<?php
/**
 * eloy, display 1x1 dot for simple stats
 */
if (!empty($wgDotDisplay)) {
?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/1dot.js?0"><!-- 1dot js --></script>
	<noscript><img src="http://wikia-ads.wikia.com/1dot.php?js=0" alt="." width="1" height="1" border="0" /></noscript>
<?php
}
?>
</body></html>
<!--<?php global $wgLoadBalancer; echo $wgLoadBalancer->allowLagged();?>-->
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>
