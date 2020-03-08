<?php
// logout.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//
session_start();
echo ('<br>');
session_unset();
session_destroy();
$_SESSION = array();
?>