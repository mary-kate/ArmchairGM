<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Dialog design and hooks for ProblemReports extension
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Maciej Brencz <tomek@wikia.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// add link to content actions array in skin
function wfProblemReportsAddLink(&$content_actions)
{
	wfProfileIn(__METHOD__);

	// where are we? on article page?
	global $wgUser, $wgTitle, $wgRequest, $wgProblemReportsEnableAnonReports;

	// do nothing when user is blocked or anon users can't report problems or
	// we're not on article page (main namespace) or we're on printable page version
	if (
	     $wgUser->isBlocked() ||
	     ($wgUser->isAnon() && isset($wgProblemReportsEnableAnonReports) && $wgProblemReportsEnableAnonReports === false) ||
	     $wgTitle->getNamespace() != 0 ||
	     ($wgRequest->getVal('printable') != '')
	   )
	{

	    wfDebug("ProblemReports: leaving without adding dialog\n".
	            'ProblemReportsEnableAnonReports: '.($wgProblemReportsEnableAnonReports ? 'yes' : 'no')."\n");

	    wfProfileOut(__METHOD__);
	    return;
	}

	wfDebug("ProblemReports: adding problem reports action tab & reporting dialog\n");

	$content_actions['report-problem'] = array
	(
		'class' => '',
		'text' => wfMsg('reportproblem'),
		'href' => '#' , //(Title::makeTitle(NS_SPECIAL,'ReportProblem')->escapeFullURL()) // no fallback for now
	);

	wfProblemReportsAddDialog(array());

	wfProfileOut(__METHOD__);
}

// add dialog to report problem - will be shown when user clicks 'Report a problem' link in toolbox
function wfProblemReportsAddDialog($foo)
{
	global $wgOut, $wgTitle, $wgUser;

	// do nothing when user is blocked
	if ($wgUser->isBlocked()) {
		return;
	}

	wfProfileIn(__METHOD__);

	$url = htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ReportProblem')->escapeFullURL());

	// current article infos
	$pageTitle = htmlspecialchars($wgTitle->getText());
	$pageNamespace = $wgTitle->getNamespace();

	// introductory text in dialog
	$introductoryText = $wgOut->parse(wfMsg('reportproblemtext'));

	// problem types to be choosen from dropdown list
	$what_problem_options = array(
		'pr_what_problem_spam', 'pr_what_problem_vandalised','pr_what_problem_incorrect_content', 'pr_what_problem_software_bug', 'pr_what_problem_other');

	// prepare dialog xHTML
	$dialog = '<!-- Report a problem dialog -->

<div id="reportProblemForm" class="roundedDiv" style="left: 150px; top: 100px; margin-top: 0; margin-left: 0">
<b class="xtop"><b class="xb1"></b><b class="xb2"></b><b class="xb3"></b><b class="xb4"></b></b><div class="r_boxContent">

<form name="reportProblem" id="reportProblem" action="'.$url.'" method="post" onsubmit="return reportProblemSubmit()">

<input type="hidden" name="pr_ns" value="'.$pageNamespace.'" />

<div class="boxHeader">'.htmlspecialchars(wfMsg('reportproblem')).' | <span style="text-transform: none">'.$pageTitle.'</span></div>

<div class="reportProblemText">'.$introductoryText.'</div>';

// page name
$dialog .= '<div class="clearfix"><div style="width: 270px; float:left"><label for="pr_title">'.wfMsg('pr_what_page').'</label>
<input name="pr_title" id="pr_title" type="text" value="'.$pageTitle.'" disabled="disabled" class="priefix" /></div>';

// kind of reported problem
$dialog .= '<div style="width: 270px; float:left; margin-left: 10px"><label for="pr_cat">'.wfMsg('pr_what_problem').'</label>
<select id="pr_cat" style="width:100%" name="pr_cat">';

foreach($what_problem_options as $id => $option)
{
	$dialog .= "\n\t".'<option value="'.$id.'"'. ($id == 4 ? ' selected="selected"' : '') . '>'.htmlspecialchars(wfMsg($option)).'</option>';
}

$dialog .= '</select></div></div><div style="clear: both">&nbsp;</div>';

// summary
$dialog .= '<label for="pr_summary">'.wfMsg('pr_describe_problem').'</label>
<textarea name="pr_summary" id="pr_summary" rows="3" cols="15" style="width:550px" onchange="reportProblemCheckTextarea(this)" onkeyup="reportProblemCheckTextarea(this)"></textarea>';



// user name or his IP
$dialog .= '<div class="clearfix"><div style="width: 270px; float:left"><label for="pr_reporter">'.wfMsg('yourname').'</label>
<input id="pr_reporter" name="pr_reporter" type="text" value="'. ($wgUser->isLoggedIn() ?   htmlspecialchars($wgUser->getName()).'" disabled="disabled"' : '"') . ' class="priefix" /></div>';

// user email (if he want to be informed about report progress)
$dialog .= '<div style="width: 270px; float:left; margin-left: 10px"><label for="pr_email">' . wfMsg('email') . ' (' . htmlspecialchars(wfMsg('pr_email_visible_only_to_staff')) . ')</label>
<input id="pr_email" name="pr_email" type="text" value="'.htmlspecialchars($wgUser->getEmail()).'" class="priefix" /></div></div>';

// send button & progress
$dialog .= '<div style="text-align:center; clear: both; padding: 15px 0 5px 0"><input type="submit" class="submit" value="' . htmlspecialchars(wfMsg('reportproblem')) . '" id="pr_submit" /></div>';

$dialog .= '</form><div style="clear: both"></div></div><b class="xbottom"><b class="xb4"></b><b class="xb3"></b><b class="xb2"></b><b class="xb1"></b></b></div>

<!-- /Report a problem dialog -->';

	$wgOut->addHTML("\n\n\n".$dialog."\n\n\n"); // add it at the end of article content

	$wgOut->addScript("\n\n\t\t".'<style type="text/css">#reportProblemForm_c {top: 0px; left: 0px; position: absolute} '.
			  '#reportProblemSummary_c {position: absolute; top: 150px; left: 150px;} '.
			  '#reportProblemForm a {color: inherit; text-decoration: underline}</style>'."\n\n");

	wfProfileOut(__METHOD__);
}

?>