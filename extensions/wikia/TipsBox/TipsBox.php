<?php
/**
 * Created on 2007-01-24
 * author: Inez Korczyński (inez@wikia.com)
 */

if(!defined('MEDIAWIKI')) {
	die();
	die();
}

global $wgWhitelistRead;
$wgWhitelistRead[] = 'Special:Tips';
/*
global $wgAvailableRights, $wgGroupPermissions;
$wgAvailableRights [] = 'tips';
$wgGroupPermissions['*']['tips'] = true;
$wgGroupPermissions['user']['tips'] = true;
$wgGroupPermissions['staff']['tips'] = true;
$wgGroupPermissions['bureaucrat']['tips'] = true;
*/

/** Set function to initialize extension */
$wgExtensionFunctions[] = 'TipsBox_Setup';
$wgSpecialPages['Tips'] = new SpecialPage('Tips');


function wfSpecialTips() {
	global $wgOut, $wgRequest;

	$which = $wgRequest->getText('w');
	if($which == 'p') { // previous
		echo TipsBox_GetTip(-2);
	} else {
		echo TipsBox_GetTip();
	}
	die();
}

/**
 * Initialize extension
 */
function TipsBox_Setup() {
	global $wgHooks, $wgMessageCache;
	$wgHooks['MonoBookTemplateTipsStart'][] = 'TipsBox_Display';
	$wgMessageCache->addMessage ('tips', 'Did you know?');
}

function TipsBox_GetLastIndex() {
	global $wgCookiePrefix;

	if(isset($_COOKIE[$wgCookiePrefix . '_TipsLastIndex'])) {
		return $_COOKIE[$wgCookiePrefix . '_TipsLastIndex'];
	} else {
		return -1;
	}
}

function TipsBox_SaveNewIndex($index) {
	global $wgCookiePrefix, $wgCookiePath, $wgCookieSecure;
	setcookie( $wgCookiePrefix.'_TipsLastIndex', $index);
}

function TipsBox_GetTip($offset = 0) {
	# get Template:Tips Title object
	$tipsTitle = Title::newFromText("Tips", NS_TEMPLATE);

	# if Template:Tips doesn't exists then return
	if ( ! $tipsTitle->exists() ) {
		return false;
	}

	$tipsArticle = new Article($tipsTitle);

	# get Template:Tips article content..
	$tips = $tipsArticle->getContent();

	# ..end then explode tips (one per line) from it..
	$tipsArray = explode("\n", $tips);

	# ..and count them
	$tipsCount = count($tipsArray);

	# if there is no tips in Template:Tips so return
	if( $tipsCount < 1) {
		return false;
	}

	# get from cookie index of last displayed tip (-1 if not displayed)
	$lastTipIndex = TipsBox_GetLastIndex();

	$newTipIndex = $lastTipIndex + 1 + $offset;
	if($newTipIndex >= $tipsCount) {
		$newTipIndex = 0;
	} else if( !isset ($tipsArray[$newTipIndex]) || $tipsArray[$newTipIndex] == '') {
		$newTipIndex = 0;
	}

	if($lastTipIndex == $newTipIndex) {
		$newTipIndex = $tipsCount - 1;
	}

	# save in cookie index of now displayed tip
	TipsBox_SaveNewIndex($newTipIndex);

	# return wikitext content tip
	global $wgOut;
	return $wgOut->parse($tipsArray[$newTipIndex]);
}


function TipsBox_Display($cos) {

	if(($tip = TipsBox_GetTip()) === false) {
		return;
	}

	$target = Title::newFromText("Tips", NS_SPECIAL);
	$url = $target->getFullURL() . "?w=";

	echo '<script type=\'text/javascript\'>
	var reqTips;
	function tip(w) {
		try {
			reqTips = new XMLHttpRequest();
		} catch (error) {
			try {
				reqTips = new ActiveXObject(\'Microsoft.XMLHTTP\');
			} catch (error) {
				return false;
			}
		}
		reqTips.onreadystatechange = processReqChangeTips;
		reqTips.open(\'GET\', \''.$url.'\' + w);
		reqTips.send(null);
	}
	function processReqChangeTips() {
		if (reqTips.readyState == 4) {
			if (reqTips.status == 200) {
				if(reqTips.responseText != \'\') {
					document.getElementById(\'p-tips-body\').innerHTML = reqTips.responseText;
				}
			}
		}
	}
	</script>';


	echo '<div class="portlet" id="p-tips">
	<h5>'.wfMsgHtml('tips').'</h5>
		<div class="pBody">
			<div id="p-tips-body">'.$tip.'</div>
			<hr />
			<div style="float:left;font-size: 95%;"><a href="#" onClick="tip(\'p\'); return false;">'.wfMsgHtml('allpagesprev').'</a></div>
			<div style="float:right;font-size: 95%;"><a href="#" onClick="tip(\'n\'); return false;">'.wfMsgHtml('allpagesnext').'</a></div>
			<br style="clear:both"/>
		</div>
	</div>';
	return;
}
?>
