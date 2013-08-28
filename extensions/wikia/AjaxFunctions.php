<?php
/*
 * Ajax Functions used by Wikia extensions
 */

Global $IP;

Require_once ( $IP . '/extensions/wikia/AjaxLogin/AjaxLogin.php' );

/**
 * Validates user names.
 *
 * @Author CorfiX (corfix@wikia.com)
 *
 * @Param String $uName
 *
 * @Return String
 */
function cxValidateUserName ($uName)
{
    Global $IP, $wgDBname, $wgSharedDB;
    Require_Once ($IP . '/includes/User.php');

    $DB =& wfGetDB (DB_SLAVE);

    $nt = Title::newFromText( $uName );
	if( is_null( $nt ) ) {
	   # Illegal name
	    return 'INVALID';
	}
    $uName = $nt->getText();

    $uName = MySQL_Real_Escape_String ($uName);

    //if (! User::isValidUserName ($uName))
    //    return 'INVALID';return wfMsg ('username-invalid');
    if ($uName == '')
	return 'INVALID';

    if ($DB->NumRows ($dbResults = $DB->Query ("SELECT User_Name FROM `wikicities`.`user` WHERE User_Name = '$uName';")))
	return 'EXISTS';#wfMsg ('username-exists');
    if ($DB->NumRows ($dbResults = $DB->Query ("SELECT User_Name FROM `user` WHERE User_Name = '$uName';")))
	return 'EXISTS';#wfMsg ('username-exists');

    return 'OK';#wfMsg ('username-valid');
}


/**
 * Return HTML forms for changing various variable params
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * response is in json format
 * (if php < 5.2 = uses class from ApiFormatJson_json.php)
 */
function wfwkGetConfVariableForms()
{
    #--- init
    global $wgRequest, $wgScript;
    $url = sprintf("%s?action=ajax&rs=wfwkPutConfVariableForms", $wgScript);

    #--- empty variable just for getting params for selectors
    $wkVariable = new WikiaConfVariable( null );

    $variable_id = $wgRequest->getVal("variable_id");
    $variable_div = $wgRequest->getText("variable_div");
    $variable_form = $wgRequest->getVal("variable_form");

    switch ($variable_form) {
        case 1:
            $variable_type = $wgRequest->getText("variable_type");
            $div = $variable_div."-select";
            $types = array();
            foreach ($wkVariable->Types() as $val) {
                $types[$val] = $val;
            }
            #--- prepare response
            $response = array();
            $response["div-name"] = $variable_div;
            $qform = new WikiaQuickForm( "group-selector", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );
            $qform->addElement( "select", "group-selector-select", "Select new type", $types)->setSelected($variable_type);
            $qform->addElement( "button" /*type*/, "button" /*name*/, "Save selection" /*value*/,
                array( "onclick" => "makeRequest('group-selector', '{$url}');")/*attributes*/
            );
            $response["div-body"] = $qform->toHTML();
            break;

        case 2:
            $variable_group = $wgRequest->getVal("variable_group");
            $div = $variable_div."-select";
            $groups = $wkVariable->Groups();

            #--- prepare response
            $response = array();
            $response["div-name"] = $variable_div;
            $qform = new WikiaQuickForm( "group-selector", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );

            $qform->addElement( "select", "group-selector-select", "Select new group", $groups)->setSelected($variable_group);
            $qform->addElement( "button" /*type*/, "button" /*name*/, "Save selection" /*value*/,
                array( "onclick" => sprintf("makeRequest('group-selector', '%s');", $url))/*attributes*/
            );
            $response["div-body"] = $qform->toHTML();
            break;

        case 3:
            $variable_level = $wgRequest->getVal("variable_level");
            $div = $variable_div."-select";
            $levels = $wkVariable->AccessLevels();

            #--- prepare response
            $response = array();
            $response["div-name"] = $variable_div;
            $qform = new WikiaQuickForm( "group-selector", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );

            $qform->addElement( "select", "group-selector-select", "Select new access level", $levels)->setSelected($variable_level);
            $qform->addElement( "button" /*type*/, "button" /*name*/, "Save selection" /*value*/,
                array( "onclick" => sprintf("makeRequest('group-selector', '%s');", $url))/*attributes*/
            );
            $response["div-body"] = $qform->toHTML();
            break;
    }

    #--- send response
    if (!function_exists('json_encode'))  {
        $json = new Services_JSON();
        return $json->encode($response);
    }
    else {
        return json_encode($response);
    }
}

/**
 * Save changes from wfwkGetConfVariableForms()
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * response is in json format
 * (if php < 5.2 = uses class from ApiFormatJson_json.php)
 */
