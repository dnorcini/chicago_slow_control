<?php
  // edit_ccd.php
  // D.Norcini, UChicago, 2020
  //

session_start();
$req_priv = "full";
include("db_login.php");
include("page_setup.php");

$table = "CCD";

if (!empty($_SESSION['req_id']))
  $_SESSION['choosen_ccd'] = $_SESSION['req_id'];
  unset($_SESSION['req_id']); //need to get rid of this var or screws everything up!

if (!empty($_POST['choosen']))
  $_SESSION['choosen_ccd'] = $_POST['choosen'];
  
if (empty($_SESSION['choosen_ccd']))
  $_SESSION['choosen_ccd'] = 1;
 
//////////////////////////////////// New entry:

if (isset($_POST['new']))
{
  
  $query = "INSERT into `".$table."` (`ID`, `Last_update`) VALUES (NULL, '".time()."')";
  $result = mysql_query($query);
  if (!$result)
    die ("Could not query the database <br />" . mysql_error());
  
  include("aux/get_last_table_id.php");
  $_SESSION['choosen_ccd'] = $last_id;
}

////////////////////////////   Goto specific entry:

if (isset($_POST['first']))
  $_SESSION['choosen_ccd'] = 1;

if (isset($_POST['last']))
{
  include("aux/get_last_ccd_id.php");
  $_SESSION['choosen_ccd'] = $last_id;
}
if (isset($_POST['prev']))
  $_SESSION['choosen_ccd'] -= 1;
  
if (isset($_POST['next']))
  $_SESSION['choosen_ccd'] += 1;

include("aux/get_last_ccd_id.php");
if ($_SESSION['choosen_ccd'] < 1)
  $_SESSION['choosen_ccd'] = 1;

if ($_SESSION['choosen_ccd'] > $last_id)
  $_SESSION['choosen_ccd'] =  $last_id;


$id = (int)$_SESSION['choosen_ccd'];

////////////////////////////////////    Do table updates :

