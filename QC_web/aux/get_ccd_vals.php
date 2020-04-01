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
$testing_parm_values = array();

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
   
    foreach ($testing_parameter_names as $testing_parm_name)
      {
	if (isset($row[$testing_parm_name]))
	  $testing_parm_values[] = (double)$row[$testing_parm_name];
	else
	  $testing_parm_values[] = NULL;
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

    if (isset($row['Delivery_date']))
      $delivery = $row['Delivery_date'];
    else
      $delivery = "";

    if (isset($row['Wafer_ID']))
      $wafer_id = $row['Wafer_ID'];
    else
      $wafer_id = "0";

    if (isset($row['Production_date']))
      $production_date = $row['Production_date'];
    else
      $production_date = "Unknown";
      
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
      $defects = 0;

    if (isset($row['Defects_detail']))
      $defects_detail = $row['Defects_detail'];
    else
      $defects_detail = "";
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

$testing_parm_values = array_combine($testing_parameter_names, $testing_parm_values);
$dark_current = $testing_parm_values["Dark_current"];
$resolution = $testing_parm_values["Resolution"];
$eff_resistivity = $testing_parm_values["Eff_resistivity"];
?>
