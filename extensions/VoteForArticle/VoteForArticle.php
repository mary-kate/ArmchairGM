<?php
if(!defined('MEDIAWIKI')) {
	die();
}

class VoteForArticleEngine {

	var $articleId;
	/**
	 * Get simple html div for not-logged in users, user after click vote
	 * arrows will be redirected to error page and then to login page
	 *
	 * @return string
	 */
	private function getDivForNotLoggedIn() {
		global $wgTitle;
		$target = Title::newFromText("VoteForArticle", NS_SPECIAL);
		$url = $target->getFullURL() . "?returnto=" . $wgTitle->getSubpageUrlForm();

		return '
<div id="votebox" style="float:right;padding:5px;">
  <div id="votecount">'.$this->getVoteCount().'</div>
  <a id="up" href="'.$url.'" >Vote</a>
</div>';
	}

	/**
	 * Get extended html div for logged in users
	 *
	 * @return string
	 */
	private function getDivForLoggedIn() {
		global $wgTitle;
		global $wgServer, $wgUploadDirectory;
		$target = Title::newFromText("VoteForArticle", NS_SPECIAL);
		$url = $target->getFullURL() . "?article=" . $wgTitle->getSubpageUrlForm();
		$uservote = $this->getUserVoteCount();
		return "
<script type='text/javascript'>
var reqVoteForArticle;
var voteState = '" . (($uservote == 0 || $uservote == -1) ? 'up' : 'down') . "';
function vote(o) {
	reqVoteForArticle = false;
	try {
		reqVoteForArticle = new XMLHttpRequest();
	} catch (error) {
		try {
			reqVoteForArticle = new ActiveXObject('Microsoft.XMLHTTP');
		} catch (error) {
			return false;
		}
	}

	document.getElementById('ajaxProgressIcon').style.visibility = '';
	reqVoteForArticle.onreadystatechange = processReqChangeVoteForArticle;
	reqVoteForArticle.open('GET', '" . $url . "' + '&vote=' + voteState + '&ajax=true');
	reqVoteForArticle.send(null);
	return true;
}
function processReqChangeVoteForArticle() {
	if (reqVoteForArticle.readyState == 4) {
		if (reqVoteForArticle.status == 200) {
			if(reqVoteForArticle.responseText == '') return;
				var vota_params = reqVoteForArticle.responseText.split(',');
				if(vota_params.length == 2)
				{
				document.getElementById('votecount').innerHTML = vota_params[1];
				//var xxx = document.getElementById ('up');
				if(vota_params[0] == 0) {
					// unvote
					document.getElementById('up').innerHTML = 'Vote';
					voteState = 'up';
					//document.getElementById('up').style.display = '';
					//document.getElementById('down').style.display = 'none';
				} else if(vota_params[0] == 1) {
					// vote up
					document.getElementById('up').innerHTML = 'Unvote';
					voteState = 'down'
					//document.getElementById('up').style.display = 'none';
					//document.getElementById('down').style.display = '';
				} else if(vota_params[0] == -1) {
					// vote down
					document.getElementById('up').innerHTML = 'Vote';
					voteState = 'up'
					//document.getElementById('up').style.display = '';
					//document.getElementById('down').style.display = 'none';
				}
				}
				document.getElementById('ajaxProgressIcon').style.visibility = 'hidden';
		}
	}
}
</script>
<div id=\"votebox\">
    <div id=\"votecount\">".$this->getVoteCount()."</div>
    <a id=\"up\" href=\"$url&vote=up\" onClick=\"if(vote(this)) return false;\">" . (($uservote == 0 || $uservote == -1) ? 'Vote' : 'Unvote') . "</a>
</div>";
	}

	/**
	 * Check if vote counter should be visible on processed page
	 *
	 * @return bool
	 */
	private function isCorrectArticle() {
                global $wgArticle, $wgTitle, $wgOut;

                if ($wgOut->mIsarticle != '1') {
                        return false;
                }

		if ($wgArticle == null)  {
			return false;
		}

		$page_id = $wgTitle->mArticleID;

		if ($page_id <= 0) {
			return false;
		}

		if (in_array($wgTitle->getNamespace(), array(NS_USER,NS_USER_TALK))) {
			return false;
		}

		/*
		$mainPageObj = Title::newMainPage();
		if ($wgTitle->getNamespace() != NS_MAIN || $mainPageObj->getFullText() == $wgTitle->getFullText()) {
			return false;
		}
		*/

		return true;
	}

