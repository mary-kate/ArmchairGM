<?php
class FeaturedEditors{

	//Query Variables
	var $featured_editors = array();
	var $editorsStr = "";
	
	var $numEditors = 3;
	var $numDays;
	
	
	
	function __construct($days=7) {
		$this->numDays = $days;
		$this->featured_editors = $this->getFeaturedEditors($this->numAuthors);
		//$this->authorsStr = implode(";", $this->featured_authors);
		$this->setEditorList();
		$this->numEditors = sizeOf($this->featured_editors);
	}
	/*
	function newFromList($author_list) {
		$this->authorsStr = $author_list;
		$this->featured_authors = explode(";", $this->authorsStr);
		$this->numAuthors = sizeOf($this->featured_authors);
	}
	*/
	
	
	function getFeaturedEditors($num=3) {
		
		global $wgMemc;
		
		$oneDay = (24*60*60);
		$checkDateTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$checkDateTime = date("Ymd", $checkDateTime - ($this->numDays*$oneDay)) . "000000";
		
		$key = wfMemcKey( 'site', 'featured_editors', $this->numDays );
		$data = $wgMemc->get( $key );
		if( !$data){
			wfDebug( "loading editors from db - {$key}\n" );
			$dbr =& wfGetDB( DB_MASTER);
			$sqlc = "SELECT rev_user_text as author, count(distinct page_id) as numedits, max(rev_page) as newest FROM revision inner join page on rev_page=page_id WHERE rev_timestamp>{$checkDateTime} and rev_user > 0 and page_namespace=0 and rev_user NOT IN (SELECT ug_user from user_groups where ug_group in ('bot','bureaucrat')) group by rev_user_text order by numedits desc LIMIT 3";
			$res = $dbr->query($sqlc);
			$editors = array();
			while($row = $dbr->fetchObject( $res )) {
				$editors[] = array('name'=>$row->author,'numedits'=>$row->numedits,'newest'=>$this->getEditorEdits($row->author));
			}
			$wgMemc->set( $key, $editors, false, $oneday );
		}
		else{
			wfDebug( "loading featured_editors from cache - {$key}\n" );
			$editors = $data;
		}
		return $editors;
		
	}
	
	function getEditorEdits($editor, $numEdits=3) {
		global $wgMemc;
		
		$oneDay = (24*60*60);
		$checkDateTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$checkDateTime = date("Ymd", $checkDateTime - ($this->numDays*$oneDay)) . "000000";
		
		$key = wfMemcKey( 'recentedits', $editor, $this->numDays );
		$data = $wgMemc->get( $key );
		if( !$data){
			wfDebug( "loading recent edits for {$editor} from db - {$key}\n" );
			$dbr =& wfGetDB( DB_MASTER);
			$sqlc = "SELECT distinct page_id as edit FROM revision inner join page on rev_page=page_id WHERE rev_timestamp>{$checkDateTime} and rev_user_text='{$editor}' and page_namespace=0 order by rev_timestamp desc LIMIT 3";
			$res = $dbr->query($sqlc);
			$edits = array();
			while($row = $dbr->fetchObject( $res )) {
				$edits[] = $row->edit;
			}
			$wgMemc->set( $key, $edits, false, $oneday );
		}
		else{
			wfDebug( "loading recent edits for {$editor} from cache - {$key}\n" );
			$edits = $data;
		}
		return implode(";", $edits);
	}
	
	function getNumEditors() {
		return $this->numEditors;
	}
	
	function getEditorList() {
		return $this-editorsStr;
		
	}
	
	function setEditorList() {
		
		$returnStr = "";
		
		foreach($this->featured_editors as $key=>$value) {
			$returnStr .= $value['name'] . ";";
		}
		
		if (sizeof($this->featured_editors)) {
			$returnStr = substr($returnStr, 0, strlen($returnStr)-1);
		}
		
		$this->editorsStr = $returnStr;
	}
	
	function displayFeaturedEditors() {
		global $wgOut;
		$returnStr = "<div id=\"featured-articles-container\" class=\"featured-articles-container\">
			<h2>Featured Experts</h2>";
			$count = 1;
		
		
		foreach($this->featured_editors as $key=>$value) {
			
			$user = User::newFromName($value['name']);
			$avatar = new wAvatar($user->getID(), 'm'); 
			
			$returnStr .= "<div id=\"featured-article-{$count}\" class=\"featured-article" . (($count == $this->numEditors) ? " featured-article-last" : "") . "\">";
			$returnStr .= "<div id=\"featured-article-avatar-{$count}\" class=\"featured-article-avatar\">";
				$returnStr .= $avatar->getAvatarUrl();
			$returnStr .= "</div>";
			$returnStr .= "<div class=\"featured-article-right\">"; 
			$returnStr .= "<span id=\"featured-article-author-{$count}\" class=\"featured-article-author\">";
				$returnStr .= "<a href=\"" . $user->getUserPage()->getFullUrl() . "\">" . $value['name'] . "</a>";
				//$returnStr .= $value['name'];
			$returnStr .= "</span>";
			$returnStr .= "<span id=\"featured-article-title-{$count}\" class=\"featured-article-title-container\">";
			$returnStr .= " is editing the following entries: ";
			$edits = explode(";", $value['newest']);
			$edits_count = 1;
			foreach ($edits as $article_id) {
				$article_title = Title::newFromID(intval($article_id));
				
					$returnStr .= "<a href=\"" . $article_title->getFullUrl() . "\">" . $article_title->getText() . "</a>" . (($edits_count != sizeof($edits)) ? ", " : "");
					$edits_count++;
			}
			$returnStr .= "</span>";
			$returnStr .= "</div>";
			$returnStr .= "<div class=\"cleared\"></div>";
			$returnStr .= "</div>";
			
			$count++;
		}
		
		$returnStr .= "</div>";
		
		return $returnStr;
	}

}



?>
