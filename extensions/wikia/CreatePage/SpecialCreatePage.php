<?php

/*
	A special page to create a new article, attempting to use wikiwyg, a wysiwig wikitext editor
*/
if(!defined('MEDIAWIKI'))
   die();

$wgExtensionFunctions[] = 'wfCreatePageSetup';
$wgExtensionCredits['specialpage'][] = array(
   'name' => 'Create Page',
   'author' => 'Bartek',
   'description' => 'allows to create a new page - with the wysiwyg editor '
);

/* special page init */
function wfCreatePageSetup() {
	global $IP, $wgMessageCache, $wgOut ;
	require_once($IP. '/includes/SpecialPage.php');

        /* add messages to all the translator people out there to play with */
        $wgMessageCache->addMessages(
        array(
                        'createpage_button' => 'Create a new article' ,
			/* blank as a newborn's mind, for the Comm Team to fill I guess */
			'createpage_help' => '' ,
			'createpage_caption' => 'title' ,
			'createpage_button_caption' => 'Create article' ,
			'createpage_title' => 'Create a new article' ,
			'createpage_categories' => 'Categories:' ,
			'createpage_title_caption' => 'Title:' ,
			'createpage_loading_mesg' => 'Loading... please wait...' ,
			'createpage_enter_text' => 'Enter text here:' ,
			'createpage_show_help' => '[show help]' ,
			'createpage_hide_help' => '[hide help]' ,
			'createpage_categories_help' => 'You can add different categories here. They will be added to the article after creation. Separate them with commas.' 
                )
        );
	SpecialPage::addPage(new SpecialPage('Createpage', '', true, 'wfCreatePageSpecial', false));
	$wgMessageCache->addMessage('createpage', 'Create a new article');
}

/* the core */
function wfCreatePageSpecial( $par ) {
	global $wgOut, $wgUser, $wgRequest, $wgServer ;

	if (! isset($wgWikiwygPath)) {
		$wgWikiwygPath = "/extensions/wikiwyg";
	}
	if (! isset($wgWikiwygJsPath)) {
		$wgWikiwygJsPath = "$wgWikiwygPath/lib";
	}
	if (! isset($wgWikiwygCssPath)) {
		$wgWikiwygCssPath = "$wgWikiwygPath/share/MediaWiki/css";
	}
	if (! isset($wgWikiwygImagePath)) {
		$wgWikiwygImagePath = "$wgWikiwygPath/images";
	}

    $wgOut->addScript("<style type=\"text/css\" media=\"screen,projection\">/*<![CDATA[*/ @import \"$wgWikiwygCssPath/MediaWikiwyg.css\"; /*]]>*/</style>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/Wikiwyg.js\"></script>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/Wikiwyg/Toolbar.js\"></script>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/Wikiwyg/Wysiwyg.js\"></script>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/Wikiwyg/Wikitext.js\"></script>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"$wgWikiwygJsPath/Wikiwyg/Util.js\"></script>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/CreatePage/js/createpage.js\"></script>\n");

   	$wgOut->setPageTitle (wfMsg('createpage_title'));
	$cSF = new CreatePageForm ($par) ;

	$action = $wgRequest->getVal ('action') ;
	if ('success' == $action) {
		/* do something */
	} else if ( $wgRequest->wasPosted() && 'submit' == $action &&
	        $wgUser->matchEditToken( $wgRequest->getVal ('wpEditToken') ) ) {
	        $cSF->doSubmit () ;
	} else if ('failure' == $action) {
		$cSF->showForm ('Please specify title') ;
	} else if ('check' == $action) {		
		$cSF->checkArticleExists ($wgRequest->getVal ('to_check')) ;
	} else {
		$cSF->showForm ('') ;
	}
}

/* the form for blocking names and addresses */
class CreatePageForm {
	var $mMode, $mLink, $mDo, $mFile ;

	/* constructor */
	function CreatePageForm ( $par ) {
		global $wgRequest ;
	}

