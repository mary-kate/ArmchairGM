<?php

$wgExtensionFunctions[] = 'wfSpecialAaron';

function wfSpecialAaron(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class UserImageList extends SpecialPage {

	
	function UserImageList(){
		UnlistedSpecialPage::UnlistedSpecialPage("UserImageList");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		//variables
		$output = "";
		$user_name = $wgRequest->getVal('user');
		$page =  $wgRequest->getVal('page');
		$per_page = 10;
		if(!$page)$page=1;
		
		//No UserName Then Assume Current User			
		if(!$user_name)$user_name = $wgUser->getName();
		$user_id = User::idFromName($user_name);
		$user =  Title::makeTitle( NS_USER  , $user_name  );
		
		//No UserName Then Error Message
		if($user_id == 0){
			$wgOut->setPagetitle( "Woops!" );
			$wgOut->addHTML("The user you are trying to view does not exist.");
			return false;
		}
		
		//set title
		$wgOut->setPagetitle( "Gallery of Photos From {$user_name}" );
		
		//css
		$output .= "<style>
			.user-image-container {
				float:left;
				margin:0px 10px 15px 0px;
			}
			
			.user-image {
				padding:4px;
				border:1px solid #dcdcdc;
			}
			
			.slide-show-top {
				margin:-5px 0px 15px 0px;
			}
		</style>";
		
		//set javascript
		$output .= "
			<script language = \"javascript\">/*<![CDATA[*/
			
			function doHover(divID) {
				$(divID).setStyle({backgroundColor: '#4B9AF6'});
			}
			
			function endHover(divID){
				$(divID).setStyle({backgroundColor: ''});
			}
			
			
			/*]]>*/</script>
		";
		
		//Add Limit to SQL
		$per_page = 15;
		$limit=$per_page;
		
		if ($limit > 0) {
				$limitvalue = 0;
				if($page)$limitvalue = $page * $limit - ($limit); 
				$limit_sql = " LIMIT {$limitvalue},{$limit} ";
		}
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		$sql_total = "SELECT count(*) as count FROM image 
		INNER JOIN
		categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user_name)."'
		AND cl_to = 'Profile_Pictures'
		";
	    $res_total = $dbr->query($sql_total);
		$row = $dbr->fetchObject($res_total);
		$total = $row->count;
		
	
		$sql = "SELECT img_name, img_user, img_user_text, img_timestamp FROM image INNER JOIN
		categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user_name)."' 
		AND cl_to = 'Profile_Pictures'
		ORDER BY img_timestamp DESC {$limit_sql}";
		$res = $dbr->query($sql);
		
		//Top Nav
		$output .= "<div class=\"slide-show-top\">
			<a href=\"index.php?title=User:{$user_name}\">Back to Profile</a> -
			<a href=\"index.php?title=Special:UserSlideShow&user={$user_name}&picture=0\">Slide Show</a>
		</div>";
		
		
		if ($total) {
		
			//Loop Through Images
			$per_row = 5;
			$x = 1;

			if(!$page)$page=1;

			while ($row = $dbr->fetchObject( $res ) ) {
				$image_path = $row->img_name;
				$render_image = Image::newFromName ($image_path);
				$thumb_image = $render_image->getThumbNail(128,0,true);
				$thumbnail = $thumb_image->toHtml();
				$image_id = "user-image-{$x}";


				$output .= "<div class=\"user-image-container\"><div class=\"user-image\" id=\"{$image_id}\" onmouseover=\"doHover('{$image_id}')\" onmouseout=\"endHover('{$image_id}')\"><a href=\"index.php?title=Image:{$image_path}\">{$thumbnail}</a></div></div>";
				if($x!=1 && $x%$per_row ==0) {
					$output .= "<div class=\"cleared\"></div>";
				}
				$x++;
			}


			//Page Nav

			$numofpages = $total / $per_page; 

			if($numofpages>1) {
				$output .= "<div class=\"page-nav\">";
				if($page > 1) { 
					$output .= "<a href=\"index.php?title=Special:UserImageList&user={$user_name}&page=" . ($page-1) . "\">prev</a> ";
				}


				if(($total % $per_page) != 0)$numofpages++;
				if($numofpages >=9)$numofpages=9+$page;

				for($i = 1; $i <= $numofpages; $i++) {
					if($i == $page) {
					    $output .=($i." ");
					} else {
					    $output .="<a href=\"index.php?title=Special:UserImageList&user={$user_name}&page=$i\">$i</a> ";
					}
				}

				if(($total - ($per_page * $page)) > 0){
					$output .=" <a href=\"index.php?title=Special:UserImageList&user={$user_name}&page=" . ($page+1) . "\">next</a>"; 
				}
				$output .= "</div>";
			}
			
		} else {
				$output .= "{$user_name} does not have any images!";
		}
		
		$output .= "<div class=\"cleared\"></div>";
		
		
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new UserImageList );

 


}

?>