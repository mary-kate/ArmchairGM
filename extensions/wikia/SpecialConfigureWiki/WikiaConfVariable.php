<?php

/**
 * @package MediaWiki
 * @subpackage WikiaConfigure
 * @author Krzysztof Krzyżaniak <eloy@wikia.com> for Wikia.com
 * @version: 0.1
 *
 * container for configuration variable
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This is MediaWiki extension named WikiaConfVariable.\n";
    exit( 1 ) ;
}

/**
 *
 */
class WikiaConfVariable {

    var $mVariable, $mCityId;
    var $mTypes = array (
        "integer",  "long",     "string",   "float",    "array",
        "boolean",  "text",     "struct",   "hash"
    );
    var $mAccessLevels = array (
        1 => "read only (global)", 2 => "staff", 3 => "user"
    );
    var $mGroups = array();

    /**
     * constructor, takes database row as argument
     */
    public function __construct( $row ) {
        $this->mVariable = $row;

        if ($row != null) {
            $this->mCityId = $row->cv_city_id;
        }
        else {
            $this->mCityId = 0;
        }
    }

    /**
     * some getters, some lazy one
     */
    public function AccessLevels() {
        return $this->mAccessLevels;
    }

    /**
     * for example this one is lazy
     */
    public function Groups() {
        if (sizeof($this->mGroups == 0)) {
            #--- fill array
        	$api = new ApiMain( new FauxRequest(array("action" => "query", "list" => "wkconfgroups")) );
        	$api->execute();
        	$tmparr =& $api->GetResultData();

            if (is_array($tmparr["query"]["wkconfgroups"])) {
                foreach ($tmparr["query"]["wkconfgroups"] as $key => $value) {
                    $this->mGroups[$value["id"]] = $value["name"];
                }
            }
        }
        return $this->mGroups;
    }

    public function Types() {
        return $this->mTypes;
    }

    /**
     * display HTML code with variable data
     */
    public function display( $withform = 0 ) {
        global $wgScript;
        $retval = "";

        $tmpl = new WikiaTemplate( dirname( __FILE__ ) . "/templates/" );

        if ( $this->mVariable->cv_is_default == 1 ) {
            $tmpl->set_vars( array(
                "variable" => $this->mVariable,
                "groups" => $this->Groups(),
                "accesslevels" => $this->mAccessLevels,
                "baseurl" => "{$wgScript}?action=ajax&rs=wfwkGetConfVariableForms"
            ));
            $retval .= $tmpl->execute("variable-editor");
        }
        if ( $withform == 1 ) {
            $retval .= $this->form();
        }
        return $retval;
    }


    /**
     * display HTML form for editing data
     */
    public function form( ) {
        $retval = dumpvar( $this->mVariable, 1 );
        switch( $this->mVariable->cv_variable_type ) {

            case 'boolean':
                $retval .= $this->booleanToHTMLform();
                break;

            case 'string':
            case 'integer':
                $retval .= $this->scalarToHTMLform();
                break;

            case 'array':
                $retval .= $this->arrayToHTMLform();
                break;
            default:
                $retval .= "Editing this type of variable is not supported yet. Sorry.";
        }

        return $retval;
    }

    /**
     * booleanToHTMLform - takes boolean and shows input fields for editing
     * @access private
     *
     */
    private function booleanToHTMLform() {
        global $wgScript;
        $cityid = $this->mCityId;

        if ($this->mVariable->cv_is_default == 1) {
            $value = unserialize( $this->mVariable->cv_default_value );
        }
        else {
            $value = unserialize( $this->mVariable->cv_value );
        }
        $val = empty( $val ) ? 0 : 1;

        $url = "{$wgScript}?action=ajax&rs=wfwkPutConfVariable&rsargs[0]={$cityid}";
        $qform = new WikiaQuickForm(
            "wikia-variable-form",  /* form name        */
            "post",                 /* form method      */
            $url,                   /* form action      */
            ''                      /* form target      */
        );
        $qform->addElement( "hidden", "xcityid", $cityid ) ;
        $qform->addElement( "hidden", "var_type", "boolean" ) ;
        $qform->addElement( "hidden", "var_is_default", $this->mVariable->cv_is_default);
        $qform->addElement( "hidden", "var_name", $this->mVariable->cv_name );
        $qform->addElement( "hidden", "var_id", $this->mVariable->cv_id );
        $qform->addElement( "header", "header", sprintf( "Current value of %s", $this->mVariable->cv_name, $this->mVariable->cv_id ));

        $radio = array();
        $select = $qform->addElement("select", "var_value", "Select new value", array( 1 => "True", 0 => "False"));
        $select->setSelected( $value );
        $qform->addElement(
            "button",               /* field type       */
            "button",               /* field name       */
            "Save changes",         /* field value      */
            array(                  /* field attributes */
                "onclick" => sprintf("wikiaSaveVariable('var_id', '%s');", $url)
            )
        );
        return $qform->toHTML();
    }

