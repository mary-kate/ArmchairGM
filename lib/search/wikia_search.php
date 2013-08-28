<?
/*
 * Copyright 2006 Wikia, Inc.  All rights reserved.
 * Use is subject to license terms.
 */

if (!defined('MEDIAWIKI'))
	exit;

require_once("$IP/includes/SpecialPage.php");
require_once("wikia_engine.php");

$wgExtensionFunctions[] = 'wfWikiaSearch';

class WikiaSearch extends SpecialPage {
	function WikiaSearch() {
	global	$wgWikiaSearchPageName;
	global	$wgWikiaSearchNative;
	global	$wgDBname;
		if (!isset($wgWikiaSearchNative) || is_null($wgWikiaSearchNative))
			$wgWikiaSearchNative = $wgDBname;
		SpecialPage::SpecialPage($wgWikiaSearchPageName);
	}

	function including($x = NULL) { }
	function execute($par) {
	global	$wgRequest;
	global	$wgOut;
	global	$wgWikiaSearchStaticDir;

		$wgOut->setPageTitle(wfMsg("wikiasearchnoquerytitle"));
		$wgOut->addLink(array("rel" => "stylesheet", "href" => $wgWikiaSearchStaticDir . "/wikia_search.css"));

		$searchTerm = $wgRequest->getText('search');
		if (!strlen($searchTerm))
			$searchTerm = $par;
		$wikia = new WikiaSearchEngine($searchTerm);
		$wikia->run();
	}
}

