<?php
date_default_timezone_set('US/Central');    // set this to your server's time zone
$db_host='localhost';      // fill me in!
$db_database='ccd_qc';
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

