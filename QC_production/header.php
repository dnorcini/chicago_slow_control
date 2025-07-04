<?php
// header.php
// James Nikkel, Yale University, 2016
// james.nikkel@yale.edu
//
// added scatter plot function
// D.Norcini, UChicago, 2020
//
// Refactor and add underground and fixed tab positions
// Cinyu Zhu, JHU, 2025
// xzhu98@jh.edu
session_start();

if (!(empty($_POST['login'])))
  include("aux/login.php");

if (!(empty($_POST['logout'])))
  include("aux/logout.php"); 

include("aux/guest_login.php");

function nav_link($label, $file, $required_priv) {
    global $allowed_host_array;
    if (check_access($_SESSION['privileges'], $required_priv, $allowed_host_array)) {
        $is_current = strpos($_SERVER['PHP_SELF'], $file) !== false;
        echo('<TH>');
        if ($is_current) {
            echo('<span style="font-weight:bold; color:black; border:1px solid black; padding:2px;">' . $label . '</span>');
        } else {
            echo('<A HREF="' . $file . '">' . $label . '</A>');
        }
        echo('</TH>');
    }
}

echo('<font size="-1">');
echo('<TABLE border="0" cellpadding="2" width=100%>');
echo('<TR valign="center">');

// Refresh button
echo('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo('<TH align="left" width="25">');
echo('<input type="image" src="pixmaps/reload.png" alt="Refresh" title="Refresh page">');
echo('</TH>');
echo('</FORM>');

// Navigation Links
nav_link("Plots", "plot_entries.php", "basic");
// nav_link("Scatter", "scatter_entries.php", "full"); //What is this page for?
nav_link("DIE", "list_entries.php", "basic");
nav_link("DIE Details", "edit_die.php", "full");
nav_link("MODULE SURFACE", "list_module_surface_entries.php", "basic");
nav_link("MODULE SURFACE Details", "edit_module_surface.php", "full");
nav_link("MODULE UNDERGROUND", "list_module_underground_entries.php", "basic");  // New page
nav_link("MODULE UNDERGROUND Details", "edit_module_underground.php", "basic");  // New page
nav_link("Edit Users", "users.php", "full");

// Login/Logout
echo('<TH align="right">');
echo('You are logged in as '.$_SESSION['user_name']);
echo(' from '.$_SERVER['REMOTE_ADDR'].'.');

if (strpos($_SESSION['privileges'], "guest") !== false) {
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
} else {
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

if (!check_access($_SESSION['privileges'], $req_priv, $allowed_host_array)) {
    echo('<br><br>You do not have clearance to view this page.<br>');
    exit();
}
?>
