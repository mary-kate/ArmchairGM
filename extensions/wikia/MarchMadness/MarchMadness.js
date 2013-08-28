function make_pick(bracket, game, team, dest, opp, num_teams) {
	//bracket = bracket.toLowerCase();
	//var is_finals = document.getElementById("is_finals").value;
	
	document.getElementById("any_changes").value = '1';
	$("madness-messages").innerHTML = "";
	
	if (document.getElementById(team).innerHTML != "&nbsp;") {
		document.getElementById(bracket+"_team_"+dest).innerHTML = document.getElementById(team).innerHTML;
		document.getElementById(bracket+"_team_"+dest).setAttribute('team', document.getElementById(team).getAttribute('team'));
		//alert(document.getElementById(bracket+"_team_"+dest).getAttribute('team'));
	}
	
	var num_picks = ((num_teams * 2) - 1);
	while (dest < num_picks) {
		if ((dest%2) == 1) {
			dest+=1;
		}
		game = dest/2;
		dest = game+num_teams;
			/* changed 20080311 to make it switch to the team instead of blank (since we switched to being able to submit at any time)
		if ((document.getElementById(bracket+"_team_"+dest).innerHTML == document.getElementById(opp).innerHTML) && (document.getElementById(bracket+"_team_"+dest).innerHTML != "&nbsp;")) {
			document.getElementById(bracket+"_team_"+dest).innerHTML = "&nbsp;";
			document.getElementById(bracket+"_team_"+dest).setAttribute('team', '');
			*/
		if ((document.getElementById(bracket+"_team_"+dest).innerHTML == document.getElementById(opp).innerHTML) && (document.getElementById(bracket+"_team_"+dest).getAttribute('team') != '')) {
			document.getElementById(bracket+"_team_"+dest).innerHTML = document.getElementById(team).innerHTML;
			document.getElementById(bracket+"_team_"+dest).setAttribute('team', document.getElementById(team).getAttribute('team'));
		}
		
	}
	var done = true;
	for (var i=num_teams+1; i<=num_picks; i++) {
		if (document.getElementById(bracket+"_team_"+i).getAttribute('team') == '') {
			done = false;
			break;
		}
	}
	if (done) {
		var next_link = document.getElementById("next_link").value;
		document.getElementById("complete-check").innerHTML = "<div id='submit-button' class=\"bracket-button-off submit-picks\" onclick='calculateResultsAndSwitch(" + num_teams + ", \"" + bracket + "\", \"" + next_link + "\" );'>Submit Picks</div>";
	}
	else {
		document.getElementById("complete-check").innerHTML = "";
	}
	
}

function calculateResults(num_teams, bracket) {
	var entry = 1;
	var tournament_id=1;
		
	var num_picks = ((num_teams * 2) - 1);
	//var picks = "Picks for " + bracket + "\n";
	var bracket_id = document.getElementById("bracket_id").value;
	var is_finals = parseInt(document.getElementById("is_finals").value);
	var entry_id = document.getElementById("entry_id").value;
	var next_bracket = document.getElementById("next_bracket").value;
	var picks = "";
	if (is_finals) {
		var pick_count = num_teams+1;
	}
	else {
		var pick_count = 1;
	}
	for (var i=num_teams+1; i<=num_picks; i++) {
		//picks += "Pick " + bracket + "_" + pick_count + ": " + document.getElementById(bracket+"_team_"+i).innerHTML + " : " + bracket + "_" +document.getElementById(bracket+"_team_"+i).getAttribute('team') + "\n";
		var team = document.getElementById(bracket+"_team_"+i).getAttribute('team');
		var round = document.getElementById(bracket+"_team_"+i).getAttribute('round');
		if (is_finals) {
			var pick= team;
			//round 
		}
		else {
			var pick= bracket + "_" +team;
		}
		
		if (team != "") {
			picks += bracket+"_"+pick_count+":"+ pick + ":" + round + ":" + bracket_id + ":" + entry_id;
			if (i< num_picks) {
				picks+= ";";
			}
		}
		
		pick_count++;
	}
	
	if (picks.charAt(picks.length-1) == ";") {
		picks = picks.substr(0, picks.length-1);
	}
	
	document.getElementById("bracket-picks-field").value = picks;
	//document.getElementById("bracket-picks-return").value = "/Special:MarchMadness?entry_id=" + entry_id + "&bracket=" + escape(next_bracket);
	//document.getElementById("bracket-picks-return").value = location.href;
	//document.getElementById("bracket-picks").submit();
	//alert(document.getElementById("bracket-picks-field").value);
	//alert(picks);
	
	return picks;
}

