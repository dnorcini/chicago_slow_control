<?php
// slow_control_RGA.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2011.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
$my_sensor_name = "RGA_scan";
$my_inst_name = "SRS_RGA";
$Master_sens = "RGA_Master";
$get_all_sensors = 1;
$get_single_type = "RGA";
include("aux/get_sensor_info.php"); 
include("aux/get_inst_info.php"); 
include("aux/new_set_val.php");     // do insert of new value if requested
include("aux/make_plot.php");

if (isset($_POST['start_stop_inst']))  
{
    if (check_access($_SESSION['privileges'], $sensor_ctrl_priv[$Master_sens], $allowed_host_array))
    { 
	mysql_close($connection);
	include("master_db_login.php");
	if (($inst_run[$my_inst_name] == 0) && ($_POST['start_stop_inst'] == 1))
	{
	    $query = "UPDATE `sc_insts` SET `run` = 1 WHERE `name` = \"".$my_inst_name."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$my_inst_name." started by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	}
	else if (($inst_run[$my_inst_name] == 1) && ($_POST['start_stop_inst'] == 0))
	{
	    $query = "UPDATE `sc_insts` SET `run` = 0 WHERE `name` = \"".$my_inst_name."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$my_inst_name." stopped by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	}
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
    include("aux/get_inst_info.php"); 
}

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
$temp_num = $_SESSION['num_val_avgs'];
$_SESSION['num_val_avgs'] = 1;
include("aux/get_sensor_vals.php");
$_SESSION['num_val_avgs'] = $temp_num;

if (empty($_SESSION['click_type']))
    $_SESSION['click_type'] = 2;
    
if (!empty($_POST['click_position']))
{
    $_SESSION['click_type'] = $_POST['click_type'];
    $click_x = ($_POST['click_position'][0] - 100.0)/($_SESSION['single_view_x_size'] - 105.0);
    $DX = ($_SESSION['x_max_dat'] - $_SESSION['x_min_dat'])*(1+2*0.01);
   
    if ($_POST['click_type'] == 1)
	$_SESSION['x_min_p'] = intval($_SESSION['x_min_p'] + $DX * $click_x);	
    
    if ($_POST['click_type'] == 2)
    {
	$_SESSION['x_min_p'] = intval($_SESSION['x_min_p'] + $DX * ($click_x - 0.5));
	$_SESSION['x_max_p'] = intval($_SESSION['x_max_p'] + $DX * ($click_x - 0.5));
    }
    if ($_POST['click_type'] == 3)    
    {
	$_SESSION['x_max_p'] = intval($_SESSION['x_max_p'] - $DX * (1-$click_x));	
    }
    if ($_POST['click_type'] == 4)
    {
	if ($click_x <= 0)
	    $click_t = $_SESSION['x_min_dat'];
	else
	    $click_t = intval($click_x * $DX + $_SESSION['x_min_dat']);
    }
    else
    	unset($click_t);
}

if (!empty($_POST['zoom_in']))
{
    $DX = $_SESSION['x_max_p'] - $_SESSION['x_min_p'];
    $MID_X = ($_SESSION['x_max_p'] + $_SESSION['x_min_p'])/2.0;
    $_SESSION['x_min_p'] = intval($MID_X - $DX/4.0);
    $_SESSION['x_max_p'] = intval($MID_X + $DX/4.0);
}

if (!empty($_POST['zoom_out']))
{
    $DX = $_SESSION['x_max_p'] - $_SESSION['x_min_p'];
    $MID_X = ($_SESSION['x_max_p'] + $_SESSION['x_min_p'])/2.0;
    $_SESSION['x_min_p'] = intval($MID_X - $DX);
    $_SESSION['x_max_p'] = intval($MID_X + $DX);
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

if (empty($_SESSION['RGA_log']))
{
    $_SESSION['RGA_log'] = 0;
}

if (!empty($_POST['log-y-button']))
{
    if ($_SESSION['RGA_log'])
	$_SESSION['RGA_log'] = 0;
    else
	$_SESSION['RGA_log'] = 1;
}


//////////////////////////////////////////////////////////////////
/*
$query = "CREATE TABLE IF NOT EXISTS `sc_sens_".my_sensor_name."` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  `x0` double NOT NULL,
  `x1` double NOT NULL,
  `dxN` int(11) NOT NULL,
  `y0` double NOT NULL,
  `y` text collate latin1_general_cs NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;";

mysql_close($connection);
include("master_db_login.php");
$result = mysql_query($query);
if (!$result)
  die ("Could not make table <br />" . mysql_error());
mysql_close($connection);
include("db_login.php");
*/

$all_times = array();
$query = "SELECT `time` FROM `sc_sens_".$my_sensor_name."` ORDER BY `time` DESC";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error());
}
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $all_times[] = (int)$row['time'];
}

