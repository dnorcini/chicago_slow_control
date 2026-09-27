<?php
// get_module_surface_vals.php
// D.Norcini, Hopkins, 2024

if (empty($_SESSION['choosen_module_surface'])) {
    $_SESSION['choosen_module_surface'] = 1;
}

$id = (int)$_SESSION['choosen_module_surface'];

// Query to fetch data from the MODULE_SURFACE table
$query = "SELECT * FROM `MODULE_SURFACE` WHERE `ID` = " . $id;
$result = mysql_query($query);
if (!$result) {
    die("Could not query the database <br />" . mysql_error());
}

$row = mysql_fetch_assoc($result);

// Fetching all regular fields
$name = $row['Name'] ?? "";
$status = $row['Status'] ?? "";
$pitch_adaptor_id = $row['Pitch_Adaptor_ID'] ?? "";
$activation = $row['Activation'] ?? "";
$humidity = $row['Humidity'] ?? "";
$radon = $row['Radon'] ?? "";
$die_A = $row['Die_A'] ?? "";
$die_B = $row['Die_B'] ?? "";
$die_C = $row['Die_C'] ?? "";
$die_D = $row['Die_D'] ?? "";
$amp_A = $row['Amp_A'] ?? "";
$amp_B = $row['Amp_B'] ?? "";
$amp_C = $row['Amp_C'] ?? "";
$amp_D = $row['Amp_D'] ?? "";
$channel_A = $row['Channel_A'] ?? "";
$channel_B = $row['Channel_B'] ?? "";
$channel_C = $row['Channel_C'] ?? "";
$channel_D = $row['Channel_D'] ?? "";
$grade_A = $row['Grade_A'] ?? "";
$grade_B = $row['Grade_B'] ?? "";
$grade_C = $row['Grade_C'] ?? "";
$grade_D = $row['Grade_D'] ?? "";
$defects_A = $row['Defects_A'] ?? "";
$defects_B = $row['Defects_B'] ?? "";
$defects_C = $row['Defects_C'] ?? "";
$defects_D = $row['Defects_D'] ?? "";
$notes_A = $row['Notes_A'] ?? "";
$notes_B = $row['Notes_B'] ?? "";
$notes_C = $row['Notes_C'] ?? "";
$notes_D = $row['Notes_D'] ?? "";
$check_A = $row['Check_A'] ?? "";
$check_B = $row['Check_B'] ?? "";
$check_C = $row['Check_C'] ?? "";
$check_D = $row['Check_D'] ?? "";
$reviewer = $row['Reviewer'] ?? "";
$notes = $row['Notes'] ?? "";
$tester = $row['Tester'] ?? "";
$test_date = $row['Test_Date'] ?? "";
$test_time = $row['Test_Time'] ?? "";
$chamber = $row['Chamber'] ?? "";
$temp_low = $row['Temp_Low'] ?? "";
$temp_high = $row['Temp_High'] ?? "";
$feedthru_position = $row['Feedthru_Position'] ?? "";
$ACM = $row['ACM'] ?? "";
$script = $row['Script'] ?? "";
$image_dir = $row['Image_Dir'] ?? "";

// Trace fields
foreach ($ccds as $amp) {
    ${'trace_high_saturation_' . $amp} = $row['Trace_High_Saturation_' . $amp] ?? "";
    ${'trace_high_comments_' . $amp} = $row['Trace_High_Comments_' . $amp] ?? "";
    ${'trace_high_reference_' . $amp} = $row['Trace_High_Reference_' . $amp] ?? "";
}
//$trace_file = isset($row['Trace_File']) ? $row['Trace_File'] : "";
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";

