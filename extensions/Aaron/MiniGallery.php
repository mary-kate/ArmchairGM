<?php
global $wgUser;

$dbr =& wfGetDB( DB_MASTER );

$output = "
	   <script language=\"javascript\">
	   
	   	var numReplaces = 1;
	   	var replaceID = 1;
		var oldHtml = '';
	   
	   	function showUploadFrame(){
			new Effect.Appear('upload-container');   
		}
		
		function uploadError(message){
			$('mini-gallery-' + replaceID).innerHTML = oldHtml;
			$('upload-frame-errors').innerHTML = message;
			$('imageUpload-frame').src = 'index.php?title=Special:PictureGameHome&picGameAction=uploadForm';
			$('upload-container').show();
		}

		function completeImageUpload(){
			/*
			var topHeight = ( $('mini-gallery-1').getHeight() > $('mini-gallery-2').getHeight() ) ? 
					  $('mini-gallery-1').getHeight() : $('mini-gallery-2').getHeight();
			var bottomHeight = ( $('mini-gallery-3').getHeight() > $('mini-gallery-4').getHeight() ) ? 
					     $('mini-gallery-3').getHeight() : $('mini-gallery-4').getHeight();
			
			var leftWidth = ( $('mini-gallery-1').getWidth() > $('mini-gallery-3').getWidth() ) ? 
					 $('mini-gallery-1').getWidth() : $('mini-gallery-3').getWidth();
			
			var rightWidth = ( $('mini-gallery-2').getWidth() > $('mini-gallery-4').getWidth() ) ? 
					 $('mini-gallery-2').getWidth() : $('mini-gallery-4').getWidth();
					 
			var height = topHeight + bottomHeight - 61;
			var width = leftWidth + rightWidth - 61;
			
			var imgHeight = ( height / 2 ) - 61;
			
			Element.setStyle('upload-frame-loading', {width: width + 'px', height: height + 'px'});
			Element.setStyle('upload-frame-loadingimg', {top: imgHeight + 'px'});
			*/
			
			oldHtml = $('mini-gallery-' + replaceID).innerHTML;
			Element.setStyle('mini-gallery-' + replaceID, { border: '2px solid red' });
			$('mini-gallery-' + replaceID).innerHTML ='<img height=\"75\" width=\"75\" src=\"../../images/common/ajax-loader-white.gif\">';
			
			$('upload-container').hide();
			// $('upload-frame-loading').show();
			
		}

		function uploadComplete(imgSrc, imgName, imgDesc){
			var idOffset = -1 - numReplaces;
			
			$('mini-gallery-' + replaceID).innerHTML = '<a href=\"javascript:slideShowLink(' + idOffset + ')\">' + imgSrc + '</a>';			
			
			replaceID = (replaceID == 3) ? 0 : ( replaceID + 1);
			numReplaces += 1;
			
			Effect.Fade('upload-frame-loading');			
			$('imageUpload-frame').src = 'index.php?title=Special:PictureGameHome&picGameAction=uploadForm';
		}
		
		function slideShowLink(id){
			window.location = 'index.php?title=Special:UserSlideShow&user={$username}&picture=' + ( numReplaces + id );	
		}
		
	   </script>
	   <h1 class=\"user-profile-title\">Pictures</h1>
	   <p class=\"profile-sub-links\" style=\"margin-bottom: 15px;\">";
	   	   
	   	if($wgUser->getName() == $username)
			   $output .= "<a href=\"javascript:showUploadFrame()\">Upload Picture</a> - ";
		   
		   $output .= "<a href=\"index.php?title=Special:UserSlideShow&user={$username}&picture=0\">Slideshow</a> -
		<a href=\"index.php?title=Special:UserImageList&user={$username}\">Gallery</a>  
	   </p>	   
	   <div class=\"user-mini-gallery-body\">
	   
	   <div id=\"upload-frame-loading\" class=\"upload-frame-loading\" style=\"display:none; position:absolute; text-align:center; vertical-align: middle; background-image: url(../../images/common/overlay.png); z-index:100\">
	   	<div id=\"upload-frame-loadingimg\" class=\"upload-frame-loadingimg\" style=\"position:relative\"> 
			<img src=\"../../images/common/ajax-loader-white.gif\">
		</div>
	   </div>
	   ";

$sql = "SELECT img_name, img_user, img_user_text, img_timestamp FROM image WHERE 
	img_user_text = '{$username}' ORDER BY img_timestamp DESC LIMIT 8";
	
$res = $dbr->query($sql);
$num = 1;
$per_row = 4;
while ($row = $dbr->fetchObject( $res ) ) {
	$image = Image::newFromName( $row->img_name );
	$thumb = $image->getThumbnail( 75, 0, true );
	$output .= "
	<div id=\"mini-gallery-{$num}\" class=\"mini-gallery\">
	<a href=\"javascript:slideShowLink({$num})\">" . $thumb->toHtml() . "</a></div>";
	
	if($num!=1 && $num%$per_row ==0) {
		$output .= "<div class=\"cleared\"></div>";
	}
	
	$num++;
}

$output .= "
<div class=\"cleared\"></div>
<div id=\"upload-container\" style=\"display:none\">
	<div id=\"upload-frame-errors\"></div>
	<iframe id=\"imageUpload-frame\" class=\"imageUpload-frame\" width=\"400\" 
		scrolling=\"no\" frameborder=\"0\" src=\"index.php?title=Special:PictureGameHome&picGameAction=uploadForm\">
	</iframe>
     </div>
</div>";

?>
