<?php
// get_module_comparison_vals.php
// Cinyu Zhu, Hopkins, 2025

if (empty($_SESSION['choosen_module_underground'])) {
    $_SESSION['choosen_module_underground'] = 1;
}

$id = (int)$_SESSION['choosen_module_underground'];
// Query to fetch data from the MODULE_UNDERGROUND table
$udg_query = "SELECT * FROM `MODULE_UNDERGROUND2` WHERE `ID` = " . $id;
$udg_result = mysql_query($udg_query);
if (!$udg_result) {
    die("Could not query the database <br />" . mysql_error());
}
$udg_row = mysql_fetch_assoc($udg_result);

// Query to fetch data from the MODULE_SURFACE table
$sur_query = "SELECT * FROM `MODULE_SURFACE` WHERE `ID` = " . $id;
$sur_result = mysql_query($sur_query);
if (!$sur_result) {
    die("Could not query the database <br />" . mysql_error());
}

$sur_row = mysql_fetch_assoc($sur_result);




// start fetching surface values
// Fetching all regular fields
$sur_name = isset($sur_row['Name']) ? $sur_row['Name'] : "";
$sur_status = isset($sur_row['Status']) ? $sur_row['Status'] : "";
$sur_pitch_adaptor_id = isset($sur_row['Pitch_Adaptor_ID']) ? $sur_row['Pitch_Adaptor_ID'] : "";
$sur_activation = isset($sur_row['Activation']) ? $sur_row['Activation'] : "";
$sur_humidity = isset($sur_row['Humidity']) ? $sur_row['Humidity'] : "";
$sur_radon = isset($sur_row['Radon']) ? $sur_row['Radon'] : "";
$sur_die_A = isset($sur_row['Die_A']) ? $sur_row['Die_A'] : "";
$sur_die_B = isset($sur_row['Die_B']) ? $sur_row['Die_B'] : "";
$sur_die_C = isset($sur_row['Die_C']) ? $sur_row['Die_C'] : "";
$sur_die_D = isset($sur_row['Die_D']) ? $sur_row['Die_D'] : "";
$sur_amp_A = isset($sur_row['Amp_A']) ? $sur_row['Amp_A'] : "";
$sur_amp_B = isset($sur_row['Amp_B']) ? $sur_row['Amp_B'] : "";
$sur_amp_C = isset($sur_row['Amp_C']) ? $sur_row['Amp_C'] : "";
$sur_amp_D = isset($sur_row['Amp_D']) ? $sur_row['Amp_D'] : "";
$sur_channel_A = isset($sur_row['Channel_A']) ? $sur_row['Channel_A'] : "";
$sur_channel_B = isset($sur_row['Channel_B']) ? $sur_row['Channel_B'] : "";
$sur_channel_C = isset($sur_row['Channel_C']) ? $sur_row['Channel_C'] : "";
$sur_channel_D = isset($sur_row['Channel_D']) ? $sur_row['Channel_D'] : "";
$sur_grade_A = isset($sur_row['Grade_A']) ? $sur_row['Grade_A'] : "";
$sur_grade_B = isset($sur_row['Grade_B']) ? $sur_row['Grade_B'] : "";
$sur_grade_C = isset($sur_row['Grade_C']) ? $sur_row['Grade_C'] : "";
$sur_grade_D = isset($sur_row['Grade_D']) ? $sur_row['Grade_D'] : "";
$sur_defects_A = isset($sur_row['Defects_A']) ? $sur_row['Defects_A'] : "";
$sur_defects_B = isset($sur_row['Defects_B']) ? $sur_row['Defects_B'] : "";
$sur_defects_C = isset($sur_row['Defects_C']) ? $sur_row['Defects_C'] : "";
$sur_defects_D = isset($sur_row['Defects_D']) ? $sur_row['Defects_D'] : "";
$sur_notes_A = isset($sur_row['Notes_A']) ? $sur_row['Notes_A'] : "";
$sur_notes_B = isset($sur_row['Notes_B']) ? $sur_row['Notes_B'] : "";
$sur_notes_C = isset($sur_row['Notes_C']) ? $sur_row['Notes_C'] : "";
$sur_notes_D = isset($sur_row['Notes_D']) ? $sur_row['Notes_D'] : "";
$sur_check_A = isset($sur_row['Check_A']) ? $sur_row['Check_A'] : "";
$sur_check_B = isset($sur_row['Check_B']) ? $sur_row['Check_B'] : "";
$sur_check_C = isset($sur_row['Check_C']) ? $sur_row['Check_C'] : "";
$sur_check_D = isset($sur_row['Check_D']) ? $sur_row['Check_D'] : "";
$sur_reviewer = isset($sur_row['Reviewer']) ? $sur_row['Reviewer'] : "";
$sur_notes = isset($sur_row['Notes']) ? $sur_row['Notes'] : "";
$sur_tester = isset($sur_row['Tester']) ? $sur_row['Tester'] : "";
$sur_test_date = isset($sur_row['Test_Date']) ? $sur_row['Test_Date'] : "";
$sur_test_time = isset($sur_row['Test_Time']) ? $sur_row['Test_Time'] : "";
$sur_chamber = isset($sur_row['Chamber']) ? $sur_row['Chamber'] : "";
$sur_temp_low = isset($sur_row['Temp_Low']) ? $sur_row['Temp_Low'] : "";
$sur_temp_high = isset($sur_row['Temp_High']) ? $sur_row['Temp_High'] : "";
$sur_feedthru_position = isset($sur_row['Feedthru_Position']) ? $sur_row['Feedthru_Position'] : "";
$sur_ACM = isset($sur_row['ACM']) ? $sur_row['ACM'] : "";
$sur_script = isset($sur_row['Script']) ? $sur_row['Script'] : "";
$sur_image_dir = isset($sur_row['Image_Dir']) ? $sur_row['Image_Dir'] : "";
// Trace fields
foreach ($ccds as $amp) {
    ${'sur_trace_high_saturation_' . $amp} = isset($sur_row['Trace_High_Saturation_' . $amp]) ? $sur_row['Trace_High_Saturation_' . $amp] : "";
    ${'sur_trace_high_comments_' . $amp} = isset($sur_row['Trace_High_Comments_' . $amp]) ? $sur_row['Trace_High_Comments_' . $amp] : "";
    ${'sur_trace_high_reference_' . $amp} = isset($sur_row['Trace_High_Reference_' . $amp]) ? $sur_row['Trace_High_Reference_' . $amp] : "";
}
// Image fields
foreach ($image_numbers_low as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "low_" to "Low_" in $img to match database structure
        $capitalized_img = str_replace("low_", "Low_", $img);
        ${'sur_image' . $img . 'tracks_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'sur_image' . $img . 'defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'sur_image' . $img . 'noise_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'sur_image' . $img . 'sharpness_tracks_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'sur_image' . $img . 'cti_code_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'sur_image' . $img . 'cti_visual_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'sur_image' . $img . 'comments_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'sur_image' . $img . 'reference_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'sur_image' . $img . 'region_defect_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'sur_image' . $img . 'noise_overscan_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'sur_image' . $img . 'pixel_defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'sur_image' . $img . 'column_defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'sur_image' . $img . 'res_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Res_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'sur_image' . $img . 'gain_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'sur_image' . $img . 'res_e_' . $amp} = (is_numeric(${'sur_image' . $img . 'res_' . $amp}) && is_numeric(${'sur_image' . $img . 'gain_' . $amp}) && ${'sur_image' . $img . 'gain_' . $amp} != 0) ? round(${'sur_image' . $img . 'res_' . $amp} / ${'sur_image' . $img . 'gain_' . $amp}, 4) : "";
        ${'sur_image' . $img . 'dark_current_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'sur_image' . $img . 'peak1_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'sur_image' . $img . 'peak2_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'sur_image' . $img . 'sigma_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'sur_image' . $img . 'front_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Front_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Front_' . $amp] : "";
    }
}

