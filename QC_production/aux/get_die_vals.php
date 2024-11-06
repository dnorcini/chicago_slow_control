<?php
// get_die_vals.php
// D.Norcini, Hopkins, 2024

if (empty($_SESSION['choosen_die'])) {
    $_SESSION['choosen_die'] = 1;
}

$id = (int)$_SESSION['choosen_die'];

// Query to fetch data from the DIE table
$query = "SELECT * FROM `DIE` WHERE `ID` = " . $id;
$result = mysql_query($query);
if (!$result) {
    die("Could not query the database <br />" . mysql_error());
}

$row = mysql_fetch_assoc($result);

// Fetching all regular fields
$name = isset($row['Name']) ? $row['Name'] : "";
$status = isset($row['Status']) ? $row['Status'] : "";
$wafer_id = isset($row['Wafer_ID']) ? $row['Wafer_ID'] : "";
$wafer_position = isset($row['Wafer_Position']) ? $row['Wafer_Position'] : "";
$activation = isset($row['Activation']) ? $row['Activation'] : "";
$humidity = isset($row['Humidity']) ? $row['Humidity'] : "";
$radon = isset($row['Radon']) ? $row['Radon'] : "";
$grade_L1 = isset($row['Grade_L1']) ? $row['Grade_L1'] : "";
$grade_L2 = isset($row['Grade_L2']) ? $row['Grade_L2'] : "";
$grade_U1 = isset($row['Grade_U1']) ? $row['Grade_U1'] : "";
$grade_U2 = isset($row['Grade_U2']) ? $row['Grade_U2'] : "";
$notes_L1 = isset($row['Notes_L1']) ? $row['Notes_L1'] : "";
$notes_L2 = isset($row['Notes_L2']) ? $row['Notes_L2'] : "";
$notes_U1 = isset($row['Notes_U1']) ? $row['Notes_U1'] : "";
$notes_U2 = isset($row['Notes_U2']) ? $row['Notes_U2'] : "";
$check_L1 = isset($row['Check_L1']) ? $row['Check_L1'] : "";
$check_L2 = isset($row['Check_L2']) ? $row['Check_L2'] : "";
$check_U1 = isset($row['Check_U1']) ? $row['Check_U1'] : "";
$check_U2 = isset($row['Check_U2']) ? $row['Check_U2'] : "";
$reviewer = isset($row['Reviewer']) ? $row['Reviewer'] : "";
$notes = isset($row['Notes']) ? $row['Notes'] : "";
$tester = isset($row['Tester']) ? $row['Tester'] : "";
$test_date = isset($row['Test_Date']) ? $row['Test_Date'] : "";
$test_time = isset($row['Test_Time']) ? $row['Test_Time'] : "";
$chamber = isset($row['Chamber']) ? $row['Chamber'] : "";
$temp = isset($row['Temp']) ? $row['Temp'] : "";
$feedthru_position = isset($row['Feedthru_Position']) ? $row['Feedthru_Position'] : "";
$ACM = isset($row['ACM']) ? $row['ACM'] : "";
$script = isset($row['Script']) ? $row['Script'] : "";
$image_dir = isset($row['Image_Dir']) ? $row['Image_Dir'] : "";

// Trace fields
foreach ($amplifiers as $amp) {
    ${'trace_saturation_' . $amp} = isset($row['Trace_Saturation_' . $amp]) ? $row['Trace_Saturation_' . $amp] : "";
    ${'trace_comments_' . $amp} = isset($row['Trace_Comments_' . $amp]) ? $row['Trace_Comments_' . $amp] : "";
    ${'trace_reference_' . $amp} = isset($row['Trace_Reference_' . $amp]) ? $row['Trace_Reference_' . $amp] : "";
}
//$trace_file = isset($row['Trace_File']) ? $row['Trace_File'] : "";
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";

// Image fields
foreach ($image_numbers as $img) {
    foreach ($amplifiers as $amp) {
        ${'image' . $img . 'tracks_' . $amp} = isset($row['Image' . $img . 'Tracks_' . $amp]) ? $row['Image' . $img . 'Tracks_' . $amp] : "";
        ${'image' . $img . 'defects_' . $amp} = isset($row['Image' . $img . 'Defects_' . $amp]) ? $row['Image' . $img . 'Defects_' . $amp] : "";
        ${'image' . $img . 'noise_' . $amp} = isset($row['Image' . $img . 'Noise_' . $amp]) ? $row['Image' . $img . 'Noise_' . $amp] : "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = isset($row['Image' . $img . 'Sharpness_Tracks_' . $amp]) ? $row['Image' . $img . 'Sharpness_Tracks_' . $amp] : "";
        ${'image' . $img . 'cti_code_' . $amp} = isset($row['Image' . $img . 'CTI_Code_' . $amp]) ? $row['Image' . $img . 'CTI_Code_' . $amp] : "";
        ${'image' . $img . 'cti_visual_' . $amp} = isset($row['Image' . $img . 'CTI_Visual_' . $amp]) ? $row['Image' . $img . 'CTI_Visual_' . $amp] : "";
        ${'image' . $img . 'comments_' . $amp} = isset($row['Image' . $img . 'Comments_' . $amp]) ? $row['Image' . $img . 'Comments_' . $amp] : "";
        ${'image' . $img . 'reference_' . $amp} = isset($row['Image' . $img . 'Reference_' . $amp]) ? $row['Image' . $img . 'Reference_' . $amp] : "";
        ${'image' . $img . 'region_defect_' . $amp} = isset($row['Image' . $img . 'Region_Defect_' . $amp]) ? $row['Image' . $img . 'Region_Defect_' . $amp] : "";
        ${'image' . $img . 'noise_overscan_' . $amp} = isset($row['Image' . $img . 'Noise_Overscan_' . $amp]) ? $row['Image' . $img . 'Noise_Overscan_' . $amp] : "";
        ${'image' . $img . 'pixel_defects_' . $amp} = isset($row['Image' . $img . 'Pixel_Defects_' . $amp]) ? $row['Image' . $img . 'Pixel_Defects_' . $amp] : "";
        ${'image' . $img . 'column_defects_' . $amp} = isset($row['Image' . $img . 'Column_Defects_' . $amp]) ? $row['Image' . $img . 'Column_Defects_' . $amp] : "";
        ${'image' . $img . 'res_' . $amp} = isset($row['Image' . $img . 'Res_' . $amp]) ? $row['Image' . $img . 'Res_' . $amp] : "";
        ${'image' . $img . 'gain_' . $amp} = isset($row['Image' . $img . 'Gain_' . $amp]) ? $row['Image' . $img . 'Gain_' . $amp] : "";
        ${'image' . $img . 'dark_current_' . $amp} = isset($row['Image' . $img . 'Dark_Current_' . $amp]) ? $row['Image' . $img . 'Dark_Current_' . $amp] : "";
    }
    //${'image' . $img . '_file'} = isset($row['Image' . $img . '_File]) ? $row['Image' . $img . '_File'] : "";
}

if (isset($row['Last_Update'])) {
    $last_update = (int)$row['Last_Update'];
} else {
    $last_update = 0;
}

?>
