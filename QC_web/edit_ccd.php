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

    $_POST['delivery'] = trim($_POST['delivery']);
    if (!get_magic_quotes_gpc())
      $_POST['delivery'] = addslashes($_POST['delivery']);

    $_POST['wafer_id'] = trim($_POST['wafer_id']);
    if (!get_magic_quotes_gpc())
      $_POST['wafer_id'] = addslashes($_POST['wafer_id']);

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

    $_POST['defects_detail'] = trim($_POST['defects_detail']);
    if (!get_magic_quotes_gpc())
      $_POST['defects_detail'] = addslashes($_POST['defects_detail']);
      
    $query = "UPDATE `CCD` SET `CCD_Type`=\"".$_POST['ccd_type']."\",`Name`=\"".$_POST['name']."\", `Size`=\"".$_POST['size']."\",`Status`=\"".$_POST['status']."\",  `Packager`=\"".$_POST['packager']."\", `Location`=\"".$_POST['location']."\", `Delivery_date`=\"".$_POST['delivery']."\", `Wafer_ID`=\"".$_POST['wafer_id']."\",`Production_date`=\"".$_POST['production_date']."\", `Note`=\"".$_POST['note']."\", `Cable_np`=\"".$_POST['cable_np']."\", `JFET_U1`=\"".$_POST['jfet_u1']."\", `AMP_U1`=\"".$_POST['amp_u1']."\", `AMP_L1`=\"".$_POST['amp_l1']."\", `JFET_l1`=\"".$_POST['jfet_l1']."\", `JFET_U2`=\"".$_POST['jfet_u2']."\", `JFET_L2`=\"".$_POST['jfet_l2']."\", `AMP_U2`=\"".$_POST['amp_u2']."\", `AMP_L2`=\"".$_POST['amp_l2']."\", `Defects`=\"".$_POST['defects']."\", `Defects_detail`=\"".$_POST['defects_detail']."\", `Last_update`=".time()." WHERE `ID` = ".$ccd_id;

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
	
    foreach ($testing_parameter_names as $testing_parm_name)
      {
	$update_val = (float)$_POST[$testing_parm_name];
	
	$query = "UPDATE `CCD` SET `".$testing_parm_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

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
echo ('Production Date (MM/YYYY): <input type="text" name="production_date" value="'.$production_date.'" size = 10>');
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

echo ('<TR>');
echo ('<TD align="left"  colspan = 1>');
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
echo ('<TD align="left"  colspan = 1>');
echo ('Current location: ');
echo ('<SELECT name="location">');
foreach ($item_location_array as $index)
{
  echo('<option ');
  if (strcmp($index, $location)==0)
    echo ('selected');
  echo(' value="'.$index.'">  '.$index.'  </option>');
}
echo ('</SELECT>');
echo ('</TD>');
echo ('<TD align="left"  colspan = 1>');
echo ('Delivery Date (MM/YYYY): <input type="text" name="delivery" value="'.$delivery.'" size = 10>');
echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');
echo ('<BR>');
echo ('<BR>');

/////////////////////////////////////////////////////////////
echo('<strong> Testing </strong>');
echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TR>');
echo ('<TD align="left"  colspan = 1>');
echo ('Defects: <input type="text" name="defects" value="'.$defects.'" size=50> (row, col; row, col; ...)');
echo ('</TD>');
echo ('<TD align="left"  colspan = 2>');
echo ('Details: <input type="text" name="defects_detail" value="'.$defects_detail.'" size=70>');
echo ('</TD>');
echo ('</TR>'); 
foreach ($testing_parameter_names as $testing_parm_name)
{
  echo ('<TR>');               
  echo ('<TD align="left" colspan = 3>');
  echo ($testing_parameter_title[$testing_parm_name].' = <input type="text" name="'.$testing_parm_name.'" value="'.$testing_parm_values[$testing_parm_name].'" size = 8> ('.$testing_parameter_units[$testing_parm_name].')');
  echo ('</TD>');
  echo ('</TR>');   	
}
echo ('<TR>');
echo ('<TD align="left" colspan=1>');
echo ('Functioning amplifers:');
echo ('<TD align="left"  colspan = 1>');
echo ('<input type="hidden" name="amp_u1" value="0">');
echo ('<input type="checkbox" name="amp_u1"');
if ($amp_u1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' U1');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="amp_l1" value="0">');
echo ('<input type="checkbox" name="amp_l1"');
if ($amp_l1)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' L1');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="amp_u2" value="0">');
echo ('<input type="checkbox" name="amp_u2"');
if ($amp_u2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' U2');

echo ('&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp');
echo ('<input type="hidden" name="amp_l2" value="0">');
echo ('<input type="checkbox" name="amp_l2"');
if ($amp_l2)
   echo ('value="1" checked="checked" />');
else
   echo ('value="1" />');
echo (' L2');
echo ('</TD>');
echo ('</TR>');
echo('</TABLE>');
echo ('<BR>');
echo ('<BR>');
/////////////////////////////////////////////////////////////
echo ('<TABLE border="1" cellpadding="2" width=100%>'); 	    	    
echo ('<TR>');                  
echo ('<TD align="left" colspan=3>');
echo ('<strong> Notes </strong> <i>(MM/DD/YY initials:)</i>:');
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
