<?php
// array_defs.php
// D.Norcini, Hopkins, 2024

// Dropdown options for times
$select_times = array(
    "10s" => 10,
    "30s" => 30,
    "1m" => 1 * 60,
    "3m" => 3 * 60,
    "10m" => 10 * 60,
    "30m" => 30 * 60,
    "never" => -1
);

// Number of messages options
$num_msgs_array = array(
    "Last 10" => 10,
    "Last 50" => 50,
    "Last 100" => 100,
    "All" => -1,
    "From plot times" => -2,
    "First 10" => -10,
    "First 50" => -50,
    "First 100" => -100
);

// HTML colors
$html_colours = array(
    "aqua", "black", "blue",
    "gray", "green", "lime", "maroon",
    "navy", "orange", "purple", "red",
    "silver", "teal", "wheat", "white", "yellow"
);

// Privilege arrays dynamically populated from DB
$privilege_array = array();
$allowed_host_array = array();
$query = "SELECT `name`, `allowed_host` FROM `user_privileges` ORDER BY `name`";
$result = mysql_query($query);
if (!$result) {
    die("Could not query the database for user privileges <br>" . mysql_error());
}
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $privilege_array[] = $row['name'];
    $allowed_host_array[] = $row['allowed_host'];
}
$allowed_host_array = array($privilege_array, $allowed_host_array);
$privilege_array = make_unique($privilege_array);

// Define status options
$status_array = array('Not Tested', 'Tested', 'Failed');

// Define grades for L1, L2, U1, and U2
$grade_array = array('', 'Failed', 'Operational', 'Engineering', 'Science');

// Define chamber options
$chamber_array = array(" ", "JH1", "JH2");

// Define ACM numbers
$ACM_numbers = array(" ", "101", "102", "106", "108", "109", "110");

// Define wafer positions
$wafer_positions = array(" ", "A", "B", "C", "D");

// Define feedthrough positions
$feedthru_positions = array(" ", "1", "2", "3", "4");

// Yes/No/Blank array for dropdowns
$yes_no_blank_array = array(" ", "Yes", "No");

// Amplifier labels (assuming it's a list of amplifiers for L1, L2, U1, U2, etc.)
$amplifiers = array("L1", "L2", "U1", "U2");

// Define trace fields
$trace_fields = array(
    'trace_saturation_', 'trace_comments_', 'trace_reference_'
);

// Define other image-related fields (for defect maps, tracks, etc.)
$image_fields = array(
    'tracks_', 'noise_', 'defects_', 'saturation_', 'sharpness_tracks_', 'cti_code_', 'cti_visual_',
    'comments_', 'reference_', 'region_defect_', 'noise_overscan_', 'res_', 'gain_', 'dark_current_', 'column_defects_', 'pixel_defects_'
);

$image_numbers = array("1_", "2_", "3_", "4_", "5_", "6_");
?>
