<?php
class MainPage extends Article {

	var $title = null;

	function __construct (&$title){
		parent::__construct(&$title);
	}

	function view() {
		
		global $wgOut, $wgUser, $wgRequest, $wgTitle, $wgRandomImageSize;
		
		$wgRandomImageSize = 90;
		$wgOut->setHTMLTitle( wfMsg( 'pagetitle', "Main Page" ) );
		
		$sk = $wgUser->getSkin();

		if( $wgUser->isLoggedIn() ){
			$this->viewLoggedIn();
		}else{
			$wgOut->addHTML( $this->viewAnon() );
		}
	
	}

	function viewLoggedIn() {
		global $wgOut;
		
		$output = "";
		$output .= "<div class=\"mp-left\">";
		//$output .= $this->getTopStory();
		$output .= $this->getArticles();
		$output .= "</div>";
		$wgOut->addHTML($output);
		
		$wgOut->addHTML("<div class=\"mp-middle\">");
		$wgOut->addHTML($this->getLinks());
		$wgOut->addHTML($this->getInTheNews());
		$this->outputVideoOfDay();
		$wgOut->addHTML(wfGetRandomGameUnit());
		$wgOut->addHTML($this->getFeaturedEditors(7));
		$wgOut->addHTML($this->getCommentsOfTheDay() );
		$wgOut->addHTML($this->getNewUsers());
		$wgOut->addHTML('</div>');
		
		$output = "";
		$output .= "<div class=\"mp-right\">";
		
		$output .= $this->getWelcome();
		$output .= $this->getRequests();
		$output .= $this->getSiteActivity();
		
		$output .= '</div>';
		$output .= "<div class=\"cleared\"></div>";
		$wgOut->addHTML($output);

	}
	

	
	function viewAnon() {
		
		global $wgOut;
		
		$output = "";
		$output .= "<div class=\"mp-left\">";
		//$output .= $this->getTopStory();
		$output .= $this->getArticles();
		$output .= "</div>";
		$wgOut->addHTML($output);
		
		$wgOut->addHTML("<div class=\"mp-middle\">");
		$wgOut->addHTML($this->getLinks());
		$wgOut->addHTML($this->getInTheNews());
		$this->outputVideoOfDay();
		$wgOut->addHTML(wfGetRandomGameUnit());
		$wgOut->addHTML($this->getFeaturedEditors(7));
		$wgOut->addHTML($this->getCommentsOfTheDay() );
		$wgOut->addHTML($this->displayTextAd());
		$wgOut->addHTML('</div>');
		
		$output = "";
		$output .= "<div class=\"mp-right\">";
		
		$output .= $this->getAnonFeaturedUsers();
		$output .= $this->getSiteActivity();
		
		$output .= '</div>';
		$output .= "<div class=\"cleared\"></div>";
		$wgOut->addHTML($output);
	}
	
	function getTopStory(){
		global $wgMemc;
		
		$top_story_time = (60 * 60 * 400);
		$top_story_time_ago = time() - $top_story_time; //every four hours should produce a new top story
		//echo $top_story_time_ago;
		//exit();
		$dbr =& wfGetDB( DB_MASTER );
		
		$key = wfMemcKey( 'mainpage', 'top-story' );
		$data = $wgMemc->get( $key );
		$wgMemc->delete($key);
		if( $data ){
			$top_story = $data;
			wfDebug("**** load top story from cache\n");
		}else{
			//fetch top story through db
			wfDebug("**** load top story from db\n");
			$params['ORDER BY'] = "vote_count desc";
			$s = $dbr->selectRow( '`page` INNER JOIN wikia_page_stats on page_id = ps_page_id
					 INNER JOIN imagelinks on il_from = page_id
					', 
					array('page_id','il_to',
				
					
					),
				" (select UNIX_TIMESTAMP(rev_timestamp) as create_date from revision where rev_page=page_id order by rev_timestamp asc limit 1) >  {$top_story_time_ago} " //UNIX_TIMESTAMP(rev_timestamp) > {$top_story_time_ago}"
				, __METHOD__, 
				$params
			);
			if ( $s !== false ) {
				$top_story["page_id"] = $s->page_id;
				$top_story["published"] = $s->create_date;
				$top_story["image_name"] = $s->il_to;
				$wgMemc->set( $key, $top_story, $top_story_time );
			}
			$key_data = wfMemcKey( 'mainpage', 'top-story', $top_story["page_id"] );
			$wgMemc->delete($key);
		}
		
		if( $top_story["page_id"] ){
			$key = wfMemcKey( 'mainpage', 'top-story', $data["page_id"] );
			$data = $wgMemc->get( $key );
			$wgMemc->delete($key);
			if( $data ){
				wfDebug("**** load top story data from cache\n");
				$top_story["page_title"] = $data["page_title"];
				$top_story["vote_count"] = $data["vote_count"];
				$top_story["comment_count"] = $data["comment_count"];
				
			}else{
				wfDebug("**** load top story data from db\n");
				unset($params['ORDER BY']);
				$s = $dbr->selectRow( '`page` INNER JOIN wikia_page_stats on page_id = ps_page_id
						', 
						array('page_id', 'page_title', 'vote_count', 'comment_count'
						
						),
					" page_id = {$top_story["page_id"]}"
					, __METHOD__, 
					$params
				);
				if ( $s !== false ) {
					$top_story["page_title"] = $s->page_title;
					$top_story["vote_count"] = $s->vote_count;
					$top_story["comment_count"] = $s->comment_count;
					$wgMemc->set( $key, $top_story, $top_story_time );
				}
			}
		}else{
			//no top story..this shouldn't happen
		}
		
		if( is_array( $top_story ) ){
			$top_story_title = Title::makeTitle( NS_BLOG, $top_story["page_title"] );
			$img = Image::newFromName($top_story["image_name"]);
			$thumb = $img->getThumbnail( 175, 0 , true );
			$img_tag = $thumb->toHtml();
			
			$output = "<div class=\"mp-top-story\">
				<div class=\"mp-top-story-title\">
					<a href=\"{$top_story_title->escapeFullURL()}\">{$top_story_title->getText()}</a>
				</div>
				<div class=\"mp-top-story-content\">
					<div class=\"mp-top-story-image\">
						{$img_tag}
					</div>
					<div class=\"mp-top-story-preview\">
						" . $this->getTopStoryPreview( $top_story_title , 700 ) . "
					</div>
					<div class=\"cleared\"></div>
				</div>
				<div class=\"mp-top-story-stats\">
				<img border=\"0\" alt=\"\" src=\"/images/common/voteIcon.gif\"/> {$top_story["vote_count"]} vote" . (($top_story["vote_count"]!=1)?"s":"") . " 
				<img border=\"0\" alt=\"\" src=\"/images/common/comment.gif\"/> {$top_story["comment_count"]} comment" . (($top_story["comment_count"]!=1)?"s":"") . "
				</div>
				<div class=\"mp-top-story-divider\"></div>
			</div>";
			
			return $output;
		}		
	
	}
	
