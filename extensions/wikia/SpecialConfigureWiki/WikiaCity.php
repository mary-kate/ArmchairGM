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
    echo "This is MediaWiki extension named WikiaCity.\n";
    exit( 1 ) ;
}

/**
 *
 */
class WikiaCity {

    var $mId, $mConfVariables, $mDomains, $mParams;

    /**
     * constructor, takes database row as argument
     *
     * city could be int like city_id
     * or string like city_domain
     */
    public function __construct( $city, $usecache = 0 ) {

        if ( is_int($city) ) {
            $this->mId = $city;
        }
        else {
            $this->mId = $this->pDomainToId( $city );
        }

        $this->mTitle = Title::makeTitle( NS_SPECIAL, 'WikiaCity' );
        $this->mConfVariables = array();
        $this->mDomains = array();
        $this->mParams = null;

        if ( $usecache == 0 ) {
            $this->mConfVariables = $this->pGetConfFromDB();
            $this->mDomains = $this->pGetDomainsFromDB();
            $this->mParams = $this->pGetParamsFromDB();
        }
        else {
            $this->mConfVariables = $this->pGetConfFromCache();
        }
    }

    /**
     * public methods
     */
    public function getConfVariables()
    {
        return $this->mConfVariables;
    }

    public function getDomains()
    {
        return $this->mDomains;
    }

    public function getParams()
    {
        return $this->mParams;
    }

    public function getId()
    {
        return $this->mId;
    }

    /**
     * return HTML summary of city (domains + params)
     * @access public
     */
    public function infoHTML() {
        global $wgScript;

        $tmpl = new WikiaTemplate( dirname( __FILE__ ) . "/templates/" );
        $tmpl->set_vars( array(
            "city" => $this,
            "ajaxurl" => "{$wgScript}?action=ajax&rs=wfwkToggleCityState"

        ));
        return $tmpl->execute("wikia-info");
    }

    /**
     * private methods
     */
    private function pGetDomainsFromDB() {

        $dbr =& wfGetDB( DB_MASTER );
        $dbr->selectDB("wikicities");

        $tmp = array();
        $sth = $dbr->select( "city_domains",
            array("*"),
            array("city_id" => $this->mId),
            __METHOD__ );
        while ( $row = $dbr->fetchObject( $sth ) ) {
            $tmp[] = $row->city_domain;
        }
        $dbr->freeResult( $sth );
        return $tmp;
    }

    private function pGetParamsFromDB() {

        $dbr =& wfGetDB( DB_MASTER );
        $dbr->selectDB("wikicities");

        $sth = $dbr->select( "city_list",
            array("*"),
            array("city_id" => $this->mId),
            __METHOD__ );
        $row = $dbr->fetchObject( $sth );
        $dbr->freeResult( $sth );
        return $row;
    }

    private function pGetConfFromDB() {
        $fname = __METHOD__;

        $dbr =& wfGetDB( DB_MASTER );
        $dbr->selectDB("wikicities");

        $tmp = array();
        $sql = "SELECT * FROM city_variables_pool LEFT JOIN city_variables ON ( cv_id = cv_variable_id AND cv_city_id = {$this->mId} )";
        $sth = $dbr->query($sql, $fname);
        while ($row = $dbr->fetchObject( $sth )) {
            $row->cv_is_default = 0; #--- we editing current value for city
            $tmp[] = new WikiaConfVariable( $row );
        }
        $dbr->freeResult( $sth );
        $dbr->close();
        return $tmp;
    }

    private function pGetConfFromCache() {
        return $this->mConfVariables;
    }

    /**
     * get domain name, retun $cityid
     */
    private function pDomainToId( $domain ) {

        $dbr =& wfGetDB( DB_MASTER );
        $dbr->selectDB("wikicities");

        $sth = $dbr->select( "city_domains",
            "city_id, city_domain",
            array("city_domain" => $domain),
            array("limit" => 1) );
        $row = $dbr->fetchObject( $sth );
        $dbr->freeResult( $sth );
        return $row->city_id;
    }
};

class WikiaCityTemplate extends QuickTemplate {

};
?>