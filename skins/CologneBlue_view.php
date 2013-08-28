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

//echo $wgSiteView->getDomainName();

class SkinCologneBlue_view extends Skin {
  
  #set stylesheet
  function getStylesheet() {
    return "common/journal.css";
  }
  
  #set skinname
  function getSkinName() {
    return "cologneblue";
  }

    #searchform
  function searchForm( $label = "" )
  {
  global $wgRequest, $wgSiteView;
  
  $s .= '<form method="get" action="http://www.google.com/custom">';
  $s .= '<input type="hidden" name="ie" value="UTF-8"/>';
  $s .= '<input type="hidden" name="oe" value="UTF-8"/>';
  $s .= '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>';
  $s .= '<input type="text" name="q" style="width:150px;" maxlength=255 value=' . htmlspecialchars(substr($search,0,256)). '/>';
  $s .= '</td><td>';
  $s .= '<input type="submit" name="sa" value="search"/></td></tr></table>';
  $s .= '<input type="hidden" name="cof" value="S:http://www.armchairgm.com;VLC:#26579A;AH:center;BGC:#ffffff;LH:51;LC:#26579A;L:http://www.armchairgm.com/mwiki/brand/logo.gif;ALC:#26579A;LW:302;T:#000000;AWFID:5f943568c946c3bb;"/>';
  $s .= '<input type="hidden" name="domains" value="armchairgm.com"/>';
  $s .= '<input type="hidden" name="sitesearch" value="armchairgm.com"/></form>';
  
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
  $s .= '<input type="hidden" name="ie" value="UTF-8"/>';
  $s .= '<input type="hidden" name="oe" value="UTF-8"/>';
  $s .= '<input type="text" name="q" style="width:150px;" maxlength="255" value="' . htmlspecialchars(substr($search,0,256)). '" /></span>';
  
  $s .= "<span id=\"searchbox\"><input type='text' style='width:150px;' name=\"search\" size='12' value=\""
  . htmlspecialchars(substr($search,0,256)) . "\" /></span>";
  
  $s .= "</td>";
  if($wgSiteView->getDomainName()==""){
  $s .= "<td>";
  $s .= "<select onchange=\"toggleSearch();document.search.action=this.value\">";
  $s .= "<option value='\"$action\"'>armchairgm.com</option>";
  $s .= "<option value='http://www.google.com/custom'>google</option>";
  $s .= "</select>";
  $s .= "</td>";
  }
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
  
  $adCode = $wgSiteView->getAd_id();
  if(!$adCode)$adCode = "pub-2291439177915740";
  
  ## left ads
  $s .= '<td valign="top" width="140" style="padding-right:15px; padding-top:10px;">';
  $s .= '<script type="text/javascript"><!--' . "\n";
  $s .= 'google_ad_client = "' . $adCode . '";' . "\n";
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
  $s .= '<td align="center" style="padding-bottom:20px;">';
  $s .= '<script type="text/javascript"><!--' . "\n";
  $s .= 'google_ad_client = "' . $adCode . '";' . "\n";
  $s .= 'google_ad_width = 728;' . "\n";
  $s .= 'google_ad_height = 90;' . "\n";
  $s .= 'google_ad_format = "728x90_as";' . "\n";
  $s .= 'google_ad_type = "image";' . "\n";
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
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Recentchanges"){
  	$s .= "On";
  }
  $s .= '">';
  $s .= '<a href="index.php?title=Special:Recentchanges">recent edits</a>';
  $s .= '</td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  $s .= '<td width="100" class="toptabs';
  if($wgTitle->getText() == "Special:SiteScout"){
  	$s .= "On";
  }
  $s .= '"><a href="index.php?title=Special:SiteScout">site scout</a></td>';
  $s .= '<td width="5" class="topright">&nbsp;</td>';
  //$s .= '<td width="100" class="toptabs';
    if($wgTitle->getText() == "Top Stuff"){
  	//$s .= "On";
  }
  //$s .= '"><a href="index.php?title=Top_Stuff">top stuff</a></td>';
  
  ###user login
  $s .= '<td class="topright">';
  
