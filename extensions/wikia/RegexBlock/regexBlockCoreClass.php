<?php
/**
 * Extension used for blocking users names and IP addresses with regular expressions.
 * Contains both the blocking mechanism and a special page to add/manage blocks.
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Bartek Lapinski <bartek@wikia.com>
 * @author Tomasz Klim <tomek@wikia.com>
 * @copyright Copyright (C) 2007 Bartek Lapinski, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */


$wgExtensionCredits['other'][] = array(
	'name' => 'RegexBlock Engine 2.0 Core',
	'description' => 'core regular expression matching engine',
	'author' => 'Tomasz Klim'
);


global $wgContactLink;
if ( $wgContactLink == '' ) {
    $wgContactLink = '[[Special:Contact|contact Wikia]]';
}

/* these users may be innocent - we do want them to contact Wikia if they are */
define ('REGEXBLOCK_REASON_IP', "This IP address is prevented from editing due to vandalism or other disruption by you or by someone who shares your IP address. If you believe this is in error, please $wgContactLink");

/* we do not really want these users to contact Wikia about the problem - those are vandals */
define ('REGEXBLOCK_REASON_NAME', "This username is prevented from editing due to vandalism or other disruption. If you believe this is in error, please $wgContactLink");
define ('REGEXBLOCK_REASON_REGEX', "This username is prevented from editing due to vandalism or other disruption by a user with a similar name. Please create an alternate user name or $wgContactLink about the problem.");

/* memcached expiration time (0 - infinite) */
define ('REGEXBLOCK_EXPIRE', 0);


class RegexBlockCore
{
    var $memc;
    var $dbr;
    var $dbw;
    var $table_blocked;
    var $table_stats;
    var $shareddb;

    private function __construct( $memc, $dbr, $dbw, $shareddb, $table_blocked, $table_stats ) {
	$this->memc = $memc;
	$this->dbr =& $dbr;
	$this->dbw =& $dbw;
	$this->shareddb = $shareddb;
	$this->table_blocked = $table_blocked;
	$this->table_stats = $table_stats;
    }


    final public static function &singleton( $memc, &$dbr, &$dbw, $shareddb = "wikicities", $table_blocked = "blockedby", $table_stats = "stats_blockedby" ) {
	static $instance;
	if ( !isset( $instance ) ) {
	    $instance = new RegexBlockCore( $memc, $dbr, $dbw, $shareddb, $table_blocked, $table_stats );
	}
	return $instance;
    }


    // prepare data by getting blockers 
    final public function check( $user_ip, $username ) {
	if ( !isset( $username ) || $username == '' ) return false;
	$out = false;
	$key = "$this->shareddb:regexBlockCore:blockers";
	$cached = $this->memc->get( $key );

	if ( !is_array( $cached ) ) {

	    /* get from database */
	    $blockers_array = array();
	    $query = "SELECT blckby_blocker FROM $this->table_blocked GROUP BY blckby_blocker";
	    $res = $this->dbr->query( $query );

	    while ( $row = $this->dbr->fetchObject( $res ) ) {
		$result = $this->getRegexBlocked( $row->blckby_blocker, $username, $user_ip );
		$blockers_array[] = $row->blckby_blocker;
		if ( $result ) $out = $result;
	    }

	    $this->dbr->freeResult( $res );
	    $this->memc->set( $key, $blockers_array, REGEXBLOCK_EXPIRE );

	} else {
	    /* get from cache */
	    foreach ( $cached as $blocker ) {
		$result = $this->getRegexBlocked( $blocker, $username, $user_ip );
		if ( $result ) $out = $result;
	    }
	}

	return $out;
    }


