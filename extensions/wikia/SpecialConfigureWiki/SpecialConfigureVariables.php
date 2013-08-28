<?php

/**
 * @package MediaWiki
 * @subpackage SpecialPage
 * @author Krzysztof Krzyżaniak <eloy@wikia.com> for Wikia.com
 * @version: 0.1
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This is MediaWiki extension named ConfigureVariables.\n";
    exit( 1 ) ;
}

$wgExtensionFunctions[] = 'wfSpecialConfigureVariablesSetup';

$wgExtensionCredits['specialpage'][] = array(
    "name" => "ConfigureVariables",
    "description" => "Variables Editor for Wikia Factory",
    "author" => "Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>"
);

/**
 * permissions
 */
$wgAvailableRights[] = 'createwiki';
$wgGroupPermissions['staff']['createwiki'] = true;

require_once("SpecialPage.php");
require_once("extensions/wikia/WikiaQuickForm/WikiaQuickForm.php");

/**
 * main parts
 */
function wfSpecialConfigureVariablesSetup() {

/**
 * inner class
 */
class ConfigureVariablesForm extends SpecialPage {

    var $mName, $mPassword, $mRetype, $mReturnto, $mCookieCheck, $mPosted;
    var $mAction, $mCreateaccount, $mCreateaccountMail, $mMailmypassword;
    var $mLoginattempt, $mRemember, $mEmail, $mImportStarter;
    var $mTitle, $mCityId, $mCityDomain;

    /**
     * contructor
     */
    function ConfigureVariablesForm() {
        global $wgLang, $wgAllowRealName, $wgRequest;
        SpecialPage::SpecialPage("ConfigureVariables");
    }

    function execute() {
        global $wgRequest, $wgUser, $wgOut, $wgTitle;

        if (!in_array('createwiki', $wgUser->getRights() )) {
            $wgOut->setArticleRelated( false );
            $wgOut->setRobotpolicy( 'noindex,nofollow' );
            $wgOut->errorpage( 'nosuchspecialpage', 'nospecialpagetext' );
            return;
        }

        $this->mName = trim( $wgRequest->getText( 'wpName' ));
        $this->mPosted = $wgRequest->wasPosted();
        $this->mAction = $wgRequest->getVal( 'action' );
        $this->mTitle = Title::makeTitle( NS_SPECIAL, 'ConfigureVariables' );

        $this->setupMessages();

        $wgOut->setPageTitle( wfMsg('confwikivarpagetitle') );
        $wgOut->setRobotpolicy( 'noindex,nofollow' );
        $wgOut->setArticleRelated( false );

        $this->doVariablesEditor();
    }

    /**
     * setup global messages cache
     * @access private
     */
    function setupMessages() {
        global $wgMessageCache;

        $wgMessageCache->addMessages( array(
            'confwikivarpagetitle' => "Configuration Variables Editor"
        ));
    }

    function doVariablesEditor() {
        global $wgOut, $wlTitle, $wgRequest;
        $fname = __METHOD__;


    	$api = new ApiMain( new FauxRequest(array("action" => "query", "list" => "wkconfvar")) );
    	$api->execute();
    	$wikiaConfVariables =& $api->GetResultData();

        $wikiaConfVariablesGroups = array();
        if (is_array($wikiaConfVariables["query"]["wkconfvar"])) {
            foreach ($wikiaConfVariables["query"]["wkconfvar"] as $key => $value) {
                $wikiaConfVariablesGroups[$value["group"]] = $value["group_name"];
            }
        }

        $tmpl = new WikiaTemplate( dirname( __FILE__ ) . "/templates/" );

        $cityid = 0; #-- we take default data
        $qform = new WikiaQuickForm( "variableselect", "post", $this->mTitle->getLocalUrl( 'action=select' ) );
        $elems = array();
        $baseurl = sprintf("%s?action=ajax&rs=wfwkGetConfGroupVariables&rsargs[0]=%d",
            $wgScript, $cityid);
        $elems[] =& $qform->createElement( 'select',
            'group', 'groups', $wikiaConfVariablesGroups,
            array(
                "size" => 16,
                "id" => "group_id",
                "style" => "width: 20em;",
                "onclick" => "wikiaSelectVariable('group_id', '{$baseurl}');"
            )
        );

        $baseurl = "{$wgScript}?action=ajax&rs=wfwkGetConfVariable&rsargs[0]={$cityid}";

        $elems[] =& $qform->createElement( 'select',
            'variable', 'variable', array('--- select group ---'),
            array(
                "size" => 16,
                "id" => "var_id",
                "style" => "width: 20em;",
                "onclick" => "wikiaSelectVariable('var_id', '{$baseurl}');"
            )
        );
        $qform->addGroup($elems, null, 'Choose group and variable', ''); #--- 'variable'

        /**
         * sent form to browser
         */
        $wgOut->addHTML( $tmpl->execute("header.tmpl.php") );
        $wgOut->addHTML( $qform->toHTML() );
        $wgOut->addHTML( "<div id=\"wikia-variable-editor\"></div>\n" );
        $wgOut->addHTML( $tmpl->execute("debug.tmpl.php") );
    }
}

#--- setup
SpecialPage::addPage(new ConfigureVariablesForm("ConfigureVariables", "createwiki") );
global $wgMessageCache;
$wgMessageCache->addMessage( "configurevariables", "Configuration Variables Editor" );

} /** wfSpecialConfigureVariablesSetup() **/

function dumpvar($var, $return = 0)
{
    if ($return == 0)
        echo "<pre>".print_r($var, 1)."</pre>";
    else
        return "<pre>".print_r($var, 1)."</pre>";
}
?>
