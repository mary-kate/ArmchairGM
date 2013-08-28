<?php

$wgExtensionFunctions[] = 'wfSpecialMarchMadnessUpdatePicks';

function wfSpecialMarchMadnessUpdatePicks(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class MarchMadnessUpdatePicks extends SpecialPage {
	
	function __construct(){
		UnlistedSpecialPage::__construct("SpringSillinessUpdatePicks");

	}
	
	function march_madness_update_picks_link($value=0, $query="") {
		//return Title::makeTitle(NS_SPECIAL, "MarchMadnessUpdatePicks" . (($value)?"/".$value:""))->escapeFullUrl($query);
		return Title::makeTitle(NS_SPECIAL, "SpringSillinessUpdatePicks" . (($value)?"/".$value:""))->escapeFullUrl($query);
	}

	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $wgMemc;
		
		if (!$value) {

			$output = $page_title = "You must have a tournament id entered";
			
			$wgOut->setPageTitle($page_title);
			$wgOut->addHTML($output);
			return "";
						
		}
		
		$page_title = "Updating correct/incorrect picks";
		set_time_limit(0);
		
		$output = "";
		
		$output .= "<div class=\"madness-nav-link\">";
		$output .= "<a href=\"" . MarchMadnessGroupAdmin::march_madness_group_link() . "\">Back to Home</a> - ";
		$output .= "<a href=\"" . MarchMadnessEnterWinners::march_madness_enter_winners_link($value) . "\">Back to Winner Enty</a>";
		$output .= "</div>";

		
		$tournament_id=$value;
		$brackets = MarchMadness::get_brackets_from_db($tournament_id);
		$brackets_flip = array_flip($brackets);
		$winners = MarchMadness::get_winners_from_db($tournament_id);
		foreach ($winners as $bracket_id=>$bracket_winners) {
			foreach($bracket_winners as $round=>$round_winners) {
				foreach($round_winners as $game_id=>$game_info) {
					$output .= "{$brackets_flip[$bracket_id]} Round: {$round} Game: {$game_info["which_game"]} Winner: {$game_info["winner"]} Loser: {$game_info["loser"]}<br/>";
					$this->update_pick_in_db($tournament_id, $game_info["which_game"], $game_info["winner"], $game_info["loser"], $round+1, $game_info["skip_calculate"], $bracket_id);
				}
			}
		}
		
		$output .= "<br/>Deleting Group Standings:<br/>";
		$group_list = MarchMadness::get_all_groups_from_db($tournament_id);
		foreach($group_list as $temp_group_id=>$temp_group_stuff) {
			//$output .= $temp_group_stuff . "<br/>";
			$key = wfMemcKey( 'marchmadness', 'groupstandings', $temp_group_id );
			$wgMemc->delete( $key );
			$output .= "Deleted group standings cache for group {$temp_group_id}.<br/>";
		}
		
		
		$wgOut->setPageTitle($page_title);
		$wgOut->addHTML($output);
		
		
	}
	
	function update_pick_in_db($tournament_id, $which_pick, $winner, $loser, $round, $skip_calculate, $bracket) {
		
		$dbr =& wfGetDB( DB_MASTER );

				
		//$sql = "UPDATE madness_tournament_picks SET pick_correct='1', skip_calculate={$skip_calculate} WHERE tournament_id={$tournament_id} AND which_pick='{$which_pick}' AND pick='{$winner}'";
		$sql = "UPDATE madness_tournament_picks SET pick_correct='1' WHERE tournament_id={$tournament_id} AND which_pick='{$which_pick}' AND pick='{$winner}'";
		$dbr->query($sql);
		
		//$sql = "UPDATE madness_tournament_picks SET pick_correct='0', skip_calculate={$skip_calculate} WHERE tournament_id={$tournament_id} AND which_pick='{$which_pick}' AND pick<>'{$winner}'";
		$sql = "UPDATE madness_tournament_picks SET pick_correct='0' WHERE tournament_id={$tournament_id} AND which_pick='{$which_pick}' AND pick<>'{$winner}'";
		$dbr->query($sql);
		
		//$sql = "UPDATE madness_tournament_picks SET pick_correct='0', skip_calculate={$skip_calculate} WHERE tournament_id={$tournament_id} AND pick='{$loser}' AND round>{$round}";
		$sql = "UPDATE madness_tournament_picks SET pick_correct='0' WHERE tournament_id={$tournament_id} AND pick='{$loser}' AND round>{$round}";
		$dbr->query($sql);
		
	}
}

SpecialPage::addPage( new MarchMadnessUpdatePicks );


}

?>
