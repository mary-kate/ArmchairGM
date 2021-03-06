<?php
/**
 *
 */
class UserRelationship {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	 /**#@+
	 * @private
	 */
	var $user_id;           	# Text form (spaces not underscores) of the main part
	var $user_name;			# Text form (spaces not underscores) of the main part
	var $friend_count;           	# Text form (spaces not underscores) of the main part
	var $foe_count;           	# Text form (spaces not underscores) of the main part
	
	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct($username) {
		$title1 = Title::newFromDBkey($username  );
		$this->user_name = $title1->getText();
		$this->user_id = User::idFromName($this->user_name);
		
	}
	
	public function addRelationshipRequest($user_to,$type,$message, $email=true){
		$dbr =& wfGetDB( DB_MASTER );
		$user_id_to = User::idFromName($user_to);
		$s = $dbr->selectRow( '`user_relationship_request`', array( 'ur_user_id_to' ), array( 'ur_user_id_to' => $user_id_to, 'ur_user_id_from' =>  $this->user_id), $fname );
		if ( $s->ur_user_id_to > 0 ) {
			return "";	
		}
		$fname = 'user_relationship_request::addToDatabase';
		$dbr->insert( '`user_relationship_request`',
		array(
			'ur_user_id_from' => $this->user_id,
			'ur_user_name_from' => $this->user_name,
			'ur_user_id_to' => $user_id_to,
			'ur_user_name_to' => $user_to,
			'ur_type' => $type,
			'ur_message' => $message,
			'ur_date' => date("Y-m-d H:i:s")
			), $fname
		);
		$request_id = $dbr->insertId();
		
		$this->incNewRequestCount($user_id_to, $type);
		
		if($email)$this->sendRelationshipRequestEmail($user_id_to,$this->user_name,$type);
		return $request_id;
		
	}
	
	public function sendRelationshipRequestEmail($user_id_to,$user_from,$type){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
		if(  $user->getEmail() && $user->getIntOption("notifyfriendrequest",1) ){ //if($user->isEmailConfirmed()  && $user->getIntOption("notifyfriendrequest",1)){
			$request_link = Title::makeTitle( NS_SPECIAL , "ViewRelationshipRequests"  );
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			if($type==1){
				$subject = wfMsg( 'friend_request_subject',
					$user_from
				 );
				$body = wfMsg( 'friend_request_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$request_link->getFullURL(),
					$update_profile_link->getFullURL()
				);
			}else{
				$subject = wfMsg( 'foe_request_subject',
					$user_from
				 );
				$body = wfMsg( 'foe_request_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$request_link->getFullURL(),
					$update_profile_link->getFullURL()
				);				
			}
			$user->sendMail($subject, $body );
		}
	}
	
	public function sendRelationshipAcceptEmail($user_id_to, $user_from, $type){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
		if(  $user->getEmail() && $user->getIntOption("notifyfriendrequest",1) ){ //if($user->isEmailConfirmed()  && $user->getIntOption("notifyfriendrequest",1)){
			$user_link = Title::makeTitle( NS_USER ,  $user_from  );
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			if($type==1){
				$subject = wfMsg( 'friend_accept_subject',
					$user_from
				 );
				$body = wfMsg( 'friend_accept_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$user_link->getFullURL(),
					$update_profile_link->getFullURL()
				);
			}else{
				$subject = wfMsg( 'foe_accept_subject',
					$user_from
				 );
				$body = wfMsg( 'foe_accept_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$user_link->getFullURL(),
					$update_profile_link->getFullURL()
				);				
			}
			$user->sendMail($subject, $body );
		}		
	}