if (isset($_POST['id']))
  {
  
    $ccd_id = (int)$_POST['id'];

   // clean up strings:
    $_POST['ccd_type'] = trim($_POST['ccd_type']);
    if (!get_magic_quotes_gpc())
      $_POST['ccd_type'] = addslashes($_POST['ccd_type']);

    $_POST['name'] = trim($_POST['name']);
    if (!get_magic_quotes_gpc())
      $_POST['name'] = addslashes($_POST['name']);

    $_POST['size'] = trim($_POST['size']);
    if (!get_magic_quotes_gpc())
      $_POST['size'] = addslashes($_POST['size']);

    $_POST['status'] = trim($_POST['status']);
    if (!get_magic_quotes_gpc())
      $_POST['status'] = addslashes($_POST['status']);

    $_POST['packager'] = trim($_POST['packager']);
    if (!get_magic_quotes_gpc())
      $_POST['packager'] = addslashes($_POST['packager']);
      
    $_POST['location'] = trim($_POST['location']);
    if (!get_magic_quotes_gpc())
      $_POST['location'] = addslashes($_POST['location']);

    $_POST['packaging'] = trim($_POST['packaging']);
    if (!get_magic_quotes_gpc())
      $_POST['packaging'] = addslashes($_POST['packaging']);

    $_POST['wafer_id'] = trim($_POST['wafer_id']);
    if (!get_magic_quotes_gpc())
      $_POST['wafer_id'] = addslashes($_POST['wafer_id']);

    $_POST['wafer_position'] = trim($_POST['wafer_position']);
    if (!get_magic_quotes_gpc())
      $_POST['wafer_position'] = addslashes($_POST['wafer_position']);
      
    $_POST['production_date'] = trim($_POST['production_date']);
    if (!get_magic_quotes_gpc())
      $_POST['production_date'] = addslashes($_POST['production_date']);
    
    $_POST['note'] = trim($_POST['note']);
    if (!get_magic_quotes_gpc())
      $_POST['note'] = addslashes($_POST['note']);

    $_POST['cable_np'] = trim($_POST['cable_np']);
    if (!get_magic_quotes_gpc())
      $_POST['cable_np'] = addslashes($_POST['cable_np']);

    $_POST['jfet_u1'] = trim($_POST['jfet_u1']);
    if (!get_magic_quotes_gpc())
      $_POST['jfet_u1'] = addslashes($_POST['jfet_u1']);

    $_POST['jfet_l1'] = trim($_POST['jfet_l1']);
    if (!get_magic_quotes_gpc())
      $_POST['jfet_l1'] = addslashes($_POST['jfet_l1']);

    $_POST['jfet_u2'] = trim($_POST['jfet_u2']);
    if (!get_magic_quotes_gpc())
      $_POST['jfet_u2'] = addslashes($_POST['jfet_u2']);

    $_POST['jfet_l2'] = trim($_POST['jfet_l2']);
    if (!get_magic_quotes_gpc())
      $_POST['jfet_l2'] = addslashes($_POST['jfet_l2']);

    $_POST['gluing_details'] = trim($_POST['gluing_details']);
    if (!get_magic_quotes_gpc())
      $_POST['gluing_details'] = addslashes($_POST['gluing_details']);

    $_POST['wirebonding_details'] = trim($_POST['wirebonding_details']);
    if (!get_magic_quotes_gpc())
      $_POST['wirebonding_details'] = addslashes($_POST['wirebonding_details']);
      
    $_POST['amp_u1'] = trim($_POST['amp_u1']);
    if (!get_magic_quotes_gpc())
      $_POST['amp_u1'] = addslashes($_POST['amp_u1']);

    $_POST['amp_l1'] = trim($_POST['amp_l1']);
    if (!get_magic_quotes_gpc())
      $_POST['amp_l1'] = addslashes($_POST['amp_l1']);

    $_POST['amp_u2'] = trim($_POST['amp_u2']);
    if (!get_magic_quotes_gpc())
      $_POST['amp_u2'] = addslashes($_POST['amp_u2']);

    $_POST['amp_l2'] = trim($_POST['amp_l2']);
    if (!get_magic_quotes_gpc())
      $_POST['amp_l2'] = addslashes($_POST['amp_l2']);

    $_POST['defects'] = trim($_POST['defects']);
    if (!get_magic_quotes_gpc())
      $_POST['defects'] = addslashes($_POST['defects']);

    $_POST['eff_resistivity'] = trim($_POST['eff_resistivity']);
    if (!get_magic_quotes_gpc())
      $_POST['eff_resistivity'] = addslashes($_POST['eff_resistivity']);

    $_POST['test_temp'] = trim($_POST['test_temp']);
    if (!get_magic_quotes_gpc())
      $_POST['test_temp'] = addslashes($_POST['test_temp']);

    $_POST['test_vref'] = trim($_POST['test_vref']);
    if (!get_magic_quotes_gpc())
      $_POST['test_vref'] = addslashes($_POST['test_vref']);
      
    $_POST['tester'] = trim($_POST['tester']);
    if (!get_magic_quotes_gpc())
      $_POST['tester'] = addslashes($_POST['tester']);

    $_POST['test_date'] = trim($_POST['test_date']);
    if (!get_magic_quotes_gpc())
      $_POST['test_date'] = addslashes($_POST['test_date']);

    $_POST['test_details'] = trim($_POST['test_details']);
    if (!get_magic_quotes_gpc())
      $_POST['test_details'] = addslashes($_POST['test_details']);
      
    $query = "UPDATE `CCD` SET `CCD_Type`=\"".$_POST['ccd_type']."\",`Name`=\"".$_POST['name']."\", `Size`=\"".$_POST['size']."\",`Status`=\"".$_POST['status']."\",  `Packager`=\"".$_POST['packager']."\", `Location`=\"".$_POST['location']."\", `Packaging_date`=\"".$_POST['packaging']."\", `Wafer_ID`=\"".$_POST['wafer_id']."\", `Wafer_position`=\"".$_POST['wafer_position']."\", `Production_date`=\"".$_POST['production_date']."\", `Note`=\"".$_POST['note']."\", `Cable_np`=\"".$_POST['cable_np']."\", `JFET_U1`=\"".$_POST['jfet_u1']."\", `AMP_U1`=\"".$_POST['amp_u1']."\", `AMP_L1`=\"".$_POST['amp_l1']."\", `JFET_l1`=\"".$_POST['jfet_l1']."\", `JFET_U2`=\"".$_POST['jfet_u2']."\", `JFET_L2`=\"".$_POST['jfet_l2']."\", `Gluing_details`=\"".$_POST['gluing_details']."\", `wirebonding_details`=\"".$_POST['wirebonding_details']."\", `AMP_U2`=\"".$_POST['amp_u2']."\", `AMP_L2`=\"".$_POST['amp_l2']."\", `Defects`=\"".$_POST['defects']."\", `Eff_resistivity`=\"".$_POST['eff_resistivity']."\", `Test_temp`=\"".$_POST['test_temp']."\", `Test_vref`=\"".$_POST['test_vref']."\", `Tester`=\"".$_POST['tester']."\",`Test_date`=\"".$_POST['test_date']."\", `Test_details`=\"".$_POST['test_details']."\", `Last_update`=".time()." WHERE `ID` = ".$ccd_id;
    
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());

    foreach ($glue_parameter_names as $glue_parm_name)
      {
    	$update_val = (float)$_POST[$glue_parm_name];
	
    	$query = "UPDATE `CCD` SET `".$glue_parm_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;

    	$result = mysql_query($query);
    	if (!$result)
    	  die ("Could not query the database <br />" . mysql_error());
      }

    foreach ($wb_parameter_names as $wb_parm_name)
      {
    	$update_val = (float)$_POST[$wb_parm_name];
	
    	$query = "UPDATE `CCD` SET `".$wb_parm_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;

    	$result = mysql_query($query);
    	if (!$result)
    	  die ("Could not query the database <br />" . mysql_error());
      }
	
    foreach ($testing_noise_names as $testing_noise_name)
      {
	$update_val = (float)$_POST[$testing_noise_name];
	
	$query = "UPDATE `CCD` SET `".$testing_noise_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
      }

    foreach ($testing_resolution_names as $testing_resolution_name)
      {
	$update_val = (float)$_POST[$testing_resolution_name];
	
	$query = "UPDATE `CCD` SET `".$testing_resolution_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
      }

    foreach ($testing_gain_names as $testing_gain_name)
      {
	$update_val = (float)$_POST[$testing_gain_name];
	
	$query = "UPDATE `CCD` SET `".$testing_gain_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
      }

    foreach ($testing_dark_current_names as $testing_dark_current_name)
      {
	$update_val = (float)$_POST[$testing_dark_current_name];
	
	$query = "UPDATE `CCD` SET `".$testing_dark_current_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
      }
  }

