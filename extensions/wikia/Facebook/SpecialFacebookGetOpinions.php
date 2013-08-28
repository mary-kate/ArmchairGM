<?php

$wgExtensionFunctions[] = 'wfSpecialFacebookGetOpinions';


function wfSpecialFacebookGetOpinions(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class FacebookGetOpinions extends UnlistedSpecialPage {
	
		function FacebookGetOpinions(){
			UnlistedSpecialPage::UnlistedSpecialPage("FacebookGetOpinions");
		}
	
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $IP, $wgSitename;
			
			$wgOut->setArticleBodyOnly(true);
			
			require_once("$IP/extensions/ListPages/ListPagesClass.php");
			require_once("$IP/extensions/wikia/UserActivity/UserActivityClass.php");
			
			$user = $wgRequest->getVal("id");
			$dbr =& wfGetDB( DB_MASTER );
			$s = $dbr->selectRow( '`fb_link_view_opinions`', array( 'fb_user_name_wikia' ), array( 'fb_user_id' => $user ), $fname );
			if ( $s !== false ) {
				$usertitle =  Title::makeTitle( NS_USER  , $s->fb_user_name_wikia  );
				$regtitle =  Title::makeTitle( NS_SPECIAL  , "UserRegister"  );
				
				$list = new ListPages();
				$list->setCategory("Opinions by User {$s->fb_user_name_wikia}");
				$list->setShowCount(5);
				$list->setOrder("New");
				$list->setBool("ShowVoteBox","NO");
				$list->setBool("ShowDate","NO");
				$list->setBool("ShowStats","NO");
				$list->setBool("ShowPic","YES");
				$list->setBool("ShowNav","NO");		
				$out =  $list->DisplayList();
				
				//DEFAULT Content if user has no articles
				//Show message and last 5 good opinions
				if($out == "No pages found."){
					$list = new ListPages();
					$list->setCategory("Opinions,News");
					$list->setShowCount(5);
					$list->setOrder("PublishedDate");
					$list->setShowPublished("YES");
					$list->setBool("ShowVoteBox","NO");
					$list->setBool("ShowDate","NO");
					$list->setBool("ShowStats","NO");
					$list->setBool("ShowPic","YES");
					$list->setBool("ShowNav","NO");		
				
					$create =  Title::makeTitle( NS_MAIN  , "Create Opinion"  );
					$out = "This user doesn't have any ArmchairGM content yet.  <p>ArmchairGM.com is the community for sports fans where you rule. Fans like you write and comment on articles and rate and vote on the best and worst things about sports. <a href=\"{$create->getFullURL()}\">It's easy. Start writing!</a><p>
					Here is the latest stuff from the ArmchairGM community<p>" . $list->DisplayList() . "
					";
				}
				
				//Facebook Header
				$out = "<fb:subtitle>Recent ArmchairGM articles by user <a href=\"{$usertitle->getFullURL()}\">{$usertitle->getText()}</a></fb:subtitle>" . $out;

				$out .= "<p>recent pages i've voted for<div style=\"border-bottom: 1px solid #D8DFEA;padding-bottom:3px\"></div>";
				$act = new UserActivity($wgUser->getName(),"user",5);
				$list = $act->getVotes();
				if($list){
					foreach ($list as $item) {
						$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
						$out .= "<div><a href=\"" . $title->getFullURL() . "\">" . $title->getPrefixedText() . "</a></div>";
					}
				}else{
					$out .= "No votes.";
				}
				
				$out .= "<p>recent pages i've edited<div style=\"border-bottom: 1px solid #D8DFEA\"></div>";
				$act = new UserActivity($wgUser->getName(),"user",5);
				$list = $act->getEdits();
				if($list){
					foreach ($list as $item) {
						$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
						$out .= "<div><a href=\"" . $title->getFullURL() . "\">" . $title->getPrefixedText() . "</a></div>";
					}
				}else{
					$out .= "No edits.";
				}

				$out .= "<p>recent pages ive commented on<div style=\"border-bottom: 1px solid #D8DFEA\"></div>";
				$act = new UserActivity($wgUser->getName(),"user",5);
				$list = $act->getComments();
				if($list){
					foreach ($list as $item) {
						$title = Title::makeTitle( $item["namespace"]  , $item["pagetitle"]  );
						$out .= "<div><a href=\"" . $title->getFullURL() . "#comment-{$item["id"]}\">" . $title->getPrefixedText() . "</a></div>";
						$out .= "<div>
									\"{$item["comment"]}\"
									</div>";
					}
				}else{
					$out .= "No comments.";
				}

				
				$out .= "<hr>
					<a href=\"{$regtitle->getFullURL()}&ref={$usertitle->getText()}&from=2\">Become my ArmchairGM friend by clicking here!</a>";
			}else{
				$out = "Error";
			}
			
			echo $out;
			 
		}
		
	}
	
	SpecialPage::addPage( new FacebookGetOpinions );
	global $wgMessageCache,$wgOut;
}

?>