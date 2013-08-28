<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Wiki engine separate database extension
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Lukasz Lach <mail@php5.pl> - safe query method
 * @author CorfiX <corfix@wikia.com> - additional mysql functions (+28% speed)
 * @author Tomasz Klim <tomek@wikia.com> - base class, integration with external code
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['other'][] = array(
	'name' => 'WikiAltDbm',
	'description' => 'separate database extension',
	'author' => 'Tomasz Klim'
);


class WikiAltDbm
{
    var $dbhost;
    var $dbuser;
    var $dbpass;
    var $dbname;
    var $dbconn;
    var $connected = false;
    var $logfile;
    var $arguments;
    var $argument_index;

    function __construct( $host, $user, $pass, $db, $log = '/tmp/sql_error.log' ) {
	$this->dbhost  = $host;
	$this->dbuser  = $user;
	$this->dbpass  = $pass;
	$this->dbname  = $db;
	$this->logfile = $log;
    }

    function __destruct() {
	if ( $this->connected ) {
	    @mysqli_close( $this->dbconn );
	}
    }

    function connect() {
	$this->dbconn = @mysqli_connect( $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname );
	$this->connected = ( $this->dbconn ? true : false );
	return $this->connected;
    }

    function error() {
	return mysqli_connect_error();
    }

    function last_id() {
	return mysqli_insert_id( $this->dbconn );
    }

    function affected() {
	return mysqli_affected_rows( $this->dbconn );
    }

    function query( $sql ) {
	if ( $res = @mysqli_query( $this->dbconn, $sql ) ) {
	    return $res;
	} else {
	    $error = date( "Y-m-d H:i:s" ) . $_SERVER['PHP_SELF'] . ": $sql\n" . mysqli_error( $this->dbconn ) . "\n\n";
	    $elems = array (
		    'line' => '__LINE__',
		    'file' => '__FILE__',
		    'func' => '__FUNCTION__',
		    'class' => '__CLASS__',
		    'method' => '__METHOD__'
	    );
	    foreach ( $elems as $key => $value ) {
		if ( defined( $value ) ) {
		    $error .= ", $key:" . constant( $value );
		}
	    }
	    @error_log( $error, 3, $this->logfile );
	    return false;
	}
    }

    /* private */ function parse_argument( $matches ) {
	$match = $matches[0];
	$argument = @$this->arguments[$this->argument_index++];
	switch ( $match ) {
	    case '%d': return (int)$argument;
	    case '%s': return "'" . mysqli_real_escape_string( $this->dbconn, $argument ) . "'";
	    case '%b': return (int)( (bool)$argument );
	}
    }

    // http://hacking.pl/5845
    function safequery( $sql ) {
	$arguments = func_get_args();
	array_shift( $arguments );
	$this->arguments = $arguments;
	$this->argument_index = 0;
	$sql = preg_replace_callback( '/(%[dsb])/', array( $this, 'parse_argument' ), $sql );
	return $this->query( $sql );
    }

    function num_rows( $res ) {
	return ( $res ? mysqli_num_rows( $res ) : 0 );
    }

    function num_fields( $res ) {
	return ( $res ? mysqli_num_fields( $res ) : 0 );
    }

    function fetch_object( $res ) {
        return ( $res ? mysqli_fetch_object( $res ) : false );
    }
    function fetchObject( $res ) {  // MediaWiki compatibility
	return $this->fetch_object( $res );
    }

    function fetch_array( $res ) {
        return ( $res ? mysqli_fetch_array( $res ) : false );
    }

    function fetch_assoc( $res ) {
        return ( $res ? mysqli_fetch_assoc( $res ) : false );
    }

    function fetch_single( $res ) {
	if ( !$this->num_rows( $res ) ) {
	    return false;
	}
	$row = $this->fetch_array( $res );
	return ( $row[0] );
    }

    function free( $res ) {
	@mysqli_free_result( $res );
    }
    function freeResult( $res ) {  // MediaWiki compatibility
	$this->free( $res );
    }
}


/*
$sql = $dbw->safequery( 'INSERT INTO users (uid, name, username, password, newsletter) VALUES (%d, %s, %s, %s, %b)',
			$_POST['uid'], $_POST['name'], $_POST['username'], md5($_POST['password']), $_POST['newsletter'] );

INSERT INTO users (uid, name, username, password, newsletter)
VALUES (1, "Lukasz \"anAKiN\" Lach", "anakin", "97296eca657a093aa379778c237e292d", 1)
*/


?>
