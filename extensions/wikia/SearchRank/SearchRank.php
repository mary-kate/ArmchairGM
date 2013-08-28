<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Tracks search rank for given URLs and keywords
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Tomasz Klim <tomek@wikia.com>
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
create table rank_groups (
    grp_id int not null auto_increment primary key,
    grp_url varchar(255) not null,
    grp_keywords varchar(255) not null,
    grp_tm timestamp default now(),
    grp_active int not null default 1
);
create table rank_results (
    res_id int not null auto_increment primary key,
    res_id_grp int not null,
    res_engine int not null,
    res_position int not null,
    res_tm timestamp default now()
);
 *
LocalSettings.php:
+$wgRankImagePath = '/var/www/wiki/images/';
+$wgRankImageUrl = '/wiki/images/';
+ require_once( "$IP/extensions/SearchRank/SearchRank.php" );
 *
 */

$wgSearchRankMessages = array();
$wgSearchRankMessages['en'] = array(
	'searchrankspecialpage'    => 'Search Rank Tracker',
	'sr_link_edit'             => 'Edit groups',
	'sr_form_keywords'         => 'Keywords',
	'sr_form_active'           => 'Active',
	'sr_form_added'            => 'Added',
	'sr_form_add'              => 'Add new search group:',
	'sr_form_info'             => ' (you can leave this disabled to delay collecting data, for example to synchronize the start date for many groups)',
	'sr_form_update'           => 'Update',
	'sr_form_already'          => ' (editing is disabled, because data collecting already started)',
	'sr_msg_updated'           => 'Groups has been updated.',
);
$wgSearchRankMessages['pl'] = array(
	'searchrankspecialpage'    => 'Sledzenie wynikow wyszukiwania',
	'sr_link_edit'             => 'Edytuj grupy',
	'sr_form_keywords'         => 'Slowa kluczowe',
	'sr_form_active'           => 'Aktywna',
	'sr_form_added'            => 'Dodana',
	'sr_form_add'              => 'Dodaj nowa grupe:',
	'sr_form_info'             => ' (pozostaw ta opcje wylaczona, aby opoznic zbieranie dancyh, np. w celu synchronizacji daty poczatkowej dla wielu grup)',
	'sr_form_update'           => 'Uaktualnij',
	'sr_form_already'          => ' (edycja jest niemozliwa, poniewaz rozpoczelo sie juz zbieranie danych)',
	'sr_msg_updated'           => 'Grupy zostaly uaktualnione.',
);

$wgAvailableRights[] = 'searchrankedit';
$wgGroupPermissions['*']['searchrankedit'] = false;
$wgGroupPermissions['user']['searchrankedit'] = true;


$wgExtensionFunctions[] = 'wfSearchRankSpecialPage';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Search Rank Tracker',
	'description' => 'tracks URL search rank for specified keywords',
	'author' => 'Tomasz Klim'
);


define('RANK_GOOGLE', 0);
define('RANK_YAHOO', 1);
define('RANK_MSN', 2);
define('RANK_ALTAVISTA', 3);
define('RANK_MAXVALUE', 3);

$wgSearchRankNames = array (
				0 => 'Google',
				1 => 'Yahoo',
				2 => 'MSN',
				3 => 'AltaVista',
			   );


