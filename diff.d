diff -NaurB -x .svn includes/AjaxDispatcher.php /srv/web/fp014/source/includes/AjaxDispatcher.php
--- includes/AjaxDispatcher.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/AjaxDispatcher.php	2007-02-01 01:03:18.000000000 +0000
@@ -14,7 +14,7 @@
 	var $func_name;
 	var $args;
 
-	function AjaxDispatcher() {
+	function __construct() {
 		wfProfileIn( __METHOD__ );
 
 		$this->mode = "";
@@ -28,14 +28,14 @@
 		}
 
 		if ($this->mode == "get") {
-			$this->func_name = $_GET["rs"];
+			$this->func_name = isset( $_GET["rs"] ) ? $_GET["rs"] : '';
 			if (! empty($_GET["rsargs"])) {
 				$this->args = $_GET["rsargs"];
 			} else {
 				$this->args = array();
 			}
 		} else {
-			$this->func_name = $_POST["rs"];
+			$this->func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : '';
 			if (! empty($_POST["rsargs"])) {
 				$this->args = $_POST["rsargs"];
 			} else {
@@ -47,7 +47,7 @@
 
 	function performAction() {
 		global $wgAjaxExportList, $wgOut;
-		
+
 		if ( empty( $this->mode ) ) {
 			return;
 		}
@@ -59,7 +59,7 @@
 		} else {
 			try {
 				$result = call_user_func_array($this->func_name, $this->args);
-				
+
 				if ( $result === false || $result === NULL ) {
 					header( 'Status: 500 Internal Error', true, 500 );
 					echo "{$this->func_name} returned no data";
@@ -68,7 +68,7 @@
 					if ( is_string( $result ) ) {
 						$result= new AjaxResponse( $result );
 					}
-					
+
 					$result->sendHeaders();
 					$result->printText();
 				}
@@ -82,7 +82,7 @@
 				}
 			}
 		}
-		
+
 		wfProfileOut( __METHOD__ );
 		$wgOut = null;
 	}
diff -NaurB -x .svn includes/AjaxFunctions.php /srv/web/fp014/source/includes/AjaxFunctions.php
--- includes/AjaxFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/AjaxFunctions.php	2007-02-01 01:03:18.000000000 +0000
@@ -45,7 +45,7 @@
    if ($iconv_to != "UTF-8") {
        $decodedStr = iconv("UTF-8", $iconv_to, $decodedStr);
    }
-  
+ 
    return $decodedStr;
 }
 
@@ -71,7 +71,7 @@
 function wfSajaxSearch( $term ) {
 	global $wgContLang, $wgOut;
 	$limit = 16;
-	
+
 	$l = new Linker;
 
 	$term = str_replace( ' ', '_', $wgContLang->ucfirst( 
@@ -81,7 +81,7 @@
 	if ( strlen( str_replace( '_', '', $term ) )<3 )
 		return;
 
-	$db =& wfGetDB( DB_SLAVE );
+	$db = wfGetDB( DB_SLAVE );
 	$res = $db->select( 'page', 'page_title',
 			array(  'page_namespace' => 0,
 				"page_title LIKE '". $db->strencode( $term) ."%'" ),
@@ -108,8 +108,8 @@
 	$subtitlemsg = ( Title::newFromText($term) ? 'searchsubtitle' : 'searchsubtitleinvalid' );
 	$subtitle = $wgOut->parse( wfMsg( $subtitlemsg, wfEscapeWikiText($term) ) ); #FIXME: parser is missing mTitle !
 
-	$term = htmlspecialchars( $term );
-	$html = '<div style="float:right; border:solid 1px black;background:gainsboro;padding:2px;"><a onclick="Searching_Hide_Results();">' 
+	$term = urlencode( $term );
+	$html = '<div style="float:right; border:solid 1px black;background:gainsboro;padding:2px;"><a onclick="Searching_Hide_Results();">'
 		. wfMsg( 'hideresults' ) . '</a></div>'
 		. '<h1 class="firstHeading">'.wfMsg('search')
 		. '</h1><div id="contentSub">'. $subtitle . '</div><ul><li>'
@@ -121,11 +121,11 @@
 					"search=$term&go=Go" )
 		. "</li></ul><h2>" . wfMsg( 'articletitles', $term ) . "</h2>"
 		. '<ul>' .$r .'</ul>'.$more;
-		
+
 	$response = new AjaxResponse( $html );
-	
+
 	$response->setCacheDuration( 30*60 );
-		
+
 	return $response;
 }
 
@@ -152,14 +152,14 @@
 
 	if($watch) {
 		if(!$watching) {
-			$dbw =& wfGetDB(DB_MASTER);
+			$dbw = wfGetDB(DB_MASTER);
 			$dbw->begin();
 			$article->doWatch();
 			$dbw->commit();
 		}
 	} else {
 		if($watching) {
-			$dbw =& wfGetDB(DB_MASTER);
+			$dbw = wfGetDB(DB_MASTER);
 			$dbw->begin();
 			$article->doUnwatch();
 			$dbw->commit();
diff -NaurB -x .svn includes/AjaxResponse.php /srv/web/fp014/source/includes/AjaxResponse.php
--- includes/AjaxResponse.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/AjaxResponse.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,28 +1,29 @@
 <?php
+if( !defined( 'MEDIAWIKI' ) ) {
+	die( 1 );
+}
 
-if( !defined( 'MEDIAWIKI' ) )
-        die( 1 );
-
+/** @todo document */
 class AjaxResponse {
 	var $mCacheDuration;
 	var $mVary;
-	
+
 	var $mDisabled;
 	var $mText;
 	var $mResponseCode;
 	var $mLastModified;
 	var $mContentType;
 
-	function AjaxResponse( $text = NULL ) {
+	function __construct( $text = NULL ) {
 		$this->mCacheDuration = NULL;
 		$this->mVary = NULL;
-		
+
 		$this->mDisabled = false;
 		$this->mText = '';
 		$this->mResponseCode = '200 OK';
 		$this->mLastModified = false;
 		$this->mContentType= 'text/html; charset=utf-8';
-		
+
 		if ( $text ) {
 			$this->addText( $text );
 		}
@@ -39,15 +40,15 @@
 	function setResponseCode( $code ) {
 		$this->mResponseCode = $code;
 	}
-	
+
 	function setContentType( $type ) {
 		$this->mContentType = $type;
 	}
-	
+
 	function disable() {
 		$this->mDisabled = true;
 	}
-	
+
 	function addText( $text ) {
 		if ( ! $this->mDisabled && $text ) {
 			$this->mText .= $text;
@@ -59,62 +60,62 @@
 			print $this->mText;
 		}
 	}
-	
+
 	function sendHeaders() {
 		global $wgUseSquid, $wgUseESI;
-		
+
 		if ( $this->mResponseCode ) {
 			$n = preg_replace( '/^ *(\d+)/', '\1', $this->mResponseCode );
 			header( "Status: " . $this->mResponseCode, true, (int)$n );
 		}
-		
+
 		header ("Content-Type: " . $this->mContentType );
-		
+
 		if ( $this->mLastModified ) {
 			header ("Last-Modified: " . $this->mLastModified );
 		}
 		else {
 			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 		}
-		
+
 		if ( $this->mCacheDuration ) {
-			
+
 			# If squid caches are configured, tell them to cache the response, 
 			# and tell the client to always check with the squid. Otherwise,
 			# tell the client to use a cached copy, without a way to purge it.
-			
+
 			if( $wgUseSquid ) {
-				
+
 				# Expect explicite purge of the proxy cache, but require end user agents
 				# to revalidate against the proxy on each visit.
 				# Surrogate-Control controls our Squid, Cache-Control downstream caches
-				
+
 				if ( $wgUseESI ) {
 					header( 'Surrogate-Control: max-age='.$this->mCacheDuration.', content="ESI/1.0"');
 					header( 'Cache-Control: s-maxage=0, must-revalidate, max-age=0' );
 				} else {
 					header( 'Cache-Control: s-maxage='.$this->mCacheDuration.', must-revalidate, max-age=0' );
 				}
-				
+
 			} else {
-			
+
 				# Let the client do the caching. Cache is not purged.
 				header ("Expires: " . gmdate( "D, d M Y H:i:s", time() + $this->mCacheDuration ) . " GMT");
 				header ("Cache-Control: s-max-age={$this->mCacheDuration},public,max-age={$this->mCacheDuration}");
 			}
-			
+
 		} else {
 			# always expired, always modified
 			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
 			header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
 			header ("Pragma: no-cache");                          // HTTP/1.0
 		}
-		
+
 		if ( $this->mVary ) {
 			header ( "Vary: " . $this->mVary );
 		}
 	}
-	
+
 	/**
 	 * checkLastModified tells the client to use the client-cached response if
 	 * possible. If sucessful, the AjaxResponse is disabled so that
@@ -154,9 +155,9 @@
 				$this->setResponseCode( "304 Not Modified" );
 				$this->disable();
 				$this->mLastModified = $lastmod;
-				
+
 				wfDebug( "$fname: CACHED client: $ismodsince ; user: $wgUser->mTouched ; page: $timestamp ; site $wgCacheEpoch\n", false );
-				
+
 				return true;
 			} else {
 				wfDebug( "$fname: READY  client: $ismodsince ; user: $wgUser->mTouched ; page: $timestamp ; site $wgCacheEpoch\n", false );
@@ -167,11 +168,11 @@
 			$this->mLastModified = $lastmod;
 		}
 	}
-	
+
 	function loadFromMemcached( $mckey, $touched ) {
 		global $wgMemc;
 		if ( !$touched ) return false;
-		
+
 		$mcvalue = $wgMemc->get( $mckey );
 		if ( $mcvalue ) {
 			# Check to see if the value has been invalidated
@@ -183,20 +184,20 @@
 				wfDebug( "$mckey has expired\n" );
 			}
 		}
-		
+
 		return false;
 	}
-	
+
 	function storeInMemcached( $mckey, $expiry = 86400 ) {
 		global $wgMemc;
-		
-		$wgMemc->set( $mckey, 
+
+		$wgMemc->set( $mckey,
 			array(
 				'timestamp' => wfTimestampNow(),
 				'value' => $this->mText
 			), $expiry
 		);
-		
+
 		return true;
 	}
 }
diff -NaurB -x .svn includes/api/ApiFormatBase.php /srv/web/fp014/source/includes/api/ApiFormatBase.php
--- includes/api/ApiFormatBase.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiFormatBase.php	2007-02-01 01:03:18.000000000 +0000
@@ -170,7 +170,7 @@
 	}
 
 	public static function getBaseVersion() {
-		return __CLASS__ . ': $Id: ApiFormatBase.php 19434 2007-01-18 02:04:11Z brion $';
+		return __CLASS__ . ': $Id: ApiFormatBase.php 19427 2007-01-18 00:01:20Z brion $';
 	}
 }
 
@@ -226,7 +226,7 @@
 	}
 	
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiFormatBase.php 19434 2007-01-18 02:04:11Z brion $';
+		return __CLASS__ . ': $Id: ApiFormatBase.php 19427 2007-01-18 00:01:20Z brion $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/api/ApiFormatJson_json.php /srv/web/fp014/source/includes/api/ApiFormatJson_json.php
--- includes/api/ApiFormatJson_json.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiFormatJson_json.php	2007-02-01 01:03:18.000000000 +0000
@@ -46,7 +46,7 @@
 * DAMAGE.
 *
 * @category
-* @package     Services_JSON
+* @addtogroup     Services_JSON
 * @author      Michal Migurski <mike-json@teczno.com>
 * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
 * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
diff -NaurB -x .svn includes/api/ApiFormatYaml_spyc.php /srv/web/fp014/source/includes/api/ApiFormatYaml_spyc.php
--- includes/api/ApiFormatYaml_spyc.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiFormatYaml_spyc.php	2007-02-01 01:03:18.000000000 +0000
@@ -6,12 +6,12 @@
    * @link http://spyc.sourceforge.net/
    * @copyright Copyright 2005-2006 Chris Wanstrath
    * @license http://www.opensource.org/licenses/mit-license.php MIT License
-   * @package Spyc
+   * @addtogroup Spyc
    */
 
   /** 
    * A node, used by Spyc for parsing YAML.
-   * @package Spyc
+   * @addtogroup Spyc
    */
   class YAMLNode {
     /**#@+
@@ -59,7 +59,7 @@
    *   $parser = new Spyc;
    *   $array  = $parser->load($file);
    * </code>
-   * @package Spyc
+   * @addtogroup Spyc
    */
   class Spyc {
     
diff -NaurB -x .svn includes/api/ApiPageSet.php /srv/web/fp014/source/includes/api/ApiPageSet.php
--- includes/api/ApiPageSet.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiPageSet.php	2007-02-01 01:03:18.000000000 +0000
@@ -308,7 +308,7 @@
 		if($linkBatch->isEmpty())
 			return;
 			
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		$set = $linkBatch->constructSet('page', $db);
 
 		// Get pageIDs data from the `page` table
@@ -331,7 +331,7 @@
 			'page_id' => $pageids
 		);
 
-		$db = & $this->getDB();
+		$db = $this->getDB();
 
 		// Get pageIDs data from the `page` table
 		$this->profileDBIn();
@@ -406,7 +406,7 @@
 		if(empty($revids))
 			return;
 			
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		$pageids = array();
 		$remaining = array_flip($revids);
 		
@@ -438,7 +438,7 @@
 	private function resolvePendingRedirects() {
 
 		if($this->mResolveRedirects) {
-			$db = & $this->getDB();
+			$db = $this->getDB();
 			$pageFlds = $this->getPageTableFields();
 	
 			// Repeat until all redirects have been resolved
@@ -470,7 +470,7 @@
 	private function getRedirectTargets() {
 
 		$linkBatch = new LinkBatch();
-		$db = & $this->getDB();
+		$db = $this->getDB();
 
 		// find redirect targets for all redirect pages
 		$this->profileDBIn();
@@ -592,7 +592,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiPageSet.php 17929 2006-11-25 17:11:58Z tstarling $';
+		return __CLASS__ . ': $Id: ApiPageSet.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/api/ApiQueryAllpages.php /srv/web/fp014/source/includes/api/ApiQueryAllpages.php
--- includes/api/ApiQueryAllpages.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryAllpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -49,7 +49,7 @@
 	private function run($resultPageSet = null) {
 
 		wfProfileIn($this->getModuleProfileName() . '-getDB');
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		wfProfileOut($this->getModuleProfileName() . '-getDB');
 
 		wfProfileIn($this->getModuleProfileName() . '-parseParams');
@@ -167,7 +167,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryAllpages.php 17880 2006-11-23 08:25:56Z nickj $';
+		return __CLASS__ . ': $Id: ApiQueryAllpages.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/api/ApiQueryBacklinks.php /srv/web/fp014/source/includes/api/ApiQueryBacklinks.php
--- includes/api/ApiQueryBacklinks.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryBacklinks.php	2007-02-01 01:03:18.000000000 +0000
@@ -122,7 +122,7 @@
 		if ($redirect)
 			$this->addWhereFld('page_is_redirect', 0);
 
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		if (!is_null($continue)) {
 			$plfrm = intval($this->contID);
 			if ($this->contLevel == 0) {
@@ -352,7 +352,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryBacklinks.php 17880 2006-11-23 08:25:56Z nickj $';
+		return __CLASS__ . ': $Id: ApiQueryBacklinks.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
\ No newline at end of file
diff -NaurB -x .svn includes/api/ApiQueryLogEvents.php /srv/web/fp014/source/includes/api/ApiQueryLogEvents.php
--- includes/api/ApiQueryLogEvents.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryLogEvents.php	2007-02-01 01:03:18.000000000 +0000
@@ -39,7 +39,7 @@
 		$limit = $type = $start = $end = $dir = $user = $title = null;
 		extract($this->extractRequestParams());
 
-		$db = & $this->getDB();
+		$db = $this->getDB();
 
 		list($tbl_logging, $tbl_page, $tbl_user) = $db->tableNamesN('logging', 'page', 'user');
 
@@ -167,7 +167,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryLogEvents.php 17952 2006-11-27 08:36:57Z nickj $';
+		return __CLASS__ . ': $Id: ApiQueryLogEvents.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/api/ApiQuery.php /srv/web/fp014/source/includes/api/ApiQuery.php
--- includes/api/ApiQuery.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQuery.php	2007-02-01 01:03:18.000000000 +0000
@@ -79,10 +79,10 @@
 		$this->mAllowedGenerators = array_merge($this->mListModuleNames, $this->mPropModuleNames);
 	}
 
-	public function & getDB() {
+	public function getDB() {
 		if (!isset ($this->mSlaveDB)) {
 			$this->profileDBIn();
-			$this->mSlaveDB = & wfGetDB(DB_SLAVE);
+			$this->mSlaveDB = wfGetDB(DB_SLAVE);
 			$this->profileDBOut();
 		}
 		return $this->mSlaveDB;
@@ -370,7 +370,7 @@
 	public function getVersion() {
 		$psModule = new ApiPageSet($this);
 		$vers = array ();
-		$vers[] = __CLASS__ . ': $Id: ApiQuery.php 17374 2006-11-03 06:53:47Z yurik $';
+		$vers[] = __CLASS__ . ': $Id: ApiQuery.php 19598 2007-01-22 23:50:42Z nickj $';
 		$vers[] = $psModule->getVersion();
 		return $vers;
 	}
diff -NaurB -x .svn includes/api/ApiQueryRecentChanges.php /srv/web/fp014/source/includes/api/ApiQueryRecentChanges.php
--- includes/api/ApiQueryRecentChanges.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryRecentChanges.php	2007-02-01 01:03:18.000000000 +0000
@@ -87,7 +87,7 @@
 
 		$data = array ();
 		$count = 0;
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		$res = $this->select(__METHOD__);
 		while ($row = $db->fetchObject($res)) {
 			if (++ $count > $limit) {
@@ -181,7 +181,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryRecentChanges.php 17880 2006-11-23 08:25:56Z nickj $';
+		return __CLASS__ . ': $Id: ApiQueryRecentChanges.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
\ No newline at end of file
diff -NaurB -x .svn includes/api/ApiQueryRevisions.php /srv/web/fp014/source/includes/api/ApiQueryRevisions.php
--- includes/api/ApiQueryRevisions.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryRevisions.php	2007-02-01 01:03:18.000000000 +0000
@@ -149,7 +149,7 @@
 		$count = 0;
 		$res = $this->select(__METHOD__);
 
-		$db = & $this->getDB();
+		$db = $this->getDB();
 		while ($row = $db->fetchObject($res)) {
 
 			if (++ $count > $limit) {
@@ -262,7 +262,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryRevisions.php 19434 2007-01-18 02:04:11Z brion $';
+		return __CLASS__ . ': $Id: ApiQueryRevisions.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/api/ApiQueryUserContributions.php /srv/web/fp014/source/includes/api/ApiQueryUserContributions.php
--- includes/api/ApiQueryUserContributions.php	2007-02-02 01:47:09.000000000 +0000
+++ /srv/web/fp014/source/includes/api/ApiQueryUserContributions.php	2007-02-01 01:03:18.000000000 +0000
@@ -44,7 +44,7 @@
 		extract($this->extractRequestParams());
 
 		//Get a database instance
-		$db = & $this->getDB();
+		$db = $this->getDB();
 
 		if (is_null($user))
 			$this->dieUsage("User parameter may not be empty", 'param_user');
@@ -169,7 +169,7 @@
 	}
 
 	public function getVersion() {
-		return __CLASS__ . ': $Id: ApiQueryUserContributions.php 17952 2006-11-27 08:36:57Z nickj $';
+		return __CLASS__ . ': $Id: ApiQueryUserContributions.php 19598 2007-01-22 23:50:42Z nickj $';
 	}
 }
 ?>
diff -NaurB -x .svn includes/Article.php /srv/web/fp014/source/includes/Article.php
--- includes/Article.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Article.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * File for articles
- * @package MediaWiki
  */
 
 /**
@@ -11,7 +10,6 @@
  * Note: edit user interface and cache support functions have been
  * moved to separate EditPage and HTMLFileCache classes.
  *
- * @package MediaWiki
  */
 class Article {
 	/**@{{
@@ -43,7 +41,7 @@
 	 * @param $title Reference to a Title object.
 	 * @param $oldId Integer revision ID, null to fetch from request, zero for current
 	 */
-	function Article( &$title, $oldId = null ) {
+	function __construct( &$title, $oldId = null ) {
 		$this->mTitle =& $title;
 		$this->mOldId = $oldId;
 		$this->clear();
@@ -57,14 +55,14 @@
 	function setRedirectedFrom( $from ) {
 		$this->mRedirectedFrom = $from;
 	}
-	
+
 	/**
 	 * @return mixed false, Title of in-wiki target, or string with URL
 	 */
 	function followRedirect() {
 		$text = $this->getContent();
 		$rt = Title::newFromRedirect( $text );
-		
+
 		# process if title object is valid and not special:userlogout
 		if( $rt ) {
 			if( $rt->getInterwiki() != '' ) {
@@ -73,7 +71,7 @@
 					//
 					// This can be hard to reverse and may produce loops,
 					// so they may be disabled in the site configuration.
-					
+
 					$source = $this->mTitle->getFullURL( 'redirect=no' );
 					return $rt->getFullURL( 'rdfrom=' . urlencode( $source ) );
 				}
@@ -84,7 +82,7 @@
 					// the rest of the page we're on.
 					//
 					// This can be hard to reverse, so they may be disabled.
-					
+
 					if( $rt->isSpecial( 'Userlogout' ) ) {
 						// rolleyes
 					} else {
@@ -94,7 +92,7 @@
 				return $rt;
 			}
 		}
-		
+
 		// No or invalid redirect
 		return false;
 	}
@@ -151,11 +149,14 @@
 				$ret = wfMsgWeirdKey ( $this->mTitle->getText() ) ;
 			} else {
 				$ret = wfMsg( $wgUser->isLoggedIn() ? 'noarticletext' : 'noarticletextanon' );
+				wfRunHooks('ArticleAddContent', array(&$this, $action, &$ret) );
 			}
 
+			wfProfileOut( __METHOD__ );
 			return "<div class='noarticletext'>$ret</div>";
 		} else {
 			$this->loadContent();
+			wfRunHooks('ArticleAddContent', array(&$this, $action, &$this->mContent) );
 			wfProfileOut( __METHOD__ );
 			return $this->mContent;
 		}
@@ -247,7 +248,7 @@
 	 * @param array    $conditions
 	 * @private
 	 */
-	function pageData( &$dbr, $conditions ) {
+	function pageData( $dbr, $conditions ) {
 		$fields = array(
 				'page_id',
 				'page_namespace',
@@ -273,7 +274,7 @@
 	 * @param Database $dbr
 	 * @param Title $title
 	 */
-	function pageDataFromTitle( &$dbr, $title ) {
+	function pageDataFromTitle( $dbr, $title ) {
 		return $this->pageData( $dbr, array(
 			'page_namespace' => $title->getNamespace(),
 			'page_title'     => $title->getDBkey() ) );
@@ -283,7 +284,7 @@
 	 * @param Database $dbr
 	 * @param int $id
 	 */
-	function pageDataFromId( &$dbr, $id ) {
+	function pageDataFromId( $dbr, $id ) {
 		return $this->pageData( $dbr, array( 'page_id' => $id ) );
 	}
 
@@ -296,17 +297,18 @@
 	 */
 	function loadPageData( $data = 'fromdb' ) {
 		if ( $data === 'fromdb' ) {
-			$dbr =& $this->getDB();
+			$dbr = $this->getDB();
 			$data = $this->pageDataFromId( $dbr, $this->getId() );
 		}
-			
+
 		$lc =& LinkCache::singleton();
 		if ( $data ) {
 			$lc->addGoodLinkObj( $data->page_id, $this->mTitle );
 
 			$this->mTitle->mArticleID = $data->page_id;
+
+			# Old-fashioned restrictions.
 			$this->mTitle->loadRestrictions( $data->page_restrictions );
-			$this->mTitle->mRestrictionsLoaded = true;
 
 			$this->mCounter     = $data->page_counter;
 			$this->mTouched     = wfTimestamp( TS_MW, $data->page_touched );
@@ -333,7 +335,7 @@
 			return $this->mContent;
 		}
 
-		$dbr =& $this->getDB();
+		$dbr = $this->getDB();
 
 		# Pre-fill content with error message so that if something
 		# fails we'll have something telling us what we intended.
@@ -405,9 +407,8 @@
 	 *
 	 * @return Database
 	 */
-	function &getDB() {
-		$ret =& wfGetDB( DB_MASTER );
-		return $ret;
+	function getDB() {
+		return wfGetDB( DB_MASTER );
 	}
 
 	/**
@@ -455,7 +456,7 @@
 			if ( $id == 0 ) {
 				$this->mCounter = 0;
 			} else {
-				$dbr =& wfGetDB( DB_SLAVE );
+				$dbr = wfGetDB( DB_SLAVE );
 				$this->mCounter = $dbr->selectField( 'page', 'page_counter', array( 'page_id' => $id ),
 					'Article::getCount', $this->getSelectOptions() );
 			}
@@ -471,12 +472,12 @@
 	 * @return bool
 	 */
 	function isCountable( $text ) {
-		global $wgUseCommaCount, $wgContentNamespaces;
+		global $wgUseCommaCount;
 
 		$token = $wgUseCommaCount ? ',' : '[[';
 		return
-			array_search( $this->mTitle->getNamespace(), $wgContentNamespaces ) !== false
-			&& ! $this->isRedirect( $text )
+			$this->mTitle->isContentPage()
+			&& !$this->isRedirect( $text )
 			&& in_string( $token, $text );
 	}
 
@@ -573,7 +574,7 @@
 		# XXX: this is expensive; cache this info somewhere.
 
 		$contribs = array();
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$revTable = $dbr->tableName( 'revision' );
 		$userTable = $dbr->tableName( 'user' );
 		$user = $this->getUser();
@@ -613,7 +614,7 @@
 
 		$parserCache =& ParserCache::singleton();
 		$ns = $this->mTitle->getNamespace(); # shortcut
-		
+
 		# Get variables from query string
 		$oldid = $this->getOldID();
 
@@ -627,16 +628,21 @@
 		$diff = $wgRequest->getVal( 'diff' );
 		$rcid = $wgRequest->getVal( 'rcid' );
 		$rdfrom = $wgRequest->getVal( 'rdfrom' );
+		$diffOnly = $wgRequest->getBool( 'diffonly', $wgUser->getOption( 'diffonly' ) );
 
 		$wgOut->setArticleFlag( true );
-		if ( isset( $wgNamespaceRobotPolicies[$ns] ) ) {
+
+		# Discourage indexing of printable versions, but encourage following
+		if( $wgOut->isPrintable() ) {
+			$policy = 'noindex,follow';
+		} elseif( isset( $wgNamespaceRobotPolicies[$ns] ) ) {
+			# Honour customised robot policies for this namespace
 			$policy = $wgNamespaceRobotPolicies[$ns];
 		} else {
-			# The default policy. Dev note: make sure you change the documentation
-			# in DefaultSettings.php before changing it.
+			# Default to encourage indexing and following links
 			$policy = 'index,follow';
 		}
-		$wgOut->setRobotpolicy( $policy );
+		$wgOut->setRobotPolicy( $policy );
 
 		# If we got diff and oldid in the query, we want to see a
 		# diff page instead of the article.
@@ -647,8 +653,8 @@
 			$de = new DifferenceEngine( $this->mTitle, $oldid, $diff, $rcid );
 			// DifferenceEngine directly fetched the revision:
 			$this->mRevIdFetched = $de->mNewid;
-			$de->showDiffPage();
-			
+			$de->showDiffPage( $diffOnly );
+
 			// Needed to get the page's current revision
 			$this->loadPageData();
 			if( $diff == 0 || $diff == $this->mLatest ) {
@@ -658,7 +664,7 @@
 			wfProfileOut( __METHOD__ );
 			return;
 		}
-		
+
 		if ( empty( $oldid ) && $this->checkTouched() ) {
 			$wgOut->setETag($parserCache->getETag($this, $wgUser));
 
@@ -713,7 +719,7 @@
 				$wasRedirected = true;
 			}
 		}
-		
+
 		$outputDone = false;
 		if ( $pcache ) {
 			if ( $wgOut->tryParserCache( $this, $wgUser ) ) {
@@ -772,7 +778,13 @@
 			$wgOut->setRevisionId( $this->getRevIdFetched() );
 			# wrap user css and user js in pre and don't parse
 			# XXX: use $this->mTitle->usCssJsSubpage() when php is fixed/ a workaround is found
-			if (
+
+			/** Added by egon@wikia.com.
+			 *  This hook is used in extension SiteWideMessages.
+			 */
+			if ( ! wfRunHooks('ArticleLoadNoCache', array( &$this, $text) ) ){
+				// do nothing
+			} else if (
 				$ns == NS_USER &&
 				preg_match('/\\/[\\w]+\\.(css|js)$/', $this->mTitle->getDBkey())
 			) {
@@ -795,7 +807,7 @@
 				$wgOut->addParserOutputNoText( $parseout );
 			} else if ( $pcache ) {
 				# Display content and save to parser cache
-				$wgOut->addPrimaryWikiText( $text, $this );
+				$this->outputWikiText( $text );
 			} else {
 				# Display content, don't attempt to save to parser cache
 				# Don't show section-edit links on old revisions... this way lies madness.
@@ -803,7 +815,16 @@
 					$oldEditSectionSetting = $wgOut->parserOptions()->setEditSection( false );
 				}
 				# Display content and don't save to parser cache
-				$wgOut->addPrimaryWikiText( $text, $this, false );
+				# With timing hack -- TS 2006-07-26
+				$time = -wfTime();
+				$this->outputWikiText( $text, false );
+				$time += wfTime();
+
+				# Timing hack
+				if ( $time > 3 ) {
+					wfDebugLog( 'slow-parse', sprintf( "%-5.2f %s", $time,
+						$this->mTitle->getPrefixedDBkey()));
+				}
 
 				if( !$this->isCurrent() ) {
 					$wgOut->parserOptions()->setEditSection( $oldEditSectionSetting );
@@ -827,8 +849,9 @@
 		if ( $wgUseRCPatrol && !is_null( $rcid ) && $rcid != 0 && $wgUser->isAllowed( 'patrol' ) ) {
 			$wgOut->addHTML(
 				"<div class='patrollink'>" .
-					wfMsg ( 'markaspatrolledlink',
-					$sk->makeKnownLinkObj( $this->mTitle, wfMsg('markaspatrolledtext'), "action=markpatrolled&rcid=$rcid" )
+					wfMsgHtml( 'markaspatrolledlink',
+					$sk->makeKnownLinkObj( $this->mTitle, wfMsgHtml('markaspatrolledtext'),
+						"action=markpatrolled&rcid=$rcid" )
 			 		) .
 				'</div>'
 			 );
@@ -845,7 +868,7 @@
 	function addTrackbacks() {
 		global $wgOut, $wgUser;
 
-		$dbr =& wfGetDB(DB_SLAVE);
+		$dbr = wfGetDB(DB_SLAVE);
 		$tbs = $dbr->select(
 				/* FROM   */ 'trackbacks',
 				/* SELECT */ array('tb_id', 'tb_title', 'tb_url', 'tb_ex', 'tb_name'),
@@ -891,7 +914,7 @@
 			return;
 		}
 
-		$db =& wfGetDB(DB_MASTER);
+		$db = wfGetDB(DB_MASTER);
 		$db->delete('trackbacks', array('tb_id' => $wgRequest->getInt('tbid')));
 		$wgTitle->invalidateCache();
 		$wgOut->addWikiText(wfMsg('trackbackdeleteok'));
@@ -910,7 +933,7 @@
 	function purge() {
 		global $wgUser, $wgRequest, $wgOut;
 
-		if ( $wgUser->isLoggedIn() || $wgRequest->wasPosted() ) {
+		if ( $wgUser->isAllowed( 'purge' ) || $wgRequest->wasPosted() ) {
 			if( wfRunHooks( 'ArticlePurge', array( &$this ) ) ) {
 				$this->doPurge();
 			}
@@ -928,7 +951,7 @@
 			$wgOut->addHTML( $msg );
 		}
 	}
-	
+
 	/**
 	 * Perform the actions of a page purging
 	 */
@@ -957,11 +980,10 @@
 	 * Best if all done inside a transaction.
 	 *
 	 * @param Database $dbw
-	 * @param string   $restrictions
 	 * @return int     The newly created page_id key
 	 * @private
 	 */
-	function insertOn( &$dbw, $restrictions = '' ) {
+	function insertOn( $dbw ) {
 		wfProfileIn( __METHOD__ );
 
 		$page_id = $dbw->nextSequenceValue( 'page_page_id_seq' );
@@ -970,7 +992,7 @@
 			'page_namespace'    => $this->mTitle->getNamespace(),
 			'page_title'        => $this->mTitle->getDBkey(),
 			'page_counter'      => 0,
-			'page_restrictions' => $restrictions,
+			'page_restrictions' => '',
 			'page_is_redirect'  => 0, # Will set this shortly...
 			'page_is_new'       => 1,
 			'page_random'       => wfRandom(),
@@ -996,7 +1018,7 @@
 	 *                          when different from the currently set value.
 	 *                          Giving 0 indicates the new page flag should
 	 *                          be set on.
-	 * @param bool $lastRevIsRedirect If given, will optimize adding and 
+	 * @param bool $lastRevIsRedirect If given, will optimize adding and
 	 * 							removing rows in redirect table.
 	 * @return bool true on success, false on failure
 	 * @private
@@ -1006,7 +1028,7 @@
 
 		$text = $revision->getText();
 		$rt = Title::newFromRedirect( $text );
-		
+
 		$conditions = array( 'page_id' => $this->getId() );
 		if( !is_null( $lastRevision ) ) {
 			# An extra check against threads stepping on each other
@@ -1028,20 +1050,20 @@
 
 		if ($result) {
 			// FIXME: Should the result from updateRedirectOn() be returned instead?
-			$this->updateRedirectOn( $dbw, $rt, $lastRevIsRedirect ); 
+			$this->updateRedirectOn( $dbw, $rt, $lastRevIsRedirect );
 		}
-		
+
 		wfProfileOut( __METHOD__ );
 		return $result;
 	}
 
 	/**
-	 * Add row to the redirect table if this is a redirect, remove otherwise. 
+	 * Add row to the redirect table if this is a redirect, remove otherwise.
 	 *
 	 * @param Database $dbw
 	 * @param $redirectTitle a title object pointing to the redirect target,
-	 * 							or NULL if this is not a redirect  
-	 * @param bool $lastRevIsRedirect If given, will optimize adding and 
+	 * 							or NULL if this is not a redirect
+	 * @param bool $lastRevIsRedirect If given, will optimize adding and
 	 * 							removing rows in redirect table.
 	 * @return bool true on success, false on failure
 	 * @private
@@ -1067,7 +1089,7 @@
 
 				$dbw->replace( 'redirect', array( 'rd_from' ), $set, __METHOD__ );
 			} else {
-				// This is not a redirect, remove row from redirect table 
+				// This is not a redirect, remove row from redirect table
 				$where = array( 'rd_from' => $this->getId() );
 				$dbw->delete( 'redirect', $where, __METHOD__);
 			}
@@ -1075,7 +1097,7 @@
 			wfProfileOut( __METHOD__ );
 			return ( $dbw->affectedRows() != 0 );
 		}
-		
+
 		return true;
 	}
 
@@ -1119,14 +1141,14 @@
 	 */
 	function replaceSection($section, $text, $summary = '', $edittime = NULL) {
 		wfProfileIn( __METHOD__ );
-		
+
 		if( $section == '' ) {
 			// Whole-page edit; let the text through unmolested.
 		} else {
 			if( is_null( $edittime ) ) {
 				$rev = Revision::newFromTitle( $this->mTitle );
 			} else {
-				$dbw =& wfGetDB( DB_MASTER );
+				$dbw = wfGetDB( DB_MASTER );
 				$rev = Revision::loadFromTimestamp( $dbw, $this->mTitle, $edittime );
 			}
 			if( is_null( $rev ) ) {
@@ -1166,10 +1188,10 @@
 		if ( $comment && $summary != "" ) {
 			$text = "== {$summary} ==\n\n".$text;
 		}
-		
+
 		$this->doEdit( $text, $summary, $flags );
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		if ($watchthis) {
 			if (!$this->mTitle->userIsWatching()) {
 				$dbw->begin();
@@ -1196,7 +1218,7 @@
 
 		$good = $this->doEdit( $text, $summary, $flags );
 		if ( $good ) {
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			if ($watchthis) {
 				if (!$this->mTitle->userIsWatching()) {
 					$dbw->begin();
@@ -1219,7 +1241,7 @@
 	/**
 	 * Article::doEdit()
 	 *
-	 * Change an existing article or create a new article. Updates RC and all necessary caches, 
+	 * Change an existing article or create a new article. Updates RC and all necessary caches,
 	 * optionally via the deferred update array.
 	 *
 	 * $wgUser must be set before calling this function.
@@ -1241,9 +1263,9 @@
 	 *          Defer some of the updates until the end of index.php
 	 *      EDIT_AUTOSUMMARY
 	 *          Fill in blank summaries with generated text where possible
-	 * 
-	 * If neither EDIT_NEW nor EDIT_UPDATE is specified, the status of the article will be detected. 
-	 * If EDIT_UPDATE is specified and the article doesn't exist, the function will return false. If 
+	 *
+	 * If neither EDIT_NEW nor EDIT_UPDATE is specified, the status of the article will be detected.
+	 * If EDIT_UPDATE is specified and the article doesn't exist, the function will return false. If
 	 * EDIT_NEW is specified and the article does exist, a duplicate key error will cause an exception
 	 * to be thrown from the Database. These two conditions are also possible with auto-detection due
 	 * to MediaWiki's performance-optimised locking strategy.
@@ -1267,7 +1289,7 @@
 
 		if( !wfRunHooks( 'ArticleSave', array( &$this, &$wgUser, &$text,
 			&$summary, $flags & EDIT_MINOR,
-			null, null, &$flags ) ) ) 
+			null, null, &$flags ) ) )
 		{
 			wfDebug( __METHOD__ . ": ArticleSave hook aborted save!\n" );
 			wfProfileOut( __METHOD__ );
@@ -1288,9 +1310,9 @@
 		$text = $this->preSaveTransform( $text );
 		$newsize = strlen( $text );
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$now = wfTimestampNow();
-		
+
 		if ( $flags & EDIT_UPDATE ) {
 			# Update article, but only if changed.
 
@@ -1316,7 +1338,7 @@
 					wfProfileOut( __METHOD__ );
 					return false;
 				}
-				
+
 				$revision = new Revision( array(
 					'page'       => $this->getId(),
 					'comment'    => $summary,
@@ -1340,10 +1362,11 @@
 						$rcid = RecentChange::notifyEdit( $now, $this->mTitle, $isminor, $wgUser, $summary,
 							$lastRevision, $this->getTimestamp(), $bot, '', $oldsize, $newsize,
 							$revisionId );
-							
+
 						# Mark as patrolled if the user can do so
-						if( $wgUser->isAllowed( 'autopatrol' ) ) {
+						if( $GLOBALS['wgUseRCPatrol'] && $wgUser->isAllowed( 'autopatrol' ) ) {
 							RecentChange::markPatrolled( $rcid );
+							PatrolLog::record( $rcid, true );
 						}
 					}
 					$wgUser->incEditCount();
@@ -1362,19 +1385,19 @@
 			}
 
 			if ( $good ) {
-				# Invalidate cache of this article and all pages using this article 
+				# Invalidate cache of this article and all pages using this article
 				# as a template. Partly deferred.
 				Article::onArticleEdit( $this->mTitle );
-				
+
 				# Update links tables, site stats, etc.
 				$changed = ( strcmp( $oldtext, $text ) != 0 );
 				$this->editUpdates( $text, $summary, $isminor, $now, $revisionId, $changed );
 			}
 		} else {
 			# Create new article
-			
+
 			# Set statistics members
-			# We work out if it's countable after PST to avoid counter drift 
+			# We work out if it's countable after PST to avoid counter drift
 			# when articles are created with {{subst:}}
 			$this->mGoodAdjustment = (int)$this->isCountable( $text );
 			$this->mTotalAdjustment = 1;
@@ -1403,8 +1426,9 @@
 				$rcid = RecentChange::notifyNew( $now, $this->mTitle, $isminor, $wgUser, $summary, $bot,
 				  '', strlen( $text ), $revisionId );
 				# Mark as patrolled if the user can
-				if( $wgUser->isAllowed( 'autopatrol' ) ) {
+				if( $GLOBALS['wgUseRCPatrol'] && $wgUser->isAllowed( 'autopatrol' ) ) {
 					RecentChange::markPatrolled( $rcid );
+					PatrolLog::record( $rcid, true );
 				}
 			}
 			$wgUser->incEditCount();
@@ -1429,7 +1453,7 @@
 			array( &$this, &$wgUser, $text,
 			$summary, $flags & EDIT_MINOR,
 			null, null, &$flags ) );
-		
+
 		wfProfileOut( __METHOD__ );
 		return $good;
 	}
@@ -1457,7 +1481,7 @@
 		}
 		$wgOut->redirect( $this->mTitle->getFullURL( $query ) . $sectionAnchor );
 	}
-		
+
 	/**
 	 * Mark this particular edit as patrolled
 	 */
@@ -1470,25 +1494,25 @@
 			$wgOut->errorPage( 'rcpatroldisabled', 'rcpatroldisabledtext' );
 			return;
 		}
-		
+
 		# Check permissions
 		if( !$wgUser->isAllowed( 'patrol' ) ) {
 			$wgOut->permissionRequired( 'patrol' );
 			return;
 		}
-		
+
 		# If we haven't been given an rc_id value, we can't do anything
 		$rcid = $wgRequest->getVal( 'rcid' );
 		if( !$rcid ) {
 			$wgOut->errorPage( 'markedaspatrollederror', 'markedaspatrollederrortext' );
 			return;
 		}
-		
+
 		# Handle the 'MarkPatrolled' hook
 		if( !wfRunHooks( 'MarkPatrolled', array( $rcid, &$wgUser, false ) ) ) {
 			return;
 		}
-		
+
 		$return = SpecialPage::getTitleFor( 'Recentchanges' );
 		# If it's left up to us, check that the user is allowed to patrol this edit
 		# If the user has the "autopatrol" right, then we'll assume there are no
@@ -1507,11 +1531,12 @@
 				return;
 			}
 		}
-		
+
 		# Mark the edit as patrolled
 		RecentChange::markPatrolled( $rcid );
+		PatrolLog::record( $rcid );
 		wfRunHooks( 'MarkPatrolledComplete', array( &$rcid, &$wgUser, false ) );
-		
+
 		# Inform the user
 		$wgOut->setPageTitle( wfMsg( 'markedaspatrolled' ) );
 		$wgOut->addWikiText( wfMsgNoTrans( 'markedaspatrolledtext' ) );
@@ -1534,7 +1559,7 @@
 			$wgOut->readOnlyPage();
 			return;
 		}
-		
+
 		if( $this->doWatch() ) {
 			$wgOut->setPagetitle( wfMsg( 'addedwatch' ) );
 			$wgOut->setRobotpolicy( 'noindex,nofollow' );
@@ -1546,7 +1571,7 @@
 
 		$wgOut->returnToMain( true, $this->mTitle->getPrefixedText() );
 	}
-	
+
 	/**
 	 * Add this page to $wgUser's watchlist
 	 * @return bool true on successful watch operation
@@ -1556,13 +1581,13 @@
 		if( $wgUser->isAnon() ) {
 			return false;
 		}
-		
+
 		if (wfRunHooks('WatchArticle', array(&$wgUser, &$this))) {
 			$wgUser->addWatch( $this->mTitle );
 
 			return wfRunHooks('WatchArticleComplete', array(&$wgUser, &$this));
 		}
-		
+
 		return false;
 	}
 
@@ -1581,7 +1606,7 @@
 			$wgOut->readOnlyPage();
 			return;
 		}
-		
+
 		if( $this->doUnwatch() ) {
 			$wgOut->setPagetitle( wfMsg( 'removedwatch' ) );
 			$wgOut->setRobotpolicy( 'noindex,nofollow' );
@@ -1593,7 +1618,7 @@
 
 		$wgOut->returnToMain( true, $this->mTitle->getPrefixedText() );
 	}
-	
+
 	/**
 	 * Stop watching a page
 	 * @return bool true on successful unwatch
@@ -1609,7 +1634,7 @@
 
 			return wfRunHooks('UnwatchArticleComplete', array(&$wgUser, &$this));
 		}
-		
+
 		return false;
 	}
 
@@ -1618,7 +1643,7 @@
 	 */
 	function protect() {
 		$form = new ProtectionForm( $this );
-		$form->show();
+		$form->execute();
 	}
 
 	/**
@@ -1635,14 +1660,21 @@
 	 * @param string $reason
 	 * @return bool true on success
 	 */
-	function updateRestrictions( $limit = array(), $reason = '' ) {
+	function updateRestrictions( $limit = array(), $reason = '', $cascade = 0, $expiry = null ) {
 		global $wgUser, $wgRestrictionTypes, $wgContLang;
-		
+
 		$id = $this->mTitle->getArticleID();
 		if( !$wgUser->isAllowed( 'protect' ) || wfReadOnly() || $id == 0 ) {
 			return false;
 		}
 
+		if (!$cascade) {
+			$cascade = false;
+		}
+
+		// Take this opportunity to purge out expired restrictions
+		Title::purgeExpiredRestrictions();
+
 		# FIXME: Same limitations as described in ProtectionForm.php (line 37);
 		# we expect a single selection, but the schema allows otherwise.
 		$current = array();
@@ -1651,18 +1683,29 @@
 
 		$current = Article::flattenRestrictions( $current );
 		$updated = Article::flattenRestrictions( $limit );
-		
+
 		$changed = ( $current != $updated );
+		$changed = $changed || ($this->mTitle->areRestrictionsCascading() != $cascade);
+		$changed = $changed || ($this->mTitle->mRestrictionsExpiry != $expiry);
 		$protect = ( $updated != '' );
-		
+
 		# If nothing's changed, do nothing
 		if( $changed ) {
 			if( wfRunHooks( 'ArticleProtect', array( &$this, &$wgUser, $limit, $reason ) ) ) {
 
-				$dbw =& wfGetDB( DB_MASTER );
-				
+				$dbw = wfGetDB( DB_MASTER );
+
+				$encodedExpiry = Block::encodeExpiry($expiry, $dbw );
+
+				$expiry_description = '';
+
+				if ( $encodedExpiry != 'infinity' ) {
+					$expiry_description = ' (' . wfMsgForContent( 'protect-expiring', $wgContLang->timeanddate( $expiry ) ).')';
+				}
+
 				# Prepare a null revision to be added to the history
 				$comment = $wgContLang->ucfirst( wfMsgForContent( $protect ? 'protectedarticle' : 'unprotectedarticle', $this->mTitle->getPrefixedText() ) );
+
 				if( $reason )
 					$comment .= ": $reason";
 				if( $protect )
@@ -1667,32 +1710,55 @@
 					$comment .= ": $reason";
 				if( $protect )
 					$comment .= " [$updated]";
+				if ( $expiry_description && $protect )
+					$comment .= "$expiry_description";
+
 				$nullRevision = Revision::newNullRevision( $dbw, $id, $comment, true );
 				$nullRevId = $nullRevision->insertOn( $dbw );
-			
+
+				# Update restrictions table
+				foreach( $limit as $action => $restrictions ) {
+					if ($restrictions != '' ) {
+						$dbw->replace( 'page_restrictions', array( 'pr_pagetype'),
+							array( 'pr_page' => $id, 'pr_type' => $action
+								, 'pr_level' => $restrictions, 'pr_cascade' => $cascade ? 1 : 0
+								, 'pr_expiry' => $encodedExpiry ), __METHOD__  );
+					} else {
+						$dbw->delete( 'page_restrictions', array( 'pr_page' => $id,
+							'pr_type' => $action ), __METHOD__ );
+					}
+				}
+
 				# Update page record
 				$dbw->update( 'page',
 					array( /* SET */
 						'page_touched' => $dbw->timestamp(),
-						'page_restrictions' => $updated,
+						'page_restrictions' => '',
 						'page_latest' => $nullRevId
 					), array( /* WHERE */
 						'page_id' => $id
 					), 'Article::protect'
 				);
 				wfRunHooks( 'ArticleProtectComplete', array( &$this, &$wgUser, $limit, $reason ) );
-	
+
 				# Update the protection log
 				$log = new LogPage( 'protect' );
+
+				$cascade_description = '';
+
+				if ($cascade) {
+					$cascade_description = ' ['.wfMsg('protect-summary-cascade').']';
+				}
+
 				if( $protect ) {
-					$log->addEntry( 'protect', $this->mTitle, trim( $reason . " [$updated]" ) );
+					$log->addEntry( 'protect', $this->mTitle, trim( $reason . " [$updated]$cascade_description$expiry_description" ) );
 				} else {
 					$log->addEntry( 'unprotect', $this->mTitle, $reason );
 				}
-				
+
 			} # End hook
 		} # End "changed" check
-		
+
 		return true;
 	}
 
@@ -1745,9 +1811,9 @@
 		}
 
 		$wgOut->setPagetitle( wfMsg( 'confirmdelete' ) );
-		
+
 		# Better double-check that it hasn't been deleted yet!
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$conds = $this->mTitle->pageCond();
 		$latest = $dbw->selectField( 'page', 'page_latest', $conds, __METHOD__ );
 		if ( $latest === false ) {
@@ -1769,7 +1835,7 @@
 		# and insert a warning if it does
 		$maxRevisions = 20;
 		$authors = $this->getLastNAuthors( $maxRevisions, $latest );
-		
+
 		if( count( $authors ) > 1 && !$confirm ) {
 			$skin=$wgUser->getSkin();
 			$wgOut->addHTML( '<strong>' . wfMsg( 'historywarning' ) . ' ' . $skin->historyLink() . '</strong>' );
@@ -1849,7 +1915,7 @@
 		// First try the slave
 		// If that doesn't have the latest revision, try the master
 		$continue = 2;
-		$db =& wfGetDB( DB_SLAVE );
+		$db = wfGetDB( DB_SLAVE );
 		do {
 			$res = $db->select( array( 'page', 'revision' ),
 				array( 'rev_id', 'rev_user_text' ),
@@ -1868,7 +1934,7 @@
 			}
 			$row = $db->fetchObject( $res );
 			if ( $continue == 2 && $revLatest && $row->rev_id != $revLatest ) {
-				$db =& wfGetDB( DB_MASTER );
+				$db = wfGetDB( DB_MASTER );
 				$continue--;
 			} else {
 				$continue = 0;
@@ -1882,7 +1948,7 @@
 		wfProfileOut( __METHOD__ );
 		return $authors;
 	}
-	
+
 	/**
 	 * Output deletion confirmation dialog
 	 */
@@ -1903,8 +1969,13 @@
 		$token = htmlspecialchars( $wgUser->editToken() );
 		$watch = Xml::checkLabel( wfMsg( 'watchthis' ), 'wpWatch', 'wpWatch', $wgUser->getBoolOption( 'watchdeletion' ) || $this->mTitle->userIsWatching(), array( 'tabindex' => '2' ) );
 
+		/** Added by inez@wikia.com
+		 *  This hook is used in ConfirmEdit (Captcha and FancyCaptcha) extension
+		 */
+		$wgOut->addHTML( "
+<form id='deleteconfirm' method='post' action=\"{$formaction}\">");
+		wfRunHooks( 'ArticleDeleteForm', array( &$wgOut ) ) ;
 		$wgOut->addHTML( "
-<form id='deleteconfirm' method='post' action=\"{$formaction}\">
 	<table border='0'>
 		<tr>
 			<td align='right'>
@@ -1969,7 +2040,7 @@
 
 		wfDebug( __METHOD__."\n" );
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$ns = $this->mTitle->getNamespace();
 		$t = $this->mTitle->getDBkey();
 		$id = $this->mTitle->getArticleID();
@@ -2010,6 +2081,9 @@
 			), __METHOD__
 		);
 
+		# Delete restrictions for it
+		$dbw->delete( 'page_restrictions', array ( 'pr_page' => $id ), __METHOD__ );
+
 		# Now that it's safely backed up, delete it
 		$dbw->delete( 'page', array( 'page_id' => $id ), __METHOD__);
 
@@ -2078,7 +2152,7 @@
 			$wgOut->addWikiText( wfMsg( 'sessionfailure' ) );
 			return;
 		}
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		# Enhanced rollback, marks edits rc_bot=1
 		$bot = $wgRequest->getBool( 'bot' );
@@ -2189,7 +2263,7 @@
 	 * Do standard deferred updates after page edit.
 	 * Update links tables, site stats, search index and message cache.
 	 * Every 1000th edit, prune the recent changes table.
-	 * 
+	 *
 	 * @private
 	 * @param $text New text of the article
 	 * @param $summary Edit summary
@@ -2222,7 +2296,7 @@
 				# Periodically flush old entries from the recentchanges table.
 				global $wgRCMaxAge;
 
-				$dbw =& wfGetDB( DB_MASTER );
+				$dbw = wfGetDB( DB_MASTER );
 				$cutoff = $dbw->timestamp( time() - $wgRCMaxAge );
 				$recentchanges = $dbw->tableName( 'recentchanges' );
 				$sql = "DELETE FROM $recentchanges WHERE rc_timestamp < '{$cutoff}'";
@@ -2269,7 +2343,7 @@
 
 		wfProfileOut( __METHOD__ );
 	}
-	
+
 	/**
 	 * Perform article updates on a special page creation.
 	 *
@@ -2299,8 +2373,8 @@
 		global $wgLang, $wgOut, $wgUser;
 
 		if ( !wfRunHooks( 'DisplayOldSubtitle', array(&$this, &$oldid) ) ) {
-				return; 
-		}       
+				return;
+		}
 
 		$revision = Revision::newFromId( $oldid );
 
@@ -2326,10 +2400,10 @@
 		$nextdiff = $current
 			? wfMsg( 'diff' )
 			: $sk->makeKnownLinkObj( $this->mTitle, wfMsg( 'diff' ), 'diff=next&oldid='.$oldid );
-		
+
 		$userlinks = $sk->userLink( $revision->getUser(), $revision->getUserText() )
 						. $sk->userToolLinks( $revision->getUser(), $revision->getUserText() );
-		
+
 		$r = "\n\t\t\t\t<div id=\"mw-revision-info\">" . wfMsg( 'revision-info', $td, $userlinks ) . "</div>\n" .
 		     "\n\t\t\t\t<div id=\"mw-revision-nav\">" . wfMsg( 'revision-nav', $prevdiff, $prevlink, $lnk, $curdiff, $nextlink, $nextdiff ) . "</div>\n\t\t\t";
 		$wgOut->setSubtitle( $r );
@@ -2446,7 +2520,7 @@
 	function quickEdit( $text, $comment = '', $minor = 0 ) {
 		wfProfileIn( __METHOD__ );
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->begin();
 		$revision = new Revision( array(
 			'page'       => $this->getId(),
@@ -2471,7 +2545,7 @@
 		$id = intval( $id );
 		global $wgHitcounterUpdateFreq, $wgDBtype;
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$pageTable = $dbw->tableName( 'page' );
 		$hitcounterTable = $dbw->tableName( 'hitcounter' );
 		$acchitsTable = $dbw->tableName( 'acchits' );
@@ -2555,7 +2629,7 @@
 
 		$title->touchLinks();
 		$title->purgeSquid();
-		
+
 		# File cache
 		if ( $wgUseFileCache ) {
 			$cm = new HTMLFileCache( $title );
@@ -2617,7 +2691,7 @@
 				$wgOut->addHTML(wfMsg( $wgUser->isLoggedIn() ? 'noarticletext' : 'noarticletextanon' ) );
 			}
 		} else {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$wl_clause = array(
 				'wl_title'     => $page->getDBkey(),
 				'wl_namespace' => $page->getNamespace() );
@@ -2659,7 +2733,7 @@
 			return false;
 		}
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$rev_clause = array( 'rev_page' => $id );
 
@@ -2693,7 +2767,7 @@
 			return array();
 		}
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( array( 'templatelinks' ),
 			array( 'tl_namespace', 'tl_title' ),
 			array( 'tl_from' => $id ),
@@ -2708,7 +2782,7 @@
 		$dbr->freeResult( $res );
 		return $result;
 	}
-	
+
 	/**
 	 * Return an auto-generated summary if the text provided is a redirect.
 	 *
@@ -2785,6 +2859,84 @@
 
 		return $summary;
 	}
+
+	/**
+	 * Add the primary page-view wikitext to the output buffer
+	 * Saves the text into the parser cache if possible.
+	 * Updates templatelinks if it is out of date.
+	 *
+	 * @param string  $text
+	 * @param bool    $cache
+	 */
+	public function outputWikiText( $text, $cache = true ) {
+		global $wgParser, $wgUser, $wgOut;
+
+		$popts = $wgOut->parserOptions();
+		$popts->setTidy(true);
+		$parserOutput = $wgParser->parse( $text, $this->mTitle,
+			$popts, true, true, $this->getRevIdFetched() );
+		$popts->setTidy(false);
+		if ( $cache && $this && $parserOutput->getCacheTime() != -1 ) {
+			$parserCache =& ParserCache::singleton();
+			$parserCache->save( $parserOutput, $this, $wgUser );
+		}
+
+		if ( !wfReadOnly() && $this->mTitle->areRestrictionsCascading() ) {
+			// templatelinks table may have become out of sync,
+			// especially if using variable-based transclusions.
+			// For paranoia, check if things have changed and if
+			// so apply updates to the database. This will ensure
+			// that cascaded protections apply as soon as the changes
+			// are visible.
+
+			# Get templates from templatelinks
+			$id = $this->mTitle->getArticleID();
+
+			$tlTemplates = array();
+
+			$dbr = wfGetDB( DB_SLAVE );
+			$res = $dbr->select( array( 'templatelinks' ),
+				array( 'tl_namespace', 'tl_title' ),
+				array( 'tl_from' => $id ),
+				'Article:getUsedTemplates' );
+
+			global $wgContLang;
+
+			if ( false !== $res ) {
+				if ( $dbr->numRows( $res ) ) {
+					while ( $row = $dbr->fetchObject( $res ) ) {
+						$tlTemplates[] = $wgContLang->getNsText( $row->tl_namespace ) . ':' . $row->tl_title ;
+					}
+				}
+			}
+
+			# Get templates from parser output.
+			$poTemplates_allns = $parserOutput->getTemplates();
+
+			$poTemplates = array ();
+			foreach ( $poTemplates_allns as $ns_templates ) {
+				$poTemplates = array_merge( $poTemplates, $ns_templates );
+			}
+
+			# Get the diff
+			$templates_diff = array_diff( $poTemplates, $tlTemplates );
+
+			if ( count( $templates_diff ) > 0 ) {
+				# Whee, link updates time.
+				$u = new LinksUpdate( $this->mTitle, $parserOutput );
+
+				$dbw = wfGetDb( DB_MASTER );
+				$dbw->begin();
+
+				$u->doUpdate();
+
+				$dbw->commit();
+			}
+		}
+
+		$wgOut->addParserOutput( $parserOutput );
+	}
+
 }
 
 ?>
diff -NaurB -x .svn includes/AuthPlugin.php /srv/web/fp014/source/includes/AuthPlugin.php
--- includes/AuthPlugin.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/AuthPlugin.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,6 +1,5 @@
 <?php
 /**
- * @package MediaWiki
  */
 # Copyright (C) 2004 Brion Vibber <brion@pobox.com>
 # http://www.mediawiki.org/
@@ -33,7 +32,6 @@
  * This interface is new, and might change a bit before 1.4.0 final is
  * done...
  *
- * @package MediaWiki
  */
 class AuthPlugin {
 	/**
@@ -187,12 +185,14 @@
 	 * Add a user to the external authentication database.
 	 * Return true if successful.
 	 *
-	 * @param User $user
+	 * @param User $user - only the name should be assumed valid at this point
 	 * @param string $password
+	 * @param string $email
+	 * @param string $realname
 	 * @return bool
 	 * @public
 	 */
-	function addUser( $user, $password ) {
+	function addUser( $user, $password, $email='', $realname='' ) {
 		return true;
 	}
 
diff -NaurB -x .svn includes/AutoLoader.php /srv/web/fp014/source/includes/AutoLoader.php
--- includes/AutoLoader.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/AutoLoader.php	2007-02-01 01:03:18.000000000 +0000
@@ -39,8 +39,6 @@
 		'Database' => 'includes/Database.php',
 		'DatabaseMysql' => 'includes/Database.php',
 		'ResultWrapper' => 'includes/Database.php',
-		'OracleBlob' => 'includes/DatabaseOracle.php',
-		'DatabaseOracle' => 'includes/DatabaseOracle.php',
 		'DatabasePostgres' => 'includes/DatabasePostgres.php',
 		'DateFormatter' => 'includes/DateFormatter.php',
 		'DifferenceEngine' => 'includes/DifferenceEngine.php',
@@ -124,8 +122,8 @@
 		'ReverseChronologicalPager' => 'includes/Pager.php',
 		'TablePager' => 'includes/Pager.php',
 		'Parser' => 'includes/Parser.php',
-		'ParserOutput' => 'includes/Parser.php',
-		'ParserOptions' => 'includes/Parser.php',
+		'ParserOutput' => 'includes/ParserOutput.php',
+		'ParserOptions' => 'includes/ParserOptions.php',
 		'ParserCache' => 'includes/ParserCache.php',
 		'ProfilerSimple' => 'includes/ProfilerSimple.php',
 		'ProfilerSimpleUDP' => 'includes/ProfilerSimpleUDP.php',
@@ -242,6 +240,7 @@
 		'UserloginTemplate' => 'includes/templates/Userlogin.php',
 		'Language' => 'languages/Language.php',
 		'PasswordResetForm' => 'includes/SpecialResetpass.php',
+		'PatrolLog' => 'includes/PatrolLog.php',
 
 		// API classes
 		'ApiBase' => 'includes/api/ApiBase.php',
@@ -274,6 +273,7 @@
 		'ApiResult' => 'includes/api/ApiResult.php',
 	);
 	
+	wfProfileIn( __METHOD__ );
 	if ( isset( $localClasses[$className] ) ) {
 		$filename = $localClasses[$className];
 	} elseif ( isset( $wgAutoloadClasses[$className] ) ) {
@@ -290,6 +290,7 @@
 		}
 		if ( !$filename ) {
 			# Give up
+			wfProfileOut( __METHOD__ );
 			return;
 		}
 	}
@@ -300,6 +301,7 @@
 		$filename = "$IP/$filename";
 	}
 	require( $filename );
+	wfProfileOut( __METHOD__ );
 }
 
 function wfLoadAllExtensions() {
@@ -314,7 +316,7 @@
 	require_once( 'SpecialPage.php' );
 	
 	foreach( $wgAutoloadClasses as $class => $file ) {
-		if ( ! class_exists( $class ) ) {
+		if( !( class_exists( $class ) || interface_exists( $class ) ) ) {
 			require( $file );
 		}
 	}
diff -NaurB -x .svn includes/BagOStuff.php /srv/web/fp014/source/includes/BagOStuff.php
--- includes/BagOStuff.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/BagOStuff.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,7 +19,6 @@
 # http://www.gnu.org/copyleft/gpl.html
 /**
  *
- * @package MediaWiki
  */
 
 /**
@@ -32,12 +31,11 @@
  * $bag = new HashBagOStuff();
  * $bag = new MysqlBagOStuff($tablename); # connect to db first
  *
- * @package MediaWiki
  */
 class BagOStuff {
 	var $debugmode;
 
-	function BagOStuff() {
+	function __construct() {
 		$this->set_debug( false );
 	}
 
@@ -163,7 +161,6 @@
 /**
  * Functional versions!
  * @todo document
- * @package MediaWiki
  */
 class HashBagOStuff extends BagOStuff {
 	/*
@@ -218,7 +215,6 @@
 /**
  * @todo document
  * @abstract
- * @package MediaWiki
  */
 abstract class SqlBagOStuff extends BagOStuff {
 	var $table;
@@ -386,34 +382,32 @@
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class MediaWikiBagOStuff extends SqlBagOStuff {
 	var $tableInitialised = false;
 
 	function _doquery($sql) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->query($sql, 'MediaWikiBagOStuff::_doquery');
 	}
 	function _doinsert($t, $v) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->insert($t, $v, 'MediaWikiBagOStuff::_doinsert',
 			array( 'IGNORE' ) );
 	}
 	function _fetchobject($result) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->fetchObject($result);
 	}
 	function _freeresult($result) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->freeResult($result);
 	}
 	function _dberror($result) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->lastError();
 	}
 	function _maxdatetime() {
-		$dbw =& wfGetDB(DB_MASTER);
 		if ( time() > 0x7fffffff ) {
 			return $this->_fromunixtime( 1<<62 );
 		} else {
@@ -421,24 +415,24 @@
 		}
 	}
 	function _fromunixtime($ts) {
-		$dbw =& wfGetDB(DB_MASTER);
+		$dbw = wfGetDB(DB_MASTER);
 		return $dbw->timestamp($ts);
 	}
 	function _strencode($s) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->strencode($s);
 	}
 	function _blobencode($s) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->encodeBlob($s);
 	}
 	function _blobdecode($s) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->decodeBlob($s);
 	}
 	function getTableName() {
 		if ( !$this->tableInitialised ) {
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			/* This is actually a hack, we should be able
 			   to use Language classes here... or not */
 			if (!$dbw)
@@ -463,7 +457,6 @@
  * that Turck's serializer is faster, so a possible future extension would be
  * to use it for arrays but not for objects.
  *
- * @package MediaWiki
  */
 class TurckBagOStuff extends BagOStuff {
 	function get($key) {
@@ -498,7 +491,6 @@
 /**
  * This is a wrapper for APC's shared memory functions
  *
- * @package MediaWiki
  */
 
 class APCBagOStuff extends BagOStuff {
@@ -528,7 +520,6 @@
  * This is basically identical to the Turck MMCache version,
  * mostly because eAccelerator is based on Turck MMCache.
  *
- * @package MediaWiki
  */
 class eAccelBagOStuff extends BagOStuff {
 	function get($key) {
diff -NaurB -x .svn includes/Block.php /srv/web/fp014/source/includes/Block.php
--- includes/Block.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Block.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Blocks and bans object
- * @package MediaWiki
  */
 
 /**
@@ -12,7 +11,6 @@
  * Globals used: $wgAutoblockExpiry, $wgAntiLockFlags
  *
  * @todo This could be used everywhere, but it isn't.
- * @package MediaWiki
  */
 class Block
 {
@@ -24,7 +22,7 @@
 	const EB_FOR_UPDATE = 2;
 	const EB_RANGE_ONLY = 4;
 
-	function Block( $address = '', $user = 0, $by = 0, $reason = '',
+	function __construct( $address = '', $user = 0, $by = 0, $reason = '',
 		$timestamp = '' , $auto = 0, $expiry = '', $anonOnly = 0, $createAccount = 0, $enableAutoblock = 0 )
 	{
 		$this->mId = 0;
@@ -58,7 +56,7 @@
 
 	static function newFromID( $id ) 
 	{
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->resultObject( $dbr->select( 'ipblocks', '*', 
 			array( 'ipb_id' => $id ), __METHOD__ ) );
 		$block = new Block;
@@ -85,14 +83,14 @@
 	{
 		global $wgAntiLockFlags;
 		if ( $this->mForUpdate || $this->mFromMaster ) {
-			$db =& wfGetDB( DB_MASTER );
+			$db = wfGetDB( DB_MASTER );
 			if ( !$this->mForUpdate || ($wgAntiLockFlags & ALF_NO_BLOCK_LOCK) ) {
 				$options = array();
 			} else {
 				$options = array( 'FOR UPDATE' );
 			}
 		} else {
-			$db =& wfGetDB( DB_SLAVE );
+			$db = wfGetDB( DB_SLAVE );
 			$options = array();
 		}
 		return $db;
@@ -286,7 +284,7 @@
 
 		$block = new Block();
 		if ( $flags & Block::EB_FOR_UPDATE ) {
-			$db =& wfGetDB( DB_MASTER );
+			$db = wfGetDB( DB_MASTER );
 			if ( $wgAntiLockFlags & ALF_NO_BLOCK_LOCK ) {
 				$options = '';
 			} else {
@@ -294,7 +292,7 @@
 			}
 			$block->forUpdate( true );
 		} else {
-			$db =& wfGetDB( DB_SLAVE );
+			$db = wfGetDB( DB_SLAVE );
 			$options = '';
 		}
 		if ( $flags & Block::EB_RANGE_ONLY ) {
@@ -341,7 +339,7 @@
 			throw new MWException( "Block::delete() now requires that the mId member be filled\n" );
 		}
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'ipblocks', array( 'ipb_id' => $this->mId ), __METHOD__ );
 		return $dbw->affectedRows() > 0;
 	}
@@ -353,7 +351,7 @@
 	function insert()
 	{
 		wfDebug( "Block::insert; timestamp {$this->mTimestamp}\n" );
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->begin();
 
 		# Unset ipb_anon_only for user blocks, makes no sense
@@ -430,7 +428,7 @@
 	*/
 	function doAutoblock( $autoblockip ) {
 		# Check if this IP address is already blocked
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->begin();
 
 		# If autoblocks are disabled, go away.
@@ -544,7 +542,7 @@
 			$this->mTimestamp = wfTimestamp();
 			$this->mExpiry = Block::getAutoblockExpiry( $this->mTimestamp );
 
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$dbw->update( 'ipblocks',
 				array( /* SET */
 					'ipb_timestamp' => $dbw->timestamp($this->mTimestamp),
@@ -646,7 +644,7 @@
 	 * Purge expired blocks from the ipblocks table
 	 */
 	static function purgeExpired() {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'ipblocks', array( 'ipb_expiry < ' . $dbw->addQuotes( $dbw->timestamp() ) ), __METHOD__ );
 	}
 
@@ -658,7 +656,7 @@
 		/*
 		static $infinity;
 		if ( !isset( $infinity ) ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$infinity = $dbr->bigTimestamp();
 		}
 		return $infinity;
diff -NaurB -x .svn includes/CacheDependency.php /srv/web/fp014/source/includes/CacheDependency.php
--- includes/CacheDependency.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/CacheDependency.php	2007-02-01 01:03:18.000000000 +0000
@@ -244,7 +244,7 @@
 
 		# Do the query
 		if ( count( $timestamps ) ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$where = $this->getLinkBatch()->constructSet( 'page', $dbr );
 			$res = $dbr->select( 'page', 
 				array( 'page_namespace', 'page_title', 'page_touched' ),
diff -NaurB -x .svn includes/Categoryfinder.php /srv/web/fp014/source/includes/Categoryfinder.php
--- includes/Categoryfinder.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Categoryfinder.php	2007-02-01 01:03:18.000000000 +0000
@@ -35,7 +35,7 @@
 	/**
 	 * Constructor (currently empty).
 	*/
-	function Categoryfinder () {
+	function __construct() {
 	}
 
 	/**
@@ -64,7 +64,7 @@
 	 @return array of page_ids (those given to seed() that match the conditions)
 	*/
 	function run () {
-		$this->dbr =& wfGetDB( DB_SLAVE );
+		$this->dbr = wfGetDB( DB_SLAVE );
 		while ( count ( $this->next ) > 0 ) {
 			$this->scan_next_layer () ;
 		}
diff -NaurB -x .svn includes/CategoryPage.php /srv/web/fp014/source/includes/CategoryPage.php
--- includes/CategoryPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/CategoryPage.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,17 +3,23 @@
  * Special handling for category description pages
  * Modelled after ImagePage.php
  *
- * @package MediaWiki
  */
 
 if( !defined( 'MEDIAWIKI' ) )
 	die( 1 );
 
 /**
- * @package MediaWiki
  */
 class CategoryPage extends Article {
 	function view() {
+		global $wgRequest, $wgUser;
+
+		$diff = $wgRequest->getVal( 'diff' );
+		$diffOnly = $wgRequest->getBool( 'diffonly', $wgUser->getOption( 'diffonly' ) );
+
+		if ( isset( $diff ) && $diffOnly )
+			return Article::view();
+
 		if(!wfRunHooks('CategoryPageView', array(&$this))) return;
 
 		if ( NS_CATEGORY == $this->mTitle->getNamespace() ) {
@@ -175,7 +181,7 @@
 	}
 
 	function doCategoryQuery() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		if( $this->from != '' ) {
 			$pageCondition = 'cl_sortkey >= ' . $dbr->addQuotes( $this->from );
 			$this->flip = false;
@@ -391,7 +397,7 @@
 	 */
 	function pagingLinks( $title, $first, $last, $limit, $query = array() ) {
 		global $wgUser, $wgLang;
-		$sk =& $this->getSkin();
+		$sk = $this->getSkin();
 		$limitText = $wgLang->formatNum( $limit );
 
 		$prevLink = htmlspecialchars( wfMsg( 'prevn', $limitText ) );
diff -NaurB -x .svn includes/ChangesList.php /srv/web/fp014/source/includes/ChangesList.php
--- includes/ChangesList.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ChangesList.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,6 +1,5 @@
 <?php
 /**
- * @package MediaWiki
  * Contain class to show various lists of change:
  * - what's link here
  * - related changes
@@ -9,7 +8,6 @@
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class RCCacheEntry extends RecentChange
 {
@@ -17,8 +15,7 @@
 	var $curlink , $difflink, $lastlink , $usertalklink , $versionlink ;
 	var $userlink, $timestamp, $watched;
 
-	function newFromParent( $rc )
-	{
+	function newFromParent( $rc ) {
 		$rc2 = new RCCacheEntry;
 		$rc2->mAttribs = $rc->mAttribs;
 		$rc2->mExtra = $rc->mExtra;
@@ -27,14 +24,13 @@
 } ;
 
 /**
- * @package MediaWiki
  */
 class ChangesList {
 	# Called by history lists and recent changes
 	#
 
 	/** @todo document */
-	function ChangesList( &$skin ) {
+	function __construct( &$skin ) {
 		$this->skin =& $skin;
 		$this->preCacheMessages();
 	}
@@ -47,7 +43,7 @@
 	 * @return ChangesList derivative
 	 */
 	public static function newFromUser( &$user ) {
-		$sk =& $user->getSkin();
+		$sk = $user->getSkin();
 		$list = NULL;
 		if( wfRunHooks( 'FetchChangesList', array( &$user, &$sk, &$list ) ) ) {
 			return $user->getOption( 'usenewrc' ) ? new EnhancedChangesList( $sk ) : new OldChangesList( $sk );
@@ -212,6 +208,23 @@
 		global $wgUseRCPatrol, $wgUser;
 		return( $wgUseRCPatrol && $wgUser->isAllowed( 'patrol' ) );
 	}
+
+	/**
+	 * Returns the string which indicates the number of watching users
+	 */
+	function numberofWatchingusers( $count ) {
+		global $wgLang;
+		static $cache = array();
+		if ( $count > 0 ) {
+			if ( !isset( $cache[$count] ) ) {
+				$cache[$count] = wfMsgExt('number_of_watching_users_RCview',
+					array('parsemag', 'escape'), $wgLang->formatNum($count));
+			}
+			return $cache[$count];
+		} else {
+			return '';
+		}
+	}
 }
 
 
@@ -229,6 +242,7 @@
 		wfProfileIn( $fname );
 
 		# Extract DB fields into local scope
+		// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 		extract( $rc->mAttribs );
 
 		# Should patrol-related stuff be shown?
@@ -273,9 +287,7 @@
 		$this->insertUserRelatedLinks($s,$rc);
 		$this->insertComment($s, $rc);
 
-		if($rc->numberofWatchingusers > 0) {
-			$s .= ' ' . wfMsg('number_of_watching_users_RCview',  $wgContLang->formatNum($rc->numberofWatchingusers));
-		}
+		$s .=  rtrim(' ' . $this->numberofWatchingusers($rc->numberofWatchingusers));
 
 		$s .= "</li>\n";
 
@@ -301,6 +313,7 @@
 		$rc = RCCacheEntry::newFromParent( $baseRC );
 
 		# Extract fields from DB into the function scope (rc_xxxx variables)
+		// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 		extract( $rc->mAttribs );
 		$curIdEq = 'curid=' . $rc_cur_id;
 
@@ -476,13 +489,15 @@
 					$this->message['changes'], $curIdEq."&diff=$currentRevision&oldid=$oldid" );
 			}
 
+			$r .= ') . . ';
+
 			# Character difference
 			$chardiff = $rcObj->getCharacterDifference( $block[ count( $block ) - 1 ]->mAttribs['rc_old_len'],
 					$block[0]->mAttribs['rc_new_len'] );
 			if( $chardiff == '' ) {
-				$r .= '; ';
+				$r .= ' (';
 			} else {
-				$r .= '; ' . $chardiff . ' ';
+				$r .= ' ' . $chardiff. ' . . (';
 			}
 			
 
@@ -494,16 +509,14 @@
 
 		$r .= $users;
 
-		if($block[0]->numberofWatchingusers > 0) {
-			global $wgContLang;
-			$r .= wfMsg('number_of_watching_users_RCview',  $wgContLang->formatNum($block[0]->numberofWatchingusers));
-		}
+		$r .= $this->numberofWatchingusers($block[0]->numberofWatchingusers);
 		$r .= "<br />\n";
 
 		# Sub-entries
 		$r .= '<div id="'.$rci.'" style="display:none">';
 		foreach( $block as $rcObj ) {
 			# Get rc_xxxx variables
+			// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 			extract( $rcObj->mAttribs );
 
 			$r .= $this->spacerArrow();
@@ -607,6 +620,7 @@
 		global $wgContLang, $wgRCShowChangedSize;
 
 		# Get rc_xxxx variables
+		// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 		extract( $rcObj->mAttribs );
 		$curIdEq = 'curid='.$rc_cur_id;
 
@@ -647,9 +661,7 @@
 			$r .= $this->skin->commentBlock( $rc_comment, $rcObj->getTitle() );
 		}
 
-		if( $rcObj->numberofWatchingusers > 0 ) {
-			$r .= wfMsg('number_of_watching_users_RCview', $wgContLang->formatNum($rcObj->numberofWatchingusers));
-		}
+		$r .= $this->numberofWatchingusers($rcObj->numberofWatchingusers);
 
 		$r .= "<br />\n";
 		return $r;
diff -NaurB -x .svn includes/CoreParserFunctions.php /srv/web/fp014/source/includes/CoreParserFunctions.php
--- includes/CoreParserFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/CoreParserFunctions.php	2007-02-01 01:03:18.000000000 +0000
@@ -87,7 +87,7 @@
 	static function formatNum( $parser, $num = '' ) {
 		return $parser->getFunctionLang()->formatNum( $num );
 	}
-	
+
 	static function grammar( $parser, $case = '', $word = '' ) {
 		return $parser->getFunctionLang()->convertGrammar( $word, $case );
 	}
@@ -151,7 +151,7 @@
 		$lang = $wgContLang->getLanguageName( strtolower( $arg ) );
 		return $lang != '' ? $lang : $arg;
 	}
-	
+
 	static function pad( $string = '', $length = 0, $char = 0, $direction = STR_PAD_RIGHT ) {
 		$length = min( max( $length, 0 ), 500 );
 		$char = substr( $char, 0, 1 );
@@ -159,15 +159,15 @@
 				? str_pad( $string, $length, (string)$char, $direction )
 				: $string;
 	}
-	
+
 	static function padleft( $parser, $string = '', $length = 0, $char = 0 ) {
 		return self::pad( $string, $length, $char, STR_PAD_LEFT );
 	}
-	
+
 	static function padright( $parser, $string = '', $length = 0, $char = 0 ) {
 		return self::pad( $string, $length, $char );
 	}
-	
+
 	static function anchorencode( $parser, $text ) {
 		return strtr( urlencode( $text ) , array( '%' => '.' , '+' => '_' ) );
 	}
@@ -180,14 +180,12 @@
 			return wfMsgForContent( 'nosuchspecialpage' );
 		}
 	}
-	
+
 	public static function defaultsort( $parser, $text ) {
 		$text = trim( $text );
 		if( strlen( $text ) > 0 )
 			$parser->setDefaultSort( $text );
 		return '';
 	}
-	
 }
-
 ?>
diff -NaurB -x .svn includes/Credits.php /srv/web/fp014/source/includes/Credits.php
--- includes/Credits.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Credits.php	2007-02-01 01:04:18.000000000 +0000
@@ -18,7 +18,6 @@
  *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
  *
  * @author <evan@wikitravel.org>
- * @package MediaWiki
  */
 
 /**
@@ -48,22 +47,100 @@
 	wfProfileOut( $fname );
 }
 
-function getCredits($article, $cnt, $showIfMax=true) {
+/** Modified by ppiotr@wikia.com
+ *  Used in Contribution extension
+ */ 
+function getCredits($article, $cnt, $showIfMax=true)
+{
 	$fname = 'getCredits';
-	wfProfileIn( $fname );
+	wfProfileIn($fname);
+
+	global $wgLang;
 	$s = '';
 
-	if (isset($cnt) && $cnt != 0) {
-		$s = getAuthorCredits($article);
-		if ($cnt > 1 || $cnt < 0) {
-			$s .= ' ' . getContributorCredits($article, $cnt - 1, $showIfMax);
+	$timestamp = $article->getTimestamp();
+	if ($timestamp)
+	{
+		$s .= wfMsg('lastmodified', $wgLang->timeanddate($timestamp, true));
+	}
+
+	$s .= ' ';
+
+	if ($cnt)
+	{
+		$s .= '<div style="visibility: hidden; display: block; white-space: normal;">';
+
+		global $wgDBname;
+		$memc_key              = "{$wgDBname}:credits:page_id:{$article->getId()}";
+		$memc_key_timestamp    = "{$memc_key}:timestamp";
+		$memc_key_contributors = "{$memc_key}:contributors";
+
+		global $wgMemc;
+		$memc_timestamp    = $wgMemc->get($memc_key_timestamp);
+		$memc_contributors = $wgMemc->get($memc_key_contributors);
+		if (($memc_timestamp < $timestamp) || empty($memc_contributors))
+		{
+			$dbr =& wfGetDB( DB_SLAVE );
+			$sql = "SELECT DISTINCT rev_user_text
+				FROM {$dbr->tableName('revision')}
+				WHERE rev_page = {$article->getId()} AND rev_user != 0
+				ORDER BY rev_user_text;";
+			$res = $dbr->query($sql, $fname);
+
+			$contributors = array();
+			while ($line = $dbr->fetchRow($res))
+			{
+				$contributors[] = $line['rev_user_text'];
+			}
+
+			$dbr->freeResult($res);
+
+			$wgMemc->set($memc_key_timestamp,    $timestamp);
+			$wgMemc->set($memc_key_contributors, $contributors);
+
+			$s .= ' [new] ';
+		} else
+		{
+			$contributors =& $memc_contributors;
+			$s .= ' [cached] ';
 		}
+
+		$others_link = '';
+		if ((-1 != $cnt) && ($cnt < count($contributors)))
+		{
+			$others_link = creditOthersLink($article);
+
+			if (!$showIfMax)
+			{
+				$s .= wfMsg('othercontribs', $others_link);
+				return $s;
+			} else {
+				$contributors = array_slice($contributors, 0, $cnt);
+			}
+		}
+
+		$links = array();
+		foreach ($contributors as $user)
+		{
+			$links[] = creditLink($user);
+		}
+
+		if (!empty($others_link))
+		{
+			$links[] = $others_link;
+		}
+
+		$creds = $wgLang->listToText($links);
+		if ($creds)
+		{
+			$s .= wfMsg('othercontribs', $creds);
+		}
+		$s .= '</div>';
 	}
 
-	wfProfileOut( $fname );
+	wfProfileOut($fname);
 	return $s;
 }
-
 /**
  *
  */
diff -NaurB -x .svn includes/DatabaseFunctions.php /srv/web/fp014/source/includes/DatabaseFunctions.php
--- includes/DatabaseFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DatabaseFunctions.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,7 +3,6 @@
  * Legacy database functions, for compatibility with pre-1.3 code
  * NOTE: this file is no longer loaded by default.
  *
- * @package MediaWiki
  */
 
 /**
@@ -18,7 +17,7 @@
 		# Someone has tried to call this the old way
 		throw new FatalError( wfMsgNoDB( 'wrong_wfQuery_params', $db, $sql ) );
 	}
-	$c =& wfGetDB( $db );
+	$c = wfGetDB( $db );
 	if ( $c !== false ) {
 		return $c->query( $sql, $fname );
 	} else {
@@ -34,7 +33,7 @@
  * @return Array: first row from the database
  */
 function wfSingleQuery( $sql, $dbi, $fname = '' ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	$res = $db->query($sql, $fname );
 	$row = $db->fetchRow( $res );
 	$ret = $row[0];
@@ -54,7 +53,7 @@
  * @return Returns the previous state.
  */
 function wfIgnoreSQLErrors( $newstate, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->ignoreErrors( $newstate );
 	} else {
@@ -73,7 +72,7 @@
  */
 function wfFreeResult( $res, $dbi = DB_LAST )
 {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		$db->freeResult( $res );
 		return true;
@@ -87,7 +86,7 @@
  * @return object|false object we requested
  */
 function wfFetchObject( $res, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->fetchObject( $res, $dbi = DB_LAST );
 	} else {
@@ -100,7 +99,7 @@
  * @return object|false row we requested
  */
 function wfFetchRow( $res, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->fetchRow ( $res, $dbi = DB_LAST );
 	} else {
@@ -113,7 +112,7 @@
  * @return integer|false number of rows
  */
 function wfNumRows( $res, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->numRows( $res, $dbi = DB_LAST );
 	} else {
@@ -126,7 +125,7 @@
  * @return integer|false number of fields
  */
 function wfNumFields( $res, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->numFields( $res );
 	} else {
@@ -143,7 +142,7 @@
  */
 function wfFieldName( $res, $n, $dbi = DB_LAST )
 {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->fieldName( $res, $n, $dbi = DB_LAST );
 	} else {
@@ -156,7 +155,7 @@
  * @todo document function
  */
 function wfInsertId( $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->insertId();
 	} else {
@@ -168,7 +167,7 @@
  * @todo document function
  */
 function wfDataSeek( $res, $row, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->dataSeek( $res, $row );
 	} else {
@@ -180,7 +179,7 @@
  * @todo document function
  */
 function wfLastErrno( $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->lastErrno();
 	} else {
@@ -192,7 +191,7 @@
  * @todo document function
  */
 function wfLastError( $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->lastError();
 	} else {
@@ -204,7 +203,7 @@
  * @todo document function
  */
 function wfAffectedRows( $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->affectedRows();
 	} else {
@@ -216,7 +215,7 @@
  * @todo document function
  */
 function wfLastDBquery( $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->lastQuery();
 	} else {
@@ -235,7 +234,7 @@
  */
 function wfSetSQL( $table, $var, $value, $cond, $dbi = DB_MASTER )
 {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->set( $table, $var, $value, $cond );
 	} else {
@@ -254,7 +253,7 @@
  */
 function wfGetSQL( $table, $var, $cond='', $dbi = DB_LAST )
 {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->selectField( $table, $var, $cond );
 	} else {
@@ -271,7 +270,7 @@
  * @return Result of Database::fieldExists() or false.
  */
 function wfFieldExists( $table, $field, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->fieldExists( $table, $field );
 	} else {
@@ -288,7 +287,7 @@
  * @return Result of Database::indexExists() or false.
  */
 function wfIndexExists( $table, $index, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->indexExists( $table, $index );
 	} else {
@@ -306,7 +305,7 @@
  * @return result of Database::insert() or false.
  */
 function wfInsertArray( $table, $array, $fname = 'wfInsertArray', $dbi = DB_MASTER ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->insert( $table, $array, $fname );
 	} else {
@@ -325,7 +324,7 @@
  * @return result of Database::getArray() or false.
  */
 function wfGetArray( $table, $vars, $conds, $fname = 'wfGetArray', $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->getArray( $table, $vars, $conds, $fname );
 	} else {
@@ -344,7 +343,7 @@
  * @todo document function
  */
 function wfUpdateArray( $table, $values, $conds, $fname = 'wfUpdateArray', $dbi = DB_MASTER ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		$db->update( $table, $values, $conds, $fname );
 		return true;
@@ -357,7 +356,7 @@
  * @todo document function
  */
 function wfTableName( $name, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->tableName( $name );
 	} else {
@@ -369,7 +368,7 @@
  * @todo document function
  */
 function wfStrencode( $s, $dbi = DB_LAST ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->strencode( $s );
 	} else {
@@ -381,7 +380,7 @@
  * @todo document function
  */
 function wfNextSequenceValue( $seqName, $dbi = DB_MASTER ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->nextSequenceValue( $seqName );
 	} else {
@@ -393,7 +392,7 @@
  * @todo document function
  */
 function wfUseIndexClause( $index, $dbi = DB_SLAVE ) {
-	$db =& wfGetDB( $dbi );
+	$db = wfGetDB( $dbi );
 	if ( $db !== false ) {
 		return $db->useIndexClause( $index );
 	} else {
diff -NaurB -x .svn includes/DatabaseOracle.php /srv/web/fp014/source/includes/DatabaseOracle.php
--- includes/DatabaseOracle.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DatabaseOracle.php	1970-01-01 00:00:00.000000000 +0000
@@ -1,691 +0,0 @@
-<?php
-
-/**
- * Oracle.
- *
- * @package MediaWiki
- */
-
-class OracleBlob extends DBObject {
-	function isLOB() {
-		return true;
-	}
-	function data() {
-		return $this->mData;
-	}
-};
-
-/**
- *
- * @package MediaWiki
- */
-class DatabaseOracle extends Database {
-	var $mInsertId = NULL;
-	var $mLastResult = NULL;
-	var $mFetchCache = array();
-	var $mFetchID = array();
-	var $mNcols = array();
-	var $mFieldNames = array(), $mFieldTypes = array();
-	var $mAffectedRows = array();
-	var $mErr;
-
-	function DatabaseOracle($server = false, $user = false, $password = false, $dbName = false,
-		$failFunction = false, $flags = 0, $tablePrefix = 'get from global' )
-	{
-		Database::Database( $server, $user, $password, $dbName, $failFunction, $flags, $tablePrefix );
-	}
-
-	/* static */ function newFromParams( $server = false, $user = false, $password = false, $dbName = false,
-		$failFunction = false, $flags = 0, $tablePrefix = 'get from global' )
-	{
-		return new DatabaseOracle( $server, $user, $password, $dbName, $failFunction, $flags, $tablePrefix );
-	}
-
-	/**
-	 * Usually aborts on failure
-	 * If the failFunction is set to a non-zero integer, returns success
-	 */
-	function open( $server, $user, $password, $dbName ) {
-		if ( !function_exists( 'oci_connect' ) ) {
-			throw new DBConnectionError( $this, "Oracle functions missing, have you compiled PHP with the --with-oci8 option?\n" );
-		}
-		$this->close();
-		$this->mServer = $server;
-		$this->mUser = $user;
-		$this->mPassword = $password;
-		$this->mDBname = $dbName;
-
-		$this->mConn = oci_new_connect($user, $password, $dbName, "AL32UTF8");
-		if ( $this->mConn === false ) {
-			wfDebug( "DB connection error\n" );
-			wfDebug( "Server: $server, Database: $dbName, User: $user, Password: "
-				. substr( $password, 0, 3 ) . "...\n" );
-			wfDebug( $this->lastError()."\n" );
-		} else {
-			$this->mOpened = true;
-		}
-		return $this->mConn;
-	}
-
-	/**
-	 * Closes a database connection, if it is open
-	 * Returns success, true if already closed
-	 */
-	function close() {
-		$this->mOpened = false;
-		if ($this->mConn) {
-			return oci_close($this->mConn);
-		} else {
-			return true;
-		}
-	}
-
-	function parseStatement($sql) {
-		$this->mErr = $this->mLastResult = false;
-		if (($stmt = oci_parse($this->mConn, $sql)) === false) {
-			$this->lastError();
-			return $this->mLastResult = false;
-		}
-		$this->mAffectedRows[$stmt] = 0;
-		return $this->mLastResult = $stmt;
-	}
-
-	function doQuery($sql) {
-		if (($stmt = $this->parseStatement($sql)) === false)
-			return false;
-		return $this->executeStatement($stmt);
-	}
-
-	function executeStatement($stmt) {
-		if (!oci_execute($stmt, OCI_DEFAULT)) {
-			$this->lastError();
-			oci_free_statement($stmt);
-			return false;
-		}
-		$this->mAffectedRows[$stmt] = oci_num_rows($stmt);
-		$this->mFetchCache[$stmt] = array();
-		$this->mFetchID[$stmt] = 0;
-		$this->mNcols[$stmt] = oci_num_fields($stmt);
-		if ($this->mNcols[$stmt] == 0)
-			return $this->mLastResult;
-		for ($i = 1; $i <= $this->mNcols[$stmt]; $i++) {
-			$this->mFieldNames[$stmt][$i] = oci_field_name($stmt, $i);
-			$this->mFieldTypes[$stmt][$i] = oci_field_type($stmt, $i);
-		}
-		while (($o = oci_fetch_array($stmt)) !== false) {
-			foreach ($o as $key => $value) {
-				if (is_object($value)) {
-					$o[$key] = $value->load();
-				}
-			}
-			$this->mFetchCache[$stmt][] = $o;
-		}
-		return $this->mLastResult;
-	}
-
-	function queryIgnore( $sql, $fname = '' ) {
-		return $this->query( $sql, $fname, true );
-	}
-
-	function freeResult( $res ) {
-		if (!oci_free_statement($res)) {
-			throw new DBUnexpectedError( $this, "Unable to free Oracle result\n" );
-		}
-		unset($this->mFetchID[$res]);
-		unset($this->mFetchCache[$res]);
-		unset($this->mNcols[$res]);
-		unset($this->mFieldNames[$res]);
-		unset($this->mFieldTypes[$res]);
-	}
-
-	function fetchAssoc($res) {
-		if ($this->mFetchID[$res] >= count($this->mFetchCache[$res]))
-			return false;
-
-		for ($i = 1; $i <= $this->mNcols[$res]; $i++) {
-			$name = $this->mFieldNames[$res][$i];
-			if (isset($this->mFetchCache[$res][$this->mFetchID[$res]][$name]))
-				$value = $this->mFetchCache[$res][$this->mFetchID[$res]][$name];
-			else	$value = NULL;
-			$key = strtolower($name);
-			wfdebug("'$key' => '$value'\n");
-			$ret[$key] = $value;
-		}
-		$this->mFetchID[$res]++;
-		return $ret;
-	}
-
-	function fetchRow($res) {
-		$r = $this->fetchAssoc($res);
-		if (!$r)
-			return false;
-		$i = 0;
-		$ret = array();
-		foreach ($r as $value) {
-			wfdebug("ret[$i]=[$value]\n");
-			$ret[$i++] = $value;
-		}
-		return $ret;
-	}
-
-	function fetchObject($res) {
-		$row = $this->fetchAssoc($res);
-		if (!$row)
-			return false;
-		$ret = new stdClass;
-		foreach ($row as $key => $value)
-			$ret->$key = $value;
-		return $ret;
-	}
-
-	function numRows($res) {
-		return count($this->mFetchCache[$res]);
-	}
-	function numFields( $res ) { return pg_num_fields( $res ); }
-	function fieldName( $res, $n ) { return pg_field_name( $res, $n ); }
-
-	/**
-	 * This must be called after nextSequenceVal
-	 */
-	function insertId() {
-		return $this->mInsertId;
-	}
-
-	function dataSeek($res, $row) {
-		$this->mFetchID[$res] = $row;
-	}
-
-	function lastError() {
-		if ($this->mErr === false) {
-			if ($this->mLastResult !== false) { 
-				$what = $this->mLastResult;
-			} else if ($this->mConn !== false) {
-				$what = $this->mConn;
-			} else {
-				$what = false;
-			}
-			$err = ($what !== false) ? oci_error($what) : oci_error();
-			if ($err === false) {
-				$this->mErr = 'no error';
-			} else {
-				$this->mErr = $err['message'];
-			}
-		}
-		return str_replace("\n", '<br />', $this->mErr);
-	}
-	function lastErrno() {
-		return 0;
-	}
-
-	function affectedRows() {
-		return $this->mAffectedRows[$this->mLastResult];
-	}
-
-	/**
-	 * Returns information about an index
-	 * If errors are explicitly ignored, returns NULL on failure
-	 */
-	function indexInfo ($table, $index, $fname = 'Database::indexInfo' ) {
-		$table = $this->tableName($table, true);
-		if ($index == 'PRIMARY')
-			$index = "${table}_pk";
-		$sql = "SELECT uniqueness FROM all_indexes WHERE table_name='" .
-			$table . "' AND index_name='" .
-			$this->strencode(strtoupper($index)) . "'";
-		$res = $this->query($sql, $fname);
-		if (!$res)
-			return NULL;
-		if (($row = $this->fetchObject($res)) == NULL)
-			return false;
-		$this->freeResult($res);
-		$row->Non_unique = !$row->uniqueness;
-		return $row;
-		
-		// BUG: !!!! This code needs to be synced up with database.php
-		
-	}
-
-	function indexUnique ($table, $index, $fname = 'indexUnique') {
-		if (!($i = $this->indexInfo($table, $index, $fname)))
-			return $i;
-		return $i->uniqueness == 'UNIQUE';
-	}
-
-	function fieldInfo( $table, $field ) {
-		$o = new stdClass;
-		$o->multiple_key = true; /* XXX */
-		return $o;
-	}
-
-	function getColumnInformation($table, $field) {
-		$table = $this->tableName($table, true);
-		$field = strtoupper($field);
-
-		$res = $this->doQuery("SELECT * FROM all_tab_columns " .
-			"WHERE table_name='".$table."' " .
-			"AND   column_name='".$field."'");
-		if (!$res)
-			return false;
-		$o = $this->fetchObject($res);
-		$this->freeResult($res);
-		return $o;
-	}
-
-	function fieldExists( $table, $field, $fname = 'Database::fieldExists' ) {
-		$column = $this->getColumnInformation($table, $field);
-		if (!$column)
-			return false;
-		return true;
-	}
-
-	function tableName($name, $forddl = false) {
-		# First run any transformations from the parent object
-		$name = parent::tableName( $name );
-
-		# Replace backticks into empty
-		# Note: "foo" and foo are not the same in Oracle!
-		$name = str_replace('`', '', $name);
-
-		# Now quote Oracle reserved keywords
-		switch( $name ) {
-			case 'user':
-			case 'group':
-			case 'validate':
-				if ($forddl)
-					return $name;
-				else
-					return '"' . $name . '"';
-
-			default:
-				return strtoupper($name);
-		}
-	}
-
-	function strencode( $s ) {
-		return str_replace("'", "''", $s);
-	}
-
-	/**
-	 * Return the next in a sequence, save the value for retrieval via insertId()
-	 */
-	function nextSequenceValue( $seqName ) {
-		$r = $this->doQuery("SELECT $seqName.nextval AS val FROM dual");
-		$o = $this->fetchObject($r);
-		$this->freeResult($r);
-		return $this->mInsertId = (int)$o->val;
-	}
-
-	/**
-	 * USE INDEX clause
-	 * PostgreSQL doesn't have them and returns ""
-	 */
-	function useIndexClause( $index ) {
-		return '';
-	}
-
-	# REPLACE query wrapper
-	# PostgreSQL simulates this with a DELETE followed by INSERT
-	# $row is the row to insert, an associative array
-	# $uniqueIndexes is an array of indexes. Each element may be either a
-	# field name or an array of field names
-	#
-	# It may be more efficient to leave off unique indexes which are unlikely to collide.
-	# However if you do this, you run the risk of encountering errors which wouldn't have
-	# occurred in MySQL
-	function replace( $table, $uniqueIndexes, $rows, $fname = 'Database::replace' ) {
-		$table = $this->tableName( $table );
-
-		if (count($rows)==0) {
-			return;
-		}
-
-		# Single row case
-		if ( !is_array( reset( $rows ) ) ) {
-			$rows = array( $rows );
-		}
-
-		foreach( $rows as $row ) {
-			# Delete rows which collide
-			if ( $uniqueIndexes ) {
-				$sql = "DELETE FROM $table WHERE ";
-				$first = true;
-				foreach ( $uniqueIndexes as $index ) {
-					if ( $first ) {
-						$first = false;
-						$sql .= "(";
-					} else {
-						$sql .= ') OR (';
-					}
-					if ( is_array( $index ) ) {
-						$first2 = true;
-						foreach ( $index as $col ) {
-							if ( $first2 ) {
-								$first2 = false;
-							} else {
-								$sql .= ' AND ';
-							}
-							$sql .= $col.'=' . $this->addQuotes( $row[$col] );
-						}
-					} else {
-						$sql .= $index.'=' . $this->addQuotes( $row[$index] );
-					}
-				}
-				$sql .= ')';
-				$this->query( $sql, $fname );
-			}
-
-			# Now insert the row
-			$sql = "INSERT INTO $table (" . $this->makeList( array_keys( $row ), LIST_NAMES ) .') VALUES (' .
-				$this->makeList( $row, LIST_COMMA ) . ')';
-			$this->query( $sql, $fname );
-		}
-	}
-
-	# DELETE where the condition is a join
-	function deleteJoin( $delTable, $joinTable, $delVar, $joinVar, $conds, $fname = "Database::deleteJoin" ) {
-		if ( !$conds ) {
-			throw new DBUnexpectedError( $this, 'Database::deleteJoin() called with empty $conds' );
-		}
-
-		$delTable = $this->tableName( $delTable );
-		$joinTable = $this->tableName( $joinTable );
-		$sql = "DELETE FROM $delTable WHERE $delVar IN (SELECT $joinVar FROM $joinTable ";
-		if ( $conds != '*' ) {
-			$sql .= 'WHERE ' . $this->makeList( $conds, LIST_AND );
-		}
-		$sql .= ')';
-
-		$this->query( $sql, $fname );
-	}
-
-	# Returns the size of a text field, or -1 for "unlimited"
-	function textFieldSize( $table, $field ) {
-		$table = $this->tableName( $table );
-		$sql = "SELECT t.typname as ftype,a.atttypmod as size
-			FROM pg_class c, pg_attribute a, pg_type t
-			WHERE relname='$table' AND a.attrelid=c.oid AND
-				a.atttypid=t.oid and a.attname='$field'";
-		$res =$this->query($sql);
-		$row=$this->fetchObject($res);
-		if ($row->ftype=="varchar") {
-			$size=$row->size-4;
-		} else {
-			$size=$row->size;
-		}
-		$this->freeResult( $res );
-		return $size;
-	}
-
-	function lowPriorityOption() {
-		return '';
-	}
-
-	function limitResult($sql, $limit, $offset) {
-		$ret = "SELECT * FROM ($sql) WHERE ROWNUM < " . ((int)$limit + (int)($offset+1));
-		if (is_numeric($offset))
-			$ret .= " AND ROWNUM >= " . (int)$offset;
-		return $ret;
-	}
-	function limitResultForUpdate($sql, $limit) {
-		return $sql;
-	}
-	/**
-	 * Returns an SQL expression for a simple conditional.
-	 * Uses CASE on PostgreSQL.
-	 *
-	 * @param string $cond SQL expression which will result in a boolean value
-	 * @param string $trueVal SQL expression to return if true
-	 * @param string $falseVal SQL expression to return if false
-	 * @return string SQL fragment
-	 */
-	function conditional( $cond, $trueVal, $falseVal ) {
-		return " (CASE WHEN $cond THEN $trueVal ELSE $falseVal END) ";
-	}
-
-	# FIXME: actually detecting deadlocks might be nice
-	function wasDeadlock() {
-		return false;
-	}
-
-	# Return DB-style timestamp used for MySQL schema
-	function timestamp($ts = 0) {
-		return $this->strencode(wfTimestamp(TS_ORACLE, $ts));
-#		return "TO_TIMESTAMP('" . $this->strencode(wfTimestamp(TS_DB, $ts)) . "', 'RRRR-MM-DD HH24:MI:SS')";
-	}
-
-	/**
-	 * Return aggregated value function call
-	 */
-	function aggregateValue ($valuedata,$valuename='value') {
-		return $valuedata;
-	}
-
-
-	function reportQueryError( $error, $errno, $sql, $fname, $tempIgnore = false ) {
-		$message = "A database error has occurred\n" .
-			"Query: $sql\n" .
-			"Function: $fname\n" .
-			"Error: $errno $error\n";
-		throw new DBUnexpectedError($this, $message);
-	}
-
-	/**
-	 * @return string wikitext of a link to the server software's web site
-	 */
-	function getSoftwareLink() {
-		return "[http://www.oracle.com/ Oracle]";
-	}
-
-	/**
-	 * @return string Version information from the database
-	 */
-	function getServerVersion() {
-		return oci_server_version($this->mConn);
-	}
-
-	function setSchema($schema=false) {
-		$schemas=$this->mSchemas;
-		if ($schema) { array_unshift($schemas,$schema); }
-		$searchpath=$this->makeList($schemas,LIST_NAMES);
-		$this->query("SET search_path = $searchpath");
-	}
-
-	function begin() {
-	}
-
-	function immediateCommit( $fname = 'Database::immediateCommit' ) {
-		oci_commit($this->mConn);
-		$this->mTrxLevel = 0;
-	}
-	function rollback( $fname = 'Database::rollback' ) {
-		oci_rollback($this->mConn);
-		$this->mTrxLevel = 0;
-	}
-	function getLag() {
-		return false;
-	}
-	function getStatus($which=null) {
-		$result = array('Threads_running' => 0, 'Threads_connected' => 0);
-		return $result;
-	}
-
-	/**
-	 * Returns an optional USE INDEX clause to go after the table, and a
-	 * string to go at the end of the query
-	 *
-	 * @access private
-	 *
-	 * @param array $options an associative array of options to be turned into
-	 *              an SQL query, valid keys are listed in the function.
-	 * @return array
-	 */
-	function makeSelectOptions($options) {
-		$tailOpts = '';
-
-		if (isset( $options['ORDER BY'])) {
-			$tailOpts .= " ORDER BY {$options['ORDER BY']}";
-		}
-
-		return array('', $tailOpts);
-	}
-
-	function maxListLen() {
-		return 1000;
-	}
-
-	/**
-	 * Query whether a given table exists
-	 */
-	function tableExists( $table ) {
-		$table = $this->tableName($table, true);
-		$res = $this->query( "SELECT COUNT(*) as NUM FROM user_tables WHERE table_name='"
-			. $table . "'" );
-		if (!$res)
-			return false;
-		$row = $this->fetchObject($res);
-		$this->freeResult($res);
-		return $row->num >= 1;
-	}
-
-	/**
-	 * UPDATE wrapper, takes a condition array and a SET array
-	 */
-	function update( $table, $values, $conds, $fname = 'Database::update' ) {
-		$table = $this->tableName( $table );
-
-		$sql = "UPDATE $table SET ";
-		$first = true;
-		foreach ($values as $field => $v) {
-			if ($first)
-				$first = false;
-			else
-				$sql .= ", ";
-			$sql .= "$field = :n$field ";
-		}
-		if ( $conds != '*' ) {
-			$sql .= " WHERE " . $this->makeList( $conds, LIST_AND );
-		}
-		$stmt = $this->parseStatement($sql);
-		if ($stmt === false) {
-			$this->reportQueryError( $this->lastError(), $this->lastErrno(), $stmt );
-			return false;
-		}
-		if ($this->debug())
-			wfDebug("SQL: $sql\n");
-		$s = '';
-		foreach ($values as $field => $v) {
-			oci_bind_by_name($stmt, ":n$field", $values[$field]);
-			if ($this->debug())
-				$s .= " [$field] = [$v]\n";
-		}
-		if ($this->debug())
-			wfdebug(" PH: $s\n");
-		$ret = $this->executeStatement($stmt);
-		return $ret;
-	}
-
-	/**
-	 * INSERT wrapper, inserts an array into a table
-	 *
-	 * $a may be a single associative array, or an array of these with numeric keys, for
-	 * multi-row insert.
-	 *
-	 * Usually aborts on failure
-	 * If errors are explicitly ignored, returns success
-	 */
-	function insert( $table, $a, $fname = 'Database::insert', $options = array() ) {
-		# No rows to insert, easy just return now
-		if ( !count( $a ) ) {
-			return true;
-		}
-
-		$table = $this->tableName( $table );
-		if (!is_array($options))
-			$options = array($options);
-
-		$oldIgnore = false;
-		if (in_array('IGNORE', $options))
-			$oldIgnore = $this->ignoreErrors( true );
-
-		if ( isset( $a[0] ) && is_array( $a[0] ) ) {
-			$multi = true;
-			$keys = array_keys( $a[0] );
-		} else {
-			$multi = false;
-			$keys = array_keys( $a );
-		}
-
-		$sql = "INSERT INTO $table (" . implode( ',', $keys ) . ') VALUES (';
-		$return = '';
-		$first = true;
-		foreach ($a as $key => $value) {
-			if ($first)
-				$first = false;
-			else
-				$sql .= ", ";
-			if (is_object($value) && $value->isLOB()) {
-				$sql .= "EMPTY_BLOB()";
-				$return = "RETURNING $key INTO :bobj";
-			} else
-				$sql .= ":$key";
-		}
-		$sql .= ") $return";
-
-		if ($this->debug()) {
-			wfDebug("SQL: $sql\n");
-		}
-
-		if (($stmt = $this->parseStatement($sql)) === false) {
-			$this->reportQueryError($this->lastError(), $this->lastErrno(), $sql, $fname);
-			$this->ignoreErrors($oldIgnore);
-			return false;
-		}
-
-		/*
-		 * If we're inserting multiple rows, parse the statement once and
-		 * execute it for each set of values.  Otherwise, convert it into an
-		 * array and pretend.
-		 */
-		if (!$multi)
-			$a = array($a);
-
-		foreach ($a as $key => $row) {
-			$blob = false;
-			$bdata = false;
-			$s = '';
-			foreach ($row as $k => $value) {
-				if (is_object($value) && $value->isLOB()) {
-					$blob = oci_new_descriptor($this->mConn, OCI_D_LOB);
-					$bdata = $value->data();
-					oci_bind_by_name($stmt, ":bobj", $blob, -1, OCI_B_BLOB);
-				} else
-					oci_bind_by_name($stmt, ":$k", $a[$key][$k], -1);
-				if ($this->debug())
-					$s .= " [$k] = {$row[$k]}";
-			}
-			if ($this->debug())
-				wfDebug(" PH: $s\n");
-			if (($s = $this->executeStatement($stmt)) === false) {
-				$this->reportQueryError($this->lastError(), $this->lastErrno(), $sql, $fname);
-				$this->ignoreErrors($oldIgnore);
-				return false;
-			}
-
-			if ($blob) {
-				$blob->save($bdata);
-			}
-		}
-		$this->ignoreErrors($oldIgnore);
-		return $this->mLastResult = $s;
-	}
-
-	function ping() {
-		return true;
-	}
-
-	function encodeBlob($b) {
-		return new OracleBlob($b);
-	}
-}
-
-?>
diff -NaurB -x .svn includes/Database.php /srv/web/fp014/source/includes/Database.php
--- includes/Database.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Database.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,7 +2,6 @@
 /**
  * This file deals with MySQL interface functions
  * and query specifics/optimisations
- * @package MediaWiki
  */
 
 /** Number of times to re-try an operation in case of deadlock */
@@ -154,6 +153,7 @@
 
 			$cache = new HTMLFileCache( $t );
 			if( $cache->isFileCached() ) {
+				// FIXME: $msg is not defined on the next line.
 				$msg = '<p style="color: red"><b>'.$msg."<br />\n" .
 					$cachederror . "</b></p>\n";
 
@@ -228,7 +228,6 @@
 
 /**
  * Database abstraction object
- * @package MediaWiki
  */
 class Database {
 
@@ -376,6 +375,14 @@
 		return true;
 	}
 
+	/**
+	 * Returns true if this database can do a native search on IP columns
+	 * e.g. this works as expected: .. WHERE rc_ip = '127.42.12.102/32';
+	 */
+	function searchableIPs() {
+		return false;
+	}
+
 	/**#@+
 	 * Get function
 	 */
@@ -2050,7 +2057,6 @@
  * Database abstraction object for mySQL
  * Inherit all methods and properties of Database::Database()
  *
- * @package MediaWiki
  * @see Database
  */
 class DatabaseMysql extends Database {
@@ -2061,7 +2067,6 @@
 /**
  * Result wrapper for grabbing data queried by someone else
  *
- * @package MediaWiki
  */
 class ResultWrapper {
 	var $db, $result;
diff -NaurB -x .svn includes/DatabasePostgres.php /srv/web/fp014/source/includes/DatabasePostgres.php
--- includes/DatabasePostgres.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DatabasePostgres.php	2007-02-01 01:03:18.000000000 +0000
@@ -7,12 +7,12 @@
  * than MySQL ones, some of them should be moved to parent
  * Database class.
  *
- * @package MediaWiki
  */
 
 class DatabasePostgres extends Database {
 	var $mInsertId = NULL;
 	var $mLastResult = NULL;
+	var $numeric_version = NULL;
 
 	function DatabasePostgres($server = false, $user = false, $password = false, $dbName = false,
 		$failFunction = false, $flags = 0 )
@@ -41,6 +41,10 @@
 		return false;
 	}
 
+	function searchableIPs() {
+		return true;
+	}
+
 	static function newFromParams( $server = false, $user = false, $password = false, $dbName = false,
 		$failFunction = false, $flags = 0)
 	{
@@ -94,21 +98,15 @@
 		if (defined('MEDIAWIKI_INSTALL')) {
 			global $wgDBname, $wgDBuser, $wgDBpassword, $wgDBsuperuser, $wgDBmwschema,
 				$wgDBts2schema;
-			print "OK</li>\n";
 
 			print "<li>Checking the version of Postgres...";
-			$version = pg_fetch_result($this->doQuery("SELECT version()"),0,0);
-			$thisver = array();
-			if (!preg_match('/PostgreSQL (\d+\.\d+)(\S+)/', $version, $thisver)) {
-				print "<b>FAILED</b> (could not determine the version)</li>\n";
-				dieout("</ul>");
-			}
+			$version = $this->getServerVersion();
 			$PGMINVER = "8.1";
-			if ($thisver[1] < $PGMINVER) {
-				print "<b>FAILED</b>. Required version is $PGMINVER. You have $thisver[1]$thisver[2]</li>\n";
+			if ($this->numeric_version < $PGMINVER) {
+				print "<b>FAILED</b>. Required version is $PGMINVER. You have $this->numeric_version ($version)</li>\n";
 				dieout("</ul>");
 			}
-			print "version $thisver[1]$thisver[2] is OK.</li>\n";
+			print "version $this->numeric_version is OK.</li>\n";
 
 			$safeuser = $this->quote_ident($wgDBuser);
 			## Are we connecting as a superuser for the first time?
@@ -249,13 +247,24 @@
 			## Does this user have the rights to the tsearch2 tables?
 			$ctype = pg_fetch_result($this->doQuery("SHOW lc_ctype"),0,0);
 			print "<li>Checking tsearch2 permissions...";
+			## Let's check all four, just to be safe
+			error_reporting( 0 );
+			$ts2tables = array('cfg','cfgmap','dict','parser');
+			foreach ( $ts2tables AS $tname ) {
+				$SQL = "SELECT count(*) FROM $wgDBts2schema.pg_ts_$tname";
+				$res = $this->doQuery($SQL);
+				if (!$res) {
+					print "<b>FAILED</b> to access pg_ts_$tname. Make sure that the user ".
+					"\"$wgDBuser\" has SELECT access to all four tsearch2 tables</li>\n";
+					dieout("</ul>");
+				}
+			}
 			$SQL = "SELECT ts_name FROM $wgDBts2schema.pg_ts_cfg WHERE locale = '$ctype'";
 			$SQL .= " ORDER BY CASE WHEN ts_name <> 'default' THEN 1 ELSE 0 END";
-			error_reporting( 0 );
 			$res = $this->doQuery($SQL);
 			error_reporting( E_ALL );
 			if (!$res) {
-				print "<b>FAILED</b>. Make sure that the user \"$wgDBuser\" has SELECT access to the tsearch2 tables</li>\n";
+				print "<b>FAILED</b>. Could not determine the tsearch2 locale information</li>\n";
 				dieout("</ul>");
 			}
 			print "OK</li>";
@@ -325,9 +334,13 @@
 			$result = $this->schemaExists($wgDBmwschema);
 			if (!$result) {
 				print "<li>Creating schema <b>$wgDBmwschema</b> ...";
+				error_reporting( 0 );
 				$result = $this->doQuery("CREATE SCHEMA $wgDBmwschema");
+				error_reporting( E_ALL );
 				if (!$result) {
-					print "<b>FAILED</b>.</li>\n";
+					print "<b>FAILED</b>. The user \"$wgDBuser\" must be able to access the schema. ".
+					"You can try making them the owner of the database, or try creating the schema with a ".
+					"different user, and then grant access to the \"$wgDBuser\" user.</li>\n";
 					dieout("</ul>");
 				}
 				print "OK</li>\n";
@@ -707,10 +720,12 @@
 	 * @return string Version information from the database
 	 */
 	function getServerVersion() {
-		$res = $this->query( "SELECT version()" );
-		$row = $this->fetchRow( $res );
-		$version = $row[0];
-		$this->freeResult( $res );
+		$version = pg_fetch_result($this->doQuery("SELECT version()"),0,0);
+		$thisver = array();
+		if (!preg_match('/PostgreSQL (\d+\.\d+)(\S+)/', $version, $thisver)) {
+			die("Could not determine the numeric version from $version!");
+		}
+		$this->numeric_version = $thisver[1];
 		return $version;
 	}
 
@@ -791,10 +806,31 @@
 	}
 
 	function setup_database() {
-		global $wgVersion, $wgDBmwschema, $wgDBts2schema, $wgDBport;
+		global $wgVersion, $wgDBmwschema, $wgDBts2schema, $wgDBport, $wgDBuser;
+
+		## Make sure that we can write to the correct schema
+		## If not, Postgres will happily and silently go to the next search_path item
+		$SQL = "CREATE TABLE $wgDBmwschema.mw_test_table(a int)";
+		error_reporting( 0 );
+		$res = $this->doQuery($SQL);
+		error_reporting( E_ALL );
+		if (!$res) {
+			print "<b>FAILED</b>. Make sure that the user \"$wgDBuser\" can write to the schema \"wgDBmwschema\"</li>\n";
+			dieout("</ul>");
+		}
 
 		dbsource( "../maintenance/postgres/tables.sql", $this);
 
+		## Version-specific stuff
+		if ($this->numeric_version == 8.1) {
+			$this->doQuery("CREATE INDEX ts2_page_text ON pagecontent USING gist(textvector)");
+			$this->doQuery("CREATE INDEX ts2_page_title ON page USING gist(titlevector)");
+		}
+		else {
+			$this->doQuery("CREATE INDEX ts2_page_text ON pagecontent USING gin(textvector)");
+			$this->doQuery("CREATE INDEX ts2_page_title ON page USING gin(titlevector)");
+		}
+
 		## Update version information
 		$mwv = $this->addQuotes($wgVersion);
 		$pgv = $this->addQuotes($this->getServerVersion());
diff -NaurB -x .svn includes/DateFormatter.php /srv/web/fp014/source/includes/DateFormatter.php
--- includes/DateFormatter.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DateFormatter.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,14 +2,12 @@
 /**
  * Date formatter, recognises dates in plain text and formats them accoding to user preferences.
  *
- * @package MediaWiki
- * @subpackage Parser
+ * @addtogroup Parser
  */
 
 /**
  * @todo preferences, OutputPage
- * @package MediaWiki
- * @subpackage Parser
+ * @addtogroup Parser
  */
 class DateFormatter
 {
diff -NaurB -x .svn includes/DefaultSettings.php /srv/web/fp014/source/includes/DefaultSettings.php
--- includes/DefaultSettings.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DefaultSettings.php	2007-02-01 01:04:18.000000000 +0000
@@ -15,7 +15,6 @@
  * Documentation is in the source and on:
  * http://www.mediawiki.org/wiki/Help:Configuration_settings
  *
- * @package MediaWiki
  */
 
 # This is not a valid entry point, perform no further processing unless MEDIAWIKI is defined
@@ -32,7 +31,7 @@
 $wgConf = new SiteConfiguration;
 
 /** MediaWiki version number */
-$wgVersion			= '1.9.1';
+$wgVersion			= '1.10alpha';
 
 /** Name of the site. It must be changed in LocalSettings.php */
 $wgSitename         = 'MediaWiki';
@@ -964,6 +963,7 @@
 $wgGroupPermissions['user' ]['reupload']        = true;
 $wgGroupPermissions['user' ]['reupload-shared'] = true;
 $wgGroupPermissions['user' ]['minoredit']       = true;
+$wgGroupPermissions['user' ]['purge']           = true; // can use ?action=purge without clicking "ok"
 
 // Implicit group for accounts that pass $wgAutoConfirmAge
 $wgGroupPermissions['autoconfirmed']['autoconfirmed'] = true;
@@ -988,7 +988,7 @@
 $wgGroupPermissions['sysop']['importupload']    = true;
 $wgGroupPermissions['sysop']['move']            = true;
 $wgGroupPermissions['sysop']['patrol']          = true;
-$wgGroupPermissions['sysop']['autopatrol']		= true;
+$wgGroupPermissions['sysop']['autopatrol']      = true;
 $wgGroupPermissions['sysop']['protect']         = true;
 $wgGroupPermissions['sysop']['proxyunbannable'] = true;
 $wgGroupPermissions['sysop']['rollback']        = true;
@@ -1029,6 +1029,14 @@
  */
 $wgRestrictionLevels = array( '', 'autoconfirmed', 'sysop' );
 
+/**
+ * Set the minimum permissions required to edit pages in each
+ * namespace.  If you list more than one permission, a user must
+ * have all of them to edit pages in that namespace.
+ */
+$wgNamespaceProtection = array();
+$wgNamespaceProtection[ NS_MEDIAWIKI ] = array( 'editinterface' );
+
 
 /**
  * Number of seconds an account is required to age before
@@ -1045,6 +1053,11 @@
 //$wgAutoConfirmAge = 600;     // ten minutes
 //$wgAutoConfirmAge = 3600*24; // one day
 
+# Number of edits an account requires before it is autoconfirmed
+# Passing both this AND the time requirement is needed
+$wgAutoConfirmCount = 0;
+//$wgAutoConfirmCount = 50;
+
 
 
 # Proxy scanner settings
@@ -1096,7 +1109,7 @@
  * to ensure that client-side caches don't keep obsolete copies of global
  * styles.
  */
-$wgStyleVersion = '42a';
+$wgStyleVersion = '52';
 
 
 # Server-side caching:
@@ -1504,6 +1517,8 @@
 	$wgCommandLineMode = false;
 }
 
+/** For colorized maintenance script output, is your terminal background dark ? */
+$wgCommandLineDarkBg = false;
 
 #
 # Recent changes settings
@@ -1613,7 +1628,17 @@
 /** Text matching this regular expression will be recognised as spam
  * See http://en.wikipedia.org/wiki/Regular_expression */
 $wgSpamRegex = false;
-/** Similarly if this function returns true */
+/** Similarly you can get a function to do the job. The function will be given
+ * the following args:
+ *   - a Title object for the article the edit is made on
+ *   - the text submitted in the textarea (wpTextbox1)
+ *   - the section number.
+ * The return should be boolean indicating whether the edit matched some evilness:
+ *  - true : block it
+ *  - false : let it through
+ *
+ * For a complete example, have a look at the SpamBlacklist extension.
+ */
 $wgFilterCallback = false;
 
 /** Go button goes straight to the edit screen if the article doesn't exist. */
@@ -1660,7 +1685,7 @@
  * Settings added to this array will override the default globals for the user
  * preferences used by anonymous visitors and newly created accounts.
  * For instance, to disable section editing links:
- *  $wgDefaultUserOptions ['editsection'] = 0;
+ * $wgDefaultUserOptions ['editsection'] = 0;
  *
  */
 $wgDefaultUserOptions = array( 
@@ -1985,7 +2010,9 @@
 	'delete',
 	'upload',
 	'move',
-	'import' );
+	'import',
+	'patrol',
+);
 
 /**
  * Lists the message key string for each log type. The localized messages
@@ -2001,7 +2028,9 @@
 	'delete'  => 'dellogpage',
 	'upload'  => 'uploadlogpage',
 	'move'    => 'movelogpage',
-	'import'  => 'importlogpage' );
+	'import'  => 'importlogpage',
+	'patrol'  => 'patrol-log-page',
+);
 
 /**
  * Lists the message key string for descriptive text to be shown at the
@@ -2017,7 +2046,9 @@
 	'delete'  => 'dellogpagetext',
 	'upload'  => 'uploadlogpagetext',
 	'move'    => 'movelogpagetext',
-	'import'  => 'importlogpagetext', );
+	'import'  => 'importlogpagetext',
+	'patrol'  => 'patrol-log-header',
+);
 
 /**
  * Lists the message key string for formatting individual events of each
@@ -2039,7 +2070,8 @@
 	'move/move'         => '1movedto2',
 	'move/move_redir'   => '1movedto2_redir',
 	'import/upload'     => 'import-logentry-upload',
-	'import/interwiki'  => 'import-logentry-interwiki' );
+	'import/interwiki'  => 'import-logentry-interwiki',
+);
 
 /**
  * Experimental preview feature to fetch rendered text
@@ -2281,7 +2313,7 @@
  * during ordinary apache requests. In this case, maintenance/runJobs.php should
  * be run periodically.
  */
-$wgJobRunRate = 1;
+$wgJobRunRate = 0;
 
 /**
  * Number of rows to update per job
@@ -2416,4 +2448,9 @@
  */
 $wgDisableQueryPageUpdate = false;
 
+/**
+ * Set this to false to disable cascading protection
+ */
+$wgEnableCascadingProtection = true;
+
 ?>
diff -NaurB -x .svn includes/Defines.php /srv/web/fp014/source/includes/Defines.php
--- includes/Defines.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Defines.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * A few constants that might be needed during LocalSettings.php
- * @package MediaWiki
  */
 
 /**
diff -NaurB -x .svn includes/DifferenceEngine.php /srv/web/fp014/source/includes/DifferenceEngine.php
--- includes/DifferenceEngine.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DifferenceEngine.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,15 +1,13 @@
 <?php
 /**
  * See diff.doc
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 
 /**
  * @todo document
  * @public
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class DifferenceEngine {
 	/**#@+
@@ -63,8 +61,8 @@
 		$this->mRcidMarkPatrolled = intval($rcid);  # force it to be an integer
 	}
 
-	function showDiffPage() {
-		global $wgUser, $wgOut, $wgContLang, $wgUseExternalEditor, $wgUseRCPatrol;
+	function showDiffPage( $diffOnly = false ) {
+		global $wgUser, $wgOut, $wgUseExternalEditor, $wgUseRCPatrol;
 		$fname = 'DifferenceEngine::showDiffPage';
 		wfProfileIn( $fname );
 
@@ -118,6 +116,7 @@
 		# is the first version of that article. In that case, V' does not exist.
 		if ( $this->mOldid === false ) {
 			$this->showFirstRevision();
+			$this->renderNewRevision();  // should we respect $diffOnly here or not?
 			wfProfileOut( $fname );
 			return;
 		}
@@ -178,14 +177,29 @@
 
 		$oldHeader = "<strong>{$this->mOldtitle}</strong><br />" .
 			$sk->revUserTools( $this->mOldRev ) . "<br />" .
-			$oldminor . $sk->revComment( $this->mOldRev, true ) . "<br />" .
+			$oldminor . $sk->revComment( $this->mOldRev, !$diffOnly ) . "<br />" .
 			$prevlink;
 		$newHeader = "<strong>{$this->mNewtitle}</strong><br />" .
 			$sk->revUserTools( $this->mNewRev ) . " $rollback<br />" .
-			$newminor . $sk->revComment( $this->mNewRev, true ) . "<br />" .
+			$newminor . $sk->revComment( $this->mNewRev, !$diffOnly ) . "<br />" .
 			$nextlink . $patrol;
 
 		$this->showDiff( $oldHeader, $newHeader );
+
+		if ( !$diffOnly )
+			$this->renderNewRevision();
+
+		wfProfileOut( $fname );
+	}
+
+	/**
+	 * Show the new revision of the page.
+	 */
+	function renderNewRevision() {
+		global $wgOut;
+		$fname = 'DifferenceEngine::renderNewRevision';
+		wfProfileIn( $fname );
+
 		$wgOut->addHTML( "<hr /><h2>{$this->mPagetitle}</h2>\n" );
 
 		if( !$this->mNewRev->isCurrent() ) {
@@ -196,7 +210,8 @@
 		if( is_object( $this->mNewRev ) ) {
 			$wgOut->setRevisionId( $this->mNewRev->getId() );
 		}
-		$wgOut->addSecondaryWikiText( $this->mNewtext );
+
+		$wgOut->addWikiTextTidy( $this->mNewtext );
 
 		if( !$this->mNewRev->isCurrent() ) {
 			$wgOut->parserOptions()->setEditSection( $oldEditSectionSetting );
@@ -254,15 +269,6 @@
 		$wgOut->setSubtitle( wfMsg( 'difference' ) );
 		$wgOut->setRobotpolicy( 'noindex,nofollow' );
 
-
-		# Show current revision
-		#
-		$wgOut->addHTML( "<hr /><h2>{$this->mPagetitle}</h2>\n" );
-		if( is_object( $this->mNewRev ) ) {
-			$wgOut->setRevisionId( $this->mNewRev->getId() );
-		}
-		$wgOut->addSecondaryWikiText( $this->mNewtext );
-
 		wfProfileOut( $fname );
 	}
 
@@ -596,6 +602,18 @@
 		}
 		if ( $this->mNewRev ) {
 			$this->mNewtext = $this->mNewRev->getText();
+
+			/** Added by corfix@wikia.com
+			 *  Probably used in SiteWideMessages extension
+			 *  We really need to change it - it was added in bad way
+			 */
+			$tMsgs = false;
+			wfRunHooks ('GetUserMessages', Array ($this->mTitle, & $tMsgs));
+
+			if ($tMsgs != false) {
+				$this->mNewtext .= $tMsgs;
+			}
+
 			if ( $this->mNewtext === false ) {
 				return false;
 			}
@@ -616,6 +634,18 @@
 			return false;
 		}
 		$this->mNewtext = $this->mNewRev->getText();
+
+		/** Added by corfix@wikia.com
+		 *  Probably used in SiteWideMessages extension
+		 *  We really need to change it - it was added in bad way
+		 */
+		$tMsgs = false;
+		wfRunHooks ('GetUserMessages', Array ($this->mTitle, & $tMsgs));
+
+		if ($tMsgs != false) {
+			$this->mNewtext .= $tMsgs;
+		}
+
 		return true;
 	}
 
@@ -633,8 +663,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffOp {
 	var $type;
@@ -657,8 +686,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffOp_Copy extends _DiffOp {
 	var $type = 'copy';
@@ -678,8 +706,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffOp_Delete extends _DiffOp {
 	var $type = 'delete';
@@ -697,8 +724,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffOp_Add extends _DiffOp {
 	var $type = 'add';
@@ -716,8 +742,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffOp_Change extends _DiffOp {
 	var $type = 'change';
@@ -754,8 +779,7 @@
  *
  * @author Geoffrey T. Dairiki, Tim Starling
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _DiffEngine
 {
@@ -1176,8 +1200,7 @@
  * Class representing a 'diff' between two sequences of strings.
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class Diff
 {
@@ -1318,8 +1341,7 @@
  * FIXME: bad name.
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class MappedDiff extends Diff
 {
@@ -1382,8 +1404,7 @@
  * to obtain fancier outputs.
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class DiffFormatter
 {
@@ -1549,8 +1570,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class _HWLDF_WordAccumulator {
 	function _HWLDF_WordAccumulator () {
@@ -1608,8 +1628,7 @@
 /**
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class WordLevelDiff extends MappedDiff
 {
@@ -1697,8 +1716,7 @@
  *	Wikipedia Table style diff formatter.
  * @todo document
  * @private
- * @package MediaWiki
- * @subpackage DifferenceEngine
+ * @addtogroup DifferenceEngine
  */
 class TableDiffFormatter extends DiffFormatter
 {
diff -NaurB -x .svn includes/DjVuImage.php /srv/web/fp014/source/includes/DjVuImage.php
--- includes/DjVuImage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/DjVuImage.php	2007-02-01 01:03:18.000000000 +0000
@@ -25,7 +25,6 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
  */
 
 class DjVuImage {
@@ -68,6 +67,7 @@
 	function dump() {
 		$file = fopen( $this->mFilename, 'rb' );
 		$header = fread( $file, 12 );
+		// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 		extract( unpack( 'a4magic/a4chunk/NchunkLength', $header ) );
 		echo "$chunk $chunkLength\n";
 		$this->dumpForm( $file, $chunkLength, 1 );
@@ -83,6 +83,7 @@
 			if( $chunkHeader == '' ) {
 				break;
 			}
+			// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 			extract( unpack( 'a4chunk/NchunkLength', $chunkHeader ) );
 			echo str_repeat( ' ', $indent * 4 ) . "$chunk $chunkLength\n";
 			
@@ -111,6 +112,7 @@
 		if( strlen( $header ) < 16 ) {
 			wfDebug( __METHOD__ . ": too short file header\n" );
 		} else {
+			// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 			extract( unpack( 'a4magic/a4form/NformLength/a4subtype', $header ) );
 			
 			if( $magic != 'AT&T' ) {
@@ -134,6 +136,7 @@
 		if( strlen( $header ) < 8 ) {
 			return array( false, 0 );
 		} else {
+			// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 			extract( unpack( 'a4chunk/Nlength', $header ) );
 			return array( $chunk, $length );
 		}
@@ -192,6 +195,7 @@
 			return false;
 		}
 		
+		// FIXME: Would be good to replace this extract() call with something that explicitly initializes local variables.
 		extract( unpack(
 			'nwidth/' .
 			'nheight/' .
diff -NaurB -x .svn includes/EditPage.php /srv/web/fp014/source/includes/EditPage.php
--- includes/EditPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/EditPage.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Contain the EditPage class
- * @package MediaWiki
  */
 
 /**
@@ -10,7 +9,6 @@
  * but it should get easier to call those from alternate
  * interfaces.
  *
- * @package MediaWiki
  */
 
 class EditPage {
@@ -40,6 +38,7 @@
 	var $textbox1 = '', $textbox2 = '', $summary = '';
 	var $edittime = '', $section = '', $starttime = '';
 	var $oldid = 0, $editintro = '', $scrolltop = null;
+	var $autosave = false; // by emil (2006-08-14)
 
 	# Placeholders for text injection by hooks (must be HTML)
 	# extensions should take care to _append_ to the present value
@@ -81,10 +80,13 @@
 
 		$text = '';
 		if( !$this->mTitle->exists() ) {
-
-			# If requested, preload some text.
-			$text = $this->getPreloadedText( $preload );
-
+			if ( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
+				# If this is a system message, get the default text. 
+				$text = wfMsgWeirdKey ( $this->mTitle->getText() ) ;
+			} else {
+				# If requested, preload some text.
+				$text = $this->getPreloadedText( $preload );
+			}
 			# We used to put MediaWiki:Newarticletext here if
 			# $text was empty at this point.
 			# This is now shown above the edit box instead.
@@ -94,7 +96,7 @@
 			// fetch the page record from the high-priority server,
 			// which is needed to guarantee we don't pick up lagged
 			// information.
-			
+
 			$text = $this->mArticle->getContent();
 
 			if ( $undo > 0 ) {
@@ -117,7 +119,7 @@
 						$text = $oldrev_text;
 						$result = true;
 					}
-					
+
 					if( $result ) {
 						# Inform the user of our success and set an automatic edit summary
 						$this->editFormPageTop .= $wgOut->parse( wfMsgNoTrans( 'undo-success' ) );
@@ -127,7 +129,7 @@
 						# Warn the user that something went wrong
 						$this->editFormPageTop .= $wgOut->parse( wfMsgNoTrans( 'undo-failure' ) );
 					}
-	
+
 				}
 			}
 			else if( $section != '' ) {
@@ -138,7 +140,7 @@
 				}
 			}
 		}
-		
+
 		wfProfileOut( __METHOD__ );
 		return $text;
 	}
@@ -301,7 +303,7 @@
 			return;
 		}
 
-		if ( ! $this->mTitle->userCanEdit() ) {
+		if ( ! $this->mTitle->userCan( 'edit' ) ) {
 			wfDebug( "$fname: user can't edit\n" );
 			$wgOut->readOnlyPage( $this->getContent(), true );
 			wfProfileOut( $fname );
@@ -335,7 +337,7 @@
 			wfProfileOut($fname);
 			return;
 		}
-		if ( !$this->mTitle->userCanCreate() && !$this->mTitle->exists() ) {
+		if ( !$this->mTitle->userCan( 'create' ) && !$this->mTitle->exists() ) {
 			wfDebug( "$fname: no create permission\n" );
 			$this->noCreatePermission();
 			wfProfileOut( $fname );
@@ -366,6 +368,8 @@
 				} else {
 					$this->extractMetaDataFromArticle () ;
 					$this->formtype = 'initial';
+
+					$this->autosave = $wgRequest->getBool('autosave'); // by emil (2006-08-14)
 				}
 			}
 		}
@@ -482,7 +486,7 @@
 				// Remember whether a save was requested, so we can indicate
 				// if we forced preview due to session failure.
 				$this->mTriedSave = !$this->preview;
-				
+
 				if ( $this->tokenOk( $request ) ) {
 					# Some browsers will not report any submit button
 					# if the user hits enter in the comment box.
@@ -519,8 +523,8 @@
 			} else {
 				$this->allowBlankSummary = $request->getBool( 'wpIgnoreBlankSummary' );
 			}
-	
-			$this->autoSumm = $request->getText( 'wpAutoSummary' );			
+
+			$this->autoSumm = $request->getText( 'wpAutoSummary' );	
 		} else {
 			# Not a posted form? Start with nothing.
 			wfDebug( "$fname: Not a posted form.\n" );
@@ -652,7 +656,7 @@
 			wfProfileOut( $fname );
 			return true;
 		}
-		
+
 		if ( !$wgUser->isAllowed('edit') ) {
 			if ( $wgUser->isAnon() ) {
 				$this->userNotLoggedInPage();
@@ -696,7 +700,7 @@
 		if ( 0 == $aid ) {
 
 			// Late check for create permission, just in case *PARANOIA*
-			if ( !$this->mTitle->userCanCreate() ) {
+			if ( !$this->mTitle->userCan( 'create' ) ) {
 				wfDebug( "$fname: no create permission\n" );
 				$this->noCreatePermission();
 				wfProfileOut( $fname );
@@ -862,7 +866,7 @@
 		$this->summary = '';
 		$this->textbox1 = $this->getContent();
 		if ( !$this->mArticle->exists() && $this->mArticle->mTitle->getNamespace() == NS_MEDIAWIKI )
-			$this->textbox1 = wfMsgWeirdKey( $this->mArticle->mTitle->getText() ) ;
+			$this->textbox1 = wfMsgWeirdKey( $this->mArticle->mTitle->getText() );
 		wfProxyCheck();
 	}
 
@@ -878,7 +882,7 @@
 		$fname = 'EditPage::showEditForm';
 		wfProfileIn( $fname );
 
-		$sk =& $wgUser->getSkin();
+		$sk = $wgUser->getSkin();
 
 		wfRunHooks( 'EditPage::showEditForm:initial', array( &$this ) ) ;
 
@@ -920,7 +924,7 @@
 			if ( $this->missingComment ) {
 				$wgOut->addWikiText( wfMsg( 'missingcommenttext' ) );
 			}
-			
+
 			if( $this->missingSummary && $this->section != 'new' ) {
 				$wgOut->addWikiText( wfMsg( 'missingsummary' ) );
 			}
@@ -928,7 +932,7 @@
                         if( $this->missingSummary && $this->section == 'new' ) {
                                 $wgOut->addWikiText( wfMsg( 'missingcommentheader' ) );
                         }
-			
+
 			if( !$this->hookError == '' ) {
 				$wgOut->addWikiText( $this->hookError );
 			}
@@ -958,21 +962,28 @@
 				}
 			}
 		}
-			
-		if( $this->mTitle->isProtected( 'edit' ) ) {
-			# Is the protection due to the namespace, e.g. interface text?
-			if( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
-				# Yes; remind the user
-				$notice = wfMsg( 'editinginterface' );
-			} elseif( $this->mTitle->isSemiProtected() ) {
-				# No; semi protected
+
+		if( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
+			# Show a warning if editing an interface message
+			$wgOut->addWikiText( wfMsg( 'editinginterface' ) );
+		} elseif( $this->mTitle->isProtected( 'edit' ) ) {
+			# Is the title semi-protected?
+			if( $this->mTitle->isSemiProtected() ) {
 				$notice = wfMsg( 'semiprotectedpagewarning' );
-				if( wfEmptyMsg( 'semiprotectedpagewarning', $notice ) || $notice == '-' ) {
+				if( wfEmptyMsg( 'semiprotectedpagewarning', $notice ) || $notice == '-' )
 					$notice = '';
-				}
 			} else {
-				# No; regular protection
-				$notice = wfMsg( 'protectedpagewarning' );
+				# It's either cascading protection or regular protection; work out which
+				$cascadeSources = $this->mTitle->getCascadeProtectionSources();
+				if( $cascadeSources && count( $cascadeSources ) > 0 ) {
+					# Cascading protection; explain, and list the titles responsible
+					$notice = wfMsg( 'cascadeprotectedwarning' ) . "\n";
+					foreach( $cascadeSources as $source )
+						$notice .= '* [[:' . $source->getPrefixedText() . "]]\n";
+				} else {
+					# Regular protection
+					$notice = wfMsg( 'protectedpagewarning' );
+				}
 			}
 			$wgOut->addWikiText( $notice );
 		}
@@ -1041,7 +1052,7 @@
 				# Already watched
 				$this->watchthis = true;
 			}
-			
+
 			if( $wgUser->getOption( 'minordefault' ) ) $this->minoredit = true;
 		}
 
@@ -1051,7 +1062,7 @@
 			$minoredithtml =
 				"<input tabindex='3' type='checkbox' value='1' name='wpMinoredit'".($this->minoredit?" checked='checked'":"").
 				" accesskey='".wfMsg('accesskey-minoredit')."' id='wpMinoredit' />\n".
-				"<label for='wpMinoredit' title='".wfMsg('tooltip-minoredit')."'>{$minor}</label>\n";
+				"<label for='wpMinoredit'".$sk->tooltipAndAccesskey('minoredit').">{$minor}</label>\n";
 		}
 
 		$watchhtml = '';
@@ -1060,8 +1071,7 @@
 			$watchhtml = "<input tabindex='4' type='checkbox' name='wpWatchthis'".
 				($this->watchthis?" checked='checked'":"").
 				" accesskey=\"".htmlspecialchars(wfMsg('accesskey-watch'))."\" id='wpWatchthis'  />\n".
-				"<label for='wpWatchthis' title=\"" .
-					htmlspecialchars(wfMsg('tooltip-watch'))."\">{$watchthis}</label>\n";
+				"<label for='wpWatchthis'".$sk->tooltipAndAccesskey('watch').">{$watchthis}</label>\n";
 		}
 
 		$checkboxhtml = $minoredithtml . $watchhtml;
@@ -1139,7 +1149,7 @@
 			'tabindex'  => '5',
 			'value'     => wfMsg('savearticle'),
 			'accesskey' => wfMsg('accesskey-save'),
-			'title'     => wfMsg('tooltip-save'),
+			'title'     => wfMsg( 'tooltip-save' ).' ['.wfMsg( 'accesskey-save' ).']',
 		);
 		$buttons['save'] = wfElement('input', $temp, '');
 		$temp = array(
@@ -1149,7 +1159,7 @@
 			'tabindex'  => '7',
 			'value'     => wfMsg('showdiff'),
 			'accesskey' => wfMsg('accesskey-diff'),
-			'title'     => wfMsg('tooltip-diff'),
+			'title'     => wfMsg( 'tooltip-diff' ).' ['.wfMsg( 'accesskey-diff' ).']',
 		);
 		$buttons['diff'] = wfElement('input', $temp, '');
 
@@ -1162,7 +1172,7 @@
 				'tabindex'  => '6',
 				'value'     => wfMsg('showpreview'),
 				'accesskey' => '',
-				'title'     => wfMsg('tooltip-preview'),
+				'title'     => wfMsg( 'tooltip-preview' ).' ['.wfMsg( 'accesskey-preview' ).']',
 				'style'     => 'display: none;',
 			);
 			$buttons['preview'] = wfElement('input', $temp, '');
@@ -1185,7 +1195,7 @@
 				'tabindex'  => '6',
 				'value'     => wfMsg('showpreview'),
 				'accesskey' => wfMsg('accesskey-preview'),
-				'title'     => wfMsg('tooltip-preview'),
+				'title'     => wfMsg( 'tooltip-preview' ).' ['.wfMsg( 'accesskey-preview' ).']',
 			);
 			$buttons['preview'] = wfElement('input', $temp, '');
 			$buttons['live'] = '';
@@ -1282,7 +1292,7 @@
 		if( $this->missingSummary ) {
 			$wgOut->addHTML( "<input type=\"hidden\" name=\"wpIgnoreBlankSummary\" value=\"1\" />\n" );
 		}
-		
+
 		# For a bit more sophisticated detection of blank summaries, hash the
 		# automatic one and pass that in a hidden field.
 		$autosumm = $this->autoSumm ? $this->autoSumm : md5( $this->summary );
@@ -1308,13 +1318,17 @@
 			} else {
 				$wgOut->addHTML( '<div id="wikiPreview"></div>' );
 			}
-		
+
 			if ( $this->formtype == 'diff') {
 				$wgOut->addHTML( $this->getDiff() );
 			}
 
 		}
 
+		if ( $this->autosave ) { // by emil (2006-08-14)
+			$wgOut->addHTML( "<script type='text/javascript'>document.editform.wpPreview.click();</script>" );
+		}
+
 		wfProfileOut( $fname );
 	}
 
@@ -1361,7 +1375,7 @@
 	}
 
 	function getLastDelete() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$fname = 'EditPage::getLastDelete';
 		$res = $dbr->select(
 			array( 'logging', 'user' ),
@@ -1425,7 +1439,7 @@
 
 		# don't parse user css/js, show message about preview
 		# XXX: stupid php bug won't let us use $wgTitle->isCssJsSubpage() here
-		
+
 		if ( $this->isCssJsSubpage ) {
 			if(preg_match("/\\.css$/", $wgTitle->getText() ) ) {
 				$previewtext = wfMsg('usercsspreview');
@@ -1469,16 +1483,16 @@
 	function blockedPage() {
 		global $wgOut, $wgUser;
 		$wgOut->blockedPage( false ); # Standard block notice on the top, don't 'return'
-		
+
 		# If the user made changes, preserve them when showing the markup
-		# (This happens when a user is blocked during edit, for instance)		
+		# (This happens when a user is blocked during edit, for instance)
 		$first = $this->firsttime || ( !$this->save && $this->textbox1 == '' );
 		if( $first ) {
 			$source = $this->mTitle->exists() ? $this->getContent() : false;
 		} else {
 			$source = $this->textbox1;
 		}
-	
+
 		# Spit out the source or the user's modified version
 		if( $source !== false ) {
 			$rows = $wgUser->getOption( 'rows' );
@@ -1496,14 +1510,14 @@
 	function userNotLoggedInPage() {
 		global $wgUser, $wgOut;
 		$skin = $wgUser->getSkin();
-		
+
 		$loginTitle = SpecialPage::getTitleFor( 'Userlogin' );
 		$loginLink = $skin->makeKnownLinkObj( $loginTitle, wfMsgHtml( 'loginreqlink' ), 'returnto=' . $this->mTitle->getPrefixedUrl() );
-	
+
 		$wgOut->setPageTitle( wfMsg( 'whitelistedittitle' ) );
 		$wgOut->setRobotPolicy( 'noindex,nofollow' );
 		$wgOut->setArticleRelated( false );
-		
+
 		$wgOut->addHtml( wfMsgWikiHtml( 'whitelistedittext', $loginLink ) );
 		$wgOut->returnToMain( false, $this->mTitle->getPrefixedUrl() );
 	}
@@ -1519,7 +1533,7 @@
 		$wgOut->setPageTitle( wfMsg( 'confirmedittitle' ) );
 		$wgOut->setRobotPolicy( 'noindex,nofollow' );
 		$wgOut->setArticleRelated( false );
-		
+
 		$wgOut->addWikiText( wfMsg( 'confirmedittext' ) );
 		$wgOut->returnToMain( false );
 	}
@@ -1539,7 +1553,7 @@
 		$wgOut->addWikiText( wfMsg( 'spamprotectiontext' ) );
 		if ( $match )
 			$wgOut->addWikiText( wfMsg( 'spamprotectionmatch', "<nowiki>{$match}</nowiki>" ) );
-			
+
 		$wgOut->returnToMain( false );
 	}
 
@@ -1551,7 +1565,7 @@
 		$fname = 'EditPage::mergeChangesInto';
 		wfProfileIn( $fname );
 
-		$db =& wfGetDB( DB_MASTER );
+		$db = wfGetDB( DB_MASTER );
 
 		// This is the revision the editor started from
 		$baseRevision = Revision::loadFromTimestamp(
@@ -1724,6 +1738,12 @@
 					'key'	=>	'R'
 				)
 		);
+
+		/** Added by inez@wikia.com
+		 *  Should be used to add custom buttons to editor toolbar on editpage
+		 */
+		wfRunHooks( 'ToolbarGenerate', array( &$toolarray ) );
+
 		$toolbar = "<div id='toolbar'>\n";
 		$toolbar.="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";
 
diff -NaurB -x .svn includes/Exif.php /srv/web/fp014/source/includes/Exif.php
--- includes/Exif.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Exif.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage Metadata
+ * @addtogroup Metadata
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -27,8 +26,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage Metadata
+ * @addtogroup Metadata
  */
 class Exif {
 	//@{
@@ -106,7 +104,7 @@
 	 *
 	 * @param $file String: filename.
 	 */
-	function Exif( $file ) {
+	function __construct( $file ) {
 		/**
 		 * Page numbers here refer to pages in the EXIF 2.2 standard
 		 *
@@ -599,8 +597,7 @@
 }
 
 /**
- * @package MediaWiki
- * @subpackage Metadata
+ * @addtogroup Metadata
  */
 class FormatExif {
 	/**
diff -NaurB -x .svn includes/Export.php /srv/web/fp014/source/includes/Export.php
--- includes/Export.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Export.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,14 +19,13 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class WikiExporter {
 	var $list_authors = false ; # Return distinct author list (when not returning full history)
 	var $author_list = "" ;
-	
+
 	const FULL = 0;
 	const CURRENT = 1;
 
@@ -44,14 +43,14 @@
 	 * main query is still running.
 	 *
 	 * @param Database $db
-	 * @param mixed $history one of WikiExporter::FULL or WikiExporter::CURRENT, or an 
+	 * @param mixed $history one of WikiExporter::FULL or WikiExporter::CURRENT, or an
 	 *                       associative array:
 	 *                         offset: non-inclusive offset at which to start the query
 	 *                         limit: maximum number of rows to return
 	 *                         dir: "asc" or "desc" timestamp order
 	 * @param int $buffer one of WikiExporter::BUFFER or WikiExporter::STREAM
 	 */
-	function WikiExporter( &$db, $history = WikiExporter::CURRENT,
+	function __construct( &$db, $history = WikiExporter::CURRENT,
 			$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT ) {
 		$this->db =& $db;
 		$this->history = $history;
@@ -164,10 +163,10 @@
 		$page     = $this->db->tableName( 'page' );
 		$revision = $this->db->tableName( 'revision' );
 		$text     = $this->db->tableName( 'text' );
-		
+
 		$order = 'ORDER BY page_id';
 		$limit = '';
-		
+
 		if( $this->history == WikiExporter::FULL ) {
 			$join = 'page_id=rev_page';
 		} elseif( $this->history == WikiExporter::CURRENT ) {
@@ -185,7 +184,7 @@
 				$order .= ', rev_timestamp DESC';
 			}
 			if ( !empty( $this->history['offset'] ) ) {
-				$join .= " AND rev_timestamp $op " . $this->db->addQuotes( 
+				$join .= " AND rev_timestamp $op " . $this->db->addQuotes(
 					$this->db->timestamp( $this->history['offset'] ) );
 			}
 			if ( !empty( $this->history['limit'] ) ) {
@@ -229,7 +228,7 @@
 		$result = $this->db->query( $sql, $fname );
 		$wrapper = $this->db->resultObject( $result );
 		$this->outputStream( $wrapper );
-		
+
 		if ( $this->list_authors ) {
 			$this->outputStream( $wrapper );
 		}
diff -NaurB -x .svn includes/ExternalEdit.php /srv/web/fp014/source/includes/ExternalEdit.php
--- includes/ExternalEdit.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ExternalEdit.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,12 +3,10 @@
  * License: Public domain
  *
  * @author Erik Moeller <moeller@scireview.de>
- * @package MediaWiki
  */
 
 /**
  *
- * @package MediaWiki
  *
  * Support for external editors to modify both text and files
  * in external applications. It works as follows: MediaWiki
@@ -22,7 +20,7 @@
 
 class ExternalEdit {
 
-	function ExternalEdit ( $article, $mode ) {
+	function __construct( $article, $mode ) {
 		global $wgInputEncoding;
 		$this->mArticle =& $article;
 		$this->mTitle =& $article->mTitle;
diff -NaurB -x .svn includes/ExternalStoreDB.php /srv/web/fp014/source/includes/ExternalStoreDB.php
--- includes/ExternalStoreDB.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ExternalStoreDB.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
  *
  * DB accessable external objects
  *
  */
 
 
-/** @package MediaWiki */
 
 /**
  * External database storage will use one (or more) separate connection pools
diff -NaurB -x .svn includes/ExternalStoreHttp.php /srv/web/fp014/source/includes/ExternalStoreHttp.php
--- includes/ExternalStoreHttp.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ExternalStoreHttp.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  *
- * @package MediaWiki
  *
  * Example class for HTTP accessable external objects
  *
diff -NaurB -x .svn includes/ExternalStore.php /srv/web/fp014/source/includes/ExternalStore.php
--- includes/ExternalStore.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ExternalStore.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  *
- * @package MediaWiki
  *
  * Constructor class for data kept in external repositories
  *
diff -NaurB -x .svn includes/FakeTitle.php /srv/web/fp014/source/includes/FakeTitle.php
--- includes/FakeTitle.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/FakeTitle.php	2007-02-01 01:03:18.000000000 +0000
@@ -41,6 +41,7 @@
 	function isProtected() { $this->error(); }
 	function userIsWatching() { $this->error(); }
 	function userCan() { $this->error(); }
+	function userCanCreate() { $this->error(); }
 	function userCanEdit() { $this->error(); }
 	function userCanMove() { $this->error(); }
 	function isMovable() { $this->error(); }
@@ -71,7 +72,6 @@
 	function moveOverExistingRedirect() { $this->error(); }
 	function moveToNewTitle() { $this->error(); }
 	function isValidMoveTarget() { $this->error(); }
-	function createRedirect() { $this->error(); }
 	function getParentCategories() { $this->error(); }
 	function getParentCategoryTree() { $this->error(); }
 	function pageCond() { $this->error(); }
diff -NaurB -x .svn includes/Feed.php /srv/web/fp014/source/includes/Feed.php
--- includes/Feed.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Feed.php	2007-02-01 01:03:18.000000000 +0000
@@ -22,13 +22,11 @@
 /**
  * Contain a feed class as well as classes to build rss / atom ... feeds
  * Available feeds are defined in Defines.php
- * @package MediaWiki
  */
 
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class FeedItem {
 	/**#@+
@@ -45,7 +43,7 @@
 	/**#@+
 	 * @todo document
 	 */
-	function FeedItem( $Title, $Description, $Url, $Date = '', $Author = '', $Comments = '' ) {
+	function __construct( $Title, $Description, $Url, $Date = '', $Author = '', $Comments = '' ) {
 		$this->Title = $Title;
 		$this->Description = $Description;
 		$this->Url = $Url;
@@ -78,7 +76,6 @@
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class ChannelFeed extends FeedItem {
 	/**#@+
@@ -161,7 +158,6 @@
 /**
  * Generate a RSS feed
  * @todo document
- * @package MediaWiki
  */
 class RSSFeed extends ChannelFeed {
 
@@ -222,7 +218,6 @@
 /**
  * Generate an Atom feed
  * @todo document
- * @package MediaWiki
  */
 class AtomFeed extends ChannelFeed {
 	/**
diff -NaurB -x .svn includes/GlobalFunctions.php /srv/web/fp014/source/includes/GlobalFunctions.php
--- includes/GlobalFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/GlobalFunctions.php	2007-02-01 01:04:18.000000000 +0000
@@ -2,7 +2,6 @@
 
 /**
  * Global functions used everywhere
- * @package MediaWiki
  */
 
 /**
@@ -376,8 +375,11 @@
  * @return String: the requested message.
  */
 function wfMsgReal( $key, $args, $useDB = true, $forContent=false, $transform = true ) {
+	$fname = 'wfMsgReal';
+	wfProfileIn( $fname );
 	$message = wfMsgGetKey( $key, $useDB, $forContent, $transform );
 	$message = wfMsgReplaceArgs( $message, $args );
+	wfProfileOut( $fname );
 	return $message;
 }
 
@@ -575,7 +577,7 @@
 	global $wgLoadBalancer;
 	static $called = false;
 	if ( $called ){
-		exit( -1 );
+		exit( intval ( $error ) );
 	}
 	$called = true;
 
@@ -595,7 +597,7 @@
 	if ( !$error ) {
 		$wgLoadBalancer->closeAll();
 	}
-	exit( -1 );
+	exit( intval ( $error ) );
 }
 
 /**
@@ -1624,6 +1626,7 @@
 	foreach ( $createList as $dir ) {
 		# use chmod to override the umask, as suggested by the PHP manual
 		if ( !mkdir( $dir, $mode ) || !chmod( $dir, $mode ) ) {
+			wfDebugLog( 'mkdir', "Unable to create directory $dir\n" );
 			return false;
 		} 
 	}
@@ -1844,6 +1847,41 @@
 }
 
 /**
+ * Generate a relative path name to the given file.
+ * May explode on non-matching case-insensitive paths,
+ * funky symlinks, etc.
+ *
+ * @param string $path Absolute destination path including target filename
+ * @param string $from Absolute source path, directory only
+ * @return string
+ */
+function wfRelativePath( $path, $from ) {
+	// Normalize mixed input on Windows...
+	$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );
+	$from = str_replace( '/', DIRECTORY_SEPARATOR, $from );
+	
+	$pieces  = explode( DIRECTORY_SEPARATOR, dirname( $path ) );
+	$against = explode( DIRECTORY_SEPARATOR, $from );
+
+	// Trim off common prefix
+	while( count( $pieces ) && count( $against )
+		&& $pieces[0] == $against[0] ) {
+		array_shift( $pieces );
+		array_shift( $against );
+	}
+
+	// relative dots to bump us to the parent
+	while( count( $against ) ) {
+		array_unshift( $pieces, '..' );
+		array_shift( $against );
+	}
+
+	array_push( $pieces, wfBaseName( $path ) );
+
+	return implode( DIRECTORY_SEPARATOR, $pieces );
+}
+
+/**
  * Make a URL index, appropriate for the el_index field of externallinks.
  */
 function wfMakeUrlIndex( $url ) {
diff -NaurB -x .svn includes/HistoryBlob.php /srv/web/fp014/source/includes/HistoryBlob.php
--- includes/HistoryBlob.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/HistoryBlob.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  *
- * @package MediaWiki
  */
 
 /**
  * Pure virtual parent
- * @package MediaWiki
  */
 class HistoryBlob
 {
@@ -50,7 +48,6 @@
 
 /**
  * The real object
- * @package MediaWiki
  */
 class ConcatenatedGzipHistoryBlob extends HistoryBlob
 {
@@ -179,7 +176,6 @@
 
 
 /**
- * @package MediaWiki
  */
 class HistoryBlobStub {
 	var $mOldId, $mHash, $mRef;
@@ -218,7 +214,7 @@
 		if( isset( $wgBlobCache[$this->mOldId] ) ) {
 			$obj = $wgBlobCache[$this->mOldId];
 		} else {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$row = $dbr->selectRow( 'text', array( 'old_flags', 'old_text' ), array( 'old_id' => $this->mOldId ) );
 			if( !$row ) {
 				return false;
@@ -274,7 +270,6 @@
  * Serialized HistoryBlobCurStub objects will be inserted into the text table
  * on conversion if $wgFastSchemaUpgrades is set to true.
  *
- * @package MediaWiki
  */
 class HistoryBlobCurStub {
 	var $mCurId;
@@ -294,7 +289,7 @@
 
 	/** @todo document */
 	function getText() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$row = $dbr->selectRow( 'cur', array( 'cur_text' ), array( 'cur_id' => $this->mCurId ) );
 		if( !$row ) {
 			return false;
diff -NaurB -x .svn includes/Hooks.php /srv/web/fp014/source/includes/Hooks.php
--- includes/Hooks.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Hooks.php	2007-02-01 01:03:18.000000000 +0000
@@ -18,7 +18,6 @@
  *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
  *
  * @author Evan Prodromou <evan@wikitravel.org>
- * @package MediaWiki
  * @see hooks.txt
  */
 
diff -NaurB -x .svn includes/HTMLCacheUpdate.php /srv/web/fp014/source/includes/HTMLCacheUpdate.php
--- includes/HTMLCacheUpdate.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/HTMLCacheUpdate.php	2007-02-01 01:03:18.000000000 +0000
@@ -38,7 +38,7 @@
 	function doUpdate() {
 		# Fetch the IDs
 		$cond = $this->getToCondition();
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( $this->mTable, $this->getFromField(), $cond, __METHOD__ );
 		$resWrap = new ResultWrapper( $dbr, $res );
 		if ( $dbr->numRows( $res ) != 0 ) {
@@ -136,7 +136,7 @@
 			return;
 		}
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$timestamp = $dbw->timestamp();
 		$done = false;
 		
@@ -218,7 +218,7 @@
 			$conds[] = "$fromField <= {$this->end}";
 		}
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( $this->table, $fromField, $conds, __METHOD__ );
 		$update->invalidateIDs( new ResultWrapper( $dbr, $res ) );
 		$dbr->freeResult( $res );
diff -NaurB -x .svn includes/HTMLFileCache.php /srv/web/fp014/source/includes/HTMLFileCache.php
--- includes/HTMLFileCache.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/HTMLFileCache.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  * Contain the HTMLFileCache class
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 
 /**
@@ -16,7 +15,6 @@
  * $wgUseFileCache
  * $wgFileCacheDirectory
  * $wgUseGzip
- * @package MediaWiki
  */
 class HTMLFileCache {
 	var $mTitle, $mFileCache;
diff -NaurB -x .svn includes/HTMLForm.php /srv/web/fp014/source/includes/HTMLForm.php
--- includes/HTMLForm.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/HTMLForm.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,13 +2,11 @@
 /**
  * This file contain a class to easily build HTML forms as well as custom
  * functions used by SpecialUserrights.php
- * @package MediaWiki
  */
 
 /**
  * Class to build various forms
  *
- * @package MediaWiki
  * @author jeluf, hashar
  */
 class HTMLForm {
diff -NaurB -x .svn includes/ImageFunctions.php /srv/web/fp014/source/includes/ImageFunctions.php
--- includes/ImageFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ImageFunctions.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,7 +21,7 @@
 }
 
 /**
- * Returns the image directory of an image's thubnail
+ * Returns the image directory of an image's thumbnail
  * The result is an absolute path.
  *
  * This function is called from thumb.php before Setup.php is included
diff -NaurB -x .svn includes/ImageGallery.php /srv/web/fp014/source/includes/ImageGallery.php
--- includes/ImageGallery.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ImageGallery.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,7 +3,6 @@
 	die( 1 );
 
 /**
- * @package MediaWiki
  */
 
 /**
@@ -11,7 +10,6 @@
  *
  * Add images to the gallery using add(), then render that list to HTML using toHTML().
  *
- * @package MediaWiki
  */
 class ImageGallery
 {
@@ -23,11 +21,17 @@
 	 * Is the gallery on a wiki page (i.e. not a special page)
 	 */
 	var $mParsing;
+	
+	/**
+	 * Contextual title, used when images are being screened
+	 * against the bad image list
+	 */
+	private $contextTitle = false;
 
 	/**
 	 * Create a new image gallery object.
 	 */
-	function ImageGallery( ) {
+	function __construct( ) {
 		$this->mImages = array();
 		$this->mShowBytes = true;
 		$this->mShowFilename = true;
@@ -65,7 +69,7 @@
 	 * @param $skin Skin object
 	 */
 	function useSkin( $skin ) {
-		$this->mSkin =& $skin;
+		$this->mSkin = $skin;
 	}
 	
 	/**
@@ -76,9 +80,9 @@
 	function getSkin() {
 		if( !$this->mSkin ) {
 			global $wgUser;
-			$skin =& $wgUser->getSkin();
+			$skin = $wgUser->getSkin();
 		} else {
-			$skin =& $this->mSkin;
+			$skin = $this->mSkin;
 		}
 		return $skin;
 	}
@@ -161,8 +165,7 @@
 			if( $nt->getNamespace() != NS_IMAGE ) {
 				# We're dealing with a non-image, spit out the name and be done with it.
 				$thumbhtml = '<div style="height: 152px;">' . htmlspecialchars( $nt->getText() ) . '</div>';
- 			}
-			else if( $this->mParsing && wfIsBadImage( $nt->getDBkey() ) ) {
+ 			} elseif( $this->mParsing && wfIsBadImage( $nt->getDBkey(), $this->getContextTitle() ) ) {
 				# The image is blacklisted, just show it as a text link.
 				$thumbhtml = '<div style="height: 152px;">'
 					. $sk->makeKnownLinkObj( $nt, htmlspecialchars( $nt->getText() ) ) . '</div>';
@@ -170,8 +173,7 @@
 				# Error generating thumbnail.
 				$thumbhtml = '<div style="height: 152px;">'
 					. htmlspecialchars( $img->getLastError() ) . '</div>';
-			}
-			else {
+			} else {
 				$vpad = floor( ( 150 - $thumb->height ) /2 ) - 2;
 				$thumbhtml = '<div class="thumb" style="padding: ' . $vpad . 'px 0;">'
 					. $sk->makeKnownLinkObj( $nt, $thumb->toHtml() ) . '</div>';
@@ -221,6 +223,26 @@
 	public function count() {
 		return count( $this->mImages );
 	}
+	
+	/**
+	 * Set the contextual title
+	 *
+	 * @param Title $title Contextual title
+	 */
+	public function setContextTitle( $title ) {
+		$this->contextTitle = $title;
+	}
+	
+	/**
+	 * Get the contextual title, if applicable
+	 *
+	 * @return mixed Title or false
+	 */
+	public function getContextTitle() {
+		return is_object( $this->contextTitle ) && $this->contextTitle instanceof Title
+				? $this->contextTitle
+				: false;
+	}
 
 } //class
 ?>
diff -NaurB -x .svn includes/ImagePage.php /srv/web/fp014/source/includes/ImagePage.php
--- includes/ImagePage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ImagePage.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,6 +1,5 @@
 <?php
 /**
- * @package MediaWiki
  */
 
 /**
@@ -11,7 +10,6 @@
 
 /**
  * Special handling for image description pages
- * @package MediaWiki
  */
 class ImagePage extends Article {
 
@@ -29,59 +27,62 @@
 	}
 
 	function view() {
-		global $wgOut, $wgShowEXIF;
+		global $wgOut, $wgShowEXIF, $wgRequest, $wgUser;
 
 		$this->img = new Image( $this->mTitle );
 
-		if( $this->mTitle->getNamespace() == NS_IMAGE  ) {
-			if ($wgShowEXIF && $this->img->exists()) {
-				$exif = $this->img->getExifData();
-				$showmeta = count($exif) ? true : false;
-			} else {
-				$exif = false;
-				$showmeta = false;
-			}
+		$diff = $wgRequest->getVal( 'diff' );
+		$diffOnly = $wgRequest->getBool( 'diffonly', $wgUser->getOption( 'diffonly' ) );
 
-			if ($this->img->exists())
-				$wgOut->addHTML($this->showTOC($showmeta));
+		if ( $this->mTitle->getNamespace() != NS_IMAGE || ( isset( $diff ) && $diffOnly ) )
+			return Article::view();
 
-			$this->openShowImage();
+		if ($wgShowEXIF && $this->img->exists()) {
+			$exif = $this->img->getExifData();
+			$showmeta = count($exif) ? true : false;
+		} else {
+			$exif = false;
+			$showmeta = false;
+		}
 
-			# No need to display noarticletext, we use our own message, output in openShowImage()
-			if( $this->getID() ) {
-				Article::view();
-			} else {
-				# Just need to set the right headers
-				$wgOut->setArticleFlag( true );
-				$wgOut->setRobotpolicy( 'index,follow' );
-				$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
-				$this->viewUpdates();
-			}
+		if ($this->img->exists())
+			$wgOut->addHTML($this->showTOC($showmeta));
 
-			# Show shared description, if needed
-			if( $this->mExtraDescription ) {
-				$fol = wfMsg( 'shareddescriptionfollows' );
-				if( $fol != '-' ) {
-					$wgOut->addWikiText( $fol );
-				}
-				$wgOut->addHTML( '<div id="shared-image-desc">' . $this->mExtraDescription . '</div>' );
-			}
+		$this->openShowImage();
 
-			$this->closeShowImage();
-			$this->imageHistory();
-			$this->imageLinks();
-			if( $exif ) {
-				global $wgStylePath, $wgStyleVersion;
-				$expand = htmlspecialchars( wfEscapeJsString( wfMsg( 'metadata-expand' ) ) );
-				$collapse = htmlspecialchars( wfEscapeJsString( wfMsg( 'metadata-collapse' ) ) );
-				$wgOut->addHTML( "<h2 id=\"metadata\">" . wfMsgHtml( 'metadata' ) . "</h2>\n" );
-				$wgOut->addWikiText( $this->makeMetadataTable( $exif ) );
-				$wgOut->addHTML(
-					"<script type=\"text/javascript\" src=\"$wgStylePath/common/metadata.js?$wgStyleVersion\"></script>\n" .
-					"<script type=\"text/javascript\">attachMetadataToggle('mw_metadata', '$expand', '$collapse');</script>\n" );
-			}
-		} else {
+		# No need to display noarticletext, we use our own message, output in openShowImage()
+		if ( $this->getID() ) {
 			Article::view();
+		} else {
+			# Just need to set the right headers
+			$wgOut->setArticleFlag( true );
+			$wgOut->setRobotpolicy( 'index,follow' );
+			$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
+			$this->viewUpdates();
+		}
+
+		# Show shared description, if needed
+		if ( $this->mExtraDescription ) {
+			$fol = wfMsg( 'shareddescriptionfollows' );
+			if( $fol != '-' ) {
+				$wgOut->addWikiText( $fol );
+			}
+			$wgOut->addHTML( '<div id="shared-image-desc">' . $this->mExtraDescription . '</div>' );
+		}
+
+		$this->closeShowImage();
+		$this->imageHistory();
+		$this->imageLinks();
+
+		if ( $exif ) {
+			global $wgStylePath, $wgStyleVersion;
+			$expand = htmlspecialchars( wfEscapeJsString( wfMsg( 'metadata-expand' ) ) );
+			$collapse = htmlspecialchars( wfEscapeJsString( wfMsg( 'metadata-collapse' ) ) );
+			$wgOut->addHTML( Xml::element( 'h2', array( 'id' => 'metadata' ), wfMsg( 'metadata' ) ). "\n" );
+			$wgOut->addWikiText( $this->makeMetadataTable( $exif ) );
+			$wgOut->addHTML(
+				"<script type=\"text/javascript\" src=\"$wgStylePath/common/metadata.js?$wgStyleVersion\"></script>\n" .
+				"<script type=\"text/javascript\">attachMetadataToggle('mw_metadata', '$expand', '$collapse');</script>\n" );
 		}
 	}
 
@@ -393,7 +394,7 @@
 		}
 		
 		# External editing link
-		$elink = $sk->makeKnownLinkObj( $this->mTitle, wfMsg( 'edit-externally' ), 'action=edit&externaledit=true&mode=file' );
+		$elink = $sk->makeKnownLinkObj( $this->mTitle, wfMsgHtml( 'edit-externally' ), 'action=edit&externaledit=true&mode=file' );
 		$wgOut->addHtml( '<li>' . $elink . '<div>' . wfMsgWikiHtml( 'edit-externally-help' ) . '</div></li>' );
 		
 		$wgOut->addHtml( '</ul>' );
@@ -449,9 +450,9 @@
 	{
 		global $wgUser, $wgOut;
 
-		$wgOut->addHTML( '<h2 id="filelinks">' . wfMsg( 'imagelinks' ) . "</h2>\n" );
+		$wgOut->addHTML( Xml::element( 'h2', array( 'id' => 'filelinks' ), wfMsg( 'imagelinks' ) ) . "\n" );
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$page = $dbr->tableName( 'page' );
 		$imagelinks = $dbr->tableName( 'imagelinks' );
 
@@ -619,7 +620,7 @@
 			$wgOut->showErrorPage( 'uploadnologin', 'uploadnologintext' );
 			return;
 		}
-		if ( ! $this->mTitle->userCanEdit() ) {
+		if ( ! $this->mTitle->userCan( 'edit' ) ) {
 			$wgOut->readOnlyPage( $this->getContent(), true );
 			return;
 		}
@@ -645,9 +646,6 @@
 		}
 		$oldver = wfTimestampNow() . "!{$name}";
 
-		$dbr =& wfGetDB( DB_SLAVE );
-		$size = $dbr->selectField( 'oldimage', 'oi_size', array( 'oi_archive_name' => $oldimage )  );
-
 		if ( ! rename( $curfile, "${archive}/{$oldver}" ) ) {
 			$wgOut->showFileRenameError( $curfile, "${archive}/{$oldver}" );
 			return;
@@ -694,7 +692,6 @@
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class ImageHistoryList {
 	function ImageHistoryList( &$skin ) {
@@ -702,8 +699,9 @@
 	}
 
 	function beginImageHistoryList() {
-		$s = "\n<h2 id=\"filehistory\">" . wfMsg( 'imghistory' ) . "</h2>\n" .
-		  "<p>" . wfMsg( 'imghistlegend' ) . "</p>\n".'<ul class="special">';
+		$s = "\n" .
+			Xml::element( 'h2', array( 'id' => 'filehistory' ), wfMsg( 'imghistory' ) ) .
+			"\n<p>" . wfMsg( 'imghistlegend' ) . "</p>\n".'<ul class="special">';
 		return $s;
 	}
 
@@ -716,9 +714,9 @@
 		global $wgUser, $wgLang, $wgTitle, $wgContLang;
 
 		$datetime = $wgLang->timeanddate( $timestamp, true );
-		$del = wfMsg( 'deleteimg' );
-		$delall = wfMsg( 'deleteimgcompletely' );
-		$cur = wfMsg( 'cur' );
+		$del = wfMsgHtml( 'deleteimg' );
+		$delall = wfMsgHtml( 'deleteimgcompletely' );
+		$cur = wfMsgHtml( 'cur' );
 
 		if ( $iscur ) {
 			$url = Image::imageUrl( $img );
@@ -734,10 +732,10 @@
 			}
 		} else {
 			$url = htmlspecialchars( wfImageArchiveUrl( $img ) );
-			if( $wgUser->getID() != 0 && $wgTitle->userCanEdit() ) {
+			if( $wgUser->getID() != 0 && $wgTitle->userCan( 'edit' ) ) {
 				$token = urlencode( $wgUser->editToken( $img ) );
 				$rlink = $this->skin->makeKnownLinkObj( $wgTitle,
-				           wfMsg( 'revertimg' ), 'action=revert&oldimage=' .
+				           wfMsgHtml( 'revertimg' ), 'action=revert&oldimage=' .
 				           urlencode( $img ) . "&wpEditToken=$token" );
 				$dlink = $this->skin->makeKnownLinkObj( $wgTitle,
 				           $del, 'action=delete&oldimage=' . urlencode( $img ) .
@@ -746,7 +744,7 @@
 				# Having live active links for non-logged in users
 				# means that bots and spiders crawling our site can
 				# inadvertently change content. Baaaad idea.
-				$rlink = wfMsg( 'revertimg' );
+				$rlink = wfMsgHtml( 'revertimg' );
 				$dlink = $del;
 			}
 		}
@@ -754,7 +752,7 @@
 		$userlink = $this->skin->userLink( $user, $usertext ) . $this->skin->userToolLinks( $user, $usertext );
 		$nbytes = wfMsgExt( 'nbytes', array( 'parsemag', 'escape' ),
 			$wgLang->formatNum( $size ) );
-		$widthheight = wfMsg( 'widthheight', $width, $height );
+		$widthheight = wfMsgHtml( 'widthheight', $width, $height );
 		$style = $this->skin->getInternalLinkAttributes( $url, $datetime );
 
 		$s = "<li> ({$dlink}) ({$rlink}) <a href=\"{$url}\"{$style}>{$datetime}</a> . . {$userlink} . . {$widthheight} ({$nbytes})";
diff -NaurB -x .svn includes/Image.php /srv/web/fp014/source/includes/Image.php
--- includes/Image.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Image.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,6 +1,5 @@
 <?php
 /**
- * @package MediaWiki
  */
 
 /**
@@ -22,7 +21,6 @@
  *
  * Provides methods to retrieve paths (physical, logical, URL),
  * to generate thumbnails or for uploading.
- * @package MediaWiki
  */
 class Image
 {
@@ -328,7 +326,7 @@
 		global $wgUseSharedUploads, $wgSharedUploadDBname, $wgSharedUploadDBprefix, $wgContLang;
 		wfProfileIn( __METHOD__ );
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$this->checkDBSchema($dbr);
 
 		$row = $dbr->selectRow( 'image',
@@ -349,7 +347,7 @@
 			# capitalize the first letter of the filename before
 			# looking it up in the shared repository.
 			$name = $wgContLang->ucfirst($this->name);
-			$dbc =& wfGetDB( DB_SLAVE, 'commons' );
+			$dbc = wfGetDB( DB_SLAVE, 'commons' );
 
 			$row = $dbc->selectRow( "`$wgSharedUploadDBname`.{$wgSharedUploadDBprefix}image",
 				array(
@@ -451,10 +449,10 @@
 
 			// Write to the other DB using selectDB, not database selectors
 			// This avoids breaking replication in MySQL
-			$dbw =& wfGetDB( DB_MASTER, 'commons' );
+			$dbw = wfGetDB( DB_MASTER, 'commons' );
 			$dbw->selectDB( $wgSharedUploadDBname );
 		} else {
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 		}
 
 		$this->checkDBSchema($dbw);
@@ -1445,7 +1443,7 @@
 	 * @public
 	 */
 	function nextHistoryLine() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$this->checkDBSchema($dbr);
 
@@ -1541,7 +1539,7 @@
 	function recordUpload( $oldver, $desc, $license = '', $copyStatus = '', $source = '', $watch = false ) {
 		global $wgUser, $wgUseCopyrightUpload;
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		$this->checkDBSchema($dbw);
 
@@ -1697,9 +1695,9 @@
 		wfProfileIn( __METHOD__ );
 
 		if ( $options ) {
-			$db =& wfGetDB( DB_MASTER );
+			$db = wfGetDB( DB_MASTER );
 		} else {
-			$db =& wfGetDB( DB_SLAVE );
+			$db = wfGetDB( DB_SLAVE );
 		}
 		$linkCache =& LinkCache::singleton();
 
@@ -1781,7 +1779,7 @@
 		}
 
 		# Update EXIF data in database
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		$this->checkDBSchema($dbw);
 
@@ -1870,6 +1868,8 @@
 		// Update site_stats
 		$site_stats = $dbw->tableName( 'site_stats' );
 		$dbw->query( "UPDATE $site_stats SET ss_images=ss_images-1", __METHOD__ );
+
+		wfRunHooks('FileDeleteComplete', array(&$this, &$wgUser, $reason));
 		
 		$this->purgeEverything( $urlArr );
 		
@@ -2277,7 +2277,7 @@
 			$this->purgeMetadataCache();
 
 			# Update metadata in the database
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$dbw->update( 'image',
 				array( 'img_metadata' => $this->metadata ),
 				array( 'img_name' => $this->name ),
@@ -2317,7 +2317,6 @@
 
 /**
  * Wrapper class for thumbnail images
- * @package MediaWiki
  */
 class ThumbnailImage {
 	/**
diff -NaurB -x .svn includes/IP.php /srv/web/fp014/source/includes/IP.php
--- includes/IP.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/IP.php	2007-02-01 01:03:18.000000000 +0000
@@ -244,6 +244,7 @@
 	    return $addr;
 
 	// IPv6 loopback address
+	$m = array();
 	if ( preg_match( '/^0*' . RE_IPV6_GAP . '1$/', $addr, $m ) )
 	    return '127.0.0.1';
 
diff -NaurB -x .svn includes/JobQueue.php /srv/web/fp014/source/includes/JobQueue.php
--- includes/JobQueue.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/JobQueue.php	2007-02-01 01:03:18.000000000 +0000
@@ -31,7 +31,7 @@
 	static function pop() {
 		wfProfileIn( __METHOD__ );
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		// Get a job from the slave
 		$row = $dbr->selectRow( 'job', '*', '', __METHOD__,
@@ -44,7 +44,7 @@
 		}
 
 		// Try to delete it from the master
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'job', array( 'job_id' => $row->job_id ), __METHOD__ );
 		$affected = $dbw->affectedRows();
 		$dbw->immediateCommit();
@@ -147,7 +147,7 @@
 	function insert() {
 		$fields = $this->insertFields();
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		
 		if ( $this->removeDuplicates ) {
 			$res = $dbw->select( 'job', array( '1' ), $fields, __METHOD__ );
diff -NaurB -x .svn includes/Licenses.php /srv/web/fp014/source/includes/Licenses.php
--- includes/Licenses.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Licenses.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * A License class for use on Special:Upload
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -31,12 +30,12 @@
 	/**#@-*/
 
 	/**
-	 * Constrictor
+	 * Constructor
 	 *
 	 * @param $str String: the string to build the licenses member from, will use
 	 *                    wfMsgForContent( 'licenses' ) if null (default: null)
 	 */
-	function Licenses( $str = null ) {
+	function __construct( $str = null ) {
 		// PHP sucks, this should be possible in the constructor
 		$this->msg = is_null( $str ) ? wfMsgForContent( 'licenses' ) : $str;
 		$this->html = '';
diff -NaurB -x .svn includes/LinkBatch.php /srv/web/fp014/source/includes/LinkBatch.php
--- includes/LinkBatch.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/LinkBatch.php	2007-02-01 01:03:18.000000000 +0000
@@ -4,8 +4,7 @@
  * Class representing a list of titles
  * The execute() method checks them all for existence and adds them to a LinkCache object
  +
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 class LinkBatch {
 	/**
@@ -13,7 +12,7 @@
 	 */
 	var $data = array();
 
-	function LinkBatch( $arr = array() ) {
+	function __construct( $arr = array() ) {
 		foreach( $arr as $item ) {
 			$this->addObj( $item );
 		}
@@ -120,7 +119,7 @@
 
 		// Construct query
 		// This is very similar to Parser::replaceLinkHolders
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$page = $dbr->tableName( 'page' );
 		$set = $this->constructSet( 'page', $dbr );
 		if ( $set === false ) {
diff -NaurB -x .svn includes/LinkCache.php /srv/web/fp014/source/includes/LinkCache.php
--- includes/LinkCache.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/LinkCache.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,13 +1,11 @@
 <?php
 /**
  * Cache for article titles (prefixed DB keys) and ids linked from one source
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 
 /**
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 class LinkCache {
 	// Increment $mClassVer whenever old serialized versions of this class
@@ -29,7 +27,7 @@
 		return $instance;
 	}
 
-	function LinkCache() {
+	function __construct() {
 		$this->mForUpdate = false;
 		$this->mPageLinks = array();
 		$this->mGoodLinks = array();
@@ -135,14 +133,14 @@
 			$id = $wgMemc->get( $key = $this->getKey( $title ) );
 		if( ! is_integer( $id ) ) {
 			if ( $this->mForUpdate ) {
-				$db =& wfGetDB( DB_MASTER );
+				$db = wfGetDB( DB_MASTER );
 				if ( !( $wgAntiLockFlags & ALF_NO_LINK_LOCK ) ) {
 					$options = array( 'FOR UPDATE' );
 				} else {
 					$options = array();
 				}
 			} else {
-				$db =& wfGetDB( DB_SLAVE );
+				$db = wfGetDB( DB_SLAVE );
 				$options = array();
 			}
 
diff -NaurB -x .svn includes/Linker.php /srv/web/fp014/source/includes/Linker.php
--- includes/Linker.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Linker.php	2007-02-01 01:03:18.000000000 +0000
@@ -4,7 +4,6 @@
  * These functions are used for primarily page content:
  * links, embedded images, table of contents. Links are
  * also used in the skin.
- * @package MediaWiki
  */
 
 /**
@@ -13,10 +12,9 @@
  * so that ever other bit of the wiki doesn't have to
  * go loading up Skin to get at it.
  *
- * @package MediaWiki
  */
 class Linker {
-	function Linker() {}
+	function __construct() {}
 
 	/**
 	 * @deprecated
@@ -229,7 +227,7 @@
 			} else {
 				$threshold = $wgUser->getOption('stubthreshold') ;
 				if ( $threshold > 0 ) {
-					$dbr =& wfGetDB( DB_SLAVE );
+					$dbr = wfGetDB( DB_SLAVE );
 					$s = $dbr->selectRow(
 						array( 'page' ),
 						array( 'page_len',
@@ -756,10 +754,10 @@
 	/**
 	 * @param $userId Integer: user id in database.
 	 * @param $userText String: user name in database.
+	 * @param $redContribsWhenNoEdits Bool: return a red contribs link when the user had no edits and this is true.
 	 * @return string HTML fragment with talk and/or block links
-	 * @private
 	 */
-	function userToolLinks( $userId, $userText ) {
+	public function userToolLinks( $userId, $userText, $redContribsWhenNoEdits = false ) {
 		global $wgUser, $wgDisableAnonTalk, $wgSysopUserBans;
 		$talkable = !( $wgDisableAnonTalk && 0 == $userId );
 		$blockable = ( $wgSysopUserBans || 0 == $userId );
@@ -769,9 +767,15 @@
 			$items[] = $this->userTalkLink( $userId, $userText );
 		}
 		if( $userId ) {
+			// check if the user has an edit
+			if( $redContribsWhenNoEdits && User::edits( $userId ) == 0 ) {
+				$style = "class='new'";
+			} else {
+				$style = '';
+			}
 			$contribsPage = SpecialPage::getTitleFor( 'Contributions', $userText );
-			$items[] = $this->makeKnownLinkObj( $contribsPage ,
-				wfMsgHtml( 'contribslink' ) );
+
+			$items[] = $this->makeKnownLinkObj( $contribsPage, wfMsgHtml( 'contribslink' ), '', '', '', '', $style );
 		}
 		if( $blockable && $wgUser->isAllowed( 'block' ) ) {
 			$items[] = $this->blockLink( $userId, $userText );
@@ -785,6 +789,14 @@
 	}
 
 	/**
+	 * Alias for userToolLinks( $userId, $userText, true );
+	 */
+	public function userToolLinksRedContribs( $userId, $userText ) {
+		return $this->userToolLinks( $userId, $userText, true );
+	}
+
+
+	/**
 	 * @param $userId Integer: user id in database.
 	 * @param $userText String: user name in database.
 	 * @return string HTML fragment with user talk link
@@ -1134,7 +1146,7 @@
 		global $wgUser;
 		wfProfileIn( __METHOD__ );
 
-		$sk =& $wgUser->getSkin();
+		$sk = $wgUser->getSkin();
 
 		$outText = '';
 		if ( count( $templates ) > 0 ) {
@@ -1201,7 +1213,56 @@
 		$size = round( $size, 0 );
 		return wfMsgHtml( $msg, $wgLang->formatNum( $size ) );
 	}
-	
+
+	/**
+	 * Given the id of an interface element, constructs the appropriate title
+	 * and accesskey attributes from the system messages.  (Note, this is usu-
+	 * ally the id but isn't always, because sometimes the accesskey needs to
+	 * go on a different element than the id, for reverse-compatibility, etc.)
+	 *
+	 * @param string $name Id of the element, minus prefixes.
+	 * @return string title and accesskey attributes, ready to drop in an
+	 *   element (e.g., ' title="This does something [x]" accesskey="x"').
+	 */
+	public function tooltipAndAccesskey($name) {
+		$out = '';
+
+		$tooltip = wfMsg('tooltip-'.$name);
+		if (!wfEmptyMsg('tooltip-'.$name, $tooltip) && $tooltip != '-') {
+			// Compatibility: formerly some tooltips had [alt-.] hardcoded
+			$tooltip = preg_replace( "/ ?\[alt-.\]$/", '', $tooltip );
+			$out .= ' title="'.htmlspecialchars($tooltip);
+		}
+		$accesskey = wfMsg('accesskey-'.$name);
+		if ($accesskey && $accesskey != '-' && !wfEmptyMsg('accesskey-'.$name, $accesskey)) {
+			if ($out) $out .= " [$accesskey]\" accesskey=\"$accesskey\"";
+			else $out .= " title=\"[$accesskey]\" accesskey=\"$accesskey\"";
+		} elseif ($out) {
+			$out .= '"';
+		}
+		return $out;
+	}
+
+	/**
+	 * Given the id of an interface element, constructs the appropriate title
+	 * attribute from the system messages.  (Note, this is usually the id but
+	 * isn't always, because sometimes the accesskey needs to go on a different
+	 * element than the id, for reverse-compatibility, etc.)
+	 *
+	 * @param string $name Id of the element, minus prefixes.
+	 * @return string title attribute, ready to drop in an element
+	 * (e.g., ' title="This does something"').
+	 */
+	public function tooltip($name) {
+		$out = '';
+
+		$tooltip = wfMsg('tooltip-'.$name);
+		if (!wfEmptyMsg('tooltip-'.$name, $tooltip) && $tooltip != '-') {
+			$out = ' title="'.htmlspecialchars($tooltip).'"';
+		}
+
+		return $out;
+	}
 }
 
 ?>
diff -NaurB -x .svn includes/LinksUpdate.php /srv/web/fp014/source/includes/LinksUpdate.php
--- includes/LinksUpdate.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/LinksUpdate.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  * See deferred.txt
- * @package MediaWiki
  */
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class LinksUpdate {
 
@@ -41,7 +39,7 @@
 		} else {
 			$this->mOptions = array( 'FOR UPDATE' );
 		}
-		$this->mDb =& wfGetDB( DB_MASTER );
+		$this->mDb = wfGetDB( DB_MASTER );
 
 		if ( !is_object( $title ) ) {
 			throw new MWException( "The calling convention to LinksUpdate::LinksUpdate() has changed. " .
@@ -172,7 +170,7 @@
 		wfProfileIn( __METHOD__ );
 		
 		$batchSize = 100;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( array( 'templatelinks', 'page' ), 
 			array( 'page_namespace', 'page_title' ),
 			array( 
diff -NaurB -x .svn includes/LoadBalancer.php /srv/web/fp014/source/includes/LoadBalancer.php
--- includes/LoadBalancer.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/LoadBalancer.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  *
- * @package MediaWiki
  */
 
 
@@ -9,7 +8,6 @@
  * Database load balancing object
  *
  * @todo document
- * @package MediaWiki
  */
 class LoadBalancer {
 	/* private */ var $mServers, $mConnections, $mLoads, $mGroupLoads;
@@ -24,7 +22,7 @@
 	 */
 	const AVG_STATUS_POLL = 2000;
 
-	function LoadBalancer( $servers, $failFunction = false, $waitTimeout = 10, $waitForMasterNow = false )
+	function __construct( $servers, $failFunction = false, $waitTimeout = 10, $waitForMasterNow = false )
 	{
 		$this->mServers = $servers;
 		$this->mFailFunction = $failFunction;
diff -NaurB -x .svn includes/LogPage.php /srv/web/fp014/source/includes/LogPage.php
--- includes/LogPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/LogPage.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,7 +21,6 @@
 /**
  * Contain log classes
  *
- * @package MediaWiki
  */
 
 /**
@@ -29,7 +28,6 @@
  * The logs are now kept in a table which is easier to manage and trim
  * than ever-growing wiki pages.
  *
- * @package MediaWiki
  */
 class LogPage {
 	/* @access private */
@@ -44,7 +42,7 @@
 	  *               'upload', 'move'
 	  * @param bool $rc Whether to update recent changes as well as the logging table
 	  */
-	function LogPage( $type, $rc = true ) {
+	function __construct( $type, $rc = true ) {
 		$this->type = $type;
 		$this->updateRecentChanges = $rc;
 	}
@@ -55,7 +53,7 @@
 		global $wgUser;
 		$fname = 'LogPage::saveContent';
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$uid = $wgUser->getID();
 
 		$this->timestamp = $now = wfTimestampNow();
@@ -134,6 +132,10 @@
 		global $wgLang, $wgContLang, $wgLogActions;
 
 		$key = "$type/$action";
+		
+		if( $key == 'patrol/patrol' )
+			return PatrolLog::makeActionText( $title, $params, $skin );
+		
 		if( isset( $wgLogActions[$key] ) ) {
 			if( is_null( $title ) ) {
 				$rv=wfMsg( $wgLogActions[$key] );
@@ -184,7 +186,10 @@
 				} else {
 					array_unshift( $params, $titleLink );
 					if ( $translate && $key == 'block/block' ) {
-						$params[1] = $wgLang->translateBlockExpiry($params[1]);
+						$params[1] = $wgLang->translateBlockExpiry( $params[1] );
+						$params[2] = isset( $params[2] )
+										? self::formatBlockFlags( $params[2] )
+										: '';
 					}
 					$rv = wfMsgReal( $wgLogActions[$key], $params, true, !$skin );
 				}
@@ -241,6 +246,41 @@
 			return explode( "\n", $blob );
 		}
 	}
+	
+	/**
+	 * Convert a comma-delimited list of block log flags
+	 * into a more readable (and translated) form
+	 *
+	 * @param $flags Flags to format
+	 * @return string
+	 */
+	public static function formatBlockFlags( $flags ) {
+		$flags = explode( ',', trim( $flags ) );
+		if( count( $flags ) > 0 ) {
+			for( $i = 0; $i < count( $flags ); $i++ )
+				$flags[$i] = self::formatBlockFlag( $flags[$i] );
+			return '(' . implode( ', ', $flags ) . ')';
+		} else {
+			return '';
+		}
+	}
+	
+	/**
+	 * Translate a block log flag if possible
+	 *
+	 * @param $flag Flag to translate
+	 * @return string
+	 */
+	public static function formatBlockFlag( $flag ) {
+		static $messages = array();
+		if( !isset( $messages[$flag] ) ) {
+			$k = 'block-log-flags-' . $flag;
+			$msg = wfMsg( $k );
+			$messages[$flag] = htmlspecialchars( wfEmptyMsg( $k, $msg ) ? $flag : $msg );
+		}
+		return $messages[$flag];
+	}
+	
 }
 
 ?>
diff -NaurB -x .svn includes/MacBinary.php /srv/web/fp014/source/includes/MacBinary.php
--- includes/MacBinary.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/MacBinary.php	2007-02-01 01:03:18.000000000 +0000
@@ -22,12 +22,11 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class MacBinary {
-	function MacBinary( $filename ) {
+	function __construct( $filename ) {
 		$this->open( $filename );
 		$this->loadHeader();
 	}
@@ -269,4 +268,4 @@
 	}
 }
 
-?>
\ No newline at end of file
+?>
diff -NaurB -x .svn includes/MagicWord.php /srv/web/fp014/source/includes/MagicWord.php
--- includes/MagicWord.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/MagicWord.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  * File for magic words
- * @package MediaWiki
- * @subpackage Parser
+ * @addtogroup Parser
  */
 
 /**
@@ -21,7 +20,6 @@
  * magic words which are also Parser variables, add a MagicWordwgVariableIDs
  * hook. Use string keys.
  *
- * @package MediaWiki
  */
 class MagicWord {
 	/**#@+
@@ -108,7 +106,7 @@
 
 	/**#@-*/
 
-	function MagicWord($id = 0, $syn = '', $cs = false) {
+	function __construct($id = 0, $syn = '', $cs = false) {
 		$this->mId = $id;
 		$this->mSynonyms = (array)$syn;
 		$this->mCaseSensitive = $cs;
diff -NaurB -x .svn includes/Math.php /srv/web/fp014/source/includes/Math.php
--- includes/Math.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Math.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Contain everything related to <math> </math> parsing
- * @package MediaWiki
  */
 
 /**
@@ -11,7 +10,6 @@
  *
  * by Tomasz Wegrzanowski, with additions by Brion Vibber (2003, 2004)
  *
- * @package MediaWiki
  */
 class MathRenderer {
 	var $mode = MW_MATH_MODERN;
@@ -22,7 +20,7 @@
 	var $mathml = '';
 	var $conservativeness = 0;
 
-	function MathRenderer( $tex ) {
+	function __construct( $tex ) {
 		$this->tex = $tex;
  	}
 
@@ -156,7 +154,7 @@
 
 				$md5_sql = pack('H32', $this->md5); # Binary packed, not hex
 
-				$dbw =& wfGetDB( DB_MASTER );
+				$dbw = wfGetDB( DB_MASTER );
 				$dbw->replace( 'math', array( 'math_inputhash' ),
 				  array(
 					'math_inputhash' => $md5_sql,
@@ -185,7 +183,7 @@
 		$fname = 'MathRenderer::_recall';
 
 		$this->md5 = md5( $this->tex );
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$rpage = $dbr->selectRow( 'math',
 			array( 'math_outputhash','math_html_conservativeness','math_html','math_mathml' ),
 			array( 'math_inputhash' => pack("H32", $this->md5)), # Binary packed, not hex
diff -NaurB -x .svn includes/MemcachedSessions.php /srv/web/fp014/source/includes/MemcachedSessions.php
--- includes/MemcachedSessions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/MemcachedSessions.php	2007-02-01 01:04:18.000000000 +0000
@@ -6,14 +6,14 @@
  * be necessary to change the cookie settings to work across hostnames.
  * See: http://www.php.net/manual/en/function.session-set-save-handler.php
  *
- * @package MediaWiki
  */
 
 /**
  * @todo document
  */
 function memsess_key( $id ) {
-	return wfMemcKey( 'session', $id );
+	//return wfMemcKey( 'session', $id );
+	return "wikia:session:{$id}"; /** wikia hack */
 }
 
 /**
diff -NaurB -x .svn includes/MessageCache.php /srv/web/fp014/source/includes/MessageCache.php
--- includes/MessageCache.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/MessageCache.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 
 /**
@@ -17,7 +16,6 @@
  * Message cache
  * Performs various MediaWiki namespace-related functions
  *
- * @package MediaWiki
  */
 class MessageCache {
 	var $mCache, $mUseCache, $mDisable, $mExpiry;
@@ -298,10 +296,10 @@
 	 * Loads all or main part of cacheable messages from the database
 	 */
 	function loadFromDB() {
-		global $wgLang, $wgMaxMsgCacheEntrySize;
+		global $wgMaxMsgCacheEntrySize;
 
 		wfProfileIn( __METHOD__ );
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$this->mCache = array();
 
 		# Load titles for all oversized pages in the MediaWiki namespace
diff -NaurB -x .svn includes/Metadata.php /srv/web/fp014/source/includes/Metadata.php
--- includes/Metadata.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Metadata.php	2007-02-01 01:03:18.000000000 +0000
@@ -18,7 +18,6 @@
  *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
  *
  * @author Evan Prodromou <evan@wikitravel.org>
- * @package MediaWiki
  */
 
 /**
diff -NaurB -x .svn includes/MimeMagic.php /srv/web/fp014/source/includes/MimeMagic.php
--- includes/MimeMagic.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/MimeMagic.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /** Module defining helper functions for detecting and dealing with mime types.
  *
- * @package MediaWiki
  */
 
  /** Defines a set of well known mime types
@@ -75,7 +74,6 @@
 *
 * Instances of this class are stateles, there only needs to be one global instance
 * of MimeMagic. Please use MimeMagic::singleton() to get that instance.
-* @package MediaWiki
 */
 class MimeMagic {
 
@@ -105,7 +103,7 @@
 	*
 	* This constructor parses the mime.types and mime.info files and build internal mappings.
 	*/
-	function MimeMagic() {
+	function __construct() {
 		/*
 		*   --- load mime.types ---
 		*/
@@ -149,7 +147,7 @@
 
 			if (empty($ext)) continue;
 
-			if (@$this->mMimeToExt[$mime]) $this->mMimeToExt[$mime] .= ' '.$ext;
+			if ( !empty($this->mMimeToExt[$mime])) $this->mMimeToExt[$mime] .= ' '.$ext;
 			else $this->mMimeToExt[$mime]= $ext;
 
 			$extensions= explode(' ',$ext);
@@ -158,7 +156,7 @@
 				$e= trim($e);
 				if (empty($e)) continue;
 
-				if (@$this->mExtToMime[$e]) $this->mExtToMime[$e] .= ' '.$mime;
+				if ( !empty($this->mExtToMime[$e])) $this->mExtToMime[$e] .= ' '.$mime;
 				else $this->mExtToMime[$e]= $mime;
 			}
 		}
@@ -424,7 +422,9 @@
 					$match= array();
 					$prog= "";
 
-					if (preg_match('%/?([^\s]+/)(w+)%sim',$head,$match)) $script= $match[2];
+					if (preg_match('%/?([^\s]+/)(w+)%sim',$head,$match)) {
+						$script= $match[2]; // FIXME: $script variable not used; should this be "$prog = $match[2];" instead?
+					}
 
 					$mime= "application/x-$prog";
 				}
diff -NaurB -x .svn includes/Namespace.php /srv/web/fp014/source/includes/Namespace.php
--- includes/Namespace.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Namespace.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Provide things related to namespaces
- * @package MediaWiki
  */
 
 /**
@@ -41,7 +40,6 @@
  * These are synonyms for the names given in the language file
  * Users and translators should not change them
  *
- * @package MediaWiki
  */
 class Namespace {
 
@@ -125,5 +123,19 @@
 	 static function canTalk( $index ) {
 	 	return( $index >= NS_MAIN );
 	 }
+	 
+	/**
+	 * Does this namespace contain content, for the purposes
+	 * of calculating statistics, etc?
+	 *
+	 * @param $index Index to check
+	 * @return bool
+	 */
+	public static function isContent( $index ) {
+		global $wgContentNamespaces;
+		return $index == NS_MAIN || in_array( $index, $wgContentNamespaces );
+	}	 
+	 
 }
+
 ?>
diff -NaurB -x .svn includes/normal/CleanUpTest.php /srv/web/fp014/source/includes/normal/CleanUpTest.php
--- includes/normal/CleanUpTest.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/CleanUpTest.php	2007-02-01 01:03:18.000000000 +0000
@@ -23,7 +23,7 @@
  *
  * Requires PHPUnit.
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @private
  */
 
@@ -38,20 +38,15 @@
 
 #ini_set( 'memory_limit', '40M' );
 
-require_once( 'PHPUnit.php' );
-require_once( 'UtfNormal.php' );
+require_once 'PHPUnit/Framework.php';
+require_once 'PHPUnit/TextUI/TestRunner.php';
+
+require_once 'UtfNormal.php';
 
 /**
- * @package UtfNormal
+ * @addtogroup UtfNormal
  */
-class CleanUpTest extends PHPUnit_TestCase {
-	/**
-	 * @param $name String: FIXME
-	 */
-	function CleanUpTest( $name ) {
-		$this->PHPUnit_TestCase( $name );
-	}
-
+class CleanUpTest extends PHPUnit_Framework_TestCase {
 	/** @todo document */
 	function setUp() {
 	}
@@ -412,9 +407,8 @@
 }
 
 
-$suite = new PHPUnit_TestSuite( 'CleanUpTest' );
-$result = PHPUnit::run( $suite );
-echo $result->toString();
+$suite = new PHPUnit_Framework_TestSuite( 'CleanUpTest' );
+$result = PHPUnit_TextUI_TestRunner::run( $suite );
 
 if( !$result->wasSuccessful() ) {
 	exit( -1 );
diff -NaurB -x .svn includes/normal/Makefile /srv/web/fp014/source/includes/normal/Makefile
--- includes/normal/Makefile	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/Makefile	2007-02-01 01:03:18.000000000 +0000
@@ -1,11 +1,21 @@
 .PHONY : all test testutf8 testclean icutest bench icubench clean distclean
 
-FETCH=wget
-#FETCH=fetch
-BASE=http://www.unicode.org/Public/UNIDATA
+## Latest greatest version of Unicode
+## May cause confusion if running test suite from these files
+## when the data was generated from a previous version.
+#BASE=http://www.unicode.org/Public/UNIDATA
+
+# Explicitly using Unicode 5.0
+BASE=http://www.unicode.org/Public/5.0.0/ucd/
+
+# Can override to php-cli or php5 or whatevah
 PHP=php
 #PHP=php-cli
 
+# Some nice tool to grab URLs with
+FETCH=wget
+#FETCH=fetch
+
 all : UtfNormalData.inc
 
 UtfNormalData.inc : UtfNormalGenerate.php UtfNormalUtil.php UnicodeData.txt CompositionExclusions.txt NormalizationCorrections.txt DerivedNormalizationProps.txt
@@ -20,7 +30,7 @@
 testclean : CleanUpTest.php
 	$(PHP) CleanUpTest.php
 
-bench : UtfNormalData.inc testdata/washington.txt testdata/berlin.txt testdata/tokyo.txt testdata/sociology.txt testdata/bulgakov.txt
+bench : UtfNormalData.inc testdata/washington.txt testdata/berlin.txt testdata/tokyo.txt testdata/young.txt testdata/bulgakov.txt
 	$(PHP) UtfNormalBench.php
 
 icutest : UtfNormalData.inc NormalizationTest.txt
@@ -28,14 +38,14 @@
 	$(PHP) CleanUpTest.php --icu
 	$(PHP) UtfNormalTest.php --icu
 
-icubench : UtfNormalData.inc testdata/washington.txt testdata/berlin.txt testdata/tokyo.txt testdata/sociology.txt testdata/bulgakov.txt
+icubench : UtfNormalData.inc testdata/washington.txt testdata/berlin.txt testdata/tokyo.txt testdata/young.txt testdata/bulgakov.txt
 	$(PHP) UtfNormalBench.php --icu
 
 clean :
-	rm -f UtfNormalData.inc
+	rm -f UtfNormalData.inc UtfNormalDataK.inc
 
 distclean : clean
-	rm -f CompositionExclusions.txt NormalizationTest.txt NormalizationCorrections.txt UnicodeData.txt DerivedNormalizationProps.txt
+	rm -f CompositionExclusions.txt NormalizationTest.txt NormalizationCorrections.txt UnicodeData.txt DerivedNormalizationProps.txt UTF-8-test.txt
 
 # The Unicode data files...
 CompositionExclusions.txt :
@@ -57,16 +67,16 @@
 	$(FETCH) http://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt
 
 testdata/berlin.txt :
-	mkdir -p testdata && wget -U MediaWiki/test -O testdata/berlin.txt "http://de.wikipedia.org/w/wiki.phtml?title=Berlin&oldid=2775712&action=raw"
+	mkdir -p testdata && wget -U MediaWiki/test -O testdata/berlin.txt "http://de.wikipedia.org/w/index.php?title=Berlin&oldid=2775712&action=raw"
 
 testdata/washington.txt :
-	mkdir -p testdata && wget -U MediaWiki/test -O testdata/washington.txt "http://en.wikipedia.org/w/wiki.phtml?title=Washington%2C_DC&oldid=6370218&action=raw"
+	mkdir -p testdata && wget -U MediaWiki/test -O testdata/washington.txt "http://en.wikipedia.org/w/index.php?title=Washington%2C_D.C.&oldid=6370218&action=raw"
 
 testdata/tokyo.txt :
-	mkdir -p testdata && wget -U MediaWiki/test -O testdata/tokyo.txt "http://ja.wikipedia.org/w/wiki.phtml?title=%E6%9D%B1%E4%BA%AC%E9%83%BD&oldid=940880&action=raw"
+	mkdir -p testdata && wget -U MediaWiki/test -O testdata/tokyo.txt "http://ja.wikipedia.org/w/index.php?title=%E6%9D%B1%E4%BA%AC%E9%83%BD&oldid=940880&action=raw"
 
-testdata/sociology.txt :
-	mkdir -p testdata && wget -U MediaWiki/test -O testdata/sociology.txt "http://ko.wikipedia.org/w/wiki.phtml?title=%EC%82%AC%ED%9A%8C%ED%95%99&oldid=16409&action=raw"
+testdata/young.txt :
+	mkdir -p testdata && wget -U MediaWiki/test -O testdata/young.txt "http://ko.wikipedia.org/w/index.php?title=%EC%9D%B4%EC%88%98%EC%98%81&oldid=627688&action=raw"
 
 testdata/bulgakov.txt :
-	mkdir -p testdata && wget -U MediaWiki/test -O testdata/bulgakov.txt "http://ru.wikipedia.org/w/wiki.phtml?title=%D0%91%D1%83%D0%BB%D0%B3%D0%B0%D0%BA%D0%BE%D0%B2%2C_%D0%A1%D0%B5%D1%80%D0%B3%D0%B5%D0%B9_%D0%9D%D0%B8%D0%BA%D0%BE%D0%BB%D0%B0%D0%B5%D0%B2%D0%B8%D1%87&oldid=17704&action=raw"
+	mkdir -p testdata && wget -U MediaWiki/test -O testdata/bulgakov.txt "http://ru.wikipedia.org/w/index.php?title=%D0%91%D1%83%D0%BB%D0%B3%D0%B0%D0%BA%D0%BE%D0%B2%2C_%D0%A1%D0%B5%D1%80%D0%B3%D0%B5%D0%B9_%D0%9D%D0%B8%D0%BA%D0%BE%D0%BB%D0%B0%D0%B5%D0%B2%D0%B8%D1%87&oldid=17704&action=raw"
diff -NaurB -x .svn includes/normal/RandomTest.php /srv/web/fp014/source/includes/normal/RandomTest.php
--- includes/normal/RandomTest.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/RandomTest.php	2007-02-01 01:03:18.000000000 +0000
@@ -22,7 +22,7 @@
  * UtfNormal::cleanUp() code paths, and checks to see if there's a
  * difference. Will run forever until it finds one or you kill it.
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @access private
  */
 
diff -NaurB -x .svn includes/normal/README /srv/web/fp014/source/includes/normal/README
--- includes/normal/README	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/README	2007-02-01 01:03:18.000000000 +0000
@@ -32,6 +32,10 @@
 data from from the net if necessary. If it reports failure, something is
 going wrong!
 
+You may have to set up PHPUnit first.
+
+$ pear channel-discover pear.phpunit.de
+$ pear install phpunit/PHPUnit
 
 == Benchmarks ==
 
diff -NaurB -x .svn includes/normal/Utf8Test.php /srv/web/fp014/source/includes/normal/Utf8Test.php
--- includes/normal/Utf8Test.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/Utf8Test.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,7 +21,7 @@
  * Runs the UTF-8 decoder test at:
  * http://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @access private
  */
 
diff -NaurB -x .svn includes/normal/UtfNormalBench.php /srv/web/fp014/source/includes/normal/UtfNormalBench.php
--- includes/normal/UtfNormalBench.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalBench.php	2007-02-01 01:03:18.000000000 +0000
@@ -20,7 +20,7 @@
 /**
  * Approximate benchmark for some basic operations.
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @access private
  */
 
@@ -43,7 +43,7 @@
 	'testdata/berlin.txt' => 'German text',
 	'testdata/bulgakov.txt' => 'Russian text',
 	'testdata/tokyo.txt' => 'Japanese text',
-	'testdata/sociology.txt' => 'Korean text'
+	'testdata/young.txt' => 'Korean text'
 );
 $normalizer = new UtfNormal;
 UtfNormal::loadData();
@@ -100,7 +100,11 @@
 	$rate = intval( strlen( $data ) / $delta );
 	$same = (0 == strcmp( $data, $out ) );
 
-	printf( " %20s %6.1fms %8d bytes/s (%s)\n", $form, $delta*1000.0, $rate, ($same ? 'no change' : 'changed' ) );
+	printf( " %20s %6.1fms %12s bytes/s (%s)\n",
+		$form,
+		$delta*1000.0,
+		number_format( $rate ),
+		($same ? 'no change' : 'changed' ) );
 	return $out;
 }
 
diff -NaurB -x .svn includes/normal/UtfNormalData.inc /srv/web/fp014/source/includes/normal/UtfNormalData.inc
--- includes/normal/UtfNormalData.inc	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalData.inc	2007-02-01 01:03:18.000000000 +0000
@@ -2,12 +2,11 @@
 /**
  * This file was automatically generated -- do not edit!
  * Run UtfNormalGenerate.php to create this file again (make clean && make)
- * @package MediaWiki
  */
 /** */
 global $utfCombiningClass, $utfCanonicalComp, $utfCanonicalDecomp, $utfCheckNFC;
-$utfCombiningClass = unserialize( 'a:384:{s:2:"̀";i:230;s:2:"́";i:230;s:2:"̂";i:230;s:2:"̃";i:230;s:2:"̄";i:230;s:2:"̅";i:230;s:2:"̆";i:230;s:2:"̇";i:230;s:2:"̈";i:230;s:2:"̉";i:230;s:2:"̊";i:230;s:2:"̋";i:230;s:2:"̌";i:230;s:2:"̍";i:230;s:2:"̎";i:230;s:2:"̏";i:230;s:2:"̐";i:230;s:2:"̑";i:230;s:2:"̒";i:230;s:2:"̓";i:230;s:2:"̔";i:230;s:2:"̕";i:232;s:2:"̖";i:220;s:2:"̗";i:220;s:2:"̘";i:220;s:2:"̙";i:220;s:2:"̚";i:232;s:2:"̛";i:216;s:2:"̜";i:220;s:2:"̝";i:220;s:2:"̞";i:220;s:2:"̟";i:220;s:2:"̠";i:220;s:2:"̡";i:202;s:2:"̢";i:202;s:2:"̣";i:220;s:2:"̤";i:220;s:2:"̥";i:220;s:2:"̦";i:220;s:2:"̧";i:202;s:2:"̨";i:202;s:2:"̩";i:220;s:2:"̪";i:220;s:2:"̫";i:220;s:2:"̬";i:220;s:2:"̭";i:220;s:2:"̮";i:220;s:2:"̯";i:220;s:2:"̰";i:220;s:2:"̱";i:220;s:2:"̲";i:220;s:2:"̳";i:220;s:2:"̴";i:1;s:2:"̵";i:1;s:2:"̶";i:1;s:2:"̷";i:1;s:2:"̸";i:1;s:2:"̹";i:220;s:2:"̺";i:220;s:2:"̻";i:220;s:2:"̼";i:220;s:2:"̽";i:230;s:2:"̾";i:230;s:2:"̿";i:230;s:2:"̀";i:230;s:2:"́";i:230;s:2:"͂";i:230;s:2:"̓";i:230;s:2:"̈́";i:230;s:2:"ͅ";i:240;s:2:"͆";i:230;s:2:"͇";i:220;s:2:"͈";i:220;s:2:"͉";i:220;s:2:"͊";i:230;s:2:"͋";i:230;s:2:"͌";i:230;s:2:"͍";i:220;s:2:"͎";i:220;s:2:"͐";i:230;s:2:"͑";i:230;s:2:"͒";i:230;s:2:"͓";i:220;s:2:"͔";i:220;s:2:"͕";i:220;s:2:"͖";i:220;s:2:"͗";i:230;s:2:"͘";i:232;s:2:"͙";i:220;s:2:"͚";i:220;s:2:"͛";i:230;s:2:"͜";i:233;s:2:"͝";i:234;s:2:"͞";i:234;s:2:"͟";i:233;s:2:"͠";i:234;s:2:"͡";i:234;s:2:"͢";i:233;s:2:"ͣ";i:230;s:2:"ͤ";i:230;s:2:"ͥ";i:230;s:2:"ͦ";i:230;s:2:"ͧ";i:230;s:2:"ͨ";i:230;s:2:"ͩ";i:230;s:2:"ͪ";i:230;s:2:"ͫ";i:230;s:2:"ͬ";i:230;s:2:"ͭ";i:230;s:2:"ͮ";i:230;s:2:"ͯ";i:230;s:2:"҃";i:230;s:2:"҄";i:230;s:2:"҅";i:230;s:2:"҆";i:230;s:2:"֑";i:220;s:2:"֒";i:230;s:2:"֓";i:230;s:2:"֔";i:230;s:2:"֕";i:230;s:2:"֖";i:220;s:2:"֗";i:230;s:2:"֘";i:230;s:2:"֙";i:230;s:2:"֚";i:222;s:2:"֛";i:220;s:2:"֜";i:230;s:2:"֝";i:230;s:2:"֞";i:230;s:2:"֟";i:230;s:2:"֠";i:230;s:2:"֡";i:230;s:2:"֢";i:220;s:2:"֣";i:220;s:2:"֤";i:220;s:2:"֥";i:220;s:2:"֦";i:220;s:2:"֧";i:220;s:2:"֨";i:230;s:2:"֩";i:230;s:2:"֪";i:220;s:2:"֫";i:230;s:2:"֬";i:230;s:2:"֭";i:222;s:2:"֮";i:228;s:2:"֯";i:230;s:2:"ְ";i:10;s:2:"ֱ";i:11;s:2:"ֲ";i:12;s:2:"ֳ";i:13;s:2:"ִ";i:14;s:2:"ֵ";i:15;s:2:"ֶ";i:16;s:2:"ַ";i:17;s:2:"ָ";i:18;s:2:"ֹ";i:19;s:2:"ֻ";i:20;s:2:"ּ";i:21;s:2:"ֽ";i:22;s:2:"ֿ";i:23;s:2:"ׁ";i:24;s:2:"ׂ";i:25;s:2:"ׄ";i:230;s:2:"ׅ";i:220;s:2:"ׇ";i:18;s:2:"ؐ";i:230;s:2:"ؑ";i:230;s:2:"ؒ";i:230;s:2:"ؓ";i:230;s:2:"ؔ";i:230;s:2:"ؕ";i:230;s:2:"ً";i:27;s:2:"ٌ";i:28;s:2:"ٍ";i:29;s:2:"َ";i:30;s:2:"ُ";i:31;s:2:"ِ";i:32;s:2:"ّ";i:33;s:2:"ْ";i:34;s:2:"ٓ";i:230;s:2:"ٔ";i:230;s:2:"ٕ";i:220;s:2:"ٖ";i:220;s:2:"ٗ";i:230;s:2:"٘";i:230;s:2:"ٙ";i:230;s:2:"ٚ";i:230;s:2:"ٛ";i:230;s:2:"ٜ";i:220;s:2:"ٝ";i:230;s:2:"ٞ";i:230;s:2:"ٰ";i:35;s:2:"ۖ";i:230;s:2:"ۗ";i:230;s:2:"ۘ";i:230;s:2:"ۙ";i:230;s:2:"ۚ";i:230;s:2:"ۛ";i:230;s:2:"ۜ";i:230;s:2:"۟";i:230;s:2:"۠";i:230;s:2:"ۡ";i:230;s:2:"ۢ";i:230;s:2:"ۣ";i:220;s:2:"ۤ";i:230;s:2:"ۧ";i:230;s:2:"ۨ";i:230;s:2:"۪";i:220;s:2:"۫";i:230;s:2:"۬";i:230;s:2:"ۭ";i:220;s:2:"ܑ";i:36;s:2:"ܰ";i:230;s:2:"ܱ";i:220;s:2:"ܲ";i:230;s:2:"ܳ";i:230;s:2:"ܴ";i:220;s:2:"ܵ";i:230;s:2:"ܶ";i:230;s:2:"ܷ";i:220;s:2:"ܸ";i:220;s:2:"ܹ";i:220;s:2:"ܺ";i:230;s:2:"ܻ";i:220;s:2:"ܼ";i:220;s:2:"ܽ";i:230;s:2:"ܾ";i:220;s:2:"ܿ";i:230;s:2:"݀";i:230;s:2:"݁";i:230;s:2:"݂";i:220;s:2:"݃";i:230;s:2:"݄";i:220;s:2:"݅";i:230;s:2:"݆";i:220;s:2:"݇";i:230;s:2:"݈";i:220;s:2:"݉";i:230;s:2:"݊";i:230;s:3:"़";i:7;s:3:"्";i:9;s:3:"॑";i:230;s:3:"॒";i:220;s:3:"॓";i:230;s:3:"॔";i:230;s:3:"়";i:7;s:3:"্";i:9;s:3:"਼";i:7;s:3:"੍";i:9;s:3:"઼";i:7;s:3:"્";i:9;s:3:"଼";i:7;s:3:"୍";i:9;s:3:"்";i:9;s:3:"్";i:9;s:3:"ౕ";i:84;s:3:"ౖ";i:91;s:3:"಼";i:7;s:3:"್";i:9;s:3:"്";i:9;s:3:"්";i:9;s:3:"ุ";i:103;s:3:"ู";i:103;s:3:"ฺ";i:9;s:3:"่";i:107;s:3:"้";i:107;s:3:"๊";i:107;s:3:"๋";i:107;s:3:"ຸ";i:118;s:3:"ູ";i:118;s:3:"່";i:122;s:3:"້";i:122;s:3:"໊";i:122;s:3:"໋";i:122;s:3:"༘";i:220;s:3:"༙";i:220;s:3:"༵";i:220;s:3:"༷";i:220;s:3:"༹";i:216;s:3:"ཱ";i:129;s:3:"ི";i:130;s:3:"ུ";i:132;s:3:"ེ";i:130;s:3:"ཻ";i:130;s:3:"ོ";i:130;s:3:"ཽ";i:130;s:3:"ྀ";i:130;s:3:"ྂ";i:230;s:3:"ྃ";i:230;s:3:"྄";i:9;s:3:"྆";i:230;s:3:"྇";i:230;s:3:"࿆";i:220;s:3:"့";i:7;s:3:"္";i:9;s:3:"፟";i:230;s:3:"᜔";i:9;s:3:"᜴";i:9;s:3:"្";i:9;s:3:"៝";i:230;s:3:"ᢩ";i:228;s:3:"᤹";i:222;s:3:"᤺";i:230;s:3:"᤻";i:220;s:3:"ᨗ";i:230;s:3:"ᨘ";i:220;s:3:"᷀";i:230;s:3:"᷁";i:230;s:3:"᷂";i:220;s:3:"᷃";i:230;s:3:"⃐";i:230;s:3:"⃑";i:230;s:3:"⃒";i:1;s:3:"⃓";i:1;s:3:"⃔";i:230;s:3:"⃕";i:230;s:3:"⃖";i:230;s:3:"⃗";i:230;s:3:"⃘";i:1;s:3:"⃙";i:1;s:3:"⃚";i:1;s:3:"⃛";i:230;s:3:"⃜";i:230;s:3:"⃡";i:230;s:3:"⃥";i:1;s:3:"⃦";i:1;s:3:"⃧";i:230;s:3:"⃨";i:220;s:3:"⃩";i:230;s:3:"⃪";i:1;s:3:"⃫";i:1;s:3:"〪";i:218;s:3:"〫";i:228;s:3:"〬";i:232;s:3:"〭";i:222;s:3:"〮";i:224;s:3:"〯";i:224;s:3:"゙";i:8;s:3:"゚";i:8;s:3:"꠆";i:9;s:3:"ﬞ";i:26;s:3:"︠";i:230;s:3:"︡";i:230;s:3:"︢";i:230;s:3:"︣";i:230;s:4:"𐨍";i:220;s:4:"𐨏";i:230;s:4:"𐨸";i:230;s:4:"𐨹";i:1;s:4:"𐨺";i:220;s:4:"𐨿";i:9;s:4:"𝅥";i:216;s:4:"𝅦";i:216;s:4:"𝅧";i:1;s:4:"𝅨";i:1;s:4:"𝅩";i:1;s:4:"𝅭";i:226;s:4:"𝅮";i:216;s:4:"𝅯";i:216;s:4:"𝅰";i:216;s:4:"𝅱";i:216;s:4:"𝅲";i:216;s:4:"𝅻";i:220;s:4:"𝅼";i:220;s:4:"𝅽";i:220;s:4:"𝅾";i:220;s:4:"𝅿";i:220;s:4:"𝆀";i:220;s:4:"𝆁";i:220;s:4:"𝆂";i:220;s:4:"𝆅";i:230;s:4:"𝆆";i:230;s:4:"𝆇";i:230;s:4:"𝆈";i:230;s:4:"𝆉";i:230;s:4:"𝆊";i:220;s:4:"𝆋";i:220;s:4:"𝆪";i:230;s:4:"𝆫";i:230;s:4:"𝆬";i:230;s:4:"𝆭";i:230;s:4:"𝉂";i:230;s:4:"𝉃";i:230;s:4:"𝉄";i:230;}' );
-$utfCanonicalComp = unserialize( 'a:1851:{s:3:"À";s:2:"À";s:3:"Á";s:2:"Á";s:3:"Â";s:2:"Â";s:3:"Ã";s:2:"Ã";s:3:"Ä";s:2:"Ä";s:3:"Å";s:2:"Å";s:3:"Ç";s:2:"Ç";s:3:"È";s:2:"È";s:3:"É";s:2:"É";s:3:"Ê";s:2:"Ê";s:3:"Ë";s:2:"Ë";s:3:"Ì";s:2:"Ì";s:3:"Í";s:2:"Í";s:3:"Î";s:2:"Î";s:3:"Ï";s:2:"Ï";s:3:"Ñ";s:2:"Ñ";s:3:"Ò";s:2:"Ò";s:3:"Ó";s:2:"Ó";s:3:"Ô";s:2:"Ô";s:3:"Õ";s:2:"Õ";s:3:"Ö";s:2:"Ö";s:3:"Ù";s:2:"Ù";s:3:"Ú";s:2:"Ú";s:3:"Û";s:2:"Û";s:3:"Ü";s:2:"Ü";s:3:"Ý";s:2:"Ý";s:3:"à";s:2:"à";s:3:"á";s:2:"á";s:3:"â";s:2:"â";s:3:"ã";s:2:"ã";s:3:"ä";s:2:"ä";s:3:"å";s:2:"å";s:3:"ç";s:2:"ç";s:3:"è";s:2:"è";s:3:"é";s:2:"é";s:3:"ê";s:2:"ê";s:3:"ë";s:2:"ë";s:3:"ì";s:2:"ì";s:3:"í";s:2:"í";s:3:"î";s:2:"î";s:3:"ï";s:2:"ï";s:3:"ñ";s:2:"ñ";s:3:"ò";s:2:"ò";s:3:"ó";s:2:"ó";s:3:"ô";s:2:"ô";s:3:"õ";s:2:"õ";s:3:"ö";s:2:"ö";s:3:"ù";s:2:"ù";s:3:"ú";s:2:"ú";s:3:"û";s:2:"û";s:3:"ü";s:2:"ü";s:3:"ý";s:2:"ý";s:3:"ÿ";s:2:"ÿ";s:3:"Ā";s:2:"Ā";s:3:"ā";s:2:"ā";s:3:"Ă";s:2:"Ă";s:3:"ă";s:2:"ă";s:3:"Ą";s:2:"Ą";s:3:"ą";s:2:"ą";s:3:"Ć";s:2:"Ć";s:3:"ć";s:2:"ć";s:3:"Ĉ";s:2:"Ĉ";s:3:"ĉ";s:2:"ĉ";s:3:"Ċ";s:2:"Ċ";s:3:"ċ";s:2:"ċ";s:3:"Č";s:2:"Č";s:3:"č";s:2:"č";s:3:"Ď";s:2:"Ď";s:3:"ď";s:2:"ď";s:3:"Ē";s:2:"Ē";s:3:"ē";s:2:"ē";s:3:"Ĕ";s:2:"Ĕ";s:3:"ĕ";s:2:"ĕ";s:3:"Ė";s:2:"Ė";s:3:"ė";s:2:"ė";s:3:"Ę";s:2:"Ę";s:3:"ę";s:2:"ę";s:3:"Ě";s:2:"Ě";s:3:"ě";s:2:"ě";s:3:"Ĝ";s:2:"Ĝ";s:3:"ĝ";s:2:"ĝ";s:3:"Ğ";s:2:"Ğ";s:3:"ğ";s:2:"ğ";s:3:"Ġ";s:2:"Ġ";s:3:"ġ";s:2:"ġ";s:3:"Ģ";s:2:"Ģ";s:3:"ģ";s:2:"ģ";s:3:"Ĥ";s:2:"Ĥ";s:3:"ĥ";s:2:"ĥ";s:3:"Ĩ";s:2:"Ĩ";s:3:"ĩ";s:2:"ĩ";s:3:"Ī";s:2:"Ī";s:3:"ī";s:2:"ī";s:3:"Ĭ";s:2:"Ĭ";s:3:"ĭ";s:2:"ĭ";s:3:"Į";s:2:"Į";s:3:"į";s:2:"į";s:3:"İ";s:2:"İ";s:3:"Ĵ";s:2:"Ĵ";s:3:"ĵ";s:2:"ĵ";s:3:"Ķ";s:2:"Ķ";s:3:"ķ";s:2:"ķ";s:3:"Ĺ";s:2:"Ĺ";s:3:"ĺ";s:2:"ĺ";s:3:"Ļ";s:2:"Ļ";s:3:"ļ";s:2:"ļ";s:3:"Ľ";s:2:"Ľ";s:3:"ľ";s:2:"ľ";s:3:"Ń";s:2:"Ń";s:3:"ń";s:2:"ń";s:3:"Ņ";s:2:"Ņ";s:3:"ņ";s:2:"ņ";s:3:"Ň";s:2:"Ň";s:3:"ň";s:2:"ň";s:3:"Ō";s:2:"Ō";s:3:"ō";s:2:"ō";s:3:"Ŏ";s:2:"Ŏ";s:3:"ŏ";s:2:"ŏ";s:3:"Ő";s:2:"Ő";s:3:"ő";s:2:"ő";s:3:"Ŕ";s:2:"Ŕ";s:3:"ŕ";s:2:"ŕ";s:3:"Ŗ";s:2:"Ŗ";s:3:"ŗ";s:2:"ŗ";s:3:"Ř";s:2:"Ř";s:3:"ř";s:2:"ř";s:3:"Ś";s:2:"Ś";s:3:"ś";s:2:"ś";s:3:"Ŝ";s:2:"Ŝ";s:3:"ŝ";s:2:"ŝ";s:3:"Ş";s:2:"Ş";s:3:"ş";s:2:"ş";s:3:"Š";s:2:"Š";s:3:"š";s:2:"š";s:3:"Ţ";s:2:"Ţ";s:3:"ţ";s:2:"ţ";s:3:"Ť";s:2:"Ť";s:3:"ť";s:2:"ť";s:3:"Ũ";s:2:"Ũ";s:3:"ũ";s:2:"ũ";s:3:"Ū";s:2:"Ū";s:3:"ū";s:2:"ū";s:3:"Ŭ";s:2:"Ŭ";s:3:"ŭ";s:2:"ŭ";s:3:"Ů";s:2:"Ů";s:3:"ů";s:2:"ů";s:3:"Ű";s:2:"Ű";s:3:"ű";s:2:"ű";s:3:"Ų";s:2:"Ų";s:3:"ų";s:2:"ų";s:3:"Ŵ";s:2:"Ŵ";s:3:"ŵ";s:2:"ŵ";s:3:"Ŷ";s:2:"Ŷ";s:3:"ŷ";s:2:"ŷ";s:3:"Ÿ";s:2:"Ÿ";s:3:"Ź";s:2:"Ź";s:3:"ź";s:2:"ź";s:3:"Ż";s:2:"Ż";s:3:"ż";s:2:"ż";s:3:"Ž";s:2:"Ž";s:3:"ž";s:2:"ž";s:3:"Ơ";s:2:"Ơ";s:3:"ơ";s:2:"ơ";s:3:"Ư";s:2:"Ư";s:3:"ư";s:2:"ư";s:3:"Ǎ";s:2:"Ǎ";s:3:"ǎ";s:2:"ǎ";s:3:"Ǐ";s:2:"Ǐ";s:3:"ǐ";s:2:"ǐ";s:3:"Ǒ";s:2:"Ǒ";s:3:"ǒ";s:2:"ǒ";s:3:"Ǔ";s:2:"Ǔ";s:3:"ǔ";s:2:"ǔ";s:4:"Ǖ";s:2:"Ǖ";s:4:"ǖ";s:2:"ǖ";s:4:"Ǘ";s:2:"Ǘ";s:4:"ǘ";s:2:"ǘ";s:4:"Ǚ";s:2:"Ǚ";s:4:"ǚ";s:2:"ǚ";s:4:"Ǜ";s:2:"Ǜ";s:4:"ǜ";s:2:"ǜ";s:4:"Ǟ";s:2:"Ǟ";s:4:"ǟ";s:2:"ǟ";s:4:"Ǡ";s:2:"Ǡ";s:4:"ǡ";s:2:"ǡ";s:4:"Ǣ";s:2:"Ǣ";s:4:"ǣ";s:2:"ǣ";s:3:"Ǧ";s:2:"Ǧ";s:3:"ǧ";s:2:"ǧ";s:3:"Ǩ";s:2:"Ǩ";s:3:"ǩ";s:2:"ǩ";s:3:"Ǫ";s:2:"Ǫ";s:3:"ǫ";s:2:"ǫ";s:4:"Ǭ";s:2:"Ǭ";s:4:"ǭ";s:2:"ǭ";s:4:"Ǯ";s:2:"Ǯ";s:4:"ǯ";s:2:"ǯ";s:3:"ǰ";s:2:"ǰ";s:3:"Ǵ";s:2:"Ǵ";s:3:"ǵ";s:2:"ǵ";s:3:"Ǹ";s:2:"Ǹ";s:3:"ǹ";s:2:"ǹ";s:4:"Ǻ";s:2:"Ǻ";s:4:"ǻ";s:2:"ǻ";s:4:"Ǽ";s:2:"Ǽ";s:4:"ǽ";s:2:"ǽ";s:4:"Ǿ";s:2:"Ǿ";s:4:"ǿ";s:2:"ǿ";s:3:"Ȁ";s:2:"Ȁ";s:3:"ȁ";s:2:"ȁ";s:3:"Ȃ";s:2:"Ȃ";s:3:"ȃ";s:2:"ȃ";s:3:"Ȅ";s:2:"Ȅ";s:3:"ȅ";s:2:"ȅ";s:3:"Ȇ";s:2:"Ȇ";s:3:"ȇ";s:2:"ȇ";s:3:"Ȉ";s:2:"Ȉ";s:3:"ȉ";s:2:"ȉ";s:3:"Ȋ";s:2:"Ȋ";s:3:"ȋ";s:2:"ȋ";s:3:"Ȍ";s:2:"Ȍ";s:3:"ȍ";s:2:"ȍ";s:3:"Ȏ";s:2:"Ȏ";s:3:"ȏ";s:2:"ȏ";s:3:"Ȑ";s:2:"Ȑ";s:3:"ȑ";s:2:"ȑ";s:3:"Ȓ";s:2:"Ȓ";s:3:"ȓ";s:2:"ȓ";s:3:"Ȕ";s:2:"Ȕ";s:3:"ȕ";s:2:"ȕ";s:3:"Ȗ";s:2:"Ȗ";s:3:"ȗ";s:2:"ȗ";s:3:"Ș";s:2:"Ș";s:3:"ș";s:2:"ș";s:3:"Ț";s:2:"Ț";s:3:"ț";s:2:"ț";s:3:"Ȟ";s:2:"Ȟ";s:3:"ȟ";s:2:"ȟ";s:3:"Ȧ";s:2:"Ȧ";s:3:"ȧ";s:2:"ȧ";s:3:"Ȩ";s:2:"Ȩ";s:3:"ȩ";s:2:"ȩ";s:4:"Ȫ";s:2:"Ȫ";s:4:"ȫ";s:2:"ȫ";s:4:"Ȭ";s:2:"Ȭ";s:4:"ȭ";s:2:"ȭ";s:3:"Ȯ";s:2:"Ȯ";s:3:"ȯ";s:2:"ȯ";s:4:"Ȱ";s:2:"Ȱ";s:4:"ȱ";s:2:"ȱ";s:3:"Ȳ";s:2:"Ȳ";s:3:"ȳ";s:2:"ȳ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:4:"̈́";s:2:"̈́";s:2:"ʹ";s:2:"ʹ";s:1:";";s:2:";";s:4:"΅";s:2:"΅";s:4:"Ά";s:2:"Ά";s:2:"·";s:2:"·";s:4:"Έ";s:2:"Έ";s:4:"Ή";s:2:"Ή";s:4:"Ί";s:2:"Ί";s:4:"Ό";s:2:"Ό";s:4:"Ύ";s:2:"Ύ";s:4:"Ώ";s:2:"Ώ";s:4:"ΐ";s:2:"ΐ";s:4:"Ϊ";s:2:"Ϊ";s:4:"Ϋ";s:2:"Ϋ";s:4:"ά";s:2:"ά";s:4:"έ";s:2:"έ";s:4:"ή";s:2:"ή";s:4:"ί";s:2:"ί";s:4:"ΰ";s:2:"ΰ";s:4:"ϊ";s:2:"ϊ";s:4:"ϋ";s:2:"ϋ";s:4:"ό";s:2:"ό";s:4:"ύ";s:2:"ύ";s:4:"ώ";s:2:"ώ";s:4:"ϓ";s:2:"ϓ";s:4:"ϔ";s:2:"ϔ";s:4:"Ѐ";s:2:"Ѐ";s:4:"Ё";s:2:"Ё";s:4:"Ѓ";s:2:"Ѓ";s:4:"Ї";s:2:"Ї";s:4:"Ќ";s:2:"Ќ";s:4:"Ѝ";s:2:"Ѝ";s:4:"Ў";s:2:"Ў";s:4:"Й";s:2:"Й";s:4:"й";s:2:"й";s:4:"ѐ";s:2:"ѐ";s:4:"ё";s:2:"ё";s:4:"ѓ";s:2:"ѓ";s:4:"ї";s:2:"ї";s:4:"ќ";s:2:"ќ";s:4:"ѝ";s:2:"ѝ";s:4:"ў";s:2:"ў";s:4:"Ѷ";s:2:"Ѷ";s:4:"ѷ";s:2:"ѷ";s:4:"Ӂ";s:2:"Ӂ";s:4:"ӂ";s:2:"ӂ";s:4:"Ӑ";s:2:"Ӑ";s:4:"ӑ";s:2:"ӑ";s:4:"Ӓ";s:2:"Ӓ";s:4:"ӓ";s:2:"ӓ";s:4:"Ӗ";s:2:"Ӗ";s:4:"ӗ";s:2:"ӗ";s:4:"Ӛ";s:2:"Ӛ";s:4:"ӛ";s:2:"ӛ";s:4:"Ӝ";s:2:"Ӝ";s:4:"ӝ";s:2:"ӝ";s:4:"Ӟ";s:2:"Ӟ";s:4:"ӟ";s:2:"ӟ";s:4:"Ӣ";s:2:"Ӣ";s:4:"ӣ";s:2:"ӣ";s:4:"Ӥ";s:2:"Ӥ";s:4:"ӥ";s:2:"ӥ";s:4:"Ӧ";s:2:"Ӧ";s:4:"ӧ";s:2:"ӧ";s:4:"Ӫ";s:2:"Ӫ";s:4:"ӫ";s:2:"ӫ";s:4:"Ӭ";s:2:"Ӭ";s:4:"ӭ";s:2:"ӭ";s:4:"Ӯ";s:2:"Ӯ";s:4:"ӯ";s:2:"ӯ";s:4:"Ӱ";s:2:"Ӱ";s:4:"ӱ";s:2:"ӱ";s:4:"Ӳ";s:2:"Ӳ";s:4:"ӳ";s:2:"ӳ";s:4:"Ӵ";s:2:"Ӵ";s:4:"ӵ";s:2:"ӵ";s:4:"Ӹ";s:2:"Ӹ";s:4:"ӹ";s:2:"ӹ";s:4:"آ";s:2:"آ";s:4:"أ";s:2:"أ";s:4:"ؤ";s:2:"ؤ";s:4:"إ";s:2:"إ";s:4:"ئ";s:2:"ئ";s:4:"ۀ";s:2:"ۀ";s:4:"ۂ";s:2:"ۂ";s:4:"ۓ";s:2:"ۓ";s:6:"ऩ";s:3:"ऩ";s:6:"ऱ";s:3:"ऱ";s:6:"ऴ";s:3:"ऴ";s:6:"ো";s:3:"ো";s:6:"ৌ";s:3:"ৌ";s:6:"ୈ";s:3:"ୈ";s:6:"ୋ";s:3:"ୋ";s:6:"ୌ";s:3:"ୌ";s:6:"ஔ";s:3:"ஔ";s:6:"ொ";s:3:"ொ";s:6:"ோ";s:3:"ோ";s:6:"ௌ";s:3:"ௌ";s:6:"ై";s:3:"ై";s:6:"ೀ";s:3:"ೀ";s:6:"ೇ";s:3:"ೇ";s:6:"ೈ";s:3:"ೈ";s:6:"ೊ";s:3:"ೊ";s:6:"ೋ";s:3:"ೋ";s:6:"ൊ";s:3:"ൊ";s:6:"ോ";s:3:"ോ";s:6:"ൌ";s:3:"ൌ";s:6:"ේ";s:3:"ේ";s:6:"ො";s:3:"ො";s:6:"ෝ";s:3:"ෝ";s:6:"ෞ";s:3:"ෞ";s:6:"ཱི";s:3:"ཱི";s:6:"ཱུ";s:3:"ཱུ";s:6:"ཱྀ";s:3:"ཱྀ";s:6:"ဦ";s:3:"ဦ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:4:"Ḉ";s:3:"Ḉ";s:4:"ḉ";s:3:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:4:"Ḕ";s:3:"Ḕ";s:4:"ḕ";s:3:"ḕ";s:4:"Ḗ";s:3:"Ḗ";s:4:"ḗ";s:3:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:4:"Ḝ";s:3:"Ḝ";s:4:"ḝ";s:3:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:4:"Ḯ";s:3:"Ḯ";s:4:"ḯ";s:3:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:5:"Ḹ";s:3:"Ḹ";s:5:"ḹ";s:3:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:4:"Ṍ";s:3:"Ṍ";s:4:"ṍ";s:3:"ṍ";s:4:"Ṏ";s:3:"Ṏ";s:4:"ṏ";s:3:"ṏ";s:4:"Ṑ";s:3:"Ṑ";s:4:"ṑ";s:3:"ṑ";s:4:"Ṓ";s:3:"Ṓ";s:4:"ṓ";s:3:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:5:"Ṝ";s:3:"Ṝ";s:5:"ṝ";s:3:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:4:"Ṥ";s:3:"Ṥ";s:4:"ṥ";s:3:"ṥ";s:4:"Ṧ";s:3:"Ṧ";s:4:"ṧ";s:3:"ṧ";s:5:"Ṩ";s:3:"Ṩ";s:5:"ṩ";s:3:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:4:"Ṹ";s:3:"Ṹ";s:4:"ṹ";s:3:"ṹ";s:4:"Ṻ";s:3:"Ṻ";s:4:"ṻ";s:3:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:4:"ẛ";s:3:"ẛ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:4:"Ấ";s:3:"Ấ";s:4:"ấ";s:3:"ấ";s:4:"Ầ";s:3:"Ầ";s:4:"ầ";s:3:"ầ";s:4:"Ẩ";s:3:"Ẩ";s:4:"ẩ";s:3:"ẩ";s:4:"Ẫ";s:3:"Ẫ";s:4:"ẫ";s:3:"ẫ";s:5:"Ậ";s:3:"Ậ";s:5:"ậ";s:3:"ậ";s:4:"Ắ";s:3:"Ắ";s:4:"ắ";s:3:"ắ";s:4:"Ằ";s:3:"Ằ";s:4:"ằ";s:3:"ằ";s:4:"Ẳ";s:3:"Ẳ";s:4:"ẳ";s:3:"ẳ";s:4:"Ẵ";s:3:"Ẵ";s:4:"ẵ";s:3:"ẵ";s:5:"Ặ";s:3:"Ặ";s:5:"ặ";s:3:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:4:"Ế";s:3:"Ế";s:4:"ế";s:3:"ế";s:4:"Ề";s:3:"Ề";s:4:"ề";s:3:"ề";s:4:"Ể";s:3:"Ể";s:4:"ể";s:3:"ể";s:4:"Ễ";s:3:"Ễ";s:4:"ễ";s:3:"ễ";s:5:"Ệ";s:3:"Ệ";s:5:"ệ";s:3:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:4:"Ố";s:3:"Ố";s:4:"ố";s:3:"ố";s:4:"Ồ";s:3:"Ồ";s:4:"ồ";s:3:"ồ";s:4:"Ổ";s:3:"Ổ";s:4:"ổ";s:3:"ổ";s:4:"Ỗ";s:3:"Ỗ";s:4:"ỗ";s:3:"ỗ";s:5:"Ộ";s:3:"Ộ";s:5:"ộ";s:3:"ộ";s:4:"Ớ";s:3:"Ớ";s:4:"ớ";s:3:"ớ";s:4:"Ờ";s:3:"Ờ";s:4:"ờ";s:3:"ờ";s:4:"Ở";s:3:"Ở";s:4:"ở";s:3:"ở";s:4:"Ỡ";s:3:"Ỡ";s:4:"ỡ";s:3:"ỡ";s:4:"Ợ";s:3:"Ợ";s:4:"ợ";s:3:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:4:"Ứ";s:3:"Ứ";s:4:"ứ";s:3:"ứ";s:4:"Ừ";s:3:"Ừ";s:4:"ừ";s:3:"ừ";s:4:"Ử";s:3:"Ử";s:4:"ử";s:3:"ử";s:4:"Ữ";s:3:"Ữ";s:4:"ữ";s:3:"ữ";s:4:"Ự";s:3:"Ự";s:4:"ự";s:3:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:4:"ἀ";s:3:"ἀ";s:4:"ἁ";s:3:"ἁ";s:5:"ἂ";s:3:"ἂ";s:5:"ἃ";s:3:"ἃ";s:5:"ἄ";s:3:"ἄ";s:5:"ἅ";s:3:"ἅ";s:5:"ἆ";s:3:"ἆ";s:5:"ἇ";s:3:"ἇ";s:4:"Ἀ";s:3:"Ἀ";s:4:"Ἁ";s:3:"Ἁ";s:5:"Ἂ";s:3:"Ἂ";s:5:"Ἃ";s:3:"Ἃ";s:5:"Ἄ";s:3:"Ἄ";s:5:"Ἅ";s:3:"Ἅ";s:5:"Ἆ";s:3:"Ἆ";s:5:"Ἇ";s:3:"Ἇ";s:4:"ἐ";s:3:"ἐ";s:4:"ἑ";s:3:"ἑ";s:5:"ἒ";s:3:"ἒ";s:5:"ἓ";s:3:"ἓ";s:5:"ἔ";s:3:"ἔ";s:5:"ἕ";s:3:"ἕ";s:4:"Ἐ";s:3:"Ἐ";s:4:"Ἑ";s:3:"Ἑ";s:5:"Ἒ";s:3:"Ἒ";s:5:"Ἓ";s:3:"Ἓ";s:5:"Ἔ";s:3:"Ἔ";s:5:"Ἕ";s:3:"Ἕ";s:4:"ἠ";s:3:"ἠ";s:4:"ἡ";s:3:"ἡ";s:5:"ἢ";s:3:"ἢ";s:5:"ἣ";s:3:"ἣ";s:5:"ἤ";s:3:"ἤ";s:5:"ἥ";s:3:"ἥ";s:5:"ἦ";s:3:"ἦ";s:5:"ἧ";s:3:"ἧ";s:4:"Ἠ";s:3:"Ἠ";s:4:"Ἡ";s:3:"Ἡ";s:5:"Ἢ";s:3:"Ἢ";s:5:"Ἣ";s:3:"Ἣ";s:5:"Ἤ";s:3:"Ἤ";s:5:"Ἥ";s:3:"Ἥ";s:5:"Ἦ";s:3:"Ἦ";s:5:"Ἧ";s:3:"Ἧ";s:4:"ἰ";s:3:"ἰ";s:4:"ἱ";s:3:"ἱ";s:5:"ἲ";s:3:"ἲ";s:5:"ἳ";s:3:"ἳ";s:5:"ἴ";s:3:"ἴ";s:5:"ἵ";s:3:"ἵ";s:5:"ἶ";s:3:"ἶ";s:5:"ἷ";s:3:"ἷ";s:4:"Ἰ";s:3:"Ἰ";s:4:"Ἱ";s:3:"Ἱ";s:5:"Ἲ";s:3:"Ἲ";s:5:"Ἳ";s:3:"Ἳ";s:5:"Ἴ";s:3:"Ἴ";s:5:"Ἵ";s:3:"Ἵ";s:5:"Ἶ";s:3:"Ἶ";s:5:"Ἷ";s:3:"Ἷ";s:4:"ὀ";s:3:"ὀ";s:4:"ὁ";s:3:"ὁ";s:5:"ὂ";s:3:"ὂ";s:5:"ὃ";s:3:"ὃ";s:5:"ὄ";s:3:"ὄ";s:5:"ὅ";s:3:"ὅ";s:4:"Ὀ";s:3:"Ὀ";s:4:"Ὁ";s:3:"Ὁ";s:5:"Ὂ";s:3:"Ὂ";s:5:"Ὃ";s:3:"Ὃ";s:5:"Ὄ";s:3:"Ὄ";s:5:"Ὅ";s:3:"Ὅ";s:4:"ὐ";s:3:"ὐ";s:4:"ὑ";s:3:"ὑ";s:5:"ὒ";s:3:"ὒ";s:5:"ὓ";s:3:"ὓ";s:5:"ὔ";s:3:"ὔ";s:5:"ὕ";s:3:"ὕ";s:5:"ὖ";s:3:"ὖ";s:5:"ὗ";s:3:"ὗ";s:4:"Ὑ";s:3:"Ὑ";s:5:"Ὓ";s:3:"Ὓ";s:5:"Ὕ";s:3:"Ὕ";s:5:"Ὗ";s:3:"Ὗ";s:4:"ὠ";s:3:"ὠ";s:4:"ὡ";s:3:"ὡ";s:5:"ὢ";s:3:"ὢ";s:5:"ὣ";s:3:"ὣ";s:5:"ὤ";s:3:"ὤ";s:5:"ὥ";s:3:"ὥ";s:5:"ὦ";s:3:"ὦ";s:5:"ὧ";s:3:"ὧ";s:4:"Ὠ";s:3:"Ὠ";s:4:"Ὡ";s:3:"Ὡ";s:5:"Ὢ";s:3:"Ὢ";s:5:"Ὣ";s:3:"Ὣ";s:5:"Ὤ";s:3:"Ὤ";s:5:"Ὥ";s:3:"Ὥ";s:5:"Ὦ";s:3:"Ὦ";s:5:"Ὧ";s:3:"Ὧ";s:4:"ὰ";s:3:"ὰ";s:2:"ά";s:3:"ά";s:4:"ὲ";s:3:"ὲ";s:2:"έ";s:3:"έ";s:4:"ὴ";s:3:"ὴ";s:2:"ή";s:3:"ή";s:4:"ὶ";s:3:"ὶ";s:2:"ί";s:3:"ί";s:4:"ὸ";s:3:"ὸ";s:2:"ό";s:3:"ό";s:4:"ὺ";s:3:"ὺ";s:2:"ύ";s:3:"ύ";s:4:"ὼ";s:3:"ὼ";s:2:"ώ";s:3:"ώ";s:5:"ᾀ";s:3:"ᾀ";s:5:"ᾁ";s:3:"ᾁ";s:5:"ᾂ";s:3:"ᾂ";s:5:"ᾃ";s:3:"ᾃ";s:5:"ᾄ";s:3:"ᾄ";s:5:"ᾅ";s:3:"ᾅ";s:5:"ᾆ";s:3:"ᾆ";s:5:"ᾇ";s:3:"ᾇ";s:5:"ᾈ";s:3:"ᾈ";s:5:"ᾉ";s:3:"ᾉ";s:5:"ᾊ";s:3:"ᾊ";s:5:"ᾋ";s:3:"ᾋ";s:5:"ᾌ";s:3:"ᾌ";s:5:"ᾍ";s:3:"ᾍ";s:5:"ᾎ";s:3:"ᾎ";s:5:"ᾏ";s:3:"ᾏ";s:5:"ᾐ";s:3:"ᾐ";s:5:"ᾑ";s:3:"ᾑ";s:5:"ᾒ";s:3:"ᾒ";s:5:"ᾓ";s:3:"ᾓ";s:5:"ᾔ";s:3:"ᾔ";s:5:"ᾕ";s:3:"ᾕ";s:5:"ᾖ";s:3:"ᾖ";s:5:"ᾗ";s:3:"ᾗ";s:5:"ᾘ";s:3:"ᾘ";s:5:"ᾙ";s:3:"ᾙ";s:5:"ᾚ";s:3:"ᾚ";s:5:"ᾛ";s:3:"ᾛ";s:5:"ᾜ";s:3:"ᾜ";s:5:"ᾝ";s:3:"ᾝ";s:5:"ᾞ";s:3:"ᾞ";s:5:"ᾟ";s:3:"ᾟ";s:5:"ᾠ";s:3:"ᾠ";s:5:"ᾡ";s:3:"ᾡ";s:5:"ᾢ";s:3:"ᾢ";s:5:"ᾣ";s:3:"ᾣ";s:5:"ᾤ";s:3:"ᾤ";s:5:"ᾥ";s:3:"ᾥ";s:5:"ᾦ";s:3:"ᾦ";s:5:"ᾧ";s:3:"ᾧ";s:5:"ᾨ";s:3:"ᾨ";s:5:"ᾩ";s:3:"ᾩ";s:5:"ᾪ";s:3:"ᾪ";s:5:"ᾫ";s:3:"ᾫ";s:5:"ᾬ";s:3:"ᾬ";s:5:"ᾭ";s:3:"ᾭ";s:5:"ᾮ";s:3:"ᾮ";s:5:"ᾯ";s:3:"ᾯ";s:4:"ᾰ";s:3:"ᾰ";s:4:"ᾱ";s:3:"ᾱ";s:5:"ᾲ";s:3:"ᾲ";s:4:"ᾳ";s:3:"ᾳ";s:4:"ᾴ";s:3:"ᾴ";s:4:"ᾶ";s:3:"ᾶ";s:5:"ᾷ";s:3:"ᾷ";s:4:"Ᾰ";s:3:"Ᾰ";s:4:"Ᾱ";s:3:"Ᾱ";s:4:"Ὰ";s:3:"Ὰ";s:2:"Ά";s:3:"Ά";s:4:"ᾼ";s:3:"ᾼ";s:2:"ι";s:3:"ι";s:4:"῁";s:3:"῁";s:5:"ῂ";s:3:"ῂ";s:4:"ῃ";s:3:"ῃ";s:4:"ῄ";s:3:"ῄ";s:4:"ῆ";s:3:"ῆ";s:5:"ῇ";s:3:"ῇ";s:4:"Ὲ";s:3:"Ὲ";s:2:"Έ";s:3:"Έ";s:4:"Ὴ";s:3:"Ὴ";s:2:"Ή";s:3:"Ή";s:4:"ῌ";s:3:"ῌ";s:5:"῍";s:3:"῍";s:5:"῎";s:3:"῎";s:5:"῏";s:3:"῏";s:4:"ῐ";s:3:"ῐ";s:4:"ῑ";s:3:"ῑ";s:4:"ῒ";s:3:"ῒ";s:2:"ΐ";s:3:"ΐ";s:4:"ῖ";s:3:"ῖ";s:4:"ῗ";s:3:"ῗ";s:4:"Ῐ";s:3:"Ῐ";s:4:"Ῑ";s:3:"Ῑ";s:4:"Ὶ";s:3:"Ὶ";s:2:"Ί";s:3:"Ί";s:5:"῝";s:3:"῝";s:5:"῞";s:3:"῞";s:5:"῟";s:3:"῟";s:4:"ῠ";s:3:"ῠ";s:4:"ῡ";s:3:"ῡ";s:4:"ῢ";s:3:"ῢ";s:2:"ΰ";s:3:"ΰ";s:4:"ῤ";s:3:"ῤ";s:4:"ῥ";s:3:"ῥ";s:4:"ῦ";s:3:"ῦ";s:4:"ῧ";s:3:"ῧ";s:4:"Ῠ";s:3:"Ῠ";s:4:"Ῡ";s:3:"Ῡ";s:4:"Ὺ";s:3:"Ὺ";s:2:"Ύ";s:3:"Ύ";s:4:"Ῥ";s:3:"Ῥ";s:4:"῭";s:3:"῭";s:2:"΅";s:3:"΅";s:1:"`";s:3:"`";s:5:"ῲ";s:3:"ῲ";s:4:"ῳ";s:3:"ῳ";s:4:"ῴ";s:3:"ῴ";s:4:"ῶ";s:3:"ῶ";s:5:"ῷ";s:3:"ῷ";s:4:"Ὸ";s:3:"Ὸ";s:2:"Ό";s:3:"Ό";s:4:"Ὼ";s:3:"Ὼ";s:2:"Ώ";s:3:"Ώ";s:4:"ῼ";s:3:"ῼ";s:2:"´";s:3:"´";s:3:" ";s:3:" ";s:3:" ";s:3:" ";s:2:"Ω";s:3:"Ω";s:1:"K";s:3:"K";s:2:"Å";s:3:"Å";s:5:"↚";s:3:"↚";s:5:"↛";s:3:"↛";s:5:"↮";s:3:"↮";s:5:"⇍";s:3:"⇍";s:5:"⇎";s:3:"⇎";s:5:"⇏";s:3:"⇏";s:5:"∄";s:3:"∄";s:5:"∉";s:3:"∉";s:5:"∌";s:3:"∌";s:5:"∤";s:3:"∤";s:5:"∦";s:3:"∦";s:5:"≁";s:3:"≁";s:5:"≄";s:3:"≄";s:5:"≇";s:3:"≇";s:5:"≉";s:3:"≉";s:3:"≠";s:3:"≠";s:5:"≢";s:3:"≢";s:5:"≭";s:3:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:5:"≰";s:3:"≰";s:5:"≱";s:3:"≱";s:5:"≴";s:3:"≴";s:5:"≵";s:3:"≵";s:5:"≸";s:3:"≸";s:5:"≹";s:3:"≹";s:5:"⊀";s:3:"⊀";s:5:"⊁";s:3:"⊁";s:5:"⊄";s:3:"⊄";s:5:"⊅";s:3:"⊅";s:5:"⊈";s:3:"⊈";s:5:"⊉";s:3:"⊉";s:5:"⊬";s:3:"⊬";s:5:"⊭";s:3:"⊭";s:5:"⊮";s:3:"⊮";s:5:"⊯";s:3:"⊯";s:5:"⋠";s:3:"⋠";s:5:"⋡";s:3:"⋡";s:5:"⋢";s:3:"⋢";s:5:"⋣";s:3:"⋣";s:5:"⋪";s:3:"⋪";s:5:"⋫";s:3:"⋫";s:5:"⋬";s:3:"⋬";s:5:"⋭";s:3:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:6:"が";s:3:"が";s:6:"ぎ";s:3:"ぎ";s:6:"ぐ";s:3:"ぐ";s:6:"げ";s:3:"げ";s:6:"ご";s:3:"ご";s:6:"ざ";s:3:"ざ";s:6:"じ";s:3:"じ";s:6:"ず";s:3:"ず";s:6:"ぜ";s:3:"ぜ";s:6:"ぞ";s:3:"ぞ";s:6:"だ";s:3:"だ";s:6:"ぢ";s:3:"ぢ";s:6:"づ";s:3:"づ";s:6:"で";s:3:"で";s:6:"ど";s:3:"ど";s:6:"ば";s:3:"ば";s:6:"ぱ";s:3:"ぱ";s:6:"び";s:3:"び";s:6:"ぴ";s:3:"ぴ";s:6:"ぶ";s:3:"ぶ";s:6:"ぷ";s:3:"ぷ";s:6:"べ";s:3:"べ";s:6:"ぺ";s:3:"ぺ";s:6:"ぼ";s:3:"ぼ";s:6:"ぽ";s:3:"ぽ";s:6:"ゔ";s:3:"ゔ";s:6:"ゞ";s:3:"ゞ";s:6:"ガ";s:3:"ガ";s:6:"ギ";s:3:"ギ";s:6:"グ";s:3:"グ";s:6:"ゲ";s:3:"ゲ";s:6:"ゴ";s:3:"ゴ";s:6:"ザ";s:3:"ザ";s:6:"ジ";s:3:"ジ";s:6:"ズ";s:3:"ズ";s:6:"ゼ";s:3:"ゼ";s:6:"ゾ";s:3:"ゾ";s:6:"ダ";s:3:"ダ";s:6:"ヂ";s:3:"ヂ";s:6:"ヅ";s:3:"ヅ";s:6:"デ";s:3:"デ";s:6:"ド";s:3:"ド";s:6:"バ";s:3:"バ";s:6:"パ";s:3:"パ";s:6:"ビ";s:3:"ビ";s:6:"ピ";s:3:"ピ";s:6:"ブ";s:3:"ブ";s:6:"プ";s:3:"プ";s:6:"ベ";s:3:"ベ";s:6:"ペ";s:3:"ペ";s:6:"ボ";s:3:"ボ";s:6:"ポ";s:3:"ポ";s:6:"ヴ";s:3:"ヴ";s:6:"ヷ";s:3:"ヷ";s:6:"ヸ";s:3:"ヸ";s:6:"ヹ";s:3:"ヹ";s:6:"ヺ";s:3:"ヺ";s:6:"ヾ";s:3:"ヾ";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:4:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:4:"廊";s:3:"朗";s:4:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:4:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:4:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:4:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:4:"異";s:3:"北";s:4:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:4:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:4:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:4:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:4:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:4:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:4:"侮";s:3:"僧";s:4:"僧";s:3:"免";s:4:"免";s:3:"勉";s:4:"勉";s:3:"勤";s:4:"勤";s:3:"卑";s:4:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:4:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:4:"屮";s:3:"悔";s:4:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:4:"憎";s:3:"懲";s:4:"懲";s:3:"敏";s:4:"敏";s:3:"既";s:3:"既";s:3:"暑";s:4:"暑";s:3:"梅";s:4:"梅";s:3:"海";s:4:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:4:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:4:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:4:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"著";s:4:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:4:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:4:"勇";s:3:"勺";s:4:"勺";s:3:"啕";s:3:"啕";s:3:"喙";s:4:"喙";s:3:"嗢";s:3:"嗢";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:4:"慎";s:3:"愈";s:3:"愈";s:3:"慠";s:3:"慠";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"望";s:4:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"滛";s:3:"滛";s:3:"滋";s:4:"滋";s:3:"瀞";s:4:"瀞";s:3:"瞧";s:3:"瞧";s:3:"爵";s:4:"爵";s:3:"犯";s:3:"犯";s:3:"瑱";s:4:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"盛";s:3:"盛";s:3:"直";s:4:"直";s:3:"睊";s:4:"睊";s:3:"着";s:3:"着";s:3:"磌";s:4:"磌";s:3:"窱";s:3:"窱";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"缾";s:3:"缾";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:4:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"調";s:3:"調";s:3:"請";s:3:"請";s:3:"諭";s:4:"諭";s:3:"變";s:4:"變";s:3:"輸";s:4:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"韛";s:3:"韛";s:3:"頋";s:4:"頋";s:3:"鬒";s:4:"鬒";s:4:"𢡊";s:3:"𢡊";s:4:"𢡄";s:3:"𢡄";s:4:"𣏕";s:3:"𣏕";s:3:"㮝";s:4:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:4:"䀹";s:4:"𥉉";s:3:"𥉉";s:4:"𥳐";s:3:"𥳐";s:4:"𧻓";s:3:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"丽";s:4:"丽";s:3:"丸";s:4:"丸";s:3:"乁";s:4:"乁";s:4:"𠄢";s:4:"𠄢";s:3:"你";s:4:"你";s:3:"侻";s:4:"侻";s:3:"倂";s:4:"倂";s:3:"偺";s:4:"偺";s:3:"備";s:4:"備";s:3:"像";s:4:"像";s:3:"㒞";s:4:"㒞";s:4:"𠘺";s:4:"𠘺";s:3:"兔";s:4:"兔";s:3:"兤";s:4:"兤";s:3:"具";s:4:"具";s:4:"𠔜";s:4:"𠔜";s:3:"㒹";s:4:"㒹";s:3:"內";s:4:"內";s:3:"再";s:4:"再";s:4:"𠕋";s:4:"𠕋";s:3:"冗";s:4:"冗";s:3:"冤";s:4:"冤";s:3:"仌";s:4:"仌";s:3:"冬";s:4:"冬";s:4:"𩇟";s:4:"𩇟";s:3:"凵";s:4:"凵";s:3:"刃";s:4:"刃";s:3:"㓟";s:4:"㓟";s:3:"刻";s:4:"刻";s:3:"剆";s:4:"剆";s:3:"割";s:4:"割";s:3:"剷";s:4:"剷";s:3:"㔕";s:4:"㔕";s:3:"包";s:4:"包";s:3:"匆";s:4:"匆";s:3:"卉";s:4:"卉";s:3:"博";s:4:"博";s:3:"即";s:4:"即";s:3:"卽";s:4:"卽";s:3:"卿";s:4:"卿";s:4:"𠨬";s:4:"𠨬";s:3:"灰";s:4:"灰";s:3:"及";s:4:"及";s:3:"叟";s:4:"叟";s:4:"𠭣";s:4:"𠭣";s:3:"叫";s:4:"叫";s:3:"叱";s:4:"叱";s:3:"吆";s:4:"吆";s:3:"咞";s:4:"咞";s:3:"吸";s:4:"吸";s:3:"呈";s:4:"呈";s:3:"周";s:4:"周";s:3:"咢";s:4:"咢";s:3:"哶";s:4:"哶";s:3:"唐";s:4:"唐";s:3:"啓";s:4:"啓";s:3:"啣";s:4:"啣";s:3:"善";s:4:"善";s:3:"喫";s:4:"喫";s:3:"喳";s:4:"喳";s:3:"嗂";s:4:"嗂";s:3:"圖";s:4:"圖";s:3:"圗";s:4:"圗";s:3:"噑";s:4:"噑";s:3:"噴";s:4:"噴";s:3:"壮";s:4:"壮";s:3:"城";s:4:"城";s:3:"埴";s:4:"埴";s:3:"堍";s:4:"堍";s:3:"型";s:4:"型";s:3:"堲";s:4:"堲";s:3:"報";s:4:"報";s:3:"墬";s:4:"墬";s:4:"𡓤";s:4:"𡓤";s:3:"売";s:4:"売";s:3:"壷";s:4:"壷";s:3:"夆";s:4:"夆";s:3:"多";s:4:"多";s:3:"夢";s:4:"夢";s:3:"奢";s:4:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:3:"姬";s:4:"姬";s:3:"娛";s:4:"娛";s:3:"娧";s:4:"娧";s:3:"姘";s:4:"姘";s:3:"婦";s:4:"婦";s:3:"㛮";s:4:"㛮";s:3:"㛼";s:4:"㛼";s:3:"嬈";s:4:"嬈";s:3:"嬾";s:4:"嬾";s:4:"𡧈";s:4:"𡧈";s:3:"寃";s:4:"寃";s:3:"寘";s:4:"寘";s:3:"寳";s:4:"寳";s:4:"𡬘";s:4:"𡬘";s:3:"寿";s:4:"寿";s:3:"将";s:4:"将";s:3:"当";s:4:"当";s:3:"尢";s:4:"尢";s:3:"㞁";s:4:"㞁";s:3:"屠";s:4:"屠";s:3:"峀";s:4:"峀";s:3:"岍";s:4:"岍";s:4:"𡷤";s:4:"𡷤";s:3:"嵃";s:4:"嵃";s:4:"𡷦";s:4:"𡷦";s:3:"嵮";s:4:"嵮";s:3:"嵫";s:4:"嵫";s:3:"嵼";s:4:"嵼";s:3:"巡";s:4:"巡";s:3:"巢";s:4:"巢";s:3:"㠯";s:4:"㠯";s:3:"巽";s:4:"巽";s:3:"帨";s:4:"帨";s:3:"帽";s:4:"帽";s:3:"幩";s:4:"幩";s:3:"㡢";s:4:"㡢";s:4:"𢆃";s:4:"𢆃";s:3:"㡼";s:4:"㡼";s:3:"庰";s:4:"庰";s:3:"庳";s:4:"庳";s:3:"庶";s:4:"庶";s:4:"𪎒";s:4:"𪎒";s:3:"廾";s:4:"廾";s:4:"𢌱";s:4:"𢌱";s:3:"舁";s:4:"舁";s:3:"弢";s:4:"弢";s:3:"㣇";s:4:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:3:"形";s:4:"形";s:3:"彫";s:4:"彫";s:3:"㣣";s:4:"㣣";s:3:"徚";s:4:"徚";s:3:"忍";s:4:"忍";s:3:"志";s:4:"志";s:3:"忹";s:4:"忹";s:3:"悁";s:4:"悁";s:3:"㤺";s:4:"㤺";s:3:"㤜";s:4:"㤜";s:4:"𢛔";s:4:"𢛔";s:3:"惇";s:4:"惇";s:3:"慈";s:4:"慈";s:3:"慌";s:4:"慌";s:3:"慺";s:4:"慺";s:3:"憲";s:4:"憲";s:3:"憤";s:4:"憤";s:3:"憯";s:4:"憯";s:3:"懞";s:4:"懞";s:3:"成";s:4:"成";s:3:"戛";s:4:"戛";s:3:"扝";s:4:"扝";s:3:"抱";s:4:"抱";s:3:"拔";s:4:"拔";s:3:"捐";s:4:"捐";s:4:"𢬌";s:4:"𢬌";s:3:"挽";s:4:"挽";s:3:"拼";s:4:"拼";s:3:"捨";s:4:"捨";s:3:"掃";s:4:"掃";s:3:"揤";s:4:"揤";s:4:"𢯱";s:4:"𢯱";s:3:"搢";s:4:"搢";s:3:"揅";s:4:"揅";s:3:"掩";s:4:"掩";s:3:"㨮";s:4:"㨮";s:3:"摩";s:4:"摩";s:3:"摾";s:4:"摾";s:3:"撝";s:4:"撝";s:3:"摷";s:4:"摷";s:3:"㩬";s:4:"㩬";s:3:"敬";s:4:"敬";s:4:"𣀊";s:4:"𣀊";s:3:"旣";s:4:"旣";s:3:"書";s:4:"書";s:3:"晉";s:4:"晉";s:3:"㬙";s:4:"㬙";s:3:"㬈";s:4:"㬈";s:3:"㫤";s:4:"㫤";s:3:"冒";s:4:"冒";s:3:"冕";s:4:"冕";s:3:"最";s:4:"最";s:3:"暜";s:4:"暜";s:3:"肭";s:4:"肭";s:3:"䏙";s:4:"䏙";s:3:"朡";s:4:"朡";s:3:"杞";s:4:"杞";s:3:"杓";s:4:"杓";s:4:"𣏃";s:4:"𣏃";s:3:"㭉";s:4:"㭉";s:3:"柺";s:4:"柺";s:3:"枅";s:4:"枅";s:3:"桒";s:4:"桒";s:4:"𣑭";s:4:"𣑭";s:3:"梎";s:4:"梎";s:3:"栟";s:4:"栟";s:3:"椔";s:4:"椔";s:3:"楂";s:4:"楂";s:3:"榣";s:4:"榣";s:3:"槪";s:4:"槪";s:3:"檨";s:4:"檨";s:4:"𣚣";s:4:"𣚣";s:3:"櫛";s:4:"櫛";s:3:"㰘";s:4:"㰘";s:3:"次";s:4:"次";s:4:"𣢧";s:4:"𣢧";s:3:"歔";s:4:"歔";s:3:"㱎";s:4:"㱎";s:3:"歲";s:4:"歲";s:3:"殟";s:4:"殟";s:3:"殻";s:4:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:3:"汎";s:4:"汎";s:4:"𣲼";s:4:"𣲼";s:3:"沿";s:4:"沿";s:3:"泍";s:4:"泍";s:3:"汧";s:4:"汧";s:3:"洖";s:4:"洖";s:3:"派";s:4:"派";s:3:"浩";s:4:"浩";s:3:"浸";s:4:"浸";s:3:"涅";s:4:"涅";s:4:"𣴞";s:4:"𣴞";s:3:"洴";s:4:"洴";s:3:"港";s:4:"港";s:3:"湮";s:4:"湮";s:3:"㴳";s:4:"㴳";s:3:"滇";s:4:"滇";s:4:"𣻑";s:4:"𣻑";s:3:"淹";s:4:"淹";s:3:"潮";s:4:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:3:"濆";s:4:"濆";s:3:"瀹";s:4:"瀹";s:3:"瀛";s:4:"瀛";s:3:"㶖";s:4:"㶖";s:3:"灊";s:4:"灊";s:3:"災";s:4:"災";s:3:"灷";s:4:"灷";s:3:"炭";s:4:"炭";s:4:"𠔥";s:4:"𠔥";s:3:"煅";s:4:"煅";s:4:"𤉣";s:4:"𤉣";s:3:"熜";s:4:"熜";s:4:"𤎫";s:4:"𤎫";s:3:"爨";s:4:"爨";s:3:"牐";s:4:"牐";s:4:"𤘈";s:4:"𤘈";s:3:"犀";s:4:"犀";s:3:"犕";s:4:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:3:"獺";s:4:"獺";s:3:"王";s:4:"王";s:3:"㺬";s:4:"㺬";s:3:"玥";s:4:"玥";s:3:"㺸";s:4:"㺸";s:3:"瑇";s:4:"瑇";s:3:"瑜";s:4:"瑜";s:3:"璅";s:4:"璅";s:3:"瓊";s:4:"瓊";s:3:"㼛";s:4:"㼛";s:3:"甤";s:4:"甤";s:4:"𤰶";s:4:"𤰶";s:3:"甾";s:4:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"𢆟";s:4:"𢆟";s:3:"瘐";s:4:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:3:"㿼";s:4:"㿼";s:3:"䀈";s:4:"䀈";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:3:"眞";s:4:"眞";s:3:"真";s:4:"真";s:3:"瞋";s:4:"瞋";s:3:"䁆";s:4:"䁆";s:3:"䂖";s:4:"䂖";s:4:"𥐝";s:4:"𥐝";s:3:"硎";s:4:"硎";s:3:"䃣";s:4:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:3:"秫";s:4:"秫";s:3:"䄯";s:4:"䄯";s:3:"穊";s:4:"穊";s:3:"穏";s:4:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:3:"竮";s:4:"竮";s:3:"䈂";s:4:"䈂";s:4:"𥮫";s:4:"𥮫";s:3:"篆";s:4:"篆";s:3:"築";s:4:"築";s:3:"䈧";s:4:"䈧";s:4:"𥲀";s:4:"𥲀";s:3:"糒";s:4:"糒";s:3:"䊠";s:4:"䊠";s:3:"糨";s:4:"糨";s:3:"糣";s:4:"糣";s:3:"紀";s:4:"紀";s:4:"𥾆";s:4:"𥾆";s:3:"絣";s:4:"絣";s:3:"䌁";s:4:"䌁";s:3:"緇";s:4:"緇";s:3:"縂";s:4:"縂";s:3:"繅";s:4:"繅";s:3:"䌴";s:4:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:3:"䍙";s:4:"䍙";s:4:"𦋙";s:4:"𦋙";s:3:"罺";s:4:"罺";s:4:"𦌾";s:4:"𦌾";s:3:"羕";s:4:"羕";s:3:"翺";s:4:"翺";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:3:"聠";s:4:"聠";s:4:"𦖨";s:4:"𦖨";s:3:"聰";s:4:"聰";s:4:"𣍟";s:4:"𣍟";s:3:"䏕";s:4:"䏕";s:3:"育";s:4:"育";s:3:"脃";s:4:"脃";s:3:"䐋";s:4:"䐋";s:3:"脾";s:4:"脾";s:3:"媵";s:4:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:3:"舄";s:4:"舄";s:3:"辞";s:4:"辞";s:3:"䑫";s:4:"䑫";s:3:"芑";s:4:"芑";s:3:"芋";s:4:"芋";s:3:"芝";s:4:"芝";s:3:"劳";s:4:"劳";s:3:"花";s:4:"花";s:3:"芳";s:4:"芳";s:3:"芽";s:4:"芽";s:3:"苦";s:4:"苦";s:4:"𦬼";s:4:"𦬼";s:3:"茝";s:4:"茝";s:3:"荣";s:4:"荣";s:3:"莭";s:4:"莭";s:3:"茣";s:4:"茣";s:3:"莽";s:4:"莽";s:3:"菧";s:4:"菧";s:3:"荓";s:4:"荓";s:3:"菊";s:4:"菊";s:3:"菌";s:4:"菌";s:3:"菜";s:4:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:3:"䔫";s:4:"䔫";s:3:"蓱";s:4:"蓱";s:3:"蓳";s:4:"蓳";s:3:"蔖";s:4:"蔖";s:4:"𧏊";s:4:"𧏊";s:3:"蕤";s:4:"蕤";s:4:"𦼬";s:4:"𦼬";s:3:"䕝";s:4:"䕝";s:3:"䕡";s:4:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:3:"䕫";s:4:"䕫";s:3:"虐";s:4:"虐";s:3:"虧";s:4:"虧";s:3:"虩";s:4:"虩";s:3:"蚩";s:4:"蚩";s:3:"蚈";s:4:"蚈";s:3:"蜎";s:4:"蜎";s:3:"蛢";s:4:"蛢";s:3:"蜨";s:4:"蜨";s:3:"蝫";s:4:"蝫";s:3:"螆";s:4:"螆";s:3:"䗗";s:4:"䗗";s:3:"蟡";s:4:"蟡";s:3:"蠁";s:4:"蠁";s:3:"䗹";s:4:"䗹";s:3:"衠";s:4:"衠";s:3:"衣";s:4:"衣";s:4:"𧙧";s:4:"𧙧";s:3:"裗";s:4:"裗";s:3:"裞";s:4:"裞";s:3:"䘵";s:4:"䘵";s:3:"裺";s:4:"裺";s:3:"㒻";s:4:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:3:"䚾";s:4:"䚾";s:3:"䛇";s:4:"䛇";s:3:"誠";s:4:"誠";s:3:"豕";s:4:"豕";s:4:"𧲨";s:4:"𧲨";s:3:"貫";s:4:"貫";s:3:"賁";s:4:"賁";s:3:"贛";s:4:"贛";s:3:"起";s:4:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:3:"跋";s:4:"跋";s:3:"趼";s:4:"趼";s:3:"跰";s:4:"跰";s:4:"𠣞";s:4:"𠣞";s:3:"軔";s:4:"軔";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:3:"邔";s:4:"邔";s:3:"郱";s:4:"郱";s:3:"鄑";s:4:"鄑";s:4:"𨜮";s:4:"𨜮";s:3:"鄛";s:4:"鄛";s:3:"鈸";s:4:"鈸";s:3:"鋗";s:4:"鋗";s:3:"鋘";s:4:"鋘";s:3:"鉼";s:4:"鉼";s:3:"鏹";s:4:"鏹";s:3:"鐕";s:4:"鐕";s:4:"𨯺";s:4:"𨯺";s:3:"開";s:4:"開";s:3:"䦕";s:4:"䦕";s:3:"閷";s:4:"閷";s:4:"𨵷";s:4:"𨵷";s:3:"䧦";s:4:"䧦";s:3:"雃";s:4:"雃";s:3:"嶲";s:4:"嶲";s:3:"霣";s:4:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:3:"䩮";s:4:"䩮";s:3:"䩶";s:4:"䩶";s:3:"韠";s:4:"韠";s:4:"𩐊";s:4:"𩐊";s:3:"䪲";s:4:"䪲";s:4:"𩒖";s:4:"𩒖";s:3:"頩";s:4:"頩";s:4:"𩖶";s:4:"𩖶";s:3:"飢";s:4:"飢";s:3:"䬳";s:4:"䬳";s:3:"餩";s:4:"餩";s:3:"馧";s:4:"馧";s:3:"駂";s:4:"駂";s:3:"駾";s:4:"駾";s:3:"䯎";s:4:"䯎";s:4:"𩬰";s:4:"𩬰";s:3:"鱀";s:4:"鱀";s:3:"鳽";s:4:"鳽";s:3:"䳎";s:4:"䳎";s:3:"䳭";s:4:"䳭";s:3:"鵧";s:4:"鵧";s:4:"𪃎";s:4:"𪃎";s:3:"䳸";s:4:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:3:"麻";s:4:"麻";s:3:"䵖";s:4:"䵖";s:3:"黹";s:4:"黹";s:3:"黾";s:4:"黾";s:3:"鼅";s:4:"鼅";s:3:"鼏";s:4:"鼏";s:3:"鼖";s:4:"鼖";s:3:"鼻";s:4:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
-$utfCanonicalDecomp = unserialize( 'a:2032:{s:2:"À";s:3:"À";s:2:"Á";s:3:"Á";s:2:"Â";s:3:"Â";s:2:"Ã";s:3:"Ã";s:2:"Ä";s:3:"Ä";s:2:"Å";s:3:"Å";s:2:"Ç";s:3:"Ç";s:2:"È";s:3:"È";s:2:"É";s:3:"É";s:2:"Ê";s:3:"Ê";s:2:"Ë";s:3:"Ë";s:2:"Ì";s:3:"Ì";s:2:"Í";s:3:"Í";s:2:"Î";s:3:"Î";s:2:"Ï";s:3:"Ï";s:2:"Ñ";s:3:"Ñ";s:2:"Ò";s:3:"Ò";s:2:"Ó";s:3:"Ó";s:2:"Ô";s:3:"Ô";s:2:"Õ";s:3:"Õ";s:2:"Ö";s:3:"Ö";s:2:"Ù";s:3:"Ù";s:2:"Ú";s:3:"Ú";s:2:"Û";s:3:"Û";s:2:"Ü";s:3:"Ü";s:2:"Ý";s:3:"Ý";s:2:"à";s:3:"à";s:2:"á";s:3:"á";s:2:"â";s:3:"â";s:2:"ã";s:3:"ã";s:2:"ä";s:3:"ä";s:2:"å";s:3:"å";s:2:"ç";s:3:"ç";s:2:"è";s:3:"è";s:2:"é";s:3:"é";s:2:"ê";s:3:"ê";s:2:"ë";s:3:"ë";s:2:"ì";s:3:"ì";s:2:"í";s:3:"í";s:2:"î";s:3:"î";s:2:"ï";s:3:"ï";s:2:"ñ";s:3:"ñ";s:2:"ò";s:3:"ò";s:2:"ó";s:3:"ó";s:2:"ô";s:3:"ô";s:2:"õ";s:3:"õ";s:2:"ö";s:3:"ö";s:2:"ù";s:3:"ù";s:2:"ú";s:3:"ú";s:2:"û";s:3:"û";s:2:"ü";s:3:"ü";s:2:"ý";s:3:"ý";s:2:"ÿ";s:3:"ÿ";s:2:"Ā";s:3:"Ā";s:2:"ā";s:3:"ā";s:2:"Ă";s:3:"Ă";s:2:"ă";s:3:"ă";s:2:"Ą";s:3:"Ą";s:2:"ą";s:3:"ą";s:2:"Ć";s:3:"Ć";s:2:"ć";s:3:"ć";s:2:"Ĉ";s:3:"Ĉ";s:2:"ĉ";s:3:"ĉ";s:2:"Ċ";s:3:"Ċ";s:2:"ċ";s:3:"ċ";s:2:"Č";s:3:"Č";s:2:"č";s:3:"č";s:2:"Ď";s:3:"Ď";s:2:"ď";s:3:"ď";s:2:"Ē";s:3:"Ē";s:2:"ē";s:3:"ē";s:2:"Ĕ";s:3:"Ĕ";s:2:"ĕ";s:3:"ĕ";s:2:"Ė";s:3:"Ė";s:2:"ė";s:3:"ė";s:2:"Ę";s:3:"Ę";s:2:"ę";s:3:"ę";s:2:"Ě";s:3:"Ě";s:2:"ě";s:3:"ě";s:2:"Ĝ";s:3:"Ĝ";s:2:"ĝ";s:3:"ĝ";s:2:"Ğ";s:3:"Ğ";s:2:"ğ";s:3:"ğ";s:2:"Ġ";s:3:"Ġ";s:2:"ġ";s:3:"ġ";s:2:"Ģ";s:3:"Ģ";s:2:"ģ";s:3:"ģ";s:2:"Ĥ";s:3:"Ĥ";s:2:"ĥ";s:3:"ĥ";s:2:"Ĩ";s:3:"Ĩ";s:2:"ĩ";s:3:"ĩ";s:2:"Ī";s:3:"Ī";s:2:"ī";s:3:"ī";s:2:"Ĭ";s:3:"Ĭ";s:2:"ĭ";s:3:"ĭ";s:2:"Į";s:3:"Į";s:2:"į";s:3:"į";s:2:"İ";s:3:"İ";s:2:"Ĵ";s:3:"Ĵ";s:2:"ĵ";s:3:"ĵ";s:2:"Ķ";s:3:"Ķ";s:2:"ķ";s:3:"ķ";s:2:"Ĺ";s:3:"Ĺ";s:2:"ĺ";s:3:"ĺ";s:2:"Ļ";s:3:"Ļ";s:2:"ļ";s:3:"ļ";s:2:"Ľ";s:3:"Ľ";s:2:"ľ";s:3:"ľ";s:2:"Ń";s:3:"Ń";s:2:"ń";s:3:"ń";s:2:"Ņ";s:3:"Ņ";s:2:"ņ";s:3:"ņ";s:2:"Ň";s:3:"Ň";s:2:"ň";s:3:"ň";s:2:"Ō";s:3:"Ō";s:2:"ō";s:3:"ō";s:2:"Ŏ";s:3:"Ŏ";s:2:"ŏ";s:3:"ŏ";s:2:"Ő";s:3:"Ő";s:2:"ő";s:3:"ő";s:2:"Ŕ";s:3:"Ŕ";s:2:"ŕ";s:3:"ŕ";s:2:"Ŗ";s:3:"Ŗ";s:2:"ŗ";s:3:"ŗ";s:2:"Ř";s:3:"Ř";s:2:"ř";s:3:"ř";s:2:"Ś";s:3:"Ś";s:2:"ś";s:3:"ś";s:2:"Ŝ";s:3:"Ŝ";s:2:"ŝ";s:3:"ŝ";s:2:"Ş";s:3:"Ş";s:2:"ş";s:3:"ş";s:2:"Š";s:3:"Š";s:2:"š";s:3:"š";s:2:"Ţ";s:3:"Ţ";s:2:"ţ";s:3:"ţ";s:2:"Ť";s:3:"Ť";s:2:"ť";s:3:"ť";s:2:"Ũ";s:3:"Ũ";s:2:"ũ";s:3:"ũ";s:2:"Ū";s:3:"Ū";s:2:"ū";s:3:"ū";s:2:"Ŭ";s:3:"Ŭ";s:2:"ŭ";s:3:"ŭ";s:2:"Ů";s:3:"Ů";s:2:"ů";s:3:"ů";s:2:"Ű";s:3:"Ű";s:2:"ű";s:3:"ű";s:2:"Ų";s:3:"Ų";s:2:"ų";s:3:"ų";s:2:"Ŵ";s:3:"Ŵ";s:2:"ŵ";s:3:"ŵ";s:2:"Ŷ";s:3:"Ŷ";s:2:"ŷ";s:3:"ŷ";s:2:"Ÿ";s:3:"Ÿ";s:2:"Ź";s:3:"Ź";s:2:"ź";s:3:"ź";s:2:"Ż";s:3:"Ż";s:2:"ż";s:3:"ż";s:2:"Ž";s:3:"Ž";s:2:"ž";s:3:"ž";s:2:"Ơ";s:3:"Ơ";s:2:"ơ";s:3:"ơ";s:2:"Ư";s:3:"Ư";s:2:"ư";s:3:"ư";s:2:"Ǎ";s:3:"Ǎ";s:2:"ǎ";s:3:"ǎ";s:2:"Ǐ";s:3:"Ǐ";s:2:"ǐ";s:3:"ǐ";s:2:"Ǒ";s:3:"Ǒ";s:2:"ǒ";s:3:"ǒ";s:2:"Ǔ";s:3:"Ǔ";s:2:"ǔ";s:3:"ǔ";s:2:"Ǖ";s:5:"Ǖ";s:2:"ǖ";s:5:"ǖ";s:2:"Ǘ";s:5:"Ǘ";s:2:"ǘ";s:5:"ǘ";s:2:"Ǚ";s:5:"Ǚ";s:2:"ǚ";s:5:"ǚ";s:2:"Ǜ";s:5:"Ǜ";s:2:"ǜ";s:5:"ǜ";s:2:"Ǟ";s:5:"Ǟ";s:2:"ǟ";s:5:"ǟ";s:2:"Ǡ";s:5:"Ǡ";s:2:"ǡ";s:5:"ǡ";s:2:"Ǣ";s:4:"Ǣ";s:2:"ǣ";s:4:"ǣ";s:2:"Ǧ";s:3:"Ǧ";s:2:"ǧ";s:3:"ǧ";s:2:"Ǩ";s:3:"Ǩ";s:2:"ǩ";s:3:"ǩ";s:2:"Ǫ";s:3:"Ǫ";s:2:"ǫ";s:3:"ǫ";s:2:"Ǭ";s:5:"Ǭ";s:2:"ǭ";s:5:"ǭ";s:2:"Ǯ";s:4:"Ǯ";s:2:"ǯ";s:4:"ǯ";s:2:"ǰ";s:3:"ǰ";s:2:"Ǵ";s:3:"Ǵ";s:2:"ǵ";s:3:"ǵ";s:2:"Ǹ";s:3:"Ǹ";s:2:"ǹ";s:3:"ǹ";s:2:"Ǻ";s:5:"Ǻ";s:2:"ǻ";s:5:"ǻ";s:2:"Ǽ";s:4:"Ǽ";s:2:"ǽ";s:4:"ǽ";s:2:"Ǿ";s:4:"Ǿ";s:2:"ǿ";s:4:"ǿ";s:2:"Ȁ";s:3:"Ȁ";s:2:"ȁ";s:3:"ȁ";s:2:"Ȃ";s:3:"Ȃ";s:2:"ȃ";s:3:"ȃ";s:2:"Ȅ";s:3:"Ȅ";s:2:"ȅ";s:3:"ȅ";s:2:"Ȇ";s:3:"Ȇ";s:2:"ȇ";s:3:"ȇ";s:2:"Ȉ";s:3:"Ȉ";s:2:"ȉ";s:3:"ȉ";s:2:"Ȋ";s:3:"Ȋ";s:2:"ȋ";s:3:"ȋ";s:2:"Ȍ";s:3:"Ȍ";s:2:"ȍ";s:3:"ȍ";s:2:"Ȏ";s:3:"Ȏ";s:2:"ȏ";s:3:"ȏ";s:2:"Ȑ";s:3:"Ȑ";s:2:"ȑ";s:3:"ȑ";s:2:"Ȓ";s:3:"Ȓ";s:2:"ȓ";s:3:"ȓ";s:2:"Ȕ";s:3:"Ȕ";s:2:"ȕ";s:3:"ȕ";s:2:"Ȗ";s:3:"Ȗ";s:2:"ȗ";s:3:"ȗ";s:2:"Ș";s:3:"Ș";s:2:"ș";s:3:"ș";s:2:"Ț";s:3:"Ț";s:2:"ț";s:3:"ț";s:2:"Ȟ";s:3:"Ȟ";s:2:"ȟ";s:3:"ȟ";s:2:"Ȧ";s:3:"Ȧ";s:2:"ȧ";s:3:"ȧ";s:2:"Ȩ";s:3:"Ȩ";s:2:"ȩ";s:3:"ȩ";s:2:"Ȫ";s:5:"Ȫ";s:2:"ȫ";s:5:"ȫ";s:2:"Ȭ";s:5:"Ȭ";s:2:"ȭ";s:5:"ȭ";s:2:"Ȯ";s:3:"Ȯ";s:2:"ȯ";s:3:"ȯ";s:2:"Ȱ";s:5:"Ȱ";s:2:"ȱ";s:5:"ȱ";s:2:"Ȳ";s:3:"Ȳ";s:2:"ȳ";s:3:"ȳ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:2:"̈́";s:4:"̈́";s:2:"ʹ";s:2:"ʹ";s:2:";";s:1:";";s:2:"΅";s:4:"΅";s:2:"Ά";s:4:"Ά";s:2:"·";s:2:"·";s:2:"Έ";s:4:"Έ";s:2:"Ή";s:4:"Ή";s:2:"Ί";s:4:"Ί";s:2:"Ό";s:4:"Ό";s:2:"Ύ";s:4:"Ύ";s:2:"Ώ";s:4:"Ώ";s:2:"ΐ";s:6:"ΐ";s:2:"Ϊ";s:4:"Ϊ";s:2:"Ϋ";s:4:"Ϋ";s:2:"ά";s:4:"ά";s:2:"έ";s:4:"έ";s:2:"ή";s:4:"ή";s:2:"ί";s:4:"ί";s:2:"ΰ";s:6:"ΰ";s:2:"ϊ";s:4:"ϊ";s:2:"ϋ";s:4:"ϋ";s:2:"ό";s:4:"ό";s:2:"ύ";s:4:"ύ";s:2:"ώ";s:4:"ώ";s:2:"ϓ";s:4:"ϓ";s:2:"ϔ";s:4:"ϔ";s:2:"Ѐ";s:4:"Ѐ";s:2:"Ё";s:4:"Ё";s:2:"Ѓ";s:4:"Ѓ";s:2:"Ї";s:4:"Ї";s:2:"Ќ";s:4:"Ќ";s:2:"Ѝ";s:4:"Ѝ";s:2:"Ў";s:4:"Ў";s:2:"Й";s:4:"Й";s:2:"й";s:4:"й";s:2:"ѐ";s:4:"ѐ";s:2:"ё";s:4:"ё";s:2:"ѓ";s:4:"ѓ";s:2:"ї";s:4:"ї";s:2:"ќ";s:4:"ќ";s:2:"ѝ";s:4:"ѝ";s:2:"ў";s:4:"ў";s:2:"Ѷ";s:4:"Ѷ";s:2:"ѷ";s:4:"ѷ";s:2:"Ӂ";s:4:"Ӂ";s:2:"ӂ";s:4:"ӂ";s:2:"Ӑ";s:4:"Ӑ";s:2:"ӑ";s:4:"ӑ";s:2:"Ӓ";s:4:"Ӓ";s:2:"ӓ";s:4:"ӓ";s:2:"Ӗ";s:4:"Ӗ";s:2:"ӗ";s:4:"ӗ";s:2:"Ӛ";s:4:"Ӛ";s:2:"ӛ";s:4:"ӛ";s:2:"Ӝ";s:4:"Ӝ";s:2:"ӝ";s:4:"ӝ";s:2:"Ӟ";s:4:"Ӟ";s:2:"ӟ";s:4:"ӟ";s:2:"Ӣ";s:4:"Ӣ";s:2:"ӣ";s:4:"ӣ";s:2:"Ӥ";s:4:"Ӥ";s:2:"ӥ";s:4:"ӥ";s:2:"Ӧ";s:4:"Ӧ";s:2:"ӧ";s:4:"ӧ";s:2:"Ӫ";s:4:"Ӫ";s:2:"ӫ";s:4:"ӫ";s:2:"Ӭ";s:4:"Ӭ";s:2:"ӭ";s:4:"ӭ";s:2:"Ӯ";s:4:"Ӯ";s:2:"ӯ";s:4:"ӯ";s:2:"Ӱ";s:4:"Ӱ";s:2:"ӱ";s:4:"ӱ";s:2:"Ӳ";s:4:"Ӳ";s:2:"ӳ";s:4:"ӳ";s:2:"Ӵ";s:4:"Ӵ";s:2:"ӵ";s:4:"ӵ";s:2:"Ӹ";s:4:"Ӹ";s:2:"ӹ";s:4:"ӹ";s:2:"آ";s:4:"آ";s:2:"أ";s:4:"أ";s:2:"ؤ";s:4:"ؤ";s:2:"إ";s:4:"إ";s:2:"ئ";s:4:"ئ";s:2:"ۀ";s:4:"ۀ";s:2:"ۂ";s:4:"ۂ";s:2:"ۓ";s:4:"ۓ";s:3:"ऩ";s:6:"ऩ";s:3:"ऱ";s:6:"ऱ";s:3:"ऴ";s:6:"ऴ";s:3:"क़";s:6:"क़";s:3:"ख़";s:6:"ख़";s:3:"ग़";s:6:"ग़";s:3:"ज़";s:6:"ज़";s:3:"ड़";s:6:"ड़";s:3:"ढ़";s:6:"ढ़";s:3:"फ़";s:6:"फ़";s:3:"य़";s:6:"य़";s:3:"ো";s:6:"ো";s:3:"ৌ";s:6:"ৌ";s:3:"ড়";s:6:"ড়";s:3:"ঢ়";s:6:"ঢ়";s:3:"য়";s:6:"য়";s:3:"ਲ਼";s:6:"ਲ਼";s:3:"ਸ਼";s:6:"ਸ਼";s:3:"ਖ਼";s:6:"ਖ਼";s:3:"ਗ਼";s:6:"ਗ਼";s:3:"ਜ਼";s:6:"ਜ਼";s:3:"ਫ਼";s:6:"ਫ਼";s:3:"ୈ";s:6:"ୈ";s:3:"ୋ";s:6:"ୋ";s:3:"ୌ";s:6:"ୌ";s:3:"ଡ଼";s:6:"ଡ଼";s:3:"ଢ଼";s:6:"ଢ଼";s:3:"ஔ";s:6:"ஔ";s:3:"ொ";s:6:"ொ";s:3:"ோ";s:6:"ோ";s:3:"ௌ";s:6:"ௌ";s:3:"ై";s:6:"ై";s:3:"ೀ";s:6:"ೀ";s:3:"ೇ";s:6:"ೇ";s:3:"ೈ";s:6:"ೈ";s:3:"ೊ";s:6:"ೊ";s:3:"ೋ";s:9:"ೋ";s:3:"ൊ";s:6:"ൊ";s:3:"ോ";s:6:"ോ";s:3:"ൌ";s:6:"ൌ";s:3:"ේ";s:6:"ේ";s:3:"ො";s:6:"ො";s:3:"ෝ";s:9:"ෝ";s:3:"ෞ";s:6:"ෞ";s:3:"གྷ";s:6:"གྷ";s:3:"ཌྷ";s:6:"ཌྷ";s:3:"དྷ";s:6:"དྷ";s:3:"བྷ";s:6:"བྷ";s:3:"ཛྷ";s:6:"ཛྷ";s:3:"ཀྵ";s:6:"ཀྵ";s:3:"ཱི";s:6:"ཱི";s:3:"ཱུ";s:6:"ཱུ";s:3:"ྲྀ";s:6:"ྲྀ";s:3:"ླྀ";s:6:"ླྀ";s:3:"ཱྀ";s:6:"ཱྀ";s:3:"ྒྷ";s:6:"ྒྷ";s:3:"ྜྷ";s:6:"ྜྷ";s:3:"ྡྷ";s:6:"ྡྷ";s:3:"ྦྷ";s:6:"ྦྷ";s:3:"ྫྷ";s:6:"ྫྷ";s:3:"ྐྵ";s:6:"ྐྵ";s:3:"ဦ";s:6:"ဦ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:3:"Ḉ";s:5:"Ḉ";s:3:"ḉ";s:5:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:3:"Ḕ";s:5:"Ḕ";s:3:"ḕ";s:5:"ḕ";s:3:"Ḗ";s:5:"Ḗ";s:3:"ḗ";s:5:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:3:"Ḝ";s:5:"Ḝ";s:3:"ḝ";s:5:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:3:"Ḯ";s:5:"Ḯ";s:3:"ḯ";s:5:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:3:"Ḹ";s:5:"Ḹ";s:3:"ḹ";s:5:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:3:"Ṍ";s:5:"Ṍ";s:3:"ṍ";s:5:"ṍ";s:3:"Ṏ";s:5:"Ṏ";s:3:"ṏ";s:5:"ṏ";s:3:"Ṑ";s:5:"Ṑ";s:3:"ṑ";s:5:"ṑ";s:3:"Ṓ";s:5:"Ṓ";s:3:"ṓ";s:5:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:3:"Ṝ";s:5:"Ṝ";s:3:"ṝ";s:5:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:3:"Ṥ";s:5:"Ṥ";s:3:"ṥ";s:5:"ṥ";s:3:"Ṧ";s:5:"Ṧ";s:3:"ṧ";s:5:"ṧ";s:3:"Ṩ";s:5:"Ṩ";s:3:"ṩ";s:5:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:3:"Ṹ";s:5:"Ṹ";s:3:"ṹ";s:5:"ṹ";s:3:"Ṻ";s:5:"Ṻ";s:3:"ṻ";s:5:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:3:"ẛ";s:4:"ẛ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:3:"Ấ";s:5:"Ấ";s:3:"ấ";s:5:"ấ";s:3:"Ầ";s:5:"Ầ";s:3:"ầ";s:5:"ầ";s:3:"Ẩ";s:5:"Ẩ";s:3:"ẩ";s:5:"ẩ";s:3:"Ẫ";s:5:"Ẫ";s:3:"ẫ";s:5:"ẫ";s:3:"Ậ";s:5:"Ậ";s:3:"ậ";s:5:"ậ";s:3:"Ắ";s:5:"Ắ";s:3:"ắ";s:5:"ắ";s:3:"Ằ";s:5:"Ằ";s:3:"ằ";s:5:"ằ";s:3:"Ẳ";s:5:"Ẳ";s:3:"ẳ";s:5:"ẳ";s:3:"Ẵ";s:5:"Ẵ";s:3:"ẵ";s:5:"ẵ";s:3:"Ặ";s:5:"Ặ";s:3:"ặ";s:5:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:3:"Ế";s:5:"Ế";s:3:"ế";s:5:"ế";s:3:"Ề";s:5:"Ề";s:3:"ề";s:5:"ề";s:3:"Ể";s:5:"Ể";s:3:"ể";s:5:"ể";s:3:"Ễ";s:5:"Ễ";s:3:"ễ";s:5:"ễ";s:3:"Ệ";s:5:"Ệ";s:3:"ệ";s:5:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:3:"Ố";s:5:"Ố";s:3:"ố";s:5:"ố";s:3:"Ồ";s:5:"Ồ";s:3:"ồ";s:5:"ồ";s:3:"Ổ";s:5:"Ổ";s:3:"ổ";s:5:"ổ";s:3:"Ỗ";s:5:"Ỗ";s:3:"ỗ";s:5:"ỗ";s:3:"Ộ";s:5:"Ộ";s:3:"ộ";s:5:"ộ";s:3:"Ớ";s:5:"Ớ";s:3:"ớ";s:5:"ớ";s:3:"Ờ";s:5:"Ờ";s:3:"ờ";s:5:"ờ";s:3:"Ở";s:5:"Ở";s:3:"ở";s:5:"ở";s:3:"Ỡ";s:5:"Ỡ";s:3:"ỡ";s:5:"ỡ";s:3:"Ợ";s:5:"Ợ";s:3:"ợ";s:5:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:3:"Ứ";s:5:"Ứ";s:3:"ứ";s:5:"ứ";s:3:"Ừ";s:5:"Ừ";s:3:"ừ";s:5:"ừ";s:3:"Ử";s:5:"Ử";s:3:"ử";s:5:"ử";s:3:"Ữ";s:5:"Ữ";s:3:"ữ";s:5:"ữ";s:3:"Ự";s:5:"Ự";s:3:"ự";s:5:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:3:"ἀ";s:4:"ἀ";s:3:"ἁ";s:4:"ἁ";s:3:"ἂ";s:6:"ἂ";s:3:"ἃ";s:6:"ἃ";s:3:"ἄ";s:6:"ἄ";s:3:"ἅ";s:6:"ἅ";s:3:"ἆ";s:6:"ἆ";s:3:"ἇ";s:6:"ἇ";s:3:"Ἀ";s:4:"Ἀ";s:3:"Ἁ";s:4:"Ἁ";s:3:"Ἂ";s:6:"Ἂ";s:3:"Ἃ";s:6:"Ἃ";s:3:"Ἄ";s:6:"Ἄ";s:3:"Ἅ";s:6:"Ἅ";s:3:"Ἆ";s:6:"Ἆ";s:3:"Ἇ";s:6:"Ἇ";s:3:"ἐ";s:4:"ἐ";s:3:"ἑ";s:4:"ἑ";s:3:"ἒ";s:6:"ἒ";s:3:"ἓ";s:6:"ἓ";s:3:"ἔ";s:6:"ἔ";s:3:"ἕ";s:6:"ἕ";s:3:"Ἐ";s:4:"Ἐ";s:3:"Ἑ";s:4:"Ἑ";s:3:"Ἒ";s:6:"Ἒ";s:3:"Ἓ";s:6:"Ἓ";s:3:"Ἔ";s:6:"Ἔ";s:3:"Ἕ";s:6:"Ἕ";s:3:"ἠ";s:4:"ἠ";s:3:"ἡ";s:4:"ἡ";s:3:"ἢ";s:6:"ἢ";s:3:"ἣ";s:6:"ἣ";s:3:"ἤ";s:6:"ἤ";s:3:"ἥ";s:6:"ἥ";s:3:"ἦ";s:6:"ἦ";s:3:"ἧ";s:6:"ἧ";s:3:"Ἠ";s:4:"Ἠ";s:3:"Ἡ";s:4:"Ἡ";s:3:"Ἢ";s:6:"Ἢ";s:3:"Ἣ";s:6:"Ἣ";s:3:"Ἤ";s:6:"Ἤ";s:3:"Ἥ";s:6:"Ἥ";s:3:"Ἦ";s:6:"Ἦ";s:3:"Ἧ";s:6:"Ἧ";s:3:"ἰ";s:4:"ἰ";s:3:"ἱ";s:4:"ἱ";s:3:"ἲ";s:6:"ἲ";s:3:"ἳ";s:6:"ἳ";s:3:"ἴ";s:6:"ἴ";s:3:"ἵ";s:6:"ἵ";s:3:"ἶ";s:6:"ἶ";s:3:"ἷ";s:6:"ἷ";s:3:"Ἰ";s:4:"Ἰ";s:3:"Ἱ";s:4:"Ἱ";s:3:"Ἲ";s:6:"Ἲ";s:3:"Ἳ";s:6:"Ἳ";s:3:"Ἴ";s:6:"Ἴ";s:3:"Ἵ";s:6:"Ἵ";s:3:"Ἶ";s:6:"Ἶ";s:3:"Ἷ";s:6:"Ἷ";s:3:"ὀ";s:4:"ὀ";s:3:"ὁ";s:4:"ὁ";s:3:"ὂ";s:6:"ὂ";s:3:"ὃ";s:6:"ὃ";s:3:"ὄ";s:6:"ὄ";s:3:"ὅ";s:6:"ὅ";s:3:"Ὀ";s:4:"Ὀ";s:3:"Ὁ";s:4:"Ὁ";s:3:"Ὂ";s:6:"Ὂ";s:3:"Ὃ";s:6:"Ὃ";s:3:"Ὄ";s:6:"Ὄ";s:3:"Ὅ";s:6:"Ὅ";s:3:"ὐ";s:4:"ὐ";s:3:"ὑ";s:4:"ὑ";s:3:"ὒ";s:6:"ὒ";s:3:"ὓ";s:6:"ὓ";s:3:"ὔ";s:6:"ὔ";s:3:"ὕ";s:6:"ὕ";s:3:"ὖ";s:6:"ὖ";s:3:"ὗ";s:6:"ὗ";s:3:"Ὑ";s:4:"Ὑ";s:3:"Ὓ";s:6:"Ὓ";s:3:"Ὕ";s:6:"Ὕ";s:3:"Ὗ";s:6:"Ὗ";s:3:"ὠ";s:4:"ὠ";s:3:"ὡ";s:4:"ὡ";s:3:"ὢ";s:6:"ὢ";s:3:"ὣ";s:6:"ὣ";s:3:"ὤ";s:6:"ὤ";s:3:"ὥ";s:6:"ὥ";s:3:"ὦ";s:6:"ὦ";s:3:"ὧ";s:6:"ὧ";s:3:"Ὠ";s:4:"Ὠ";s:3:"Ὡ";s:4:"Ὡ";s:3:"Ὢ";s:6:"Ὢ";s:3:"Ὣ";s:6:"Ὣ";s:3:"Ὤ";s:6:"Ὤ";s:3:"Ὥ";s:6:"Ὥ";s:3:"Ὦ";s:6:"Ὦ";s:3:"Ὧ";s:6:"Ὧ";s:3:"ὰ";s:4:"ὰ";s:3:"ά";s:4:"ά";s:3:"ὲ";s:4:"ὲ";s:3:"έ";s:4:"έ";s:3:"ὴ";s:4:"ὴ";s:3:"ή";s:4:"ή";s:3:"ὶ";s:4:"ὶ";s:3:"ί";s:4:"ί";s:3:"ὸ";s:4:"ὸ";s:3:"ό";s:4:"ό";s:3:"ὺ";s:4:"ὺ";s:3:"ύ";s:4:"ύ";s:3:"ὼ";s:4:"ὼ";s:3:"ώ";s:4:"ώ";s:3:"ᾀ";s:6:"ᾀ";s:3:"ᾁ";s:6:"ᾁ";s:3:"ᾂ";s:8:"ᾂ";s:3:"ᾃ";s:8:"ᾃ";s:3:"ᾄ";s:8:"ᾄ";s:3:"ᾅ";s:8:"ᾅ";s:3:"ᾆ";s:8:"ᾆ";s:3:"ᾇ";s:8:"ᾇ";s:3:"ᾈ";s:6:"ᾈ";s:3:"ᾉ";s:6:"ᾉ";s:3:"ᾊ";s:8:"ᾊ";s:3:"ᾋ";s:8:"ᾋ";s:3:"ᾌ";s:8:"ᾌ";s:3:"ᾍ";s:8:"ᾍ";s:3:"ᾎ";s:8:"ᾎ";s:3:"ᾏ";s:8:"ᾏ";s:3:"ᾐ";s:6:"ᾐ";s:3:"ᾑ";s:6:"ᾑ";s:3:"ᾒ";s:8:"ᾒ";s:3:"ᾓ";s:8:"ᾓ";s:3:"ᾔ";s:8:"ᾔ";s:3:"ᾕ";s:8:"ᾕ";s:3:"ᾖ";s:8:"ᾖ";s:3:"ᾗ";s:8:"ᾗ";s:3:"ᾘ";s:6:"ᾘ";s:3:"ᾙ";s:6:"ᾙ";s:3:"ᾚ";s:8:"ᾚ";s:3:"ᾛ";s:8:"ᾛ";s:3:"ᾜ";s:8:"ᾜ";s:3:"ᾝ";s:8:"ᾝ";s:3:"ᾞ";s:8:"ᾞ";s:3:"ᾟ";s:8:"ᾟ";s:3:"ᾠ";s:6:"ᾠ";s:3:"ᾡ";s:6:"ᾡ";s:3:"ᾢ";s:8:"ᾢ";s:3:"ᾣ";s:8:"ᾣ";s:3:"ᾤ";s:8:"ᾤ";s:3:"ᾥ";s:8:"ᾥ";s:3:"ᾦ";s:8:"ᾦ";s:3:"ᾧ";s:8:"ᾧ";s:3:"ᾨ";s:6:"ᾨ";s:3:"ᾩ";s:6:"ᾩ";s:3:"ᾪ";s:8:"ᾪ";s:3:"ᾫ";s:8:"ᾫ";s:3:"ᾬ";s:8:"ᾬ";s:3:"ᾭ";s:8:"ᾭ";s:3:"ᾮ";s:8:"ᾮ";s:3:"ᾯ";s:8:"ᾯ";s:3:"ᾰ";s:4:"ᾰ";s:3:"ᾱ";s:4:"ᾱ";s:3:"ᾲ";s:6:"ᾲ";s:3:"ᾳ";s:4:"ᾳ";s:3:"ᾴ";s:6:"ᾴ";s:3:"ᾶ";s:4:"ᾶ";s:3:"ᾷ";s:6:"ᾷ";s:3:"Ᾰ";s:4:"Ᾰ";s:3:"Ᾱ";s:4:"Ᾱ";s:3:"Ὰ";s:4:"Ὰ";s:3:"Ά";s:4:"Ά";s:3:"ᾼ";s:4:"ᾼ";s:3:"ι";s:2:"ι";s:3:"῁";s:4:"῁";s:3:"ῂ";s:6:"ῂ";s:3:"ῃ";s:4:"ῃ";s:3:"ῄ";s:6:"ῄ";s:3:"ῆ";s:4:"ῆ";s:3:"ῇ";s:6:"ῇ";s:3:"Ὲ";s:4:"Ὲ";s:3:"Έ";s:4:"Έ";s:3:"Ὴ";s:4:"Ὴ";s:3:"Ή";s:4:"Ή";s:3:"ῌ";s:4:"ῌ";s:3:"῍";s:5:"῍";s:3:"῎";s:5:"῎";s:3:"῏";s:5:"῏";s:3:"ῐ";s:4:"ῐ";s:3:"ῑ";s:4:"ῑ";s:3:"ῒ";s:6:"ῒ";s:3:"ΐ";s:6:"ΐ";s:3:"ῖ";s:4:"ῖ";s:3:"ῗ";s:6:"ῗ";s:3:"Ῐ";s:4:"Ῐ";s:3:"Ῑ";s:4:"Ῑ";s:3:"Ὶ";s:4:"Ὶ";s:3:"Ί";s:4:"Ί";s:3:"῝";s:5:"῝";s:3:"῞";s:5:"῞";s:3:"῟";s:5:"῟";s:3:"ῠ";s:4:"ῠ";s:3:"ῡ";s:4:"ῡ";s:3:"ῢ";s:6:"ῢ";s:3:"ΰ";s:6:"ΰ";s:3:"ῤ";s:4:"ῤ";s:3:"ῥ";s:4:"ῥ";s:3:"ῦ";s:4:"ῦ";s:3:"ῧ";s:6:"ῧ";s:3:"Ῠ";s:4:"Ῠ";s:3:"Ῡ";s:4:"Ῡ";s:3:"Ὺ";s:4:"Ὺ";s:3:"Ύ";s:4:"Ύ";s:3:"Ῥ";s:4:"Ῥ";s:3:"῭";s:4:"῭";s:3:"΅";s:4:"΅";s:3:"`";s:1:"`";s:3:"ῲ";s:6:"ῲ";s:3:"ῳ";s:4:"ῳ";s:3:"ῴ";s:6:"ῴ";s:3:"ῶ";s:4:"ῶ";s:3:"ῷ";s:6:"ῷ";s:3:"Ὸ";s:4:"Ὸ";s:3:"Ό";s:4:"Ό";s:3:"Ὼ";s:4:"Ὼ";s:3:"Ώ";s:4:"Ώ";s:3:"ῼ";s:4:"ῼ";s:3:"´";s:2:"´";s:3:" ";s:3:" ";s:3:" ";s:3:" ";s:3:"Ω";s:2:"Ω";s:3:"K";s:1:"K";s:3:"Å";s:3:"Å";s:3:"↚";s:5:"↚";s:3:"↛";s:5:"↛";s:3:"↮";s:5:"↮";s:3:"⇍";s:5:"⇍";s:3:"⇎";s:5:"⇎";s:3:"⇏";s:5:"⇏";s:3:"∄";s:5:"∄";s:3:"∉";s:5:"∉";s:3:"∌";s:5:"∌";s:3:"∤";s:5:"∤";s:3:"∦";s:5:"∦";s:3:"≁";s:5:"≁";s:3:"≄";s:5:"≄";s:3:"≇";s:5:"≇";s:3:"≉";s:5:"≉";s:3:"≠";s:3:"≠";s:3:"≢";s:5:"≢";s:3:"≭";s:5:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:3:"≰";s:5:"≰";s:3:"≱";s:5:"≱";s:3:"≴";s:5:"≴";s:3:"≵";s:5:"≵";s:3:"≸";s:5:"≸";s:3:"≹";s:5:"≹";s:3:"⊀";s:5:"⊀";s:3:"⊁";s:5:"⊁";s:3:"⊄";s:5:"⊄";s:3:"⊅";s:5:"⊅";s:3:"⊈";s:5:"⊈";s:3:"⊉";s:5:"⊉";s:3:"⊬";s:5:"⊬";s:3:"⊭";s:5:"⊭";s:3:"⊮";s:5:"⊮";s:3:"⊯";s:5:"⊯";s:3:"⋠";s:5:"⋠";s:3:"⋡";s:5:"⋡";s:3:"⋢";s:5:"⋢";s:3:"⋣";s:5:"⋣";s:3:"⋪";s:5:"⋪";s:3:"⋫";s:5:"⋫";s:3:"⋬";s:5:"⋬";s:3:"⋭";s:5:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:3:"⫝̸";s:5:"⫝̸";s:3:"が";s:6:"が";s:3:"ぎ";s:6:"ぎ";s:3:"ぐ";s:6:"ぐ";s:3:"げ";s:6:"げ";s:3:"ご";s:6:"ご";s:3:"ざ";s:6:"ざ";s:3:"じ";s:6:"じ";s:3:"ず";s:6:"ず";s:3:"ぜ";s:6:"ぜ";s:3:"ぞ";s:6:"ぞ";s:3:"だ";s:6:"だ";s:3:"ぢ";s:6:"ぢ";s:3:"づ";s:6:"づ";s:3:"で";s:6:"で";s:3:"ど";s:6:"ど";s:3:"ば";s:6:"ば";s:3:"ぱ";s:6:"ぱ";s:3:"び";s:6:"び";s:3:"ぴ";s:6:"ぴ";s:3:"ぶ";s:6:"ぶ";s:3:"ぷ";s:6:"ぷ";s:3:"べ";s:6:"べ";s:3:"ぺ";s:6:"ぺ";s:3:"ぼ";s:6:"ぼ";s:3:"ぽ";s:6:"ぽ";s:3:"ゔ";s:6:"ゔ";s:3:"ゞ";s:6:"ゞ";s:3:"ガ";s:6:"ガ";s:3:"ギ";s:6:"ギ";s:3:"グ";s:6:"グ";s:3:"ゲ";s:6:"ゲ";s:3:"ゴ";s:6:"ゴ";s:3:"ザ";s:6:"ザ";s:3:"ジ";s:6:"ジ";s:3:"ズ";s:6:"ズ";s:3:"ゼ";s:6:"ゼ";s:3:"ゾ";s:6:"ゾ";s:3:"ダ";s:6:"ダ";s:3:"ヂ";s:6:"ヂ";s:3:"ヅ";s:6:"ヅ";s:3:"デ";s:6:"デ";s:3:"ド";s:6:"ド";s:3:"バ";s:6:"バ";s:3:"パ";s:6:"パ";s:3:"ビ";s:6:"ビ";s:3:"ピ";s:6:"ピ";s:3:"ブ";s:6:"ブ";s:3:"プ";s:6:"プ";s:3:"ベ";s:6:"ベ";s:3:"ペ";s:6:"ペ";s:3:"ボ";s:6:"ボ";s:3:"ポ";s:6:"ポ";s:3:"ヴ";s:6:"ヴ";s:3:"ヷ";s:6:"ヷ";s:3:"ヸ";s:6:"ヸ";s:3:"ヹ";s:6:"ヹ";s:3:"ヺ";s:6:"ヺ";s:3:"ヾ";s:6:"ヾ";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:3:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:3:"廊";s:3:"朗";s:3:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:3:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:3:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"樂";s:3:"樂";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:3:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:3:"異";s:3:"北";s:3:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:3:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:3:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"說";s:3:"說";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"寧";s:3:"寧";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"樂";s:3:"樂";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:3:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"率";s:3:"率";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:3:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:3:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:3:"侮";s:3:"僧";s:3:"僧";s:3:"免";s:3:"免";s:3:"勉";s:3:"勉";s:3:"勤";s:3:"勤";s:3:"卑";s:3:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:3:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:3:"屮";s:3:"悔";s:3:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:3:"憎";s:3:"懲";s:3:"懲";s:3:"敏";s:3:"敏";s:3:"既";s:3:"既";s:3:"暑";s:3:"暑";s:3:"梅";s:3:"梅";s:3:"海";s:3:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:3:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:3:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"練";s:3:"練";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:3:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"著";s:3:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"逸";s:3:"逸";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:3:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:3:"勇";s:3:"勺";s:3:"勺";s:3:"喝";s:3:"喝";s:3:"啕";s:3:"啕";s:3:"喙";s:3:"喙";s:3:"嗢";s:3:"嗢";s:3:"塚";s:3:"塚";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:3:"慎";s:3:"愈";s:3:"愈";s:3:"憎";s:3:"憎";s:3:"慠";s:3:"慠";s:3:"懲";s:3:"懲";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"晴";s:3:"晴";s:3:"朗";s:3:"朗";s:3:"望";s:3:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"殺";s:3:"殺";s:3:"流";s:3:"流";s:3:"滛";s:3:"滛";s:3:"滋";s:3:"滋";s:3:"漢";s:3:"漢";s:3:"瀞";s:3:"瀞";s:3:"煮";s:3:"煮";s:3:"瞧";s:3:"瞧";s:3:"爵";s:3:"爵";s:3:"犯";s:3:"犯";s:3:"猪";s:3:"猪";s:3:"瑱";s:3:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"益";s:3:"益";s:3:"盛";s:3:"盛";s:3:"直";s:3:"直";s:3:"睊";s:3:"睊";s:3:"着";s:3:"着";s:3:"磌";s:3:"磌";s:3:"窱";s:3:"窱";s:3:"節";s:3:"節";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"練";s:3:"練";s:3:"缾";s:3:"缾";s:3:"者";s:3:"者";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:3:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"視";s:3:"視";s:3:"調";s:3:"調";s:3:"諸";s:3:"諸";s:3:"請";s:3:"請";s:3:"謁";s:3:"謁";s:3:"諾";s:3:"諾";s:3:"諭";s:3:"諭";s:3:"謹";s:3:"謹";s:3:"變";s:3:"變";s:3:"贈";s:3:"贈";s:3:"輸";s:3:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"難";s:3:"難";s:3:"靖";s:3:"靖";s:3:"韛";s:3:"韛";s:3:"響";s:3:"響";s:3:"頋";s:3:"頋";s:3:"頻";s:3:"頻";s:3:"鬒";s:3:"鬒";s:3:"龜";s:3:"龜";s:3:"𢡊";s:4:"𢡊";s:3:"𢡄";s:4:"𢡄";s:3:"𣏕";s:4:"𣏕";s:3:"㮝";s:3:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:3:"䀹";s:3:"𥉉";s:4:"𥉉";s:3:"𥳐";s:4:"𥳐";s:3:"𧻓";s:4:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"יִ";s:4:"יִ";s:3:"ײַ";s:4:"ײַ";s:3:"שׁ";s:4:"שׁ";s:3:"שׂ";s:4:"שׂ";s:3:"שּׁ";s:6:"שּׁ";s:3:"שּׂ";s:6:"שּׂ";s:3:"אַ";s:4:"אַ";s:3:"אָ";s:4:"אָ";s:3:"אּ";s:4:"אּ";s:3:"בּ";s:4:"בּ";s:3:"גּ";s:4:"גּ";s:3:"דּ";s:4:"דּ";s:3:"הּ";s:4:"הּ";s:3:"וּ";s:4:"וּ";s:3:"זּ";s:4:"זּ";s:3:"טּ";s:4:"טּ";s:3:"יּ";s:4:"יּ";s:3:"ךּ";s:4:"ךּ";s:3:"כּ";s:4:"כּ";s:3:"לּ";s:4:"לּ";s:3:"מּ";s:4:"מּ";s:3:"נּ";s:4:"נּ";s:3:"סּ";s:4:"סּ";s:3:"ףּ";s:4:"ףּ";s:3:"פּ";s:4:"פּ";s:3:"צּ";s:4:"צּ";s:3:"קּ";s:4:"קּ";s:3:"רּ";s:4:"רּ";s:3:"שּ";s:4:"שּ";s:3:"תּ";s:4:"תּ";s:3:"וֹ";s:4:"וֹ";s:3:"בֿ";s:4:"בֿ";s:3:"כֿ";s:4:"כֿ";s:3:"פֿ";s:4:"פֿ";s:4:"𝅗𝅥";s:8:"𝅗𝅥";s:4:"𝅘𝅥";s:8:"𝅘𝅥";s:4:"𝅘𝅥𝅮";s:12:"𝅘𝅥𝅮";s:4:"𝅘𝅥𝅯";s:12:"𝅘𝅥𝅯";s:4:"𝅘𝅥𝅰";s:12:"𝅘𝅥𝅰";s:4:"𝅘𝅥𝅱";s:12:"𝅘𝅥𝅱";s:4:"𝅘𝅥𝅲";s:12:"𝅘𝅥𝅲";s:4:"𝆹𝅥";s:8:"𝆹𝅥";s:4:"𝆺𝅥";s:8:"𝆺𝅥";s:4:"𝆹𝅥𝅮";s:12:"𝆹𝅥𝅮";s:4:"𝆺𝅥𝅮";s:12:"𝆺𝅥𝅮";s:4:"𝆹𝅥𝅯";s:12:"𝆹𝅥𝅯";s:4:"𝆺𝅥𝅯";s:12:"𝆺𝅥𝅯";s:4:"丽";s:3:"丽";s:4:"丸";s:3:"丸";s:4:"乁";s:3:"乁";s:4:"𠄢";s:4:"𠄢";s:4:"你";s:3:"你";s:4:"侮";s:3:"侮";s:4:"侻";s:3:"侻";s:4:"倂";s:3:"倂";s:4:"偺";s:3:"偺";s:4:"備";s:3:"備";s:4:"僧";s:3:"僧";s:4:"像";s:3:"像";s:4:"㒞";s:3:"㒞";s:4:"𠘺";s:4:"𠘺";s:4:"免";s:3:"免";s:4:"兔";s:3:"兔";s:4:"兤";s:3:"兤";s:4:"具";s:3:"具";s:4:"𠔜";s:4:"𠔜";s:4:"㒹";s:3:"㒹";s:4:"內";s:3:"內";s:4:"再";s:3:"再";s:4:"𠕋";s:4:"𠕋";s:4:"冗";s:3:"冗";s:4:"冤";s:3:"冤";s:4:"仌";s:3:"仌";s:4:"冬";s:3:"冬";s:4:"况";s:3:"况";s:4:"𩇟";s:4:"𩇟";s:4:"凵";s:3:"凵";s:4:"刃";s:3:"刃";s:4:"㓟";s:3:"㓟";s:4:"刻";s:3:"刻";s:4:"剆";s:3:"剆";s:4:"割";s:3:"割";s:4:"剷";s:3:"剷";s:4:"㔕";s:3:"㔕";s:4:"勇";s:3:"勇";s:4:"勉";s:3:"勉";s:4:"勤";s:3:"勤";s:4:"勺";s:3:"勺";s:4:"包";s:3:"包";s:4:"匆";s:3:"匆";s:4:"北";s:3:"北";s:4:"卉";s:3:"卉";s:4:"卑";s:3:"卑";s:4:"博";s:3:"博";s:4:"即";s:3:"即";s:4:"卽";s:3:"卽";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"𠨬";s:4:"𠨬";s:4:"灰";s:3:"灰";s:4:"及";s:3:"及";s:4:"叟";s:3:"叟";s:4:"𠭣";s:4:"𠭣";s:4:"叫";s:3:"叫";s:4:"叱";s:3:"叱";s:4:"吆";s:3:"吆";s:4:"咞";s:3:"咞";s:4:"吸";s:3:"吸";s:4:"呈";s:3:"呈";s:4:"周";s:3:"周";s:4:"咢";s:3:"咢";s:4:"哶";s:3:"哶";s:4:"唐";s:3:"唐";s:4:"啓";s:3:"啓";s:4:"啣";s:3:"啣";s:4:"善";s:3:"善";s:4:"善";s:3:"善";s:4:"喙";s:3:"喙";s:4:"喫";s:3:"喫";s:4:"喳";s:3:"喳";s:4:"嗂";s:3:"嗂";s:4:"圖";s:3:"圖";s:4:"嘆";s:3:"嘆";s:4:"圗";s:3:"圗";s:4:"噑";s:3:"噑";s:4:"噴";s:3:"噴";s:4:"切";s:3:"切";s:4:"壮";s:3:"壮";s:4:"城";s:3:"城";s:4:"埴";s:3:"埴";s:4:"堍";s:3:"堍";s:4:"型";s:3:"型";s:4:"堲";s:3:"堲";s:4:"報";s:3:"報";s:4:"墬";s:3:"墬";s:4:"𡓤";s:4:"𡓤";s:4:"売";s:3:"売";s:4:"壷";s:3:"壷";s:4:"夆";s:3:"夆";s:4:"多";s:3:"多";s:4:"夢";s:3:"夢";s:4:"奢";s:3:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:4:"姬";s:3:"姬";s:4:"娛";s:3:"娛";s:4:"娧";s:3:"娧";s:4:"姘";s:3:"姘";s:4:"婦";s:3:"婦";s:4:"㛮";s:3:"㛮";s:4:"㛼";s:3:"㛼";s:4:"嬈";s:3:"嬈";s:4:"嬾";s:3:"嬾";s:4:"嬾";s:3:"嬾";s:4:"𡧈";s:4:"𡧈";s:4:"寃";s:3:"寃";s:4:"寘";s:3:"寘";s:4:"寧";s:3:"寧";s:4:"寳";s:3:"寳";s:4:"𡬘";s:4:"𡬘";s:4:"寿";s:3:"寿";s:4:"将";s:3:"将";s:4:"当";s:3:"当";s:4:"尢";s:3:"尢";s:4:"㞁";s:3:"㞁";s:4:"屠";s:3:"屠";s:4:"屮";s:3:"屮";s:4:"峀";s:3:"峀";s:4:"岍";s:3:"岍";s:4:"𡷤";s:4:"𡷤";s:4:"嵃";s:3:"嵃";s:4:"𡷦";s:4:"𡷦";s:4:"嵮";s:3:"嵮";s:4:"嵫";s:3:"嵫";s:4:"嵼";s:3:"嵼";s:4:"巡";s:3:"巡";s:4:"巢";s:3:"巢";s:4:"㠯";s:3:"㠯";s:4:"巽";s:3:"巽";s:4:"帨";s:3:"帨";s:4:"帽";s:3:"帽";s:4:"幩";s:3:"幩";s:4:"㡢";s:3:"㡢";s:4:"𢆃";s:4:"𢆃";s:4:"㡼";s:3:"㡼";s:4:"庰";s:3:"庰";s:4:"庳";s:3:"庳";s:4:"庶";s:3:"庶";s:4:"廊";s:3:"廊";s:4:"𪎒";s:4:"𪎒";s:4:"廾";s:3:"廾";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"舁";s:3:"舁";s:4:"弢";s:3:"弢";s:4:"弢";s:3:"弢";s:4:"㣇";s:3:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:4:"形";s:3:"形";s:4:"彫";s:3:"彫";s:4:"㣣";s:3:"㣣";s:4:"徚";s:3:"徚";s:4:"忍";s:3:"忍";s:4:"志";s:3:"志";s:4:"忹";s:3:"忹";s:4:"悁";s:3:"悁";s:4:"㤺";s:3:"㤺";s:4:"㤜";s:3:"㤜";s:4:"悔";s:3:"悔";s:4:"𢛔";s:4:"𢛔";s:4:"惇";s:3:"惇";s:4:"慈";s:3:"慈";s:4:"慌";s:3:"慌";s:4:"慎";s:3:"慎";s:4:"慌";s:3:"慌";s:4:"慺";s:3:"慺";s:4:"憎";s:3:"憎";s:4:"憲";s:3:"憲";s:4:"憤";s:3:"憤";s:4:"憯";s:3:"憯";s:4:"懞";s:3:"懞";s:4:"懲";s:3:"懲";s:4:"懶";s:3:"懶";s:4:"成";s:3:"成";s:4:"戛";s:3:"戛";s:4:"扝";s:3:"扝";s:4:"抱";s:3:"抱";s:4:"拔";s:3:"拔";s:4:"捐";s:3:"捐";s:4:"𢬌";s:4:"𢬌";s:4:"挽";s:3:"挽";s:4:"拼";s:3:"拼";s:4:"捨";s:3:"捨";s:4:"掃";s:3:"掃";s:4:"揤";s:3:"揤";s:4:"𢯱";s:4:"𢯱";s:4:"搢";s:3:"搢";s:4:"揅";s:3:"揅";s:4:"掩";s:3:"掩";s:4:"㨮";s:3:"㨮";s:4:"摩";s:3:"摩";s:4:"摾";s:3:"摾";s:4:"撝";s:3:"撝";s:4:"摷";s:3:"摷";s:4:"㩬";s:3:"㩬";s:4:"敏";s:3:"敏";s:4:"敬";s:3:"敬";s:4:"𣀊";s:4:"𣀊";s:4:"旣";s:3:"旣";s:4:"書";s:3:"書";s:4:"晉";s:3:"晉";s:4:"㬙";s:3:"㬙";s:4:"暑";s:3:"暑";s:4:"㬈";s:3:"㬈";s:4:"㫤";s:3:"㫤";s:4:"冒";s:3:"冒";s:4:"冕";s:3:"冕";s:4:"最";s:3:"最";s:4:"暜";s:3:"暜";s:4:"肭";s:3:"肭";s:4:"䏙";s:3:"䏙";s:4:"朗";s:3:"朗";s:4:"望";s:3:"望";s:4:"朡";s:3:"朡";s:4:"杞";s:3:"杞";s:4:"杓";s:3:"杓";s:4:"𣏃";s:4:"𣏃";s:4:"㭉";s:3:"㭉";s:4:"柺";s:3:"柺";s:4:"枅";s:3:"枅";s:4:"桒";s:3:"桒";s:4:"梅";s:3:"梅";s:4:"𣑭";s:4:"𣑭";s:4:"梎";s:3:"梎";s:4:"栟";s:3:"栟";s:4:"椔";s:3:"椔";s:4:"㮝";s:3:"㮝";s:4:"楂";s:3:"楂";s:4:"榣";s:3:"榣";s:4:"槪";s:3:"槪";s:4:"檨";s:3:"檨";s:4:"𣚣";s:4:"𣚣";s:4:"櫛";s:3:"櫛";s:4:"㰘";s:3:"㰘";s:4:"次";s:3:"次";s:4:"𣢧";s:4:"𣢧";s:4:"歔";s:3:"歔";s:4:"㱎";s:3:"㱎";s:4:"歲";s:3:"歲";s:4:"殟";s:3:"殟";s:4:"殺";s:3:"殺";s:4:"殻";s:3:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:4:"汎";s:3:"汎";s:4:"𣲼";s:4:"𣲼";s:4:"沿";s:3:"沿";s:4:"泍";s:3:"泍";s:4:"汧";s:3:"汧";s:4:"洖";s:3:"洖";s:4:"派";s:3:"派";s:4:"海";s:3:"海";s:4:"流";s:3:"流";s:4:"浩";s:3:"浩";s:4:"浸";s:3:"浸";s:4:"涅";s:3:"涅";s:4:"𣴞";s:4:"𣴞";s:4:"洴";s:3:"洴";s:4:"港";s:3:"港";s:4:"湮";s:3:"湮";s:4:"㴳";s:3:"㴳";s:4:"滋";s:3:"滋";s:4:"滇";s:3:"滇";s:4:"𣻑";s:4:"𣻑";s:4:"淹";s:3:"淹";s:4:"潮";s:3:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:4:"濆";s:3:"濆";s:4:"瀹";s:3:"瀹";s:4:"瀞";s:3:"瀞";s:4:"瀛";s:3:"瀛";s:4:"㶖";s:3:"㶖";s:4:"灊";s:3:"灊";s:4:"災";s:3:"災";s:4:"灷";s:3:"灷";s:4:"炭";s:3:"炭";s:4:"𠔥";s:4:"𠔥";s:4:"煅";s:3:"煅";s:4:"𤉣";s:4:"𤉣";s:4:"熜";s:3:"熜";s:4:"𤎫";s:4:"𤎫";s:4:"爨";s:3:"爨";s:4:"爵";s:3:"爵";s:4:"牐";s:3:"牐";s:4:"𤘈";s:4:"𤘈";s:4:"犀";s:3:"犀";s:4:"犕";s:3:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:4:"獺";s:3:"獺";s:4:"王";s:3:"王";s:4:"㺬";s:3:"㺬";s:4:"玥";s:3:"玥";s:4:"㺸";s:3:"㺸";s:4:"㺸";s:3:"㺸";s:4:"瑇";s:3:"瑇";s:4:"瑜";s:3:"瑜";s:4:"瑱";s:3:"瑱";s:4:"璅";s:3:"璅";s:4:"瓊";s:3:"瓊";s:4:"㼛";s:3:"㼛";s:4:"甤";s:3:"甤";s:4:"𤰶";s:4:"𤰶";s:4:"甾";s:3:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"異";s:3:"異";s:4:"𢆟";s:4:"𢆟";s:4:"瘐";s:3:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:4:"㿼";s:3:"㿼";s:4:"䀈";s:3:"䀈";s:4:"直";s:3:"直";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:4:"眞";s:3:"眞";s:4:"真";s:3:"真";s:4:"真";s:3:"真";s:4:"睊";s:3:"睊";s:4:"䀹";s:3:"䀹";s:4:"瞋";s:3:"瞋";s:4:"䁆";s:3:"䁆";s:4:"䂖";s:3:"䂖";s:4:"𥐝";s:4:"𥐝";s:4:"硎";s:3:"硎";s:4:"碌";s:3:"碌";s:4:"磌";s:3:"磌";s:4:"䃣";s:3:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"祖";s:3:"祖";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:4:"福";s:3:"福";s:4:"秫";s:3:"秫";s:4:"䄯";s:3:"䄯";s:4:"穀";s:3:"穀";s:4:"穊";s:3:"穊";s:4:"穏";s:3:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"竮";s:3:"竮";s:4:"䈂";s:3:"䈂";s:4:"𥮫";s:4:"𥮫";s:4:"篆";s:3:"篆";s:4:"築";s:3:"築";s:4:"䈧";s:3:"䈧";s:4:"𥲀";s:4:"𥲀";s:4:"糒";s:3:"糒";s:4:"䊠";s:3:"䊠";s:4:"糨";s:3:"糨";s:4:"糣";s:3:"糣";s:4:"紀";s:3:"紀";s:4:"𥾆";s:4:"𥾆";s:4:"絣";s:3:"絣";s:4:"䌁";s:3:"䌁";s:4:"緇";s:3:"緇";s:4:"縂";s:3:"縂";s:4:"繅";s:3:"繅";s:4:"䌴";s:3:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:4:"䍙";s:3:"䍙";s:4:"𦋙";s:4:"𦋙";s:4:"罺";s:3:"罺";s:4:"𦌾";s:4:"𦌾";s:4:"羕";s:3:"羕";s:4:"翺";s:3:"翺";s:4:"者";s:3:"者";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:4:"聠";s:3:"聠";s:4:"𦖨";s:4:"𦖨";s:4:"聰";s:3:"聰";s:4:"𣍟";s:4:"𣍟";s:4:"䏕";s:3:"䏕";s:4:"育";s:3:"育";s:4:"脃";s:3:"脃";s:4:"䐋";s:3:"䐋";s:4:"脾";s:3:"脾";s:4:"媵";s:3:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:4:"舁";s:3:"舁";s:4:"舄";s:3:"舄";s:4:"辞";s:3:"辞";s:4:"䑫";s:3:"䑫";s:4:"芑";s:3:"芑";s:4:"芋";s:3:"芋";s:4:"芝";s:3:"芝";s:4:"劳";s:3:"劳";s:4:"花";s:3:"花";s:4:"芳";s:3:"芳";s:4:"芽";s:3:"芽";s:4:"苦";s:3:"苦";s:4:"𦬼";s:4:"𦬼";s:4:"若";s:3:"若";s:4:"茝";s:3:"茝";s:4:"荣";s:3:"荣";s:4:"莭";s:3:"莭";s:4:"茣";s:3:"茣";s:4:"莽";s:3:"莽";s:4:"菧";s:3:"菧";s:4:"著";s:3:"著";s:4:"荓";s:3:"荓";s:4:"菊";s:3:"菊";s:4:"菌";s:3:"菌";s:4:"菜";s:3:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:4:"䔫";s:3:"䔫";s:4:"蓱";s:3:"蓱";s:4:"蓳";s:3:"蓳";s:4:"蔖";s:3:"蔖";s:4:"𧏊";s:4:"𧏊";s:4:"蕤";s:3:"蕤";s:4:"𦼬";s:4:"𦼬";s:4:"䕝";s:3:"䕝";s:4:"䕡";s:3:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:4:"䕫";s:3:"䕫";s:4:"虐";s:3:"虐";s:4:"虜";s:3:"虜";s:4:"虧";s:3:"虧";s:4:"虩";s:3:"虩";s:4:"蚩";s:3:"蚩";s:4:"蚈";s:3:"蚈";s:4:"蜎";s:3:"蜎";s:4:"蛢";s:3:"蛢";s:4:"蝹";s:3:"蝹";s:4:"蜨";s:3:"蜨";s:4:"蝫";s:3:"蝫";s:4:"螆";s:3:"螆";s:4:"䗗";s:3:"䗗";s:4:"蟡";s:3:"蟡";s:4:"蠁";s:3:"蠁";s:4:"䗹";s:3:"䗹";s:4:"衠";s:3:"衠";s:4:"衣";s:3:"衣";s:4:"𧙧";s:4:"𧙧";s:4:"裗";s:3:"裗";s:4:"裞";s:3:"裞";s:4:"䘵";s:3:"䘵";s:4:"裺";s:3:"裺";s:4:"㒻";s:3:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:4:"䚾";s:3:"䚾";s:4:"䛇";s:3:"䛇";s:4:"誠";s:3:"誠";s:4:"諭";s:3:"諭";s:4:"變";s:3:"變";s:4:"豕";s:3:"豕";s:4:"𧲨";s:4:"𧲨";s:4:"貫";s:3:"貫";s:4:"賁";s:3:"賁";s:4:"贛";s:3:"贛";s:4:"起";s:3:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:4:"跋";s:3:"跋";s:4:"趼";s:3:"趼";s:4:"跰";s:3:"跰";s:4:"𠣞";s:4:"𠣞";s:4:"軔";s:3:"軔";s:4:"輸";s:3:"輸";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:4:"邔";s:3:"邔";s:4:"郱";s:3:"郱";s:4:"鄑";s:3:"鄑";s:4:"𨜮";s:4:"𨜮";s:4:"鄛";s:3:"鄛";s:4:"鈸";s:3:"鈸";s:4:"鋗";s:3:"鋗";s:4:"鋘";s:3:"鋘";s:4:"鉼";s:3:"鉼";s:4:"鏹";s:3:"鏹";s:4:"鐕";s:3:"鐕";s:4:"𨯺";s:4:"𨯺";s:4:"開";s:3:"開";s:4:"䦕";s:3:"䦕";s:4:"閷";s:3:"閷";s:4:"𨵷";s:4:"𨵷";s:4:"䧦";s:3:"䧦";s:4:"雃";s:3:"雃";s:4:"嶲";s:3:"嶲";s:4:"霣";s:3:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:4:"䩮";s:3:"䩮";s:4:"䩶";s:3:"䩶";s:4:"韠";s:3:"韠";s:4:"𩐊";s:4:"𩐊";s:4:"䪲";s:3:"䪲";s:4:"𩒖";s:4:"𩒖";s:4:"頋";s:3:"頋";s:4:"頋";s:3:"頋";s:4:"頩";s:3:"頩";s:4:"𩖶";s:4:"𩖶";s:4:"飢";s:3:"飢";s:4:"䬳";s:3:"䬳";s:4:"餩";s:3:"餩";s:4:"馧";s:3:"馧";s:4:"駂";s:3:"駂";s:4:"駾";s:3:"駾";s:4:"䯎";s:3:"䯎";s:4:"𩬰";s:4:"𩬰";s:4:"鬒";s:3:"鬒";s:4:"鱀";s:3:"鱀";s:4:"鳽";s:3:"鳽";s:4:"䳎";s:3:"䳎";s:4:"䳭";s:3:"䳭";s:4:"鵧";s:3:"鵧";s:4:"𪃎";s:4:"𪃎";s:4:"䳸";s:3:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:4:"麻";s:3:"麻";s:4:"䵖";s:3:"䵖";s:4:"黹";s:3:"黹";s:4:"黾";s:3:"黾";s:4:"鼅";s:3:"鼅";s:4:"鼏";s:3:"鼏";s:4:"鼖";s:3:"鼖";s:4:"鼻";s:3:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
-$utfCheckNFC = unserialize( 'a:1216:{s:2:"̀";s:1:"N";s:2:"́";s:1:"N";s:2:"̓";s:1:"N";s:2:"̈́";s:1:"N";s:2:"ʹ";s:1:"N";s:2:";";s:1:"N";s:2:"·";s:1:"N";s:3:"क़";s:1:"N";s:3:"ख़";s:1:"N";s:3:"ग़";s:1:"N";s:3:"ज़";s:1:"N";s:3:"ड़";s:1:"N";s:3:"ढ़";s:1:"N";s:3:"फ़";s:1:"N";s:3:"य़";s:1:"N";s:3:"ড়";s:1:"N";s:3:"ঢ়";s:1:"N";s:3:"য়";s:1:"N";s:3:"ਲ਼";s:1:"N";s:3:"ਸ਼";s:1:"N";s:3:"ਖ਼";s:1:"N";s:3:"ਗ਼";s:1:"N";s:3:"ਜ਼";s:1:"N";s:3:"ਫ਼";s:1:"N";s:3:"ଡ଼";s:1:"N";s:3:"ଢ଼";s:1:"N";s:3:"གྷ";s:1:"N";s:3:"ཌྷ";s:1:"N";s:3:"དྷ";s:1:"N";s:3:"བྷ";s:1:"N";s:3:"ཛྷ";s:1:"N";s:3:"ཀྵ";s:1:"N";s:3:"ཱི";s:1:"N";s:3:"ཱུ";s:1:"N";s:3:"ྲྀ";s:1:"N";s:3:"ླྀ";s:1:"N";s:3:"ཱྀ";s:1:"N";s:3:"ྒྷ";s:1:"N";s:3:"ྜྷ";s:1:"N";s:3:"ྡྷ";s:1:"N";s:3:"ྦྷ";s:1:"N";s:3:"ྫྷ";s:1:"N";s:3:"ྐྵ";s:1:"N";s:3:"ά";s:1:"N";s:3:"έ";s:1:"N";s:3:"ή";s:1:"N";s:3:"ί";s:1:"N";s:3:"ό";s:1:"N";s:3:"ύ";s:1:"N";s:3:"ώ";s:1:"N";s:3:"Ά";s:1:"N";s:3:"ι";s:1:"N";s:3:"Έ";s:1:"N";s:3:"Ή";s:1:"N";s:3:"ΐ";s:1:"N";s:3:"Ί";s:1:"N";s:3:"ΰ";s:1:"N";s:3:"Ύ";s:1:"N";s:3:"΅";s:1:"N";s:3:"`";s:1:"N";s:3:"Ό";s:1:"N";s:3:"Ώ";s:1:"N";s:3:"´";s:1:"N";s:3:" ";s:1:"N";s:3:" ";s:1:"N";s:3:"Ω";s:1:"N";s:3:"K";s:1:"N";s:3:"Å";s:1:"N";s:3:"〈";s:1:"N";s:3:"〉";s:1:"N";s:3:"⫝̸";s:1:"N";s:3:"豈";s:1:"N";s:3:"更";s:1:"N";s:3:"車";s:1:"N";s:3:"賈";s:1:"N";s:3:"滑";s:1:"N";s:3:"串";s:1:"N";s:3:"句";s:1:"N";s:3:"龜";s:1:"N";s:3:"龜";s:1:"N";s:3:"契";s:1:"N";s:3:"金";s:1:"N";s:3:"喇";s:1:"N";s:3:"奈";s:1:"N";s:3:"懶";s:1:"N";s:3:"癩";s:1:"N";s:3:"羅";s:1:"N";s:3:"蘿";s:1:"N";s:3:"螺";s:1:"N";s:3:"裸";s:1:"N";s:3:"邏";s:1:"N";s:3:"樂";s:1:"N";s:3:"洛";s:1:"N";s:3:"烙";s:1:"N";s:3:"珞";s:1:"N";s:3:"落";s:1:"N";s:3:"酪";s:1:"N";s:3:"駱";s:1:"N";s:3:"亂";s:1:"N";s:3:"卵";s:1:"N";s:3:"欄";s:1:"N";s:3:"爛";s:1:"N";s:3:"蘭";s:1:"N";s:3:"鸞";s:1:"N";s:3:"嵐";s:1:"N";s:3:"濫";s:1:"N";s:3:"藍";s:1:"N";s:3:"襤";s:1:"N";s:3:"拉";s:1:"N";s:3:"臘";s:1:"N";s:3:"蠟";s:1:"N";s:3:"廊";s:1:"N";s:3:"朗";s:1:"N";s:3:"浪";s:1:"N";s:3:"狼";s:1:"N";s:3:"郎";s:1:"N";s:3:"來";s:1:"N";s:3:"冷";s:1:"N";s:3:"勞";s:1:"N";s:3:"擄";s:1:"N";s:3:"櫓";s:1:"N";s:3:"爐";s:1:"N";s:3:"盧";s:1:"N";s:3:"老";s:1:"N";s:3:"蘆";s:1:"N";s:3:"虜";s:1:"N";s:3:"路";s:1:"N";s:3:"露";s:1:"N";s:3:"魯";s:1:"N";s:3:"鷺";s:1:"N";s:3:"碌";s:1:"N";s:3:"祿";s:1:"N";s:3:"綠";s:1:"N";s:3:"菉";s:1:"N";s:3:"錄";s:1:"N";s:3:"鹿";s:1:"N";s:3:"論";s:1:"N";s:3:"壟";s:1:"N";s:3:"弄";s:1:"N";s:3:"籠";s:1:"N";s:3:"聾";s:1:"N";s:3:"牢";s:1:"N";s:3:"磊";s:1:"N";s:3:"賂";s:1:"N";s:3:"雷";s:1:"N";s:3:"壘";s:1:"N";s:3:"屢";s:1:"N";s:3:"樓";s:1:"N";s:3:"淚";s:1:"N";s:3:"漏";s:1:"N";s:3:"累";s:1:"N";s:3:"縷";s:1:"N";s:3:"陋";s:1:"N";s:3:"勒";s:1:"N";s:3:"肋";s:1:"N";s:3:"凜";s:1:"N";s:3:"凌";s:1:"N";s:3:"稜";s:1:"N";s:3:"綾";s:1:"N";s:3:"菱";s:1:"N";s:3:"陵";s:1:"N";s:3:"讀";s:1:"N";s:3:"拏";s:1:"N";s:3:"樂";s:1:"N";s:3:"諾";s:1:"N";s:3:"丹";s:1:"N";s:3:"寧";s:1:"N";s:3:"怒";s:1:"N";s:3:"率";s:1:"N";s:3:"異";s:1:"N";s:3:"北";s:1:"N";s:3:"磻";s:1:"N";s:3:"便";s:1:"N";s:3:"復";s:1:"N";s:3:"不";s:1:"N";s:3:"泌";s:1:"N";s:3:"數";s:1:"N";s:3:"索";s:1:"N";s:3:"參";s:1:"N";s:3:"塞";s:1:"N";s:3:"省";s:1:"N";s:3:"葉";s:1:"N";s:3:"說";s:1:"N";s:3:"殺";s:1:"N";s:3:"辰";s:1:"N";s:3:"沈";s:1:"N";s:3:"拾";s:1:"N";s:3:"若";s:1:"N";s:3:"掠";s:1:"N";s:3:"略";s:1:"N";s:3:"亮";s:1:"N";s:3:"兩";s:1:"N";s:3:"凉";s:1:"N";s:3:"梁";s:1:"N";s:3:"糧";s:1:"N";s:3:"良";s:1:"N";s:3:"諒";s:1:"N";s:3:"量";s:1:"N";s:3:"勵";s:1:"N";s:3:"呂";s:1:"N";s:3:"女";s:1:"N";s:3:"廬";s:1:"N";s:3:"旅";s:1:"N";s:3:"濾";s:1:"N";s:3:"礪";s:1:"N";s:3:"閭";s:1:"N";s:3:"驪";s:1:"N";s:3:"麗";s:1:"N";s:3:"黎";s:1:"N";s:3:"力";s:1:"N";s:3:"曆";s:1:"N";s:3:"歷";s:1:"N";s:3:"轢";s:1:"N";s:3:"年";s:1:"N";s:3:"憐";s:1:"N";s:3:"戀";s:1:"N";s:3:"撚";s:1:"N";s:3:"漣";s:1:"N";s:3:"煉";s:1:"N";s:3:"璉";s:1:"N";s:3:"秊";s:1:"N";s:3:"練";s:1:"N";s:3:"聯";s:1:"N";s:3:"輦";s:1:"N";s:3:"蓮";s:1:"N";s:3:"連";s:1:"N";s:3:"鍊";s:1:"N";s:3:"列";s:1:"N";s:3:"劣";s:1:"N";s:3:"咽";s:1:"N";s:3:"烈";s:1:"N";s:3:"裂";s:1:"N";s:3:"說";s:1:"N";s:3:"廉";s:1:"N";s:3:"念";s:1:"N";s:3:"捻";s:1:"N";s:3:"殮";s:1:"N";s:3:"簾";s:1:"N";s:3:"獵";s:1:"N";s:3:"令";s:1:"N";s:3:"囹";s:1:"N";s:3:"寧";s:1:"N";s:3:"嶺";s:1:"N";s:3:"怜";s:1:"N";s:3:"玲";s:1:"N";s:3:"瑩";s:1:"N";s:3:"羚";s:1:"N";s:3:"聆";s:1:"N";s:3:"鈴";s:1:"N";s:3:"零";s:1:"N";s:3:"靈";s:1:"N";s:3:"領";s:1:"N";s:3:"例";s:1:"N";s:3:"禮";s:1:"N";s:3:"醴";s:1:"N";s:3:"隸";s:1:"N";s:3:"惡";s:1:"N";s:3:"了";s:1:"N";s:3:"僚";s:1:"N";s:3:"寮";s:1:"N";s:3:"尿";s:1:"N";s:3:"料";s:1:"N";s:3:"樂";s:1:"N";s:3:"燎";s:1:"N";s:3:"療";s:1:"N";s:3:"蓼";s:1:"N";s:3:"遼";s:1:"N";s:3:"龍";s:1:"N";s:3:"暈";s:1:"N";s:3:"阮";s:1:"N";s:3:"劉";s:1:"N";s:3:"杻";s:1:"N";s:3:"柳";s:1:"N";s:3:"流";s:1:"N";s:3:"溜";s:1:"N";s:3:"琉";s:1:"N";s:3:"留";s:1:"N";s:3:"硫";s:1:"N";s:3:"紐";s:1:"N";s:3:"類";s:1:"N";s:3:"六";s:1:"N";s:3:"戮";s:1:"N";s:3:"陸";s:1:"N";s:3:"倫";s:1:"N";s:3:"崙";s:1:"N";s:3:"淪";s:1:"N";s:3:"輪";s:1:"N";s:3:"律";s:1:"N";s:3:"慄";s:1:"N";s:3:"栗";s:1:"N";s:3:"率";s:1:"N";s:3:"隆";s:1:"N";s:3:"利";s:1:"N";s:3:"吏";s:1:"N";s:3:"履";s:1:"N";s:3:"易";s:1:"N";s:3:"李";s:1:"N";s:3:"梨";s:1:"N";s:3:"泥";s:1:"N";s:3:"理";s:1:"N";s:3:"痢";s:1:"N";s:3:"罹";s:1:"N";s:3:"裏";s:1:"N";s:3:"裡";s:1:"N";s:3:"里";s:1:"N";s:3:"離";s:1:"N";s:3:"匿";s:1:"N";s:3:"溺";s:1:"N";s:3:"吝";s:1:"N";s:3:"燐";s:1:"N";s:3:"璘";s:1:"N";s:3:"藺";s:1:"N";s:3:"隣";s:1:"N";s:3:"鱗";s:1:"N";s:3:"麟";s:1:"N";s:3:"林";s:1:"N";s:3:"淋";s:1:"N";s:3:"臨";s:1:"N";s:3:"立";s:1:"N";s:3:"笠";s:1:"N";s:3:"粒";s:1:"N";s:3:"狀";s:1:"N";s:3:"炙";s:1:"N";s:3:"識";s:1:"N";s:3:"什";s:1:"N";s:3:"茶";s:1:"N";s:3:"刺";s:1:"N";s:3:"切";s:1:"N";s:3:"度";s:1:"N";s:3:"拓";s:1:"N";s:3:"糖";s:1:"N";s:3:"宅";s:1:"N";s:3:"洞";s:1:"N";s:3:"暴";s:1:"N";s:3:"輻";s:1:"N";s:3:"行";s:1:"N";s:3:"降";s:1:"N";s:3:"見";s:1:"N";s:3:"廓";s:1:"N";s:3:"兀";s:1:"N";s:3:"嗀";s:1:"N";s:3:"塚";s:1:"N";s:3:"晴";s:1:"N";s:3:"凞";s:1:"N";s:3:"猪";s:1:"N";s:3:"益";s:1:"N";s:3:"礼";s:1:"N";s:3:"神";s:1:"N";s:3:"祥";s:1:"N";s:3:"福";s:1:"N";s:3:"靖";s:1:"N";s:3:"精";s:1:"N";s:3:"羽";s:1:"N";s:3:"蘒";s:1:"N";s:3:"諸";s:1:"N";s:3:"逸";s:1:"N";s:3:"都";s:1:"N";s:3:"飯";s:1:"N";s:3:"飼";s:1:"N";s:3:"館";s:1:"N";s:3:"鶴";s:1:"N";s:3:"侮";s:1:"N";s:3:"僧";s:1:"N";s:3:"免";s:1:"N";s:3:"勉";s:1:"N";s:3:"勤";s:1:"N";s:3:"卑";s:1:"N";s:3:"喝";s:1:"N";s:3:"嘆";s:1:"N";s:3:"器";s:1:"N";s:3:"塀";s:1:"N";s:3:"墨";s:1:"N";s:3:"層";s:1:"N";s:3:"屮";s:1:"N";s:3:"悔";s:1:"N";s:3:"慨";s:1:"N";s:3:"憎";s:1:"N";s:3:"懲";s:1:"N";s:3:"敏";s:1:"N";s:3:"既";s:1:"N";s:3:"暑";s:1:"N";s:3:"梅";s:1:"N";s:3:"海";s:1:"N";s:3:"渚";s:1:"N";s:3:"漢";s:1:"N";s:3:"煮";s:1:"N";s:3:"爫";s:1:"N";s:3:"琢";s:1:"N";s:3:"碑";s:1:"N";s:3:"社";s:1:"N";s:3:"祉";s:1:"N";s:3:"祈";s:1:"N";s:3:"祐";s:1:"N";s:3:"祖";s:1:"N";s:3:"祝";s:1:"N";s:3:"禍";s:1:"N";s:3:"禎";s:1:"N";s:3:"穀";s:1:"N";s:3:"突";s:1:"N";s:3:"節";s:1:"N";s:3:"練";s:1:"N";s:3:"縉";s:1:"N";s:3:"繁";s:1:"N";s:3:"署";s:1:"N";s:3:"者";s:1:"N";s:3:"臭";s:1:"N";s:3:"艹";s:1:"N";s:3:"艹";s:1:"N";s:3:"著";s:1:"N";s:3:"褐";s:1:"N";s:3:"視";s:1:"N";s:3:"謁";s:1:"N";s:3:"謹";s:1:"N";s:3:"賓";s:1:"N";s:3:"贈";s:1:"N";s:3:"辶";s:1:"N";s:3:"逸";s:1:"N";s:3:"難";s:1:"N";s:3:"響";s:1:"N";s:3:"頻";s:1:"N";s:3:"並";s:1:"N";s:3:"况";s:1:"N";s:3:"全";s:1:"N";s:3:"侀";s:1:"N";s:3:"充";s:1:"N";s:3:"冀";s:1:"N";s:3:"勇";s:1:"N";s:3:"勺";s:1:"N";s:3:"喝";s:1:"N";s:3:"啕";s:1:"N";s:3:"喙";s:1:"N";s:3:"嗢";s:1:"N";s:3:"塚";s:1:"N";s:3:"墳";s:1:"N";s:3:"奄";s:1:"N";s:3:"奔";s:1:"N";s:3:"婢";s:1:"N";s:3:"嬨";s:1:"N";s:3:"廒";s:1:"N";s:3:"廙";s:1:"N";s:3:"彩";s:1:"N";s:3:"徭";s:1:"N";s:3:"惘";s:1:"N";s:3:"慎";s:1:"N";s:3:"愈";s:1:"N";s:3:"憎";s:1:"N";s:3:"慠";s:1:"N";s:3:"懲";s:1:"N";s:3:"戴";s:1:"N";s:3:"揄";s:1:"N";s:3:"搜";s:1:"N";s:3:"摒";s:1:"N";s:3:"敖";s:1:"N";s:3:"晴";s:1:"N";s:3:"朗";s:1:"N";s:3:"望";s:1:"N";s:3:"杖";s:1:"N";s:3:"歹";s:1:"N";s:3:"殺";s:1:"N";s:3:"流";s:1:"N";s:3:"滛";s:1:"N";s:3:"滋";s:1:"N";s:3:"漢";s:1:"N";s:3:"瀞";s:1:"N";s:3:"煮";s:1:"N";s:3:"瞧";s:1:"N";s:3:"爵";s:1:"N";s:3:"犯";s:1:"N";s:3:"猪";s:1:"N";s:3:"瑱";s:1:"N";s:3:"甆";s:1:"N";s:3:"画";s:1:"N";s:3:"瘝";s:1:"N";s:3:"瘟";s:1:"N";s:3:"益";s:1:"N";s:3:"盛";s:1:"N";s:3:"直";s:1:"N";s:3:"睊";s:1:"N";s:3:"着";s:1:"N";s:3:"磌";s:1:"N";s:3:"窱";s:1:"N";s:3:"節";s:1:"N";s:3:"类";s:1:"N";s:3:"絛";s:1:"N";s:3:"練";s:1:"N";s:3:"缾";s:1:"N";s:3:"者";s:1:"N";s:3:"荒";s:1:"N";s:3:"華";s:1:"N";s:3:"蝹";s:1:"N";s:3:"襁";s:1:"N";s:3:"覆";s:1:"N";s:3:"視";s:1:"N";s:3:"調";s:1:"N";s:3:"諸";s:1:"N";s:3:"請";s:1:"N";s:3:"謁";s:1:"N";s:3:"諾";s:1:"N";s:3:"諭";s:1:"N";s:3:"謹";s:1:"N";s:3:"變";s:1:"N";s:3:"贈";s:1:"N";s:3:"輸";s:1:"N";s:3:"遲";s:1:"N";s:3:"醙";s:1:"N";s:3:"鉶";s:1:"N";s:3:"陼";s:1:"N";s:3:"難";s:1:"N";s:3:"靖";s:1:"N";s:3:"韛";s:1:"N";s:3:"響";s:1:"N";s:3:"頋";s:1:"N";s:3:"頻";s:1:"N";s:3:"鬒";s:1:"N";s:3:"龜";s:1:"N";s:3:"𢡊";s:1:"N";s:3:"𢡄";s:1:"N";s:3:"𣏕";s:1:"N";s:3:"㮝";s:1:"N";s:3:"䀘";s:1:"N";s:3:"䀹";s:1:"N";s:3:"𥉉";s:1:"N";s:3:"𥳐";s:1:"N";s:3:"𧻓";s:1:"N";s:3:"齃";s:1:"N";s:3:"龎";s:1:"N";s:3:"יִ";s:1:"N";s:3:"ײַ";s:1:"N";s:3:"שׁ";s:1:"N";s:3:"שׂ";s:1:"N";s:3:"שּׁ";s:1:"N";s:3:"שּׂ";s:1:"N";s:3:"אַ";s:1:"N";s:3:"אָ";s:1:"N";s:3:"אּ";s:1:"N";s:3:"בּ";s:1:"N";s:3:"גּ";s:1:"N";s:3:"דּ";s:1:"N";s:3:"הּ";s:1:"N";s:3:"וּ";s:1:"N";s:3:"זּ";s:1:"N";s:3:"טּ";s:1:"N";s:3:"יּ";s:1:"N";s:3:"ךּ";s:1:"N";s:3:"כּ";s:1:"N";s:3:"לּ";s:1:"N";s:3:"מּ";s:1:"N";s:3:"נּ";s:1:"N";s:3:"סּ";s:1:"N";s:3:"ףּ";s:1:"N";s:3:"פּ";s:1:"N";s:3:"צּ";s:1:"N";s:3:"קּ";s:1:"N";s:3:"רּ";s:1:"N";s:3:"שּ";s:1:"N";s:3:"תּ";s:1:"N";s:3:"וֹ";s:1:"N";s:3:"בֿ";s:1:"N";s:3:"כֿ";s:1:"N";s:3:"פֿ";s:1:"N";s:4:"𝅗𝅥";s:1:"N";s:4:"𝅘𝅥";s:1:"N";s:4:"𝅘𝅥𝅮";s:1:"N";s:4:"𝅘𝅥𝅯";s:1:"N";s:4:"𝅘𝅥𝅰";s:1:"N";s:4:"𝅘𝅥𝅱";s:1:"N";s:4:"𝅘𝅥𝅲";s:1:"N";s:4:"𝆹𝅥";s:1:"N";s:4:"𝆺𝅥";s:1:"N";s:4:"𝆹𝅥𝅮";s:1:"N";s:4:"𝆺𝅥𝅮";s:1:"N";s:4:"𝆹𝅥𝅯";s:1:"N";s:4:"𝆺𝅥𝅯";s:1:"N";s:4:"丽";s:1:"N";s:4:"丸";s:1:"N";s:4:"乁";s:1:"N";s:4:"𠄢";s:1:"N";s:4:"你";s:1:"N";s:4:"侮";s:1:"N";s:4:"侻";s:1:"N";s:4:"倂";s:1:"N";s:4:"偺";s:1:"N";s:4:"備";s:1:"N";s:4:"僧";s:1:"N";s:4:"像";s:1:"N";s:4:"㒞";s:1:"N";s:4:"𠘺";s:1:"N";s:4:"免";s:1:"N";s:4:"兔";s:1:"N";s:4:"兤";s:1:"N";s:4:"具";s:1:"N";s:4:"𠔜";s:1:"N";s:4:"㒹";s:1:"N";s:4:"內";s:1:"N";s:4:"再";s:1:"N";s:4:"𠕋";s:1:"N";s:4:"冗";s:1:"N";s:4:"冤";s:1:"N";s:4:"仌";s:1:"N";s:4:"冬";s:1:"N";s:4:"况";s:1:"N";s:4:"𩇟";s:1:"N";s:4:"凵";s:1:"N";s:4:"刃";s:1:"N";s:4:"㓟";s:1:"N";s:4:"刻";s:1:"N";s:4:"剆";s:1:"N";s:4:"割";s:1:"N";s:4:"剷";s:1:"N";s:4:"㔕";s:1:"N";s:4:"勇";s:1:"N";s:4:"勉";s:1:"N";s:4:"勤";s:1:"N";s:4:"勺";s:1:"N";s:4:"包";s:1:"N";s:4:"匆";s:1:"N";s:4:"北";s:1:"N";s:4:"卉";s:1:"N";s:4:"卑";s:1:"N";s:4:"博";s:1:"N";s:4:"即";s:1:"N";s:4:"卽";s:1:"N";s:4:"卿";s:1:"N";s:4:"卿";s:1:"N";s:4:"卿";s:1:"N";s:4:"𠨬";s:1:"N";s:4:"灰";s:1:"N";s:4:"及";s:1:"N";s:4:"叟";s:1:"N";s:4:"𠭣";s:1:"N";s:4:"叫";s:1:"N";s:4:"叱";s:1:"N";s:4:"吆";s:1:"N";s:4:"咞";s:1:"N";s:4:"吸";s:1:"N";s:4:"呈";s:1:"N";s:4:"周";s:1:"N";s:4:"咢";s:1:"N";s:4:"哶";s:1:"N";s:4:"唐";s:1:"N";s:4:"啓";s:1:"N";s:4:"啣";s:1:"N";s:4:"善";s:1:"N";s:4:"善";s:1:"N";s:4:"喙";s:1:"N";s:4:"喫";s:1:"N";s:4:"喳";s:1:"N";s:4:"嗂";s:1:"N";s:4:"圖";s:1:"N";s:4:"嘆";s:1:"N";s:4:"圗";s:1:"N";s:4:"噑";s:1:"N";s:4:"噴";s:1:"N";s:4:"切";s:1:"N";s:4:"壮";s:1:"N";s:4:"城";s:1:"N";s:4:"埴";s:1:"N";s:4:"堍";s:1:"N";s:4:"型";s:1:"N";s:4:"堲";s:1:"N";s:4:"報";s:1:"N";s:4:"墬";s:1:"N";s:4:"𡓤";s:1:"N";s:4:"売";s:1:"N";s:4:"壷";s:1:"N";s:4:"夆";s:1:"N";s:4:"多";s:1:"N";s:4:"夢";s:1:"N";s:4:"奢";s:1:"N";s:4:"𡚨";s:1:"N";s:4:"𡛪";s:1:"N";s:4:"姬";s:1:"N";s:4:"娛";s:1:"N";s:4:"娧";s:1:"N";s:4:"姘";s:1:"N";s:4:"婦";s:1:"N";s:4:"㛮";s:1:"N";s:4:"㛼";s:1:"N";s:4:"嬈";s:1:"N";s:4:"嬾";s:1:"N";s:4:"嬾";s:1:"N";s:4:"𡧈";s:1:"N";s:4:"寃";s:1:"N";s:4:"寘";s:1:"N";s:4:"寧";s:1:"N";s:4:"寳";s:1:"N";s:4:"𡬘";s:1:"N";s:4:"寿";s:1:"N";s:4:"将";s:1:"N";s:4:"当";s:1:"N";s:4:"尢";s:1:"N";s:4:"㞁";s:1:"N";s:4:"屠";s:1:"N";s:4:"屮";s:1:"N";s:4:"峀";s:1:"N";s:4:"岍";s:1:"N";s:4:"𡷤";s:1:"N";s:4:"嵃";s:1:"N";s:4:"𡷦";s:1:"N";s:4:"嵮";s:1:"N";s:4:"嵫";s:1:"N";s:4:"嵼";s:1:"N";s:4:"巡";s:1:"N";s:4:"巢";s:1:"N";s:4:"㠯";s:1:"N";s:4:"巽";s:1:"N";s:4:"帨";s:1:"N";s:4:"帽";s:1:"N";s:4:"幩";s:1:"N";s:4:"㡢";s:1:"N";s:4:"𢆃";s:1:"N";s:4:"㡼";s:1:"N";s:4:"庰";s:1:"N";s:4:"庳";s:1:"N";s:4:"庶";s:1:"N";s:4:"廊";s:1:"N";s:4:"𪎒";s:1:"N";s:4:"廾";s:1:"N";s:4:"𢌱";s:1:"N";s:4:"𢌱";s:1:"N";s:4:"舁";s:1:"N";s:4:"弢";s:1:"N";s:4:"弢";s:1:"N";s:4:"㣇";s:1:"N";s:4:"𣊸";s:1:"N";s:4:"𦇚";s:1:"N";s:4:"形";s:1:"N";s:4:"彫";s:1:"N";s:4:"㣣";s:1:"N";s:4:"徚";s:1:"N";s:4:"忍";s:1:"N";s:4:"志";s:1:"N";s:4:"忹";s:1:"N";s:4:"悁";s:1:"N";s:4:"㤺";s:1:"N";s:4:"㤜";s:1:"N";s:4:"悔";s:1:"N";s:4:"𢛔";s:1:"N";s:4:"惇";s:1:"N";s:4:"慈";s:1:"N";s:4:"慌";s:1:"N";s:4:"慎";s:1:"N";s:4:"慌";s:1:"N";s:4:"慺";s:1:"N";s:4:"憎";s:1:"N";s:4:"憲";s:1:"N";s:4:"憤";s:1:"N";s:4:"憯";s:1:"N";s:4:"懞";s:1:"N";s:4:"懲";s:1:"N";s:4:"懶";s:1:"N";s:4:"成";s:1:"N";s:4:"戛";s:1:"N";s:4:"扝";s:1:"N";s:4:"抱";s:1:"N";s:4:"拔";s:1:"N";s:4:"捐";s:1:"N";s:4:"𢬌";s:1:"N";s:4:"挽";s:1:"N";s:4:"拼";s:1:"N";s:4:"捨";s:1:"N";s:4:"掃";s:1:"N";s:4:"揤";s:1:"N";s:4:"𢯱";s:1:"N";s:4:"搢";s:1:"N";s:4:"揅";s:1:"N";s:4:"掩";s:1:"N";s:4:"㨮";s:1:"N";s:4:"摩";s:1:"N";s:4:"摾";s:1:"N";s:4:"撝";s:1:"N";s:4:"摷";s:1:"N";s:4:"㩬";s:1:"N";s:4:"敏";s:1:"N";s:4:"敬";s:1:"N";s:4:"𣀊";s:1:"N";s:4:"旣";s:1:"N";s:4:"書";s:1:"N";s:4:"晉";s:1:"N";s:4:"㬙";s:1:"N";s:4:"暑";s:1:"N";s:4:"㬈";s:1:"N";s:4:"㫤";s:1:"N";s:4:"冒";s:1:"N";s:4:"冕";s:1:"N";s:4:"最";s:1:"N";s:4:"暜";s:1:"N";s:4:"肭";s:1:"N";s:4:"䏙";s:1:"N";s:4:"朗";s:1:"N";s:4:"望";s:1:"N";s:4:"朡";s:1:"N";s:4:"杞";s:1:"N";s:4:"杓";s:1:"N";s:4:"𣏃";s:1:"N";s:4:"㭉";s:1:"N";s:4:"柺";s:1:"N";s:4:"枅";s:1:"N";s:4:"桒";s:1:"N";s:4:"梅";s:1:"N";s:4:"𣑭";s:1:"N";s:4:"梎";s:1:"N";s:4:"栟";s:1:"N";s:4:"椔";s:1:"N";s:4:"㮝";s:1:"N";s:4:"楂";s:1:"N";s:4:"榣";s:1:"N";s:4:"槪";s:1:"N";s:4:"檨";s:1:"N";s:4:"𣚣";s:1:"N";s:4:"櫛";s:1:"N";s:4:"㰘";s:1:"N";s:4:"次";s:1:"N";s:4:"𣢧";s:1:"N";s:4:"歔";s:1:"N";s:4:"㱎";s:1:"N";s:4:"歲";s:1:"N";s:4:"殟";s:1:"N";s:4:"殺";s:1:"N";s:4:"殻";s:1:"N";s:4:"𣪍";s:1:"N";s:4:"𡴋";s:1:"N";s:4:"𣫺";s:1:"N";s:4:"汎";s:1:"N";s:4:"𣲼";s:1:"N";s:4:"沿";s:1:"N";s:4:"泍";s:1:"N";s:4:"汧";s:1:"N";s:4:"洖";s:1:"N";s:4:"派";s:1:"N";s:4:"海";s:1:"N";s:4:"流";s:1:"N";s:4:"浩";s:1:"N";s:4:"浸";s:1:"N";s:4:"涅";s:1:"N";s:4:"𣴞";s:1:"N";s:4:"洴";s:1:"N";s:4:"港";s:1:"N";s:4:"湮";s:1:"N";s:4:"㴳";s:1:"N";s:4:"滋";s:1:"N";s:4:"滇";s:1:"N";s:4:"𣻑";s:1:"N";s:4:"淹";s:1:"N";s:4:"潮";s:1:"N";s:4:"𣽞";s:1:"N";s:4:"𣾎";s:1:"N";s:4:"濆";s:1:"N";s:4:"瀹";s:1:"N";s:4:"瀞";s:1:"N";s:4:"瀛";s:1:"N";s:4:"㶖";s:1:"N";s:4:"灊";s:1:"N";s:4:"災";s:1:"N";s:4:"灷";s:1:"N";s:4:"炭";s:1:"N";s:4:"𠔥";s:1:"N";s:4:"煅";s:1:"N";s:4:"𤉣";s:1:"N";s:4:"熜";s:1:"N";s:4:"𤎫";s:1:"N";s:4:"爨";s:1:"N";s:4:"爵";s:1:"N";s:4:"牐";s:1:"N";s:4:"𤘈";s:1:"N";s:4:"犀";s:1:"N";s:4:"犕";s:1:"N";s:4:"𤜵";s:1:"N";s:4:"𤠔";s:1:"N";s:4:"獺";s:1:"N";s:4:"王";s:1:"N";s:4:"㺬";s:1:"N";s:4:"玥";s:1:"N";s:4:"㺸";s:1:"N";s:4:"㺸";s:1:"N";s:4:"瑇";s:1:"N";s:4:"瑜";s:1:"N";s:4:"瑱";s:1:"N";s:4:"璅";s:1:"N";s:4:"瓊";s:1:"N";s:4:"㼛";s:1:"N";s:4:"甤";s:1:"N";s:4:"𤰶";s:1:"N";s:4:"甾";s:1:"N";s:4:"𤲒";s:1:"N";s:4:"異";s:1:"N";s:4:"𢆟";s:1:"N";s:4:"瘐";s:1:"N";s:4:"𤾡";s:1:"N";s:4:"𤾸";s:1:"N";s:4:"𥁄";s:1:"N";s:4:"㿼";s:1:"N";s:4:"䀈";s:1:"N";s:4:"直";s:1:"N";s:4:"𥃳";s:1:"N";s:4:"𥃲";s:1:"N";s:4:"𥄙";s:1:"N";s:4:"𥄳";s:1:"N";s:4:"眞";s:1:"N";s:4:"真";s:1:"N";s:4:"真";s:1:"N";s:4:"睊";s:1:"N";s:4:"䀹";s:1:"N";s:4:"瞋";s:1:"N";s:4:"䁆";s:1:"N";s:4:"䂖";s:1:"N";s:4:"𥐝";s:1:"N";s:4:"硎";s:1:"N";s:4:"碌";s:1:"N";s:4:"磌";s:1:"N";s:4:"䃣";s:1:"N";s:4:"𥘦";s:1:"N";s:4:"祖";s:1:"N";s:4:"𥚚";s:1:"N";s:4:"𥛅";s:1:"N";s:4:"福";s:1:"N";s:4:"秫";s:1:"N";s:4:"䄯";s:1:"N";s:4:"穀";s:1:"N";s:4:"穊";s:1:"N";s:4:"穏";s:1:"N";s:4:"𥥼";s:1:"N";s:4:"𥪧";s:1:"N";s:4:"𥪧";s:1:"N";s:4:"竮";s:1:"N";s:4:"䈂";s:1:"N";s:4:"𥮫";s:1:"N";s:4:"篆";s:1:"N";s:4:"築";s:1:"N";s:4:"䈧";s:1:"N";s:4:"𥲀";s:1:"N";s:4:"糒";s:1:"N";s:4:"䊠";s:1:"N";s:4:"糨";s:1:"N";s:4:"糣";s:1:"N";s:4:"紀";s:1:"N";s:4:"𥾆";s:1:"N";s:4:"絣";s:1:"N";s:4:"䌁";s:1:"N";s:4:"緇";s:1:"N";s:4:"縂";s:1:"N";s:4:"繅";s:1:"N";s:4:"䌴";s:1:"N";s:4:"𦈨";s:1:"N";s:4:"𦉇";s:1:"N";s:4:"䍙";s:1:"N";s:4:"𦋙";s:1:"N";s:4:"罺";s:1:"N";s:4:"𦌾";s:1:"N";s:4:"羕";s:1:"N";s:4:"翺";s:1:"N";s:4:"者";s:1:"N";s:4:"𦓚";s:1:"N";s:4:"𦔣";s:1:"N";s:4:"聠";s:1:"N";s:4:"𦖨";s:1:"N";s:4:"聰";s:1:"N";s:4:"𣍟";s:1:"N";s:4:"䏕";s:1:"N";s:4:"育";s:1:"N";s:4:"脃";s:1:"N";s:4:"䐋";s:1:"N";s:4:"脾";s:1:"N";s:4:"媵";s:1:"N";s:4:"𦞧";s:1:"N";s:4:"𦞵";s:1:"N";s:4:"𣎓";s:1:"N";s:4:"𣎜";s:1:"N";s:4:"舁";s:1:"N";s:4:"舄";s:1:"N";s:4:"辞";s:1:"N";s:4:"䑫";s:1:"N";s:4:"芑";s:1:"N";s:4:"芋";s:1:"N";s:4:"芝";s:1:"N";s:4:"劳";s:1:"N";s:4:"花";s:1:"N";s:4:"芳";s:1:"N";s:4:"芽";s:1:"N";s:4:"苦";s:1:"N";s:4:"𦬼";s:1:"N";s:4:"若";s:1:"N";s:4:"茝";s:1:"N";s:4:"荣";s:1:"N";s:4:"莭";s:1:"N";s:4:"茣";s:1:"N";s:4:"莽";s:1:"N";s:4:"菧";s:1:"N";s:4:"著";s:1:"N";s:4:"荓";s:1:"N";s:4:"菊";s:1:"N";s:4:"菌";s:1:"N";s:4:"菜";s:1:"N";s:4:"𦰶";s:1:"N";s:4:"𦵫";s:1:"N";s:4:"𦳕";s:1:"N";s:4:"䔫";s:1:"N";s:4:"蓱";s:1:"N";s:4:"蓳";s:1:"N";s:4:"蔖";s:1:"N";s:4:"𧏊";s:1:"N";s:4:"蕤";s:1:"N";s:4:"𦼬";s:1:"N";s:4:"䕝";s:1:"N";s:4:"䕡";s:1:"N";s:4:"𦾱";s:1:"N";s:4:"𧃒";s:1:"N";s:4:"䕫";s:1:"N";s:4:"虐";s:1:"N";s:4:"虜";s:1:"N";s:4:"虧";s:1:"N";s:4:"虩";s:1:"N";s:4:"蚩";s:1:"N";s:4:"蚈";s:1:"N";s:4:"蜎";s:1:"N";s:4:"蛢";s:1:"N";s:4:"蝹";s:1:"N";s:4:"蜨";s:1:"N";s:4:"蝫";s:1:"N";s:4:"螆";s:1:"N";s:4:"䗗";s:1:"N";s:4:"蟡";s:1:"N";s:4:"蠁";s:1:"N";s:4:"䗹";s:1:"N";s:4:"衠";s:1:"N";s:4:"衣";s:1:"N";s:4:"𧙧";s:1:"N";s:4:"裗";s:1:"N";s:4:"裞";s:1:"N";s:4:"䘵";s:1:"N";s:4:"裺";s:1:"N";s:4:"㒻";s:1:"N";s:4:"𧢮";s:1:"N";s:4:"𧥦";s:1:"N";s:4:"䚾";s:1:"N";s:4:"䛇";s:1:"N";s:4:"誠";s:1:"N";s:4:"諭";s:1:"N";s:4:"變";s:1:"N";s:4:"豕";s:1:"N";s:4:"𧲨";s:1:"N";s:4:"貫";s:1:"N";s:4:"賁";s:1:"N";s:4:"贛";s:1:"N";s:4:"起";s:1:"N";s:4:"𧼯";s:1:"N";s:4:"𠠄";s:1:"N";s:4:"跋";s:1:"N";s:4:"趼";s:1:"N";s:4:"跰";s:1:"N";s:4:"𠣞";s:1:"N";s:4:"軔";s:1:"N";s:4:"輸";s:1:"N";s:4:"𨗒";s:1:"N";s:4:"𨗭";s:1:"N";s:4:"邔";s:1:"N";s:4:"郱";s:1:"N";s:4:"鄑";s:1:"N";s:4:"𨜮";s:1:"N";s:4:"鄛";s:1:"N";s:4:"鈸";s:1:"N";s:4:"鋗";s:1:"N";s:4:"鋘";s:1:"N";s:4:"鉼";s:1:"N";s:4:"鏹";s:1:"N";s:4:"鐕";s:1:"N";s:4:"𨯺";s:1:"N";s:4:"開";s:1:"N";s:4:"䦕";s:1:"N";s:4:"閷";s:1:"N";s:4:"𨵷";s:1:"N";s:4:"䧦";s:1:"N";s:4:"雃";s:1:"N";s:4:"嶲";s:1:"N";s:4:"霣";s:1:"N";s:4:"𩅅";s:1:"N";s:4:"𩈚";s:1:"N";s:4:"䩮";s:1:"N";s:4:"䩶";s:1:"N";s:4:"韠";s:1:"N";s:4:"𩐊";s:1:"N";s:4:"䪲";s:1:"N";s:4:"𩒖";s:1:"N";s:4:"頋";s:1:"N";s:4:"頋";s:1:"N";s:4:"頩";s:1:"N";s:4:"𩖶";s:1:"N";s:4:"飢";s:1:"N";s:4:"䬳";s:1:"N";s:4:"餩";s:1:"N";s:4:"馧";s:1:"N";s:4:"駂";s:1:"N";s:4:"駾";s:1:"N";s:4:"䯎";s:1:"N";s:4:"𩬰";s:1:"N";s:4:"鬒";s:1:"N";s:4:"鱀";s:1:"N";s:4:"鳽";s:1:"N";s:4:"䳎";s:1:"N";s:4:"䳭";s:1:"N";s:4:"鵧";s:1:"N";s:4:"𪃎";s:1:"N";s:4:"䳸";s:1:"N";s:4:"𪄅";s:1:"N";s:4:"𪈎";s:1:"N";s:4:"𪊑";s:1:"N";s:4:"麻";s:1:"N";s:4:"䵖";s:1:"N";s:4:"黹";s:1:"N";s:4:"黾";s:1:"N";s:4:"鼅";s:1:"N";s:4:"鼏";s:1:"N";s:4:"鼖";s:1:"N";s:4:"鼻";s:1:"N";s:4:"𪘀";s:1:"N";s:2:"̀";s:1:"M";s:2:"́";s:1:"M";s:2:"̂";s:1:"M";s:2:"̃";s:1:"M";s:2:"̄";s:1:"M";s:2:"̆";s:1:"M";s:2:"̇";s:1:"M";s:2:"̈";s:1:"M";s:2:"̉";s:1:"M";s:2:"̊";s:1:"M";s:2:"̋";s:1:"M";s:2:"̌";s:1:"M";s:2:"̏";s:1:"M";s:2:"̑";s:1:"M";s:2:"̓";s:1:"M";s:2:"̔";s:1:"M";s:2:"̛";s:1:"M";s:2:"̣";s:1:"M";s:2:"̤";s:1:"M";s:2:"̥";s:1:"M";s:2:"̦";s:1:"M";s:2:"̧";s:1:"M";s:2:"̨";s:1:"M";s:2:"̭";s:1:"M";s:2:"̮";s:1:"M";s:2:"̰";s:1:"M";s:2:"̱";s:1:"M";s:2:"̸";s:1:"M";s:2:"͂";s:1:"M";s:2:"ͅ";s:1:"M";s:2:"ٓ";s:1:"M";s:2:"ٔ";s:1:"M";s:2:"ٕ";s:1:"M";s:3:"़";s:1:"M";s:3:"া";s:1:"M";s:3:"ৗ";s:1:"M";s:3:"ା";s:1:"M";s:3:"ୖ";s:1:"M";s:3:"ୗ";s:1:"M";s:3:"ா";s:1:"M";s:3:"ௗ";s:1:"M";s:3:"ౖ";s:1:"M";s:3:"ೂ";s:1:"M";s:3:"ೕ";s:1:"M";s:3:"ೖ";s:1:"M";s:3:"ാ";s:1:"M";s:3:"ൗ";s:1:"M";s:3:"්";s:1:"M";s:3:"ා";s:1:"M";s:3:"ෟ";s:1:"M";s:3:"ီ";s:1:"M";s:3:"ᅡ";s:1:"M";s:3:"ᅢ";s:1:"M";s:3:"ᅣ";s:1:"M";s:3:"ᅤ";s:1:"M";s:3:"ᅥ";s:1:"M";s:3:"ᅦ";s:1:"M";s:3:"ᅧ";s:1:"M";s:3:"ᅨ";s:1:"M";s:3:"ᅩ";s:1:"M";s:3:"ᅪ";s:1:"M";s:3:"ᅫ";s:1:"M";s:3:"ᅬ";s:1:"M";s:3:"ᅭ";s:1:"M";s:3:"ᅮ";s:1:"M";s:3:"ᅯ";s:1:"M";s:3:"ᅰ";s:1:"M";s:3:"ᅱ";s:1:"M";s:3:"ᅲ";s:1:"M";s:3:"ᅳ";s:1:"M";s:3:"ᅴ";s:1:"M";s:3:"ᅵ";s:1:"M";s:3:"ᆨ";s:1:"M";s:3:"ᆩ";s:1:"M";s:3:"ᆪ";s:1:"M";s:3:"ᆫ";s:1:"M";s:3:"ᆬ";s:1:"M";s:3:"ᆭ";s:1:"M";s:3:"ᆮ";s:1:"M";s:3:"ᆯ";s:1:"M";s:3:"ᆰ";s:1:"M";s:3:"ᆱ";s:1:"M";s:3:"ᆲ";s:1:"M";s:3:"ᆳ";s:1:"M";s:3:"ᆴ";s:1:"M";s:3:"ᆵ";s:1:"M";s:3:"ᆶ";s:1:"M";s:3:"ᆷ";s:1:"M";s:3:"ᆸ";s:1:"M";s:3:"ᆹ";s:1:"M";s:3:"ᆺ";s:1:"M";s:3:"ᆻ";s:1:"M";s:3:"ᆼ";s:1:"M";s:3:"ᆽ";s:1:"M";s:3:"ᆾ";s:1:"M";s:3:"ᆿ";s:1:"M";s:3:"ᇀ";s:1:"M";s:3:"ᇁ";s:1:"M";s:3:"ᇂ";s:1:"M";s:3:"゙";s:1:"M";s:3:"゚";s:1:"M";}' );
+$utfCombiningClass = unserialize( 'a:418:{s:2:"̀";i:230;s:2:"́";i:230;s:2:"̂";i:230;s:2:"̃";i:230;s:2:"̄";i:230;s:2:"̅";i:230;s:2:"̆";i:230;s:2:"̇";i:230;s:2:"̈";i:230;s:2:"̉";i:230;s:2:"̊";i:230;s:2:"̋";i:230;s:2:"̌";i:230;s:2:"̍";i:230;s:2:"̎";i:230;s:2:"̏";i:230;s:2:"̐";i:230;s:2:"̑";i:230;s:2:"̒";i:230;s:2:"̓";i:230;s:2:"̔";i:230;s:2:"̕";i:232;s:2:"̖";i:220;s:2:"̗";i:220;s:2:"̘";i:220;s:2:"̙";i:220;s:2:"̚";i:232;s:2:"̛";i:216;s:2:"̜";i:220;s:2:"̝";i:220;s:2:"̞";i:220;s:2:"̟";i:220;s:2:"̠";i:220;s:2:"̡";i:202;s:2:"̢";i:202;s:2:"̣";i:220;s:2:"̤";i:220;s:2:"̥";i:220;s:2:"̦";i:220;s:2:"̧";i:202;s:2:"̨";i:202;s:2:"̩";i:220;s:2:"̪";i:220;s:2:"̫";i:220;s:2:"̬";i:220;s:2:"̭";i:220;s:2:"̮";i:220;s:2:"̯";i:220;s:2:"̰";i:220;s:2:"̱";i:220;s:2:"̲";i:220;s:2:"̳";i:220;s:2:"̴";i:1;s:2:"̵";i:1;s:2:"̶";i:1;s:2:"̷";i:1;s:2:"̸";i:1;s:2:"̹";i:220;s:2:"̺";i:220;s:2:"̻";i:220;s:2:"̼";i:220;s:2:"̽";i:230;s:2:"̾";i:230;s:2:"̿";i:230;s:2:"̀";i:230;s:2:"́";i:230;s:2:"͂";i:230;s:2:"̓";i:230;s:2:"̈́";i:230;s:2:"ͅ";i:240;s:2:"͆";i:230;s:2:"͇";i:220;s:2:"͈";i:220;s:2:"͉";i:220;s:2:"͊";i:230;s:2:"͋";i:230;s:2:"͌";i:230;s:2:"͍";i:220;s:2:"͎";i:220;s:2:"͐";i:230;s:2:"͑";i:230;s:2:"͒";i:230;s:2:"͓";i:220;s:2:"͔";i:220;s:2:"͕";i:220;s:2:"͖";i:220;s:2:"͗";i:230;s:2:"͘";i:232;s:2:"͙";i:220;s:2:"͚";i:220;s:2:"͛";i:230;s:2:"͜";i:233;s:2:"͝";i:234;s:2:"͞";i:234;s:2:"͟";i:233;s:2:"͠";i:234;s:2:"͡";i:234;s:2:"͢";i:233;s:2:"ͣ";i:230;s:2:"ͤ";i:230;s:2:"ͥ";i:230;s:2:"ͦ";i:230;s:2:"ͧ";i:230;s:2:"ͨ";i:230;s:2:"ͩ";i:230;s:2:"ͪ";i:230;s:2:"ͫ";i:230;s:2:"ͬ";i:230;s:2:"ͭ";i:230;s:2:"ͮ";i:230;s:2:"ͯ";i:230;s:2:"҃";i:230;s:2:"҄";i:230;s:2:"҅";i:230;s:2:"҆";i:230;s:2:"֑";i:220;s:2:"֒";i:230;s:2:"֓";i:230;s:2:"֔";i:230;s:2:"֕";i:230;s:2:"֖";i:220;s:2:"֗";i:230;s:2:"֘";i:230;s:2:"֙";i:230;s:2:"֚";i:222;s:2:"֛";i:220;s:2:"֜";i:230;s:2:"֝";i:230;s:2:"֞";i:230;s:2:"֟";i:230;s:2:"֠";i:230;s:2:"֡";i:230;s:2:"֢";i:220;s:2:"֣";i:220;s:2:"֤";i:220;s:2:"֥";i:220;s:2:"֦";i:220;s:2:"֧";i:220;s:2:"֨";i:230;s:2:"֩";i:230;s:2:"֪";i:220;s:2:"֫";i:230;s:2:"֬";i:230;s:2:"֭";i:222;s:2:"֮";i:228;s:2:"֯";i:230;s:2:"ְ";i:10;s:2:"ֱ";i:11;s:2:"ֲ";i:12;s:2:"ֳ";i:13;s:2:"ִ";i:14;s:2:"ֵ";i:15;s:2:"ֶ";i:16;s:2:"ַ";i:17;s:2:"ָ";i:18;s:2:"ֹ";i:19;s:2:"ֺ";i:19;s:2:"ֻ";i:20;s:2:"ּ";i:21;s:2:"ֽ";i:22;s:2:"ֿ";i:23;s:2:"ׁ";i:24;s:2:"ׂ";i:25;s:2:"ׄ";i:230;s:2:"ׅ";i:220;s:2:"ׇ";i:18;s:2:"ؐ";i:230;s:2:"ؑ";i:230;s:2:"ؒ";i:230;s:2:"ؓ";i:230;s:2:"ؔ";i:230;s:2:"ؕ";i:230;s:2:"ً";i:27;s:2:"ٌ";i:28;s:2:"ٍ";i:29;s:2:"َ";i:30;s:2:"ُ";i:31;s:2:"ِ";i:32;s:2:"ّ";i:33;s:2:"ْ";i:34;s:2:"ٓ";i:230;s:2:"ٔ";i:230;s:2:"ٕ";i:220;s:2:"ٖ";i:220;s:2:"ٗ";i:230;s:2:"٘";i:230;s:2:"ٙ";i:230;s:2:"ٚ";i:230;s:2:"ٛ";i:230;s:2:"ٜ";i:220;s:2:"ٝ";i:230;s:2:"ٞ";i:230;s:2:"ٰ";i:35;s:2:"ۖ";i:230;s:2:"ۗ";i:230;s:2:"ۘ";i:230;s:2:"ۙ";i:230;s:2:"ۚ";i:230;s:2:"ۛ";i:230;s:2:"ۜ";i:230;s:2:"۟";i:230;s:2:"۠";i:230;s:2:"ۡ";i:230;s:2:"ۢ";i:230;s:2:"ۣ";i:220;s:2:"ۤ";i:230;s:2:"ۧ";i:230;s:2:"ۨ";i:230;s:2:"۪";i:220;s:2:"۫";i:230;s:2:"۬";i:230;s:2:"ۭ";i:220;s:2:"ܑ";i:36;s:2:"ܰ";i:230;s:2:"ܱ";i:220;s:2:"ܲ";i:230;s:2:"ܳ";i:230;s:2:"ܴ";i:220;s:2:"ܵ";i:230;s:2:"ܶ";i:230;s:2:"ܷ";i:220;s:2:"ܸ";i:220;s:2:"ܹ";i:220;s:2:"ܺ";i:230;s:2:"ܻ";i:220;s:2:"ܼ";i:220;s:2:"ܽ";i:230;s:2:"ܾ";i:220;s:2:"ܿ";i:230;s:2:"݀";i:230;s:2:"݁";i:230;s:2:"݂";i:220;s:2:"݃";i:230;s:2:"݄";i:220;s:2:"݅";i:230;s:2:"݆";i:220;s:2:"݇";i:230;s:2:"݈";i:220;s:2:"݉";i:230;s:2:"݊";i:230;s:2:"߫";i:230;s:2:"߬";i:230;s:2:"߭";i:230;s:2:"߮";i:230;s:2:"߯";i:230;s:2:"߰";i:230;s:2:"߱";i:230;s:2:"߲";i:220;s:2:"߳";i:230;s:3:"़";i:7;s:3:"्";i:9;s:3:"॑";i:230;s:3:"॒";i:220;s:3:"॓";i:230;s:3:"॔";i:230;s:3:"়";i:7;s:3:"্";i:9;s:3:"਼";i:7;s:3:"੍";i:9;s:3:"઼";i:7;s:3:"્";i:9;s:3:"଼";i:7;s:3:"୍";i:9;s:3:"்";i:9;s:3:"్";i:9;s:3:"ౕ";i:84;s:3:"ౖ";i:91;s:3:"಼";i:7;s:3:"್";i:9;s:3:"്";i:9;s:3:"්";i:9;s:3:"ุ";i:103;s:3:"ู";i:103;s:3:"ฺ";i:9;s:3:"่";i:107;s:3:"้";i:107;s:3:"๊";i:107;s:3:"๋";i:107;s:3:"ຸ";i:118;s:3:"ູ";i:118;s:3:"່";i:122;s:3:"້";i:122;s:3:"໊";i:122;s:3:"໋";i:122;s:3:"༘";i:220;s:3:"༙";i:220;s:3:"༵";i:220;s:3:"༷";i:220;s:3:"༹";i:216;s:3:"ཱ";i:129;s:3:"ི";i:130;s:3:"ུ";i:132;s:3:"ེ";i:130;s:3:"ཻ";i:130;s:3:"ོ";i:130;s:3:"ཽ";i:130;s:3:"ྀ";i:130;s:3:"ྂ";i:230;s:3:"ྃ";i:230;s:3:"྄";i:9;s:3:"྆";i:230;s:3:"྇";i:230;s:3:"࿆";i:220;s:3:"့";i:7;s:3:"္";i:9;s:3:"፟";i:230;s:3:"᜔";i:9;s:3:"᜴";i:9;s:3:"្";i:9;s:3:"៝";i:230;s:3:"ᢩ";i:228;s:3:"᤹";i:222;s:3:"᤺";i:230;s:3:"᤻";i:220;s:3:"ᨗ";i:230;s:3:"ᨘ";i:220;s:3:"᬴";i:7;s:3:"᭄";i:9;s:3:"᭫";i:230;s:3:"᭬";i:220;s:3:"᭭";i:230;s:3:"᭮";i:230;s:3:"᭯";i:230;s:3:"᭰";i:230;s:3:"᭱";i:230;s:3:"᭲";i:230;s:3:"᭳";i:230;s:3:"᷀";i:230;s:3:"᷁";i:230;s:3:"᷂";i:220;s:3:"᷃";i:230;s:3:"᷄";i:230;s:3:"᷅";i:230;s:3:"᷆";i:230;s:3:"᷇";i:230;s:3:"᷈";i:230;s:3:"᷉";i:230;s:3:"᷊";i:220;s:3:"᷾";i:230;s:3:"᷿";i:220;s:3:"⃐";i:230;s:3:"⃑";i:230;s:3:"⃒";i:1;s:3:"⃓";i:1;s:3:"⃔";i:230;s:3:"⃕";i:230;s:3:"⃖";i:230;s:3:"⃗";i:230;s:3:"⃘";i:1;s:3:"⃙";i:1;s:3:"⃚";i:1;s:3:"⃛";i:230;s:3:"⃜";i:230;s:3:"⃡";i:230;s:3:"⃥";i:1;s:3:"⃦";i:1;s:3:"⃧";i:230;s:3:"⃨";i:220;s:3:"⃩";i:230;s:3:"⃪";i:1;s:3:"⃫";i:1;s:3:"⃬";i:220;s:3:"⃭";i:220;s:3:"⃮";i:220;s:3:"⃯";i:220;s:3:"〪";i:218;s:3:"〫";i:228;s:3:"〬";i:232;s:3:"〭";i:222;s:3:"〮";i:224;s:3:"〯";i:224;s:3:"゙";i:8;s:3:"゚";i:8;s:3:"꠆";i:9;s:3:"ﬞ";i:26;s:3:"︠";i:230;s:3:"︡";i:230;s:3:"︢";i:230;s:3:"︣";i:230;s:4:"𐨍";i:220;s:4:"𐨏";i:230;s:4:"𐨸";i:230;s:4:"𐨹";i:1;s:4:"𐨺";i:220;s:4:"𐨿";i:9;s:4:"𝅥";i:216;s:4:"𝅦";i:216;s:4:"𝅧";i:1;s:4:"𝅨";i:1;s:4:"𝅩";i:1;s:4:"𝅭";i:226;s:4:"𝅮";i:216;s:4:"𝅯";i:216;s:4:"𝅰";i:216;s:4:"𝅱";i:216;s:4:"𝅲";i:216;s:4:"𝅻";i:220;s:4:"𝅼";i:220;s:4:"𝅽";i:220;s:4:"𝅾";i:220;s:4:"𝅿";i:220;s:4:"𝆀";i:220;s:4:"𝆁";i:220;s:4:"𝆂";i:220;s:4:"𝆅";i:230;s:4:"𝆆";i:230;s:4:"𝆇";i:230;s:4:"𝆈";i:230;s:4:"𝆉";i:230;s:4:"𝆊";i:220;s:4:"𝆋";i:220;s:4:"𝆪";i:230;s:4:"𝆫";i:230;s:4:"𝆬";i:230;s:4:"𝆭";i:230;s:4:"𝉂";i:230;s:4:"𝉃";i:230;s:4:"𝉄";i:230;}' );
+$utfCanonicalComp = unserialize( 'a:1862:{s:3:"À";s:2:"À";s:3:"Á";s:2:"Á";s:3:"Â";s:2:"Â";s:3:"Ã";s:2:"Ã";s:3:"Ä";s:2:"Ä";s:3:"Å";s:2:"Å";s:3:"Ç";s:2:"Ç";s:3:"È";s:2:"È";s:3:"É";s:2:"É";s:3:"Ê";s:2:"Ê";s:3:"Ë";s:2:"Ë";s:3:"Ì";s:2:"Ì";s:3:"Í";s:2:"Í";s:3:"Î";s:2:"Î";s:3:"Ï";s:2:"Ï";s:3:"Ñ";s:2:"Ñ";s:3:"Ò";s:2:"Ò";s:3:"Ó";s:2:"Ó";s:3:"Ô";s:2:"Ô";s:3:"Õ";s:2:"Õ";s:3:"Ö";s:2:"Ö";s:3:"Ù";s:2:"Ù";s:3:"Ú";s:2:"Ú";s:3:"Û";s:2:"Û";s:3:"Ü";s:2:"Ü";s:3:"Ý";s:2:"Ý";s:3:"à";s:2:"à";s:3:"á";s:2:"á";s:3:"â";s:2:"â";s:3:"ã";s:2:"ã";s:3:"ä";s:2:"ä";s:3:"å";s:2:"å";s:3:"ç";s:2:"ç";s:3:"è";s:2:"è";s:3:"é";s:2:"é";s:3:"ê";s:2:"ê";s:3:"ë";s:2:"ë";s:3:"ì";s:2:"ì";s:3:"í";s:2:"í";s:3:"î";s:2:"î";s:3:"ï";s:2:"ï";s:3:"ñ";s:2:"ñ";s:3:"ò";s:2:"ò";s:3:"ó";s:2:"ó";s:3:"ô";s:2:"ô";s:3:"õ";s:2:"õ";s:3:"ö";s:2:"ö";s:3:"ù";s:2:"ù";s:3:"ú";s:2:"ú";s:3:"û";s:2:"û";s:3:"ü";s:2:"ü";s:3:"ý";s:2:"ý";s:3:"ÿ";s:2:"ÿ";s:3:"Ā";s:2:"Ā";s:3:"ā";s:2:"ā";s:3:"Ă";s:2:"Ă";s:3:"ă";s:2:"ă";s:3:"Ą";s:2:"Ą";s:3:"ą";s:2:"ą";s:3:"Ć";s:2:"Ć";s:3:"ć";s:2:"ć";s:3:"Ĉ";s:2:"Ĉ";s:3:"ĉ";s:2:"ĉ";s:3:"Ċ";s:2:"Ċ";s:3:"ċ";s:2:"ċ";s:3:"Č";s:2:"Č";s:3:"č";s:2:"č";s:3:"Ď";s:2:"Ď";s:3:"ď";s:2:"ď";s:3:"Ē";s:2:"Ē";s:3:"ē";s:2:"ē";s:3:"Ĕ";s:2:"Ĕ";s:3:"ĕ";s:2:"ĕ";s:3:"Ė";s:2:"Ė";s:3:"ė";s:2:"ė";s:3:"Ę";s:2:"Ę";s:3:"ę";s:2:"ę";s:3:"Ě";s:2:"Ě";s:3:"ě";s:2:"ě";s:3:"Ĝ";s:2:"Ĝ";s:3:"ĝ";s:2:"ĝ";s:3:"Ğ";s:2:"Ğ";s:3:"ğ";s:2:"ğ";s:3:"Ġ";s:2:"Ġ";s:3:"ġ";s:2:"ġ";s:3:"Ģ";s:2:"Ģ";s:3:"ģ";s:2:"ģ";s:3:"Ĥ";s:2:"Ĥ";s:3:"ĥ";s:2:"ĥ";s:3:"Ĩ";s:2:"Ĩ";s:3:"ĩ";s:2:"ĩ";s:3:"Ī";s:2:"Ī";s:3:"ī";s:2:"ī";s:3:"Ĭ";s:2:"Ĭ";s:3:"ĭ";s:2:"ĭ";s:3:"Į";s:2:"Į";s:3:"į";s:2:"į";s:3:"İ";s:2:"İ";s:3:"Ĵ";s:2:"Ĵ";s:3:"ĵ";s:2:"ĵ";s:3:"Ķ";s:2:"Ķ";s:3:"ķ";s:2:"ķ";s:3:"Ĺ";s:2:"Ĺ";s:3:"ĺ";s:2:"ĺ";s:3:"Ļ";s:2:"Ļ";s:3:"ļ";s:2:"ļ";s:3:"Ľ";s:2:"Ľ";s:3:"ľ";s:2:"ľ";s:3:"Ń";s:2:"Ń";s:3:"ń";s:2:"ń";s:3:"Ņ";s:2:"Ņ";s:3:"ņ";s:2:"ņ";s:3:"Ň";s:2:"Ň";s:3:"ň";s:2:"ň";s:3:"Ō";s:2:"Ō";s:3:"ō";s:2:"ō";s:3:"Ŏ";s:2:"Ŏ";s:3:"ŏ";s:2:"ŏ";s:3:"Ő";s:2:"Ő";s:3:"ő";s:2:"ő";s:3:"Ŕ";s:2:"Ŕ";s:3:"ŕ";s:2:"ŕ";s:3:"Ŗ";s:2:"Ŗ";s:3:"ŗ";s:2:"ŗ";s:3:"Ř";s:2:"Ř";s:3:"ř";s:2:"ř";s:3:"Ś";s:2:"Ś";s:3:"ś";s:2:"ś";s:3:"Ŝ";s:2:"Ŝ";s:3:"ŝ";s:2:"ŝ";s:3:"Ş";s:2:"Ş";s:3:"ş";s:2:"ş";s:3:"Š";s:2:"Š";s:3:"š";s:2:"š";s:3:"Ţ";s:2:"Ţ";s:3:"ţ";s:2:"ţ";s:3:"Ť";s:2:"Ť";s:3:"ť";s:2:"ť";s:3:"Ũ";s:2:"Ũ";s:3:"ũ";s:2:"ũ";s:3:"Ū";s:2:"Ū";s:3:"ū";s:2:"ū";s:3:"Ŭ";s:2:"Ŭ";s:3:"ŭ";s:2:"ŭ";s:3:"Ů";s:2:"Ů";s:3:"ů";s:2:"ů";s:3:"Ű";s:2:"Ű";s:3:"ű";s:2:"ű";s:3:"Ų";s:2:"Ų";s:3:"ų";s:2:"ų";s:3:"Ŵ";s:2:"Ŵ";s:3:"ŵ";s:2:"ŵ";s:3:"Ŷ";s:2:"Ŷ";s:3:"ŷ";s:2:"ŷ";s:3:"Ÿ";s:2:"Ÿ";s:3:"Ź";s:2:"Ź";s:3:"ź";s:2:"ź";s:3:"Ż";s:2:"Ż";s:3:"ż";s:2:"ż";s:3:"Ž";s:2:"Ž";s:3:"ž";s:2:"ž";s:3:"Ơ";s:2:"Ơ";s:3:"ơ";s:2:"ơ";s:3:"Ư";s:2:"Ư";s:3:"ư";s:2:"ư";s:3:"Ǎ";s:2:"Ǎ";s:3:"ǎ";s:2:"ǎ";s:3:"Ǐ";s:2:"Ǐ";s:3:"ǐ";s:2:"ǐ";s:3:"Ǒ";s:2:"Ǒ";s:3:"ǒ";s:2:"ǒ";s:3:"Ǔ";s:2:"Ǔ";s:3:"ǔ";s:2:"ǔ";s:4:"Ǖ";s:2:"Ǖ";s:4:"ǖ";s:2:"ǖ";s:4:"Ǘ";s:2:"Ǘ";s:4:"ǘ";s:2:"ǘ";s:4:"Ǚ";s:2:"Ǚ";s:4:"ǚ";s:2:"ǚ";s:4:"Ǜ";s:2:"Ǜ";s:4:"ǜ";s:2:"ǜ";s:4:"Ǟ";s:2:"Ǟ";s:4:"ǟ";s:2:"ǟ";s:4:"Ǡ";s:2:"Ǡ";s:4:"ǡ";s:2:"ǡ";s:4:"Ǣ";s:2:"Ǣ";s:4:"ǣ";s:2:"ǣ";s:3:"Ǧ";s:2:"Ǧ";s:3:"ǧ";s:2:"ǧ";s:3:"Ǩ";s:2:"Ǩ";s:3:"ǩ";s:2:"ǩ";s:3:"Ǫ";s:2:"Ǫ";s:3:"ǫ";s:2:"ǫ";s:4:"Ǭ";s:2:"Ǭ";s:4:"ǭ";s:2:"ǭ";s:4:"Ǯ";s:2:"Ǯ";s:4:"ǯ";s:2:"ǯ";s:3:"ǰ";s:2:"ǰ";s:3:"Ǵ";s:2:"Ǵ";s:3:"ǵ";s:2:"ǵ";s:3:"Ǹ";s:2:"Ǹ";s:3:"ǹ";s:2:"ǹ";s:4:"Ǻ";s:2:"Ǻ";s:4:"ǻ";s:2:"ǻ";s:4:"Ǽ";s:2:"Ǽ";s:4:"ǽ";s:2:"ǽ";s:4:"Ǿ";s:2:"Ǿ";s:4:"ǿ";s:2:"ǿ";s:3:"Ȁ";s:2:"Ȁ";s:3:"ȁ";s:2:"ȁ";s:3:"Ȃ";s:2:"Ȃ";s:3:"ȃ";s:2:"ȃ";s:3:"Ȅ";s:2:"Ȅ";s:3:"ȅ";s:2:"ȅ";s:3:"Ȇ";s:2:"Ȇ";s:3:"ȇ";s:2:"ȇ";s:3:"Ȉ";s:2:"Ȉ";s:3:"ȉ";s:2:"ȉ";s:3:"Ȋ";s:2:"Ȋ";s:3:"ȋ";s:2:"ȋ";s:3:"Ȍ";s:2:"Ȍ";s:3:"ȍ";s:2:"ȍ";s:3:"Ȏ";s:2:"Ȏ";s:3:"ȏ";s:2:"ȏ";s:3:"Ȑ";s:2:"Ȑ";s:3:"ȑ";s:2:"ȑ";s:3:"Ȓ";s:2:"Ȓ";s:3:"ȓ";s:2:"ȓ";s:3:"Ȕ";s:2:"Ȕ";s:3:"ȕ";s:2:"ȕ";s:3:"Ȗ";s:2:"Ȗ";s:3:"ȗ";s:2:"ȗ";s:3:"Ș";s:2:"Ș";s:3:"ș";s:2:"ș";s:3:"Ț";s:2:"Ț";s:3:"ț";s:2:"ț";s:3:"Ȟ";s:2:"Ȟ";s:3:"ȟ";s:2:"ȟ";s:3:"Ȧ";s:2:"Ȧ";s:3:"ȧ";s:2:"ȧ";s:3:"Ȩ";s:2:"Ȩ";s:3:"ȩ";s:2:"ȩ";s:4:"Ȫ";s:2:"Ȫ";s:4:"ȫ";s:2:"ȫ";s:4:"Ȭ";s:2:"Ȭ";s:4:"ȭ";s:2:"ȭ";s:3:"Ȯ";s:2:"Ȯ";s:3:"ȯ";s:2:"ȯ";s:4:"Ȱ";s:2:"Ȱ";s:4:"ȱ";s:2:"ȱ";s:3:"Ȳ";s:2:"Ȳ";s:3:"ȳ";s:2:"ȳ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:4:"̈́";s:2:"̈́";s:2:"ʹ";s:2:"ʹ";s:1:";";s:2:";";s:4:"΅";s:2:"΅";s:4:"Ά";s:2:"Ά";s:2:"·";s:2:"·";s:4:"Έ";s:2:"Έ";s:4:"Ή";s:2:"Ή";s:4:"Ί";s:2:"Ί";s:4:"Ό";s:2:"Ό";s:4:"Ύ";s:2:"Ύ";s:4:"Ώ";s:2:"Ώ";s:4:"ΐ";s:2:"ΐ";s:4:"Ϊ";s:2:"Ϊ";s:4:"Ϋ";s:2:"Ϋ";s:4:"ά";s:2:"ά";s:4:"έ";s:2:"έ";s:4:"ή";s:2:"ή";s:4:"ί";s:2:"ί";s:4:"ΰ";s:2:"ΰ";s:4:"ϊ";s:2:"ϊ";s:4:"ϋ";s:2:"ϋ";s:4:"ό";s:2:"ό";s:4:"ύ";s:2:"ύ";s:4:"ώ";s:2:"ώ";s:4:"ϓ";s:2:"ϓ";s:4:"ϔ";s:2:"ϔ";s:4:"Ѐ";s:2:"Ѐ";s:4:"Ё";s:2:"Ё";s:4:"Ѓ";s:2:"Ѓ";s:4:"Ї";s:2:"Ї";s:4:"Ќ";s:2:"Ќ";s:4:"Ѝ";s:2:"Ѝ";s:4:"Ў";s:2:"Ў";s:4:"Й";s:2:"Й";s:4:"й";s:2:"й";s:4:"ѐ";s:2:"ѐ";s:4:"ё";s:2:"ё";s:4:"ѓ";s:2:"ѓ";s:4:"ї";s:2:"ї";s:4:"ќ";s:2:"ќ";s:4:"ѝ";s:2:"ѝ";s:4:"ў";s:2:"ў";s:4:"Ѷ";s:2:"Ѷ";s:4:"ѷ";s:2:"ѷ";s:4:"Ӂ";s:2:"Ӂ";s:4:"ӂ";s:2:"ӂ";s:4:"Ӑ";s:2:"Ӑ";s:4:"ӑ";s:2:"ӑ";s:4:"Ӓ";s:2:"Ӓ";s:4:"ӓ";s:2:"ӓ";s:4:"Ӗ";s:2:"Ӗ";s:4:"ӗ";s:2:"ӗ";s:4:"Ӛ";s:2:"Ӛ";s:4:"ӛ";s:2:"ӛ";s:4:"Ӝ";s:2:"Ӝ";s:4:"ӝ";s:2:"ӝ";s:4:"Ӟ";s:2:"Ӟ";s:4:"ӟ";s:2:"ӟ";s:4:"Ӣ";s:2:"Ӣ";s:4:"ӣ";s:2:"ӣ";s:4:"Ӥ";s:2:"Ӥ";s:4:"ӥ";s:2:"ӥ";s:4:"Ӧ";s:2:"Ӧ";s:4:"ӧ";s:2:"ӧ";s:4:"Ӫ";s:2:"Ӫ";s:4:"ӫ";s:2:"ӫ";s:4:"Ӭ";s:2:"Ӭ";s:4:"ӭ";s:2:"ӭ";s:4:"Ӯ";s:2:"Ӯ";s:4:"ӯ";s:2:"ӯ";s:4:"Ӱ";s:2:"Ӱ";s:4:"ӱ";s:2:"ӱ";s:4:"Ӳ";s:2:"Ӳ";s:4:"ӳ";s:2:"ӳ";s:4:"Ӵ";s:2:"Ӵ";s:4:"ӵ";s:2:"ӵ";s:4:"Ӹ";s:2:"Ӹ";s:4:"ӹ";s:2:"ӹ";s:4:"آ";s:2:"آ";s:4:"أ";s:2:"أ";s:4:"ؤ";s:2:"ؤ";s:4:"إ";s:2:"إ";s:4:"ئ";s:2:"ئ";s:4:"ۀ";s:2:"ۀ";s:4:"ۂ";s:2:"ۂ";s:4:"ۓ";s:2:"ۓ";s:6:"ऩ";s:3:"ऩ";s:6:"ऱ";s:3:"ऱ";s:6:"ऴ";s:3:"ऴ";s:6:"ো";s:3:"ো";s:6:"ৌ";s:3:"ৌ";s:6:"ୈ";s:3:"ୈ";s:6:"ୋ";s:3:"ୋ";s:6:"ୌ";s:3:"ୌ";s:6:"ஔ";s:3:"ஔ";s:6:"ொ";s:3:"ொ";s:6:"ோ";s:3:"ோ";s:6:"ௌ";s:3:"ௌ";s:6:"ై";s:3:"ై";s:6:"ೀ";s:3:"ೀ";s:6:"ೇ";s:3:"ೇ";s:6:"ೈ";s:3:"ೈ";s:6:"ೊ";s:3:"ೊ";s:6:"ೋ";s:3:"ೋ";s:6:"ൊ";s:3:"ൊ";s:6:"ോ";s:3:"ോ";s:6:"ൌ";s:3:"ൌ";s:6:"ේ";s:3:"ේ";s:6:"ො";s:3:"ො";s:6:"ෝ";s:3:"ෝ";s:6:"ෞ";s:3:"ෞ";s:6:"ཱི";s:3:"ཱི";s:6:"ཱུ";s:3:"ཱུ";s:6:"ཱྀ";s:3:"ཱྀ";s:6:"ဦ";s:3:"ဦ";s:6:"ᬆ";s:3:"ᬆ";s:6:"ᬈ";s:3:"ᬈ";s:6:"ᬊ";s:3:"ᬊ";s:6:"ᬌ";s:3:"ᬌ";s:6:"ᬎ";s:3:"ᬎ";s:6:"ᬒ";s:3:"ᬒ";s:6:"ᬻ";s:3:"ᬻ";s:6:"ᬽ";s:3:"ᬽ";s:6:"ᭀ";s:3:"ᭀ";s:6:"ᭁ";s:3:"ᭁ";s:6:"ᭃ";s:3:"ᭃ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:4:"Ḉ";s:3:"Ḉ";s:4:"ḉ";s:3:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:4:"Ḕ";s:3:"Ḕ";s:4:"ḕ";s:3:"ḕ";s:4:"Ḗ";s:3:"Ḗ";s:4:"ḗ";s:3:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:4:"Ḝ";s:3:"Ḝ";s:4:"ḝ";s:3:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:4:"Ḯ";s:3:"Ḯ";s:4:"ḯ";s:3:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:5:"Ḹ";s:3:"Ḹ";s:5:"ḹ";s:3:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:4:"Ṍ";s:3:"Ṍ";s:4:"ṍ";s:3:"ṍ";s:4:"Ṏ";s:3:"Ṏ";s:4:"ṏ";s:3:"ṏ";s:4:"Ṑ";s:3:"Ṑ";s:4:"ṑ";s:3:"ṑ";s:4:"Ṓ";s:3:"Ṓ";s:4:"ṓ";s:3:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:5:"Ṝ";s:3:"Ṝ";s:5:"ṝ";s:3:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:4:"Ṥ";s:3:"Ṥ";s:4:"ṥ";s:3:"ṥ";s:4:"Ṧ";s:3:"Ṧ";s:4:"ṧ";s:3:"ṧ";s:5:"Ṩ";s:3:"Ṩ";s:5:"ṩ";s:3:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:4:"Ṹ";s:3:"Ṹ";s:4:"ṹ";s:3:"ṹ";s:4:"Ṻ";s:3:"Ṻ";s:4:"ṻ";s:3:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:4:"ẛ";s:3:"ẛ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:4:"Ấ";s:3:"Ấ";s:4:"ấ";s:3:"ấ";s:4:"Ầ";s:3:"Ầ";s:4:"ầ";s:3:"ầ";s:4:"Ẩ";s:3:"Ẩ";s:4:"ẩ";s:3:"ẩ";s:4:"Ẫ";s:3:"Ẫ";s:4:"ẫ";s:3:"ẫ";s:5:"Ậ";s:3:"Ậ";s:5:"ậ";s:3:"ậ";s:4:"Ắ";s:3:"Ắ";s:4:"ắ";s:3:"ắ";s:4:"Ằ";s:3:"Ằ";s:4:"ằ";s:3:"ằ";s:4:"Ẳ";s:3:"Ẳ";s:4:"ẳ";s:3:"ẳ";s:4:"Ẵ";s:3:"Ẵ";s:4:"ẵ";s:3:"ẵ";s:5:"Ặ";s:3:"Ặ";s:5:"ặ";s:3:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:4:"Ế";s:3:"Ế";s:4:"ế";s:3:"ế";s:4:"Ề";s:3:"Ề";s:4:"ề";s:3:"ề";s:4:"Ể";s:3:"Ể";s:4:"ể";s:3:"ể";s:4:"Ễ";s:3:"Ễ";s:4:"ễ";s:3:"ễ";s:5:"Ệ";s:3:"Ệ";s:5:"ệ";s:3:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:4:"Ố";s:3:"Ố";s:4:"ố";s:3:"ố";s:4:"Ồ";s:3:"Ồ";s:4:"ồ";s:3:"ồ";s:4:"Ổ";s:3:"Ổ";s:4:"ổ";s:3:"ổ";s:4:"Ỗ";s:3:"Ỗ";s:4:"ỗ";s:3:"ỗ";s:5:"Ộ";s:3:"Ộ";s:5:"ộ";s:3:"ộ";s:4:"Ớ";s:3:"Ớ";s:4:"ớ";s:3:"ớ";s:4:"Ờ";s:3:"Ờ";s:4:"ờ";s:3:"ờ";s:4:"Ở";s:3:"Ở";s:4:"ở";s:3:"ở";s:4:"Ỡ";s:3:"Ỡ";s:4:"ỡ";s:3:"ỡ";s:4:"Ợ";s:3:"Ợ";s:4:"ợ";s:3:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:4:"Ứ";s:3:"Ứ";s:4:"ứ";s:3:"ứ";s:4:"Ừ";s:3:"Ừ";s:4:"ừ";s:3:"ừ";s:4:"Ử";s:3:"Ử";s:4:"ử";s:3:"ử";s:4:"Ữ";s:3:"Ữ";s:4:"ữ";s:3:"ữ";s:4:"Ự";s:3:"Ự";s:4:"ự";s:3:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:4:"ἀ";s:3:"ἀ";s:4:"ἁ";s:3:"ἁ";s:5:"ἂ";s:3:"ἂ";s:5:"ἃ";s:3:"ἃ";s:5:"ἄ";s:3:"ἄ";s:5:"ἅ";s:3:"ἅ";s:5:"ἆ";s:3:"ἆ";s:5:"ἇ";s:3:"ἇ";s:4:"Ἀ";s:3:"Ἀ";s:4:"Ἁ";s:3:"Ἁ";s:5:"Ἂ";s:3:"Ἂ";s:5:"Ἃ";s:3:"Ἃ";s:5:"Ἄ";s:3:"Ἄ";s:5:"Ἅ";s:3:"Ἅ";s:5:"Ἆ";s:3:"Ἆ";s:5:"Ἇ";s:3:"Ἇ";s:4:"ἐ";s:3:"ἐ";s:4:"ἑ";s:3:"ἑ";s:5:"ἒ";s:3:"ἒ";s:5:"ἓ";s:3:"ἓ";s:5:"ἔ";s:3:"ἔ";s:5:"ἕ";s:3:"ἕ";s:4:"Ἐ";s:3:"Ἐ";s:4:"Ἑ";s:3:"Ἑ";s:5:"Ἒ";s:3:"Ἒ";s:5:"Ἓ";s:3:"Ἓ";s:5:"Ἔ";s:3:"Ἔ";s:5:"Ἕ";s:3:"Ἕ";s:4:"ἠ";s:3:"ἠ";s:4:"ἡ";s:3:"ἡ";s:5:"ἢ";s:3:"ἢ";s:5:"ἣ";s:3:"ἣ";s:5:"ἤ";s:3:"ἤ";s:5:"ἥ";s:3:"ἥ";s:5:"ἦ";s:3:"ἦ";s:5:"ἧ";s:3:"ἧ";s:4:"Ἠ";s:3:"Ἠ";s:4:"Ἡ";s:3:"Ἡ";s:5:"Ἢ";s:3:"Ἢ";s:5:"Ἣ";s:3:"Ἣ";s:5:"Ἤ";s:3:"Ἤ";s:5:"Ἥ";s:3:"Ἥ";s:5:"Ἦ";s:3:"Ἦ";s:5:"Ἧ";s:3:"Ἧ";s:4:"ἰ";s:3:"ἰ";s:4:"ἱ";s:3:"ἱ";s:5:"ἲ";s:3:"ἲ";s:5:"ἳ";s:3:"ἳ";s:5:"ἴ";s:3:"ἴ";s:5:"ἵ";s:3:"ἵ";s:5:"ἶ";s:3:"ἶ";s:5:"ἷ";s:3:"ἷ";s:4:"Ἰ";s:3:"Ἰ";s:4:"Ἱ";s:3:"Ἱ";s:5:"Ἲ";s:3:"Ἲ";s:5:"Ἳ";s:3:"Ἳ";s:5:"Ἴ";s:3:"Ἴ";s:5:"Ἵ";s:3:"Ἵ";s:5:"Ἶ";s:3:"Ἶ";s:5:"Ἷ";s:3:"Ἷ";s:4:"ὀ";s:3:"ὀ";s:4:"ὁ";s:3:"ὁ";s:5:"ὂ";s:3:"ὂ";s:5:"ὃ";s:3:"ὃ";s:5:"ὄ";s:3:"ὄ";s:5:"ὅ";s:3:"ὅ";s:4:"Ὀ";s:3:"Ὀ";s:4:"Ὁ";s:3:"Ὁ";s:5:"Ὂ";s:3:"Ὂ";s:5:"Ὃ";s:3:"Ὃ";s:5:"Ὄ";s:3:"Ὄ";s:5:"Ὅ";s:3:"Ὅ";s:4:"ὐ";s:3:"ὐ";s:4:"ὑ";s:3:"ὑ";s:5:"ὒ";s:3:"ὒ";s:5:"ὓ";s:3:"ὓ";s:5:"ὔ";s:3:"ὔ";s:5:"ὕ";s:3:"ὕ";s:5:"ὖ";s:3:"ὖ";s:5:"ὗ";s:3:"ὗ";s:4:"Ὑ";s:3:"Ὑ";s:5:"Ὓ";s:3:"Ὓ";s:5:"Ὕ";s:3:"Ὕ";s:5:"Ὗ";s:3:"Ὗ";s:4:"ὠ";s:3:"ὠ";s:4:"ὡ";s:3:"ὡ";s:5:"ὢ";s:3:"ὢ";s:5:"ὣ";s:3:"ὣ";s:5:"ὤ";s:3:"ὤ";s:5:"ὥ";s:3:"ὥ";s:5:"ὦ";s:3:"ὦ";s:5:"ὧ";s:3:"ὧ";s:4:"Ὠ";s:3:"Ὠ";s:4:"Ὡ";s:3:"Ὡ";s:5:"Ὢ";s:3:"Ὢ";s:5:"Ὣ";s:3:"Ὣ";s:5:"Ὤ";s:3:"Ὤ";s:5:"Ὥ";s:3:"Ὥ";s:5:"Ὦ";s:3:"Ὦ";s:5:"Ὧ";s:3:"Ὧ";s:4:"ὰ";s:3:"ὰ";s:2:"ά";s:3:"ά";s:4:"ὲ";s:3:"ὲ";s:2:"έ";s:3:"έ";s:4:"ὴ";s:3:"ὴ";s:2:"ή";s:3:"ή";s:4:"ὶ";s:3:"ὶ";s:2:"ί";s:3:"ί";s:4:"ὸ";s:3:"ὸ";s:2:"ό";s:3:"ό";s:4:"ὺ";s:3:"ὺ";s:2:"ύ";s:3:"ύ";s:4:"ὼ";s:3:"ὼ";s:2:"ώ";s:3:"ώ";s:5:"ᾀ";s:3:"ᾀ";s:5:"ᾁ";s:3:"ᾁ";s:5:"ᾂ";s:3:"ᾂ";s:5:"ᾃ";s:3:"ᾃ";s:5:"ᾄ";s:3:"ᾄ";s:5:"ᾅ";s:3:"ᾅ";s:5:"ᾆ";s:3:"ᾆ";s:5:"ᾇ";s:3:"ᾇ";s:5:"ᾈ";s:3:"ᾈ";s:5:"ᾉ";s:3:"ᾉ";s:5:"ᾊ";s:3:"ᾊ";s:5:"ᾋ";s:3:"ᾋ";s:5:"ᾌ";s:3:"ᾌ";s:5:"ᾍ";s:3:"ᾍ";s:5:"ᾎ";s:3:"ᾎ";s:5:"ᾏ";s:3:"ᾏ";s:5:"ᾐ";s:3:"ᾐ";s:5:"ᾑ";s:3:"ᾑ";s:5:"ᾒ";s:3:"ᾒ";s:5:"ᾓ";s:3:"ᾓ";s:5:"ᾔ";s:3:"ᾔ";s:5:"ᾕ";s:3:"ᾕ";s:5:"ᾖ";s:3:"ᾖ";s:5:"ᾗ";s:3:"ᾗ";s:5:"ᾘ";s:3:"ᾘ";s:5:"ᾙ";s:3:"ᾙ";s:5:"ᾚ";s:3:"ᾚ";s:5:"ᾛ";s:3:"ᾛ";s:5:"ᾜ";s:3:"ᾜ";s:5:"ᾝ";s:3:"ᾝ";s:5:"ᾞ";s:3:"ᾞ";s:5:"ᾟ";s:3:"ᾟ";s:5:"ᾠ";s:3:"ᾠ";s:5:"ᾡ";s:3:"ᾡ";s:5:"ᾢ";s:3:"ᾢ";s:5:"ᾣ";s:3:"ᾣ";s:5:"ᾤ";s:3:"ᾤ";s:5:"ᾥ";s:3:"ᾥ";s:5:"ᾦ";s:3:"ᾦ";s:5:"ᾧ";s:3:"ᾧ";s:5:"ᾨ";s:3:"ᾨ";s:5:"ᾩ";s:3:"ᾩ";s:5:"ᾪ";s:3:"ᾪ";s:5:"ᾫ";s:3:"ᾫ";s:5:"ᾬ";s:3:"ᾬ";s:5:"ᾭ";s:3:"ᾭ";s:5:"ᾮ";s:3:"ᾮ";s:5:"ᾯ";s:3:"ᾯ";s:4:"ᾰ";s:3:"ᾰ";s:4:"ᾱ";s:3:"ᾱ";s:5:"ᾲ";s:3:"ᾲ";s:4:"ᾳ";s:3:"ᾳ";s:4:"ᾴ";s:3:"ᾴ";s:4:"ᾶ";s:3:"ᾶ";s:5:"ᾷ";s:3:"ᾷ";s:4:"Ᾰ";s:3:"Ᾰ";s:4:"Ᾱ";s:3:"Ᾱ";s:4:"Ὰ";s:3:"Ὰ";s:2:"Ά";s:3:"Ά";s:4:"ᾼ";s:3:"ᾼ";s:2:"ι";s:3:"ι";s:4:"῁";s:3:"῁";s:5:"ῂ";s:3:"ῂ";s:4:"ῃ";s:3:"ῃ";s:4:"ῄ";s:3:"ῄ";s:4:"ῆ";s:3:"ῆ";s:5:"ῇ";s:3:"ῇ";s:4:"Ὲ";s:3:"Ὲ";s:2:"Έ";s:3:"Έ";s:4:"Ὴ";s:3:"Ὴ";s:2:"Ή";s:3:"Ή";s:4:"ῌ";s:3:"ῌ";s:5:"῍";s:3:"῍";s:5:"῎";s:3:"῎";s:5:"῏";s:3:"῏";s:4:"ῐ";s:3:"ῐ";s:4:"ῑ";s:3:"ῑ";s:4:"ῒ";s:3:"ῒ";s:2:"ΐ";s:3:"ΐ";s:4:"ῖ";s:3:"ῖ";s:4:"ῗ";s:3:"ῗ";s:4:"Ῐ";s:3:"Ῐ";s:4:"Ῑ";s:3:"Ῑ";s:4:"Ὶ";s:3:"Ὶ";s:2:"Ί";s:3:"Ί";s:5:"῝";s:3:"῝";s:5:"῞";s:3:"῞";s:5:"῟";s:3:"῟";s:4:"ῠ";s:3:"ῠ";s:4:"ῡ";s:3:"ῡ";s:4:"ῢ";s:3:"ῢ";s:2:"ΰ";s:3:"ΰ";s:4:"ῤ";s:3:"ῤ";s:4:"ῥ";s:3:"ῥ";s:4:"ῦ";s:3:"ῦ";s:4:"ῧ";s:3:"ῧ";s:4:"Ῠ";s:3:"Ῠ";s:4:"Ῡ";s:3:"Ῡ";s:4:"Ὺ";s:3:"Ὺ";s:2:"Ύ";s:3:"Ύ";s:4:"Ῥ";s:3:"Ῥ";s:4:"῭";s:3:"῭";s:2:"΅";s:3:"΅";s:1:"`";s:3:"`";s:5:"ῲ";s:3:"ῲ";s:4:"ῳ";s:3:"ῳ";s:4:"ῴ";s:3:"ῴ";s:4:"ῶ";s:3:"ῶ";s:5:"ῷ";s:3:"ῷ";s:4:"Ὸ";s:3:"Ὸ";s:2:"Ό";s:3:"Ό";s:4:"Ὼ";s:3:"Ὼ";s:2:"Ώ";s:3:"Ώ";s:4:"ῼ";s:3:"ῼ";s:2:"´";s:3:"´";s:3:" ";s:3:" ";s:3:" ";s:3:" ";s:2:"Ω";s:3:"Ω";s:1:"K";s:3:"K";s:2:"Å";s:3:"Å";s:5:"↚";s:3:"↚";s:5:"↛";s:3:"↛";s:5:"↮";s:3:"↮";s:5:"⇍";s:3:"⇍";s:5:"⇎";s:3:"⇎";s:5:"⇏";s:3:"⇏";s:5:"∄";s:3:"∄";s:5:"∉";s:3:"∉";s:5:"∌";s:3:"∌";s:5:"∤";s:3:"∤";s:5:"∦";s:3:"∦";s:5:"≁";s:3:"≁";s:5:"≄";s:3:"≄";s:5:"≇";s:3:"≇";s:5:"≉";s:3:"≉";s:3:"≠";s:3:"≠";s:5:"≢";s:3:"≢";s:5:"≭";s:3:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:5:"≰";s:3:"≰";s:5:"≱";s:3:"≱";s:5:"≴";s:3:"≴";s:5:"≵";s:3:"≵";s:5:"≸";s:3:"≸";s:5:"≹";s:3:"≹";s:5:"⊀";s:3:"⊀";s:5:"⊁";s:3:"⊁";s:5:"⊄";s:3:"⊄";s:5:"⊅";s:3:"⊅";s:5:"⊈";s:3:"⊈";s:5:"⊉";s:3:"⊉";s:5:"⊬";s:3:"⊬";s:5:"⊭";s:3:"⊭";s:5:"⊮";s:3:"⊮";s:5:"⊯";s:3:"⊯";s:5:"⋠";s:3:"⋠";s:5:"⋡";s:3:"⋡";s:5:"⋢";s:3:"⋢";s:5:"⋣";s:3:"⋣";s:5:"⋪";s:3:"⋪";s:5:"⋫";s:3:"⋫";s:5:"⋬";s:3:"⋬";s:5:"⋭";s:3:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:6:"が";s:3:"が";s:6:"ぎ";s:3:"ぎ";s:6:"ぐ";s:3:"ぐ";s:6:"げ";s:3:"げ";s:6:"ご";s:3:"ご";s:6:"ざ";s:3:"ざ";s:6:"じ";s:3:"じ";s:6:"ず";s:3:"ず";s:6:"ぜ";s:3:"ぜ";s:6:"ぞ";s:3:"ぞ";s:6:"だ";s:3:"だ";s:6:"ぢ";s:3:"ぢ";s:6:"づ";s:3:"づ";s:6:"で";s:3:"で";s:6:"ど";s:3:"ど";s:6:"ば";s:3:"ば";s:6:"ぱ";s:3:"ぱ";s:6:"び";s:3:"び";s:6:"ぴ";s:3:"ぴ";s:6:"ぶ";s:3:"ぶ";s:6:"ぷ";s:3:"ぷ";s:6:"べ";s:3:"べ";s:6:"ぺ";s:3:"ぺ";s:6:"ぼ";s:3:"ぼ";s:6:"ぽ";s:3:"ぽ";s:6:"ゔ";s:3:"ゔ";s:6:"ゞ";s:3:"ゞ";s:6:"ガ";s:3:"ガ";s:6:"ギ";s:3:"ギ";s:6:"グ";s:3:"グ";s:6:"ゲ";s:3:"ゲ";s:6:"ゴ";s:3:"ゴ";s:6:"ザ";s:3:"ザ";s:6:"ジ";s:3:"ジ";s:6:"ズ";s:3:"ズ";s:6:"ゼ";s:3:"ゼ";s:6:"ゾ";s:3:"ゾ";s:6:"ダ";s:3:"ダ";s:6:"ヂ";s:3:"ヂ";s:6:"ヅ";s:3:"ヅ";s:6:"デ";s:3:"デ";s:6:"ド";s:3:"ド";s:6:"バ";s:3:"バ";s:6:"パ";s:3:"パ";s:6:"ビ";s:3:"ビ";s:6:"ピ";s:3:"ピ";s:6:"ブ";s:3:"ブ";s:6:"プ";s:3:"プ";s:6:"ベ";s:3:"ベ";s:6:"ペ";s:3:"ペ";s:6:"ボ";s:3:"ボ";s:6:"ポ";s:3:"ポ";s:6:"ヴ";s:3:"ヴ";s:6:"ヷ";s:3:"ヷ";s:6:"ヸ";s:3:"ヸ";s:6:"ヹ";s:3:"ヹ";s:6:"ヺ";s:3:"ヺ";s:6:"ヾ";s:3:"ヾ";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:4:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:4:"廊";s:3:"朗";s:4:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:4:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:4:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:4:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:4:"異";s:3:"北";s:4:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:4:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:4:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:4:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:4:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:4:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:4:"侮";s:3:"僧";s:4:"僧";s:3:"免";s:4:"免";s:3:"勉";s:4:"勉";s:3:"勤";s:4:"勤";s:3:"卑";s:4:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:4:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:4:"屮";s:3:"悔";s:4:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:4:"憎";s:3:"懲";s:4:"懲";s:3:"敏";s:4:"敏";s:3:"既";s:3:"既";s:3:"暑";s:4:"暑";s:3:"梅";s:4:"梅";s:3:"海";s:4:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:4:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:4:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:4:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"著";s:4:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:4:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:4:"勇";s:3:"勺";s:4:"勺";s:3:"啕";s:3:"啕";s:3:"喙";s:4:"喙";s:3:"嗢";s:3:"嗢";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:4:"慎";s:3:"愈";s:3:"愈";s:3:"慠";s:3:"慠";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"望";s:4:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"滛";s:3:"滛";s:3:"滋";s:4:"滋";s:3:"瀞";s:4:"瀞";s:3:"瞧";s:3:"瞧";s:3:"爵";s:4:"爵";s:3:"犯";s:3:"犯";s:3:"瑱";s:4:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"盛";s:3:"盛";s:3:"直";s:4:"直";s:3:"睊";s:4:"睊";s:3:"着";s:3:"着";s:3:"磌";s:4:"磌";s:3:"窱";s:3:"窱";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"缾";s:3:"缾";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:4:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"調";s:3:"調";s:3:"請";s:3:"請";s:3:"諭";s:4:"諭";s:3:"變";s:4:"變";s:3:"輸";s:4:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"韛";s:3:"韛";s:3:"頋";s:4:"頋";s:3:"鬒";s:4:"鬒";s:4:"𢡊";s:3:"𢡊";s:4:"𢡄";s:3:"𢡄";s:4:"𣏕";s:3:"𣏕";s:3:"㮝";s:4:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:4:"䀹";s:4:"𥉉";s:3:"𥉉";s:4:"𥳐";s:3:"𥳐";s:4:"𧻓";s:3:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"丽";s:4:"丽";s:3:"丸";s:4:"丸";s:3:"乁";s:4:"乁";s:4:"𠄢";s:4:"𠄢";s:3:"你";s:4:"你";s:3:"侻";s:4:"侻";s:3:"倂";s:4:"倂";s:3:"偺";s:4:"偺";s:3:"備";s:4:"備";s:3:"像";s:4:"像";s:3:"㒞";s:4:"㒞";s:4:"𠘺";s:4:"𠘺";s:3:"兔";s:4:"兔";s:3:"兤";s:4:"兤";s:3:"具";s:4:"具";s:4:"𠔜";s:4:"𠔜";s:3:"㒹";s:4:"㒹";s:3:"內";s:4:"內";s:3:"再";s:4:"再";s:4:"𠕋";s:4:"𠕋";s:3:"冗";s:4:"冗";s:3:"冤";s:4:"冤";s:3:"仌";s:4:"仌";s:3:"冬";s:4:"冬";s:4:"𩇟";s:4:"𩇟";s:3:"凵";s:4:"凵";s:3:"刃";s:4:"刃";s:3:"㓟";s:4:"㓟";s:3:"刻";s:4:"刻";s:3:"剆";s:4:"剆";s:3:"割";s:4:"割";s:3:"剷";s:4:"剷";s:3:"㔕";s:4:"㔕";s:3:"包";s:4:"包";s:3:"匆";s:4:"匆";s:3:"卉";s:4:"卉";s:3:"博";s:4:"博";s:3:"即";s:4:"即";s:3:"卽";s:4:"卽";s:3:"卿";s:4:"卿";s:4:"𠨬";s:4:"𠨬";s:3:"灰";s:4:"灰";s:3:"及";s:4:"及";s:3:"叟";s:4:"叟";s:4:"𠭣";s:4:"𠭣";s:3:"叫";s:4:"叫";s:3:"叱";s:4:"叱";s:3:"吆";s:4:"吆";s:3:"咞";s:4:"咞";s:3:"吸";s:4:"吸";s:3:"呈";s:4:"呈";s:3:"周";s:4:"周";s:3:"咢";s:4:"咢";s:3:"哶";s:4:"哶";s:3:"唐";s:4:"唐";s:3:"啓";s:4:"啓";s:3:"啣";s:4:"啣";s:3:"善";s:4:"善";s:3:"喫";s:4:"喫";s:3:"喳";s:4:"喳";s:3:"嗂";s:4:"嗂";s:3:"圖";s:4:"圖";s:3:"圗";s:4:"圗";s:3:"噑";s:4:"噑";s:3:"噴";s:4:"噴";s:3:"壮";s:4:"壮";s:3:"城";s:4:"城";s:3:"埴";s:4:"埴";s:3:"堍";s:4:"堍";s:3:"型";s:4:"型";s:3:"堲";s:4:"堲";s:3:"報";s:4:"報";s:3:"墬";s:4:"墬";s:4:"𡓤";s:4:"𡓤";s:3:"売";s:4:"売";s:3:"壷";s:4:"壷";s:3:"夆";s:4:"夆";s:3:"多";s:4:"多";s:3:"夢";s:4:"夢";s:3:"奢";s:4:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:3:"姬";s:4:"姬";s:3:"娛";s:4:"娛";s:3:"娧";s:4:"娧";s:3:"姘";s:4:"姘";s:3:"婦";s:4:"婦";s:3:"㛮";s:4:"㛮";s:3:"㛼";s:4:"㛼";s:3:"嬈";s:4:"嬈";s:3:"嬾";s:4:"嬾";s:4:"𡧈";s:4:"𡧈";s:3:"寃";s:4:"寃";s:3:"寘";s:4:"寘";s:3:"寳";s:4:"寳";s:4:"𡬘";s:4:"𡬘";s:3:"寿";s:4:"寿";s:3:"将";s:4:"将";s:3:"当";s:4:"当";s:3:"尢";s:4:"尢";s:3:"㞁";s:4:"㞁";s:3:"屠";s:4:"屠";s:3:"峀";s:4:"峀";s:3:"岍";s:4:"岍";s:4:"𡷤";s:4:"𡷤";s:3:"嵃";s:4:"嵃";s:4:"𡷦";s:4:"𡷦";s:3:"嵮";s:4:"嵮";s:3:"嵫";s:4:"嵫";s:3:"嵼";s:4:"嵼";s:3:"巡";s:4:"巡";s:3:"巢";s:4:"巢";s:3:"㠯";s:4:"㠯";s:3:"巽";s:4:"巽";s:3:"帨";s:4:"帨";s:3:"帽";s:4:"帽";s:3:"幩";s:4:"幩";s:3:"㡢";s:4:"㡢";s:4:"𢆃";s:4:"𢆃";s:3:"㡼";s:4:"㡼";s:3:"庰";s:4:"庰";s:3:"庳";s:4:"庳";s:3:"庶";s:4:"庶";s:4:"𪎒";s:4:"𪎒";s:3:"廾";s:4:"廾";s:4:"𢌱";s:4:"𢌱";s:3:"舁";s:4:"舁";s:3:"弢";s:4:"弢";s:3:"㣇";s:4:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:3:"形";s:4:"形";s:3:"彫";s:4:"彫";s:3:"㣣";s:4:"㣣";s:3:"徚";s:4:"徚";s:3:"忍";s:4:"忍";s:3:"志";s:4:"志";s:3:"忹";s:4:"忹";s:3:"悁";s:4:"悁";s:3:"㤺";s:4:"㤺";s:3:"㤜";s:4:"㤜";s:4:"𢛔";s:4:"𢛔";s:3:"惇";s:4:"惇";s:3:"慈";s:4:"慈";s:3:"慌";s:4:"慌";s:3:"慺";s:4:"慺";s:3:"憲";s:4:"憲";s:3:"憤";s:4:"憤";s:3:"憯";s:4:"憯";s:3:"懞";s:4:"懞";s:3:"成";s:4:"成";s:3:"戛";s:4:"戛";s:3:"扝";s:4:"扝";s:3:"抱";s:4:"抱";s:3:"拔";s:4:"拔";s:3:"捐";s:4:"捐";s:4:"𢬌";s:4:"𢬌";s:3:"挽";s:4:"挽";s:3:"拼";s:4:"拼";s:3:"捨";s:4:"捨";s:3:"掃";s:4:"掃";s:3:"揤";s:4:"揤";s:4:"𢯱";s:4:"𢯱";s:3:"搢";s:4:"搢";s:3:"揅";s:4:"揅";s:3:"掩";s:4:"掩";s:3:"㨮";s:4:"㨮";s:3:"摩";s:4:"摩";s:3:"摾";s:4:"摾";s:3:"撝";s:4:"撝";s:3:"摷";s:4:"摷";s:3:"㩬";s:4:"㩬";s:3:"敬";s:4:"敬";s:4:"𣀊";s:4:"𣀊";s:3:"旣";s:4:"旣";s:3:"書";s:4:"書";s:3:"晉";s:4:"晉";s:3:"㬙";s:4:"㬙";s:3:"㬈";s:4:"㬈";s:3:"㫤";s:4:"㫤";s:3:"冒";s:4:"冒";s:3:"冕";s:4:"冕";s:3:"最";s:4:"最";s:3:"暜";s:4:"暜";s:3:"肭";s:4:"肭";s:3:"䏙";s:4:"䏙";s:3:"朡";s:4:"朡";s:3:"杞";s:4:"杞";s:3:"杓";s:4:"杓";s:4:"𣏃";s:4:"𣏃";s:3:"㭉";s:4:"㭉";s:3:"柺";s:4:"柺";s:3:"枅";s:4:"枅";s:3:"桒";s:4:"桒";s:4:"𣑭";s:4:"𣑭";s:3:"梎";s:4:"梎";s:3:"栟";s:4:"栟";s:3:"椔";s:4:"椔";s:3:"楂";s:4:"楂";s:3:"榣";s:4:"榣";s:3:"槪";s:4:"槪";s:3:"檨";s:4:"檨";s:4:"𣚣";s:4:"𣚣";s:3:"櫛";s:4:"櫛";s:3:"㰘";s:4:"㰘";s:3:"次";s:4:"次";s:4:"𣢧";s:4:"𣢧";s:3:"歔";s:4:"歔";s:3:"㱎";s:4:"㱎";s:3:"歲";s:4:"歲";s:3:"殟";s:4:"殟";s:3:"殻";s:4:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:3:"汎";s:4:"汎";s:4:"𣲼";s:4:"𣲼";s:3:"沿";s:4:"沿";s:3:"泍";s:4:"泍";s:3:"汧";s:4:"汧";s:3:"洖";s:4:"洖";s:3:"派";s:4:"派";s:3:"浩";s:4:"浩";s:3:"浸";s:4:"浸";s:3:"涅";s:4:"涅";s:4:"𣴞";s:4:"𣴞";s:3:"洴";s:4:"洴";s:3:"港";s:4:"港";s:3:"湮";s:4:"湮";s:3:"㴳";s:4:"㴳";s:3:"滇";s:4:"滇";s:4:"𣻑";s:4:"𣻑";s:3:"淹";s:4:"淹";s:3:"潮";s:4:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:3:"濆";s:4:"濆";s:3:"瀹";s:4:"瀹";s:3:"瀛";s:4:"瀛";s:3:"㶖";s:4:"㶖";s:3:"灊";s:4:"灊";s:3:"災";s:4:"災";s:3:"灷";s:4:"灷";s:3:"炭";s:4:"炭";s:4:"𠔥";s:4:"𠔥";s:3:"煅";s:4:"煅";s:4:"𤉣";s:4:"𤉣";s:3:"熜";s:4:"熜";s:4:"𤎫";s:4:"𤎫";s:3:"爨";s:4:"爨";s:3:"牐";s:4:"牐";s:4:"𤘈";s:4:"𤘈";s:3:"犀";s:4:"犀";s:3:"犕";s:4:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:3:"獺";s:4:"獺";s:3:"王";s:4:"王";s:3:"㺬";s:4:"㺬";s:3:"玥";s:4:"玥";s:3:"㺸";s:4:"㺸";s:3:"瑇";s:4:"瑇";s:3:"瑜";s:4:"瑜";s:3:"璅";s:4:"璅";s:3:"瓊";s:4:"瓊";s:3:"㼛";s:4:"㼛";s:3:"甤";s:4:"甤";s:4:"𤰶";s:4:"𤰶";s:3:"甾";s:4:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"𢆟";s:4:"𢆟";s:3:"瘐";s:4:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:3:"㿼";s:4:"㿼";s:3:"䀈";s:4:"䀈";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:3:"眞";s:4:"眞";s:3:"真";s:4:"真";s:3:"瞋";s:4:"瞋";s:3:"䁆";s:4:"䁆";s:3:"䂖";s:4:"䂖";s:4:"𥐝";s:4:"𥐝";s:3:"硎";s:4:"硎";s:3:"䃣";s:4:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:3:"秫";s:4:"秫";s:3:"䄯";s:4:"䄯";s:3:"穊";s:4:"穊";s:3:"穏";s:4:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:3:"竮";s:4:"竮";s:3:"䈂";s:4:"䈂";s:4:"𥮫";s:4:"𥮫";s:3:"篆";s:4:"篆";s:3:"築";s:4:"築";s:3:"䈧";s:4:"䈧";s:4:"𥲀";s:4:"𥲀";s:3:"糒";s:4:"糒";s:3:"䊠";s:4:"䊠";s:3:"糨";s:4:"糨";s:3:"糣";s:4:"糣";s:3:"紀";s:4:"紀";s:4:"𥾆";s:4:"𥾆";s:3:"絣";s:4:"絣";s:3:"䌁";s:4:"䌁";s:3:"緇";s:4:"緇";s:3:"縂";s:4:"縂";s:3:"繅";s:4:"繅";s:3:"䌴";s:4:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:3:"䍙";s:4:"䍙";s:4:"𦋙";s:4:"𦋙";s:3:"罺";s:4:"罺";s:4:"𦌾";s:4:"𦌾";s:3:"羕";s:4:"羕";s:3:"翺";s:4:"翺";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:3:"聠";s:4:"聠";s:4:"𦖨";s:4:"𦖨";s:3:"聰";s:4:"聰";s:4:"𣍟";s:4:"𣍟";s:3:"䏕";s:4:"䏕";s:3:"育";s:4:"育";s:3:"脃";s:4:"脃";s:3:"䐋";s:4:"䐋";s:3:"脾";s:4:"脾";s:3:"媵";s:4:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:3:"舄";s:4:"舄";s:3:"辞";s:4:"辞";s:3:"䑫";s:4:"䑫";s:3:"芑";s:4:"芑";s:3:"芋";s:4:"芋";s:3:"芝";s:4:"芝";s:3:"劳";s:4:"劳";s:3:"花";s:4:"花";s:3:"芳";s:4:"芳";s:3:"芽";s:4:"芽";s:3:"苦";s:4:"苦";s:4:"𦬼";s:4:"𦬼";s:3:"茝";s:4:"茝";s:3:"荣";s:4:"荣";s:3:"莭";s:4:"莭";s:3:"茣";s:4:"茣";s:3:"莽";s:4:"莽";s:3:"菧";s:4:"菧";s:3:"荓";s:4:"荓";s:3:"菊";s:4:"菊";s:3:"菌";s:4:"菌";s:3:"菜";s:4:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:3:"䔫";s:4:"䔫";s:3:"蓱";s:4:"蓱";s:3:"蓳";s:4:"蓳";s:3:"蔖";s:4:"蔖";s:4:"𧏊";s:4:"𧏊";s:3:"蕤";s:4:"蕤";s:4:"𦼬";s:4:"𦼬";s:3:"䕝";s:4:"䕝";s:3:"䕡";s:4:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:3:"䕫";s:4:"䕫";s:3:"虐";s:4:"虐";s:3:"虧";s:4:"虧";s:3:"虩";s:4:"虩";s:3:"蚩";s:4:"蚩";s:3:"蚈";s:4:"蚈";s:3:"蜎";s:4:"蜎";s:3:"蛢";s:4:"蛢";s:3:"蜨";s:4:"蜨";s:3:"蝫";s:4:"蝫";s:3:"螆";s:4:"螆";s:3:"䗗";s:4:"䗗";s:3:"蟡";s:4:"蟡";s:3:"蠁";s:4:"蠁";s:3:"䗹";s:4:"䗹";s:3:"衠";s:4:"衠";s:3:"衣";s:4:"衣";s:4:"𧙧";s:4:"𧙧";s:3:"裗";s:4:"裗";s:3:"裞";s:4:"裞";s:3:"䘵";s:4:"䘵";s:3:"裺";s:4:"裺";s:3:"㒻";s:4:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:3:"䚾";s:4:"䚾";s:3:"䛇";s:4:"䛇";s:3:"誠";s:4:"誠";s:3:"豕";s:4:"豕";s:4:"𧲨";s:4:"𧲨";s:3:"貫";s:4:"貫";s:3:"賁";s:4:"賁";s:3:"贛";s:4:"贛";s:3:"起";s:4:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:3:"跋";s:4:"跋";s:3:"趼";s:4:"趼";s:3:"跰";s:4:"跰";s:4:"𠣞";s:4:"𠣞";s:3:"軔";s:4:"軔";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:3:"邔";s:4:"邔";s:3:"郱";s:4:"郱";s:3:"鄑";s:4:"鄑";s:4:"𨜮";s:4:"𨜮";s:3:"鄛";s:4:"鄛";s:3:"鈸";s:4:"鈸";s:3:"鋗";s:4:"鋗";s:3:"鋘";s:4:"鋘";s:3:"鉼";s:4:"鉼";s:3:"鏹";s:4:"鏹";s:3:"鐕";s:4:"鐕";s:4:"𨯺";s:4:"𨯺";s:3:"開";s:4:"開";s:3:"䦕";s:4:"䦕";s:3:"閷";s:4:"閷";s:4:"𨵷";s:4:"𨵷";s:3:"䧦";s:4:"䧦";s:3:"雃";s:4:"雃";s:3:"嶲";s:4:"嶲";s:3:"霣";s:4:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:3:"䩮";s:4:"䩮";s:3:"䩶";s:4:"䩶";s:3:"韠";s:4:"韠";s:4:"𩐊";s:4:"𩐊";s:3:"䪲";s:4:"䪲";s:4:"𩒖";s:4:"𩒖";s:3:"頩";s:4:"頩";s:4:"𩖶";s:4:"𩖶";s:3:"飢";s:4:"飢";s:3:"䬳";s:4:"䬳";s:3:"餩";s:4:"餩";s:3:"馧";s:4:"馧";s:3:"駂";s:4:"駂";s:3:"駾";s:4:"駾";s:3:"䯎";s:4:"䯎";s:4:"𩬰";s:4:"𩬰";s:3:"鱀";s:4:"鱀";s:3:"鳽";s:4:"鳽";s:3:"䳎";s:4:"䳎";s:3:"䳭";s:4:"䳭";s:3:"鵧";s:4:"鵧";s:4:"𪃎";s:4:"𪃎";s:3:"䳸";s:4:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:3:"麻";s:4:"麻";s:3:"䵖";s:4:"䵖";s:3:"黹";s:4:"黹";s:3:"黾";s:4:"黾";s:3:"鼅";s:4:"鼅";s:3:"鼏";s:4:"鼏";s:3:"鼖";s:4:"鼖";s:3:"鼻";s:4:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
+$utfCanonicalDecomp = unserialize( 'a:2043:{s:2:"À";s:3:"À";s:2:"Á";s:3:"Á";s:2:"Â";s:3:"Â";s:2:"Ã";s:3:"Ã";s:2:"Ä";s:3:"Ä";s:2:"Å";s:3:"Å";s:2:"Ç";s:3:"Ç";s:2:"È";s:3:"È";s:2:"É";s:3:"É";s:2:"Ê";s:3:"Ê";s:2:"Ë";s:3:"Ë";s:2:"Ì";s:3:"Ì";s:2:"Í";s:3:"Í";s:2:"Î";s:3:"Î";s:2:"Ï";s:3:"Ï";s:2:"Ñ";s:3:"Ñ";s:2:"Ò";s:3:"Ò";s:2:"Ó";s:3:"Ó";s:2:"Ô";s:3:"Ô";s:2:"Õ";s:3:"Õ";s:2:"Ö";s:3:"Ö";s:2:"Ù";s:3:"Ù";s:2:"Ú";s:3:"Ú";s:2:"Û";s:3:"Û";s:2:"Ü";s:3:"Ü";s:2:"Ý";s:3:"Ý";s:2:"à";s:3:"à";s:2:"á";s:3:"á";s:2:"â";s:3:"â";s:2:"ã";s:3:"ã";s:2:"ä";s:3:"ä";s:2:"å";s:3:"å";s:2:"ç";s:3:"ç";s:2:"è";s:3:"è";s:2:"é";s:3:"é";s:2:"ê";s:3:"ê";s:2:"ë";s:3:"ë";s:2:"ì";s:3:"ì";s:2:"í";s:3:"í";s:2:"î";s:3:"î";s:2:"ï";s:3:"ï";s:2:"ñ";s:3:"ñ";s:2:"ò";s:3:"ò";s:2:"ó";s:3:"ó";s:2:"ô";s:3:"ô";s:2:"õ";s:3:"õ";s:2:"ö";s:3:"ö";s:2:"ù";s:3:"ù";s:2:"ú";s:3:"ú";s:2:"û";s:3:"û";s:2:"ü";s:3:"ü";s:2:"ý";s:3:"ý";s:2:"ÿ";s:3:"ÿ";s:2:"Ā";s:3:"Ā";s:2:"ā";s:3:"ā";s:2:"Ă";s:3:"Ă";s:2:"ă";s:3:"ă";s:2:"Ą";s:3:"Ą";s:2:"ą";s:3:"ą";s:2:"Ć";s:3:"Ć";s:2:"ć";s:3:"ć";s:2:"Ĉ";s:3:"Ĉ";s:2:"ĉ";s:3:"ĉ";s:2:"Ċ";s:3:"Ċ";s:2:"ċ";s:3:"ċ";s:2:"Č";s:3:"Č";s:2:"č";s:3:"č";s:2:"Ď";s:3:"Ď";s:2:"ď";s:3:"ď";s:2:"Ē";s:3:"Ē";s:2:"ē";s:3:"ē";s:2:"Ĕ";s:3:"Ĕ";s:2:"ĕ";s:3:"ĕ";s:2:"Ė";s:3:"Ė";s:2:"ė";s:3:"ė";s:2:"Ę";s:3:"Ę";s:2:"ę";s:3:"ę";s:2:"Ě";s:3:"Ě";s:2:"ě";s:3:"ě";s:2:"Ĝ";s:3:"Ĝ";s:2:"ĝ";s:3:"ĝ";s:2:"Ğ";s:3:"Ğ";s:2:"ğ";s:3:"ğ";s:2:"Ġ";s:3:"Ġ";s:2:"ġ";s:3:"ġ";s:2:"Ģ";s:3:"Ģ";s:2:"ģ";s:3:"ģ";s:2:"Ĥ";s:3:"Ĥ";s:2:"ĥ";s:3:"ĥ";s:2:"Ĩ";s:3:"Ĩ";s:2:"ĩ";s:3:"ĩ";s:2:"Ī";s:3:"Ī";s:2:"ī";s:3:"ī";s:2:"Ĭ";s:3:"Ĭ";s:2:"ĭ";s:3:"ĭ";s:2:"Į";s:3:"Į";s:2:"į";s:3:"į";s:2:"İ";s:3:"İ";s:2:"Ĵ";s:3:"Ĵ";s:2:"ĵ";s:3:"ĵ";s:2:"Ķ";s:3:"Ķ";s:2:"ķ";s:3:"ķ";s:2:"Ĺ";s:3:"Ĺ";s:2:"ĺ";s:3:"ĺ";s:2:"Ļ";s:3:"Ļ";s:2:"ļ";s:3:"ļ";s:2:"Ľ";s:3:"Ľ";s:2:"ľ";s:3:"ľ";s:2:"Ń";s:3:"Ń";s:2:"ń";s:3:"ń";s:2:"Ņ";s:3:"Ņ";s:2:"ņ";s:3:"ņ";s:2:"Ň";s:3:"Ň";s:2:"ň";s:3:"ň";s:2:"Ō";s:3:"Ō";s:2:"ō";s:3:"ō";s:2:"Ŏ";s:3:"Ŏ";s:2:"ŏ";s:3:"ŏ";s:2:"Ő";s:3:"Ő";s:2:"ő";s:3:"ő";s:2:"Ŕ";s:3:"Ŕ";s:2:"ŕ";s:3:"ŕ";s:2:"Ŗ";s:3:"Ŗ";s:2:"ŗ";s:3:"ŗ";s:2:"Ř";s:3:"Ř";s:2:"ř";s:3:"ř";s:2:"Ś";s:3:"Ś";s:2:"ś";s:3:"ś";s:2:"Ŝ";s:3:"Ŝ";s:2:"ŝ";s:3:"ŝ";s:2:"Ş";s:3:"Ş";s:2:"ş";s:3:"ş";s:2:"Š";s:3:"Š";s:2:"š";s:3:"š";s:2:"Ţ";s:3:"Ţ";s:2:"ţ";s:3:"ţ";s:2:"Ť";s:3:"Ť";s:2:"ť";s:3:"ť";s:2:"Ũ";s:3:"Ũ";s:2:"ũ";s:3:"ũ";s:2:"Ū";s:3:"Ū";s:2:"ū";s:3:"ū";s:2:"Ŭ";s:3:"Ŭ";s:2:"ŭ";s:3:"ŭ";s:2:"Ů";s:3:"Ů";s:2:"ů";s:3:"ů";s:2:"Ű";s:3:"Ű";s:2:"ű";s:3:"ű";s:2:"Ų";s:3:"Ų";s:2:"ų";s:3:"ų";s:2:"Ŵ";s:3:"Ŵ";s:2:"ŵ";s:3:"ŵ";s:2:"Ŷ";s:3:"Ŷ";s:2:"ŷ";s:3:"ŷ";s:2:"Ÿ";s:3:"Ÿ";s:2:"Ź";s:3:"Ź";s:2:"ź";s:3:"ź";s:2:"Ż";s:3:"Ż";s:2:"ż";s:3:"ż";s:2:"Ž";s:3:"Ž";s:2:"ž";s:3:"ž";s:2:"Ơ";s:3:"Ơ";s:2:"ơ";s:3:"ơ";s:2:"Ư";s:3:"Ư";s:2:"ư";s:3:"ư";s:2:"Ǎ";s:3:"Ǎ";s:2:"ǎ";s:3:"ǎ";s:2:"Ǐ";s:3:"Ǐ";s:2:"ǐ";s:3:"ǐ";s:2:"Ǒ";s:3:"Ǒ";s:2:"ǒ";s:3:"ǒ";s:2:"Ǔ";s:3:"Ǔ";s:2:"ǔ";s:3:"ǔ";s:2:"Ǖ";s:5:"Ǖ";s:2:"ǖ";s:5:"ǖ";s:2:"Ǘ";s:5:"Ǘ";s:2:"ǘ";s:5:"ǘ";s:2:"Ǚ";s:5:"Ǚ";s:2:"ǚ";s:5:"ǚ";s:2:"Ǜ";s:5:"Ǜ";s:2:"ǜ";s:5:"ǜ";s:2:"Ǟ";s:5:"Ǟ";s:2:"ǟ";s:5:"ǟ";s:2:"Ǡ";s:5:"Ǡ";s:2:"ǡ";s:5:"ǡ";s:2:"Ǣ";s:4:"Ǣ";s:2:"ǣ";s:4:"ǣ";s:2:"Ǧ";s:3:"Ǧ";s:2:"ǧ";s:3:"ǧ";s:2:"Ǩ";s:3:"Ǩ";s:2:"ǩ";s:3:"ǩ";s:2:"Ǫ";s:3:"Ǫ";s:2:"ǫ";s:3:"ǫ";s:2:"Ǭ";s:5:"Ǭ";s:2:"ǭ";s:5:"ǭ";s:2:"Ǯ";s:4:"Ǯ";s:2:"ǯ";s:4:"ǯ";s:2:"ǰ";s:3:"ǰ";s:2:"Ǵ";s:3:"Ǵ";s:2:"ǵ";s:3:"ǵ";s:2:"Ǹ";s:3:"Ǹ";s:2:"ǹ";s:3:"ǹ";s:2:"Ǻ";s:5:"Ǻ";s:2:"ǻ";s:5:"ǻ";s:2:"Ǽ";s:4:"Ǽ";s:2:"ǽ";s:4:"ǽ";s:2:"Ǿ";s:4:"Ǿ";s:2:"ǿ";s:4:"ǿ";s:2:"Ȁ";s:3:"Ȁ";s:2:"ȁ";s:3:"ȁ";s:2:"Ȃ";s:3:"Ȃ";s:2:"ȃ";s:3:"ȃ";s:2:"Ȅ";s:3:"Ȅ";s:2:"ȅ";s:3:"ȅ";s:2:"Ȇ";s:3:"Ȇ";s:2:"ȇ";s:3:"ȇ";s:2:"Ȉ";s:3:"Ȉ";s:2:"ȉ";s:3:"ȉ";s:2:"Ȋ";s:3:"Ȋ";s:2:"ȋ";s:3:"ȋ";s:2:"Ȍ";s:3:"Ȍ";s:2:"ȍ";s:3:"ȍ";s:2:"Ȏ";s:3:"Ȏ";s:2:"ȏ";s:3:"ȏ";s:2:"Ȑ";s:3:"Ȑ";s:2:"ȑ";s:3:"ȑ";s:2:"Ȓ";s:3:"Ȓ";s:2:"ȓ";s:3:"ȓ";s:2:"Ȕ";s:3:"Ȕ";s:2:"ȕ";s:3:"ȕ";s:2:"Ȗ";s:3:"Ȗ";s:2:"ȗ";s:3:"ȗ";s:2:"Ș";s:3:"Ș";s:2:"ș";s:3:"ș";s:2:"Ț";s:3:"Ț";s:2:"ț";s:3:"ț";s:2:"Ȟ";s:3:"Ȟ";s:2:"ȟ";s:3:"ȟ";s:2:"Ȧ";s:3:"Ȧ";s:2:"ȧ";s:3:"ȧ";s:2:"Ȩ";s:3:"Ȩ";s:2:"ȩ";s:3:"ȩ";s:2:"Ȫ";s:5:"Ȫ";s:2:"ȫ";s:5:"ȫ";s:2:"Ȭ";s:5:"Ȭ";s:2:"ȭ";s:5:"ȭ";s:2:"Ȯ";s:3:"Ȯ";s:2:"ȯ";s:3:"ȯ";s:2:"Ȱ";s:5:"Ȱ";s:2:"ȱ";s:5:"ȱ";s:2:"Ȳ";s:3:"Ȳ";s:2:"ȳ";s:3:"ȳ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:2:"̈́";s:4:"̈́";s:2:"ʹ";s:2:"ʹ";s:2:";";s:1:";";s:2:"΅";s:4:"΅";s:2:"Ά";s:4:"Ά";s:2:"·";s:2:"·";s:2:"Έ";s:4:"Έ";s:2:"Ή";s:4:"Ή";s:2:"Ί";s:4:"Ί";s:2:"Ό";s:4:"Ό";s:2:"Ύ";s:4:"Ύ";s:2:"Ώ";s:4:"Ώ";s:2:"ΐ";s:6:"ΐ";s:2:"Ϊ";s:4:"Ϊ";s:2:"Ϋ";s:4:"Ϋ";s:2:"ά";s:4:"ά";s:2:"έ";s:4:"έ";s:2:"ή";s:4:"ή";s:2:"ί";s:4:"ί";s:2:"ΰ";s:6:"ΰ";s:2:"ϊ";s:4:"ϊ";s:2:"ϋ";s:4:"ϋ";s:2:"ό";s:4:"ό";s:2:"ύ";s:4:"ύ";s:2:"ώ";s:4:"ώ";s:2:"ϓ";s:4:"ϓ";s:2:"ϔ";s:4:"ϔ";s:2:"Ѐ";s:4:"Ѐ";s:2:"Ё";s:4:"Ё";s:2:"Ѓ";s:4:"Ѓ";s:2:"Ї";s:4:"Ї";s:2:"Ќ";s:4:"Ќ";s:2:"Ѝ";s:4:"Ѝ";s:2:"Ў";s:4:"Ў";s:2:"Й";s:4:"Й";s:2:"й";s:4:"й";s:2:"ѐ";s:4:"ѐ";s:2:"ё";s:4:"ё";s:2:"ѓ";s:4:"ѓ";s:2:"ї";s:4:"ї";s:2:"ќ";s:4:"ќ";s:2:"ѝ";s:4:"ѝ";s:2:"ў";s:4:"ў";s:2:"Ѷ";s:4:"Ѷ";s:2:"ѷ";s:4:"ѷ";s:2:"Ӂ";s:4:"Ӂ";s:2:"ӂ";s:4:"ӂ";s:2:"Ӑ";s:4:"Ӑ";s:2:"ӑ";s:4:"ӑ";s:2:"Ӓ";s:4:"Ӓ";s:2:"ӓ";s:4:"ӓ";s:2:"Ӗ";s:4:"Ӗ";s:2:"ӗ";s:4:"ӗ";s:2:"Ӛ";s:4:"Ӛ";s:2:"ӛ";s:4:"ӛ";s:2:"Ӝ";s:4:"Ӝ";s:2:"ӝ";s:4:"ӝ";s:2:"Ӟ";s:4:"Ӟ";s:2:"ӟ";s:4:"ӟ";s:2:"Ӣ";s:4:"Ӣ";s:2:"ӣ";s:4:"ӣ";s:2:"Ӥ";s:4:"Ӥ";s:2:"ӥ";s:4:"ӥ";s:2:"Ӧ";s:4:"Ӧ";s:2:"ӧ";s:4:"ӧ";s:2:"Ӫ";s:4:"Ӫ";s:2:"ӫ";s:4:"ӫ";s:2:"Ӭ";s:4:"Ӭ";s:2:"ӭ";s:4:"ӭ";s:2:"Ӯ";s:4:"Ӯ";s:2:"ӯ";s:4:"ӯ";s:2:"Ӱ";s:4:"Ӱ";s:2:"ӱ";s:4:"ӱ";s:2:"Ӳ";s:4:"Ӳ";s:2:"ӳ";s:4:"ӳ";s:2:"Ӵ";s:4:"Ӵ";s:2:"ӵ";s:4:"ӵ";s:2:"Ӹ";s:4:"Ӹ";s:2:"ӹ";s:4:"ӹ";s:2:"آ";s:4:"آ";s:2:"أ";s:4:"أ";s:2:"ؤ";s:4:"ؤ";s:2:"إ";s:4:"إ";s:2:"ئ";s:4:"ئ";s:2:"ۀ";s:4:"ۀ";s:2:"ۂ";s:4:"ۂ";s:2:"ۓ";s:4:"ۓ";s:3:"ऩ";s:6:"ऩ";s:3:"ऱ";s:6:"ऱ";s:3:"ऴ";s:6:"ऴ";s:3:"क़";s:6:"क़";s:3:"ख़";s:6:"ख़";s:3:"ग़";s:6:"ग़";s:3:"ज़";s:6:"ज़";s:3:"ड़";s:6:"ड़";s:3:"ढ़";s:6:"ढ़";s:3:"फ़";s:6:"फ़";s:3:"य़";s:6:"य़";s:3:"ো";s:6:"ো";s:3:"ৌ";s:6:"ৌ";s:3:"ড়";s:6:"ড়";s:3:"ঢ়";s:6:"ঢ়";s:3:"য়";s:6:"য়";s:3:"ਲ਼";s:6:"ਲ਼";s:3:"ਸ਼";s:6:"ਸ਼";s:3:"ਖ਼";s:6:"ਖ਼";s:3:"ਗ਼";s:6:"ਗ਼";s:3:"ਜ਼";s:6:"ਜ਼";s:3:"ਫ਼";s:6:"ਫ਼";s:3:"ୈ";s:6:"ୈ";s:3:"ୋ";s:6:"ୋ";s:3:"ୌ";s:6:"ୌ";s:3:"ଡ଼";s:6:"ଡ଼";s:3:"ଢ଼";s:6:"ଢ଼";s:3:"ஔ";s:6:"ஔ";s:3:"ொ";s:6:"ொ";s:3:"ோ";s:6:"ோ";s:3:"ௌ";s:6:"ௌ";s:3:"ై";s:6:"ై";s:3:"ೀ";s:6:"ೀ";s:3:"ೇ";s:6:"ೇ";s:3:"ೈ";s:6:"ೈ";s:3:"ೊ";s:6:"ೊ";s:3:"ೋ";s:9:"ೋ";s:3:"ൊ";s:6:"ൊ";s:3:"ോ";s:6:"ോ";s:3:"ൌ";s:6:"ൌ";s:3:"ේ";s:6:"ේ";s:3:"ො";s:6:"ො";s:3:"ෝ";s:9:"ෝ";s:3:"ෞ";s:6:"ෞ";s:3:"གྷ";s:6:"གྷ";s:3:"ཌྷ";s:6:"ཌྷ";s:3:"དྷ";s:6:"དྷ";s:3:"བྷ";s:6:"བྷ";s:3:"ཛྷ";s:6:"ཛྷ";s:3:"ཀྵ";s:6:"ཀྵ";s:3:"ཱི";s:6:"ཱི";s:3:"ཱུ";s:6:"ཱུ";s:3:"ྲྀ";s:6:"ྲྀ";s:3:"ླྀ";s:6:"ླྀ";s:3:"ཱྀ";s:6:"ཱྀ";s:3:"ྒྷ";s:6:"ྒྷ";s:3:"ྜྷ";s:6:"ྜྷ";s:3:"ྡྷ";s:6:"ྡྷ";s:3:"ྦྷ";s:6:"ྦྷ";s:3:"ྫྷ";s:6:"ྫྷ";s:3:"ྐྵ";s:6:"ྐྵ";s:3:"ဦ";s:6:"ဦ";s:3:"ᬆ";s:6:"ᬆ";s:3:"ᬈ";s:6:"ᬈ";s:3:"ᬊ";s:6:"ᬊ";s:3:"ᬌ";s:6:"ᬌ";s:3:"ᬎ";s:6:"ᬎ";s:3:"ᬒ";s:6:"ᬒ";s:3:"ᬻ";s:6:"ᬻ";s:3:"ᬽ";s:6:"ᬽ";s:3:"ᭀ";s:6:"ᭀ";s:3:"ᭁ";s:6:"ᭁ";s:3:"ᭃ";s:6:"ᭃ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:3:"Ḉ";s:5:"Ḉ";s:3:"ḉ";s:5:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:3:"Ḕ";s:5:"Ḕ";s:3:"ḕ";s:5:"ḕ";s:3:"Ḗ";s:5:"Ḗ";s:3:"ḗ";s:5:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:3:"Ḝ";s:5:"Ḝ";s:3:"ḝ";s:5:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:3:"Ḯ";s:5:"Ḯ";s:3:"ḯ";s:5:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:3:"Ḹ";s:5:"Ḹ";s:3:"ḹ";s:5:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:3:"Ṍ";s:5:"Ṍ";s:3:"ṍ";s:5:"ṍ";s:3:"Ṏ";s:5:"Ṏ";s:3:"ṏ";s:5:"ṏ";s:3:"Ṑ";s:5:"Ṑ";s:3:"ṑ";s:5:"ṑ";s:3:"Ṓ";s:5:"Ṓ";s:3:"ṓ";s:5:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:3:"Ṝ";s:5:"Ṝ";s:3:"ṝ";s:5:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:3:"Ṥ";s:5:"Ṥ";s:3:"ṥ";s:5:"ṥ";s:3:"Ṧ";s:5:"Ṧ";s:3:"ṧ";s:5:"ṧ";s:3:"Ṩ";s:5:"Ṩ";s:3:"ṩ";s:5:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:3:"Ṹ";s:5:"Ṹ";s:3:"ṹ";s:5:"ṹ";s:3:"Ṻ";s:5:"Ṻ";s:3:"ṻ";s:5:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:3:"ẛ";s:4:"ẛ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:3:"Ấ";s:5:"Ấ";s:3:"ấ";s:5:"ấ";s:3:"Ầ";s:5:"Ầ";s:3:"ầ";s:5:"ầ";s:3:"Ẩ";s:5:"Ẩ";s:3:"ẩ";s:5:"ẩ";s:3:"Ẫ";s:5:"Ẫ";s:3:"ẫ";s:5:"ẫ";s:3:"Ậ";s:5:"Ậ";s:3:"ậ";s:5:"ậ";s:3:"Ắ";s:5:"Ắ";s:3:"ắ";s:5:"ắ";s:3:"Ằ";s:5:"Ằ";s:3:"ằ";s:5:"ằ";s:3:"Ẳ";s:5:"Ẳ";s:3:"ẳ";s:5:"ẳ";s:3:"Ẵ";s:5:"Ẵ";s:3:"ẵ";s:5:"ẵ";s:3:"Ặ";s:5:"Ặ";s:3:"ặ";s:5:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:3:"Ế";s:5:"Ế";s:3:"ế";s:5:"ế";s:3:"Ề";s:5:"Ề";s:3:"ề";s:5:"ề";s:3:"Ể";s:5:"Ể";s:3:"ể";s:5:"ể";s:3:"Ễ";s:5:"Ễ";s:3:"ễ";s:5:"ễ";s:3:"Ệ";s:5:"Ệ";s:3:"ệ";s:5:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:3:"Ố";s:5:"Ố";s:3:"ố";s:5:"ố";s:3:"Ồ";s:5:"Ồ";s:3:"ồ";s:5:"ồ";s:3:"Ổ";s:5:"Ổ";s:3:"ổ";s:5:"ổ";s:3:"Ỗ";s:5:"Ỗ";s:3:"ỗ";s:5:"ỗ";s:3:"Ộ";s:5:"Ộ";s:3:"ộ";s:5:"ộ";s:3:"Ớ";s:5:"Ớ";s:3:"ớ";s:5:"ớ";s:3:"Ờ";s:5:"Ờ";s:3:"ờ";s:5:"ờ";s:3:"Ở";s:5:"Ở";s:3:"ở";s:5:"ở";s:3:"Ỡ";s:5:"Ỡ";s:3:"ỡ";s:5:"ỡ";s:3:"Ợ";s:5:"Ợ";s:3:"ợ";s:5:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:3:"Ứ";s:5:"Ứ";s:3:"ứ";s:5:"ứ";s:3:"Ừ";s:5:"Ừ";s:3:"ừ";s:5:"ừ";s:3:"Ử";s:5:"Ử";s:3:"ử";s:5:"ử";s:3:"Ữ";s:5:"Ữ";s:3:"ữ";s:5:"ữ";s:3:"Ự";s:5:"Ự";s:3:"ự";s:5:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:3:"ἀ";s:4:"ἀ";s:3:"ἁ";s:4:"ἁ";s:3:"ἂ";s:6:"ἂ";s:3:"ἃ";s:6:"ἃ";s:3:"ἄ";s:6:"ἄ";s:3:"ἅ";s:6:"ἅ";s:3:"ἆ";s:6:"ἆ";s:3:"ἇ";s:6:"ἇ";s:3:"Ἀ";s:4:"Ἀ";s:3:"Ἁ";s:4:"Ἁ";s:3:"Ἂ";s:6:"Ἂ";s:3:"Ἃ";s:6:"Ἃ";s:3:"Ἄ";s:6:"Ἄ";s:3:"Ἅ";s:6:"Ἅ";s:3:"Ἆ";s:6:"Ἆ";s:3:"Ἇ";s:6:"Ἇ";s:3:"ἐ";s:4:"ἐ";s:3:"ἑ";s:4:"ἑ";s:3:"ἒ";s:6:"ἒ";s:3:"ἓ";s:6:"ἓ";s:3:"ἔ";s:6:"ἔ";s:3:"ἕ";s:6:"ἕ";s:3:"Ἐ";s:4:"Ἐ";s:3:"Ἑ";s:4:"Ἑ";s:3:"Ἒ";s:6:"Ἒ";s:3:"Ἓ";s:6:"Ἓ";s:3:"Ἔ";s:6:"Ἔ";s:3:"Ἕ";s:6:"Ἕ";s:3:"ἠ";s:4:"ἠ";s:3:"ἡ";s:4:"ἡ";s:3:"ἢ";s:6:"ἢ";s:3:"ἣ";s:6:"ἣ";s:3:"ἤ";s:6:"ἤ";s:3:"ἥ";s:6:"ἥ";s:3:"ἦ";s:6:"ἦ";s:3:"ἧ";s:6:"ἧ";s:3:"Ἠ";s:4:"Ἠ";s:3:"Ἡ";s:4:"Ἡ";s:3:"Ἢ";s:6:"Ἢ";s:3:"Ἣ";s:6:"Ἣ";s:3:"Ἤ";s:6:"Ἤ";s:3:"Ἥ";s:6:"Ἥ";s:3:"Ἦ";s:6:"Ἦ";s:3:"Ἧ";s:6:"Ἧ";s:3:"ἰ";s:4:"ἰ";s:3:"ἱ";s:4:"ἱ";s:3:"ἲ";s:6:"ἲ";s:3:"ἳ";s:6:"ἳ";s:3:"ἴ";s:6:"ἴ";s:3:"ἵ";s:6:"ἵ";s:3:"ἶ";s:6:"ἶ";s:3:"ἷ";s:6:"ἷ";s:3:"Ἰ";s:4:"Ἰ";s:3:"Ἱ";s:4:"Ἱ";s:3:"Ἲ";s:6:"Ἲ";s:3:"Ἳ";s:6:"Ἳ";s:3:"Ἴ";s:6:"Ἴ";s:3:"Ἵ";s:6:"Ἵ";s:3:"Ἶ";s:6:"Ἶ";s:3:"Ἷ";s:6:"Ἷ";s:3:"ὀ";s:4:"ὀ";s:3:"ὁ";s:4:"ὁ";s:3:"ὂ";s:6:"ὂ";s:3:"ὃ";s:6:"ὃ";s:3:"ὄ";s:6:"ὄ";s:3:"ὅ";s:6:"ὅ";s:3:"Ὀ";s:4:"Ὀ";s:3:"Ὁ";s:4:"Ὁ";s:3:"Ὂ";s:6:"Ὂ";s:3:"Ὃ";s:6:"Ὃ";s:3:"Ὄ";s:6:"Ὄ";s:3:"Ὅ";s:6:"Ὅ";s:3:"ὐ";s:4:"ὐ";s:3:"ὑ";s:4:"ὑ";s:3:"ὒ";s:6:"ὒ";s:3:"ὓ";s:6:"ὓ";s:3:"ὔ";s:6:"ὔ";s:3:"ὕ";s:6:"ὕ";s:3:"ὖ";s:6:"ὖ";s:3:"ὗ";s:6:"ὗ";s:3:"Ὑ";s:4:"Ὑ";s:3:"Ὓ";s:6:"Ὓ";s:3:"Ὕ";s:6:"Ὕ";s:3:"Ὗ";s:6:"Ὗ";s:3:"ὠ";s:4:"ὠ";s:3:"ὡ";s:4:"ὡ";s:3:"ὢ";s:6:"ὢ";s:3:"ὣ";s:6:"ὣ";s:3:"ὤ";s:6:"ὤ";s:3:"ὥ";s:6:"ὥ";s:3:"ὦ";s:6:"ὦ";s:3:"ὧ";s:6:"ὧ";s:3:"Ὠ";s:4:"Ὠ";s:3:"Ὡ";s:4:"Ὡ";s:3:"Ὢ";s:6:"Ὢ";s:3:"Ὣ";s:6:"Ὣ";s:3:"Ὤ";s:6:"Ὤ";s:3:"Ὥ";s:6:"Ὥ";s:3:"Ὦ";s:6:"Ὦ";s:3:"Ὧ";s:6:"Ὧ";s:3:"ὰ";s:4:"ὰ";s:3:"ά";s:4:"ά";s:3:"ὲ";s:4:"ὲ";s:3:"έ";s:4:"έ";s:3:"ὴ";s:4:"ὴ";s:3:"ή";s:4:"ή";s:3:"ὶ";s:4:"ὶ";s:3:"ί";s:4:"ί";s:3:"ὸ";s:4:"ὸ";s:3:"ό";s:4:"ό";s:3:"ὺ";s:4:"ὺ";s:3:"ύ";s:4:"ύ";s:3:"ὼ";s:4:"ὼ";s:3:"ώ";s:4:"ώ";s:3:"ᾀ";s:6:"ᾀ";s:3:"ᾁ";s:6:"ᾁ";s:3:"ᾂ";s:8:"ᾂ";s:3:"ᾃ";s:8:"ᾃ";s:3:"ᾄ";s:8:"ᾄ";s:3:"ᾅ";s:8:"ᾅ";s:3:"ᾆ";s:8:"ᾆ";s:3:"ᾇ";s:8:"ᾇ";s:3:"ᾈ";s:6:"ᾈ";s:3:"ᾉ";s:6:"ᾉ";s:3:"ᾊ";s:8:"ᾊ";s:3:"ᾋ";s:8:"ᾋ";s:3:"ᾌ";s:8:"ᾌ";s:3:"ᾍ";s:8:"ᾍ";s:3:"ᾎ";s:8:"ᾎ";s:3:"ᾏ";s:8:"ᾏ";s:3:"ᾐ";s:6:"ᾐ";s:3:"ᾑ";s:6:"ᾑ";s:3:"ᾒ";s:8:"ᾒ";s:3:"ᾓ";s:8:"ᾓ";s:3:"ᾔ";s:8:"ᾔ";s:3:"ᾕ";s:8:"ᾕ";s:3:"ᾖ";s:8:"ᾖ";s:3:"ᾗ";s:8:"ᾗ";s:3:"ᾘ";s:6:"ᾘ";s:3:"ᾙ";s:6:"ᾙ";s:3:"ᾚ";s:8:"ᾚ";s:3:"ᾛ";s:8:"ᾛ";s:3:"ᾜ";s:8:"ᾜ";s:3:"ᾝ";s:8:"ᾝ";s:3:"ᾞ";s:8:"ᾞ";s:3:"ᾟ";s:8:"ᾟ";s:3:"ᾠ";s:6:"ᾠ";s:3:"ᾡ";s:6:"ᾡ";s:3:"ᾢ";s:8:"ᾢ";s:3:"ᾣ";s:8:"ᾣ";s:3:"ᾤ";s:8:"ᾤ";s:3:"ᾥ";s:8:"ᾥ";s:3:"ᾦ";s:8:"ᾦ";s:3:"ᾧ";s:8:"ᾧ";s:3:"ᾨ";s:6:"ᾨ";s:3:"ᾩ";s:6:"ᾩ";s:3:"ᾪ";s:8:"ᾪ";s:3:"ᾫ";s:8:"ᾫ";s:3:"ᾬ";s:8:"ᾬ";s:3:"ᾭ";s:8:"ᾭ";s:3:"ᾮ";s:8:"ᾮ";s:3:"ᾯ";s:8:"ᾯ";s:3:"ᾰ";s:4:"ᾰ";s:3:"ᾱ";s:4:"ᾱ";s:3:"ᾲ";s:6:"ᾲ";s:3:"ᾳ";s:4:"ᾳ";s:3:"ᾴ";s:6:"ᾴ";s:3:"ᾶ";s:4:"ᾶ";s:3:"ᾷ";s:6:"ᾷ";s:3:"Ᾰ";s:4:"Ᾰ";s:3:"Ᾱ";s:4:"Ᾱ";s:3:"Ὰ";s:4:"Ὰ";s:3:"Ά";s:4:"Ά";s:3:"ᾼ";s:4:"ᾼ";s:3:"ι";s:2:"ι";s:3:"῁";s:4:"῁";s:3:"ῂ";s:6:"ῂ";s:3:"ῃ";s:4:"ῃ";s:3:"ῄ";s:6:"ῄ";s:3:"ῆ";s:4:"ῆ";s:3:"ῇ";s:6:"ῇ";s:3:"Ὲ";s:4:"Ὲ";s:3:"Έ";s:4:"Έ";s:3:"Ὴ";s:4:"Ὴ";s:3:"Ή";s:4:"Ή";s:3:"ῌ";s:4:"ῌ";s:3:"῍";s:5:"῍";s:3:"῎";s:5:"῎";s:3:"῏";s:5:"῏";s:3:"ῐ";s:4:"ῐ";s:3:"ῑ";s:4:"ῑ";s:3:"ῒ";s:6:"ῒ";s:3:"ΐ";s:6:"ΐ";s:3:"ῖ";s:4:"ῖ";s:3:"ῗ";s:6:"ῗ";s:3:"Ῐ";s:4:"Ῐ";s:3:"Ῑ";s:4:"Ῑ";s:3:"Ὶ";s:4:"Ὶ";s:3:"Ί";s:4:"Ί";s:3:"῝";s:5:"῝";s:3:"῞";s:5:"῞";s:3:"῟";s:5:"῟";s:3:"ῠ";s:4:"ῠ";s:3:"ῡ";s:4:"ῡ";s:3:"ῢ";s:6:"ῢ";s:3:"ΰ";s:6:"ΰ";s:3:"ῤ";s:4:"ῤ";s:3:"ῥ";s:4:"ῥ";s:3:"ῦ";s:4:"ῦ";s:3:"ῧ";s:6:"ῧ";s:3:"Ῠ";s:4:"Ῠ";s:3:"Ῡ";s:4:"Ῡ";s:3:"Ὺ";s:4:"Ὺ";s:3:"Ύ";s:4:"Ύ";s:3:"Ῥ";s:4:"Ῥ";s:3:"῭";s:4:"῭";s:3:"΅";s:4:"΅";s:3:"`";s:1:"`";s:3:"ῲ";s:6:"ῲ";s:3:"ῳ";s:4:"ῳ";s:3:"ῴ";s:6:"ῴ";s:3:"ῶ";s:4:"ῶ";s:3:"ῷ";s:6:"ῷ";s:3:"Ὸ";s:4:"Ὸ";s:3:"Ό";s:4:"Ό";s:3:"Ὼ";s:4:"Ὼ";s:3:"Ώ";s:4:"Ώ";s:3:"ῼ";s:4:"ῼ";s:3:"´";s:2:"´";s:3:" ";s:3:" ";s:3:" ";s:3:" ";s:3:"Ω";s:2:"Ω";s:3:"K";s:1:"K";s:3:"Å";s:3:"Å";s:3:"↚";s:5:"↚";s:3:"↛";s:5:"↛";s:3:"↮";s:5:"↮";s:3:"⇍";s:5:"⇍";s:3:"⇎";s:5:"⇎";s:3:"⇏";s:5:"⇏";s:3:"∄";s:5:"∄";s:3:"∉";s:5:"∉";s:3:"∌";s:5:"∌";s:3:"∤";s:5:"∤";s:3:"∦";s:5:"∦";s:3:"≁";s:5:"≁";s:3:"≄";s:5:"≄";s:3:"≇";s:5:"≇";s:3:"≉";s:5:"≉";s:3:"≠";s:3:"≠";s:3:"≢";s:5:"≢";s:3:"≭";s:5:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:3:"≰";s:5:"≰";s:3:"≱";s:5:"≱";s:3:"≴";s:5:"≴";s:3:"≵";s:5:"≵";s:3:"≸";s:5:"≸";s:3:"≹";s:5:"≹";s:3:"⊀";s:5:"⊀";s:3:"⊁";s:5:"⊁";s:3:"⊄";s:5:"⊄";s:3:"⊅";s:5:"⊅";s:3:"⊈";s:5:"⊈";s:3:"⊉";s:5:"⊉";s:3:"⊬";s:5:"⊬";s:3:"⊭";s:5:"⊭";s:3:"⊮";s:5:"⊮";s:3:"⊯";s:5:"⊯";s:3:"⋠";s:5:"⋠";s:3:"⋡";s:5:"⋡";s:3:"⋢";s:5:"⋢";s:3:"⋣";s:5:"⋣";s:3:"⋪";s:5:"⋪";s:3:"⋫";s:5:"⋫";s:3:"⋬";s:5:"⋬";s:3:"⋭";s:5:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:3:"⫝̸";s:5:"⫝̸";s:3:"が";s:6:"が";s:3:"ぎ";s:6:"ぎ";s:3:"ぐ";s:6:"ぐ";s:3:"げ";s:6:"げ";s:3:"ご";s:6:"ご";s:3:"ざ";s:6:"ざ";s:3:"じ";s:6:"じ";s:3:"ず";s:6:"ず";s:3:"ぜ";s:6:"ぜ";s:3:"ぞ";s:6:"ぞ";s:3:"だ";s:6:"だ";s:3:"ぢ";s:6:"ぢ";s:3:"づ";s:6:"づ";s:3:"で";s:6:"で";s:3:"ど";s:6:"ど";s:3:"ば";s:6:"ば";s:3:"ぱ";s:6:"ぱ";s:3:"び";s:6:"び";s:3:"ぴ";s:6:"ぴ";s:3:"ぶ";s:6:"ぶ";s:3:"ぷ";s:6:"ぷ";s:3:"べ";s:6:"べ";s:3:"ぺ";s:6:"ぺ";s:3:"ぼ";s:6:"ぼ";s:3:"ぽ";s:6:"ぽ";s:3:"ゔ";s:6:"ゔ";s:3:"ゞ";s:6:"ゞ";s:3:"ガ";s:6:"ガ";s:3:"ギ";s:6:"ギ";s:3:"グ";s:6:"グ";s:3:"ゲ";s:6:"ゲ";s:3:"ゴ";s:6:"ゴ";s:3:"ザ";s:6:"ザ";s:3:"ジ";s:6:"ジ";s:3:"ズ";s:6:"ズ";s:3:"ゼ";s:6:"ゼ";s:3:"ゾ";s:6:"ゾ";s:3:"ダ";s:6:"ダ";s:3:"ヂ";s:6:"ヂ";s:3:"ヅ";s:6:"ヅ";s:3:"デ";s:6:"デ";s:3:"ド";s:6:"ド";s:3:"バ";s:6:"バ";s:3:"パ";s:6:"パ";s:3:"ビ";s:6:"ビ";s:3:"ピ";s:6:"ピ";s:3:"ブ";s:6:"ブ";s:3:"プ";s:6:"プ";s:3:"ベ";s:6:"ベ";s:3:"ペ";s:6:"ペ";s:3:"ボ";s:6:"ボ";s:3:"ポ";s:6:"ポ";s:3:"ヴ";s:6:"ヴ";s:3:"ヷ";s:6:"ヷ";s:3:"ヸ";s:6:"ヸ";s:3:"ヹ";s:6:"ヹ";s:3:"ヺ";s:6:"ヺ";s:3:"ヾ";s:6:"ヾ";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:3:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:3:"廊";s:3:"朗";s:3:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:3:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:3:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"樂";s:3:"樂";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:3:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:3:"異";s:3:"北";s:3:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:3:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:3:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"說";s:3:"說";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"寧";s:3:"寧";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"樂";s:3:"樂";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:3:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"率";s:3:"率";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:3:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:3:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:3:"侮";s:3:"僧";s:3:"僧";s:3:"免";s:3:"免";s:3:"勉";s:3:"勉";s:3:"勤";s:3:"勤";s:3:"卑";s:3:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:3:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:3:"屮";s:3:"悔";s:3:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:3:"憎";s:3:"懲";s:3:"懲";s:3:"敏";s:3:"敏";s:3:"既";s:3:"既";s:3:"暑";s:3:"暑";s:3:"梅";s:3:"梅";s:3:"海";s:3:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:3:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:3:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"練";s:3:"練";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:3:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"著";s:3:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"逸";s:3:"逸";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:3:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:3:"勇";s:3:"勺";s:3:"勺";s:3:"喝";s:3:"喝";s:3:"啕";s:3:"啕";s:3:"喙";s:3:"喙";s:3:"嗢";s:3:"嗢";s:3:"塚";s:3:"塚";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:3:"慎";s:3:"愈";s:3:"愈";s:3:"憎";s:3:"憎";s:3:"慠";s:3:"慠";s:3:"懲";s:3:"懲";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"晴";s:3:"晴";s:3:"朗";s:3:"朗";s:3:"望";s:3:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"殺";s:3:"殺";s:3:"流";s:3:"流";s:3:"滛";s:3:"滛";s:3:"滋";s:3:"滋";s:3:"漢";s:3:"漢";s:3:"瀞";s:3:"瀞";s:3:"煮";s:3:"煮";s:3:"瞧";s:3:"瞧";s:3:"爵";s:3:"爵";s:3:"犯";s:3:"犯";s:3:"猪";s:3:"猪";s:3:"瑱";s:3:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"益";s:3:"益";s:3:"盛";s:3:"盛";s:3:"直";s:3:"直";s:3:"睊";s:3:"睊";s:3:"着";s:3:"着";s:3:"磌";s:3:"磌";s:3:"窱";s:3:"窱";s:3:"節";s:3:"節";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"練";s:3:"練";s:3:"缾";s:3:"缾";s:3:"者";s:3:"者";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:3:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"視";s:3:"視";s:3:"調";s:3:"調";s:3:"諸";s:3:"諸";s:3:"請";s:3:"請";s:3:"謁";s:3:"謁";s:3:"諾";s:3:"諾";s:3:"諭";s:3:"諭";s:3:"謹";s:3:"謹";s:3:"變";s:3:"變";s:3:"贈";s:3:"贈";s:3:"輸";s:3:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"難";s:3:"難";s:3:"靖";s:3:"靖";s:3:"韛";s:3:"韛";s:3:"響";s:3:"響";s:3:"頋";s:3:"頋";s:3:"頻";s:3:"頻";s:3:"鬒";s:3:"鬒";s:3:"龜";s:3:"龜";s:3:"𢡊";s:4:"𢡊";s:3:"𢡄";s:4:"𢡄";s:3:"𣏕";s:4:"𣏕";s:3:"㮝";s:3:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:3:"䀹";s:3:"𥉉";s:4:"𥉉";s:3:"𥳐";s:4:"𥳐";s:3:"𧻓";s:4:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"יִ";s:4:"יִ";s:3:"ײַ";s:4:"ײַ";s:3:"שׁ";s:4:"שׁ";s:3:"שׂ";s:4:"שׂ";s:3:"שּׁ";s:6:"שּׁ";s:3:"שּׂ";s:6:"שּׂ";s:3:"אַ";s:4:"אַ";s:3:"אָ";s:4:"אָ";s:3:"אּ";s:4:"אּ";s:3:"בּ";s:4:"בּ";s:3:"גּ";s:4:"גּ";s:3:"דּ";s:4:"דּ";s:3:"הּ";s:4:"הּ";s:3:"וּ";s:4:"וּ";s:3:"זּ";s:4:"זּ";s:3:"טּ";s:4:"טּ";s:3:"יּ";s:4:"יּ";s:3:"ךּ";s:4:"ךּ";s:3:"כּ";s:4:"כּ";s:3:"לּ";s:4:"לּ";s:3:"מּ";s:4:"מּ";s:3:"נּ";s:4:"נּ";s:3:"סּ";s:4:"סּ";s:3:"ףּ";s:4:"ףּ";s:3:"פּ";s:4:"פּ";s:3:"צּ";s:4:"צּ";s:3:"קּ";s:4:"קּ";s:3:"רּ";s:4:"רּ";s:3:"שּ";s:4:"שּ";s:3:"תּ";s:4:"תּ";s:3:"וֹ";s:4:"וֹ";s:3:"בֿ";s:4:"בֿ";s:3:"כֿ";s:4:"כֿ";s:3:"פֿ";s:4:"פֿ";s:4:"𝅗𝅥";s:8:"𝅗𝅥";s:4:"𝅘𝅥";s:8:"𝅘𝅥";s:4:"𝅘𝅥𝅮";s:12:"𝅘𝅥𝅮";s:4:"𝅘𝅥𝅯";s:12:"𝅘𝅥𝅯";s:4:"𝅘𝅥𝅰";s:12:"𝅘𝅥𝅰";s:4:"𝅘𝅥𝅱";s:12:"𝅘𝅥𝅱";s:4:"𝅘𝅥𝅲";s:12:"𝅘𝅥𝅲";s:4:"𝆹𝅥";s:8:"𝆹𝅥";s:4:"𝆺𝅥";s:8:"𝆺𝅥";s:4:"𝆹𝅥𝅮";s:12:"𝆹𝅥𝅮";s:4:"𝆺𝅥𝅮";s:12:"𝆺𝅥𝅮";s:4:"𝆹𝅥𝅯";s:12:"𝆹𝅥𝅯";s:4:"𝆺𝅥𝅯";s:12:"𝆺𝅥𝅯";s:4:"丽";s:3:"丽";s:4:"丸";s:3:"丸";s:4:"乁";s:3:"乁";s:4:"𠄢";s:4:"𠄢";s:4:"你";s:3:"你";s:4:"侮";s:3:"侮";s:4:"侻";s:3:"侻";s:4:"倂";s:3:"倂";s:4:"偺";s:3:"偺";s:4:"備";s:3:"備";s:4:"僧";s:3:"僧";s:4:"像";s:3:"像";s:4:"㒞";s:3:"㒞";s:4:"𠘺";s:4:"𠘺";s:4:"免";s:3:"免";s:4:"兔";s:3:"兔";s:4:"兤";s:3:"兤";s:4:"具";s:3:"具";s:4:"𠔜";s:4:"𠔜";s:4:"㒹";s:3:"㒹";s:4:"內";s:3:"內";s:4:"再";s:3:"再";s:4:"𠕋";s:4:"𠕋";s:4:"冗";s:3:"冗";s:4:"冤";s:3:"冤";s:4:"仌";s:3:"仌";s:4:"冬";s:3:"冬";s:4:"况";s:3:"况";s:4:"𩇟";s:4:"𩇟";s:4:"凵";s:3:"凵";s:4:"刃";s:3:"刃";s:4:"㓟";s:3:"㓟";s:4:"刻";s:3:"刻";s:4:"剆";s:3:"剆";s:4:"割";s:3:"割";s:4:"剷";s:3:"剷";s:4:"㔕";s:3:"㔕";s:4:"勇";s:3:"勇";s:4:"勉";s:3:"勉";s:4:"勤";s:3:"勤";s:4:"勺";s:3:"勺";s:4:"包";s:3:"包";s:4:"匆";s:3:"匆";s:4:"北";s:3:"北";s:4:"卉";s:3:"卉";s:4:"卑";s:3:"卑";s:4:"博";s:3:"博";s:4:"即";s:3:"即";s:4:"卽";s:3:"卽";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"𠨬";s:4:"𠨬";s:4:"灰";s:3:"灰";s:4:"及";s:3:"及";s:4:"叟";s:3:"叟";s:4:"𠭣";s:4:"𠭣";s:4:"叫";s:3:"叫";s:4:"叱";s:3:"叱";s:4:"吆";s:3:"吆";s:4:"咞";s:3:"咞";s:4:"吸";s:3:"吸";s:4:"呈";s:3:"呈";s:4:"周";s:3:"周";s:4:"咢";s:3:"咢";s:4:"哶";s:3:"哶";s:4:"唐";s:3:"唐";s:4:"啓";s:3:"啓";s:4:"啣";s:3:"啣";s:4:"善";s:3:"善";s:4:"善";s:3:"善";s:4:"喙";s:3:"喙";s:4:"喫";s:3:"喫";s:4:"喳";s:3:"喳";s:4:"嗂";s:3:"嗂";s:4:"圖";s:3:"圖";s:4:"嘆";s:3:"嘆";s:4:"圗";s:3:"圗";s:4:"噑";s:3:"噑";s:4:"噴";s:3:"噴";s:4:"切";s:3:"切";s:4:"壮";s:3:"壮";s:4:"城";s:3:"城";s:4:"埴";s:3:"埴";s:4:"堍";s:3:"堍";s:4:"型";s:3:"型";s:4:"堲";s:3:"堲";s:4:"報";s:3:"報";s:4:"墬";s:3:"墬";s:4:"𡓤";s:4:"𡓤";s:4:"売";s:3:"売";s:4:"壷";s:3:"壷";s:4:"夆";s:3:"夆";s:4:"多";s:3:"多";s:4:"夢";s:3:"夢";s:4:"奢";s:3:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:4:"姬";s:3:"姬";s:4:"娛";s:3:"娛";s:4:"娧";s:3:"娧";s:4:"姘";s:3:"姘";s:4:"婦";s:3:"婦";s:4:"㛮";s:3:"㛮";s:4:"㛼";s:3:"㛼";s:4:"嬈";s:3:"嬈";s:4:"嬾";s:3:"嬾";s:4:"嬾";s:3:"嬾";s:4:"𡧈";s:4:"𡧈";s:4:"寃";s:3:"寃";s:4:"寘";s:3:"寘";s:4:"寧";s:3:"寧";s:4:"寳";s:3:"寳";s:4:"𡬘";s:4:"𡬘";s:4:"寿";s:3:"寿";s:4:"将";s:3:"将";s:4:"当";s:3:"当";s:4:"尢";s:3:"尢";s:4:"㞁";s:3:"㞁";s:4:"屠";s:3:"屠";s:4:"屮";s:3:"屮";s:4:"峀";s:3:"峀";s:4:"岍";s:3:"岍";s:4:"𡷤";s:4:"𡷤";s:4:"嵃";s:3:"嵃";s:4:"𡷦";s:4:"𡷦";s:4:"嵮";s:3:"嵮";s:4:"嵫";s:3:"嵫";s:4:"嵼";s:3:"嵼";s:4:"巡";s:3:"巡";s:4:"巢";s:3:"巢";s:4:"㠯";s:3:"㠯";s:4:"巽";s:3:"巽";s:4:"帨";s:3:"帨";s:4:"帽";s:3:"帽";s:4:"幩";s:3:"幩";s:4:"㡢";s:3:"㡢";s:4:"𢆃";s:4:"𢆃";s:4:"㡼";s:3:"㡼";s:4:"庰";s:3:"庰";s:4:"庳";s:3:"庳";s:4:"庶";s:3:"庶";s:4:"廊";s:3:"廊";s:4:"𪎒";s:4:"𪎒";s:4:"廾";s:3:"廾";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"舁";s:3:"舁";s:4:"弢";s:3:"弢";s:4:"弢";s:3:"弢";s:4:"㣇";s:3:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:4:"形";s:3:"形";s:4:"彫";s:3:"彫";s:4:"㣣";s:3:"㣣";s:4:"徚";s:3:"徚";s:4:"忍";s:3:"忍";s:4:"志";s:3:"志";s:4:"忹";s:3:"忹";s:4:"悁";s:3:"悁";s:4:"㤺";s:3:"㤺";s:4:"㤜";s:3:"㤜";s:4:"悔";s:3:"悔";s:4:"𢛔";s:4:"𢛔";s:4:"惇";s:3:"惇";s:4:"慈";s:3:"慈";s:4:"慌";s:3:"慌";s:4:"慎";s:3:"慎";s:4:"慌";s:3:"慌";s:4:"慺";s:3:"慺";s:4:"憎";s:3:"憎";s:4:"憲";s:3:"憲";s:4:"憤";s:3:"憤";s:4:"憯";s:3:"憯";s:4:"懞";s:3:"懞";s:4:"懲";s:3:"懲";s:4:"懶";s:3:"懶";s:4:"成";s:3:"成";s:4:"戛";s:3:"戛";s:4:"扝";s:3:"扝";s:4:"抱";s:3:"抱";s:4:"拔";s:3:"拔";s:4:"捐";s:3:"捐";s:4:"𢬌";s:4:"𢬌";s:4:"挽";s:3:"挽";s:4:"拼";s:3:"拼";s:4:"捨";s:3:"捨";s:4:"掃";s:3:"掃";s:4:"揤";s:3:"揤";s:4:"𢯱";s:4:"𢯱";s:4:"搢";s:3:"搢";s:4:"揅";s:3:"揅";s:4:"掩";s:3:"掩";s:4:"㨮";s:3:"㨮";s:4:"摩";s:3:"摩";s:4:"摾";s:3:"摾";s:4:"撝";s:3:"撝";s:4:"摷";s:3:"摷";s:4:"㩬";s:3:"㩬";s:4:"敏";s:3:"敏";s:4:"敬";s:3:"敬";s:4:"𣀊";s:4:"𣀊";s:4:"旣";s:3:"旣";s:4:"書";s:3:"書";s:4:"晉";s:3:"晉";s:4:"㬙";s:3:"㬙";s:4:"暑";s:3:"暑";s:4:"㬈";s:3:"㬈";s:4:"㫤";s:3:"㫤";s:4:"冒";s:3:"冒";s:4:"冕";s:3:"冕";s:4:"最";s:3:"最";s:4:"暜";s:3:"暜";s:4:"肭";s:3:"肭";s:4:"䏙";s:3:"䏙";s:4:"朗";s:3:"朗";s:4:"望";s:3:"望";s:4:"朡";s:3:"朡";s:4:"杞";s:3:"杞";s:4:"杓";s:3:"杓";s:4:"𣏃";s:4:"𣏃";s:4:"㭉";s:3:"㭉";s:4:"柺";s:3:"柺";s:4:"枅";s:3:"枅";s:4:"桒";s:3:"桒";s:4:"梅";s:3:"梅";s:4:"𣑭";s:4:"𣑭";s:4:"梎";s:3:"梎";s:4:"栟";s:3:"栟";s:4:"椔";s:3:"椔";s:4:"㮝";s:3:"㮝";s:4:"楂";s:3:"楂";s:4:"榣";s:3:"榣";s:4:"槪";s:3:"槪";s:4:"檨";s:3:"檨";s:4:"𣚣";s:4:"𣚣";s:4:"櫛";s:3:"櫛";s:4:"㰘";s:3:"㰘";s:4:"次";s:3:"次";s:4:"𣢧";s:4:"𣢧";s:4:"歔";s:3:"歔";s:4:"㱎";s:3:"㱎";s:4:"歲";s:3:"歲";s:4:"殟";s:3:"殟";s:4:"殺";s:3:"殺";s:4:"殻";s:3:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:4:"汎";s:3:"汎";s:4:"𣲼";s:4:"𣲼";s:4:"沿";s:3:"沿";s:4:"泍";s:3:"泍";s:4:"汧";s:3:"汧";s:4:"洖";s:3:"洖";s:4:"派";s:3:"派";s:4:"海";s:3:"海";s:4:"流";s:3:"流";s:4:"浩";s:3:"浩";s:4:"浸";s:3:"浸";s:4:"涅";s:3:"涅";s:4:"𣴞";s:4:"𣴞";s:4:"洴";s:3:"洴";s:4:"港";s:3:"港";s:4:"湮";s:3:"湮";s:4:"㴳";s:3:"㴳";s:4:"滋";s:3:"滋";s:4:"滇";s:3:"滇";s:4:"𣻑";s:4:"𣻑";s:4:"淹";s:3:"淹";s:4:"潮";s:3:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:4:"濆";s:3:"濆";s:4:"瀹";s:3:"瀹";s:4:"瀞";s:3:"瀞";s:4:"瀛";s:3:"瀛";s:4:"㶖";s:3:"㶖";s:4:"灊";s:3:"灊";s:4:"災";s:3:"災";s:4:"灷";s:3:"灷";s:4:"炭";s:3:"炭";s:4:"𠔥";s:4:"𠔥";s:4:"煅";s:3:"煅";s:4:"𤉣";s:4:"𤉣";s:4:"熜";s:3:"熜";s:4:"𤎫";s:4:"𤎫";s:4:"爨";s:3:"爨";s:4:"爵";s:3:"爵";s:4:"牐";s:3:"牐";s:4:"𤘈";s:4:"𤘈";s:4:"犀";s:3:"犀";s:4:"犕";s:3:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:4:"獺";s:3:"獺";s:4:"王";s:3:"王";s:4:"㺬";s:3:"㺬";s:4:"玥";s:3:"玥";s:4:"㺸";s:3:"㺸";s:4:"㺸";s:3:"㺸";s:4:"瑇";s:3:"瑇";s:4:"瑜";s:3:"瑜";s:4:"瑱";s:3:"瑱";s:4:"璅";s:3:"璅";s:4:"瓊";s:3:"瓊";s:4:"㼛";s:3:"㼛";s:4:"甤";s:3:"甤";s:4:"𤰶";s:4:"𤰶";s:4:"甾";s:3:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"異";s:3:"異";s:4:"𢆟";s:4:"𢆟";s:4:"瘐";s:3:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:4:"㿼";s:3:"㿼";s:4:"䀈";s:3:"䀈";s:4:"直";s:3:"直";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:4:"眞";s:3:"眞";s:4:"真";s:3:"真";s:4:"真";s:3:"真";s:4:"睊";s:3:"睊";s:4:"䀹";s:3:"䀹";s:4:"瞋";s:3:"瞋";s:4:"䁆";s:3:"䁆";s:4:"䂖";s:3:"䂖";s:4:"𥐝";s:4:"𥐝";s:4:"硎";s:3:"硎";s:4:"碌";s:3:"碌";s:4:"磌";s:3:"磌";s:4:"䃣";s:3:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"祖";s:3:"祖";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:4:"福";s:3:"福";s:4:"秫";s:3:"秫";s:4:"䄯";s:3:"䄯";s:4:"穀";s:3:"穀";s:4:"穊";s:3:"穊";s:4:"穏";s:3:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"竮";s:3:"竮";s:4:"䈂";s:3:"䈂";s:4:"𥮫";s:4:"𥮫";s:4:"篆";s:3:"篆";s:4:"築";s:3:"築";s:4:"䈧";s:3:"䈧";s:4:"𥲀";s:4:"𥲀";s:4:"糒";s:3:"糒";s:4:"䊠";s:3:"䊠";s:4:"糨";s:3:"糨";s:4:"糣";s:3:"糣";s:4:"紀";s:3:"紀";s:4:"𥾆";s:4:"𥾆";s:4:"絣";s:3:"絣";s:4:"䌁";s:3:"䌁";s:4:"緇";s:3:"緇";s:4:"縂";s:3:"縂";s:4:"繅";s:3:"繅";s:4:"䌴";s:3:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:4:"䍙";s:3:"䍙";s:4:"𦋙";s:4:"𦋙";s:4:"罺";s:3:"罺";s:4:"𦌾";s:4:"𦌾";s:4:"羕";s:3:"羕";s:4:"翺";s:3:"翺";s:4:"者";s:3:"者";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:4:"聠";s:3:"聠";s:4:"𦖨";s:4:"𦖨";s:4:"聰";s:3:"聰";s:4:"𣍟";s:4:"𣍟";s:4:"䏕";s:3:"䏕";s:4:"育";s:3:"育";s:4:"脃";s:3:"脃";s:4:"䐋";s:3:"䐋";s:4:"脾";s:3:"脾";s:4:"媵";s:3:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:4:"舁";s:3:"舁";s:4:"舄";s:3:"舄";s:4:"辞";s:3:"辞";s:4:"䑫";s:3:"䑫";s:4:"芑";s:3:"芑";s:4:"芋";s:3:"芋";s:4:"芝";s:3:"芝";s:4:"劳";s:3:"劳";s:4:"花";s:3:"花";s:4:"芳";s:3:"芳";s:4:"芽";s:3:"芽";s:4:"苦";s:3:"苦";s:4:"𦬼";s:4:"𦬼";s:4:"若";s:3:"若";s:4:"茝";s:3:"茝";s:4:"荣";s:3:"荣";s:4:"莭";s:3:"莭";s:4:"茣";s:3:"茣";s:4:"莽";s:3:"莽";s:4:"菧";s:3:"菧";s:4:"著";s:3:"著";s:4:"荓";s:3:"荓";s:4:"菊";s:3:"菊";s:4:"菌";s:3:"菌";s:4:"菜";s:3:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:4:"䔫";s:3:"䔫";s:4:"蓱";s:3:"蓱";s:4:"蓳";s:3:"蓳";s:4:"蔖";s:3:"蔖";s:4:"𧏊";s:4:"𧏊";s:4:"蕤";s:3:"蕤";s:4:"𦼬";s:4:"𦼬";s:4:"䕝";s:3:"䕝";s:4:"䕡";s:3:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:4:"䕫";s:3:"䕫";s:4:"虐";s:3:"虐";s:4:"虜";s:3:"虜";s:4:"虧";s:3:"虧";s:4:"虩";s:3:"虩";s:4:"蚩";s:3:"蚩";s:4:"蚈";s:3:"蚈";s:4:"蜎";s:3:"蜎";s:4:"蛢";s:3:"蛢";s:4:"蝹";s:3:"蝹";s:4:"蜨";s:3:"蜨";s:4:"蝫";s:3:"蝫";s:4:"螆";s:3:"螆";s:4:"䗗";s:3:"䗗";s:4:"蟡";s:3:"蟡";s:4:"蠁";s:3:"蠁";s:4:"䗹";s:3:"䗹";s:4:"衠";s:3:"衠";s:4:"衣";s:3:"衣";s:4:"𧙧";s:4:"𧙧";s:4:"裗";s:3:"裗";s:4:"裞";s:3:"裞";s:4:"䘵";s:3:"䘵";s:4:"裺";s:3:"裺";s:4:"㒻";s:3:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:4:"䚾";s:3:"䚾";s:4:"䛇";s:3:"䛇";s:4:"誠";s:3:"誠";s:4:"諭";s:3:"諭";s:4:"變";s:3:"變";s:4:"豕";s:3:"豕";s:4:"𧲨";s:4:"𧲨";s:4:"貫";s:3:"貫";s:4:"賁";s:3:"賁";s:4:"贛";s:3:"贛";s:4:"起";s:3:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:4:"跋";s:3:"跋";s:4:"趼";s:3:"趼";s:4:"跰";s:3:"跰";s:4:"𠣞";s:4:"𠣞";s:4:"軔";s:3:"軔";s:4:"輸";s:3:"輸";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:4:"邔";s:3:"邔";s:4:"郱";s:3:"郱";s:4:"鄑";s:3:"鄑";s:4:"𨜮";s:4:"𨜮";s:4:"鄛";s:3:"鄛";s:4:"鈸";s:3:"鈸";s:4:"鋗";s:3:"鋗";s:4:"鋘";s:3:"鋘";s:4:"鉼";s:3:"鉼";s:4:"鏹";s:3:"鏹";s:4:"鐕";s:3:"鐕";s:4:"𨯺";s:4:"𨯺";s:4:"開";s:3:"開";s:4:"䦕";s:3:"䦕";s:4:"閷";s:3:"閷";s:4:"𨵷";s:4:"𨵷";s:4:"䧦";s:3:"䧦";s:4:"雃";s:3:"雃";s:4:"嶲";s:3:"嶲";s:4:"霣";s:3:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:4:"䩮";s:3:"䩮";s:4:"䩶";s:3:"䩶";s:4:"韠";s:3:"韠";s:4:"𩐊";s:4:"𩐊";s:4:"䪲";s:3:"䪲";s:4:"𩒖";s:4:"𩒖";s:4:"頋";s:3:"頋";s:4:"頋";s:3:"頋";s:4:"頩";s:3:"頩";s:4:"𩖶";s:4:"𩖶";s:4:"飢";s:3:"飢";s:4:"䬳";s:3:"䬳";s:4:"餩";s:3:"餩";s:4:"馧";s:3:"馧";s:4:"駂";s:3:"駂";s:4:"駾";s:3:"駾";s:4:"䯎";s:3:"䯎";s:4:"𩬰";s:4:"𩬰";s:4:"鬒";s:3:"鬒";s:4:"鱀";s:3:"鱀";s:4:"鳽";s:3:"鳽";s:4:"䳎";s:3:"䳎";s:4:"䳭";s:3:"䳭";s:4:"鵧";s:3:"鵧";s:4:"𪃎";s:4:"𪃎";s:4:"䳸";s:3:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:4:"麻";s:3:"麻";s:4:"䵖";s:3:"䵖";s:4:"黹";s:3:"黹";s:4:"黾";s:3:"黾";s:4:"鼅";s:3:"鼅";s:4:"鼏";s:3:"鼏";s:4:"鼖";s:3:"鼖";s:4:"鼻";s:3:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
+$utfCheckNFC = unserialize( 'a:1217:{s:2:"̀";s:1:"N";s:2:"́";s:1:"N";s:2:"̓";s:1:"N";s:2:"̈́";s:1:"N";s:2:"ʹ";s:1:"N";s:2:";";s:1:"N";s:2:"·";s:1:"N";s:3:"क़";s:1:"N";s:3:"ख़";s:1:"N";s:3:"ग़";s:1:"N";s:3:"ज़";s:1:"N";s:3:"ड़";s:1:"N";s:3:"ढ़";s:1:"N";s:3:"फ़";s:1:"N";s:3:"य़";s:1:"N";s:3:"ড়";s:1:"N";s:3:"ঢ়";s:1:"N";s:3:"য়";s:1:"N";s:3:"ਲ਼";s:1:"N";s:3:"ਸ਼";s:1:"N";s:3:"ਖ਼";s:1:"N";s:3:"ਗ਼";s:1:"N";s:3:"ਜ਼";s:1:"N";s:3:"ਫ਼";s:1:"N";s:3:"ଡ଼";s:1:"N";s:3:"ଢ଼";s:1:"N";s:3:"གྷ";s:1:"N";s:3:"ཌྷ";s:1:"N";s:3:"དྷ";s:1:"N";s:3:"བྷ";s:1:"N";s:3:"ཛྷ";s:1:"N";s:3:"ཀྵ";s:1:"N";s:3:"ཱི";s:1:"N";s:3:"ཱུ";s:1:"N";s:3:"ྲྀ";s:1:"N";s:3:"ླྀ";s:1:"N";s:3:"ཱྀ";s:1:"N";s:3:"ྒྷ";s:1:"N";s:3:"ྜྷ";s:1:"N";s:3:"ྡྷ";s:1:"N";s:3:"ྦྷ";s:1:"N";s:3:"ྫྷ";s:1:"N";s:3:"ྐྵ";s:1:"N";s:3:"ά";s:1:"N";s:3:"έ";s:1:"N";s:3:"ή";s:1:"N";s:3:"ί";s:1:"N";s:3:"ό";s:1:"N";s:3:"ύ";s:1:"N";s:3:"ώ";s:1:"N";s:3:"Ά";s:1:"N";s:3:"ι";s:1:"N";s:3:"Έ";s:1:"N";s:3:"Ή";s:1:"N";s:3:"ΐ";s:1:"N";s:3:"Ί";s:1:"N";s:3:"ΰ";s:1:"N";s:3:"Ύ";s:1:"N";s:3:"΅";s:1:"N";s:3:"`";s:1:"N";s:3:"Ό";s:1:"N";s:3:"Ώ";s:1:"N";s:3:"´";s:1:"N";s:3:" ";s:1:"N";s:3:" ";s:1:"N";s:3:"Ω";s:1:"N";s:3:"K";s:1:"N";s:3:"Å";s:1:"N";s:3:"〈";s:1:"N";s:3:"〉";s:1:"N";s:3:"⫝̸";s:1:"N";s:3:"豈";s:1:"N";s:3:"更";s:1:"N";s:3:"車";s:1:"N";s:3:"賈";s:1:"N";s:3:"滑";s:1:"N";s:3:"串";s:1:"N";s:3:"句";s:1:"N";s:3:"龜";s:1:"N";s:3:"龜";s:1:"N";s:3:"契";s:1:"N";s:3:"金";s:1:"N";s:3:"喇";s:1:"N";s:3:"奈";s:1:"N";s:3:"懶";s:1:"N";s:3:"癩";s:1:"N";s:3:"羅";s:1:"N";s:3:"蘿";s:1:"N";s:3:"螺";s:1:"N";s:3:"裸";s:1:"N";s:3:"邏";s:1:"N";s:3:"樂";s:1:"N";s:3:"洛";s:1:"N";s:3:"烙";s:1:"N";s:3:"珞";s:1:"N";s:3:"落";s:1:"N";s:3:"酪";s:1:"N";s:3:"駱";s:1:"N";s:3:"亂";s:1:"N";s:3:"卵";s:1:"N";s:3:"欄";s:1:"N";s:3:"爛";s:1:"N";s:3:"蘭";s:1:"N";s:3:"鸞";s:1:"N";s:3:"嵐";s:1:"N";s:3:"濫";s:1:"N";s:3:"藍";s:1:"N";s:3:"襤";s:1:"N";s:3:"拉";s:1:"N";s:3:"臘";s:1:"N";s:3:"蠟";s:1:"N";s:3:"廊";s:1:"N";s:3:"朗";s:1:"N";s:3:"浪";s:1:"N";s:3:"狼";s:1:"N";s:3:"郎";s:1:"N";s:3:"來";s:1:"N";s:3:"冷";s:1:"N";s:3:"勞";s:1:"N";s:3:"擄";s:1:"N";s:3:"櫓";s:1:"N";s:3:"爐";s:1:"N";s:3:"盧";s:1:"N";s:3:"老";s:1:"N";s:3:"蘆";s:1:"N";s:3:"虜";s:1:"N";s:3:"路";s:1:"N";s:3:"露";s:1:"N";s:3:"魯";s:1:"N";s:3:"鷺";s:1:"N";s:3:"碌";s:1:"N";s:3:"祿";s:1:"N";s:3:"綠";s:1:"N";s:3:"菉";s:1:"N";s:3:"錄";s:1:"N";s:3:"鹿";s:1:"N";s:3:"論";s:1:"N";s:3:"壟";s:1:"N";s:3:"弄";s:1:"N";s:3:"籠";s:1:"N";s:3:"聾";s:1:"N";s:3:"牢";s:1:"N";s:3:"磊";s:1:"N";s:3:"賂";s:1:"N";s:3:"雷";s:1:"N";s:3:"壘";s:1:"N";s:3:"屢";s:1:"N";s:3:"樓";s:1:"N";s:3:"淚";s:1:"N";s:3:"漏";s:1:"N";s:3:"累";s:1:"N";s:3:"縷";s:1:"N";s:3:"陋";s:1:"N";s:3:"勒";s:1:"N";s:3:"肋";s:1:"N";s:3:"凜";s:1:"N";s:3:"凌";s:1:"N";s:3:"稜";s:1:"N";s:3:"綾";s:1:"N";s:3:"菱";s:1:"N";s:3:"陵";s:1:"N";s:3:"讀";s:1:"N";s:3:"拏";s:1:"N";s:3:"樂";s:1:"N";s:3:"諾";s:1:"N";s:3:"丹";s:1:"N";s:3:"寧";s:1:"N";s:3:"怒";s:1:"N";s:3:"率";s:1:"N";s:3:"異";s:1:"N";s:3:"北";s:1:"N";s:3:"磻";s:1:"N";s:3:"便";s:1:"N";s:3:"復";s:1:"N";s:3:"不";s:1:"N";s:3:"泌";s:1:"N";s:3:"數";s:1:"N";s:3:"索";s:1:"N";s:3:"參";s:1:"N";s:3:"塞";s:1:"N";s:3:"省";s:1:"N";s:3:"葉";s:1:"N";s:3:"說";s:1:"N";s:3:"殺";s:1:"N";s:3:"辰";s:1:"N";s:3:"沈";s:1:"N";s:3:"拾";s:1:"N";s:3:"若";s:1:"N";s:3:"掠";s:1:"N";s:3:"略";s:1:"N";s:3:"亮";s:1:"N";s:3:"兩";s:1:"N";s:3:"凉";s:1:"N";s:3:"梁";s:1:"N";s:3:"糧";s:1:"N";s:3:"良";s:1:"N";s:3:"諒";s:1:"N";s:3:"量";s:1:"N";s:3:"勵";s:1:"N";s:3:"呂";s:1:"N";s:3:"女";s:1:"N";s:3:"廬";s:1:"N";s:3:"旅";s:1:"N";s:3:"濾";s:1:"N";s:3:"礪";s:1:"N";s:3:"閭";s:1:"N";s:3:"驪";s:1:"N";s:3:"麗";s:1:"N";s:3:"黎";s:1:"N";s:3:"力";s:1:"N";s:3:"曆";s:1:"N";s:3:"歷";s:1:"N";s:3:"轢";s:1:"N";s:3:"年";s:1:"N";s:3:"憐";s:1:"N";s:3:"戀";s:1:"N";s:3:"撚";s:1:"N";s:3:"漣";s:1:"N";s:3:"煉";s:1:"N";s:3:"璉";s:1:"N";s:3:"秊";s:1:"N";s:3:"練";s:1:"N";s:3:"聯";s:1:"N";s:3:"輦";s:1:"N";s:3:"蓮";s:1:"N";s:3:"連";s:1:"N";s:3:"鍊";s:1:"N";s:3:"列";s:1:"N";s:3:"劣";s:1:"N";s:3:"咽";s:1:"N";s:3:"烈";s:1:"N";s:3:"裂";s:1:"N";s:3:"說";s:1:"N";s:3:"廉";s:1:"N";s:3:"念";s:1:"N";s:3:"捻";s:1:"N";s:3:"殮";s:1:"N";s:3:"簾";s:1:"N";s:3:"獵";s:1:"N";s:3:"令";s:1:"N";s:3:"囹";s:1:"N";s:3:"寧";s:1:"N";s:3:"嶺";s:1:"N";s:3:"怜";s:1:"N";s:3:"玲";s:1:"N";s:3:"瑩";s:1:"N";s:3:"羚";s:1:"N";s:3:"聆";s:1:"N";s:3:"鈴";s:1:"N";s:3:"零";s:1:"N";s:3:"靈";s:1:"N";s:3:"領";s:1:"N";s:3:"例";s:1:"N";s:3:"禮";s:1:"N";s:3:"醴";s:1:"N";s:3:"隸";s:1:"N";s:3:"惡";s:1:"N";s:3:"了";s:1:"N";s:3:"僚";s:1:"N";s:3:"寮";s:1:"N";s:3:"尿";s:1:"N";s:3:"料";s:1:"N";s:3:"樂";s:1:"N";s:3:"燎";s:1:"N";s:3:"療";s:1:"N";s:3:"蓼";s:1:"N";s:3:"遼";s:1:"N";s:3:"龍";s:1:"N";s:3:"暈";s:1:"N";s:3:"阮";s:1:"N";s:3:"劉";s:1:"N";s:3:"杻";s:1:"N";s:3:"柳";s:1:"N";s:3:"流";s:1:"N";s:3:"溜";s:1:"N";s:3:"琉";s:1:"N";s:3:"留";s:1:"N";s:3:"硫";s:1:"N";s:3:"紐";s:1:"N";s:3:"類";s:1:"N";s:3:"六";s:1:"N";s:3:"戮";s:1:"N";s:3:"陸";s:1:"N";s:3:"倫";s:1:"N";s:3:"崙";s:1:"N";s:3:"淪";s:1:"N";s:3:"輪";s:1:"N";s:3:"律";s:1:"N";s:3:"慄";s:1:"N";s:3:"栗";s:1:"N";s:3:"率";s:1:"N";s:3:"隆";s:1:"N";s:3:"利";s:1:"N";s:3:"吏";s:1:"N";s:3:"履";s:1:"N";s:3:"易";s:1:"N";s:3:"李";s:1:"N";s:3:"梨";s:1:"N";s:3:"泥";s:1:"N";s:3:"理";s:1:"N";s:3:"痢";s:1:"N";s:3:"罹";s:1:"N";s:3:"裏";s:1:"N";s:3:"裡";s:1:"N";s:3:"里";s:1:"N";s:3:"離";s:1:"N";s:3:"匿";s:1:"N";s:3:"溺";s:1:"N";s:3:"吝";s:1:"N";s:3:"燐";s:1:"N";s:3:"璘";s:1:"N";s:3:"藺";s:1:"N";s:3:"隣";s:1:"N";s:3:"鱗";s:1:"N";s:3:"麟";s:1:"N";s:3:"林";s:1:"N";s:3:"淋";s:1:"N";s:3:"臨";s:1:"N";s:3:"立";s:1:"N";s:3:"笠";s:1:"N";s:3:"粒";s:1:"N";s:3:"狀";s:1:"N";s:3:"炙";s:1:"N";s:3:"識";s:1:"N";s:3:"什";s:1:"N";s:3:"茶";s:1:"N";s:3:"刺";s:1:"N";s:3:"切";s:1:"N";s:3:"度";s:1:"N";s:3:"拓";s:1:"N";s:3:"糖";s:1:"N";s:3:"宅";s:1:"N";s:3:"洞";s:1:"N";s:3:"暴";s:1:"N";s:3:"輻";s:1:"N";s:3:"行";s:1:"N";s:3:"降";s:1:"N";s:3:"見";s:1:"N";s:3:"廓";s:1:"N";s:3:"兀";s:1:"N";s:3:"嗀";s:1:"N";s:3:"塚";s:1:"N";s:3:"晴";s:1:"N";s:3:"凞";s:1:"N";s:3:"猪";s:1:"N";s:3:"益";s:1:"N";s:3:"礼";s:1:"N";s:3:"神";s:1:"N";s:3:"祥";s:1:"N";s:3:"福";s:1:"N";s:3:"靖";s:1:"N";s:3:"精";s:1:"N";s:3:"羽";s:1:"N";s:3:"蘒";s:1:"N";s:3:"諸";s:1:"N";s:3:"逸";s:1:"N";s:3:"都";s:1:"N";s:3:"飯";s:1:"N";s:3:"飼";s:1:"N";s:3:"館";s:1:"N";s:3:"鶴";s:1:"N";s:3:"侮";s:1:"N";s:3:"僧";s:1:"N";s:3:"免";s:1:"N";s:3:"勉";s:1:"N";s:3:"勤";s:1:"N";s:3:"卑";s:1:"N";s:3:"喝";s:1:"N";s:3:"嘆";s:1:"N";s:3:"器";s:1:"N";s:3:"塀";s:1:"N";s:3:"墨";s:1:"N";s:3:"層";s:1:"N";s:3:"屮";s:1:"N";s:3:"悔";s:1:"N";s:3:"慨";s:1:"N";s:3:"憎";s:1:"N";s:3:"懲";s:1:"N";s:3:"敏";s:1:"N";s:3:"既";s:1:"N";s:3:"暑";s:1:"N";s:3:"梅";s:1:"N";s:3:"海";s:1:"N";s:3:"渚";s:1:"N";s:3:"漢";s:1:"N";s:3:"煮";s:1:"N";s:3:"爫";s:1:"N";s:3:"琢";s:1:"N";s:3:"碑";s:1:"N";s:3:"社";s:1:"N";s:3:"祉";s:1:"N";s:3:"祈";s:1:"N";s:3:"祐";s:1:"N";s:3:"祖";s:1:"N";s:3:"祝";s:1:"N";s:3:"禍";s:1:"N";s:3:"禎";s:1:"N";s:3:"穀";s:1:"N";s:3:"突";s:1:"N";s:3:"節";s:1:"N";s:3:"練";s:1:"N";s:3:"縉";s:1:"N";s:3:"繁";s:1:"N";s:3:"署";s:1:"N";s:3:"者";s:1:"N";s:3:"臭";s:1:"N";s:3:"艹";s:1:"N";s:3:"艹";s:1:"N";s:3:"著";s:1:"N";s:3:"褐";s:1:"N";s:3:"視";s:1:"N";s:3:"謁";s:1:"N";s:3:"謹";s:1:"N";s:3:"賓";s:1:"N";s:3:"贈";s:1:"N";s:3:"辶";s:1:"N";s:3:"逸";s:1:"N";s:3:"難";s:1:"N";s:3:"響";s:1:"N";s:3:"頻";s:1:"N";s:3:"並";s:1:"N";s:3:"况";s:1:"N";s:3:"全";s:1:"N";s:3:"侀";s:1:"N";s:3:"充";s:1:"N";s:3:"冀";s:1:"N";s:3:"勇";s:1:"N";s:3:"勺";s:1:"N";s:3:"喝";s:1:"N";s:3:"啕";s:1:"N";s:3:"喙";s:1:"N";s:3:"嗢";s:1:"N";s:3:"塚";s:1:"N";s:3:"墳";s:1:"N";s:3:"奄";s:1:"N";s:3:"奔";s:1:"N";s:3:"婢";s:1:"N";s:3:"嬨";s:1:"N";s:3:"廒";s:1:"N";s:3:"廙";s:1:"N";s:3:"彩";s:1:"N";s:3:"徭";s:1:"N";s:3:"惘";s:1:"N";s:3:"慎";s:1:"N";s:3:"愈";s:1:"N";s:3:"憎";s:1:"N";s:3:"慠";s:1:"N";s:3:"懲";s:1:"N";s:3:"戴";s:1:"N";s:3:"揄";s:1:"N";s:3:"搜";s:1:"N";s:3:"摒";s:1:"N";s:3:"敖";s:1:"N";s:3:"晴";s:1:"N";s:3:"朗";s:1:"N";s:3:"望";s:1:"N";s:3:"杖";s:1:"N";s:3:"歹";s:1:"N";s:3:"殺";s:1:"N";s:3:"流";s:1:"N";s:3:"滛";s:1:"N";s:3:"滋";s:1:"N";s:3:"漢";s:1:"N";s:3:"瀞";s:1:"N";s:3:"煮";s:1:"N";s:3:"瞧";s:1:"N";s:3:"爵";s:1:"N";s:3:"犯";s:1:"N";s:3:"猪";s:1:"N";s:3:"瑱";s:1:"N";s:3:"甆";s:1:"N";s:3:"画";s:1:"N";s:3:"瘝";s:1:"N";s:3:"瘟";s:1:"N";s:3:"益";s:1:"N";s:3:"盛";s:1:"N";s:3:"直";s:1:"N";s:3:"睊";s:1:"N";s:3:"着";s:1:"N";s:3:"磌";s:1:"N";s:3:"窱";s:1:"N";s:3:"節";s:1:"N";s:3:"类";s:1:"N";s:3:"絛";s:1:"N";s:3:"練";s:1:"N";s:3:"缾";s:1:"N";s:3:"者";s:1:"N";s:3:"荒";s:1:"N";s:3:"華";s:1:"N";s:3:"蝹";s:1:"N";s:3:"襁";s:1:"N";s:3:"覆";s:1:"N";s:3:"視";s:1:"N";s:3:"調";s:1:"N";s:3:"諸";s:1:"N";s:3:"請";s:1:"N";s:3:"謁";s:1:"N";s:3:"諾";s:1:"N";s:3:"諭";s:1:"N";s:3:"謹";s:1:"N";s:3:"變";s:1:"N";s:3:"贈";s:1:"N";s:3:"輸";s:1:"N";s:3:"遲";s:1:"N";s:3:"醙";s:1:"N";s:3:"鉶";s:1:"N";s:3:"陼";s:1:"N";s:3:"難";s:1:"N";s:3:"靖";s:1:"N";s:3:"韛";s:1:"N";s:3:"響";s:1:"N";s:3:"頋";s:1:"N";s:3:"頻";s:1:"N";s:3:"鬒";s:1:"N";s:3:"龜";s:1:"N";s:3:"𢡊";s:1:"N";s:3:"𢡄";s:1:"N";s:3:"𣏕";s:1:"N";s:3:"㮝";s:1:"N";s:3:"䀘";s:1:"N";s:3:"䀹";s:1:"N";s:3:"𥉉";s:1:"N";s:3:"𥳐";s:1:"N";s:3:"𧻓";s:1:"N";s:3:"齃";s:1:"N";s:3:"龎";s:1:"N";s:3:"יִ";s:1:"N";s:3:"ײַ";s:1:"N";s:3:"שׁ";s:1:"N";s:3:"שׂ";s:1:"N";s:3:"שּׁ";s:1:"N";s:3:"שּׂ";s:1:"N";s:3:"אַ";s:1:"N";s:3:"אָ";s:1:"N";s:3:"אּ";s:1:"N";s:3:"בּ";s:1:"N";s:3:"גּ";s:1:"N";s:3:"דּ";s:1:"N";s:3:"הּ";s:1:"N";s:3:"וּ";s:1:"N";s:3:"זּ";s:1:"N";s:3:"טּ";s:1:"N";s:3:"יּ";s:1:"N";s:3:"ךּ";s:1:"N";s:3:"כּ";s:1:"N";s:3:"לּ";s:1:"N";s:3:"מּ";s:1:"N";s:3:"נּ";s:1:"N";s:3:"סּ";s:1:"N";s:3:"ףּ";s:1:"N";s:3:"פּ";s:1:"N";s:3:"צּ";s:1:"N";s:3:"קּ";s:1:"N";s:3:"רּ";s:1:"N";s:3:"שּ";s:1:"N";s:3:"תּ";s:1:"N";s:3:"וֹ";s:1:"N";s:3:"בֿ";s:1:"N";s:3:"כֿ";s:1:"N";s:3:"פֿ";s:1:"N";s:4:"𝅗𝅥";s:1:"N";s:4:"𝅘𝅥";s:1:"N";s:4:"𝅘𝅥𝅮";s:1:"N";s:4:"𝅘𝅥𝅯";s:1:"N";s:4:"𝅘𝅥𝅰";s:1:"N";s:4:"𝅘𝅥𝅱";s:1:"N";s:4:"𝅘𝅥𝅲";s:1:"N";s:4:"𝆹𝅥";s:1:"N";s:4:"𝆺𝅥";s:1:"N";s:4:"𝆹𝅥𝅮";s:1:"N";s:4:"𝆺𝅥𝅮";s:1:"N";s:4:"𝆹𝅥𝅯";s:1:"N";s:4:"𝆺𝅥𝅯";s:1:"N";s:4:"丽";s:1:"N";s:4:"丸";s:1:"N";s:4:"乁";s:1:"N";s:4:"𠄢";s:1:"N";s:4:"你";s:1:"N";s:4:"侮";s:1:"N";s:4:"侻";s:1:"N";s:4:"倂";s:1:"N";s:4:"偺";s:1:"N";s:4:"備";s:1:"N";s:4:"僧";s:1:"N";s:4:"像";s:1:"N";s:4:"㒞";s:1:"N";s:4:"𠘺";s:1:"N";s:4:"免";s:1:"N";s:4:"兔";s:1:"N";s:4:"兤";s:1:"N";s:4:"具";s:1:"N";s:4:"𠔜";s:1:"N";s:4:"㒹";s:1:"N";s:4:"內";s:1:"N";s:4:"再";s:1:"N";s:4:"𠕋";s:1:"N";s:4:"冗";s:1:"N";s:4:"冤";s:1:"N";s:4:"仌";s:1:"N";s:4:"冬";s:1:"N";s:4:"况";s:1:"N";s:4:"𩇟";s:1:"N";s:4:"凵";s:1:"N";s:4:"刃";s:1:"N";s:4:"㓟";s:1:"N";s:4:"刻";s:1:"N";s:4:"剆";s:1:"N";s:4:"割";s:1:"N";s:4:"剷";s:1:"N";s:4:"㔕";s:1:"N";s:4:"勇";s:1:"N";s:4:"勉";s:1:"N";s:4:"勤";s:1:"N";s:4:"勺";s:1:"N";s:4:"包";s:1:"N";s:4:"匆";s:1:"N";s:4:"北";s:1:"N";s:4:"卉";s:1:"N";s:4:"卑";s:1:"N";s:4:"博";s:1:"N";s:4:"即";s:1:"N";s:4:"卽";s:1:"N";s:4:"卿";s:1:"N";s:4:"卿";s:1:"N";s:4:"卿";s:1:"N";s:4:"𠨬";s:1:"N";s:4:"灰";s:1:"N";s:4:"及";s:1:"N";s:4:"叟";s:1:"N";s:4:"𠭣";s:1:"N";s:4:"叫";s:1:"N";s:4:"叱";s:1:"N";s:4:"吆";s:1:"N";s:4:"咞";s:1:"N";s:4:"吸";s:1:"N";s:4:"呈";s:1:"N";s:4:"周";s:1:"N";s:4:"咢";s:1:"N";s:4:"哶";s:1:"N";s:4:"唐";s:1:"N";s:4:"啓";s:1:"N";s:4:"啣";s:1:"N";s:4:"善";s:1:"N";s:4:"善";s:1:"N";s:4:"喙";s:1:"N";s:4:"喫";s:1:"N";s:4:"喳";s:1:"N";s:4:"嗂";s:1:"N";s:4:"圖";s:1:"N";s:4:"嘆";s:1:"N";s:4:"圗";s:1:"N";s:4:"噑";s:1:"N";s:4:"噴";s:1:"N";s:4:"切";s:1:"N";s:4:"壮";s:1:"N";s:4:"城";s:1:"N";s:4:"埴";s:1:"N";s:4:"堍";s:1:"N";s:4:"型";s:1:"N";s:4:"堲";s:1:"N";s:4:"報";s:1:"N";s:4:"墬";s:1:"N";s:4:"𡓤";s:1:"N";s:4:"売";s:1:"N";s:4:"壷";s:1:"N";s:4:"夆";s:1:"N";s:4:"多";s:1:"N";s:4:"夢";s:1:"N";s:4:"奢";s:1:"N";s:4:"𡚨";s:1:"N";s:4:"𡛪";s:1:"N";s:4:"姬";s:1:"N";s:4:"娛";s:1:"N";s:4:"娧";s:1:"N";s:4:"姘";s:1:"N";s:4:"婦";s:1:"N";s:4:"㛮";s:1:"N";s:4:"㛼";s:1:"N";s:4:"嬈";s:1:"N";s:4:"嬾";s:1:"N";s:4:"嬾";s:1:"N";s:4:"𡧈";s:1:"N";s:4:"寃";s:1:"N";s:4:"寘";s:1:"N";s:4:"寧";s:1:"N";s:4:"寳";s:1:"N";s:4:"𡬘";s:1:"N";s:4:"寿";s:1:"N";s:4:"将";s:1:"N";s:4:"当";s:1:"N";s:4:"尢";s:1:"N";s:4:"㞁";s:1:"N";s:4:"屠";s:1:"N";s:4:"屮";s:1:"N";s:4:"峀";s:1:"N";s:4:"岍";s:1:"N";s:4:"𡷤";s:1:"N";s:4:"嵃";s:1:"N";s:4:"𡷦";s:1:"N";s:4:"嵮";s:1:"N";s:4:"嵫";s:1:"N";s:4:"嵼";s:1:"N";s:4:"巡";s:1:"N";s:4:"巢";s:1:"N";s:4:"㠯";s:1:"N";s:4:"巽";s:1:"N";s:4:"帨";s:1:"N";s:4:"帽";s:1:"N";s:4:"幩";s:1:"N";s:4:"㡢";s:1:"N";s:4:"𢆃";s:1:"N";s:4:"㡼";s:1:"N";s:4:"庰";s:1:"N";s:4:"庳";s:1:"N";s:4:"庶";s:1:"N";s:4:"廊";s:1:"N";s:4:"𪎒";s:1:"N";s:4:"廾";s:1:"N";s:4:"𢌱";s:1:"N";s:4:"𢌱";s:1:"N";s:4:"舁";s:1:"N";s:4:"弢";s:1:"N";s:4:"弢";s:1:"N";s:4:"㣇";s:1:"N";s:4:"𣊸";s:1:"N";s:4:"𦇚";s:1:"N";s:4:"形";s:1:"N";s:4:"彫";s:1:"N";s:4:"㣣";s:1:"N";s:4:"徚";s:1:"N";s:4:"忍";s:1:"N";s:4:"志";s:1:"N";s:4:"忹";s:1:"N";s:4:"悁";s:1:"N";s:4:"㤺";s:1:"N";s:4:"㤜";s:1:"N";s:4:"悔";s:1:"N";s:4:"𢛔";s:1:"N";s:4:"惇";s:1:"N";s:4:"慈";s:1:"N";s:4:"慌";s:1:"N";s:4:"慎";s:1:"N";s:4:"慌";s:1:"N";s:4:"慺";s:1:"N";s:4:"憎";s:1:"N";s:4:"憲";s:1:"N";s:4:"憤";s:1:"N";s:4:"憯";s:1:"N";s:4:"懞";s:1:"N";s:4:"懲";s:1:"N";s:4:"懶";s:1:"N";s:4:"成";s:1:"N";s:4:"戛";s:1:"N";s:4:"扝";s:1:"N";s:4:"抱";s:1:"N";s:4:"拔";s:1:"N";s:4:"捐";s:1:"N";s:4:"𢬌";s:1:"N";s:4:"挽";s:1:"N";s:4:"拼";s:1:"N";s:4:"捨";s:1:"N";s:4:"掃";s:1:"N";s:4:"揤";s:1:"N";s:4:"𢯱";s:1:"N";s:4:"搢";s:1:"N";s:4:"揅";s:1:"N";s:4:"掩";s:1:"N";s:4:"㨮";s:1:"N";s:4:"摩";s:1:"N";s:4:"摾";s:1:"N";s:4:"撝";s:1:"N";s:4:"摷";s:1:"N";s:4:"㩬";s:1:"N";s:4:"敏";s:1:"N";s:4:"敬";s:1:"N";s:4:"𣀊";s:1:"N";s:4:"旣";s:1:"N";s:4:"書";s:1:"N";s:4:"晉";s:1:"N";s:4:"㬙";s:1:"N";s:4:"暑";s:1:"N";s:4:"㬈";s:1:"N";s:4:"㫤";s:1:"N";s:4:"冒";s:1:"N";s:4:"冕";s:1:"N";s:4:"最";s:1:"N";s:4:"暜";s:1:"N";s:4:"肭";s:1:"N";s:4:"䏙";s:1:"N";s:4:"朗";s:1:"N";s:4:"望";s:1:"N";s:4:"朡";s:1:"N";s:4:"杞";s:1:"N";s:4:"杓";s:1:"N";s:4:"𣏃";s:1:"N";s:4:"㭉";s:1:"N";s:4:"柺";s:1:"N";s:4:"枅";s:1:"N";s:4:"桒";s:1:"N";s:4:"梅";s:1:"N";s:4:"𣑭";s:1:"N";s:4:"梎";s:1:"N";s:4:"栟";s:1:"N";s:4:"椔";s:1:"N";s:4:"㮝";s:1:"N";s:4:"楂";s:1:"N";s:4:"榣";s:1:"N";s:4:"槪";s:1:"N";s:4:"檨";s:1:"N";s:4:"𣚣";s:1:"N";s:4:"櫛";s:1:"N";s:4:"㰘";s:1:"N";s:4:"次";s:1:"N";s:4:"𣢧";s:1:"N";s:4:"歔";s:1:"N";s:4:"㱎";s:1:"N";s:4:"歲";s:1:"N";s:4:"殟";s:1:"N";s:4:"殺";s:1:"N";s:4:"殻";s:1:"N";s:4:"𣪍";s:1:"N";s:4:"𡴋";s:1:"N";s:4:"𣫺";s:1:"N";s:4:"汎";s:1:"N";s:4:"𣲼";s:1:"N";s:4:"沿";s:1:"N";s:4:"泍";s:1:"N";s:4:"汧";s:1:"N";s:4:"洖";s:1:"N";s:4:"派";s:1:"N";s:4:"海";s:1:"N";s:4:"流";s:1:"N";s:4:"浩";s:1:"N";s:4:"浸";s:1:"N";s:4:"涅";s:1:"N";s:4:"𣴞";s:1:"N";s:4:"洴";s:1:"N";s:4:"港";s:1:"N";s:4:"湮";s:1:"N";s:4:"㴳";s:1:"N";s:4:"滋";s:1:"N";s:4:"滇";s:1:"N";s:4:"𣻑";s:1:"N";s:4:"淹";s:1:"N";s:4:"潮";s:1:"N";s:4:"𣽞";s:1:"N";s:4:"𣾎";s:1:"N";s:4:"濆";s:1:"N";s:4:"瀹";s:1:"N";s:4:"瀞";s:1:"N";s:4:"瀛";s:1:"N";s:4:"㶖";s:1:"N";s:4:"灊";s:1:"N";s:4:"災";s:1:"N";s:4:"灷";s:1:"N";s:4:"炭";s:1:"N";s:4:"𠔥";s:1:"N";s:4:"煅";s:1:"N";s:4:"𤉣";s:1:"N";s:4:"熜";s:1:"N";s:4:"𤎫";s:1:"N";s:4:"爨";s:1:"N";s:4:"爵";s:1:"N";s:4:"牐";s:1:"N";s:4:"𤘈";s:1:"N";s:4:"犀";s:1:"N";s:4:"犕";s:1:"N";s:4:"𤜵";s:1:"N";s:4:"𤠔";s:1:"N";s:4:"獺";s:1:"N";s:4:"王";s:1:"N";s:4:"㺬";s:1:"N";s:4:"玥";s:1:"N";s:4:"㺸";s:1:"N";s:4:"㺸";s:1:"N";s:4:"瑇";s:1:"N";s:4:"瑜";s:1:"N";s:4:"瑱";s:1:"N";s:4:"璅";s:1:"N";s:4:"瓊";s:1:"N";s:4:"㼛";s:1:"N";s:4:"甤";s:1:"N";s:4:"𤰶";s:1:"N";s:4:"甾";s:1:"N";s:4:"𤲒";s:1:"N";s:4:"異";s:1:"N";s:4:"𢆟";s:1:"N";s:4:"瘐";s:1:"N";s:4:"𤾡";s:1:"N";s:4:"𤾸";s:1:"N";s:4:"𥁄";s:1:"N";s:4:"㿼";s:1:"N";s:4:"䀈";s:1:"N";s:4:"直";s:1:"N";s:4:"𥃳";s:1:"N";s:4:"𥃲";s:1:"N";s:4:"𥄙";s:1:"N";s:4:"𥄳";s:1:"N";s:4:"眞";s:1:"N";s:4:"真";s:1:"N";s:4:"真";s:1:"N";s:4:"睊";s:1:"N";s:4:"䀹";s:1:"N";s:4:"瞋";s:1:"N";s:4:"䁆";s:1:"N";s:4:"䂖";s:1:"N";s:4:"𥐝";s:1:"N";s:4:"硎";s:1:"N";s:4:"碌";s:1:"N";s:4:"磌";s:1:"N";s:4:"䃣";s:1:"N";s:4:"𥘦";s:1:"N";s:4:"祖";s:1:"N";s:4:"𥚚";s:1:"N";s:4:"𥛅";s:1:"N";s:4:"福";s:1:"N";s:4:"秫";s:1:"N";s:4:"䄯";s:1:"N";s:4:"穀";s:1:"N";s:4:"穊";s:1:"N";s:4:"穏";s:1:"N";s:4:"𥥼";s:1:"N";s:4:"𥪧";s:1:"N";s:4:"𥪧";s:1:"N";s:4:"竮";s:1:"N";s:4:"䈂";s:1:"N";s:4:"𥮫";s:1:"N";s:4:"篆";s:1:"N";s:4:"築";s:1:"N";s:4:"䈧";s:1:"N";s:4:"𥲀";s:1:"N";s:4:"糒";s:1:"N";s:4:"䊠";s:1:"N";s:4:"糨";s:1:"N";s:4:"糣";s:1:"N";s:4:"紀";s:1:"N";s:4:"𥾆";s:1:"N";s:4:"絣";s:1:"N";s:4:"䌁";s:1:"N";s:4:"緇";s:1:"N";s:4:"縂";s:1:"N";s:4:"繅";s:1:"N";s:4:"䌴";s:1:"N";s:4:"𦈨";s:1:"N";s:4:"𦉇";s:1:"N";s:4:"䍙";s:1:"N";s:4:"𦋙";s:1:"N";s:4:"罺";s:1:"N";s:4:"𦌾";s:1:"N";s:4:"羕";s:1:"N";s:4:"翺";s:1:"N";s:4:"者";s:1:"N";s:4:"𦓚";s:1:"N";s:4:"𦔣";s:1:"N";s:4:"聠";s:1:"N";s:4:"𦖨";s:1:"N";s:4:"聰";s:1:"N";s:4:"𣍟";s:1:"N";s:4:"䏕";s:1:"N";s:4:"育";s:1:"N";s:4:"脃";s:1:"N";s:4:"䐋";s:1:"N";s:4:"脾";s:1:"N";s:4:"媵";s:1:"N";s:4:"𦞧";s:1:"N";s:4:"𦞵";s:1:"N";s:4:"𣎓";s:1:"N";s:4:"𣎜";s:1:"N";s:4:"舁";s:1:"N";s:4:"舄";s:1:"N";s:4:"辞";s:1:"N";s:4:"䑫";s:1:"N";s:4:"芑";s:1:"N";s:4:"芋";s:1:"N";s:4:"芝";s:1:"N";s:4:"劳";s:1:"N";s:4:"花";s:1:"N";s:4:"芳";s:1:"N";s:4:"芽";s:1:"N";s:4:"苦";s:1:"N";s:4:"𦬼";s:1:"N";s:4:"若";s:1:"N";s:4:"茝";s:1:"N";s:4:"荣";s:1:"N";s:4:"莭";s:1:"N";s:4:"茣";s:1:"N";s:4:"莽";s:1:"N";s:4:"菧";s:1:"N";s:4:"著";s:1:"N";s:4:"荓";s:1:"N";s:4:"菊";s:1:"N";s:4:"菌";s:1:"N";s:4:"菜";s:1:"N";s:4:"𦰶";s:1:"N";s:4:"𦵫";s:1:"N";s:4:"𦳕";s:1:"N";s:4:"䔫";s:1:"N";s:4:"蓱";s:1:"N";s:4:"蓳";s:1:"N";s:4:"蔖";s:1:"N";s:4:"𧏊";s:1:"N";s:4:"蕤";s:1:"N";s:4:"𦼬";s:1:"N";s:4:"䕝";s:1:"N";s:4:"䕡";s:1:"N";s:4:"𦾱";s:1:"N";s:4:"𧃒";s:1:"N";s:4:"䕫";s:1:"N";s:4:"虐";s:1:"N";s:4:"虜";s:1:"N";s:4:"虧";s:1:"N";s:4:"虩";s:1:"N";s:4:"蚩";s:1:"N";s:4:"蚈";s:1:"N";s:4:"蜎";s:1:"N";s:4:"蛢";s:1:"N";s:4:"蝹";s:1:"N";s:4:"蜨";s:1:"N";s:4:"蝫";s:1:"N";s:4:"螆";s:1:"N";s:4:"䗗";s:1:"N";s:4:"蟡";s:1:"N";s:4:"蠁";s:1:"N";s:4:"䗹";s:1:"N";s:4:"衠";s:1:"N";s:4:"衣";s:1:"N";s:4:"𧙧";s:1:"N";s:4:"裗";s:1:"N";s:4:"裞";s:1:"N";s:4:"䘵";s:1:"N";s:4:"裺";s:1:"N";s:4:"㒻";s:1:"N";s:4:"𧢮";s:1:"N";s:4:"𧥦";s:1:"N";s:4:"䚾";s:1:"N";s:4:"䛇";s:1:"N";s:4:"誠";s:1:"N";s:4:"諭";s:1:"N";s:4:"變";s:1:"N";s:4:"豕";s:1:"N";s:4:"𧲨";s:1:"N";s:4:"貫";s:1:"N";s:4:"賁";s:1:"N";s:4:"贛";s:1:"N";s:4:"起";s:1:"N";s:4:"𧼯";s:1:"N";s:4:"𠠄";s:1:"N";s:4:"跋";s:1:"N";s:4:"趼";s:1:"N";s:4:"跰";s:1:"N";s:4:"𠣞";s:1:"N";s:4:"軔";s:1:"N";s:4:"輸";s:1:"N";s:4:"𨗒";s:1:"N";s:4:"𨗭";s:1:"N";s:4:"邔";s:1:"N";s:4:"郱";s:1:"N";s:4:"鄑";s:1:"N";s:4:"𨜮";s:1:"N";s:4:"鄛";s:1:"N";s:4:"鈸";s:1:"N";s:4:"鋗";s:1:"N";s:4:"鋘";s:1:"N";s:4:"鉼";s:1:"N";s:4:"鏹";s:1:"N";s:4:"鐕";s:1:"N";s:4:"𨯺";s:1:"N";s:4:"開";s:1:"N";s:4:"䦕";s:1:"N";s:4:"閷";s:1:"N";s:4:"𨵷";s:1:"N";s:4:"䧦";s:1:"N";s:4:"雃";s:1:"N";s:4:"嶲";s:1:"N";s:4:"霣";s:1:"N";s:4:"𩅅";s:1:"N";s:4:"𩈚";s:1:"N";s:4:"䩮";s:1:"N";s:4:"䩶";s:1:"N";s:4:"韠";s:1:"N";s:4:"𩐊";s:1:"N";s:4:"䪲";s:1:"N";s:4:"𩒖";s:1:"N";s:4:"頋";s:1:"N";s:4:"頋";s:1:"N";s:4:"頩";s:1:"N";s:4:"𩖶";s:1:"N";s:4:"飢";s:1:"N";s:4:"䬳";s:1:"N";s:4:"餩";s:1:"N";s:4:"馧";s:1:"N";s:4:"駂";s:1:"N";s:4:"駾";s:1:"N";s:4:"䯎";s:1:"N";s:4:"𩬰";s:1:"N";s:4:"鬒";s:1:"N";s:4:"鱀";s:1:"N";s:4:"鳽";s:1:"N";s:4:"䳎";s:1:"N";s:4:"䳭";s:1:"N";s:4:"鵧";s:1:"N";s:4:"𪃎";s:1:"N";s:4:"䳸";s:1:"N";s:4:"𪄅";s:1:"N";s:4:"𪈎";s:1:"N";s:4:"𪊑";s:1:"N";s:4:"麻";s:1:"N";s:4:"䵖";s:1:"N";s:4:"黹";s:1:"N";s:4:"黾";s:1:"N";s:4:"鼅";s:1:"N";s:4:"鼏";s:1:"N";s:4:"鼖";s:1:"N";s:4:"鼻";s:1:"N";s:4:"𪘀";s:1:"N";s:2:"̀";s:1:"M";s:2:"́";s:1:"M";s:2:"̂";s:1:"M";s:2:"̃";s:1:"M";s:2:"̄";s:1:"M";s:2:"̆";s:1:"M";s:2:"̇";s:1:"M";s:2:"̈";s:1:"M";s:2:"̉";s:1:"M";s:2:"̊";s:1:"M";s:2:"̋";s:1:"M";s:2:"̌";s:1:"M";s:2:"̏";s:1:"M";s:2:"̑";s:1:"M";s:2:"̓";s:1:"M";s:2:"̔";s:1:"M";s:2:"̛";s:1:"M";s:2:"̣";s:1:"M";s:2:"̤";s:1:"M";s:2:"̥";s:1:"M";s:2:"̦";s:1:"M";s:2:"̧";s:1:"M";s:2:"̨";s:1:"M";s:2:"̭";s:1:"M";s:2:"̮";s:1:"M";s:2:"̰";s:1:"M";s:2:"̱";s:1:"M";s:2:"̸";s:1:"M";s:2:"͂";s:1:"M";s:2:"ͅ";s:1:"M";s:2:"ٓ";s:1:"M";s:2:"ٔ";s:1:"M";s:2:"ٕ";s:1:"M";s:3:"़";s:1:"M";s:3:"া";s:1:"M";s:3:"ৗ";s:1:"M";s:3:"ା";s:1:"M";s:3:"ୖ";s:1:"M";s:3:"ୗ";s:1:"M";s:3:"ா";s:1:"M";s:3:"ௗ";s:1:"M";s:3:"ౖ";s:1:"M";s:3:"ೂ";s:1:"M";s:3:"ೕ";s:1:"M";s:3:"ೖ";s:1:"M";s:3:"ാ";s:1:"M";s:3:"ൗ";s:1:"M";s:3:"්";s:1:"M";s:3:"ා";s:1:"M";s:3:"ෟ";s:1:"M";s:3:"ီ";s:1:"M";s:3:"ᅡ";s:1:"M";s:3:"ᅢ";s:1:"M";s:3:"ᅣ";s:1:"M";s:3:"ᅤ";s:1:"M";s:3:"ᅥ";s:1:"M";s:3:"ᅦ";s:1:"M";s:3:"ᅧ";s:1:"M";s:3:"ᅨ";s:1:"M";s:3:"ᅩ";s:1:"M";s:3:"ᅪ";s:1:"M";s:3:"ᅫ";s:1:"M";s:3:"ᅬ";s:1:"M";s:3:"ᅭ";s:1:"M";s:3:"ᅮ";s:1:"M";s:3:"ᅯ";s:1:"M";s:3:"ᅰ";s:1:"M";s:3:"ᅱ";s:1:"M";s:3:"ᅲ";s:1:"M";s:3:"ᅳ";s:1:"M";s:3:"ᅴ";s:1:"M";s:3:"ᅵ";s:1:"M";s:3:"ᆨ";s:1:"M";s:3:"ᆩ";s:1:"M";s:3:"ᆪ";s:1:"M";s:3:"ᆫ";s:1:"M";s:3:"ᆬ";s:1:"M";s:3:"ᆭ";s:1:"M";s:3:"ᆮ";s:1:"M";s:3:"ᆯ";s:1:"M";s:3:"ᆰ";s:1:"M";s:3:"ᆱ";s:1:"M";s:3:"ᆲ";s:1:"M";s:3:"ᆳ";s:1:"M";s:3:"ᆴ";s:1:"M";s:3:"ᆵ";s:1:"M";s:3:"ᆶ";s:1:"M";s:3:"ᆷ";s:1:"M";s:3:"ᆸ";s:1:"M";s:3:"ᆹ";s:1:"M";s:3:"ᆺ";s:1:"M";s:3:"ᆻ";s:1:"M";s:3:"ᆼ";s:1:"M";s:3:"ᆽ";s:1:"M";s:3:"ᆾ";s:1:"M";s:3:"ᆿ";s:1:"M";s:3:"ᇀ";s:1:"M";s:3:"ᇁ";s:1:"M";s:3:"ᇂ";s:1:"M";s:3:"ᬵ";s:1:"M";s:3:"゙";s:1:"M";s:3:"゚";s:1:"M";}' );
 ?>
diff -NaurB -x .svn includes/normal/UtfNormalDataK.inc /srv/web/fp014/source/includes/normal/UtfNormalDataK.inc
--- includes/normal/UtfNormalDataK.inc	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalDataK.inc	2007-02-01 01:03:18.000000000 +0000
@@ -2,9 +2,8 @@
 /**
  * This file was automatically generated -- do not edit!
  * Run UtfNormalGenerate.php to create this file again (make clean && make)
- * @package MediaWiki
  */
 /** */
 global $utfCompatibilityDecomp;
-$utfCompatibilityDecomp = unserialize( 'a:5389:{s:2:" ";s:1:" ";s:2:"¨";s:3:" ̈";s:2:"ª";s:1:"a";s:2:"¯";s:3:" ̄";s:2:"²";s:1:"2";s:2:"³";s:1:"3";s:2:"´";s:3:" ́";s:2:"µ";s:2:"μ";s:2:"¸";s:3:" ̧";s:2:"¹";s:1:"1";s:2:"º";s:1:"o";s:2:"¼";s:5:"1⁄4";s:2:"½";s:5:"1⁄2";s:2:"¾";s:5:"3⁄4";s:2:"À";s:3:"À";s:2:"Á";s:3:"Á";s:2:"Â";s:3:"Â";s:2:"Ã";s:3:"Ã";s:2:"Ä";s:3:"Ä";s:2:"Å";s:3:"Å";s:2:"Ç";s:3:"Ç";s:2:"È";s:3:"È";s:2:"É";s:3:"É";s:2:"Ê";s:3:"Ê";s:2:"Ë";s:3:"Ë";s:2:"Ì";s:3:"Ì";s:2:"Í";s:3:"Í";s:2:"Î";s:3:"Î";s:2:"Ï";s:3:"Ï";s:2:"Ñ";s:3:"Ñ";s:2:"Ò";s:3:"Ò";s:2:"Ó";s:3:"Ó";s:2:"Ô";s:3:"Ô";s:2:"Õ";s:3:"Õ";s:2:"Ö";s:3:"Ö";s:2:"Ù";s:3:"Ù";s:2:"Ú";s:3:"Ú";s:2:"Û";s:3:"Û";s:2:"Ü";s:3:"Ü";s:2:"Ý";s:3:"Ý";s:2:"à";s:3:"à";s:2:"á";s:3:"á";s:2:"â";s:3:"â";s:2:"ã";s:3:"ã";s:2:"ä";s:3:"ä";s:2:"å";s:3:"å";s:2:"ç";s:3:"ç";s:2:"è";s:3:"è";s:2:"é";s:3:"é";s:2:"ê";s:3:"ê";s:2:"ë";s:3:"ë";s:2:"ì";s:3:"ì";s:2:"í";s:3:"í";s:2:"î";s:3:"î";s:2:"ï";s:3:"ï";s:2:"ñ";s:3:"ñ";s:2:"ò";s:3:"ò";s:2:"ó";s:3:"ó";s:2:"ô";s:3:"ô";s:2:"õ";s:3:"õ";s:2:"ö";s:3:"ö";s:2:"ù";s:3:"ù";s:2:"ú";s:3:"ú";s:2:"û";s:3:"û";s:2:"ü";s:3:"ü";s:2:"ý";s:3:"ý";s:2:"ÿ";s:3:"ÿ";s:2:"Ā";s:3:"Ā";s:2:"ā";s:3:"ā";s:2:"Ă";s:3:"Ă";s:2:"ă";s:3:"ă";s:2:"Ą";s:3:"Ą";s:2:"ą";s:3:"ą";s:2:"Ć";s:3:"Ć";s:2:"ć";s:3:"ć";s:2:"Ĉ";s:3:"Ĉ";s:2:"ĉ";s:3:"ĉ";s:2:"Ċ";s:3:"Ċ";s:2:"ċ";s:3:"ċ";s:2:"Č";s:3:"Č";s:2:"č";s:3:"č";s:2:"Ď";s:3:"Ď";s:2:"ď";s:3:"ď";s:2:"Ē";s:3:"Ē";s:2:"ē";s:3:"ē";s:2:"Ĕ";s:3:"Ĕ";s:2:"ĕ";s:3:"ĕ";s:2:"Ė";s:3:"Ė";s:2:"ė";s:3:"ė";s:2:"Ę";s:3:"Ę";s:2:"ę";s:3:"ę";s:2:"Ě";s:3:"Ě";s:2:"ě";s:3:"ě";s:2:"Ĝ";s:3:"Ĝ";s:2:"ĝ";s:3:"ĝ";s:2:"Ğ";s:3:"Ğ";s:2:"ğ";s:3:"ğ";s:2:"Ġ";s:3:"Ġ";s:2:"ġ";s:3:"ġ";s:2:"Ģ";s:3:"Ģ";s:2:"ģ";s:3:"ģ";s:2:"Ĥ";s:3:"Ĥ";s:2:"ĥ";s:3:"ĥ";s:2:"Ĩ";s:3:"Ĩ";s:2:"ĩ";s:3:"ĩ";s:2:"Ī";s:3:"Ī";s:2:"ī";s:3:"ī";s:2:"Ĭ";s:3:"Ĭ";s:2:"ĭ";s:3:"ĭ";s:2:"Į";s:3:"Į";s:2:"į";s:3:"į";s:2:"İ";s:3:"İ";s:2:"Ĳ";s:2:"IJ";s:2:"ĳ";s:2:"ij";s:2:"Ĵ";s:3:"Ĵ";s:2:"ĵ";s:3:"ĵ";s:2:"Ķ";s:3:"Ķ";s:2:"ķ";s:3:"ķ";s:2:"Ĺ";s:3:"Ĺ";s:2:"ĺ";s:3:"ĺ";s:2:"Ļ";s:3:"Ļ";s:2:"ļ";s:3:"ļ";s:2:"Ľ";s:3:"Ľ";s:2:"ľ";s:3:"ľ";s:2:"Ŀ";s:3:"L·";s:2:"ŀ";s:3:"l·";s:2:"Ń";s:3:"Ń";s:2:"ń";s:3:"ń";s:2:"Ņ";s:3:"Ņ";s:2:"ņ";s:3:"ņ";s:2:"Ň";s:3:"Ň";s:2:"ň";s:3:"ň";s:2:"ŉ";s:3:"ʼn";s:2:"Ō";s:3:"Ō";s:2:"ō";s:3:"ō";s:2:"Ŏ";s:3:"Ŏ";s:2:"ŏ";s:3:"ŏ";s:2:"Ő";s:3:"Ő";s:2:"ő";s:3:"ő";s:2:"Ŕ";s:3:"Ŕ";s:2:"ŕ";s:3:"ŕ";s:2:"Ŗ";s:3:"Ŗ";s:2:"ŗ";s:3:"ŗ";s:2:"Ř";s:3:"Ř";s:2:"ř";s:3:"ř";s:2:"Ś";s:3:"Ś";s:2:"ś";s:3:"ś";s:2:"Ŝ";s:3:"Ŝ";s:2:"ŝ";s:3:"ŝ";s:2:"Ş";s:3:"Ş";s:2:"ş";s:3:"ş";s:2:"Š";s:3:"Š";s:2:"š";s:3:"š";s:2:"Ţ";s:3:"Ţ";s:2:"ţ";s:3:"ţ";s:2:"Ť";s:3:"Ť";s:2:"ť";s:3:"ť";s:2:"Ũ";s:3:"Ũ";s:2:"ũ";s:3:"ũ";s:2:"Ū";s:3:"Ū";s:2:"ū";s:3:"ū";s:2:"Ŭ";s:3:"Ŭ";s:2:"ŭ";s:3:"ŭ";s:2:"Ů";s:3:"Ů";s:2:"ů";s:3:"ů";s:2:"Ű";s:3:"Ű";s:2:"ű";s:3:"ű";s:2:"Ų";s:3:"Ų";s:2:"ų";s:3:"ų";s:2:"Ŵ";s:3:"Ŵ";s:2:"ŵ";s:3:"ŵ";s:2:"Ŷ";s:3:"Ŷ";s:2:"ŷ";s:3:"ŷ";s:2:"Ÿ";s:3:"Ÿ";s:2:"Ź";s:3:"Ź";s:2:"ź";s:3:"ź";s:2:"Ż";s:3:"Ż";s:2:"ż";s:3:"ż";s:2:"Ž";s:3:"Ž";s:2:"ž";s:3:"ž";s:2:"ſ";s:1:"s";s:2:"Ơ";s:3:"Ơ";s:2:"ơ";s:3:"ơ";s:2:"Ư";s:3:"Ư";s:2:"ư";s:3:"ư";s:2:"Ǆ";s:4:"DŽ";s:2:"ǅ";s:4:"Dž";s:2:"ǆ";s:4:"dž";s:2:"Ǉ";s:2:"LJ";s:2:"ǈ";s:2:"Lj";s:2:"ǉ";s:2:"lj";s:2:"Ǌ";s:2:"NJ";s:2:"ǋ";s:2:"Nj";s:2:"ǌ";s:2:"nj";s:2:"Ǎ";s:3:"Ǎ";s:2:"ǎ";s:3:"ǎ";s:2:"Ǐ";s:3:"Ǐ";s:2:"ǐ";s:3:"ǐ";s:2:"Ǒ";s:3:"Ǒ";s:2:"ǒ";s:3:"ǒ";s:2:"Ǔ";s:3:"Ǔ";s:2:"ǔ";s:3:"ǔ";s:2:"Ǖ";s:5:"Ǖ";s:2:"ǖ";s:5:"ǖ";s:2:"Ǘ";s:5:"Ǘ";s:2:"ǘ";s:5:"ǘ";s:2:"Ǚ";s:5:"Ǚ";s:2:"ǚ";s:5:"ǚ";s:2:"Ǜ";s:5:"Ǜ";s:2:"ǜ";s:5:"ǜ";s:2:"Ǟ";s:5:"Ǟ";s:2:"ǟ";s:5:"ǟ";s:2:"Ǡ";s:5:"Ǡ";s:2:"ǡ";s:5:"ǡ";s:2:"Ǣ";s:4:"Ǣ";s:2:"ǣ";s:4:"ǣ";s:2:"Ǧ";s:3:"Ǧ";s:2:"ǧ";s:3:"ǧ";s:2:"Ǩ";s:3:"Ǩ";s:2:"ǩ";s:3:"ǩ";s:2:"Ǫ";s:3:"Ǫ";s:2:"ǫ";s:3:"ǫ";s:2:"Ǭ";s:5:"Ǭ";s:2:"ǭ";s:5:"ǭ";s:2:"Ǯ";s:4:"Ǯ";s:2:"ǯ";s:4:"ǯ";s:2:"ǰ";s:3:"ǰ";s:2:"Ǳ";s:2:"DZ";s:2:"ǲ";s:2:"Dz";s:2:"ǳ";s:2:"dz";s:2:"Ǵ";s:3:"Ǵ";s:2:"ǵ";s:3:"ǵ";s:2:"Ǹ";s:3:"Ǹ";s:2:"ǹ";s:3:"ǹ";s:2:"Ǻ";s:5:"Ǻ";s:2:"ǻ";s:5:"ǻ";s:2:"Ǽ";s:4:"Ǽ";s:2:"ǽ";s:4:"ǽ";s:2:"Ǿ";s:4:"Ǿ";s:2:"ǿ";s:4:"ǿ";s:2:"Ȁ";s:3:"Ȁ";s:2:"ȁ";s:3:"ȁ";s:2:"Ȃ";s:3:"Ȃ";s:2:"ȃ";s:3:"ȃ";s:2:"Ȅ";s:3:"Ȅ";s:2:"ȅ";s:3:"ȅ";s:2:"Ȇ";s:3:"Ȇ";s:2:"ȇ";s:3:"ȇ";s:2:"Ȉ";s:3:"Ȉ";s:2:"ȉ";s:3:"ȉ";s:2:"Ȋ";s:3:"Ȋ";s:2:"ȋ";s:3:"ȋ";s:2:"Ȍ";s:3:"Ȍ";s:2:"ȍ";s:3:"ȍ";s:2:"Ȏ";s:3:"Ȏ";s:2:"ȏ";s:3:"ȏ";s:2:"Ȑ";s:3:"Ȑ";s:2:"ȑ";s:3:"ȑ";s:2:"Ȓ";s:3:"Ȓ";s:2:"ȓ";s:3:"ȓ";s:2:"Ȕ";s:3:"Ȕ";s:2:"ȕ";s:3:"ȕ";s:2:"Ȗ";s:3:"Ȗ";s:2:"ȗ";s:3:"ȗ";s:2:"Ș";s:3:"Ș";s:2:"ș";s:3:"ș";s:2:"Ț";s:3:"Ț";s:2:"ț";s:3:"ț";s:2:"Ȟ";s:3:"Ȟ";s:2:"ȟ";s:3:"ȟ";s:2:"Ȧ";s:3:"Ȧ";s:2:"ȧ";s:3:"ȧ";s:2:"Ȩ";s:3:"Ȩ";s:2:"ȩ";s:3:"ȩ";s:2:"Ȫ";s:5:"Ȫ";s:2:"ȫ";s:5:"ȫ";s:2:"Ȭ";s:5:"Ȭ";s:2:"ȭ";s:5:"ȭ";s:2:"Ȯ";s:3:"Ȯ";s:2:"ȯ";s:3:"ȯ";s:2:"Ȱ";s:5:"Ȱ";s:2:"ȱ";s:5:"ȱ";s:2:"Ȳ";s:3:"Ȳ";s:2:"ȳ";s:3:"ȳ";s:2:"ʰ";s:1:"h";s:2:"ʱ";s:2:"ɦ";s:2:"ʲ";s:1:"j";s:2:"ʳ";s:1:"r";s:2:"ʴ";s:2:"ɹ";s:2:"ʵ";s:2:"ɻ";s:2:"ʶ";s:2:"ʁ";s:2:"ʷ";s:1:"w";s:2:"ʸ";s:1:"y";s:2:"˘";s:3:" ̆";s:2:"˙";s:3:" ̇";s:2:"˚";s:3:" ̊";s:2:"˛";s:3:" ̨";s:2:"˜";s:3:" ̃";s:2:"˝";s:3:" ̋";s:2:"ˠ";s:2:"ɣ";s:2:"ˡ";s:1:"l";s:2:"ˢ";s:1:"s";s:2:"ˣ";s:1:"x";s:2:"ˤ";s:2:"ʕ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:2:"̈́";s:4:"̈́";s:2:"ʹ";s:2:"ʹ";s:2:"ͺ";s:3:" ͅ";s:2:";";s:1:";";s:2:"΄";s:3:" ́";s:2:"΅";s:5:" ̈́";s:2:"Ά";s:4:"Ά";s:2:"·";s:2:"·";s:2:"Έ";s:4:"Έ";s:2:"Ή";s:4:"Ή";s:2:"Ί";s:4:"Ί";s:2:"Ό";s:4:"Ό";s:2:"Ύ";s:4:"Ύ";s:2:"Ώ";s:4:"Ώ";s:2:"ΐ";s:6:"ΐ";s:2:"Ϊ";s:4:"Ϊ";s:2:"Ϋ";s:4:"Ϋ";s:2:"ά";s:4:"ά";s:2:"έ";s:4:"έ";s:2:"ή";s:4:"ή";s:2:"ί";s:4:"ί";s:2:"ΰ";s:6:"ΰ";s:2:"ϊ";s:4:"ϊ";s:2:"ϋ";s:4:"ϋ";s:2:"ό";s:4:"ό";s:2:"ύ";s:4:"ύ";s:2:"ώ";s:4:"ώ";s:2:"ϐ";s:2:"β";s:2:"ϑ";s:2:"θ";s:2:"ϒ";s:2:"Υ";s:2:"ϓ";s:4:"Ύ";s:2:"ϔ";s:4:"Ϋ";s:2:"ϕ";s:2:"φ";s:2:"ϖ";s:2:"π";s:2:"ϰ";s:2:"κ";s:2:"ϱ";s:2:"ρ";s:2:"ϲ";s:2:"ς";s:2:"ϴ";s:2:"Θ";s:2:"ϵ";s:2:"ε";s:2:"Ϲ";s:2:"Σ";s:2:"Ѐ";s:4:"Ѐ";s:2:"Ё";s:4:"Ё";s:2:"Ѓ";s:4:"Ѓ";s:2:"Ї";s:4:"Ї";s:2:"Ќ";s:4:"Ќ";s:2:"Ѝ";s:4:"Ѝ";s:2:"Ў";s:4:"Ў";s:2:"Й";s:4:"Й";s:2:"й";s:4:"й";s:2:"ѐ";s:4:"ѐ";s:2:"ё";s:4:"ё";s:2:"ѓ";s:4:"ѓ";s:2:"ї";s:4:"ї";s:2:"ќ";s:4:"ќ";s:2:"ѝ";s:4:"ѝ";s:2:"ў";s:4:"ў";s:2:"Ѷ";s:4:"Ѷ";s:2:"ѷ";s:4:"ѷ";s:2:"Ӂ";s:4:"Ӂ";s:2:"ӂ";s:4:"ӂ";s:2:"Ӑ";s:4:"Ӑ";s:2:"ӑ";s:4:"ӑ";s:2:"Ӓ";s:4:"Ӓ";s:2:"ӓ";s:4:"ӓ";s:2:"Ӗ";s:4:"Ӗ";s:2:"ӗ";s:4:"ӗ";s:2:"Ӛ";s:4:"Ӛ";s:2:"ӛ";s:4:"ӛ";s:2:"Ӝ";s:4:"Ӝ";s:2:"ӝ";s:4:"ӝ";s:2:"Ӟ";s:4:"Ӟ";s:2:"ӟ";s:4:"ӟ";s:2:"Ӣ";s:4:"Ӣ";s:2:"ӣ";s:4:"ӣ";s:2:"Ӥ";s:4:"Ӥ";s:2:"ӥ";s:4:"ӥ";s:2:"Ӧ";s:4:"Ӧ";s:2:"ӧ";s:4:"ӧ";s:2:"Ӫ";s:4:"Ӫ";s:2:"ӫ";s:4:"ӫ";s:2:"Ӭ";s:4:"Ӭ";s:2:"ӭ";s:4:"ӭ";s:2:"Ӯ";s:4:"Ӯ";s:2:"ӯ";s:4:"ӯ";s:2:"Ӱ";s:4:"Ӱ";s:2:"ӱ";s:4:"ӱ";s:2:"Ӳ";s:4:"Ӳ";s:2:"ӳ";s:4:"ӳ";s:2:"Ӵ";s:4:"Ӵ";s:2:"ӵ";s:4:"ӵ";s:2:"Ӹ";s:4:"Ӹ";s:2:"ӹ";s:4:"ӹ";s:2:"և";s:4:"եւ";s:2:"آ";s:4:"آ";s:2:"أ";s:4:"أ";s:2:"ؤ";s:4:"ؤ";s:2:"إ";s:4:"إ";s:2:"ئ";s:4:"ئ";s:2:"ٵ";s:4:"اٴ";s:2:"ٶ";s:4:"وٴ";s:2:"ٷ";s:4:"ۇٴ";s:2:"ٸ";s:4:"يٴ";s:2:"ۀ";s:4:"ۀ";s:2:"ۂ";s:4:"ۂ";s:2:"ۓ";s:4:"ۓ";s:3:"ऩ";s:6:"ऩ";s:3:"ऱ";s:6:"ऱ";s:3:"ऴ";s:6:"ऴ";s:3:"क़";s:6:"क़";s:3:"ख़";s:6:"ख़";s:3:"ग़";s:6:"ग़";s:3:"ज़";s:6:"ज़";s:3:"ड़";s:6:"ड़";s:3:"ढ़";s:6:"ढ़";s:3:"फ़";s:6:"फ़";s:3:"य़";s:6:"य़";s:3:"ো";s:6:"ো";s:3:"ৌ";s:6:"ৌ";s:3:"ড়";s:6:"ড়";s:3:"ঢ়";s:6:"ঢ়";s:3:"য়";s:6:"য়";s:3:"ਲ਼";s:6:"ਲ਼";s:3:"ਸ਼";s:6:"ਸ਼";s:3:"ਖ਼";s:6:"ਖ਼";s:3:"ਗ਼";s:6:"ਗ਼";s:3:"ਜ਼";s:6:"ਜ਼";s:3:"ਫ਼";s:6:"ਫ਼";s:3:"ୈ";s:6:"ୈ";s:3:"ୋ";s:6:"ୋ";s:3:"ୌ";s:6:"ୌ";s:3:"ଡ଼";s:6:"ଡ଼";s:3:"ଢ଼";s:6:"ଢ଼";s:3:"ஔ";s:6:"ஔ";s:3:"ொ";s:6:"ொ";s:3:"ோ";s:6:"ோ";s:3:"ௌ";s:6:"ௌ";s:3:"ై";s:6:"ై";s:3:"ೀ";s:6:"ೀ";s:3:"ೇ";s:6:"ೇ";s:3:"ೈ";s:6:"ೈ";s:3:"ೊ";s:6:"ೊ";s:3:"ೋ";s:9:"ೋ";s:3:"ൊ";s:6:"ൊ";s:3:"ോ";s:6:"ോ";s:3:"ൌ";s:6:"ൌ";s:3:"ේ";s:6:"ේ";s:3:"ො";s:6:"ො";s:3:"ෝ";s:9:"ෝ";s:3:"ෞ";s:6:"ෞ";s:3:"ำ";s:6:"ํา";s:3:"ຳ";s:6:"ໍາ";s:3:"ໜ";s:6:"ຫນ";s:3:"ໝ";s:6:"ຫມ";s:3:"༌";s:3:"་";s:3:"གྷ";s:6:"གྷ";s:3:"ཌྷ";s:6:"ཌྷ";s:3:"དྷ";s:6:"དྷ";s:3:"བྷ";s:6:"བྷ";s:3:"ཛྷ";s:6:"ཛྷ";s:3:"ཀྵ";s:6:"ཀྵ";s:3:"ཱི";s:6:"ཱི";s:3:"ཱུ";s:6:"ཱུ";s:3:"ྲྀ";s:6:"ྲྀ";s:3:"ཷ";s:9:"ྲཱྀ";s:3:"ླྀ";s:6:"ླྀ";s:3:"ཹ";s:9:"ླཱྀ";s:3:"ཱྀ";s:6:"ཱྀ";s:3:"ྒྷ";s:6:"ྒྷ";s:3:"ྜྷ";s:6:"ྜྷ";s:3:"ྡྷ";s:6:"ྡྷ";s:3:"ྦྷ";s:6:"ྦྷ";s:3:"ྫྷ";s:6:"ྫྷ";s:3:"ྐྵ";s:6:"ྐྵ";s:3:"ဦ";s:6:"ဦ";s:3:"ჼ";s:3:"ნ";s:3:"ᴬ";s:1:"A";s:3:"ᴭ";s:2:"Æ";s:3:"ᴮ";s:1:"B";s:3:"ᴰ";s:1:"D";s:3:"ᴱ";s:1:"E";s:3:"ᴲ";s:2:"Ǝ";s:3:"ᴳ";s:1:"G";s:3:"ᴴ";s:1:"H";s:3:"ᴵ";s:1:"I";s:3:"ᴶ";s:1:"J";s:3:"ᴷ";s:1:"K";s:3:"ᴸ";s:1:"L";s:3:"ᴹ";s:1:"M";s:3:"ᴺ";s:1:"N";s:3:"ᴼ";s:1:"O";s:3:"ᴽ";s:2:"Ȣ";s:3:"ᴾ";s:1:"P";s:3:"ᴿ";s:1:"R";s:3:"ᵀ";s:1:"T";s:3:"ᵁ";s:1:"U";s:3:"ᵂ";s:1:"W";s:3:"ᵃ";s:1:"a";s:3:"ᵄ";s:2:"ɐ";s:3:"ᵅ";s:2:"ɑ";s:3:"ᵆ";s:3:"ᴂ";s:3:"ᵇ";s:1:"b";s:3:"ᵈ";s:1:"d";s:3:"ᵉ";s:1:"e";s:3:"ᵊ";s:2:"ə";s:3:"ᵋ";s:2:"ɛ";s:3:"ᵌ";s:2:"ɜ";s:3:"ᵍ";s:1:"g";s:3:"ᵏ";s:1:"k";s:3:"ᵐ";s:1:"m";s:3:"ᵑ";s:2:"ŋ";s:3:"ᵒ";s:1:"o";s:3:"ᵓ";s:2:"ɔ";s:3:"ᵔ";s:3:"ᴖ";s:3:"ᵕ";s:3:"ᴗ";s:3:"ᵖ";s:1:"p";s:3:"ᵗ";s:1:"t";s:3:"ᵘ";s:1:"u";s:3:"ᵙ";s:3:"ᴝ";s:3:"ᵚ";s:2:"ɯ";s:3:"ᵛ";s:1:"v";s:3:"ᵜ";s:3:"ᴥ";s:3:"ᵝ";s:2:"β";s:3:"ᵞ";s:2:"γ";s:3:"ᵟ";s:2:"δ";s:3:"ᵠ";s:2:"φ";s:3:"ᵡ";s:2:"χ";s:3:"ᵢ";s:1:"i";s:3:"ᵣ";s:1:"r";s:3:"ᵤ";s:1:"u";s:3:"ᵥ";s:1:"v";s:3:"ᵦ";s:2:"β";s:3:"ᵧ";s:2:"γ";s:3:"ᵨ";s:2:"ρ";s:3:"ᵩ";s:2:"φ";s:3:"ᵪ";s:2:"χ";s:3:"ᵸ";s:2:"н";s:3:"ᶛ";s:2:"ɒ";s:3:"ᶜ";s:1:"c";s:3:"ᶝ";s:2:"ɕ";s:3:"ᶞ";s:2:"ð";s:3:"ᶟ";s:2:"ɜ";s:3:"ᶠ";s:1:"f";s:3:"ᶡ";s:2:"ɟ";s:3:"ᶢ";s:2:"ɡ";s:3:"ᶣ";s:2:"ɥ";s:3:"ᶤ";s:2:"ɨ";s:3:"ᶥ";s:2:"ɩ";s:3:"ᶦ";s:2:"ɪ";s:3:"ᶧ";s:3:"ᵻ";s:3:"ᶨ";s:2:"ʝ";s:3:"ᶩ";s:2:"ɭ";s:3:"ᶪ";s:3:"ᶅ";s:3:"ᶫ";s:2:"ʟ";s:3:"ᶬ";s:2:"ɱ";s:3:"ᶭ";s:2:"ɰ";s:3:"ᶮ";s:2:"ɲ";s:3:"ᶯ";s:2:"ɳ";s:3:"ᶰ";s:2:"ɴ";s:3:"ᶱ";s:2:"ɵ";s:3:"ᶲ";s:2:"ɸ";s:3:"ᶳ";s:2:"ʂ";s:3:"ᶴ";s:2:"ʃ";s:3:"ᶵ";s:2:"ƫ";s:3:"ᶶ";s:2:"ʉ";s:3:"ᶷ";s:2:"ʊ";s:3:"ᶸ";s:3:"ᴜ";s:3:"ᶹ";s:2:"ʋ";s:3:"ᶺ";s:2:"ʌ";s:3:"ᶻ";s:1:"z";s:3:"ᶼ";s:2:"ʐ";s:3:"ᶽ";s:2:"ʑ";s:3:"ᶾ";s:2:"ʒ";s:3:"ᶿ";s:2:"θ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:3:"Ḉ";s:5:"Ḉ";s:3:"ḉ";s:5:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:3:"Ḕ";s:5:"Ḕ";s:3:"ḕ";s:5:"ḕ";s:3:"Ḗ";s:5:"Ḗ";s:3:"ḗ";s:5:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:3:"Ḝ";s:5:"Ḝ";s:3:"ḝ";s:5:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:3:"Ḯ";s:5:"Ḯ";s:3:"ḯ";s:5:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:3:"Ḹ";s:5:"Ḹ";s:3:"ḹ";s:5:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:3:"Ṍ";s:5:"Ṍ";s:3:"ṍ";s:5:"ṍ";s:3:"Ṏ";s:5:"Ṏ";s:3:"ṏ";s:5:"ṏ";s:3:"Ṑ";s:5:"Ṑ";s:3:"ṑ";s:5:"ṑ";s:3:"Ṓ";s:5:"Ṓ";s:3:"ṓ";s:5:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:3:"Ṝ";s:5:"Ṝ";s:3:"ṝ";s:5:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:3:"Ṥ";s:5:"Ṥ";s:3:"ṥ";s:5:"ṥ";s:3:"Ṧ";s:5:"Ṧ";s:3:"ṧ";s:5:"ṧ";s:3:"Ṩ";s:5:"Ṩ";s:3:"ṩ";s:5:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:3:"Ṹ";s:5:"Ṹ";s:3:"ṹ";s:5:"ṹ";s:3:"Ṻ";s:5:"Ṻ";s:3:"ṻ";s:5:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:3:"ẚ";s:3:"aʾ";s:3:"ẛ";s:3:"ṡ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:3:"Ấ";s:5:"Ấ";s:3:"ấ";s:5:"ấ";s:3:"Ầ";s:5:"Ầ";s:3:"ầ";s:5:"ầ";s:3:"Ẩ";s:5:"Ẩ";s:3:"ẩ";s:5:"ẩ";s:3:"Ẫ";s:5:"Ẫ";s:3:"ẫ";s:5:"ẫ";s:3:"Ậ";s:5:"Ậ";s:3:"ậ";s:5:"ậ";s:3:"Ắ";s:5:"Ắ";s:3:"ắ";s:5:"ắ";s:3:"Ằ";s:5:"Ằ";s:3:"ằ";s:5:"ằ";s:3:"Ẳ";s:5:"Ẳ";s:3:"ẳ";s:5:"ẳ";s:3:"Ẵ";s:5:"Ẵ";s:3:"ẵ";s:5:"ẵ";s:3:"Ặ";s:5:"Ặ";s:3:"ặ";s:5:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:3:"Ế";s:5:"Ế";s:3:"ế";s:5:"ế";s:3:"Ề";s:5:"Ề";s:3:"ề";s:5:"ề";s:3:"Ể";s:5:"Ể";s:3:"ể";s:5:"ể";s:3:"Ễ";s:5:"Ễ";s:3:"ễ";s:5:"ễ";s:3:"Ệ";s:5:"Ệ";s:3:"ệ";s:5:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:3:"Ố";s:5:"Ố";s:3:"ố";s:5:"ố";s:3:"Ồ";s:5:"Ồ";s:3:"ồ";s:5:"ồ";s:3:"Ổ";s:5:"Ổ";s:3:"ổ";s:5:"ổ";s:3:"Ỗ";s:5:"Ỗ";s:3:"ỗ";s:5:"ỗ";s:3:"Ộ";s:5:"Ộ";s:3:"ộ";s:5:"ộ";s:3:"Ớ";s:5:"Ớ";s:3:"ớ";s:5:"ớ";s:3:"Ờ";s:5:"Ờ";s:3:"ờ";s:5:"ờ";s:3:"Ở";s:5:"Ở";s:3:"ở";s:5:"ở";s:3:"Ỡ";s:5:"Ỡ";s:3:"ỡ";s:5:"ỡ";s:3:"Ợ";s:5:"Ợ";s:3:"ợ";s:5:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:3:"Ứ";s:5:"Ứ";s:3:"ứ";s:5:"ứ";s:3:"Ừ";s:5:"Ừ";s:3:"ừ";s:5:"ừ";s:3:"Ử";s:5:"Ử";s:3:"ử";s:5:"ử";s:3:"Ữ";s:5:"Ữ";s:3:"ữ";s:5:"ữ";s:3:"Ự";s:5:"Ự";s:3:"ự";s:5:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:3:"ἀ";s:4:"ἀ";s:3:"ἁ";s:4:"ἁ";s:3:"ἂ";s:6:"ἂ";s:3:"ἃ";s:6:"ἃ";s:3:"ἄ";s:6:"ἄ";s:3:"ἅ";s:6:"ἅ";s:3:"ἆ";s:6:"ἆ";s:3:"ἇ";s:6:"ἇ";s:3:"Ἀ";s:4:"Ἀ";s:3:"Ἁ";s:4:"Ἁ";s:3:"Ἂ";s:6:"Ἂ";s:3:"Ἃ";s:6:"Ἃ";s:3:"Ἄ";s:6:"Ἄ";s:3:"Ἅ";s:6:"Ἅ";s:3:"Ἆ";s:6:"Ἆ";s:3:"Ἇ";s:6:"Ἇ";s:3:"ἐ";s:4:"ἐ";s:3:"ἑ";s:4:"ἑ";s:3:"ἒ";s:6:"ἒ";s:3:"ἓ";s:6:"ἓ";s:3:"ἔ";s:6:"ἔ";s:3:"ἕ";s:6:"ἕ";s:3:"Ἐ";s:4:"Ἐ";s:3:"Ἑ";s:4:"Ἑ";s:3:"Ἒ";s:6:"Ἒ";s:3:"Ἓ";s:6:"Ἓ";s:3:"Ἔ";s:6:"Ἔ";s:3:"Ἕ";s:6:"Ἕ";s:3:"ἠ";s:4:"ἠ";s:3:"ἡ";s:4:"ἡ";s:3:"ἢ";s:6:"ἢ";s:3:"ἣ";s:6:"ἣ";s:3:"ἤ";s:6:"ἤ";s:3:"ἥ";s:6:"ἥ";s:3:"ἦ";s:6:"ἦ";s:3:"ἧ";s:6:"ἧ";s:3:"Ἠ";s:4:"Ἠ";s:3:"Ἡ";s:4:"Ἡ";s:3:"Ἢ";s:6:"Ἢ";s:3:"Ἣ";s:6:"Ἣ";s:3:"Ἤ";s:6:"Ἤ";s:3:"Ἥ";s:6:"Ἥ";s:3:"Ἦ";s:6:"Ἦ";s:3:"Ἧ";s:6:"Ἧ";s:3:"ἰ";s:4:"ἰ";s:3:"ἱ";s:4:"ἱ";s:3:"ἲ";s:6:"ἲ";s:3:"ἳ";s:6:"ἳ";s:3:"ἴ";s:6:"ἴ";s:3:"ἵ";s:6:"ἵ";s:3:"ἶ";s:6:"ἶ";s:3:"ἷ";s:6:"ἷ";s:3:"Ἰ";s:4:"Ἰ";s:3:"Ἱ";s:4:"Ἱ";s:3:"Ἲ";s:6:"Ἲ";s:3:"Ἳ";s:6:"Ἳ";s:3:"Ἴ";s:6:"Ἴ";s:3:"Ἵ";s:6:"Ἵ";s:3:"Ἶ";s:6:"Ἶ";s:3:"Ἷ";s:6:"Ἷ";s:3:"ὀ";s:4:"ὀ";s:3:"ὁ";s:4:"ὁ";s:3:"ὂ";s:6:"ὂ";s:3:"ὃ";s:6:"ὃ";s:3:"ὄ";s:6:"ὄ";s:3:"ὅ";s:6:"ὅ";s:3:"Ὀ";s:4:"Ὀ";s:3:"Ὁ";s:4:"Ὁ";s:3:"Ὂ";s:6:"Ὂ";s:3:"Ὃ";s:6:"Ὃ";s:3:"Ὄ";s:6:"Ὄ";s:3:"Ὅ";s:6:"Ὅ";s:3:"ὐ";s:4:"ὐ";s:3:"ὑ";s:4:"ὑ";s:3:"ὒ";s:6:"ὒ";s:3:"ὓ";s:6:"ὓ";s:3:"ὔ";s:6:"ὔ";s:3:"ὕ";s:6:"ὕ";s:3:"ὖ";s:6:"ὖ";s:3:"ὗ";s:6:"ὗ";s:3:"Ὑ";s:4:"Ὑ";s:3:"Ὓ";s:6:"Ὓ";s:3:"Ὕ";s:6:"Ὕ";s:3:"Ὗ";s:6:"Ὗ";s:3:"ὠ";s:4:"ὠ";s:3:"ὡ";s:4:"ὡ";s:3:"ὢ";s:6:"ὢ";s:3:"ὣ";s:6:"ὣ";s:3:"ὤ";s:6:"ὤ";s:3:"ὥ";s:6:"ὥ";s:3:"ὦ";s:6:"ὦ";s:3:"ὧ";s:6:"ὧ";s:3:"Ὠ";s:4:"Ὠ";s:3:"Ὡ";s:4:"Ὡ";s:3:"Ὢ";s:6:"Ὢ";s:3:"Ὣ";s:6:"Ὣ";s:3:"Ὤ";s:6:"Ὤ";s:3:"Ὥ";s:6:"Ὥ";s:3:"Ὦ";s:6:"Ὦ";s:3:"Ὧ";s:6:"Ὧ";s:3:"ὰ";s:4:"ὰ";s:3:"ά";s:4:"ά";s:3:"ὲ";s:4:"ὲ";s:3:"έ";s:4:"έ";s:3:"ὴ";s:4:"ὴ";s:3:"ή";s:4:"ή";s:3:"ὶ";s:4:"ὶ";s:3:"ί";s:4:"ί";s:3:"ὸ";s:4:"ὸ";s:3:"ό";s:4:"ό";s:3:"ὺ";s:4:"ὺ";s:3:"ύ";s:4:"ύ";s:3:"ὼ";s:4:"ὼ";s:3:"ώ";s:4:"ώ";s:3:"ᾀ";s:6:"ᾀ";s:3:"ᾁ";s:6:"ᾁ";s:3:"ᾂ";s:8:"ᾂ";s:3:"ᾃ";s:8:"ᾃ";s:3:"ᾄ";s:8:"ᾄ";s:3:"ᾅ";s:8:"ᾅ";s:3:"ᾆ";s:8:"ᾆ";s:3:"ᾇ";s:8:"ᾇ";s:3:"ᾈ";s:6:"ᾈ";s:3:"ᾉ";s:6:"ᾉ";s:3:"ᾊ";s:8:"ᾊ";s:3:"ᾋ";s:8:"ᾋ";s:3:"ᾌ";s:8:"ᾌ";s:3:"ᾍ";s:8:"ᾍ";s:3:"ᾎ";s:8:"ᾎ";s:3:"ᾏ";s:8:"ᾏ";s:3:"ᾐ";s:6:"ᾐ";s:3:"ᾑ";s:6:"ᾑ";s:3:"ᾒ";s:8:"ᾒ";s:3:"ᾓ";s:8:"ᾓ";s:3:"ᾔ";s:8:"ᾔ";s:3:"ᾕ";s:8:"ᾕ";s:3:"ᾖ";s:8:"ᾖ";s:3:"ᾗ";s:8:"ᾗ";s:3:"ᾘ";s:6:"ᾘ";s:3:"ᾙ";s:6:"ᾙ";s:3:"ᾚ";s:8:"ᾚ";s:3:"ᾛ";s:8:"ᾛ";s:3:"ᾜ";s:8:"ᾜ";s:3:"ᾝ";s:8:"ᾝ";s:3:"ᾞ";s:8:"ᾞ";s:3:"ᾟ";s:8:"ᾟ";s:3:"ᾠ";s:6:"ᾠ";s:3:"ᾡ";s:6:"ᾡ";s:3:"ᾢ";s:8:"ᾢ";s:3:"ᾣ";s:8:"ᾣ";s:3:"ᾤ";s:8:"ᾤ";s:3:"ᾥ";s:8:"ᾥ";s:3:"ᾦ";s:8:"ᾦ";s:3:"ᾧ";s:8:"ᾧ";s:3:"ᾨ";s:6:"ᾨ";s:3:"ᾩ";s:6:"ᾩ";s:3:"ᾪ";s:8:"ᾪ";s:3:"ᾫ";s:8:"ᾫ";s:3:"ᾬ";s:8:"ᾬ";s:3:"ᾭ";s:8:"ᾭ";s:3:"ᾮ";s:8:"ᾮ";s:3:"ᾯ";s:8:"ᾯ";s:3:"ᾰ";s:4:"ᾰ";s:3:"ᾱ";s:4:"ᾱ";s:3:"ᾲ";s:6:"ᾲ";s:3:"ᾳ";s:4:"ᾳ";s:3:"ᾴ";s:6:"ᾴ";s:3:"ᾶ";s:4:"ᾶ";s:3:"ᾷ";s:6:"ᾷ";s:3:"Ᾰ";s:4:"Ᾰ";s:3:"Ᾱ";s:4:"Ᾱ";s:3:"Ὰ";s:4:"Ὰ";s:3:"Ά";s:4:"Ά";s:3:"ᾼ";s:4:"ᾼ";s:3:"᾽";s:3:" ̓";s:3:"ι";s:2:"ι";s:3:"᾿";s:3:" ̓";s:3:"῀";s:3:" ͂";s:3:"῁";s:5:" ̈͂";s:3:"ῂ";s:6:"ῂ";s:3:"ῃ";s:4:"ῃ";s:3:"ῄ";s:6:"ῄ";s:3:"ῆ";s:4:"ῆ";s:3:"ῇ";s:6:"ῇ";s:3:"Ὲ";s:4:"Ὲ";s:3:"Έ";s:4:"Έ";s:3:"Ὴ";s:4:"Ὴ";s:3:"Ή";s:4:"Ή";s:3:"ῌ";s:4:"ῌ";s:3:"῍";s:5:" ̓̀";s:3:"῎";s:5:" ̓́";s:3:"῏";s:5:" ̓͂";s:3:"ῐ";s:4:"ῐ";s:3:"ῑ";s:4:"ῑ";s:3:"ῒ";s:6:"ῒ";s:3:"ΐ";s:6:"ΐ";s:3:"ῖ";s:4:"ῖ";s:3:"ῗ";s:6:"ῗ";s:3:"Ῐ";s:4:"Ῐ";s:3:"Ῑ";s:4:"Ῑ";s:3:"Ὶ";s:4:"Ὶ";s:3:"Ί";s:4:"Ί";s:3:"῝";s:5:" ̔̀";s:3:"῞";s:5:" ̔́";s:3:"῟";s:5:" ̔͂";s:3:"ῠ";s:4:"ῠ";s:3:"ῡ";s:4:"ῡ";s:3:"ῢ";s:6:"ῢ";s:3:"ΰ";s:6:"ΰ";s:3:"ῤ";s:4:"ῤ";s:3:"ῥ";s:4:"ῥ";s:3:"ῦ";s:4:"ῦ";s:3:"ῧ";s:6:"ῧ";s:3:"Ῠ";s:4:"Ῠ";s:3:"Ῡ";s:4:"Ῡ";s:3:"Ὺ";s:4:"Ὺ";s:3:"Ύ";s:4:"Ύ";s:3:"Ῥ";s:4:"Ῥ";s:3:"῭";s:5:" ̈̀";s:3:"΅";s:5:" ̈́";s:3:"`";s:1:"`";s:3:"ῲ";s:6:"ῲ";s:3:"ῳ";s:4:"ῳ";s:3:"ῴ";s:6:"ῴ";s:3:"ῶ";s:4:"ῶ";s:3:"ῷ";s:6:"ῷ";s:3:"Ὸ";s:4:"Ὸ";s:3:"Ό";s:4:"Ό";s:3:"Ὼ";s:4:"Ὼ";s:3:"Ώ";s:4:"Ώ";s:3:"ῼ";s:4:"ῼ";s:3:"´";s:3:" ́";s:3:"῾";s:3:" ̔";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:"‑";s:3:"‐";s:3:"‗";s:3:" ̳";s:3:"․";s:1:".";s:3:"‥";s:2:"..";s:3:"…";s:3:"...";s:3:" ";s:1:" ";s:3:"″";s:6:"′′";s:3:"‴";s:9:"′′′";s:3:"‶";s:6:"‵‵";s:3:"‷";s:9:"‵‵‵";s:3:"‼";s:2:"!!";s:3:"‾";s:3:" ̅";s:3:"⁇";s:2:"??";s:3:"⁈";s:2:"?!";s:3:"⁉";s:2:"!?";s:3:"⁗";s:12:"′′′′";s:3:" ";s:1:" ";s:3:"⁰";s:1:"0";s:3:"ⁱ";s:1:"i";s:3:"⁴";s:1:"4";s:3:"⁵";s:1:"5";s:3:"⁶";s:1:"6";s:3:"⁷";s:1:"7";s:3:"⁸";s:1:"8";s:3:"⁹";s:1:"9";s:3:"⁺";s:1:"+";s:3:"⁻";s:3:"−";s:3:"⁼";s:1:"=";s:3:"⁽";s:1:"(";s:3:"⁾";s:1:")";s:3:"ⁿ";s:1:"n";s:3:"₀";s:1:"0";s:3:"₁";s:1:"1";s:3:"₂";s:1:"2";s:3:"₃";s:1:"3";s:3:"₄";s:1:"4";s:3:"₅";s:1:"5";s:3:"₆";s:1:"6";s:3:"₇";s:1:"7";s:3:"₈";s:1:"8";s:3:"₉";s:1:"9";s:3:"₊";s:1:"+";s:3:"₋";s:3:"−";s:3:"₌";s:1:"=";s:3:"₍";s:1:"(";s:3:"₎";s:1:")";s:3:"ₐ";s:1:"a";s:3:"ₑ";s:1:"e";s:3:"ₒ";s:1:"o";s:3:"ₓ";s:1:"x";s:3:"ₔ";s:2:"ə";s:3:"₨";s:2:"Rs";s:3:"℀";s:3:"a/c";s:3:"℁";s:3:"a/s";s:3:"ℂ";s:1:"C";s:3:"℃";s:3:"°C";s:3:"℅";s:3:"c/o";s:3:"℆";s:3:"c/u";s:3:"ℇ";s:2:"Ɛ";s:3:"℉";s:3:"°F";s:3:"ℊ";s:1:"g";s:3:"ℋ";s:1:"H";s:3:"ℌ";s:1:"H";s:3:"ℍ";s:1:"H";s:3:"ℎ";s:1:"h";s:3:"ℏ";s:2:"ħ";s:3:"ℐ";s:1:"I";s:3:"ℑ";s:1:"I";s:3:"ℒ";s:1:"L";s:3:"ℓ";s:1:"l";s:3:"ℕ";s:1:"N";s:3:"№";s:2:"No";s:3:"ℙ";s:1:"P";s:3:"ℚ";s:1:"Q";s:3:"ℛ";s:1:"R";s:3:"ℜ";s:1:"R";s:3:"ℝ";s:1:"R";s:3:"℠";s:2:"SM";s:3:"℡";s:3:"TEL";s:3:"™";s:2:"TM";s:3:"ℤ";s:1:"Z";s:3:"Ω";s:2:"Ω";s:3:"ℨ";s:1:"Z";s:3:"K";s:1:"K";s:3:"Å";s:3:"Å";s:3:"ℬ";s:1:"B";s:3:"ℭ";s:1:"C";s:3:"ℯ";s:1:"e";s:3:"ℰ";s:1:"E";s:3:"ℱ";s:1:"F";s:3:"ℳ";s:1:"M";s:3:"ℴ";s:1:"o";s:3:"ℵ";s:2:"א";s:3:"ℶ";s:2:"ב";s:3:"ℷ";s:2:"ג";s:3:"ℸ";s:2:"ד";s:3:"ℹ";s:1:"i";s:3:"℻";s:3:"FAX";s:3:"ℼ";s:2:"π";s:3:"ℽ";s:2:"γ";s:3:"ℾ";s:2:"Γ";s:3:"ℿ";s:2:"Π";s:3:"⅀";s:3:"∑";s:3:"ⅅ";s:1:"D";s:3:"ⅆ";s:1:"d";s:3:"ⅇ";s:1:"e";s:3:"ⅈ";s:1:"i";s:3:"ⅉ";s:1:"j";s:3:"⅓";s:5:"1⁄3";s:3:"⅔";s:5:"2⁄3";s:3:"⅕";s:5:"1⁄5";s:3:"⅖";s:5:"2⁄5";s:3:"⅗";s:5:"3⁄5";s:3:"⅘";s:5:"4⁄5";s:3:"⅙";s:5:"1⁄6";s:3:"⅚";s:5:"5⁄6";s:3:"⅛";s:5:"1⁄8";s:3:"⅜";s:5:"3⁄8";s:3:"⅝";s:5:"5⁄8";s:3:"⅞";s:5:"7⁄8";s:3:"⅟";s:4:"1⁄";s:3:"Ⅰ";s:1:"I";s:3:"Ⅱ";s:2:"II";s:3:"Ⅲ";s:3:"III";s:3:"Ⅳ";s:2:"IV";s:3:"Ⅴ";s:1:"V";s:3:"Ⅵ";s:2:"VI";s:3:"Ⅶ";s:3:"VII";s:3:"Ⅷ";s:4:"VIII";s:3:"Ⅸ";s:2:"IX";s:3:"Ⅹ";s:1:"X";s:3:"Ⅺ";s:2:"XI";s:3:"Ⅻ";s:3:"XII";s:3:"Ⅼ";s:1:"L";s:3:"Ⅽ";s:1:"C";s:3:"Ⅾ";s:1:"D";s:3:"Ⅿ";s:1:"M";s:3:"ⅰ";s:1:"i";s:3:"ⅱ";s:2:"ii";s:3:"ⅲ";s:3:"iii";s:3:"ⅳ";s:2:"iv";s:3:"ⅴ";s:1:"v";s:3:"ⅵ";s:2:"vi";s:3:"ⅶ";s:3:"vii";s:3:"ⅷ";s:4:"viii";s:3:"ⅸ";s:2:"ix";s:3:"ⅹ";s:1:"x";s:3:"ⅺ";s:2:"xi";s:3:"ⅻ";s:3:"xii";s:3:"ⅼ";s:1:"l";s:3:"ⅽ";s:1:"c";s:3:"ⅾ";s:1:"d";s:3:"ⅿ";s:1:"m";s:3:"↚";s:5:"↚";s:3:"↛";s:5:"↛";s:3:"↮";s:5:"↮";s:3:"⇍";s:5:"⇍";s:3:"⇎";s:5:"⇎";s:3:"⇏";s:5:"⇏";s:3:"∄";s:5:"∄";s:3:"∉";s:5:"∉";s:3:"∌";s:5:"∌";s:3:"∤";s:5:"∤";s:3:"∦";s:5:"∦";s:3:"∬";s:6:"∫∫";s:3:"∭";s:9:"∫∫∫";s:3:"∯";s:6:"∮∮";s:3:"∰";s:9:"∮∮∮";s:3:"≁";s:5:"≁";s:3:"≄";s:5:"≄";s:3:"≇";s:5:"≇";s:3:"≉";s:5:"≉";s:3:"≠";s:3:"≠";s:3:"≢";s:5:"≢";s:3:"≭";s:5:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:3:"≰";s:5:"≰";s:3:"≱";s:5:"≱";s:3:"≴";s:5:"≴";s:3:"≵";s:5:"≵";s:3:"≸";s:5:"≸";s:3:"≹";s:5:"≹";s:3:"⊀";s:5:"⊀";s:3:"⊁";s:5:"⊁";s:3:"⊄";s:5:"⊄";s:3:"⊅";s:5:"⊅";s:3:"⊈";s:5:"⊈";s:3:"⊉";s:5:"⊉";s:3:"⊬";s:5:"⊬";s:3:"⊭";s:5:"⊭";s:3:"⊮";s:5:"⊮";s:3:"⊯";s:5:"⊯";s:3:"⋠";s:5:"⋠";s:3:"⋡";s:5:"⋡";s:3:"⋢";s:5:"⋢";s:3:"⋣";s:5:"⋣";s:3:"⋪";s:5:"⋪";s:3:"⋫";s:5:"⋫";s:3:"⋬";s:5:"⋬";s:3:"⋭";s:5:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:3:"①";s:1:"1";s:3:"②";s:1:"2";s:3:"③";s:1:"3";s:3:"④";s:1:"4";s:3:"⑤";s:1:"5";s:3:"⑥";s:1:"6";s:3:"⑦";s:1:"7";s:3:"⑧";s:1:"8";s:3:"⑨";s:1:"9";s:3:"⑩";s:2:"10";s:3:"⑪";s:2:"11";s:3:"⑫";s:2:"12";s:3:"⑬";s:2:"13";s:3:"⑭";s:2:"14";s:3:"⑮";s:2:"15";s:3:"⑯";s:2:"16";s:3:"⑰";s:2:"17";s:3:"⑱";s:2:"18";s:3:"⑲";s:2:"19";s:3:"⑳";s:2:"20";s:3:"⑴";s:3:"(1)";s:3:"⑵";s:3:"(2)";s:3:"⑶";s:3:"(3)";s:3:"⑷";s:3:"(4)";s:3:"⑸";s:3:"(5)";s:3:"⑹";s:3:"(6)";s:3:"⑺";s:3:"(7)";s:3:"⑻";s:3:"(8)";s:3:"⑼";s:3:"(9)";s:3:"⑽";s:4:"(10)";s:3:"⑾";s:4:"(11)";s:3:"⑿";s:4:"(12)";s:3:"⒀";s:4:"(13)";s:3:"⒁";s:4:"(14)";s:3:"⒂";s:4:"(15)";s:3:"⒃";s:4:"(16)";s:3:"⒄";s:4:"(17)";s:3:"⒅";s:4:"(18)";s:3:"⒆";s:4:"(19)";s:3:"⒇";s:4:"(20)";s:3:"⒈";s:2:"1.";s:3:"⒉";s:2:"2.";s:3:"⒊";s:2:"3.";s:3:"⒋";s:2:"4.";s:3:"⒌";s:2:"5.";s:3:"⒍";s:2:"6.";s:3:"⒎";s:2:"7.";s:3:"⒏";s:2:"8.";s:3:"⒐";s:2:"9.";s:3:"⒑";s:3:"10.";s:3:"⒒";s:3:"11.";s:3:"⒓";s:3:"12.";s:3:"⒔";s:3:"13.";s:3:"⒕";s:3:"14.";s:3:"⒖";s:3:"15.";s:3:"⒗";s:3:"16.";s:3:"⒘";s:3:"17.";s:3:"⒙";s:3:"18.";s:3:"⒚";s:3:"19.";s:3:"⒛";s:3:"20.";s:3:"⒜";s:3:"(a)";s:3:"⒝";s:3:"(b)";s:3:"⒞";s:3:"(c)";s:3:"⒟";s:3:"(d)";s:3:"⒠";s:3:"(e)";s:3:"⒡";s:3:"(f)";s:3:"⒢";s:3:"(g)";s:3:"⒣";s:3:"(h)";s:3:"⒤";s:3:"(i)";s:3:"⒥";s:3:"(j)";s:3:"⒦";s:3:"(k)";s:3:"⒧";s:3:"(l)";s:3:"⒨";s:3:"(m)";s:3:"⒩";s:3:"(n)";s:3:"⒪";s:3:"(o)";s:3:"⒫";s:3:"(p)";s:3:"⒬";s:3:"(q)";s:3:"⒭";s:3:"(r)";s:3:"⒮";s:3:"(s)";s:3:"⒯";s:3:"(t)";s:3:"⒰";s:3:"(u)";s:3:"⒱";s:3:"(v)";s:3:"⒲";s:3:"(w)";s:3:"⒳";s:3:"(x)";s:3:"⒴";s:3:"(y)";s:3:"⒵";s:3:"(z)";s:3:"Ⓐ";s:1:"A";s:3:"Ⓑ";s:1:"B";s:3:"Ⓒ";s:1:"C";s:3:"Ⓓ";s:1:"D";s:3:"Ⓔ";s:1:"E";s:3:"Ⓕ";s:1:"F";s:3:"Ⓖ";s:1:"G";s:3:"Ⓗ";s:1:"H";s:3:"Ⓘ";s:1:"I";s:3:"Ⓙ";s:1:"J";s:3:"Ⓚ";s:1:"K";s:3:"Ⓛ";s:1:"L";s:3:"Ⓜ";s:1:"M";s:3:"Ⓝ";s:1:"N";s:3:"Ⓞ";s:1:"O";s:3:"Ⓟ";s:1:"P";s:3:"Ⓠ";s:1:"Q";s:3:"Ⓡ";s:1:"R";s:3:"Ⓢ";s:1:"S";s:3:"Ⓣ";s:1:"T";s:3:"Ⓤ";s:1:"U";s:3:"Ⓥ";s:1:"V";s:3:"Ⓦ";s:1:"W";s:3:"Ⓧ";s:1:"X";s:3:"Ⓨ";s:1:"Y";s:3:"Ⓩ";s:1:"Z";s:3:"ⓐ";s:1:"a";s:3:"ⓑ";s:1:"b";s:3:"ⓒ";s:1:"c";s:3:"ⓓ";s:1:"d";s:3:"ⓔ";s:1:"e";s:3:"ⓕ";s:1:"f";s:3:"ⓖ";s:1:"g";s:3:"ⓗ";s:1:"h";s:3:"ⓘ";s:1:"i";s:3:"ⓙ";s:1:"j";s:3:"ⓚ";s:1:"k";s:3:"ⓛ";s:1:"l";s:3:"ⓜ";s:1:"m";s:3:"ⓝ";s:1:"n";s:3:"ⓞ";s:1:"o";s:3:"ⓟ";s:1:"p";s:3:"ⓠ";s:1:"q";s:3:"ⓡ";s:1:"r";s:3:"ⓢ";s:1:"s";s:3:"ⓣ";s:1:"t";s:3:"ⓤ";s:1:"u";s:3:"ⓥ";s:1:"v";s:3:"ⓦ";s:1:"w";s:3:"ⓧ";s:1:"x";s:3:"ⓨ";s:1:"y";s:3:"ⓩ";s:1:"z";s:3:"⓪";s:1:"0";s:3:"⨌";s:12:"∫∫∫∫";s:3:"⩴";s:3:"::=";s:3:"⩵";s:2:"==";s:3:"⩶";s:3:"===";s:3:"⫝̸";s:5:"⫝̸";s:3:"ⵯ";s:3:"ⵡ";s:3:"⺟";s:3:"母";s:3:"⻳";s:3:"龟";s:3:"⼀";s:3:"一";s:3:"⼁";s:3:"丨";s:3:"⼂";s:3:"丶";s:3:"⼃";s:3:"丿";s:3:"⼄";s:3:"乙";s:3:"⼅";s:3:"亅";s:3:"⼆";s:3:"二";s:3:"⼇";s:3:"亠";s:3:"⼈";s:3:"人";s:3:"⼉";s:3:"儿";s:3:"⼊";s:3:"入";s:3:"⼋";s:3:"八";s:3:"⼌";s:3:"冂";s:3:"⼍";s:3:"冖";s:3:"⼎";s:3:"冫";s:3:"⼏";s:3:"几";s:3:"⼐";s:3:"凵";s:3:"⼑";s:3:"刀";s:3:"⼒";s:3:"力";s:3:"⼓";s:3:"勹";s:3:"⼔";s:3:"匕";s:3:"⼕";s:3:"匚";s:3:"⼖";s:3:"匸";s:3:"⼗";s:3:"十";s:3:"⼘";s:3:"卜";s:3:"⼙";s:3:"卩";s:3:"⼚";s:3:"厂";s:3:"⼛";s:3:"厶";s:3:"⼜";s:3:"又";s:3:"⼝";s:3:"口";s:3:"⼞";s:3:"囗";s:3:"⼟";s:3:"土";s:3:"⼠";s:3:"士";s:3:"⼡";s:3:"夂";s:3:"⼢";s:3:"夊";s:3:"⼣";s:3:"夕";s:3:"⼤";s:3:"大";s:3:"⼥";s:3:"女";s:3:"⼦";s:3:"子";s:3:"⼧";s:3:"宀";s:3:"⼨";s:3:"寸";s:3:"⼩";s:3:"小";s:3:"⼪";s:3:"尢";s:3:"⼫";s:3:"尸";s:3:"⼬";s:3:"屮";s:3:"⼭";s:3:"山";s:3:"⼮";s:3:"巛";s:3:"⼯";s:3:"工";s:3:"⼰";s:3:"己";s:3:"⼱";s:3:"巾";s:3:"⼲";s:3:"干";s:3:"⼳";s:3:"幺";s:3:"⼴";s:3:"广";s:3:"⼵";s:3:"廴";s:3:"⼶";s:3:"廾";s:3:"⼷";s:3:"弋";s:3:"⼸";s:3:"弓";s:3:"⼹";s:3:"彐";s:3:"⼺";s:3:"彡";s:3:"⼻";s:3:"彳";s:3:"⼼";s:3:"心";s:3:"⼽";s:3:"戈";s:3:"⼾";s:3:"戶";s:3:"⼿";s:3:"手";s:3:"⽀";s:3:"支";s:3:"⽁";s:3:"攴";s:3:"⽂";s:3:"文";s:3:"⽃";s:3:"斗";s:3:"⽄";s:3:"斤";s:3:"⽅";s:3:"方";s:3:"⽆";s:3:"无";s:3:"⽇";s:3:"日";s:3:"⽈";s:3:"曰";s:3:"⽉";s:3:"月";s:3:"⽊";s:3:"木";s:3:"⽋";s:3:"欠";s:3:"⽌";s:3:"止";s:3:"⽍";s:3:"歹";s:3:"⽎";s:3:"殳";s:3:"⽏";s:3:"毋";s:3:"⽐";s:3:"比";s:3:"⽑";s:3:"毛";s:3:"⽒";s:3:"氏";s:3:"⽓";s:3:"气";s:3:"⽔";s:3:"水";s:3:"⽕";s:3:"火";s:3:"⽖";s:3:"爪";s:3:"⽗";s:3:"父";s:3:"⽘";s:3:"爻";s:3:"⽙";s:3:"爿";s:3:"⽚";s:3:"片";s:3:"⽛";s:3:"牙";s:3:"⽜";s:3:"牛";s:3:"⽝";s:3:"犬";s:3:"⽞";s:3:"玄";s:3:"⽟";s:3:"玉";s:3:"⽠";s:3:"瓜";s:3:"⽡";s:3:"瓦";s:3:"⽢";s:3:"甘";s:3:"⽣";s:3:"生";s:3:"⽤";s:3:"用";s:3:"⽥";s:3:"田";s:3:"⽦";s:3:"疋";s:3:"⽧";s:3:"疒";s:3:"⽨";s:3:"癶";s:3:"⽩";s:3:"白";s:3:"⽪";s:3:"皮";s:3:"⽫";s:3:"皿";s:3:"⽬";s:3:"目";s:3:"⽭";s:3:"矛";s:3:"⽮";s:3:"矢";s:3:"⽯";s:3:"石";s:3:"⽰";s:3:"示";s:3:"⽱";s:3:"禸";s:3:"⽲";s:3:"禾";s:3:"⽳";s:3:"穴";s:3:"⽴";s:3:"立";s:3:"⽵";s:3:"竹";s:3:"⽶";s:3:"米";s:3:"⽷";s:3:"糸";s:3:"⽸";s:3:"缶";s:3:"⽹";s:3:"网";s:3:"⽺";s:3:"羊";s:3:"⽻";s:3:"羽";s:3:"⽼";s:3:"老";s:3:"⽽";s:3:"而";s:3:"⽾";s:3:"耒";s:3:"⽿";s:3:"耳";s:3:"⾀";s:3:"聿";s:3:"⾁";s:3:"肉";s:3:"⾂";s:3:"臣";s:3:"⾃";s:3:"自";s:3:"⾄";s:3:"至";s:3:"⾅";s:3:"臼";s:3:"⾆";s:3:"舌";s:3:"⾇";s:3:"舛";s:3:"⾈";s:3:"舟";s:3:"⾉";s:3:"艮";s:3:"⾊";s:3:"色";s:3:"⾋";s:3:"艸";s:3:"⾌";s:3:"虍";s:3:"⾍";s:3:"虫";s:3:"⾎";s:3:"血";s:3:"⾏";s:3:"行";s:3:"⾐";s:3:"衣";s:3:"⾑";s:3:"襾";s:3:"⾒";s:3:"見";s:3:"⾓";s:3:"角";s:3:"⾔";s:3:"言";s:3:"⾕";s:3:"谷";s:3:"⾖";s:3:"豆";s:3:"⾗";s:3:"豕";s:3:"⾘";s:3:"豸";s:3:"⾙";s:3:"貝";s:3:"⾚";s:3:"赤";s:3:"⾛";s:3:"走";s:3:"⾜";s:3:"足";s:3:"⾝";s:3:"身";s:3:"⾞";s:3:"車";s:3:"⾟";s:3:"辛";s:3:"⾠";s:3:"辰";s:3:"⾡";s:3:"辵";s:3:"⾢";s:3:"邑";s:3:"⾣";s:3:"酉";s:3:"⾤";s:3:"釆";s:3:"⾥";s:3:"里";s:3:"⾦";s:3:"金";s:3:"⾧";s:3:"長";s:3:"⾨";s:3:"門";s:3:"⾩";s:3:"阜";s:3:"⾪";s:3:"隶";s:3:"⾫";s:3:"隹";s:3:"⾬";s:3:"雨";s:3:"⾭";s:3:"靑";s:3:"⾮";s:3:"非";s:3:"⾯";s:3:"面";s:3:"⾰";s:3:"革";s:3:"⾱";s:3:"韋";s:3:"⾲";s:3:"韭";s:3:"⾳";s:3:"音";s:3:"⾴";s:3:"頁";s:3:"⾵";s:3:"風";s:3:"⾶";s:3:"飛";s:3:"⾷";s:3:"食";s:3:"⾸";s:3:"首";s:3:"⾹";s:3:"香";s:3:"⾺";s:3:"馬";s:3:"⾻";s:3:"骨";s:3:"⾼";s:3:"高";s:3:"⾽";s:3:"髟";s:3:"⾾";s:3:"鬥";s:3:"⾿";s:3:"鬯";s:3:"⿀";s:3:"鬲";s:3:"⿁";s:3:"鬼";s:3:"⿂";s:3:"魚";s:3:"⿃";s:3:"鳥";s:3:"⿄";s:3:"鹵";s:3:"⿅";s:3:"鹿";s:3:"⿆";s:3:"麥";s:3:"⿇";s:3:"麻";s:3:"⿈";s:3:"黃";s:3:"⿉";s:3:"黍";s:3:"⿊";s:3:"黑";s:3:"⿋";s:3:"黹";s:3:"⿌";s:3:"黽";s:3:"⿍";s:3:"鼎";s:3:"⿎";s:3:"鼓";s:3:"⿏";s:3:"鼠";s:3:"⿐";s:3:"鼻";s:3:"⿑";s:3:"齊";s:3:"⿒";s:3:"齒";s:3:"⿓";s:3:"龍";s:3:"⿔";s:3:"龜";s:3:"⿕";s:3:"龠";s:3:"　";s:1:" ";s:3:"〶";s:3:"〒";s:3:"〸";s:3:"十";s:3:"〹";s:3:"卄";s:3:"〺";s:3:"卅";s:3:"が";s:6:"が";s:3:"ぎ";s:6:"ぎ";s:3:"ぐ";s:6:"ぐ";s:3:"げ";s:6:"げ";s:3:"ご";s:6:"ご";s:3:"ざ";s:6:"ざ";s:3:"じ";s:6:"じ";s:3:"ず";s:6:"ず";s:3:"ぜ";s:6:"ぜ";s:3:"ぞ";s:6:"ぞ";s:3:"だ";s:6:"だ";s:3:"ぢ";s:6:"ぢ";s:3:"づ";s:6:"づ";s:3:"で";s:6:"で";s:3:"ど";s:6:"ど";s:3:"ば";s:6:"ば";s:3:"ぱ";s:6:"ぱ";s:3:"び";s:6:"び";s:3:"ぴ";s:6:"ぴ";s:3:"ぶ";s:6:"ぶ";s:3:"ぷ";s:6:"ぷ";s:3:"べ";s:6:"べ";s:3:"ぺ";s:6:"ぺ";s:3:"ぼ";s:6:"ぼ";s:3:"ぽ";s:6:"ぽ";s:3:"ゔ";s:6:"ゔ";s:3:"゛";s:4:" ゙";s:3:"゜";s:4:" ゚";s:3:"ゞ";s:6:"ゞ";s:3:"ゟ";s:6:"より";s:3:"ガ";s:6:"ガ";s:3:"ギ";s:6:"ギ";s:3:"グ";s:6:"グ";s:3:"ゲ";s:6:"ゲ";s:3:"ゴ";s:6:"ゴ";s:3:"ザ";s:6:"ザ";s:3:"ジ";s:6:"ジ";s:3:"ズ";s:6:"ズ";s:3:"ゼ";s:6:"ゼ";s:3:"ゾ";s:6:"ゾ";s:3:"ダ";s:6:"ダ";s:3:"ヂ";s:6:"ヂ";s:3:"ヅ";s:6:"ヅ";s:3:"デ";s:6:"デ";s:3:"ド";s:6:"ド";s:3:"バ";s:6:"バ";s:3:"パ";s:6:"パ";s:3:"ビ";s:6:"ビ";s:3:"ピ";s:6:"ピ";s:3:"ブ";s:6:"ブ";s:3:"プ";s:6:"プ";s:3:"ベ";s:6:"ベ";s:3:"ペ";s:6:"ペ";s:3:"ボ";s:6:"ボ";s:3:"ポ";s:6:"ポ";s:3:"ヴ";s:6:"ヴ";s:3:"ヷ";s:6:"ヷ";s:3:"ヸ";s:6:"ヸ";s:3:"ヹ";s:6:"ヹ";s:3:"ヺ";s:6:"ヺ";s:3:"ヾ";s:6:"ヾ";s:3:"ヿ";s:6:"コト";s:3:"ㄱ";s:3:"ᄀ";s:3:"ㄲ";s:3:"ᄁ";s:3:"ㄳ";s:3:"ᆪ";s:3:"ㄴ";s:3:"ᄂ";s:3:"ㄵ";s:3:"ᆬ";s:3:"ㄶ";s:3:"ᆭ";s:3:"ㄷ";s:3:"ᄃ";s:3:"ㄸ";s:3:"ᄄ";s:3:"ㄹ";s:3:"ᄅ";s:3:"ㄺ";s:3:"ᆰ";s:3:"ㄻ";s:3:"ᆱ";s:3:"ㄼ";s:3:"ᆲ";s:3:"ㄽ";s:3:"ᆳ";s:3:"ㄾ";s:3:"ᆴ";s:3:"ㄿ";s:3:"ᆵ";s:3:"ㅀ";s:3:"ᄚ";s:3:"ㅁ";s:3:"ᄆ";s:3:"ㅂ";s:3:"ᄇ";s:3:"ㅃ";s:3:"ᄈ";s:3:"ㅄ";s:3:"ᄡ";s:3:"ㅅ";s:3:"ᄉ";s:3:"ㅆ";s:3:"ᄊ";s:3:"ㅇ";s:3:"ᄋ";s:3:"ㅈ";s:3:"ᄌ";s:3:"ㅉ";s:3:"ᄍ";s:3:"ㅊ";s:3:"ᄎ";s:3:"ㅋ";s:3:"ᄏ";s:3:"ㅌ";s:3:"ᄐ";s:3:"ㅍ";s:3:"ᄑ";s:3:"ㅎ";s:3:"ᄒ";s:3:"ㅏ";s:3:"ᅡ";s:3:"ㅐ";s:3:"ᅢ";s:3:"ㅑ";s:3:"ᅣ";s:3:"ㅒ";s:3:"ᅤ";s:3:"ㅓ";s:3:"ᅥ";s:3:"ㅔ";s:3:"ᅦ";s:3:"ㅕ";s:3:"ᅧ";s:3:"ㅖ";s:3:"ᅨ";s:3:"ㅗ";s:3:"ᅩ";s:3:"ㅘ";s:3:"ᅪ";s:3:"ㅙ";s:3:"ᅫ";s:3:"ㅚ";s:3:"ᅬ";s:3:"ㅛ";s:3:"ᅭ";s:3:"ㅜ";s:3:"ᅮ";s:3:"ㅝ";s:3:"ᅯ";s:3:"ㅞ";s:3:"ᅰ";s:3:"ㅟ";s:3:"ᅱ";s:3:"ㅠ";s:3:"ᅲ";s:3:"ㅡ";s:3:"ᅳ";s:3:"ㅢ";s:3:"ᅴ";s:3:"ㅣ";s:3:"ᅵ";s:3:"ㅤ";s:3:"ᅠ";s:3:"ㅥ";s:3:"ᄔ";s:3:"ㅦ";s:3:"ᄕ";s:3:"ㅧ";s:3:"ᇇ";s:3:"ㅨ";s:3:"ᇈ";s:3:"ㅩ";s:3:"ᇌ";s:3:"ㅪ";s:3:"ᇎ";s:3:"ㅫ";s:3:"ᇓ";s:3:"ㅬ";s:3:"ᇗ";s:3:"ㅭ";s:3:"ᇙ";s:3:"ㅮ";s:3:"ᄜ";s:3:"ㅯ";s:3:"ᇝ";s:3:"ㅰ";s:3:"ᇟ";s:3:"ㅱ";s:3:"ᄝ";s:3:"ㅲ";s:3:"ᄞ";s:3:"ㅳ";s:3:"ᄠ";s:3:"ㅴ";s:3:"ᄢ";s:3:"ㅵ";s:3:"ᄣ";s:3:"ㅶ";s:3:"ᄧ";s:3:"ㅷ";s:3:"ᄩ";s:3:"ㅸ";s:3:"ᄫ";s:3:"ㅹ";s:3:"ᄬ";s:3:"ㅺ";s:3:"ᄭ";s:3:"ㅻ";s:3:"ᄮ";s:3:"ㅼ";s:3:"ᄯ";s:3:"ㅽ";s:3:"ᄲ";s:3:"ㅾ";s:3:"ᄶ";s:3:"ㅿ";s:3:"ᅀ";s:3:"ㆀ";s:3:"ᅇ";s:3:"ㆁ";s:3:"ᅌ";s:3:"ㆂ";s:3:"ᇱ";s:3:"ㆃ";s:3:"ᇲ";s:3:"ㆄ";s:3:"ᅗ";s:3:"ㆅ";s:3:"ᅘ";s:3:"ㆆ";s:3:"ᅙ";s:3:"ㆇ";s:3:"ᆄ";s:3:"ㆈ";s:3:"ᆅ";s:3:"ㆉ";s:3:"ᆈ";s:3:"ㆊ";s:3:"ᆑ";s:3:"ㆋ";s:3:"ᆒ";s:3:"ㆌ";s:3:"ᆔ";s:3:"ㆍ";s:3:"ᆞ";s:3:"ㆎ";s:3:"ᆡ";s:3:"㆒";s:3:"一";s:3:"㆓";s:3:"二";s:3:"㆔";s:3:"三";s:3:"㆕";s:3:"四";s:3:"㆖";s:3:"上";s:3:"㆗";s:3:"中";s:3:"㆘";s:3:"下";s:3:"㆙";s:3:"甲";s:3:"㆚";s:3:"乙";s:3:"㆛";s:3:"丙";s:3:"㆜";s:3:"丁";s:3:"㆝";s:3:"天";s:3:"㆞";s:3:"地";s:3:"㆟";s:3:"人";s:3:"㈀";s:5:"(ᄀ)";s:3:"㈁";s:5:"(ᄂ)";s:3:"㈂";s:5:"(ᄃ)";s:3:"㈃";s:5:"(ᄅ)";s:3:"㈄";s:5:"(ᄆ)";s:3:"㈅";s:5:"(ᄇ)";s:3:"㈆";s:5:"(ᄉ)";s:3:"㈇";s:5:"(ᄋ)";s:3:"㈈";s:5:"(ᄌ)";s:3:"㈉";s:5:"(ᄎ)";s:3:"㈊";s:5:"(ᄏ)";s:3:"㈋";s:5:"(ᄐ)";s:3:"㈌";s:5:"(ᄑ)";s:3:"㈍";s:5:"(ᄒ)";s:3:"㈎";s:8:"(가)";s:3:"㈏";s:8:"(나)";s:3:"㈐";s:8:"(다)";s:3:"㈑";s:8:"(라)";s:3:"㈒";s:8:"(마)";s:3:"㈓";s:8:"(바)";s:3:"㈔";s:8:"(사)";s:3:"㈕";s:8:"(아)";s:3:"㈖";s:8:"(자)";s:3:"㈗";s:8:"(차)";s:3:"㈘";s:8:"(카)";s:3:"㈙";s:8:"(타)";s:3:"㈚";s:8:"(파)";s:3:"㈛";s:8:"(하)";s:3:"㈜";s:8:"(주)";s:3:"㈝";s:17:"(오전)";s:3:"㈞";s:14:"(오후)";s:3:"㈠";s:5:"(一)";s:3:"㈡";s:5:"(二)";s:3:"㈢";s:5:"(三)";s:3:"㈣";s:5:"(四)";s:3:"㈤";s:5:"(五)";s:3:"㈥";s:5:"(六)";s:3:"㈦";s:5:"(七)";s:3:"㈧";s:5:"(八)";s:3:"㈨";s:5:"(九)";s:3:"㈩";s:5:"(十)";s:3:"㈪";s:5:"(月)";s:3:"㈫";s:5:"(火)";s:3:"㈬";s:5:"(水)";s:3:"㈭";s:5:"(木)";s:3:"㈮";s:5:"(金)";s:3:"㈯";s:5:"(土)";s:3:"㈰";s:5:"(日)";s:3:"㈱";s:5:"(株)";s:3:"㈲";s:5:"(有)";s:3:"㈳";s:5:"(社)";s:3:"㈴";s:5:"(名)";s:3:"㈵";s:5:"(特)";s:3:"㈶";s:5:"(財)";s:3:"㈷";s:5:"(祝)";s:3:"㈸";s:5:"(労)";s:3:"㈹";s:5:"(代)";s:3:"㈺";s:5:"(呼)";s:3:"㈻";s:5:"(学)";s:3:"㈼";s:5:"(監)";s:3:"㈽";s:5:"(企)";s:3:"㈾";s:5:"(資)";s:3:"㈿";s:5:"(協)";s:3:"㉀";s:5:"(祭)";s:3:"㉁";s:5:"(休)";s:3:"㉂";s:5:"(自)";s:3:"㉃";s:5:"(至)";s:3:"㉐";s:3:"PTE";s:3:"㉑";s:2:"21";s:3:"㉒";s:2:"22";s:3:"㉓";s:2:"23";s:3:"㉔";s:2:"24";s:3:"㉕";s:2:"25";s:3:"㉖";s:2:"26";s:3:"㉗";s:2:"27";s:3:"㉘";s:2:"28";s:3:"㉙";s:2:"29";s:3:"㉚";s:2:"30";s:3:"㉛";s:2:"31";s:3:"㉜";s:2:"32";s:3:"㉝";s:2:"33";s:3:"㉞";s:2:"34";s:3:"㉟";s:2:"35";s:3:"㉠";s:3:"ᄀ";s:3:"㉡";s:3:"ᄂ";s:3:"㉢";s:3:"ᄃ";s:3:"㉣";s:3:"ᄅ";s:3:"㉤";s:3:"ᄆ";s:3:"㉥";s:3:"ᄇ";s:3:"㉦";s:3:"ᄉ";s:3:"㉧";s:3:"ᄋ";s:3:"㉨";s:3:"ᄌ";s:3:"㉩";s:3:"ᄎ";s:3:"㉪";s:3:"ᄏ";s:3:"㉫";s:3:"ᄐ";s:3:"㉬";s:3:"ᄑ";s:3:"㉭";s:3:"ᄒ";s:3:"㉮";s:6:"가";s:3:"㉯";s:6:"나";s:3:"㉰";s:6:"다";s:3:"㉱";s:6:"라";s:3:"㉲";s:6:"마";s:3:"㉳";s:6:"바";s:3:"㉴";s:6:"사";s:3:"㉵";s:6:"아";s:3:"㉶";s:6:"자";s:3:"㉷";s:6:"차";s:3:"㉸";s:6:"카";s:3:"㉹";s:6:"타";s:3:"㉺";s:6:"파";s:3:"㉻";s:6:"하";s:3:"㉼";s:15:"참고";s:3:"㉽";s:12:"주의";s:3:"㉾";s:6:"우";s:3:"㊀";s:3:"一";s:3:"㊁";s:3:"二";s:3:"㊂";s:3:"三";s:3:"㊃";s:3:"四";s:3:"㊄";s:3:"五";s:3:"㊅";s:3:"六";s:3:"㊆";s:3:"七";s:3:"㊇";s:3:"八";s:3:"㊈";s:3:"九";s:3:"㊉";s:3:"十";s:3:"㊊";s:3:"月";s:3:"㊋";s:3:"火";s:3:"㊌";s:3:"水";s:3:"㊍";s:3:"木";s:3:"㊎";s:3:"金";s:3:"㊏";s:3:"土";s:3:"㊐";s:3:"日";s:3:"㊑";s:3:"株";s:3:"㊒";s:3:"有";s:3:"㊓";s:3:"社";s:3:"㊔";s:3:"名";s:3:"㊕";s:3:"特";s:3:"㊖";s:3:"財";s:3:"㊗";s:3:"祝";s:3:"㊘";s:3:"労";s:3:"㊙";s:3:"秘";s:3:"㊚";s:3:"男";s:3:"㊛";s:3:"女";s:3:"㊜";s:3:"適";s:3:"㊝";s:3:"優";s:3:"㊞";s:3:"印";s:3:"㊟";s:3:"注";s:3:"㊠";s:3:"項";s:3:"㊡";s:3:"休";s:3:"㊢";s:3:"写";s:3:"㊣";s:3:"正";s:3:"㊤";s:3:"上";s:3:"㊥";s:3:"中";s:3:"㊦";s:3:"下";s:3:"㊧";s:3:"左";s:3:"㊨";s:3:"右";s:3:"㊩";s:3:"医";s:3:"㊪";s:3:"宗";s:3:"㊫";s:3:"学";s:3:"㊬";s:3:"監";s:3:"㊭";s:3:"企";s:3:"㊮";s:3:"資";s:3:"㊯";s:3:"協";s:3:"㊰";s:3:"夜";s:3:"㊱";s:2:"36";s:3:"㊲";s:2:"37";s:3:"㊳";s:2:"38";s:3:"㊴";s:2:"39";s:3:"㊵";s:2:"40";s:3:"㊶";s:2:"41";s:3:"㊷";s:2:"42";s:3:"㊸";s:2:"43";s:3:"㊹";s:2:"44";s:3:"㊺";s:2:"45";s:3:"㊻";s:2:"46";s:3:"㊼";s:2:"47";s:3:"㊽";s:2:"48";s:3:"㊾";s:2:"49";s:3:"㊿";s:2:"50";s:3:"㋀";s:4:"1月";s:3:"㋁";s:4:"2月";s:3:"㋂";s:4:"3月";s:3:"㋃";s:4:"4月";s:3:"㋄";s:4:"5月";s:3:"㋅";s:4:"6月";s:3:"㋆";s:4:"7月";s:3:"㋇";s:4:"8月";s:3:"㋈";s:4:"9月";s:3:"㋉";s:5:"10月";s:3:"㋊";s:5:"11月";s:3:"㋋";s:5:"12月";s:3:"㋌";s:2:"Hg";s:3:"㋍";s:3:"erg";s:3:"㋎";s:2:"eV";s:3:"㋏";s:3:"LTD";s:3:"㋐";s:3:"ア";s:3:"㋑";s:3:"イ";s:3:"㋒";s:3:"ウ";s:3:"㋓";s:3:"エ";s:3:"㋔";s:3:"オ";s:3:"㋕";s:3:"カ";s:3:"㋖";s:3:"キ";s:3:"㋗";s:3:"ク";s:3:"㋘";s:3:"ケ";s:3:"㋙";s:3:"コ";s:3:"㋚";s:3:"サ";s:3:"㋛";s:3:"シ";s:3:"㋜";s:3:"ス";s:3:"㋝";s:3:"セ";s:3:"㋞";s:3:"ソ";s:3:"㋟";s:3:"タ";s:3:"㋠";s:3:"チ";s:3:"㋡";s:3:"ツ";s:3:"㋢";s:3:"テ";s:3:"㋣";s:3:"ト";s:3:"㋤";s:3:"ナ";s:3:"㋥";s:3:"ニ";s:3:"㋦";s:3:"ヌ";s:3:"㋧";s:3:"ネ";s:3:"㋨";s:3:"ノ";s:3:"㋩";s:3:"ハ";s:3:"㋪";s:3:"ヒ";s:3:"㋫";s:3:"フ";s:3:"㋬";s:3:"ヘ";s:3:"㋭";s:3:"ホ";s:3:"㋮";s:3:"マ";s:3:"㋯";s:3:"ミ";s:3:"㋰";s:3:"ム";s:3:"㋱";s:3:"メ";s:3:"㋲";s:3:"モ";s:3:"㋳";s:3:"ヤ";s:3:"㋴";s:3:"ユ";s:3:"㋵";s:3:"ヨ";s:3:"㋶";s:3:"ラ";s:3:"㋷";s:3:"リ";s:3:"㋸";s:3:"ル";s:3:"㋹";s:3:"レ";s:3:"㋺";s:3:"ロ";s:3:"㋻";s:3:"ワ";s:3:"㋼";s:3:"ヰ";s:3:"㋽";s:3:"ヱ";s:3:"㋾";s:3:"ヲ";s:3:"㌀";s:15:"アパート";s:3:"㌁";s:12:"アルファ";s:3:"㌂";s:15:"アンペア";s:3:"㌃";s:9:"アール";s:3:"㌄";s:15:"イニング";s:3:"㌅";s:9:"インチ";s:3:"㌆";s:9:"ウォン";s:3:"㌇";s:18:"エスクード";s:3:"㌈";s:12:"エーカー";s:3:"㌉";s:9:"オンス";s:3:"㌊";s:9:"オーム";s:3:"㌋";s:9:"カイリ";s:3:"㌌";s:12:"カラット";s:3:"㌍";s:12:"カロリー";s:3:"㌎";s:12:"ガロン";s:3:"㌏";s:12:"ガンマ";s:3:"㌐";s:12:"ギガ";s:3:"㌑";s:12:"ギニー";s:3:"㌒";s:12:"キュリー";s:3:"㌓";s:18:"ギルダー";s:3:"㌔";s:6:"キロ";s:3:"㌕";s:18:"キログラム";s:3:"㌖";s:18:"キロメートル";s:3:"㌗";s:15:"キロワット";s:3:"㌘";s:12:"グラム";s:3:"㌙";s:18:"グラムトン";s:3:"㌚";s:18:"クルゼイロ";s:3:"㌛";s:12:"クローネ";s:3:"㌜";s:9:"ケース";s:3:"㌝";s:9:"コルナ";s:3:"㌞";s:12:"コーポ";s:3:"㌟";s:12:"サイクル";s:3:"㌠";s:15:"サンチーム";s:3:"㌡";s:15:"シリング";s:3:"㌢";s:9:"センチ";s:3:"㌣";s:9:"セント";s:3:"㌤";s:12:"ダース";s:3:"㌥";s:9:"デシ";s:3:"㌦";s:9:"ドル";s:3:"㌧";s:6:"トン";s:3:"㌨";s:6:"ナノ";s:3:"㌩";s:9:"ノット";s:3:"㌪";s:9:"ハイツ";s:3:"㌫";s:18:"パーセント";s:3:"㌬";s:12:"パーツ";s:3:"㌭";s:15:"バーレル";s:3:"㌮";s:18:"ピアストル";s:3:"㌯";s:12:"ピクル";s:3:"㌰";s:9:"ピコ";s:3:"㌱";s:9:"ビル";s:3:"㌲";s:18:"ファラッド";s:3:"㌳";s:12:"フィート";s:3:"㌴";s:18:"ブッシェル";s:3:"㌵";s:9:"フラン";s:3:"㌶";s:15:"ヘクタール";s:3:"㌷";s:9:"ペソ";s:3:"㌸";s:12:"ペニヒ";s:3:"㌹";s:9:"ヘルツ";s:3:"㌺";s:12:"ペンス";s:3:"㌻";s:15:"ページ";s:3:"㌼";s:12:"ベータ";s:3:"㌽";s:15:"ポイント";s:3:"㌾";s:12:"ボルト";s:3:"㌿";s:6:"ホン";s:3:"㍀";s:15:"ポンド";s:3:"㍁";s:9:"ホール";s:3:"㍂";s:9:"ホーン";s:3:"㍃";s:12:"マイクロ";s:3:"㍄";s:9:"マイル";s:3:"㍅";s:9:"マッハ";s:3:"㍆";s:9:"マルク";s:3:"㍇";s:15:"マンション";s:3:"㍈";s:12:"ミクロン";s:3:"㍉";s:6:"ミリ";s:3:"㍊";s:18:"ミリバール";s:3:"㍋";s:9:"メガ";s:3:"㍌";s:15:"メガトン";s:3:"㍍";s:12:"メートル";s:3:"㍎";s:12:"ヤード";s:3:"㍏";s:9:"ヤール";s:3:"㍐";s:9:"ユアン";s:3:"㍑";s:12:"リットル";s:3:"㍒";s:6:"リラ";s:3:"㍓";s:12:"ルピー";s:3:"㍔";s:15:"ルーブル";s:3:"㍕";s:6:"レム";s:3:"㍖";s:18:"レントゲン";s:3:"㍗";s:9:"ワット";s:3:"㍘";s:4:"0点";s:3:"㍙";s:4:"1点";s:3:"㍚";s:4:"2点";s:3:"㍛";s:4:"3点";s:3:"㍜";s:4:"4点";s:3:"㍝";s:4:"5点";s:3:"㍞";s:4:"6点";s:3:"㍟";s:4:"7点";s:3:"㍠";s:4:"8点";s:3:"㍡";s:4:"9点";s:3:"㍢";s:5:"10点";s:3:"㍣";s:5:"11点";s:3:"㍤";s:5:"12点";s:3:"㍥";s:5:"13点";s:3:"㍦";s:5:"14点";s:3:"㍧";s:5:"15点";s:3:"㍨";s:5:"16点";s:3:"㍩";s:5:"17点";s:3:"㍪";s:5:"18点";s:3:"㍫";s:5:"19点";s:3:"㍬";s:5:"20点";s:3:"㍭";s:5:"21点";s:3:"㍮";s:5:"22点";s:3:"㍯";s:5:"23点";s:3:"㍰";s:5:"24点";s:3:"㍱";s:3:"hPa";s:3:"㍲";s:2:"da";s:3:"㍳";s:2:"AU";s:3:"㍴";s:3:"bar";s:3:"㍵";s:2:"oV";s:3:"㍶";s:2:"pc";s:3:"㍷";s:2:"dm";s:3:"㍸";s:3:"dm2";s:3:"㍹";s:3:"dm3";s:3:"㍺";s:2:"IU";s:3:"㍻";s:6:"平成";s:3:"㍼";s:6:"昭和";s:3:"㍽";s:6:"大正";s:3:"㍾";s:6:"明治";s:3:"㍿";s:12:"株式会社";s:3:"㎀";s:2:"pA";s:3:"㎁";s:2:"nA";s:3:"㎂";s:3:"μA";s:3:"㎃";s:2:"mA";s:3:"㎄";s:2:"kA";s:3:"㎅";s:2:"KB";s:3:"㎆";s:2:"MB";s:3:"㎇";s:2:"GB";s:3:"㎈";s:3:"cal";s:3:"㎉";s:4:"kcal";s:3:"㎊";s:2:"pF";s:3:"㎋";s:2:"nF";s:3:"㎌";s:3:"μF";s:3:"㎍";s:3:"μg";s:3:"㎎";s:2:"mg";s:3:"㎏";s:2:"kg";s:3:"㎐";s:2:"Hz";s:3:"㎑";s:3:"kHz";s:3:"㎒";s:3:"MHz";s:3:"㎓";s:3:"GHz";s:3:"㎔";s:3:"THz";s:3:"㎕";s:3:"μl";s:3:"㎖";s:2:"ml";s:3:"㎗";s:2:"dl";s:3:"㎘";s:2:"kl";s:3:"㎙";s:2:"fm";s:3:"㎚";s:2:"nm";s:3:"㎛";s:3:"μm";s:3:"㎜";s:2:"mm";s:3:"㎝";s:2:"cm";s:3:"㎞";s:2:"km";s:3:"㎟";s:3:"mm2";s:3:"㎠";s:3:"cm2";s:3:"㎡";s:2:"m2";s:3:"㎢";s:3:"km2";s:3:"㎣";s:3:"mm3";s:3:"㎤";s:3:"cm3";s:3:"㎥";s:2:"m3";s:3:"㎦";s:3:"km3";s:3:"㎧";s:5:"m∕s";s:3:"㎨";s:6:"m∕s2";s:3:"㎩";s:2:"Pa";s:3:"㎪";s:3:"kPa";s:3:"㎫";s:3:"MPa";s:3:"㎬";s:3:"GPa";s:3:"㎭";s:3:"rad";s:3:"㎮";s:7:"rad∕s";s:3:"㎯";s:8:"rad∕s2";s:3:"㎰";s:2:"ps";s:3:"㎱";s:2:"ns";s:3:"㎲";s:3:"μs";s:3:"㎳";s:2:"ms";s:3:"㎴";s:2:"pV";s:3:"㎵";s:2:"nV";s:3:"㎶";s:3:"μV";s:3:"㎷";s:2:"mV";s:3:"㎸";s:2:"kV";s:3:"㎹";s:2:"MV";s:3:"㎺";s:2:"pW";s:3:"㎻";s:2:"nW";s:3:"㎼";s:3:"μW";s:3:"㎽";s:2:"mW";s:3:"㎾";s:2:"kW";s:3:"㎿";s:2:"MW";s:3:"㏀";s:3:"kΩ";s:3:"㏁";s:3:"MΩ";s:3:"㏂";s:4:"a.m.";s:3:"㏃";s:2:"Bq";s:3:"㏄";s:2:"cc";s:3:"㏅";s:2:"cd";s:3:"㏆";s:6:"C∕kg";s:3:"㏇";s:3:"Co.";s:3:"㏈";s:2:"dB";s:3:"㏉";s:2:"Gy";s:3:"㏊";s:2:"ha";s:3:"㏋";s:2:"HP";s:3:"㏌";s:2:"in";s:3:"㏍";s:2:"KK";s:3:"㏎";s:2:"KM";s:3:"㏏";s:2:"kt";s:3:"㏐";s:2:"lm";s:3:"㏑";s:2:"ln";s:3:"㏒";s:3:"log";s:3:"㏓";s:2:"lx";s:3:"㏔";s:2:"mb";s:3:"㏕";s:3:"mil";s:3:"㏖";s:3:"mol";s:3:"㏗";s:2:"PH";s:3:"㏘";s:4:"p.m.";s:3:"㏙";s:3:"PPM";s:3:"㏚";s:2:"PR";s:3:"㏛";s:2:"sr";s:3:"㏜";s:2:"Sv";s:3:"㏝";s:2:"Wb";s:3:"㏞";s:5:"V∕m";s:3:"㏟";s:5:"A∕m";s:3:"㏠";s:4:"1日";s:3:"㏡";s:4:"2日";s:3:"㏢";s:4:"3日";s:3:"㏣";s:4:"4日";s:3:"㏤";s:4:"5日";s:3:"㏥";s:4:"6日";s:3:"㏦";s:4:"7日";s:3:"㏧";s:4:"8日";s:3:"㏨";s:4:"9日";s:3:"㏩";s:5:"10日";s:3:"㏪";s:5:"11日";s:3:"㏫";s:5:"12日";s:3:"㏬";s:5:"13日";s:3:"㏭";s:5:"14日";s:3:"㏮";s:5:"15日";s:3:"㏯";s:5:"16日";s:3:"㏰";s:5:"17日";s:3:"㏱";s:5:"18日";s:3:"㏲";s:5:"19日";s:3:"㏳";s:5:"20日";s:3:"㏴";s:5:"21日";s:3:"㏵";s:5:"22日";s:3:"㏶";s:5:"23日";s:3:"㏷";s:5:"24日";s:3:"㏸";s:5:"25日";s:3:"㏹";s:5:"26日";s:3:"㏺";s:5:"27日";s:3:"㏻";s:5:"28日";s:3:"㏼";s:5:"29日";s:3:"㏽";s:5:"30日";s:3:"㏾";s:5:"31日";s:3:"㏿";s:3:"gal";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:3:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:3:"廊";s:3:"朗";s:3:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:3:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:3:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"樂";s:3:"樂";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:3:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:3:"異";s:3:"北";s:3:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:3:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:3:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"說";s:3:"說";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"寧";s:3:"寧";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"樂";s:3:"樂";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:3:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"率";s:3:"率";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:3:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:3:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:3:"侮";s:3:"僧";s:3:"僧";s:3:"免";s:3:"免";s:3:"勉";s:3:"勉";s:3:"勤";s:3:"勤";s:3:"卑";s:3:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:3:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:3:"屮";s:3:"悔";s:3:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:3:"憎";s:3:"懲";s:3:"懲";s:3:"敏";s:3:"敏";s:3:"既";s:3:"既";s:3:"暑";s:3:"暑";s:3:"梅";s:3:"梅";s:3:"海";s:3:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:3:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:3:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"練";s:3:"練";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:3:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"著";s:3:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"逸";s:3:"逸";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:3:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:3:"勇";s:3:"勺";s:3:"勺";s:3:"喝";s:3:"喝";s:3:"啕";s:3:"啕";s:3:"喙";s:3:"喙";s:3:"嗢";s:3:"嗢";s:3:"塚";s:3:"塚";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:3:"慎";s:3:"愈";s:3:"愈";s:3:"憎";s:3:"憎";s:3:"慠";s:3:"慠";s:3:"懲";s:3:"懲";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"晴";s:3:"晴";s:3:"朗";s:3:"朗";s:3:"望";s:3:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"殺";s:3:"殺";s:3:"流";s:3:"流";s:3:"滛";s:3:"滛";s:3:"滋";s:3:"滋";s:3:"漢";s:3:"漢";s:3:"瀞";s:3:"瀞";s:3:"煮";s:3:"煮";s:3:"瞧";s:3:"瞧";s:3:"爵";s:3:"爵";s:3:"犯";s:3:"犯";s:3:"猪";s:3:"猪";s:3:"瑱";s:3:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"益";s:3:"益";s:3:"盛";s:3:"盛";s:3:"直";s:3:"直";s:3:"睊";s:3:"睊";s:3:"着";s:3:"着";s:3:"磌";s:3:"磌";s:3:"窱";s:3:"窱";s:3:"節";s:3:"節";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"練";s:3:"練";s:3:"缾";s:3:"缾";s:3:"者";s:3:"者";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:3:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"視";s:3:"視";s:3:"調";s:3:"調";s:3:"諸";s:3:"諸";s:3:"請";s:3:"請";s:3:"謁";s:3:"謁";s:3:"諾";s:3:"諾";s:3:"諭";s:3:"諭";s:3:"謹";s:3:"謹";s:3:"變";s:3:"變";s:3:"贈";s:3:"贈";s:3:"輸";s:3:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"難";s:3:"難";s:3:"靖";s:3:"靖";s:3:"韛";s:3:"韛";s:3:"響";s:3:"響";s:3:"頋";s:3:"頋";s:3:"頻";s:3:"頻";s:3:"鬒";s:3:"鬒";s:3:"龜";s:3:"龜";s:3:"𢡊";s:4:"𢡊";s:3:"𢡄";s:4:"𢡄";s:3:"𣏕";s:4:"𣏕";s:3:"㮝";s:3:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:3:"䀹";s:3:"𥉉";s:4:"𥉉";s:3:"𥳐";s:4:"𥳐";s:3:"𧻓";s:4:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"ﬀ";s:2:"ff";s:3:"ﬁ";s:2:"fi";s:3:"ﬂ";s:2:"fl";s:3:"ﬃ";s:3:"ffi";s:3:"ﬄ";s:3:"ffl";s:3:"ﬅ";s:2:"st";s:3:"ﬆ";s:2:"st";s:3:"ﬓ";s:4:"մն";s:3:"ﬔ";s:4:"մե";s:3:"ﬕ";s:4:"մի";s:3:"ﬖ";s:4:"վն";s:3:"ﬗ";s:4:"մխ";s:3:"יִ";s:4:"יִ";s:3:"ײַ";s:4:"ײַ";s:3:"ﬠ";s:2:"ע";s:3:"ﬡ";s:2:"א";s:3:"ﬢ";s:2:"ד";s:3:"ﬣ";s:2:"ה";s:3:"ﬤ";s:2:"כ";s:3:"ﬥ";s:2:"ל";s:3:"ﬦ";s:2:"ם";s:3:"ﬧ";s:2:"ר";s:3:"ﬨ";s:2:"ת";s:3:"﬩";s:1:"+";s:3:"שׁ";s:4:"שׁ";s:3:"שׂ";s:4:"שׂ";s:3:"שּׁ";s:6:"שּׁ";s:3:"שּׂ";s:6:"שּׂ";s:3:"אַ";s:4:"אַ";s:3:"אָ";s:4:"אָ";s:3:"אּ";s:4:"אּ";s:3:"בּ";s:4:"בּ";s:3:"גּ";s:4:"גּ";s:3:"דּ";s:4:"דּ";s:3:"הּ";s:4:"הּ";s:3:"וּ";s:4:"וּ";s:3:"זּ";s:4:"זּ";s:3:"טּ";s:4:"טּ";s:3:"יּ";s:4:"יּ";s:3:"ךּ";s:4:"ךּ";s:3:"כּ";s:4:"כּ";s:3:"לּ";s:4:"לּ";s:3:"מּ";s:4:"מּ";s:3:"נּ";s:4:"נּ";s:3:"סּ";s:4:"סּ";s:3:"ףּ";s:4:"ףּ";s:3:"פּ";s:4:"פּ";s:3:"צּ";s:4:"צּ";s:3:"קּ";s:4:"קּ";s:3:"רּ";s:4:"רּ";s:3:"שּ";s:4:"שּ";s:3:"תּ";s:4:"תּ";s:3:"וֹ";s:4:"וֹ";s:3:"בֿ";s:4:"בֿ";s:3:"כֿ";s:4:"כֿ";s:3:"פֿ";s:4:"פֿ";s:3:"ﭏ";s:4:"אל";s:3:"ﭐ";s:2:"ٱ";s:3:"ﭑ";s:2:"ٱ";s:3:"ﭒ";s:2:"ٻ";s:3:"ﭓ";s:2:"ٻ";s:3:"ﭔ";s:2:"ٻ";s:3:"ﭕ";s:2:"ٻ";s:3:"ﭖ";s:2:"پ";s:3:"ﭗ";s:2:"پ";s:3:"ﭘ";s:2:"پ";s:3:"ﭙ";s:2:"پ";s:3:"ﭚ";s:2:"ڀ";s:3:"ﭛ";s:2:"ڀ";s:3:"ﭜ";s:2:"ڀ";s:3:"ﭝ";s:2:"ڀ";s:3:"ﭞ";s:2:"ٺ";s:3:"ﭟ";s:2:"ٺ";s:3:"ﭠ";s:2:"ٺ";s:3:"ﭡ";s:2:"ٺ";s:3:"ﭢ";s:2:"ٿ";s:3:"ﭣ";s:2:"ٿ";s:3:"ﭤ";s:2:"ٿ";s:3:"ﭥ";s:2:"ٿ";s:3:"ﭦ";s:2:"ٹ";s:3:"ﭧ";s:2:"ٹ";s:3:"ﭨ";s:2:"ٹ";s:3:"ﭩ";s:2:"ٹ";s:3:"ﭪ";s:2:"ڤ";s:3:"ﭫ";s:2:"ڤ";s:3:"ﭬ";s:2:"ڤ";s:3:"ﭭ";s:2:"ڤ";s:3:"ﭮ";s:2:"ڦ";s:3:"ﭯ";s:2:"ڦ";s:3:"ﭰ";s:2:"ڦ";s:3:"ﭱ";s:2:"ڦ";s:3:"ﭲ";s:2:"ڄ";s:3:"ﭳ";s:2:"ڄ";s:3:"ﭴ";s:2:"ڄ";s:3:"ﭵ";s:2:"ڄ";s:3:"ﭶ";s:2:"ڃ";s:3:"ﭷ";s:2:"ڃ";s:3:"ﭸ";s:2:"ڃ";s:3:"ﭹ";s:2:"ڃ";s:3:"ﭺ";s:2:"چ";s:3:"ﭻ";s:2:"چ";s:3:"ﭼ";s:2:"چ";s:3:"ﭽ";s:2:"چ";s:3:"ﭾ";s:2:"ڇ";s:3:"ﭿ";s:2:"ڇ";s:3:"ﮀ";s:2:"ڇ";s:3:"ﮁ";s:2:"ڇ";s:3:"ﮂ";s:2:"ڍ";s:3:"ﮃ";s:2:"ڍ";s:3:"ﮄ";s:2:"ڌ";s:3:"ﮅ";s:2:"ڌ";s:3:"ﮆ";s:2:"ڎ";s:3:"ﮇ";s:2:"ڎ";s:3:"ﮈ";s:2:"ڈ";s:3:"ﮉ";s:2:"ڈ";s:3:"ﮊ";s:2:"ژ";s:3:"ﮋ";s:2:"ژ";s:3:"ﮌ";s:2:"ڑ";s:3:"ﮍ";s:2:"ڑ";s:3:"ﮎ";s:2:"ک";s:3:"ﮏ";s:2:"ک";s:3:"ﮐ";s:2:"ک";s:3:"ﮑ";s:2:"ک";s:3:"ﮒ";s:2:"گ";s:3:"ﮓ";s:2:"گ";s:3:"ﮔ";s:2:"گ";s:3:"ﮕ";s:2:"گ";s:3:"ﮖ";s:2:"ڳ";s:3:"ﮗ";s:2:"ڳ";s:3:"ﮘ";s:2:"ڳ";s:3:"ﮙ";s:2:"ڳ";s:3:"ﮚ";s:2:"ڱ";s:3:"ﮛ";s:2:"ڱ";s:3:"ﮜ";s:2:"ڱ";s:3:"ﮝ";s:2:"ڱ";s:3:"ﮞ";s:2:"ں";s:3:"ﮟ";s:2:"ں";s:3:"ﮠ";s:2:"ڻ";s:3:"ﮡ";s:2:"ڻ";s:3:"ﮢ";s:2:"ڻ";s:3:"ﮣ";s:2:"ڻ";s:3:"ﮤ";s:4:"ۀ";s:3:"ﮥ";s:4:"ۀ";s:3:"ﮦ";s:2:"ہ";s:3:"ﮧ";s:2:"ہ";s:3:"ﮨ";s:2:"ہ";s:3:"ﮩ";s:2:"ہ";s:3:"ﮪ";s:2:"ھ";s:3:"ﮫ";s:2:"ھ";s:3:"ﮬ";s:2:"ھ";s:3:"ﮭ";s:2:"ھ";s:3:"ﮮ";s:2:"ے";s:3:"ﮯ";s:2:"ے";s:3:"ﮰ";s:4:"ۓ";s:3:"ﮱ";s:4:"ۓ";s:3:"ﯓ";s:2:"ڭ";s:3:"ﯔ";s:2:"ڭ";s:3:"ﯕ";s:2:"ڭ";s:3:"ﯖ";s:2:"ڭ";s:3:"ﯗ";s:2:"ۇ";s:3:"ﯘ";s:2:"ۇ";s:3:"ﯙ";s:2:"ۆ";s:3:"ﯚ";s:2:"ۆ";s:3:"ﯛ";s:2:"ۈ";s:3:"ﯜ";s:2:"ۈ";s:3:"ﯝ";s:4:"ۇٴ";s:3:"ﯞ";s:2:"ۋ";s:3:"ﯟ";s:2:"ۋ";s:3:"ﯠ";s:2:"ۅ";s:3:"ﯡ";s:2:"ۅ";s:3:"ﯢ";s:2:"ۉ";s:3:"ﯣ";s:2:"ۉ";s:3:"ﯤ";s:2:"ې";s:3:"ﯥ";s:2:"ې";s:3:"ﯦ";s:2:"ې";s:3:"ﯧ";s:2:"ې";s:3:"ﯨ";s:2:"ى";s:3:"ﯩ";s:2:"ى";s:3:"ﯪ";s:6:"ئا";s:3:"ﯫ";s:6:"ئا";s:3:"ﯬ";s:6:"ئە";s:3:"ﯭ";s:6:"ئە";s:3:"ﯮ";s:6:"ئو";s:3:"ﯯ";s:6:"ئو";s:3:"ﯰ";s:6:"ئۇ";s:3:"ﯱ";s:6:"ئۇ";s:3:"ﯲ";s:6:"ئۆ";s:3:"ﯳ";s:6:"ئۆ";s:3:"ﯴ";s:6:"ئۈ";s:3:"ﯵ";s:6:"ئۈ";s:3:"ﯶ";s:6:"ئې";s:3:"ﯷ";s:6:"ئې";s:3:"ﯸ";s:6:"ئې";s:3:"ﯹ";s:6:"ئى";s:3:"ﯺ";s:6:"ئى";s:3:"ﯻ";s:6:"ئى";s:3:"ﯼ";s:2:"ی";s:3:"ﯽ";s:2:"ی";s:3:"ﯾ";s:2:"ی";s:3:"ﯿ";s:2:"ی";s:3:"ﰀ";s:6:"ئج";s:3:"ﰁ";s:6:"ئح";s:3:"ﰂ";s:6:"ئم";s:3:"ﰃ";s:6:"ئى";s:3:"ﰄ";s:6:"ئي";s:3:"ﰅ";s:4:"بج";s:3:"ﰆ";s:4:"بح";s:3:"ﰇ";s:4:"بخ";s:3:"ﰈ";s:4:"بم";s:3:"ﰉ";s:4:"بى";s:3:"ﰊ";s:4:"بي";s:3:"ﰋ";s:4:"تج";s:3:"ﰌ";s:4:"تح";s:3:"ﰍ";s:4:"تخ";s:3:"ﰎ";s:4:"تم";s:3:"ﰏ";s:4:"تى";s:3:"ﰐ";s:4:"تي";s:3:"ﰑ";s:4:"ثج";s:3:"ﰒ";s:4:"ثم";s:3:"ﰓ";s:4:"ثى";s:3:"ﰔ";s:4:"ثي";s:3:"ﰕ";s:4:"جح";s:3:"ﰖ";s:4:"جم";s:3:"ﰗ";s:4:"حج";s:3:"ﰘ";s:4:"حم";s:3:"ﰙ";s:4:"خج";s:3:"ﰚ";s:4:"خح";s:3:"ﰛ";s:4:"خم";s:3:"ﰜ";s:4:"سج";s:3:"ﰝ";s:4:"سح";s:3:"ﰞ";s:4:"سخ";s:3:"ﰟ";s:4:"سم";s:3:"ﰠ";s:4:"صح";s:3:"ﰡ";s:4:"صم";s:3:"ﰢ";s:4:"ضج";s:3:"ﰣ";s:4:"ضح";s:3:"ﰤ";s:4:"ضخ";s:3:"ﰥ";s:4:"ضم";s:3:"ﰦ";s:4:"طح";s:3:"ﰧ";s:4:"طم";s:3:"ﰨ";s:4:"ظم";s:3:"ﰩ";s:4:"عج";s:3:"ﰪ";s:4:"عم";s:3:"ﰫ";s:4:"غج";s:3:"ﰬ";s:4:"غم";s:3:"ﰭ";s:4:"فج";s:3:"ﰮ";s:4:"فح";s:3:"ﰯ";s:4:"فخ";s:3:"ﰰ";s:4:"فم";s:3:"ﰱ";s:4:"فى";s:3:"ﰲ";s:4:"في";s:3:"ﰳ";s:4:"قح";s:3:"ﰴ";s:4:"قم";s:3:"ﰵ";s:4:"قى";s:3:"ﰶ";s:4:"قي";s:3:"ﰷ";s:4:"كا";s:3:"ﰸ";s:4:"كج";s:3:"ﰹ";s:4:"كح";s:3:"ﰺ";s:4:"كخ";s:3:"ﰻ";s:4:"كل";s:3:"ﰼ";s:4:"كم";s:3:"ﰽ";s:4:"كى";s:3:"ﰾ";s:4:"كي";s:3:"ﰿ";s:4:"لج";s:3:"ﱀ";s:4:"لح";s:3:"ﱁ";s:4:"لخ";s:3:"ﱂ";s:4:"لم";s:3:"ﱃ";s:4:"لى";s:3:"ﱄ";s:4:"لي";s:3:"ﱅ";s:4:"مج";s:3:"ﱆ";s:4:"مح";s:3:"ﱇ";s:4:"مخ";s:3:"ﱈ";s:4:"مم";s:3:"ﱉ";s:4:"مى";s:3:"ﱊ";s:4:"مي";s:3:"ﱋ";s:4:"نج";s:3:"ﱌ";s:4:"نح";s:3:"ﱍ";s:4:"نخ";s:3:"ﱎ";s:4:"نم";s:3:"ﱏ";s:4:"نى";s:3:"ﱐ";s:4:"ني";s:3:"ﱑ";s:4:"هج";s:3:"ﱒ";s:4:"هم";s:3:"ﱓ";s:4:"هى";s:3:"ﱔ";s:4:"هي";s:3:"ﱕ";s:4:"يج";s:3:"ﱖ";s:4:"يح";s:3:"ﱗ";s:4:"يخ";s:3:"ﱘ";s:4:"يم";s:3:"ﱙ";s:4:"يى";s:3:"ﱚ";s:4:"يي";s:3:"ﱛ";s:4:"ذٰ";s:3:"ﱜ";s:4:"رٰ";s:3:"ﱝ";s:4:"ىٰ";s:3:"ﱞ";s:5:" ٌّ";s:3:"ﱟ";s:5:" ٍّ";s:3:"ﱠ";s:5:" َّ";s:3:"ﱡ";s:5:" ُّ";s:3:"ﱢ";s:5:" ِّ";s:3:"ﱣ";s:5:" ّٰ";s:3:"ﱤ";s:6:"ئر";s:3:"ﱥ";s:6:"ئز";s:3:"ﱦ";s:6:"ئم";s:3:"ﱧ";s:6:"ئن";s:3:"ﱨ";s:6:"ئى";s:3:"ﱩ";s:6:"ئي";s:3:"ﱪ";s:4:"بر";s:3:"ﱫ";s:4:"بز";s:3:"ﱬ";s:4:"بم";s:3:"ﱭ";s:4:"بن";s:3:"ﱮ";s:4:"بى";s:3:"ﱯ";s:4:"بي";s:3:"ﱰ";s:4:"تر";s:3:"ﱱ";s:4:"تز";s:3:"ﱲ";s:4:"تم";s:3:"ﱳ";s:4:"تن";s:3:"ﱴ";s:4:"تى";s:3:"ﱵ";s:4:"تي";s:3:"ﱶ";s:4:"ثر";s:3:"ﱷ";s:4:"ثز";s:3:"ﱸ";s:4:"ثم";s:3:"ﱹ";s:4:"ثن";s:3:"ﱺ";s:4:"ثى";s:3:"ﱻ";s:4:"ثي";s:3:"ﱼ";s:4:"فى";s:3:"ﱽ";s:4:"في";s:3:"ﱾ";s:4:"قى";s:3:"ﱿ";s:4:"قي";s:3:"ﲀ";s:4:"كا";s:3:"ﲁ";s:4:"كل";s:3:"ﲂ";s:4:"كم";s:3:"ﲃ";s:4:"كى";s:3:"ﲄ";s:4:"كي";s:3:"ﲅ";s:4:"لم";s:3:"ﲆ";s:4:"لى";s:3:"ﲇ";s:4:"لي";s:3:"ﲈ";s:4:"ما";s:3:"ﲉ";s:4:"مم";s:3:"ﲊ";s:4:"نر";s:3:"ﲋ";s:4:"نز";s:3:"ﲌ";s:4:"نم";s:3:"ﲍ";s:4:"نن";s:3:"ﲎ";s:4:"نى";s:3:"ﲏ";s:4:"ني";s:3:"ﲐ";s:4:"ىٰ";s:3:"ﲑ";s:4:"ير";s:3:"ﲒ";s:4:"يز";s:3:"ﲓ";s:4:"يم";s:3:"ﲔ";s:4:"ين";s:3:"ﲕ";s:4:"يى";s:3:"ﲖ";s:4:"يي";s:3:"ﲗ";s:6:"ئج";s:3:"ﲘ";s:6:"ئح";s:3:"ﲙ";s:6:"ئخ";s:3:"ﲚ";s:6:"ئم";s:3:"ﲛ";s:6:"ئه";s:3:"ﲜ";s:4:"بج";s:3:"ﲝ";s:4:"بح";s:3:"ﲞ";s:4:"بخ";s:3:"ﲟ";s:4:"بم";s:3:"ﲠ";s:4:"به";s:3:"ﲡ";s:4:"تج";s:3:"ﲢ";s:4:"تح";s:3:"ﲣ";s:4:"تخ";s:3:"ﲤ";s:4:"تم";s:3:"ﲥ";s:4:"ته";s:3:"ﲦ";s:4:"ثم";s:3:"ﲧ";s:4:"جح";s:3:"ﲨ";s:4:"جم";s:3:"ﲩ";s:4:"حج";s:3:"ﲪ";s:4:"حم";s:3:"ﲫ";s:4:"خج";s:3:"ﲬ";s:4:"خم";s:3:"ﲭ";s:4:"سج";s:3:"ﲮ";s:4:"سح";s:3:"ﲯ";s:4:"سخ";s:3:"ﲰ";s:4:"سم";s:3:"ﲱ";s:4:"صح";s:3:"ﲲ";s:4:"صخ";s:3:"ﲳ";s:4:"صم";s:3:"ﲴ";s:4:"ضج";s:3:"ﲵ";s:4:"ضح";s:3:"ﲶ";s:4:"ضخ";s:3:"ﲷ";s:4:"ضم";s:3:"ﲸ";s:4:"طح";s:3:"ﲹ";s:4:"ظم";s:3:"ﲺ";s:4:"عج";s:3:"ﲻ";s:4:"عم";s:3:"ﲼ";s:4:"غج";s:3:"ﲽ";s:4:"غم";s:3:"ﲾ";s:4:"فج";s:3:"ﲿ";s:4:"فح";s:3:"ﳀ";s:4:"فخ";s:3:"ﳁ";s:4:"فم";s:3:"ﳂ";s:4:"قح";s:3:"ﳃ";s:4:"قم";s:3:"ﳄ";s:4:"كج";s:3:"ﳅ";s:4:"كح";s:3:"ﳆ";s:4:"كخ";s:3:"ﳇ";s:4:"كل";s:3:"ﳈ";s:4:"كم";s:3:"ﳉ";s:4:"لج";s:3:"ﳊ";s:4:"لح";s:3:"ﳋ";s:4:"لخ";s:3:"ﳌ";s:4:"لم";s:3:"ﳍ";s:4:"له";s:3:"ﳎ";s:4:"مج";s:3:"ﳏ";s:4:"مح";s:3:"ﳐ";s:4:"مخ";s:3:"ﳑ";s:4:"مم";s:3:"ﳒ";s:4:"نج";s:3:"ﳓ";s:4:"نح";s:3:"ﳔ";s:4:"نخ";s:3:"ﳕ";s:4:"نم";s:3:"ﳖ";s:4:"نه";s:3:"ﳗ";s:4:"هج";s:3:"ﳘ";s:4:"هم";s:3:"ﳙ";s:4:"هٰ";s:3:"ﳚ";s:4:"يج";s:3:"ﳛ";s:4:"يح";s:3:"ﳜ";s:4:"يخ";s:3:"ﳝ";s:4:"يم";s:3:"ﳞ";s:4:"يه";s:3:"ﳟ";s:6:"ئم";s:3:"ﳠ";s:6:"ئه";s:3:"ﳡ";s:4:"بم";s:3:"ﳢ";s:4:"به";s:3:"ﳣ";s:4:"تم";s:3:"ﳤ";s:4:"ته";s:3:"ﳥ";s:4:"ثم";s:3:"ﳦ";s:4:"ثه";s:3:"ﳧ";s:4:"سم";s:3:"ﳨ";s:4:"سه";s:3:"ﳩ";s:4:"شم";s:3:"ﳪ";s:4:"شه";s:3:"ﳫ";s:4:"كل";s:3:"ﳬ";s:4:"كم";s:3:"ﳭ";s:4:"لم";s:3:"ﳮ";s:4:"نم";s:3:"ﳯ";s:4:"نه";s:3:"ﳰ";s:4:"يم";s:3:"ﳱ";s:4:"يه";s:3:"ﳲ";s:6:"ـَّ";s:3:"ﳳ";s:6:"ـُّ";s:3:"ﳴ";s:6:"ـِّ";s:3:"ﳵ";s:4:"طى";s:3:"ﳶ";s:4:"طي";s:3:"ﳷ";s:4:"عى";s:3:"ﳸ";s:4:"عي";s:3:"ﳹ";s:4:"غى";s:3:"ﳺ";s:4:"غي";s:3:"ﳻ";s:4:"سى";s:3:"ﳼ";s:4:"سي";s:3:"ﳽ";s:4:"شى";s:3:"ﳾ";s:4:"شي";s:3:"ﳿ";s:4:"حى";s:3:"ﴀ";s:4:"حي";s:3:"ﴁ";s:4:"جى";s:3:"ﴂ";s:4:"جي";s:3:"ﴃ";s:4:"خى";s:3:"ﴄ";s:4:"خي";s:3:"ﴅ";s:4:"صى";s:3:"ﴆ";s:4:"صي";s:3:"ﴇ";s:4:"ضى";s:3:"ﴈ";s:4:"ضي";s:3:"ﴉ";s:4:"شج";s:3:"ﴊ";s:4:"شح";s:3:"ﴋ";s:4:"شخ";s:3:"ﴌ";s:4:"شم";s:3:"ﴍ";s:4:"شر";s:3:"ﴎ";s:4:"سر";s:3:"ﴏ";s:4:"صر";s:3:"ﴐ";s:4:"ضر";s:3:"ﴑ";s:4:"طى";s:3:"ﴒ";s:4:"طي";s:3:"ﴓ";s:4:"عى";s:3:"ﴔ";s:4:"عي";s:3:"ﴕ";s:4:"غى";s:3:"ﴖ";s:4:"غي";s:3:"ﴗ";s:4:"سى";s:3:"ﴘ";s:4:"سي";s:3:"ﴙ";s:4:"شى";s:3:"ﴚ";s:4:"شي";s:3:"ﴛ";s:4:"حى";s:3:"ﴜ";s:4:"حي";s:3:"ﴝ";s:4:"جى";s:3:"ﴞ";s:4:"جي";s:3:"ﴟ";s:4:"خى";s:3:"ﴠ";s:4:"خي";s:3:"ﴡ";s:4:"صى";s:3:"ﴢ";s:4:"صي";s:3:"ﴣ";s:4:"ضى";s:3:"ﴤ";s:4:"ضي";s:3:"ﴥ";s:4:"شج";s:3:"ﴦ";s:4:"شح";s:3:"ﴧ";s:4:"شخ";s:3:"ﴨ";s:4:"شم";s:3:"ﴩ";s:4:"شر";s:3:"ﴪ";s:4:"سر";s:3:"ﴫ";s:4:"صر";s:3:"ﴬ";s:4:"ضر";s:3:"ﴭ";s:4:"شج";s:3:"ﴮ";s:4:"شح";s:3:"ﴯ";s:4:"شخ";s:3:"ﴰ";s:4:"شم";s:3:"ﴱ";s:4:"سه";s:3:"ﴲ";s:4:"شه";s:3:"ﴳ";s:4:"طم";s:3:"ﴴ";s:4:"سج";s:3:"ﴵ";s:4:"سح";s:3:"ﴶ";s:4:"سخ";s:3:"ﴷ";s:4:"شج";s:3:"ﴸ";s:4:"شح";s:3:"ﴹ";s:4:"شخ";s:3:"ﴺ";s:4:"طم";s:3:"ﴻ";s:4:"ظم";s:3:"ﴼ";s:4:"اً";s:3:"ﴽ";s:4:"اً";s:3:"ﵐ";s:6:"تجم";s:3:"ﵑ";s:6:"تحج";s:3:"ﵒ";s:6:"تحج";s:3:"ﵓ";s:6:"تحم";s:3:"ﵔ";s:6:"تخم";s:3:"ﵕ";s:6:"تمج";s:3:"ﵖ";s:6:"تمح";s:3:"ﵗ";s:6:"تمخ";s:3:"ﵘ";s:6:"جمح";s:3:"ﵙ";s:6:"جمح";s:3:"ﵚ";s:6:"حمي";s:3:"ﵛ";s:6:"حمى";s:3:"ﵜ";s:6:"سحج";s:3:"ﵝ";s:6:"سجح";s:3:"ﵞ";s:6:"سجى";s:3:"ﵟ";s:6:"سمح";s:3:"ﵠ";s:6:"سمح";s:3:"ﵡ";s:6:"سمج";s:3:"ﵢ";s:6:"سمم";s:3:"ﵣ";s:6:"سمم";s:3:"ﵤ";s:6:"صحح";s:3:"ﵥ";s:6:"صحح";s:3:"ﵦ";s:6:"صمم";s:3:"ﵧ";s:6:"شحم";s:3:"ﵨ";s:6:"شحم";s:3:"ﵩ";s:6:"شجي";s:3:"ﵪ";s:6:"شمخ";s:3:"ﵫ";s:6:"شمخ";s:3:"ﵬ";s:6:"شمم";s:3:"ﵭ";s:6:"شمم";s:3:"ﵮ";s:6:"ضحى";s:3:"ﵯ";s:6:"ضخم";s:3:"ﵰ";s:6:"ضخم";s:3:"ﵱ";s:6:"طمح";s:3:"ﵲ";s:6:"طمح";s:3:"ﵳ";s:6:"طمم";s:3:"ﵴ";s:6:"طمي";s:3:"ﵵ";s:6:"عجم";s:3:"ﵶ";s:6:"عمم";s:3:"ﵷ";s:6:"عمم";s:3:"ﵸ";s:6:"عمى";s:3:"ﵹ";s:6:"غمم";s:3:"ﵺ";s:6:"غمي";s:3:"ﵻ";s:6:"غمى";s:3:"ﵼ";s:6:"فخم";s:3:"ﵽ";s:6:"فخم";s:3:"ﵾ";s:6:"قمح";s:3:"ﵿ";s:6:"قمم";s:3:"ﶀ";s:6:"لحم";s:3:"ﶁ";s:6:"لحي";s:3:"ﶂ";s:6:"لحى";s:3:"ﶃ";s:6:"لجج";s:3:"ﶄ";s:6:"لجج";s:3:"ﶅ";s:6:"لخم";s:3:"ﶆ";s:6:"لخم";s:3:"ﶇ";s:6:"لمح";s:3:"ﶈ";s:6:"لمح";s:3:"ﶉ";s:6:"محج";s:3:"ﶊ";s:6:"محم";s:3:"ﶋ";s:6:"محي";s:3:"ﶌ";s:6:"مجح";s:3:"ﶍ";s:6:"مجم";s:3:"ﶎ";s:6:"مخج";s:3:"ﶏ";s:6:"مخم";s:3:"ﶒ";s:6:"مجخ";s:3:"ﶓ";s:6:"همج";s:3:"ﶔ";s:6:"همم";s:3:"ﶕ";s:6:"نحم";s:3:"ﶖ";s:6:"نحى";s:3:"ﶗ";s:6:"نجم";s:3:"ﶘ";s:6:"نجم";s:3:"ﶙ";s:6:"نجى";s:3:"ﶚ";s:6:"نمي";s:3:"ﶛ";s:6:"نمى";s:3:"ﶜ";s:6:"يمم";s:3:"ﶝ";s:6:"يمم";s:3:"ﶞ";s:6:"بخي";s:3:"ﶟ";s:6:"تجي";s:3:"ﶠ";s:6:"تجى";s:3:"ﶡ";s:6:"تخي";s:3:"ﶢ";s:6:"تخى";s:3:"ﶣ";s:6:"تمي";s:3:"ﶤ";s:6:"تمى";s:3:"ﶥ";s:6:"جمي";s:3:"ﶦ";s:6:"جحى";s:3:"ﶧ";s:6:"جمى";s:3:"ﶨ";s:6:"سخى";s:3:"ﶩ";s:6:"صحي";s:3:"ﶪ";s:6:"شحي";s:3:"ﶫ";s:6:"ضحي";s:3:"ﶬ";s:6:"لجي";s:3:"ﶭ";s:6:"لمي";s:3:"ﶮ";s:6:"يحي";s:3:"ﶯ";s:6:"يجي";s:3:"ﶰ";s:6:"يمي";s:3:"ﶱ";s:6:"ممي";s:3:"ﶲ";s:6:"قمي";s:3:"ﶳ";s:6:"نحي";s:3:"ﶴ";s:6:"قمح";s:3:"ﶵ";s:6:"لحم";s:3:"ﶶ";s:6:"عمي";s:3:"ﶷ";s:6:"كمي";s:3:"ﶸ";s:6:"نجح";s:3:"ﶹ";s:6:"مخي";s:3:"ﶺ";s:6:"لجم";s:3:"ﶻ";s:6:"كمم";s:3:"ﶼ";s:6:"لجم";s:3:"ﶽ";s:6:"نجح";s:3:"ﶾ";s:6:"جحي";s:3:"ﶿ";s:6:"حجي";s:3:"ﷀ";s:6:"مجي";s:3:"ﷁ";s:6:"فمي";s:3:"ﷂ";s:6:"بحي";s:3:"ﷃ";s:6:"كمم";s:3:"ﷄ";s:6:"عجم";s:3:"ﷅ";s:6:"صمم";s:3:"ﷆ";s:6:"سخي";s:3:"ﷇ";s:6:"نجي";s:3:"ﷰ";s:6:"صلے";s:3:"ﷱ";s:6:"قلے";s:3:"ﷲ";s:8:"الله";s:3:"ﷳ";s:8:"اكبر";s:3:"ﷴ";s:8:"محمد";s:3:"ﷵ";s:8:"صلعم";s:3:"ﷶ";s:8:"رسول";s:3:"ﷷ";s:8:"عليه";s:3:"ﷸ";s:8:"وسلم";s:3:"ﷹ";s:6:"صلى";s:3:"ﷺ";s:33:"صلى الله عليه وسلم";s:3:"ﷻ";s:15:"جل جلاله";s:3:"﷼";s:8:"ریال";s:3:"︐";s:1:",";s:3:"︑";s:3:"、";s:3:"︒";s:3:"。";s:3:"︓";s:1:":";s:3:"︔";s:1:";";s:3:"︕";s:1:"!";s:3:"︖";s:1:"?";s:3:"︗";s:3:"〖";s:3:"︘";s:3:"〗";s:3:"︙";s:3:"...";s:3:"︰";s:2:"..";s:3:"︱";s:3:"—";s:3:"︲";s:3:"–";s:3:"︳";s:1:"_";s:3:"︴";s:1:"_";s:3:"︵";s:1:"(";s:3:"︶";s:1:")";s:3:"︷";s:1:"{";s:3:"︸";s:1:"}";s:3:"︹";s:3:"〔";s:3:"︺";s:3:"〕";s:3:"︻";s:3:"【";s:3:"︼";s:3:"】";s:3:"︽";s:3:"《";s:3:"︾";s:3:"》";s:3:"︿";s:3:"〈";s:3:"﹀";s:3:"〉";s:3:"﹁";s:3:"「";s:3:"﹂";s:3:"」";s:3:"﹃";s:3:"『";s:3:"﹄";s:3:"』";s:3:"﹇";s:1:"[";s:3:"﹈";s:1:"]";s:3:"﹉";s:3:" ̅";s:3:"﹊";s:3:" ̅";s:3:"﹋";s:3:" ̅";s:3:"﹌";s:3:" ̅";s:3:"﹍";s:1:"_";s:3:"﹎";s:1:"_";s:3:"﹏";s:1:"_";s:3:"﹐";s:1:",";s:3:"﹑";s:3:"、";s:3:"﹒";s:1:".";s:3:"﹔";s:1:";";s:3:"﹕";s:1:":";s:3:"﹖";s:1:"?";s:3:"﹗";s:1:"!";s:3:"﹘";s:3:"—";s:3:"﹙";s:1:"(";s:3:"﹚";s:1:")";s:3:"﹛";s:1:"{";s:3:"﹜";s:1:"}";s:3:"﹝";s:3:"〔";s:3:"﹞";s:3:"〕";s:3:"﹟";s:1:"#";s:3:"﹠";s:1:"&";s:3:"﹡";s:1:"*";s:3:"﹢";s:1:"+";s:3:"﹣";s:1:"-";s:3:"﹤";s:1:"<";s:3:"﹥";s:1:">";s:3:"﹦";s:1:"=";s:3:"﹨";s:1:"\\";s:3:"﹩";s:1:"$";s:3:"﹪";s:1:"%";s:3:"﹫";s:1:"@";s:3:"ﹰ";s:3:" ً";s:3:"ﹱ";s:4:"ـً";s:3:"ﹲ";s:3:" ٌ";s:3:"ﹴ";s:3:" ٍ";s:3:"ﹶ";s:3:" َ";s:3:"ﹷ";s:4:"ـَ";s:3:"ﹸ";s:3:" ُ";s:3:"ﹹ";s:4:"ـُ";s:3:"ﹺ";s:3:" ِ";s:3:"ﹻ";s:4:"ـِ";s:3:"ﹼ";s:3:" ّ";s:3:"ﹽ";s:4:"ـّ";s:3:"ﹾ";s:3:" ْ";s:3:"ﹿ";s:4:"ـْ";s:3:"ﺀ";s:2:"ء";s:3:"ﺁ";s:4:"آ";s:3:"ﺂ";s:4:"آ";s:3:"ﺃ";s:4:"أ";s:3:"ﺄ";s:4:"أ";s:3:"ﺅ";s:4:"ؤ";s:3:"ﺆ";s:4:"ؤ";s:3:"ﺇ";s:4:"إ";s:3:"ﺈ";s:4:"إ";s:3:"ﺉ";s:4:"ئ";s:3:"ﺊ";s:4:"ئ";s:3:"ﺋ";s:4:"ئ";s:3:"ﺌ";s:4:"ئ";s:3:"ﺍ";s:2:"ا";s:3:"ﺎ";s:2:"ا";s:3:"ﺏ";s:2:"ب";s:3:"ﺐ";s:2:"ب";s:3:"ﺑ";s:2:"ب";s:3:"ﺒ";s:2:"ب";s:3:"ﺓ";s:2:"ة";s:3:"ﺔ";s:2:"ة";s:3:"ﺕ";s:2:"ت";s:3:"ﺖ";s:2:"ت";s:3:"ﺗ";s:2:"ت";s:3:"ﺘ";s:2:"ت";s:3:"ﺙ";s:2:"ث";s:3:"ﺚ";s:2:"ث";s:3:"ﺛ";s:2:"ث";s:3:"ﺜ";s:2:"ث";s:3:"ﺝ";s:2:"ج";s:3:"ﺞ";s:2:"ج";s:3:"ﺟ";s:2:"ج";s:3:"ﺠ";s:2:"ج";s:3:"ﺡ";s:2:"ح";s:3:"ﺢ";s:2:"ح";s:3:"ﺣ";s:2:"ح";s:3:"ﺤ";s:2:"ح";s:3:"ﺥ";s:2:"خ";s:3:"ﺦ";s:2:"خ";s:3:"ﺧ";s:2:"خ";s:3:"ﺨ";s:2:"خ";s:3:"ﺩ";s:2:"د";s:3:"ﺪ";s:2:"د";s:3:"ﺫ";s:2:"ذ";s:3:"ﺬ";s:2:"ذ";s:3:"ﺭ";s:2:"ر";s:3:"ﺮ";s:2:"ر";s:3:"ﺯ";s:2:"ز";s:3:"ﺰ";s:2:"ز";s:3:"ﺱ";s:2:"س";s:3:"ﺲ";s:2:"س";s:3:"ﺳ";s:2:"س";s:3:"ﺴ";s:2:"س";s:3:"ﺵ";s:2:"ش";s:3:"ﺶ";s:2:"ش";s:3:"ﺷ";s:2:"ش";s:3:"ﺸ";s:2:"ش";s:3:"ﺹ";s:2:"ص";s:3:"ﺺ";s:2:"ص";s:3:"ﺻ";s:2:"ص";s:3:"ﺼ";s:2:"ص";s:3:"ﺽ";s:2:"ض";s:3:"ﺾ";s:2:"ض";s:3:"ﺿ";s:2:"ض";s:3:"ﻀ";s:2:"ض";s:3:"ﻁ";s:2:"ط";s:3:"ﻂ";s:2:"ط";s:3:"ﻃ";s:2:"ط";s:3:"ﻄ";s:2:"ط";s:3:"ﻅ";s:2:"ظ";s:3:"ﻆ";s:2:"ظ";s:3:"ﻇ";s:2:"ظ";s:3:"ﻈ";s:2:"ظ";s:3:"ﻉ";s:2:"ع";s:3:"ﻊ";s:2:"ع";s:3:"ﻋ";s:2:"ع";s:3:"ﻌ";s:2:"ع";s:3:"ﻍ";s:2:"غ";s:3:"ﻎ";s:2:"غ";s:3:"ﻏ";s:2:"غ";s:3:"ﻐ";s:2:"غ";s:3:"ﻑ";s:2:"ف";s:3:"ﻒ";s:2:"ف";s:3:"ﻓ";s:2:"ف";s:3:"ﻔ";s:2:"ف";s:3:"ﻕ";s:2:"ق";s:3:"ﻖ";s:2:"ق";s:3:"ﻗ";s:2:"ق";s:3:"ﻘ";s:2:"ق";s:3:"ﻙ";s:2:"ك";s:3:"ﻚ";s:2:"ك";s:3:"ﻛ";s:2:"ك";s:3:"ﻜ";s:2:"ك";s:3:"ﻝ";s:2:"ل";s:3:"ﻞ";s:2:"ل";s:3:"ﻟ";s:2:"ل";s:3:"ﻠ";s:2:"ل";s:3:"ﻡ";s:2:"م";s:3:"ﻢ";s:2:"م";s:3:"ﻣ";s:2:"م";s:3:"ﻤ";s:2:"م";s:3:"ﻥ";s:2:"ن";s:3:"ﻦ";s:2:"ن";s:3:"ﻧ";s:2:"ن";s:3:"ﻨ";s:2:"ن";s:3:"ﻩ";s:2:"ه";s:3:"ﻪ";s:2:"ه";s:3:"ﻫ";s:2:"ه";s:3:"ﻬ";s:2:"ه";s:3:"ﻭ";s:2:"و";s:3:"ﻮ";s:2:"و";s:3:"ﻯ";s:2:"ى";s:3:"ﻰ";s:2:"ى";s:3:"ﻱ";s:2:"ي";s:3:"ﻲ";s:2:"ي";s:3:"ﻳ";s:2:"ي";s:3:"ﻴ";s:2:"ي";s:3:"ﻵ";s:6:"لآ";s:3:"ﻶ";s:6:"لآ";s:3:"ﻷ";s:6:"لأ";s:3:"ﻸ";s:6:"لأ";s:3:"ﻹ";s:6:"لإ";s:3:"ﻺ";s:6:"لإ";s:3:"ﻻ";s:4:"لا";s:3:"ﻼ";s:4:"لا";s:3:"！";s:1:"!";s:3:"＂";s:1:""";s:3:"＃";s:1:"#";s:3:"＄";s:1:"$";s:3:"％";s:1:"%";s:3:"＆";s:1:"&";s:3:"＇";s:1:"\'";s:3:"（";s:1:"(";s:3:"）";s:1:")";s:3:"＊";s:1:"*";s:3:"＋";s:1:"+";s:3:"，";s:1:",";s:3:"－";s:1:"-";s:3:"．";s:1:".";s:3:"／";s:1:"/";s:3:"０";s:1:"0";s:3:"１";s:1:"1";s:3:"２";s:1:"2";s:3:"３";s:1:"3";s:3:"４";s:1:"4";s:3:"５";s:1:"5";s:3:"６";s:1:"6";s:3:"７";s:1:"7";s:3:"８";s:1:"8";s:3:"９";s:1:"9";s:3:"：";s:1:":";s:3:"；";s:1:";";s:3:"＜";s:1:"<";s:3:"＝";s:1:"=";s:3:"＞";s:1:">";s:3:"？";s:1:"?";s:3:"＠";s:1:"@";s:3:"Ａ";s:1:"A";s:3:"Ｂ";s:1:"B";s:3:"Ｃ";s:1:"C";s:3:"Ｄ";s:1:"D";s:3:"Ｅ";s:1:"E";s:3:"Ｆ";s:1:"F";s:3:"Ｇ";s:1:"G";s:3:"Ｈ";s:1:"H";s:3:"Ｉ";s:1:"I";s:3:"Ｊ";s:1:"J";s:3:"Ｋ";s:1:"K";s:3:"Ｌ";s:1:"L";s:3:"Ｍ";s:1:"M";s:3:"Ｎ";s:1:"N";s:3:"Ｏ";s:1:"O";s:3:"Ｐ";s:1:"P";s:3:"Ｑ";s:1:"Q";s:3:"Ｒ";s:1:"R";s:3:"Ｓ";s:1:"S";s:3:"Ｔ";s:1:"T";s:3:"Ｕ";s:1:"U";s:3:"Ｖ";s:1:"V";s:3:"Ｗ";s:1:"W";s:3:"Ｘ";s:1:"X";s:3:"Ｙ";s:1:"Y";s:3:"Ｚ";s:1:"Z";s:3:"［";s:1:"[";s:3:"＼";s:1:"\\";s:3:"］";s:1:"]";s:3:"＾";s:1:"^";s:3:"＿";s:1:"_";s:3:"｀";s:1:"`";s:3:"ａ";s:1:"a";s:3:"ｂ";s:1:"b";s:3:"ｃ";s:1:"c";s:3:"ｄ";s:1:"d";s:3:"ｅ";s:1:"e";s:3:"ｆ";s:1:"f";s:3:"ｇ";s:1:"g";s:3:"ｈ";s:1:"h";s:3:"ｉ";s:1:"i";s:3:"ｊ";s:1:"j";s:3:"ｋ";s:1:"k";s:3:"ｌ";s:1:"l";s:3:"ｍ";s:1:"m";s:3:"ｎ";s:1:"n";s:3:"ｏ";s:1:"o";s:3:"ｐ";s:1:"p";s:3:"ｑ";s:1:"q";s:3:"ｒ";s:1:"r";s:3:"ｓ";s:1:"s";s:3:"ｔ";s:1:"t";s:3:"ｕ";s:1:"u";s:3:"ｖ";s:1:"v";s:3:"ｗ";s:1:"w";s:3:"ｘ";s:1:"x";s:3:"ｙ";s:1:"y";s:3:"ｚ";s:1:"z";s:3:"｛";s:1:"{";s:3:"｜";s:1:"|";s:3:"｝";s:1:"}";s:3:"～";s:1:"~";s:3:"｟";s:3:"⦅";s:3:"｠";s:3:"⦆";s:3:"｡";s:3:"。";s:3:"｢";s:3:"「";s:3:"｣";s:3:"」";s:3:"､";s:3:"、";s:3:"･";s:3:"・";s:3:"ｦ";s:3:"ヲ";s:3:"ｧ";s:3:"ァ";s:3:"ｨ";s:3:"ィ";s:3:"ｩ";s:3:"ゥ";s:3:"ｪ";s:3:"ェ";s:3:"ｫ";s:3:"ォ";s:3:"ｬ";s:3:"ャ";s:3:"ｭ";s:3:"ュ";s:3:"ｮ";s:3:"ョ";s:3:"ｯ";s:3:"ッ";s:3:"ｰ";s:3:"ー";s:3:"ｱ";s:3:"ア";s:3:"ｲ";s:3:"イ";s:3:"ｳ";s:3:"ウ";s:3:"ｴ";s:3:"エ";s:3:"ｵ";s:3:"オ";s:3:"ｶ";s:3:"カ";s:3:"ｷ";s:3:"キ";s:3:"ｸ";s:3:"ク";s:3:"ｹ";s:3:"ケ";s:3:"ｺ";s:3:"コ";s:3:"ｻ";s:3:"サ";s:3:"ｼ";s:3:"シ";s:3:"ｽ";s:3:"ス";s:3:"ｾ";s:3:"セ";s:3:"ｿ";s:3:"ソ";s:3:"ﾀ";s:3:"タ";s:3:"ﾁ";s:3:"チ";s:3:"ﾂ";s:3:"ツ";s:3:"ﾃ";s:3:"テ";s:3:"ﾄ";s:3:"ト";s:3:"ﾅ";s:3:"ナ";s:3:"ﾆ";s:3:"ニ";s:3:"ﾇ";s:3:"ヌ";s:3:"ﾈ";s:3:"ネ";s:3:"ﾉ";s:3:"ノ";s:3:"ﾊ";s:3:"ハ";s:3:"ﾋ";s:3:"ヒ";s:3:"ﾌ";s:3:"フ";s:3:"ﾍ";s:3:"ヘ";s:3:"ﾎ";s:3:"ホ";s:3:"ﾏ";s:3:"マ";s:3:"ﾐ";s:3:"ミ";s:3:"ﾑ";s:3:"ム";s:3:"ﾒ";s:3:"メ";s:3:"ﾓ";s:3:"モ";s:3:"ﾔ";s:3:"ヤ";s:3:"ﾕ";s:3:"ユ";s:3:"ﾖ";s:3:"ヨ";s:3:"ﾗ";s:3:"ラ";s:3:"ﾘ";s:3:"リ";s:3:"ﾙ";s:3:"ル";s:3:"ﾚ";s:3:"レ";s:3:"ﾛ";s:3:"ロ";s:3:"ﾜ";s:3:"ワ";s:3:"ﾝ";s:3:"ン";s:3:"ﾞ";s:3:"゙";s:3:"ﾟ";s:3:"゚";s:3:"ﾠ";s:3:"ᅠ";s:3:"ﾡ";s:3:"ᄀ";s:3:"ﾢ";s:3:"ᄁ";s:3:"ﾣ";s:3:"ᆪ";s:3:"ﾤ";s:3:"ᄂ";s:3:"ﾥ";s:3:"ᆬ";s:3:"ﾦ";s:3:"ᆭ";s:3:"ﾧ";s:3:"ᄃ";s:3:"ﾨ";s:3:"ᄄ";s:3:"ﾩ";s:3:"ᄅ";s:3:"ﾪ";s:3:"ᆰ";s:3:"ﾫ";s:3:"ᆱ";s:3:"ﾬ";s:3:"ᆲ";s:3:"ﾭ";s:3:"ᆳ";s:3:"ﾮ";s:3:"ᆴ";s:3:"ﾯ";s:3:"ᆵ";s:3:"ﾰ";s:3:"ᄚ";s:3:"ﾱ";s:3:"ᄆ";s:3:"ﾲ";s:3:"ᄇ";s:3:"ﾳ";s:3:"ᄈ";s:3:"ﾴ";s:3:"ᄡ";s:3:"ﾵ";s:3:"ᄉ";s:3:"ﾶ";s:3:"ᄊ";s:3:"ﾷ";s:3:"ᄋ";s:3:"ﾸ";s:3:"ᄌ";s:3:"ﾹ";s:3:"ᄍ";s:3:"ﾺ";s:3:"ᄎ";s:3:"ﾻ";s:3:"ᄏ";s:3:"ﾼ";s:3:"ᄐ";s:3:"ﾽ";s:3:"ᄑ";s:3:"ﾾ";s:3:"ᄒ";s:3:"ￂ";s:3:"ᅡ";s:3:"ￃ";s:3:"ᅢ";s:3:"ￄ";s:3:"ᅣ";s:3:"ￅ";s:3:"ᅤ";s:3:"ￆ";s:3:"ᅥ";s:3:"ￇ";s:3:"ᅦ";s:3:"ￊ";s:3:"ᅧ";s:3:"ￋ";s:3:"ᅨ";s:3:"ￌ";s:3:"ᅩ";s:3:"ￍ";s:3:"ᅪ";s:3:"ￎ";s:3:"ᅫ";s:3:"ￏ";s:3:"ᅬ";s:3:"ￒ";s:3:"ᅭ";s:3:"ￓ";s:3:"ᅮ";s:3:"ￔ";s:3:"ᅯ";s:3:"ￕ";s:3:"ᅰ";s:3:"ￖ";s:3:"ᅱ";s:3:"ￗ";s:3:"ᅲ";s:3:"ￚ";s:3:"ᅳ";s:3:"ￛ";s:3:"ᅴ";s:3:"ￜ";s:3:"ᅵ";s:3:"￠";s:2:"¢";s:3:"￡";s:2:"£";s:3:"￢";s:2:"¬";s:3:"￣";s:3:" ̄";s:3:"￤";s:2:"¦";s:3:"￥";s:2:"¥";s:3:"￦";s:3:"₩";s:3:"￨";s:3:"│";s:3:"￩";s:3:"←";s:3:"￪";s:3:"↑";s:3:"￫";s:3:"→";s:3:"￬";s:3:"↓";s:3:"￭";s:3:"■";s:3:"￮";s:3:"○";s:4:"𝅗𝅥";s:8:"𝅗𝅥";s:4:"𝅘𝅥";s:8:"𝅘𝅥";s:4:"𝅘𝅥𝅮";s:12:"𝅘𝅥𝅮";s:4:"𝅘𝅥𝅯";s:12:"𝅘𝅥𝅯";s:4:"𝅘𝅥𝅰";s:12:"𝅘𝅥𝅰";s:4:"𝅘𝅥𝅱";s:12:"𝅘𝅥𝅱";s:4:"𝅘𝅥𝅲";s:12:"𝅘𝅥𝅲";s:4:"𝆹𝅥";s:8:"𝆹𝅥";s:4:"𝆺𝅥";s:8:"𝆺𝅥";s:4:"𝆹𝅥𝅮";s:12:"𝆹𝅥𝅮";s:4:"𝆺𝅥𝅮";s:12:"𝆺𝅥𝅮";s:4:"𝆹𝅥𝅯";s:12:"𝆹𝅥𝅯";s:4:"𝆺𝅥𝅯";s:12:"𝆺𝅥𝅯";s:4:"𝐀";s:1:"A";s:4:"𝐁";s:1:"B";s:4:"𝐂";s:1:"C";s:4:"𝐃";s:1:"D";s:4:"𝐄";s:1:"E";s:4:"𝐅";s:1:"F";s:4:"𝐆";s:1:"G";s:4:"𝐇";s:1:"H";s:4:"𝐈";s:1:"I";s:4:"𝐉";s:1:"J";s:4:"𝐊";s:1:"K";s:4:"𝐋";s:1:"L";s:4:"𝐌";s:1:"M";s:4:"𝐍";s:1:"N";s:4:"𝐎";s:1:"O";s:4:"𝐏";s:1:"P";s:4:"𝐐";s:1:"Q";s:4:"𝐑";s:1:"R";s:4:"𝐒";s:1:"S";s:4:"𝐓";s:1:"T";s:4:"𝐔";s:1:"U";s:4:"𝐕";s:1:"V";s:4:"𝐖";s:1:"W";s:4:"𝐗";s:1:"X";s:4:"𝐘";s:1:"Y";s:4:"𝐙";s:1:"Z";s:4:"𝐚";s:1:"a";s:4:"𝐛";s:1:"b";s:4:"𝐜";s:1:"c";s:4:"𝐝";s:1:"d";s:4:"𝐞";s:1:"e";s:4:"𝐟";s:1:"f";s:4:"𝐠";s:1:"g";s:4:"𝐡";s:1:"h";s:4:"𝐢";s:1:"i";s:4:"𝐣";s:1:"j";s:4:"𝐤";s:1:"k";s:4:"𝐥";s:1:"l";s:4:"𝐦";s:1:"m";s:4:"𝐧";s:1:"n";s:4:"𝐨";s:1:"o";s:4:"𝐩";s:1:"p";s:4:"𝐪";s:1:"q";s:4:"𝐫";s:1:"r";s:4:"𝐬";s:1:"s";s:4:"𝐭";s:1:"t";s:4:"𝐮";s:1:"u";s:4:"𝐯";s:1:"v";s:4:"𝐰";s:1:"w";s:4:"𝐱";s:1:"x";s:4:"𝐲";s:1:"y";s:4:"𝐳";s:1:"z";s:4:"𝐴";s:1:"A";s:4:"𝐵";s:1:"B";s:4:"𝐶";s:1:"C";s:4:"𝐷";s:1:"D";s:4:"𝐸";s:1:"E";s:4:"𝐹";s:1:"F";s:4:"𝐺";s:1:"G";s:4:"𝐻";s:1:"H";s:4:"𝐼";s:1:"I";s:4:"𝐽";s:1:"J";s:4:"𝐾";s:1:"K";s:4:"𝐿";s:1:"L";s:4:"𝑀";s:1:"M";s:4:"𝑁";s:1:"N";s:4:"𝑂";s:1:"O";s:4:"𝑃";s:1:"P";s:4:"𝑄";s:1:"Q";s:4:"𝑅";s:1:"R";s:4:"𝑆";s:1:"S";s:4:"𝑇";s:1:"T";s:4:"𝑈";s:1:"U";s:4:"𝑉";s:1:"V";s:4:"𝑊";s:1:"W";s:4:"𝑋";s:1:"X";s:4:"𝑌";s:1:"Y";s:4:"𝑍";s:1:"Z";s:4:"𝑎";s:1:"a";s:4:"𝑏";s:1:"b";s:4:"𝑐";s:1:"c";s:4:"𝑑";s:1:"d";s:4:"𝑒";s:1:"e";s:4:"𝑓";s:1:"f";s:4:"𝑔";s:1:"g";s:4:"𝑖";s:1:"i";s:4:"𝑗";s:1:"j";s:4:"𝑘";s:1:"k";s:4:"𝑙";s:1:"l";s:4:"𝑚";s:1:"m";s:4:"𝑛";s:1:"n";s:4:"𝑜";s:1:"o";s:4:"𝑝";s:1:"p";s:4:"𝑞";s:1:"q";s:4:"𝑟";s:1:"r";s:4:"𝑠";s:1:"s";s:4:"𝑡";s:1:"t";s:4:"𝑢";s:1:"u";s:4:"𝑣";s:1:"v";s:4:"𝑤";s:1:"w";s:4:"𝑥";s:1:"x";s:4:"𝑦";s:1:"y";s:4:"𝑧";s:1:"z";s:4:"𝑨";s:1:"A";s:4:"𝑩";s:1:"B";s:4:"𝑪";s:1:"C";s:4:"𝑫";s:1:"D";s:4:"𝑬";s:1:"E";s:4:"𝑭";s:1:"F";s:4:"𝑮";s:1:"G";s:4:"𝑯";s:1:"H";s:4:"𝑰";s:1:"I";s:4:"𝑱";s:1:"J";s:4:"𝑲";s:1:"K";s:4:"𝑳";s:1:"L";s:4:"𝑴";s:1:"M";s:4:"𝑵";s:1:"N";s:4:"𝑶";s:1:"O";s:4:"𝑷";s:1:"P";s:4:"𝑸";s:1:"Q";s:4:"𝑹";s:1:"R";s:4:"𝑺";s:1:"S";s:4:"𝑻";s:1:"T";s:4:"𝑼";s:1:"U";s:4:"𝑽";s:1:"V";s:4:"𝑾";s:1:"W";s:4:"𝑿";s:1:"X";s:4:"𝒀";s:1:"Y";s:4:"𝒁";s:1:"Z";s:4:"𝒂";s:1:"a";s:4:"𝒃";s:1:"b";s:4:"𝒄";s:1:"c";s:4:"𝒅";s:1:"d";s:4:"𝒆";s:1:"e";s:4:"𝒇";s:1:"f";s:4:"𝒈";s:1:"g";s:4:"𝒉";s:1:"h";s:4:"𝒊";s:1:"i";s:4:"𝒋";s:1:"j";s:4:"𝒌";s:1:"k";s:4:"𝒍";s:1:"l";s:4:"𝒎";s:1:"m";s:4:"𝒏";s:1:"n";s:4:"𝒐";s:1:"o";s:4:"𝒑";s:1:"p";s:4:"𝒒";s:1:"q";s:4:"𝒓";s:1:"r";s:4:"𝒔";s:1:"s";s:4:"𝒕";s:1:"t";s:4:"𝒖";s:1:"u";s:4:"𝒗";s:1:"v";s:4:"𝒘";s:1:"w";s:4:"𝒙";s:1:"x";s:4:"𝒚";s:1:"y";s:4:"𝒛";s:1:"z";s:4:"𝒜";s:1:"A";s:4:"𝒞";s:1:"C";s:4:"𝒟";s:1:"D";s:4:"𝒢";s:1:"G";s:4:"𝒥";s:1:"J";s:4:"𝒦";s:1:"K";s:4:"𝒩";s:1:"N";s:4:"𝒪";s:1:"O";s:4:"𝒫";s:1:"P";s:4:"𝒬";s:1:"Q";s:4:"𝒮";s:1:"S";s:4:"𝒯";s:1:"T";s:4:"𝒰";s:1:"U";s:4:"𝒱";s:1:"V";s:4:"𝒲";s:1:"W";s:4:"𝒳";s:1:"X";s:4:"𝒴";s:1:"Y";s:4:"𝒵";s:1:"Z";s:4:"𝒶";s:1:"a";s:4:"𝒷";s:1:"b";s:4:"𝒸";s:1:"c";s:4:"𝒹";s:1:"d";s:4:"𝒻";s:1:"f";s:4:"𝒽";s:1:"h";s:4:"𝒾";s:1:"i";s:4:"𝒿";s:1:"j";s:4:"𝓀";s:1:"k";s:4:"𝓁";s:1:"l";s:4:"𝓂";s:1:"m";s:4:"𝓃";s:1:"n";s:4:"𝓅";s:1:"p";s:4:"𝓆";s:1:"q";s:4:"𝓇";s:1:"r";s:4:"𝓈";s:1:"s";s:4:"𝓉";s:1:"t";s:4:"𝓊";s:1:"u";s:4:"𝓋";s:1:"v";s:4:"𝓌";s:1:"w";s:4:"𝓍";s:1:"x";s:4:"𝓎";s:1:"y";s:4:"𝓏";s:1:"z";s:4:"𝓐";s:1:"A";s:4:"𝓑";s:1:"B";s:4:"𝓒";s:1:"C";s:4:"𝓓";s:1:"D";s:4:"𝓔";s:1:"E";s:4:"𝓕";s:1:"F";s:4:"𝓖";s:1:"G";s:4:"𝓗";s:1:"H";s:4:"𝓘";s:1:"I";s:4:"𝓙";s:1:"J";s:4:"𝓚";s:1:"K";s:4:"𝓛";s:1:"L";s:4:"𝓜";s:1:"M";s:4:"𝓝";s:1:"N";s:4:"𝓞";s:1:"O";s:4:"𝓟";s:1:"P";s:4:"𝓠";s:1:"Q";s:4:"𝓡";s:1:"R";s:4:"𝓢";s:1:"S";s:4:"𝓣";s:1:"T";s:4:"𝓤";s:1:"U";s:4:"𝓥";s:1:"V";s:4:"𝓦";s:1:"W";s:4:"𝓧";s:1:"X";s:4:"𝓨";s:1:"Y";s:4:"𝓩";s:1:"Z";s:4:"𝓪";s:1:"a";s:4:"𝓫";s:1:"b";s:4:"𝓬";s:1:"c";s:4:"𝓭";s:1:"d";s:4:"𝓮";s:1:"e";s:4:"𝓯";s:1:"f";s:4:"𝓰";s:1:"g";s:4:"𝓱";s:1:"h";s:4:"𝓲";s:1:"i";s:4:"𝓳";s:1:"j";s:4:"𝓴";s:1:"k";s:4:"𝓵";s:1:"l";s:4:"𝓶";s:1:"m";s:4:"𝓷";s:1:"n";s:4:"𝓸";s:1:"o";s:4:"𝓹";s:1:"p";s:4:"𝓺";s:1:"q";s:4:"𝓻";s:1:"r";s:4:"𝓼";s:1:"s";s:4:"𝓽";s:1:"t";s:4:"𝓾";s:1:"u";s:4:"𝓿";s:1:"v";s:4:"𝔀";s:1:"w";s:4:"𝔁";s:1:"x";s:4:"𝔂";s:1:"y";s:4:"𝔃";s:1:"z";s:4:"𝔄";s:1:"A";s:4:"𝔅";s:1:"B";s:4:"𝔇";s:1:"D";s:4:"𝔈";s:1:"E";s:4:"𝔉";s:1:"F";s:4:"𝔊";s:1:"G";s:4:"𝔍";s:1:"J";s:4:"𝔎";s:1:"K";s:4:"𝔏";s:1:"L";s:4:"𝔐";s:1:"M";s:4:"𝔑";s:1:"N";s:4:"𝔒";s:1:"O";s:4:"𝔓";s:1:"P";s:4:"𝔔";s:1:"Q";s:4:"𝔖";s:1:"S";s:4:"𝔗";s:1:"T";s:4:"𝔘";s:1:"U";s:4:"𝔙";s:1:"V";s:4:"𝔚";s:1:"W";s:4:"𝔛";s:1:"X";s:4:"𝔜";s:1:"Y";s:4:"𝔞";s:1:"a";s:4:"𝔟";s:1:"b";s:4:"𝔠";s:1:"c";s:4:"𝔡";s:1:"d";s:4:"𝔢";s:1:"e";s:4:"𝔣";s:1:"f";s:4:"𝔤";s:1:"g";s:4:"𝔥";s:1:"h";s:4:"𝔦";s:1:"i";s:4:"𝔧";s:1:"j";s:4:"𝔨";s:1:"k";s:4:"𝔩";s:1:"l";s:4:"𝔪";s:1:"m";s:4:"𝔫";s:1:"n";s:4:"𝔬";s:1:"o";s:4:"𝔭";s:1:"p";s:4:"𝔮";s:1:"q";s:4:"𝔯";s:1:"r";s:4:"𝔰";s:1:"s";s:4:"𝔱";s:1:"t";s:4:"𝔲";s:1:"u";s:4:"𝔳";s:1:"v";s:4:"𝔴";s:1:"w";s:4:"𝔵";s:1:"x";s:4:"𝔶";s:1:"y";s:4:"𝔷";s:1:"z";s:4:"𝔸";s:1:"A";s:4:"𝔹";s:1:"B";s:4:"𝔻";s:1:"D";s:4:"𝔼";s:1:"E";s:4:"𝔽";s:1:"F";s:4:"𝔾";s:1:"G";s:4:"𝕀";s:1:"I";s:4:"𝕁";s:1:"J";s:4:"𝕂";s:1:"K";s:4:"𝕃";s:1:"L";s:4:"𝕄";s:1:"M";s:4:"𝕆";s:1:"O";s:4:"𝕊";s:1:"S";s:4:"𝕋";s:1:"T";s:4:"𝕌";s:1:"U";s:4:"𝕍";s:1:"V";s:4:"𝕎";s:1:"W";s:4:"𝕏";s:1:"X";s:4:"𝕐";s:1:"Y";s:4:"𝕒";s:1:"a";s:4:"𝕓";s:1:"b";s:4:"𝕔";s:1:"c";s:4:"𝕕";s:1:"d";s:4:"𝕖";s:1:"e";s:4:"𝕗";s:1:"f";s:4:"𝕘";s:1:"g";s:4:"𝕙";s:1:"h";s:4:"𝕚";s:1:"i";s:4:"𝕛";s:1:"j";s:4:"𝕜";s:1:"k";s:4:"𝕝";s:1:"l";s:4:"𝕞";s:1:"m";s:4:"𝕟";s:1:"n";s:4:"𝕠";s:1:"o";s:4:"𝕡";s:1:"p";s:4:"𝕢";s:1:"q";s:4:"𝕣";s:1:"r";s:4:"𝕤";s:1:"s";s:4:"𝕥";s:1:"t";s:4:"𝕦";s:1:"u";s:4:"𝕧";s:1:"v";s:4:"𝕨";s:1:"w";s:4:"𝕩";s:1:"x";s:4:"𝕪";s:1:"y";s:4:"𝕫";s:1:"z";s:4:"𝕬";s:1:"A";s:4:"𝕭";s:1:"B";s:4:"𝕮";s:1:"C";s:4:"𝕯";s:1:"D";s:4:"𝕰";s:1:"E";s:4:"𝕱";s:1:"F";s:4:"𝕲";s:1:"G";s:4:"𝕳";s:1:"H";s:4:"𝕴";s:1:"I";s:4:"𝕵";s:1:"J";s:4:"𝕶";s:1:"K";s:4:"𝕷";s:1:"L";s:4:"𝕸";s:1:"M";s:4:"𝕹";s:1:"N";s:4:"𝕺";s:1:"O";s:4:"𝕻";s:1:"P";s:4:"𝕼";s:1:"Q";s:4:"𝕽";s:1:"R";s:4:"𝕾";s:1:"S";s:4:"𝕿";s:1:"T";s:4:"𝖀";s:1:"U";s:4:"𝖁";s:1:"V";s:4:"𝖂";s:1:"W";s:4:"𝖃";s:1:"X";s:4:"𝖄";s:1:"Y";s:4:"𝖅";s:1:"Z";s:4:"𝖆";s:1:"a";s:4:"𝖇";s:1:"b";s:4:"𝖈";s:1:"c";s:4:"𝖉";s:1:"d";s:4:"𝖊";s:1:"e";s:4:"𝖋";s:1:"f";s:4:"𝖌";s:1:"g";s:4:"𝖍";s:1:"h";s:4:"𝖎";s:1:"i";s:4:"𝖏";s:1:"j";s:4:"𝖐";s:1:"k";s:4:"𝖑";s:1:"l";s:4:"𝖒";s:1:"m";s:4:"𝖓";s:1:"n";s:4:"𝖔";s:1:"o";s:4:"𝖕";s:1:"p";s:4:"𝖖";s:1:"q";s:4:"𝖗";s:1:"r";s:4:"𝖘";s:1:"s";s:4:"𝖙";s:1:"t";s:4:"𝖚";s:1:"u";s:4:"𝖛";s:1:"v";s:4:"𝖜";s:1:"w";s:4:"𝖝";s:1:"x";s:4:"𝖞";s:1:"y";s:4:"𝖟";s:1:"z";s:4:"𝖠";s:1:"A";s:4:"𝖡";s:1:"B";s:4:"𝖢";s:1:"C";s:4:"𝖣";s:1:"D";s:4:"𝖤";s:1:"E";s:4:"𝖥";s:1:"F";s:4:"𝖦";s:1:"G";s:4:"𝖧";s:1:"H";s:4:"𝖨";s:1:"I";s:4:"𝖩";s:1:"J";s:4:"𝖪";s:1:"K";s:4:"𝖫";s:1:"L";s:4:"𝖬";s:1:"M";s:4:"𝖭";s:1:"N";s:4:"𝖮";s:1:"O";s:4:"𝖯";s:1:"P";s:4:"𝖰";s:1:"Q";s:4:"𝖱";s:1:"R";s:4:"𝖲";s:1:"S";s:4:"𝖳";s:1:"T";s:4:"𝖴";s:1:"U";s:4:"𝖵";s:1:"V";s:4:"𝖶";s:1:"W";s:4:"𝖷";s:1:"X";s:4:"𝖸";s:1:"Y";s:4:"𝖹";s:1:"Z";s:4:"𝖺";s:1:"a";s:4:"𝖻";s:1:"b";s:4:"𝖼";s:1:"c";s:4:"𝖽";s:1:"d";s:4:"𝖾";s:1:"e";s:4:"𝖿";s:1:"f";s:4:"𝗀";s:1:"g";s:4:"𝗁";s:1:"h";s:4:"𝗂";s:1:"i";s:4:"𝗃";s:1:"j";s:4:"𝗄";s:1:"k";s:4:"𝗅";s:1:"l";s:4:"𝗆";s:1:"m";s:4:"𝗇";s:1:"n";s:4:"𝗈";s:1:"o";s:4:"𝗉";s:1:"p";s:4:"𝗊";s:1:"q";s:4:"𝗋";s:1:"r";s:4:"𝗌";s:1:"s";s:4:"𝗍";s:1:"t";s:4:"𝗎";s:1:"u";s:4:"𝗏";s:1:"v";s:4:"𝗐";s:1:"w";s:4:"𝗑";s:1:"x";s:4:"𝗒";s:1:"y";s:4:"𝗓";s:1:"z";s:4:"𝗔";s:1:"A";s:4:"𝗕";s:1:"B";s:4:"𝗖";s:1:"C";s:4:"𝗗";s:1:"D";s:4:"𝗘";s:1:"E";s:4:"𝗙";s:1:"F";s:4:"𝗚";s:1:"G";s:4:"𝗛";s:1:"H";s:4:"𝗜";s:1:"I";s:4:"𝗝";s:1:"J";s:4:"𝗞";s:1:"K";s:4:"𝗟";s:1:"L";s:4:"𝗠";s:1:"M";s:4:"𝗡";s:1:"N";s:4:"𝗢";s:1:"O";s:4:"𝗣";s:1:"P";s:4:"𝗤";s:1:"Q";s:4:"𝗥";s:1:"R";s:4:"𝗦";s:1:"S";s:4:"𝗧";s:1:"T";s:4:"𝗨";s:1:"U";s:4:"𝗩";s:1:"V";s:4:"𝗪";s:1:"W";s:4:"𝗫";s:1:"X";s:4:"𝗬";s:1:"Y";s:4:"𝗭";s:1:"Z";s:4:"𝗮";s:1:"a";s:4:"𝗯";s:1:"b";s:4:"𝗰";s:1:"c";s:4:"𝗱";s:1:"d";s:4:"𝗲";s:1:"e";s:4:"𝗳";s:1:"f";s:4:"𝗴";s:1:"g";s:4:"𝗵";s:1:"h";s:4:"𝗶";s:1:"i";s:4:"𝗷";s:1:"j";s:4:"𝗸";s:1:"k";s:4:"𝗹";s:1:"l";s:4:"𝗺";s:1:"m";s:4:"𝗻";s:1:"n";s:4:"𝗼";s:1:"o";s:4:"𝗽";s:1:"p";s:4:"𝗾";s:1:"q";s:4:"𝗿";s:1:"r";s:4:"𝘀";s:1:"s";s:4:"𝘁";s:1:"t";s:4:"𝘂";s:1:"u";s:4:"𝘃";s:1:"v";s:4:"𝘄";s:1:"w";s:4:"𝘅";s:1:"x";s:4:"𝘆";s:1:"y";s:4:"𝘇";s:1:"z";s:4:"𝘈";s:1:"A";s:4:"𝘉";s:1:"B";s:4:"𝘊";s:1:"C";s:4:"𝘋";s:1:"D";s:4:"𝘌";s:1:"E";s:4:"𝘍";s:1:"F";s:4:"𝘎";s:1:"G";s:4:"𝘏";s:1:"H";s:4:"𝘐";s:1:"I";s:4:"𝘑";s:1:"J";s:4:"𝘒";s:1:"K";s:4:"𝘓";s:1:"L";s:4:"𝘔";s:1:"M";s:4:"𝘕";s:1:"N";s:4:"𝘖";s:1:"O";s:4:"𝘗";s:1:"P";s:4:"𝘘";s:1:"Q";s:4:"𝘙";s:1:"R";s:4:"𝘚";s:1:"S";s:4:"𝘛";s:1:"T";s:4:"𝘜";s:1:"U";s:4:"𝘝";s:1:"V";s:4:"𝘞";s:1:"W";s:4:"𝘟";s:1:"X";s:4:"𝘠";s:1:"Y";s:4:"𝘡";s:1:"Z";s:4:"𝘢";s:1:"a";s:4:"𝘣";s:1:"b";s:4:"𝘤";s:1:"c";s:4:"𝘥";s:1:"d";s:4:"𝘦";s:1:"e";s:4:"𝘧";s:1:"f";s:4:"𝘨";s:1:"g";s:4:"𝘩";s:1:"h";s:4:"𝘪";s:1:"i";s:4:"𝘫";s:1:"j";s:4:"𝘬";s:1:"k";s:4:"𝘭";s:1:"l";s:4:"𝘮";s:1:"m";s:4:"𝘯";s:1:"n";s:4:"𝘰";s:1:"o";s:4:"𝘱";s:1:"p";s:4:"𝘲";s:1:"q";s:4:"𝘳";s:1:"r";s:4:"𝘴";s:1:"s";s:4:"𝘵";s:1:"t";s:4:"𝘶";s:1:"u";s:4:"𝘷";s:1:"v";s:4:"𝘸";s:1:"w";s:4:"𝘹";s:1:"x";s:4:"𝘺";s:1:"y";s:4:"𝘻";s:1:"z";s:4:"𝘼";s:1:"A";s:4:"𝘽";s:1:"B";s:4:"𝘾";s:1:"C";s:4:"𝘿";s:1:"D";s:4:"𝙀";s:1:"E";s:4:"𝙁";s:1:"F";s:4:"𝙂";s:1:"G";s:4:"𝙃";s:1:"H";s:4:"𝙄";s:1:"I";s:4:"𝙅";s:1:"J";s:4:"𝙆";s:1:"K";s:4:"𝙇";s:1:"L";s:4:"𝙈";s:1:"M";s:4:"𝙉";s:1:"N";s:4:"𝙊";s:1:"O";s:4:"𝙋";s:1:"P";s:4:"𝙌";s:1:"Q";s:4:"𝙍";s:1:"R";s:4:"𝙎";s:1:"S";s:4:"𝙏";s:1:"T";s:4:"𝙐";s:1:"U";s:4:"𝙑";s:1:"V";s:4:"𝙒";s:1:"W";s:4:"𝙓";s:1:"X";s:4:"𝙔";s:1:"Y";s:4:"𝙕";s:1:"Z";s:4:"𝙖";s:1:"a";s:4:"𝙗";s:1:"b";s:4:"𝙘";s:1:"c";s:4:"𝙙";s:1:"d";s:4:"𝙚";s:1:"e";s:4:"𝙛";s:1:"f";s:4:"𝙜";s:1:"g";s:4:"𝙝";s:1:"h";s:4:"𝙞";s:1:"i";s:4:"𝙟";s:1:"j";s:4:"𝙠";s:1:"k";s:4:"𝙡";s:1:"l";s:4:"𝙢";s:1:"m";s:4:"𝙣";s:1:"n";s:4:"𝙤";s:1:"o";s:4:"𝙥";s:1:"p";s:4:"𝙦";s:1:"q";s:4:"𝙧";s:1:"r";s:4:"𝙨";s:1:"s";s:4:"𝙩";s:1:"t";s:4:"𝙪";s:1:"u";s:4:"𝙫";s:1:"v";s:4:"𝙬";s:1:"w";s:4:"𝙭";s:1:"x";s:4:"𝙮";s:1:"y";s:4:"𝙯";s:1:"z";s:4:"𝙰";s:1:"A";s:4:"𝙱";s:1:"B";s:4:"𝙲";s:1:"C";s:4:"𝙳";s:1:"D";s:4:"𝙴";s:1:"E";s:4:"𝙵";s:1:"F";s:4:"𝙶";s:1:"G";s:4:"𝙷";s:1:"H";s:4:"𝙸";s:1:"I";s:4:"𝙹";s:1:"J";s:4:"𝙺";s:1:"K";s:4:"𝙻";s:1:"L";s:4:"𝙼";s:1:"M";s:4:"𝙽";s:1:"N";s:4:"𝙾";s:1:"O";s:4:"𝙿";s:1:"P";s:4:"𝚀";s:1:"Q";s:4:"𝚁";s:1:"R";s:4:"𝚂";s:1:"S";s:4:"𝚃";s:1:"T";s:4:"𝚄";s:1:"U";s:4:"𝚅";s:1:"V";s:4:"𝚆";s:1:"W";s:4:"𝚇";s:1:"X";s:4:"𝚈";s:1:"Y";s:4:"𝚉";s:1:"Z";s:4:"𝚊";s:1:"a";s:4:"𝚋";s:1:"b";s:4:"𝚌";s:1:"c";s:4:"𝚍";s:1:"d";s:4:"𝚎";s:1:"e";s:4:"𝚏";s:1:"f";s:4:"𝚐";s:1:"g";s:4:"𝚑";s:1:"h";s:4:"𝚒";s:1:"i";s:4:"𝚓";s:1:"j";s:4:"𝚔";s:1:"k";s:4:"𝚕";s:1:"l";s:4:"𝚖";s:1:"m";s:4:"𝚗";s:1:"n";s:4:"𝚘";s:1:"o";s:4:"𝚙";s:1:"p";s:4:"𝚚";s:1:"q";s:4:"𝚛";s:1:"r";s:4:"𝚜";s:1:"s";s:4:"𝚝";s:1:"t";s:4:"𝚞";s:1:"u";s:4:"𝚟";s:1:"v";s:4:"𝚠";s:1:"w";s:4:"𝚡";s:1:"x";s:4:"𝚢";s:1:"y";s:4:"𝚣";s:1:"z";s:4:"𝚤";s:2:"ı";s:4:"𝚥";s:2:"ȷ";s:4:"𝚨";s:2:"Α";s:4:"𝚩";s:2:"Β";s:4:"𝚪";s:2:"Γ";s:4:"𝚫";s:2:"Δ";s:4:"𝚬";s:2:"Ε";s:4:"𝚭";s:2:"Ζ";s:4:"𝚮";s:2:"Η";s:4:"𝚯";s:2:"Θ";s:4:"𝚰";s:2:"Ι";s:4:"𝚱";s:2:"Κ";s:4:"𝚲";s:2:"Λ";s:4:"𝚳";s:2:"Μ";s:4:"𝚴";s:2:"Ν";s:4:"𝚵";s:2:"Ξ";s:4:"𝚶";s:2:"Ο";s:4:"𝚷";s:2:"Π";s:4:"𝚸";s:2:"Ρ";s:4:"𝚹";s:2:"Θ";s:4:"𝚺";s:2:"Σ";s:4:"𝚻";s:2:"Τ";s:4:"𝚼";s:2:"Υ";s:4:"𝚽";s:2:"Φ";s:4:"𝚾";s:2:"Χ";s:4:"𝚿";s:2:"Ψ";s:4:"𝛀";s:2:"Ω";s:4:"𝛁";s:3:"∇";s:4:"𝛂";s:2:"α";s:4:"𝛃";s:2:"β";s:4:"𝛄";s:2:"γ";s:4:"𝛅";s:2:"δ";s:4:"𝛆";s:2:"ε";s:4:"𝛇";s:2:"ζ";s:4:"𝛈";s:2:"η";s:4:"𝛉";s:2:"θ";s:4:"𝛊";s:2:"ι";s:4:"𝛋";s:2:"κ";s:4:"𝛌";s:2:"λ";s:4:"𝛍";s:2:"μ";s:4:"𝛎";s:2:"ν";s:4:"𝛏";s:2:"ξ";s:4:"𝛐";s:2:"ο";s:4:"𝛑";s:2:"π";s:4:"𝛒";s:2:"ρ";s:4:"𝛓";s:2:"ς";s:4:"𝛔";s:2:"σ";s:4:"𝛕";s:2:"τ";s:4:"𝛖";s:2:"υ";s:4:"𝛗";s:2:"φ";s:4:"𝛘";s:2:"χ";s:4:"𝛙";s:2:"ψ";s:4:"𝛚";s:2:"ω";s:4:"𝛛";s:3:"∂";s:4:"𝛜";s:2:"ε";s:4:"𝛝";s:2:"θ";s:4:"𝛞";s:2:"κ";s:4:"𝛟";s:2:"φ";s:4:"𝛠";s:2:"ρ";s:4:"𝛡";s:2:"π";s:4:"𝛢";s:2:"Α";s:4:"𝛣";s:2:"Β";s:4:"𝛤";s:2:"Γ";s:4:"𝛥";s:2:"Δ";s:4:"𝛦";s:2:"Ε";s:4:"𝛧";s:2:"Ζ";s:4:"𝛨";s:2:"Η";s:4:"𝛩";s:2:"Θ";s:4:"𝛪";s:2:"Ι";s:4:"𝛫";s:2:"Κ";s:4:"𝛬";s:2:"Λ";s:4:"𝛭";s:2:"Μ";s:4:"𝛮";s:2:"Ν";s:4:"𝛯";s:2:"Ξ";s:4:"𝛰";s:2:"Ο";s:4:"𝛱";s:2:"Π";s:4:"𝛲";s:2:"Ρ";s:4:"𝛳";s:2:"Θ";s:4:"𝛴";s:2:"Σ";s:4:"𝛵";s:2:"Τ";s:4:"𝛶";s:2:"Υ";s:4:"𝛷";s:2:"Φ";s:4:"𝛸";s:2:"Χ";s:4:"𝛹";s:2:"Ψ";s:4:"𝛺";s:2:"Ω";s:4:"𝛻";s:3:"∇";s:4:"𝛼";s:2:"α";s:4:"𝛽";s:2:"β";s:4:"𝛾";s:2:"γ";s:4:"𝛿";s:2:"δ";s:4:"𝜀";s:2:"ε";s:4:"𝜁";s:2:"ζ";s:4:"𝜂";s:2:"η";s:4:"𝜃";s:2:"θ";s:4:"𝜄";s:2:"ι";s:4:"𝜅";s:2:"κ";s:4:"𝜆";s:2:"λ";s:4:"𝜇";s:2:"μ";s:4:"𝜈";s:2:"ν";s:4:"𝜉";s:2:"ξ";s:4:"𝜊";s:2:"ο";s:4:"𝜋";s:2:"π";s:4:"𝜌";s:2:"ρ";s:4:"𝜍";s:2:"ς";s:4:"𝜎";s:2:"σ";s:4:"𝜏";s:2:"τ";s:4:"𝜐";s:2:"υ";s:4:"𝜑";s:2:"φ";s:4:"𝜒";s:2:"χ";s:4:"𝜓";s:2:"ψ";s:4:"𝜔";s:2:"ω";s:4:"𝜕";s:3:"∂";s:4:"𝜖";s:2:"ε";s:4:"𝜗";s:2:"θ";s:4:"𝜘";s:2:"κ";s:4:"𝜙";s:2:"φ";s:4:"𝜚";s:2:"ρ";s:4:"𝜛";s:2:"π";s:4:"𝜜";s:2:"Α";s:4:"𝜝";s:2:"Β";s:4:"𝜞";s:2:"Γ";s:4:"𝜟";s:2:"Δ";s:4:"𝜠";s:2:"Ε";s:4:"𝜡";s:2:"Ζ";s:4:"𝜢";s:2:"Η";s:4:"𝜣";s:2:"Θ";s:4:"𝜤";s:2:"Ι";s:4:"𝜥";s:2:"Κ";s:4:"𝜦";s:2:"Λ";s:4:"𝜧";s:2:"Μ";s:4:"𝜨";s:2:"Ν";s:4:"𝜩";s:2:"Ξ";s:4:"𝜪";s:2:"Ο";s:4:"𝜫";s:2:"Π";s:4:"𝜬";s:2:"Ρ";s:4:"𝜭";s:2:"Θ";s:4:"𝜮";s:2:"Σ";s:4:"𝜯";s:2:"Τ";s:4:"𝜰";s:2:"Υ";s:4:"𝜱";s:2:"Φ";s:4:"𝜲";s:2:"Χ";s:4:"𝜳";s:2:"Ψ";s:4:"𝜴";s:2:"Ω";s:4:"𝜵";s:3:"∇";s:4:"𝜶";s:2:"α";s:4:"𝜷";s:2:"β";s:4:"𝜸";s:2:"γ";s:4:"𝜹";s:2:"δ";s:4:"𝜺";s:2:"ε";s:4:"𝜻";s:2:"ζ";s:4:"𝜼";s:2:"η";s:4:"𝜽";s:2:"θ";s:4:"𝜾";s:2:"ι";s:4:"𝜿";s:2:"κ";s:4:"𝝀";s:2:"λ";s:4:"𝝁";s:2:"μ";s:4:"𝝂";s:2:"ν";s:4:"𝝃";s:2:"ξ";s:4:"𝝄";s:2:"ο";s:4:"𝝅";s:2:"π";s:4:"𝝆";s:2:"ρ";s:4:"𝝇";s:2:"ς";s:4:"𝝈";s:2:"σ";s:4:"𝝉";s:2:"τ";s:4:"𝝊";s:2:"υ";s:4:"𝝋";s:2:"φ";s:4:"𝝌";s:2:"χ";s:4:"𝝍";s:2:"ψ";s:4:"𝝎";s:2:"ω";s:4:"𝝏";s:3:"∂";s:4:"𝝐";s:2:"ε";s:4:"𝝑";s:2:"θ";s:4:"𝝒";s:2:"κ";s:4:"𝝓";s:2:"φ";s:4:"𝝔";s:2:"ρ";s:4:"𝝕";s:2:"π";s:4:"𝝖";s:2:"Α";s:4:"𝝗";s:2:"Β";s:4:"𝝘";s:2:"Γ";s:4:"𝝙";s:2:"Δ";s:4:"𝝚";s:2:"Ε";s:4:"𝝛";s:2:"Ζ";s:4:"𝝜";s:2:"Η";s:4:"𝝝";s:2:"Θ";s:4:"𝝞";s:2:"Ι";s:4:"𝝟";s:2:"Κ";s:4:"𝝠";s:2:"Λ";s:4:"𝝡";s:2:"Μ";s:4:"𝝢";s:2:"Ν";s:4:"𝝣";s:2:"Ξ";s:4:"𝝤";s:2:"Ο";s:4:"𝝥";s:2:"Π";s:4:"𝝦";s:2:"Ρ";s:4:"𝝧";s:2:"Θ";s:4:"𝝨";s:2:"Σ";s:4:"𝝩";s:2:"Τ";s:4:"𝝪";s:2:"Υ";s:4:"𝝫";s:2:"Φ";s:4:"𝝬";s:2:"Χ";s:4:"𝝭";s:2:"Ψ";s:4:"𝝮";s:2:"Ω";s:4:"𝝯";s:3:"∇";s:4:"𝝰";s:2:"α";s:4:"𝝱";s:2:"β";s:4:"𝝲";s:2:"γ";s:4:"𝝳";s:2:"δ";s:4:"𝝴";s:2:"ε";s:4:"𝝵";s:2:"ζ";s:4:"𝝶";s:2:"η";s:4:"𝝷";s:2:"θ";s:4:"𝝸";s:2:"ι";s:4:"𝝹";s:2:"κ";s:4:"𝝺";s:2:"λ";s:4:"𝝻";s:2:"μ";s:4:"𝝼";s:2:"ν";s:4:"𝝽";s:2:"ξ";s:4:"𝝾";s:2:"ο";s:4:"𝝿";s:2:"π";s:4:"𝞀";s:2:"ρ";s:4:"𝞁";s:2:"ς";s:4:"𝞂";s:2:"σ";s:4:"𝞃";s:2:"τ";s:4:"𝞄";s:2:"υ";s:4:"𝞅";s:2:"φ";s:4:"𝞆";s:2:"χ";s:4:"𝞇";s:2:"ψ";s:4:"𝞈";s:2:"ω";s:4:"𝞉";s:3:"∂";s:4:"𝞊";s:2:"ε";s:4:"𝞋";s:2:"θ";s:4:"𝞌";s:2:"κ";s:4:"𝞍";s:2:"φ";s:4:"𝞎";s:2:"ρ";s:4:"𝞏";s:2:"π";s:4:"𝞐";s:2:"Α";s:4:"𝞑";s:2:"Β";s:4:"𝞒";s:2:"Γ";s:4:"𝞓";s:2:"Δ";s:4:"𝞔";s:2:"Ε";s:4:"𝞕";s:2:"Ζ";s:4:"𝞖";s:2:"Η";s:4:"𝞗";s:2:"Θ";s:4:"𝞘";s:2:"Ι";s:4:"𝞙";s:2:"Κ";s:4:"𝞚";s:2:"Λ";s:4:"𝞛";s:2:"Μ";s:4:"𝞜";s:2:"Ν";s:4:"𝞝";s:2:"Ξ";s:4:"𝞞";s:2:"Ο";s:4:"𝞟";s:2:"Π";s:4:"𝞠";s:2:"Ρ";s:4:"𝞡";s:2:"Θ";s:4:"𝞢";s:2:"Σ";s:4:"𝞣";s:2:"Τ";s:4:"𝞤";s:2:"Υ";s:4:"𝞥";s:2:"Φ";s:4:"𝞦";s:2:"Χ";s:4:"𝞧";s:2:"Ψ";s:4:"𝞨";s:2:"Ω";s:4:"𝞩";s:3:"∇";s:4:"𝞪";s:2:"α";s:4:"𝞫";s:2:"β";s:4:"𝞬";s:2:"γ";s:4:"𝞭";s:2:"δ";s:4:"𝞮";s:2:"ε";s:4:"𝞯";s:2:"ζ";s:4:"𝞰";s:2:"η";s:4:"𝞱";s:2:"θ";s:4:"𝞲";s:2:"ι";s:4:"𝞳";s:2:"κ";s:4:"𝞴";s:2:"λ";s:4:"𝞵";s:2:"μ";s:4:"𝞶";s:2:"ν";s:4:"𝞷";s:2:"ξ";s:4:"𝞸";s:2:"ο";s:4:"𝞹";s:2:"π";s:4:"𝞺";s:2:"ρ";s:4:"𝞻";s:2:"ς";s:4:"𝞼";s:2:"σ";s:4:"𝞽";s:2:"τ";s:4:"𝞾";s:2:"υ";s:4:"𝞿";s:2:"φ";s:4:"𝟀";s:2:"χ";s:4:"𝟁";s:2:"ψ";s:4:"𝟂";s:2:"ω";s:4:"𝟃";s:3:"∂";s:4:"𝟄";s:2:"ε";s:4:"𝟅";s:2:"θ";s:4:"𝟆";s:2:"κ";s:4:"𝟇";s:2:"φ";s:4:"𝟈";s:2:"ρ";s:4:"𝟉";s:2:"π";s:4:"𝟎";s:1:"0";s:4:"𝟏";s:1:"1";s:4:"𝟐";s:1:"2";s:4:"𝟑";s:1:"3";s:4:"𝟒";s:1:"4";s:4:"𝟓";s:1:"5";s:4:"𝟔";s:1:"6";s:4:"𝟕";s:1:"7";s:4:"𝟖";s:1:"8";s:4:"𝟗";s:1:"9";s:4:"𝟘";s:1:"0";s:4:"𝟙";s:1:"1";s:4:"𝟚";s:1:"2";s:4:"𝟛";s:1:"3";s:4:"𝟜";s:1:"4";s:4:"𝟝";s:1:"5";s:4:"𝟞";s:1:"6";s:4:"𝟟";s:1:"7";s:4:"𝟠";s:1:"8";s:4:"𝟡";s:1:"9";s:4:"𝟢";s:1:"0";s:4:"𝟣";s:1:"1";s:4:"𝟤";s:1:"2";s:4:"𝟥";s:1:"3";s:4:"𝟦";s:1:"4";s:4:"𝟧";s:1:"5";s:4:"𝟨";s:1:"6";s:4:"𝟩";s:1:"7";s:4:"𝟪";s:1:"8";s:4:"𝟫";s:1:"9";s:4:"𝟬";s:1:"0";s:4:"𝟭";s:1:"1";s:4:"𝟮";s:1:"2";s:4:"𝟯";s:1:"3";s:4:"𝟰";s:1:"4";s:4:"𝟱";s:1:"5";s:4:"𝟲";s:1:"6";s:4:"𝟳";s:1:"7";s:4:"𝟴";s:1:"8";s:4:"𝟵";s:1:"9";s:4:"𝟶";s:1:"0";s:4:"𝟷";s:1:"1";s:4:"𝟸";s:1:"2";s:4:"𝟹";s:1:"3";s:4:"𝟺";s:1:"4";s:4:"𝟻";s:1:"5";s:4:"𝟼";s:1:"6";s:4:"𝟽";s:1:"7";s:4:"𝟾";s:1:"8";s:4:"𝟿";s:1:"9";s:4:"丽";s:3:"丽";s:4:"丸";s:3:"丸";s:4:"乁";s:3:"乁";s:4:"𠄢";s:4:"𠄢";s:4:"你";s:3:"你";s:4:"侮";s:3:"侮";s:4:"侻";s:3:"侻";s:4:"倂";s:3:"倂";s:4:"偺";s:3:"偺";s:4:"備";s:3:"備";s:4:"僧";s:3:"僧";s:4:"像";s:3:"像";s:4:"㒞";s:3:"㒞";s:4:"𠘺";s:4:"𠘺";s:4:"免";s:3:"免";s:4:"兔";s:3:"兔";s:4:"兤";s:3:"兤";s:4:"具";s:3:"具";s:4:"𠔜";s:4:"𠔜";s:4:"㒹";s:3:"㒹";s:4:"內";s:3:"內";s:4:"再";s:3:"再";s:4:"𠕋";s:4:"𠕋";s:4:"冗";s:3:"冗";s:4:"冤";s:3:"冤";s:4:"仌";s:3:"仌";s:4:"冬";s:3:"冬";s:4:"况";s:3:"况";s:4:"𩇟";s:4:"𩇟";s:4:"凵";s:3:"凵";s:4:"刃";s:3:"刃";s:4:"㓟";s:3:"㓟";s:4:"刻";s:3:"刻";s:4:"剆";s:3:"剆";s:4:"割";s:3:"割";s:4:"剷";s:3:"剷";s:4:"㔕";s:3:"㔕";s:4:"勇";s:3:"勇";s:4:"勉";s:3:"勉";s:4:"勤";s:3:"勤";s:4:"勺";s:3:"勺";s:4:"包";s:3:"包";s:4:"匆";s:3:"匆";s:4:"北";s:3:"北";s:4:"卉";s:3:"卉";s:4:"卑";s:3:"卑";s:4:"博";s:3:"博";s:4:"即";s:3:"即";s:4:"卽";s:3:"卽";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"𠨬";s:4:"𠨬";s:4:"灰";s:3:"灰";s:4:"及";s:3:"及";s:4:"叟";s:3:"叟";s:4:"𠭣";s:4:"𠭣";s:4:"叫";s:3:"叫";s:4:"叱";s:3:"叱";s:4:"吆";s:3:"吆";s:4:"咞";s:3:"咞";s:4:"吸";s:3:"吸";s:4:"呈";s:3:"呈";s:4:"周";s:3:"周";s:4:"咢";s:3:"咢";s:4:"哶";s:3:"哶";s:4:"唐";s:3:"唐";s:4:"啓";s:3:"啓";s:4:"啣";s:3:"啣";s:4:"善";s:3:"善";s:4:"善";s:3:"善";s:4:"喙";s:3:"喙";s:4:"喫";s:3:"喫";s:4:"喳";s:3:"喳";s:4:"嗂";s:3:"嗂";s:4:"圖";s:3:"圖";s:4:"嘆";s:3:"嘆";s:4:"圗";s:3:"圗";s:4:"噑";s:3:"噑";s:4:"噴";s:3:"噴";s:4:"切";s:3:"切";s:4:"壮";s:3:"壮";s:4:"城";s:3:"城";s:4:"埴";s:3:"埴";s:4:"堍";s:3:"堍";s:4:"型";s:3:"型";s:4:"堲";s:3:"堲";s:4:"報";s:3:"報";s:4:"墬";s:3:"墬";s:4:"𡓤";s:4:"𡓤";s:4:"売";s:3:"売";s:4:"壷";s:3:"壷";s:4:"夆";s:3:"夆";s:4:"多";s:3:"多";s:4:"夢";s:3:"夢";s:4:"奢";s:3:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:4:"姬";s:3:"姬";s:4:"娛";s:3:"娛";s:4:"娧";s:3:"娧";s:4:"姘";s:3:"姘";s:4:"婦";s:3:"婦";s:4:"㛮";s:3:"㛮";s:4:"㛼";s:3:"㛼";s:4:"嬈";s:3:"嬈";s:4:"嬾";s:3:"嬾";s:4:"嬾";s:3:"嬾";s:4:"𡧈";s:4:"𡧈";s:4:"寃";s:3:"寃";s:4:"寘";s:3:"寘";s:4:"寧";s:3:"寧";s:4:"寳";s:3:"寳";s:4:"𡬘";s:4:"𡬘";s:4:"寿";s:3:"寿";s:4:"将";s:3:"将";s:4:"当";s:3:"当";s:4:"尢";s:3:"尢";s:4:"㞁";s:3:"㞁";s:4:"屠";s:3:"屠";s:4:"屮";s:3:"屮";s:4:"峀";s:3:"峀";s:4:"岍";s:3:"岍";s:4:"𡷤";s:4:"𡷤";s:4:"嵃";s:3:"嵃";s:4:"𡷦";s:4:"𡷦";s:4:"嵮";s:3:"嵮";s:4:"嵫";s:3:"嵫";s:4:"嵼";s:3:"嵼";s:4:"巡";s:3:"巡";s:4:"巢";s:3:"巢";s:4:"㠯";s:3:"㠯";s:4:"巽";s:3:"巽";s:4:"帨";s:3:"帨";s:4:"帽";s:3:"帽";s:4:"幩";s:3:"幩";s:4:"㡢";s:3:"㡢";s:4:"𢆃";s:4:"𢆃";s:4:"㡼";s:3:"㡼";s:4:"庰";s:3:"庰";s:4:"庳";s:3:"庳";s:4:"庶";s:3:"庶";s:4:"廊";s:3:"廊";s:4:"𪎒";s:4:"𪎒";s:4:"廾";s:3:"廾";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"舁";s:3:"舁";s:4:"弢";s:3:"弢";s:4:"弢";s:3:"弢";s:4:"㣇";s:3:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:4:"形";s:3:"形";s:4:"彫";s:3:"彫";s:4:"㣣";s:3:"㣣";s:4:"徚";s:3:"徚";s:4:"忍";s:3:"忍";s:4:"志";s:3:"志";s:4:"忹";s:3:"忹";s:4:"悁";s:3:"悁";s:4:"㤺";s:3:"㤺";s:4:"㤜";s:3:"㤜";s:4:"悔";s:3:"悔";s:4:"𢛔";s:4:"𢛔";s:4:"惇";s:3:"惇";s:4:"慈";s:3:"慈";s:4:"慌";s:3:"慌";s:4:"慎";s:3:"慎";s:4:"慌";s:3:"慌";s:4:"慺";s:3:"慺";s:4:"憎";s:3:"憎";s:4:"憲";s:3:"憲";s:4:"憤";s:3:"憤";s:4:"憯";s:3:"憯";s:4:"懞";s:3:"懞";s:4:"懲";s:3:"懲";s:4:"懶";s:3:"懶";s:4:"成";s:3:"成";s:4:"戛";s:3:"戛";s:4:"扝";s:3:"扝";s:4:"抱";s:3:"抱";s:4:"拔";s:3:"拔";s:4:"捐";s:3:"捐";s:4:"𢬌";s:4:"𢬌";s:4:"挽";s:3:"挽";s:4:"拼";s:3:"拼";s:4:"捨";s:3:"捨";s:4:"掃";s:3:"掃";s:4:"揤";s:3:"揤";s:4:"𢯱";s:4:"𢯱";s:4:"搢";s:3:"搢";s:4:"揅";s:3:"揅";s:4:"掩";s:3:"掩";s:4:"㨮";s:3:"㨮";s:4:"摩";s:3:"摩";s:4:"摾";s:3:"摾";s:4:"撝";s:3:"撝";s:4:"摷";s:3:"摷";s:4:"㩬";s:3:"㩬";s:4:"敏";s:3:"敏";s:4:"敬";s:3:"敬";s:4:"𣀊";s:4:"𣀊";s:4:"旣";s:3:"旣";s:4:"書";s:3:"書";s:4:"晉";s:3:"晉";s:4:"㬙";s:3:"㬙";s:4:"暑";s:3:"暑";s:4:"㬈";s:3:"㬈";s:4:"㫤";s:3:"㫤";s:4:"冒";s:3:"冒";s:4:"冕";s:3:"冕";s:4:"最";s:3:"最";s:4:"暜";s:3:"暜";s:4:"肭";s:3:"肭";s:4:"䏙";s:3:"䏙";s:4:"朗";s:3:"朗";s:4:"望";s:3:"望";s:4:"朡";s:3:"朡";s:4:"杞";s:3:"杞";s:4:"杓";s:3:"杓";s:4:"𣏃";s:4:"𣏃";s:4:"㭉";s:3:"㭉";s:4:"柺";s:3:"柺";s:4:"枅";s:3:"枅";s:4:"桒";s:3:"桒";s:4:"梅";s:3:"梅";s:4:"𣑭";s:4:"𣑭";s:4:"梎";s:3:"梎";s:4:"栟";s:3:"栟";s:4:"椔";s:3:"椔";s:4:"㮝";s:3:"㮝";s:4:"楂";s:3:"楂";s:4:"榣";s:3:"榣";s:4:"槪";s:3:"槪";s:4:"檨";s:3:"檨";s:4:"𣚣";s:4:"𣚣";s:4:"櫛";s:3:"櫛";s:4:"㰘";s:3:"㰘";s:4:"次";s:3:"次";s:4:"𣢧";s:4:"𣢧";s:4:"歔";s:3:"歔";s:4:"㱎";s:3:"㱎";s:4:"歲";s:3:"歲";s:4:"殟";s:3:"殟";s:4:"殺";s:3:"殺";s:4:"殻";s:3:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:4:"汎";s:3:"汎";s:4:"𣲼";s:4:"𣲼";s:4:"沿";s:3:"沿";s:4:"泍";s:3:"泍";s:4:"汧";s:3:"汧";s:4:"洖";s:3:"洖";s:4:"派";s:3:"派";s:4:"海";s:3:"海";s:4:"流";s:3:"流";s:4:"浩";s:3:"浩";s:4:"浸";s:3:"浸";s:4:"涅";s:3:"涅";s:4:"𣴞";s:4:"𣴞";s:4:"洴";s:3:"洴";s:4:"港";s:3:"港";s:4:"湮";s:3:"湮";s:4:"㴳";s:3:"㴳";s:4:"滋";s:3:"滋";s:4:"滇";s:3:"滇";s:4:"𣻑";s:4:"𣻑";s:4:"淹";s:3:"淹";s:4:"潮";s:3:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:4:"濆";s:3:"濆";s:4:"瀹";s:3:"瀹";s:4:"瀞";s:3:"瀞";s:4:"瀛";s:3:"瀛";s:4:"㶖";s:3:"㶖";s:4:"灊";s:3:"灊";s:4:"災";s:3:"災";s:4:"灷";s:3:"灷";s:4:"炭";s:3:"炭";s:4:"𠔥";s:4:"𠔥";s:4:"煅";s:3:"煅";s:4:"𤉣";s:4:"𤉣";s:4:"熜";s:3:"熜";s:4:"𤎫";s:4:"𤎫";s:4:"爨";s:3:"爨";s:4:"爵";s:3:"爵";s:4:"牐";s:3:"牐";s:4:"𤘈";s:4:"𤘈";s:4:"犀";s:3:"犀";s:4:"犕";s:3:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:4:"獺";s:3:"獺";s:4:"王";s:3:"王";s:4:"㺬";s:3:"㺬";s:4:"玥";s:3:"玥";s:4:"㺸";s:3:"㺸";s:4:"㺸";s:3:"㺸";s:4:"瑇";s:3:"瑇";s:4:"瑜";s:3:"瑜";s:4:"瑱";s:3:"瑱";s:4:"璅";s:3:"璅";s:4:"瓊";s:3:"瓊";s:4:"㼛";s:3:"㼛";s:4:"甤";s:3:"甤";s:4:"𤰶";s:4:"𤰶";s:4:"甾";s:3:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"異";s:3:"異";s:4:"𢆟";s:4:"𢆟";s:4:"瘐";s:3:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:4:"㿼";s:3:"㿼";s:4:"䀈";s:3:"䀈";s:4:"直";s:3:"直";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:4:"眞";s:3:"眞";s:4:"真";s:3:"真";s:4:"真";s:3:"真";s:4:"睊";s:3:"睊";s:4:"䀹";s:3:"䀹";s:4:"瞋";s:3:"瞋";s:4:"䁆";s:3:"䁆";s:4:"䂖";s:3:"䂖";s:4:"𥐝";s:4:"𥐝";s:4:"硎";s:3:"硎";s:4:"碌";s:3:"碌";s:4:"磌";s:3:"磌";s:4:"䃣";s:3:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"祖";s:3:"祖";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:4:"福";s:3:"福";s:4:"秫";s:3:"秫";s:4:"䄯";s:3:"䄯";s:4:"穀";s:3:"穀";s:4:"穊";s:3:"穊";s:4:"穏";s:3:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"竮";s:3:"竮";s:4:"䈂";s:3:"䈂";s:4:"𥮫";s:4:"𥮫";s:4:"篆";s:3:"篆";s:4:"築";s:3:"築";s:4:"䈧";s:3:"䈧";s:4:"𥲀";s:4:"𥲀";s:4:"糒";s:3:"糒";s:4:"䊠";s:3:"䊠";s:4:"糨";s:3:"糨";s:4:"糣";s:3:"糣";s:4:"紀";s:3:"紀";s:4:"𥾆";s:4:"𥾆";s:4:"絣";s:3:"絣";s:4:"䌁";s:3:"䌁";s:4:"緇";s:3:"緇";s:4:"縂";s:3:"縂";s:4:"繅";s:3:"繅";s:4:"䌴";s:3:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:4:"䍙";s:3:"䍙";s:4:"𦋙";s:4:"𦋙";s:4:"罺";s:3:"罺";s:4:"𦌾";s:4:"𦌾";s:4:"羕";s:3:"羕";s:4:"翺";s:3:"翺";s:4:"者";s:3:"者";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:4:"聠";s:3:"聠";s:4:"𦖨";s:4:"𦖨";s:4:"聰";s:3:"聰";s:4:"𣍟";s:4:"𣍟";s:4:"䏕";s:3:"䏕";s:4:"育";s:3:"育";s:4:"脃";s:3:"脃";s:4:"䐋";s:3:"䐋";s:4:"脾";s:3:"脾";s:4:"媵";s:3:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:4:"舁";s:3:"舁";s:4:"舄";s:3:"舄";s:4:"辞";s:3:"辞";s:4:"䑫";s:3:"䑫";s:4:"芑";s:3:"芑";s:4:"芋";s:3:"芋";s:4:"芝";s:3:"芝";s:4:"劳";s:3:"劳";s:4:"花";s:3:"花";s:4:"芳";s:3:"芳";s:4:"芽";s:3:"芽";s:4:"苦";s:3:"苦";s:4:"𦬼";s:4:"𦬼";s:4:"若";s:3:"若";s:4:"茝";s:3:"茝";s:4:"荣";s:3:"荣";s:4:"莭";s:3:"莭";s:4:"茣";s:3:"茣";s:4:"莽";s:3:"莽";s:4:"菧";s:3:"菧";s:4:"著";s:3:"著";s:4:"荓";s:3:"荓";s:4:"菊";s:3:"菊";s:4:"菌";s:3:"菌";s:4:"菜";s:3:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:4:"䔫";s:3:"䔫";s:4:"蓱";s:3:"蓱";s:4:"蓳";s:3:"蓳";s:4:"蔖";s:3:"蔖";s:4:"𧏊";s:4:"𧏊";s:4:"蕤";s:3:"蕤";s:4:"𦼬";s:4:"𦼬";s:4:"䕝";s:3:"䕝";s:4:"䕡";s:3:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:4:"䕫";s:3:"䕫";s:4:"虐";s:3:"虐";s:4:"虜";s:3:"虜";s:4:"虧";s:3:"虧";s:4:"虩";s:3:"虩";s:4:"蚩";s:3:"蚩";s:4:"蚈";s:3:"蚈";s:4:"蜎";s:3:"蜎";s:4:"蛢";s:3:"蛢";s:4:"蝹";s:3:"蝹";s:4:"蜨";s:3:"蜨";s:4:"蝫";s:3:"蝫";s:4:"螆";s:3:"螆";s:4:"䗗";s:3:"䗗";s:4:"蟡";s:3:"蟡";s:4:"蠁";s:3:"蠁";s:4:"䗹";s:3:"䗹";s:4:"衠";s:3:"衠";s:4:"衣";s:3:"衣";s:4:"𧙧";s:4:"𧙧";s:4:"裗";s:3:"裗";s:4:"裞";s:3:"裞";s:4:"䘵";s:3:"䘵";s:4:"裺";s:3:"裺";s:4:"㒻";s:3:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:4:"䚾";s:3:"䚾";s:4:"䛇";s:3:"䛇";s:4:"誠";s:3:"誠";s:4:"諭";s:3:"諭";s:4:"變";s:3:"變";s:4:"豕";s:3:"豕";s:4:"𧲨";s:4:"𧲨";s:4:"貫";s:3:"貫";s:4:"賁";s:3:"賁";s:4:"贛";s:3:"贛";s:4:"起";s:3:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:4:"跋";s:3:"跋";s:4:"趼";s:3:"趼";s:4:"跰";s:3:"跰";s:4:"𠣞";s:4:"𠣞";s:4:"軔";s:3:"軔";s:4:"輸";s:3:"輸";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:4:"邔";s:3:"邔";s:4:"郱";s:3:"郱";s:4:"鄑";s:3:"鄑";s:4:"𨜮";s:4:"𨜮";s:4:"鄛";s:3:"鄛";s:4:"鈸";s:3:"鈸";s:4:"鋗";s:3:"鋗";s:4:"鋘";s:3:"鋘";s:4:"鉼";s:3:"鉼";s:4:"鏹";s:3:"鏹";s:4:"鐕";s:3:"鐕";s:4:"𨯺";s:4:"𨯺";s:4:"開";s:3:"開";s:4:"䦕";s:3:"䦕";s:4:"閷";s:3:"閷";s:4:"𨵷";s:4:"𨵷";s:4:"䧦";s:3:"䧦";s:4:"雃";s:3:"雃";s:4:"嶲";s:3:"嶲";s:4:"霣";s:3:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:4:"䩮";s:3:"䩮";s:4:"䩶";s:3:"䩶";s:4:"韠";s:3:"韠";s:4:"𩐊";s:4:"𩐊";s:4:"䪲";s:3:"䪲";s:4:"𩒖";s:4:"𩒖";s:4:"頋";s:3:"頋";s:4:"頋";s:3:"頋";s:4:"頩";s:3:"頩";s:4:"𩖶";s:4:"𩖶";s:4:"飢";s:3:"飢";s:4:"䬳";s:3:"䬳";s:4:"餩";s:3:"餩";s:4:"馧";s:3:"馧";s:4:"駂";s:3:"駂";s:4:"駾";s:3:"駾";s:4:"䯎";s:3:"䯎";s:4:"𩬰";s:4:"𩬰";s:4:"鬒";s:3:"鬒";s:4:"鱀";s:3:"鱀";s:4:"鳽";s:3:"鳽";s:4:"䳎";s:3:"䳎";s:4:"䳭";s:3:"䳭";s:4:"鵧";s:3:"鵧";s:4:"𪃎";s:4:"𪃎";s:4:"䳸";s:3:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:4:"麻";s:3:"麻";s:4:"䵖";s:3:"䵖";s:4:"黹";s:3:"黹";s:4:"黾";s:3:"黾";s:4:"鼅";s:3:"鼅";s:4:"鼏";s:3:"鼏";s:4:"鼖";s:3:"鼖";s:4:"鼻";s:3:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
+$utfCompatibilityDecomp = unserialize( 'a:5402:{s:2:" ";s:1:" ";s:2:"¨";s:3:" ̈";s:2:"ª";s:1:"a";s:2:"¯";s:3:" ̄";s:2:"²";s:1:"2";s:2:"³";s:1:"3";s:2:"´";s:3:" ́";s:2:"µ";s:2:"μ";s:2:"¸";s:3:" ̧";s:2:"¹";s:1:"1";s:2:"º";s:1:"o";s:2:"¼";s:5:"1⁄4";s:2:"½";s:5:"1⁄2";s:2:"¾";s:5:"3⁄4";s:2:"À";s:3:"À";s:2:"Á";s:3:"Á";s:2:"Â";s:3:"Â";s:2:"Ã";s:3:"Ã";s:2:"Ä";s:3:"Ä";s:2:"Å";s:3:"Å";s:2:"Ç";s:3:"Ç";s:2:"È";s:3:"È";s:2:"É";s:3:"É";s:2:"Ê";s:3:"Ê";s:2:"Ë";s:3:"Ë";s:2:"Ì";s:3:"Ì";s:2:"Í";s:3:"Í";s:2:"Î";s:3:"Î";s:2:"Ï";s:3:"Ï";s:2:"Ñ";s:3:"Ñ";s:2:"Ò";s:3:"Ò";s:2:"Ó";s:3:"Ó";s:2:"Ô";s:3:"Ô";s:2:"Õ";s:3:"Õ";s:2:"Ö";s:3:"Ö";s:2:"Ù";s:3:"Ù";s:2:"Ú";s:3:"Ú";s:2:"Û";s:3:"Û";s:2:"Ü";s:3:"Ü";s:2:"Ý";s:3:"Ý";s:2:"à";s:3:"à";s:2:"á";s:3:"á";s:2:"â";s:3:"â";s:2:"ã";s:3:"ã";s:2:"ä";s:3:"ä";s:2:"å";s:3:"å";s:2:"ç";s:3:"ç";s:2:"è";s:3:"è";s:2:"é";s:3:"é";s:2:"ê";s:3:"ê";s:2:"ë";s:3:"ë";s:2:"ì";s:3:"ì";s:2:"í";s:3:"í";s:2:"î";s:3:"î";s:2:"ï";s:3:"ï";s:2:"ñ";s:3:"ñ";s:2:"ò";s:3:"ò";s:2:"ó";s:3:"ó";s:2:"ô";s:3:"ô";s:2:"õ";s:3:"õ";s:2:"ö";s:3:"ö";s:2:"ù";s:3:"ù";s:2:"ú";s:3:"ú";s:2:"û";s:3:"û";s:2:"ü";s:3:"ü";s:2:"ý";s:3:"ý";s:2:"ÿ";s:3:"ÿ";s:2:"Ā";s:3:"Ā";s:2:"ā";s:3:"ā";s:2:"Ă";s:3:"Ă";s:2:"ă";s:3:"ă";s:2:"Ą";s:3:"Ą";s:2:"ą";s:3:"ą";s:2:"Ć";s:3:"Ć";s:2:"ć";s:3:"ć";s:2:"Ĉ";s:3:"Ĉ";s:2:"ĉ";s:3:"ĉ";s:2:"Ċ";s:3:"Ċ";s:2:"ċ";s:3:"ċ";s:2:"Č";s:3:"Č";s:2:"č";s:3:"č";s:2:"Ď";s:3:"Ď";s:2:"ď";s:3:"ď";s:2:"Ē";s:3:"Ē";s:2:"ē";s:3:"ē";s:2:"Ĕ";s:3:"Ĕ";s:2:"ĕ";s:3:"ĕ";s:2:"Ė";s:3:"Ė";s:2:"ė";s:3:"ė";s:2:"Ę";s:3:"Ę";s:2:"ę";s:3:"ę";s:2:"Ě";s:3:"Ě";s:2:"ě";s:3:"ě";s:2:"Ĝ";s:3:"Ĝ";s:2:"ĝ";s:3:"ĝ";s:2:"Ğ";s:3:"Ğ";s:2:"ğ";s:3:"ğ";s:2:"Ġ";s:3:"Ġ";s:2:"ġ";s:3:"ġ";s:2:"Ģ";s:3:"Ģ";s:2:"ģ";s:3:"ģ";s:2:"Ĥ";s:3:"Ĥ";s:2:"ĥ";s:3:"ĥ";s:2:"Ĩ";s:3:"Ĩ";s:2:"ĩ";s:3:"ĩ";s:2:"Ī";s:3:"Ī";s:2:"ī";s:3:"ī";s:2:"Ĭ";s:3:"Ĭ";s:2:"ĭ";s:3:"ĭ";s:2:"Į";s:3:"Į";s:2:"į";s:3:"į";s:2:"İ";s:3:"İ";s:2:"Ĳ";s:2:"IJ";s:2:"ĳ";s:2:"ij";s:2:"Ĵ";s:3:"Ĵ";s:2:"ĵ";s:3:"ĵ";s:2:"Ķ";s:3:"Ķ";s:2:"ķ";s:3:"ķ";s:2:"Ĺ";s:3:"Ĺ";s:2:"ĺ";s:3:"ĺ";s:2:"Ļ";s:3:"Ļ";s:2:"ļ";s:3:"ļ";s:2:"Ľ";s:3:"Ľ";s:2:"ľ";s:3:"ľ";s:2:"Ŀ";s:3:"L·";s:2:"ŀ";s:3:"l·";s:2:"Ń";s:3:"Ń";s:2:"ń";s:3:"ń";s:2:"Ņ";s:3:"Ņ";s:2:"ņ";s:3:"ņ";s:2:"Ň";s:3:"Ň";s:2:"ň";s:3:"ň";s:2:"ŉ";s:3:"ʼn";s:2:"Ō";s:3:"Ō";s:2:"ō";s:3:"ō";s:2:"Ŏ";s:3:"Ŏ";s:2:"ŏ";s:3:"ŏ";s:2:"Ő";s:3:"Ő";s:2:"ő";s:3:"ő";s:2:"Ŕ";s:3:"Ŕ";s:2:"ŕ";s:3:"ŕ";s:2:"Ŗ";s:3:"Ŗ";s:2:"ŗ";s:3:"ŗ";s:2:"Ř";s:3:"Ř";s:2:"ř";s:3:"ř";s:2:"Ś";s:3:"Ś";s:2:"ś";s:3:"ś";s:2:"Ŝ";s:3:"Ŝ";s:2:"ŝ";s:3:"ŝ";s:2:"Ş";s:3:"Ş";s:2:"ş";s:3:"ş";s:2:"Š";s:3:"Š";s:2:"š";s:3:"š";s:2:"Ţ";s:3:"Ţ";s:2:"ţ";s:3:"ţ";s:2:"Ť";s:3:"Ť";s:2:"ť";s:3:"ť";s:2:"Ũ";s:3:"Ũ";s:2:"ũ";s:3:"ũ";s:2:"Ū";s:3:"Ū";s:2:"ū";s:3:"ū";s:2:"Ŭ";s:3:"Ŭ";s:2:"ŭ";s:3:"ŭ";s:2:"Ů";s:3:"Ů";s:2:"ů";s:3:"ů";s:2:"Ű";s:3:"Ű";s:2:"ű";s:3:"ű";s:2:"Ų";s:3:"Ų";s:2:"ų";s:3:"ų";s:2:"Ŵ";s:3:"Ŵ";s:2:"ŵ";s:3:"ŵ";s:2:"Ŷ";s:3:"Ŷ";s:2:"ŷ";s:3:"ŷ";s:2:"Ÿ";s:3:"Ÿ";s:2:"Ź";s:3:"Ź";s:2:"ź";s:3:"ź";s:2:"Ż";s:3:"Ż";s:2:"ż";s:3:"ż";s:2:"Ž";s:3:"Ž";s:2:"ž";s:3:"ž";s:2:"ſ";s:1:"s";s:2:"Ơ";s:3:"Ơ";s:2:"ơ";s:3:"ơ";s:2:"Ư";s:3:"Ư";s:2:"ư";s:3:"ư";s:2:"Ǆ";s:4:"DŽ";s:2:"ǅ";s:4:"Dž";s:2:"ǆ";s:4:"dž";s:2:"Ǉ";s:2:"LJ";s:2:"ǈ";s:2:"Lj";s:2:"ǉ";s:2:"lj";s:2:"Ǌ";s:2:"NJ";s:2:"ǋ";s:2:"Nj";s:2:"ǌ";s:2:"nj";s:2:"Ǎ";s:3:"Ǎ";s:2:"ǎ";s:3:"ǎ";s:2:"Ǐ";s:3:"Ǐ";s:2:"ǐ";s:3:"ǐ";s:2:"Ǒ";s:3:"Ǒ";s:2:"ǒ";s:3:"ǒ";s:2:"Ǔ";s:3:"Ǔ";s:2:"ǔ";s:3:"ǔ";s:2:"Ǖ";s:5:"Ǖ";s:2:"ǖ";s:5:"ǖ";s:2:"Ǘ";s:5:"Ǘ";s:2:"ǘ";s:5:"ǘ";s:2:"Ǚ";s:5:"Ǚ";s:2:"ǚ";s:5:"ǚ";s:2:"Ǜ";s:5:"Ǜ";s:2:"ǜ";s:5:"ǜ";s:2:"Ǟ";s:5:"Ǟ";s:2:"ǟ";s:5:"ǟ";s:2:"Ǡ";s:5:"Ǡ";s:2:"ǡ";s:5:"ǡ";s:2:"Ǣ";s:4:"Ǣ";s:2:"ǣ";s:4:"ǣ";s:2:"Ǧ";s:3:"Ǧ";s:2:"ǧ";s:3:"ǧ";s:2:"Ǩ";s:3:"Ǩ";s:2:"ǩ";s:3:"ǩ";s:2:"Ǫ";s:3:"Ǫ";s:2:"ǫ";s:3:"ǫ";s:2:"Ǭ";s:5:"Ǭ";s:2:"ǭ";s:5:"ǭ";s:2:"Ǯ";s:4:"Ǯ";s:2:"ǯ";s:4:"ǯ";s:2:"ǰ";s:3:"ǰ";s:2:"Ǳ";s:2:"DZ";s:2:"ǲ";s:2:"Dz";s:2:"ǳ";s:2:"dz";s:2:"Ǵ";s:3:"Ǵ";s:2:"ǵ";s:3:"ǵ";s:2:"Ǹ";s:3:"Ǹ";s:2:"ǹ";s:3:"ǹ";s:2:"Ǻ";s:5:"Ǻ";s:2:"ǻ";s:5:"ǻ";s:2:"Ǽ";s:4:"Ǽ";s:2:"ǽ";s:4:"ǽ";s:2:"Ǿ";s:4:"Ǿ";s:2:"ǿ";s:4:"ǿ";s:2:"Ȁ";s:3:"Ȁ";s:2:"ȁ";s:3:"ȁ";s:2:"Ȃ";s:3:"Ȃ";s:2:"ȃ";s:3:"ȃ";s:2:"Ȅ";s:3:"Ȅ";s:2:"ȅ";s:3:"ȅ";s:2:"Ȇ";s:3:"Ȇ";s:2:"ȇ";s:3:"ȇ";s:2:"Ȉ";s:3:"Ȉ";s:2:"ȉ";s:3:"ȉ";s:2:"Ȋ";s:3:"Ȋ";s:2:"ȋ";s:3:"ȋ";s:2:"Ȍ";s:3:"Ȍ";s:2:"ȍ";s:3:"ȍ";s:2:"Ȏ";s:3:"Ȏ";s:2:"ȏ";s:3:"ȏ";s:2:"Ȑ";s:3:"Ȑ";s:2:"ȑ";s:3:"ȑ";s:2:"Ȓ";s:3:"Ȓ";s:2:"ȓ";s:3:"ȓ";s:2:"Ȕ";s:3:"Ȕ";s:2:"ȕ";s:3:"ȕ";s:2:"Ȗ";s:3:"Ȗ";s:2:"ȗ";s:3:"ȗ";s:2:"Ș";s:3:"Ș";s:2:"ș";s:3:"ș";s:2:"Ț";s:3:"Ț";s:2:"ț";s:3:"ț";s:2:"Ȟ";s:3:"Ȟ";s:2:"ȟ";s:3:"ȟ";s:2:"Ȧ";s:3:"Ȧ";s:2:"ȧ";s:3:"ȧ";s:2:"Ȩ";s:3:"Ȩ";s:2:"ȩ";s:3:"ȩ";s:2:"Ȫ";s:5:"Ȫ";s:2:"ȫ";s:5:"ȫ";s:2:"Ȭ";s:5:"Ȭ";s:2:"ȭ";s:5:"ȭ";s:2:"Ȯ";s:3:"Ȯ";s:2:"ȯ";s:3:"ȯ";s:2:"Ȱ";s:5:"Ȱ";s:2:"ȱ";s:5:"ȱ";s:2:"Ȳ";s:3:"Ȳ";s:2:"ȳ";s:3:"ȳ";s:2:"ʰ";s:1:"h";s:2:"ʱ";s:2:"ɦ";s:2:"ʲ";s:1:"j";s:2:"ʳ";s:1:"r";s:2:"ʴ";s:2:"ɹ";s:2:"ʵ";s:2:"ɻ";s:2:"ʶ";s:2:"ʁ";s:2:"ʷ";s:1:"w";s:2:"ʸ";s:1:"y";s:2:"˘";s:3:" ̆";s:2:"˙";s:3:" ̇";s:2:"˚";s:3:" ̊";s:2:"˛";s:3:" ̨";s:2:"˜";s:3:" ̃";s:2:"˝";s:3:" ̋";s:2:"ˠ";s:2:"ɣ";s:2:"ˡ";s:1:"l";s:2:"ˢ";s:1:"s";s:2:"ˣ";s:1:"x";s:2:"ˤ";s:2:"ʕ";s:2:"̀";s:2:"̀";s:2:"́";s:2:"́";s:2:"̓";s:2:"̓";s:2:"̈́";s:4:"̈́";s:2:"ʹ";s:2:"ʹ";s:2:"ͺ";s:3:" ͅ";s:2:";";s:1:";";s:2:"΄";s:3:" ́";s:2:"΅";s:5:" ̈́";s:2:"Ά";s:4:"Ά";s:2:"·";s:2:"·";s:2:"Έ";s:4:"Έ";s:2:"Ή";s:4:"Ή";s:2:"Ί";s:4:"Ί";s:2:"Ό";s:4:"Ό";s:2:"Ύ";s:4:"Ύ";s:2:"Ώ";s:4:"Ώ";s:2:"ΐ";s:6:"ΐ";s:2:"Ϊ";s:4:"Ϊ";s:2:"Ϋ";s:4:"Ϋ";s:2:"ά";s:4:"ά";s:2:"έ";s:4:"έ";s:2:"ή";s:4:"ή";s:2:"ί";s:4:"ί";s:2:"ΰ";s:6:"ΰ";s:2:"ϊ";s:4:"ϊ";s:2:"ϋ";s:4:"ϋ";s:2:"ό";s:4:"ό";s:2:"ύ";s:4:"ύ";s:2:"ώ";s:4:"ώ";s:2:"ϐ";s:2:"β";s:2:"ϑ";s:2:"θ";s:2:"ϒ";s:2:"Υ";s:2:"ϓ";s:4:"Ύ";s:2:"ϔ";s:4:"Ϋ";s:2:"ϕ";s:2:"φ";s:2:"ϖ";s:2:"π";s:2:"ϰ";s:2:"κ";s:2:"ϱ";s:2:"ρ";s:2:"ϲ";s:2:"ς";s:2:"ϴ";s:2:"Θ";s:2:"ϵ";s:2:"ε";s:2:"Ϲ";s:2:"Σ";s:2:"Ѐ";s:4:"Ѐ";s:2:"Ё";s:4:"Ё";s:2:"Ѓ";s:4:"Ѓ";s:2:"Ї";s:4:"Ї";s:2:"Ќ";s:4:"Ќ";s:2:"Ѝ";s:4:"Ѝ";s:2:"Ў";s:4:"Ў";s:2:"Й";s:4:"Й";s:2:"й";s:4:"й";s:2:"ѐ";s:4:"ѐ";s:2:"ё";s:4:"ё";s:2:"ѓ";s:4:"ѓ";s:2:"ї";s:4:"ї";s:2:"ќ";s:4:"ќ";s:2:"ѝ";s:4:"ѝ";s:2:"ў";s:4:"ў";s:2:"Ѷ";s:4:"Ѷ";s:2:"ѷ";s:4:"ѷ";s:2:"Ӂ";s:4:"Ӂ";s:2:"ӂ";s:4:"ӂ";s:2:"Ӑ";s:4:"Ӑ";s:2:"ӑ";s:4:"ӑ";s:2:"Ӓ";s:4:"Ӓ";s:2:"ӓ";s:4:"ӓ";s:2:"Ӗ";s:4:"Ӗ";s:2:"ӗ";s:4:"ӗ";s:2:"Ӛ";s:4:"Ӛ";s:2:"ӛ";s:4:"ӛ";s:2:"Ӝ";s:4:"Ӝ";s:2:"ӝ";s:4:"ӝ";s:2:"Ӟ";s:4:"Ӟ";s:2:"ӟ";s:4:"ӟ";s:2:"Ӣ";s:4:"Ӣ";s:2:"ӣ";s:4:"ӣ";s:2:"Ӥ";s:4:"Ӥ";s:2:"ӥ";s:4:"ӥ";s:2:"Ӧ";s:4:"Ӧ";s:2:"ӧ";s:4:"ӧ";s:2:"Ӫ";s:4:"Ӫ";s:2:"ӫ";s:4:"ӫ";s:2:"Ӭ";s:4:"Ӭ";s:2:"ӭ";s:4:"ӭ";s:2:"Ӯ";s:4:"Ӯ";s:2:"ӯ";s:4:"ӯ";s:2:"Ӱ";s:4:"Ӱ";s:2:"ӱ";s:4:"ӱ";s:2:"Ӳ";s:4:"Ӳ";s:2:"ӳ";s:4:"ӳ";s:2:"Ӵ";s:4:"Ӵ";s:2:"ӵ";s:4:"ӵ";s:2:"Ӹ";s:4:"Ӹ";s:2:"ӹ";s:4:"ӹ";s:2:"և";s:4:"եւ";s:2:"آ";s:4:"آ";s:2:"أ";s:4:"أ";s:2:"ؤ";s:4:"ؤ";s:2:"إ";s:4:"إ";s:2:"ئ";s:4:"ئ";s:2:"ٵ";s:4:"اٴ";s:2:"ٶ";s:4:"وٴ";s:2:"ٷ";s:4:"ۇٴ";s:2:"ٸ";s:4:"يٴ";s:2:"ۀ";s:4:"ۀ";s:2:"ۂ";s:4:"ۂ";s:2:"ۓ";s:4:"ۓ";s:3:"ऩ";s:6:"ऩ";s:3:"ऱ";s:6:"ऱ";s:3:"ऴ";s:6:"ऴ";s:3:"क़";s:6:"क़";s:3:"ख़";s:6:"ख़";s:3:"ग़";s:6:"ग़";s:3:"ज़";s:6:"ज़";s:3:"ड़";s:6:"ड़";s:3:"ढ़";s:6:"ढ़";s:3:"फ़";s:6:"फ़";s:3:"य़";s:6:"य़";s:3:"ো";s:6:"ো";s:3:"ৌ";s:6:"ৌ";s:3:"ড়";s:6:"ড়";s:3:"ঢ়";s:6:"ঢ়";s:3:"য়";s:6:"য়";s:3:"ਲ਼";s:6:"ਲ਼";s:3:"ਸ਼";s:6:"ਸ਼";s:3:"ਖ਼";s:6:"ਖ਼";s:3:"ਗ਼";s:6:"ਗ਼";s:3:"ਜ਼";s:6:"ਜ਼";s:3:"ਫ਼";s:6:"ਫ਼";s:3:"ୈ";s:6:"ୈ";s:3:"ୋ";s:6:"ୋ";s:3:"ୌ";s:6:"ୌ";s:3:"ଡ଼";s:6:"ଡ଼";s:3:"ଢ଼";s:6:"ଢ଼";s:3:"ஔ";s:6:"ஔ";s:3:"ொ";s:6:"ொ";s:3:"ோ";s:6:"ோ";s:3:"ௌ";s:6:"ௌ";s:3:"ై";s:6:"ై";s:3:"ೀ";s:6:"ೀ";s:3:"ೇ";s:6:"ೇ";s:3:"ೈ";s:6:"ೈ";s:3:"ೊ";s:6:"ೊ";s:3:"ೋ";s:9:"ೋ";s:3:"ൊ";s:6:"ൊ";s:3:"ോ";s:6:"ോ";s:3:"ൌ";s:6:"ൌ";s:3:"ේ";s:6:"ේ";s:3:"ො";s:6:"ො";s:3:"ෝ";s:9:"ෝ";s:3:"ෞ";s:6:"ෞ";s:3:"ำ";s:6:"ํา";s:3:"ຳ";s:6:"ໍາ";s:3:"ໜ";s:6:"ຫນ";s:3:"ໝ";s:6:"ຫມ";s:3:"༌";s:3:"་";s:3:"གྷ";s:6:"གྷ";s:3:"ཌྷ";s:6:"ཌྷ";s:3:"དྷ";s:6:"དྷ";s:3:"བྷ";s:6:"བྷ";s:3:"ཛྷ";s:6:"ཛྷ";s:3:"ཀྵ";s:6:"ཀྵ";s:3:"ཱི";s:6:"ཱི";s:3:"ཱུ";s:6:"ཱུ";s:3:"ྲྀ";s:6:"ྲྀ";s:3:"ཷ";s:9:"ྲཱྀ";s:3:"ླྀ";s:6:"ླྀ";s:3:"ཹ";s:9:"ླཱྀ";s:3:"ཱྀ";s:6:"ཱྀ";s:3:"ྒྷ";s:6:"ྒྷ";s:3:"ྜྷ";s:6:"ྜྷ";s:3:"ྡྷ";s:6:"ྡྷ";s:3:"ྦྷ";s:6:"ྦྷ";s:3:"ྫྷ";s:6:"ྫྷ";s:3:"ྐྵ";s:6:"ྐྵ";s:3:"ဦ";s:6:"ဦ";s:3:"ჼ";s:3:"ნ";s:3:"ᬆ";s:6:"ᬆ";s:3:"ᬈ";s:6:"ᬈ";s:3:"ᬊ";s:6:"ᬊ";s:3:"ᬌ";s:6:"ᬌ";s:3:"ᬎ";s:6:"ᬎ";s:3:"ᬒ";s:6:"ᬒ";s:3:"ᬻ";s:6:"ᬻ";s:3:"ᬽ";s:6:"ᬽ";s:3:"ᭀ";s:6:"ᭀ";s:3:"ᭁ";s:6:"ᭁ";s:3:"ᭃ";s:6:"ᭃ";s:3:"ᴬ";s:1:"A";s:3:"ᴭ";s:2:"Æ";s:3:"ᴮ";s:1:"B";s:3:"ᴰ";s:1:"D";s:3:"ᴱ";s:1:"E";s:3:"ᴲ";s:2:"Ǝ";s:3:"ᴳ";s:1:"G";s:3:"ᴴ";s:1:"H";s:3:"ᴵ";s:1:"I";s:3:"ᴶ";s:1:"J";s:3:"ᴷ";s:1:"K";s:3:"ᴸ";s:1:"L";s:3:"ᴹ";s:1:"M";s:3:"ᴺ";s:1:"N";s:3:"ᴼ";s:1:"O";s:3:"ᴽ";s:2:"Ȣ";s:3:"ᴾ";s:1:"P";s:3:"ᴿ";s:1:"R";s:3:"ᵀ";s:1:"T";s:3:"ᵁ";s:1:"U";s:3:"ᵂ";s:1:"W";s:3:"ᵃ";s:1:"a";s:3:"ᵄ";s:2:"ɐ";s:3:"ᵅ";s:2:"ɑ";s:3:"ᵆ";s:3:"ᴂ";s:3:"ᵇ";s:1:"b";s:3:"ᵈ";s:1:"d";s:3:"ᵉ";s:1:"e";s:3:"ᵊ";s:2:"ə";s:3:"ᵋ";s:2:"ɛ";s:3:"ᵌ";s:2:"ɜ";s:3:"ᵍ";s:1:"g";s:3:"ᵏ";s:1:"k";s:3:"ᵐ";s:1:"m";s:3:"ᵑ";s:2:"ŋ";s:3:"ᵒ";s:1:"o";s:3:"ᵓ";s:2:"ɔ";s:3:"ᵔ";s:3:"ᴖ";s:3:"ᵕ";s:3:"ᴗ";s:3:"ᵖ";s:1:"p";s:3:"ᵗ";s:1:"t";s:3:"ᵘ";s:1:"u";s:3:"ᵙ";s:3:"ᴝ";s:3:"ᵚ";s:2:"ɯ";s:3:"ᵛ";s:1:"v";s:3:"ᵜ";s:3:"ᴥ";s:3:"ᵝ";s:2:"β";s:3:"ᵞ";s:2:"γ";s:3:"ᵟ";s:2:"δ";s:3:"ᵠ";s:2:"φ";s:3:"ᵡ";s:2:"χ";s:3:"ᵢ";s:1:"i";s:3:"ᵣ";s:1:"r";s:3:"ᵤ";s:1:"u";s:3:"ᵥ";s:1:"v";s:3:"ᵦ";s:2:"β";s:3:"ᵧ";s:2:"γ";s:3:"ᵨ";s:2:"ρ";s:3:"ᵩ";s:2:"φ";s:3:"ᵪ";s:2:"χ";s:3:"ᵸ";s:2:"н";s:3:"ᶛ";s:2:"ɒ";s:3:"ᶜ";s:1:"c";s:3:"ᶝ";s:2:"ɕ";s:3:"ᶞ";s:2:"ð";s:3:"ᶟ";s:2:"ɜ";s:3:"ᶠ";s:1:"f";s:3:"ᶡ";s:2:"ɟ";s:3:"ᶢ";s:2:"ɡ";s:3:"ᶣ";s:2:"ɥ";s:3:"ᶤ";s:2:"ɨ";s:3:"ᶥ";s:2:"ɩ";s:3:"ᶦ";s:2:"ɪ";s:3:"ᶧ";s:3:"ᵻ";s:3:"ᶨ";s:2:"ʝ";s:3:"ᶩ";s:2:"ɭ";s:3:"ᶪ";s:3:"ᶅ";s:3:"ᶫ";s:2:"ʟ";s:3:"ᶬ";s:2:"ɱ";s:3:"ᶭ";s:2:"ɰ";s:3:"ᶮ";s:2:"ɲ";s:3:"ᶯ";s:2:"ɳ";s:3:"ᶰ";s:2:"ɴ";s:3:"ᶱ";s:2:"ɵ";s:3:"ᶲ";s:2:"ɸ";s:3:"ᶳ";s:2:"ʂ";s:3:"ᶴ";s:2:"ʃ";s:3:"ᶵ";s:2:"ƫ";s:3:"ᶶ";s:2:"ʉ";s:3:"ᶷ";s:2:"ʊ";s:3:"ᶸ";s:3:"ᴜ";s:3:"ᶹ";s:2:"ʋ";s:3:"ᶺ";s:2:"ʌ";s:3:"ᶻ";s:1:"z";s:3:"ᶼ";s:2:"ʐ";s:3:"ᶽ";s:2:"ʑ";s:3:"ᶾ";s:2:"ʒ";s:3:"ᶿ";s:2:"θ";s:3:"Ḁ";s:3:"Ḁ";s:3:"ḁ";s:3:"ḁ";s:3:"Ḃ";s:3:"Ḃ";s:3:"ḃ";s:3:"ḃ";s:3:"Ḅ";s:3:"Ḅ";s:3:"ḅ";s:3:"ḅ";s:3:"Ḇ";s:3:"Ḇ";s:3:"ḇ";s:3:"ḇ";s:3:"Ḉ";s:5:"Ḉ";s:3:"ḉ";s:5:"ḉ";s:3:"Ḋ";s:3:"Ḋ";s:3:"ḋ";s:3:"ḋ";s:3:"Ḍ";s:3:"Ḍ";s:3:"ḍ";s:3:"ḍ";s:3:"Ḏ";s:3:"Ḏ";s:3:"ḏ";s:3:"ḏ";s:3:"Ḑ";s:3:"Ḑ";s:3:"ḑ";s:3:"ḑ";s:3:"Ḓ";s:3:"Ḓ";s:3:"ḓ";s:3:"ḓ";s:3:"Ḕ";s:5:"Ḕ";s:3:"ḕ";s:5:"ḕ";s:3:"Ḗ";s:5:"Ḗ";s:3:"ḗ";s:5:"ḗ";s:3:"Ḙ";s:3:"Ḙ";s:3:"ḙ";s:3:"ḙ";s:3:"Ḛ";s:3:"Ḛ";s:3:"ḛ";s:3:"ḛ";s:3:"Ḝ";s:5:"Ḝ";s:3:"ḝ";s:5:"ḝ";s:3:"Ḟ";s:3:"Ḟ";s:3:"ḟ";s:3:"ḟ";s:3:"Ḡ";s:3:"Ḡ";s:3:"ḡ";s:3:"ḡ";s:3:"Ḣ";s:3:"Ḣ";s:3:"ḣ";s:3:"ḣ";s:3:"Ḥ";s:3:"Ḥ";s:3:"ḥ";s:3:"ḥ";s:3:"Ḧ";s:3:"Ḧ";s:3:"ḧ";s:3:"ḧ";s:3:"Ḩ";s:3:"Ḩ";s:3:"ḩ";s:3:"ḩ";s:3:"Ḫ";s:3:"Ḫ";s:3:"ḫ";s:3:"ḫ";s:3:"Ḭ";s:3:"Ḭ";s:3:"ḭ";s:3:"ḭ";s:3:"Ḯ";s:5:"Ḯ";s:3:"ḯ";s:5:"ḯ";s:3:"Ḱ";s:3:"Ḱ";s:3:"ḱ";s:3:"ḱ";s:3:"Ḳ";s:3:"Ḳ";s:3:"ḳ";s:3:"ḳ";s:3:"Ḵ";s:3:"Ḵ";s:3:"ḵ";s:3:"ḵ";s:3:"Ḷ";s:3:"Ḷ";s:3:"ḷ";s:3:"ḷ";s:3:"Ḹ";s:5:"Ḹ";s:3:"ḹ";s:5:"ḹ";s:3:"Ḻ";s:3:"Ḻ";s:3:"ḻ";s:3:"ḻ";s:3:"Ḽ";s:3:"Ḽ";s:3:"ḽ";s:3:"ḽ";s:3:"Ḿ";s:3:"Ḿ";s:3:"ḿ";s:3:"ḿ";s:3:"Ṁ";s:3:"Ṁ";s:3:"ṁ";s:3:"ṁ";s:3:"Ṃ";s:3:"Ṃ";s:3:"ṃ";s:3:"ṃ";s:3:"Ṅ";s:3:"Ṅ";s:3:"ṅ";s:3:"ṅ";s:3:"Ṇ";s:3:"Ṇ";s:3:"ṇ";s:3:"ṇ";s:3:"Ṉ";s:3:"Ṉ";s:3:"ṉ";s:3:"ṉ";s:3:"Ṋ";s:3:"Ṋ";s:3:"ṋ";s:3:"ṋ";s:3:"Ṍ";s:5:"Ṍ";s:3:"ṍ";s:5:"ṍ";s:3:"Ṏ";s:5:"Ṏ";s:3:"ṏ";s:5:"ṏ";s:3:"Ṑ";s:5:"Ṑ";s:3:"ṑ";s:5:"ṑ";s:3:"Ṓ";s:5:"Ṓ";s:3:"ṓ";s:5:"ṓ";s:3:"Ṕ";s:3:"Ṕ";s:3:"ṕ";s:3:"ṕ";s:3:"Ṗ";s:3:"Ṗ";s:3:"ṗ";s:3:"ṗ";s:3:"Ṙ";s:3:"Ṙ";s:3:"ṙ";s:3:"ṙ";s:3:"Ṛ";s:3:"Ṛ";s:3:"ṛ";s:3:"ṛ";s:3:"Ṝ";s:5:"Ṝ";s:3:"ṝ";s:5:"ṝ";s:3:"Ṟ";s:3:"Ṟ";s:3:"ṟ";s:3:"ṟ";s:3:"Ṡ";s:3:"Ṡ";s:3:"ṡ";s:3:"ṡ";s:3:"Ṣ";s:3:"Ṣ";s:3:"ṣ";s:3:"ṣ";s:3:"Ṥ";s:5:"Ṥ";s:3:"ṥ";s:5:"ṥ";s:3:"Ṧ";s:5:"Ṧ";s:3:"ṧ";s:5:"ṧ";s:3:"Ṩ";s:5:"Ṩ";s:3:"ṩ";s:5:"ṩ";s:3:"Ṫ";s:3:"Ṫ";s:3:"ṫ";s:3:"ṫ";s:3:"Ṭ";s:3:"Ṭ";s:3:"ṭ";s:3:"ṭ";s:3:"Ṯ";s:3:"Ṯ";s:3:"ṯ";s:3:"ṯ";s:3:"Ṱ";s:3:"Ṱ";s:3:"ṱ";s:3:"ṱ";s:3:"Ṳ";s:3:"Ṳ";s:3:"ṳ";s:3:"ṳ";s:3:"Ṵ";s:3:"Ṵ";s:3:"ṵ";s:3:"ṵ";s:3:"Ṷ";s:3:"Ṷ";s:3:"ṷ";s:3:"ṷ";s:3:"Ṹ";s:5:"Ṹ";s:3:"ṹ";s:5:"ṹ";s:3:"Ṻ";s:5:"Ṻ";s:3:"ṻ";s:5:"ṻ";s:3:"Ṽ";s:3:"Ṽ";s:3:"ṽ";s:3:"ṽ";s:3:"Ṿ";s:3:"Ṿ";s:3:"ṿ";s:3:"ṿ";s:3:"Ẁ";s:3:"Ẁ";s:3:"ẁ";s:3:"ẁ";s:3:"Ẃ";s:3:"Ẃ";s:3:"ẃ";s:3:"ẃ";s:3:"Ẅ";s:3:"Ẅ";s:3:"ẅ";s:3:"ẅ";s:3:"Ẇ";s:3:"Ẇ";s:3:"ẇ";s:3:"ẇ";s:3:"Ẉ";s:3:"Ẉ";s:3:"ẉ";s:3:"ẉ";s:3:"Ẋ";s:3:"Ẋ";s:3:"ẋ";s:3:"ẋ";s:3:"Ẍ";s:3:"Ẍ";s:3:"ẍ";s:3:"ẍ";s:3:"Ẏ";s:3:"Ẏ";s:3:"ẏ";s:3:"ẏ";s:3:"Ẑ";s:3:"Ẑ";s:3:"ẑ";s:3:"ẑ";s:3:"Ẓ";s:3:"Ẓ";s:3:"ẓ";s:3:"ẓ";s:3:"Ẕ";s:3:"Ẕ";s:3:"ẕ";s:3:"ẕ";s:3:"ẖ";s:3:"ẖ";s:3:"ẗ";s:3:"ẗ";s:3:"ẘ";s:3:"ẘ";s:3:"ẙ";s:3:"ẙ";s:3:"ẚ";s:3:"aʾ";s:3:"ẛ";s:3:"ṡ";s:3:"Ạ";s:3:"Ạ";s:3:"ạ";s:3:"ạ";s:3:"Ả";s:3:"Ả";s:3:"ả";s:3:"ả";s:3:"Ấ";s:5:"Ấ";s:3:"ấ";s:5:"ấ";s:3:"Ầ";s:5:"Ầ";s:3:"ầ";s:5:"ầ";s:3:"Ẩ";s:5:"Ẩ";s:3:"ẩ";s:5:"ẩ";s:3:"Ẫ";s:5:"Ẫ";s:3:"ẫ";s:5:"ẫ";s:3:"Ậ";s:5:"Ậ";s:3:"ậ";s:5:"ậ";s:3:"Ắ";s:5:"Ắ";s:3:"ắ";s:5:"ắ";s:3:"Ằ";s:5:"Ằ";s:3:"ằ";s:5:"ằ";s:3:"Ẳ";s:5:"Ẳ";s:3:"ẳ";s:5:"ẳ";s:3:"Ẵ";s:5:"Ẵ";s:3:"ẵ";s:5:"ẵ";s:3:"Ặ";s:5:"Ặ";s:3:"ặ";s:5:"ặ";s:3:"Ẹ";s:3:"Ẹ";s:3:"ẹ";s:3:"ẹ";s:3:"Ẻ";s:3:"Ẻ";s:3:"ẻ";s:3:"ẻ";s:3:"Ẽ";s:3:"Ẽ";s:3:"ẽ";s:3:"ẽ";s:3:"Ế";s:5:"Ế";s:3:"ế";s:5:"ế";s:3:"Ề";s:5:"Ề";s:3:"ề";s:5:"ề";s:3:"Ể";s:5:"Ể";s:3:"ể";s:5:"ể";s:3:"Ễ";s:5:"Ễ";s:3:"ễ";s:5:"ễ";s:3:"Ệ";s:5:"Ệ";s:3:"ệ";s:5:"ệ";s:3:"Ỉ";s:3:"Ỉ";s:3:"ỉ";s:3:"ỉ";s:3:"Ị";s:3:"Ị";s:3:"ị";s:3:"ị";s:3:"Ọ";s:3:"Ọ";s:3:"ọ";s:3:"ọ";s:3:"Ỏ";s:3:"Ỏ";s:3:"ỏ";s:3:"ỏ";s:3:"Ố";s:5:"Ố";s:3:"ố";s:5:"ố";s:3:"Ồ";s:5:"Ồ";s:3:"ồ";s:5:"ồ";s:3:"Ổ";s:5:"Ổ";s:3:"ổ";s:5:"ổ";s:3:"Ỗ";s:5:"Ỗ";s:3:"ỗ";s:5:"ỗ";s:3:"Ộ";s:5:"Ộ";s:3:"ộ";s:5:"ộ";s:3:"Ớ";s:5:"Ớ";s:3:"ớ";s:5:"ớ";s:3:"Ờ";s:5:"Ờ";s:3:"ờ";s:5:"ờ";s:3:"Ở";s:5:"Ở";s:3:"ở";s:5:"ở";s:3:"Ỡ";s:5:"Ỡ";s:3:"ỡ";s:5:"ỡ";s:3:"Ợ";s:5:"Ợ";s:3:"ợ";s:5:"ợ";s:3:"Ụ";s:3:"Ụ";s:3:"ụ";s:3:"ụ";s:3:"Ủ";s:3:"Ủ";s:3:"ủ";s:3:"ủ";s:3:"Ứ";s:5:"Ứ";s:3:"ứ";s:5:"ứ";s:3:"Ừ";s:5:"Ừ";s:3:"ừ";s:5:"ừ";s:3:"Ử";s:5:"Ử";s:3:"ử";s:5:"ử";s:3:"Ữ";s:5:"Ữ";s:3:"ữ";s:5:"ữ";s:3:"Ự";s:5:"Ự";s:3:"ự";s:5:"ự";s:3:"Ỳ";s:3:"Ỳ";s:3:"ỳ";s:3:"ỳ";s:3:"Ỵ";s:3:"Ỵ";s:3:"ỵ";s:3:"ỵ";s:3:"Ỷ";s:3:"Ỷ";s:3:"ỷ";s:3:"ỷ";s:3:"Ỹ";s:3:"Ỹ";s:3:"ỹ";s:3:"ỹ";s:3:"ἀ";s:4:"ἀ";s:3:"ἁ";s:4:"ἁ";s:3:"ἂ";s:6:"ἂ";s:3:"ἃ";s:6:"ἃ";s:3:"ἄ";s:6:"ἄ";s:3:"ἅ";s:6:"ἅ";s:3:"ἆ";s:6:"ἆ";s:3:"ἇ";s:6:"ἇ";s:3:"Ἀ";s:4:"Ἀ";s:3:"Ἁ";s:4:"Ἁ";s:3:"Ἂ";s:6:"Ἂ";s:3:"Ἃ";s:6:"Ἃ";s:3:"Ἄ";s:6:"Ἄ";s:3:"Ἅ";s:6:"Ἅ";s:3:"Ἆ";s:6:"Ἆ";s:3:"Ἇ";s:6:"Ἇ";s:3:"ἐ";s:4:"ἐ";s:3:"ἑ";s:4:"ἑ";s:3:"ἒ";s:6:"ἒ";s:3:"ἓ";s:6:"ἓ";s:3:"ἔ";s:6:"ἔ";s:3:"ἕ";s:6:"ἕ";s:3:"Ἐ";s:4:"Ἐ";s:3:"Ἑ";s:4:"Ἑ";s:3:"Ἒ";s:6:"Ἒ";s:3:"Ἓ";s:6:"Ἓ";s:3:"Ἔ";s:6:"Ἔ";s:3:"Ἕ";s:6:"Ἕ";s:3:"ἠ";s:4:"ἠ";s:3:"ἡ";s:4:"ἡ";s:3:"ἢ";s:6:"ἢ";s:3:"ἣ";s:6:"ἣ";s:3:"ἤ";s:6:"ἤ";s:3:"ἥ";s:6:"ἥ";s:3:"ἦ";s:6:"ἦ";s:3:"ἧ";s:6:"ἧ";s:3:"Ἠ";s:4:"Ἠ";s:3:"Ἡ";s:4:"Ἡ";s:3:"Ἢ";s:6:"Ἢ";s:3:"Ἣ";s:6:"Ἣ";s:3:"Ἤ";s:6:"Ἤ";s:3:"Ἥ";s:6:"Ἥ";s:3:"Ἦ";s:6:"Ἦ";s:3:"Ἧ";s:6:"Ἧ";s:3:"ἰ";s:4:"ἰ";s:3:"ἱ";s:4:"ἱ";s:3:"ἲ";s:6:"ἲ";s:3:"ἳ";s:6:"ἳ";s:3:"ἴ";s:6:"ἴ";s:3:"ἵ";s:6:"ἵ";s:3:"ἶ";s:6:"ἶ";s:3:"ἷ";s:6:"ἷ";s:3:"Ἰ";s:4:"Ἰ";s:3:"Ἱ";s:4:"Ἱ";s:3:"Ἲ";s:6:"Ἲ";s:3:"Ἳ";s:6:"Ἳ";s:3:"Ἴ";s:6:"Ἴ";s:3:"Ἵ";s:6:"Ἵ";s:3:"Ἶ";s:6:"Ἶ";s:3:"Ἷ";s:6:"Ἷ";s:3:"ὀ";s:4:"ὀ";s:3:"ὁ";s:4:"ὁ";s:3:"ὂ";s:6:"ὂ";s:3:"ὃ";s:6:"ὃ";s:3:"ὄ";s:6:"ὄ";s:3:"ὅ";s:6:"ὅ";s:3:"Ὀ";s:4:"Ὀ";s:3:"Ὁ";s:4:"Ὁ";s:3:"Ὂ";s:6:"Ὂ";s:3:"Ὃ";s:6:"Ὃ";s:3:"Ὄ";s:6:"Ὄ";s:3:"Ὅ";s:6:"Ὅ";s:3:"ὐ";s:4:"ὐ";s:3:"ὑ";s:4:"ὑ";s:3:"ὒ";s:6:"ὒ";s:3:"ὓ";s:6:"ὓ";s:3:"ὔ";s:6:"ὔ";s:3:"ὕ";s:6:"ὕ";s:3:"ὖ";s:6:"ὖ";s:3:"ὗ";s:6:"ὗ";s:3:"Ὑ";s:4:"Ὑ";s:3:"Ὓ";s:6:"Ὓ";s:3:"Ὕ";s:6:"Ὕ";s:3:"Ὗ";s:6:"Ὗ";s:3:"ὠ";s:4:"ὠ";s:3:"ὡ";s:4:"ὡ";s:3:"ὢ";s:6:"ὢ";s:3:"ὣ";s:6:"ὣ";s:3:"ὤ";s:6:"ὤ";s:3:"ὥ";s:6:"ὥ";s:3:"ὦ";s:6:"ὦ";s:3:"ὧ";s:6:"ὧ";s:3:"Ὠ";s:4:"Ὠ";s:3:"Ὡ";s:4:"Ὡ";s:3:"Ὢ";s:6:"Ὢ";s:3:"Ὣ";s:6:"Ὣ";s:3:"Ὤ";s:6:"Ὤ";s:3:"Ὥ";s:6:"Ὥ";s:3:"Ὦ";s:6:"Ὦ";s:3:"Ὧ";s:6:"Ὧ";s:3:"ὰ";s:4:"ὰ";s:3:"ά";s:4:"ά";s:3:"ὲ";s:4:"ὲ";s:3:"έ";s:4:"έ";s:3:"ὴ";s:4:"ὴ";s:3:"ή";s:4:"ή";s:3:"ὶ";s:4:"ὶ";s:3:"ί";s:4:"ί";s:3:"ὸ";s:4:"ὸ";s:3:"ό";s:4:"ό";s:3:"ὺ";s:4:"ὺ";s:3:"ύ";s:4:"ύ";s:3:"ὼ";s:4:"ὼ";s:3:"ώ";s:4:"ώ";s:3:"ᾀ";s:6:"ᾀ";s:3:"ᾁ";s:6:"ᾁ";s:3:"ᾂ";s:8:"ᾂ";s:3:"ᾃ";s:8:"ᾃ";s:3:"ᾄ";s:8:"ᾄ";s:3:"ᾅ";s:8:"ᾅ";s:3:"ᾆ";s:8:"ᾆ";s:3:"ᾇ";s:8:"ᾇ";s:3:"ᾈ";s:6:"ᾈ";s:3:"ᾉ";s:6:"ᾉ";s:3:"ᾊ";s:8:"ᾊ";s:3:"ᾋ";s:8:"ᾋ";s:3:"ᾌ";s:8:"ᾌ";s:3:"ᾍ";s:8:"ᾍ";s:3:"ᾎ";s:8:"ᾎ";s:3:"ᾏ";s:8:"ᾏ";s:3:"ᾐ";s:6:"ᾐ";s:3:"ᾑ";s:6:"ᾑ";s:3:"ᾒ";s:8:"ᾒ";s:3:"ᾓ";s:8:"ᾓ";s:3:"ᾔ";s:8:"ᾔ";s:3:"ᾕ";s:8:"ᾕ";s:3:"ᾖ";s:8:"ᾖ";s:3:"ᾗ";s:8:"ᾗ";s:3:"ᾘ";s:6:"ᾘ";s:3:"ᾙ";s:6:"ᾙ";s:3:"ᾚ";s:8:"ᾚ";s:3:"ᾛ";s:8:"ᾛ";s:3:"ᾜ";s:8:"ᾜ";s:3:"ᾝ";s:8:"ᾝ";s:3:"ᾞ";s:8:"ᾞ";s:3:"ᾟ";s:8:"ᾟ";s:3:"ᾠ";s:6:"ᾠ";s:3:"ᾡ";s:6:"ᾡ";s:3:"ᾢ";s:8:"ᾢ";s:3:"ᾣ";s:8:"ᾣ";s:3:"ᾤ";s:8:"ᾤ";s:3:"ᾥ";s:8:"ᾥ";s:3:"ᾦ";s:8:"ᾦ";s:3:"ᾧ";s:8:"ᾧ";s:3:"ᾨ";s:6:"ᾨ";s:3:"ᾩ";s:6:"ᾩ";s:3:"ᾪ";s:8:"ᾪ";s:3:"ᾫ";s:8:"ᾫ";s:3:"ᾬ";s:8:"ᾬ";s:3:"ᾭ";s:8:"ᾭ";s:3:"ᾮ";s:8:"ᾮ";s:3:"ᾯ";s:8:"ᾯ";s:3:"ᾰ";s:4:"ᾰ";s:3:"ᾱ";s:4:"ᾱ";s:3:"ᾲ";s:6:"ᾲ";s:3:"ᾳ";s:4:"ᾳ";s:3:"ᾴ";s:6:"ᾴ";s:3:"ᾶ";s:4:"ᾶ";s:3:"ᾷ";s:6:"ᾷ";s:3:"Ᾰ";s:4:"Ᾰ";s:3:"Ᾱ";s:4:"Ᾱ";s:3:"Ὰ";s:4:"Ὰ";s:3:"Ά";s:4:"Ά";s:3:"ᾼ";s:4:"ᾼ";s:3:"᾽";s:3:" ̓";s:3:"ι";s:2:"ι";s:3:"᾿";s:3:" ̓";s:3:"῀";s:3:" ͂";s:3:"῁";s:5:" ̈͂";s:3:"ῂ";s:6:"ῂ";s:3:"ῃ";s:4:"ῃ";s:3:"ῄ";s:6:"ῄ";s:3:"ῆ";s:4:"ῆ";s:3:"ῇ";s:6:"ῇ";s:3:"Ὲ";s:4:"Ὲ";s:3:"Έ";s:4:"Έ";s:3:"Ὴ";s:4:"Ὴ";s:3:"Ή";s:4:"Ή";s:3:"ῌ";s:4:"ῌ";s:3:"῍";s:5:" ̓̀";s:3:"῎";s:5:" ̓́";s:3:"῏";s:5:" ̓͂";s:3:"ῐ";s:4:"ῐ";s:3:"ῑ";s:4:"ῑ";s:3:"ῒ";s:6:"ῒ";s:3:"ΐ";s:6:"ΐ";s:3:"ῖ";s:4:"ῖ";s:3:"ῗ";s:6:"ῗ";s:3:"Ῐ";s:4:"Ῐ";s:3:"Ῑ";s:4:"Ῑ";s:3:"Ὶ";s:4:"Ὶ";s:3:"Ί";s:4:"Ί";s:3:"῝";s:5:" ̔̀";s:3:"῞";s:5:" ̔́";s:3:"῟";s:5:" ̔͂";s:3:"ῠ";s:4:"ῠ";s:3:"ῡ";s:4:"ῡ";s:3:"ῢ";s:6:"ῢ";s:3:"ΰ";s:6:"ΰ";s:3:"ῤ";s:4:"ῤ";s:3:"ῥ";s:4:"ῥ";s:3:"ῦ";s:4:"ῦ";s:3:"ῧ";s:6:"ῧ";s:3:"Ῠ";s:4:"Ῠ";s:3:"Ῡ";s:4:"Ῡ";s:3:"Ὺ";s:4:"Ὺ";s:3:"Ύ";s:4:"Ύ";s:3:"Ῥ";s:4:"Ῥ";s:3:"῭";s:5:" ̈̀";s:3:"΅";s:5:" ̈́";s:3:"`";s:1:"`";s:3:"ῲ";s:6:"ῲ";s:3:"ῳ";s:4:"ῳ";s:3:"ῴ";s:6:"ῴ";s:3:"ῶ";s:4:"ῶ";s:3:"ῷ";s:6:"ῷ";s:3:"Ὸ";s:4:"Ὸ";s:3:"Ό";s:4:"Ό";s:3:"Ὼ";s:4:"Ὼ";s:3:"Ώ";s:4:"Ώ";s:3:"ῼ";s:4:"ῼ";s:3:"´";s:3:" ́";s:3:"῾";s:3:" ̔";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:" ";s:1:" ";s:3:"‑";s:3:"‐";s:3:"‗";s:3:" ̳";s:3:"․";s:1:".";s:3:"‥";s:2:"..";s:3:"…";s:3:"...";s:3:" ";s:1:" ";s:3:"″";s:6:"′′";s:3:"‴";s:9:"′′′";s:3:"‶";s:6:"‵‵";s:3:"‷";s:9:"‵‵‵";s:3:"‼";s:2:"!!";s:3:"‾";s:3:" ̅";s:3:"⁇";s:2:"??";s:3:"⁈";s:2:"?!";s:3:"⁉";s:2:"!?";s:3:"⁗";s:12:"′′′′";s:3:" ";s:1:" ";s:3:"⁰";s:1:"0";s:3:"ⁱ";s:1:"i";s:3:"⁴";s:1:"4";s:3:"⁵";s:1:"5";s:3:"⁶";s:1:"6";s:3:"⁷";s:1:"7";s:3:"⁸";s:1:"8";s:3:"⁹";s:1:"9";s:3:"⁺";s:1:"+";s:3:"⁻";s:3:"−";s:3:"⁼";s:1:"=";s:3:"⁽";s:1:"(";s:3:"⁾";s:1:")";s:3:"ⁿ";s:1:"n";s:3:"₀";s:1:"0";s:3:"₁";s:1:"1";s:3:"₂";s:1:"2";s:3:"₃";s:1:"3";s:3:"₄";s:1:"4";s:3:"₅";s:1:"5";s:3:"₆";s:1:"6";s:3:"₇";s:1:"7";s:3:"₈";s:1:"8";s:3:"₉";s:1:"9";s:3:"₊";s:1:"+";s:3:"₋";s:3:"−";s:3:"₌";s:1:"=";s:3:"₍";s:1:"(";s:3:"₎";s:1:")";s:3:"ₐ";s:1:"a";s:3:"ₑ";s:1:"e";s:3:"ₒ";s:1:"o";s:3:"ₓ";s:1:"x";s:3:"ₔ";s:2:"ə";s:3:"₨";s:2:"Rs";s:3:"℀";s:3:"a/c";s:3:"℁";s:3:"a/s";s:3:"ℂ";s:1:"C";s:3:"℃";s:3:"°C";s:3:"℅";s:3:"c/o";s:3:"℆";s:3:"c/u";s:3:"ℇ";s:2:"Ɛ";s:3:"℉";s:3:"°F";s:3:"ℊ";s:1:"g";s:3:"ℋ";s:1:"H";s:3:"ℌ";s:1:"H";s:3:"ℍ";s:1:"H";s:3:"ℎ";s:1:"h";s:3:"ℏ";s:2:"ħ";s:3:"ℐ";s:1:"I";s:3:"ℑ";s:1:"I";s:3:"ℒ";s:1:"L";s:3:"ℓ";s:1:"l";s:3:"ℕ";s:1:"N";s:3:"№";s:2:"No";s:3:"ℙ";s:1:"P";s:3:"ℚ";s:1:"Q";s:3:"ℛ";s:1:"R";s:3:"ℜ";s:1:"R";s:3:"ℝ";s:1:"R";s:3:"℠";s:2:"SM";s:3:"℡";s:3:"TEL";s:3:"™";s:2:"TM";s:3:"ℤ";s:1:"Z";s:3:"Ω";s:2:"Ω";s:3:"ℨ";s:1:"Z";s:3:"K";s:1:"K";s:3:"Å";s:3:"Å";s:3:"ℬ";s:1:"B";s:3:"ℭ";s:1:"C";s:3:"ℯ";s:1:"e";s:3:"ℰ";s:1:"E";s:3:"ℱ";s:1:"F";s:3:"ℳ";s:1:"M";s:3:"ℴ";s:1:"o";s:3:"ℵ";s:2:"א";s:3:"ℶ";s:2:"ב";s:3:"ℷ";s:2:"ג";s:3:"ℸ";s:2:"ד";s:3:"ℹ";s:1:"i";s:3:"℻";s:3:"FAX";s:3:"ℼ";s:2:"π";s:3:"ℽ";s:2:"γ";s:3:"ℾ";s:2:"Γ";s:3:"ℿ";s:2:"Π";s:3:"⅀";s:3:"∑";s:3:"ⅅ";s:1:"D";s:3:"ⅆ";s:1:"d";s:3:"ⅇ";s:1:"e";s:3:"ⅈ";s:1:"i";s:3:"ⅉ";s:1:"j";s:3:"⅓";s:5:"1⁄3";s:3:"⅔";s:5:"2⁄3";s:3:"⅕";s:5:"1⁄5";s:3:"⅖";s:5:"2⁄5";s:3:"⅗";s:5:"3⁄5";s:3:"⅘";s:5:"4⁄5";s:3:"⅙";s:5:"1⁄6";s:3:"⅚";s:5:"5⁄6";s:3:"⅛";s:5:"1⁄8";s:3:"⅜";s:5:"3⁄8";s:3:"⅝";s:5:"5⁄8";s:3:"⅞";s:5:"7⁄8";s:3:"⅟";s:4:"1⁄";s:3:"Ⅰ";s:1:"I";s:3:"Ⅱ";s:2:"II";s:3:"Ⅲ";s:3:"III";s:3:"Ⅳ";s:2:"IV";s:3:"Ⅴ";s:1:"V";s:3:"Ⅵ";s:2:"VI";s:3:"Ⅶ";s:3:"VII";s:3:"Ⅷ";s:4:"VIII";s:3:"Ⅸ";s:2:"IX";s:3:"Ⅹ";s:1:"X";s:3:"Ⅺ";s:2:"XI";s:3:"Ⅻ";s:3:"XII";s:3:"Ⅼ";s:1:"L";s:3:"Ⅽ";s:1:"C";s:3:"Ⅾ";s:1:"D";s:3:"Ⅿ";s:1:"M";s:3:"ⅰ";s:1:"i";s:3:"ⅱ";s:2:"ii";s:3:"ⅲ";s:3:"iii";s:3:"ⅳ";s:2:"iv";s:3:"ⅴ";s:1:"v";s:3:"ⅵ";s:2:"vi";s:3:"ⅶ";s:3:"vii";s:3:"ⅷ";s:4:"viii";s:3:"ⅸ";s:2:"ix";s:3:"ⅹ";s:1:"x";s:3:"ⅺ";s:2:"xi";s:3:"ⅻ";s:3:"xii";s:3:"ⅼ";s:1:"l";s:3:"ⅽ";s:1:"c";s:3:"ⅾ";s:1:"d";s:3:"ⅿ";s:1:"m";s:3:"↚";s:5:"↚";s:3:"↛";s:5:"↛";s:3:"↮";s:5:"↮";s:3:"⇍";s:5:"⇍";s:3:"⇎";s:5:"⇎";s:3:"⇏";s:5:"⇏";s:3:"∄";s:5:"∄";s:3:"∉";s:5:"∉";s:3:"∌";s:5:"∌";s:3:"∤";s:5:"∤";s:3:"∦";s:5:"∦";s:3:"∬";s:6:"∫∫";s:3:"∭";s:9:"∫∫∫";s:3:"∯";s:6:"∮∮";s:3:"∰";s:9:"∮∮∮";s:3:"≁";s:5:"≁";s:3:"≄";s:5:"≄";s:3:"≇";s:5:"≇";s:3:"≉";s:5:"≉";s:3:"≠";s:3:"≠";s:3:"≢";s:5:"≢";s:3:"≭";s:5:"≭";s:3:"≮";s:3:"≮";s:3:"≯";s:3:"≯";s:3:"≰";s:5:"≰";s:3:"≱";s:5:"≱";s:3:"≴";s:5:"≴";s:3:"≵";s:5:"≵";s:3:"≸";s:5:"≸";s:3:"≹";s:5:"≹";s:3:"⊀";s:5:"⊀";s:3:"⊁";s:5:"⊁";s:3:"⊄";s:5:"⊄";s:3:"⊅";s:5:"⊅";s:3:"⊈";s:5:"⊈";s:3:"⊉";s:5:"⊉";s:3:"⊬";s:5:"⊬";s:3:"⊭";s:5:"⊭";s:3:"⊮";s:5:"⊮";s:3:"⊯";s:5:"⊯";s:3:"⋠";s:5:"⋠";s:3:"⋡";s:5:"⋡";s:3:"⋢";s:5:"⋢";s:3:"⋣";s:5:"⋣";s:3:"⋪";s:5:"⋪";s:3:"⋫";s:5:"⋫";s:3:"⋬";s:5:"⋬";s:3:"⋭";s:5:"⋭";s:3:"〈";s:3:"〈";s:3:"〉";s:3:"〉";s:3:"①";s:1:"1";s:3:"②";s:1:"2";s:3:"③";s:1:"3";s:3:"④";s:1:"4";s:3:"⑤";s:1:"5";s:3:"⑥";s:1:"6";s:3:"⑦";s:1:"7";s:3:"⑧";s:1:"8";s:3:"⑨";s:1:"9";s:3:"⑩";s:2:"10";s:3:"⑪";s:2:"11";s:3:"⑫";s:2:"12";s:3:"⑬";s:2:"13";s:3:"⑭";s:2:"14";s:3:"⑮";s:2:"15";s:3:"⑯";s:2:"16";s:3:"⑰";s:2:"17";s:3:"⑱";s:2:"18";s:3:"⑲";s:2:"19";s:3:"⑳";s:2:"20";s:3:"⑴";s:3:"(1)";s:3:"⑵";s:3:"(2)";s:3:"⑶";s:3:"(3)";s:3:"⑷";s:3:"(4)";s:3:"⑸";s:3:"(5)";s:3:"⑹";s:3:"(6)";s:3:"⑺";s:3:"(7)";s:3:"⑻";s:3:"(8)";s:3:"⑼";s:3:"(9)";s:3:"⑽";s:4:"(10)";s:3:"⑾";s:4:"(11)";s:3:"⑿";s:4:"(12)";s:3:"⒀";s:4:"(13)";s:3:"⒁";s:4:"(14)";s:3:"⒂";s:4:"(15)";s:3:"⒃";s:4:"(16)";s:3:"⒄";s:4:"(17)";s:3:"⒅";s:4:"(18)";s:3:"⒆";s:4:"(19)";s:3:"⒇";s:4:"(20)";s:3:"⒈";s:2:"1.";s:3:"⒉";s:2:"2.";s:3:"⒊";s:2:"3.";s:3:"⒋";s:2:"4.";s:3:"⒌";s:2:"5.";s:3:"⒍";s:2:"6.";s:3:"⒎";s:2:"7.";s:3:"⒏";s:2:"8.";s:3:"⒐";s:2:"9.";s:3:"⒑";s:3:"10.";s:3:"⒒";s:3:"11.";s:3:"⒓";s:3:"12.";s:3:"⒔";s:3:"13.";s:3:"⒕";s:3:"14.";s:3:"⒖";s:3:"15.";s:3:"⒗";s:3:"16.";s:3:"⒘";s:3:"17.";s:3:"⒙";s:3:"18.";s:3:"⒚";s:3:"19.";s:3:"⒛";s:3:"20.";s:3:"⒜";s:3:"(a)";s:3:"⒝";s:3:"(b)";s:3:"⒞";s:3:"(c)";s:3:"⒟";s:3:"(d)";s:3:"⒠";s:3:"(e)";s:3:"⒡";s:3:"(f)";s:3:"⒢";s:3:"(g)";s:3:"⒣";s:3:"(h)";s:3:"⒤";s:3:"(i)";s:3:"⒥";s:3:"(j)";s:3:"⒦";s:3:"(k)";s:3:"⒧";s:3:"(l)";s:3:"⒨";s:3:"(m)";s:3:"⒩";s:3:"(n)";s:3:"⒪";s:3:"(o)";s:3:"⒫";s:3:"(p)";s:3:"⒬";s:3:"(q)";s:3:"⒭";s:3:"(r)";s:3:"⒮";s:3:"(s)";s:3:"⒯";s:3:"(t)";s:3:"⒰";s:3:"(u)";s:3:"⒱";s:3:"(v)";s:3:"⒲";s:3:"(w)";s:3:"⒳";s:3:"(x)";s:3:"⒴";s:3:"(y)";s:3:"⒵";s:3:"(z)";s:3:"Ⓐ";s:1:"A";s:3:"Ⓑ";s:1:"B";s:3:"Ⓒ";s:1:"C";s:3:"Ⓓ";s:1:"D";s:3:"Ⓔ";s:1:"E";s:3:"Ⓕ";s:1:"F";s:3:"Ⓖ";s:1:"G";s:3:"Ⓗ";s:1:"H";s:3:"Ⓘ";s:1:"I";s:3:"Ⓙ";s:1:"J";s:3:"Ⓚ";s:1:"K";s:3:"Ⓛ";s:1:"L";s:3:"Ⓜ";s:1:"M";s:3:"Ⓝ";s:1:"N";s:3:"Ⓞ";s:1:"O";s:3:"Ⓟ";s:1:"P";s:3:"Ⓠ";s:1:"Q";s:3:"Ⓡ";s:1:"R";s:3:"Ⓢ";s:1:"S";s:3:"Ⓣ";s:1:"T";s:3:"Ⓤ";s:1:"U";s:3:"Ⓥ";s:1:"V";s:3:"Ⓦ";s:1:"W";s:3:"Ⓧ";s:1:"X";s:3:"Ⓨ";s:1:"Y";s:3:"Ⓩ";s:1:"Z";s:3:"ⓐ";s:1:"a";s:3:"ⓑ";s:1:"b";s:3:"ⓒ";s:1:"c";s:3:"ⓓ";s:1:"d";s:3:"ⓔ";s:1:"e";s:3:"ⓕ";s:1:"f";s:3:"ⓖ";s:1:"g";s:3:"ⓗ";s:1:"h";s:3:"ⓘ";s:1:"i";s:3:"ⓙ";s:1:"j";s:3:"ⓚ";s:1:"k";s:3:"ⓛ";s:1:"l";s:3:"ⓜ";s:1:"m";s:3:"ⓝ";s:1:"n";s:3:"ⓞ";s:1:"o";s:3:"ⓟ";s:1:"p";s:3:"ⓠ";s:1:"q";s:3:"ⓡ";s:1:"r";s:3:"ⓢ";s:1:"s";s:3:"ⓣ";s:1:"t";s:3:"ⓤ";s:1:"u";s:3:"ⓥ";s:1:"v";s:3:"ⓦ";s:1:"w";s:3:"ⓧ";s:1:"x";s:3:"ⓨ";s:1:"y";s:3:"ⓩ";s:1:"z";s:3:"⓪";s:1:"0";s:3:"⨌";s:12:"∫∫∫∫";s:3:"⩴";s:3:"::=";s:3:"⩵";s:2:"==";s:3:"⩶";s:3:"===";s:3:"⫝̸";s:5:"⫝̸";s:3:"ⵯ";s:3:"ⵡ";s:3:"⺟";s:3:"母";s:3:"⻳";s:3:"龟";s:3:"⼀";s:3:"一";s:3:"⼁";s:3:"丨";s:3:"⼂";s:3:"丶";s:3:"⼃";s:3:"丿";s:3:"⼄";s:3:"乙";s:3:"⼅";s:3:"亅";s:3:"⼆";s:3:"二";s:3:"⼇";s:3:"亠";s:3:"⼈";s:3:"人";s:3:"⼉";s:3:"儿";s:3:"⼊";s:3:"入";s:3:"⼋";s:3:"八";s:3:"⼌";s:3:"冂";s:3:"⼍";s:3:"冖";s:3:"⼎";s:3:"冫";s:3:"⼏";s:3:"几";s:3:"⼐";s:3:"凵";s:3:"⼑";s:3:"刀";s:3:"⼒";s:3:"力";s:3:"⼓";s:3:"勹";s:3:"⼔";s:3:"匕";s:3:"⼕";s:3:"匚";s:3:"⼖";s:3:"匸";s:3:"⼗";s:3:"十";s:3:"⼘";s:3:"卜";s:3:"⼙";s:3:"卩";s:3:"⼚";s:3:"厂";s:3:"⼛";s:3:"厶";s:3:"⼜";s:3:"又";s:3:"⼝";s:3:"口";s:3:"⼞";s:3:"囗";s:3:"⼟";s:3:"土";s:3:"⼠";s:3:"士";s:3:"⼡";s:3:"夂";s:3:"⼢";s:3:"夊";s:3:"⼣";s:3:"夕";s:3:"⼤";s:3:"大";s:3:"⼥";s:3:"女";s:3:"⼦";s:3:"子";s:3:"⼧";s:3:"宀";s:3:"⼨";s:3:"寸";s:3:"⼩";s:3:"小";s:3:"⼪";s:3:"尢";s:3:"⼫";s:3:"尸";s:3:"⼬";s:3:"屮";s:3:"⼭";s:3:"山";s:3:"⼮";s:3:"巛";s:3:"⼯";s:3:"工";s:3:"⼰";s:3:"己";s:3:"⼱";s:3:"巾";s:3:"⼲";s:3:"干";s:3:"⼳";s:3:"幺";s:3:"⼴";s:3:"广";s:3:"⼵";s:3:"廴";s:3:"⼶";s:3:"廾";s:3:"⼷";s:3:"弋";s:3:"⼸";s:3:"弓";s:3:"⼹";s:3:"彐";s:3:"⼺";s:3:"彡";s:3:"⼻";s:3:"彳";s:3:"⼼";s:3:"心";s:3:"⼽";s:3:"戈";s:3:"⼾";s:3:"戶";s:3:"⼿";s:3:"手";s:3:"⽀";s:3:"支";s:3:"⽁";s:3:"攴";s:3:"⽂";s:3:"文";s:3:"⽃";s:3:"斗";s:3:"⽄";s:3:"斤";s:3:"⽅";s:3:"方";s:3:"⽆";s:3:"无";s:3:"⽇";s:3:"日";s:3:"⽈";s:3:"曰";s:3:"⽉";s:3:"月";s:3:"⽊";s:3:"木";s:3:"⽋";s:3:"欠";s:3:"⽌";s:3:"止";s:3:"⽍";s:3:"歹";s:3:"⽎";s:3:"殳";s:3:"⽏";s:3:"毋";s:3:"⽐";s:3:"比";s:3:"⽑";s:3:"毛";s:3:"⽒";s:3:"氏";s:3:"⽓";s:3:"气";s:3:"⽔";s:3:"水";s:3:"⽕";s:3:"火";s:3:"⽖";s:3:"爪";s:3:"⽗";s:3:"父";s:3:"⽘";s:3:"爻";s:3:"⽙";s:3:"爿";s:3:"⽚";s:3:"片";s:3:"⽛";s:3:"牙";s:3:"⽜";s:3:"牛";s:3:"⽝";s:3:"犬";s:3:"⽞";s:3:"玄";s:3:"⽟";s:3:"玉";s:3:"⽠";s:3:"瓜";s:3:"⽡";s:3:"瓦";s:3:"⽢";s:3:"甘";s:3:"⽣";s:3:"生";s:3:"⽤";s:3:"用";s:3:"⽥";s:3:"田";s:3:"⽦";s:3:"疋";s:3:"⽧";s:3:"疒";s:3:"⽨";s:3:"癶";s:3:"⽩";s:3:"白";s:3:"⽪";s:3:"皮";s:3:"⽫";s:3:"皿";s:3:"⽬";s:3:"目";s:3:"⽭";s:3:"矛";s:3:"⽮";s:3:"矢";s:3:"⽯";s:3:"石";s:3:"⽰";s:3:"示";s:3:"⽱";s:3:"禸";s:3:"⽲";s:3:"禾";s:3:"⽳";s:3:"穴";s:3:"⽴";s:3:"立";s:3:"⽵";s:3:"竹";s:3:"⽶";s:3:"米";s:3:"⽷";s:3:"糸";s:3:"⽸";s:3:"缶";s:3:"⽹";s:3:"网";s:3:"⽺";s:3:"羊";s:3:"⽻";s:3:"羽";s:3:"⽼";s:3:"老";s:3:"⽽";s:3:"而";s:3:"⽾";s:3:"耒";s:3:"⽿";s:3:"耳";s:3:"⾀";s:3:"聿";s:3:"⾁";s:3:"肉";s:3:"⾂";s:3:"臣";s:3:"⾃";s:3:"自";s:3:"⾄";s:3:"至";s:3:"⾅";s:3:"臼";s:3:"⾆";s:3:"舌";s:3:"⾇";s:3:"舛";s:3:"⾈";s:3:"舟";s:3:"⾉";s:3:"艮";s:3:"⾊";s:3:"色";s:3:"⾋";s:3:"艸";s:3:"⾌";s:3:"虍";s:3:"⾍";s:3:"虫";s:3:"⾎";s:3:"血";s:3:"⾏";s:3:"行";s:3:"⾐";s:3:"衣";s:3:"⾑";s:3:"襾";s:3:"⾒";s:3:"見";s:3:"⾓";s:3:"角";s:3:"⾔";s:3:"言";s:3:"⾕";s:3:"谷";s:3:"⾖";s:3:"豆";s:3:"⾗";s:3:"豕";s:3:"⾘";s:3:"豸";s:3:"⾙";s:3:"貝";s:3:"⾚";s:3:"赤";s:3:"⾛";s:3:"走";s:3:"⾜";s:3:"足";s:3:"⾝";s:3:"身";s:3:"⾞";s:3:"車";s:3:"⾟";s:3:"辛";s:3:"⾠";s:3:"辰";s:3:"⾡";s:3:"辵";s:3:"⾢";s:3:"邑";s:3:"⾣";s:3:"酉";s:3:"⾤";s:3:"釆";s:3:"⾥";s:3:"里";s:3:"⾦";s:3:"金";s:3:"⾧";s:3:"長";s:3:"⾨";s:3:"門";s:3:"⾩";s:3:"阜";s:3:"⾪";s:3:"隶";s:3:"⾫";s:3:"隹";s:3:"⾬";s:3:"雨";s:3:"⾭";s:3:"靑";s:3:"⾮";s:3:"非";s:3:"⾯";s:3:"面";s:3:"⾰";s:3:"革";s:3:"⾱";s:3:"韋";s:3:"⾲";s:3:"韭";s:3:"⾳";s:3:"音";s:3:"⾴";s:3:"頁";s:3:"⾵";s:3:"風";s:3:"⾶";s:3:"飛";s:3:"⾷";s:3:"食";s:3:"⾸";s:3:"首";s:3:"⾹";s:3:"香";s:3:"⾺";s:3:"馬";s:3:"⾻";s:3:"骨";s:3:"⾼";s:3:"高";s:3:"⾽";s:3:"髟";s:3:"⾾";s:3:"鬥";s:3:"⾿";s:3:"鬯";s:3:"⿀";s:3:"鬲";s:3:"⿁";s:3:"鬼";s:3:"⿂";s:3:"魚";s:3:"⿃";s:3:"鳥";s:3:"⿄";s:3:"鹵";s:3:"⿅";s:3:"鹿";s:3:"⿆";s:3:"麥";s:3:"⿇";s:3:"麻";s:3:"⿈";s:3:"黃";s:3:"⿉";s:3:"黍";s:3:"⿊";s:3:"黑";s:3:"⿋";s:3:"黹";s:3:"⿌";s:3:"黽";s:3:"⿍";s:3:"鼎";s:3:"⿎";s:3:"鼓";s:3:"⿏";s:3:"鼠";s:3:"⿐";s:3:"鼻";s:3:"⿑";s:3:"齊";s:3:"⿒";s:3:"齒";s:3:"⿓";s:3:"龍";s:3:"⿔";s:3:"龜";s:3:"⿕";s:3:"龠";s:3:"　";s:1:" ";s:3:"〶";s:3:"〒";s:3:"〸";s:3:"十";s:3:"〹";s:3:"卄";s:3:"〺";s:3:"卅";s:3:"が";s:6:"が";s:3:"ぎ";s:6:"ぎ";s:3:"ぐ";s:6:"ぐ";s:3:"げ";s:6:"げ";s:3:"ご";s:6:"ご";s:3:"ざ";s:6:"ざ";s:3:"じ";s:6:"じ";s:3:"ず";s:6:"ず";s:3:"ぜ";s:6:"ぜ";s:3:"ぞ";s:6:"ぞ";s:3:"だ";s:6:"だ";s:3:"ぢ";s:6:"ぢ";s:3:"づ";s:6:"づ";s:3:"で";s:6:"で";s:3:"ど";s:6:"ど";s:3:"ば";s:6:"ば";s:3:"ぱ";s:6:"ぱ";s:3:"び";s:6:"び";s:3:"ぴ";s:6:"ぴ";s:3:"ぶ";s:6:"ぶ";s:3:"ぷ";s:6:"ぷ";s:3:"べ";s:6:"べ";s:3:"ぺ";s:6:"ぺ";s:3:"ぼ";s:6:"ぼ";s:3:"ぽ";s:6:"ぽ";s:3:"ゔ";s:6:"ゔ";s:3:"゛";s:4:" ゙";s:3:"゜";s:4:" ゚";s:3:"ゞ";s:6:"ゞ";s:3:"ゟ";s:6:"より";s:3:"ガ";s:6:"ガ";s:3:"ギ";s:6:"ギ";s:3:"グ";s:6:"グ";s:3:"ゲ";s:6:"ゲ";s:3:"ゴ";s:6:"ゴ";s:3:"ザ";s:6:"ザ";s:3:"ジ";s:6:"ジ";s:3:"ズ";s:6:"ズ";s:3:"ゼ";s:6:"ゼ";s:3:"ゾ";s:6:"ゾ";s:3:"ダ";s:6:"ダ";s:3:"ヂ";s:6:"ヂ";s:3:"ヅ";s:6:"ヅ";s:3:"デ";s:6:"デ";s:3:"ド";s:6:"ド";s:3:"バ";s:6:"バ";s:3:"パ";s:6:"パ";s:3:"ビ";s:6:"ビ";s:3:"ピ";s:6:"ピ";s:3:"ブ";s:6:"ブ";s:3:"プ";s:6:"プ";s:3:"ベ";s:6:"ベ";s:3:"ペ";s:6:"ペ";s:3:"ボ";s:6:"ボ";s:3:"ポ";s:6:"ポ";s:3:"ヴ";s:6:"ヴ";s:3:"ヷ";s:6:"ヷ";s:3:"ヸ";s:6:"ヸ";s:3:"ヹ";s:6:"ヹ";s:3:"ヺ";s:6:"ヺ";s:3:"ヾ";s:6:"ヾ";s:3:"ヿ";s:6:"コト";s:3:"ㄱ";s:3:"ᄀ";s:3:"ㄲ";s:3:"ᄁ";s:3:"ㄳ";s:3:"ᆪ";s:3:"ㄴ";s:3:"ᄂ";s:3:"ㄵ";s:3:"ᆬ";s:3:"ㄶ";s:3:"ᆭ";s:3:"ㄷ";s:3:"ᄃ";s:3:"ㄸ";s:3:"ᄄ";s:3:"ㄹ";s:3:"ᄅ";s:3:"ㄺ";s:3:"ᆰ";s:3:"ㄻ";s:3:"ᆱ";s:3:"ㄼ";s:3:"ᆲ";s:3:"ㄽ";s:3:"ᆳ";s:3:"ㄾ";s:3:"ᆴ";s:3:"ㄿ";s:3:"ᆵ";s:3:"ㅀ";s:3:"ᄚ";s:3:"ㅁ";s:3:"ᄆ";s:3:"ㅂ";s:3:"ᄇ";s:3:"ㅃ";s:3:"ᄈ";s:3:"ㅄ";s:3:"ᄡ";s:3:"ㅅ";s:3:"ᄉ";s:3:"ㅆ";s:3:"ᄊ";s:3:"ㅇ";s:3:"ᄋ";s:3:"ㅈ";s:3:"ᄌ";s:3:"ㅉ";s:3:"ᄍ";s:3:"ㅊ";s:3:"ᄎ";s:3:"ㅋ";s:3:"ᄏ";s:3:"ㅌ";s:3:"ᄐ";s:3:"ㅍ";s:3:"ᄑ";s:3:"ㅎ";s:3:"ᄒ";s:3:"ㅏ";s:3:"ᅡ";s:3:"ㅐ";s:3:"ᅢ";s:3:"ㅑ";s:3:"ᅣ";s:3:"ㅒ";s:3:"ᅤ";s:3:"ㅓ";s:3:"ᅥ";s:3:"ㅔ";s:3:"ᅦ";s:3:"ㅕ";s:3:"ᅧ";s:3:"ㅖ";s:3:"ᅨ";s:3:"ㅗ";s:3:"ᅩ";s:3:"ㅘ";s:3:"ᅪ";s:3:"ㅙ";s:3:"ᅫ";s:3:"ㅚ";s:3:"ᅬ";s:3:"ㅛ";s:3:"ᅭ";s:3:"ㅜ";s:3:"ᅮ";s:3:"ㅝ";s:3:"ᅯ";s:3:"ㅞ";s:3:"ᅰ";s:3:"ㅟ";s:3:"ᅱ";s:3:"ㅠ";s:3:"ᅲ";s:3:"ㅡ";s:3:"ᅳ";s:3:"ㅢ";s:3:"ᅴ";s:3:"ㅣ";s:3:"ᅵ";s:3:"ㅤ";s:3:"ᅠ";s:3:"ㅥ";s:3:"ᄔ";s:3:"ㅦ";s:3:"ᄕ";s:3:"ㅧ";s:3:"ᇇ";s:3:"ㅨ";s:3:"ᇈ";s:3:"ㅩ";s:3:"ᇌ";s:3:"ㅪ";s:3:"ᇎ";s:3:"ㅫ";s:3:"ᇓ";s:3:"ㅬ";s:3:"ᇗ";s:3:"ㅭ";s:3:"ᇙ";s:3:"ㅮ";s:3:"ᄜ";s:3:"ㅯ";s:3:"ᇝ";s:3:"ㅰ";s:3:"ᇟ";s:3:"ㅱ";s:3:"ᄝ";s:3:"ㅲ";s:3:"ᄞ";s:3:"ㅳ";s:3:"ᄠ";s:3:"ㅴ";s:3:"ᄢ";s:3:"ㅵ";s:3:"ᄣ";s:3:"ㅶ";s:3:"ᄧ";s:3:"ㅷ";s:3:"ᄩ";s:3:"ㅸ";s:3:"ᄫ";s:3:"ㅹ";s:3:"ᄬ";s:3:"ㅺ";s:3:"ᄭ";s:3:"ㅻ";s:3:"ᄮ";s:3:"ㅼ";s:3:"ᄯ";s:3:"ㅽ";s:3:"ᄲ";s:3:"ㅾ";s:3:"ᄶ";s:3:"ㅿ";s:3:"ᅀ";s:3:"ㆀ";s:3:"ᅇ";s:3:"ㆁ";s:3:"ᅌ";s:3:"ㆂ";s:3:"ᇱ";s:3:"ㆃ";s:3:"ᇲ";s:3:"ㆄ";s:3:"ᅗ";s:3:"ㆅ";s:3:"ᅘ";s:3:"ㆆ";s:3:"ᅙ";s:3:"ㆇ";s:3:"ᆄ";s:3:"ㆈ";s:3:"ᆅ";s:3:"ㆉ";s:3:"ᆈ";s:3:"ㆊ";s:3:"ᆑ";s:3:"ㆋ";s:3:"ᆒ";s:3:"ㆌ";s:3:"ᆔ";s:3:"ㆍ";s:3:"ᆞ";s:3:"ㆎ";s:3:"ᆡ";s:3:"㆒";s:3:"一";s:3:"㆓";s:3:"二";s:3:"㆔";s:3:"三";s:3:"㆕";s:3:"四";s:3:"㆖";s:3:"上";s:3:"㆗";s:3:"中";s:3:"㆘";s:3:"下";s:3:"㆙";s:3:"甲";s:3:"㆚";s:3:"乙";s:3:"㆛";s:3:"丙";s:3:"㆜";s:3:"丁";s:3:"㆝";s:3:"天";s:3:"㆞";s:3:"地";s:3:"㆟";s:3:"人";s:3:"㈀";s:5:"(ᄀ)";s:3:"㈁";s:5:"(ᄂ)";s:3:"㈂";s:5:"(ᄃ)";s:3:"㈃";s:5:"(ᄅ)";s:3:"㈄";s:5:"(ᄆ)";s:3:"㈅";s:5:"(ᄇ)";s:3:"㈆";s:5:"(ᄉ)";s:3:"㈇";s:5:"(ᄋ)";s:3:"㈈";s:5:"(ᄌ)";s:3:"㈉";s:5:"(ᄎ)";s:3:"㈊";s:5:"(ᄏ)";s:3:"㈋";s:5:"(ᄐ)";s:3:"㈌";s:5:"(ᄑ)";s:3:"㈍";s:5:"(ᄒ)";s:3:"㈎";s:8:"(가)";s:3:"㈏";s:8:"(나)";s:3:"㈐";s:8:"(다)";s:3:"㈑";s:8:"(라)";s:3:"㈒";s:8:"(마)";s:3:"㈓";s:8:"(바)";s:3:"㈔";s:8:"(사)";s:3:"㈕";s:8:"(아)";s:3:"㈖";s:8:"(자)";s:3:"㈗";s:8:"(차)";s:3:"㈘";s:8:"(카)";s:3:"㈙";s:8:"(타)";s:3:"㈚";s:8:"(파)";s:3:"㈛";s:8:"(하)";s:3:"㈜";s:8:"(주)";s:3:"㈝";s:17:"(오전)";s:3:"㈞";s:14:"(오후)";s:3:"㈠";s:5:"(一)";s:3:"㈡";s:5:"(二)";s:3:"㈢";s:5:"(三)";s:3:"㈣";s:5:"(四)";s:3:"㈤";s:5:"(五)";s:3:"㈥";s:5:"(六)";s:3:"㈦";s:5:"(七)";s:3:"㈧";s:5:"(八)";s:3:"㈨";s:5:"(九)";s:3:"㈩";s:5:"(十)";s:3:"㈪";s:5:"(月)";s:3:"㈫";s:5:"(火)";s:3:"㈬";s:5:"(水)";s:3:"㈭";s:5:"(木)";s:3:"㈮";s:5:"(金)";s:3:"㈯";s:5:"(土)";s:3:"㈰";s:5:"(日)";s:3:"㈱";s:5:"(株)";s:3:"㈲";s:5:"(有)";s:3:"㈳";s:5:"(社)";s:3:"㈴";s:5:"(名)";s:3:"㈵";s:5:"(特)";s:3:"㈶";s:5:"(財)";s:3:"㈷";s:5:"(祝)";s:3:"㈸";s:5:"(労)";s:3:"㈹";s:5:"(代)";s:3:"㈺";s:5:"(呼)";s:3:"㈻";s:5:"(学)";s:3:"㈼";s:5:"(監)";s:3:"㈽";s:5:"(企)";s:3:"㈾";s:5:"(資)";s:3:"㈿";s:5:"(協)";s:3:"㉀";s:5:"(祭)";s:3:"㉁";s:5:"(休)";s:3:"㉂";s:5:"(自)";s:3:"㉃";s:5:"(至)";s:3:"㉐";s:3:"PTE";s:3:"㉑";s:2:"21";s:3:"㉒";s:2:"22";s:3:"㉓";s:2:"23";s:3:"㉔";s:2:"24";s:3:"㉕";s:2:"25";s:3:"㉖";s:2:"26";s:3:"㉗";s:2:"27";s:3:"㉘";s:2:"28";s:3:"㉙";s:2:"29";s:3:"㉚";s:2:"30";s:3:"㉛";s:2:"31";s:3:"㉜";s:2:"32";s:3:"㉝";s:2:"33";s:3:"㉞";s:2:"34";s:3:"㉟";s:2:"35";s:3:"㉠";s:3:"ᄀ";s:3:"㉡";s:3:"ᄂ";s:3:"㉢";s:3:"ᄃ";s:3:"㉣";s:3:"ᄅ";s:3:"㉤";s:3:"ᄆ";s:3:"㉥";s:3:"ᄇ";s:3:"㉦";s:3:"ᄉ";s:3:"㉧";s:3:"ᄋ";s:3:"㉨";s:3:"ᄌ";s:3:"㉩";s:3:"ᄎ";s:3:"㉪";s:3:"ᄏ";s:3:"㉫";s:3:"ᄐ";s:3:"㉬";s:3:"ᄑ";s:3:"㉭";s:3:"ᄒ";s:3:"㉮";s:6:"가";s:3:"㉯";s:6:"나";s:3:"㉰";s:6:"다";s:3:"㉱";s:6:"라";s:3:"㉲";s:6:"마";s:3:"㉳";s:6:"바";s:3:"㉴";s:6:"사";s:3:"㉵";s:6:"아";s:3:"㉶";s:6:"자";s:3:"㉷";s:6:"차";s:3:"㉸";s:6:"카";s:3:"㉹";s:6:"타";s:3:"㉺";s:6:"파";s:3:"㉻";s:6:"하";s:3:"㉼";s:15:"참고";s:3:"㉽";s:12:"주의";s:3:"㉾";s:6:"우";s:3:"㊀";s:3:"一";s:3:"㊁";s:3:"二";s:3:"㊂";s:3:"三";s:3:"㊃";s:3:"四";s:3:"㊄";s:3:"五";s:3:"㊅";s:3:"六";s:3:"㊆";s:3:"七";s:3:"㊇";s:3:"八";s:3:"㊈";s:3:"九";s:3:"㊉";s:3:"十";s:3:"㊊";s:3:"月";s:3:"㊋";s:3:"火";s:3:"㊌";s:3:"水";s:3:"㊍";s:3:"木";s:3:"㊎";s:3:"金";s:3:"㊏";s:3:"土";s:3:"㊐";s:3:"日";s:3:"㊑";s:3:"株";s:3:"㊒";s:3:"有";s:3:"㊓";s:3:"社";s:3:"㊔";s:3:"名";s:3:"㊕";s:3:"特";s:3:"㊖";s:3:"財";s:3:"㊗";s:3:"祝";s:3:"㊘";s:3:"労";s:3:"㊙";s:3:"秘";s:3:"㊚";s:3:"男";s:3:"㊛";s:3:"女";s:3:"㊜";s:3:"適";s:3:"㊝";s:3:"優";s:3:"㊞";s:3:"印";s:3:"㊟";s:3:"注";s:3:"㊠";s:3:"項";s:3:"㊡";s:3:"休";s:3:"㊢";s:3:"写";s:3:"㊣";s:3:"正";s:3:"㊤";s:3:"上";s:3:"㊥";s:3:"中";s:3:"㊦";s:3:"下";s:3:"㊧";s:3:"左";s:3:"㊨";s:3:"右";s:3:"㊩";s:3:"医";s:3:"㊪";s:3:"宗";s:3:"㊫";s:3:"学";s:3:"㊬";s:3:"監";s:3:"㊭";s:3:"企";s:3:"㊮";s:3:"資";s:3:"㊯";s:3:"協";s:3:"㊰";s:3:"夜";s:3:"㊱";s:2:"36";s:3:"㊲";s:2:"37";s:3:"㊳";s:2:"38";s:3:"㊴";s:2:"39";s:3:"㊵";s:2:"40";s:3:"㊶";s:2:"41";s:3:"㊷";s:2:"42";s:3:"㊸";s:2:"43";s:3:"㊹";s:2:"44";s:3:"㊺";s:2:"45";s:3:"㊻";s:2:"46";s:3:"㊼";s:2:"47";s:3:"㊽";s:2:"48";s:3:"㊾";s:2:"49";s:3:"㊿";s:2:"50";s:3:"㋀";s:4:"1月";s:3:"㋁";s:4:"2月";s:3:"㋂";s:4:"3月";s:3:"㋃";s:4:"4月";s:3:"㋄";s:4:"5月";s:3:"㋅";s:4:"6月";s:3:"㋆";s:4:"7月";s:3:"㋇";s:4:"8月";s:3:"㋈";s:4:"9月";s:3:"㋉";s:5:"10月";s:3:"㋊";s:5:"11月";s:3:"㋋";s:5:"12月";s:3:"㋌";s:2:"Hg";s:3:"㋍";s:3:"erg";s:3:"㋎";s:2:"eV";s:3:"㋏";s:3:"LTD";s:3:"㋐";s:3:"ア";s:3:"㋑";s:3:"イ";s:3:"㋒";s:3:"ウ";s:3:"㋓";s:3:"エ";s:3:"㋔";s:3:"オ";s:3:"㋕";s:3:"カ";s:3:"㋖";s:3:"キ";s:3:"㋗";s:3:"ク";s:3:"㋘";s:3:"ケ";s:3:"㋙";s:3:"コ";s:3:"㋚";s:3:"サ";s:3:"㋛";s:3:"シ";s:3:"㋜";s:3:"ス";s:3:"㋝";s:3:"セ";s:3:"㋞";s:3:"ソ";s:3:"㋟";s:3:"タ";s:3:"㋠";s:3:"チ";s:3:"㋡";s:3:"ツ";s:3:"㋢";s:3:"テ";s:3:"㋣";s:3:"ト";s:3:"㋤";s:3:"ナ";s:3:"㋥";s:3:"ニ";s:3:"㋦";s:3:"ヌ";s:3:"㋧";s:3:"ネ";s:3:"㋨";s:3:"ノ";s:3:"㋩";s:3:"ハ";s:3:"㋪";s:3:"ヒ";s:3:"㋫";s:3:"フ";s:3:"㋬";s:3:"ヘ";s:3:"㋭";s:3:"ホ";s:3:"㋮";s:3:"マ";s:3:"㋯";s:3:"ミ";s:3:"㋰";s:3:"ム";s:3:"㋱";s:3:"メ";s:3:"㋲";s:3:"モ";s:3:"㋳";s:3:"ヤ";s:3:"㋴";s:3:"ユ";s:3:"㋵";s:3:"ヨ";s:3:"㋶";s:3:"ラ";s:3:"㋷";s:3:"リ";s:3:"㋸";s:3:"ル";s:3:"㋹";s:3:"レ";s:3:"㋺";s:3:"ロ";s:3:"㋻";s:3:"ワ";s:3:"㋼";s:3:"ヰ";s:3:"㋽";s:3:"ヱ";s:3:"㋾";s:3:"ヲ";s:3:"㌀";s:15:"アパート";s:3:"㌁";s:12:"アルファ";s:3:"㌂";s:15:"アンペア";s:3:"㌃";s:9:"アール";s:3:"㌄";s:15:"イニング";s:3:"㌅";s:9:"インチ";s:3:"㌆";s:9:"ウォン";s:3:"㌇";s:18:"エスクード";s:3:"㌈";s:12:"エーカー";s:3:"㌉";s:9:"オンス";s:3:"㌊";s:9:"オーム";s:3:"㌋";s:9:"カイリ";s:3:"㌌";s:12:"カラット";s:3:"㌍";s:12:"カロリー";s:3:"㌎";s:12:"ガロン";s:3:"㌏";s:12:"ガンマ";s:3:"㌐";s:12:"ギガ";s:3:"㌑";s:12:"ギニー";s:3:"㌒";s:12:"キュリー";s:3:"㌓";s:18:"ギルダー";s:3:"㌔";s:6:"キロ";s:3:"㌕";s:18:"キログラム";s:3:"㌖";s:18:"キロメートル";s:3:"㌗";s:15:"キロワット";s:3:"㌘";s:12:"グラム";s:3:"㌙";s:18:"グラムトン";s:3:"㌚";s:18:"クルゼイロ";s:3:"㌛";s:12:"クローネ";s:3:"㌜";s:9:"ケース";s:3:"㌝";s:9:"コルナ";s:3:"㌞";s:12:"コーポ";s:3:"㌟";s:12:"サイクル";s:3:"㌠";s:15:"サンチーム";s:3:"㌡";s:15:"シリング";s:3:"㌢";s:9:"センチ";s:3:"㌣";s:9:"セント";s:3:"㌤";s:12:"ダース";s:3:"㌥";s:9:"デシ";s:3:"㌦";s:9:"ドル";s:3:"㌧";s:6:"トン";s:3:"㌨";s:6:"ナノ";s:3:"㌩";s:9:"ノット";s:3:"㌪";s:9:"ハイツ";s:3:"㌫";s:18:"パーセント";s:3:"㌬";s:12:"パーツ";s:3:"㌭";s:15:"バーレル";s:3:"㌮";s:18:"ピアストル";s:3:"㌯";s:12:"ピクル";s:3:"㌰";s:9:"ピコ";s:3:"㌱";s:9:"ビル";s:3:"㌲";s:18:"ファラッド";s:3:"㌳";s:12:"フィート";s:3:"㌴";s:18:"ブッシェル";s:3:"㌵";s:9:"フラン";s:3:"㌶";s:15:"ヘクタール";s:3:"㌷";s:9:"ペソ";s:3:"㌸";s:12:"ペニヒ";s:3:"㌹";s:9:"ヘルツ";s:3:"㌺";s:12:"ペンス";s:3:"㌻";s:15:"ページ";s:3:"㌼";s:12:"ベータ";s:3:"㌽";s:15:"ポイント";s:3:"㌾";s:12:"ボルト";s:3:"㌿";s:6:"ホン";s:3:"㍀";s:15:"ポンド";s:3:"㍁";s:9:"ホール";s:3:"㍂";s:9:"ホーン";s:3:"㍃";s:12:"マイクロ";s:3:"㍄";s:9:"マイル";s:3:"㍅";s:9:"マッハ";s:3:"㍆";s:9:"マルク";s:3:"㍇";s:15:"マンション";s:3:"㍈";s:12:"ミクロン";s:3:"㍉";s:6:"ミリ";s:3:"㍊";s:18:"ミリバール";s:3:"㍋";s:9:"メガ";s:3:"㍌";s:15:"メガトン";s:3:"㍍";s:12:"メートル";s:3:"㍎";s:12:"ヤード";s:3:"㍏";s:9:"ヤール";s:3:"㍐";s:9:"ユアン";s:3:"㍑";s:12:"リットル";s:3:"㍒";s:6:"リラ";s:3:"㍓";s:12:"ルピー";s:3:"㍔";s:15:"ルーブル";s:3:"㍕";s:6:"レム";s:3:"㍖";s:18:"レントゲン";s:3:"㍗";s:9:"ワット";s:3:"㍘";s:4:"0点";s:3:"㍙";s:4:"1点";s:3:"㍚";s:4:"2点";s:3:"㍛";s:4:"3点";s:3:"㍜";s:4:"4点";s:3:"㍝";s:4:"5点";s:3:"㍞";s:4:"6点";s:3:"㍟";s:4:"7点";s:3:"㍠";s:4:"8点";s:3:"㍡";s:4:"9点";s:3:"㍢";s:5:"10点";s:3:"㍣";s:5:"11点";s:3:"㍤";s:5:"12点";s:3:"㍥";s:5:"13点";s:3:"㍦";s:5:"14点";s:3:"㍧";s:5:"15点";s:3:"㍨";s:5:"16点";s:3:"㍩";s:5:"17点";s:3:"㍪";s:5:"18点";s:3:"㍫";s:5:"19点";s:3:"㍬";s:5:"20点";s:3:"㍭";s:5:"21点";s:3:"㍮";s:5:"22点";s:3:"㍯";s:5:"23点";s:3:"㍰";s:5:"24点";s:3:"㍱";s:3:"hPa";s:3:"㍲";s:2:"da";s:3:"㍳";s:2:"AU";s:3:"㍴";s:3:"bar";s:3:"㍵";s:2:"oV";s:3:"㍶";s:2:"pc";s:3:"㍷";s:2:"dm";s:3:"㍸";s:3:"dm2";s:3:"㍹";s:3:"dm3";s:3:"㍺";s:2:"IU";s:3:"㍻";s:6:"平成";s:3:"㍼";s:6:"昭和";s:3:"㍽";s:6:"大正";s:3:"㍾";s:6:"明治";s:3:"㍿";s:12:"株式会社";s:3:"㎀";s:2:"pA";s:3:"㎁";s:2:"nA";s:3:"㎂";s:3:"μA";s:3:"㎃";s:2:"mA";s:3:"㎄";s:2:"kA";s:3:"㎅";s:2:"KB";s:3:"㎆";s:2:"MB";s:3:"㎇";s:2:"GB";s:3:"㎈";s:3:"cal";s:3:"㎉";s:4:"kcal";s:3:"㎊";s:2:"pF";s:3:"㎋";s:2:"nF";s:3:"㎌";s:3:"μF";s:3:"㎍";s:3:"μg";s:3:"㎎";s:2:"mg";s:3:"㎏";s:2:"kg";s:3:"㎐";s:2:"Hz";s:3:"㎑";s:3:"kHz";s:3:"㎒";s:3:"MHz";s:3:"㎓";s:3:"GHz";s:3:"㎔";s:3:"THz";s:3:"㎕";s:3:"μl";s:3:"㎖";s:2:"ml";s:3:"㎗";s:2:"dl";s:3:"㎘";s:2:"kl";s:3:"㎙";s:2:"fm";s:3:"㎚";s:2:"nm";s:3:"㎛";s:3:"μm";s:3:"㎜";s:2:"mm";s:3:"㎝";s:2:"cm";s:3:"㎞";s:2:"km";s:3:"㎟";s:3:"mm2";s:3:"㎠";s:3:"cm2";s:3:"㎡";s:2:"m2";s:3:"㎢";s:3:"km2";s:3:"㎣";s:3:"mm3";s:3:"㎤";s:3:"cm3";s:3:"㎥";s:2:"m3";s:3:"㎦";s:3:"km3";s:3:"㎧";s:5:"m∕s";s:3:"㎨";s:6:"m∕s2";s:3:"㎩";s:2:"Pa";s:3:"㎪";s:3:"kPa";s:3:"㎫";s:3:"MPa";s:3:"㎬";s:3:"GPa";s:3:"㎭";s:3:"rad";s:3:"㎮";s:7:"rad∕s";s:3:"㎯";s:8:"rad∕s2";s:3:"㎰";s:2:"ps";s:3:"㎱";s:2:"ns";s:3:"㎲";s:3:"μs";s:3:"㎳";s:2:"ms";s:3:"㎴";s:2:"pV";s:3:"㎵";s:2:"nV";s:3:"㎶";s:3:"μV";s:3:"㎷";s:2:"mV";s:3:"㎸";s:2:"kV";s:3:"㎹";s:2:"MV";s:3:"㎺";s:2:"pW";s:3:"㎻";s:2:"nW";s:3:"㎼";s:3:"μW";s:3:"㎽";s:2:"mW";s:3:"㎾";s:2:"kW";s:3:"㎿";s:2:"MW";s:3:"㏀";s:3:"kΩ";s:3:"㏁";s:3:"MΩ";s:3:"㏂";s:4:"a.m.";s:3:"㏃";s:2:"Bq";s:3:"㏄";s:2:"cc";s:3:"㏅";s:2:"cd";s:3:"㏆";s:6:"C∕kg";s:3:"㏇";s:3:"Co.";s:3:"㏈";s:2:"dB";s:3:"㏉";s:2:"Gy";s:3:"㏊";s:2:"ha";s:3:"㏋";s:2:"HP";s:3:"㏌";s:2:"in";s:3:"㏍";s:2:"KK";s:3:"㏎";s:2:"KM";s:3:"㏏";s:2:"kt";s:3:"㏐";s:2:"lm";s:3:"㏑";s:2:"ln";s:3:"㏒";s:3:"log";s:3:"㏓";s:2:"lx";s:3:"㏔";s:2:"mb";s:3:"㏕";s:3:"mil";s:3:"㏖";s:3:"mol";s:3:"㏗";s:2:"PH";s:3:"㏘";s:4:"p.m.";s:3:"㏙";s:3:"PPM";s:3:"㏚";s:2:"PR";s:3:"㏛";s:2:"sr";s:3:"㏜";s:2:"Sv";s:3:"㏝";s:2:"Wb";s:3:"㏞";s:5:"V∕m";s:3:"㏟";s:5:"A∕m";s:3:"㏠";s:4:"1日";s:3:"㏡";s:4:"2日";s:3:"㏢";s:4:"3日";s:3:"㏣";s:4:"4日";s:3:"㏤";s:4:"5日";s:3:"㏥";s:4:"6日";s:3:"㏦";s:4:"7日";s:3:"㏧";s:4:"8日";s:3:"㏨";s:4:"9日";s:3:"㏩";s:5:"10日";s:3:"㏪";s:5:"11日";s:3:"㏫";s:5:"12日";s:3:"㏬";s:5:"13日";s:3:"㏭";s:5:"14日";s:3:"㏮";s:5:"15日";s:3:"㏯";s:5:"16日";s:3:"㏰";s:5:"17日";s:3:"㏱";s:5:"18日";s:3:"㏲";s:5:"19日";s:3:"㏳";s:5:"20日";s:3:"㏴";s:5:"21日";s:3:"㏵";s:5:"22日";s:3:"㏶";s:5:"23日";s:3:"㏷";s:5:"24日";s:3:"㏸";s:5:"25日";s:3:"㏹";s:5:"26日";s:3:"㏺";s:5:"27日";s:3:"㏻";s:5:"28日";s:3:"㏼";s:5:"29日";s:3:"㏽";s:5:"30日";s:3:"㏾";s:5:"31日";s:3:"㏿";s:3:"gal";s:3:"豈";s:3:"豈";s:3:"更";s:3:"更";s:3:"車";s:3:"車";s:3:"賈";s:3:"賈";s:3:"滑";s:3:"滑";s:3:"串";s:3:"串";s:3:"句";s:3:"句";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"龜";s:3:"契";s:3:"契";s:3:"金";s:3:"金";s:3:"喇";s:3:"喇";s:3:"奈";s:3:"奈";s:3:"懶";s:3:"懶";s:3:"癩";s:3:"癩";s:3:"羅";s:3:"羅";s:3:"蘿";s:3:"蘿";s:3:"螺";s:3:"螺";s:3:"裸";s:3:"裸";s:3:"邏";s:3:"邏";s:3:"樂";s:3:"樂";s:3:"洛";s:3:"洛";s:3:"烙";s:3:"烙";s:3:"珞";s:3:"珞";s:3:"落";s:3:"落";s:3:"酪";s:3:"酪";s:3:"駱";s:3:"駱";s:3:"亂";s:3:"亂";s:3:"卵";s:3:"卵";s:3:"欄";s:3:"欄";s:3:"爛";s:3:"爛";s:3:"蘭";s:3:"蘭";s:3:"鸞";s:3:"鸞";s:3:"嵐";s:3:"嵐";s:3:"濫";s:3:"濫";s:3:"藍";s:3:"藍";s:3:"襤";s:3:"襤";s:3:"拉";s:3:"拉";s:3:"臘";s:3:"臘";s:3:"蠟";s:3:"蠟";s:3:"廊";s:3:"廊";s:3:"朗";s:3:"朗";s:3:"浪";s:3:"浪";s:3:"狼";s:3:"狼";s:3:"郎";s:3:"郎";s:3:"來";s:3:"來";s:3:"冷";s:3:"冷";s:3:"勞";s:3:"勞";s:3:"擄";s:3:"擄";s:3:"櫓";s:3:"櫓";s:3:"爐";s:3:"爐";s:3:"盧";s:3:"盧";s:3:"老";s:3:"老";s:3:"蘆";s:3:"蘆";s:3:"虜";s:3:"虜";s:3:"路";s:3:"路";s:3:"露";s:3:"露";s:3:"魯";s:3:"魯";s:3:"鷺";s:3:"鷺";s:3:"碌";s:3:"碌";s:3:"祿";s:3:"祿";s:3:"綠";s:3:"綠";s:3:"菉";s:3:"菉";s:3:"錄";s:3:"錄";s:3:"鹿";s:3:"鹿";s:3:"論";s:3:"論";s:3:"壟";s:3:"壟";s:3:"弄";s:3:"弄";s:3:"籠";s:3:"籠";s:3:"聾";s:3:"聾";s:3:"牢";s:3:"牢";s:3:"磊";s:3:"磊";s:3:"賂";s:3:"賂";s:3:"雷";s:3:"雷";s:3:"壘";s:3:"壘";s:3:"屢";s:3:"屢";s:3:"樓";s:3:"樓";s:3:"淚";s:3:"淚";s:3:"漏";s:3:"漏";s:3:"累";s:3:"累";s:3:"縷";s:3:"縷";s:3:"陋";s:3:"陋";s:3:"勒";s:3:"勒";s:3:"肋";s:3:"肋";s:3:"凜";s:3:"凜";s:3:"凌";s:3:"凌";s:3:"稜";s:3:"稜";s:3:"綾";s:3:"綾";s:3:"菱";s:3:"菱";s:3:"陵";s:3:"陵";s:3:"讀";s:3:"讀";s:3:"拏";s:3:"拏";s:3:"樂";s:3:"樂";s:3:"諾";s:3:"諾";s:3:"丹";s:3:"丹";s:3:"寧";s:3:"寧";s:3:"怒";s:3:"怒";s:3:"率";s:3:"率";s:3:"異";s:3:"異";s:3:"北";s:3:"北";s:3:"磻";s:3:"磻";s:3:"便";s:3:"便";s:3:"復";s:3:"復";s:3:"不";s:3:"不";s:3:"泌";s:3:"泌";s:3:"數";s:3:"數";s:3:"索";s:3:"索";s:3:"參";s:3:"參";s:3:"塞";s:3:"塞";s:3:"省";s:3:"省";s:3:"葉";s:3:"葉";s:3:"說";s:3:"說";s:3:"殺";s:3:"殺";s:3:"辰";s:3:"辰";s:3:"沈";s:3:"沈";s:3:"拾";s:3:"拾";s:3:"若";s:3:"若";s:3:"掠";s:3:"掠";s:3:"略";s:3:"略";s:3:"亮";s:3:"亮";s:3:"兩";s:3:"兩";s:3:"凉";s:3:"凉";s:3:"梁";s:3:"梁";s:3:"糧";s:3:"糧";s:3:"良";s:3:"良";s:3:"諒";s:3:"諒";s:3:"量";s:3:"量";s:3:"勵";s:3:"勵";s:3:"呂";s:3:"呂";s:3:"女";s:3:"女";s:3:"廬";s:3:"廬";s:3:"旅";s:3:"旅";s:3:"濾";s:3:"濾";s:3:"礪";s:3:"礪";s:3:"閭";s:3:"閭";s:3:"驪";s:3:"驪";s:3:"麗";s:3:"麗";s:3:"黎";s:3:"黎";s:3:"力";s:3:"力";s:3:"曆";s:3:"曆";s:3:"歷";s:3:"歷";s:3:"轢";s:3:"轢";s:3:"年";s:3:"年";s:3:"憐";s:3:"憐";s:3:"戀";s:3:"戀";s:3:"撚";s:3:"撚";s:3:"漣";s:3:"漣";s:3:"煉";s:3:"煉";s:3:"璉";s:3:"璉";s:3:"秊";s:3:"秊";s:3:"練";s:3:"練";s:3:"聯";s:3:"聯";s:3:"輦";s:3:"輦";s:3:"蓮";s:3:"蓮";s:3:"連";s:3:"連";s:3:"鍊";s:3:"鍊";s:3:"列";s:3:"列";s:3:"劣";s:3:"劣";s:3:"咽";s:3:"咽";s:3:"烈";s:3:"烈";s:3:"裂";s:3:"裂";s:3:"說";s:3:"說";s:3:"廉";s:3:"廉";s:3:"念";s:3:"念";s:3:"捻";s:3:"捻";s:3:"殮";s:3:"殮";s:3:"簾";s:3:"簾";s:3:"獵";s:3:"獵";s:3:"令";s:3:"令";s:3:"囹";s:3:"囹";s:3:"寧";s:3:"寧";s:3:"嶺";s:3:"嶺";s:3:"怜";s:3:"怜";s:3:"玲";s:3:"玲";s:3:"瑩";s:3:"瑩";s:3:"羚";s:3:"羚";s:3:"聆";s:3:"聆";s:3:"鈴";s:3:"鈴";s:3:"零";s:3:"零";s:3:"靈";s:3:"靈";s:3:"領";s:3:"領";s:3:"例";s:3:"例";s:3:"禮";s:3:"禮";s:3:"醴";s:3:"醴";s:3:"隸";s:3:"隸";s:3:"惡";s:3:"惡";s:3:"了";s:3:"了";s:3:"僚";s:3:"僚";s:3:"寮";s:3:"寮";s:3:"尿";s:3:"尿";s:3:"料";s:3:"料";s:3:"樂";s:3:"樂";s:3:"燎";s:3:"燎";s:3:"療";s:3:"療";s:3:"蓼";s:3:"蓼";s:3:"遼";s:3:"遼";s:3:"龍";s:3:"龍";s:3:"暈";s:3:"暈";s:3:"阮";s:3:"阮";s:3:"劉";s:3:"劉";s:3:"杻";s:3:"杻";s:3:"柳";s:3:"柳";s:3:"流";s:3:"流";s:3:"溜";s:3:"溜";s:3:"琉";s:3:"琉";s:3:"留";s:3:"留";s:3:"硫";s:3:"硫";s:3:"紐";s:3:"紐";s:3:"類";s:3:"類";s:3:"六";s:3:"六";s:3:"戮";s:3:"戮";s:3:"陸";s:3:"陸";s:3:"倫";s:3:"倫";s:3:"崙";s:3:"崙";s:3:"淪";s:3:"淪";s:3:"輪";s:3:"輪";s:3:"律";s:3:"律";s:3:"慄";s:3:"慄";s:3:"栗";s:3:"栗";s:3:"率";s:3:"率";s:3:"隆";s:3:"隆";s:3:"利";s:3:"利";s:3:"吏";s:3:"吏";s:3:"履";s:3:"履";s:3:"易";s:3:"易";s:3:"李";s:3:"李";s:3:"梨";s:3:"梨";s:3:"泥";s:3:"泥";s:3:"理";s:3:"理";s:3:"痢";s:3:"痢";s:3:"罹";s:3:"罹";s:3:"裏";s:3:"裏";s:3:"裡";s:3:"裡";s:3:"里";s:3:"里";s:3:"離";s:3:"離";s:3:"匿";s:3:"匿";s:3:"溺";s:3:"溺";s:3:"吝";s:3:"吝";s:3:"燐";s:3:"燐";s:3:"璘";s:3:"璘";s:3:"藺";s:3:"藺";s:3:"隣";s:3:"隣";s:3:"鱗";s:3:"鱗";s:3:"麟";s:3:"麟";s:3:"林";s:3:"林";s:3:"淋";s:3:"淋";s:3:"臨";s:3:"臨";s:3:"立";s:3:"立";s:3:"笠";s:3:"笠";s:3:"粒";s:3:"粒";s:3:"狀";s:3:"狀";s:3:"炙";s:3:"炙";s:3:"識";s:3:"識";s:3:"什";s:3:"什";s:3:"茶";s:3:"茶";s:3:"刺";s:3:"刺";s:3:"切";s:3:"切";s:3:"度";s:3:"度";s:3:"拓";s:3:"拓";s:3:"糖";s:3:"糖";s:3:"宅";s:3:"宅";s:3:"洞";s:3:"洞";s:3:"暴";s:3:"暴";s:3:"輻";s:3:"輻";s:3:"行";s:3:"行";s:3:"降";s:3:"降";s:3:"見";s:3:"見";s:3:"廓";s:3:"廓";s:3:"兀";s:3:"兀";s:3:"嗀";s:3:"嗀";s:3:"塚";s:3:"塚";s:3:"晴";s:3:"晴";s:3:"凞";s:3:"凞";s:3:"猪";s:3:"猪";s:3:"益";s:3:"益";s:3:"礼";s:3:"礼";s:3:"神";s:3:"神";s:3:"祥";s:3:"祥";s:3:"福";s:3:"福";s:3:"靖";s:3:"靖";s:3:"精";s:3:"精";s:3:"羽";s:3:"羽";s:3:"蘒";s:3:"蘒";s:3:"諸";s:3:"諸";s:3:"逸";s:3:"逸";s:3:"都";s:3:"都";s:3:"飯";s:3:"飯";s:3:"飼";s:3:"飼";s:3:"館";s:3:"館";s:3:"鶴";s:3:"鶴";s:3:"侮";s:3:"侮";s:3:"僧";s:3:"僧";s:3:"免";s:3:"免";s:3:"勉";s:3:"勉";s:3:"勤";s:3:"勤";s:3:"卑";s:3:"卑";s:3:"喝";s:3:"喝";s:3:"嘆";s:3:"嘆";s:3:"器";s:3:"器";s:3:"塀";s:3:"塀";s:3:"墨";s:3:"墨";s:3:"層";s:3:"層";s:3:"屮";s:3:"屮";s:3:"悔";s:3:"悔";s:3:"慨";s:3:"慨";s:3:"憎";s:3:"憎";s:3:"懲";s:3:"懲";s:3:"敏";s:3:"敏";s:3:"既";s:3:"既";s:3:"暑";s:3:"暑";s:3:"梅";s:3:"梅";s:3:"海";s:3:"海";s:3:"渚";s:3:"渚";s:3:"漢";s:3:"漢";s:3:"煮";s:3:"煮";s:3:"爫";s:3:"爫";s:3:"琢";s:3:"琢";s:3:"碑";s:3:"碑";s:3:"社";s:3:"社";s:3:"祉";s:3:"祉";s:3:"祈";s:3:"祈";s:3:"祐";s:3:"祐";s:3:"祖";s:3:"祖";s:3:"祝";s:3:"祝";s:3:"禍";s:3:"禍";s:3:"禎";s:3:"禎";s:3:"穀";s:3:"穀";s:3:"突";s:3:"突";s:3:"節";s:3:"節";s:3:"練";s:3:"練";s:3:"縉";s:3:"縉";s:3:"繁";s:3:"繁";s:3:"署";s:3:"署";s:3:"者";s:3:"者";s:3:"臭";s:3:"臭";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"艹";s:3:"著";s:3:"著";s:3:"褐";s:3:"褐";s:3:"視";s:3:"視";s:3:"謁";s:3:"謁";s:3:"謹";s:3:"謹";s:3:"賓";s:3:"賓";s:3:"贈";s:3:"贈";s:3:"辶";s:3:"辶";s:3:"逸";s:3:"逸";s:3:"難";s:3:"難";s:3:"響";s:3:"響";s:3:"頻";s:3:"頻";s:3:"並";s:3:"並";s:3:"况";s:3:"况";s:3:"全";s:3:"全";s:3:"侀";s:3:"侀";s:3:"充";s:3:"充";s:3:"冀";s:3:"冀";s:3:"勇";s:3:"勇";s:3:"勺";s:3:"勺";s:3:"喝";s:3:"喝";s:3:"啕";s:3:"啕";s:3:"喙";s:3:"喙";s:3:"嗢";s:3:"嗢";s:3:"塚";s:3:"塚";s:3:"墳";s:3:"墳";s:3:"奄";s:3:"奄";s:3:"奔";s:3:"奔";s:3:"婢";s:3:"婢";s:3:"嬨";s:3:"嬨";s:3:"廒";s:3:"廒";s:3:"廙";s:3:"廙";s:3:"彩";s:3:"彩";s:3:"徭";s:3:"徭";s:3:"惘";s:3:"惘";s:3:"慎";s:3:"慎";s:3:"愈";s:3:"愈";s:3:"憎";s:3:"憎";s:3:"慠";s:3:"慠";s:3:"懲";s:3:"懲";s:3:"戴";s:3:"戴";s:3:"揄";s:3:"揄";s:3:"搜";s:3:"搜";s:3:"摒";s:3:"摒";s:3:"敖";s:3:"敖";s:3:"晴";s:3:"晴";s:3:"朗";s:3:"朗";s:3:"望";s:3:"望";s:3:"杖";s:3:"杖";s:3:"歹";s:3:"歹";s:3:"殺";s:3:"殺";s:3:"流";s:3:"流";s:3:"滛";s:3:"滛";s:3:"滋";s:3:"滋";s:3:"漢";s:3:"漢";s:3:"瀞";s:3:"瀞";s:3:"煮";s:3:"煮";s:3:"瞧";s:3:"瞧";s:3:"爵";s:3:"爵";s:3:"犯";s:3:"犯";s:3:"猪";s:3:"猪";s:3:"瑱";s:3:"瑱";s:3:"甆";s:3:"甆";s:3:"画";s:3:"画";s:3:"瘝";s:3:"瘝";s:3:"瘟";s:3:"瘟";s:3:"益";s:3:"益";s:3:"盛";s:3:"盛";s:3:"直";s:3:"直";s:3:"睊";s:3:"睊";s:3:"着";s:3:"着";s:3:"磌";s:3:"磌";s:3:"窱";s:3:"窱";s:3:"節";s:3:"節";s:3:"类";s:3:"类";s:3:"絛";s:3:"絛";s:3:"練";s:3:"練";s:3:"缾";s:3:"缾";s:3:"者";s:3:"者";s:3:"荒";s:3:"荒";s:3:"華";s:3:"華";s:3:"蝹";s:3:"蝹";s:3:"襁";s:3:"襁";s:3:"覆";s:3:"覆";s:3:"視";s:3:"視";s:3:"調";s:3:"調";s:3:"諸";s:3:"諸";s:3:"請";s:3:"請";s:3:"謁";s:3:"謁";s:3:"諾";s:3:"諾";s:3:"諭";s:3:"諭";s:3:"謹";s:3:"謹";s:3:"變";s:3:"變";s:3:"贈";s:3:"贈";s:3:"輸";s:3:"輸";s:3:"遲";s:3:"遲";s:3:"醙";s:3:"醙";s:3:"鉶";s:3:"鉶";s:3:"陼";s:3:"陼";s:3:"難";s:3:"難";s:3:"靖";s:3:"靖";s:3:"韛";s:3:"韛";s:3:"響";s:3:"響";s:3:"頋";s:3:"頋";s:3:"頻";s:3:"頻";s:3:"鬒";s:3:"鬒";s:3:"龜";s:3:"龜";s:3:"𢡊";s:4:"𢡊";s:3:"𢡄";s:4:"𢡄";s:3:"𣏕";s:4:"𣏕";s:3:"㮝";s:3:"㮝";s:3:"䀘";s:3:"䀘";s:3:"䀹";s:3:"䀹";s:3:"𥉉";s:4:"𥉉";s:3:"𥳐";s:4:"𥳐";s:3:"𧻓";s:4:"𧻓";s:3:"齃";s:3:"齃";s:3:"龎";s:3:"龎";s:3:"ﬀ";s:2:"ff";s:3:"ﬁ";s:2:"fi";s:3:"ﬂ";s:2:"fl";s:3:"ﬃ";s:3:"ffi";s:3:"ﬄ";s:3:"ffl";s:3:"ﬅ";s:2:"st";s:3:"ﬆ";s:2:"st";s:3:"ﬓ";s:4:"մն";s:3:"ﬔ";s:4:"մե";s:3:"ﬕ";s:4:"մի";s:3:"ﬖ";s:4:"վն";s:3:"ﬗ";s:4:"մխ";s:3:"יִ";s:4:"יִ";s:3:"ײַ";s:4:"ײַ";s:3:"ﬠ";s:2:"ע";s:3:"ﬡ";s:2:"א";s:3:"ﬢ";s:2:"ד";s:3:"ﬣ";s:2:"ה";s:3:"ﬤ";s:2:"כ";s:3:"ﬥ";s:2:"ל";s:3:"ﬦ";s:2:"ם";s:3:"ﬧ";s:2:"ר";s:3:"ﬨ";s:2:"ת";s:3:"﬩";s:1:"+";s:3:"שׁ";s:4:"שׁ";s:3:"שׂ";s:4:"שׂ";s:3:"שּׁ";s:6:"שּׁ";s:3:"שּׂ";s:6:"שּׂ";s:3:"אַ";s:4:"אַ";s:3:"אָ";s:4:"אָ";s:3:"אּ";s:4:"אּ";s:3:"בּ";s:4:"בּ";s:3:"גּ";s:4:"גּ";s:3:"דּ";s:4:"דּ";s:3:"הּ";s:4:"הּ";s:3:"וּ";s:4:"וּ";s:3:"זּ";s:4:"זּ";s:3:"טּ";s:4:"טּ";s:3:"יּ";s:4:"יּ";s:3:"ךּ";s:4:"ךּ";s:3:"כּ";s:4:"כּ";s:3:"לּ";s:4:"לּ";s:3:"מּ";s:4:"מּ";s:3:"נּ";s:4:"נּ";s:3:"סּ";s:4:"סּ";s:3:"ףּ";s:4:"ףּ";s:3:"פּ";s:4:"פּ";s:3:"צּ";s:4:"צּ";s:3:"קּ";s:4:"קּ";s:3:"רּ";s:4:"רּ";s:3:"שּ";s:4:"שּ";s:3:"תּ";s:4:"תּ";s:3:"וֹ";s:4:"וֹ";s:3:"בֿ";s:4:"בֿ";s:3:"כֿ";s:4:"כֿ";s:3:"פֿ";s:4:"פֿ";s:3:"ﭏ";s:4:"אל";s:3:"ﭐ";s:2:"ٱ";s:3:"ﭑ";s:2:"ٱ";s:3:"ﭒ";s:2:"ٻ";s:3:"ﭓ";s:2:"ٻ";s:3:"ﭔ";s:2:"ٻ";s:3:"ﭕ";s:2:"ٻ";s:3:"ﭖ";s:2:"پ";s:3:"ﭗ";s:2:"پ";s:3:"ﭘ";s:2:"پ";s:3:"ﭙ";s:2:"پ";s:3:"ﭚ";s:2:"ڀ";s:3:"ﭛ";s:2:"ڀ";s:3:"ﭜ";s:2:"ڀ";s:3:"ﭝ";s:2:"ڀ";s:3:"ﭞ";s:2:"ٺ";s:3:"ﭟ";s:2:"ٺ";s:3:"ﭠ";s:2:"ٺ";s:3:"ﭡ";s:2:"ٺ";s:3:"ﭢ";s:2:"ٿ";s:3:"ﭣ";s:2:"ٿ";s:3:"ﭤ";s:2:"ٿ";s:3:"ﭥ";s:2:"ٿ";s:3:"ﭦ";s:2:"ٹ";s:3:"ﭧ";s:2:"ٹ";s:3:"ﭨ";s:2:"ٹ";s:3:"ﭩ";s:2:"ٹ";s:3:"ﭪ";s:2:"ڤ";s:3:"ﭫ";s:2:"ڤ";s:3:"ﭬ";s:2:"ڤ";s:3:"ﭭ";s:2:"ڤ";s:3:"ﭮ";s:2:"ڦ";s:3:"ﭯ";s:2:"ڦ";s:3:"ﭰ";s:2:"ڦ";s:3:"ﭱ";s:2:"ڦ";s:3:"ﭲ";s:2:"ڄ";s:3:"ﭳ";s:2:"ڄ";s:3:"ﭴ";s:2:"ڄ";s:3:"ﭵ";s:2:"ڄ";s:3:"ﭶ";s:2:"ڃ";s:3:"ﭷ";s:2:"ڃ";s:3:"ﭸ";s:2:"ڃ";s:3:"ﭹ";s:2:"ڃ";s:3:"ﭺ";s:2:"چ";s:3:"ﭻ";s:2:"چ";s:3:"ﭼ";s:2:"چ";s:3:"ﭽ";s:2:"چ";s:3:"ﭾ";s:2:"ڇ";s:3:"ﭿ";s:2:"ڇ";s:3:"ﮀ";s:2:"ڇ";s:3:"ﮁ";s:2:"ڇ";s:3:"ﮂ";s:2:"ڍ";s:3:"ﮃ";s:2:"ڍ";s:3:"ﮄ";s:2:"ڌ";s:3:"ﮅ";s:2:"ڌ";s:3:"ﮆ";s:2:"ڎ";s:3:"ﮇ";s:2:"ڎ";s:3:"ﮈ";s:2:"ڈ";s:3:"ﮉ";s:2:"ڈ";s:3:"ﮊ";s:2:"ژ";s:3:"ﮋ";s:2:"ژ";s:3:"ﮌ";s:2:"ڑ";s:3:"ﮍ";s:2:"ڑ";s:3:"ﮎ";s:2:"ک";s:3:"ﮏ";s:2:"ک";s:3:"ﮐ";s:2:"ک";s:3:"ﮑ";s:2:"ک";s:3:"ﮒ";s:2:"گ";s:3:"ﮓ";s:2:"گ";s:3:"ﮔ";s:2:"گ";s:3:"ﮕ";s:2:"گ";s:3:"ﮖ";s:2:"ڳ";s:3:"ﮗ";s:2:"ڳ";s:3:"ﮘ";s:2:"ڳ";s:3:"ﮙ";s:2:"ڳ";s:3:"ﮚ";s:2:"ڱ";s:3:"ﮛ";s:2:"ڱ";s:3:"ﮜ";s:2:"ڱ";s:3:"ﮝ";s:2:"ڱ";s:3:"ﮞ";s:2:"ں";s:3:"ﮟ";s:2:"ں";s:3:"ﮠ";s:2:"ڻ";s:3:"ﮡ";s:2:"ڻ";s:3:"ﮢ";s:2:"ڻ";s:3:"ﮣ";s:2:"ڻ";s:3:"ﮤ";s:4:"ۀ";s:3:"ﮥ";s:4:"ۀ";s:3:"ﮦ";s:2:"ہ";s:3:"ﮧ";s:2:"ہ";s:3:"ﮨ";s:2:"ہ";s:3:"ﮩ";s:2:"ہ";s:3:"ﮪ";s:2:"ھ";s:3:"ﮫ";s:2:"ھ";s:3:"ﮬ";s:2:"ھ";s:3:"ﮭ";s:2:"ھ";s:3:"ﮮ";s:2:"ے";s:3:"ﮯ";s:2:"ے";s:3:"ﮰ";s:4:"ۓ";s:3:"ﮱ";s:4:"ۓ";s:3:"ﯓ";s:2:"ڭ";s:3:"ﯔ";s:2:"ڭ";s:3:"ﯕ";s:2:"ڭ";s:3:"ﯖ";s:2:"ڭ";s:3:"ﯗ";s:2:"ۇ";s:3:"ﯘ";s:2:"ۇ";s:3:"ﯙ";s:2:"ۆ";s:3:"ﯚ";s:2:"ۆ";s:3:"ﯛ";s:2:"ۈ";s:3:"ﯜ";s:2:"ۈ";s:3:"ﯝ";s:4:"ۇٴ";s:3:"ﯞ";s:2:"ۋ";s:3:"ﯟ";s:2:"ۋ";s:3:"ﯠ";s:2:"ۅ";s:3:"ﯡ";s:2:"ۅ";s:3:"ﯢ";s:2:"ۉ";s:3:"ﯣ";s:2:"ۉ";s:3:"ﯤ";s:2:"ې";s:3:"ﯥ";s:2:"ې";s:3:"ﯦ";s:2:"ې";s:3:"ﯧ";s:2:"ې";s:3:"ﯨ";s:2:"ى";s:3:"ﯩ";s:2:"ى";s:3:"ﯪ";s:6:"ئا";s:3:"ﯫ";s:6:"ئا";s:3:"ﯬ";s:6:"ئە";s:3:"ﯭ";s:6:"ئە";s:3:"ﯮ";s:6:"ئو";s:3:"ﯯ";s:6:"ئو";s:3:"ﯰ";s:6:"ئۇ";s:3:"ﯱ";s:6:"ئۇ";s:3:"ﯲ";s:6:"ئۆ";s:3:"ﯳ";s:6:"ئۆ";s:3:"ﯴ";s:6:"ئۈ";s:3:"ﯵ";s:6:"ئۈ";s:3:"ﯶ";s:6:"ئې";s:3:"ﯷ";s:6:"ئې";s:3:"ﯸ";s:6:"ئې";s:3:"ﯹ";s:6:"ئى";s:3:"ﯺ";s:6:"ئى";s:3:"ﯻ";s:6:"ئى";s:3:"ﯼ";s:2:"ی";s:3:"ﯽ";s:2:"ی";s:3:"ﯾ";s:2:"ی";s:3:"ﯿ";s:2:"ی";s:3:"ﰀ";s:6:"ئج";s:3:"ﰁ";s:6:"ئح";s:3:"ﰂ";s:6:"ئم";s:3:"ﰃ";s:6:"ئى";s:3:"ﰄ";s:6:"ئي";s:3:"ﰅ";s:4:"بج";s:3:"ﰆ";s:4:"بح";s:3:"ﰇ";s:4:"بخ";s:3:"ﰈ";s:4:"بم";s:3:"ﰉ";s:4:"بى";s:3:"ﰊ";s:4:"بي";s:3:"ﰋ";s:4:"تج";s:3:"ﰌ";s:4:"تح";s:3:"ﰍ";s:4:"تخ";s:3:"ﰎ";s:4:"تم";s:3:"ﰏ";s:4:"تى";s:3:"ﰐ";s:4:"تي";s:3:"ﰑ";s:4:"ثج";s:3:"ﰒ";s:4:"ثم";s:3:"ﰓ";s:4:"ثى";s:3:"ﰔ";s:4:"ثي";s:3:"ﰕ";s:4:"جح";s:3:"ﰖ";s:4:"جم";s:3:"ﰗ";s:4:"حج";s:3:"ﰘ";s:4:"حم";s:3:"ﰙ";s:4:"خج";s:3:"ﰚ";s:4:"خح";s:3:"ﰛ";s:4:"خم";s:3:"ﰜ";s:4:"سج";s:3:"ﰝ";s:4:"سح";s:3:"ﰞ";s:4:"سخ";s:3:"ﰟ";s:4:"سم";s:3:"ﰠ";s:4:"صح";s:3:"ﰡ";s:4:"صم";s:3:"ﰢ";s:4:"ضج";s:3:"ﰣ";s:4:"ضح";s:3:"ﰤ";s:4:"ضخ";s:3:"ﰥ";s:4:"ضم";s:3:"ﰦ";s:4:"طح";s:3:"ﰧ";s:4:"طم";s:3:"ﰨ";s:4:"ظم";s:3:"ﰩ";s:4:"عج";s:3:"ﰪ";s:4:"عم";s:3:"ﰫ";s:4:"غج";s:3:"ﰬ";s:4:"غم";s:3:"ﰭ";s:4:"فج";s:3:"ﰮ";s:4:"فح";s:3:"ﰯ";s:4:"فخ";s:3:"ﰰ";s:4:"فم";s:3:"ﰱ";s:4:"فى";s:3:"ﰲ";s:4:"في";s:3:"ﰳ";s:4:"قح";s:3:"ﰴ";s:4:"قم";s:3:"ﰵ";s:4:"قى";s:3:"ﰶ";s:4:"قي";s:3:"ﰷ";s:4:"كا";s:3:"ﰸ";s:4:"كج";s:3:"ﰹ";s:4:"كح";s:3:"ﰺ";s:4:"كخ";s:3:"ﰻ";s:4:"كل";s:3:"ﰼ";s:4:"كم";s:3:"ﰽ";s:4:"كى";s:3:"ﰾ";s:4:"كي";s:3:"ﰿ";s:4:"لج";s:3:"ﱀ";s:4:"لح";s:3:"ﱁ";s:4:"لخ";s:3:"ﱂ";s:4:"لم";s:3:"ﱃ";s:4:"لى";s:3:"ﱄ";s:4:"لي";s:3:"ﱅ";s:4:"مج";s:3:"ﱆ";s:4:"مح";s:3:"ﱇ";s:4:"مخ";s:3:"ﱈ";s:4:"مم";s:3:"ﱉ";s:4:"مى";s:3:"ﱊ";s:4:"مي";s:3:"ﱋ";s:4:"نج";s:3:"ﱌ";s:4:"نح";s:3:"ﱍ";s:4:"نخ";s:3:"ﱎ";s:4:"نم";s:3:"ﱏ";s:4:"نى";s:3:"ﱐ";s:4:"ني";s:3:"ﱑ";s:4:"هج";s:3:"ﱒ";s:4:"هم";s:3:"ﱓ";s:4:"هى";s:3:"ﱔ";s:4:"هي";s:3:"ﱕ";s:4:"يج";s:3:"ﱖ";s:4:"يح";s:3:"ﱗ";s:4:"يخ";s:3:"ﱘ";s:4:"يم";s:3:"ﱙ";s:4:"يى";s:3:"ﱚ";s:4:"يي";s:3:"ﱛ";s:4:"ذٰ";s:3:"ﱜ";s:4:"رٰ";s:3:"ﱝ";s:4:"ىٰ";s:3:"ﱞ";s:5:" ٌّ";s:3:"ﱟ";s:5:" ٍّ";s:3:"ﱠ";s:5:" َّ";s:3:"ﱡ";s:5:" ُّ";s:3:"ﱢ";s:5:" ِّ";s:3:"ﱣ";s:5:" ّٰ";s:3:"ﱤ";s:6:"ئر";s:3:"ﱥ";s:6:"ئز";s:3:"ﱦ";s:6:"ئم";s:3:"ﱧ";s:6:"ئن";s:3:"ﱨ";s:6:"ئى";s:3:"ﱩ";s:6:"ئي";s:3:"ﱪ";s:4:"بر";s:3:"ﱫ";s:4:"بز";s:3:"ﱬ";s:4:"بم";s:3:"ﱭ";s:4:"بن";s:3:"ﱮ";s:4:"بى";s:3:"ﱯ";s:4:"بي";s:3:"ﱰ";s:4:"تر";s:3:"ﱱ";s:4:"تز";s:3:"ﱲ";s:4:"تم";s:3:"ﱳ";s:4:"تن";s:3:"ﱴ";s:4:"تى";s:3:"ﱵ";s:4:"تي";s:3:"ﱶ";s:4:"ثر";s:3:"ﱷ";s:4:"ثز";s:3:"ﱸ";s:4:"ثم";s:3:"ﱹ";s:4:"ثن";s:3:"ﱺ";s:4:"ثى";s:3:"ﱻ";s:4:"ثي";s:3:"ﱼ";s:4:"فى";s:3:"ﱽ";s:4:"في";s:3:"ﱾ";s:4:"قى";s:3:"ﱿ";s:4:"قي";s:3:"ﲀ";s:4:"كا";s:3:"ﲁ";s:4:"كل";s:3:"ﲂ";s:4:"كم";s:3:"ﲃ";s:4:"كى";s:3:"ﲄ";s:4:"كي";s:3:"ﲅ";s:4:"لم";s:3:"ﲆ";s:4:"لى";s:3:"ﲇ";s:4:"لي";s:3:"ﲈ";s:4:"ما";s:3:"ﲉ";s:4:"مم";s:3:"ﲊ";s:4:"نر";s:3:"ﲋ";s:4:"نز";s:3:"ﲌ";s:4:"نم";s:3:"ﲍ";s:4:"نن";s:3:"ﲎ";s:4:"نى";s:3:"ﲏ";s:4:"ني";s:3:"ﲐ";s:4:"ىٰ";s:3:"ﲑ";s:4:"ير";s:3:"ﲒ";s:4:"يز";s:3:"ﲓ";s:4:"يم";s:3:"ﲔ";s:4:"ين";s:3:"ﲕ";s:4:"يى";s:3:"ﲖ";s:4:"يي";s:3:"ﲗ";s:6:"ئج";s:3:"ﲘ";s:6:"ئح";s:3:"ﲙ";s:6:"ئخ";s:3:"ﲚ";s:6:"ئم";s:3:"ﲛ";s:6:"ئه";s:3:"ﲜ";s:4:"بج";s:3:"ﲝ";s:4:"بح";s:3:"ﲞ";s:4:"بخ";s:3:"ﲟ";s:4:"بم";s:3:"ﲠ";s:4:"به";s:3:"ﲡ";s:4:"تج";s:3:"ﲢ";s:4:"تح";s:3:"ﲣ";s:4:"تخ";s:3:"ﲤ";s:4:"تم";s:3:"ﲥ";s:4:"ته";s:3:"ﲦ";s:4:"ثم";s:3:"ﲧ";s:4:"جح";s:3:"ﲨ";s:4:"جم";s:3:"ﲩ";s:4:"حج";s:3:"ﲪ";s:4:"حم";s:3:"ﲫ";s:4:"خج";s:3:"ﲬ";s:4:"خم";s:3:"ﲭ";s:4:"سج";s:3:"ﲮ";s:4:"سح";s:3:"ﲯ";s:4:"سخ";s:3:"ﲰ";s:4:"سم";s:3:"ﲱ";s:4:"صح";s:3:"ﲲ";s:4:"صخ";s:3:"ﲳ";s:4:"صم";s:3:"ﲴ";s:4:"ضج";s:3:"ﲵ";s:4:"ضح";s:3:"ﲶ";s:4:"ضخ";s:3:"ﲷ";s:4:"ضم";s:3:"ﲸ";s:4:"طح";s:3:"ﲹ";s:4:"ظم";s:3:"ﲺ";s:4:"عج";s:3:"ﲻ";s:4:"عم";s:3:"ﲼ";s:4:"غج";s:3:"ﲽ";s:4:"غم";s:3:"ﲾ";s:4:"فج";s:3:"ﲿ";s:4:"فح";s:3:"ﳀ";s:4:"فخ";s:3:"ﳁ";s:4:"فم";s:3:"ﳂ";s:4:"قح";s:3:"ﳃ";s:4:"قم";s:3:"ﳄ";s:4:"كج";s:3:"ﳅ";s:4:"كح";s:3:"ﳆ";s:4:"كخ";s:3:"ﳇ";s:4:"كل";s:3:"ﳈ";s:4:"كم";s:3:"ﳉ";s:4:"لج";s:3:"ﳊ";s:4:"لح";s:3:"ﳋ";s:4:"لخ";s:3:"ﳌ";s:4:"لم";s:3:"ﳍ";s:4:"له";s:3:"ﳎ";s:4:"مج";s:3:"ﳏ";s:4:"مح";s:3:"ﳐ";s:4:"مخ";s:3:"ﳑ";s:4:"مم";s:3:"ﳒ";s:4:"نج";s:3:"ﳓ";s:4:"نح";s:3:"ﳔ";s:4:"نخ";s:3:"ﳕ";s:4:"نم";s:3:"ﳖ";s:4:"نه";s:3:"ﳗ";s:4:"هج";s:3:"ﳘ";s:4:"هم";s:3:"ﳙ";s:4:"هٰ";s:3:"ﳚ";s:4:"يج";s:3:"ﳛ";s:4:"يح";s:3:"ﳜ";s:4:"يخ";s:3:"ﳝ";s:4:"يم";s:3:"ﳞ";s:4:"يه";s:3:"ﳟ";s:6:"ئم";s:3:"ﳠ";s:6:"ئه";s:3:"ﳡ";s:4:"بم";s:3:"ﳢ";s:4:"به";s:3:"ﳣ";s:4:"تم";s:3:"ﳤ";s:4:"ته";s:3:"ﳥ";s:4:"ثم";s:3:"ﳦ";s:4:"ثه";s:3:"ﳧ";s:4:"سم";s:3:"ﳨ";s:4:"سه";s:3:"ﳩ";s:4:"شم";s:3:"ﳪ";s:4:"شه";s:3:"ﳫ";s:4:"كل";s:3:"ﳬ";s:4:"كم";s:3:"ﳭ";s:4:"لم";s:3:"ﳮ";s:4:"نم";s:3:"ﳯ";s:4:"نه";s:3:"ﳰ";s:4:"يم";s:3:"ﳱ";s:4:"يه";s:3:"ﳲ";s:6:"ـَّ";s:3:"ﳳ";s:6:"ـُّ";s:3:"ﳴ";s:6:"ـِّ";s:3:"ﳵ";s:4:"طى";s:3:"ﳶ";s:4:"طي";s:3:"ﳷ";s:4:"عى";s:3:"ﳸ";s:4:"عي";s:3:"ﳹ";s:4:"غى";s:3:"ﳺ";s:4:"غي";s:3:"ﳻ";s:4:"سى";s:3:"ﳼ";s:4:"سي";s:3:"ﳽ";s:4:"شى";s:3:"ﳾ";s:4:"شي";s:3:"ﳿ";s:4:"حى";s:3:"ﴀ";s:4:"حي";s:3:"ﴁ";s:4:"جى";s:3:"ﴂ";s:4:"جي";s:3:"ﴃ";s:4:"خى";s:3:"ﴄ";s:4:"خي";s:3:"ﴅ";s:4:"صى";s:3:"ﴆ";s:4:"صي";s:3:"ﴇ";s:4:"ضى";s:3:"ﴈ";s:4:"ضي";s:3:"ﴉ";s:4:"شج";s:3:"ﴊ";s:4:"شح";s:3:"ﴋ";s:4:"شخ";s:3:"ﴌ";s:4:"شم";s:3:"ﴍ";s:4:"شر";s:3:"ﴎ";s:4:"سر";s:3:"ﴏ";s:4:"صر";s:3:"ﴐ";s:4:"ضر";s:3:"ﴑ";s:4:"طى";s:3:"ﴒ";s:4:"طي";s:3:"ﴓ";s:4:"عى";s:3:"ﴔ";s:4:"عي";s:3:"ﴕ";s:4:"غى";s:3:"ﴖ";s:4:"غي";s:3:"ﴗ";s:4:"سى";s:3:"ﴘ";s:4:"سي";s:3:"ﴙ";s:4:"شى";s:3:"ﴚ";s:4:"شي";s:3:"ﴛ";s:4:"حى";s:3:"ﴜ";s:4:"حي";s:3:"ﴝ";s:4:"جى";s:3:"ﴞ";s:4:"جي";s:3:"ﴟ";s:4:"خى";s:3:"ﴠ";s:4:"خي";s:3:"ﴡ";s:4:"صى";s:3:"ﴢ";s:4:"صي";s:3:"ﴣ";s:4:"ضى";s:3:"ﴤ";s:4:"ضي";s:3:"ﴥ";s:4:"شج";s:3:"ﴦ";s:4:"شح";s:3:"ﴧ";s:4:"شخ";s:3:"ﴨ";s:4:"شم";s:3:"ﴩ";s:4:"شر";s:3:"ﴪ";s:4:"سر";s:3:"ﴫ";s:4:"صر";s:3:"ﴬ";s:4:"ضر";s:3:"ﴭ";s:4:"شج";s:3:"ﴮ";s:4:"شح";s:3:"ﴯ";s:4:"شخ";s:3:"ﴰ";s:4:"شم";s:3:"ﴱ";s:4:"سه";s:3:"ﴲ";s:4:"شه";s:3:"ﴳ";s:4:"طم";s:3:"ﴴ";s:4:"سج";s:3:"ﴵ";s:4:"سح";s:3:"ﴶ";s:4:"سخ";s:3:"ﴷ";s:4:"شج";s:3:"ﴸ";s:4:"شح";s:3:"ﴹ";s:4:"شخ";s:3:"ﴺ";s:4:"طم";s:3:"ﴻ";s:4:"ظم";s:3:"ﴼ";s:4:"اً";s:3:"ﴽ";s:4:"اً";s:3:"ﵐ";s:6:"تجم";s:3:"ﵑ";s:6:"تحج";s:3:"ﵒ";s:6:"تحج";s:3:"ﵓ";s:6:"تحم";s:3:"ﵔ";s:6:"تخم";s:3:"ﵕ";s:6:"تمج";s:3:"ﵖ";s:6:"تمح";s:3:"ﵗ";s:6:"تمخ";s:3:"ﵘ";s:6:"جمح";s:3:"ﵙ";s:6:"جمح";s:3:"ﵚ";s:6:"حمي";s:3:"ﵛ";s:6:"حمى";s:3:"ﵜ";s:6:"سحج";s:3:"ﵝ";s:6:"سجح";s:3:"ﵞ";s:6:"سجى";s:3:"ﵟ";s:6:"سمح";s:3:"ﵠ";s:6:"سمح";s:3:"ﵡ";s:6:"سمج";s:3:"ﵢ";s:6:"سمم";s:3:"ﵣ";s:6:"سمم";s:3:"ﵤ";s:6:"صحح";s:3:"ﵥ";s:6:"صحح";s:3:"ﵦ";s:6:"صمم";s:3:"ﵧ";s:6:"شحم";s:3:"ﵨ";s:6:"شحم";s:3:"ﵩ";s:6:"شجي";s:3:"ﵪ";s:6:"شمخ";s:3:"ﵫ";s:6:"شمخ";s:3:"ﵬ";s:6:"شمم";s:3:"ﵭ";s:6:"شمم";s:3:"ﵮ";s:6:"ضحى";s:3:"ﵯ";s:6:"ضخم";s:3:"ﵰ";s:6:"ضخم";s:3:"ﵱ";s:6:"طمح";s:3:"ﵲ";s:6:"طمح";s:3:"ﵳ";s:6:"طمم";s:3:"ﵴ";s:6:"طمي";s:3:"ﵵ";s:6:"عجم";s:3:"ﵶ";s:6:"عمم";s:3:"ﵷ";s:6:"عمم";s:3:"ﵸ";s:6:"عمى";s:3:"ﵹ";s:6:"غمم";s:3:"ﵺ";s:6:"غمي";s:3:"ﵻ";s:6:"غمى";s:3:"ﵼ";s:6:"فخم";s:3:"ﵽ";s:6:"فخم";s:3:"ﵾ";s:6:"قمح";s:3:"ﵿ";s:6:"قمم";s:3:"ﶀ";s:6:"لحم";s:3:"ﶁ";s:6:"لحي";s:3:"ﶂ";s:6:"لحى";s:3:"ﶃ";s:6:"لجج";s:3:"ﶄ";s:6:"لجج";s:3:"ﶅ";s:6:"لخم";s:3:"ﶆ";s:6:"لخم";s:3:"ﶇ";s:6:"لمح";s:3:"ﶈ";s:6:"لمح";s:3:"ﶉ";s:6:"محج";s:3:"ﶊ";s:6:"محم";s:3:"ﶋ";s:6:"محي";s:3:"ﶌ";s:6:"مجح";s:3:"ﶍ";s:6:"مجم";s:3:"ﶎ";s:6:"مخج";s:3:"ﶏ";s:6:"مخم";s:3:"ﶒ";s:6:"مجخ";s:3:"ﶓ";s:6:"همج";s:3:"ﶔ";s:6:"همم";s:3:"ﶕ";s:6:"نحم";s:3:"ﶖ";s:6:"نحى";s:3:"ﶗ";s:6:"نجم";s:3:"ﶘ";s:6:"نجم";s:3:"ﶙ";s:6:"نجى";s:3:"ﶚ";s:6:"نمي";s:3:"ﶛ";s:6:"نمى";s:3:"ﶜ";s:6:"يمم";s:3:"ﶝ";s:6:"يمم";s:3:"ﶞ";s:6:"بخي";s:3:"ﶟ";s:6:"تجي";s:3:"ﶠ";s:6:"تجى";s:3:"ﶡ";s:6:"تخي";s:3:"ﶢ";s:6:"تخى";s:3:"ﶣ";s:6:"تمي";s:3:"ﶤ";s:6:"تمى";s:3:"ﶥ";s:6:"جمي";s:3:"ﶦ";s:6:"جحى";s:3:"ﶧ";s:6:"جمى";s:3:"ﶨ";s:6:"سخى";s:3:"ﶩ";s:6:"صحي";s:3:"ﶪ";s:6:"شحي";s:3:"ﶫ";s:6:"ضحي";s:3:"ﶬ";s:6:"لجي";s:3:"ﶭ";s:6:"لمي";s:3:"ﶮ";s:6:"يحي";s:3:"ﶯ";s:6:"يجي";s:3:"ﶰ";s:6:"يمي";s:3:"ﶱ";s:6:"ممي";s:3:"ﶲ";s:6:"قمي";s:3:"ﶳ";s:6:"نحي";s:3:"ﶴ";s:6:"قمح";s:3:"ﶵ";s:6:"لحم";s:3:"ﶶ";s:6:"عمي";s:3:"ﶷ";s:6:"كمي";s:3:"ﶸ";s:6:"نجح";s:3:"ﶹ";s:6:"مخي";s:3:"ﶺ";s:6:"لجم";s:3:"ﶻ";s:6:"كمم";s:3:"ﶼ";s:6:"لجم";s:3:"ﶽ";s:6:"نجح";s:3:"ﶾ";s:6:"جحي";s:3:"ﶿ";s:6:"حجي";s:3:"ﷀ";s:6:"مجي";s:3:"ﷁ";s:6:"فمي";s:3:"ﷂ";s:6:"بحي";s:3:"ﷃ";s:6:"كمم";s:3:"ﷄ";s:6:"عجم";s:3:"ﷅ";s:6:"صمم";s:3:"ﷆ";s:6:"سخي";s:3:"ﷇ";s:6:"نجي";s:3:"ﷰ";s:6:"صلے";s:3:"ﷱ";s:6:"قلے";s:3:"ﷲ";s:8:"الله";s:3:"ﷳ";s:8:"اكبر";s:3:"ﷴ";s:8:"محمد";s:3:"ﷵ";s:8:"صلعم";s:3:"ﷶ";s:8:"رسول";s:3:"ﷷ";s:8:"عليه";s:3:"ﷸ";s:8:"وسلم";s:3:"ﷹ";s:6:"صلى";s:3:"ﷺ";s:33:"صلى الله عليه وسلم";s:3:"ﷻ";s:15:"جل جلاله";s:3:"﷼";s:8:"ریال";s:3:"︐";s:1:",";s:3:"︑";s:3:"、";s:3:"︒";s:3:"。";s:3:"︓";s:1:":";s:3:"︔";s:1:";";s:3:"︕";s:1:"!";s:3:"︖";s:1:"?";s:3:"︗";s:3:"〖";s:3:"︘";s:3:"〗";s:3:"︙";s:3:"...";s:3:"︰";s:2:"..";s:3:"︱";s:3:"—";s:3:"︲";s:3:"–";s:3:"︳";s:1:"_";s:3:"︴";s:1:"_";s:3:"︵";s:1:"(";s:3:"︶";s:1:")";s:3:"︷";s:1:"{";s:3:"︸";s:1:"}";s:3:"︹";s:3:"〔";s:3:"︺";s:3:"〕";s:3:"︻";s:3:"【";s:3:"︼";s:3:"】";s:3:"︽";s:3:"《";s:3:"︾";s:3:"》";s:3:"︿";s:3:"〈";s:3:"﹀";s:3:"〉";s:3:"﹁";s:3:"「";s:3:"﹂";s:3:"」";s:3:"﹃";s:3:"『";s:3:"﹄";s:3:"』";s:3:"﹇";s:1:"[";s:3:"﹈";s:1:"]";s:3:"﹉";s:3:" ̅";s:3:"﹊";s:3:" ̅";s:3:"﹋";s:3:" ̅";s:3:"﹌";s:3:" ̅";s:3:"﹍";s:1:"_";s:3:"﹎";s:1:"_";s:3:"﹏";s:1:"_";s:3:"﹐";s:1:",";s:3:"﹑";s:3:"、";s:3:"﹒";s:1:".";s:3:"﹔";s:1:";";s:3:"﹕";s:1:":";s:3:"﹖";s:1:"?";s:3:"﹗";s:1:"!";s:3:"﹘";s:3:"—";s:3:"﹙";s:1:"(";s:3:"﹚";s:1:")";s:3:"﹛";s:1:"{";s:3:"﹜";s:1:"}";s:3:"﹝";s:3:"〔";s:3:"﹞";s:3:"〕";s:3:"﹟";s:1:"#";s:3:"﹠";s:1:"&";s:3:"﹡";s:1:"*";s:3:"﹢";s:1:"+";s:3:"﹣";s:1:"-";s:3:"﹤";s:1:"<";s:3:"﹥";s:1:">";s:3:"﹦";s:1:"=";s:3:"﹨";s:1:"\\";s:3:"﹩";s:1:"$";s:3:"﹪";s:1:"%";s:3:"﹫";s:1:"@";s:3:"ﹰ";s:3:" ً";s:3:"ﹱ";s:4:"ـً";s:3:"ﹲ";s:3:" ٌ";s:3:"ﹴ";s:3:" ٍ";s:3:"ﹶ";s:3:" َ";s:3:"ﹷ";s:4:"ـَ";s:3:"ﹸ";s:3:" ُ";s:3:"ﹹ";s:4:"ـُ";s:3:"ﹺ";s:3:" ِ";s:3:"ﹻ";s:4:"ـِ";s:3:"ﹼ";s:3:" ّ";s:3:"ﹽ";s:4:"ـّ";s:3:"ﹾ";s:3:" ْ";s:3:"ﹿ";s:4:"ـْ";s:3:"ﺀ";s:2:"ء";s:3:"ﺁ";s:4:"آ";s:3:"ﺂ";s:4:"آ";s:3:"ﺃ";s:4:"أ";s:3:"ﺄ";s:4:"أ";s:3:"ﺅ";s:4:"ؤ";s:3:"ﺆ";s:4:"ؤ";s:3:"ﺇ";s:4:"إ";s:3:"ﺈ";s:4:"إ";s:3:"ﺉ";s:4:"ئ";s:3:"ﺊ";s:4:"ئ";s:3:"ﺋ";s:4:"ئ";s:3:"ﺌ";s:4:"ئ";s:3:"ﺍ";s:2:"ا";s:3:"ﺎ";s:2:"ا";s:3:"ﺏ";s:2:"ب";s:3:"ﺐ";s:2:"ب";s:3:"ﺑ";s:2:"ب";s:3:"ﺒ";s:2:"ب";s:3:"ﺓ";s:2:"ة";s:3:"ﺔ";s:2:"ة";s:3:"ﺕ";s:2:"ت";s:3:"ﺖ";s:2:"ت";s:3:"ﺗ";s:2:"ت";s:3:"ﺘ";s:2:"ت";s:3:"ﺙ";s:2:"ث";s:3:"ﺚ";s:2:"ث";s:3:"ﺛ";s:2:"ث";s:3:"ﺜ";s:2:"ث";s:3:"ﺝ";s:2:"ج";s:3:"ﺞ";s:2:"ج";s:3:"ﺟ";s:2:"ج";s:3:"ﺠ";s:2:"ج";s:3:"ﺡ";s:2:"ح";s:3:"ﺢ";s:2:"ح";s:3:"ﺣ";s:2:"ح";s:3:"ﺤ";s:2:"ح";s:3:"ﺥ";s:2:"خ";s:3:"ﺦ";s:2:"خ";s:3:"ﺧ";s:2:"خ";s:3:"ﺨ";s:2:"خ";s:3:"ﺩ";s:2:"د";s:3:"ﺪ";s:2:"د";s:3:"ﺫ";s:2:"ذ";s:3:"ﺬ";s:2:"ذ";s:3:"ﺭ";s:2:"ر";s:3:"ﺮ";s:2:"ر";s:3:"ﺯ";s:2:"ز";s:3:"ﺰ";s:2:"ز";s:3:"ﺱ";s:2:"س";s:3:"ﺲ";s:2:"س";s:3:"ﺳ";s:2:"س";s:3:"ﺴ";s:2:"س";s:3:"ﺵ";s:2:"ش";s:3:"ﺶ";s:2:"ش";s:3:"ﺷ";s:2:"ش";s:3:"ﺸ";s:2:"ش";s:3:"ﺹ";s:2:"ص";s:3:"ﺺ";s:2:"ص";s:3:"ﺻ";s:2:"ص";s:3:"ﺼ";s:2:"ص";s:3:"ﺽ";s:2:"ض";s:3:"ﺾ";s:2:"ض";s:3:"ﺿ";s:2:"ض";s:3:"ﻀ";s:2:"ض";s:3:"ﻁ";s:2:"ط";s:3:"ﻂ";s:2:"ط";s:3:"ﻃ";s:2:"ط";s:3:"ﻄ";s:2:"ط";s:3:"ﻅ";s:2:"ظ";s:3:"ﻆ";s:2:"ظ";s:3:"ﻇ";s:2:"ظ";s:3:"ﻈ";s:2:"ظ";s:3:"ﻉ";s:2:"ع";s:3:"ﻊ";s:2:"ع";s:3:"ﻋ";s:2:"ع";s:3:"ﻌ";s:2:"ع";s:3:"ﻍ";s:2:"غ";s:3:"ﻎ";s:2:"غ";s:3:"ﻏ";s:2:"غ";s:3:"ﻐ";s:2:"غ";s:3:"ﻑ";s:2:"ف";s:3:"ﻒ";s:2:"ف";s:3:"ﻓ";s:2:"ف";s:3:"ﻔ";s:2:"ف";s:3:"ﻕ";s:2:"ق";s:3:"ﻖ";s:2:"ق";s:3:"ﻗ";s:2:"ق";s:3:"ﻘ";s:2:"ق";s:3:"ﻙ";s:2:"ك";s:3:"ﻚ";s:2:"ك";s:3:"ﻛ";s:2:"ك";s:3:"ﻜ";s:2:"ك";s:3:"ﻝ";s:2:"ل";s:3:"ﻞ";s:2:"ل";s:3:"ﻟ";s:2:"ل";s:3:"ﻠ";s:2:"ل";s:3:"ﻡ";s:2:"م";s:3:"ﻢ";s:2:"م";s:3:"ﻣ";s:2:"م";s:3:"ﻤ";s:2:"م";s:3:"ﻥ";s:2:"ن";s:3:"ﻦ";s:2:"ن";s:3:"ﻧ";s:2:"ن";s:3:"ﻨ";s:2:"ن";s:3:"ﻩ";s:2:"ه";s:3:"ﻪ";s:2:"ه";s:3:"ﻫ";s:2:"ه";s:3:"ﻬ";s:2:"ه";s:3:"ﻭ";s:2:"و";s:3:"ﻮ";s:2:"و";s:3:"ﻯ";s:2:"ى";s:3:"ﻰ";s:2:"ى";s:3:"ﻱ";s:2:"ي";s:3:"ﻲ";s:2:"ي";s:3:"ﻳ";s:2:"ي";s:3:"ﻴ";s:2:"ي";s:3:"ﻵ";s:6:"لآ";s:3:"ﻶ";s:6:"لآ";s:3:"ﻷ";s:6:"لأ";s:3:"ﻸ";s:6:"لأ";s:3:"ﻹ";s:6:"لإ";s:3:"ﻺ";s:6:"لإ";s:3:"ﻻ";s:4:"لا";s:3:"ﻼ";s:4:"لا";s:3:"！";s:1:"!";s:3:"＂";s:1:""";s:3:"＃";s:1:"#";s:3:"＄";s:1:"$";s:3:"％";s:1:"%";s:3:"＆";s:1:"&";s:3:"＇";s:1:"\'";s:3:"（";s:1:"(";s:3:"）";s:1:")";s:3:"＊";s:1:"*";s:3:"＋";s:1:"+";s:3:"，";s:1:",";s:3:"－";s:1:"-";s:3:"．";s:1:".";s:3:"／";s:1:"/";s:3:"０";s:1:"0";s:3:"１";s:1:"1";s:3:"２";s:1:"2";s:3:"３";s:1:"3";s:3:"４";s:1:"4";s:3:"５";s:1:"5";s:3:"６";s:1:"6";s:3:"７";s:1:"7";s:3:"８";s:1:"8";s:3:"９";s:1:"9";s:3:"：";s:1:":";s:3:"；";s:1:";";s:3:"＜";s:1:"<";s:3:"＝";s:1:"=";s:3:"＞";s:1:">";s:3:"？";s:1:"?";s:3:"＠";s:1:"@";s:3:"Ａ";s:1:"A";s:3:"Ｂ";s:1:"B";s:3:"Ｃ";s:1:"C";s:3:"Ｄ";s:1:"D";s:3:"Ｅ";s:1:"E";s:3:"Ｆ";s:1:"F";s:3:"Ｇ";s:1:"G";s:3:"Ｈ";s:1:"H";s:3:"Ｉ";s:1:"I";s:3:"Ｊ";s:1:"J";s:3:"Ｋ";s:1:"K";s:3:"Ｌ";s:1:"L";s:3:"Ｍ";s:1:"M";s:3:"Ｎ";s:1:"N";s:3:"Ｏ";s:1:"O";s:3:"Ｐ";s:1:"P";s:3:"Ｑ";s:1:"Q";s:3:"Ｒ";s:1:"R";s:3:"Ｓ";s:1:"S";s:3:"Ｔ";s:1:"T";s:3:"Ｕ";s:1:"U";s:3:"Ｖ";s:1:"V";s:3:"Ｗ";s:1:"W";s:3:"Ｘ";s:1:"X";s:3:"Ｙ";s:1:"Y";s:3:"Ｚ";s:1:"Z";s:3:"［";s:1:"[";s:3:"＼";s:1:"\\";s:3:"］";s:1:"]";s:3:"＾";s:1:"^";s:3:"＿";s:1:"_";s:3:"｀";s:1:"`";s:3:"ａ";s:1:"a";s:3:"ｂ";s:1:"b";s:3:"ｃ";s:1:"c";s:3:"ｄ";s:1:"d";s:3:"ｅ";s:1:"e";s:3:"ｆ";s:1:"f";s:3:"ｇ";s:1:"g";s:3:"ｈ";s:1:"h";s:3:"ｉ";s:1:"i";s:3:"ｊ";s:1:"j";s:3:"ｋ";s:1:"k";s:3:"ｌ";s:1:"l";s:3:"ｍ";s:1:"m";s:3:"ｎ";s:1:"n";s:3:"ｏ";s:1:"o";s:3:"ｐ";s:1:"p";s:3:"ｑ";s:1:"q";s:3:"ｒ";s:1:"r";s:3:"ｓ";s:1:"s";s:3:"ｔ";s:1:"t";s:3:"ｕ";s:1:"u";s:3:"ｖ";s:1:"v";s:3:"ｗ";s:1:"w";s:3:"ｘ";s:1:"x";s:3:"ｙ";s:1:"y";s:3:"ｚ";s:1:"z";s:3:"｛";s:1:"{";s:3:"｜";s:1:"|";s:3:"｝";s:1:"}";s:3:"～";s:1:"~";s:3:"｟";s:3:"⦅";s:3:"｠";s:3:"⦆";s:3:"｡";s:3:"。";s:3:"｢";s:3:"「";s:3:"｣";s:3:"」";s:3:"､";s:3:"、";s:3:"･";s:3:"・";s:3:"ｦ";s:3:"ヲ";s:3:"ｧ";s:3:"ァ";s:3:"ｨ";s:3:"ィ";s:3:"ｩ";s:3:"ゥ";s:3:"ｪ";s:3:"ェ";s:3:"ｫ";s:3:"ォ";s:3:"ｬ";s:3:"ャ";s:3:"ｭ";s:3:"ュ";s:3:"ｮ";s:3:"ョ";s:3:"ｯ";s:3:"ッ";s:3:"ｰ";s:3:"ー";s:3:"ｱ";s:3:"ア";s:3:"ｲ";s:3:"イ";s:3:"ｳ";s:3:"ウ";s:3:"ｴ";s:3:"エ";s:3:"ｵ";s:3:"オ";s:3:"ｶ";s:3:"カ";s:3:"ｷ";s:3:"キ";s:3:"ｸ";s:3:"ク";s:3:"ｹ";s:3:"ケ";s:3:"ｺ";s:3:"コ";s:3:"ｻ";s:3:"サ";s:3:"ｼ";s:3:"シ";s:3:"ｽ";s:3:"ス";s:3:"ｾ";s:3:"セ";s:3:"ｿ";s:3:"ソ";s:3:"ﾀ";s:3:"タ";s:3:"ﾁ";s:3:"チ";s:3:"ﾂ";s:3:"ツ";s:3:"ﾃ";s:3:"テ";s:3:"ﾄ";s:3:"ト";s:3:"ﾅ";s:3:"ナ";s:3:"ﾆ";s:3:"ニ";s:3:"ﾇ";s:3:"ヌ";s:3:"ﾈ";s:3:"ネ";s:3:"ﾉ";s:3:"ノ";s:3:"ﾊ";s:3:"ハ";s:3:"ﾋ";s:3:"ヒ";s:3:"ﾌ";s:3:"フ";s:3:"ﾍ";s:3:"ヘ";s:3:"ﾎ";s:3:"ホ";s:3:"ﾏ";s:3:"マ";s:3:"ﾐ";s:3:"ミ";s:3:"ﾑ";s:3:"ム";s:3:"ﾒ";s:3:"メ";s:3:"ﾓ";s:3:"モ";s:3:"ﾔ";s:3:"ヤ";s:3:"ﾕ";s:3:"ユ";s:3:"ﾖ";s:3:"ヨ";s:3:"ﾗ";s:3:"ラ";s:3:"ﾘ";s:3:"リ";s:3:"ﾙ";s:3:"ル";s:3:"ﾚ";s:3:"レ";s:3:"ﾛ";s:3:"ロ";s:3:"ﾜ";s:3:"ワ";s:3:"ﾝ";s:3:"ン";s:3:"ﾞ";s:3:"゙";s:3:"ﾟ";s:3:"゚";s:3:"ﾠ";s:3:"ᅠ";s:3:"ﾡ";s:3:"ᄀ";s:3:"ﾢ";s:3:"ᄁ";s:3:"ﾣ";s:3:"ᆪ";s:3:"ﾤ";s:3:"ᄂ";s:3:"ﾥ";s:3:"ᆬ";s:3:"ﾦ";s:3:"ᆭ";s:3:"ﾧ";s:3:"ᄃ";s:3:"ﾨ";s:3:"ᄄ";s:3:"ﾩ";s:3:"ᄅ";s:3:"ﾪ";s:3:"ᆰ";s:3:"ﾫ";s:3:"ᆱ";s:3:"ﾬ";s:3:"ᆲ";s:3:"ﾭ";s:3:"ᆳ";s:3:"ﾮ";s:3:"ᆴ";s:3:"ﾯ";s:3:"ᆵ";s:3:"ﾰ";s:3:"ᄚ";s:3:"ﾱ";s:3:"ᄆ";s:3:"ﾲ";s:3:"ᄇ";s:3:"ﾳ";s:3:"ᄈ";s:3:"ﾴ";s:3:"ᄡ";s:3:"ﾵ";s:3:"ᄉ";s:3:"ﾶ";s:3:"ᄊ";s:3:"ﾷ";s:3:"ᄋ";s:3:"ﾸ";s:3:"ᄌ";s:3:"ﾹ";s:3:"ᄍ";s:3:"ﾺ";s:3:"ᄎ";s:3:"ﾻ";s:3:"ᄏ";s:3:"ﾼ";s:3:"ᄐ";s:3:"ﾽ";s:3:"ᄑ";s:3:"ﾾ";s:3:"ᄒ";s:3:"ￂ";s:3:"ᅡ";s:3:"ￃ";s:3:"ᅢ";s:3:"ￄ";s:3:"ᅣ";s:3:"ￅ";s:3:"ᅤ";s:3:"ￆ";s:3:"ᅥ";s:3:"ￇ";s:3:"ᅦ";s:3:"ￊ";s:3:"ᅧ";s:3:"ￋ";s:3:"ᅨ";s:3:"ￌ";s:3:"ᅩ";s:3:"ￍ";s:3:"ᅪ";s:3:"ￎ";s:3:"ᅫ";s:3:"ￏ";s:3:"ᅬ";s:3:"ￒ";s:3:"ᅭ";s:3:"ￓ";s:3:"ᅮ";s:3:"ￔ";s:3:"ᅯ";s:3:"ￕ";s:3:"ᅰ";s:3:"ￖ";s:3:"ᅱ";s:3:"ￗ";s:3:"ᅲ";s:3:"ￚ";s:3:"ᅳ";s:3:"ￛ";s:3:"ᅴ";s:3:"ￜ";s:3:"ᅵ";s:3:"￠";s:2:"¢";s:3:"￡";s:2:"£";s:3:"￢";s:2:"¬";s:3:"￣";s:3:" ̄";s:3:"￤";s:2:"¦";s:3:"￥";s:2:"¥";s:3:"￦";s:3:"₩";s:3:"￨";s:3:"│";s:3:"￩";s:3:"←";s:3:"￪";s:3:"↑";s:3:"￫";s:3:"→";s:3:"￬";s:3:"↓";s:3:"￭";s:3:"■";s:3:"￮";s:3:"○";s:4:"𝅗𝅥";s:8:"𝅗𝅥";s:4:"𝅘𝅥";s:8:"𝅘𝅥";s:4:"𝅘𝅥𝅮";s:12:"𝅘𝅥𝅮";s:4:"𝅘𝅥𝅯";s:12:"𝅘𝅥𝅯";s:4:"𝅘𝅥𝅰";s:12:"𝅘𝅥𝅰";s:4:"𝅘𝅥𝅱";s:12:"𝅘𝅥𝅱";s:4:"𝅘𝅥𝅲";s:12:"𝅘𝅥𝅲";s:4:"𝆹𝅥";s:8:"𝆹𝅥";s:4:"𝆺𝅥";s:8:"𝆺𝅥";s:4:"𝆹𝅥𝅮";s:12:"𝆹𝅥𝅮";s:4:"𝆺𝅥𝅮";s:12:"𝆺𝅥𝅮";s:4:"𝆹𝅥𝅯";s:12:"𝆹𝅥𝅯";s:4:"𝆺𝅥𝅯";s:12:"𝆺𝅥𝅯";s:4:"𝐀";s:1:"A";s:4:"𝐁";s:1:"B";s:4:"𝐂";s:1:"C";s:4:"𝐃";s:1:"D";s:4:"𝐄";s:1:"E";s:4:"𝐅";s:1:"F";s:4:"𝐆";s:1:"G";s:4:"𝐇";s:1:"H";s:4:"𝐈";s:1:"I";s:4:"𝐉";s:1:"J";s:4:"𝐊";s:1:"K";s:4:"𝐋";s:1:"L";s:4:"𝐌";s:1:"M";s:4:"𝐍";s:1:"N";s:4:"𝐎";s:1:"O";s:4:"𝐏";s:1:"P";s:4:"𝐐";s:1:"Q";s:4:"𝐑";s:1:"R";s:4:"𝐒";s:1:"S";s:4:"𝐓";s:1:"T";s:4:"𝐔";s:1:"U";s:4:"𝐕";s:1:"V";s:4:"𝐖";s:1:"W";s:4:"𝐗";s:1:"X";s:4:"𝐘";s:1:"Y";s:4:"𝐙";s:1:"Z";s:4:"𝐚";s:1:"a";s:4:"𝐛";s:1:"b";s:4:"𝐜";s:1:"c";s:4:"𝐝";s:1:"d";s:4:"𝐞";s:1:"e";s:4:"𝐟";s:1:"f";s:4:"𝐠";s:1:"g";s:4:"𝐡";s:1:"h";s:4:"𝐢";s:1:"i";s:4:"𝐣";s:1:"j";s:4:"𝐤";s:1:"k";s:4:"𝐥";s:1:"l";s:4:"𝐦";s:1:"m";s:4:"𝐧";s:1:"n";s:4:"𝐨";s:1:"o";s:4:"𝐩";s:1:"p";s:4:"𝐪";s:1:"q";s:4:"𝐫";s:1:"r";s:4:"𝐬";s:1:"s";s:4:"𝐭";s:1:"t";s:4:"𝐮";s:1:"u";s:4:"𝐯";s:1:"v";s:4:"𝐰";s:1:"w";s:4:"𝐱";s:1:"x";s:4:"𝐲";s:1:"y";s:4:"𝐳";s:1:"z";s:4:"𝐴";s:1:"A";s:4:"𝐵";s:1:"B";s:4:"𝐶";s:1:"C";s:4:"𝐷";s:1:"D";s:4:"𝐸";s:1:"E";s:4:"𝐹";s:1:"F";s:4:"𝐺";s:1:"G";s:4:"𝐻";s:1:"H";s:4:"𝐼";s:1:"I";s:4:"𝐽";s:1:"J";s:4:"𝐾";s:1:"K";s:4:"𝐿";s:1:"L";s:4:"𝑀";s:1:"M";s:4:"𝑁";s:1:"N";s:4:"𝑂";s:1:"O";s:4:"𝑃";s:1:"P";s:4:"𝑄";s:1:"Q";s:4:"𝑅";s:1:"R";s:4:"𝑆";s:1:"S";s:4:"𝑇";s:1:"T";s:4:"𝑈";s:1:"U";s:4:"𝑉";s:1:"V";s:4:"𝑊";s:1:"W";s:4:"𝑋";s:1:"X";s:4:"𝑌";s:1:"Y";s:4:"𝑍";s:1:"Z";s:4:"𝑎";s:1:"a";s:4:"𝑏";s:1:"b";s:4:"𝑐";s:1:"c";s:4:"𝑑";s:1:"d";s:4:"𝑒";s:1:"e";s:4:"𝑓";s:1:"f";s:4:"𝑔";s:1:"g";s:4:"𝑖";s:1:"i";s:4:"𝑗";s:1:"j";s:4:"𝑘";s:1:"k";s:4:"𝑙";s:1:"l";s:4:"𝑚";s:1:"m";s:4:"𝑛";s:1:"n";s:4:"𝑜";s:1:"o";s:4:"𝑝";s:1:"p";s:4:"𝑞";s:1:"q";s:4:"𝑟";s:1:"r";s:4:"𝑠";s:1:"s";s:4:"𝑡";s:1:"t";s:4:"𝑢";s:1:"u";s:4:"𝑣";s:1:"v";s:4:"𝑤";s:1:"w";s:4:"𝑥";s:1:"x";s:4:"𝑦";s:1:"y";s:4:"𝑧";s:1:"z";s:4:"𝑨";s:1:"A";s:4:"𝑩";s:1:"B";s:4:"𝑪";s:1:"C";s:4:"𝑫";s:1:"D";s:4:"𝑬";s:1:"E";s:4:"𝑭";s:1:"F";s:4:"𝑮";s:1:"G";s:4:"𝑯";s:1:"H";s:4:"𝑰";s:1:"I";s:4:"𝑱";s:1:"J";s:4:"𝑲";s:1:"K";s:4:"𝑳";s:1:"L";s:4:"𝑴";s:1:"M";s:4:"𝑵";s:1:"N";s:4:"𝑶";s:1:"O";s:4:"𝑷";s:1:"P";s:4:"𝑸";s:1:"Q";s:4:"𝑹";s:1:"R";s:4:"𝑺";s:1:"S";s:4:"𝑻";s:1:"T";s:4:"𝑼";s:1:"U";s:4:"𝑽";s:1:"V";s:4:"𝑾";s:1:"W";s:4:"𝑿";s:1:"X";s:4:"𝒀";s:1:"Y";s:4:"𝒁";s:1:"Z";s:4:"𝒂";s:1:"a";s:4:"𝒃";s:1:"b";s:4:"𝒄";s:1:"c";s:4:"𝒅";s:1:"d";s:4:"𝒆";s:1:"e";s:4:"𝒇";s:1:"f";s:4:"𝒈";s:1:"g";s:4:"𝒉";s:1:"h";s:4:"𝒊";s:1:"i";s:4:"𝒋";s:1:"j";s:4:"𝒌";s:1:"k";s:4:"𝒍";s:1:"l";s:4:"𝒎";s:1:"m";s:4:"𝒏";s:1:"n";s:4:"𝒐";s:1:"o";s:4:"𝒑";s:1:"p";s:4:"𝒒";s:1:"q";s:4:"𝒓";s:1:"r";s:4:"𝒔";s:1:"s";s:4:"𝒕";s:1:"t";s:4:"𝒖";s:1:"u";s:4:"𝒗";s:1:"v";s:4:"𝒘";s:1:"w";s:4:"𝒙";s:1:"x";s:4:"𝒚";s:1:"y";s:4:"𝒛";s:1:"z";s:4:"𝒜";s:1:"A";s:4:"𝒞";s:1:"C";s:4:"𝒟";s:1:"D";s:4:"𝒢";s:1:"G";s:4:"𝒥";s:1:"J";s:4:"𝒦";s:1:"K";s:4:"𝒩";s:1:"N";s:4:"𝒪";s:1:"O";s:4:"𝒫";s:1:"P";s:4:"𝒬";s:1:"Q";s:4:"𝒮";s:1:"S";s:4:"𝒯";s:1:"T";s:4:"𝒰";s:1:"U";s:4:"𝒱";s:1:"V";s:4:"𝒲";s:1:"W";s:4:"𝒳";s:1:"X";s:4:"𝒴";s:1:"Y";s:4:"𝒵";s:1:"Z";s:4:"𝒶";s:1:"a";s:4:"𝒷";s:1:"b";s:4:"𝒸";s:1:"c";s:4:"𝒹";s:1:"d";s:4:"𝒻";s:1:"f";s:4:"𝒽";s:1:"h";s:4:"𝒾";s:1:"i";s:4:"𝒿";s:1:"j";s:4:"𝓀";s:1:"k";s:4:"𝓁";s:1:"l";s:4:"𝓂";s:1:"m";s:4:"𝓃";s:1:"n";s:4:"𝓅";s:1:"p";s:4:"𝓆";s:1:"q";s:4:"𝓇";s:1:"r";s:4:"𝓈";s:1:"s";s:4:"𝓉";s:1:"t";s:4:"𝓊";s:1:"u";s:4:"𝓋";s:1:"v";s:4:"𝓌";s:1:"w";s:4:"𝓍";s:1:"x";s:4:"𝓎";s:1:"y";s:4:"𝓏";s:1:"z";s:4:"𝓐";s:1:"A";s:4:"𝓑";s:1:"B";s:4:"𝓒";s:1:"C";s:4:"𝓓";s:1:"D";s:4:"𝓔";s:1:"E";s:4:"𝓕";s:1:"F";s:4:"𝓖";s:1:"G";s:4:"𝓗";s:1:"H";s:4:"𝓘";s:1:"I";s:4:"𝓙";s:1:"J";s:4:"𝓚";s:1:"K";s:4:"𝓛";s:1:"L";s:4:"𝓜";s:1:"M";s:4:"𝓝";s:1:"N";s:4:"𝓞";s:1:"O";s:4:"𝓟";s:1:"P";s:4:"𝓠";s:1:"Q";s:4:"𝓡";s:1:"R";s:4:"𝓢";s:1:"S";s:4:"𝓣";s:1:"T";s:4:"𝓤";s:1:"U";s:4:"𝓥";s:1:"V";s:4:"𝓦";s:1:"W";s:4:"𝓧";s:1:"X";s:4:"𝓨";s:1:"Y";s:4:"𝓩";s:1:"Z";s:4:"𝓪";s:1:"a";s:4:"𝓫";s:1:"b";s:4:"𝓬";s:1:"c";s:4:"𝓭";s:1:"d";s:4:"𝓮";s:1:"e";s:4:"𝓯";s:1:"f";s:4:"𝓰";s:1:"g";s:4:"𝓱";s:1:"h";s:4:"𝓲";s:1:"i";s:4:"𝓳";s:1:"j";s:4:"𝓴";s:1:"k";s:4:"𝓵";s:1:"l";s:4:"𝓶";s:1:"m";s:4:"𝓷";s:1:"n";s:4:"𝓸";s:1:"o";s:4:"𝓹";s:1:"p";s:4:"𝓺";s:1:"q";s:4:"𝓻";s:1:"r";s:4:"𝓼";s:1:"s";s:4:"𝓽";s:1:"t";s:4:"𝓾";s:1:"u";s:4:"𝓿";s:1:"v";s:4:"𝔀";s:1:"w";s:4:"𝔁";s:1:"x";s:4:"𝔂";s:1:"y";s:4:"𝔃";s:1:"z";s:4:"𝔄";s:1:"A";s:4:"𝔅";s:1:"B";s:4:"𝔇";s:1:"D";s:4:"𝔈";s:1:"E";s:4:"𝔉";s:1:"F";s:4:"𝔊";s:1:"G";s:4:"𝔍";s:1:"J";s:4:"𝔎";s:1:"K";s:4:"𝔏";s:1:"L";s:4:"𝔐";s:1:"M";s:4:"𝔑";s:1:"N";s:4:"𝔒";s:1:"O";s:4:"𝔓";s:1:"P";s:4:"𝔔";s:1:"Q";s:4:"𝔖";s:1:"S";s:4:"𝔗";s:1:"T";s:4:"𝔘";s:1:"U";s:4:"𝔙";s:1:"V";s:4:"𝔚";s:1:"W";s:4:"𝔛";s:1:"X";s:4:"𝔜";s:1:"Y";s:4:"𝔞";s:1:"a";s:4:"𝔟";s:1:"b";s:4:"𝔠";s:1:"c";s:4:"𝔡";s:1:"d";s:4:"𝔢";s:1:"e";s:4:"𝔣";s:1:"f";s:4:"𝔤";s:1:"g";s:4:"𝔥";s:1:"h";s:4:"𝔦";s:1:"i";s:4:"𝔧";s:1:"j";s:4:"𝔨";s:1:"k";s:4:"𝔩";s:1:"l";s:4:"𝔪";s:1:"m";s:4:"𝔫";s:1:"n";s:4:"𝔬";s:1:"o";s:4:"𝔭";s:1:"p";s:4:"𝔮";s:1:"q";s:4:"𝔯";s:1:"r";s:4:"𝔰";s:1:"s";s:4:"𝔱";s:1:"t";s:4:"𝔲";s:1:"u";s:4:"𝔳";s:1:"v";s:4:"𝔴";s:1:"w";s:4:"𝔵";s:1:"x";s:4:"𝔶";s:1:"y";s:4:"𝔷";s:1:"z";s:4:"𝔸";s:1:"A";s:4:"𝔹";s:1:"B";s:4:"𝔻";s:1:"D";s:4:"𝔼";s:1:"E";s:4:"𝔽";s:1:"F";s:4:"𝔾";s:1:"G";s:4:"𝕀";s:1:"I";s:4:"𝕁";s:1:"J";s:4:"𝕂";s:1:"K";s:4:"𝕃";s:1:"L";s:4:"𝕄";s:1:"M";s:4:"𝕆";s:1:"O";s:4:"𝕊";s:1:"S";s:4:"𝕋";s:1:"T";s:4:"𝕌";s:1:"U";s:4:"𝕍";s:1:"V";s:4:"𝕎";s:1:"W";s:4:"𝕏";s:1:"X";s:4:"𝕐";s:1:"Y";s:4:"𝕒";s:1:"a";s:4:"𝕓";s:1:"b";s:4:"𝕔";s:1:"c";s:4:"𝕕";s:1:"d";s:4:"𝕖";s:1:"e";s:4:"𝕗";s:1:"f";s:4:"𝕘";s:1:"g";s:4:"𝕙";s:1:"h";s:4:"𝕚";s:1:"i";s:4:"𝕛";s:1:"j";s:4:"𝕜";s:1:"k";s:4:"𝕝";s:1:"l";s:4:"𝕞";s:1:"m";s:4:"𝕟";s:1:"n";s:4:"𝕠";s:1:"o";s:4:"𝕡";s:1:"p";s:4:"𝕢";s:1:"q";s:4:"𝕣";s:1:"r";s:4:"𝕤";s:1:"s";s:4:"𝕥";s:1:"t";s:4:"𝕦";s:1:"u";s:4:"𝕧";s:1:"v";s:4:"𝕨";s:1:"w";s:4:"𝕩";s:1:"x";s:4:"𝕪";s:1:"y";s:4:"𝕫";s:1:"z";s:4:"𝕬";s:1:"A";s:4:"𝕭";s:1:"B";s:4:"𝕮";s:1:"C";s:4:"𝕯";s:1:"D";s:4:"𝕰";s:1:"E";s:4:"𝕱";s:1:"F";s:4:"𝕲";s:1:"G";s:4:"𝕳";s:1:"H";s:4:"𝕴";s:1:"I";s:4:"𝕵";s:1:"J";s:4:"𝕶";s:1:"K";s:4:"𝕷";s:1:"L";s:4:"𝕸";s:1:"M";s:4:"𝕹";s:1:"N";s:4:"𝕺";s:1:"O";s:4:"𝕻";s:1:"P";s:4:"𝕼";s:1:"Q";s:4:"𝕽";s:1:"R";s:4:"𝕾";s:1:"S";s:4:"𝕿";s:1:"T";s:4:"𝖀";s:1:"U";s:4:"𝖁";s:1:"V";s:4:"𝖂";s:1:"W";s:4:"𝖃";s:1:"X";s:4:"𝖄";s:1:"Y";s:4:"𝖅";s:1:"Z";s:4:"𝖆";s:1:"a";s:4:"𝖇";s:1:"b";s:4:"𝖈";s:1:"c";s:4:"𝖉";s:1:"d";s:4:"𝖊";s:1:"e";s:4:"𝖋";s:1:"f";s:4:"𝖌";s:1:"g";s:4:"𝖍";s:1:"h";s:4:"𝖎";s:1:"i";s:4:"𝖏";s:1:"j";s:4:"𝖐";s:1:"k";s:4:"𝖑";s:1:"l";s:4:"𝖒";s:1:"m";s:4:"𝖓";s:1:"n";s:4:"𝖔";s:1:"o";s:4:"𝖕";s:1:"p";s:4:"𝖖";s:1:"q";s:4:"𝖗";s:1:"r";s:4:"𝖘";s:1:"s";s:4:"𝖙";s:1:"t";s:4:"𝖚";s:1:"u";s:4:"𝖛";s:1:"v";s:4:"𝖜";s:1:"w";s:4:"𝖝";s:1:"x";s:4:"𝖞";s:1:"y";s:4:"𝖟";s:1:"z";s:4:"𝖠";s:1:"A";s:4:"𝖡";s:1:"B";s:4:"𝖢";s:1:"C";s:4:"𝖣";s:1:"D";s:4:"𝖤";s:1:"E";s:4:"𝖥";s:1:"F";s:4:"𝖦";s:1:"G";s:4:"𝖧";s:1:"H";s:4:"𝖨";s:1:"I";s:4:"𝖩";s:1:"J";s:4:"𝖪";s:1:"K";s:4:"𝖫";s:1:"L";s:4:"𝖬";s:1:"M";s:4:"𝖭";s:1:"N";s:4:"𝖮";s:1:"O";s:4:"𝖯";s:1:"P";s:4:"𝖰";s:1:"Q";s:4:"𝖱";s:1:"R";s:4:"𝖲";s:1:"S";s:4:"𝖳";s:1:"T";s:4:"𝖴";s:1:"U";s:4:"𝖵";s:1:"V";s:4:"𝖶";s:1:"W";s:4:"𝖷";s:1:"X";s:4:"𝖸";s:1:"Y";s:4:"𝖹";s:1:"Z";s:4:"𝖺";s:1:"a";s:4:"𝖻";s:1:"b";s:4:"𝖼";s:1:"c";s:4:"𝖽";s:1:"d";s:4:"𝖾";s:1:"e";s:4:"𝖿";s:1:"f";s:4:"𝗀";s:1:"g";s:4:"𝗁";s:1:"h";s:4:"𝗂";s:1:"i";s:4:"𝗃";s:1:"j";s:4:"𝗄";s:1:"k";s:4:"𝗅";s:1:"l";s:4:"𝗆";s:1:"m";s:4:"𝗇";s:1:"n";s:4:"𝗈";s:1:"o";s:4:"𝗉";s:1:"p";s:4:"𝗊";s:1:"q";s:4:"𝗋";s:1:"r";s:4:"𝗌";s:1:"s";s:4:"𝗍";s:1:"t";s:4:"𝗎";s:1:"u";s:4:"𝗏";s:1:"v";s:4:"𝗐";s:1:"w";s:4:"𝗑";s:1:"x";s:4:"𝗒";s:1:"y";s:4:"𝗓";s:1:"z";s:4:"𝗔";s:1:"A";s:4:"𝗕";s:1:"B";s:4:"𝗖";s:1:"C";s:4:"𝗗";s:1:"D";s:4:"𝗘";s:1:"E";s:4:"𝗙";s:1:"F";s:4:"𝗚";s:1:"G";s:4:"𝗛";s:1:"H";s:4:"𝗜";s:1:"I";s:4:"𝗝";s:1:"J";s:4:"𝗞";s:1:"K";s:4:"𝗟";s:1:"L";s:4:"𝗠";s:1:"M";s:4:"𝗡";s:1:"N";s:4:"𝗢";s:1:"O";s:4:"𝗣";s:1:"P";s:4:"𝗤";s:1:"Q";s:4:"𝗥";s:1:"R";s:4:"𝗦";s:1:"S";s:4:"𝗧";s:1:"T";s:4:"𝗨";s:1:"U";s:4:"𝗩";s:1:"V";s:4:"𝗪";s:1:"W";s:4:"𝗫";s:1:"X";s:4:"𝗬";s:1:"Y";s:4:"𝗭";s:1:"Z";s:4:"𝗮";s:1:"a";s:4:"𝗯";s:1:"b";s:4:"𝗰";s:1:"c";s:4:"𝗱";s:1:"d";s:4:"𝗲";s:1:"e";s:4:"𝗳";s:1:"f";s:4:"𝗴";s:1:"g";s:4:"𝗵";s:1:"h";s:4:"𝗶";s:1:"i";s:4:"𝗷";s:1:"j";s:4:"𝗸";s:1:"k";s:4:"𝗹";s:1:"l";s:4:"𝗺";s:1:"m";s:4:"𝗻";s:1:"n";s:4:"𝗼";s:1:"o";s:4:"𝗽";s:1:"p";s:4:"𝗾";s:1:"q";s:4:"𝗿";s:1:"r";s:4:"𝘀";s:1:"s";s:4:"𝘁";s:1:"t";s:4:"𝘂";s:1:"u";s:4:"𝘃";s:1:"v";s:4:"𝘄";s:1:"w";s:4:"𝘅";s:1:"x";s:4:"𝘆";s:1:"y";s:4:"𝘇";s:1:"z";s:4:"𝘈";s:1:"A";s:4:"𝘉";s:1:"B";s:4:"𝘊";s:1:"C";s:4:"𝘋";s:1:"D";s:4:"𝘌";s:1:"E";s:4:"𝘍";s:1:"F";s:4:"𝘎";s:1:"G";s:4:"𝘏";s:1:"H";s:4:"𝘐";s:1:"I";s:4:"𝘑";s:1:"J";s:4:"𝘒";s:1:"K";s:4:"𝘓";s:1:"L";s:4:"𝘔";s:1:"M";s:4:"𝘕";s:1:"N";s:4:"𝘖";s:1:"O";s:4:"𝘗";s:1:"P";s:4:"𝘘";s:1:"Q";s:4:"𝘙";s:1:"R";s:4:"𝘚";s:1:"S";s:4:"𝘛";s:1:"T";s:4:"𝘜";s:1:"U";s:4:"𝘝";s:1:"V";s:4:"𝘞";s:1:"W";s:4:"𝘟";s:1:"X";s:4:"𝘠";s:1:"Y";s:4:"𝘡";s:1:"Z";s:4:"𝘢";s:1:"a";s:4:"𝘣";s:1:"b";s:4:"𝘤";s:1:"c";s:4:"𝘥";s:1:"d";s:4:"𝘦";s:1:"e";s:4:"𝘧";s:1:"f";s:4:"𝘨";s:1:"g";s:4:"𝘩";s:1:"h";s:4:"𝘪";s:1:"i";s:4:"𝘫";s:1:"j";s:4:"𝘬";s:1:"k";s:4:"𝘭";s:1:"l";s:4:"𝘮";s:1:"m";s:4:"𝘯";s:1:"n";s:4:"𝘰";s:1:"o";s:4:"𝘱";s:1:"p";s:4:"𝘲";s:1:"q";s:4:"𝘳";s:1:"r";s:4:"𝘴";s:1:"s";s:4:"𝘵";s:1:"t";s:4:"𝘶";s:1:"u";s:4:"𝘷";s:1:"v";s:4:"𝘸";s:1:"w";s:4:"𝘹";s:1:"x";s:4:"𝘺";s:1:"y";s:4:"𝘻";s:1:"z";s:4:"𝘼";s:1:"A";s:4:"𝘽";s:1:"B";s:4:"𝘾";s:1:"C";s:4:"𝘿";s:1:"D";s:4:"𝙀";s:1:"E";s:4:"𝙁";s:1:"F";s:4:"𝙂";s:1:"G";s:4:"𝙃";s:1:"H";s:4:"𝙄";s:1:"I";s:4:"𝙅";s:1:"J";s:4:"𝙆";s:1:"K";s:4:"𝙇";s:1:"L";s:4:"𝙈";s:1:"M";s:4:"𝙉";s:1:"N";s:4:"𝙊";s:1:"O";s:4:"𝙋";s:1:"P";s:4:"𝙌";s:1:"Q";s:4:"𝙍";s:1:"R";s:4:"𝙎";s:1:"S";s:4:"𝙏";s:1:"T";s:4:"𝙐";s:1:"U";s:4:"𝙑";s:1:"V";s:4:"𝙒";s:1:"W";s:4:"𝙓";s:1:"X";s:4:"𝙔";s:1:"Y";s:4:"𝙕";s:1:"Z";s:4:"𝙖";s:1:"a";s:4:"𝙗";s:1:"b";s:4:"𝙘";s:1:"c";s:4:"𝙙";s:1:"d";s:4:"𝙚";s:1:"e";s:4:"𝙛";s:1:"f";s:4:"𝙜";s:1:"g";s:4:"𝙝";s:1:"h";s:4:"𝙞";s:1:"i";s:4:"𝙟";s:1:"j";s:4:"𝙠";s:1:"k";s:4:"𝙡";s:1:"l";s:4:"𝙢";s:1:"m";s:4:"𝙣";s:1:"n";s:4:"𝙤";s:1:"o";s:4:"𝙥";s:1:"p";s:4:"𝙦";s:1:"q";s:4:"𝙧";s:1:"r";s:4:"𝙨";s:1:"s";s:4:"𝙩";s:1:"t";s:4:"𝙪";s:1:"u";s:4:"𝙫";s:1:"v";s:4:"𝙬";s:1:"w";s:4:"𝙭";s:1:"x";s:4:"𝙮";s:1:"y";s:4:"𝙯";s:1:"z";s:4:"𝙰";s:1:"A";s:4:"𝙱";s:1:"B";s:4:"𝙲";s:1:"C";s:4:"𝙳";s:1:"D";s:4:"𝙴";s:1:"E";s:4:"𝙵";s:1:"F";s:4:"𝙶";s:1:"G";s:4:"𝙷";s:1:"H";s:4:"𝙸";s:1:"I";s:4:"𝙹";s:1:"J";s:4:"𝙺";s:1:"K";s:4:"𝙻";s:1:"L";s:4:"𝙼";s:1:"M";s:4:"𝙽";s:1:"N";s:4:"𝙾";s:1:"O";s:4:"𝙿";s:1:"P";s:4:"𝚀";s:1:"Q";s:4:"𝚁";s:1:"R";s:4:"𝚂";s:1:"S";s:4:"𝚃";s:1:"T";s:4:"𝚄";s:1:"U";s:4:"𝚅";s:1:"V";s:4:"𝚆";s:1:"W";s:4:"𝚇";s:1:"X";s:4:"𝚈";s:1:"Y";s:4:"𝚉";s:1:"Z";s:4:"𝚊";s:1:"a";s:4:"𝚋";s:1:"b";s:4:"𝚌";s:1:"c";s:4:"𝚍";s:1:"d";s:4:"𝚎";s:1:"e";s:4:"𝚏";s:1:"f";s:4:"𝚐";s:1:"g";s:4:"𝚑";s:1:"h";s:4:"𝚒";s:1:"i";s:4:"𝚓";s:1:"j";s:4:"𝚔";s:1:"k";s:4:"𝚕";s:1:"l";s:4:"𝚖";s:1:"m";s:4:"𝚗";s:1:"n";s:4:"𝚘";s:1:"o";s:4:"𝚙";s:1:"p";s:4:"𝚚";s:1:"q";s:4:"𝚛";s:1:"r";s:4:"𝚜";s:1:"s";s:4:"𝚝";s:1:"t";s:4:"𝚞";s:1:"u";s:4:"𝚟";s:1:"v";s:4:"𝚠";s:1:"w";s:4:"𝚡";s:1:"x";s:4:"𝚢";s:1:"y";s:4:"𝚣";s:1:"z";s:4:"𝚤";s:2:"ı";s:4:"𝚥";s:2:"ȷ";s:4:"𝚨";s:2:"Α";s:4:"𝚩";s:2:"Β";s:4:"𝚪";s:2:"Γ";s:4:"𝚫";s:2:"Δ";s:4:"𝚬";s:2:"Ε";s:4:"𝚭";s:2:"Ζ";s:4:"𝚮";s:2:"Η";s:4:"𝚯";s:2:"Θ";s:4:"𝚰";s:2:"Ι";s:4:"𝚱";s:2:"Κ";s:4:"𝚲";s:2:"Λ";s:4:"𝚳";s:2:"Μ";s:4:"𝚴";s:2:"Ν";s:4:"𝚵";s:2:"Ξ";s:4:"𝚶";s:2:"Ο";s:4:"𝚷";s:2:"Π";s:4:"𝚸";s:2:"Ρ";s:4:"𝚹";s:2:"Θ";s:4:"𝚺";s:2:"Σ";s:4:"𝚻";s:2:"Τ";s:4:"𝚼";s:2:"Υ";s:4:"𝚽";s:2:"Φ";s:4:"𝚾";s:2:"Χ";s:4:"𝚿";s:2:"Ψ";s:4:"𝛀";s:2:"Ω";s:4:"𝛁";s:3:"∇";s:4:"𝛂";s:2:"α";s:4:"𝛃";s:2:"β";s:4:"𝛄";s:2:"γ";s:4:"𝛅";s:2:"δ";s:4:"𝛆";s:2:"ε";s:4:"𝛇";s:2:"ζ";s:4:"𝛈";s:2:"η";s:4:"𝛉";s:2:"θ";s:4:"𝛊";s:2:"ι";s:4:"𝛋";s:2:"κ";s:4:"𝛌";s:2:"λ";s:4:"𝛍";s:2:"μ";s:4:"𝛎";s:2:"ν";s:4:"𝛏";s:2:"ξ";s:4:"𝛐";s:2:"ο";s:4:"𝛑";s:2:"π";s:4:"𝛒";s:2:"ρ";s:4:"𝛓";s:2:"ς";s:4:"𝛔";s:2:"σ";s:4:"𝛕";s:2:"τ";s:4:"𝛖";s:2:"υ";s:4:"𝛗";s:2:"φ";s:4:"𝛘";s:2:"χ";s:4:"𝛙";s:2:"ψ";s:4:"𝛚";s:2:"ω";s:4:"𝛛";s:3:"∂";s:4:"𝛜";s:2:"ε";s:4:"𝛝";s:2:"θ";s:4:"𝛞";s:2:"κ";s:4:"𝛟";s:2:"φ";s:4:"𝛠";s:2:"ρ";s:4:"𝛡";s:2:"π";s:4:"𝛢";s:2:"Α";s:4:"𝛣";s:2:"Β";s:4:"𝛤";s:2:"Γ";s:4:"𝛥";s:2:"Δ";s:4:"𝛦";s:2:"Ε";s:4:"𝛧";s:2:"Ζ";s:4:"𝛨";s:2:"Η";s:4:"𝛩";s:2:"Θ";s:4:"𝛪";s:2:"Ι";s:4:"𝛫";s:2:"Κ";s:4:"𝛬";s:2:"Λ";s:4:"𝛭";s:2:"Μ";s:4:"𝛮";s:2:"Ν";s:4:"𝛯";s:2:"Ξ";s:4:"𝛰";s:2:"Ο";s:4:"𝛱";s:2:"Π";s:4:"𝛲";s:2:"Ρ";s:4:"𝛳";s:2:"Θ";s:4:"𝛴";s:2:"Σ";s:4:"𝛵";s:2:"Τ";s:4:"𝛶";s:2:"Υ";s:4:"𝛷";s:2:"Φ";s:4:"𝛸";s:2:"Χ";s:4:"𝛹";s:2:"Ψ";s:4:"𝛺";s:2:"Ω";s:4:"𝛻";s:3:"∇";s:4:"𝛼";s:2:"α";s:4:"𝛽";s:2:"β";s:4:"𝛾";s:2:"γ";s:4:"𝛿";s:2:"δ";s:4:"𝜀";s:2:"ε";s:4:"𝜁";s:2:"ζ";s:4:"𝜂";s:2:"η";s:4:"𝜃";s:2:"θ";s:4:"𝜄";s:2:"ι";s:4:"𝜅";s:2:"κ";s:4:"𝜆";s:2:"λ";s:4:"𝜇";s:2:"μ";s:4:"𝜈";s:2:"ν";s:4:"𝜉";s:2:"ξ";s:4:"𝜊";s:2:"ο";s:4:"𝜋";s:2:"π";s:4:"𝜌";s:2:"ρ";s:4:"𝜍";s:2:"ς";s:4:"𝜎";s:2:"σ";s:4:"𝜏";s:2:"τ";s:4:"𝜐";s:2:"υ";s:4:"𝜑";s:2:"φ";s:4:"𝜒";s:2:"χ";s:4:"𝜓";s:2:"ψ";s:4:"𝜔";s:2:"ω";s:4:"𝜕";s:3:"∂";s:4:"𝜖";s:2:"ε";s:4:"𝜗";s:2:"θ";s:4:"𝜘";s:2:"κ";s:4:"𝜙";s:2:"φ";s:4:"𝜚";s:2:"ρ";s:4:"𝜛";s:2:"π";s:4:"𝜜";s:2:"Α";s:4:"𝜝";s:2:"Β";s:4:"𝜞";s:2:"Γ";s:4:"𝜟";s:2:"Δ";s:4:"𝜠";s:2:"Ε";s:4:"𝜡";s:2:"Ζ";s:4:"𝜢";s:2:"Η";s:4:"𝜣";s:2:"Θ";s:4:"𝜤";s:2:"Ι";s:4:"𝜥";s:2:"Κ";s:4:"𝜦";s:2:"Λ";s:4:"𝜧";s:2:"Μ";s:4:"𝜨";s:2:"Ν";s:4:"𝜩";s:2:"Ξ";s:4:"𝜪";s:2:"Ο";s:4:"𝜫";s:2:"Π";s:4:"𝜬";s:2:"Ρ";s:4:"𝜭";s:2:"Θ";s:4:"𝜮";s:2:"Σ";s:4:"𝜯";s:2:"Τ";s:4:"𝜰";s:2:"Υ";s:4:"𝜱";s:2:"Φ";s:4:"𝜲";s:2:"Χ";s:4:"𝜳";s:2:"Ψ";s:4:"𝜴";s:2:"Ω";s:4:"𝜵";s:3:"∇";s:4:"𝜶";s:2:"α";s:4:"𝜷";s:2:"β";s:4:"𝜸";s:2:"γ";s:4:"𝜹";s:2:"δ";s:4:"𝜺";s:2:"ε";s:4:"𝜻";s:2:"ζ";s:4:"𝜼";s:2:"η";s:4:"𝜽";s:2:"θ";s:4:"𝜾";s:2:"ι";s:4:"𝜿";s:2:"κ";s:4:"𝝀";s:2:"λ";s:4:"𝝁";s:2:"μ";s:4:"𝝂";s:2:"ν";s:4:"𝝃";s:2:"ξ";s:4:"𝝄";s:2:"ο";s:4:"𝝅";s:2:"π";s:4:"𝝆";s:2:"ρ";s:4:"𝝇";s:2:"ς";s:4:"𝝈";s:2:"σ";s:4:"𝝉";s:2:"τ";s:4:"𝝊";s:2:"υ";s:4:"𝝋";s:2:"φ";s:4:"𝝌";s:2:"χ";s:4:"𝝍";s:2:"ψ";s:4:"𝝎";s:2:"ω";s:4:"𝝏";s:3:"∂";s:4:"𝝐";s:2:"ε";s:4:"𝝑";s:2:"θ";s:4:"𝝒";s:2:"κ";s:4:"𝝓";s:2:"φ";s:4:"𝝔";s:2:"ρ";s:4:"𝝕";s:2:"π";s:4:"𝝖";s:2:"Α";s:4:"𝝗";s:2:"Β";s:4:"𝝘";s:2:"Γ";s:4:"𝝙";s:2:"Δ";s:4:"𝝚";s:2:"Ε";s:4:"𝝛";s:2:"Ζ";s:4:"𝝜";s:2:"Η";s:4:"𝝝";s:2:"Θ";s:4:"𝝞";s:2:"Ι";s:4:"𝝟";s:2:"Κ";s:4:"𝝠";s:2:"Λ";s:4:"𝝡";s:2:"Μ";s:4:"𝝢";s:2:"Ν";s:4:"𝝣";s:2:"Ξ";s:4:"𝝤";s:2:"Ο";s:4:"𝝥";s:2:"Π";s:4:"𝝦";s:2:"Ρ";s:4:"𝝧";s:2:"Θ";s:4:"𝝨";s:2:"Σ";s:4:"𝝩";s:2:"Τ";s:4:"𝝪";s:2:"Υ";s:4:"𝝫";s:2:"Φ";s:4:"𝝬";s:2:"Χ";s:4:"𝝭";s:2:"Ψ";s:4:"𝝮";s:2:"Ω";s:4:"𝝯";s:3:"∇";s:4:"𝝰";s:2:"α";s:4:"𝝱";s:2:"β";s:4:"𝝲";s:2:"γ";s:4:"𝝳";s:2:"δ";s:4:"𝝴";s:2:"ε";s:4:"𝝵";s:2:"ζ";s:4:"𝝶";s:2:"η";s:4:"𝝷";s:2:"θ";s:4:"𝝸";s:2:"ι";s:4:"𝝹";s:2:"κ";s:4:"𝝺";s:2:"λ";s:4:"𝝻";s:2:"μ";s:4:"𝝼";s:2:"ν";s:4:"𝝽";s:2:"ξ";s:4:"𝝾";s:2:"ο";s:4:"𝝿";s:2:"π";s:4:"𝞀";s:2:"ρ";s:4:"𝞁";s:2:"ς";s:4:"𝞂";s:2:"σ";s:4:"𝞃";s:2:"τ";s:4:"𝞄";s:2:"υ";s:4:"𝞅";s:2:"φ";s:4:"𝞆";s:2:"χ";s:4:"𝞇";s:2:"ψ";s:4:"𝞈";s:2:"ω";s:4:"𝞉";s:3:"∂";s:4:"𝞊";s:2:"ε";s:4:"𝞋";s:2:"θ";s:4:"𝞌";s:2:"κ";s:4:"𝞍";s:2:"φ";s:4:"𝞎";s:2:"ρ";s:4:"𝞏";s:2:"π";s:4:"𝞐";s:2:"Α";s:4:"𝞑";s:2:"Β";s:4:"𝞒";s:2:"Γ";s:4:"𝞓";s:2:"Δ";s:4:"𝞔";s:2:"Ε";s:4:"𝞕";s:2:"Ζ";s:4:"𝞖";s:2:"Η";s:4:"𝞗";s:2:"Θ";s:4:"𝞘";s:2:"Ι";s:4:"𝞙";s:2:"Κ";s:4:"𝞚";s:2:"Λ";s:4:"𝞛";s:2:"Μ";s:4:"𝞜";s:2:"Ν";s:4:"𝞝";s:2:"Ξ";s:4:"𝞞";s:2:"Ο";s:4:"𝞟";s:2:"Π";s:4:"𝞠";s:2:"Ρ";s:4:"𝞡";s:2:"Θ";s:4:"𝞢";s:2:"Σ";s:4:"𝞣";s:2:"Τ";s:4:"𝞤";s:2:"Υ";s:4:"𝞥";s:2:"Φ";s:4:"𝞦";s:2:"Χ";s:4:"𝞧";s:2:"Ψ";s:4:"𝞨";s:2:"Ω";s:4:"𝞩";s:3:"∇";s:4:"𝞪";s:2:"α";s:4:"𝞫";s:2:"β";s:4:"𝞬";s:2:"γ";s:4:"𝞭";s:2:"δ";s:4:"𝞮";s:2:"ε";s:4:"𝞯";s:2:"ζ";s:4:"𝞰";s:2:"η";s:4:"𝞱";s:2:"θ";s:4:"𝞲";s:2:"ι";s:4:"𝞳";s:2:"κ";s:4:"𝞴";s:2:"λ";s:4:"𝞵";s:2:"μ";s:4:"𝞶";s:2:"ν";s:4:"𝞷";s:2:"ξ";s:4:"𝞸";s:2:"ο";s:4:"𝞹";s:2:"π";s:4:"𝞺";s:2:"ρ";s:4:"𝞻";s:2:"ς";s:4:"𝞼";s:2:"σ";s:4:"𝞽";s:2:"τ";s:4:"𝞾";s:2:"υ";s:4:"𝞿";s:2:"φ";s:4:"𝟀";s:2:"χ";s:4:"𝟁";s:2:"ψ";s:4:"𝟂";s:2:"ω";s:4:"𝟃";s:3:"∂";s:4:"𝟄";s:2:"ε";s:4:"𝟅";s:2:"θ";s:4:"𝟆";s:2:"κ";s:4:"𝟇";s:2:"φ";s:4:"𝟈";s:2:"ρ";s:4:"𝟉";s:2:"π";s:4:"𝟊";s:2:"Ϝ";s:4:"𝟋";s:2:"ϝ";s:4:"𝟎";s:1:"0";s:4:"𝟏";s:1:"1";s:4:"𝟐";s:1:"2";s:4:"𝟑";s:1:"3";s:4:"𝟒";s:1:"4";s:4:"𝟓";s:1:"5";s:4:"𝟔";s:1:"6";s:4:"𝟕";s:1:"7";s:4:"𝟖";s:1:"8";s:4:"𝟗";s:1:"9";s:4:"𝟘";s:1:"0";s:4:"𝟙";s:1:"1";s:4:"𝟚";s:1:"2";s:4:"𝟛";s:1:"3";s:4:"𝟜";s:1:"4";s:4:"𝟝";s:1:"5";s:4:"𝟞";s:1:"6";s:4:"𝟟";s:1:"7";s:4:"𝟠";s:1:"8";s:4:"𝟡";s:1:"9";s:4:"𝟢";s:1:"0";s:4:"𝟣";s:1:"1";s:4:"𝟤";s:1:"2";s:4:"𝟥";s:1:"3";s:4:"𝟦";s:1:"4";s:4:"𝟧";s:1:"5";s:4:"𝟨";s:1:"6";s:4:"𝟩";s:1:"7";s:4:"𝟪";s:1:"8";s:4:"𝟫";s:1:"9";s:4:"𝟬";s:1:"0";s:4:"𝟭";s:1:"1";s:4:"𝟮";s:1:"2";s:4:"𝟯";s:1:"3";s:4:"𝟰";s:1:"4";s:4:"𝟱";s:1:"5";s:4:"𝟲";s:1:"6";s:4:"𝟳";s:1:"7";s:4:"𝟴";s:1:"8";s:4:"𝟵";s:1:"9";s:4:"𝟶";s:1:"0";s:4:"𝟷";s:1:"1";s:4:"𝟸";s:1:"2";s:4:"𝟹";s:1:"3";s:4:"𝟺";s:1:"4";s:4:"𝟻";s:1:"5";s:4:"𝟼";s:1:"6";s:4:"𝟽";s:1:"7";s:4:"𝟾";s:1:"8";s:4:"𝟿";s:1:"9";s:4:"丽";s:3:"丽";s:4:"丸";s:3:"丸";s:4:"乁";s:3:"乁";s:4:"𠄢";s:4:"𠄢";s:4:"你";s:3:"你";s:4:"侮";s:3:"侮";s:4:"侻";s:3:"侻";s:4:"倂";s:3:"倂";s:4:"偺";s:3:"偺";s:4:"備";s:3:"備";s:4:"僧";s:3:"僧";s:4:"像";s:3:"像";s:4:"㒞";s:3:"㒞";s:4:"𠘺";s:4:"𠘺";s:4:"免";s:3:"免";s:4:"兔";s:3:"兔";s:4:"兤";s:3:"兤";s:4:"具";s:3:"具";s:4:"𠔜";s:4:"𠔜";s:4:"㒹";s:3:"㒹";s:4:"內";s:3:"內";s:4:"再";s:3:"再";s:4:"𠕋";s:4:"𠕋";s:4:"冗";s:3:"冗";s:4:"冤";s:3:"冤";s:4:"仌";s:3:"仌";s:4:"冬";s:3:"冬";s:4:"况";s:3:"况";s:4:"𩇟";s:4:"𩇟";s:4:"凵";s:3:"凵";s:4:"刃";s:3:"刃";s:4:"㓟";s:3:"㓟";s:4:"刻";s:3:"刻";s:4:"剆";s:3:"剆";s:4:"割";s:3:"割";s:4:"剷";s:3:"剷";s:4:"㔕";s:3:"㔕";s:4:"勇";s:3:"勇";s:4:"勉";s:3:"勉";s:4:"勤";s:3:"勤";s:4:"勺";s:3:"勺";s:4:"包";s:3:"包";s:4:"匆";s:3:"匆";s:4:"北";s:3:"北";s:4:"卉";s:3:"卉";s:4:"卑";s:3:"卑";s:4:"博";s:3:"博";s:4:"即";s:3:"即";s:4:"卽";s:3:"卽";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"卿";s:3:"卿";s:4:"𠨬";s:4:"𠨬";s:4:"灰";s:3:"灰";s:4:"及";s:3:"及";s:4:"叟";s:3:"叟";s:4:"𠭣";s:4:"𠭣";s:4:"叫";s:3:"叫";s:4:"叱";s:3:"叱";s:4:"吆";s:3:"吆";s:4:"咞";s:3:"咞";s:4:"吸";s:3:"吸";s:4:"呈";s:3:"呈";s:4:"周";s:3:"周";s:4:"咢";s:3:"咢";s:4:"哶";s:3:"哶";s:4:"唐";s:3:"唐";s:4:"啓";s:3:"啓";s:4:"啣";s:3:"啣";s:4:"善";s:3:"善";s:4:"善";s:3:"善";s:4:"喙";s:3:"喙";s:4:"喫";s:3:"喫";s:4:"喳";s:3:"喳";s:4:"嗂";s:3:"嗂";s:4:"圖";s:3:"圖";s:4:"嘆";s:3:"嘆";s:4:"圗";s:3:"圗";s:4:"噑";s:3:"噑";s:4:"噴";s:3:"噴";s:4:"切";s:3:"切";s:4:"壮";s:3:"壮";s:4:"城";s:3:"城";s:4:"埴";s:3:"埴";s:4:"堍";s:3:"堍";s:4:"型";s:3:"型";s:4:"堲";s:3:"堲";s:4:"報";s:3:"報";s:4:"墬";s:3:"墬";s:4:"𡓤";s:4:"𡓤";s:4:"売";s:3:"売";s:4:"壷";s:3:"壷";s:4:"夆";s:3:"夆";s:4:"多";s:3:"多";s:4:"夢";s:3:"夢";s:4:"奢";s:3:"奢";s:4:"𡚨";s:4:"𡚨";s:4:"𡛪";s:4:"𡛪";s:4:"姬";s:3:"姬";s:4:"娛";s:3:"娛";s:4:"娧";s:3:"娧";s:4:"姘";s:3:"姘";s:4:"婦";s:3:"婦";s:4:"㛮";s:3:"㛮";s:4:"㛼";s:3:"㛼";s:4:"嬈";s:3:"嬈";s:4:"嬾";s:3:"嬾";s:4:"嬾";s:3:"嬾";s:4:"𡧈";s:4:"𡧈";s:4:"寃";s:3:"寃";s:4:"寘";s:3:"寘";s:4:"寧";s:3:"寧";s:4:"寳";s:3:"寳";s:4:"𡬘";s:4:"𡬘";s:4:"寿";s:3:"寿";s:4:"将";s:3:"将";s:4:"当";s:3:"当";s:4:"尢";s:3:"尢";s:4:"㞁";s:3:"㞁";s:4:"屠";s:3:"屠";s:4:"屮";s:3:"屮";s:4:"峀";s:3:"峀";s:4:"岍";s:3:"岍";s:4:"𡷤";s:4:"𡷤";s:4:"嵃";s:3:"嵃";s:4:"𡷦";s:4:"𡷦";s:4:"嵮";s:3:"嵮";s:4:"嵫";s:3:"嵫";s:4:"嵼";s:3:"嵼";s:4:"巡";s:3:"巡";s:4:"巢";s:3:"巢";s:4:"㠯";s:3:"㠯";s:4:"巽";s:3:"巽";s:4:"帨";s:3:"帨";s:4:"帽";s:3:"帽";s:4:"幩";s:3:"幩";s:4:"㡢";s:3:"㡢";s:4:"𢆃";s:4:"𢆃";s:4:"㡼";s:3:"㡼";s:4:"庰";s:3:"庰";s:4:"庳";s:3:"庳";s:4:"庶";s:3:"庶";s:4:"廊";s:3:"廊";s:4:"𪎒";s:4:"𪎒";s:4:"廾";s:3:"廾";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"𢌱";s:4:"舁";s:3:"舁";s:4:"弢";s:3:"弢";s:4:"弢";s:3:"弢";s:4:"㣇";s:3:"㣇";s:4:"𣊸";s:4:"𣊸";s:4:"𦇚";s:4:"𦇚";s:4:"形";s:3:"形";s:4:"彫";s:3:"彫";s:4:"㣣";s:3:"㣣";s:4:"徚";s:3:"徚";s:4:"忍";s:3:"忍";s:4:"志";s:3:"志";s:4:"忹";s:3:"忹";s:4:"悁";s:3:"悁";s:4:"㤺";s:3:"㤺";s:4:"㤜";s:3:"㤜";s:4:"悔";s:3:"悔";s:4:"𢛔";s:4:"𢛔";s:4:"惇";s:3:"惇";s:4:"慈";s:3:"慈";s:4:"慌";s:3:"慌";s:4:"慎";s:3:"慎";s:4:"慌";s:3:"慌";s:4:"慺";s:3:"慺";s:4:"憎";s:3:"憎";s:4:"憲";s:3:"憲";s:4:"憤";s:3:"憤";s:4:"憯";s:3:"憯";s:4:"懞";s:3:"懞";s:4:"懲";s:3:"懲";s:4:"懶";s:3:"懶";s:4:"成";s:3:"成";s:4:"戛";s:3:"戛";s:4:"扝";s:3:"扝";s:4:"抱";s:3:"抱";s:4:"拔";s:3:"拔";s:4:"捐";s:3:"捐";s:4:"𢬌";s:4:"𢬌";s:4:"挽";s:3:"挽";s:4:"拼";s:3:"拼";s:4:"捨";s:3:"捨";s:4:"掃";s:3:"掃";s:4:"揤";s:3:"揤";s:4:"𢯱";s:4:"𢯱";s:4:"搢";s:3:"搢";s:4:"揅";s:3:"揅";s:4:"掩";s:3:"掩";s:4:"㨮";s:3:"㨮";s:4:"摩";s:3:"摩";s:4:"摾";s:3:"摾";s:4:"撝";s:3:"撝";s:4:"摷";s:3:"摷";s:4:"㩬";s:3:"㩬";s:4:"敏";s:3:"敏";s:4:"敬";s:3:"敬";s:4:"𣀊";s:4:"𣀊";s:4:"旣";s:3:"旣";s:4:"書";s:3:"書";s:4:"晉";s:3:"晉";s:4:"㬙";s:3:"㬙";s:4:"暑";s:3:"暑";s:4:"㬈";s:3:"㬈";s:4:"㫤";s:3:"㫤";s:4:"冒";s:3:"冒";s:4:"冕";s:3:"冕";s:4:"最";s:3:"最";s:4:"暜";s:3:"暜";s:4:"肭";s:3:"肭";s:4:"䏙";s:3:"䏙";s:4:"朗";s:3:"朗";s:4:"望";s:3:"望";s:4:"朡";s:3:"朡";s:4:"杞";s:3:"杞";s:4:"杓";s:3:"杓";s:4:"𣏃";s:4:"𣏃";s:4:"㭉";s:3:"㭉";s:4:"柺";s:3:"柺";s:4:"枅";s:3:"枅";s:4:"桒";s:3:"桒";s:4:"梅";s:3:"梅";s:4:"𣑭";s:4:"𣑭";s:4:"梎";s:3:"梎";s:4:"栟";s:3:"栟";s:4:"椔";s:3:"椔";s:4:"㮝";s:3:"㮝";s:4:"楂";s:3:"楂";s:4:"榣";s:3:"榣";s:4:"槪";s:3:"槪";s:4:"檨";s:3:"檨";s:4:"𣚣";s:4:"𣚣";s:4:"櫛";s:3:"櫛";s:4:"㰘";s:3:"㰘";s:4:"次";s:3:"次";s:4:"𣢧";s:4:"𣢧";s:4:"歔";s:3:"歔";s:4:"㱎";s:3:"㱎";s:4:"歲";s:3:"歲";s:4:"殟";s:3:"殟";s:4:"殺";s:3:"殺";s:4:"殻";s:3:"殻";s:4:"𣪍";s:4:"𣪍";s:4:"𡴋";s:4:"𡴋";s:4:"𣫺";s:4:"𣫺";s:4:"汎";s:3:"汎";s:4:"𣲼";s:4:"𣲼";s:4:"沿";s:3:"沿";s:4:"泍";s:3:"泍";s:4:"汧";s:3:"汧";s:4:"洖";s:3:"洖";s:4:"派";s:3:"派";s:4:"海";s:3:"海";s:4:"流";s:3:"流";s:4:"浩";s:3:"浩";s:4:"浸";s:3:"浸";s:4:"涅";s:3:"涅";s:4:"𣴞";s:4:"𣴞";s:4:"洴";s:3:"洴";s:4:"港";s:3:"港";s:4:"湮";s:3:"湮";s:4:"㴳";s:3:"㴳";s:4:"滋";s:3:"滋";s:4:"滇";s:3:"滇";s:4:"𣻑";s:4:"𣻑";s:4:"淹";s:3:"淹";s:4:"潮";s:3:"潮";s:4:"𣽞";s:4:"𣽞";s:4:"𣾎";s:4:"𣾎";s:4:"濆";s:3:"濆";s:4:"瀹";s:3:"瀹";s:4:"瀞";s:3:"瀞";s:4:"瀛";s:3:"瀛";s:4:"㶖";s:3:"㶖";s:4:"灊";s:3:"灊";s:4:"災";s:3:"災";s:4:"灷";s:3:"灷";s:4:"炭";s:3:"炭";s:4:"𠔥";s:4:"𠔥";s:4:"煅";s:3:"煅";s:4:"𤉣";s:4:"𤉣";s:4:"熜";s:3:"熜";s:4:"𤎫";s:4:"𤎫";s:4:"爨";s:3:"爨";s:4:"爵";s:3:"爵";s:4:"牐";s:3:"牐";s:4:"𤘈";s:4:"𤘈";s:4:"犀";s:3:"犀";s:4:"犕";s:3:"犕";s:4:"𤜵";s:4:"𤜵";s:4:"𤠔";s:4:"𤠔";s:4:"獺";s:3:"獺";s:4:"王";s:3:"王";s:4:"㺬";s:3:"㺬";s:4:"玥";s:3:"玥";s:4:"㺸";s:3:"㺸";s:4:"㺸";s:3:"㺸";s:4:"瑇";s:3:"瑇";s:4:"瑜";s:3:"瑜";s:4:"瑱";s:3:"瑱";s:4:"璅";s:3:"璅";s:4:"瓊";s:3:"瓊";s:4:"㼛";s:3:"㼛";s:4:"甤";s:3:"甤";s:4:"𤰶";s:4:"𤰶";s:4:"甾";s:3:"甾";s:4:"𤲒";s:4:"𤲒";s:4:"異";s:3:"異";s:4:"𢆟";s:4:"𢆟";s:4:"瘐";s:3:"瘐";s:4:"𤾡";s:4:"𤾡";s:4:"𤾸";s:4:"𤾸";s:4:"𥁄";s:4:"𥁄";s:4:"㿼";s:3:"㿼";s:4:"䀈";s:3:"䀈";s:4:"直";s:3:"直";s:4:"𥃳";s:4:"𥃳";s:4:"𥃲";s:4:"𥃲";s:4:"𥄙";s:4:"𥄙";s:4:"𥄳";s:4:"𥄳";s:4:"眞";s:3:"眞";s:4:"真";s:3:"真";s:4:"真";s:3:"真";s:4:"睊";s:3:"睊";s:4:"䀹";s:3:"䀹";s:4:"瞋";s:3:"瞋";s:4:"䁆";s:3:"䁆";s:4:"䂖";s:3:"䂖";s:4:"𥐝";s:4:"𥐝";s:4:"硎";s:3:"硎";s:4:"碌";s:3:"碌";s:4:"磌";s:3:"磌";s:4:"䃣";s:3:"䃣";s:4:"𥘦";s:4:"𥘦";s:4:"祖";s:3:"祖";s:4:"𥚚";s:4:"𥚚";s:4:"𥛅";s:4:"𥛅";s:4:"福";s:3:"福";s:4:"秫";s:3:"秫";s:4:"䄯";s:3:"䄯";s:4:"穀";s:3:"穀";s:4:"穊";s:3:"穊";s:4:"穏";s:3:"穏";s:4:"𥥼";s:4:"𥥼";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"𥪧";s:4:"竮";s:3:"竮";s:4:"䈂";s:3:"䈂";s:4:"𥮫";s:4:"𥮫";s:4:"篆";s:3:"篆";s:4:"築";s:3:"築";s:4:"䈧";s:3:"䈧";s:4:"𥲀";s:4:"𥲀";s:4:"糒";s:3:"糒";s:4:"䊠";s:3:"䊠";s:4:"糨";s:3:"糨";s:4:"糣";s:3:"糣";s:4:"紀";s:3:"紀";s:4:"𥾆";s:4:"𥾆";s:4:"絣";s:3:"絣";s:4:"䌁";s:3:"䌁";s:4:"緇";s:3:"緇";s:4:"縂";s:3:"縂";s:4:"繅";s:3:"繅";s:4:"䌴";s:3:"䌴";s:4:"𦈨";s:4:"𦈨";s:4:"𦉇";s:4:"𦉇";s:4:"䍙";s:3:"䍙";s:4:"𦋙";s:4:"𦋙";s:4:"罺";s:3:"罺";s:4:"𦌾";s:4:"𦌾";s:4:"羕";s:3:"羕";s:4:"翺";s:3:"翺";s:4:"者";s:3:"者";s:4:"𦓚";s:4:"𦓚";s:4:"𦔣";s:4:"𦔣";s:4:"聠";s:3:"聠";s:4:"𦖨";s:4:"𦖨";s:4:"聰";s:3:"聰";s:4:"𣍟";s:4:"𣍟";s:4:"䏕";s:3:"䏕";s:4:"育";s:3:"育";s:4:"脃";s:3:"脃";s:4:"䐋";s:3:"䐋";s:4:"脾";s:3:"脾";s:4:"媵";s:3:"媵";s:4:"𦞧";s:4:"𦞧";s:4:"𦞵";s:4:"𦞵";s:4:"𣎓";s:4:"𣎓";s:4:"𣎜";s:4:"𣎜";s:4:"舁";s:3:"舁";s:4:"舄";s:3:"舄";s:4:"辞";s:3:"辞";s:4:"䑫";s:3:"䑫";s:4:"芑";s:3:"芑";s:4:"芋";s:3:"芋";s:4:"芝";s:3:"芝";s:4:"劳";s:3:"劳";s:4:"花";s:3:"花";s:4:"芳";s:3:"芳";s:4:"芽";s:3:"芽";s:4:"苦";s:3:"苦";s:4:"𦬼";s:4:"𦬼";s:4:"若";s:3:"若";s:4:"茝";s:3:"茝";s:4:"荣";s:3:"荣";s:4:"莭";s:3:"莭";s:4:"茣";s:3:"茣";s:4:"莽";s:3:"莽";s:4:"菧";s:3:"菧";s:4:"著";s:3:"著";s:4:"荓";s:3:"荓";s:4:"菊";s:3:"菊";s:4:"菌";s:3:"菌";s:4:"菜";s:3:"菜";s:4:"𦰶";s:4:"𦰶";s:4:"𦵫";s:4:"𦵫";s:4:"𦳕";s:4:"𦳕";s:4:"䔫";s:3:"䔫";s:4:"蓱";s:3:"蓱";s:4:"蓳";s:3:"蓳";s:4:"蔖";s:3:"蔖";s:4:"𧏊";s:4:"𧏊";s:4:"蕤";s:3:"蕤";s:4:"𦼬";s:4:"𦼬";s:4:"䕝";s:3:"䕝";s:4:"䕡";s:3:"䕡";s:4:"𦾱";s:4:"𦾱";s:4:"𧃒";s:4:"𧃒";s:4:"䕫";s:3:"䕫";s:4:"虐";s:3:"虐";s:4:"虜";s:3:"虜";s:4:"虧";s:3:"虧";s:4:"虩";s:3:"虩";s:4:"蚩";s:3:"蚩";s:4:"蚈";s:3:"蚈";s:4:"蜎";s:3:"蜎";s:4:"蛢";s:3:"蛢";s:4:"蝹";s:3:"蝹";s:4:"蜨";s:3:"蜨";s:4:"蝫";s:3:"蝫";s:4:"螆";s:3:"螆";s:4:"䗗";s:3:"䗗";s:4:"蟡";s:3:"蟡";s:4:"蠁";s:3:"蠁";s:4:"䗹";s:3:"䗹";s:4:"衠";s:3:"衠";s:4:"衣";s:3:"衣";s:4:"𧙧";s:4:"𧙧";s:4:"裗";s:3:"裗";s:4:"裞";s:3:"裞";s:4:"䘵";s:3:"䘵";s:4:"裺";s:3:"裺";s:4:"㒻";s:3:"㒻";s:4:"𧢮";s:4:"𧢮";s:4:"𧥦";s:4:"𧥦";s:4:"䚾";s:3:"䚾";s:4:"䛇";s:3:"䛇";s:4:"誠";s:3:"誠";s:4:"諭";s:3:"諭";s:4:"變";s:3:"變";s:4:"豕";s:3:"豕";s:4:"𧲨";s:4:"𧲨";s:4:"貫";s:3:"貫";s:4:"賁";s:3:"賁";s:4:"贛";s:3:"贛";s:4:"起";s:3:"起";s:4:"𧼯";s:4:"𧼯";s:4:"𠠄";s:4:"𠠄";s:4:"跋";s:3:"跋";s:4:"趼";s:3:"趼";s:4:"跰";s:3:"跰";s:4:"𠣞";s:4:"𠣞";s:4:"軔";s:3:"軔";s:4:"輸";s:3:"輸";s:4:"𨗒";s:4:"𨗒";s:4:"𨗭";s:4:"𨗭";s:4:"邔";s:3:"邔";s:4:"郱";s:3:"郱";s:4:"鄑";s:3:"鄑";s:4:"𨜮";s:4:"𨜮";s:4:"鄛";s:3:"鄛";s:4:"鈸";s:3:"鈸";s:4:"鋗";s:3:"鋗";s:4:"鋘";s:3:"鋘";s:4:"鉼";s:3:"鉼";s:4:"鏹";s:3:"鏹";s:4:"鐕";s:3:"鐕";s:4:"𨯺";s:4:"𨯺";s:4:"開";s:3:"開";s:4:"䦕";s:3:"䦕";s:4:"閷";s:3:"閷";s:4:"𨵷";s:4:"𨵷";s:4:"䧦";s:3:"䧦";s:4:"雃";s:3:"雃";s:4:"嶲";s:3:"嶲";s:4:"霣";s:3:"霣";s:4:"𩅅";s:4:"𩅅";s:4:"𩈚";s:4:"𩈚";s:4:"䩮";s:3:"䩮";s:4:"䩶";s:3:"䩶";s:4:"韠";s:3:"韠";s:4:"𩐊";s:4:"𩐊";s:4:"䪲";s:3:"䪲";s:4:"𩒖";s:4:"𩒖";s:4:"頋";s:3:"頋";s:4:"頋";s:3:"頋";s:4:"頩";s:3:"頩";s:4:"𩖶";s:4:"𩖶";s:4:"飢";s:3:"飢";s:4:"䬳";s:3:"䬳";s:4:"餩";s:3:"餩";s:4:"馧";s:3:"馧";s:4:"駂";s:3:"駂";s:4:"駾";s:3:"駾";s:4:"䯎";s:3:"䯎";s:4:"𩬰";s:4:"𩬰";s:4:"鬒";s:3:"鬒";s:4:"鱀";s:3:"鱀";s:4:"鳽";s:3:"鳽";s:4:"䳎";s:3:"䳎";s:4:"䳭";s:3:"䳭";s:4:"鵧";s:3:"鵧";s:4:"𪃎";s:4:"𪃎";s:4:"䳸";s:3:"䳸";s:4:"𪄅";s:4:"𪄅";s:4:"𪈎";s:4:"𪈎";s:4:"𪊑";s:4:"𪊑";s:4:"麻";s:3:"麻";s:4:"䵖";s:3:"䵖";s:4:"黹";s:3:"黹";s:4:"黾";s:3:"黾";s:4:"鼅";s:3:"鼅";s:4:"鼏";s:3:"鼏";s:4:"鼖";s:3:"鼖";s:4:"鼻";s:3:"鼻";s:4:"𪘀";s:4:"𪘀";}' );
 ?>
diff -NaurB -x .svn includes/normal/UtfNormalGenerate.php /srv/web/fp014/source/includes/normal/UtfNormalGenerate.php
--- includes/normal/UtfNormalGenerate.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalGenerate.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,7 +21,7 @@
  * This script generates UniNormalData.inc from the Unicode Character Database
  * and supplementary files.
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @access private
  */
 
@@ -175,7 +175,6 @@
 /**
  * This file was automatically generated -- do not edit!
  * Run UtfNormalGenerate.php to create this file again (make clean && make)
- * @package MediaWiki
  */
 /** */
 global \$utfCombiningClass, \$utfCanonicalComp, \$utfCanonicalDecomp, \$utfCheckNFC;
@@ -200,7 +199,6 @@
 /**
  * This file was automatically generated -- do not edit!
  * Run UtfNormalGenerate.php to create this file again (make clean && make)
- * @package MediaWiki
  */
 /** */
 global \$utfCompatibilityDecomp;
diff -NaurB -x .svn includes/normal/UtfNormal.php /srv/web/fp014/source/includes/normal/UtfNormal.php
--- includes/normal/UtfNormal.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormal.php	2007-02-01 01:03:18.000000000 +0000
@@ -29,7 +29,7 @@
  *
  * See description of forms at http://www.unicode.org/reports/tr15/
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  */
 
 /** */
@@ -112,7 +112,6 @@
 
 /**
  *
- * @package MediaWiki
  */
 class UtfNormal {
 	/**
diff -NaurB -x .svn includes/normal/UtfNormalTest.php /srv/web/fp014/source/includes/normal/UtfNormalTest.php
--- includes/normal/UtfNormalTest.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalTest.php	2007-02-01 01:03:18.000000000 +0000
@@ -20,7 +20,7 @@
 /**
  * Implements the conformance test at:
  * http://www.unicode.org/Public/UNIDATA/NormalizationTest.txt
- * @package UtfNormal
+ * @addtogroup UtfNormal
  */
 
 /** */
diff -NaurB -x .svn includes/normal/UtfNormalUtil.php /srv/web/fp014/source/includes/normal/UtfNormalUtil.php
--- includes/normal/UtfNormalUtil.php	2007-02-02 01:47:13.000000000 +0000
+++ /srv/web/fp014/source/includes/normal/UtfNormalUtil.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,7 +21,7 @@
  * Some of these functions are adapted from places in MediaWiki.
  * Should probably merge them for consistency.
  *
- * @package UtfNormal
+ * @addtogroup UtfNormal
  * @public
  */
 
diff -NaurB -x .svn includes/ObjectCache.php /srv/web/fp014/source/includes/ObjectCache.php
--- includes/ObjectCache.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ObjectCache.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 
 /**
@@ -9,8 +8,7 @@
  * It acts as a memcached server with no RAM, that is, all objects are
  * cleared the moment they are set. All set operations succeed and all
  * get operations return null.
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 class FakeMemCachedClient {
 	function add ($key, $val, $exp = 0) { return true; }
diff -NaurB -x .svn includes/OutputPage.php /srv/web/fp014/source/includes/OutputPage.php
--- includes/OutputPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/OutputPage.php	2007-02-01 01:17:09.000000000 +0000
@@ -2,12 +2,10 @@
 if ( ! defined( 'MEDIAWIKI' ) )
 	die( 1 );
 /**
- * @package MediaWiki
  */
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class OutputPage {
 	var $mMetatags, $mKeywords;
@@ -28,13 +26,14 @@
 	var $mArticleBodyOnly = false;
 	
 	var $mNewSectionLink = false;
+	var $mRedirectsEnabled = true;
 	var $mNoGallery = false;
 
 	/**
 	 * Constructor
 	 * Initialise private variables
 	 */
-	function OutputPage() {
+	function __construct() {
 		$this->mMetatags = $this->mKeywords = $this->mLinktags = array();
 		$this->mHTMLtitle = $this->mPagetitle = $this->mBodytext =
 		$this->mRedirect = $this->mLastModified =
@@ -52,9 +51,19 @@
 		$this->mETag = false;
 		$this->mRevisionId = null;
 		$this->mNewSectionLink = false;
+		$this->mRedirectsEnabled = true;
+	}
+
+	public function enableRedirects($state = true) {
+		$returnval = $this->mRedirectsEnabled;
+		$this->mRedirectsEnabled = $state;
+		return $returnval;
 	}
 	
 	public function redirect( $url, $responsecode = '302' ) {
+		if(!$this->mRedirectsEnabled){
+			return false;
+		}
 		# Strip newlines as a paranoia check for header injection in PHP<5.1.2
 		$this->mRedirect = str_replace( "\n", '', $url );
 		$this->mRedirectCode = $responsecode;
@@ -71,7 +80,7 @@
 	# To add an http-equiv meta tag, precede the name with "http:"
 	function addMeta( $name, $val ) { array_push( $this->mMetatags, array( $name, $val ) ); }
 	function addKeyword( $text ) { array_push( $this->mKeywords, $text ); }
-	function addScript( $script ) { $this->mScripts .= $script; }
+	function addScript( $script ) { $this->mScripts .= "\t\t".$script; }
 
 	/**
 	 * Add a self-contained script tag with the given contents
@@ -254,7 +263,7 @@
 		$lb->setArray( $arr );
 		$lb->execute();
 
-		$sk =& $wgUser->getSkin();
+		$sk = $wgUser->getSkin();
 		foreach ( $categories as $category => $unused ) {
 			$title = Title::makeTitleSafe( NS_CATEGORY, $category );
 			$text = $wgContLang->convertHtml( $title->getText() );
@@ -315,14 +324,26 @@
 		$this->addWikiTextTitle($text, $title, $linestart);
 	}
 
-	private function addWikiTextTitle($text, &$title, $linestart) {
+	function addWikiTextTitleTidy($text, &$title, $linestart = true) {
+		$this->addWikiTextTitle( $text, $title, $linestart, true );
+	}
+
+	public function addWikiTextTitle($text, &$title, $linestart, $tidy = false) {
 		global $wgParser;
+
 		$fname = 'OutputPage:addWikiTextTitle';
 		wfProfileIn($fname);
+
 		wfIncrStats('pcache_not_possible');
-		$parserOutput = $wgParser->parse( $text, $title, $this->parserOptions(),
+
+		$popts = $this->parserOptions();
+		$popts->setTidy($tidy);
+
+		$parserOutput = $wgParser->parse( $text, $title, $popts,
 			$linestart, true, $this->mRevisionId );
+
 		$this->addParserOutput( $parserOutput );
+
 		wfProfileOut($fname);
 	}
 
@@ -366,6 +387,7 @@
 	 * @param string  $text
 	 * @param Article $article
 	 * @param bool    $cache
+	 * @deprecated Use Article::outputWikitext
 	 */
 	public function addPrimaryWikiText( $text, $article, $cache = true ) {
 		global $wgParser, $wgUser;
@@ -384,17 +406,19 @@
 	}
 
 	/**
-	 * For anything that isn't primary text or interface message
-	 *
-	 * @param string $text
-	 * @param bool   $linestart Is this the start of a line?
+	 * @deprecated use addWikiTextTidy()
 	 */
 	public function addSecondaryWikiText( $text, $linestart = true ) {
 		global $wgTitle;
-		$popts = $this->parserOptions();
-		$popts->setTidy(true);
-		$this->addWikiTextTitle($text, $wgTitle, $linestart);
-		$popts->setTidy(false);
+		$this->addWikiTextTitleTidy($text, $wgTitle, $linestart);
+	}
+
+	/**
+	 * Add wikitext with tidy enabled
+	 */
+	public function addWikiTextTidy(  $text, $linestart = true ) {
+		global $wgTitle;
+		$this->addWikiTextTitleTidy($text, $wgTitle, $linestart);
 	}
 
 
@@ -536,6 +560,9 @@
 
 		if ( $wgUseAjax ) {
 			$this->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/ajax.js?$wgStyleVersion\"></script>\n" );
+
+			wfRunHooks( 'AjaxAddScript', array( &$this ) );
+
 			if( $wgAjaxSearch ) {
 				$this->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/ajaxsearch.js\"></script>\n" );
 				$this->addScript( "<script type=\"{$wgJsMimeType}\">hookEvent(\"load\", sajax_onload);</script>\n" );
@@ -791,7 +818,7 @@
 				$groupName = User::getGroupName( $key );
 				$groupPage = User::getGroupPage( $key );
 				if( $groupPage ) {
-					$skin =& $wgUser->getSkin();
+					$skin = $wgUser->getSkin();
 					$groups[] = '"'.$skin->makeLinkObj( $groupPage, $groupName ).'"';
 				} else {
 					$groups[] = '"'.$groupName.'"';
@@ -880,10 +907,22 @@
 			$this->setPageTitle( wfMsg( 'viewsource' ) );
 			$this->setSubtitle( wfMsg( 'viewsourcefor', $skin->makeKnownLinkObj( $wgTitle ) ) );
 
+			$cascadeSources = $wgTitle->getCascadeProtectionSources();
+
 			# Determine if protection is due to the page being a system message
 			# and show an appropriate explanation
-			if( $wgTitle->getNamespace() == NS_MEDIAWIKI && !$wgUser->isAllowed( 'editinterface' ) ) {
+			if( $wgTitle->getNamespace() == NS_MEDIAWIKI ) {
 				$this->addWikiText( wfMsg( 'protectedinterface' ) );
+			} if ( $cascadeSources && count($cascadeSources) > 0 ) {
+				$titles = '';
+	
+				foreach ( $cascadeSources as $title ) {
+					$titles .= '* [[:' . $title->getPrefixedText() . "]]\n";
+				}
+
+				$notice = wfMsg( 'cascadeprotected' ) . "\n$titles";
+
+				$this->addWikiText( $notice );
 			} else {
 				$this->addWikiText( wfMsg( 'protectedpagetext' ) );
 			}
@@ -899,17 +938,8 @@
 
 		if( is_string( $source ) ) {
 			$this->addWikiText( wfMsg( 'viewsourcetext' ) );
-			if( $source === '' ) {
-				global $wgTitle;
-				if ( $wgTitle->getNamespace() == NS_MEDIAWIKI ) {
-					$source = wfMsgWeirdKey ( $wgTitle->getText() );
-				} else {
-					$source = '';
-				}
-			}
 			$rows = $wgUser->getIntOption( 'rows' );
 			$cols = $wgUser->getIntOption( 'cols' );
-
 			$text = "\n<textarea name='wpTextbox1' id='wpTextbox1' cols='$cols' rows='$rows' readonly='readonly'>" .
 				htmlspecialchars( $source ) . "\n</textarea>";
 			$this->addHTML( $text );
@@ -1021,7 +1051,9 @@
 	 * @param ParserOutput &$parserOutput
 	 */
 	private function addKeywords( &$parserOutput ) {
-		global $wgTitle;
+		global $wgTitle, $wgSitename, $wgDBname;
+		$this->addKeyword( $wgSitename );
+		$this->addKeyword( $wgDBname );
 		$this->addKeyword( $wgTitle->getPrefixedText() );
 		$count = 1;
 		$links2d =& $parserOutput->getLinks();
@@ -1117,11 +1149,11 @@
 				"/<.*?>/" => '',
 				"/_/" => ' '
 			);
-			$ret .= "<meta name=\"keywords\" content=\"" .
+			$ret .= "\t\t<meta name=\"keywords\" content=\"" .
 			  htmlspecialchars(preg_replace(array_keys($strip), array_values($strip),implode( ",", $this->mKeywords ))) . "\" />\n";
 		}
 		foreach ( $this->mLinktags as $tag ) {
-			$ret .= '<link';
+			$ret .= "\t\t<link";
 			foreach( $tag as $attr => $val ) {
 				$ret .= " $attr=\"" . htmlspecialchars( $val ) . "\"";
 			}
diff -NaurB -x .svn includes/PageHistory.php /srv/web/fp014/source/includes/PageHistory.php
--- includes/PageHistory.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/PageHistory.php	2007-02-01 01:04:18.000000000 +0000
@@ -3,7 +3,6 @@
  * Page history
  *
  * Split off from Article.php and Skin.php, 2003-12-22
- * @package MediaWiki
  */
 
 /**
@@ -14,7 +13,6 @@
  * Construct it by passing in an Article, and call $h->history() to print the
  * history.
  *
- * @package MediaWiki
  */
 
 class PageHistory {
@@ -33,7 +31,7 @@
 	 * @param Article $article
 	 * @returns nothing
 	 */
-	function PageHistory($article) {
+	function __construct($article) {
 		global $wgUser;
 
 		$this->mArticle =& $article;
@@ -159,7 +157,7 @@
 					'class'     => 'historysubmit',
 					'type'      => 'submit',
 					'accesskey' => wfMsg( 'accesskey-compareselectedversions' ),
-					'title'     => wfMsg( 'tooltip-compareselectedversions' ),
+					'title'     => wfMsg( 'tooltip-compareselectedversions' ).' ['.wfMsg( 'accesskey-compareselectedversions' ).']',
 					'value'     => wfMsg( 'compareselectedversions' ),
 				) ) )
 			: '';
@@ -231,11 +229,11 @@
 	
 	/** @todo document */
 	function revLink( $rev ) {
-		global $wgLang;
+		global $wgLang, $wgContLang;
 		$date = $wgLang->timeanddate( wfTimestamp(TS_MW, $rev->getTimestamp()), true );
 		if( $rev->userCan( Revision::DELETED_TEXT ) ) {
 			$link = $this->mSkin->makeKnownLinkObj(
-				$this->mTitle, $date, "oldid=" . $rev->getId() );
+				$this->mTitle, $date, "oldid=" . $rev->getId(), '', '', "dir=" . $wgContLang->getDirLangStr() );
 		} else {
 			$link = $date;
 		}
@@ -247,6 +245,7 @@
 
 	/** @todo document */
 	function curLink( $rev, $latest ) {
+		global $wgContLang;
 		$cur = wfMsgExt( 'cur', array( 'escape') );
 		if( $latest || !$rev->userCan( Revision::DELETED_TEXT ) ) {
 			return $cur;
@@ -254,12 +253,13 @@
 			return $this->mSkin->makeKnownLinkObj(
 				$this->mTitle, $cur,
 				'diff=' . $this->getLatestID() .
-				"&oldid=" . $rev->getId() );
+				"&oldid=" . $rev->getId(), '', '', "dir=" . $wgContLang->getDirLangStr() );
 		}
 	}
 
 	/** @todo document */
 	function lastLink( $rev, $next, $counter ) {
+		global $wgContLang;
 		$last = wfMsgExt( 'last', array( 'escape' ) );
 		if ( is_null( $next ) ) {
 			# Probably no next row
@@ -269,14 +269,14 @@
 			return $this->mSkin->makeKnownLinkObj(
 				$this->mTitle,
 				$last,
-				"diff=" . $rev->getId() . "&oldid=prev" );
+				"diff=" . $rev->getId() . "&oldid=prev", '', '', "dir=" . $wgContLang->getDirLangStr() );
 		} elseif( !$rev->userCan( Revision::DELETED_TEXT ) ) {
 			return $last;
 		} else {
 			return $this->mSkin->makeKnownLinkObj(
 				$this->mTitle,
 				$last,
-				"diff=" . $rev->getId() . "&oldid={$next->rev_id}"
+				"diff=" . $rev->getId() . "&oldid={$next->rev_id}", '', '', "dir=" . $wgContLang->getDirLangStr()
 				/*,
 				'',
 				'',
@@ -286,10 +286,12 @@
 
 	/** @todo document */
 	function diffButtons( $rev, $firstInList, $counter ) {
+		global $wgContLang;
 		if( $this->linesonpage > 1) {
 			$radio = array(
 				'type'  => 'radio',
 				'value' => $rev->getId(),
+				'dir'   =>  $wgContLang->getDirLangStr()
 # do we really need to flood this on every item?
 #				'title' => wfMsgHtml( 'selectolderversionfordiff' )
 			);
@@ -332,7 +334,7 @@
 	function getLatestId() {
 		if( is_null( $this->mLatestId ) ) {
 			$id = $this->mTitle->getArticleID();
-			$db =& wfGetDB(DB_SLAVE);
+			$db = wfGetDB(DB_SLAVE);
 			$this->mLatestId = $db->selectField( 'page',
 				"page_latest",
 				array( 'page_id' => $id ),
@@ -349,7 +351,7 @@
 	function fetchRevisions($limit, $offset, $direction) {
 		$fname = 'PageHistory::fetchRevisions';
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		if ($direction == PageHistory::DIR_PREV)
 			list($dirs, $oper) = array("ASC", ">=");
@@ -391,7 +393,7 @@
 		if ($wgUser->isAnon() || !$wgShowUpdatedMarker)
 			return $this->mNotificationTimestamp = false;
 
-		$dbr =& wfGetDB(DB_SLAVE);
+		$dbr = wfGetDB(DB_SLAVE);
 
 		$this->mNotificationTimestamp = $dbr->selectField(
 			'watchlist',
diff -NaurB -x .svn includes/ParserCache.php /srv/web/fp014/source/includes/ParserCache.php
--- includes/ParserCache.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ParserCache.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,13 +1,11 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage Cache
+ * @addtogroup Cache
  */
 
 /**
  *
- * @package MediaWiki
  */
 class ParserCache {
 	/**
@@ -28,14 +26,14 @@
 	 *
 	 * @param object $memCached
 	 */
-	function ParserCache( &$memCached ) {
+	function __construct( &$memCached ) {
 		$this->mMemc =& $memCached;
 	}
 
 	function getKey( &$article, &$user ) {
 		global $action;
 		$hash = $user->getPageRenderingHash();
-		if( !$article->mTitle->userCanEdit() ) {
+		if( !$article->mTitle->quickUserCan( 'edit' ) ) {
 			// section edit links are suppressed even if the user has them on
 			$edit = '!edit=0';
 		} else {
diff -NaurB -x .svn includes/ParserOptions.php /srv/web/fp014/source/includes/ParserOptions.php
--- includes/ParserOptions.php	1970-01-01 00:00:00.000000000 +0000
+++ /srv/web/fp014/source/includes/ParserOptions.php	2007-02-01 01:03:18.000000000 +0000
@@ -0,0 +1,118 @@
+<?php
+
+/**
+ * Set options of the Parser
+ * @todo document
+ */
+class ParserOptions
+{
+	# All variables are supposed to be private in theory, although in practise this is not the case.
+	var $mUseTeX;                    # Use texvc to expand <math> tags
+	var $mUseDynamicDates;           # Use DateFormatter to format dates
+	var $mInterwikiMagic;            # Interlanguage links are removed and returned in an array
+	var $mAllowExternalImages;       # Allow external images inline
+	var $mAllowExternalImagesFrom;   # If not, any exception?
+	var $mSkin;                      # Reference to the preferred skin
+	var $mDateFormat;                # Date format index
+	var $mEditSection;               # Create "edit section" links
+	var $mNumberHeadings;            # Automatically number headings
+	var $mAllowSpecialInclusion;     # Allow inclusion of special pages
+	var $mTidy;                      # Ask for tidy cleanup
+	var $mInterfaceMessage;          # Which lang to call for PLURAL and GRAMMAR
+	var $mMaxIncludeSize;            # Maximum size of template expansions, in bytes
+	var $mRemoveComments;            # Remove HTML comments. ONLY APPLIES TO PREPROCESS OPERATIONS
+
+	var $mUser;                      # Stored user object, just used to initialise the skin
+
+	function getUseTeX()                        { return $this->mUseTeX; }
+	function getUseDynamicDates()               { return $this->mUseDynamicDates; }
+	function getInterwikiMagic()                { return $this->mInterwikiMagic; }
+	function getAllowExternalImages()           { return $this->mAllowExternalImages; }
+	function getAllowExternalImagesFrom()       { return $this->mAllowExternalImagesFrom; }
+	function getEditSection()                   { return $this->mEditSection; }
+	function getNumberHeadings()                { return $this->mNumberHeadings; }
+	function getAllowSpecialInclusion()         { return $this->mAllowSpecialInclusion; }
+	function getTidy()                          { return $this->mTidy; }
+	function getInterfaceMessage()              { return $this->mInterfaceMessage; }
+	function getMaxIncludeSize()                { return $this->mMaxIncludeSize; }
+	function getRemoveComments()                { return $this->mRemoveComments; }
+
+	function getSkin() {
+		if ( !isset( $this->mSkin ) ) {
+			$this->mSkin = $this->mUser->getSkin();
+		}
+		return $this->mSkin;
+	}
+
+	function getDateFormat() {
+		if ( !isset( $this->mDateFormat ) ) {
+			$this->mDateFormat = $this->mUser->getDatePreference();
+		}
+		return $this->mDateFormat;
+	}
+
+	function setUseTeX( $x )                    { return wfSetVar( $this->mUseTeX, $x ); }
+	function setUseDynamicDates( $x )           { return wfSetVar( $this->mUseDynamicDates, $x ); }
+	function setInterwikiMagic( $x )            { return wfSetVar( $this->mInterwikiMagic, $x ); }
+	function setAllowExternalImages( $x )       { return wfSetVar( $this->mAllowExternalImages, $x ); }
+	function setAllowExternalImagesFrom( $x )   { return wfSetVar( $this->mAllowExternalImagesFrom, $x ); }
+	function setDateFormat( $x )                { return wfSetVar( $this->mDateFormat, $x ); }
+	function setEditSection( $x )               { return wfSetVar( $this->mEditSection, $x ); }
+	function setNumberHeadings( $x )            { return wfSetVar( $this->mNumberHeadings, $x ); }
+	function setAllowSpecialInclusion( $x )     { return wfSetVar( $this->mAllowSpecialInclusion, $x ); }
+	function setTidy( $x )                      { return wfSetVar( $this->mTidy, $x); }
+	function setSkin( $x )                      { $this->mSkin = $x; }
+	function setInterfaceMessage( $x )          { return wfSetVar( $this->mInterfaceMessage, $x); }
+	function setMaxIncludeSize( $x )            { return wfSetVar( $this->mMaxIncludeSize, $x ); }
+	function setRemoveComments( $x )            { return wfSetVar( $this->mRemoveComments, $x ); }
+
+	function __construct( $user = null ) {
+		$this->initialiseFromUser( $user );
+	}
+
+	/**
+	 * Get parser options
+	 * @static
+	 */
+	static function newFromUser( $user ) {
+		return new ParserOptions( $user );
+	}
+
+	/** Get user options */
+	function initialiseFromUser( $userInput ) {
+		global $wgUseTeX, $wgUseDynamicDates, $wgInterwikiMagic, $wgAllowExternalImages;
+		global $wgAllowExternalImagesFrom, $wgAllowSpecialInclusion, $wgMaxArticleSize;
+		$fname = 'ParserOptions::initialiseFromUser';
+		wfProfileIn( $fname );
+		if ( !$userInput ) {
+			global $wgUser;
+			if ( isset( $wgUser ) ) {
+				$user = $wgUser;
+			} else {
+				$user = new User;
+			}
+		} else {
+			$user =& $userInput;
+		}
+
+		$this->mUser = $user;
+
+		$this->mUseTeX = $wgUseTeX;
+		$this->mUseDynamicDates = $wgUseDynamicDates;
+		$this->mInterwikiMagic = $wgInterwikiMagic;
+		$this->mAllowExternalImages = $wgAllowExternalImages;
+		$this->mAllowExternalImagesFrom = $wgAllowExternalImagesFrom;
+		$this->mSkin = null; # Deferred
+		$this->mDateFormat = null; # Deferred
+		$this->mEditSection = true;
+		$this->mNumberHeadings = $user->getOption( 'numberheadings' );
+		$this->mAllowSpecialInclusion = $wgAllowSpecialInclusion;
+		$this->mTidy = false;
+		$this->mInterfaceMessage = false;
+		$this->mMaxIncludeSize = $wgMaxArticleSize * 1024;
+		$this->mRemoveComments = true;
+		wfProfileOut( $fname );
+	}
+}
+
+?>
diff -NaurB -x .svn includes/ParserOutput.php /srv/web/fp014/source/includes/ParserOutput.php
--- includes/ParserOutput.php	1970-01-01 00:00:00.000000000 +0000
+++ /srv/web/fp014/source/includes/ParserOutput.php	2007-02-01 01:03:18.000000000 +0000
@@ -0,0 +1,117 @@
+<?php
+/**
+ * @todo document
+ */
+class ParserOutput
+{
+	var $mText,             # The output text
+		$mLanguageLinks,    # List of the full text of language links, in the order they appear
+		$mCategories,       # Map of category names to sort keys
+		$mContainsOldMagic, # Boolean variable indicating if the input contained variables like {{CURRENTDAY}}
+		$mCacheTime,        # Time when this object was generated, or -1 for uncacheable. Used in ParserCache.
+		$mVersion,          # Compatibility check
+		$mTitleText,        # title text of the chosen language variant
+		$mLinks,            # 2-D map of NS/DBK to ID for the links in the document. ID=zero for broken.
+		$mTemplates,        # 2-D map of NS/DBK to ID for the template references. ID=zero for broken.
+		$mImages,           # DB keys of the images used, in the array key only
+		$mExternalLinks,    # External link URLs, in the key only
+		$mHTMLtitle,		# Display HTML title
+		$mSubtitle,			# Additional subtitle
+		$mNewSection,		# Show a new section link?
+		$mNoGallery;		# No gallery on category page? (__NOGALLERY__)
+
+	function ParserOutput( $text = '', $languageLinks = array(), $categoryLinks = array(),
+		$containsOldMagic = false, $titletext = '' )
+	{
+		$this->mText = $text;
+		$this->mLanguageLinks = $languageLinks;
+		$this->mCategories = $categoryLinks;
+		$this->mContainsOldMagic = $containsOldMagic;
+		$this->mCacheTime = '';
+		$this->mVersion = Parser::VERSION;
+		$this->mTitleText = $titletext;
+		$this->mLinks = array();
+		$this->mTemplates = array();
+		$this->mImages = array();
+		$this->mExternalLinks = array();
+		$this->mHTMLtitle = "" ;
+		$this->mSubtitle = "" ;
+		$this->mNewSection = false;
+		$this->mNoGallery = false;
+	}
+
+	function getText()                   { return $this->mText; }
+	function &getLanguageLinks()          { return $this->mLanguageLinks; }
+	function getCategoryLinks()          { return array_keys( $this->mCategories ); }
+	function &getCategories()            { return $this->mCategories; }
+	function getCacheTime()              { return $this->mCacheTime; }
+	function getTitleText()              { return $this->mTitleText; }
+	function &getLinks()                 { return $this->mLinks; }
+	function &getTemplates()             { return $this->mTemplates; }
+	function &getImages()                { return $this->mImages; }
+	function &getExternalLinks()         { return $this->mExternalLinks; }
+	function getNoGallery()              { return $this->mNoGallery; }
+	function getSubtitle()               { return $this->mSubtitle; }
+
+	function containsOldMagic()          { return $this->mContainsOldMagic; }
+	function setText( $text )            { return wfSetVar( $this->mText, $text ); }
+	function setLanguageLinks( $ll )     { return wfSetVar( $this->mLanguageLinks, $ll ); }
+	function setCategoryLinks( $cl )     { return wfSetVar( $this->mCategories, $cl ); }
+	function setContainsOldMagic( $com ) { return wfSetVar( $this->mContainsOldMagic, $com ); }
+	function setCacheTime( $t )          { return wfSetVar( $this->mCacheTime, $t ); }
+	function setTitleText( $t )          { return wfSetVar($this->mTitleText, $t); }
+	function setSubtitle( $st )          { return wfSetVar( $this->mSubtitle, $st ); }
+
+	function addCategory( $c, $sort )    { $this->mCategories[$c] = $sort; }
+	function addImage( $name )           { $this->mImages[$name] = 1; }
+	function addLanguageLink( $t )       { $this->mLanguageLinks[] = $t; }
+	function addExternalLink( $url )     { $this->mExternalLinks[$url] = 1; }
+
+	function setNewSection( $value ) {
+		$this->mNewSection = (bool)$value;
+	}
+	function getNewSection() {
+		return (bool)$this->mNewSection;
+	}
+
+	function addLink( $title, $id = null ) {
+		$ns = $title->getNamespace();
+		$dbk = $title->getDBkey();
+		if ( !isset( $this->mLinks[$ns] ) ) {
+			$this->mLinks[$ns] = array();
+		}
+		if ( is_null( $id ) ) {
+			$id = $title->getArticleID();
+		}
+		$this->mLinks[$ns][$dbk] = $id;
+	}
+
+	function addTemplate( $title, $id ) {
+		$ns = $title->getNamespace();
+		$dbk = $title->getDBkey();
+		if ( !isset( $this->mTemplates[$ns] ) ) {
+			$this->mTemplates[$ns] = array();
+		}
+		$this->mTemplates[$ns][$dbk] = $id;
+	}
+
+	/**
+	 * Return true if this cached output object predates the global or
+	 * per-article cache invalidation timestamps, or if it comes from
+	 * an incompatible older version.
+	 *
+	 * @param string $touched the affected article's last touched timestamp
+	 * @return bool
+	 * @public
+	 */
+	function expired( $touched ) {
+		global $wgCacheEpoch;
+		return $this->getCacheTime() == -1 || // parser says it's uncacheable
+		       $this->getCacheTime() < $touched ||
+		       $this->getCacheTime() <= $wgCacheEpoch ||
+		       !isset( $this->mVersion ) ||
+		       version_compare( $this->mVersion, Parser::VERSION, "lt" );
+	}
+}
+
+?>
diff -NaurB -x .svn includes/Parser.php /srv/web/fp014/source/includes/Parser.php
--- includes/Parser.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Parser.php	2007-02-01 01:04:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * File for Parser and related classes
  *
- * @package MediaWiki
- * @subpackage Parser
+ * @addtogroup Parser
  */
 
 /**
@@ -86,10 +85,10 @@
  *  * only within ParserOptions
  * </pre>
  *
- * @package MediaWiki
  */
 class Parser
 {
+	const VERSION = MW_PARSER_VERSION;
 	/**#@+
 	 * @private
 	 */
@@ -114,7 +113,7 @@
 		$ot,            // Shortcut alias, see setOutputType()
 		$mRevisionId,   // ID to display in {{REVISIONID}} tags
 		$mRevisionTimestamp, // The timestamp of the specified revision ID
-		$mRevIdForTs;   // The revision ID which was used to fetch the timestamp  
+		$mRevIdForTs;   // The revision ID which was used to fetch the timestamp
 
 	/**#@-*/
 
@@ -211,7 +210,7 @@
 			'titles' => array()
 		);
 		$this->mRevisionTimestamp = $this->mRevisionId = null;
-		
+
 		/**
 		 * Prefix for temporary replacement strings for the multipass parser.
 		 * \x07 should never appear in input as it's disallowed in XML.
@@ -262,7 +261,6 @@
 	 * Convert wikitext to HTML
 	 * Do not call this function recursively.
 	 *
-	 * @private
 	 * @param string $text Text we want to parse
 	 * @param Title &$title A title object
 	 * @param array $options
@@ -271,7 +269,7 @@
 	 * @param int $revid number to pass in {{REVISIONID}}
 	 * @return ParserOutput a ParserOutput
 	 */
-	function parse( $text, &$title, $options, $linestart = true, $clearState = true, $revid = null ) {
+	public function parse( $text, &$title, $options, $linestart = true, $clearState = true, $revid = null ) {
 		/**
 		 * First pass--just handle <nowiki> sections, pass the rest off
 		 * to internalParse() which does all the real work.
@@ -585,9 +583,11 @@
 				case 'nowiki':
 					$output = Xml::escapeTagsOnly( $content );
 					break;
+				/*
 				case 'math':
 					$output = $wgContLang->armourMath( MathRenderer::renderMath( $content ) );
 					break;
+				*/
 				case 'gallery':
 					$output = $this->renderImageGallery( $content, $params );
 					break;
@@ -724,7 +724,7 @@
 		$descriptorspec = array(
 			0 => array('pipe', 'r'),
 			1 => array('pipe', 'w'),
-			2 => array('file', '/dev/null', 'a')
+			2 => array('file', '/dev/null', 'a')  // FIXME: this line in UNIX-specific, it generates a warning on Windows, because /dev/null is not a valid Windows file.
 		);
 		$pipes = array();
 		$process = proc_open("$wgTidyBin -config $wgTidyConf $wgTidyOpts$opts", $descriptorspec, $pipes);
@@ -872,7 +872,7 @@
 				array_push ( $td_history , false );
 				array_push ( $last_tag_history , '' );
 			}
-			else if ( $first_character == '|' || $first_character == '!' || substr ( $line , 0 , 2 )  == '|+' ) { 
+			else if ( $first_character == '|' || $first_character == '!' || substr ( $line , 0 , 2 )  == '|+' ) {
 				// This might be cell elements, td, th or captions
 				if ( substr ( $line , 0 , 2 ) == '|+' ) {
 					$first_character = '+';
@@ -1086,7 +1086,7 @@
 			}
 
 			$url = wfMsg( $urlmsg, $id);
-			$sk =& $this->mOptions->getSkin();
+			$sk = $this->mOptions->getSkin();
 			$la = $sk->getExternalLinkAttributes( $url, $keyword.$id );
 			$text = "<a href=\"{$url}\"{$la}>{$keyword} {$id}</a>";
 		}
@@ -1306,7 +1306,7 @@
 		$fname = 'Parser::replaceExternalLinks';
 		wfProfileIn( $fname );
 
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 
 		$bits = preg_split( EXT_LINK_BRACKETED, $text, -1, PREG_SPLIT_DELIM_CAPTURE );
 
@@ -1395,7 +1395,7 @@
 		$s = array_shift( $bits );
 		$i = 0;
 
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 
 		while ( $i < count( $bits ) ){
 			$protocol = $bits[$i++];
@@ -1503,7 +1503,7 @@
 	 * @private
 	 */
 	function maybeMakeExternalImage( $url ) {
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 		$imagesfrom = $this->mOptions->getAllowExternalImagesFrom();
 		$imagesexception = !empty($imagesfrom);
 		$text = false;
@@ -1533,7 +1533,7 @@
 		# the % is needed to support urlencoded titles as well
 		if ( !$tc ) { $tc = Title::legalChars() . '#%'; }
 
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 
 		#split the entire text string on occurences of [[
 		$a = explode( '[[', ' ' . $s );
@@ -1569,10 +1568,11 @@
 			$prefix = '';
 		}
 
-		if($wgContLang->hasVariants())
+		if($wgContLang->hasVariants()) {
 			$selflink = $wgContLang->convertLinkToAllVariants($this->mTitle->getPrefixedText());
-		else 
+		} else {
 			$selflink = array($this->mTitle->getPrefixedText());
+		}
 		$useSubpages = $this->areSubpagesAllowed();
 		wfProfileOut( $fname.'-setup' );
 
@@ -1778,11 +1778,12 @@
 				}
 			}
 
-			if( ( in_array( $nt->getPrefixedText(), $selflink ) ) &&
-			    ( $nt->getFragment() === '' ) ) {
-				# Self-links are handled specially; generally de-link and change to bold.
-				$s .= $prefix . $sk->makeSelfLinkObj( $nt, $text, '', $trail );
-				continue;
+			# Self-link checking
+			if( $nt->getFragment() === '' ) {
+				if( in_array( $nt->getPrefixedText(), $selflink, true ) ) {
+					$s .= $prefix . $sk->makeSelfLinkObj( $nt, $text, '', $trail );
+					continue;
+				}
 			}
 
 			# Special and Media are pseudo-namespaces; no pages actually exist in them
@@ -1862,7 +1863,7 @@
 	 */
 	function makeKnownLinkHolder( $nt, $text = '', $query = '', $trail = '', $prefix = '' ) {
 		list( $inside, $trail ) = Linker::splitTrail( $trail );
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 		$link = $sk->makeKnownLinkObj( $nt, $text, $query, $inside, $prefix );
 		return $this->armorLinks( $link ) . $trail;
 	}
@@ -1923,9 +1924,9 @@
 			# Look at the first character
 			if( $target != '' && $target{0} == '/' ) {
 				# / at end means we don't want the slash to be shown
-				if( substr( $target, -1, 1 ) == '/' ) {
-					$target = substr( $target, 1, -1 );
-					$noslash = $target;
+				$trailingSlashes = preg_match_all( '%(/+)$%', $target, $m );
+				if( $trailingSlashes ) {
+					$noslash = $target = substr( $target, 1, -strlen($m[0][0]) );
 				} else {
 					$noslash = substr( $target, 1 );
 				}
@@ -2852,7 +2853,7 @@
 		return $text;
 	}
 
-		
+
 	/// Clean up argument array - refactored in 1.9 so parserfunctions can use it, too.
 	static function createAssocArgs( $args ) {
 		$assocArgs = array();
@@ -2872,10 +2873,10 @@
 				}
 			}
 		}
-		
+
 		return $assocArgs;
 	}
-	
+
 	/**
 	 * Return the text of a template, after recursively
 	 * replacing any variables or templates within the template.
@@ -3271,7 +3272,7 @@
 			return wfMsg('scarytranscludedisabled');
 
 		$url = $title->getFullUrl( "action=$action" );
-		
+
 		if (strlen($url) > 255)
 			return wfMsg('scarytranscludetoolong');
 		return $this->fetchScaryTemplateMaybeFromCache($url);
@@ -3279,7 +3280,7 @@
 
 	function fetchScaryTemplateMaybeFromCache($url) {
 		global $wgTranscludeCacheExpiry;
-		$dbr =& wfGetDB(DB_SLAVE);
+		$dbr = wfGetDB(DB_SLAVE);
 		$obj = $dbr->selectRow('transcache', array('tc_time', 'tc_contents'),
 				array('tc_url' => $url));
 		if ($obj) {
@@ -3294,7 +3295,7 @@
 		if (!$text)
 			return wfMsg('scarytranscludefailed', $url);
 
-		$dbw =& wfGetDB(DB_MASTER);
+		$dbw = wfGetDB(DB_MASTER);
 		$dbw->replace('transcache', array('tc_url'), array(
 			'tc_url' => $url,
 			'tc_time' => time(),
@@ -3395,7 +3396,7 @@
 		global $wgMaxTocLevel, $wgContLang;
 
 		$doNumberHeadings = $this->mOptions->getNumberHeadings();
-		if( !$this->mTitle->userCanEdit() ) {
+		if( !$this->mTitle->quickUserCan( 'edit' ) ) {
 			$showEditLink = 0;
 		} else {
 			$showEditLink = $this->mOptions->getEditSection();
@@ -3437,7 +3438,7 @@
 		}
 
 		# We need this to perform operations on the HTML
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 
 		# headline counter
 		$headlineCount = 0;
@@ -3723,11 +3724,7 @@
 		}
 
 		# Trim trailing whitespace
-		# __END__ tag allows for trailing
-		# whitespace to be deliberately included
 		$text = rtrim( $text );
-		$mw =& MagicWord::get( 'end' );
-		$mw->matchAndRemove( $text );
 
 		return $text;
 	}
@@ -3847,7 +3844,7 @@
 
 		wfProfileIn($fname);
 
-		if ( $wgTitle ) {
+		if ( $wgTitle && !( $wgTitle instanceof FakeTitle ) ) {
 			$this->mTitle = $wgTitle;
 		} else {
 			$this->mTitle = Title::newFromText('msg');
@@ -3966,12 +3963,12 @@
 
 		$pdbks = array();
 		$colours = array();
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 		$linkCache =& LinkCache::singleton();
 
 		if ( !empty( $this->mLinkHolders['namespaces'] ) ) {
 			wfProfileIn( $fname.'-check' );
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$page = $dbr->tableName( 'page' );
 			$threshold = $wgUser->getOption('stubthreshold');
 
@@ -4161,8 +4158,8 @@
 						if(isset($categoryMap[$vardbk])){
 							$oldkey = $categoryMap[$vardbk];
 							if($oldkey != $vardbk)
-								$varCategories[$oldkey]=$vardbk;							
-						}						
+								$varCategories[$oldkey]=$vardbk;
+						}
 					}
 
 					// rebuild the categories in original order (if there are replacements)
@@ -4303,6 +4300,7 @@
 	 */
 	function renderImageGallery( $text, $params ) {
 		$ig = new ImageGallery();
+		$ig->setContextTitle( $this->mTitle );
 		$ig->setShowBytes( false );
 		$ig->setShowFilename( false );
 		$ig->setParsing();
@@ -4439,7 +4437,7 @@
 		$alt = Sanitizer::stripAllTags( $alt );
 
 		# Linker does the rest
-		$sk =& $this->mOptions->getSkin();
+		$sk = $this->mOptions->getSkin();
 		return $sk->makeImageLinkObj( $nt, $caption, $alt, $align, $width, $height, $framed, $thumb, $manual_thumb, $page );
 	}
 
@@ -4639,23 +4637,23 @@
 	}
 
 	/**
-	 * Get the timestamp associated with the current revision, adjusted for 
+	 * Get the timestamp associated with the current revision, adjusted for
 	 * the default server-local timestamp
 	 */
 	function getRevisionTimestamp() {
 		if ( is_null( $this->mRevisionTimestamp ) ) {
 			wfProfileIn( __METHOD__ );
 			global $wgContLang;
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$timestamp = $dbr->selectField( 'revision', 'rev_timestamp',
 					array( 'rev_id' => $this->mRevisionId ), __METHOD__ );
-			
+
 			// Normalize timestamp to internal MW format for timezone processing.
 			// This has the added side-effect of replacing a null value with
 			// the current time, which gives us more sensible behavior for
 			// previews.
 			$timestamp = wfTimestamp( TS_MW, $timestamp );
-			
+
 			// The cryptic '' timezone parameter tells to use the site-default
 			// timezone offset instead of the user settings.
 			//
@@ -4663,12 +4661,12 @@
 			// to other users, and potentially even used inside links and such,
 			// it needs to be consistent for all visitors.
 			$this->mRevisionTimestamp = $wgContLang->userAdjust( $timestamp, '' );
-			
+
 			wfProfileOut( __METHOD__ );
 		}
 		return $this->mRevisionTimestamp;
 	}
-	
+
 	/**
 	 * Mutator for $mDefaultSort
 	 *
@@ -4677,7 +4675,7 @@
 	public function setDefaultSort( $sort ) {
 		$this->mDefaultSort = $sort;
 	}
-	
+
 	/**
 	 * Accessor for $mDefaultSort
 	 * Will use the title/prefixed title if none is set
@@ -4693,239 +4691,7 @@
 					: $this->mTitle->getPrefixedText();
 		}
 	}
-	
-}
 
-/**
- * @todo document
- * @package MediaWiki
- */
-class ParserOutput
-{
-	var $mText,             # The output text
-		$mLanguageLinks,    # List of the full text of language links, in the order they appear
-		$mCategories,       # Map of category names to sort keys
-		$mContainsOldMagic, # Boolean variable indicating if the input contained variables like {{CURRENTDAY}}
-		$mCacheTime,        # Time when this object was generated, or -1 for uncacheable. Used in ParserCache.
-		$mVersion,          # Compatibility check
-		$mTitleText,        # title text of the chosen language variant
-		$mLinks,            # 2-D map of NS/DBK to ID for the links in the document. ID=zero for broken.
-		$mTemplates,        # 2-D map of NS/DBK to ID for the template references. ID=zero for broken.
-		$mImages,           # DB keys of the images used, in the array key only
-		$mExternalLinks,    # External link URLs, in the key only
-		$mHTMLtitle,		# Display HTML title
-		$mSubtitle,			# Additional subtitle
-		$mNewSection,		# Show a new section link?
-		$mNoGallery;		# No gallery on category page? (__NOGALLERY__)
-
-	function ParserOutput( $text = '', $languageLinks = array(), $categoryLinks = array(),
-		$containsOldMagic = false, $titletext = '' )
-	{
-		$this->mText = $text;
-		$this->mLanguageLinks = $languageLinks;
-		$this->mCategories = $categoryLinks;
-		$this->mContainsOldMagic = $containsOldMagic;
-		$this->mCacheTime = '';
-		$this->mVersion = MW_PARSER_VERSION;
-		$this->mTitleText = $titletext;
-		$this->mLinks = array();
-		$this->mTemplates = array();
-		$this->mImages = array();
-		$this->mExternalLinks = array();
-		$this->mHTMLtitle = "" ;
-		$this->mSubtitle = "" ;
-		$this->mNewSection = false;
-		$this->mNoGallery = false;
-	}
-
-	function getText()                   { return $this->mText; }
-	function &getLanguageLinks()          { return $this->mLanguageLinks; }
-	function getCategoryLinks()          { return array_keys( $this->mCategories ); }
-	function &getCategories()            { return $this->mCategories; }
-	function getCacheTime()              { return $this->mCacheTime; }
-	function getTitleText()              { return $this->mTitleText; }
-	function &getLinks()                 { return $this->mLinks; }
-	function &getTemplates()             { return $this->mTemplates; }
-	function &getImages()                { return $this->mImages; }
-	function &getExternalLinks()         { return $this->mExternalLinks; }
-	function getNoGallery()              { return $this->mNoGallery; }
-	function getSubtitle()               { return $this->mSubtitle; }
-
-	function containsOldMagic()          { return $this->mContainsOldMagic; }
-	function setText( $text )            { return wfSetVar( $this->mText, $text ); }
-	function setLanguageLinks( $ll )     { return wfSetVar( $this->mLanguageLinks, $ll ); }
-	function setCategoryLinks( $cl )     { return wfSetVar( $this->mCategories, $cl ); }
-	function setContainsOldMagic( $com ) { return wfSetVar( $this->mContainsOldMagic, $com ); }
-	function setCacheTime( $t )          { return wfSetVar( $this->mCacheTime, $t ); }
-	function setTitleText( $t )          { return wfSetVar($this->mTitleText, $t); }
-	function setSubtitle( $st )          { return wfSetVar( $this->mSubtitle, $st ); }
-
-	function addCategory( $c, $sort )    { $this->mCategories[$c] = $sort; }
-	function addImage( $name )           { $this->mImages[$name] = 1; }
-	function addLanguageLink( $t )       { $this->mLanguageLinks[] = $t; }
-	function addExternalLink( $url )     { $this->mExternalLinks[$url] = 1; }
-
-	function setNewSection( $value ) {
-		$this->mNewSection = (bool)$value;
-	}
-	function getNewSection() {
-		return (bool)$this->mNewSection;
-	}
-
-	function addLink( $title, $id = null ) {
-		$ns = $title->getNamespace();
-		$dbk = $title->getDBkey();
-		if ( !isset( $this->mLinks[$ns] ) ) {
-			$this->mLinks[$ns] = array();
-		}
-		if ( is_null( $id ) ) {
-			$id = $title->getArticleID();
-		}
-		$this->mLinks[$ns][$dbk] = $id;
-	}
-
-	function addTemplate( $title, $id ) {
-		$ns = $title->getNamespace();
-		$dbk = $title->getDBkey();
-		if ( !isset( $this->mTemplates[$ns] ) ) {
-			$this->mTemplates[$ns] = array();
-		}
-		$this->mTemplates[$ns][$dbk] = $id;
-	}
-
-	/**
-	 * Return true if this cached output object predates the global or
-	 * per-article cache invalidation timestamps, or if it comes from
-	 * an incompatible older version.
-	 *
-	 * @param string $touched the affected article's last touched timestamp
-	 * @return bool
-	 * @public
-	 */
-	function expired( $touched ) {
-		global $wgCacheEpoch;
-		return $this->getCacheTime() == -1 || // parser says it's uncacheable
-		       $this->getCacheTime() < $touched ||
-		       $this->getCacheTime() <= $wgCacheEpoch ||
-		       !isset( $this->mVersion ) ||
-		       version_compare( $this->mVersion, MW_PARSER_VERSION, "lt" );
-	}
-}
-
-/**
- * Set options of the Parser
- * @todo document
- * @package MediaWiki
- */
-class ParserOptions
-{
-	# All variables are supposed to be private in theory, although in practise this is not the case.
-	var $mUseTeX;                    # Use texvc to expand <math> tags
-	var $mUseDynamicDates;           # Use DateFormatter to format dates
-	var $mInterwikiMagic;            # Interlanguage links are removed and returned in an array
-	var $mAllowExternalImages;       # Allow external images inline
-	var $mAllowExternalImagesFrom;   # If not, any exception?
-	var $mSkin;                      # Reference to the preferred skin
-	var $mDateFormat;                # Date format index
-	var $mEditSection;               # Create "edit section" links
-	var $mNumberHeadings;            # Automatically number headings
-	var $mAllowSpecialInclusion;     # Allow inclusion of special pages
-	var $mTidy;                      # Ask for tidy cleanup
-	var $mInterfaceMessage;          # Which lang to call for PLURAL and GRAMMAR
-	var $mMaxIncludeSize;            # Maximum size of template expansions, in bytes
-	var $mRemoveComments;            # Remove HTML comments. ONLY APPLIES TO PREPROCESS OPERATIONS
-
-	var $mUser;                      # Stored user object, just used to initialise the skin
-
-	function getUseTeX()                        { return $this->mUseTeX; }
-	function getUseDynamicDates()               { return $this->mUseDynamicDates; }
-	function getInterwikiMagic()                { return $this->mInterwikiMagic; }
-	function getAllowExternalImages()           { return $this->mAllowExternalImages; }
-	function getAllowExternalImagesFrom()       { return $this->mAllowExternalImagesFrom; }
-	function getEditSection()                   { return $this->mEditSection; }
-	function getNumberHeadings()                { return $this->mNumberHeadings; }
-	function getAllowSpecialInclusion()         { return $this->mAllowSpecialInclusion; }
-	function getTidy()                          { return $this->mTidy; }
-	function getInterfaceMessage()              { return $this->mInterfaceMessage; }
-	function getMaxIncludeSize()                { return $this->mMaxIncludeSize; }
-	function getRemoveComments()                { return $this->mRemoveComments; }
-
-	function &getSkin() {
-		if ( !isset( $this->mSkin ) ) {
-			$this->mSkin = $this->mUser->getSkin();
-		}
-		return $this->mSkin;
-	}
-
-	function getDateFormat() {
-		if ( !isset( $this->mDateFormat ) ) {
-			$this->mDateFormat = $this->mUser->getDatePreference();
-		}
-		return $this->mDateFormat;
-	}
-
-	function setUseTeX( $x )                    { return wfSetVar( $this->mUseTeX, $x ); }
-	function setUseDynamicDates( $x )           { return wfSetVar( $this->mUseDynamicDates, $x ); }
-	function setInterwikiMagic( $x )            { return wfSetVar( $this->mInterwikiMagic, $x ); }
-	function setAllowExternalImages( $x )       { return wfSetVar( $this->mAllowExternalImages, $x ); }
-	function setAllowExternalImagesFrom( $x )   { return wfSetVar( $this->mAllowExternalImagesFrom, $x ); }
-	function setDateFormat( $x )                { return wfSetVar( $this->mDateFormat, $x ); }
-	function setEditSection( $x )               { return wfSetVar( $this->mEditSection, $x ); }
-	function setNumberHeadings( $x )            { return wfSetVar( $this->mNumberHeadings, $x ); }
-	function setAllowSpecialInclusion( $x )     { return wfSetVar( $this->mAllowSpecialInclusion, $x ); }
-	function setTidy( $x )                      { return wfSetVar( $this->mTidy, $x); }
-	function setSkin( $x )                      { $this->mSkin = $x; }
-	function setInterfaceMessage( $x )          { return wfSetVar( $this->mInterfaceMessage, $x); }
-	function setMaxIncludeSize( $x )            { return wfSetVar( $this->mMaxIncludeSize, $x ); }
-	function setRemoveComments( $x )            { return wfSetVar( $this->mRemoveComments, $x ); }
-
-	function ParserOptions( $user = null ) {
-		$this->initialiseFromUser( $user );
-	}
-
-	/**
-	 * Get parser options
-	 * @static
-	 */
-	static function newFromUser( $user ) {
-		return new ParserOptions( $user );
-	}
-
-	/** Get user options */
-	function initialiseFromUser( $userInput ) {
-		global $wgUseTeX, $wgUseDynamicDates, $wgInterwikiMagic, $wgAllowExternalImages;
-		global $wgAllowExternalImagesFrom, $wgAllowSpecialInclusion, $wgMaxArticleSize;
-		$fname = 'ParserOptions::initialiseFromUser';
-		wfProfileIn( $fname );
-		if ( !$userInput ) {
-			global $wgUser;
-			if ( isset( $wgUser ) ) {
-				$user = $wgUser;
-			} else {
-				$user = new User;
-			}
-		} else {
-			$user =& $userInput;
-		}
-
-		$this->mUser = $user;
-
-		$this->mUseTeX = $wgUseTeX;
-		$this->mUseDynamicDates = $wgUseDynamicDates;
-		$this->mInterwikiMagic = $wgInterwikiMagic;
-		$this->mAllowExternalImages = $wgAllowExternalImages;
-		$this->mAllowExternalImagesFrom = $wgAllowExternalImagesFrom;
-		$this->mSkin = null; # Deferred
-		$this->mDateFormat = null; # Deferred
-		$this->mEditSection = true;
-		$this->mNumberHeadings = $user->getOption( 'numberheadings' );
-		$this->mAllowSpecialInclusion = $wgAllowSpecialInclusion;
-		$this->mTidy = false;
-		$this->mInterfaceMessage = false;
-		$this->mMaxIncludeSize = $wgMaxArticleSize * 1024;
-		$this->mRemoveComments = true;
-		wfProfileOut( $fname );
-	}
 }
 
 class OnlyIncludeReplacer {
diff -NaurB -x .svn includes/PatrolLog.php /srv/web/fp014/source/includes/PatrolLog.php
--- includes/PatrolLog.php	1970-01-01 00:00:00.000000000 +0000
+++ /srv/web/fp014/source/includes/PatrolLog.php	2007-02-01 01:03:18.000000000 +0000
@@ -0,0 +1,83 @@
+<?php
+
+/**
+ * Class containing static functions for working with
+ * logs of patrol events
+ *
+ * @author Rob Church <robchur@gmail.com>
+ */
+class PatrolLog {
+
+	/**
+	 * Record a log event for a change being patrolled
+	 *
+	 * @param mixed $change Change identifier or RecentChange object
+	 * @param bool $auto Was this patrol event automatic?
+	 */
+	public static function record( $change, $auto = false ) {
+		if( !( is_object( $change ) && $change instanceof RecentChange ) ) {
+			$change = RecentChange::newFromId( $change );
+			if( !is_object( $change ) )
+				return false;
+		}
+		$title = Title::makeTitleSafe( $change->getAttribute( 'rc_namespace' ),
+					$change->getAttribute( 'rc_title' ) );
+		if( is_object( $title ) ) {
+			$params = self::buildParams( $change, $auto );
+			$log = new LogPage( 'patrol', false ); # False suppresses RC entries
+			$log->addEntry( 'patrol', $title, '', $params );
+			return true;
+		} else {
+			return false;
+		}
+	}
+	
+	/**
+	 * Generate the log action text corresponding to a patrol log item
+	 *
+	 * @param Title $title Title of the page that was patrolled
+	 * @param array $params Log parameters (from logging.log_params)
+	 * @param Skin $skin Skin to use for building links, etc.
+	 * @return string
+	 */
+	public static function makeActionText( $title, $params, $skin ) {
+		# This is a bit of a hack, but...if $skin is not a Skin, then *do nothing*
+		# -- this is fine, because the action text we would be queried for under
+		# these conditions would have gone into recentchanges, which we aren't
+		# supposed to be updating
+		if( is_object( $skin ) ) {
+			list( $cur, $prev, $auto ) = $params;
+			# Standard link to the page in question
+			$link = $skin->makeLinkObj( $title );
+			# Generate a diff link
+			$bits[] = 'oldid=' . urlencode( $cur );
+			$bits[] = 'diff=prev';
+			$bits = implode( '&', $bits );
+			$diff = $skin->makeLinkObj( $title, htmlspecialchars( wfMsg( 'patrol-log-diff', $cur ) ), $bits );
+			# Indicate whether or not the patrolling was automatic
+			$auto = $auto ? wfMsgHtml( 'patrol-log-auto' ) : '';
+			# Put it all together
+			return wfMsgHtml( 'patrol-log-line', $diff, $link, $auto );
+		} else {
+			return '';
+		}
+	}
+	
+	/**
+	 * Prepare log parameters for a patrolled change
+	 *
+	 * @param RecentChange $change RecentChange to represent
+	 * @param bool $auto Whether the patrol event was automatic
+	 * @return array
+	 */
+	private static function buildParams( $change, $auto ) {
+		return array(
+			$change->getAttribute( 'rc_this_oldid' ),
+			$change->getAttribute( 'rc_last_oldid' ),
+			(int)$auto
+		);
+	}
+
+}
+
+?>
\ No newline at end of file
diff -NaurB -x .svn includes/Profiler.php /srv/web/fp014/source/includes/Profiler.php
--- includes/Profiler.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Profiler.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * This file is only included if profiling is enabled
- * @package MediaWiki
  */
 
 $wgProfiling = true;
@@ -41,14 +40,12 @@
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class Profiler {
 	var $mStack = array (), $mWorkStack = array (), $mCollated = array ();
 	var $mCalls = array (), $mTotals = array ();
 
-	function Profiler()
-	{
+	function __construct() {
 		// Push an entry for the pre-profile setup time onto the stack
 		global $wgRequestTime;
 		if ( !empty( $wgRequestTime ) ) {
@@ -291,7 +287,7 @@
 	 * @return Integer
 	 * @private
 	 */
-	function calltreeCount(& $stack, $start) {
+	function calltreeCount($stack, $start) {
 		$level = $stack[$start][1];
 		$count = 0;
 		for ($i = $start -1; $i >= 0 && $stack[$i][1] > $level; $i --) {
@@ -308,7 +304,7 @@
 		global $wguname, $wgProfilePerHost;
 
 		$fname = 'Profiler::logToDB';
-		$dbw = & wfGetDB(DB_MASTER);
+		$dbw = wfGetDB(DB_MASTER);
 		if (!is_object($dbw))
 			return false;
 		$errorState = $dbw->ignoreErrors( true );
diff -NaurB -x .svn includes/ProfilerSimple.php /srv/web/fp014/source/includes/ProfilerSimple.php
--- includes/ProfilerSimple.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ProfilerSimple.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  * Simple profiler base class
- * @package MediaWiki
  */
 
 /**
  * @todo document
- * @package MediaWiki
  */
 require_once(dirname(__FILE__).'/Profiler.php');
 
@@ -14,7 +12,7 @@
 	var $mMinimumTime = 0;
 	var $mProfileID = false;
 
-	function ProfilerSimple() {
+	function __construct() {
 		global $wgRequestTime,$wgRUstart;
 		if (!empty($wgRequestTime) && !empty($wgRUstart)) {
 			$this->mWorkStack[] = array( '-total', 0, $wgRequestTime,$this->getCpuTime($wgRUstart));
@@ -26,7 +24,6 @@
 			if (!is_array($entry)) {
 				$entry = array('cpu'=> 0.0, 'cpu_sq' => 0.0, 'real' => 0.0, 'real_sq' => 0.0, 'count' => 0);
 				$this->mCollated["-setup"] =& $entry;
-				
 			}
 			$entry['cpu'] += $elapsedcpu;
 			$entry['cpu_sq'] += $elapsedcpu*$elapsedcpu;
@@ -57,7 +54,7 @@
 		if ($wgDebugFunctionEntry) {
 			$this->debug(str_repeat(' ', count($this->mWorkStack)).'Entering '.$functionname."\n");
 		}
-		$this->mWorkStack[] = array($functionname, count( $this->mWorkStack ), microtime(true), $this->getCpuTime());		
+		$this->mWorkStack[] = array($functionname, count( $this->mWorkStack ), microtime(true), $this->getCpuTime());
 	}
 
 	function profileOut($functionname) {
@@ -87,7 +84,6 @@
 			if (!is_array($entry)) {
 				$entry = array('cpu'=> 0.0, 'cpu_sq' => 0.0, 'real' => 0.0, 'real_sq' => 0.0, 'count' => 0);
 				$this->mCollated[$functionname] =& $entry;
-				
 			}
 			$entry['cpu'] += $elapsedcpu;
 			$entry['cpu_sq'] += $elapsedcpu*$elapsedcpu;
diff -NaurB -x .svn includes/ProfilerSimpleUDP.php /srv/web/fp014/source/includes/ProfilerSimpleUDP.php
--- includes/ProfilerSimpleUDP.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ProfilerSimpleUDP.php	2007-02-01 01:03:18.000000000 +0000
@@ -15,8 +15,7 @@
 			# Less than minimum, ignore
 			return;
 		}
-			
-		
+
 		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
 		$plength=0;
 		$packet="";
diff -NaurB -x .svn includes/ProtectionForm.php /srv/web/fp014/source/includes/ProtectionForm.php
--- includes/ProtectionForm.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ProtectionForm.php	2007-02-01 01:03:18.000000000 +0000
@@ -18,26 +18,39 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class ProtectionForm {
 	var $mRestrictions = array();
 	var $mReason = '';
+	var $mCascade = false;
+	var $mExpiry = null;
 
-	function ProtectionForm( &$article ) {
+	function __construct( &$article ) {
 		global $wgRequest, $wgUser;
 		global $wgRestrictionTypes, $wgRestrictionLevels;
 		$this->mArticle =& $article;
 		$this->mTitle =& $article->mTitle;
 
 		if( $this->mTitle ) {
+			$this->mTitle->loadRestrictions();
+
 			foreach( $wgRestrictionTypes as $action ) {
 				// Fixme: this form currently requires individual selections,
 				// but the db allows multiples separated by commas.
 				$this->mRestrictions[$action] = implode( '', $this->mTitle->getRestrictions( $action ) );
 			}
+
+			$this->mCascade = $this->mTitle->areRestrictionsCascading();
+
+			if ( $this->mTitle->mRestrictionsExpiry == 'infinity' ) {
+				$this->mExpiry = 'infinite';
+			} else if ( strlen($this->mTitle->mRestrictionsExpiry) == 0 ) {
+				$this->mExpiry = '';
+			} else {
+				$this->mExpiry = wfTimestamp( TS_RFC2822, $this->mTitle->mRestrictionsExpiry );
+			}
 		}
 
 		// The form will be available in read-only to show levels.
@@ -48,6 +61,9 @@
 
 		if( $wgRequest->wasPosted() ) {
 			$this->mReason = $wgRequest->getText( 'mwProtect-reason' );
+			$this->mCascade = $wgRequest->getBool( 'mwProtect-cascade' );
+			$this->mExpiry = $wgRequest->getText( 'mwProtect-expiry' );
+
 			foreach( $wgRestrictionTypes as $action ) {
 				$val = $wgRequest->getVal( "mwProtect-level-$action" );
 				if( isset( $val ) && in_array( $val, $wgRestrictionLevels ) ) {
@@ -56,8 +72,20 @@
 			}
 		}
 	}
+	
+	function execute() {
+		global $wgRequest;
+		if( $wgRequest->wasPosted() ) {
+			if( $this->save() ) {
+				global $wgOut;
+				$wgOut->redirect( $this->mTitle->getFullUrl() );
+			}
+		} else {
+			$this->show();
+		}
+	}
 
-	function show() {
+	function show( $err = null ) {
 		global $wgOut;
 
 		$wgOut->setRobotpolicy( 'noindex,nofollow' );
@@ -69,9 +97,23 @@
 			return;
 		}
 
-		if( $this->save() ) {
-			$wgOut->redirect( $this->mTitle->getFullUrl() );
-			return;
+		$cascadeSources = $this->mTitle->getCascadeProtectionSources();
+
+		if ( "" != $err ) {
+			$wgOut->setSubtitle( wfMsgHtml( 'formerror' ) );
+			$wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
+		}
+
+		if ( $cascadeSources && count($cascadeSources) > 0 ) {
+			$titles = '';
+
+			foreach ( $cascadeSources as $title ) {
+				$titles .= '* [[:' . $title->getPrefixedText() . "]]\n";
+			}
+
+			$notice = wfMsg( 'protect-cascadeon' ) . "\r\n$titles";
+
+			$wgOut->addWikiText( $notice );
 		}
 
 		$wgOut->setPageTitle( wfMsg( 'confirmprotect' ) );
@@ -88,20 +130,37 @@
 
 	function save() {
 		global $wgRequest, $wgUser, $wgOut;
-		if( !$wgRequest->wasPosted() ) {
-			return false;
-		}
-
+		
 		if( $this->disabled ) {
+			$this->show();
 			return false;
 		}
 
 		$token = $wgRequest->getVal( 'wpEditToken' );
 		if( !$wgUser->matchEditToken( $token ) ) {
-			throw new FatalError( wfMsg( 'sessionfailure' ) );
+			$this->show( wfMsg( 'sessionfailure' ) );
+			return false;
+		}
+
+		if ( strlen( $this->mExpiry ) == 0 ) {
+			$this->mExpiry = 'infinite';
+		}
+
+		if ( $this->mExpiry == 'infinite' || $this->mExpiry == 'indefinite' ) {
+			$expiry = Block::infinity();
+		} else {
+			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
+			$expiry = strtotime( $this->mExpiry );
+
+			if ( $expiry < 0 || $expiry === false ) {
+				$this->show( wfMsg( 'protect_expiry_invalid' ) );
+				return false;
+			}
+
+			$expiry = wfTimestamp( TS_MW, $expiry );
 		}
 
-		$ok = $this->mArticle->updateRestrictions( $this->mRestrictions, $this->mReason );
+		$ok = $this->mArticle->updateRestrictions( $this->mRestrictions, $this->mReason, $this->mCascade, $expiry );
 		if( !$ok ) {
 			throw new FatalError( "Unknown error at restriction save time." );
 		}
@@ -148,13 +207,25 @@
 		$out .= "</tbody>\n";
 		$out .= "</table>\n";
 
+		$out .= "<table>\n";
+		$out .= "<tbody>\n";
+
+		global $wgEnableCascadingProtection;
+
+		if ($wgEnableCascadingProtection)
+			$out .= $this->buildCascadeInput();
+
+		$out .= $this->buildExpiryInput();
+
 		if( !$this->disabled ) {
-			$out .= "<table>\n";
-			$out .= "<tbody>\n";
 			$out .= "<tr><td>" . $this->buildReasonInput() . "</td></tr>\n";
 			$out .= "<tr><td></td><td>" . $this->buildSubmit() . "</td></tr>\n";
-			$out .= "</tbody>\n";
-			$out .= "</table>\n";
+		}
+
+		$out .= "</tbody>\n";
+		$out .= "</table>\n";
+
+		if ( !$this->disabled ) {
 			$out .= "</form>\n";
 			$out .= $this->buildCleanupScript();
 		}
@@ -205,6 +276,32 @@
 				'id' => $id ) );
 	}
 
+	function buildCascadeInput() {
+		$id = 'mwProtect-cascade';
+		$ci = "<tr>" . wfCheckLabel( wfMsg( 'protect-cascade' ), $id, $id, $this->mCascade, $this->disabledAttrib) . "</tr>";
+
+		return $ci;
+	}
+
+	function buildExpiryInput() {
+		$id = 'mwProtect-expiry';
+
+		$ci = "<tr> <td align=\"right\">";
+		$ci .= wfElement( 'label', array (
+				'id' => "$id-label",
+				'for' => $id ),
+				wfMsg( 'protectexpiry' ) );
+		$ci .= "</td> <td aligh=\"left\">";
+		$ci .= wfElement( 'input', array(
+				'size' => 60,
+				'name' => $id,
+				'id' => $id,
+				'value' => $this->mExpiry ) + $this->disabledAttrib );
+		$ci .= "</td></tr>";
+
+		return $ci;
+	}
+
 	function buildSubmit() {
 		return wfElement( 'input', array(
 			'type' => 'submit',
diff -NaurB -x .svn includes/proxy_check.php /srv/web/fp014/source/includes/proxy_check.php
--- includes/proxy_check.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/proxy_check.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Command line script to check for an open proxy at a specified location
- * @package MediaWiki
  */
 
 if( php_sapi_name() != 'cli' ) {
diff -NaurB -x .svn includes/ProxyTools.php /srv/web/fp014/source/includes/ProxyTools.php
--- includes/ProxyTools.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ProxyTools.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Functions for dealing with proxies
- * @package MediaWiki
  */
 
 function wfGetForwardedFor() {
diff -NaurB -x .svn includes/QueryPage.php /srv/web/fp014/source/includes/QueryPage.php
--- includes/QueryPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/QueryPage.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Contain a class for special pages
- * @package MediaWiki
  */
 
 /**
@@ -50,7 +49,6 @@
  * we factor out some of the functionality into a superclass, and let
  * subclasses derive from it.
  *
- * @package MediaWiki
  */
 class QueryPage {
 	/**
@@ -59,7 +57,7 @@
 	 * @var bool
 	 */
 	var $listoutput = false;
-	
+
 	/**
 	 * The offset and limit in use, as passed to the query() function
 	 *
@@ -197,8 +195,8 @@
 	 */
 	function recache( $limit, $ignoreErrors = true ) {
 		$fname = get_class($this) . '::recache';
-		$dbw =& wfGetDB( DB_MASTER );
-		$dbr =& wfGetDB( DB_SLAVE, array( $this->getName(), 'QueryPage::recache', 'vslow' ) );
+		$dbw = wfGetDB( DB_MASTER );
+		$dbr = wfGetDB( DB_SLAVE, array( $this->getName(), 'QueryPage::recache', 'vslow' ) );
 		if ( !$dbw || !$dbr ) {
 			return false;
 		}
@@ -282,7 +280,7 @@
 
 		$sname = $this->getName();
 		$fname = get_class($this) . '::doQuery';
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$wgOut->setSyndicated( $this->isSyndicated() );
 
@@ -411,7 +409,7 @@
 				$this->feedUrl() );
 			$feed->outHeader();
 
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$sql = $this->getSQL() . $this->getOrder();
 			$sql = $dbr->limitResult( $sql, $limit, 0 );
 			$res = $dbr->query( $sql, 'QueryPage::doFeed' );
@@ -487,7 +485,6 @@
  * titles that match some criteria. It formats each result item as a link to
  * that page.
  *
- * @package MediaWiki
  */
 class PageQueryPage extends QueryPage {
 
diff -NaurB -x .svn includes/RawPage.php /srv/web/fp014/source/includes/RawPage.php
--- includes/RawPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/RawPage.php	2007-02-01 01:03:18.000000000 +0000
@@ -7,12 +7,10 @@
  * License: GPL (http://www.gnu.org/copyleft/gpl.html)
  *
  * @author Gabriel Wicke <wicke@wikidev.net>
- * @package MediaWiki
  */
 
 /**
  * @todo document
- * @package MediaWiki
  */
 class RawPage {
 	var $mArticle, $mTitle, $mRequest;
@@ -20,7 +18,7 @@
 	var $mSmaxage, $mMaxage;
 	var $mContentType, $mExpandTemplates;
 
-	function RawPage( &$article, $request = false ) {
+	function __construct( &$article, $request = false ) {
 		global $wgRequest, $wgInputEncoding, $wgSquidMaxage, $wgJsMimeType;
 		global $wgUser;
 
@@ -39,7 +37,7 @@
 		$maxage = $this->mRequest->getInt( 'maxage', $wgSquidMaxage );
 		$this->mExpandTemplates = $this->mRequest->getVal( 'templates' ) === 'expand';
 		$this->mUseMessageCache = $this->mRequest->getBool( 'usemsgcache' );
-		
+
 		$oldid = $this->mRequest->getInt( 'oldid' );
 		switch ( $wgRequest->getText( 'direction' ) ) {
 			case 'next':
@@ -137,7 +135,13 @@
 		# allow the client to cache this for 24 hours
 		$mode = $this->mPrivateCache ? 'private' : 'public';
 		header( 'Cache-Control: '.$mode.', s-maxage='.$this->mSmaxage.', max-age='.$this->mMaxage );
-		echo $this->getRawText();
+		$text = $this->getRawText();
+
+		if( !wfRunHooks( 'RawPageViewBeforeOutput', array( &$this, &$text ) ) ) {
+			wfDebug( __METHOD__ . ': RawPageViewBeforeOutput hook broke raw page output.' );
+		}
+
+		echo $text;
 		$wgOut->disable();
 	}
 
diff -NaurB -x .svn includes/RecentChange.php /srv/web/fp014/source/includes/RecentChange.php
--- includes/RecentChange.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/RecentChange.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  *
- * @package MediaWiki
  */
 
 /**
@@ -39,7 +38,6 @@
  *      numberofWatchingusers
  *
  * @todo document functions and variables
- * @package MediaWiki
  */
 class RecentChange
 {
@@ -49,7 +47,7 @@
 
 	# Factory methods
 
-	/* static */ function newFromRow( $row )
+	public static function newFromRow( $row )
 	{
 		$rc = new RecentChange;
 		$rc->loadFromRow( $row );
@@ -72,7 +70,7 @@
 	 * @return RecentChange
 	 */
 	public static function newFromId( $rcid ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'recentchanges', '*', array( 'rc_id' => $rcid ), __METHOD__ );
 		if( $res && $dbr->numRows( $res ) > 0 ) {
 			$row = $dbr->fetchObject( $res );
@@ -118,7 +116,7 @@
 		global $wgLocalInterwiki, $wgPutIPinRC, $wgRC2UDPAddress, $wgRC2UDPPort, $wgRC2UDPPrefix;
 		$fname = 'RecentChange::save';
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		if ( !is_array($this->mExtra) ) {
 			$this->mExtra = array();
 		}
@@ -216,7 +214,7 @@
 	{
 		$fname = 'RecentChange::markPatrolled';
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		$dbw->update( 'recentchanges',
 			array( /* SET */
@@ -504,6 +502,8 @@
 	function getIRCLine() {
 		global $wgUseRCPatrol;
 
+		// FIXME: Would be good to replace these 2 extract() calls with something more explicit
+		// e.g. list ($rc_type, $rc_id) = array_values ($this->mAttribs); [or something like that]
 		extract($this->mAttribs);
 		extract($this->mExtra);
 
diff -NaurB -x .svn includes/Revision.php /srv/web/fp014/source/includes/Revision.php
--- includes/Revision.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Revision.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,11 +1,9 @@
 <?php
 /**
- * @package MediaWiki
  * @todo document
  */
 
 /**
- * @package MediaWiki
  * @todo document
  */
 class Revision {
@@ -79,7 +77,7 @@
 	 * @access public
 	 * @static
 	 */
-	public static function loadFromPageId( &$db, $pageid, $id = 0 ) {
+	public static function loadFromPageId( $db, $pageid, $id = 0 ) {
 		$conds=array('page_id=rev_page','rev_page'=>intval( $pageid ), 'page_id'=>intval( $pageid ));
 		if( $id ) {
 			$conds['rev_id']=intval($id);
@@ -145,10 +143,10 @@
 	 * @static
 	 */
 	private static function newFromConds( $conditions ) {
-		$db =& wfGetDB( DB_SLAVE );
+		$db = wfGetDB( DB_SLAVE );
 		$row = Revision::loadFromConds( $db, $conditions );
 		if( is_null( $row ) ) {
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$row = Revision::loadFromConds( $dbw, $conditions );
 		}
 		return $row;
@@ -164,7 +162,7 @@
 	 * @access private
 	 * @static
 	 */
-	private static function loadFromConds( &$db, $conditions ) {
+	private static function loadFromConds( $db, $conditions ) {
 		$res = Revision::fetchFromConds( $db, $conditions );
 		if( $res ) {
 			$row = $res->fetchObject();
@@ -226,7 +224,7 @@
 	 * @access private
 	 * @static
 	 */
-	private static function fetchFromConds( &$db, $conditions ) {
+	private static function fetchFromConds( $db, $conditions ) {
 		$res = $db->select(
 			array( 'page', 'revision' ),
 			array( 'page_namespace',
@@ -332,7 +330,7 @@
 		if( isset( $this->mTitle ) ) {
 			return $this->mTitle;
 		}
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$row = $dbr->selectRow(
 			array( 'page', 'revision' ),
 			array( 'page_namespace', 'page_title' ),
@@ -706,7 +704,7 @@
 		
 		if( !$row ) {
 			// Text data is immutable; check slaves first.
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$row = $dbr->selectRow( 'text',
 				array( 'old_text', 'old_flags' ),
 				array( 'old_id' => $this->getTextId() ),
@@ -715,7 +713,7 @@
 
 		if( !$row ) {
 			// Possible slave lag!
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$row = $dbw->selectRow( 'text',
 				array( 'old_text', 'old_flags' ),
 				array( 'old_id' => $this->getTextId() ),
@@ -802,12 +800,12 @@
 	 * @param integer $id
 	 */
 	static function getTimestampFromID( $id ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$timestamp = $dbr->selectField( 'revision', 'rev_timestamp', 
 			array( 'rev_id' => $id ), __METHOD__ );
 		if ( $timestamp === false ) {
 			# Not in slave, try master
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$timestamp = $dbw->selectField( 'revision', 'rev_timestamp', 
 				array( 'rev_id' => $id ), __METHOD__ );
 		}
diff -NaurB -x .svn includes/Sanitizer.php /srv/web/fp014/source/includes/Sanitizer.php
--- includes/Sanitizer.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Sanitizer.php	2007-02-01 01:04:18.000000000 +0000
@@ -20,8 +20,7 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
- * @subpackage Parser
+ * @addtogroup Parser
  */
 
 /**
@@ -316,7 +315,6 @@
 	'zwj'      => 8205,
 	'zwnj'     => 8204 );
 
-/** @package MediaWiki */
 class Sanitizer {
 	/**
 	 * Cleans up HTML, removes dangerous tags and attributes, and
@@ -330,11 +328,11 @@
 	static function removeHTMLtags( $text, $processCallback = null, $args = array() ) {
 		global $wgUseTidy, $wgUserHtml;
 
-		static $htmlpairs, $htmlsingle, $htmlsingleonly, $htmlnest, $tabletags, 
+		static $htmlpairs, $htmlsingle, $htmlsingleonly, $htmlnest, $tabletags,
 			$htmllist, $listtags, $htmlsingleallowed, $htmlelements, $staticInitialised;
-		
+
 		wfProfileIn( __METHOD__ );
-		
+
 		if ( !$staticInitialised ) {
 			if( $wgUserHtml ) {
 				$htmlpairs = array( # Tags that must be closed
@@ -342,7 +340,7 @@
 					'h2', 'h3', 'h4', 'h5', 'h6', 'cite', 'code', 'em', 's',
 					'strike', 'strong', 'tt', 'var', 'div', 'center',
 					'blockquote', 'ol', 'ul', 'dl', 'table', 'caption', 'pre',
-					'ruby', 'rt' , 'rb' , 'rp', 'p', 'span', 'u'
+					'ruby', 'rt' , 'rb' , 'rp', 'p', 'span', 'u', 'q'
 				);
 				$htmlsingle = array(
 					'br', 'hr', 'li', 'dt', 'dd'
@@ -351,10 +349,10 @@
 					'br', 'hr'
 				);
 				$htmlnest = array( # Tags that can be nested--??
-					'table', 'tr', 'td', 'th', 'div', 'blockquote', 'ol', 'ul',
+					'table', 'tr', 'td', 'th', 'div', 'blockquote', 'ol', 'ul', 'q',
 					'dl', 'font', 'big', 'small', 'sub', 'sup', 'span'
 				);
-				$tabletags = array( # Can only appear inside table
+				$tabletags = array( # Can only appear inside table, we will close them
 					'td', 'th', 'tr',
 				);
 				$htmllist = array( # Tags used by list
@@ -386,7 +384,7 @@
 		# Remove HTML comments
 		$text = Sanitizer::removeHTMLcomments( $text );
 		$bits = explode( '<', $text );
-		$text = array_shift( $bits );
+		$text = str_replace( '>', '&gt;', array_shift( $bits ) );
 		if(!$wgUseTidy) {
 			$tagstack = $tablestack = array();
 			foreach ( $bits as $x ) {
@@ -396,7 +394,7 @@
 				} else {
 					$slash = $t = $params = $brace = $rest = null;
 				}
-				
+
 				$badtag = 0 ;
 				if ( isset( $htmlelements[$t = strtolower( $t )] ) ) {
 					# Check our stack
@@ -453,6 +451,10 @@
 						} else if( isset( $htmlsingle[$t] ) ) {
 							# Hack to not close $htmlsingle tags
 							$brace = NULL;
+						} else if( isset( $tabletags[$t] )
+						&&  in_array($t ,$tagstack) ) {
+							// New table tag but forgot to close the previous one
+							$text .= "</$t>";
 						} else {
 							if ( $t == 'table' ) {
 								array_push( $tablestack, $tagstack );
@@ -472,7 +474,7 @@
 					}
 					if ( ! $badtag ) {
 						$rest = str_replace( '>', '&gt;', $rest );
-						$close = ( $brace == '/>' ) ? ' /' : '';
+						$close = ( $brace == '/>' && !$slash ) ? ' /' : '';
 						$text .= "<$slash$t$newparams$close>$rest";
 						continue;
 					}
@@ -645,15 +647,15 @@
 		if( trim( $text ) == '' ) {
 			return '';
 		}
-		
+
 		$stripped = Sanitizer::validateTagAttributes(
 			Sanitizer::decodeTagAttributes( $text ), $element );
-		
+
 		$attribs = array();
 		foreach( $stripped as $attribute => $value ) {
 			$encAttribute = htmlspecialchars( $attribute );
 			$encValue = Sanitizer::safeEncodeAttribute( $value );
-			
+
 			$attribs[] = "$encAttribute=\"$encValue\"";
 		}
 		return count( $attribs ) ? ' ' . implode( ' ', $attribs ) : '';
@@ -666,7 +668,7 @@
 	 */
 	static function encodeAttribute( $text ) {
 		$encValue = htmlspecialchars( $text );
-		
+
 		// Whitespace is normalized during attribute decoding,
 		// so if we've been passed non-spaces we must encode them
 		// ahead of time or they won't be preserved.
@@ -675,10 +677,10 @@
 			"\r" => '&#13;',
 			"\t" => '&#9;',
 		) );
-		
+
 		return $encValue;
 	}
-	
+
 	/**
 	 * Encode an attribute value for HTML tags, with extra armoring
 	 * against further wiki processing.
@@ -687,7 +689,7 @@
 	 */
 	static function safeEncodeAttribute( $text ) {
 		$encValue = Sanitizer::encodeAttribute( $text );
-		
+
 		# Templates and links may be expanded in later parsing,
 		# creating invalid or dangerous output. Suppress this.
 		$encValue = strtr( $encValue, array(
@@ -743,7 +745,7 @@
 	 * Given a value, escape it so that it can be used as a CSS class and
 	 * return it.
 	 *
-	 * TODO: For extra validity, input should be validated UTF-8.
+	 * @todo For extra validity, input should be validated UTF-8.
 	 *
 	 * @link http://www.w3.org/TR/CSS21/syndata.html Valid characters/format
 	 *
@@ -795,11 +797,11 @@
 		foreach( $pairs as $set ) {
 			$attribute = strtolower( $set[1] );
 			$value = Sanitizer::getTagAttributeCallback( $set );
-			
+
 			// Normalize whitespace
 			$value = preg_replace( '/[\t\r\n ]+/', ' ', $value );
 			$value = trim( $value );
-			
+
 			// Decode character references
 			$attribs[$attribute] = Sanitizer::decodeCharReferences( $value );
 		}
@@ -1087,7 +1089,7 @@
 
 			# 9.2.2
 			'blockquote' => array_merge( $common, array( 'cite' ) ),
-			# q
+			'q' => $common,
 
 			# 9.2.3
 			'sub'        => $common,
@@ -1215,7 +1217,7 @@
 		$out .= "]>\n";
 		return $out;
 	}
-	
+
 	static function cleanUrl( $url, $hostname=true ) {
 		# Normalize any HTML entities in input. They will be
 		# re-escaped by makeExternalLink().
@@ -1223,12 +1225,12 @@
 
 		# Escape any control characters introduced by the above step
 		$url = preg_replace( '/[\][<>"\\x00-\\x20\\x7F]/e', "urlencode('\\0')", $url );
-		
+
 		# Validate hostname portion
 		$matches = array();
 		if( preg_match( '!^([^:]+:)(//[^/]+)?(.*)$!iD', $url, $matches ) ) {
 			list( /* $whole */, $protocol, $host, $rest ) = $matches;
-			
+
 			// Characters that will be ignored in IDNs.
 			// http://tools.ietf.org/html/3454#section-3.1
 			// Strip them before further processing so blacklists and such work.
@@ -1247,11 +1249,11 @@
 				\xe2\x80\x8d| # 200d ZERO WIDTH JOINER
 				[\xef\xb8\x80-\xef\xb8\x8f] # fe00-fe00f VARIATION SELECTOR-1-16
 				/xuD";
-			
+
 			$host = preg_replace( $strip, '', $host );
-			
+
 			// @fixme: validate hostnames here
-			
+
 			return $protocol . $host . $rest;
 		} else {
 			return $url;
diff -NaurB -x .svn includes/SearchEngine.php /srv/web/fp014/source/includes/SearchEngine.php
--- includes/SearchEngine.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchEngine.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  * Contain a class for special pages
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 
 /**
- * @package MediaWiki
  */
 class SearchEngine {
 	var $limit = 10;
@@ -134,7 +132,7 @@
 		return NULL;
 	}
 
-	function legalSearchChars() {
+	public static function legalSearchChars() {
 		return "A-Za-z_'0-9\\x80-\\xFF\\-";
 	}
 
@@ -193,9 +191,8 @@
 	 * active database backend, and return a configured instance.
 	 *
 	 * @return SearchEngine
-	 * @private
 	 */
-	function create() {
+	public static function create() {
 		global $wgDBtype, $wgSearchType;
 		if( $wgSearchType ) {
 			$class = $wgSearchType;
@@ -237,7 +234,6 @@
     }
 }
 
-/** @package MediaWiki */
 class SearchResultSet {
 	/**
 	 * Fetch an array of regular expression fragments for matching
@@ -312,7 +308,6 @@
 	}
 }
 
-/** @package MediaWiki */
 class SearchResult {
 	function SearchResult( $row ) {
 		$this->mTitle = Title::makeTitle( $row->page_namespace, $row->page_title );
@@ -335,7 +330,6 @@
 }
 
 /**
- * @package MediaWiki
  */
 class SearchEngineDummy {
 	function search( $term ) {
diff -NaurB -x .svn includes/SearchMySQL4.php /srv/web/fp014/source/includes/SearchMySQL4.php
--- includes/SearchMySQL4.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchMySQL4.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,20 +19,18 @@
 
 /**
  * Search engine hook for MySQL 4+
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 
 /**
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 class SearchMySQL4 extends SearchMySQL {
 	var $strictMatching = true;
 
 	/** @todo document */
-	function SearchMySQL4( &$db ) {
-		$this->db =& $db;
+	function SearchMySQL4( $db ) {
+		$this->db = $db;
 	}
 
 	/** @todo document */
diff -NaurB -x .svn includes/SearchMySQL.php /srv/web/fp014/source/includes/SearchMySQL.php
--- includes/SearchMySQL.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchMySQL.php	2007-02-01 01:03:18.000000000 +0000
@@ -20,11 +20,9 @@
 /**
  * Search engine hook base class for MySQL.
  * Specific bits for MySQL 3 and 4 variants are in child classes.
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 
-/** @package MediaWiki */
 class SearchMySQL extends SearchEngine {
 	/**
 	 * Perform a full text search query and return a result set.
@@ -150,7 +148,7 @@
 	 * @param string $text
 	 */
 	function update( $id, $title, $text ) {
-		$dbw=& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->replace( 'searchindex',
 			array( 'si_page' ),
 			array(
@@ -168,7 +166,7 @@
 	 * @param string $title
 	 */
     function updateTitle( $id, $title ) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		$dbw->update( 'searchindex',
 			array( 'si_title' => $title ),
@@ -178,7 +176,6 @@
 	}
 }
 
-/** @package MediaWiki */
 class MySQLSearchResultSet extends SearchResultSet {
 	function MySQLSearchResultSet( $resultSet, $terms ) {
 		$this->mResultSet = $resultSet;
diff -NaurB -x .svn includes/SearchPostgres.php /srv/web/fp014/source/includes/SearchPostgres.php
--- includes/SearchPostgres.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchPostgres.php	2007-02-01 01:03:18.000000000 +0000
@@ -21,15 +21,13 @@
 
 /**
  * Search engine hook base class for Postgres
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 
-/** @package MediaWiki */
 class SearchPostgres extends SearchEngine {
 
-	function SearchPostgres( &$db ) {
-		$this->db =& $db;
+	function SearchPostgres( $db ) {
+		$this->db = $db;
 	}
 
 	/**
@@ -124,13 +122,20 @@
 
 	## These two functions are done automatically via triggers
 
-	function update( $id, $title, $text ) { return true; }
+	function update( $pageid, $title, $text ) {
+		$dbw = wfGetDB( DB_MASTER );
+		## We don't want to index older revisions
+		$SQL = "UPDATE pagecontent SET textvector = NULL WHERE old_id = ".
+				"(SELECT rev_text_id FROM revision WHERE rev_page = $pageid ".
+				"ORDER BY rev_text_id DESC LIMIT 1 OFFSET 1)";
+		$dbw->doQuery($SQL);
+		return true;
+	}
     function updateTitle( $id, $title )   { return true; }
 
 } ## end of the SearchPostgres class
 
 
-/** @package MediaWiki */
 class PostgresSearchResultSet extends SearchResultSet {
 	function PostgresSearchResultSet( $resultSet, $terms ) {
 		$this->mResultSet = $resultSet;
diff -NaurB -x .svn includes/SearchTsearch2.php /srv/web/fp014/source/includes/SearchTsearch2.php
--- includes/SearchTsearch2.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchTsearch2.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,14 +19,12 @@
 
 /**
  * Search engine hook for PostgreSQL / Tsearch2
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 
 /**
  * @todo document
- * @package MediaWiki
- * @subpackage Search
+ * @addtogroup Search
  */
 class SearchTsearch2 extends SearchEngine {
 	var $strictMatching = false;
@@ -97,7 +95,7 @@
 	}
 
 	function update( $id, $title, $text ) {
-	        $dbw=& wfGetDB(DB_MASTER);
+		$dbw = wfGetDB(DB_MASTER);
 		$searchindex = $dbw->tableName( 'searchindex' );
 		$sql = "DELETE FROM $searchindex WHERE si_page={$id}";
 		$dbw->query($sql,"SearchTsearch2:update");
@@ -110,13 +108,13 @@
 	}
 
 	function updateTitle($id,$title) {
-	        $dbw=& wfGetDB(DB_MASTER);
-	        $searchindex = $dbw->tableName( 'searchindex' );
-	        $sql = "UPDATE $searchindex SET si_title=to_tsvector('" .
-	                  $dbw->strencode( $title ) .
-	                  "') WHERE si_page={$id}";
+		$dbw = wfGetDB(DB_MASTER);
+		$searchindex = $dbw->tableName( 'searchindex' );
+		$sql = "UPDATE $searchindex SET si_title=to_tsvector('" .
+				$dbw->strencode( $title ) .
+				"') WHERE si_page={$id}";
 
-	        $dbw->query( $sql, "SearchMySQL4::updateTitle" );
+		$dbw->query( $sql, "SearchMySQL4::updateTitle" );
 	}
 
 }
diff -NaurB -x .svn includes/SearchUpdate.php /srv/web/fp014/source/includes/SearchUpdate.php
--- includes/SearchUpdate.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SearchUpdate.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  * See deferred.txt
- * @package MediaWiki
  */
 
 /**
  *
- * @package MediaWiki
  */
 class SearchUpdate {
 
@@ -38,7 +36,7 @@
 		wfProfileIn( $fname );
 
 		$search = SearchEngine::create();
-		$lc = $search->legalSearchChars() . '&#;';
+		$lc = SearchEngine::legalSearchChars() . '&#;';
 
 		if( $this->mText === false ) {
 			$search->updateTitle($this->mId,
@@ -106,7 +104,6 @@
 
 /**
  * Placeholder class
- * @package MediaWiki
  */
 class SearchUpdateMyISAM extends SearchUpdate {
 	# Inherits everything
diff -NaurB -x .svn includes/Setup.php /srv/web/fp014/source/includes/Setup.php
--- includes/Setup.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Setup.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Include most things that's need to customize the site
- * @package MediaWiki
  */
 
 /**
diff -NaurB -x .svn includes/SiteConfiguration.php /srv/web/fp014/source/includes/SiteConfiguration.php
--- includes/SiteConfiguration.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SiteConfiguration.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,7 +2,6 @@
 /**
  * This is a class used to hold configuration settings, particularly for multi-wiki sites.
  *
- * @package MediaWiki
  */
 
 /**
@@ -13,7 +12,6 @@
 if (!defined('SITE_CONFIGURATION')) {
 define('SITE_CONFIGURATION', 1);
 
-/** @package MediaWiki */
 class SiteConfiguration {
 	var $suffixes = array();
 	var $wikis = array();
diff -NaurB -x .svn includes/SiteStats.php /srv/web/fp014/source/includes/SiteStats.php
--- includes/SiteStats.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SiteStats.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,7 +2,6 @@
 
 /**
  * Static accessor class for site_stats and related things
- * @package MediaWiki
  */
 class SiteStats {
 	static $row, $loaded = false;
@@ -18,7 +17,7 @@
 			return;
 		}
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		self::$row = $dbr->selectRow( 'site_stats', '*', false, __METHOD__ );
 
 		# This code is somewhat schema-agnostic, because I'm changing it in a minor release -- TS
@@ -62,7 +61,7 @@
 
 	static function admins() {
 		if ( !isset( self::$admins ) ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			self::$admins = $dbr->selectField( 'user_groups', 'COUNT(*)', array( 'ug_group' => 'sysop' ), __METHOD__ );
 		}
 		return self::$admins;
@@ -71,7 +70,7 @@
 	static function pagesInNs( $ns ) {
 		wfProfileIn( __METHOD__ );
 		if( !isset( self::$pageCount[$ns] ) ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$pageCount[$ns] = (int)$dbr->selectField( 'page', 'COUNT(*)', array( 'page_namespace' => $ns ), __METHOD__ );
 		}
 		wfProfileOut( __METHOD__ );
@@ -83,13 +82,12 @@
 
 /**
  *
- * @package MediaWiki
  */
 class SiteStatsUpdate {
 
 	var $mViews, $mEdits, $mGood, $mPages, $mUsers;
 
-	function SiteStatsUpdate( $views, $edits, $good, $pages = 0, $users = 0 ) {
+	function __construct( $views, $edits, $good, $pages = 0, $users = 0 ) {
 		$this->mViews = $views;
 		$this->mEdits = $edits;
 		$this->mGood = $good;
@@ -112,7 +110,7 @@
 
 	function doUpdate() {
 		$fname = 'SiteStatsUpdate::doUpdate';
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		# First retrieve the row just to find out which schema we're in
 		$row = $dbw->selectRow( 'site_stats', '*', false, $fname );
@@ -126,7 +124,7 @@
 		if ( isset( $row->ss_total_pages ) ) {
 			# Update schema if required
 			if ( $row->ss_total_pages == -1 && !$this->mViews ) {
-				$dbr =& wfGetDB( DB_SLAVE, array( 'SpecialStatistics', 'vslow') );
+				$dbr = wfGetDB( DB_SLAVE, array( 'SpecialStatistics', 'vslow') );
 				list( $page, $user ) = $dbr->tableNamesN( 'page', 'user' );
 
 				$sql = "SELECT COUNT(page_namespace) AS total FROM $page";
diff -NaurB -x .svn includes/Skin.php /srv/web/fp014/source/includes/Skin.php
--- includes/Skin.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Skin.php	2007-02-02 01:07:48.000000000 +0000
@@ -4,8 +4,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage Skins
+ * @addtogroup Skins
  */
 
 # See skin.txt
@@ -13,7 +12,6 @@
 /**
  * The main skin class that provide methods and properties for all other skins.
  * This base class is also the "Standard" skin.
- * @package MediaWiki
  */
 class Skin extends Linker {
 	/**#@+
@@ -27,14 +25,14 @@
 	/**#@-*/
 
 	/** Constructor, call parent constructor */
-	function Skin() { parent::Linker(); }
+	function Skin() { parent::__construct(); }
 
 	/**
 	 * Fetch the set of available skins.
 	 * @return array of strings
 	 * @static
 	 */
-	static function &getSkinNames() {
+	static function getSkinNames() {
 		global $wgValidSkinNames;
 		static $skinsInitialised = false;
 		if ( !$skinsInitialised ) {
@@ -189,16 +187,19 @@
 	function preloadExistence() {
 		global $wgUser, $wgTitle;
 
-		if ( $wgTitle->isTalkPage() ) {
-			$otherTab = $wgTitle->getSubjectPage();
+		// User/talk link
+		$titles = array( $wgUser->getUserPage(), $wgUser->getTalkPage() );
+
+		// Other tab link
+		if ( $wgTitle->getNamespace() == NS_SPECIAL ) {
+			// nothing
+		} elseif ( $wgTitle->isTalkPage() ) {
+			$titles[] = $wgTitle->getSubjectPage();
 		} else {
-			$otherTab = $wgTitle->getTalkPage();
+			$titles[] = $wgTitle->getTalkPage();
 		}
-		$lb = new LinkBatch( array( 
-			$wgUser->getUserPage(),
-			$wgUser->getTalkPage(),
-			$otherTab
-		));
+
+		$lb = new LinkBatch( $titles );
 		$lb->execute();
 	}
 	
@@ -312,6 +313,7 @@
 			'wgArticleId' => $wgTitle->getArticleId(),
 			'wgIsArticle' => $wgOut->isArticle(),
 			'wgUserName' => $wgUser->isAnon() ? NULL : $wgUser->getName(),
+			'wgUserGroups' => $wgUser->isAnon() ? NULL : $wgUser->getEffectiveGroups(),
 			'wgUserLanguage' => $wgLang->getCode(),
 			'wgContentLanguage' => $wgContLang->getCode(),
 			'wgBreakFrames' => $wgBreakFrames,
@@ -392,7 +394,6 @@
 	 * @return string
 	 */
 	function getUserJs() {
-		$fname = 'Skin::getUserJs';
 		wfProfileIn( __METHOD__ );
 
 		global $wgStylePath;
@@ -505,7 +506,7 @@
 		}
 		else $a = array( 'bgcolor' => '#FFFFFF' );
 		if($wgOut->isArticle() && $wgUser->getOption('editondblclick') &&
-		  $wgTitle->userCanEdit() ) {
+		  $wgTitle->userCan( 'edit' ) ) {
 			$s = $wgTitle->getFullURL( $this->editUrlOptions() );
 			$s = 'document.location = "' .wfEscapeJSString( $s ) .'";';
 			$a += array ('ondblclick' => $s);
@@ -519,7 +520,7 @@
 			$a['onload'] .= 'setupRightClickEdit()';
 		}
 		$a['class'] = 'ns-'.$wgTitle->getNamespace().' '.($wgContLang->isRTL() ? "rtl" : "ltr").
-		' '.Sanitizer::escapeId( 'page-'.$wgTitle->getPrefixedText() );
+		' '.Sanitizer::escapeClass( 'page-'.$wgTitle->getPrefixedText() );
 		return $a;
 	}
 
@@ -1041,7 +1042,7 @@
 		}
 
 		if ($wgPageShowWatchingUsers && $wgUser->getOption( 'shownumberswatching' )) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$watchlist = $dbr->tableName( 'watchlist' );
 			$sql = "SELECT COUNT(*) AS n FROM $watchlist
 				WHERE wl_title='" . $dbr->strencode($wgTitle->getDBKey()) .
@@ -1114,6 +1115,13 @@
 		return $img;
 	}
 
+	function getHostedBy() {
+		global $wgStylePath;
+		$url = htmlspecialchars( "http://www.wikicities.com/images/e/e1/Hosted_by_wikicities.png" );
+		$img = '<a href="http://www.wikicities.com/"><img src="'.$url.'" alt="MediaWiki" /></a>';
+		return $img;
+	}
+
 	function lastModified() {
 		global $wgLang, $wgArticle, $wgLoadBalancer;
 
@@ -1194,6 +1202,20 @@
 		}
 	}
 
+	function deliciousLink() {
+		global $wgDelicious, $wgGraphicalDelicious;
+
+		$delicious = ($wgGraphicalDelicious) ? '<img src="http://images.wikia.com/common/OPmydel.gif">' : wfMsgForContent('deliciouslink');
+		return ( $wgDelicious ) ? "<a href=\"http://del.icio.us/post\" onclick=\"window.open('http://del.icio.us/post?v=4&amp;noui&amp;jump=close&amp;url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title), 'delicious', 'toolbar=no,width=700,height=400'); return false;\">".$delicious."</a>" : '';
+	}
+
+	function diggsLink() {
+		global $wgDigg, $wgGraphicalDigg;
+
+		$digg = ($wgGraphicalDigg) ? '<script type="text/javascript">document.write(\'<\'+\'img src="http://images.wikia.com/common/91x17-digg-button.png?js=1" width="91" height="17" alt="Digg!" border="0"\'+\' />\');</script><noscript><img src="http://images.wikia.com/common/91x17-digg-button.png?js=0" width="91" height="17" alt="Digg!" /></noscript>' : wfMsgForContent('digglink');
+		return ( $wgDigg ) ? "<a href=\"http://digg.com/submit\"  onclick=\"window.open('http://digg.com/submit?phase=2&amp;url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title)); return false;\">".$digg."</a>" : '';
+	}
+
 	function aboutLink() {
 		$s = $this->makeKnownLink( wfMsgForContent( 'aboutpage' ),
 		  wfMsg( 'aboutsite' ) );
@@ -1216,7 +1238,7 @@
 		if ( ! $wgOut->isArticleRelated() ) {
 			$s = wfMsg( 'protectedpage' );
 		} else {
-			if ( $wgTitle->userCanEdit() ) {
+			if ( $wgTitle->userCan( 'edit' ) ) {
 				$t = wfMsg( 'editthispage' );
 			} else {
 				$t = wfMsg( 'viewsource' );
@@ -1301,7 +1323,7 @@
 	function moveThisPage() {
 		global $wgTitle;
 
-		if ( $wgTitle->userCanMove() ) {
+		if ( $wgTitle->userCan( 'move' ) ) {
 			return $this->makeKnownLinkObj( SpecialPage::getTitleFor( 'Movepage' ),
 			  wfMsg( 'movethispage' ), 'target=' . $wgTitle->getPrefixedURL() );
 		} else {
@@ -1503,7 +1525,7 @@
 	/* these are used extensively in SkinTemplate, but also some other places */
 	static function makeMainPageUrl( $urlaction = '' ) {
 		$title = Title::newMainPage();
-		self::checkTitle( $title, $name );
+		self::checkTitle( $title, '' );
 		return $title->getLocalURL( $urlaction );
 	}
 
@@ -1569,7 +1591,7 @@
 	}
 
 	# make sure we have some title to operate on
-	static function checkTitle( &$title, &$name ) {
+	static function checkTitle( &$title, $name ) {
 		if( !is_object( $title ) ) {
 			$title = Title::newFromText( $name );
 			if( !is_object( $title ) ) {
diff -NaurB -x .svn includes/SkinTemplate.php /srv/web/fp014/source/includes/SkinTemplate.php
--- includes/SkinTemplate.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SkinTemplate.php	2007-02-02 01:07:06.000000000 +0000
@@ -27,8 +27,7 @@
  * to the computations individual esi snippets need. Most importantly no body
  * parsing for most of those of course.
  *
- * @package MediaWiki
- * @subpackage Skins
+ * @addtogroup Skins
  */
 
 /**
@@ -36,7 +35,6 @@
  * to be passed to the template engine.
  *
  * @private
- * @package MediaWiki
  */
 class MediaWiki_I18N {
 	var $_context = array();
@@ -69,7 +67,6 @@
 
 /**
  *
- * @package MediaWiki
  */
 class SkinTemplate extends Skin {
 	/**#@+
@@ -193,9 +190,9 @@
 		$tpl->set( 'title', $wgOut->getPageTitle() );
 		$tpl->set( 'pagetitle', $wgOut->getHTMLTitle() );
 		$tpl->set( 'displaytitle', $wgOut->mPageLinkTitle );
-		$tpl->set( 'pageclass', Sanitizer::escapeClass( 'page-'.$wgTitle->getPrefixedText() ) );
+		$tpl->set( 'pageclass', Sanitizer::escapeClass( 'page-'.$this->mTitle->getPrefixedText() ) );
 
-		$nsname = isset( $wgCanonicalNamespaceNames[ $this->mTitle->getNamespace() ] ) ? 
+		$nsname = isset( $wgCanonicalNamespaceNames[ $this->mTitle->getNamespace() ] ) ?
 		          $wgCanonicalNamespaceNames[ $this->mTitle->getNamespace() ] :
 		          $this->mTitle->getNsText();
 
@@ -339,8 +336,8 @@
 		$tpl->setRef( 'newtalk', $ntl );
 		$tpl->setRef( 'skin', $this);
 		$tpl->set( 'logo', $this->logoText() );
-		if ( $wgOut->isArticle() and (!isset( $oldid ) or isset( $diff )) and 
-			$wgArticle and 0 != $wgArticle->getID() ) 
+		if ( $wgOut->isArticle() and (!isset( $oldid ) or isset( $diff )) and
+			$wgArticle and 0 != $wgArticle->getID() )
 		{
 			if ( !$wgDisableCounters ) {
 				$viewcount = $wgLang->formatNum( $wgArticle->getCount() );
@@ -354,7 +351,7 @@
 			}
 
 			if ($wgPageShowWatchingUsers) {
-				$dbr =& wfGetDB( DB_SLAVE );
+				$dbr = wfGetDB( DB_SLAVE );
 				$watchlist = $dbr->tableName( 'watchlist' );
 				$sql = "SELECT COUNT(*) AS n FROM $watchlist
 					WHERE wl_title='" . $dbr->strencode($this->mTitle->getDBKey()) .
@@ -406,6 +403,10 @@
 		$tpl->set( 'privacy', $this->privacyLink() );
 		$tpl->set( 'about', $this->aboutLink() );
 
+		$tpl->set( 'hostedbyico', $this->getHostedBy() );
+		$tpl->set( 'diggs', $this->diggsLink() );
+		$tpl->set( 'delicious', $this->deliciousLink() );
+
 		$tpl->setRef( 'debug', $out->mDebugtext );
 		$tpl->set( 'reporttime', $out->reportTime() );
 		$tpl->set( 'sitenotice', wfGetSiteNotice() );
@@ -458,6 +459,11 @@
 		$tpl->set( 'sidebar', $this->buildSidebar() );
 		$tpl->set( 'nav_urls', $this->buildNavUrls() );
 
+		// original version by hansm
+		if( !wfRunHooks( 'SkinTemplateOutputPageBeforeExec', array( &$this, &$tpl ) ) ) {
+			wfDebug( __METHOD__ . ': Hook SkinTemplateOutputPageBeforeExec broke outputPage execution!' );
+		}
+
 		// execute template
 		wfProfileIn( "$fname-execute" );
 		$res = $tpl->execute();
@@ -476,7 +482,7 @@
 	 * @param mixed $str
 	 * @private
 	 */
-	function printOrError( &$str ) {
+	function printOrError( $str ) {
 		echo $str;
 	}
 
@@ -526,7 +532,7 @@
 				'href' => $href,
 				// FIXME #  'active' was disabed in r11346 with message: "disable bold link to my contributions; link was bold on all
 				// Special:Contributions, not just current user's (fix me please!)". Until resolved, explicitly setting active to false.
-				'active' => false # ( ( $href == $pageurl . '/' . $this->username ) 
+				'active' => false # ( ( $href == $pageurl . '/' . $this->username )
 			);
 			$personal_urls['logout'] = array(
 				'text' => wfMsg( 'userlogout' ),
@@ -555,7 +561,7 @@
 				$personal_urls['anonlogin'] = array(
 					'text' => wfMsg('userlogin'),
 					'href' => self::makeSpecialUrl( 'Userlogin', 'returnto=' . $this->thisurl ),
-					'active' => $wgTitle->isSpecial( 'Userlogin' ) 
+					'active' => $wgTitle->isSpecial( 'Userlogin' )
 				);
 			} else {
 
@@ -567,7 +573,7 @@
 			}
 		}
 
-		wfRunHooks( 'PersonalUrls', array( &$personal_urls, &$wgTitle ) );		
+		wfRunHooks( 'PersonalUrls', array( &$personal_urls, &$wgTitle ) );
 		wfProfileOut( $fname );
 		return $personal_urls;
 	}
@@ -659,7 +665,7 @@
 				true);
 
 			wfProfileIn( "$fname-edit" );
-			if ( $this->mTitle->userCanEdit() && ( $this->mTitle->exists() || $this->mTitle->userCanCreate() ) ) {
+			if ( $this->mTitle->quickUserCan( 'edit' ) && ( $this->mTitle->exists() || $this->mTitle->quickUserCan( 'create' ) ) ) {
 				$istalk = $this->mTitle->isTalkPage();
 				$istalkclass = $istalk?' istalk':'';
 				$content_actions['edit'] = array(
@@ -716,7 +722,7 @@
 						'href' => $this->mTitle->getLocalUrl( 'action=delete' )
 					);
 				}
-				if ( $this->mTitle->userCanMove()) {
+				if ( $this->mTitle->quickUserCan( 'move' ) ) {
 					$moveTitle = SpecialPage::getTitleFor( 'Movepage', $this->thispage );
 					$content_actions['move'] = array(
 						'class' => $this->mTitle->isSpecial( 'Movepage' ) ? 'selected' : false,
@@ -762,7 +768,7 @@
 
 			$content_actions[$this->mTitle->getNamespaceKey()] = array(
 				'class' => 'selected',
-				'text' => wfMsg('specialpage'),
+				'text' => wfMsg('nstab-special'),
 				'href' => $wgRequest->getRequestURL(), // @bug 2457, 2510
 			);
 
@@ -832,7 +838,7 @@
 
 		// default permalink to being off, will override it as required below.
 		$nav_urls['permalink'] = false;
-		
+
 		// A print stylesheet is attached to all pages, but nobody ever
 		// figures that out. :)  Add a link...
 		if( $this->iscontent && ($action == '' || $action == 'view' || $action == 'purge' ) ) {
@@ -894,7 +900,7 @@
 			if ( $wgUser->isAllowed( 'block' ) ) {
 				$nav_urls['blockip'] = array(
 					'href' => self::makeSpecialUrlSubpage( 'Blockip', $this->mTitle->getText() )
-				); 
+				);
 			} else {
 				$nav_urls['blockip'] = false;
 			}
@@ -1010,7 +1016,7 @@
 		wfProfileIn( $fname );
 		$out = false;
 		wfRunHooks( 'SkinTemplateSetupPageCss', array( &$out ) );
-		
+
 		wfProfileOut( $fname );
 		return $out;
 	}
@@ -1065,8 +1071,7 @@
 /**
  * Generic wrapper for template functions, with interface
  * compatible with what we use of PHPTAL 0.7.
- * @package MediaWiki
- * @subpackage Skins
+ * @addtogroup Skins
  */
 class QuickTemplate {
 	/**
diff -NaurB -x .svn includes/SpecialAllmessages.php /srv/web/fp014/source/includes/SpecialAllmessages.php
--- includes/SpecialAllmessages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialAllmessages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  * Use this special page to get a list of the MediaWiki system messages.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -89,7 +88,7 @@
 	global $wgLang, $wgContLang, $wgUser;
 	wfProfileIn( __METHOD__ );
 
-	$sk =& $wgUser->getSkin();
+	$sk = $wgUser->getSkin();
 	$talk = $wgLang->getNsText( NS_TALK );
 
 	$input = wfElement( 'input', array(
@@ -124,7 +123,7 @@
 		NS_MEDIAWIKI => array(),
 		NS_MEDIAWIKI_TALK => array()
 	);
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	$page = $dbr->tableName( 'page' );
 	$sql = "SELECT page_namespace,page_title FROM $page WHERE page_namespace IN (" . NS_MEDIAWIKI . ", " . NS_MEDIAWIKI_TALK . ")";
 	$res = $dbr->query( $sql );
diff -NaurB -x .svn includes/SpecialAllpages.php /srv/web/fp014/source/includes/SpecialAllpages.php
--- includes/SpecialAllpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialAllpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -89,7 +88,7 @@
 	# TODO: Either make this *much* faster or cache the title index points
 	# in the querycache table.
 
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	$out = "";
 	$where = array( 'page_namespace' => $namespace );
 
@@ -217,7 +216,7 @@
 	} else {
 		list( $namespace, $fromKey, $from ) = $fromList;
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'page',
 			array( 'page_namespace', 'page_title', 'page_is_redirect' ),
 			array(
@@ -263,7 +262,7 @@
 	} else {
 
 		# Get the last title from previous chunk
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res_prev = $dbr->select(
 			'page',
 			'page_title',
diff -NaurB -x .svn includes/SpecialAncientpages.php /srv/web/fp014/source/includes/SpecialAncientpages.php
--- includes/SpecialAncientpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialAncientpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class AncientPagesPage extends QueryPage {
 
@@ -24,7 +22,7 @@
 
 	function getSQL() {
 		global $wgDBtype;
-		$db =& wfGetDB( DB_SLAVE );
+		$db = wfGetDB( DB_SLAVE );
 		$page = $db->tableName( 'page' );
 		$revision = $db->tableName( 'revision' );
 		#$use_index = $db->useIndexClause( 'cur_timestamp' ); # FIXME! this is gone
diff -NaurB -x .svn includes/SpecialBlockip.php /srv/web/fp014/source/includes/SpecialBlockip.php
--- includes/SpecialBlockip.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialBlockip.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * Constructor for Special:Blockip page
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -12,6 +11,13 @@
 function wfSpecialBlockip( $par ) {
 	global $wgUser, $wgOut, $wgRequest;
 
+	# Can't block when the database is locked
+	if( wfReadOnly() ) {
+		$wgOut->readOnlyPage();
+		return;
+	}
+
+	# Permission check
 	if( !$wgUser->isAllowed( 'block' ) ) {
 		$wgOut->permissionRequired( 'block' );
 		return;
@@ -33,8 +39,7 @@
 /**
  * Form object
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class IPBlockForm {
 	var $BlockAddress, $BlockExpiry, $BlockReason;
@@ -43,11 +48,12 @@
 		global $wgRequest;
 
 		$this->BlockAddress = $wgRequest->getVal( 'wpBlockAddress', $wgRequest->getVal( 'ip', $par ) );
+		$this->BlockAddress = strtr( $this->BlockAddress, '_', ' ' );
 		$this->BlockReason = $wgRequest->getText( 'wpBlockReason' );
 		$this->BlockExpiry = $wgRequest->getVal( 'wpBlockExpiry', wfMsg('ipbotheroption') );
 		$this->BlockOther = $wgRequest->getVal( 'wpBlockOther', '' );
 
-		# Unchecked checkboxes are not included in the form data at all, so having one 
+		# Unchecked checkboxes are not included in the form data at all, so having one
 		# that is true by default is a bit tricky
 		$byDefault = !$wgRequest->wasPosted();
 		$this->BlockAnonOnly = $wgRequest->getBool( 'wpAnonOnly', $byDefault );
@@ -62,15 +68,14 @@
 		$wgOut->addWikiText( wfMsg( 'blockiptext' ) );
 
 		if($wgSysopUserBans) {
-			$mIpaddress = wfMsgHtml( 'ipadressorusername' );
+			$mIpaddress = Xml::label( wfMsg( 'ipadressorusername' ), 'mw-bi-target' );
 		} else {
-			$mIpaddress = wfMsgHtml( 'ipaddress' );
+			$mIpaddress = Xml::label( wfMsg( 'ipadress' ), 'mw-bi-target' );
 		}
-		$mIpbexpiry = wfMsgHtml( 'ipbexpiry' );
-		$mIpbother = wfMsgHtml( 'ipbother' );
+		$mIpbexpiry = Xml::label( wfMsg( 'ipbexpiry' ), 'wpBlockExpiry' );
+		$mIpbother = Xml::label( wfMsg( 'ipbother' ), 'mw-bi-other' );
 		$mIpbothertime = wfMsgHtml( 'ipbotheroption' );
-		$mIpbreason = wfMsgHtml( 'ipbreason' );
-		$mIpbsubmit = wfMsgHtml( 'ipbsubmit' );
+		$mIpbreason = Xml::label( wfMsg( 'ipbreason' ), 'mw-bi-reason' );
 		$titleObj = SpecialPage::getTitleFor( 'Blockip' );
 		$action = $titleObj->escapeLocalURL( "action=submit" );
 
@@ -79,10 +84,7 @@
 			$wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
 		}
 
-		$scBlockAddress = htmlspecialchars( $this->BlockAddress );
-		$scBlockReason = htmlspecialchars( $this->BlockReason );
-		$scBlockOtherTime = htmlspecialchars( $this->BlockOther );
-		$scBlockExpiryOptions = htmlspecialchars( wfMsgForContent( 'ipboptions' ) );
+		$scBlockExpiryOptions = wfMsgForContent( 'ipboptions' );
 
 		$showblockoptions = $scBlockExpiryOptions != '-';
 		if (!$showblockoptions)
@@ -108,7 +110,8 @@
 		<tr>
 			<td align=\"right\">{$mIpaddress}:</td>
 			<td align=\"left\">
-				<input tabindex='1' type='text' size='40' name=\"wpBlockAddress\" value=\"{$scBlockAddress}\" />
+				" . Xml::input( 'wpBlockAddress', 40, $this->BlockAddress,
+					array( 'tabindex' => '1', 'id' => 'mw-bi-target' ) ) . "
 			</td>
 		</tr>
 		<tr>");
@@ -127,13 +130,15 @@
 		<tr id='wpBlockOther'>
 			<td align=\"right\">{$mIpbother}:</td>
 			<td align=\"left\">
-				<input tabindex='3' type='text' size='40' name=\"wpBlockOther\" value=\"{$scBlockOtherTime}\" />
+				" . Xml::input( 'wpBlockOther', 40, $this->BlockOther,
+					array( 'tabindex' => '3', 'id' => 'mw-bi-other' ) ) . "
 			</td>
 		</tr>
 		<tr>
 			<td align=\"right\">{$mIpbreason}:</td>
 			<td align=\"left\">
-				<input tabindex='3' type='text' size='40' name=\"wpBlockReason\" value=\"{$scBlockReason}\" />
+				" . Xml::input( 'wpBlockReason', 40, $this->BlockReason,
+					array( 'tabindex' => '3', 'id' => 'mw-bi-reason' ) ) . "
 			</td>
 		</tr>
 		<tr>
@@ -152,23 +157,26 @@
 					array( 'tabindex' => 5 ) ) . "
 			</td>
 		</tr>
-                <tr>
-                        <td>&nbsp;</td>
-                        <td align=\"left\">
-                                " . wfCheckLabel( wfMsg( 'ipbenableautoblock' ),
-                                        'wpEnableAutoblock', 'wpEnableAutoblock', $this->BlockEnableAutoblock,
-                                        array( 'tabindex' => 6 ) ) . "
-                        </td>
-                </tr>
+		<tr>
+			<td>&nbsp;</td>
+			<td align=\"left\">
+				" . wfCheckLabel( wfMsg( 'ipbenableautoblock' ),
+						'wpEnableAutoblock', 'wpEnableAutoblock', $this->BlockEnableAutoblock,
+							array( 'tabindex' => 6 ) ) . "
+			</td>
+		</tr>
 		<tr>
 			<td style='padding-top: 1em'>&nbsp;</td>
 			<td style='padding-top: 1em' align=\"left\">
-				<input tabindex='7' type='submit' name=\"wpBlock\" value=\"{$mIpbsubmit}\" />
+				" . Xml::submitButton( wfMsg( 'ipbsubmit' ),
+							array( 'name' => 'wpBlock', 'tabindex' => '7' ) ) . "
 			</td>
 		</tr>
-	</table>
-	<input type='hidden' name='wpEditToken' value=\"{$token}\" />
-</form>\n" );
+	</table>" .
+	Xml::hidden( 'wpEditToken', $token ) .
+"</form>\n" );
+
+		$wgOut->addHtml( $this->getConvenienceLinks() );
 
 		$user = User::newFromName( $this->BlockAddress );
 		if( is_object( $user ) ) {
@@ -176,7 +184,6 @@
 		} elseif( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $this->BlockAddress ) ) {
 			$this->showLogFragment( $wgOut, Title::makeTitle( NS_USER, $this->BlockAddress ) );
 		}
-	
 	}
 
 	function doSubmit() {
@@ -247,23 +254,28 @@
 		# Note: for a user block, ipb_address is only for display purposes
 
 		$block = new Block( $this->BlockAddress, $userId, $wgUser->getID(),
-			$this->BlockReason, wfTimestampNow(), 0, $expiry, $this->BlockAnonOnly, 
+			$this->BlockReason, wfTimestampNow(), 0, $expiry, $this->BlockAnonOnly,
 			$this->BlockCreateAccount, $this->BlockEnableAutoblock );
 
 		if (wfRunHooks('BlockIp', array(&$block, &$wgUser))) {
 
 			if ( !$block->insert() ) {
-				$this->showForm( wfMsg( 'ipb_already_blocked', 
+				$this->showForm( wfMsg( 'ipb_already_blocked',
 					htmlspecialchars( $this->BlockAddress ) ) );
 				return;
 			}
 
 			wfRunHooks('BlockIpComplete', array($block, $wgUser));
 
+			# Prepare log parameters
+			$logParams = array();
+			$logParams[] = $expirestr;
+			$logParams[] = $this->blockLogFlags();
+
 			# Make log entry
 			$log = new LogPage( 'block' );
 			$log->addEntry( 'block', Title::makeTitle( NS_USER, $this->BlockAddress ),
-			  $this->BlockReason, $expirestr );
+			  $this->BlockReason, $logParams );
 
 			# Report to the user
 			$titleObj = SpecialPage::getTitleFor( 'Blockip' );
@@ -280,14 +292,78 @@
 		$text = wfMsg( 'blockipsuccesstext', $this->BlockAddress );
 		$wgOut->addWikiText( $text );
 	}
-	
+
 	function showLogFragment( $out, $title ) {
 		$out->addHtml( wfElement( 'h2', NULL, LogPage::logName( 'block' ) ) );
 		$request = new FauxRequest( array( 'page' => $title->getPrefixedText(), 'type' => 'block' ) );
 		$viewer = new LogViewer( new LogReader( $request ) );
 		$viewer->showList( $out );
 	}
-	
-}
 
+	/**
+	 * Return a comma-delimited list of "flags" to be passed to the log
+	 * reader for this block, to provide more information in the logs
+	 *
+	 * @return array
+	 */
+	private function blockLogFlags() {
+		$flags = array();
+		if( $this->BlockAnonOnly )
+			$flags[] = 'anononly';
+		if( $this->BlockCreateAccount )
+			$flags[] = 'nocreate';
+		if( $this->BlockEnableAutoblock )
+			$flags[] = 'autoblock';
+		return implode( ',', $flags );
+	}
+
+	/**
+	 * Builds unblock and block list links
+	 *
+	 * @return string
+	 */
+	private function getConvenienceLinks() {
+		global $wgUser;
+		$skin = $wgUser->getSkin();
+		$links[] = $this->getUnblockLink( $skin );
+		$links[] = $this->getBlockListLink( $skin );
+		return '<p class="mw-ipb-conveniencelinks">' . implode( ' | ', $links ) . '</p>';
+	}
+
+	/**
+	 * Build a convenient link to unblock the given username or IP
+	 * address, if available; otherwise link to a blank unblock
+	 * form
+	 *
+	 * @param $skin Skin to use
+	 * @return string
+	 */
+	private function getUnblockLink( $skin ) {
+		$list = SpecialPage::getTitleFor( 'Ipblocklist' );
+		if( $this->BlockAddress ) {
+			$addr = htmlspecialchars( strtr( $this->BlockAddress, '_', ' ' ) );
+			return $skin->makeKnownLinkObj( $list, wfMsgHtml( 'ipb-unblock-addr', $addr ),
+				'action=unblock&ip=' . urlencode( $this->BlockAddress ) );
+		} else {
+			return $skin->makeKnownLinkObj( $list, wfMsgHtml( 'ipb-unblock' ),	'action=unblock' );
+		}
+	}
+
+	/**
+	 * Build a convenience link to the block list
+	 *
+	 * @param $skin Skin to use
+	 * @return string
+	 */
+	private function getBlockListLink( $skin ) {
+		$list = SpecialPage::getTitleFor( 'Ipblocklist' );
+		if( $this->BlockAddress ) {
+			$addr = htmlspecialchars( strtr( $this->BlockAddress, '_', ' ' ) );
+			return $skin->makeKnownLinkObj( $list, wfMsgHtml( 'ipb-blocklist-addr', $addr ),
+				'ip=' . urlencode( $this->BlockAddress ) );
+		} else {
+			return $skin->makeKnownLinkObj( $list, wfMsgHtml( 'ipb-blocklist' ) );
+		}
+	}
+}
 ?>
diff -NaurB -x .svn includes/SpecialBlockme.php /srv/web/fp014/source/includes/SpecialBlockme.php
--- includes/SpecialBlockme.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialBlockme.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
diff -NaurB -x .svn includes/SpecialBooksources.php /srv/web/fp014/source/includes/SpecialBooksources.php
--- includes/SpecialBooksources.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialBooksources.php	2007-02-01 01:03:18.000000000 +0000
@@ -4,8 +4,7 @@
  * Special page outputs information on sourcing a book with a particular ISBN
  * The parser creates links to this page when dealing with ISBNs in wikitext
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  * @author Rob Church <robchur@gmail.com>
  * @todo Validate ISBNs using the standard check-digit method
  */
@@ -34,7 +33,7 @@
 		$this->isbn = $this->cleanIsbn( $isbn ? $isbn : $wgRequest->getText( 'isbn' ) );
 		$wgOut->addWikiText( wfMsgNoTrans( 'booksources-summary' ) );
 		$wgOut->addHtml( $this->makeForm() );
-		if( strlen( $this->isbn) > 0 )
+		if( strlen( $this->isbn ) > 0 )
 			$this->showList();
 	}
 	
@@ -75,6 +74,10 @@
 	private function showList() {
 		global $wgOut, $wgContLang;
 		
+		# Hook to allow extensions to insert additional HTML,
+		# e.g. for API-interacting plugins and so on
+		wfRunHooks( 'BookInformation', array( $this->isbn, &$wgOut ) );
+		
 		# Check for a local page such as Project:Book_sources and use that if available
 		$title = Title::makeTitleSafe( NS_PROJECT, wfMsg( 'booksources' ) ); # Should this be wfMsgForContent()? -- RC
 		if( is_object( $title ) && $title->exists() ) {
diff -NaurB -x .svn includes/SpecialBrokenRedirects.php /srv/web/fp014/source/includes/SpecialBrokenRedirects.php
--- includes/SpecialBrokenRedirects.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialBrokenRedirects.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class BrokenRedirectsPage extends PageQueryPage {
 	var $targets = array();
@@ -26,7 +24,7 @@
 	}
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $pagelinks ) = $dbr->tableNamesN( 'page', 'pagelinks' );
 
 		$sql = "SELECT 'BrokenRedirects'  AS type,
@@ -46,7 +44,7 @@
 	}
 
 	function formatResult( $skin, $result ) {
-		global $wgContLang;
+		global $wgUser, $wgContLang;
 		
 		$fromObj = Title::makeTitle( $result->namespace, $result->title );
 		if ( isset( $result->pl_title ) ) {
@@ -66,11 +64,19 @@
 		}
 
 		$from = $skin->makeKnownLinkObj( $fromObj ,'', 'redirect=no' );
-		$edit = $skin->makeBrokenLinkObj( $fromObj , "(".wfMsg("qbedit").")" , 'redirect=no');
+		$edit = $skin->makeKnownLinkObj( $fromObj, wfMsgHtml( 'brokenredirects-edit' ), 'action=edit' );
 		$to   = $skin->makeBrokenLinkObj( $toObj );
 		$arr = $wgContLang->getArrow();
-
-		return "$from $edit $arr $to";
+		
+		$out = "{$from} {$edit}";
+		
+		if( $wgUser->isAllowed( 'delete' ) ) {
+			$delete = $skin->makeKnownLinkObj( $fromObj, wfMsgHtml( 'brokenredirects-delete' ), 'action=delete' );
+			$out .= " {$delete}";
+		}
+		
+		$out .= " {$arr} {$to}";
+		return $out;
 	}
 }
 
diff -NaurB -x .svn includes/SpecialCategories.php /srv/web/fp014/source/includes/SpecialCategories.php
--- includes/SpecialCategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialCategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class CategoriesPage extends QueryPage {
 
@@ -28,7 +26,7 @@
 	
 	function getSQL() {
 		$NScat = NS_CATEGORY;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$categorylinks = $dbr->tableName( 'categorylinks' );
 		$implicit_groupby = $dbr->implicitGroupby() ? '1' : 'cl_to';
 		$s= "SELECT 'Categories' as type,
diff -NaurB -x .svn includes/SpecialConfirmemail.php /srv/web/fp014/source/includes/SpecialConfirmemail.php
--- includes/SpecialConfirmemail.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialConfirmemail.php	2007-02-01 01:04:18.000000000 +0000
@@ -4,8 +4,7 @@
  * Special page allows users to request email confirmation message, and handles
  * processing of the confirmation code when the link in the email is followed
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  * @author Rob Church <robchur@gmail.com>
  */
  
@@ -94,6 +93,11 @@
 					$title = SpecialPage::getTitleFor( 'Userlogin' );
 					$wgOut->returnToMain( true, $title->getPrefixedText() );
 				}
+
+				/** Added by inez@wikia.com
+				 *  Used in PrivateDomains extension
+				 */
+				wfRunHooks( 'ConfirmEmailComplete', array(&$user) );
 			} else {
 				$wgOut->addWikiText( wfMsg( 'confirmemail_error' ) );
 			}
diff -NaurB -x .svn includes/SpecialContributions.php /srv/web/fp014/source/includes/SpecialContributions.php
--- includes/SpecialContributions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialContributions.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,10 +1,8 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
-/** @package MediaWiki */
 class ContribsFinder {
 	var $username, $offset, $limit, $namespace;
 	var $dbr;
@@ -16,7 +14,7 @@
 	function ContribsFinder( $username ) {
 		$this->username = $username;
 		$this->namespace = false;
-		$this->dbr =& wfGetDB( DB_SLAVE );
+		$this->dbr = wfGetDB( DB_SLAVE, 'contributions' );
 	}
 
 	function setNamespace( $ns ) {
@@ -158,13 +156,13 @@
 
 		$nscond = $this->getNamespaceCond();
 		$use_index = $this->dbr->useIndexClause( $index );
-		$sql = "SELECT
-			page_namespace,page_title,page_is_new,page_latest,
-			rev_id,rev_page,rev_text_id,rev_timestamp,rev_comment,rev_minor_edit,rev_user,rev_user_text,
-			rev_deleted
-			FROM $page,$revision $use_index
-			WHERE page_id=rev_page AND $userCond $nscond $offsetQuery
-		 	ORDER BY rev_timestamp DESC";
+		$sql = 'SELECT ' .
+			'page_namespace,page_title,page_is_new,page_latest,'.
+			'rev_id,rev_page,rev_text_id,rev_timestamp,rev_comment,rev_minor_edit,rev_user,rev_user_text,'.
+			'rev_deleted ' .
+			"FROM $page,$revision $use_index " .
+			"WHERE page_id=rev_page AND $userCond $nscond $offsetQuery " .
+		 	'ORDER BY rev_timestamp DESC';
 		$sql = $this->dbr->limitResult( $sql, $this->limit, 0 );
 		return $sql;
 	}
diff -NaurB -x .svn includes/SpecialDeadendpages.php /srv/web/fp014/source/includes/SpecialDeadendpages.php
--- includes/SpecialDeadendpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialDeadendpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class DeadendPagesPage extends PageQueryPage {
 
@@ -42,7 +40,7 @@
 	 * @return string an sqlquery
 	 */
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $pagelinks ) = $dbr->tableNamesN( 'page', 'pagelinks' );
 		return "SELECT 'Deadendpages' as type, page_namespace AS namespace, page_title as title, page_title AS value " .
 	"FROM $page LEFT JOIN $pagelinks ON page_id = pl_from " .
diff -NaurB -x .svn includes/SpecialDisambiguations.php /srv/web/fp014/source/includes/SpecialDisambiguations.php
--- includes/SpecialDisambiguations.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialDisambiguations.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,15 +1,9 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
-/**
- *
- * @package MediaWiki
- * @subpackage SpecialPage
- */
 class DisambiguationsPage extends PageQueryPage {
 
 	function getName() {
@@ -19,69 +13,67 @@
 	function isExpensive( ) { return true; }
 	function isSyndicated() { return false; }
 
-    function getDisambiguationPageObj() {
-        return Title::makeTitleSafe( NS_MEDIAWIKI, 'disambiguationspage');
-    }
-    
-	function getPageHeader( ) {
-		global $wgUser;
-		$sk = $wgUser->getSkin();
 
-		return '<p>'.wfMsg('disambiguationstext', $sk->makeKnownLinkObj($this->getDisambiguationPageObj()))."</p><br />\n";
+	function getPageHeader( ) {
+		global $wgOut;
+		return $wgOut->parse( wfMsg( 'disambiguations-text' ) );
 	}
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
-		list( $page, $pagelinks, $templatelinks) =  $dbr->tableNamesN( 'page', 'pagelinks', 'templatelinks' );
+		$dbr = wfGetDB( DB_SLAVE );
+
+		$dMsgText = wfMsgForContent('disambiguationspage');
 
-        $dMsgText = wfMsgForContent('disambiguationspage');
-		
 		$linkBatch = new LinkBatch;
-        
-        # If the text can be treated as a title, use it verbatim.
-        # Otherwise, pull the titles from the links table
-        $dp = Title::newFromText($dMsgText);
-        if( $dp ) {
-    		if($dp->getNamespace() != NS_TEMPLATE) {
-    			# FIXME we assume the disambiguation message is a template but
-    			# the page can potentially be from another namespace :/
-    			wfDebug("Mediawiki:disambiguationspage message does not refer to a template!\n");
-    		}
-            $linkBatch->addObj( $dp );
-        } else {
-            # Get all the templates linked from the Mediawiki:Disambiguationspage
-            $disPageObj = $this->getDisambiguationPageObj();
-			$res = $dbr->select(
-				array('pagelinks', 'page'),
-				'pl_title',
-				array('page_id = pl_from', 'pl_namespace' => NS_TEMPLATE,
-                      'page_namespace' => $disPageObj->getNamespace(), 'page_title' => $disPageObj->getDBkey()),
-				'DisambiguationsPage::getSQL' );
-            
-    		while ( $row = $dbr->fetchObject( $res ) ) {
-                $linkBatch->addObj( Title::makeTitle( NS_TEMPLATE, $row->pl_title ));
-            }
-    		$dbr->freeResult( $res );
-        }
-            
-        $set = $linkBatch->constructSet( 'lb.tl', $dbr );
-        if( $set === false ) {
-            $set = 'FALSE';  # We must always return a valid sql query, but this way DB will always quicly return an empty result
-            wfDebug("Mediawiki:disambiguationspage message does not link to any templates!\n");
-        }
-        
-        $sql = "SELECT 'Disambiguations' AS \"type\", pb.page_namespace AS namespace,"
-             ." pb.page_title AS title, la.pl_from AS value"
-             ." FROM {$templatelinks} AS lb, {$page} AS pb, {$pagelinks} AS la, {$page} AS pa"
-             ." WHERE $set"  # disambiguation template(s)
-             .' AND pa.page_id = la.pl_from'
-             .' AND pa.page_namespace = ' . NS_MAIN  # Limit to just articles in the main namespace
-             .' AND pb.page_id = lb.tl_from'
-             .' AND pb.page_namespace = la.pl_namespace'
-             .' AND pb.page_title = la.pl_title'
-			 .' ORDER BY lb.tl_namespace, lb.tl_title';
 
-        return $sql;
+		# If the text can be treated as a title, use it verbatim.
+		# Otherwise, pull the titles from the links table
+		$dp = Title::newFromText($dMsgText);
+		if( $dp ) {
+			if($dp->getNamespace() != NS_TEMPLATE) {
+				# FIXME we assume the disambiguation message is a template but
+				# the page can potentially be from another namespace :/
+				wfDebug("Mediawiki:disambiguationspage message does not refer to a template!\n");
+			}
+			$linkBatch->addObj( $dp );
+		} else {
+				# Get all the templates linked from the Mediawiki:Disambiguationspage
+				$disPageObj = Title::makeTitleSafe( NS_MEDIAWIKI, 'disambiguationspage' );
+				$res = $dbr->select(
+					array('pagelinks', 'page'),
+					'pl_title',
+					array('page_id = pl_from', 'pl_namespace' => NS_TEMPLATE,
+						'page_namespace' => $disPageObj->getNamespace(), 'page_title' => $disPageObj->getDBkey()),
+					__METHOD__ );
+
+				while ( $row = $dbr->fetchObject( $res ) ) {
+					$linkBatch->addObj( Title::makeTitle( NS_TEMPLATE, $row->pl_title ));
+				}
+
+				$dbr->freeResult( $res );
+		}
+
+		$set = $linkBatch->constructSet( 'lb.tl', $dbr );
+		if( $set === false ) {
+			# We must always return a valid sql query, but this way DB will always quicly return an empty result
+			$set = 'FALSE';
+			wfDebug("Mediawiki:disambiguationspage message does not link to any templates!\n");
+		}
+
+		list( $page, $pagelinks, $templatelinks) = $dbr->tableNamesN( 'page', 'pagelinks', 'templatelinks' );
+
+		$sql = "SELECT 'Disambiguations' AS \"type\", pb.page_namespace AS namespace,"
+			." pb.page_title AS title, la.pl_from AS value"
+			." FROM {$templatelinks} AS lb, {$page} AS pb, {$pagelinks} AS la, {$page} AS pa"
+			." WHERE $set"  # disambiguation template(s)
+			.' AND pa.page_id = la.pl_from'
+			.' AND pa.page_namespace = ' . NS_MAIN  # Limit to just articles in the main namespace
+			.' AND pb.page_id = lb.tl_from'
+			.' AND pb.page_namespace = la.pl_namespace'
+			.' AND pb.page_title = la.pl_title'
+			.' ORDER BY lb.tl_namespace, lb.tl_title';
+
+		return $sql;
 	}
 
 	function getOrder() {
@@ -93,10 +85,10 @@
 		$title = Title::newFromId( $result->value );
 		$dp = Title::makeTitle( $result->namespace, $result->title );
 
-		$from = $skin->makeKnownLinkObj( $title,'');
-		$edit = $skin->makeBrokenLinkObj( $title, "(".wfMsg("qbedit").")" , 'redirect=no');
+		$from = $skin->makeKnownLinkObj( $title, '' );
+		$edit = $skin->makeKnownLinkObj( $title, "(".wfMsgHtml("qbedit").")" , 'redirect=no&action=edit' );
 		$arr  = $wgContLang->getArrow();
-		$to   = $skin->makeKnownLinkObj( $dp,'');
+		$to   = $skin->makeKnownLinkObj( $dp, '' );
 
 		return "$from $edit $arr $to";
 	}
@@ -112,4 +104,5 @@
 
 	return $sd->doQuery( $offset, $limit );
 }
-?>
+
+?>
\ No newline at end of file
diff -NaurB -x .svn includes/SpecialDoubleRedirects.php /srv/web/fp014/source/includes/SpecialDoubleRedirects.php
--- includes/SpecialDoubleRedirects.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialDoubleRedirects.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class DoubleRedirectsPage extends PageQueryPage {
 
@@ -53,7 +51,7 @@
 	}
 	
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $this->getSQLText( $dbr );
 	}
 
@@ -68,7 +66,7 @@
 		$titleA = Title::makeTitle( $result->namespace, $result->title );
 
 		if ( $result && !isset( $result->nsb ) ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$sql = $this->getSQLText( $dbr, $result->namespace, $result->title );
 			$res = $dbr->query( $sql, $fname );
 			if ( $res ) {
diff -NaurB -x .svn includes/SpecialEmailuser.php /srv/web/fp014/source/includes/SpecialEmailuser.php
--- includes/SpecialEmailuser.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialEmailuser.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -60,8 +59,7 @@
 
 /**
  * @todo document
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class EmailUserForm {
 
diff -NaurB -x .svn includes/SpecialExport.php /srv/web/fp014/source/includes/SpecialExport.php
--- includes/SpecialExport.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialExport.php	2007-02-01 01:03:18.000000000 +0000
@@ -18,8 +18,7 @@
 # http://www.gnu.org/copyleft/gpl.html
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -87,7 +86,7 @@
 		header( "Content-type: application/xml; charset=utf-8" );
 		$pages = explode( "\n", $page );
 
-		$db =& wfGetDB( DB_SLAVE );
+		$db = wfGetDB( DB_SLAVE );
 		$exporter = new WikiExporter( $db, $history );
 		$exporter->list_authors = $list_authors ;
 		$exporter->openStream();
diff -NaurB -x .svn includes/SpecialImagelist.php /srv/web/fp014/source/includes/SpecialImagelist.php
--- includes/SpecialImagelist.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialImagelist.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -40,7 +39,7 @@
 		if ( $search != '' && !$wgMiserMode ) {
 			$nt = Title::newFromUrl( $search );
 			if( $nt ) {
-				$dbr =& wfGetDB( DB_SLAVE );
+				$dbr = wfGetDB( DB_SLAVE );
 				$m = $dbr->strencode( strtolower( $nt->getDBkey() ) );
 				$m = str_replace( "%", "\\%", $m );
 				$m = str_replace( "_", "\\_", $m );
@@ -138,17 +137,14 @@
 	function getForm() {
 		global $wgRequest, $wgMiserMode;
 		$url = $this->getTitle()->escapeLocalURL();
-		$msgSubmit = wfMsgHtml( 'table_pager_limit_submit' );
-		$msgSearch = wfMsgHtml( 'imagelist_search_for' );
 		$search = $wgRequest->getText( 'ilsearch' );
-		$encSearch = htmlspecialchars( $search );
-		$s = "<form method=\"get\" action=\"$url\">\n" . 
+		$s = "<form method=\"get\" action=\"$url\">\n" .
 			wfMsgHtml( 'table_pager_limit', $this->getLimitSelect() );
 		if ( !$wgMiserMode ) {
-			$s .= "<br/>\n" . $msgSearch .
-				" <input type=\"text\" size=\"20\" name=\"ilsearch\" value=\"$encSearch\"/><br/>\n";
+			$s .= "<br/>\n" .
+			Xml::inputLabel( wfMsg( 'imagelist_search_for' ), 'ilsearch', 'mw-ilsearch', 20, $search );
 		}
-		$s .= " <input type=\"submit\" value=\"$msgSubmit\"/>\n" .
+		$s .= " " . Xml::submitButton( wfMsg( 'table_pager_limit_submit' ) ) ." \n" .
 			$this->getHiddenFields( array( 'limit', 'ilsearch' ) ) .
 			"</form>\n";
 		return $s;
diff -NaurB -x .svn includes/SpecialImport.php /srv/web/fp014/source/includes/SpecialImport.php
--- includes/SpecialImport.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialImport.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,8 +19,7 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -223,8 +222,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class WikiRevision {
 	var $title = null;
@@ -304,7 +302,7 @@
 	}
 
 	function importOldRevision() {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		# Sneak a single revision into place
 		$user = User::newFromName( $this->getUser() );
@@ -386,8 +384,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class WikiImporter {
 	var $mSource = null;
@@ -508,7 +505,7 @@
 	 * @private
 	 */
 	function importRevision( &$revision ) {
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		return $dbw->deadlockLoop( array( &$revision, 'importOldRevision' ) );
 	}
 
@@ -774,7 +771,6 @@
 
 }
 
-/** @package MediaWiki */
 class ImportStringSource {
 	function ImportStringSource( $string ) {
 		$this->mString = $string;
@@ -795,7 +791,6 @@
 	}
 }
 
-/** @package MediaWiki */
 class ImportStreamSource {
 	function ImportStreamSource( $handle ) {
 		$this->mHandle = $handle;
diff -NaurB -x .svn includes/SpecialIpblocklist.php /srv/web/fp014/source/includes/SpecialIpblocklist.php
--- includes/SpecialIpblocklist.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialIpblocklist.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -10,7 +9,7 @@
  */
 function wfSpecialIpblocklist() {
 	global $wgUser, $wgOut, $wgRequest;
-
+	
 	$ip = $wgRequest->getVal( 'wpUnblockAddress', $wgRequest->getVal( 'ip' ) );
 	$id = $wgRequest->getVal( 'id' );
 	$reason = $wgRequest->getText( 'wpUnblockReason' );
@@ -27,8 +26,18 @@
 			$wgOut->permissionRequired( 'block' );
 			return;
 		}
+		# Can't unblock when the database is locked
+		if( wfReadOnly() ) {
+			$wgOut->readOnlyPage();
+			return;
+		}
 		$ipu->doSubmit();
 	} else if ( "unblock" == $action ) {
+		# Can't unblock when the database is locked
+		if( wfReadOnly() ) {
+			$wgOut->readOnlyPage();
+			return;
+		}
 		$ipu->showForm( "" );
 	} else {
 		$ipu->showList( "" );
@@ -37,14 +46,13 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class IPUnblockForm {
 	var $ip, $reason, $id;
 
 	function IPUnblockForm( $ip, $id, $reason ) {
-		$this->ip = $ip;
+		$this->ip = strtr( $ip, '_', ' ' );
 		$this->id = $id;
 		$this->reason = $reason;
 	}
diff -NaurB -x .svn includes/SpecialListredirects.php /srv/web/fp014/source/includes/SpecialListredirects.php
--- includes/SpecialListredirects.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialListredirects.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Rob Church <robchur@gmail.com>
  * @copyright © 2006 Rob Church
@@ -9,8 +8,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class ListredirectsPage extends QueryPage {
@@ -21,7 +19,7 @@
 	function sortDescending() { return( false ); }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$page = $dbr->tableName( 'page' );
 		$sql = "SELECT 'Listredirects' AS type, page_title AS title, page_namespace AS namespace, 0 AS value FROM $page WHERE page_is_redirect = 1";
 		return( $sql );
diff -NaurB -x .svn includes/SpecialListusers.php /srv/web/fp014/source/includes/SpecialListusers.php
--- includes/SpecialListusers.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialListusers.php	2007-02-01 01:04:18.000000000 +0000
@@ -23,8 +23,7 @@
 # http://www.gnu.org/copyleft/gpl.html
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -32,8 +31,7 @@
  * rights (sysop, bureaucrat, developer) will have them displayed
  * next to their names.
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class ListUsersPage extends QueryPage {
 	var $requestedGroup = '';
@@ -74,14 +72,16 @@
 	 * @todo localize
 	 */
 	function getPageHeader( ) {
+		global $wgContLang;
+
 		$self = $this->getTitle();
 
 		# Form tag
 		$out = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );
 		
 		# Group drop-down list
-		$out .= wfElement( 'label', array( 'for' => 'group' ), wfMsg( 'group' ) ) . ' ';
-		$out .= wfOpenElement( 'select', array( 'name' => 'group' ) );
+		$out .= wfElement( 'label', array( 'for' => 'group', 'dir' => $wgContLang->getDirLangStr() ), wfMsg( 'group' ) ) . ' ';
+		$out .= wfOpenElement( 'select', array( 'name' => 'group', 'id' => 'group' ) );
 		$out .= wfElement( 'option', array( 'value' => '' ), wfMsg( 'group-all' ) ); # Item for "all groups"
 		$groups = User::getAllGroups();
 		foreach( $groups as $group ) {
@@ -112,7 +112,7 @@
 
 	function getSQL() {
 		global $wgDBtype;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$user = $dbr->tableName( 'user' );
 		$user_groups = $dbr->tableName( 'user_groups' );
 
@@ -155,6 +155,10 @@
 		if( $this->requestedUser != '' ) {
 			$conds[] = 'user_name >= ' . $dbr->addQuotes( $this->requestedUser );
 		}
+
+		/** wikia patch to hide useres with names starting with ! */
+		$conds[] = "user_name NOT LIKE '!%'";
+
 		return $conds;
 	}
 
@@ -179,7 +183,7 @@
 		$groups = null;
 
 		if( !isset( $result->numgroups ) || $result->numgroups > 0 ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$result = $dbr->select( 'user_groups',
 				array( 'ug_group' ),
 				array( 'ug_user' => $result->user_id ),
diff -NaurB -x .svn includes/SpecialLockdb.php /srv/web/fp014/source/includes/SpecialLockdb.php
--- includes/SpecialLockdb.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialLockdb.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -38,8 +37,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class DBLockForm {
 	var $reason = '';
@@ -126,7 +124,7 @@
 		$wgOut->addWikiText( wfMsg( 'lockdbsuccesstext' ) );
 	}
 	
-	function notWritable() {
+	public static function notWritable() {
 		global $wgOut;
 		$wgOut->errorPage( 'lockdb', 'lockfilenotwritable' );
 	}
diff -NaurB -x .svn includes/SpecialLog.php /srv/web/fp014/source/includes/SpecialLog.php
--- includes/SpecialLog.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialLog.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,8 +19,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -38,8 +37,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class LogReader {
 	var $db, $joinClauses, $whereClauses;
@@ -49,7 +47,7 @@
 	 * @param WebRequest $request For internal use use a FauxRequest object to pass arbitrary parameters.
 	 */
 	function LogReader( $request ) {
-		$this->db =& wfGetDB( DB_SLAVE );
+		$this->db = wfGetDB( DB_SLAVE );
 		$this->setupQuery( $request );
 	}
 
@@ -203,8 +201,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class LogViewer {
 	/**
@@ -218,7 +215,7 @@
 	 */
 	function LogViewer( &$reader ) {
 		global $wgUser;
-		$this->skin =& $wgUser->getSkin();
+		$this->skin = $wgUser->getSkin();
 		$this->reader =& $reader;
 	}
 
@@ -252,8 +249,8 @@
 		$batch = new LinkBatch;
 		while ( $s = $result->fetchObject() ) {
 			// User link
-			$title = Title::makeTitleSafe( NS_USER, $s->user_name );
-			$batch->addObj( $title );
+			$batch->addObj( Title::makeTitleSafe( NS_USER, $s->user_name ) );
+			$batch->addObj( Title::makeTitleSafe( NS_USER_TALK, $s->user_name ) );
 
 			// Move destination link
 			if ( $s->log_type == 'move' ) {
@@ -314,7 +311,7 @@
 			$linkCache->addBadLinkObj( $title );
 		}
 
-		$userLink = $this->skin->userLink( $s->log_user, $s->user_name ) . $this->skin->userToolLinks( $s->log_user, $s->user_name );
+		$userLink = $this->skin->userLink( $s->log_user, $s->user_name ) . $this->skin->userToolLinksRedContribs( $s->log_user, $s->user_name );
 		$comment = $this->skin->commentBlock( $s->log_comment );
 		$paramArray = LogPage::extractParams( $s->log_params );
 		$revert = '';
@@ -357,11 +354,11 @@
 		$title = SpecialPage::getTitleFor( 'Log' );
 		$special = htmlspecialchars( $title->getPrefixedDBkey() );
 		$out->addHTML( "<form action=\"$action\" method=\"get\">\n" .
-			"<input type='hidden' name='title' value=\"$special\" />\n" .
-			$this->getTypeMenu() .
-			$this->getUserInput() .
-			$this->getTitleInput() .
-			"<input type='submit' value=\"" . wfMsg( 'allpagessubmit' ) . "\" />" .
+			Xml::hidden( 'title', $special ) . "\n" .
+			$this->getTypeMenu() . "\n" .
+			$this->getUserInput() . "\n" .
+			$this->getTitleInput() . "\n" .
+			Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "\n" .
 			"</form>" );
 	}
 
@@ -372,11 +369,11 @@
 	function getTypeMenu() {
 		$out = "<select name='type'>\n";
 		foreach( LogPage::validTypes() as $type ) {
-			$text = htmlspecialchars( LogPage::logName( $type ) );
-			$selected = ($type == $this->reader->queryType()) ? ' selected="selected"' : '';
-			$out .= "<option value=\"$type\"$selected>$text</option>\n";
+			$text = LogPage::logName( $type );
+			$selected = ($type == $this->reader->queryType());
+			$out .= Xml::option( $text, $type, $selected ) . "\n";
 		}
-		$out .= "</select>\n";
+		$out .= '</select>';
 		return $out;
 	}
 
@@ -385,8 +382,8 @@
 	 * @private
 	 */
 	function getUserInput() {
-		$user = htmlspecialchars( $this->reader->queryUser() );
-		return wfMsg('specialloguserlabel') . "<input type='text' name='user' size='12' value=\"$user\" />\n";
+		$user =  $this->reader->queryUser();
+		return Xml::inputLabel( wfMsg( 'specialloguserlabel' ), 'user', 'user', 12, $user );
 	}
 
 	/**
@@ -394,8 +391,8 @@
 	 * @private
 	 */
 	function getTitleInput() {
-		$title = htmlspecialchars( $this->reader->queryTitle() );
-		return wfMsg('speciallogtitlelabel') . "<input type='text' name='page' size='20' value=\"$title\" />\n";
+		$title = $this->reader->queryTitle();
+		return Xml::inputLabel( wfMsg( 'speciallogtitlelabel' ), 'page', 'page', 20, $title );
 	}
 
 	/**
diff -NaurB -x .svn includes/SpecialLonelypages.php /srv/web/fp014/source/includes/SpecialLonelypages.php
--- includes/SpecialLonelypages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialLonelypages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class LonelyPagesPage extends PageQueryPage {
 
@@ -29,7 +27,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $pagelinks ) = $dbr->tableNamesN( 'page', 'pagelinks' );
 
 		return
diff -NaurB -x .svn includes/SpecialLongpages.php /srv/web/fp014/source/includes/SpecialLongpages.php
--- includes/SpecialLongpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialLongpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class LongPagesPage extends ShortPagesPage {
 
diff -NaurB -x .svn includes/SpecialMIMEsearch.php /srv/web/fp014/source/includes/SpecialMIMEsearch.php
--- includes/SpecialMIMEsearch.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMIMEsearch.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,16 +3,14 @@
  * A special page to search for files by MIME type as defined in the
  * img_major_mime and img_minor_mime fields in the image table
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MIMEsearchPage extends QueryPage {
 	var $major, $minor;
@@ -38,7 +36,7 @@
 	}
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$image = $dbr->tableName( 'image' );
 		$major = $dbr->addQuotes( $this->major );
 		$minor = $dbr->addQuotes( $this->minor );
@@ -69,7 +67,7 @@
 		$download = $skin->makeMediaLink( $nt->getText(), 'fuck me!', wfMsgHtml( 'download' ) );
 		$bytes = wfMsgExt( 'nbytes', array( 'parsemag', 'escape'),
 			$wgLang->formatNum( $result->img_size ) );
-		$dimensions = wfMsg( 'widthheight', $wgLang->formatNum( $result->img_width ),
+		$dimensions = wfMsgHtml( 'widthheight', $wgLang->formatNum( $result->img_width ),
 			$wgLang->formatNum( $result->img_height ) );
 		$user = $skin->makeLinkObj( Title::makeTitle( NS_USER, $result->img_user_text ), $result->img_user_text );
 		$time = $wgLang->timeanddate( $result->img_timestamp );
diff -NaurB -x .svn includes/SpecialMostcategories.php /srv/web/fp014/source/includes/SpecialMostcategories.php
--- includes/SpecialMostcategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMostcategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -9,8 +8,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MostcategoriesPage extends QueryPage {
 
@@ -19,7 +17,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $categorylinks, $page) = $dbr->tableNamesN( 'categorylinks', 'page' );
 		return
 			"
diff -NaurB -x .svn includes/SpecialMostimages.php /srv/web/fp014/source/includes/SpecialMostimages.php
--- includes/SpecialMostimages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMostimages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -9,8 +8,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MostimagesPage extends QueryPage {
 
@@ -19,7 +17,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$imagelinks = $dbr->tableName( 'imagelinks' );
 		return
 			"
diff -NaurB -x .svn includes/SpecialMostlinkedcategories.php /srv/web/fp014/source/includes/SpecialMostlinkedcategories.php
--- includes/SpecialMostlinkedcategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMostlinkedcategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * A querypage to show categories ordered in descending order by the pages  in them
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -11,8 +10,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MostlinkedCategoriesPage extends QueryPage {
 
@@ -21,7 +19,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$categorylinks = $dbr->tableName( 'categorylinks' );
 		$name = $dbr->addQuotes( $this->getName() );
 		return
diff -NaurB -x .svn includes/SpecialMostlinked.php /srv/web/fp014/source/includes/SpecialMostlinked.php
--- includes/SpecialMostlinked.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMostlinked.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,8 +3,7 @@
 /**
  * A special page to show pages ordered by the number of pages linking to them
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @author Rob Church <robchur@gmail.com>
@@ -14,8 +13,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MostlinkedPage extends QueryPage {
 
@@ -27,7 +25,7 @@
 	 * Note: Getting page_namespace only works if $this->isCached() is false
 	 */
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $pagelinks, $page ) = $dbr->tableNamesN( 'pagelinks', 'page' );
 		return
 			"SELECT 'Mostlinked' AS type,
diff -NaurB -x .svn includes/SpecialMostrevisions.php /srv/web/fp014/source/includes/SpecialMostrevisions.php
--- includes/SpecialMostrevisions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMostrevisions.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * A special page to show pages in the
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -11,8 +10,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MostrevisionsPage extends QueryPage {
 
@@ -21,7 +19,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $revision, $page ) = $dbr->tableNamesN( 'revision', 'page' );
 		return
 			"
diff -NaurB -x .svn includes/SpecialMovepage.php /srv/web/fp014/source/includes/SpecialMovepage.php
--- includes/SpecialMovepage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialMovepage.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -43,8 +42,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class MovePageForm {
 	var $oldTitle, $newTitle, $reason; # Text input
@@ -144,8 +142,13 @@
 
 		$moveTalkChecked = $this->moveTalk ? ' checked="checked"' : '';
 
+		/** Added by inez@wikia.com
+		 *  Used in ConfirmEdit extension (Captcha and FancyCaptcha)
+		 */
+		$wgOut->addHTML( "
+<form id=\"movepage\" method=\"post\" action=\"{$action}\">");
+		wfRunHooks( 'ArticleMoveForm', array( &$wgOut ) );
 		$wgOut->addHTML( "
-<form id=\"movepage\" method=\"post\" action=\"{$action}\">
 	<table border='0'>
 		<tr>
 			<td align='right'>{$movearticle}:</td>
@@ -208,6 +211,13 @@
 
 		# Variables beginning with 'o' for old article 'n' for new article
 
+                /** Added by inez@wikia.com
+                 *  Used in ConfirmEdit extension (Captcha and FancyCaptcha)
+		 */
+		if( ! wfRunHooks( 'SpecialMovepageBeforeMove', array(&$this))) {
+			return;
+		}		
+
 		$ot = Title::newFromText( $this->oldTitle );
 		$nt = Title::newFromText( $this->newTitle );
 
diff -NaurB -x .svn includes/SpecialNewimages.php /srv/web/fp014/source/includes/SpecialNewimages.php
--- includes/SpecialNewimages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialNewimages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -12,7 +11,7 @@
 	global $wgUser, $wgOut, $wgLang, $wgRequest, $wgGroupPermissions;
 
 	$wpIlMatch = $wgRequest->getText( 'wpIlMatch' );
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	$sk = $wgUser->getSkin();
 	$shownav = !$specialPage->including();
 	$hidebots = $wgRequest->getBool('hidebots',1);
@@ -161,9 +160,9 @@
 	if ($shownav) {
 		$wgOut->addHTML( "<form id=\"imagesearch\" method=\"post\" action=\"" .
 		  "{$action}\">" .
-		  "<input type='text' size='20' name=\"wpIlMatch\" value=\"" .
-		  htmlspecialchars( $wpIlMatch ) . "\" /> " .
-		  "<input type='submit' name=\"wpIlSubmit\" value=\"{$sub}\" /></form>" );
+			Xml::input( 'wpIlMatch', 20, $wpIlMatch ) . ' ' .
+		  Xml::submitButton( $sub, array( 'name' => 'wpIlSubmit' ) ) .
+		  "</form>" );
 	}
 
 	/**
@@ -178,21 +177,21 @@
 	}
 	$now = wfTimestampNow();
 	$date = $wgLang->timeanddate( $now, true );
-	$dateLink = $sk->makeKnownLinkObj( $titleObj, wfMsg( 'sp-newimages-showfrom', $date ), 'from='.$now.$botpar.$searchpar );
+	$dateLink = $sk->makeKnownLinkObj( $titleObj, wfMsgHtml( 'sp-newimages-showfrom', $date ), 'from='.$now.$botpar.$searchpar );
 
-	$botLink = $sk->makeKnownLinkObj($titleObj, wfMsg( 'showhidebots', ($hidebots ? wfMsg('show') : wfMsg('hide'))),'hidebots='.($hidebots ? '0' : '1').$searchpar);
+	$botLink = $sk->makeKnownLinkObj($titleObj, wfMsgHtml( 'showhidebots', ($hidebots ? wfMsgHtml('show') : wfMsgHtml('hide'))),'hidebots='.($hidebots ? '0' : '1').$searchpar);
 
-	$prevLink = wfMsg( 'prevn', $wgLang->formatNum( $limit ) );
+	$prevLink = wfMsgHtml( 'prevn', $wgLang->formatNum( $limit ) );
 	if( $firstTimestamp && $firstTimestamp != $latestTimestamp ) {
 		$prevLink = $sk->makeKnownLinkObj( $titleObj, $prevLink, 'from=' . $firstTimestamp . $botpar . $searchpar );
 	}
 
-	$nextLink = wfMsg( 'nextn', $wgLang->formatNum( $limit ) );
+	$nextLink = wfMsgHtml( 'nextn', $wgLang->formatNum( $limit ) );
 	if( $shownImages > $limit && $lastTimestamp ) {
 		$nextLink = $sk->makeKnownLinkObj( $titleObj, $nextLink, 'until=' . $lastTimestamp.$botpar.$searchpar );
 	}
 
-	$prevnext = '<p>' . $botLink . ' '. wfMsg( 'viewprevnext', $prevLink, $nextLink, $dateLink ) .'</p>';
+	$prevnext = '<p>' . $botLink . ' '. wfMsgHtml( 'viewprevnext', $prevLink, $nextLink, $dateLink ) .'</p>';
 
 	if ($shownav)
 		$wgOut->addHTML( $prevnext );
diff -NaurB -x .svn includes/SpecialNewpages.php /srv/web/fp014/source/includes/SpecialNewpages.php
--- includes/SpecialNewpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialNewpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class NewPagesPage extends QueryPage {
 
@@ -41,7 +39,7 @@
 	function getSQL() {
 		global $wgUser, $wgUseRCPatrol;
 		$usepatrol = ( $wgUseRCPatrol && $wgUser->isAllowed( 'patrol' ) ) ? 1 : 0;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $recentchanges, $page ) = $dbr->tableNamesN( 'recentchanges', 'page' );
 
 		$uwhere = $this->makeUserWhere( $dbr );
@@ -133,13 +131,13 @@
 	 */	
 	function getPageHeader() {
 		$self = SpecialPage::getTitleFor( $this->getName() );
-		$form = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );
-		$form .= '<table><tr><td align="right">' . wfMsgHtml( 'namespace' ) . '</td>';
-		$form .= '<td>' . HtmlNamespaceSelector( $this->namespace ) . '</td><tr>';
-		$form .= '<tr><td align="right">' . wfMsgHtml( 'newpages-username' ) . '</td>';
-		$form .= '<td>' . wfInput( 'username', 30, $this->username ) . '</td></tr>';
-		$form .= '<tr><td></td><td>' . wfSubmitButton( wfMsg( 'allpagessubmit' ) ) . '</td></tr></table>';
-		$form .= wfHidden( 'offset', $this->offset ) . wfHidden( 'limit', $this->limit ) . '</form>';
+		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );
+		$form .= '<table><tr><td align="right">' . Xml::label( wfMsg( 'namespace' ), 'namespace' ) . '</td>';
+		$form .= '<td>' . Xml::namespaceSelector( $this->namespace ) . '</td><tr>';
+		$form .= '<tr><td align="right">' . Xml::label( wfMsg( 'newpages-username' ), 'mw-np-username' ) . '</td>';
+		$form .= '<td>' . Xml::input( 'username', 30, $this->username, array( 'id' => 'mw-np-username' ) ) . '</td></tr>';
+		$form .= '<tr><td></td><td>' . Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . '</td></tr></table>';
+		$form .= Xml::hidden( 'offset', $this->offset ) . Xml::hidden( 'limit', $this->limit ) . '</form>';
 		return $form;
 	}
 	
diff -NaurB -x .svn includes/SpecialPage.php /srv/web/fp014/source/includes/SpecialPage.php
--- includes/SpecialPage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialPage.php	2007-02-01 01:03:18.000000000 +0000
@@ -17,8 +17,7 @@
  * SpecialPage::$mList. To remove a core static special page at runtime, use
  * a SpecialPage_initList hook.
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -28,7 +27,6 @@
 /**
  * Parent special page class, also static functions for handling the special
  * page list
- * @package MediaWiki
  */
 class SpecialPage
 {
@@ -109,6 +107,7 @@
 		'Newpages'                  => array( 'IncludableSpecialPage', 'Newpages' ),
 		'Ancientpages'              => array( 'SpecialPage', 'Ancientpages' ),
 		'Deadendpages'              => array( 'SpecialPage', 'Deadendpages' ),
+		'Protectedpages'	    => array( 'SpecialPage', 'Protectedpages' ),
 		'Allpages'                  => array( 'IncludableSpecialPage', 'Allpages' ),
 		'Prefixindex'               => array( 'IncludableSpecialPage', 'Prefixindex' ) ,
 		'Ipblocklist'               => array( 'SpecialPage', 'Ipblocklist' ),
@@ -691,7 +690,6 @@
 
 /**
  * Shortcut to construct a special page which is unlisted by default
- * @package MediaWiki
  */
 class UnlistedSpecialPage extends SpecialPage
 {
@@ -702,7 +700,6 @@
 
 /**
  * Shortcut to construct an includable special  page
- * @package MediaWiki
  */
 class IncludableSpecialPage extends SpecialPage
 {
diff -NaurB -x .svn includes/SpecialPopularpages.php /srv/web/fp014/source/includes/SpecialPopularpages.php
--- includes/SpecialPopularpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialPopularpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class PopularPagesPage extends QueryPage {
 
@@ -23,7 +21,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$page = $dbr->tableName( 'page' );
 
 		return
diff -NaurB -x .svn includes/SpecialPreferences.php /srv/web/fp014/source/includes/SpecialPreferences.php
--- includes/SpecialPreferences.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialPreferences.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  * Hold things related to displaying and saving user preferences.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -18,8 +17,7 @@
 /**
  * Preferences form handling
  * This object will show the preferences form and can save it as well.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class PreferencesForm {
 	var $mQuickbar, $mOldpass, $mNewpass, $mRetypePass, $mStubs;
@@ -838,7 +836,7 @@
 
 		# Editing
 		#
-		global $wgLivePreview, $wgUseRCPatrol;
+		global $wgLivePreview;
 		$wgOut->addHTML( '<fieldset><legend>' . wfMsg( 'textboxsize' ) . '</legend>
 			<div>' .
 				wfInputLabel( wfMsg( 'rows' ), 'wpRows', 'wpRows', 3, $this->mRows ) .
@@ -941,11 +939,11 @@
 		$wgOut->addHTML( '</fieldset>' );
 
 		$token = $wgUser->editToken();
+		$skin = $wgUser->getSkin();
 		$wgOut->addHTML( "
 	<div id='prefsubmit'>
 	<div>
-		<input type='submit' name='wpSaveprefs' class='btnSavePrefs' value=\"" . wfMsgHtml( 'saveprefs' ) . "\" accesskey=\"".
-		wfMsgHtml('accesskey-save')."\" title=\"".wfMsgHtml('tooltip-save')."\" />
+		<input type='submit' name='wpSaveprefs' class='btnSavePrefs' value=\"" . wfMsgHtml( 'saveprefs' ) . '"'.$skin->tooltipAndAccesskey('save')." />
 		<input type='submit' name='wpReset' value=\"" . wfMsgHtml( 'resetprefs' ) . "\" />
 	</div>
 
diff -NaurB -x .svn includes/SpecialPrefixindex.php /srv/web/fp014/source/includes/SpecialPrefixindex.php
--- includes/SpecialPrefixindex.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialPrefixindex.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 require_once 'SpecialAllpages.php';
@@ -71,11 +70,11 @@
 		$out = wfMsgWikiHtml( 'allpagesbadtitle' );
 	} else {
 		list( $namespace, $prefixKey, $prefix ) = $prefixList;
-		list( $fromNs, $fromKey, $from ) = $fromList;
+		list( /* $fromNs */, $fromKey, $from ) = $fromList;
 
 		### FIXME: should complain if $fromNs != $namespace
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$res = $dbr->select( 'page',
 			array( 'page_namespace', 'page_title', 'page_is_redirect' ),
diff -NaurB -x .svn includes/SpecialProtectedpages.php /srv/web/fp014/source/includes/SpecialProtectedpages.php
--- includes/SpecialProtectedpages.php	1970-01-01 00:00:00.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialProtectedpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -0,0 +1,96 @@
+<?php
+/**
+ *
+ * @addtogroup SpecialPage
+ */
+
+/**
+ *
+ * @addtogroup SpecialPage
+ */
+class ProtectedPagesPage extends PageQueryPage {
+
+	function getName( ) {
+		return "Protectedpages";
+	}
+
+	function getPageHeader() {
+		return '<p>' . wfMsg('protectedpagestext') . '</p>';
+	}
+
+	/**
+	 * LEFT JOIN is expensive
+	 *
+	 * @return true
+	 */
+	function isExpensive( ) {
+		return 1;
+	}
+
+	function isSyndicated() { return false; }
+
+	/**
+	 * @return false
+	 */
+	function sortDescending() {
+		return false;
+	}
+
+    /**
+	 * @return string an sqlquery
+	 */
+	function getSQL() {
+		$dbr =& wfGetDB( DB_SLAVE );
+		list( $page, $page_restrictions ) = $dbr->tableNamesN( 'page', 'page_restrictions' );
+		return "SELECT DISTINCT page_id, 'Protectedpages' as type, page_namespace AS namespace, page_title as title, " .
+			"page_title AS value, pr_level, pr_expiry " .
+			"FROM $page, $page_restrictions WHERE page_id = pr_page AND pr_user IS NULL ";
+    }
+
+	/**
+	 * Make link to the page, and add the protection levels.
+	 *
+	 * @param $skin Skin to be used
+	 * @param $result Result row
+	 * @return string
+	 */
+	function formatResult( $skin, $result ) {
+		global $wgLang;
+		$title = Title::makeTitleSafe( $result->namespace, $result->title );
+		$link = $skin->makeLinkObj( $title );
+
+		$description_items = array ();
+
+		$protType = wfMsg( 'restriction-level-' . $result->pr_level );
+
+		$description_items[] = $protType;
+
+		$expiry_description = '';
+
+		if ( $result->pr_expiry != 'infinity' && strlen($result->pr_expiry) ) {
+			$expiry = Block::decodeExpiry( $result->pr_expiry );
+	
+			$expiry_description = wfMsgForContent( 'protect-expiring', $wgLang->timeanddate( $expiry ) );
+
+			$description_items[] = $expiry_description;
+		}
+
+		return wfSpecialList( $link, implode( $description_items, ', ' ) );
+	}
+}
+
+/**
+ * Constructor
+ */
+function wfSpecialProtectedpages() {
+
+	list( $limit, $offset ) = wfCheckLimits();
+
+	$depp = new ProtectedPagesPage();
+
+	Title::purgeExpiredRestrictions();
+
+	return $depp->doQuery( $offset, $limit );
+}
+
+?>
diff -NaurB -x .svn includes/SpecialRandompage.php /srv/web/fp014/source/includes/SpecialRandompage.php
--- includes/SpecialRandompage.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialRandompage.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -30,7 +29,7 @@
 	# interpolation and sprintf() can muck up with locale-specific decimal separator
 	$randstr = wfRandom();
 
-	$db =& wfGetDB( DB_SLAVE );
+	$db = wfGetDB( DB_SLAVE );
 	$use_index = $db->useIndexClause( 'page_random' );
 	$page = $db->tableName( 'page' );
 
diff -NaurB -x .svn includes/SpecialRandomredirect.php /srv/web/fp014/source/includes/SpecialRandomredirect.php
--- includes/SpecialRandomredirect.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialRandomredirect.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,8 +3,7 @@
 /**
  * Special page to direct the user to a random redirect page (minus the second redirect)
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  * @author Rob Church <robchur@gmail.com>
  * @licence GNU General Public Licence 2.0 or later
  */
@@ -25,7 +24,7 @@
 	# Same logic as RandomPage
 	$randstr = wfRandom();
 
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	$use_index = $dbr->useIndexClause( 'page_random' );
 	$page = $dbr->tableName( 'page' );
 
diff -NaurB -x .svn includes/SpecialRecentchangeslinked.php /srv/web/fp014/source/includes/SpecialRecentchangeslinked.php
--- includes/SpecialRecentchangeslinked.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialRecentchangeslinked.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  * This is to display changes made to all articles linked in an article.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -48,7 +47,7 @@
 	}
 	list( $limit, /* offset */ ) = wfCheckLimits( 100, 'rclimit' );
 
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	$cutoff = $dbr->timestamp( time() - ( $days * 86400 ) );
 
 	$hideminor = ($hideminor ? 1 : 0);
@@ -73,7 +72,7 @@
 	$GROUPBY = "
 	GROUP BY rc_cur_id,rc_namespace,rc_title,
 		rc_user,rc_comment,rc_user_text,rc_timestamp,rc_minor,
-		rc_new, rc_id, rc_this_oldid, rc_last_oldid, rc_bot, rc_patrolled, rc_type
+		rc_new, rc_id, rc_this_oldid, rc_last_oldid, rc_bot, rc_patrolled, rc_type, rc_old_len, rc_new_len
 " . ($uid ? ",wl_user" : "") . "
 		ORDER BY rc_timestamp DESC
 	LIMIT {$limit}";
diff -NaurB -x .svn includes/SpecialRecentchanges.php /srv/web/fp014/source/includes/SpecialRecentchanges.php
--- includes/SpecialRecentchanges.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialRecentchanges.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -105,7 +104,7 @@
 
 
 	# Database connection and caching
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 	list( $recentchanges, $watchlist ) = $dbr->tableNamesN( 'recentchanges', 'watchlist' );
 
 
diff -NaurB -x .svn includes/SpecialResetpass.php /srv/web/fp014/source/includes/SpecialResetpass.php
--- includes/SpecialResetpass.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialResetpass.php	2007-02-01 01:03:18.000000000 +0000
@@ -68,7 +68,7 @@
 	}
 	
 	function showForm() {
-		global $wgOut, $wgUser, $wgLang, $wgRequest;
+		global $wgOut, $wgUser, $wgRequest;
 		
 		$self = SpecialPage::getTitleFor( 'Resetpass' );		
 		$form  =
diff -NaurB -x .svn includes/SpecialSearch.php /srv/web/fp014/source/includes/SpecialSearch.php
--- includes/SpecialSearch.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialSearch.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,8 +19,7 @@
 
 /**
  * Run text & title search and display the output
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -44,8 +43,7 @@
 
 /**
  * @todo document
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class SpecialSearch {
 
@@ -314,7 +312,7 @@
 			wfProfileOut( $fname );
 			return "<!-- Broken link in search result -->\n";
 		}
-		$sk =& $wgUser->getSkin();
+		$sk = $wgUser->getSkin();
 
 		$contextlines = $wgUser->getOption( 'contextlines',  5 );
 		$contextchars = $wgUser->getOption( 'contextchars', 50 );
diff -NaurB -x .svn includes/SpecialShortpages.php /srv/web/fp014/source/includes/SpecialShortpages.php
--- includes/SpecialShortpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialShortpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,15 +1,13 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  * SpecialShortpages extends QueryPage. It is used to return the shortest
  * pages in the database.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class ShortPagesPage extends QueryPage {
 
@@ -29,7 +27,7 @@
 	}
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$page = $dbr->tableName( 'page' );
 		$name = $dbr->addQuotes( $this->getName() );
 
diff -NaurB -x .svn includes/SpecialSpecialpages.php /srv/web/fp014/source/includes/SpecialSpecialpages.php
--- includes/SpecialSpecialpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialSpecialpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -51,7 +50,7 @@
 	/** Now output the HTML */
 	$wgOut->addHTML( '<h2>' . wfMsgHtml( $heading ) . "</h2>\n<ul>" );
 	foreach ( $sortedPages as $desc => $title ) {
-		$link = $sk->makeKnownLinkObj( $title, $desc );
+		$link = $sk->makeKnownLinkObj( $title , htmlspecialchars( $desc ) );
 		$wgOut->addHTML( "<li>{$link}</li>\n" );
 	}
 	$wgOut->addHTML( "</ul>\n" );
diff -NaurB -x .svn includes/SpecialStatistics.php /srv/web/fp014/source/includes/SpecialStatistics.php
--- includes/SpecialStatistics.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialStatistics.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
 *
-* @package MediaWiki
-* @subpackage SpecialPage
+* @addtogroup SpecialPage
 */
 
 /**
@@ -14,7 +13,7 @@
 
 	$action = $wgRequest->getVal( 'action' );
 
-	$dbr =& wfGetDB( DB_SLAVE );
+	$dbr = wfGetDB( DB_SLAVE );
 
 	$views = SiteStats::views();
 	$edits = SiteStats::edits();
@@ -64,7 +63,7 @@
 			$res = $dbr->query( $sql, $fname );
 			if( $res ) {
 				$wgOut->addHtml( '<h2>' . wfMsgHtml( 'statistics-mostpopular' ) . '</h2>' );
-				$skin =& $wgUser->getSkin();
+				$skin = $wgUser->getSkin();
 				$wgOut->addHtml( '<ol>' );
 				while( $row = $dbr->fetchObject( $res ) ) {
 					$link = $skin->makeKnownLinkObj( Title::makeTitleSafe( $row->page_namespace, $row->page_title ) );
@@ -76,6 +75,10 @@
 			}
 		}
 		
+		$footer = wfMsg( 'statistics-footer' );
+		if( !wfEmptyMsg( 'statistics-footer', $footer ) && $footer != '' )
+			$wgOut->addWikiText( $footer );
+		
 	}
 }
 ?>
diff -NaurB -x .svn includes/SpecialUncategorizedcategories.php /srv/web/fp014/source/includes/SpecialUncategorizedcategories.php
--- includes/SpecialUncategorizedcategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUncategorizedcategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -12,8 +11,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UncategorizedCategoriesPage extends UncategorizedPagesPage {
 	function UncategorizedCategoriesPage() {
diff -NaurB -x .svn includes/SpecialUncategorizedimages.php /srv/web/fp014/source/includes/SpecialUncategorizedimages.php
--- includes/SpecialUncategorizedimages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUncategorizedimages.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,31 +3,30 @@
 /**
  * Special page lists images which haven't been categorised
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  * @author Rob Church <robchur@gmail.com>
  */
- 
+
 class UncategorizedImagesPage extends QueryPage {
 
 	function getName() {
 		return 'Uncategorizedimages';
 	}
-	
+
 	function sortDescending() {
 		return false;
 	}
-	
+
 	function isExpensive() {
 		return true;
 	}
-	
+
 	function isSyndicated() {
 		return false;
 	}
-	
+
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
 		$ns = NS_IMAGE;
 
@@ -36,14 +35,13 @@
 				FROM {$page} LEFT JOIN {$categorylinks} ON page_id = cl_from
 				WHERE cl_from IS NULL AND page_namespace = {$ns} AND page_is_redirect = 0";
 	}
-	
-	function formatResult( &$skin, $row ) {
+
+	function formatResult( $skin, $row ) {
 		global $wgContLang;
 		$title = Title::makeTitleSafe( NS_IMAGE, $row->title );
 		$label = htmlspecialchars( $wgContLang->convert( $title->getText() ) );
 		return $skin->makeKnownLinkObj( $title, $label );
 	}
-				
 }
 
 function wfSpecialUncategorizedimages() {
diff -NaurB -x .svn includes/SpecialUncategorizedpages.php /srv/web/fp014/source/includes/SpecialUncategorizedpages.php
--- includes/SpecialUncategorizedpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUncategorizedpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UncategorizedPagesPage extends PageQueryPage {
 	var $requestedNamespace = NS_MAIN;
@@ -27,7 +25,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
 		$name = $dbr->addQuotes( $this->getName() );
 
diff -NaurB -x .svn includes/SpecialUndelete.php /srv/web/fp014/source/includes/SpecialUndelete.php
--- includes/SpecialUndelete.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUndelete.php	2007-02-01 01:04:18.000000000 +0000
@@ -4,8 +4,7 @@
  * Special page allowing users with the appropriate permissions to view
  * and restore deleted content
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  */
 
 /**
@@ -20,36 +19,77 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class PageArchive {
-	var $title;
+	protected $title;
 
-	function PageArchive( &$title ) {
+	function __construct( $title ) {
 		if( is_null( $title ) ) {
 			throw new MWException( 'Archiver() given a null title.');
 		}
-		$this->title =& $title;
+		$this->title = $title;
 	}
 
 	/**
 	 * List all deleted pages recorded in the archive table. Returns result
 	 * wrapper with (ar_namespace, ar_title, count) fields, ordered by page
-	 * namespace/title. Can be called staticaly.
+	 * namespace/title.
 	 *
 	 * @return ResultWrapper
 	 */
-	/* static */ function listAllPages() {
-		$dbr =& wfGetDB( DB_SLAVE );
-		$archive = $dbr->tableName( 'archive' );
-
-		$sql = "SELECT ar_namespace,ar_title, COUNT(*) AS count FROM $archive " .
-		  "GROUP BY ar_namespace,ar_title ORDER BY ar_namespace,ar_title";
-
-		return $dbr->resultObject( $dbr->query( $sql, 'PageArchive::listAllPages' ) );
+	public static function listAllPages() {
+		$dbr = wfGetDB( DB_SLAVE );
+		return self::listPages( $dbr, '' );
 	}
-
+	
+	/**
+	 * List deleted pages recorded in the archive table matching the
+	 * given title prefix.
+	 * Returns result wrapper with (ar_namespace, ar_title, count) fields.
+	 *
+	 * @return ResultWrapper
+	 */
+	public static function listPagesByPrefix( $prefix ) {
+		$dbr = wfGetDB( DB_SLAVE );
+		
+		$title = Title::newFromText( $prefix );
+		if( $title ) {
+			$ns = $title->getNamespace();
+			$encPrefix = $dbr->escapeLike( $title->getDbKey() );
+		} else {
+			// Prolly won't work too good
+			// @todo handle bare namespace names cleanly?
+			$ns = 0;
+			$encPrefix = $dbr->escapeLike( $prefix );
+		}
+		$conds = array(
+			'ar_namespace' => $ns,
+			"ar_title LIKE '$encPrefix%'",
+		);
+		return self::listPages( $dbr, $conds );
+	}
+
+	protected static function listPages( $dbr, $condition ) {
+		return $dbr->resultObject(
+			$dbr->select(
+				array( 'archive' ),
+				array(
+					'ar_namespace',
+					'ar_title',
+					'COUNT(*) AS count',
+				),
+				$condition,
+				__METHOD__,
+				array(
+					'GROUP BY' => 'ar_namespace,ar_title',
+					'ORDER BY' => 'ar_namespace,ar_title',
+					'LIMIT' => 100,
+				)
+			)
+		);
+	}
+	
 	/**
 	 * List the revisions of the given page. Returns result wrapper with
 	 * (ar_minor_edit, ar_timestamp, ar_user, ar_user_text, ar_comment) fields.
@@ -57,7 +97,7 @@
 	 * @return ResultWrapper
 	 */
 	function listRevisions() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'archive',
 			array( 'ar_minor_edit', 'ar_timestamp', 'ar_user', 'ar_user_text', 'ar_comment' ),
 			array( 'ar_namespace' => $this->title->getNamespace(),
@@ -78,7 +118,7 @@
 	 */
 	function listFiles() {
 		if( $this->title->getNamespace() == NS_IMAGE ) {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$res = $dbr->select( 'filearchive',
 				array(
 					'fa_id',
@@ -119,7 +159,7 @@
 	 * @return Revision
 	 */
 	function getRevision( $timestamp ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$row = $dbr->selectRow( 'archive',
 			array(
 				'ar_rev_id',
@@ -163,7 +203,7 @@
 			return Revision::getRevisionText( $row, "ar_" );
 		} else {
 			// New-style: keyed to the text storage backend.
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$text = $dbr->selectRow( 'text',
 				array( 'old_text', 'old_flags' ),
 				array( 'old_id' => $row->ar_text_id ),
@@ -182,7 +222,7 @@
 	 * @return string
 	 */
 	function getLastRevisionText() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$row = $dbr->selectRow( 'archive',
 			array( 'ar_text', 'ar_flags', 'ar_text_id' ),
 			array( 'ar_namespace' => $this->title->getNamespace(),
@@ -201,7 +241,7 @@
 	 * @return bool
 	 */
 	function isDeleted() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$n = $dbr->selectField( 'archive', 'COUNT(ar_title)',
 			array( 'ar_namespace' => $this->title->getNamespace(),
 			       'ar_title' => $this->title->getDBkey() ) );
@@ -262,6 +302,11 @@
 		if( trim( $comment ) != '' )
 			$reason .= ": {$comment}";
 		$log->addEntry( 'restore', $this->title, $reason );
+
+		/** Added by inez@wikia.com
+		 *  Used in LuceneNotifier extension
+		 */
+		wfRunHooks( 'UndeleteComplete', array(&$this->title, &$wgUser, $reason ) );
 		
 		return true;
 	}
@@ -282,7 +327,7 @@
 
 		$restoreAll = empty( $timestamps );
 		
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$page = $dbw->tableName( 'archive' );
 
 		# Does this page already exist? We'll have to update it...
@@ -389,8 +434,10 @@
 			}
 
 			if( $newid ) {
+				wfRunHooks( 'ArticleUndelete', array( &$this->title, true ) );
 				Article::onArticleCreate( $this->title );
 			} else {
+				wfRunHooks( 'ArticleUndelete', array( &$this->title, false ) );
 				Article::onArticleEdit( $this->title );
 			}
 		} else {
@@ -412,17 +459,17 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UndeleteForm {
 	var $mAction, $mTarget, $mTimestamp, $mRestore, $mTargetObj;
 	var $mTargetTimestamp, $mAllowed, $mComment;
 
-	function UndeleteForm( &$request, $par = "" ) {
+	function UndeleteForm( $request, $par = "" ) {
 		global $wgUser;
 		$this->mAction = $request->getVal( 'action' );
 		$this->mTarget = $request->getVal( 'target' );
+		$this->mSearchPrefix = $request->getText( 'prefix' );
 		$time = $request->getVal( 'timestamp' );
 		$this->mTimestamp = $time ? wfTimestamp( TS_MW, $time ) : '';
 		$this->mFile = $request->getVal( 'file' );
@@ -467,9 +514,23 @@
 	}
 
 	function execute() {
-
+		global $wgOut;
+		if ( $this->mAllowed ) {
+			$wgOut->setPagetitle( wfMsg( "undeletepage" ) );
+		} else {
+			$wgOut->setPagetitle( wfMsg( "viewdeletedpage" ) );
+		}
+		
 		if( is_null( $this->mTargetObj ) ) {
-			return $this->showList();
+			$this->showSearchForm();
+
+			# List undeletable articles
+			if( $this->mSearchPrefix ) {
+				$result = PageArchive::listPagesByPrefix(
+					$this->mSearchPrefix );
+				$this->showList( $result );
+			}
+			return;
 		}
 		if( $this->mTimestamp !== '' ) {
 			return $this->showRevision( $this->mTimestamp );
@@ -483,17 +544,35 @@
 		return $this->showHistory();
 	}
 
-	/* private */ function showList() {
-		global $wgLang, $wgContLang, $wgUser, $wgOut;
-
-		# List undeletable articles
-		$result = PageArchive::listAllPages();
+	function showSearchForm() {
+		global $wgOut, $wgScript;
+		$wgOut->addWikiText( wfMsg( 'undelete-header' ) );
+		
+		$wgOut->addHtml(
+			Xml::openElement( 'form', array(
+				'method' => 'get',
+				'action' => $wgScript ) ) .
+			'<fieldset>' .
+			Xml::element( 'legend', array(),
+				wfMsg( 'undelete-search-box' ) ) .
+			Xml::hidden( 'title',
+				SpecialPage::getTitleFor( 'Undelete' )->getPrefixedDbKey() ) .
+			Xml::inputLabel( wfMsg( 'undelete-search-prefix' ),
+				'prefix', 'prefix', 20,
+				$this->mSearchPrefix ) .
+			Xml::submitButton( wfMsg( 'undelete-search-submit' ) ) .
+			'</fieldset>' .
+			'</form>' );
+	}
 
-		if ( $this->mAllowed ) {
-			$wgOut->setPagetitle( wfMsg( "undeletepage" ) );
-		} else {
-			$wgOut->setPagetitle( wfMsg( "viewdeletedpage" ) );
+	/* private */ function showList( $result ) {
+		global $wgLang, $wgContLang, $wgUser, $wgOut;
+		
+		if( $result->numRows() == 0 ) {
+			$wgOut->addWikiText( wfMsg( 'undelete-no-results' ) );
+			return;
 		}
+
 		$wgOut->addWikiText( wfMsg( "undeletepagetext" ) );
 
 		$sk = $wgUser->getSkin();
@@ -502,7 +581,10 @@
 		while( $row = $result->fetchObject() ) {
 			$title = Title::makeTitleSafe( $row->ar_namespace, $row->ar_title );
 			$link = $sk->makeKnownLinkObj( $undelete, htmlspecialchars( $title->getPrefixedText() ), 'target=' . $title->getPrefixedUrl() );
-			$revs = wfMsgHtml( 'undeleterevisions', $wgLang->formatNum( $row->count ) );
+			#$revs = wfMsgHtml( 'undeleterevisions', $wgLang->formatNum( $row->count ) );
+			$revs = wfMsgExt( 'undeleterevisions',
+				array( 'parseinline' ),
+				$wgLang->formatNum( $row->count ) );
 			$wgOut->addHtml( "<li>{$link} ({$revs})</li>\n" );
 		}
 		$result->free();
@@ -513,15 +595,19 @@
 
 	/* private */ function showRevision( $timestamp ) {
 		global $wgLang, $wgUser, $wgOut;
+		$self = SpecialPage::getTitleFor( 'Undelete' );
+		$skin = $wgUser->getSkin();
 
 		if(!preg_match("/[0-9]{14}/",$timestamp)) return 0;
 
 		$archive = new PageArchive( $this->mTargetObj );
 		$rev = $archive->getRevision( $timestamp );
 		
-		$wgOut->setPagetitle( wfMsg( "undeletepage" ) );
-		$wgOut->addWikiText( "(" . wfMsg( "undeleterevision",
-			$wgLang->timeAndDate( $timestamp ) ) . ")\n" );
+		$wgOut->setPageTitle( wfMsg( 'undeletepage' ) );
+		$link = $skin->makeKnownLinkObj( $self, htmlspecialchars( $this->mTargetObj->getPrefixedText() ),
+					'target=' . $this->mTargetObj->getPrefixedUrl() );
+		$wgOut->addHtml( '<p>' . wfMsgHtml( 'undelete-revision', $link,
+			htmlspecialchars( $wgLang->timeAndDate( $timestamp ) ) ) . '</p>' ); 
 		
 		if( !$rev ) {
 			$wgOut->addWikiText( wfMsg( 'undeleterevision-missing' ) );
@@ -532,12 +618,9 @@
 		
 		if( $this->mPreview ) {
 			$wgOut->addHtml( "<hr />\n" );
-			$article = new Article ( $archive->title );  # OutputPage wants an Article obj
-			$wgOut->addPrimaryWikiText( $rev->getText(), $article, false );
+			$wgOut->addWikiTextTitle( $rev->getText(), $this->mTargetObj, false );
 		}
-		
-		$self = SpecialPage::getTitleFor( "Undelete" );
-		
+
 		$wgOut->addHtml(
 			wfElement( 'textarea', array(
 					'readonly' => true,
@@ -753,7 +836,7 @@
 				$this->mFileVersions );
 			
 			if( $ok ) {
-				$skin =& $wgUser->getSkin();
+				$skin = $wgUser->getSkin();
 				$link = $skin->makeKnownLinkObj( $this->mTargetObj );
 				$wgOut->addHtml( wfMsgWikiHtml( 'undeletedpage', $link ) );
 				return true;
diff -NaurB -x .svn includes/SpecialUnlockdb.php /srv/web/fp014/source/includes/SpecialUnlockdb.php
--- includes/SpecialUnlockdb.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUnlockdb.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -31,8 +30,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class DBUnlockForm {
 	function showForm( $err )
diff -NaurB -x .svn includes/SpecialUnusedcategories.php /srv/web/fp014/source/includes/SpecialUnusedcategories.php
--- includes/SpecialUnusedcategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUnusedcategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UnusedCategoriesPage extends QueryPage {
 
@@ -22,7 +20,7 @@
 
 	function getSQL() {
 		$NScat = NS_CATEGORY;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $categorylinks, $page ) = $dbr->tableNamesN( 'categorylinks', 'page' );
 		return "SELECT 'Unusedcategories' as type,
 				{$NScat} as namespace, page_title as title, page_title as value
diff -NaurB -x .svn includes/SpecialUnusedimages.php /srv/web/fp014/source/includes/SpecialUnusedimages.php
--- includes/SpecialUnusedimages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUnusedimages.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,13 +1,11 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UnusedimagesPage extends QueryPage {
 
@@ -22,7 +20,7 @@
 
 	function getSQL() {
 		global $wgCountCategorizedImagesAsUsed;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		if ( $wgCountCategorizedImagesAsUsed ) {
 			list( $page, $image, $imagelinks, $categorylinks ) = $dbr->tableNamesN( 'page', 'image', 'imagelinks', 'categorylinks' );
diff -NaurB -x .svn includes/SpecialUnusedtemplates.php /srv/web/fp014/source/includes/SpecialUnusedtemplates.php
--- includes/SpecialUnusedtemplates.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUnusedtemplates.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 
 /**
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  *
  * @author Rob Church <robchur@gmail.com>
  * @copyright © 2006 Rob Church
@@ -10,8 +9,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class UnusedtemplatesPage extends QueryPage {
@@ -22,7 +20,7 @@
 	function sortDescending() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $templatelinks) = $dbr->tableNamesN( 'page', 'templatelinks' );
 		$sql = "SELECT 'Unusedtemplates' AS type, page_title AS title,
 			page_namespace AS namespace, 0 AS value
diff -NaurB -x .svn includes/SpecialUnwatchedpages.php /srv/web/fp014/source/includes/SpecialUnwatchedpages.php
--- includes/SpecialUnwatchedpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUnwatchedpages.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * A special page that displays a list of pages that are not on anyones watchlist
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -11,8 +10,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UnwatchedpagesPage extends QueryPage {
 
@@ -21,7 +19,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $page, $watchlist ) = $dbr->tableNamesN( 'page', 'watchlist' );
 		$mwns = NS_MEDIAWIKI;
 		return
diff -NaurB -x .svn includes/SpecialUploadMogile.php /srv/web/fp014/source/includes/SpecialUploadMogile.php
--- includes/SpecialUploadMogile.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUploadMogile.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -19,7 +18,6 @@
 	$form->execute();
 }
 
-/** @package MediaWiki */
 class UploadFormMogile extends UploadForm {
 	/**
 	 * Move the uploaded file from its temporary location to the final
diff -NaurB -x .svn includes/SpecialUpload.php /srv/web/fp014/source/includes/SpecialUpload.php
--- includes/SpecialUpload.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUpload.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 
@@ -17,8 +16,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UploadForm {
 	/**#@+
@@ -126,11 +124,11 @@
 		$this->mOname          = array_pop( explode( '/', $url ) );
 		$this->mSessionKey     = false;
 		$this->mStashed        = false;
-		
+
 		// PHP won't auto-cleanup the file
 		$this->mRemoveTempFile = file_exists( $local_file );
 	}
-	
+
 	/**
 	 * Safe copy from URL
 	 * Returns true if there was an error, false otherwise
@@ -158,19 +156,19 @@
 			$wgOut->errorPage( 'upload-file-error', 'upload-file-error-text');
 			return true;
 		}
-		
+
 		$ch = curl_init();
 		curl_setopt( $ch, CURLOPT_HTTP_VERSION, 1.0); # Probably not needed, but apparently can work around some bug
 		curl_setopt( $ch, CURLOPT_TIMEOUT, 10); # 10 seconds timeout
 		curl_setopt( $ch, CURLOPT_LOW_SPEED_LIMIT, 512); # 0.5KB per second minimum transfer speed
-		curl_setopt( $ch, CURLOPT_URL, $url); 
+		curl_setopt( $ch, CURLOPT_URL, $url);
 		curl_setopt( $ch, CURLOPT_WRITEFUNCTION, array( $this, 'uploadCurlCallback' ) );
 		curl_exec( $ch );
 		$error = curl_errno( $ch ) ? true : false;
 		$errornum =  curl_errno( $ch );
 		// if ( $error ) print curl_error ( $ch ) ; # Debugging output
 		curl_close( $ch );
-		
+
 		fclose( $this->mUploadTempFile );
 		unset( $this->mUploadTempFile );
 		if( $error ) {
@@ -180,10 +178,10 @@
 			else
 				$wgOut->errorPage( "upload-curl-error$errornum", "upload-curl-error$errornum-text" );
 		}
-		
+
 		return $error;
 	}
-	
+
 	/**
 	 * Callback function for CURL-based web transfer
 	 * Write data to file unless we've passed the length limit;
@@ -200,7 +198,7 @@
 		fwrite( $this->mUploadTempFile, $data );
 		return $length;
 	}
-	
+
 	/**
 	 * Start doing stuff
 	 * @access public
@@ -298,13 +296,12 @@
 		 * only the final one for the whitelist.
 		 */
 		list( $partname, $ext ) = $this->splitExtensions( $basename );
-		
+
 		if( count( $ext ) ) {
 			$finalExt = $ext[count( $ext ) - 1];
 		} else {
 			$finalExt = '';
 		}
-		$fullExt = implode( '.', $ext );
 
 		# If there was more than one "extension", reassemble the base
 		# filename to prevent bogus complaints about length
@@ -335,7 +332,7 @@
 		 * If the image is protected, non-sysop users won't be able
 		 * to modify it by uploading a new revision.
 		 */
-		if( !$nt->userCanEdit() ) {
+		if( !$nt->userCan( 'edit' ) ) {
 			return $this->uploadError( wfMsgWikiHtml( 'protectedpage' ) );
 		}
 
@@ -402,7 +399,7 @@
 
 			global $wgUploadSizeWarning;
 			if ( $wgUploadSizeWarning && ( $this->mUploadSize > $wgUploadSizeWarning ) ) {
-				$skin =& $wgUser->getSkin();
+				$skin = $wgUser->getSkin();
 				$wsize = $skin->formatSize( $wgUploadSizeWarning );
 				$asize = $skin->formatSize( $this->mUploadSize );
 				$warning .= '<li>' . wfMsgHtml( 'large-file', $wsize, $asize ) . '</li>';
@@ -482,7 +479,7 @@
 	 */
 	function saveUploadedFile( $saveName, $tempName, $useRename = false ) {
 		global $wgOut, $wgAllowCopyUploads;
-		
+
 		if ( !$useRename AND $wgAllowCopyUploads AND $this->mSourceType == 'web' ) $useRename = true;
 
 		$fname= "SpecialUpload::saveUploadedFile";
@@ -491,7 +488,7 @@
 		$archive = wfImageArchiveDir( $saveName );
 		if ( !is_dir( $dest ) ) wfMkdirParents( $dest );
 		if ( !is_dir( $archive ) ) wfMkdirParents( $archive );
-		
+
 		$this->mSavedFile = "{$dest}/{$saveName}";
 
 		if( is_file( $this->mSavedFile ) ) {
@@ -725,7 +722,7 @@
 			  "<span class='error'>{$msg}</span>\n" );
 		}
 		$wgOut->addHTML( '<div id="uploadtext">' );
-		$wgOut->addWikiText( wfMsg( 'uploadtext' ) );
+		$wgOut->addWikiText( wfMsgNoTrans( 'uploadtext', $this->mDestFile ) );
 		$wgOut->addHTML( '</div>' );
 
 		$sourcefilename = wfMsgHtml( 'sourcefilename' );
@@ -753,19 +750,19 @@
 
 		// Prepare form for upload or upload/copy
 		if( $wgAllowCopyUploads && $wgUser->isAllowed( 'upload_by_url' ) ) {
-			$filename_form = 
-				"<input type='radio' id='wpSourceTypeFile' name='wpSourceType' value='file' onchange='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\")' checked />" . 
-				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' onfocus='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\");toggle_element_check(\"wpSourceTypeFile\",\"wpSourceTypeURL\")'" . 
+			$filename_form =
+				"<input type='radio' id='wpSourceTypeFile' name='wpSourceType' value='file' onchange='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\")' checked />" .
+				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' onfocus='toggle_element_activation(\"wpUploadFileURL\",\"wpUploadFile\");toggle_element_check(\"wpSourceTypeFile\",\"wpSourceTypeURL\")'" .
 				($this->mDestFile?"":"onchange='fillDestFilename(\"wpUploadFile\")' ") . "size='40' />" .
 				wfMsgHTML( 'upload_source_file' ) . "<br/>" .
 				"<input type='radio' id='wpSourceTypeURL' name='wpSourceType' value='web' onchange='toggle_element_activation(\"wpUploadFile\",\"wpUploadFileURL\")' />" .
-				"<input tabindex='1' type='text' name='wpUploadFileURL' id='wpUploadFileURL' onfocus='toggle_element_activation(\"wpUploadFile\",\"wpUploadFileURL\");toggle_element_check(\"wpSourceTypeURL\",\"wpSourceTypeFile\")'" . 
+				"<input tabindex='1' type='text' name='wpUploadFileURL' id='wpUploadFileURL' onfocus='toggle_element_activation(\"wpUploadFile\",\"wpUploadFileURL\");toggle_element_check(\"wpSourceTypeURL\",\"wpSourceTypeFile\")'" .
 				($this->mDestFile?"":"onchange='fillDestFilename(\"wpUploadFileURL\")' ") . "size='40' DISABLED />" .
 				wfMsgHtml( 'upload_source_url' ) ;
 		} else {
-			$filename_form = 
-				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' " . 
-				($this->mDestFile?"":"onchange='fillDestFilename(\"wpUploadFile\")' ") . 
+			$filename_form =
+				"<input tabindex='1' type='file' name='wpUploadFile' id='wpUploadFile' " .
+				($this->mDestFile?"":"onchange='fillDestFilename(\"wpUploadFile\")' ") .
 				"size='40' />" .
 				"<input type='hidden' name='wpSourceType' value='file' />" ;
 		}
@@ -817,7 +814,7 @@
 			$copystatus =  htmlspecialchars( $this->mUploadCopyStatus );
 			$filesource = wfMsgHtml ( 'filesource' );
 			$uploadsource = htmlspecialchars( $this->mUploadSource );
-			
+
 			$wgOut->addHTML( "
 			        <td align='right' nowrap='nowrap'><label for='wpUploadCopyStatus'>$filestatus:</label></td>
 			        <td><input tabindex='5' type='text' name='wpUploadCopyStatus' id='wpUploadCopyStatus' value=\"$copystatus\" size='40' /></td>
@@ -1255,6 +1252,4 @@
 	}
 
 }
-	
-
 ?>
diff -NaurB -x .svn includes/SpecialUserlogin.php /srv/web/fp014/source/includes/SpecialUserlogin.php
--- includes/SpecialUserlogin.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUserlogin.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -21,8 +20,7 @@
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 class LoginForm {
@@ -37,7 +35,7 @@
 	const RESET_PASS = 7;
 
 	var $mName, $mPassword, $mRetype, $mReturnTo, $mCookieCheck, $mPosted;
-	var $mAction, $mCreateaccount, $mCreateaccountMail, $mMailmypassword;
+	var $mAction, $mCreateaccount, $mCreateaccountMail, $mCreateMessage, $mMailmypassword;
 	var $mLoginattempt, $mRemember, $mEmail, $mDomain, $mLanguage;
 
 	/**
@@ -59,6 +57,7 @@
 		$this->mCreateaccount = $request->getCheck( 'wpCreateaccount' );
 		$this->mCreateaccountMail = $request->getCheck( 'wpCreateaccountMail' )
 		                            && $wgEnableEmail;
+		$this->mCreateMessage = htmlspecialchars( $request->getText( 'wpCreateMessage' ) );
 		$this->mMailmypassword = $request->getCheck( 'wpMailmypassword' )
 		                         && $wgEnableEmail;
 		$this->mLoginattempt = $request->getCheck( 'wpLoginattempt' );
@@ -96,7 +95,7 @@
 			if( $this->mCreateaccount ) {
 				return $this->addNewAccount();
 			} else if ( $this->mCreateaccountMail ) {
-				return $this->addNewAccountMailPassword();
+				return $this->addNewAccountMailPassword($this->mCreateMessage);
 			} else if ( $this->mMailmypassword ) {
 				return $this->mailPassword();
 			} else if ( ( 'submitlogin' == $this->mAction ) || $this->mLoginattempt ) {
@@ -109,7 +108,7 @@
 	/**
 	 * @private
 	 */
-	function addNewAccountMailPassword() {
+	function addNewAccountMailPassword($message = '') {
 		global $wgOut;
 
 		if ('' == $this->mEmail) {
@@ -126,7 +125,7 @@
 		// Wipe the initial password and mail a temporary one
 		$u->setPassword( null );
 		$u->saveSettings();
-		$result = $this->mailPasswordInternal( $u, false );
+		$result = $this->mailPasswordInternal( $u, false, $message);
 
 		wfRunHooks( 'AddNewAccount', array( $u ) );
 
@@ -149,12 +148,12 @@
 	 */
 	function addNewAccount() {
 		global $wgUser, $wgEmailAuthentication;
-		
+
 		# Create the account and abort if there's a problem doing so
 		$u = $this->addNewAccountInternal();
 		if( $u == NULL )
 			return;
-			
+
 		# If we showed up language selection links, and one was in use, be
 		# smart (and sensible) and save that language as the user's preference
 		global $wgLoginLanguageSelector;
@@ -286,15 +285,11 @@
 			}
 		}
 
-		if( !$wgAuth->addUser( $u, $this->mPassword ) ) {
+		if( !$wgAuth->addUser( $u, $this->mPassword, $this->mEmail, $this->mRealName ) ) {
 			$this->mainLoginForm( wfMsg( 'externaldberror' ) );
 			return false;
 		}
 
-		# Update user count
-		$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
-		$ssUpdate->doUpdate();
-
 		return $this->initUser( $u );
 	}
 
@@ -319,6 +314,10 @@
 		$u->setOption( 'rememberpassword', $this->mRemember ? 1 : 0 );
 		$u->saveSettings();
 
+		# Update user count
+		$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
+		$ssUpdate->doUpdate();
+
 		return $u;
 	}
 
@@ -383,7 +382,7 @@
 				if( !$u->isEmailConfirmed() ) {
 					$u->confirmEmail();
 				}
-				
+
 				// At this point we just return an appropriate code
 				// indicating that the UI should show a password
 				// reset form; bot interfaces etc will probably just
@@ -393,14 +392,14 @@
 			} else {
 				return '' == $this->mPassword ? self::EMPTY_PASS : self::WRONG_PASS;
 			}
-		} else {	
+		} else {
 			$wgAuth->updateUser( $u );
 			$wgUser = $u;
 
 			return self::SUCCESS;
 		}
 	}
-	
+
 	function processLogin() {
 		global $wgUser, $wgAuth;
 
@@ -446,7 +445,7 @@
 				wfDebugDieBacktrace( "Unhandled case value" );
 		}
 	}
-	
+
 	function resetLoginForm( $error ) {
 		global $wgOut;
 		$wgOut->addWikiText( "<div class=\"errorbox\">$error</div>" );
@@ -459,19 +458,19 @@
 	 */
 	function mailPassword() {
 		global $wgUser, $wgOut, $wgAuth;
-		
+
 		if( !$wgAuth->allowPasswordChange() ) {
 			$this->mainLoginForm( wfMsg( 'resetpass_forbidden' ) );
 			return;
 		}
-		
+
 		# Check against blocked IPs
 		# fixme -- should we not?
 		if( $wgUser->isBlocked() ) {
 			$this->mainLoginForm( wfMsg( 'blocked-mailpassword' ) );
 			return;
 		}
-		
+
 		# Check against the rate limiter
 		if( $wgUser->pingLimiter( 'mailpassword' ) ) {
 			$wgOut->rateLimited();
@@ -496,7 +495,7 @@
 		if ( $u->isPasswordReminderThrottled() ) {
 			global $wgPasswordReminderResendTime;
 			# Round the time in hours to 3 d.p., in case someone is specifying minutes or seconds.
-			$this->mainLoginForm( wfMsg( 'throttled-mailpassword', 
+			$this->mainLoginForm( wfMsg( 'throttled-mailpassword',
 				round( $wgPasswordReminderResendTime, 3 ) ) );
 			return;
 		}
@@ -514,7 +513,7 @@
 	 * @return mixed true on success, WikiError on failure
 	 * @private
 	 */
-	function mailPasswordInternal( $u, $throttle = true ) {
+	function mailPasswordInternal( $u, $throttle = true, $message = '' ) {
 		global $wgCookiePath, $wgCookieDomain, $wgCookiePrefix, $wgCookieSecure;
 		global $wgServer, $wgScript;
 
@@ -534,6 +533,10 @@
 
 		$m = wfMsg( 'passwordremindertext', $ip, $u->getName(), $np, $wgServer . $wgScript );
 
+		if($message != '') {
+			$m = "$message\n\n$m";
+		}
+
 		$result = $u->sendMail( wfMsg( 'passwordremindertitle' ), $m );
 		return $result;
 	}
@@ -587,7 +590,7 @@
 		# haven't bothered to log out before trying to create an account to 
 		# evade it, but we'll leave that to their guilty conscience to figure
 		# out.
-		
+
 		$wgOut->setPageTitle( wfMsg( 'cantcreateaccounttitle' ) );
 		$wgOut->setRobotpolicy( 'noindex,nofollow' );
 		$wgOut->setArticleRelated( false );
@@ -642,7 +645,7 @@
 			$q .= $returnto;
 			$linkq .= $returnto;
 		}
-		
+
 		# Pass any language selection on to the mode switch link
 		if( $wgLoginLanguageSelector && $this->mLanguage )
 			$linkq .= '&uselang=' . $this->mLanguage;
@@ -656,7 +659,7 @@
 			$template->set( 'link', wfMsgHtml( $linkmsg, $link ) );
 		else
 			$template->set( 'link', '' );
-		
+
 		$template->set( 'header', '' );
 		$template->set( 'name', $this->mName );
 		$template->set( 'password', $this->mPassword );
@@ -673,14 +676,14 @@
 		$template->set( 'useemail', $wgEnableEmail );
 		$template->set( 'canreset', $wgAuth->allowPasswordChange() );
 		$template->set( 'remember', $wgUser->getOption( 'rememberpassword' ) or $this->mRemember  );
-				
+
 		# Prepare language selection links as needed
 		if( $wgLoginLanguageSelector ) {
 			$template->set( 'languages', $this->makeLanguageSelector() );
 			if( $this->mLanguage )
 				$template->set( 'uselang', $this->mLanguage );
 		}
-		
+
 		// Give authentication and captcha plugins a chance to modify the form
 		$wgAuth->modifyUITemplate( $template );
 		if ( $this->mType == 'signup' ) {
@@ -694,7 +697,7 @@
 		$wgOut->setArticleRelated( false );
 		$wgOut->addTemplate( $template );
 	}
-	
+
 	/**
 	 * @private
 	 */
@@ -756,7 +759,7 @@
 
 		$wgOut->addWikiText( wfMsg( 'acct_creation_throttle_hit', $limit ) );
 	}
-	
+
 	/**
 	 * Produce a bar of links which allow the user to select another language
 	 * during login/registration but retain "returnto"
@@ -778,7 +781,7 @@
 			return '';
 		}
 	}
-	
+
 	/**
 	 * Create a language selector link for a particular language
 	 * Links back to this page preserving type and returnto
@@ -794,9 +797,8 @@
 			$attr[] = 'type=signup';
 		if( $this->mReturnTo )
 			$attr[] = 'returnto=' . $this->mReturnTo;
-		$skin =& $wgUser->getSkin();
+		$skin = $wgUser->getSkin();
 		return $skin->makeKnownLinkObj( $self, htmlspecialchars( $text ), implode( '&', $attr ) );
 	}
-	
 }
 ?>
diff -NaurB -x .svn includes/SpecialUserlogout.php /srv/web/fp014/source/includes/SpecialUserlogout.php
--- includes/SpecialUserlogout.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUserlogout.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
diff -NaurB -x .svn includes/SpecialUserrights.php /srv/web/fp014/source/includes/SpecialUserrights.php
--- includes/SpecialUserrights.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialUserrights.php	2007-02-01 01:03:18.000000000 +0000
@@ -3,8 +3,7 @@
 /**
  * Special page to allow managing user group membership
  *
- * @package MediaWiki
- * @subpackage Special pages
+ * @addtogroup Special pages
  * @todo This code is disgusting and needs a total rewrite
  */
 
@@ -20,8 +19,7 @@
 
 /**
  * A class to manage user levels rights.
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class UserrightsForm extends HTMLForm {
 	var $mPosted, $mRequest, $mSaveprefs;
diff -NaurB -x .svn includes/SpecialVersion.php /srv/web/fp014/source/includes/SpecialVersion.php
--- includes/SpecialVersion.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialVersion.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**#@+
  * Give information about the version of MediaWiki, PHP, the DB and extensions
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @bug 2019, 4531
  *
@@ -50,10 +49,7 @@
 	 */
 	function MediaWikiCredits() {
 		$version = self::getVersion();
-		$dbr =& wfGetDB( DB_SLAVE );
-
-		global $wgLanguageNames, $wgLanguageCode;
-		$mwlang = $wgLanguageNames[$wgLanguageCode];
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$ret =
 		"__NOTOC__
@@ -110,21 +106,19 @@
 		$out .= wfOpenElement('table', array('id' => 'sv-ext') );
 
 		foreach ( $extensionTypes as $type => $text ) {
-			if ( count( @$wgExtensionCredits[$type] ) ) {
+			if ( isset ( $wgExtensionCredits[$type] ) && count ( $wgExtensionCredits[$type] ) ) {
 				$out .= $this->openExtType( $text );
 
 				usort( $wgExtensionCredits[$type], array( $this, 'compare' ) );
 
 				foreach ( $wgExtensionCredits[$type] as $extension ) {
-					wfSuppressWarnings();
 					$out .= $this->formatCredits(
-						$extension['name'],
-						$extension['version'],
-						$extension['author'],
-						$extension['url'],
-						$extension['description']
+						isset ( $extension['name'] )        ? $extension['name']        : '',
+						isset ( $extension['version'] )     ? $extension['version']     : null,
+						isset ( $extension['author'] )      ? $extension['author']      : '',
+						isset ( $extension['url'] )         ? $extension['url']         : null,
+						isset ( $extension['description'] ) ? $extension['description'] : ''
 					);
-					wfRestoreWarnings();
 				}
 			}
 		}
@@ -195,7 +189,7 @@
 
 			foreach ($myWgHooks as $hook => $hooks)
 				$ret .= "<tr><td>$hook</td><td>" . $this->listToText( $hooks ) . "</td></tr>\n";
-			
+
 			$ret .= '</table>';
 			return $ret;
 		} else
@@ -292,10 +286,7 @@
 				return false;
 			}
 
-			// SimpleXml whines about the xmlns...
-			wfSuppressWarnings();
-			$xml = simplexml_load_file( $entries );
-			wfRestoreWarnings();
+			$xml = simplexml_load_file( $entries, "SimpleXMLElement", LIBXML_NOWARNING );
 
 			if( $xml ) {
 				foreach( $xml->entry as $entry ) {
diff -NaurB -x .svn includes/SpecialWantedcategories.php /srv/web/fp014/source/includes/SpecialWantedcategories.php
--- includes/SpecialWantedcategories.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialWantedcategories.php	2007-02-01 01:03:18.000000000 +0000
@@ -2,8 +2,7 @@
 /**
  * A querypage to list the most wanted categories
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  *
  * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
  * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
@@ -11,8 +10,7 @@
  */
 
 /**
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class WantedCategoriesPage extends QueryPage {
 
@@ -21,7 +19,7 @@
 	function isSyndicated() { return false; }
 
 	function getSQL() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		list( $categorylinks, $page ) = $dbr->tableNamesN( 'categorylinks', 'page' );
 		$name = $dbr->addQuotes( $this->getName() );
 		return
diff -NaurB -x .svn includes/SpecialWantedpages.php /srv/web/fp014/source/includes/SpecialWantedpages.php
--- includes/SpecialWantedpages.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialWantedpages.php	2007-02-01 01:04:18.000000000 +0000
@@ -1,14 +1,12 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 class WantedPagesPage extends QueryPage {
 	var $nlinks;
@@ -30,7 +28,7 @@
 	function getSQL() {
 		global $wgWantedPagesThreshold;
 		$count = $wgWantedPagesThreshold - 1;
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$pagelinks = $dbr->tableName( 'pagelinks' );
 		$page      = $dbr->tableName( 'page' );
 		return
@@ -39,13 +37,9 @@
 			        pl_title AS title,
 			        COUNT(*) AS value
 			 FROM $pagelinks
-			 LEFT JOIN $page AS pg1
-			 ON pl_namespace = pg1.page_namespace AND pl_title = pg1.page_title
-			 LEFT JOIN $page AS pg2
-			 ON pl_from = pg2.page_id
-			 WHERE pg1.page_namespace IS NULL
-			 AND pl_namespace NOT IN ( 2, 3 )
-			 AND pg2.page_namespace != 8
+			 LEFT JOIN $page
+			 ON pl_namespace=page_namespace AND pl_title=page_title
+			 WHERE page_namespace IS NULL
 			 GROUP BY 1,2,3
 			 HAVING COUNT(*) > $count";
 	}
diff -NaurB -x .svn includes/SpecialWatchlist.php /srv/web/fp014/source/includes/SpecialWatchlist.php
--- includes/SpecialWatchlist.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialWatchlist.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -16,12 +15,12 @@
  * @param $par Parameter passed to the page
  */
 function wfSpecialWatchlist( $par ) {
-	global $wgUser, $wgOut, $wgLang, $wgMemc, $wgRequest, $wgContLang;
+	global $wgUser, $wgOut, $wgLang, $wgRequest, $wgContLang;
 	global $wgRCShowWatchingUsers, $wgEnotifWatchlist, $wgShowUpdatedMarker;
 	global $wgEnotifWatchlist;
 	$fname = 'wfSpecialWatchlist';
 
-	$skin =& $wgUser->getSkin();
+	$skin = $wgUser->getSkin();
 	$specialTitle = SpecialPage::getTitleFor( 'Watchlist' );
 	$wgOut->setRobotPolicy( 'noindex,nofollow' );
 
@@ -104,7 +103,7 @@
 		$wgOut->addHTML( "</p>\n<p>" . wfMsg( 'wldone' ) . "</p>\n" );
 	}
 
-	$dbr =& wfGetDB( DB_SLAVE, 'watchlist' );
+	$dbr = wfGetDB( DB_SLAVE, 'watchlist' );
 	list( $page, $watchlist, $recentchanges ) = $dbr->tableNamesN( 'page', 'watchlist', 'recentchanges' );
 
 	$sql = "SELECT COUNT(*) AS n FROM $watchlist WHERE wl_user=$uid";
@@ -353,6 +352,18 @@
 
 	/* End bottom header */
 
+	/* Do link batch query */
+	$linkBatch = new LinkBatch;
+	while ( $row = $dbr->fetchObject( $res ) ) {
+		$userNameUnderscored = str_replace( ' ', '_', $row->rc_user_text );
+		if ( $row->rc_user != 0 ) {
+			$linkBatch->add( NS_USER, $userNameUnderscored );
+		}
+		$linkBatch->add( NS_USER_TALK, $userNameUnderscored );
+	}
+	$linkBatch->execute();
+	$dbr->dataSeek( $res, 0 );
+
 	$list = ChangesList::newFromUser( $wgUser );
 
 	$s = $list->beginRecentChangesList();
@@ -435,7 +446,7 @@
  * @return integer
  */
 function wlCountItems( &$user, $talk = true ) {
-	$dbr =& wfGetDB( DB_SLAVE, 'watchlist' );
+	$dbr = wfGetDB( DB_SLAVE, 'watchlist' );
 
 	# Fetch the raw count
 	$res = $dbr->select( 'watchlist', 'COUNT(*) AS count', array( 'wl_user' => $user->mId ), 'wlCountItems' );
@@ -471,7 +482,7 @@
 			# See if we're clearing or confirming
 			if( $request->wasPosted() && $wgUser->matchEditToken( $request->getText( 'token' ), 'clearwatchlist' ) ) {
 				# Clearing, so do it and report the result
-				$dbw =& wfGetDB( DB_MASTER );
+				$dbw = wfGetDB( DB_MASTER );
 				$dbw->delete( 'watchlist', array( 'wl_user' => $wgUser->mId ), 'wlHandleClear' );
 				$out->addWikiText( wfMsgExt( 'watchlistcleardone', array( 'parsemag', 'escape'), $wgLang->formatNum( $count ) ) );
 				$out->returnToMain();
diff -NaurB -x .svn includes/SpecialWhatlinkshere.php /srv/web/fp014/source/includes/SpecialWhatlinkshere.php
--- includes/SpecialWhatlinkshere.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SpecialWhatlinkshere.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,8 +1,7 @@
 <?php
 /**
  *
- * @package MediaWiki
- * @subpackage SpecialPage
+ * @addtogroup SpecialPage
  */
 
 /**
@@ -23,7 +22,7 @@
 	function WhatLinksHerePage( &$request, $par = null ) {
 		global $wgUser;
 		$this->request =& $request;
-		$this->skin =& $wgUser->getSkin();
+		$this->skin = $wgUser->getSkin();
 		$this->par = $par;
 	}
 
@@ -74,7 +73,7 @@
 		global $wgOut;
 		$fname = 'WhatLinksHerePage::showIndirectLinks';
 
-		$dbr =& wfGetDB( DB_READ );
+		$dbr = wfGetDB( DB_READ );
 
 		// Some extra validation
 		$from = intval( $from );
diff -NaurB -x .svn includes/SquidUpdate.php /srv/web/fp014/source/includes/SquidUpdate.php
--- includes/SquidUpdate.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/SquidUpdate.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,17 +1,15 @@
 <?php
 /**
  * See deferred.txt
- * @package MediaWiki
  */
 
 /**
  *
- * @package MediaWiki
  */
 class SquidUpdate {
 	var $urlArr, $mMaxTitles;
 
-	function SquidUpdate( $urlArr = Array(), $maxTitles = false ) {
+	function __construct( $urlArr = Array(), $maxTitles = false ) {
 		global $wgMaxSquidPurgeTitles;
 		if ( $maxTitles === false ) {
 			$this->mMaxTitles = $wgMaxSquidPurgeTitles;
@@ -29,7 +27,7 @@
 		wfProfileIn( $fname );
 
 		# Get a list of URLs linking to this page
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( array( 'links', 'page' ),
 			array( 'page_namespace', 'page_title' ),
 			array(
diff -NaurB -x .svn includes/templates/Userlogin.php /srv/web/fp014/source/includes/templates/Userlogin.php
--- includes/templates/Userlogin.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/templates/Userlogin.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
- * @package MediaWiki
- * @subpackage Templates
+ * @addtogroup Templates
  */
 if( !defined( 'MEDIAWIKI' ) ) die( -1 );
 
@@ -10,8 +9,7 @@
 
 /**
  * HTML template for Special:Userlogin form
- * @package MediaWiki
- * @subpackage Templates
+ * @addtogroup Templates
  */
 class UserloginTemplate extends QuickTemplate {
 	function execute() {
diff -NaurB -x .svn includes/Title.php /srv/web/fp014/source/includes/Title.php
--- includes/Title.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Title.php	2007-02-01 01:04:18.000000000 +0000
@@ -2,7 +2,6 @@
 /**
  * See title.txt
  *
- * @package MediaWiki
  */
 
 /** */
@@ -17,12 +16,14 @@
 # reset the cache.
 define( 'MW_TITLECACHE_MAX', 1000 );
 
+# Constants for pr_cascade bitfield
+define( 'CASCADE', 1 );
+
 /**
  * Title class
  * - Represents a title, which may contain an interwiki designation or namespace
  * - Can fetch various kinds of data from the database, albeit inefficiently.
  *
- * @package MediaWiki
  */
 class Title {
 	/**
@@ -41,21 +42,24 @@
 	 * @private
 	 */
 
-	var $mTextform;           # Text form (spaces not underscores) of the main part
-	var $mUrlform;            # URL-encoded form of the main part
-	var $mDbkeyform;          # Main part with underscores
-	var $mNamespace;          # Namespace index, i.e. one of the NS_xxxx constants
-	var $mInterwiki;          # Interwiki prefix (or null string)
-	var $mFragment;           # Title fragment (i.e. the bit after the #)
-	var $mArticleID;          # Article ID, fetched from the link cache on demand
-	var $mLatestID;         # ID of most recent revision
-	var $mRestrictions;       # Array of groups allowed to edit this article
-	                        # Only null or "sysop" are supported
-	var $mRestrictionsLoaded; # Boolean for initialisation on demand
-	var $mPrefixedText;       # Text form including namespace/interwiki, initialised on demand
-	var $mDefaultNamespace;   # Namespace index when there is no namespace
-	                    # Zero except in {{transclusion}} tags
-	var $mWatched;      # Is $wgUser watching this page? NULL if unfilled, accessed through userIsWatching()
+	var $mTextform;           	# Text form (spaces not underscores) of the main part
+	var $mUrlform;            	# URL-encoded form of the main part
+	var $mDbkeyform;          	# Main part with underscores
+	var $mNamespace;          	# Namespace index, i.e. one of the NS_xxxx constants
+	var $mInterwiki;          	# Interwiki prefix (or null string)
+	var $mFragment;           	# Title fragment (i.e. the bit after the #)
+	var $mArticleID;          	# Article ID, fetched from the link cache on demand
+	var $mLatestID;         	# ID of most recent revision
+	var $mRestrictions;       	# Array of groups allowed to edit this article
+	var $mCascadeRestriction;	# Cascade restrictions on this page to included templates and images?
+	var $mRestrictionsExpiry;	# When do the restrictions on this page expire?
+	var $mHasCascadingRestrictions;	# Are cascading restrictions in effect on this page?
+	var $mCascadeRestrictionSources;# Where are the cascading restrictions coming from on this page?
+	var $mRestrictionsLoaded; 	# Boolean for initialisation on demand
+	var $mPrefixedText;       	# Text form including namespace/interwiki, initialised on demand
+	var $mDefaultNamespace;   	# Namespace index when there is no namespace
+	                    		# Zero except in {{transclusion}} tags
+	var $mWatched;      		# Is $wgUser watching this page? NULL if unfilled, accessed through userIsWatching()
 	/**#@-*/
 
 
@@ -63,7 +67,7 @@
 	 * Constructor
 	 * @private
 	 */
-	/* private */ function Title() {
+	/* private */ function __construct() {
 		$this->mInterwiki = $this->mUrlform =
 		$this->mTextform = $this->mDbkeyform = '';
 		$this->mArticleID = -1;
@@ -75,6 +79,7 @@
 		$this->mDefaultNamespace = NS_MAIN;
 		$this->mWatched = NULL;
 		$this->mLatestID = false;
+		$this->mOldRestrictions = false;
 	}
 
 	/**
@@ -192,7 +197,7 @@
 	 */
 	public static function newFromID( $id ) {
 		$fname = 'Title::newFromID';
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$row = $dbr->selectRow( 'page', array( 'page_namespace', 'page_title' ),
 			array( 'page_id' => $id ), $fname );
 		if ( $row !== false ) {
@@ -207,7 +212,7 @@
 	 * Make an array of titles from an array of IDs 
 	 */
 	function newFromIDs( $ids ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'page', array( 'page_namespace', 'page_title' ),
 			'page_id IN (' . $dbr->makeList( $ids ) . ')', __METHOD__ );
 
@@ -280,8 +285,6 @@
 	 * @param string $text the redirect title text
 	 * @return Title the new object, or NULL if the text is not a
 	 *	valid redirect
-	 * @static
-	 * @access public
 	 */
 	public static function newFromRedirect( $text ) {
 		$mwRedir = MagicWord::get( 'redirect' );
@@ -320,7 +323,7 @@
 	 */
 	function nameOf( $id ) {
 		$fname = 'Title::nameOf';
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 
 		$s = $dbr->selectRow( 'page', array( 'page_namespace','page_title' ),  array( 'page_id' => $id ), $fname );
 		if ( $s === false ) { return NULL; }
@@ -349,7 +352,7 @@
 	 * @return string a stripped-down title string ready for the
 	 * 	search index
 	 */
-	/* static */ function indexTitle( $ns, $title ) {
+	public static function indexTitle( $ns, $title ) {
 		global $wgContLang;
 
 		$lc = SearchEngine::legalSearchChars() . '&#;';
@@ -413,7 +416,7 @@
 			return $s->iw_url;
 		}
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'interwiki',
 			array( 'iw_url', 'iw_local', 'iw_trans' ),
 			array( 'iw_prefix' => $key ), $fname );
@@ -528,7 +531,7 @@
 		if ( count( $titles ) == 0 ) {
 			return;
 		}
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		if ( $timestamp == '' ) {
 			$timestamp = $dbw->timestamp();
 		}
@@ -1029,8 +1032,17 @@
 	 */
 	function isProtected( $action = '' ) {
 		global $wgRestrictionLevels;
-		if ( NS_SPECIAL == $this->mNamespace ) { return true; }
-				
+
+		# Special pages have inherent protection
+		if( $this->getNamespace() == NS_SPECIAL )
+			return true;
+		
+		# Cascading protection depends on more than
+		# this page...
+		if( $this->isCascadeProtected() )
+			return true;
+
+		# Check regular protection levels				
 		if( $action == 'edit' || $action == '' ) {
 			$r = $this->getRestrictions( 'edit' );
 			foreach( $wgRestrictionLevels as $level ) {
@@ -1071,16 +1083,32 @@
 	}
 
  	/**
-	 * Can $wgUser perform $action this page?
+	 * Can $wgUser perform $action on this page?
+	 * This skips potentially expensive cascading permission checks.
+	 *
+	 * Suitable for use for nonessential UI controls in common cases, but
+	 * _not_ for functional access control.
+	 *
+	 * May provide false positives, but should never provide a false negative.
+	 *
+	 * @param string $action action that permission needs to be checked for
+	 * @return boolean
+ 	 */
+	public function quickUserCan( $action ) {
+		return $this->userCan( $action, false );
+	}
+
+ 	/**
+	 * Can $wgUser perform $action on this page?
 	 * @param string $action action that permission needs to be checked for
+	 * @param bool $doExpensiveQueries Set this to false to avoid doing unnecessary queries.
 	 * @return boolean
-	 * @private
  	 */
-	function userCan($action) {
+	public function userCan( $action, $doExpensiveQueries = true ) {
 		$fname = 'Title::userCan';
 		wfProfileIn( $fname );
 
-		global $wgUser;
+		global $wgUser, $wgNamespaceProtection;
 
 		$result = null;
 		wfRunHooks( 'userCan', array( &$this, &$wgUser, $action, &$result ) );
@@ -1093,12 +1121,16 @@
 			wfProfileOut( $fname );
 			return false;
 		}
-		// XXX: This is the code that prevents unprotecting a page in NS_MEDIAWIKI
-		// from taking effect -ævar
-		if( NS_MEDIAWIKI == $this->mNamespace &&
-		    !$wgUser->isAllowed('editinterface') ) {
-			wfProfileOut( $fname );
-			return false;
+		
+		if ( array_key_exists( $this->mNamespace, $wgNamespaceProtection ) ) {
+			$nsProt = $wgNamespaceProtection[ $this->mNamespace ];
+			if ( !is_array($nsProt) ) $nsProt = array($nsProt);
+			foreach( $nsProt as $right ) {
+				if( '' != $right && !$wgUser->isAllowed( $right ) ) {
+					wfProfileOut( $fname );
+					return false;
+				}
+			}
 		}
 
 		if( $this->mDbkeyform == '_' ) {
@@ -1116,6 +1148,17 @@
 			wfProfileOut( $fname );
 			return false;
 		}
+		
+		if ( $doExpensiveQueries && !$this->isCssJsSubpage() && $this->isCascadeProtected() ) {
+			# We /could/ use the protection level on the source page, but it's fairly ugly
+			#  as we have to establish a precedence hierarchy for pages included by multiple
+			#  cascade-protected pages. So just restrict it to people with 'protect' permission,
+			#  as they could remove the protection anyway.
+			if ( !$wgUser->isAllowed('protect') ) {
+				wfProfileOut( $fname );
+				return false;
+			}
+		}
 
 		foreach( $this->getRestrictions($action) as $right ) {
 			// Backwards compatibility, rewrite sysop -> protect
@@ -1149,28 +1192,28 @@
 	/**
 	 * Can $wgUser edit this page?
 	 * @return boolean
-	 * @access public
+	 * @deprecated use userCan('edit')
 	 */
-	function userCanEdit() {
-		return $this->userCan('edit');
+	public function userCanEdit( $doExpensiveQueries = true ) {
+		return $this->userCan( 'edit', $doExpensiveQueries );
 	}
 
 	/**
 	 * Can $wgUser create this page?
 	 * @return boolean
-	 * @access public
+	 * @deprecated use userCan('create')
 	 */
-	function userCanCreate() {
-		return $this->userCan('create');
+	public function userCanCreate( $doExpensiveQueries = true ) {
+		return $this->userCan( 'create', $doExpensiveQueries );
 	}
 
 	/**
 	 * Can $wgUser move this page?
 	 * @return boolean
-	 * @access public
+	 * @deprecated use userCan('move')
 	 */
-	function userCanMove() {
-		return $this->userCan('move');
+	public function userCanMove( $doExpensiveQueries = true ) {
+		return $this->userCan( 'move', $doExpensiveQueries );
 	}
 
 	/**
@@ -1188,9 +1231,9 @@
 	/**
 	 * Can $wgUser read this page?
 	 * @return boolean
-	 * @access public
+	 * @fixme fold these checks into userCan()
 	 */
-	function userCanRead() {
+	public function userCanRead() {
 		global $wgUser;
 
 		$result = null;
@@ -1310,33 +1353,194 @@
 	}
 
 	/**
+	 * Cascading protection: Return true if cascading restrictions apply to this page, false if not.
+	 *
+	 * @return bool If the page is subject to cascading restrictions.
+	 * @access public.
+	 */
+	function isCascadeProtected() {
+		return ( $this->getCascadeProtectionSources( false ) );
+	}
+
+	/**
+	 * Cascading protection: Get the source of any cascading restrictions on this page.
+	 *
+	 * @param $get_pages bool Whether or not to retrieve the actual pages that the restrictions have come from.
+	 * @return mixed Array of the Title objects of the pages from which cascading restrictions have come, false for none, or true if such restrictions exist, but $get_pages was not set.
+	 * @access public
+	 */
+	function getCascadeProtectionSources( $get_pages = true ) {
+		global $wgEnableCascadingProtection;
+		if (!$wgEnableCascadingProtection)
+			return false;
+
+		if ( isset( $this->mCascadeSources ) && $get_pages ) {
+			return $this->mCascadeSources;
+		} else if ( isset( $this->mHasCascadingRestrictions ) && !$get_pages ) {
+			return $this->mHasCascadingRestrictions;
+		}
+
+		wfProfileIn( __METHOD__ );
+
+		$dbr = wfGetDb( DB_SLAVE );
+
+		if ( $this->getNamespace() == NS_IMAGE ) {
+			$tables = array ('imagelinks', 'page_restrictions');
+			$where_clauses = array(
+				'il_to' => $this->getDBkey(),
+				'il_from=pr_page',
+				'pr_cascade' => 1 );
+		} else {
+			$tables = array ('templatelinks', 'page_restrictions');
+			$where_clauses = array(
+				'tl_namespace' => $this->getNamespace(),
+				'tl_title' => $this->getDBkey(),
+				'tl_from=pr_page',
+				'pr_cascade' => 1 );
+		}
+
+		if ( $get_pages ) {
+			$cols = array('pr_page', 'page_namespace', 'page_title', 'pr_expiry' );
+			$where_clauses[] = 'page_id=pr_page';
+			$tables[] = 'page';
+		} else {
+			$cols = array( 'pr_expiry' );
+		}
+
+		$res = $dbr->select( $tables, $cols, $where_clauses, __METHOD__ );
+
+		$sources = $get_pages ? array() : false;
+		$now = wfTimestampNow();
+		$purgeExpired = false;
+		
+		while( $row = $dbr->fetchObject( $res ) ) {
+			$expiry = Block::decodeExpiry( $row->pr_expiry );
+			if( $expiry > $now ) {
+				if ($get_pages) {
+					$page_id = $row->pr_page;
+					$page_ns = $row->page_namespace;
+					$page_title = $row->page_title;
+					$sources[$page_id] = Title::makeTitle($page_ns, $page_title);
+				} else {
+					$sources = true;
+				}
+			} else {
+				// Trigger lazy purge of expired restrictions from the db
+				$purgeExpired = true;
+			}
+		}
+		if( $purgeExpired ) {
+			Title::purgeExpiredRestrictions();
+		}
+
+		wfProfileOut( __METHOD__ );
+
+		if ( $get_pages ) {
+			$this->mCascadeSources = $sources;
+		} else {
+			$this->mHasCascadingRestrictions = $sources;
+		}
+
+		return $sources;
+	}
+
+	function areRestrictionsCascading() {
+		if (!$this->mRestrictionsLoaded) {
+			$this->loadRestrictions();
+		}
+
+		return $this->mCascadeRestriction;
+	}
+
+	/**
 	 * Loads a string into mRestrictions array
-	 * @param string $res restrictions in string format
+	 * @param resource $res restrictions as an SQL result.
 	 * @access public
 	 */
-	function loadRestrictions( $res ) {
+	function loadRestrictionsFromRow( $res, $oldFashionedRestrictions = NULL ) {
+		$dbr = wfGetDb( DB_SLAVE );
+
 		$this->mRestrictions['edit'] = array();
 		$this->mRestrictions['move'] = array();
-		
-		if( !$res ) {
-			# No restrictions (page_restrictions blank)
-			$this->mRestrictionsLoaded = true;
-			return;
+
+		# Backwards-compatibility: also load the restrictions from the page record (old format).
+
+		if ( $oldFashionedRestrictions == NULL ) {
+			$oldFashionedRestrictions = $dbr->selectField( 'page', 'page_restrictions', array( 'page_id' => $this->getArticleId() ), __METHOD__ );
 		}
-	
-		foreach( explode( ':', trim( $res ) ) as $restrict ) {
-			$temp = explode( '=', trim( $restrict ) );
-			if(count($temp) == 1) {
-				// old format should be treated as edit/move restriction
-				$this->mRestrictions["edit"] = explode( ',', trim( $temp[0] ) );
-				$this->mRestrictions["move"] = explode( ',', trim( $temp[0] ) );
-			} else {
-				$this->mRestrictions[$temp[0]] = explode( ',', trim( $temp[1] ) );
+
+		if ($oldFashionedRestrictions != '') {
+
+			foreach( explode( ':', trim( $oldFashionedRestrictions ) ) as $restrict ) {
+				$temp = explode( '=', trim( $restrict ) );
+				if(count($temp) == 1) {
+					// old old format should be treated as edit/move restriction
+					$this->mRestrictions["edit"] = explode( ',', trim( $temp[0] ) );
+					$this->mRestrictions["move"] = explode( ',', trim( $temp[0] ) );
+				} else {
+					$this->mRestrictions[$temp[0]] = explode( ',', trim( $temp[1] ) );
+				}
 			}
+
+			$this->mOldRestrictions = true;
+			$this->mCascadeRestriction = false;
+			$this->mRestrictionsExpiry = Block::decodeExpiry('');
+
 		}
+
+		if( $dbr->numRows( $res ) ) {
+			# Current system - load second to make them override.
+			$now = wfTimestampNow();
+			$purgeExpired = false;
+
+			while ($row = $dbr->fetchObject( $res ) ) {
+				# Cycle through all the restrictions.
+
+				// This code should be refactored, now that it's being used more generally,
+				// But I don't really see any harm in leaving it in Block for now -werdna
+				$expiry = Block::decodeExpiry( $row->pr_expiry );
+
+				// Only apply the restrictions if they haven't expired!
+				if ( !$expiry || $expiry > $now ) {
+					$this->mRestrictionsExpiry = $expiry;
+					$this->mRestrictions[$row->pr_type] = explode( ',', trim( $row->pr_level ) );
+		
+					$this->mCascadeRestriction |= $row->pr_cascade;
+				} else {
+					// Trigger a lazy purge of expired restrictions
+					$purgeExpired = true;
+				}
+			}
+		
+			if( $purgeExpired ) {
+				Title::purgeExpiredRestrictions();
+			}
+		}
+
 		$this->mRestrictionsLoaded = true;
 	}
 
+	function loadRestrictions( $oldFashionedRestrictions = NULL ) {
+		if( !$this->mRestrictionsLoaded ) {
+			$dbr = wfGetDB( DB_SLAVE );
+		
+			$res = $dbr->select( 'page_restrictions', '*',
+				array ( 'pr_page' => $this->getArticleId() ), __METHOD__ );
+
+			$this->loadRestrictionsFromRow( $res, $oldFashionedRestrictions );
+		}
+	}
+
+	/** 
+	 * Purge expired restrictions from the page_restrictions table
+	 */
+	static function purgeExpiredRestrictions() {
+		$dbw = wfGetDB( DB_MASTER );
+		$dbw->delete( 'page_restrictions',
+			array( 'pr_expiry < ' . $dbw->addQuotes( $dbw->timestamp() ) ),
+			__METHOD__ );
+	}
+
 	/**
 	 * Accessor/initialisation for mRestrictions
 	 *
@@ -1347,9 +1551,7 @@
 	function getRestrictions( $action ) {
 		if( $this->exists() ) {
 			if( !$this->mRestrictionsLoaded ) {
-				$dbr =& wfGetDB( DB_SLAVE );
-				$res = $dbr->selectField( 'page', 'page_restrictions', array( 'page_id' => $this->getArticleId() ) );
-				$this->loadRestrictions( $res );
+				$this->loadRestrictions();
 			}
 			return isset( $this->mRestrictions[$action] )
 					? $this->mRestrictions[$action]
@@ -1369,7 +1571,7 @@
 		if ( $this->getNamespace() < 0 ) {
 			$n = 0;
 		} else {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			$n = $dbr->selectField( 'archive', 'COUNT(*)', array( 'ar_namespace' => $this->getNamespace(),
 				'ar_title' => $this->getDBkey() ), $fname );
 			if( $this->getNamespace() == NS_IMAGE ) {
@@ -1386,9 +1588,8 @@
 	 * @param int $flags a bit field; may be GAID_FOR_UPDATE to select
 	 * 	for update
 	 * @return int the ID
-	 * @access public
 	 */
-	function getArticleID( $flags = 0 ) {
+	public function getArticleID( $flags = 0 ) {
 		$linkCache =& LinkCache::singleton();
 		if ( $flags & GAID_FOR_UPDATE ) {
 			$oldUpdate = $linkCache->forUpdate( true );
@@ -1406,7 +1607,7 @@
 		if ($this->mLatestID !== false)
 			return $this->mLatestID;
 
-		$db =& wfGetDB(DB_SLAVE);
+		$db = wfGetDB(DB_SLAVE);
 		return $this->mLatestID = $db->selectField( 'revision',
 			"max(rev_id)",
 			array('rev_page' => $this->getArticleID()),
@@ -1446,7 +1647,7 @@
 			return;
 		}
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$success = $dbw->update( 'page',
 			array( /* SET */
 				'page_touched' => $dbw->timestamp()
@@ -1722,9 +1923,9 @@
 		$linkCache =& LinkCache::singleton();
 
 		if ( $options ) {
-			$db =& wfGetDB( DB_MASTER );
+			$db = wfGetDB( DB_MASTER );
 		} else {
-			$db =& wfGetDB( DB_SLAVE );
+			$db = wfGetDB( DB_SLAVE );
 		}
 
 		$res = $db->select( array( 'page', $table ),
@@ -1773,9 +1974,9 @@
 	 */
 	function getBrokenLinksFrom( $options = '' ) {
 		if ( $options ) {
-			$db =& wfGetDB( DB_MASTER );
+			$db = wfGetDB( DB_MASTER );
 		} else {
-			$db =& wfGetDB( DB_SLAVE );
+			$db = wfGetDB( DB_SLAVE );
 		}
 
 		$res = $db->safeQuery(
@@ -1882,8 +2083,8 @@
 		}
 
 		if ( $auth && (
-				!$this->userCanEdit() || !$nt->userCanEdit() ||
-				!$this->userCanMove() || !$nt->userCanMove() ) ) {
+				!$this->userCan( 'edit' ) || !$nt->userCan( 'edit' ) ||
+				!$this->userCan( 'move' ) || !$nt->userCan( 'move' ) ) ) {
 			return 'protectedpage';
 		}
 
@@ -1924,7 +2125,7 @@
 		$redirid = $this->getArticleID();
 
 		# Fixing category links (those without piped 'alternate' names) to be sorted under the new title
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$categorylinks = $dbw->tableName( 'categorylinks' );
 		$sql = "UPDATE $categorylinks SET cl_sortkey=" . $dbw->addQuotes( $nt->getPrefixedText() ) .
 			" WHERE cl_from=" . $dbw->addQuotes( $pageid ) .
@@ -1949,24 +2150,24 @@
 		$u->doUpdate();
 
 		# Update site_stats
-		if ( $this->getNamespace() == NS_MAIN and $nt->getNamespace() != NS_MAIN ) {
-			# Moved out of main namespace
-			# not viewed, edited, removing
-			$u = new SiteStatsUpdate( 0, 1, -1, $pageCountChange);
-		} elseif ( $this->getNamespace() != NS_MAIN and $nt->getNamespace() == NS_MAIN ) {
-			# Moved into main namespace
-			# not viewed, edited, adding
+		if( $this->isContentPage() && !$nt->isContentPage() ) {
+			# No longer a content page
+			# Not viewed, edited, removing
+			$u = new SiteStatsUpdate( 0, 1, -1, $pageCountChange );
+		} elseif( !$this->isContentPage() && $nt->isContentPage() ) {
+			# Now a content page
+			# Not viewed, edited, adding
 			$u = new SiteStatsUpdate( 0, 1, +1, $pageCountChange );
-		} elseif ( $pageCountChange ) {
-			# Added redirect
+		} elseif( $pageCountChange ) {
+			# Redirect added
 			$u = new SiteStatsUpdate( 0, 0, 0, 1 );
-		} else{
+		} else {
+			# Nothing special
 			$u = false;
 		}
-		if ( $u ) {
+		if( $u )
 			$u->doUpdate();
-		}
-
+		
 		global $wgUser;
 		wfRunHooks( 'TitleMoveComplete', array( &$this, &$nt, &$wgUser, $pageid, $redirid ) );
 		return true;
@@ -1983,7 +2184,7 @@
 	function moveOverExistingRedirect( &$nt, $reason = '' ) {
 		global $wgUseSquid;
 		$fname = 'Title::moveOverExistingRedirect';
-		$comment = wfMsgForContent( '1movedto2', $this->getPrefixedText(), $nt->getPrefixedText() );
+		$comment = wfMsgForContent( '1movedto2_redir', $this->getPrefixedText(), $nt->getPrefixedText() );
 
 		if ( $reason ) {
 			$comment .= ": $reason";
@@ -1992,7 +2193,7 @@
 		$now = wfTimestampNow();
 		$newid = $nt->getArticleID();
 		$oldid = $this->getArticleID();
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$linkCache =& LinkCache::singleton();
 
 		# Delete the old redirect. We don't save it to history since
@@ -2001,6 +2202,8 @@
 		# a conflict on the unique namespace+title index...
 		$dbw->delete( 'page', array( 'page_id' => $newid ), $fname );
 
+		$dbw->delete( 'pagelinks', array( 'pl_from' => $newid ), $fname ); /** wikia hack */
+
 		# Save a null revision in the page's history notifying of the move
 		$nullRevision = Revision::newNullRevision( $dbw, $oldid, $comment, true );
 		$nullRevId = $nullRevision->insertOn( $dbw );
@@ -2068,7 +2271,7 @@
 
 		$newid = $nt->getArticleID();
 		$oldid = $this->getArticleID();
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$now = $dbw->timestamp();
 		$linkCache =& LinkCache::singleton();
 
@@ -2133,7 +2336,7 @@
 	function isValidMoveTarget( $nt ) {
 
 		$fname = 'Title::isValidMoveTarget';
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 
 		# Is it a redirect?
 		$id  = $nt->getArticleID();
@@ -2180,46 +2383,6 @@
 	}
 
 	/**
-	 * Create a redirect; fails if the title already exists; does
-	 * not notify RC
-	 *
-	 * @param Title $dest the destination of the redirect
-	 * @param string $comment the comment string describing the move
-	 * @return bool true on success
-	 * @access public
-	 */
-	function createRedirect( $dest, $comment ) {
-		if ( $this->getArticleID() ) {
-			return false;
-		}
-
-		$fname = 'Title::createRedirect';
-		$dbw =& wfGetDB( DB_MASTER );
-
-		$article = new Article( $this );
-		$newid = $article->insertOn( $dbw );
-		$revision = new Revision( array(
-			'page'      => $newid,
-			'comment'   => $comment,
-			'text'      => "#REDIRECT [[" . $dest->getPrefixedText() . "]]\n",
-			) );
-		$revision->insertOn( $dbw );
-		$article->updateRevisionOn( $dbw, $revision, 0 );
-
-		# Link table
-		$dbw->insert( 'pagelinks',
-			array(
-				'pl_from'      => $newid,
-				'pl_namespace' => $dest->getNamespace(),
-				'pl_title'     => $dest->getDbKey()
-			), $fname
-		);
-
-		Article::onArticleCreate( $this );
-		return true;
-	}
-
-	/**
 	 * Get categories to which this Title belongs and return an array of
 	 * categories' names.
 	 *
@@ -2231,7 +2394,7 @@
 		global $wgContLang;
 
 		$titlekey = $this->getArticleId();
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$categorylinks = $dbr->tableName( 'categorylinks' );
 
 		# NEW SQL
@@ -2299,7 +2462,7 @@
 	 * @return integer $oldrevision|false
 	 */
 	function getPreviousRevisionID( $revision ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $dbr->selectField( 'revision', 'rev_id',
 			'rev_page=' . intval( $this->getArticleId() ) .
 			' AND rev_id<' . intval( $revision ) . ' ORDER BY rev_id DESC' );
@@ -2312,7 +2475,7 @@
 	 * @return integer $oldrevision|false
 	 */
 	function getNextRevisionID( $revision ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $dbr->selectField( 'revision', 'rev_id',
 			'rev_page=' . intval( $this->getArticleId() ) .
 			' AND rev_id>' . intval( $revision ) . ' ORDER BY rev_id' );
@@ -2326,7 +2489,7 @@
 	 * @return integer  Number of revisions between these IDs.
 	 */
 	function countRevisionsBetween( $old, $new ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $dbr->selectField( 'revision', 'count(*)',
 			'rev_page = ' . intval( $this->getArticleId() ) .
 			' AND rev_id > ' . intval( $old ) .
@@ -2384,7 +2547,7 @@
 	 * Get the last touched timestamp
 	 */
 	function getTouched() {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$touched = $dbr->selectField( 'page', 'page_touched',
 			array( 
 				'page_namespace' => $this->getNamespace(),
@@ -2501,5 +2664,18 @@
 		}
 		return $this;
 	}
+	
+	/**
+	 * Is this Title in a namespace which contains content?
+	 * In other words, is this a content page, for the purposes of calculating
+	 * statistics, etc?
+	 *
+	 * @return bool
+	 */
+	public function isContentPage() {
+		return Namespace::isContent( $this->getNamespace() );
+	}
+	
 }
+
 ?>
diff -NaurB -x .svn includes/UserMailer.php /srv/web/fp014/source/includes/UserMailer.php
--- includes/UserMailer.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/UserMailer.php	2007-02-01 01:03:18.000000000 +0000
@@ -22,7 +22,6 @@
  * @author <brion@pobox.com>
  * @author <mail@tgries.de>
  *
- * @package MediaWiki
  */
 
 /**
@@ -38,7 +37,7 @@
 	 * @param mixed $address String with an email address, or a User object
 	 * @param string $name Human-readable name if a string address is given
 	 */
-	function MailAddress( $address, $name=null ) {
+	function __construct( $address, $name=null ) {
 		if( is_object( $address ) && $address instanceof User ) {
 			$this->address = $address->getEmail();
 			$this->name = $address->getName();
@@ -189,7 +188,6 @@
  *
  * Visit the documentation pages under http://meta.wikipedia.com/Enotif
  *
- * @package MediaWiki
  *
  */
 class EmailNotification {
@@ -247,7 +245,7 @@
 				$userCondition = false;
 			}
 			if( $userCondition ) {
-				$dbr =& wfGetDB( DB_MASTER );
+				$dbr = wfGetDB( DB_MASTER );
 
 				$res = $dbr->select( 'watchlist', array( 'wl_user' ),
 					array(
@@ -294,7 +292,7 @@
 		if ( $wgShowUpdatedMarker || $wgEnotifWatchlist ) {
 			# mark the changed watch-listed page with a timestamp, so that the page is
 			# listed with an "updated since your last visit" icon in the watch list, ...
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$success = $dbw->update( 'watchlist',
 				array( /* SET */
 					'wl_notificationtimestamp' => $dbw->timestamp($timestamp)
diff -NaurB -x .svn includes/User.php /srv/web/fp014/source/includes/User.php
--- includes/User.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/User.php	2007-02-01 01:04:18.000000000 +0000
@@ -2,7 +2,6 @@
 /**
  * See user.txt
  *
- * @package MediaWiki
  */
 
 # Number of characters in user_token field
@@ -25,7 +24,6 @@
 
 /**
  *
- * @package MediaWiki
  */
 class User {
 
@@ -71,6 +69,7 @@
 		'watchlisthidebots',
 		'watchlisthideminor',
 		'ccmeonemails',
+		'diffonly',
 	);
 
 	/**
@@ -270,7 +269,7 @@
 	 * @static
 	 */
 	static function newFromConfirmationCode( $code ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$id = $dbr->selectField( 'user', 'user_id', array(
 			'user_email_token' => md5( $code ),
 			'user_email_token_expires > ' . $dbr->addQuotes( $dbr->timestamp() ),
@@ -302,7 +301,7 @@
 	 * @static
 	 */
 	static function whoIs( $id ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $dbr->selectField( 'user', 'user_name', array( 'user_id' => $id ), 'User::whoIs' );
 	}
 
@@ -313,7 +312,7 @@
 	 * @static
 	 */
 	static function whoIsReal( $id ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		return $dbr->selectField( 'user', 'user_real_name', array( 'user_id' => $id ), 'User::whoIsReal' );
 	}
 
@@ -329,7 +328,7 @@
 			# Illegal name
 			return null;
 		}
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$s = $dbr->selectRow( 'user', array( 'user_id' ), array( 'user_name' => $nt->getText() ), __METHOD__ );
 
 		if ( $s === false ) {
@@ -339,6 +338,30 @@
 		}
 	}
 
+	/** wikia hack */
+	function oldIdFromName( $name ) {
+		global $wgDBname;
+		
+		$fname = "User::oldIdFromName";
+		// make sure no funky chars in user name.
+		$nt = Title::newFromText( $name );
+		if ( is_null( $nt ) ) {
+			// Illegal name
+			return null;
+		}
+
+		// We want to check the original user database, not the shared user database in wikicities.
+		$oldusertable = "`$wgDBname`.`user`";
+		$dbr =& wfGetDB ( DB_SLAVE );
+		$s = $dbr->selectRow( $oldusertable, array( 'user_id' ), array( 'user_name' => $nt->getText() ), $fname );
+
+		if ( $s === false ) {
+			return 0;
+		} else {
+			return $s->user_id;
+		}
+	}
+
 	/**
 	 * Does the string match an anonymous IPv4 address?
 	 *
@@ -546,12 +569,34 @@
 	 * @static
 	 */
 	static function edits( $uid ) {
-		$dbr =& wfGetDB( DB_SLAVE );
-		return $dbr->selectField(
-			'revision', 'count(*)',
-			array( 'rev_user' => $uid ),
+		wfProfileIn( __METHOD__ );
+		$dbr = wfGetDB( DB_SLAVE );
+
+		// check if the user_editcount field has been initialized
+		$field = $dbr->selectField(
+			'user', 'user_editcount',
+			array( 'user_id' => $uid ),
 			__METHOD__
 		);
+
+		if( $field === null ) { // it has not been initialized. do so.
+			$dbw = wfGetDb( DB_MASTER );
+			$count = $dbr->selectField(
+				'revision', 'count(*)',
+				array( 'rev_user' => $uid ),
+				__METHOD__
+			);
+			$dbw->update(
+				'user',
+				array( 'user_editcount' => $count ),
+				array( 'user_id' => $uid ),
+				__METHOD__
+			);
+		} else {
+			$count = $field;
+		}
+		wfProfileOut( __METHOD__ );
+		return $count;
 	}
 
 	/**
@@ -699,7 +744,7 @@
 			return false;
 		}
 
-		$dbr =& wfGetDB( DB_MASTER );
+		$dbr = wfGetDB( DB_MASTER );
 		$s = $dbr->selectRow( 'user', '*', array( 'user_id' => $this->mId ), __METHOD__ );
 
 		if ( $s !== false ) {
@@ -1121,11 +1166,11 @@
 				global $wgMemc;
 				$key = wfMemcKey( 'newtalk', 'ip', $this->getName() );
 				$newtalk = $wgMemc->get( $key );
-				if( is_integer( $newtalk ) ) {
+				if( $newtalk != "" ) {
 					$this->mNewtalk = (bool)$newtalk;
 				} else {
 					$this->mNewtalk = $this->checkNewtalk( 'user_ip', $this->getName() );
-					$wgMemc->set( $key, $this->mNewtalk, time() ); // + 1800 );
+					$wgMemc->set( $key, (int)$this->mNewtalk, time() + 1800 );
 				}
 			} else {
 				$this->mNewtalk = $this->checkNewtalk( 'user_id', $this->mId );
@@ -1162,7 +1207,7 @@
 	 * @private
 	 */
 	function checkNewtalk( $field, $id ) {
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$ok = $dbr->selectField( 'user_newtalk', $field,
 			array( $field => $id ), __METHOD__ );
 		return $ok !== false;
@@ -1179,7 +1224,7 @@
 			wfDebug( __METHOD__." already set ($field, $id), ignoring\n" );
 			return false;
 		}
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->insert( 'user_newtalk',
 			array( $field => $id ),
 			__METHOD__,
@@ -1199,7 +1244,7 @@
 			wfDebug( __METHOD__.": already gone ($field, $id), ignoring\n" );
 			return false;
 		}
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'user_newtalk',
 			array( $field => $id ),
 			__METHOD__ );
@@ -1284,7 +1329,7 @@
 		if( $this->mId ) {
 			$this->mTouched = self::newTouchedTimestamp();
 			
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$dbw->update( 'user',
 				array( 'user_touched' => $dbw->timestamp( $this->mTouched ) ),
 				array( 'user_id' => $this->mId ),
@@ -1310,6 +1355,10 @@
 		return wfEncryptPassword( $this->mId, $p );
 	}
 
+	function encryptPasswordId( $id, $p ) {
+		return wfEncryptPassword( $id, $p );
+	}
+
 	/**
 	 * Set the password and reset the random token
 	 * Calls through to authentication plugin if necessary;
@@ -1546,9 +1595,11 @@
 			if( $this->mId ) {
 				$this->mEffectiveGroups[] = 'user';
 				
-				global $wgAutoConfirmAge;
+				global $wgAutoConfirmAge, $wgAutoConfirmCount;
+
 				$accountAge = time() - wfTimestampOrNull( TS_UNIX, $this->mRegistration );
-				if( $accountAge >= $wgAutoConfirmAge ) {
+				$accountEditCount = User::edits( $this->mId );
+				if( $accountAge >= $wgAutoConfirmAge && $accountEditCount >= $wgAutoConfirmCount ) {
 					$this->mEffectiveGroups[] = 'autoconfirmed';
 				}
 				
@@ -1574,7 +1625,7 @@
 	 */
 	function addGroup( $group ) {
 		$this->load();
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		if( $this->getId() ) {
 			$dbw->insert( 'user_groups',
 				array(
@@ -1598,7 +1649,7 @@
 	 */
 	function removeGroup( $group ) {
 		$this->load();
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'user_groups',
 			array(
 				'ug_user'  => $this->getID(),
@@ -1612,6 +1663,30 @@
 		$this->invalidateCache();
 	}
 
+	function saveOldUserPassword( $id, $passwd ) {
+		global $wgMemc, $wgDBname, $wgOut;
+
+		$fname = 'User::saveOldSettings';
+
+		if ( wfReadOnly() ) {
+			return;
+		}
+
+		if ( 0 == $id ) {
+			return;
+		}
+
+		$wgOut->addHTML("changing the password in the {$wgDBname} DB for user #{$id} to {$passwd}<br>\n");
+		$dbw =& wfGetDB( DB_MASTER );
+		$dbw->update( "$wgDBname`.`user",
+				array( /* SET */
+					'user_password' => $passwd,
+					), array( /* WHERE */
+						'user_id' => $id
+					), $fname
+				);
+	}
+
 
 	/**
 	 * A more legible check for non-anonymousness.
@@ -1750,7 +1825,7 @@
 		// If the page is watched by the user (or may be watched), update the timestamp on any
 		// any matching rows
 		if ( $watched ) {
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$dbw->update( 'watchlist',
 					array( /* SET */
 						'wl_notificationtimestamp' => NULL
@@ -1781,7 +1856,7 @@
 		}
 		if( $currentUser != 0 )  {
 
-			$dbw =& wfGetDB( DB_MASTER );
+			$dbw = wfGetDB( DB_MASTER );
 			$dbw->update( 'watchlist',
 				array( /* SET */
 					'wl_notificationtimestamp' => NULL
@@ -1874,7 +1949,7 @@
 		
 		$this->mTouched = self::newTouchedTimestamp();
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->update( 'user',
 			array( /* SET */
 				'user_name' => $this->mName,
@@ -1902,7 +1977,7 @@
 		$s = trim( $this->getName() );
 		if ( 0 == strcmp( '', $s ) ) return 0;
 
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$id = $dbr->selectField( 'user', 'user_id', array( 'user_name' => $s ), __METHOD__ );
 		if ( $id === false ) {
 			$id = 0;
@@ -1933,7 +2008,7 @@
 			$user->mOptions = $params['options'] + $user->mOptions;
 			unset( $params['options'] );
 		}
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$seqVal = $dbw->nextSequenceValue( 'user_user_id_seq' );
 		$fields = array(
 			'user_id' => $seqVal,
@@ -1966,7 +2041,7 @@
 	 */
 	function addToDatabase() {
 		$this->load();
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$seqVal = $dbw->nextSequenceValue( 'user_user_id_seq' );
 		$dbw->insert( 'user',
 			array(
@@ -2096,7 +2171,7 @@
 		if ( isset( $res ) )
 			return $res;
 		else {
-			$dbr =& wfGetDB( DB_SLAVE );
+			$dbr = wfGetDB( DB_SLAVE );
 			return $res = $dbr->selectField( 'user', 'max(user_id)', false, 'User::getMaxID' );
 		}
 	}
@@ -2145,6 +2220,30 @@
 				return true;
 			}
 		}
+
+		// finally, as a last resort, check this using the old user id.
+		global $wgLoginLocalFallback;
+		if ($wgLoginLocalFallback) {
+			$old_id = $this->oldIdFromName($this->mName);
+			if (0 == $old_id) {
+				return false;
+			}
+			$old_ep = $this->encryptPasswordId ( $old_id, $password );
+			if (0 == strcmp( $old_ep, $this->mPassword ) ) {
+				$this->mPassword = $this->encryptPassword( $password );
+				$this->setToken();
+				$this->saveSettings();
+				return true;
+			} elseif ( function_exists( 'iconv' ) ) {
+				$old_ep = $this->encryptPasswordId( $old_id, iconv( 'UTF-8', 'WINDOWS-1252', $password) );
+				if (0 == strcmp( $old_ep, $this->mPassword ) ) {
+					$this->mPassword = $this->encryptPassword( iconv( 'UTF-8', 'WINDOWS-1252', $password) );
+					$this->setToken();
+					$this->saveSettings();
+					return true;
+				}
+			}
+		}
 		return false;
 	}
 	
@@ -2272,7 +2371,7 @@
 		$token = $this->generateToken( $this->mId . $this->mEmail . $expires );
 		$hash = md5( $token );
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->update( 'user',
 			array( 'user_email_token'         => $hash,
 			       'user_email_token_expires' => $dbw->timestamp( $expires ) ),
@@ -2455,7 +2554,7 @@
 		if( $title ) {
 			global $wgUser;
 			$sk = $wgUser->getSkin();
-			return $sk->makeLinkObj( $title, $text );
+			return $sk->makeLinkObj( $title, htmlspecialchars( $text ) );
 		} else {
 			return $text;
 		}
diff -NaurB -x .svn includes/Utf8Case.php /srv/web/fp014/source/includes/Utf8Case.php
--- includes/Utf8Case.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Utf8Case.php	2007-02-01 01:03:18.000000000 +0000
@@ -7,8 +7,7 @@
  *
  * These are pulled from memcached if possible, as this is faster than filling
  * up a big array manually.
- * @package MediaWiki
- * @subpackage Language
+ * @addtogroup Language
  */
 
 /*
diff -NaurB -x .svn includes/WatchedItem.php /srv/web/fp014/source/includes/WatchedItem.php
--- includes/WatchedItem.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/WatchedItem.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
  *
- * @package MediaWiki
  */
 
 /**
  *
- * @package MediaWiki
  */
 class WatchedItem {
 	var $mTitle, $mUser;
@@ -32,30 +30,17 @@
 	}
 
 	/**
-	 * Returns the memcached key for this item
-	 */
-	function watchKey() {
-		return wfMemcKey( 'watchlist', 'user', $this->id, 'page', $this->ns, $this->ti );
-	}
-
-	/**
 	 * Is mTitle being watched by mUser?
 	 */
 	function isWatched() {
 		# Pages and their talk pages are considered equivalent for watching;
 		# remember that talk namespaces are numbered as page namespace+1.
-		global $wgMemc;
 		$fname = 'WatchedItem::isWatched';
 
-		$key = $this->watchKey();
-		$iswatched = $wgMemc->get( $key );
-		if( is_integer( $iswatched ) ) return $iswatched;
-
-		$dbr =& wfGetDB( DB_SLAVE );
+		$dbr = wfGetDB( DB_SLAVE );
 		$res = $dbr->select( 'watchlist', 1, array( 'wl_user' => $this->id, 'wl_namespace' => $this->ns,
 			'wl_title' => $this->ti ), $fname );
 		$iswatched = ($dbr->numRows( $res ) > 0) ? 1 : 0;
-		$wgMemc->set( $key, $iswatched );
 		return $iswatched;
 	}
 
@@ -68,7 +53,7 @@
 
 		// Use INSERT IGNORE to avoid overwriting the notification timestamp
 		// if there's already an entry for this page
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->insert( 'watchlist',
 		  array(
 		    'wl_user' => $this->id,
@@ -87,18 +72,15 @@
 			'wl_notificationtimestamp' => NULL
 		  ), $fname, 'IGNORE' );
 
-		global $wgMemc;
-		$wgMemc->set( $this->watchkey(), 1 );
 		wfProfileOut( $fname );
 		return true;
 	}
 
 	function removeWatch() {
-		global $wgMemc;
 		$fname = 'WatchedItem::removeWatch';
 
 		$success = false;
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$dbw->delete( 'watchlist',
 			array(
 				'wl_user' => $this->id,
@@ -125,9 +107,6 @@
 		if ( $dbw->affectedRows() ) {
 			$success = true;
 		}
-		if ( $success ) {
-			$wgMemc->set( $this->watchkey(), 0 );
-		}
 		return $success;
 	}
 
@@ -155,7 +134,7 @@
 		$oldtitle = $ot->getDBkey();
 		$newtitle = $nt->getDBkey();
 
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		$res = $dbw->select( 'watchlist', 'wl_user',
 			array( 'wl_namespace' => $oldnamespace, 'wl_title' => $oldtitle ),
 			$fname, 'FOR UPDATE'
diff -NaurB -x .svn includes/WebRequest.php /srv/web/fp014/source/includes/WebRequest.php
--- includes/WebRequest.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/WebRequest.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,7 +1,6 @@
 <?php
 /**
  * Deal with importing all those nasssty globals and things
- * @package MediaWiki
  */
 
 # Copyright (C) 2003 Brion Vibber <brion@pobox.com>
@@ -32,7 +31,6 @@
  * you want to pass arbitrary data to some function in place of the web
  * input.
  *
- * @package MediaWiki
  */
 
 /**
@@ -44,7 +42,7 @@
 }
 
 class WebRequest {
-	function WebRequest() {
+	function __construct() {
 		$this->checkMagicQuotes();
 		global $wgUsePathInfo;
 		if ( $wgUsePathInfo ) {
@@ -483,7 +481,6 @@
 /**
  * WebRequest clone which takes values from a provided array.
  *
- * @package MediaWiki
  */
 class FauxRequest extends WebRequest {
 	var $data = null;
diff -NaurB -x .svn includes/WikiError.php /srv/web/fp014/source/includes/WikiError.php
--- includes/WikiError.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/WikiError.php	2007-02-01 01:03:18.000000000 +0000
@@ -19,19 +19,17 @@
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
  * http://www.gnu.org/copyleft/gpl.html
  *
- * @package MediaWiki
  */
 
 /**
  * Since PHP4 doesn't have exceptions, here's some error objects
  * loosely modeled on the standard PEAR_Error model...
- * @package MediaWiki
  */
 class WikiError {
 	/**
 	 * @param string $message
 	 */
-	function WikiError( $message ) {
+	function __construct( $message ) {
 		$this->mMessage = $message;
 	}
 
@@ -66,7 +64,6 @@
 
 /**
  * Localized error message object
- * @package MediaWiki
  */
 class WikiErrorMsg extends WikiError {
 	/**
@@ -81,7 +78,6 @@
 }
 
 /**
- * @package MediaWiki
  * @todo document
  */
 class WikiXmlError extends WikiError {
diff -NaurB -x .svn includes/Wiki.php /srv/web/fp014/source/includes/Wiki.php
--- includes/Wiki.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Wiki.php	2007-02-01 01:04:18.000000000 +0000
@@ -7,14 +7,12 @@
 
 	var $GET; /* Stores the $_GET variables at time of creation, can be changed */
 	var $params = array();
-	
-	/**
-	 * Constructor
-	 */
-	function MediaWiki () {
+
+	/** Constructor. It just save the $_GET variable */
+	function __construct() {
 		$this->GET = $_GET;
 	}
-	
+
 	/**
 	 * Stores key/value pairs to circumvent global variables
 	 * Note that keys are case-insensitive!
@@ -23,7 +21,7 @@
 		$key = strtolower( $key );
 		$this->params[$key] =& $value;
 	}
-	
+
 	/**
 	 * Retrieves key/value pairs to circumvent global variables
 	 * Note that keys are case-insensitive!
@@ -35,7 +33,7 @@
 		}
 		return $default;
 	}
-	
+
 	/**
 	 * Initialization of ... everything
 	 @return Article either the object to become $wgArticle, or NULL
@@ -57,7 +55,7 @@
 		wfProfileOut( 'MediaWiki::initialize' );
 		return $article;
 	}
-	
+
 	/**
 	 * Checks some initial queries
 	 * Note that $title here is *not* a Title object, but a string!
@@ -66,10 +64,10 @@
 		if ($request->getVal( 'printable' ) == 'yes') {
 			$output->setPrintable();
 		}
-		
+
 		$ret = NULL ;
-		
-		
+
+
 		if ( '' == $title && 'delete' != $action ) {
 			$ret = Title::newMainPage();
 		} elseif ( $curid = $request->getInt( 'curid' ) ) {
@@ -82,19 +80,19 @@
 			*/
 			if( count($lang->getVariants()) > 1 && !is_null($ret) && $ret->getArticleID() == 0 )
 				$lang->findVariantLink( $title, $ret );
-		
+
 		}
 		return $ret ;
 	}
-	
+
 	/**
 	 * Checks for search query and anon-cannot-read case
 	 */
 	function preliminaryChecks ( &$title, &$output, $request ) {
-	
+
 		# Debug statement for user levels
 		// print_r($wgUser);
-		
+
 		$search = $request->getText( 'search' );
 		if( !is_null( $search ) && $search !== '' ) {
 			// Compatibility with old search URLs which didn't use Special:Search
@@ -111,16 +109,16 @@
 			$output->output();
 			exit;
 		}
-		
+
 	}
-	
+
 	/**
 	 * Initialize the object to be known as $wgArticle for special cases
 	 */
 	function initializeSpecialCases ( &$title, &$output, $request ) {
 		global $wgRequest;
 		wfProfileIn( 'MediaWiki::initializeSpecialCases' );
-		
+
 		$search = $this->getVal('Search');
 		$action = $this->getVal('Action');
 		if( !$this->getVal('DisableInternalSearch') && !is_null( $search ) && $search !== '' ) {
@@ -150,13 +148,13 @@
 		{
 			$targetUrl = $title->getFullURL();
 			// Redirect to canonical url, make it a 301 to allow caching
-			global $wgServer, $wgUsePathInfo;
+			global $wgUsePathInfo;
 			if( $targetUrl == $wgRequest->getFullRequestURL() ) {
 				$message = "Redirect loop detected!\n\n" .
 					"This means the wiki got confused about what page was " .
 					"requested; this sometimes happens when moving a wiki " .
 					"to a new server or changing the server configuration.\n\n";
-				
+
 				if( $wgUsePathInfo ) {
 					$message .= "The wiki is trying to interpret the page " .
 						"title from the URL path portion (PATH_INFO), which " .
@@ -206,7 +204,7 @@
 			// FIXME: where should this go?
 			$title = Title::makeTitle( NS_IMAGE, $title->getDBkey() );
 		}
-	
+
 		switch( $title->getNamespace() ) {
 		case NS_IMAGE:
 			return new ImagePage( $title );
@@ -216,7 +214,7 @@
 			return new Article( $title );
 		}
 	}
-	
+
 	/**
 	 * Initialize the object to be known as $wgArticle for "standard" actions
 	 * Create an Article object for the page, following redirects if needed.
@@ -228,17 +226,17 @@
 	function initializeArticle( $title, $request ) {
 		global $wgTitle;
 		wfProfileIn( 'MediaWiki::initializeArticle' );
-		
+
 		$action = $this->getVal('Action');
 		$article = $this->articleFromTitle( $title );
-		
+
 		// Namespace might change when using redirects
 		if( $action == 'view' && !$request->getVal( 'oldid' ) &&
 						$request->getVal( 'redirect' ) != 'no' ) {
-							
-			$dbr =& wfGetDB(DB_SLAVE);
+
+			$dbr = wfGetDB(DB_SLAVE);
 			$article->loadPageData($article->pageDataFromTitle($dbr, $title));
-		
+
 			/* Follow redirects only for... redirects */
 			if ($article->mIsRedirect) {
 				$target = $article->followRedirect();
@@ -290,7 +288,7 @@
 	 */
 	function doUpdates ( &$updates ) {
 		wfProfileIn( 'MediaWiki::doUpdates' );
-		$dbw =& wfGetDB( DB_MASTER );
+		$dbw = wfGetDB( DB_MASTER );
 		foreach( $updates as $up ) {
 			$up->doUpdate();
 
@@ -307,7 +305,7 @@
 	 */
 	function doJobs() {
 		global $wgJobRunRate;
-		
+
 		if ( $wgJobRunRate <= 0 || wfReadOnly() ) {
 			return;
 		}
@@ -335,7 +333,7 @@
 			wfDebugLog( 'jobqueue', $output );
 		}
 	}
-	
+
 	/**
 	 * Ends this task peacefully
 	 */
@@ -407,18 +405,25 @@
 				}
 				/* Continue... */
 			case 'edit':
-				$internal = $request->getVal( 'internaledit' );
-				$external = $request->getVal( 'externaledit' );
-				$section = $request->getVal( 'section' );
-				$oldid = $request->getVal( 'oldid' );
-				if( !$this->getVal( 'UseExternalEditor' ) || $action=='submit' || $internal ||
-				   $section || $oldid || ( !$user->getOption( 'externaleditor' ) && !$external ) ) {
-					$editor = new EditPage( $article );
-					$editor->submit();
-				} elseif( $this->getVal( 'UseExternalEditor' ) && ( $external || $user->getOption( 'externaleditor' ) ) ) {
-					$mode = $request->getVal( 'mode' );
-					$extedit = new ExternalEdit( $article, $mode );
-					$extedit->edit();
+				if( wfRunHooks( 'CustomEditor', array( $article, $user ) ) ) {
+					$internal = $request->getVal( 'internaledit' );
+					$external = $request->getVal( 'externaledit' );
+					$section = $request->getVal( 'section' );
+					$oldid = $request->getVal( 'oldid' );
+					if( !$this->getVal( 'UseExternalEditor' ) || $action=='submit' || $internal ||
+					   $section || $oldid || ( !$user->getOption( 'externaleditor' ) && !$external ) ) {
+					   	global $wgEnableStructuredEdit;
+						if($wgEnableStructuredEdit) {
+							$editor = new StructuredEditPage( $article );
+						} else {
+							$editor = new EditPage( $article );
+						}
+						$editor->submit();
+					} elseif( $this->getVal( 'UseExternalEditor' ) && ( $external || $user->getOption( 'externaleditor' ) ) ) {
+						$mode = $request->getVal( 'mode' );
+						$extedit = new ExternalEdit( $article, $mode );
+						$extedit->edit();
+					}
 				}
 				break;
 			case 'history':
@@ -439,7 +444,6 @@
 		}
 		wfProfileOut( 'MediaWiki::performAction' );
 
-	
 	}
 
 }; /* End of class MediaWiki */
diff -NaurB -x .svn includes/XmlFunctions.php /srv/web/fp014/source/includes/XmlFunctions.php
--- includes/XmlFunctions.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/XmlFunctions.php	2007-02-01 01:03:18.000000000 +0000
@@ -15,7 +15,7 @@
 function wfCloseElement( $element ) {
 	return "</$element>";
 }
-function &HTMLnamespaceselector($selected = '', $allnamespaces = null, $includehidden=false) {
+function HTMLnamespaceselector($selected = '', $allnamespaces = null, $includehidden=false) {
 	return Xml::namespaceSelector( $selected, $allnamespaces, $includehidden );
 }
 function wfSpan( $text, $class, $attribs=array() ) {
diff -NaurB -x .svn includes/Xml.php /srv/web/fp014/source/includes/Xml.php
--- includes/Xml.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/Xml.php	2007-02-01 01:03:18.000000000 +0000
@@ -50,7 +50,9 @@
 			$attribs = array_map( array( 'UtfNormal', 'cleanUp' ), $attribs );
 		}
 		if( $contents ) {
+			wfProfileIn( __METHOD__ . '-norm' );
 			$contents = UtfNormal::cleanUp( $contents );
+			wfProfileOut( __METHOD__ . '-norm' );
 		}
 		return self::element( $element, $attribs, $contents );
 	}
@@ -67,7 +69,7 @@
 	 * @param $includehidden Bool: include hidden namespaces?
 	 * @return String: Html string containing the namespace selector
 	 */
-	public static function &namespaceSelector($selected = '', $allnamespaces = null, $includehidden=false) {
+	public static function namespaceSelector($selected = '', $allnamespaces = null, $includehidden=false) {
 		global $wgContLang;
 		if( $selected !== '' ) {
 			if( is_null( $selected ) ) {
diff -NaurB -x .svn includes/ZhClient.php /srv/web/fp014/source/includes/ZhClient.php
--- includes/ZhClient.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ZhClient.php	2007-02-01 01:03:18.000000000 +0000
@@ -1,12 +1,10 @@
 <?php
 /**
- * @package MediaWiki
  */
 
 /**
  * Client for querying zhdaemon
  *
- * @package MediaWiki
  */
 class ZhClient {
 	var $mHost, $mPort, $mFP, $mConnected;
diff -NaurB -x .svn includes/ZhConversion.php /srv/web/fp014/source/includes/ZhConversion.php
--- includes/ZhConversion.php	2007-02-02 01:47:14.000000000 +0000
+++ /srv/web/fp014/source/includes/ZhConversion.php	2007-02-01 01:03:18.000000000 +0000
@@ -5,7 +5,6 @@
  * Automatically generated using code and data in includes/zhtable/
  * Do not modify directly!
  *
- * @package MediaWiki
 */
 
 $zh2TW=array(
