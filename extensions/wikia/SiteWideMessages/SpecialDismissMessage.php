<?php
if (! Defined ('MEDIAWIKI'))
{
    Echo '[ <b> Error <b /> ] This is not a valid entry point.' . "\n";
    Exit (1);
}
else
{
    SpecialPage::AddPage (new SpecialPage ('DismissMessage', '', False, 'wfSpecialDismissMessage', False));

    function wfSpecialDismissMessage ()
    {
        global $wgOut, $wgUser, $wgRequest;
        global $wgUseSquid;

        $wgOut->SetPageTitle ("Dismiss message");

        $usrMssg = new UserMessages ();
        $usrMssg->SetUserInfo ($wgUser);

        $mssgStatus = $usrMssg->GetUserMssgStatus( $_REQUEST ['mssgid']);

        if ( $mssgStatus == MSSG_STATUS_SHOW )
        {
            $usrMssg->setUserMssgStatus ($_REQUEST ['mssgid'], MSSG_STATUS_SHOW, MSSG_STATUS_SEEN );

            $wgUser->SetNewTalk (True);

            $wgUser->GetTalkPage ()->InvalidateCache();

            $wgOut->SetSquidMaxAge (0);
            $wgOut->EnableClientCache (False);

            $wgUser->GetTalkPage ()->InvalidateCache ();

            if ($wgUseSquid)
                SquidUpdate::NewSimplePurge ($wgUser->GetTalkPage ())->DoUpdate ();
        }

        $URI = $wgUser->GetTalkPage ()->GetLocalURL ();
        $wgOut->AddHTML ('The message has been dismissed.<br /><a href="' . $URI . '"> Return </a>');
    }
}
?>
