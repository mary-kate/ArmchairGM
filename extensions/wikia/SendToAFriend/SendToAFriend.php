<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Displays various types of "invite a friend" form
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Tomasz Klim <tomek@wikia.com>
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
create table send_stats (
    send_id int not null auto_increment primary key,
    send_page_id int not null,
    send_page_ns int not null,
    send_page_re int not null,
    send_unique int not null,
    send_tm timestamp default now(),
    send_ip varchar(16) not null,
    send_to varchar(255) not null,
    send_from varchar(255) not null,
    send_ajax int not null,
    send_seen int not null default 0
);
 *
LocalSettings.php:
+ $wgNotificationThrottle = 20;
+ $wgNotificationFetchContacts = true;
+ require_once( "$IP/extensions/SendToAFriend/SendToAFriend.php" );
 *
includes/SpecialUserlogin.php:
+ wfRunHooks( 'AddNewAccount2', array( $wgUser ) );
 *
 */

$wgNotificationMessages = array();
$wgNotificationMessages['en'] = array(
	'invitespecialpage'        => 'Invite a friend',
	'stf_button'               => 'Send to a friend',
	'stf_after_reg'            => 'Invite a friend to join Wikia! [[Special:InviteSpecialPage|Click here]].',
	'stf_subject'              => ' has sent you an article from Wikia!',
	'stf_confirm'              => 'Your invitation has been sent.',
	'stf_error'                => 'Error sending email.',
	'stf_frm1'                 => 'Your email address: ',
	'stf_frm2'                 => 'Friend\'s email address: ',
	'stf_frm3_send'            => "Hi\n\nCheck out this page at Wikia...\n",
	'stf_frm3_invite'          => "Hi\n\nI just signed up for Wikia and thought you might want to check it out...\n",
	'stf_frm4_send'            => 'Send!',
	'stf_frm4_invite'          => 'Invite!',
	'stf_frm5'                 => '(the URL of this site will be appended to your message)',
	'stf_frm6'                 => 'Close this window',
	'stf_throttle'             => 'For security reasons, you can only send $1 notifications a day.',
	'stf_abuse'                => "This email was sent by $1 via Wikia.\nIf you think this was sent in error, please let us know at support@wikia.com.\n",
	'stf_ctx_invite'           => 'Instead of typing your friend\'s address, you can search your Gmail account (if you have one), and choose an email from Contacts.',
	'stf_ctx_choose'           => 'Please choose your desired friend from the list:',
	'stf_ctx_check'            => 'Check',
	'stf_ctx_empty'            => 'You have no contacts in this account.',
	'stf_ctx_invalid'          => 'Login or password you typed is invalid. Please try again.',
	'stf_ctx_password'         => 'Password',
);
$wgNotificationMessages['pl'] = array(
	'invitespecialpage'        => 'Zapros znajomego',
	'stf_button'               => 'Wyslij do znajomego',
	'stf_after_reg'            => 'Zapros znajomego do Wikii! [[Special:InviteSpecialPage|Kliknij tutaj]].',
	'stf_subject'              => ' wyslal Ci artykul z Wikii!',
	'stf_confirm'              => 'Twoje zaproszenie zostalo wyslane.',
	'stf_error'                => 'Blad podczas wysylania emaila.',
	'stf_frm1'                 => 'Twoj adres email: ',
	'stf_frm2'                 => 'Adres email znajomego: ',
	'stf_frm3_send'            => "Czesc\n\nZobacz ta strone na Wikii...\n",
	'stf_frm3_invite'          => "Czesc\n\nWlasnie sie zarejestrowalem na Wikii i mysle, ze moglaby Cie ona zainteresowac...\n",
	'stf_frm4_send'            => 'Wyslij!',
	'stf_frm4_invite'          => 'Zapros!',
	'stf_frm5'                 => '(URL tej strony zostanie dolaczony do Twojego tekstu)',
	'stf_frm6'                 => 'Zamknij to okienko',
	'stf_throttle'             => 'Ze wzgledow bezpieczenstwa mozesz wyslac dziennie jedynie $1 zaproszen.',
	'stf_abuse'                => "Ten email zostal wyslany przez $1 z Wikii.\nJesli uwazasz, ze nie powinien on do Ciebie trafic, daj nam znac na support@wikia.com.\n",
	'stf_ctx_invite'           => 'Zamiast wpisywac adres przyjaciela, mozesz wybrac je z listy kontaktow konta Gmail (jesli takie posiadasz).',
	'stf_ctx_choose'           => 'Wybierz przyjaciela z ponizszej listy:',
	'stf_ctx_check'            => 'Sprawdz',
	'stf_ctx_empty'            => 'Lista kontaktow tego konta jest pusta.',
	'stf_ctx_invalid'          => 'Podany login lub haslo sa nieprawidlowe. Prosze sprobowac ponownie.',
	'stf_ctx_password'         => 'Haslo',
);


