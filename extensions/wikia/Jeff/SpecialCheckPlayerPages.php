<?php

$wgExtensionFunctions[] = 'wfSpecialCheckPlayerPages';

function wfSpecialCheckPlayerPages(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class CheckPlayerPages extends SpecialPage {

	
	function CheckPlayerPages(){
		UnlistedSpecialPage::UnlistedSpecialPage("CheckPlayerPages");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser;
		
		if ($value) {
			$players = $this->get_test_array($value);
		
			$output = "";
			$count = 0;
			foreach($players as $key=>$arr_value) {
				$count++;	
				//construct page title object to convert to Database Key
				$page_title =  Title::makeTitle( NS_MAIN  , $arr_value );
				$db_key = $page_title->getDBKey();
				
				//Database key would be in page title if the page already exists
				$dbr =& wfGetDB( DB_MASTER );
				$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key ),"" );
				if ( $s !== false ) {
					$output .= "{$arr_value} - " . $s->page_id . " - Page exists (<a href=\"index.php?title={$db_key}\">link</a>)<br/>";
					$output .= $this->check_edits($s->page_id);
					
					
				} else {
					$output .= "{$arr_value} - Page does not exist<br/>";
				}
			}
			
			//return $output;
			$title = "Checking {$count} players whose last names start with {$value}";
			$wgOut->setPageTitle($title);
			$wgOut->addHTML($output);

			
			
			//return SkinSports::getTodaysGames();
		}
	}
	
	function check_edits($page_id) {
			$routput = "";
			//Database key would be in page title if the page already exists
			$dbr =& wfGetDB( DB_MASTER );
			
			//$sql = "SELECT count(distinct rev_user_text) as user_edits FROM revision WHERE rev_page={$page_id}";
			$sql = "SELECT rev_user_text, rev_timestamp FROM revision WHERE rev_page={$page_id} group by rev_user_text, rev_timestamp order by rev_timestamp desc";
			//$s = $dbr->selectRow( 'revision', array( 'count(distinct rev_user_text) as user_edits' ), array( 'rev_page' => $page_id ),"" );
			//$s = $dbr->selectRow( 'revision', array( 'count(distinct rev_user_text) as user_edits', 'rev_user_text' ), array( 'rev_page' => $page_id ), array( 'GROUP BY rev_user_text' );
			
			$result = mysql_query($sql);
			$count = mysql_num_rows($result);
			
			if ($row = mysql_fetch_array($result)) {
				$routput .= "----- {$count} edits to this page... the last one was {$row["rev_user_text"]}<br/>";
			} else {
				$routput .= "-----Page has not been edited <br/>";
			}
	
			return $routput;
	}
	
	function get_test_array($start_text) {
		
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT * FROM mlb_players_info where player_name LIKE '% {$start_text}%'";
	
		
		$return_array = array();
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			$return_array[] = $row->player_name;
		}
		
	
		return $return_array;
	}

}

SpecialPage::addPage( new CheckPlayerPages );



}

?>
