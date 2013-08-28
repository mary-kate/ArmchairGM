<?php

$wgExtensionFunctions[] = 'wfSpecialFanHome';
$wgExtensionFunctions[] = 'wfSportsTeamsReadLang';


function wfSpecialFanHome(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");

	class FanHome extends SpecialPage {
	
		function FanHome(){
			UnlistedSpecialPage::UnlistedSpecialPage("FanHome");
		}
		 
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
			
			$this->friends = $this->getRelationships(1);
			$this->foes = $this->getRelationships(2);
			$this->relationships = array_merge($this->friends,$this->foes);
			
			$sport_id = $wgRequest->getVal("sport_id");
			$team_id = $wgRequest->getVal("team_id");
			
			if(!$sport_id && !$team_id){
				$wgOut->setPagetitle( wfMsgForContent( 'st_network_woops_title' ) );
				$out .= "<div class=\"relationship-request-message\">" . wfMsgForContent( 'st_network_woops_text' ) . "</div>";
				$out .= "<div class=\"relationship-request-buttons\">";
				$out .= "<input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_main_page' ) ."\" onclick=\"window.location='" . Title::makeTitle(NS_MAIN, "Main_Page")->escapeFullUrl() ."'\"/>";
				if ($wgUser->isLoggedIn()) {
					$out .= " <input type=\"button\" class=\"site-button\" value=\"" . wfMsgForContent( 'st_network_your_profile' ) . "\" onclick=\"window.location='" . Title::makeTitle(NS_USER, $wgUser->getName())->escapeFullUrl() ."'\"/>";
				}
			  	$out .= "</div>";
			  	$wgOut->addHTML($out);
				return true;			
			}
			
			$this->network_count = SportsTeams::getUserCount($sport_id,$team_id);
			$this->friends_network_count = SportsTeams::getFriendsCountInFavorite($wgUser->getID(),$sport_id,$team_id);
			
			if($team_id){
				$team = SportsTeams::getTeam($team_id);
				$this->network = $team["name"];
			}else{
				$sport = SportsTeams::getSport($sport_id);
				$this->network = $sport["name"];
			}
			
			$team_image =  SportsTeams::getLogo($sport_id,$team_id,"l");
			
			$homepage_title = Title::makeTitle( NS_MAIN  ,$this->network );
			$view_fans_title = Title::makeTitle( NS_SPECIAL  , "ViewFans" );
			$join_fans_title = Title::makeTitle( NS_SPECIAL  , "AddFan" );
			$leave_fans_title = Title::makeTitle( NS_SPECIAL  , "RemoveFan" );
			$wgOut->setPagetitle( "{$this->network} " . wfMsgForContent( 'st_network_fan_network' ) );


			$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/SportsTeams/SportsTeams.css?{$wgStyleVersion}\"/>\n");
			$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/SportsTeams/fanhome.js\"></script>\n");

			// Ashish Datta
			// Add the script for the maps and set the onload() handler
			// DONT FORGET TO CHANGE KEY WHEN YOU CHANGE DOMAINS
			$wgOut->addScript("<script src=\"http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=ABQIAAAAHOCE3e-8iiz5WA81vnPUqRTclLgz7ue5Q1mPcDsF57eIHHh18BRQv5iJ2WTVkDF7iDq1qckRJ52FDQ\" type=\"text/javascript\"></script>");
			$wgOut->addScript($this->getMap());
			$wgOut->setOnLoadHandler('loadMap()');
			//
						
			if(SportsTeams::isFan($wgUser->getID(),$sport_id,$team_id)){
				$fan_info = "<p><span class=\"profile-on\">" . wfMsgForContent( 'st_network_you_are_fan' ) ."</span></p>";
				$fan_info .= "<p><span ><a href=\"". $leave_fans_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}") ."\" style=\"text-decoration:none;\">" . wfMsgForContent( 'st_network_leave_network' ) ."</a></span></p>";
			} else if ($wgUser->isLoggedIn()) {
				$fan_info = "<p><span class=\"profile-on\"><a href=\"" . $join_fans_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}") ."\" style=\"text-decoration:none;\">" . wfMsgForContent( 'st_network_join_network' ) ."</a></span></p>";
			}
			$output = ""; 
				
				$output .= "<div class=\"fan-top\">";
					
					$output .= "<div class=\"fan-top-left\">";
						$output .= "<h1>" . wfMsgForContent( 'st_network_info' ) ."</h1>";       
						$output .= "<div class=\"network-info-left\">";
							$output .= $team_image;
							$output .= "<p>" . wfMsgForContent( 'st_network_logo' ) ."</p>";
						$output .= "</div>";
						$output .= "<div class=\"network-info-right\">";
						$output .= "<p>" . wfMsgForContent( 'st_network_fans_col' ) ." <a href=\"" .$view_fans_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}") ."\">{$this->network_count}</a></p>";
							if($wgUser->isLoggedIn())$output .= "<p>" . wfMsgForContent( 'st_network_friends_col' ) ." {$this->friends_network_count}</p>";
							$output .= $fan_info;
						$output .= "</div>";
						$output .= "<div class=\"cleared\"></div>";
					$output .= "</div>";
					$this_count = count(SportsTeams::getUsersByFavorite($sport_id,$team_id,7,0));
					$output .= "<div class=\"fan-top-right\">";
						$output .= "<h1>{$this->network} " . wfMsgForContent( 'st_network_fans' ) ."</h1>";
						//$output .= "<p style=\"margin:-8px 0px 0px 0px;color:#797979;\">" . wfMsgForContent( 'st_network_fan_display', $this_count, "<a href=\"" . $view_fans_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}") ."\">{$this->network_count}</a>", (($this->network_count>1 || $this->network_count==0)?"s":"")) . "</p>";
						$output .= "<p style=\"margin:-8px 0px 0px 0px;color:#797979;\">" . wfMsgExt( 'st_network_fan_display', 'parsemag', $this_count, $view_fans_title->getFullURL("sport_id={$sport_id}&team_id={$team_id}"), $this->network_count) . "</p>";
						$output .= $this->getFans();
					$output .= "</div>";
					
				$output .= "<div class=\"cleared\"></div>";
				$output .= "</div>";
				
				$output .= "<div class=\"fan-left\">";
				
					//Latest Network User Updates
					$updates_show = 25;
					$s = new UserStatus();
					$output .= "<div class=\"network-updates\">";
						$output .= "<h1 class=\"network-page-title\">" . wfMsgForContent( 'st_network_latest_thoughts' ) ."</h1>";
						$output .= "<div style=\"margin-bottom:10px\">
						<a href=\"". SportsTeams::getFanUpdatesURL($sport_id,$team_id) . "\">" . wfMsgForContent( 'st_network_all_thoughts' ) ."</a>
					</div>";
					if( $wgUser->isLoggedIn() ){
						//**script**//
						$output .= "<script type=\"text/javascript\"> var __sport_id__={$sport_id}; var __team_id__={$team_id}; var __updates_show__={$updates_show}; var __user_status_link__ = '" . Title::makeTitle( NS_SPECIAL  , "UserStatus" )->getFullUrl() . "'</script>"; 
						$output .= "<div class=\"user-status-form\">
							<span class=\"user-name-top\">{$wgUser->getName()}</span> <input type=\"text\" name=\"user_status_text\" id=\"user_status_text\" size=\"40\"  onKeyPress=\"detEnter(event)\" maxlength=\"150\" /> 
							<input type=\"button\" value=\"add\" class=\"site-button\" onclick=\"add_status()\"/>
						</div>";
					}
						$output .= "<div id=\"network-updates\">";
							$output .= $s->displayStatusMessages(0,$sport_id,$team_id,$updates_show,$page);
						$output .= "</div>";
				
					$output .= "</div></div>";
				
					$output .= "<div class=\"fan-right\">";
						
						// NETWORK LOCATION MAP
						$output .= "<div class=\"fan-map\">";
							$output .= "<h1 class=\"network-page-title\">" . wfMsgForContent( 'st_network_fan_locations' ) ."</h1>";
							$output .= "<div class=\"gMap\" id=\"gMap\"></div>
								<div class=\"gMapInfo\" id=\"gMapInfo\"></div>";
						$output .= "</div>";
						
						//TOP NETWORK FANS
						$output .= "<div class=\"top-fans\">";
							$output .= "<h1 class=\"network-page-title\">" . wfMsgForContent( 'st_network_top_fans' ) ."</h1>";
								$output .= "<p class=\"fan-network-sub-text\">
									<a href=\"index.php?title=Special:TopFansRecent&period=weekly\">" . wfMsgForContent( 'st_network_top_fans_week' ) ."</a> - 
									<a href=\"{$view_fans_title->getFullURL()}&sport_id={$sport_id}&team_id={$team_id}\">" . wfMsgForContent( 'st_network_complete_list' ) ."</a>
								</p>";
							$output .= $this->getTopFans();
						$output .= "</div>";
						
						

						$output .= "<div class=\"network-articles\">";
							$output .= "<h1 class=\"network-page-title\">{$this->network} " . wfMsgForContent( 'st_network_articles' ) ."</h1>";
								$output .= "<p class=\"fan-network-sub-text\">
									<a href=\"index.php?title=Create_Opinion\">" . wfMsgForContent( 'st_network_write_article' ) ."</a> -
									<a href=\"{$homepage_title->getFullURL()}\">" . wfMsgForContent( 'st_network_main_page' ) ."</a> 
								</p>";
							$output .= $this->getArticles();
						$output .= "</div>";
				
				$output .= "</div>";
				$output .= "<div class=\"cleared\"></div>";
				
			$wgOut->addHTML($output);		
	 		
		}
		
		/* 'Ashish Datta
		   GMaps code 
		   TODO:
		   - The team images need to be cleaned up.
		   - The team logos need some shadows.
		   - The Google Maps Geocoder produces weird results sometimes: 
		   	ie: New York, California geocodes to somewhere in CA instead of failing. 
		   */
		   function getMap(){
			   global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			   
			   $sport_id = $wgRequest->getVal("sport_id");
			   $team_id = $wgRequest->getVal("team_id");
			   
			   // maybe error check this to make sure the file exists...
			   if($team_id){
				   $team_image = "images/team_logos/" . SportsTeams::getTeamLogo($team_id,"l");
			   }else{
				   $team_image = "images/sport_logos/" . SportsTeams::getSportLogo($sport_id,"l");
			   }
			   
			   $userIDs = array();	// stores the userIDs for this network
			   $fanLocations = array();	// stores the locations on the map
			   $fanStates = array();	// stores the states along with the fans from that state
			   
			   $markerCode = '';
			   
			   $output = '';   
			
			   $fans = SportsTeams::getUsersByFavorite($sport_id,$team_id,7,0);
			   
			   // go through all the fans for this network
			   // grab their userIDs and save HTML for their mini-profiles
			   
			   foreach($fans as $fan){
				   
				   $fanInfo = array();
				   
				   $user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				   $avatar = new wAvatar( $fan["user_id"],"l");
				   $avatar_img = '<img src="images/avatars/' . $avatar->getAvatarImage() . '" alt="avatar" />';
				
				   $out = "<p class=\"map-avatar-image\"> <a href=\"{$user->getFullURL()}\">{$avatar_img}</a></p> <p class=\"map-avatar-info\"> <a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a>";
				
				   $fanInfo["divHTML"] = $out;
				   $fanInfo["URL"] = $user->getFullURL();
				   $fanInfo["user_name"] = $fan["user_name"];
				   
				   $userIDs[$fan["user_id"]] = $fanInfo;
				   
			   }
			   
			   // get the location info about this networks fans
			   $idList = implode(",", array_keys($userIDs) );
			   $idList = "(" . $idList . ")";
			   
			   // get the info about the fans. only select fans that have country info
			   $dbr =& wfGetDB( DB_MASTER );
			   $sql = "SELECT up_user_id, up_location_country, up_location_city, up_location_state FROM 
			   	   user_profile WHERE up_user_id in " . $idList . " AND up_location_country IS NOT NULL;";
			   
			   $res = $dbr->query($sql);
			   
			   while ($row = $dbr->fetchObject( $res ) ) {
			   
				   $topLoc = "";
				   $loc = "";
				   
				   $userInfo = array();
				   $userInfo["user_id"] = $row->up_user_id;
				   $userInfo["user_name"] = $userIDs[$row->up_user_id]["user_name"];
			
				   // case everything nicely
				   $country = ucwords(strtolower( $row->up_location_country ));
				   $state = ucwords(strtolower( $row->up_location_state ));
				   $city = ucwords(strtolower( $row->up_location_city ));
				   
				   // if the fan is in the US geocode by city, state
				   if($country == "United States"){
					   // if the users profile doesnt have a city only use a state
					   if( strlen($city) > 0 && strlen($state) > 0 ){
						   $loc = $city . ", " . $state;
						   $topLoc = $state;
					   }elseif( strlen($state) > 0 ){
						   $loc = $state;
						   $topLoc = $state;
					   }else{
						   $loc = $country;
						   $topLoc = $country;
					   }
				   }else{	// if they are non-US then geocode by city, country
					   if( strlen($city) > 0 && strlen($country) > 0 ){
						$loc = $city . ", " . $country;
						$topLoc = $country;
					   }else{
						$loc = $country;
						$topLoc = $country;
					   }
				   }
				   
				   // build a hashtable using higher locations as keys and arrays of fans as objects
				   if( !array_key_exists($topLoc, $fanStates) ){
					   $fanStates[$topLoc] = array();
					   $fanStates[$topLoc][] = $userInfo;
				   }else{
					$fanStates[$topLoc][] = $userInfo;   
				   }
				   
				   // htmlentities( $userIDs[$row->up_user_id]["divHTML"] )
				   // javascript to place the marker
				   $markerCode .= "geocoder.getLatLng( '" . $loc . "', 
								   function(point) {
									   if (!point) {
										   geocoder.getLatLng( '" . $state . "',
											   function(point){
												   var nPoint = new GPoint( point.x + (Math.random() * .12), point.y + (Math.random() * .12) );
												   var gMark = createMarker(nPoint, \"" . addslashes( $userIDs[$row->up_user_id]["divHTML"] ) . "<br />" . $loc . "</p>\" ,'" . $userIDs[$row->up_user_id]["URL"] . "' , map);
												   mgr.addMarker(gMark, 6);
											   } );
									   }else{ 
						  ";
				   
				   // this is the first fan at $loc
				   if( !in_array($loc, $fanLocations) ){
					   $fanLocations[] = $loc;
				   }else{
					   // there is allready a placemark at $loc so add some jitter
					   $markerCode .= "var point = new GPoint( point.x + (Math.random() * .1), point.y + (Math.random() * .1) );";
				   }
				   
				   $markerCode .= "var gMark = createMarker(point, \"" . addslashes( $userIDs[$row->up_user_id]['divHTML'] ) . "<br />" . $loc . "</p>\" ,'" . $userIDs[$row->up_user_id]["URL"] . "' , map);
						   mgr.addMarker(gMark, 6);
						   }} );	";
				   
			   }
			   
			   // helper function to compare the $fanStates objects
			   function cmpFanStates($a, $b){
				   if($a["user_id"] < $b["user_id"]){
					   return 1;
				   }else{
					   return -1;
				   }
			   }
			   
			   // at the state level markers include the 5 newest users
			   foreach($fanStates as $state => $users){
				   usort($users, "cmpFanStates");
				   
				   $userList = "";
				   
				   for($i = 0; $i < ( count($users) < 5 ? count($users) : 5 ); $i++){
					   $userList .= $users[$i]["user_name"] . "<br />";
				   }
				   
				   $markerCode .= "geocoder.getLatLng( '" . $state . "' , 
					   			function(point) {
									if(point)
										mgr.addMarker( 
									
									createTopMarker(point, '<div id=\"gMapStateInfo\" class=\"gMapStateInfo\"> <div class=\"fan-location-blurb-title\">" . wfMsgForContent( 'st_network_newest' ) . " " . $state . "</div><div class=\"user-list\">" . $userList . "<div><div style=\"font-size:10px; color:#797979;\">" . wfMsgForContent( 'st_network_clicktozoom' ) . "</div></div>', map), 1, 5);
								   }	);";
			   }
			   
			   //**** script ****//
			   $output .= "	<script language = \"javascript\"> var __team_image__ = \"{$team_image}\";


