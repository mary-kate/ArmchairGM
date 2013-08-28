<?php

$wgExtensionFunctions[] = 'wfSpecialTopNetworks';

function wfSpecialTopNetworks() {
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class TopNetworks extends SpecialPage {

	
	function TopNetworks(){
		UnlistedSpecialPage::UnlistedSpecialPage("TopNetworks");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		
		
		//variables
		$output = "";
		$direction = $wgRequest->getVal('direction');
		$sport = $wgRequest->getVal('sport');
	
	
		//direction
		if ($direction == "worst") {
			$order = "ASC";
			$adj = "Least";
		} else {
			$order = "DESC";
			$adj = "Most";
		}
		
		//sport
		
		if ($sport) {
			$sport_where = "WHERE sport_team.team_sport_id={$sport}";
		}
		
		//set title
		$wgOut->setPagetitle( "{$adj} Popular 	Sports Fan Networks" );
		
		//css
		$output .= "<style>
		
			.network-row {
				margin:18px 0px;
			}
		
			.network-number {
				float:left;
				font-size:18px;
				color:#dcdcdc;
				font-weight:bold;
				width:60px;
				padding:5px 0px 0px 0px;
			}
			
			.network-team {
				float:left;
				font-size:14px;
				font-weight:bold;
				margin:0px 10px 0px 0px;
				width:270px;
			}
			
			.network-team img {
				vertical-align:middle;
				width:30px;
				margin:0px 3px 0px 0px;
			}
			
			.network-team a {
				text-decoration:none;
			}
			
			.network-count {
				float:left;
				font-size:14px;
				color:#797979;
				padding:5px 0px 0px 0px;
			}
			
			.top-networks-navigation {
				float:right;
				width:300px;
			}
			
			.top-networks-navigation a {
				text-decoration:none;
				font-weight:bold;
			}
			
			.top-networks-navigation h1 {
				color:#333333;
				padding:0px 0px 3px 0px;
				border-bottom:1px solid #dcdcdc;
				font-size:16px;
				font-weight:bold;
				margin:0px 0px 10px 0px !important;
			}
			
			.top-networks-navigation h2 {
				font-size:14px;
				font-weight:bold;
				margin:0px 0px 5px 0px !important;
			}
			
			.top-networks {
				float:left;
				width:600px;
			}
			
		</style>";
		
		//database
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT COUNT(sport_favorite.sf_team_id) as network_user_count, sport_favorite.sf_team_id, sport_team.team_name, sport_team.team_sport_id FROM sport_favorite INNER JOIN sport_team ON sport_favorite.sf_team_id=sport_team.team_id {$sport_where} GROUP BY sport_team.team_id ORDER BY network_user_count {$order} LIMIT 0,50";
		$res = $dbr->query($sql);
		
		$sql_sport = "SELECT sport_id, sport_name FROM sport";
		$res_sport = $dbr->query($sql_sport);
		
		
		//navigation
		$output .= "<div class=\"top-networks-navigation\">
			<h1>Overall</h1>";
			
			if (!($sport)  && ($direction=="best")) {
				$output .= "<p><b>Most Popular</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction=best\">Most Popular</a></p>";
			}
			
			if (!($sport)  && ($direction=="worst")) {	
				$output .= "<p><b>Least Popular</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction=worst\">Least Popular</a></p>";
			}
			
			$output .= "<h1 style=\"margin-top:15px !important;\">{$adj} Popular By Sport</h1>";	
			
			while ($row_sport = $dbr->fetchObject( $res_sport ) ) {
				
				$sport_id = $row_sport->sport_id;
				$sport_name = $row_sport->sport_name;
				
				if ($sport_id == $sport) {
					$output .= "<p><b>{$sport_name}</b></p>";
					$wgOut->setPagetitle( "{$adj} Popular {$sport_name} Fan Networks" );
				} else {
					$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction={$direction}&sport={$sport_id}\">{$sport_name}</a></p>";
				}
				
			}
			
		$output .= "</div>";
		
		
		//List Networks
		$output .= "<div class=\"top-networks\">";
		
		//set counter
		$x = 1;

		while ($row = $dbr->fetchObject( $res ) ) {
			
			//more variables
			$user_count = $row->network_user_count;
			$team = $row->team_name;
			$team_id = $row->sf_team_id;
			$sport_id = $row->team_sport_id;

			//get team logo
			$team_image = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($team_id,"l") . "\" border=\"0\" alt=\"logo\" />";
			

			$output .= "<div class=\"network-row\">
				<span class=\"network-number\">{$x}.</span>
				<span class=\"network-team\">
					{$team_image} 
					<a href=\"index.php?title=Special:FanHome&sport_id={$sport_id}&team_id={$team_id}\">{$team}</a>
				</span>
				<span class=\"network-count\"><b>{$user_count}</b> ".(( $user_count > 1) ? "fans":"fan")."</span>
				<div class=\"cleared\"></div>
			</div>";
			$x++;
		}
		
		$output .= "</div>
		<div class=\"cleared\"></div>";
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new TopNetworks );

 


}

?>