
var WikiwygInstance ;

/* copied and adapted from upload image script... */
// apply tagOpen/tagClose to selection in textarea,
// use sampleText instead of selection if there is none
// copied and adapted from phpBB
function insertTags(tagOpen, tagClose, sampleText) {
	if (WikiwygInstance.enabled) {
		var out = WikiwygInstance.current_mode ;
	        out.insert_html (tagOpen + sampleText + tagClose) ;
	}
}

/* fall back if edit mode not supported - provide a normal interface  */
CreatePageFallBack = function () {
	var loading_mesg = document.getElementById ('loading_mesg') ;
	loading_mesg.style.display = 'none' ;
	var input1 = document.createElement ('textarea') ;
	input1.setAttribute ('name','wpTextbox1') ;
	input1.setAttribute ('id','wpTextbox1') ;
	input1.setAttribute ('rows', 25) ;
	input1.setAttribute ('cols', 80) ;
	var backup_txt = document.getElementById ('backup_textarea_placeholder') ;
	backup_txt.appendChild (input1) ;
	/* todo give standard edit toolbar - mainly for Opera users */
}


document.insertTags = insertTags ;

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
	if (loc.match (/\?index\.php/i) != '')  {
		var base = loc.replace (/&action=.*$/i, '');
		return base ;
	} else {
		var base = loc.replace(/index\.php.*/, '');
		if (base == loc)
			base = loc.replace(/(.*\/wiki\/).*/, '$1');
		if (base == loc)
			throw("fixupRelativeUrl error: " + loc);	
		return base + url;
	}
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
//	var Throbber = document.getElementById ('ajaxProgressIcon') ;
//	Throbber.style.visibility = 'visible' ;
}

CreatePageHideThrobber = function (text) {
//	var Throbber = document.getElementById ('ajaxProgressIcon') ;
//	Throbber.style.visibility = 'hidden' ;
}


window.onload = function() {
        var WikiwygDiv = document.getElementById ('wikiwyg') ;
	var category_wrapper = document.getElementById ('category_wrapper') ;
	CreatePageShowThrobber ('') ;
	category_wrapper.style.display = 'block' ;
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
			        'table'
			]
		},
		modeClasses: [
		     'Wikiwyg.Wysiwyg.Custom' ,
		     'Wikiwyg.Wikitext.Custom'
		     ]
	} ;
	WikiwygInstance = new Wikiwyg.Test ();
	WikiwygInstance.createWikiwygArea(WikiwygDiv, WikiwygConfig);	
	if (WikiwygInstance.enabled) {
		setTimeout("WikiwygInstance.editMode();",400);
	} else {
		CreatePageFallBack () ;
	}
	document.getElementById ('loading_mesg').style.display = 'none' ;

	// register edit page
	var CreatePageLinkUp = document.getElementById ('wpSaveUp') ;
	var CreatePageLinkBottom = document.getElementById ('wpSaveBottom') ;
	var self = WikiwygInstance ;	
	CreatePageLinkUp.onclick = function() { eval('WikiwygInstance.saveChanges()'); return false };
	CreatePageLinkBottom.onclick = function() { eval('WikiwygInstance.saveChanges()'); return false };	
	CreatePageHideThrobber ('') ;
}

proto = new Subclass('Wikiwyg.Test', 'Wikiwyg');

    proto.modeClasses = [
        'Wikiwyg.Wysiwyg.Custom',
	'Wikiwyg.Wikitext.Custom'
    ];

proto.saveChanges = function () {
	if (!this.checkContents()) {
		return false ;
	}
	var title = document.getElementById ('title') ;
	if (!this.checkIfArticleExists (title.value)) {
		return false ;
	}
}

proto.checkContents = function () {
	var error_msg = document.getElementById ('createpage_messenger') ;
	var title = document.getElementById ('title') ;
	article_text = this.current_mode.get_edit_document().body.innerHTML ;
	article_text = article_text.replace(/<br[^>]+./gi,"<br>") ;
        article_text = article_text.replace(/<br><br>/gi,"<p>") ;
	var class_name = this.config.modeClasses[1];
	var mode_object = this.mode_objects[class_name];
	article_text = mode_object.convert_html_to_wikitext(article_text) ;
	if ( (title.value == '') || (article_text == '') ) {
		error_msg.innerHTML = "You need to specify both title and some content to create an article." ;
		error_msg.style.display = 'block' ;
		return false ;
	}
	return true ;
}

