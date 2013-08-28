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
$wgSpecialPages['ProblemReports'] = 'SpecialProblemReports';

class SpecialProblemReports extends SpecialPage
{
	var $mApi; // api object for easy handling of reports
	
	var $problemTypes;
	var $problemColors;

        function SpecialProblemReports() {
                SpecialPage::SpecialPage('ProblemReports');

		// construct extension API object
		$this->mApi = new ApiProblemReports();
		
		// types of problems
		$this->problemTypes = array
		(
			wfMsg('pr_what_problem_spam_short'),
			wfMsg('pr_what_problem_vandalised_short'),
			wfMsg('pr_what_problem_incorrect_content_short'),
			wfMsg('pr_what_problem_software_bug_short'),
			wfMsg('pr_what_problem_other_short'),
		);
		
		$this->problemColors = array
		(
			'6B8E23',
			'DC143C',
			'483D8B',
			'87CEEB',
			'C0C0C0'
		);
		
		// load the internationalization messages
		require_once( dirname( __FILE__ ) . '/ProblemReports.i18n.php' );
		
		global $wgMessageCache, $wgProblemReportsMessages;
		
		foreach( $wgProblemReportsMessages as $lang => $msgs) {
		    $wgMessageCache->addMessages( $msgs, $lang );
		}
	}
	
