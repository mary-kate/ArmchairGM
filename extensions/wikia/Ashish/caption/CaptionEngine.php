<?php

$wgExtensionFunctions[] = 'wfSpecialCaptionEngine';

function wfSpecialCaptionEngine(){

	global $wgUser, $IP;

	class CaptionEngine extends SpecialPage {
		
		function CaptionEngine(){
			UnlistedSpecialPage::UnlistedSpecialPage("CaptionEngine");
		}
		
		function execute(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			$this->renderCaption();
		}
		
		function renderCaption(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$wgOut->setArticleBodyOnly(true);
			
			$url = $wgRequest->getVal("imagename");
			list($junk, $imageName) = explode(":", $url);
			
			$width = $wgRequest->getVal("imagewidth");
			$savedCaptions =  $_REQUEST["captions"];
			
			$image = Image::newFromName( $imageName );
			$imageThumb = $image->createThumb($width);
			
			// fix the absolute linking
			$im = imagecreatefromjpeg( $imageThumb ) or die ("Fatal Error!");
			
			$fontDir = "/usr/openserving/conf/mediawiki/wiki2/extensions/wikia/Ashish/caption/fonts";
						
			foreach($savedCaptions as $curr){
				$top = str_replace("px", "", $curr["top"]) + 15;
				$left = str_replace("px", "", $curr["left"]) - 14;
				$caption = $curr["text"];
				$color = $curr["color"];
				
				switch( $curr["font"] ){
				case "arial":
					$font = $fontDir . "/arial.ttf";
					break;
				case "comic sans ms":
					$font = $fontDir . "/comic.ttf";
					break;
				case "courier new":
					$font = $fontDir . "/courbd.ttf";
					break;
				case "impact":
					$font = $fontDir . "/impact.ttf";
					break;
				case "times":
					$font = $fontDir . "/times.ttf";
					break;
				default:
					$font = $fontDir . "/arial.ttf";
					break;
				}
				
				if(is_numeric($curr["size"]))
					$size = $curr["size"]-4;
				else
					$size = 12;
				
				$arrColor = $this->html2rgb ( $color );
				$textColor = imagecolorallocate($im, $arrColor[0], $arrColor[1], $arrColor[2]);
				
				$result = imagettftext( $im, $size, 0, $left, $top, $textColor, $font, $caption );
				
			}
			
			$black = imagecolorallocate ( $im, 255, 255, 255 );
			
			$result = imagettftext( $im, 12, 0, 0, 13, $black, ($fontDir . "/arial.ttf"), "ArmchairGM" );
			
			$now = time();
			
			$tempName = "/tmp/{$now}_{$imageName}";
			$saveName = "{$now}_{$imageName}";
			
			$result = imagejpeg($im, $tempName, 100) or die("fatal error");
			$dest = wfImageDir( $saveName );
			
			$mSavedFile = "{$dest}/{$saveName}";
			
			rename( $tempName, $mSavedFile );
			chmod( $mSavedFile, 0644 );
			
			$img = Image::newFromName( $saveName );
			
			$success = $img->recordUpload( "",
				"lolcat",
				"",
				"",
				"",
				"" );
			
			$image = Image::newFromName( $saveName );
			$imageThumb = $image->createThumb($width);

			print $imageThumb;
			
			// this is bad - fix me
			exit(0);
		}
		
		// from http://www.anyexample.com/programming/php/php_convert_rgb_from_to_html_hex_color.xml
		function html2rgb($color)
		{
			if ($color[0] == '#')
				$color = substr($color, 1);
			
			if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
				$color[2].$color[3],
				$color[4].$color[5]);
			elseif (strlen($color) == 3)
				list($r, $g, $b) = array($color[0], $color[1], $color[2]);
			else
			return false;
			
			$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
			
			return array($r, $g, $b);
		}
		
	}
	
	SpecialPage::addPage( new CaptionEngine );
}

?>
