<?php
/**
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 * @author Inez Korczyński (inez@wikia.com)
 *
 * Add 'wfRunHooks('ConfirmEmailComplete', array(&$user));' in SpecialConfirmemail.php at 86 line.
 */

if ( ! defined( 'MEDIAWIKI' ) )
	die();

global $wgAvailableRights, $wgGroupPermissions, $wgSpecialPages, $wgSpecialPages, $wgExtensionFunctions;

# set function to call when loading extension
$wgExtensionFunctions[] = 'wfSpecialPrivateDomainsSetup';

# create new special page restricted to user with 'PrivateDomains' right
$wgSpecialPages['PrivateDomains'] = new SpecialPage('PrivateDomains', 'PrivateDomains');

# set 'PrivateDomains' right to users in staff or bureaucrat group
$wgAvailableRights [] = 'PrivateDomains';
$wgGroupPermissions ['staff']['PrivateDomains'] = true;
$wgGroupPermissions ['bureaucrat']['PrivateDomains'] = true;

# overwrite standard groups permissions
$wgGroupPermissions['staff']['edit'] = true;
$wgGroupPermissions['bureaucrat']['edit'] = true;
$wgGroupPermissions['user']['edit'] = false;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['privatedomains']['edit'] = true;

$wgGroupPermissions['staff']['upload'] = true;
$wgGroupPermissions['bureaucrat']['upload'] = true;
$wgGroupPermissions['user']['upload'] = false;
$wgGroupPermissions['*']['upload'] = false;
$wgGroupPermissions['privatedomains']['upload'] = true;

$wgGroupPermissions['staff']['move'] = true;
$wgGroupPermissions['bureaucrat']['move'] = true;
$wgGroupPermissions['user']['move'] = false;
$wgGroupPermissions['*']['move'] = false;
$wgGroupPermissions['privatedomains']['move'] = true;

$wgGroupPermissions['user']['reupload'] = false;
$wgGroupPermissions['*']['reupload'] = false;
$wgGroupPermissions['privatedomains']['reupload'] = true;

$wgGroupPermissions['user']['reupload-shared'] = false;
$wgGroupPermissions['*']['reupload-shared'] = false;
$wgGroupPermissions['privatedomains']['reupload-shared'] = true;

$wgGroupPermissions['user']['minoredit'] = false;
$wgGroupPermissions['*']['minoredit'] = false;
$wgGroupPermissions['privatedomains']['minoredit'] = true;

$wgHooks['AlternateEdit'][] = 'pd_AlternateEdit'; // Occurs whenever action=edit is called
$wgHooks['UserLoginComplete'][] = 'pd_UserLoginComplete'; // Occurs after a user has successfully logged in
$wgHooks['ConfirmEmailComplete'][] = 'pd_UserLoginComplete'; // Occurs after a user has successfully confirm email (not standard hook)

function wfSpecialPrivateDomainsSetup() {
	global $wgMessageCache;

	$wgMessageCache->addMessage ('privatedomains_nomanageaccess', "<p>Sorry, you do not have enough rights to manage the allowed private domains for this wiki. Only wiki bureaucrats and staff members have access.</p><p>If you aren't logged in, you probably <a href='/wiki/Special:Userlogin'>should</a>.</p>");
	$wgMessageCache->addMessage ('privatedomains', 'Manage Private Domains');
	$wgMessageCache->addMessage ('privatedomains_ifemailcontact', "<<p>Otherwise, please contact [[Special:Emailuser/$1|$1]] if you have any questions.</p>");
	$wgMessageCache->addMessage ('saveprivatedomains_success', "Private Domains changes saved.");
	$wgMessageCache->addMessage ('privatedomains_invalidemail',"<p>Sorry, access to this wiki is restricted to members of $1. If you have an email address affiliated with $1, you can enter or reconfirm your email address on your account preference page <a href=/wiki/Special:Preferences>here</a>. You can still view pages on this wiki, but you will be unable to edit.</p>");
	$wgMessageCache->addMessage ('privatedomains_affiliatenamelabel', "<br>Name of organization: ");
	$wgMessageCache->addMessage ('privatedomains_emailadminlabel', "<br>Contact username for access problems or queries: ");
	$wgMessageCache->addMessage ('privatedomainsinstructions', "<br /> <br /> <p>Below is the list of email domains allowed for editors of this wiki. Each line designates an email suffix that is given access for editing. This should be formatted with one suffix per line. For example:</p> <p style=\"width: 20%; padding:5px; border: 1px solid grey;\">cs.stanford.edu<br /> stanfordalumni.org</p> <p>This would allow edits from anyone with the email address whatever@cs.stanford.edu or whatever@stanfordalumni.org</p> <p><b>Enter the allowed domains in the text box below, and click \"save\".</b></p>");

}

/*
 * If user isn't in group privatedomains/staff/bureaucrat then
 * deny access to edit page and show information box.
 */
