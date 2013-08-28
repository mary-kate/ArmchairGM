<?php
//GLOBAL FANTAG NAMESPACE REFERENCE
define( 'NS_FANTAG', 600 );

$wgFanBoxPageDisplay['comments'] = true;

$wgHooks['ArticleFromTitle'][] = 'wfFantagFromTitle';
$wgHooks['ParserBeforeStrip'][] = 'fnFanBoxTag';

$wgExtensionFunctions[] = 'wfFanBox';


//ParserBeforeStrip
//Convert [[Fan:Fan_Name]] tags to <fan></fan> hook
function fnFanBoxTag(&$parser, &$text, &$strip_state) {
	$pattern = "@(\[\[Fan:)([^\]]*?)].*?\]@si";
        $text = preg_replace_callback($pattern, 'wfRenderFanBoxTag', $text);
	
        return true;	
}


//on preg_replace_callback
//found a match of [[Fan:]], so get parameters and construct <fan> hook
function wfRenderFanBoxTag($matches){
	global $wgOut, $IP;
	require_once( "$IP/extensions/wikia/Rob/FanBoxClass.php" );
	
	$name = $matches[2];
	$params = explode("|",$name);
	$fan_name = $params[0];
	$fan =  FanBox::newFromName( $fan_name );

	if( $fan->exists() ){	
		$output = "<fan name=\"{$fan->getName()}\"></fan>";
		return $output;
	}
	return $matches[0];
	
}



//ArticleFromTitle
//Calls BlogPage instead of standard article
function wfFantagFromTitle( &$title, &$article ){
	global $wgUser, $wgRequest, $IP, $wgOut, $wgTitle, $wgMessageCache, $wgStyleVersion, $wgSupressPageTitle, $wgSupressPageCategories;
	
	if ( NS_FANTAG == $title->getNamespace()  ) {
		$wgSupressPageTitle = true;
		
		require_once ( "$IP/extensions/wikia/Rob/FanBox.i18n.php" );
		foreach( efWikiaFantag() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
		
		require_once( "$IP/extensions/wikia/Rob/FanBoxClass.php" );
		require_once( "$IP/extensions/wikia/Rob/FanBoxPage.php" );
		$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Rob/FanBoxes.css?{$wgStyleVersion}\"/>\n");
				
		
		if( $wgRequest->getVal("action") == "edit" ){
			$add_title = Title::makeTitle(NS_SPECIAL,"FanBoxes");
			$fan = FanBox::newFromName( $title->getText() );
			if(!$fan->exists()){
				$wgOut->redirect( $add_title->getFullURL() . "&destName=" . $fan->getName() );
			}
			else{
				$update = Title::makeTitle( NS_SPECIAL, "FanBoxes");
				$wgOut->redirect( $update->getFullURL("id=".$wgTitle->getArticleID() ) );
			}
		}

		//$wgSupressPageCategories = true;
		$article = new FanBoxPage($wgTitle);
		
	}

	return true;
}

//wgExtensionFunctions
//new <video> hook
function wfFanBox() {
	global $wgParser;
	$wgParser->setHook('fan', 'wfFanBoxEmbed');
}

function wfFanBoxEmbed($input, $argv, &$parser){
	global $wgOut, $wgRequest, $wgMessageCache, $IP, $wgUser;
	$wgOut->addScript("<link rel='stylesheet' type='text/css' href=\"/extensions/wikia/Rob/FanBoxes.css\"/>\n");
	$wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Rob/FanBoxes.js\"></script>\n");

	require_once ( "$IP/extensions/wikia/Rob/FanBox.i18n.php" );
		foreach( efWikiaFantag() as $lang => $messages ){
			$wgMessageCache->addMessages( $messages, $lang );
		}
	
	$fan_name = $argv["name"];
	if(!$fan_name){
		return "";
	}
	
	$fan =  FanBox::newFromName($fan_name );
	if( $fan->exists() ){
		$output .= $fan->outputFanBox();
		$output .= "<div id=\"show-message-container\">";
		if($wgUser->isLoggedIn()){
			$check = $fan->checkIfUserHasFanBox();
			if ($check == 0){
				$output .= $fan->outputIfUserDoesntHaveFanBox();

			}
			else $output .= $fan->outputIfUserHasFanBox();
		}
		else {
			$output .= $fan->outputIfUserNotLoggedIn();
		}

		
		$output .= "</div>";

	}
	return $output;
		
}


?>