function calculateResultsAndSwitch(num_teams, bracket, which) {
	
	if (document.getElementById("any_changes").value == '1') {
		//alert("changes made, submitting to " + which);
		//document.getElementById("bracket-picks").setAttribute("action", which);
		var picks = calculateResults(num_teams, bracket);
		
		var postBody = 'bracket-picks-field='+escape(picks);
		var sUrl = 'index.php?title=index.php&action=ajax&rs=wfProcessPicks';
		
		var callback =
		{
		  success: function(t) {
			  
			  //alert(t.responseText);
			  
			//$(divID).innerHTML = t.responseText;
			  var response_string = t.responseText.replace(/^\s*/, "").replace(/\s*$/, "");
			  if (response_string == "ok") {
				  
				  if (location.href!=which) {
					  location.href=which;
				  }
				  else {
					  $("madness-messages").innerHTML = "Your picks have been submitted.";
					  document.getElementById("any_changes").value = '0';
				  }
				  
			  }
			
			
		  }
		  //failure: function(o) {/*failure handler code*/},
		  //argument: [argument1, argument2, argument3]
		}
		
		
		var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postBody);
	}
	else {
		//alert("no changes made, just going to " + which);
		location.href = which;
	}
	
}
/*
function create_group_get() {

	var group_name = escape($("group_name").value);
	var tournament_id = $("tournament_id").value;
	var is_private = $("is_private").value;
	var entry_name = escape($("entry_name").value);
	var divID = "madness-group-create";
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfCreateMadnessGroup&rsargs[]='+group_name+'&rsargs[]='+tournament_id+'&rsargs[]='+is_private+'&rsargs[]='+entry_name
	
	var callback =
	{
	  success: function(t) {
		$(divID).innerHTML = t.responseText;
		
		
	  }
	  //failure: function(o) {},
	  //argument: [argument1, argument2, argument3]
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

}
*/

function toggle_password() {
	if ($('is_private').value == '1') {
		new YAHOO.widget.Effects.Show('password-entry');
	}
	else {
		new YAHOO.widget.Effects.Hide('password-entry');
		$('password').value = '';
		$('confirm-password').value = '';
	}
}

function create_group() {

	var temp = $('group_name').value.replace(/^\s*/, "").replace(/\s*$/, "");
	if (temp == "") {
			$('madness-group-create-errors').innerHTML = "You must specify a name for this group.";
			new YAHOO.widget.Effects.Show('madness-group-create-errors');
			return "";
	}
	var group_name = temp;
	
	temp = $('entry_name').value.replace(/^\s*/, "").replace(/\s*$/, "");
	if (temp == "") {
			$('madness-group-create-errors').innerHTML = "You must specify an entry name for your entry in this group (you can change it later).";
			new YAHOO.widget.Effects.Show('madness-group-create-errors');
			return "";
	}
	
	var entry_name = temp;
	
	var group_desc = $("group_desc").value;

	if (group_name.length > 50 || entry_name.length > 50 || group_desc.length > 255) {
		$('madness-group-create-errors').innerHTML = "";
		if (group_name.length > 50) {
			$('madness-group-create-errors').innerHTML += "Your group name is too long (" + group_name.length + ").<br/>";
		}
		if (entry_name.length > 50) {
			$('madness-group-create-errors').innerHTML += "Your team name is too long (" + entry_name.length + ").<br/>";
		}
		if (group_desc.length > 255) {
			$('madness-group-create-errors').innerHTML += "Your group description is too long (" + group_desc.length + ").";
		}
		$('madness-group-create-errors').innerHTML += "Please note the max limits on each field.";
		new YAHOO.widget.Effects.Show('madness-group-create-errors');
		return "";
		
	}
	
	var tournament_id = $("tournament_id").value;
	var is_private = $("is_private").value;
	if (is_private == "1") {
		if ($("password").value != "" && $("password").value == $("confirm-password").value) {
			var password = $("password").value;
		}
		else {
			$('madness-group-create-errors').innerHTML = "The passwords that you specified do not match.  Please re-enter the password.";
			new YAHOO.widget.Effects.Show('madness-group-create-errors');
			return "";
		}
	}
	else {
		var password = "";
	}
	var divID = "madness-group-create";
	
	var postBody = 'group_name='+group_name+'&tournament_id='+tournament_id+'&is_private='+is_private+'&entry_name='+entry_name+'&password='+password+'&group_desc='+group_desc;
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfCreateMadnessGroup';
	
	var callback =
	{
	  success: function(t) {
		//$(divID).innerHTML = t.responseText;
		  var response_string = t.responseText.replace(/^\s*/, "").replace(/\s*$/, "");
		  if (response_string == "ok") {
			  location.href="/Special:SpringSillinessHome";
		  }
		  else if(response_string.substr(0,2) == "__") {
			  var group = response_string.substr(2);
			  location.href="/Special:SpringSillinessGroupInvite/" + group;
		  }
		  else {
			$("madness-group-create-errors").innerHTML = t.responseText;
			new YAHOO.widget.Effects.Show('madness-group-create-errors');
		  }
		
		
	  },
	  failure: function(o) {
		  $("madness-group-create-errors").innerHTML = o.responseText;
		new YAHOO.widget.Effects.Show('madness-group-create-errors');
	  }
	  //argument: [argument1, argument2, argument3]
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postBody);

}