	function getLinks(){
		global $IP, $wgOut, $wgMessageCache, $wgMemc;
		require_once ( "$IP/extensions/wikia/LinkFilter/LinkClass.php" );
		
		//language messages
		require_once ( "$IP/extensions/wikia/LinkFilter/LinkFilter.i18n.php" );
		foreach( efWikiaLinkFilter() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		$count = 10;
		$key = wfMemcKey( 'mainpage', 'linkfilter', $count );
		$data = $wgMemc->get( $key );
		if ( $data ){
			wfDebug("loaded mainpage linkfilter from cache\n");
			$links = $data;
		} else {
			wfDebug("loaded mainpage linkfilter from db\n");
			$l = new LinkList();
			$links = $l->getLinkList(LINK_APPROVED_STATUS, "", 10, 1, "link_approved_date");
			$wgMemc->set( $key, $links, 60 * 5 );
		}
		
		$link_redirect = Title::makeTitle( NS_SPECIAL, "LinkRedirect");
		$link_submit = Title::makeTitle(NS_SPECIAL, "LinkSubmit");
		$link_all = Title::makeTitle(NS_SPECIAL, "LinksHome");
		
		$output .= "<div class=\"mp-linkfilter\">
				<div class=\"mp-title-container\">
					<div class=\"mp-title\">
						Hot Links
					</div>
					<div class=\"mp-title-links\">
						<a href=\"".$link_submit->escapeFullURL()."\">Submit</a> / <a href=\"".$link_all->escapeFullURL()."\">All</a> / <a href=\"{$wgServer}/rss/links.xml\">RSS</a>
					</div>
					<div class=\"cleared\"></div>
				</div>";
		
		foreach($links as $link){
			$output .= "<div class=\"link-item\">
				<span class=\"link-item-type\">
					{$link["type_name"]}
				</span>
				<span class=\"link-item-url\">
					<a href=\"" . $link_redirect->escapeFullURL("link=true") . "&url=" . urlencode($link["url"]) . "\" rel=\"nofollow\" target=new>{$link["title"]}</a>
				</span>
				<span class=\"link-comments\">
					<a href=\"{$link["wiki_page"]}\">(" . wfMsgExt("linkfilter-comments", "parsemag", $link["comments"] ) . ")</a>
				</span>
			</div>";
		}
		$output .= "</div>";
		
		return $output;
	}
	
	function getTopStoryPreview($title,$max){
		global $wgTitle, $wgOut, $wgContLang;
		
		$article = new Article( $title );
		$text = $article->getContent();
	
		//remove some problem characters
		$text =  str_replace("* ","",$text);
		$text =  str_replace("===","",$text);
		$text =  str_replace("==","",$text);
		$text =  str_replace("{{Comments}}","",$text);
		$text =  preg_replace('@<youtube[^>]*?>.*?</youtube>@si', '', $text);
		$text =  preg_replace('@<vote[^>]*?>.*?</vote>@si', '', $text);
		$text =  preg_replace("@\[\[" . $wgContLang->getNsText( NS_CATEGORY ) . ":[^\]]*?].*?\]@si", '', $text);
		
		//start looking at text after content, and force no Table of Contents
		$pos = strpos($text,"<!--start text-->");
		if($pos !== false){
			$text = substr($text,$pos);
		}
		
		$text = "__NOTOC__ " . $text;
		
		//run text through parser
		$BlurbParser = new Parser();
		$blurb_text = $BlurbParser->parse( $text, $wgTitle, $wgOut->parserOptions(),true );
		$blurb_text = strip_tags($blurb_text->getText());
		//$blurb_text = $text;
		$pos = strpos($blurb_text,"[");
		if($pos !== false){
			$blurb_text = substr($blurb_text,0,$pos);
		}
		
		//Take first N characters, and then make sure it ends on last full word
		if (strlen($blurb_text) > $max)$blurb_text = strrev(strstr(strrev(substr($blurb_text,0,$max)),' '));
	
		
		//fix multiple whitespace, returns etc
		$blurb_text = trim($blurb_text); // remove trailing spaces
		$blurb_text = preg_replace('/\s(?=\s)/','',$blurb_text); // remove double whitespace
		$blurb_text = preg_replace('/[\n\r\t]/',' ',$blurb_text); // replace any non-space whitespace with a space
		
		return $blurb_font . $blurb_text. ". . . <a href=\"" . $title->escapeFullURL() . "\">more</a>";
	}

	function outputVideoOfDay(){
		global $wgOut, $wgTitle;
		$lines = explode( "\n\n", wfMsg("videooftheday") );
		$today = date("n/j/y", time());
		
		$edit_link = Title::makeTitle(NS_MEDIAWIKI, "Videooftheday");
		
		foreach( $lines as $line ){
			
			$line_array = explode("|", $line);
			 
			//invalid line
			if( count( $line_array ) < 2 ){
				continue;
			}
		
			$date = $line_array[0];
			$video_name = $line_array[1];
			
			if( $video_name && $date == $today ){
				if( count( $line_array ) > 2 ){
					$description = $line_array[2];
				}
				$video = Video::newFromName( $video_name );
				if( $video->exists() ){
					$video->setWidth( 250 );
					$wgOut->addHTML("<div class=\"mp-video-of-day\">
						
						<h2>Video of the Day</h2>");
						
						if ( $description ) {
							
							$wgOut->addHTML("<div class=\"mp-video-of-day-desc\">");
							$wgOut->addWikiText($description);
							$wgOut->addHTML("</div>");
						}
						
					$wgOut->addHTML($video->getEmbedCode());
							
					$wgOut->addHTML("</div>");
					
				}
			}
		}
							
	}
	
	function getInTheNews() {
		
		global $wgOut, $wgUser;
		
		$news_array = explode( "\n\n", wfMsg("inthenews") );
		
		$edit_link = Title::MakeTitle(NS_TEMPLATE, "Inthenews");
		
		$output .= "<div class=\"mp-in-thenews\">
			<h2>".wfMsg("mp_in_the_news_title");
			$output .= "";
			$output .= "</h2>";
			$news_item = $news_array[ array_rand( $news_array ) ];
			
			$output .= $wgOut->parse("$news_item", false);
			
		$output .= "<div class=\"cleared\"></div>
		</div>";
		
		return $output;
		
	}
	
	function getRequests() {
		
		//get requests
		$requests = $this->getNewMessagesLink() . $this->getRelationshipRequestLink() . $this->getNewGiftLink() . $this->getNewSystemGiftLink();
		
		if ($requests) {
			
			$output .= "<div class=\"mp-requests\">
				<h2>".wfMsg("mp_requests_title")."</h2>
				<div class=\"mp-requests-message\">
					".wfMsg("mp_requests_message")."
				</div>
				$requests
			</div>";

		 }
		
		return $output;
		
	}
	
	function getWelcome(){
		
		global $wgUser, $IP, $wgUploadPath;
		$wgUploadPath       = "http://fp029.sjc.wikia-inc.com/images";
		
		require_once("$IP/extensions/wikia/UserProfile/UserProfileClass.php");
		
		//get votes, edit, comment count
		$dbr =& wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
		$vote_count = number_format($s->count);
		$s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
		$comment_count = number_format($s->count);
		$edits_count = number_format(SiteStats::edits());
		$good_count = number_format(SiteStats::articles());
		
		//get stats and user level
		$stats = new UserStats($wgUser->getID(), $wgUser->getName());
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel($stats_data["points"]);
		
		//safe links
		$level_link = Title::makeTitle(NS_HELP,"User Levels");
		$avatar_link = Title::makeTitle(NS_SPECIAL,"UploadAvatar");
		$invite_link = Title::makeTitle(NS_SPECIAL,"InviteContacts");
		
		//make an avatar
		$avatar = new wAvatar($wgUser->getID(),"ml");
		
		$output = "";
		
		//PROFILE TOP IMAGES/POINTS
		//<h2>".wgGetWelcomeMessage()."</h2>

		$output .= "<div class=\"mp-welcome-logged-in\">
			<h2>Welcome {$wgUser->getName()}</h2>
			<div class=\"mp-welcome-image\">
				<a href=\"". $wgUser->getUserPage()->escapeFullURL(). "\" rel=\"nofollow\"><img src=\"$wgUploadPath/avatars/" . $avatar->getAvatarImage() . "\" alt=\"\" border=\"0\"/></a>";
				if (strpos($avatar->getAvatarImage(), 'default_') !== false) {
					$output .= "<div><a href=\"".$avatar_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_welcome_upload")."</a></div>";
				} else {
					$output .= "<div><a href=\"".$avatar_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_welcome_edit")."</a></div>";
				}
			$output .= "</div>
			<div class=\"mp-welcome-points\">
				<div class=\"points-and-level\">
					<div class=\"total-points\">".wfMsg("mp_welcome_points", $stats_data["points"])."</div>
					<div class=\"honorific-level\"><a href=\"".$level_link->escapeFullURL()."\">({$user_level->getLevelName()})</a></div>
				</div>
				<div class=\"cleared\"></div>
				<div class=\"needed-points\">
					".wfMsg("mp_welcome_needed_points", $level_link->escapeFullURL(), $user_level->getNextLevelName(), $user_level->getPointsNeededToAdvance())."
				</div>
			</div>
			<div class=\"cleared\"></div>";
			
			$output .= "</div>";
		  
		return $output;
	
	}
	
	function getAnonWelcome() {
		
		global $wgUploadPath;
		
		$dbr =& wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
		$vote_count = number_format($s->count);
		$s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
		$comment_count = number_format($s->count);
		$edits_count = number_format(SiteStats::edits());
		$good_count = number_format(SiteStats::articles());
		$register_link = Title::makeTitle(NS_SPECIAL,"UserRegister");
		
		$output .= "<div class=\"mp-anon-welcome\">
			<h1>Connect with Sports Fans</h1>
			<div class=\"mp-anon-welcome-message\">ArmchairGM is a community for passionate sports fans.  Read, write, and talk about sports.  Meet other fans of your favorite teams.  Rate players, teams, and sporting events.  Earn points and receive gifts.</div>
			<div class=\"mp-anon-welcome-stats\"><img src=\"{$wgUploadPath}/common/pagesIcon.gif\" border=\"0\" alt=\"\"/>
				<b>Pages</b>: {$good_count} <img src=\"{$wgUploadPath}/common/pencilIcon.gif\" border=\"0\" alt=\"\"/> 
				<b>Edits</b>: {$edits_count} <img src=\"{$wgUploadPath}/common/voteIcon.gif\" border=\"0\" alt=\"\"/>
				<b>Votes</b>: {$vote_count} <img src=\"{$wgUploadPath}/common/comment.gif\" border=\"0\" alt=\"\"/>
				<b>Comments</b>: {$comment_count}
			</div>
			<div class=\"mp-anon-welcome-link\">
				<a href=\"".$register_link->escapeFullURL()."\" rel=\"nofollow\">Sign Up!</a>
			</div>
		</div>";
		
		return $output;
	}
	
	function getAnonArticles(){
		global $IP, $wgUploadPath;
		
		require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");
		
		$list = new ListPages();
		$list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions");
		$list->setShowCount(5);
		$list->setOrder("PublishedDate");
		$list->setShowPublished("YES");
		$list->setShowBlurb("10");
		$list->setBlurbFontSize("small");
		$list->setBool("ShowVoteBox","NO");
		$list->setBool("ShowDate","YES");
		$list->setBool("ShowStats","YES");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowNav","YES");
		$list->setBool("ShowPic","YES");
		
		$articles_home = Title::makeTitle(NS_SPECIAL,"ArticlesHome");
		$register_link = Title::makeTitle(NS_SPECIAL, "UserRegister");
		$write_link = Title::makeTitle(NS_MAIN,"Create Article");
		
		$output .= "<div class=\"mp-article-container\">
			<h2>".
				wfMsg("mp_popular_article_title")."
			</h2>";
			
		$output .= $list->DisplayList();
		$output .= '</div>';
		
		$output .= $this->getRatings();
		
		//New Articles
		$list = new ListPages();
		$list->setCategory("News, Opinions,Questions, ArmchairGM Announcements");
		$list->setShowCount(6);
		$list->setOrder("New");
		$list->setShowPublished("NO");
		$list->setShowBlurb("10");
		$list->setBlurbFontSize("small");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowDate","yes");
		$list->setBool("ShowStats","yes");
		$list->setBool("ShowPic","YES");
		$list->setBool("cache","yes");
		$list->setHash("main page new");
		
		$output .= "<div class=\"mp-article-container\">
			<h2>".wfMsg("mp_new_article_title")."</h2>
			<div class=\"mp-article-links\">
				<a href=\"".$register_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_write_article_link")."</a> - 
				<a href=\"".$articles_home->escapeFullURL()."/New\">".wfMsg("mp_new_all_link")."</a>
			</div>";
			$output .= $list->DisplayList();
		$output .= "</div>";
		
		return $output;
	}
	
	function getRatings(){
		global $wgUser, $IP, $wgOut, $wgVoteDirectory;
		require_once("$wgVoteDirectory/VoteClass.php");
		require_once("$IP/extensions/wikia/ListRatings/ListRatingsClass.php");
		
		$per_row = 1;
		$r = new ListRatings();
		
		$categories = array( "MLB Players","NFL Players","NHL Players","NBA Players");
		$random_category = $categories[ array_rand( $categories ) ];
		
		$r->setCategory( $random_category );
		$ratings = $r->getRatingsList(4,0,"vote_avg","desc", false );
		$x = 1;
		foreach($ratings as $item) {		
			$rating_title = Title::makeTitle( $item["page_namespace"], $item["page_title"]);
				
			$Vote = new VoteStars($item["page_id"]);
			$Vote->setUser($wgUser->getName(),$wgUser->getID());
		
			$ratings_list .= "<div class=\"featured-rating\">
				<a href=\"{$rating_title->escapeFullURL()}\" rel=\"nofollow\">{$rating_title->getText()}</a>
				<div id=\"rating_stars_{$x}\">" .  $Vote->displayStars($x,$item["vote_avg"],false) . "</div>
				<div id=\"rating_{$x}\" class=\"featured-rating-total\">" . $Vote->displayScore() . "</div>
			</div>";
			
			if($x==count($ratings) || $x!=1 && $x%$per_row ==0)$ratings_list .= "<div class=\"cleared\"></div>";
			$x++;
		}
		
		$rate_players = Title::makeTitle( NS_MAIN  , "Ratings"  );
		$rate_teams = Title::makeTitle( NS_MAIN  , "Ratings"  );
 
		$output .= "<div class=\"featured-ratings\">
			<h2>".wfMsg("mp_rate_title", $random_category)."</h2>
			{$ratings_list}
		</div>";
		
		return $output;
	}
	
	function getNewUsers($limit = 10, $per_row = 5){
		global $wgUser, $wgMemc;
		
		$key = wfMemcKey( 'users', 'new', "1", $limit );
		$data = $wgMemc->get( $key );
		
		$list = array();
		
		if (!$data) {
			$dbr =& wfGetDB( DB_MASTER );
			
			if ($limit>0) {
				$limitvalue = 0;
				if($page)$limitvalue = $page * $limit - ($limit); 
				$params['OFFSET'] = $limitvalue;
				$params['LIMIT'] = $limit;
			}
		
			$params['ORDER BY'] = "ur_date DESC";
			$res = $dbr->select( '`user_register_track`'
				, array('ur_user_id', 'ur_user_name' ), 
				/*where*/ ""  , __METHOD__, 
					$params
			);
			
			while ($row = $dbr->fetchObject( $res ) ) {
				 $list[] = array(
					 "user_id"=>$row->ur_user_id,"user_name"=>$row->ur_user_name);
			}
	
			$wgMemc->set( $key, $list );
		
		} else {
			wfDebug( "Got new users from cache\n" );
			$list = $data;
		}
	
		$x = 1;
		$output = "";
		global $wgUploadPath;
		
		foreach($list as $user) {		
			
			$avatar = new wAvatar($user["user_id"],"m");
			$avatar_image = "<img src='{$wgUploadPath}/avatars/" . $avatar->getAvatarImage() . "' width=50 alt='avatar' border=\"0\" />";
			
			if (strpos($avatar_image, 'default_') !== false) {
				
				$fav =  SportsTeams::getUserFavorites($user["user_id"]);
				
				if ($fav[0]) {
					if ($fav[0]["team_name"]) {
						$avatar_image = "<img src=\"{$wgUploadPath}/team_logos/" . SportsTeams::getTeamLogo($fav[0]["team_id"],"m") . "\" border=\"0\" alt=\"\" />";
						
					} else {
						$avatar_image = "<img src=\"{$wgUploadPath}/sport_logos/" . SportsTeams::getSportLogo($fav[0]["sport_id"],"m") . "\" border=\"0\" alt=\"\" />";
					}
				}
			}
			
			$user =  Title::makeTitle( NS_USER  , $user["user_name"]  );
			$users .= "<a href=\"".$user->escapeFullURL()."\" rel=\"nofollow\">{$avatar_image}</a>";
			
			if($x==count($list) || $x!=1 && $x%$per_row ==0)$users .= "<div class=\"cleared\"></div>";
			$x++;
		}
		
		$register_title = Title::makeTitle(NS_SPECIAL, "UserRegister");
		$similar_title = Title::makeTitle(NS_SPECIAL, "SimilarFans");
		
		$output .= "<div class=\"mp-new-users-container\">
				<h2>".wfMsg("mp_new_users")."</h2>
				<div class=\"mp-new-user-message\">
					".wfMsg("mp_new_users_message")."
				</div>";
			$output .= $users;
		
		$output .= "</div>";
		
		return $output;
		 
	}	
	
	function getAnonFeaturedUsers(){
		
		global $IP, $wgOut, $wgTitle, $wgUploadDirectory, $wgDBname;
		
		$files = glob($wgUploadDirectory . "/avatars/{$wgDBname}_*_ml.*");

		
		$user_array = array();
		$random_users = array();
		
		$output .= "<div class=\"mp-anon-featured-users\">
			<h2>Meet the Community</h2>";
			
			$count = 6;
			$per_row = 3;
			$x=1;
			
			if( count( $files ) < $count)$count = count($files);
			$random_keys = array_rand($files, $count);
			
			foreach ($random_keys as $random) {
				
				//extract userid out of avatar image name
				$avatar_name = basename( $files[$random] );
				preg_match("/{$wgDBname}_(.*)_/i", $avatar_name, $matches);
				$user_id = $matches[1];
				
				if( $user_id ){
					//load user
					$user = User::newFromId( $user_id );
					$user->loadFromDatabase();
					$user_name = $user->getName();	
					
					$avatar = new wAvatar($user_id,"ml");
					$user_link = Title::makeTitle(NS_USER, $user_name);
					
					$output .= "<a href=\"".$user_link->escapeFullURL()."\" rel=\"nofollow\">{$avatar->getAvatarURL()}</a>";
					
					if($x==$count || $x!=1 && $x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
					$x++;
					
				}
			}
			
			$output .= "<div class=\"cleared\"></div>
		</div>";
		
		return $output;
				
	}
	
	function getAnonTopFans(){
		$fans = UserStats::getTopFansList(4);
		$x = 1;
		foreach($fans as $fan) {		
			$avatar = new wAvatar($fan["user_id"],"m");
			$avatar_image = "<img src='images/avatars/" . $avatar->getAvatarImage() . "' alt='avatar' border=\"0\" />";
			$user =  Title::makeTitle( NS_USER  , $fan["user_name"]  );
			
			$topfans .= "<div class=\"top-fan\"><span class=\"top-fan-number\">{$x}.</span> <a href=\"{$user->escapeFullURL()}\" rel=\"nofollow\">{$avatar_image}</a> <span class=\"top-fans-user\"><a href=\"{$user->escapeFullURL()}\" rel=\"nofollow\">{$fan["user_name"]}</a></span> <span class=\"top-fans-points\"><b>+" . number_format($fan["points"])."</b> points</span></div>";
			$x++;
			 
		}
		$weekly_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFansRecent"  );
		$top_fans_title = Title::makeTitle( NS_SPECIAL  , "TopFans"  );
		
		$output .= '<div class="top-fans">';
		$output .= '<h1>Earn Points</h1>';
		$output .= "<p class=\"main-page-sub-links\"><a href=\"{$weekly_fans_title->escapeFullURL()}\" rel=\"nofollow\">This Week's Top Fans</a> - <a href=\"{$top_fans_title->escapeFullURL()}\" rel=\"nofollow\">Complete List</a></p>";
		$output .= $topfans;
		$output .= '</div>';
		
		return $output;
	}
	
	function getAnonRecentGifts(){
		global $IP, $wgMemc, $wgUploadPath;
		
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
		
		$gift_title = Title::makeTitle(NS_SPECIAL,"ViewGift");
		foreach($gifts as $gift) {		
			$recent_gifts .= "<a href=\"".$gift_title->escapeFullURL('gift_id='.$gift["id"])."\" rel=\"nofollow\"><img src=\"{$wgUploadPath}/awards/" . Gifts::getGiftImage($gift["gift_id"],"ml")."\" border=\"0\" alt=\"\"/></a>";
		}
	
		$register_link = Title::makeTitle(NS_SPECIAL,"UserRegister");
		$output .= "<div class=\"recent-gift-container\">
			<h2>Receive Gifts and Awards</h2>
			<div class=\"recent-gift-links\">
				<a href=\"".$register_link->escapeFullURL()."\" rel=\"nofollow\">Give a Gift</a> - 
				<a href=\"".$register_link->escapeFullURL()."\" rel=\"nofollow\">Learn About Awards</a>
			</div>
			{$recent_gifts}
			<div class=\"cleared\"></div>
		</div>";	
		
		return $output;
	}
	
	function getQuizPreview(){
		global $wgUser, $IP;
		
		$output .= "<div class=\"picture-game-preview\">";
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
			$output .= "<p>There are no more quiz games to play. <a href=\"index.php?title=Special:QuizGameHome&questionGameAction=createForm\" rel=\"nofollow\">Create one!</a></p>";
		}
		$output .= "</div>";
		return $output;
	}
	
	function getPollPreview(){
		global $wgUser, $IP;
		require_once("$IP/extensions/wikia/Poll/PollClass.php");
		
		$output .= '<div class="picture-game-preview">';
		$create_link = Title::makeTitle(NS_SPECIAL,"CreatePoll");
		$view_link = Title::makeTitle(NS_SPECIAL,"ViewPoll");
		$output = "<div class=\"main-page-poll\">";
		$output .= '<h1>Take a Poll</h1>';
		$output .= "<p class=\"main-page-sub-links\"><a href=\"{$create_link->escapeFullURL()}\" rel=\"nofollow\">Create a Poll</a> - <a href=\"{$view_link->escapeFullURL()}\" rel=\"nofollow\">View All Polls</a></p>";
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
				$output .= "<div class=\"main-page-poll-choice\"><a href=\"{$poll_link->escapeFullURL()}\" rel=\"nofollow\"><input id=\"poll_choice\" type=\"radio\" value=\"10\" name=\"poll_choice\"/> {$choice["choice"]}</a></div>";
			}
			$output .= "</div><div class=\"cleared\"></div>";
		}else{
			$output .= "<p>There are no new polls to vote on. <a href=\"{$create_link->getFullURL()}\" rel=\"nofollow\">Create one!</a></p>";
		}
		
		$output .= '</div>';	
		
		return $output;
	}
		
