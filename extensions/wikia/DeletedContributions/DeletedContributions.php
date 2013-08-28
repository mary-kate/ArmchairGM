<?php
/**
 * Extension based on SpecialContributions for arhived revisions
 * Modifications made to SpecialContributions.php by Jason Schulz
 * Key code snipets from HideRevision.php also modified for use here
 */
if ( ! defined( 'MEDIAWIKI' ) ) {
	die();
}

global $wgExtensionFunctions, $wgSpecialPages, $wgHooks;

$wgExtensionFunctions[] = 'dcSetup';
$wgSpecialPages['DeletedContributions'] = array( 'SpecialPage', 'DeletedContributions', 'delete',
                /*listed*/ false, /*function*/ false, /*file*/ false );
$wgHooks['SpecialContributionsBeforeMainOutput'][] = 'DeletedContribsHook';

function dcSetup() {
	global $wgMessageCache;
	$wgMessageCache->addMessage('deletedcontributions', 'Deleted user contributions');
}

/**
 * Hook for deletion archive revision view, giving us a chance to
 * insert a tab to see user's archived revision.
 */
function DeletedContribsHook( $id ) {
#FIXME: event hook from specialcontribs only gives Id, not enough for IPs
        if ($id == 0) return;
        $target = User::whoIs( $id );
        InstallDelContribsTab( $target);
        return true;
}

/**
 * If the user is allowed, installs a tab hook on the skin
 * which links to deleted contribs.
 */
function InstallDelContribsTab( $target ) {
        global $wgUser;
        if( $wgUser->isAllowed( 'delete' ) ) {
                global $wgHooks;
                $tab = new DelContribsTabInstaller(
                        'target=' . urlencode( $target ) );
                $wgHooks['SkinTemplateBuildContentActionUrlsAfterSpecialPage'][] =
                        array( $tab, 'insertTab' );
        }
}

class DelContribsTabInstaller {
        function __construct( $linkParam ) {
                $this->mLinkParam = $linkParam;
        }

        function insertTab( $skin, &$content_actions ) {
                $special = Title::makeTitle( NS_SPECIAL, 'DeletedContributions' );
                        $content_actions['viewdeleted'] = array(
                                'class' => false,
                                'text' => wfmsg( 'undelete' ),
                                'href' => $special->getLocalUrl( $this->mLinkParam ) );
                        return true;
        }
}

/** @package MediaWiki */
class DeletedContribsFinder {
        var $username, $offset, $limit, $namespace;
        var $dbr;

        function DeletedContribsFinder( $username ) {
                $this->username = $username;
                $this->namespace = false;
                $this->dbr =& wfGetDB( DB_SLAVE );
        }

        function setNamespace( $ns ) {
                $this->namespace = $ns;
        }

        function setLimit( $limit ) {
                $this->limit = $limit;
        }

        function setOffset( $offset ) {
                $this->offset = $offset;
        }

        function getEditLimit( $dir ) {
                list( $index, $usercond ) = $this->getUserCond();
                $nscond = $this->getNamespaceCond();
                $use_index = $this->dbr->useIndexClause( $index );
                $dbr =& wfGetDB( DB_SLAVE );
                $archive = $dbr->tableName( 'archive' );
                $sql = "SELECT ar_timestamp " .
                        " FROM $archive $use_index" .
                        " WHERE $usercond $nscond" .
                        " ORDER BY ar_timestamp $dir LIMIT 1";

                $res = $this->dbr->query( $sql, __METHOD__ );
                $row = $this->dbr->fetchObject( $res );
                if ( $row ) {
                        return $row->ar_timestamp;
                } else {
                        return false;
                }
        }

        function getEditLimits() {
                return array(
                        $this->getEditLimit( "ASC" ),
                        $this->getEditLimit( "DESC" )
                );
        }

        function getUserCond() {
                $condition = '';

                if ( $condition == '' ) {
                        $condition = ' ar_user_text=' . $this->dbr->addQuotes( $this->username );
                        $index = 'usertext_timestamp';
                } else {
                        $condition = 'ar_user '.$condition ;
                        $index = 'usertext_timestamp';
                }
                return array( $index, $condition );
        }

        function getNamespaceCond() {
                if ( $this->namespace !== false )
                        return ' AND ar_namespace = ' . (int)$this->namespace;
                return '';
        }

