<?php

require_once 'SpecialPage.php';
require_once 'SpecialUpload.php';

$wgExtensionFunctions[] = 'MiniUploadSetup';
global $wgSpecialPages;

$wgSpecialPages['MiniUpload'] = new UnlistedSpecialPage('MiniUpload');

function MiniUploadSetup(){
  global $wgMessageCache;
  $wgMessageCache->addMessage('insertimagetitle', "Upload and insert an image inline"); 
  $wgMessageCache->addMessage('insertimagelink', "Insert image");
}

/**
 * Entry point
 */
function wfSpecialMiniUpload() {
  global $wgRequest;
  global $wgMessageCache;
  
  $wgMessageCache->addMessage('almostthere',"Step 2: Size your Image");
  $wgMessageCache->addMessage('almosttheretext','Use the slider to select a thumbnail size, enter a caption and click Insert');
  $wgMessageCache->addMessage('insertimage', "Insert Image");
  $wgMessageCache->addMessage('leftalign-tooltip', "Left align");
  $wgMessageCache->addMessage('inlinealign-tooltip', "No align, insert inline");
  $wgMessageCache->addMessage('rightalign-tooltip', "Right align");
  $wgMessageCache->addMessage('captionoptional', "Caption (optional):");
  $wgMessageCache->addMessage('thumbnailsize', "Thumbnail Size");
  $wgMessageCache->addMessage('insertfullsize', "Insert original full size image");
  $wgMessageCache->addMessage('insertthumbnail', "Insert thumbnail");
  $wgMessageCache->addMessage('uploading_status', "Uploading...");
  $wgMessageCache->addMessage('saving_status', "Saving...");
  $wgMessageCache->addMessage('wikia_licence', "All images uploaded are subject to Wikia's GNU Free Documentation License. Do not upload copyrighted images.");

  $form = new MiniUploadForm( $wgRequest );
  $form->execute();
}


#$wgExtensionFunctions[] = 'wfSpecialMiniUploadSetup';

