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
		$previous = $picture_number - 1;
		$next = $picture_number +1;
		
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
		</style>";
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		$sql_total = "SELECT count(*) as count FROM image WHERE img_user_text = '{$user_name}'";
	    $res_total = $dbr->query($sql_total);
		$row = $dbr->fetchObject($res_total);
		$total = $row->count;
		
		//database calls
		$dbr =& wfGetDB( DB_MASTER );
		
		$sql = "SELECT img_name, img_user, img_width, img_user_text, img_timestamp FROM image WHERE img_user_text = '{$user_name}' ORDER BY img_timestamp DESC LIMIT {$picture_number},1";
		
		$sql_preload = "SELECT img_name, img_user, img_width, img_user_text, img_timestamp FROM image WHERE img_user_text = '{$user_name}'ORDER BY img_timestamp DESC LIMIT ".($picture_number+1).",3";
		
		//$res1 = $dbr->query($sql_preload);
		$res = $dbr->query($sql);
		
		//set javascript
		$output .= "
			<script language = \"javascript\">/*<![CDATA[*/
					
					var cur_page = 1;
					
					function loadImage (user, direction) {
						
						new Ajax.Updater(
							'user-image-container', 'index.php?title=index.php&action=ajax&rs=wfSlideShow&rsargs[]='+(cur_page)+'&rsargs[]='+user,
							
							{
							   method:'get',
							   onSuccess: function (t) {
										cur_page++;
										alert (cur_page);
								}
							}
							
							);
						
					}
			
			/*]]>*/</script>
		";
		
		
		//Top Stuff
		
		$output .= "<div class=\"slide-show-top\">
			<a href=\"index.php?title=User:{$user_name}\">Back to Profile</a> -
			<a href=\"index.php?title=Special:UserImageList&user={$user_name}\">See All Photos</a>
		</div>";
		
		
		//Loop Through Images
		
		while ($row = $dbr->fetchObject( $res ) ) {
			$image_path = $row->img_name;
			$render_image = Image::newFromName ($image_path);
			$thumb_image = $render_image->getThumbNail(600,0,true);
			$thumbnail = $thumb_image->toHtml();
			$picture_counter = $picture_number + 1;
			
			
			$output .= "<div class=\"user-image-container\" id=\"user-image-container\">
				<div class=\"user-image\" id=\"user-image\">
					<p>
						Photo {$picture_counter} of $total 
					</p>
					<p>
						<a href=\"javascript:loadImage('{$user_name}', 1)\">{$thumbnail}</a>
					</p>
				</div>";
				
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
		
		//Bottom Navigation Links
		
		
		$output .= "
				<div class=\"slide-show-bottom\">
					<ul>
						<li><a href=\"javascript:loadImage('{$user_name}', -1)\">Previous</a></i> 
						<li><a href=\"javascript:loadImage('{$user_name}', 1)\">Next</a></li>
					</ul> 
				</div>
		";
		
		
		
		$wgOut->addHTML($output);
	
	}
  
 
	
}

SpecialPage::addPage( new UserSlideShow );

 


}

?>