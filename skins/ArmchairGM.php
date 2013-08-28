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

function get_dates_from_elapsed_days($number_of_days){
	$dates[date("F j, Y", time() )] = 1; //gets today's date string
	for($x=1;$x<=$number_of_days;$x++){
		$time_ago = time() - (60 * 60 * 24 * $x);
		$date_string = date("F j, Y", $time_ago);
		$dates[$date_string] = 1;
	}
	return $dates;
}

/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinArmchairGM extends Skin {
  
  #set stylesheet
  function getStylesheet() {
    return "common/ArmchairGM.css";
  }

  #set skinname
  function getSkinName() {
	  global $wgUser;
	  $wgUser->setOption( 'skin',"ArmchairGM" );
    return "cologneblue";
  }

  function getMainPage(){
  global $wgOut, $wgTitle, $wgUser, $wgLang, $wgContLang, $wgEnableUploads;
global $wgOut, $wgTitle, $wgUser, $wgLang, $wgContLang, $wgEnableUploads;

$output = "";
/*
# find stuff 
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center">';
$output .= '<tr>';
$output .= '<td width="35"><img src="images/findMain.gif" alt="" border="0" /></td>';
$output .= '<td width="425" class="title" style="padding-bottom:5px;">find stuff ';
$output .= '<a href="javascript:toggle(\'otherfind\',\'toggle1\')"><span id="toggle1" style="font-size:8pt;">(expand)</span></a>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0">';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Encyclopedia">encyclopedia</a></td>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Ask_the_Chair">ask the chair</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Category:Dictionary">dictionary</a></td>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Category:Blogs">sports blog index</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Category:Travel_Guides">sports travel guides</a></td>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Special:ChallengeHistory">challenges <span style="color:red;">(new)</span></a></td>';
$output .= '</tr>';
$output .= '</table>';

#other find
$output .= '<div id="otherfind" style="display:none;">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0">';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Category:Opinions">sports archive</a></td>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Sports_Media">sports media</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Sports_Culture">sports culture</a></td>';
$output .= '<td width="30"><img src="images/folder.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Todays_Stuff">today\'s stuff</a></td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</div>';

#end find stuff
$output .= '</td></tr>';
$output .= '</table>';
*/

#create stuff
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:2px;">create stuff ';
$output .= '<a href="javascript:toggle(\'othercreate\',\'toggle2\')"><span id="toggle2" style="font-size:8pt;">(expand)</span></a>';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="createborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0">';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Opinion">opinion</a></td>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_News">news</a></td>';
$output .= '</tr>';
$output .= '</table>';

#other create
$output .= '<div id="othercreate" style="display:none;">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0">';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Page">encyclopedic entry</a></td>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Question">ask the chair</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Dictionary">dictionary entry</a></td>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Game_Recap">game recap</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Movie_Summary">movie summary</a></td>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Book_Summary">book summary</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="30"><img src="images/page.gif" alt="" border="0" /></td>';
$output .= '<td class="button"><a href="index.php?title=Create_Discussion">discussion</a></td>';
$output .= '<td width="30"></td>';
$output .= '<td class="button"></td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</div>';

# find stuff end
$output .= '</td></tr>';
$output .= '</table>';

$dates_array = get_dates_from_elapsed_days(2);
$date_categories = "";
foreach ($dates_array as $key => $value) {
	if($date_categories)$date_categories .=",";
	$date_categories .= str_replace(",","\,",$key);
}
#$date_categories = "December 7\, 2006";

$list = new ListPages();
$list->setHash("Count=5;Order=Votes;Category=" . $date_categories);
$list->setCategory($date_categories);
$list->setShowCount(5);
$list->setOrder("Votes");
$list->setShowPublished("Yes");
$list->setBool("ShowNav","No");
$list->setBool("ShowVoteBox","yes");
$list->setBool("ShowDate","NO");
$list->setBool("ShowStats","NO");
$list->setBool("useCache","YES");
$list->setLevel(1);

#top recent votes
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="../images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:10px;">most votes <span style="font-size:12px;color:#666666;font-weight:800">(last 3 days)</span> ';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" style="padding-bottom:4px;border-bottom:1px solid #dcdcdc;">';
$output .= '<tr>';
$output .= '<td class="smallList">';
$output .= $list->DisplayList();
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

$list = new ListPages();
$list->setCategory($date_categories);
$list->setShowCount(5);
$list->setOrder("Comments");
$list->setShowPublished("Yes");
$list->setBool("ShowNav","No");
$list->setBool("ShowCommentBox","yes");
$list->setBool("ShowDate","NO");
$list->setBool("ShowStats","NO");
$list->setHash("Count=5;Order=Comments;Category=" . $date_categories);
$list->setBool("useCache","YES");
$list->setLevel(1);

#top recent comments
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="../images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:10px;">what people are talking about';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" >';
$output .= '<tr>';
$output .= '<td class="smallList">';
$output .= $list->DisplayList();
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

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
#comments of the day 
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="../images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:10px;">comments of the day <span style="font-size:12px;color:#666666;font-weight:800;">(last 24 hours)</span>';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" >';
$output .= '<tr>';
$output .= '<td class="smallList">';
$output .= $comments;
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

$list = new ListPages();
$list->setCategory("Opinions,News,Projects,ArmchairGM Announcements,Game Recaps,Open Thread,Showdowns,Questions");
$list->setShowCount(6);
$list->setOrder("New");
$list->setShowPublished("No");
$list->setBool("ShowNav","No");
$list->setBool("ShowDate","NO");
$list->setBool("ShowStats","Yes");
$list->setHash("Count=5;Order=New;Category=" . $date_categories);
$list->setBool("useCache","YES");

#new pages

$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="../images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:10px;">just created';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" >';
$output .= '<tr>';
$output .= '<td class="smallList">';
$output .= $list->DisplayList();
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

$list = new ListPages();
$list->setCategory("Lockerroom");
$list->setShowCount(5);
$list->setOrder("New");
$list->setShowPublished("No");
$list->setBool("ShowNav","No");
$list->setBool("ShowDate","NO");
$list->setBool("ShowStats","No");

#new pages

$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center" style="padding-top:10px;">';
$output .= '<tr>';
#$output .= '<td width="35"><img src="../images/createMain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="425" style="padding-bottom:10px;">latest open discussions';
$output .= '</td></tr>';
$output .= '<tr><td colspan="2" class="findborder">';
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" >';
$output .= '<tr>';
$output .= '<td class="smallList">';
$output .= $list->DisplayList();
$output .= '<td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
/*
#subscribe
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center">';
$output .= '<tr><td class="subscribe">';

#subscribe title
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center">';
$output .= '<tr>';
$output .= '<td width="28"><img src="images/arrowmain.gif" alt="" border="0" /></td>';
$output .= '<td class="title" width="432">subscribe</td>';
$output .= '</tr>';
$output .= '<tr><td colspan="2">';

#subscribe by e-mail
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center">';
$output .= '';
$output .= '<tr>';
$output .= '<td class="mainsubtitle" width="60">e-mail </td>';
$output .= '<td ><form name="feedburner" style="margin:0px" action="http://www.feedburner.com/fb/a/emailverify" method="post" target="popupwindow" onsubmit="window.open(\'http://www.feedburner.com\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');return true"><input type="text" style="width:150px; color:#666666; padding:3px;" value="your e-mail" name="email" onfocus="clearDefault(this);" /><input type="hidden" value="http://feeds.feedburner.com/~e?ffid=252114" name="url"/><input type="hidden" value="ArmchairGM" name="title"/><input type="image" src="images/go.gif" align="top" /></form></td>';
//$output .= '<td>';
$output .= '</tr></table>';

#rss
$output .= '<table width="380" cellpadding="0" cellspacing="0" border="0" align="center"><tr>';
$output .= '<td class="mainsubtitle" width="60">rss </td>';
$output .= '<td><a href="http://feeds.feedburner.com/Armchairgm" target="_new"><img src="images/rss.gif" alt="" border="0"/></a></td>';
$output .= '</tr></table></td></tr>';

#end subscribe
$output .= '</table>';
$output .= '</td></tr>';
$output .= '</table>'; 
*/

return $output;
  
  }
  #searchform
  function searchForm( $label = "" )
  {
  global $wgRequest;
  
  $s .= '<FORM method="GET" action="http://www.google.com/custom">';
  $s .= '<input type="hidden" name="ie" value="UTF-8" />';
  $s .= '<input type="hidden" name="oe" value="UTF-8" />';
  $s .= '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>';
  $s .= '<input type="text" name="q" style="width:150px;" maxlength="255" value=' . htmlspecialchars(substr($search,0,256)). ' />';
  $s .= '</td><td>';
  $s .= '<input type="submit" name="sa" value="search"/></td></tr></table>';
  $s .= '<input type="hidden" name="cof" VALUE="S:http://www.armchairgm.com;VLC:#26579A;AH:center;BGC:#ffffff;LH:51;LC:#26579A;L:http://www.armchairgm.com/mwiki/brand/logo.gif;ALC:#26579A;LW:302;T:#000000;AWFID:5f943568c946c3bb;"  />';
  $s .= '<input type="hidden" name="domains" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="sitesearch" value="armchairgm.com"/></FORM>';
  
  $search = $wgRequest->getText( 'search' );
  $action = $this->escapeSearchLink();
  $s = "<form name='search' id=\"search\" method=\"get\" class=\"inline\" action=\"$action\">";

  if ( "" != $label ) { $s .= "{$label}: "; }


  $s .= '<table border="0" cellpadding="3" cellspacing="3" align="right"><tr>';
 // $s .= "<td><span style='font-size:14pt; font-weight:bold; color:#285C98;'>search</span></td>";
  $s .= '<td>';
  $s .= '<span id="searchboxG" style="display: none">';
  $s .= '<input type="hidden" name="cof" value="S:http://www.armchairgm.com;VLC:#26579A;AH:center;BGC:#ffffff;LH:51;LC:#26579A;L:http://www.armchairgm.com/mwiki/brand/logo.gif;ALC:#26579A;LW:302;T:#000000;AWFID:5f943568c946c3bb;"/>';
  $s .= '<input type="hidden" name="domains" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="sitesearch" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="ie" value="UTF-8" />';
  $s .= '<input type="hidden" name="oe" value="UTF-8" />';
  $s .= '<input type="text" name="q" style="width:150px;" maxlength="255" value="' . htmlspecialchars(substr($search,0,256)). '" /></span>';
  
  $s .= "<span id=\"searchbox\"><input type='text'  style='width:150px;' name=\"search\" size='12' value=\""
  . htmlspecialchars(substr($search,0,256)) . "\" /></span>";
  
  $s .= "</td>";
  $s .= "<td>";
  $s .= "<select onchange='toggleSearch();document.search.action=this.value'>";
  $s .= "<option value='\"$action\"'>armchairgm.com</option>";
  $s .= "<option value='http://www.google.com/custom'>google</option>";
  $s .= "</select>";
  $s .= "</td>";
  $s .= "<td><input type='image' src='images/search.png' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" /></td></tr></table></form>";
  
  return $s;
  
  }
  
  ##edit/protect/source buttons
  function editButtonMainPage() {
		global $wgOut, $wgTitle, $wgRequest, $wgUser;

		$oldid = $wgRequest->getVal( 'oldid' );
		$diff = $wgRequest->getVal( 'diff' );
		$redirect = $wgRequest->getVal( 'redirect' );

		if ( ! $wgOut->isArticleRelated() ) {
			$s = '<img src="images/protected.gif" alt="" border="0"/>';
		} else {
			if ( $wgTitle->userCanEdit() ) {
				$t = '<img src="images/edit.gif" alt="" border="0"/>';
			} else {
				$t = '<img src="images/viewsource.gif" alt="" border="0"/>';
			}
			$oid = $red = '';

			if ( !is_null( $redirect ) ) { $red = "&redirect={$redirect}"; }
			if ( $oldid && ! isset( $diff ) ) {
				$oid = '&oldid='.$oldid;
			}
			$s = $this->makeKnownLinkObj( $wgTitle, $t, "action=edit{$oid}{$red}" );
		}
		
		$bookmarkToggle = 0;
		$bookmarkLink = "removeBookmark";
		if(!$wgTitle->userIsWatching() ){
			$bookmarkToggle = 1;
			$bookmarkLink = "addBookmark";
		}
		if ( $wgUser->isLoggedIn() ) {
		//$s .= "<span id=bookmarkLink><a href=javascript:toggleWatch(" . $bookmarkToggle . "," . $wgTitle->mArticleID . ")><img src=images/" . $bookmarkLink . ".png border=0 /></a></span>";
		}
		return $s;
	}
  
  #main page before wiki content
  function doBeforeContent() {
	
  ##global variables
  global $wgOut, $wgTitle, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest;	
  
  ##login/logout
  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");
  $tns=$wgTitle->getNamespace();
  
  #redirect
  global $wgMessageCache;
  $wgMessageCache->addMessage( 'tagline', '' );
  $redirect = $this->pageSubtitle();
  
  ##declare s
  $s = "";
	
  ##top header
  $s .= '<table width="1000" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td colspan="2">';
  $s .= '<table width="1000" cellpadding="0" cellspacing="0" border="0"><tr>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Recentchanges"){
  	$s .= "On";
  }
  $s .= '">';
  $s .= '<a href="index.php?title=Special:Recentchanges">recent edits</a>';
  $s .= '</td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Head Scout"){
  	$s .= "On";
  }
  $s .= '"><a href="index.php?title=Special:SiteScout">head scout <i style="color:#009900">beta</i></a></td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  $s .= '<td width="100" class="toptabs';
    if($wgTitle->getText() == "Top Stuff"){
  	$s .= "On";
  }
  $s .= '"><a href="index.php?title=Top_Stuff">top stuff</a></td>';
  
  ###user login
  $s .= '<td class="topright">';
  
  $s .= 'welcome ';
  $avatar = new wAvatar($wgUser->mId,"s");
  $s .= "<img src=\"images/avatars/" . $avatar->getAvatarImage() . "\" align=\"middle\" alt=\"avatar\" style=\"border:1px solid #cccccc;margin-bottom:8px;\"  />";
  $s .= ' <b>' . $wgUser->getName() . '</b>';
  if ( $wgUser->isLoggedIn() ) {
  $title1 = Title::makeTitle( NS_USER  , $wgUser->mName  );
    if ( $wgUser->getNewtalk() ) {
	  $s .= " " . $this->makeKnownLinkObj($wgUser->getTalkPage(), "<img src='images/talkMessage.gif' width=14 height=10 border='0' alt='new message on talk page'/>");
	  }
	  
	  	  	$dbr =& wfGetDB( DB_SLAVE );
	  	$challenge = $dbr->selectRow( '`challenge`', array( 'challenge_id'),
			array( 'challenge_user_id_2' => $wgUser->mId , 'challenge_status' => 0), "" );
			if ( $challenge > 0 ) {
				
				$s .= " | <a href=index.php?title=Special:ChallengeHistory&user=" . $title1->getDbKey() . "&status=0  style='color:#990000;font-weight:bold'>new challenge</a> ";
			}
  }
  if ( $wgUser->isLoggedIn() ) {
    $s .='&nbsp;| <a href="http://www.armchairgm.com/mwiki/index.php?title=Category:Lockerroom">locker room</a> | <a href="index.php?title=Help:Contents">help</a> | ';
    $s .=  $this->makeKnownLink( $lo, wfMsg( "logout" ), $q );
  } else {
	$s .= ' | <a href="http://www.armchairgm.com/mwiki/index.php?title=Category:Lockerroom">locker room</a> | ';
	$s .=  '<a href="javascript:Login()" >log-in</a>';
  }
  $s .= '</td>';
  $s .= '</tr></table>';
  $s .= '</td></tr><tr>';
  $s .= '<td class="middleleft"><a href="index.php?title=Main_Page"><img src="images/logo.png" alt="main logo" border="0"/></a></td>';
  $s .= '<td class="middleright">';
  $s .= $this->searchForm();
  $s .= '</td>';
  $s .= '</tr><tr><td colspan="2">';
  $s .= '<table cellpadding="0" cellspacing="0" width="1000" border="0">';
  #menuBarTop
  $s .= '<tr>';
  $s .= '<td width="848"></td>';
  if ( $wgUser->isLoggedIn() ) {
	  $s .= '<td><a href="index.php?title=Special:UserMenu"><img src="images/joinusTopUser.gif" alt="" border="0"/></a></td>';
  } else {
  $s .= '<td><img src="images/joinusTop.gif" alt="" border="0"/></td>';
  }
  $s .= '</tr>';
  
  #menuBarMiddle
  $s .= '<tr>';
  $s .= '<td width="848" class="greenbar">';

  #menuBarLinks

  $s .= '<table width="848" cellpadding="0" cellspacing="0" border="0"><tr>';
  $s .= '<td class="menubutton" width="75" align="center"><a href="index.php?title=Main_Page">HOME</a></td>';
  $dbr =& wfGetDB( DB_SLAVE );
  $sql = "SELECT user_menuitems FROM user_menu WHERE user_id = " . $wgUser->mId . " AND user_id<>0";
  $res = $dbr->query($sql);
  while ($row = $dbr->fetchObject( $res ) ) {
  	$MenuItems = $row->user_menuitems;
  }
  if(!$MenuItems)$MenuItems = "MLB|NFL|NBA|NHL|CFB|CBB|Soccer|Other";
  $MenuArray = explode ("|",$MenuItems);
  
  foreach($MenuArray as $item ){
  	$s .= '<td class="menubutton" width="75" align="center"><a href="index.php?title=' . $item . '">' . strtoupper($item) . '</a></td>';
  }
  
  /*
  $s .= '<td class="menubutton" width="75" align="center"><a href="#">HOME</a></td>';
  $s .= '<td class="menubutton" width="75" align="center"><a href="#">MLB</a></td>';
  $s .= '<td class="menubutton" width="75" align="center"><a href="#">NFL</a></td>';
  $s .= '<td class="menubutton" width="75" align="center"><a href="#">NBA</a></td>';
  $s .= '<td class="menubutton" width="67" align="center"><a href="#">NHL</a></td>';
  $s .= '<td class="menubutton" width="95" align="center"><a href="#">COLLEGE</a></td>';
  $s .= '<td class="menubutton" width="110" align="center"><a href="#">SOCCER</a></td>';
  $s .= '<td class="menubutton" align="left"><a href="#">OTHER</a></td>';  
   */
  $s .= '</tr></table>';
  
  $s .= '</td>';
  if ( $wgUser->isLoggedIn() ) {
  $s .= '<td>' . $this->makeKnownLinkObj( $wgUser->getUserPage(),
				"<img src='images/joinusMiddleUser.gif' alt='' border='0' />" ) . '</td>';
  } else {
  $s .= '<td><a href="javascript:Register()" ><img src="images/joinusMiddle.gif" alt="" border="0"/></a></td>';
  }
  $s .= '</tr>';
  
  #menuBarBottom
  $s .= '<tr>';
  $s .= '<td width="848" class="fade"></td>';
  $s .= '<td><img src="images/joinusBottom.gif" alt="" border="0"/></td>';
  $s .= '</tr>';
  $s .= '</table>';
 
  #main table
  $s .= '<table cellpadding="0" cellspacing="0" border="0" width="1000" align="center"><tr>';
  
  #side bar
  if ( $wgUser->isLoggedIn() && ($wgOut->getPageTitle() !== 'Main Page') && ($wgOut->getPageTitle() !== 'Main Page New')) {
  $s .= '<td width="150" valign="top" class="main">';
  
  #my pages table 
  $s .= '<table border="0" cellpadding="1" cellspacing="1" width="130">';
  $s .= '<tr><td class="sideTitle"Top colspan="2"><span class="sideTitleGreen">my</span>pages</td></tr>';
  $s .= '<tr><td width="22"><img src="images/messages.gif" alt="" border="0" /></td><td class=sideButton>' . $this->makeKnownLinkObj($wgUser->getTalkPage(), "messages") . '</td></tr>';
  $s .= '<tr><td width="22"><img src="images/challenge.gif" alt="" border="0" /></td><td class=sideButton><a href=index.php?title=Special:ChallengeHistory&user=' . $title1->getDbKey() . '>challenges</a></td></tr>';
  $s .= '<tr><td width="22"><img src="images/myfeed.gif" alt="" border="0" /></td><td class=sideButton><a href=http://www.armchairgm.com/mwiki/index.php?title=My_Feed>my feed</a></td></tr>';
  $s .= '<tr><td width="22"><img src="images/cupholder.gif" alt="" border="0" /></td><td class=sideButton><a href=http://www.armchairgm.com/mwiki/index.php?title=Cupholder>cupholder</a></td></tr>';
  $s .= '<tr><td width="22"><img src="images/watchlist.gif" alt="" border="0" /></td><td class=sideButton>' . $this->specialLink( "watchlist" ) . '</td></tr>';
  $s .= '<tr><td width="22"><img src="images/contribute.gif" alt="" border="0" /></td><td class=sideButton>' . $this->makeKnownLinkObj( Title::makeTitle( NS_SPECIAL, "Contributions" ),wfMsg( "mycontris" ), "target=" . wfUrlencode($wgUser->getName() ) ) . '</td></tr>';
  $s .= '</table>';
  
  #move pages table
  if ($wgOut->isArticleRelated()) {
  $s .= '<table border="0" cellpadding="1" cellspacing="1" width="130">';
  if ( $wgUser->isAllowed('delete') || $wgUser->isAllowed('protect') ) { 
  $s .= '<tr><td class="sideTitle" colspan="2"><span class="sideTitleGreen">admin</span>toolbox</td></tr>';
  } else {
  $s .= '<tr><td class="sideTitle" colspan="2"><span class="sideTitleGreen">tool</span>box</td></tr>';
  }
  $s .= '<tr><td width="22"><img src="images/create.gif" alt="" border="0" /></td><td class=sideButton><a href="lightbox/create.php?height=290" class="lbOn" >create</a></td></tr>';
  if ( $wgTitle->getNamespace() == NS_USER ) {
  $s .= '<tr><td width="22"><img src="images/talk.gif" alt="" border="0" /></td><td class=sideButton>' . $this->makeLinkObj ($wgTitle->getTalkPage(), "message user") . '</td></tr>';
    if($wgTitle->getText() != $wgUser->mName){ //users shouldn't be able to challenge themselves
  		$s .= '<tr><td width="22"><img src="images/challenge.gif" alt="" border="0" /></td><td class=sideButton><a href=index.php?title=Special:ChallengeUser&user=' . $wgTitle->getDbKey() . '>challenge user</a></td></tr>';
  	}
  } else {
  $s .= '<tr><td width="22"><img src="images/talk.gif" alt="" border="0" /></td><td class=sideButton>';
  $s .= $this->makeLinkObj( $wgTitle->getTalkPage(), "talk page");
  $s .= '</td></tr>';
  }
  $s .= '<tr><td width="22"><img src="images/pagehistory.gif" alt="" border="0" /></td><td class=sideButton><a href="#">' . $this->historyLink() . '</a></td></tr>';
  $s .= '<tr><td width="22"><img src="images/whatlinkshere.gif" alt="" border="0" /></td><td class=sideButton><a href="#">' . $this->whatLinksHere() . '</a></tr>';
  if ( $wgTitle->userCanMove() ) {
  $s .= '<tr><td width="22"><img src="images/move.gif" alt="" border="0" /></td><td class=sideButton>';
  $s .= $this->moveThisPage();
  $s .= '</td></tr>';
  }
  if ( $wgUser->isAllowed('delete') ) {
    $dtp = $this->deleteThisPage();
	if ( "" != $dtp ) {
	  $s .= '<tr><td width="22"><img src="images/delete.gif" alt="" border="0" /></td><td class=sideButton>' . $dtp . '</td></tr>';
	}
  }
  if ( $wgUser->isAllowed('protect') ) {
    $ptp = $this->protectThisPage();
	if ( "" != $ptp ) {
	  $s .= '<tr><td width="22"><img src="images/protect.gif" alt="" border="0" /></td><td class=sideButton>' . $ptp . '</td></tr>';
	}
  }
  $s .= '</table>';
  }
  
  #special pages table
  $s .= '<table border="0" cellpadding="1" cellspacing="1" width="130">';
  $s .= '<tr><td class="sideTitle" colspan="2"><span class="sideTitleGreen">special</span>pages</td></tr>';
  $s .= '<tr><td width="22"><img src="images/statistics.gif" alt="" border="0" /></td><td class=sideButton>' . $this->specialLink( "statistics" ) . '</td></tr>';
  $s .= '<tr><td width="22"><img src="images/upload.gif" alt="" border="0" /></td><td class=sideButton>' . $this->specialLink( "upload" ) . '</td></tr>';
   $s .= '<tr><td width="22"><img src="images/challenge.gif" alt="" border="0" /></td><td class=sideButton><a href=index.php?title=Special:ChallengeHistory>all challenges</a></td></tr>';
 
  $s .= '<tr><td width="22"><img src="images/specialpages.gif" alt="" border="0" /></td><td class=sideButton>' . $this->makeKnownLinkObj(Title::makeTitle( NS_SPECIAL, 'Specialpages' ),'special pages') . '</td></tr>'; 
  $s .= '</table>';
  
   #bookmarks table
  $s .= '<table border="0" cellpadding="1" cellspacing="1" width="130">';
  $s .= '<tr><td class="sideTitle"><span class="sideTitleGreen">my</span>bookmarks</td></tr>';
  $s .= '<tr><td><a href="javascript:toggleBookmarks()" style="text-decoration:none"><span id="togglebookmarks" style="font-size:8pt;">';
  
  if(!isset( $_COOKIE["bookmarks"]) || $_COOKIE["bookmarks"] == 0){
  	$s .= '<img src="images/folderClosed.png" border="0" /> open</span>';
  }else{
  	$s .= '<img src="images/folderOpen.png" border="0" /> close</span>';
  }
  $s .= '</a>';
  
  $s .= '</td></tr>';
  $s .= '</table>';
  
  #end sidebar
  $s .= '</td>';
  
  # beginning row for title/main for logged-in users 
  $s .= '<td class="mainright" valign="top">';
  
  } else {
  # beginning row for title/main for non-logged in users/main page
  $s .= '<td class="main" valign="top">';
  }
  
  #title/main table
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
  
  #title
  
  $title = $wgOut->getPageTitle();
  if($title){
  $nontitlepages = strpos(',Main Page,Main Page New,', $title); 
  }
  $numberofletters = strlen($title);
    
  if ($nontitlepages === false) {
  
  if($wgTitle->getNamespace() == NS_USER){
    $s .= '<tr>';
  	$s .= '<td>';
	$s .= '<table cellpadding="0" cellspacing="0" border="0">';
	$s .= '<tr>';
  	$s .= '<td>';
	$u = User::newFromName($wgTitle->getText());
	$avatar = new wAvatar($u->mId,"l");
	
	
	$s .= "<img src='images/avatars/" . $avatar->getAvatarImage() . "' align='absmiddle' hspace='3' vspace='5' />";
	$s .= '</td><td class="pagetitle" style="padding-left:5px;">' . str_replace("User:","",$this->pageTitle()) ;
	if($u->mName == $wgUser->mName){
		$s .= "<br><a href='index.php?title=Profile_Image' class='avatarline'>Add/Update Profile Image</a>";
	}
  $s .= '</td>';
	$s .= '<td style="padding-left:5px;" valign="top">' . $this->editButtonMainPage() . '</td>';
	$s .= '</tr>';
  }else{
  $s .= '<tr>';
  $s .= '<td>';
  $s .= '<table cellpadding="0" cellspacing="0" border="0">';
  $s .= '<tr>';
  $s .= '<td><img src="images/arrow.gif" alt="" border="0"/></td>';
  
  if ($numberofletters > 60) {
  $s .= '<td class="pagetitlesmallest">' . $title . '</td>';
  }
  else if ($numberofletters > 49) {
  $s .= '<td class="pagetitlesmaller">' . $title . '</td>';
  } 
  else {
  $s .= '<td class="pagetitle">' . $title . '</td>';
  }
    $s .= '<td style="padding-left:5px;" valign="top">' . $this->editButtonMainPage() . '</td>';
  $s .= '<td style="padding-left:5px;" valign="top"></td>';
  $s .= '</tr>';
  }
  
  #if redirect then output the following
  if ($redirect) {
  $s .= '<tr>';
  $s .= '<td colspan="3" class="redirect">';
  $s .= $redirect;
  $s .= '</td>';
  $s .= '</tr>';
  }
  
  # end title table
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  }
  
  # beginning main text
  $s .= '<tr>';
  $s .= '<td valign="top">';
  
  
	
  return $s;
	
	}
 
 function doAfterContent()
  {
 
 global $wgOut, $wgUser, $wgTitle;
 
  # end title/main page
  $s .= '</td>';
  $s .= '</tr>';
  
  # if categories then output
  if ($this->getCategoryLinks()) {
  $s .= '<tr>';
  $s .= '<td>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0">';
  $s .= '<tr>';
  $s .= '<td width="28"><img src="images/arrowmain.gif" alt="" border="0" /></td>';
  $s .= '<td class="title" width="842">categories</td>';
  $s .= '</tr>';
  //$s .= '</td></tr>';
  $s .= '<tr><td colspan="2">';
  $s .= $this->getCategoryLinks();
  $s .= '</td></tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  }
  
  # real end of title/main page
  $s .= '</table>';
  $s .= '</td>';
  
  #spacer for main table and footer
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td class="main"><img src="images/spacer.gif" alt="" border="0" height="8"/></td>';
  $s .= '</tr>';
		
  
  # end main table
  $s .= '</table></td></tr></table>';
 
 #footer
 
 $s .= '<table cellpadding="0" cellspacing="0"  width="1000" align="center">';

 if($wgTitle->getNamespace()!=NS_SPECIAL){
 $s .= '<tr><td colspan="2" class="footertop">' . $this->pageStats() . '</td></tr><tr>';
 }
 $s .= '<td class="footermiddle">';
 $s .= '<a href="index.php?title=Main_Page">Home</a> | ';
 $s .= '<a href="index.php?title=About">About</a> | ';
 $s .= '<a href="index.php?title=Special:Statistics">Stats</a> | ';
 $s .= '<a href="index.php?title=Terms_of_Service">Terms of Service</a> | ';
 $s .= '<a href="index.php?title=Privacy_Policy">Privacy Policy</a> | ';
 $s .= '<a href="mailto:support@armchairgm.com">Contact Us</a>';
 $s .= '&nbsp; &copy; 2006 ArmchairGM, LLC';
 $s .= '</td></tr><tr>';
 $s .= '<td align="center" class="footerbottom">';
 $s .= '<a href="http://www.gnu.org/copyleft/fdl.html"><img src="http://www.openserving.com/images/gnu-fdl.png" alt="gnu" /></a>';
 $s .= '</td></tr>';
 $s .= '</table>';
 
 return $s;
 
 }
 
	
}
?>
