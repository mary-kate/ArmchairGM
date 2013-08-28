<?php
/**
 *
 */
class Challenge {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	 /**#@+
	 * @private
	 */
	var $rating_names = array(1=>"positive",-1=>"negative",0=>"neutral");
	
	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct() {
	
		
	}
	
	public function addChallenge($user_to,$info, $event_date, $description, $win_terms, $lose_terms){
		global $wgUser;
		$user_id_to = User::idFromName($user_to);
		$fname = 'ChallengeUser::addToDatabase';
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->insert( '`challenge`',
			array(
				'challenge_user_id_1' => $wgUser->getID(),
				'challenge_username1' => $wgUser->getName(),
				'challenge_user_id_2' => $user_id_to,
				'challenge_username2' => $user_to,
				'challenge_info' =>  $info ,
				'challenge_description' => $description,
				'challenge_win_terms' => $win_terms,
				'challenge_lose_terms' => $lose_terms,
				'challenge_status' => 0,
				'challenge_date' => date("Y-m-d H:i:s"),
				'challenge_event_date'  => $event_date
			), $fname
		);
		$this->challenge_id = $dbw->insertId();
		$this->sendChallengeRequestEmail($user_id_to,$wgUser->getName(),$this->challenge_id);
		
	}
	
	
	public function sendChallengeRequestEmail($user_id_to,$user_from,$id){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
	
		if($user->isEmailConfirmed() && $user->getIntOption("notifychallenge",1)){
			$challenge_view_title = Title::makeTitle( NS_SPECIAL  , "ChallengeView"  ); 
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			$subject = wfMsg( 'challenge_request_subject',
				$user_from,
				$challenge_view_title->getFullURL() . "&id=".$id
			 );
			$body = wfMsg( 'challenge_request_body',
				$user->getName(),
				$user_from,
				$update_profile_link->getFullURL(),
				$challenge_view_title->getFullURL() . "&id=".$id
			);
			$user->sendMail($subject, $body );
		}
	}

	public function sendChallengeAcceptEmail($user_id_to,$user_from,$id){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();

		if($user->isEmailConfirmed() && $user->getIntOption("notifychallenge",1)){
			$challenge_view_title = Title::makeTitle( NS_SPECIAL  , "ChallengeView"  ); 
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			$subject = wfMsg( 'challenge_accept_subject',
				$user_from,
				$challenge_view_title->getFullURL() . "&id=".$id
			 );
			$body = wfMsg( 'challenge_accept_body',
				$user->getName(),
				$user_from,
				$update_profile_link->getFullURL(),
				$challenge_view_title->getFullURL() . "&id=".$id
			);
			$user->sendMail($subject, $body );
		}
	}	

	public function sendChallengeLoseEmail($user_id_to,$user_from,$id){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
		if($user->isEmailConfirmed() && $user->getIntOption("notifychallenge",1)){
			$challenge_view_title = Title::makeTitle( NS_SPECIAL  , "ChallengeView"  ); 
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			$subject = wfMsg( 'challenge_lose_subject',
				$user_from,
				$id
			 );
			$body = wfMsg( 'challenge_lose_body',
				$user->getName(),
				$user_from,
				$update_profile_link->getFullURL(),
				$challenge_view_title->getFullURL() . "&id=".$id
			);
			$user->sendMail($subject, $body );
		}
	}	

	public function sendChallengeWinEmail($user_id_to,$user_from,$id){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
		if($user->isEmailConfirmed() && $user->getIntOption("notifychallenge",1)){
			$challenge_view_title = Title::makeTitle( NS_SPECIAL  , "ChallengeView"  ); 
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			$subject = wfMsg( 'challenge_win_subject',
				$user_from,
				$id
			 );
			$body = wfMsg( 'challenge_win_body',
				$user->getName(),
				$user_from,
				$update_profile_link->getFullURL(),
				$challenge_view_title->getFullURL() . "&id=".$id
			);
			$user->sendMail($subject, $body );
		}
	}		

	public function updateChallengeStatus($challenge_id, $status, $email=true){
		global $IP;
		require_once ("$IP/extensions/UserStats/UserStatsClass.php");
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->update( '`challenge`',
			array( /* SET */
			'challenge_status' => $status
			), array( /* WHERE */
			'challenge_id' => $challenge_id
			), ""
		);
		$c = $this->getChallenge($challenge_id);
		
		switch ($status) {
			case 1: //challenge was accepted
					
				//update Stats for both users involved in challenge
				$stats = new UserStatsTrack(1,$c["user_id_1"], $c["user_name_1"]);
				$stats->incStatField("challenges");
				
				$stats = new UserStatsTrack(1,$c["user_id_2"], $c["user_name_2"]);
				$stats->incStatField("challenges");	
				
				if($email)$this->sendChallengeAcceptEmail($c["user_id_1"],$c["user_name_2"],$challenge_id);
				
			break;
			case 3: //challenge was completed, send email to loser
				$stats = new UserStatsTrack(1,$c["winner_user_id"], $c["winner_user_name"]);
				$stats->incStatField("challenges_won");	
				
				
				$this->updateUserStandings( $challenge_id );
				if($c["winner_user_id"] == $c["user_id_1"]){
					$loser_id = $c["user_id_2"];
					$loser_name = $c["user_name_2"];
				}else{
					$loser_id = $c["user_id_1"];
					$loser_name = $c["user_name_1"];
				}
				if($email){
					$this->sendChallengeLoseEmail($loser_id,$c["winner_user_name"], $challenge_id);
					$this->sendChallengeWinEmail($c["winner_user_id"],$loser_name, $challenge_id);
				}
			break;
		}
	}
	
