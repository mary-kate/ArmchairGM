<?php

/**
 * @package MediaWiki
 * @subpackage SpecialPage
 * @author Krzysztof Krzyżaniak <eloy@wikia.com> for Wikia.com
 * @version: 0.1
 */

if ( !defined( 'MEDIAWIKI' ) ) {
        echo "This is MediaWiki extension named ActivateWiki.\n";
        exit( 1 ) ;
}

$wgExtensionFunctions[] = 'wfSpecialConfigureWikiSetup';

$wgExtensionCredits['specialpage'][] = array(
    "name" => "ConfigureWiki",
    "description" => "Configure wikia in Wikia Factory",
    "author" => "Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>"
);

/**
 * permissions
 */
$wgAvailableRights[] = 'createwiki';
$wgGroupPermissions['staff']['createwiki'] = true;

require_once("SpecialPage.php");
require_once("HTMLForm.php");
require_once("htmlform/WikiaHTMLForm.php");

/**
 * main parts
 */
function wfSpecialConfigureWikiSetup() {

/**
 * inner class
 */
class ConfigureWikiForm extends SpecialPage {

    var $mName, $mPassword, $mRetype, $mReturnto, $mCookieCheck, $mPosted;
    var $mAction, $mCreateaccount, $mCreateaccountMail, $mMailmypassword;
    var $mLoginattempt, $mRemember, $mEmail, $mImportStarter;
    var $mTitle, $mCityId;

    /**
     * contructor
     */
    function ConfigureWikiForm() {
        global $wgLang, $wgAllowRealName, $wgRequest;
        SpecialPage::SpecialPage("ConfigureWiki");
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
        $this->mTitle = Title::makeTitle( NS_SPECIAL, 'ConfigureWiki' );

        if (!empty( $this->mPosted )) {
            $this->mCityId =  $wgRequest->getVal("cityid");
        }
        else {
            $this->mCityId =  null;
        }


        $this->setupMessages();

        $wgOut->setPageTitle( wfMsg('activatewikipagetitle') );
        $wgOut->setRobotpolicy( 'noindex,nofollow' );
        $wgOut->setArticleRelated( false );

        $this->doWikiaSelector(  );
        if ( !empty( $this->mCityId )) {
            $this->doWikiaVariablesList( $this->mCityId );
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
                "activatewikipagetitle" => "Configure Wikia",
                "cityselect" => "Select Wikia",
                $this->mName.'-'."citydomain" => " or specify domain name for Wikia "
            )
        );
    }

    /**
     * list all wikias, mark active & inactive
     * @access private
     */
    function doWikiaList( $action ) {
        global $wgUser, $wgOut, $wgLang, $wgRequest, $wikiaCityVariables;
        $fname = "ActivateWikiForm::doWikiaList";
        $wikiaCityDomains = array();
        $wikiaCityParams = array();
        $wikiaMemCached =& wfGetCache(CACHE_MEMCACHED);
        $action =  $wgRequest->getVal("submit");
        $cityid =  $wgRequest->getVal("city_id");

        if ( $action == "Disable" ) {
            /**
             * disable city in database
             */
            $dbw =& wfGetDB( DB_MASTER );
            $dbw->selectDB("wikicities");
            $dbw->update("city_list", array("city_public" => 0), array( "city_id" => $cityid));
            /**
             * ... and clean memcached
             */
            $wikiaMemCached->delete('wikiaCityDomains');
            $wikiaMemCached->delete('wikiaCityVariables');
            $wgOut->addHTML("
                <div class=\"successbox\">Successfully disabled.</div>
                <br style=\"clear:both;\" />
            ");
        }
        elseif ( $action == "Enable" ) {
            /**
             * enable city in database
             */
            $dbw =& wfGetDB( DB_MASTER );
            $dbw->selectDB("wikicities");
            $dbw->update("city_list", array("city_public" => 1), array( "city_id" => $cityid));
            /**
             * ... and clean memcached
             */
            $wikiaMemCached->delete('wikiaCityDomains');
            $wikiaMemCached->delete('wikiaCityVariables');
            $wgOut->addHTML("
                <div class=\"successbox\">Successfully enabled.</div>
                <br style=\"clear:both;\" />
            ");
        }

        $dbr =& wfGetDB( DB_SLAVE );
        $dbr->selectDB("wikicities");
        $sth = $dbr->query("
            SELECT city_list.city_id, city_domains.city_domain, city_list.city_public,
                   city_list.city_sitename
            FROM wikicities.city_domains, wikicities.city_list
            WHERE city_domains.city_id = city_list.city_id
            AND city_list.city_sitename <> 'wikia'
        ");
        while ( $row = $dbr->fetchObject( $sth )) {
            $wikiaCityDomains[$row->city_id][] = $row->city_domain;
            $wikiaCityParams[$row->city_id] = $row;
        }
        $dbr->freeResult( $sth );


        $wgOut->addHTML( "<ul>" );

        foreach ( $wikiaCityParams as $key => $value ) {
            if ($wikiaCityParams[ $key ]->city_public == 1) {
                $info = "is active";
                $buttons = array("disable" => "", "enable" => "disabled=\"disabled\"");
            }
            else {
                $info = "is not active";
                $buttons = array("enable" => "", "disable" => "disabled=\"disabled\"");
            }
            if ( $wikiaCityParams[ $key ]->city_sitename != 'notreal' ) {
            $wgOut->addHTML( "<form name=\"activatewiki\" id=\"activatewiki\" method=\"post\" action=\"\">" );
            $wgOut->addHTML( "
                <li>
                 <label>{$value->city_sitename}:{$this->__unsvar($key, "wgSitename")} {$info} </label>
                 <input type=\"hidden\" id=\"city_id\" name=\"city_id\" value=\"{$key}\" />
                 <input type=\"submit\" name=\"submit\" value=\"Disable\" {$buttons["disable"]} />
                 <input type=\"submit\" name=\"submit\" value=\"Enable\" {$buttons["enable"]} />
                </li>
            ");
            $wgOut->addHTML( "</form>" );
            }
        }
        $wgOut->addHTML( "</ul>" );
    }

    /**
     * list all wikias as selector
     * @access private
     */
    function doWikiaSelector(  ) {
        global $wgOut, $wlTitle, $wgRequest;

        $html = new HTMLForm( $wgRequest );

        $dbr =& wfGetDB( DB_SLAVE );
        $dbr->selectDB("wikicities");
        $sth = $dbr->query("
            SELECT  city_list.city_id, city_list.city_public, city_list.city_sitename
            FROM    city_list
            WHERE   city_list.city_sitename <> 'wikicities'
            ORDER BY city_list.city_sitename
        ");
        $wgOut->addHTML(  wfElement( 'form', array( 'method' => 'post', 'action' => $this->mTitle->getLocalUrl( 'action=select' ) ), NULL ) );
        $wgOut->addHTML( wfLabel( wfMsg("cityselect"), "cityselect" ));
        $wgOut->addHTML( wfOpenElement("select", array( "name" => "cityid" )) );
        $wgOut->addHTML( wfElement( 'option', array( 'value' => 0 ), '-- select Wikia --' ));
        while ( $row = $dbr->fetchObject( $sth )) {
            $wgOut->addHTML( wfElement( 'option', array( 'value' => $row->city_id ), $row->city_sitename ));
        }
        $dbr->freeResult( $sth );
        $wgOut->addHTML( wfCloseElement("select") );
        $wgOut->addHTML( $this->mName );
        $wgOut->addHTML( $html->textbox("citydomain") );
        $wgOut->addHTML( wfSubmitButton( 'Show Wikia parameters' ));
        $wgOut->addHTML( wfCloseElement("form") );
    }

    /**
     * list all wikias, mark active & inactive
     * @access private
     */
    function doWikiaVariablesList( $cityid ) {
        global $wgUser, $wgOut, $wgLang, $wgRequest, $wikiaCityVariables;
        global $wikiaCityParams;
        $fname = "ActivateWikiForm::doWikiaVariablesList";
        $wikiaCityVariables = array();
        $whtml = new WikiaHTMLForm( $wgRequest );

        /**
         * slave database connection
         */
        $dbr =& wfGetDB( DB_SLAVE );
        $dbr->selectDB("wikicities");

        $sth = $dbr->query("SELECT * FROM city_variables, city_variables_pool WHERE cv_id = cv_variable_id AND cv_city_id = $cityid");
        while ( $row = $dbr->fetchObject( $sth ) ) {
            $wikiaCityVariables[ $row->cv_name ] = $row; // [ $key ]
        }
        $dbr->freeResult( $sth );

        $wgOut->addHTML( "<ol>" );
        foreach( $wikiaCityVariables as $variable => $value ) {
            $wgOut->addHTML( $this->__display( $value ) );
        }
        $wgOut->addHTML( "</ol>" );
    }

    /**
     * unserialize variable
     * @access private
     */
    function __unsvar( $cityid, $var ) {
        global $wikiaCityVariables;
        return unserialize($wikiaCityVariables[ $cityid ][ $var ]->cv_value);
    }

    /**
     * display properly formated variable value
     * @access private
     */
    function __display( $data ) {
        $retval = "
        <style type=\"text/css\">
        .variable {border: 1px solid lightgray;margin: 0.2em;}
        .variable .value {font-weight: bold; border: 1px solid blue; background: lightblue; padding: 0.2em; }
        </style>
        <!-- s: variable display -->
        <div class=\"variable\">
         <div class=\"header\">
          name: <strong>{$data->cv_name}</strong> type: <strong>{$data->cv_variable_type}</strong>
         </div>
         <div>
          <em>{$data->cv_description}</em>
         </div>
         <div>
          Current value:
        ";
        switch( $data->cv_variable_type ) {
            case 'boolean':
                $val = unserialize( $data->cv_value );
                $val = empty( $val ) ? 'false' : 'true';
                $retval .= "<span class=\"value\">".$val."</span>";
                break;
            case 'integer':
                $retval .= "<span class=\"value\">".unserialize( $data->cv_value )."</span>";
                break;
            case 'array':
                $value_arr = unserialize($data->cv_value);
                if (is_array( $value_arr )) {
                    $retval .=  "<ul>";
                    foreach ( $value_arr as $keya => $valuea) {
                        $retval .= sprintf("<li>%s => %s</li>", $keya, print_r( $valuea, true));
                    }
                    $retval .=  "</ul>";
                }
                else {
                    $retval .=  "empty";
                }
                break;
            case 'string':
                $retval .= "<span class=\"value\">".htmlspecialchars(unserialize($data->cv_value))."</span>";
                break;
        }

        $retval .= "
         </div>
        </div>
        <!-- e: variable display -->
        ";

        return $retval;
    }
};

SpecialPage::addPage(new ConfigureWikiForm );
global $wgMessageCache;
$wgMessageCache->addMessage( 'configurewiki', 'Configure Wikia' );

} /** wfSpecialActivateWikiSetup() **/
?>