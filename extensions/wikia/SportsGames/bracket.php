
<?php
function load_picks() {
	
	$num_teams = 16;
	$total = $num_teams+1;
	$rounds = 0;
	$temp = $num_teams;
	while($temp >= 1) {
		$temp = $temp/2;
		$rounds++;
	}
	
	$script_output = "<script type=\"text/javascript\">var num_teams = {$num_teams};</script>";
	
	
	$games_order = array(1,8,5,4,6,3,7,2);
	$bracket = "east";
	$teams = array(
			1 => "team1",
			2 => "team2",
			3 => "team3",
			4 => "team4",
			5 => "team5",
			6 => "team6",
			7 => "team7",
			8 => "team8",
			9 => "team9",
			10 => "team10",
			11 => "team11",
			12 => "team12",
			13 => "team13",
			14 => "team14",
			15 => "team15",
			16 => "team16",
		);
		
	$picks = array(
	);
	
	//$output = "rounds: " . $rounds . "<br/>";
	$g = 1;
	$p = 0;
	$t = 1;
	$output = "<div id='complete-check'></div>";
	for($r=1; $r<=$rounds; $r++) {
		if ($r == $rounds) {
			$top = ((pow(2,$r-2)-1)*29) + 13;
		}
		else {
			$top = ((pow(2,$r-1)-1)*29);
		}
		$output .= "<div id='round_{$r}' style='float:left; position:relative; top:" . $top . "px;'>";
		
		for ($i=0; $i<$num_teams/(pow(2,$r)); $i++) { 
		
			if ($r==1) {
				$which_array = $teams;
				$team_1 = $games_order[$i];
				$team_2 = $total-$team_1;
				$team_1_code = $team_1;
				$team_2_code = $team_2;
				
			}
			else {
				$which_array = $picks;
				$team_1 = $p++;
				$team_2 = $p++;
				$team_1_code = "";
				$team_2_code = "";
			}
			
			//$next = $g + ($num_teams/(pow(2,$r-1)));
			$next = $g + $num_teams;
			
			$game = "{$bracket}_{$g}";
			$team_1_spot = "{$bracket}_team_" . $t++;
			$team_2_spot = "{$bracket}_team_" . $t++;
			$team_1_name = (isset($which_array[$team_1]) ? $which_array[$team_1]:"&nbsp;");
			$team_2_name = (isset($which_array[$team_2]) ? $which_array[$team_2] : "&nbsp;");
			
			if ($r < $rounds) {
				$output .= "<div id='" . $game . "' class='game' style='margin-bottom: " . ((pow(2,$r-1)-1)*60) . "';><div class='topteam' team='{$team_1_code}' id='{$team_1_spot}' onclick='make_pick(\"{$bracket}\", {$g}, \"{$team_1_spot}\", {$next}, \"{$team_2_spot}\", {$num_teams});'>" . $team_1_name . "</div><div class='bottomteam' team='{$team_2_code}' id='{$team_2_spot}' onclick='make_pick(\"{$bracket}\", {$g}, \"{$team_2_spot}\", {$next}, \"{$team_1_spot}\", {$num_teams});'>" . $team_2_name . "</div></div>";
			}
			else {
				$output .= "<div id='" . $game . "' class='game'><div team='' id='{$team_1_spot}' class='champ' >" . $team_1_name . "</div></div>";
			}
			$g++;
		}
			
		
			
		
		$output .= "</div>";
	}
	
	
	return $output;
}

echo load_picks();
?>

<html>
<head>

<script type="text/javascript">

function make_pick(bracket, game, team, dest, opp, num_teams) {
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
		document.getElementById("complete-check").innerHTML = "Completed";
	}
	else {
		document.getElementById("complete-check").innerHTML = "";
	}
	
}
</script>

<style type="text/css">



.game {
	position:relative; 
	top: 25px;
	left:25px;
	border: solid 1px black;
	margin: 2px;
	padding: 2px;
}

.topteam {
	border: solid 1px blue;
	margin: 1px;
	padding: 1px;
	width: 125px;
	cursor: pointer;
}

.bottomteam {
	border: solid 1px red;
	margin: 1px;
	padding: 1px;
	width: 125px;
	cursor: pointer;
}

.champ {
	border: solid 1px green;
	margin: 1px;
	padding: 1px;
	width: 125px;
	cursor: pointer;
}
</style>

</head>
<body>

	

</body>
</html>
