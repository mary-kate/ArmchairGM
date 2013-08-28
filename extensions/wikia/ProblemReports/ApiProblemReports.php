<?php
/**
 * Api interface for ProblemReports extension, adding/editing/removing of reports
 * and actions logging to Special:Log made easier and faster
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Maciej Brencz <macbre@wikia.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 */

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	exit( 1 );
}

class ApiProblemReports
{
	var $userGroups; // user groups

	var $mDB; // DB handler
	
	var $problem_reports_tbl;
	
	var $problemTypes;
	
	function ApiProblemReports()
	{
		global $wgUser, $wgSharedDB;
		
		$this->userGroups = $wgUser->getGroups();
		$this->mDB =& wfGetDB( DB_SLAVE );
		
		if( empty( $wgSharedDB ) ) {
			$this->problem_reports_tbl = 'problem_reports';
		} else {
			$this->problem_reports_tbl = "`$wgSharedDB`.`problem_reports`";
		}
	
		//print_pre($this);
	}
	
	// adds report do database
	function addReport($data)
	{
		global $wgCityId, $wgServer, $wgSharedDB, $wgUser;
	
		// add row to problem_reports table (use DB_MASTER !!!)
		$dbw =& wfGetDB( DB_MASTER );

		$values = array(
			'pr_cat'     => $data['cat'],
			'pr_summary' => $data['summary'],
			'pr_ns'      => $data['ns'],
			'pr_title'   => $data['title'],
			'pr_city_id' => ($wgCityId > 0) ? $wgCityId : 831, // for local tests
			'pr_server'  => $wgServer, // wikia hostname like 'muppet.wikia.com' or 'fp012.sjc.wikia-inc.com'
			'pr_anon_reporter' => $wgUser->isLoggedIn() ? 0 : 1, // is reporting user logged in
			'pr_reporter'=> $data['reporter'],
			'pr_ip'      => ip2long(wfGetIP()), // save some bytes
			'pr_email'   => $data['email'],
			'pr_date'    => date('Y-m-d H:i:s'),
			'pr_status'  => 0 // awaits
		);
		$dbw->begin();
		$dbw->insert( $this->problem_reports_tbl, $values, __METHOD__ );
		$insertId = (int) $dbw->insertId();
		$dbw->commit();

		// log if succesfull
		if ($insertId)
		{
			// add the log entry for problem reports
			$log = new LogPage('pr_rep_log', true); // true: also add entry to Special:Recentchanges
			
			$reportedTitle = Title::newFromText($data['title'], $data['ns']);
			$desc = 'reported a problem';
			
			$log->addEntry('prl_rep', $reportedTitle, /*$data['summary']*/ '', array
			(
				$reportedTitle->getFullURL(),
				$insertId
			) );
			
			$dbw->immediateCommit(); // do commit (MW 'forgets' to do it)
			
			// ok!
			wfDebug('ProblemReports: report #'.$insertId." reported and log added to Special:Log...\n");
		}
		else
		{
			wfDebug('ProblemReports: report #'.$insertId." NOT reported!\n");
		}
		
		return $insertId;
	}
	
	// returns list of reports
	function getReports($limit = 50, $offset = 0, $cityId = -1, $problem = -1, $archived = false, $staff = false)
	{
		// limit, offset - mySQL typical stuff
		// cityId - get reports only from given cityId wikia or all Wikias (cityId == -1)
		// problem - type of problems of which reports should be listed (-1 - don't care)
		// archived - show only fixed / not_a_problem reports
		// staff - show only reports marked as 'need staff help'
		
		$sql = 'SELECT * FROM ' . $this->problem_reports_tbl . ' WHERE 1=1 '.
			($cityId > 0 ? ' AND pr_city_id = ' . $cityId : '') . 
			($problem > -1 ? ' AND pr_cat = ' . $problem : '') . 
			($archived        ? ' AND pr_status IN (1,2)' : '') .
			($staff           ? ' AND pr_status = 3'      : '').
			(!$archived && !$staff ? ' AND pr_status IN (0,3)' : '').
			' ORDER BY pr_date DESC LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
		
		//print_pre('SQL: '. $sql);
		
		return $this->mDB->query($sql);
	}
	
