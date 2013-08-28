<?PHP

/*
 * TODO: JS check if message can be Saved/Send/Delete. Same thing in PHP (not working ATM).
 * CHECK: /Approved for 1.0/ notice means that those queries uses new table definition. [cx]
 */

if (! Defined ('MEDIAWIKI'))
{
    Exit ('THIS IS NOT VALID ENTRY POINT.');
}
else
{
    Global $wgMessageCache;
    
    Require_Once ($IP. '/includes/SpecialPage.php');
    
    SpecialPage::AddPage (new SpecialPage ('MessageEditor', 'MessageTool', True, 'wfSpecialMessageEditor', False));
    $wgMessageCache->AddMessage ('messageeditor', 'Site Wide Messages: Editor');
    
    function wfSpecialMessageEditor ()
    {
        Global $wgOut;
        Global $wgParser;

        # First... 
        if (! MessageEditor::AuthUser ())
        {
            $oForm .= wfElementClean ('h3', Null, 'Only members of Wikia staff can send messages!');
        }
        # Special AJAX request: Retrieves message content.
        else if ($_REQUEST ['mAction'] == 'AjaxGetMessage')
        {
            Header ('Content-Type: text/xml');
            Exit (MessageContainer::CreateFromID ($_REQUEST ['mID'], $_REQUEST ['mLanguage'])->ToXML ());
        }
        # Special AJAX request: Parses given WikiText to HTML.
        else if ($_REQUEST ['mAction'] == 'AjaxGetWikiText')
        {
            Header ('Content-Type: text/html');
            Exit ($wgParser->Parse ($_REQUEST ['mContent'], Title::newFromText ('MessageEditor', NS_SPECIAL), new ParserOptions ())->GetText ());
        }
        else
        {
            $uMessage = MessageContainer::CreateFromID ($_REQUEST ['mID'], $_REQUEST ['mLanguage']);

	        if (! IsSet ($uMessage) || $uMessage == False)
	            Exit ('<h3> Fatal ERROR! </h3> Could not create instance of MessageContainer. <br />');

	        # Update MessageContainer fields with user values!
	        if (IsSet ($_REQUEST ['mContent'])) $uMessage->SetContent ($_REQUEST ['mContent']);
	        if (IsSet ($_REQUEST ['mComment'])) $uMessage->SetComment ($_REQUEST ['mComment']);
	        if (IsSet ($_REQUEST ['mLanguage'])) $uMessage->SetLang ($_REQUEST ['mLanguage']);
	        
	        if (! IsSet ($_REQUEST ['mAction']))
            {
                # Default action is COMPOSE
            }
            else if ($_REQUEST ['mAction'] == 'Delete')
            {
                $v = MessageEditor::Delete ($uMessage);
                $uMessage = new MessageContainer ();
                
                $oForm = wfElementClean ('p', Null, 'Message (ID: ' . $v . ') ... OK !');
            }
            else if ($_REQUEST ['mAction'] == 'Save')
            {
                $v = MessageEditor::Save ($uMessage);
                $uMessage = new MessageContainer ();
                
                $oForm = wfElementClean ('p', Null, 'Message (ID: ' . $v . ') ... OK!');
            }
            else if ($_REQUEST ['mAction'] == 'Send')
            {
                if ($_REQUEST ['mSendMode'] == 'ALL')
                    $v = MessageEditor::Send ($uMessage, MessageEditor::SEND_MODE_ALL);
	            if ($_REQUEST ['mSendMode'] == 'USER')
                    $v = MessageEditor::Send ($uMessage, MessageEditor::SEND_MODE_USER, $_REQUEST ['mRecipientUser']);
                if ($_REQUEST ['mSendMode'] == 'GROUP')
                    $v = MessageEditor::Send ($uMessage, MessageEditor::SEND_MODE_GROUP, $_REQUEST ['mRecipientGroup']);
                
                if ($v === True)
                    $oForm = wfElementClean ('p', Null, 'Message (ID: ' . $uMessage->GetID () . ') was sent!');
                else if ($v === False)
                    $oForm = wfElementClean ('p', Null, 'Incorrect recipient name! Message could not be sent.');
                
                $uMessage = new MessageContainer ();
            }

            $oForm .= MessageEditor::CreateForm ($uMessage, $_REQUEST ['mSendMode'], $_REQUEST ['mRecipient']);
        }

        $wgOut->SetPageTitle ('Manage shared messages');
        $wgOut->AddHTML ($oForm);
    }
}

/**
 * Class for browsing tables with messages.
 *
 * @Author: CorfiX (corfix@wikia.com)
 */

class MessageBrowser
{
    /**
     * This retrieves all messages from DB.
     *
     * @Return Array (ID, Lang, Comment)
     */
    public static function GetMessagesFromDB ()
    {
        $DB =& wfGetDB (DB_SLAVE);
        # [cx] Approved for 1.0
        $dbResult = $DB->Query ('SELECT DISTINCT mssg_id, mssg_lang, mssg_name FROM ' . MSSG_TEXT_DB . ' ORDER BY mssg_revision DESC;', __METHOD__);
        
        $dbMessages = Array ();
        $_dbID = Array ();
        $_dbLang = Array ();
        $_dbName = Array ();

		while ($dbObject = $DB->FetchObject ($dbResult))
		{
		    $dbMessages [] = Array ('ID' => $dbObject->mssg_id, 'Lang' => $dbObject->mssg_lang, 'Comment' => $dbObject->mssg_name);
		    $_dbID [] = $dbObject->mssg_id;
		    $_dbLang [] = $dbObject->mssg_lang;
		    $_dbName [] = $dbObject->mssg_name;
		}
        $DB->FreeResult ($dbResult);

        Array_Multisort ($_dbID, SORT_ASC, $_dbLang, SORT_ASC, SORT_STRING, $_dbName, SORT_ASC, SORT_STRING, $dbMessages);
        
        return $dbMessages;
    }
}