#function wfSpecialMiniUploadSetup() {

  class MiniUploadForm extends UploadForm {
  	var $mType ;

    function MiniUploadForm( &$request ) {
      global $wgLang, $wgAllowRealName;
      global $wgRequest, $wgMessageCache ;

      $wgMessageCache->addMessages(
		      array(
			      'saveimage' => 'Step1: Upload an image' ,
			   )
		      );
      $this->mType = $request->getVal ('type') ;  
      SpecialPage::SpecialPage("MiniUpload");
      UploadForm::UploadForm($request);
    }
    
        /**
	 * There's something wrong with this file, not enough to reject it
	 * totally but we require manual intervention to save it for real.
	 * Stash it away, then present a form asking to confirm or cancel.
	 *
	 * @param string $warning as HTML
	 * @access private
	 */
	function uploadWarning( $warning ) {
		global $wgOut ;
		global $wgUseCopyrightUpload,$wgStylePath;

        $wgOut->setArticleBodyOnly(true);
        
        // because articleBodyOnly=true kills all the basic HTML headers, we have to do it ourselves
        $wgOut->addHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
   
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

<head>
<style type="text/css" >@import "'.$wgStylePath.'/common/img_success.css";</style>
        <script type="text/javascript" src="'.$wgStylePath.'/common/img_success.js"></script>
</head>
<body bgcolor=white>');
		
		$this->mSessionKey = $this->stashSession();
		if( !$this->mSessionKey ) {
			# Couldn't save file; an error has been displayed so let's go.
			return;
		}

		$wgOut->addHTML( "<h2>" . wfMsgHtml( 'uploadwarning' ) . "</h2>\n" );
		$wgOut->addHTML( "<ul class='warning'>{$warning}</ul><br />\n" );
				
		$save = wfMsgHtml ('savefile') ;

		$reupload = wfMsgHtml( 'reupload' );
		$iw = wfMsgWikiHtml( 'ignorewarning' );
		$reup = wfMsgWikiHtml( 'reuploaddesc' );
		$titleObj = Title::makeTitle( NS_SPECIAL, 'MiniUpload' );
		$action = $titleObj->escapeLocalURL( 'action=submit' );

		if ( $wgUseCopyrightUpload )
		{
			$copyright =  "
	<input type='hidden' name='wpUploadCopyStatus' value=\"" . htmlspecialchars( $this->mUploadCopyStatus ) . "\" />
	<input type='hidden' name='wpUploadSource' value=\"" . htmlspecialchars( $this->mUploadSource ) . "\" />
	";
		} else {
			$copyright = "";
		}

		$wgOut->addHTML( "
	<form id='uploadwarning' method='post' enctype='multipart/form-data' action='$action'>
		<input type='hidden' name='wpIgnoreWarning' value='1' />
		<input type='hidden' name='wpSessionKey' value=\"" . htmlspecialchars( $this->mSessionKey ) . "\" />
		<input type='hidden' name='wpUploadDescription' value=\"" . htmlspecialchars( $this->mUploadDescription ) . "\" />
		<input type='hidden' name='wpLicense' value=\"" . htmlspecialchars( $this->mLicense ) . "\" />
		<input type='hidden' name='wpDestFile' value=\"" . htmlspecialchars( $this->mDestFile ) . "\" />
		<input type='hidden' name='wpWatchthis' value=\"" . htmlspecialchars( intval( $this->mWatchthis ) ) . "\" />
	{$copyright}
	<table border='0'>
		<tr>
			<tr>
				<td align='right'>
					<input tabindex='2' type='submit' name='wpUpload' value='$save' onclick='clickSave()'/>
				</td>
				<td align='left'>$iw</td>
			</tr>
			<tr>
				<td align='right'>
					<input tabindex='2' type='submit' name='wpReUpload' value='{$reupload}' />
				</td>
				<td align='left'>$reup</td>
			</tr>
			<tr>
            <td align='left' valign='middle'><div id='saveStatus' style='display: none;'><img style='vertical-align: bottom;' src='$wgStylePath/common/progress-wheel.gif'>&nbsp;" . wfMsgHtml('saving_status') ."</div></td>
<td align='left'></td>
			</tr>
		</tr>
	</table></form>\n" );
	
	   $wgOut->addHTML("<script language='javascript'>\n
//<!--
function clickSave()
{
    document.getElementById('saveStatus').style.display = 'block';
}
//-->
</script>");
	
	   $wgOut->addHTML("</body></html>");
	}

function execute() {
	global $wgUser, $wgOut;
	global $wgEnableUploads, $wgUploadDirectory;
	global $wgArticlePath ;

	# Check uploading enabled
	if( !$wgEnableUploads ) {
		$wgOut->showErrorPage( 'uploaddisabled', 'uploaddisabledtext' );
		return;
	}

	# Check permissions
	if( !$wgUser->isAllowed( 'upload' ) ) {
		if( !$wgUser->isLoggedIn() ) {
			/* we would want to return to our page after login, yes? */
			$fixed_return = wfMsg ('uploadnologintext') ;
			$fixed_link = "<a href=\"".$wgArticlePath."Special:Userlogin?returnto=Special:MiniUpload\">logged in</a>";
			$fixed_return = preg_replace ("/\[\[Special:Userlogin.*\]\]/i", $fixed_link, $fixed_return) ;
			global $wgTitle;

			$wgOut->mDebugtext .= 'Original title: ' .
			$wgTitle->getPrefixedText() . "\n";
			$wgOut->setPageTitle( wfMsg( 'uploadnologin' ) );
			$wgOut->setHTMLTitle( wfMsg( 'errorpagetitle' ) );
			$wgOut->setRobotpolicy( 'noindex,nofollow' );
			$wgOut->setArticleRelated( false );
			$wgOut->enableClientCache( false );
			$wgOut->mRedirect = '';

			$wgOut->mBodytext = '';
			
			$wgOut->addHTML( $fixed_return );
			$wgOut->returnToMain( false );
		} else {
			$wgOut->permissionRequired( 'upload' );
		}
		return;
	}

	# Check blocks
	if( $wgUser->isBlocked() ) {
		$wgOut->blockedPage();
		return;
	}

	if( wfReadOnly() ) {
		$wgOut->readOnlyPage();
		return;
	}

	/** Check if the image directory is writeable, this is a common mistake */
	if( !is_writeable( $wgUploadDirectory ) ) {
		$wgOut->addWikiText( wfMsg( 'upload_directory_read_only', $wgUploadDirectory ) );
		return;
	}

	if( $this->mReUpload ) {
		if( !$this->unsaveUploadedFile() ) {
			return;
		}
		$this->mainUploadForm();
	} else if( 'submit' == $this->mAction || $this->mUpload ) {
		$this->processUpload();
	} else {
		$this->mainUploadForm();
	}
	
	$this->cleanupTempFile();
}

    /**
	 * @param string $error as HTML
	 * @access private
	 */
	function uploadError( $error ) {
		global $wgOut,$wgStylePath;
  
        $wgOut->setArticleBodyOnly(true);
        
        // because articleBodyOnly=true kills all the basic HTML headers, we have to do it ourselves
        $wgOut->addHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
   
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

<head>
<style type="text/css" >@import "'.$wgStylePath.'/common/img_success.css";</style>
        <script type="text/javascript" src="'.$wgStylePath.'/common/img_success.js"></script>
</head>
<body bgcolor=white>');
        
        #$wgOut->addHTML("<b><h2>UPLOAD ERROR</h2></b>");
        #$wgOut->addHTML("<script>alert('upload error');</script>");
        UploadForm::uploadError($error);
        
        $wgOut->addHTML("</body></html>");
	}
	
	/**
     * Show some text and linkage on successful upload.
     * @access private
     */
    function showSuccess() {
        global $wgUser, $wgOut, $wgContLang,$wgStylePath;
        
        $img = Image::newFromName( $this->mUploadSaveName );
        
        $wgOut->setArticleBodyOnly(true);
        
        // because articleBodyOnly=true kills all the basic HTML headers, we have to do it ourselves
        $wgOut->addHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
   
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

<head>
<style type="text/css" >@import "'.$wgStylePath.'/common/img_success.css";</style>
        <script type="text/javascript" src="'.$wgStylePath.'/common/img_success.js"></script>
</head>
<body bgcolor=white onload="initImageInsert()">');
        $sk = $wgUser->getSkin();
        $ilink = $sk->makeMediaLink( $this->mUploadSaveName, $img->getEscapeFullURL() );
        $dname = $wgContLang->getNsText( NS_IMAGE ) . ':'.$this->mUploadSaveName;
        $dlink = $sk->makeKnownLink( $dname, $dname );
        
        $wgOut->addHTML('<p><input type="hidden" value="' . $this->mUploadSaveName . '" id="imgName"/></p>');
        $wgOut->addHTML( '<h2>' . wfMsgHtml( 'almostthere' ) . "</h2>\n" );
        $text = wfMsgWikiHtml( 'almosttheretext', '<a href="'.$img->getEscapeFullURL() . '" target="__blank" >' . $dname . '</a>' );
        $wgOut->addHTML( $text );
        
        $wgOut->addHTML('<input type="radio" name="useThumbnails" value="Original" id="insertFullSize" onclick="insertRadioSelect(this,event)">' . wfMsgHtml('insertfullsize') . '<br>
<input type="radio" name="useThumbnails" id="insertThumbnail" checked=true value="Thumbnail" onclick="insertRadioSelect(this,event)">' . wfMsgHtml('insertthumbnail') . '<br>
<p></p>');
        
        $wgOut->addHTML('<div id="userControls">
    <div id="thumbSizeLabel">' . wfMsgHtml("thumbnailsize") . '</div>
    <img src="'.$wgStylePath.'/common/slider_groove.png" id="groove" />
    <img src="'.$wgStylePath.'/common/slider_thumb_bg.png" id="slider" style="left:205px;" />
    <div id="thumbSizeValue"></div>');
    
        $wgOut->addHTML('
    <div id="alignControls">
        <img class="alignControl" title="'. wfMsgHtml("leftalign-tooltip") .'" src="'.$wgStylePath.'/common/align_left.png" onclick="setAlignment(\'left\')" />
        <img class="alignControl" title="'. wfMsgHtml("rightalign-tooltip") .'" src="'.$wgStylePath.'/common/align_right.png" onclick="setAlignment(\'right\')" />
    </div>
</div>
<div id="wrapper" style="text-align:center; width:450px; height:200px;">
    <img id="imgThumbnail" wpWidth=' . $img->getWidth() . ' wpHeight=' . $img->getHeight() . ' style="width:50px; height:50px; background-color:#bbb; margin:5px; float: left; clear:none;" src="' . htmlspecialchars($img->getURL()) .'"/>	
</div>
 
<div id="captionDiv">
<label for=\'captionText\'>
' . wfMsgHtml("captionoptional") .
'<input type=text value="" name="captionText" id="captionText" style="\'Lucida Grande\', Verdana, Arial, sans-serif; padding: 5px; width: 310px; font-size: 13px; border: 1px solid #999; background-color: #fff">
</label>
</div>

<br/>
<p style="text-align:center;">');
        
        $wgOut->addHTML("<input type=button value='" . wfMsgHtml("insertimage") ."' onclick=\"doInsertImage('"  . $wgContLang->getNsText(NS_IMAGE). "')\" />
</p>");
        $wgOut->addHTML("\n</body></html>");
    }
    
    /**
     * Displays the main upload form, optionally with a highlighted
     * error message up at the top.
     *
     * @param string $msg as HTML
     * @access private
     */
    function mainUploadForm( $msg='' ) {
      global $wgOut, $wgUser,$wgStylePath;
      global $wgUseCopyrightUpload;

      $wgOut->setArticleBodyOnly(true);
      
      // because articleBodyOnly=true kills all the basic HTML headers, we have to do it ourselves
        $wgOut->addHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
   
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

<head>
<style type="text/css" >@import "'.$wgStylePath.'/common/img_success.css";</style>
        <script type="text/javascript" src="'.$wgStylePath.'/common/img_success.js"></script>
</head>
<body bgcolor=white>');

      $cols = intval($wgUser->getOption( 'cols' ));
      $ew = $wgUser->getOption( 'editwidth' );
      if ( $ew ) $ew = " style=\"width:100%\"";
      else $ew = '';

      if ( '' != $msg ) {
	$sub = wfMsgHtml( 'uploaderror' );
	$wgOut->addHTML( "<h2>{$sub}</h2>\n" .
			 "<span class='error'>{$msg}</span>\n" );
      }
      #$wgOut->addHTML( '<div id="uploadtext">' );
      #$wgOut->addWikiText( wfMsg( 'uploadtext' ) );
      #$wgOut->addHTML( '</div>' );
      $sk = $wgUser->getSkin();

      $sourcefilename = 'File';
      //wfMsgHtml( 'sourcefilename' );
      $destfilename = wfMsgHtml( 'destfilename' );
      $summary = wfMsgWikiHtml( 'fileuploadsummary' );

      $licenses = new Licenses();
      $license = wfMsgHtml( 'license' );
      $nolicense = wfMsgHtml( 'nolicense' );
      $licenseshtml = $licenses->getHtml();

      if ($this->mType == 'image') {
              $ulb = wfMsgHtml ('saveimage') ;
	      $ulTitle = $ulb ;
      } else {
	      $ulb = wfMsgHtml ('uploadbtn') ;
	      $ulTitle = wfMsgHtml ('upload') ;
      }

      $titleObj = Title::makeTitle( NS_SPECIAL, 'MiniUpload' );
      $action = $titleObj->escapeLocalURL();

      $encDestFile = htmlspecialchars( $this->mDestFile );

      $watchChecked = $wgUser->getOption( 'watchdefault' )
	? 'checked="checked"'
	: '';

      $wgOut->addHTML( "
<h1>$ulTitle</h1>
<form id='upload' method='post' enctype='multipart/form-data' action=\"$action\">
<table border='0'>
<tr>
<td align='right'><label for='wpUploadFile'>{$sourcefilename}:</label></td>
<td align='left'>
<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' " . ($this->mDestFile?"":"onchange='fillDestFilename()' ") . "size='30' />
</td>
</tr>");
      
        $wgOut->addHTML("<!--
        <tr>
	<td align='right'>
            <label for='wpDestFile'>{$destfilename}:</label></td>
<td align='left'> -->
<!-- input tabindex='2' type='text' name='wpDestFile' id='wpDestFile' size='30' value=\"$encDestFile\" / -->

<input type='hidden' name='wpDestFile' id='wpDestFile' value=\"$encDestFile\" />

<!--
</td>
</tr>
-->");	

/*	$wgOut->addHTML("
<tr>
<td align='right'><label for='wpUploadDescription'>{$summary}</label></td>
<td align='left'>
<textarea tabindex='3' name='wpUploadDescription' id='wpUploadDescription' rows='3' cols='30'{$ew}>" . htmlspecialchars( $this->mUploadDescription ) . "</textarea>
</td>
</tr>
<tr>" );

	if ( $licenseshtml != '' ) {
	  global $wgStylePath;
	  $wgOut->addHTML( "
<td align='right'><label for='wpLicense'>$license:</label></td>
<td align='left'>
<script type='text/javascript' src=\"$wgStylePath/common/upload.js\"></script>
<select name='wpLicense' id='wpLicense' width='270' style='width:270px;' tabindex='4'
onchange='licenseSelectorCheck()'>
<option value=''>$nolicense</option>
$licenseshtml
</select>
</td>
</tr>
<tr>
");
	} */
	
	$wgOut->addHtml( '<tr><td colspan="2">' . wfMsgHtml( 'wikia_licence' ) . '<td></tr>' );

	if ( $wgUseCopyrightUpload ) {
	  $filestatus = wfMsgHtml ( 'filestatus' );
	  $copystatus =  htmlspecialchars( $this->mUploadCopyStatus );
	  $filesource = wfMsgHtml ( 'filesource' );
	  $uploadsource = htmlspecialchars( $this->mUploadSource );
	  
	  $wgOut->addHTML( " <tr>
        <td align='right' nowrap='nowrap'><label for='wpUploadCopyStatus'>$filestatus:</label></td>
        <td><input tabindex='5' type='text' name='wpUploadCopyStatus' id='wpUploadCopyStatus' value=\"$copystatus\" size='40' /></td>
        </tr>
<tr>
        <td align='right'><label for='wpUploadCopyStatus'>$filesource:</label></td>
        <td><input tabindex='6' type='text' name='wpUploadSource' id='wpUploadCopyStatus' value=\"$uploadsource\" size='40' /></td>
</tr>
<tr>
");
	}


	$wgOut->addHtml( "
<td></td>
<td>
<input tabindex='7' type='checkbox' name='wpWatchthis' id='wpWatchthis' $watchChecked value='true' />
<label for='wpWatchthis'>" . wfMsgHtml( 'watchthis' ) . "</label>
<input tabindex='8' type='checkbox' name='wpIgnoreWarning' id='wpIgnoreWarning' value='true' />
<label for='wpIgnoreWarning'>" . wfMsgHtml( 'ignorewarnings' ) . "</label>
</td>
</tr>
<tr>

</tr>
<tr>
<td align='left' valign='middle'><div id='uploadStatus' style='display: none;'><img style='vertical-align: bottom;' src='$wgStylePath/common/progress-wheel.gif'>&nbsp;" . wfMsgHtml('uploading_status') ."</div></td>
<td align='left'><input tabindex='9' onclick='clickSubmit()' type='submit' name='wpUpload' value=\"{$ulb}\" /></td>
</tr>

<tr>
<td></td>
<td align='left' style='font-size: 0.5em;'>
" );
	$wgOut->addWikiText( wfMsgForContent( 'edittools' ) );
	$wgOut->addHTML( "
</td>
</tr>

</table>
</form>" );

        $wgOut->addHTML("<script language='javascript'>\n
//<!--
function clickSubmit()
{
    document.getElementById('uploadStatus').style.display = 'block';
}
//-->
</script>");
        $wgOut->addHTML("\n</body></html>");
    }
    
    /**
	 * Really do the upload
	 * Checks are made in SpecialUpload::execute()
	 * @access private
	 */
	function processUpload() {
		global $wgUser, $wgOut;

		/* Check for PHP error if any, requires php 4.2 or newer */
		if ( $this->mUploadError == 1/*UPLOAD_ERR_INI_SIZE*/ ) {
			$this->mainUploadForm( wfMsgHtml( 'largefileserver' ) );
			return;
		}

		/**
		 * If there was no filename or a zero size given, give up quick.
		 */
		if( trim( $this->mOname ) == '' || empty( $this->mUploadSize ) ) {
			$this->mainUploadForm( wfMsgHtml( 'emptyfile' ) );
			return;
		}

		# Chop off any directories in the given filename
		if ( $this->mDestFile ) {
			$basename = wfBaseName( $this->mDestFile );
		} else {
			$basename = wfBaseName( $this->mOname );
		}

		/**
		 * We'll want to blacklist against *any* 'extension', and use
		 * only the final one for the whitelist.
		 */
		list( $partname, $ext ) = $this->splitExtensions( $basename );
		
		if( count( $ext ) ) {
			$finalExt = $ext[count( $ext ) - 1];
		} else {
			$finalExt = '';
		}
		$fullExt = implode( '.', $ext );

		# If there was more than one "extension", reassemble the base
		# filename to prevent bogus complaints about length
		if( count( $ext ) > 1 ) {
			for( $i = 0; $i < count( $ext ) - 1; $i++ )
				$partname .= '.' . $ext[$i];
		}

		if ( strlen( $partname ) < 3 ) {
			$this->mainUploadForm( wfMsgHtml( 'minlength' ) );
			return;
		}

		/**
		 * Filter out illegal characters, and try to make a legible name
		 * out of it. We'll strip some silently that Title would die on.
		 */
		$filtered = preg_replace ( "/[^".Title::legalChars()."]|:/", '-', $basename );
		$nt = Title::newFromText( $filtered );
		if( is_null( $nt ) ) {
			$this->uploadError( wfMsgWikiHtml( 'illegalfilename', htmlspecialchars( $filtered ) ) );
			return;
		}
		$nt =& Title::makeTitle( NS_IMAGE, $nt->getDBkey() );
		$this->mUploadSaveName = $nt->getDBkey();

		/**
		 * If the image is protected, non-sysop users won't be able
		 * to modify it by uploading a new revision.
		 */
		if( !$nt->userCanEdit() ) {
			return $this->uploadError( wfMsgWikiHtml( 'protectedpage' ) );
		}

		/**
		 * In some cases we may forbid overwriting of existing files.
		 */
		$overwrite = $this->checkOverwrite( $this->mUploadSaveName );
		if( WikiError::isError( $overwrite ) ) {
			return $this->uploadError( $overwrite->toString() );
		}

		/* Don't allow users to override the blacklist (check file extension) */
		global $wgStrictFileExtensions;
		global $wgFileExtensions, $wgFileBlacklist;
		if( $this->checkFileExtensionList( $ext, $wgFileBlacklist ) ||
			($wgStrictFileExtensions &&
				!$this->checkFileExtension( $finalExt, $wgFileExtensions ) ) ) {
			return $this->uploadError( wfMsgHtml( 'badfiletype', htmlspecialchars( $fullExt ) ) );
		}

		/**
		 * Look at the contents of the file; if we can recognize the
		 * type but it's corrupt or data of the wrong type, we should
		 * probably not accept it.
		 */
		if( !$this->mStashed ) {
			$this->checkMacBinary();
			$veri = $this->verify( $this->mUploadTempName, $finalExt );

			if( $veri !== true ) { //it's a wiki error...
				return $this->uploadError( $veri->toString() );
			}
		}

		/**
		 * Provide an opportunity for extensions to add futher checks
		 */
		$error = '';
		if( !wfRunHooks( 'UploadVerification',
				array( $this->mUploadSaveName, $this->mUploadTempName, &$error ) ) ) {
			return $this->uploadError( $error );
		}

		/**
		 * Check for non-fatal conditions
		 */
		if ( ! $this->mIgnoreWarning ) {
			$warning = '';

			global $wgCapitalLinks;
			if( $wgCapitalLinks ) {
				$filtered = ucfirst( $filtered );
			}
			if( $this->mUploadSaveName != $filtered ) {
				$warning .=  '<li>'.wfMsgHtml( 'badfilename', htmlspecialchars( $this->mUploadSaveName ) ).'</li>';
			}

			global $wgCheckFileExtensions;
			if ( $wgCheckFileExtensions ) {
				if ( ! $this->checkFileExtension( $finalExt, $wgFileExtensions ) ) {
					$warning .= '<li>'.wfMsgHtml( 'badfiletype', htmlspecialchars( $fullExt ) ).'</li>';
				}
			}

			global $wgUploadSizeWarning;
			if ( $wgUploadSizeWarning && ( $this->mUploadSize > $wgUploadSizeWarning ) ) {
				# TODO: Format $wgUploadSizeWarning to something that looks better than the raw byte
				# value, perhaps add GB,MB and KB suffixes?
				$warning .= '<li>'.wfMsgHtml( 'large-file', $wgUploadSizeWarning, $this->mUploadSize ).'</li>';
			}
			if ( $this->mUploadSize == 0 ) {
				$warning .= '<li>'.wfMsgHtml( 'emptyfile' ).'</li>';
			}

			if( $nt->getArticleID() ) {
				global $wgUser;
				$sk = $wgUser->getSkin();
				$dlink = $sk->makeKnownLinkObj( $nt );
				$warning .= '<li>'.wfMsgHtml( 'fileexists', $dlink ).'</li>';
			}

			if( $warning != '' ) {
				/**
				 * Stash the file in a temporary location; the user can choose
				 * to let it through and we'll complete the upload then.
				 */
				return $this->uploadWarning( $warning );
			}
		}
	
		/**
		 * Try actually saving the thing...
		 * It will show an error form on failure.
		 */
		$hasBeenMunged = !empty( $this->mSessionKey ) || $this->mRemoveTempFile;
		if( $this->saveUploadedFile( $this->mUploadSaveName,
		                             $this->mUploadTempName,
		                             $hasBeenMunged ) ) {
			/**
			 * Update the upload log and create the description page
			 * if it's a new file.
			 */
			$img = Image::newFromName( $this->mUploadSaveName );
			$oldredirval = $wgOut->enableRedirects(false);
			$success = $img->recordUpload( $this->mUploadOldVersion,
			                                $this->mUploadDescription,
			                                $this->mLicense,
			                                $this->mUploadCopyStatus,
			                                $this->mUploadSource,
			                                $this->mWatchthis );
			$wgOut->enableRedirects($oldredirval);
			if ( $success ) {
				$this->showSuccess();
			} else {
				// Image::recordUpload() fails if the image went missing, which is
				// unlikely, hence the lack of a specialised message
				$wgOut->fileNotFoundError( $this->mUploadSaveName );
			}
		}
	}

    
  }


#  SpecialPage::addPage( new MiniUploadForm );
#  global $wgMessageCache;
#  $wgMessageCache->addMessage( 'miniuploadform', 'Mini Upload Form' );
#}


?>
