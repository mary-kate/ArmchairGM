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
		if(!$type)$type="best";
		
		
		
		//sql limit based on page
		$per_page = 5;
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
			
			.image-rating-row {
				border-bottom:1px solid #dcdcdc;
				padding:0px 0px 20px 0px;
				margin:0px 0px 20px 0px;
				width:700px;
			}
			
			.image-rating-row-bottom {
				padding:0px 0px 0px 0px;
				margin:0px 0px 20px 0px;
				width:700px;
			}
			
			.image-rating-container {
				float:left;
				width:250px;
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
			
			.image-categories-container {
				float:left;
				width:400px;
			}
			
			.image-categories-container h2 {
				font-size:14px;
				font-weight:bold;
				color:#333333;
				margin:0px 0px 5px 0px;
			}
			
			.category-button {
				border-top:1px solid #dcdcdc;
				cursor:pointer;
				float:left;
				font-size:11px;
				margin:0px 5px 5px 0px;
				padding:5px;
				border-left:1px solid #dcdcdc;;
				border-right:2px solid #666666;;
				border-bottom:2px solid #666666;;
				color:#376EA6;;
				font-weight:bold;
			}
			
			.image-categories-add {
				margin:10px 0px 0px 0px;
			}
		
			.featured-image {
				background-color:#F2F4F7;
				border:1px solid #dcdcdc;
				margin:0px 0px 20px 0px;
				padding:15px;
				width:685px;
			}
			
			.featured-image h2 {
				color:#333333;
				margin:0px 0px 10px 0px !important;
			}
			
			.featured-image-container {
				float:left;
				width:300px;
			}
			
			.featured-image-container img {
				border:1px solid #dcdcdc;
				padding:3px;
			}
			
			.featured-image-user {
				float:left;
			}
			
			.featured-image-submitted {
				font-size:13px;
				color:#797979;
			}
			
			.featured-image-submitted p {
				margin:0px 0px 2px 0px !important;
			}
			
			.featured-image-submitted a {
				text-decoration:none;
				font-size:16px;
				font-weight:bold;
			}
			
			.featured-image-submitted img {
				vertical-align:middle;
			}
			
			.rate-image-navigation {
				font-weight:bold;
			}
		</style>";
		
		$output .= "<script>
				
				function doHover(divID) {
					$(divID).setStyle({backgroundColor: '#FFFCA9'});
				}
				
				function endHover(divID){
					$(divID).setStyle({backgroundColor: ''});
				}
				
		</script>";
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		
		if ($type == "best") {
			$sql = "SELECT page_id, page_title, vote_avg FROM page INNER JOIN page_stats ON page_id=ps_page_id WHERE page_namespace=" . NS_IMAGE . " ORDER BY vote_avg DESC, vote_count DESC {$limit_sql}";
			$sql_count = "SELECT COUNT(*) as total_ratings FROM page INNER JOIN page_stats ON page_id=ps_page_id WHERE page_namespace=" . NS_IMAGE . "";
			$res_count = $dbr->query($sql_count);
			$row_count = $dbr->fetchObject($res_count);
			$total = $row_count->total_ratings;
			$wgOut->setPageTitle("Best Images");
		} else {
			$sql = "SELECT page_id, page_title FROM page WHERE page_namespace=" . NS_IMAGE . " ORDER BY page_id DESC {$limit_sql}";
			$total = SiteStats::images();
			$wgOut->setPageTitle("New Images");
		}
		
		$res = $dbr->query($sql);
		
		//variables
		$x = 1;
		
		$output .= "<div class=\"image-rating-menu\">
			<h2>Menu</h2>";
			
			if ($type=="best") {
				$output .= "<p><b>Best Images</b></p>
				<p><a href=\"index.php?title=Special:ImageRating&type=new\">Newest Images</a></p>";
			} else {
				$output .= "<p><a href=\"index.php?title=Special:ImageRating&type=best\">Best Images</a></p>
				<p><b>Newest Images</b></p>";
			}
			
		$output .= "</div>";
		
		$output .= "<div class=\"image-ratings\">";
		
		$sql_top = "SELECT page_id, page_title, img_user, img_user_text FROM page, page_stats, image WHERE  page_id=ps_page_id and page_namespace=6 and page_title=img_name ORDER BY vote_avg DESC, vote_count DESC LIMIT 0,1";
		
		$res_top = $dbr->query($sql_top);
		$row_top = $dbr->fetchObject($res_top);
		$top_path = $row_top->page_title;
		$top_image_id = $row_top->page_id;
		$render_top_image = Image::newFromName($top_path);
		$thumb_top_image = $render_top_image->getThumbNail(250,250,true);
		$thumbnail_top = $thumb_top_image->toHtml();
		$top_image_user_id = $row_top->img_user;
		$top_image_user_name = $row_top->img_user_text;
		$avatar = new wAvatar($top_image_user_id,"ml");
		
		$voteClassTop = new VoteStars($top_image_id);
		$voteClassTop->setUser($wgUser->getName(),$wgUser->getID());
		$countTop = $voteClassTop->count();
		
		$output .= "<div class=\"featured-image\">
			<h2>Featured Image</h2>
				<div class=\"featured-image-container\">
					<a href=\"index.php?title=Image:{$top_path}\">{$thumbnail_top}</a>
				</div>
				<div class=\"featured-image-user\">
					
					<div class=\"featured-image-submitted\">
						<p>submitted by</p>
						<p><a href=\"index.php?title=User:".urlencode("$top_image_user_name")."\"><img src=images/avatars/{$avatar->getAvatarImage()}/>
						{$top_image_user_name}</a></p>
					</div>
					
					<div class=\"image-rating-bar\">"
						.$voteClassTop->displayStars( $image_id,  $voteClassTop->getAverageVote(), false ).
						"<div class=\"image-rating-score\" id=\"rating_{$top_image_id}\">
							community score: <b>".$voteClassTop->getAverageVote()."</b> ({$countTop}  ".(($countTop==1)?"rating":"ratings").")
						</div>
					</div>
					
				</div>
				<div class=\"cleared\"></div>
		</div>
		<h2>Rate and Tag Images</h2>";
		
		while ( $row = $dbr->fetchObject($res) ) {
			
			$image_path = $row->page_title;
			$image_id = $row->page_id;
			$render_image = Image::newFromName($image_path);
			$thumb_image = $render_image->getThumbNail(120,120,true);
			$thumbnail = $thumb_image->toHtml();
			
			$voteClass = new VoteStars($image_id);
			$voteClass->setUser($wgUser->getName(),$wgUser->getID());
			$count = $voteClass->count();
			
			if ($x !== $per_page) {
				$output .= "<div class=\"image-rating-row\">";
			} else {
				$output .= "<div class=\"image-rating-row-bottom\">";
			}
			
				$output .= "<div class=\"image-rating-container\">
					<div class=\"image-for-rating\">
						<a href=\"index.php?title=Image:{$image_path}\">{$thumbnail}</a>
					</div>
				
					<div class=\"image-rating-bar\">"
						.$voteClass->displayStars( $image_id,  $voteClass->getAverageVote(), false ).
						"<div class=\"image-rating-score\" id=\"rating_{$image_id}\">
							community score: <b>".$voteClass->getAverageVote()."</b> ({$count}  ".(($count==1)?"rating":"ratings").")
						</div>
					</div>
				</div>";
				
				$sql_category = "SELECT cl_to, cl_sortkey, cl_from FROM categorylinks WHERE cl_from={$image_id}";
				$sql_category_count = "SELECT COUNT(*) as category_count FROM categorylinks WHERE cl_from={$image_id}";
				$res_category_count = $dbr->query($sql_category_count);
				$row_category_total = $dbr->fetchObject($res_category_total);
				$category_total = $row_category_total->category_count;
			
				$res_category = $dbr->query($sql_category);
			
				$output .= "<div class=\"image-categories-container\">
					<h2>Categories</h2>";
			
					$per_row = 3;
					$category_x = 1;
			
					while ( $row_category = $dbr->fetchObject($res_category) ) {
					
					$category = str_replace("_", " ", $row_category->cl_to);
					$category_id = "category-button-{$image_id}-{$category_x}";
					
					$output .= "<div class=\"category-button\" id=\"{$category_id}\" onclick=\"window.location='index.php?title=Category:".urlencode($row_category->cl_to)."'\" onmouseover=\"doHover('{$category_id}')\" onmouseout=\"endHover('{$category_id}')\">
						$category
					</div>";
					
					if($category_x==$category_total || $category_x!=1 && $category_x%$per_row ==0)$output .= "<div class=\"cleared\"></div>";
					
					$category_x++;
					 
				}
			
				$output .= "<div class=\"cleared\"></div>
				<div class=\"image-categories-add\">
					add category <input type=\"text\" size=\"22\"/> <input type=\"button\" value=\"add\" class=\"site-button\"/>	
				</div>
					
		
			</div>";
			
			$output .= "<div class=\"cleared\"></div>
		</div>";
			
		$x++;
		$categories="";
			
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