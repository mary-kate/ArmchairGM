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

class SkinPolitics extends Skin {
  
//require_once("$IP/extensions/MainPage_Entertainment.php");
	
  #set stylesheet
  function getStylesheet() {
    return "common/politics.css";
  }
  
  #set skinname
  function getSkinName() {
    return "Politics";
  }

    #searchform
  function searchForm( $label = "" )
  {
  global $wgRequest;
  
  $s .= '<FORM method=GET action=http://www.google.com/custom>';
  $s .= '<input type="hidden" name="ie" value="UTF-8">';
  $s .= '<input type="hidden" name="oe" value="UTF-8">';
  $s .= '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>';
  $s .= '<INPUT TYPE="text" name="q" style="width:150px;" maxlength=255 value=' . htmlspecialchars(substr($search,0,256)). '>';
  $s .= '</td><td>';
  $s .= '<INPUT type="submit" name="sa" VALUE="search"/></td></tr></table>';
  $s .= '<INPUT type="hidden" name="cof" VALUE="S:http://www.armchairgm.com;VLC:#26579A;AH:center;BGC:#ffffff;LH:51;LC:#26579A;L:http://www.armchairgm.com/mwiki/brand/logo.gif;ALC:#26579A;LW:302;T:#000000;AWFID:5f943568c946c3bb;">';
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
  $s .= '<INPUT type="hidden" name="cof" VALUE="S:http://www.armchairgm.com;VLC:#26579A;AH:center;BGC:#ffffff;LH:51;LC:#26579A;L:http://www.armchairgm.com/mwiki/brand/logo.gif;ALC:#26579A;LW:302;T:#000000;AWFID:5f943568c946c3bb;"/>';
  $s .= '<input type="hidden" name="domains" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="sitesearch" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="ie" value="UTF-8">';
  $s .= '<input type="hidden" name="oe" value="UTF-8">';
  $s .= '<INPUT TYPE="text" name="q" style="width:150px;" maxlength="255" value=' . htmlspecialchars(substr($search,0,256)). '></span>';
  
  $s .= "<span id=searchbox><input type='text' dclass='topsearch' style='width:150px;' name=\"search\" size='12' value=\""
  . htmlspecialchars(substr($search,0,256)) . "\" /></span>";
  
  $s .= "</td>";
  $s .= "<td><input type='image' src='../images/journal/search.gif' value=\"" . htmlspecialchars( wfMsg( "go" ) ) . "\" /></td></tr></table></form>";
  
  return $s;
  
  }

    
  #main page before wiki content
  function doBeforeContent() {
	
  ##global variables
  global $wgOut, $wgTitle, $wgUser, $wgLang, $wgContLang, $wgEnableUploads, $wgRequest, $wgSiteView;	
  
  ##login/logout
  $li = $wgContLang->specialPage("Userlogin");
  $lo = $wgContLang->specialPage("Userlogout");
  $tns=$wgTitle->getNamespace();
  
  #redirect
  $redirect = $this->pageSubtitle();
  
  ##declare s
  $s = "";
  
  $s .= '<table width="975" cellpadding="0" cellspacing="0" border="0" align="center">';
  $s .= '<tr>';
  
  # main window
  $s .= '<td width="975" valign="top">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Recentchanges"){
  	$s .= "On";
  }
  $s .= '">';
  $s .= '<a href=index.php?title=Special:Recentchanges>recent edits</a>';
  $s .= '</td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Special:SiteScout"){
  	$s .= "On";
  }
  $s .= '"><a href="index.php?title=Special:SiteScout">site scout</a></td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  
  ###user login
  $s .= '<td class="topright">';
  
  $s .= 'welcome ';
  $avatar = new wAvatar($wgUser->mId,"s");
  $s .= "<img src=images/avatars/" . $avatar->getAvatarImage() . " align=absmiddle style='border:1px solid #cccccc'>";
  $s .= ' <b>' . $wgUser->getName() . '</b>';
  if ( $wgUser->isLoggedIn() ) {
    if ( $wgUser->getNewtalk() ) {
	  $s .= " | <font color='red'><b>" . $this->makeKnownLinkObj($wgUser->getTalkPage(), "new message") . "</b></font>";
	  }
  }
  if ( $wgUser->isLoggedIn() ) {
    $s .='&nbsp;| <a href="index.php?title=Community_Portal">community portal</a> | <a href="index.php?title=Help">help</a> | ';
    $s .=  $this->makeKnownLink( $lo, wfMsg( "logout" ), $q );
  } else {
	$s .= ' | <a href="index.php?title=Community_Portal">community portal</a> | ';
	$s .=  '<a href="javascript:Login()">log-in</a>';
  }
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  $s .= '<td class="middleleft">';
  $s .= '<a href="index.php?title=Main_Page"><img src="images/politics/logo.png" alt="politics.wikia" border="0"/></a>';
  $s .= '</td>';
  $s .= '<td class="middleright">';
  $s .= $this->searchForm();
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975" style="margin-bottom:5px;">';
  $s .= '<tr>';
  $s .= '<td></td>';
  $s .= '<td width="190"><img src="images/politics/righttop.gif" alt="main logo" border="0"/></td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td class="leftmiddle">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
  $s .= '<tr>';
  $s .= '<td><a href="index.php?title=Main_Page">home</a></td>';
  $s .= '<td><a href="index.php?title=Democratic">democratic</a></td>';
  $s .= '<td><a href="index.php?title=Republican">republican</a></td>';
  $s .= '<td><a href="index.php?title=Libertarian">libertarian</a></td>';
  $s .= '<td><a href="index.php?title=Socialist">socialist</a></td>';
  $s .= '<td><a href="index.php?title=Green">green</a></td>';
  $s .= '<td><a href="index.php?title=Other">other</a></td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '<td class="rightmiddle" width="190" height="35">';
  if ( $wgUser->isLoggedIn() ) {
  $s .= $this->makeKnownLinkObj( $wgUser->getUserPage(), "my page");
  } else {
  $s .= '<a href=javascript:Register()>join us!</a>';
  }
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td class="leftbottom"><img src="images/leftbottomfade.gif" alt="main logo" border="0"/></td>';
  $s .= '<td class="rightbottom"><img src="images/politics/rightbottom.gif" alt="main logo" border="0"/></td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975" style="height:110px">';
  $s .= '<tr>';
  $s .= '<td align="center" style="padding-bottom:20px;">';
  $s .= '<div id="ad">';
  $s .= '<script language="javascript">';
  $s .= 'var AdLoaded = false;';
  $s .= 'var bsid = \'20840\';';
  $s .= 'var bsads = \'5\';';
  $s .= 'var bsloc = \'politics_728x90\';';
  $s .= 'var bswx = 728;';
  $s .= 'var bshx = 90;';
  $s .= 'var bsw = \'_blank\';';
  $s .= 'var bsb = \'FFFFFF\';';
  $s .= 'var bsf = \'FF0000\';';
  $s .= 'var bsalt = \'off\';';
  $s .= 'bspop = 1';;
  $s .= '</script>';
  $s .= '<script language="javascript" src="http://www5.bannerspace.com/js/getad3.js">';
  $s .= '</script>';
  $s .= '<noscript>';
  $s .= '<IFRAME name="bsadframe" ID="bsadframe" WIDTH=728 HEIGHT=90 SCROLLING="no" FRAMEBORDER="0" FRAMESPACING="0" MARGINHEIGHT="0" 
