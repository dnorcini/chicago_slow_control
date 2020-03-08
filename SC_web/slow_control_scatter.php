<?php
// slow_control_scatter.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 

include ("jpgraph/jpgraph.php");
include	("jpgraph/jpgraph_scatter.php");
include	("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_plotline.php");

///  Define max and min times for display my finding the very first and very
///  last entry in the database.  These are stored as session variables
///  t_min and t_max.  We then initialize the plotting limits, t_min_p and t_max_p
include("aux/get_time_max_min.php"); 
if ((empty($_SESSION['t_min_p'])) || (empty($_SESSION['t_max_p'])))
{
    $_SESSION['t_min_p'] = $_SESSION['t_max'] - (24 * 3600);  // plot only last 24 hours of data
    $_SESSION['t_max_p'] = time();  // set to now
}

///  Now make a form where we can choose the time window from which to plot the data.
///  This creates/sets session variables, t_min_p and t_max_p used below. 
include("aux/choose_times.php");


///////  Find which sensors we want to plot
if (!empty($_POST['x_sensor_selection']))
     $_SESSION['scatt_x_sensor'] = $_POST['x_sensor_selection'];
if (!empty($_POST['y_sensor_selection']))
     $_SESSION['scatt_y_sensor'] = $_POST['y_sensor_selection'];

if (empty($_SESSION['scatt_x_sensor']))
    $_SESSION['scatt_x_sensor'] = $sensor_names[0];

if (empty($_SESSION['scatt_y_sensor']))
    $_SESSION['scatt_y_sensor'] = $sensor_names[0];


echo ('<TABLE border="0" cellpadding="20" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH align="left">');
echo ('X sensor selection: <select name="x_sensor_selection">');
foreach ($sensor_names as $sensor_name)
{
    if (!($sensor_settable[$sensor_name]))
    {
	echo('<option ');
	if (strcmp($sensor_name ,$_SESSION['scatt_x_sensor']) == 0)
	{
	    echo ('selected="selected"');
	}
	echo(' value="'.$sensor_name.'" >'.$sensor_descs[$sensor_name].'</option> ');
    }
}
echo ('</select>');
echo ('<INPUT type="submit" value="Submit"> ');
echo ('</TH>');

echo ('<TH align="left">');
echo ('Y sensor selection: <select name="y_sensor_selection">');
foreach ($sensor_names as $sensor_name)
{
    if (!($sensor_settable[$sensor_name]))
    {
	echo('<option ');
	if (strcmp($sensor_name ,$_SESSION['scatt_y_sensor']) == 0)
	{
	    echo ('selected="selected"');
	}
	echo(' value="'.$sensor_name.'" >'.$sensor_descs[$sensor_name].'</option> ');
    }
}
echo ('</select>');
echo ('<INPUT type="submit" value="Submit"> ');
echo ('</TH>');
echo ('</FORM>');
echo ('</TABLE>');


$x_sensor_name = $_SESSION['scatt_x_sensor'];
$y_sensor_name = $_SESSION['scatt_y_sensor'];


//  Set the plot size
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

/////////////////////////////////       Generate Plot here:
$plot_name = "jpgraph_cache/plot_scatter.png";
$plot_title = "Scatter Plot";

$query = "SELECT time, value FROM sc_sens_".$x_sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
    " AND ".$_SESSION['t_max_p']." ORDER BY RAND() LIMIT 500";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error());
}

$t_x = array();
$x = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $t_x[] = (int)$row['time'];
    $x[] = (double)$row['value'];
}

$query = "SELECT time, value FROM sc_sens_".$y_sensor_name." WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
    " AND ".$_SESSION['t_max_p']." ORDER BY RAND() LIMIT 500";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error());
}

$t_y = array();
$y = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $t_y[] = (int)$row['time'];
    $y[] = (double)$row['value'];
}

$y = interpolate_arrays($x, $y, $t_x, $t_y);