/**
 * Class for creating and editing messages.
 *
 * @Author: CorfiX (corfix@wikia.com)
 */
class MessageEditor
{
    /**
     * MessageEditor::SEND_MODE_CUSTOM
     * Used as an argument for MessageEditor::Send method.
     * 
     * @Var String
     * @Example  MessageEditor::Send ($uMessage, SEND_MODE_CUSTOM, Array ('Angela', 'Jimbo', 'CorfiX'));
     */
    const SEND_MODE_CUSTOM = 'CUSTOM';
    /**
     * MessageEditor::SEND_MODE_GROUP
     * Used as an argument for MessageEditor::Send method.
     * 
     * @Var String
     * @Example MessageEditor::Send ($uMessage, SEND_MODE_GROUP, 'sysop');
     */
    const SEND_MODE_GROUP = 'GROUP';
    /**
     * MessageEditor::SEND_MODE_USER
     * Used as an argument for MessageEditor::Send method.
     * 
     * @Var String
     * @Example MessageEditor::Send ($uMessage, SEND_MODE_GROUP, 'CorfiX');
     */
    const SEND_MODE_USER = 'USER';
	/**
	 * MessageEditor:: SEND_MODE_ALL
	 * Used as an argument for MessageEditor::Send method.
	 * 
     * @Var String
     * @Example MessageEditor::Send ($uMessage, SEND_MODE_ALL);
     */
    const SEND_MODE_ALL = 'ALL';
    
    public static $DefaultGroupOption = '*';
    public static $DefaultLanguageOption = 'en';
    public static $DefaultRecipientOption = 'USER';
    
    public static $ForbiddenGroups = Array ('autoconfirmed', 'bot', 'user');
    public static $ForbiddenLanguages = Array ();
    
    
    
