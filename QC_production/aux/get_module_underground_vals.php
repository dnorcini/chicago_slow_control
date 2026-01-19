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
$name = isset($row['Name']) ? $row['Name'] : "";
$status = isset($row['Status']) ? $row['Status'] : "";
$pitch_adaptor_id = isset($row['Pitch_Adaptor_ID']) ? $row['Pitch_Adaptor_ID'] : "";
$activation = isset($row['Activation']) ? $row['Activation'] : "";
$humidity = isset($row['Humidity']) ? $row['Humidity'] : "";
$radon = isset($row['Radon']) ? $row['Radon'] : "";
$die_A = isset($row['Die_A']) ? $row['Die_A'] : "";
$die_B = isset($row['Die_B']) ? $row['Die_B'] : "";
$die_C = isset($row['Die_C']) ? $row['Die_C'] : "";
$die_D = isset($row['Die_D']) ? $row['Die_D'] : "";
$amp_A = isset($row['Amp_A']) ? $row['Amp_A'] : "";
$amp_B = isset($row['Amp_B']) ? $row['Amp_B'] : "";
$amp_C = isset($row['Amp_C']) ? $row['Amp_C'] : "";
$amp_D = isset($row['Amp_D']) ? $row['Amp_D'] : "";
$channel_A = isset($row['Channel_A']) ? $row['Channel_A'] : "";
$channel_B = isset($row['Channel_B']) ? $row['Channel_B'] : "";
$channel_C = isset($row['Channel_C']) ? $row['Channel_C'] : "";
$channel_D = isset($row['Channel_D']) ? $row['Channel_D'] : "";
$grade_A = isset($row['Grade_A']) ? $row['Grade_A'] : "";
$grade_B = isset($row['Grade_B']) ? $row['Grade_B'] : "";
$grade_C = isset($row['Grade_C']) ? $row['Grade_C'] : "";
$grade_D = isset($row['Grade_D']) ? $row['Grade_D'] : "";
$defects_A = isset($row['Defects_A']) ? $row['Defects_A'] : "";
$defects_B = isset($row['Defects_B']) ? $row['Defects_B'] : "";
$defects_C = isset($row['Defects_C']) ? $row['Defects_C'] : "";
$defects_D = isset($row['Defects_D']) ? $row['Defects_D'] : "";
$notes_A = isset($row['Notes_A']) ? $row['Notes_A'] : "";
$notes_B = isset($row['Notes_B']) ? $row['Notes_B'] : "";
$notes_C = isset($row['Notes_C']) ? $row['Notes_C'] : "";
$notes_D = isset($row['Notes_D']) ? $row['Notes_D'] : "";
$check_A = isset($row['Check_A']) ? $row['Check_A'] : "";
$check_B = isset($row['Check_B']) ? $row['Check_B'] : "";
$check_C = isset($row['Check_C']) ? $row['Check_C'] : "";
$check_D = isset($row['Check_D']) ? $row['Check_D'] : "";
$reviewer = isset($row['Reviewer']) ? $row['Reviewer'] : "";
$notes = isset($row['Notes']) ? $row['Notes'] : "";
$tester = isset($row['Tester']) ? $row['Tester'] : "";
$test_date = isset($row['Test_Date']) ? $row['Test_Date'] : "";
$test_time = isset($row['Test_Time']) ? $row['Test_Time'] : "";
$chamber = isset($row['Chamber']) ? $row['Chamber'] : "";
$temp_low_B = isset($row['Temp_Low_B']) ? $row['Temp_Low_B'] : "";
$temp_high_B = isset($row['Temp_High_B']) ? $row['Temp_High_B'] : "";
$temp_low_C = isset($row['Temp_Low_C']) ? $row['Temp_Low_C'] : "";
$temp_high_C = isset($row['Temp_High_C']) ? $row['Temp_High_C'] : "";

$feedthru_position = isset($row['Feedthru_Position']) ? $row['Feedthru_Position'] : "";
$ACM = isset($row['ACM']) ? $row['ACM'] : "";
$script = isset($row['Script']) ? $row['Script'] : "";
$image_high_dir = isset($row['Image_High_Dir']) ? $row['Image_High_Dir'] : "";
$image_low_dir = isset($row['Image_Low_Dir']) ? $row['Image_Low_Dir'] : "";

