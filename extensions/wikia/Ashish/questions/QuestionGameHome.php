<?php

$wgExtensionFunctions[] = 'wfSpecialQuestionGameHome';

function wfSpecialQuestionGameHome(){

	global $wgUser,$IP;
	
	class QuestionGameHome extends SpecialPage {
	
		private $SALT;
		private $INCLUDEPATH = "/extensions/wikia/Ashish/questions/";
		
		/* Construct the MediaWiki special page */
		function QuestionGameHome(){
			UnlistedSpecialPage::UnlistedSpecialPage("QuestionGameHome");
		}
		
		// main execute function
		function execute(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			// salt at will
			$this->SALT = "SALT";
			
			$action = $wgRequest->getVal("questionGameAction");
			
			if( !$wgUser->isLoggedIn() ){
				$this->renderLoginPage();
				return;
			}
			
			switch($action){
			case "createGame":
				$this->createQuizGame();
				break;
			case "launchGame":
				$this->launchGame();
				break;
			case "renderPermalink":
				$this->launchGame();
				break;
			case "castVote":
				$this->castVote();
				break;
			case "flagItem":
				$this->adminAjaxFunctions("FLAG");
				break;
			case "editItem":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->editItem();
				}else{
					$this->renderWelcomePage();
				}
				break;
			case "completeEdit":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->completeEdit();
				}else{
					$this->renderWelcomePage();
				}
				break;
			case "deleteItem":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->adminAjaxFunctions("DELETE");
				}else{
					$this->renderWelcomePage();
				}
				break;
			case "adminPanel":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->adminPanel();
				}else{
					$this->renderWelcomePage();
				}
				break;				
			case "protectItem":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->adminAjaxFunctions("PROTECT");
				}else{
					$this->renderWelcomePage();
				}
				break;
			case "unprotectItem":
				if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
					$this->adminAjaxFunctions("UNPROTECT");
				}else{
					$this->renderWelcomePage();
				}
				break;
			default:
				$this->renderWelcomePage();
			
			}
		}
		
		function adminAjaxFunctions($action){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$wgOut->setArticleBodyOnly(true);
			
			$key = $wgRequest->getVal("quizGameKey");
			$id =  addslashes ( $wgRequest->getVal("quizGameId") );
			
			if($key != md5( $this->SALT . $id ) ){
				$output = "You need a valid key to do that.";
				$wgOut->addHTML($output);
				return;
			}
			
			$dbr =& wfGetDB( DB_MASTER );
			
			switch($action){
			case "UNPROTECT":
				$sql = "UPDATE quizgame_questions SET flag=\"NONE\" WHERE id={$id};";
				$output = "The question has been un-protected.";
				break;
			case "PROTECT":
				$sql = "UPDATE quizgame_questions SET flag=\"PROTECT\" WHERE id={$id};";
				$output = "The question has been protected.";
				break;
			case "FLAG":
				$sql = "UPDATE quizgame_questions SET flag=\"FLAGGED\" WHERE id={$id};";
				$output = "The question has been flagged.";
				break;
			case "DELETE":
				$sql = "DELETE FROM quizgame_questions WHERE id={$id};";
				$output = "Delete Succesfull!";
				break;
			default:
				$output = "Invalid AJAX option.";
				$sql = "";
				break;
			}
			//"
			$res = $dbr->query($sql);
			$wgOut->addHTML( $output );
		}
			
		function adminPanel(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$dbr =& wfGetDB( DB_MASTER );
			
			$sql = "SELECT id, username, question, options, op1, op2, op3, op4, correct_answer, picture, flag FROM quizgame_questions WHERE flag=\"FLAGGED\" OR flag=\"PROTECT\";";
			$res = $dbr->query($sql);
			//"
			$flaggedQuestions = "";
			$protectedQuestions = "";
			while ( $row = $dbr->fetchObject( $res ) ) {
				
				$options = "<ul>";
				switch($row->options){
					case 4:
						$options .= "<li>$row->op4</li>";
					case 3:
						$options .= "<li>$row->op3</li>";
					case 2:
						$options .= "<li>$row->op2</li>";
					case 1:
						$options .= "<li>$row->op1</li>";
				}
				$options .= "</ul>";
				
				if (strlen($row->picture) > 0) {
					$image = Image::newFromName( $row->picture );
					$thumb = $image->getThumbnail( 80, 0, true );
					$thumbnail = $thumb->toHtml();
				} else {
					$thumbnail = "";	
				}
				
				$key = md5( $this->SALT . $row->id );
				$buttons = "<a href=\"?title=Special:QuestionGameHome&questionGameAction=editItem&quizGameId={$row->id}&quizGameKey={$key}\">Edit</a> -
						<a href=\"javascript:deleteById('{$row->id}', '{$key}')\">Delete</a> - ";
				
				if ($row->flag == "FLAGGED") {
					$buttons .= "<a href=\"javascript:protectById('{$row->id}', '{$key}')\">Protect</a> 
						     - <a href=\"javascript:unprotectById('{$row->id}', '{$key}')\">Re-instate</a>";
				} else {
					$buttons .= "<a href=\"javascript:unprotectById({$row->id}, '{$key}')\">Unprotect</a>";
				}
				
				if($row->flag == "FLAGGED"){
				
				$flaggedQuestions .= "<div class=\"quizgame-flagged-item\" id=\"items[{$row->id}]\">
					   	
					<h1>{$row->question}</h1>
					
					<div class=\"quizgame-flagged-picture\" id=\"quizgame-flagged-picture-{$row->id}\">
						{$thumbnail}
					</div>
					
					<div class=\"quizgame-flagged-answers\" id=\"quizgame-flagged-answers-{$row->id}\">
						{$options}
					</div>
					
					<div class=\"quizgame-flagged-buttons\" id=\"quizgame-flagged-buttons\">
						{$buttons}
					</div>
					
				</div>";
				   
				
				} else {
					
				$protectedQuestions .= "<div class=\"quizgame-protected-item\" id=\"items[{$row->id}]\">
				
				   	<h1>{$row->question}</h1>

					<div class=\"quizgame-flagged-picture\" id=\"quizgame-flagged-picture-{$row->id}\">
						{$thumbnail}
					</div>
					
					<div class=\"quizgame-flagged-answers\" id=\"quizgame-flagged-answers-{$row->id}\">
						{$options}
					</div>
					
					<div class=\"quizgame-flagged-buttons\" id=\"quizgame-flagged-buttons\">
						{$buttons}
					</div>
					
				</div>";

				}
			}

			$output = "
			<script language=\"javascript\">
			
				function deleteById(id, key){
					\$('items['+id+']').hide();
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=deleteItem',
						{method:'post',
						postBody:'quizGameKey=' + key + '&quizGameId=' + id,
						onSuccess:
							function(t){	
								\$('ajax-messages').innerHTML = t.responseText;	
							},
						onFailure: 	
							function(t) { alert('Error was: ' + t.responseText); }
						});
				}
				
				function unprotectById(id, key){
					\$('items['+id+']').hide();
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=unprotectItem',
						{method:'post',
						postBody:'quizGameKey=' + key + '&quizGameId=' + id,
						onSuccess:
							function(t){	\$('ajax-messages').innerHTML = t.responseText;},
						onFailure: 	
							function(t) { alert('Error was: ' + t.responseText); }
						});
				}

				function protectById(id, key){
				new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=protectItem',
					{method:'post',
					postBody:'quizGameKey=' + key + '&quizGameId=' + id,
					onSuccess:
						function(t){	\$('ajax-messages').innerHTML = t.responseText;},
					onFailure: 	
						function(t) { alert('Error was: ' + t.responseText); }
					});
	
				}

				
			</script>";
			
			$output .= "<style>
			
			.quizgame-admin h1 {
				font-size:18px;
				color:#333333;
				font-weight:bold;
				margin:0px 0px 20px 0px !important;
			}
			
			.quizgame-admin-top-links {
				margin:-10px 0px 20px 0px;
			}
			
			.quizgame-admin-top-links a {
				text-decoration:none;
				font-weight:bold;
			}
			
			.quizgame-flagged-item {
				border-bottom:1px solid #dcdcdc;
				margin:0px 0px 20px 0px;
				padding:0px 0px 20px 0px;
				width:500px;
			}
			
			.quizgame-flagged-item h1 {
				font-size:14px;
				font-weight:bold;
				color:#333333;
				margin:0px 0px 10px 0px !important;
			}
			
			.quizgame-flagged-picture img {
				padding:3px;
				border:1px solid #dcdcdc;
			}
			
			.quizgame-flagged-question {
				margin:0px 0px 10px 0px;
			}
			
			.quizgame-flagged-answers ul {
				margin:0px 0px 15px 14px;
				padding:0px 0px 0px 0px;
			}
			
			</style>";

			$wgOut->setPagetitle("Never Ending Quiz Admin Panel");
			
			$output .= "<div class=\"quizgame-admin\" id=\"quizgame-admin\">
					
					<div class=\"ajax-messages\" id=\"ajax-messages\" style=\"color:red; font-size:16px; font-weight:bold;margin:0px 0px 15px 0px;\"></div>
					
					<div class=\"quizgame-admin-top-links\">
						<a href=\"index.php?title=Special:QuestionGameHome&questionGameAction=launchGame\">< Back to Never Ending Quiz</a>
					</div>
					
					<h1>Flagged Questions</h1>
					{$flaggedQuestions}
					
					<h1>Protected Questions</h1>
					{$protectedQuestions}
					
				  </div>";
			
			$wgOut->addHTML( $output );
		}
		
		// Completes an edit of a question"
		// updates the SQL and then forwards to the permalink
		function completeEdit(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$key = $wgRequest->getVal("quizGameKey");
			$id =  addslashes( $wgRequest->getVal("quizGameId") );
			
			if($key != md5( $this->SALT . $id ) ){
				$output = "You dont have permission to edit";
				$wgOut->addHTML($output);
				return;
			}
			
			$protect =  addslashes ( $wgRequest->getVal("quizgame-protection") );
			$question = addslashes ( $wgRequest->getVal("quizgame-question") );
			$picture = addslashes ( $wgRequest->getVal("quizGamePicture") );
			$numOptions = addslashes ( $wgRequest->getVal("quizGameOptions") );
			
			$optionFour = addslashes ( $wgRequest->getVal("quizgame-option-four") );
			$optionThree = addslashes ( $wgRequest->getVal("quizgame-option-three") );
			$optionTwo = addslashes ( $wgRequest->getVal("quizgame-option-two") );
			$optionOne = addslashes ( $wgRequest->getVal("quizgame-option-one") );
			
			$isRightOne = addslashes ( $wgRequest->getText("quizgame-isright-one") );
			$isRightTwo = addslashes ( $wgRequest->getText("quizgame-isright-two") );
			$isRightThree = addslashes ( $wgRequest->getText("quizgame-isright-three") );
			$isRightFour = addslashes ( $wgRequest->getText("quizgame-isright-four") );
			
			$isRight = array($isRightOne, $isRightTwo, $isRightThree, $isRightFour);
			
			$rightAnswer = 0;
			for($i = 0; $i < count($isRight); $i++){
				if($isRight[$i] != "")
					$rightAnswer = $i;
			}
			$rightAnswer += 1;

			
			switch($numOptions){
			case 4:
				$optionSQL = "op4=\"{$optionFour}\", op3=\"{$optionThree}\", 
						op2=\"{$optionTwo}\", op1=\"{$optionOne}\"";
				break;
			case 3:
				$optionSQL = "op3=\"{$optionThree}\", op2=\"{$optionTwo}\", op1=\"{$optionOne}\"";
				break;
			case 2:
				$optionSQL = "op2=\"{$optionTwo}\", op1=\"{$optionOne}\"";
				break;
			case 1:
				$optionSQL = "op1=\"{$optionOne}\"";
				break;
			}
			//"
			
			$id = addslashes( $id );
			$dbr =& wfGetDB( DB_MASTER );
			$sql = "UPDATE quizgame_questions SET question=\"{$question}\", picture=\"{$picture}\", correct_answer=\"{$rightAnswer}\", flag=\"{$protect}\", {$optionSQL} WHERE id={$id};";
			$res = $dbr->query($sql);
			//"
			header("Location: ?title=Special:QuestionGameHome&questionGameAction=renderPermalink&permalinkID={$id}");
			
		}
		
		// shows the edit panel for a single question
		function editItem(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$key = $wgRequest->getVal("quizGameKey");
			$id =  $wgRequest->getVal("quizGameId");
			
			if($key != md5( $this->SALT . $id ) ){
				$output = "You dont have permission to edit";
				$wgOut->addHTML($output);
				return;
			}
			
			$id = addslashes( $id );
			$dbr =& wfGetDB( DB_MASTER );
			
			$sql = "SELECT id, username, question, options, op1, op2, op3, op4, correct_answer, picture FROM quizgame_questions WHERE id={$id}";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			
			$wgOut->setPageTitle( "Editing - " . $row->question );
			
			$user_name = $row->username;
			$id = User::idFromName($user_name);
			$avatar = new wAvatar($id,"l");
			$avatarID = $avatar->getAvatarImage();
			$stats = new UserStats($id, $user_name);
			$stats_data = $stats->getUserStats();
			
			if(strlen( $row->picture ) > 0){
				$image = Image::newFromName( $row->picture );
				$thumb = $image->getThumbnail( 80, 0, true );
				$thumbtag = $thumb->toHtml();
				
				$pictag =  "<div id=\"quizgame-picture\" class=\"quizgame-picture\">" . $thumbtag . "</div>
					    <p id=\"quizgame-editpicture-link\"><a href=\"javascript:showUpload()\">Edit Picture</a></p>
					    <div id=\"quizgame-upload\" class=\"quizgame-upload\" style=\"display:none\"> 
					  	<iframe id=\"imageUpload-frame\" class=\"imageUpload-frame\" width=\"420\" scrolling=\"no\" frameborder=\"0\" src=\"?title=Special:MiniAjaxUpload&wpThumbWidth=80&wpCategory=Quizgames&wpOverwriteFile=true&wpDestFile={$row->picture}\">
						</iframe>
					    </div>";
				
			} else {
				$pictag =  "<div id=\"quizgame-picture\" class=\"quizgame-picture\"></div>
					    <div id=\"quizgame-editpicture-link\"></div>
					
					   	<div id=\"quizgame-upload\" class=\"quizgame-upload\"> 
								<iframe id=\"imageUpload-frame\" class=\"imageUpload-frame\" width=\"420\" scrolling=\"no\" frameborder=\"0\" src=\"?title=Special:MiniAjaxUpload&wpThumbWidth=80&wpCategory=Quizgames\">
								</iframe>
					    </div>
						
						</div>";
			}
			//"
			$quizOptions = array();
			
			switch($row->options){
			case 4:
				$quizOptions[] =	"<div class=\"quizgame-edit-answer\"><span class=\"quizgame-answer-number\">4.</span>
							<input name=\"quizgame-option-four\" id=\"quizgame-option-four\" type=\"text\" onChange=\"syncSelect(4)\" value=\"{$row->op4}\" size=\"32\" />
							<input type=\"checkbox\" name=\"quizgame-isright-four\" id=\"quizgame-isright-four\" " . ($row->correct_answer == "4" ? "checked" : "") . " onclick=\"javascript:toggleCheck(this)\"/>
						</div>"; 
			case 3:
				$quizOptions[] =	"<div class=\"quizgame-edit-answer\"><span class=\"quizgame-answer-number\">3.</span>
							<input name=\"quizgame-option-three\" id=\"quizgame-option-three\" type=\"text\" onChange=\"syncSelect(3)\" value=\"{$row->op3}\" size=\"32\" />
							<input type=\"checkbox\" name=\"quizgame-isright-three\" id=\"quizgame-isright-three\" " . ($row->correct_answer == "3" ? "checked" : "") . " onclick=\"javascript:toggleCheck(this)\"/>
						</div>";
			case 2:
				$quizOptions[] =	"<div class=\"quizgame-edit-answer\"><span class=\"quizgame-answer-number\">2.</span>
							<input name=\"quizgame-option-two\" id=\"quizgame-option-two\" type=\"text\" onChange=\"syncSelect(2)\" value=\"{$row->op2}\" size=\"32\" />
							<input type=\"checkbox\" name=\"quizgame-isright-two\" id=\"quizgame-isright-two\" " . ($row->correct_answer == "2" ? "checked" : "") . " onclick=\"javascript:toggleCheck(this)\"/>
						</div>";
			case 1:
				$quizOptions[] =	"<div class=\"quizgame-edit-answer\"><span class=\"quizgame-answer-number\">1.</span>
							<input name=\"quizgame-option-one\" id=\"quizgame-option-one\" type=\"text\" onChange=\"syncSelect(1)\" value=\"{$row->op1}\" size=\"32\" />
							<input type=\"checkbox\" name=\"quizgame-isright-one\" id=\"quizgame-isright-one\" " . ($row->correct_answer == "1" ? "checked" : "") . " onclick=\"javascript:toggleCheck(this)\"/>
						</div>";				
			}
			//"
			$quizOptions = array_reverse($quizOptions);
			
			$quizOptions = implode("\n", $quizOptions);
			
			$output = "<div class=\"quizgame-edit-container\" id=\"quizgame-edit-container\">
				
					<script language=\"javascript\">
					
					function toggleCheck(thisBox){
						$('quizgame-isright-one').checked = false;
						$('quizgame-isright-two').checked = false;
						$('quizgame-isright-three').checked = false;
						$('quizgame-isright-four').checked = false;
						
						thisBox.checked = true;
					}
					
					function uploadError(message){
						$('ajax-messages').innerHTML = message;
						$('quizgame-picture').innerHTML = '';
						
						$('imageUpload-frame').src = '?title=Special:MiniAjaxUpload&wpThumbWidth=80&wpCategory=Quizgames&wpOverwriteFile=true&wpDestFile=' + \$F('quizGamePicture');
						$('quizgame-upload').show();
					}
					
					function completeImageUpload(){
						$('quizgame-upload').hide();
						$('quizgame-picture').innerHTML = '<img src=\"../../images/common/ajax-loader-white.gif\"\>';
					}
					
					function uploadComplete(imgSrc, imgName, imgDesc){
						$('quizgame-picture').innerHTML = imgSrc;
						
						$('quizgame-picture').down().src = $('quizgame-picture').down().src + '?' + Math.floor( Math.random()*100 );
						
						document.quizGameEditForm.quizGamePicture.value = imgName;
						
						$('imageUpload-frame').src = '?title=Special:MiniAjaxUpload&wpThumbWidth=80&wpCategory=Quizgames&wpOverwriteFile=true&wpDestFile=' + imgName;
						
						$('quizgame-editpicture-link').innerHTML = '<a href=\"javascript:showUpload()\">Edit Picture</a>';
						$('quizgame-editpicture-link').show();
					}
					
					function showUpload(){
						$('quizgame-editpicture-link').hide();
						$('quizgame-upload').show();	
					}
					
					</script>
					
					<style>
						
						.quizgame-picture {
							margin:0px 0px 10px 0px;
						}
						
						.quizgame-edit-question input {
							padding:0px 0px 0px 2px;
						}
						
						.quizgame-edit-question h1 {
							font-size:16px;
							font-weight:bold;
							color:#333333;
							margin:20px 0px 10px 0px !important;
						}
						
						.quizgame-picture img {
							border:1px solid #dcdcdc;
							padding:3px;
						}
						
						.quizgame-copyright-warning {
							margin:10px 0px 0px 0px;
							color:#797979;
							font-size:11px;
							width:500px;
						}
						
						.quizgame-edit-answer {
							margin:0px 0px 10px 0px;
						}
						
						.quizgame-answer-number {
							color:#999999;
							font-size:16px;
							font-weight:bold;
							margin:0px 10px 0px 0px;
						}
						
						.credit-box {
							background-color:#F2F4F7;
							border:1px solid #DCDCDC;
							margin:8px 0px 0px;
							padding:10px;
							width:300px;
						}

						.credit-box h1 {
							color:#333333;
							font-size:14px;
							margin:0px 0px 10px !important;
						}

						.submitted-by-image {
							float:left;
							width:60px;
						}

						.submitted-by-user {
							float:left;
						}

						.submitted-by-user img {
							vertical-align:middle;
						}

						.submitted-by-user a {
							font-size:14px;
							font-weight:bold;
							text-decoration:none;
						}

						.submitted-by-user ul {
							list-style:none;
							margin:3px 0px 0px;
							padding:0px;
						}

						.submitted-by-user li {
							color:#333333;
							float:left;
							font-size:12px;
							font-weight:bold;
							margin:0px 6px 0px 0px;
						}
						
						.imageUpload-frame {
							height:70px;
						}
						
					</style>
				
					<div class=\"quizgame-edit-question\" id=\"quizgame-edit-question\">
						<form name=\"quizGameEditForm\" id=\"quizGameEditForm\" method=\"post\" action=\"?title=Special:QuestionGameHome&questionGameAction=completeEdit\">
							
							<div class=\"credit-box\" id=\"creditBox\">
								<h1>Submitted By</h1>

								<div id=\"submitted-by-image\" class=\"submitted-by-image\">
									<a href=\"index.php?title=User:{$user_name}\">
									<img src=images/avatars/{$avatarID} style=\"border:1px solid #d7dee8; width:50px; height:50px;\"/></a>
								</div>

								<div id=\"submitted-by-user\" class=\"submitted-by-user\">
									<div id=\"submitted-by-user-text\"><a href=\"index.php?title=User:{$user_name}\">{$user_name}</a></div>							
									<ul>
										<li id=\"userstats-votes\">
											<img src=\"images/common/voteIcon.gif\" border=\"0\"> {$stats_data["votes"]}
										</li>
										<li id=\"userstats-edits\">
											<img src=\"images/common/pencilIcon.gif\" border=\"0\"> {$stats_data["edits"]}
										</li>
										<li id=\"userstats-comments\">
											<img src=\"images/common/commentsIcon.gif\" border=\"0\"> {$stats_data["comments"]}
										</li>
									</ul>
								</div>
								<div class=\"cleared\"></div>
							</div>
							
							<div class=\"ajax-messages\" id=\"ajax-messages\" style=\"color:red; font-size:16px; font-weight:bold;margin:20px 0px 15px 0px;\"></div>
							
							<h1>Question</h1>
							<input name=\"quizgame-question\" id=\"quizgame-question\" type=\"text\" value=\"{$row->question}\" size=\"64\" />
							<h1>Answers</h1>
							<div style=\"margin:10px 0px;\">The correct answer is checked.</div>
							{$quizOptions}
							<h1>Picture</h1>
							<div class=\"quizgame-edit-picture\" id=\"quizgame-edit-picture\">
								{$pictag}
							</div>
							
							<input id=\"quizGamePicture\" name=\"quizGamePicture\" type=\"hidden\" value=\"{$row->picture}\" />
							<input id=\"quizGameOptions\" name=\"quizGameOptions\" type=\"hidden\" value=\"{$row->options}\" />
							<input id=\"quizGameId\" name=\"quizGameId\" type=\"hidden\" value=\"{$row->id}\" />
							<input id=\"quizGameKey\" name=\"quizGameKey\" type=\"hidden\" value=\"{$key}\" />
							
						</form>
					</div>
					
					<div class=\"quizgame-copyright-warning\">".wfMsgExt("copyrightwarning","parse")."</div>
					
					<div class=\"quizgame-edit-buttons\" id=\"quizgame-edit-buttons\">
						<input type=\"button\" class=\"site-button\" value=\"Save Page\" onclick=\"javascript:document.quizGameEditForm.submit()\"/>
						<input type=\"button\" class=\"site-button\" value=\"Cancel\" onclick=\"javascript:document.location='index.php?title=Special:QuestionGameHome&questionGameAction=launchGame'\"/>
					</div>
				</div>
				";
			
			$wgOut->addHTML($output);
		}
		//" present some log in message
		function renderLoginPage(){
			global $wgOut;
			
			$wgOut->setPageTitle("You Must Be Logged in to Play the Never Ending Quiz");
			
			$output = "You need to log in, to play the never ending quiz!";
			$output .= "<div>
				<input type=\"button\" class=\"site-button\" value=\"Main Page\" onclick=\"window.location='index.php?title=Main_Page'\"/> 
				<input type=\"button\" class=\"site-button\" value=\"Log In\" onclick=\"window.location='index.php?title=Special:Login'\"/>
			</div>";
			$wgOut->addHTML($output);
		}
		
		// casts a vote by inserting some SQL
		// returns the next question as well as stats about previous question in JSON
		function castVote(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$wgOut->setArticleBodyOnly(true);
			
			$key = $wgRequest->getVal("quizGameKey");
			$id =  $wgRequest->getVal("quizGameId");
			
			if($key != md5( $this->SALT . $id ) ){
				$err = '
				{
					"status": "500",
					"error": "Key is invalid!"
				}';
				
				$wgOut->addHTML($err);
				return;
			}
			
			$id = addslashes ( $id );
			$answer = addslashes ( $wgRequest->getVal("quizGameAnswer") );
			
			if( !is_numeric($answer) ){
				$err = '
				{
					"status": "500",
					"error": "Answer choice is not numeric."
				}';
				
				$wgOut->addHTML($err);
				return;
			}
			
			$dbr =& wfGetDB( DB_MASTER );
			
			$answerPick = "res" . $answer;
			$sql = "UPDATE quizgame_questions SET {$answerPick}={$answerPick}+1 WHERE id={$id};"; 
			$res = $dbr->query($sql);
			
			$sql = "INSERT INTO quizgame_answers (quizid, userid, option_picked) 
					VALUES('{$id}', '{$wgUser->getID()}', '{$answer}');";
			
			$res = $dbr->query($sql);
			
			$sql = "SELECT correct_answer AS answer FROM quizgame_questions WHERE id={$id};";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			
			$isRight = ($row->answer == $answer) ? "true" : "false";
			
			$output = "{'isRight': '{$isRight}', 'rightAnswer':'{$row->answer}'}";
			
			$wgOut->addHTML( $output );
			
		}
		
		function renderPermalinkError(){
			global $wgOut;
			
			$output = "Sorry this question is unavailable!";
			$wgOut->addHTML($output);	
		}
		
		// main function to render a game
		// also handles rendering a permalink
		function launchGame(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			// controls the maximum length of the previous game bar graphs"
			$dbr =& wfGetDB( DB_MASTER );
			
			$permalinkID = addslashes ( $wgRequest->getVal("permalinkID") );
			$lastid = addslashes ( $wgRequest->getVal("lastid") );
			$skipid = addslashes ( $wgRequest->getVal("skipid") );
			
			$isPermalink = is_numeric($permalinkID);
			$isFixedlink = false;
			$permalinkOptions = -1;
			$backButton = "";
			$editMenu = "";
			$editLinks = "";
			
			// initialize the variables
			$correctAnswer = "";
			$correctAnswerText = "";
			
			$answerOne = "";
			$answerOnePercent = "";
			$answerOneWidth = "";
			
			$answerTwo = "";
			$answerTwoPercent = "";
			$answerTwoWidth = "";
			
			$answerThree = "";
			$answerThreePercent = "";
			$answerThreeWidth = "";
			
			$answerFour = "";
			$answerFourPercent = "";
			$answerFourWidth = "";
			
			$answerBarWidth = "300.0";
			
			// this is assuming that lastid and permalinkid 
			// are mutually exclusive
			
			if( $isPermalink || is_numeric($lastid) ){
				
				$selectID = ($isPermalink ? $permalinkID : $lastid);
				
				$sql = "SELECT *, (res1+res2+res3+res4) as total FROM quizgame_questions WHERE id={$selectID} AND flag !='FLAGGED';";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );
				//"
				
				if( !$row && $isPermalink ){
					$this->renderPermalinkError();
					return;
				}
				
				if($row->total > 0){
					$isFixedlink = true;
					$numericCorrectAnswer = $row->correct_answer;
					
					switch($numericCorrectAnswer){
					case 1:
						$correctAnswer = $row->op1;
						break;
					case 2:
						$correctAnswer = $row->op2;
						break;
					case 3:
						$correctAnswer = $row->op3;
						break;
					case 4:
						$correctAnswer = $row->op4;
						break;
					}
					
					$correctAnswerText = "The correct answer is " . $correctAnswe;
					
					$permalinkOptions = $row->options;
					$answerFour = $row->res4;
					$answerThree = $row->res3;
					$answerTwo = $row->res2;
					$answerOne = $row->res1;
					$total = $row->total;
					
					switch($row->options){
					case 4:					
						$answerFourText = $row->op4 . " ({$answerFour})";
						if( $total != 0 )
							$answerFourPercent = $answerFour / $total;
						else
							$answerFourPercent = 0;
						
						$answerFourPercentText = ceil($answerFourPercent * 100) . "%";
						$answerFourWidth = ceil( $answerBarWidth * $answerFourPercent ) . "px";
					case 3:
						$answerThreeText = $row->op3 . " ({$answerThree})";
						if( $total != 0 )
							$answerThreePercent = $answerThree / $total;
						else
							$answerThreePercent = 0;
							
						$answerThreePercentText = ceil($answerThreePercent * 100)  . "%";
						$answerThreeWidth = ceil ( $answerBarWidth * $answerThreePercent ) . "px";
					case 2:
						$answerTwoText = $row->op2 . " ({$answerTwo})";;
						if( $total != 0 )
							$answerTwoPercent = $answerTwo / $total;
						else
							$answerTwoPercent = 0;
						
						$answerTwoPercentText = ceil($answerTwoPercent * 100) . "%";
						$answerTwoWidth = ceil( $answerBarWidth * $answerTwoPercent ) . "px";
					case 1:					
						$answerOneText = $row->op1 . " ({$answerOne})";;
						if( $total != 0 )
							$answerOnePercent = $answerOne / $total;
						else
							$answerOnePercent = 0;
							
						$answerOnePercentText = ceil($answerOnePercent * 100)  . "%";
						$answerOneWidth = ceil( $answerBarWidth * $answerOnePercent ) . "px";
					}
				}							
			}
			
			if( !$isPermalink ){
				
				if( is_numeric($lastid) ){
					$sql = "SELECT id, username, question, options, op1, op2, op3, op4, picture FROM quizgame_questions WHERE flag != 'FLAGGED' AND id <> {$lastid} ORDER BY RAND() LIMIT 1";
					$backButton = "<a href=\"javascript:history.go(-1)\">Back</a>";
				}else if( is_numeric($skipid) ){
					$sql = "SELECT id, username, question, options, op1, op2, op3, op4, picture FROM quizgame_questions WHERE flag != 'FLAGGED' AND id <> {$skipid} ORDER BY RAND() LIMIT 1";
					$backButton = "<a href=\"javascript:history.go(-1)\">Back</a>";					
				}else{
					$sql = "SELECT id, username, question, options, op1, op2, op3, op4, picture FROM quizgame_questions WHERE flag != 'FLAGGED' ORDER BY RAND() LIMIT 1";
				}
				//"
				
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );
			}
						
			$gameid = $row->id;
			$wgOut->setHTMLTitle( wfMsg( 'pagetitle', $row->question ) );
			
			if(strlen($row->picture) > 0){
				$image = Image::newFromName( $row->picture );
				$imageThumb = $image->createThumb(160);
				$imageThumb .= "?" . time();
				$imageTag = "
				<div id=\"quizgame-picture\" class=\"quizgame-picture\">
					<img src='" . $imageThumb . "' width='" . 
						($image->getWidth() >= 160 ? 160 : $image->getWidth() ) . "'></div>";
			}else{
				$imageTag = "";
			}
			
			$key = md5( $this->SALT . $row->id );
			
			$user_name = $row->username;
			$id = User::idFromName($user_name);
			$avatar = new wAvatar($id,"l");
			$avatarID = $avatar->getAvatarImage();
			$stats = new UserStats($id, $user_name);
			$stats_data = $stats->getUserStats();
			
			if( $wgUser->isLoggedIn() && $wgUser->isAllowed("delete") ) {
				$editMenu = "
					<div class=\"edit-menu-quiz-game\">
						<div class=\"edit-button-quiz-game\">
						<img src=\"../../images/common/editIcon.gif\"/>
						<a href=\"javascript:showEditMenu()\">Edit</a>
					</div></div>";
				
				$editLinks = "
				    <a href=\"?title=Special:QuestionGameHome&questionGameAction=adminPanel\">Admin Panel</a> -
					<a href=\"javascript:protectImage()\">Protect</a> -
					<a href=\"javascript:deleteQuestion()\">Delete</a> -";
			}
			
			$answers = "<ul>";
			
			switch($row->options){
			
			case 4:
				$answers .= "<li id=\"4\"><a href=\"javascript:vote(4);\">" . $row->op4 . "</a></li>";
			case 3:
				$answers .= "<li id=\"3\"><a href=\"javascript:vote(3);\">" . $row->op3 . "</a></li>";
			case 2:
				$answers .= "<li id=\"2\"><a href=\"javascript:vote(2);\">" . $row->op2 . "</a></li>";
			case 1:
				$answers .= "<li id=\"1\"><a href=\"javascript:vote(1);\">" . $row->op1 . "</a></li>";
			}
			
			$answers .= "</ul>";
			
			$output = "
			<script type=\"text/javascript\" src=\"{$this->INCLUDEPATH}lightbox_light.js\"></script>
			
			<script language=\"javascript\">
				function detectMacXFF() {
				  var userAgent = navigator.userAgent.toLowerCase();
				  if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox')!=-1) {
				    return true;
				  }
				}

				function deleteQuestion(){
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=deleteItem',
						{method:'post',
						postBody:'quizGameKey=' + \$F( 'quizGameKey' ) + '&quizGameId=' + \$F( 'quizGameId' ),
						onSuccess:
							function(t){	
								\$('ajax-messages').innerHTML = t.responseText + '<br /> Re-loading...';
								document.location = '?title=Special:QuestionGameHome&questionGameAction=launchGame';
								},
						onFailure: 	
							function(t) { alert('Error was: ' + t.responseText); }
						});
				}
			
				function showEditMenu(){
					document.location = \"?title=Special:QuestionGameHome&questionGameAction=editItem&quizGameId=\" + 
							     \$F( 'quizGameId' ) + \"&quizGameKey=\" + \$F( 'quizGameKey' );
				}
			
				function flagQuestion(){
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=flagItem',
						{method:'post',
						postBody:'quizGameKey=' + \$F( 'quizGameKey' ) + '&quizGameId=' + \$F( 'quizGameId' ),
						onSuccess:
							function(t){	\$('ajax-messages').innerHTML = t.responseText;},
						onFailure: 	
							function(t) { alert('Error was: ' + t.responseText); }
						});

				}
				
				function protectImage(){	
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=protectItem',
						{method:'post',
						postBody:'quizGameKey=' + \$F( 'quizGameKey' ) + '&quizGameId=' + \$F( 'quizGameId' ),
						onSuccess:
							function(t){	\$('ajax-messages').innerHTML = t.responseText;},
						onFailure: 	
							function(t) { alert('Error was: ' + t.responseText); }
						});
				}
				
				// casts a vote and forwards the user to a new question
				function vote(id){
					
					\$('ajax-messages').innerHTML = '';
					
					objLink = new Object();
					// objLink.href = \"../../images/common/ajax-loader.gif\"
					objLink.href = \"#\";
					objLink.title = \"Loading...\";
					
					showLightbox(objLink);
					
					if( !detectMacXFF() ){
					setLightboxText( '<embed src=\"/extensions/wikia/Ashish/questions/ajax-loading.swf\" quality=\"high\" wmode=\"transparent\" bgcolor=\"#ffffff\"' + 
						'pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\"' + 
						'type=\"application/x-shockwave-flash\" width=\"100\" height=\"100\"></embed><br /><br /><p>&nbsp;</p>');
					}else{
						setLightboxText( 'Loading...');
					}
					new Ajax.Request('?title=Special:QuestionGameHome&questionGameAction=castVote',
					{method:'post',
					postBody:'quizGameKey=' + \$F( 'quizGameKey' ) + '&quizGameId=' + \$F( 'quizGameId' ) + '&quizGameAnswer=' + id,
					onSuccess:function(t){
							var payload = eval('(' + t.responseText + ')');
							
							window.location = '?title=Special:QuestionGameHome&questionGameAction=launchGame&lastid=' + \$F( 'quizGameId' );
							
							if( payload.isRight == 'true'){
								if( !detectMacXFF() ){
								setLightboxText( '<embed src=\"/extensions/wikia/Ashish/questions/ajax-loading.swf\" quality=\"high\" wmode=\"transparent\" bgcolor=\"#ffffff\"' + 
									'pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\"' + 
									'type=\"application/x-shockwave-flash\" width=\"100\" height=\"100\"></embed><br /><br /><p class=\"quizgame-lightbox-righttext\">Nice job, you answered the question right!</p>');
								}else{
									setLightboxText( 'Loading...');
								}
							}else{
								if( !detectMacXFF() ){
									setLightboxText( '<embed src=\"/extensions/wikia/Ashish/questions/ajax-loading.swf\" quality=\"high\" wmode=\"transparent\" bgcolor=\"#ffffff\"' + 
									'pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\"' + 
									'type=\"application/x-shockwave-flash\" width=\"100\" height=\"100\"></embed><br /><br /><p class=\"quizgame-lightbox-wrongtext\">Sorry, you answered the question wrong! The answer is ' + 
									\$(payload.rightAnswer).down().innerHTML + '</p>');
								}else{
									setLightboxText( 'Loading...');
								}

							}
							
						   	}
					,
					onFailure: 	function(t) { alert('Error was: ' + t.responseText); }
				});
			}
			</script>
			
			<style>
			
			#lightbox {
				display:none;
				padding:10px;
			}
			#lightbox img {
				background-color:transparent;
				border:medium none;
				clear:both;
				display:none;
			}
			
			#lightboxText {
				color:white;
				width:auto;
			}
			
			#overlay img {
				border:medium none;
			}
		
			#overlay {
				background-color:transparent;
				background-image:url('" . $this->INCLUDEPATH . "overlay.png');
			}
			
			* html #overlay {
				background-color:transparent;
				background-image:url(blank.gif);
				}
			
			li:hover{
				color:red;
				cursor:pointer;
			}
			
			.quizgame-lightbox-wrongtext{
				color:red;	
			}
			
			.quizgame-lightbox-righttext{
				color:white;	
			}

			
			.quizgame-left {
				float:left;
				width:500px;
			}
			
			.quizgame-right {
				float:right;
				width:300px;
			}
			
			.quizgame-title {
				font-family:arial;
				font-size:22px;
				font-weight:bold;
				margin:-3px 0px 30px;
				line-height:100%;
			}
			
			.quizgame-picture {
				margin:0px 0px 20px 0px;
			}
			
			.quizgame-answers {
				font-size:16px;
				font-weight:bold;
			}
			
			.quizgame-answers ul {
				margin:0px 0px 0px 14px;
				padding:0px;
			}
			
			.quizgame-answers li {
				margin:0px 0px 5px;
			}
			
			.quizgame-answers a {
				text-decoration:none;
			}
			
			.credit-box {
				background-color:#F2F4F7;
				border:1px solid #DCDCDC;
				margin:8px 0px 0px;
				padding:10px;
			}
			
			.credit-box h1 {
				color:#333333;
				font-size:14px;
				margin:0px 0px 10px !important;
			}
			
			.submitted-by-image {
				float:left;
				width:60px;
			}
			
			.submitted-by-user {
				float:left;
			}
			
			.submitted-by-user img {
				vertical-align:middle;
			}
			
			.submitted-by-user a {
				font-size:14px;
				font-weight:bold;
				text-decoration:none;
			}
			
			.submitted-by-user ul {
				list-style:none;
				margin:3px 0px 0px;
				padding:0px;
			}
			
			.submitted-by-user li {
				color:#333333;
				float:left;
				font-size:12px;
				font-weight:bold;
				margin:0px 6px 0px 0px;
			}
			
			.last-game {
				margin:10px 0px 0px 0px;
			}
			
			.last-game h1 {
				color:#333333;
				font-size:14px;
				margin:0px 0px 5px !important;
			}
			
			.correct-anwser {
				font-weight:bold;
				margin:0px 0px 5px 0px;
			}
			
			.answer-blue img {
				border-bottom:2px solid #000000;
				border-right:2px solid #000000;
				border-top:2px solid #82C3FF;
				vertical-align:middle;
			}
			
			.answer-red img {
				border-bottom:2px solid #000000;
				border-right:2px solid #000000;
				border-top:2px solid #F0B4AB;
				vertical-align:middle;
			}
			
			.small-answer {
				color:#797979;
				font-weight:bold;
				margin:2px 0px 1px 0px;
			}
			
			.bottom-links {
				margin:10px 0px 0px 0px;
			}
			
			.bottom-links a {
				text-decoration:none;
			}
			
			.edit-menu-quiz-game {
				float:right;
				margin:5px 0px 0px 10px;
				position:relative;
			}
			
			.edit-button-quiz-game {
				background-color:#FFFCA9;
				border:1px solid #FDC745;
				padding:5px 0px 5px 5px;
				width:60px;
			}
			
			.edit-button-quiz-game img {
				margin:-3px 3px 0px 0px;
				vertical-align:middle;
			}
			
			.edit-button-quiz-game a {
				font-size:14px;
				font-weight:bold;
				text-decoration:none;
			}
			
			.create-link {
				margin:0px 0px 5px 0px;
			}
			
			.create-link a {
				text-decoration:none;
			}
			
			.create-link img {
				margin:-1px 2px 0px 0px ;
				vertical-align:middle;
			}
			
			.navigation-buttons {
				margin:20px 0px 0px 0px;
				font-size:14px;
				font-weight:bold;
			}
			
			.navigation-buttons a {
				margin:0px 20px 0px 0px;
			}
			
			</style>
			
			<div id=\"quizgame-container\" class=\"quizgame-container\">
				{$editMenu}
			<div id=\"quizgame-title\" class=\"quizgame-title\">
						{$row->question}
					</div>
				
					<div class=\"ajax-messages\" id=\"ajax-messages\" style=\"color:red; font-size:16px; font-weight:bold;margin:0px 0px 15px 0px;\"></div>
			
					<div class=\"quizgame-left\">
						
						{$imageTag}
						
						<div id=\"quizgame-answers\" class=\"quizgame-answers\">
							{$answers}
						</div>
						
						<form name=\"quizGameForm\" id=\"quizGameForm\">
							<input id=\"quizGameId\" name=\"quizGameId\" type=\"hidden\" value=\"{$gameid}\" />
							<input id=\"quizGameKey\" name=\"quizGameKey\" type=\"hidden\" value=\"{$key}\" />
						</form>
					
						<div class=\"navigation-buttons\">
							{$backButton}
							<a href=\"?title=Special:QuestionGameHome&questionGameAction=launchGame&skipid={$gameid}\">Skip</a>
						</div>
					
					</div>
					
					<div class=\"quizgame-right\">
					
						<div class=\"create-link\"><img border=\"0\" src=\"images/common/addIcon.gif\"/> <a href=\"index.php?title=Special:QuestionGameHome\">Create a Quiz Question</a></div>
						<div class=\"credit-box\" id=\"creditBox\">
							<h1>Submitted By</h1>
							
							<div id=\"submitted-by-image\" class=\"submitted-by-image\">
								<a href=\"index.php?title=User:{$user_name}\">
								<img src=images/avatars/{$avatarID} style=\"border:1px solid #d7dee8; width:50px; height:50px;\"/></a>
							</div>
						
							<div id=\"submitted-by-user\" class=\"submitted-by-user\">
								<div id=\"submitted-by-user-text\"><a href=\"index.php?title=User:{$user_name}\">{$user_name}</a></div>							
								<ul>
									<li id=\"userstats-votes\">
										<img src=\"images/common/voteIcon.gif\" border=\"0\"> {$stats_data["votes"]}
									</li>
									<li id=\"userstats-edits\">
										<img src=\"images/common/pencilIcon.gif\" border=\"0\"> {$stats_data["edits"]}
									</li>
									<li id=\"userstats-comments\">
										<img src=\"images/common/commentsIcon.gif\" border=\"0\"> {$stats_data["comments"]}
									</li>
								</ul>
								</div>
								<div class=\"cleared\"></div>
							</div>
					
							<div id=\"answer-stats\" class=\"answer-stats\" style=\"display:" . ($isFixedlink ? "block" : "none") . "\">
				
								<div class=\"last-game\">
							
									<h1>Game Stats</h1>
							
									<div class=\"correct-anwser\" id=\"correct-anwser\">{$correctAnswerText}</div>
							
									<div class=\"answer-bar\" id=\"answer-bar-one\" style=\"display:" . ($permalinkOptions >= 1 ? "block" : "none") . "\">
										<div id=\"one-answer\" class=\"small-answer\">{$answerOneText}</div>
										<span id=\"one-answer-bar\" class=\"answer-".($numericCorrectAnswer == "1" ? "blue" : "red")."\">
											<img border=\"0\" style=\"width: {$answerOneWidth}; height: 9px;\" id=\"one-answer-width\" src=\"../../images/common/vote-bar-" . ($numericCorrectAnswer == "1" ? "blue" : "red") . ".gif\"/> 
											<span id=\"one-answer-percent\" class=\"answer-percent\">{$answerOnePercentText}</span>
										</span>
									</div>
		
									<div class=\"answer-bar\" id=\"answer-bar-two\" style=\"display:" . ($permalinkOptions >= 2 ? "block" : "none") . "\">
										<div id=\"two-answer\" class=\"small-answer\">{$answerTwoText}</div>
										<span id=\"two-answer-bar\" class=\"answer-".($numericCorrectAnswer == "2" ? "blue" : "red")."\">
											<img border=\"0\" style=\"width: {$answerTwoWidth}; height: 9px;\" id=\"two-answer-width\" src=\"../../images/common/vote-bar-".($numericCorrectAnswer == "2" ? "blue" : "red").".gif\"/> 
											<span id=\"two-answer-percent\" class=\"answer-percent\">{$answerTwoPercentText}</span>
										</span>
									</div>
		
									<div class=\"answer-bar\" id=\"answer-bar-three\" style=\"display:" . ($permalinkOptions >= 3 ? "block" : "none") . "\">
										<div id=\"three-answer\" class=\"small-answer\">{$answerThreeText}</div>
										<span id=\"three-answer-bar\" class=\"answer-".($numericCorrectAnswer == "3" ? "blue" : "red")."\">
											<img border=\"0\" style=\"width: {$answerThreeWidth}; height: 9px;\" id=\"three-answer-width\" src=\"../../images/common/vote-bar-" . ($numericCorrectAnswer == "3" ? "blue" : "red") . ".gif\"/>
										</span>
										<span id=\"three-answer-percent\" class=\"answer-percent\">{$answerThreePercentText}</span>
									</div>
		
									<div class=\"answer-bar\" id=\"answer-bar-four\" style=\"display:" . ($permalinkOptions == 4 ? "block" : "none") . "\">
										<div id=\"four-answer\" class=\"small-answer\">{$answerFourText}</div>
										<span id=\"four-answer-bar\" class=\"answer-".($numericCorrectAnswer == "4" ? "blue" : "red")."\">
											<img border=\"0\" style=\"width: {$answerFourWidth}; height: 9px;\" id=\"four-answer-width\" src=\"../../images/common/vote-bar-" . ($numericCorrectAnswer == "4" ? "blue" : "red") . ".gif\"/>
										</span>
										<span id=\"four-answer-percent\" class=\"answer-percent\">{$answerFourPercentText}</span>
									</div>
							</div>
						</div>
				
						<div class=\"bottom-links\" id=\"utility-buttons\">
							<a href=\"javascript:flagQuestion()\">Flag</a> -
							{$editLinks}
							<a href=\"javascript:document.location='?title=Special:QuestionGameHome&questionGameAction=renderPermalink&permalinkID=' + \$F( 'quizGameId' )\">Permalink</a>
						</div>
							
					</div>
				</div>
				
				<div class=\"cleared\"/>
				<div class=\"hiddendiv\" style=\"display:none\">
					<img src=\"{$this->INCLUDEPATH}overlay.png\">
				</div>
			</div>";
			
			$wgOut->addHTML($output);
		}
		
		//" function that inserts questions into the database
		function createQuizGame(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$key = $wgRequest->getText("key");
			$chain = $wgRequest->getText("chain");
			
			if($key != md5( $this->SALT . $chain ) ){
				header( 'Location: ?title=Special:QuestionGameHome' ) ;
				return;
			}
			
			$question = addslashes ( $wgRequest->getText("quizgame-question") );
			
			$imageName = addslashes ( $wgRequest->getText("quizGamePictureName") );
			
			$answerOne = addslashes ( $wgRequest->getText("quizgame-answer-one") );
			$answerTwo = addslashes ( $wgRequest->getText("quizgame-answer-two") );
			$answerThree = addslashes ( $wgRequest->getText("quizgame-answer-three") );
			$answerFour = addslashes ( $wgRequest->getText("quizgame-answer-four") );
			
			$isRightOne = addslashes ( $wgRequest->getText("quizgame-isright-one") );
			$isRightTwo = addslashes ( $wgRequest->getText("quizgame-isright-two") );
			$isRightThree = addslashes ( $wgRequest->getText("quizgame-isright-three") );
			$isRightFour = addslashes ( $wgRequest->getText("quizgame-isright-four") );
			
			$answers = array($answerOne, $answerTwo, $answerThree, $answerFour);
			$isRight = array($isRightOne, $isRightTwo, $isRightThree, $isRightFour);
			
			$numAnswers = 0;
			
			foreach($answers as $i){
				if(strlen($i) > 0)
					$numAnswers ++;
			}
			
			$rightAnswer = 0;
			for($i = 0; $i < count($isRight); $i++){
				if($isRight[$i] != "")
					$rightAnswer = $i;
			}
			$rightAnswer += 1;
			
			$dbr =& wfGetDB( DB_MASTER );
			
			$sql = "SELECT COUNT(*) AS mycount FROM quizgame_questions WHERE question=\"{$question}\";";
			$res = $dbr->query($sql);
			$row = $dbr->fetchObject( $res );
			
			if($row->mycount == 0){
				/* Not sure about this
				$sql = "SELECT UNIX_TIMESTAMP() as time;";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );
				*/
				
				$mysqlDate = date('Y-m-d H:i:s');
				
				$sql = "INSERT INTO quizgame_questions 
				(userid, username, question, options, op1, op2, op3, op4, correct_answer, picture, timestamp) 
				VALUES('{$wgUser->getID()}', '{$wgUser->getName()}', '{$question}', '{$numAnswers}', 
					'{$answerOne}', '{$answerTwo}', '{$answerThree}', '{$answerFour}', '{$rightAnswer}', '{$imageName}', '{$mysqlDate}');";
				
				$res = $dbr->query($sql);
				//"
				$sql = "SELECT MAX(id) as maxid FROM quizgame_questions;";
				$res = $dbr->query($sql);
				$row = $dbr->fetchObject( $res );

				header( 'Location: ?title=Special:QuestionGameHome&questionGameAction=renderPermalink&permalinkID=' . $row->maxid );
				return;
			}
			
			header( 'Location: ?title=Special:QuestionGameHome&questionGameAction=launchGame' );
		}
		
		function renderWelcomePage(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			
			$chain = time();
			$key = md5( $this->SALT . $chain );
			
			$output .= "<style>
				.create-message {
					background-color:#F2F4F7;
					border:1px solid #DCDCDC;
					margin:-10px 0px 0px 0px;
					padding:9px;
					width:500px;
				}
				
				.create-message h1 {
					font-size:22px;
					font-weight:bold;
					margin:0px 0px 10px !important;
				}
				
				.quizgame-create-form h1 {
					color:#333333;
					font-size:16px;
					margin:20px 0px 15px 0px !important;
				}
				
				h1.write-answer {
					color:#333333;
					font-size:16px;
					margin:20px 0px 10px 0px !important;
				}
				
				.quizgame-answer-number {
					font-size:16px;
					font-weight:bold;
					color:#999999;
					margin:0px 10px 0px 0px;
				}
				
				.quizgame-answer {
					margin:10px 0px 10px 0px;
				}
				
				.imageUpload-frame {
					height:70px;
				}
				
			</style>";
			
			$output .= "
			<script language=\"javascript\">
			
				function uploadError(message){
					$('imageUpload-frame').src = '?title=Special:MiniAjaxUpload&wpThumbWidth=75&wpCategory=Quizgames';
					$('quizgame-picture-upload').show();
				}
				
				function completeImageUpload(){
					$('quizgame-picture-upload').hide();
					$('quizgame-picture-preview').innerHTML = '<img src=\"../../images/common/ajax-loader-white.gif\" \>';
				}
				
				function uploadComplete(imgSrc, imgName, imgDesc){
					$('quizgame-picture-preview').innerHTML = imgSrc;
					document.quizGameCreate.quizGamePictureName.value = imgName;
					$('imageUpload-frame').src = '?title=Special:MiniAjaxUpload&wpThumbWidth=75&wpCategory=Quizgames';
					$('quizgame-picture-reupload').show();
				}
			
				function showAnswerBox(id){
					Effect.Appear('quizgame-answer-' + id);
					if(id == 2)
						$('startButton').show();
				}
				
				function startGame(){
					var isError = false;
					var errorText = '';
					
					if(\$F('quizgame-question') == ''){
						isError = true;
						errorText = \"You need to enter a question!<br />\";
					}
					
					if(\$F('quizgame-answer-one') == '' || \$F('quizgame-answer-two') == ''){
						isError = true;
						errorText += \"You need at least two answer choices!<br />\";
					}
					
					if(\$F('quizgame-answer-four') != ''){
						if(\$F('quizgame-answer-three') == ''){
							isError = true;
							errorText += \"You can not have a fourth option without a third!<br />\";
						}
						
						if(\$F('quizgame-answer-two') == ''){
							isError = true;
							errorText += \"You can not have a fourth option without a second!<br />\";
						}
						
						if(\$F('quizgame-answer-one') == ''){
							isError = true;
							errorText += \"You can not have a fourth option without a first!<br />\";
						}
					}
					
					if(\$F('quizgame-answer-three') != ''){
						if(\$F('quizgame-answer-two') == ''){
							isError = true;
							errorText += \"You can not have a third option without a second!\";
						}
						
						if(\$F('quizgame-answer-one') == ''){
							isError = true;
							errorText += \"You can not have a third option without a first!\";
						}
					}
					
					if(\$F('quizgame-answer-two') != ''){
						if(\$F('quizgame-answer-one') == ''){
							isError = true;
							errorText += \"You can not have a first option without a second!\";
						}
					}
					
					if( $('quizgame-isright-one').checked && \$F('quizgame-answer-one') == '' ){
						isError = true;
						errorText += \"The right answer can't be blank!\";
					}
					
					if( $('quizgame-isright-two').checked && \$F('quizgame-answer-two') == '' ){
						isError = true;
						errorText += \"The right answer can't be blank!\";
					}

					if( $('quizgame-isright-three').checked && \$F('quizgame-answer-three') == '' ){
						isError = true;
						errorText += \"The right answer can't be blank!\";
					}

					if( $('quizgame-isright-four').checked && \$F('quizgame-answer-four') == '' ){
						isError = true;
						errorText += \"The right answer can't be blank!\";
					}

					
					//'
					if( $('quizgame-isright-one').checked == false &&
						$('quizgame-isright-two').checked == false &&
						$('quizgame-isright-three').checked == false &&
						$('quizgame-isright-four').checked == false ){
							isError = true;
							errorText += \"You need a correct answer!\";
						}
					
					if(!isError)
						$('quizGameCreate').submit();
					else
						$('quiz-game-errors').innerHTML = \"<h2>\" + errorText + \"<h2>\";
				}
				
				function showAttachPicture(){
					$('quizgame-picture-preview').hide();
					$('quizgame-picture-reupload').hide();
					$('quizgame-picture-upload').show();
				}
				
				function toggleCheck(thisBox){
					$('quizgame-isright-one').checked = false;
					$('quizgame-isright-two').checked = false;
					$('quizgame-isright-three').checked = false;
					$('quizgame-isright-four').checked = false;
					
					thisBox.checked = true;
				}
				
				function check_iframe(e, win) {
					Element.hide('fake-form');
					Element.show('real-form');
				}
				
			</script>";
			
			$wgOut->setHTMLTitle( wfMsg( 'pagetitle', "Create a Quiz Question" ) );
			
			$output .= "<div id=\"quiz-container\" class=\"quiz-container\">
				
				<div class=\"create-message\">
					<h1>Create a Quiz Question</h1>
					<p>To add a quiz question to the never ending quiz, write a question and add some answer choices.</p>
					<input class=\"site-button\" type=\"button\" onclick=\"document.location='?title=Special:QuestionGameHome&questionGameAction=launchGame'\" value=\"Play the Never Ending Quiz\"/>
				</div>
				
				<div class=\"quizgame-create-form\" id=\"quizgame-create-form\">		
					<form id=\"quizGameCreate\" name=\"quizGameCreate\" method=\"post\" action=\"?title=Special:QuestionGameHome&questionGameAction=createGame\">
					<div id=\"quiz-game-errors\" style=\"color:red\"></div>
					
					<h1>Write a Question</h1> 
					<input name=\"quizgame-question\" id=\"quizgame-question\" type=\"text\" value=\"\" size=\"64\" />
					<h1 class=\"write-answer\">Write the Answers </h1>
					<span style=\"margin-top:10px;\">Please check the correct answer.</span>
					
					<div id=\"quizgame-answer-1\" class=\"quizgame-answer\">
						<span class=\"quizgame-answer-number\">1.</span>
						<input name=\"quizgame-answer-one\" id=\"quizgame-answer-one\" type=\"text\" value=\"\" size=\"32\" onclick=\"javascript:showAnswerBox(2)\" onselect=\"javascript:showAnswerBox(2)\" />
						<input type=\"checkbox\" onclick=\"javascript:toggleCheck(this)\" id=\"quizgame-isright-one\" name=\"quizgame-isright-one\">
					</div>
					
					<div id=\"quizgame-answer-2\" class=\"quizgame-answer\" style=\"display:block\">
						<span class=\"quizgame-answer-number\">2.</span>
						<input name=\"quizgame-answer-two\" id=\"quizgame-answer-two\"  type=\"text\" value=\"\" size=\"32\" onclick=\"javascript:showAnswerBox(3)\" onselect=\"javascript:showAnswerBox(3)\" />
						<input type=\"checkbox\" onclick=\"javascript:toggleCheck(this)\" id=\"quizgame-isright-two\" name=\"quizgame-isright-two\">
					</div>
							
					<div id=\"quizgame-answer-3\" class=\"quizgame-answer\" style=\"display:none\">
						<span class=\"quizgame-answer-number\">3.</span> 
						<input name=\"quizgame-answer-three\" id=\"quizgame-answer-three\"  type=\"text\" value=\"\" size=\"32\" onclick=\"javascript:showAnswerBox(4)\" onselect=\"javascript:showAnswerBox(4)\" />
						<input type=\"checkbox\" onclick=\"javascript:toggleCheck(this)\" id=\"quizgame-isright-three\" name=\"quizgame-isright-three\">
					</div>
							
					<div id=\"quizgame-answer-4\" class=\"quizgame-answer\" style=\"display:none\">
						<span class=\"quizgame-answer-number\">4.</span>
						<input name=\"quizgame-answer-four\" id=\"quizgame-answer-four\"  type=\"text\" value=\"\" size=\"32\" />
						<input type=\"checkbox\" onclick=\"javascript:toggleCheck(this)\" id=\"quizgame-isright-four\" name=\"quizgame-isright-four\">
					</div>
					
					<input id=\"quizGamePictureName\" name=\"quizGamePictureName\" type=\"hidden\" value=\"\" />
					<input id=\"key\" name=\"key\" type=\"hidden\" value=\"{$key}\" />
					<input id=\"chain\" name=\"chain\" type=\"hidden\" value=\"{$chain}\" />
					
				</form>
				
				<h1 style=\"margin-top:20px\">Add a Picture</h1>
				<div id=\"quizgame-picture-upload\" style=\"display:block;\">
					
					<div id=\"fake-form\" style=\"display:block;height:70px;\">
						<input type=\"file\" size=\"40\" disabled/>
						<div style=\"margin:9px 0px 0px 0px;\">
							<input type=\"button\" class=\"site-button\" value=\"Upload\"/>
						</div>
					</div>
					
					<div id=\"real-form\" style=\"display:none;\">
						<iframe id=\"imageUpload-frame\" class=\"imageUpload-frame\" width=\"420\" 
							scrolling=\"no\" framPiceborder=\"0\" onload=\"check_iframe(event, this.contentWindow)\" src=\"?title=Special:MiniAjaxUpload&wpThumbWidth=75&wpCategory=Quizgames\">
						</iframe>
					</div>
				</div>
				<div id=\"quizgame-picture-preview\" class=\"quizgame-picture-preview\"></div>
				<p id=\"quizgame-picture-reupload\" style=\"display:none\"><a href=\"javascript:showAttachPicture()\">Edit Picture</a></p>
				</div>
				
					<div id=\"startButton\" class=\"startButton\" style=\"display:none\">
						<input type=\"button\" class=\"site-button\" onclick=\"startGame()\" value=\"Create and Play!\"/>
					</div>
				  
				</div>";
			
			$wgOut->addHTML($output);
		}
	}
	
	SpecialPage::addPage( new QuestionGameHome );
}

?>
