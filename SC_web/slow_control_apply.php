<?php
session_start();
$req_priv = "full";

include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php");

if (!isset($_POST['set_sens_name'], $_POST['new_set_val'])) {
    die("Invalid request.");
}

$sensor = $_POST['set_sens_name'];
$newval = $_POST['new_set_val'];

// Critical flow: username + confirmation code from confirm step (in set_vals.php)
if (isset($_POST['confirm_username'], $_POST['confirm_code'])) {
    $expected_user = isset($_SESSION['user_name']) ? trim($_SESSION['user_name']) : '';
    $expected_code = isset($_SESSION['critical_confirm_code']) ? $_SESSION['critical_confirm_code'] : '';
    $given_user = trim($_POST['confirm_username']);
    $given_code = strtoupper(trim($_POST['confirm_code']));
    if ($given_user !== $expected_user || $given_code !== $expected_code) {
        die("Confirmation failed: username or confirmation code incorrect.");
    }
    unset($_SESSION['critical_confirm_code']);
} else {
    // Direct flow: non-critical sensors only
    if (empty($sensor_critical[$sensor]) || $sensor_critical[$sensor] != 1) {
        // allowed
    } else {
        die("Critical sensor changes must be confirmed. Please use the confirmation flow.");
    }
}

if (!check_access($_SESSION['privileges'], $sensor_ctrl_priv[$sensor], $allowed_host_array)) {
    die("Access denied.");
}

include("aux/new_set_val.php");
header("Location: slow_control_set_vals.php");
exit;
