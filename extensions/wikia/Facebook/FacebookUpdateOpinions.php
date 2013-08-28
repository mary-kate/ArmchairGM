<?php

$wgHooks['ArticleInsertComplete'][] = 'wfFaceboookUpdateOpinionsCheck';
$wgHooks['OutputPageBeforeHTML'][] = 'wfFacebookUpdateProfile';

function wfFacebookUpdateProfile(){
	global $wgOut, $wgTitle, $wgArticle, $wgUser, $wgRequest, $wgEnableFacebook, $IP;
	if($wgEnableFacebook){
		if(isset($_SESSION["fb_user_id"])){
			require_once "$IP/extensions/wikia/Facebook/appinclude.php";
			$facebook = new Facebook($appapikey, $appsecret);
			//$facebook->api_client->auth_getSession("QR1YVV");
			//$facebook->api_client->session_key = "QR1YVV";
			////update facebook profile
			 try{
				 $facebook->api_client->session_key = $infinite_session_key;
				$facebook->api_client->fbml_refreshRefUrl("http://sports.box8.tpa.wikia-inc.com/index.php?title=Special:FacebookGetOpinions&id={$_SESSION["fb_user_id"]}");
			 }catch(exception $ex){
			 
			}
		
			/*
			$feed_title = '<fb:userlink uid="'.$s->fb_user_id.'" /> wrote a new article on <a href=\"http://www.armchairgm.com\">ArmchairGM.com</a>';
			 $feed_body = "<a href=\"{$wgTitle->getFullURL()}\">{$wgTitle->getText()}</a>";
			 try{
			   $facebook->api_client->feed_publishActionOfUser($feed_title, $feed_body);
			 }catch(exception $ex){
				 
			 }
			 */		
			unset($_SESSION["fb_user_id"]);
		}
	}
}

function wfFaceboookUpdateOpinionsCheck(&$article, &$user, &$text, &$summary, &$minoredit, &$watchthis, &$sectionanchor, &$flags){
	global $wgOut, $wgTitle, $wgArticle, $wgUser, $wgRequest, $IP,$wgEnableFacebook;

	if($wgEnableFacebook){
		//If the user has created a new opinion, we want to turn on a session flag
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT cl_to FROM " . $dbr->tableName( 'categorylinks' ) . "  WHERE cl_from=" . $wgTitle->mArticleID;
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			if(   strtoupper($row->cl_to) == "OPINIONS" ) {
				 
				//check if the current user has the app installed
				$s = $dbr->selectRow( '`fb_link_view_opinions`', array( 'fb_user_id','fb_user_session_key' ), array( 'fb_user_id_wikia' => $wgUser->getID() ), $fname );
				if ( $s !== false ) {
					$_SESSION["fb_user_id"] = $s->fb_user_id;
				}
			}
		}
	}
	return true;
}



?>