<?php
  // main.php
  // Part of the astro slow control system.  
  // James Nikkel, 2016
  // james.nikkel@gmail.com
  //
session_start();
$req_priv = "guest";
include("db_login.php");
include("page_setup.php");

echo('
<div align="center">
<br>
<br>
<br>
<br>
<font size="+2">
Welcome to the Quality Control drop page!
<br> 
If you have a username and password, enter them in the fields above and click the log in button or press enter.
<br>
If you do not, contact an admin for this experiment.
<br>
Have a lovely day.
</font>
<br>
<br>
');


echo('</body>');
echo ('</HTML>');
?>