function chooseFriendFromTb() {
	
	var friend_name = $("entry-tb").value;
	if (friend_name != "") {
		var selectedfriendval = friend_name;
		var selectedfriendtext = friend_name;
	
		chooseFriend(selectedfriendtext, selectedfriendval);
		$("entry-tb").value = "";
	}
}
	
function chooseFriendFromList() {
		var from_list = $("madness-friends-list");
		
		//friend = from_list.selectedIndex;
		
		var the_options = from_list.options;
		for (var i=0; i<the_options.length; i++) {
			if (the_options[i].selected) {
				var selectedfriendval = the_options[i].value;
				var selectedfriendtext = the_options[i].text;
				
				chooseFriend(selectedfriendtext, selectedfriendval);
			}
		}
}

function chooseFriend(selectedfriendtext, selectedfriendval) {
	
	var to_list = $("madness-invite-list");
	
	if (selectedfriendval != "#") {
		
		if (already_in_group[selectedfriendval]) {
			alert(selectedfriendval + " is already in group or has already been invited");
		}
		else {	
			var can_add = true;
			for (i=0; i<to_list.options.length; i++) {
				if (to_list.options[i].value == selectedfriendval) {
					can_add = false;
				}
			}
			if (can_add) {
				var newOpt = new Option(selectedfriendtext, selectedfriendval);
				
				to_list.options.add(newOpt);
			}
		}
		
	}
	
}

function selectAllToSend() {
	
	//var friend_list = $("madness-invite-list");
	var friend_list = $("madness-friends-list");
	var send_invites_to = "";
	if (friend_list) {
		for (var i=0; i<friend_list.options.length; i++) {
			//friend_list.options[i].selected = true;
			if (friend_list.options[i].selected) {
				send_invites_to += friend_list.options[i].value + ";"
				already_in_group[friend_list.options[i].value] = 1;
			}
		}
		if (send_invites_to != "") {
			send_invites_to = send_invites_to.substr(0, send_invites_to.length-1);
		}
		
		//friend_list.options.length = 0;
		if (friend_list.options.length) {
			for (var i=friend_list.options.length-1; i>=0; i--) {
				if (friend_list.options[i].selected) {
					friend_list.remove(i);
					//to_list.options.length--;
				}
			}
		}
	}
	return send_invites_to;
	
}

function selectAllEmailToSend() {
	/*
	var friend_list = $("madness-email-invite-list");
	var send_invites_to = "";
	for (i=0; i<friend_list.options.length; i++) {
		//friend_list.options[i].selected = true;
		send_invites_to += friend_list.options[i].value + ";"
		already_in_group[friend_list.options[i].value] = 1;
	}
	if (send_invites_to != "") {
		send_invites_to = send_invites_to.substr(0, send_invites_to.length-1);
	}
	
	friend_list.options.length = 0;
	
	return send_invites_to;
	*/
	
	var friend_list = $("madness-email-invite-list");
	var send_invites_to = "";
	//for (i=0; i<friend_list.options.length; i++) {
		//friend_list.options[i].selected = true;
	var emails = friend_list.value.split(",");
	
	for (var i=0; i<emails.length; i++) {
		var trim_email = emails[i].replace(/^\s*/, "").replace(/\s*$/, "");
		if (trim_email != "") {
			send_invites_to += trim_email + ";"
			already_in_group[trim_email] = 1;
		}
	}
	//}
	if (send_invites_to != "") {
		send_invites_to = send_invites_to.substr(0, send_invites_to.length-1);
	}
	
	friend_list.value = "";
	
	return send_invites_to;
}

