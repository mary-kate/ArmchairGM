<?php

/**
 * Internationalization for ProblemReports extension
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Maciej Brencz <macbre@wikia.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 */

if (!defined('MEDIAWIKI')) die();

$wgProblemReportsMessages = array
(
	'en' => array
	(
		'problemreports' => 'Problem reports list',
		'reportproblem' => 'Report a problem',
		
		'prlogtext' => 'Problem reports',
		'prlogheader' => 'List of reported problems and changes of their status',
		'prlog_reportedentry' => 'reported a problem on $1 ($2)',
		'prlog_changedentry' => 'marked problem $1 as "$2"',
		'prlog_removedentry' => 'removed problem $1',

		'reportproblemtext' => 'Most pages on this wiki are editable, and you are welcome to edit the page and correct mistakes yourself! If you need help doing that, see [[help:editing|how to edit]] and [[help:revert|how to revert vandalism]].

To contact staff or to report copyright problems, please see [[w:contact us|Wikia\'s "contact us" page]].

Software bugs can be reported on the forums. Reports made here will be [[Special:ProblemReports|displayed on the wiki]].',

		'pr_what_problem' => 'What problem are you reporting?',
		'pr_what_problem_spam' => 'there is a spam link here',
		'pr_what_problem_vandalised' => 'this page has been vandalised',
		'pr_what_problem_incorrect_content' => 'this content is incorrect',
		'pr_what_problem_software_bug' => 'there is a bug in the software',
		'pr_what_problem_other' => 'other',

		'pr_what_problem_spam_short' => 'spam',
		'pr_what_problem_vandalised_short' => 'vandalised page',
		'pr_what_problem_incorrect_content_short' => 'incorrect content',
		'pr_what_problem_software_bug_short' => 'software bug',
		'pr_what_problem_other_short' => 'other',


		'pr_describe_problem' => 'Please describe the problem here. You may use wikitext but no external links.',
		'pr_what_page' => 'What page is the problem on?',
		'pr_email_visible_only_to_staff' => 'visible only to staff',
		'pr_thank_you' => 'Thank you for reporting a problem',
		'pr_thank_you_error' => 'Error occured when sending problem report, please try later...',
		'pr_spam_found' => 'Spam has been found in your report summary. Please change summary content',
		'pr_empty_summary' => 'Please provide short problem description',

		'pr_total_number'       => 'Total number of reports',
		'pr_view_archive'       => 'View archived problems',
		'pr_view_all'           => 'Show all reports',
		'pr_view_staff'         => 'Show reports that needs staff help',
		'pr_raports_from_this_wikia' => 'View reports from this Wikia only',
		'pr_reports_from'       => 'Reports only from',
		'pr_no_reports' => 'No reports matching your criteria',

		'pr_table_problem_id'   => 'Problem ID',
		'pr_table_wiki_name'    => 'Wiki name',
		'pr_table_problem_type' => 'Problem type',
		'pr_table_page_link'    => 'Page',
		'pr_table_date_submitted' => 'Date submitted',
		'pr_table_reporter_name'=> 'Reporter name',
		'pr_table_description'  => 'Description',
		'pr_table_comments'     => 'Comments',
		'pr_table_status'       => 'Status',
		'pr_table_actions'      => 'Actions',

		'pr_status_0' => 'awaits',
		'pr_status_1' => 'fixed',
		'pr_status_2' => 'not a problem',
		'pr_status_3' => 'need staff help',
		'pr_status_10' => 'remove report',

		'pr_status_undo' => 'Undo report status change',
		'pr_status_ask'  => 'Change report status?',
		
		'pr_remove_ask'  => 'Permanently remove report?',

		'pr_status_wait' => 'wait...'
	),
	
	'pl' => array
	(
		'problemreports' => 'Lista zgłoszonych problemów',
		'reportproblem' => 'Zgłoś problem',
		
		'prlogtext' => 'Zgłoszone problemy',
		'prlogheader' => 'Lista zgłoszonych problemów i zmian w ich statusach',
		'prlog_reportedentry' => 'zgłoszono problem z $1 ($2)',
		'prlog_changedentry' => 'oznaczono problem $1 jako "$2"',
		'prlog_removedentry' => 'usunięto problem $1',

		'reportproblemtext' => 'Większość stron na tej wiki jest edytowalna i zachęcamy Ciebie do ich edycji oraz korygowania błędów w ich treści! Jeśli potrzebujesz pomocy, zobacz poradnik [[help:editing|jak edytować strony]] i [[help:revert|jak usuwać wandalizmy]].

Aby skontaktować się z zarządcami wiki lub zgłosić problem związany z prawami autorskimi, zajrzyj na stronę [[w:contact us|kontakt z Wikią]].

Błędy w oprogramowaniu mogą być zgłaszane na forach. Problemy zgłaszane tutaj będą [[Special:ProblemReports|widoczne na wiki]].',

		'pr_what_problem' => 'Jakiego rodzaju problem zgłaszasz?',
		'pr_what_problem_spam' => 'na stronie znajduje się link spamera',
		'pr_what_problem_vandalised' => 'strona padła ofiarą wandala',
		'pr_what_problem_incorrect_content' => 'zawartość strony jest niepoprawna',
		'pr_what_problem_software_bug' => 'w oprogramowaniu znajduje się błąd',
		'pr_what_problem_other' => 'inny',

		'pr_what_problem_spam_short' => 'spam',
		'pr_what_problem_vandalised_short' => 'wandalizm',
		'pr_what_problem_incorrect_content_short' => 'niepoprawna zawartość',
		'pr_what_problem_software_bug_short' => 'błąd w oprogramowaniu',
		'pr_what_problem_other_short' => 'inny',


		'pr_describe_problem' => 'Opisz problem w polu poniżej. Możesz użyć formatowania wiki, ale nie zamieszczaj odnośników do zewnętrznych stron.',
		'pr_what_page' => 'Na jakiej stronie znalazłeś problem?',
		'pr_email_visible_only_to_staff' => 'dostępny tylko dla administracji Wikii',
		'pr_thank_you' => 'Dziękujemy za zgłoszenie problemu',
		'pr_thank_you_error' => 'Wystąpił problem w czasie wysyłania raportu, spróbuj później...',
		'pr_spam_found' => 'Wykryto spam w treści komentarza. Prosimy skorygować jego treść',
		'pr_empty_summary' => 'Podaj krótki opis problemu',


		'pr_total_number'       => 'Łączna liczba raportów',
		'pr_view_archive'       => 'Przejrzyj archiwalne raporty',
		'pr_view_all'           => 'Przeglądaj wszystkie raporty',
		'pr_view_staff'         => 'Przejrzyj raporty wymagające pomocy administracji Wikii',
		'pr_raports_from_this_wikia' => 'Przejrzyj raporty dotyczące tylko tej Wikii',
		'pr_reports_from'       => 'Raporty z',
		'pr_no_reports' => 'Brak raportów',

		'pr_table_problem_id'   => 'ID problemu',
		'pr_table_wiki_name'    => 'Nazwa Wikii',
		'pr_table_problem_type' => 'Rodzaj problemu',
		'pr_table_page_link'    => 'Strona',
		'pr_table_date_submitted' => 'Data zgłoszenia',
		'pr_table_reporter_name'=> 'Kto zgłosił',
		'pr_table_description'  => 'Opis',
		'pr_table_comments'  => 'Komentarze',
		'pr_table_status'       => 'Status',
		'pr_table_actions'      => 'Akcje',

		'pr_status_0' => 'oczekuje',
		'pr_status_1' => 'naprawione',
		'pr_status_2' => 'to nie jest problem',
		'pr_status_3' => 'konieczna pomoc',
		'pr_status_10' => 'usuń raport',

		'pr_status_undo' => 'Cofnij zmianę statusu raportu',
		'pr_status_ask'  => 'Zmienić status raportu?',
		
		'pr_remove_ask'  => 'Na pewno usunąć raport?',

		'pr_status_wait' => 'czekaj...'
	)
);


function wfProblemReportsLoadMessages()
{
	global $wgMessageCache, $wgProblemReportsMessages;
	
	//print_pre($wgProblemReportsMessages);
	
	wfProfileIn(__METHOD__);
	
	foreach( $wgProblemReportsMessages as $lang => $msgs) {
	    $wgMessageCache->addMessages( $msgs, $lang );
	}
	
	wfProfileOut(__METHOD__);
}

?>