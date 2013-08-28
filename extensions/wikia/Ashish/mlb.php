<?php

function clearHTML($t){
	
	$t = str_replace("<tr bgcolor=#CCCCCC>", "", $t);
	$t = str_replace("<tr>", "", $t);
	
	$t = str_replace("<font size=2>", "", $t);
	$t = str_replace("</font>", "", $t);
	
	$t = str_replace('<td align="center">', "", $t);
	$t = str_replace("<td>", "", $t);

	return $t;	
}

// american league data
$ALData = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>ESPN.com -- MLB</title><link rel="stylesheet" href="http://static.mobile.espn.go.com/wireless/css/main10.css" type="text/css" /><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><div id="top"><a href="http://proxy.espn.go.com/espn/wireless/html/pocketpc"><img src="http://static.mobile.espn.go.com/i/wireless/pocket/bg_wireless_mlb.gif" height="40" width="240" alt="ESPN" border="0" /></a><div id="sportnav"><ul><li><a href="http://mobileapp.espn.go.com/mlb/mp/html/index?dvc=1">MLB</a></li><li><a href="http://mobileapp.espn.go.com/nfl/mp/html/index?dvc=1">NFL</a></li><li><a href="http://mobileapp.espn.go.com/nba/mp/html/index?dvc=1">NBA</a></li><li><a href="http://mobileapp.espn.go.com/nhl/mp/html/index?dvc=1">NHL</a></li><li><a href="http://mobileapp.espn.go.com/ncf/mp/html/index?dvc=1">College FB</a></li><li><a href="http://mobileapp.espn.go.com/ncb/mp/html/index?dvc=1">Mens College BB</a></li><li><a href="http://mobileapp.espn.go.com/golf/mp/html/index?dvc=1">Golf</a></li><li><a href="http://mobileapp.espn.go.com/rpm/mp/html/index?dvc=1">RPM</a></li><li><a href="http://mobileapp.espn.go.com/ncw/mp/html/index?dvc=1">Womens College BB</a></li><li><a href="http://mobileapp.espn.go.com/general/mp/html/dontmiss?dvc=1">Dont Miss</a></li></ul></div></div><div id="topsections" class="gutter"><strong>MLB: </strong><a href="news?dvc=1">News</a> | <a href="scoreboard?dvc=1">Scores</a> | <a href="standings?dvc=1">Standings</a> | <a href="statsIndex?dvc=1">Stats</a></div><p align="center"></p><font size=4><b>Standings</b></font><br><font size=1>American League | <a href="standings?div=nl&dvc=1">National League</a></font><hr><table width="229"><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>EAST</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>Boston</td><td align="center"><font size=2>36</td><td align="center"><font size=2>15</td><td align="center"><font size=2>.706</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Baltimore</td><td align="center"><font size=2>25</td><td align="center"><font size=2>27</td><td align="center"><font size=2>.481</td><td align="center"><font size=2>11.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Toronto</td><td align="center"><font size=2>24</td><td align="center"><font size=2>27</td><td align="center"><font size=2>.471</td><td align="center"><font size=2>12</td></tr><tr><td><font size=2>Tampa Bay</td><td align="center"><font size=2>21</td><td align="center"><font size=2>29</td><td align="center"><font size=2>.420</td><td align="center"><font size=2>14.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>NY Yankees</td><td align="center"><font size=2>21</td><td align="center"><font size=2>29</td><td align="center"><font size=2>.420</td><td align="center"><font size=2>14.5</td></tr><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>CENTRAL</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>Cleveland</td><td align="center"><font size=2>31</td><td align="center"><font size=2>19</td><td align="center"><font size=2>.620</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Detroit</td><td align="center"><font size=2>30</td><td align="center"><font size=2>21</td><td align="center"><font size=2>.588</td><td align="center"><font size=2>1.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Minnesota</td><td align="center"><font size=2>27</td><td align="center"><font size=2>25</td><td align="center"><font size=2>.519</td><td align="center"><font size=2>5</td></tr><tr><td><font size=2>Chicago WS</td><td align="center"><font size=2>24</td><td align="center"><font size=2>24</td><td align="center"><font size=2>.500</td><td align="center"><font size=2>6</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Kansas City</td><td align="center"><font size=2>19</td><td align="center"><font size=2>34</td><td align="center"><font size=2>.358</td><td align="center"><font size=2>13.5</td></tr><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>WEST</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>Los Angeles</td><td align="center"><font size=2>32</td><td align="center"><font size=2>21</td><td align="center"><font size=2>.604</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Seattle</td><td align="center"><font size=2>25</td><td align="center"><font size=2>23</td><td align="center"><font size=2>.521</td><td align="center"><font size=2>4.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Oakland</td><td align="center"><font size=2>25</td><td align="center"><font size=2>26</td><td align="center"><font size=2>.490</td><td align="center"><font size=2>6</td></tr><tr><td><font size=2>Texas</td><td align="center"><font size=2>19</td><td align="center"><font size=2>33</td><td align="center"><font size=2>.365</td><td align="center"><font size=2>12.5</td></tr></table><br>&nbsp;</body></html>';

