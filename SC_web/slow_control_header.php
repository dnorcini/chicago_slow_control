<?php
// slow_control_header.php
// Part of the CLEAN slow control.
// James Nikkel, Yale University, 2006, 2009, 2010
// james.nikkel@yale.edu
//
// Refactor and add underground and fixed tab positions
// Cinyu Zhu, JHU, 2025
// xzhu98@jh.edu

session_start();

////////////////   Check login info
if (!empty($_POST['login']))  include("aux/login.php");
if (!empty($_POST['logout'])) include("aux/logout.php");

////////////////   If there is nothing found, autologin as guest
include("aux/guest_login.php");

/**
 * Render a nav "tab" consistent with header.php:
 * - If current page: show as boxed/bold span
 * - Else: normal link
 * - Only shows if privilege passes + optional condition is true
 */
function nav_link($label, $file, $required_priv, $condition = true, $style_extra = '')
{
    global $allowed_host_array;

    if (!$condition) return;
    if (!check_access($_SESSION['privileges'], $required_priv, $allowed_host_array)) return;

    $is_current = (strpos($_SERVER['PHP_SELF'], $file) !== false);

    echo('<TH' . ($style_extra ? ' style="' . $style_extra . '"' : '') . '>');
    if ($is_current) {
        echo('<span style="font-weight:bold; color:black; border:1px solid black; padding:2px;">' . $label . '</span>');
    } else {
        echo('<A HREF="' . $file . '">' . $label . '</A>');
    }
    echo('</TH>');
}

/////////////////////////////////////////////////////////   Header starts here
echo('<font size="-1">');
echo('<TABLE border="0" cellpadding="2" width="100%">');
echo('<TR valign="center">');

//////////////// Refresh control (keep your logic, but render consistently)
echo('<FORM action="' . $_SERVER['PHP_SELF'] . '" method="post">');
echo('<TH align="left" width="25">');
echo('<input type="image" src="pixmaps/reload.png" alt="Refresh" title="Refresh page">');
echo('</TH>');

if (!($never_ref || $auto_ref)) {
    echo('<TH align="left">');
    echo('Refresh time: <select name="refresh_t">');
    foreach ($select_times as $st_s => $st_v) {
        echo('<option ');
        if ((int)$st_v === (int)$_SESSION['refresh_time']) echo('selected="selected"');
        echo(' value="' . $st_v . '">' . $st_s . '</option>');
    }
    echo('</select>');
    echo('</TH>');
}
echo('</FORM>');

//////////////// Navigation tabs (always show; highlight current instead of hiding)
nav_link("Plots",   "slow_control_plots.php",      "basic");
nav_link("MPlot",   "slow_control_multiplot.php",  "full");
// nav_link("Scatter", "slow_control_scatter.php",    "full");
nav_link("Text",    "slow_control_text.php",       "basic");
// Alarms: keep “red when active” but still highlight current page if on it
$alarm_active = ((int)$global_int1["Master_alarm"] === 1);
$alarm_style  = $alarm_active ? 'color:red; font-weight:bold;' : '';
nav_link("Alarms", "slow_control_alarms.php", "full", true, $alarm_style);

// nav_link("Runs",    "slow_control_runs.php",    "full");

nav_link("Control", "slow_control_set_vals.php", "full");

nav_link("Config",  "slow_control_config.php",   "config");
nav_link("Sys Log", "slow_control_sys_log.php", "full");
nav_link("LogBook", "slow_control_logbook.php", "full", ((int)$global_int1["have_LB"]   === 1));
// nav_link("Cams",    "slow_control_webcam.php",  "full", ((int)$global_int1["have_Cams"] === 1));
nav_link("Users",   "slow_control_users.php",   "full");
nav_link("Guide",   "userguide.php",   "full");

//////////////// Login/Logout (same layout as header.php)
echo('<TH align="right">');
echo('You are logged in as ' . $_SESSION['user_name']);
echo(' from ' . $_SERVER['REMOTE_ADDR'] . '.');

if (strpos($_SESSION['privileges'], "guest") !== false) {
    echo('<FORM action="' . $_SERVER['PHP_SELF'] . '" method="post">');
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
} else {
    echo('<TH>');
    echo('<FORM action="' . $_SERVER['PHP_SELF'] . '" method="post">');
    echo('<input type="hidden" name="logout" value="1">');
    echo('<input type="image" src="pixmaps/logout.png" alt="Log out" title="Log out">');
    echo('</FORM>');
    echo('</TH>');
}

echo('</TH>');
echo('</TR>');
echo('</TABLE>');
echo('</font>');

//////////////////////////////////////  Check for access levels
if (!check_access($_SESSION['privileges'], $req_priv, $allowed_host_array)) {
    echo('<br><br>You do not have clearance to view this page.<br>');
    exit();
}
?>