// Image fields
foreach ($image_numbers_low as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "low_" to "Low_" in $img to match database structure
        $capitalized_img = str_replace("low_", "Low_", $img);

        ${'image' . $img . 'tracks_' . $amp} = $row['Image' . $capitalized_img . 'Tracks_' . $amp] ?? "";
        ${'image' . $img . 'defects_' . $amp} = $row['Image' . $capitalized_img . 'Defects_' . $amp] ?? "";
        ${'image' . $img . 'noise_' . $amp} = $row['Image' . $capitalized_img . 'Noise_' . $amp] ?? "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = $row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] ?? "";
        ${'image' . $img . 'cti_code_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Code_' . $amp] ?? "";
        ${'image' . $img . 'cti_visual_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] ?? "";
        ${'image' . $img . 'comments_' . $amp} = $row['Image' . $capitalized_img . 'Comments_' . $amp] ?? "";
        ${'image' . $img . 'reference_' . $amp} = $row['Image' . $capitalized_img . 'Reference_' . $amp] ?? "";
        ${'image' . $img . 'region_defect_' . $amp} = $row['Image' . $capitalized_img . 'Region_Defect_' . $amp] ?? "";
        ${'image' . $img . 'noise_overscan_' . $amp} = $row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] ?? "";
        ${'image' . $img . 'pixel_defects_' . $amp} = $row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] ?? "";
        ${'image' . $img . 'column_defects_' . $amp} = $row['Image' . $capitalized_img . 'Column_Defects_' . $amp] ?? "";
        ${'image' . $img . 'res_' . $amp} = $row['Image' . $capitalized_img . 'Res_' . $amp] ?? "";
        ${'image' . $img . 'gain_' . $amp} = $row['Image' . $capitalized_img . 'Gain_' . $amp] ?? "";
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 3) : "";
        ${'image' . $img . 'dark_current_' . $amp} = $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] ?? "";
        ${'image' . $img . 'peak1_' . $amp} = $row['Image' . $capitalized_img . 'Peak1_' . $amp] ?? "";
        ${'image' . $img . 'peak2_' . $amp} = $row['Image' . $capitalized_img . 'Peak2_' . $amp] ?? "";
        ${'image' . $img . 'sigma_' . $amp} = $row['Image' . $capitalized_img . 'Sigma_' . $amp] ?? "";
        ${'image' . $img . 'front_' . $amp} = $row['Image' . $capitalized_img . 'Front_' . $amp] ?? "";
    }

    // Handling file field separately if needed
    ${'image' . $img . '_file'} = $row['Image' . $capitalized_img . '_File'] ?? "";
}

foreach ($image_numbers_high as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "high_" to "High_" in $img to match database structure
        $capitalized_img = str_replace("high_", "High_", $img);

        ${'image' . $img . 'tracks_' . $amp} = $row['Image' . $capitalized_img . 'Tracks_' . $amp] ?? "";
        ${'image' . $img . 'defects_' . $amp} = $row['Image' . $capitalized_img . 'Defects_' . $amp] ?? "";
        ${'image' . $img . 'noise_' . $amp} = $row['Image' . $capitalized_img . 'Noise_' . $amp] ?? "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = $row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] ?? "";
        ${'image' . $img . 'cti_code_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Code_' . $amp] ?? "";
        ${'image' . $img . 'cti_visual_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] ?? "";
        ${'image' . $img . 'comments_' . $amp} = $row['Image' . $capitalized_img . 'Comments_' . $amp] ?? "";
        ${'image' . $img . 'reference_' . $amp} = $row['Image' . $capitalized_img . 'Reference_' . $amp] ?? "";
        ${'image' . $img . 'region_defect_' . $amp} = $row['Image' . $capitalized_img . 'Region_Defect_' . $amp] ?? "";
        ${'image' . $img . 'noise_overscan_' . $amp} = $row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] ?? "";
        ${'image' . $img . 'pixel_defects_' . $amp} = $row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] ?? "";
        ${'image' . $img . 'column_defects_' . $amp} = $row['Image' . $capitalized_img . 'Column_Defects_' . $amp] ?? "";
        ${'image' . $img . 'res_' . $amp} = $row['Image' . $capitalized_img . 'Res_' . $amp] ?? "";
        ${'image' . $img . 'gain_' . $amp} = $row['Image' . $capitalized_img . 'Gain_' . $amp] ?? "";
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 3) : "";
        ${'image' . $img . 'dark_current_' . $amp} = $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] ?? "";
        ${'image' . $img . 'peak1_' . $amp} = $row['Image' . $capitalized_img . 'Peak1_' . $amp] ?? "";
        ${'image' . $img . 'peak2_' . $amp} = $row['Image' . $capitalized_img . 'Peak2_' . $amp] ?? "";
        ${'image' . $img . 'sigma_' . $amp} = $row['Image' . $capitalized_img . 'Sigma_' . $amp] ?? "";
        ${'image' . $img . 'front_' . $amp} = $row['Image' . $capitalized_img . 'Front_' . $amp] ?? "";
    }

    // Handling file field separately if needed
    ${'image' . $img . '_file'} = $row['Image' . $capitalized_img . '_File'] ?? "";
}

if (isset($row['Last_Update'])) {
    $last_update = (int)$row['Last_Update'];
} else {
    $last_update = 0;
}
