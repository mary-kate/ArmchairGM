<?php
$wgExtensionFunctions[] = "wfCheckPlayers";

function wfCheckPlayers() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "checkPlayers", "lookupPlayers" );
}

function lookupPlayers($input, $args, $parser) {

	
	$players = get_test_array();
	
	$output = "";
	
	foreach($players as $key=>$value) {
			
		//construct page title object to convert to Database Key
		$page_title =  Title::makeTitle( NS_MAIN  , $value );
		$db_key = $page_title->getDBKey();
		
		//Database key would be in page title if the page already exists
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( 'page', array( 'page_id' ), array( 'page_title' => $db_key ),"" );
		if ( $s !== false ) {
			$output .= "{$value} - " . $s->page_id . " - Page exists (<a href=\"index.php?title={$db_key}\">link</a>)<br/>";
			$output .= check_edits($s->page_id);
			
			
		} else {
			$output .= "{$value} - Page does not exist<br/>";
		}
	}
	
	return $output;
	
	
	//return SkinSports::getTodaysGames();
	
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

function get_test_array() {
	
	$dbr =& wfGetDB( DB_MASTER );
	
	$sql = "SELECT * FROM mlb_players_info where player_name LIKE '% A%'";

	
	$return_array = array();
	
	$res = $dbr->query($sql);
	while ($row = $dbr->fetchObject( $res ) ) {
		$return_array[] = $row->player_name;
	}
	

	return $return_array;
}

?>