$ALTeamURLs["Boston"] = "http://www.armchairgm.com/index.php?title=Red_Sox";
$ALTeamURLs["Baltimore"] = "http://www.armchairgm.com/index.php?title=Orioles";
$ALTeamURLs["Toronto"] = "http://www.armchairgm.com/index.php?title=Blue_Jays";
$ALTeamURLs["Tampa Bay"] = "http://www.armchairgm.com/index.php?title=Devil_Rays";
$ALTeamURLs["NY Yankees"] = "http://www.armchairgm.com/index.php?title=Yankees";
$ALTeamURLs["Cleveland"] = "http://www.armchairgm.com/index.php?title=Indians";
$ALTeamURLs["Detroit"] = "http://www.armchairgm.com/index.php?title=Detroit_Tigers";
$ALTeamURLs["Minnesota"] = "http://www.armchairgm.com/index.php?title=Minnesota_Twins";
$ALTeamURLs["Chicago WS"] = "http://www.armchairgm.com/index.php?title=White_Sox";
$ALTeamURLs["Kansas City"] = "http://www.armchairgm.com/index.php?title=Kansas_City_Royals";
$ALTeamURLs["Los Angeles"] = "http://www.armchairgm.com/index.php?title=Dodgers";
$ALTeamURLs["Seattle"] = "http://www.armchairgm.com/index.php?title=Seattle_Mariners";
$ALTeamURLs["Oakland"] = "http://www.armchairgm.com/index.php?title=Oakland_Athletics";
$ALTeamURLs["Texas"] = "http://www.armchairgm.com/index.php?title=Texas_Rangers";

