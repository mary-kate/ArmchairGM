<?PHP

global $wgHooks;
$wgHooks['ParserBeforeTidy'][] = 'beforeTidyHook' ;
$wgHooks['UserToggles'][] = 'wfWikiwygToggle' ;
$wgHooks['handleWikiPrefs'][] = 'wfWikiwygHandleEditingPrefs' ;
$wgHooks['getEditingPreferencesTab'][] = 'wfWikiwygAddEditingPrefs' ;

$wgExtensionFunctions[] = 'registerWikiwygExtension';
$wgExtensionCredits['other'][] = array(
    'name' => 'MediaWikiWyg',
    'author' => 'http://svn.wikiwyg.net/code/trunk/wikiwyg/AUTHORS',
    'version' => 0.10,
    'url' => 'http://www.wikiwyg.net',
    'description' => 'Mediawiki integration of the Wikiwyg WYSIWYG wiki editor'
);

function registerWikiwygExtension() {
    global $wgOut,$wgSkin,$jsdir,$cssdir, $wgScriptPath ;
    global $wgWikiwygPath, $wgUser ;
    global $wgServer,$wgWikiwygJsPath,$wgWikiwygCssPath,$wgWikiwygImagePath;

    if (! isset($wgWikiwygPath)) {
        $wgWikiwygPath = "$wgServer/$wgScriptPath/wikiwyg";
    }
    if (! isset($wgWikiwygJsPath)) {
        $wgWikiwygJsPath = "$wgWikiwygPath/share/MediaWiki";
    }
    if (! isset($wgWikiwygCssPath)) {
        $wgWikiwygCssPath = "$wgWikiwygPath/share/MediaWiki/css";
    }
    if (! isset($wgWikiwygImagePath)) {
        $wgWikiwygImagePath = "$wgWikiwygPath/share/MediaWiki/images";
    }

    $wgOut->addScript("<style type=\"text/css\" media=\"screen,projection\">/*<![CDATA[*/ @import \"$wgWikiwygCssPath/MediaWikiwyg.css\"; /*]]>*/</style>\n");

    $wgOut->addScript("
<script type=\"text/javascript\">
    if (typeof(Wikiwyg) == 'undefined') Wikiwyg = function() {};
    Wikiwyg.mediawiki_source_path = \"$wgWikiwygPath\";
    var wgEditCaption = \"".strtolower(wfMsg ('edit'))."\";
    var wgSaveCaption = \"".wfMsg ('savearticle')."\";
    var wgCancelCaption = \"".wfMsg ('cancel')."\";
    var wgPreviewCaption = \"".wfMsg ('preview')."\";
    var wgBoldTip = \"".wfMsg ('bold_tip')."\";
    var wgItalicTip = \"".wfMsg ('italic_tip')."\";
    var wgIntlinkTip = \"".wfMsg ('link_tip')."\";
    var wgExtlinkTip = \"".wfMsg ('extlink_tip')."\";
    var wgNowikiTip = \"".wfMsg ('nowiki_tip')."\";
    var wgHrTip = \"".wfMsg ('hr_tip')."\";
    var wgTimestampTip =  \"".wfMsg ('sig_tip')."\";
    var wgUseWysiwyg = " .$wgUser->getOption ('wysiwyg',1)." ;    
    var wgUseInPage = ".$wgUser->getOption ('in-page',1)." ;
    var wgFullPageEditing = false ;
</script>
");
    $wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/MediaWikiWyg.js\"></script>\n");
}

function wfRecurrentParse ($text, $level) {	
    $blocks = preg_split(
        '/(<a name=".*?".*?<\/a><h'.$level.'><span class="editsection".*?<\/span>)/i',
        $text, -1, PREG_SPLIT_DELIM_CAPTURE
    );

    $i = 0;
		
    $full = array_shift($blocks);
    $header_block = "" ;
    ($level < 8) ? $next_level = $level + 1 : $next_level = "NONE" ;
    foreach ($blocks as $block) {
        /* now, _this_ is an edit link */
        if (preg_match('/<h'.$level.'><span class="editsection".*?<\/span>/i', $block)) {
		$inner_blocks = preg_split(
			'/(<h'.$level.'>)/i',
			$block, -1, PREG_SPLIT_DELIM_CAPTURE
		);
	        foreach ($inner_blocks as $inner_block) {
                	if (preg_match('/<span class="editsection".*?<\/span>/i', $inner_block)) {
                        	/* now, this is a real edit link... */
				            $i++;
					    /* extract the _real_ section number */
					    preg_match ('/section=[0-9]+/',$inner_block, $section_number) ;
					    $section_number = substr ($section_number[0], 8)  ;
					    $full .= "<span class='wikiwyg_edit' id=\"wikiwyg_edit_{$section_number}\">
						$inner_block
						</span>
						";
			} else {
	                 	/* not an edit link... */
				if (!preg_match('/<h'.$level.'>/i', $inner_block)) {
					$full .= $inner_block ;
				} else {
		                       	$header_block = $inner_block ;
				}
			}
                }
        }
        # This is a section body
        else {
            if ($i == 0) {
                die("Wrong order!");
            }
	    /* investigate matter further - there may be subsections */
	    if (preg_match('/<h'.$next_level.'>/i',$block)) {
	    	/* we found more sections - split it up, add the main thing and go deeper */
    		$blocked_splits = preg_split(
	        	'/(<\/h'.$level.'>)/i',
	        	$block, -1, PREG_SPLIT_DELIM_CAPTURE
		    );
		$block = '' ;
		$block .= $blocked_splits[0].$blocked_splits[1] ;
	    	$block .= wfRecurrentParse($blocked_splits[2], $next_level) ;
	    }

	    /* split it up further to insert the throbber - we need to put it after mw-headline */
	    $full .= "<span class=\"wikiwyg_section\" id=\"wikiwyg_section_{$section_number}\">$header_block" ;
	    $full .= $block ;
            $full .= "
</span>
<iframe class='wikiwyg_iframe'
        id=\"wikiwyg_iframe_{$section_number}\"
        height='0' width='0' 
        frameborder='0'>
</iframe>
";

        }
    }
    return $full ;
}

