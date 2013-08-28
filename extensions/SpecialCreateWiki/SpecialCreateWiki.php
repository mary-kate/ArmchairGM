<?php
/**
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */

$wgExtensionFunctions[] = 'wfSpecialCreateWikiSetup';

$wgAvailableRights[] = 'createwiki';
$wgGroupPermissions['staff']['createwiki'] = true;

/**
 *
 */
require_once('UserMailer.php');

/**
 * constructor
 */
function wfSpecialCreateWikiSetup() {

/**
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */

class CreateWikiForm extends SpecialPage {
	var $mName, $mPassword, $mRetype, $mReturnto, $mCookieCheck, $mPosted;
	var $mAction, $mCreateaccount, $mCreateaccountMail, $mMailmypassword;
	var $mLoginattempt, $mRemember, $mEmail, $mImportStarter;

	function CreateWikiForm() {
		global $wgLang, $wgAllowRealName;
		global $wgRequest;

		SpecialPage::SpecialPage("CreateWiki");
	}

	function execute() {
	  global $wgRequest,$wgUser,$wgOut;

	  $this->mName = trim($wgRequest->getText( 'wpName' ));
	  $this->mWikiName = trim($wgRequest->getText( 'wpCreateWikiName' ));
	  $this->mWikiTitle = trim($wgRequest->getText( 'wpCreateWikiTitle' ));
	  $this->mWikiLang = trim($wgRequest->getText( 'wpCreateWikiLang' ));
	    $this->mWikiIncludeLang = $wgRequest->getCheck( 'wpCreateWikiIncludeLang' );
	    $this->mWikiDesc = $wgRequest->getText( 'wpCreateWikiDesc' );
	    $this->mWikiAddtnl = $wgRequest->getText( 'wpCreateWikiAddtnl' );
#		$this->mCookieCheck = $wgRequest->getVal( 'wpCookieCheck' );
	    $this->mPosted = $wgRequest->wasPosted();
	    $this->mAction = $wgRequest->getVal( 'action' );
	    $this->mEmail = trim($wgRequest->getText( 'wpEmail' ));
	    $this->mCreateMailList = $wgRequest->getCheck( 'wpCreateMailList' );
	    $this->mImportStarter = $wgRequest->getCheck( 'wpImportStarter' );
	    $this->mReqID = $wgRequest->getVal( 'req_id' ); 
	    $this->mLoadID = $wgRequest->getVal( 'load_id' ); 
	    $this->mLoadAction = $wgRequest->getVal( 'actionLoadRequest' ); 
	    $this->mChangeAction = $wgRequest->getVal( 'actionChangeRequest' ); 
	    $this->mDenyAction = $wgRequest->getVal( 'actionDenyRequest' ); 
	    $this->mRequestAction = $wgRequest->getVal( 'actionRequestWiki' ); 
	    $this->mCreateAction = $wgRequest->getVal( 'actionCreateWiki' ); 
	    $this->mLoadAction = $wgRequest->getVal( 'actionLoadRequest' ); 
	    $this->setupMessages();

	  $wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
	  $wgOut->setRobotpolicy( 'noindex,nofollow' );
	  $wgOut->setArticleRelated( false );


	    if( $this->mPosted ) {
	      global $wgOut;
	      if (  'submit' == $this->mAction ) {
		$retval = null;
		if ( $this->mLoadAction ) {
		  $this->mLoadID = $this->mReqID;
		  $this->loadRequest();
		}
		else if ( $this->mChangeAction ) {
		  $retval = $this->processChange();
		}
		else if ( $this->mDenyAction ) {
		  $retval = $this->processDenial();
		  $this->clearRequest();
		}
		else if ( $this->mCreateAction ) {
		  $retval = $this->processCreation();
			if(strlen($retval)>0) {
				$this->releaseLock();
				$wgOut->addHTML("<span style=\"color: red;\">Something is wrong: {$retval}.  Creation aborted.</span>  Perhaps you meant to include the language prefix in the URL.  Click your back button and try again.<br><br>\n");
			}
		  $this->clearRequest();
		}
		else{
		  return $this->processRequest();
		}
	      }

	    }
	    $this->mainCreateWikiForm( '' );
	    return $retval;
	  }
	/**
	 * @access private
	 */
	function processRequest() {
		global $wgUser, $wgOut;

		$wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );
 
		$wgOut->addHTML(wfMsg( 'createwikisubmitcomplete' ));
		$today = date("F j, Y");
		$m = "from $this->mName - $this->mEmail\n";
		$m .= "$this->mWikiTitle at http://$this->mWikiName.wikia.com\n\n";
		$m .= "$this->mWikiDesc\n\n";
		$m .= "Additional information: $this->mWikiAddtnl\n";
		$m .= "Default language: $this->mWikiLang\n\n";
		$m .= "http://www.wikia.com/index.php?title=$this->mWikiName&action=edit\n\n";
		$m .= "{{subst:New wiki header|{$this->mWikiName}|{$this->mWikiDesc}}} \n";
		$m .= "{{subst:New wiki|{$this->mWikiName}|{$today}|{$this->mName}|{$this->mWikiLang}|}} \n";
		$m .= "{{visit|{{PAGENAME}}|w:c:{$this->mWikiName}|align=right}}  \n";
		$m .= "\n[[Category:Wikia descriptions]]\n[[Category:]]\n";

		$dbw = wfGetDB(DB_MASTER);
		$dbw->insert( '`wikicities`.request_list',
			array( /* SET */
			      'req_title'       => $this->mWikiTitle,
			      'req_name'        => $this->mWikiName,
			      'req_lang'        => $this->mWikiLang,
			      'req_founder'     => $this->mName,
			      'req_email'       => $this->mEmail,
			      'req_description' => $this->mWikiDesc,
			      'req_additional'  => $this->mWikiAddtnl,
			      'req_date'        => $today
			      ));

		exec("/bin/echo '$m' >> /home/wikia/request2006.log");
		
		$toaddr = new MailAddress("jasonr@wikia.com");
		$fromaddr = new MailAddress($wgPasswordSender);
		$error = userMailer( $toaddr, 
				     $fromaddr, 
				     wfMsg( 'createwikimailsub') . ' - ' . $this->mWikiName, 
 				     "Request for a new wiki: $m", $this->mEmail . ',' . 'angela@wikia.com' );
 		$toaddr = new MailAddress("beesley@gmail.com");
 		$error = userMailer( $toaddr, 
 				     $fromaddr, 
 				     wfMsg( 'createwikimailsub') . ' - ' . $this->mWikiName, 
 				     "Request for a new wiki: $m", $this->mEmail . ',' . 'angela@wikia.com' );
 		$toaddr = new MailAddress("wikia-requests@wikia.com");
 		$error = userMailer( $toaddr, 
 				     $fromaddr, 
 				     wfMsg( 'createwikimailsub') . ' - ' . $this->mWikiName, 
 				     "Request for a new wiki: $m", $this->mEmail );
 		
 
		return htmlspecialchars( $error );
	}

	/**
	 * @access private
	 */
	function processChange() {
	  global $wgUser, $wgOut;

	  if ( !in_array( 'createwiki', $wgUser->getRights() ) ) {
	    return;
	  }
	  if(!isset($this->mLoadID) || '' == $this->mLoadID){
	    $wgOut->addHTML("No valid request ID.  Try saving the request as a new request\n");
	    return;
	  }
	  $wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
	  $wgOut->setRobotpolicy( 'noindex,nofollow' );
	  $wgOut->setArticleRelated( false );
	  
	  $wgOut->addHTML(wfMsg( 'createwikichangecomplete' ));
	  $dbw = wfGetDB(DB_MASTER);
	  $dbw->replace( '`wikicities`.request_list',
			 array('req_id' => $this->mLoadID),
			 array( /* SET */
			       'req_id' => $this->mLoadID,
			       'req_title'       => $this->mWikiTitle,
			       'req_name'        => $this->mWikiName,
			       'req_lang'        => $this->mWikiLang,
			       'req_founder'     => $this->mName,
			       'req_email'       => $this->mEmail,
			       'req_description' => $this->mWikiDesc,
			       'req_additional' => $this->mWikiAddtnl,
			       ));
	  
	}
	
	/**
	 * @access private
	 */
	function processDenial() {
	  global $wgUser, $wgOut;

	  if ( !in_array( 'createwiki', $wgUser->getRights() ) ) {
	    return;
	  }
	  if(!isset($this->mLoadID) || '' == $this->mLoadID){
	    $wgOut->addHTML("No valid request ID.");
	    return;
	  }
	  $wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
	  $wgOut->setRobotpolicy( 'noindex,nofollow' );
	  $wgOut->setArticleRelated( false );

	  $wgOut->addHTML("request {$this->mWikiName} ({$this->mLoadID}) marked as \"Denied\".<br>\n");
	  
	  $wgOut->addHTML(wfMsg( 'createwikichangecomplete' ));
	  $dbw = wfGetDB(DB_MASTER);
	  $dbw->update( '`wikicities`.request_list',
			array('req_state' => "denied"),
			array('req_id' => $this->mLoadID));
	  
	}

	/**
	 * @access private
	 */

	function clearRequest() {
	  global $wgUser, $wgOut;
	  
	  $this->mName = "";
	  $this->mWikiName = "";
	  $this->mWikiTitle = "";
	  $this->mWikiLang = "";
	  $this->mWikiDesc = "";
	  $this->mWikiAddtnl = "";
	  $this->mEmail = "";

	  return;
	}
	/**
	 * @access private
	 */

	function loadRequest() {
	  global $wgUser, $wgOut;
	  
	  if ( !in_array( 'createwiki', $wgUser->getRights() ) ) {
	    return;
	  }
	  $dbw = wfGetDB(DB_MASTER);
	  $obj = $dbw->selectRow("`wikicities`.request_list",
				 array('req_name','req_title','req_lang','req_founder','req_email','req_description','req_additional','req_state'),
				 array('req_id' => $this->mLoadID));
	  
	  $this->mName = $obj->req_founder;
	  $this->mWikiName = $obj->req_name;
	  $this->mWikiTitle = $obj->req_title;
	  $this->mWikiLang = $obj->req_lang;
	  $this->mWikiDesc = $obj->req_description;
	  $this->mWikiAddtnl = $obj->req_additional;
	  $this->mEmail = $obj->req_email;

		$wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );
 
		return;
	}

	function getLock($numAttempts = 3){
	  # open a lock file
	  # if it opens, return true.
	  # if it fails, try again $numAttempts times, and return false
	  for ($i=0;$i<$numAttempts;$i++){
	    if(!file_exists("/home/wikia/conf/creation_lockfile.lock")){
	      $file = fopen("/home/wikia/conf/creation_lockfile.lock","x");
	      if($file){
		$i=$numAttempts;
		fwrite($file,"Someone has the creation script locked");
		fclose($file);
		return true;
	      }
	      else{
		sleep(1);
	      }	    
	    }
	    else{
	      sleep(1);
	    }
	  }
	  return false;
	}

 
 function releaseLock(){
   if(file_exists("/home/wikia/conf/creation_lockfile.lock")){
     unlink("/home/wikia/conf/creation_lockfile.lock");
     return true;
   }
   return false;
 }

	/**
	 * @access private
	 */
	function processCreation() {
		global $wgUser, $wgOut, $wgSharedDB, $IP;

		$today = date("F j, Y");

		if (!in_array('createwiki', $wgUser->getRights())) {
			return;
		}

		if(!$this->getLock()){
			return "couldn't get creation lock";
		}

		#We gather all the info about the wiki
		$WikiCodeDir = '/usr/wikia/source/wiki/';
		$WikiCitiesDir = '/home/wikia/conf/cities/';
		$WikiImagesDir = '/images/';
		$WikiWorkHttpDir = '/home/wikia/work-http/';
		$WikiMainDir = '/home/wikia/';

		$wiki_name = trim($this->mWikiName);
		$wiki_title = trim($this->mWikiTitle);
		$wiki_language = trim($this->mWikiLang);
		$wiki_subdomain = trim($this->mWikiName);
		$wiki_dir_part = $wiki_name;
		$dbname = $wiki_name;

		$this->mPath = $WikiCitiesDir . $wiki_name;
		$this->mImagesPath = $WikiImagesDir . $wiki_name;

		if(isset($this->mWikiLang) && $this->mWikiLang != "" && $this->mWikiIncludeLang) {
			$wiki_subdomain = $this->mWikiLang . "." . $wiki_name;
			$this->mPath .= "/$this->mWikiLang";
			$this->mImagesPath .= "/$this->mWikiLang";
			$dbname = str_replace("-","",$this->mWikiLang).$dbname;
			$wiki_dir_part .= "/$this->mWikiLang";
		}

		$wgOut->setPageTitle( wfMsg( 'createwikipagetitle'.$wiki_name ) );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );
 
		# if something about the wiki already exists (DB,path,etc.) we should abort
		if(file_exists($this->mPath)){
			return "$this->mPath already exists";
		}

		if(file_exists($this->mImagesPath)){
			return "$this->mImagesPath already exists";
		}

		if(preg_match("/[^0-9a-zA-Z_]+/",$dbname)){
			return "name \"$dbname\" contains non-alphanumeric characters";
		}

		if(!is_writable($WikiCitiesDir)) {
			return "directory {$WikiCitiesDir} is not writable";
		}

		#We create the wiki
		$dbw = wfGetDB(DB_MASTER);
		$dbw->insert( '`wikicities`.city_list',
				array( /* SET */
				      'city_title'          => $this->mWikiTitle,
				      'city_dbname'         => $dbname,
				      'city_url'            => "http://$wiki_subdomain.wikia.com/",
				      'city_founding_user'  => $this->mName,
				      'city_founding_email' => $this->mEmail,
				      'city_path'           => $this->mPath,
				      'city_description'    => $this->mWikiDesc,
				      'city_lang'           => $this->mWikiLang,
				      'city_created'        => $today,
				      'city_additional'     => $this->mWikiAddtnl,
				      ));

		if(isset($this->mLoadID) && '' != $this->mLoadID){
			$dbw->update('`wikicities`.request_list', array('req_state' => "created"), array('req_id' => $this->mLoadID));
		}

		exec("/bin/echo '$dbname' >> /home/wikia/wikilist.txt");

		mkdir("$this->mPath",0775,true);
		mkdir("$this->mImagesPath/images/b/bc",0775,true);
		exec("/bin/cp $WikiWorkHttpDir/images/b/bc/Wiki.png $this->mImagesPath/images/b/bc/Wiki.png");
		mkdir("$this->mImagesPath/images/6/64",0775,true);
		exec("/bin/cp $WikiWorkHttpDir/images/6/64/Favicon.ico $this->mImagesPath/images/6/64");

		symlink("$this->mImagesPath/images", "$this->mPath/images");
		symlink("$WikiWorkHttpDir/robots.txt", "$this->mPath/robots.txt");
		symlink("$WikiCodeDir/redirect.php", "$this->mPath/redirect.php");
		symlink("$WikiCodeDir/index.php", "$this->mPath/index.php");
		symlink("$WikiCodeDir/includes", "$this->mPath/includes");
		symlink("$WikiCodeDir/skins", "$this->mPath/skins");
		symlink("$WikiCodeDir/languages", "$this->mPath/languages");
		symlink("$WikiCodeDir/extensions", "$this->mPath/extensions");
		symlink("$WikiCodeDir/StartProfiler.php", "$this->mPath/StartProfiler.php");
		symlink("$WikiCodeDir/api.php", "$this->mPath/api.php");
		mkdir("$WikiWorkHttpDir/dbdumps/$dbname",0775,true);
		symlink("$this->mImagesPath/images/6/64/Favicon.ico", "$this->mPath/favicon.ico");