/////////////  Get selected ccd values:

include("aux/get_ccd_vals.php");

mysql_close($connection);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TR>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="new" value="New CCD" title="Generate a new CCD entry" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('&#160 &#160 &#160 &#160 Choose CCD ID: <input type="text" name="choosen" size = 6>');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="first" value="Goto First CCD" title="Go to the first added CCD" style="font-size: 10pt">');
echo ('<input type="submit" name="prev"  value="Goto Previous CCD" title="Go to the CCD before the currently selected one" style="font-size: 10pt">');
echo ('<input type="submit" name="next"  value="Goto Next CCD" title="Go to the CCD after the currently selected one" style="font-size: 10pt">');
echo ('<input type="submit" name="last"  value="Goto Last CCD" title="Go to the last added CCD" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');

echo ('</TR>');
echo ('</TABLE>');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ('<BR>');
echo ('<BR>');

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');

echo ('<TR>');                
echo ('<TD align="left" colspan = 1>');
echo ('CCD ID:  '.$id);
echo ('</TD>');

echo ('<TD align="left" colspan = 1>');
echo ('Name: <input type="text" name="name" value="'.$name.'" size = 10>');

echo ('<TD align="left" colspan=1>');
echo ('Entry Last updated:');
echo (date("G:i:s  M d, Y", $last_update));
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan = 1>');
echo ('CCD Format: ');
echo ('<SELECT name="ccd_type">');
foreach ($ccd_type_array as $index)
{
  echo('<option ');
  if (strcmp($index, $ccd_type)==0)
    echo ('selected');
  echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo ('</TD>');

echo ('<TD align="left" colspan = 1>');
echo ('Size: ');
echo ('<SELECT name="size">');
foreach ($ccd_size_array as $index)
{
  echo('<option ');
  if (strcmp($index, $size)==0)
    echo ('selected');
  echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo ('</TD>');

echo ('<TD align="left"  colspan = 1>');
echo ('Status: ');
echo ('<SELECT name="status">');
foreach ($ccd_qc_status_array as $index)
{
  echo('<option ');
  if (strcmp($index, $status)==0)
    echo ('selected');
  echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 3>');
echo ('Current location: ');
echo ('<SELECT name="location">');
foreach ($item_location_array as $index)
{
  echo('<option ');
  if (strcmp($index, $location)==0)
    echo ('selected');
  echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</TR>');
echo ('</TABLE>');
echo ('<BR>');
echo ('<BR>');

/////////////////////////////////////////////////////////////
echo('<strong> Origin </strong>');
echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Wafer ID: <input type="text" name="wafer_id" value="'.$wafer_id.'" size = 10>');
echo ('</TD>');
echo ('<TD align="left"  colspan = 1>');
echo ('CCD wafer position: <input type="text" name="wafer_position" value="'.$wafer_position.'" size = 10>');
echo ('</TD>');
echo ('<TD align="left"  colspan = 1>');
echo ('CCD production date (YYYY/MM): <input type="text" name="production_date" value="'.$production_date.'" size = 10>');
echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');
echo ('<BR>');
echo ('<BR>');

/////////////////////////////////////////////////////////////
echo('<strong> Packaging </strong>');
echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo('</TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Cable connections: ');
echo ('</TD>');
echo ('<TD align="left"  colspan = 1>');
echo ('<input type="hidden" name="cable_np" value="0">');
echo ('<input type="checkbox" name="cable_np"'); 
if ($cable_np)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' n+ p+');
echo ('</TD>');

echo ('<TD align="left"  colspan = 1>');
echo ('<input type="hidden" name="jfet_u1" value="0">');
echo ('<input type="checkbox" name="jfet_u1"');
if ($jfet_u1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' JFET U1');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="jfet_l1" value="0">');
echo ('<input type="checkbox" name="jfet_l1"');
if ($jfet_l1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' JFET L1');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="jfet_u2" value="0">');
echo ('<input type="checkbox" name="jfet_u2"');
if ($jfet_u2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' JFET U2');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="jfet_l2" value="0">');
echo ('<input type="checkbox" name="jfet_l2"');
if ($jfet_l2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' JFET L2');
echo ('</TD>');
echo ('</TR>');

echo('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Gluing conditions: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
foreach ($glue_parameter_names as $glue_parm_name)
{
  echo ($glue_parameter_title[$glue_parm_name].' = <input type="text" name="'.$glue_parm_name.'" value="'.$glue_parm_values[$glue_parm_name].'" size = 8> ('.$glue_parameter_units[$glue_parm_name].')');
  echo ('&nbsp &nbsp &nbsp');	
}
echo ('</TD>');
echo ('</TR>');   

echo('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Gluing details: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
echo ('Elog: <input type="url" name="gluing_details" value="'.$gluing_details.'" size=120>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Wirebonding conditions: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
foreach ($wb_parameter_names as $wb_parm_name)
{
  echo ($wb_parameter_title[$wb_parm_name].' = <input type="text" name="'.$wb_parm_name.'" value="'.$wb_parm_values[$wb_parm_name].'" size = 8> ('.$wb_parameter_units[$wb_parm_name].')');
  echo ('&nbsp &nbsp &nbsp');	
}
echo ('</TD>');
echo ('</TR>');

echo('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Wirebonding details: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
echo ('Elog: <input type="url" name="wirebonding_details" value="'.$wirebonding_details.'" size=120>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 2>');
echo ('Packaged by: ');
echo ('<SELECT name="packager">');
foreach ($item_packager_array as $index)
{
  echo('<option ');
  if (strcmp($index, $packager)==0)
    echo ('selected');
    echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo('</TD>');
echo ('<TD align="left"  colspan = 2>');
echo ('Packaging Date (MM/YYYY): <input type="text" name="packaging" value="'.$packaging.'" size = 10>');
echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');
echo ('<BR>');
echo ('<BR>');

/////////////////////////////////////////////////////////////
echo('<strong> Testing </strong>');
echo ('<TABLE border="1" cellpadding="2" width=100%>');

echo('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Testing conditions: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
echo ('Temperature (K): <input type="text" name="test_temp" value="'.$test_temp.'">');
echo ('</TD>');
echo ('<TD align="left" colspan = 2>');
echo ('Vref (V): <input type="text" name="test_vref" value="'.$test_vref.'">');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 5>');
echo ('Defects (row, col, details; row, col, details; ...): <input type="text" name="defects" value="'.$defects.'" size = 90>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Effective resistivity (kOhm-cm): <input type="text" name="eff_resistivity" value="'.$eff_resistivity.'">');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Amplifier details:');
echo ('</TD>');
echo ('<TD align="center">');
echo ('U1 (CCD C)');
echo ('</TD>');
echo ('<TD align="center">');
echo ('L1 (CCD A)');
echo ('</TD>');
echo ('<TD align="center">');
echo ('U2 (CCD D)');
echo ('</TD>');
echo ('<TD align="center">');
echo ('L2 (CCD B)');
echo ('</TD>');
echo ('</TR>');


echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Charge?:');
echo ('</TD>');
echo ('<TD align="center"  colspan = 1>');
echo ('<input type="hidden" name="amp_u1" value="0">');
echo ('<input type="checkbox" name="amp_u1"');
if ($amp_u1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
//echo (' U1');
echo ('</TD>');
//echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<TD align="center" colspan=1>');
echo ('<input type="hidden" name="amp_l1" value="0">');
echo ('<input type="checkbox" name="amp_l1"');
if ($amp_l1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
//echo (' L1');
echo ('</TD>');
//echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<TD align="center" colspan=1>');
echo ('<input type="hidden" name="amp_u2" value="0">');
echo ('<input type="checkbox" name="amp_u2"');
if ($amp_u2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
//echo (' U2');
echo ('</TD>');
//echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<TD align="center" colspan=1>');
echo ('<input type="hidden" name="amp_l2" value="0">');
echo ('<input type="checkbox" name="amp_l2"');
if ($amp_l2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
//echo (' L2');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Noise @ 1 skip (ADU):');
echo ('</TD>');
foreach ($testing_noise_names as $testing_noise_name)
{
  echo ('<TD align="center" colspan = 1>');
  echo ('<input type="text" name="'.$testing_noise_name.'" value="'.$testing_noise_values[$testing_noise_name].'">');
  echo ('</TD>');
}
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Resolution @ 1000 skips (e-):');
echo ('</TD>');
foreach ($testing_resolution_names as $testing_resolution_name)
{
  echo ('<TD align="center" colspan = 1>');
  echo ('<input type="text" name="'.$testing_resolution_name.'" value="'.$testing_resolution_values[$testing_resolution_name].'">');
  echo ('</TD>');
}
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Gain (ADU/e-):');
echo ('</TD>');
foreach ($testing_gain_names as $testing_gain_name)
{
  echo ('<TD align="center" colspan = 1>');
  echo ('<input type="text" name="'.$testing_gain_name.'" value="'.$testing_gain_values[$testing_gain_name].'">');
  echo ('</TD>');
}
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Dark current (e-/pix/day):');
echo ('</TD>');
foreach ($testing_dark_current_names as $testing_dark_current_name)
{
  echo ('<TD align="center" colspan = 1>');
  echo ('<input type="text" name="'.$testing_dark_current_name.'" value="'.$testing_dark_current_values[$testing_dark_current_name].'">');
  echo ('</TD>');
}
echo ('</TR>');

//foreach ($testing_parameter_names as $testing_parm_name)
//{
  //echo ('<TD align="left" colspan = 1>');
  //echo ($testing_parameter_title[$testing_parm_name].' = <input type="text" name="'.$testing_parm_name.'" value="'.$testing_parm_values[$testing_parm_name].'" size = 8> ('.$testing_parameter_units[$testing_parm_name].')');
  //echo ('</TD>');
  //echo ('</TR>');   	
//}

echo('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Testing details: ');
echo ('</TD>');
echo ('<TD align="left" colspan = 4>');
echo ('Elog: <input type="url" name="test_details" value="'.$test_details.'" size=120>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align="left"  colspan = 2>');
echo ('Tested by: ');
echo ('<SELECT name="tester">');
foreach ($item_location_array as $index)
{
  echo('<option ');
  if (strcmp($index, $tester)==0)
    echo ('selected');
    echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo('</TD>');
echo ('<TD align="left"  colspan = 2>');
echo ('Test Date (YYYY/MM): <input type="text" name="test_date" value="'.$test_date.'" size = 10>');
echo ('</TD>');
echo ('</TR>');

echo('</TABLE>');
echo ('<BR>');
echo ('<BR>');

/////////////////////////////////////////////////////////////
// file upload and download
//include("file_upload_download/file_logic.php");

//echo('<strong> Files upload and download </strong>');
//echo ('<BR>');
//echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">');
//echo ('Upload config file (.ini)');
//echo ('<input type="file" name="myfile">');
//echo ('<input type="submit" name="upload">');
//echo ('</FORM>');

/////////////////////////////////////////////////////////////
echo ('<TABLE border="1" cellpadding="2" width=100%>'); 	    	    
echo ('<TR>');                  
echo ('<TD align="left" colspan=3>');
echo ('<strong> Notes </strong> <i>(YYYY/MM/DD initials:)</i>:');
echo ('<BR>');
echo ('<TEXTAREA name="note" rows="8" cols="75">');
echo ($note);
echo ('</TEXTAREA>');
echo ('<BR>');
echo ('</TD>');
echo ('</TR>');
echo ('<TR>');                   
echo ('<TD align="center" colspan=4>');
echo ('<input type="hidden" name="id" value="'.$id.'">');
echo ('<input type="submit" name="void" value="submit">');
echo ('</TD>');
echo ('</TR>');

echo ('</FORM>');
echo ('</TABLE>');

echo ('</body>');
echo ('</HTML>');
?>