  $s .= 'welcome ';
  $avatar = new wAvatar($wgUser->mId,"s");
  $s .= "<img src=\"images/avatars/" . $avatar->getAvatarImage() . "\" align=\"middle\" alt=\"avatar\" style=\"border:1px solid #cccccc;margin-bottom:8px;\" />";
  $s .= ' <b>' . $wgUser->getName() . '</b>';
  if ( $wgUser->isLoggedIn() ) {
    if ( $wgUser->getNewtalk() ) {
	  $s .= " " . $this->makeKnownLinkObj($wgUser->getTalkPage(), "<img src='images/talkMessage.gif' width=14 height=10 border='0' alt='new message on talk page'/>");
	  }
  }
  if ( $wgUser->isLoggedIn() ) {
	  if($wgSiteView->isUserOwner()){
		  $s .='&nbsp;| <a href="index.php?title=Special:ViewManager&method=edit&name=' . $wgSiteView->getDomainName() . '">Openserving Settings</a>';
	  }
    $s .='&nbsp;| <a href="index.php?title=Help:Contents">help</a> | ';
    $s .=  $this->makeKnownLink( $lo, wfMsg( "logout" ), $q );
  } else {
	//$s .= ' | <a href="index.php?title=ArmchairGM:Community_Portal">community portal</a> | ';
	$s .=  '| <a href="javascript:Login()">log-in</a>';
  }
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td class="middleleft">';
  $s .= '<a href="index.php?title=My_Openserve"><img src="images/views/' . $wgSiteView->getLogo() . '" alt="main logo" border="0"/></a>';
  $s .= '</td>';
  $s .= '<td class="middleright">';
  $s .= $this->searchForm();
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '<tr>';
  $s .= '<td class="fade" colspan="2" >&nbsp;</td>';
  $s .= '</tr>';
  $s .= '</table>';
  
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  
  $width=0;
  if ($wgOut->getPageTitle() == 'My Openserve') {
  $width=535;
  } else {
  $width=810;
  }
  
  if ($wgOut->getPageTitle() !== 'My Openserve') {  
  $s .= '<td valign="top" class="bluehatch">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0">';
  $s .= '<tr>';
  $s .= '<td class="main">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="' . $width . '">';
  $s .= '<tr>';
  $s .= '<td style="padding-bottom:10px;">';
  if ( $wgOut->isArticle() ) {
  if( $wgUser->isLoggedIn() ) {
  $s .= '<a href="index.php?title=My Openserve">home</a>';
  $s .= ' | ' . $this->editThisPage();
  $s .= ' | ' . $this->moveThisPage();
  $s .= ' | ' . $this->historyLink();
  $s .= ' | ' . $this->whatLinksHere();
  $s .= ' | ' . $this->talkLink();
  if( $tns == NS_USER || $tns == NS_USER_TALK ) {
  $id=User::idFromName($wgTitle->getText());
  if ($id != 0) {
  $s .= ' | ' . $this->userContribsLink();
  if( $this->showEmailUser( $id ) ) {
  $s .= ' | ' . $this->emailUserLink();
  }
  }
  }
  if ( $wgUser->isAllowed('delete') ) {
  $s .= ' | ' . $this->deleteThisPage();
  }
  if ( $wgUser->isAllowed('protect') ) {
  $s .= ' | ' . $this->protectThisPage();
  }
  } else {
  $s .= '<a href="index.php?title=My Openserve">home</a>';
  $s .= ' | ' . '<a href=javascript:Register()>register</a>';
  $s .= ' | ' . $this->editThisPage();
  $s .= ' | ' . $this->talkLink();
  $s .= ' | <a href="index.php?title=Help">help</a>';
  }
  }
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="' . $width . '">';
  $s .= '<tr>';
  $s .= '<td class="pagetitle">';
  $s .= $this->pageTitle();
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
    $s .= '<td valign="top" width="' . $width . '">';
	$s .= '<table border="0" cellpadding="0" cellspacing="0" width="535">';
	$s .= '<tr>';
	$s .= '<td class="bluehatch">';
  }
  	
