<?php

$wgExtensionFunctions[] = 'wfSpecialRecruitFix';


function wfSpecialRecruitFix(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class RecruitFix extends UnlistedSpecialPage {

  function RecruitFix(){
    UnlistedSpecialPage::UnlistedSpecialPage("RecruitFix");
  }

  function execute($period){
    global $wgUser, $wgOut, $wgRequest, $wgMemc; 

     $dbw = wfGetDB( DB_MASTER );
     
     if( ! in_array( "staff", $wgUser->getGroups() ) ){
	     $wgOut->addHTML("invalid access");
	     return "";
     }
     
     $user_id = User::idFromName($wgRequest->getVal("user"));
     
    if(  $wgRequest->wasPosted()  ) {
	    
	   
	  
		
		$dbw->update( 'user_stats',
			array( "stats_referrals_completed" => $wgRequest->getVal("referrals") ),
			array( 'stats_user_id' => $user_id   ),
			__METHOD__ );
				
		$stats = new UserStatsTrack( $user_id, $wgRequest->getVal("user") );
		$stats->updateTotalPoints();
		
		$key = wfMemcKey( 'user', 'stats', $user_id );
		$wgMemc->delete( $key );
		
		$wgOut->addHTML( $wgRequest->getVal("user") . " now has " . $wgRequest->getVal("referrals") . " recruits");
	
    }else{
	    $s = $dbw->selectRow( 'user_stats', "stats_referrals_completed", array( 'stats_user_id' => $user_id ), __METHOD__ );
	    $wgOut->addHTML($wgRequest->getVal("user") . " has <form method=post action=''><input type=text value='" . $s->stats_referrals_completed . "' name=referrals><input type=hidden name=changed value=1><input type=submit value=submit></form>");
		    
		    
    }
    
  
  }

}

 SpecialPage::addPage( new RecruitFix );
 global $wgMessageCache,$wgOut;
 
}

?>