function wfWikiaSearch() {
	global $wgMessageCache;

	SpecialPage::addPage(new WikiaSearch);	
	$wgMessageCache->addMessage("wikiasearch", "Wikia Search system");
	$wgMessageCache->addMessage("wikiasearchnoquerytitle", "Search");
	$wgMessageCache->addMessage("wikiasearchsubmit", "Search");
	$wgMessageCache->addMessage("wikiasearchhelplinktext", "help&nbsp;&raquo;");
	$wgMessageCache->addMessage("wikiasearchprefslinktext", "preferences");
	$wgMessageCache->addMessage("wikiasearchheaderotherwikis", "Results on other Wikia");
	$wgMessageCache->addMessage("wikiasearchpagetitle", "$1 - Search");
	$wgMessageCache->addMessage("wikiasearchmoreresultsnative", "More pages on this wiki &raquo;");
	$wgMessageCache->addMessage("wikiasearchmoreresultsother", "More pages on other wikis &raquo;");
	$wgMessageCache->addMessage("wikiasearchhelplinkpage", "Searching");
	$wgMessageCache->addMessage("wikiasearchnoresults", "Sorry, there were ".
				" no results for your search on this Wikia.");
	$wgMessageCache->addMessage("wikiasearchnoresultsarticle", "You searched for: [[$1]].\n\n:Sorry, there were ".
				" no results for your search on this Wikia.");
	$wgMessageCache->addMessage("wikiasearchoverthere", "Search on $1");
	$wgMessageCache->addMessage("wikiasearcherror", "Sorry, an internal error occured while trying ".
					"to process your search: $1");
	$wgMessageCache->addMessage("wikiasearchprev", "&laquo; Prev");
	$wgMessageCache->addMessage("wikiasearchnext", "Next &raquo;");
	$wgMessageCache->addMessage("wikiasearchprefsintro",
		"On this page you can set your preferences for how the search function ".
		"should work.");
	$wgMessageCache->addMessage("wikiasearchprefsresultsdisplay", "Results display");
	$wgMessageCache->addMessage("wikiasearchprefsoldformat",
		"Use old title/text matches display");
	$wgMessageCache->addMessage("wikiasearchprefssubmit", "Submit changes");
	$wgMessageCache->addMessage("wikiasearchdymtext", "Did you mean: $1?");
	$wgMessageCache->addMessage("wikiasearchresultsnumlocal",
		"Results $1 - $2 of $3 on this Wikia for: \"$4\"");
	$wgMessageCache->addMessage("wikiasearchprefsmustlogin",
		"Sorry, you must log in to set your search preferences.");
	$wgMessageCache->addMessage("wikiasearchprefssaved",
		"Your search preferences were saved successfully.");
	$wgMessageCache->addMessage("wikiasearcholdbodyheader", "Page text matches");
	$wgMessageCache->addMessage("wikiasearcholdtitleheader", "Page title matches");
	$wgMessageCache->addMessage("wikiasearcholdtitlenone", "No page title matches");
	$wgMessageCache->addMessage("wikiasearcholdbodynone", "No page text matches");
	$wgMessageCache->addMessage("wikiasearchprefsback", "&laquo; back to search");
	$wgMessageCache->addMessage("wikiasearchperpage", "Results to display per page:");
	$wgMessageCache->addMessage("wikiasearcholdformathelp",
		"When this is enabled, search results will be displayed in two sections, ".
		"text matches and title matches.");
	$wgMessageCache->addMessage("wikiasearchperpagehelp",
		"This controls the number of results that will be displayed, only when ".
		"searching on this wiki ('more pages from this wiki').");
	$wgMessageCache->addMessage("wikiasearchpagestext", "Pages:");
	$wgMessageCache->addMessage("wikiasearcholdsize", "($1 bytes)");
	$wgMessageCache->addMessage("wikiasearchadvquery", "Query:");
	$wgMessageCache->addMessage("wikiasearchadvcategory", "Category:");
	$wgMessageCache->addMessage("wikiasearchadvlinktext", "advanced&nbsp;search");
	$wgMessageCache->addMessage("wikiasearchonwikitext", "On:");
	$wgMessageCache->addMessage("wikiasearchonthiswiki", "This wiki only");
	$wgMessageCache->addMessage("wikiasearchonotherwikis", "Other wikis");
	$wgMessageCache->addMessage("wikiasearchadvtitle", "Advanced search");
	$wgMessageCache->addMessage("wikiasearchotherwikitext", "Other wiki&hellip;");
	$wgMessageCache->addMessage("wikiasearchotherwikihelp", 
		"Enter the name of a wiki here and select \"$1\" to search only on that wiki.");
	$wgMessageCache->addMessage("wikiasearchadvbacktonormal", "&laquo; back to standard search");
	$wgMessageCache->addMessage("wikiasearchadvaddcat", "New category...");
	$wgMessageCache->addMessage("wikiasearchresultsnumlocal2", "Results $1 - $2 of $3 on $4 for: \"$5\"");
	$wgMessageCache->addMessage("wikiasearchadvcontent", "Article content");
	$wgMessageCache->addMessage("wikiasearchadvallwords", "With all of the words:");
	$wgMessageCache->addMessage("wikiasearchadvsomewords", "With at least one of the words:");
	$wgMessageCache->addMessage("wikiasearchadvnowords", "With none of the words:");
	$wgMessageCache->addMessage("wikiasearchadvmeta", "Metadata");
	$wgMessageCache->addMessage("wikiasearchadvin", "In:");
	$wgMessageCache->addMessage("wikiasearchadvtitlebody", "title or body");
	$wgMessageCache->addMessage("wikiasearchadvtitleonly", "only the title");
	$wgMessageCache->addMessage("wikiasearchadvbodyonly", "only the body");
	$wgMessageCache->addMessage("wikiasearchbothwikitext", "Any wiki");
	$wgMessageCache->addMessage("wikiasearchresultsnumlocal3", "Results $1 - $2 of $3 on other wikis for: \"$4\"");
	$wgMessageCache->addMessage("wikiasearchadvinnamespaces", "Search in namespaces:");
	$wgMessageCache->addMessage("wikiasearchadvns0", "Articles");
	$wgMessageCache->addMessage("wikiasearchadvfiletype", "Files of type:");
	$wgMessageCache->addMessage("wikiasearchadvanytype", "any");
	$wgMessageCache->addMessage("wikiasearchadvsortby", "Sort by:");
	$wgMessageCache->addMessage("wikiasearchadvbytitle", "title");
	$wgMessageCache->addMessage("wikiasearchadvbyrelevance", "relevance");
	$wgMessageCache->addMessage("wikiasearchadvlangen", "English");
	$wgMessageCache->addMessage("wikiasearchadvlanges", "Spanish");
	$wgMessageCache->addMessage("wikiasearchadvlangfr", "French");
	$wgMessageCache->addMessage("wikiasearchadvlangde", "German");
	$wgMessageCache->addMessage("wikiasearchadvlanguage", "Language:");
	$wgMessageCache->addMessage("wikiasearchadvlangany", "any");
	$wgMessageCache->addMessage("wikiasearchadvlanghelp",
		"Note: language searching is based on automated language identification and may not " .
		"return correct results in all cases.");
	$wgMessageCache->addMessage("wikiasearchadvlastedit", "Last edited by:");
	$wgMessageCache->addMessage("wikiasearchadvlangeo", "Esperanto");
	$wgMessageCache->addMessage("wikiasearchadvlangzh", "Chinese");
	$wgMessageCache->addMessage("wikiasearchadvlangru", "Russian");
	$wgMessageCache->addMessage("wikiasearchadvlangpt", "Portuguese");
	$wgMessageCache->addMessage("wikiasearchadvlangcy", "Welsh");
	$wgMessageCache->addMessage("wikiasearchadvlangpl", "Polish");
	$wgMessageCache->addMessage("wikiasearchadvlangsv", "Swedish");
	$wgMessageCache->addMessage("wikiasearchadvlangda", "Danish");
	$wgMessageCache->addMessage("wikiasearchsubmitgo", "Go to article");
	$wgMessageCache->addMessage("wikiasearchinlang", "Would you rather search for $1?");
	$wgMessageCache->addMessage("wikiasearchdocsin", "documents in $1");
	$wgMessageCache->addMessage("wikiasearchngramlabel", "Fuzzy text search:");
	$wgMessageCache->addMessage("wikiasearchordiv", "&mdash; or &mdash;");
	$wgMessageCache->addMessage("wikiasearchdefaultlocal", "0");
}

?>