	public function sendRelationshipRemoveEmail($user_id_to, $user_from, $type){
		$user = User::newFromId($user_id_to);
		$user->loadFromDatabase();
		if($user->isEmailConfirmed() && $user->getIntOption("notifyfriendrequest",1)){
			$user_link = Title::makeTitle( NS_USER ,  $user_from  );
			$update_profile_link = Title::makeTitle( NS_SPECIAL , "UpdateProfile"  );
			if($type==1){
				$subject = wfMsg( 'friend_removed_subject',
					$user_from
				 );
				$body = wfMsg( 'friend_removed_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$user_link->getFullURL(),
					$update_profile_link->getFullURL()
				);
			}else{
				$subject = wfMsg( 'foe_removed_subject',
					$user_from
				 );
				$body = wfMsg( 'foe_removed_body',
					(( trim($user->getRealName()) )?$user->getRealName():$user->getName()),
					$user_from,
					$user_link->getFullURL(),
					$update_profile_link->getFullURL()
				);				
			}
			$user->sendMail($subject, $body );
		}		
	}
	
	public function addRelationship($relationship_request_id, $email=true){
		global $wgMemc;
		
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_relationship_request`', 
				array( 'ur_user_id_from','ur_user_name_from','ur_type'),
				array( 'ur_id' => $relationship_request_id ), $fname 
		);
		if ( $s == true ) {
			$ur_user_id_from = $s->ur_user_id_from;
			$ur_user_name_from = $s->ur_user_name_from;
			$ur_type = $s->ur_type;
			
			if( self::getUserRelationshipByID($this->user_id,$ur_user_id_from) > 0 ){
				return "";
			}

			$fname = 'user_relationship::addToDatabase';
			$dbr->insert( '`user_relationship`',
			array(
				'r_user_id' => $this->user_id,
				'r_user_name' => $this->user_name,
				'r_user_id_relation' => $ur_user_id_from,
				'r_user_name_relation' => $ur_user_name_from,
				'r_type' => $ur_type,
				'r_date' => date("Y-m-d H:i:s")
				), $fname
			);
			
			$fname = 'user_relationship::addToDatabase';
			$dbr->insert( '`user_relationship`',
			array(
				'r_user_id' => $ur_user_id_from,
				'r_user_name' => $ur_user_name_from,
				'r_user_id_relation' => $this->user_id,
				'r_user_name_relation' => $this->user_name,
				'r_type' => $ur_type,
				'r_date' => date("Y-m-d H:i:s")
				), $fname
			);
			
			$stats = new UserStatsTrack($this->user_id, $this->user_name);
			if($ur_type==1){
				$stats->incStatField("friend");
			}else{
				$stats->incStatField("foe");
			}
			
			$stats = new UserStatsTrack($ur_user_id_from,$ur_user_name_from);
			if($ur_type==1){
				$stats->incStatField("friend");
			}else{
				$stats->incStatField("foe");
			}
			
			if($email)$this->sendRelationshipAcceptEmail($ur_user_id_from,$this->user_name,$ur_type);
			
			$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$this->user_id}-{$ur_type}") );
			$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$ur_user_id_from}-{$ur_type}") );
		
			return true;
		}else{
			return false;
		}
		
		
	}
	
	public function removeRelationshipByUserID($user1,$user2){
		global $wgUser, $wgMemc;
		
		if($user1!=$wgUser->getID() && $user2!=$wgUser->getID()){
			return false; //only logged in user should be able to delete
		}
		//must delete record for each user involved in relationship
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "DELETE FROM user_relationship WHERE r_user_id={$user1} AND r_user_id_relation={$user2}";
		$res = $dbr->query($sql);
		$sql = "DELETE FROM user_relationship WHERE r_user_id={$user2} AND r_user_id_relation={$user1}";
		$res = $dbr->query($sql);
		
		$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$user1}-1") );
		$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$user2}-1" ) );
			
		$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$user1}-2" ) );
		$wgMemc->delete( wfMemcKey( 'relationship', 'profile', "{$user2}-2" ) );
		
		$stats = new UserStatsTrack($user1,"");
		$stats->updateRelationshipCount(1);
		$stats->updateRelationshipCount(2);
		$stats->clearCache();

		$stats = new UserStatsTrack($user2,"");
		$stats->updateRelationshipCount(1);
		$stats->updateRelationshipCount(2);
		$stats->clearCache();
	}
	
	public function deleteRequest($id){
		$request = $this->getRequest($id);
		$this->decNewRequestCount($this->user_id,$request[0]["rel_type"]);
		
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "DELETE FROM user_relationship_request WHERE ur_id={$id}";
		$res = $dbr->query($sql);;
	}	
	
	public function updateRelationshipRequestStatus($relationship_request_id, $status){
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->update( '`user_relationship_request`',
			array( /* SET */
			'ur_status' => $status
			), array( /* WHERE */
			'ur_id' => $relationship_request_id
			), ""
		);
	}
	
	public function verifyRelationshipRequest($relationship_request_id){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_relationship_request`', array( 'ur_user_id_to' ), array( 'ur_id' => $relationship_request_id ), $fname );
		if ( $s !== false ) {
			if($this->user_id == $s->ur_user_id_to){
				return true;
			}
		}
		return false;
	}
	
