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

define ('REGEXBLOCK_PATH', '/');

/* help displayed on the special page  */
define ('REGEXBLOCK_HELP', "Use the form below to block write access from a specific IP address or username. This should be done only only to prevent vandalism, and in accordance with policy. <i>This page will allow you to block even non-existing users, and will also block users with names similar to given, i.e. 'Test' will be blocked along with 'Test 2' etc. You can also block full IP addresses, meaning that no one logging from them will be able to edit pages. Note: partial IP addresses will be treated by usernames in determining blocking.  If no reason is specified, a default generic reason will be used.</i>");

/* get name of the table  */
function wfRegexBlockGetTable() {
	return wfSharedTable('blockedby');
}

/* get the name of the stats table */
function wfRegexBlockGetStatsTable() {
	return wfSharedTable('stats_blockedby');
}

/* modes for fetching data during blocking */
define ('REGEXBLOCK_MODE_NAMES',0);
define ('REGEXBLOCK_MODE_IPS',1);

/* for future use */
define ('REGEXBLOCK_USE_STATS', 1);

/* core includes */
//require_once ($IP.REGEXBLOCK_PATH."extensions/wikia/RegexBlock/regexBlockCore.php");     // old version
require_once ($IP.REGEXBLOCK_PATH."extensions/wikia/RegexBlock/regexBlockCoreClass.php");  // new version
require_once ($IP.REGEXBLOCK_PATH."extensions/wikia/RegexBlock/regexBlockCoreNew.php");    // new version
require_once ($IP.REGEXBLOCK_PATH."extensions/wikia/RegexBlock/SpecialRegexBlock.php");
require_once ($IP.REGEXBLOCK_PATH."extensions/wikia/RegexBlock/SpecialRegexBlockStats.php");

/* simplified regexes, this is shared with SpamRegex */
require_once ($IP.REGEXBLOCK_PATH."extensions/SimplifiedRegex/SimplifiedRegex.php");

?>
