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
    "aqua",
    "black",
    "blue",
    "gray",
    "green",
    "lime",
    "maroon",
    "navy",
    "orange",
    "purple",
    "red",
    "silver",
    "teal",
    "wheat",
    "white",
    "yellow"
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
$amp_array = array(" ", "L1", "L2", "U1", "U2");
$amplifiers = array("L1", "L2", "U1", "U2");

// CCD label
$ccds = array("A", "B", "C", "D");

// channels
$channels = array('', 'ch0', 'ch1', 'ch2', 'ch3');


// Define trace fields
$trace_fields = array(
    'trace_saturation_',
    'trace_comments_',
    'trace_reference_'
);
$trace_fields_high = array(
    'trace_high_saturation_',
    'trace_high_comments_',
    'trace_high_reference_'
);

// Define other image-related fields (for defect maps, tracks, etc.)
$image_fields = array(
    'tracks_',
    'noise_',
    'defects_',
    'saturation_',
    'sharpness_tracks_',
    'cti_code_',
    'cti_visual_',
    'comments_',
    'reference_',
    'region_defect_',
    'noise_overscan_',
    'res_',
    'res_e_',
    'gain_',
    'dark_current_',
    'column_defects_',
    'pixel_defects_',
    'peak1_',
    'peak2_',
    'sigma_',
    'front_',
    'testxxx_',
    'file_',
    'cti_back_left_mean_',
    'cti_back_left_rms_',
    'cti_back_left_skewness_',
    'cti_back_left_integral_',
    'cti_back_below_mean_',
    'cti_back_below_rms_',
    'cti_back_below_skewness_',
    'cti_back_below_integral_',
    'cti_front_left_fraction_',
    'cti_front_right_fraction_',
    'cti_front_above_fraction_',
    'cti_front_below_fraction_',
    'ctix_comments_',
    'ctiy_comments_',
    'crosstalk_A',
    'crosstalk_B',
    'crosstalk_C',
    'crosstalk_D',
);

$image_numbers = array("1_", "2_", "3_", "4_", "5_", "6_", "7_", "98_", "99_");
$image_numbers_low = array("1_low_", "2_low_", "3_low_", "31_low_", "32_low_", "4_low_", "5_low_", "6_low_", "7_low_");
$image_numbers_high = array("1_high_", "2_high_", "3_high_", "31_high_", "32_high_", "4_high_", "5_high_", "6_high_");

//for comparison page
$typeMap = [
    'sur_' => 'surface',
    'udg_' => 'underground'
];

////////////////////////////////////////////////////////////////////////////////////////
// CCD (pre-production) arrays

$ccd_qc_status_array = array(
                             " ",
                             "Unchecked",
                             "Science-grade",
                             "Operation-grade",
                             "Toy-grade",
                             "Failed",
                             );

$ccd_type_array = array(
                        " ",
                        "DES D42",
                        "LBNL Skipper",
                        "DAMIC Skipper",
                        "DAMIC Module",
                        "47/6 Skipper+DES"
                        );

$item_packager_array = array(
                             " ",
                             "UW",
                             "LSM",
                             "LBNL",
                             );

$item_location_array = array(
                             " ",
                             "UChicago",
                             "UW",
                             "Hopkins",
                             "LPNHE",
                             "LSM",
                             "PNNL",
                             "UZH",
                             "IFCA",
                             "Other",
                             );

$ccd_size_array = array(
                        " ",
                        "4kx2k",
                        "1kx4k",
                        "1kx6k",
                        "6kx1k",
                        "6kx1.5k",
                        "6kx4k",
                        "6kx6k"
                        );

$glue_parameter_names = array("Glue_humid", "Glue_temp", "Glue_radon");
$glue_parameter_title = array_combine($glue_parameter_names, array("Rel. Humidity", "Temperature", "Radon"));
$glue_parameter_units = array_combine($glue_parameter_names, array("%", "C", "Bq/m^3"));

$wb_parameter_names = array("Wb_humid", "Wb_temp", "Wb_radon", "Wb_power", "Wb_time");
$wb_parameter_title = array_combine($wb_parameter_names, array("Rel. Humidity", "Temperature", "Radon", "Bond Power", "Bond Time"));
$wb_parameter_units = array_combine($wb_parameter_names, array("%", "C", "Bq/m^3", "%", "us"));

$testing_noise_names = array("Noise_U1", "Noise_L1", "Noise_U2", "Noise_L2");
$testing_noise_title = array_combine($testing_noise_names, array("Noise U1", "Noise L1", "Noise U2", "Noise L2"));
$testing_noise_units = array_combine($testing_noise_names, array("ADU", "ADU", "ADU", "ADU"));

$testing_resolution_names = array("Resolution_U1", "Resolution_L1", "Resolution_U2", "Resolution_L2");
$testing_resolution_title = array_combine($testing_resolution_names, array("Resolution U1", "Resolution L1", "Resolution U2", "Resolution L2"));
$testing_resolution_units = array_combine($testing_resolution_names, array("ADU", "ADU", "ADU", "ADU"));

$testing_gain_names = array("Gain_U1", "Gain_L1", "Gain_U2", "Gain_L2");
$testing_gain_title = array_combine($testing_gain_names, array("Gain U1", "Gain L1", "Gain U2", "Gain L2"));
$testing_gain_units = array_combine($testing_gain_names, array("ADU", "ADU", "ADU", "ADU"));

$testing_dark_current_names = array("Dark_current_U1", "Dark_current_L1", "Dark_current_U2", "Dark_current_L2");
$testing_dark_current_title = array_combine($testing_dark_current_names, array("Dark_current U1", "Dark_current L1", "Dark_current U2", "Dark_current L2"));
$testing_dark_current_units = array_combine($testing_dark_current_names, array("e-/px/day", "e-/px/day", "e-/px/day", "e-/px/day"));