    /**
     * Checks if current user ($wgUser) has permission to use this extension.
     * Currently it is: Group=>Staff || Rights=>MessageTool.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Return Boolean
     */
    public static function AuthUser ()
    {
        Global $wgUser;

        return (In_Array ('MessageTool', $wgUser->GetRights ()) || In_Array ('staff', $wgUser->GetGroups ()));
    }
    /**
     * Checks (or even sets !!!) if this extension is enabled on this server.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Param Boolean $e
     */
    public static function ExtensionEnabled ($e = Null)
    {
        if ($e === Null)
            return (Boolean) $wgDisableSiteWideMessages;
        /* TODO
        else if ($e !== Null && $e == True)
            $e = True;
        else if ($e !== Null && $e == False)
            $e = False;
		*/
    }
    /**
     * Removes given message from DB.
     * Also removes user annotations.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Param MessageContainer $uMessage
     * @Param Boolean			- [Optional] $forceAction
     * 
     * @Return Variant [Boolean/Int]
     */
    public static function Delete (MessageContainer $uMessage, $forceAction = False)
    {
        //if (self::ExtensionEnabled () && ($forceAction || self::AuthUser ()))
        if ($forceAction || self::AuthUser ())
        {
	        $DB =& wfGetDB (DB_MASTER);
            # [cx] Approved for 1.0
	        //$v = $DB->Delete (MSSG_TEXT_DB, Array ('mssg_id' => $uMessage->GetID (), 'mssg_lang' => $uMessage->GetLang ()), 'MessageTool::Delete');
	        $v |= $DB->Query ('DELETE FROM ' . MSSG_TEXT_DB . ' WHERE ' . 'mssg_id = ' . $uMessage->GetID () . ' AND ' . 'mssg_lang = \'' . $uMessage->GetLang () . '\';', __METHOD__);
	        $v |= $DB->Query ('DELETE FROM ' . SHARED_NEW_TALK_DB . ' WHERE sn_user IN ' . '(' . ' SELECT user_id FROM ' . MSSG_STATUS_DB . ' WHERE mssg_id = ' . $uMessage->GetID () . ')', __METHOD__);

	        return ($v) ? $uMessage->GetID () : False;
        }
    }
    /**
     * Reverts to previous (or the given) message revision.
     *
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Param MessageContainer	- $uMessage
     * @Param Int				- [Optional] $revertToID
     * @Param Boolean			- [Optional] $forceAction
     * 
     * @Return Variant [Boolean/Int]
     */
    public static function Revert (MessageContainer $uMessage, $revertToID, $forceAction = False)
    {
        //if (self::ExtensionEnabled () && ($forceAction || self::AuthUser ()))
        if ($forceAction || self::AuthUser ())
        {
	        $DB =& wfGetDB (DB_MASTER);
            // [cx] Approved for 1.0
	        $v |= $DB->Query ('DELETE FROM ' . MSSG_TEXT_DB . ' WHERE ' . 'mssg_id = ' . $uMessage->GetID () . ' AND ' . 'mssg_lang = ' . $uMessage->GetLang () . ' AND ' . 'mssg_revision > ' . $revertToID . ';', __METHOD__);

	        return ($v) ? $uMessage->GetID () : False;
        }
    }
    /**
     * Saves given message to DB.
     *
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Param MessageContainer $uMessage
     * @Param Boolean			- [Optional] $forceAction
     * 
     * @Return Variant [Boolean/Int]
     */
    public static function Save (MessageContainer $uMessage, $forceAction = False)
    {
        //if (self::ExtensionEnabled () && ($forceAction || self::AuthUser ()))
        if ($forceAction || self::AuthUser ())
        {
	        $DB =& wfGetDB (DB_MASTER);
            # [cx] Approved for 1.0
            $v |= $DB->Query
	        (
	            'INSERT INTO ' . MSSG_TEXT_DB
	            . ' (mssg_id, mssg_lang, mssg_user, mssg_name, mssg_text, mssg_revision)'
	            . ' VALUES'
				. ' (' . '\'' . $uMessage->GetID () . '\''
				. ', ' . '\'' . Str_Replace ('\'', '\'\'', $uMessage->GetLang ()) . '\''
				. ', ' . '\'' . Str_Replace ('\'', '\'\'', $uMessage->GetAuthor ()) . '\''
				. ', ' . '\'' . Str_Replace ('\'', '\'\'', $uMessage->GetComment ()) . '\''
				. ', ' . '\'' . Str_Replace ('\'', '\'\'', $uMessage->GetContent ()) . '\''
				. ', ' . '\'' . (((Int) $uMessage->GetRevision ()) + 1) . '\''
				. ' );'
	            , __METHOD__
	        );

	        return ($v) ? MySQL_Insert_ID () : False;
        }
    }
    /**
     * Sends given message to:
     *  - Specific group (if MessageEditor::SEND_MODE_GROUP is passed as 2nd argument)
     *  - Specific user (if MessageEditor::SEND_MODE_USER is passed as 2nd argument)
     *  - All users (if MessageEditor::SEND_MODE_ALL is passed as 2nd argument)
     * 
     * If you'd like to send message to specific user or group you have to pass it's name as 3rd argument.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Access Public
     * @Static 
     * 
     * @Param MessageContainer	- $uMessage
     * @Param String [ENUM]		- $mSendMode
     * @Param String			- $mRecipient
     * @Param Boolean			- [Optional] $forceAction
     * 
     * @Return Boolean
     */
    public static function Send (MessageContainer $uMessage, $mSendMode, $mRecipient = '', $forceAction = False)
    {
        Global $wgOut, $wgUser;
        Global $wgGroupPermissions;

        //if (self::ExtensionEnabled () && ($forceAction || self::AuthUser ()))
        if ($forceAction || self::AuthUser ())
        {
	        $mID = ($uMessage->GetID () > 0 ? $uMessage->GetID () : self::Save ($uMessage));
	        
	        switch ($mSendMode)
	        {
	            case self::SEND_MODE_ALL:
	                
	                self::__sendToAll ($mID, $mRecipient); break;
	                
	            case self::SEND_MODE_USER:
	                
	                self::__sendToUser ($mID, $mRecipient); break;
	                /*
	                if (IsSet ($mRecipient) && $mRecipient != '')
	                    $mRecipientUser = User::NewFromName ($mRecipient);
                    else
                        return False;
	                if (IsSet ($mRecipientUser) && $mRecipientUser->GetID () != 0)
	                {
	                    self::__sendToUser ($mID, $mRecipientUser->GetName (), $mRecipientUser->GetID ());
	                    
	                    $mRecipientUser->SetNewTalk (True);
                        $mRecipientUser->InvalidateCache ();
	                }
	                else
	                    self::__sendToUser ($mID, $mRecipient, 0);
                    break;                      
	                */
	            case self::SEND_MODE_GROUP:
	                
	                if (Key_Exists ($mRecipient, $wgGroupPermissions))
	                    self::__sendToGroup ($mID, $mRecipient);
	                else
	                    return False;
                    break;
                    
	            case self::SEND_MODE_CUSTOM:
	                
	                self::__sendToCustomGroup ($mID, $mRecipient); break;
	                
	            default:
	                return False;
	        }
	        
            return True;
        }
        else
        {
            return Null;
        }
    }
    /**
     * Access this method through MessageEditor::Send !
     *
     * @Param Int $mID
     * @Return Int
     */
    private static function __sendToAll ($mID)
    {
        Global $wgDBname;
        # [cx] Approved for 1.0
        $DB =& wfGetDB (DB_MASTER);
        $DB->Begin ();

        $v |= $DB->Query
        (
	          'INSERT INTO ' . MSSG_STATUS_DB
            . ' (user_id, user_ip, user_mssg_id, user_mssg_status, user_mssg_timestamp)'
            . ' SELECT DISTINCT user_id, user_name, ' . $mID . ', ' . MSSG_STATUS_SHOW . ', ' . Time ()
			. ' FROM ' . USER_DB
            . ' ON DUPLICATE KEY UPDATE'
            . ' user_mssg_status = ' . MSSG_STATUS_SHOW
            . ';'
            , __METHOD__
        );
        $v |= $DB->Query
        (
            'INSERT IGNORE INTO ' . LOCAL_NEW_TALK_DB
            . ' (user_id)'
            . ' SELECT user_id'
            . ' FROM ' . USER_DB
            . ' WHERE user_id <> 0'
            . ';'
            , __METHOD__
        );
        $v |= $DB->Query
        (
            'INSERT IGNORE INTO ' . SHARED_NEW_TALK_DB
            . ' (sn_user_id, sn_wiki)'
            . ' SELECT user_id ' . ', ' . '\'' . $wgDBname . '\''
            . ' FROM ' . USER_DB
            . ' WHERE user_id <> 0'
            . ';'
            , __METHOD__
        );
        
        if ($v)
            $DB->Commit ();
        else
            $DB->Rollback ();
        
        return $v;
    }
    /**
     * Access this method through MessageEditor::Send !
     *
     * @Param Int $mID
     * @Param String $mRecipientUser
     * @Return Int
     */
    private static function __sendToUser ($mID, $mRecipientName)
    {
        Global $wgDBname;
        # [cx] Approved for 1.0
        $DB =& wfGetDB (DB_MASTER);
        $DB->Begin ();
        
        $mRecipientUser = User::NewFromName ($mRecipientName);
        $mRecipientID = ( IsSet ($mRecipientUser) ? $mRecipientUser->GetID () : 0 );

        $v |= $DB->Query
        (
	        'INSERT INTO ' . MSSG_STATUS_DB
            . ' (user_id, user_ip, user_mssg_id, user_mssg_status, user_mssg_timestamp)'
            . ' VALUES'
            . ' (' . '\'' . $mRecipientID . '\''
            . ', ' . '\'' . $mRecipientName . '\''
            . ', ' . '\'' . $mID . '\''
            . ', ' . '\'' . MSSG_STATUS_SHOW . '\''
            . ', ' . '\'' . Time () . '\''
            . ' )'
            . ' ON DUPLICATE KEY UPDATE'
            . ' user_mssg_status = ' . MSSG_STATUS_SHOW
			. ';'
            , __METHOD__
        );
        $v |= $DB->Query
        (
	        'INSERT IGNORE INTO ' . LOCAL_NEW_TALK_DB
            . ' (user_id, user_ip)'
            . ' VALUES'
            . ' (' . '\'' . $mRecipientID . '\''
            . ', ' . '\'' . $mRecipientName . '\''
            . ' );'
            , __METHOD__
        );
        $v |= $DB->Query
        (
	        'INSERT IGNORE INTO ' . SHARED_NEW_TALK_DB
//            . ' (sn_user_id, sn_user_ip, sn_wiki)'
            . ' (sn_user_id, sn_wiki)'
            . ' VALUES'
            . ' (' . '\'' . $mRecipientID . '\''
//          . ', ' . '\'' . $mRecipientName . '\''
            . ', ' . '\'' . $wgDBname . '\''
            . ' );'
            , __METHOD__
        );
        
        //$mRecipientUser->SetNewTalk (True);
        //$mRecipientUser->InvalidateCache ();
            
        if ($v)
            $DB->Commit ();
        else
            $DB->Rollback ();
        
        return $v;
    }
    /**
     * Access this method through MessageEditor::Send !
     *
     * @Param Int $mID
     * @Param String $mRecipientGroup
     * @Return Int
     */
    private static function __sendToGroup ($mID, $mRecipientGroup)
    {
        Global $wgDBname;
        # [cx] Approved for 1.0
        
        $DB =& wfGetDB (DB_MASTER);
        $DB->Begin ();

        $v |= $DB->Query
        (
	          'INSERT INTO ' . MSSG_STATUS_DB
            . ' (user_id, user_ip, user_mssg_id, user_mssg_status, user_mssg_timestamp)'
		    . ' SELECT DISTINCT user_id, user_name, ' . '\'' . $mID . '\'' . ', ' . MSSG_STATUS_SHOW . ', ' . Time ()
		    . ' FROM ' . USER_DB . ' JOIN ' . GROUPS_DB . ' ON user_id = ug_user'
		    . ' WHERE ug_group=' . '\'' . $mRecipientGroup . '\''
            . ' ON DUPLICATE KEY UPDATE'
            . ' user_mssg_status = ' . MSSG_STATUS_SHOW
		    . ';'
		    , __METHOD__
        );
        $v |= $DB->Query
        (
	        'INSERT IGNORE INTO ' . LOCAL_NEW_TALK_DB
			. ' (user_id)'
            . ' SELECT user_id'
			. ' FROM ' . USER_DB . ' JOIN ' . GROUPS_DB . ' ON user_id = ug_user'
            . ' WHERE user_id <> 0 AND ug_group = ' . '\'' . $mRecipientGroup . '\''
            . ';'
            , __METHOD__
        );
        $v |= $DB->Query
        (
	        'INSERT IGNORE INTO ' . SHARED_NEW_TALK_DB
            . ' (sn_user_id, sn_wiki)'
            . ' SELECT user_id, ' . '\'' . $wgDBname . '\''
            . ' FROM ' . USER_DB . ' JOIN ' . GROUPS_DB . ' ON user_id = ug_user'
            . ' WHERE user_id <> 0 AND ug_group = ' . '\'' . $mRecipientGroup . '\''
            . ';'
            , __METHOD__
        );
                
        if ($v)
            $DB->Commit ();
        else
            $DB->Rollback ();
        
        return $v;
    }
    /**
     * Access this method through MessageEditor::Send !
     *
     * @Param Int $mID
     * @Param Array $mRecipientGroup
     * @Return Int
     */
    private static function __sendToCustomGroup ($mID, $mRecipientGroup)
    {
        Global $wgDBname;
        # [cx] Approved for 1.0

        $DB =& wfGetDB (DB_MASTER);
        $DB->Begin ();
        
        foreach ($mRecipientList as $mRecipient)
        {
            $mRecipientString .= ',' . '\'' . $mRecipient . '\'';
        }
        $mRecipientString = SubStr ($mRecipientString, 1);

        $v |= $DB->Query
        (
	          'INSERT INTO ' . MSSG_STATUS_DB
            . ' (user_id, user_ip, user_mssg_id, user_mssg_status, user_mssg_timestamp)'
		    . ' SELECT DISTINCT user_id, user_name, ' . "'" . $mID . "'" . ', ' . MSSG_STATUS_SHOW . ', ' . Time ()
		    . ' FROM ' . USER_DB
		    . ' WHERE user_name IN (' . $mRecipientString .')'
            . ' ON DUPLICATE KEY UPDATE'
            . ' user_mssg_status = ' . MSSG_STATUS_SHOW
            . ';'
		    , __METHOD__
        );
        $v |= $DB->Query
        (
            'INSERT IGNORE INTO ' . LOCAL_NEW_TALK_DB
            . ' (user_id)'
            . ' SELECT user_id'
            . ' FROM ' . USER_DB
            . ' WHERE user_name IN (' . $mRecipientString .')'
            . ';'
            , __METHOD__
        );
        $v |= $DB->Query
        (
            'INSERT IGNORE INTO ' . SHARED_NEW_TALK_DB
			. ' (sn_user_id, sn_wiki)'
			. ' SELECT user_id, ' . '\'' . $wgDBname . '\''
			. ' FROM ' . USER_DB
			. ' WHERE user_name IN (' . $mRecipientString .')'
			. ';'
			, __METHOD__
		);
        
        if ($v)
            $DB->Commit ();
        else
            $DB->Rollback ();
        
        return $v;
    }
    /**
     * Builds and returns HTML form based on given arguments.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     *
     * @Param MessageContainer	- $uMessage
     * @Param String [Enum]		- [Optional] $mSendMode
     * @Param String 			- [Optional] $mRecipient
     * 
     * @Return String
     */
    public static function CreateForm (MessageContainer $uMessage, $mSendMode = 'USER', String $mRecipient = Null)
    {
        Global $wgOut;
        
        Global $wgLanguageNames;
        Global $wgGroupPermissions;

        # Default VALUE!
        if ($mSendMode != 'ALL' && $mSendMode != 'USER' && $mSendMode != 'GROUP')
        {
            $mSendMode = MessageEditor::$DefaultRecipientOption;
        }

        foreach ($wgLanguageNames as $mLangCode => $mLangName)
        {
            if (! In_Array ($mLangCode, MessageEditor::$ForbiddenLanguages))
            {
                if (($mLanguage != Null && $mLangCode == $mLanguage) || ($mLanguage == Null && $mLangCode == MessageEditor::$DefaultLanguageOption))
                    $fLangOptions .= wfElement ('option', Array ('id' => $mLangCode, 'value' => $mLangCode, 'selected' => '1'), ' [' . $mLangCode . '] ' . $mLangName);
                else
                    $fLangOptions .= wfElement ('option', Array ('id' => $mLangCode, 'value' => $mLangCode), ' [' . $mLangCode . '] ' . $mLangName);
            }    
        }
        foreach ($wgGroupPermissions as $mGroupCode => $mGroupValues)
        {
            if (! In_Array ($mGroupCode, MessageEditor::$ForbiddenGroups))
            {
                if (($mRecipient != Null && $mGroupCode == $mRecipient) || ($mRecipient == Null && $mGroupCode == MessageEditor::$DefaultGroupOption))
                    $fGroupOptions .= wfElement ('option', Array ('value' => $mGroupCode, 'selected' => '1'), $mGroupCode);
                else
                    $fGroupOptions .= wfElement ('option', Array ('value' => $mGroupCode), $mGroupCode);
            }
        }
        
        foreach (MessageBrowser::GetMessagesFromDB () as $dbMsg)
        {
            if ($previousOption !== ($oName = $dbMsg ['ID']))
            {
                if ($previousOption) $dbMessages .= wfCloseElement ('optgroup') . "\r\n";
                $dbMessages .= wfOpenElement ('optgroup', Array ('label' => $dbMsg ['ID']));
            }
            $previousOption = $oName;
            
            $dbMessages .= wfElement ('option', Array ('value' => $dbMsg ['ID'] . '|#|' . $dbMsg ['Lang']), ' [' . $dbMsg ['Lang'] . '] ' . $dbMsg ['Comment']);
        }
        
        return ''
            . MessageHTMLForm::GenerateJScript ()
	        . MessageHTMLForm::GeneratePanePreview ($uMessage)
			. MessageHTMLForm::GeneratePaneCompose ($uMessage, $fLangOptions, $fGroupOptions, $mSendMode)
		    . MessageHTMLForm::GeneratePaneBrowse ($dbMessages);
    }
}
/**
 * Storage class.
 * 
 * In most cases when working with MessageTools (i.e. creating or deleting message) you have to create MessageContainer first.
 *
 */
