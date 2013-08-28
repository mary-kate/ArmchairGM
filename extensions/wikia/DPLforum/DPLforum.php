<?php
/*

 DPLforum v2.1.3 -- DynamicPageList-based forum extension

 This is a hacked version of DynamicPageList 1.12 that produces
 forum-style output.

 Added features:
  * addlastedit=true   (shows the last edit time)
  * addlasteditor=true (shows the last editor)
  * cache=false        (disables caching)
  * historylink=show   (appends a history link to the last edit)
  * historylink=embed  (links the last edit to the page history)
  * mode=table         (outputs <tr> tags)
  * newdays={number}   (marks topics less than {n} days old as new)
  * ordermethod=pageid (sorts by the page id)
  * start={number}     (starts listing at the {n+1}th topic)
  * timestamp=true     (uses timestamped URLs)
  * title={string}     (changes the page link & sets count=1)

  * Support for URL arguments when caching is disabled:
    * offset=X (adjusts start by X)

 Changed defaults:
  * addlastedit=true
  * cache=false
  * mode=table
  * newdays=7
  * ordermethod=lastedit
  * timestamp=true

 Author: meta:User:Algorithm
 http://meta.wikimedia.org/wiki/User:Algorithm

 DynamicPageList written by: n:en:User:IlyaHaykinson n:en:User:Amgine
 http://en.wikinews.org/wiki/User:Amgine
 http://en.wikinews.org/wiki/User:IlyaHaykinson

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 http://www.gnu.org/copyleft/gpl.html

 To install, add following to LocalSettings.php
   include("extensions/forum.php");

*/

$wgExtensionFunctions[] = "wfDPLforum";
$wgExtensionCredits['parserhook'][] = array(
'name' => 'DPLforum v2.1.3',
'url' => 'http://meta.wikimedia.org/wiki/DPLforum',
);

function wfDPLforum() {
  global $wgParser, $wgMessageCache;

  $wgMessageCache->addMessages( array(
    'forum_toomanycats' => 'DPL Forum: Too many categories!',
    'forum_toofewcats' => 'DPL Forum: Too few categories!',
    'forum_noresults' => 'DPL Forum: No results!',
    'forum_noincludecats' => 'DPL Forum: You need to include at least one category, or specify a namespace!',
  ));

  $wgParser->setHook( "forum", "parseForum" );
}

function parseForum($input) {
  $f = new DPLForum();
  return $f->parse($input);
}

class DPLForum {
  var $minCategories = 1;           // Minimum number of categories to look for
  var $maxCategories = 6;           // Maximum number of categories to look for
  var $minResultCount = 0;          // Minimum number of results to allow
  var $maxResultCount = 50;         // Maximum number of results to allow
  var $unlimitedResults = true;     // Allow unlimited results
  var $unlimitedCategories = false; // Allow unlimited categories

  var $bTableMode = true;
  var $bTimestamp = true;
  var $bLinkHistory = false;
  var $bEmbedHistory = false;
  var $bShowNamespace = true;
  var $bAddCategoryDate = false;
  var $bAddLastEdit = true;
  var $bAddLastEditor = false;
  var $vMarkNew = 7;

