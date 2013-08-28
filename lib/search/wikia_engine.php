<?
/*
 * Copyright 2006 Wikia, Inc.  All rights reserved.
 * Use is subject to license terms.
 */

$dontsearch = array('staff', 'internal');

/*
 * Implements the logic for the search engine.
 *
 * For every search, we do two actual searches: first on the local wiki
 * ("+wiki:foo"), and then on other wikis, but only in namespace 0
 * ("-wiki:foo +namespace:0").  The reason for this is that we cannot
 * know the namespace names of other wikis.
 */
 
/*
 * Represents a single result.
 */
class AResult {
	var $wiki;		/* name of wiki					*/
	var $namespace;	/* NS as int					*/
	var $title;		/* title, no NS					*/
	var $fragment;	/* highlighted extract, HTML	*/
	var $size;		/* in bytes						*/
	var $date;		/* in MediaWiki format 			*/

	function AResult($wiki, $namespace, $title, $fragment, $size, $date) {
		$this->wiki = $wiki;
		$this->namespace = $namespace;
		$this->title = $title;
		$this->fragment = $fragment;
		$this->date = $date;
		$this->size = $size;
	}
}

$wslangs = explode(" ", "en es de fr cy eo pl pt ru zh da sv");

/*
 * Main logic for search.
 */
class WikiaSearchEngine {
	var $query;				/* original search query from user		*/
	var $results_native;	/* results from local wiki				*/
	var $results_other;		/*      -''-    other wikis				*/
	var $nresults;			/* number of results from last query	*/
	var $local;				/* 1 if the query is local				*/
	var $suggestion;		/* spelling suggestion					*/
	var	$curlerr;			/* if the last curl call gave an error	*/
	var	$langcount;			/* # of documents not in the user's lang*/
	/*
	 * Return the number of results that this user wants per page.
	 */
	function pp() {
	global	$wgUser;
		static $pp = null;
		if (is_null($pp)) {
			$pp = (int)$wgUser->getOption("wikiasearchperpage");
			if ($pp == 0 || !is_numeric($pp))
				$pp = 10;
		}
		return $pp;
	}

	function getlocal() {
		if (isset($_REQUEST['local']))
			return (int) $_REQUEST['local'];
		$l = (int) wfMsg("wikiasearchdefaultlocal");
		if (strlen($l))
			return $l;
		return 0;
	}

	function nativewiki() {
	global	$wgRequest;
	global	$wgWikiaSearchNative;
		static $nw = null;
		if (is_null($nw)) {
			$nw = trim($wgRequest->getText("wiki", ""));
			if (!strlen($nw) || $this->getlocal() != 2)
				$nw = $wgWikiaSearchNative;
		}
		return $nw;
	}

	/*
	 * Escape < and > but not entities in an HTML string
	 */
	function htmlmsg($msg) {
		return strtr(wfMsg($msg),
			array("<" => "&lt;", ">" => "&gt;", '"' => "&dquot;", "'" => "&quot;"));
	}

	/*
	 * Construct a new search.
	 */
	function WikiaSearchEngine($query) {
		if (is_null($query) || !strlen($query))
			return;
		$this->query = $query;
		$this->suggestion = null;
	}

	function addcategories($q) {
	global	$wgRequest;
		if ($wgRequest->getInt("adv", 0) == 1 && ($ncats = $wgRequest->getInt("ncats", 0)) > 0) {
			$cats = '';
			for ($i = 1; $i <= min(10, $ncats); ++$i) {
				$cat = $wgRequest->getText("category$i", "");
				if (!strlen($cat))
					continue;
				$cat = strtr($cat, '"', '\\"');
				$cats .= " AND category:".$this->escape($cat, '"');
			}
			if (!strlen($cats))
				return $q;
			return "($q) $cats";
		}
		return $q;
	}

	function escape($term, $char = '(') {
		$escaped = preg_replace('/([\(\)"^])/', '\\\$1', $term);
		if ($term === $escaped && !strstr($term, ' '))
			return $term;
		if ($char == '(')
			return "($escaped)";
		else
			return "$char$escaped$char";
	}

	function formsearchquery() {
	global	$wgRequest;
	global	$wgContLang;
		if (strlen($q = trim($this->query))) {
			$q = $this->addcategories($q);
		} else if (strlen($ng = trim($wgRequest->getText("ngrams", "")))) {
			$this->ngrams = true;
			$this->nterms = $ng;
		} else {
			$all = trim($wgRequest->getText("qall", ""));
			$none = trim($wgRequest->getText("qnone", ""));
			$some = trim($wgRequest->getText("qsome", ""));
			$q = "";
			if (strlen($all))
				$q .= " +(" . implode(" AND ", explode(" ", $all)) . ") ";
			if (strlen($some))
				$q .= " (" . implode(" OR ", explode(" ", $some)) . ") ";
			if (strlen($none))
				$q .= " -(" . implode(" OR ", explode(" ", $none)). ") ";
			$what = trim($wgRequest->getText("where", ""));
			if (strlen($what) && ($what === "name" || $what === "content"))
				$q = "$what:".$this->escape($q);
		}
		$nsminus = array();
		foreach(array_diff(array_keys($wgContLang->getNamespaces()), $this->searchablens()) as $nsn)
			#if ($nsn >= 0)
				$nsminus[] = $nsn;
		$nsplus = array();
		foreach(array_diff($this->searchablens(), array_keys($wgContLang->getNamespaces())) as $nsn)
			if ($nsn >= 0)
				$nsplus[] = "+namespace:\"$nsn\"";
		#if (count($nsminus) || count($nsplus))
		#	if (count(
		#	$q = "($q) " . implode(" ", (count($nsminus < count($nsplus$ns);
		if (count($nsminus) > 2)
			$q = "($q) +namespace:(" . implode(" OR ", array_diff(array_keys($wgContLang->getNamespaces()), $nsminus)) . ")";
		$ftype = trim($wgRequest->getText("filetype"));
		if (strlen($ftype))
			$q = (strlen($q) ? "($q) AND " : "") . "filetype:".$this->escape($ftype);
		if (($lang = trim($wgRequest->getText("lang"))) !== "")
			$q = (strlen($q) ? "($q) AND " : "") . "lang:".$this->escape($lang);
		if (($user = trim($wgRequest->getText("lastedit", ""))) !== "")
			$q = (strlen($q) ? "($q) AND " : "") . "user:".$this->escape($user, '"');
		return $this->addcategories(trim($q));
	}

