<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (defined('MEDIAWIKI')) {

$wgExtensionFunctions[] = 'wfWikicitiesMessages';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'WikicitiesMessages',
	'description' => 'Loads some basic messages that are specific to Wikia'
);

function wfWikicitiesMessages(){
  global $wgMessageCache;
  $wgMessageCache->addMessage('wikicitieshome','Wikia Home');
  $wgMessageCache->addMessage('wikicitieshome-url','http://www.wikia.com/');
  $wgMessageCache->addMessage('wikicitieshome-url/fr','http://fr.wikia.com/');
  $wgMessageCache->addMessage('irc','Live wiki help');
  $wgMessageCache->addMessage('irc-url','http://irc.wikia.com/');
  $wgMessageCache->addMessage('shared-problemreport','Report a problem');
  $wgMessageCache->addMessage('shared-problemreport-url','http://www.wikia.com/wiki/Report_a_problem');
  $wgMessageCache->addMessage('wikicities-nav','wikia');
  $wgMessageCache->addMessages(array(

#CreateWiki stuff

'createwiki' => 'Request a new wiki',
'createwikipagetitle' => 'Request a new wiki',
'createwikitext' => 'You can request a new wiki be created on this page.  Just fill out the form',
'createwikititle' => 'Title for the wiki',
'createwikiname' => 'Name for the wiki',
'createwikinamevstitle' => 'The name for the wiki differs from the title of the wiki in that the name is what will be used to determine the default url.  For instance, a name of "starwars" would be accessible as http://starwars.wikia.com/. The title of the wiki may contain spaces, the name should only contain letters and numbers.',
'createwikidesc' => 'Description of the wiki',
'createwikiaddtnl' => 'Additional Information',
'createwikimailsub' => 'Request for a new Wikia',
'requestcreatewiki' => 'Submit Request',
'createwikisubmitcomplete' => 'Your submission is complete.  If you gave an email address, you will be contacted regarding the new Wiki.  Thank you for using {{SITENAME}}.',
'createwikilang' => 'Default language for this wiki',


#Special:Contact stuff

'contact' => 'Contact Wikia',
'contactpagetitle' => 'Contact Wikia',
'contactproblem' => 'Subject',
'contactproblemdesc' => 'Message',
'createwikidesc' => 'Description of the Wiki',
'contactmailsub' => 'Wikia Contact Mail',
'contactmail' => 'Send',
'yourmail' => 'Your email address',
'contactsubmitcomplete' => 'Thank you for contacting Wikia.',
'contactrealname' => 'Your name',
'contactwikiname' => 'Name of the wiki',
'contactintro' => 'Please read the <a href=http://www.wikia.com/wiki/Report_a_problem>Report a problem</a> page for information on reporting problems and using this contact form.<p />You can contact the Wikia community at the <a href=http://www.wikia.com/wiki/Community_portal>Community portal</a> and report software bugs at <a href=http://bugs.wikia.com>bugs.wikia.com</a>. <p>If you prefer your message to <a href=http://www.wikia.com/wiki/Wikia>Wikia</a> to be private, please use the contact form below. <i>All fields are optional</i>.',

));

}

}
?>
