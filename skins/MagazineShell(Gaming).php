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
 

 
 class SkinMagazineShell extends Skin {
	 
	 function getBodyOptions() {
		global $wgUser, $wgTitle, $wgOut, $wgRequest, $wgContLang;

		extract( $wgRequest->getValues( 'oldid', 'redirect', 'diff' ) );

		/*if ( 0 != $wgTitle->getNamespace() ) {
			$a = array( 'bgcolor' => '#ffffec' );
		}
		else*/
		$a = array( 'bgcolor' => '#FFFFFF' );
		if($wgOut->isArticle() && $wgUser->getOption('editondblclick') &&
		  $wgTitle->userCan( 'edit' ) ) {
			$s = $wgTitle->getFullURL( $this->editUrlOptions() );
			$s = 'document.location = "' .wfEscapeJSString( $s ) .'";';
			$a += array ('ondblclick' => $s);

		}
		$a['onload'] = $wgOut->getOnloadHandler();
		if( $wgUser->getOption( 'editsectiononrightclick' ) ) {
			if( $a['onload'] != '' ) {
				$a['onload'] .= ';';
			}
			$a['onload'] .= 'setupRightClickEdit()';
		}
		$a['class'] = 'ns-'.$wgTitle->getNamespace().' '.($wgContLang->isRTL() ? "rtl" : "ltr").
		' '.Sanitizer::escapeClass( 'page-'.$wgTitle->getPrefixedText() );
		return $a;
	}
	
	function bottomScripts() {
		$r .= '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
			<script src="/skins/common.js" type="text/javascript"></script>';
		$r .= '<script type="text/javascript">' . "\n";
		$r .= '_uacct = "UA-1328449-1";' . "\n";
		$r .= 'urchinTracker();' . "\n";
		$r .= '</script>' . "\n";
		
		//return $r;
	}
	
	/**
	* Ashish: Get picure game preview
	*/
	function getPictureGamePreview(){
		$mt = new PictureGameHome();
		return $mt->getMiniGame();
	}
	
	/**
	* Ashish: Mini-gallery on the user page
	*/
	function getMiniGallery($username){
		global $IP;
		include $IP . "/skins/minigallery.php";
		return $output;
	}
	
  	/**
	 * Return html code that include User stylesheets
	 */
	function getUserStyles() {
		$s = "<style type='text/css'>\n";
		$s .= "/*/*/ /*<![CDATA[*/\n"; # <-- Hide the styles from Netscape 4 without hiding them from IE/Mac
		$s .= $this->getUserStylesheet();
		$s .= "/*]]>*/ /* */\n";
		$s .= "</style>\n";
		
		$s .= "<!--[if IE]><style type=\"text/css\" media=\"all\">@import \"skins/common/commonie.css\";</style><![endif]-->\n";
		return $s;
	}
	
	function getGoogleAds($height, $width) {
		  $s = '';
		  $s .= '<script type="text/javascript"><!--' . "\n";
		  $s .= 'google_ad_client = "ca-pub-4086838842346968";' . "\n";
		  $s .= 'google_ad_width = '.$width.';' . "\n";
		  $s .= 'google_ad_height = '.$height.';' . "\n";
		  $s .= 'google_ad_format = "'.$width.'x'.$height.'_as";' . "\n";
		  $s .= 'google_ad_type = "text";' . "\n";
		  $s .= '//2006-12-04: wiki' . "\n";
		  $s .= 'google_ad_channel = "8721043353+0098152242+0152562336+4900065124";' . "\n";
		  $s .= 'google_color_border = "ffffff";' . "\n";
		  $s .= 'google_color_bg = "FFFFFF";' . "\n";
		  $s .= 'google_color_link = "' . $wgSiteView->view_border_color_1 . '";' . "\n";
		  $s .= 'google_color_text = "000000";' . "\n";
		  $s .= 'google_color_url = "' . $wgSiteView->view_border_color_2 . '";' . "\n";
		  $s .= '//--></script>' . "\n";
		  $s .= '<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">' . "\n";
		  $s .= '</script>' . "\n";
		  return $s;
	}

	function getTribalFusionAds($height, $width, $site) {
		  	$s = "";
		  	$s .= "<script type=\"text/javascript\">\n";
			$s .= "e9 = new Object();\n";
			$s .= "e9.size = \"{$width}x{$height},468x60\";\n";
			$s .= "e9.noAd = 1;\n";
			$s .= "</script>\n";
			$s .= "<script type=\"text/javascript\"\n";
			$s .= "src=\"http://tags.expo9.exponential.com/tags/Wikiacom/{$site}/tags.js\">\n";
			$s .= "</script>";
		  
		  //$s .= "<!-- TF {$width}x{$height} JScript VAR NoAD code -->\n";
		  //$s .= "<center><script language=javascript><!--\n";
		  //$s .= "document.write('<scr'+'ipt language=javascript src=\"http://a.tribalfusion.com/j.ad?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&noAd=1&requestID='+((new Date()).getTime() % 2147483648) + Math.random()+'\"></scr'+'ipt>');\n";     
		  //$s .= "//-->\n";
		  //$s .= "</script>\n";
		  //$s .= "<noscript>\n\n";
		  //$s .= "<a href=\"http://a.tribalfusion.com/i.click?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&requestID=995263280\" target=_blank>\n\n";
		  //$s .= "<img src=\"http://a.tribalfusion.com/i.ad?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&requestID=995263280\" width=$width height=$height border=0 alt=\"Click Here\"></a>\n\n";
		  //$s .= "</noscript>\n\n";
		  //$s .= "</center>\n";
		  //$s .= "<!-- TF {$width}x{$height} JScript VAR NoAD code -->";
		  return $s;
	}
	
	function getFooter() {
		  $s = '';
		  $s .= '<p>';
			  $s .= '<a href="index.php?title=Main_Page">Home</a>';
			  $s .= '<a href="index.php?title=About">About</a>';
			  $s .= '<a href="index.php?title=Special:Specialpages">Special Pages</a>';
			  $s .= '<a href="index.php?title=Help">Help</a>';
			  $s .= '<a href="http://www.wikia.com/wiki/Terms_of_use">Terms of Use</a>';
			  $s .= '<a href="http://www.federatedmedia.net/authors/wikia">Advertise</a>';
		  $s .= '</p>';
		  $s .= '<p>';
		  $s .= 'Wikia&reg; is a registered service mark of Wikia, Inc. All rights reserved.';
		  $s .= '</p>';
		  $s .= '<p>';
		  $s .= '<a href="http://www.gnu.org/copyleft/fdl.html"><img src="images/common/gnu-fdl.png" alt="GNU-FDL" border="0"/></a>';
		  $s .= '<a href="http://www.mediawiki.org"><img src="images/common/poweredby_mediawiki_88x31.png" alt="powered by mediawiki" border="0"/></a>';
		  $s .= '</p>';
		  return $s;
	}
	
	
	function userBox() {
		global $wgUser, $wgChallengesEnabled;
		
		$stats = new UserStats($wgUser->getID(), $wgUser->getName());
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		$user_name = ($wgUser->mName == substr($wgUser->mName, 0, 12) ) ? 
							 $wgUser->mName : ( substr($wgUser->mName, 0, 12) . "...");
		
		$s = "";
		if ( $wgUser->isLoggedIn() ) {
		  $avatar = new wAvatar($wgUser->getID(),"l");
		  
		  $s .= '<div class="user-box">';
		  
		  $s .= '<div class="avatar-messages">';
		  	$s .= '<h1>' . $user_name .'</h1>';
		  	$s .= $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . ' style="border:1px solid #d7dee8;"/>');
		  		$s .= '<p><span class="upload-link">';   
		    	if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
			    	$s .= '<a href=index.php?title=Special:UploadAvatar>(add image)</a>';
		    	} else {
		      		$s .= '<a href=index.php?title=Special:UploadAvatar>(new image)</a>';
		    	}
		    $s .= '</span></p>';
		    
		    //New Messages 
		    $new_messages = UserBoard::getNewMessageCount($wgUser->getID());
		    if ( $new_messages ) {
			    	$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
				$s .= '<p>';
				$s .= '<img src="images/common/emailIcon.png" alt="email icon" border="0"/> ';
				$s .= '<span class="profile-on"><a href="' . $board_link->getFullURL() . '">New Message</a></span>';
				$s .= '</p>';
		    }
	      
		    //New Relationship, Gift, System Gift Links
		    $s .= $this->getRelationshipRequestLink();
		    $s .= $this->getNewGiftLink();
		    $s .= $this->getNewSystemGiftLink(); 
		    
		  $s .= '</div>';
		  
		  $s .= "<div class=\"user-points\">";
		  	$s .= "<p class=\"point-total\"><span class=\"profile-on\">+{$stats_data["points"]}</span> points</p>";
		    $s .= "<p><a href=\"{$level_link->getFullURL()}\">({$user_level->getLevelName()})</a></p>";
		  $s .= "</div>";  
		  
		  //$s .= "<div class=\"user-links\">";
		  	//$s .= '<p><img src="images/common/userpageIcon.png" alt="userpage icon" border="0"/> '.$this->makeKnownLinkObj( $wgUser->getUserPage(), "Profile").'</p>';
		    //$s .= '<p><img src="images/common/homeIcon.png" alt="userpage icon" border="0"/> <a href="index.php?title=Special:UserHome">Friend\'s Activity</a></p>';
		  //$s .= "</div>";
		  
		  $s .= "<div class=\"user-friends-links\">";
		  	$s .= '<h1>Add Friends</h1>';
		    	$s .= '<p>';
		    		$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/msnIconSmall.png" alt="challenge icon" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/yahooIconSmall.png" alt="challenge icon" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/gmailIconSmall.png" alt="challenge icon" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/aolIconSmall.png" alt="challenge icon" border="0"/></a>';
		    	$s .= '</p>';
		  $s .= "</div>";
		         
		$s .= '</div>';		  
		  
		  
		}
		return $s;
	}

	function shareBox() {
		  global $wgTitle;
		  $s = '';
		  $s .= '<div class="side-box"><h1>Share</h1>';
		  $s .= "<p><script>function fbs_click() {u=location.href;t=document.title;window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');return false;}</script><a href=\"javascript:void(0)\" onclick=\"return fbs_click()\" target=\"_blank\" class=\"fb_share_link\">Facebook</a></p>";
		    $s .= '<p><a href="http://digg.com/submit?phase=2&amp;url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'"><img src="images/common/diggIcon.png" alt="digg icon" border="0"/> Digg</a></p>';
		    $s .= '<p><a href="http://reddit.com/submit?url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'""><img src="images/common/redditIcon.png" alt="reddit icon" border="0"/> Reddit</a></p>';
		    $s .= '<p><a href="http://del.icio.us/post"><img src="images/common/deliciousIcon.png" alt="delicious icon" border="0"/> Delicious</a></p>';
		    $s .= '</div>';
		  return $s;
	}

	function thisArticle() {
		  global $wgOut, $wgTitle, $wgUser;
		  $s = "";
		  if ( $wgOut->isArticle() ) {
		     $s .= "<div class=\"page-tools\"><h1>Tools</h1>";
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
		       $s .= '</div>';
		  }
		  return $s;
	}
	
	function getMainPageUserBox($avatar){
		global $wgUser,$wgChallengesEnabled, $IP;
		$stats = new UserStats($wgUser->getID(), $wgUser->getName());
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		$avatar = new wAvatar($wgUser->mId,"l");
		
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		$p = new UserProfile($wgUser->getName());
		$complete = $p->getProfileComplete();
		
		$output = "";
		  		$output .= "<div class=\"profile-box-top-links\">
					<a href=\"". $wgUser->getUserPage()->getFullURL(). "\">Profile</a> - <a href=\"index.php?title=Special:UserHome\">Friend's Activity</a>
				</div>";
				
				//PROFILE TOP IMAGES/POINTS

				$output .= "<div class=\"profile-box-image-links\">";
				$output .= '<div class="profile-box-image">';
					$output .= $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . ' />');
					if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
						$output .= '<p><a href=index.php?title=Special:UploadAvatar>(add image)</a></p>';
					} else {
						$output .= '<p><a href=index.php?title=Special:UploadAvatar>(new image)</a></p>';
					}
					$output .= '</div>';
					$output .= '<div class="profile-box-links">';
						$output .= "<div class=\"profile-box-points\">
							<span class=\"profile-on\">+{$stats_data["points"]}</span> points <a href=\"{$level_link->getFullURL()}\">({$user_level->getLevelName()})</a>
						</div>";
						$output .= "<div class=\"profile-box-advance\">
							To advance to <b><a href=\"{$level_link->getFullURL()}\">{$user_level->getNextLevelName()}</a></b> earn<br> <i>{$user_level->getPointsNeededToAdvance()}</i> more points!
						</div>";
					$output .= '</div>';
					$output .= '<div class="cleared"></div>';
				$output .= "</div>";
				
			
					//PROFILE COMPLETENESS BAR

					if ($complete != 100) {
						$bar_width = ($complete*2-20);
						if($bar_width<0)$bar_width = 0;
						$output .= "<div class=\"profile-completeness\">";
							$output .= "<h1>Profile Completeness</h1>";
							$output .= "<div class=\"profile-complete-bar-container\">";
								$output .= "<div style=\"background-color:#89C46F;width:".($bar_width)."px; height:14px;\">&nbsp;</div>";
							$output .= "</div>";
							$output .= "<div class=\"profile-complete-bar-number\">";
								$output .=  "<a href=\"index.php?title=Special:UpdateProfile\">" . $complete . "% (edit)</a>";
							$output .= "</div>";
							$output .= '<div class="cleared"></div>';
						$output .= "</div>";

					}
					
					//PROFILE MESSAGES
					require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
					require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
					require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
					$friend_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),1);
					$foe_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),2);
					$gift_count = UserGifts::getNewGiftCount($wgUser->getID());
					$system_gift_count = UserSystemGifts::getNewSystemGiftCount($wgUser->getID());
					$new_messages = UserBoard::getNewMessageCount($wgUser->getID());

					if (($friend_request_count) || ($foe_request_count) || ($new_messages) 
					|| ($gift_count) || ($system_gift_count)) {

					$output .= "<div class=\"profile-box-messages\">";
						$output .= "<h1>Requests</h1>";

						//new talk messages
					    //New Messages 
					
					    if ( $new_messages ) {
							$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
							$output .= '<p>';
							$output .= '<img src="images/common/emailIcon.png" alt="email icon" border="0"/> ';
							$output .= '<span class="profile-on"><a href="' . $board_link->getFullURL() . '">New Message</a></span>';
							$output .= '</p>';
					    }
						

						//new challenges

						//if ($wgChallengesEnabled) {
							//$title1 = Title::makeTitle( NS_USER  , $wgUser->getName()  );
							//$dbr =& wfGetDB( DB_SLAVE );
							//$challenge = $dbr->selectRow( '`challenge`', array( 'challenge_id'),
							//array( 'challenge_user_id_2' => $wgUser->getID() , 'challenge_status' => 0), "" )

							//if ($challenge > 0) {
							   //$output .= '<p>
									//<img src="images/common/challengeIcon.png" alt="challenge icon" border="0"/> 
									//<span class="profile-on">
										//<a href=index.php?title=Special:ChallengeHistory&user=" . $title1->getDbKey() . "&status=0>New Challenge</a>
									//</span>
								//</p>';
						    //}	
						//}

							$output .= $this->getRelationshipRequestLink();
							$output .= $this->getNewGiftLink();
							$output .= $this->getNewSystemGiftLink();
							$output .= "<div class=\"cleared\"></div>";
						$output .= "</div>";

					  } else {
						 //ADD FRIENDS

					    $output .= "<div class=\"profile-box-add-friends\">";
							$output .= "<h1>Add Friends</h1>";
							$output .= "<p>
								<a href=\"index.php?title=Special:InviteContacts\">
									<img src=\"../../images/common/msnIconSmall.png\" border=\"0\"/> Hotmail
								</a>
								<a href=\"index.php?title=Special:InviteContacts\"> 
									<img src=\"../../images/common/yahooIconSmall.png\" border=\"0\"/> Yahoo!
								</a>
								<a href=\"index.php?title=Special:InviteContacts\">
									<img src=\"../../images/common/gmailIconSmall.png\" border=\"0\"/> Gmail
								</a>
								<a href=\"index.php?title=Special:InviteContacts\"> 
									<img src=\"../../images/common/aolIconSmall.png\" border=\"0\"/> AOL
								</a>
							</p>";
						$output .= "</div>";
					}
		  
		 return $output;
	}
	
	function getRelationshipRequestLink(){
		global $wgUser, $IP;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		$friend_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),1);
		$foe_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),2);
		$output = "";
		if($friend_request_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/addedFriendIcon.png" alt="challenge icon" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ViewRelationshipRequests>{$friend_request_count} New Friend" . (($friend_request_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		if($foe_request_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/addedFoeIcon.png" alt="challenge icon" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ViewRelationshipRequests>{$foe_request_count} New Foe" . (($foe_request_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;
	}
	
	function getUserPageRelationships($user_name,$rel_type){
		global $IP, $wgMemc, $wgUser, $wgTitle;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");

		$rel = new UserRelationship($user_name);
		
		$key = wfMemcKey( 'relationship', 'profile', "{$rel->user_id}-{$rel_type}" );
		$data = $wgMemc->get( $key );
		$wgMemc->delete( $key );
		
		//try cache
		if(!$data){
			$friends = $rel->getRelationshipList($rel_type,6);
			$wgMemc->set( $key, $friends );
		}else{
			wfDebug( "Got profile relationship type {$rel_type} for user {$user_name} from cache\n" );
			$friends = $data;
		}
		
		$stats = new UserStats($rel->user_id,$user_name);
		$stats_data = $stats->getUserStats();
		$friend_count = $stats_data["friend_count"];
		$foe_count = $stats_data["foe_count"];
		$user_safe = urlencode(   $user_name  );
		if($rel_type==1){
			$output .= "<h1 class=\"user-profile-title\">Friends ({$friend_count})</h1>
					<p class=\"profile-sub-links\">
						<a href=\"index.php?title=Special:ViewRelationships&user={$user_safe}&rel_type={$rel_type}\">All Friends</a>";
						if($wgUser->getName() == $wgTitle->getText()) {
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\">Add Friends</a>";
						}
					$output .= "</p>";
		}else{
			$output .= "<h1 class=\"user-profile-title\">Foes ({$foe_count})</h1>
					<p class=\"profile-sub-links\">
						<a href=\"index.php?title=Special:ViewRelationships&user={$user_safe}&rel_type={$rel_type}\">All Foes</a>";
						if($wgUser->getName() == $wgTitle->getText()) {
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\">Add Friend</a>";
						}
					$output .= "</p>";
		}
		if($friends){
			$x = 1;
			$per_row = 6;
			foreach ($friends as $friend) {
				$user =  Title::makeTitle( NS_USER  , $friend["user_name"]  );
				$avatar = new wAvatar($friend["user_id"],"ml");
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\"/>";
				
				//chop down username that gets displayed
				$user_name = substr($friend["user_name"],0,9);
				if($user_name != $friend["user_name"]){
					$user_name .= "..";
				}
				
				$output .= "<div class=\"user-page-rel\">
						<div class=\"user-page-rel-image\"><a href=\"{$user->getFullURL()}\" title=\"{$friend["user_name"]}\">{$avatar_img}</a></div>
							<div class=\"user-page-rel-info\">
									<a href=\"{$user->getFullURL()}\" title=\"{$friend["user_name"]}\">{$user_name}</a>
							</div>";
				if($x!=1 && $x%$per_row ==0)$output.="<div class=\"cleared\"></div>";
				$output .= "</div>";	
	
				if($x==count($friends) || $x!=1 && $x%$per_row ==0)$output.="<div class=\"cleared\"></div>";
				$x++;
			}
		} else {
		    if($rel_type==1) {
			    if ( $wgUser->getName() == $wgTitle->getText() ) {
				    $output .= "<p>No friends.  No worries . . . <a href='index.php?title=Special:InviteContacts'>invite some!</a></p>";
			    } else {
				    $output .= "<p>Boo! {$wgTitle->getText()} has no friends . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=1\">friend!</a>";
			    }
		    } else {
			    if ( $wgUser->getName() == $wgTitle->getText() ) {
				    $output .= "<p>No foes, no fun . . . <a href='index.php?title=Special:InviteContacts'>Start a war!</a></p>";
			    } else {
				    $output .= "<p>{$wgTitle->getText()} has no foes.  Start a war . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=2\">foe!</a>";
			    }
		    }
		}
		//$output .= "</div>";
		
		return $output;
	}
	
	function getUserPageActivity($user_name){
		global $IP, $wgUser, $wgTitle;
		require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
		require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
		require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
		$output = "<script>
				var last_activity = 'all';
				function view_user_activity(filter){
					Effect.Fade('recent-'+last_activity,{duration:.5,afterFinish:function(){Effect.Appear('recent-'+filter,{duration:.5} );last_activity=filter;} });
				
				}
		</script>";
		
				
			$output .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
				<tr>
					<td class=\"user-activity-title\">Activity</td>
					<td align=\"right\"><a href=\"javascript:view_user_activity('all');\">all</a> <a href=\"javascript:view_user_activity('edits');\"><img src=\"images/common/" . UserActivity::getTypeIcon("edit") . "\"></a> <a href=\"javascript:view_user_activity('comments');\"><img src=\"images/common/" . UserActivity::getTypeIcon("comment") . "\"></a> <a href=\"javascript:view_user_activity('friends');\"><img src=\"images/common/" . UserActivity::getTypeIcon("friend") . "\"></a> <a href=\"javascript:view_user_activity('foes');\"><img src=\"images/common/" . UserActivity::getTypeIcon("foe") . "\"></a> <a href=\"javascript:view_user_activity('gifts-sent');\"><img src=\"images/common/" . UserActivity::getTypeIcon("gift-sent") . "\"></a> <a href=\"javascript:view_user_activity('gifts-rec');\"><img src=\"images/common/" . UserActivity::getTypeIcon("gift-rec") . "\"></a> <a href=\"javascript:view_user_activity('messages');\"><img src=\"images/common/" . UserActivity::getTypeIcon("system_message") . "\"></a> <a href=\"javascript:view_user_activity('system_gifts');\"><img src=\"images/common/" . UserActivity::getTypeIcon("system_gift") . "\"></a>
					</td>
				</tr>
			</table>";
			
		$limit = 6;
		$rel = new UserActivity($user_name,"user",$limit);
		$rel->setActivityToggle("show_votes",0);
		$rel->setActivityToggle("show_gifts_sent",1);
			/*
			Get all relationship activity
			*/
			$activity = $rel->getActivityList();
			if($activity){
				$x = 1;
				foreach ($activity as $item) {
					$item_html = "";
					$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
					$user_title = Title::makeTitle( NS_USER  , $item["username"]  );
					$user_title_2 = Title::makeTitle( NS_USER  , $item["comment"]  );
					if($user_title_2){
						$user_link_2 = "<a href=\"{$user_title_2->getFullURL()}\">
									<dimg src=\"images/avatars/{$CommentIcon} alt=\"\" border=\"0\" />
									{$item["comment"]}</a>";
					}
					
					$avatar = new wAvatar($item["userid"],"s");
					$CommentIcon = $avatar->getAvatarImage();
					
					$comment_url = "";
					if($item["type"] == "comment"){
						$comment_url = "#comment-{$item["id"]}";
					}
					$page_link = "<a href=\"" . $title->getFullURL() . "{$comment_url}\">" . $title->getPrefixedText() . "</a> ";
					$item_time = "<div class=\"user-feed-item-time\">{$rel->getTimeAgo($item["timestamp"])} ago</div>";
					$item_html .= "<div class=\"user-feed-item\">
							<span class=\"user-feed-item-icon\">
								<img src=\"images/common/" . UserActivity::getTypeIcon($item["type"]) . "\" alt=\""  . UserActivity::getTypeIcon($item["type"]) . "\" border='0'>
							</span>
							<span class=\"user-feed-item-user\">
								<a href=\"{$user_title->getFullURL()}\">
									<dimg src=\"images/avatars/{$CommentIcon} alt=\"\" border=\"0\" />
									{$item["username"]}</a>
								<a href=\"{$user_title->getTalkPage()->getFullURL()}\">
									<dimg src=\"images/common/comment_new.gif\" border=\"0\" hspace=\"3\" align=\"middle\">
								</a>
							</span>
							<span class=\"user-feed-item-activity\">
							";
							
					switch ($item["type"]) {
						case "edit":
							$item_html .= "edited the page {$page_link} {$item_time}</span>
								<div class=\"user-feed-item-editinfo\">
									{$item["comment"]}
									</div>
							";
							break;
						case "vote":
							$item_html .= "voted for the page {$page_link} {$item_time}</span>";
							break;
						case "comment":
							$item_html .= "commented on the page {$page_link} {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									\"{$item["comment"]}\"
									</div>
							";
							break;
						case "gift-sent":
							$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$item_html .= "sent a <a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">gift</a> to {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-gift\">
							<span class=\"user-feed-gift-image\">
								<a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">{$gift_image}</a>
							</span>
							<span class=\"user-feed-gift-info\">
								{$item["pagetitle"]}
							</span>
							</div>";
							break;
						case "gift-rec":
							$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$item_html .= "received a <a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">gift</a> from {$user_link_2} {$item_time}</span>
									<div class=\"user-feed-item-gift\">
									<span class=\"user-feed-gift-image\">
										<a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\">{$gift_image}</a>
									</span>
									<span class=\"user-feed-gift-info\">
										{$item["pagetitle"]}
									</span>
									</div>
							";
							break;
						case "system_gift":
							$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$item_html .= "received an <a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\">award</a> {$item_time}</span>
									<div class=\"user-home-item-gift\">
									<span class=\"user-home-gift-image\">
										<a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\">{$gift_image}</a>
									</span>
									<span class=\"user-home-gift-info\">
										{$item["pagetitle"]}
									</span>
									</div>
							";
							break;
						case "friend":
							$item_html .= "is now friends with {$user_link_2} {$item_time}</span>";
							break;
						case "foe":
							$item_html .= "is now foes with {$user_link_2} {$item_time}</span>";
							break;
						case "challenge_sent":
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\">challenge</a>";
							$item_html .= "issued an accepted {$challenge_link} to {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>
							";
							break;
						case "challenge_rec":
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\">challenge</a>";
							$item_html .= "accepted a {$challenge_link} from {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>
							";
							break;
						case "system_message":
							$item_html .= "{$item["comment"]} {$item_time}</span>";
							break;
						case "user_message":
							$item_html .= "wrote on <a href=\"{$user_title_2->getFullURL()}\">{$item["comment"]}'s</a> <b><a href=\"" . UserBoard::getUserBoardURL($user_title_2->getText()) . "\">board</a></b>  {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									\"{$item["namespace"]}\"
									</div>";
							break;
					}
					$item_html .= "</span>";
					
					
					$item_html .= "</div>";
					
					
					if($x<=$limit){
						$items_html_type["all"][] = $item_html;
					}
					$items_html_type[$item["type"]][] = $item_html;
					
					$x++;
				}
					
				$by_type = "";
				foreach($items_html_type["all"] as $item){
					$by_type .= $item;	
				}
				$output .= "<div id=\"recent-all\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["edit"]){
					foreach($items_html_type["edit"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent edits";
				}
				$output .= "<div id=\"recent-edits\" style=\"display:none\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["comment"]){
					foreach($items_html_type["comment"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent comments";
				}
				$output .= "<div id=\"recent-comments\" style=\"display:none\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["gift-sent"]){
					foreach($items_html_type["gift-sent"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent gifts sent";
				}
				$output .= "<div id=\"recent-gifts-sent\" style=\"display:none\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["gift-rec"]){
					foreach($items_html_type["gift-rec"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent gifts received";
				}
				$output .= "<div id=\"recent-gifts-rec\" style=\"display:none\">$by_type</div>";

				$by_type = "";
				if($items_html_type["system_gift"]){
					foreach($items_html_type["system_gift"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent awards received";
				}
				$output .= "<div id=\"recent-system_gifts\" style=\"display:none\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["friend"]){
					foreach($items_html_type["friend"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent friends";
				}
				$output .= "<div id=\"recent-friends\" style=\"display:none\">$by_type</div>";
				
				$by_type = "";
				if($items_html_type["foe"]){
					foreach($items_html_type["foe"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent foes";
				}
				$output .= "<div id=\"recent-foes\" style=\"display:none\">$by_type</div>";

				$by_type = "";
				if($items_html_type["system_message"]){
					foreach($items_html_type["system_message"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent messages";
				}
				$output .= "<div id=\"recent-messages\" style=\"display:none\">$by_type</div>";
				
			} else {
				if ( $wgUser->getName() == $wgTitle->getText() ) {
					$output .= "<p>Why haven't you done anyting yet?  <a href='index.php?title=Special:InviteContacts'>Invite some friends</a>, <a href='index.php?title=Create_Opinion'>write an article</a>, or <a href='index.php?title=Special:Randompage'>edit a random article</a>.</p>";
				} else {
					$output .= "<p>{$wgTitle->getText()} does not have any recent activity. :-(</p>";	
				}
			}
			
			$output .= "";
		return $output;
	}
	
	function getUserPageGifts($user_name){
		global $IP, $wgUser, $wgTitle, $wgMemc;
		
		require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
		require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
		require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
		require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
		
		//USER TO USER GIFTS
		$g = new UserGifts($user_name);
		$user_safe = urlencode($user_name);
		//try cache
		$key = wfMemcKey( 'gifts', 'profile', "{$g->user_id}" );
		$data = $wgMemc->get( $key );
		
		if(1==1){//!$data){
			$user_gifts = $g->getUserGiftList(0,6);
			$wgMemc->set( $key, $user_gifts );
		}else{
			wfDebug( "Got profile gifts for user {$user_name} from cache\n" );
			$user_gifts = $data;
		}
 
		//SYSTEM GIFTS
		$sg = new UserSystemGifts($user_name);
		
		//try cache
		$sg_key = wfMemcKey( 'system_gifts', 'profile', "{$sg->user_id}" );
		$data = $wgMemc->get( $sg_key );
		if(!$data){
			$system_gifts = $sg->getUserGiftList(0,6);
			$wgMemc->set( $sg_key, $system_gifts );
		}else{
			wfDebug( "Got profile gifts for user {$user_name} from cache\n" );
			$system_gifts = $data;
		}
		
		//MERGE gifts and system_gifts data
		$gifts = array_merge($user_gifts, $system_gifts);
		if($gifts)usort($gifts, '_sort_gifts');
		
		$gift_count = $g->getGiftCountByUsername($user_name);
		$system_gift_count = $sg->getGiftCountByUsername($user_name);
		$per_row = 6;
		
	
			$output .= "<h1 class=\"user-profile-title\">Gifts and Awards (" . ($gift_count+$system_gift_count).")</h1>
					<p class=\"profile-sub-links\">
						<a href=\"index.php?title=Special:ViewGifts&user={$user_safe}\">All Gifts</a> -
						<a href=\"index.php?title=Special:ViewSystemGifts&user={$user_safe}\">All Awards</a>
					</p>";
	
		if($gifts){
			$x = 1;
			foreach ($gifts as $gift) {
	
				if($gift["status"] == 1 && $user_name==$wgUser->getName() ){
					if(!$gift["user_name_from"]){
						$sg->clearUserGiftStatus($gift["id"]);
					}else{
						$g->clearUserGiftStatus($gift["id"]);
					}
					$wgMemc->delete( $key );
					$wgMemc->delete( $sg_key );
				}
				
				$user =  Title::makeTitle( NS_USER  , $gift["user_name_from"]  );
				$avatar = new wAvatar($gift["user_id_from"],"s");
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' />";
				
				if(!$gift["user_name_from"]){
					$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($gift["gift_id"],"ml") . "\" border=\"0\" alt=\"gift\" />";
					$gift_link = $user =  Title::makeTitle( NS_SPECIAL  , "ViewSystemGift"  );
				}else{
					$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($gift["gift_id"],"ml") . "\" border=\"0\" alt=\"gift\" />";
					$gift_link = $user =  Title::makeTitle( NS_SPECIAL  , "ViewGift"  );
				}
				$output .= "<div class=\"user-page-gift\">
						<div " . (($gift["status"] == 1)?"class=\"user-page-gift-image-new\"":"class=\"user-page-gift-image\"") . ">
							<a href=\"{$gift_link->getFullURL()}&gift_id={$gift["id"]}\">{$gift_image}</a>
						</div>";

				$output .= "</div>";
				if($x==count($gifts) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
				$x++;	
			}
		} else {
			if ( $wgUser->getName() == $wgTitle->getText() ) {
				$output .= "<p>You don't have any gifts . . . yet.  The more gifts you <a href='index.php?title=Special:GiveGift'>give</a>, the more gifts you get!";
			 } else {
				$output .= "<p>Give {$wgTitle->getText()} a <a href=\"index.php?title=Special:GiveGift&user={$user_safe}\">gift</a> . . . it's the nice thing to do.";
			 }
		}
		//$output .= "</div>";
		
		return $output;
	}
	
	function getNewGiftLink(){
		global $wgUser, $IP;
		require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
		$gift_count = UserGifts::getNewGiftCount($wgUser->getID());
		$output = "";
		if($gift_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/icon_package_get.gif" alt="challenge icon" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ViewGifts>{$gift_count} New Gift" . (($gift_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;		
	}

	function getNewSystemGiftLink(){
		global $wgUser, $IP;
		require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
		$gift_count = UserSystemGifts::getNewSystemGiftCount($wgUser->getID());
		$output = "";
		if($gift_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/awardIcon.png" alt="challenge icon" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ViewSystemGifts>{$gift_count} New Award" . (($gift_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;		
	}

	function getProfileSection($label,$value,$required=true){
		global $wgUser, $wgTitle;
		
		if($value || $required){
			if(!$value){
				if ( $wgUser->getName() == $wgTitle->getText()  ) {
					$value = 'Update Your Profile';	
				}else{
					$value = 'Not Provided';	
				}
			}
			
			$output = "<div class=\"user-profile-data\">";
			$output .= "<div class=\"user-profile-data-left\">";	
			$output .= "{$label}:";
			$output .= "</div>";
			$output .= "<div class=\"user-profile-data-right\">";	
			$output .= "{$value}";
			$output .= "</div>";
			$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
		}			
		return $output;
	}
	
	function getUserProfile($user){
		global $wgUser, $wgTitle, $IP, $wgMemc;
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		
		//try cache first
		$key = wfMemcKey( 'user', 'profile', $user );
		$wgMemc->delete( $key );
		$data = $wgMemc->get( $key );
		if($data){
			wfDebug( "Got  user profile info for {$user} from cache\n" );
			$profile_data = $data;
		}else{
			$profile = new UserProfile($user);
			$profile_data = $profile->getProfile();
			$wgMemc->set( $key, $profile_data );
		}
		
		$output = "";			
		$output .= "<h1 class=\"user-profile-title\">More About Me</h1>";
				if ($wgUser->getName() == $wgTitle->getText()) {
						$output .= "<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">
							<a href=\"index.php?title=Special:UpdateProfile\">Edit Your Profile</a>
						</p>";
				}
		
		$output .= "<h2>Personal</h2>";
		
		//$output .= $this->getProfileSection("Name",$profile_data["real_name"]);
		
		//$location = $profile_data["location_city"] . ", " . $profile_data["location_state"];
		//if($profile_data["location_country"]!="United States"){
			//$location .= $profile_data["location_country"];
		//}	
		$hometown = $profile_data["hometown_city"] . ", " . $profile_data["hometown_state"];
		if($profile_data["hometown_country"]!="United States"){
			$hometown .= $profile_data["hometown_country"];
		}
		if($hometown==", ")$hometown="";
		
		//$output .= $this->getProfileSection("Location",$location);
		$output .= $this->getProfileSection("Hometown",$hometown);
		$output .= $this->getProfileSection("Birthday",$profile_data["birthday"]);
		$output .= $this->getProfileSection("Occupation",$profile_data["occupation"]);
		$output .= $this->getProfileSection("Websites",$profile_data["websites"]);
		$output .= $this->getProfileSection("Places I've Lived",$profile_data["places_lived"],false);
		$output .= $this->getProfileSection("Schools",$profile_data["schools"],false);
		$output .= $this->getProfileSection("About Me",$profile_data["about"],false);
		
		$output .= "<h2 style=\"margin-top:5px;\">Sports Tidbits</h2>";
		$output .= $this->getProfileSection("Favorite Moment",$profile_data["custom_1"],false);
		$output .= $this->getProfileSection("Worst Moment",$profile_data["custom_2"],false);
		$output .= $this->getProfileSection("Favorite Athelete and Why",$profile_data["custom_3"],false);
		$output .= $this->getProfileSection("Least Favorite Athelete and Why",$profile_data["custom_4"],false);
		
		$output .= "<h2 style=\"margin-top:5px;\">Interests</h2>";	
		
		$output .= $this->getProfileSection("Movies",$profile_data["movies"],false);
		$output .= $this->getProfileSection("TV",$profile_data["tv"],false);
		$output .= $this->getProfileSection("Music",$profile_data["music"],false);
		$output .= $this->getProfileSection("Books",$profile_data["books"],false);
		$output .= $this->getProfileSection("Video Games",$profile_data["video_games"],false);
		$output .= $this->getProfileSection("Magazines",$profile_data["magazines"],false);
		$output .= $this->getProfileSection("Food & Snacks",$profile_data["snacks"],false);
		$output .= $this->getProfileSection("Drinks",$profile_data["drinks"],false);
		
		
		return $output;
	}

	function getUserLevel($user_id,$user_name){
		global $wgUser, $wgTitle, $IP;
		
		$stats = new UserStats($user_id, $user_name);
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		$output = "";			
		$output .= "<div class=\"user-stats\" style=\"padding-bottom:5px;\">
			<div class=\"user-stats-title\">My Scoreboard</div>
			<div class=\"user-stats-data\"><b>Points</b>: <span class=\"profile-on\" style=\"font-weight:800;font-size:16px;color:#666666;\">+{$stats_data["points"]}</span></div>
			<div class=\"cleared\"></div>
			<div class=\"user-stats-data\" style=\"width:300px\"><b>Level</b>: <span   style=\"font-size:14px;\"><a href=\"{$level_link->getFullURL()}\">{$user_level->getLevelName()}</a></span></div>
			<div class=\"cleared\"></div>
			<div class=\"user-stats-data\" style=\"width:300px\">Needs <i>{$user_level->getPointsNeededToAdvance()}</i> pts. to advance to <span  style=\"font-weight:800;\">{$user_level->getNextLevelName()}</span></span></div>
			<div class=\"cleared\"></div>
			</div>";
		
		return $output;
	}

	
	function getUserStats($user_id,$user_name){
		global $wgUser, $wgTitle, $IP;
		
		$stats = new UserStats($user_id, $user_name);
		$stats_data = $stats->getUserStats();
		
		$output = "";
		
		$output .= "<div class=\"user-stats-title\">";
		$output .= "<h1 class=\"user-profile-title\">Stats</h1>
				<p class=\"profile-sub-links\" style=\"margin-bottom:15px\">
					<a href=\"index.php?title=Special:TopFansRecent&period=weekly\">This Week's Top Gamers</a> - 
					<a href=\"index.php?Special:TopFans\">Complete List</a>
				</p>";
		$output .= "</div>";
		$output .= "<div class=\"user-stats-data\">";
		$output .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
			<tr>
				<td><b>Edits</b>: {$stats_data["edits"]}</td>
				<td><b>Votes</b>: {$stats_data["votes"]}</td>
			</tr>
			<tr>
				<td><b>Opinions</b>: {$stats_data["opinions_created"]} ({$stats_data["opinions_published"]})</td>
				<td><b>Comments</b>: {$stats_data["comments"]}</td>
			</tr>
			<tr>
				<td><b>Comment Score</b>: {$stats_data["comment_score"]}</td>
				<td><b>Thumbs up</b>: {$stats_data["comment_score_plus"]}</td>
			</tr>
			<tr>
				<td><b>Users Recruited</b>: {$stats_data["recruits"]}</td>
				<td><b>Challenges Won</b>: {$stats_data["challenges_won"]}</td>
			</tr>
		</table>";
		$output .= "</div>";
		
		return $output;
	}
	
	function getNewUsers($limit = 10){
		global $wgUser, $wgMemc;
		
		$key = wfMemcKey( 'users', 'new', "1" );
		$data = $wgMemc->get( $key );
		
		if(!$data){
			$dbr =& wfGetDB( DB_MASTER );
			
			if($limit>0){
				$limitvalue = 0;
				if($page)$limitvalue = $page * $limit - ($limit); 
				$limit_sql = " LIMIT {$limitvalue},{$limit} ";
			}
			
			$sql = "SELECT ur_user_id, ur_user_name
				FROM user_register_track
				ORDER BY ur_date DESC
				{$limit_sql}";
			
			$list = array();
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				 $list[] = array(
					 "user_id"=>$row->ur_user_id,"user_name"=>$row->ur_user_name);
			}
			$wgMemc->set( $key, $list );
		}else{
			wfDebug( "Got new users from cache\n" );
			$list = $data;
		}
		
		$per_row = 5;
		$x = 1;
		$output = "";
		foreach($list as $user) {		
			$avatar = new wAvatar($user["user_id"],"ml");
			$avatar_image = $avatar->getAvatarURL();
			$user =  Title::makeTitle( NS_USER  , $user["user_name"]  );
			$users .= "<div class=\"featured-fan\"><a href=\"{$user->getFullURL()}\">{$avatar_image}</a></div>";
			if($x==count($list) || $x!=1 && $x%$per_row ==0)$users .= "<div class=\"cleared\"></div>";
			$x++;
		}
		$register_title = Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
		$similar_title = Title::makeTitle( NS_SPECIAL  , "SimilarFans"  );
		
		$output .= '<div class="featured-users">';
			$output .= '<h1>Recently Joined</h1>';
			$output .= $users;
		$output .= '</div>';
		return $output;
		 
	}
	
	function getFeaturedUsers(){
		// FEATURED USERS
		$per_row = 5;
		$x = 1;
		$output = "";
		foreach($this->featured_users as $featured_user_id => $featured_user_name) {		
			$avatar = new wAvatar($featured_user_id,"ml");
			$user =  Title::makeTitle( NS_USER  , $featured_user_name  );
			$users .= "<div class=\"featured-fan\"><a href=\"{$user->getFullURL()}\">{$avatar->getAvatarURL()}</a></div>";
			if($x==count($this->featured_users) || $x!=1 && $x%$per_row ==0)$users .= "<div class=\"cleared\"></div>";
				$x++;
		}
		$register_title = Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
		$output .= '<div class="featured-users">';
			$output .= '<h1>Meet Gamers</h1>';
			$output .= $users;
		$output .= '</div>';
		return $output;
	}
	
	function getCommentsOfTheDay(){
		$sql = "SELECT Comment_Username,comment_ip, comment_text,comment_date,Comment_user_id,
				CommentID,IFNULL(Comment_Plus_Count - Comment_Minus_Count,0) as Comment_Score,
				Comment_Plus_Count as CommentVotePlus, 
				Comment_Minus_Count as CommentVoteMinus,
				Comment_Parent_ID, page_title, page_namespace
				FROM Comments c, page p where c.comment_page_id=page_id 
				AND UNIX_TIMESTAMP(comment_date) > " . ( time() - (60 * 60 * 24 ) ) . "
				ORDER BY (Comment_Plus_Count - Comment_Minus_Count) DESC LIMIT 0,5";

		  $comments = "";
		  $dbr =& wfGetDB( DB_MASTER );
		  $res = $dbr->query($sql);
		  while ($row = $dbr->fetchObject( $res ) ) {
			$title2 = Title::makeTitle( $row->page_namespace, $row->page_title);
		
			if($row->Comment_user_id!=0){
				$title = Title::makeTitle( 2, $row->Comment_Username);
				$CommentPoster_Display = $row->Comment_Username;
				$CommentPoster = '<a href="' . $title->getFullURL() . '" title="' . $title->getText() . '">' . $row->Comment_Username . '</a>';
				$avatar = new wAvatar($row->Comment_user_id,"s");
				$CommentIcon = $avatar->getAvatarImage();
			}else{
				$CommentPoster_Display = "Anonymous Fanatic";
				$CommentPoster = "Anonymous Fanatic";
				$CommentIcon = "af_s.gif";
			}
			$comment_text = substr($row->comment_text,0,55 - strlen($CommentPoster_Display) );
			if($comment_text != $row->comment_text){
				$comment_text .= "...";
			}
			$comments .= "<div class=\"cod\">";
			$comments .=  "<span class=\"cod-score\">+" . $row->Comment_Score . '</span> <img src="images/avatars/' . $CommentIcon . '" alt="" align="middle" style="margin-bottom:8px;" border="0"/> <span class="cod-poster">' . $CommentPoster . "</span>";
			$comments .= "<span class=\"cod-comment\"><a href=\"" . $title2->getFullURL() . "#comment-" . $row->CommentID . "\" title=\"" . $title2->getText() . "\" >" . $comment_text . "</a></span>";
			$comments .= "</div>";
		  }
		
		  $output .= '<h1>Comments of the Day <span class="grey">(last 24 hours)</span></h1>';
		  $output .= $comments;
		  return $output;
	}
	
	function getUserBoard($user_id,$user_name){
		global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;
		require_once("$IP/extensions/wikia/UserBoard/UserBoardClass.php");
		
		if($user_id==$wgUser->getID() )UserBoard::clearNewMessageCount($wgUser->getID());
		
		$user_safe = str_replace("&","%26",$user_name);
		$output .= "<script>
			var posted = 0;
			function send_message(){
				if(\$(\"message\").value && !posted){
					posted = 1;
					var url = \"index.php?action=ajax\";
					var pars = 'rs=wfSendBoardMessage&rsargs[]=' + \$(\"user_name_to\").value +'&rsargs[]=' + escape(\$(\"message\").value) + '&rsargs[]=' + \$(\"message_type\").value +'&rsargs[]=10'
					var myAjax = new Ajax.Updater(
						'user-page-board', url, {
							method: 'post', 
							parameters: pars,
							onSuccess: function(originalRequest) {
								posted = 0;
								\$(\"message\").value='';
							}
					});
				}
			}
			function delete_message(id){
				if(confirm('Are you sure you want to delete this message?')){
					var url = \"index.php?action=ajax\";
					var pars = 'rs=wfDeleteBoardMessage&rsargs[]=' + id
					var myAjax = new Ajax.Request(
						url, {
							method: 'post', 
							parameters: pars,
							onSuccess: function(originalRequest) {
								window.location='index.php?title=User:'+\$(\"user_name_to\").value;
							}
					});
				}
				
			}
		</script>";
		$stats = new UserStats($user_id, $user_name);
		$stats_data = $stats->getUserStats();
		$total = $stats_data["user_board"];
		if($wgUser->getName() == $user_name)$total=$total+$stats_data["user_board_priv"];
		
		if($wgUser->getName() !== $user_name){
			$board_to_board = "<a href=\"" . UserBoard::getUserBoardToBoardURL($wgUser->getName(),$user_name)."\">board-to-board</a>";
		}
		
		$output .= '<h1>Board</h1>';
		$output .= "<div class=\"user-page-message-top\">
		<span class=\"user-page-message-count\">{$total} Total Message" . (($total!=1)?"s":"") . ". </span> {$board_to_board} <a href=\"" . UserBoard::getUserBoardURL($user_name)."\">Show All</a></span> 
			</div>";
			
		if($wgUser->getName() !== $user_name){
			if($wgUser->isLoggedIn() ){
				$output .= "<div class=\"user-page-message-form\">
						<input type=\"hidden\" id=\"user_name_to\" name=\"user_name_to\" value=\"" . addslashes($user_name)."\"/>
						<span style=\"color:#797979;\">Message Type</span> <select id=\"message_type\"><option value=\"0\">public</option><option value=\"1\">private</option></select><p>
						<textarea name=\"message\" id=\"message\" cols=\"43\" rows=\"4\"/></textarea>
						<div class=\"user-page-message-box-button\">
							<input type=\"button\" value=\"Send\" class=\"site-button\" onclick=\"javascript:send_message();\">
						</div>
					</div>";
			} else {
				$output .= "<div class=\"user-page-message-form\">
						You Must be Logged-In to Post Messages to Other Users
				</div>";
			}
		}
		$output .= "<div id=\"user-page-board\">";
		 
		$b = new UserBoard();
		$output .= $b->displayMessages($user_id,0,10);
		
		$output .= "</div>";
		
		
		return $output;
	}
	
	function recentEditors() {
		global $IP, $wgUser, $wgTitle, $wgOut;
		
		$page_title_id = $wgTitle->getArticleID();
		
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT DISTINCT rev_user, rev_user_text FROM revision WHERE rev_page = {$page_title_id} and rev_user <> 0 ORDER BY rev_user_text ASC LIMIT 0,6";
		
		$output = "";
		
		if ( $wgUser->isLoggedIn() ) {
			$output .= "<div class=\"recent-editors\">";
		} else {
			$output .= "<div class=\"recent-editors-logged-out\">";
		}
		
		
		$output .= "<h1>Recent Editors</h1>";
		
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			 
			$user_name = ($row->rev_user_text == substr($row->rev_user_text, 0, 12) ) ? $row->rev_user_text : ( substr($row->rev_user_text, 0, 12) . "...");
			$user_id = $row->rev_user;
			$avatar = new wAvatar($user_id,"s");
			
			
			$output .= "<div class=\"recent-editor\">
			
				<img src=images/avatars/{$avatar->getAvatarImage()} style=\"border:1px solid #dcdcdc;\"/>
				<a href=\"index.php?title=User:{$row->rev_user_text}\">{$user_name}</a>
			
			</div>";
			
		}
		
		$output .= "</div>";
		return $output;
	}
	
	function recentVoters() {
		global $IP, $wgUser, $wgTitle, $wgOut;
		
		//gets the page id for the query
		$page_title_id = $wgTitle->getArticleID();
		
		//query to get the people who are voting
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT DISTINCT username, vote_user_id, vote_page_id FROM Vote WHERE vote_user_id <> 0 and vote_value = 1 and vote_page_id = {$page_title_id} ORDER BY vote_date LIMIT 0,6";
		
		//query to see if this is an opinion
		$sql_cat = "SELECT cl_to, cl_timestamp, cl_sortkey FROM categorylinks WHERE cl_from={$page_title_id} and cl_to='Opinions'";
		
		$res = $dbr->query($sql);
		$res_cat = $dbr->query($sql_cat);
		$row_cat = $dbr->fetchObject( $res_cat );
		$category = $row_cat->cl_to;
		
		$output = "";
		
		if ($category) {
		
		$output .= "<div class=\"recent-editors\">";
		$output .= "<h1>Recent Voters</h1>";
			
			while ($row = $dbr->fetchObject( $res ) ) {

				$user_name = ($row->username == substr($row->username, 0, 12) ) ? $row->username : ( substr($row->username, 0, 12) . "...");
				$user_id = $row->vote_user_id;
				$avatar = new wAvatar($user_id,"s");

				$output .= "<div class=\"recent-editor\">
					<img src=images/avatars/{$avatar->getAvatarImage()} style=\"border:1px solid #dcdcdc;\"/>
					<a href=\"index.php?title=User:{$row->usernamet}\">{$user_name}</a>

				</div>";

			}
		
		$output .= "</div>";	
			
		}
		
		return $output;
		
	}
	
	
	function getUserPictureGames($user_name) {
		
		global $IP, $wgUser, $wgTitle, $wgOut;
		
		//declare variables
		$output = "";
		
		//query to get the people who are voting
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT id, title, img1, img2, username from picturegame_images WHERE username = 'Awrigh01' ORDER BY id DESC LIMIT 0,3";
		$res = $dbr->query($sql);
		
		$output .= "<h1>Picture Games</h1>";
		$output .= "<p class=\"profile-sub-links\">
			<a href=\"index.php?title=\">Create a Picture Game</a> - 
			<a href=\"index.php?title=Special:PictureGameHome&picGameAction=startGame\">Play Picture Game</a> -
			<a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery\">Gallery</a>
		</p>";
		
		//loop through picture games
		
		$x = 1;
		
		while ($row = $dbr->fetchObject( $res ) ) {
			
			$picture_game_id = $row->id;
			$title = $row->title;
			$image_path_1 = $row->img1;
			$image_path_2 = $row->img2;
			
			$render_image_1 = Image::newFromName ($image_path_1);
			$thumb_image_1 = $render_image_1->getThumbNail(36,0,true);
			$image_1 = $thumb_image_1->toHtml();
			
			$render_image_2 = Image::newFromName ($image_path_2);
			$thumb_image_2 = $render_image_2->getThumbNail(36,0,true);
			$image_2 = $thumb_image_2->toHtml();
			
			if ($x == 1) {
				$output .= "<div class=\"picture-game-row-top\">";
			} else {
				$output .= "<div class=\"picture-game-row\">";
			}
			
			
			$output .= "<span class=\"picture-game-image\">
						{$image_1}
					</span>
					<span class=\"picture-game-image\">
						{$image_2}
					</span>
					<span class=\"picture-game-title\">
						<a href=\"index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id={$picture_game_id}\">{$title}</a>
					</span>
					<div class=\"cleared\"></div>
				
			</div>";
			
			$x++;
			
		}
				
		
		
		return $output;
		
		
	}
	
	
 }
 
  function _sort_gifts($x, $y){
	if ( $x["unix_timestamp"] == $y["unix_timestamp"] )
	 return 0;
	else if ( $x["unix_timestamp"] > $y["unix_timestamp"] )
	 return -1;
	else
	 return 1;
}
?>