	/**
	 * Check if user is logged-in (only logged-in users can vote)
	 *
	 * @return bool
	 */
	private function isUserLoggedIn() {
		global $wgUser;
		return $wgUser->isLoggedIn();
	}

	private function showErrorPage() {
		global $wgRequest, $wgOut, $wgUser;

		$skin = $wgUser->getSkin();

		$wgOut->setPageTitle( wfMsg( 'loginreqtitle' ) );
		$wgOut->setHtmlTitle( wfMsg( 'errorpagetitle' ) );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
		$wgOut->setArticleFlag( false );

		$loginTitle = Title::makeTitle( NS_SPECIAL, 'Userlogin' );
		$loginLink = $skin->makeKnownLinkObj( $loginTitle, wfMsgHtml( 'loginreqlink' ), 'returnto=' . $wgRequest->getVal("returnto") );
		$wgOut->addHtml( wfMsgWikiHtml( 'loginreqpagetext', $loginLink ) );
		$wgOut->returnToMain( false );
	}

	/**
	 * Show vote box for logged in and not-logged in users
	 *
	 */
	function showVoteForArticleBox() {
		global $wgTitle;

		if (! $this->isCorrectArticle()) {
			return true;
		}

		$article = new Article($wgTitle);
		$this->articleId = $article->getID();

		if (! $this->isUserLoggedIn()) {
			echo $this->getDivForNotLoggedIn();
		} else {
			echo $this->getDivForLoggedIn();
		}
		return true;
	}

	private function getVoteCount() {
		$dbr =& wfGetDB( DB_SLAVE);
		$res = $dbr->query('SELECT COALESCE(SUM(vote),0) AS votecount FROM votecounter WHERE article_id=' . $this->articleId);
		$row = $dbr->fetchObject($res);
		$votessum = $row->votecount;
		$dbr->freeResult($res);
		return $votessum;
	}

	private function getUserVoteCount() {
		global $wgUser;
		$dbr =& wfGetDB( DB_SLAVE);
		$res = $dbr->query('SELECT COALESCE(vote,0) AS vote FROM votecounter WHERE article_id=' . $this->articleId . ' AND user_id=' . $wgUser->getID());
		$row = $dbr->fetchObject($res);
		$vote = $row->vote;
		$dbr->freeResult($res);
		return $vote;
	}

	function specialVoteForArticle() {
		global $wgRequest, $wgUser, $wgOut;

		$returnto = $wgRequest->getVal("returnto");
		$article = $wgRequest->getVal("article");
		$ajax = $wgRequest->getVal("ajax");
		$vote = $wgRequest->getVal("vote");

		if($returnto != '' && !$this->isUserLoggedIn()) {
			$this->showErrorPage();
		} else if($article != '' && $vote != '' && $this->isUserLoggedIn()) {
			if ($vote == "up") {
				$vote = 1;
			} else if ($vote == "down") {
				$vote = -1;
			} else {
				die();
			}

			$art = new Article(Title::newFromText($article));
			$this->articleId = $art->getID();

			$old_vote = $this->getUserVoteCount();

			if($old_vote == $vote) {
				die();
			}

			if( ($vote == -1) && ($old_vote == -1 || $old_vote == 0)) {
			    die();
			}

			$dbw =& wfGetDB( DB_MASTER );

    			if($old_vote == 0) {
				$dbw->insert( 'votecounter', array( 'article_id' => $this->articleId, 'user_id' => $wgUser->getID(), 'vote' => $vote, 'time' => date('Y-m-d H:i:s')));
			} else {
				$vote = 0;
				$dbw->delete( 'votecounter', array( 'article_id' => $this->articleId, 'user_id' => $wgUser->getID()));
			}

			$dbw->commit();

			$art->purge();

			if($ajax == "true") {
				echo $vote.",".$this->getVoteCount();
				die();
			} else {
				$target = Title::newFromText($article);
				$wgOut->redirect($target->getFullURL());
			}
		}
	}
}


// add new, unlisted special page (in WIKIA way)
$wgSpecialPages['VoteForArticle'] = array('SpecialPage','VoteForArticle');
$VoteEngine = new VoteForArticleEngine();

function wfSpecialVoteForArticle() {
	global $VoteEngine;
	$VoteEngine->specialVoteForArticle();
}

/** Set function to initialize extension */
$wgExtensionFunctions[] = 'VoteForArticle_Setup';

/**
 * Initialize extension
 */
function VoteForArticle_Setup() {
	global $wgHooks, $wgSpecialPages, $VoteEngine;

	$wgHooks['BeforeTitleDisplayed'][] = array($VoteEngine, 'showVoteForArticleBox');
}
?>