	// create feed (RSS/Atom) from DB
	function makeFeed($type, $par)
	{
		global $wgOut, $wgFeedClasses, $wgParser, $parserOptions, $wgTitle, $wgSitename, $wgRequest, $wgCityId, $wgServer;
		
		// in what cityID are we interested in?
		$params = explode('/', $par);
		
		// force city ID value (for staff only!)
		if ($params[0] == 'wikia' && $this->mApi->isStaff())
		{
			$cityId = (int) $params[1];
		}
		else if ($this->mApi->isStaff())
		{
			// for staff list reports from all wikia
			$cityId = 0;
		}
		else
		{
			$cityId = $wgCityId > 0 ? $wgCityId : 831; // local tests
		}
		
		// parse request params...
			
		// is 'archived' subpage required?
		$archived = in_array('archived', $params);
		
		// show only staff reports?
		$staff_reports = in_array('staff', $params) && $this->mApi->isStaff() && !$archived;
		
		// problem type
		$problem = is_numeric($wgRequest->getVal('problem')) && ($wgRequest->getVal('problem') > -1) ? $wgRequest->getVal('problem') : -1;
		
		// get list of reports (latest 25 reports of given type)
		$res = $this->mApi->getReports(25, 0, $cityId, $problem, $archived, $staff_reports);
		
		//$wgOut->addHTML('<pre>Feed: ' . $type . '</pre>');
		
		if( isset($wgFeedClasses[$type]) )
		{
			$feed = new $wgFeedClasses[$type]
			(
				wfMsg('problemreports') . ' - '. $wgSitename,
				'',
				Title::makeTitle(NS_SPECIAL,'ProblemReports')->escapeFullURL()
			);
				
			$feed->outHeader();

			while( $row = $this->mApi->mDB->fetchObject( $res ) )
			{
				$url = htmlspecialchars(Title::makeTitle(NS_MAIN,$row->pr_title)->escapeFullURL());
				$url = str_replace($wgServer, $row->pr_server, $url);
				
				// user page
				$user_url = htmlspecialchars(Title::makeTitle(NS_USER, $row->pr_reporter)->escapeFullURL());
				
				// format date
				$item = new FeedItem
				(
					$row->pr_title . ' - ' . str_replace('http://', '', $row->pr_server),
					'<a href="'.$user_url.'">'.htmlspecialchars($row->pr_reporter).'</a>: '. wfMsg('pr_table_problem_type'). ' - '. $this->problemTypes[$row->pr_cat] .
						$wgOut->parse( $row->pr_summary ),
					$url,
					$row->pr_date
				);
				
				$feed->outItem( $item );
			}
			$this->mApi->mDB->freeResult( $res );

			$feed->outFooter();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function handleAjax()
	{
		global $wgOut, $wgRequest;
		
		// check whether user is staff or admin
		if (!$this->mApi->isAdmin() && !$this->mApi->isStaff() || !true) {
		
			echo '{success: 0, text: "error (please login)"}'; die(); // logout fallback
		
			$wgOut->permissionRequired('admin');
			return;
		}
		
		$params = array(
			// get data from form fields
			'id'       => (int) $wgRequest->getVal('id'),
			'status'   => (int) $wgRequest->getVal('status')
		);
		
		//print_pre($params);
		
		// update / remove report
		$success = ($params['status'] != 10) ? $this->mApi->updateReport($params['id'], $params['status']) : $this->mApi->removeReport($params['id']);
		
		$text = wfMsg('pr_status_'.$params['status']);
		
		echo '{success: ' . ($success ? 1 : 0). ', text: "' . addslashes($text) . '", reportID: '.$params['id'].', status: '.$params['status'].'}';
		
		die();
	}
	
	
		function outputCSS(&$out)
		{
			global $wgStylePath;
		
			$out .= '
			.problemReportsList {border-collapse: collapse; margin: 10px 0}
			.problemReportsList td, .problemReportsList th {border: solid 1px #d1d1d1; font-size: 0.95em; padding: 3px 5px; height: 34px}
			.problemReportsList .odd {background-color: #fafafa}
			.problemReportsCategoriesLegend {font-size: 0.9em}
			.problemReportsCategoriesLegend dt {display: block; float: left; clear: both; width: 8px; height: 8px; margin: 5px 0 0 5px}
			a.problemReportsActions {width: 16px; height: 16px; margin: 0 3px; text-decoration: none; display: block; float: left; cursor: pointer}
			a.problemReportsActionFixed {background-image: url("'.$wgStylePath.'/common/problem_reports/problem_reports_fixed.png")}
			a.problemReportsActionNotAProblem {background-image: url("'.$wgStylePath.'/common/problem_reports/problem_reports_not_problem.png")}
			a.problemReportsActionNeedStaffHelp {background-image: url("'.$wgStylePath.'/common/problem_reports/problem_reports_need_staff_help.png")}
			a.problemReportsActionUndo {background-image: url("'.$wgStylePath.'/common/problem_reports/problem_reports_undo.png"); float: right; margin: 0}
			a.problemReportsActionRemove {background-image: url("'.$wgStylePath.'/common/problem_reports/problem_reports_remove.png"); margin-left: 10px; opacity: 0.7}
			em.reportProblemStatus0 {color: #555555}
			em.reportProblemStatus1 {color: #006400}
			em.reportProblemStatus2 {color: #909090}
			em.reportProblemStatus3 {color:#DC143C}

			* html a.problemReportsActionFixed {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$wgStylePath.'/common/problem_reports/problem_reports_fixed.png",sizingMethod="image");}
			* html a.problemReportsActionNotAProblem {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$wgStylePath.'/common/problem_reports/problem_reports_not_problem.png",sizingMethod="image");}
			* html a.problemReportsActionNeedStaffHelp {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$wgStylePath.'/common/problem_reports/problem_reports_need_staff_help.png",sizingMethod="image");}
			* html a.problemReportsActionUndo {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$wgStylePath.'/common/problem_reports/problem_reports_undo.png",sizingMethod="image");}
			* html a.problemReportsActionRemove {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.$wgStylePath.'/common/problem_reports/problem_reports_remove.png",sizingMethod="image");}'."\n";

			return true;
		}


        function execute( $par ) {
		global $wgOut, $wgRequest, $wgServer, $wgLang, $wgTitle, $wgCityId, $wgHooks;
		
		$this->setHeaders();
		
		$css = '<style>
			.problemReportsList {border-collapse: collapse; margin: 10px 0}
			.problemReportsList td, .problemReportsList th {border: solid 1px #d1d1d1; font-size: 0.95em; padding: 3px 5px; height: 34px}
			.problemReportsList .odd {background-color: #fafafa}
			.problemReportsCategoriesLegend {font-size: 0.9em}
			.problemReportsCategoriesLegend dt {display: block; float: left; clear: both; width: 8px; height: 8px; margin: 5px 0 0 5px}
			a.problemReportsActions {width: 16px; height: 16px; margin: 0 3px; text-decoration: none; display: block; float: left; cursor: pointer}
			a.problemReportsActionFixed {background-image: url("http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_fixed.png")}
			a.problemReportsActionNotAProblem {background-image: url("http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_not_problem.png")}
			a.problemReportsActionNeedStaffHelp {background-image: url("http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_need_staff_help.png")}
			a.problemReportsActionUndo {background-image: url("http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_undo.png"); float: right; margin: 0}
			a.problemReportsActionRemove {background-image: url("http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_remove.png"); margin-left: 10px; opacity: 0.7}
			em.reportProblemStatus0 {color: #555555}
			em.reportProblemStatus1 {color: #006400}
			em.reportProblemStatus2 {color: #909090}
			em.reportProblemStatus3 {color:#DC143C}

			* html a.problemReportsActionFixed {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports/problem_reports_fixed.png",sizingMethod="image");}
			* html a.problemReportsActionNotAProblem {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_not_problem.png",sizingMethod="image");}
			* html a.problemReportsActionNeedStaffHelp {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_need_staff_help.png",sizingMethod="image");}
			* html a.problemReportsActionUndo {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_undo.png",sizingMethod="image");}
			* html a.problemReportsActionRemove {background-image: none;  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://images.wikia.com/common/extensions/wikia/ProblemReports/images/problem_reports_remove.png",sizingMethod="image");}</style>'."\n";
		
		$wgOut->addHTML($css);

		// append CSS to <!-- MediaWiki --> section
		$wgHooks['SkinTemplateSetupPageCss'][] = array(&$this, 'outputCSS');
		
		// allow access to special page for all
		
		/**
		// check whether user is staff or admin
		if (!$this->mApi->isAdmin() && !$this->mApi->isStaff() || !true) {
			$wgOut->permissionRequired('admin');
			return;
		}
		**/
		
		
		$wgOut->setSyndicated(true);
		
		// parse AJAX request - return JSON
		if ($wgRequest->getVal('ajax')) {
			return $this->handleAjax();
		}
		
		// make feed (RSS/Atom) if requested
		if ($wgRequest->getVal('feed')) {
			return $this->makeFeed($wgRequest->getVal('feed'), $par);
		}
		
		// in what cityID are we interested in?
		$params = explode('/', $par);
		
		// force city ID value (for staff only!)
		if ($params[0] == 'wikia' && $this->mApi->isStaff())
		{
			$cityId = (int) $params[1];
			$my_url = 'ProblemReports/wikia/'.$cityId;
		}
		else if ($this->mApi->isStaff())
		{
			// for staff list reports from all wikia
			$cityId = 0;
			$my_url = 'ProblemReports';
		}
		else
		{
			$cityId = $wgCityId > 0 ? $wgCityId : 831; // local tests
			$my_url = 'ProblemReports';
		}
		
		
		// parse request params...
			
		// is 'archived' subpage required?
		$archived = in_array('archived', $params);
		
		if ($archived)
			$my_url .= '/archived';
			
		// show only staff reports?
		$staff_reports = in_array('staff', $params) && $this->mApi->isStaff() && !$archived;
		
		if ($staff_reports)
			$my_url = 'ProblemReports/staff';
			
		// reports per page limiting
		$limit = $wgRequest->getVal('limit') > 0 ? $wgRequest->getVal('limit') : 50;
		
		// offset
		$offset = is_numeric($wgRequest->getVal('offset')) && ($wgRequest->getVal('offset') >= 0) ? $wgRequest->getVal('offset') : 0;
		
		// problem type
		$problem = is_numeric($wgRequest->getVal('problem')) && ($wgRequest->getVal('problem') > -1) ? $wgRequest->getVal('problem') : -1;
		
		
		
		//print_pre('limit: '.$limit.', offset: '.$offset.', problemID: '.$problem.', cityID: '.$cityId);
		
		// count all reports of type given (or 0 for all reports)
		$count = $this->mApi->countReports($cityId, $problem, $archived, $staff_reports);
		
		
		// prev / next links
		if ($offset > 0)
		{
			// add latest link
			$latest_url = Title::makeTitle(NS_SPECIAL,$my_url)->escapeFullURL('problem='.$problem.'&limit='.$limit.'&offset='. ($offset - $limit)  );
			$wgOut->addHTML('<div>(<a href="'.$latest_url.'">'.wfMsg('histlast').'</a>');
		}
		else
		{
			$wgOut->addHTML('<div>('.wfMsg('histlast'));
		}
		
		if ($count > $limit + $offset)
		{
			// add earliest link
			$earliest_url = Title::makeTitle(NS_SPECIAL,$my_url)->escapeFullURL('problem='.$problem.'&limit='.$limit.'&offset='. ($offset + $limit)  );
			$wgOut->addHTML(' | <a href="'.$earliest_url.'">'.wfMsg('histfirst').'</a>)');
		}
		else
		{
			$wgOut->addHTML(' | '.wfMsg('histfirst').')');
		}
		
		foreach(array(20,50,100,250,500) as $l)
		{
			$limits[] = '<a href="'.Title::makeTitle(NS_SPECIAL,$my_url)->escapeFullURL('problem='.$problem.'&limit='.$l) .'">'.$l.'</a>';
		}
		
		$wgOut->addHTML('<span style="margin-left: 30px">('. implode(' | ', $limits) .')</span></div>');
		
	
		// get list of reports
		$res = $this->mApi->getReports($limit, $offset, $cityId, $problem, $archived, $staff_reports);
		
		// allow staff to view 'need staff help' reports (show link when not in /staff subpage)
		if ($this->mApi->isStaff() && !$staff_reports)
		{
			$staff_help_url = ' | <a href="'.Title::makeTitle(NS_SPECIAL, 'ProblemReports' . ($cityId > 0 ? '/wikia/'.$cityId : ''). '/staff')->escapeFullURL().'">'.wfMsg('pr_view_staff').'</a>';
		}
		
		$rss_feed_url = ' | <a href="'.Title::makeTitle(NS_SPECIAL,$my_url)->escapeFullURL('feed=rss' . ($problem > -1 ? '&problem='.$problem : '')) .'">RSS</a>';
		
		if ($problem > -1)
		{
			// filter by problem type
			$wgOut->setSubtitle(wfMsg('pr_total_number').': '.number_format($count, 0, ' ', ' ').'<br />'.
				wfMsg('pr_reports_from').': '. $this->problemTypes[$problem] . ' (<a href="'.htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports')->escapeFullURL()).'">'.wfMsg('pr_view_all').'</a>'.$staff_help_url.$rss_feed_url.')');
		}
		else if ($cityId == $wgCityId)
		{
			// show all wikia's
			$wgOut->setSubtitle(wfMsg('pr_total_number').': '.number_format($count, 0, ' ', ' ').'<br />'.
				'<a href="'.htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports')->escapeFullURL()).'">'.wfMsg('pr_view_all').'</a>'.
				(!$archived ? ' | <a href="'.htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports/archived')->escapeFullURL()).'">'.wfMsg('pr_view_archive').'</a>' : '').
				$staff_help_url.$rss_feed_url);
		}
		else
		{
			// show reports from one wikia
			$wgOut->setSubtitle(wfMsg('pr_total_number').': '.number_format($count, 0, ' ', ' ').'<br />'.
				($cityId > 0 ? wfMsg('pr_reports_from').': wikia #'. $cityId
				: '<a href="'.Title::makeTitle(NS_SPECIAL,'ProblemReports/wikia/'.$wgCityId. ($archived ? '/archived' : ''))->escapeFullURL().'" title="'.wfMsg('pr_raports_from_this_wikia').'">'.$wgServer.'</a>').
				' (<a href="'.htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports')->escapeFullURL()).'">'.wfMsg('pr_view_all').'</a>'.
				(!$archived ? ' | <a href="'.htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports/archived')->escapeFullURL()).'">'.wfMsg('pr_view_archive').'</a>' : '').
				$staff_help_url.$rss_feed_url.')');
		}
		
		//$wgOut->addHTML('<pre>Params: '.$par.'</pre>');
		
		//
		// output table
		//
		
		// inline JS

		$wgOut->addHTML('<script type="text/javascript">function reportProblemAction(elem, id, statusID, msgWait, askMsg)
{
	if (statusID == 10 && !confirm(askMsg))
		return;

	var parent = elem.parentNode;
	
	var oldHTML = parent.innerHTML;

	function reportProblemActionCallback(data)
	{
		parent.previousSibling.innerHTML = "<em class=\"reportProblemStatus" + data.status + "\">" + data.text + "</em>";
		parent.innerHTML = oldHTML;
		
		// remove report
		if (data.status == 10)
		{
			parent.parentNode.parentNode.removeChild(parent.parentNode);
			
			// don\'t forget to remove report summary
			var problemDescTr = YAHOO.util.Dom.get("problemReportsList-problem-" + data.reportID + "-summary");
		
			if (problemDescTr)
			    problemDescTr.parentNode.removeChild(problemDescTr);
		}

	}
	
	// display progress message
	parent.innerHTML = msgWait;
	
	var callback = {
		success: function(o) { reportProblemActionCallback(eval("(" + o.responseText + ")")); },
		failure: function(o) { reportProblemActionCallback(eval("(" + o.responseText + ")")); }
	}
	
	req = YAHOO.util.Connect.asyncRequest("POST", document.location, callback, "id=" + id +"&status=" + statusID + "&ajax=1");
}

function reportProblemToogleSummary(id)
{
	var elem = YAHOO.util.Dom.get("problemReportsList-problem-" + id + "-summary");
	
	switch(elem.style.display)
	{
		case "none": elem.style.display = ""; break;
		default:     elem.style.display = "none"; break;
	}
}

</script>');

		// are actions allowed - i.e. changing reports status by sysops and staff members
		$actionsAllowed = $this->mApi->isStaff() || $this->mApi->isAdmin();
		
		// print out table header
		$wgOut->addHTML('<table class="problemReportsList">'."\n".
			//'<colgroup><col width="70" align="center" /><col width="120" /><col  width="150" /><col  width="*" /><col  width="180" /><col width="175" /><col  width="140" /><col  width="140" />' . ( $actionsAllowed ? '<col width="250" />' : '') . '</colgroup>'."\n".
			'<tr style="background-color: #eeeeee">');
		
		// messages for table header row (<th> elements)
		$th = array
		(
			'pr_table_problem_id', 'pr_table_wiki_name', 'pr_table_problem_type', 'pr_table_page_link', 'pr_table_date_submitted',
			'pr_table_reporter_name', /*'pr_table_description',*/ 'pr_table_comments', 'pr_table_status', 'pr_table_actions'
		);
		
		if (!$this->mApi->isStaff() && !$this->mApi->isAdmin())
		{
			// remove actions column for non staff / sysop users
			array_pop($th);
		}
		
		
		foreach($th as $header)
		{
			$wgOut->addHTML("\n\t<th>". htmlspecialchars(wfMsg($header)) .'</th>');
		}
		
		$wgOut->addHTML("\n</tr>");
		
		$count = 0;
		
		
		
		while ( $row = $this->mApi->mDB->fetchObject( $res ) )
		{
			// change hostname to the one set in pr_server
			$url = htmlspecialchars(Title::makeTitle(NS_MAIN,$row->pr_title)->escapeFullURL());
			$url = str_replace($wgServer, $row->pr_server, $url);
			
			// comments page - red links only for not existing local project_talk pages (use parser)
			if ($wgServer == $row->pr_server)
			{
				// link to local page -> use $wgOut->parse
				$comments_url = $wgOut->parse('[[Project_talk:ProblemReports/'.$row->pr_id.'|'.wfMsg('pr_table_comments').']]');
			}
			else
			{
				$comments_url = htmlspecialchars(Title::makeTitle(NS_MAIN, 'Project_talk:ProblemReports/'.$row->pr_id)->escapeFullURL());
				$comments_url = str_replace($wgServer, $row->pr_server, $comments_url);
				
				$comments_url = '<a href="'.$comments_url.'">'. htmlspecialchars(wfMsg('pr_table_comments')) .'</a>';
			}
			
			// make link to: user page (for logged-in users) / contributions list of certain IP + entered username (for anons)
			if ($row->pr_anon_reporter == 1)
			{
				// Special:Contributions/72.134.123.98
				$user_url = htmlspecialchars(Title::makeTitle(NS_SPECIAL, 'Contributions/'.long2ip($row->pr_ip))->escapeFullURL());
				$user_name = long2ip($row->pr_ip). (empty($row->pr_reporter) ? '' :  ' ("'.$row->pr_reporter.'")');								
			}
			else
			{
				// User:foo
				$user_url = htmlspecialchars(Title::makeTitle(NS_USER, $row->pr_reporter)->escapeFullURL());
				$user_name = $row->pr_reporter;
			}
			
			// change link to point to wikia where problem was reported
			$user_url = str_replace($wgServer, $row->pr_server, $user_url);
			
			// format date
			$date = $wgLang->sprintfDate('j M Y<\b\r />H:i', date('YmdHis', strtotime($row->pr_date)));
			
			// more link
			$more_url = htmlspecialchars(Title::makeTitle(NS_SPECIAL,'ProblemReports/wikia/'.$row->pr_city_id. ($archived ? '/archived' : ''))->escapeFullURL());
			
			//
			// print out table row ...
			//
			
			// row id
			$wgOut->addHTML('<tr id="problemReportsList-problem-'.$row->pr_id.'"'. ( (++$count) % 2 ? '' : ' class="odd"').'>');
			
			// problem id and color for problem cat
			$wgOut->addHTML("\n\t".'<td style="text-align: center; border-left: solid 4px #'.$this->problemColors[$row->pr_cat].'">');
			
			if (!empty($row->pr_summary))
				$wgOut->addHTML('<a onclick="reportProblemToogleSummary('.$row->pr_id.')" style="cursor: pointer; font-weight: bold" title="'.wfMsg('pr_table_description').'">');
				
			$wgOut->addHTML( number_format($row->pr_id, 0, ' ', ' ') );
			
			if (!empty($row->pr_summary) )
				$wgOut->addHTML(' &raquo; </a>');
				
			// wiki domain
			$wgOut->addHTML("</td>\n\t".'<td title="City ID: #'.$row->pr_city_id.'"><a href="'.$more_url.'" title="'.wfMsg('pr_raports_from_this_wikia').'">'.str_replace(array('http://', '.wikia.com'), '', $row->pr_server).'</a></td>');
			
			// problem type
			$wgOut->addHTML("\n\t".'<td>'.htmlspecialchars($this->problemTypes[$row->pr_cat]).'</td>');
			
			// page title
			$wgOut->addHTML("\n\t".'<td><a href="'.$url.'">'.htmlspecialchars($row->pr_title).'</a></td>');
			
			// date
			$wgOut->addHTML("\n\t".'<td style="text-align: center">'.$date.'</td>');
			
			// reporter name, email & IP
			$wgOut->addHTML("\n\t".'<td><a href="'.$user_url.'">'.htmlspecialchars($user_name).'</a>');
			
			if (!empty($row->pr_email) && $this->mApi->isStaff() )
				$wgOut->addHTML(' <a href="mailto:'.htmlspecialchars($row->pr_email).'" title="'.wfMsg('email').'"></a>');
			if ($this->mApi->isStaff() && $row->pr_anon_reporter == 0) {	
				$wgOut->addHTML(' ('.long2ip($row->pr_ip).')</td>');
			}
			else
			{
				// always remember to close tags ;)
				$wgOut->addHTML('</td>');
			}
			
			// comments url
			$wgOut->addHTML("\n\t".'<td>'.$comments_url.'</td>');
			
			// report status
			$wgOut->addHTML("\n\t".'<td style="text-align: center; width: 65px"><em class="reportProblemStatus'.$row->pr_status.'">'.wfMsg('pr_status_'.$row->pr_status).'</em></td>');
			
			// action icons (only for staff / admin)
			if ( $actionsAllowed )
			{
				$wgOut->addHTML('<td style="width: 120px"><a class="problemReportsActions problemReportsActionFixed" title="'.wfMsg('pr_status_1').'" onclick="reportProblemAction(this, '.$row->pr_id.', 1, \''.wfMsg('pr_status_wait').'\', \''.wfMsg('pr_status_ask').'\')">&nbsp;</a>');
				$wgOut->addHTML('<a class="problemReportsActions problemReportsActionNotAProblem" title="'.wfMsg('pr_status_2').'" onclick="reportProblemAction(this, '.$row->pr_id.', 2, \''.wfMsg('pr_status_wait').'\', \''.wfMsg('pr_status_ask').'\')">&nbsp;</a>');
				$wgOut->addHTML('<a class="problemReportsActions problemReportsActionNeedStaffHelp" title="'.wfMsg('pr_status_3').'" onclick="reportProblemAction(this, '.$row->pr_id.', 3, \''.wfMsg('pr_status_wait').'\', \''.wfMsg('pr_status_ask').'\')">&nbsp;</a>');
				
				// only staff can remove reports
				if ($this->mApi->isStaff()) {
					$wgOut->addHTML('<a class="problemReportsActions problemReportsActionRemove" title="'.wfMsg('pr_status_10').'" onclick="reportProblemAction(this, '.$row->pr_id.', 10, \''.wfMsg('pr_status_wait').'\', \''.wfMsg('pr_remove_ask').'\')">&nbsp;</a>');
				}
				
				$wgOut->addHTML('<a class="problemReportsActions problemReportsActionUndo" title="'.wfMsg('pr_status_0').'" onclick="reportProblemAction(this, '.$row->pr_id.', 0, \''.wfMsg('pr_remove_wait').'\', \''.wfMsg('pr_status_ask').'\')">&nbsp;</a>');
				
				$wgOut->addHTML('</td>');
			}
			
			$wgOut->addHTML("\n</tr>\n\n");
			
			// add report description
			if (!empty($row->pr_summary))
			{
				$wgOut->addHTML('<tr id="problemReportsList-problem-'.$row->pr_id.'-summary" '.
					'style="display: none; border-left: solid 4px #'.$this->problemColors[$row->pr_cat].'"'.( ($count) % 2 ? '' : ' class="odd"').'>'.
					'<td colspan="'. ($this->mApi->isStaff() || $this->mApi->isAdmin() ? 9 : 8) .'"><strong>'. wfMsg('pr_table_description') .'</strong>: '.$wgOut->parse($row->pr_summary).'</td></tr>'."\n\n\n");
			}
		}
		
		// no reports
		if ($count == 0)
		{
			$wgOut->addHTML('<tr><td colspan="9" style="text-align: center; font-weight: bold">'.wfMsg('pr_no_reports').'</td></tr>');
		}

		$wgOut->addHTML('</table>');
		
		// add colors legend + links enabling filtering by problem categories
		$wgOut->addHTML('<h4>'.wfMsg('pr_table_problem_type').'</h4>'."\n\n".'<dl class="problemReportsCategoriesLegend">');
		
		foreach($this->problemTypes as $id => $problemType)
		{
			$wgOut->addHTML("\n\t".'<dt style="background-color: #'.$this->problemColors[$id].'"></dt>'.
				"\n\t\t".'<dd><a href="'.Title::makeTitle(NS_SPECIAL,$my_url)->escapeFullURL('problem='.$id) .'">'.htmlspecialchars($problemType).'</a></dd>');
		}
		
		$wgOut->addHTML("\n</dl>\n\n");
        }
}

?>