// national league data
$NLdata = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>ESPN.com -- MLB</title><link rel="stylesheet" href="http://static.mobile.espn.go.com/wireless/css/main10.css" type="text/css" /><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><div id="top"><a href="http://proxy.espn.go.com/espn/wireless/html/pocketpc"><img src="http://static.mobile.espn.go.com/i/wireless/pocket/bg_wireless_mlb.gif" height="40" width="240" alt="ESPN" border="0" /></a><div id="sportnav"><ul><li><a href="http://mobileapp.espn.go.com/mlb/mp/html/index?dvc=1">MLB</a></li><li><a href="http://mobileapp.espn.go.com/nfl/mp/html/index?dvc=1">NFL</a></li><li><a href="http://mobileapp.espn.go.com/nba/mp/html/index?dvc=1">NBA</a></li><li><a href="http://mobileapp.espn.go.com/nhl/mp/html/index?dvc=1">NHL</a></li><li><a href="http://mobileapp.espn.go.com/ncf/mp/html/index?dvc=1">College FB</a></li><li><a href="http://mobileapp.espn.go.com/ncb/mp/html/index?dvc=1">Mens College BB</a></li><li><a href="http://mobileapp.espn.go.com/golf/mp/html/index?dvc=1">Golf</a></li><li><a href="http://mobileapp.espn.go.com/rpm/mp/html/index?dvc=1">RPM</a></li><li><a href="http://mobileapp.espn.go.com/ncw/mp/html/index?dvc=1">Womens College BB</a></li><li><a href="http://mobileapp.espn.go.com/general/mp/html/dontmiss?dvc=1">Dont Miss</a></li></ul></div></div><div id="topsections" class="gutter"><strong>MLB: </strong><a href="news?dvc=1">News</a> | <a href="scoreboard?dvc=1">Scores</a> | <a href="standings?dvc=1">Standings</a> | <a href="statsIndex?dvc=1">Stats</a></div><p align="center"></p><font size=4><b>Standings</b></font><br><font size=1><a href="standings?div=al&dvc=1">American League</a> | National League</font><hr><table width="229"><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>EAST</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>NY Mets</td><td align="center"><font size=2>33</td><td align="center"><font size=2>18</td><td align="center"><font size=2>.647</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Atlanta</td><td align="center"><font size=2>30</td><td align="center"><font size=2>23</td><td align="center"><font size=2>.566</td><td align="center"><font size=2>4</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Florida</td><td align="center"><font size=2>26</td><td align="center"><font size=2>27</td><td align="center"><font size=2>.491</td><td align="center"><font size=2>8</td></tr><tr><td><font size=2>Philadelphia</td><td align="center"><font size=2>26</td><td align="center"><font size=2>27</td><td align="center"><font size=2>.491</td><td align="center"><font size=2>8</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Washington</td><td align="center"><font size=2>21</td><td align="center"><font size=2>32</td><td align="center"><font size=2>.396</td><td align="center"><font size=2>13</td></tr><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>CENTRAL</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>Milwaukee</td><td align="center"><font size=2>29</td><td align="center"><font size=2>24</td><td align="center"><font size=2>.547</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Pittsburgh</td><td align="center"><font size=2>23</td><td align="center"><font size=2>29</td><td align="center"><font size=2>.442</td><td align="center"><font size=2>5.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Chicago Cubs</td><td align="center"><font size=2>22</td><td align="center"><font size=2>29</td><td align="center"><font size=2>.431</td><td align="center"><font size=2>6</td></tr><tr><td><font size=2>St. Louis</td><td align="center"><font size=2>20</td><td align="center"><font size=2>29</td><td align="center"><font size=2>.408</td><td align="center"><font size=2>7</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Houston</td><td align="center"><font size=2>21</td><td align="center"><font size=2>31</td><td align="center"><font size=2>.404</td><td align="center"><font size=2>7.5</td></tr><tr><td><font size=2>Cincinnati</td><td align="center"><font size=2>21</td><td align="center"><font size=2>33</td><td align="center"><font size=2>.389</td><td align="center"><font size=2>8.5</td></tr><tr bgcolor="#002175"><td><font size=1 color=#FFFFFF><b>WEST</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>W</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>L</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>PCT</b></font></td><td align="center"><font size=1 color=#FFFFFF><b>GB</b></font></td></tr><tr bgcolor=#CCCCCC><td><font size=2>Los Angeles</td><td align="center"><font size=2>31</td><td align="center"><font size=2>21</td><td align="center"><font size=2>.596</td><td align="center"><font size=2> - </td></tr><tr><td><font size=2>Arizona</td><td align="center"><font size=2>32</td><td align="center"><font size=2>23</td><td align="center"><font size=2>.582</td><td align="center"><font size=2>.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>San Diego</td><td align="center"><font size=2>30</td><td align="center"><font size=2>22</td><td align="center"><font size=2>.577</td><td align="center"><font size=2>1</td></tr><tr><td><font size=2>San Francisco</td><td align="center"><font size=2>25</td><td align="center"><font size=2>26</td><td align="center"><font size=2>.490</td><td align="center"><font size=2>5.5</td></tr><tr bgcolor=#CCCCCC><td><font size=2>Colorado</td><td align="center"><font size=2>25</td><td align="center"><font size=2>27</td><td align="center"><font size=2>.481</td><td align="center"><font size=2>6</td></tr></table>

