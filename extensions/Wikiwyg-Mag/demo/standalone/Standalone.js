proto = new Subclass('Wikiwyg.Standalone', 'Wikiwyg');

proto.saveChanges = function() {
    var self = this;

	this.current_mode.toHtml(
            function(html) {
                var wikitext_mode = self.mode_objects['Wikiwyg.Wikitext'];
                wikitext_mode.convertHtmlToWikitext(
                    html,
                    function(wikitext) {alert(wikitext) }
                );
            }
        );
		

}

proto = Wikiwyg.Toolbar.prototype;
proto.config.controlLayout = [
    'bold',
    'italic',
	'strike',
	'|',
	 'ordered',
	 'unordered',
	'|',
	'h1',
    'h2',
	'h3',
	'h4',
	'h5',
	'h6',
	'hr',
	  '|',
	'link', 
	'table'
];