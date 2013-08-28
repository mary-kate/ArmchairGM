<?php
/*
	Shared Contributions - displays user's contributions from multiple wikis
*/

if(!defined('MEDIAWIKI'))
   die();

$wgAvailableRights[] = 'lookupcontribs';
$wgGroupPermissions['staff']['lookupcontribs'] = true;

/* add data to tables */
$wgExtensionFunctions[] = 'wfLookupContribsPageSetup';
$wgExtensionCredits['specialpage'][] = array(
   'name' => 'Lookup Contribs',
   'author' => 'Bartek',
   'description' => 'Displays user contributions on multiple wikis'
);

/* special page setup function */
function wfLookupContribsPageSetup () {
   global $IP, $wgMessageCache;
   require_once($IP. '/includes/SpecialPage.php');
   /* name, restrictions, */
   SpecialPage::addPage(new SpecialPage('Lookupcontribs', 'lookupcontribs', true, 'wfLookupContribsCore', false));
   $wgMessageCache->addMessage('lookupcontribs', 'Lookup Contribs');
}

/* special page core function */
function wfLookupContribsCore () {
global $wgOut, $wgUser, $wgRequest, $wgUser ;
        $wgOut->setPageTitle("Lookup Contribs") ;
	$username = $wgRequest->getVal ('target') ;	
	$mode = $wgRequest->getVal ('mode') ;
	$view = $wgRequest->getVal ('view') ;
	if ('' != $username) {
		$sk = $wgUser->getSkin () ;
		$page_user = Title::makeTitle (NS_USER, $username) ;
		$user_link = $sk->makeKnownLinkObj ($page_user, $username) ;
		('normal' == $mode) ? $mode_text = "Recent contributions " : $mode_text = "Final contributions " ;
        	$wgOut->setSubtitle ("$mode_text for (". $user_link.")") ;
	}
	$scF = new LookupContribsForm () ;
	$scF->showForm ('',$username) ;
	$scL = new LookupContribsList ($view, $mode) ;
        $scL->showList ('',$username) ;
}

/* form class */
class LookupContribsForm {
	/* constructor */
	function LookupContribsForm () {

	} 

	/* draws select and selects it properly */
	function makeSelect ($name, $options_array, $current) {
        	global $wgOut ;
		$wgOut->addHTML ("<select name=\"$name\">") ;
		foreach ($options_array as $key => $value) {
			if ($value == $current )
	                	$wgOut->addHTML ("<option value=\"$value\" selected=\"selected\">$key</option>") ;
			else
	                	$wgOut->addHTML ("<option value=\"$value\">$key</option>") ;
		}
		$wgOut->addHTML ("</select>") ;
	} 

