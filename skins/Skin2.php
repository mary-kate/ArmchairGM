<?php
/**
 * Skin2 skin
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
class SkinSkin2 extends SkinTemplate {
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'Skin2';
		$this->stylename = 'Skin2';
		$this->template  = 'Skin2Template';
	}
}

/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class Skin2Template extends QuickTemplate {
	/**
	 * Template filter callback for Skin2 skin.
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
	/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/Skin2.css?<?php echo $GLOBALS['wgStyleVersion'] ?>"; /*]]>*/</style>
</head>
<body>
<script>
<!--
	sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);
-->
</script>
<div id="logo"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" title="<?php $this->msg('mainpage') ?>"><img src="<?php $this->text('logopath') ?>" alt="<?php $this->msg('mainpage') ?>" border="0" /></a></div>
<div id="top">
	<span class="register">
		<?php
		$links = array();
  				foreach($this->data['personal_urls'] as $key => $item) { 
					$links[] = '<a href="'.htmlspecialchars($item['href']).'">'.htmlspecialchars($item['text']).'</a>';
				}
				echo implode(' ', $links);
		?>
	</span>
	<span class="search">
  <form action="<?php $this->text('searchaction') ?>" id="searchform">
    <?php $this->msg('search') ?>
    <input id="searchInput" class="textbox" name="search" type="text" <?php
				if($this->haveMsg('accesskey-search')) {
				?>accesskey="<?php $this->msg('accesskey-search') ?>"<?php }
				if( isset( $this->data['search'] ) ) {
				?> value="<?php $this->text('search') ?>"<?php } ?> />
    <input name="imageField" type="image" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/btn_search_25x25.png" />
  </form>
  </span>
  <div id="tabscontainer">
  			<div class="tab"><ul id="nav"><li><?php $this->msg('toolbox') ?><ul><?php
							if($this->data['notspecialpage']) { ?><li><a href="<?php
							echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
							?>"><?php $this->msg('whatlinkshere') ?></a></li><?php
							if( $this->data['nav_urls']['recentchangeslinked'] ) { ?><li><a href="<?php
							echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
							?>"><?php $this->msg('recentchangeslinked') ?></a></li><?php 		}
							}
							if(isset($this->data['nav_urls']['trackbacklink'])) { ?><li><a href="<?php
							echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
							?>"><?php $this->msg('trackbacklink') ?></a></li><?php 	}
							if($this->data['feeds']) { ?><li><?php foreach($this->data['feeds'] as $key => $feed) {
							?><span><a href="<?php
							echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
							<?php } ?></li><?php
							}
							foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {
							if($this->data['nav_urls'][$special]) {
							?><li><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
							?>"><?php $this->msg($special) ?></a></li><?php }
							}
							if(!empty($this->data['nav_urls']['print']['href'])) { ?><li><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
							?>"><?php $this->msg('printableversion') ?></a></li><?php
							}
							if(!empty($this->data['nav_urls']['permalink']['href'])) { ?><li><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
							?>"><?php $this->msg('permalink') ?></a></li><?php
							} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?><li><?php $this->msg('permalink') ?></li><?php
							}
							?></ul></li></ul>
			</div>
  			<?php
  				foreach ($this->data['sidebar'] as $bar => $cont) { ?>
  				<div class="tab"><ul id="nav"><li><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?><ul><?php
									foreach($cont as $key => $val) { ?><li><a href="<?php echo htmlspecialchars($val['href']) ?>"><?php echo htmlspecialchars($val['text']) ?></a></li><?php
									}
								?></ul></li></ul>	
				</div>
			<?php
				}
			?>
  </div>
</div>
<div id="content">
	<div id="toolbox">
		<?php
				foreach($this->data['content_actions'] as $key => $tab) {
					 echo '<a href="'.htmlspecialchars($tab['href']).'"><img src="';
					 $this->text('stylepath');
					 echo '/';
					 $this->text('stylename');
					 $img = trim(substr(strrchr( $tab['href'], '=' ), 1));
					 $img = empty( $img ) ? 'article' : $img;
					 echo '/'.$img.'.gif" alt="" width="20" height="20" />'.htmlspecialchars($tab['text']).'</a>';
				}
			?>
	</div>
	<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
  	<h1><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
	<p><?php $this->html('subtitle') ?></p>
	<!-- start content -->
	<p><?php $this->html('bodytext') ?></p>
	<!-- end content -->
</div> <!-- content -->


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
</div> <!-- footer -->
</body>
</html>
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>
