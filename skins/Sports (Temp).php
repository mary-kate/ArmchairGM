<?php
/**
 * See skin.txt
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die();
	
/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */  

 function get_dates_from_elapsed_days($number_of_days){
	$dates[date("F j, Y", time() )] = 1; //gets today's date string
	for($x=1;$x<=$number_of_days;$x++){
		$time_ago = time() - (60 * 60 * 24 * $x);
		$date_string = date("F j, Y", $time_ago);
		$dates[$date_string] = 1;
	}
	return $dates;
}  

global $IP;
require_once("$IP/skins/MagazineShell.php");
require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");


class SkinSports extends SkinMagazineShell {
	
  var $featured_users = array(
	  			"159661"=>"SoccerBalls4Me",
				"159653"=>"Annadrome",
				"143045"=>"ManningFan",
				"159617"=>"Mister Nice",
				"159651"=>"Seven Kinds of Smoke",
				"159351"=>"Marry Me Jeter",
				"93500"=>"OK Felb OR",
				"159655"=>"Baller48321"
				);
  
  #set stylesheet
  function getStylesheet() {
    return "common/Sports.css";
  }
  
  #set skinname
  function getSkinName() {
    return "Sports";
  }
  
  	/**
	 * This gets called shortly before the \</body\> tag.
	 * @return String HTML-wrapped JS code to be put before \</body\> 
	 */
	function bottomScripts() {
		global $wgJsMimeType;
		$r = "\n\t\t<script type=\"$wgJsMimeType\">if (window.runOnloadHook) runOnloadHook();</script>\n";
			
		$r .= '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
			<script src="/skins/common.js" type="text/javascript"></script>';
		$r .= '<script type="text/javascript">' . "\n";
		$r .= '_uacct = "UA-1328449-1";' . "\n";
		$r .= 'urchinTracker();' . "\n";
		
	
		$r .= '</script>' . "\n";
		$r .= "<!-- FM Tracking Pixel -->\n<script type='text/javascript' src='http://static.fmpub.net/site/ArmchairGM'></script>\n<!-- FM Tracking Pixel -->\n";
		
		return $r;
		 
	}
	
  #main page before wiki content
  function doBeforeContent() {
	
  ##global variables
  global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP, $wgMemc, $wgUploadPath;	
  
  
  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");  
  $tns=$wgTitle->getNamespace();
  
  $s = '';
  $s .= '<div id="container">';
        
		$s .= "<div id=\"topad\">

			<!-- FM Leaderboard Zone -->\n
			<script type='text/javascript'>\n
			var federated_media_section = '';\n
			</script>\n
			<script type='text/javascript' src='http://static.fmpub.net/zone/
			791'></script>\n
			<!-- FM Leaderboard Zone -->\n

		</div>";
	
	$s .= "\n<style>	
	.grey {
		color:#797979;
		font-size:12px;
		font-weight:bold;
	}
	
	.commentmiddle, .replymiddle {
		padding:0px 18px 0px 18px;
	}
	
	.toptabs, .toptabsOn {
		cursor:pointer;
	}
	
	.liststats img, .title img {
		vertical-align:middle;
	}
	
	.listpagesnav {
		margin:10px 0px 0px 0px;
	}
	
	p.relationship-link {
		margin:2px 10px 1px 0px;
	}
	
	.last-comment-line a {
		text-decoration:none;
	}
	
	.user-profile-links a {
		margin:0px 10px 0px 0px;
		text-decoration:none;
	}
	
	.user-profile-links img {
		margin:0px 3px 0px 0px;
		vertical-align:middle;
	}
	
	#categories {
		margin:15px 0px 0px 0px;
	}
	
	.site-button {
		background-color:#78BA5D;
		border:1px solid #6B6B6B;
		color:#ffffff;
		font-size:12px;
		padding:3px;
	}
	
	.email-this-title {
		font-size:14px;
		font-weight:bold;
		color:#333333;
	}
	
	.top-fan {
		margin:0px 0px 2px 0px;
	}
	
	.profile-box-image img {
		border:1px solid #D7DEE8;
	}
	
	.gMapInfo {
		display: none; 
		position:absolute; 
		width:auto; 
		height:auto; 
		z-index: 100; 
		background-color: #F2F4F7;
		border:1px solid #D7DEE8;
		padding:10px;
	}

	.fan-location-blurb-title {
		font-size:14px;
		font-weight:bold;
		color:#333333;
		margin:0px 0px 5px 0px;
	}

	.user-list {
		font-size:12px;
		font-weight:bold;
		color:#376EA6;
		margin:0px 0px 3px 0px;
	}

	.map-avatar-image {
		float:left;
		width:96px;
	}

	.map-avatar-image img {
		border:1px solid #D7DEE8;
	}

	.map-avatar-info {
		float:left;
		font-size:14px;
		font-weight:bold;
	}

	.map-avatar-info a {
		text-decoration:none;
	}
	
	.item-comment {
		float:right;
		font-size:75%;
		width:150px;
		overflow:hidden;
	}
	
	.copyright-warning {
		margin:0px 0px 10px 0px;
		width:700px;
		font-size:11px;
		color:#333333;
	}
	
	.categorytext {
		width:700px;
	}
	
	.wikiwyg_toolbar {
		border-bottom:none;
	}
	
	.search-form {
		float:right;
		margin:4px 0px 0px 0px;
	}

	.search-form ul {
		margin:0px;
		padding:0px;
		list-style:none;
	}

	.search-form li {
		float:left;
		margin:0px 5px 0px 0px;
	}
	
	.sub-menu {
		float:left;
		margin:5px 0px 0px 9px;
		width:750px;
	}
	
	.featured-fan {
		float:left;
		margin:5px 5px 9px 0px;
	}
	
	.profile-box-image {
		border:none;
	}
	
	.mini-gallery-nopics img {
		vertical-align:middle;
	}
	
	</style>\n";
		$s .= "<div class=\"top-bar\">";
    		$s .= "<div class=\"logo\">";
		$s .= "<a href=\"index.php?title=Main_Page\"><img src=\"{$wgUploadPath}/sports/logo.png\" alt=\"armchairgm\" border=\"0\"></a>";
    		$s .= "</div>";	
    		$s .= "<div class=\"tag-line\">";
    			$s .= "all sports, all you";
			//$s .= "pleads guilty too";
    		$s .= "</div>";
    		$s .= "<div class=\"add-friends\">";
    			if ($wgUser->isLoggedIn()) {
    				$s .= "<a href=\"index.php?title=Special:InviteContacts\">+ Add Friends</a>";
    			} else {
    				$s .= "<a href=\"index.php?title=Special:UserRegister\">+ Add Friends</a>";
    			}
    			
    		$s .= "</div>";
    			if ($wgUser->isLoggedIn()) {
					$s .= "<div class=\"other-links\">";
    				$s .= "<a href=\"index.php?title=Special:SiteScout\">Site Scout</a> - <a href=\"index.php?title=Special:Recentchanges\">Recent Changes</a> - <a href=\"index.php?title=Special:TopFans\">Top Fans</a> - ";
    				$s .= "<a href=\"index.php?title=Special:Userlogout\">Log Out</a>";
    			} else {
					$s .= "<div class=\"other-links-out\">";
    				$s .= "<span class=\"grey\">You are Not Logged-In</span><br>";
    				$s .= "<a href=\"index.php?title=Special:UserRegister\">Sign Up</a> - <a href=\"index.php?title=Special:Login\">Log In</a>"; 
    			}
    			
    		$s .= "</div>";
    		$s .= "<div class=\"cleared\"></div>";
    		$s .= "<div class=\"nav-tabs\" >";
    	
		
		// MENU DEFINITION
		$menu[] = array(
				"display_name" => "Home",
				"page_name" =>"Main Page"
				);
		
