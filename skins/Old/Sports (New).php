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
  global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP;


  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");  
  $tns=$wgTitle->getNamespace();
  
  $s = "";
  $s .= "<div id=\"container\">";
  	if (!($wgUser->isLoggedIn())) {  
        $s .= "<div id=\"topad\">";
			$s .= $this->getTribalFusionAds(90, 728, "Sports");
        $s .= "</div>";
    }
    $s .= "<div id=\"topbar\">";
    		$s .= "<div id=\"logo\">";
    			$s .= "<a href=\"index.php?title=Main_Page\"><img src=\"../../images/sports/logo.png\" alt=\"armchairgm\" border=\"0\"></a>";
    		$s .= "</div>";	
    		$s .= "<div id=\"tagline\">";
    			$s .= "all sports, all you";
    		$s .= "</div>";
    		$s .= "<div id=\"addfriends\">";
    			if ($wgUser->isLoggedIn()) {
    				$s .= "<a href=\"index.php?title=Special:InviteContacts\">+ Add Friends</a>";
    			} else {
    				$s .= "<a href=\"index.php?title=Special:UserRegister\">+ Add Friends</a>";
    			}
    			
    		$s .= "</div>";
    		$s .= "<div id=\"otherlinks\">";
    			if ($wgUser->isLoggedIn()) {
    				$s .= "<span class=\"grey\">You are Logged-In</span><br>";
    				$s .= "<a href=\"index.php?title=Special:Recentchanges\">Recent Changes</a> - <a href=\"index.php?title=Special:SiteScout\">Site Scout</a> - <a href=\"index.php?title=Special:Userlogout\">Log Out</a>";
    			} else {
    				$s .= "<span class=\"grey\">You are Not Logged-In</span><br>";
    				$s .= "<a href=\"index.php?title=Special:UserRegister\">Sign Up</a> or <a href=\"javascript:Login()\">Log In</a>"; 
    			}
    			
    		$s .= "</div>";
    		$s .= "<div class=\"cleared\"></div>";
    		$s .= "<div id=\"navtabs\">";
    	
		
		// MENU DEFINITION
		$menu[] = array(
				"display_name" => "Home",
				"page_name" =>"Main Page"
				);
		
		$menu[] = array(
				"display_name" => "Profile",
				"page_name" => "User:{$wgUser->getName()}",
				"subpages" =>
				array("Your Profile" => "User:{$wgUser->getName()}","Friend's Activities" => "Special:UserHome","User Wiki" => "UserWiki:{$wgUser->getName()}")
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
				"display_name" => "Create",
				"page_name" => "",
				"subpages" =>
					 array("Opinion" => "Create Opinion","News" => "Create News","Game Recap" => "Create Game Recap",
						 "Discussion"=> "Create Discussion","Dictionary Entry" => "Create Dictionary",
						 "Add a Blog" => "Add Blog","Movie Summary" => "Create Movie Summary","Book Summary" => "Create Book Summary")
		);
				
		$menu[] = array(
				"display_name" => "Articles",
				"page_name" => "Articles"
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
				"display_name" => "Challenges",
				"page_name" => "Special:ChallengeHistory"
		);
		
		$menu[] = array(
				"display_name" => "Meet&nbsp;People",
				"page_name" => "Meet People"
		);
		
		$menu[] = array(
				"display_name" => "Explore",
				"page_name" => "Special:Random"
		);
		
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
			if($wgOut->getPageTitle()==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($wgOut->getPageTitle(),$menu_item["subpages"])) ){
				$menu_class = "{$class_base}tabon";
			}else{
				$menu_class = "{$class_base}tab";
			}
			//If No page_name is passed, the link needs to load the submenu via JS
			//otherwise, we contruct the MW page_title to get the proper URL
			if(!$menu_item["page_name"]){
				$menu_link = "javascript:void(0);\" onclick=\"javascript:submenu({$tab_count});";
			}else{
				$page_title = Title::makeTitle( NS_MAIN  , $menu_item["page_name"]  );
				$menu_link = $page_title->getFullURL();
			}
			$s .= "<p class=\"{$menu_class}\" id=\"menu-{$tab_count}\"><a href=\"{$menu_link}\">{$menu_item["display_name"]}</a></p>";
			$tab_count++;
		}
		
		$s .= "<div class=\"cleared\"></div>";
    		$s .= "</div>";
    		$s .= "<div id=\"greenbar\">";
		
		//Sub Menu Bar
		$tab_count = 1;
		foreach($menu as $menu_item){
			if($menu_item["subpages"]){
				
				//If you are on this page, or any of its subpages, the submenu should be visible on load
				if($wgOut->getPageTitle()==$menu_item["page_name"] || ($menu_item["subpages"] && in_array($wgOut->getPageTitle(),$menu_item["subpages"])) ){
					$menu_class = "display:block;";
					$s .= "<script>last_clicked={$tab_count};</script>";
				}else{
					$menu_class = "display:none";
				}
			
				$s .= "<div class=\"submenu\" style=\"{$menu_class}\" id=\"submenu-{$tab_count}\">";
				
				//Output each subpage link
				$x = 1;
				foreach($menu_item["subpages"] as $subpage_display_name => $subpage_page_name){
					
					if($wgOut->getPageTitle()==$subpage_page_name){
						$sub_menu_class = "submenuon";
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
			
    			$s .= "<div id=\"searchbox\">";
            		$s .= $this->searchForm();
          		$s .= "</div>";
    		$s .= "</div>";
    $s .= "</div>";
  	
  	$s .= "<div id=\"middle\">";
  	if (!($wgOut->getPageTitle() == 'Main Page') && ($wgOut->isArticle()) && (NS_SPECIAL !== $wgTitle->getNamespace()) && ($tns !== NS_USER) && ($tns !== NS_USER_TALK) && ($wgTitle->getPrefixedText()!=="UserWiki:{$wgTitle->getText()}")) {
  		$s .= "<div id=\"side\">";
  			$s .= "<div class=\"editbutton\">";
  				$s .= "<img src=\"../../images/sportstweak/editIcon.gif\" border=\"0\" alt=\"\"/> <a href=\"" . $wgTitle->getFullURL() . "&action=edit\">Edit This Page</a>";
  			$s .= "</div>";
  			$s .= $this->userBox();
  			$s .= $this->thisArticle();
  		$s .= "</div>";
  		$s .= "<div id=\"main\">";
  				$s .= $this->pageTitle();
  		
  	} else if ($tns == NS_USER) {
  	
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
  			$s .= "<div class=\"user-profile-box\">";
  				$s .= '<div class="user-title-bar">';
					$s .= "<div class=\"user-title-bar-title\">{$wgTitle->getText()}</div>";
					//$s .= "<div class=\"user-title-bar-tab-large\"><a href=\"index.php?title=Special:Contributions/{$wgTitle->getText()}\">contributions</a></div>";
					$s .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?title=UserWiki:{$wgTitle->getText()}\">user wiki</a></div>";
					$s .= "<div class=\"cleared\"></div>";
				$s .= '</div>';
  				$s .= "<div class=\"user-profile-avatar\">";
  					$s .= "<img src='images/avatars/" . $avatar->getAvatarImage() . "'/>";
  				$s .= "</div>";
  				$s .= "<div class=\"user-profile-level\">";
  					$s .= "<div class=\"user-profile-data\"><b>Points</b>: <span class=\"profile-on\"> +{$stats_data["points"]}</span></div>";
  					$s .= "<div class=\"user-profile-data\"><b>Level</b>: <a href=\"{$level_link->getFullURL()}\">{$user_level->getLevelName()}</a></div>";
					$s .= "<div class=\"user-profile-data-small\">Needs <i>{$user_level->getPointsNeededToAdvance()}</i> pts. to advance to <b>{$user_level->getNextLevelName()}</b></div>";
  				$s .= "</div>";
  				$s .= "<div class=\"cleared\"></div>";
  				$s .= '<div class="user-profile-bottom">';
  				if ( $wgUser->getName() == $wgTitle->getText()  ) {
		    			$s .= '<h1>Find Friends</h1>';
		    			$s .= '<p><a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/msnIconSmall.png" alt="challenge icon" border="0"/> Hotmail</a> ';
		    			$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/yahooIconSmall.png" alt="challenge icon" border="0"/> Yahoo</a> ';
		    			$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/gmailIconSmall.png" alt="challenge icon" border="0"/> Gmail</a> ';
		    			$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/aolIconSmall.png" alt="challenge icon" border="0"/> Aol</a></p>';
		    	} else {
		    			//$s .= "<h1>Actions</h1>";
		    			$s .= "<p>";
		    				$s .= "<a href=\"\"><img src=\"../../images/common/friendRequestIcon.png\"> Add as Friend</a> ";
		    				$s .= "<a href=\"\"><img src=\"../../images/common/foeRequestIcon.png\"> Add as Foe</a> ";
		    				$s .= "<a href=\"\"><img src=\"../../images/common/icon_package_get.gif\"> Send Gift</a> ";
		    				$s .= "<a href=\"\"><img src=\"../../images/common/challengeIcon.png\"> Challenge</a> ";
		    			$s .= "</p>";
		    	}
		    	$s .= '</div>';
  			$s .= "</div>";
  			
  			//user activity
  			$s .= "<div class=\"user-activity\">";
  				$s .= $this->getUserPageActivity($wgTitle->getText());
  			$s .= "</div>";
  			
  			//articles by user
  			require_once("$IP/extensions/ListPages/ListPagesClass.php");
			
			$list = new ListPages();
			$list->setCategory("Opinions by User {$wgTitle->getText()}");
			$list->setShowCount(3);
			$list->setOrder("New");
			$list->setBool("ShowVoteBox","yes");
			$list->setBool("ShowDate","NO");
			$list->setBool("ShowStats","NO");
			$list->setBool("ShowNav","NO");
		
			$s .= "<div class=\"user-page-articles\">";
				$s .= '<div class="user-title-bar">';
					$s .= "<div class=\"user-title-bar-title\">My Articles</div>";
					$s .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Category:Opinions by User {$wgTitle->getText()}\">view all</a></div>";
					$s .= "<div class=\"cleared\"></div>";
				$s .= '</div>';
				$s .= $list->DisplayList();
			$s .= "</div>";
  			
  			//user profile
  			$s .= "<div class=\"user-profile\">";
				$s .= $this->getUserProfile($wgTitle->getText());
			$s .= "</div>";
  			
  			//user stats
  			$s .= "<div class=\"user-stats\">";
				$s .= $this->getUserStats($id,$wgTitle->getText());
			$s .= "</div>";
  			
  		$s .= '</div>';
  		$s .= '<div class="user-right">';
  			$s .= "<div class=\"user-friends\" style=\"margin-bottom:15px\">";
  				$s .= $this->getUserPageRelationships($wgTitle->getText(),1);
  			$s .= "</div>";
  			$s .= "<div class=\"user-foes\">";
  				$s .= $this->getUserPageRelationships($wgTitle->getText(),2);
  			$s .= "</div>";
  			$s .= "<div class=\"user-gifts\">";
  				$s .= $this->getUserPageGifts($wgTitle->getText());
  			$s .= "</div>";
  		$s .= '</div>';
  		$s .= "<div class=\"cleared\"></div>";
  	}
  
       
  return $s;
  
}
 
 function doAfterContent() {
 
  global $wgOut, $wgUser;
  
  $s = "";
  
  if (!($wgOut->getPageTitle() == 'Main Page') && ($wgOut->isArticle()) && (NS_SPECIAL !== $wgTitle->getNamespace())) {
  	$s .= "</div>";
  	$s .= "<div class=\"cleared\"></div>";
  }
  $s .= "</div>";
  $s .= "<div id=\"footer\">";
  	$s .= $this->getFooter();
  $s .= "</div>";
  $s .= "</div>";
  
  
   
  return $s;
 }
 
 function getMainPage(){
  
  global $wgUser, $IP;
  
  require_once ("$IP/extensions/ListPages/ListPagesClass.php");
  
  $output = "";
  $dates_array = get_dates_from_elapsed_days(2);
  $date_categories = "";
  foreach ($dates_array as $key => $value) {
	if($date_categories)$date_categories .=",";
	$date_categories .= str_replace(",","\,",$key);
  }
  
  if (!($wgUser->isLoggedIn())) {
	  
	 $dbr =& wfGetDB( DB_SLAVE );
	 $s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
	 $vote_count = number_format($s->count);
	 $s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
	 $comment_count = number_format($s->count);
	 $edits_count = number_format(SiteStats::edits());
	 $good_count = number_format(SiteStats::articles());
  	$output .= '<div class="welcome-message">';
		$output .= '<h1>welcome to ArmchairGM!</h1>';
  		$output .= '<p>ArmchairGM is a <b>completely free</b> community for passionate sports fanatics.</p>';
  		$output .= '<p class="welcome-message-cell"><img src="images/common/pagesIcon.gif" border="0"/> <b>pages</b>: ' . $good_count . '</p>';
  		$output .= '<p class="welcome-message-cell"><img src="images/common/editIcon.gif" border="0"/> <b>edits</b>: ' . $edits_count . '</p>';
  		$output .= '<div class="cleared"></div>';
		$output .= '<p class="welcome-message-cell"><img src="images/common/voteIcon.gif" border="0"/> <b>votes</b>: ' . $vote_count . '</p>';
  		$output .= '<p class="welcome-message-cell"><img src="images/common/commentsIcon.gif" border="0"/> <b>comments</b>: ' . $comment_count . '</p>';
		$output .= '<div class="cleared"></div>';
		$output .= '<p class="welcome-big-link"><a href="index.php?title=Special:UserRegister">start here >></a></p>';
		$output .= '</div>';
		
  $output .= '</div>';
  } else {
  $output .= '<div class="profilebox">';
  $output .= '<h1> '. wgGetWelcomeMessage() .'</h1>';
  $avatar = new wAvatar($wgUser->mId,"l");
  $output .= '<div id="profileboximage">';
  if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
    $output .= '<p>' . $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . ' width="50" height="50"/>').'</p>';
  } else {
    $output .= '<p>' . $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . '/>') . '</p>';
  }

  $output .= '</div>';
  $output .= '<div id="profileboxlinks">';
  $output .= 	$this->getMainPageUserBox($avatar);
  $output .= '</div>';
  $output .= '<div class="cleared"></div>';
  $output .= '</div>';
  }
  
  $list = new ListPages();
  $list->setCategory("News, Opinions,Questions");
  $list->setShowCount(10);
  $list->setOrder("New");
  $list->setShowPublished("NO");
  $list->setBool("ShowVoteBox","yes");
  $list->setBool("ShowDate","NO");
  $list->setBool("ShowStats","NO");
  
  $output .= '<div class="smallList">';
  $output .= '<h1>just created</h1>';
  $output .= $list->DisplayList();
  $output .= '</div>';
  
  $list = new ListPages();
  $list->setCategory($date_categories);
  $list->setShowCount(5);
  $list->setOrder("Comments");
  $list->setShowPublished("Yes");
  $list->setBool("ShowNav","No");
  $list->setBool("ShowCommentBox","yes");
  $list->setBool("ShowDate","NO");
  $list->setBool("ShowStats","NO");
  $list->setLevel(1);

  #top recent comments
  $output .= '<div class="smallList">';
  $output .= '<h1>what people are talking about</h1>';
  $output .= $list->DisplayList();
  $output .= '</div>';

  $output .= $this->getCommentsOfTheDay();
  
  $list = new ListPages();
  $list->setCategory("Lockerroom");
  $list->setShowCount(5);
  $list->setOrder("New");
  $list->setShowPublished("NO");
  $list->setBool("ShowVoteBox","No");
  $list->setBool("ShowDate","NO");
  $list->setBool("ShowStats","NO");
  
  $output .= '<div class="smallList">';
  $output .= '<h1>latest open discussions</h1>';
  $output .= $list->DisplayList();
  $output .= '</div>';
  
  
  return $output;

 }
 
   function searchForm( $label = "" ) {
    global $wgRequest;
  
    $search = $wgRequest->getText( 'search' );
    $action = $this->escapeSearchLink();
  
    $s = "<form id=\"search\" method=\"get\" class=\"inline\" action=\"$action\">";
  
    if ( "" != $label ) { $s .= "{$label}: "; }
    $s .= "<div id=\"searchinput\">";
    	$s .= "<input type='text' name=\"search\" size='20' value=\"Players, Teams, Sports\" / onclick=\"this.value=''\"> ";
    $s .= "</div>";
    $s .= "<div id=\"searchinput\">";
    	$s .= "<input class='button' type='image' src='../images/sportstweak/search.png' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" />";
    $s .= "</div>";
    $s .= "<div class=\"cleared\"></div>";
    $s .= "</form>";
  
    return $s;
  }
 
}

?>
