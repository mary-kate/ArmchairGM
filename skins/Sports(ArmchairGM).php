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
	  			"67758"=>"Awrigh01",
				"66574"=>"Pean",
				"56870"=>"DNL",
				"66573"=>"Roblefko",
				"677581"=>"Awrigh012",
				"665741"=>"Pean2",
				"568701"=>"DNL2",
				"677581"=>"Awrigh012",
				"665741"=>"Pean2",
				"5687021"=>"DNL2"
				);
  
  #set stylesheet
  function getStylesheet() {
    return "common/Sports.css";
  }
  
  #set skinname
  function getSkinName() {
    return "Sports";
  }
  
  #main page before wiki content
  function doBeforeContent() {
	
  ##global variables
  global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP, $wgMemc;	
  
  
  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");  
  $tns=$wgTitle->getNamespace();
  
  $s = '';
  $s .= '<div id="container">';
    if (!($wgUser->isLoggedIn())) {  
        $s .= '<div id="topad">';
	$s .= $this->getTribalFusionAds(90, 728, "Sports");
        $s .= '</div>';
    }
	
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
	
	</style>\n";
		$s .= "<div class=\"top-bar\">";
    		$s .= "<div class=\"logo\">";
    			$s .= "<a href=\"index.php?title=Main_Page\"><img src=\"../../images/sports/logo.png\" alt=\"armchairgm\" border=\"0\"></a>";
    		$s .= "</div>";	
    		$s .= "<div class=\"tag-line\">";
    			$s .= "all sports, all you";
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
    				$s .= "<a href=\"index.php?title=Special:UserRegister\">Sign Up</a> or <a href=\"index.php?title=Special:Login\">Log In</a>"; 
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
				array("Your Profile" => "User:{$wgUser->getName()}","Friend's Activities" => "Special:UserHome","Talk Page" => "User talk:{$wgUser->getName()}","User Wiki" => "UserWiki:{$wgUser->getName()}", "Watchlist" => "Special:Watchlist")
				);
				
		$menu[] = array(
				"display_name" => "Sports",
				"page_name" => "",
				"subpages" =>
					 array("MLB" => "MLB","NFL" => "NFL","NBA" => "NBA","NHL" => "NHL",
						 "College Basketball"  => "College Basketball","College Football" => "College Football",
						 "Soccer" => "Soccer","Nascar"  => "Nascar")
				);
		
		$menu[] = array(
				"display_name" => "Write",
				"page_name" => "Create Opinion",
				"subpages" =>
					 array("Opinion" => "Create Opinion","News" => "Create News","Game Recap" => "Create Game Recap",
						 "Discussion"=> "Create Discussion","Dictionary Entry" => "Create Dictionary",
						 "Add a Blog" => "Add Blog","Movie Summary" => "Create Movie Summary","Book Summary" => "Create Book Summary")
		);
				
		$menu[] = array(
				"display_name" => "Articles",
				"page_name" => "Special:ArticlesHome",
				"subpages" =>
					 array("All Articles" => "Special:ArticlesHome","New Articles" => "Special:ArticlesHome/New", "Today's Articles" => "Category:".date("F d, Y"))
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
    		$s .= "<div class=\"green-bar\" style=\"height:35px;\">";
	
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
		if (!($wgOut->getPageTitle() == 'Main Page') && ($wgOut->isArticle()) && (NS_SPECIAL !== $wgTitle->getNamespace()) && ($tns !== NS_USER) && ($tns !== NS_USER_TALK) && ($wgTitle->getPrefixedText()!=="UserWiki:{$wgTitle->getText()}")) {
	  	$s .= '<div id="side">';
	 
	  			$s .= $this->userBox();
	  
	  			//$s .= $this->thisArticle();
	  
	  			if (!($wgUser->isLoggedIn())) {
		  			$s .= '<div id="sideads">' . "\n";
		  			$s .= $this->getGoogleAds(600, 120);
		  			$s .= '</div>';
	  			}
	  
		$s .= '</div>'; #end side
		$s .= '<div id="main-body">';
			
			
				$s .= "<div class=\"edit-menu\">";
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
					$s .= "<p style=\"margin:-3px 0px 5px 0px;\">
						<a href=\"index.php?title=UserWiki:{$wgTitle->getText()}\">User Wiki</a> -
						<a href=\"index.php?title=User_talk:{$wgTitle->getText()}\">Talk Page</a>
					</p>";
				
					$s .= "<div class=\"user-profile-avatar-bar\">";
  						$s .= "<div class=\"user-profile-avatar\">";
							$s .= "<img src='images/avatars/" . $avatar->getAvatarImage() . "'/>";

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
								$s .= "<div style=\"background-color:#89C46F;width:".($complete*2)."px; height:14px;\">&nbsp;</div>";
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
							<img src="../../images/common/msnIconSmall.png" alt="challenge icon" border="0"/> Hotmail
						</a>
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/yahooIconSmall.png" alt="challenge icon" border="0"/> Yahoo
						</a>
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/gmailIconSmall.png" alt="challenge icon" border="0"/> Gmail
						</a> 
						<a href="index.php?title=Special:InviteContacts">
							<img src="../../images/common/aolIconSmall.png" alt="challenge icon" border="0"/> AOL
						</a>
					</p>';
					$s .= "</div>";
		    	} else {
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
			    					$s .= "<a href=\"index.php?title=Special:AddRelationship&user={$wgTitle->getText()}&rel_type=1\">
											<img src=\"../../images/common/friendRequestIcon.png\"> Add as Friend
									</a> ";
			    					$s .= "<a href=\"index.php?title=Special:AddRelationship&user={$wgTitle->getText()}&rel_type=2\">
										   <img src=\"../../images/common/foeRequestIcon.png\"> Add as Foe
									</a> ";
			    			}
							$s .= "<a href=\"index.php?title=Special:GiveGift&user={$wgTitle->getText()}\"><img src=\"../../images/common/icon_package_get.gif\"> Send A Gift</a> ";
		    				$s .= "<a href=\"index.php?title=Special:ChallengeUser&user={$wgTitle->getText()}\"><img src=\"../../images/common/challengeIcon.png\"> Issue Challenge</a> ";
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
			$s .= "<h1 class=\"user-profile-title\">My Articles</h1>
					<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">
						<a href=\"index.php?title=Category:Opinions by User {$wgTitle->getText()}\">View All</a> - 
						<a href=\"index.php?title=Main_Page\">Main Page</a> 
					</p>";
			$s .= "</div>";
			$s .= $list->DisplayList();
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
  		$s .= $this->pageTitle();
	   		if ($this->subPageSubtitle()) {
	    		 $s .= '<p class="sub-title">'.$wgOut->getSubtitle().$this->subPageSubtitle().'</p>';
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
			$s .= "</div>";
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
  
  global $wgUser, $IP, $wgRequest;
	 
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
				$output .= '<h2>ArmchairGM is a community for passionate sports fans.  Read, write, and talk about sports.  Meet other fans of your favorite teams.  Rates players, teams, and sporting events.  Earn points and receive gifts.</h2>';
	  			$output .= '<p class="welcome-message-cell"><img src="images/common/editicon.png" border="0"/> <b>pages</b>: ' . $good_count . ' <img src="images/common/16-em-pencil.png" border="0"/> <b>edits</b>: ' . $edits_count . ' <img src="images/common/arrow_up.gif" border="0"/> <b>votes</b>: ' . $vote_count . ' <img src="images/common/comment.gif" border="0"/> <b>comments</b>: ' . $comment_count . '</p>';
				$output .= '<div class="cleared"></div>';
				$output .= '<p class="welcome-big-link"><a href="index.php?title=Special:UserRegister">Sign Up!</a></p>';
			$output .= '</div>';
			
			
		//LOGGED OUT ARTICLES
		  $list = new ListPages();
		  $list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions");
		  $list->setShowCount(3);
		  $list->setOrder("PublishedDate");
		  $list->setShowPublished("YES");
		  $list->setShowBlurb("300");
		  $list->setBool("ShowVoteBox","NO");
		  $list->setBool("ShowDate","YES");
		  $list->setBool("ShowStats","YES");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowNav","NO");
		  $list->setBool("ShowPicture","YES");

		  $output .= "<div class=\"logged-out-articles\">";
		  $output .= '<h1>Read, Write, and Talk About Sports</h1>';
		  $output .= "<p class=\"main-page-sub-links\"><a href=\"\">Read More Articles</a> or <a href=\"\">Write Your Own Article</a></p>";
		  $output .= $list->DisplayList();
		  $output .= '</div>';
		

	    
		// FEATURED RATINGS
		require_once("$IP/extensions/Vote-Mag/VoteClass.php");
		require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
		$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>";
		
		$per_row = 3;
		$r = new ListRatings();
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players, Snooker Players, PGA Players, Grand Prix Drivers, IRL Drivers, NASCAR Drivers, CART Drivers, Formula One Drivers, Tennis Players, MotoGP Riders, MLB Power Rankings");
		$ratings = $r->getRatingsList(17,0,"vote_avg","desc",false);
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
		
		$rate_players = Title::makeTitle( NS_USER  , "Ratings"  );
		$rate_teams = Title::makeTitle( NS_USER  , "Ratings"  );
 
		$output .= '<div class="featured-ratings">';
			$output .= '<h1>Rate Your Favorite Players and Teams</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"{$rate_players->getFullURL()}\">Rate More Players</a> or <a href=\"{$rate_teams->getFullURL()}\">Rate More Teams</a></p>";
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
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Earn Points</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> or <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';
			
			// RECENT GIFTS
			require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
			require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
		
			$gifts = UserGifts::getAllGiftList(4);
			$gift_title = Title::makeTitle( NS_SPECIAL  , "ViewGift"  );
			foreach($gifts as $gift) {		
				$recent_gifts .= "<div class=\"recent-gift\"><a href=\"{$gift_title->getFullURL()}&gift_id={$gift["id"]}\"><img src=\"images/awards/" . Gifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" /></a> </div>";
			}
		
			$output .= '<div class="recent-gifts">';
				$output .= '<h1>Receive Gifts and Awards</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"\">Give a Gift</a> or <a href=\"\">Learn About Awards</a></p>";
				$output .= $recent_gifts;
				$output .= "<div class=\"cleared\"></div>";
			$output .= '</div>';		
		
			
			//Picture Game
			$output .= '<div class="picture-game-preview">';
				$output .= $this->getPictureGamePreview();
			$output .= '</div>';
		
			//Browse Sports Encylopedia
			$output .= '<div class="browse-encyclopedia">';
				$output .= '<h1>Browse the Sports Encyclopedia</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"\">MLB</a> - <a href=\"\">NFL</a> - <a href=\"\">NBA</a> - <a href=\"\">NHL</a></p>";
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
		  $list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions");
		  $list->setShowCount(6);
		  $list->setOrder("PublishedDate");
		  $list->setShowPublished("YES");
		  $list->setShowBlurb("300");
		  $list->setBool("ShowVoteBox","NO");
		  $list->setBool("ShowDate","YES");
		  $list->setBool("ShowStats","YES");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowNav","YES");
		  $list->setBool("ShowPic","YES");

		  $output .= "<div class=\"logged-in-articles\">";
		  $output .= '<h1 >Popular Sports Articles <span class="rss-feed"><a href="http://feeds.feedburner.com/Armchairgm"><img src=http://www.armchairgm.com/images/a/a7/Rss-icon.gif border="0"></a> rss feed</span></h1>';
		  	$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Create_Opinion\">Write An Article</a> - <a href=\"index.php?title=Category:" . date("F d, Y") . "\">Today's Articles</a> - <a href=\"index.php?title=Special:ArticlesHome\">All Popular</a></p>";
		  	$output .= $list->DisplayList();
		  $output .= '</div>';
		
		  //New Articles
		  $output .= '<div class="logged-in-articles">';
		  $list = new ListPages();
		  $list->setCategory("News, Opinions,Questions");
		  $list->setShowCount(6);
		  $list->setOrder("New");
		  $list->setShowPublished("NO");
		  $list->setShowBlurb("150");
		  $list->setBool("ShowCtg","YES");
		  $list->setBool("ShowDate","NO");
		  $list->setBool("ShowStats","yes");
		  $output .= '<h1>New Sports Articles</h1>';
		  $output .= "<p class=\"main-page-sub-links\" style=\"margin-bottom:10px;\"><a href=\"index.php?title=Create_Opinion\">Write An Article</a> - <a href=\"index.php?title=Category:" . date("F d, Y") . "\">Today's Articles</a> - <a href=\"index.php?title=Special:ArticlesHome\">All New</a></p>";
		  $output .= $list->DisplayList();
		  $output .= '</div>';
		  
		//Ratings
		require_once("$IP/extensions/Vote-Mag/VoteClass.php");
		require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
		$output .= "<script type=\"text/javascript\" src=\"extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>";

		$per_row = 3;
		$r = new ListRatings($wgUser->getName());
		$r->setCategory("MLB Players, NFL Players, NHL Players, NBA Players, Snooker Players, PGA Players, Grand Prix Drivers, IRL Drivers, NASCAR Drivers, CART Drivers, Formula One Drivers, Tennis Players, MotoGP Riders, MLB Power Rankings");
		$ratings = $r->getRatingsList(17,0,"vote_avg","desc",false);
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
			$output .= "<p class=\"main-page-sub-links\"><a href=\"{$rate_players->getFullURL()}\">Rate More Players</a> or <a href=\"{$rate_teams->getFullURL()}\">Rate More Teams</a></p>";
			$output .= $ratings_list;
		$output .= '</div>';
		
		//Browse Sports Encylopedia
		$output .= '<div class="browse-encyclopedia">';
			$output .= '<h1>Browse the Sports Encyclopedia</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"\">MLB</a> - <a href=\"\">NFL</a> - <a href=\"\">NBA</a> - <a href=\"\">NHL</a></p>";
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
		  $output .= "<h1>Friend's Activity</h1>";
		
		  //$output .= "<div class=\"user-home-feed\">";
		    require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
			require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
			require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			
			$rel = new UserActivity($wgUser->getName(),(($rel_type==1)?"friends":"foes"),5);
			
			$rel->setActivityToggle("show_votes",0);
		 
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
						$user_link_2 = "<a href=\"{$user_title_2->getFullURL()}\"><img src=\"images/avatars/{$CommentIcon} alt=\"\" border=\"0\" />{$item["comment"]}</a>";
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
	
		$output .= $this->getNewUsers(12);
		
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
				$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar_image}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
			}
			
			//CURRENT USER
			$avatar = new wAvatar($wgUser->getID(),"m");
			$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
			$user =  Title::makeTitle( NS_USER  , $wgUser->getName()  );
			
			$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar_image}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$wgUser->getName()}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($points)."</b> points</span> <span class=\"profile-on\">you</span></div>";
	
			
			foreach($fans_below as $fan) {		
				$avatar = new wAvatar($fan["user_id"],"m");
				$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
				$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
				
				$topfans .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\">{$avatar_image}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
			}		
			
			$weekly_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFansRecent"  );
			$top_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFans"  );
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Rank</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> or <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';
		}else{
			$fans = UserStats::getTopFansListPeriod(5);
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
			
			$output .= '<div class="top-fans">';
				$output .= '<h1>Weekly Points</h1>';
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->getFullURL()}\">This Week's Top Fans</a> or <a href=\"{$top_fans_title->getFullURL()}\">Complete List</a></p>";
				$output .= $topfans;
			$output .= '</div>';			
		}
		
			$output .= '<div class="top-fans">';
				$output .= $this->getCommentsOfTheDay();
			$output .= '</div>';
				
			$output .= '<div class="picture-game-preview">';
				$output .= $this->getPictureGamePreview();
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
			$output .= "<h1 class=\"user-profile-title\">Fan Networks</h1>";
			$output .= "<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">";	
			if($wgUser->getID()==$user_id) {
				$output .= "<a href=\"index.php?title=Special:UpdateFavoriteTeams\">Add More Networks</a> - ";
				$output .= "<a href=\"index.php?title=Special:SimilarFans\">Meet Similar Fans</a>";
			}
			
			$output .= "</p>";
		
			$favs = SportsTeams::getUserFavorites($user_id);
			foreach($favs as $fav){
				$homepage_title = Title::makeTitle( NS_SPECIAL  , "FanHome" );
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
				$output .= "</div>";	
			}
			
		$output .= "<div class=\"cleared\"></div>";
		
		return $output;
	}
 
}

?>

