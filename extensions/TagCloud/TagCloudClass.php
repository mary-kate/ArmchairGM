<?php
/**
 

/** */
 
class TagCloud {
	var $tags_min_pts = 8;
	var $tags_max_pts = 32;
	var $tags_highest_count = 0;
	var $tags_size_type = "pt";
	
	 public function __construct($limit=10) {
		 $this->limit = $limit;
		 $this->tags = array();
		 $this->initialize();
	 }
	 
	 public function initialize(){
		global $wgBlogCategory;
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT  replace( cl_to ,'_{$wgBlogCategory}','') as cl_to, count(*) as count FROM {$dbr->tableName( 'categorylinks' )} cl1 
		WHERE (cl_to) LIKE '%_{$wgBlogCategory}' 
			GROUP BY replace( cl_to ,'_{$wgBlogCategory}','')
			ORDER BY 
			count DESC 
			LIMIT 0,{$this->limit}";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			$tag_name = Title::makeTitle( NS_CATEGORY, $row->cl_to);
			$tag_text = $tag_name->getText();
			if( strtotime( $tag_text  ) == "" ){ //dont want dates to show up
				if($row->count > $this->tags_highest_count)$this->tags_highest_count = $row->count;
				$this->tags[ $tag_text ] = array("count" => $row->count);
			}
		}
		
		//sort tag array by key (tag name)
		ksort($this->tags);
		
		if( $this->tags_highest_count - 1 > 0 ){
			$coef = ($this->tags_max_pts - $this->tags_min_pts)/($this->tags_highest_count-1);
			
			foreach ($this->tags as $tag => $att) {
				$this->tags[$tag]["size"] = $this->tags_min_pts + ($this->tags[$tag]["count"] - 1) * $coef;
			}
		}
	 }


}
?>
