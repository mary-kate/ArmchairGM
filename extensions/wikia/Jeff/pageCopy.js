function getOtherPage (baseurl, additional, title, textarea) {
	//alert($(textsource).value);
	
	var url = baseurl + additional + "Special:PageCopy&pageCopyAction=display&pageToCopy=" + escape(title);
	//var formObject = document.getElementById('page_copy_form');
	//YAHOO.util.Connect.setForm(formObject);
	//var postBody = 'rs=wfQuestionGameAdmin&rsargs[]=deleteItem&rsargs[]=' + \$( 'quizGameKey' ).value + '&rsargs[]=' + \$( 'quizGameId' ).value;
	var callback = 
	{
		success:function(t){
			var page = t.responseText;
			if (page.indexOf("<div=\"page_copy_container_div\">")) {
				var wiki_text = page.substring(page.indexOf("<div=\"page_copy_container_div\">") + 31, page.indexOf("<!--end_page_copy_content-->"));
				YAHOO.util.Dom.get('page_to_copy').value = wiki_text;
				//document.location = '#view_preview';
			}
		},
		failure:function(t) { 
			alert(t.responseText);	
			//\$('preview_div').innerHTML = t.responseText;
			//document.location = '#view_preview'; 
		}
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', url, callback);
	
}

function viewPreview(previewlocation, which, thispage) {
	//alert($(textsource).value);
	YAHOO.util.Dom.get('preview_text').value = YAHOO.util.Dom.get(which).value;
	var url = thispage.substring(0, thispage.indexOf("Special:PageCopy") + 16);
	if (url.indexOf("?") > 0) {
		var nextchar = "&";
	}
	else {
		var nextchar = "?";
	}
	url += nextchar + "pageCopyAction=preview";
	var formObject = document.getElementById('page_copy_form');
	YAHOO.util.Connect.setForm(formObject);
	//var postBody = 'rs=wfQuestionGameAdmin&rsargs[]=deleteItem&rsargs[]=' + \$( 'quizGameKey' ).value + '&rsargs[]=' + \$( 'quizGameId' ).value;
	var callback = 
	{
		success:function(t){
			var page = t.responseText;
			if (page.indexOf("<div=\"page_copy_container_div\">")) {
				var wiki_text = page.substring(page.indexOf("<div=\"page_copy_container_div\">") + 31, page.indexOf("<!--end_page_copy_content-->"));
				YAHOO.util.Dom.get('preview_div').innerHTML = wiki_text;
				document.location = '#view_preview';
			}
		},
		failure:function(t) { 
			alert(t.responseText);	
			//\$('preview_div').innerHTML = t.responseText;
			//document.location = '#view_preview'; 
		}
	}
	var transaction = YAHOO.util.Connect.asyncRequest('POST', url, callback);
	
}