foreach ($image_numbers_high as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "high_" to "High_" in $img to match database structure
        $capitalized_img = str_replace("high_", "High_", $img);
        ${'sur_image' . $img . 'tracks_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'sur_image' . $img . 'defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'sur_image' . $img . 'noise_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'sur_image' . $img . 'sharpness_tracks_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'sur_image' . $img . 'cti_code_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'sur_image' . $img . 'cti_visual_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'sur_image' . $img . 'comments_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'sur_image' . $img . 'reference_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'sur_image' . $img . 'region_defect_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'sur_image' . $img . 'noise_overscan_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'sur_image' . $img . 'pixel_defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'sur_image' . $img . 'column_defects_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'sur_image' . $img . 'res_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Res_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'sur_image' . $img . 'gain_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'sur_image' . $img . 'res_e_' . $amp} = (is_numeric(${'sur_image' . $img . 'res_' . $amp}) && is_numeric(${'sur_image' . $img . 'gain_' . $amp}) && ${'sur_image' . $img . 'gain_' . $amp} != 0) ? round(${'sur_image' . $img . 'res_' . $amp} / ${'sur_image' . $img . 'gain_' . $amp}, 4) : "";
        ${'sur_image' . $img . 'dark_current_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'sur_image' . $img . 'peak1_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'sur_image' . $img . 'peak2_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'sur_image' . $img . 'sigma_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'sur_image' . $img . 'front_' . $amp} = isset($sur_row['Image' . $capitalized_img . 'Front_' . $amp]) ? $sur_row['Image' . $capitalized_img . 'Front_' . $amp] : "";
    }
}

