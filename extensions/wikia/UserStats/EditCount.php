<?php
$wgHooks['ArticleSaveComplete'][] = 'incEditCount';

function incEditCount(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags ) {
    global $wgUser, $wgTitle, $wgNamespacesForEditPoints;
    
    /*
    if( ! is_object( $revision ) ){
	    return true;
    }
    
    $revision_array = $_SESSION["revision_array"];    
    if( in_array(  $revision->getID(), $revision_array ) ){
	    return true;
    }
    
    $revision_array[] = $revision->getID();
    $_SESSION["revision_array"] =  $revision_array;
    */
    
    //only keep tally for allowable namespaces
    if( !is_array($wgNamespacesForEditPoints) || in_array( $wgTitle->getNamespace(), $wgNamespacesForEditPoints ) ){
	    $stats = new UserStatsTrack($wgUser->getID(), $wgUser->getName());
	    $stats->incStatField("edit");
    }
    return true;
}

?>