	/*
	 * Execute the query for this search object and display results to
	 * user.
	 */
	function run() {
	global	$wgRequest;
	global	$wgUser;
		$this->langcount = false;
		$what = $wgRequest->getVal("what");
		if ($what === "prefs") {
			$this->prefs_form();
			return;
		}
		if ($wgRequest->getInt("adv", 0) == 1 && is_null($wgRequest->getVal("advdo", null))) {
			$this->advanced_search_form();
			return;
		}
		$this->query = $this->formsearchquery();
		if ($wgRequest->getInt("adv", 0) != 1)
			$this->printSearchForm();
		else
			$this->advanced_search_form();
		if (!strlen($this->query) && !strlen($this->nterms))
			return;
		$this->page = $wgRequest->getInt("page", 1);;
		if ($wgUser->getOption("wikiasearcholdformat") == 1) 
			$this->runTraditionalSearch();
		else
			$this->runSearch();
	}

	function searchablens() {
	global	$wgRequest;
	global	$wgContLang;
	static	$sns = null;
		if (!is_null($sns))
			return $sns;
		$sns = array();
		$allns = 0;
		if ($wgRequest->getInt("adv", 0) != 1)
			$allns = 1;
		foreach ($wgContLang->getNamespaces() as $num => $ns) {
			if ($allns)
				$sns[] = $num;
			else if ($wgRequest->getInt("ns$num", 0) == 1)
				$sns[] = $num;
		}
		if (!count($sns))
			$sns = array_keys($wgContLang->getNamespaces());
		return $sns;
	}

	function dosearchns($ns) {
		return in_array($ns, $this->searchablens());
	}

	function advanced_search_form() {
	global	$wgOut;
	global	$wgRequest;
	global	$wgWikiaSearchPageName;
	global	$wgWikiaSearchStaticDir;
	global	$wgContLang;
	global	$wslangs;
		/* This doesn't work in monobook in 1.5 */ 
		$wgOut->addScript("<script type='text/javascript' src='$wgWikiaSearchStaticDir/adv_search.js'></script>");

		$wgOut->setPageTitle(wfMsg("wikiasearchadvtitle"));
		
		$querytext = $this->htmlmsg("wikiasearchadvquery");
		$categorytext = $this->htmlmsg("wikiasearchadvcategory");
		$submittext = $this->htmlmsg("wikiasearchsubmit");
		$ontext = $this->htmlmsg("wikiasearchonwikitext");
		$thiswikitext = $this->htmlmsg("wikiasearchonthiswiki");
		$otherwikistext = $this->htmlmsg("wikiasearchonotherwikis");
		$otherwikitext = $this->htmlmsg("wikiasearchotherwikitext");
		$ncats = $wgRequest->getInt("ncats", 1);
		
		/* User is adding a category */
		if (!is_null($wgRequest->getVal("addcat", null)))
			++$ncats;
		
		$me = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$simplesearch = htmlspecialchars($me->getLocalURL());
		$backtosimple = $this->htmlmsg("wikiasearchadvbacktonormal");
		$acturl = htmlspecialchars($me->getLocalURL());
		
		/* Generate box of categories */
		$cats = '';
		$addcat = $this->htmlmsg("wikiasearchadvaddcat");
		if (($nwcats = $this->ncats()) > 300) {
			$lastcat = "<input type=\"submit\" name=\"addcat\" value=\"$addcat\" />";
			$onecat = <<<END
<tr>
<th><label for="category%d">$categorytext</label></td>
<td><input id="category%d" type="text" name="category%d" value="%s" />%s</td>
</tr>
END;
			$categories = array();
			for ($i = 1; $i <= $ncats; ++$i) {
				$categories[$i] = $wgRequest->getText("category$i");
				$cats .= sprintf($onecat, $i, $i, $i, htmlspecialchars($categories[$i]),
						($i == $ncats && $ncats <= 10) ? $lastcat : "");
			}
		} else if ($nwcats) { /* Few enough cats to show drop-down */
			$catlist = $this->cats();
			$lastcat = '';
			for ($i = 1; $i <= $ncats; ++$i) {
				if ($i == $ncats)
					$lastcat = <<<END
<br />
<input type="submit" name="addcat" value="$addcat" />
END;
				$chooser = '';
				foreach ($catlist as $cat) {
					$sel = ($wgRequest->getText("category$i") == $cat) ?
						" selected='selected'" : "";
					$chooser .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($cat),
								$sel, htmlspecialchars(str_replace("_", " ", $cat)));
				}
				$acat = $wgRequest->getText("category$i");
				$cats .= <<<END
<tr>
<th><label for="category$i">$categorytext</label></td>
<td>
<select name="category$i" id="category$i">
<option value=""></option>
$chooser
</select>
$lastcat
</td>
</tr>
END;
			}
		}
		
