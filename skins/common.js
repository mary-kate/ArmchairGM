var last_clicked = ""

function submenu(id){
	
	//clear all tab classes
	on_tabs = $$("tab-on")
	for(x = 0 ; x<= on_tabs.length-1 ; x++)$(on_tabs[x]).className="tab-off"
	
	on_tabs = $$("sub-menu")
	for(x = 0 ; x<= on_tabs.length-1 ; x++)YAHOO.widget.Effects.Hide($(on_tabs[x]))
		
	//hide submenu that might have been previously clicked
	if (last_clicked)YAHOO.widget.Effects.Hide("submenu-"+last_clicked)

	//update tab class you clicked on/show its submenu
	if( $D.hasClass("menu-"+id,"tab-off") ) $D.addClass( ("menu-"+id),"tab-on" );
		
	YAHOO.widget.Effects.Show("submenu-"+id)

	last_clicked = id
}

function editMenuToggle() {
	
	var submenu = document.getElementById("edit-sub-menu-id")
	
	if (submenu.style.display == "block") {
		submenu.style.display = "none"
	} else {
		submenu.style.display = "block"
	}
}