// Trace fields
foreach ($ccds as $amp) {
    ${'trace_high_saturation_' . $amp} = isset($row['Trace_High_Saturation_' . $amp]) ? $row['Trace_High_Saturation_' . $amp] : "";
    ${'trace_high_comments_' . $amp} = isset($row['Trace_High_Comments_' . $amp]) ? $row['Trace_High_Comments_' . $amp] : "";
    ${'trace_high_reference_' . $amp} = isset($row['Trace_High_Reference_' . $amp]) ? $row['Trace_High_Reference_' . $amp] : "";
}
//$trace_file = isset($row['Trace_File']) ? $row['Trace_File'] : "";
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";
$image5_low_crosstalk_AB = isset($row['Image5_Low_Crosstalk_AB']) ? $row['Image5_Low_Crosstalk_AB'] : "";
$image5_low_crosstalk_AC = isset($row['Image5_Low_Crosstalk_AC']) ? $row['Image5_Low_Crosstalk_AC'] : "";
$image5_low_crosstalk_AD = isset($row['Image5_Low_Crosstalk_AD']) ? $row['Image5_Low_Crosstalk_AD'] : "";
$image5_low_crosstalk_BA = isset($row['Image5_Low_Crosstalk_BA']) ? $row['Image5_Low_Crosstalk_BA'] : "";
$image5_low_crosstalk_BC = isset($row['Image5_Low_Crosstalk_BC']) ? $row['Image5_Low_Crosstalk_BC'] : "";
$image5_low_crosstalk_BD = isset($row['Image5_Low_Crosstalk_BD']) ? $row['Image5_Low_Crosstalk_BD'] : "";
$image5_low_crosstalk_CA = isset($row['Image5_Low_Crosstalk_CA']) ? $row['Image5_Low_Crosstalk_CA'] : "";
$image5_low_crosstalk_CB = isset($row['Image5_Low_Crosstalk_CB']) ? $row['Image5_Low_Crosstalk_CB'] : "";
$image5_low_crosstalk_CD = isset($row['Image5_Low_Crosstalk_CD']) ? $row['Image5_Low_Crosstalk_CD'] : "";
$image5_low_crosstalk_DA = isset($row['Image5_Low_Crosstalk_DA']) ? $row['Image5_Low_Crosstalk_DA'] : "";
$image5_low_crosstalk_DB = isset($row['Image5_Low_Crosstalk_DB']) ? $row['Image5_Low_Crosstalk_DB'] : "";
$image5_low_crosstalk_DC = isset($row['Image5_Low_Crosstalk_DC']) ? $row['Image5_Low_Crosstalk_DC'] : "";
$image5_low_crosstalk_comments = isset($row['Image5_Low_Crosstalk_comments']) ? $row['Image5_Low_Crosstalk_comments'] : "";


