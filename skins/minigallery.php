<html>
<body>
<?php

global $wgUser;

$dbr =& wfGetDB( DB_MASTER );

$output = "
	   <script language=\"javascript\">
	   
	   	var numReplaces = 0;
	   	var replaceID = 0;
		var replaceSrc = '';
		var oldHtml = '';
	   
	   	function showUploadFrame(){
			//new Effect.Appear('upload-container'); 
			$('upload-container').show(); 
		}
		
		function uploadError(message){
			$('mini-gallery-' + replaceID).innerHTML = oldHtml;
			$('upload-frame-errors').innerHTML = message;
			$('imageUpload-frame').src = '?title=Special:MiniAjaxUpload&wpThumbWidth=75';
			
			$('upload-container').show();
		}

		function textError(message){
			$('upload-frame-errors').innerHTML = message;
		}
		
		function completeImageUpload(){
			 
			$('upload-frame-errors').innerHTML = '';
			oldHtml = $('mini-gallery-' + replaceID).innerHTML;
			Element.setStyle('mini-gallery-' + replaceID, { border: '2px solid red', width:'75px' });
			//alert('after set style');
			for(x=7;x>0;x--){
				//alert(x + '--' + $('mini-gallery-' + (x-1) ).innerHTML)
				if($('mini-gallery-' + (x-1) ).innerHTML!=''){
					$('mini-gallery-' + (x) ).addClassName('mini-gallery');
				}
				$('mini-gallery-' + (x) ).innerHTML = $('mini-gallery-' + (x-1) ).innerHTML.replace('slideShowLink('+(x-1)+')','slideShowLink('+(x)+')')
			}
			$('mini-gallery-0').innerHTML = '<img height=\"75\" width=\"75\" src=\"../../images/common/ajax-loader-white.gif\">';
	
			if($('mini-gallery-nopics'))$('mini-gallery-nopics').hide();
			 
		}

		function uploadComplete(imgSrc, imgName, imgDesc){
			//alert(1);
			replaceSrc = imgSrc;
			
			$('upload-frame-errors').innerHTML = '';
			
			$('imageUpload-frame').onload = function(){ 
				 
				var idOffset = -1 - numReplaces;
				$('mini-gallery-0' ).addClassName('mini-gallery');
				$('mini-gallery-0').innerHTML = '<a href=\"javascript:slideShowLink(' + idOffset + ')\">' + replaceSrc + '</a>';			
				
				replaceID = (replaceID == 7) ? 0 : (replaceID + 1);
				numReplaces += 1;
				//$('imageUpload-frame').src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75';
				$('upload-container').show(); 
				//$('upload-frame-loadingimg').hide();
			}
			
			$('imageUpload-frame').src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75';
			//$('upload-container').hide();
		}
		
		function slideShowLink(id){
			window.location = 'index.php?title=Special:UserSlideShow&user={$username}&picture=' + ( numReplaces + id );	
		}
		
	   </script>";


		//database calls
		$sql = "SELECT img_name, img_user, img_user_text, img_timestamp FROM image INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name) WHERE img_user_text='".addslashes($username)."' AND cl_to = 'Profile_Pictures' ORDER BY img_timestamp DESC LIMIT 8;";

		$sql_total = "SELECT COUNT(img_name) as gallery_count FROM image INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name) WHERE img_user_text='".addslashes($username)."' AND cl_to = 'Profile_Pictures'";
		
		$res = $dbr->query($sql);
		$res_total = $dbr->query($sql_total);
		$row_total = $dbr->fetchObject( $res_total );
		$total = $row_total->gallery_count;
	
		//output mini gallery
		$output .= "<h1 class=\"user-profile-title\">Pictures</h1>
	   <p class=\"profile-sub-links\" style=\"margin-bottom: 15px;\">";
	   	   
		if ($total > 0) {
			
			if($wgUser->getName() == $username) {
				$output .= "<a href=\"javascript:showUploadFrame()\">Upload Picture</a> - ";
			}

			$output .= "<a href=\"index.php?title=Special:UserSlideShow&user={$username}&picture=0\">Slideshow</a> -
			<a href=\"index.php?title=Special:UserImageList&user={$username}\">Gallery</a>";
			
		} else {
			
			if($wgUser->getName() == $username) {
				$output .= "<a href=\"javascript:showUploadFrame()\">Upload Picture</a>";
			}
			
		}
		
	   $output .= "</p>	   
	   <div class=\"user-mini-gallery-body\">";

$num = 0;
$per_row = 4;

while ($row = $dbr->fetchObject( $res ) ) {
	
	$image = Image::newFromName( $row->img_name );
	$thumb = $image->getThumbnail( 75, 0, true );
	
	$output .= "
	<div id=\"mini-gallery-{$num}\" class=\"mini-gallery\">
		<a href=\"javascript:slideShowLink({$num})\">" . $thumb->toHtml() . 
	"</a></div>";
	
	if( ( $num + 1 ) % $per_row == 0 ) {
		$output .= "<div class=\"cleared\"></div>";
	}
	
	$num++;
}

for($i = $num; $i < 8; $i++){
	$output .= "<div id=\"mini-gallery-{$i}\"></div>";
	
	if( ($i+1) % $per_row == 0 ) {
		$output .= "<div class=\"cleared\"></div>";
	}
}

if ($num > 0) {
	$output .= "<div id=\"mini-gallery-nopics\" style=\"display:none;\"></div>";
} else {
	$output .= "<div id=\"mini-gallery-nopics\" style=\"margin:0px 0px 18px 0px;\"> This user has not uploaded any pictures.</div>";
}

	
//'

$output .= "
<div class=\"cleared\"></div>
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
					background-color:#89C46F;
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

<div id=\"upload-frame-errors\" class=\"upload-frame-errors\" style=\"margin:0px 0px 10px 0px;color:red; font-size:14px; font-weight:bold;\"></div>

<div id=\"upload-container\" style=\"display:none; height: 80px;\">
		<iframe id=\"imageUpload-frame\" class=\"imageUpload-frame\" width=\"410\" 
			scrolling=\"no\" frameborder=\"0\" src=\"?title=Special:MiniAjaxUpload&wpThumbWidth=75\">
		</iframe>
	</div>
</div>";

?>
</body></html>
