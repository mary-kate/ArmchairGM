<?php
$wgExtensionFunctions[] = 'wfSpecialChallengeHistory';


function wfSpecialChallengeHistory(){
	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");


	class ChallengeHistory extends SpecialPage {
	
		function ChallengeHistory(){
			UnlistedSpecialPage::UnlistedSpecialPage("ChallengeHistory");
		}


		function displayUserHeader($user_name, $user_id){
			$avatar = new wAvatar($user_id,"l");
			$pos = Challenge::getUserFeedbackScoreByType(1,$user_id);
			$neg = Challenge::getUserFeedbackScoreByType(-1,$user_id);
			$neu = Challenge::getUserFeedbackScoreByType(0,$user_id);
			$total = ($pos + $neg + $neu);
			$percent = 0;
			if($pos)$percent = $pos / $total * 100;
				
			$out .= "<b>Overall Record</b>: (" .  Challenge::getUserChallengeRecord($user_id) . ")<br><br>";
			$out .= "<b>Ratings When Loser</b>: <br>";
			$out .= "<span class=challenge-rate-positive>Positive</span>: " . $pos . " (" . $percent . "%)<br>";
			$out .= "<span class=challenge-rate-negative>Negative</span>: " . $neg . "<br>";
			$out .= "<span class=challenge-rate-neutral>Neutral</span>: " . $neu . "<br><br>";
			return $out;
		}
		
		function execute(){
			global $wgUser, $wgOut, $wgRequest, $wgStyleVersion, $IP;
			
			require_once("$IP/extensions/wikia/Challenge/ChallengeClass.php");
			$challenge_link = Title::makeTitle( NS_SPECIAL  , "ChallengeUser"  );
			
			$css = "<style>
			.challenge-nav{
				width:740;
				padding-bottom:25px;
			}
			.challenge-history-header{
				font-size:12px;
				font-weight:800;
				padding-right:5px;
				color:#666666;
			}
			
			.challenge-data{
				padding:5px;
				font-size:11px;
				vertical-align:top;
				border-bottom:1px solid #eeeeee;
			}
			
			.challenge-data img{
				padding:2px;
			}
			
			.challenge-history-filter{
				float:right;
			}
			
			.challenge-link{
				float:left;
			}
			.challenge-link a{
				font-weight:800;
				font-size:15px;
			}
			</style>";
			$wgOut->addHTML($css);
			
			$out = "";
			if($wgRequest->getVal("user")){
				$user_title = Title::newFromDBkey($wgRequest->getVal("user"));
				if($user_title){
					$user_id = User::idFromName($user_title->getText());
				}else{
					//invalid user
				}
				
				$wgOut->setPagetitle( "{$user_title->getText()}'s Challenge History" );	
				$out .= $this->displayUserHeader($user_title->getText(), $user_id);
			}else{
				$wgOut->setPagetitle( "Recent Challenges" );
				$standings_title = Title::makeTitle( NS_SPECIAL  , "ChallengeStandings"  );
				$standings_link = " - <img src=\"images/common/userpageIcon.png\"> <a href=\"{$standings_title->getFullURL()}\">View Standings</a>";
				
			}
		
			$out .= "
			
			<div class=\"challenge-nav\">
			<div class=\"challenge-history-filter\">filter:
			<select style='font-size:10px' name=status-filter onChange=changeFilter('" . $_GET["user"] . "',this.value)>
			<option value='' " . ($_GET["status"] == "" && strlen($_GET["status"]) == 0 ? "selected":"") . ">All</option>
			<option value=0 " . ($_GET["status"] == 0 && strlen($_GET["status"]) == 1 ? "selected":"") . ">Awaiting Acceptance</option>
			<option value=1 " . ($_GET["status"] == 1 ? "selected":"") . ">In progress</option>
			<option value=-1 " . ($_GET["status"] == -1 ? "selected":"") . ">Rejected</option>
			<option value=3 " . ($_GET["status"] == 3 ? "selected":"") . ">Completed</option>
			</select></div>
			<div class=\"challenge-link\">
			<img src=\"images/common/challengeIcon.png\"> <a href=\"{$challenge_link->getFullURL()}" . (($wgRequest->getVal("user"))?"&user={$wgRequest->getVal("user")}":"") . "\">Challenge " . (($wgRequest->getVal("user"))?"{$wgRequest->getVal("user")}":"Someone") . "</a> {$standings_link}
			</div>
			<div class=\"cleared\"></div>
			</div>
			
			<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"830\"><tr>
			<td class=\"challenge-history-header\">event</td>
			<td class=\"challenge-history-header\">challenger description</td>
			<td class=\"challenge-history-header\">challenger</td>
			<td class=\"challenge-history-header\">target</td>
			<td class=\"challenge-history-header\">status</td>
			</tr>";
			
			$page =  $wgRequest->getVal('page');
			if(!$page)$page=1;
			
			$per_page = 25;
			
			$c = new Challenge();
			$challenge_list = $c->getChallengeList($wgRequest->getVal("user"),$wgRequest->getVal("status"),$per_page,$page);
			$total_challenges = $c->getChallengeCount();
			
			if($challenge_list){
				foreach ($challenge_list as $challenge) {
				
					//set up avatars and wiki titles for challenge and target
					$avatar1 = new wAvatar($challenge["user_id_1"],"s");
					$avatar2 = new wAvatar($challenge["user_id_2"],"s");
				
					$title1 = Title::makeTitle( NS_USER  , $challenge["user_name_1"]  );
					$title2 = Title::makeTitle( NS_USER  , $challenge["user_name_2"]  );
				
					//set up titles for pages used in table
					$challenge_view_title = Title::makeTitle( NS_SPECIAL  , "ChallengeView"  );
					
					$out .= "<tr>
						<td class=\"challenge-data\"><a href=index.php?title=Special:ChallengeView&id={$challenge["id"]}>{$challenge["info"]} [{$challenge["date"]}]</a></td>
						<td class=\"challenge-data\" width=\"150\">{$challenge["description"]}</td>
						<td class=\"challenge-data\" ><img src='images/avatars/{$avatar1->getAvatarImage()}' border=\"0\"  align=\"absmiddle\" /><a href=\"{$title1->getFullURL()}\">{$challenge["user_name_1"]}</a> " . (($challenge["winner_user_id"]==$challenge["user_id_1"])?"<img src=\"images/winner-check.gif\" align=\"absmiddle\">":"") . "</td>
						<td class=\"challenge-data\" ><img src='images/avatars/{$avatar2->getAvatarImage()}'  border=\"0\" align=\"absmiddle\" /><a href=\"{$title2->getFullURL()}\">{$challenge["user_name_2"]}</a> " . (($challenge["winner_user_id"]==$challenge["user_id_2"])?"<img src=\"images/winner-check.gif\" align=\"absmiddle\">":"") . "</td>
						<td class=\"challenge-data\">{$c->getChallengeStatusName($challenge["status"])}</td>
						</tr>";
							
				}
			}else{
				$out .= "<tr><td style=\"font-size:11px\"><br>There is no current challenge history</td></tr>";
			}
		 
			$out.= "</table>";
			 
			 			/**/
			/*BUILD NEXT/PREV NAV
			**/
			$numofpages = $total_challenges  / $per_page; 
			
			if($numofpages>1 && !$wgRequest->getVal("user")){
				$challenge_history_title = Title::makeTitle( NS_SPECIAL  , "ChallengeHistory"  ); 
				$out .= "<div class=\"page-nav\">";
				if($page > 1){ 
					$out .= "<a href=\"{$challenge_history_title->getFullURL()}&user={$user_name}&page=" . ($page-1) . "\">prev</a> ";
				}
				
				
				if(($total % $per_page) != 0)$numofpages++;
				if($numofpages >=9)$numofpages=9+$page;
				
				for($i = 1; $i <= $numofpages; $i++){
					if($i == $page){
					    $out .=($i." ");
					}else{
					    $out .="<a href=\"{$challenge_history_title->getFullURL()}&user={$user_name}&page=$i\">$i</a> ";
					}
				}
		
				if(($total - ($per_page * $page)) > 0){
					$out .=" <a href=\"{$challenge_history_title->getFullURL()}&user={$user_name}&page=" . ($page+1) . "\">next</a>"; 
				}
				$out .= "</div>";
			}
			/**/
			/*BUILD NEXT/PREV NAV
			**/
			
			 $wgOut->addScript("<script type=\"text/javascript\" src=\"/extensions/wikia/Challenge/Challenge.js?{$wgStyleVersion}\"></script>\n");
			 $wgOut->addHTML($out);
		}
	}

	SpecialPage::addPage( new ChallengeHistory );
	global $wgMessageCache,$wgOut;
	$wgMessageCache->addMessage( 'challengehistory', 'Challenge History' );
}

?>