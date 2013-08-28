<?
/*
 * Copyright 2006 Wikia, Inc.  All rights reserved.
 * Use is subject to license terms.
 */

if (!defined('MEDIAWIKI'))
	exit;

require_once("$IP/includes/SpecialPage.php");
require_once("$IP/includes/DatabaseFunctions.php");

$wgExtensionFunctions[] = 'wfActiveusers';

class Activeusers extends SpecialPage {
	function Activeusers() {
		SpecialPage::SpecialPage("Activeusers");
	}

	function execute($par) {
	global	$wgRequest;
	global	$wgOut;
	global	$wgContLang;

		$wgOut->setPageTitle(wfMsg("activeuserstitle"));

		$wgOut->addWikiText(wfMsg("activeusersintro"));
		$offset = $wgRequest->getText('from');
		$count = $wgRequest->getText('count');
		if($count == 0){
		  $count=20;
		}
		$countp=$count+1;
		if ($offset === null)
			$offset = '';
		$db =& wfGetDB(DB_SLAVE);
		$usertable = wfTableName('user');
		$sql = sprintf("SELECT user_name, MAX(rev_timestamp) as last " .
				"FROM $usertable, revision WHERE rev_user=user_id AND rev_user_text >= %s " .
				"GROUP BY user_name ORDER BY user_name ASC LIMIT $countp", $db->addQuotes($offset));
		$r = $db->query($sql);
		$i = 0;
		$text = "";
		while (($o = $db->fetchObject($r)) !== false) {
			if (++$i == $countp)
				break;
			$t = Title::makeTitle(NS_USER, $o->user_name);
			$u = $t->getPrefixedText();
			$text .= wfMsg("activeusersline", $u, $o->user_name, $wgContLang->timeanddate($o->last, true)) . "\n";
		}
		if (strlen($text)) 
			$wgOut->addWikiText($text);
		else
			$wgOut->addWikiText(wfMsg("activeusersempty"));
		if ($i == $countp) {
			$me = Title::makeTitle(NS_SPECIAL, "Activeusers");
			$last = $o->user_name;
			$url = $me->getLocalURL("from=$last");
			$wgOut->addHTML(sprintf("<p><a href=\"%s\">%s</a></p>\n",
				htmlspecialchars($url), 
				htmlspecialchars(wfMsg("activeusersnext", "$count"))));
		}
		$db->freeResult($r);
	}
}

function wfActiveusers() {
	global $wgMessageCache;

	SpecialPage::addPage(new Activeusers);	
	$wgMessageCache->addMessage("activeusers", "Active user list");
	$wgMessageCache->addMessage("activeuserstitle", "Active users");
	$wgMessageCache->addMessage("activeusersintro", "This page shows the list of users who have made edits on this wiki");
	$wgMessageCache->addMessage("activeusersnext", "Next $1 >>");
	$wgMessageCache->addMessage("activeusersempty", "This wiki does not have any edits by registered users yet!");
	$wgMessageCache->addMessage("activeusersline", "* [[$1]] ([[Special:Contributions/$2|contribs]]; last edit: $3)");
}

?>