	function getPictureGamePreview(){
		$output .= "<div class=\"picture-game-preview\">
		<h1>Play the Picture Game</h1>";
		$mt = new PictureGameHome();
		$output .= $mt->getMiniGame();
		$output .= "</div>";	
		
		return $output;
	}
	
	function getArticles(){
		global $IP, $wgUploadPath;
		require_once ("$IP/extensions/wikia/ListPages/ListPagesClass.php");
		
		$list = new ListPages();
		$list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions,ArmchairGM Announcements");
		$list->setShowCount(6);
		$list->setOrder("PublishedDate");
		$list->setShowPublished("YES");
		$list->setShowBlurb("200");
		$list->setBlurbFontSize("small");
		$list->setBool("ShowVoteBox","NO");
		$list->setBool("ShowDate","YES");
		$list->setBool("ShowStats","YES");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowNav","NO");
		$list->setBool("ShowPic","YES");
		
		$articles_home = Title::makeTitle(NS_SPECIAL,"ArticlesHome");
		$write_link = Title::makeTitle(NS_MAIN,"Create Article");
		$todays_articles = Title::makeTitle(NS_CATEGORY,date("F j, Y"));
		
		$output .= "<div class=\"mp-article-container\">
			
			<div class=\"mp-large-title-container\">
				<div class=\"mp-large-title\">
					".wfMsg("mp_popular_article_title")."
				</div>
				<div class=\"mp-title-links\">
					<a href=\"".$write_link->escapeFullURL()."\">Write an Article</a> / <a href=\"".$articles_home->escapeFullURL()."\">All</a> / <a href=\"http://feeds.feedburner.com/Armchairgm\">RSS</a>
				</div>
				<div class=\"cleared\"></div>
			</div>";
			
			$output .= $list->DisplayList();
			
		$output .= "</div>";
		
		$output .= "<div class=\"mp-top-story-divider\"></div>";
				
		//New Articles
		$list = new ListPages();
		$list->setCategory("News, Opinions,Questions, ArmchairGM Announcements");
		$list->setShowCount(6);
		$list->setOrder("New");
		$list->setShowPublished("NO");
		$list->setShowBlurb("200");
		$list->setBlurbFontSize("small");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowDate","yes");
		$list->setBool("ShowStats","yes");
		$list->setBool("ShowNav","NO");
		$list->setBool("ShowPic","YES");
		
		$output .= "<div class=\"mp-article-container\">
			<div class=\"mp-large-title-container\">
				<div class=\"mp-large-title\">
					".wfMsg("mp_new_article_title")."
				</div>
				<div class=\"mp-title-links\">
					<a href=\"".$write_link->escapeFullURL()."\">Write an Article</a> / <a href=\"".$articles_home->escapeFullURL()."\">All</a> / <a href=\"http://feeds.feedburner.com/Armchairgm\">RSS</a>
				</div>
				<div class=\"cleared\"></div>
			</div>";
			$output .= $list->DisplayList();
		$output .= "</div>";
		
		return $output;
	}
	