	static function getUserRelationshipByID($user1,$user2){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_relationship`', array( 'r_type' ), array( 'r_user_id' => $user1, 'r_user_id_relation' => $user2 ), $fname );
		if ( $s !== false ) {
			return $s->r_type;
		}else{
			return false;
		}
	}
	
	static function userHasRequestByID($user1,$user2){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_relationship_request`', array( 'ur_type' ), array( 'ur_user_id_to' => $user1, 'ur_user_id_from' => $user2, 'ur_status' => 0 ), $fname );
		if ( $s === false ) {
			return false;
		}else{
			return true;
		}
	}
	
	public function getRequest($id){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT ur_id, ur_user_id_from, ur_user_name_from, ur_type, ur_message, ur_date
			FROM user_relationship_request 
			WHERE ur_id = {$id}";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			if($row->ur_type==1){
				$type_name = "Friend";
			}else{
				$type_name = "Foe";
			}
			 $request[] = array(
				 "id"=>$row->ur_id,"rel_type"=>$row->ur_type,"type"=>$type_name,"timestamp"=>($row->ur_date ) ,
				 "user_id_from"=>$row->ur_user_id_from,"user_name_from"=>$row->ur_user_name_from
				 );
		}
		return $request;
	}
	
	public function getRequestList($status,$limit=0){
		$dbr =& wfGetDB( DB_MASTER );
		
		if($limit>0)$limit_sql = " LIMIT 0,{$limit} ";
		
		$sql = "SELECT ur_id, ur_user_id_from, ur_user_name_from, ur_type, ur_message, ur_date
			FROM user_relationship_request 
			WHERE ur_user_id_to = {$this->user_id} AND ur_status = {$status}
			{$limit_sql}
			ORDER BY ur_id DESC";
		$res = $dbr->query($sql);
		
		$requests = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			if( $row->ur_type==1){
				$type_name = "Friend";
			} else {
				$type_name = "Foe";
			}
			if( !in_array( $row->ur_user_id_from, $requests ) ){
			 $requests[$row->ur_user_id_from] = array(
				 "id"=>$row->ur_id,"type"=>$type_name,"message"=>$row->ur_message,"timestamp"=>($row->ur_date ) ,
				 "user_id_from"=>$row->ur_user_id_from,"user_name_from"=>$row->ur_user_name_from
				 );
			}
		}
		return $requests;
	}

	private function incNewRequestCount($user_id, $rel_type){
		return false; // we will just clear on visit to request page instead of inc/dec
		global $wgMemc;
		$key = wfMemcKey( 'user_relationship', 'open_request', $rel_type, $user_id );;
		$wgMemc->incr( $key );
	}

	private function decNewRequestCount($user_id, $rel_type){
		global $wgMemc;
		$key = wfMemcKey( 'user_relationship', 'open_request', $rel_type, $user_id );
		$wgMemc->decr( $key );
	}
	
	static function getOpenRequestCountDB($user_id, $rel_type){
		wfDebug( "Got open request count (type={$rel_type}) for id $user_id from db\n" );
		
		global $wgMemc;
		$key = wfMemcKey( 'user_relationship', 'open_request', $rel_type, $user_id );
		$dbr =& wfGetDB( DB_MASTER );
		$request_count = 0;
		$s = $dbr->selectRow( '`user_relationship_request`', array( 'count(distinct(ur_user_id_from)) as count' ), array( 'ur_user_id_to' => $user_id, 'ur_status' => 0, 'ur_type' => $rel_type ), $fname );
		if ( $s !== false )$request_count = $s->count;	
	
		$wgMemc->set($key,$request_count);
		
		return $request_count;
	}	
	
	static function getOpenRequestCountCache($user_id, $rel_type){
		global $wgMemc;
		$key = wfMemcKey( 'user_relationship', 'open_request', $rel_type, $user_id );
		$data = $wgMemc->get( $key );
		//$wgMemc->delete( $key );
		if( $data != "" ){
			wfDebug( "Got open request count of $data (type={$rel_type}) for id $user_id from cache\n" );
			return $data;
		}
	}		
	
	static function getOpenRequestCount($user_id, $rel_type){
		$data = self::getOpenRequestCountCache($user_id, $rel_type);
		
		if( $data != "" ){
			if($data==-1)$data = 0;
			$count = $data;
		}else{
			$count = self::getOpenRequestCountDB($user_id, $rel_type);
		}	
		return $count;
	}
	
	public function getRelationshipList($type=0,$limit=0,$page=0){
		
		$dbr =& wfGetDB( DB_MASTER  );
		
		if($limit>0){
			$limitvalue = 0;
			if($page)$limitvalue = $page * $limit - ($limit); 
			$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		
		if($type){
			$type_sql = " AND r_type = {$type} ";
		}
			
		$sql = "SELECT r_id, r_user_id_relation, r_user_name_relation, r_date, r_type
			FROM user_relationship 
			WHERE r_user_id = {$this->user_id} $type_sql 
			ORDER BY r_user_name_relation
			{$limit_sql}";
		
		$res = $dbr->query($sql);
		$requests = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			 $requests[] = array(
				 "id"=>$row->r_id,"timestamp"=>($row->r_date ) ,
				 "user_id"=>$row->r_user_id_relation,"user_name"=>$row->r_user_name_relation,
				 "type" => $row->r_type
				 );
		}
		
		return $requests;
	}

	public function getRelationshipIDs($type){
		
		$dbr =& wfGetDB( DB_MASTER );
	
		$sql = "SELECT r_id, r_user_id_relation, r_user_name_relation, r_date
			FROM user_relationship 
			WHERE r_user_id = {$this->user_id} AND r_type = {$type}
			ORDER BY r_user_name_relation
			{$limit_sql}";
		
		$rel = array();
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			 $rel[] =  $row->r_user_id_relation;
		}
		return $rel;
	}
	
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
	
	public function updateUserStats($user_id,$user_name){
            $dbr =& wfGetDB( DB_MASTER );
     	
	    $friend_count = 0;
	    $foe_count = 0;
	    
	    $s = $dbr->selectRow( '`user_relationship`', array( 'count(*) as count' ), array( 'r_user_id' => $user_id, 'r_type' => 1 ), $fname );
	    if ( $s !== false )$friend_count = $s->count;
	    $s = $dbr->selectRow( '`user_relationship`', array( 'count(*) as count' ), array( 'r_user_id' => $user_id, 'r_type' => 2 ), $fname );
	    if ( $s !== false )$foe_count = $s->count;
	    
	    $s = $dbr->selectRow( 'user_relationship_stats', array( 'rs_id' ), array( 'rs_user_id' => $user_id ), $fname );
	    if ( $s === false ) {
		    	$fname = 'user_relationship_stats::addToDatabase';
			$dbr->insert( '`user_relationship_stats`',
			array(
				'rs_user_id' => $user_id,
				'rs_user_name' => $user_name,
				'rs_friend_count' => $friend_count,
				'rs_foe_count' => $foe_count
				), $fname
			);
            }else{
		    $dbr->update( '`user_relationship_stats`',
			array( /* SET */
				'rs_friend_count' => $friend_count,
				'rs_foe_count' => $foe_count
			), array( /* WHERE */
			'rs_user_id' => $user_id
			), ""
		);
	    }
    }
    

}
	
?>
