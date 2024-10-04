<?php
  // list_entries.php
 // James Nikkel, Yale University, 2017.
  // james.nikkel@yale.edu
  //
  // D. Norcini, UChicago, 2020
  // added features to Goto edit_ccd entry
  
session_start();
$req_priv = "basic";
include("db_login.php");
include("page_setup.php");
include("aux/make_data_plot.php");

echo ('<TABLE border="1" cellpadding="2" width=100%>');

$plot_type_array = array(
			 "Summary",
			"CCDs",
                         );


if (empty($_SESSION['choose_type']))
  $_SESSION['choose_type'] = $plot_type_array[0];

if (!(empty($_POST['choose_type'])))
  $_SESSION['choose_type'] = $_POST['choose_type'];


echo ('<TABLE border="1" cellpadding="2" width=100%>');
foreach ($plot_type_array as $index)
{
  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
  echo ('<TH>');
  echo ('<input type="submit" name="choose_type" value="'.$index.'" 
               title="'.$index.'" style="font-size: 10pt">');
  echo ('</TH>');
}
echo ('</TABLE>');


echo ('<TABLE border="1" cellpadding="2" width=100%>');
if ($_SESSION['choose_type'] == "Summary")
  {
    include("list_entries_summary.php");
  }

 else if ($_SESSION['choose_type'] == "CCDs")
   {
     $ave_A = 0;
     $ave_A_count = 0;
     $ave_B = 0;
     $ave_B_count = 0;

     $temp = $_SESSION['choosen_ccd'];

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
     echo ('<TH align="left">');  echo ('Status');     echo ('</TH>');
     echo ('<TH align="left">');  echo ('Location');   echo ('</TH>');
     echo ('<TH align="left">');  echo ('Dark Current');   echo ('</TH>');
     echo ('<TH align="left">');  echo ('Resolution');   echo ('</TH>');
     echo ('</TR>');
    
     $table = "CCD";
     include("aux/get_last_table_id.php");

     for ($i=1; $i <= $last_id; $i++)
       { 
	 $_SESSION['choosen_ccd'] = $i;
	 
	 /////////////  Get selected ccd values:
	 include("aux/get_ccd_vals.php");

	 if ($status=='Science-grade')
	   {
	     $ave_DC += $dark_current;
	     $ave_DC_count++;

             $ave_Res += $dark_current;
             $ave_Res_count++;
           }
         echo ('<TR>');
	 echo ('<TD align="left">'); 
	 echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	 echo ('<input type="submit" name="go" value="'.$id.'" title="Goto CCD ID '.$id.'" style="font-size: 14pt">');
	 echo ('</FORM>');

	 echo ('<TD align="left">'); echo ($name);   echo ('</TD>');
	 echo ('<TD align="left">'); echo ($ccd_type); echo ('</TD>');
	 echo ('<TD align="left">'); echo ($size);   echo ('</TD>');	
	 echo ('<TD align="left">'); echo ($status);   echo ('</TD>');
	 echo ('<TD align="left">'); echo ($location); echo ('</TD>');
	 echo ('<TD align="left">'); echo ($dark_current); echo ('</TD>');
         echo ('<TD align="left">'); echo ($resolution);   echo ('</TD>');
	 echo ('</TR>');
       }
    
     $_SESSION['choosen_ccd'] = $temp;
	 
echo ('</TABLE>');

echo("Average Dark Current (Science-grade only) = ".$ave_DC/$ave_DC_count." e-/pixel/image");
echo("<br>");
echo("Average Resolution (Science-grade only) = ".$ave_Res/$ave_Res_count." e-");

mysql_close($connection);
echo(' </body>');
echo ('</HTML>');
}
?>
