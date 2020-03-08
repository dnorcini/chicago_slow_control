<?php
// slow_control_calc.php
// Part of the CLEAN slow control.  
// James Nikkel, 2013.
// james.nikkel@gmail.com
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 
include("aux/make_plot.php");

///  Decide if we want to use the rate or regular value
if (!(isset($_SESSION['use_rate'])))
  $_SESSION['use_rate'] = 0;    /// by default, do not use rate value
if (!(empty($_POST['use_rate'])))   // then toggle if set
{
    if ($_SESSION['use_rate'])
	$_SESSION['use_rate'] = 0;
    else
	$_SESSION['use_rate'] = 1;
}

$click_t = NULL;
if (empty($_SESSION['click_type']))
    $_SESSION['click_type'] = 2;
    
if (!empty($_POST['click_position']))
{
    $_SESSION['click_type'] = $_POST['click_type'];
    $click_x = ($_POST['click_position'][0] - 100.0)/($_SESSION['single_view_x_size'] - 105.0);
    $DX = ($_SESSION['t_max_p'] - $_SESSION['t_min_p']);
   
    if ($_POST['click_type'] == 1)
	$_SESSION['t_min_p'] = intval($_SESSION['t_min_p'] + $DX * $click_x);	
    
    if ($_POST['click_type'] == 2)
    {
	$_SESSION['t_min_p'] = intval($_SESSION['t_min_p'] + $DX * ($click_x - 0.5));
	$_SESSION['t_max_p'] = intval($_SESSION['t_max_p'] + $DX * ($click_x - 0.5));
	$_SESSION['t_max_now'] = -1;
    }
    if ($_POST['click_type'] == 3)    
    {
	$_SESSION['t_max_p'] = intval($_SESSION['t_max_p'] - $DX * (1-$click_x));	
	$_SESSION['t_max_now'] = -1;
    }
    if ($_POST['click_type'] == 4)
    {
	if ($click_x <= 0)
	    $click_t = $_SESSION['t_min_p'];
	else
	    $click_t = intval($click_x * $DX + $_SESSION['t_min_p']);
    }
}


if (!empty($_POST['zoom_in']))
{
    $DX = $_SESSION['t_max_p'] - $_SESSION['t_min_p'];
    $MID_X = ($_SESSION['t_max_p'] + $_SESSION['t_min_p'])/2.0;
    $_SESSION['t_min_p'] = intval($MID_X - $DX/4.0);
    $_SESSION['t_max_p'] = intval($MID_X + $DX/4.0);
    $_SESSION['t_max_now'] = -1;
}

if (!empty($_POST['zoom_out']))
{
    $DX = $_SESSION['t_max_p'] - $_SESSION['t_min_p'];
    $MID_X = ($_SESSION['t_max_p'] + $_SESSION['t_min_p'])/2.0;
    $_SESSION['t_min_p'] = intval($MID_X - $DX);
    $_SESSION['t_max_p'] = intval($MID_X + $DX);
    $_SESSION['t_max_now'] = -1;
}



if (!empty($_POST['single_view_x_size']))
    if (((int)$_POST['single_view_x_size'] > 120) AND ((int)$_POST['single_view_x_size'] < 2000))
	$_SESSION['single_view_x_size'] = (int)$_POST['single_view_x_size'];

if (!empty($_POST['single_view_y_size']))
    if (((int)$_POST['single_view_y_size'] > 120) AND ((int)$_POST['single_view_y_size'] < 2000)) 
        $_SESSION['single_view_y_size'] = (int)$_POST['single_view_y_size'];


if (empty($_SESSION['single_view_x_size']))
{
    $_SESSION['single_view_x_size'] = 800;
    $_SESSION['single_view_y_size'] = 400;
}

///  Set the number of points in the plots to half that of the view size
$num_plot_points = (int)($_SESSION['single_view_x_size']/2);

