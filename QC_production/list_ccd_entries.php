<?php
  // list_ccd_entries.php
  // D. Norcini, UChicago, 2020
  // Moved to QC_production, D. Norcini, JHU, 2026

session_start();
$req_priv = "basic";
include("db_login.php");
include("page_setup.php");
// include("aux/make_data_plot.php");

echo ('<TABLE border="1" cellpadding="2" width=100%>');

$plot_type_array = [
    "Summary",
    "CCDs",
];

if (empty($_SESSION['ccd_choose_type']))
  $_SESSION['ccd_choose_type'] = $plot_type_array[0];

if (!(empty($_POST['ccd_choose_type'])))
  $_SESSION['ccd_choose_type'] = $_POST['ccd_choose_type'];

echo ('<TABLE border="1" cellpadding="2" width=100%>');
foreach ($plot_type_array as $index)
{
  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
  echo ('<TH>');
  echo ('<input type="submit" name="ccd_choose_type" value="'.$index.'"
               title="'.$index.'" style="font-size: 10pt">');
  echo ('</TH>');
}
echo ('</TABLE>');

echo ('<TABLE border="1" cellpadding="2" width=100%>');
if ($_SESSION['ccd_choose_type'] == "Summary")
  {
    include("list_ccd_entries_summary.php");
  }

else if ($_SESSION['ccd_choose_type'] == "CCDs")
  {
    $temp = $_SESSION['choosen_ccd'] ?? null;

    if (isset($_POST['go']))
    {
        $_SESSION['req_id'] = $_POST['go'];
        header("Location: edit_ccd.php");
    }

    echo ('<TR>');
    echo ('<TH align="left">');  echo ('CCD ID'); echo ('</TH>');
    echo ('<TH align="left">');  echo ('Name');     echo ('</TH>');
    echo ('<TH align="left">');  echo ('Type');       echo ('</TH>');
    echo ('<TH align="left">');  echo ('Size');     echo ('</TH>');
    echo ('<TH align="left">');  echo ('Wafer ID'); echo ('</TH>');
    echo ('<TH align="left">');  echo ('Wafer position'); echo ('</TH>');
    echo ('<TH align="left">');  echo ('Status');     echo ('</TH>');
    echo ('<TH align="left">');  echo ('Current location');   echo ('</TH>');
    echo ('</TR>');

    $table = "CCD";
    include("aux/get_last_table_id.php");

    for ($i=1; $i <= $last_id; $i++)
      {
        $_SESSION['choosen_ccd'] = $i;

        include("aux/get_ccd_vals.php");

        echo ('<TR>');
        echo ('<TD align="left">');
        echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
        echo ('<input type="submit" name="go" value="'.$id.'" title="Goto CCD ID '.$id.'" style="font-size: 14pt">');
        echo ('</FORM>');

        echo ('<TD align="left">'); echo ($name);   echo ('</TD>');
        echo ('<TD align="left">'); echo ($ccd_type); echo ('</TD>');
        echo ('<TD align="left">'); echo ($size);   echo ('</TD>');
        echo ('<TD align="left">'); echo ($wafer_id);  echo('</TD>');
        echo ('<TD align="left">'); echo ($wafer_position);  echo('</TD>');
        echo ('<TD align="left">'); echo ($status);   echo ('</TD>');
        echo ('<TD align="left">'); echo ($location); echo ('</TD>');
        echo ('</TR>');
      }

    $_SESSION['choosen_ccd'] = $temp;

    echo ('</TABLE>');

    mysql_close($connection);
    echo(' </body>');
    echo ('</HTML>');
  }
?>
