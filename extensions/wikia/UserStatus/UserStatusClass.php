<?php
/**
 *
 */
class UserStatus {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	 /**#@+
	 * @private
	 */
	var $user_id;           	# Text form (spaces not underscores) of the main part
	var $user_name;			# Text form (spaces not underscores) of the main part
	
	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct() {

		
	}
	
	public function addStatus($sport_id,$team_id,$text){	
		global $wgUser;
		$dbr =& wfGetDB( DB_MASTER );
	
		if( $wgUser->isBlocked() ) return "";
		
		$dbr->insert( '`user_status`',
		array(
	
			'us_user_id' => $wgUser->getID(),
			'us_user_name' => $wgUser->getName(),
			'us_sport_id' => $sport_id,
			'us_team_id' => $team_id,
			'us_text' => $text,
			'us_date' => date("Y-m-d H:i:s"),
			), $fname
		);
		$us_id = $dbr->insertId();
	
		$stats = new UserStatsTrack($wgUser->getID(), $wgUser->getName());
		$stats->incStatField("user_status_count");
	
		$this->updateUserCache($text,$sport_id,$team_id);
		return $us_id;
	}
	
	public function addStatusVote($us_id,$vote){	
		global $wgUser;
		
		if($wgUser->isLoggedIn() ){
			
			if( $this->alreadyVotedStatusMessage($wgUser->getID(), $us_id) ){
				return;
			}
			
			$dbr =& wfGetDB( DB_MASTER );
		
			$dbr->insert( '`user_status_vote`',
			array(
				'sv_user_id' => $wgUser->getID(),
				'sv_user_name' => $wgUser->getName(),
				'sv_us_id' => $us_id,
				'sv_vote_score' => $vote,
				'sv_date' => date("Y-m-d H:i:s"),
				), $fname
			);
			$sv_id = $dbr->insertId();
		
			$this->incStatusVoteCount($us_id,$vote);
			return $sv_id;
		}
	}
	
	public function incStatusVoteCount($us_id,$vote){
		if($vote==1){
			$field = "us_vote_plus";
		}else{
			$field = "us_vote_minus";
		}
		$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( 'user_status',
			array( "{$field}={$field}+1" ),
			array( 'us_id' => $us_id ),
			__METHOD__ );
		
	}
	 
	public function updateUserCache($text,$sport_id,$team_id=0){
		global $wgUser, $wgMemc;
		$key = wfMemcKey( 'user', 'status-last-update', $wgUser->getID() );
		
		$data["text"] = $this->formatMessage($text);
		$data["sport_id"] = $sport_id;
		$data["team_id"] = $team_id;
		$data["timestamp"] = time();
		if($team_id){
			$team = SportsTeams::getTeam($team_id);
			$data["network"] = $team["name"];
		}else{
			$sport = SportsTeams::getSport($sport_id);
			$data["network"] = $sport["name"];
		}
		$wgMemc->set( $key, $data );
	}
	
	public function alreadyVotedStatusMessage($user_id, $us_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_status_vote`', array( 'sv_user_id' ), array( 'sv_us_id' => $us_id, 'sv_user_id' => $user_id ), $fname );
		if ( $s !== false ) {
			return true;
		}
		return false;
	}
	
	public function doesUserOwnStatusMessage($user_id, $us_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_status`', array( 'us_user_id' ), array( 'us_id' => $us_id ), $fname );
		if ( $s !== false ) {
			if($user_id == $s->us_user_id){
				return true;
			}
		}
		return false;
	}

	public function deleteStatus($us_id){
		if($us_id){
			$dbr =& wfGetDB( DB_MASTER );
			$s = $dbr->selectRow( '`user_status`', array( 'us_user_id','us_user_name','us_sport_id', 'us_team_id' ), array( 'us_id' => $us_id ), $fname );
			if ( $s !== false ) {
				
				$sql = "DELETE FROM user_status WHERE us_id={$us_id}";
				$res = $dbr->query($sql);
			
				$stats = new UserStatsTrack($s->us_user_id, $s->us_user_name);
				$stats->decStatField("user_status_count");
				
			}
		}
	}
	
	static function formatMessage($message){
		global $wgTitle, $wgOut;
		$message_text = $wgOut->parse( trim($message), false );
		return $message_text;
	}
	
