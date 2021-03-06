<?php
/**
 *
 */
class UserProfile {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	 /**#@+
	 * @private
	 */
	var $user_id;           	# Text form (spaces not underscores) of the main part
	var $user_name;			# Text form (spaces not underscores) of the main part
	var $profile;           	# Text form (spaces not underscores) of the main part
	
	var $profile_fields_count;
	
	var $profile_fields = array(
					"real_name","location_city","hometown_city","birthday",
					"about","places_lived","websites","occupation","schools",
					"movies","tv","books","magazines","video_games","snacks","drinks",
					"custom_1","custom_2","custom_3","custom_4","email"
					);
	var $profile_missing = array();
	
	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct($username) {
		$title1 = Title::newFromDBkey($username  );
		$this->user_name = $title1->getText();
		$this->user_id = User::idFromName($this->user_name);
		
	}
	
	public function getProfile(){
		global $wgMemc;
		
		//try cache first
		$key = wfMemcKey( 'user', 'profile', 'info', $this->user_id );
		//$wgMemc->delete( $key );
		$data = $wgMemc->get( $key );
		if ( $data ) {
			wfDebug( "Got user profile info for {$this->user_name} from cache\n" );
			$profile = $data;
		}else{
			wfDebug( "Got user profile info for {$this->user_name} from db\n" );
			$dbr =& wfGetDB( DB_SLAVE );
			$params['LIMIT'] = "5";
			$row = $dbr->selectRow( 'user_profile', 
				"*", 
				array( 'up_user_id' => $this->user_id ), __METHOD__,
				$params
				);
		
			if($row){
				$profile["user_id"]= $this->user_id;
				$profile["location_city"]= $row->up_location_city;	
				$profile["location_state"]= $row->up_location_state;	
				$profile["location_country"]= $row->up_location_country;
				$profile["hometown_city"]= $row->up_hometown_city;	
				$profile["hometown_state"]= $row->up_hometown_state;	
				$profile["hometown_country"]= $row->up_hometown_country;
				$profile["birthday"]= $this->formatBirthday($row->up_birthday);	
				
				$profile["about"]= $row->up_about;
				$profile["places_lived"]= $row->up_places_lived;
				$profile["websites"]= $row->up_websites;
				$profile["relationship"]= $row->up_relationship;
				$profile["occupation"]= $row->up_occupation;
				$profile["schools"]= $row->up_schools;
				$profile["movies"]= $row->up_movies;
				$profile["music"]= $row->up_music;
				$profile["tv"]= $row->up_tv;
				$profile["books"]= $row->up_books;
				$profile["magazines"]= $row->up_magazines;
				$profile["video_games"]= $row->up_video_games;
				$profile["snacks"]= $row->up_snacks;
				$profile["drinks"]= $row->up_drinks;
				$profile["custom_1"]= $row->up_custom_1;
				$profile["custom_2"]= $row->up_custom_2;
				$profile["custom_3"]= $row->up_custom_3;
				$profile["custom_4"]= $row->up_custom_4;
				$profile["custom_5"]= $row->up_custom_5;
				$profile["user_page_type"] = $row->up_type;
				$wgMemc->set($key, $profile);
			}else{
				$profile["user_page_type"] = 1;
			}
		}

		$user = User::newFromId($this->user_id);
		$user->loadFromId();
		$profile["real_name"]= $user->getRealName();	
		$profile["email"]= $user->getEmail();	
		 
		return $profile;
	}

	function formatBirthday($birthday){
		$dob = explode('-', $birthday);
		if(count($dob) == 3){
			$month = $dob[1];
			$day = $dob[2];
			$birthday_date = date("F jS", mktime(0,0,0,$month,$day));
		}
		return $birthday_date;
	}
	
	public function getProfileComplete(){
		global $wgUser, $wgSitename;
		
		$complete_count = 0;
		
		//check all profile fields
		$profile = $this->getProfile();
		foreach($this->profile_fields as $field){
			if($profile[$field]){
				$complete_count++;	
			}
			$this->profile_fields_count++;
		}
	
		//check if avatar
		$this->profile_fields_count++;
		$avatar = new wAvatar($wgUser->getID(),"l");
		if (strpos($avatar->getAvatarImage(), 'default_') === false)$complete_count++;	
		
		//if ArmchairGM, check if they have a favorite team/sport
		if($wgSitename == "ArmchairGM"){
			$this->profile_fields_count++;
			$favs = SportsTeams::getUserFavorites($wgUser->getID());
			if(count($favs) > 0)$complete_count++;
		}
		
		return round($complete_count / $this->profile_fields_count * 100);
	}
	
	static function getEditProfileNav( $current_nav ){
		$lines = explode( "\n", wfMsgForContent( 'update_profile_nav' ) );
		$output = "<div class=\"profile-tab-bar\">";
		foreach ($lines as $line) {
			
			if (strpos($line, '*') !== 0){
				continue;
			}else{
				$line = explode( '|' , trim($line, '* '), 2 );
				$page = Title::newFromText($line[0]);
				$link_text = $line[1];
				
				$output .= "<div class=\"profile-tab" . (($current_nav==$link_text)?"-on":"") . "\"><a href=\"" . $page->escapeFullURL() . "\">{$link_text}</a></div>";
			}
		}
		$output .= "<div class=\"cleared\"></div></div>";
		
		return $output;
	}

}
	


?>