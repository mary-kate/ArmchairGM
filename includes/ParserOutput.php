<?php
/**
 * @todo document
 */
class ParserOutput
{
	var $mText,             # The output text
		$mLanguageLinks,    # List of the full text of language links, in the order they appear
		$mCategories,       # Map of category names to sort keys
		$mContainsOldMagic, # Boolean variable indicating if the input contained variables like {{CURRENTDAY}}
		$mCacheTime,        # Time when this object was generated, or -1 for uncacheable. Used in ParserCache.
		$mVersion,          # Compatibility check
		$mTitleText,        # title text of the chosen language variant
		$mLinks,            # 2-D map of NS/DBK to ID for the links in the document. ID=zero for broken.
		$mTemplates,        # 2-D map of NS/DBK to ID for the template references. ID=zero for broken.
		$mImages,           # DB keys of the images used, in the array key only
		$mExternalLinks,    # External link URLs, in the key only
		$mHTMLtitle,		# Display HTML title
		$mSubtitle,			# Additional subtitle
		$mNewSection,		# Show a new section link?
		$mNoGallery;		# No gallery on category page? (__NOGALLERY__)

	function ParserOutput( $text = '', $languageLinks = array(), $categoryLinks = array(),
		$containsOldMagic = false, $titletext = '' )
	{
		$this->mText = $text;
		$this->mLanguageLinks = $languageLinks;
		$this->mCategories = $categoryLinks;
		$this->mContainsOldMagic = $containsOldMagic;
		$this->mCacheTime = '';
		$this->mVersion = Parser::VERSION;
		$this->mTitleText = $titletext;
		$this->mLinks = array();
		$this->mTemplates = array();
		$this->mImages = array();
		$this->mExternalLinks = array();
		$this->mHTMLtitle = "" ;
		$this->mSubtitle = "" ;
		$this->mNewSection = false;
		$this->mNoGallery = false;
	}

	function getText()                   { return $this->mText; }
	function &getLanguageLinks()          { return $this->mLanguageLinks; }
	function getCategoryLinks()          { return array_keys( $this->mCategories ); }
	function &getCategories()            { return $this->mCategories; }
	function getCacheTime()              { return $this->mCacheTime; }
	function getTitleText()              { return $this->mTitleText; }
	function &getLinks()                 { return $this->mLinks; }
	function &getTemplates()             { return $this->mTemplates; }
	function &getImages()                { return $this->mImages; }
	function &getExternalLinks()         { return $this->mExternalLinks; }
	function getNoGallery()              { return $this->mNoGallery; }
	function getSubtitle()               { return $this->mSubtitle; }

	function containsOldMagic()          { return $this->mContainsOldMagic; }
	function setText( $text )            { return wfSetVar( $this->mText, $text ); }
	function setLanguageLinks( $ll )     { return wfSetVar( $this->mLanguageLinks, $ll ); }
	function setCategoryLinks( $cl )     { return wfSetVar( $this->mCategories, $cl ); }
	function setContainsOldMagic( $com ) { return wfSetVar( $this->mContainsOldMagic, $com ); }
	function setCacheTime( $t )          { return wfSetVar( $this->mCacheTime, $t ); }
	function setTitleText( $t )          { return wfSetVar($this->mTitleText, $t); }
	function setSubtitle( $st )          { return wfSetVar( $this->mSubtitle, $st ); }

	function addCategory( $c, $sort )    { $this->mCategories[$c] = $sort; }
	function addImage( $name )           { $this->mImages[$name] = 1; }
	function addLanguageLink( $t )       { $this->mLanguageLinks[] = $t; }
	function addExternalLink( $url )     { $this->mExternalLinks[$url] = 1; }

	function setNewSection( $value ) {
		$this->mNewSection = (bool)$value;
	}
	function getNewSection() {
		return (bool)$this->mNewSection;
	}

	function addLink( $title, $id = null ) {
		$ns = $title->getNamespace();
		$dbk = $title->getDBkey();
		if ( !isset( $this->mLinks[$ns] ) ) {
			$this->mLinks[$ns] = array();
		}
		if ( is_null( $id ) ) {
			$id = $title->getArticleID();
		}
		$this->mLinks[$ns][$dbk] = $id;
	}

	function addTemplate( $title, $id ) {
		$ns = $title->getNamespace();
		$dbk = $title->getDBkey();
		if ( !isset( $this->mTemplates[$ns] ) ) {
			$this->mTemplates[$ns] = array();
		}
		$this->mTemplates[$ns][$dbk] = $id;
	}

	/**
	 * Return true if this cached output object predates the global or
	 * per-article cache invalidation timestamps, or if it comes from
	 * an incompatible older version.
	 *
	 * @param string $touched the affected article's last touched timestamp
	 * @return bool
	 * @public
	 */
	function expired( $touched ) {
		global $wgCacheEpoch;
		return $this->getCacheTime() == -1 || // parser says it's uncacheable
		       $this->getCacheTime() < $touched ||
		       $this->getCacheTime() <= $wgCacheEpoch ||
		       !isset( $this->mVersion ) ||
		       version_compare( $this->mVersion, Parser::VERSION, "lt" );
	}
}

?>
