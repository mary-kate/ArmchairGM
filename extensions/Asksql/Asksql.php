<?php
/* $Id: Asksql.php 19503 2007-01-20 15:10:35Z hashar $ */

/**
 * If enabled through $wgAllowSysopQueries = true, this class
 * let users with sysop right the possibility to make sql queries
 * against the cur table.
 * Heavy queries could slow down the database specially for the
 * biggest wikis.
 *
 * @addtogroup SpecialPage
 */

if (!defined('MEDIAWIKI'))
	exit;

# Sysop SQL queries
#   The sql user shouldn't have too many rights other the database, restrict
#   it to SELECT only on 'page', 'revision' and 'text' tables for example
#
/** Dangerous if not configured properly. */
$wgAllowSysopQueries = true;
#$wgDBsqluser = 'sqluser';
#$wgDBsqlpassword = 'sqlpass';
$wgSqlLogFile = "{$wgUploadDirectory}/sqllog_mFhyRe6";

if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/Asksql_body.php', 'Asksql', 'SpecialAsksql' );

?>
