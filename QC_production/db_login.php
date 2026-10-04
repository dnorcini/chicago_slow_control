<?php
// db_login.php
// Opens the database connection used by every page.
//
// Credentials are never stored in this file. They come from:
//   1. environment variables DB_HOST / DB_USER / DB_PASSWORD (set by docker-compose), or
//   2. db_config.php next to this file: git-ignored, copy db_config.example.php to create it.
require_once __DIR__ . '/aux/mysql_compat.php';   // ext/mysql shim for PHP 8.4 (see file for details)
date_default_timezone_set('America/New_York');    // set this to your server's time zone (US/Eastern alias not in PHP 8.4's bundled tzdata)

$db_config = is_file(__DIR__ . '/db_config.php') ? require __DIR__ . '/db_config.php' : [];
$db_host     = getenv('DB_HOST')     ?: ($db_config['host'] ?? 'localhost');
$db_username = getenv('DB_USER')     ?: ($db_config['user'] ?? '');
$db_password = getenv('DB_PASSWORD') ?: ($db_config['password'] ?? '');
$db_database = $db_config['database'] ?? 'die_qc';

if ($db_username === '')
    die ("No database credentials: copy db_config.example.php to db_config.php and fill it in.");

///  Open up the database connection 
$connection = mysql_connect($db_host, $db_username, $db_password);
if (!$connection)
   die ("Could not connect to database <br />" . mysql_error());

$db_select = mysql_select_db($db_database);
if (!$db_select)	
    die ("Could not select the database <br />" . mysql_error());
?>
