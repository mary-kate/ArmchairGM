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
	 
	function inCategory($category){
		 global $wgOut;

		 foreach($wgOut->getCategoryLinks() as $ctg){
		 	$category_text[]=strip_tags($ctg);
		 }

		 if (is_array($category_text) && in_array($category,$category_text ) ){
			 return true;
		 }else{
			return false;	 
		 }
	 }
	
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
	
	/**
	* Ashish: Get picure game preview
	*/
	function getPictureGamePreview(){
		$mt = new PictureGameHome();
		return $mt->getMiniGame();
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

	function getHeadScripts() {
		global $wgStylePath, $wgUser, $wgAllowUserJs, $wgJsMimeType, $wgStyleVersion, $wgServer;

		$r = self::makeGlobalVariablesScript( array( 'skinname' => $this->getSkinName() ) );

		$r .= "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/wikibits.js?$wgStyleVersion\"></script>\n";
		$r .= "<script type=\"{$wgJsMimeType}\" src=\"http://fp029.sjc.wikia-inc.com/extensions/wikia/onejstorule.js?{$wgStyleVersion}\"></script>\n";
		
		global $wgUseSiteJs;
		if ($wgUseSiteJs) {
			if ($wgUser->isLoggedIn()) {
				$r .= "<script type=\"$wgJsMimeType\" src=\"".htmlspecialchars(self::makeUrl('-','action=raw&smaxage=0&gen=js'))."\"><!-- site js --></script>\n";
			} else {
				$r .= "<script type=\"$wgJsMimeType\" src=\"".htmlspecialchars(self::makeUrl('-','action=raw&gen=js'))."\"><!-- site js --></script>\n";
			}
		}
		if( $wgAllowUserJs && $wgUser->isLoggedIn() ) {
			$userpage = $wgUser->getUserPage();
			$userjs = htmlspecialchars( self::makeUrl(
				$userpage->getPrefixedText().'/'.$this->getSkinName().'.js',
				'action=raw&ctype='.$wgJsMimeType));
			$r .= '<script type="'.$wgJsMimeType.'" src="'.$userjs."\"></script>\n";
		}
		return $r;
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
		 $main_page = Title::makeTitle(NS_MAIN,"Main Page");
		 $about = Title::makeTitle(NS_MAIN,"About");
		 $special = Title::makeTitle(NS_SPECIAL,"Specialpages");
		 $help = Title::makeTitle(NS_MAIN,"UserRegister");
		 
		  $s = '';
		  $s .= '<p>';
			  $s .= "<a href=\"{$main_page->escapeLocalURL()}\">Home</a>";
			  $s .= "<a href=\"{$about->escapeLocalURL()}\" rel=\"nofollow\">About</a>";
			  $s .= "<a href=\"{$special->escapeLocalURL()}\" rel=\"nofollow\">Special Pages</a>";
			  $s .= "<a href=\"{$help->escapeLocalURL()}\" rel=\"nofollow\">Help</a>";
			  $s .= '<a href="http://www.wikia.com/wiki/Terms_of_use" rel="nofollow">Terms of Use</a>';
			  $s .= '<a href="http://www.federatedmedia.net/authors/ArmchairGM" rel="nofollow">Advertise</a>';
		  $s .= '</p>';
		  $s .= '<p>';
		  $s .= 'Wikia&reg; is a registered service mark of Wikia, Inc. All rights reserved.';
		  $s .= '</p>';
		  $s .= '<p>';
		  $s .= '<a href="http://www.gnu.org/copyleft/fdl.html" rel="nofollow"><img src="images/common/gnu-fdl.png" alt="GNU-FDL" border="0"/></a>';
		  $s .= '<a href="http://www.mediawiki.org" rel="nofollow"><img src="images/common/poweredby_mediawiki_88x31.png" alt="powered by mediawiki" border="0"/></a>';
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
		  $avatar = new wAvatar($wgUser->mId,"l");
		  
		  $s .= '<div class="user-box">';
		  
		  $s .= '<div class="avatar-messages">';
		  	$s .= "<h1><a href=\"index.php?title=User:{$wgUser->mName}\" rel=\"nofollow\">{$user_name}</h1>";
		  	$s .= $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . ' style="border:1px solid #d7dee8;"/>');
		  		$s .= '<p><span class="upload-link">';   
		    	if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
			    	$s .= '<a href="index.php?title=Special:UploadAvatar" rel="nofollow">(upload image)</a>';
		    	} else {
		      		$s .= '<a href="index.php?title=Special:UploadAvatar" rel="nofollow">(new image)</a>';
		    	}
		    $s .= '</span></p>';
		    
		    //New Messages 
		    $new_messages = UserBoard::getNewMessageCount($wgUser->getID());
		    if ( $new_messages ) {
			    	$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
				$s .= '<p>';
				$s .= '<img src="images/common/emailIcon.gif" alt="" border="0"/> ';
				$s .= '<span class="profile-on"><a href="' . $board_link->getFullURL() . '" rel=\"nofollow\">New Message</a></span>';
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
		  	//$s .= '<p><img src="images/common/userPageIcon.gif" alt="userpage icon" border="0"/> '.$this->makeKnownLinkObj( $wgUser->getUserPage(), "Profile").'</p>';
		    //$s .= '<p><img src="images/common/homeIcon.gif" alt="userpage icon" border="0"/> <a href="index.php?title=Special:UserHome">Friends\' Activity</a></p>';
		  //$s .= "</div>";
		  
		  $s .= "<div class=\"user-friends-links\">";
		  	$s .= '<h1>Add Friends</h1>';
		    	$s .= '<p>';
		    		$s .= '<a href="index.php?title=Special:InviteContacts" rel="nofollow"><img src="../../images/common/msnIconSmall.gif" alt="" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts" rel="nofollow"><img src="../../images/common/yahooIconSmall.gif" alt="" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts" rel="nofollow"><img src="../../images/common/gmailIconSmall.gif" alt="" border="0"/></a> ';
		    		$s .= '<a href="index.php?title=Special:InviteContacts" rel="nofollow"><img src="../../images/common/aolIconSmall.gif" alt="" border="0"/></a>';
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
		  $s .= "<p><script>function fbs_click() {u=location.href;t=document.title;window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');return false;}</script><a href=\"javascript:void(0)\" onclick=\"return fbs_click()\" target=\"_blank\" class=\"fb_share_link\" rel=\"nofollow\">Facebook</a></p>";
		    $s .= '<p><a href="http://digg.com/submit?phase=2&amp;url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'" rel="nofollow"><img src="images/common/diggIcon.png" alt="" border="0"/> Digg</a></p>';
		    $s .= '<p><a href="http://reddit.com/submit?url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'"" rel="nofollow"><img src="images/common/redditIcon.png" alt="" border="0"/> Reddit</a></p>';
		    $s .= '<p><a href="http://del.icio.us/post"><img src="images/common/deliciousIcon.png" alt="" border="0" rel="nofollow"/> Delicious</a></p>';
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
					<a href=\"". $wgUser->getUserPage()->getFullURL(). "\" rel=\"nofollow\">Profile</a> - <a href=\"index.php?title=Special:UserHome\" rel=\"nofollow\">Friends' Activity</a>
				</div>";
				
				//PROFILE TOP IMAGES/POINTS

				$output .= "<div class=\"profile-box-image-links\">";
				$output .= '<div class="profile-box-image">';
					$output .= "<a href=\"index.php?title=User:{$wgUser->getName()}\" rel=\"nofollow\"><img src=\"images/avatars/" . $avatar->getAvatarImage() . "\" alt=\"\" border=\"0\"/></a>";
					if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
						$output .= '<p><a href="index.php?title=Special:UploadAvatar" rel="nofollow">(Add Image)</a></p>';
					} else {
						$output .= '<p><a href="index.php?title=Special:UploadAvatar" rel="nofollow">(New Image)</a></p>';
					}
					$output .= '</div>';
					$output .= '<div class="profile-box-links">';
						$output .= "<div class=\"profile-box-points\">
							<span class=\"profile-on\">+{$stats_data["points"]}</span> points <a href=\"{$level_link->getFullURL()}\" rel=\"nofollow\">({$user_level->getLevelName()})</a>
						</div>";
						$output .= "<div class=\"profile-box-advance\">
							To advance to <b><a href=\"{$level_link->getFullURL()}\" rel=\"nofollow\">{$user_level->getNextLevelName()}</a></b> earn<br> <i>{$user_level->getPointsNeededToAdvance()}</i> more points!
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
								$output .=  "<a href=\"index.php?title=Special:UpdateProfile\" rel=\"nofollow\">" . $complete . "% (edit)</a>";
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
					//New Messages 
					$new_messages = UserBoard::getNewMessageCount($wgUser->getID());

					if (($friend_request_count) || ($foe_request_count) || ($new_messages) 
					|| ($gift_count) || ($system_gift_count)) {

					$output .= "<div class=\"profile-box-messages\">";
						$output .= "<h1>Requests</h1>";

					   
					    if ( $new_messages ) {
							$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
							$output .= '<p>';
							$output .= '<img src="images/common/emailIcon.png" alt="email icon" border="0"/> ';
							$output .= '<span class="profile-on"><a href="' . $board_link->getFullURL() . '" rel=\"nofollow\">New Message</a></span>';
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
								<a href=\"index.php?title=Special:InviteContacts\" rel=\"nofollow\">
									<img src=\"../../images/common/msnIconSmall.gif\" border=\"0\"/> Hotmail
								</a>
								<a href=\"index.php?title=Special:InviteContacts\" rel=\"nofollow\"> 
									<img src=\"../../images/common/yahooIconSmall.gif\" border=\"0\"/> Yahoo!
								</a>
								<a href=\"index.php?title=Special:InviteContacts\" rel=\"nofollow\">
									<img src=\"../../images/common/gmailIconSmall.gif\" border=\"0\"/> Gmail
								</a>
								<a href=\"index.php?title=Special:InviteContacts\" rel=\"nofollow\"> 
									<img src=\"../../images/common/aolIconSmall.gif\" border=\"0\"/> AOL
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
			  $output .= '<img src="images/common/addedFriendIcon.png" alt="" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=\"index.php?title=Special:ViewRelationshipRequests\" rel=\"nofollow\">{$friend_request_count} New Friend" . (($friend_request_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		if($foe_request_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/addedFoeIcon.png" alt="" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=\"index.php?title=Special:ViewRelationshipRequests\" rel=\"nofollow\">{$foe_request_count} New Foe" . (($foe_request_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;
	}
	
	function getUserPageRelationships($user_name,$rel_type){
		global $IP, $wgMemc, $wgUser, $wgTitle;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");

		
		$count = 8;
		$rel = new UserRelationship($user_name);
		
		$key = wfMemcKey( 'relationship', 'profile', "{$rel->user_id}-{$rel_type}-{$count}" );
		$data = $wgMemc->get( $key );
	 
		
		//try cache
		if(!$data){
			$friends = $rel->getRelationshipList($rel_type,$count);
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
						<a href=\"index.php?title=Special:ViewRelationships&user={$user_safe}&rel_type={$rel_type}\" rel=\"nofollow\">All Friends</a>";
						if($wgUser->getName() == $wgTitle->getText()) {
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\">Meet Similar Fans</a>";
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\">Add Fans</a>";
							$output .= " - <a href=\"" . UserBoard::getBoardBlastURL()."\">Send Board Blast</a>";
						}
					$output .= "</p>";
		}else{
			$output .= "<h1 class=\"user-profile-title\">Foes ({$foe_count})</h1>
					<p class=\"profile-sub-links\">
						<a href=\"index.php?title=Special:ViewRelationships&user={$user_safe}&rel_type={$rel_type}\" rel=\"nofollow\">All Foes</a>";
						if($wgUser->getName() == $wgTitle->getText()) {
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\" rel=\"nofollow\">Meet Similar Fans</a>";
							$output .= " - <a href=\"index.php?title=Special:SimilarFans\" rel=\"nofollow\">Add Fans</a>";
							$output .= " - <a href=\"" . UserBoard::getBoardBlastURL()."\" rel=\"nofollow\">Send Board Blast</a>";
						}
					$output .= "</p>";
		}
		if($friends){
			$x = 1;
			$per_row = 4;
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
						<div class=\"user-page-rel-image\"><a href=\"{$user->getFullURL()}\" title=\"{$friend["user_name"]}\" rel=\"nofollow\">{$avatar_img}</a></div>
							<div class=\"user-page-rel-info\">
									<a href=\"{$user->getFullURL()}\" title=\"{$friend["user_name"]}\" rel=\"nofollow\">{$user_name}</a>
							</div>";
				if($x!=1 && $x%$per_row ==0)$output.="<div class=\"cleared\"></div>";
				$output .= "</div>";	
	
				if($x==count($friends) || $x!=1 && $x%$per_row ==0)$output.="<div class=\"cleared\"></div>";
				$x++;
			}
		} else {
		    if($rel_type==1) {
			    if ( $wgUser->getName() == $wgTitle->getText() ) {
				    $output .= "<p>No friends.  No worries . . . <a href='index.php?title=Special:InviteContacts' rel=\"nofollow\">invite some!</a></p>";
			    } else {
				    $output .= "<p>Boo! {$wgTitle->getText()} has no friends . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=1\" rel=\"nofollow\">friend!</a>";
			    }
		    } else {
			    if ( $wgUser->getName() == $wgTitle->getText() ) {
				    $output .= "<p>No foes, no fun . . . <a href='index.php?title=Special:InviteContacts' rel=\"nofollow\">Start a war!</a></p>";
			    } else {
				    $output .= "<p>{$wgTitle->getText()} has no foes.  Start a war . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$user_safe}&rel_type=2\" rel=\"nofollow\">foe!</a>";
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
					<td align=\"right\"><a href=\"javascript:view_user_activity('all');\" rel=\"nofollow\">all</a> <a href=\"javascript:view_user_activity('edits');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("edit") . "\ alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('comments');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("comment") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('friends');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("friend") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('foes');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("foe") . "\"></a> <a href=\"javascript:view_user_activity('gifts-sent');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("gift-sent") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('gifts-rec');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("gift-rec") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('messages');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("system_message") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('system_gifts');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("system_gift") . "\" alt=\"\" border=\"0\"></a> <a href=\"javascript:view_user_activity('network_updates');\" rel=\"nofollow\"><img src=\"images/common/" . UserActivity::getTypeIcon("network_update") . "\"alt=\"\" border=\"0\"></a>
					</td>
				</tr>
			</table>";
			
		$limit = 6;
		$rel = new UserActivity($user_name,"user",$limit);
		$rel->setActivityToggle("show_votes",0);
		$rel->setActivityToggle("show_gifts_sent",1);
		$rel->setActivityToggle("show_network_updates",1);
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
						$user_link_2 = "<a href=\"{$user_title_2->getFullURL()}\" rel=\"nofollow\">
									<img src=\"images/avatars/{$CommentIcon} alt=\"\" border=\"0\" />
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
								<a href=\"{$user_title->getFullURL()}\" rel=\"nofollow\">
									<dimg src=\"images/avatars/{$CommentIcon} alt=\"\" border=\"0\" />
									{$item["username"]}</a>
								<a href=\"{$user_title->getTalkPage()->getFullURL()}\" rel=\"nofollow\">
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
							$item_html .= "sent a <a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\" rel=\"nofollow\">gift</a> to {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-gift\">
							<span class=\"user-feed-gift-image\">
								<a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\" rel=\"nofollow\">{$gift_image}</a>
							</span>
							<span class=\"user-feed-gift-info\">
								{$item["pagetitle"]}
							</span>
							</div>";
							break;
						case "gift-rec":
							$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$item_html .= "received a <a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\" rel=\"nofollow\">gift</a> from {$user_link_2} {$item_time}</span>
									<div class=\"user-feed-item-gift\">
									<span class=\"user-feed-gift-image\">
										<a href=\"index.php?title=Special:ViewGift&gift_id={$item["id"]}\" rel=\"nofollow\">{$gift_image}</a>
									</span>
									<span class=\"user-feed-gift-info\">
										{$item["pagetitle"]}
									</span>
									</div>
							";
							break;
						case "system_gift":
							$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"gift\" />";
							$item_html .= "received an <a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\" rel=\"nofollow\">award</a> {$item_time}</span>
									<div class=\"user-home-item-gift\">
									<span class=\"user-home-gift-image\">
										<a href=\"index.php?title=Special:ViewSystemGift&gift_id={$item["id"]}\" rel=\"nofollow\">{$gift_image}</a>
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
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\" rel=\"nofollow\">challenge</a>";
							$item_html .= "issued an accepted {$challenge_link} to {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>
							";
							break;
						case "challenge_rec":
							$challenge_link = "<a href=\"index.php?title=Special:ChallengeView&id={$item["id"]}\" rel=\"nofollow\">challenge</a>";
							$item_html .= "accepted a {$challenge_link} from {$user_link_2} {$item_time}</span>
							<div class=\"user-feed-item-comment\">{$item["pagetitle"]}</div>
							";
							break;
						case "system_message":
							$item_html .= "{$item["comment"]} {$item_time}</span>";
							break;
						case "user_message":
							$item_html .= "wrote on <a href=\"{$user_title_2->getFullURL()}\">{$item["comment"]}'s</a> <b><a href=\"" . UserBoard::getUserBoardURL($user_title_2->getText()) . "\" rel=\"nofollow\">board</a></b>  {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									\"{$item["namespace"]}\"
									</div>";
							break;
						case "network_update":
							$page_link = "<a href=\"" . SportsTeams::getNetworkURL($item["sport_id"],$item["team_id"]) . "\" rel=\"nofollow\">" . $item["network"] . "</a> ";
							$network_image = SportsTeams::getLogo($item["sport_id"],$item["team_id"],"s");
							$item_html .= "has a thought for the {$page_link} Network {$item_time}</span>
									<div class=\"user-feed-item-comment\">
									{$network_image} \"{$item["comment"]}\"
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
				
				$by_type = "";
				if($items_html_type["network_update"]){
					foreach($items_html_type["network_update"] as $item){
						$by_type .= $item;	
					}
				}else{
					$by_type = "no recent updates";
				}
				$output .= "<div id=\"recent-network_updates\" style=\"display:none\">$by_type</div>";
				
			} else {
				if ( $wgUser->getName() == $wgTitle->getText() ) {
					$output .= "<p>Why haven't you done anyting yet?  <a href='index.php?title=Special:InviteContacts' rel=\"nofollow\">Invite some friends</a>, <a href='index.php?title=Create_Opinion' rel=\"nofollow\">write an article</a>, or <a href='index.php?title=Special:Randompage' rel=\"nofollow\">edit a random article</a>.</p>";
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
			$gifts = $g->getUserGiftList(0,8);
			$wgMemc->set( $key, $user_gifts );
		}else{
			wfDebug( "Got profile gifts for user {$user_name} from cache\n" );
			$gifts = $data;
		}

	
		$gift_count = $g->getGiftCountByUsername($user_name);
		$per_row = 4;
		
	
			$output .= "<h1 class=\"user-profile-title\">Gifts (" . ($gift_count).")</h1>
					<p class=\"profile-sub-links\">
						<a href=\"index.php?title=Special:ViewGifts&user={$user_safe}\" rel=\"nofollow\">All Gifts</a>
					</p>";
	
		if($gifts){
			$x = 1;
			foreach ($gifts as $gift) {
	
				if($gift["status"] == 1 && $user_name==$wgUser->getName() ){
					$g->clearUserGiftStatus($gift["id"]);
					$wgMemc->delete( $key );
					
				}
				
				$user =  Title::makeTitle( NS_USER  , $gift["user_name_from"]  );
				$avatar = new wAvatar($gift["user_id_from"],"s");
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' />";
			
				$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" />";
				$gift_link = $user =  Title::makeTitle( NS_SPECIAL  , "ViewGift"  );
				
				$output .= "<div class=\"user-page-gift\">
						<div " . (($gift["status"] == 1)?"class=\"user-page-gift-image-new\"":"class=\"user-page-gift-image\"") . ">
							<a href=\"{$gift_link->getFullURL()}&gift_id={$gift["id"]}\" rel=\"nofollow\">{$gift_image}</a>
						</div>";

				$output .= "</div>";
				if($x==count($gifts) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
				$x++;	
			}
		} else {
			if ( $wgUser->getName() == $wgTitle->getText() ) {
				$output .= "<p>You don't have any gifts . . . yet.  The more gifts you <a href='index.php?title=Special:GiveGift' rel=\"nofollow\">give</a>, the more gifts you get!";
			 } else {
				$output .= "<p>Give {$wgTitle->getText()} a <a href=\"index.php?title=Special:GiveGift&user={$user_safe}\" rel=\"nofollow\">gift</a> . . . it's the nice thing to do.";
			 }
		}
		//$output .= "</div>";
		
		return $output;
	}

	function getUserPageAwards($user_name){
		global $IP, $wgUser, $wgTitle, $wgMemc;

		require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
		require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
		
 
		//SYSTEM GIFTS
		$sg = new UserSystemGifts($user_name);
		
		//try cache
		$sg_key = wfMemcKey( 'system_gifts', 'profile', "{$sg->user_id}" );
		$data = $wgMemc->get( $sg_key );
		if(1==1){ //!$data){
			$system_gifts = $sg->getUserGiftList(0,8);
			$wgMemc->set( $sg_key, $system_gifts );
		}else{
			wfDebug( "Got profile gifts for user {$user_name} from cache\n" );
			$system_gifts = $data;
		}
		
		$system_gift_count = $sg->getGiftCountByUsername($user_name);
		$per_row = 4;
		
		$output .= "<h1 class=\"user-profile-title\">Awards (" . ($system_gift_count).")</h1>
				<p class=\"profile-sub-links\">
					<a href=\"index.php?title=Special:ViewSystemGifts&user={$user_safe}\" rel=\"nofollow\">All Awards</a>
				</p>";
	
		if($system_gifts){
			$x = 1;
			foreach ($system_gifts as $gift) {
	
				if($gift["status"] == 1 && $user_name==$wgUser->getName() ){
					$sg->clearUserGiftStatus($gift["id"]);
					$wgMemc->delete( $sg_key );
				}
			
				
				$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" />";
				$gift_link = $user =  Title::makeTitle( NS_SPECIAL  , "ViewSystemGift"  );
				
				$output .= "<div class=\"user-page-gift\">
						<div " . (($gift["status"] == 1)?"class=\"user-page-gift-image-new\"":"class=\"user-page-gift-image\"") . ">
							<a href=\"{$gift_link->getFullURL()}&gift_id={$gift["id"]}\" rel=\"nofollow\">{$gift_image}</a>
						</div>";

				$output .= "</div>";
				if($x==count($system_gifts) || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
				$x++;	
			}
		} else {
			if ( $wgUser->getName() == $wgTitle->getText() ) {
				$output .= "<p>You don't have any awards . . . yet.  Start doing stuff!";
			 } else {
				$output .= "<p>{$wgTitle->getText()} has no awards";
			 }
		}
	 
		
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
			  $output .= '<span class="profile-on">' . "<a href=\"index.php?title=Special:ViewGifts\" rel=\"nofollow\">{$gift_count} New Gift" . (($gift_count>1)?"s":"") . "</a></span>";
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
			  $output .= '<img src="images/common/awardIcon.png" alt="" border="0"/> ';
			  $output .= '<span class="profile-on">' . "<a href=\"index.php?title=Special:ViewSystemGifts>{$gift_count}\" rel=\"nofollow\"> New Award" . (($gift_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;		
	}

	function getProfileSection($label,$value,$required=true){
		global $wgUser, $wgTitle, $wgOut;
		
		if($value || $required){
			if(!$value){
				if ( $wgUser->getName() == $wgTitle->getText()  ) {
					$value = 'Update Your Profile';	
				}else{
					$value = 'Not Provided';	
				}
			}
			$value = $wgOut->parse( trim($value), false );
			 
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
							<a href=\"index.php?title=Special:UpdateProfile\" rel=\"nofollow\">Edit Your Profile</a>
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
			<div class=\"user-stats-data\" style=\"width:300px\"><b>Level</b>: <span   style=\"font-size:14px;\"><a href=\"{$level_link->getFullURL()}\" rel=\"nofollow\">{$user_level->getLevelName()}</a></span></div>
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
					<a href=\"index.php?title=Special:TopFansRecent&period=weekly\" rel=\"nofollow\">This Week's Top Fans</a> -  
					<a href=\"index.php?Special:TopFans\" rel=\"nofollow\">Complete List</a>
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
				<td><b>Messages Sent</b>: {$stats_data["user_board_sent"]}</td>
			</tr>
			<tr>
				<td><b>Weekly Points Wins</b>: {$stats_data["weekly_wins"]}</td>
				<td><b>Monthly Points Wins</b>: {$stats_data["monthly_wins"]}</td>
			</tr>
			
			<tr>
				<td><b>Poll Votes</b>: {$stats_data["poll_votes"]}</td>
				<td><b>Picture Game Votes</b>: {$stats_data["picture_game_votes"]}</td>
			</tr>
			<tr>
				<td><b>Quiz Points</b>: {$stats_data["quiz_points"]}</td>
				<td><b>Quiz Questions Correct</b>: {$stats_data["quiz_correct"]}</td>
			</tr>
			<tr>
				<td><b>Quiz Correct %</b>: {$stats_data["quiz_correct_percent"]}%</td>
				<td><b>Quiz Created</b>: {$stats_data["quiz_created"]}</td>
			</tr>
			<tr>
				<td><b>Pick 'Em Points</b>: {$stats_data["currency"]}</td>
				<td></td>
			</tr>
		</table>";
		$output .= "</div>";
		
		return $output;
	}
	
	function getNewUsers($limit = 12){
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
		global $wgUploadPath;
		foreach($list as $user) {		
			$avatar = new wAvatar($user["user_id"],"l");
			$avatar_image = "<img src='{$wgUploadPath}/avatars/" . $avatar->getAvatarImage() . "' width=50 alt='avatar' border=\"0\" />";
			if (strpos($avatar_image, 'default_') !== false) {
				$fav =  SportsTeams::getUserFavorites($user["user_id"]);
				if($fav[0]){
					if($fav[0]["team_name"]){
						$avatar_image = "<img src=\"images/team_logos/" . SportsTeams::getTeamLogo($fav[0]["team_id"],"m") . "\" border=\"0\" alt=\"logo\" />";
						
					}else{
						$avatar_image = "<img src=\"images/sport_logos/" . SportsTeams::getSportLogo($fav[0]["sport_id"],"m") . "\" border=\"0\" alt=\"logo\" />";
					}
				}
			}
			
			$user =  Title::makeTitle( NS_USER  , $user["user_name"]  );
			$users .= "<div class=\"featured-fan\" style=\"text-align:center;width:60px;\"><a href=\"{$user->getFullURL()}\" rel=\"nofollow\">{$avatar_image}</a></div>";
			//<div style=\"text-align:center\"><a href=\"{$user->getFullURL()}\">{$user->getText()}</a></div>
			if($x==count($list) || $x!=1 && $x%$per_row ==0)$users .= "<div class=\"cleared\"></div>";
			$x++;
		}
		$register_title = Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
		$similar_title = Title::makeTitle( NS_SPECIAL  , "SimilarFans"  );
		$output .= '<div class="featured-users">';
			$output .= '<h1>New Fans</h1>';
			if($wgUser->isLoggedIn() ){
				$f = SportsTeams::getUserFavorites( $wgUser->getID() );
				if(count($f)>0){
					$fav_title = Title::makeTitle( NS_SPECIAL  , "ViewFans" );
				}else{
					$fav_title = Title::makeTitle( NS_SPECIAL  , "SimilarFans"  );
				}
				$output .= "<p class=\"main-page-sub-links\" style=\"margin-bottom:10px;\"><a href=\"{$similar_title->getFullURL()}\" rel=\"nofollow\">Meet Similar Fans</a> - <a href=\"{$fav_title->getFullURL()}&sport_id={$f[0]["sport_id"]}&team_id={$f[0]["team_id"]}\" rel=\"nofollow\">Fans From Your Favorite Team</a></p>";
			}else{
				$output .= "<p class=\"main-page-sub-links\"><a href=\"{$register_title->getFullURL()}\" rel=\"nofollow\">Meet Similar Fans</a> - <a href=\"{$register_title->getFullURL()}\" rel=\"nofollow\">Fans From Your Favorite Team</a></p>";
			}
			$output .= $users;
		$output .= '</div>';
		return $output;
		 
	}
	
	function getFeaturedUsers(){
		// FEATURED USERS
		$per_row = 4;
		$x = 1;
		$output = "";
		foreach($this->featured_users as $featured_user_id => $featured_user_name) {		
			$avatar = new wAvatar($featured_user_id,"l");
			//$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
			$user =  Title::makeTitle( NS_USER  , $featured_user_name  );
			$users .= "<div class=\"featured-fan\"><a href=\"{$user->getFullURL()}\" rel=\"nofollow\">{$avatar->getAvatarURL()}</a></div>";
			//<div style=\"text-align:center\"><a href=\"{$user->getFullURL()}\">{$user->getText()}</a></div>
			if($x==count($this->featured_users) || $x!=1 && $x%$per_row ==0)$users .= "<div class=\"cleared\"></div>";
				$x++;
		}
		$register_title = Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
		$output .= '<div class="featured-users">';
			$output .= '<h1>Meet Sports Fans</h1>';
			$output .= "<p class=\"main-page-sub-links\"><a href=\"{$register_title->getFullURL()}\" rel=\"nofollow\">Meet Similar Fans</a> - <a href=\"{$register_title->getFullURL()}\" rel=\"nofollow\">Fans From Your Favorite Team</a></p>";
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
				ORDER BY (Comment_Plus_Count) DESC LIMIT 0,5";

		  $comments = "";
		  $dbr =& wfGetDB( DB_MASTER );
		  $res = $dbr->query($sql);
		  while ($row = $dbr->fetchObject( $res ) ) {
			$title2 = Title::makeTitle( $row->page_namespace, $row->page_title);
		
			if($row->Comment_user_id!=0){
				$title = Title::makeTitle( 2, $row->Comment_Username);
				$CommentPoster_Display = $row->Comment_Username;
				$CommentPoster = '<a href="' . $title->getFullURL() . '" title="' . $title->getText() . '" rel=\"nofollow\">' . $row->Comment_Username . '</a>';
				$avatar = new wAvatar($row->Comment_user_id,"s");
				$CommentIcon = $avatar->getAvatarImage();
			}else{
				$CommentPoster_Display = "Anonymous Fanatic";
				$CommentPoster = "Anonymous Fanatic";
				$CommentIcon = "af_s.gif";
			}
			$comment_text = substr($row->comment_text,0,50 - strlen($CommentPoster_Display) );
			if($comment_text != $row->comment_text){
				$comment_text .= "...";
			}
			$comments .= "<div class=\"cod\">";
			$comments .=  "<span class=\"cod-score\">+" . $row->CommentVotePlus . '</span> <img src="images/avatars/' . $CommentIcon . '" alt="" align="middle" style="margin-bottom:8px;" border="0"/> <span class="cod-poster">' . $CommentPoster . "</span>";
			$comments .= "<span class=\"cod-comment\"><a href=\"" . $title2->getFullURL() . "#comment-" . $row->CommentID . "\" title=\"" . $title2->getText() . "\" >" . $comment_text . "</a></span>";
			$comments .= "</div>";
		  }
		
		  $output .= '<h1>Comments of the Day <span class="grey">(last 24 hours)</span></h1>';
		  $output .= $comments;
		
		  return $output;
	}
	
	function getUserBoard($user_id,$user_name){
		global $IP, $wgMemc, $wgUser, $wgTitle, $wgOut;
		if($user_id == 0)return "";
		
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

		if($wgUser->getName() == $user_name){
			$board_blast = "- <a href=\"" . UserBoard::getBoardBlastURL()."\" rel=\"nofollow\">Send Board Blast</a>";
		}
		
		if($wgUser->getName() !== $user_name){
			$board_to_board = "<a href=\"" . UserBoard::getUserBoardToBoardURL($wgUser->getName(),$user_name)."\" rel=\"nofollow\">Board-to-Board</a> - ";
		}
		
		$output .= '<h1>Board</h1>';
		$output .= "<div class=\"user-page-message-top\">
		<span class=\"user-page-message-count\">{$total} Total Message" . (($total!=1)?"s":"") . ". </span> {$board_to_board} <a href=\"" . UserBoard::getUserBoardURL($user_name)."\" rel=\"nofollow\">Show All</a> {$board_blast}</span> 
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
		global $IP, $wgUser, $wgTitle, $wgOut,$wgUploadPath, $wgMemc;
		
		$page_title_id = $wgTitle->getArticleID();
		
		$key = wfMemcKey( 'recenteditors', 'list', $page_title_id );
		$data = $wgMemc->get( $key );
		$editors = array();
		if(!$data ){
			wfDebug( "loading recent editors for page {$page_title_id} from db\n" );
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT DISTINCT rev_user, rev_user_text FROM revision WHERE rev_page = {$page_title_id} and rev_user <> 0 and rev_user_text<>'Mediawiki Default' ORDER BY rev_user_text ASC LIMIT 0,6";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				$editors[] = array( "user_id" => $row->rev_user, "user_name" => $row->rev_user_text);
			}
			$wgMemc->set( $key, $editors, 60 * 5 );
		}else{
			wfDebug( "loading recent editors for page {$page_title_id} from cache\n" );
			$editors = $data;
		}
		
		$output = "";
		
		if ( $wgUser->isLoggedIn() ) {
			$output .= "<div class=\"recent-editors\">";
		} else {
			$output .= "<div class=\"recent-editors-logged-out\">";
		}
		
		
		$output .= "<h2>Recent Editors</h2>";
		 
		foreach($editors as $editor){
			$user_name = ( $editor["user_name"] == substr( $editor["user_name"], 0, 12) ) ?  $editor["user_name"] : ( substr( $editor["user_name"], 0, 12) . "...");
			$avatar = new wAvatar($editor["user_id"],"s");
			$user_title = Title::makeTitle(NS_USER,$editor["user_name"]);
			
			$output .= "<div class=\"recent-editor\">
			<img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" style=\"border:1px solid #dcdcdc;\"/>
				<a href=\"{$user_title->getFullURL()}\" rel=\"nofollow\">{$user_name}</a>
			</div>";
			
		}
		
		$output .= "</div>";
		return $output;
	}
	
	function recentVoters() {
		global $IP, $wgUser, $wgTitle, $wgOut,$wgUploadPath, $wgMemc;;
		
		//gets the page id for the query
		$page_title_id = $wgTitle->getArticleID();

		$key = wfMemcKey( 'recentvoters', 'list', $page_title_id );
		$data = $wgMemc->get( $key );
		$voters = array();
		if(!$data ){
			wfDebug( "loading recent voters for page {$page_title_id} from db\n" );
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT DISTINCT username, vote_user_id, vote_page_id FROM Vote WHERE vote_page_id = {$page_title_id} and vote_user_id <> 0  ORDER BY vote_id desc LIMIT 0,6";
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				$voters[] = array( "user_id" => $row->vote_user_id, "user_name" => $row->username);
			}
			$wgMemc->set( $key, $voters, 60 * 5 );
		}else{
			wfDebug( "loading recent voters for page {$page_title_id} from cache\n" );
			$voters = $data;
		}
		$output = "";
		
		if ( $this->inCategory("Opinions") ) {
		
		$output .= "<div class=\"recent-editors\">";
		$output .= "<h2>Recent Voters</h2>";
			
			foreach($voters as $voter){
				$user_name = ($voter["user_name"] == substr($voter["user_name"], 0, 12) ) ? $voter["user_name"] : ( substr($voter["user_name"], 0, 12) . "...");
				$user_title = Title::makeTitle(NS_USER,$voter["user_name"]);
				$avatar = new wAvatar($voter["user_id"],"s");

				$output .= "<div class=\"recent-editor\">
					<img src=\"{$wgUploadPath}/avatars/{$avatar->getAvatarImage()}\" alt=\"\" border=\"0\" style=\"border:1px solid #dcdcdc;\"/>
					<a href=\"{$user_title->getFullURL()}\" rel=\"nofollow\">{$user_name}</a>

				</div>";

			}
		
		$output .= "</div>";	
			
		}
		
		return $output;
		
	}
	
	/**
	* Pean: Get poll preview
	*/
	function getPollPreview(){
		global $wgUser, $IP;
		require_once("$IP/extensions/wikia/Poll/PollClass.php");
		$output = "<div class=\"main-page-poll\">";
		$output .= '<h1>Take a Poll</h1>';
		$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Special:CreatePoll\" rel=\"nofollow\">Create a Poll</a> - <a href=\"index.php?title=Special:ViewPoll\" rel=\"nofollow\">View All Polls</a></p>";
		$output .= "</div>";
		$p = new Poll();
		$poll = $p->get_random_poll($wgUser->getName());
		if($poll["id"]){
			$poll_link = Title::makeTitle(300,$poll["question"]);
			$output .= "<div class=\"main-page-poll-question\">{$poll["question"]}</div>";
			if( $poll["image"]){
				$poll_image_width = 75;
				$poll_image = Image::newFromName( $poll["image"] );
				$poll_image_url = $poll_image->createThumb($poll_image_width);
				$poll_image_tag = '<img width="' . ($poll_image->getWidth() >= $poll_image_width ? $poll_image_width : $poll_image->getWidth()) . '" alt="" src="' . $poll_image_url . '"/>';
				$output .= "<div class=\"main-page-poll-image\">{$poll_image_tag}</div>";
			}
			$output .= "<div class=\"main-page-poll-choices\">";
			foreach($poll["choices"] as $choice){
				$output .= "<div class=\"main-page-poll-choice\"><a href=\"{$poll_link->getFullURL()}\" rel=\"nofollow\"><input id=\"poll_choice\" type=\"radio\" value=\"10\" name=\"poll_choice\"/> {$choice["choice"]}</a></div>";
			}
			$output .= "</div><div class=\"cleared\"></div>";
		}else{
			$output .= "<p>There are no new polls to vote on. <a href=\"?title=Special:CreatePoll\" rel=\"nofollow\">Create one!</a></p>";
		}
		
		return $output;
	}
	
	/**
	* Pean: Get quiz preview logged out
	*/
	function getQuizPreview(){
		global $wgUser, $IP;
		 
		$output = "<div class=\"main-page-poll\">";
		$output .= '<h1>Play the Quiz Game</h1>';
		$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Special:QuizGameHome&questionGameAction=createForm\" rel=\"nofollow\">Create a Quiz Question</a> - <a href=\"index.php?title=Special:QuizLeaderboard\" rel=\"nofollow\">Quiz Leaderboard</a></p>";
		$output .= "</div>";
		
		$dbr =& wfGetDB( DB_MASTER );
		$id = QuizGameHome::get_next_question($wgUser->getName());
		if($id){
			$sql = "SELECT q_id,q_user_id, q_user_name, q_text, q_flag, q_answer_count, q_answer_correct_count, q_picture, q_date
			FROM quizgame_questions WHERE q_id = $id LIMIT 0,1";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			if($row){
			 
				$output .= "<div class=\"main-page-poll-question\"><a href=\"index.php?title=Special:QuizGameHome&questionGameAction=renderPermalink&permalinkID={$id}\" rel=\"nofollow\">{$row->q_text}</a></div>";
				if( $row->q_picture){
					$quiz_image_width = 75;
					$quiz_image = Image::newFromName( $row->q_picture );
					$quiz_image_url = $quiz_image->createThumb($quiz_image_width);
					$quiz_image_tag = '<img width="' . ($quiz_image->getWidth() >= $quiz_image_width ? $quiz_image_width : $quiz_image->getWidth()) . '" alt="" src="' . $quiz_image_url . '"/>';
					$output .= "<div class=\"main-page-poll-image\">{$quiz_image_tag}</div>";
				}
			}
			 
			$output .= "<div class=\"cleared\"></div>";
		}else{
			$output .= "<p>There are no more quiz games to play. <a href=\"?title=Special:QuizGameHome&questionGameAction=createForm\" rel=\"nofollow\">Create one!</a></p>";
		}
		
		return $output;
	}

	/**
	* Pean: Get quiz preview logged in
	*/
	function getQuizLeaderboardPreview($points){
		global $wgUser, $IP, $wgMemc;
		 
		$output = "<div class=\"main-page-quiz\">";
		$output .= '<h1>Quiz Game</h1>';
		$output .= "<p class=\"main-page-sub-links\"><a href=\"index.php?title=Special:QuizGameHome\" rel=\"nofollow\">Play the Quiz Game</a> - <a href=\"index.php?title=Special:QuizLeaderboard\" rel=\"nofollow\">Quiz Leaderboard</a></p>";
		$output .= "</div>";
		
		$key = wfMemcKey( 'quiz', 'leaderboard', 5 );
		$data = $wgMemc->get( $key );
		
		//no cache, load from db
		if(!$data){
			wfDebug( "loading front page quiz leaderboard data from db\n" );
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT stats_user_id, stats_user_name, stats_quiz_points
				FROM user_stats
				ORDER BY stats_quiz_points DESC LIMIT 0,5
				";
			
			$list = array();
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				 $list[] = array(
					 "user_id"=>$row->stats_user_id,"user_name"=>$row->stats_user_name,
					 "points"=>$row->stats_quiz_points );
			}
		
			$wgMemc->set( $key, $list, 60 * 60 );
		}else{
			wfDebug( "loading front page quiz leaderboard data from cache\n" );
			$list = $data;
		}
		$output .= '<p><div style=\"margin-top:16px;margin-bottom:10px;\"><b>Quiz Leaderboard</b></div>';
		$output .= "<div class=\"top-fans\">";
		foreach($list as $top_user) {		
			$avatar = new wAvatar($top_user["user_id"],"m");
			$user =  Title::makeTitle( NS_USER  , $top_user["user_name"]  );
			
			$output .= "<div class=\"top-fan\"> <a href=\"{$user->getFullURL()}\" rel=\"nofollow\">{$avatar->getAvatarURL()}</a> <span class=\"top-fans-user\"><a href=\"{$user->getFullURL()}\" rel=\"nofollow\">{$top_user["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($top_user["points"])."</b> points</span></div>";
		}
		$output .= "</div>";
		$output .= "<div style=\"margin-top:15px;\">You have <span class=\"profile-on\">{$points} points</span></div>";
		
		return $output;
	}
	
	function getUserPictureGames($user_id) {
		
		global $IP, $wgUser, $wgTitle, $wgOut;
		
		//declare variables
		$output = "";
		
		//query to get the people who are voting
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT id, title, img1, img2, username FROM picturegame_images WHERE userid = {$user_id} ORDER BY id DESC LIMIT 0,3";
		
		
		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		
		$output .= "<h1>Picture Games</h1>";
		
		
		$output .= "<p class=\"profile-sub-links\">
			<a href=\"index.php?title=Special:PictureGameHome\" rel=\"nofollow\">Create a Picture Game</a> -
			<a href=\"index.php?title=Special:PictureGameHome&picGameAction=startGame\" rel=\"nofollow\">Play the Picture Game</a> -
			<a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery\" rel=\"nofollow\">All Games</a>
		</p>";
		
		//loop through picture games
		
		$x = 1;
		
		if ($row) {
			
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
						<a href=\"index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id={$picture_game_id}\" rel=\"nofollow\">{$title}</a>
					</span>
					<div class=\"cleared\"></div>
				
			</div>";
			
			$x++;
			
		} else {
			$output .= "<div class=\"picture-game-row-top\">{$wgTitle->getText()} has not created any picture games. <img src=\"images/common/emoticon-sad.gif\" style=\"vertical-align:middle;\"/></div>";
		}
				
		
		
		return $output;
		
		
	}
	
	function getUserPolls($user_id, $user_name){
		$dbr =& wfGetDB( DB_MASTER );
		$output .= "<h1>Polls</h1>";
		
		
		$output .= "<p class=\"profile-sub-links\">
			<a href=\"index.php?title=Special:ViewPoll&user=" . urlencode($user_name)  . "\" rel=\"nofollow\">View All</a> -
			<a href=\"index.php?title=Special:CreatePoll\" rel=\"nofollow\">Create a Poll</a> -
			<a href=\"index.php?title=Special:RandomPoll\" rel=\"nofollow\">Take a Poll</a>
		</p><p>";
		
		$sql = "SELECT page_title, poll_user_id, UNIX_TIMESTAMP(poll_date) as poll_time, poll_vote_count,poll_user_name, poll_text, poll_page_id, page_id FROM poll_question INNER JOIN page ON poll_page_id=page_id WHERE poll_user_id={$user_id} order by poll_id DESC LIMIT 0,3";
		$res = $dbr->query($sql);
		
		if($dbr->numRows($res) > 0){
			while ( $row = $dbr->fetchObject($res) ) {
				$poll_title = Title::makeTitle( 300  , $row->page_title  );
				$output .= "<div>- <a href=\"{$poll_title->getFullURL()}\" style=\"text-decoration:none;font-weight:bold;\" rel=\"nofollow\">{$row->poll_text}</a></div>";
			}
		}else{
			$output .= "<div class=\"picture-game-row-top\">{$user_name} has not created any polls. <img src=\"images/common/emoticon-sad.gif\" style=\"vertical-align:middle;\"/></div>";
		}
		
		return $output;
	}
	
	function getUserQuiz($user_id, $user_name,$stats_data){
		$dbr =& wfGetDB( DB_MASTER );
		$output .= "<h1>Quiz Game</h1>";
		
		
		$output .= "<p class=\"profile-sub-links\">
			<a href=\"index.php?title=Special:ViewQuizzes&user=" . urlencode($user_name)  . "\" rel=\"nofollow\">View All</a> -
			<a href=\"index.php?title=Special:QuizGameHome&questionGameAction=launchGame\" rel=\"nofollow\">Play Quiz Game</a> -
			<a href=\"index.php?title=Special:QuizGameHome&questionGameAction=createForm\" rel=\"nofollow\">Create Quiz Question</a>
		</p><p>";
		if( $stats_data["quiz_answered"] ){
			$output .= "<div style=\"margin-bottom:10px;\"><span style=\"color:red;font-weight:800;font-size:14px;\">{$stats_data["quiz_points"]} Quiz Points</span> <span style=\"color:#666666;\">({$stats_data["quiz_correct"]} correct out of {$stats_data["quiz_answered"]} answered - <b>{$stats_data["quiz_correct_percent"]}%</b>)</span></div>";
		}else{
			$output .= "<div class=\"picture-game-row-top\">{$user_name} has not played the quiz game yet. <img src=\"images/common/emoticon-sad.gif\" style=\"vertical-align:middle;\"/></div>";
		}//
		$output .= "<div style=\"margin-bottom:5px;\"><b>Questions Created</b></div>";
		 
		$sql = "SELECT q_id, q_text, UNIX_TIMESTAMP(q_date) as quiz_date  FROM quizgame_questions WHERE q_user_id={$user_id} order by q_id DESC LIMIT 0,3";
		$res = $dbr->query($sql);
		
		if($dbr->numRows($res) > 0){
			while ( $row = $dbr->fetchObject($res) ) {
				$poll_title = Title::makeTitle( 300  , $row->page_title  );
				$output .= "<div>- <a href=\"index.php?title=Special:QuizGameHome&questionGameAction=renderPermalink&permalinkID={$row->q_id}\" style=\"text-decoration:none;font-weight:bold;\" rel=\"nofollow\">{$row->q_text}</a></div>";
			}
		}else{
			$output .= "<div class=\"picture-game-row-top\">{$user_name} has not created any quiz questions. <img src=\"images/common/emoticon-sad.gif\" style=\"vertical-align:middle;\"/></div>";
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
