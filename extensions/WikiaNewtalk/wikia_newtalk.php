<?
# Original code by Tristan Harris.
# Modifications by CorfiX (corfix@wikia.com)

if (! Defined ('MEDIAWIKI'))
{
    Exit ('THIS IS NOT VALID ENTRY POINT.');
}
else
{
    Global $wgSharedDB, $wgDontWantShared;
    Global $wgHooks;
    
	# Include files ONLY when SharedDB is defined and desired.
	if (IsSet ($wgSharedDB) && ! (IsSet ($wgDontWantShared) && $wgDontWantShared == True))
	{
		$wgHooks ['UserRetrieveNewTalks'] [] = 'wfWikiaRetrieveSharedNewTalk';
		$wgHooks ['ArticleEditUpdateNewTalk'] [] = 'wfWikiaUpdateSharedNewTalk';
		$wgHooks ['UserClearNewTalkNotification'] [] = 'wfWikiaClearNotification';
	}
}
function wfWikiaClearNotification ($oUser)
{
    Global	$wgSharedDB, $wgDBname;

    $DB =& wfGetDB(DB_MASTER);

    $DB->Delete ("`$wgDBname`.`user_newtalk`", Array ('user_id' => $oUser->GetID ()));
    $DB->Delete ("`$wgSharedDB`.`shared_newtalks`", Array ('sn_wiki' => $wgDBname, 'sn_user_id' => $oUser->GetID (), 'sn_user_ip' => $oUser->GetName()));
}

function wfWikiaUpdateSharedNewTalk ($data)
{
    Global	$wgSharedDB, $wgDBname;

    $user = User::NewFromName ($data->mTitle->GetDBKey ());

    if (! Is_Object ($user) || $user->GetID () == 0)
    {
        return False;
    }
    else
    {
	    $DB =& wfGetDB (DB_MASTER);
	    
	    $DB->Query
	    (
	          'REPLACE INTO ' . "`$wgSharedDB`.`shared_newtalks`"
	        . ' (sn_user_id, sn_user_ip, sn_wiki, sn_date)'
	        . ' VALUES'
	        . ' (' . '\'' . $user->GetID() . '\''
	        . ', ' . '\'' . addslashes($user->GetName()) . '\''
	        . ', ' . '\'' . $wgDBname . '\''
	        . ', ' . '\'' . wfTimestamp (TS_DB) . '\''
	        . ' );'
	    , 'HOOK > UpdateSharedNewTalk');
    }
}

function wfWikiaRetrieveSharedNewTalk ($user, $uTalks)
{
    Global $wgSharedDB, $wgDBname;
    Global $wgTitle, $wgUser, $wgOut;
    
    if (! $wgTitle->Equals ($wgUser->GetTalkPage ()))
    {
        $DB =& wfGetDB (DB_SLAVE);
        
        $dbResult = $DB->Query
        (
	        'SELECT city_title, city_dbname, city_url, COUNT(city_dbname) AS sn_count '
            . ' FROM ' . "`$wgSharedDB`.`shared_newtalks`" . ', ' . "`$wgSharedDB`.`city_list`"
            . ' WHERE sn_wiki = city_dbname AND sn_user_id = ' . $wgUser->GetID () . ' AND sn_user_ip = \'' . addslashes($wgUser->GetName())  . '\' GROUP BY city_dbname'
            . ';'
            , 'HOOK > RetrieveSharedNewTalk'
        );

        if ($DB->NumRows ($dbResult))
        {
            $wgOut->SetSquidMaxAge (0);
            $wgOut->EnableClientCache (False);

            while ($o = $DB->FetchObject ($dbResult))
            {
                $URI = $o->city_url . 'wiki/' . URLEncode ('User_talk:' . $wgUser->GetTitleKey ());
                $siteName = (IsSet ($o->city_title)) ? $o->city_title : $o->city_dbname;

                $uTalks [] = Array ('wiki' => ($siteName . ' (' . $o->sn_count . ') '), 'link' => $URI);
            }
        }
    }

    return False;
}
?>
