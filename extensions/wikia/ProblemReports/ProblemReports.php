<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Loader of ProblemReports extension
 *
 * Extension allows users/anons to report problems with wiki-articles and admin/staff members to view and resolve them
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Maciej Brencz <macbre@wikia.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * 
  create table problem_reports (
    pr_id int not null auto_increment primary key,
    pr_cat varchar(32) not null,
    pr_summary varchar(512),
    pr_ns int not null,
    pr_title varchar(255) not null,
    pr_city_id int,
    pr_server varchar(128),
    pr_anon_reporter int(1),
    pr_reporter varchar(128),
    pr_ip int(32) unsigned not null,
    pr_email varchar(128),
    pr_date datetime not null,
    pr_status int(8)
 );
  *
  
  settings:
  
  $wgProblemReportsEnable			// turn on "ProblemReports" extension
  $wgProblemReportsEnableAnonReports		// allow anons to report a problems
  
 */

$wgExtensionCredits['other'][] = array(
	'name' => 'ProblemReports',
	'description' => 'Allow users to report problems with wiki-articles and admins/staff to view & resolve them',
	'author' => '[http://pl.inside.wikia.com/wiki/User:Macbre Maciej Brencz]'
);

// extension setup function
$wgExtensionFunctions[] = 'wfProblemReports';

// this file loads all files from ProblemReport extension...
global $IP;

require_once( "$IP/extensions/wikia/ProblemReports/ProblemReports.i18n.php" );
require_once( "$IP/extensions/wikia/ProblemReports/ApiProblemReports.php" );
require_once( "$IP/extensions/wikia/ProblemReports/SpecialProblemReports.php" );
require_once( "$IP/extensions/wikia/ProblemReports/SpecialReportProblem.php" );
require_once( "$IP/extensions/wikia/ProblemReports/ProblemReportsDialog.php" );


// extension setup
function wfProblemReports()
{
	global $wgLogTypes, $wgLogNames, $wgLogTypes, $wgLogActions, $wgLogHeaders, $wgProblemReportsEnable, $wgHooks;
	
	// load extension messages
	wfProblemReportsLoadMessages();
	
	// add hooks & messages if problem reporting is enabled
	if (isset($wgProblemReportsEnable) && $wgProblemReportsEnable)
	{
		// add "Report a problem" link and return html of Report a problem" dialog
		$wgHooks['SkinTemplateContentActions'][] = 'wfProblemReportsAddLink';
	}

	// setup for Special:Log
	$wgLogTypes[] = 'pr_rep_log';
	$wgLogHeaders['pr_rep_log'] = 'prlogheader';
	$wgLogNames['pr_rep_log']  = 'prlogtext';
	
	$wgLogNames['prl_rep']  = 'prlog_reported';
	$wgLogNames['prl_chn']  = 'prlog_changed';
	$wgLogNames['prl_fix']  = 'prlog_fixed';
	$wgLogNames['prl_rem']  = 'prlog_removed';
	
	$wgLogActions['pr_rep_log/prl_rep'] = 'prlog_reportedentry';
	$wgLogActions['pr_rep_log/prl_chn'] = 'prlog_changedentry';
	$wgLogActions['pr_rep_log/prl_rem'] = 'prlog_removedentry';

	//print_pre($wgLogHeaders);print_pre($wgLogNames);print_pre($wgLogTypes);print_pre($wgLogActions);

	wfDebug("ProblemReports: extension initalized\n");
}

?>