$wgAvailableRights[] = 'notification';
$wgGroupPermissions['*']['notification'] = true;
$wgGroupPermissions['user']['notification'] = true;

$wgExtensionFunctions[] = 'wfInviteSpecialPage';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Invite a friend',
	'description' => 'displays "invite a friend" form',
	'author' => 'Tomasz Klim'
);
$wgHooks['AddNewAccount2'][] = 'wfInviteAfterReg';
$wgExtensionCredits['other'][] = array(
	'name' => 'Invite a friend',
	'description' => 'displays "invite a friend" form after creating new user account',
	'author' => 'Tomasz Klim'
);
//$wgHooks['AfterTitleDisplayed'][] = 'wfSendAjaxForm';  // without monobook hooks
$wgHooks['BeforeTitleDisplayed'][] = 'wfSendAjaxForm';
$wgExtensionCredits['other'][] = array(
	'name' => 'Send to a friend',
	'description' => 'displays "send to a friend" button in the article',
	'author' => 'Tomasz Klim'
);


function wfInitI18n() {
	global $wgMessageCache, $wgNotificationMessages;

	foreach( $wgNotificationMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgNotificationMessages[$key], $key );
	}
}


function wfInviteAfterReg( $user=null ) {
	global $wgUser, $wgOut;
	wfInitI18n();

	if ( is_null( $user ) ) {
		// Compatibility with old versions which didn't pass the parameter
		$user = $wgUser;
	}

	$wgOut->addWikiText( wfMsg('stf_after_reg') );
//	$wgOut->addWikiText( wfMsg('stf_after_reg') . wfMsg('sendtoafriend_reg2') . wfMsg('sendtoafriend_reg3') . $_SERVER['SERVER_NAME'] . wfMsg('sendtoafriend_reg4') . wfMsg('sendtoafriend_reg5') );
	return true;
}