	/* output */
	function showForm ( $err ) {
		global $wgOut, $wgUser, $wgRequest ;
	
		$token = htmlspecialchars( $wgUser->editToken() );
		$titleObj = Title::makeTitle( NS_SPECIAL, 'Createpage' );
		$action = $titleObj->escapeLocalURL( "action=submit" ) ;

                if ( "" != $err ) {
                        $wgOut->setSubtitle( wfMsgHtml( 'formerror' ) );
                        $wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
                }
	
	       	$edittime =  wfTimestamp(TS_MW, $this->mTimestamp) ;
   		$wgOut->addHtml("
<form name=\"editform\" method=\"post\" action=\"{$action}\">
	<div id=\"createpage_messenger\" style=\"display:none; color:red \" ></div>
	<input type=\"submit\" name=\"wpSave\" id=\"wpSaveUp\"  value=\"".wfMsg ('createpage_button_caption') ."\" /><br/>".
        wfMsg ('createpage_title_caption') 
	."<br/>
	<input name=\"title\" id=\"title\" value=\"\" size=\"100\" /><br/><br/>".		
             	wfMsg ('createpage_enter_text')
		."
		<span style='text-align:right;' id=\"image_upload\"><a title='Upload and insert an image inline' href=\"javascript:specialCreatePageImageUpload('[[Image:', ']]');\">Insert image</a></span>
		<br><div id=\"wikiwyg\"></div>
		<div id=\"loading_mesg\"><b>".wfMsg('createpage_loading_mesg')."</b></div>

		<noscript>
		<style type=\"text/css\">
			#loading_mesg, #image_upload {
				display: none ;
			}
		</style>
		<textarea tabindex=\"1\" accesskey=\",\" name=\"wpTextbox1\" id=\"wpTextbox1\" rows=\"25\" cols=\"80\" ></textarea>
		</noscript>
		<div id=\"backup_textarea_placeholder\"></div>
		<iframe id=\"wikiwyg-iframe\" height=\"0\" width=\"0\" frameborder=\"0\"></iframe>
		<input type=\"hidden\" value=\"{$this->edittime}\" name=\"wpEdittime\" />
		<br/>
		<div id=\"category_wrapper\" style=\"display:none\">".
		wfMsg ('createpage_categories') .
		"&#160;
		<a href=\"#\" id=\"createpage_show_help\" onclick=\"CreatePageShowHelp(); return false;\">".wfMsg('createpage_show_help')."</a>
		<a href=\"#\" id=\"createpage_hide_help\" onclick=\"CreatePageHideHelp(); return false;\" style=\"display:none\">".wfMsg('createpage_hide_help')."</a>
                <div id=\"createpage_help_section\" style=\"display:none\">".wfMsg ('createpage_categories_help')."</div>
		<br/><textarea name=\"category\" id=\"category\" rows=\"1\" cols=\"80\" /></textarea><br/>
		</div><br/>
		<input type=\"submit\" name=\"wpSave\" id=\"wpSaveBottom\"  value=\"".wfMsg ('createpage_button_caption') ."\" />
	<input type='hidden' name='wpEditToken' value=\"{$token}\" />
	<input type=\"hidden\" name=\"wpCreatePage\" value=\"true\" />
</form>");
	}

        /* draws select and selects it properly */
        function makeSelect ($name, $options_array, $current, $tabindex) {
                global $wgOut ;
                $wgOut->addHTML ("<select tabindex=\"$tabindex\" name=\"$name\" id=\"$name\">") ;
                foreach ($options_array as $key => $value) {
                        if ($value == $current )
                                $wgOut->addHTML ("<option value=\"$value\" selected=\"selected\">$key</option>") ;
                        else
                                $wgOut->addHTML ("<option value=\"$value\">$key</option>") ;
                }
                $wgOut->addHTML ("</select>") ;
        }

	/* check if article exists */
	function checkArticleExists ($given) {
		global $wgOut ;
		$wgOut->setArticleBodyOnly( true );	
		$title = Title::newFromText( $given );
		$page = $title->getText () ;
		$page = str_replace( ' ', '_', $page ) ;
		$dbr =& wfGetDB (DB_SLAVE);
		$exists = $dbr->selectField ('page', 'page_title', array ('page_title' => $page)) ;
		if ($exists != '')
		$wgOut->addHTML('pagetitleexists');
	}

	/* on success */
	function showSuccess () {
		global $wgOut, $wgRequest ;
		$wgOut->setPageTitle (wfMsg('createpage_success_title') ) ;
		$wgOut->setSubTitle(wfMsg('createpage_success_subtitle')) ;	
	}


	/* on submit */
	function doSubmit () {
		global $wgOut, $wgUser, $wgRequest ;
		$wgOut->setSubTitle ( wfMsg ('createpage_success_subtitle', wfMsg('createpage_'.$this->mMode) ) ) ;
	}
}

?>