function wfwkPutConfVariableForms() {
    #--- init
    global $wgRequest, $wgScript;
    $variable_id = $wgRequest->getVal("variable_id");
    $variable_div = $wgRequest->getText("variable_div");
    $variable_form = $wgRequest->getVal("variable_form");
    $url = "{$wgScript}?action=ajax&rs=wfwkGetConfVariableForms";

    #--- empty variable just for getting params for selectors
    $wkVariable = new WikiaConfVariable( null );

    #--- TODO check user rights

    #--- master database connection
    $dbw =& wfGetDB( DB_MASTER );
    $dbw->selectDB("wikicities");

    switch ($variable_form) {
        case 1:
            $variable_type = $wgRequest->getText("group-selector-select");
            $dbw->update("city_variables_pool",
                array( "cv_variable_type" => $variable_type ),
                array( "cv_id" => $variable_id )
            );
            $qform = new WikiaQuickForm( "wikia-variable-form1", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );
            $qform->addElement( "hidden", "variable_type", $variable_type );
            $row[] = $qform->createElement( "button", "button" /*name*/, "Change type of variable" /*value*/, array( "onclick" => "makeRequest('wikia-variable-form1', '{$url}');" ) );
            $qform->addGroup( $row, null, "Type: <strong>{$variable_type}</strong>", null );

            break;
        case 2:
            $variable_group = $wgRequest->getVal("group-selector-select");
            $groups = $wkVariable->Groups();
            $dbw->update("city_variables_pool",
                array( "cv_variable_group" => $variable_group ),
                array( "cv_id" => $variable_id )
            );
            $qform = new WikiaQuickForm( "wikia-variable-form2", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );
            $qform->addElement( "hidden", "variable_group", $variable_group );
            $row[] = $qform->createElement( "button", "button" /*name*/, "Change group for variable" /*value*/, array( "onclick" => "makeRequest('wikia-variable-form2', '{$url}');" ) );
            $qform->addGroup( $row, null, "Group: <strong>{$groups[$variable_group]}</strong>", null );
            break;

        case 3:
            $variable_level = $wgRequest->getVal("group-selector-select");
            $levels = $wkVariable->AccessLevels();
            $dbw->update("city_variables_pool",
                array( "cv_access_level" => $variable_level ),
                array( "cv_id" => $variable_id )
            );
            $qform = new WikiaQuickForm( "wikia-variable-form3", "post", "#" );
            $qform->addElement( "hidden", "variable_id", $variable_id );
            $qform->addElement( "hidden", "variable_div", $variable_div );
            $qform->addElement( "hidden", "variable_form", $variable_form );
            $qform->addElement( "hidden", "variable_level", $variable_level );
            $row[] = $qform->createElement( "button", "button" /*name*/, "Change access level for variable" /*value*/, array( "onclick" => "makeRequest('wikia-variable-form3', '{$url}');" ) );
            $qform->addGroup( $row, null, "Access level: <strong>{$levels[$variable_level]}</strong>", null );
            break;
    }

    #--- prepare response
    $response = array();
    $response["div-name"] = $variable_div;
    $response["div-body"] = $qform->toHTML();

    #--- send response
    if (!function_exists('json_encode'))  {
        $json = new Services_JSON();
        return $json->encode($response);
    }
    else {
        return json_encode($response);
    }
}

/**
 * Return configuration variables groups
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @param integer $cityid (not used currently)
 * @param integer $groupid
 *
 * @return String for <select></select>
 *
 */
