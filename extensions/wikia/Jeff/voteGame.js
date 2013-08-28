function doHover(divID){
	$(divID).setStyle({backgroundColor: '#F2F4F7'});
}

function endHover(divID){
	$(divID).setStyle({backgroundColor: ''});
}

function imageSwap(divID, type, on) {
	
	if (on==1) {
		$(divID).src='images/common/'+type+'-on.gif';
	} else {
		$(divID).src='images/common/'+type+'.gif';
	}
	
	
}

function makeGamePick (userid, username, game, choice, wager, wager_level, wager_choices, divID) {

	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfMakePick&rsargs[]='+userid+'&rsargs[]='+game+'&rsargs[]='+choice+'&rsargs[]='+wager+'&rsargs[]='+wager_level+'&rsargs[]='+wager_choices+'&rsargs[]='+username,
		
		{
		   method:'get',
		   onSuccess: function (t) {
				$(divID).setStyle({backgroundColor: '#F2F4F7'});
				$(divID).onmouseover=null;
				$(divID).onmouseout=null;
				
				var current_wager_value;
				
				
				if ($('total-wagers')) {
					if (wager_amounts[game] != null && wager_amounts[game] != 0) {
						current_wager_value = (parseInt($('total-wagers').innerHTML) - wager_amounts[game]) + wager;
					}
					else {
						current_wager_value = parseInt($('total-wagers').innerHTML) + wager;
					}
					
					
						
					 
					 if (current_wager_value != 0) {
						$('total-wagers').innerHTML = current_wager_value;
					 }
					 else {
						 $('total-wagers-text').innerHTML = no_wager_text;
					 }

						
				}
				else {
					$('total-wagers-text').innerHTML = "Your wagers for this day total <span id='total-wagers'>" + wager + "</span> points.";
				}
			 
				wager_amounts[game] = wager;

			}
		}
		
		);
	
}

function doWagerHover (spanID) {
	$(spanID).setStyle({border: '1px solid #797979'});
	$(spanID).setStyle({padding: '2px'});

}

function endWagerHover (spanID) {
	$(spanID).setStyle({border: '0px solid #797979'});
	$(spanID).setStyle({padding: '3px'});
}