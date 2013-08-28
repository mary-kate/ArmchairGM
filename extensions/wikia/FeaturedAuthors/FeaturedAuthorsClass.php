<?php
class FeaturedAuthors{

	//Query Variables
	var $featured_authors = array();
	var $authorsStr = "";
	
	var $numAuthors = 3;
	var $numDays;
	
	
	
	function __construct($days=7) {
		$this->numDays = $days;
		$this->featured_authors = $this->getFeaturedAuthors($this->numAuthors);
		//$this->authorsStr = implode(";", $this->featured_authors);
		$this->setAuthorList();
		$this->numAuthors = sizeOf($this->featured_authors);
	}
	/*
	function newFromList($author_list) {
		$this->authorsStr = $author_list;
		$this->featured_authors = explode(";", $this->authorsStr);
		$this->numAuthors = sizeOf($this->featured_authors);
	}
	*/
	
	
	function getFeaturedAuthors($num=3) {
		
		global $wgMemc;
		
		$key = wfMemcKey( 'site', 'featured_authors', $this->numDays );
		$data = $wgMemc->get( $key );
		if( !$data){
			wfDebug( "loading featured_authors from db - {$key}\n" );
			$dbr =& wfGetDB( DB_MASTER);
			//$sqlc = "SELECT SUBSTRING(cl_to, 18) as author, sum(numvotes) as totalvotes FROM ((select * from categorylinks where cl_from in (SELECT distinct(vote_page_id) from Vote where vote_date > ADDTIME(NOW(), '-7 0:0:0.000000')) and categorylinks.cl_to LIKE 'Opinions_by_User_%') as category inner join (SELECT vote_page_id, count(vote_id) as numvotes FROM `armchairgm`.`Vote` WHERE vote_date > ADDTIME(NOW(), '-7 0:0:0.000000') GROUP BY vote_page_id) as q_votes on category.cl_from=q_votes.vote_page_id) group by cl_to ORDER BY totalvotes desc LIMIT {$num}";
			//$sqlc = "SELECT SUBSTRING(cl_to, 18)as author, sum(numvotes) as totalvotes, max(cl_from) as newest FROM ((select * from categorylinks where cl_from in (SELECT distinct(vote_page_id) from Vote where vote_date > ADDTIME(NOW(), '-7 0:0:0.000000')) and categorylinks.cl_to LIKE 'Opinions_by_User_%') as category inner join (SELECT vote_page_id, count(vote_id) as numvotes FROM `armchairgm`.`Vote` WHERE vote_date > ADDTIME(NOW(), '-7 0:0:0.000000') GROUP BY vote_page_id) as q_votes on category.cl_from=q_votes.vote_page_id) group by cl_to ORDER BY totalvotes desc LIMIT {$num}";
			$sqlc = "SELECT SUBSTRING(cl_to, 18)as author, sum(numvotes) as totalvotes, max(cl_from) as newest FROM ((select * from categorylinks where cl_from in (SELECT distinct(vote_page_id) from Vote where vote_date > ADDTIME(NOW(), '-{$this->numDays} 0:0:0.000000')) and categorylinks.cl_to LIKE 'Opinions_by_User_%') as category inner join (SELECT vote_page_id, count(vote_id) as numvotes FROM `armchairgm`.`Vote` WHERE vote_date > ADDTIME(NOW(), '-{$this->numDays} 0:0:0.000000') GROUP BY vote_page_id) as q_votes on category.cl_from=q_votes.vote_page_id) group by cl_to ORDER BY totalvotes desc LIMIT {$num}";
			$res = $dbr->query($sqlc);
			$authors = array();
			while($row = $dbr->fetchObject( $res )) {
				$authors[] = array('name'=>$row->author,'votes'=>$row->totalvotes,'newest'=>$row->newest);
			}
			//$wgMemc->set( $key, $authors );
		}
		else{
			wfDebug( "loading featured_authors from cache - {$key}\n" );
			$authors = $data;
		}
		return $authors;
		
	}
	
	function getNumAuthors() {
		return $this->numAuthors;
	}
	
	function getAuthorList() {
		return $this->authorsStr;
		
	}
	
	function setAuthorList() {
		
		$returnStr = "";
		
		foreach($this->featured_authors as $key=>$value) {
			$returnStr .= $value['name'] . ";";
		}
		
		if (sizeof($this->featured_authors)) {
			$returnStr = substr($returnStr, 0, strlen($returnStr)-1);
		}
		
		$this->authorsStr = $returnStr;
	}
	
	function displayFeaturedArticles() {
		global $wgOut;
		$returnStr = "<div id=\"featured-articles-container\" class=\"featured-articles-container\">";
		$count = 1;
		
		
		foreach($this->featured_authors as $key=>$value) {
			$article_title = Title::newFromID(intval($value['newest']));
			$user = User::newFromName($value['name']);
			$avatar = new wAvatar($user->getID(), 'm'); 
			
			$returnStr .= "<div id=\"featured-article-{$count}\" class=\"featured-article" . (($count == $this->numAuthors) ? " featured-article-last" : "") . "\">";
			$returnStr .= "<div id=\"featured-article-avatar-{$count}\" class=\"featured-article-avatar\">";
				$returnStr .= $avatar->getAvatarUrl();
			$returnStr .= "</div>";
			$returnStr .= "<div id=\"featured-article-author-{$count}\" class=\"featured-article-author\">";
				$returnStr .= "<a href=\"" . $user->getUserPage()->getFullUrl() . "\">" . $value['name'] . "</a>";
				//$returnStr .= $value['name'];
			$returnStr .= "</div>";
			$returnStr .= "<div class=\"cleared\"></div>";
			$returnStr .= "<div id=\"featured-article-title-{$count}\" class=\"featured-article-title\">";
				//$returnStr .= $article_title->getText();
				$returnStr .= "<a href=\"" . $article_title->getFullUrl() . "\">" . $article_title->getText() . "</a>";
			$returnStr .= "</div>";
			$returnStr .= "</div>";
			
			$count++;
		}
		
		$returnStr .= "</div>";
		
		return $returnStr;
	}

}



?>
