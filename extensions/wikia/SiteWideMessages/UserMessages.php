<?php

if (! Defined ('MEDIAWIKI'))
{
    Echo '[ <b> Error <b /> ] This is not a valid entry point.' . "\n";
    Exit (1);
}
else
{
	Define ('MAX_ANON_IP_TIME', 86400);
	Define ('MSSG_STATUS_NOEXIST', 255);
	Define ('MSSG_STATUS_NOTACTIVE', 0);
	Define ('MSSG_STATUS_SHOW', 1);
	Define ('MSSG_STATUS_SEEN', 2);
	Define ('MSSG_STATUS_DEL',  3);
	
	Require_once ('WatchedItem.php');
}

class UserMessages
{
	private $mInited;
	private $mAnon;
	private $mUserId;
	private $mUserIp;
	private $mDfltLang;
	private $mUserLang;
	private $mHasNewMssgs;
	private $mNewMssgIds;   

    function UserMessages ()
	{
        global $wgLanguageCode;

        $this->mInited = false;
        $this->mAnon = false;
        $this->mUserId = 0;
        $this->mUserIp = 0;
        $this->mDfltLang = 'en';
        $this->mUserLang = 'en';

        if ($wgLanguageCode)
        {
            $this->mUserLang = $wgLanguageCode;
        }

        $this->mHasNewMssgs = false;
        $this->mNewMssgIds = 0;
    }

    function setUserInfo ($user)
    {
        $this->mInited = true;
        $this->mAnon = $user->isAnon ();
        $this->mUserId = $user->getID ();
        $this->mUserIp = $user->getName ();
        $tmplang = $user->getOption ('language');
        
        if ($tmplang !== '')
        {
            $this->mUserLang = $tmplang;
        }
	}

    /**
     * If user HAS Talk Page he'll see his messages ONLY on HIS Talk Page.
     *
     * @Param Title $tmpTitle
     * @Return String|False
     */
	function getCurMessages ($tmpTitle)
	{
        Global $wgUser;
        Global $wgDissmisNotice;
        
        $name = $wgUser->getName();
        $utp = $wgUser->getTalkPage();
        $retval = '';

        $utp_url = $utp->getLocalUrl();
        $art_url = $tmpTitle->getLocalUrl();

        if ($utp_url == $art_url) 
        {
            $tmpTitle->invalidateCache();
            
            $wgDissmisNotice=True;

            $this->setUserInfo( $wgUser );
            $mssg_ids = $this->getAllUserMssgIds();

            if ( $mssg_ids !== false )
            {
                foreach ($mssg_ids as $mssgid)
                {
                    $tmptext = $this->getUserMssgText( $mssgid );
                    
                    if ($tmptext !== false )
                    {
                        $retval .= $tmptext;
                    }
                }
		wfDebug("UserMessages:getCurMessages; added Notices\n");
            }
        }

        if ( $retval == '')
            return False;
        else
            return $retval;
	}

    function getUserMssgText( $mssgid )
    {

        $fname = 'UserMessages::getUserMssgText';

        $dbr =& wfGetDB( DB_SLAVE );

        $mssg_text = $dbr->selectField (
                    /* FROM   */    MSSG_TEXT_DB, 
	                /* SELECT */	'mssg_text',
	                /* WHERE  */    Array ('mssg_id' => $mssgid, 'mssg_lang' => $this->mUserLang),
                    /* DEBUG  */    $fname,
                    /* OPTION */	'IGNORE');

        // If SELECT failed to retrieve ANY informations then try with DfltLang.
        
        if (! $mssg_text && $this->mUserLang !== $this->mDfltLang)
        {
	        $mssg_text = $dbr->selectField (
	                /* FROM   */    MSSG_TEXT_DB, 
	                /* SELECT */	'mssg_text',
	                /* WHERE  */    Array('mssg_id' => $mssgid, 'mssg_lang' => $this->mDfltLang),
                    /* DEBUG  */    $fname,
                    /* OPTION */	'IGNORE');
        }
        // NOTICE: BLOCK added by KZ & Egon
        if ($mssg_text)
        {
	    wfDebug("UserMessages::getUserMssgText; Added Mssg whith ID=$mssgid\n");
            $UserIp = str_replace (' ', '_', $this->mUserIp);
            return ($mssg_text . " [{{fullurl:Special:DismissMessage|mssgid=$mssgid&returnto={{ns:User_talk}}:$UserIp}} [dismiss this message]] \n\n\n");
        }
        else
        {
            return False;
        }
    }
    /**
     * Returns status for current user of the given message.
     *
     * @Param Int $mssg_id
     * @Return Int
     */
    function getUserMssgStatus ($mssg_id)
    {
        $fname = 'UserMessages::getUserMssgStatus';
        
        $dbr =& wfGetDB( DB_SLAVE );
        
        $mssgRow = $dbr->selectRow (
	            /* FROM   */    MSSG_STATUS_DB,
	            /* SELECT */    Array ('user_mssg_status', 'user_mssg_timestamp' ),
	            /* WHERE  */    Array ( 'user_ip' => $this->mUserIp,
	                                    'user_id' => $this->mUserId,
	                                    'user_mssg_id' => $mssg_id ),
                /* DEBUG  */    $fname,
                /* OPTION */	'IGNORE');

        // There are a couple of different cases: 
        //   - A valid user who does not have an entry
        //   - A valid user who has an entry (NOTACTIVE,SHOW,SEEN,DEL)
        //   - An anon ip user who has no entry
        //   - An anon ip user who has an entry but it's expired
	    //   - An anon ip user who has an entry that's valid (NOTACTIVE,SHOW,SEEN,DEL)

        if (! $mssgRow)
        {
	        return MSSG_STATUS_NOEXIST;
        }
        else
        {
            if ($this->mAnon)
	        {
	            $difftime = Time () - $mssgRow->user_mssg_timestamp;
	            
		        if ($difftime < MAX_ANON_IP_TIME)
	            {
	                // - An anon ip user who has an entry that's valid
	                return $mssgRow->user_mssg_status;
	            }
			    else
			    {
			        // - An anon ip user who has no entry
		            return MSSG_STATUS_NOTACTIVE;
			    }
	        }
	        else
	        {
	            // - A valid user who has an entry (NOTACTIVE,SHOW,SEEN,DEL)
	            return $mssgRow->user_mssg_status;
	        }
        }
    }
	/**
     * .
     *
     * @Param Int $mssg_id
     * @Return Int
     */
	function setUserMssgStatus ($mssg_id, $oldstatus = MSSG_STATUS_NOTACTIVE, $newstatus = MSSG_STATUS_SEEN)
	{
        $fname = 'UserMessages::setUserMssgStatus';

        $dbw =& wfGetDB (DB_MASTER);

        if ( $oldstatus === MSSG_STATUS_NOEXIST)
        {
            $this->addNewUserMssg( $mssg_id, $newstatus );
        }
        else
        {
            $dbw->update (
                    /* FROM   */    MSSG_STATUS_DB,
		            /* SET    */    Array ('user_mssg_status' => $newstatus, 'user_mssg_timestamp' => time () ), 
                    /* WHERE  */    Array ( 'user_id' => $this->mUserId,
                                            'user_ip' => $this->mUserIp, 
                                            'user_mssg_id' => $mssg_id ),
                    /* DEBUG  */    $fname,
                    /* OPTION */	'IGNORE');
        }
	}