function beforeTidyHook($parser,$text) { 
    global $wgServer, $wgScriptPath, $wgUser ;
    if ($wgUser->getOption ('in-page', 1) == 0 ) return ;
    $wgScriptPath != "" ? $fixedPath = $wgServer."/".$wgScriptPath : $fixedPath = $wgServer ;
    /* stuff changed in MW 1.9.3, the order of elements is different now */
    $text = wfRecurrentParse ($text, 1, 0) ;
}

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (defined('MEDIAWIKI')) {
$wgExtensionFunctions[] = 'wfEZParser';

$wgAvailableRights[] = 'ezparser';

$wgGroupPermissions['ezparser']['ezparser'] = true;

function wfEZParser() {
global $IP;
require_once( $IP.'/includes/SpecialPage.php' );

#class EZParser extends UnlistedSpecialPage
class EZParser extends SpecialPage
{
	function EZParser() {
#		UnlistedSpecialPage::UnlistedSpecialPage('EZParser');
		SpecialPage::SpecialPage('EZParser');
	}

	function execute( $par ) {
		global $wgRequest, $wgOut, $wgTitle, $wgUser;
		
/*		if (!in_array( 'ezparser', $wgUser->getRights() ) ) {
			$wgOut->setArticleRelated( false );
			$wgOut->setRobotpolicy( 'noindex,follow' );
			$wgOut->errorpage( 'nosuchspecialpage', 'nospecialpagetext' );
			return;
		}
*/

		$this->setHeaders();

		$text = $wgRequest->getText( 'text' );

		if ( $text ) {
			$this->parseText( $text );
		} else {
	  		$wgOut->setArticleBodyOnly( true );
		}
	}

	function parseText($text){
	  #still need to make it actually parse the input.
	  global $wgOut, $wgUser, $wgTitle, $wgParser, $wgAllowDiffPreview, $wgEnableDiffPreviewPreference;
$parserOptions = ParserOptions::newFromUser( $wgUser );
	  $parserOptions->setEditSection( false );
          $pre_parsed = $wgParser->preSaveTransform ($text, $wgTitle, $wgUser, $parserOptions, true) ;
          $output = $wgParser->parse( $pre_parsed, $wgTitle, $parserOptions );
	  $wgOut->setArticleBodyOnly( true );

# Here we filter the output. If there's a section header in the beginning,
# we'll have an empty wikiwyg_section_0 div, and we do not want it.
# So we strip the empty span out.

          $goodHTML = str_replace("<span class=\"wikiwyg_section_0\">\n<p><!-- before block -->\n</p><p><br />\n</p><p><!-- After block -->\n</p>\n</span><iframe class=\"wikiwyg_iframe\" id=\"wikiwyg_iframe_0\" height='0' width='0' frameborder='0'></iframe>", "", $output->mText) ;
	  /* manually strip away TOC - may work for Monobook only? */
	  $goodHTML = preg_replace ('/<table id="toc".*<\/table>*.<script type="text\/javascript"> if \(window\.showTocToggle\).*<\/script>/is', "", $goodHTML) ;	
          $wgOut->addHTML($goodHTML) ; 
	}
}

global $wgMessageCache;
SpecialPage::addPage( new EZParser );
$wgMessageCache->addMessage( 'ezparser', 'Simple parser test' );

}

function wfWikiwygToggle ($toggles) {
	global $wgMessageCache ;
	$wgMessageCache->addMessages (
		array (
			'tog-in-page' => 'in-page editing' ,
			'tog-wysiwyg' => 'wysiwyg editing'
		)
	) ;
	$toggles ["wysiwyg"] = "wysiwyg" ;
	$toggles ["in-page"] = "in-page" ;
}

function wfWikiwygAddEditingPrefs ($prefsForm, $prefs) {
	$prefs = array_merge ($prefs, array (
						'in-page' ,
						'wysiwyg'	
					)) ;
}

function wfWikiwygHandleEditingPrefs () {
	global $wgOut ;
        $wgOut->addScript("
    		<script type=\"text/javascript\">
			function WikiwygEnhanceControls () {
				var inPageControl = document.getElementById ('in-page') ;
				var WysiwygControl = document.getElementById ('wysiwyg') ;
				var PreferencesSave = document.getElementById ('wpSaveprefs') ;
				inPageControl.onclick = function () {
					if (inPageControl.checked) {
						WysiwygControl.disabled = false ;
					} else {
						WysiwygControl.parentNode.style.fontColor = 'gray' ;
						WysiwygControl.checked = false ;
	                                	WysiwygControl.disabled = true ;
					}
				}
				PreferencesSave.onclick = function () {
				       Cookie.del (\"WikiwygEditMode\") ; 
				       Cookie.del (\"WikiwygFPEditMode\") ;
				}
			}
			addOnloadHook (WikiwygEnhanceControls) ;
		</script>"
	) ;
}

} # End if(defined MEDIAWIKI)

?>
