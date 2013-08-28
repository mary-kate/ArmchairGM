<?PHP

global $wgHooks;
$wgHooks['EditPage::showEditForm:initial'][] = 'WikiwygAlternateEdit' ;

$wgExtensionFunctions[] = 'registerWikiwygEditing';
$wgExtensionCredits['other'][] = array(
    'name' => 'WikiwygEditing' ,
    'author' => 'Bartek' ,
    'version' => 1.0 ,
    'url' => 'http://www.wikia.com' ,
    'description' => 'Mediawiki integration of the Wikiwyg WYSIWYG wiki editor - one more time '
);

function registerWikiwygEditing () {
}

function WikiwygAlternateEdit () {
    global $wgOut,$wgSkin,$jsdir,$cssdir;
    global $wgWikiwygPath;
    global $wgServer,$wgWikiwygJsPath,$wgWikiwygCssPath,$wgWikiwygImagePath;
    global $wgUser ;

    /* in-page disabled automatically disables this loading */
    if ($wgUser->getOption ('in-page',1) == 0 ) {
	return ;
    }
    if (! isset($wgWikiwygPath)) {
        $wgWikiwygPath = "$wgServer/wikiwyg";
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
    $wgOut->addScript("<script type=\"text/javascript\" src=\"$IP/extensions/WikiwygEditing/js/editpage.js\"></script>\n");

    $wgOut->addScript("
<script type=\"text/javascript\">
    if (typeof(Wikiwyg) == 'undefined') Wikiwyg = function() {};
    Wikiwyg.mediawiki_source_path = \"$wgWikiwygPath\";
</script>
");
    $wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/MediaWikiWyg.js\"></script>\n");
    $wgOut->addHTML ("<div id=\"backup_textarea_placeholder\"></div>") ;

}

?>
