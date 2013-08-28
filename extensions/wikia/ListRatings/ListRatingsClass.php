<?php
class ListRatings{

	//Query Variables
	var $Categories = array(), $CategoriesStr = "";
	var $ShowCount = 5;
	var $PageNo = 1,$SortBy = NULL,$Order = NULL;
	var $user_id = 0, $user_name = "";
	
	/* private */ function __construct($username="") {
		if($username){
			$title1 = Title::newFromDBkey($username);
			$this->user_name = $title1->getText();
			$this->user_id = User::idFromName($this->user_name);
		}
		
	}
	
	function setCategory($ctg){
		global $wgUser,$wgLang, $wgContLang,$wgTitle,$wgOut;
		$parser = new Parser();
		$ctg = $parser->transformMsg( $ctg, $wgOut->parserOptions() );
		$ctg = str_replace("\,","#comma#",$ctg);
		$aCat = explode(",", $ctg);
		$CtgTitle = "";
		foreach($aCat as $sCat){
			if($sCat!=""){
				if($this->CategoriesStr!=""){
					$this->CategoriesStr .= ",";
				}
				$CtgTitle = Title::newFromText(  trim( str_replace("#comma#",",",$sCat))   );		
				$this->CategoriesStr .= $CtgTitle->getDbKey();
				$this->Categories[] = $CtgTitle;
			}
		}
	}
 
	function setShowCount($count){
		$this->ShowCount = IntVal( $count ) + 1;
	}

	function setPageNo($page){
		if($page && is_numeric($page)){
			$this->PageNo = $page;
		}else{
			$this->PageNo = 1;
		}
	}
	
	public function getRatingsList($limit = 5,$page = 0, $order = "vote_value", $sort="desc", $voted = true){
		$this->setPageNo($page);
		$this->setShowCount($limit);
		
		$dbr =& wfGetDB( DB_SLAVE );
		
		if($this->ShowCount>0){
			$limitvalue = 0;
			if($this->PageNo)$limitvalue = $this->PageNo * $this->ShowCount - ($this->ShowCount); 
			$limit_sql = " LIMIT {$limitvalue},{$this->ShowCount} ";
		}

		$sql = "SELECT page_id,page_title, page_namespace, vote_avg, IFNULL(vote_count,0) as vote_count ";
		if($voted)$sql.= ", vote_value, vote_date ";
		$sql.= " FROM {$dbr->tableName( 'page' )}
			LEFT JOIN wikia_page_stats ON page_id=ps_page_id ";
			
		if($voted)$sql.= " INNER JOIN Vote on vote_page_id=page_id ";
		$sql.= " INNER JOIN {$dbr->tableName( 'categorylinks' )} AS c
			ON page_id = c.cl_from WHERE 1=1
			";

			$sql_where = "";
			if(count($this->Categories) > 0){
				$sql_where .= ' AND UPPER(c.cl_to) in (';
				for ($i = 0; $i < count($this->Categories); $i++) {
					if($i>0)$ctg_sql .= ",";
					$ctg_sql .=  strtoupper($dbr->addQuotes( $this->Categories[$i]->getDbKey() ));
				}
				$sql_where .= $ctg_sql . ')';
			}
			$sql .= $sql_where;
			if($this->user_id){
				if($voted){
					$sql .= " AND vote_user_id = {$this->user_id} ";
				}else{
					$sql .= " AND page_id NOT IN (select vote_page_id from Vote WHERE vote_user_id = {$this->user_id} ) ";
				}
			}
			$sql .= " ORDER by {$order} {$sort} {$limit_sql}";
	
		$items = array();
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			 $items[] = array(
				 "page_id"=>$row->page_id,"page_title"=>$row->page_title,"page_namespace"=>($row->page_namespace ) , "vote"=>$row->vote_value,
				 "vote_avg" => $row->vote_avg, "vote_count"=>$row->vote_count,"vote_date"=>$row->vote_date
				 );
		}
		return $items;		
	}
	
	public function displayList($categories,$limit=5,$page=0,$order = "vote_value", $sort="desc", $voted = true){
		global $wgUser, $IP;
		require_once("$IP/extensions/Vote-Mag/VoteClass.php");
		
		$this->setCategory($categories);
		$list = $this->getRatingsList($limit,$page,$order,$sort,$voted);
		if($list){
			$output .= "<div id=test1 class=\"user-list-ratings\">";
	
			$ListCountShow=0;
			$ListCount = 0;
			foreach ($list as $item) {
				if($ListCountShow < $this->ShowCount - 1){
					$title = Title::makeTitle( $item["page_namespace"], $item["page_title"]);
				
					$Vote = new VoteStars($item["page_id"]);
					$Vote->setUser($wgUser->getName(),$wgUser->getID());
					$id = $ListCountShow;
					
					$output .= "<div class=\"user-list-rating\">
						<a href=\"{$title->getFullURL()}\">{$title->getText()}</a> ({$item["vote"]})
						</div>";
						
					$output .=	"<div id=\"rating_stars_{$id}\">" .  $Vote->displayStars($id,$item["vote_avg"],false) . "</div>";
					$output .=	"<div id=\"rating_{$id}\" class=\"rating-total\">" . $Vote->displayScore() . "</div>";
					
					$ListCountShow++;
				}
				$ListCount++;
			}
		}
		
		
		
		$output .= "<div class=\"buttons\">";
		if($this->PageNo == 1){
			$output .= $this->getNavLink("Prev",0);
		} else {
			$output .= $this->getNavLink("Prev",-1);
		}
		if($ListCount > $ListCountShow){
			$output .= $this->getNavLink("Next",1);
		} else {
			$output .= $this->getNavLink("Next",0);
		}
		$output .= "</div>";
			
		$output .= "</div>";
		return $output;
	}
	
	
	function getNavLink($Button,$Direction){
		$options = "{";
		$options .= "shw:'" .  ($this->ShowCount-1) . "',";
		$options.= "ctg:'" . $this->CategoriesStr . "',";
		$options .= "}";
		
		$nav = "";
	
		$nav .= "<a href=\"javascript:view_ratings('" . $this->user_name . "'," . ($this->PageNo + $Direction) . "," . $options .");\"> {$Button} </a>";
		
		
		return $nav; 
	}
	
	
}


?>