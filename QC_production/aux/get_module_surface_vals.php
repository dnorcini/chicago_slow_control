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
    module_surface("Could not query the database <br />" . mysql_error());
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
$grade_A = isset($row['Grade_A']) ? $row['Grade_A'] : "";
$grade_B = isset($row['Grade_B']) ? $row['Grade_B'] : "";
$grade_C = isset($row['Grade_C']) ? $row['Grade_C'] : "";
$grade_D = isset($row['Grade_D']) ? $row['Grade_D'] : "";
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
$temp_low = isset($row['Temp_Low']) ? $row['Temp_Low'] : "";
$temp_high = isset($row['Temp_High']) ? $row['Temp_High'] : "";
$feedthru_position = isset($row['Feedthru_Position']) ? $row['Feedthru_Position'] : "";
$ACM = isset($row['ACM']) ? $row['ACM'] : "";
$script = isset($row['Script']) ? $row['Script'] : "";
$image_dir = isset($row['Image_Dir']) ? $row['Image_Dir'] : "";

// Trace fields
foreach ($ccds as $amp) {
    ${'trace_low_saturation_' . $amp} = isset($row['Trace_Low_Saturation_' . $amp]) ? $row['Trace_Low_Saturation_' . $amp] : "";
    ${'trace_low_comments_' . $amp} = isset($row['Trace_Low_Comments_' . $amp]) ? $row['Trace_Low_Comments_' . $amp] : "";
    ${'trace_low_reference_' . $amp} = isset($row['Trace_Low_Reference_' . $amp]) ? $row['Trace_Low_Reference_' . $amp] : "";
}
//$trace_file = isset($row['Trace_File']) ? $row['Trace_File'] : "";
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";

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
        ${'image' . $img . 'dark_current_' . $amp} = isset($row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
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
        ${'image' . $img . 'dark_current_' . $amp} = isset($row['Image' . $capitalized_img . 'Dark_Current_' . $amp]) ? $row['Image' . $capitalized_img . 'Dark_Current_' . $amp] : "";
    }

    // Handling file field separately if needed
    ${'image' . $img . '_file'} = isset($row['Image' . $capitalized_img . '_File']) ? $row['Image' . $capitalized_img . '_File'] : "";
}

if (isset($row['Last_Update'])) {
    $last_update = (int)$row['Last_Update'];
} else {
    $last_update = 0;
}

?>