function wfSendAjaxForm( ) {
	global $wgTitle, $wgRequest;
	wfInitI18n();

	$pageId = $wgTitle->getArticleID();
	$pageNamespace = $wgTitle->getNamespace();
	$pageRevision = $wgTitle->getLatestRevID();

	// Tomek: in case of invalid article, point user to the Main Page
	// Emil: dirty hacks to stick the feature to article pages only
	if ( 'edit' == $wgRequest->getText( 'action' ) || $pageId == 0 || $pageNamespace == -1 ) {
		return true;
//		$titleObj = Title::makeTitle( 0, wfMsg('mainpage') );
//		$pageId = $titleObj->getArticleID();
//		$pageNamespace = $titleObj->getNamespace();
//		$pageRevision = $titleObj->getLatestRevID();
	}

	$target = Title::newFromText( 'InviteSpecialPage', NS_SPECIAL );
	$url = $target->getFullURL();
	$cap = wfMsg('stf_button');

	$fallback = ' onSubmit="return notifySend(this);"';
	$form = generateSendInviteForm( $url, $pageId, $pageNamespace, $pageRevision, $fallback );

	echo "
<script type='text/javascript'>
var reqSendToAFriend;
function notifyShow() {
	document.getElementById('notifyForm').style.visibility = '';
	return false;
}
function notifyHide() {
	document.getElementById('notifyForm').style.visibility = 'hidden';
	return false;
}
function notifySend(o) {
	reqSendToAFriend = false;
	try {
		reqSendToAFriend = new XMLHttpRequest();
	} catch (error) {
		try {
			reqSendToAFriend = new ActiveXObject('Microsoft.XMLHTTP');
		} catch (error) {
			return true;
		}
	}
	reqSendToAFriend.onreadystatechange = processReqChangeSendToAFriend;
	if (document.getElementById('ajaxProgressIcon')) {
		document.getElementById('ajaxProgressIcon').style.visibility = '';
	}
	reqSendToAFriend.open('GET', '$url?en=1&id=' + o.id.value + '&ns=' + o.ns.value + '&re=' + o.re.value + '&fr=' + o.fr.value + '&to=' + o.to.value + '&bo=' + escape(o.bo.value));
	reqSendToAFriend.send(null);
	return false;
}
function processReqChangeSendToAFriend() {
	if (reqSendToAFriend.readyState == 4) {
		if (reqSendToAFriend.status == 200) {
			document.getElementById('notifyForm').style.visibility = 'hidden';
			if (document.getElementById('ajaxProgressIcon')) {
				document.getElementById('ajaxProgressIcon').style.visibility = 'hidden';
			}
		}
	}
}
</script>
<div id='sendtofriendbox'>
	<a href='{$url}?id={$pageId}&ns={$pageNamespace}&re={$pageRevision}' onClick=\"return notifyShow();\">
		<img src='../skins/monobook/sendToFriend.png' alt='->' title='{$cap}' width='16' height='16'/>{$cap}
	</a>
</div>
<div id=\"notifyForm\" style=\"position:absolute;right:100px;top:45px;border:1px #aaaaaa solid;background-color:#ffffce;font-size:12px;padding:5px;margin-top:3px;width:500px;height:260px;visibility:hidden;\">
$form
</div>
";
	return true;
}


function generateSendInviteForm( $url, $pageId, $pageNamespace, $pageRevision, $fallback='' ) {
    global $wgUser;

    $mode   = ( $fallback != '' ? 'send' : 'invite' );

    $email  = $wgUser->getEmail();
    $form1  = wfMsg('stf_frm1');
    $form2  = wfMsg('stf_frm2');
    $form3  = wfMsg('stf_frm3_' . $mode);
    $form4  = wfMsg('stf_frm4_' . $mode);
    $form5  = wfMsg('stf_frm5');
    $form6  = wfMsg('stf_frm6');

    $cancel = ( $fallback != '' ? "<br><input type=\"reset\" style=\"margin-top:3px;\" value=\"$form6\" onClick=\"return notifyHide();\">" : "" );

    return <<<EOF
<form name="notification" action="$url" method="post"$fallback>
<input type="hidden" name="id" value="$pageId">
<input type="hidden" name="ns" value="$pageNamespace">
<input type="hidden" name="re" value="$pageRevision">
$form1<input type="text" style="margin-top:3px;" name="fr" value="$email"><br>
$form2<input type="text" style="margin-top:3px;" name="to"><br>
<textarea name="bo" rows="8" cols="12" style="width:95%; margin-top:3px;">
$form3
</textarea>
<input type="submit" style="margin-top:3px;" name="submit" value="$form4">
$form5$cancel
</form>
EOF
;
}


