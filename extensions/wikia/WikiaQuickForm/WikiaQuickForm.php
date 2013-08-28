<?php
/**
 * Wrapper to pear lib HTML_QuickForm
 *
 * @package MediaWiki
 * 
 * @author Krzysztof Krzyzaniak (eloy) for wikia.com
 */

/**
 * You have to install these files from pear
 */
require_once("lib/HTML/QuickForm.php");
require_once("lib/HTML/QuickForm/Renderer/Tableless.php");

/**
 * Class to build various forms
 *
 * @package MediaWiki
 * @author Krzysztof Krzyzaniak <eloy@wikia.com>
 */
class WikiaQuickForm extends HTML_QuickForm {

    var $mRequest;
	/**
	 * constructor
	 */
	function WikiaQuickForm( $formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false ) {
        $renderer =& new HTML_QuickForm_Renderer_Tableless();
        $GLOBALS['_HTML_QuickForm_default_renderer'] =& $renderer;
        $renderer->addStopFieldsetElements('submit');
        HTML_QuickForm::HTML_QuickForm( $formName, $method, $action, $target, $attributes, $trackSubmit );
    }
};

?>