function chooseEmailFromTb() {
	var friend_email = $("email-entry-tb").value;
	
	if (friend_email != "") {
		var selectedfriendval = friend_email;
		var selectedfriendtext = friend_email;
	}
	
	var to_list = $("madness-email-invite-list");
	
	if (selectedfriendval) {
		var can_add = true;
		for (i=0; i<to_list.options.length; i++) {
			if (to_list.options[i].value == selectedfriendval) {
				can_add = false;
			}
		}
		if (can_add) {
			var newOpt = new Option(selectedfriendtext, selectedfriendval);
			
			to_list.options.add(newOpt);
		}
	}
	
	$("email-entry-tb").value = "";
	
}

function removeUserNames() {
	var to_list = $("madness-invite-list");
	if (to_list.options.length) {
		for (var i=to_list.options.length-1; i>=0; i--) {
			if (to_list.options[i].selected) {
				to_list.remove(i);
				//to_list.options.length--;
			}
		}
	}
}

function removeEmails() {
	var to_list = $("madness-email-invite-list");
	if (to_list.options.length) {
		for (var i=to_list.options.length-1; i>=0; i--) {
			if (to_list.options[i].selected) {
				to_list.remove(i);
				//to_list.options.length--;
			}
		}
	}
}

function sendInviteEmails() {
	var users = escape(selectAllToSend());
	var emails = escape(selectAllEmailToSend());
	
	//alert(users);
	//alert(emails);
	
	
	var postBody = 'user_names=' + users + '&email_addresses=' + emails + '&group_id=' + __group_id + '&group_name=' + __group_name;
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfSendMadnessInvite';
	var callback =
	{
	  success: function(t) {
		$("madness-invite-return").innerHTML = t.responseText;
	  },
	  failure: function(t) {
		$("madness-invite-return").innerHTML = t.responseText;
	  }
	  
	}
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postBody);
	
}

function join_group() {
	if ($('madness-join-password')) {
		if ($('madness-join-password').value == "") {
			$('madness-group-join-errors').innerHTML = "This is a private league, you must enter a password to join.";
			new YAHOO.widget.Effects.Show('madness-group-join-errors');
			return "";
		
		}
		else {
			var needs_password = 1;
			var password = escape($('madness-join-password').value);
		}
	}
	else {
		var needs_password=0;
		var password = '';
	}
	
	if ($('madness-create-entry').value == "") {
			$('madness-group-join-errors').innerHTML = "You must specify an entry name for your entry in this group (you can change it later).";
			new YAHOO.widget.Effects.Show('madness-group-join-errors');
			return "";
	}
	else {
		var entry_name = escape($('madness-create-entry').value);
	}
	
	var group_id = $('madness-group-join-id').value;
	var tournament_id = $('madness-group-join-tournament-id').value;
	
	var postBody = 'needs_password=' + needs_password + '&password=' + password + '&entry_name=' + entry_name + '&group_id=' + group_id + '&tournament_id=' + tournament_id;
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfJoinMadnessGroup';
	var callback =
	{
	  success: function(t) {
		  var response_string = t.responseText.replace(/^\s*/, "").replace(/\s*$/, "");
		  if (response_string == "ok") {
			  location.href= $('madness-group-redirect').value + "/" + group_id;
			  //$("madness-group-join-container").innerHTML = t.responseText;
		  }
		  else {
			  $("madness-group-join-errors").innerHTML = t.responseText;
			new YAHOO.widget.Effects.Show('madness-group-join-errors');
		  }
	  },
	  failure: function(t) {
		$("madness-group-join-container").innerHTML = t.responseText;
	  }
	  
	}
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postBody);

}

