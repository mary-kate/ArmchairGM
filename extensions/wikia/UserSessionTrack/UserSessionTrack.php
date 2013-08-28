<?php

$wgExtensionFunctions[] = 'fnUserSessionTrack';

function fnUserSessionTrack(){
	global $wgUser, $wgMemc;

	//logged in user has entered the site as new session
	if( $wgUser->isLoggedIn() && !isset($_SESSION['wsUserSession']) ){
		
		$session_count = UserSessionTrack::getCount();
		
		//reset the cache for this user
		$session_count = $session_count + 1;
		$wgMemc->set( $key, $session_count );

		//update new data
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "update low_priority user_session_track set us_count={$session_count} where us_user_id = {$wgUser->getID()}";
	
		$res = $dbr->query($sql);
			
		//set flag for this session
		$_SESSION['wsUserSession'] = 1;
		
		wfDebug( "incremented session count for user {$wgUser->getName()}\n" );
	}
	return "";
}

Class UserSessionTrack {

	static function getCount(){
		global $wgUser, $wgMemc;
		
		$key = wfMemcKey( 'user', 'session', $wgUser->getID() );
		$data = $wgMemc->get( $key );
		
		$session_count = 0;
		if($data){
			//try getting from cache
			$session_count = $data;
			wfDebug( "loading session count for user {$wgUser->getName()} from cache\n" );
		}else{
			//retreive from db
			$dbr =& wfGetDB( DB_MASTER );
			$s = $dbr->selectRow( '`user_session_track`', array( 'us_count' ), array( 'us_user_id' => $wgUser->getID()  ), $fname );
			if ( $s !== false ){
				$session_count = $s->us_count;	
			}else{
				$dbr->insert( '`user_session_track`',
				array(
					'us_user_id' => $wgUser->getID(),
					'us_user_name' => $wgUser->getName(),
					'us_count' => 1
					), $fname
				);
			}
			wfDebug( "loading session count for user {$wgUser->getName()} from db\n" );
		}
		return $session_count;
		
	}
}

?>