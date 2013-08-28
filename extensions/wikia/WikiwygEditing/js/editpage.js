
var WikiwygInstance ;
var wgFullPageEditing = true ;

/* copied and adapted from upload image script... */
// apply tagOpen/tagClose to selection in textarea,
// use sampleText instead of selection if there is none
// copied and adapted from phpBB
function WKWinsertTags(tagOpen, tagClose, sampleText) {
	if (WikiwygInstance.enabled) {
		var out = WikiwygInstance.current_mode ;
	        out.insert_html (tagOpen + sampleText + tagClose) ;
	}
}

/* fall back if edit mode not supported - provide a normal interface  */
EditPageFallBack = function () {       
	/* todo give standard edit toolbar - mainly for Opera users */
	document.insertTags = insertTags ;
}

document.insertTags = WKWinsertTags ;

/* modified function for image upload (omitting HACK section for non-existing watchthis link )*/
var imageUploadDialog = null;

function specialCreatePageImageUpload(tagOpen, tagClose, sampleText) {
	var re = /http:\/\/([^\/]*)\//g;
	var matches = re.exec(window.location.href);
	if ( !matches ) {
	// TAH: firefox bug: have to do it twice for it to work
	matches = re.exec(window.location.href);
	}
	var domain = matches[1];
	if (imageUploadDialog && imageUploadDialog.open && !imageUploadDialog.closed)
	imageUploadDialog.close();

	imageUploadDialog = window.open("http://" + domain + "/wiki/Special:MiniUpload", "upload_file", "height=520,width=500,toolbar=no,location=no,resizable=no,top=0,left=0,menubar=0");
}

function fixupRelativeUrl(url) {
	var loc = String(location);
	var base = loc.replace(/index\.php.*/, '');
	if (base == loc)
		base = loc.replace(/(.*\/wiki\/).*/, '$1');
	if (base == loc)
		throw("fixupRelativeUrl error: " + loc);
	return base + url;
}

CreatePageShowHelp = function () {
	var help_section = document.getElementById ('createpage_help_section') ;
	var show_help = document.getElementById ('createpage_show_help') ;
	var hide_help = document.getElementById ('createpage_hide_help') ;

	help_section.style.display = '' ;
	show_help.style.display = 'none' ;
	hide_help.style.display = '' ;
}

CreatePageHideHelp = function () {
	var help_section = document.getElementById ('createpage_help_section') ;
	var show_help = document.getElementById ('createpage_show_help') ;
	var hide_help = document.getElementById ('createpage_hide_help') ;

	help_section.style.display = 'none' ;
	show_help.style.display = '' ;
	hide_help.style.display = 'none' ;
}

CreatePageShowThrobber = function (text) {
	return ;
	var Throbber = document.getElementById ('ajaxProgressIcon') ;
	Throbber.style.visibility = 'visible' ;
}

CreatePageHideThrobber = function (text) {
	return ;
	var Throbber = document.getElementById ('ajaxProgressIcon') ;
	Throbber.style.visibility = 'hidden' ;
}


window.onload = function() {
	/* go with editform, then make a div for content */
	var editform = document.getElementById ('editform') ;
	var edit_text = document.getElementById ('wpTextbox1') ;
	var content = edit_text.value ;
        
	var WikiwygDiv = document.createElement ('div') ;
	WikiwygDiv.setAttribute ('id','wikiwyg') ;

	var WikiwygIframe = document.createElement ('iframe') ;
	WikiwygIframe.setAttribute ('id','wikiwyg-iframe') ;
	WikiwygIframe.setAttribute ('height','0') ;
	WikiwygIframe.setAttribute ('width','0') ;
	WikiwygIframe.setAttribute ('frameborder','0') ;
	document.editform.insertBefore (WikiwygIframe, edit_text) ;
	document.editform.insertBefore (WikiwygDiv, WikiwygIframe) ;
	
	var WikiwygConfig = {
		doubleClickToEdit: true ,
		editHeightMinimum: 300 ,
		wysiwyg: {
			iframeId: 'wikiwyg-iframe' 			
		},
		toolbar: {
			imagesLocation: '../../wikiwyg/images/',
			markupRules: {
				 link: ['bound_phrase', '[', ']']
			 }, 
	       		 controlLayout: [
			        'link',
			        'www',
			        'bold',
			        'italic', '|' ,
			        'strike',
			        'pre',
			        'p',
			        'h1',
			        'h2',		
			        'h3',
			        'h4', '|',		
			        'ordered',				
			        'unordered',
			        'hr',
			        'table' ,
				'save' ,
				'cancel' ,
				'mode_selector'
			]
		},
		modeClasses: [
		     'Wikiwyg.Wysiwyg.Custom' ,		
		     'Wikiwyg.Wikitext.Custom' ,
		     'Wikiwyg.Preview.Custom'
		     ]
	} ;
	/* check if wysiwyg enabled, if not, remove the mode */
	if (wgUseWysiwyg == 0) {
		WikiwygConfig.modeClasses.shift () ;
	}

	WikiwygInstance = new Wikiwyg.Test ();
	WikiwygInstance.createWikiwygArea(WikiwygDiv, WikiwygConfig);	

	if (WikiwygInstance.enabled) {
		setTimeout("WikiwygInstance.editMode();",400);
       		document.getElementById ('wpTextbox1').style.display = 'none' ;
       		document.getElementById ('toolbar').style.display = 'none' ; 
	        var SaveLink = document.getElementById ('wpSave') ;
		var PreviewMode = document.getElementById ('wpPreview') ;
		var DiffMode = document.getElementById ('wpDiff') ;
		var CancelLink = document.getElementById ('wikiwyg_ctrl_lnk_Cancel') ;
		SaveLink.onclick = function() { eval('WikiwygInstance.saveChanges()'); return false };
		PreviewMode.onclick = function() { eval("WikiwygInstance.switchMode('Wikiwyg.Preview.Custom')"); return false };   
		DiffMode.onclick = function() { eval("WikiwygInstance.updateStuff()"); };   
		CancelLink.onclick = function () {} ;
		var cancel_loc = wgArticlePath.replace (/\$1/, wgPageName) ;
		CancelLink.href = cancel_loc ;

	} else {
		EditPageFallBack () ;
	}
}

