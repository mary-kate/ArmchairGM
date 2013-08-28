<?PHP
/**#@+
*      Extension used for blocking users names and IP addresses with regular expressions. Contains both the blocking mechanism and a special page to add/manage blocks
*
* @package MediaWiki
* @subpackage SpecialPage
*
* @author Bartek
* @copyright Copyright © 2007, Wikia Inc.
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
*/


/* generic reasons */
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


/* add hook */
global $wgHooks;
$wgHooks['GetBlockedStatus'][] = 'wfRegexBlockCheck';

/* 
	prepare data by getting blockers 
	@param $current_user User: current user  
*/
function wfRegexBlockCheck ($current_user) {
	global $wgMemc, $wgSharedDB ;
        if (!wfSimplifiedRegexCheckSharedDB())
	                return ;
	$ip_to_check = wfGetIP () ;
	$key = "$wgSharedDB:regexBlockCore:blockers" ;
	$cached = $wgMemc->get ($key) ;
	if (!is_array($cached)) {
		/* get from database */
		$blockers_array = array () ;
		$dbr =& wfGetDB (DB_SLAVE);	
		$query = "SELECT blckby_blocker 
			  FROM ".wfRegexBlockGetTable()." 
			  GROUP BY blckby_blocker" ;

		$res = $dbr->query($query) ;
	        while ( $row = $dbr->fetchObject( $res ) ) {
			wfGetRegexBlocked ($row->blckby_blocker, $current_user, $ip_to_check) ;
			array_push ($blockers_array, $row->blckby_blocker) ;	
	        }
	        $dbr->freeResult ($res) ;
		$wgMemc->set ($key, $blockers_array, REGEXBLOCK_EXPIRE) ;
	} else {
		/* get from cache */
		foreach ($cached as $blocker) {
			wfGetRegexBlocked ($blocker, $current_user, $ip_to_check) ;
		}		
	}
}

/* 
	fetch usernames or IP addresses to run a match against
	@param $blocker String: the admin who blocked
	@param $user User: current user
	@return Array: an array of arrays to run a regex match against
*/
function wfGetRegexBlockedData ($blocker, $user) {
	global $wgMemc, $wgSharedDB ;
	$names = array (
			"ips" => "",
			"exact" => "",
			"regex" => ""
		)  ;

	/* first, check if regex strings are already stored in memcache */
	/* we will store entire array of regex strings here */

	$key = str_replace( ' ', '_', "$wgSharedDB:regexBlockCore:blocker:$blocker" );
	$cached = $wgMemc->get ($key) ;
	if ( "" == $cached ) {
		/* fetch data from db, concatenate into one string, then fill cache */
		$dbr =& wfGetDB( DB_SLAVE ) ;
		$query = "SELECT blckby_name, blckby_exact 
			  FROM ".wfRegexBlockGetTable()." 
			  WHERE blckby_blocker = {$dbr->addQuotes($blocker)}" ;

		$res = $dbr->query ($query) ;

		/* run through all and split it into three strings */
		while ( $row = $dbr->fetchObject( $res ) ) {
			$is_ip = $user->isIP($row->blckby_name) ;
		       	if ($is_ip != 0) {
				$names["ips"][] = $row->blckby_name ;
			} else if ($row->blckby_exact != 0) {
				$names["exact"][] = $row->blckby_name ;
			} else {
				$names["regex"][] = $row->blckby_name ;
			}	
		}
		$wgMemc->set ($key, $names, REGEXBLOCK_EXPIRE) ; 
		$dbr->freeResult ($res) ;
	} else {
		/* take from cache */
		$names = $cached ;
	}	
	return $names ;
}

/*	perform a match against all given values 
	@param $matching Array: array of strings containing list of values
	@param $value String: a given value to run a match against
	@param $exact Boolean: whether or not perform an exact match
	@return Array of matched values or false
*/
function wfRegexBlockPerformMatch ($matching, $value, $exact = false ) {
	$matched = array () ;
	if (!is_array($matching)) { /* empty? begone! */
		return false ;
	}
	/* normalise for regex */
	foreach ($matching as $one) { /* the real deal */
		($exact) ? $regex = '/^'.$one.'$/' : $regex = '/'.$one.'/i' ;
		$found = preg_match ($regex, $value, $match) ;
		if ($found) {
			$matched[] = $one;
		}
	}
	return $matched ;
}

