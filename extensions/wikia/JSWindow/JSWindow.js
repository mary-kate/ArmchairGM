function sendEmail(){
	err = ""
	if(document.emailform.emailto.value=="")err+= "Please enter the email to send to \n";
	if(document.emailform.yourname.value=="")err+= "Please enter your name \n";
	if(document.emailform.emailfrom.value=="")err+= "Please enter your email \n";
	if(!err){
		getContent("index.php?title=Special:EmailThis","pageid=" + document.emailform.pageid.value + "&emailto=" + document.emailform.emailto.value + "&yourname=" + document.emailform.yourname.value + "&emailfrom=" + document.emailform.emailfrom.value + "&message=" + document.emailform.message.value,"pageToolsContent")
	}else{
		alert(err)
	}
}

var jsWindow = Class.create();

jsWindow.prototype = {
   initialize: function(content,options) {
   		this.options = options
		this.editWindow = document.createElement('div');
		
		$D.addClass(this.editWindow,this.options.className);
		//$(this.editWindow).addClassName(this.options.className);
		document.body.appendChild(this.editWindow); 
		new YAHOO.widget.Effects.Hide(this.editWindow);
		//this.editWindow.hide();

		$El(this.editWindow).setStyle('top', (this.getYpos() + 120) + 'px'); 
		$El(this.editWindow).setStyle('display', 'block');  //Safari Fix
		
		//Element.setStyle(this.editWindow,{'top':  (this.getYpos() + 120) + 'px' });
		//Element.setStyle(this.editWindow,{'display':  'block' }); //Safari Fix
		WindowWidth = $D.getStyle(this.editWindow,"width").replace("px","");
		//WindowWidth = Element.getStyle(this.editWindow,"width").replace("px","")
		BrowserWidth = ((window.innerWidth)?window.innerWidth:document.body.clientWidth);
		if(WindowWidth){
			$El(this.editWindow).setStyle('left', (BrowserWidth/2) - (WindowWidth/2) + 'px');
			//Element.setStyle(this.editWindow,{'left':  (BrowserWidth/2) - (WindowWidth/2) + 'px' });
		}
		closeBox = "<div id=close style='float:right;'><span style='cursor:hand;cursor:pointer'><img src=images/closeWindow.gif id=close ></span></div>"
		$(this.editWindow).innerHTML = closeBox + '<div id=jsWindow style="display:none" >'+ content + '</div>';
		new YAHOO.widget.Effects.Show(this.editWindow);
		//this.editWindow.show();
		
		new YAHOO.widget.Effects.BlindDown( $("jsWindow") );
		//new Effect[ 'BlindDown']($("jsWindow"));
		
		var closeWindow =  function(){
			
			YAHOO.util.Element.remove(this.editWindow);
			//Element.remove(this.editWindow)
		}.bind(this) 		
		YAHOO.util.Event.on('close', 'click', closeWindow); 

   },
   
   	getYpos: function(){
		if (self.pageYOffset) {
			this.yPos = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){
			this.yPos = document.documentElement.scrollTop; 
		} else if (document.body) {
			this.yPos = document.body.scrollTop;
		}
		return this.yPos
	}
}
	
