function vote_status(id,vote){
	//Effect.Fade('user-status-vote-'+id, {duration:1.0, fps:32});
	YAHOO.widget.Effects.Hide('status-update');
	
	var url = "index.php?action=ajax";
	var pars = 'rs=wfVoteUserStatus&rsargs[]=' + id + '&rsargs[]=' + vote
	
	var callback = {
		success: function( oResponse ) {
			posted = 0;
			$('status-update').innerHTML = oResponse.responseText
			YAHOO.widget.Effects.Appear('status-update' );
			 
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
				Effect.Appear('user-status-vote-'+id, {duration:2.0, fps:32});
			}
	});
	*/
	
	
}

var last_box;

function detEnter(e,num,sport_id,team_id) {
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	if (keycode == 13){
		add_message(num,sport_id,team_id)
		return false;
	} else return true;
}

function close_message_box(num){
	YAHOO.widget.Effects.Fade("status-update-box-"+num);
}

function show_message_box(num,sport_id,team_id){
	if(last_box)YAHOO.widget.Effects.Hide("status-update-box-"+last_box);
	$("status-update-box-"+num).innerHTML = '<input  type="text" id="status_text" onKeyPress="detEnter(event,' + num + ',' + sport_id + ',' + team_id + ' )" value="" maxlength="150"> <input type="button" class="site-button" value="add" onclick="add_message(' + num + ',' + sport_id + ',' + team_id + ' )"  > <input type="button" class="site-button" value="cancel" onclick="close_message_box(' + num + ' )"  >'
	YAHOO.widget.Effects.Appear("status-update-box-"+num);
	last_box = num;
}

function add_message(num,sport_id,team_id){
	if($("status_text").value && !posted){
		
		posted = 1;
		var url = "index.php?action=ajax";
		var pars = 'rs=wfAddUserStatusProfile&rsargs[]=' + sport_id +'&rsargs[]=' + team_id + '&rsargs[]=' + escape($("status_text").value) + '&rsargs[]=10'
		YAHOO.widget.Effects.Hide('status-update');
		
		var callback = {
		success: function( oResponse ) {
			posted = 0;
			
			if ($('status-update') == null) {
				var theDiv2 = document.createElement('div');
				YAHOO.util.Dom.addClass(theDiv2, "status-container");
				theDiv2.setAttribute("id", "status-update");
				YAHOO.util.Dom.insertBefore(theDiv2, YAHOO.util.Dom.getFirstChild('user-page-left'));

				var theDiv = document.createElement('div');
				//<div class="user-section-heading">
				YAHOO.util.Dom.addClass(theDiv, "user-section-heading");
				//theDiv.innerHTML = '<div class="user-section-title">Latest Thought</div><div class="user-section-action"><a href="http://fp029.sjc.wikia-inc.com/index.php?title=Special:UserStatus&amp;user=TheyGetTheJobDone" rel="nofollow">View All</a></div><div class="status-container" id="status-update"></div>';
				theDiv.innerHTML = '<div class="user-section-title">' + __thoughts_text__ + '</div>';
				theDiv.innerHTML += '<div class="user-section-action"><a href="' + __more_thoughts_url__ + '" rel="nofollow">' + __view_all__ + '</a></div>';
				YAHOO.util.Dom.insertBefore(theDiv, YAHOO.util.Dom.getFirstChild('user-page-left'));
		

			}
			$('status-update').innerHTML = oResponse.responseText
			YAHOO.widget.Effects.Appear('status-update' );


			close_message_box(num)
		}
		};
		var request = YAHOO.util.Connect.asyncRequest('POST', url, callback, pars);	
	}
}

/*

function add_message(num,sport_id,team_id){
	if($("status_text").value && !posted){
		
		posted = 1;
		var url = "index.php?action=ajax";
		var pars = 'rs=wfAddUserStatusProfile&rsargs[]=' + sport_id +'&rsargs[]=' + team_id + '&rsargs[]=' + escape($("status_text").value) + '&rsargs[]=10'
		YAHOO.widget.Effects.Hide('status-update');
		
		var callback = {
		success: function( oResponse ) {
			posted = 0;
			$('status-update').innerHTML = oResponse.responseText
			YAHOO.widget.Effects.Appear('status-update' );
			 
			close_message_box(num)
		}
		};
		var request = YAHOO.util.Connect.asyncRequest('POST', url, callback, pars);	
	}
}
*/