MARGINWIDTH="0" BORDER="0" HSPACE="10" VSPACE="10" ALIGN="right" SRC="http://www6.bannerspace.com/asp/getad_fc.asp?MID=20840&alt=off&f=FF0000&b=FFFFFF&w=_blank&loc=politics_728x90&s=728x90&pop=1">';
  $s .= '<a href="http://www6.bannerspace.com/asp/gosite.asp?MID=20840&loc=politics_728x90&s=728x90" target=_blank><img src="http://www8.bannerspace.com/asp/getban.asp?MID=20840&s=728x90&loc=politics_728x90" width=728 height=90 align=right border=0></a>';
  $s .= '</IFRAME>';
  $s .= '</noscript>';
  $s .= '</div>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  if ($wgOut->getPageTitle() !== 'Main Page') {  
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975" style="border-top:1px solid #dcdcdc;">';
  ## right ads
  $s .= '<tr>';
  $s .= '<td valign="top">';
  $s .= '<table border="0" cellpadding="2" cellspacing="2" width="140" style="border:1px solid #dcdcdc;margin-top:13px;">';
  $s .= '<tr>';
  $s .= '<td style="background-color:#dcdcdc;color:#666666;font-size:10px;" align="center">';
  $s .= 'spotlight';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td style="font-size:11px;">';
  $s .= '<a href="http://entertainment.wikia.com"><img src="images/spotlight/entertainmentsmall.gif" alt="edit" border="0"/></a>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td style="font-size:11px;">';
  $s .= '<a href="http://local.wikia.com"><img src="images/spotlight/localsmall.gif" alt="edit" border="0"/></a>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="2" cellspacing="2" width="140" style="border:1px solid #dcdcdc;margin-top:10px;">';
  $s .= '<tr>';
  $s .= '<td style="background-color:#dcdcdc;color:#666666;font-size:10px;" align="center">';
  $s .= 'sponsors';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td align="center">';
  $s .= '<script language="javascript">';
  $s .= 'var AdLoaded = false;';
  $s .= 'var bsid = \'20840\';';
  $s .= 'var bsads = \'6\';';
  $s .= 'var bsloc = \'politics_120x600\';';
  $s .= 'var bswx = 120;';
  $s .= 'var bshx = 600;';
  $s .= 'var bsw = \'_blank\';';
  $s .= 'var bsb = \'FFFFFF\';';
  $s .= 'var bsf = \'FF0000\';';
  $s .= 'var bsalt = \'off\';';
  $s .= 'bspop = 1;';
  $s .= '</script>';
  $s .= '<script language="javascript" src="http://www6.bannerspace.com/js/getad3.js">';
  $s .= '</script>';
  $s .= '<noscript>';
  $s .= '<IFRAME name="bsadframe" ID="bsadframe" WIDTH=120 HEIGHT=600 SCROLLING="no" FRAMEBORDER="0" FRAMESPACING="0" MARGINHEIGHT="0" 
