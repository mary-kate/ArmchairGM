<?php

$wgExtensionFunctions[] = 'wfPictureCaption';

function wfPictureCaption(){
	global $wgUser,$IP, $wgParser;
	$wgParser->setHook( "piccaption", "RenderCaption" );
}

function RenderCaption($input){
	global $wgOut;
	
	$script = "
	<script language=\"javascript\">
	    
	    var currid = 0;
	    var activeDiv = null;
	    var titleHash = new Array();
	    var currentImage = -1;
	    
	    var oldMouseX = 0;
	    var oldMouseY = 0;
	    
	    function moveDiv(e){
		    if(oldMouseX == 0 || oldMouseY == 0){
			    oldMouseX = e.pageX;
			    oldMouseY = e.pageY;
		    }else{
			    Element.setStyle(activeDiv, {left:  (Math.abs(e.pageX-oldMouseX)+(activeDiv.getWidth()/2)) + 'px'} );
			    Element.setStyle(activeDiv, {top:  (Math.abs(e.pageY-oldMouseY)+(activeDiv.getHeight()/2)) + 'px'} );
		    }
	    }
	    
	    function clearCaption(){
		    $('captionText[' + currentImage + ']').innerHTML = '';
	    }
	    
	    function saveCaption(){
		    var postBody = 'imagename=' + escape( titleHash[currentImage] )+'&imagewidth='+\$('captionContainer['+currentImage+']').getWidth();
		    var color = null;
		    var caption = null;
		    
		    for(var i = 0; i < currid; i++){
			
			color = escape( $('captionText[' + currentImage + '][' + i + ']').getStyle('color').parseColor() );
			caption = escape( $('captionText[' + currentImage + '][' + i + ']').innerHTML );
			
			postBody = postBody + '&captions['+i+'][left]=' + 
					      $('captionText[' + currentImage + '][' + i + ']').getStyle('left').replace(/px/, '') + 
					      '&captions['+i+'][top]=' + 
					      $('captionText[' + currentImage + '][' + i + ']').getStyle('top').replace(/px/, '') +
					      '&captions['+i+'][font]=' + $('captionText[' + currentImage + '][' + i + ']').getStyle('font-family') +
					      '&captions['+i+'][color]=' + color +
					      '&captions['+i+'][text]=' + caption + 
					      '&captions['+i+'][size]=' + $('captionText[' + currentImage + '][' + i + ']').getStyle('font-size').replace(/px/, '');   
		    }
		    
		    // $('formcontainer').innerHTML = postBody;
		    $('ajax-loading').show();
		    
		    new Ajax.Request('index.php?title=Special:CaptionEngine',
		    {
			postBody:	postBody,
		    	onSuccess:	function(t){
						$('imageTag[' + currentImage + ']').down().href = t.responseText;
						$('imageTag[' + currentImage + ']').down().down().src = t.responseText;
						$('captionText[' + currentImage + ']').innerHTML = '';
						$('imageLabels[' + currentImage + ']').innerHTML = '<a href=\"' + t.responseText + '\"> New Image </a>'; 
						$('ajax-loading').hide();
						\$('addTextContainer').show();
						
						
						},
			onFailure: 	function(t) { alert('Error was: ' + t.responseText); }
		    });
	    }

	    function addText(){
		    
		    if(currentImage >= 0){
			    $('captionText['+currentImage+']').innerHTML = $('captionText['+currentImage+']').innerHTML + 
			    	'<div id=\"captionText[' + currentImage + '][' + currid + ']\" ' +
				'class=\"captionText\" style=\"position: absolute; left:50px; z-index:103; color: #' + \$F('fontcolor') + ';' +  
				'font-family: '+\$F('selectfontname') +'; font-size: '+\$F('fontsize')+'px; cursor:pointer; cursot:hand\">' + \$F('captionHookCaption') + '</div>';
				
				for(var i = 0; i <= currid; i++){
					new Draggable('captionText[' + currentImage + '][' + i + ']');
				}
				currid ++; 
		    }
		    
		    
	   }
	   	   
	   function loadImageNum(num){
		   currentImage = num;
		   \$('addTextContainer').show();
	   }
	   
	   function initCaptionHook(){
		
		var imgArray = $$('a.image');
		for(var i = 0; i < imgArray.length; i++){
			
			var imageParent = imgArray[i].up();
			var linkURL = imgArray[i].href;
			var oldHtml = imageParent.innerHTML;
			
			titleHash[i] = imgArray[i].title;
			
			new Insertion.After(imgArray[i].up(), '<div id=\"captionContainer[' + i + ']\" onmouseover=\"$(\'imageLabels['+i+']\').show()\" onmouseout=\"$(\'imageLabels['+i+']\').hide()\" class=\"caption-container\"' + 
								'style=\"width: ' + imgArray[i].down().width + 'px; height: ' + imgArray[i].down().height + 'px; z-index:0; position:relative; left:0px; top:0px\">' +  
								'<div id=\"imageLabels[' + i + ']\" class=\"imageLabels\" style=\"background-color: #CCCCCC; display:none; color:white; z-index:101; position:absolute\"><a href=\"javascript:loadImageNum('+ i +')\">Add Text</a> -- <a href=\"'+linkURL+'\">Link</a></div>' + 
								'<div id=\"captionText[' + i + ']\" style=\"z-index:100\"></div><div id=\"imageTag[' + i + ']\" class=\"caption-imageTag\">' + 
								oldHtml + '</div></div>');
			
			
			imgArray[i].remove();
			/* imgArray[i].remove();
			
			imageParent.innerHTML = '<div id=\"captionContainer[' + i + ']\" onmouseover=\"$(\'imageLabels['+i+']\').show()\" onmouseout=\"$(\'imageLabels['+i+']\').hide()\" class=\"caption-container\"' + 
			'style=\"width: ' + imgArray[i].down().width + 'px; height: ' + imgArray[i].down().height + 'px; z-index:0; position:relative; left:0px; top:0px\">' +  
			'<div id=\"imageLabels[' + i + ']\" class=\"imageLabels\" style=\"background-color: #CCCCCC; display:none; color:white; z-index:101; position:absolute\"><a href=\"javascript:loadImageNum('+ i +')\">Add Text</a> -- <a href=\"'+linkURL+'\">Link</a></div>' + 
			'<div id=\"captionText[' + i + ']\" style=\"z-index:100\"></div><div id=\"imageTag[' + i + ']\" class=\"caption-imageTag\">' + 
			imgArray[i].innerHTML + '</div></div>';
			*/
			
			// Droppables.add('captionContainer[' + i + ']');
		}
		
		// \$('loadurl').options[i] = new Option(\"NONE\", \"NONE\");
		// \$('loadurl').selectedIndex = i;
		
		new Control.ColorPicker(\"fontcolor\", { IMAGE_BASE : 'extensions/wikia/YUIPicker/img/' });
	   }
	   
	</script>";
	
	$wgOut->setOnloadHandler( "initCaptionHook()" );
	
	$wgOut->addScript( "<script type=\"text/javascript\" src=\"/extensions/wikia/YUIPicker/colorPicker.js\"></script>" );
	$wgOut->addScript( "<script type=\"text/javascript\" src=\"/extensions/wikia/YUIPicker/yahoo.color.js\"></script>" );
	
	$wgOut->addScript( $script );
	
	$output = "
	<style type='text/css'>
		@import \"extensions/wikia/YUIPicker/colorPicker.css\";
	</style>
		
	<div id=\"addTextContainer\" class=\"addTextContainer\" style=\"display:none\">
		Add Some Text: <input type=text value=\"\" id=\"captionHookCaption\" name=\"captionHookCaption\" size=\"20\">
		<br />
		Color: #<input type=\"text\" id=\"fontcolor\" name=\"fontcolor\" value=\"797979\">
		<img id=\"ajax-loading\" style=\"display:none\" src=\"extensions/wikia/Ashish/caption/small-ajax-loader.gif\">
		<br />
		Font: <select name=\"selectfontname\" id=\"selectfontname\">
			<option selected value=\"arial\">Arial</option>
			<option value=\"comic sans ms\">Comic Sans</option>
			<option value=\"courier new\">Courier New</option>
			<option value=\"impact\">Impact</option>
			<option value=\"times\">Times New Roman</option>
		</select>
		<br />
		Size: <select name=\"fontsize\" id=\"fontsize\">
				<option value=\"8\">8</option>
				<option value=\"9\">9</option>
				<option value=\"10\">10</option>
				<option value=\"11\">11</option>
				<option selected value=\"12\">12</option>
				<option value=\"14\">14</option>
				<option value=\"16\">16</option>
				<option value=\"18\">18</option>
				<option value=\"20\">20</option>
				<option value=\"22\">22</option>
				<option value=\"24\">24</option>
				<option value=\"26\">26</option>
				<option value=\"28\">28</option>
				<option value=\"36\">36</option>
				<option value=\"48\">48</option>
				<option value=\"72\">72</option>
			   </select>
		<br />
		<input type=\"button\" value=\"Insert\" onclick=\"addText()\">
		<input type=\"button\" value=\"Clear\"   onclick=\"clearCaption()\">
		<input type=\"button\" value=\"Save\"   onclick=\"saveCaption()\">
	</div>
	<div id=\"formcontainer\"></div>
	<div class=\"cleared\"></div>
	";
	
	return $output;
}

?>