		$menu[] = array(
				"display_name" => "Profile",
				"page_name" => "User:{$wgUser->getName()}",
				"subpages" =>
				array("Your Profile" => "User:{$wgUser->getName()}","Friends' Activities" => "Special:UserHome","User Wiki" => "UserWiki:{$wgUser->getName()}", "Watchlist" => "Special:Watchlist")
				);
				
		$menu[] = array(
				"display_name" => "Sports",
				"page_name" => "",
				"subpages" =>
					 array("MLB" => "MLB","NFL" => "NFL","NBA" => "NBA","NHL" => "NHL",
						 "College Basketball"  => "College Basketball","College Football" => "College Football",
						 "Soccer" => "Soccer","Nascar"  => "Nascar","Other"  => "Other")
				);
		
		$menu[] = array(
				"display_name" => "Write",
				"page_name" => "Create Article",
				"subpages" =>
					 array("Article" => "Create Article","Locker Room Discussion"=> "Create Locker Room Discussion","Game Recap" => "Create Game Recap",
						 "Dictionary Entry" => "Create Dictionary",
						 "Add a Blog" => "Add Blog","Movie Summary" => "Create Movie Summary","Book Summary" => "Create Book Summary")
		);
				
		$menu[] = array(
				"display_name" => "Articles",
				"page_name" => "Special:ArticlesHome",
				"subpages" =>
					 array("All Articles" => "Special:ArticlesHome","New Articles" => "Special:ArticlesHome/New", "Today's Articles" => "Category:".date("F j, Y"))
		);		
				
				
		$menu[] = array(
				"display_name" => "Encyclopedia",
					"page_name" => "Encyclopedia" 
				);
						
		$menu[] = array(
				"display_name" => "Ratings",
				"page_name" => "Ratings"
		);
		
						
		$menu[] = array(
				"display_name" => "Locker Room",
				"page_name" => "Category:Lockerroom"
		);
		
		//$menu[] = array(
		//		"display_name" => "Challenges",
		//		"page_name" => "Special:ChallengeHistory"
		//);
		
		$menu[] = array(
				"display_name" => "Meet People",
				"page_name" => "Special:SimilarFans"
		);
		
		$menu[] = array(
				"display_name" => "Fun",
				"page_name" => "",			
				"subpages" =>
					 array("Picture Game" => "Special:PictureGameHome","Polls" => "Special:RandomPoll" )
		);
			
		$menu[] = array(
				"display_name" => "Explore",
				"page_name" => "Special:Random"
		);
				

		$current_page = $wgTitle->getPrefixedText();
		$parts = explode( '/', $_SERVER['QUERY_STRING'] );
		if( count( $parts ) > 1 )$current_page.="/" . $parts[ count( $parts ) - 1 ];
		
		//Main Menu Bar
		$tab_count = 1;
		foreach($menu as $menu_item){
			//If you are on this page, or any of its subpages, we set the class
			//ugly ugly hack to accomodate large names
			if(strlen($menu_item["display_name"]) <= 8){
				$class_base = "small";
			}else{
				$class_base = "large";
			}
			if($current_page==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($current_page,$menu_item["subpages"])) ){
				$menu_class = "{$class_base}-tab-on";
			}else{
				$menu_class = "{$class_base}-tab";
			}
			//If No page_name is passed, the link needs to load the submenu via JS
			//otherwise, we contruct the MW page_title to get the proper URL
			if(!$menu_item["page_name"]){
				$menu_link = "javascript:void(0);\" onclick=\"javascript:submenu({$tab_count});";
			}else{
				$page_title = Title::makeTitle( NS_MAIN  , $menu_item["page_name"]  );
				$menu_link = $page_title->getFullURL();
			}
			