proto = new Subclass('Wikiwyg.Test', 'Wikiwyg');

proto.saveChanges = function () {
	/* activate mode  */
	var class_name = this.config.modeClasses[1];
	var mode_object = this.mode_objects[class_name];
	this.current_mode.saveChanges (mode_object) ;				
}

proto.updateStuff = function () {
	var article_text = this.current_mode.normalizeContent () ;
	document.editform.wpTextbox1.value = article_text ;
//      document.editform.submit () ;
}

proto.clearModes = function () {
	/* run through all elements and clear them */
	    for (var i = 0; i < this.config.modeClasses.length; i++) {
	            var mode_radio = document.getElementById () ;
		    if (this.config.modeClasses[i] == this.current_mode.classname) {
			mode_radio.checked = 'checked' ;
		    } else {
			mode_radio.checked = false ;
		    }
		}
}

proto.selectRMode = function () {
//	this.clearModes () ;
//	var mode_id = document.getElementById (this.current_mode.classname) ;
//	mode_id.checked = 'checked' ;
}

proto.switchMode = function(new_mode_key) {	
	if ( Wikiwyg.MediaWiki.canSupportWysiwyg(this.div) ) {
		if ( ! new_mode_key.match(/preview/i) ) {
			Cookie.set("WikiwygFPEditMode", new_mode_key);
		}
	}
	var new_mode = this.modeByName(new_mode_key);
	/* update wpTextbox1 with normalized content _before_ switching */
	document.editform.wpTextbox1.value = this.current_mode.normalizeContent () ;
	Wikiwyg.prototype.switchMode.call(this, new_mode_key);
}

proto = new Subclass('Wikiwyg.Wysiwyg.Custom', 'Wikiwyg.Wysiwyg');

/* taken from Wysiwig.MediaWiki */

proto.enableThis = function() {
	Wikiwyg.Mode.prototype.enableThis.call(this);
	this.edit_iframe.style.border = '1px black solid';
	this.edit_iframe.width = '100%';
	this.setHeightOf(this.edit_iframe);
	this.fix_up_relative_imgs();
	this.get_edit_document().designMode = 'on';
	this.apply_stylesheets();
	this.enable_keybindings();
	this.clear_inner_html();
	var text = document.getElementById ('wpTextbox1') ;
	var to_convert = text.value ;
	/* todo wysiwygify - now, that phrase is certainly evil to spell */
	var self = this ;	
	this.convertWikitextToHtml (
		to_convert,
		function (to_convert) {
			self.set_inner_html (to_convert) ;
		}
        ) ;
}

proto.convertWikitextToHtml = function(wikitext, func) {
	WKWAjax.post(
			fixupRelativeUrl('index.php/Special:EZParser'),
			"text=" + encodeURIComponent(wikitext),
			func
		 );
}


proto.disableCreateButtons = function () {
	var EditPageLink = document.getElementById ('wpSave') ;
	EditPageLink.disabled = true ;
}

