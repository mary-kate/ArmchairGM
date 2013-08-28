
function makeGameUpdate (game_id, h_score_txt, v_score_txt, status_sel, category, divID) {
	
	var status = $El(status_sel).get('value');
	var status_desc = $El(status_sel).get('options')[$El(status_sel).get('selectedIndex')].text;
	var h_score = $El(h_score_txt).get('value');
	var v_score = $El(v_score_txt).get('value');
	
	//alert(game_id + " " + h_score + " " + v_score + " " + status + " " + status_desc + " " + category + " " + divID);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfMakePickGameUpdate&rsargs[]='+game_id+'&rsargs[]='+h_score+'&rsargs[]='+v_score+'&rsargs[]='+status+'&rsargs[]='+status_desc+'&rsargs[]='+category;
	var callback =
	{
	  success: function(t) {
		$El(divID).set('innerHTML', t.responseText);
	  }
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
/*
	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfMakePickGameUpdate&rsargs[]='+game_id+'&rsargs[]='+h_score+'&rsargs[]='+v_score+'&rsargs[]='+status+'&rsargs[]='+status_desc+'&rsargs[]='+category,
		
		{
		   method:'get',
		   onSuccess: function (t) {
			}
		}
		
		);
*/	
}

function editGameUpdate (game_id, h_score, v_score, status, status_desc, category, divID) {
	/*
	var status = $(status_sel).value;
	var status_desc = $(status_sel).options[$(status_sel).selectedIndex].text;
	var h_score = $(h_score_txt).value;
	var v_score = $(v_score_txt).value;
	*/
	//alert(game_id + " " + h_score + " " + v_score + " " + status + " " + status_desc + " " + category + " " + divID);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfEditPickGameUpdate&rsargs[]='+game_id+'&rsargs[]='+h_score+'&rsargs[]='+v_score+'&rsargs[]='+status+'&rsargs[]='+status_desc+'&rsargs[]='+category;	
	var callback =
	{
	  success: function(t) {
		$El(divID).set('innerHTML', t.responseText);
	  }
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

	/*
	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfEditPickGameUpdate&rsargs[]='+game_id+'&rsargs[]='+h_score+'&rsargs[]='+v_score+'&rsargs[]='+status+'&rsargs[]='+status_desc+'&rsargs[]='+category,
		
		{
		   method:'get',
		   onSuccess: function (t) {
			}
		}
		
		);
	*/
}

function editGameEntry (game_id, category, divID) {
	/*
	var status = $(status_sel).value;
	var status_desc = $(status_sel).options[$(status_sel).selectedIndex].text;
	var h_score = $(h_score_txt).value;
	var v_score = $(v_score_txt).value;
	*/
	//alert(game_id + " " + category + " " + divID);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfEditPickGameEntry&rsargs[]='+game_id+'&rsargs[]='+category;	
	var callback =
	{
	  success: function(t) {
		$El(divID).set('innerHTML', t.responseText);
	  }
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
	
/*
	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfEditPickGameEntry&rsargs[]='+game_id+'&rsargs[]='+category,
		
		{
		   method:'get',
		   onSuccess: function (t) {
			}
		}
		
		);
*/	
}

function updateGameEntry (game_id, sport_id, empty_count, divID) {
	
	var vis_abbr = $El('vis-abbr-'+game_id).get('value');
	var home_abbr = $El('home-abbr-'+game_id).get('value');
	var visitor = $El('visitor-'+game_id).get('value');
	var home = $El('home-'+game_id).get('value');
	var vis_addl = $El('visitor-addl-'+game_id).get('value');
	var home_addl = $El('home-addl-'+game_id).get('value');
	var game_date = $El('game-date-'+game_id).get('value');
	var category = $El('category-'+game_id).get('value');
	var identifier = $El('identifier-'+game_id).get('value');
	
	//alert(game_id + " " + vis_abbr + " " + home_abbr + " " + visitor + " " + home + " " + vis_addl + " " + home_addl + " " + game_date + " " + category + " " + identifier + " " + sport_id + " " + empty_count + " " + divID);
	
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfUpdatePickGameEntry&rsargs[]='+game_id+'&rsargs[]='+vis_abbr+'&rsargs[]='+visitor+'&rsargs[]='+vis_addl+'&rsargs[]='+home_abbr+'&rsargs[]='+home+'&rsargs[]='+home_addl+'&rsargs[]='+game_date+'&rsargs[]='+category+'&rsargs[]='+identifier+'&rsargs[]='+sport_id+'&rsargs[]='+empty_count;	
	var callback =
	{
	  success: function(t) {
		$El(divID).set('innerHTML', t.responseText);
	  }
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
/*
	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfUpdatePickGameEntry&rsargs[]='+game_id+'&rsargs[]='+vis_abbr+'&rsargs[]='+visitor+'&rsargs[]='+vis_addl+'&rsargs[]='+home_abbr+'&rsargs[]='+home+'&rsargs[]='+home_addl+'&rsargs[]='+game_date+'&rsargs[]='+category+'&rsargs[]='+identifier+'&rsargs[]='+sport_id+'&rsargs[]='+empty_count,
		
		{
		   method:'get',
		   onSuccess: function (t) {
			}
		}
		
		);
*/	
}

function removeGameUpdate (game_id, category, divID) {

	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfRemovePickGameResult&rsargs[]='+game_id+'&rsargs[]='+category;	
	var callback =
	{
	  success: function(t) {
		$El(divID).set('innerHTML', t.responseText);
	  }
	}
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
/*	
	new Ajax.Updater(
		divID, 'index.php?title=index.php&action=ajax&rs=wfRemovePickGameResult&rsargs[]='+game_id+'&rsargs[]='+category,
		
		{
		   method:'get',
		   onSuccess: function (t) {
			}
		}
		
		);
*/	
}

function removeGameEntry (game_id, divID) {
	
	if (confirm ("Are you sure that you want to remove game: " + game_id)) {
		
		var sUrl = 'index.php?title=index.php&action=ajax&rs=wfRemovePickGameEntry&rsargs[]='+game_id;	
		var callback =
		{
		  success: function(t) {
			$El(divID).set('innerHTML', t.responseText);
		  }
		}
		var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
		/*
		new Ajax.Updater(
			divID, 'index.php?title=index.php&action=ajax&rs=wfRemovePickGameEntry&rsargs[]='+game_id,
			
			{
			   method:'get',
			   onSuccess: function (t) {
				}
			}
			
			);
			*/
	}
}

function generateIdentifier (game_id, sport_name) {
	var vis_abbr = $El('vis-abbr-'+game_id).get('value');
	var home_abbr = $El('home-abbr-'+game_id).get('value');
	var game_date = $El('game-date-'+game_id).get('value');
	
	if (game_date != "" && vis_abbr != "" && home_abbr != "") {
		var game_display_date = game_date.substr(0,game_date.indexOf(" ")).replace(/-/g,"");
		var identifier = sport_name+"_"+game_display_date+"_"+vis_abbr+"@"+home_abbr;
		$El('identifier-'+game_id).set('value', identifier);
	}
		

}