	public function getStatusMessage($us_id){
		global $wgUser, $wgOut, $wgTitle;
		$dbr =& wfGetDB( DB_MASTER );
		
		
		$sql = "SELECT us_id, us_user_id, us_user_name, us_text,
			us_sport_id, us_team_id, us_vote_plus, us_vote_minus,
			UNIX_TIMESTAMP(us_date) as unix_time,
			(select count(*) FROM user_status_vote WHERE sv_us_id = us_id AND sv_user_id =" .  $wgUser->getID()  . ") as AlreadyVoted
			FROM user_status
			WHERE us_id={$us_id} limit 1
			";
			
		$res = $dbr->query($sql);
		$messages = array();
		while ($row = $dbr->fetchObject( $res ) ) {	

			 $messages[] = array(
				 "id"=>$row->us_id,"timestamp"=>($row->unix_time ) ,
				 "user_id"=>$row->us_user_id,"user_name"=>$row->us_user_name,
				 "sport_id"=>$row->us_sport_id,"team_id"=>$row->us_team_id,
				 "plus_count"=>$row->us_vote_plus, "minus_count"=>$row->us_vote_minus,
				 "text"=>$this->formatMessage($row->us_text), "voted"=>$row->AlreadyVoted
			
				 );
		}
		return $messages[0];
	}
	