proto.normalizeContent = function () {
        var class_name = WikiwygInstance.config.modeClasses[1] ;
	var mode_object = WikiwygInstance.mode_objects[class_name] ;

	content = this.get_edit_document().body.innerHTML ;
	content = content.replace(/<br[^>]+./gi,"<br>") ;
        content = content.replace(/<br><br>/gi,"<p>") ;
	content = mode_object.convert_html_to_wikitext (content) ;
	return content ;
}

proto.saveChanges = function (mode) {
	/* needs parametrising */
	document.editform.action="index.php?title=" + wgPageName + "&action=submit" ;
	/* todo disable buttons plus give user some feedback */
	this.disableCreateButtons () ;
	var input1 = document.createElement ('input') ;
	var article_text = this.get_edit_document().body.innerHTML ;
	article_text = article_text.replace(/<br[^>]+./gi,"<br>") ;
	article_text = article_text.replace(/<br><br>/gi,"<p>") ;
	article_text = mode.convert_html_to_wikitext(article_text) ;
        document.editform.wpTextbox1.value = article_text ;
	/* todo expand */
	document.editform.submit() ;
}

proto.getCategories = function () {
	/* get categories separated by commas */
	var categories = document.getElementById ('category').value ;
	categories = categories.split (",") ;
	for (i=0;i<categories.length;i++) {
		this.addCategory (categories[i]) ;
	}
}

proto.addCategory = function (text) {
	if (text != '') {		
		document.editform.wpTextbox1.value += '[[Category:'+text+']]' ;	
	}
}

proto = new Subclass('Wikiwyg.Wikitext.Custom', 'Wikiwyg.Wikitext');

proto.initialize_object = function() {
	this.div = document.createElement('div');
	if (this.config.textareaId)
		this.textarea = document.getElementById(this.config.textareaId);
	else
		this.textarea = document.createElement('textarea');
	this.textarea.setAttribute ('id', 'wikiwyg_wikitext_textarea') ;
	this.div.appendChild(this.textarea);
	this.area = this.textarea;
	this.clear_inner_text();
}

proto.normalizeContent = function () {
	return this.textarea.value ;
}

proto.saveChanges = function () {
	article_text = this.normalizeContent () ;
        document.editform.wpTextbox1.value = article_text ;
	document.editform.submit() ;
}

proto.enableThis = function() {
	Wikiwyg.Mode.prototype.enableThis.call(this);
	this.textarea.style.width = '100%';
	this.textarea.style.height = '300px' ;
	this.setHeightOfEditor();
	this.enable_keybindings();
	this.textarea.value = document.getElementById('wpTextbox1').value ;
}

proto.format_table = function(element) {
	this.insert_new_line();
	this.appendOutput ('{|') ;
		this.assert_blank_line() ;
		this.walk(element);
		this.assert_blank_line() ;
		this.appendOutput ('|}') ;
		this.insert_new_line();
}

proto.format_tr = function(element) {
	this.appendOutput('|-') ;
	this.assert_new_line() ;
	this.appendOutput('|') ;
	this.walk(element) ;
	this.assert_blank_line() ;
	this.assert_new_line() ;
}

proto.format_td = function(element) {
	this.no_following_whitespace();
	this.walk(element);
	this.chomp();
	this.appendOutput('||');
}

proto.convertWikitextToHtml = function(wikitext, func) {
       WKWAjax.post(
			fixupRelativeUrl('index.php/Special:EZParser'),
			"text=" + encodeURIComponent(wikitext),
			func
		 );
}

proto.normalizeContent = function () {
	return this.textarea.value ;
}

proto.config = {
markupRules: {
link: ['bound_phrase', '[[', ']]'],
      www: ['bound_phrase', '[', ']'],
      bold: ['bound_phrase', "'''", "'''"],
      italic: ['bound_phrase', "''", "''"],
      strike: ['bound_phrase', "<s>", "</s>"],
      pre: ['start_lines', '    '],
      p: ['bound_line', '', ''],
      h1: ['bound_line', '= ', ' ='],
      h2: ['bound_line', '== ', ' =='],
      h3: ['bound_line', '=== ', ' ==='],
      h4: ['bound_line', '==== ', ' ===='],
      ordered: ['start_lines', '#'],
      unordered: ['start_lines', '*'],
      indent: ['start_lines', ''],
      hr: ['line_alone', '----'],
      table: ['line_alone', '{ | A | B | C |\n|   |   |   |\n|   |   |   | }']
	     }
}

proto = new Subclass ('Wikiwyg.Preview.Custom', 'Wikiwyg.Preview') ;

proto.normalizeContent = function () {
	var content = document.getElementById ('wpTextbox1').value ;
	return content ;
}

proto.saveChanges = function () {
	/* do nothing */
}