	public function updateUserStandings($id){
			$dbr =& wfGetDB( DB_MASTER );
			$s = $dbr->selectRow( '`challenge`', array( 'challenge_user_id_1','challenge_username1','challenge_user_id_2','challenge_username2','challenge_info','challenge_event_date','challenge_description','challenge_win_terms','challenge_lose_terms','challenge_winner_user_id','challenge_winner_username','challenge_status'),
		
			array( 'challenge_id' => $id ), "" );
	
			if ( $s !== false ) {
				if( $s->challenge_winner_user_id != -1){ // if its not a tie
					if( $s->challenge_user_id_1 == $s->challenge_winner_user_id){
						$winner_id = $s->challenge_user_id_1;
						$loser_id = $s->challenge_user_id_2;
					}else{
						$winner_id = $s->challenge_user_id_2;
						$loser_id = $s->challenge_user_id_1;
					}
					$this->updateUserRecord($winner_id,1);
					$this->updateUserRecord($loser_id,-1);
				}else{
					$this->updateUserRecord($s->challenge_user_id_1,0);
					$this->updateUserRecord($s->challenge_user_id_2,0);
				} 	
			} 
	}

	public function updateChallengeWinner($id,$user_id){
		$user = User::newFromId($user_id);
		$user_name = $user->getName();
		$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( '`challenge`',
				array( /* SET */
					'challenge_winner_user_id' => $_POST["userid"],  'challenge_winner_username' => $user_name ), array( /* WHERE */
					'challenge_id' => $id ), ""
				);
	}
	
	public function updateUserRecord($id,$type){
		$user = User::newFromId($id);
		$username = $user->getName();
		
		$dbr =& wfGetDB( DB_SLAVE );
		$wins = 0;
		$losses = 0;
		$ties = 0;
		
		$sql = "SELECT challenge_wins, challenge_losses,challenge_ties FROM challenge_user_record  WHERE challenge_record_user_id =  " .  $id . " LIMIT 0,1";
	
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		if(!$row){
			switch ($type) {
				case -1:
				   $losses = 1;
				   break;
				case 0:
				  $ties = 1;
				   break;
				 case 1:
				  $wins = 1;
				   break;
			}
			$sql2 =  "INSERT INTO challenge_user_record (challenge_record_user_id,challenge_record_username,challenge_wins,challenge_losses,challenge_ties)
					VALUES (" . $id . ", '" .  addslashes($username) . "'," . $wins . "," . $losses . "," . $ties . ")";
		}else{
				
			   $wins =  $row->challenge_wins;
				$losses =  $row->challenge_losses;
				$ties =  $row->challenge_ties;
				switch ($type) {
				case -1:
				   $losses++;
				   break;
				case 0:
				  $ties++;
				   break;
				 case 1:
				  $wins++;
				   break;
				}
				$sql2 = "UPDATE `challenge_user_record` SET challenge_wins = " . $wins . ", challenge_losses=" . $losses . ",challenge_ties=" . $ties . " WHERE challenge_record_user_id = " . $id;
		}
		$res2 = $dbr->query($sql2);
	}
		
	public function isUserInChallenge($user_id,$challenge_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`challenge`', array( 'challlenge_user_id_1', 'challlenge_user_id_2'  ), array( 'challenge_id' => $challenge_id ), $fname );
		if ( $s !== false ) {
			if($this->user_id == $s->challlenge_user_id_1 || $this->user_id == $s->challlenge_user_id_2){
				return true;
			}
		}
		return false;
	}
	
	static function getOpenChallengeCount($user_id){
		$dbr =& wfGetDB( DB_MASTER );
		$open_challenge_count = 0;
		$s = $dbr->selectRow( '`challenge`', array( 'count(*) as count' ), array( 'challenge_user_id_2' => $user_id, 'challenge_status' => 0 ), $fname );
		if ( $s !== false )$open_challenge_count = $s->count;	
		return $open_challenge_count;
	}

	static function getChallengeCount($user_id=0){
		$dbr =& wfGetDB( DB_SLAVE );
		$challenge_count = 0;
		
		if($user_id)$user_sql = array( 'challenge_user_id_1' => $user_id);
		
		$s = $dbr->selectRow( '`challenge`', array( 'count(*) as count' ),"", $fname );
		if ( $s !== false )$challenge_count = $s->count;	
		return $challenge_count;
	}
	