// start fetching underground values
// Fetching all regular fields
$udg_name = isset($udg_row['Name']) ? $udg_row['Name'] : "";
$udg_status = isset($udg_row['Status']) ? $udg_row['Status'] : "";
$udg_pitch_adaptor_id = isset($udg_row['Pitch_Adaptor_ID']) ? $udg_row['Pitch_Adaptor_ID'] : "";
$udg_activation = isset($udg_row['Activation']) ? $udg_row['Activation'] : "";
$udg_humidity = isset($udg_row['Humidity']) ? $udg_row['Humidity'] : "";
$udg_radon = isset($udg_row['Radon']) ? $udg_row['Radon'] : "";
$udg_die_A = isset($udg_row['Die_A']) ? $udg_row['Die_A'] : "";
$udg_die_B = isset($udg_row['Die_B']) ? $udg_row['Die_B'] : "";
$udg_die_C = isset($udg_row['Die_C']) ? $udg_row['Die_C'] : "";
$udg_die_D = isset($udg_row['Die_D']) ? $udg_row['Die_D'] : "";
$udg_amp_A = isset($udg_row['Amp_A']) ? $udg_row['Amp_A'] : "";
$udg_amp_B = isset($udg_row['Amp_B']) ? $udg_row['Amp_B'] : "";
$udg_amp_C = isset($udg_row['Amp_C']) ? $udg_row['Amp_C'] : "";
$udg_amp_D = isset($udg_row['Amp_D']) ? $udg_row['Amp_D'] : "";
$udg_channel_A = isset($udg_row['Channel_A']) ? $udg_row['Channel_A'] : "";
$udg_channel_B = isset($udg_row['Channel_B']) ? $udg_row['Channel_B'] : "";
$udg_channel_C = isset($udg_row['Channel_C']) ? $udg_row['Channel_C'] : "";
$udg_channel_D = isset($udg_row['Channel_D']) ? $udg_row['Channel_D'] : "";
$udg_grade_A = isset($udg_row['Grade_A']) ? $udg_row['Grade_A'] : "";
$udg_grade_B = isset($udg_row['Grade_B']) ? $udg_row['Grade_B'] : "";
$udg_grade_C = isset($udg_row['Grade_C']) ? $udg_row['Grade_C'] : "";
$udg_grade_D = isset($udg_row['Grade_D']) ? $udg_row['Grade_D'] : "";
$udg_defects_A = isset($udg_row['Defects_A']) ? $udg_row['Defects_A'] : "";
$udg_defects_B = isset($udg_row['Defects_B']) ? $udg_row['Defects_B'] : "";
$udg_defects_C = isset($udg_row['Defects_C']) ? $udg_row['Defects_C'] : "";
$udg_defects_D = isset($udg_row['Defects_D']) ? $udg_row['Defects_D'] : "";
$udg_notes_A = isset($udg_row['Notes_A']) ? $udg_row['Notes_A'] : "";
$udg_notes_B = isset($udg_row['Notes_B']) ? $udg_row['Notes_B'] : "";
$udg_notes_C = isset($udg_row['Notes_C']) ? $udg_row['Notes_C'] : "";
$udg_notes_D = isset($udg_row['Notes_D']) ? $udg_row['Notes_D'] : "";
$udg_check_A = isset($udg_row['Check_A']) ? $udg_row['Check_A'] : "";
$udg_check_B = isset($udg_row['Check_B']) ? $udg_row['Check_B'] : "";
$udg_check_C = isset($udg_row['Check_C']) ? $udg_row['Check_C'] : "";
$udg_check_D = isset($udg_row['Check_D']) ? $udg_row['Check_D'] : "";
$udg_reviewer = isset($udg_row['Reviewer']) ? $udg_row['Reviewer'] : "";
$udg_notes = isset($udg_row['Notes']) ? $udg_row['Notes'] : "";
$udg_tester = isset($udg_row['Tester']) ? $udg_row['Tester'] : "";
$udg_test_date = isset($udg_row['Test_Date']) ? $udg_row['Test_Date'] : "";
$udg_test_time = isset($udg_row['Test_Time']) ? $udg_row['Test_Time'] : "";
$udg_chamber = isset($udg_row['Chamber']) ? $udg_row['Chamber'] : "";
$udg_temp_low_B = isset($udg_row['Temp_Low_B']) ? $udg_row['Temp_Low_B'] : "";
$udg_temp_high_B = isset($udg_row['Temp_High_B']) ? $udg_row['Temp_High_B'] : "";
$udg_temp_low_C = isset($udg_row['Temp_Low_C']) ? $udg_row['Temp_Low_C'] : "";
$udg_temp_high_C = isset($udg_row['Temp_High_C']) ? $udg_row['Temp_High_C'] : "";
$udg_feedthru_position = isset($udg_row['Feedthru_Position']) ? $udg_row['Feedthru_Position'] : "";
$udg_ACM = isset($udg_row['ACM']) ? $udg_row['ACM'] : "";
$udg_script = isset($udg_row['Script']) ? $udg_row['Script'] : "";
$udg_image_dir = isset($udg_row['Image_Dir']) ? $udg_row['Image_Dir'] : "";
// Trace fields
foreach ($ccds as $amp) {
    ${'udg_trace_high_saturation_' . $amp} = isset($udg_row['Trace_High_Saturation_' . $amp]) ? $udg_row['Trace_High_Saturation_' . $amp] : "";
    ${'udg_trace_high_comments_' . $amp} = isset($udg_row['Trace_High_Comments_' . $amp]) ? $udg_row['Trace_High_Comments_' . $amp] : "";
    ${'udg_trace_high_reference_' . $amp} = isset($udg_row['Trace_High_Reference_' . $amp]) ? $udg_row['Trace_High_Reference_' . $amp] : "";
}
// Image fields
foreach ($image_numbers_low as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "low_" to "Low_" in $img to match database structure
        $capitalized_img = str_replace("low_", "Low_", $img);
        ${'udg_image' . $img . 'tracks_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'udg_image' . $img . 'defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'udg_image' . $img . 'noise_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'udg_image' . $img . 'sharpness_tracks_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'udg_image' . $img . 'cti_code_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'udg_image' . $img . 'cti_visual_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'udg_image' . $img . 'comments_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'udg_image' . $img . 'reference_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'udg_image' . $img . 'region_defect_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'udg_image' . $img . 'noise_overscan_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'udg_image' . $img . 'pixel_defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'udg_image' . $img . 'column_defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'udg_image' . $img . 'res_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Res_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'udg_image' . $img . 'gain_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'udg_image' . $img . 'res_e_' . $amp} = (is_numeric(${'udg_image' . $img . 'res_' . $amp}) && is_numeric(${'udg_image' . $img . 'gain_' . $amp}) && ${'udg_image' . $img . 'gain_' . $amp} != 0) ? round(${'udg_image' . $img . 'res_' . $amp} / ${'udg_image' . $img . 'gain_' . $amp}, 3) : "";
        ${'udg_image' . $img . 'dark_current_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'udg_image' . $img . 'peak1_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'udg_image' . $img . 'peak2_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'udg_image' . $img . 'sigma_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'udg_image' . $img . 'front_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Front_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Front_' . $amp] : "";
    }
}

