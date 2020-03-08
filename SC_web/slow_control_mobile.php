<?php
// slow_control_mobile.php
// This page is nicely compatible with mobile devices
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009.
// james.nikkel@yale.edu
//
session_start();
if (!(empty($_POST['refresh_t'])))
{
    $_SESSION['refresh_time'] = $_POST['refresh_t'];
}
if (empty($_SESSION['refresh_time']))
{
    $_SESSION['refresh_time'] = 60*10;
}
echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">');
echo('<HTML>');
echo('<head>');
if ($_SESSION['refresh_time'] != -1)
{
    echo('<META HTTP-EQUIV="Refresh" CONTENT='.$_SESSION['refresh_time'].'>');
}

include("db_login.php");
include("aux/get_globals.php"); 
include("aux/make_title.php");  
include("aux/general_fns.php");

///  Open up the database connection 
$connection = mysql_connect($db_host, $db_username, $db_password);
if (!$connection)
{	
    die ("Could not connect to database <br />" . mysql_error());
}

$db_select = mysql_select_db($db_database);
if (!$db_select)
{	
    die ("Could not select the database <br />" . mysql_error());
}

//  Get login information and set session variables for 
///  user_id, username, and access_level.
require_once("aux/guest_login.php");

///  Get all the sensor information from the database so that we can 
///  define what we want to plot.
include("aux/get_sensor_info.php"); 

///  The idea here is to create a list of unique sensor types from all the available sensors.
///  This allows us to just view one type at a time on the screen.
$unique_sensor_types=make_unique($sensor_types);
$_SESSION['choose_type'] = $unique_sensor_types;

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
include("aux/get_sensor_vals.php");
mysql_close($connection);

////   This next section generate the HTML with the plot names in a table.

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TR>');
foreach ($sensor_names as $sensor_name)
{
    if  (!($sensor_settable[$sensor_name])) // don't show settable sensors in this table
    {
	echo ('<TH>');
	if ($sensor_al_trip[$sensor_name])
	    echo ('<font color="red">');
	echo (' <font size="+2"> ');
	echo ($sensor_descs[$sensor_name]);
	echo (' </font> ');
	echo ('<br>');
	echo (' <font size="+4"> ');
	echo (format_num2($sensor_values[$sensor_name], $sensor_num_format[$sensor_name]));
	echo (' </font> ');
	echo (' <font size="+2"> ');
	echo ('('.$sensor_units[$sensor_name].')');
	echo (' </font> ');
	echo ('</TH>');
	echo ('</TR>');
	echo ('<TR>');
    }
}
echo ('</TR>');
echo ('</TABLE>');

echo(' </body>');
echo ('</HTML>');
?>
