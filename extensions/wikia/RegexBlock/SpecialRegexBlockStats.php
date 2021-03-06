<?php

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

if(!defined('MEDIAWIKI'))
   die();

/* add data to arrays */
$wgExtensionFunctions[] = 'wfRegexBlockStatsPageSetup';
$wgExtensionCredits['specialpage'][] = array(
   'name' => 'Regex Block Stats',
   'author' => 'Bartek Lapinski, Tomasz Klim',
   'description' => 'Displays block statistics for the regexblock extension'
);

/* special page setup function */
function wfRegexBlockStatsPageSetup () {
   global $IP, $wgMessageCache;
   if (!wfSimplifiedRegexCheckSharedDB())
   	return ;
   require_once($IP. '/includes/SpecialPage.php');
   /* name, restrictions, */
   SpecialPage::addPage(new SpecialPage('Regexblockstats', 'regexblock', false, 'wfRegexBlockStatsCore', false));
   $wgMessageCache->addMessage('regexblockstats', 'Regex Block Statistics');
}

/* special page core function */
function wfRegexBlockStatsCore () {
global $wgOut, $wgUser, $wgRequest ;
        $wgOut->setPageTitle ("Regex Block Stats") ;
	$username = $wgRequest->getVal ('target') ;
	$wgOut->setSubtitle ("for {$username}") ;	
	$scL = new RegexBlockStatsList () ;
        $scL->showList ('',$username) ;
}

/* list class  */
class RegexBlockStatsList {
	var $numResults ;

	/* constructor */
	function RegexBlockStatsList () {
		$this->numResults = 0 ;
	} 
	
        /* show it up */
	function showList ($error, $username) {
		global $wgOut, $wgRequest ;
		/* no list when no user */
		if ("" == $username)
			return false ;
		$this->fetchNumResults ($username) ;
		$this->showPrevNext ($wgOut) ;
		$this->fetchLogs ($username) ;
		$this->showPrevNext ($wgOut) ;		
	}

        /* fetch number of all rows */
        function fetchNumResults ($username) {
                global $wgMemc, $wgSharedDB ;
                $dbr = &wfGetDB (DB_SLAVE) ;
                $query_count = "SELECT COUNT(*) as n 
				FROM ".wfRegexBlockGetStatsTable()." 
				WHERE stats_user = ".$dbr->addQuotes ($username) ;

                $res_count = $dbr->query($query_count) ;
                $row_count = $dbr->fetchObject ($res_count);
                $this->numResults = $row_count->n ;
                $dbr->freeResult ($res_count) ;
        }

	/* fetch all logs */
	function fetchLogs ($username) {
        	global $wgOut, $wgSharedDB, $wgDBname, $wgRequest, $wgLang ;
		/* from database */
                list( $limit, $offset ) = $wgRequest->getLimitOffset();
		$dbr =& wfGetDB (DB_SLAVE);
		$query = "SELECT stats_user, stats_blocker, stats_timestamp, stats_ip, stats_match, stats_wiki   
			  FROM ".wfRegexBlockGetStatsTable()." 
			  WHERE stats_match={$dbr->addQuotes($username)} 
			  ORDER BY stats_timestamp DESC LIMIT $offset,$limit" ;

		$res = $dbr->query ($query) ;			
		while ($row = $dbr->fetchObject($res)) {
			$time = $wgLang->timeanddate( wfTimestamp( TS_MW, $row->stats_timestamp ), true );
			$wgOut->addWikiText ("* {$row->stats_match} blocked '''{$row->stats_user}''' on ".htmlspecialchars($row->stats_wiki)." at '''{$time}''', logging from address '''{$row->stats_ip}'''") ;
		}
		$dbr->freeResult ($res) ;			
	}

        /* init for showprevnext */
        function showPrevNext( &$out ) {
                global $wgContLang, $wgRequest;
                list( $limit, $offset ) = $wgRequest->getLimitOffset();
                $target = 'target=' . urlencode ( $wgRequest->getVal ('target') ) ;
                $mode = '&mode=' . urlencode ( $wgRequest->getVal ('mode') ) ;
                $html = wfViewPrevNext(
                                $offset,
                                $limit,
                                $wgContLang->specialpage( 'Regexblockstats' ),
                                $target.$mode,
                                ($this->numResults - $offset) <= $limit
                        );
                $out->addHTML( '<p>' . $html . '</p>' );
        }
}

?>
