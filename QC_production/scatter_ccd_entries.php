<?php
// scatter_ccd_entries.php
// D.Norcini, UChicago, 2020
// Moved to QC_production, D. Norcini, JHU, 2026

session_start();
$req_priv = "full";
include("db_login.php");
include("page_setup.php");

include ("jpgraph/jpgraph.php");
include	("jpgraph/jpgraph_scatter.php");
include	("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_plotline.php");

$ccd_parameter_names = array_merge($glue_parameter_names, $wb_parameter_names, $testing_noise_names, $testing_resolution_names, $testing_gain_names, $testing_dark_current_names);
$ccd_parameter_units = array_merge($glue_parameter_units, $wb_parameter_units, $testing_noise_units, $testing_resolution_units, $testing_gain_units, $testing_dark_current_units);

///////  Find which sensors we want to plot
if (!empty($_POST['x_sensor_selection']))
     $_SESSION['ccd_scatt_x_sensor'] = $_POST['x_sensor_selection'];
if (!empty($_POST['y_sensor_selection']))
     $_SESSION['ccd_scatt_y_sensor'] = $_POST['y_sensor_selection'];

if (empty($_SESSION['ccd_scatt_x_sensor']))
    $_SESSION['ccd_scatt_x_sensor'] = $ccd_parameter_names[0];

if (empty($_SESSION['ccd_scatt_y_sensor']))
    $_SESSION['ccd_scatt_y_sensor'] = $ccd_parameter_names[0];

echo ('<TABLE border="0" cellpadding="20" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');

echo ('<TH align="left">');
echo ('X parameter selection: <select name="x_sensor_selection">');
foreach ($ccd_parameter_names as $sensor_name)
{
    echo('<option ');
    if (strcmp($sensor_name ,$_SESSION['ccd_scatt_x_sensor']) == 0)
    {
    echo ('selected="selected"');
    }
    echo(' value="'.$sensor_name.'" >'.$sensor_name.' </option> ');
}
echo ('</select>');
echo ('<INPUT type="submit" value="Submit"> ');
echo ('</TH>');

echo ('<TH align="left">');
echo ('Y parameter selection: <select name="y_sensor_selection">');
foreach ($ccd_parameter_names as $sensor_name)
{
    echo('<option ');
    if (strcmp($sensor_name ,$_SESSION['ccd_scatt_y_sensor']) == 0)
    {
    echo ('selected="selected"');
    }
    echo(' value="'.$sensor_name.'" >'.$sensor_name.' </option> ');
}
echo ('</select>');
echo ('<INPUT type="submit" value="Submit"> ');
echo ('</TH>');
echo ('</FORM>');
echo ('</TABLE>');

$x_sensor_name = $_SESSION['ccd_scatt_x_sensor'];
$y_sensor_name = $_SESSION['ccd_scatt_y_sensor'];

//  Set the plot size
if (!empty($_POST['single_view_x_size']))
    if (((int)$_POST['single_view_x_size'] > 120) AND ((int)$_POST['single_view_x_size'] < 2000))
	$_SESSION['ccd_single_view_x_size'] = (int)$_POST['single_view_x_size'];

if (!empty($_POST['single_view_y_size']))
    if (((int)$_POST['single_view_y_size'] > 120) AND ((int)$_POST['single_view_y_size'] < 2000))
        $_SESSION['ccd_single_view_y_size'] = (int)$_POST['single_view_y_size'];

if (empty($_SESSION['ccd_single_view_x_size']))
{
    $_SESSION['ccd_single_view_x_size'] = 800;
    $_SESSION['ccd_single_view_y_size'] = 400;
}

/////////////////////////////////       Generate Plot here:
$plot_name = "jpgraph_cache/ccd_plot_scatter.png";
$plot_title = "CCD Scatter Plot";

$query = "SELECT id, ".$x_sensor_name." FROM CCD LIMIT 500";
$result = mysql_query($query);
if (!$result)
{
    die ("Could not query the database <br />" . mysql_error());
}

$id_x = [];
$x = [];
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $id_x[] = (int)$row['id'];
    $x[] = (double)$row[$x_sensor_name];
}

$query = "SELECT id, ".$y_sensor_name." FROM CCD LIMIT 500";
$result = mysql_query($query);
if (!$result)
{
    die ("Could not query the database <br />" . mysql_error());
}

$id_y = [];
$y = [];
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $id_y[] = (int)$row['id'];
    $y[] = (double)$row[$y_sensor_name];
}

$y = interpolate_arrays($x, $y, $id_x, $id_y);

if (!empty($_POST['lin_reg']))
    $y_reg = linear_regression($x, $y);
else
    unset($y_reg);

$graph = new Graph($_SESSION['ccd_single_view_x_size'], $_SESSION['ccd_single_view_y_size'], "auto");
$graph->SetScale("linlin", min($y)-0.05*(max($y)-min($y)), max($y)+0.05*(max($y)-min($y)), min($x)-0.05*(max($x)-min($x)), max($x)+0.05*(max($x)-min($x)));
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
$graph->xaxis->title->Set($x_sensor_name." (".$ccd_parameter_units[$x_sensor_name].")");
$graph->yaxis->title->Set($y_sensor_name." (".$ccd_parameter_units[$y_sensor_name].")");
$graph->xaxis->SetTitleMargin(10);
$graph->yaxis->SetTitleMargin(80);
$graph->xaxis->SetPos("min");

$scatterplot = new ScatterPlot($y, $x);
$scatterplot->mark->SetType(MARK_FILLEDCIRCLE);
$scatterplot->mark->SetFillColor("red");
$graph->Add($scatterplot);

if (!empty($y_reg))
{
    $x_reg=[min($x), max($x)];
    $linereg = new LinePlot([$y_reg[0], $y_reg[1]], $x_reg);
    $linereg->SetColor("green");
    $graph->AddLine($linereg);
}

$graph->Stroke($plot_name);

echo ('<TABLE border="0" cellpadding="0" frame="box" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TR align=center>');
echo ('<TD colspan=1>');
echo ('<input type="image" src="'.$plot_name.'" name="click_position[]" border=0 align=center width ='.$_SESSION['ccd_single_view_x_size'].'>');
echo ('</TD>');
echo ('</TR>');
echo ('</FORM>');

if (!empty($_POST['lin_reg']))
{
    echo ('<TABLE border="0" cellpadding="2">');
    echo ('<TR>');
    echo ('<TH align="left">');
    echo ('Linear regression over visible region.');
    echo ('</TH>');

    echo ('<TH align="left">');
    echo (' &#160 Slope = '.format_num($y_reg[2]));
    echo ('('.$ccd_parameter_units[$y_sensor_name].'/'.$ccd_parameter_units[$x_sensor_name].')');

    echo (' &#160 &#160 Y-intercept = ');
    echo (format_num($y_reg[3]));
    echo ('('.$ccd_parameter_units[$y_sensor_name].')');
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
echo ('Horizontal plot size <input type="text" name="single_view_x_size" value="'.$_SESSION['ccd_single_view_x_size'].'" />');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('Vertical plot size <input type="text" name="single_view_y_size" value="'.$_SESSION['ccd_single_view_y_size'].'" />');
echo ('</TH>');
echo ('</FORM>');
echo ('</TABLE>');

mysql_close($connection);

echo(' </body>');
echo ('</HTML>');
?>