	// Returns all message id's for this user that need to be shown.

	function getAllUserMssgIds( $status = MSSG_STATUS_SHOW )
	{
        $fname = 'UserMessages::getAllUserMssgIds';
        
        $dbr =& wfGetDB (DB_SLAVE);
                
        $mssg_rows = $dbr->select (
                /* FROM   */    MSSG_STATUS_DB,
                /* SELECT */    Array ('user_mssg_id', 'user_mssg_timestamp'), 
                /* WHERE  */    Array ( 'user_ip' => $this->mUserIp,
                                        'user_id' => $this->mUserId,
                                        'user_mssg_status' => $status),
	            /* DEBUG  */    $fname);

        if (!$dbr->numrows($mssg_rows))
        {
            $dbr->freeResult ($mssg_rows);
            return false;
        }

        $cntr = 0;
        
        while ($mssgobj = $dbr->fetchObject ($mssg_rows))
        {
            if ($this->mAnon)
            {
                $difftime = time() - $mssgobj->user_mssg_timestamp;
                if ($difftime < MAX_ANON_IP_TIME)
                {
                    $cntr++;
                    $mssg_ids[] = $mssgobj->user_mssg_id;
                }
            }
	        else
	        {
	            $cntr++;
	            $mssg_ids[] = $mssgobj->user_mssg_id;
	        }
		}

        $dbr->freeResult ($mssg_rows);

        if ($cntr === 0)
        {
            return false;
        }
        else
        {
            return $mssg_ids;
        }
    }

    function updateAllUserMssgs ($mssg_ids, $new_status = MSSG_STATUS_SEEN)
    {
        $fname = 'User::updateAllUserMssgs';
        $dbw =& wfGetDB( DB_MASTER );

        foreach ( $mssg_ids as $mssgid )
        {
            $dbw->update (
                    /* FROM   */    MSSG_STATUS_DB,
	                /* SET    */    Array ('user_mssg_status' => $new_status, 'user_mssg_timestamp' => time() ), 
	                /* WHERE  */    Array ( 'user_ip' => $this->mUserUp,
                                            'user_id' => $this->mUserIp,
	                                        'user_mssg_id' => $mssgid ),
                    /* DEBUG  */    $fname,
                    /* OPTION */	'IGNORE');
		}
    }

    function addNewUserMssg ($mssg_id, $status = MSSG_STATUS_NOTACTIVE)
    {
        $fname = 'UserMessages::addNewUserMssg';
        $dbw =& wfGetDB( DB_MASTER );

        $worked = $dbw->insert (
                /* FROM   */    MSSG_STATUS_DB,
                /* VALUES */    Array ( 'user_id' => $this->mUserId,
	                                    'user_ip' => $this->mUserIp,
	                                    'user_mssg_id' => $mssg_id,
	                                    'user_mssg_status' => $status,
	                                    'user_mssg_timestamp' => time() ),
	            /* DEBUG  */    $fname,
	            /* OPTION */	'IGNORE');

		return $worked;
	}

    function deleteNewUserMssg ($mssg_id)
    {
        $fname = 'User::deleteNewUserMssg';
        $dbw =& wfGetDB( DB_MASTER );

        $dbw->delete (
                /* FROM   */    MSSG_STATUS_DB,
				/* VALUES */    Array ( 'user_ip' => $this->mUserIp,
	                                    'user_id' => $this->mUserId,
	                                    'user_mssg_id' => $mssg_id ),
	            /* DEBUG  */    $fname,
	            /* OPTION */	'IGNORE');
	}
}

?>
