<?php
require_once __DIR__ . '/aux/mysql_compat.php';   // ext/mysql shim for PHP 8.4 (see file for details)
date_default_timezone_set('America/New_York');    // set this to your server's time zone (US/Eastern alias not in PHP 8.4's bundled tzdata)
$db_host='localhost';      // fill me in!
$db_database='die_qc';
$db_username='control_user';
$db_password='MyLife4AiurUser';             // fill me in as well!

///  Open up the database connection 
$connection = mysql_connect($db_host, $db_username, $db_password);
if (!$connection)
    die ("Could not connect to database <br />" . mysql_error());

$db_select = mysql_select_db($db_database);
if (!$db_select)	
    die ("Could not select the database <br />" . mysql_error());
?>

