var posted = 0;


function add_status(){
	if($("user_status_text").value && !posted){
		posted = 1;
		var url = "index.php?action=ajax";
		var pars = 'rs=wfAddUserStatusNetwork&rsargs[]=' + __sport_id__ + '&rsargs[]=' + __team_id__ + '&rsargs[]=' + escape($("user_status_text").value) + '&rsargs[]=' + __updates_show__

		var callback = {
			success: function( oResponse ) {
				posted = 0;
				window.location=__redirect_url__;
				 
			}
		}
		var request = YAHOO.util.Connect.asyncRequest('POST', url, callback, pars);	
		
		/*
		var myAjax = new Ajax.Request(
			url, {
				method: 'post', 
				parameters: pars,
				onSuccess: function(originalRequest) {
					posted = 0;
					window.location='" . str_replace("&amp;","&",SportsTeams::getFanUpdatesURL($sport_id,$team_id)) . "';
				}
		});
		*/
	}
}
			
function vote_status(id,vote){
	
	var url = "index.php?action=ajax";
	var pars = 'rs=wfVoteUserStatus&rsargs[]=' + id + '&rsargs[]=' + vote

	var callback = {
		success: function( oResponse ) {
			$('user-status-vote-'+id).innerHTML = oResponse.responseText
			 
		}
	}
	var request = YAHOO.util.Connect.asyncRequest('POST', url, callback, pars);	

	/*
	var myAjax = new Ajax.Updater(
		'user-status-vote-'+id,
		url, {
			method: 'post', 
			parameters: pars,
			onSuccess: function(originalRequest) {
				
			}
		});
		*/
}

function delete_message(id){
	if(confirm('Are you sure you want to delete this thought?')){
		var url = "index.php?action=ajax";
		var pars = 'rs=wfDeleteUserStatus&rsargs[]=' + id
		
	var callback = {
		success: function( oResponse ) {
			window.location='/Special:UserStatus';
		}
	}
		/*
		var myAjax = new Ajax.Request(
			url, {
				method: 'post', 
				parameters: pars,
				onSuccess: function(originalRequest) {
					window.location='index.php?title=Special:UserStatus';
				}
		});
		*/
	}
	
	var request = YAHOO.util.Connect.asyncRequest('POST', url, callback, pars);	
	
}