if (!empty($_POST['lin_reg']))
    $y_reg = linear_regression($x, $y);
else
    unset($y_reg);

$graph = new Graph($_SESSION['single_view_x_size'], $_SESSION['single_view_y_size'], "auto");	
$graph->SetScale("linlin", min($y)-0.05*(max($y)-min($y)), max($y)+0.05*(max($y)-min($y)), min($x)-0.05*(max($x)-min($x)), max($x)+0.05*(max($x)-min($x)));
//$graph->SetScale("linlin", 0, 0,min($x)-0.05*(max($x)-min($x)), max($x)+0.05*(max($x)-min($x)));
$graph->title->SetFont(FF_FONT1,FS_BOLD);	
$graph->SetFrame(true,$_SESSION['bgcolour'], 1);

$graph->SetBackgroundGradient('darkblue','blue', GRAD_MIDHOR, BGRAD_PLOT);
$graph->SetColor("darkblue");
$graph->SetMarginColor($_SESSION['bgcolour']);
$graph->img->SetMargin(100, 20, 10, 50);  
$graph ->xgrid->Show(true);
$graph ->ygrid->Show(true);
$graph ->xgrid->SetColor("black");
$graph ->ygrid->SetColor("black");
$graph->xaxis->title->Set($sensor_descs[$x_sensor_name]." (".$sensor_units[$x_sensor_name].")");
$graph->yaxis->title->Set($sensor_descs[$y_sensor_name]." (".$sensor_units[$y_sensor_name].")");	
$graph->xaxis->SetTitleMargin(10);
$graph->yaxis->SetTitleMargin(80);
$graph->xaxis->SetPos("min");

$scatterplot = new ScatterPlot($y, $x);
$scatterplot->mark->SetType(MARK_FILLEDCIRCLE);
$scatterplot->mark->SetFillColor("red");
$graph->Add($scatterplot);

if (!empty($y_reg))
{
    $x_reg=array(min($x), max($x)); 
    $linereg = new LinePlot(array($y_reg[0], $y_reg[1]), $x_reg);	
    $linereg->SetColor("green");
    $graph->AddLine($linereg);
}

$graph->Stroke($plot_name);


////   This next section generate the HTML with the plot in a table.

echo ('<TABLE border="0" cellpadding="0" frame="box" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TR align=center>');
echo ('<TD colspan=1>');
echo ('<input type="image" src="'.$plot_name.'" name="click_position[]" border=0 align=center width ='.$_SESSION['single_view_x_size'].'>');
echo ('</TD>');
echo ('</TR>');
echo ('</FORM>');
//echo ('&#160 &#160 &#160 <input type="image" src="pixmaps/int.png" name="int_y" value=1 title="Integrate">');
//echo ('&#160 &#160 &#160 <input type="image" src="pixmaps/lin_reg.png" name="lin_reg" value=1 title="Linear regression">');
//echo ('</FORM>');

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
    echo ('('.$sensor_units[$sensor_name].'*s)');
    echo ('</TH>');
    echo ('</TR>');
    
    echo ('<TR>');
    echo ('<TD align="right">');
    echo ('(=  &#160');
    echo ('</TD>');

    echo ('<TD align="left">');
    echo (format_num($int_y/60.0));
    echo ('('.$sensor_units[$sensor_name].'*min)');
    echo (' &#160 = &#160 ');
    echo (format_num($int_y/3600.0));
    echo ('('.$sensor_units[$sensor_name].'*hour))');
    echo ('</TD>');

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
    echo ('('.$sensor_units[$y_sensor_name].'/'.$sensor_units[$x_sensor_name].')');
    
    echo (' &#160 &#160 Y-intercept = ');
    echo (format_num($y_reg[3]));
    echo ('('.$sensor_units[$y_sensor_name].')');
    echo ('</TH>');

    echo ('<TR>');
    echo ('<TD align="left">');
    echo ('</TD>');

    echo ('</TR>');	
    echo ('</TABLE>'); 
}

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
