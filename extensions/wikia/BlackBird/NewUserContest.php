<?php

$wgHooks['AddNewAccount'][] = 'fnBlackBirdEnroll';
function fnBlackBirdEnroll() {
        global $wgOut, $wgUser;
	$wgUser->setOption( 'blackbirdenroll', 1 );
	$wgUser->saveSettings();
}
?>