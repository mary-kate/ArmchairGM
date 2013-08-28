<?php

Class MailingListArmchairGM extends MailingList{
	
	private $subject = "ArmchairGM Weekly Update";
	private $user_activity = array();
	
	public function __construct( ) {
		parent::__construct();
		$this->populateUserData();
		$this->popular_articles = $this->getPopularArticles();
	}
		
	public function getBody($user_id, $user_name){
		$output .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"620\" style=\"border:1px solid #dcdcdc;padding:10px;\" align=\"center\">
				<tr>
					<td align=\"right\" color=\"#333333\">
						<font face=\"arial\" size=\"-2\">
							".$this->getDatePeriod()."
						</font>
					</td>
				</tr>
				<tr>
					<td>";
			
						$output .= $this->getHeader();
						$output .= $this->getUserPoints($user_id, $user_name);
						$output .= $this->getWelcome($user_name);
						$output .= $this->getFriendUpdates($user_id,$user_name);
						$output .= $this->getNetworkActivity($user_id);
						$output .= $this->popular_articles;
						$output .= $this->getNextQuizQuestion($user_id,$user_name);
						$output .= $this->getNextPictureGame($user_id, $user_name);
				
					$output .= "</td>
				</tr>
				<tr>
			</table>";
			$output .= $this->getFooter();
			$output .= "";
			
			return $output;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function getHeader() {
		
		$header = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"padding:0px 0px 10px 0px;\">
			<tr>
				<td>
				<a href=\"" . $this->getURL("http://www.armchairgm.com/index.php?title=Main_Page") . "\"><img src=\"http://images.wikia.com/openserving/sports/images/sports/logo.png\" alt=\"ArmchairGM\" border=\"0\"></a>
				</td>
				<td style=\"padding:0px 10px 0px 10px;\">
					<font face=\"arial\" color=\"#999999\">
						<b>weekly update</b>
					</font>
				</td>
			</tr>
		</table>";
		
		return $header;	
	}

	public function getWelcome($user_name) {
		
		$user_title = Title::makeTitle(NS_USER,$user_name);
		$output = "<table cellpadding=\"0\" cellspacing=\"0\" style=\"padding:5px 0px 15px 0px;\" width=\"620\"  >
				<tr>
					<td >
						<font face=\"arial\"  color=\"#000000\">
						<b>Hi <a href=\"" . $this->getURL($user_title->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">{$user_name}</font></a></b>,
						<br>
						<font size=\"-1\">Here's your weekly ArmchairGM update</font>
						</font>
					</td>
				</tr>
			</table>";
		return $output;
	}
	
	public function getFooter() {
		
		$preferences_title = Title::makeTitle(NS_SPECIAL,"UpdateProfile");
		$output = "<table cellpadding=\"0\" cellspacing=\"0\" style=\"padding:5px 0px 0px 0px;\" width=\"620\" align=\"center\">
				<tr>
					<td align=\"center\">
						<font face=\"arial\" size=\"-2\" color=\"#999999\">
							Wikia\&reg; is a registered service mark of Wikia, Inc. All rights reserved.<br>
							<a href=\"" . $this->getURL("http://www.armchairgm.com/index.php?title=Main_Page") . "\"><font face=\"arial\" color=\"#285C98\">Main Page</font></a> - <a href=\"{$preferences_title->getFullURL()}\"><font face=\"arial\" color=\"#285C98\">Unsubscribe</font></a> - <a href=\"http://www.wikia.com/wiki/Terms_of_use\"><font face=\"arial\" color=\"#285C98\">Terms of Use</font></a>
						</font>
					</td>
				</tr>
			</table>";
		return $output;
	}
	
	public function getPopularArticles(){
		global $IP;
		require_once ("$IP/extensions/ListPages/ListPagesClass.php");
		$list = new ListPages();
		$list->setCategory( $this->getDatesFromElapsedDays(7) );
		$list->setShowCount(7);
		$list->setOrder("Votes");
		$list->setShowPublished("YES");
		$dbr =& wfGetDB( DB_MASTER );
	
		$res = $dbr->query($list->buildSQL());
		
		$articles = "";
		while ($row = $dbr->fetchObject( $res ) ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$articles .= "<tr>
				<td align=\"left\" style=\"padding:0px 0px 10px 0px;\">
					<b><font face=\"arial\" size=\"-1\"><a href=\"" . $this->getURL($title->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">{$title->getText()}</font></a> - <font face=\"arial\" color=\"#666666\">{$row->vote_count} " . (($row->vote_count!=1)?"votes":"vote") . ", {$row->comment_count} ".(($row->comment_count!=1)?"comments":"comment")."</font></font></b>
				</td>
			</tr>";
		}
		
		$output = "<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\">
			<tr>
				<td colspan=\"2\" align=\"left\" style=\"padding:0px 0px 10px 0px;\">
					<font face=\"arial\" color=\"#333333\">
						<b>Most Popular Articles of the Week</font></b>
				</td>
			</tr>
			{$articles}
		</table><br>";
		
		return $output;
	 
	}

	public function getNetworkActivity($user_id){
		$dbr =& wfGetDB( DB_MASTER );
		$favs = SportsTeams::getUserFavorites($user_id);
		foreach($favs as $fav){
			
			if(!$fav["team_id"]){
				$where_sql = " sf_sport_id = {$fav["sport_id"]} and sf_team_id=0 ";
			}else{
				$where_sql = " sf_team_id = {$fav["team_id"]} ";	
			}

			$sql = "SELECT count(*) as the_count FROM sport_favorite WHERE {$where_sql} and sf_user_id <> {$user_id} and UNIX_TIMESTAMP(sf_date) > {$this->time_7_days_ago} ";
			 
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			if( $row->the_count > 0 ){
				$networks .= "<tr>
					<td width=\"150\" style=\"padding:12px 0px; border-bottom:1px solid #dcdcdc;\">
						<font face=\"arial\" size=\"-1\">
							<b><a href=\"" . $this->getURL(SportsTeams::getNetworkURL($fav["sport_id"], $fav["team_id"])) . "\" style=\"text-decoration:none\"><font face=\"arial\" color=\"#285C98\">" .
						SportsTeams::getNetworkName($fav["sport_id"], $fav["team_id"]) . 
						"</font></a></b>
						</font>
					</td>
						<td style=\"padding:12px 0px; border-bottom:1px solid #dcdcdc;\">
							<font face=\"arial\" size=\"-1\">
								<b>{$row->the_count}</b> New Fan" . (($row->the_count!=1)?"s":"") . "
							</font>
						</td>
					</tr>";
			}
			
		}
		
		if (count($favs)==0) {
			$add_networks = Title::makeTitle(NS_SPECIAL,"UpdateFavoriteTeams");
			$networks .= "<tr>
				<td>
					<font face=\"arial\" size=\"-1\">
						You don't have any favorite teams set up.  <a href=\"" . $this->getURL($add_networks->getFullURL()) . "\"><font face=\"arial\" color=\"#285C98\">Tell us who you root for!</font></a>
					</font>
				</td>
			</tr>";
		}
		
		
		if ($networks != "") {
			$output = "
			<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\">
					<tr>
						<td colspan=\"2\"style=\"padding:0px 0px 2px 0px;\">
							<b><font face=\"arial\" color=\"#333333\">Meet New Sports Fans</font></b>
						</td>
					</tr>
					{$networks}
			</table>
			<br>";
		}
		
		return $output;
	 
	}
	

	function getUserPoints($user_id, $user_name){
		
		$u = new UserStats($user_id, $user_name);
		$stats = $u->getUserStats();
		
		$dbr =& wfGetDB( DB_MASTER );
		$s = $dbr->selectRow( '`user_points_weekly`', array( 'up_points'),array( 'up_user_id' => $user_id ), "" );  
		$weekly_points = number_format($s->up_points);
	 
		$output = "<table bgcolor=\"#F2F4F7\" width=\"600\" style=\"border:1px solid #D7DEE8;\">
				<tr>
					<td><font face=\"arial\" size=\"-1\" color=\"#333333\"><b>Your Points</b></font></td>
					<td width=\"75\"><font face=\"arial\" size=\"-1\"><b>This Week</b></font></td>
					<td><font face=\"arial\" size=\"-1\">{$weekly_points}</font></td>
					<td width=\"75\"><font face=\"arial\" size=\"-1\"><b>Overall</font></b></td>
					<td><font face=\"arial\" size=\"-1\">{$stats["points"]}</font></td>
				</tr>	 
			</table><br>";
		
		return $output;	
	}
	
	function getNextQuizQuestion($user_id, $user_name){
		$dbr =& wfGetDB( DB_MASTER );
		$id = QuizGameHome::get_next_question( $user_name );	
		if($id){
			$sql = "SELECT q_id,q_user_id, q_user_name, q_text, q_flag, q_answer_count, q_answer_correct_count, q_picture, q_date
			FROM quizgame_questions WHERE q_id = $id LIMIT 0,1";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			if($row){
				$quiz_title = Title::makeTitle(NS_SPECIAL,"QuizGameHome");
				$output = "<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\"> 
				<tr>
					<td style=\"padding:0px 0px 5px 0px;\" align=\"left\">
						<font face=\"arial\" color=\"#333333\"><b>Play the Quiz Game</b></font>
					</td>
				</tr>
				<tr>
					<td>
						<font face=\"arial\" size=\"-1\">Your next question: <b><a href=\"" . $this->getURL($quiz_title->getFullURL()."&questionGameAction=renderPermalink&permalinkID={$id}") . "\" style=\"text-decoration:none\"><font face=\"arial\" color=\"#285C98\">{$row->q_text}</font></a> . . . <a href=\"" . $this->getURL($quiz_title->getFullURL()) . "\" style=\"text-decoration:none\"><font face=\"arial\" color=\"#285C98\">Play Now!</font></a></b></font>
					</td>
				</tr>
				 
				</table><br>";
			}
		}
		
		return $output;	
	}
	
	function getNextPictureGame($user_id, $user_name){
 
		$dbr =& wfGetDB( DB_MASTER );
		$order = ( (time() % 2 == 0) ? "ASC" : "DESC" );
		$sql = "SELECT * FROM picturegame_images WHERE picturegame_images.id NOT IN (SELECT picid FROM picturegame_votes WHERE picturegame_votes.username='" . addslashes( $user_name ) . "') AND flag != 'FLAGGED' and img1<>'' ORDER BY title {$order} LIMIT 1;";

		$res = $dbr->query($sql);
		$row = $dbr->fetchObject( $res );
		$imgID = $row->id;
		if($imgID){
			$game_title = Title::makeTitle(NS_SPECIAL,"PictureGameHome");
			
			$img_one = Image::newFromName( $row->img1 );
			$thumb_one_url = $img_one->createThumb(128);
			$imgOne = '<img border="0" style="border:1px solid #dcdcdc;padding:3px;" width="' . ($img_one->getWidth() >= 128 ? 128 : $img_one->getWidth()) . '" alt="" src="' . $thumb_one_url . '?' . time() . '"/>';

			$img_two = Image::newFromName( $row->img2 );
			$thumb_two_url = $img_two->createThumb(128);
			$imgTwo = '<img border="0" style="border:1px solid #dcdcdc;padding:3px;" width="' . ($img_two->getWidth() >= 128 ? 128 : $img_two->getWidth()) . '" alt="" src="' . $thumb_two_url . '?' . time() . '"/>';

			$output = "<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\">
				<tr>
					<td style=\"padding:0px 0px 5px 0px;\" align=\"left\">
						<font face=\"arial\" color=\"#333333\"><b>Play the Picture Game</b></font>
					</td>
				</tr>
				<tr>
					<td style=\"padding:0px 0px 5px 0px;\" align=\"left\">
						<font face=\"arial\" size=\"-1\">{$row->title}</font>
					</td>
				</tr>
				<tr>
					<td> 
						<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
							<tr>
								<td style=\"padding:0px 10px 0px 0px;\" valign=\"top\">
									<font face=\"arial\" size=\"-1\">
										<a href=\"" . $this->getURL($game_title->getFullURL()."&picGameAction=renderPermalink&id={$imgID}") . "\" >{$imgOne}</a>
									</font>
								</td>
								<td valign=\"top\">
									<font face=\"arial\" size=\"-1\">
										<a href=\"" . $this->getURL($game_title->getFullURL()."&picGameAction=renderPermalink&id={$imgID}") . "\">{$imgTwo}</a>
									</font>
								</td>
							</tr>
							<tr>
								<td colspan=\"2\" style=\"padding:5px 0px 0px 0px;\">
									<font face=\"arial\" size=\"-1\">
										Click the image you like better . . . <b><a href=\"" . $this->getURL($game_title->getFullURL()."&picGameAction=renderPermalink&id={$imgID}") . "\" style=\"text-decoration:none\"><font face=\"arial\" color=\"#285C98\">Play Now!</font></a></b>
									</font>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table><br>";
		}
	
		return $output;	
	}
	
	function populateUserData(){
		$this->setFriendCount();
		$this->setGiftsCount();
		$this->setEditsCount();
		$this->setThoughtsCount();
		$this->setCommentsCount();	
	}
	
	function getFriendUpdates($user_id, $user_name){
		global $IP;
		require_once("$IP/extensions/wikia/UserRelationship/UserRelationshipClass.php");
		
		$this->setFriendCount($user_id);
		$this->setGiftsCount($user_id);
		$this->setEditsCount($user_id);
		$this->setThoughtsCount($user_id);
		$this->setCommentsCount($user_id);
		
		$activity_title = Title::makeTitle(NS_SPECIAL,"UserHome");
		
		$friend_activity = array();
		$rel = new UserRelationship( $user_name );
		
		$relationships = $rel->getRelationshipList(1);	
		 
		foreach($relationships as $relationship){
			if($this->user_activity[ $relationship["user_name"] ]["total"] > 0){
				$friend_activity[ $relationship["user_name"] ] = $this->user_activity[ $relationship["user_name"] ];
			}
		}
		
		usort($friend_activity, array("MailingListArmchairGM", "sortActivity"));

		$x = 1;
		foreach($friend_activity as $friend => $count){
			if($x<=10){
				$user =  Title::makeTitle( NS_USER  , $count["user_name"]  );
				$avatar = new wAvatar($count["user_id"],"s");
				
				$activity .= "<tr>
					<td valign=\"middle\" width=\"22\" style=\"padding:12px 0px 12px 0px;border-bottom:1px solid #dcdcdc;\">
						{$avatar->getAvatarURL()}
					</td>
					<td width=\"130\" style=\"padding:12px 0px 12px 0px;border-bottom:1px solid #dcdcdc;\">
						<font face=\"arial\" size=\"-1\">		 
							<b><a href=\"" . $this->getURL($user->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">{$count["user_name"]}</font></a></b>
						</font>
					</td>
					<td width=\"448\" style=\"padding:12px 0px 12px 0px;border-bottom:1px solid #dcdcdc;\">
						<font face=\"arial\" size=\"-1\">";
				
					$activity_list = "";
					if($count["edits"])$activity_list .= (($activity_list)?",":"") . " made <b>{$count["edits"]}</b> edit" . (($count["edits"]>1)?"s":"") ;
				if($count["friends"])$activity_list .= (($activity_list)?",":"") . " made <b>{$count["friends"]}</b> new friend" . (($count["friends"]>1)?"s":"");
				if($count["comments"])$activity_list .= (($activity_list)?",":"") . " posted <b>{$count["comments"]}</b> comment" . (($count["comments"]>1)?"s":"");
				if($count["thoughts"])$activity_list .= (($activity_list)?",":"") . " posted <b>{$count["thoughts"]}</b> thought" . (($count["thoughts"]>1)?"s":"");
				if($count["gifts"])$activity_list .= (($activity_list)?",":"") . " received <b>{$count["gifts"]}</b> gift" . (($count["gifts"]>1)?"s":"");
				
						$activity .= "{$activity_list}
						</font>
					</td>
				</tr>";
				$x++;
			}
		}
		
		if(count($relationships)==0){
			$meet_title = Title::makeTitle(NS_SPECIAL,"SimilarFans");
			$add_networks = Title::makeTitle(NS_SPECIAL,"UpdateFavoriteTeams");
			$activity .= "<tr>
				<td>
					<font face=\"arial\" size=\"-1\">
						You don't have any ArmchairGM Friends yet :(
						<br><br>
						<a href=\"" . $this->getURL($add_networks->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">Tell us your favorite teams</font></a> and <a href=\"" . $this->getURL($meet_title->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">Meet similar fans</font></a>
					</font>
				</td>
			</tr>";
		}else{
			$activity .= "<tr>
				<td colspan=\"3\" align=\"left\">
					<br>
					<font face=\"arial\" size=\"-1\">
						<a href=\"" . $this->getURL($activity_title->getFullURL()) . "\" style=\"text-decoration:none;\"><font face=\"arial\" color=\"#285C98\">See all friend activity</font></a>
					</font>
				</td>
			</tr>";
		}
						
		$output = "<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\">
				<tr>
					<td colspan=\"3\" align=\"left\">
						<font face=\"arial\" color=\"#333333\">
							<b>Most Active Friends</b>
						</font>
					</td>
				</tr>
				{$activity}
				</table>
				<br>";
	
		return $output;
	}
	
	function setFriendCount(){

		$dbr =& wfGetDB( DB_MASTER );
		$sql = "select r_user_name, r_user_id, count(*) as the_count
			from user_relationship 
			where UNIX_TIMESTAMP(r_date) > {$this->time_7_days_ago}
			group by r_user_id
			";
		
		$res = $dbr->query($sql);
 
		while ($row = $dbr->fetchObject( $res ) ) {
			$this->user_activity[$row->r_user_name]["friends"] = $row->the_count;
			$this->user_activity[$row->r_user_name]["user_name"] = $row->r_user_name;
			$this->user_activity[$row->r_user_name]["user_id"] = $row->r_user_id;
			$this->user_activity[$row->r_user_name]["total"] = $this->user_activity[$row->r_user_name]["total"] + $row->the_count;
		}
			
	}
	
	function setGiftsCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "select ug_user_name_to, ug_user_id_to, count(*) as the_count
			from user_gift
			 
			group by ug_user_id_to
			";
		
		$res = $dbr->query($sql);
 
		while ($row = $dbr->fetchObject( $res ) ) {
			$this->user_activity[$row->ug_user_name_to]["gifts"] = $row->the_count;
			$this->user_activity[$row->ug_user_name_to]["user_name"] = $row->ug_user_name_to;
			$this->user_activity[$row->ug_user_name_to]["user_id"] = $row->ug_user_id_to;
			$this->user_activity[$row->ug_user_name_to]["total"] = $this->user_activity[$row->ug_user_name_to]["total"] + $row->the_count;
		}
			
	}
	
	function setEditsCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "select rc_user_text, rc_user, count(*) as the_count
			from recentchanges  
			group by rc_user
			";
		
		$res = $dbr->query($sql);
 
		while ($row = $dbr->fetchObject( $res ) ) {
			$this->user_activity[$row->rc_user_text]["edits"] = $row->the_count;
			$this->user_activity[$row->rc_user_text]["user_name"] = $row->rc_user_text;
			$this->user_activity[$row->rc_user_text]["user_id"] = $row->rc_user;
			$this->user_activity[$row->rc_user_text]["total"] = $this->user_activity[$row->rc_user_text]["total"] + $row->the_count;
		}
			
	}
	
	function setThoughtsCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "select us_user_name, us_user_id, count(*) as the_count
			from user_status  
			group by us_user_id
			";
		
		$res = $dbr->query($sql);
 
		while ($row = $dbr->fetchObject( $res ) ) {
			$this->user_activity[$row->us_user_name]["thoughts"] = $row->the_count;
			$this->user_activity[$row->us_user_name]["user_name"] = $row->us_user_name;
			$this->user_activity[$row->us_user_name]["user_id"] = $row->us_user_id;
			$this->user_activity[$row->us_user_name]["total"] = $this->user_activity[$row->us_user_name]["total"] + $row->the_count;
		}
			
	}

	function setCommentsCount(){
		$dbr =& wfGetDB( DB_MASTER );
		$sql = "select Comment_Username, Comment_user_id, count(*) as the_count
			from Comments  
			group by Comment_user_id
			";
		
		$res = $dbr->query($sql);
 
		while ($row = $dbr->fetchObject( $res ) ) {
			$this->user_activity[$row->Comment_Username]["comments"] = $row->the_count;
			$this->user_activity[$row->Comment_Username]["user_name"] = $row->Comment_Username;
			$this->user_activity[$row->Comment_Username]["user_id"] = $row->Comment_user_id;
			$this->user_activity[$row->Comment_Username]["total"] = $this->user_activity[$row->Comment_Username]["total"] + $row->the_count;
		}
			
	}
	
	 function sortActivity($x, $y){
		if ( $x["total"] == $y["total"] )
		 return 0;
		else if ( $x["total"] > $y["total"] )
		 return -1;
		else
		 return 1;
	 }
	

}

?>