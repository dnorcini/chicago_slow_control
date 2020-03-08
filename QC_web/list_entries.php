<?php
  // list_entries.php
 // James Nikkel, Yale University, 2017.
  // james.nikkel@yale.edu
  //
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
     $ave_dc = 0;
     $ave_dc_count = 0;
     $ave_er = 0;
     $ave_er_count = 0;

     $temp = $_SESSION['choosen_ccd'];
   
     echo ('<TR>');
     echo ('<TH align="left">');  echo ('CCD ID'); echo ('</TH>');
     echo ('<TH align="left">');  echo ('Name');     echo ('</TH>');
     echo ('<TH align="left">');  echo ('Type');       echo ('</TH>');
     echo ('<TH align="left">');  echo ('Size');     echo ('</TH>');
     echo ('<TH align="left">');  echo ('Status');     echo ('</TH>');
     echo ('<TH align="left">');  echo ('Location');   echo ('</TH>');
     echo ('<TH align="left">');  echo ('Dark Current');   echo ('</TH>');
     echo ('<TH align="left">');  echo ('e- Resolution');   echo ('</TH>');
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
	     $ave_dc += $dark_current;
	     $ave_dc_count++;

             $ave_er += $e_resolution;
             $ave_er_count++;
           }
	 
	 echo ('<TR>');
	 echo ('<TD align="left">'); echo ($id);	      echo ('</TD>');
	 echo ('<TD align="left">'); echo ($name);   echo ('</TD>');
	 echo ('<TD align="left">'); echo ($ccd_type); echo ('</TD>');
	 echo ('<TD align="left">'); echo ($size);   echo ('</TD>');	
	 echo ('<TD align="left">'); echo ($status);   echo ('</TD>');
	 echo ('<TD align="left">'); echo ($location); echo ('</TD>');
	 echo ('<TD align="left">'); echo ($dark_current); echo ('</TD>');
         echo ('<TD align="left">'); echo ($e_resolution);   echo ('</TD>');
	 echo ('</TR>');
       }
    
     $_SESSION['choosen_ccd'] = $temp;
  
echo ('</TABLE>');

echo("Average Dark Current (Science-grade only) = ".$ave_dc/$ave_dc_count." e-/pixel/image");
echo("<br>");
echo("Average e- Resolution (Science-grade only) = ".$ave_er/$ave_er_count." e-");

mysql_close($connection);
echo(' </body>');
echo ('</HTML>');
}
?>
