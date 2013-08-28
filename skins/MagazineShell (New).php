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

	/*
	function getHeadScripts() {
		global $wgStylePath, $wgUser, $wgAllowUserJs, $wgJsMimeType, $wgStyleVersion;

		$r = self::makeGlobalVariablesScript( array( 'skinname' => $this->getSkinName() ) );

		$r .= "<base href=\"http://{$_SERVER["HTTP_HOST"]}\"><script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/wikibits.js?$wgStyleVersion\"></script>\n";
		
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
		
			//	$r .= '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>';
        //$r .= '<script type="text/javascript">' . "\n";
       // $r .= '_uacct = "UA-1328449-1";' . "\n";
        //$r .= 'urchinTracker();' . "\n";
       // $r .= '</script>' . "\n";
		return $r;
	}	
	*/
	
  	/**
	 * Return html code that include User stylesheets
	 */
	function getUserStyles() {
		$s = "<style type='text/css'>\n";
		$s .= "/*/*/ /*<![CDATA[*/\n"; # <-- Hide the styles from Netscape 4 without hiding them from IE/Mac
		$s .= $this->getUserStylesheet();
		$s .= "/*]]>*/ /* */\n";
		$s .= "</style>\n";
		
		$s .= "<!--[if lte IE 6]><style type=\"text/css\" media=\"all\">@import \"skins/common/commonie.css\";</style><![endif]-->\n";
		$s .= "<!--[if gt IE 6]><style type=\"text/css\" media=\"all\">@import \"skins/common/commonie7.css\";</style><![endif]-->\n";
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
		$s .= "src=\"http://tags.expo9.exponential.com/tags/Wikiacom/Sports/tags.js\">\n";
		$s .= "</script>";
		
		  //$s .= "<!-- TF {$width}x{$height} JScript VAR NoAD code -->\n";
		  //$s .= "<center><script language=javascript><!--\n";
		  //$s .= "document.write('<script language=javascript src=\"http://a.tribalfusion.com/j.ad?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&noAd=1&requestID='+((new Date()).getTime() % 2147483648) + Math.random()+'\"></scr'+'ipt>');\n";     
		  //$s .= "//-->\n";
		  //$s .= "</script>\n";
		  //$s .= "<noscript>\n";
		  //$s .= "<a href=\"http://a.tribalfusion.com/i.click?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&requestID=995263280\" target=_blank>\n";
		  //$s .= "<img src=\"http://a.tribalfusion.com/i.ad?site=Wikiacom&adSpace={$site}&size={$width}x{$height}&requestID=995263280\" width=$width height=$height border=0 alt=\"Click Here\"></a>\n";
		  //$s .= "</noscript>\n";
		  //$s .= "</center>\n";
		  //$s .= "<!-- TF {$width}x{$height} JScript VAR NoAD code -->";
		  return $s;
	}
	
	function getFooter() {
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
	
	function inviteBox(){
		global $wgUser;
		  $invite_title = Title::makeTitle( NS_SPECIAL  , "InviteContacts"  );
		  
		  $dbr =& wfGetDB( DB_SLAVE );
		  $sql = "SELECT ur_user_name,ur_user_id, ur_user_name_referral,ur_user_id_referral FROM user_register_track where ur_user_id_referral <> 0 ORDER BY ur_date DESC LIMIT 0,1";
		  $res = $dbr->query($sql);
		  $row = $dbr->fetchObject( $res );
		  if($row){
			  $user_name_1 = $row->ur_user_name_referral;
			  $user_name_2 = $row->ur_user_name;
			  $title1 = Title::makeTitle( NS_USER  , $user_name_1 );
			  $title2 = Title::makeTitle( NS_USER  , $user_name_2  );
			  $avatar1 = new wAvatar($row->ur_user_id_referral,"s");
			  $avatar2 = new wAvatar($row->ur_user_id,"s");
			  
			  if($wgUser->getID()==0 || $wgUser->getIntOption( 'showsharelink',1)==1){
			  $out = "
			  	<script type=\"text/javascript\">
					function remove_invite_box(){
						var url = \"index.php?action=ajax&rs=wfRemoveShareLink\";
						var myAjax = new Ajax.Request(url, {method: 'GET', parameters: ''});
						Element.hide(\"invite\");
					}
				</script>
			  	<style>
				.invite-box{
					width:300px;
					font-size:10px;
					color:#666666;
				}
				</style>
			  	<div class=\"invite-box\" id=\"invite\">
				<div  style=\"float:right;\"><span style=\"cursor:hand;cursor:pointer\"><a href=\"javascript:void(0);\" onclick=\"javascript:remove_invite_box()\"><img src=\"images/closeWindow.gif\" border=\"0\" /></a></span></div>
					<div class=\"invite-title\">share the 'chair</div>
					<div class=\"invite-last\">
					<img src=\"images/avatars/{$avatar1->getAvatarImage()}\" alt='' border=''><a href=\"{$title1->getFullURL()}\">{$user_name_1}</a> recruited <img src=\"images/avatars/{$avatar2->getAvatarImage()}\" alt='' border=''><a href=\"{$title2->getFullURL()}\">{$user_name_2}</a>
					</div>
					<div class=\"invite-link\"><a href=\"{$invite_title->getFullURL()}\">Invite Friends</a></div>
				</div>";
			  }
					
		  }
		  return $out;
		
	}
	
	function userBox() {
		global $wgUser, $wgChallengesEnabled, $IP;

		$stats = new UserStats($wgUser->getID(), $wgUser->getName());
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		
		$s = "";
		if ( $wgUser->isLoggedIn() ) {
		  $s .= "<div class=\"userbox\">";
		  $s .= "<h1>Welcome ".$wgUser->getName()."</h1>";
		    $avatar = new wAvatar($wgUser->getID(),"m");
		    $s .= "<p class=\"userboximage\">";
		      $s .= $this->makeKnownLinkObj( $wgUser->getUserPage(), '<img src=images/avatars/' . $avatar->getAvatarImage() . '/>');
		    $s .= "</p>";
		    $s .= "<p class=\"userboxlinks\">";
		    	$s .= "<p class=\"pointsline\"><span class=\"profile-on\">+{$stats_data["points"]}</span> points</p>";
		    	$s .= "<p><a href=\"{$level_link->getFullURL()}\">{$user_level->getLevelName()}</a></p>";
		    $s .= "<div class=\"cleared\"></div>";
		    $s .= "</p>";
		    
		    //upload avatar
		    if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
			    $s .= "<p style=\"margin-top:10px;\">";
			    	$s .= "<span class=\"red\"><a href=index.php?title=Special:UploadAvatar>";
			    		$s .= "<img src=\"images/sportstweak/avatarIcon.png\" alt=\"avatar icon\" border=\"0\"/> ";
			    		$s .= "Upload your Avatar!";
			    	$s .= "</a></span>";
			    $s .= "</p>";
		    } 
		    
		    //new message
		    if ( $wgUser->getNewtalk() ) {
		    	$s .= "<p>";
					$s .= "<img src=\"images/common/emailIcon.png\" alt=\"email icon\" border=\"0\"/> ";
					$s .= "<span class=\"red\">" . $this->makeKnownLinkObj($wgUser->getTalkPage(), "New Message!") . "</span>";
				$s .= "</p>";
		    }
		    
		    //new challenge
		    if($wgChallengesEnabled){
		     
		      $dbr =& wfGetDB( DB_SLAVE );
		      $challenge = $dbr->selectRow( '`challenge`', array( 'challenge_id'),
		      array( 'challenge_user_id_2' => $wgUser->mId , 'challenge_status' => 0), "" );
		      $title1 = Title::makeTitle( NS_USER  , $wgUser->mName  );
		      if ( $challenge > 0 ) {
			      $s .= '<p>';
			      $s .= '<img src="images/common/challengeIcon.png" alt="challenge icon" border="0"/> ';
			      $s .= '<span class="red">' . "<a href=index.php?title=Special:ChallengeHistory&user=" . $title1->getDbKey() . "&status=0>New Challenge</a></span>";
			      $s .= '</p>';
		      }
		   
		    }
	      
		    //new friend/foe request
		    $s .= $this->getRelationshipRequestLink();
		    
		    //new gift
		    $s .= $this->getNewGiftLink();
		    
		    $s .= '</div>';
		    
		    $s .= '<div class="friendbox">';
		    	$s .= '<h1>Find Friends</h1>';
		    	$s .= '<p><a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/msnIconSmall.png" alt="challenge icon" border="0"/> Hotmail</a></p>';
		    		$s .= '<p><a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/yahooIconSmall.png" alt="challenge icon" border="0"/> Yahoo</a></p>';
		    		$s .= '<div class="cleared"></div>';
		    		$s .= '<p><a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/gmailIconSmall.png" alt="challenge icon" border="0"/> Gmail</a></p>';
		    		$s .= '<p><a href="index.php?title=Special:InviteContacts"><img src="../../images/sportstweak/aolIconSmall.png" alt="challenge icon" border="0"/> Aol</a></p>';
		    		$s .= '<div class="cleared"></div>';
		    $s .= '</div>';
		}
		return $s;
	}

	function shareBox() {
		  global $wgTitle, $wgOut;
		  $s = '';
		  if ( $wgOut->isArticle() ) {
		  $s .= '<div class="sharebox"><h1>Share This</h1>';
		  $s .= "<p><script>function fbs_click() {u=location.href;t=document.title;window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');return false;}</script><a href=\"javascript:void(0)\" onclick=\"return fbs_click()\" target=\"_blank\" class=\"fb_share_link\">Facebook</a></p>";
		    $s .= '<p><a href="http://digg.com/submit?phase=2&amp;url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'"><img src="images/common/diggIcon.png" alt="digg icon" border="0"/> Digg</a></p>';
		    $s .= '<p><a href="http://reddit.com/submit?url='.$wgTitle->getFullURL().'&amp;title='.$wgTitle->getText().'""><img src="images/common/redditIcon.png" alt="reddit icon" border="0"/> Reddit</a></p>';
		    $s .= '<p><a href="http://del.icio.us/post"><img src="images/common/deliciousIcon.png" alt="delicious icon" border="0"/> Delicious</a></p>';
		    $s .= '</div>';
		  }
		  return $s;
	}

	function thisArticle() {
		  global $wgOut, $wgTitle, $wgUser;
		  $s = "";
		  if ( $wgOut->isArticle() ) {
		   $s .= "<div class=\"thisarticle\">";
		   	 $s .= "<h1>Page Tools</h1>";
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
		$output = "";
		$output .= "<p><div style=\"font-size:16px;color:#666666;font-weight:800;padding-bottom:2px;\"><span class=\"profile-on\">+{$stats_data["points"]}</span> points <span style=\"font-size:12px\"><a href=\"{$level_link->getFullURL()}\">({$user_level->getLevelName()}</a>)</span></div></p>";
			$output .= "<p><div style=\"font-size:11px;padding-bottom:5px;\">To advance to <span style=\"font-weight:800\"><a href=\"{$level_link->getFullURL()}\">{$user_level->getNextLevelName()}</a></span> earn <i>{$user_level->getPointsNeededToAdvance()}</i> more points!</div></p>";
		$output .= '<p><img src="images/common/userpageIcon.png" alt="userpage icon" border="0"/> '.$this->makeKnownLinkObj( $wgUser->getUserPage(), "your profile").'</p>';
		$output .= '<p><img src="images/common/homeIcon.png" alt="userpage icon" border="0"/> <a href="index.php?title=Special:UserHome">your home</a></p>';  
		$output .= '<p><img src="images/common/emailIcon.png" alt="email icon" border="0"/> ';
		    if ( $wgUser->getNewtalk() ) {
		      $output .= '<span class="profile-on">' . $this->makeKnownLinkObj($wgUser->getTalkPage(), "new message!") . '</span>';
		    } else {
		      $output .= $this->makeKnownLinkObj($wgUser->getTalkPage(), "no new messages");
		    }
		  $output .= '</p>';
		  
		  if($wgChallengesEnabled){
			  $title1 = Title::makeTitle( NS_USER  , $wgUser->getName()  );
			    $dbr =& wfGetDB( DB_SLAVE );
			    $challenge = $dbr->selectRow( '`challenge`', array( 'challenge_id'),
			    array( 'challenge_user_id_2' => $wgUser->getID() , 'challenge_status' => 0), "" );
			    if ( $challenge > 0 ) {
				     $output .= '<p>';
				     $output .= '<img src="images/common/challengeIcon.png" alt="challenge icon" border="0"/> ';
				     $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ChallengeHistory&user=" . $title1->getDbKey() . "&status=0  style='color:#990000;font-weight:bold'>new challenge</a></span>";
				     $output .= '</p>';
			    }else{
			      //$output .= '<a href="index.php?title=Special:ChallengeHistory&user=' . $title1->getDbKey() . '">no new challenges</a>';
			    }
			 
		  }    
		  $output .= $this->getRelationshipRequestLink();
		  $output .= $this->getNewGiftLink();
		  if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
		    $output .= '<p><a href=index.php?title=Special:UploadAvatar>add a profile image</a></p>';
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
			  $output .= "<span class=\"red\"><a href=index.php?title=Special:ViewRelationshipRequests>{$friend_request_count} New Friend" . (($friend_request_count>1)?"s":"") . " Request</a></span>";
			  $output .= '</p>';
		 }
		if($foe_request_count){
			  $output .= '<p>';
			  $output .= '<img src="images/common/addedFoeIcon.png" alt="challenge icon" border="0"/> ';
			  $output .= "<span class=\"red\"><a href=index.php?title=Special:ViewRelationshipRequests>{$foe_request_count} New Foe" . (($foe_request_count>1)?"s":"") . " Request</a></span>";
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
		
		//$wgMemc->delete( $key );
		
		//try cache
		if(!$data){
			$friends = $rel->getRelationshipList($rel_type,6);
			$wgMemc->set( $key, $friends );
		}else{
			wfDebug( "Got profile relationship type {$rel_type} for user {$user_name} from cache\n" );
			$friends = $data;
		}
		
		$rel_count = $rel->getRelationshipCountByUsername($user_name);
		$stats = new UserStats($rel->user_id,$user_name);
		$stats_data = $stats->getUserStats();
		$friend_count = $stats_data["friend_count"];
		$foe_count = $stats_data["foe_count"];
		
		if($rel_type==1){
			$output .= "<div class=\"user-title-bar\">
						<div class=\"user-title-bar-title\">Friends ({$friend_count})</div>
						<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Special:ViewRelationships&user={$user_name}&rel_type={$rel_type}\">view all</a></div>
						<div class=\"cleared\"></div>
					</div>";
		}else{
			$output .= "<div class=\"user-title-bar\">
						<div class=\"user-title-bar-title\">Foes ({$foe_count})</div>
						<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Special:ViewRelationships&user={$user_name}&rel_type={$rel_type}\">view all</a></div>
						<div class=\"cleared\"></div>
					</div>";	
		}
		if($friends){
			$x = 1;
			$per_row = 4;
			foreach ($friends as $friend) {
				$user =  Title::makeTitle( NS_USER  , $friend["user_name"]  );
				$avatar = new wAvatar($friend["user_id"],"l");
				
				$user_name = substr($friend["user_name"],0,13);
				if($user_name != $friend["user_name"]){
					$user_name .= "..";
				}
		
				$avatar_img = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\"/>";
				$output .= "<div class=\"user-page-rel\">
								<div class=\"user-page-rel-image\">
									<a href=\"{$user->getFullURL()}\" title=\"{$friend["user_name"]}\">{$avatar_img}</a>
								</div>
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
				    $output .= "<p>Boo! {$wgTitle->getText()} has no friends . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$wgTitle->getText()}&rel_type=1\">friend!</a>";
			    }
		    } else {
			    if ( $wgUser->getName() == $wgTitle->getText() ) {
				    $output .= "<p>No foes, no fun . . . <a href='index.php?title=Special:InviteContacts'>Start a war!</a></p>";
			    } else {
				    $output .= "<p>{$wgTitle->getText()} has no foes.  Start a war . . . Make {$wgTitle->getText()} your <a href=\"index.php?title=Special:AddRelationship&user={$wgTitle->getText()}&rel_type=2\">foe!</a>";
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
		
				
		$output .= "<div class=\"user-feed\">";
			$output .= "<div class=\"user-feed-title-bar\">";
				$output .= "<div class=\"user-feed-title\">";
					$output .= "My Activity";
				$output .= "</div>";
				$output .= "<div class=\"user-feed-menu\">";
					$output .= "<a href=\"javascript:view_user_activity('all');\">all</a> <a href=\"javascript:view_user_activity('edits');\"><img src=\"../../images/sportstweak/editIconGrey.png\"></a> <a href=\"javascript:view_user_activity('comments');\"><img src=\"../../images/sportstweak/commentIconGrey.png\"></a> <a href=\"javascript:view_user_activity('friends');\"><img src=\"../../images/sportstweak/friendIconGrey.png\"></a> <a href=\"javascript:view_user_activity('foes');\"><img src=\"../../images/sportstweak/foeIconGrey.png\"></a> <a href=\"javascript:view_user_activity('gifts-sent');\"><img src=\"../../images/sportstweak/giftIconGrey.png\"></a> <a href=\"javascript:view_user_activity('gifts-rec');\"><img src=\"../../images/sportstweak/giveGiftIconGrey.png\"></a> <a href=\"javascript:view_user_activity('messages');\"><img src=\"../../images/sportstweak/systemMessageIconGrey.png\"></a> <a href=\"javascript:view_user_activity('system_gifts');\"><img src=\"../../images/sportstweak/awardIconGrey.png\"></a>";
				$output .= "</div>";
				$output .= "<div class=\"cleared\"></div>";
			$output .= "</div>";
			
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
			
			$output .= "</div>
			<div class=\"cleared\"></div>
			";
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
		
		//try cache
		$key = wfMemcKey( 'gifts', 'profile', "{$g->user_id}" );
		$data = $wgMemc->get( $key );
		
		if(!$data){
			$user_gifts = $g->getUserGiftList(0,12);
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
			$system_gifts = $sg->getUserGiftList(0,12);
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
		$per_row = 4;
		
		
		$output .= '<div class="user-title-bar">';
			$output .= "<div class=\"user-title-bar-title\">Gifts and Awards (" . ($gift_count+$system_gift_count).")</div>";
			$output .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Special:ViewGifts&user={$user_name}\">gifts</a></div>";
			
			$output .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Special:ViewSystemGifts&user={$user_name}\">awards</a></div>";
			$output .= "<div class=\"cleared\"></div>";
		$output .= '</div>';
		
		//$output .= "<div class=\"user-page-gifts\">
		
		
		//<div class=\"user-page-gifts-title\">gifts and awards (" . ($gift_count+$system_gift_count).") <span class=\"user-page-gift-link\"><a href=\"index.php?title=Special:GiveGift".(($user_name != $wgUser->getName())?"&user={$user_name}":"")."\">[give a gift]</a> view all <a href=\"index.php?title=Special:ViewGifts&user={$user_name}\">[gifts]</a> <a href=\"index.php?title=Special:ViewSystemGifts&user={$user_name}\">[awards]</a></span></div>";	

		//$output .= "<div class=\"cleared\"></div>"; 
	
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
					$gift_image = "<img src=\"images/awards/" . SystemGifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" />";
					$gift_link = $user =  Title::makeTitle( NS_SPECIAL  , "ViewSystemGift"  );
				}else{
					$gift_image = "<img src=\"images/awards/" . Gifts::getGiftImage($gift["gift_id"],"l") . "\" border=\"0\" alt=\"gift\" />";
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
				$output .= "<p>Give {$wgTitle->getText()} a <a href=\"index.php?title=Special:GiveGift&user={$wgTitle->getText()}\">gift</a> . . . it's the nice thing to do.";
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
			  $output .= '<span class="profile-on">' . "<a href=index.php?title=Special:ViewGifts  style='color:#990000;font-weight:bold'>{$gift_count} new gift" . (($gift_count>1)?"s":"") . "</a></span>";
			  $output .= '</p>';
		 }
		 return $output;		
	}

	function getUserProfile($user){
		global $wgUser, $wgTitle, $IP, $wgMemc;
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		
		//try cache first
		$key = wfMemcKey( 'user', 'profile', $user );
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
		$output .= "<div class=\"user-profile\">";
			
			if ( $wgUser->getName() == $wgTitle->getText()  ) {
			  $message = 'Update Your Profile';	
			} else {
			  $message = 'Not Provided';	
			}
			
			$output .= '<div class="user-title-bar">';
				$output .= "<div class=\"user-title-bar-title\">More About Me</div>";
				if ($wgUser->getName() == $wgTitle->getText()) {
					$output .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?title=Special:UpdateProfile\">update</a></div>";
				}
				$output .= "<div class=\"cleared\"></div>";
			$output .= '</div>';
			$output .= "<div class=\"user-profile-data\"><b>Name</b>: ";
				if($profile_data["real_name"]) {
					$output .= $profile_data["real_name"];
				} else {
					$output .= $message;
				}
			$output .= "</div>";		
			$output .= "<div class=\"user-profile-data\"><b>Location</b>: ";
				if($profile_data["location_city"]) {
					$output .= $profile_data["location_city"] . ", " . $profile_data["location_state"];
					if($profile_data["location_country"]!="United States"){
						$output .= $profile_data["location_country"];
					}
				} else {
					$output .= $message;
				}
			$output .= "</div>";		
			$output .= "<div class=\"user-profile-data\"><b>Hometown</b>: ";
				if($profile_data["hometown_city"]) {
					$output .= $profile_data["hometown_city"] . ", " . $profile_data["hometown_state"];
					if($profile_data["hometown_country"]!="United States"){
						$output .= $profile_data["hometown_country"];
					}
				} else {
					$output .= $message;
				}
			$output .= "</div>";
			$output .= "<div class=\"user-profile-data\"><b>Birthday</b>: ";
				if ($profile_data["birthday"]) {
					$output .= $profile_data["birthday"];
				} else {
					$output .= $message;
				}
			$output .= "</div>";
		$output .= "</div>";
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
			<div class=\"user-stats-title\">my scoreboard</div>
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
		$output .= "<div class=\"user-stats\">";
			$output .= '<div class="user-title-bar">';
				$output .= "<div class=\"user-title-bar-title\">My Stats</div>";
				$output .= "<div class=\"user-title-bar-tab\"><a href=\"index.php?Special:TopFans\">top fans</a></div>";
				$output .= "<div class=\"cleared\"></div>";
			$output .= '</div>';

		$output .= "<div class=\"user-stats-data\"><b>Edits</b>: {$stats_data["edits"]}</div>
			<div class=\"user-stats-data\"><b>Votes</b>: {$stats_data["votes"]}</div>
			<div class=\"cleared\"></div>
			<div class=\"user-stats-data\"><b>Opinions</b>: {$stats_data["opinions_created"]} ({$stats_data["opinions_published"]})</div>
			<div class=\"user-stats-data\"><b>Comments</b>: {$stats_data["comments"]}</div>
			<div class=\"cleared\"></div>
			<div class=\"user-stats-data\"><b>Comment Score</b>: {$stats_data["comment_score"]}</div>
			<div class=\"user-stats-data\"><b>Thumbs up</b>: {$stats_data["comment_score_plus"]}</div>
			<div class=\"cleared\"></div>
			<div class=\"user-stats-data\"><b>Users Recruited</b>: {$stats_data["recruits"]}</div>
			<div class=\"user-stats-data\"><b>Challenges Won</b>: {$stats_data["challenges_won"]}</div>
			<div class=\"cleared\"></div>
			</div>";
		
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
		
		  $output = '<div class="smallList">';
		  $output .= '<h1>comments of the day <span class="grey">(last 24 hours)</span></h1>';
		  $output .= $comments;
		  $output .= '</div>';
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
