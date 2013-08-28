<?php
$wgHooks['SkinTemplateSetupPageCss'][] = 'wfGlobalWikiaCSS';
$wgHooks['BeforePageDisplay'][] = 'wfGlobalWikiaJS';

/**
 * Adds custom user CSS and JavaScript to a page - Dariusz Siedlecki, datrio@wikia.com
 * Usage: $wgHooks['SkinTemplateSetupPageCss'][] = 'wfGlobalWikiaCSS'; $wgHooks['BeforePageDisplay'][] = 'wfGlobalWikiaJS';
 * @param $out Handle to an OutputPage object (presumably $wgOut).
 */

function wfGlobalWikiaCSS( &$out ) {
	global $wgUser;
	
	if (!$wgUser->isAnon())
		$out = '@import "http://www.wikia.com/index.php?title=User:'. str_replace(" ", "_", $wgUser->mName) .'/global.css&action=raw&ctype=text/css&smaxage=18000";';
}

function wfGlobalWikiaJS( &$out ) {
	global $wgJsMimeType, $wgUser;
	
	if (!$wgUser->isAnon())
		$out->addScript('<script language="javascript" type="'. $wgJsMimeType .'" src="http://www.wikia.com/index.php?title=User:'. str_replace(" ", "_", $wgUser->mName) .'/global.js&amp;action=raw&amp;ctype='. $wgJsMimeType .'&amp;dontcountme=s"></script>');
}

?>