// loads everything onto the map
function loadMap() {
	if (GBrowserIsCompatible()) {
		var geocoder = new GClientGeocoder();
		var map = new GMap2( document.getElementById(\"gMap\") );		
		
		// make sure to clean things up
		window.onunload = GUnload;
		
		geocoder.setBaseCountryCode(\"US\");
		
		map.setCenter(new GLatLng(37.0625,-95.677068), 3);	
		map.addControl(new GSmallZoomControl());
		var mgr = new GMarkerManager(map);
		
		" . $markerCode . "
		
		mgr.refresh();
	}
}

</script>";

	return $output;	     
}

// 



		function getArticles(){
			global $IP;
  			require_once("$IP/extensions/wikia/ListPages/ListPagesClass.php");
			
			$list = new ListPages();
			$list->setCategory("{$this->network} Opinions,{$this->network} News");
			$list->setShowCount(6);
			$list->setBool("ShowCtg","NO");
			$list->setOrder("New");
			$list->setBool("ShowVoteBox","YES");
			$list->setBool("ShowDate","NO");
			$list->setBool("ShowStats","NO");
			$list->setBool("ShowNav","YES");
			$list->setBool("ShowPic", "NO");
			return $list->DisplayList();
		}

		
		function getRelationships($rel_type){
			global $wgUser, $IP;
			require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
			$rel = new UserRelationship($wgUser->getName());
			$relationships = $rel->getRelationshipIDs($rel_type);
			return $relationships;		
		}
		
		function getTopFans(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$sport_id = $wgRequest->getVal("sport_id");
			$team_id = $wgRequest->getVal("team_id");
			
			
			$output = "<div class=\"top-fans\">";
			$fans = SportsTeams::getUsersByPoints($sport_id,$team_id,15,0);
			$x = 1;
			foreach($fans as $fan){
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				$user_name = $fan["user_name"];
				$user_name_short = ($user_name == substr($user_name, 0, 12) ) ? 
									 $user_name : ( substr($user_name, 0, 12) . "...");
				$avatar = new wAvatar( $fan["user_id"],"m");
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' />";
				$output .= "<div class=\"top-fan-row\">
						<span class=\"top-fan-num\">{$x}.</span> <span class=\"top-fan\"><img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='' border=''> <a href='" . $user->getFullURL() . "' >" . $user_name_short . "</a>
						</span>
						<span class=\"top-fan-points\"><b>" . number_format($fan["points"]) . "</b> " . wfMsgForContent( 'st_network_points' ) . "</span>
					</div>";
				$x++;
			}
			$output .= "</div>";
			return $output;
		}
		
		
		function getFans(){
			global $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$sport_id = $wgRequest->getVal("sport_id");
			$team_id = $wgRequest->getVal("team_id");
			
			$output = "<div class=\"fans\">";
			$fans = SportsTeams::getUsersByFavorite($sport_id,$team_id,7,0);
			foreach($fans as $fan){
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				$avatar = new wAvatar( $fan["user_id"],"l");
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' />";
				
				$fan_name = ($fan["user_name"] == substr($fan["user_name"], 0, 12) ) ? 
									 $fan["user_name"] : ( substr($fan["user_name"], 0, 12) . "...");
				
				
				$output .= "<p class=\"fan\">
					      	<a href=\"{$user->getFullURL()}\">{$avatar_img}</a><br>
					      	<a href=\"{$user->getFullURL()}\">{$fan_name}</a>
					      </p>";
						
			}
			$output .= "<div class=\"cleared\"></div></div>";
			
			return $output;
		}

		
	}

	SpecialPage::addPage( new FanHome );
}

?>