class MessageContainer
{
    private $__ID;

    private $__Author;
    
    private $__Lang;
    private $__Time;
    private $__Content;
    private $__Comment;
    
    private $__Revision;

    public function MessageContainer ($_Author = '', $_Lang = '', $_Time = '', $_Content = '', $_Comment = '', $_Revision = 0)
    {
        Global $wgUser;
        
        $this->__ID = NULL;

        $this->__Lang = ($_Lang == '') ? 'en'    : $_Lang;
        $this->__Time = ($_Time == '') ? Time () : $_Time;
        $this->__Author = (!$_Author || $_Author == '') ? $wgUser->GetName () : $_Author;
        $this->__Content = $_Content;
        $this->__Comment = $_Comment;
        $this->__Revision = $_Revision;
    }
    /**
     * Creates new MessageContainer with data fetched from DB.
     *
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Param Int 				- $mID
     * @param String 			- $mLang
     * @Param Int				- $mRevision
     * 
     * @Return MessageContainer
     */
    public static function CreateFromID ($mID, $mLang = '', $mRevision = 0)
    {
        $DB =& wfGetDB (DB_SLAVE);
        
        #TODO: Add default Lang!
        $DefaultLang = MessageEditor::$DefaultLanguageOption;

        # Defaults: 
        if (! IsSet ($mLang) || $mLang == '')        $mLang = $DefaultLang;

        $dbResult = $DB->Query
        (
           'SELECT * FROM ' . MSSG_TEXT_DB
            . ' WHERE mssg_id = ' . '\'' . $mID . '\' AND mssg_lang = ' . '\'' . $mLang . '\'' . ($mRevision != 0 ? ' AND mssg_revision = ' . $mRevision : '')
            . ' ORDER BY mssg_revision DESC LIMIT 1;'
            , __METHOD__
        );

		$msgData = $DB->FetchObject ($dbResult);
		            $DB->FreeResult ($dbResult);

	    if ($msgData != False)
	    {
            $uMessage = new MessageContainer ($msgData->mssg_user, $msgData->mssg_lang, $msgData->mssg_date, $msgData->mssg_text, $msgData->mssg_name, $msgData->mssg_revision);
            $uMessage->__ID = (Int) $mID;
	    }
        else
        {
	        $uMessage = new MessageContainer (Null, $mLang);
            $uMessage->__ID = (Int) $mID;
        }

        return $uMessage;
    }