MARGINWIDTH="0" BORDER="0" HSPACE="10" VSPACE="10" ALIGN="right" SRC="http://www6.bannerspace.com/asp/getad_fc.asp?MID=20840&alt=off&f=FF0000&b=FFFFFF&w=_blank&loc=politics_120x600&s=120x600&pop=1">';
  $s .= '<a href="http://www6.bannerspace.com/asp/gosite.asp?MID=20840&loc=politics_120x600&s=120x600" target=_blank><img src="http://www6.bannerspace.com/asp/getban.asp?MID=20840&s=120x600&loc=politics_120x600" width=120 height=600 align=right border=0></a>';
  $s .= '</IFRAME>';
  $s .= '</noscript>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  } else {
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  }
  
  if ($wgOut->getPageTitle() !== 'Main Page') {  
  $s .= '<td valign="top">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" style="margin-left:5px;">';
  $s .= '<tr>';
  $s .= '<td class="main" >';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="815">';
  $s .= '<tr>';
  $s .= '<td style="padding-top:5px; padding-bottom:2px;">';
  if ( $wgOut->isArticle() ) {
  $s .= '<table border="0" cellpadding="2" cellspacing="3">';
  $s .= '<tr>';
  if( $wgUser->isLoggedIn() ) {
  $s .= '<td width="22"><img src="images/politics/edit.gif" alt="edit" border="0"/></td>';
  $s .= '<td>' . $this->editThisPage() . '</td>';
  if ( $wgTitle->userCanMove() ) {
  $s .= '<td width="22"><img src="images/politics/move.gif" alt="move" border="0"/></td>';
  $s .= '<td>' . $this->moveThisPage() . '</td>';
  }
  $s .= '<td width="22"><img src="images/politics/history.gif" alt="history" border="0"/></td>';
  $s .= '<td>' . $this->historyLink()  . '</td>';
  $s .= '<td width="22"><img src="images/politics/whatlinkshere.gif" alt="what links here" border="0"/></td>';
  $s .= '<td>' . $this->whatLinksHere()  . '</td>';
  if( $tns == NS_USER || $tns == NS_USER_TALK ) {
  $link = $wgTitle->getTalkPage();
  $s .= '<td width="22"><img src="images/politics/discuss.gif" alt="discuss" border="0"/></td>';
  $s .= '<td>' . $this->makeLinkObj( $link, 'talk page')  . '</td>';
  $id=User::idFromName($wgTitle->getText());
  if ($id != 0) {
  $s .= '<td width="22"><img src="images/politics/usercontribution.gif" alt="user contributions" border="0"/></td>';
  $s .= '<td>' . $this->userContribsLink()  . '</td>';
  if( $this->showEmailUser( $id ) ) {
  $s .= '<td width="22"><img src="images/politics/email.gif" alt="email user" border="0"/></td>';
  $s .= '<td>' . $this->emailUserLink()  . '</td>';
  }
  }
  }
  if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace()) {
  $s .= '<td width="22"><img src="images/politics/delete.gif" alt="delete" border="0"/></td>';
  $s .= '<td>' . $this->deleteThisPage() . '</td>';
  }
  if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() ) {
  $s .= '<td width="22"><img src="images/politics/protect.gif" alt="protect" border="0"/></td>';
  $s .= '<td>' . $this->protectThisPage() . '</td>';
  }
  } else {
  if ( $wgTitle->userCanEdit() ) {
  $s .= '<td class="backgroundtip" width="250" height="40">did you know you can edit this page?</td>';
  $s .= '<td width="22"><img src="images/politics/arrow.gif" alt="help" border="0"/></td>';
  }
  $s .= '<td width="22"><img src="images/politics/edit.gif" alt="edit" border="0"/></td>';
  $s .= '<td>' . $this->editThisPage() . '</td>';
  $s .= '<td width="22"><img src="images/politics/history.gif" alt="history" border="0"/></td>';
  $s .= '<td>' . $this->historyLink()  . '</td>';
  $s .= '<td width="22"><img src="images/politics/help.gif" alt="help" border="0"/></td>';
  $s .= '<td><a href="index.php?title=Help">help</a></td>';
  }
  $s .= '</tr>';
  $s .= '</table>';
  }
  
  $userPage = str_replace("User:","",$wgOut->getPageTitle());
  
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="815">';
  $s .= '<tr>';
  $s .= '<td>';
  
  if($wgTitle->getNamespace() == NS_USER){
    $s .= '<tr>';
  	$s .= '<td>';
	$s .= '<table cellpadding="0" cellspacing="0" border="0">';
	$s .= '<tr>';
  	$s .= '<td>';
	$u = User::newFromName($wgTitle->getText());
	$avatar = new wAvatar($u->mId,"l");
	
	$s .= "<img src=images/avatars/" . $avatar->getAvatarImage() . " align=absmiddle hspace=3 vspace=5>";
	$s .= '</td><td class="pagetitle" style="padding-left:5px;">' . str_replace("User:","",$this->pageTitle()) ;
	if($userPage == $wgUser->mName){
		$s .= "</td></tr><tr><td colspan=2><a href=index.php?title=Profile_Image class='avatarline'>Add/Update Profile Image</a><br><br>";
	}
  $s .= '</td>';
	$s .= '</tr></table>';
  }
  
  else{
  $s .=  '<h1 class="pagetitle">' . $this->pageTitle() . '</h1>';
  }
  	
  $s .= '</td>';
  $s .= '</tr>';
  
    #redirect
  $redirect = $this->pageSubtitle();
  
  #if redirect then output the following
  if ($redirect) {
  $s .= '<tr>';
  $s .= '<td colspan="3" class="redirect">';
  $s .= $redirect;
  $s .= '</td>';
  $s .= '</tr>';
  }
  
  $s .= '</table>';
  } else {
    $s .= '<td valign="top" width="975">';
	$s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
	$s .= '<tr>';
	$s .= '<td class="mainpage">';
  }
  	
  return $s;
	
	}
 
 function doAfterContent()
  {
 
 global $wgOut, $wgUser;
 
 $cat = $this->getCategoryLinks();
  if( $cat ) $s .= "<br />$cat\n";
  
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  
  
		
  ##footer
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  $s .= '<td style="padding-top:18px;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  $s .= '<td class="footer" style="border-top:1px solid #dcdcdc;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="975">';
  $s .= '<tr>';
  $s .= '<td width="100" align="left"><a href="http://www.gnu.org/copyleft/fdl.html"><img src="../images/journal/gnu-fdl.png" alt="" border="0"></a></td>';
  $s .= '<td align="center">';
  $s .= '<a href="http://www.wikia.com/wiki/About_Wikia">About Wikia, Inc.</a> | ';
  $s .= '<a href="http://www.wikia.com/wiki/Terms_of_use">Terms of Use </a> | ';  
  $s .= '<a href="index.php?title=Special:Specialpages">Special Pages</a> | ';
  $s .= '<a href="http://digg.com/submit">Digg this page </a> | ';
  $s .= '<a href="http://del.icio.us/post">Del.icio.us</a>';
  $s .= '</td>';
  $s .= '<td width="100" align="right"><img src="../images/journal/poweredby_mediawiki_88x31.png" alt="" border="0"></td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  
  #end main table
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  
  return $s;
  
 }
 
 function getMainPage(){
	 global $wgAnonName;
require_once ('extensions/ListPagesClass.php');
$output = "";

$dates_array = get_dates_from_elapsed_days(2);
$date_categories = "";
foreach ($dates_array as $key => $value) {
	if($date_categories)$date_categories .=",";
	$date_categories .= str_replace(",","\,",$key);
}

# find information
#$output .= '<table width=425 cellpadding=0 cellspacing=0 border=0 align=center>';
#$output .= '<tr>';
#$output .= '<td class="bluehatch">';
#$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
#$output .= '<tr>';
#$output .= '<td colspan="2" class="title">';
#$output .= 'political info ';
#$output .= '<a href="javascript:toggle(\'otherfind\',\'toggle1\')"><span id="toggle1" style="font-size:8pt; font-weight:normal;">(expand)</span></a>';
#$output .= '</td>';
#$output .= '</tr>';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Campaigns">campaigns</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Voting_Guides">voting guides</a></td>';
#$output .= '</tr>';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Law_Proposals">law proposals</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Questions">questions</a></td>';
#$output .= '</tr>';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Politicians">politicians</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Political_Issues">issues</a></td>';
#$output .= '</tr>';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Political_Issues">political history</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Blogs">blog index</a></td>';
#$output .= '</tr>';
#$output .= '</table>';

#other information
#$output .= '<div id=otherfind style="display:none;">';
#$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0">';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Speeches">political speeches</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Media_Bias">media bias</a></td>';
#$output .= '</tr>';
#$output .= '<tr>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Opinions">opinions</a></td>';
#$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Political_Issues">political books</a></td>';
#$output .= '</tr>';
#$output .= '</table>';
#$output .= '</div>';

#end information
#$output .= '</td>';
#$output .= '</tr>';
#$output .= '</table>';

#share beginning
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="22"><img src="../images/politics/pencilmain.gif" alt="pencil main"></td>';
$output .= '<td style="font-size:18px" class="mainpagelinks"><a href="index.php?title=Create_Opinion">write an opinion</a></td>';
$output .= '<td width="22"><a href="javascript:toggle(\'share\',\'toggle2\')"><span id="toggle2" style="font-size:8pt; #font-weight:normal;">expand</span></a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td colspan="3" class="mainpagelinks"><a href="index.php?title=Propose_A_Law">propose a law</a></td>';
$output .= '</tr>';
$output .= '</table>';

#other share
$output .= '<div id="share" style="display:none;">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0">';
$output .= '<tr>';
$output .= '<td class="mainpagelinks"><a href="index.php?title=Create_Question">ask a question</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td class="mainpagelinks"><a href="index.php?title=Add_Blog">add your blog</a></td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</div>';

#end share
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

$list = new ListPages();
$list->setCategory("Opinions");
$list->setShowCount(5);
$list->setOrder("New");
$list->setShowPublished("No");
$list->setBool("ShowVoteBox","yes");
$list->setBool("ShowDate","NO");
$list->setBool("ShowStats","NO");
$list->setHash("Count=5;Order=New;Category=Opinions");
$list->setBool("useCache","YES");
$list->setLevel(1);
 
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td class="title" xstyle="color:#FFFFFF;background-image:url(\'../../images/politics/background.gif\');">';
$output .= 'new opinions';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td class=smallList>';
$output .= $list->DisplayList();
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
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
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td class="title">';
$output .= 'what people are talking about';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td class=smallList>';
$output .= $list->DisplayList();
$output .= '<td>';
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
		$CommentPoster_Display = "Anonymous Debater";
		$CommentPoster = "Anonymous Debater";
		$CommentIcon = "af_s.gif";
	}
	$comment_text = substr($row->comment_text,0,60 - strlen($CommentPoster_Display) );
	if($comment_text != $row->comment_text){
		$comment_text .= "...";
	}
	$comments .= "<div class=\"cod\">";
	$comments .=  "<span class=\"cod-score\">+" . $row->Comment_Score . '</span> <img src="images/avatars/' . $CommentIcon . '" alt="" align="middle" style="margin-bottom:8px;" border="0"/> <span class="cod-poster">' . $CommentPoster . "</span>";
	$comments .= "<span class=\"cod-comment\"><a href=\"" . $title2->getFullURL() . "#comment-" . $row->CommentID . "\" title=\"" . $title2->getText() . "\" >" . $comment_text . "</a></span>";
	$comments .= "</div>";
}

#comments of the day
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td class="title">';
$output .= 'comments of the day <span style="font-size:12px;color:#666666;font-weight:800;">(last 24 hours)</span>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td class=smallList>';
$output .= $comments;
$output .= '<td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

return $output;

 }
 
}
?>