///  Define max and min times for display by finding the very first and very
///  last entry in the database.  These are stored as session variables
///  t_min and t_max.  We then initialize the plotting limits, t_min_p and t_max_p
include("aux/get_time_max_min.php"); 
include("aux/choose_times.php");

if (!empty($_POST['choosen_sensor']))
{
    $_SESSION['choosen_sensor'] = $_POST['choosen_sensor'];
}
if (empty($_SESSION['choosen_sensor']))
{
    $_SESSION['choosen_sensor'] = $sensor_names[0];
}
$sensor_name = $_SESSION['choosen_sensor'];

if (!empty($_POST['log-y-button']))  
{
    if (in_array($sensor_name, $_POST['log-y-button']))
	if ($_SESSION['s_logxy'][$sensor_name] == 2)
	    $_SESSION['s_logxy'][$sensor_name] = 0;
	else  if ($_SESSION['s_logxy'][$sensor_name] == 1)
	    $_SESSION['s_logxy'][$sensor_name] = 3;
	else if ($_SESSION['s_logxy'][$sensor_name] == 3)
	    $_SESSION['s_logxy'][$sensor_name] = 1;
	else
	    $_SESSION['s_logxy'][$sensor_name] = 2;
}

if ($_SESSION['s_logxy'][$sensor_name] == 0)
    $logxy = "intlin";
else if ($_SESSION['s_logxy'][$sensor_name] == 1)
    $logxy = "loglin";
else if ($_SESSION['s_logxy'][$sensor_name] == 2)
    $logxy = "intlog";
else 
    $logxy = "loglog";


if (strcmp($logxy, "intlog") == 0)
    $query = "SELECT time, value, rate FROM sc_sens_".$sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
	" AND ".$_SESSION['t_max_p']." AND `value` > 0 ORDER BY RAND() LIMIT ".$num_plot_points;
else
    $query = "SELECT time, value, rate FROM sc_sens_".$sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
	" AND ".$_SESSION['t_max_p']." ORDER BY RAND() LIMIT ".$num_plot_points;


$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error());
}


$time = array();
$value = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $time[] = (int)$row['time'];

    if ($_SESSION['use_rate'])
      {
	$value[] = (double)$row['rate'];
	$s_units = $sensor_units[$sensor_name]."/s";
      }
    else 
      {
	$value[] = (double)$row['value'];
	$s_units = $sensor_units[$sensor_name];
      }  
 }

if (count($time) > 1)
{
    array_multisort($time, $value);

    if (!empty($_POST['lin_reg']))
	$y_reg = linear_regression($time, $value);
    else if (!empty($_POST['avg_y']))
      {
	$avg_y = array_sum($value)/count($value);
	$y_reg = array($avg_y, $avg_y, 0, $avg_y);
      }
    else
    	$y_reg = NULL;
    
    $plot_name = "jpgraph_cache/plot_single.png";
    $plot_title = $sensor_descs[$sensor_name]." (".$sensor_name.")";
    
    $plot_y_label = " (".$s_units.")";
   
    make_plot($plot_name, $time, $value, $logxy, $plot_title, $plot_y_label, 
	      $sensor_al_trip[$sensor_name],
	      $_SESSION['single_view_x_size'], $_SESSION['single_view_y_size'], $y_reg, $click_t);
}
else
    echo ('<br><br> &#160 &#160 You might try zooming out a bit. <br>');

mysql_close($connection);

////   This next section generate the HTML with the plot in a table.

echo ('<TABLE border="0" cellpadding="0" frame="box" width=100%>');
echo ('<TR align=center>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD align=left>');
echo ('Use Rate: ');
echo('<input type="hidden" name="use_rate" value="1">');
if ($_SESSION['use_rate'] == 1)
    echo ('<input type="image" src="pixmaps/checked.png" alt="Click to use normal sensor value" title="Click to use normal sensor value">');
else
    echo ('<input type="image" src="pixmaps/unchecked.png" alt="Click to use rate value" title="Click to use rate value">');