function submit_winner(bracket, team_1, team_2, round, game_id, tournament_id, next_game, bracket_id, finals_bracket_id) {
	
	if($(bracket + '_team_' + team_1).value == "" || $(bracket + '_team_' + team_2).value == "") {
		alert("Please enter a value for both teams");
		return "";
	}
	
	
	//alert(bracket);
	//alert('team_name_' + bracket + '_' + team_1);
	var team_1_name = $('team_name_' + bracket + '_' + team_1).innerHTML;
	var team_2_name = $('team_name_' + bracket + '_' + team_2).innerHTML;
	var team_1_score = parseInt($(bracket + '_team_' + team_1).value);
	var team_2_score = parseInt($(bracket + '_team_' + team_2).value);
	
	if (team_1_score == team_2_score) {
		alert("There can not be a tie. Please adjust the score");
		return "";
	}
	
	
	var winner = (team_1_score>team_2_score ? escape(team_1_name) : escape(team_2_name));
	var loser = (team_1_score>team_2_score ? escape(team_2_name) : escape(team_1_name));
	var winner_score = (team_1_score>team_2_score ? team_1_score : team_2_score);
	var loser_score = (team_1_score>team_2_score ? team_2_score : team_1_score);
	
	if (finals_bracket_id != bracket_id) {
		var winner_id = (team_1_score>team_2_score ? bracket + '_' + team_1 : bracket + '_' + team_2);
		var loser_id = (team_1_score>team_2_score ? bracket + '_' + team_2 : bracket + '_' + team_1);
		var which_game = escape(bracket) + "_" + game_id;
		
		var team_1_id = escape(bracket + '_' + team_1);
		var team_2_id = escape(bracket + '_' + team_2);
	}
	else {
		var winner_id = (team_1_score>team_2_score ? team_1 : team_2);
		var loser_id = (team_1_score>team_2_score ? team_2 : team_1);
		var which_game = escape(bracket) + "_" + game_id;
		
		var team_1_id = escape(team_1);
		var team_2_id = escape(team_2);

	}
	bracket_old = bracket;
	bracket = escape(bracket);
	next_game = escape(next_game);
	
	//alert(winner + ' ' + winner_score + ' vs ' + loser + ' ' + loser_score + '\n' + 'Round: ' + round + ' Game: ' + which_game + ' Tournament: ' + tournament_id + ' NextGame: ' + next_game);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfUpdateGameWinner&rsargs[]='+tournament_id+'&rsargs[]='+bracket+'&rsargs[]='+round+'&rsargs[]='+which_game+'&rsargs[]='+winner_id+'&rsargs[]='+winner+'&rsargs[]='+winner_score+'&rsargs[]='+loser_id+'&rsargs[]='+loser+'&rsargs[]='+loser_score+'&rsargs[]='+next_game+'&rsargs[]='+team_1_id+'&rsargs[]='+team_2_id+'&rsargs[]='+bracket_id+'&rsargs[]='+finals_bracket_id;
	
	var callback =
	{
	  success: function(t) {
		  var response_string = t.responseText.replace(/^\s*/, "").replace(/\s*$/, "");
		  if (response_string == "ok") {
			  $(bracket_old + "_winner_" + game_id ).innerHTML = winner + ' ' + winner_score + ' vs ' + loser + ' ' + loser_score + '<br/>' + 'Round: ' + round + ' Game: ' + which_game + ' Tournament: ' + tournament_id + ' NextGame: ' + next_game;
		  }
		  else {
			  $(bracket_old + "_winner_" + game_id ).innerHTML = t.responseText;
		  }
	  },
	  failure: function(o) {
		  $(bracket_old + "_winner_" + game_id ).innerHTML = o.responseText;
	  }
	  //failure: function(o) {/*failure handler code*/},
	  //argument: [argument1, argument2, argument3]
	}
	
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
	
}

function enableThis(bracket_lower, game, team_1, team_2) {
	$(bracket_lower + "_team_" + team_1).disabled=false;
	$(bracket_lower + "_team_" + team_2).disabled=false;
	//$("submit_button_" +bracket_lower + "_" + game).disabled=false;
	new YAHOO.widget.Effects.Show("submit_button_" +bracket_lower + "_" + game);
	$("enable_button_" +bracket_lower + "_" + game).innerHTML="";
	
}

function swapClass(which, on, next, bracket) {
	if (on) {
		var team = $(bracket+"_team_"+next).getAttribute("team");
		var team_name = $(which).innerHTML;
		if (team == "" || team_name=="&nbsp;") {
			//$(which).className='pick-button-on';
			$D.addClass(which, 'pick-button-on');
		}
		else {
			//alert(team);
		}
	}
	else {
		//$(which).className='pick-button-off';
		$D.removeClass(which, 'pick-button-on');
	}
	
}

