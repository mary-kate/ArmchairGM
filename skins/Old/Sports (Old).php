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
  
foreach($wgOut->getCategoryLinks() as $ctg){$category_text[]=strip_tags($ctg);}

if (is_array($category_text) && in_array('MLB Players',$category_text ) ){
	//echo "this is an mlb player";
}

if (is_array($category_text) && in_array('Opinions',$category_text ) ){
	//echo "this is an opinion";
}

/*
require_once "$IP/extensions/wikia/Facebook/appinclude.php";
$appapikey = 'c9afec6b80a42faf5746a906175ad608';
$appsecret = '6ee2d898e1b11ae9a669933677e51efb';
$facebook = new Facebook($appapikey, $appsecret);
$facebook->api_client->session_key =  "9fa8e1bbbb43540aef6af3ad-582436954";
$facebook->api_client->fbml_refreshRefUrl("http://www.armchairgm.com/index.php?title=Special:FacebookGetOpinions&id=598902062");
*/


  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");  
  $tns=$wgTitle->getNamespace();
  
  $s = '';
  $s .= $this->InviteBox();
  
  $s .= '<div id="container">';
    if (!($wgUser->isLoggedIn())) {  
        $s .= '<div id="topad">';
	$s .= $this->getTribalFusionAds(90, 728, "Sports");
        $s .= '</div>';
    }
       $s .= '<div id="login">';
	  if ( $wgUser->isLoggedIn() ) {
	  $s .= $this->makeKnownLink( $lo, "Log-out", $q );
	  } else {
	  $s .= '<a href="javascript:Login()">Log-in</a>';
	  }
	$s .= '</div>'; #end login
	
	$s .= '<div id="top">';
          $s .= '<div id="logo">';
            $s .= '<a href="index.php?title=Main_Page"><img src="images/sports/logo.png" alt="logo" border="0"/></a>';
          $s .= '</div>';
          $s .= '<div id="toplinks">';
            $s .= '<a href="index.php?title=Main_Page">home</a> - <a href="index.php?title=Special:Recentchanges">recent changes</a> - <a href="index.php?title=Special:SiteScout">site scout</a> - ';
	    $s .= '<a href="index.php?title=Top_Stuff">top stuff</a> - ';
	    $s .= '<a href="index.php?title=Help">help</a>';
	    if ( $wgUser->isLoggedIn() ) {
		$s .= ' - <a href="index.php?title=Category:Lockerroom">locker room</a>';    
	    }
          $s .= '</div>';
          $s .= '<div id="search">';
            $s .= $this->searchForm();
          $s .= '</div>';
	  $s .= '<div class="cleared"></div>';
        $s .= '</div>'; #end top
    
	$s .= '<div id="navbar">';
          $s .= '<div id="leftNavLeft">';
            $s .= '<img src="images/sports/leftNavLeft.png" alt="logo" border="0"/>';
          $s .= '</div>';
	  /*
          $s .= '<div id="leftNavMiddle">';
            $s .= '<ul>';
	      $s .= '<li><a href="index.php?title=MLB">MLB</a></li>';
	      $s .= '<li><a href="index.php?title=NFL">NFL</a></li>';
	      $s .= '<li><a href="index.php?title=NBA">NBA</a></li>';
	      $s .= '<li><a href="index.php?title=NHL">NHL</a></li>';
	      $s .= '<li><a href="index.php?title=CBB">College Basketball</a></li>';
	      $s .= '<li><a href="index.php?title=CFB">College Football</a></li>';
	      $s .= '<li><a href="index.php?title=Soccer">European Football</a></li>';
	      $s .= '<li><a href="index.php?title=Nascar">NASCAR</a></li>';
	      $s .= '<li><a href="index.php?title=Other">Other</a></li>';
            $s .= '</ul>';
         $s .= '</div>';
	 */
          $s .= '<div id="leftNavMiddle">';
            $s .= '<ul>';
	      $s .= '<li><a href="index.php?title=MLB">Sports</a></li>';
	      $s .= '<li><a href="index.php?title=NFL">Articles</a></li>';
	      $s .= '<li><a href="index.php?title=NBA">Encyclopedia</a></li>';
	      $s .= '<li><a href="index.php?title=NHL">Rate Stuff</a></li>';
	      $s .= '<li><a href="index.php?title=CBB">Challenges</a></li>';
	      $s .= '<li><a href="index.php?title=CFB">Top Users</a></li>';
	      $s .= '<li><a href="index.php?title=Soccer">Invite Friends</a></li>';
            $s .= '</ul>';
         $s .= '</div>';	 
         $s .= '<div id="leftNavRight">';
           $s .= '<img src="images/sports/leftNavRight.png" alt="logo" border="0"/>';
         $s .= '</div>';
         $s .= '<div id="rightNav">';
	    $s .= '<img src="images/common/pencilnav.gif" alt="logo" border="0"/> <a href="index.php?title=Create_Opinion">write a sports article</a>';
          $s .= '</div>';
	  $s .= '<div class="cleared"></div>';
        $s .= '</div>';  #end navbar
    
	$s .= '<table cellspacing=5 style="padding-bottom:5px;color:#666666;font-size:11px">';
            $s .= '<tr>';
	    $s .= '<td><b>Poo Nav:</b> </td>';
	    $s .= '<td><a href="index.php?title=MLB" style="color:#666666">MLB</a></td>';
	      $s .= '<td><a href="index.php?title=NFL" style="color:#666666">NFL</a></td>';
	      $s .= '<td><a href="index.php?title=NBA"style="color:#666666">NBA</a></td>';
	      $s .= '<td><a href="index.php?title=NHL" style="color:#666666">NHL</a></td>';
	      $s .= '<td><a href="index.php?title=CBB" style="color:#666666">CBB</a></td>';
	      $s .= '<td><a href="index.php?title=CFB" style="color:#666666">CFB</a></td>';
	      $s .= '<td><a href="index.php?title=Soccer" style="color:#666666">European Football</a></td>';
	      $s .= '<td><a href="index.php?title=Nascar" style="color:#666666">NASCAR</a></td>';
	      $s .= '<td><a href="index.php?title=Other" style="color:#666666">Other</a></td>';
            $s .= '</tr>';
         $s .= '</table>';	
	if ($wgOut->getPageTitle() !== 'Main Page') {
	  $type = "sub";
	  $s .= '<div id="side">';
	 
	  $s .= $this->userBox();
	  $s .= $this->thisArticle();
	  
	  if ( $wgUser->isLoggedIn()) {
	    $s .= '<div class="topside"><div class="sidetitle" style="color:#78BA5D;">share the chair</div></div>';
	    $s .= '<div class="middleside">';
	    $s .= '<p style="font-weight:bold;">';
	    $s .= '<img src="images/common/friendRequestIcon.png" border="0" alt="invite friend"> <a href="index.php?title=Special:InviteContacts">Invite Your Friends!</a>';
	    $s .= '</p>';
	    $s .= '</div>';
	    $s .= '<div class="bottomside"></div>';
	  }
	  
	    $s .= $this->shareBox();
	  
	  if (!($wgUser->isLoggedIn())) {
		  $s .= '<div id="sideads">' . "\n";
		  $s .= $this->getGoogleAds(600, 120);
		  $s .= '</div>';
	  }
	  
	$s .= '</div>'; #end side
	} else {
	  $type = "main";
	}
	
      $s .= '<div id="main">';
      if ($tns == NS_USER || $tns == NS_USER_TALK || ($wgTitle->getPrefixedText()=="UserWiki:{$wgTitle->getText()}") || ($wgTitle->getPrefixedText()=="Special:UserHome")) {
		
	
	    $page_title = $wgTitle->getText();
	    $title_parts = explode("/",$page_title);
	    $user = $title_parts[0];
	
	    $id=User::idFromName($user);
	    $relationship = UserRelationship::getUserRelationshipByID($id,$wgUser->getID());
	    
	    $s .= '<div class="top'. $type .'">';
	      $s .= '<div id="userpageAvatar">';
	        $avatar = new wAvatar($id,"l");
		$avatarhome = new wAvatar($wgUser->getID(),"l");
		if ($wgTitle->getPrefixedText()=="Special:UserHome") {
			$s .= "<img src='images/avatars/" . $avatarhome->getAvatarImage() . "'/>";		
		} else {
			$s .= "<img src='images/avatars/" . $avatar->getAvatarImage() . "'/>";
		}
		if (($wgUser->getName() == $wgTitle->getText()) || ($wgTitle->getPrefixedText()=="Special:UserHome"))  {
		    if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
			    $s .= '<div class="add-avatar"><a href=index.php?title=Profile_Image>(add image)</a></div>';
		    } else {
		      $s .= '<div class="update-avatar"><a href=index.php?title=Profile_Image>(update image)</a></div>';
		    }
		}
	      $s .= '</div>';
	      $s .= '<div id="userpageTop">';
	      	if ($wgTitle->getPrefixedText()=="UserWiki:{$wgUser->getName()}") {
		       $s .= "<h1 class=\"pagetitle\">Your Wiki Page</h1>";
      		} else if ($wgUser->getName() == $wgTitle->getText()) {
			$s .= "<h1 class=\"pagetitle\">Your Profile</h1>";
	        } else {
			$s .= $this->pageTitle();
		}
		$s .= '<span class="edit"><a href="' . $wgTitle->getFullURL() . '&action=edit"><img src="images/common/editPageIcon.gif" alt="logo" border="0"/> edit</a></span>';
		$s .= '<div id="usertoplinks">';
		
	          if ( NS_SPECIAL !== $wgTitle->getNamespace() && (!($wgUser->getName()== $wgTitle->getText()))) {
                    if ((!($relationship==2)) && ($relationship == false))  {
		      $s .= '<span class="profile-on"><a href="index.php?title=Special:AddRelationship&user='.$wgTitle->getText().'&rel_type=1">Friend me</a></span> - ';
		    }
		    if (!($relationship==1) && ($relationship == false)) {
		      $s .= '<a href="index.php?title=Special:AddRelationship&user='.$wgTitle->getText().'&rel_type=2">Foe me</a> - ';
		    }
		    if ($relationship==true) {
		      if ($relationship == 1) {
			$s .= "<span class=\"profile-on\"><b>Your Friend</b></span> - ";	    
		      }
		      if ($relationship == 2) {
			$s .= "<span class=\"profile-on\"><b>Your Foe</b></span> - ";      
		      }
	            }
		    $s .= '<a href="index.php?title=Special:GiveGift&user='.$wgTitle->getText().'">Give a Gift</a> - ';
		    	if (!($wgTitle->getPrefixedText()=="UserWiki:{$wgTitle->getText()}")) {
					$s .= $this->talkLink() . ' - ';
				} else {
					$s .= "<a href=\"index.php?User_talk:{$wgTitle->getText()}\">".wfMsg( 'talkpage' )."</a> - ";
				}
                  }
	          if ($id != 0) {
		    $s .=  "x".$this->userContribsLink();
	          }
	          if( $this->showEmailUser( $id ) && (!($wgUser->getName()== $wgTitle->getText()))) {
		    $s .= ' - ' . $this->emailUserLink();      
	          }
		  if( !($wgUser->getName()== $wgTitle->getText())){
			      $s .= '- <a href="index.php?title=Special:ChallengeUser&user='.$wgTitle->getText().'">Challenge me</a>';
		  }
	        $s .= '</div>';
	      $s .= '</div>';
	      $s .= '<div class="cleared"></div>';
	   $s .= '</div>';
	   $s .= '<div class="cleared"></div>';
     } else {
       $s .= '<div class="top'. $type .'">';
         if ($wgOut->getPageTitle() !== 'Main Page') {
	   $s .= $this->pageTitle();
	   if ($this->subPageSubtitle()) {
	     $s .= '<p class="subtitle">'.$wgOut->getSubtitle().$this->subPageSubtitle().'</p>';
	   }
	 }
	
	 if (($wgOut->isArticle()) && ($wgOut->getPageTitle() !== 'Main Page') && (NS_SPECIAL !== $wgTitle->getNamespace()) && ($wgTitle->getPrefixedText() !== "User:{$wgTitle->getText()}")) {
	   $s .= '<span class="edit"><a href="' . $wgTitle->getFullURL($this->editUrlOptions()) . '"><img src="images/common/editPageIcon.gif" alt="logo" border="0"/> edit</a></span>';
	  
	   $edits_views = $_SESSION["edits_views"];
	   $page_edits_views = $edits_views[$wgArticle->getID()];
	   if( $page_edits_views==1 && $wgUser->isLoggedIn()){
	
		   $invite_title = Title::makeTitle( NS_SPECIAL , "InviteEmail"  );
		   $s .= '<span id="invite_to_edit" class="edit" style="display:none;background-color:#FFFB9B"><a href="' . $invite_title->getFullURL() . '&email_type=edit&page=' . $wgTitle->getText() . '">invite friend to edit</a></span>
		   <script>
		   Effect.Appear($("invite_to_edit"),{duration:3.0} )
		   </script>
		   ';
		   $edits_views[$wgArticle->getID()] = $page_edits_views + 1;
		   $_SESSION["edits_views"] = $edits_views;
	   }
	   /*
	   if( $_SESSION["new_opinion"] == 1){
	
		   $invite_title = Title::makeTitle( NS_SPECIAL , "InviteEmail"  );
		   $s .= '<span id="invite_to_read" class="edit" style="display:none;background-color:#FFFB9B"><a href="' . $invite_title->getFullURL() . '&email_type=view&page=' . $wgTitle->getText() . '">invite friend to read</a></span>
		   <script>
		   Effect.Appear($("invite_to_read"),{duration:3.0} )
		   </script>
		   ';
		   $_SESSION["new_opinion"] = 0;
	   }
	   */
	 }
	 $s .= '</div>';      
     }
     
     $s .= '<div class="middle'. $type .'">';
     
     if ($tns == NS_USER || $tns == NS_USER_TALK || ($wgTitle->getPrefixedText()=="UserWiki:{$wgTitle->getText()}") || ($wgTitle->getPrefixedText()=="Special:UserHome")) {
	     $s .= "<div class=\"user-page-tabs\">";
			
			//Profile Button
			if ($wgTitle->getPrefixedText()=="User:{$wgTitle->getText()}") {
				$s .= "<p id=\"user-tab-on\"><a href=\"index.php?title=User:{$wgTitle->getText()}\">Your Profile</a></p>";
			} else if ($wgTitle->getPrefixedText()=="Special:UserHome") {
				$s .= "<p id=\"user-tab-off\"><a href=\"index.php?title=User:{$wgUser->getName()}\">Profile</a></p>";
			} else {
				$s .= "<p id=\"user-tab-off\"><a href=\"index.php?title=User:{$wgTitle->getText()}\">Profile</a></p>";
			}
			
			//Your Home Buton
			if (($wgUser->getName() == $wgTitle->getText()) || ($wgTitle->getPrefixedText()=="Special:UserHome")) {
				if ($wgTitle->getPrefixedText()=="Special:UserHome") {
					$s .= "<p id=\"user-tab-on\"><a href=\"index.php?title=Special:UserHome\">Your Home</a></p>";
				} else {
					$s .= "<p id=\"user-tab-off\"><a href=\"index.php?title=Special:UserHome\">Your Home</a></p>";
				}
			}
			
			//WikiPage
			if ($wgTitle->getPrefixedText()=="UserWiki:{$wgTitle->getText()}") {
				$s .= "<p id=\"user-tab-on\"><a href=\"index.php?title=UserWiki:{$wgTitle->getText()}\">Wiki Page</a></p>";
			} else if ($wgTitle->getPrefixedText()=="Special:UserHome") {
				$s .= "<p id=\"user-tab-off\"><a href=\"index.php?title=UserWiki:{$wgUser->getName()}\">Wiki Page</a></p>";
			} else {
				$s .= "<p id=\"user-tab-off\"><a href=\"index.php?title=UserWiki:{$wgTitle->getText()}\">Wiki Page</a></p>";
			}
			$s .= "<p class=\"user-tab-end\"></p>";
			$s .= "<div class=\"cleared\"></div>";
		$s .= "</div>";
		$s .= "<div class=\"cleared\"></div>";
     }
     
     if ($tns == NS_USER){

	     	require_once("$IP/extensions/ListPages/ListPagesClass.php");
		
	        $s .= "<div style=\"float:left\">";
			
			$list = new ListPages();
			$list->setCategory("Opinions by User {$wgTitle->getText()}");
			$list->setShowCount(3);
			$list->setOrder("New");
			$list->setBool("ShowVoteBox","yes");
			$list->setBool("ShowDate","NO");
			$list->setBool("ShowStats","NO");
			$list->setBool("ShowNav","NO");
		
			$s .= "<div class=\"user-page-articles\">";
				$s .= "<div class=\"user-page-articles-title\">my latest opinions <span class=\"profile-page-update-link\">[<a href=\"index.php?title=Category:Opinions by User {$wgTitle->getText()}\">archive</a>]</span></div>";
				$s .= $list->DisplayList();
			$s .= "</div>";
			
			$s .= $this->getUserPageRelationships($wgTitle->getText(),1);
			$s .="<div class='cleared'></div>";
			$s .= $this->getUserPageRelationships($wgTitle->getText(),2);
			$s .="<div class='cleared'></div>";
			$s .= $this->getUserPageGifts($wgTitle->getText());
			$s .="<div class='cleared'></div>";
			
		$s .= "</div>";
		
		$s .= "<div style=\"float:left; margin-left:20px;\">";
			$s .= $this->getUserLevel($id,$wgTitle->getText());
			$s .= $this->getUserProfile($wgTitle->getText());
			$s .= $this->getUserStats($id,$wgTitle->getText());
			$s .= $this->getUserPageActivity($wgTitle->getText());
		$s .= "</div>";
		$s .="<div class='cleared'>&nbsp;</div>";
     }
     
  return $s;
  
}
 
 function doAfterContent() {
 
  global $wgOut, $wgUser;
  
  if ($wgOut->getPageTitle() !== 'Main Page') {
	  $type = "sub";
	} else {
	  $type = "main";
	}
     $cat = $this->getCategoryLinks();
     if( $cat ) $s .= "<div id=\"categories\">$cat</div>";
     $s .= '</div>'; #end middle
     $s .= '<div class="bottom'. $type .'"></div>';
     $s .= '<div class="footer'. $type .'">';
      $s .= $this->getFooter();
     $s .= '</div>';
     
     if ( $wgOut->isArticle() ) {
       $s .= '<div class="top'. $type .'"></div>';
       $s .= '<div class="rssfooter'. $type .'">';
       $s .= rsspagefooter();
       $s .= '</div>';
       $s .= '<div class="bottom'. $type .'"></div>';
     }
     
   $s .= '</div>'; #end main
  
  $s .=  '</div>'; #end container
   
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
  
  $output .= '<div id="buttons">';
  $output .= '<p><a href="index.php?title=Create_Opinion"><img src="images/sports/createicon.png" alt="icon" border="0"/> write a sports article</a></p>';
  $output .= '<p><a href="index.php?title=Sports_Encyclopedia"><img src="images/sports/encyclopediaicon.png" alt="icon" border="0"/> add to our sports encyclopedia</a></p>';
  $output .= '</div>';
  
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
    $s .= "<div id='searchbutton'>";
    $s .= "<input class='button' type='image' src='../images/common/search.gif' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" />";
    $s .= "</div>";
    $s .= "<div style='float:right;'>";
    $s .= "<input type='text' name=\"search\" size='18' value=\"" . htmlspecialchars(substr($search,0,256)) . "\" /> ";
    $s .= '</div>';
    $s .= "</form>";
  
    return $s;
  }
 
}

?>