proto.checkIfArticleExists = function (article) {
	CreatePageShowThrobber ('') ;
	WKWAjax.post (
		fixupRelativeUrl('Special:Createpage') ,
		'action=check&to_check=' + article ,
		function (response) {
			WikiwygInstance.handleArticleExistsResponse (response)
		}
	) ;
}

proto.handleArticleExistsResponse = function (response) {
	if (response.indexOf("pagetitleexists") != -1) {
		var error_msg = document.getElementById ('createpage_messenger') ;
		var title = document.getElementById ('title') ;
		error_msg.innerHTML = "That title already exists. Please choose another title." ;
		error_msg.style.display = 'block' ;
		CreatePageHideThrobber ('') ;
		return false ;
	} else {
		/* select wikitext mode */
		var class_name = this.config.modeClasses[1];
		var mode_object = this.mode_objects[class_name];
		this.current_mode.saveChanges (mode_object) ;				
		CreatePageHideThrobber ('') ;
		return true ;
	}
}

proto = new Subclass('Wikiwyg.Wysiwyg.Custom', 'Wikiwyg.Wysiwyg');

/* taken from Wysiwig.MediaWiki */

proto.apply_stylesheets = function() {
	Wikiwyg.Wysiwyg.prototype.apply_stylesheets.apply(this, arguments);
	var head = this.get_edit_document().getElementsByTagName("head")[0];
	var style_string = "body { font-size: small; }";
	this.append_inline_style_element(style_string, head);
}

proto.enableStarted = function () {
	this.wikiwyg.toolbarObject.disableThis();
	this.wikiwyg.toolbarObject.enableMessage();
}

proto.enableFinished = function (){
	this.wikiwyg.toolbarObject.enableThis();
	this.wikiwyg.toolbarObject.disableMessage();
}

proto.disableCreateButtons = function () {
	var CreatePageLinkUp = document.getElementById ('wpSaveUp') ;
	var CreatePageLinkBottom = document.getElementById ('wpSaveBottom') ;
	CreatePageLinkUp.disabled = true ;
	CreatePageLinkBottom.disabled = true ;
}

proto.saveChanges = function (mode) {
	/* needs parametrising */
	var title = document.getElementById ('title') ;
	document.editform.action="index.php?title=" + title.value + "&action=submit" ;
	/* todo disable buttons plus give user some feedback */
	this.disableCreateButtons () ;
	var input1 = document.createElement ('input') ;
	input1.setAttribute ('name','wpTextbox1') ;
	input1.setAttribute ('id','wpTextbox1') ;
	input1.setAttribute ('type','hidden') ;
	document.editform.appendChild (input1) ;
	var article_text = this.get_edit_document().body.innerHTML ;
	article_text = article_text.replace(/<br[^>]+./gi,"<br>") ;
	article_text = article_text.replace(/<br><br>/gi,"<p>") ;
	article_text = mode.convert_html_to_wikitext(article_text) ;
        document.editform.wpTextbox1.value = article_text ;
	/* todo expand */
	this.getCategories () ;
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

proto.do_p = function() {
	this.selection_mangle(
			function(that) {
			if (that.sel == '') return false;
			that.sel = that.sel.replace(/^\=* */gm, '');
			that.sel = that.sel.replace(/ *\=*$/gm, '');
			return true;
			}
			)
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


	proto.format_div = function(element) {
		if (! this.previous_was_newline_or_start())
			this.insert_new_line();

		this.walk(element);
		this.assert_blank_line();
	}


proto.normalizeDomWhitespace = function(dom) {
	Wikiwyg.Wikitext.prototype.normalizeDomWhitespace.call(this, dom);
	var tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li'];
	for (var ii = 0; ii < tags.length; ii++) {
		var elements = dom.getElementsByTagName(tags[ii]);
		for (var i = 0; i < elements.length; i++) {
			var element = elements[i];
			if (element.firstChild && element.firstChild.nodeType == '3') {
				element.firstChild.nodeValue =
					element.firstChild.nodeValue.replace(/^\s*/, '');
			}
			if (element.lastChild && element.lastChild.nodeType == '3') {
				element.lastChild.nodeValue =
					element.lastChild.nodeValue.replace(/\s*$/, '');
			}
		}
	}
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