foreach ($image_numbers_high as $img) {
    foreach ($ccds as $amp) {
        // Capitalize "high_" to "High_" in $img to match database structure
        $capitalized_img = str_replace("high_", "High_", $img);
        ${'udg_image' . $img . 'tracks_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Tracks_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Tracks_' . $amp] : "";
        ${'udg_image' . $img . 'defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Defects_' . $amp] : "";
        ${'udg_image' . $img . 'noise_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Noise_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Noise_' . $amp] : "";
        ${'udg_image' . $img . 'sharpness_tracks_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Sharpness_Tracks_' . $amp] : "";
        ${'udg_image' . $img . 'cti_code_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'CTI_Code_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'CTI_Code_' . $amp] : "";
        ${'udg_image' . $img . 'cti_visual_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'CTI_Visual_' . $amp] : "";
        ${'udg_image' . $img . 'comments_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Comments_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Comments_' . $amp] : "";
        ${'udg_image' . $img . 'reference_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Reference_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Reference_' . $amp] : "";
        ${'udg_image' . $img . 'region_defect_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Region_Defect_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Region_Defect_' . $amp] : "";
        ${'udg_image' . $img . 'noise_overscan_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Noise_Overscan_' . $amp] : "";
        ${'udg_image' . $img . 'pixel_defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Pixel_Defects_' . $amp] : "";
        ${'udg_image' . $img . 'column_defects_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Column_Defects_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Column_Defects_' . $amp] : "";
        ${'udg_image' . $img . 'res_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Res_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Res_' . $amp] : "";
        ${'udg_image' . $img . 'gain_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Gain_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Gain_' . $amp] : "";
        ${'udg_image' . $img . 'res_e_' . $amp} = (is_numeric(${'udg_image' . $img . 'res_' . $amp}) && is_numeric(${'udg_image' . $img . 'gain_' . $amp}) && ${'udg_image' . $img . 'gain_' . $amp} != 0) ? round(${'udg_image' . $img . 'res_' . $amp} / ${'udg_image' . $img . 'gain_' . $amp}, 3) : "";
        ${'udg_image' . $img . 'dark_current_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
        ${'udg_image' . $img . 'peak1_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Peak1_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Peak1_' . $amp] : "";
        ${'udg_image' . $img . 'peak2_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Peak2_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Peak2_' . $amp] : "";
        ${'udg_image' . $img . 'sigma_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Sigma_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Sigma_' . $amp] : "";
        ${'udg_image' . $img . 'front_' . $amp} = isset($udg_row['Image' . $capitalized_img . 'Front_' . $amp]) ? $udg_row['Image' . $capitalized_img . 'Front_' . $amp] : "";
    }
}
if (isset($udg_row['Last_Update'])) {
    $last_update = (int)$udg_row['Last_Update'];
} else {
    $last_update = 0;
}
