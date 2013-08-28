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


class SkinLaw extends Skin {
  
//require_once("$IP/extensions/MainPage_Entertainment.php");
	
  #set stylesheet
  function getStylesheet() {
    return "common/law.css";
  }
  
  #set skinname
  function getSkinName() {
    return "Law";
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
  $s .= "<td>";
  $s .= "<select onChange=toggleSearch();document.search.action=this.value>";
  $s .= "<option value='\"$action\"'>law.wikia.com</option>";
  $s .= "<option value='http://www.google.com/custom'>google</option>";
  $s .= "</select>";
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
  
  ## left ads
  $s .= '<td valign="top" width="140" style="padding-right:15px; padding-top:10px;">';
  $s .= '<script type="text/javascript"><!--' . "\n";
  $s .= 'google_ad_client = "pub-2291439177915740";' . "\n";
  $s .= 'google_ad_width = 120;' . "\n";
  $s .= 'google_ad_height = 600;' . "\n";
  $s .= 'google_ad_format = "120x600_as";' . "\n";
  $s .= 'google_ad_type = "text";' . "\n";
  $s .= '//2006-12-04: wiki' . "\n";
  $s .= 'google_ad_channel = "9515194498";' . "\n";
  $s .= 'google_color_border = "ffffff";' . "\n";
  $s .= 'google_color_bg = "FFFFFF";' . "\n";
  $s .= 'google_color_link = "' . $wgSiteView->view_border_color_1 . '";' . "\n";
  $s .= 'google_color_text = "000000";' . "\n";
  $s .= 'google_color_url = "' . $wgSiteView->view_border_color_2 . '";' . "\n";
  $s .= '//--></script>' . "\n";
  $s .= '<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">' . "\n";
  $s .= '</script>' . "\n";
  $s .= '</td>';
  
  # main window
  $s .= '<td width="835" valign="top">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
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
  $s .= '<td width="100" class="toptabs';
    if($wgTitle->getText() == "Top Stuff"){
  	$s .= "On";
  }
  $s .= '"><a href="index.php?title=Top_Stuff">top stuff</a></td>';
  
  ###user login
  $s .= '<td class="topright" width="525">';
  
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
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td class="middleleft">';
  $s .= '<a href="index.php?title=Main_Page"><img src="images/law/lawlogo.png" alt="main logo" border="0"/></a>';
  $s .= '</td>';
  $s .= '<td class="middleright">';
  $s .= $this->searchForm();
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835" style="margin-bottom:5px;">';
  $s .= '<tr>';
  $s .= '<td></td>';
  $s .= '<td width="190"><img src="images/law/righttop.gif" alt="main logo" border="0"/></td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td class="leftmiddle">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
  $s .= '<tr>';
  $s .= '<td><a href="index.php?title=Main_Page">home</a></td>';
  $s .= '<td><a href="index.php?title=Constitutional_Law">con law</a></td>';
  $s .= '<td><a href="index.php?title=Criminal_Law">criminal</a></td>';
  $s .= '<td><a href="index.php?title=Tort_Law">torts</a></td>';
  $s .= '<td><a href="index.php?title=Contract_Law">contracts</a></td>';
  $s .= '<td><a href="index.php?title=Civil_Procedure">civ pro</a></td>';
  $s .= '<td><a href="index.php?title=Property_Law">property</a></td>';
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
  $s .= '<td class="rightbottom"><img src="images/law/rightbottom.gif" alt="main logo" border="0"/></td>';
  $s .= '</tr>';
  $s .= '</table>';
  
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  
  if ($wgOut->getPageTitle() !== 'Main Page') {  
  $s .= '<td valign="top" class="bluehatch">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0">';
  $s .= '<tr>';
  $s .= '<td class="main">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="810">';
  $s .= '<tr>';
  $s .= '<td style="padding-bottom:10px;">';
  if ( $wgOut->isArticle() ) {
  if( $wgUser->isLoggedIn() ) {
  $s .= $this->editThisPage();
  if ( $wgTitle->userCanMove() ) {
  $s .= ' | ' . $this->moveThisPage();
  }
  $s .= ' | ' . $this->historyLink();
  $s .= ' | ' . $this->whatLinksHere();
  if ( NS_SPECIAL !== $wgTitle->getNamespace() ) {
  $s .= ' | ' . $this->talkLink();
  }
  if( $tns == NS_USER || $tns == NS_USER_TALK ) {
  $id=User::idFromName($wgTitle->getText());
  if ($id != 0) {
  $s .= ' | ' . $this->userContribsLink();
  if( $this->showEmailUser( $id ) ) {
  $s .= ' | ' . $this->emailUserLink();
  }
  }
  }
  if ( $wgUser->isAllowed('delete') && NS_SPECIAL !== $wgTitle->getNamespace()) {
  $s .= ' | ' . $this->deleteThisPage();
  }
  if ( $wgUser->isAllowed('protect') && NS_SPECIAL !== $wgTitle->getNamespace() ) {
  $s .= ' | ' . $this->protectThisPage();
  }
  } else {
  $s .= $this->editThisPage();
  $s .= ' | ' . $this->talkLink();
  $s .= ' | <a href="index.php?title=Help">help</a>';
  }
  }
  
  $userPage = str_replace("User:","",$wgOut->getPageTitle());
  
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="810">';
  $s .= '<tr>';
  $s .= '<td>';
  
  if($wgTitle->getNamespace() == NS_USER){
    $s .= '<tr>';
  	$s .= '<td>';
	$s .= '<table cellpadding="0" cellspacing="0" border="0">';
	$s .= '<tr>';
  	$s .= '<td>';
	$userPage = str_replace("User:","",$wgOut->getPageTitle());
	$avatar = new wAvatar($wgUser->mId,"l");
	
	$s .= "<img src=images/avatars/" . $avatar->getAvatarImage() . " align=absmiddle hspace=3 vspace=5>";
	$s .= '</td><td class="pagetitle" style="padding-left:5px;">' . str_replace("User:","",$this->pageTitle()) ;
	if($userPage == $wgUser->mName){
		$s .= "</td></tr><tr><td colspan=2><a href=index.php?title=Profile_Image class='avatarline'>Add/Update Profile Image</a><br><br>";
	}
  $s .= '</td>';
	$s .= '</tr></table>';
  }
  
  else{
  $s .=  $this->pageTitle();
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
    $s .= '<td valign="top" width="835">';
	$s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
	$s .= '<tr>';
	$s .= '<td>';
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
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td style="padding-top:18px;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td class="footer" style="border-top:1px solid #dcdcdc;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td width="100" align="left"><img src="../images/journal/gnu-fdl.png" alt="" border="0"></td>';
  $s .= '<td align="center">';
  $s .= '<a href="#">About Wikia, Inc.</a> | ';
  $s .= '<a href="#">Terms of Use </a> | ';  
  $s .= '<a href="index.php?title=Special:Specialpages">Special Pages</a> | ';
  $s .= '<a href="#">Digg this page </a> | ';
  $s .= '<a href="#">Del.icio.us</a>';
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
require_once ('extensions/ListPagesClass.php');
$output = "";

# find information
$output .= '<table width=425 cellpadding=0 cellspacing=0 border=0 align=center>';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td colspan="2" class="title">';
$output .= 'law info ';
$output .= '<a href="javascript:toggle(\'otherfind\',\'toggle1\')"><span id="toggle1" style="font-size:8pt; font-weight:normal;">(expand)</span></a>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Areas_of_Law">areas of law</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Statutes_and_Regulations">statutes and regulations</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Case_Law">case law</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Questions">questions</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Law_Firms">law firms</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Law_Schools">law schools</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Blog_Index">blog index</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Dictionary">dictionary</a></td>';
$output .= '</tr>';
$output .= '</table>';

 #other information
$output .= '<div id=otherfind style="display:none;">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0">';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Law_Journals">law journals</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Category:Opinions">opinions archive</a></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Forms">forms</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Citation_Guide">citation guide</a></td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</div>';

#end information
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

#share beginning
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td colspan="2" class="title">';
$output .= 'share ';
$output .= '<a href="javascript:toggle(\'share\',\'toggle2\')"><span id="toggle2" style="font-size:8pt; font-weight:normal;">(expand)</span></a>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Create_Opinion">write an opinion</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Create_Question">ask a question</a></td>';
$output .= '</tr>';
$output .= '</table>';

#other share
$output .= '<div id="share" style="display:none;">';
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0">';
$output .= '<tr>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Add_Dictionary_Entry">add to the dictionary</a></td>';
$output .= '<td width="205" class="mainpagelinks"><a href="index.php?title=Add_Blog">add your blog</a></td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</div>';

#end share
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

#listpage 
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';

#subscribe title
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td class="title" style="padding-bottom:5px;">new opinions</td>';
$output .= '</tr>';
$output .= '<td>';
$output .= '<div id="destinationview">';
$list = new ListPagesView();
$list->setCategory("Opinions");
$list->setShowCount(6);
$list->setOrder("New");
$list->setLevel(1);
$list->setShowDetails("VoteBox");
 
$output .= $list->DisplayList();
$output .= '</div>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';


#subscribe
$output .= '<table width="425" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:5px;">';
$output .= '<tr>';
$output .= '<td class="bluehatch">';

#subscribe title
$output .= '<table width="410" cellpadding="2" cellspacing="2" border="0" align="center">';
$output .= '<tr>';
$output .= '<td colspan="3" class="title">subscribe</td>';
$output .= '<tr>';
$output .= '<form name=feedburner action="http://www.feedburner.com/fb/a/emailverify" method="post" target="popupwindow" onsubmit="window.open(\'http://www.feedburner.com\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');return true">';
$output .= '<td width="60">e-mail </td>';
$output .= '<td width=110><input type=text style="width:150px; color:#666666; padding:3px;" value="your e-mail" name="email" onfocus="clearDefault(this);"><input type="hidden" value="http://feeds.feedburner.com/~e?ffid=252114" name="url"/><input type="hidden" value="ArmchairGM" name="title"/></td>';
$output .= '<td><input type="button" value="go"></td>';
$output .= '</tr>';
$output .= '</form>';
$output .= '<tr>';
$output .= '<td width="60">rss</td>';
$output .= '<td>';
$output .= '<a href=http://feeds.feedburner.com/Armchairgm target=_new><img src="../images/rss.gif" alt="" border="0"></a>';
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>';

#end subscribe
$output .= '</td>';
$output .= '</tr>';
$output .= '</table>'; 

return $output;

 }
 
}
?>
