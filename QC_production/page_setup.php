<?php 
// page_setup.php
// 
// James Nikkel, Yale University, 2016
// james.nikkel@yale.edu
//
echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
echo('<HTML>');
echo('<head>');

include("aux/get_globals.php"); //for the frontend websettings
include("aux/make_title.php");  
include("aux/general_fns.php"); // general functions
include("aux/array_defs.php"); // def arrays of variables to be used
include("header.php");   // make headers of the page
include("aux/keep_scroll_position.php");
?>