        function getPreviousOffsetForPaging() {
                list( $index, $usercond ) = $this->getUserCond();
                $nscond = $this->getNamespaceCond();

                $use_index = $this->dbr->useIndexClause( $index );

                $dbr =& wfGetDB( DB_SLAVE );
                $archive = $dbr->tableName( 'archive' );

                $sql =  "SELECT ar_timestamp FROM $archive $use_index" .
                        "WHERE ar_timestamp > '" . $this->offset . "' AND " .
                        $usercond . $nscond;
                $sql .= " ORDER BY ar_timestamp ASC";
                $sql = $this->dbr->limitResult( $sql, $this->limit, 0 );
                $res = $this->dbr->query( $sql );

                $numRows = $this->dbr->numRows( $res );
                if ( $numRows ) {
                        $this->dbr->dataSeek( $res, $numRows - 1 );
                        $row = $this->dbr->fetchObject( $res );
                        $offset = $row->ar_timestamp;
                } else {
                        $offset = false;
                }
                $this->dbr->freeResult( $res );
                return $offset;
        }

        function getFirstOffsetForPaging() {
                list( $index, $usercond ) = $this->getUserCond();
                $use_index = $this->dbr->useIndexClause( $index );

                $dbr =& wfGetDB( DB_SLAVE );
                $archive = $dbr->tableName( 'archive' );

                $nscond = $this->getNamespaceCond();
                $sql =  "SELECT ar_timestamp FROM $archive $use_index" .
                        "WHERE " . $usercond . $nscond;
                $sql .= " ORDER ar_timestamp ASC";
                $sql = $this->dbr->limitResult( $sql, $this->limit, 0 );
                $res = $this->dbr->query( $sql );

                $numRows = $this->dbr->numRows( $res );
                if ( $numRows ) {
                        $this->dbr->dataSeek( $res, $numRows - 1 );
                        $row = $this->dbr->fetchObject( $res );
                        $offset = $row->ar_timestamp;
                } else {
                        $offset = false;
                }
                $this->dbr->freeResult( $res );
                return $offset;
        }

        /* private */ function makeSql() {
                $userCond = $condition = $index = $offsetQuery = '';

                $dbr =& wfGetDB( DB_SLAVE );
                $archive = $dbr->tableName( 'archive' );

                list( $index, $userCond ) = $this->getUserCond();
                if ( $this->offset )
                        $offsetQuery = "AND ar_timestamp <= '{$this->offset}'";

                $nscond = $this->getNamespaceCond();
                $use_index = $this->dbr->useIndexClause( $index );
                $sql = "SELECT ar_namespace,ar_title,ar_text,ar_comment,ar_user,ar_user_text,ar_timestamp,ar_minor_edit,ar_flags,ar_rev_id,ar_text_id 
                                FROM $archive $use_index
                                WHERE $userCond $nscond $offsetQuery
                                ORDER BY ar_timestamp DESC";
                $sql = $this->dbr->limitResult( $sql, $this->limit, 0 );
                return $sql;
        }

        function find() {
                $contribs = array();
                $res = $this->dbr->query( $this->makeSql(), __METHOD__ );
                while ( $c = $this->dbr->fetchObject( $res ) )
                        $contribs[] = $c;
                $this->dbr->freeResult( $res );
                return $contribs;
        }
};

/**
 * Special page "deleted user contributions".
 * Shows a list of the deleted contributions of a user.
 *
 * @return      none
 * @param       $par    String: (optional) user name of the user for which to show the contributions
 */