function pd_AlternateEdit(&$editpage) {
	global $wgUser;
	$groups = $wgUser->getGroups();
	if ( $wgUser->isLoggedIn() && !in_array('privatedomains', $groups) &&  !in_array('staff', $groups) && !in_array('bureaucrat', $groups)) {
		global $wgOut;
		$privatedomains_affiliatename = PrivateDomains::getParam("privatedomains_affiliatename");
		$wgOut->addHTML('<div class="errorbox" style="width:92%;"><strong>');
		$wgOut->addHTML(wfMsg('privatedomains_invalidemail', $privatedomains_affiliatename));
		$wgOut->addHTML('</strong></div><br><br><br>');
		return false;
	}
	return true;
}

/*
 * If user have confirmed and allowed address email
 * then add him to privatedomains user group.
 */
function pd_UserLoginComplete($user) {
	if( $user->isEmailConfirmed() ) {
		$domainsStr = PrivateDomains::getParam('privatedomains_domains');
		if($domainsStr != '') {
			$email = strtolower($user->mEmail);
			# get suffix domain name
			preg_match("/([^@]+)@(.+)$/i",$email, $matches);
			$emailDomain = $matches[2];
			$domainsArr = explode("\n", $domainsStr);
			foreach ( $domainsArr as $allowedDomain ) {
				$allowedDomain = strtolower($allowedDomain);
				if ( preg_match("/.*?$allowedDomain$/",$emailDomain) ) {
					$user->addGroup('privatedomains');
					return;
				}
			}
		}
	}
	$user->removeGroup('privatedomains');
}

function wfSpecialPrivateDomains() {
    $page = new PrivateDomains();
    $page->execute();
}

/*
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class PrivateDomains extends SpecialPage {

	function PrivateDomains() {
		SpecialPage::SpecialPage("PrivateDomains");
	}

	function saveParam($name, $value) {
		$nameTitle = Title::newFromText($name, NS_MEDIAWIKI);
		$article = new Article($nameTitle);

		if ( $nameTitle->exists() ) {
			$article->quickEdit($value);
		} else {
			$article->insertNewArticle($value, '', false, false, false, false);
		}
	}

	static function getParam($name) {
		$nameTitle = Title::newFromText($name, NS_MEDIAWIKI);
		if ( $nameTitle->exists() ) {
			$article = new Article($nameTitle);
			return $article->getContent();
		} else {
			return "";
		}
	}

	function execute() {
		global $wgRequest,$wgUser,$wgOut;

		$wgOut->setPageTitle( wfMsg('privatedomains') );
		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );

		$msg = '';

	    if( $wgRequest->wasPosted() ) {
			if ( 'submit' == $wgRequest->getText('action') ) {

				global $wgMessageCache;

				$this->saveParam('privatedomains_domains', $wgRequest->getText('listdata'));
				$this->saveParam('privatedomains_affiliatename', $wgRequest->getText('affiliateName'));
				$this->saveParam('privatedomains_emailadmin', $wgRequest->getText('optionalPrivateDomainsEmail'));

				$msg = wfMsgHtml('saveprivatedomains_success');
			}
		}
		$this->mainForm( $msg );
	}

	/**
	 * @access private
	 */
	function mainForm( $msg ) {
		global $wgUser, $wgOut, $wgLang, $wgDBname, $wgMessageCache;

		$titleObj = Title::makeTitle( NS_SPECIAL, 'PrivateDomains' );
		$action = $titleObj->escapeLocalUrl('action=submit');

		$userGroups = $wgUser->getGroups();
		if ( !in_array('staff', $userGroups ) && !in_array('bureaucrat', $userGroups) ) {

			$wgOut->addHTML(wfMsg('privatedomains_ifemailcontact'));

			$privatedomains_emailadmin = PrivateDomains::getParam("privatedomains_emailadmin");

			if ($privatedomains_emailadmin != '') {
				$wgOut->addWikiText(wfMsg('privatedomains_ifemailcontact', $privatedomains_emailadmin));
			}

			return false;
        }

		if ( $msg != '' ) {
			$wgOut->addHTML('<div class="errorbox" style="width:92%;"><h2>' . $msg . '</h2></div><br><br><br>');
		}

		$wgOut->addHTML("<form name=\"privatedomains\" id=\"privatedomains\" method=\"post\" action=\"{$action}\"><label for=\"affiliateName\">" . wfMsg('privatedomains_affiliatenamelabel') . "</label><input type='text' name=\"affiliateName\"  width=30 value=\"" . $this->getParam('privatedomains_affiliatename') ."\"><label for=\"optionalEmail\">" . wfMsg('privatedomains_emailadminlabel') . "</label><input type='text' name=\"optionalPrivateDomainsEmail\" value=\"" . $this->getParam('privatedomains_emailadmin') . "\">");
		$wgOut->addHTML(wfMsg('privatedomainsinstructions'));
		$wgOut->addHTML("<textarea name='listdata' rows=10 cols=40>" . $this->getParam('privatedomains_domains') . "</textarea>");
		$wgOut->addHTML("<br><input type='submit' name=\"saveList\" value=\"" . wfMsgHtml('saveprefs') . "\" />");
		$wgOut->addHTML("</form>");
	}
}
?>