	function getSiteActivity(){
		global $IP, $wgUploadPath, $wgMessageCache;
		require_once ( "$IP/extensions/wikia/UserHome/UserActivity.i18n.php" );
		foreach( efWikiaUserActivity() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		/*
		Get all relationship activity
		*/
		require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
		require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
		require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			
		$limit = 15;
		$rel = new UserActivity("","ALL",$limit);
		
		$rel->setActivityToggle("show_votes",0);
		$rel->setActivityToggle("show_network_updates",1);
		$activity = $rel->getActivityListGrouped();
	 
		if($activity) {
			
			$output .= "<div class=\"mp-friends-activity\">
				<h2>".wfMsg("mp_activity_title")."</h2>";
			
			$x = 1;
			foreach ($activity as $item) {
				if( $x < $limit ) {
					$output .= "<div class=\"mp-activity " . (($x+1==$limit)?"mp-activity-boarder-fix":"") . "\">
							
							<img src=\"{$wgUploadPath}/common/" . UserActivity::getTypeIcon($item["type"]) . "\" alt=\""  . UserActivity::getTypeIcon($item["type"]) . "\" border='0' />
							
							{$item["data"]}
						</div>";
					$x++;
				}
			}
			
			$output .= "</div>";
		}
		
		return $output;
	}
	
	function getFriendActivity(){

		global $wgUser, $IP, $wgUploadPath, $wgMessageCache;
		
		require_once ( "$IP/extensions/wikia/UserHome/UserActivity.i18n.php" );
		foreach( efWikiaUserActivity() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		 $u = new UserStats($wgUser->getID(),$wgUser->getName());
		$stats = $u->getUserStats();
		$points = str_replace(",","",$stats["points"]);
		$friends_count = str_replace(",","",$stats["friend_count"]);
		
		if($friends_count > 0) {
			
		$output .= "<div class=\"mp-friends-activity\">
			<h2>".wfMsg("mp_friends_activity_title")."</h2>";
		
		  //$output .= "<div class=\"user-home-feed\">";
		    require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
			require_once("$IP/extensions/wikia/UserGifts/GiftsClass.php");
			require_once("$IP/extensions/wikia/SystemGifts/SystemGiftsClass.php");
			
			$limit = 12;
			
			$rel = new UserActivity($wgUser->getName(),"friends",$limit);
			
			$rel->setActivityToggle("show_votes",0);
			$rel->setActivityToggle("show_network_updates",1);
			/*
			Get all relationship activity
			*/
			$activity = $rel->getActivityListGrouped();
	 
			if($activity){
				$x = 1;
				foreach ($activity as $item) {
					if( $x < $limit ){
						$output .= "<div class=\"mp-activity\">
								
								<img src=\"{$wgUploadPath}/common/" . UserActivity::getTypeIcon($item["type"]) . "\" alt=\""  . UserActivity::getTypeIcon($item["type"]) . "\" border='0' />
								
								{$item["data"]}
							</div>";
						$x++;
					}
					
						
				}
			}
			/*
			$activity = $rel->getActivityList();
			if ($activity) {
				$x = 1;
				
				foreach ($activity as $item) {
					
					if ($x<$limit) {
						
						$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
						$user_title = Title::makeTitle( NS_USER  , $item["username"]  );
						$user_title_2 = Title::makeTitle( NS_USER  , $item["comment"]  );
						
						if($user_title_2) {
							$user_link_2 = "<a href=\"{$user_title_2->escapeFullURL()}\" rel=\"nofollow\">{$item["comment"]}</a>";
						}
					
					$avatar = new wAvatar($item["userid"],"s");
					$CommentIcon = $avatar->getAvatarImage();
					
					if($item["type"] == "comment"){
						$comment_url = "#comment-{$item["id"]}";
					}
					$page_link = "<a href=\"" . $title->getFullURL() . "{$comment_url}\">" . $title->getPrefixedText() . "</a> ";
					
					$item_time = "<span class=\"mp-activity-timestamp\">".get_time_ago($item["timestamp"])." ago</span>";
					
					if ($x==$limit) {
						
						$output .= "<div class=\"mp-activity-last\">";
					
					} else {
						
						$output .= "<div class=\"mp-activity\">";
					
					}
					
					$output .= "<img src=\"{$wgUploadPath}/common/".UserActivity::getTypeIcon($item["type"])."\" alt=\"\" border=\"0\"/>
					<a href=\"{$user_title->escapeFullURL()}\" rel=\"nofollow\">{$item["username"]}</a>
					<span>";
					switch ($item["type"]) {
						case "edit":
							$output .= wfMsg("mp_edited", $page_link)." {$item_time}</span>
								<div class=\"item\">
									{$item["comment"]}
								</div>";
							break;
						case "vote":
							$output .= wfMsg("mp_voted", $page_link)." {$item_time}</span>";
							break;
						case "comment":
							$output .= wfMsg("mp_commented", $page_link)." {$item_time}</span>
								<div class=\"item\">
								\"{$item["comment"]}\"
							</div>";
							break;
						case "gift-sent":
							$gift_image = "<img src=\"{$wgUploadPath}/awards/".Gifts::getGiftImage($item["namespace"],"m")."\" border=\"0\" alt=\"\"/>";
							$view_gift_link = Title::makeTitle(NS_SPECIAL, "ViewGift");
							
							$output .= wfMsg("mp_sent_gift", $user_link_2)." {$item_time}</span>
							<div class=\"item\">
								<a href=\"".$view_gift_link->escapeFullURL('gift_id='.$item["id"])."\" rel=\"nofollow\">
									{$gift_image}
									{$item["pagetitle"]}
								</a>
							</div>";
							
							break;
							
						case "gift-rec":
							$gift_image = "<img src=\"{$wgUploadPath}/awards/".Gifts::getGiftImage($item["namespace"],"m")."\" border=\"0\" alt=\"\"/>";
							$view_gift_link = Title::makeTitle(NS_SPECIAL, "ViewGift");
							
							$output .= wfMsg("mp_received_gift", $user_link_2)." {$item_time}</span>
							<div class=\"item\">
								<a href=\"".$view_gift_link->escapeFullURL('gift_id='.$item["id"])."\" rel=\"nofollow\">
									{$gift_image}
									{$item["pagetitle"]}
								</a>
							</div>";
						
							break;
							
						case "system_gift":
						
							$system_gift_image = "<img src=\"{$wgUploadPath}/awards/" . SystemGifts::getGiftImage($item["namespace"],"m") . "\" border=\"0\" alt=\"\" />";
							$system_gift_link = Title::makeTitle(NS_SPECIAL, "ViewSystemGift");
							
							$output .= wfMsg("mp_received_award", $user_link_2)." {$item_time}</span>
							<div class=\"item\">	
								<a href=\"".$system_gift_link->escapeFullURL('gift_id='.$item["id"])."\" rel=\"nofollow\">
									{$system_gift_image}
									{$item["pagetitle"]}
								</a>
							</div>";
						
							break;
							
						case "friend":
							$output .= wfMsg("mp_friend", $user_link_2)." {$item_time}</span>";
							
							break;
							
						case "foe":
							$output .= wfMsg("mp_foe", $user_link_2)." {$item_time}</span>";
							
							break;
							
						case "system_message":
							$output .= "{$item["comment"]} {$item_time}</span>";
							break;
						
						case "user_message":
							
							
							
							$output .=  wfMsg("mp_user_message", $item["comment"], UserBoard::getUserBoardURL($user_title_2->getText())) . " {$item_time}</span>
									<div class=\"item\">
									\"{$item["namespace"]}\"
									</div>";
							break;
						case "network_update":
							$page_link = "<a href=\"" . SportsTeams::getNetworkURL($item["sport_id"],$item["team_id"]) . "\" rel=\"nofollow\">" . $item["network"] . "</a> ";							$network_image = SportsTeams::getLogo($item["sport_id"],$item["team_id"],"s");
							
							$output .= wfMsg("mp_network_thought", $page_link)." {$item_time}</span>
							<div class=\"item\">
								<a href=\"" . SportsTeams::getNetworkURL($item["sport_id"],$item["team_id"]) . "\" rel=\"nofollow\">
								{$network_image} 
								\"{$item["comment"]}\"
								</a>
							</div>";
							break;
					}
					
					$comment = $item["comment"];
					
					if($item["type"] == "comment"){
						
						$comment = "<a href=\"" . $title->escapeFullURL() . "#comment-" . $item["id"]  . "\" title=\"" . $title->getText() . "\" >" . $item["comment"] . "</a>";
					}
					
					$output .= "</div>";
					
					$x++;
					}
					
						
				}
			}
			*/
			
			$output .= "<div class=\"cleared\"></div>";	
	    $output .= '</div>';
		
		} else {
			$output .= $this->getSiteActivity();
		}
		
		return $output;
	}

	function getRelationshipRequestLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		$friend_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),1);
		$foe_request_count = UserRelationship::getOpenRequestCount($wgUser->getID(),2);
		$relationship_request_link = Title::makeTitle(NS_SPECIAL, "ViewRelationshipRequests");
		