	// update report in database
	function updateReport($id, $status)
	{
		global $wgCityId, $wgServer;
		
		// update rows in problem_reports table (use DB_MASTER !!!)
		$dbw =& wfGetDB( DB_MASTER );
	
		// are you staff?
		if($this->isStaff())
		{
			$sql_where = 'pr_id ='.(int) $id; // yes, you can update all reports
		}
		else
		{ 
			$sql_where = 'pr_id ='.(int) $id.' AND pr_city_id= '.(int) $wgCityId; // no, you can only update reports from your wiki
		}
	
		$sql = 'UPDATE '.$this->problem_reports_tbl.' SET pr_status = '.(int) $status.' WHERE '.$sql_where;
		
		$dbw->begin();
		$dbw->query($sql);
		$ret = $dbw->affectedRows() > 0; // did we actually update any row?
		
		
		if ($ret)
		{
			// add the log entry for problem reports
			$log = new LogPage('pr_rep_log', true); // true: also add entry to Special:Recentchanges
			
			$log->addEntry('prl_chn', new Title(), '', array
			(
				new Title(),
				$id,
				$status
			) );
			
			$dbw->immediateCommit(); // do commit (MW 'forgets' to do it)
			
			// ok!
			wfDebug('ProblemReports: report #'.$id." updated and log added to Special:Log...\n");
		}
		
		$dbw->commit();
		
		return $ret;
	}
	
	
	// remove report from database
	// 
	// actually set report status to 10 (won't be displayed anywere)
	function removeReport($id)
	{
		global $wgCityId, $wgServer;
		
		// update rows in problem_reports table (use DB_MASTER !!!)
		$dbw =& wfGetDB( DB_MASTER );
	
		// you are not staff?
		if(!$this->isStaff())
		{
			return false;
		}

		$sql = 'DELETE FROM '.$this->problem_reports_tbl.' WHERE pr_id = '.(int) $id;
		
		$dbw->begin();
		$dbw->query($sql);
		

		// add the log entry for problem reports
		$log = new LogPage('pr_rep_log', true); // true: also add entry to Special:Recentchanges
		
		$log->addEntry('prl_rem', new Title(), '', array
		(
			new Title(),
			$id
		) );
		
		$dbw->immediateCommit(); // do commit (MW 'forgets' to do it)
		
		// ok!
		wfDebug('ProblemReports: report #'.$id." removed by staff member and log added to Special:Log...\n");

		$dbw->commit();
		
		return true;
	}
	
	// return amount of all reports
	function countReports($cityId = -1, $problem = -1, $archived = false, $staff = false)
	{
		$sql = 'SELECT COUNT(*) as cnt FROM ' . $this->problem_reports_tbl . ' WHERE 1=1'.
			($cityId  > 0     ? ' AND pr_city_id = ' . $cityId  : '').
			($problem != -1   ? ' AND pr_cat = '     . $problem : '').
			($archived        ? ' AND pr_status IN (1,2)' : '') .
			($staff           ? ' AND pr_status = 3'      : '').
			(!$archived && !$staff ? ' AND pr_status IN (0,3)' : '');
		
		//print_pre($sql);
		
		$obj = $this->mDB->fetchObject($this->mDB->query($sql));
		
		return $obj->cnt;
	}
	
