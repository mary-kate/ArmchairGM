<?php

$wgExtensionFunctions[] = 'wfSpecialPictureGameHome';

function wfSpecialPictureGameHome(){

	global $wgUser,$IP;

	include_once("AjaxUploadForm.php");	// The modified upload form class

	class PictureGameHome extends SpecialPage {
		// ABSOLUTE PATH
		private $INCLUDEPATH = "/extensions/wikia/Ashish/picturegame/";
		private $SALT;

		/* Construct the MediaWiki special page */
		function PictureGameHome(){
			UnlistedSpecialPage::UnlistedSpecialPage("PictureGameHome");
		}

		/* Gets the mini display for the home page */
		function getMiniGame(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$game_home = Title::makeTitle(NS_SPECIAL,"PictureGameHome");
			
			$output = "";

			$dbr =& wfGetDB( DB_MASTER );
			$order = ( (time() % 2 == 0) ? "ASC" : "DESC" );
			$sql = "SELECT * FROM picturegame_images WHERE picturegame_images.id NOT IN (SELECT picid FROM picturegame_votes WHERE picturegame_votes.username='" . addslashes($wgUser->getName()) . "') AND flag != 'FLAGGED' ORDER BY title {$order} LIMIT 1;";

			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			$imgID = $row->id;

			if( ( $imgID < 0 ) || !is_numeric( $imgID )){
				$nothing = "<p>There are no new picture games to play. <a href=\"{$game_home->escapeLocalURL()}\">Create one!</a></p>";
				return $nothing;
			}

			$title_text = ($row->title == substr($row->title, 0, 48) ) ? $row->title : ( substr($row->title, 0, 48) . "...");
			$img1_caption_text = ($row->img1_caption == substr($row->img1_caption, 0, 18) ) ?
								 $row->img1_caption : ( substr($row->img1_caption, 0, 18) . "...");
			$img2_caption_text = ($row->img2_caption == substr($row->img2_caption, 0, 18) ) ?
												 $row->img2_caption : ( substr($row->img2_caption, 0, 18) . "...");

			// I assume MediaWiki does some caching with these functions?
			$img_one = Image::newFromName( $row->img1 );
			$thumb_one_url = $img_one->createThumb(128);
			$imgOne = '<img width="' . ($img_one->getWidth() >= 128 ? 128 : $img_one->getWidth()) . '" alt="" src="' . $thumb_one_url . '?' . time() . '"/>';

			$img_two = Image::newFromName( $row->img2 );
			$thumb_two_url = $img_two->createThumb(128);
			$imgTwo = '<img width="' . ($img_two->getWidth() >= 128 ? 128 : $img_two->getWidth()) . '" alt="" src="' . $thumb_two_url . '?' . time() . '"/>';

			$key = md5($imgID . $this->SALT);

			$output = "<div class=\"picgame-content\">
					<div class=\"picgame-title\">{$title_text}</div>
					<div class=\"picgame-image-one\">
						<div class=\"picgame-image-one-title\">{$img1_caption_text}</div>
						<div class=\"picgame-image-one-html\" onmouseout=\"$(this).setStyle({backgroundColor: ''});\" onmouseover=\"$(this).setStyle({backgroundColor: '#4B9AF6'});\"><a href=\"" . $game_home->escapeLocalURL("picGameAction=renderPermalink&id={$imgID}&voteID={$imgID}&voteImage=1&key={$key}") . "\">{$imgOne}</a></div>
					</div>
					<div class=\"picgame-image-two\">
						<div class=\"picgame-image-two-title\">{$img2_caption_text}</div>
						<div class=\"picgame-image-two-html\" onmouseout=\"$(this).setStyle({backgroundColor: ''});\" onmouseover=\"$(this).setStyle({backgroundColor: '#FF0000'});\"><a href=\"" . $game_home->escapeLocalURL("picGameAction=renderPermalink&id={$imgID}&voteID={$imgID}&voteImage=2&key={$key}") . "\">{$imgTwo}</a></div>
					</div>
					<div class=\"cleared\"></div>
					<p>To play, click the one you prefer.</p>
			</div>";


			return $output;
		}

		/* the main functino that handles browser requests" */
		function execute(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			// Salt as you like
			$this->SALT = md5( $wgUser->getName() );

			$action = $wgRequest->getVal("picGameAction");

			switch($action){

			case "uploadForm":
				$wgOut->setArticleBodyOnly(true);
				$form = new AjaxUploadForm($wgRequest);
				$form->execute();
				break;
			case "startGame":
				$this->renderPictureGame();
				break;
			case "createGame":
				$this->createGame();
				break;
			case "castVote":
				$this->voteAndForward();
				break;
			case "flagImage":
				$this->flagImage();
				break;
			case "renderPermalink":
				$this->renderPictureGame();
				break;
			case "gallery":
				$this->displayGallery();
				break;
			case "editPanel":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->editPanel();
				else
					$this->showHomePage();
				break;
			case "completeEdit":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->completeEdit();
				else
					$this->showHomePage();
				break;
			case "adminPanel":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->adminPanel();
				else
					$this->showHomePage();
				break;
			case "adminPanelUnflag":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->adminPanelUnflag();
				else
					$this->showHomePage();
				break;
			case "adminPanelDelete":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->adminPanelDelete();
				else
					$this->showHomePage();
				break;
			case "protectImages":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->protectImages();
				else
					print "You aren't authorized to do that.";
				break;
			case "unprotectImages":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete")  )
					$this->unprotectImages();
				else
					$this->showHomePage();
				break;
			default:
				$this->renderPictureGame();
				break;
			}

		}

		/* Called via AJAX to delete an image out of the game */
		function adminPanelDelete(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setArticleBodyOnly(true);

			$id =  addslashes( $wgRequest->getVal("id") );
			$image1 =  addslashes( $wgRequest->getVal("img1") );
			$image2 =  addslashes( $wgRequest->getVal("img2") );

			$key = $wgRequest->getVal("key");
			$now = $wgRequest->getVal("chain");

			if($key != md5($now . $this->SALT) || (!$wgUser->isLoggedIn() || !$wgUser->isAllowed("delete")  ) ){
				print "Fatal Error: You key is bad.";
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "DELETE FROM picturegame_images WHERE id=" . $id . ";";
			$res = $dbr->query($sql);

			/* Pop the images out of MediaWiki also */
			$img_one = Image::newFromName( $image1 );
			$img_two = Image::newFromName( $image2 );
			$oneResult = $img_one->delete("Picture Game: Admin Delete");
			$twoResult = $img_two->delete("Picture Game: Admin Delete");

			if($oneResult && $twoResult){
				print "You have successfully delete this picture game!";
				return;
			}

			if($oneResult)
				print "Deleting {$image1} from MediaWiki failed!";

			if($twoResult)
				print "Deleting {$image2} from MediaWiki failed!";
		}

		/* Called over AJAX to unflag an image */
		function adminPanelUnflag(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setArticleBodyOnly(true);

			$id =  addslashes( $wgRequest->getVal("id") );

			$key = $wgRequest->getVal("key");
			$now = $wgRequest->getVal("chain");

			if($key != md5($now . $this->SALT) || (!$wgUser->isLoggedIn() || !$wgUser->isAllowed("delete") ) ){
				print "Fatal Error: You key is bad.";
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE picturegame_images SET flag='NONE' WHERE id=" . $id . ";";
			$res = $dbr->query($sql);

			$wgOut->clearHTML();
			print "You have placed these images back into circulation.";
		}

		/* Updates a record in the picture game table */
		function completeEdit(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$id =  addslashes( $wgRequest->getVal("id") );
			$key =  addslashes( $wgRequest->getVal("key") );

			$title =  addslashes( $wgRequest->getVal("newTitle") );
			$imgOneCaption =  addslashes( $wgRequest->getVal("imgOneCaption") );
			$imgTwoCaption =  addslashes( $wgRequest->getVal("imgTwoCaption") );

			$imageOneName = addslashes( $wgRequest->getVal("imageOneName") );
			$imageTwoName = addslashes( $wgRequest->getVal("imageTwoName") );
			
			if($key != md5($id . $this->SALT) ){
				$wgOut->addHTML("<h3> Your key is bad! Go back and try again? </h3>");
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE picturegame_images SET title='{$title}', img1='{$imageOneName}', img2='{$imageTwoName}', img1_caption='{$imgOneCaption}', img2_caption='{$imgTwoCaption}' WHERE id={$id};";
			$res = $dbr->query($sql);

			/* When its done redirect to a permalink of these images "*/
			$wgOut->setArticleBodyOnly(true);
			header( 'Location: index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id=' . $id ) ;
		}

		/* Displays the edit panel */
		function editPanel(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP, $wgStyleVersion;

			$id =  addslashes( $wgRequest->getVal("id") );

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT * FROM picturegame_images WHERE id={$id};";
			$res = $dbr->query($sql);

			$row = $dbr->fetchObject( $res );

			$imgID = $row->id;
			$user_name = ($row->username == substr($row->username, 0, 20) ) ?
								 $row->username : ( substr($row->username, 0, 20) . "...");

			$title_text = $row->title;
			$img1_caption_text = $row->img1_caption;
			$img2_caption_text = $row->img2_caption;

			// I assume MediaWiki does some caching with these functions?
			$img_one = Image::newFromName( $row->img1 );
			$thumb_one_url = $img_one->createThumb(128);
			$imgOne =  '<img width="' . ($img_one->getWidth() >= 128 ? 128 : $img_one->getWidth()) . '" alt="" src="' . $thumb_one_url . '?' . time() . '"/>';
			$imgOneName = $row->img1;

			$img_two = Image::newFromName( $row->img2 );
			$thumb_two_url = $img_two->createThumb(128);
			$imgTwo =  '<img width="' . ($img_two->getWidth() >= 128 ? 128 : $img_two->getWidth()) . '" alt="" src="' . $thumb_two_url . '?' . time() . '"/>';
			$imgTwoName = $row->img2;

			$output = "

			<script language=\"javascript\">
			
			var currImg = 0;

			/* Shows the upload frame*/
			function loadUploadFrame(filename, img){

				currImg = img;

				if(img == 1)
					\$(\"edit-image-text\").innerHTML = \"<h2> Editing Image 1 </h2>\";
				else
					\$(\"edit-image-text\").innerHTML = \"<h2> Editing Image 2 </h2>\";

				\$(\"upload-frame\").src = \"{$wgRequest->getRequestURL()}&picGameAction=uploadForm&wpOverwriteFile=true&wpDestFile=\" + filename;
			}

			
			function uploadError(message){
				$(\"loadingImg\").hide();
				alert(message);
				
				$(\"edit-image-frame\").show();
				\$(\"upload-frame\").src = \$(\"upload-frame\").src;
			}

			/* Called when the upload starts */
			function completeImageUpload(){
				$(\"edit-image-frame\").hide();
				$(\"loadingImg\").show();
			}

			/* Called when the upload is complete
				imgSrc will be HTML for the image thumbnail
				imgName is the MediaWiki image name
				imgDesc is the MediaWiki image descriptions
			*/
			function uploadComplete(imgSrc, imgName, imgDesc){
				
				$(\"loadingImg\").hide();
					
				if(currImg == 1){
					$(\"image-one-tag\").innerHTML = imgSrc;
					$(\"imageOneName\").value = imgName;
				}else{
					$(\"image-two-tag\").innerHTML = imgSrc;
					$(\"imageTwoName\").value = imgName;
				}
				
			}

			</script>";



			$wgOut->setpageTitle("Editing {$title_text}");

			$id=User::idFromName($row->username);
			$avatar = new wAvatar($id,"l");
			$avatarID = $avatar->getAvatarImage();
			$stats = new UserStats($id, $row->username);
			$stats_data = $stats->getUserStats();

			$output .= "

			<style type=\"text/css\">@import \"{$this->INCLUDEPATH}editpanel.css?{$wgStyleVersion}\";</style>

			<div id=\"edit-container\" class=\"edit-container\">
				<div id=\"edit-textboxes\" class=\"edit-textboxes\">

					<div class=\"credit-box-edit\" id=\"creditBox\">
						<h1>Submitted By</h1>
						<div class=\"submitted-by-image\">
							<a href=\"index.php?title=User:{$row->username}\"><img src=images/avatars/{$avatarID} style=\"border:1px solid #d7dee8; width:50px; height:50px;\"/></a>
						</div>
						<div class=\"submitted-by-user\">
							<a href=\"index.php?title=User:{$row->username}\">{$user_name}</a>
							<ul>
								<li>
									<img src=\"images/common/voteIcon.gif\" border=\"0\"> {$stats_data["votes"]}
								</li>
								<li>
									<img src=\"images/common/pencilIcon.gif\" border=\"0\"> {$stats_data["edits"]}
								</li>
								<li>
									<img src=\"images/common/commentsIcon.gif\" border=\"0\"> {$stats_data["comments"]}
								</li>
							</ul>
						</div>
						<div class=\"cleared\"></div>
					</div>



					<form id=\"picGameVote\" name=\"picGameVote\" method=\"post\" action=\"index.php?title=Special:PictureGameHome&picGameAction=completeEdit\">
						<h1>Title</h1>
						<p><input name=\"newTitle\" id=\"newTitle\" type=\"text\" value=\"{$title_text}\" size=\"40\"/></p>
						<input id=\"key\" name=\"key\" type=\"hidden\" value=\"" . md5($imgID . $this->SALT) . "\" />
						<input id=\"id\" name=\"id\" type=\"hidden\" value=\"{$imgID}\" />
						<input id=\"imageOneName\" name=\"imageOneName\" type=\"hidden\" value=\"{$row->img1}\" />
						<input id=\"imageTwoName\" name=\"imageTwoName\" type=\"hidden\" value=\"{$row->img2}\" />

				</div>
				<div class=\"edit-images-container\">
					<div id=\"edit-images\" class=\"edit-images\">
						<div id=\"edit-image-one\" class=\"edit-image-one\">
							<h1>First Image</h1>
							<p><input name=\"imgOneCaption\" id=\"imgOneCaption\" type=\"text\" value=\"{$img1_caption_text}\" /></p>
							<p id=\"image-one-tag\">{$imgOne}</p>
							<p><a href=\"javascript:loadUploadFrame('{$imgOneName}', 1)\">Upload New Image</a></p>
						</div>

						<div id=\"edit-image-two\" class=\"edit-image-one\">
							<h1>Second Image</h1>
							<p><input name=\"imgTwoCaption\" id=\"imgTwoCaption\" type=\"text\" value=\"{$img2_caption_text}\" /></p>
							<p id=\"image-two-tag\">{$imgTwo}</p>
							<p><a href=\"javascript:loadUploadFrame('{$imgTwoName}', 2)\">Upload New Image</a></p>
						</div>

						<div id=\"loadingImg\" class=\"loadingImg\" style=\"display:none\">
							<img src=\"../../images/common/ajax-loader-white.gif\" />
						</div>

						<div class=\"cleared\"></div>

					</div>

					<div class=\"edit-image-frame\" id=\"edit-image-frame\" style=\"display:hidden\">
						<div class=\"edit-image-text\" id=\"edit-image-text\"> </div>
						<iframe frameBorder=\"0\" scrollbar=\"no\" class=\"upload-frame\" id=\"upload-frame\" src=\"\"></iframe>
					</div>

					<div class=\"cleared\"></div>
				</div>

				<div class=\"copyright-warning\">"
					. wfMsg("copyrightwarning") .
				"</div>

				<div id=\"complete-buttons\" class=\"complete-buttons\">
					<input type=\"button\" onclick=\"document.picGameVote.submit()\" value=\"Submit\"/>
					<input type=\"button\"  onclick=\"window.location='index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id={$imgID}'\" value=\"Cancel\"/>
				</div>
				</form>
			</div>
			";
			//"
			$dbr->freeResult( $res );
			$wgOut->addHTML($output);
		}

		/* Displays the admin panel */
		function adminPanel(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$now = time();
			$key = md5($now . $this->SALT);

			$output = "
				<script langauge=\"javascript\">

				/* Unflags an image */
				function unflag(id){
					Effect.Fade('' + id + '');

					new Ajax.Request('index.php?title=Special:PictureGameHome&picGameAction=adminPanelUnflag&chain=" . $now . "&key=" .  $key . "&id=' + id + '',
							{onSuccess:	function(t){	alert(t.responseText);	},
							onFailure: 	function(t) { alert('Error was: ' + t.responseText); }
							});
				}

				/* Deletes the image:
					img1 and img2 are the MediaWiki names */
				function deleteimg(id, img1, img2){
					Effect.Fade('' + id + '');

					new Ajax.Request('index.php?title=Special:PictureGameHome&picGameAction=adminPanelDelete&chain=" . $now . "&key=" .  $key . "&id=' + id + '&img1=' + img1 + '&img2=' + img2,
							{onSuccess:	function(t){	alert(t.responseText);	},
							onFailure: 	function(t) { alert('Error was: ' + t.responseText); }
							});

				}

				/* Unprotects an image */
				function unprotect(id){
					Effect.Fade('' + id + '');
					new Ajax.Request('index.php?title=Special:PictureGameHome&picGameAction=unprotectImages&chain=" . $now . "&key=" .  md5($now, $this->SALT) . "&id=' + id + '',
							{onSuccess:	function(t){	alert(t.responseText);	},
							onFailure: 	function(t) { alert('Error was: ' + t.responseText); }
							});
				}

				</script>";

			$wgOut->setPagetitle("Picture Game Admin Panel");

			$output .= "

			<style type=\"text/css\">@import \"{$this->INCLUDEPATH}adminpanel.css\";</style>

			<div class=\"back-link\"><a href=\"index.php?title=Special:PictureGameHome&picGameAction=startGame\"> < Back to the Picture Game</a></div>


			<div id=\"admin-container\" class=\"admin-container\">
				<p><strong>Flagged Images:</strong></p>";

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT id, img1, img2 FROM picturegame_images WHERE flag='FLAGGED';";
			$res = $dbr->query($sql);

			while ( $row = $dbr->fetchObject( $res ) ){

				$img_one = Image::newFromName( $row->img1 );
				$thumb_one = $img_one->getThumbnail( 128, 0, true );
				$img_one_tag = $thumb_one->toHtml();

				$img_two = Image::newFromName( $row->img2 );
				$thumb_two = $img_two->getThumbnail( 128, 0, true );
				$img_two_tag = $thumb_two->toHtml();

				$img_one_description = ($row->img1 == substr($row->img1, 0, 12) ) ?
									 $row->img1 : ( substr($row->img1, 0, 12) . "...");

				$img_two_description = ($row->img2 == substr($row->img2, 0, 12) ) ?
													 $row->img2 : ( substr($row->img2, 0, 12) . "...");

				$output .= "<div id=\"" . $row->id . "\" class=\"admin-row\">

					<div class=\"admin-image\">
						<p>{$img_one_tag}</p>
						<p><b>{$img_one_description}</b></p>
					</div>
					<div class=\"admin-image\">
						<p>{$img_two_tag}</p>
						<p><b>{$img_two_description}</b></p>
					</div>
					<div class=\"admin-controls\">
						<a href=\"javascript:unflag({$row->id})\">Reinstate</a> |
						<a href=\"javascript:deleteimg(" . $row->id . ", '" . $row->img1 . "', '" . $row->img2 . "')\">Delete</a>
					</div>
					<div class=\"cleared\"></div>

				</div>";
			}

			$output .= "</div>
			<div id=\"admin-container\" class=\"admin-container\">
				<p><strong>Protected Images:</strong></p>";

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT id, img1, img2 FROM picturegame_images WHERE flag='PROTECT';";
			$res = $dbr->query($sql);

			while ( $row = $dbr->fetchObject( $res ) ){

				$img_one = Image::newFromName( $row->img1 );
				$thumb_one = $img_one->getThumbnail( 128, 0, true );
				$img_one_tag = $thumb_one->toHtml();

				$img_two = Image::newFromName( $row->img2 );
				$thumb_two = $img_two->getThumbnail( 128, 0, true );
				$img_two_tag = $thumb_two->toHtml();

				$img_one_description = ($row->img1 == substr($row->img1, 0, 12) ) ?
									 $row->img1 : ( substr($row->img1, 0, 12) . "...");

				$img_two_description = ($row->img2 == substr($row->img2, 0, 12) ) ?
													 $row->img2 : ( substr($row->img2, 0, 12) . "...");

				$output .= "<div id=\"" . $row->id . "\" class=\"admin-row\">

					<div class=\"admin-image\">
						<p>{$img_one_tag}</p>
						<p><b>{$img_one_description}</b></p>
					</div>
					<div class=\"admin-image\">
						<p>{$img_two_tag}</p>
						<p><b>{$img_two_description}</b></p>
					</div>
					<div class=\"admin-controls\">
						<a href=\"javascript:unprotect({$row->id})\">Unprotect</a> |
						<a href=\"javascript:deleteimg(" . $row->id . ", '" . $row->img1 . "', '" . $row->img2 . "')\">Delete</a>
					</div>
					<div class=\"cleared\"></div>

				</div>";
			}

			$output .= "</div>";

			// "
			$dbr->freeResult( $res );
			$wgOut->addHTML($output);
		}

		/* Called with AJAX to flag an image */
		function flagImage() {
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setArticleBodyOnly(true);

			$id =  addslashes( $wgRequest->getVal("id") );
			$key = $wgRequest->getVal("key");


			if($key != md5($id . $this->SALT) ){
				print "Fatal Error: You key is bad.";
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE picturegame_images SET flag='FLAGGED' WHERE id=" . $id . " AND flag='NONE';";
			$res = $dbr->query($sql);

			$wgOut->clearHTML();
			print "<div style=\"color:red; font-weight:bold; font-size:16px; margin:-5px 0px 20px 0px;\">The images have been reported!</div>";
		}

		/* Called with AJAX to unprotect an image set "*/
		function unprotectImages(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setArticleBodyOnly(true);

			$id =  addslashes( $wgRequest->getVal("id") );
			$key = $wgRequest->getVal("key");
			$chain = $wgRequest->getVal("chain");

			if($key != md5($chain . $this->SALT) ){
				print "Fatal Error: You key is bad.";
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE picturegame_images SET flag='NONE' WHERE id=" . $id . ";";
			$res = $dbr->query($sql);

			$wgOut->clearHTML();
			print "The images have been un-protected!";
		}

		/* Protects an image set */
		function protectImages(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setArticleBodyOnly(true);

			$id =  addslashes( $wgRequest->getVal("id") );
			$key = $wgRequest->getVal("key");


			if($key != md5($id . $this->SALT) ){
				print "Fatal Error: You key is bad.";
				return;
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE picturegame_images SET flag='PROTECT' WHERE id=" . $id . ";";
			$res = $dbr->query($sql);

			$wgOut->clearHTML();
			print "The images have been protected!";
		}

		function displayGallery(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;

			$wgOut->setHTMLTitle( wfMsg( 'pagetitle', "Gallery" ) );

			$type = $wgRequest->getVal("type");
			$direction = $wgRequest->getVal("direction");
			
			if (($type == "heat") && ($direction=="most")) {
				$crit = "Heat";
				$noun = "Heat";
				$order = "ASC";
				$adj = "Most";
			} 
			
			else if (($type == "heat") && ($direction=="least")) {
				$crit = "Heat";
				$noun = "Heat";
				$order = "DESC";
				$adj = "Least";
			}
			
			else if (($type == "votes") && ($direction=="most")) {
				$crit = "(img0_votes+img1_votes)";
				$noun = "Votes";
				$order = "DESC";
				$adj = "Most";
			}
			
			else if (($type == "votes") && ($direction=="least")) {
				$crit = "(img0_votes+img1_votes)";
				$noun = "Votes";
				$order = "ASC";
				$adj = "Least";
			}

			else {
				$type = "heat";
				$direction = "most";
				
				$crit = "Heat";
				$noun = "Heat";
				$order = "ASC";
				$adj = "Most";
			}

			$sortheader = "Picture Games Sorted By {$adj} {$noun}";

			$wgOut->setPageTitle ("$sortheader");

			$output = "
				<style type=\"text/css\">@import \"{$this->INCLUDEPATH}gallery.css\";</style>
					<div class=\"picgame-gallery-navigtion\">";

					if ($type == "votes" && $direction == "most") {

						$output .= "<h1>Most</h1>
						<p><b>Most Votes</b></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=most\">Most Heat</a></p>
						
						<h1 style=\"margin:10px 0px !important;\">Least</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=least\">Least Votes</a></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=least\">Least Heat</a></p>";
						
					}

					if ($type == "votes" && $direction == "least") {

						$output .= "<h1>Most</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=most\">Most Votes</a></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=most\">Most Heat</a></p>
						
						<h1 style=\"margin:10px 0px !important;\">Least</h1>
						<p><b>Least Votes</b></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=least\">Least Heat</a></p>";
					
					}

					if ($type == "heat" && $direction == "most") {

						$output .= "<h1>Most</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=most\">Most Votes</a></p>
						<p><b>Most Heat</b></p>
						
						<h1 style=\"margin:10px 0px !important;\">Least</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=least\">Least Votes</a></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=least\">Least Heat</a></p>";
						
					}

					if ($type == "heat" && $direction == "least") {

						$output .= "<h1>Most</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=most\">Most Votes</a></p>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=heat&direction=most\">Most Heat</a></p>
						
						<h1 style=\"margin:10px 0px !important;\">Least</h1>
						<p><a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&type=votes&direction=least\">Least Votes</a></p>
						<p><b>Least Heat</b></p>";
						
					}


			$output .= "</div>";

			$output .= "<div class=\"picgame-gallery-container\" id=\"picgame-gallery-thumbnails\">";

			$per_row = 3;
			$x = 1;

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT COUNT(*) as mycount FROM picturegame_images WHERE 1;";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );

			//page nav variables
			$total = $row->mycount;
			$page =  addslashes( $wgRequest->getVal("page") );

			if(!$page)
				$page = 1;

			//Add Limit to SQL
			$per_page = 9;
			$limit = $per_page;

			$limitvalue = 0;
			if ($limit > 0) {
					if($page)
						$limitvalue = $page * $limit - ($limit);
					$limit_sql = " LIMIT {$limitvalue},{$limit} ";
			}

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT * FROM picturegame_images WHERE flag!='Flagged' ORDER BY {$crit} {$order} {$limit_sql}";
			$res = $dbr->query($sql);

			$preloadImages = array();

			$output .= "<script>
			
			function doHover(divID){
				$(divID).setStyle({backgroundColor: '#e5e7ea'});
			}

			function endHover(divID){
				$(divID).setStyle({backgroundColor: ''});
			}
			
			</script>";

			while( $row = $dbr->fetchObject( $res ) ){

				$gameid = $row->id;

				$title_text = ($row->title == substr($row->title, 0, 23) ) ? htmlentities ( $row->title ) : htmlentities ( ( substr($row->title, 0, 23) . "...") );
				
				$imgOneCount = $row->img0_votes;
				$imgTwoCount = $row->img1_votes;
				$totalVotes = $imgOneCount + $imgTwoCount;

				if ($imgOneCount == 0) {
					$imgOnePercent = 0;
				} else {
					$imgOnePercent = floor( $imgOneCount / $totalVotes  * 100 );
				}

				if ($imgTwoCount == 0) {
					$imgTwoPercent = 0;
				} else {
					$imgTwoPercent = 100 - $imgOnePercent;
				}
				
				$img_one = Image::newFromName( $row->img1 );
				$gallery_thumb_image_one = $img_one->getThumbNail(80,0,true);
				$gallery_thumbnail_one = $gallery_thumb_image_one->toHtml();
				
				$img_two = Image::newFromName( $row->img2 );				
				$gallery_thumb_image_two = $img_two->getThumbNail(80,0,true);
				$gallery_thumbnail_two = $gallery_thumb_image_two->toHtml();
				
				$output .= "
				<div class=\"picgame-gallery-thumbnail\" id=\"picgame-gallery-thumbnail-{$x}\" onclick=\"javascript:document.location='index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id={$gameid}'\" onmouseover=\"doHover('picgame-gallery-thumbnail-{$x}')\" onmouseout=\"endHover('picgame-gallery-thumbnail-{$x}')\" >
				<h1>{$title_text} ({$totalVotes})</h1>
					
					<div class=\"picgame-gallery-thumbnailimg\">
						{$gallery_thumbnail_one}
						<p>{$imgOnePercent}%</p>
					</div>     
					
					<div class=\"picgame-gallery-thumbnailimg\">
						{$gallery_thumbnail_two}
						<p>{$imgTwoPercent}%</p>
					</div>
					
					<div class=\"cleared\"></div>
				</div>

				";

				if($x!=1 && $x % $per_row ==0) {
					$output .= "<div class=\"cleared\"></div>";
				}
				$x++;
			}
			
			$output .="</div>";

			//Page Nav

			$numofpages = ceil( $total / $per_page );

			if($numofpages > 1) {

				$output .= "<div class=\"page-nav\">";

				if($page > 1) {
					$output .= "<a <a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&page=" . ($page - 1) . "&type={$type}&direction={$direction}\">prev</a> ";
				}

				for($i = 1; $i <= $numofpages; $i++) {
					if($i == $page) {
					    $output .= ($i . " ");
					} else {
						$output .="<a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&page={$i}&type={$type}&direction={$direction}\">{$i}</a> ";
					}
				}

				if( $page < $numofpages ){
					$output .=" <a href=\"index.php?title=Special:PictureGameHome&picGameAction=gallery&page=" . ($page + 1) . "&type={$type}&direction={$direction}\">next</a>";
				}

				$output .= "</div>";
			}
			
			$wgOut->addHTML($output);
		}

		//"
		// cast a user vote
		// the js takes care of redirecting the page
		function voteAndForward(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$wgOut->setArticleBodyOnly(true);
			
			$key = $wgRequest->getVal("key");
			$next_id = $wgRequest->getVal("nextid");
			$id = addslashes( $wgRequest->getVal("id") );
			$img = addslashes( $wgRequest->getVal("img") );

			$imgnum = ($img == 0) ? 0 : 1;
			
			if($key != md5($id . $this->SALT)){
				$wgOut->addHTML("Your key is wrong. Go back and try again.");
				return;
			}
			
			if( strlen($id) > 0 && strlen($img) > 0 ){
								
				$dbr =& wfGetDB( DB_MASTER );
				
				// check if the user has voted on this allready
				$sql = "SELECT COUNT(*) as mycount FROM picturegame_votes WHERE username='". addslashes($wgUser->getName()) ."' AND picid=" . $id . ";";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );

				// if he hasnt then check if the id exists and then insert the vote
				if($row->mycount == 0){
					$sql = "SELECT COUNT(*) as mycount FROM picturegame_images WHERE id=" . $id . ";";
					$res = $dbr->query($sql);
					$row = $dbr->fetchObject( $res );

					if( $row->mycount == 1 ){
						$sql = "INSERT INTO picturegame_votes (picid, userid, username,imgpicked, vote_date) 
							VALUES(" . $id . ", {$wgUser->getID()}, \"" . addslashes( $wgUser->getName() ) . "\", " . $imgnum . ", \"" . date("Y-m-d H:i:s") . "\") ;";
						$res = $dbr->query($sql);
						// "
						
						$sql = "UPDATE picturegame_images SET img" . $imgnum ."_votes=img" . $imgnum . "_votes+1, 
							heat=ABS( ( img0_votes / ( img0_votes+img1_votes) ) - ( img1_votes / ( img0_votes+img1_votes ) ) ) 
							WHERE id=" . $id . ";";
						$res = $dbr->query($sql);

						$stats = new UserStatsTrack(1,$wgUser->getID(), $wgUser->getName());
						$stats->incStatField("picturegame_vote");
					}
				}	
			}
		
			$output = "OK";
			$wgOut->addHTML( $output );
		}

		// fetches the two images to be voted on
		// optional param lastID is the last image id the user saw
		// imgID is present if rendering a permalink
		function getImageDivs($isPermalink = false, $imgID = -1, $lastID = -1){

			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP, $IMGCONTENT;

			$dbr =& wfGetDB( DB_MASTER );

			// if imgID is -1 then we need some random ids
			if($imgID == -1){
				$order = ( (time() % 2 == 0) ? "ASC" : "DESC" );
				$sql = "SELECT * FROM picturegame_images WHERE picturegame_images.id NOT IN (SELECT picid FROM picturegame_votes WHERE picturegame_votes.username='" . addslashes($wgUser->getName()) . "') AND flag != 'FLAGGED' ORDER BY RAND() LIMIT 1;";

				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );
				$imgID = $row->id;

			}else{
				$sql = "SELECT * FROM picturegame_images WHERE flag!='FLAGGED' AND id=" . $imgID . ";";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );	
			}
			
			$user_title =  Title::makeTitle( NS_USER  , $row->username  );
			if($imgID){
				$sql = "SELECT * FROM picturegame_images WHERE picturegame_images.id <> {$imgID} and  picturegame_images.id NOT IN (SELECT picid FROM picturegame_votes WHERE picturegame_votes.username='" . addslashes($wgUser->getName()) . "') AND flag != 'FLAGGED' ORDER BY RAND() LIMIT 1;";
				$nextres = $dbr->query($sql);
				$nextrow = $dbr->fetchObject( $nextres );
				$next_id = $nextrow->id;
				
				if($next_id){
					
					$img_one = Image::newFromName( $nextrow->img1 );
					$preload_thumb = $img_one->getThumbnail( 256, 0, true );
					$preload_one_tag = $preload_thumb->toHtml();
					
					$img_two = Image::newFromName( $nextrow->img2 );
					$preload_thumb = $img_two->getThumbnail( 256, 0, true );
					$preload_two_tag = $preload_thumb->toHtml();
					
					$preload = $preload_one_tag . $preload_two_tag;
				}
				
			}
			
			if( ( $imgID < 0 ) || !is_numeric( $imgID ) || is_null($row) ){

				$wgOut->setPagetitle("No More Picture Games!");

				$out = "<p>
					You have played every picture game!<br>Don't get sad,
					<a href=\"index.php?title=Special:PictureGameHome\">create your very own!</a>
				</p>
				<p>
					<input type=\"button\" value=\"Main Page\" class=\"site-button\" onclick=\"window.location='index.php?title=Main_Page'\"/>
				</p>";

				$wgOut->addHTML($out);
				return;
			}
			//"
			// snag the images to vote on and grab some thumbnails
			// modify this query so that if the current user has voted on this image pair dont show it again
			
			$imgOneCount = $row->img0_votes;
			$imgTwoCount = $row->img1_votes;

			$user_name = ($row->username == substr($row->username, 0, 20) ) ?
								 $row->username : ( substr($row->username, 0, 20) . "...");

			$title_text = ($row->title == substr($row->title, 0, 48) ) ? $row->title : ( substr($row->title, 0, 48) . "...");
			$img1_caption_text = ($row->img1_caption == substr($row->img1_caption, 0, 24) ) ?
								 $row->img1_caption : ( substr($row->img1_caption, 0, 24) . "...");
			$img2_caption_text = ($row->img2_caption == substr($row->img2_caption, 0, 24) ) ?
												 $row->img2_caption : ( substr($row->img2_caption, 0, 24) . "...");

			// I assume MediaWiki does some caching with these functions"
			$img_one = Image::newFromName( $row->img1 );
			$thumb_one_url = $img_one->createThumb(256);
			$imageOneWidth = $img_one->getWidth();
			$imgOne =  '<img width="' . ($imageOneWidth >= 256 ? 256 : $imageOneWidth) . '" alt="" src="' . $thumb_one_url . ' "/>';
			$imageOneWidth = ($imageOneWidth >= 256 ? 256 : $imageOneWidth);
			$imageOneWidth += 10;
			
			$img_two = Image::newFromName( $row->img2 );
			$thumb_two_url = $img_two->createThumb(256);
			$imageTwoWidth = $img_two->getWidth();
			$imgTwo =   '<img width="' . ($imageTwoWidth >= 256 ? 256 : $imageTwoWidth) . '" alt="" src="' . $thumb_two_url . ' "/>';
			
			$imageTwoWidth = ($imageTwoWidth >= 256 ? 256 : $imageTwoWidth);
			$imageTwoWidth += 10;
			
			
			$title = $title_text;
			$img1_caption = $img1_caption_text;
			$img2_caption = $img2_caption_text;

			$vote_one_tag = "";
			$vote_two_tag = "";
			$imgOnePercent = "";
			$barOneWidth = "";
			$imgTwoPercent = "";
			$barTwoWidth = "";
			$permalinkJS = "";
			
			$isShowVotes = false;
			if($lastID > 0){
				$sql = "SELECT * FROM picturegame_images WHERE flag!='FLAGGED' AND id={$lastID};";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );
				
				if($row){
					$img_one = Image::newFromName( $row->img1 );
					$img_two = Image::newFromName( $row->img2 );
					$imgOneCount = $row->img0_votes;
					$imgTwoCount = $row->img1_votes;
					$isShowVotes = true;
				}
			}
			
			if($isPermalink || $isShowVotes){
				$vote_one_thumb = $img_one->getThumbnail( 40, 0, true );
				$vote_one_tag = $vote_one_thumb->toHtml();

				$vote_two_thumb = $img_two->getThumbnail( 40, 0, true );
				$vote_two_tag = $vote_two_thumb->toHtml();
				
				$totalVotes = $imgOneCount + $imgTwoCount;
				
				if($imgOneCount == 0){
					$imgOnePercent = 0;
					$barOneWidth = 0;
				}else{
					$imgOnePercent = floor( $imgOneCount / $totalVotes  * 100 );
					$barOneWidth = floor( 200 * ($imgOneCount / $totalVotes ) );
				}
				
				if($imgTwoCount == 0){
					$imgTwoPercent = 0;
					$barTwoWidth = 0;
				}else{
					$imgTwoPercent = 100 - $imgOnePercent;
					$barTwoWidth = floor( 200 * ($imgTwoCount / $totalVotes ) );
				}

				$permalinkJS = "Effect.Appear('voteStats')";
			}
			

			// set the page title
			// $wgOut->setPagetitle($title_text);

			// figure out if the user is an admin / the creator
			if( $wgUser->isAllowed('protect') ){

				// if the user can edit throw in some links
				$editlinks = " - <a href=\"index.php?title=Special:PictureGameHome&picGameAction=adminPanel\"> Admin Panel</a>
					       - <a href=\"javascript:protectImages()\"> Protect</a>";
			}else{
				$editlinks = "";
			}

			if ($wgUser->isLoggedIn()) {
				$createLink = "
				<div class=\"create-link\">
					<a href=index.php?title=Special:PictureGameHome>
					<img src=\"images/common/addIcon.gif\" border=\"0\"/>Create a Picture Game</a>
				</div>";
			}else{
				$createLink = "";	
			}

			if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
				$editLink .= "<div class=\"edit-menu-pic-game\">
						<div class=\"edit-button-pic-game\">
							<img src=\"../../images/common/editIcon.gif\"/>
							<a href=\"javascript:editPanel()\">Edit</a>
						</div>
					    </div>";
			}else{
				$editLink = "";	
			}

			
			$id=User::idFromName($user_title->getText());
			$avatar = new wAvatar($id,"l");
			$avatarID = $avatar->getAvatarImage();
			$stats = new UserStats($id, $user_title->getText());
			$stats_data = $stats->getUserStats();

			$wgOut->setHTMLTitle( wfMsg( 'pagetitle', $title ) );
			
			$output .= "
			<script type=\"text/javascript\" src=\"{$this->INCLUDEPATH}lightbox_light.js\"></script>
			<script type=\"text/javascript\" src=\"{$this->INCLUDEPATH}picturegame.js\"></script>
			<script>var next_id = \"{$next_id}\";</script>
			{$editLink}
			<div class=\"editDiv\" id=\"editDiv\" style=\"display: none\"> </div>

					<div class=\"imgTitle\" id=\"imgTitle\">" . $title . "</div>

					<div class=\"serverMessages\" id=\"serverMessages\"></div>

					<div class=\"imgContent\" id=\"imgContent\">
						<div class=\"imgContainer\" id=\"imgContainer\">
							<div class=\"imgCaption\" id=\"imgOneCaption\">" . $img1_caption . "</div>
							<div class=\"imageOne\" id=\"imageOne\" style=\"width:{$imageOneWidth}px\" onClick=\"castVote(0)\" onmouseover=\"doHover('imageOne')\" onmouseout=\"endHover('imageOne')\" >
								" . $imgOne . "	</div>
						</div>

						<div class=\"imgContainer\" id=\"imgContainer\">
							<div class=\"imgCaption\" id=\"imgTwoCaption\">" . $img2_caption . "</div>
							<div class=\"imageTwo\" id=\"imageTwo\" style=\"width:{$imageTwoWidth}px\" onClick=\"castVote(1)\" onmouseover=\"doHover('imageTwo')\" onmouseout=\"endHover('imageTwo')\">
							" . $imgTwo . "	</div>
						</div>
						<div class=\"cleared\"></div>

						<div class=\"pic-game-navigation\">
							<ul>
								<li id=\"backButton\" style=\"display:" . ($lastID > 0 ? "block" : "none") . "\"><a href=\"javascript:window.parent.document.location='index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id=' + Form.Element.getValue('lastid')\">Go Back</a></li>
								<li id=\"skipButton\" style=\"display:" . ($next_id > 0 ? "block" : "none") . "\"><a href=\"index.php?title=Special:PictureGameHome&picGameAction=startGame\">Skip</a></li>
							</ul>
						</div>

						<form id=\"picGameVote\" name=\"picGameVote\" method=\"post\" action=\"index.php?title=Special:PictureGameHome&picGameAction=castVote\">
							<input id=\"key\" name=\"key\" type=\"hidden\" value=\"" . md5($imgID . $this->SALT) . "\" />
							<input id=\"id\" name=\"id\" type=\"hidden\" value=\"" . $imgID . "\" />
							<input id=\"lastid\" name=\"lastid\" type=\"hidden\" value=\"" . $lastID . "\" />
							<input id=\"nextid\" name=\"nextid\" type=\"hidden\" value=\"" . $next_id . "\" />
							
							<input id=\"img\" name=\"img\" type=\"hidden\" value=\"\" />
						</form>
				</div>
				<div class=\"other-info\">
					{$createLink}
					<div class=\"credit-box\" id=\"creditBox\">
						<h1>Submitted By</h1>
						<div class=\"submitted-by-image\">
							<a href=\"{$user_title->getFullURL()}\"><img src=images/avatars/{$avatarID} style=\"border:1px solid #d7dee8; width:50px; height:50px;\"/></a>
						</div>
						<div class=\"submitted-by-user\">
							<a href=\"{$user_title->getFullURL()}\">{$user_name}</a>
							<ul>
								<li>
									<img src=\"images/common/voteIcon.gif\" border=\"0\"> {$stats_data["votes"]}
								</li>
								<li>
									<img src=\"images/common/pencilIcon.gif\" border=\"0\"> {$stats_data["edits"]}
								</li>
								<li>
									<img src=\"images/common/commentsIcon.gif\" border=\"0\"> {$stats_data["comments"]}
								</li>
							</ul>
						</div>
						<div class=\"cleared\"></div>
					</div>

					<div class=\"voteStats\" id=\"voteStats\" style=\"display:none\">
						<div id=\"vote-stats-text\"><h1>Previous Game ({$totalVotes})</h1></div>
						<div class=\"vote-bar\">
							<span class=\"vote-thumbnail\" id=\"one-vote-thumbnail\">{$vote_one_tag}</span>
							<span class=\"vote-percent\" id=\"one-vote-percent\">{$imgOnePercent}%</span>
							<span class=\"vote-blue\"><img src=\"../../images/common/vote-bar-blue.gif\" id=\"one-vote-width\" border=\"0\" style=\"width:{$barOneWidth}px;height:11px;\"/></span>
						</div>
						<div class=\"vote-bar\">
							<span class=\"vote-thumbnail\" id=\"two-vote-thumbnail\">{$vote_two_tag}</span>
							<span class=\"vote-percent\" id=\"two-vote-percent\">{$imgTwoPercent}%</span>
							<span class=\"vote-red\"><img src=\"../../images/common/vote-bar-red.gif\" id=\"two-vote-width\" border=\"0\" style=\"width:{$barTwoWidth}px;height:11px;\"/></span>
						</div>
					</div>

					<div class=\"utilityButtons\" id=\"utilityButtons\">
						<a href=\"javascript:flagImg()\">Report Images </a> -
						<a href=\"javascript:window.parent.document.location='index.php?title=Special:PictureGameHome&picGameAction=renderPermalink&id=' + Form.Element.getValue('id')\"> Permalink </a>
					" . $editlinks . "
					</div>

				</div>

				<div class=\"cleared\"></div>
				
				<script language=\"javascript\">
					{$permalinkJS}
				</script>
				
			</div>
			
			<div id=\"preload\" style=\"display:none\">
				{$preload}
				<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0\" width=\"75\" height=\"75\" title=\"hourglass\"> 
				      <param name=\"movie\" value=\"/extensions/wikia/Ashish/picturegame/ajax-loading.swf\" /> 
				      <param name=\"quality\" value=\"high\" /> 
				      <param name=\"wmode\" value=\"transparent\" /> 
				      <param name=\"bgcolor\" value=\"#ffffff\" /> 
				      <embed src=\"/extensions/wikia/Ashish/picturegame/ajax-loading.swf\" quality=\"high\" wmode=\"transparent\" bgcolor=\"#ffffff\" pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\"  
				      type=\"application/x-shockwave-flash\" width=\"100\" height=\"100\"> 
				      </embed> 
				 </object>
			</div>
			";

			// " fix syntax coloring

			return $output;

		}

		function createGame(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP,$wgStyleVersion;

			$title = addslashes( $wgRequest->getVal("picGameTitle") );

			$img1 = addslashes ( $wgRequest->getVal("picOneURL") );
			$img2 = addslashes( $wgRequest->getVal("picTwoURL") );
			$img1_caption = addslashes ( $wgRequest->getVal("picOneDesc") );
			$img2_caption = addslashes( $wgRequest->getVal("picTwoDesc") );

			$voteID = addslashes( $wgRequest->getVal("voteID") );
			$voteImage = addslashes( $wgRequest->getVal("voteImage") );

			$key = $wgRequest->getVal("key");
			$chain = $wgRequest->getVal("chain");
			$id = -1;
			
			$dbr =& wfGetDB( DB_MASTER );

			// make sure no one is trying to do bad things
			if($key == md5($chain . $this->SALT) ){

				$sql = "SELECT COUNT(*) AS mycount FROM picturegame_images WHERE
					( img1 = \"" . $img1 . "\" OR img2 = \"" . $img1 . "\" ) AND
					( img1 = \"" . $img2 . "\" OR img2 = \"" . $img2 . "\" ) GROUP BY id;";

				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );

				// if these image pairs dont exist insert them "
				if($row->mycount == 0){
					$sql = "INSERT INTO picturegame_images (userid, username, img1, img2, title, img1_caption, img2_caption, pg_date)
						VALUES(\"" . $wgUser->getID() . "\", \"". $wgUser->getName() . "\", \"" . $img1 . "\", \"" . $img2 . "\", \"" . $title . "\", \"" . $img1_caption . "\", \"" . $img2_caption . "\", \"" . date("Y-m-d H:i:s")  . "\");";
					//"
					$res = $dbr->query($sql);

					$sql = "SELECT MAX(id) AS maxid from picturegame_images WHERE 1;";
					$res = $dbr->query($sql);
					$row = $dbr->fetchObject( $res );
					$id = $row->maxid;
					
					$stats = new UserStatsTrack(1,$wgUser->getID(), $wgUser->getName());
					$stats->incStatField("picturegame_created");
				  }
			}

			header("Location: index.php?title=Special:PictureGameHome&picGameAction=startGame&id={$id}");
			
		}
		
		// renders the inital page of the game
		function renderPictureGame(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP,$wgStyleVersion;

			$permalinkID =  addslashes( $wgRequest->getVal("id") );
			$lastid = addslashes( $wgRequest->getVal("lastid") );

			if(!is_numeric( $lastid ))
				$lastid = -1;
			
			$isPermalink = false;
			$permalinkError = false;

			$dbr =& wfGetDB( DB_MASTER );
			if($permalinkID > 0){

				$isPermalink = true;
				
				$sql = "SELECT COUNT(*) AS mycount FROM picturegame_images WHERE (flag='NONE' OR flag='PROTECT') AND id=" . $permalinkID . ";";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );

				if($row->mycount == 0){
					$output = "
						<style>@import \"{$this->INCLUDEPATH}maingame.css?{$wgStyleVersion}\";</style>
						<div class=\"picgame-container\" id=\"picgame-container\">
							<p>These pictures have been flagged, because of inappropriate 
							content or copyrighted material. To play the picture game, click the 
							button below.</p>
							<p><input type=\"button\" class=\"site-button\" value=\"Play the Picture Game\" 
							onclick=\"window.location='index.php?title=Special:PictureGameHome&picGameAction=startGame'\"/>
							</p>
						</div>";
					$wgOut->addHTML( $output );
					return;
					//"
				}
				
			}else{
				$permalinkID = -1;
			}


			$output = "

			<style>@import \"{$this->INCLUDEPATH}maingame.css?{$wgStyleVersion}\";</style>

			<div class=\"picgame-container\" id=\"picgame-container\">" . $this->getImageDivs($isPermalink, $permalinkID, $lastid);

			// " syntax coloring

			$wgOut->addHTML($output);
		}

		// shows the inital page that prompts the image upload
		function showHomePage(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP,$wgStyleVersion;

			if( !$wgUser->isLoggedIn() ){

				$wgOut->setPagetitle("Create a Picture Game");
				$output = "You must log-in to create a picture game.";
				$output .= "<p>
					<input type=\"button\" class=\"site-button\" onclick=\"window.location='index.php?title=Special:UserRegister'\" value=\"Sign Up\"/>
					<input type=\"button\" class=\"site-button\" onclick=\"window.location='index.php?title=Special:Login'\" value=\"Log In\"/>
				</p>";
				$wgOut->addHTML($output);
				return;
			}

			if( $wgUser->isAllowed('protect') ) {
				$adminlink = "<a href=\"index.php?title=Special:PictureGameHome&picGameAction=adminPanel\"> Admin Panel </a>";
			}

			//"

			$dbr =& wfGetDB( DB_MASTER );
			$sql = "SELECT COUNT(*) AS mycount FROM picturegame_images WHERE picturegame_images.id NOT IN (SELECT picid FROM picturegame_votes WHERE picturegame_votes.username='" . addslashes($wgUser->getName()) . "') AND flag != 'FLAGGED' ORDER BY RAND() LIMIT 1;";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );

			$canSkip = ($row->mycount != 0 ? true : false);

			// used for the key
			$NOW = time();

			$wgOut->setHTMLTitle( wfMsg( 'pagetitle', "Create a Picture Game" ) );

			$output = "<script type=\"text/javascript\" src=\"{$this->INCLUDEPATH}startgame.js?{$wgStyleVersion}\"></script>
			<style>@import \"{$this->INCLUDEPATH}startgame.css?{$wgStyleVersion}\";</style>

				<div class=\"welcome-message\">
					<h1>Create a Picture Game</h1>
					Upload two pictures, add some captions, and then go crazy rating everyone's pictures.  Its that easy.
					<!-- ' -->
					<br />

					<div id=\"skipButton\" class=\"startButton\">
					" . ($canSkip ? "<input class=\"startButton\" type=\"button\" onclick=\"javascript:skipToGame()\" value=\"Play Game Instead\"/>" : "") . "
					</div>
				</div>

				<div class=\"uploadLeft\">
					<div id=\"uploadTitle\" class=\"uploadTitle\">
						<form id=\"picGamePlay\" name=\"picGamePlay\" method=\"post\" action=\"" . $wgRequest->getRequestURL() . "&picGameAction=createGame\">
							<h1>
								Picture Game Title
							</h1>
							<div class=\"picgame-errors\" id=\"picgame-errors\"></div>
							<p>
								<input name=\"picGameTitle\" id=\"picGameTitle\" type=\"text\" value=\"\" size=\"40\"/> </h2>
							</p>

							<input name=\"picOneURL\" id=\"picOneURL\" type=\"hidden\" value=\"\" />
							<input name=\"picTwoURL\" id=\"picTwoURL\" type=\"hidden\" value=\"\" />
							<input name=\"picOneDesc\" id=\"picOneDesc\" type=\"hidden\" value=\"\" />
							<input name=\"picTwoDesc\" id=\"picTwoDesc\" type=\"hidden\" value=\"\" />
							<input name=\"key\" type=\"hidden\" value=\"" . md5($NOW . $this->SALT) . "\" />
							<input name=\"chain\" type=\"hidden\" value=\"" . $NOW . "\" />
						</form>
					</div>

					<div class=\"content\">
						<div id=\"uploadImageForms\" class=\"uploadImage\">

							<div id=\"imageOneUpload\" class=\"imageOneUpload\">
								<h1>First Image</h1>
								<div id=\"imageOneUploadError\"></div>
								<div id=\"imageOneLoadingImg\" class=\"loadingImg\" style=\"display:none\"> <img src=\"../../images/common/ajax-loader-white.gif\" /> </div>
								<div id=\"imageOne\" class=\"imageOne\" style=\"display:none;\"></div>
								<iframe class=\"imageOneUpload-frame\" scrolling=\"no\" frameBorder=\"0\" width=\"410\" id=\"imageOneUpload-frame\" src=\"" . $wgRequest->getRequestURL() . "&picGameAction=uploadForm&callbackPrefix=imageOne_\"></iframe>
							</div>

							<div id=\"imageTwoUpload\" class=\"imageTwoUpload\">
								<h1>Second Image</h1>
								<div id=\"imageTwoUploadError\"></div>
								<div id=\"imageTwoLoadingImg\" class=\"loadingImg\" style=\"display:none\"> <img src=\"../../images/common/ajax-loader-white.gif\" /> </div>
								<div id=\"imageTwo\" class=\"imageTwo\" style=\"display:none;\"></div>
								<iframe id=\"imageTwoUpload-frame\" scrolling=\"no\" frameBorder=\"0\" width=\"410\" src=\"" . $wgRequest->getRequestURL() . "&picGameAction=uploadForm&callbackPrefix=imageTwo_\"></iframe>
							</div>

							<div class=\"cleared\"></div>
						</div>
					</div>
				</div>

				<div id=\"startButton\" class=\"startButton\" style=\"display: none;\">
					<input type=\"button\" onclick=\"startGame()\" value=\"Create and Play!\"/>
				</div>

			";

			// "
			$wgOut->addHTML($output);
			}
	}

	SpecialPage::addPage( new PictureGameHome );
}

?>
