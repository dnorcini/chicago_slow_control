<?php
 
echo ('<TR>');
echo ('<TH align="left">');  echo ('Number of Total CCDs');     echo ('</TH>');
echo ('<TH align="left">');  echo ('Number of DES CCDs'); echo ('</TH>');
echo ('<TH align="left">');  echo ('Number of Skipper CCDs');     echo ('</TH>');
echo ('</TR>');

echo ('<TR>');	
$query = "SELECT COUNT(*) FROM CCD WHERE ID >= 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);          echo ('</TD>');

$query = "SELECT COUNT(*) FROM CCD WHERE CCD_Type='DES' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');

$query = "SELECT COUNT(*) FROM CCD WHERE CCD_Type='Skipper' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TH align="left">');  echo ('Number of Science-grade Skipper CCDs'); echo ('</TH>');
echo ('<TH align="left">');  echo ('Number of Operation-grade Skipper CCDs');     echo ('</TH>');
echo ('<TH align="left">');  echo ('Number of Failed Skipper CCDs');       echo ('</TH>');
echo ('</TR>');

echo ('<TR>');	
$query = "SELECT COUNT(*) FROM CCD WHERE CCD_Type='Skipper' AND Status='Science-grade' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');

$query = "SELECT COUNT(*) FROM CCD WHERE CCD_Type='Skipper' AND Status='Operation-grade' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');

$query = "SELECT COUNT(*) FROM CCD WHERE CCD_Type='Skipper' AND Status='Failed' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);          echo ('</TD>');
echo ('</TR>');

echo ('<TR>');	
echo ('<TD colspan=5 align="center">'); echo ('---'); echo ('</TD>'); 
echo ('</TR>');


?>
