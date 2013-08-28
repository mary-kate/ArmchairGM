<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Example Special Page
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Tomasz Klim <tomek@wikia.com>
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */


// localization messages
$wgExampleMessages = array();
$wgExampleMessages['en'] = array(
	'examplepage'              => 'Example Special Page',
	'exampletext'              => 'Some example content...',
);
$wgExampleMessages['pl'] = array(
	'examplepage'              => 'Przykladowa strona specjalna',
	'exampletext'              => 'Jakas przykladowa tresc...',
);


// new permission type "example"
$wgAvailableRights[] = 'example';
$wgGroupPermissions['*'         ]['example'] = false;  // non-users can't access this page
$wgGroupPermissions['user'      ]['example'] = true;   // logged on users can...
$wgGroupPermissions['bureaucrat']['example'] = true;   // users with higher privileges also can...
$wgGroupPermissions['sysop'     ]['example'] = true;
$wgGroupPermissions['staff'     ]['example'] = true;


// register our new special page
$wgExtensionFunctions[] = 'wfExamplePage';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Example',
	'description' => 'demonstration, how to add new special page',
	'author' => 'Tomasz Klim'
);


// implementation
function wfExamplePage() {
    global $wgMessageCache, $wgExampleMessages, $wgUser;

    // register all localization messages at once
    foreach( $wgExampleMessages as $key => $value ) {
	$wgMessageCache->addMessages( $wgExampleMessages[$key], $key );
    }

    class ExamplePage extends SpecialPage
    {
	function ExamplePage() {
		// to correctly display the name of this special page, you'll need to add localization
		// message equal to the parameter below, but in lower case (e.g. 'ExamplePage' -> 'examplepage')
		SpecialPage::SpecialPage('ExamplePage');
	}

	// this method will be executed by engine, when someone enter the special page
	function execute( $par ) {
		global $wgRequest, $wgOut, $wgUser;

		// permission check method 1 - block users, that don't have permission to access this page
		if (!in_array( 'example', $wgUser->getRights() ) ) {
			$wgOut->setArticleRelated( false );
			$wgOut->setRobotpolicy( 'noindex,follow' );
			$wgOut->errorpage( 'nosuchspecialpage', 'nospecialpagetext' );
			return;
		}

		$this->setHeaders();

		// some action - here you can implement new functionality
		$wgOut->addWikiText( wfMsg( 'exampletext' ) );

		// this will capture both GET and POST
		$message = $wgRequest->getText( 'message' );
		$wgOut->addWikiText( $message );

	} // execute

    } // class

    // permission check method 2 - just don't list this page for users, that don't have permission to access it
    //                             (you can also use UnlistedSpecialPage)
    if ( in_array( 'example', $wgUser->getRights() ) ) {
        SpecialPage::addPage( new ExamplePage );
    }
}

?>
