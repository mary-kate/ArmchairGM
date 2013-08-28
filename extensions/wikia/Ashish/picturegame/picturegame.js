var img = new Image();
img.src = "../../images/common/overlay.png";

var img2 = new Image();
img.src = "../../images/common/ajax-loader.gif";

var cleanTitle = document.title;

// flags an image set
function flagImg(){
	var ask = confirm("Are you sure you want to report these images?");
	
	if (ask){
		new Ajax.Request('?title=Special:PictureGameHome&picGameAction=flagImage&key=' + $F('key') + '&id=' + $F('id') + '', {
				onSuccess: function(t){	
					$('serverMessages').innerHTML = '<strong>' + t.responseText + '</strong>';
				}, 
				onError: function(t){ $('serverMessages').innerHTML = '<strong>' + t.responseText + '</strong>' }
		});
	}
}

function doHover(divID){
	if (divID=='imageOne') {
		$(divID).setStyle({backgroundColor: '#4B9AF6'});
	} else {
		$(divID).setStyle({backgroundColor: '#FF1800'});
	}
	
}

function endHover(divID){
	$(divID).setStyle({backgroundColor: ''});
}

function editPanel(){
	document.location = '?title=Special:PictureGameHome&picGameAction=editPanel&id=' + $F('id');
}

function protectImages(){
	var ask = confirm("Are you sure you want to protect these images?");
	
	if (ask){
		new Ajax.Request('?title=Special:PictureGameHome&picGameAction=protectImages&key=' + Form.Element.getValue('key') + '&id=' + Form.Element.getValue('id'), {
				onSuccess: function(t){	
					$('serverMessages').innerHTML = '<strong>' + t.responseText + '</strong>';	
				}
		});
	}
}

function detectMacXFF() {
  var userAgent = navigator.userAgent.toLowerCase();
  if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox')!=-1) {
    return true;
  }
}

function castVote(picID){
	
	// pop up the lightbox
	objLink = new Object();
	//objLink.href = "../../images/common/ajax-loader.gif"
	objLink.href = "";
	objLink.title = "";
	
	showLightbox(objLink);
	
	if( !detectMacXFF() ){
	setLightboxText( '<embed src="/extensions/wikia/Ashish/picturegame/ajax-loading.swf" quality="high" wmode="transparent" bgcolor="#ffffff"' + 
			  	'pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"' + 
				'type="application/x-shockwave-flash" width="100" height="100">' +
				'</embed>' );
	}else{
		setLightboxText( 'Loading...');
	}
	
	document.picGameVote.lastid.value = $F('id');
	document.picGameVote.img.value = picID;
	
	new Ajax.Request('?title=Special:PictureGameHome&picGameAction=castVote', 
		{method:'post',
			postBody:'key=' + Form.Element.getValue('key') + '&id=' + Form.Element.getValue('id') + '&img=' + picID + '&nextid='+ $F('nextid'), 
			onSuccess:	
			function(t){
				window.location = '?title=Special:PictureGameHome&picGameAction=startGame&lastid=' + $F('id') +'&id=' + $F('nextid');
			}
		});
	
}