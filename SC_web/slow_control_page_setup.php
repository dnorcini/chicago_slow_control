<?php 
// slow_control_page_setup.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009, 2011
// james.nikkel@yale.edu
//
echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
echo('<HTML>');
echo('<head>');

if (!(empty($_POST['refresh_t'])))
    $_SESSION['refresh_time'] = $_POST['refresh_t'];

if (empty($_SESSION['refresh_time']))
    $_SESSION['refresh_time'] = 60*3;

if (empty($_SESSION['auto_refresh_time']))
    $_SESSION['auto_refresh_time'] = 30;

if (!(isset($never_ref)))
   $never_ref = 0;

if (!(isset($auto_ref)))
   $auto_ref = 0;

if ($auto_ref)
    echo('<META HTTP-EQUIV="Refresh" CONTENT='.$_SESSION['auto_refresh_time'].'>');
else if (($_SESSION['refresh_time'] != -1) && ($never_ref != 1))
    echo('<META HTTP-EQUIV="Refresh" CONTENT='.$_SESSION['refresh_time'].'>');

include("aux/get_globals.php"); 
include("aux/make_title.php");  
include("aux/general_fns.php");
include("aux/array_defs.php");
include("slow_control_header.php");   

//  Useful to define Master alarm info and play wav file if it goes high 
//  This is found in the globals table
$master_alarm_name = "Master_alarm";
$master_alarm_on = $global_int1[$master_alarm_name];

//if ($master_alarm_on) 
//  echo('<embed src="wave_files/SirenWebAlert_LUX.wav" width="300" height="90" loop="true" autostart="true" />');

if ($master_alarm_on) 
  echo('
<audio controls autoplay loop>
  <source src="wave_files/SirenWebAlert_LUX.wav" type="audio/wav">
    Your browser does not support the audio element.
</audio>
');


///  Decide if we want to show the sensor names with the descriptions on the various pages
if (!(isset($_SESSION['show_sens_name'])))
    $_SESSION['show_sens_name'] = 1;
if (!(empty($_POST['show_sens_name'])))
{
    if ($_SESSION['show_sens_name'])
	$_SESSION['show_sens_name'] = 0;
    else
	$_SESSION['show_sens_name'] = 1;
}
?>