$num_times = count($all_times);

if (empty($_SESSION['RGA_time_index']))
    $_SESSION['RGA_time_index'] = 0;

if (!empty($_POST['RGA_last_time']))
    $_SESSION['RGA_time_index'] = 0;

if (!empty($_POST['RGA_first_time']))
    $_SESSION['RGA_time_index'] = $num_times-1;

if (!empty($_POST['RGA_prev_time']))
    $_SESSION['RGA_time_index']++;

if (!empty($_POST['RGA_next_time']))
    $_SESSION['RGA_time_index']--;

if (!empty($_POST['RGA_choose_time']))
    $_SESSION['RGA_time_index'] =  closest_index(strtotime($_POST['RGA_choose_time']), $all_times);

if ($_SESSION['RGA_time_index'] > $num_times-1)
    $_SESSION['RGA_time_index'] = $num_times-1;

if ($_SESSION['RGA_time_index'] < 0)
    $_SESSION['RGA_time_index'] = 0;


$RGA_time = $all_times[$_SESSION['RGA_time_index']];

echo ('<TABLE border="1" cellpadding="2" width=100%>');

echo ('<TH>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="RGA_first_time" value="First">');
echo ('<input type="image" src="pixmaps/to_start.png" alt="First" title="Go to first scan">');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="RGA_prev_time" value="Prev">');
echo ('<input type="image" src="pixmaps/prev.png" alt="Prev" title="Go to previous scan">');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('Scan time/date: <input type="text" name="RGA_choose_time" value="'.date("G:i:s  M d, y", $RGA_time).'" >');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="RGA_next_time" value="Next">');
echo ('<input type="image" src="pixmaps/next.png" alt="Next" title="Go to next scan">');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="RGA_last_time" value="Last">');
echo ('<input type="image" src="pixmaps/to_end.png" alt="Last" title="Go to latest scan">');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
include("aux/inst_status.php");
echo ('</TH>');

echo ('</TABLE>');


/////////////////////////////////       Generate Plot here:
$plot_name = "jpgraph_cache/plot_RGA.png";
$plot_title = "RGA Plot";

$query = "SELECT * FROM `sc_sens_".$my_sensor_name."` WHERE `time` = \"".$RGA_time."\" LIMIT 1";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error());
}
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $time = (int)$row['time'];
    $value = (double)$row['value'];
    $rate = (double)$row['rate'];
    $x0 = (double)$row['x0'];
    $x1 = (double)$row['x1'];
    $dxN = (int)$row['dxN'];
    $y0 =(double)$row['y0'];
    $y_string = $row['y'];
}

$y = explode(",", $y_string);
$y = scale_array($y, $y0);
$x = make_new_data_array_N($x0, $x1, count($y));

//$y_min = min($y);
//$y = shift_array($y, min($y));
//$y_max = max($y);
//$y = scale_array($y, 0.1/max($y));
//$y = shift_array($y, 0.1);

if ($_SESSION['RGA_log'])
    if (min($y)<=0)
	$y = shift_array($y, -1.0*min($y)+1e-3);

$graph = new Graph($_SESSION['single_view_x_size'], $_SESSION['single_view_y_size'], "auto");	

if ($_SESSION['RGA_log'])
    $graph->SetScale("linlog", 0, 0, $x0-1, $x1+1);
else
    $graph->SetScale("linlin", min($y)-0.05*(max($y)-min($y)), max($y)+0.05*(max($y)-min($y)), $x0-1, $x1+1);

$graph->title->SetFont(FF_FONT1,FS_BOLD);	
$graph->SetFrame(true,$_SESSION['bgcolour'], 1);
$graph->SetBackgroundGradient('darkblue','blue', GRAD_MIDHOR, BGRAD_PLOT);
$graph->SetColor("darkblue");
$graph->SetMarginColor($_SESSION['bgcolour']);
$graph->img->SetMargin(100, 5, 30, 80);  
$graph ->xgrid->Show(true);
$graph ->ygrid->Show(true);
$graph ->xgrid->SetColor("black");
$graph ->ygrid->SetColor("black");
$graph->xaxis->title->Set("Mass (AMU)");
$graph->yaxis->title->Set("Partial Pressure (pTorr)");	
$graph->xaxis->SetTitleMargin(10);
$graph->yaxis->SetTitleMargin(50);
$graph->xaxis->SetPos("min");