	// check whether provided problem description contains spam-like things: words, hostnames etc
	// based on SpamBlacklist extension
	function checkForSpam($content)
	{
		global $wgTitle, $IP, $wgUser, $wgParser;
	
		if ( !function_exists('wfSpamBlacklistLoader') )
		{
			// extension is not loaded! fallback - not spam...
			wfDebug('Install SpamBlacklist extension to check for spam in problem reports!');
			return false;
		}
		
		// load SpamBlacklist extension...
		require_once("$IP/extensions/SpamBlacklist/SpamBlacklist_body.php");
		
		// perform check (based on SpamBlackList::filter)
		wfProfileIn(__METHOD__);
		
		// some settings stuff
		$settings = array
		(
			'files' => array( 'http://meta.wikimedia.org/w/index.php?title=Spam_blacklist&action=raw&sb_ver=1' ),
			'title' => $wgTitle,
			'text'  => $content,
			
			'regexes' => false,
			
			'warningTime'   => 8 * 3600,	// 8h - after when try to get new version of regexps file
			'expiryTime'    => 10 * 3600,	// 10h - how long should we keep regexps in memcache
			'warningChance' => 100,		// posibility of HTTP request after 'warningTime' elapses
			
			'memcache_file'    => 'spam_blacklist_file',
			'memcache_regexes' => 'spam_blacklist_regexes'
		);
		
		// do filtering
		$spamList = new SpamList_helper($settings);

		$regexes    = $spamList->getRegexes();
		$whitelists = $spamList->getWhitelists();

		if ( is_array( $regexes ) ) 
		{
			# Run parser to strip SGML comments and such out of the markup
			# This was being used to circumvent the filter (see bug 5185)
			$options = new ParserOptions();
			$text = $wgParser->preSaveTransform( $content, $wgTitle, $wgUser, $options );
			$out = $wgParser->parse( $content, $wgTitle, $options );
			
			$links = implode( "\n", array_keys( $out->getExternalLinks() ) );

			# Strip whitelisted URLs from the match
			if( is_array( $whitelists ) ) 
			{
				wfDebug( "Excluding whitelisted URLs from " . count( $whitelists ) . " regexes: " . implode( ', ', $whitelists ) . "\n" );
				foreach( $whitelists as $regex ) 
				{
					$links = preg_replace( $regex, '', $links );
				}
			}

			# Do the match
			//wfDebug( "Checking text against " . count( $regexes ) . " regexes: " . implode( ', ', $regexes ) . "\n" );
			wfDebug( "Checking text against " . count( $regexes ) . " regexes\n" );
			$retVal = false;
			foreach( $regexes as $regex ) 
			{
				if ( preg_match( $regex, $links, $matches ) ) 
				{
					wfDebug( "\n".' -- Match: "'.$matches[0].'"' );
					$retVal = true;
					break;
				}
			}
		} 
		else 
		{
			$retVal = false;
		}
		
		wfDebug("\n".' -- Spam check result: ' . ($retVal ? 'spam found :(' : 'spam not found :D') ."\n");

		wfProfileOut( __METHOD__ );
		return $retVal;
	}

	function isStaff()
	{
		return in_array( 'staff', $this->userGroups);
	}
	
	function isAdmin()
	{
		return in_array( 'sysop', $this->userGroups) || in_array( 'staff', $this->userGroups ) || in_array( 'janitor', $this->userGroups ) || in_array( 'helper', $this->userGroups );
	}
	
	
	
	static function makeActionText( $key, $title, $params, $skin )
	{
		global $wgLogActions, $wgOut, $wgTitle;
		
		wfProfileIn(__METHOD__);
	
		//$titleLink = '<a href="'.$title->getFullURL().'">'.$title->getPrefixedText().'</a>';
		$titleLink = '[['.$title->getNsText().':'.$title->getPrefixedText().']]';
		//$specialLink = Title::newFromText('ProblemReports')->getFullURL();
		
		// additional information
		switch($key)
		{
			// problem is reported
			case 'pr_rep_log/prl_rep':
				$rt = wfMsg( $wgLogActions[$key], $titleLink, '[[Special:ProblemReports|#'.$params[1].']]' );
				break;
		
			// problem reports status is changed
			case 'pr_rep_log/prl_chn':
				$rt = wfMsg( $wgLogActions[$key], '[[Special:ProblemReports|#'.$params[1].']]', ucfirst(wfMsg('pr_status_'.$params[2])) );
				break;
				
			// problem is removed
			case 'pr_rep_log/prl_rem':
				$rt = wfMsg( $wgLogActions[$key], '[[Special:ProblemReports|#'.$params[1].']]' );
				break;
		}

		// another dirty hack (serve WikiText when saving to recent changes, serve xHTML when displaying Special:Log)
		$rt = ($wgTitle->getText() == 'Log' && $wgTitle->getNamespace() == NS_SPECIAL ) ? substr($wgOut->parse($rt), 3, -4) : $rt;
		
		wfProfileOut(__METHOD__);
		
		return $rt;
	}
}

?>
