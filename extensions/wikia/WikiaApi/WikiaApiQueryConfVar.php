<?php

/**
 * WikiaApiQueryConfVar - ask for configuration variables
 *
 * @author Krzysztof Krzyżaniak (eloy) <eloy@wikia.com>
 *
 * @todo use access for giving variables values only with proper access rights
 *
 * $Id: WikiaApiQueryConfVar.php 2339 2007-05-17 22:34:37Z emil $
 */
class WikiaApiQueryConfVar extends ApiQueryBase {

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

        #--- blank variables
        $wikia = $group = $variable = null;

		extract($this->extractRequestParams());

        #--- database instance
		$db =& $this->getDB();
        $db->selectDB( 'wikicities' );

	list( $tbl_cvg, $tbl_cvp, $tbl_cv ) = $db->tableNamesN( "city_variables_groups", "city_variables_pool", "city_variables" );
        if (!is_null( $wikia )) {
            $this->addTables("$tbl_cvp JOIN $tbl_cvg ON cv_variable_group = cv_group_id JOIN $tbl_cv ON cv_id = cv_variable_id");
            $this->addFields( array(
                "cv_id",
                "cv_name",
                "cv_description",
                "cv_variable_type",
                "cv_variable_group",
                "cv_access_level",
                "cv_default_value",
                "cv_city_id",
                "cv_value",
                "cv_group_name"
            ));
            $this->addWhereFld( "cv_city_id", $wikia );
            if (!is_null( $wikia )) {
                $this->addWhereFld( "cv_id", $variable );
            }
        }
        else {
            $this->addTables("$tbl_cvp JOIN $tbl_cvg ON cv_variable_group = cv_group_id");
            $this->addFields( array(
                "cv_id",
                "cv_name",
                "cv_description",
                "cv_variable_type",
                "cv_variable_group",
                "cv_access_level",
                "cv_default_value",
                "cv_group_name"
            ));
        }
        if (!is_null( $group )) {
            $this->addWhereFld( "cv_group_id", $group );
        }
        /**
         * so far only editable variables
         */
        $this->addWhere( "cv_access_level > 1" );
        $this->addOption( "ORDER BY ", "cv_name" );

		$data = array();

        $res = $this->select(__METHOD__);
        while ($row = $db->fetchObject($res)) {
            $cv_value = is_null($row->cv_value) ? $row->cv_default_value : $row->cv_value;
            $data[$row->cv_id] = array(
                "id"            => $row->cv_id,
                "name"          => $row->cv_name,
                "description"   => $row->cv_description,
                "type"          => $row->cv_variable_type,
                "group"         => $row->cv_variable_group,
                "group_name"    => $row->cv_group_name,
                "access_level"  => $row->cv_access_level,
                "default_value" => $row->cv_default_value,
                "value"         => $cv_value
            );
            ApiResult :: setContent( $data[$row->cv_id], $row->cv_name );
        }
		$db->freeResult($res);
		$this->getResult()->setIndexedTagName($data, 'item');
		$this->getResult()->addValue('query', $this->getModuleName(), $data);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: WikiaApiQueryConfVar.php 2339 2007-05-17 22:34:37Z emil $';
	}

	protected function getDescription() {
		return 'Get Wiki configuration variables.';
	}

	protected function getAllowedParams() {
		return array (
            "wikia" => array (
				ApiBase :: PARAM_TYPE => 'integer'
			),
            "group" => array (
				ApiBase :: PARAM_TYPE => 'integer'
			),
            "variable" => array (
				ApiBase :: PARAM_TYPE => 'integer'
			)
        );
    }

	protected function getParamDescription() {
		return array (
			'wikia' => 'Identifier in Wikia Factory',
			'group' => 'Get only variables for group',
			'variable' => 'Get only variable value for this id. It require wkwikia to be set'
		);
	}

	protected function getExamples() {
		return array (
			'api.php?action=query&list=wkconfvar',
			'api.php?action=query&list=wkconfvar&wkwikia=1588',
			'api.php?action=query&list=wkconfvar&wkwikia=1588&wkgroup=2',
		);
	}

};

?>