  function parse($input) {
    global $wgContLang;

    $sStartList = '';
    $sEndList = '';
    $sStartItem = '<tr>';
    $sEndItem = '</tr>';

    $iCount = 0;
    $iStart = 0;
    $sOrderMethod = 'lastedit';
    $bOrderAsc = false;
    $sRedirects = 'exclude';
    $iNamespace = -1;
    $bCache = false;
    $bSuppressErrors = false;

    $aParams = array();
    $aCategories = array();
    $aExcludeCategories = array();

    $aParams = explode("\n", $input);
    $parser = new Parser();

    foreach($aParams as $sParam)
    {
      $aParam = explode("=", $sParam);
      if( count( $aParam ) < 2 )
         continue;
      $sType = trim($aParam[0]);
      $sArg = trim($aParam[1]);
      switch($sType)
      {
        case 'category':
          $title = Title::newFromText( $parser->transformMsg($sArg, null) );
          if( !is_null( $title ) )
            $aCategories[] = $title;
          break;
        case 'notcategory':
          $title = Title::newFromText( $parser->transformMsg($sArg, null) );
          if( !is_null( $title ) )
            $aExcludeCategories[] = $title;
          break;
        case 'namespace':
          $iNamespace = $wgContLang->getNsIndex($sArg);
          if ( !$iNamespace )
            $iNamespace = intval($sArg);
          break;
        case 'start':
          $iStart = intval( $sArg );
          break;
        case 'count':
          $iCount = intval( $sArg );
          break;
        case 'mode':
          switch ($sArg)
          {
            case 'none':
              $this->bTableMode = false;
              $sStartList = '';
              $sEndList = '';
              $sStartItem = '';
              $sEndItem = '<br/>';
              break;
            case 'ordered':
              $this->bTableMode = false;
              $sStartList = '<ol>';
              $sEndList = '</ol>';
              $sStartItem = '<li>';
              $sEndItem = '</li>';
              break;
            case 'unordered':
              $this->bTableMode = false;
              $sStartList = '<ul>';
              $sEndList = '</ul>';
              $sStartItem = '<li>';
              $sEndItem = '</li>';
              break;
            case 'table':
            default:
              $this->bTableMode = true;
              $sStartList = '';
              $sEndList = '';
              $sStartItem = '<tr>';
              $sEndItem = '</tr>';
              break;
          }
          break;
        case 'order':
          $bOrderAsc = ($sArg == 'ascending');
          break;
        case 'ordermethod':
          $sOrderMethod = $sArg;
          break;
        case 'redirects':
          $sRedirects = $sArg;
          break;
        case 'timestamp':
          $this->bTimestamp = ($sArg != 'false');
          break;
        case 'historylink':
          $this->bEmbedHistory = ($sArg == 'embed' || $sArg == 'true');
          $this->bLinkHistory = ($this->bEmbedHistory || ($sArg == 'show'));
          break;
        case 'cache':
          $bCache = ($sArg == 'true');
          break;
        case 'suppresserrors':
          $bSuppressErrors = ($sArg == 'true');
          break;
        case 'addfirstcategorydate':
          $this->bAddCategoryDate = ($sArg == 'true');
          break;
        case 'addlastedit':
          $this->bAddLastEdit = ($sArg != 'false');
          break;
        case 'addlasteditor':
          $this->bAddLastEditor = ($sArg == 'true');
          break;
        case 'shownamespace':
          $this->bShowNamespace = ($sArg != 'false');
          break;
        case 'title':
          $tCustomTitle = Title::newFromText( $parser->transformMsg($sArg, null) );
          $iCount = 1;
          break;
        case 'newdays':
          $this->vMarkNew = intval($sArg);
          break;
      }
    }

    $iCatCount = count($aCategories);
    $iExcludeCatCount = count($aExcludeCategories);
    $iTotalCatCount = $iCatCount + $iExcludeCatCount;

    if($iCatCount < 1 && $iNamespace < 0)
    {
      if($bSuppressErrors) return '';
      return htmlspecialchars( wfMsg( 'forum_noincludecats' ) ); // "!!no included categories!!";
    }

    if($iTotalCatCount < $this->minCategories)
    {
      if($bSuppressErrors) return '';
      return htmlspecialchars( wfMsg( 'forum_toofewcats' ) ); // "!!too few categories!!";
    }

    if($iTotalCatCount > $this->maxCategories && !$this->unlimitedCategories)
    {
      if($bSuppressErrors) return '';
      return htmlspecialchars( wfMsg( 'forum_toomanycats' ) ); // "!!too many categories!!";
    }

    if($bCache == false)
    {
      global $wgParser, $wgRequest;

      $wgParser->disableCache();

      if( is_null($tCustomTitle) )
        $iStart += intval($wgRequest->getVal('offset'));
    }

    if($iStart < 0)
      $iStart = 0;

    if($iCount > 0)
    {
      if ($iCount < $this->minResultCount)
        $iCount = $this->minResultCount;
      else if ($iCount > $this->maxResultCount)
        $iCount = $this->maxResultCount;
    }
    else
    {
      if ($this->unlimitedResults)
        $iCount = 0x7FFFFFFF; // maximum integer value
      else
        $iCount = $this->maxResultCount;
    }

    //disallow showing date if the query doesn't have an inclusion category parameter
    if($iCatCount < 1)
      $this->bAddCategoryDate = false;

    //build the SQL query
    $dbr =& wfGetDB( DB_SLAVE );
    $sPageTable = $dbr->tableName( 'page' );
    $sRevTable = $dbr->tableName( 'revision' );
    $categorylinks = $dbr->tableName( 'categorylinks' );
    $sSqlSelectFrom = "SELECT page_namespace, page_title, r.rev_user_text, r.rev_timestamp, "
      . "c1.cl_timestamp FROM $sPageTable INNER JOIN $sRevTable AS r ON page_latest = r.rev_id";

    if($iNamespace >= 0)
      $sSqlWhere = ' WHERE page_namespace='.$iNamespace.' ';
    else
      $sSqlWhere = ' WHERE 1=1 ';

    switch($sRedirects)
    {
      case 'only':
        $sSqlWhere .= ' AND page_is_redirect = 1 ';
      case 'include':
        break;
      case 'exclude':
      default:
        $sSqlWhere .= ' AND page_is_redirect = 0 ';
        break;
    }

    $iCurrentTableNumber = 0;

    for($i = 0; $i < $iCatCount; $i++) {
      $sSqlSelectFrom .= " INNER JOIN $categorylinks AS c" . ($iCurrentTableNumber+1);
      $sSqlSelectFrom .= ' ON page_id = c'.($iCurrentTableNumber+1).'.cl_from';
      $sSqlSelectFrom .= ' AND c'.($iCurrentTableNumber+1).'.cl_to='.
        $dbr->addQuotes( $aCategories[$i]->getDbKey() );

      $iCurrentTableNumber++;
    }

    for($i = 0; $i < $iExcludeCatCount; $i++) {
      $sSqlSelectFrom .= " LEFT OUTER JOIN $categorylinks AS c" . ($iCurrentTableNumber+1);
      $sSqlSelectFrom .= ' ON page_id = c'.($iCurrentTableNumber+1).'.cl_from';
      $sSqlSelectFrom .= ' AND c'.($iCurrentTableNumber+1).'.cl_to='.
        $dbr->addQuotes( $aExcludeCategories[$i]->getDbKey() );

      $sSqlWhere .= ' AND c'.($iCurrentTableNumber+1).'.cl_to IS NULL';

      $iCurrentTableNumber++;
    }

    switch($sOrderMethod)
    {
      case 'categoryadd':
        $sSqlWhere .= ' ORDER BY c1.cl_timestamp ';
        break;
      case 'pageid':
        $sSqlWhere .= ' ORDER BY page_id ';
        break;
      case 'lastedit':
      default:
        $sSqlWhere .= ' ORDER BY r.rev_timestamp ';
        break;
    }

    if($bOrderAsc)
      $sSqlWhere .= 'ASC';
    else
      $sSqlWhere .= 'DESC';

    $sSqlWhere .= ' LIMIT ' . $iStart . ',' . $iCount;

    //DEBUG: output SQL query
    //$output .= 'QUERY: [' . $sSqlSelectFrom . $sSqlWhere . "]<br />";

    // process the query
    $res = $dbr->query($sSqlSelectFrom . $sSqlWhere);

    if($dbr->numRows( $res ) == 0)
    {
      if($this->minResultCount > 0)
      {
        if($bSuppressErrors) return '';
        return htmlspecialchars( wfMsg( 'forum_noresults' ) );
      }
      else if($this->bTableMode && !is_null($tCustomTitle))
      {
        $this->bTimestamp = false;
        $output .= $sStartList . $sStartItem;
        $output .= $this->buildOutput($tCustomTitle, 'Never', '', '');
        $output .= $sEndItem . "\n" . $sEndList;
        return $output;
      }
    }

    $this->vMarkNew = $dbr->timestamp( time() - intval( $this->vMarkNew * 86400 ) );
    $output .= $sStartList;

    while($row = $dbr->fetchObject( $res ) ) {
      if( is_null($tCustomTitle) )
        $title = Title::makeTitle($row->page_namespace, $row->page_title);
      else
        $title = $tCustomTitle;

      $output .= $sStartItem;
      $output .= $this->buildOutput($title, $row->rev_timestamp,
                                    $row->rev_user_text, $row->cl_timestamp);
      $output .= $sEndItem . "\n";
    }

    $output .= $sEndList;

    return $output;
  }

