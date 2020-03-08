<?php
  // slow_control_text.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006.
  // james.nikkel@yale.edu
  //
session_start();
$req_priv = "basic";
include("db_login.php");
include("slow_control_page_setup.php");

///  Get all the sensor information from the database so that we can 
///  define what we want to plot.
include("aux/get_sensor_info.php"); 

///  The idea here is to create a list of unique sensor types from all the available sensors.
///  This allows us to just view one type at a time on the screen.
include("aux/choose_types.php");    // sets session variable $choose_type from the check boxes.

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
if (!(empty($_POST['num_val_avgs'])))
    $_SESSION['num_val_avgs'] = $_POST['num_val_avgs'];

include("aux/get_sensor_vals.php");
include("aux/get_time_max_min.php"); 
mysql_close($connection);

include("aux/show_last_update_time.php");
////   This next section generate the HTML with the plot names in a table.

$cur_time = time();
echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');

$temp_types = $sensor_types;
$my_sensor_names = array();
foreach ($_SESSION['choose_type'] as $c_index)
{
    foreach ($sensor_names as $sensor_name)
	if (in_array($c_index, explode(",", $temp_types[$sensor_name])))
	{
	    $my_sensor_names[] = $sensor_name;
	    $temp_types[$sensor_name] = "";
	}
}

foreach ($my_sensor_names as $sensor_name)
{
    if ($sensor_settable[$sensor_name] == 0)
    {                                                                      // don't show settable or hidden sensors in this table
	echo ('<TH>');
	if ($sensor_al_trip[$sensor_name])
	    echo ('<font color="red">');
	echo ($sensor_descs[$sensor_name]);
	if ($_SESSION['show_sens_name'])
	    echo (" (".$sensor_name.")");
	echo ('<br>');
	echo ('<font size="+4">');
	if (time()-$sensor_times[$sensor_name] > 600)
	    echo ('<font color="yellow">');
	if  (strncmp($sensor_units[$sensor_name], "discrete", 8) == 0)    // discrete value sensors
	{	
	    echo (get_disc_units_val($sensor_values[$sensor_name], $sensor_discrete_vals[$sensor_name]));
	    echo ('</font>');
	    echo ('<br>');	
	}
	else
	{
	    echo (format_num2($sensor_values[$sensor_name], $sensor_num_format[$sensor_name]));
	    echo ('</font></font>');
	    echo ('<br>');
	    echo ('('.$sensor_units[$sensor_name].')');
	}
	echo ('</TH>');
	if ($i % 3 ==0)
	{
	    echo ('</TR>');
	    echo ('<TR>');
	}
	$i++;
    }
    if (($sensor_settable[$sensor_name] == 0) && ($sensor_show_rate[$sensor_name] == 1)) //rates
    {                                                                      // don't show settable or hidden sensors in this table
	echo ('<TH>');
	if ($sensor_al_trip[$sensor_name])
	    echo ('<font color="red">');
	echo ("Rate of ".$sensor_descs[$sensor_name]);
	if ($_SESSION['show_sens_name'])
	    echo (" (".$sensor_name.")");
	echo ('<br>');
	echo ('<font size="+4">');
	echo (format_num2($sensor_rates[$sensor_name], $sensor_num_format[$sensor_name]));
	echo ('</font>');
	echo ('<br>');
	echo ('('.$sensor_units[$sensor_name].'/s)');
	echo ('</TH>');
	if ($i % 3 ==0)
	{
	    echo ('</TR>');
	    echo ('<TR>');
	}
	$i++;	    
    }
}
echo ('</TR>');
echo ('</TABLE>');


$num_val_avgs_array = array(
    "1" => 1,
    "3" => 3,
    "5" => 5,
    "10" => 10,
    "20" => 20
    );

echo ('<TABLE border="0" cellpadding="1" cellspacing="2" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD align=left>');
echo ('Number of values to average: <select name="num_val_avgs"> ');
foreach ($num_val_avgs_array as $st_s => $st_v)
{
    echo('<option ');
    if ($st_v == $_SESSION['num_val_avgs'])
    {
	echo ('selected="selected"');
    }
    echo(' value="'.$st_v.'" >'.$st_s.'</option> ');
}
echo ('</select>');
//echo ('</TD>');
//echo ('<TD align=center>');
echo ('<input type="image" src="pixmaps/reload.png" value="Change" alt="Refresh" title="Refresh page with new number of values">');
echo ('</TD>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD align=right>');
echo ('Show raw sensor names with descriptions: ');
echo('<input type="hidden" name="show_sens_name" value="1">');
if ($_SESSION['show_sens_name'] == 1)
    echo ('<input type="image" src="pixmaps/checked.png" alt="Click to hide raw sensor names" title="Click to hide raw sensor names">');
else
    echo ('<input type="image" src="pixmaps/unchecked.png" alt="Click to show raw sensor names" title="Click to show raw sensor names">');
echo ('</TD>');
echo ('</FORM>');

echo ('</TABLE>');

echo(' </body>');
echo ('</HTML>');
?>