  return $s;
	
	}
 
 function doAfterContent()
  {
 
 global $wgOut, $wgUser;
  
  if ($wgOut->getPageTitle() == 'My Openserve') {
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '<td width="290" valign="top" style="padding-left:10px;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="285" align="center">';
  $s .= '<tr>';
  $s .= '<td class="title">';
  $s .= '<table border="0" cellpadding="0"  class="createOpinionBox" cellspacing="0" width="290" align="center">';
  $s .= '<tr>';
  $s .= '<td class="bluehatch">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0">';
  $s .= '<tr>';
  $s .= '<td width="25">';
  $s .= '<img src="../images/journal/editmain.gif" border="0" alt="" />';
  $s .= '</td>';
  $s .= '<td>';
  $s .= '<a href="index.php?title=Create Openserve Opinions" class="title">write your own opinion</a>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="290" align="center">';
  $s .= '<tr>';
  $s .= '<td style="padding-top:8px;">';
  $s .= $this->renderFeeds();
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>'; 
  } else {
  $cat = $this->getCategoryLinks();
  if( $cat ) $s .= "<br />$cat\n";
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  $s .= '</td>';
  $s .= '</tr>';
  $s .= '</table>';
  }
  
  ##footer
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td style="padding-top:18px;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td class="footer" style="border-top:1px solid #dcdcdc;">';
  $s .= '<table border="0" cellpadding="0" cellspacing="0" width="835">';
  $s .= '<tr>';
  $s .= '<td width="100" align="left"><img src="../images/journal/gnu-fdl.png" alt="" border="0"/></td>';
  $s .= '<td align="center">';
  $s .= '<a href="http://www.wikia.com/wiki/About_Wikia">About Wikia, Inc.</a> | ';
  $s .= '<a href="http://www.wikia.com/wiki/Terms_of_use">Terms of Use </a> | ';  
  $s .= '<a href="index.php?title=Special:Specialpages">Special Pages</a> | ';
  $s .= '<a href="http://digg.com/submit">Digg this page </a> | ';
  $s .= '<a href="http://del.icio.us/post">Del.icio.us</a>';
  $s .= '</td>';
  $s .= '<td width="100" align="right"><img src="../images/journal/poweredby_mediawiki_88x31.png" alt="" border="0"/></td>';
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
 
 function renderFeeds() {
	global $wgOut, $wgSiteView;
	require_once ('extensions/ListPagesClass.php');

	$output = "";
	//$output .= " status: <span id=status></span><br> status2: <span id=status2></span>";
	if($wgSiteView->isUserAdmin() == true){
		$output .= '<div class="addfeed"><a href="javascript:addFeed()">add feed</a></div>';
	}
	
	$output .= "<div id=\"listpages\">";
	$items .= "var feedItems = [];";
	$dbr =& wfGetDB( DB_SLAVE );
	$sql = "SELECT feed_id,feed_title,feed_count,feed_ctg,feed_order_by,feed_item_order FROM site_view_feeds WHERE feed_mirror_id=" . $wgSiteView->getID() . " ORDER BY feed_item_order";
	$res = $dbr->query($sql);
	$x = 0;
	while ($row = $dbr->fetchObject( $res ) ) {
		
		$output .= "<div class=\"feedItem\" id=\"item_" . $row->feed_id . "\">";
		$output .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td><div class=\"feedtitle\">" . $row->feed_title . "</div>";
		if($wgSiteView->isUserAdmin() == true){
			$output .= "<td align=\"right\" valign=\"top\"><a href=\"javascript:;\" class=\"editFeed\" id=\"el_" . $row->feed_id . "\" >edit<a> | <a href=\"javascript:;\" class=\"deleteFeed\" id=\"dl_" . $row->feed_id . "\">remove</a></td>";
	 	}
		$output .= "</td></tr></table>";
		$list = new ListPages();
		$list->setCategory($row->feed_ctg);
		$list->setShowCount($row->feed_count);
		$list->setPageNo(1);
		$list->setOrder($row->feed_order_by);
		$list->setBool("ShowVoteBox","Yes");
		$list->setBool("ShowStats","No");
		$list->setBool("ShowDate","No");
		$list->setHash($row->feed_id . $row->feed_ctg);
		$list->setBool("useCache","Yes");
		if(strtoupper($row->feed_order_by) == "NEW"){
			$list->setShowPublished("No");
		}else{
			$list->setShowPublished("Yes");
		}
		$output .=  $list->DisplayList();
	
		$output .= "</div>";
		$items .= "feedItems[" . $x  . "] = {id:" . $row->feed_id . ",title:'" .  str_replace("'","\'",$row->feed_title)  . "',categories:'" .  str_replace("'","\'",$row->feed_ctg)  . "',count:" .  $row->feed_count  . ",itemOrder:" .  $x  . ",orderBy:'" .  $row->feed_order_by  . "'};";
	 	$x++;
	 }
	
	$output .= "</div>";
	if($wgSiteView->isUserAdmin() == true){
		$output .= "<script>" . $items . "</script>";
	}
	return $output;
}

function getMainPage(){}
	
}
?>
