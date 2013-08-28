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
		$type=$wgRequest->getVal('type');
		$sport = $wgRequest->getVal('sport');
	
	
		//direction
		if ($direction == "worst") {
			$order = "ASC";
			$adj = wfMsgForContent( 'st_top_network_least' );
		} else {
			$order = "DESC";
			$adj = wfMsgForContent( 'st_top_network_most' );
		}
		
		//type 
		
		if ($type=="sport") {
			$type_title = wfMsgForContent( 'st_top_network_sports' );
		} else {
			$type_title = wfMsgForContent( 'st_top_network_teams' );
		}
		
		//sport
		
		if ($sport) {
			$sport_where = "WHERE sport_team.team_sport_id={$sport}";
		}
		
		//set title
		$wgOut->setPagetitle( wfMsgForContent( 'st_top_network_team_title', $adj, $type_title) );
		
		//css
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsTeams/SportsTeams.css?{$wgStyleVersion}\"/>\n");
		
		//database
		$dbr =& wfGetDB( DB_MASTER );
		
		//teams
		$sql = "SELECT COUNT(sport_favorite.sf_team_id) as network_user_count, sport_favorite.sf_team_id, sport_team.team_name, sport_team.team_sport_id FROM sport_favorite INNER JOIN sport_team ON sport_favorite.sf_team_id=sport_team.team_id {$sport_where} GROUP BY sport_team.team_id ORDER BY network_user_count {$order} LIMIT 0,50";
		$res = $dbr->query($sql);
		
		
		//sports
		$sql_sport = "SELECT COUNT(sf_sport_id) as sport_count, sf_sport_id, sport_name FROM sport_favorite INNER JOIN sport ON sf_sport_id=sport_id GROUP BY sf_sport_id ORDER BY sport_count {$order} LIMIT 0,50";
		$res_sport = $dbr->query($sql_sport);
		
		//navigation
		$sql_sport_nav = "SELECT sport_id, sport_name, team_sport_id FROM sport INNER JOIN sport_team ON sport_id=team_sport_id GROUP BY sport_name ORDER BY sport_id";
		$res_sport_nav = $dbr->query($sql_sport_nav);
		
		
		//navigation
		$output .= "<div class=\"top-networks-navigation\">
			<h1>" . wfMsgForContent( 'st_top_network_most_popular' ) . "</h1>";
			
			if (!($sport) && !($type) && !($direction)) {
				$output .= "<p><b>" . wfMsgForContent( 'st_top_network_teams' ) . "</b></p>";
			} else if (!($sport) && !($type) && ($direction=="best")) {
				$output .= "<p><b>" . wfMsgForContent( 'st_top_network_teams' ) . "</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction=best\">" . wfMsgForContent( 'st_top_network_teams' ) . "</a></p>";
			}
			
			if (!($sport) && ($type=="sport") && ($direction=="best")) {
				$output .= "<p><b>" . wfMsgForContent( 'st_top_network_sports' ) . "</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&type=sport&direction=best\">" . wfMsgForContent( 'st_top_network_sports' ) . "</a></p>";
			}
			
		$output .= "<h1 style=\"margin-top:15px !important;\">" . wfMsgForContent( 'st_top_network_least_popular' ) . "</h1>";
			
			if (!($sport) && !($type) && ($direction=="worst")) {
				$output .= "<p><b>" . wfMsgForContent( 'st_top_network_teams' ) . "</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction=worst\">" . wfMsgForContent( 'st_top_network_teams' ) . "</a></p>";
			}
			
			if (!($sport) && ($type=="sport") && ($direction=="worst")) {
				$output .= "<p><b>" . wfMsgForContent( 'st_top_network_sports' ) . "</b></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:TopNetworks&type=sport&direction=worst\">" . wfMsgForContent( 'st_top_network_sports' ) . "</a></p>";
			}
			
			
			$output .= "<h1 style=\"margin-top:15px !important;\">" . wfMsgForContent( 'st_top_network_pop_by_sport', $adj ) . "</h1>";	
			
			while ($row_sport_nav = $dbr->fetchObject( $res_sport_nav ) ) {
				
				$sport_id = $row_sport_nav->sport_id;
				$sport_name = $row_sport_nav->sport_name;
				
				if ($sport_id == $sport) {
					$output .= "<p><b>{$sport_name}</b></p>";
					$wgOut->setPagetitle( wfMsgForContent( 'st_top_network_team_title', $adj, $sport_name) );
				} else {
					$output .= "<p><a href=\"index.php?title=Special:TopNetworks&direction={$direction}&sport={$sport_id}\">{$sport_name}</a></p>";
				}
				
			}
			
		$output .= "</div>";
		
		
		//List Networks
		$output .= "<div class=\"top-networks\">";
		
		//set counter
		$x = 1;

		if ($type == "sport") {
			
			while ($row_sport = $dbr->fetchObject( $res_sport ) ) {

				//more variables
				$user_count = $row_sport->sport_count;
				$sport = $row_sport->sport_name;
				$sport_id = $row_sport->sf_sport_id;
	
				//get team logo
				$sport_image = "<img src=\"images/sport_logos/" . SportsTeams::getSportLogo($sport_id,"s") . "\" border=\"0\" alt=\"logo\" />";


				$output .= "<div class=\"network-row\">
					<span class=\"network-number\">{$x}.</span>
					<span class=\"network-team\">
						{$sport_image} 
						<a href=\"index.php?title=Special:FanHome&sport_id={$sport_id}\">{$sport}</a>
					</span>
					<span class=\"network-count\"><b>{$user_count}</b> ".(( $user_count > 1) ? wfMsgForContent( 'st_network_fans' ):wfMsgForContent( 'st_network_fan' ))."</span>
					<div class=\"cleared\"></div>
				</div>";
				$x++;
			}
			
		} else {
		
			while ($row = $dbr->fetchObject( $res ) ) {

				//more variables
				$user_count = $row->network_user_count;
				$team = $row->team_name;
				$team_id = $row->sf_team_id;
				$sport_id = $row->team_sport_id;

				//get team logo
				$team_image = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($team_id,"s") . "\" border=\"0\" alt=\"logo\" />";


				$output .= "<div class=\"network-row\">
					<span class=\"network-number\">{$x}.</span>
					<span class=\"network-team\">
						{$team_image} 
						<a href=\"index.php?title=Special:FanHome&sport_id={$sport_id}&team_id={$team_id}\">{$team}</a>
					</span>
					<span class=\"network-count\"><b>{$user_count}</b> ".(( $user_count > 1) ? wfMsgForContent( 'st_network_fans' ):wfMsgForContent( 'st_network_fan' ))."</span>
					<div class=\"cleared\"></div>
				</div>";
				$x++;
			}
		}

		
		
		$output .= "</div>
		<div class=\"cleared\"></div>";
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new TopNetworks );

 


}

?>