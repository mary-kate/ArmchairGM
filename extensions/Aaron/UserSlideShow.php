<?php

$wgExtensionFunctions[] = 'wfSpecialUserSlideShow';

function wfSpecialUserSlideShow(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class UserSlideShow extends SpecialPage {

	
	function UserSlideShow(){
		UnlistedSpecialPage::UnlistedSpecialPage("UserSlideShow");
	}

	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser;
		
		//variables
		$output = "";
		$user_name = $wgRequest->getVal('user');
		$picture_number = $wgRequest->getVal('picture');
		$previous = $picture_number-1;
		$next = $picture_number+1;
		
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
		$wgOut->setPagetitle( "Photos From {$user_name}" );
		
		//css
		$output .= "<style>
			.slide-show-top {
				margin:-5px 0px 15px 0px;
			}
			
			.slide-show-bottom {
				margin:0px 0px 20px 0px;
			}
			
			.slide-show-bottom a {
				text-decoration:none;
				font-size:13px;
			}
			
			.slide-show-bottom ul {
				padding:0px;
				margin:0px;
				list-style:none;
			}
			
			.slide-show-bottom li {
				float:left;
				margin:0px 10px 0px 0px;
			}
			
			.user-image-container {
				float:left;
			}
			
			.user-image {
				color: #c2c8d0;
				font-size:14px;
				font-weight:bold;
			}
			
			.user-image p {
				margin:0px 0px 10px 0px !important;
			}
			
			.user-image img {
				padding:3px;
				background-color:#ffffff;
				border:1px solid #dcdcdc;
			}
			
			.slide-show-friends {
				margin:0px 0px 20px 0px;
			}
			
			
			.slide-show-right {
				float:right;
				width:300px;
				margin:-65px -20px 0px 0px;
			}
			
			.slide-show-friends h1 {
				color:#333333;
				margin:0px 0px 10px 0px !important;
				font-size:14px;
				padding:0px 0px 3px 0px;
				font-weight:bold;
			}
			
			.slide-show-friend {
				float:left;
				width:150px;
				margin:0px 0px 10px 0px;
			}
			
			.slide-show-friend a {
				text-decoration:none;
			}
			
			.slide-show-friend img {
				border:1px solid #dcdcdc;
				vertical-align:middle;
			}
			
			.new-images {
				width:300px;
			}
			
			.new-images h1 {
				color:#333333;
				margin:0px 0px 10px 0px !important;
				font-size:14px;
				padding:0px 0px 3px 0px;
				font-weight:bold;
			}
			
			.new-image {
				float:left;
				width:30px;
				margin:0px 6px 6px 0px;
			}
			
			.new-image img {
				padding:1px;
				border:1px solid #dcdcdc;
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
			
					
					function loadImage (current_page, user, direction) {
						
						new Ajax.Updater(
							'user-image-container', 'index.php?title=index.php&action=ajax&rs=wfSlideShow&rsargs[]='+current_page+'&rsargs[]='+user+'&rsargs[]='+direction,
							
							{
							   method:'get',
							   onSuccess: function (t) {
									
										current_page++;
								}
							}
							
							);
						
					}
			/*]]>*/</script>
		";
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		$sql_total = "SELECT count(*) as count FROM image INNER JOIN
		categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user_name)."' 
		AND cl_to = 'Profile_Pictures'
		ORDER BY img_timestamp DESC {$limit_sql}";
	
	    $res_total = $dbr->query($sql_total);
		$row = $dbr->fetchObject($res_total);
		$total = $row->count;
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT img_name, img_user, img_width, img_user_text, img_timestamp FROM image
		INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user_name)."' AND cl_to = 'Profile_Pictures' ORDER BY img_timestamp DESC LIMIT {$picture_number},1";
		
		$sql_preload = "SELECT img_name, img_user, img_width, img_user_text, img_timestamp FROM image 
		INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text = '".addslashes($user_name)."' AND cl_to = 'Profile_Pictures' ORDER BY img_timestamp DESC LIMIT ".($picture_number+1).",3";
		
		$sql_friend = "SELECT r_user_name, r_user_name_relation, r_type, r_date, r_user_id_relation FROM user_relationship WHERE r_user_name = '".addslashes($user_name)."' and r_type = 1 ORDER BY RAND() LIMIT 8";
		
		$sql_friend_count = "SELECT count(*) as count FROM user_relationship WHERE r_user_name = '".addslashes($user_name)."' and r_type = 1";
		$res_friend_total = $dbr->query($sql_friend_count);
		$row_friend_total = $dbr->fetchObject($res_friend_total);
		$friend_total = $row_friend_total->count;
		
		$sql_new_images = "SELECT img_name, img_timestamp, (img_width / img_height) as img_ratio, img_user_text FROM image 
		INNER JOIN categorylinks on replace(cl_sortkey,' ','_')=concat('Image:',img_name)
		WHERE img_user_text <> '".addslashes($user_name)."'  AND cl_to = 'Profile_Pictures' AND (img_width / img_height > 1) and (img_width / img_height < 5) ORDER BY img_timestamp DESC LIMIT 0,24";
		
		$res1 = $dbr->query($sql_preload);
		$res_friend = $dbr->query($sql_friend);
		$res_new_images = $dbr->query($sql_new_images);
		$res = $dbr->query($sql);
		
		//Reset Navigation Links
		if ($next==$total) {
			$next=0;
		}
		if ($next==1) {
			$previous = ($total-1);
		}
		
		//Top Stuff
		
		$output .= "<div class=\"slide-show-top\">
			<a href=\"index.php?title=User:{$user_name}\">Back to Profile</a> -
			<a href=\"index.php?title=Special:UserImageList&user={$user_name}\">See All Photos</a>
		</div>";
		
		
		//Loop Through Images
		
		if ($total) {
			
		
			while ($row = $dbr->fetchObject( $res ) ) {
				$image_path = $row->img_name;
				$render_image = Image::newFromName ($image_path);
				$thumb_image = $render_image->getThumbNail(600,0,true);
				$thumbnail = $thumb_image->toHtml( array("id"=>"user-image", "onmouseover"=>"doHover('user-image')", "onmouseout"=>"endHover('user-image')") );
				$picture_counter = $picture_number + 1;


				$output .= "
				<div class=\"user-image-container\" id=\"user-image-container\">
					<div class=\"user-image\">
						<p>
							Photo {$picture_counter} of $total
						</p>
						<p>
							<a href=\"javascript:loadImage('{$picture_number}', '{$user_name}', 'next');\">{$thumbnail}</a>
						</p>
					</div>";

					//Bottom Navigation Links

					$output .= "
							<div class=\"slide-show-bottom\">
								<ul>
									<li><a href=\"javascript:loadImage('{$picture_number}', '{$user_name}', 'previous');\">Previous</a></i> 
									<li><a href=\"javascript:loadImage('{$picture_number}', '{$user_name}', 'next');\">Next</a></li>
								</ul> 
							</div>
					";

					//preload images
					$output .= "<div style=\"display:none\">";

					while ($row1 = $dbr->fetchObject($res1)) {
						$image_path_preload = $row1->img_name;
						$render_image_preload = Image::newFromName ($image_path_preload);
						$thumb_image_preload = $render_image_preload->getThumbNail(600,0,true);
						$thumbnail_preload = $thumb_image_preload->toHtml();

						$output .= "<p>{$thumbnail_preload}</p>";

					}

					$output .= "</div>";

				$output .= "</div>";

			}
			
			
		} else {
			$output .= "{$user_name} does not have any images!";
		}
		
		//right hand side
		
		$output .= "<div class=\"slide-show-right\">";
		
		//Friends
		$output .= "<div class=\"slide-show-friends\">
		<h1>Random Friends' Photos</h1>";
		
		//Friends Variables
		$per_row = 2;
		$per_page = 6;
		$x = 1;
		
				while ($row_friend = $dbr->fetchObject($res_friend)) {
					
					$avatar = new wAvatar($row_friend->r_user_id_relation,"s");
					$user_name_friend = $row_friend->r_user_name_relation;
					$user_name_friend = ($row_friend->r_user_name_relation == substr($row_friend->r_user_name_relation, 0, 15) ) ? $row_friend->r_user_name_relation : ( substr($row_friend->r_user_name_relation, 0, 15) . "...");
					
					
					$output .= "<div class=\"slide-show-friend\">
						<img src=images/avatars/{$avatar->getAvatarImage()} style=\"border:1px solid #d7dee8;\"/>
						<a href=\"index.php?title=Special:UserSlideShow&user={$row_friend->r_user_name_relation}&picture=0\">{$user_name_friend}</a>
					</div>";
					if($x!=1 && $x%$per_row ==0) {
						$output .= "<div class=\"cleared\"></div>";
					}
					$x++;
				}
		
				
				$output .= "<div class=\"cleared\"></div>";
				
				//Page Nav
			
				//$numofpages = $friend_total / $per_page; 
		
				//if ($numofpages>1) {
					//$output .= "<div class=\"page-nav\">";
					
					//if ($page > 1) { 
						//$output .= "<a href=\"index.php?title=Special:UserImageList&user={$user_name}&page=" . ($page-1) . "\">prev</a> ";
					//}
			
			
					//if(($friend_total % $per_page) != 0)$numofpages++;
					//if($numofpages >=9)$numofpages=9+$page;
			
					//for($i = 1; $i <= $numofpages; $i++){
						//if($i == $page){
				    		//$output .=($i." ");
						//}else{
				    		//$output .="<a href=\"javascript:morefriends();\">$i</a> ";
						//}
					//}
	
					//if(($friend_total - ($per_page * $page)) > 0){
						//$output .=" <a href=\"index.php?title=Special:UserImageList&user={$user_name}&page=" . ($page+1) . "\">next</a>"; 
					//}
					//$output .= "</div>";
				//}
				
				$output .= "</div>";
				
		
		//new images 
		
		$per_row = 6;
		$x = 1;
		
		$output .= "<div class=\"new-images\">
			<h1>New Images</h1>";
			
			while ($row_new_images = $dbr->fetchObject( $res_new_images ) ) {
				
				$new_image_path = $row_new_images->img_name;
				$new_image_ratio = $row_new_images->img_ratio;
				$render_new_image = Image::newFromName ($new_image_path);
				$thumb_new_image = $render_new_image->getThumbNail(30,0,true);
				$thumbnail_new = $thumb_new_image->toHtml(array("id"=>"user-image-{$x}", "onmouseover"=>"doHover('user-image-{$x}')", "onmouseout"=>"endHover('user-image-{$x}')"));

				$output .= "<div class=\"new-image\"><a href=\"index.php?title=Image:{$new_image_path}\">{$thumbnail_new}</a></div>";
				
				if($x!=1 && $x%$per_row ==0) {
					$output .= "<div class=\"cleared\"></div>";
				}
				$x++;
				
			}
			
			
		$output .= "</div>";
		
		$output .= "</div>";
		
		$output .= "<div class=\"cleared\"></div>";
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new UserSlideShow );

 


}

?>