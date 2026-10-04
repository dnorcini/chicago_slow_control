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
$name = $row['Name'] ?? "";
$status = $row['Status'] ?? "";
$wafer_id = $row['Wafer_ID'] ?? "";
$wafer_position = $row['Wafer_Position'] ?? "";
$activation = $row['Activation'] ?? "";
$humidity = $row['Humidity'] ?? "";
$radon = $row['Radon'] ?? "";
$grade_L1 = $row['Grade_L1'] ?? "";
$grade_L2 = $row['Grade_L2'] ?? "";
$grade_U1 = $row['Grade_U1'] ?? "";
$grade_U2 = $row['Grade_U2'] ?? "";
$notes_L1 = $row['Notes_L1'] ?? "";
$notes_L2 = $row['Notes_L2'] ?? "";
$notes_U1 = $row['Notes_U1'] ?? "";
$notes_U2 = $row['Notes_U2'] ?? "";
$check_L1 = $row['Check_L1'] ?? "";
$check_L2 = $row['Check_L2'] ?? "";
$check_U1 = $row['Check_U1'] ?? "";
$check_U2 = $row['Check_U2'] ?? "";
$reviewer = $row['Reviewer'] ?? "";
$notes = $row['Notes'] ?? "";
$tester = $row['Tester'] ?? "";
$test_date = $row['Test_Date'] ?? "";
$test_time = $row['Test_Time'] ?? "";
$chamber = $row['Chamber'] ?? "";
$temp = $row['Temp'] ?? "";
$feedthru_position = $row['Feedthru_Position'] ?? "";
$ACM = $row['ACM'] ?? "";
$script = $row['Script'] ?? "";
$image_dir = $row['Image_Dir'] ?? "";

// Trace fields
foreach ($amplifiers as $amp) {
    ${'trace_saturation_' . $amp} = $row['Trace_Saturation_' . $amp] ?? "";
    ${'trace_comments_' . $amp} = $row['Trace_Comments_' . $amp] ?? "";
    ${'trace_reference_' . $amp} = $row['Trace_Reference_' . $amp] ?? "";
}
//$trace_log = isset($row['Trace_Log']) ? $row['Trace_Log'] : "";

// Image fields
foreach ($image_numbers as $img) {
    foreach ($amplifiers as $amp) {
        ${'image' . $img . 'tracks_' . $amp} = $row['Image' . $img . 'Tracks_' . $amp] ?? "";
        ${'image' . $img . 'defects_' . $amp} = $row['Image' . $img . 'Defects_' . $amp] ?? "";
        ${'image' . $img . 'noise_' . $amp} = $row['Image' . $img . 'Noise_' . $amp] ?? "";
        ${'image' . $img . 'sharpness_tracks_' . $amp} = $row['Image' . $img . 'Sharpness_Tracks_' . $amp] ?? "";
        ${'image' . $img . 'cti_code_' . $amp} = $row['Image' . $img . 'CTI_Code_' . $amp] ?? "";
        ${'image' . $img . 'cti_visual_' . $amp} = $row['Image' . $img . 'CTI_Visual_' . $amp] ?? "";
        ${'image' . $img . 'comments_' . $amp} = $row['Image' . $img . 'Comments_' . $amp] ?? "";
        ${'image' . $img . 'reference_' . $amp} = $row['Image' . $img . 'Reference_' . $amp] ?? "";
        ${'image' . $img . 'region_defect_' . $amp} = $row['Image' . $img . 'Region_Defect_' . $amp] ?? "";
        ${'image' . $img . 'noise_overscan_' . $amp} = $row['Image' . $img . 'Noise_Overscan_' . $amp] ?? "";
        ${'image' . $img . 'pixel_defects_' . $amp} = $row['Image' . $img . 'Pixel_Defects_' . $amp] ?? "";
        ${'image' . $img . 'column_defects_' . $amp} = $row['Image' . $img . 'Column_Defects_' . $amp] ?? "";
        ${'image' . $img . 'res_' . $amp} = $row['Image' . $img . 'Res_' . $amp] ?? "";
        ${'image' . $img . 'gain_' . $amp} = $row['Image' . $img . 'Gain_' . $amp] ?? "";
        ${'image' . $img . 'dark_current_' . $amp} = $row['Image' . $img . 'Dark_Current_' . $amp] ?? "";
    }
}

if (isset($row['Last_Update'])) {
    $last_update = (int)$row['Last_Update'];
} else {
    $last_update = 0;
}

?>