    public function GetID ()
    {
        return $this->__ID;
    }
    public function GetTime ()
    {
        return Date ('d-m-Y H:i:j', $this->__Time);
        //return $this->__Time;
    }
    public function GetAuthor ()
    {
        return $this->__Author;
    }
    public function SetLang ($e)
    {
        $this->__Lang = $e;
    }
    public function GetLang ()
    {
        return $this->__Lang;
    }
    public function SetContent ($e)
    {
        $this->__Content = $e;
    }
    public function GetContent ()
    {
        return $this->__Content;
    }
    public function SetComment ($e)
    {
        $this->__Comment = $e;
    }
    public function GetComment ()
    {
        return $this->__Comment;
    }
    public function GetRevision ()
    {
        return (Int) $this->__Revision;
    }

    public function IsValid ()
    {
        return ($this->__ID !== NULL);
    }
    
    public function toXML ()
    {
        return ''/*'<?xml version="1.0" encoding="ISO-8859-1"?>'*/
                . '<o>'
                . '<ID><![CDATA[' . $this->GetID () . ']]></ID>'
                . '<Time><![CDATA[' . $this->GetTime () . ']]></Time>'
                . '<Lang><![CDATA[' . $this->GetLang () . ']]></Lang>'
                . '<Author><![CDATA[' . $this->GetAuthor () . ']]></Author>'
                . '<Content><![CDATA[' . $this->GetContent () . ']]></Content>'
                . '<Comment><![CDATA[' . $this->GetComment () . ']]></Comment>'
                . '<Revision><![CDATA[' . $this->GetRevision () . ']]></Revision>'
                . '</o>';
    }
    public function __toString ()
    {
        return 'mID : ' . $this->mID . "\r\n" . 'mContent : ' . $this->mContent;
    }
}
/**
 * Auxiliary class for generating JS & HTML.
 *
 */