/*
	check if the block expired or not (AFTER we found an existing block)
	@param $user User: current user object
	@param $names Array: matched names
	@param $ips Array: matched ips
	@return Array or false
*/
function wfRegexBlockExpireCheck ($user, $array_match = null, $ips = 0) {	
	global $wgMemc, $wgSharedDB ;
	/* I will use memcache, with the key being particular block
	*/
	if (empty($array_match)) {
		return false ;
	}
	if (1 == $ips) {
		$username = wfGetIP () ;
	} else {
		$username = $user->getName () ;
	}
	$ret = array() ;
	/* for EACH match check whether timestamp expired until found VALID timestamp
           but: only for a BLOCKED user, and it will be memcached 
	   moreover, expired blocks will be consequently deleted
	*/
	foreach ($array_match as $single) {
        	$key = "$wgSharedDB:regexBlockCore:blocked:$single" ;
	        $cached = $wgMemc->get ($key) ;
		if ( !is_object ($cached) ) {
			/* get from database */
			$dbr =& wfGetDB (DB_SLAVE) ;
			$query = "SELECT blckby_timestamp, blckby_expire, blckby_blocker, blckby_create, blckby_exact, blckby_reason 
			  	  FROM ".wfRegexBlockGetTable()." 
			          WHERE blckby_name like {$dbr->addQuotes('%". $dbr->escapeLike( $single )  ."%')}" ;

			$res = $dbr->query ($query) ;
			if ($row = $dbr->fetchObject ($res) ) {
				/* if still valid or infinite, ok to block user */
				if ((wfTimestampNow () <= $row->blckby_expire) || ('infinite' == $row->blckby_expire)) {
					$ret['create'] = $row->blckby_create ;
					$ret['exact'] = $row->blckby_exact ;
					$ret['reason'] = $row->blckby_reason ; 
					$ret['match'] = $single ;
					$ret['ip'] = $ips ;
					$wgMemc->set ($key, $row) ;
					$dbr->freeResult ($res) ;
					return $ret ;
				} else {  /* clean up an obsolete block */
					wfRegexBlockClearExpired ($single, $row->blckby_blocker) ;
				}
       	 		}
			$dbr->freeResult ($res) ;
		} else {
		       	/* get from cache */ 
 			if ((wfTimestampNow () <= $cached->blckby_expire) || ('infinite' == $cached->blckby_expire)) {
                        	$ret['create'] = $cached->blckby_create ;
                                $ret['exact'] = $cached->blckby_exact ;
                                $ret['reason'] = $cached->blckby_reason ;
				$ret['match'] = $single ;
				$ret['ip'] = $ips ;
                                return $ret ;
                        } else {  /* clean up an obsolete block */
                                wfRegexBlockClearExpired ($single, $cached->blckby_blocker) ;
                        }
		}
	}
	return false ;
}

/* clean up an existing expired block 
   @param $username String: name of the user
   @param $blocker String: name of the blocker 
*/
function wfRegexBlockClearExpired ($username, $blocker) {
	$dbw =& wfGetDB( DB_MASTER );
        $query = "DELETE FROM ".wfRegexBlockGetTable()." 
		  WHERE blckby_name = ".$dbw->addQuotes($username) ;

        $dbw->query ($query) ;
        if ( $dbw->affectedRows() ) {
        	/* success, remember to delete cache key  */
                wfRegexBlockUnsetKeys ($blocker, $username) ;
		return true ;
        }
	return false ;
}

/* put the stats about block into database 
   @param $username String
   @param $user_ip String: IP of the current user
   @param $blocker String
*/
function wfRegexBlockUpdateStats ($user, $user_ip, $blocker, $match) {
	global $wgSharedDB, $wgServer ;
	$dbw =& wfGetDB( DB_MASTER );
	$now = wfTimestampNow () ;	
	
	$query = "INSERT INTO ".wfRegexBlockGetStatsTable()." 
		  (stats_id, stats_user, stats_ip, stats_blocker, stats_timestamp, stats_match, stats_wiki) 
		   values (
				null, 
				{$dbw->addQuotes($user->getName ())}, 
				{$dbw->addQuotes($user_ip)},
				{$dbw->addQuotes($blocker)},
				'{$now}',
				{$dbw->addQuotes($match)},
				{$dbw->addQuotes($wgServer)}
		   )" ;

	$res = $dbw->query ($query) ;
	if ( $dbw->affectedRows()  ) {
		return true ;
	}
	return false ;
}

/* 	
  the actual blocking goes here, for each blocker
  @param $blocker String
  @param $user User
  @param $user_ip String
*/
function wfGetRegexBlocked ($blocker, $user, $user_ip) {
	$result = wfGetRegexBlockedData ($blocker, $user);
	$username = $user->getName () ;
	$ips = $result ["ips"] ;
	$names = $result ["regex"] ;
	$exact = $result ["exact"] ; 

	$result["ips"]["matches"] = wfRegexBlockPerformMatch ($ips, $user_ip) ;
	$result["regex"]["matches"] = wfRegexBlockPerformMatch ($names, $username) ;
	$result["exact"]["matches"] = wfRegexBlockPerformMatch ($exact, $username, true) ;

	/* run expire checks for all matched values
	   this is only for determining validity of this block, so
	   a first successful match means the block is applied
	*/
	foreach ($result as $key => $value) {
		("ips" == $key) ? $is_ip = 1 : $is_ip = 0 ;
		/* check if this block hasn't expired already  */
		$valid = wfRegexBlockExpireCheck ($user, $result[$key]["matches"], $is_ip) ;
		if (is_array ($valid)) {			
			break ;
		}
	}

	if ( is_array ($valid) ) {
		$user->mBlockedby = $blocker ;
		if ($valid['reason'] != "") { /* a reason was given, display it */
			$user->mBlockreason = $valid['reason'] ;
		} else { /* display generic reasons */
			if (1 == $valid['ip']) { /* we blocked by IP */
				$user->mBlockreason = REGEXBLOCK_REASON_IP ;
			} else if (1 == $valid['exact']) { /* we blocked by username exact match */
				$user->mBlockreason = REGEXBLOCK_REASON_NAME ;
			} else { /* we blocked by regex match */
				$user->mBlockreason = REGEXBLOCK_REASON_REGEX ;
			}
		}
		/* account creation check goes through the same hook... */			
		if ($valid['create'] == 1) $user->mBlock->mCreateAccount = 1  ;
						
		wfRegexBlockUpdateStats ($user, $user_ip, $blocker, $valid['match']) ;
	}
	
}

?>
