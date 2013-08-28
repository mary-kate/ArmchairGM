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
require_once("$IP/extensions/ListPages/ListPagesClass.php");
require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");


class SkinHP extends SkinMagazineShell {
	
  
  #set stylesheet
  function getStylesheet() {
    return "common/HP.css";
  }
  
  #set skinname
  function getSkinName() {
    return "HP";
  }
  
  #main page before wiki content
  function doBeforeContent() {
	
  	##global variables
  	global $wgOut, $wgTitle, $wgParser, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView, $wgArticle, $IP, $wgMemc;	
  
  
  	$output = "";
  	$output .= "<div class=\"container\">
  	
		<div class=\"top-ad-unit\">
			<a href=\"http://www.hp.com/blackbird\"><img src=\"images/HP/728-90-unit.gif\"/></a>
		</div>

		<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
			<tr>
				<td class=\"page-container\">
					<div class=\"page-top\">

						<div class=\"page-top-images\">

							<p>
								<img src=\"images/HP/kevin-rose.jpg\"/><br>
								Kevin Rose
							</p>

							<p>
								<img src=\"images/HP/jimmy-wales.jpg\"/><br>
								Jimmy Wales
							</p>

							<p align=\"center\">
								<img src=\"images/HP/joi-ito.jpg\"/><br>
								Joi Ito
							</p>

							<p>
								<img src=\"images/HP/morgan-webb.jpg\"/><br>
								Morgan Webb
							</p>

							<div class=\"cleared\"></div>

						</div>

						<div class=\"page-top-text\">

							We know you need an awesome computer to keep up with you.  Tell us about the coolest mods you've seen. Brag about
							your ultimate gaming machine, or even describe a gaming machine that would make us all drool.  
							Click <a href=\"index.php?title=Create_Article\">write</a> below to get started!

						</div>

						<div class=\"page-top-links\">";

							if ($wgUser->isLoggedIn()) {

								$output .= "You are logged in.<br>
								<a href=\"index.php?title=Special:Recentchanges\">Recent Changes</a> - <a href=\"index.php?title=Special:Userlogout\">Log-Out</a>";

							} else {
								$output .= "You are not logged in.<br>
								<a href=\"index.php?title=Special:UserRegister\">Sign Up</a> or <a href=\"index.php?title=Special:Login\">Log-In</a>";
							}



						$output .= "</div>

						<div class=\"cleared\"></div>

					</div>

					<div class=\"page-navigation\">

						<ul>

							<li><a href=\"index.php?title=Main_Page\">Home</a></li>";

							if ($wgUser->isLoggedin()) {
								$output .= "<li><a href=\"index.php?title=User:{$wgUser->getName()}\">Profile</a></li>"; 
							}

							$output .= "<li><a href=\"index.php?title=Create_Article\">Write</a></li>

							<li><a href=\"index.php?title=Special:ArticleLists\">New Articles</a></li>

						</ul>


					</div>
					
					<div class=\"page-right\">

						<div class=\"left-ad-unit\">
							<a href=\"http://www.hp.com/blackbird\"><img src=\"images/HP/300-250-unit.gif\"/></a>
						</div>


						<div class=\"right-articles\">";

							$friend_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),1);
							$new_messages = UserBoard::getNewMessageCount($wgUser->getID());

							if ( $new_messages || $friend_request_count ) {

								$output .= "<div class=\"site-notifications\">
									<h2>Site Messages</h2>";

								    if ( $new_messages ) {
									    	$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
											$output .= "<p>";
											$output .= "<img src=\"images/common/emailIcon.png\" border=\"0\"/> ";
											$output .= "<span class=\"profile-on\"><a href=\"" . $board_link->getFullURL() . "\">New Message</a></span>";
											$output .= "</p>";
								    }
									$output .= $this->getRelationshipRequestLink();
								$output .= "</div>";

							}


							if ($wgOut->getPageTitle() == "Main Page") {

								$list = new ListPages();
								$list->setCategory("Articles,Opinions");
								$list->setShowCount(6);
								$list->setOrder("New");
								$list->setBool("ShowVoteBox","YES");
								$list->setShowPublished("NO");
								$list->setBool("ShowCtg","NO");
								$list->setBool("ShowDate","NO");
								$list->setBool("ShowStats","NO");


								$output .= "<h2>New Articles</h2>" . $list->DisplayList() . 
							"</div>";

							} else {

								$list = new ListPages();
								$list->setCategory("Articles,Opinions");
								$list->setShowCount(6);
								$list->setOrder("PublishedDate");
								$list->setShowPublished("YES");
								$list->setBool("ShowVoteBox","YES");
								$list->setBool("ShowCtg","NO");
								$list->setBool("ShowDate","NO");
								$list->setBool("ShowStats","NO");

								$output .= "<h2>Popular Articles</h2>" . $list->DisplayList() . 
							"</div>";

							}

						$output .= "<div class=\"new-users\">
							<h2>New Users</h2>
							".$this->getNewUsersNoMenu(12)."
						</div>
					</div>
					
					<div class=\"page-left\">";

						if ($wgOut->getPageTitle() == "Main Page") {


							$list = new ListPages();
							$list->setCategory("Articles");
							  $list->setShowCount(10);
							  $list->setOrder("PublishedDate");
							  $list->setShowPublished("YES");
							  $list->setShowBlurb("300");
							   $list->setBlurbFontSize("small");
							  $list->setBool("ShowVoteBox","NO");
							  $list->setBool("ShowDate","NO");
							  $list->setBool("ShowStats","YES");
							  $list->setBool("ShowCtg","NO");
							  $list->setBool("ShowNav","YES");
							  $list->setBool("ShowPic","YES");

							$output .= "
							<div class=\"left-articles\">
								<h2>Popular Articles</h2>" . 
								$list->DisplayList() . 
							"</div>	
						</div>";

						}  else if (NS_USER == $wgTitle->getNamespace()) {

							//variables and other crap
							$page_title = $wgTitle->getText();
				    		$title_parts = explode("/",$page_title);
				    		$user = $title_parts[0];
							$id=User::idFromName($user);


				    		$relationship = UserRelationship::getUserRelationshipByID($id,$wgUser->getID());
							$avatar = new wAvatar($id,"l");


							$output .= "<div class=\"profile-top\">
								<div class=\"profile-image\">";
								$output .= "<img src=\"images/avatars/" . $avatar->getAvatarImage() . "\"/>";

								if ( $wgUser->getName() == $wgTitle->getText()  ) {
									$output .= '<p>';
									if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
										$output .= '<a href=index.php?title=Special:UploadAvatar>(upload image)</a>';
									} else {
										$output .= '<a href=index.php?title=Special:UploadAvatar>(new image)</a>';
									}
						 			$output .= '</p>';
								}
							$output .= "</div>";
							$output .= "<div class=\"profile-heading\">
								<div class=\"profile-title\">
									{$user}
								</div>
								<div class=\"profile-link\">";
									if ( $wgUser->getName() !== $wgTitle->getText()  ) {
										if ($relationship==true) {
					      					$output .= "<span class=\"profile-on\" style=\"margin:0px 5px 0px -3px;\">
													<b>Your Friend</b>
											</span> ";
					 					} else {
											$output .= "<a href=\"index.php?title=Special:AddRelationship&user=" . urlencode($user) . "&rel_type=1\">
												<b>Add as Friend</b>
											</a> ";
						    			}
									}
								$output .= "</div>
							</div>
							<div class=\"cleared\"></div></div>";

							//user friends
							$output .= "<div class=\"user-friends\">";
				  				$output .= $this->getUserPageRelationshipsNoMenu($wgTitle->getText(),1);
				  			$output .= "</div>";

							//user-profile
				  			$output .= "<div class=\"user-profile\">";
								$output .= $this->getUserProfile($wgTitle->getText());
							$output .= "</div>";

							//user activity
				  			$output .= "<div class=\"user-board\">";
				  				$output .= $this->getUserBoard($id,$wgTitle->getText());
				  			$output .= "</div>";


						} else {

							//add edit menu
							if ((NS_SPECIAL !== $wgTitle->getNamespace()) && ($wgOut->getPageTitle() !== "Create Article") && ($wgOut->getPageTitle() !== "Create Article Standard")) {
								$output .= "<div class=\"edit-menu-hp\">";
					   				$output .= "<div class=\"edit-button\" >";

										if ( ! $wgOut->isArticleRelated() ) {
											$output .= "<img src=\"{$wgUploadPath}/images/common/editIcon.gif\"/>";
											$output .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
											$output .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
										} else {
											if ( $wgTitle->userCan( 'edit' ) ) {
												$output .= "<img src=\"{$wgUploadPath}/images/common/editIcon.gif\"/>";
												$output .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Edit</a>";
												$output .= "<span style=\"margin:0px 0px 0px 29px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
											} else {
												$output .= "<img src=\"{$wgUploadPath}/images/common/editIcon.gif\"/>";
												$output .= "<a href=\"{$wgTitle->getFullURL()}&action=edit\">Locked</a>";
												$output .= "<span style=\"margin:0px 0px 0px 9px;\"><a href=\"javascript:editMenuToggle();\"><img src=\"images/common/edit-menu-arrow.gif\"/></a></span>";
											}
										}

									$output .= "</div>";
									$output .= "<div class=\"edit-sub-menu-hp\" id=\"edit-sub-menu-id\" style=\"display:none;\">";
										$output .= '<p><img src="'.$wgUploadPath.'/images/common/pagehistoryIcon.png" alt="page history icon" border="0"/> ' . $this->historyLink() . '</p>';
								   		if ( $wgTitle->userCanMove() ) {
									 		$output .= '<p><img src="'.$wgUploadPath.'/images/common/moveIcon.png" alt="move icon" border="0"/> ' . $this->moveThisPage() . '</p>';
								       	}
								       	$output .= '<p><img src="images/common/whatlinkshereIcon.png" alt="what links here icon" border="0"/> ' . $this->whatLinksHere() . '</p>';
								       	if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists() ) {
									 		$output .= '<p><img src="'.$wgUploadPath.'/images/common/protectIcon.png" alt="protect icon" border="0"/> ' . $this->protectThisPage() . '</p>';
								       	}
								       	if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace() && $wgTitle->exists()) {
									 		$output .= '<p><img src="'.$wgUploadPath.'/images/common/deleteIcon.png" alt="delete icon" border="0"/> ' . $this->deleteThisPage() . '</p>';
								       	}
								       	if ( $wgUser->isLoggedIn() ) {
									   		$output .= '<p><img src="'.$wgUploadPath.'/images/common/addtowatchlistIcon.png" alt="watchlist" border="0"/> ' . $this->watchThisPage() . '</p>';
									 		$output .= '<p><img src="'.$wgUploadPath.'images/common/uploadIcon.png" alt="upload" border="0"/> ' . $this->specialLink("upload") . '</p>';
								       }
					   				$output .= "</div>";
								$output .= "</div>";
							}


							//add page title and subtitle
							$output .= $this->pageTitle();
							if ($this->subPageSubtitle()) {
						     	$output .= "<p class=\"sub-title\">".$wgOut->getSubtitle().$this->subPageSubtitle()."</p>";
						   	}

						}
		
			
    
		
     
  return $output;
  
}
 