echo ('</TD>');
echo ('</FORM>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD>');
echo ('Clicking on the plot: ');
echo ('</TD>');
echo ('<TD>');
echo ('Moves begin time <input type="radio" name="click_type" value="1" '); 
if ($_SESSION['click_type'] == 1)
  echo('CHECKED>');
else 
  echo('>');
echo ('</TD>');
echo ('<TD>');
echo ('Moves central time <input type="radio" name="click_type" value="2" ');
if ($_SESSION['click_type'] == 2)
  echo('CHECKED>');
else 
  echo('>');
echo ('</TD>');
echo ('<TD>');
echo ('Moves end time <input type="radio" name="click_type" value="3" ');
if ($_SESSION['click_type'] == 3)
  echo('CHECKED>');
else 
  echo('>');
echo ('</TD>');
echo ('<TD>');
echo ('Selects value <input type="radio" name="click_type" value="4" ');
if ($_SESSION['click_type'] == 4)
  echo('CHECKED>');
else 
  echo('>');
echo ('</TD>');
echo ('</TR>');
echo ('<TR align=center>');
echo ('<TD colspan=6>');
if (count($time) > 1)
    echo ('<input type="image" src="'.$plot_name.'" name="click_position[]" border=0 align=center width ='.$_SESSION['single_view_x_size'].'>');
echo ('</TD>');
echo ('</TR>');
echo ('</FORM>');

echo ('<TR>');
echo ('<TD colspan=6>');
echo ('<HR>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR align=center>');
echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden"name="zoom_in" value=1">');
echo ('<input type="image" src="pixmaps/zoom_in.png" title="Zoom in">');
echo ('</FORM>');
echo ('</TD>');
echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="zoom_out" value=1>');
echo ('<input type="image" src="pixmaps/zoom_out.png" title="Zoom out">');
echo ('</FORM>');
echo ('</TD>');

echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="log-y-button[]" value='.$sensor_name.'>');
echo ('<input type="image" src="pixmaps/Log_y.png" title="Log">');
echo ('</FORM>');
echo ('</TD>');

echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="int_y" value=1>');
echo ('<input type="image" src="pixmaps/int.png" title="Integrate">');
echo ('</FORM>');
echo ('</TD>');

echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="avg_y" value=1>');
echo ('<input type="image" src="pixmaps/avg.png" title="Average">');
echo ('</FORM>');
echo ('</TD>');

echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="lin_reg" value=1>');
echo ('<input type="image" src="pixmaps/lin_reg.png" title="Linear regression">');
echo ('</FORM>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD colspan=6>');
echo ('<HR>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD>');
echo ('<FORM action="slow_control_export_data.php" method="post">');
echo ('Export data to text file: <input type="submit" src="pixmaps/export.png" name="export" value='.$sensor_name.' title="Export data">');
echo ('</FORM>');
echo ('</TD>');
echo ('</TR>');


echo ('<TR>');
echo ('<TD colspan=6>');
echo ('<HR>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD colspan=6>');

if (!empty($_POST['int_y']))
{
    $int_y = integrate_array($time, $value);
    echo ('<TABLE border="0" cellpadding="2">');
    echo ('<TR>');
    
    echo ('<TH align="left">');
    echo ('Integral over visible region:');
    echo ('</TH>');

    echo ('<TH align="left">');
    echo (format_num($int_y));
    echo ('('.$s_units.'*s)');
    echo ('</TH>');
    echo ('</TR>');
    
    echo ('<TR>');
    echo ('<TD align="right">');
    echo ('(=  &#160');
    echo ('</TD>');

    echo ('<TD align="left">');
    echo (format_num($int_y/60.0));
    echo ('('.$s_units.'*min)');
    echo (' &#160 = &#160 ');
    echo (format_num($int_y/3600.0));
    echo ('('.$s_units.'*hour))');
    echo ('</TD>');

    echo ('</TR>');
    echo ('</TABLE>'); 
}

