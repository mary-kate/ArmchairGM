<?php

/**
 * @package MediaWiki
 * @subpackage SpecialPage
 * @author Krzysztof Krzyżaniak <eloy@wikia.com> for Wikia.com
 * @version: 0.1
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This is MediaWiki extension named ConfigureWiki.\n";
    exit( 1 ) ;
}

$wgExtensionFunctions[] = 'wfSpecialConfigureWikiSetup';

$wgExtensionCredits['specialpage'][] = array(
    "name" => "ConfigureWiki",
    "description" => "Configure wikia for Wikia Factory",
    "author" => "Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>"
);

/**
 * permissions
 */
$wgAvailableRights[] = 'createwiki';
$wgGroupPermissions['staff']['createwiki'] = true;

require_once("SpecialPage.php");
require_once("extensions/wikia/SpecialConfigureWiki/SpecialConfigureVariables.php");

/**
 * main parts
 */
function wfSpecialConfigureWikiSetup() {

/**
 * inner class
 */
class ConfigureWikiForm extends SpecialPage {

    var $mPosted, $mAction;
    var $mTitle, $mCityId, $mCityDomain;

    /**
     * contructor
     */
    function ConfigureWikiForm() {
        global $wgLang, $wgAllowRealName, $wgRequest;
        SpecialPage::SpecialPage("ConfigureWiki");
    }

    function execute() {
        global $wgRequest, $wgUser, $wgOut, $wgTitle;

        $this->setupMessages();

        if (!in_array('createwiki', $wgUser->getRights() )) {
            $wgOut->setArticleRelated( false );
            $wgOut->setPageTitle( wfMsg('confiwikipagetitle') );
            $wgOut->setRobotpolicy( 'noindex,nofollow' );
            $wgOut->errorpage( 'nosuchspecialpage', 'nospecialpagetext' );
            return;
        }

        $this->mPosted = $wgRequest->wasPosted();
        $this->mAction = $wgRequest->getVal( 'action' );
        $this->mCityId = $wgRequest->getVal("cityid");
        $this->mCityDomain = $wgRequest->getVal("citydomain");
        $this->mTitle = Title::makeTitle( NS_SPECIAL, 'ConfigureWiki' );

        $wgOut->setPageTitle( wfMsg('confiwikipagetitle') );
        $wgOut->setRobotpolicy( 'noindex,nofollow' );
        $wgOut->setArticleRelated( false );

        $this->doWikiaSelector();

        if ( !empty( $this->mCityId) || !empty( $this->mCityDomain) ) {
            if ( !empty( $this->mCityId )) {
                $this->doWikiaVariablesList( $this->mCityId );
            }
            else {
                $this->doWikiaVariablesList( $this->mCityDomain );
            }
        }
    }

    /**
     * setup global messages cache
     * @access private
     */
    function setupMessages() {
        global $wgMessageCache;

        $wgMessageCache->addMessages(
            array(
                "confiwikipagetitle" => "Configure Wikia",
                "variablegroupid" => "Select variables section",
                "citydomain" => "Specify domain name for Wikia "
            )
        );
    }

    /**
     * list all wikias as selector
     * @access private
     */
    function doWikiaSelector(  ) {
        global $wgOut, $wlTitle, $wgRequest;
        $fname = __METHOD__;

        #--- init
        $aDomains = $aResult = array();
        $oTmpl = new WikiaTemplate( dirname( __FILE__ ) . "/templates/" );

        $oForm = new WikiaQuickForm( "cityselect", "POST", $this->mTitle->getLocalUrl( 'action=select' ) );
        $oForm->addElement( "text", "citydomain", wfMsg("citydomain"), array("value" => $this->mCityDomain) );
        $oForm->addElement( "html", "<div id=\"var-autocomplete\"></div>" );
        $oForm->addElement( "submit", "submit", 'Show Wikia parameters' );

        /**
         * get domains from api query
         */
    	$oApi = new ApiMain( new FauxRequest(array("action" => "query", "list" => "wkdomains")) );
    	$oApi->execute();
    	$aResult =& $oApi->GetResultData();

        if ( is_array( $aResult["query"]["wkdomains"] )) {
            foreach ( $aResult["query"]["wkdomains"] as $domain ) {
                $aDomains[$domain["domain"]] = $domain["id"];
            }
        }

        $wgOut->addHTML( $oTmpl->execute("header") );
        $oTmpl->set_vars( array(
            "qform" => $oForm, "domains" => $aDomains
        ));
        $wgOut->addHTML( $oTmpl->execute("wikia-selector") );
    }

    /**
     * list all wikias, mark active & inactive
     * @access private
     */
    function doWikiaVariablesList( $city ) {
        global $wgUser, $wgOut, $wgLang, $wgRequest, $wgScript;
        $fname = __METHOD__;

        $oWikiaCity = new WikiaCity( $city );
        $iCityId =  $oWikiaCity->getId();

        $wgOut->addHTML( $oWikiaCity->infoHTML() );

        /**
         * display stuffs
         */
    	$oApi = new ApiMain( new FauxRequest(array("action" => "query", "list" => "wkconfvar")) );
    	$oApi->execute();
    	$aVars =& $oApi->GetResultData();

        $aGroups = array();
        if (is_array( $aVars["query"]["wkconfvar"])) {
            foreach ($aVars["query"]["wkconfvar"] as $key => $value) {
                $aGroups[$value["group"]] = $value["group_name"];
            }
        }

        $oForm = new WikiaQuickForm( "variableselect", "post", $this->mTitle->getLocalUrl( 'action=select' ) );
        $aElems = array();

        $sUrl = "{$wgScript}?action=ajax&rs=wfwkGetConfGroupVariables&rsargs[0]={$iCityId}";
        $aElems[] =& $oForm->createElement( 'select',
            'group', null, $aGroups,
            array(
                "size" => 16,
                "id" => "group_id",
                "style" => "width: 20em;",
                "onclick" => "wikiaSelectVariable('group_id', '{$sUrl}');",
            )
        );

        $sUrl = "$wgScript?action=ajax&rs=wfwkGetConfVariable&rsargs[0]=$iCityId";
        $aElems[] =& $oForm->createElement( 'select',
            'variable', null, array('--- select group ---'),
            array(
                "size" => 16,
                "id" => "var_id",
                "style" => "width: 20em;",
                "onclick" => "wikiaSelectVariable('var_id', '{$sUrl}');"
            )
        );
        $oForm->addGroup( $aElems, null, null, ' '); #--- 'variable'
        /**
         * sent form to browser
         */
        $wgOut->addHTML( $oForm->toHTML() );

        $wgOut->addHTML( "</ul>" );
        $wgOut->addHTML( "<div id=\"wikia-variable-editor\"></div>\n" );
        $wgOut->addHTML( "</div>\n" );
    }
};

SpecialPage::addPage(new ConfigureWikiForm );
global $wgMessageCache;
$wgMessageCache->addMessage( 'configurewiki', 'Configure Wikia' );

} #--- wfSpecialConfigureWikiSetup()



?>