function update_scoring_levels(group_id, tournament_id) {
	var count = $('num-scoring-levels').value;
	var level_string = "";
	for (var i=1; i<count; i++) {
		level_string += $('scoring-level-' + i).value + ",";
	}
	level_string += $('scoring-level-' + count).value;
	
	level_string = escape(level_string);
	//alert(level_string + " group: " + group_id);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfUpdateGroupScoring&rsargs[]='+level_string+'&rsargs[]='+group_id+'&rsargs[]='+tournament_id;
	
	var callback =
	{
	  success: function(t) {
		  $('madness-group-points-errors').innerHTML = t.responseText;
		  new YAHOO.widget.Effects.Show('madness-group-points-errors');
	  },
	  failure: function(o) {
		  $('madness-group-points-errors').innerHTML = o.responseText;
		  new YAHOO.widget.Effects.Show('madness-group-points-errors');
	  }
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
}

function update_entry_name(entry_id) {
	
	var temp = $('entry_name').value.replace(/^\s*/, "").replace(/\s*$/, "");
	if (temp == "") {
			$('madness-group-update-errors').innerHTML = "You must specify an entry name for your entry in this group.";
			new YAHOO.widget.Effects.Show('madness-group-update-errors');
			return "";
	}
	
	
	if (temp.length > 50) {
		$('madness-group-update-errors').innerHTML = "Your entry name is too long (" + temp.length + "). The maximum length is 50 characters.";
		new YAHOO.widget.Effects.Show('madness-group-update-errors');
		return "";
	}
	
	var entry_name = escape(temp);
	
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfUpdateEntryInfo&rsargs[]='+entry_name+'&rsargs[]='+entry_id
	
	var callback =
	{
	  success: function(t) {
		  $('madness-group-update-errors').innerHTML = t.responseText;
		  new YAHOO.widget.Effects.Show('madness-group-update-errors');
	  }
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
}


function update_group_name(group_id, entry_id) {
	
	var temp = $('group_name').value.replace(/^\s*/, "").replace(/\s*$/, "");
	if (temp == "") {
			$('madness-group-update-errors').innerHTML = "You must specify an group name for this group.";
			new YAHOO.widget.Effects.Show('madness-group-update-errors');
			return "";
	}
	
	
	if (temp.length > 50) {
		$('madness-group-update-errors').innerHTML = "Your group name is too long (" + temp.length + "). The maximum length is 50 characters.";
		new YAHOO.widget.Effects.Show('madness-group-update-errors');
		return "";
	}
	
	var group_name = temp;
	
	var temp = $('group_desc').value.replace(/^\s*/, "").replace(/\s*$/, "");
	
	
	if (temp.length > 255) {
		$('madness-group-update-errors').innerHTML = "Your group description is too long (" + temp.length + "). The maximum length is 255 characters.";
		new YAHOO.widget.Effects.Show('madness-group-update-errors');
		return "";
	}
	
	var group_desc = temp;
	
	var postBody = 'group_id=' + group_id + '&group_name=' + group_name + '&group_desc=' + group_desc + '&entry_id=' + entry_id;
	var sUrl = 'index.php?title=index.php&action=ajax&rs=wfUpdateGroupInfo';
	
	var callback =
	{
	  success: function(t) {
		  $('madness-group-update-errors').innerHTML = t.responseText;
		  new YAHOO.widget.Effects.Show('madness-group-update-errors');
	  },
	  failure: function(t) {
		  $('madness-group-update-errors').innerHTML = t.responseText;
		  new YAHOO.widget.Effects.Show('madness-group-update-errors');
	  }
	  
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postBody);
}

function set_tiebreak(entry_id) {
	
	if($("pick-tiebreaker")) {
		var temp = $('pick-tiebreaker').value.replace(/^\s*/, "").replace(/\s*$/, "");
		
		if (temp == "") {
			$('madness-messages').innerHTML = "Please enter something for the tiebreaker";
			return "";
		}
		else {
			var tiebreaker = temp;
			var sUrl = 'index.php?title=index.php&action=ajax&rs=wfSetTiebreaker&rsargs[]='+entry_id+'&rsargs[]='+tiebreaker
	
			var callback =
			{
			  success: function(t) {
				  $('madness-messages').innerHTML = t.responseText;
			  }
			}
			
			
			var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
		}
		
	}
}

function set_start_time(tournament_id) {
	
	if($("tournament-start-date")) {
		var temp = $('tournament-start-date').value.replace(/^\s*/, "").replace(/\s*$/, "");
		
		if (temp == "") {
			$('madness-enterwinners-messages').innerHTML = "Please enter something for the startdate";
			return "";
		}
		else {
			var start_date = temp;
			var sUrl = 'index.php?title=index.php&action=ajax&rs=wfSetStartDate&rsargs[]='+tournament_id+'&rsargs[]='+start_date
	
			var callback =
			{
			  success: function(t) {
				  $('madness-enterwinners-messages').innerHTML = t.responseText;
			  }
			}
			
			
			var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
		}
		
	}
}