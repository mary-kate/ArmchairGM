<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (defined('MEDIAWIKI')) {

require_once('includes/Revision.php');

#$wgHooks[] = "wfSharedMessages";
$wgExtensionFunctions[] = 'wfSharedMessages';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'SharedMessages',
	'description' => 'Allows drawing "shared*" messages from the shared DB'
);

function wfSharedMessages() {
	global $wgHooks;
#	echo "Hooking!!\n";
	$wgHooks['MessagesPreLoad'][] = 'wfGetSharedMessage';
	
}

function wfGetSharedMessage($message_title, $newmessage){
	$fname = 'wgGetSharedMessage';
	global $wgOut;
	$wgOut->addHTML("<!-- attempt to fetch shared message $message_title-->\n");
	wfProfileIn( $fname );
 	if(preg_match('/^shared-(.*)/i',$message_title,$message_bits)){
		$shared_title = $message_bits[1];

		if ( !$newmessage ) {
			wfDebug("sharedmessages: attempting to fetch $shared_title\n");
			$revision = SharedRevision::newFromTitle( Title::makeTitle( NS_MEDIAWIKI, $shared_title ) );
			if( $revision ) {
				wfDebug("I have a shared mesage object!\n");
				$newmessage = $revision->getText();
				wfDebug("I have a text like '$newmessage'!\n");
				//if ($this->mUseCache) {
					//$this->mCache[$message_title]=$newmessage;
					/* individual messages may be often
					   recached until proper purge code exists
					*/
					//$this->mMemc->set( $this->mMemcKey . ':' . $message_title, $newmessage, 300 );
				//}
			}
			else{wfDebug("failed revision fetching\n");}
		}

		$newmessage = "$newmessage";
		wfProfileOut( $fname );
 		return true;
 	}
 	else{
		wfProfileOut( $fname );
 		return true;
 	}	
}

class SharedRevision extends Revision{

	/**
	 * Load either the current, or a specified, revision
	 * that's attached to a given title. If not attached
	 * to that title, will return null.
	 *
	 * @param Title $title
	 * @param int $id
	 * @return Revision
	 * @access public
	 * @static
	 */
	static function newFromTitle( &$title, $id = 0 ) {
		wfDebug("attempting to newtitle the object!\n");
		if( $id ) {
			$matchId = IntVal( $id );
		} else {
			$matchId = 'page_latest';
		}
		return SharedRevision::newFromConds(
			array( "rev_id=$matchId",
			       'page_id=rev_page',
			       'page_namespace' => $title->getNamespace(),
			       'page_title'     => $title->getDbkey() ) );
	}

	/**
	 * Given a set of conditions, fetch a revision.
	 *
	 * @param array $conditions
	 * @return Revision
	 * @static
	 * @access private
	 */
	static function newFromConds( $conditions ) {
		wfDebug("attempting to new the object!\n");
		$db =& wfGetDB( DB_SLAVE );
		$row = SharedRevision::loadFromConds( $db, $conditions );
		if( is_null( $row ) ) {
			$dbw =& wfGetDB( DB_MASTER );
			$row = SharedRevision::loadFromConds( $dbw, $conditions );
		}
		return $row;
	}

	/**
	 * Given a set of conditions, fetch a revision from
	 * the given database connection.
	 *
	 * @param Database $db
	 * @param array $conditions
	 * @return Revision
	 * @static
	 * @access private
	 */
	static function loadFromConds( &$db, $conditions ) {
		$res = SharedRevision::fetchFromConds( $db, $conditions );
		if( $res ) {
			wfDebug("attempting to crfetchsuccess the object!\n");
			$row = $res->fetchObject();
			$res->free();
			if( $row ) {
			wfDebug("attempting to create the object!\n");

				return new SharedRevision( $row );
			}
		}
		return null;
	}

	static function fetchFromConds( &$db, $conditions ) {
		global $wgSharedDB;
		if ( !$wgSharedDB ) {
			return false;
		}

		wfDebug("attempting to fetch the object!\n");
		$res = $db->select(
			array( "`$wgSharedDB`.page", "`$wgSharedDB`.revision" ),
			array( 'page_namespace',
			       'page_title',
			       'page_latest',
			       'rev_id',
			       'rev_page',
			       'rev_text_id',
			       'rev_comment',
			       'rev_user_text',
			       'rev_user',
			       'rev_minor_edit',
			       'rev_timestamp',
			       'rev_deleted' ),
			$conditions,
			'SharedRevision::fetchRow',
			array( 'LIMIT' => 1 ) );
		return $db->resultObject( $res );
	}
	/**
	 * Lazy-load the revision's text.
	 * Currently hardcoded to the 'text' table storage engine.
	 *
	 * @return string
	 * @access private
	 */
	function loadText() {
		global $wgSharedDB;
		$fname = 'SharedRevision::loadText';
		wfProfileIn( $fname );
		
		$dbr =& wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow( "`$wgSharedDB`.text",
			array( 'old_text', 'old_flags' ),
			array( 'old_id' => $this->getTextId() ),
			$fname);
		
		if( !$row ) {
			$dbw =& wfGetDB( DB_MASTER );
			$row = $dbw->selectRow( "`$wgSharedDB`.text",
				array( 'old_text', 'old_flags' ),
				array( 'old_id' => $this->getTextId() ),
				$fname);
		}
		
		$text = SharedRevision::getRevisionText( $row );
		wfProfileOut( $fname );
		
		return $text;
	}
}
}
?>
