<?php

if ( defined( 'MEDIAWIKI' ) ) {
require_once ($GLOBALS['IP']."/extensions/SpamList/SpamList_helper.php");
class SpamBlacklist {
	var $spamList = null;
	var $settings = array();
	
	function SpamBlacklist( $settings = array() ) {
		if (empty($settings['regexes'])) 
			$settings['regexes'] = false;
		if (empty($settings['previousFilter'])) 
			$settings['previousFilter'] = false;
		if (empty($settings['files'])) 
			$settings['files'] = array( "http://meta.wikimedia.org/w/index.php?title=Spam_blacklist&action=raw&sb_ver=1" );
		if (empty($settings['warningTime'])) 
			$settings['warningTime'] = 600;
		if (empty($settings['expiryTime'])) 
			$settings['expiryTime'] = 900;
		if (empty($settings['warningChance'])) 
			$settings['warningChance'] = 100;

		if (empty($settings['memcache_file'])) 
			$settings['memcache_file']  = 'spam_blacklist_file';
		if (empty($settings['memcache_regexes']))
			$settings['memcache_regexes'] = 'spam_blacklist_regexes';

		$this->settings = $settings;
	}

	function filter( &$title, $text, $section ) 
	{
		global $wgArticle, $wgVersion, $wgOut, $wgParser, $wgUser;
		
		$fname = 'wfSpamBlacklistFilter';
		wfProfileIn( $fname );

		$this->settings['title'] = $title;
		$this->settings['text'] = $text;
		$this->settings['section'] = $section;
			
		$this->spamList = new SpamList_helper($this->settings);

		# Call the rest of the hook chain first
		if ( $this->spamList->getPreviousFilter() )
		{
			$f = $this->spamList->getPreviousFilter();
			if ( $f( $title, $text, $section ) ) 
			{
				wfProfileOut( $fname );
				return true;
			}
		}
		
		$regexes = $this->spamList->getRegexes();
		$whitelists = $this->spamList->getWhitelists();
		
		if ( is_array( $regexes ) ) 
		{
			# Run parser to strip SGML comments and such out of the markup
			# This was being used to circumvent the filter (see bug 5185)
			$options = new ParserOptions();
			$text = $wgParser->preSaveTransform( $text, $title, $wgUser, $options );
			$out = $wgParser->parse( $text, $title, $options );
			$links = implode( "\n", array_keys( $out->getExternalLinks() ) );

			# Strip whitelisted URLs from the match
			if( is_array( $whitelists ) ) 
			{
				wfDebug( "Excluding whitelisted URLs from " . count( $whitelists ) . " regexes: " . implode( ', ', $whitelists ) . "\n" );
				foreach( $whitelists as $regex ) 
				{
					$links = preg_replace( $regex, '', $links );
				}
			}
			
			# Do the match
			wfDebug( "Checking text against " . count( $regexes ) . " regexes: " . implode( ', ', $regexes ) . "\n" );
			$retVal = false;
			foreach( $regexes as $regex ) 
			{
				if ( preg_match( $regex, $links, $matches ) ) 
				{
					wfDebug( "Match!\n" );
					EditPage::spamPage( $matches[0] );
					$retVal = true;
					break;
				}
			}
		}
		else {
			$retVal = false;
		}
		
		wfProfileOut( $fname );
		return $retVal;
	}
}

} # End invocation guard
?>