		$rel_title = Title::makeTitle(NS_SPECIAL,"ViewRelationshipRequests");
		$output = "";
		if ($friend_request_count) {
			
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/addedFriendIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$relationship_request_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_friend", "parsemag", $friend_request_count)."</a>
			</p>";
			
		 }
		if ($foe_request_count) {
			  $output .= "<p>
				<img src=\"{$wgUploadPath}/common/addedFoeIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$relationship_request_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_foe", "parsemag", $foe_request_count)."</a>
			</p>";
		 }
		 return $output;
	}

	function getNewGiftLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/UserGifts/UserGiftsClass.php");
		$gift_count = UserGifts::getNewGiftCount($wgUser->getID());
		$gifts_title = Title::makeTitle(NS_SPECIAL,"ViewGifts");
		$output = "";
		if ($gift_count) {
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/icon_package_get.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$gifts_title->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_gift", "parsemag", $gift_count)."</a>
			</p>";
		 }
		 return $output;		
	}

	function getNewSystemGiftLink(){
		global $wgUser, $IP, $wgUploadPath;
		require_once("$IP/extensions/wikia/SystemGifts/UserSystemGiftsClass.php");
		$gift_count = UserSystemGifts::getNewSystemGiftCount($wgUser->getID());
		$gifts_title = Title::makeTitle(NS_SPECIAL,"ViewSystemGifts");
		$output = "";
		
		if ($gift_count) {
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/awardIcon.gif\" alt=\"\" border=\"0\"/> 
				<a href=\"".$gifts_title->escapeFullURL()."\" rel=\"nofollow\">".wfMsgExt("mp_request_new_award", "parsemag", $gift_count)."</a>
			</p>";
		 }
		
		 return $output;		
	}
	
	function getNewMessagesLink(){
		global $wgUser, $wgUploadPath;
		$new_messages = UserBoard::getNewMessageCount($wgUser->getID());
		if ( $new_messages ) {
			$board_link = Title::makeTitle(NS_SPECIAL,"UserBoard");
			$output .= "<p>
				<img src=\"{$wgUploadPath}/common/emailIcon.gif\" alt=\"email icon\" border=\"\"/> 
				<a href=\"".$board_link->escapeFullURL()."\" rel=\"nofollow\">".wfMsg("mp_request_new_message")."</a>
			</p>";
		}
		return $output;
	}
	
	function getContent() {
		/*
		if( $this->img && $this->img->fromSharedDirectory && 0 == $this->getID() ) {
			return '';
		}
		*/
		return "";
	}
	
	function getFeaturedEditors($days) {
		global $IP;
		include_once($IP . "/extensions/wikia/FeaturedEditors/FeaturedEditorsClass.php");
		
		$authors = new FeaturedEditors($days);
		$output .= $authors->displayFeaturedEditors();
		return $output;
	}
	

	function getCommentsOfTheDay(){
		global $wgUploadPath, $wgMemc;
		
		
		$comments = array();
		
		//try cache first
		$key = wfMemcKey( 'comments', 'plus', '24hours' );
		$wgMemc->delete( $key );
		$data = $wgMemc->get( $key );
		if( $data != ""){
			wfDebug("Got comments of the day from cache\n");
			$comments = $data;
		}else{
			wfDebug("Got comments of the day from db\n");
			$sql = "SELECT Comment_Username,comment_ip, comment_text,comment_date,Comment_user_id,
				CommentID,IFNULL(Comment_Plus_Count - Comment_Minus_Count,0) as Comment_Score,
				Comment_Plus_Count as CommentVotePlus, 
				Comment_Minus_Count as CommentVoteMinus,
				Comment_Parent_ID, page_title, page_namespace
				FROM Comments c, page p where c.comment_page_id=page_id 
				AND UNIX_TIMESTAMP(comment_date) > " . ( time() - (60 * 60 * 24 ) ) . "
				AND page_namespace = " . NS_BLOG . "
				ORDER BY (Comment_Plus_Count) DESC LIMIT 0,10";
			
			$dbr =& wfGetDB( DB_SLAVE );
			$res = $dbr->query($sql);
			while ($row = $dbr->fetchObject( $res ) ) {
				$comments[] = array(  "user_name" => $row->Comment_Username,
							"user_id" => $row->Comment_user_id,
							"title" => $row->page_title,
							"namespace" => $row->page_namespace,
							"comment_id" => $row->CommentID,
							"plus_count" => $row->CommentVotePlus,
							"comment_text" => $row->comment_text
							);			  
			  
			}
			$wgMemc->set( $key, $comments, 60 * 15);
		}
		
		foreach( $comments as $comment ){
			$page_title = Title::makeTitle( $comment["namespace"] , $comment["title"]);
		
			if( $comment["user_id"] != 0 ){
				$title = Title::makeTitle( NS_USER , $comment["user_name"] );
				$CommentPoster_Display = $comment["user_name"];
				$CommentPoster = '<a href="' . $title->escapeFullURL() . '" title="' . $title->getText() . '" rel=\"nofollow\">' . $title->getText() . '</a>';
				$avatar = new wAvatar( $comment["user_id"] , "s" );
				$CommentIcon = $avatar->getAvatarImage();
			}else{
				$CommentPoster_Display = "Anonymous Fanatic";
				$CommentPoster = "Anonymous Fanatic";
				$CommentIcon = "af_s.gif";
			}
			$comment["comment_text"] = strip_tags($comment["comment_text"]);
			$comment_text = substr(  $comment["comment_text"] ,0,70 - strlen($CommentPoster_Display) );
			if($comment_text !=  $comment["comment_text"]){
				$comment_text .= "...";
			}
			$output .= "<div class=\"cod-item\">";
			$output .=  "<span class=\"cod-score\">{$comment["plus_count"]}</span> ";
			$output .= " <span class=\"cod-comment\"><a href=\"{$page_title->escapeFullURL()}#comment-{$comment["comment_id"]}\" title=\"{$page_title->getText()}\" >{$comment_text}</a></span>";
			$output .= "</div>";
		}
		
		if (count($comments)>0) {
			
			$output = "<div class=\"mp-top-comments\">
				<h2>Top Recent Comments</h2>"
				.$output.
			"</div>";
			
		}
		
		
		
		return $output;
	}

	function displayTextAd() {
		
		$output .= '<div class="mp-middle-ad">
			<script type="text/javascript"><!--
			google_ad_client = "pub-4086838842346968";
			google_ad_width = 300;
			google_ad_height = 250;
			google_ad_format = "300x250_as";
			google_ad_type = "text_image";
			google_color_border = "FFFFFF";
			google_color_bg = "FFFFFF";
			google_color_link = "0000FF";
			google_color_text = "000000";
			google_color_url = "002BB8";

			//google_ad_channel = "90000000xx";
			//google_hints= "";
			//google_page_url = "";

			//-->
			</script>
			<script type="text/javascript"
			  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>';
		
		return $output;
	}

	
}


?>