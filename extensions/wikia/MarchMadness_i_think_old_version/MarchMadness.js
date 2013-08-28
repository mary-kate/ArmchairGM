function make_pick(bracket, game, team, dest, opp, num_teams) {
	//bracket = bracket.toLowerCase();
	//var is_finals = document.getElementById("is_finals").value;
	
	document.getElementById("any_changes").value = '1';
	
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
		if ((document.getElementById(bracket+"_team_"+dest).innerHTML == document.getElementById(opp).innerHTML) && (document.getElementById(bracket+"_team_"+dest).innerHTML != "&nbsp;")) {
			document.getElementById(bracket+"_team_"+dest).innerHTML = "&nbsp;";
			//var changeStr = document.getElementById(bracket+"_team_"+dest).getAttribute('team'); 
			document.getElementById(bracket+"_team_"+dest).setAttribute('team', '');
			//changeStr += ":" + document.getElementById(bracket+"_team_"+dest).getAttribute('team');
			//alert(changeStr);
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
		document.getElementById("complete-check").innerHTML = "<div id='submit-button' onclick='calculateResults(" + num_teams + ", \"" + bracket + "\" );'>Submit Picks</div>";
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
	document.getElementById("bracket-picks").submit();
	//alert(document.getElementById("bracket-picks-field").value);
	//alert(picks);
}

function calculateResultsAndSwitch(num_teams, bracket, which) {
	
	if (document.getElementById("any_changes").value == '1') {
		//alert("changes made, submitting to " + which);
		document.getElementById("bracket-picks").setAttribute("action", which);
		calculateResults(num_teams, bracket);
	}
	else {
		//alert("no changes made, just going to " + which);
		location.href = which;
	}
	
}

function create_group() {

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
	  //failure: function(o) {/*failure handler code*/},
	  //argument: [argument1, argument2, argument3]
	}
	
	
	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

}