    /**
     * scalarToHTMLform - takes string and shows input fields for editing
     * @access private
     */
    private function scalarToHTMLform() {
        global $wgScript;
        $cityid = $this->mCityId;

        if ($this->mVariable->cv_is_default == 1) {
            $value = unserialize( $this->mVariable->cv_default_value );
        }
        else {
            $value = unserialize( $this->mVariable->cv_value );
        }

        $url = "{$wgScript}?action=ajax&rs=wfwkPutConfVariable&rsargs[0]={$cityid}";
        $qform = new WikiaQuickForm(
            "wikia-variable-form",  /* form name        */
            "post",                 /* form method      */
            $url,                   /* form action      */
            ''                      /* form target      */
        );
        $qform->addElement( "hidden", "xcityid", $cityid ) ;
        $qform->addElement( "hidden", "var_type", $this->mVariable->cv_variable_type ) ;
        $qform->addElement( "hidden", "var_is_default", $this->mVariable->cv_is_default);
        $qform->addElement( "hidden", "var_name", $this->mVariable->cv_name );
        $qform->addElement( "hidden", "var_id", $this->mVariable->cv_id );
        $qform->addElement( "header", "header", sprintf( "Current value of %s", $var_name) );
        $qform->addElement( "text", "var_value", null, array( "value" => $value, "size" => "32") );
        $qform->addElement(
            "button",               /* field type       */
            "button",               /* field name       */
            "Save changes",         /* field value      */
            array(                  /* field attributes */
                "onclick" => sprintf("wikiaSaveVariable('var_id', '%s');", $url)
            )
        );
        return $qform->toHTML();
    }

    /**
     * arrayToHTML - arrayToHTML takes array as param and return HTML form
     * @access private
     *
     */
    function arrayToHTMLform() {
        global $wgScript;

        $cityid = $this->mCityId;
        if ($this->mVariable->cv_is_default == 1) {
            $values = unserialize( $this->mVariable->cv_default_value );
        }
        else {
            $values = unserialize( $this->mVariable->cv_value );
        }

        $url = "{$wgScript}?action=ajax&rs=wfwkPutConfVariable&rsargs[0]={$cityid}";
        $qform = new WikiaQuickForm(
            "wikia-variable-form",  /* form name        */
            "post",                 /* form method      */
            $url,                   /* form action      */
            ''                      /* form target      */
        );
        $qform->addElement( "hidden", "xcityid", $cityid ) ;
        $qform->addElement( "hidden", "var_type", "array" ) ;
        $qform->addElement( "hidden", "var_is_default", $this->mVariable->cv_is_default);
        $qform->addElement( "hidden", "var_name", $this->mVariable->cv_name );
        $qform->addElement( "hidden", "var_id", $this->mVariable->cv_id );
        $qform->addElement( "header", "header", sprintf( "Current value of %s", $var_name) );

        if ( is_array( $values )) {
            foreach ( $values as $key => $val ) {
                $row = array();
                $row[] =& $qform->createElement(
                    'text',
                    sprintf("var_value[%s]", $key),
                    null,
                    array(          /* field attributes */
                        "value" => addslashes($val),
                        "size" => "32"
                    )
                );
                $row[] =& $qform->createElement( "link", null , $key, "#", "Remove row" );
                $qform->addGroup($row, null, $key, null );
            }
        }
        $qform->addElement( "link", "var_row_add" /*name/id*/, null /*label*/, "#" /*href*/, "add row", array() /*attributes*/);
        $qform->addElement(
            "button",               /* field type       */
            "button",               /* field name       */
            "Save changes",         /* field value      */
            array(                  /* field attributes */
                "onclick" => sprintf("wikiaSaveVariable('var_id', '%s');", $url)
            )
        );
        return $qform->toHTML();
    }
};

?>