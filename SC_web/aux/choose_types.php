<?php
// choose_types.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010, 2011
// james.nikkel@yale.edu
//


if (empty($_SESSION['choose_type']))
  $_SESSION['choose_type'] = array($sens_type_array_index[0]);

if (!(empty($_POST['s_all'])))             // is the select all button has been pressed do this:
  $_SESSION['choose_type']=  $sens_type_array_index;

if (!(empty($_POST['ds_all'])))     // deselect all types
  $_SESSION['choose_type']= array();

if (!(empty($_POST['r_all'])))     // roll up all plot windows
  $_SESSION['s_roll_up'] = array_combine($sensor_names, make_new_ones_array(count($sensor_names)));

if (!(empty($_POST['ur_all'])))     // unroll all plot windows
  $_SESSION['s_roll_up'] = array_combine($sensor_names, make_new_zero_array(count($sensor_names)));

if (!(empty($_POST['choose_type_P'])))
  $_SESSION['choose_type'] = $_POST['choose_type_P'];


echo ('<TABLE border="1" cellpadding="2" width=100%>');

if (!(isset($need_roller)))
    $need_roller = 0;

if ($need_roller)
  {
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<TH>');
    echo ('<input type="submit" name="ur_all" value="Unroll All" title="Unroll all the plots listed below" style="font-size: 10pt">');
    echo ('</TH>');
    echo ('</FORM>');
  }
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="s_all" value="Select All" title="Select all categories listed to the right" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
foreach  ($sens_type_array_index as $index)
{
    foreach ($sensor_names as $sensor_name)
	if (in_array($index, explode(",", $sensor_types[$sensor_name])))
	    if ($sensor_al_trip[$sensor_name])
		echo ('<font color="red">');
    echo ($sens_type_array[$index].':<input type="checkbox" name="choose_type_P[]" value="'.$index.'" ');
    if (in_array($index, $_SESSION['choose_type']))
	echo ('checked="checked" />  &#160 &#160; ');
    else 
	echo ('/>  &#160 &#160');
    echo ('</font>');
}
echo ('<input type="image" src="pixmaps/edit.png" alt="Change" title="Refresh page with selected sensor types">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="ds_all" value="Deselect All" title="Deselect all categories listed to the left" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');
if ($need_roller)
{
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<TH>');
    echo ('<input type="submit" name="r_all" value="Rollup All" title="Roll up all the plots shown below" style="font-size: 10pt">');
    echo ('</TH>');
    echo ('</FORM>');
  }

echo ('</TABLE>');
?>
