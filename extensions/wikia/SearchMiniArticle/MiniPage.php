<?php

class MiniPage extends Article{

	var $title = null;
	
	function __construct (&$title){
		parent::__construct(&$title);
	}

	
	function view(){
		global $wgOut, $wgUser, $wgRequest, $wgTitle;
	
		$wgOut->addHTML("<b>This is a mini article!</b>");
		
		parent::view();

	}
	
}


?>