<?php
// choose_times.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2011
// james.nikkel@yale.edu
//

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TH>');
if ($_SESSION['t_max'] < time() - 10*60)
  echo ('<font color="red">');
echo ('Last db update: '.date("M d, Y @ G:i:s", $_SESSION['t_max']).'. ');
echo ('Sensors values in <font color="yellow"> yellow </font> are more than 10 minutes old. ');
echo ('</TH>');
echo ('</TABLE>');
?>
