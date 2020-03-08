<?php
  // slow_control_header.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006, 2009, 2010
  // james.nikkel@yale.edu
  //
//    echo('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');

  ////////////////   Check login info

if (!(empty($_POST['login'])))
    include("aux/login.php");

if (!(empty($_POST['logout'])))
    include("aux/logout.php"); 

////////////////   If there is nothing found, autologin as guest
include("aux/guest_login.php");

/////////////////////////////////////////////////////////   Table starts here!
echo('<font size="-1">');
echo('<TABLE border="0" cellpadding="2" width=100%>');
echo('<TR valign="center">');

///  Make a small form where we can choose the refresh time of the page.
///  Use refresh_time = -1 to never refresh
echo('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo('<TH align="left" width="25">');
if (($never_ref) || ($auto_ref))
{
    echo('<input type="image" src="pixmaps/reload.png" alt="Refresh" title="Refresh page">');
    echo('</TH>');
    echo('</FORM>');
}
else
{
    echo('<input type="image" src="pixmaps/reload.png" alt="Refresh" title="Refresh page with new reload time">');
    echo('</TH>');
    
    echo('<TH align="left">');
    echo('Refresh time: <select name="refresh_t">');
    foreach ($select_times as $st_s => $st_v)
    {
	echo('<option ');
	if ($st_v == $_SESSION['refresh_time'])
	    echo('selected="selected"');
	echo(' value="'.$st_v.'" >'.$st_s.'</option> ');
    }
    echo('</select>');
    echo('</TH>');
    echo('</FORM>');
}


if (strpos($_SERVER['PHP_SELF'], "slow_control_plots.php") === false)
{
    if (check_access($_SESSION['privileges'], "basic", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_plots.php">Plots</A>');
	echo('</TH>'); 
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_multiplot.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_multiplot.php">MPlot</A>');
	echo('</TH>'); 
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_scatter.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_scatter.php">Scatter</A>');
	echo('</TH>'); 
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_text.php") === false)
{
    if (check_access($_SESSION['privileges'], "basic", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_text.php">Text</A>');
	echo('</TH>'); 
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_RGA.php") === false)
{
    if ($global_int1["have_RGA"] == 1)
	if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
	{
	    echo('<TH>');
	    echo('<A HREF="slow_control_RGA.php">RGA</A>');
	    echo('</TH>'); 
	}
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_TS.php") === false)
{    
    if ($global_int1["have_TS"] == 1)
	if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
	{
	    echo('<TH>');
	    echo('<A HREF="slow_control_TS.php">Thermo Syphon</A>');
	    echo('</TH>'); 
	}
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_PMT_HV.php") === false)
{    
    if ($global_int1["have_HV_crate"] == 1)
	if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
	{
	    echo('<TH>');
	    echo('<A HREF="slow_control_PMT_HV.php">PMT HV</A>');
	    echo('</TH>'); 
	}
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_sys_log.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_sys_log.php">Sys Log</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_runs.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_runs.php">Runs</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_users.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_users.php">Users</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_alarms.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	if ($global_int1["Master_alarm"] == 1)
	    echo('<font color="red"><BLINK>');
	echo('<A HREF="slow_control_alarms.php">');
	if ($global_int1["Master_alarm"] == 1)
	    echo('<font color="red"><BLINK>');
	echo('Alarms');
	echo('</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_config.php") === false)
{
    if (check_access($_SESSION['privileges'], "config", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_config.php">Config</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_set_vals.php") === false)
{
    if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
    {
	echo('<TH>');
	echo('<A HREF="slow_control_set_vals.php">Control</A>');
	echo('</TH>');
    }
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_logbook.php") === false)
{
    if ($global_int1["have_LB"] == 1)
	if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
	{
	    echo('<TH>');
	    echo('<A HREF="slow_control_logbook.php">LogBook</A>');
	    echo('</TH>');
	}
}

if (strpos($_SERVER['PHP_SELF'], "slow_control_webcam.php") === false)
{
    if ($global_int1["have_Cams"] == 1)
	if (check_access($_SESSION['privileges'], "full", $allowed_host_array))
	{
	    echo('<TH>');
	    echo('<A HREF="slow_control_webcam.php">Cams</A>');
	    echo('</TH>');
	}
}


echo('<TH align="right">');
echo('You are logged in as '.$_SESSION['user_name']);
echo(' from '.$_SERVER['REMOTE_ADDR'].'.');

if (strpos($_SESSION['privileges'], "guest") !== false)
{
    echo('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo('<input type="hidden" name="login" value="1">');
    echo('<TH width="200">');
    echo('Username: <input type="text" name="user_name" size="10">');
    echo('</TH>');
    echo('<TH width="168">');
    echo('Password: <input type="password" name="password" size="10">');
    echo('</TH>');
    echo('<TH width="20">');
    echo('<input type="image" src="pixmaps/login.png" alt="Log in" title="Log in">');
    echo('</TH>');
    echo('</FORM>');
}
else
{
    echo('<TH>');
    echo('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo('<input type="hidden" name="logout" value="1">');
    echo('<input type="image" src="pixmaps/logout.png" alt="Log out" title="Log out">');
    echo('</FORM>');
    echo('</TH>');
}
echo('</TH>');

echo('</TR>');
echo('</TABLE>');
echo('</font>');
//////////////////////////////////////  End  Header


//////////////////////////////////////  Check for access levels
if (!check_access($_SESSION['privileges'], $req_priv, $allowed_host_array))
{  			      
    echo('<br>');
    echo('<br>');
    echo('You do not have clearance to view this page.');
    echo('<br>');
    exit();
}
?>
