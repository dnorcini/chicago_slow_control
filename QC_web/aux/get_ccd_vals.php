<?php
// get_ccd_vals.php
// D.Norcini, UChicago, 2020
//

if (empty($_SESSION['choosen_ccd']))
  $_SESSION['choosen_ccd'] = 1;
$id = (int)$_SESSION['choosen_ccd'];

$query = "SELECT * FROM `CCD` WHERE `ID` = ".$id;
$result = mysql_query($query);
if (!$result)
  die ("Could not query the database <br />" . mysql_error());

$glue_parm_values = array();
$wb_parm_values = array();
$testing_noise_values = array();
$testing_resolution_values = array();
$testing_gain_values = array();
$testing_dark_current_values = array();

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    foreach ($glue_parameter_names as $glue_parm_name)
      {
    	if (isset($row[$glue_parm_name]))
    	  $glue_parm_values[] = (double)$row[$glue_parm_name];
    	else
    	  $glue_parm_values[] = NULL;
      }

    foreach ($wb_parameter_names as $wb_parm_name)
      {
    	if (isset($row[$wb_parm_name]))
    	  $wb_parm_values[] = (double)$row[$wb_parm_name];
    	else
    	  $wb_parm_values[] = NULL;
      }
   
    foreach ($testing_noise_names as $testing_noise_name)
      {
	if (isset($row[$testing_noise_name]))
	  $testing_noise_values[] = (double)$row[$testing_noise_name];
	else
	  $testing_noise_values[] = NULL;
      }

    foreach ($testing_resolution_names as $testing_resolution_name)
      {
	if (isset($row[$testing_resolution_name]))
	  $testing_resolution_values[] = (double)$row[$testing_resolution_name];
	else
	  $testing_resolution_values[] = NULL;
      }

    foreach ($testing_gain_names as $testing_gain_name)
      {
	if (isset($row[$testing_gain_name]))
	  $testing_gain_values[] = (double)$row[$testing_gain_name];
	else
	  $testing_gain_values[] = NULL;
      }

    foreach ($testing_dark_current_names as $testing_dark_current_name)
      {
	if (isset($row[$testing_dark_current_name]))
	  $testing_dark_current_values[] = (double)$row[$testing_dark_current_name];
	else
	  $testing_dark_current_values[] = NULL;
      }

    if (isset($row['ID']))
      $id = $row['ID'];
    else
      $id = "Unknown";

    if (isset($row['CCD_Type']))
      $ccd_type = $row['CCD_Type'];
    else
      $ccd_type = "";

    if (isset($row['Size']))
      $size = $row['Size'];
    else
      $size = "";

   if (isset($row['Name']))
      $name = $row['Name'];
    else
      $name = "";

    if (isset($row['Status']))
      $status = $row['Status'];
    else
      $status = "Unknown";

    if (isset($row['Packager']))
      $packager = $row['Packager'];
    else
      $packager = "Unknown";
      
    if (isset($row['Location']))
      $location = $row['Location'];
    else
      $location = "Unknown";

    if (isset($row['Packaging_date']))
      $packaging = $row['Packaging_date'];
    else
      $packaging = "";

    if (isset($row['Wafer_ID']))
      $wafer_id = $row['Wafer_ID'];
    else
      $wafer_id = "";

    if (isset($row['Wafer_position']))
      $wafer_position = $row['Wafer_position'];
    else
      $wafer_position = "";
      
    if (isset($row['Production_date']))
      $production_date = $row['Production_date'];
    else
      $production_date = "";
      
    if (isset($row['Note']))
      $note = $row['Note'];
    else
      $note = "";

    if (isset($row['Last_update']))
      $last_update = (int)$row['Last_update'];
    else
      $last_update = 0;    

    /* if (isset($row['Glue_humid'])) */
    /*   $glue_humid = (double)$row['Glue_humid']; */
    /* else */
    /*   $glue_humid = 0; */

    /* if (isset($row['Glue_temp'])) */
    /*   $glue_temp = (double)$row['Glue_temp']; */
    /* else */
    /*   $glue_temp = 0; */

    /* if (isset($row['Wb_humid'])) */
    /*   $wb_humid = (double)$row['Wb_humid']; */
    /* else */
    /*   $wb_humid = 0; */

    /* if (isset($row['Wb_temp'])) */
    /*   $wb_temp = (double)$row['Wb_temp']; */
    /* else */
    /*   $wb_temp = 0; */

    if (isset($row['Wb_power']))
      $wb_power = (double)$row['Wb_power'];
    else
      $wb_power = 0;

    if (isset($row['Wb_time']))
      $wb_time = (double)$row['Wb_time'];
    else
	$wb_time = 0;

    if (isset($row['Cable_np']))
      $cable_np = (int)$row['Cable_np'];
    else
      $cable_np = 0;

    if (isset($row['JFET_U1']))
      $jfet_u1 = (int)$row['JFET_U1'];
    else
      $jfet_u1 = 0;

    if (isset($row['JFET_L1']))
      $jfet_l1 = (int)$row['JFET_L1'];
    else
      $jfet_l1 = 0;

    if (isset($row['JFET_U2']))
      $jfet_u2 = (int)$row['JFET_U2'];
    else
      $jfet_u2 = 0;

    if (isset($row['JFET_L2']))
      $jfet_l2 = (int)$row['JFET_L2'];
    else
      $jfet_l2 = 0;

    if (isset($row['Gluing_details']))
      $gluing_details = $row['Gluing_details'];
    else
      $gluing_details = "";
      
    if (isset($row['Wirebonding_details']))
      $wirebonding_details = $row['Wirebonding_details'];
    else
      $wirebonding_details = "";
      
    if (isset($row['AMP_U1']))
      $amp_u1 = (int)$row['AMP_U1'];
    else
      $amp_u1 = 0;

    if (isset($row['AMP_L1']))
      $amp_l1 = (int)$row['AMP_L1'];
    else
      $amp_l1 = 0;

    if (isset($row['AMP_U2']))
      $amp_u2 = (int)$row['AMP_U2'];
    else
      $amp_u2 = 0;

    if (isset($row['AMP_L2']))
      $amp_l2 = (int)$row['AMP_L2'];
    else
      $amp_l2 = 0;

    if (isset($row['Defects']))
      $defects = $row['Defects'];
    else
      $defects = "";

   if (isset($row['Eff_resistivity']))
      $eff_resistivity = $row['Eff_resistivity'];
    else
      $eff_resistivity = "";

   if (isset($row['Test_temp']))
      $test_temp = $row['Test_temp'];
    else
      $test_temp = NULL;

   if (isset($row['Test_vref']))
      $test_vref = $row['Test_vref'];
    else
      $test_vref = NULL;

   if (isset($row['Tester']))
      $tester = $row['Tester'];
    else
      $tester = "Unknown";

   if (isset($row['Test_date']))
      $test_date = $row['Test_date'];
    else
      $test_date = "";
      
   if (isset($row['Test_details']))
      $test_details = $row['Test_details'];
    else
      $test_details = "";
}


