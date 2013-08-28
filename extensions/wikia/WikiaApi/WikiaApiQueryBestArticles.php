<?php

/**
 * WikiaApiQueryBestArticles - get list of most popular accessed pages from MW page and from statistics if needed
 *
 * @author Piotr Molski (moli) <moli@wikia.com>
 *
 * @todo
 *
 */

class WikiaApiQueryBestArticles extends WikiaApiQuery {
    /**
     * constructor
     */
	var $user = null;
	var $ctime = null;
	var $limit = null;
	var $offset = null;
	var $pagename = null;
	#---
	var $date = null;

	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName);

        #--- initial parameters (dbname, limit, offset ...)
		$initParams = $this->getInitialParams();
		foreach ($initParams as $paramName => $paramValue)
		{
			$this->$paramName = $paramValue;
		}

        #--- request parameters ()
		$reqParams = $this->extractRequestParams();
		foreach ($reqParams as $paramName => $paramValue)
		{
			$this->$paramName = $paramValue;
		}		
	}

    /**
     * main function
     */
	public function execute() {
		global $wgUser;

		switch ($this->getActionName()) {
			case parent::INSERT : /* to do - is it needed? */ break;
			case parent::UPDATE : /* to do - is it needed? */ break;
			case parent::DELETE : /* to do - is it needed? */ break;
			default: // query
			{
				$this->getBestArticles();
				break;
			}
		}
	}

	#---
	private function getBestArticles() {
		global $wgDBname;
		#---
		$this->initCacheKey(&$lcache_key, __METHOD__);
		#---
		try {
			#--- database instance
			if ( is_null($this->pagename) ) {
				throw new WikiaApiQueryError(1);
			}

			$this->setCacheKey (&$lcache_key, 'P', $this->pagename);

			#---
			if ( !empty($this->ctime) ) {
				if ( !$this->isInt($this->ctime) ) {
					throw new WikiaApiQueryError(1);
				}
			}

			#--- limit
			if ( !empty($this->limit)  ) { //WikiaApiQuery::DEF_LIMIT
				if ( !$this->isInt($this->limit) ) {
					throw new WikiaApiQueryError(1);
				}
				$this->setCacheKey (&$lcache_key, 'L', $this->limit);
			}

			#--- offset
			if ( !empty($this->offset)  ) { //WikiaApiQuery::DEF_LIMIT_COUNT
				if ( !$this->isInt($this->offset) ) {
					throw new WikiaApiQueryError(1);
				}
				$this->setCacheKey (&$lcache_key, 'LO', $this->limit);
			}

			$data = array();
			// check data from cache ...
			$cached = $this->getDataFromCache($lcache_key);
			if (!is_array($cached))
			{
				#check to take data from article
				$templateTitle = Title::newFromText ($this->pagename, NS_MEDIAWIKI);
				if( $templateTitle->exists() )
				{
					$templateArticle = new Article ($templateTitle);
					$templateContent = $templateArticle->getContent();
					$lines = explode( "\n\n", $templateContent );
					foreach( $lines as $line )
					{
						$title = Title::NewFromText( $line );

						if( is_object( $title) )
						{
							#---
							$article['id'] = $title->getArticleID();
							$article['title'] = $title->getPrefixedText();
							$article['namespace'] = $title->getNamespace();

							$results[] = $article;
						}
					}
					#---
					if (!empty($results))
					{
						$results = array_slice( $results, $offset, $limit );
					}
					#---
					if ($this->limit > count($results))
					{
						$_limit = $this->limit - count($results);
						$popPages = $this->getMostPopularPages($_limit);
						if (!empty($popPages) && is_array($popPages))
						{
							$results = array_merge($results, $popPages);
						}
					}
					#---
					if (!empty($results))
					{
						foreach ($results as $id => $result)
						{
							$data[$id] = $result;
							ApiResult :: setContent( $data[$id], $result['title'] );
						}
					}
					$this->saveCacheData($lcache_key, $data, $ctime);
				}
			} else {
				// ... cached
				$data = $cached;
			}
		} catch (WikiaApiQueryError $e) {
			// getText();
		} catch (DBQueryError $e) {
			$e = new WikiaApiQueryError(0, 'Query error: '.$e->getText());
		} catch (DBConnectionError $e) {
			$e = new WikiaApiQueryError(0, 'DB connection error: '.$e->getText());
		} catch (DBError $e) {
			$e = new WikiaApiQueryError(0, 'Error in database: '.$e->getLogMessage());
		}

		// is exception
		if ( isset($e) ) {
			$data = $e->getText();
			$this->getResult()->setIndexedTagName($data, 'fault');
		}
		else
		{
			$this->getResult()->setIndexedTagName($data, 'item');
		}
		$this->getResult()->addValue('query', $this->getModuleName(), $data);
	}

	/*
	 * get most frequently used pages from page_stats
	 */
	private function getMostPopularPages($_limit) 
	{
		global $wgDBname;
		
		try {
			#--- database instance
			$db =& $this->getDB();
			$db->selectDB( (!defined(WIKIA_API_QUERY_DBNAME)) ? WIKIA_API_QUERY_DBNAME : $wgDBname );

			if ( is_null($db) ) {
				//throw new DBConnectionError(&$db, 'Connection error');
				throw new WikiaApiQueryError(0);
			}

			/* revision was added for Gamespot project - they need last_edit timestamp */
			/* its a hack, a better way would be to make wkpoppages an API generator */
			/* Nef @ 20071026 */
			$this->addTables( array( "page_stats", "page", "revision" ) );
			$this->addFields( array(
				'article_id',
				'page_title', 
				'page_namespace',
				'rev_timestamp AS last_edit',
				'sum(article_count) as sum_cnt'
				));
			$this->addWhere ( " page_id = article_id " );
			$this->addWhere ( " rev_id  = page_latest " );
		
			#--- identifier of date
			if ( !is_null($this->date) ) {
				if ( !$this->isCorrectDate($this->date) ) {
					throw new WikiaApiQueryError(1);
				}
				$this->addWhere ( " date_stats > '".$this->date."' " );
			}
			
			#---
			if ( !empty($this->ctime) ) {
				if ( !$this->isInt($this->ctime) ) {
					throw new WikiaApiQueryError(1);
				}
			}
			
			#--- limit
			if ( !empty($_limit)  ) { // method parameter
				if ( !$this->isInt($_limit) ) {
					throw new WikiaApiQueryError(1);
				}
				$this->addOption( "LIMIT", $_limit );
			}

			#--- order by
			$this->addOption( "ORDER BY", "sum_cnt desc" );
			#--- group by
			$this->addOption( "GROUP BY", "article_id" );

			$data = array();
			#---
			$res = $this->select(__METHOD__);
			while ($row = $db->fetchObject($res)) 
			{
				$data[$row->article_id] = array(
					'id'			=> $row->article_id,
					'title'			=> $row->page_title,
					'namespace'		=> $row->page_namespace,
					#'last_edit'	=> wfTimestamp(TS_ISO_8601, $row->last_edit),
					#'counter'		=> $row->sum_cnt,
				);
			}
			$db->freeResult($res);
			
		} catch (WikiaApiQueryError $e) {
			// getText();
		} catch (DBQueryError $e) {
			$e = new WikiaApiQueryError(0, 'Query error: '.$e->getText());
		} catch (DBConnectionError $e) {
			$e = new WikiaApiQueryError(0, 'DB connection error: '.$e->getText());
		} catch (DBError $e) {
			$e = new WikiaApiQueryError(0, 'Error in database: '.$e->getLogMessage());
		}

		// is exception
		if ( isset($e) ) 
			return false;
		#---
		return $data;
	}


	/*
	 *
	 * Description's functions
	 *
	 */
	#---
	protected function getQueryDescription() {
		return 'Get best pages - get most popular articles defined on MW page and frequently used from db if necessary!';
	}

	/*
	 *
	 * Description's parameters
	 *
	 */
	#---
	protected function getParamQueryDescription() {
		return 	array (
			'pagename' => 'Name of page with "most visited articles"',
			'date' => 'Get statistics from DB newer than \'date\'',
		);
	}

	/*
	 *
	 * Allowed parameters
	 *
	 */

	#---
	protected function getAllowedQueryParams() {
		return array (
			"pagename" => array (
				ApiBase :: PARAM_TYPE => 'string'
			),
			"date" => array (
				ApiBase :: PARAM_TYPE => 'string',
			),
		);
	}

	/*
	 *
	 * Examples
	 *
	 */
	#---
	protected function getQueryExamples() {
		return array (
			'api.php?action=query&list=wkbestpages&wkpagename=Most_popular_articles',
			'api.php?action=query&list=wkbestpages&wkpagename=Most_popular_articles&wklimit=10',
			'api.php?action=query&list=wkbestpages&wkpagename=Most_popular_articles&wkdate=2007-05-01&wklimit=10',
		);
	}

	/*
	 *
	 * Version
	 *
	 */
	#---
	public function getVersion() {
		return __CLASS__ . ': $Id: '.__CLASS__.'.php '.filesize(dirname(__FILE__)."/".__CLASS__.".php").' '.strftime("%Y-%m-%d %H:%M:%S", time()).'Z wikia $';
	}
};

?>