function wfSpecialDeletedContributions( $par = null ) {
        global $wgUser, $wgOut, $wgLang, $wgRequest;

        $fname = 'wfSpecialDeletedContributions';

        $target = isset( $par ) ? $par : $wgRequest->getVal( 'target' );
        if ( !strlen( $target ) ) {
                $wgOut->showErrorPage( 'notargettitle', 'notargettext' );
                return;
        }

        $nt = Title::newFromURL( $target );
        if ( !$nt ) {
                $wgOut->showErrorPage( 'notargettitle', 'notargettext' );
                return;
        }

        $options = array();

        list( $options['limit'], $options['offset']) = wfCheckLimits();
        $options['offset'] = $wgRequest->getVal( 'offset' );
        /* Offset must be an integral. */
        if ( !strlen( $options['offset'] ) || !preg_match( '/^[0-9]+$/', $options['offset'] ) )
                $options['offset'] = '';

        $title = SpecialPage::getTitleFor( 'DeletedContributions' );
        $options['target'] = $target;

        $nt =& Title::makeTitle( NS_USER, $nt->getDBkey() );
        $finder = new DeletedContribsFinder( $nt->getText() );
        $finder->setLimit( $options['limit'] );
        $finder->setOffset( $options['offset'] );

        if ( ( $ns = $wgRequest->getVal( 'namespace', null ) ) !== null && $ns !== '' ) {
                $options['namespace'] = intval( $ns );
                $finder->setNamespace( $options['namespace'] );
        } else {
                $options['namespace'] = '';
        }

        if ( $wgRequest->getText( 'go' ) == 'prev' ) {
                $offset = $finder->getPreviousOffsetForPaging();
                if ( $offset !== false ) {
                        $options['offset'] = $offset;
                        $prevurl = $title->getLocalURL( wfArrayToCGI( $options ) );
                        $wgOut->redirect( $prevurl );
                        return;
                }
        }

        if ( $wgRequest->getText( 'go' ) == 'first') {
                $offset = $finder->getFirstOffsetForPaging();
                if ( $offset !== false ) {
                        $options['offset'] = $offset;
                        $prevurl = $title->getLocalURL( wfArrayToCGI( $options ) );
                        $wgOut->redirect( $prevurl );
                        return;
                }
        }

        $wgOut->setSubtitle( wfMsgHtml( 'contribsub', DeletedcontributionsSub( $nt ) ) );

        $id = User::idFromName( $nt->getText() );
        wfRunHooks( 'SpecialDeletedContributionsBeforeMainOutput', $id );

        $wgOut->addHTML( DeletedcontributionsForm( $options) );
        $contribs = $finder->find();

        if ( count( $contribs ) == 0) {
                $wgOut->addWikiText( wfMsg( 'nocontribs' ) );
                return;
        }

        list( $early, $late ) = $finder->getEditLimits();
        $lastts = count( $contribs ) ? $contribs[count( $contribs ) - 1]->ar_timestamp : 0;
        $atstart = ( !count( $contribs ) || $late == $contribs[0]->ar_timestamp );
        $atend = ( !count( $contribs ) || $early == $lastts );

        // These four are defaults
        $newestlink = wfMsgHtml( 'sp-contributions-newest' );
        $oldestlink = wfMsgHtml( 'sp-contributions-oldest' );
        $newerlink  = wfMsgHtml( 'sp-contributions-newer', $options['limit'] );
        $olderlink  = wfMsgHtml( 'sp-contributions-older', $options['limit'] );

        if ( !$atstart ) {
                $stuff = $title->escapeLocalURL( wfArrayToCGI( array( 'offset' => '' ), $options ) );
                $newestlink = "<a href=\"$stuff\">$newestlink</a>";
                $stuff = $title->escapeLocalURL( wfArrayToCGI( array( 'go' => 'prev' ), $options ) );
                $newerlink = "<a href=\"$stuff\">$newerlink</a>";
        }

        if ( !$atend ) {
                $stuff = $title->escapeLocalURL( wfArrayToCGI( array( 'go' => 'first' ), $options ) );
                $oldestlink = "<a href=\"$stuff\">$oldestlink</a>";
                $stuff = $title->escapeLocalURL( wfArrayToCGI( array( 'offset' => $lastts ), $options ) );
                $olderlink = "<a href=\"$stuff\">$olderlink</a>";
        }

        $urls = array();
        foreach ( array( 20, 50, 100, 250, 500 ) as $num ) {
                $stuff = $title->escapeLocalURL( wfArrayToCGI( array( 'limit' => $num ), $options ) );
                $urls[] = "<a href=\"$stuff\">".$wgLang->formatNum( $num )."</a>";
        }
        $bits = implode( $urls, ' | ' );

        $prevnextbits = "($newestlink | $oldestlink) " . wfMsgHtml( 'viewprevnext', $newerlink, $olderlink, $bits );

        $wgOut->addHTML( "<p>{$prevnextbits}</p>\n" );

        $wgOut->addHTML( "<ul>\n" );
        $sk = $wgUser->getSkin();
        foreach ( $contribs as $contrib )
                $wgOut->addHTML( ucListDeletedEdit( $sk, $contrib ) );

        $wgOut->addHTML( "</ul>\n" );
        $wgOut->addHTML( "<p>{$prevnextbits}</p>\n" );
}

/**
 * Generates the subheading with links
 * @param $nt @see Title object for the target
 */