$glue_parm_values = array_combine($glue_parameter_names, $glue_parm_values);
$glue_humid = $glue_parm_values["Glue_humid"];
$glue_temp = $glue_parm_values["Glue_temp"];
$glue_radon = $glue_parm_values["Glue_radon"];

$wb_parm_values = array_combine($wb_parameter_names, $wb_parm_values);
$wb_humid = $wb_parm_values["Wb_humid"];
$wb_temp = $wb_parm_values["Wb_temp"];
$wb_radon = $wb_parm_values["Wb_radon"];
$wb_power = $wb_parm_values["Wb_power"];
$wb_time = $wb_parm_values["Wb_time"];

$testing_noise_values = array_combine($testing_noise_names, $testing_noise_values);
$noise_u1 = $testing_noise_values["Noise_U1"];
$noise_l1 = $testing_noise_values["Noise_L1"];
$noise_u2 = $testing_noise_values["Noise_U2"];
$noise_l2 = $testing_noise_values["Noise_L2"];

$testing_resolution_values = array_combine($testing_resolution_names, $testing_resolution_values);
$resolution_u1 = $testing_resolution_values["Resolution_U1"];
$resolution_l1 = $testing_resolution_values["Resolution_L1"];
$resolution_u2 = $testing_resolution_values["Resolution_U2"];
$resolution_l2 = $testing_resolution_values["Resolution_L2"];

$testing_gain_values = array_combine($testing_gain_names, $testing_gain_values);
$gain_u1 = $testing_gain_values["Gain_U1"];
$gain_l1 = $testing_gain_values["Gain_L1"];
$gain_u2 = $testing_gain_values["Gain_U2"];
$gain_l2 = $testing_gain_values["Gain_L2"];

$testing_dark_current_values = array_combine($testing_dark_current_names, $testing_dark_current_values);
$dark_current_u1 = $testing_dark_current_values["Dark_current_U1"];
$dark_current_l1 = $testing_dark_current_values["Dark_current_L1"];
$dark_current_u2 = $testing_dark_current_values["Dark_current_U2"];
$dark_current_l2 = $testing_dark_current_values["Dark_current_L2"];

?>