if (!empty($_POST['avg_y']))
{
  //$avg_y = array_sum($value)/count($value);
    echo ('<TABLE border="0" cellpadding="2">');
    echo ('<TH align="left">');
    echo ('Average over visible region:');
    echo ('</TH>');
    echo ('<TH align="left">');
    echo (format_num($avg_y));
    echo ('</TH>');
    echo ('<TH align="left">');
    echo ('('.$s_units.')');
    echo ('</TH>');
    echo ('</TABLE>'); 
}

if (!empty($click_t))
{
    $found_y = find_y($click_t, $time, $value);
    echo ('<TABLE border="0" cellpadding="2">');
    
    echo ('<TR>');
    echo ('<TH align="left">');
    echo ('Value on  ');
    echo (date("M d, Y @ G:i:s", $click_t).' &#160 = &#160 ');
    echo (format_num($found_y));
    echo ('('.$s_units.')');
    echo ('</TH>');
    echo ('</TR>');
   
    echo ('<TR>');
    echo ('<FORM action="slow_control_export_data_all.php" method="post">');
    echo ('<TH align="left">');
    echo ('Export all sensor values for this time: ');
    echo ('<input type="text" src="pixmaps/export.png" name="export_time" value="'.date("G:i:s  M d, y", $click_t).'" title="Sensor data at this time">');
    echo ('<input type="submit" name="change" value="Export">');
    echo ('</TH>');
    echo ('</FORM>');
    echo ('</TR>');

    echo ('</TABLE>'); 
}

if (!empty($_POST['lin_reg']))
{
    echo ('<TABLE border="0" cellpadding="2">');
    echo ('<TR>');
    echo ('<TH align="left">');
    echo ('Linear regression over visible region.');
    echo ('</TH>');
    
    echo ('<TH align="left">');
    echo (' &#160 Slope = '.format_num($y_reg[2]));
    echo ('('.$s_units.'/s)');
    
    echo (' &#160 &#160 (Local) Y-intercept = ');
    echo (format_num($y_reg[0]));
    echo ('('.$s_units.')');
    echo ('</TH>');

    if (!empty($_POST['lin_reg_target']))
    {
	$targ_val=floatval($_POST['lin_reg_target']);
	$targ_time=interp_y($targ_val, $y_reg[2], $y_reg[3]);
	
	echo ('<TH align="left">');
	echo (' &#160 &#160 Will reach '.$targ_val.'('.$s_units.') on:');
	echo ('</TH>');
	echo ('<TH align="left">');
	echo (date("M d, Y @ G:i:s", $targ_time));
	echo ('</TH>');
	echo ('</TR>');
    }
    else
    {
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<TH align="left">');
	echo (' &#160 &#160 Target value: <input type="text" name="lin_reg_target" size=9>');
	echo ('<input type="hidden" name="lin_reg" value="'.$sensor_name.'" >');
	echo ('</TH>');
	echo ('<TH align="left">');
	echo ('('.$s_units.')');
	echo ('</TH>');
	echo ('</FORM>');
	echo ('</TR>');	
    }
    echo ('<TR>');
    echo ('<TD align="left">');
    echo ('</TD>');
    
    echo ('<TD align="left">');
    echo (' &#160 (Slope = '.format_num($y_reg[2]*60.0));
    echo ('('.$s_units.'/min)');
    echo (' &#160  = '.format_num($y_reg[2]*3600.0));
    echo ('('.$s_units.'/hour))');
    echo ('</TD>');

    echo ('</TR>');	


    echo ('</TABLE>'); 
}

echo ('</TD>');
echo ('</TR>');
echo ('</TABLE>'); 


echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('Horizontal plot size <input type="text" name="single_view_x_size" value="'.$_SESSION['single_view_x_size'].'" />');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('Vertical plot size <input type="text" name="single_view_y_size" value="'.$_SESSION['single_view_y_size'].'" />');
echo ('</TH>');
echo ('</FORM>');
echo ('</TABLE>');

echo(' </body>');
echo ('</HTML>');
?>