    /*  the actual blocking goes here, for each blocker
	@param $blocker String
	@param $username User
	@param $user_ip String
    */
    private function getRegexBlocked( $blocker, $username, $user_ip ) {
	$out = false;
	$result = $this->getData( $blocker );
	$ips   = $result["ips"];
	$names = $result["regex"];
	$exact = $result["exact"];

	$result["ips"  ]["matches"] = $this->performMatch( $ips, $user_ip );
	$result["regex"]["matches"] = $this->performMatch( $names, $username );
	$result["exact"]["matches"] = $this->performMatch( $exact, $username, true );

	/* run expire checks for all matched values
	   this is only for determining validity of this block, so
	   a first successful match means the block is applied
	*/
	foreach ( $result as $key => $value ) {
	    $is_ip = ( "ips" == $key ? 1 : 0 );

	    /* check if this block hasn't expired already  */
	    $valid = $this->expireCheck( $result[$key]["matches"], $is_ip );

	    if ( is_array( $valid ) ) {
		break;
	    }
	}

	if ( is_array( $valid ) ) {
	    $out = array();
	    $out['blocker'] = $blocker;

	    if ( $valid['reason'] != "" ) {  /* a reason was given, display it */
		$out['reason'] = $valid['reason'];
	    } else if ( 1 == $valid['ip'] ) { /* we blocked by IP */
		$out['reason'] = REGEXBLOCK_REASON_IP;
	    } else if ( 1 == $valid['exact'] ) { /* we blocked by username exact match */
		$out['reason'] = REGEXBLOCK_REASON_NAME;
	    } else { /* we blocked by regex match */
		$out['reason'] = REGEXBLOCK_REASON_REGEX;
	    }

	    /* account creation check goes through the same hook... */
	    $out['blockcreateaccount'] = ( $valid['create'] == 1 ? true : false );

	    $this->updateStats( $username, $user_ip, $blocker, $valid['match'] );
	}

	return $out;
    }


    /*  fetch usernames or IP addresses to run a match against
	@param $blocker String: the admin who blocked
	@return Array: an array of arrays to run a regex match against
    */
    private function getData( $blocker ) {

	$names = array ( "ips" => "", "exact" => "", "regex" => "" );

	$mask = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.(?:xxx|\d{1,3})$/';

	/* first, check if regex strings are already stored in memcache */
	/* we will store entire array of regex strings here */

	$key = str_replace( ' ', '_', "$this->shareddb:regexBlockCore:blocker:$blocker" );
	$cached = $this->memc->get( $key );

	if ( "" == $cached ) {

	    /* fetch data from db, concatenate into one string, then fill cache */
	    $query = "SELECT blckby_name, blckby_exact 
		      FROM $this->table_blocked
		      WHERE blckby_blocker = {$this->dbr->addQuotes($blocker)}";
	    $res = $this->dbr->query( $query );

	    /* run through all and split it into three strings */
	    while ( $row = $this->dbr->fetchObject( $res ) ) {
		if ( preg_match( $mask, $row->blckby_name ) ) {
		    $names["ips"][] = $row->blckby_name;
		} else if ( $row->blckby_exact != 0 ) {
		    $names["exact"][] = $row->blckby_name;
		} else {
		    $names["regex"][] = $row->blckby_name;
		}
	    }

	    $this->memc->set( $key, $names, REGEXBLOCK_EXPIRE );
	    $this->dbr->freeResult( $res );

	} else {
	    /* take from cache */
	    $names = $cached ;
	}	

	return $names ;
    }


    /*  perform a match against all given values 
	@param $matching Array: array of strings containing list of values
	@param $value String: a given value to run a match against
	@param $exact Boolean: whether or not perform an exact match
	@return Array of matched values or false
    */
    private function performMatch( $matching, $value, $exact = false ) {
	$matched = array();

	if ( !is_array( $matching ) ) { /* empty? begone! */
	    return false;
	}

	/* normalise for regex */
	foreach ( $matching as $one ) { /* the real deal */
	    $regex = ( $exact ? '/^' . $one . '$/' : '/' . $one . '/i' );

	    if ( preg_match( $regex, $value, $match ) ) {
		$matched[] = $one;
	    }
	}

	return $matched;
    }