<!-- BEGIN WEBSIDESTORY WIRELESS CODE v2.0 (wireless)-->
<!-- COPYRIGHT 1997-2003 WEBSIDESTORY, INC. ALL RIGHTS RESERVED. U.S.PATENT No. 6,393,479 B1. Privacy notice at: http://websidestory.com/privacy -->

<img src="http://ehg-dig.hitbox.com/HG?whbx=1&amp;hv=6&amp;ce=y&amp;hb=DM560603HBRV;DM5104129LMA95EN3;DM5010177GVA95EN3;DM5103083LCA38EN3&amp;cd=1&amp;n=standings&amp;vcon=/wireless/html/mlb/mp/html&amp;cv.c1=nullx-html|US;*;*;*&amp;cv.c3=nullx-html---Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3;*;*;*" alt=""/>

<!-- END WEBSIDESTORY CODE  -->

<br>&nbsp;</body></html>';



$ALTeams = parseHTML($ALData);

$divisions = array_keys($ALTeams);

?> 
<table width="30%" border="1" cellspacing="1"> 
<?php

foreach($divisions as $div){
?>

<tr> <th scope="col"><div align="left"> <?php print $div ?> </div></th> </tr>
  <tr>
    <th scope="col">Team</th>
    <th scope="col">Win</th>
    <th scope="col">Loss</th>
    <th scope="col">%</th>
    <th scope="col">GB</th>
</tr>

<?php
	foreach($ALTeams[$div] as $team){
		?>
  <tr>
    <td><a href="<?php print $ALTeamURLs[$team["name"]] ?>" target="_new"><?php print $team["name"] ?></a></td>
    <td><?php print $team["wins"] ?></td>
    <td><?php print $team["losses"] ?></td>
    <td><?php print $team["percent"] ?></td>
    <td><?php print $team["gamesBack"] ?></td>
  </tr>

<?php
	}

}

print "</table>";

function parseHTML($data){
	
	list($junk, $table) = explode("<table", $data);
	list($table, $junk) = explode("<!--", $table);
	
	$table = str_replace('width="229">', "", $table);
	$table = str_replace("<br>&nbsp;</body></html>", "", $table);
	$table = str_replace("</table>", "", $table);
	
	$trs = explode("</tr>", $table);
	
	$allTeams = null;
	$allTeams = array();
	
	foreach($trs as $c){
		
		$tds = explode("</td>", $c);
		
		$currTeam = array();
		$i = 0;
		
		foreach($tds as $t){
			
			$line = null;
			
			// $division, $name, $win, $loss, $winPercent, $gb
			if($i == 0 && strstr($t, "color=#FFFFFF")){
				$line = clearHTML($t);
				
				$line = str_replace('<b>', "", $line);
				$line = str_replace('</b>', "", $line);
				
				$line = str_replace('<font size=1 color=#FFFFFF>', "", $line);
				$line = str_replace('</font>', "", $line);
				
				$line = str_replace('<tr bgcolor="#002175">', "", $line);
				
				$currDivision = ucwords( strtolower( trim($line) ) );
				$allTeams[$currDivision] = array();
			}
			
			if(!strstr($t, "color=#FFFFFF")){
				$line = trim(clearHTML($t));
				
				switch($i){
					
				case 0:
					if(strlen($line) < 1)
						break;
					
					$currTeam["name"] = $line;
					$currTeam["division"] = $currDivision;
					break;
				case 1:
					$currTeam["wins"] = $line;
					break;
				case 2:
					$currTeam["losses"] = $line;
					break;
				case 3:
					$currTeam["percent"] = $line;
					break;
				case 4:
					$currTeam["gamesBack"] = $line;
					break;
					
				}
				
			}
			
			$i++;
		}
		
		if(sizeof($currTeam) > 0){
			array_push($allTeams[$currDivision], $currTeam);
		}
		
	}
	
	return $allTeams;
}

?>
