function showUpdaterPreview (textsource, previewlocation) {
	//alert($(textsource).value);
	var url = "index.php?action=ajax";
	var pars = 'rs=wfShowUpdaterPreview&rsargs[]=' + escape($(textsource).value);

	new Ajax.Updater(
		previewlocation, url,
		
		{
		   method:'post',
		   parameters: pars,
		   onSuccess: function (t) {
				
			   location.href="#view_preview";

			}
		}
		
		);
	
}

var theText = "";
var changeable = false;

function createPage(textsource, playername, previewlocation, textdrop) {
	//alert(escape(pagetitle));
	
	var url = "index.php?action=ajax";
	var pars = 'rs=wfCreatePageFromUpdater&rsargs[]=' + escape($(textsource).value) + '&rsargs[]=' + escape($(playername).value);

	new Ajax.Updater(
		previewlocation, url,
		
		{
		   method:'post',
		   parameters: pars,
		   onSuccess: function (t) {
				
			   changeable = true;
			   $(textdrop).value = $(textsource).value;
			   location.href="index.php?title=" + escape($(playername).value);

			}
		}
		
		);

}

function setCreateButton(location) {
	if (changeable) {
		$(location).innerHTML = "<input type=\"button\" id=\"create\" value=\"create\" onClick=\"setAction('action', 'create', 'form1');\" />";
		//new Effect.Appear(location);
		changeable = false;
	}
}

function showCategoryPopup(display) {
	
	$(display).show();
	
}

function copyCategory(to, field, display)
{
	//var field = $(which);
	//alert(field.length);
	for (i = 0; i < field.length; i++) {
		if(field[i].checked) {
			var replaceText = "[[Category: " + field[i].value + "]]"
			if($(to).value.indexOf(replaceText) < 0) {
				$(to).value += replaceText + "\n";
			}
		}
	}
	
	closeBox(display);
}
/*{

		$(to).value += "[[Category: " + which + "]]\n";
	
}*/

function checkAll(field)
{
	//var field = $(which);
	//alert(field.length);
	for (i = 0; i < field.length; i++) {
		field[i].checked = true;
	}

}

function uncheckAll(field)
{
	//var field = $(which);
	//alert(field.length);
	for (i = 0; i < field.length; i++) {
		field[i].checked = false;
	}

}

function promptOldToNew(to, from, display) {
	theText = "";
	var errorMsg = "Please select some text from the TextArea on the right";
	
	try {			
		var selectedText = document.selection;
		if (selectedText.type == 'Text') {
			var newRange = selectedText.createRange();
			theText = newRange.text;
		} 
		else {
		alert(errorMsg);
		} 
	} 
	catch (ex) {
		//alert(ex.message);
		var field = $(from);
		var startPos = field.selectionStart;
		var endPos = field.selectionEnd;
		var selected = field.value.substr(field.selectionStart, (field.selectionEnd - field.selectionStart));
		
		
		if (selected.toString().length) {
			theText = selected;

		} 
		else {
			alert(errorMsg);
		}

	}
	
	if (theText != "") {
		getOldToNewMatches(to, from, display);
	}
	
	
}


function getOldToNewMatches(to, from, display) {
	var findAll = new RegExp(":::([^:]+):::", "g");
	
	//var matches = findAll.exec($(to).value);
	var matches = $(to).value.match(findAll);
	//var alertStr = matches.global + "\n";
	var findtext = new RegExp(":::([^:]+):::");
	var match_text = new Array();
	
	var alertStr = "";
	var outputHTML = "";
	//alert("here");
	if (matches) {
		outputHTML = "Copy the selected text to the following section:<br/><blockquote>";
		for (var i=0; i<matches.length; i++) {
			//alertStr += matches[i] + "\n";
			found = findtext.exec(matches[i]);
			match_text[i] = found[1];
			//alertStr += match_text[i] + "\n";
			outputHTML +=  "- <a style=\"cursor:pointer;\" onClick=\"doCopyOldToNew('" + to + "', '" + from + "', '" + display + "', '" + match_text[i] + "');\">" + match_text[i] + "</a><br/>";
		}
		
		outputHTML += "</blockquote><div style=\"position:absolute; bottom: 5px; right: 5px;\"><a style=\"cursor:pointer;\" onClick=\"closeBox('" + display + "');\">[x] cancel</a></div>";
		
		$(display).innerHTML = outputHTML;
		$(display).show();
	}
	else {
		alertStr = "No matches found";
		alert(alertStr);
	}
	
	
}

function closeBox(display) {
	$(display).hide();
	//$(display).innerHTML = "";
}


function doCopyOldToNew(to, from, display, which) {

		$(display).innerHTML = "";
		$(display).hide();
		
		copyOldToNew(to, from, which);
	
}


function copyOldToNew(to, from, which_replace) {

	//var which_replace = prompt("Which Field are you Replacing?");
	var replace_field = "***" + which_replace + "***";
	var replace_field_re_string = ":::" + which_replace + ":::";
	var replace_field_re = new RegExp(replace_field_re_string);
	
	if (replace_field_re.test($(to).value)) {
		$(to).value = $(to).value.replace(replace_field_re, theText);
		//$(to).focus();
		theText = "";
	}
	
}

function copyOldToNew_1(to, from) {

	var which_replace = prompt("Which Field are you Replacing?");
	var replace_field = "***" + which_replace + "***";
	var replace_field_re_string = ":::" + which_replace + ":::";
	var replace_field_re = new RegExp(replace_field_re_string);
	
		try {
			
					var selectedText = document.selection;
		if (selectedText.type == 'Text') {
			var newRange = selectedText.createRange();
			if (replace_field_re.test($(to).value)) {
				$(to).value = $(to).value.replace(replace_field_re, newRange.text);
				$(to).focus();
			}
			else {
				alert("Please indicate a spot to paste the text by adding \":::field_name:::\" somewhere");
			}
		} else {
		alert('Alert: Select The text in the textarea then click on this button');
		} 
	} 
	catch (ex) {
		//alert(ex.message);
		var field = $(from);
		var startPos = field.selectionStart;
		var endPos = field.selectionEnd;
		var selected = field.value.substr(field.selectionStart, (field.selectionEnd - field.selectionStart));
		
		
		if (selected.toString().length) {
			var newRange = selected;
			if (replace_field_re.test($(to).value)) {
				$(to).value = $(to).value.replace(replace_field_re, newRange);
				$(to).focus();
			}
			else {
				alert("Please indicate a spot to paste the text by adding \":::field_name:::\" somewhere");
			}
		} else {
			alert('select text in the page and then press this button');
		}

	}
	
}

function setAction(action, which, form) {
	$(action).value = which;
	$(form).submit();
}


