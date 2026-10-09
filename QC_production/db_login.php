<?php
// db_login.php
// The old mysql_* connection. Only the assay pages still use it (their code
// runs on aux/mysql_compat.php); every other page uses lib/db.php.
// Credentials and database: the same settings as lib/db.php (env vars or db_config.php).
require_once __DIR__ . '/aux/mysql_compat.php';   // ext/mysql shim for PHP 8.4 (see file for details)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
date_default_timezone_set(TIMEZONE);

$db = db_settings();

///  Open up the database connection
$connection = mysql_connect($db['host'], $db['user'], $db['password']);
if (!$connection)
   die ("Could not connect to database <br />" . mysql_error());

$db_select = mysql_select_db($db['database']);
if (!$db_select)
    die ("Could not select the database <br />" . mysql_error());
?>
