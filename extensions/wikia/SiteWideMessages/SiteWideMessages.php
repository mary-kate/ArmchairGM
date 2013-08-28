<?PHP

/**
 * Main file of SiteWideMessages extension.
 * Include this file ONLY!
 */

if (! Defined ('MEDIAWIKI'))
{
    Echo '[ <b> Error <b /> ] This is not a valid entry point.' . "\n";
    Exit (1);
}
else
{
    # Allow group STAFF to use this extension.
    $wgAvailableRights [] = 'MessageTool';
    $wgGroupPermissions ['staff']['MessageTool'] = True;
			
    $wgExtensionFunctions [] = 'wfIntializeSiteWideMessages';

    function wfIntializeSiteWideMessages ()
    {
        Global $IP;
        Global $wgDisableSiteWideMessages;
        Global $wgDBname, $wgSharedDB, $wgDontWantShared;

        if ($wgDisableSiteWideMessages != True)
        {
            Define ('JIMMY_INSPIRE_001', 1);
            Define ('FIRST_EDIT_MSSG', 2);
            Define ('MSSG_FROM_TEAM', 3);

            Define ('CITIES_DB', '`' . $wgSharedDB . '`.`city_list`');
            Define ('USER_DB', '`' . $wgSharedDB . '`.`user`');
            Define ('GROUPS_DB', '`' . $wgSharedDB . '`.`user_groups`');
            Define ('MSSG_TEXT_DB', '`' . $wgSharedDB . '`.`messages_text`');
            # Define ('MSSG_STATUS_DB', '`' .$wgSharedDB . '`.`messages_meta`');
            Define ('MSSG_STATUS_DB', '`' .$wgSharedDB . '`.`user_mssgstatus`');
            Define ('LOCAL_NEW_TALK_DB', '`' . $wgDBname . '`.`user_newtalk`');
            Define ('SHARED_NEW_TALK_DB',  '`' . $wgSharedDB. '`.`shared_newtalks`');

            # Include files ONLY when SharedDB is defined and desired.
            if (IsSet ($wgSharedDB) && $wgDontWantShared != True)
            {
                # Required by MessageEvents.php
                Require_Once ($IP . '/includes/Setup.php');

                Require_Once ($IP . '/extensions/wikia/SiteWideMessages/SpecialMessageEditor.php');
                Require_Once ($IP . '/extensions/wikia/SiteWideMessages/SpecialDismissMessage.php');
                Require_Once ($IP . '/extensions/wikia/SiteWideMessages/UserMessages.php');
                Require_Once ($IP . '/extensions/wikia/SiteWideMessages/MessageEvents.php');

                //Require_Once ($IP . '/extensions/wikia_newtalk.php');
            }
        }
        /* TODO: OBSOLETE
        # We have to remove hooks from `wikia_newtalk` due to... awkward compatibility. Again. :|
        if (! IsSet ($wgSharedDB) || $wgDisableSiteWideMessages == True || $wgDontWantShared == True)
        {
            Global $wgHooks;
            
            if (Is_Array ($wgHooks ['UserRetrieveNewTalks']))
            {
                $v = Array_Search ('wfWikiaRetrieveSharedNewTalk', $wgHooks ['UserRetrieveNewTalks']);
                if ($v !== Null) Array_Splice ($wgHooks ['UserRetrieveNewTalks'], $v, 1);
            }
            if (Is_Array ($wgHooks ['ArticleEditUpdateNewTalk']))
            {
	            $v = Array_Search ('wfWikiaUpdateSharedNewTalk', $wgHooks ['ArticleEditUpdateNewTalk']);
	            if ($v !== Null) Array_Splice ($wgHooks ['ArticleEditUpdateNewTalk'], $v, 1);
            }
            if (Is_Array ($wgHooks ['UserClearNewTalkNotification']))
            {
	            $v = Array_Search ('wfWikiaClearNotification', $wgHooks ['UserClearNewTalkNotification']);
	            if ($v !== Null) Array_Splice ($wgHooks ['UserClearNewTalkNotification'], $v, 1);
            }
        }
		*/
    }
}

?>
