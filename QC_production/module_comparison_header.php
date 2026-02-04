<?php

//to avoil session key conflicts, leading to incorrect file paths
if (isset($_SESSION['choosen_module_underground'])) {
    $key = 'file_url_' . $_SESSION['choosen_module_underground'];
    unset($_SESSION[$key]);
    $key = 'log_url_' . $_SESSION['choosen_module_underground'];
    unset($_SESSION[$key]);
}


$req_priv = "full";
include("db_login.php");
include("page_setup.php");


$table = "MODULE_UNDERGROUND2"; //only used in get last id, update sql (not for this part)
// should be fine putting only one table here?

// Clear session variables when module_underground_id changes to prevent data from other module_undergrounds interfering
if (isset($_POST['choosen']) && $_POST['choosen'] !== $_SESSION['choosen_module_underground']) {
    unset($_SESSION['log_url'], $_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Set the chosen module_underground from session "req_id"
if (!empty($_SESSION['req_id'])) {
    $_SESSION['choosen_module_underground'] = $_SESSION['req_id'];
    unset($_SESSION['req_id']);
}
// If the user manually selects a module from a dropdown or button, it updates the current session to reflect that selection.
if (isset($_POST['choosen'])) {
    $_SESSION['choosen_module_underground'] = $_POST['choosen'];
}
// Set default as first module if none is chosen
if (empty($_SESSION['choosen_module_underground'])) {
    $_SESSION['choosen_module_underground'] = 1;
}


// Navigation logic (First, Last, Previous, Next)
if (isset($_POST['first'])) {
    $_SESSION['choosen_module_underground'] = 1;
}
if (isset($_POST['last'])) {
    include("aux/get_last_die_id.php");
    $_SESSION['choosen_module_underground'] = $last_id;
}
if (isset($_POST['prev'])) {
    $_SESSION['choosen_module_underground'] = max(1, $_SESSION['choosen_module_underground'] - 1);
}
if (isset($_POST['next'])) {
    $_SESSION['choosen_module_underground'] = $_SESSION['choosen_module_underground'] + 1;
}

// Ensure we never go below 1 or above the last MODULE_UNDERGROUND ID
include("aux/get_last_die_id.php");
if ($_SESSION['choosen_module_underground'] < 1) {
    $_SESSION['choosen_module_underground'] = 1;
}
if ($_SESSION['choosen_module_underground'] > $last_id) {
    $_SESSION['choosen_module_underground'] = $last_id;
}

// Assign the chosen choosen_module_underground to the $id variable
$id = (int)$_SESSION['choosen_module_underground'];

// Fetch module_underground details
include("aux/get_module_comparison_vals.php");


if (isset($_POST['id'])) {
    $module_underground_id = (int) $_POST['id'];
    $_SESSION['choosen_module_underground'] = $module_underground_id;
} else {
    $module_underground_id = $_SESSION['choosen_module_underground'];
}

// Fetch module_underground details again after updates
include("aux/get_module_comparison_vals.php");
mysql_close($connection);
