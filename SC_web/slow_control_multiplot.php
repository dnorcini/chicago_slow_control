<?php
// slow_control_multiplot.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 

include ("jpgraph/jpgraph.php");	
include	("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_log.php");
include ("jpgraph/jpgraph_plotline.php");


//////////////////////////////////////   Interactive form input:

if ((empty($_SESSION['click_type'])) || ($_SESSION['click_type'] > 4) || ($_SESSION['click_type'] < 1))
    $_SESSION['click_type'] = 2;
    
if (!empty($_POST['click_position']))
{
    $_SESSION['click_type'] = $_POST['click_type'];
    $click_x = ($_POST['click_position'][0] - 100.0)/($_SESSION['single_view_x_size']-100-$_SESSION['right_margin']);
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
	    $click_t = intval($click_x  * $DX + $_SESSION['t_min_p']);
    }
    else
    	unset($click_t);
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

$need_roller = 1;
include("aux/choose_types.php");    // sets session variable $choose_type from the check boxes.

///  Define max and min times for display by finding the very first and very
///  last entry in the database.  These are stored as session variables
///  t_min and t_max.  We then initialize the plotting limits, t_min_p and t_max_p
include("aux/get_time_max_min.php"); 

if ((empty($_SESSION['t_min_p'])) || (empty($_SESSION['t_max_p'])))
{
    $_SESSION['t_min_p'] = $_SESSION['t_max'] - (24 * 3600);  // plot only last 24 hours of data
    $_SESSION['t_max_p'] = time();  // set to now
}
include("aux/choose_times.php");

if (empty($_SESSION['s_roll_up']))
    $_SESSION['s_roll_up'] = array_combine($sensor_names, make_new_zero_array(count($sensor_names)));

if (!empty($_POST['sens_hide']))
    $_SESSION['s_roll_up'][$_POST['sens_hide']] = 1;

if (!empty($_POST['sens_show']))
    $_SESSION['s_roll_up'][$_POST['sens_show']] = 0;

if (empty($_SESSION['multi_logy']))
  $_SESSION['multi_logy'] = 0;
if (!empty($_POST['log-y-button']))  
{
  if ($_SESSION['multi_logy'] == 0)
    $_SESSION['multi_logy'] = 1;
  else
     $_SESSION['multi_logy'] = 0;
 }

if ($_SESSION['multi_logy'] == 0)
    $logy = "intlin";
 else
    $logy = "intlog";

/////////////////////////////////       Generate Plot here:
$plot_name = "jpgraph_cache/plot_multi.png";

$graph = new Graph($_SESSION['single_view_x_size'], $_SESSION['single_view_y_size'], "auto", 60);	

$sens_line_names = array();
$sens_line_colours = array();
$sens_line_styles = array();

$line_num = 0;
$break = 0;

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
  if (($sensor_settable[$sensor_name] == 0)
      && ($_SESSION['s_roll_up'][$sensor_name] == 0)
      && ($break == 0))
    {	    
      if (strcmp($logy, "intlog") == 0)
	$query = "SELECT time, value FROM  sc_sens_".$sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
	  " AND ".$_SESSION['t_max_p']." AND `value` > 0 ORDER BY RAND() LIMIT ".$num_plot_points;
      else
	$query = "SELECT time, value FROM  sc_sens_".$sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
	  " AND ".$_SESSION['t_max_p']." ORDER BY RAND() LIMIT ".$num_plot_points;
	    
      $result = mysql_query($query);
      if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	    
      $time = array();
      $value = array();
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{	
	  $time[] = (int)$row['time'];
	  $value[] = (double)$row['value'];
	}  
	    
      if (count($time) > 1)
	{
	  array_multisort($time, $value);
		
	  $n=count($time);
		
	  $x_min = $_SESSION['t_min_p'];
	  $x_max = $_SESSION['t_max_p'];
	  
	  if ($line_num == 0)
	    {
	      $y_min = min($value);
	      $y_max = max($value);
	    }
	  else
	    {
	      if ($y_min > min($value))
		$y_min = min($value);
	      if ($y_max < max($value))
		$y_max = max($value);
	    }
	  $dy = $y_max-$y_min;
	  $y_min -= 0.02*$dy;
	  $y_max += 0.02*$dy;
	
	  $lineplot = new LinePlot($value, $time);
	  $lineplot->SetWeight(2);

	  $colour = int_to_colour($line_num);
	  $style = int_to_style($line_num);

	  $lineplot->SetColor($colour);

	  $graph->Add($lineplot);

	  $sens_line_names[] = $sensor_name;
	  $sens_line_colours[] = $colour;
	  $sens_line_styles[] = $style;
		
	  $line_num++;
	  if ($line_num > 50)
	    $break = 1;
	} 
    }
}
if (strcmp($logy, "intlog") == 0)
  $graph->SetScale($logy, 0, 0, $x_min, $x_max);
 else
   $graph->SetScale($logy, $y_min, $y_max, $x_min, $x_max);

