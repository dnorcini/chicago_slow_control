<?php
// array_defs.php
// D.Norcini, Hopkins, 2024

// Privilege names populated from DB
$privilege_array = [];
$query = "SELECT `name` FROM `user_privileges` ORDER BY `name`";
$result = mysql_query($query);
if (!$result) {
    die("Could not query the database for user privileges <br>" . mysql_error());
}
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $privilege_array[] = $row['name'];
}
$privilege_array = array_values(array_unique($privilege_array));

// Define status options
$status_array = ['Not Tested', 'Tested', 'Failed'];

// Define grades for L1, L2, U1, and U2
$grade_array = ['', 'Failed', 'Operational', 'Engineering', 'Science'];

// Define chamber options
$chamber_array = [" ", "JH1", "JH2"];

// Define ACM numbers
$ACM_numbers = [" ", "101", "102", "106", "108", "109", "110"];

// Define wafer positions
$wafer_positions = [" ", "A", "B", "C", "D"];

// Define feedthrough positions
$feedthru_positions = [" ", "1", "2", "3", "4"];

// Yes/No/Blank array for dropdowns
$yes_no_blank_array = [" ", "Yes", "No"];

// Amplifier labels (assuming it's a list of amplifiers for L1, L2, U1, U2, etc.)
$amp_array = [" ", "L1", "L2", "U1", "U2"];
$amplifiers = ["L1", "L2", "U1", "U2"];

// CCD label
$ccds = ["A", "B", "C", "D"];

// channels
$channels = ['', 'ch0', 'ch1', 'ch2', 'ch3'];


// Define trace fields
$trace_fields = [
    'trace_saturation_',
    'trace_comments_',
    'trace_reference_'
];
$trace_fields_high = [
    'trace_high_saturation_',
    'trace_high_comments_',
    'trace_high_reference_'
];

// Define other image-related fields (for defect maps, tracks, etc.)
$image_fields = [
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
];

$image_numbers = ["1_", "2_", "3_", "4_", "5_", "6_", "7_", "98_", "99_"];
$image_numbers_low = ["1_low_", "2_low_", "3_low_", "31_low_", "32_low_", "4_low_", "5_low_", "6_low_", "7_low_"];
$image_numbers_high = ["1_high_", "2_high_", "3_high_", "31_high_", "32_high_", "4_high_", "5_high_", "6_high_"];

////////////////////////////////////////////////////////////////////////////////////////
// CCD (pre-production) arrays

$ccd_qc_status_array = [
                             " ",
                             "Unchecked",
                             "Science-grade",
                             "Operation-grade",
                             "Toy-grade",
                             "Failed",
                             ];

$ccd_type_array = [
                        " ",
                        "DES D42",
                        "LBNL Skipper",
                        "DAMIC Skipper",
                        "DAMIC Module",
                        "47/6 Skipper+DES"
                        ];

$item_packager_array = [
                             " ",
                             "UW",
                             "LSM",
                             "LBNL",
                             ];

$item_location_array = [
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
                             ];

$ccd_size_array = [
                        " ",
                        "4kx2k",
                        "1kx4k",
                        "1kx6k",
                        "6kx1k",
                        "6kx1.5k",
                        "6kx4k",
                        "6kx6k"
                        ];

$glue_parameter_names = ["Glue_humid", "Glue_temp", "Glue_radon"];
$glue_parameter_title = array_combine($glue_parameter_names, ["Rel. Humidity", "Temperature", "Radon"]);
$glue_parameter_units = array_combine($glue_parameter_names, ["%", "C", "Bq/m^3"]);

$wb_parameter_names = ["Wb_humid", "Wb_temp", "Wb_radon", "Wb_power", "Wb_time"];
$wb_parameter_title = array_combine($wb_parameter_names, ["Rel. Humidity", "Temperature", "Radon", "Bond Power", "Bond Time"]);
$wb_parameter_units = array_combine($wb_parameter_names, ["%", "C", "Bq/m^3", "%", "us"]);

$testing_noise_names = ["Noise_U1", "Noise_L1", "Noise_U2", "Noise_L2"];

$testing_resolution_names = ["Resolution_U1", "Resolution_L1", "Resolution_U2", "Resolution_L2"];

$testing_gain_names = ["Gain_U1", "Gain_L1", "Gain_U2", "Gain_L2"];

$testing_dark_current_names = ["Dark_current_U1", "Dark_current_L1", "Dark_current_U2", "Dark_current_L2"];