#		symlink("$this->mPath/dbdumps", "$WikiWorkHttpDir/dbdumps/$dbname");
		$timedate = date("r");
		$today = date("j F, Y");
		exec("/bin/echo $timedate > $this->mPath/created_on.txt");

		$infile = fopen("$WikiMainDir/DefaultLocalSettings.php",'r');
		if($infile) {
			$outfile = fopen("$this->mPath/LocalSettings.php",'w');
			if($outfile) {
				while ($line = fgets($infile)) {
					$line = preg_replace('/--!!SITEDBNAME!!--/',"$dbname",$line);
					$line = preg_replace('/--!!SITETITLE!!--/',"$this->mWikiTitle",$line);
					$line = preg_replace('/--!!SITELANGUAGE!!--/',"$this->mWikiLang",$line);
					$line = preg_replace('/--!!SITEDIRPART!!--/',"$wiki_dir_part",$line);
					$line = preg_replace('/--!!SITESUBDOMAIN!!--/',"$wiki_subdomain",$line);
					fwrite($outfile,"$line");
				}
				fclose($outfile);
				chmod("$this->mPath/LocalSettings.php",0664);
			} else {
				return "couldn't open {$this->mPath}LocalSettings.php file";
			}
			fclose($infile);
		} else {
			print "couldn't open {$WikiMainDir}DefaultLocalSettings.php file";
		}

		$infile = fopen("$WikiMainDir/DefaultHttpd.conf","r");
		if($infile) {
			$outfile = fopen("$WikiMainDir/cities.httpd.conf","a");
			if($outfile) {
				while ($line = fgets($infile)) {
					$line = preg_replace('/--!!SITEDBNAME!!--/',"$dbname",$line);
					$line = preg_replace('/--!!SITETITLE!!--/',"$this->mWikiTitle",$line);
					$line = preg_replace('/--!!SITELANGUAGE!!--/',"$this->mWikiLang",$line);
					$line = preg_replace('/--!!SITEDIR!!--/',"$this->mPath",$line);
					$line = preg_replace('/--!!SITESUBDOMAIN!!--/',"$wiki_subdomain",$line);
					fwrite($outfile,$line);
				}
				fclose($outfile);
				$track_error = ini_set("track_errors","on");
				$f = fopen("/home/wikia/restart_apache","w");
				if($f) {
					fwrite($f,"restart requested at $timedate");
					fclose($f);
					$wgOut->addHTML("I tried to schedule the webservers to reload...  It may be up to 5 minutes before the wiki is available.<br>\n");
				} else {
					$wgOut->addHTML("For some reason, I couldn't schedule Apache to restart.  New wikis will not work until Apache is restarted.($php_errormsg)<br>\n");
				}

				ini_set("track_errors",$track_error);

			} else {
				return "couldn't open  {$WikiMainDir}cities.httpd.conf file";
				fclose($infile);
			}
		} else {
			print "couldn't open {$WikiMainDir}DefaultHttpd.conf file";
		}

		#adding database for site

		$dbw->query("create database `$dbname`;");
		$dbw->selectDB($dbname);

		$sqlfiles = array("/home/wikia/wiki/maintenance/tables.sql", 
				"/home/wikia/wiki/maintenance/interwiki.sql", 
				"/home/wikia/default_userrights.sql", 
				"/home/wikia/city_interwiki_links.sql");

		$saved_shared = $wgSharedDB;
		$wgSharedDB = $dbname;

		foreach($sqlfiles as $file){
			$wgOut->addHTML("loading $file<br>\n");
			$dbw->sourceFile( $file );
		}

		#insert a blank entry in the site_stats table, so the sitestats page works
		$dbw->replace("site_stats", array('ss_row_id'), array('ss_row_id' => 1, 'ss_total_views' => 0, 'ss_total_edits' => 0, 'ss_good_articles' => 0));

		#starter.wikia.com
		if($this->mImportStarter && ($wiki_language == "en" || $wiki_language == "de")) {
			global $wgDBserver, $wgDBuser, $wgDBpassword;

			$add = ($wiki_language == "en") ? "" : "de";
			exec("/opt/wikia/bin/mysqldump -h$wgDBserver -u$wgDBuser -p$wgDBpassword {$add}starter categorylinks externallinks image imagelinks langlinks page pagelinks revision templatelinks text | /opt/wikia/bin/mysql -h$wgDBserver -u$wgDBuser -p$wgDBpassword $dbname");
			$dbw->selectDB($dbname);
			$dbw->sourceFile( "$IP/maintenance/cleanupStarter.sql" );

			exec("/bin/cp -af $WikiCitiesDir/starter/{$add}/images/ {$this->mImagesPath}/");
			exec("/opt/wikia/php/bin/php $WikiCodeDir/maintenance/updateArticleCount.php --update --conf $this->mPath/LocalSettings.php");
			$wgOut->addHTML( "Imported content from starter.wikia.com<br>\n" );
		}
	  
		$wgSharedDB = $saved_shared;

		# Do some initialisation in PHP 
		exec("php $WikiCodeDir/maintenance/init-wiki.php --conf $this->mPath/LocalSettings.php");
		exec("php $WikiCodeDir/maintenance/rebuildMessages.php --update --conf $this->mPath/LocalSettings.php");

		# this one makes the user a sysop/bureaucrat
		if ($ruser_id) {
			$dbw->replace('user_groups',array(),array('ug_user' => $ruser_id, 'ug_group' => 'sysop'));
			$dbw->replace('user_groups',array(),array('ug_user' => $ruser_id, 'ug_group' => 'bureaucrat'));
		}

		if($this->mCreateMailList) {
			$random = fopen("/dev/urandom","r");
			$list_password = fread($random,4);
			fclose($random);
			$rdata = unpack("H*data", $list_password);
			$list_password = $rdata['data'];
			$escaped_mail = $this->mEmail;
			$f = fopen("http://lists.wikia.com/mailman/create?listname={$wiki_subdomain}-l&owner=$escaped_mail&autogen=1&password=&confirm=&moderate=0&langs={$this->mWikiLang}&notify=1&auth=ikiwiggy&doit=Create+List","r");
			fclose($f);
		}
		
		$wgOut->addHTML(wfMsg( 'createwikicreatecomplete' ));
		$this->releaseLock();
		return;
	}

	/**
	 * @access private
	 */
	function mainCreateWikiForm( $err ) {
		global $wgUser, $wgOut, $wgLang;
		global $wgDBname, $wgAllowRealName;

		$yn = wfMsg( 'yourname' );
                $cwtt = wfMsg( 'createwikititle' );
                $cwn = wfMsg( 'createwikiname' );
                $cwl = wfMsg( 'createwikilang' );
                $nvt = wfMsg( 'createwikinamevstitle' );
                $cwd = wfMsg( 'createwikidesc' );
                $cwa = wfMsg( 'createwikiaddtnl' );
                $cwt = wfMsg(($wgUser->isAllowed('createwiki'))?'createwikistafftext':'createwikitext' );
                $rcw = wfMsg( 'requestcreatewiki' );
		$ye = wfMsg( 'youremail' );
              $bemail = wfMsg( 'createwikibademail' );

		if ( '' == $this->mName ) {
			if ( 0 != $wgUser->getID() ) {
				$this->mName = $wgUser->getName();
			} else {
				$wgOut->addHTML(wfMsg('createwikilogin'));
				return;
			}
		}

		$wgOut->setPageTitle( wfMsg( 'createwikipagetitle' ) );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );

		$q = 'action=submit';

		$titleObj = Title::makeTitle( NS_SPECIAL, 'CreateWiki' );
		$action = $titleObj->escapeLocalUrl( $q );

		$encName = htmlspecialchars( $this->mName );
             
                  $encEmail = ($this->mEmail == '') ? htmlspecialchars( $wgUser->getEmail() ) : htmlspecialchars( $this->mEmail );

		if ($wgUser->getID() != 0) {
			$cambutton = "<input tabindex='6' type='submit' onclick=\"clicked='create'\" name=\"wpCreateaccountMail\" value=\"{$cam}\" />";
		} else {
			$cambutton = '';
		}

		$lcase_name = strtolower($this->mWikiName);
		$wgOut->addHTML( "
        {$cwt}

<script type=\"text/javascript\">
var clicked;
function validateForm() {
  var mEmail = document.getElementById(\"wpEmail\").value;
  
  if ( (clicked=='request') && !mEmail.match(/[0-9a-zA-Z-+.]+\@[0-9a-zA-Z-+.]+\.[0-9a-zA-Z-+.]+/)) {
      window.alert(\"{$bemail}\");
      clicked = '';
      return false;
  }
  return true;
}
</script>

	<form name=\"createwiki\" onsubmit=\"return validateForm()\" id=\"createwiki\" method=\"post\" action=\"{$action}\">

	<table border='0'><tr>
	<td align='right'>$yn:</td>
	<td align='left'>{$encName}
	<input tabindex='1' type='hidden' name=\"wpName\" value=\"{$encName}\" size='35' />
	</td>
	
	</tr>

	<tr>
	<td align='right'>$ye:</td>
	<td align='left'>
	<input tabindex='2' type='text' id=\"wpEmail\" name=\"wpEmail\" value=\"{$encEmail}\" size='35' />
	</td>
        </tr>
     <tr>
	<td align='right'>$cwn:</td>
	<td align='left'>
	<input tabindex='3' type='text' name=\"wpCreateWikiName\" value=\"$lcase_name\" size='35' />
	</td>

	</tr>
	<tr>
	<td align='right'>$cwtt:</td>
	<td align='left'>
	<input tabindex='4' type='text' name=\"wpCreateWikiTitle\" value=\"$this->mWikiTitle\" size='35' />
	</td>
	
	</tr>
   
<tr>
	<td align='right'>$cwl:</td>
	<td align='left'>");

		global $wgLang,$wgContLanguageCode;
		$languages = $wgLang->getLanguageNames();

		if( !array_key_exists( $wgContLanguageCode, $languages ) ) {
			$languages[$wgContLanguageCode] = $wgContLanguageCode;
		}
		ksort( $languages );
	
		$selectedLang = isset( $languages[$this->mWikiLang] ) ? $this->mWikiLang : $wgContLanguageCode;
#		$selectedLang = isset( $languages[$this->mUserLanguage] ) ? $this->mUserLanguage : $wgContLanguageCode;
		$selbox = null;
		foreach($languages as $code => $name) {
		  global $IP;
		  $sel = ($code == $selectedLang)? ' selected="selected"' : '';
		  $selbox .= "<option value=\"$code\"$sel>$code - $name</option>\n";
		}
		$wgOut->addHTML("<select name=\"wpCreateWikiLang\">$selbox</select>");
		if($wgUser->isAllowed('createwiki')){
		  $wgOut->addHTML("        <input type=checkbox name=\"wpCreateWikiIncludeLang\"> Include language prefix in URL (for multi-language wikis)<br />");
		  $wgOut->addHTML("        <input type=checkbox name=\"wpCreateMailList\" checked=\"checked\"> Create Mailing list<br />");
		  $wgOut->addHTML("        <input type=checkbox name=\"wpImportStarter\" checked=\"checked\"> Import content from <a href='starter.wikia.com'>starter.wikia.com</a><br />");
		}
$wgOut->addHTML("	</td>

	</tr>
        <tr>
	<td align='right'>$cwd:</td>
	<td align='left'>
	<textarea tabindex='6' name=\"wpCreateWikiDesc\" value=\"\" rows=\"6\" cols=\"60\" />$this->mWikiDesc</textarea>
	</td>
	
	</tr>


	<tr>
	<td align='right'>$cwa:</td>
	<td align='left'>
	<textarea tabindex='7' name=\"wpCreateWikiAddtnl\" value=\"\" rows=\"6\" cols=\"60\" />$this->mWikiAddtnl</textarea>
	</td>
        </tr>



<tr><td align='left' colspan='2'>
	<input tabindex='8' type='submit' onclick=\"clicked='request'\" name=\"actionRequestWiki\" value=\"{$rcw}\" />");
if(in_array( 'createwiki', $wgUser->getRights() )){
  $wgOut->addHTML("	<input tabindex='8' type='submit' onclick=\"clicked='create'\" name=\"actionCreateWiki\" value=\"create this wiki\" />");
  if($this->mLoadID){
    $wgOut->addHTML("	<input type='hidden' name=\"load_id\" value=\"$this->mLoadID\" />");
    $wgOut->addHTML("	<input tabindex='8' type='submit' onclick=\"clicked='changed'\" name=\"actionChangeRequest\" value=\"save changes\" />");
    $wgOut->addHTML("	<input tabindex='8' type='submit' onclick=\"clicked='deny'\" name=\"actionDenyRequest\" value=\"deny request\" />");
  }
}
 $wgOut->addHTML("</td></tr>");

if(in_array( 'createwiki', $wgUser->getRights() )){
  $wgOut->addHTML("	<tr><td colspan=2><select name=req_id>");
  $dbw = wfGetDB(DB_MASTER);
  $res = $dbw->select("`wikicities`.request_list",
			 array('req_id','req_name'),
			 array('req_state' => "new"));
  $wgOut->addHTML($dbw->numRows($res));
  while($obj = $dbw->fetchObject( $res )){
    
    $wgOut->addHTML("<option value = \"$obj->req_id\">$obj->req_name</option>");
  }

  $wgOut->addHTML("</select><input tabindex='8' type='submit' onclick=\"clicked='load'\"name=\"actionLoadRequest\" value=\"load request\" /></td></tr>");
}

  $wgOut->addHTML("<tr><td colspan='2'>
        {$nvt}
</td></tr>
");
		    
	    
		$wgOut->addHTML("</table></form>\n" );
		$wgOut->addHTML( $endText );
	}

	/**
	 * @access private
	 */
	function setupMessages() {
	  global $wgMessageCache;
	  $wgMessageCache->addMessages( array('createwiki' => 'Request a new wiki',
					      'createwikipagetitle' => 'Request a new wiki',
					      'createwikilogin' => 'Please <a href="/index.php?title=Special:Userlogin&returnto=Special:CreateWiki" class="internal" title="create an account or log in">create an account or log in</a> before requesting a wiki.',
					      'createwikistafftext' => 'You are staff, so you can create a new wiki using this page',
					      'createwikitext' => 'You can request a new wiki be created on this page.  Just fill out the form',
					      'createwikititle' => 'Title for the wiki',
					      'createwikiname' => 'Name for the wiki',
					      'createwikinamevstitle' => 'The name for the wiki differs from the title of the wiki in that the name is what will be used to determine the default url.  For instance, a name of "starwars" would be accessible as http://starwars.wikia.com/. The title of the wiki may contain spaces, the name should only contain letters and numbers.',
					      'createwikidesc' => 'Description of the wiki',
					      'createwikiaddtnl' => 'Additional Information',
					      'createwikimailsub' => 'Wikia request',
					      'requestcreatewiki' => 'Request Wiki',
					      'createwikisubmitcomplete' => 'Your submission is complete.  If you gave an email address, you will be contacted regarding the new Wiki.  Thank you for using {{SITENAME}}.',
					      'createwikicreatecomplete' => 'Your wiki creation is complete.  ',
					      'createwikichangecomplete' => 'Your changes have been saved.',
					      'createwikilang' => 'Default language for this wiki',
                                        'createwikibademail' => "The email you provided is not a proper email address, please retype it correctly.",
					      ) );
	}
	
	
	function hasSessionCookie() {
	  global $wgDisableCookieCheck;
	  return ( $wgDisableCookieCheck ) ? true : ( '' != $_COOKIE[session_name()] );
	}
	  
	/**
	 * @access private
	 */
	function cookieRedirectCheck( $type ) {
		global $wgOut, $wgLang;

		$titleObj = Title::makeTitle( NS_SPECIAL, 'Userlogin' );
		$check = $titleObj->getFullURL( 'wpCookieCheck='.$type );

		return $wgOut->redirect( $check );
	}

	/**
	 * @access private
	 */
	function onCookieRedirectCheck( $type ) {
		global $wgUser;

		if ( !$this->hasSessionCookie() ) {
			if ( $type == 'new' ) {
				return $this->mainLoginForm( wfMsg( 'nocookiesnew' ) );
			} else if ( $type == 'login' ) {
				return $this->mainLoginForm( wfMsg( 'nocookieslogin' ) );
			} else {
				# shouldn't happen
				return $this->mainLoginForm( wfMsg( 'error' ) );
			}
		} else {
			return $this->successfulLogin( wfMsg( 'loginsuccess', $wgUser->getName() ) );
		}
	}

	/**
	 * @access private
	 */
	function throttleHit( $limit ) {
		global $wgOut;

		$wgOut->addWikiText( wfMsg( 'acct_creation_throttle_hit', $limit ) );
	}
}

SpecialPage::addPage( new CreateWikiForm );
global $wgMessageCache;
$wgMessageCache->addMessage( 'createwiki', 'Create a wiki' );
}
?>
