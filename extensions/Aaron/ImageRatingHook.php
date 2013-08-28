<?php

$wgExtensionFunctions[] = 'wfImageRating';

function wfImageRating(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class ImageRating extends SpecialPage {

	
	function ImageRating(){
		UnlistedSpecialPage::UnlistedSpecialPage("ImageRating");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/Vote-Mag/Vote.js?{$wgStyleVersion}\"></script>\n");
		require_once ("$IP/extensions/Vote-Mag/VoteClass.php");
				
		
		//page
		$page = $wgRequest->getVal('page');
		$type = $wgRequest->getVal('type'); 
		if(!$page || !is_numeric($page) )$page=1;
		if(!$type)$type="new";
		
		
		
		//sql limit based on page
		$per_page = 16;
		$limit=$per_page;
		
		if ($limit > 0) {
				$limitvalue = 0;
				if($page)$limitvalue = $page * $limit - ($limit); 
				$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		
		//set javascript
		$output .= "<script language = \"javascript\">/*<![CDATA[*/
			
			function doHover(divID) {
				$(divID).setStyle({backgroundColor: '#4B9AF6'});
			}
			
			function endHover(divID){
				$(divID).setStyle({backgroundColor: ''});
			}
			
			/*]]>*/</script>";
		
		//set css 
		$output .= "<style>
			
			.image-rating-container {
				float:left;
				border:1px solid #dcdcdc;
				padding:10px;
				margin:0px 10px 10px 0px;
				width:170px;
				height:170px;
				text-align:center;
			}
			
			.image-for-rating img {
				padding:3px;
				border:1px solid #dcdcdc;
			}
			
			.image-rating-bar {
				margin:5px 0px 0px 0px;
			}
			
			.image-rating-score {
				color:#797979;
			}
			
			.image-rating-menu {
				float:right;
				width:180px;
			}
			
			.image-rating-menu h2 {
				color:#333333;
				font-size:16px;
				font-weight:bold;
				padding:0px 0px 3px 0px;
				border-bottom:1px solid #dcdcdc;
				margin:0px 0px 10px 0px !important;
			}
			
			.image-rating-menu p {
				margin:0px 0px 3px 0px !important;
			}
			
			.image-rating-menu a {
				text-decoration:none;
				font-weight:bold;
			}
		
		</style>";
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		
		if ($type == "best") {
			$sql = "SELECT page_id, page_title, vote_avg FROM page INNER JOIN page_stats ON page_id=ps_page_id WHERE page_namespace=6 ORDER BY vote_avg DESC {$limit_sql}";
			$sql_count = "SELECT COUNT(*) as total_ratings FROM page INNER JOIN page_stats ON page_id=ps_page_id WHERE page_namespace=6";
			$res_count = $dbr->query($sql_count);
			$row_count = $dbr->fetchObject($res_count);
			$total = $row_count->total_ratings;
			$wgOut->setPageTitle("Best Images");
		} else {
			$sql = "SELECT page_id, page_title FROM page WHERE page_namespace=6 ORDER BY page_id DESC {$limit_sql}";
			$total = SiteStats::images();
			$wgOut->setPageTitle("New Images");
		}
		
		
	
		
		$res = $dbr->query($sql);
		
		//variables
		$x = 1;
		$per_row = 4;
		
		$output .= "<div class=\"image-rating-menu\">
			<h2>Menu</h2>";
			
			if ($type=="best") {
				$output .= "<p><b>Best Images</b></p>
				<p><a href=\"index.php?title=Special:ImageRating\">Newest Images</a></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:ImageRating&type=best\">Best Images</a></p>
				<p><b>Newest Images</b></p>";
			}
			
		$output .= "</div>";
		
		$output .= "<div class=\"image-ratings\">";
		
		while ( $row = $dbr->fetchObject($res) ) {
			
			$image_path = $row->page_title;
			$image_id = $row->page_id;
			$render_image = Image::newFromName($image_path);
			$thumb_image = $render_image->getThumbNail(125,125,true);
			$thumbnail = $thumb_image->toHtml();
			
			$voteClass = new VoteStars($image_id);
			$voteClass->setUser($wgUser->getName(),$wgUser->getID());
			$count = $voteClass->count();
			
			$output .= "
			<div class=\"image-rating-container\">
			
				<div class=\"image-for-rating\">
					<a href=\"index.php?title=Image:{$image_path}\">{$thumbnail}</a>
				</div>
				
				<div class=\"image-rating-bar\">"
					.$voteClass->displayStars( $image_id,  $voteClass->getAverageVote(), false ).
					"<div class=\"image-rating-score\" id=\"rating_{$image_id}\">rating <b>".$voteClass->getAverageVote()."</b> ({$count} vote".(($count>1)?"s":"").")</div>
				</div>
				
			</div>";
			
			if ($x!=1 && $x%$per_row ==0) {
				$output .= "<div class=\"cleared\"></div>";
			}
			
			$x++;
			
		}
		
		$output .= "</div>
		<div class=\"cleared\"></div>";
		
		
		$numofpages = $total / $per_page; 
		
		if($numofpages>1){
			$output .= "<div class=\"rate-image-navigation\">";
			if($page > 1){ 
				$output .= "<a href=\"index.php?title=Special:ImageRating&page=" . ($page-1) . "&type={$type}\">prev</a> ";
			}
			
			
			if(($total % $per_page) != 0)$numofpages++;
			if($numofpages >=9 && $page < $total)$numofpages=9+$page;
			if($numofpages >= ($total / $per_page) )$numofpages = ($total / $per_page)+1;
			
			for($i = 1; $i <= $numofpages; $i++){
				if($i == $page){
				    $output .=($i." ");
				}else{
				    $output .="<a href=\"index.php?title=Special:ImageRating&page=$i&type={$type}\">$i</a> ";
				}
			}
	
			if(($total - ($per_page * $page)) > 0){
				$output .=" <a href=\"index.php?title=Special:ImageRating&page=" . ($page+1) . "&type={$type}\">next</a>"; 
			}
			$output .= "</div>";
		}
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new ImageRating );

 


}

?>