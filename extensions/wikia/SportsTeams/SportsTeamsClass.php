<?php
/**
 *
 */
class SportsTeams {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	 /**#@+
	 * @private
	 */

	
	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct( ) {
	
		
	}
	
	static function getSports(){
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT  sport_id, sport_name FROM sport order by sport_order";
		$res = $dbr->query($sql);
		$sports = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$sports[] = array("id"=>$row->sport_id,"name"=>$row->sport_name);
		}
		return $sports;			
	}

	static function getTeams($sport_id){
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT team_id,team_name,team_sport_id FROM sport_team WHERE team_sport_id ={$sport_id} order by team_name";
		$res = $dbr->query($sql);
		$teams = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$teams[] = array("id"=>$row->team_id,"name"=>$row->team_name);
		}
		return $teams;			
	}
	
	static function getTeam($team_id){
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT team_id,team_name,team_sport_id FROM sport_team WHERE team_id ={$team_id} LIMIT 0,1";
		$res = $dbr->query($sql);
		$teams = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$teams[] = array("id"=>$row->team_id,"name"=>$row->team_name,"sport_id"=>$row->team_sport_id);
		}
		return $teams[0];			
	}

	static function getSport($sport_id){
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT sport_id, sport_name FROM sport WHERE sport_id ={$sport_id} LIMIT 0,1";
		$res = $dbr->query($sql);
		$sports = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$sports[] = array("id"=>$row->sport_id,"name"=>$row->sport_name);
		}
		return $sports[0];			
	}
	
	static function getNetworkName($sport_id,$team_id){
		if($team_id){
			$network = SportsTeams::getTeam($team_id);
		}else{
			$network = SportsTeams::getSport($sport_id);
			
		}
		return $network["name"];
	}
	
	public function addFavorite($user_id,$sport_id,$team_id){
		if($user_id>0){
			$user = User::newFromId($user_id);
			$user->loadFromDatabase();
			$user_name = $user->getName();
			
			if(!$this->isFan($user_id,$sport_id,$team_id) ){
				$dbw =& wfGetDB( DB_MASTER );	
				$dbw->insert( '`sport_favorite`',
				array(
					'sf_sport_id' => $sport_id,
					'sf_team_id' => $team_id,
					'sf_user_id' => $user_id,
					'sf_user_name' => $user_name,
					'sf_order' => ($this->getUserFavoriteTotal($user_id)+1),
					'sf_date' => date("Y-m-d H:i:s")
				), __METHOD__
				);
				$this->clearUserCache($user_id);
			}

		}
	}
	
	static function clearUserCache($user_id){
		global $wgMemc;
		$key = wfMemcKey( 'user', 'teams', $user_id );
		$data = $wgMemc->delete( $key );
	}
	
	static function getUserFavorites($user_id,$order=0){
		global $wgMemc;
		
		//try cache first
		
		$key = wfMemcKey( 'user', 'teams', $user_id);
		$wgMemc->delete( $key );
		$data = $wgMemc->get( $key );
		if($data){
			wfDebug( "Got favorite teams for {$user_id} from cache\n" );
			$favs = $data;
		}else{
			$dbr =& wfGetDB( DB_MASTER );
			
			$sql = "SELECT sport_id,sport_name,team_id,team_name,sf_user_id,sf_user_name,sf_order FROM sport_favorite
				INNER JOIN sport on sf_sport_id=sport_id
				LEFT JOIN sport_team on sf_team_id=team_id 
				WHERE sf_user_id = {$user_id} ORDER BY sf_order
				";
			$res = $dbr->query($sql);
			$favs = array();
			while ($row = $dbr->fetchObject( $res ) ) {
				$favs[] = array(
						"sport_id"=>$row->sport_id,"sport_name"=>$row->sport_name,
						"team_id"=>((!$row->team_id)?0:$row->team_id),"team_name"=>$row->team_name,"order"=>$row->sf_order
					);
			}
			$wgMemc->set( $key, $favs );
		}
		return $favs;		
	}
	
	static function getLogo($sport_id,$team_id=0,$size){
		if($sport_id > 0 && $team_id == 0){
			$logo_tag = "<img src=\"images/sport_logos/" . SportsTeams::getSportLogo($sport_id,$size) . "\" border=\"0\" alt=\"\" />";
		}else{
			$logo_tag =  "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($team_id,$size) . "\" border=\"0\" alt=\"\" />";	
		}
		
		return $logo_tag;
	}
	
	static function getTeamLogo($id,$size){
		global $wgUploadDirectory;
		$files = glob($wgUploadDirectory . "/team_logos/" . $id .  "_" . $size . "*");
		
		if(!$files[0]){
			$img = "default" . "_" . $size . ".gif";
		}else{
			$img = basename($files[0]) ;
		}
		return $img;		
	}
	
	static function getSportLogo($id,$size){
		global $wgUploadDirectory;
		$files = glob($wgUploadDirectory . "/sport_logos/" . $id .  "_" . $size . "*");
		
		if(!$files[0]){
			$img = "default" . "_" . $size . ".gif";
		}else{
			$img = basename($files[0]) ;
		}
		return $img;		
	}
	
	static function getUsersByFavorite($sport_id, $team_id, $limit, $page){
		global $wgMemc;
		
		//try cache first
		//$key = wfMemcKey( 'user', 'teams', $user_id);
		//$wgMemc->delete( $key );
		//$data = $wgMemc->get( $key );
		//if($data){
		//	wfDebug( "Got favorite teams for {$user_id} from cache\n" );
		//	$favs = $data;
		//}else{
			$dbr =& wfGetDB( DB_MASTER );
	
			if($limit>0){
				$limitvalue = 0;
				if($page)$limitvalue = $page * $limit - ($limit); 
				$limit_sql = " LIMIT {$limitvalue},{$limit} ";
			}
			if(!$team_id){
				$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
			}else{
				$where_sql = " sf_team_id = {$team_id} ";	
			}
		
			$sql = "SELECT sport_id,sport_name,team_id,team_name,sf_user_id,sf_user_name,sf_order FROM sport_favorite
				INNER JOIN sport on sf_sport_id=sport_id
				LEFT JOIN sport_team on sf_team_id=team_id 
				WHERE {$where_sql} ORDER BY sf_id DESC
				{$limit_sql}
				";
				
			$res = $dbr->query($sql);
			$fans = array();
			while ($row = $dbr->fetchObject( $res ) ) {
				$fans[] = array(
						"user_id"=>$row->sf_user_id,"user_name"=>$row->sf_user_name
					);
			}
			//$wgMemc->set( $key, $favs );
		//}
		return $fans;		
	}

	static function getSimilarUsers($user_id,$limit=0, $page=0){
		
		global $wgMemc;
		
	
		$dbr =& wfGetDB( DB_MASTER );

		if($limit>0){
			$limitvalue = 0;
			if($page)$limitvalue = $page * $limit - ($limit); 
			$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		 
	
		$sql = "SELECT distinct(sf_user_id),sf_user_name FROM sport_favorite
			 
			WHERE sf_team_id in (select sf_team_id from sport_favorite where sf_user_id ={$user_id}) 
			and sf_team_id <> 0 and sf_user_id <> {$user_id}
			ORDER BY sf_id DESC
			{$limit_sql}
			";
		
		$res = $dbr->query($sql);
		$fans = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$fans[] = array(
					"user_id"=>$row->sf_user_id,"user_name"=>$row->sf_user_name
				);
		}
		
		return $fans;		
	}
	
	static function getUsersByPoints($sport_id, $team_id, $limit, $page){
		$dbr =& wfGetDB( DB_MASTER );

		if($limit>0){
			$limitvalue = 0;
			if($page)$limitvalue = $page * $limit - ($limit); 
			$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		if(!$team_id){
			$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
		}else{
			$where_sql = " sf_team_id = {$team_id} ";	
		}
	
		$sql = "SELECT sport_id,sport_name,team_id,team_name,sf_user_id,sf_user_name,sf_order,stats_total_points
			FROM sport_favorite
			INNER JOIN sport on sf_sport_id=sport_id
			LEFT JOIN sport_team on sf_team_id=team_id 
			LEFT JOIN user_stats on sf_user_id=stats_user_id 
			WHERE {$where_sql} ORDER BY stats_total_points DESC
			{$limit_sql}
			";
			
		$res = $dbr->query($sql);
		$fans = array();
		while ($row = $dbr->fetchObject( $res ) ) {
			$fans[] = array(
					"user_id"=>$row->sf_user_id,"user_name"=>$row->sf_user_name, "points"=>$row->stats_total_points
				);
		}
			
		return $fans;		
	}
	
	static function getUserCount($sport_id,$team_id){
		if(!$team_id){
			$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
		}else{
			$where_sql = " sf_team_id = {$team_id} ";	
		}
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as the_count FROM sport_favorite WHERE {$where_sql}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		return $row->the_count;
	}

	static function getUserFavoriteTotal($user_id){		 
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as the_count FROM sport_favorite WHERE sf_user_id={$user_id}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		return $row->the_count;
	}
	
	static function getFriendsCountInFavorite($user_id,$sport_id,$team_id){
		if(!$team_id){
			$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
		}else{
			$where_sql = " sf_team_id = {$team_id} ";	
		}
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as the_count FROM sport_favorite WHERE {$where_sql} and sf_user_id IN (select r_user_id_relation FROM user_relationship where r_user_id = {$user_id} and r_type = 1)";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		return $row->the_count;		
	}
	
	static function getSimilarUserCount($user_id){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT count(*) as the_count FROM sport_favorite WHERE sf_team_id in (select sf_team_id from sport_favorite where sf_user_id ={$user_id}) and sf_team_id <> 0 and sf_user_id <> {$user_id}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		return $row->the_count;
	}
	
	static function isFan($user_id,$sport_id,$team_id){
		if(!$team_id){
			$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
		}else{
			$where_sql = " sf_team_id = {$team_id} ";	
		}	
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT sf_id FROM sport_favorite WHERE sf_user_id={$user_id} and {$where_sql}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		if(!$row){
			return false;
		}else{
			return true;
		}
	}
	
	static function removeFavorite($user_id,$sport_id,$team_id){
		if(!$team_id){
			$where_sql = " sf_sport_id = {$sport_id} and sf_team_id = 0 ";
		}else{
			$where_sql = " sf_team_id = {$team_id} ";	
		}
		
		$dbr =& wfGetDB( DB_MASTER );
		
		//get the order of team being deleted;
		$sql = "SELECT sf_order FROM sport_favorite WHERE sf_user_id={$user_id} and {$where_sql}";
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		$order = $row->sf_order;
		
		//update orders for those less than one being deleted
		$sql = "UPDATE sport_favorite set sf_order=sf_order-1 where sf_user_id={$user_id} and sf_order > {$order}";
		$res = $dbr->query($sql);
		
		//finally we can remove the fav
		$sql = "DELETE FROM sport_favorite WHERE sf_user_id={$user_id} and {$where_sql}";
		$res = $dbr->query($sql);
	}

	
	static function getNetworkURL($sport_id,$team_id=0){
		$title = Title::makeTitle( NS_SPECIAL , "FanHome"  );
		return $title->escapeFullURL("sport_id=" . $sport_id . "&team_id=".$team_id);
	}
	static function getFanUpdatesURL($sport_id,$team_id=0){
		$title = Title::makeTitle( NS_SPECIAL , "FanUpdates"  );
		return $title->escapeFullURL("sport_id=" . $sport_id . "&team_id=".$team_id);
	}
}
	
?>