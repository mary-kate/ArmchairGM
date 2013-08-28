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


/* add hook */
global $wgHooks;
$wgHooks['GetBlockedStatus'][] = 'wfRegexBlockCheck';

$wgExtensionCredits['other'][] = array(
	'name' => 'RegexBlock Engine 2.0 MediaWiki handler',
	'description' => 'mediawiki functionality for regular expression matching engine',
	'author' => 'Tomasz Klim'
);


/*
	prepare data by getting blockers 
	@param $current_user User: current user  
*/
function wfRegexBlockCheck( $current_user ) {
	global $wgMemc, $wgSharedDB;

	if ( !wfSimplifiedRegexCheckSharedDB() )
		return;

	$core =& RegexBlockCore::singleton( $wgMemc, wfGetDB(DB_SLAVE), wfGetDB(DB_MASTER), $wgSharedDB,
					    wfRegexBlockGetTable(), wfRegexBlockGetStatsTable() );

	$ip_to_check = wfGetIP();
	$username = $current_user->getName();

	$result = $core->check( $ip_to_check, $username );

	if ( $result ) {
		$current_user->mBlockedby = $result['blocker'];
		$current_user->mBlockreason = $result['reason'];
		if ( $result['blockcreateaccount'] ) $current_user->mBlock->mCreateAccount = 1;
	}
}


?>