class MessageHTMLForm
{
    /**
     * Returns URI to SpecialPage containing MessageTool
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     *
     * @Return String
     */
    public static function GetURI ()
    {
        return Title::newFromText ('MessageEditor', NS_SPECIAL)->GetFullURL () . '?';
    }
    /**
     * Returns JavaScript for form validation and Ajax communication.
     * First param indicates if JS should work in debug mode.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Param Int				- $DEBUG. Default: 0
     * 
     * @Returns String
     */
    public static function GenerateJScript ($DEBUG = 'false')
    {
        return '
		<script>

		DEBUG = ' . $DEBUG . ' ;

		function Confirm ()
		{
			if (DEBUG) alert ("Function::Confirm");

			document.getElementById ("eButtons").innerHTML = \'<small>You need to confirm the changes before submiting!</small>\';
		}
		function Validate ()
		{
			if (DEBUG) alert ("Function::Validate");

			document.getElementById ("formFeedbackHead").innerHTML = "";
			document.getElementById ("formFeedbackTail").innerHTML = "";

			if (document.getElementById ("msgForm").mAction != "Delete")
			{
				if (document.getElementById ("msgForm").mAction == "Send" && document.getElementById ("mSendModeU").checked == "1" && document.getElementById ("mRecipientUser").value == "")
				{
					document.getElementById ("formFeedbackHead").innerHTML = "<small>Please select recipient(s)!</small>";
					return false;
				}
				if (document.getElementById ("mContent").value == "")
				{
					document.getElementById ("formFeedbackTail").innerHTML = "<small>Please enter message text!</small>";
					return false;
				}
				if (document.getElementById ("mComment").value == "")
				{
					document.getElementById ("formFeedbackTail").innerHTML = "<small>Please enter message name!</small>";
					return false;
				}
			}
			return true;
		}
		function SetHash (q)
		{
			location.hash = q;
			return True;
		}
		function AjaxGetPreview ()
		{
			if (DEBUG) alert ("Function::AjaxGetPreview");

			var o = (window.ActiveXObject) ? new ActiveXObject ("Microsoft.XMLHTTP") : new XMLHttpRequest ();
			var v = document.getElementById ("mContent").value;

			document.getElementById ("WikiTextPreview").innerHTML = "";

			if (o)
			{
				o.onreadystatechange = function ()
				{
					if (o.readyState == 4 && o.status == 200)
					{
						if (o.responseText != "")
						{
							document.getElementById ("WikiTextPreview").innerHTML = o.responseText;
						}

						document.getElementById ("eButtons").innerHTML = \'<input name="mAction" type="submit" value="Save" id="fSave" />&nbsp;<input name="mAction" type="submit" value="Send" id="fSend"  />&nbsp;<input name="mAction" type="submit" value="Delete" id="fDelete" />&nbsp\';
					}
				}

				if (DEBUG) document.location= "' . self::GetURI () . '&mAction=AjaxGetWikiText&mContent=" + escape (v);

				o.open ("GET", "' . self::GetURI () . '&mAction=AjaxGetWikiText&mContent=" + escape (v));
				o.send (null);

				return true;
			}
			else
			{
				return false;
			}
		}
		function AjaxGetMessage ()
		{
			if (DEBUG) alert ("Function::AjaxGetMessage");

			var o = (window.ActiveXObject) ? new ActiveXObject ("Microsoft.XMLHTTP") : new XMLHttpRequest ();
			var v = document.getElementById ("fBrowse").fDBMsg.value.split ("|#|");

			document.getElementById ("WikiTextPreview").innerHTML = "";

			if (v [0] != "" && v [1] != "" && o)
			{
				o.onreadystatechange = function ()
				{
					if (o.readyState == 4 && o.status == 200)
					{
						var oXML = o.responseXML;

						if (oXML)
						{
							for (var i in oXML.firstChild.childNodes)
							{
								if (oXML.firstChild.childNodes [i].nodeName == "ID")
								{
									document.getElementById ("mID").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Lang")
								{
									var lOptions = document.getElementById ("mLanguage").childNodes; for (var e in lOptions)
									{
										lOptions [e].selected = (lOptions [e].id == oXML.firstChild.childNodes [i].firstChild.nodeValue ? "1" : "");
									}
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Time")
								{
									document.getElementById ("mTime").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Author")
								{
									document.getElementById ("mAuthor").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Content")
								{
									document.getElementById ("mContent").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Comment")
								{
									document.getElementById ("mComment").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
								else if (oXML.firstChild.childNodes [i].nodeName == "Revision")
								{
									document.getElementById ("mRevision").value = oXML.firstChild.childNodes [i].firstChild.nodeValue;
								}
							}

							Confirm ();
						}
					}
				}

				if (DEBUG) document.location="' . self::GetURI () . '&mAction=AjaxGetMessage&mID=" + v [0] + "&mLanguage=" + v [1];
				//alert ("' . self::GetURI () . '&mAction=AjaxGetMessage&mID=" + v [0] + "&mLanguage=" + v [1]);
				o.open ("GET", "' . self::GetURI () . '&mAction=AjaxGetMessage&mID=" + v [0] + "&mLanguage=" + v [1]);
				o.send (null);

				return true;
			}
			else
			{
				return false;
			}
		}

		</script>
		';
    }
    /**
     * Returns HTML for message browsing based on given messages collection.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Param unknown_type $dbMessages
     * 
     * @Return String
     */
    public static function GeneratePaneBrowse ($dbMessages)
    {
       return '
		<div id="PaneBrowse"><a name="Browse" ></a>
		<fieldset> <legend>Browse</legend>
		<form method="POST" id="fBrowse">
				<table>
					<tr>
						<td width="650">
							<select name="fDBMsg">
								' . $dbMessages . '
							</select>
						</td>
						<td width="125">
							<p align="right"><button type="button" onClick="return AjaxGetMessage (this);"> [ Load ] </button></p>
						</td>
					</tr>
				</table>
		</form>
		</fieldset>
		</div>
		';
    }
    /**
     * Returns HTML for messages previewing.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Return String
     */
    public static function GeneratePanePreview (MessageContainer $uMessage)
    {
        Global $wgParser;
        
        return '
		<div id="PanePreview"><a name="Preview"></a>
		<fieldset> <legend>Preview</legend>
			<div id="WikiTextPreview">
				' . $wgParser->Parse ($uMessage->GetContent (), Title::newFromText ('MessageEditor', NS_SPECIAL), new ParserOptions ())->GetText () . '
			</div>
		</fieldset>
		</div>
		';
    }
    /**
     * Returns HTML for message composing.
     * 
     * @Author CorfiX (corfix@wikia.com)
     * @Since 1.0
     * 
     * @Return String
     */
    public static function GeneratePaneCompose (MessageContainer $uMessage, $fLangOptions, $fGroupOptions, $mSendMode)
    {
        return '
		<div id="PaneCompose"><a name="Compose" ></a>
		<fieldset> <legend>Edit</legend>
			<form method="POST" id="msgForm" action="' . self::GetURI () . '" onSubmit="return Validate ();">
				<fieldset> <legend>Options</legend>
				<table>
					<tr>
						<td width="125">
							<label for="mID">Message ID</label>
						</td>
						<td width="650">
							<input name="mID" id="mID" type="text" size="48" readonly="1" value="' . $uMessage->GetID () . '"/>
							</input>
						</td>
					</tr>
					<tr>
						<td width="125">
							<label for="mRevision">Revision</label>
						</td>
						<td width="650">
							<input name="mRevision" id="mRevision" type="text" size="48" readonly="1" value="' . $uMessage->GetRevision () . '"/>
						</td>
					</tr>
					<tr>
						<td width="125">
							<label for="mTime">Last edit on</label>
						</td>
						<td width="650">
							<input name="mTime" id="mTime" type="text" size="48" disabled="1" value="' . $uMessage->GetTime () . '"/>
						</td>
					</tr>
					<tr>
						<td width="125">
							<label for="mAuthor">Last edit by</label>
						</td>
						<td width="650">
							<input name="mAuthor" id="mAuthor" type="text" size="48" disabled="1" value="' . $uMessage->GetAuthor () . '"/>
						</td>
					</tr>
					<tr>
						<td width="125">
							<label for="mLanguage">Language</label>
						</td>
						<td width="650">
							<select name="mLanguage" id="mLanguage">
								' . $fLangOptions . '
							</select>
						</td>
					</tr>
				</table>
				</fieldset>
	
				<fieldset> <legend>Recipient</legend>
				<table>
					<tr>
						<td width="25">
							<input name="mSendMode" id="mSendModeA" type="radio" value="ALL" ' . ($mSendMode == 'ALL' ? 'checked=""' : '') . '/>
						</td>
						<td width="100">
							<label for="mSendMode::ALL">All users</label>
						</td>
						<td width="300">
						</td>
						<td width="300">
						</td>
					</tr>
					<tr>
						<td width="25">
							<input name="mSendMode" id="mSendModeG" type="radio" value="GROUP" ' . ($mSendMode == 'GROUP' ? 'checked=""' : '') . '/>
						</td>
						<td width="100">
							<label for="mSendMode::GROUP">Selected group</label>
							
						</td>
						<td width="300">
							<select name="mRecipientGroup" id="mRecipientGroup">
								' . $fGroupOptions . '
							</select>
						</td>
						<td width="300">
						</td>
					</tr>
					<tr>
						<td width="25">
							<input name="mSendMode" id="mSendModeU" type="radio" value="USER" ' . ($mSendMode == 'USER' ? 'checked=""' : '') . '/>
						</td>
						<td width="100">
							<label for="mSendMode::USER">Selected user</label>
						</td>
						<td width="300">
							<input name="mRecipientUser" id="mRecipientUser" type="text" size="48" />
						</td>
						<td width="300">
							<p id="formFeedbackHead" align="right"></p>
						</td>
					</tr>
				</table>
				</fieldset>
	
				<fieldset> <legend>Content</legend>
	
					<table>
						<tr>
							<td width="775" colspan="3">
								<textarea name="mContent" id="mContent" cols="30" rows="10" onchange="return Confirm (this);" >' . $uMessage->GetContent () . '</textarea>
							</td>
						</tr>
						<tr>
							<td width="125">
								<label for="mComment">Comment</label>
							</td>
							<td width="300">
								<input name="mComment" id="mComment" type="text" size="48" value="' . $uMessage->GetComment () . '" />
							</td>
							<td width="350">
								<p id="formFeedbackTail" align="right"></p>
							</td>
						</tr>
					</table>
						
				</fieldset>
	
				<table>
					<tr>
						<td width="125">
							<button type="button" onClick="return AjaxGetPreview (this);"> [ Preview ] </button>	
						</td>
						<td width="650">
						<div id="eButtons">
							<small>You need to confirm changes before submiting!</small>
						</div>
						</td>
						<td width="150">
							<a href=' . self::GetURI () . '/><button type="button"> [ New message ] </button></a>
						</div>
					</tr>
				</table>
			</form>
		</fieldset>
		</div>
		';
    }
}
?>