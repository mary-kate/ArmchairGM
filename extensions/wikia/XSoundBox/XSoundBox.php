<?PHP

if (Defined ('MEDIAWIKI'))
{
	$wgExtensionFunctions [] = 'wfXSoundBox';
	$wgXSoundRepoPath = '/images/_ext_xsound/images/';
        $wgXSoundRepoURL = 'http://images.wikia.com/_ext_xsound/images/';
	
	function wfXSoundBox() 
	{
		Global $wgParser;
	
		$wgParser->SetHook ('xsound', 'EmbedXSound');
	}
	function EmbedXSound ($fName, $argV)
	{
		Global $wgStylePath;
		Global $wgUploadPath, $wgUploadDirectory;
		Global $wgXSoundRepoPath, $wgXSoundRepoURL;

		# 'Oh, well, better don't ask.' :]
		$fFullName = (Empty ($argV ['src']) ? $fName : $argV ['src']); $fFullName {0} = StrToUpper ($fFullName {0});
		$xFlashFile = (Empty ($argV ['ext']) ? $wgStylePath . '/JukeboxLite.swf' : $wgStylePath . '/JukeboxFull.swf');

		$fBaseName = SubStr ($fFullName, 0, StrLen ($fFullName) - 4);
		$fExtension = SubStr ($fFullName, StrLen ($fFullName) - 3);
			
		$strHash = MD5 ($fFullName);
		$outDirSuffix = SubStr ($strHash, 0, 1) . "/" . SubStr ($strHash, 0, 2) . "/";

		# Echo $wgUploadDirectory . '/' . $outDirSuffix . $fFullName . ' --- ';

		if (File_Exists ($wgUploadDirectory . '/' . $outDirSuffix . $fFullName))
		{
			if (StrToUpper ($fExtension) != 'MP3')
			{
				$fSndPath = $wgUploadDirectory . '/'  . $outDirSuffix . $fBaseName . '.mp3';
				$fMP3Path = $wgXSoundRepoPath . $outDirSuffix . $fBaseName . '.mp3';
				
				if (! File_Exists ($fMP3Path))
				{
					$outDir = $wgXSoundRepoPath . $outDirSuffix;
					
					if (! File_Exists ($outDir))
					{
						Exec ("mkdir -p $outDir");
					}
					
					Exec ("/usr/local/bin/sox $fSndPath -t wav - | /usr/local/bin/lame - $fMP3Path");
					
					if (! File_Exists ($fMP3Path))
					{
						wfDebug ('XSound tried to convert ' . $fSndPath . ' to ' . $fMP3Path . ' but FAILED.');
					}
				}
			}
			
			$fMP3URL = (StrToUpper ($fExtension) != 'MP3') 
				 ? ($wgXSoundRepoPath . $outDirSuffix . $fBaseName . '.mp3')
				 : ($wgUploadPath . '/' . $outDirSuffix . $fBaseName . '.mp3');

			$fPageURL = (Title::MakeTitle (NS_MEDIA, $fFullName)->Exists ())
				 ? ($wgXSoundRepoURL . $outDirSuffix . $fBaseName . 'mp3')
				 : ( Title::MakeTitle (NS_MEDIA, $fFullName)->GetLocalUrl ());
			
			return ''
					//. '<span class="xsound">' . '&nbsp;&nbsp;&nbsp;'
					. '<p style="display:inline">'
					. '<object type="application/x-shockwave-flash" data="' . $xFlashFile . '" width="' . (! Empty ($argV ['ext']) ? 215 : 20) . '" height="' . (! Empty ($argV ['ext']) ? 60 : 20) . '">'
					. '<param name="movie" value="' . $xFlashFile . '" />'
					. '<param name="flashvars" value="URL=' . $fMP3URL .'" />'
					. '</object>'
					. '</p>'
					//. '</span>'
					. (! Empty ($argV ['ext']) ? '' : '&nbsp;<a href="' . $fPageURL . '" class="old" title="' . HTMLSpecialChars ($fFullName) . '">' . HTMLSpecialChars ($fFullName) . '</a>')
					;
		}
		else
		{
			$URI = Title::MakeTitle (NS_SPECIAL, 'Upload')->GetLocalUrl ('wpDestFile=' . URLEncode ($fFullName));
			return '<a href="' . $URI . '" class="new" title="' . HTMLSpecialChars ($fFullName) . '">' . HTMLSpecialChars ($fFullName) . '</a>'
			. '<!-- File is missing: ' . $wgUploadDirectory . '/' . $outDirSuffix . $fFullName
			. '; PHP: ' . File_Exists ($wgUploadDirectory . '/' . $outDirSuffix . $fFullName) . ' -->';
		}
	}
}

?>
