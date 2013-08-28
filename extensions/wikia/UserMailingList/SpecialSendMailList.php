<?php

class MailingList {
	
	private $subject = "Weekly Email";
	public $user_list = array();
	public $time_7_days_ago;
	
	function __construct( ) {
		$this->time_7_days_ago =   time() - (60 * 60 * 24 * 7 )   ;
 
		//load mailing list into array
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "SELECT um_user_id, um_user_name, um_status from user_mailing_list where um_status = 0 and um_user_name IN ( 'Pean')";
		$res = $dbr->query($sql);
		while ($row = $dbr->fetchObject( $res ) ) {
			if($row->um_status == 0){
				$this->user_list[] = array(
					"user_id"=>$row->um_user_id,"user_name"=>$row->um_user_name
					);
			}
		}
	}
	
	public function getURL($url){
		return $url . "&src=weeklyemail";
	}
	
	public function sendEmail($user_id,$subject,$body){
		global $wgEmailFrom;
		
		$user = User::newFromId( $user_id );
		$user->loadFromId();
		if(  $user->isEmailConfirmed() && $user->getEmail()   ){
							
			$headers = "From: $wgEmailFrom\n";
			$headers .= "Reply-To: $wgEmailFrom\n";
			$headers .= "Return-Path:$wgEmailFrom\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
			
			mail($user->getEmail(),$subject,$body,$headers);
			
			//mark user as sent
			$dbr =& wfGetDB( DB_MASTER );
			$dbr->update( '`user_mailing_list`',
			array( /* SET */
			'um_status' => 1
			), array( /* WHERE */
			'um_user_id' => $user_id
			), ""
			);
			$this->sent_count++;
			
		}
	}
	
	public function resetStatus(){
		$dbr =& wfGetDB( DB_MASTER );
		//reset flag for all users
		$dbr->update( '`user_mailing_list`',
				array( /* SET */
				'um_status' => 0
				), array( "1=1"
				), ""
			);
	}
	
	public function getDatesFromElapsedDays($number_of_days){
		$dates[date("F j, Y", time() )] = 1; //gets today's date string
		for($x=1;$x<=$number_of_days;$x++){
			$time_ago = time() - (60 * 60 * 24 * $x);
			$date_string = date("F j, Y", $time_ago);
			$dates[$date_string] = 1;
		}
				  
		$date_categories = "";
		foreach ($dates as $key => $value) {
		if($date_categories)$date_categories .=",";
			$date_categories .= str_replace(",","\,",$key);
		}
		return $date_categories;
	}
	
	public function getDatePeriod(){
		$period_title = date("m/d/Y",strtotime("-1 week")) . "-" .  date("m/d/Y",time()) ;
		return $period_title;
	}
	
	/*
	public function getEditsCount(){
		return number_format(SiteStats::edits());
	}
 
	public function getPagesCount(){
		return number_format(SiteStats::articles());	
	}
	
	public function getVotesCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`Vote`', array( 'count(*) as count'),"", "" );  
		$vote_count = number_format($s->count);
		return $vote_count;
	}

	public function getCommentsCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`Comments`', array( 'count(*) as count'),"", "" );  
		$comment_count = number_format($s->count);
		return $comment_count;
	}
	*/
}
	
$wgExtensionFunctions[] = 'wfSpecialUserMailList';

function wfSpecialUserMailList(){


	
	class UserMailList extends SpecialPage {
		
		
		/* Construct the MediaWiki special page */
		function UserMailList(){
			UnlistedSpecialPage::UnlistedSpecialPage("UserMailList");
		}
		
		function execute(){
			global $wgOut,$wgEmailFrom, $wgMailingList;
			
			$class = "MailingList{$wgMailingList}";
			$m = new $class;
			
			foreach($m->user_list as $user_mail){
				$body = $m->getBody( $user_mail["user_id"], $user_mail["user_name"] );
				$m->sendEmail( $user_mail["user_id"], $m->getSubject(), $body );
				$count++;
			}
			
			$m->resetStatus();
			
			$wgOut->addHTML("Sent to {$m->sent_count} users");
		}			
	}

	SpecialPage::addPage( new UserMailList );
}

?>