if (max($time)-$time[0] > 3*3600*24)
  {
    $graph->xaxis->SetLabelFormatCallback('DateCallback');
    $graph->xaxis->title->Set("date");	
  }
 else 
   {
     $graph->xaxis->SetLabelFormatCallback('TimeCallback');
     $graph->xaxis->title->Set("time");
   }      
$graph->xaxis->SetLabelAngle(90);
$y_label = " (".$sensor_units[$sensor_name].")";
$graph->yaxis->title->Set($y_label);

if ($line_num < 0)
  echo ('<br><br> &#160 &#160 You might try zooming out a bit. <br>');


if (!empty($_SESSION['go_msg_time']))
  {
    $line_mesg = new PlotLine(VERTICAL, $_SESSION['go_msg_time'], "red", 2);
    $graph->AddLine($line_mesg);
  }

if (!empty($click_t))
  {
    $line_mesg = new PlotLine(VERTICAL, $click_t, "green", 2);
    $graph->AddLine($line_mesg);
  }

$graph->title->SetFont(FF_FONT1,FS_BOLD);	
$graph->SetFrame(true,$_SESSION['bgcolour'], 1);
$graph->SetBackgroundGradient('darkblue','blue', GRAD_MIDHOR, BGRAD_PLOT);
$graph->SetColor("darkblue");
$graph->SetMarginColor($_SESSION['bgcolour']);
$graph->img->SetMargin(100, 5, 30, 80);  
$graph ->xgrid->Show(false);
$graph ->ygrid->Show(false);
$graph->xaxis->SetTitleMargin(50);
$graph->yaxis->SetTitleMargin(70);
$graph->SetYDeltaDist(60);
$graph->Stroke($plot_name);

$sens_line_colours = array_combine($sens_line_names, $sens_line_colours);
$sens_line_styles = array_combine($sens_line_names, $sens_line_styles);

////   This next section generate the HTML with the plot names in a table.

echo ('<TABLE border="0" cellpadding="2" frame="box" width=100%>');
echo ('<TR>');
echo ('<TD>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('Clicking on the plot: ');
echo (' &#160 &#160 Moves begin time <input type="radio" name="click_type" value="1" '); 
if ($_SESSION['click_type'] == 1)
  echo('CHECKED>');
 else 
   echo('>');
echo (' &#160 &#160 Moves central time <input type="radio" name="click_type" value="2" ');
if ($_SESSION['click_type'] == 2)
  echo('CHECKED>');
 else 
   echo('>');
echo (' &#160 &#160 Moves end time <input type="radio" name="click_type" value="3" ');
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

if (count($time) > 1)
  {
    echo ('<input type="image" src="'.$plot_name.'" name="click_position[]" border=0 align=center width ='.$_SESSION['single_view_x_size'].'>');
  }
echo ('</TD>');
echo ('</FORM>');

echo ('<TD valign=top>');	

echo ('<br>');
echo ('<br>');
echo ('<br>');

echo ('</TD>');
echo ('</TR>');
echo ('</TABLE>');

echo ('<br>');

if (!empty($click_t))
  {
    $found_y = find_y($click_t, $time, $value);
    echo ('<FORM action="slow_control_export_data_all.php" method="post">');
    echo ('Export all sensor values for this time: ');
    echo ('<input type="text" src="pixmaps/export.png" name="export_time" value="'.date("G:i:s  M d, y", $click_t).'" title="Sensor data at this time">');
    echo ('<input type="submit" name="change" value="Export">');
    echo ('</FORM>');
  }


//////////////  Make Selection Table

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');

foreach ($my_sensor_names as $sensor_name)
{
  if ($sensor_settable[$sensor_name] == 0)
    {                                                                // don't show settable or hidden sensors in this table
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
