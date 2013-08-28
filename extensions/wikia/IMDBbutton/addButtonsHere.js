var buttonpara=new Array();
buttonpara[0] = new Array(insertIMDbTag, "imdb_button.png", "IMDb Lookup");

function getSelectedText() {
    var text;
    if (document.selection) {
	text = document.selection.createRange().text;
    }
    else if (document.editform) {
	var textarea = document.editform.wpTextbox1;
	if (textarea.selectionStart || textarea.selectionStart == '0') {
	    text = (textarea.value).substring(textarea.selectionStart,
					      textarea.selectionEnd);
	}
    }
    return text;
}

function insertIMDbTag(resource_id) { 
    if (typeof(resource_id) == "string") {
	if (resource_id.match(/^tt/)) {
	    insertTags("{{IMDb|title|" + resource_id + "|", "}}", 
		       "Movie or Actor");
	}
	else if (resource_id.match(/^nm/)) {
	    insertTags("{{IMDb|name|" + resource_id + "|", "}}", 
		       "Movie or Actor");
	}
    }
    else {
	var text = getSelectedText();
	if (text) {
	    window.open("find.php?q=" + text,
			"imdb_picker",
			"height=600,width=600,scrollbars=yes");
	}
    }
}
