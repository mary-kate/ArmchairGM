<?php

/**
 * WikiaApiQueryDomains - ask for id <> domains array for wikia
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @todo use access for giving variables values only with proper access rights
 *
 * $Id: WikiaApiQueryDomains.php 6286 2007-10-17 10:51:58Z ppiotr $
 */
class WikiaApiQueryDomains extends ApiQueryBase {

    /**
     * constructor
     */
	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName, "wk");
	}

    /**
     * main function
     */
	public function execute() {
        $wikia = null;

		extract($this->extractRequestParams());

        #--- database instance
		$db =& $this->getDB();
        $db->selectDB( 'wikicities' );

		#--- query builder
		list( $tbl_cd ) = $db->tableNamesN( "city_domains" );
        $this->addTables( $tbl_cd );
		$this->addFields( array( "city_id", "city_domain" ));
        if (!is_null( $wikia )) {
                $this->addWhereFld( "city_id", $wikia );
		}
        $this->addOption( "ORDER BY ", "city_id" );

		#--- result builder
		$data = array();
        $res = $this->select(__METHOD__);
        while ($row = $db->fetchObject($res)) {
            $data[$row->city_id] = array(
                "id"		=> $row->city_id,
                "domain"	=> $row->city_domain,
            );
            ApiResult :: setContent( $data[$row->city_id], $row->city_domain );
        }
		$db->freeResult($res);

		$this->getResult()->setIndexedTagName($data, 'variable');
		$this->getResult()->addValue('query', $this->getModuleName(), $data);
    }

	public function getVersion() {
		return __CLASS__ . ': $Id: WikiaApiQueryDomains.php 6286 2007-10-17 10:51:58Z ppiotr $';
	}

	public function getDescription() {
		return 'Get domains handled by Wikia';
	}

	public function getAllowedParams() {
		return array (
            "wikia" => array (
				ApiBase :: PARAM_TYPE => 'integer'
			)
        );
    }

	public function getParamDescription() {
		return array (
			'wikia' => 'Identifier in Wikia Factory',
		);
	}

	public function getExamples() {
		return array (
			'api.php?action=query&list=wkdomains',
			'api.php?action=query&list=wkdomains&wkwikia=1588'
		);
	}
};
?>