function wfInviteSpecialPage() {
    wfInitI18n();

    class InviteSpecialPage extends SpecialPage
    {
	function InviteSpecialPage() {
		SpecialPage::SpecialPage('InviteSpecialPage');
	}


	function execute( $par ) {
		global $wgRequest, $wgOut, $wgTitle, $wgUser, $wgNotificationFetchContacts;
/*
		if (!in_array( 'notification', $wgUser->getRights() ) ) {
			$wgOut->setArticleRelated( false );
			$wgOut->setRobotpolicy( 'noindex,follow' );
			$wgOut->errorpage( 'nosuchspecialpage', 'nospecialpagetext' );
			return;
		}
*/
		$this->setHeaders();

		$isEncoded = ( $wgRequest->getText( 'en' ) == '' ? 0 : 1 );

		$uniqueId      = $wgRequest->getText( 'un' );
		$pageId        = $wgRequest->getText( 'id' );
		$pageNamespace = $wgRequest->getText( 'ns' );
		$pageRevision  = $wgRequest->getText( 're' );

		$notifyTo      = $wgRequest->getText( 'to' );
		$notifyFrom    = $wgRequest->getText( 'fr' );
		$notifyBody    = $wgRequest->getText( 'bo' );  // TODO: need to filter out URLs pasted by user

		$username      = $wgRequest->getText( 'us' );
		$password      = $wgRequest->getText( 'pa' );
		$acctType      = $wgRequest->getText( 'ty' );

		if ( $uniqueId && is_numeric($uniqueId) ) { // AJAX mode - redirect

			$this->uniqueRedirect( $uniqueId );

		} elseif ( $notifyTo && $notifyFrom && $notifyBody && is_numeric($pageId) && is_numeric($pageNamespace) && is_numeric($pageRevision) ) {  // user completed the form

			$ip = wfGetIP();
			$uniqueId = mt_rand(0, 0x7fffffff);

			$target = Title::newFromText( 'InviteSpecialPage', NS_SPECIAL );
    			$url = $target->getFullURL() . "?un=$uniqueId";

			// fallback version, in case of Special::InviteSpecialPage not recognized yet
			// global $wgArticlePath;
			// $url = "http://" . $_SERVER['SERVER_NAME'] . str_replace('$1', '', $wgArticlePath) . NS_SPECIAL . ":InviteSpecialPage?un=$uniqueId";

			$notifyBody .= "\n\n" . $url . "\n\n\n" . wfMsg( 'stf_abuse', $ip );

			$dbw =& wfGetDB( DB_MASTER );
			$dbw->insert( 'send_stats',
				array(
					'send_page_id' => $pageId,
					'send_page_ns' => $pageNamespace,
					'send_page_re' => $pageRevision,
					'send_unique'  => $uniqueId,
					'send_ip'      => $ip,
					'send_to'      => $notifyTo,
					'send_from'    => $notifyFrom,
					'send_ajax'    => $isEncoded,
				), "wfInviteSpecialPage::execute"
			);

			$this->sendNotification( $isEncoded, $notifyTo, $notifyFrom, $notifyBody );
			// $wgOut->addHTML( '<pre>' . $notifyBody . '</pre>' );  // TODO: remove this

		} elseif ( $isEncoded ) {  // AJAX mode - incomplete form

			echo wfMsg( 'stf_error' );
			die();

		} elseif ( is_numeric($pageId) && is_numeric($pageNamespace) && is_numeric($pageRevision) ) {  // AJAX fallback - SEND

			// TODO: change page title to "Send to a friend"

			$wgOut->addHTML( generateSendInviteForm( $wgTitle->escapeLocalUrl(), $pageId, $pageNamespace, $pageRevision ) );

		} else {  // show the form in classic special page mode, point user to the Main Page - INVITE

			$titleObj = Title::makeTitle( 0, wfMsg('mainpage') );

			$pageId = $titleObj->getArticleID();
			$pageNamespace = $titleObj->getNamespace();
			$pageRevision = $titleObj->getLatestRevID();

			$wgOut->addHTML( generateSendInviteForm( $wgTitle->escapeLocalUrl(), $pageId, $pageNamespace, $pageRevision ) );

			// get friend's email from Gmail functionality
			if ( $wgNotificationFetchContacts ) {
			    if ( $username && $password && $acctType ) {
				$this->generateContactFormSecond( $wgTitle->escapeLocalUrl(), $username, $password, $acctType );
			    } else {
				$this->generateContactFormFirst( $wgTitle->escapeLocalUrl() );
			    }
			}
		}
	}


	function uniqueRedirect( $uniqueId ) {
		global $wgOut;

		$dbw =& wfGetDB( DB_MASTER );
    		$query = "SELECT p.page_namespace, p.page_title, s.send_page_re as page_revision
			  FROM send_stats s
			  INNER JOIN page p ON s.send_page_id = p.page_id AND s.send_page_ns = p.page_namespace
			  WHERE s.send_unique = $uniqueId
			  ORDER BY s.send_id DESC
			  LIMIT 1";
    		$res = $dbw->query( $query ) ;
		if ( $row = $dbw->fetchObject( $res ) ) {

			$dbw->query( "UPDATE send_stats SET send_seen = send_seen + 1 WHERE send_unique = $uniqueId", "wfInviteSpecialPage::uniqueRedirect" );

			$target = Title::newFromText( $row->page_title, $row->page_namespace );
            		$url = $target->getFullURL() . "?oldid=" . $row->page_revision;
			$wgOut->redirect( $url, 302 );
		} else {
			$wgOut->setArticleRelated( false );
			$wgOut->setRobotpolicy( 'noindex,follow' );
			$wgOut->errorpage( 'nosuchaction', 'nosuchactiontext' );
		}
    		$dbw->freeResult( $res );
	}


	function sendNotification( $isEncoded, $notifyTo, $notifyFrom, $notifyBody ) {
		global $wgOut, $wgUser, $wgMemc, $wgDBname, $wgNotificationThrottle;

                $ip = wfGetIP();
		if ( $wgNotificationThrottle ) {
			$key = $wgDBname.':notification:ip:'.$ip;
			$value = $wgMemc->incr( $key );
			if ( !$value ) {
				$wgMemc->set( $key, 1, 86400 );
			}
			if ( $value > $wgNotificationThrottle ) {
				$error = wfMsg( 'stf_throttle', $wgNotificationThrottle );
				if ( $isEncoded ) {
					echo $error;
					die();
				} else {
					$wgOut->addHTML( $error );
					return false;
				}
			}
		}

		$to = new MailAddress( $notifyTo );
		$sender = new MailAddress( $notifyFrom );
		$error = userMailer( $to, $sender, $wgUser->getName() . wfMsg('stf_subject'), $notifyBody );

		if ( $isEncoded ) {
			echo $error;
			die();
		} elseif ( $error == '' ) {
			$wgOut->addHTML( wfMsg('stf_confirm') );
			return true;
		} else {
			$wgOut->addHTML( $error );
			return false;
		}
	}


	function generateContactFormFirst( $url, $error='' ) {
	    global $wgOut, $wgUser;

	    $msg = wfMsg( $error!='' ? $error : 'stf_ctx_invite' );
	    $pas = wfMsg( 'stf_ctx_password' );
	    $chk = wfMsg( 'stf_ctx_check' );

	    $email = $wgUser->getEmail();

	    // check, if user has supplied an usable account in his profile
	    if ( strpos( $email, '@gmail.com' ) ) {
		$email = str_replace( '@gmail.com', '', $email );
		$type = 'gmail';
	    } else {
		$email = '';
		$type = 'gmail';  // TODO: detect also Yahoo etc. as soon as supported by generateContactFormSecond
	    }

	    $wgOut->addHTML( <<<EOF
<form name="contacts" action="$url" method="post">
<br>$msg<br>
<input type="hidden" name="ty" value="$type">
Gmail ID: <input type="text" style="margin-top:3px;" name="us" value="$email"><br>
$pas: <input type="password" style="margin-top:3px;" name="pa"><br>
<input type="submit" style="margin-top:3px;" name="submit" value="$chk">
</form>
EOF
);
	}


	function generateContactFormSecond( $url, $username, $password, $type ) {
	    if ( $type != 'gmail' ) {
		$this->generateContactFormFirst( $url );
	    } else {
		$this->fetchGmail( $url, $username, $password );
	    }
	}


	function fetchGmail( $url, $username, $password ) {
	    $handler = new WikiCurl();

	    // we allow cookies, but don't store them, because of major security hole in Gmail,
	    // related to 'GX' cookie, which allows one to take over any Gmail account.
	    // worked on 2007-02-01 12:59 local time (+1)
	    $handler->setCookies( '/dev/null' );

	    $ret = $handler->get('https://www.google.com/accounts/LoginAuth', array('Email' => $username, 'Passwd' => $password));

	    // global $wgOut;
	    // $wgOut->addWikiText( '<pre>' . $ret . '</pre>' );

	    if ( strpos( $ret, '  Redirecting  ' ) || strpos( $ret, 'Personal information' ) ) {  // we are logged in, let's analyze the Contacts page

		// this method works perfectly for me (Tomasz Klim), but doesn't work for John Q. Smith
		// anyway, we need to load this page, to set some cookies needed by the below page
	        $ret = $handler->get('https://mail.google.com/mail/', array('v'=>'cl', 'pnl'=>'a', 'ui'=>'html', 'zy'=>'n'));

		// the first contact has different formatting
		preg_match('!<tr> <td width="1%" nowrap> <input type="checkbox" name="c" value="(.*?)"> </td> <td width="28%"> <b>(.*?)</b> </td> <td width="71%"> (.*?) &nbsp; </td>!s', $ret, $code);

		// successfully matched the first contact
		if ( $code[2] && strpos( $code[3], '@' ) ) {
		    $cnt = 1;
		    $options = "<option value=\"".$code[3]."\">".$code[2]."</option>";

		    // next contacts
		    preg_match_all('!<tr> <td> <input type="checkbox" name="c" value="(.*?)"> </td> <td> <b>(.*?)</b> </td> <td> (.*?) &nbsp; </td>!s', $ret, $codes, PREG_SET_ORDER);
		    foreach ( $codes as $code ) {
			$cnt++;
			$options .= "<option value=\"".$code[3]."\">".$code[2]."</option>";
		    }

		    $cnt = max( 3, $cnt );
		    $cnt = min( 8, $cnt );
		    $this->generateContactFormHelper( wfMsg( 'stf_ctx_choose' ), $cnt, $options );

		} else {

		    // traditional method has failed, so we use a little security hole in Gmail:
		    // the "contacts" subpage doesn't check "ver" parameter, which normally contains magic number;
		    // note that it still verifies session cookies, so we need to load the previous page even, if
		    // we know that it won't work
		    // note that we should consider this method temporary, since Gmail is generally protected from
		    // scanning things, so sooner or later Gmail folks will track and fix our little feature...
		    // worked on 2007-01-31 15:48 local time (+1)
	    	    $ret = $handler->get('https://mail.google.com/mail/', array('view'=>'page', 'name'=>'contacts'));
		    $cnt = 0;

		    preg_match_all('!"ct","(.*?)","(.*?)"!s', $ret, $codes, PREG_SET_ORDER);  // JSON table

		    foreach ( $codes as $code ) {
			if ( strpos( $code[2], '@' ) ) {
			    $cnt++;
			    $name = ( $code[1] ? $code[1] : $code[2] );
			    $options .= "<option value=\"".$code[2]."\">".$name."</option>";
			}
		    }

		    if ( $cnt ) {
			$cnt = max( 3, $cnt );
		        $cnt = min( 8, $cnt );
			$this->generateContactFormHelper( wfMsg( 'stf_ctx_choose' ), $cnt, $options );

		    } else {
			$this->generateContactFormFirst( $url, 'stf_ctx_empty' );
		    }
		}
	    } else {
		$this->generateContactFormFirst( $url, 'stf_ctx_invalid' );
	    }
	}


	function generateContactFormHelper( $msg, $cnt, $options ) {
	    global $wgOut;

	    $wgOut->addHTML( <<<EOF
<script type="text/javascript">
function chooseFriend(o) {
	document.forms.notification.to.value = o.value;
	return false;
}
</script>
<br>$msg
<br><select multiple size="$cnt" onClick="return chooseFriend(this);">
$options
</select>
EOF
);
	}


    } // class
    SpecialPage::addPage( new InviteSpecialPage );
}

?>