    /*  check if the block expired or not (AFTER we found an existing block)
	@param $currentusername User: current user object
	@param $names Array: matched names
	@param $ips Array: matched ips
	@return Array or false
    */
    private function expireCheck( $array_match = null, $ips = 0 ) {

	/* I will use memcache, with the key being particular block */
	if ( empty( $array_match ) ) {
	    return false;
	}

	$ret = array();

	/* for EACH match check whether timestamp expired until found VALID timestamp
	   but: only for a BLOCKED user, and it will be memcached 
	   moreover, expired blocks will be consequently deleted
	*/
	foreach ( $array_match as $single ) {

	    $key = "$this->shareddb:regexBlockCore:blocked:$single";
	    $cached = $this->memc->get( $key );
	    if ( !is_object( $cached ) ) {

		/* get from database */
		$query = "SELECT blckby_timestamp, blckby_expire, blckby_blocker, blckby_create, blckby_exact, blckby_reason 
			  FROM $this->table_blocked
			  WHERE blckby_name like {$this->dbr->addQuotes('%'.$single.'%')}";

		$res = $this->dbr->query( $query );
		if ( $row = $this->dbr->fetchObject( $res ) ) {

		    /* if still valid or infinite, ok to block user */
		    if ( ( $this->timestamp() <= $row->blckby_expire) || ( 'infinite' == $row->blckby_expire ) ) {
			$ret['create'] = $row->blckby_create;
			$ret['exact'] = $row->blckby_exact;
			$ret['reason'] = $row->blckby_reason;
			$ret['match'] = $single;
			$ret['ip'] = $ips;
			$this->memc->set( $key, $row );
			$this->dbr->freeResult( $res );
			return $ret;
		    } else {  /* clean up an obsolete block */
			$this->clearExpired( $single, $row->blckby_blocker );
		    }
		}
		$this->dbr->freeResult( $res );

	    } else {
		/* get from cache */ 
 		if ( ( $this->timestamp() <= $cached->blckby_expire ) || ( 'infinite' == $cached->blckby_expire ) ) {
		    $ret['create'] = $cached->blckby_create;
		    $ret['exact'] = $cached->blckby_exact;
		    $ret['reason'] = $cached->blckby_reason;
		    $ret['match'] = $single;
		    $ret['ip'] = $ips;
		    return $ret;
		} else {  /* clean up an obsolete block */
		    $this->clearExpired( $single, $cached->blckby_blocker );
		}
	    }
	}

	return false;
    }


    /*  clean up an existing expired block 
	@param $username String: name of the user
	@param $blocker String: name of the blocker 
    */
    private function clearExpired( $username, $blocker ) {
	$query = "DELETE FROM $this->table_blocked
		  WHERE blckby_name = ".$this->dbw->addQuotes( $username );
	$this->dbw->query( $query );
	if ( $this->dbw->affectedRows() ) {

	    /* success, remember to delete cache key  */
	    $this->memc->delete( "$this->shareddb:regexBlockSpecial:numResults" );
	    $this->memc->delete( "$this->shareddb:regexBlockCore:blocker:$blocker" );
	    $this->memc->delete( "$this->shareddb:regexBlockCore:blockers" );
	    $this->memc->delete( "$this->shareddb:regexBlockCore:blocked:$username" );
	    return true;
	}
	return false;
    }


    /*  put the stats about block into database 
	@param $currentusername Current user object
	@param $user_ip String: IP of the current user
	@param $blocker String
    */
    private function updateStats( $currentusername, $user_ip, $blocker, $match ) {
	global $wgServer;
	$now = $this->timestamp();

	$query = "INSERT INTO $this->table_stats
		  (stats_id, stats_user, stats_ip, stats_blocker, stats_timestamp, stats_match, stats_wiki)
		   values (
			null,
			{$this->dbw->addQuotes($currentusername)},
			{$this->dbw->addQuotes($user_ip)},
			{$this->dbw->addQuotes($blocker)},
			'{$now}',
			{$this->dbw->addQuotes($match)},
			{$this->dbw->addQuotes($wgServer)}
		   )";

	$this->dbw->query( $query );
	return ( $this->dbw->affectedRows() ? true : false );
    }


    private function timestamp() {
	return gmdate( 'YmdHis', time() );  // don't change it to wfTimestampNow() !
    }
}


?>