// Image fields
foreach ($image_numbers_low as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "low_" to "Low_" in $img to match database structure
        $capitalized_img = str_replace("low_", "Low_", $img);
        ${'image' . $img . 'tracks_' . $amp} = isset($row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'image' . $img . 'defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'image' . $img . 'noise_' . $amp} = isset($row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = isset($row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'image' . $img . 'cti_code_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'image' . $img . 'cti_visual_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'image' . $img . 'comments_' . $amp} = isset($row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'image' . $img . 'reference_' . $amp} = isset($row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'image' . $img . 'region_defect_' . $amp} = isset($row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'image' . $img . 'noise_overscan_' . $amp} = isset($row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'image' . $img . 'pixel_defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'image' . $img . 'column_defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'image' . $img . 'res_' . $amp} = isset($row['Image' . $capitalized_img . 'Res_' . $amp]) ? $row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'image' . $img . 'gain_' . $amp} = isset($row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 4) : "";
        ${'image' . $img . 'dark_current_' . $amp} = isset($row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'image' . $img . 'peak1_' . $amp} = isset($row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'image' . $img . 'peak2_' . $amp} = isset($row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'image' . $img . 'sigma_' . $amp} = isset($row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'image' . $img . 'front_' . $amp} = isset($row['Image' . $capitalized_img . 'Front_' . $amp]) ? $row['Image' . $capitalized_img . 'Front_' . $amp] : "";
        ${'image' . $img . 'cti_back_left_mean_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Left_Mean_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Left_Mean_' . $amp] : "";
        ${'image' . $img . 'cti_back_left_rms_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Left_RMS_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Left_RMS_' . $amp] : "";
        ${'image' . $img . 'cti_back_left_skewness_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Left_Skewness_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Left_Skewness_' . $amp] : "";
        ${'image' . $img . 'cti_back_left_integral_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Left_Integral_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Left_Integral_' . $amp] : "";
        ${'image' . $img . 'cti_back_below_mean_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Below_Mean_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Below_Mean_' . $amp] : "";
        ${'image' . $img . 'cti_back_below_rms_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Below_RMS_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Below_RMS_' . $amp] : "";
        ${'image' . $img . 'cti_back_below_skewness_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Below_Skewness_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Below_Skewness_' . $amp] : "";
        ${'image' . $img . 'cti_back_below_integral_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Back_Below_Integral_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Back_Below_Integral_' . $amp] : "";
        ${'image' . $img . 'cti_front_left_fraction_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Front_Left_Fraction_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Front_Left_Fraction_' . $amp] : "";
        ${'image' . $img . 'cti_front_right_fraction_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Front_Right_Fraction_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Front_Right_Fraction_' . $amp] : "";
        ${'image' . $img . 'cti_front_above_fraction_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Front_Above_Fraction_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Front_Above_Fraction_' . $amp] : "";
        ${'image' . $img . 'cti_front_below_fraction_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Front_Below_Fraction_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Front_Below_Fraction_' . $amp] : "";
        ${'image' . $img . 'ctix_comments_' . $amp} = isset($row['Image' . $capitalized_img . 'CTIx_Comments_' . $amp]) ? $row['Image' . $capitalized_img . 'CTIx_Comments_' . $amp] : "";
        ${'image' . $img . 'ctiy_comments_' . $amp} = isset($row['Image' . $capitalized_img . 'CTIy_Comments_' . $amp]) ? $row['Image' . $capitalized_img . 'CTIy_Comments_' . $amp] : "";
    }

    // Handling file field separately if needed
    ${'image' . $img . '_file'} = isset($row['Image' . $capitalized_img . '_File']) ? $row['Image' . $capitalized_img . '_File'] : "";
}

foreach ($image_numbers_high as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "high_" to "High_" in $img to match database structure
        $capitalized_img = str_replace("high_", "High_", $img);
        ${'image' . $img . 'tracks_' . $amp} = isset($row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'image' . $img . 'defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'image' . $img . 'noise_' . $amp} = isset($row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = isset($row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'image' . $img . 'cti_code_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'image' . $img . 'cti_visual_' . $amp} = isset($row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'image' . $img . 'comments_' . $amp} = isset($row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'image' . $img . 'reference_' . $amp} = isset($row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'image' . $img . 'region_defect_' . $amp} = isset($row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'image' . $img . 'noise_overscan_' . $amp} = isset($row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'image' . $img . 'pixel_defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'image' . $img . 'column_defects_' . $amp} = isset($row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'image' . $img . 'res_' . $amp} = isset($row['Image' . $capitalized_img . 'Res_' . $amp]) ? $row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'image' . $img . 'gain_' . $amp} = isset($row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'image' . $img . 'res_e_' . $amp} = (is_numeric(${'image' . $img . 'res_' . $amp}) && is_numeric(${'image' . $img . 'gain_' . $amp}) && ${'image' . $img . 'gain_' . $amp} != 0) ? round(${'image' . $img . 'res_' . $amp} / ${'image' . $img . 'gain_' . $amp}, 4) : "";
        ${'image' . $img . 'dark_current_' . $amp} = isset($row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'image' . $img . 'peak1_' . $amp} = isset($row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'image' . $img . 'peak2_' . $amp} = isset($row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'image' . $img . 'sigma_' . $amp} = isset($row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'image' . $img . 'front_' . $amp} = isset($row['Image' . $capitalized_img . 'Front_' . $amp]) ? $row['Image' . $capitalized_img . 'Front_' . $amp] : "";
    }

    // Handling file field separately if needed
    ${'image' . $img . '_file'} = isset($row['Image' . $capitalized_img . '_File']) ? $row['Image' . $capitalized_img . '_File'] : "";
}

if (isset($row['Last_Update'])) {
    $last_update = (int)$row['Last_Update'];
} else {
    $last_update = 0;
}
