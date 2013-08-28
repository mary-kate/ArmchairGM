// <![CDATA[
// from the scriptalicous treasure chest

Effect.divSwap = function(element,container){
	var div = document.getElementById(container);
	var nodeList = div.childNodes;
	var queue = Effect.Queues.get('menuScope');
	
	if(queue.toArray().length<1){
		if(Element.visible(element)==false){
			for(i=0;i<nodeList.length;i++){
				if(nodeList.item(i).nodeName=="DIV" && nodeList.item(i).id!=element){
					if(Element.visible(nodeList.item(i))==true){
						Effect.Fade(nodeList.item(i))
					}
				}
			}
			Effect.Appear(element)
		}
	}
}
// ]]>

var n = new Image();
n.src = '../../images/common/ajax-loader-white.gif';

function reupload(id){
	isReload = true;
	
	if(id == 1){
		
		$("imageOne").hide();
		$("imageOneLoadingImg").show();
		
		$("imageOneUpload-frame").onload = function handleResponse(st, doc) {
			$("imageOneLoadingImg").hide();
			$("imageOneUpload-frame").show();
			this.onload = function(st, doc){ return; };
		};
		
		// passes in the description of the image
		$("imageOneUpload-frame").src = $("imageOneUpload-frame").src + '&wpUploadDescription=' + $F('picOneDesc');
		
	}else{
		
		$("imageTwo").hide();
		$("imageTwoLoadingImg").show();
		
		$("imageTwoUpload-frame").onload = function handleResponse(st, doc) {
			$("imageTwoLoadingImg").hide();
			$("imageTwoUpload-frame").show();
			this.onload = function(st, doc){ return; };
		};
		// passes in the description of the image
		$("imageTwoUpload-frame").src = $("imageTwoUpload-frame").src + '&wpUploadDescription=' + $F('picTwoDesc');
		
	}
}

function imageOne_uploadError(message){
	$("imageOneLoadingImg").hide();
	
	$("imageOneUploadError").innerHTML = '<h1>' + message + '</h1>';
	$("imageOneUpload-frame").src = $("imageOneUpload-frame").src
	$("imageOneUpload-frame").show();
}

function imageTwo_uploadError(message){
	$("imageTwoLoadingImg").hide();
	
	$("imageTwoUploadError").innerHTML = '<h1>' + message + '</h1>';
	$("imageTwoUpload-frame").src = $("imageTwoUpload-frame").src;
	$("imageTwoUpload-frame").show();
}

function imageOne_completeImageUpload(){
	$("imageOneUpload-frame").hide();
	$("imageOneLoadingImg").show();
}

function imageTwo_completeImageUpload(){
	$("imageTwoUpload-frame").hide();
	$("imageTwoLoadingImg").show();
}

function imageOne_uploadComplete(imgSrc, imgName, imgDesc){
	$("imageOneLoadingImg").hide();
	$("imageOneUpload-frame").hide();
	
	$("imageOne").innerHTML = 
	'<p><b>' + imgDesc + '</b></p>' + 
	imgSrc +
	'<p><a href="javascript:reupload(1)">Edit</a></p>';
	
	document.picGamePlay.picOneURL.value = imgName;
	document.picGamePlay.picOneDesc.value = imgDesc;
		
	Effect.Appear("imageOne");
	
	if(document.picGamePlay.picTwoURL.value != "" && document.picGamePlay.picOneURL.value != "")
		Effect.Appear("startButton");
}

function imageTwo_uploadComplete(imgSrc, imgName, imgDesc){
	$("imageTwoLoadingImg").hide();
	$("imageTwoUpload-frame").hide();
	
	$("imageTwo").innerHTML = 
	'<p><b>' + imgDesc + '</b></p>' +
	imgSrc +
	'<p><a href="javascript:reupload(2)">Edit</a></p>';
	
	document.picGamePlay.picTwoURL.value = imgName;
	document.picGamePlay.picTwoDesc.value = imgDesc;
	
	Effect.Appear("imageTwo");
	
	if(document.picGamePlay.picOneURL.value != "")
		Effect.Appear("startButton");
}

function startGame(){
	
	var iserror = false;
	var gameTitle = $F('picGameTitle');
	var imgOneURL = $F('picOneURL');
	var imgTwoURL = $F('picTwoURL');
	
	var errorText = "";
	
	if(gameTitle.length == 0){
		iserror = true;
		
		Element.setStyle('picGameTitle', {'border-style': 'solid'});
		Element.setStyle('picGameTitle', {'border-color': 'red'});
		Element.setStyle('picGameTitle', {'border-width': '2px'});
		
		errorText = errorText + "Please enter a title!<br>";
	}
	
	if(imgOneURL.length == 0){
		iserror = true;
		
		Element.setStyle('imageOneUpload', {'border-style': 'solid'});
		Element.setStyle('imageOneUpload', {'border-color': 'red'});
		Element.setStyle('imageOneUpload', {'border-width': '2px'});
		
		errorText = errorText + "Please upload image one!<br>";
	}
	
	if(imgTwoURL.length == 0){
		iserror = true;
		
		Element.setStyle('imageTwoUpload', {'border-style': 'solid'});
		Element.setStyle('imageTwoUpload', {'border-color': 'red'});
		Element.setStyle('imageTwoUpload', {'border-width': '2px'});
		
		errorText = errorText + "Please upload image two!<br>";
	}
	
	if(!iserror)
		document.picGamePlay.submit();
	else
		$('picgame-errors').innerHTML = errorText;
	
}

function skipToGame(){
	document.location = 'index.php?title=Special:PictureGameHome&picGameAction=startGame';	
}