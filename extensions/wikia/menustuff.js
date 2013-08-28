var m_timer;

var displayed_menus = new Array();
var last_displayed = '';
var last_over = '';


function menuItemAction(e) {
	clearTimeout(m_timer);

	
	if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	
	var source_id = '*';
	try {source_id = e.target.id;}
	catch (ex) {source_id = e.srcElement.id}

	if (source_id && menuitem_array[source_id]) {
		if ($(last_over)) $(last_over).style.backgroundColor="#FFF";
		last_over = source_id;
		$(source_id).style.backgroundColor="#FFFCA9";
		check_item_in_array(menuitem_array[source_id]);
	}
}

function check_item_in_array(item) {
	clearTimeout(m_timer);
	var sub_menu_item = 'sub-menu' + item;
	
	if (last_displayed == '' || ((sub_menu_item.indexOf(last_displayed) != -1) && (sub_menu_item != last_displayed))) {
		do_menuItemAction(item);
	}
	else {
		var exit = false;
		count = 0;
		var the_last_displayed;
		while( !exit && displayed_menus.length > 0 ) {
			the_last_displayed = displayed_menus.pop();
			if ((sub_menu_item.indexOf(the_last_displayed) == -1)) {
				doClear(the_last_displayed, '');
			}
			else {
				displayed_menus.push(the_last_displayed);
				exit = true;
				do_menuItemAction(item);
			}
			
			count++;
		}

		do_menuItemAction(item);
	}
}

function do_menuItemAction(item) {
	if ($('sub-menu'+item)) {
		$('sub-menu'+item).style.display="block";
		displayed_menus.push('sub-menu'+item);
		last_displayed = 'sub-menu'+item;
	}

}

function sub_menuItemAction(e) {
	clearTimeout(m_timer);
	
	if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	
	var source_id = '*';
	try {source_id = e.target.id;}
	catch (ex) {source_id = e.srcElement.id}

	if (source_id && submenuitem_array[source_id]) {
	
		check_item_in_array(submenuitem_array[source_id]);
		
		if (source_id.indexOf("_")) {
			
			if (source_id.indexOf("_", source_id.indexOf("_"))) {
				var second_start = source_id.substr(4 + source_id.indexOf("_"));
				var second_uscore = second_start.indexOf("_");
				try {
					$(source_id.substr(4,source_id.indexOf("_")+second_uscore-1)).style.backgroundColor="#FFFCA9";
				}
				catch (ex) {}
			}
			else {
				$(source_id.substr(4)).style.backgroundColor="#FFFCA9";
			}
		}
		
	}
	
}

function clearBackground(e) {
	if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	
	var source_id = '*';
	try {source_id = e.target.id;}
	catch (ex) {source_id = e.srcElement.id}
	if ($(source_id)) {
		$(source_id).style.backgroundColor="#FFF";
		clearMenu(e);
	}
}


function clearMenu(e) {

	
	if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	
	var source_id = '*';
	try {source_id = e.target.id;}
	catch (ex) {source_id = e.srcElement.id}

		clearTimeout(m_timer);
		m_timer = setTimeout(function() { doClearAll(); }, 200);
	
}
function doClear(item, type) {

	if ($(type+item)) {
		$(type+item).style.display="none";
	}

}


function doClearAll() {
	if ($("menu-item" + displayed_menus[0].substr(displayed_menus[0].indexOf("_")))) $("menu-item" + displayed_menus[0].substr(displayed_menus[0].indexOf("_"))).style.backgroundColor="#FFF";
	var the_last_displayed;
	var exit = false;
	while( !exit && displayed_menus.length > 0 ) {
		the_last_displayed = displayed_menus.pop();
		
		doClear(the_last_displayed, '');
		
	}
		
		last_displayed = '';

}