function wfSearchRankSpecialPage() {
    global $wgMessageCache, $wgSearchRankMessages;

    foreach( $wgSearchRankMessages as $key => $value ) {
	$wgMessageCache->addMessages( $wgSearchRankMessages[$key], $key );
    }


    class SearchRankSpecialPage extends SpecialPage
    {
	function SearchRankSpecialPage() {
		SpecialPage::SpecialPage('SearchRankSpecialPage');
	}


	function execute( $par ) {
		global $wgRequest, $wgUser;

		$this->setHeaders();

		$action = $wgRequest->getText( 'action' );

		if ( !in_array( 'searchrankedit', $wgUser->getRights() ) ) {
		    $this->presentation();

		} elseif ( $action == 'edit' ) {
		    $this->editGroups();

		} elseif ( $action == 'update' ) {
		    $this->updateGroups();
		    $this->presentation();

		} else {
		    $this->presentation();
		}
	}


	function editGroups() {
		global $wgOut, $wgTitle;

		$url = $wgTitle->escapeLocalUrl() . "?action=update";
		$wgOut->addHTML( "<form name=\"searchrankedit\" action=\"$url\" method=\"post\">\n" );

		$frm_alr   = wfMsg( 'sr_form_already' );
		$frm_key   = wfMsg( 'sr_form_keywords' );
		$frm_act   = wfMsg( 'sr_form_active' );
		$frm_upd   = wfMsg( 'sr_form_update' );
		$frm_info  = wfMsg( 'sr_form_info' );
		$frm_add   = wfMsg( 'sr_form_add' );
		$frm_added = wfMsg( 'sr_form_added' );

		$max = 0;
		$dbr =& wfGetDB( DB_SLAVE );
    		$query = "SELECT grp_id as id, grp_url as url, grp_keywords as kw, grp_tm as gtm,
				 case when grp_active = 1 then 'checked' else '' end as act
		          FROM rank_groups
			  ORDER BY url, kw";
    		$res = $dbr->query( $query ) ;
		while ( $row = $dbr->fetchObject( $res ) ) {
		    $row_id  = $row->id;
		    $row_url = $row->url;
		    $row_key = $row->kw;
		    $row_gtm = $row->gtm;
		    $row_act = $row->act;
		    if ( $row_id > $max) {  $max = $row_id;  }

    		    $query = "SELECT count(*) AS cnt FROM rank_results WHERE res_id_grp = $row_id";
    		    $res2 = $dbr->query( $query ) ;
		    $row2 = $dbr->fetchObject( $res2 );
    		    $dbr->freeResult( $res2 );

		    $inp = ( $row2->cnt ? 'disabled' : '' );
		    $alr = ( $row2->cnt ? $frm_alr : '' );

		    $wgOut->addHTML( "http://<input type=\"text\" style=\"margin-top:3px;\" name=\"url_$row_id\" value=\"$row_url\" size=\"30\" maxlength=\"200\" $inp>$alr<br>\n" );
		    $wgOut->addHTML( "$frm_key: <input type=\"text\" style=\"margin-top:3px;\" name=\"key_$row_id\" value=\"$row_key\" size=\"60\" maxlength=\"200\" $inp><br>\n" );
		    $wgOut->addHTML( "$frm_act: <input type=\"checkbox\" style=\"margin-top:3px;\" name=\"act_$row_id\" $row_act><br>\n" );
		    $wgOut->addHTML( "$frm_added: $row_gtm<br><hr>\n" );
		}
    		$dbr->freeResult( $res );

		$wgOut->addHTML( "$frm_add<br>\n" );
		$wgOut->addHTML( "http://<input type=\"text\" style=\"margin-top:3px;\" name=\"url_0\" size=\"30\" maxlength=\"200\"><br>\n" );
		$wgOut->addHTML( "$frm_key: <input type=\"text\" style=\"margin-top:3px;\" name=\"key_0\" size=\"60\" maxlength=\"200\"><br>\n" );
		$wgOut->addHTML( "$frm_act: <input type=\"checkbox\" style=\"margin-top:3px;\" name=\"act_0\">$frm_info<br>\n" );

		$wgOut->addHTML( "<input type=\"hidden\" name=\"max\" value=\"$max\">\n" );
		$wgOut->addHTML( "<input type=\"submit\" style=\"margin-top:3px;\" name=\"submit\" value=\"$frm_upd\">" );
		$wgOut->addHTML( "</form>" );
	}


	function updateGroups() {
		global $wgRequest, $wgOut;

		$dbw =& wfGetDB( DB_MASTER );
		$max = $wgRequest->getText( 'max' );

		for ( $index = 1; $index <= $max; $index++ ) {

		    $url = $wgRequest->getText( 'url_' . $index );
		    $key = $wgRequest->getText( 'key_' . $index );
		    $act = $wgRequest->getText( 'act_' . $index );

		    // check, if we can edit anything in the group
    		    $query = "SELECT count(*) AS cnt FROM rank_results WHERE res_id_grp = $index";
    		    $res = $dbw->query( $query ) ;
		    $row = $dbw->fetchObject( $res );
    		    $dbw->freeResult( $res );
		    $isLocked = ( $row->cnt ? true : false );

		    // group is locked -> we can only change grp_active setting
		    if ( $isLocked ) {
			$newAct = ( $act == "on" ? "1" : "0" );
			$dbw->query( "UPDATE rank_groups SET grp_active = $newAct WHERE grp_id = $index", "wfSearchRankSpecialPage::updateGroups" );

		    } else {  // group not locked -> we can change everything
			$newAct = ( $act == "on" ? "1" : "0" );
			$newKey = ( $key == "" ? "" : ", grp_keywords = '$key'" );
			$newUrl = ( $url == "" ? "" : ", grp_url = '$url'" );
			$dbw->query( "UPDATE rank_groups SET grp_active = $newAct $newKey $newUrl WHERE grp_id = $index", "wfSearchRankSpecialPage::updateGroups" );
		    }
		}

		// add new group
		$url =   $wgRequest->getText( 'url_0' );
		$key =   $wgRequest->getText( 'key_0' );
		$act = ( $wgRequest->getText( 'act_0' ) == "on" ? "1" : "0" );

		if ( $url && $key ) {
		    $dbw->query( "INSERT INTO rank_groups (grp_url, grp_keywords, grp_active) VALUES ('$url', '$key', $act)", "wfSearchRankSpecialPage::updateGroups" );
		}

		$wgOut->addWikiText( wfMsg( 'sr_msg_updated' ) );
	}


	function presentation() {
		global $wgOut, $wgUser, $wgRankImagePath, $wgRankImageUrl, $wgSearchRankNames;

		if ( in_array( 'searchrankedit', $wgUser->getRights() ) ) {
		    $target = Title::newFromText( 'SearchRankSpecialPage', NS_SPECIAL );
    		    $url = $target->getFullURL() . "?action=edit";

		    $wgOut->addHTML( '<a href="' . $url . '">' . wfMsg( 'sr_link_edit' ) . '</a><br><br>' );
		}

		$dbr =& wfGetDB( DB_SLAVE );
    		$query = "SELECT grp_id as id, grp_url as url, grp_keywords as kw, grp_tm as gtm
		          FROM rank_groups
			  WHERE grp_active = 1
			  ORDER BY url, kw";
    		$res = $dbr->query( $query ) ;
		while ( $row = $dbr->fetchObject( $res ) ) {
		    $row_id  = $row->id;
		    $row_url = $row->url;
		    $row_key = $row->kw;
		    $row_gtm = $row->gtm;

		    for ( $engine = 0; $engine <= RANK_MAXVALUE; $engine++ ) {

			// here we adjust graph parameters
			$engine_name = $wgSearchRankNames[$engine];

			$dat = array();
			$cfg = array();
			$cfg['ImgTitle'] = "$engine_name - $row_url - $row_key";
			$cfg['tmpDir'] = $wgRankImagePath;
			$cfg['wwwDir'] = $wgRankImageUrl;

			//$cfg['ImgTitleColor'] = "";			// Sets the color of the title text (default 0000FF)
			$cfg['ImgWidth'] = "850";			// Sets the width of the image (default 500)
			$cfg['ImgHeight'] = "320";			// Sets the height of the image (default 150)
			//$cfg['ImgBackgroundColor'] = "";		// Sets the image background (default FFFFFF)
			//$cfg['ImgTextColor'] = "";			// Sets the image text color (default 000000)
			//$cfg['ImgPaddingTop'] = "";			// Sets the top padding for the graphbox (default 20)
			//$cfg['ImgPaddingLeft'] = "";			// Sets the left padding for the graphbox (default 40)
			//$cfg['ImgPaddingBottom'] = "";		// Sets the bottom padding for the graphbox (default 20)
			//$cfg['ImgPaddingRight'] = "";			// Sets the right padding for the graphbox (default 40)
			//$cfg['BoxBackgroundColor'] = "";		// Sets the background color of the graph box (default FFFFFF)
			//$cfg['BoxBorderColor'] = "";			// Sets the border color of the graph box (default 000000)
			//$cfg['BoxGridColor'] = "";			// Sets the grid color (default CCCCCC)
			//$cfg['BoxTextColor'] = "";			// Sets the color of the text over the indicating box in the graph (default 0000FF)
			//$cfg['ColumnColor'] = "";			// Sets the color of the value indication (default 00CC00)
			//$cfg['CompareColumnColor'] = "";		// Sets the color of the compare value indication (default FF0066)
			//$cfg['LegendaPadding'] = "";			// Sets the padding of the left legenda text (default 30)
			//$cfg['UnitOfMeasure'] = "";			// Unit of measure (default "")
			//$cfg['ColumnTextTrigger'] = "";		// Toggles Column text on/off values(1/0) (default 1)
			//$cfg['BlockTextTrigger'] = "";		// Toggles Block text on/off values(1/0) (default 1)
			//$cfg['AvarageTrigger'] = "";			// Toggles avarage line on/off values(1/0) (default 1)
			//$cfg['GridTrigger'] = "";			// Toggles grid on/off values(1/0) (default 1)
			//$cfg['LegendaTextTrigger'] = "";		// Toggles legenda text on/off values(1/0) (default 1)
			//$cfg['TitleTextTrigger'] = "";		// Toggles Title Text on/off values(1/0) (default 1)

			//$cfg['LegendaBlockTrigger'] = "";		// Toggles Legenda block for compare graph (default 0)
			//$cfg['LegendaBlockText'] = "";		// Text for legenda block first DataArray (default "")
			//$cfg['CompareLegendaBlockText'] = "";		// Text for legenda block second DataArray (default "")

			$cfg['BlockTextVert'] = "1";			// if set to 1 (true) shows vertical block text, padding correctly the image
			//$cfg['MinColWidth'] = "";			// If set to a number larger then 0. It will automatically calculate the image width
			//$cfg['BenchMark'] = "";			// Sets a benchmark for the graph and the outcome will be displayed in the titletext color in the upper right corner of the image (default 0)

			$ok = 0;
    			$query = "SELECT res_position as pos, res_tm as rtm
				  FROM rank_results
				  WHERE res_id_grp = $row_id and res_engine = $engine
				  ORDER BY rtm";
    			$res2 = $dbr->query( $query ) ;
			while ( $row2 = $dbr->fetchObject( $res2 ) ) {
			    $row_pos = $row2->pos;
			    $row_rtm = $row2->rtm;
			    $dat[$row_rtm] = $row_pos;
			    $ok = 1;
			}
    			$dbr->freeResult( $res2 );

			if ( $ok ) {
			    $graph = new WikiGraph();
			    $graph->LineGraph( $dat, $cfg );
			    $wgOut->addHTML( $graph->getBuffer() . "<br><br>\n" );
			}
			unset($graph);
			unset($cfg);
			unset($dat);
		    }
	    	    $wgOut->addHTML( "<hr>\n" );
		}
    		$dbr->freeResult( $res );
	}


    } // class

    SpecialPage::addPage( new SearchRankSpecialPage );
}


?>