function DeletedcontributionsSub( $nt ) {
        global $wgSysopUserBans, $wgLang, $wgUser;

        $sk = $wgUser->getSkin();
        $id = User::idFromName( $nt->getText() );

        if ( 0 == $id ) {
                $ul = $nt->getText();
        } else {
                $ul = $sk->makeLinkObj( $nt, htmlspecialchars( $nt->getText() ) );
        }
        $talk = $nt->getTalkPage();
        if( $talk ) {
                # Talk page link
                $tools[] = $sk->makeLinkObj( $talk, $wgLang->getNsText( NS_TALK ) );
                if( ( $id != 0 && $wgSysopUserBans ) || ( $id == 0 && User::isIP( $nt->getText() ) ) ) {
                        # Block link
                        if( $wgUser->isAllowed( 'block' ) )
                                $tools[] = $sk->makeKnownLinkObj( SpecialPage::getTitleFor( 'Blockip', $nt->getDBkey() ), wfMsgHtml( 'blocklink' ) );
                        # Block log link
                        $tools[] = $sk->makeKnownLinkObj( SpecialPage::getTitleFor( 'Log' ), htmlspecialchars( LogPage::logName( 'block' ) ), 'type=block&page=' . $nt->getPrefixedUrl() );
                }
                # Other logs link
                $tools[] = $sk->makeKnownLinkObj( SpecialPage::getTitleFor( 'Log' ), wfMsgHtml( 'log' ), 'user=' . $nt->getPartialUrl() );
                $ul .= ' (' . implode( ' | ', $tools ) . ')';
        }
        return $ul;
}

/**
 * Generates the namespace selector form with hidden attributes.
 * @param $options Array: the options to be included.
 */
function DeletedcontributionsForm( $options ) {
        global $wgScript, $wgTitle;

        $options['title'] = $wgTitle->getPrefixedText();

        $f = "<form method='get' action=\"$wgScript\">\n";
        foreach ( $options as $name => $value ) {
                if( $name === 'namespace') continue;
                $f .= "\t" . wfElement( 'input', array(
                        'name' => $name,
                        'type' => 'hidden',
                        'value' => $value ) ) . "\n";
        }

        $f .= '<p>' . wfMsgHtml( 'namespace' ) . ' ' .
        HTMLnamespaceselector( $options['namespace'], '' ) .
        wfElement( 'input', array(
                        'type' => 'submit',
                        'value' => wfMsg( 'allpagessubmit' ) )
        ) .
        "</p></form>\n";

        return $f;
}

/**
 * Generates each row in the deleted contributions list.
 * @todo This would probably look a lot nicer in a table.
 */
function ucListDeletedEdit( $sk, $row ) {
        $fname = 'ucListDeletedEdit';
        wfProfileIn( $fname );

        global $wgLang, $wgUser, $wgRequest;
        static $messages;
        if( !isset( $messages ) ) {
                foreach( explode( ' ', 'deletionlog undelete hist minoreditletter' ) as $msg ) {
                        $messages[$msg] = wfMsgExt( $msg, array( 'escape') );
                }
        }

        $rev= new Revision( array(
                                'id'         => $row->ar_rev_id,
                                'text'       => $row->ar_text,
                                'comment'    => $row->ar_comment,
                                'user'       => $row->ar_user,
                                'user_text'  => $row->ar_user_text,
                                'timestamp'  => $row->ar_timestamp,
                                'minor_edit' => $row->ar_minor_edit,
                                'text_id'    => $row->ar_text_id
                                ) );

        $page = Title::makeTitle( $row->ar_namespace, $row->ar_title );
        $timestamp = $row->ar_timestamp;

        $undelete =& SpecialPage::getTitleFor( 'Undelete' );

        $reviewlink ='(' . $sk->makeKnownLinkObj( $undelete, $messages['deletionlog'], 'target=' . $page->getPrefixedUrl() ) . ')';
        $link = $sk->makeKnownLinkObj( $undelete, htmlspecialchars( $page->getPrefixedText() ), 'target=' . $page->getPrefixedUrl() . '&timestamp=' . $timestamp);

        $comment = $sk->revComment( $rev );
        $d = $wgLang->timeanddate( wfTimestamp( TS_MW, $row->ar_timestamp ), true );

        if( $rev->isDeleted( Revision::DELETED_TEXT ) ) {
                $d = '<span class="history-deleted">' . $d . '</span>';
        }

        if( $row->ar_minor_edit ) {
                $mflag = '<span class="minor">' . $messages['minoreditletter'] . '</span> ';
        } else {
                $mflag = '';
        }

        $ret = "{$d} {$reviewlink} {$mflag} {$link} {$comment}";
        if( $rev->isDeleted( Revision::DELETED_TEXT ) ) {
                $ret .= ' ' . wfMsgHtml( 'deletedrev' );
        }

        $ret = "<li>$ret</li>\n";
        wfProfileOut( $fname );
        return $ret;
}

?>