			if (!( (($menu_item["display_name"] == "Profile") || ($menu_item["display_name"] == "Locker Room")) && !($wgUser->isLoggedIn()))) {
				$s .= "<p class=\"{$menu_class}\" id=\"menu-{$tab_count}\"><a href=\"{$menu_link}\">{$menu_item["display_name"]}</a></p>";
			}
			$tab_count++;
		}
		
		$s .= "<div class=\"cleared\"></div>";
    		$s .= "</div>";
    		$s .= "<div class=\"green-bar\">";
	
		//Sub Menu Bar
		$tab_count = 1;
		foreach($menu as $menu_item){
			if($menu_item["subpages"]){
				
				//If you are on this page, or any of its subpages, the submenu should be visible on load
				if($current_page==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($current_page,$menu_item["subpages"])) ){
					$menu_class = "display:block;";
					$s .= "<script>last_clicked={$tab_count};</script>";
				}else{
					$menu_class = "display:none";
				}
			
				$s .= "<div class=\"sub-menu\" style=\"{$menu_class}\" id=\"submenu-{$tab_count}\">";
				
				//Output each subpage link
				$x = 1;
				foreach($menu_item["subpages"] as $subpage_display_name => $subpage_page_name){
					
					if($current_page==$subpage_page_name){
						$sub_menu_class = "sub-menu-on";
					}else{
						$sub_menu_class = "";
					}
					
					//construct sub menu link
					$page_title = Title::makeTitle( NS_MAIN  , $subpage_page_name  );
					$menu_link = $page_title->getFullURL();
					
					if($x > 1)$s .= " - ";
					$s .= "<span class=\"{$sub_menu_class}\"><a href=\"{$menu_link}\">{$subpage_display_name}</a></span>";
					$x++;
				}
				$s .= "</div>";
			}
			$tab_count++;
		}
			    
            		$s .= $this->searchForm();
          		$s .= "</div>";
    $s .= "</div>";
    
    $s .= "<div class=\"main\">";
		if (   ($wgOut->getPageTitle() !== 'Main Page') && (NS_SPECIAL !== $wgTitle->getNamespace()) && (NS_USER !== $wgTitle->getNamespace()) && (NS_USER_TALK !== $wgTitle->getNamespace()) && (300 !== $wgTitle->getNamespace()) && ($wgTitle->getPrefixedText() !== "UserWiki:{$wgTitle->getText()}")) {
	  	$s .= '<div id="side">';
	 
	  			$s .= $this->userBox();
				if ($wgOut->getPageTitle() !== "The 100 Greatest Quarterbacks of the Modern Era") {
		  			$s .= $this->recentEditors();
		  			$s .= $this->recentVoters();
				}
	
	  			//$s .= $this->thisArticle();
	  
		  			$s .= "<div id=\"sideads\">\n
						
							<!-- FM Skyscraper Zone -->\n
							<script type='text/javascript'>\n
							var federated_media_section = '';\n
							</script>\n
							<script type='text/javascript' src='http://static.fmpub.net/zone/
							792'></script>\n
							<!-- FM Skyscraper Zone -->\n
							
					</div>";
	  
		$s .= '</div>'; #end side
		$s .= '<div id="main-body">';
			
				$s .= "<div class=\"edit-menu\">";
	   				$s .= "<div class=\"edit-button\" >";
						
						if ( ! $wgOut->isArticleRelated() ) {
							$s .= "<img src=\"{$wgUploadPath}/common/editIcon.gif\"/>";
							$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
							$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
						} else {
							if ( $wgTitle->userCan( 'edit' ) ) {
								$s .= "<img src=\"{$wgUploadPath}/common/editIcon.gif\"/>";
								$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Edit</a>";
								$s .= "<span style=\"margin:0px 0px 0px 29px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
							} else {
								$s .= "<img src=\"{$wgUploadPath}/common/editIcon.gif\"/>";
								$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
								$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
							}
						}
							
					$s .= "</div>";
					$s .= "<div class=\"edit-sub-menu\" id=\"edit-sub-menu-id\" style=\"display:none;\">";
						$s .= '<p><img src="'.$wgUploadPath.'/common/pagehistoryIcon.png" alt="page history icon" border="0"/> ' . $this->historyLink() . '</p>';
				   		if ( $wgTitle->userCanMove() ) {
					 		$s .= '<p><img src="'.$wgUploadPath.'/common/moveIcon.png" alt="move icon" border="0"/> ' . $this->moveThisPage() . '</p>';
				       	}
				       	$s .= '<p><img src="images/common/whatlinkshereIcon.png" alt="what links here icon" border="0"/> ' . $this->whatLinksHere() . '</p>';
				       	if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists() ) {
					 		$s .= '<p><img src="'.$wgUploadPath.'/common/protectIcon.png" alt="protect icon" border="0"/> ' . $this->protectThisPage() . '</p>';
				       	}
				       	if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists()) {
					 		$s .= '<p><img src="'.$wgUploadPath.'/common/deleteIcon.png" alt="delete icon" border="0"/> ' . $this->deleteThisPage() . '</p>';
				       	}
				       	if ( $wgUser->isLoggedIn() ) {
					   		$s .= '<p><img src="'.$wgUploadPath.'/common/addtowatchlistIcon.png" alt="watchlist" border="0"/> ' . $this->watchThisPage() . '</p>';
					 		$s .= '<p><img src="'.$wgUploadPath.'/common/uploadIcon.png" alt="upload" border="0"/> ' . $this->specialLink("upload") . '</p>';
				       }
	   				$s .= "</div>";
				$s .= "</div>";
	   			$s .= $this->pageTitle();
	   		
	   		if ($this->subPageSubtitle()) {
	    		 $s .= '<p class="sub-title">'.$wgOut->getSubtitle().$this->subPageSubtitle().'</p>';
	   		}
	   	
		} else if ($wgOut->getPageTitle() !== 'Main Page') {
	   		if (($tns == NS_USER)) {
  	
  				//variables and other crap
  				$page_title = $wgTitle->getText();
	    		$title_parts = explode("/",$page_title);
	    		$user = $title_parts[0];
  				$id=User::idFromName($user);
	    		$relationship = UserRelationship::getUserRelationshipByID($id,$wgUser->getID());
  				$avatar = new wAvatar($id,"l");
  		
  				//laying stuff out
  				$s .= '<div class="user-left">';
  			
  				//left profile box
  				$stats = new UserStats($id,$wgTitle->getText());
				$stats_data = $stats->getUserStats();
				$user_level = new UserLevel($stats_data["points"]);
				$level_link = Title::makeTitle(NS_HELP,"User Levels");
				require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
			
				//try cache first
				$key = wfMemcKey( 'user', 'profile', $user );
				$wgMemc->delete( $key );
				$data = $wgMemc->get( $key );
				if($data){
					wfDebug( "Got  user profile info for {$user} from cache\n" );
					$profile_data = $data;
				}else {
					$profile = new UserProfile($user);
					$profile_data = $profile->getProfile();
					$wgMemc->set( $key, $profile_data );
				}
			
  				$s .= "<div class=\"user-profile-box\">";
					if ( $wgUser->getName() == $wgTitle->getText()  ) {
  						$s .= "<h1 class=\"user-profile-box-title\">Your Profile</h1>";
					} else {
						$s .= "<h1 class=\"user-profile-box-title\">{$wgTitle->getText()}'s Profile</h1>";
					}
				
					$s .= "<div class=\"user-profile-avatar-bar\">";
  						$s .= "<div class=\"user-profile-avatar\">";
							$s .= "<img src=\"images/avatars/" . $avatar->getAvatarImage() . "\"/>";

							if ( $wgUser->getName() == $wgTitle->getText()  ) {
								$s .= '<p>';
								if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
									$s .= '<a href=index.php?title=Special:UploadAvatar>(upload image)</a>';
								} else {
									$s .= '<a href=index.php?title=Special:UploadAvatar>(new image)</a>';
								}
						 		$s .= '</p>';
							}

							$s .= "</div>";
							$s .= "<div class=\"user-profile-level\">";
							$s .= "<p><b>{$profile_data["real_name"]}</b>";

							if ($profile_data["location_city"]) {
									$location = $profile_data["location_city"] . ", " . $profile_data["location_state"];
									if($profile_data["location_country"]!="United States"){
										$location .= $profile_data["location_country"];
									}
									$s .= "<br>{$location}";
								}

							$s .= "</p>";

							$s .= "<p class=\"profile-box-points\">
								<span class=\"profile-on\"> +{$stats_data["points"]}</span> points 
								<a href=\"{$level_link->getFullURL()}\">({$user_level->getLevelName()})</a>
							</p>";
						$s .= "<p class=\"profile-box-advance\">
							Needs <i>{$user_level->getPointsNeededToAdvance()}</i> points. to
							advance to <b>{$user_level->getNextLevelName()}</b>
						</p>";
					$s .= "</div>";
					$s .= "<div class=\"cleared\"></div>";
				$s .= "</div>";
				
  				if ( $wgUser->getName() == $wgTitle->getText()  ) {
		    		
					//Completeness Profile
					require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
					$p = new UserProfile($wgUser->getName());
					$complete = $p->getProfileComplete();

					if ($complete != 100) {
						$s .= "<div class=\"profile-completeness\">";
							$s .= "<h1>Profile Completeness</h1>";
							$s .= "<div class=\"profile-complete-bar-container\">";
								$s .= "<div style=\"background-color:#89C46F;width:".($complete*2-20)."px; height:14px;\">&nbsp;</div>";
							$s .= "</div>";
							$s .= "<div class=\"profile-complete-bar-number\">";
								$s .=  "<a href=\"index.php?title=Special:UpdateProfile\">" . $complete . "% (edit)</a>";
							$s .= "</div>";
							$s .= '<div class="cleared"></div>';
						$s .= "</div>";
					}
					
					$s .= '<div class="profile-box-add-friends">';
					$s .= '<h1>Add Friends</h1>';
		    		$s .= '<p>
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/msnIconSmall.gif" alt="challenge icon" border="0"/> Hotmail
						</a>
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/yahooIconSmall.gif" alt="challenge icon" border="0"/> Yahoo
						</a>
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/gmailIconSmall.gif" alt="challenge icon" border="0"/> Gmail
						</a> 
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/aolIconSmall.gif" alt="challenge icon" border="0"/> AOL
						</a>
					</p>';
					$s .= "</div>";
		    	} else {
					$user_safe = urlencode(   $wgTitle->getText()  );
					$s .= '<div class="profile-box-actions">';
		    			$s .= "<h1>Actions</h1>";
						$s .= "<p>";
							if ($relationship==true) {
		      					if ($relationship == 1) {
									$s .= "<span class=\"profile-on\" style=\"margin:0px 5px 0px -3px;\">
										<b>Your Friend</b>
									</span> ";	    
		      					}
		      					if ($relationship == 2) {
									$s .= "<span class=\"profile-on\" style=\"margin:0px 5px 0px -3px;\">
										<b>Your Foe</b>
									</span> ";      
		      					}
		 					} else {
			    					$s .= "<a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=1\">
											<img src=\"../../images/common/friendRequestIcon.gif\"> Add as Friend
									</a> ";
			    					$s .= "<a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=2\">
										   <img src=\"../../images/common/foeRequestIcon.gif\"> Add as Foe
									</a> ";
			    			}
							$s .= "<a href=\"index.php?title=Special:GiveGift&user={$user_safe}\"><img src=\"../../images/common/icon_package_get.gif\"> Send A Gift</a> ";
		    				//$s .= "<a href=\"index.php?title=Special:ChallengeUser&user={$user_safe}\"><img src=\"../../images/common/challengeIcon.gif\"> Issue Challenge</a> ";
						$s .= "</p>";
					$s .= "</div>";
				}
  			$s .= "</div>";
  			
  			$s .= "<div class=\"user-gifts\">";
  				$s .= $this->getUserPageGifts($wgTitle->getText());
  			$s .= "</div>";
  			
  			$s .= "<div class=\"user-friends\">";
  				$s .= $this->getUserPageRelationships($wgTitle->getText(),1);
  			$s .= "</div>";
  			$s .= "<div class=\"user-foes\">";
  				$s .= $this->getUserPageRelationships($wgTitle->getText(),2);
  			$s .= "</div>";
  			
			$s .= "<div class=\"user-mini-gallery\">";
				$s .= $this->getMiniGallery($wgTitle->getText());
  			$s .= "</div>";
			
  			//articles by user
  			require_once("$IP/extensions/ListPages/ListPagesClass.php");
			
			$list = new ListPages();
			$list->setCategory("Opinions by User {$wgTitle->getText()}");
			$list->setShowCount(3);
			$list->setBool("ShowCtg","NO");
			//$list->setShowBlurb("200");
			$list->setOrder("New");
			$list->setBool("ShowVoteBox","YES");
			$list->setBool("ShowDate","NO");
			$list->setBool("ShowStats","NO");
			$list->setBool("ShowNav","NO");
			//$list->setBool("ShowPic", "YES");
		
			$s .= "<div class=\"user-page-articles\">";
			$s .= "<div class=\"user-page-articles-title\">";
			$s .= "<h1 class=\"user-profile-title\">Articles</h1>
					<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">
						<a href=\"index.php?title=Category:Opinions by User {$wgTitle->getText()}\">View All</a> - 
						<a href=\"index.php?title=Main_Page\">Main Page</a> 
					</p>";
			$s .= "</div>";
			$s .= $list->DisplayList();
			$s .= "</div>";
  			
			$s .= "<div class=\"user-picture-games\">";
				$s .= $this->getUserPictureGames($id);
			$s .= "</div>";
			
  			//user stats
  			$s .= "<div class=\"user-stats\">";
				$s .= $this->getUserStats($id,$wgTitle->getText());
			$s .= "</div>";
  			
  			
  			
  		$s .= '</div>';
  		$s .= '<div class="user-right">';
  			
  			//user profile
  			$s .= "<div class=\"user-networks\">";
				$s .= $this->getUserFavoriteTeams($id,$wgTitle->getText());
			$s .= "</div>";
  			
  			//user activity
  			$s .= "<div class=\"user-activity\">";
  				$s .= $this->getUserPageActivity($wgTitle->getText());
  			$s .= "</div>";
  			
  			$s .= "<div class=\"user-profile\">";
				$s .= $this->getUserProfile($wgTitle->getText());
			$s .= "</div>";
			
  			//user activity
  			$s .= "<div class=\"user-board\">";
  				$s .= $this->getUserBoard($id,$wgTitle->getText());
  			$s .= "</div>";
			
  		$s .= '</div>';
  		$s .= "<div class=\"cleared\"></div>";
  	
	} else if ($tns == NS_USER_TALK) {
  			
			$s .= "<p><a href=\"index.php?title=User:{$wgTitle->getText()}\"><< Back to {$wgTitle->getText()}'s Profile</a></p>";
  			$s .= "<div class=\"edit-menu-sub-profile\">";
   				$s .= "<div class=\"edit-button\" >";
					
					if ( ! $wgOut->isArticleRelated() ) {
						$s .= "<img src=\"images/common/editIcon.gif\"/>";
						$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
						$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
					} else {
						if ( $wgTitle->userCan( 'edit' ) ) {
							$s .= "<img src=\"images/common/editIcon.gif\"/>";
							$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Edit</a>";
							$s .= "<span style=\"margin:0px 0px 0px 29px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
						} else {
							$s .= "<img src=\"images/common/editIcon.gif\"/>";
							$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
							$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
						}
					}
						
				$s .= "</div>";
				$s .= "<div class=\"edit-sub-menu\" id=\"edit-sub-menu-id\" style=\"display:none;\">";
					$s .= '<p><img src="images/common/pagehistoryIcon.png" alt="page history icon" border="0"/> ' . $this->historyLink() . '</p>';
			   		if ( $wgTitle->userCanMove() ) {
				 		$s .= '<p><img src="images/common/moveIcon.png" alt="move icon" border="0"/> ' . $this->moveThisPage() . '</p>';
			       	}
			       	$s .= '<p><img src="images/common/whatlinkshereIcon.png" alt="what links here icon" border="0"/> ' . $this->whatLinksHere() . '</p>';
			       	if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists() ) {
				 		$s .= '<p><img src="images/common/protectIcon.png" alt="protect icon" border="0"/> ' . $this->protectThisPage() . '</p>';
			       	}
			       	if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists()) {
				 		$s .= '<p><img src="images/common/deleteIcon.png" alt="delete icon" border="0"/> ' . $this->deleteThisPage() . '</p>';
			       	}
			       	if ( $wgUser->isLoggedIn() ) {
				   		$s .= '<p><img src="images/common/addtowatchlistIcon.png" alt="watchlist" border="0"/> ' . $this->watchThisPage() . '</p>';
				 		$s .= '<p><img src="images/common/uploadIcon.png" alt="upload" border="0"/> ' . $this->specialLink("upload") . '</p>';
			       }
   				$s .= "</div>";
			$s .= "</div>";
   			$s .= $this->pageTitle();
			$s .= "<div class=\"orange-box\">";
				$s .= "This is a talk page for <b>{$wgTitle->getText()}</b>.  To leave a message, click the edit button on the top right.  Enter your message and press save.<br>To sign your message type the following: \"~~~~\"";
			$s .= "</div>";
  			
  	} else if ($wgTitle->getPrefixedText()=="UserWiki:{$wgTitle->getText()}") {
  			$s .= "<p><a href=\"index.php?title=User:{$wgTitle->getText()}\"><< Back to {$wgTitle->getText()}'s Profile</a></p>";
  			$s .= "<div class=\"edit-menu-sub-profile\">";
   				$s .= "<div class=\"edit-button\" >";
					
					if ( ! $wgOut->isArticleRelated() ) {
						$s .= "<img src=\"images/common/editIcon.gif\"/>";
						$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
						$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
					} else {
						if ( $wgTitle->userCan( 'edit' ) ) {
							$s .= "<img src=\"images/common/editIcon.gif\"/>";
							$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Edit</a>";
							$s .= "<span style=\"margin:0px 0px 0px 29px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
						} else {
							$s .= "<img src=\"images/common/editIcon.gif\"/>";
							$s .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
							$s .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
						}
					}
						
				$s .= "</div>";
				$s .= "<div class=\"edit-sub-menu\" id=\"edit-sub-menu-id\" style=\"display:none;\">";
					$s .= '<p><img src="images/common/pagehistoryIcon.png" alt="page history icon" border="0"/> ' . $this->historyLink() . '</p>';
			   		if ( $wgTitle->userCanMove() ) {
				 		$s .= '<p><img src="images/common/moveIcon.png" alt="move icon" border="0"/> ' . $this->moveThisPage() . '</p>';
			       	}
			       	$s .= '<p><img src="images/common/whatlinkshereIcon.png" alt="what links here icon" border="0"/> ' . $this->whatLinksHere() . '</p>';
			       	if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists() ) {
				 		$s .= '<p><img src="images/common/protectIcon.png" alt="protect icon" border="0"/> ' . $this->protectThisPage() . '</p>';
			       	}
			       	if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists()) {
				 		$s .= '<p><img src="images/common/deleteIcon.png" alt="delete icon" border="0"/> ' . $this->deleteThisPage() . '</p>';
			       	}
			       	if ( $wgUser->isLoggedIn() ) {
				   		$s .= '<p><img src="images/common/addtowatchlistIcon.png" alt="watchlist" border="0"/> ' . $this->watchThisPage() . '</p>';
				 		$s .= '<p><img src="images/common/uploadIcon.png" alt="upload" border="0"/> ' . $this->specialLink("upload") . '</p>';
			       }
   				$s .= "</div>";
			$s .= "</div>";
			$s .= $this->pageTitle();
			if ( $wgUser->getName() == $wgTitle->getText()  ) {
				$s .= "<div class=\"orange-box\">";
					$s .= "This is your User Wiki.  This is your personal space to do whatever you like with it.  Have fun and be creative.<br>To edit this page, click the edit button in the top right.";
				$s .= "</div>";
			}
			
  	} else {
		if (300 !== $wgTitle->getNamespace()) {
  			$s .= $this->pageTitle();
	   			if ($this->subPageSubtitle()) {
	    		 	$s .= '<p class="sub-title">'.$wgOut->getSubtitle().$this->subPageSubtitle().'</p>';
	   			}
		}
  	}
  	}
     
  return $s;
  
}
 
 function doAfterContent() {
 
  global $wgOut, $wgUser, $wgTitle;
  
  	$cat = $this->getCategoryLinks();
  		
  		//categories
  		if( $cat ) $s .= "<div id=\"categories\">$cat</div>";
  		
  		
  		//end main-body
  		if (($wgOut->getPageTitle() !== 'Main Page') && (NS_SPECIAL !== $wgTitle->getNamespace()) && (NS_USER !== $wgTitle->getNamespace()) && (NS_USER_TALK !== $wgTitle->getNamespace()) && ($wgTitle->getPrefixedText() !== "UserWiki:{$wgTitle->getText()}") && ($wgOut->isArticle())) {
  			$s .= "</div>"; #end main-sub
	   		$s .= "<div class=\"cleared\"></div>";
	   	}
  		
  		$s .= "</div>"; #end main 
  		$s .= "<div class=\"cleared\"></div>";
  	
  	$s .= "<div class=\"footer\">";
  		$s .= $this->getFooter();
  	$s .= "</div>";
  	
  	$s .= "</div>"; #end container
   
  return $s;
 }
 
 function getMainPage(){
  
  global $wgUser, $IP, $wgRequest, $wgMemc;
	 
  require_once ("$IP/extensions/ListPages/ListPagesClass.php");
  $output = "";
  $dates_array = get_dates_from_elapsed_days(2);
  $date_categories = "";
  foreach ($dates_array as $key => $value) {
	if($date_categories)$date_categories .=",";
	$date_categories .= str_replace(",","\,",$key);
  }
  
  if (!($wgUser->isLoggedIn())) {
  	$output .= "<div class=\"main-page-left\">";
		
	 //WELCOME MESSAGE

		 $dbr =& wfGetDB( DB_SLAVE );
		 $s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
		 $vote_count = number_format($s->count);
		 $s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
		 $comment_count = number_format($s->count);
		 $edits_count = number_format(SiteStats::edits());
		 $good_count = number_format(SiteStats::articles());

			$output .= '<div class="welcome-message">';
				$output .= '<h1>Connect with Sports Fans</h1>';
				$output .= '<h2>ArmchairGM is a community for passionate sports fans.  Read, write, and talk about sports.  Meet other fans of your favorite teams.  Rate players, teams, and sporting events.  Earn points and receive gifts.</h2>';
	  			$output .= '<p class="welcome-message-cell"><img src="images/common/pagesIcon.gif" border="0"/> <b>Pages</b>: ' . $good_count . ' <img src="images/common/pencilIcon.gif" border="0"/> <b>Edits</b>: ' . $edits_count . ' <img src="images/common/voteIcon.gif" border="0"/> <b>Votes</b>: ' . $vote_count . ' <img src="images/common/comment.gif" border="0"/> <b>Comments</b>: ' . $comment_count . '</p>';
				$output .= '<div class="cleared"></div>';
				$output .= '<p class="welcome-big-link"><a href="index.php?title=Special:UserRegister">Sign Up!</a></p>';
			$output .= '</div>';
			
			
		//LOGGED OUT ARTICLES
		  $list = new ListPages();
		  $list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions");
		  $list->setShowCount(5);
		  $list->setOrder("PublishedDate");
		  $list->setShowPublished("YES");
		  $list->setShowBlurb("300");
		   $list->setBlurbFontSize("small");
		  $list->setBool("ShowVoteBox","NO");
		  $list->setBool("ShowDate","YES");
		  $list->setBool("ShowStats","YES");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowNav","YES");
		  $list->setBool("ShowPic","YES");

		  $output .= "<div class=\"logged-out-articles\">";
		  $output .= '<h1>Read, Write, and Talk About Sports</h1>';
		  $output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Special:ArticlesHome\">Read More Articles</a> - <a href=\"index.php?title=Special:UserRegister\">Write Your Own Article</a></p>";
		  $output .= $list->DisplayList();
		  $output .= '</div>';
		

	    
		// FEATURED RATINGS
		require_once("$IP/extensions/Vote-Mag/VoteClass.php");
		require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
		$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>";
		
		$per_row = 3;
		$r = new ListRatings();
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players, Snooker Players, PGA Players, Grand Prix Drivers, IRL Drivers, NASCAR Drivers, CART Drivers, Formula One Drivers, Tennis Players, MotoGP Riders, MLB Power Rankings");
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players");
		$ratings = $r->getRatingsList(11,0,"vote_avg","desc",false);
		$x = 1;
		foreach($ratings as $item) {		
			$rating_title = Title::makeTitle( $item["page_namespace"], $item["page_title"]);
				
			$Vote = new VoteStars($item["page_id"]);
			$Vote->setUser($wgUser->getName(),$wgUser->getID());
		
			$ratings_list .= "<div class=\"featured-rating\">
						<div class=\"featured-rating-title\">
							<a href=\"{$rating_title->getFullURL()}\">{$rating_title->getText()}</a>
						</div>
					<div id=\"rating_stars_{$x}\">" .  $Vote->displayStars($x,$item["vote_avg"],false) . "</div>
					<div id=\"rating_{$x}\" class=\"featured-rating-total\">" . $Vote->displayScore() . "</div>
				</div>";
			
			if($x==count($ratings) || $x!=1 && $x%$per_row ==0)$ratings_list .= "<div class=\"cleared\"></div>";
			$x++;
		}
		
		$rate_players = Title::makeTitle( NS_MAIN  , "Ratings"  );
		$rate_teams = Title::makeTitle( NS_MAIN  , "Ratings"  );
 
		$output .= '<div class="featured-ratings">';
			$output .= '<h1>Rate Your Favorite Players and Teams</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"{$rate_players->getFullURL()}\">Rate More Players</a> - <a href=\"{$rate_teams->getFullURL()}\">Rate More Teams</a></p>";
			$output .= $ratings_list;
		$output .= '</div>';
			
			
 	$output .= "</div>";
	$output .= "<div class=\"main-page-right\">";
		
		
	$output .= $this->getFeaturedUsers();

			
			// TOP FANS
			$fans = UserStats::getTopFansList(4);
			$x = 1;
			foreach($fans as $fan) {		
				$avatar = new wAvatar($fan["user_id"],"m");
				$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"><span class=\"top-fan-number\">{$x}.</span> <a href=\"{$user->getFullURL()}\">{$avatar_image}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
				$x++;
				 
			}
			
			$weekly_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFansRecent"  );
			$top_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFans"  );
			
			$output .= "<div class=\"picture-game-preview\">
				<h1>Play the Picture Game</h1>";
				$output .= $this->getPictureGamePreview();
			$output .= "</div>";
			
			$output .= '<div class="picture-game-preview">';
					$output .= $this->getPollPreview();
			$output .= '</div>';
				
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Earn Points</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> - <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';
			
			// RECENT GIFTS
			require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
			require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
		
			$key = wfMemcKey( 'gifts', 'unique', 4 );
			$data = $wgMemc->get( $key );
			if($data){
				$gifts=$data;
			}else{
				wfDebug( "Got unique gift list from cache" );
				$gifts = UserGifts::getAllGiftList(4);
			}
			
			$gift_title = Title::makeTitle( NS_SPECIAL  , "ViewGift"  );
			foreach($gifts as $gift) {		
				$recent_gifts .= "<div class=\"recent-gift\"><a href=\"{$gift_title->getFullURL()}&gift_id={$gift["id"]}\"><img src=\"images/awards/" . Gifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" /></a> </div>";
			}
		
			$output .= '<div class="recent-gifts">';
				$output .= '<h1>Receive Gifts and Awards</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Special:UserRegister\">Give a Gift</a> - <a href=\"index.php?title=Special:UserRegister\">Learn About Awards</a></p>";
				$output .= $recent_gifts;
				$output .= "<div class=\"cleared\"></div>";
			$output .= '</div>';		
		
		
			//Browse Sports Encylopedia
			$output .= '<div class="browse-encyclopedia">';
				$output .= '<h1>Browse the Sports Encyclopedia</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=MLB_Encyclopedia\">MLB</a> - <a href=\"index.php?title=NFL_Encyclopedia\">NFL</a> - <a href=\"index.php?title=NBA_Encyclopedia\">NBA</a> - <a href=\"index.php?title=NHL_Encyclopedia\">NHL</a></p>";
				$output .= "<p>" . wfMsg('Featured_Article') . "</p>";
			$output .= '</div>';
			
	  $output .= '</div>';
	  $output .= "<div class=\"cleared\"></div>";
	
		
	 
  }  else {
	
	  $u = new UserStats($wgUser->getID(),$wgUser->getName());
		$stats = $u->getUserStats();
		$points = str_replace(",","",$stats["points"]);
		$friends_count = str_replace(",","",$stats["friend_count"]);
		
  	$output .= "<div class=\"main-page-left\">";

		//Logged In Articles
		$list = new ListPages();
		  $list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions,ArmchairGM Announcements");
		  $list->setShowCount(6);
		  $list->setOrder("PublishedDate");
		  $list->setShowPublished("YES");
		  $list->setShowBlurb("300");
		   $list->setBlurbFontSize("small");
		  $list->setBool("ShowVoteBox","NO");
		  $list->setBool("ShowDate","YES");
		  $list->setBool("ShowStats","YES");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowNav","YES");
		  $list->setBool("ShowPic","YES");

		  $output .= "<div class=\"logged-in-articles\">";
		  $output .= '<h1 >Popular Sports Articles <span class="rss-feed"><a href="http://feeds.feedburner.com/Armchairgm"><img src=http://www.armchairgm.com/images/a/a7/Rss-icon.gif border="0"></a> rss feed</span></h1>';
		  	$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Create_Opinion\">Write An Article</a> - <a href=\"index.php?title=Category:" . date("F j, Y") . "\">Today's Articles</a> - <a href=\"index.php?title=Special:ArticlesHome\">All Popular</a></p>";
		  	$output .= $list->DisplayList();
		  $output .= '</div>';
		
		  //New Articles
		  $output .= '<div class="logged-in-articles">';
		  $list = new ListPages();
		  $list->setCategory("News, Opinions,Questions, ArmchairGM Announcements");
		  $list->setShowCount(6);
		  $list->setOrder("New");
		  $list->setShowPublished("NO");
		  $list->setShowBlurb("150");
		  $list->setBlurbFontSize("small");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowDate","NO");
		  $list->setBool("ShowStats","yes");
		  $list->setBool("ShowPic","YES");
		  $list->setBool("cache","yes");
		  $list->setHash("main page new");
		  $output .= '<h1>New Sports Articles</h1>';
		  $output .= "<p class=\"main-page-sub-links\" style=\"margin-bottom:10px;\"><a href=\"index.php?title=Create_Opinion\">Write An Article</a> - <a href=\"index.php?title=Category:" . date("F j, Y") . "\">Today's Articles</a> - <a href=\"index.php?title=Special:ArticlesHome\">All New</a></p>";
		  $output .= $list->DisplayList();
		  $output .= '</div>';
		  
		//Ratings
		require_once("$IP/extensions/Vote-Mag/VoteClass.php");
		require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
		$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>";

		$per_row = 3;
		$r = new ListRatings($wgUser->getName());
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players, Snooker Players, PGA Players, Grand Prix Drivers, IRL Drivers, NASCAR Drivers, CART Drivers, Formula One Drivers, Tennis Players, MotoGP Riders, MLB Power Rankings");
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players");
		$ratings = $r->getRatingsList(11,0,"vote_avg","desc",false);
		$x = 1;
		foreach($ratings as $item) {		
			$rating_title = Title::makeTitle( $item["page_namespace"], $item["page_title"]);

			$Vote = new VoteStars($item["page_id"]);
			$Vote->setUser($wgUser->getName(),$wgUser->getID());

			$ratings_list .= "<div class=\"featured-rating\">
						<div class=\"featured-rating-title\">
							<a href=\"{$rating_title->getFullURL()}\">{$rating_title->getText()}</a>
						</div>
					<div id=\"rating_stars_{$x}\">" .  $Vote->displayStars($x,$item["vote_avg"],false) . "</div>
					<div id=\"rating_{$x}\" class=\"featured-rating-total\">" . $Vote->displayScore() . "</div>
				</div>";

			if($x==count($ratings) || $x!=1 && $x%$per_row ==0)$ratings_list .= "<div class=\"cleared\"></div>";
			$x++;
		}

		$rate_players = Title::makeTitle( NS_MAIN  , "Ratings"  );
		$rate_teams = Title::makeTitle( NS_MAIN  , "Ratings"  );

		$output .= '<div class="featured-ratings">';
			$output .= '<h1>Rate Players and Teams</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"{$rate_players->getFullURL()}\">Rate More Players</a> - <a href=\"{$rate_teams->getFullURL()}\">Rate More Teams</a></p>";
			$output .= $ratings_list;
		$output .= '</div>';
		
		//Browse Sports Encylopedia
		$output .= '<div class="browse-encyclopedia">';
			$output .= '<h1>Browse the Sports Encyclopedia</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=MLB_Encyclopedia\">MLB</a> - <a href=\"index.php?title=NFL_Encyclopedia\">NFL</a> - <a href=\"index.php?title=NBA_Encyclopedia\">NBA</a> - <a href=\"index.php?title=NHL_Encyclopedia\">NHL</a></p>";
			$output .= "<p>" . wfMsg('Featured_Article') . "</p>";
		$output .= '</div>';
		
	$output .= "</div>";
	$output .= "<div class=\"main-page-right\">";
		
		//Main Page User Box
		$output .= '<div class="profile-box">';
		  $output .= '<h1> '. wgGetWelcomeMessage() .'</h1>';
		  $output .=  $this->getMainPageUserBox($avatar);
	    $output .= '</div>';
		
	
		
		//Friend's Activity
		if($friends_count > 0){
		$output .= '<div class="main-page-friends-activity">';
		  $output .= "<h1>Friends' Activity</h1>";
		
		  //$output .= "<div class=\"user-home-feed\">";
		    require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
			require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
			require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			
			$rel = new UserActivity($wgUser->getName(),"friends",5);
			
			$rel->setActivityToggle("show_votes",0);
			$rel->setActivityToggle("show_network_updates",1);
			/*
			Get all relationship activity
			*/
			$activity = $rel->getActivityList();
			if($activity){
				$x = 1;
				foreach ($activity as $item) {
					if($x<7){
					$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
					$user_title = Title::makeTitle( NS_USER  , $item["username"]  );
					$user_title_2 = Title::makeTitle( NS_USER  , $item["comment"]  );
					if($user_title_2){
						$user_link_2 = "<a href=\"{$user_title_2->getFullURL()}\">{$item["comment"]}</a>";
					}
					
					$avatar = new wAvatar($item["userid"],"s");
					$CommentIcon = $avatar->getAvatarImage();
					
					if($item["type"] == "comment"){
						$comment_url = "#comment-{$item["id"]}";
					}
					$page_link = "<a href=\"" . $title->getFullURL() . "{$comment_url}\">" . $title->getPrefixedText() . "</a> ";
					$item_time = "<span class=\"user-home-item-time\">{$rel->getTimeAgo($item["timestamp"])} ago</span>";
					if ($x==6) {
						$output .= "<div class=\"main-page-activity-last\">";
					} else {
						$output .= "<div class=\"main-page-activity\">";
					}
					
					$output .= "<span class=\"user-home-item-icon\"><img src=images/common/" . UserActivity::getTypeIcon($item["type"]) . " alt=\""  . UserActivity::getTypeIcon($item["type"]) . "\" border='0'></span><span class=\"user-home-item-user\"><a href=\"{$user_title->getFullURL()}\">{$item["username"]}</a></span><span>";
					switch ($item["type"]) {
						case "edit":
							$output .= "edited the page {$page_link} {$item_time}</span>";
							$output .= "<div class=\"user-home-item-editinfo\">";
							$output .= "{$item["comment"]}";
							$output .= "</div>";
							break;
						case "vote":
							$output .= "voted for the page {$page_link} {$item_time}</span>";
							break;
						case "comment":
							$output .= "commented on the page {$page_link} {$item_time}</span>";
							$output .= "<div class=\"user-home-item-comment\">";
							$output .= "\"{$item["comment"]}\"";
							$output .= "</div>";
						
							break;
						case "gift-sent":
							$output .= "sent a gift to {$user_link_2} {$item_time}</span>";
							break;
						case "gift-rec":
							$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$output .= "received a <a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">gift</a> from {$user_link_2} {$item_time}</span>";
							$output .= "<div class=\"user-home-item-gift\">";
							$output .= "<span class=\"user-home-gift-image\">";
							$output .= "<a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">{$gift_image}</a>";
							$output .= "</span>";
							$output .= "<span class=\"user-home-gift-info\">";
							$output .= "{$item["pagetitle"]}";
							$output .= "</span>";
							$output .= "</div>";
						
							break;
						case "system_gift":
							$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$output .= "received an <a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\">award</a> {$item_time}</span>";
							$output .= "<div class=\"user-home-item-gift\">";
							$output .= "<span class=\"user-home-gift-image\">";
							$output .= "<a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\">{$gift_image}</a>";
							$output .= "</span>";
							$output .= "<span class=\"user-home-gift-info\">";
							$output .= "{$item["pagetitle"]}";
							$output .= "</span>";
							$output .= "</div>";
						
							break;
						case "friend":
							$output .= "is now friends with {$user_link_2} {$item_time}</span>";
							break;
						case "foe":
							$output .= "is now foes with {$user_link_2} {$item_time}</span>";
							break;
						case "challenge_sent":
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\">challenge</a>";
							$output .= "issued an accepted {$challenge_link} to {$user_link_2} {$item_time}</span>";
							$output .= "<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>";
							break;
						case "challenge_rec":
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\">challenge</a>";
							$output .= "accepted a {$challenge_link} from {$user_link_2} {$item_time}</span>";
							$output .= "<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>";
							break;
						case "system_message":
							$output .= "{$item["comment"]} {$item_time}</span>";
							break;
						case "user_message":
							$output .= "wrote on <a href=\"{$user_title_2->getFullURL()}\">{$item["comment"]}'s</a> <b><a href=\"" . UserBoard::getUserBoardURL($user_title_2->getText()) . "\">board</a></b>  {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									\"{$item["namespace"]}\"
									</div>";
							break;
						case "network_update":
							$page_link = "<a href=\"" . SportsTeams::getNetworkURL($item["sport_id"],$item["team_id"]) . "\">" . $item["network"] . "</a> ";
							$network_image = SportsTeams::getLogo($item["sport_id"],$item["team_id"],"s");
							$output .= "has a thought for the {$page_link} Network {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									{$network_image} \"{$item["comment"]}\"
									</div>";
							break;
					}
					//$output .= "</span>";
					
					$comment = $item["comment"];
					if($item["type"] == "comment"){
						$comment = "<a href=\"" . $title->getFullURL() . "#comment-" . $item["id"]  . "\" title=\"" . $title->getText() . "\" >" . $item["comment"] . "</a>";
					}
					//$output .= "<span>" . $comment . "</span>";
					
					$output .= "</div>";
					$x++;
					}
						
				}
			}
			
			$output .= "<div class=\"cleared\"></div>";
			 
			
	    $output .= '</div>';
		} else {
			
		}
		
		$output .= "<div class=\"picture-game-preview\">
			<h1>Play the Picture Game</h1>
				<p class=\"main-page-sub-links\">
					<a href=\"index.php?title=Special:PictureGameHome\">Create a Picture Game</a> - 
					<a href=\"index.php?title=Special:PictureGameHome&picGameAction=startGame\">Skip This Game</a> -
					<a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery\">All Games</a>
				</p>";
			$output .= $this->getPictureGamePreview();
		$output .= '</div>';
	
						$output .= '<div class="picture-game-preview">';
					$output .= $this->getPollPreview();
				$output .= '</div>';
				
		$output .= $this->getNewUsers(15);
		
		// TOP FRIENDS RELATIVE TO POINTS
		
	
		// TWO FRIENDS THAT HAVE MORE POINTS 
		$fans_above = UserStats::getFriendsRelativeToPoints($wgUser->getID(),$points,3,1);
		$friends_above = count($fans_above);
		// TWO FRIENDS THAT HAVE LESS POINTS POINTS 
		$fans_below = UserStats::getFriendsRelativeToPoints($wgUser->getID(),$points,(6-$friends_above),-1);
		$friends_below = count($fans_below);
		
		if( ($friends_above+$friends_below) > 0){

			foreach($fans_above as $fan) {		
				$avatar = new wAvatar($fan["user_id"],"m");
				//$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
			}
			
			//CURRENT USER
			$avatar = new wAvatar($wgUser->getID(),"m");
			//$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
			$user =  Title::makeTitle( NS_USER  , $wgUser->getName()  );
			
			$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$wgUser->getName()}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($points)."</b> points</span> <span class=\"profile-on\">you</span></div>";
	
			
			foreach($fans_below as $fan) {		
				$avatar = new wAvatar($fan["user_id"],"m");
				//$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
			}		
			
			$weekly_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFansRecent"  );
			$top_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFans"  );
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Rank</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> - <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';
		}else{
			$fans = UserStats::getTopFansListPeriod(5);
			$x = 1;
			foreach($fans as $fan) {		
				$avatar = new wAvatar($fan["user_id"],"m");
				//$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"><span class=\"top-fan-number\">{$x}.</span> <a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
				$x++;
				 
			}
			$weekly_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFansRecent"  );
			$top_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFans"  );
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Weekly Points</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> - <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';			
		}
		
				$output .= '<div class="top-fans">';
					$output .= $this->getCommentsOfTheDay();
				$output .= '</div>';	
			
		
	$output .= "</div>";
	$output .= "<div class=\"cleared\"></div>";
  }
  
  return $output;

 }
 
   function searchForm( $label = "" ) {
    global $wgRequest;
  
    $search = $wgRequest->getText( 'search' );
    $action = $this->escapeSearchLink();
  
    $s = "<form method=\"get\" action=\"$action\">";
  
    if ( "" != $label ) { $s .= "{$label}: "; }
	$s .= "<div class=\"search-form\">
		<ul>
			<li><input class=\"search-input\" type='text' name=\"search\" size='20' value=\"Players, Teams, Sports\" / onclick=\"this.value=''\"></li>
			<li><input type='image' src='../images/common/search.png' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" /></li>
		</ul>
	</div>";
    $s .= "</form>";
  
    return $s;
  }
  
  function getUserFavoriteTeams($user_id,$user_name){
		global $wgUser, $wgTitle, $IP;
		
		$output = "";
		
		if($wgUser->getID()==$user_id) {
			$output .= "<script>
	
			function detEnter(e,num,sport_id,team_id) {
				var keycode;
				if (window.event) keycode = window.event.keyCode;
				else if (e) keycode = e.which;
				else return true;
				if (keycode == 13){
					add_message(num,sport_id,team_id)
					return false;
				} else return true;
			}

			function close_message_box(num){
				Effect.Fade(\"status-update-box-\"+num);
			}
			function show_message_box(num,sport_id,team_id){
				\$(\"status-update-box-\"+num).innerHTML = '<input  type=\"text\" id=\"status_text\" onKeyPress=\"detEnter(event,' + num + ',' + sport_id + ',' + team_id + ' )\" value=\"\" style=\"width:250px\"  maxlength=\"150\"> <input type=\"button\" class=\"site-button\" value=\"add\" onclick=\"add_message(' + num + ',' + sport_id + ',' + team_id + ' )\"  > <input type=\"button\" class=\"site-button\" value=\"cancel\" onclick=\"close_message_box(' + num + ' )\"  >'
				Effect.Appear(\"status-update-box-\"+num);	
			}
			
			function add_message(num,sport_id,team_id){
				if(\$(\"status_text\").value && !posted){
					close_message_box(num)
					posted = 1;
					var url = \"index.php?action=ajax\";
					var pars = 'rs=wfAddUserStatusProfile&rsargs[]=' + sport_id +'&rsargs[]=' + team_id + '&rsargs[]=' + escape(\$(\"status_text\").value) + '&rsargs[]=10'
					Element.hide( 'status-update' );
					//Effect.Fade('status-update', {duration:1.0, fps:32});
			
					var myAjax = new Ajax.Updater(
						'status-update', url, {
							method: 'post', 
							parameters: pars,
							onSuccess: function(originalRequest) {
								posted = 0;
								Effect.Appear('status-update', {duration:2.0, fps:32});
									
							}
					});
				}
			}
			
		
		
			</script>";
		}else{
			$output .= "<script>
				function vote_status(id,vote){
					Effect.Fade('user-status-vote-'+id, {duration:1.0, fps:32});
					var url = \"index.php?action=ajax\";
					var pars = 'rs=wfVoteUserStatus&rsargs[]=' + id + '&rsargs[]=' + vote
					var myAjax = new Ajax.Updater(
						'user-status-vote-'+id,
						url, {
							method: 'post', 
							parameters: pars,
							onSuccess: function(originalRequest) {
								Effect.Appear('user-status-vote-'+id, {duration:2.0, fps:32});
							}
					});
					
					
				}
			</script>";
		}
			//using cache (won't work with vote counts)
			/*
			global $wgMemc;
			$key = wfMemcKey( 'user', 'status-last-update', $user_id );
			$user_update = $wgMemc->get( $key );
			*/
			require_once("$IP/extensions/wikia/UserStatus/UserStatusClass.php");
			$s = new UserStatus();
			$user_update = $s->getStatusMessages($user_id,0,0,1,1);
			$user_update = $user_update[0];
			$output .= "<h1 class=\"user-profile-title\">Latest Thought</h1>";
			if ($user_update) {
				
				if( $wgUser->getName()==$user_update["user_name"]){
					$vote_count = $user_update["plus_count"] . " ". (($user_update["plus_count"]==1)?"person":"people") . " agree" . (($user_update["plus_count"]==1)?"s":"");
				}
				$thought_link =  Title::makeTitle( NS_SPECIAL  , "ViewThought"  );	
				$view_thought_link = "<a href=\"" . $thought_link->getFullURL() . "&id={$user_update["id"]}\" >[see who else agrees]</a>";
				if( $wgUser->isLoggedIn() && $wgUser->getName()!=$user_update["user_name"]){
					if( !$user_update["voted"] ){
						$vote_link = "<a href=\"javascript:void(0);\" onclick=\"vote_status({$user_update["id"]},1)\">[agree]</a>";
					}else{
						$vote_link = $user_update["plus_count"] . " ". (($user_update["plus_count"]==1)?"person":"people") . " agree" . (($user_update["plus_count"]==1)?"s":"");
					}
				}
				$output .= "<div class=\"status-update-username\">
					{$user_name}
				</div> 
				<div class=\"status-container\">
					<div id=\"status-update\" class=\"status-message\">".
						SportsTeams::getLogo($user_update["sport_id"],$user_update["team_id"],"s")."
						{$user_update["text"]}
						<div class=\"user-status-date\">
							".get_time_ago($user_update["timestamp"])." ago 
							<span class=\"user-status-profile-vote\" id=\"user-status-vote-{$user_update["id"]}\">{$vote_count} {$vote_link} {$view_thought_link}</span>
						</div>
					</div>
				</div>
				<p style=\"margin-bottom:20px;\">
					<a href=\"index.php?title=Special:UserStatus&user=" . urlencode($user_name) . "\">More Thoughts</a>";
					if($user_name == $wgUser->getName() ){
						$output .= " - <a href=\"index.php?title=Special:UserHome&edits=0&votes=0&comments=0&gifts=0&rel=0&messages=0&system_gifts=0&messages_sent=0&network_updates=1\">Friend's Thoughts</a>";
					}
				$output .= "</p>"; 
			} else {
				$output .= "<div id=\"status-update\" class=\"status-message-no\">How sad! {$user_name} has no thoughts.</div>";
				if($user_name == $wgUser->getName() ){
					$output .= "<div style=\"margin-bottom:20px;\"><a href=\"index.php?title=Special:UserHome&edits=0&votes=0&comments=0&gifts=0&rel=0&messages=1&system_gifts=0&messages_sent=0&network_updates=1\">Friend's Thoughts</a></div>";
				}
			}
			
			
		
			
			
			
			$output .= "<h1 class=\"user-profile-title\">Fan Networks</h1>";
			$output .= "<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">";	
			if($wgUser->getID()==$user_id) {
				$output .= "<a href=\"index.php?title=Special:UpdateFavoriteTeams\">Add More Networks</a> - ";
				$output .= "<a href=\"index.php?title=Special:SimilarFans\">Meet Similar Fans</a> - ";
			}
			
			$output .= "<a href=\"index.php?title=Special:TopNetworks\">Top Networks</a>";
			
			$output .= "</p>";
		
			
			
			$favs = SportsTeams::getUserFavorites($user_id);
			foreach($favs as $fav){
				$homepage_title = Title::makeTitle( NS_SPECIAL  , "FanHome" );
				if($wgUser->getID()==$user_id) {
					$status_link = " <span class=\"status-message-add\">- <a href=\"javascript:void(0);\" onclick=\"show_message_box({$fav["order"]},{$fav["sport_id"]},{$fav["team_id"]})\">add thought</a></span>";
				}
				$network_update_message = "";
				if($user_updates[$fav["sport_id"]."-".$fav["team_id"]]){
					$network_update_message =  $user_updates[$fav["sport_id"]."-".$fav["team_id"]];
				}
				if($fav["team_name"]){
					$display_name = $fav["team_name"];
					$logo = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($fav["team_id"],"m") . "\" border=\"0\" align=\"middle\" alt=\"logo\" />";
					
				}else{
					$display_name = $fav["sport_name"];
					$logo = "<img src=\"images/sport_logos/" . SportsTeams::getSportLogo($fav["sport_id"],"m") . "\" border=\"0\" align=\"middle\" alt=\"logo\" />";
				}
				$output .= "<div class=\"user-fan-networks\">";
					$output .= "<span class=\"user-fan-networks-number\">";
						$output .= "{$fav["order"]}.";
					$output .= "</span>";
					
					$output .= "{$logo}"; 
					
					$output .= "<a href=\"{$homepage_title->getFullURL()}&sport_id={$fav["sport_id"]}&team_id={$fav["team_id"]}\">{$display_name}</a>";
					$output .= $status_link;
					$output .= "</div>";	
					$output .= "<div class=\"status-update-box\" id=\"status-update-box-{$fav["order"]}\" style=\"display:none\">
					
					</div>
					";	
			
			}
			
		$output .= "<div class=\"cleared\"></div>";
		
		return $output;
	}
 
}

?>