$lineplot = new LinePlot($y, $x);
$lineplot->SetWeight(2);
$lineplot->SetColor("yellow");
$graph->Add($lineplot);
if (!empty($click_x))
{
    $line_click = new PlotLine(VERTICAL, $click_x, "green", 2);
    $graph->AddLine($line_click);
}
$graph->Stroke($plot_name);

////   This next section generate the HTML with the plot 

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('Clicking on the plot: ');
echo (' &#160 &#160 Moves beginning of range <input type="radio" name="click_type" value="1" '); 
if ($_SESSION['click_type'] == 1)
  echo('CHECKED>');
else 
  echo('>');
echo (' &#160 &#160 Moves central range <input type="radio" name="click_type" value="2" ');
if ($_SESSION['click_type'] == 2)
  echo('CHECKED>');
else 
  echo('>');
echo (' &#160 &#160 Moves ending of range <input type="radio" name="click_type" value="3" ');
if ($_SESSION['click_type'] == 3)
  echo('CHECKED>');
else 
  echo('>');
echo (' &#160 &#160 Selects value <input type="radio" name="click_type" value="4" ');
if ($_SESSION['click_type'] == 4)
    echo('CHECKED>');
else 
    echo('>');
echo (' &#160 &#160  &#160 <input type="image" src="pixmaps/zoom_in.png" name="zoom_in" value=1 title="Zoom in">');
echo (' &#160 &#160  &#160 <input type="image" src="pixmaps/zoom_out.png" name="zoom_out" value=1 title="Zoom out">');
echo ('<br>');

echo ('<input type="image" src="'.$plot_name.'" name="click_position[]" border=0 align=center width ='.$_SESSION['single_view_x_size'].'>');
echo ('<br>');
echo ('&#160 &#160 <input type="image" src="pixmaps/Log_y.png" name="log-y-button[]" value='.$sensor_name.' title="Log">');

//echo ('&#160 &#160 &#160 <input type="image" src="pixmaps/int.png" name="int_y" value=1 title="Integrate">');
//echo ('&#160 &#160 &#160 <input type="image" src="pixmaps/avg.png" name="avg_y" value=1 title="Average">');
//echo ('&#160 &#160 &#160 <input type="image" src="pixmaps/lin_reg.png" name="lin_reg" value=1 title="Linear regression">');

echo ('</FORM>');

echo ('<br>');
echo ('<FORM action="slow_control_export_data.php" method="post">');
echo ('&#160 &#160 &#160 Export data to text file: <input type="submit" src="pixmaps/export.png" name="export" value='.$my_sensor_name.' title="Export data">');
echo ('<input type="hidden" name="array_data" value="1">');
echo ('<input type="hidden" name="data_time" value='.$RGA_time.'>');
echo ('</FORM>');
echo ('<br>');


if (!empty($click_x))
{
    $found_y = find_y($click_x, $x, $y);
   
}


//////////////  Make Selection Table

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');

foreach ($sensor_names as $sensor_name)
{
    if ((strcmp($sensor_types[$sensor_name], "RGA") == 0) && ($sensor_settable[$sensor_name] == 0)) 
    {                                                                      // don't show settable or hidden sensors in this table
	    echo ('<TH>');
	    if (array_key_exists($sensor_name, $sens_line_colours))
	    {
		echo ('<font color='.$sens_line_colours[$sensor_name].'>');
		echo ('<input type="image" src="pixmaps/checked.png" name="sens_hide" value="'.$sensor_name.'" alt="Hide" title="Hide">');
	    }
	    else
	    {
		echo ('<font color="grey">');
		echo ('<input type="image" src="pixmaps/unchecked.png" name="sens_show" value="'.$sensor_name.'" alt="Show" title="Show">');
	    }
	    echo ($sensor_name." / ");
	    echo ($sensor_descs[$sensor_name]);
	    echo (' ('.$sensor_units[$sensor_name].')');

	    echo ('</TH>');
	    if ($i % 5 ==0)
	    {
		echo ('</TR>');
		echo ('<TR>');
	    }
	    $i++;
	}
    }

echo ('</TR>');
echo ('</TABLE>');
echo ('</FORM>');


echo ('<br>');

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

mysql_close($connection);

echo(' </body>');
echo ('</HTML>');
?>