	public function getStatusMessages($user_id=0,$sport_id=0,$team_id=0,$limit=10,$page=0){
		global $wgUser, $wgOut, $wgTitle;
		$dbr =& wfGetDB( DB_MASTER );
		
		if($limit>0){
			$limitvalue = 0;
			if($page)$limitvalue = $page * $limit - ($limit); 
			$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		
		if($user_id>0){
			$user_sql .= "  us_user_id = {$user_id} ";
		}
	 
		if($sport_id>0 && $team_id == 0){
			$sport_sql .= " ( ( us_sport_id = {$sport_id} and us_team_id = 0 ) or us_team_id in (select team_id from sport_team where team_sport_id = {$sport_id} ) ) ";
		}
		if($team_id>0){
			$sport_sql .= "  us_team_id = {$team_id} ";
		}
		
		if($user_sql && $sport_sql)$user_sql.=" and ";
		
		$sql = "SELECT us_id, us_user_id, us_user_name, us_text,
			us_sport_id, us_team_id, us_vote_plus, us_vote_minus,
			UNIX_TIMESTAMP(us_date) as unix_time,
			(select count(*) FROM user_status_vote WHERE sv_us_id = us_id AND sv_user_id =" .  $wgUser->getID()  . ") as AlreadyVoted
			FROM user_status
			WHERE {$user_sql} {$sport_sql}
			ORDER BY us_id DESC
			{$limit_sql}";
			
		$res = $dbr->query($sql);
		$messages = array();
		while ($row = $dbr->fetchObject( $res ) ) {	

			 $messages[] = array(
				 "id"=>$row->us_id,"timestamp"=>($row->unix_time ) ,
				 "user_id"=>$row->us_user_id,"user_name"=>$row->us_user_name,
				 "sport_id"=>$row->us_sport_id,"team_id"=>$row->us_team_id,
				 "plus_count"=>$row->us_vote_plus, "minus_count"=>$row->us_vote_minus,
				 "text"=>$this->formatMessage($row->us_text), "voted"=>$row->AlreadyVoted
			
				 );
		}
		return $messages;
	}


	public function displayStatusMessages($user_id, $sport_id=0,$team_id=0,$count=10,$page=0){
		global $wgUser,$max_link_text_length;
		$messages = $this->getStatusMessages($user_id, $sport_id, $team_id, $count,$page);
		$messages_count = count($messages);
		$x=1;
		
		$thought_link =  Title::makeTitle( NS_SPECIAL  , "ViewThought"  );
		
		if($messages){
			foreach ($messages as $message) {
				
				$user =  Title::makeTitle( NS_USER  , $message["user_name"]  );
				$avatar = new wAvatar($message["user_id"],"m");
				
				$messages_link = "<a href=\"" . UserStatus::getUserUpdatesURL($message["user_name"])."\">" . wfMsgForContent( 'us_view_all_updates', $message["user_name"] ) ."</a>";
				$delete_link = "";
				
				//$vote_count = $message["plus_count"] . " ". (($message["plus_count"]==1)?"person":"people") . " agree" . (($message["plus_count"]==1)?"s":"");
				$vote_count = wfMsgExt( 'us_num_agree', 'parsemag', $message["plus_count"] );
				
				if ($wgUser->getName()==$message["user_name"]){
					$delete_link = "<span class=\"user-board-red\">
							<a href=\"javascript:void(0);\" onclick=\"javascript:delete_message({$message["id"]})\">" . wfMsgForContent( 'us_delete_thought_text' ) ."</a>
						</span>";
				}
				
				$vote_link = "";
				if( $wgUser->isLoggedIn() && $wgUser->getName()!=$message["user_name"]){
					if ( !$message["voted"] ) {
						$vote_link = "<a href=\"javascript:void(0);\" onclick=\"vote_status({$message["id"]},1)\">[" . wfMsgForContent( 'us_agree' ) ."]</a>";
					} else {
						$vote_link = "{$vote_count}";
					}
				}
				
				
				$view_thought_link = "<a href=\"" . $thought_link->getFullURL() . "&id={$message["id"]}\" >[" . wfMsgForContent( 'us_see_who_agrees' ) ."]</a>";
				
				
				$max_link_text_length = 50;
				$message_text = preg_replace_callback( "/(<a[^>]*>)(.*?)(<\/a>)/i",'cut_link_text',$message["text"]);
		
				if ($x == 1) {
					$output .= "<div class=\"user-status-row-top\">";
				} else if ($x < $messages_count) {
					$output .= "<div class=\"user-status-row\">";
				} else {
					$output .= "<div class=\"user-status-row-bottom\">";
				}
				
				$output .= "
				
				<div class=\"user-status-logo\">
				
					<a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a>
				</div>
				
				<div class=\"user-status-message\">
				
					<a href=\"{$user->getFullURL()}\"><b>{$message["user_name"]}</b></a> {$message_text}
				
					<div class=\"user-status-date\">
						".get_time_ago($message["timestamp"])." ago
						<span class=\"user-status-vote\" id=\"user-status-vote-{$message["id"]}\">
							{$vote_link}
						</span>
						{$view_thought_link}
						<span class=\"user-status-links\"> 
								{$delete_link}
						</span>	
					</div>
					
				</div>
				
				<div class=\"cleared\"></div>
				
				</div>";
			
				$x++;
				
			}
			
		} else {
			$output .= "<p>" . wfMsgForContent ( 'us_no_new_thoughts' ) ."</p>";
		
		}
		return $output;
	}
	
	public function getStatusVotes($us_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_status`', array( 'us_vote_plus','us_vote_minus' ), array( 'us_id' => $us_id ), $fname );
		if ( $s !== false ) {
			$votes["plus"] = $s->us_vote_plus;
			$votes["minus"] = $s->us_vote_minus;
			return $votes;
		}
		return false;
	}

	public function getStatusVoters($us_id){
		global $wgUser, $wgOut, $wgTitle;
		$dbr =& wfGetDB( DB_MASTER );

		$sql = "SELECT sv_user_id, sv_user_name,
			UNIX_TIMESTAMP(sv_date) as unix_time, 
			sv_vote_score
			FROM user_status_vote
			WHERE sv_us_id = {$us_id}
			ORDER BY sv_id DESC
			{$limit_sql}";
			
		$res = $dbr->query($sql);
		$voters = array();
		while ($row = $dbr->fetchObject( $res ) ) {	

			 $voters[] = array(
				 "timestamp"=>($row->unix_time ) ,
				 "user_id"=>$row->sv_user_id,"user_name"=>$row->sv_user_name,
				 "score"=>$row->sv_vote_score
				 );
		}
		return $voters;
	}
	
	static function getNetworkUpdatesCount($sport_id,$team_id){
		if(!$team_id){
			$where_sql = " ( ( us_sport_id = {$sport_id} and us_team_id = 0 ) or us_team_id in (select team_id from sport_team where team_sport_id = {$sport_id} ) ) ";
		}else{
			$where_sql = " us_team_id = {$team_id} ";	
		}
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as the_count FROM user_status WHERE {$where_sql} ";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		return $row->the_count;		
	}
	
	static function getUserUpdatesURL($user_name){
		$title = Title::makeTitle( NS_SPECIAL , "UserStatus"  );
		return $title->escapeFullURL("user=$user_name");
	}
	

	

	
	
	
}
	
?>