		$qall = htmlspecialchars($wgRequest->getText("qall", ""));
		$qnone = htmlspecialchars($wgRequest->getText("qnone", ""));
		$qsome = htmlspecialchars($wgRequest->getText("qsome", ""));
		$otherwikihelp = wfMsgHtml("wikiasearchotherwikihelp", $otherwikitext);
		$encwiki = htmlspecialchars($wgRequest->getText("wiki", ""));
		$content = $this->htmlmsg("wikiasearchadvcontent");
		$allwords = $this->htmlmsg("wikiasearchadvallwords");
		$somewords = $this->htmlmsg("wikiasearchadvsomewords");
		$nowords = $this->htmlmsg("wikiasearchadvnowords");
		$metadata = $this->htmlmsg("wikiasearchadvmeta");
		$intext = $this->htmlmsg("wikiasearchadvin");
		$titlebody = $this->htmlmsg("wikiasearchadvtitlebody");
		$titleonly = $this->htmlmsg("wikiasearchadvtitleonly");
		$bodyonly = $this->htmlmsg("wikiasearchadvbodyonly");
		$bothwikitext = $this->htmlmsg("wikiasearchbothwikitext");
		$inns = $this->htmlmsg("wikiasearchadvinnamespaces");
		$filesoftype = $this->htmlmsg("wikiasearchadvfiletype");
		$anytype = $this->htmlmsg("wikiasearchadvanytype");
		$language = $this->htmlmsg("wikiasearchadvlanguage");
		$anylang = $this->htmlmsg("wikiasearchadvlangany");
		
		/* List of languages to search for */
		$sel = ($wgRequest->getText("lang", "") === "") ? " selected='selected'" : "";
		$langs = <<<END
<select id="lang" name="lang">
<option value=""$sel>$anylang</option>
END;
		foreach ($wslangs as $lang) {
			$enclang = htmlspecialchars($lang);
			$encname = $this->htmlmsg("wikiasearchadvlang$lang");
			$sel = ($wgRequest->getText("lang", "") === $lang) ? " selected='selected'" : "";
			$langs .= <<<END
<option value="$enclang"$sel>$encname</option>
END;
		}
		$langs .= "</select>";
		$ns = '';
		$namespaces = $wgContLang->getNamespaces();
		$i = 1;
		$filetypes = '';
		
		/* List of filetypes */
		foreach ($this->getFiletypes() as $type) {
			$enctype = htmlspecialchars($type);
			if ($wgRequest->getText("filetype", "") == $type)
				$selected = " selected='selected'";
			else
				$selected = '';
			$filetypes .= "<option value=\"$enctype\">$enctype</option>\n";
		}
		
		/* Namespaces checkboxes */
		foreach ($namespaces as $num => $name) {
			if ($num < 0)
				continue;
			$encname = htmlspecialchars(str_replace("_", " ", $name));
			if (!strlen($encname))
				$encname = wfMsg("wikiasearchadvns0");
			$tr = $checked = '';
			if ($this->dosearchns($num))
				$checked = ' checked="checked"';
			if (($i++ % 2) == 0)
				$tr = '</tr><tr>';
			$ns .= <<<END
<td>
<input type="checkbox" id="ns$num" name="ns$num" value="1" $checked />
<label for="ns$num">$encname</label>
</td>
$tr
END;
		}
		
