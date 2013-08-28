<?
class AjaxUploadForm extends UploadForm{
	
	/**
	 * Really do the upload
	 * Checks are made in SpecialUpload::execute()
	 * @access private
	 */
	function processUpload() {
		global $wgUser, $wgOut, $wgRequest;
		
		$isOverwrite = $wgRequest->getVal( 'wpOverwriteFile' );
		$overwriteName = $wgRequest->getVal( 'wpDestFile' );
		$editName = $wgRequest->getVal( 'wpEditUploadName' );
		
		if( !wfRunHooks( 'UploadForm:BeforeProcessing', array( &$this ) ) )
		{
			wfDebug( "Hook 'UploadForm:BeforeProcessing' broke processing the file." );
			return false;
		}

		/* Check for PHP error if any, requires php 4.2 or newer */
		if( $this->mUploadError == 1/*UPLOAD_ERR_INI_SIZE*/ ) {
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
		if( $this->mDestFile ) {
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

		# If there was more than one "extension", reassemble the base
		# filename to prevent bogus complaints about length
		if( count( $ext ) > 1 ) {
			for( $i = 0; $i < count( $ext ) - 1; $i++ )
				$partname .= '.' . $ext[$i];
		}

		if( strlen( $partname ) < 3 ) {
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
		if( !$nt->userCan( 'edit' ) ) {
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
		if ($finalExt == '') {
			return $this->uploadError( wfMsgExt( 'filetype-missing', array ( 'parseinline' ) ) );
		} elseif ( $this->checkFileExtensionList( $ext, $wgFileBlacklist ) ||
				($wgStrictFileExtensions &&
					!$this->checkFileExtension( $finalExt, $wgFileExtensions ) ) ) {
			return $this->uploadError( wfMsgExt( 'filetype-badtype', array ( 'parseinline' ), htmlspecialchars( $finalExt ), implode ( ', ', $wgFileExtensions ) ) );
		}

		/**
		 * Look at the contents of the file; if we can recognize the
		 * type but it's corrupt or data of the wrong type, we should
		 * probably not accept it.
		 */
		if( !$this->mStashed ) {
			
			if($isOverwrite){
				
				list( $partname, $ext ) = $this->splitExtensions( $editName );
				
				if( count( $ext ) ) {
					$editExt = $ext[count( $ext ) - 1];
				} else {
					$editExt = '';
				}
				
				if($finalExt != $editExt){
					$finalExt = $editExt;
					list( $partname, $ext ) = $this->splitExtensions( $this->mUploadSaveName );
					
					$this->mUploadSaveName = $partname . "." . $finalExt;
					
				}
			}
			
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
					$warning .= '<li>'.wfMsgExt( 'filetype-badtype', array ( 'parseinline' ), htmlspecialchars( $finalExt ), implode ( ', ', $wgFileExtensions ) ).'</li>';
				}
			}

			global $wgUploadSizeWarning;
			if ( $wgUploadSizeWarning && ( $this->mUploadSize > $wgUploadSizeWarning ) ) {
				$skin = $wgUser->getSkin();
				$wsize = $skin->formatSize( $wgUploadSizeWarning );
				$asize = $skin->formatSize( $this->mUploadSize );
				$warning .= '<li>' . wfMsgHtml( 'large-file', $wsize, $asize ) . '</li>';
			}
			if ( $this->mUploadSize == 0 ) {
				$warning .= '<li>'.wfMsgHtml( 'emptyfile' ).'</li>';
			}

			global $wgUser;
			$sk = $wgUser->getSkin();
			$image = new Image( $nt );

			// Check for uppercase extension. We allow these filenames but check if an image
			// with lowercase extension exists already
			if ( $finalExt != strtolower( $finalExt ) ) {
				$nt_lc = Title::newFromText( $partname . '.' . strtolower( $finalExt ) );
				$image_lc = new Image( $nt_lc );
			}

			if( $image->exists() ) {
				$dlink = $sk->makeKnownLinkObj( $nt );
				if ( $image->allowInlineDisplay() ) {
					$dlink2 = $sk->makeImageLinkObj( $nt, wfMsgExt( 'fileexists-thumb', 'parseinline', $dlink ), $nt->getText(), 'right', array(), false, true );
				} elseif ( !$image->allowInlineDisplay() && $image->isSafeFile() ) {
					$icon = $image->iconThumb();
					$dlink2 = '<div style="float:right" id="mw-media-icon"><a href="' . $image->getURL() . '">' . $icon->toHtml() . '</a><br />' . $dlink . '</div>';
				} else {
					$dlink2 = '';
				}

				$warning .= '<li>' . wfMsgExt( 'fileexists', 'parseline', $dlink ) . '</li>' . $dlink2;

			} elseif ( isset( $image_lc) && $image_lc->exists() ) {
				# Check if image with lowercase extension exists.
				# It's not forbidden but in 99% it makes no sense to upload the same filename with uppercase extension
				$dlink = $sk->makeKnownLinkObj( $nt_lc );
				if ( $image_lc->allowInlineDisplay() ) {
					$dlink2 = $sk->makeImageLinkObj( $nt_lc, wfMsgExt( 'fileexists-thumb', 'parseinline', $dlink ), $nt_lc->getText(), 'right', array(), false, true );
				} elseif ( !$image_lc->allowInlineDisplay() && $image_lc->isSafeFile() ) {
					$icon = $image_lc->iconThumb();
					$dlink2 = '<div style="float:right" id="mw-media-icon"><a href="' . $image_lc->getURL() . '">' . $icon->toHtml() . '</a><br />' . $dlink . '</div>';
				} else {
					$dlink2 = '';
				}

				$warning .= '<li>' . wfMsgExt( 'fileexists-extension', 'parsemag' , $partname . '.' . $finalExt , $dlink ) . '</li>' . $dlink2;				

			} elseif ( ( substr( $partname , 3, 3 ) == 'px-' || substr( $partname , 2, 3 ) == 'px-' ) && ereg( "[0-9]{2}" , substr( $partname , 0, 2) ) ) {
				# Check for filenames like 50px- or 180px-, these are mostly thumbnails
				$nt_thb = Title::newFromText( substr( $partname , strpos( $partname , '-' ) +1 ) . '.' . $finalExt );
				$image_thb = new Image( $nt_thb );
				if ($image_thb->exists() ) {
					# Check if an image without leading '180px-' (or similiar) exists
					$dlink = $sk->makeKnownLinkObj( $nt_thb);
					if ( $image_thb->allowInlineDisplay() ) {
						$dlink2 = $sk->makeImageLinkObj( $nt_thb, wfMsgExt( 'fileexists-thumb', 'parseinline', $dlink ), $nt_thb->getText(), 'right', array(), false, true );
					} elseif ( !$image_thb->allowInlineDisplay() && $image_thb->isSafeFile() ) {
						$icon = $image_thb->iconThumb();
						$dlink2 = '<div style="float:right" id="mw-media-icon"><a href="' . $image_thb->getURL() . '">' . $icon->toHtml() . '</a><br />' . $dlink . '</div>';
					} else {
						$dlink2 = '';
					}

					$warning .= '<li>' . wfMsgExt( 'fileexists-thumbnail-yes', 'parsemag', $dlink ) . '</li>' . $dlink2;	
				} else {
					# Image w/o '180px-' does not exists, but we do not like these filenames
					$warning .= '<li>' . wfMsgExt( 'file-thumbnail-no', 'parseinline' , substr( $partname , 0, strpos( $partname , '-' ) +1 ) ) . '</li>';
				}
			}
			if ( $image->wasDeleted() ) {
				# If the file existed before and was deleted, warn the user of this
				# Don't bother doing so if the image exists now, however
				$ltitle = SpecialPage::getTitleFor( 'Log' );
				$llink = $sk->makeKnownLinkObj( $ltitle, wfMsgHtml( 'deletionlog' ), 'type=delete&page=' . $nt->getPrefixedUrl() );
				$warning .= wfOpenElement( 'li' ) . wfMsgWikiHtml( 'filewasdeleted', $llink ) . wfCloseElement( 'li' );
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
		 
		 /* Add something to the end of the filename */ 
		 if($isOverwrite != true)
			 $this->mUploadSaveName = time() . "_" . $this->mUploadSaveName;
		 
		$hasBeenMunged = !empty( $this->mSessionKey ) || $this->mRemoveTempFile;
		if( $this->saveUploadedFile( $this->mUploadSaveName,
		                             $this->mUploadTempName,
		                             $hasBeenMunged ) ) {
			/**
			 * Update the upload log and create the description page
			 * if it's a new file.
			 */
			$img = Image::newFromName( $this->mUploadSaveName );
			$success = $img->recordUpload( $this->mUploadOldVersion,
			                                $this->mUploadDescription,
			                                $this->mLicense,
			                                $this->mUploadCopyStatus,
			                                $this->mUploadSource,
			                                $this->mWatchthis );

			if ( $success ) {
				$this->showSuccess();
				// wfRunHooks( 'UploadComplete', array( &$img ) );
			} else {
				// Image::recordUpload() fails if the image went missing, which is
				// unlikely, hence the lack of a specialised message
				$wgOut->showFileNotFoundError( $this->mUploadSaveName );
			}
		}
	}
	
	function mainUploadForm( $msg='' ) {
		global $wgOut, $wgUser;
		global $wgUseCopyrightUpload;
		global $wgRequest, $wgAllowCopyUploads;

		if( !wfRunHooks( 'UploadForm:initial', array( &$this ) ) )
		{
			wfDebug( "Hook 'UploadForm:initial' broke output of the upload form" );
			return false;
		}

		$isOverwrite = $wgRequest->getVal('wpOverwriteFile');
		$this->mUploadDescription = $wgRequest->getVal('wpUploadDescription');
		
		// $cols = intval($wgUser->getOption( 'cols' ));
		$cols = 30;
		$ew = $wgUser->getOption( 'editwidth' );
		if ( $ew ) $ew = " style=\"width:100%\"";
		else $ew = '';

		if ( '' != $msg ) {
			$sub = wfMsgHtml( 'uploaderror' );
			/* $wgOut->addHTML( "<h2>{$sub}</h2>\n" .
			  "<span class='error'>{$msg}</span>\n" ); */
			
			$prefix = $wgRequest->getVal("callbackPrefix");
			if(strlen($prefix) == 0)
				$prefix = "";
			
			$wgOut->addHTML( "<script language=\"javascript\">
					  /*<![CDATA[*/
					  window.parent.{$prefix}uploadError('{$msg}');
					  /*]]>*/</script>");
		}
		
		// don't want the upload text stuff
		/* $wgOut->addHTML( '<div id="uploadtext">' );
		$wgOut->addWikiText( wfMsgNoTrans( 'uploadtext', $this->mDestFile ) );
		$wgOut->addHTML( '</div>' ); */
		
		$destfilename = wfMsgHtml( 'destfilename' );
		$sourcefilename = 'File';
		
		// $summary = wfMsgWikiHtml( 'fileuploadsummary' );
		if($isOverwrite != true)
			$summary = "Caption";
		else
			$summary = "";

		$licenses = new Licenses();
		$license = wfMsgHtml( 'license' );
		$nolicense = wfMsgHtml( 'nolicense' );
		$licenseshtml = $licenses->getHtml();

		$ulb = wfMsgHtml( 'uploadbtn' );


		$titleObj = SpecialPage::getTitleFor( 'Upload' );
		// $action = $titleObj->escapeLocalURL();
		$action = $wgRequest->getRequestURL();

		$encDestFile = htmlspecialchars( $this->mDestFile );

		$watchChecked =
			( $wgUser->getOption( 'watchdefault' ) ||
				( $wgUser->getOption( 'watchcreations' ) && $this->mDestFile == '' ) )
			? 'checked="checked"'
			: '';

		// Prepare form for upload or upload/copy
		if( $wgAllowCopyUploads && $wgUser->isAllowed( 'upload_by_url' ) ) {
			$filename_form =
				"<input type='radio' id='wpSourceTypeFile' name='wpSourceType' value='file' onchange='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\")' checked />" .
				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' onfocus='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\");toggle_element_check(\"wpSourceTypeFile\",\"wpSourceTypeURL\")'" .
				($this->mDestFile?"onchange='fillDestEditFilename(\"wpUploadFile\")' ":"onchange='fillDestFilename(\"wpUploadFile\")' ") . "size='40' />" .
				wfMsgHTML( 'upload_source_file' ) . "<br/>" .
				"<input type='radio' id='wpSourceTypeURL' name='wpSourceType' value='web' onchange='toggle_element_activation(\"wpUploadFile\",\"wpUploadFileURL\")' />" .
				"<input tabindex='1' type='text' name='wpUploadFileURL' id='wpUploadFileURL' onfocus='toggle_element_activation(\"wpUploadFile\",\"wpUploadFileURL\");toggle_element_check(\"wpSourceTypeURL\",\"wpSourceTypeFile\")'" .
				($this->mDestFile?"":"onchange='fillDestFilename(\"wpUploadFileURL\")' ") . "size='40' DISABLED />" .
				wfMsgHtml( 'upload_source_url' ) ;
		} else {
			$filename_form =
				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' " .
				($this->mDestFile?"onchange='fillDestEditFilename(\"wpUploadFile\")' ":"onchange='fillDestFilename(\"wpUploadFile\")' ") .
				"size='40' />" .
				"<input type='hidden' name='wpSourceType' value='file' />" ;
		}

		$prefix = $wgRequest->getVal("callbackPrefix");
		if(strlen($prefix) == 0)
			$prefix = "";

		$wgOut->addHTML( "
			<script language=\"javascript\">
			
			function submitForm(){
				window.parent.{$prefix}completeImageUpload();
			}
			
			function fillDestEditFilename(id){
				if (!document.getElementById) {
					return;
				}
				
				var path = document.getElementById(id).value;
				// Find trailing part
				
				var slash = path.lastIndexOf('/');
				var backslash = path.lastIndexOf('\\\\');
				var fname;
				
				if (slash == -1 && backslash == -1) {
					fname = path;
				} else if (slash > backslash) {
					fname = path.substring(slash+1, 10000);
				} else {
					fname = path.substring(backslash+1, 10000);
				}
				
				// Capitalise first letter and replace spaces by underscores
				fname = fname.charAt(0).toUpperCase().concat(fname.substring(1,10000)).replace(/ /g, '_');
				
				// Output result
				var destFile = document.getElementById('wpEditUploadName');
				
				if (destFile) {
					destFile.value = fname;
				}
			}
			
			function fillDestFilename(id) {
				
				if (!document.getElementById) {
					return;
				}
				
				var path = document.getElementById(id).value;
				// Find trailing part
				
				var slash = path.lastIndexOf('/');
				var backslash = path.lastIndexOf('\\\\');
				var fname;
				
				if (slash == -1 && backslash == -1) {
					fname = path;
				} else if (slash > backslash) {
					fname = path.substring(slash+1, 10000);
				} else {
					fname = path.substring(backslash+1, 10000);
				}
				
				// Capitalise first letter and replace spaces by underscores
				fname = fname.charAt(0).toUpperCase().concat(fname.substring(1,10000)).replace(/ /g, '_');
				
				// Output result
				var destFile = document.getElementById('wpDestFile');
					
				if (destFile) {
					destFile.value = fname;
				}
								
			}
			</script>
			<style>
				body {
					margin:0px;
					padding:0px;
					font-family:arial;
				}
				
				.upload-form td {
					padding:0px 0px 9px 0px;
					font-size:13px;
				}
				
				input.startButton {
					background-color:#29802C;
					border:1px solid #6B6B6B;
					color:#FFFFFF;
					font-size:12px;
					font-weight:bold;
					margin:10px 0px 0px;
					padding:3px;
					cursor:pointer;
					cursor:hand;
				}
			</style>
	<form id='upload' name='upload' method='post' enctype='multipart/form-data' action=\"$action\">
		<table border='0' cellpadding='0' cellspacing='0' class='upload-form'>
		<tr>
			<td>
				<label for='wpUploadDescription'>
					<b>{$summary}</b>
				</label>
			</td>
		</tr>
		<tr>
			<td>
			<input tabindex='2' type='". ($isOverwrite ? "hidden" : "text") . "' name='wpUploadDescription' id='wpUploadDescription' size='40' value=\"" . htmlspecialchars( $this->mUploadDescription ) ."\" />
	   {$this->uploadFormTextAfterSummary}
			</td>
		</tr>
		<tr>
	  	{$this->uploadFormTextTop}
			<td>
				<label for='wpUploadFile'>
					<b>{$sourcefilename}</b>
				</label>
			</td>
		</tr>
		<tr>
			<td>
				{$filename_form}
			</td>
		</tr>
		<input tabindex='2' type='hidden' name='wpDestFile' id='wpDestFile' size='35' value=\"$encDestFile\" />" );

		$wgOut->addHtml( "
		<input type='hidden' name='wpIgnoreWarning' id='wpIgnoreWarning' value='true' />
		<input type='hidden' name='wpEditUploadName' id='wpEditUploadName' value='' />
	<tr>
		<td>
			<input tabindex='9' onClick='submitForm()' type='submit' class='startButton' name='wpUpload' value=\"Upload\" />
		</td>
	</tr>
	</table>
	</form>
	" );
	
	}
	/* --------------------------------------------------------------" */
	 
	// The way this works is probably REALLY bad.
	// I can't find the hook where MediaWiki is causing newly uploaded files
	// to forward to the summary page :(
	function showSuccess() {
		global $wgOut, $wgContLang, $wgUser, $wgRequest;
		
		$wgOut->setArticleBodyOnly(true);
		$wgOut->clearHTML();
		
		$prefix = $wgRequest->getVal("callbackPrefix");
		
		$prefix = str_replace("<script>", "", $prefix);
		$prefix = str_replace("</script>", "", $prefix);
		
		if(strlen($prefix) == 0)
			$prefix = "";
		
		$img = Image::newFromName( $this->mUploadSaveName );
		
		$thumb = $img->getThumbnail( 136,0, true );
		$img_tag = $thumb->toHtml();
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT page_id AS pageid FROM page WHERE page_namespace=6 AND page_title ='{$this->mUploadSaveName}';";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		if($wgRequest->getVal("wpOverwriteFile")!=true){
		$timestamp = date("Y-m-d H:i:s");
		$sql = "INSERT INTO categorylinks (cl_from, cl_to, cl_sortkey, cl_timestamp) 
			VALUES ('{$row->pageid}', 'Picturegames', '{$this->mUploadSaveName}', '{$timestamp}');";
		$res = $dbr->query($sql);
	
		$wgUser = User::newFromName( 'MediaWiki default' );
		$wgUser->addGroup( 'bot' );
		$title = Title::newFromUrl( "Image:".$this->mUploadSaveName);
		$article = new Article( $title );
		$article->doEdit(  "[[Category:Picturegames]]", "automatic category tagging" );
		}
		$sql = "SELECT img_description FROM `armchairgm`.`image` WHERE img_name=\"". $this->mUploadSaveName ."\";";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		$desc = $row->img_description;
		
		?> 
			<script language="javascript">
			/*<![CDATA[*/ 
			window.parent.<?php print $prefix?>uploadComplete("<?php print addslashes( $img_tag ); ?>", "<?php print $this->mUploadSaveName ?>", "<?php print htmlentities( $desc ) ?>");
			/*]]>*/</script>
		<?php
		die();
	}
	
	function uploadError( $error ) {
		global $wgOut, $wgRequest;
		/* $wgOut->addHTML( "<h2>" . wfMsgHtml( 'uploadwarning' ) . "</h2>\n" );
		$wgOut->addHTML( "<span class='error'>{$error}</span>\n" );*/
		
		$prefix = $wgRequest->getVal("callbackPrefix");
		if(strlen($prefix) == 0)
			$prefix = "";
		
		$error = addslashes ($error);
		$wgOut->addHTML("<script language=\"javascript\">
				/*<![CDATA[*/
				window.parent.{$prefix}uploadError('{$error}');
				/*]]>*/</script>");
	}
}

?>