function wfwkGetConfGroupVariables($cityid, $groupid)
{
    /**
     * readolny database connection but to master
     */
    $dbr =& wfGetDB( DB_MASTER );
    $dbr->selectDB("wikicities");
    $sth = $dbr->query("
        SELECT cv_id, cv_name
        FROM city_variables_pool
        WHERE cv_variable_group = $groupid
        ORDER BY cv_name
    ");
    $retval = "";
    while ( $row = $dbr->fetchObject( $sth ) ) {
        $retval .= sprintf("<option value=\"%d\">%s</option>\n", $row->cv_id, $row->cv_name);
    }
    $dbr->freeResult( $sth );
    $dbr->close();

    return $retval;
}

/**
 * Return html form for variable
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @param integer $cityid
 * @param integer $variableid
 *
 * @return HTML form for editing data
 *
 */
function wfwkGetConfVariable($cityid, $variableid)
{
    $fname = "wfwkGetConfVariable";

    $dbr =& wfGetDB( DB_MASTER );
    $dbr->selectDB("wikicities");
    if ($cityid == 0) {
        /**
         * take defaults from city_variables_pool
         */
        $sth = $dbr->query("
            SELECT *
            FROM city_variables_pool
            WHERE cv_id = $variableid
            LIMIT 1", $fname
        );
        $row = $dbr->fetchObject( $sth );
        $row->cv_city_id = 0;
        $row->cv_is_default = 1; #--- we editing default value for pool
    } else {
        /**
         * take defaults from city_variables for particular city
         */
        $sth = $dbr->query("
            SELECT *
            FROM city_variables_pool
            LEFT JOIN city_variables ON cv_id = cv_variable_id AND cv_city_id = $cityid
            WHERE cv_id = $variableid
            LIMIT 1
            ", $fname
        );
        $row = $dbr->fetchObject( $sth );
        $row->cv_is_default = 0; #--- we editing current value for city
    }

    $dbr->freeResult( $sth );
    $dbr->close();

    $wkConfVariable = new WikiaConfVariable( $row );

	return $wkConfVariable->display( 1 /*withform*/ );
}

/**
 * Save variable from form
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @param integer $cityid
 *
 * @return status
 *
 */
function wfwkPutConfVariable($cityid)
{
    global $wgRequest;
    $fname = "wfwkPutConfVariable";

    $var_id     = $wgRequest->getVal( 'var_id' );
    $var_name   = $wgRequest->getVal( 'var_name' );
    $cityid     = $wgRequest->getVal( 'cityid' );
    $var_is_default = $wgRequest->getVal( 'var_is_default' );

    /**
     * master database connection
     */
    $dbw =& wfGetDB( DB_MASTER );
    $dbw->selectDB("wikicities");
    $retval = "";

    switch ( $wgRequest->getVal( 'var_type' ) ) {
        case "boolean":
        case "string":
        case "integer":
            $var_value  = $wgRequest->getVal( 'var_value' );
            try {
                if ( empty($cityid) ) {
                    /**
                     * update default value in pool
                     */
                    $dbw->update("city_variables_pool",
                        array( "cv_default_value" => serialize( $var_value ) ),
                        array( "cv_id" => $var_id )
                    );
                }
                else {
                    /**
                     * update value in city
                     */
                    $dbw->update("city_variables",
                        array( "cv_value" => serialize( $var_value ) ),
                        array( "cv_variable_id" => $var_id, "cv_city_id" => $cityid )
                    );
                }
                $retval .= "<div class=\"successbox\">Value of <strong>{$var_name}</strong> changed to <strong>{$var_value}</strong>{$cityid}.</div>\n";
                $retval .= "<br style=\"clear:both;\" />";
            } catch ( DBQueryError $e ) {
                $retval .= "<div class=\"successbox\">{$e->getText()}</div>\n";
                $retval .= "<br style=\"clear:both;\" />";
            }
            break;
        case "array":
                /**
                 * data is stored in var_value and it should be array
                 */
#                $var_values  = $wgRequest->getArray( 'var_value' );
#                $dbw->update("city_variables",
#                    array( "cv_value" => serialize( $var_values ) ),
#                    array( 'cv_variable_id' => $var_id,  'cv_city_id' => $cityid )
#                );
        break;
        default:
            print_r($_REQUEST);
    }
    return $retval;
}

/**
 * Change city state from enabled to disabled or vice versa
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @param integer $cityid, $state
 *
 * response is in json format
 * (if php < 5.2 = uses class from ApiFormatJson_json.php)
 *
 */
function wfwkToggleCityState()
{
    global $wgUser, $wgRequest;

    $iError = 0;
    $sRetval = "";

    if ( !in_array('createwiki', $wgUser->getRights() )) {
        $iError++;
        $sRetval = "You are not allowed to change wikia status";
    }
    $iCityid = $wgRequest->getVal( "cityid" );
    $iState = $wgRequest->getVal( "state" );

    if (empty($iCityid)) {
        $iError++;
        $sRetval = "Error, city id is empty";
    }

    if ( !empty($iError) ) {
        $sRetval .= "Error, city id is empty";
    }
    else {
        $dbw =& wfGetDB( DB_MASTER );
        $dbw->selectDB("wikicities");

        if ( $state == 0 ) { #--- disabling
            $dbw->update("city_list", array("city_public" => 0), array( "city_id" => $iCityid ));
            $sRetval .= "This wikia is <strong>disabled</strong>"
                . "<input type=\"button\" onclick=\"ToggleWikia(0)\" value=\"Enable this wikia\" />";
        }
        if ( $state == 1 ) { #--- enabling
            $dbw->update("city_list", array("city_public" => 1), array( "city_id" => $iCityid ));
            $sRetval .= "This wikia is <strong>enabled</strong>"
                . "<input type=\"button\" onclick=\"ToggleWikia(1)\" value=\"Disable this wikia\" />";
        }

        $memc =& wfGetCache(CACHE_MEMCACHED);
        $memc->delete('wikiacitydomains');
        $memc->delete("wikiacityvariables:$iCityid");
    }
    #--- prepare response
    $aResponse = array();
    $aResponse["div-name"] = "wk-wikia-toggle";
    $aResponse["div-body"] = $sRetval;

    #--- send response
    if (!function_exists('json_encode'))  {
        $oJson = new Services_JSON();
        return $oJson->encode( $aResponse );
    }
    else {
        return json_encode( $aResponse );
    }
}

?>