<?php
// get_module_underground_vals.php
// D.Norcini, Hopkins, 2024
// Cinyu Zhu, Hopkins, 2025

if (empty($_SESSION['choosen_module_underground'])) {
    $_SESSION['choosen_module_underground'] = 1;
}

$id = (int)$_SESSION['choosen_module_underground'];

// Query to fetch data from the MODULE_UNDERGROUND table
$query = "SELECT * FROM `MODULE_UNDERGROUND2` WHERE `ID` = " . $id;
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
$temp_low_B = $row['Temp_Low_B'] ?? "";
$temp_high_B = $row['Temp_High_B'] ?? "";
$temp_low_C = $row['Temp_Low_C'] ?? "";
$temp_high_C = $row['Temp_High_C'] ?? "";

$feedthru_position = $row['Feedthru_Position'] ?? "";
$ACM = $row['ACM'] ?? "";
$script = $row['Script'] ?? "";
$image_high_dir = $row['Image_High_Dir'] ?? "";
$image_low_dir = $row['Image_Low_Dir'] ?? "";

// Trace fields
foreach ($ccds as $amp) {
    ${'trace_high_saturation_' . $amp} = $row['Trace_High_Saturation_' . $amp] ?? "";
    ${'trace_high_comments_' . $amp} = $row['Trace_High_Comments_' . $amp] ?? "";
    ${'trace_high_reference_' . $amp} = $row['Trace_High_Reference_' . $amp] ?? "";
}
//$trace_file = isset($row['Trace_File']) ? $row['Trace_File'] : "";
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";
$image5_low_crosstalk_AB = $row['Image5_Low_Crosstalk_AB'] ?? "";
$image5_low_crosstalk_AC = $row['Image5_Low_Crosstalk_AC'] ?? "";
$image5_low_crosstalk_AD = $row['Image5_Low_Crosstalk_AD'] ?? "";
$image5_low_crosstalk_BA = $row['Image5_Low_Crosstalk_BA'] ?? "";
$image5_low_crosstalk_BC = $row['Image5_Low_Crosstalk_BC'] ?? "";
$image5_low_crosstalk_BD = $row['Image5_Low_Crosstalk_BD'] ?? "";
$image5_low_crosstalk_CA = $row['Image5_Low_Crosstalk_CA'] ?? "";
$image5_low_crosstalk_CB = $row['Image5_Low_Crosstalk_CB'] ?? "";
$image5_low_crosstalk_CD = $row['Image5_Low_Crosstalk_CD'] ?? "";
$image5_low_crosstalk_DA = $row['Image5_Low_Crosstalk_DA'] ?? "";
$image5_low_crosstalk_DB = $row['Image5_Low_Crosstalk_DB'] ?? "";
$image5_low_crosstalk_DC = $row['Image5_Low_Crosstalk_DC'] ?? "";
$image5_low_crosstalk_comments = $row['Image5_Low_Crosstalk_comments'] ?? "";


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
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 4) : "";
        ${'image' . $img . 'dark_current_' . $amp} = $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] ?? "";
        ${'image' . $img . 'peak1_' . $amp} = $row['Image' . $capitalized_img . 'Peak1_' . $amp] ?? "";
        ${'image' . $img . 'peak2_' . $amp} = $row['Image' . $capitalized_img . 'Peak2_' . $amp] ?? "";
        ${'image' . $img . 'sigma_' . $amp} = $row['Image' . $capitalized_img . 'Sigma_' . $amp] ?? "";
        ${'image' . $img . 'front_' . $amp} = $row['Image' . $capitalized_img . 'Front_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_left_mean_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Left_Mean_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_left_rms_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Left_RMS_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_left_skewness_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Left_Skewness_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_left_integral_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Left_Integral_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_below_mean_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Below_Mean_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_below_rms_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Below_RMS_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_below_skewness_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Below_Skewness_' . $amp] ?? "";
        ${'image' . $img . 'cti_back_below_integral_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Back_Below_Integral_' . $amp] ?? "";
        ${'image' . $img . 'cti_front_left_fraction_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Front_Left_Fraction_' . $amp] ?? "";
        ${'image' . $img . 'cti_front_right_fraction_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Front_Right_Fraction_' . $amp] ?? "";
        ${'image' . $img . 'cti_front_above_fraction_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Front_Above_Fraction_' . $amp] ?? "";
        ${'image' . $img . 'cti_front_below_fraction_' . $amp} = $row['Image' . $capitalized_img . 'CTI_Front_Below_Fraction_' . $amp] ?? "";
        ${'image' . $img . 'ctix_comments_' . $amp} = $row['Image' . $capitalized_img . 'CTIx_Comments_' . $amp] ?? "";
        ${'image' . $img . 'ctiy_comments_' . $amp} = $row['Image' . $capitalized_img . 'CTIy_Comments_' . $amp] ?? "";
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
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 4) : "";
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
