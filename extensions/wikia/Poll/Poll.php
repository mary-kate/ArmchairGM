<?php
//GLOBAL VIDEO NAMESPACE REFERENCE
define( 'NS_POLL', 300 );

$wgPollDisplay['comments'] = false;

$wgHooks['TitleMoveComplete'][] = 'fnUpdatePollQuestion';
function fnUpdatePollQuestion(&$title, &$newtitle, &$user, $oldid, $newid) {
	if($title->getNamespace() == NS_POLL){
		$dbr =& wfGetDB( DB_MASTER );
		$dbr->update( 'poll_question',
		array( 'poll_text' => $newtitle->getText() ),
		array( 'poll_page_id' => $oldid ),
		__METHOD__ );
	}
	return true;
}

$wgHooks['ArticleDelete'][] = 'fnDeletePollQuestion';
function fnDeletePollQuestion(&$article, &$user, $reason) {
	global $wgTitle, $wgSupressPageTitle;
	if($wgTitle->getNamespace() == NS_POLL){
		$wgSupressPageTitle = true;
		

			
		$dbr =& wfGetDB( DB_MASTER );
		
		$s = $dbr->selectRow( '`poll_question`', array( 'poll_user_id' , 'poll_id'), array( 'poll_page_id' => $article->getID() ), __METHOD__ );
		if ( $s !== false ) {
			//clear profile cache for user id that created poll
			global $wgMemc;
			$key = wfMemcKey( 'user', 'profile', 'polls' , $s->poll_user_id);
			$wgMemc->delete( $key );
			
			//delete poll recorda
			$dbr->delete( 'poll_user_vote',
			array( 'pv_poll_id' =>  $s->poll_id ),
			__METHOD__ );
						
			$dbr->delete( 'poll_choice',
			array( 'pc_poll_id' =>  $s->poll_id ),
			__METHOD__ );
					
			$dbr->delete( 'poll_question',
			array( 'poll_page_id' => $article->getID() ),
			__METHOD__ );
		}
		
	}
	return true;
}

$wgExtensionFunctions[] = "wfUserPoll";
function wfUserPoll() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "userpoll", "RenderPoll" );
}

function RenderPoll( $input, $args, &$parser ){
	return "";
}

$wgHooks['ArticleFromTitle'][] = 'wfPollFromTitle';
function wfPollFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgTitle, $wgMessageCache, $wgStyleVersion,
	$wgSupressPageTitle, $wgSupressSubTitle, $wgSupressPageCategories, $wgParser;
	
	if ( NS_POLL == $title->getNamespace()  ) {
		
		$wgOut->enableClientCache(false);
		$wgParser->disableCache();
		
		//prevents editing of POLL
		if( $wgRequest->getVal("action") == "edit" ){
			if( $wgTitle->getArticleID() == 0 ){
				$create = Title::makeTitle( NS_SPECIAL, "CreatePoll");
				$wgOut->redirect( $create->getFullURL("wpDestName=".$wgTitle->getText() ) );
			}else{
				$update = Title::makeTitle( NS_SPECIAL, "UpdatePoll");
				$wgOut->redirect( $update->getFullURL("id=".$wgTitle->getArticleID() ) );
			}
		}
		
	 	$wgSupressSubTitle = true;
		$wgSupressPageCategories = true;
		
		require_once ( "$IP/extensions/wikia/Poll/Poll.i18n.php" );
		foreach( efWikiaPoll() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		$wgNameSpacesWithEditMenu[] = NS_POLL;

		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Poll/Poll.js?{$wgStyleVersion}\"></script>\n");
		$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Poll/lightbox_light.js?{$wgStyleVersion}\"></script>\n");
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Poll/Poll.css?{$wgStyleVersion}\"/>\n");
		$wgOut->setOnloadHandler( "initLightbox()" );
		
		require_once( "$IP/extensions/wikia/Poll/PollPage.php" );
		
		$article = new PollPage($wgTitle);
	}
	
	return true;
	
}


?>