function doAfterContent() {
 
	global $wgOut, $wgUser, $wgTitle;
	
	
	
	//add category links
		
		
		$output .= "</div><div class=\"cleared\"/>";
		$cat = $this->getCategoryLinks();

	  	//categories
	  	if( $cat ) $output .= "<div class=\"categories\">$cat</div>";
		
		$output .= "</td></tr></table>";
		$output .= "<div class=\"footer\">
				<p>
					<a href=\"index.php?title=Main_Page\">Home</a> -
					<a href=\"index.php?title=Special:Specialpages\">Special Pages</a> -
					<a href=\"http://www.wikia.com/wiki/Terms_of_use\">Terms of Use</a>
				</p>
				<p>
					<a href=\"http://www.mediawiki.org/wiki/MediaWiki\"><img src=\"images/HP/powered-mw.gif\"/></a>
					<a href=\"http://www.gnu.org/licenses/fdl.html\"><img src=\"images/HP/gfdl-logo.gif\"/></a>
				</p>
			</div>
		</div>";
   
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
		$output .= "<h2>Personal</h2>";
				if ($wgUser->getName() == $wgTitle->getText()) {
						$output .= "<p class=\"profile-sub-links\" style=\"margin-bottom:10px;\">
							<a href=\"index.php?title=Special:UpdateProfile\">Edit Your Profile</a>
						</p>";
				}
		
		if($wgUser->getIntOption("blackbirdenroll") == 1){
		 	$output .= "<div >
					I am entered to win a Blackbird 002
				</div>";
		}
						
		$hometown = $profile_data["hometown_city"] . ", " . $profile_data["hometown_state"];
		if($profile_data["hometown_country"]!="United States"){
			$hometown .= $profile_data["hometown_country"];
		}
		if($hometown==", ")$hometown="";
		
		$location = $profile_data["location_city"] . ", " . $profile_data["location_state"];
		if($profile_data["location_country"]!="United States"){
			$location .= $profile_data["location_country"];
		}
		if($location==", ")$location="";	
		$output .= $this->getProfileSection("Location",$location);
		$output .= $this->getProfileSection("Hometown",$hometown);
		$output .= $this->getProfileSection("Birthday",$profile_data["birthday"]);
		$output .= $this->getProfileSection("Occupation",$profile_data["occupation"]);
		$output .= $this->getProfileSection("Websites",$profile_data["websites"]);
		$output .= $this->getProfileSection("Places I've Lived",$profile_data["places_lived"],false);
		$output .= $this->getProfileSection("Schools",$profile_data["schools"],false);   
		$output .= $this->getProfileSection("About Me",$profile_data["about"],false);
		
		
		return $output;
	}
	
 function getMainPage(){
 }
 
}

?>
