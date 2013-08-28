<?php
/**
 * Skin1 skin
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once('includes/SkinTemplate.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinSkin1 extends SkinTemplate {
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'Skin1';
		$this->stylename = 'Skin1';
		$this->template  = 'Skin1Template';
	}
}

/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class Skin1Template extends QuickTemplate {
	/**
	 * Template filter callback for Skin1 skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php $this->text('pagetitle') ?></title>
	<style type="text/css" media="screen,projection">
	/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/Skin1.css?<?php echo $GLOBALS['wgStyleVersion'] ?>"; /*]]>*/</style>
</head>

<body>
	<div id="container">
  		<div id="topmenu1">
  			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" title="<?php $this->msg('mainpage') ?>"><img src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/logo_wikia.com_120x27.gif" alt="<?php $this->msg('mainpage') ?>" width="120" height="27" /></a>
  			<?php
  				$links = array();
  				foreach($this->data['personal_urls'] as $key => $item) { 
					$links[] = '<a href="'.htmlspecialchars($item['href']).'">'.htmlspecialchars($item['text']).'</a>';
				}
				echo implode(' ', $links);
			?>
  		</div>
		<div id="topmenu2">
			<?php
				$links = array();
				foreach($this->data['content_actions'] as $key => $tab) {
					 $links[] = '<a href="'.htmlspecialchars($tab['href']).'">'.htmlspecialchars($tab['text']).'</a>';
				}
				echo implode(' ', $links);
			?>
		</div>

		<div id="top">
  			<div id="logo"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" title="<?php $this->msg('mainpage') ?>"><img src="<?php $this->text('logopath') ?>" alt="<?php $this->msg('mainpage') ?>" border="0" width="120" height="110" /></a></div>
		</div>
		
		
		<div id="menu">
			<?php
					foreach ($this->data['sidebar'] as $bar => $cont)
						foreach($cont as $key => $val) {
							echo '<p><a href="'.htmlspecialchars($val['href']).'">'.htmlspecialchars($val['text']).'</a></p>';
						}
			?>
  			
  			<hr class="divider" />
  			
  			<p><?php $this->msg('search') ?></p>
			<form action="<?php $this->text('searchaction') ?>" id="searchform">
				<input id="searchInput" class="textbox" name="search" type="text" <?php
				if($this->haveMsg('accesskey-search')) {
				?>accesskey="<?php $this->msg('accesskey-search') ?>"<?php }
				if( isset( $this->data['search'] ) ) {
				?> value="<?php $this->text('search') ?>"<?php } ?> />
				<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>" />&nbsp
				<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>" />
			</form>
			
			<hr class="divider" />
			
			<?php
				if($this->data['notspecialpage']) { ?>
				<p><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
			?>"><?php $this->msg('whatlinkshere') ?></a></p>
			<?php
				if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
				<p><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
			?>"><?php $this->msg('recentchangeslinked') ?></a></p>
			<?php 		}
				}
				if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
				<p><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
			?>"><?php $this->msg('trackbacklink') ?></a></p>
			<?php 	}
				if($this->data['feeds']) { ?>
				<p><?php foreach($this->data['feeds'] as $key => $feed) {
				?><span id="feed-<?php echo Sanitizer::escapeId($key) ?>"><a href="<?php
				echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
				<?php }
				?></p><?php
				}
				foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {
					if($this->data['nav_urls'][$special]) {
					?><p><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
					?>"><?php $this->msg($special) ?></a></p>
				<?php		}
				}
				if(!empty($this->data['nav_urls']['print']['href'])) { ?>
					<p><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
					?>"><?php $this->msg('printableversion') ?></a></p><?php
				}
				if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
					<p><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
					?>"><?php $this->msg('permalink') ?></a></p><?php
				} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
					<p><?php $this->msg('permalink') ?></p><?php
				}
				wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
			?>
			
			<?php
				if( $this->data['language_urls'] ) { ?>
					<hr class="divider" />
					<?php		foreach($this->data['language_urls'] as $langlink) { ?>
					<p><?php
					?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></p>
					<?php		} ?>
			<?php	} ?>
		</div>
		<div id="content">
			<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
  			<h1><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
			<p><?php $this->html('subtitle') ?></p>
			<!-- start content -->
			<p><?php $this->html('bodytext') ?></p>
			<!-- end content -->
		</div>
		<div id="footer">
			<?php
				if($this->data['poweredbyico']) { ?>
					<p><?php $this->html('poweredbyico') ?></p>
				<?php 	}
				if($this->data['copyrightico']) { ?>
					<p><?php $this->html('copyrightico') ?></p>
				<?php	}
				// Generate additional footer links
				?>
				<?php
					$footerlinks = array(
					'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
					'privacy', 'about', 'disclaimer', 'tagline',
					);
					foreach( $footerlinks as $aLink ) {
						if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
						?>				<p><?php $this->html($aLink) ?></p>
						<?php 		}
					}
				?>
  		</div>
	</div>
</body>
</html>
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>