		/* Where to search (title, body) */
		$tbselected = $titleselected = $bodyselected = "";
		if ($wgRequest->getText("where", "") === "name")
			$titleselected = " selected='selected'";
		else if ($wgRequest->getText("where", "") === "content")
			$bodyselected = " selected='selected'";
		else
			$tbselected = " selected='selected'";
		$localsel = array();
		$localsel[$this->getlocal()] = " selected='selected'";
		$langhelp = $this->htmlmsg("wikiasearchadvlanghelp");
		$lastedited = $this->htmlmsg("wikiasearchadvlastedit");
		$letext = htmlspecialchars($wgRequest->getText("lastedit"));
		$ngrams = htmlspecialchars($wgRequest->getText("ngrams", ""));
		$ngramtext = $this->htmlmsg("wikiasearchngramlabel");
		$ordiv = $this->htmlmsg("wikiasearchordiv");
		$form = <<<END
<p><a href="$simplesearch">$backtosimple</a></p>

<form method="get" action="$acturl">
	<input type="hidden" name="adv" value="1" />
	<input type="hidden" name="ncats" value="$ncats" />
	<fieldset>
	<legend>$content</legend>
	<table class="wikiasearchadvancedform">
		<tr>
			<th><label for="qall">$allwords</label></th>
			<td><input id="qall" type="text" name="qall" value="$qall" /></td>
		</tr>
		<tr>
			<th><label for="qsome">$somewords</label></th>
			<td><input id="qsome" type="text" name="qsome" value="$qsome" /></td>
		</tr>
		<tr>
			<th><label for="qnone">$nowords</label></th>
			<td><input id="qnone" type="text" name="qnone" value="$qnone" /></td>
		</tr>
		<tr><td colspan="2" style="text-align: center;">$ordiv</td></tr>
		<tr>
			<th><label for="ngrams">$ngramtext</label></th>
			<td><input id="ngrams" name="ngrams" type="text" value="$ngrams" /></td>
		</tr>
		<tr>
			<th><label for="where">$intext</label></th>
			<td>
			<select name="where" id="where">
				<option value="" $tbselected>$titlebody</option>
				<option value="name" $titleselected>$titleonly</option>
				<option value="content" $bodyselected>$bodyonly</option>
			</select>
			</td>
		</tr>
		<tr>
		<th style="vertical-align: top"><label for="local">$ontext</label></th>
		<td>
			<dl class="pretty">
			<dt>
			<select id="local" name="local">
				<option value="1" {$localsel[1]}>$thiswikitext</option>
				<option value="0" {$localsel[0]}>$bothwikitext</option>
				<option value="3" {$localsel[3]}>$otherwikistext</option>
				<option value="2" {$localsel[2]}>$otherwikitext</option>
			</select>
			<span id="wikiasearchotherwikibox"><input type="text" name="wiki" value="$encwiki" id="wiki" /></span>
			</dt>
			<dd><label for="wiki">$otherwikihelp</label></dd>
			</dl>
<span style='display: none'><input type="submit" value="$submittext" name="advdo" /></span>
		</td>
		</tr>
	</table>
	</fieldset>
	<fieldset>
	<legend>$metadata</legend>
	<table class="wikiasearchadvancedform">
		$cats
		<tr>
		<th><label for="filetype">$filesoftype</label></th>
		<td>
<select name="filetype" id="filetype">
<option value="">$anytype</option>
$filetypes
</select>
		</td>
		</tr>
		<tr>
		<th>$inns</th>
		<td><table><tr>$ns</tr></table></td>
		</tr>
		<tr>
		<th>$language</th>
		<td><dl class="pretty"><dt>$langs</dt><dd>$langhelp</dd></dl></td>
		</tr>
		<tr>
		<th><label for="lastedit">$lastedited</label></th>
		<td><input name="lastedit" id="lastedit" value="$letext" /></td>
		</tr>
	</table>
	</fieldset>
	<p><input type="submit" value="$submittext" name="advdo" /></p>
</form>
END;
		$wgOut->addHTML($form);
		return;
	}

	/*
	 * Run a search with the old-style output, separate title and body matches
	 */
	function runTraditionalSearch() {
	global	$wgRequest;
	global	$wgOut;
	global	$wgWikiaSearchPageName;

		if ((isset($_REQUEST['go']) ||
		    $wgRequest->getText("dogo", "") != "") && (($t = $this->gotoarticle($this->query)) != null)) {
			$wgOut->redirect($t->getFullURL());
			return;
		}

		if (isset($_REQUEST['go']) || $wgRequest->getText("dogo", "") != "") {
			$t = Title::newFromText($this->query);
			if (!is_null($t))
				$wgOut->addWikiText(wfMsg("nogomatch", $this->query));
		}

		$pp = $this->pp();
		$me = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$limit = $wgRequest->getInt("limit", $pp);
		$offset = $wgRequest->getInt("offset", 0);
		$this->page = 1 + $offset / $pp;

		$title = $this->fetchSearchURL($this->query, $this->nativewiki(), null, null, 
					$limit, $this->page, "title", "title");
		if ($title === false) {
			$this->outerr($this->curlerror);
			return;
		}
		
		$body = $this->fetchSearchURL($this->query, $this->nativewiki(), null, null, 
					$limit, $this->page, "body", "title");
		if ($body === false) {
			$this->outerr($this->curlerror);
			return;
		}
		$pager = wfViewPrevNext($offset, $limit, $me,
				 "wiki=".urlencode($this->nativewiki())."&local={$this->local}&search=".urlencode($this->query));
					
		if ((list($results_body, $nbody) = $this->parseResults($body)) === false)
			return;
		if ((list($results_title, $ntitle) = $this->parseResults($title)) === false)
			return;
		$wgOut->addHTML("<p>$pager</p>");
		if (count($results_title) > 0) {
			$wgOut->addHTML(wfElement("h2", null, wfMsg("wikiasearcholdtitleheader")));
			$this->printTraditionalResults($results_title);
		} else
			$wgOut->addHTML(wfElement("h2", null, wfMsg("wikiasearcholdtitlenone")));
		if (count($results_body) > 0) {
			$wgOut->addHTML(wfElement("h2", null, wfMsg("wikiasearcholdbodyheader")));
			$this->printTraditionalResults($results_body);
		} else
			$wgOut->addHTML(wfElement("h2", null, wfMsg("wikiasearcholdbodynone")));
		$wgOut->addHTML("<p>$pager</p>");
	}

	function printTraditionalResults($results) {
	global	$wgOut;
		$wgOut->addHTML("<ol>");
		foreach ($results as $r) {
			$wgOut->addHTML($this->formatTraditionalResult($r));
		}
		$wgOut->addHTML("</ol>");
	}

	function formatTraditionalResult($result) {
	global	$wgOut;
		$titleobj = Title::makeTitleSafe($result->namespace, $result->title);
		$titlelink = htmlspecialchars($titleobj->getLocalURL());
		$title = htmlspecialchars($titleobj->getPrefixedText());
		$sizepart = htmlspecialchars(wfMsg("wikiasearcholdsize", $result->size));
		$context = $result->fragment;
		$line = <<<END
<li>
<a href="$titlelink">$title</a> $sizepart<br />
$context
</li>
END;
		return $line;
	}

	/*
	 * Save the user's search preferences.
	 */
	function prefs_form_submit() {
	global	$wgRequest;
	global	$wgUser;
	global	$wgOut;
	global	$wgWikiaSearchPageName;
		$oldformat = $wgRequest->getInt("oldformat");
		$wgUser->setOption("wikiasearcholdformat", $oldformat);
		$perpage = $wgRequest->getInt("perpage");
		$okay_perpage = array(10, 25, 50);
		if (in_array($perpage, $okay_perpage))
			$wgUser->setOption("wikiasearchperpage", $perpage);
		$wgUser->saveSettings();
		$me = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$wgOut->redirect($me->getFullURL());
	}

	/*
	 * Print a form for the user to set his preferences, or if posting,
	 * change the prefs.
	 */
	function prefs_form() {
	global	$wgOut;
	global	$wgWikiaSearchPageName;
	global	$wgUser;
	global	$wgRequest;
		if ($wgUser->getID() == 0) {
			$wgOut->addWikiText(wfMsg("wikiasearchprefsmustlogin"));
			return;
		}
		if ($wgRequest->wasPosted() && $wgUser->matchEditToken($wgRequest->getVal('token'))) {
			$this->prefs_form_submit();
			$wgOut->addWikiText(wfMsg("wikiasearchprefssaved"));
		}
		$hasoldformat = (int)$wgUser->getOption("wikiasearcholdformat");
		if ($hasoldformat)
			$oldformatcheck = ' checked="checked"';
		else
			$oldformatcheck = '';
		$me = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$myurl = $me->getLocalURL("what=prefs");
		$backurl = $me->getLocalURL();
		$backtext = $this->htmlmsg("wikiasearchprefsback");
		$perpagetext = $this->htmlmsg("wikiasearchperpage");
		$okay_perpage = array(10, 25, 50);
		$opts = "";
		foreach($okay_perpage as $ok) {
			$sel = "";
			if ($wgUser->getOption("wikiasearchperpage") == $ok)
				$sel = " selected='selected'";
			$opts .= "<option value='$ok'$sel>$ok</option>";
		}
		$perpagehelptext = $this->htmlmsg("wikiasearchperpagehelp");
		$oldformathelptext = $this->htmlmsg("wikiasearcholdformathelp");
		$form = <<<END
<p><a href="$backurl">$backtext</a></p>

<p>%s</p>

<form method="post" action="%s">
<fieldset>
<legend>%s</legend>
<dl class="wikiasearchsettings">

<dt>
<input type="checkbox" id="oldformat" name="oldformat" value="1" $oldformatcheck />
<label for="oldformat">%s</label>
</dt>
<dd>$oldformathelptext</dd>
</dt>

<dt>
<label for="perpage">$perpagetext</label>
<select id="perpage" name="perpage">
$opts
</select>
</dt>
<dd>$perpagehelptext</dd>
</dt>

</fieldset>
<input type="hidden" name="token" value="%s" />
<p><input type="submit" value="%s" /></p>
END;
		$wgOut->addHTML(sprintf($form, 
			$this->htmlmsg("wikiasearchprefsintro"),
			htmlspecialchars($myurl),
			$this->htmlmsg("wikiasearchprefsresultsdisplay"),
			$this->htmlmsg("wikiasearchprefsoldformat"),
			$wgUser->editToken(),
			$this->htmlmsg("wikiasearchprefssubmit")));
	}

	/*
	 * Internal implmenentation for run().
	 */
	function runSearch() {
	global	$wgOut;
	global	$wgWikiaSearchNative;
	global	$wgWikiaSearchPageName;
	global	$wgRequest;
	
		if ((isset($_REQUEST['go']) ||
		    $wgRequest->getText("dogo", "") != "") && (($t = $this->gotoarticle($this->query)) != null)) {
			$wgOut->redirect($t->getFullURL());
			return;
		}
		
		$wgOut->setPageTitle(wfMsg("wikiasearchpagetitle", $this->query));
		$this->local = $this->getlocal();
		$this->sort = $wgRequest->getText("sort", "normal");
		/* results for this wiki */
		if ($this->local == 3)
			$content = $this->fetchSearchURL($this->query, null, $this->nativewiki(), null, 
						$this->pp(), $this->page, $this->sort);
		else
			$content = $this->fetchSearchURL($this->query, $this->nativewiki(), null, null, 
						$this->local ? $this->pp() : 5, $this->page, $this->sort);
		if ($content === false) {
			$this->outerror($this->curlerror);
			return;
		}
		
		if ((list($this->results_native, $this->nresults) = $this->parseResults($content)) === false)
			return;

		/* results for other wikis */
		if (!$this->local) {
			$content = $this->fetchSearchURL($this->query, null, $this->nativewiki(), 0, 5, 1, $this->sort);
			if ($content === false) {
				$this->outerror($this->curlerror);
				return;
			}
			if ((list($this->results_other, $null) = $this->parseResults($content)) === false)
				return;
		}

		/* -- */
		$this->printHeaderLocal($this->results_native, $this->nresults);
		$this->printResults($this->results_native, $this->nresults, true);
		
		$self = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$ll = ($this->local) ? $this->local : 1;
		$morelocalurl = $self->getLocalURL("local={$ll}&search=".urlencode($this->query));

		if (!$this->local && $this->nresults > 5) {
			$wgOut->addHTML(sprintf("<p id=\"wikiamorelocal\"><a href=\"%s\">%s</a></p>\n",
							htmlspecialchars($morelocalurl),
						$this->htmlmsg("wikiasearchmoreresultsnative")));
		}
		
		if (!$this->local && count($this->results_other)) {
			$wgOut->addHTML(sprintf("<h2 class=\"wikiasearch\">%s</h2>",
						$this->htmlmsg("wikiasearchheaderotherwikis")));
			$this->printResults($this->results_other, 0, false);
		}
	}

	/*
	 * Print the "you searched for" header.
	 */
	function printHeaderLocal($results, $num) {
	global	$wgOut;
	global	$wgUser;
	global	$wgRequest;
		$start = $this->page * $this->pp() - ($this->pp() - 1);
		$lim = $this->local ? $this->pp() : 5;
		$end = min($this->page * $lim, $num);
		$dymlink = '<a href="%s">%s</a>';
		$dym = <<<END
<div class="wikiasearchdym">%s</div>
END;
		$dymtext = "";
		if ($this->suggestion !== null) {
			$dymurl = $this->withNewTerm($this->suggestion);
			$dymltxt = sprintf($dymlink, htmlspecialchars($dymurl),
				htmlspecialchars($this->suggestion));
			$dymtext = sprintf($dym, wfMsgHtml("wikiasearchdymtext", $dymltxt));
		}
		$langtext = '';
		if (count($results) && (((float)$this->langcount) / count($results)) > 0.5 && $start == 1) {
			$docsin = htmlspecialchars(wfMsg("wikiasearchdocsin", 
						wfMsg("wikiasearchadvlang" . $wgUser->getOption("language"))));
			$dlink = htmlspecialchars($wgRequest->appendQuery("lang=" . $wgUser->getOption("language")));
			$link = "<a href=\"$dlink\">$docsin</a>";
			$ltext = wfMsgHtml("wikiasearchinlang", $link);
			$langtext = "<div class='wikiasearchdym'>$ltext</div>";
		}
	 	$header = <<<END
<h2 class="wikiasearch">%s</h2>
%s
%s
END;
		if ($num == 0) {
			$wgOut->addHTML($dymtext);
			return;
		}
		$title = Title::newFromText($this->query);
		if (!is_null($title)) {
			$sk =& $wgUser->getSkin();
			$result = $sk->makeLinkObj($title, $this->query);
		} else {
			$result = htmlspecialchars($this->query);
		}
		
		if ($this->local == 3) {
			$wgOut->addHTML(sprintf($header,
				wfMsg("wikiasearchresultsnumlocal3", $start, $end, $num, $result), $dymtext, $langtext));
		} else if ($this->local != 2) {
			$wgOut->addHTML(sprintf($header, 
				wfMsg("wikiasearchresultsnumlocal", $start, $end, $num,
							$result), $dymtext, $langtext));
		} else {
			$wgOut->addHTML(sprintf($header,
				wfMsg("wikiasearchresultsnumlocal2", $start, $end, $num,
					$this->nativewiki(), htmlspecialchars($this->query)), $dymtext, $langtext));
		}
	}
 	
	/*
	 * Print the input box to type the search in.
	 */
	function printSearchForm() {
	global	$wgWikiaSearchPageName;
	global	$wgOut;
	global	$wgRequest;
		$self = Title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$advlink = htmlspecialchars($self->getLocalURL("adv=1"));
		$advtext = $this->htmlmsg("wikiasearchadvlinktext");
		$ltxt = $this->getlocal();
		$template = <<<EOF
<form action="%s" method="get" id="wikiasearch">
<div id="wikiasearchwrapper">
	<div id="wikiasearchtoplinks">
		<a href="%s"><strong>%s</strong></a>&nbsp;|&nbsp;<a
		   href="%s"><strong>%s</strong></a>&nbsp;|&nbsp;<a
		   href="%s"><strong>%s</strong></a>
	</div>
	<input type="text" name="search" value="%s" id="wikiasearchinput" />
	<input type="hidden" name="local" value="%s" />
	<input type="submit" name="dogo" value="%s" />
	<input type="submit" name="dosearch" value="%s" />
</div>
</form>
EOF;

		$helpurl = "";

		$selfurl = $self->getLocalURL();
		$help = Title::makeTitleSafe(NS_HELP, wfMsg("wikiasearchhelplinkpage"));
		if (!is_null($help))
			$helpurl = $help->getLocalURL();
		$prefsurl = $self->getLocalURL("what=prefs");
		$form = sprintf($template, 
				$selfurl, $advlink, $advtext,
				htmlspecialchars($prefsurl),
				$this->htmlmsg("wikiasearchprefslinktext"),
				htmlspecialchars($helpurl),
				$this->htmlmsg("wikiasearchhelplinktext"),
				htmlspecialchars($this->query), $ltxt,
				$this->htmlmsg("wikiasearchsubmitgo"),
				$this->htmlmsg("wikiasearchsubmit"));
		$wgOut->addHTML($form);
	}
				
	/*
	 * Given a query and its parameters, return the raw result text from the
	 * backend server.
	 */
	function fetchSearchURL($query, $onlywiki, $nowiki, $onlyns, $limit, $page, $type = null, $sort = "normal") {
	global	$wgWikiaSearchURL, $wgOut;
		$offset = ($page * $this->pp()) - $this->pp();
		$aq = urlencode($query);
		$ng = '';
		if (!strlen($aq)) {
			$aq = urlencode($this->nterms);
			$ng = '&type=ngram';
		}
		$queryURL = sprintf("%s?q=%s&l=%d&o=%d&sort=%s%s", 
				$wgWikiaSearchURL, $aq, $limit, $offset,
				urlencode($sort), $ng);
		if (!is_null($onlywiki))
			$queryURL .= "&onlywiki=".urlencode($onlywiki);
		$nws = '';
		if (!is_null($nowiki))
			$nws .= urlencode($nowiki) . ",";
		$nws .= $this->adultwikis();
		$queryURL .= "&nowiki=$nws,staff,internal";
		if (!is_null($onlyns))
			$queryURL .= "&onlyns=".urlencode($onlyns);
		if (!is_null($type))
			$queryURL .= "&type=".urlencode($type);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $queryURL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$this->curlerror = curl_error($ch);
		curl_close($ch);

		return $result;
	}

	/*
	 * Turn raw result text into an array of AResult objects.
	 */
	function parseResults($result) {
	global	$wgOut;
	global	$wgUser;
		$results = array();		
		$lines = explode("\n", $result);
		$num = 0;
		$lc = 0;
		$userlang = $wgUser->getOption("language");
		
		foreach ($lines as $line) {
			if (strlen($line) == 0)
				continue;

			$bits = split("=", $line, 2);
			$type = $bits[0];
			$content = $bits[1];
			$words = explode(" ", $content);

			if ($type === "error") {
				$errtemplate = <<<END
<div class="wikiasearcherror">%s</div>
END;
				$wgOut->addHTML(sprintf($errtemplate, 
						htmlspecialchars(wfMsg("wikiasearcherror",
							urldecode($words[0])))));
				return false;
			}

			if ($type === "status") {
				if ($words[0] === "error") {
					continue;
				} else {
					$num = $words[1];
					if ($this->suggestion === null && $words[2] !== "-")
						$this->suggestion = trim(urldecode($words[2]));
					continue;
				}
			}

			$wiki = $words[0];
			$namespace = $words[1];
			$title = urldecode($words[2]);
			$fragment = urldecode($words[3]);
			$size = $words[4];
			$date = $words[5];
			$lang = $words[6];
			if ($lang !== $userlang && $lang !== "null")
				$lc++;
			$results[] = new AResult($wiki, $namespace, $title, $fragment, $size, $date);
		}

		if ($this->langcount === false)
			$this->langcount = $lc; 
		return array($results, $num);
	}

	/*
	 * Convenience: display every element of an AResult array.
	 * If $thiswiki==true, results are for the local wiki, otherwise for
	 * remote wikis.
	 */
	function printResults($results, $nresults, $thiswiki = true) {
	global	$wgOut;
		
		if (!count($results)) {
			$t = Title::newFromText($this->query);
			if (!is_null($t))
				$wgOut->addWikiText(wfMsg("wikiasearchnoresultsarticle", $this->query));
			else
				$wgOut->addWikiText(wfMsg("wikiasearchnoresults", $this->query));
			return;
		}

		if ($this->local)
			$wgOut->addHTML($this->makePager($results, $nresults, $this->page));

		foreach ($results as $result) {
			$wgOut->addHTML($this->formatResult($result, $thiswiki));
		}

		if ($this->local)
			$wgOut->addHTML($this->makePager($results, $nresults, $this->page));
	}

	/*
	 * Print a single result to the user.
	 * If $thiswiki==true, results are for the local wiki, otherwise for
	 * remote wikis.
	 */
	function formatResult($result, $thiswiki) {
	global	$wgWikiaSearchPageName;
		$restemplate = <<<END
<div class="searchtitle"><a href="%s">%s</a></div>
<div class="searchfragment">%s<br clear="all" /></div>
<span class="searchfooter">%s - %.1fk - %s</span><span class="searchextras">%s</span>
END;
		
		$imgfrag = '';
		$title = Title::makeTitleSafe($result->namespace, $result->title);
		if (!is_object($title))
			$title = Title::makeTitleSafe(0, "--invalid title--");
		if ($result->namespace == 6) {
			$image = Image::newFromTitle($title);
			if (is_object($image) && $image->canRender()) {
				$imgurl = htmlspecialchars($image->createThumb(50));
				$enctit = htmlspecialchars($title->getPrefixedText());
				if (strlen($imgurl)) {
					$imgfrag = "<img src=\"$imgurl\" class=\"wikiasearchimageresult\" alt=\"$enctit\" />";
				}
			}
		}
			
		$url = sprintf("%swiki/%s", $this->cityurl($result->wiki), $title->getPrefixedDBKey());
		if (!$thiswiki || $this->local == 3)
			$aurl = $url;
		else
			$aurl = $title->getLocalURL();
			#$aurl = preg_replace("{^http://[^/]+}", "", $url);
			#$aurl = sprintf("/wiki/%s", $title->getPrefixedText());

		$fmttime = date('j M Y', wfTimestamp(TS_UNIX, $result->date));

		$searchthere = "";
		$frag = $result->fragment;
		if ($frag === "-")
			$frag = '';
		if (!$thiswiki) {
			$sturl = sprintf("%s/index.php?title=Special:%s&local=1&search=%s",
					$this->cityurl($result->wiki), $wgWikiaSearchPageName, $this->query);
			$stlabel = wfMsgHtml("wikiasearchoverthere", htmlspecialchars($result->wiki));
			$searchthere = sprintf(" - <a href=\"%s\">%s</a>",
				htmlspecialchars($sturl), ($stlabel));
		}
		return sprintf($restemplate, 
				htmlspecialchars($aurl), htmlspecialchars(str_replace("_", " ",
					$title->getPrefixedText())),
				$imgfrag . $frag, 
				htmlspecialchars($url), $result->size / 1024.0, $fmttime,
				$searchthere);
	}

	/**
	 * Return HTML for a paging section at the top/bottom of the page.
	 */
	function makePager($results, $ntotal, $thispage) {
	global	$wgWikiaSearchPageName;
		$totalpages = $ntotal ? ($ntotal / (float)$this->pp()) : 0;
		$totalpages = floor(0.5 + $totalpages);
		if ($totalpages < 2)
			return "";
		$wikibit = '';
		if ($this->local == 2)
			$wikibit = "&wiki=".urlencode($this->nativewiki());
		$me = title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		$prev = $next = "";
		if ($thispage > 1) {
			$prevurl = $me->getLocalURL("page=".($thispage - 1)."&search=".urlencode($this->query).
				"&local={$this->local}$wikibit");
			$prevtext = $this->htmlmsg("wikiasearchprev");
			$prev = sprintf("<a href=\"%s\">%s</a>", $prevurl, $prevtext);
		}
		if ($thispage < $totalpages) {
			$nexturl = $me->getLocalURL("page=".($thispage + 1)."&search=".urlencode($this->query).
				"&local={$this->local}$wikibit");
			$nexttext = $this->htmlmsg("wikiasearchnext");
			$next = sprintf("<a href=\"%s\">%s</a>", $nexturl, $nexttext);
		}

		$result = "";
		for ($i = max($thispage - 5, 1); $i <= min($thispage + 5, $totalpages); $i++) {
			if ($i == $thispage)
				$result .= " $i ";
			else {
				$url = $me->getLocalURL("page=$i&search=".urlencode($this->query)."&local={$this->local}$wikibit");
				$result .= sprintf("<a href=\"%s\">%d</a> ", htmlspecialchars($url), $i);
			}
		}
		$pagestext = $this->htmlmsg("wikiasearchpagestext");
		return "<div class=\"wikiasearchpager\">$pagestext $prev $result $next</div>";
	}

	/**
	 * Return the URL for a Wikicity.
	 */
	function cityurl($dbname) {
		$db =& wfGetDB(DB_SLAVE);
		$res = $db->select('wikicities`.`city_list', 'city_url', array('city_dbname' => $dbname));
		$o = $db->fetchObject($res);
		if ($o === false)
			$url = "http://notreal.wikicities.com/";
		else
			$url = $o->city_url;
		$db->freeResult($res);
		return $url;
	}

	/**
	 * Return a URL for the current search parameters with a new query.
	 */
	function withNewTerm($term) {
	global	$wgWikiaSearchPageName;
		$me = title::makeTitle(NS_SPECIAL, $wgWikiaSearchPageName);
		return $me->getLocalURL("search=".urlencode($this->addcategories($term))."&local={$this->local}");
	}

	/**
	 * Return an array of all mime types used in this wiki.
	 */
	function getFiletypes() {
		$db =& wfGetDB(DB_SLAVE);
		extract($db->tableNames("image"));
		$r = $db->query("SELECT DISTINCT img_major_mime, img_minor_mime FROM $image");
		$types = array();
		while (($o = $db->fetchObject($r)) !== false) {
			if (strlen($o->img_major_mime) && strlen($o->img_minor_mime))
				$types[] = $o->img_major_mime . "/" . $o->img_minor_mime;
		}
		$db->freeResult($r);
		return $types;
	}

	/**
	 * Return the number of categories that exist in the wiki.
	 */
	function ncats() {
		$db =& wfGetDB(DB_SLAVE);
		extract($db->tableNames("categorylinks"));
		$r = $db->query("SELECT COUNT(DISTINCT cl_to) AS cnt FROM $categorylinks");
		if (($o = $db->fetchObject($r)) === false) {
			$db->freeResult($r);
			return 0;
		}
		$db->freeResult($r);
		return $o->cnt;
	}

	/**
	 * Return a list of categories on this wiki.
	 */
	function cats() {
		$db =& wfGetDB(DB_SLAVE);
		extract($db->tableNames("categorylinks"));
		$re = array();
		$r = $db->query("SELECT DISTINCT cl_to FROM $categorylinks");
		while (($o = $db->fetchObject($r)) !== false) {
			$re[] = $o->cl_to;
		}
		$db->freeResult($r);
		return $re;
	}

	/**
	 * Return the list of adult wikis.
	 */
	function adultwikis() {
		if ($this->isadult())
			return "";
		$db =& wfGetDB();
		$r = $db->query("SELECT city_dbname FROM wikicities.city_list WHERE city_adult=1");
		$w = array();
		while (($o = $db->fetchObject($r)) !== false) {
			$w[] = $o->city_dbname;
		}
		$db->freeResult($r);
		return implode(",", $w);
	}

	/**
	 * true/false whether this wiki is an adult wiki.
	 */
	function isadult() {
	global	$wgDBname;
		$db =& wfGetDB(DB_SLAVE);
		$r = $db->query("SELECT 1 FROM wikicities.city_list WHERE city_dbname='$wgDBname' AND city_adult=1");
		$o = $db->fetchObject($r);
		if ($o === false)
			$a = false;
		else
			$a = true;
		$db->freeResult($r);
		return $a;
	}
	
	/**
	 * If an article with a title like this exists, return it.
	 */
	 function gotoarticle($title) {
	 	/* First try the title as is */
	 	$t = Title::newFromText($title);
	 	if (!is_null($t) && $t->exists())
	 		return $t;
	 	/* All lower-case */
	 	$t = Title::newFromText(strtolower($title));
	 	if (!is_null($t) && $t->exists())
	 		return $t;
	 	/* Upper-case */
	 	$t = Title::newFromText(strtolower($title));
	 	if (!is_null($t) && $t->exists())
	 		return $t;
	 	/* Sentence-case */
	 	$t = Title::newFromText(ucwords(strtolower($title)));
	 	if (!is_null($t) && $t->exists())
	 		return $t;
	 	return null;
	 }
	 
	 /**
	  *  Print the given text as an error message.
	  */
	 function outerror($txt) {
	 global	$wgOut;
	 	$t = htmlspecialchars(wfMsg("wikiasearcherror", $txt));
		$wgOut->addHTML("<div class=\"wikiasearcherror\">$t</div>");
	 }	 	
}
