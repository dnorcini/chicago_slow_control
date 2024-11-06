<?php
// scatter_entries.php
// D.Norcini, Hopkins, 2024

session_start();
$req_priv = "full";
include("db_login.php");
include("page_setup.php");
include("jpgraph/jpgraph.php");
include("jpgraph/jpgraph_scatter.php");
include("jpgraph/jpgraph_line.php");
include("jpgraph/jpgraph_plotline.php");

$plot_type_array = array("DIEs");

///////  Find which sensors we want to plot
if (!empty($_POST['x_sensor_selection']))
     $_SESSION['scatt_x_sensor'] = $_POST['x_sensor_selection'];
if (!empty($_POST['y_sensor_selection']))
     $_SESSION['scatt_y_sensor'] = $_POST['y_sensor_selection'];

if (empty($_SESSION['scatt_x_sensor']))
    $_SESSION['scatt_x_sensor'] = $plot_type_array[0];
if (empty($_SESSION['scatt_y_sensor']))
    $_SESSION['scatt_y_sensor'] = $plot_type_array[0];

$die_parameter_names = array(
    "Activation",
    "Temp",
    "Image1_Noise_L1",
    "Image1_Noise_L2",
    "Image1_Noise_U1",
    "Image1_Noise_U2",
    "Image2_Pixel_Defects_L1",
    "Image2_Pixel_Defects_L2",
    "Image2_Pixel_Defects_U1",
    "Image2_Pixel_Defects_U2",
    "Image2_Noise_Overscan_L1",
    "Image2_Noise_Overscan_L2",
    "Image2_Noise_Overscan_U1",
    "Image2_Noise_Overscan_U2",
    "Image2_CTI_Code_L1",
    "Image2_CTI_Code_L2",
    "Image2_CTI_Code_U1",
    "Image2_CTI_Code_U2",
    "Image3_Pixel_Defects_L1",
    "Image3_Pixel_Defects_L2",
    "Image3_Pixel_Defects_U1",
    "Image3_Pixel_Defects_U2",
    "Image3_Noise_Overscan_L1",
    "Image3_Noise_Overscan_L2",
    "Image3_Noise_Overscan_U1",
    "Image3_Noise_Overscan_U2",
    "Image3_CTI_Code_L1",
    "Image3_CTI_Code_L2",
    "Image3_CTI_Code_U1",
    "Image3_CTI_Code_U2",
    "Image4_Dark_Current_L1",
    "Image4_Dark_Current_L2",
    "Image4_Dark_Current_U1",
    "Image4_Dark_Current_U2",
    "Image6_CTI_Code_L1",
    "Image6_CTI_Code_L2",
    "Image6_CTI_Code_U1",
    "Image6_CTI_Code_U2",
    "Image6_Noise_Overscan_L1",
    "Image6_Noise_Overscan_L2",
    "Image6_Noise_Overscan_U1",
    "Image6_Noise_Overscan_U2"
);

echo ('<TABLE border="0" cellpadding="20" width=100%>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH align="left">');
echo ('X parameter selection: <select name="x_sensor_selection">');
foreach ($die_parameter_names as $sensor_name)
{
    echo('<option ');
    if (strcmp($sensor_name, $_SESSION['scatt_x_sensor']) == 0)
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
foreach ($die_parameter_names as $sensor_name)
{
    echo('<option ');
    if (strcmp($sensor_name, $_SESSION['scatt_y_sensor']) == 0)
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

// Properly include the variables in the SQL queries using backticks
$query = "SELECT `id`, `" . $x_sensor_name . "` FROM DIE LIMIT 500";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br />" . mysql_error() . "<br>Query: " . $query);
}

$id_x = array();
$x = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $id_x[] = (int)$row['id'];
    $x[] = (double)$row[$x_sensor_name];
}

$query = "SELECT `id`, `" . $y_sensor_name . "` FROM DIE LIMIT 500";
$result = mysql_query($query);
if (!$result)
{
    die ("Could not query the database <br />" . mysql_error() . "<br>Query: " . $query);
}

$id_y = array();
$y = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $id_y[] = (int)$row['id'];
    $y[] = (double)$row[$y_sensor_name];
}

// Rest of the code remains the same as before

$y = interpolate_arrays($x, $y, $id_x, $id_y);

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
$graph->xgrid->Show(true);
$graph->ygrid->Show(true);
$graph->xgrid->SetColor("black");
$graph->ygrid->SetColor("black");
$graph->xaxis->title->Set($x_sensor_name." (".$die_parameter_units[$x_sensor_name].")");
$graph->yaxis->title->Set($y_sensor_name." (".$die_parameter_units[$y_sensor_name].")");	
$graph->xaxis->SetTitleMargin(10);
$graph->yaxis->SetTitleMargin(80);
$graph->xaxis->SetPos("min");

$scatterplot = new ScatterPlot($y, $x);
$scatterplot->mark->SetType(MARK_FILLEDCIRCLE);
$scatterplot->mark->SetFillColor("red");
$graph->Add($scatterplot);

if (!empty($y_reg))
{
    $x_reg = array(min($x), max($x)); 
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
    echo ('('.$die_parameter_units[$sensor_name].'*s)');
    echo ('</TH>');
    echo ('</TR>');
    
    echo ('<TR>');
    echo ('<TD align="right">');
    echo ('(=  &#160');
    echo ('</TD>');

    echo ('<TD align="left">');
    echo (format_num($int_y/60.0));
    echo ('('.$die_parameter_units[$sensor_name].'*min)');
    echo (' &#160 = &#160 ');
    echo (format_num($int_y/3600.0));
    echo ('('.$die_parameter_units[$sensor_name].'*hour))');
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
    echo ('('.$die_parameter_units[$y_sensor_name].'/'.$die_parameter_units[$x_sensor_name].')');
    
    echo (' &#160 &#160 Y-intercept = ');
    echo (format_num($y_reg[3]));
    echo ('('.$die_parameter_units[$y_sensor_name].')');
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