<?php
/**
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
$wgSpecialPages['ReportProblem'] = 'SpecialReportProblem';

class SpecialReportProblem extends SpecialPage
{
	var $mApi; // api object for easy handling of reports

        function SpecialReportProblem() {
                SpecialPage::SpecialPage('ReportProblem', '', false);

		// construct extension API object
		$this->mApi = new ApiProblemReports();
	}
	
	function handleAjax()
	{
		global $wgOut, $wgRequest;
		
		$params = array(
			// get data from form fields
			'ns'       => (int) $wgRequest->getVal('pr_ns'),
			'title'    => urldecode($wgRequest->getVal('pr_title')),
			'cat'      => (int) $wgRequest->getVal('pr_cat'),
			'summary'  => urldecode($wgRequest->getVal('pr_summary')),
			'reporter' => urldecode($wgRequest->getVal('pr_reporter')),
			'email'    => urldecode($wgRequest->getVal('pr_email')),
		);
		
		// check for empty problem description
		$isSummaryEmpty = trim($params['summary']) == '';
		
		if ($isSummaryEmpty)
		{
			// empty summary - don't add that kind of report - show info to user so he can add summary for problem he's reporting
			echo '{spam: 1,  caption: "' . addslashes(wfMsg('reportproblem')) . '", msg: "' . addslashes(wfMsg( 'pr_empty_summary')) . '"}';
			
			die();
		}
		
		// check for spam in report summary
		$isSpam = $this->mApi->checkForSpam($params['summary']);
		
		if ($isSpam)
		{
			// spam found - don't add that kind of report - show info to user so he can correct his spammy summary
			echo '{spam: 1, caption: "' . addslashes(wfMsg('reportproblem')) . '", msg: "' . addslashes(wfMsg( 'pr_spam_found')) . '"}';
		}
		else
		{
			// add report (will return its ID)
			$id = $this->mApi->addReport($params);
			
			echo '{success: ' . ($id > 0 ? 1 : 0). ', caption: "' . addslashes(wfMsg('reportproblem')) . '", '.
			     'text: "' . addslashes(wfMsg( $id > 0 ?  'pr_thank_you' : 'pr_thank_you_error')) . '", reportID: '.$id.', spam: 0}';
		}

		die();
	}

        function execute( $par ) {
		global $wgOut, $wgRequest, $wgUser;
		
		$this->setHeaders();
		
		// check whether user is blocked
		if ($wgUser->isBlocked()) {
			$wgOut->permissionRequired('*');
			return;
		}
		
		// parse AJAX request - return JSON
		if ($wgRequest->getVal('ajax')) {
			return $this->handleAjax();
		}
		
		$wgOut->addHTML('No JS fallback placeholder...');
        }
}

?>
