<?php
session_start();
$req_priv = "full";

include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php");

// Validate
if (!isset($_POST['set_sens_name'], $_POST['new_set_val'], $_POST['confirm_phrase'])) {
    die("Invalid request.");
}

if ($_POST['confirm_phrase'] !== 'yes, i do') {
    die("Confirmation phrase incorrect.");
}

$sensor = $_POST['set_sens_name'];
$newval = $_POST['new_set_val'];

// Final access check
if (!check_access($_SESSION['privileges'], $sensor_ctrl_priv[$sensor], $allowed_host_array)) {
    die("Access denied.");
}

// Now and ONLY now do the write
include("aux/new_set_val.php");

// Redirect back to main page
header("Location: slow_control_text.php");
exit;