	public function getChallenge($id){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT challenge.challenge_id as id, challenge_user_id_1, challenge_username1, challenge_user_id_2, challenge_username2, challenge_info, challenge_description, challenge_event_date, challenge_status, challenge_winner_username,challenge_winner_user_id,
			challenge_win_terms,challenge_lose_terms,challenge_rate_score, challenge_rate_comment
			FROM challenge LEFT JOIN challenge_rate ON challenge_rate.challenge_id=challenge.challenge_id  WHERE challenge.challenge_id = {$id}";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			
			 $challenge[] = array(
				 "id"=>$row->id,"status" => $row->challenge_status, "user_id_1"=>$row->challenge_user_id_1,"user_name_1"=>$row->challenge_username1,
				 "user_id_2"=>$row->challenge_user_id_2 , "user_name_2"=>$row->challenge_username2,
				 "info"=>$row->challenge_info,"description"=>$row->challenge_description, "date" => $row->challenge_event_date,
				 "win_terms" => $row->challenge_win_terms, "lose_terms" => $row->challenge_lose_terms,
				 "winner_user_id" => $row->challenge_winner_user_id, "winner_user_name" => $row->challenge_winner_username,
				 "rating" => $row->challenge_rate_score, "rating_comment" => $row->challenge_rate_comment
				 );
		}
		return $challenge[0];
	}
	
	public function getChallengeList($user_name,$status=NULL,$limit=0,$page=0){
		$dbr =& wfGetDB( DB_MASTER );
		
		if($limit>0){
			$limitvalue = 0;
			if($page)$limitvalue = $page * $limit - ($limit); 
			$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		
		if($status!=NULL)$status_sql = " and challenge_status = {$status}";
		if($user_name){
			$user_id = User::idFromName($user_name);
			$user_sql  = " and (challenge_user_id_1 = {$user_id} OR challenge_user_id_2 = {$user_id} ) ";
		}
		$dbr =& wfGetDB( DB_MASTER );
			$sql = " SELECT challenge.challenge_id as id, challenge_user_id_1, challenge_username1, challenge_user_id_2,  challenge_username2, challenge_info, challenge_description, challenge_event_date, challenge_status, challenge_winner_username,challenge_winner_user_id,
			challenge_win_terms,challenge_lose_terms,challenge_rate_score, challenge_rate_comment
			FROM challenge LEFT JOIN challenge_rate ON challenge_rate.challenge_id=challenge.challenge_id 
			WHERE 1=1 
			{$user_sql}
			{$status_sql}
			ORDER BY challenge_date DESC
			{$limit_sql}
			";

		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			 $challenges[] = array(
				 "id"=>$row->id,"status" => $row->challenge_status, "user_id_1"=>$row->challenge_user_id_1,"user_name_1"=>$row->challenge_username1,
				 "user_id_2"=>$row->challenge_user_id_2 , "user_name_2"=>$row->challenge_username2,
				 "info"=>$row->challenge_info,"description"=>$row->challenge_description, "date" => $row->challenge_event_date,
				 "win_terms" => $row->challenge_win_terms, "lose_terms" => $row->challenge_lose_terms,
				 "winner_user_id" => $row->challenge_winner_user_id, "winner_user_name" => $row->challenge_winner_username,
				 "rating" => $row->challenge_rate_score, "rating_comment" => $row->challenge_rate_comment
				 );
		}
		return $challenges;
	}
	
	
	static function getUserChallengeRecord($user_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`challenge_user_record`', array( 'challenge_wins', 'challenge_losses','challenge_ties'),
		array( 'challenge_record_user_id' => $user_id ), "" );
		if ( $s !== false ) {
			return $s->challenge_wins . "-" . $s->challenge_losses . "-" . $s->challenge_ties;
		}else{
			return "0-0-0";
		}
	}
	
	static function getUserFeedbackScoreByType($ratetype,$userid){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT COUNT(*) AS total FROM challenge_rate WHERE challenge_rate_user_id = " . $userid . " and challenge_rate_score = " . $ratetype;
		$res = $dbr->query( $sql, $fname );
		$pageRow = $dbr->fetchObject( $res );
		$total = $pageRow->total;
		return $total;
	}
	
	static function getChallengeStatusName($status){
		$out = "";
		switch ($status) {
			case -1:
				$out .=  "rejected";
				break;
			case -2:
				$out .=  "removed";
				break;
			case 0:
				$out .=  "awaiting acceptance";
				break;
			case 1:
				$out .=  "in progress";
				break;
			case 3:
				$out .=  "completed";
				break;
		}
		return $out;
	}
	
	/*
	static function getRelationshipCountByUsername($user_name){
		$dbr =& wfGetDB( DB_MASTER );
		$user_id = User::idFromName($user_name);
		$sql = "SELECT rs_friend_count, rs_foe_count
			FROM user_relationship_stats
			WHERE rs_user_id = {$user_id}
			LIMIT 0,1";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		$friend_count = 0;
		$foe_count = 0;
		if($row){
			 $friend_count=$row->rs_friend_count;
			 $foe_count=$row->rs_foe_count;
		}
		$stats["friend_count"]= $friend_count;
		$stats["foe_count"]= $foe_count;
		return $stats;		
	}
	*/
	
	
    

}
	
?>