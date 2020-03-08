<?php
  // edit_ccd.php
  // D.Norcini, UChicago, 2020
  //

session_start();
$req_priv = "full";
include("db_login.php");
include("page_setup.php");

$table = "CCD";

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
  
     $_POST['location'] = trim($_POST['location']);
    if (!get_magic_quotes_gpc())
      $_POST['location'] = addslashes($_POST['location']);
    
    $_POST['note'] = trim($_POST['note']);
    if (!get_magic_quotes_gpc())
      $_POST['note'] = addslashes($_POST['note']);  

    $query = "UPDATE `CCD` SET `CCD_Type`=\"".$_POST['ccd_type']."\",`Name`=\"".$_POST['name']."\", `Size`=\"".$_POST['size']."\",`Status`=\"".$_POST['status']."\",  `Location`=\"".$_POST['location']."\", 
            `Note`=\"".$_POST['note']."\", `Last_update`=".time()."  
            WHERE `ID` = ".$ccd_id;

    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());

  
    foreach ($ccd_parameter_names as $parm_name)
      {
	$update_val = (float)$_POST[$parm_name];
	
	$query = "UPDATE `CCD` SET `".$parm_name."`=\"".$update_val."\" WHERE `ID` = ".$ccd_id;	

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
      }
  }



/////////////  Get selected housing values:

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

echo ('<TR>');
echo ('<TD align="left" colspan = 1>');
echo ('Name: <input type="text" name="name" value="'.$name.'" size = 10>');
echo ('</TD>');

echo ('<TD align="left" colspan = 1>');
echo ('CCD Type: ');
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

echo ('<TD align="left" colspan=1>');
echo ('Entry Last updated:');
echo ('<BR>');
echo (date("G:i:s  M d, y", $last_update));
echo ('</TD>');
echo ('</TR>');
/////////////////////////////////////////////////////////////

foreach ($ccd_parameter_names as $parm_name)
{
  echo ('<TR>');               
  echo ('<TD align="left" colspan = 4>');
  echo ($parm_name.' = <input type="text" name="'.$parm_name.'" value="'.$parm_values[$parm_name].'" size = 8> ('.$ccd_parameter_units[$parm_name].')');
  echo ('</TD>');
  echo ('</TR>');   	
}

/////////////////////////////////////////////////////////////
 	    	    
echo ('<TR>');                
echo ('<TD align="left"  colspan = 2>');
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

echo ('<TD align="left"  colspan = 2>');
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
echo ('</TR>');    

echo ('<TR>');                  
echo ('<TD align="left" colspan=4>');
echo ('Notes:');
echo ('<BR>');
echo ('<TEXTAREA name="note" rows="4" cols="60">');
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