  // Generates a single line of output.
  function buildOutput($title, $time, $user, $cat)
  {
    global $wgLang, $wgUser;
    $output = '';
    $sk =& $wgUser->getSkin();

    if($this->bAddCategoryDate)
    {
      if(intval($cat)>0)
        $cat = $wgLang->date($cat);
      if($this->bTableMode)
        $output .= '<td class="forum_category">' . $cat . '</td>';
      else
        $output .= $cat . ': ';
    }

    if($this->bTableMode)
      $output .= '<td class="forum_title">';

    $text = $query = $props = '';
    if($this->bShowNamespace == false)
      $text = htmlspecialchars($title->getText());
    if($this->bTimestamp)
      $query = 't=' . $time;
    if($time > $this->vMarkNew)
      $props = ' class="forum_new"';
    $output .= $sk->makeKnownLinkObj($title, $text, $query, '', '', $props);

    if($this->bTableMode)
      $output .= '</td>';

    if($this->bAddLastEdit)
    {
      if(intval($time)>0)
        $time = $wgLang->timeanddate($time, true);

      if($this->bLinkHistory)
      {
        if($this->bEmbedHistory)
          $time = $sk->makeKnownLinkObj($title, $time, 'action=history');
        else
          $time .= ' (' . $sk->makeKnownLinkObj($title,
            wfMsg('hist'), 'action=history') . ')';
      }

      if($this->bTableMode)
        $output .= '<td class="forum_edited">' . $time . '</td>';
      else
        $output .= ' ' . $time;
    }

    if($this->bAddLastEditor)
    {
      $user = Title::newFromText( $user, NS_USER );
      if( is_null($user) )
        $user = '';
      else
        $user = $sk->makeKnownLinkObj($user, $user->getText());

      if($this->bTableMode)
        $output .= '<td class="forum_editor">' . $user . '</td>';
      else
        $output .= ' by ' . $user;
    }
    return $output;
  }
}
?>