	/* draws the form itself  */
	function showForm ($error, $username) {
		global $wgOut, $wgRequest ;

		/* on error, display error */
                if ( "" != $error ) {
                        $wgOut->addHTML ("<p class='error'>{$error}</p>\n") ;
                }

		$titleObj = Title::makeTitle( NS_SPECIAL, 'Lookupcontribs' );
		/* help and stuff */
                $action = $titleObj->escapeLocalURL ("");

		$wgOut->addWikiText (LOOKUPCONTRIBS_HELP) ;
		$wgOut->addHTML ("
			<form method=\"get\" action=\"$action\" >
				Select user: <input name=\"target\" value=\"$username\"/>&#160;&#160;
				Search for: &#160;
		") ;

		$this->makeSelect ('mode',
				   array (
					'recent contributions for that user' => 'normal',
					'final contributions for that user' => 'final'
				   ),
				   $wgRequest->getVal ('mode')				   
			) ;
		$wgOut->addHTML ("&#160;&#160;Display mode: &#160;") ;
		$this->makeSelect ('view',
				   array (
					'full urls' => 'full',
					'contribs links' => 'links',
				   ),
				   $wgRequest->getVal ('view')				   
			) ;

		$wgOut->addHTML ("
				&#160;
				<input type=\"submit\" value=\"Go\">
			</form><br/>
		") ;

	/* indicate when we have no user */
	if ("" == $username ) {
        	$wgOut->addWikiText (":You haven't specified a user yet.") ;		
	}
}
}

/* list class  */
class LookupContribsList {                          
	var $numResults, $mView, $mMode, $mModes, $mViewModes  ;

	/* constructor */
	function LookupContribsList ($view, $mode) {
		$this->numResults = 0 ;
		$this->mView = $view ;
		$this->mMode = $mode ;
		$this->mModes = array ('normal', 'final') ;
		$this->mViewModes = array ('full', 'links') ;
	} 
	
        /* show it up */
	function showList ($error, $username) {
		global $wgOut, $wgRequest ;

		/* no list when no user */
		if ("" == $username)
			return false ;

		/* no list when user does not exist - may be a typo */

		if (0 == $this->checkUser ($username) ) {
                        $wgOut->addHTML ("<p class='error'>User \"<b>{$username}</b>\" does not exist. Check given username for possible typos.</p>\n") ;
			return false ;
		} 
		
		/* run a check against possible modes */
		if (!in_array($this->mView, $this->mViewModes)) {
                        $wgOut->addHTML ("<p class='error'>\"<b>{$this->mView}</b>\" is not a valid view mode.</p>\n") ;
			return false ;		
		}

		if (!in_array($this->mMode, $this->mModes)) {
                        $wgOut->addHTML ("<p class='error'>\"<b>{$this->mMode}</b>\" is not a valid mode.</p>\n") ;
			return false ;		
		}

		/* before, we need that numResults */
		$this->numResults = 0 ;
		$wikias = $this->fetchWikias () ;
		$fetched_all = array () ;
		if (is_array($wikias)) {
			foreach ($wikias as $wiki) {
				/* just fetch, don't display it yet */
				$fetched_all = $this->fetchContribs ($wiki->city_dbname, $wiki->city_url, $username, $fetched_all, $this->mMode, $this->mView, $wiki->city_title) ;
			} 
		}
		
		$this->showPrevNext ($wgOut) ;
		if ( 'full' == $this->mView ) {
			$wgOut->addHTML ("<table cellpadding=\"2\" cellspacing=\"2\"><thead><th>Page</th><th colspan=\"3\">Stats</th><th>Time</th></thead><tbody>") ; 
		} else if ('links' == $this->mView) {
			$wgOut->addHTML ("<table cellpadding=\"2\" cellspacing=\"2\"><thead><th>Wiki</th><th>Contribs link</th></thead><tbody>") ; 
		}
		
		$this->limitResult ($fetched_all, $username, $this->mView, $this->mMode) ;
		$wgOut->addHTML ("</tbody></table>") ; 
		$this->showPrevNext ($wgOut) ;
	}

	/* return if such user exists */
	function checkUser ($name) {
		global $wgSharedDB, $wgUser ;

		/* for all those anonymous users out there */
		if ($wgUser->isIP($name)) {
			return true ;
		}		
                $dbr =& wfGetDB( DB_SLAVE );
                $s = $dbr->selectRow( 'user', array( 'user_id' ), array( 'user_name' => $name ) );

                if ( $s === false ) {
                        return 0;
                } else {
                        return $s->user_id;
                }
	}

	/* limit results */
	function limitResult ($result_array, $username, $view, $mode) {
		global $wgOut, $wgRequest ;
		/* no matches found? */
		if ( 0 == count($result_array) ) {
			if ('full' == $view)
				$wgOut->addHTML("<tr><td colspan=\"3\">There are no results found.</td></tr>") ;			
			else if ('links' == $view)
				$wgOut->addHTML("<tr><td colspan=\"2\">There are no results found.</td></tr>") ;					
			return false ;
		}
		$range = 0 ;
		/* sort by timestamps in descending order */
		ksort ($result_array) ;
	        $result_array = array_reverse ($result_array);
		/* now, renumerate array */
		$result_array = array_values ($result_array) ;
		list( $limit, $offset ) = $wgRequest->getLimitOffset() ;
		( count ($result_array) < ($limit + $offset) ) ? $range = count ($result_array) : $range = ($limit + $offset)  ;
		for ($i = $offset; $i < $range; $i++) {			
			$this->produceLine ($result_array[$i], $username, $view, $mode) ;
		}
	}

	/* fetch all wikias from the database */
	function fetchWikias () {
		/* will memcache this - but where will be mechanism controlling that when we add/delete wikias from database?
		   won't the data get outdated? surely it would

		*/
        	global $wgMemc, $wgSharedDB ;
		$key =  "$wgSharedDB:LookupContribs:wikias" ;
                $cached = $wgMemc->get ($key) ;
		if (!is_array ($cached) || LOOKUPCONTRIBS_NO_CACHE) {
			/* from database */
			$dbr =& wfGetDB (DB_SLAVE);
			$query = "SELECT city_dbname, city_url, city_title FROM `{$wgSharedDB}`.city_list" ;
			$res = $dbr->query ($query) ;			
			$wikias_array = array () ;
			while ($row = $dbr->fetchObject($res)) {
				array_push ($wikias_array, $row ) ;
			}
			$dbr->freeResult ($res) ;			
			if (!LOOKUPCONTRIBS_NO_CACHE) $wgMemc->set ($key, $wikias_array) ;			
			return $wikias_array ;
		} else {
			/* from memcached */
			return $cached ;
		}		
	}

        /* init for showprevnext */
        function showPrevNext( &$out ) {
                global $wgContLang, $wgRequest;
                list( $limit, $offset ) = $wgRequest->getLimitOffset();
                $target = 'target=' . urlencode ( $wgRequest->getVal ('target') ) ;
                $view = 'view=' . urlencode ( $this->mView ) ;
		$mode = 'mode=' . urlencode ( $this->mMode ) ;
		$bits = implode ("&", array ($target, $view, $mode) ) ;
                $html = wfViewPrevNext(
                                $offset,
                                $limit,
                                $wgContLang->specialpage( 'Lookupcontribs' ),
                                $bits,
                                ($this->numResults - $offset) <= $limit
                        );
                $out->addHTML( '<p>' . $html . '</p>' );
        }

	/* a customized version of makeKnownLinkObj - hardened'n'modified for all those non-standard wikia out there */
	function produceLink ($nt, $text = '', $query = '', $url = '', $sk, $wiki_meta, $namespace, $article_id) {
		global $wgContLang, $wgOut, $wgMetaNamespace ;

		$str = $nt->escapeLocalURL ($query) ; 

		/* replace empty namespaces, namely: "/:Something" of "title=:Something" stuff
		it's ugly, it's brutal, it doesn't lead anywhere 
		*/
		$old_str = $str ;
		$str = preg_replace ('/title=:/i', "title=ns-".$namespace.":", $str) ; 
		$append = '' ;
		/* if found and replaced, we need that curid */
		if ($str != $old_str) {
			$append = "&curid=".$article_id ;			
		}
		$old_str = $str ;
		$str = preg_replace ('/\/:/i', "/ns-".$namespace.":", $str) ;
		if ($str != $old_str) {
			$append = "?curid=".$article_id ;			
		}

		/* replace NS_PROJECT space - it gets it from $wgMetaNamespace, which is completely wrong in this case  */
		if (NS_PROJECT == $nt->getNamespace()) {
		       $str = preg_replace ("/$wgMetaNamespace/", "Project", $str) ; 			
		}

                $part = explode ("php", $str ) ;
		if ($part[0] == $str) {		
			$part = explode ("wiki/", $str ) ; 
			$u = $url. "wiki/". $part[1] ;
		} else {		
			$u = $url ."/index.php". $part[1] ;
		}
                if ( $nt->getFragment() != '' ) {
                        if( $nt->getPrefixedDbkey() == '' ) {
                                $u = '';
                                if ( '' == $text ) {
                                        $text = htmlspecialchars( $nt->getFragment() );
                                }
                        }
                        $anchor = urlencode( Sanitizer::decodeCharReferences( str_replace( ' ', '_', $nt->getFragment() ) ) );
                        $replacearray = array(
                                '%3A' => ':',
                                '%' => '.'
                        );
                        $u .= '#' . str_replace(array_keys($replacearray),array_values($replacearray),$anchor);
                }
                if ( $style == '' ) {
                        $style = $sk->getInternalLinkAttributesObj( $nt, $text );
                }
                if ( $aprops !== '' ) $aprops = ' ' . $aprops;
                list( $inside, $trail ) = Linker::splitTrail( $trail );
		if ($text != '') {
	                $r = "<a href=\"{$u}{$append}\"{$style}{$aprops}>{$text}</a>{$trail}";
		} else {
	                $r = "<a href=\"{$u}{$append}\"{$style}{$aprops}>".urldecode($u)."</a>{$trail}";
		}
                return $r;
	}

	/* produces a single result line for a single wiki */
	function produceWikiLine ($row, $username) {
		global $wgLang, $wgOut, $wgRequest, $wgUser ;		
		$sk = $wgUser->getSkin () ;
		$page_user = Title::makeTitle (NS_USER, $username) ;
		$page_contribs = Title::makeTitle (NS_SPECIAL, "Contributions/{$username}") ;
		$page = Title::makeTitle (NS_MAIN, '') ;
		$link = $this->produceLink ($page, '', '', $row->city_url, $sk, '', $row->rc_namespace, $row->page_id) ;
        	$contrib = '('.$this->produceLink ($page_contribs, 'contribs', '', $row->city_url, $sk, '', $row->page_id ) .')' ;
		$wgOut->addHTML ("<tr><td>$link</td><td>$contrib</td></tr>") ;
	}

	/* produces a single result line out of a single row */
	function produceLine ($row, $username, $view, $mode) {
		global $wgLang, $wgOut, $wgRequest, $wgUser ;		
		$sk = $wgUser->getSkin () ;
		$page_user = Title::makeTitle (NS_USER, $username) ;
		$page_contribs = Title::makeTitle (NS_SPECIAL, "Contributions/{$username}") ;
		$meta = strtr($row->rc_city_title,' ','_') ;
        	$contrib = '('.$this->produceLink ($page_contribs, 'contribs', '', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id ) .')' ;
		
		/* two search modes beyond the view modes*/
		if ('normal' == $mode ) {
                        /* links mode and full mode */
			$page = Title::makeTitle ($row->rc_namespace, $row->rc_title) ;
			if ('full' == $view) {
				$link = $this->produceLink ($page, '', '', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id) ;
				$time = $wgLang->timeanddate( wfTimestamp( TS_MW, $row->rc_timestamp ), true );
				$diff = '('.$this->produceLink ($page, 'diff', 'diff=prev&oldid='.$row->rev_id, $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id ).')' ;
				$user = $sk->makeKnownLinkObj ($page_user, $username) ;
				$hist = '('.$this->produceLink ($page, 'hist', 'action=history', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id) . ')' ;
				$wgOut->addHTML ("<tr><td>$link</td><td> $diff</td> <td>$hist</td> <td>$contrib</td><td>$time</td></tr>") ;
			} else {
				$page = Title::makeTitle (NS_MAIN, '') ;
				$link = $this->produceLink ($page, $row->rc_url, '', $row->rc_url, $sk, '', $row->rc_namespace, $row->page_id) ;
				$wgOut->addHTML ("<tr><td>$link</td><td>$contrib</td></tr>") ;				
			}
		} else if ('final' == $mode ) {
			$page = Title::makeTitle ($row->rc_namespace, $row->page_title) ;
			if ('full' == $view) {
				$time = $wgLang->timeanddate( wfTimestamp( TS_MW, $row->rev_timestamp ), true );
				$link = $this->produceLink ($page, '', '', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id) ;
				$diff = '('.$this->produceLink ($page, 'diff', 'diff=prev&oldid='.$row->rev_id, $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id ).')' ;
				$user = $sk->makeKnownLinkObj ($page_user, $username) ;
				$hist = '('.$this->produceLink ($page, 'hist', 'action=history', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id) . ')' ;
        			$contrib = '('.$this->produceLink ($page_contribs, 'contribs', '', $row->rc_url, $sk, $meta, $row->rc_namespace, $row->page_id ) .')' ;
				$wgOut->addHTML ("<tr><td>$link</td><td>$diff</td><td>$hist</td><td>$contrib</td><td>$time</td></tr>") ;
			} else {
				$page = Title::makeTitle (NS_MAIN, '') ;
				$link = $this->produceLink ($page, $row->rc_url, '', $row->rc_url, $sk, '', $row->rc_namespace, $row->page_id) ;
				$wgOut->addHTML ("<tr><td>$link</td><td>$contrib</td></tr>") ;				
			}
		}
	}

	function exclusionCheck ($database) {
	       global $wgLookupContribsExcluded ;
	       /* grumble grumble _precautions_ cough */
	       if (!isset($wgLookupContribsExcluded) || (!is_array($wgLookupContribsExcluded)) || (empty($wgLookupContribsExcluded))  ) {
	       		return true ; 	
	       }
	       foreach ($wgLookupContribsExcluded as $excluded) {
	       		if ($excluded == $database) {
				return false ;
			}			
	       }
	       return true ;
	}

	/* fetch all contributions from that given database */
	function fetchContribs ($database, $url, $username, $fetched_array, $fetch_mode, $view_mode, $wikia) {
		global $wgOut, $wgRequest, $wgLang, $wgMemc, $wgSharedDB ;
		/*
			currently stuff works like this: store each database's data in memcache in a separate key (per database and per user),
			delete that key on hooks, (for that database only) - this way, the data from memcache would be updated only in small parts,
			despite the initial Grand Invocation
				umm, never mind that - now we have no cache    
 		*/

		/* todo since there are now TWO modes, we need TWO keys to rule them all */		
		$key = "$wgSharedDB:LookupContribs:$fetch_mode:$view_mode:$username:$database" ;
		$cached = $wgMemc->get ($key) ;
		$fetched_data = array () ;
		if ( !is_array($cached) || LOOKUPCONTRIBS_NO_CACHE) {			
			/* get that data from database */
			$dbr =& wfGetDB (DB_SLAVE);                
			/* check if such database exists... */
			if ($dbr->selectDB ($database)) {
				/* don't check these databases - their structure is not overly compactible */
				if (!$this->exclusionCheck($database)) {
					return $fetched_array ;
				}
				if ('normal' == $fetch_mode) {
					/* mode one, query one - recent contributions */
					$query = "SELECT rc_title, rev_id, rev_page as page_id, rev_timestamp as rc_timestamp, rc_namespace, rc_new 
						  FROM `$database`.recentchanges, `$database`.revision
						  WHERE rc_timestamp = rev_timestamp and rev_user_text={$dbr->addQuotes($username)}
						  ORDER BY rev_timestamp DESC" ;
					if ('links' == $view_mode) {
						$query .= " LIMIT 0,1" ;
					}
				} else if ('final' == $fetch_mode) {
					/* mode two, query two - only final contributions */
					$query = "SELECT page_title, rev_id, page_id, rev_timestamp, page_namespace as rc_namespace 
						  FROM `$database`.revision, `$database`.page 
						  WHERE rev_id = page_latest AND rev_user_text={$dbr->addQuotes($username)}
						  ORDER BY rev_timestamp DESC" ;	
					if ('links' == $view_mode) {
						$query .= " LIMIT 0,1" ;
					}
				} else { /* unknown mode */
					return $fetched_array ;		
				}

		        	$res = $dbr->query ($query) ;

				while ( $row = $dbr->fetchObject($res) ) {
					$row->rc_database = $database ;
					$row->rc_url = $url ;
					$row->rc_city_title = $wikia ;
					('normal' == $fetch_mode) ? $fetched_data[$row->rc_timestamp] = $row : $fetched_data[$row->rev_timestamp] = $row;
				}
				$this->numResults += $dbr->numRows ($res) ;			
				$dbr->freeResult ($res) ;
				if (!LOOKUPCONTRIBS_NO_CACHE)	$wgMemc->set ($key, $fetched_data) ;
				$fetched_array = $fetched_array + $fetched_data ;
			}
			return $fetched_array ;				
		} else {
                	/* get that data from memcache */
			$this->numResults += count($cached) ;
			$fetched_array = $fetched_array + $cached ;
			return $fetched_array ;
		}	                                      	
	}
}

?>
