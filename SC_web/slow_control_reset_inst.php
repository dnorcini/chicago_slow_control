<?php
// slow_control_reset_inst.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "config";
include("master_db_login.php");
include("slow_control_page_setup.php");

if (!(empty($_POST['reset_inst'])))
{
	echo ('<br>');
	echo ('<TABLE  border="0" cellpadding="2">');
	echo ('<TR>');
	echo ('<TH align=left>');
	echo ('<FORM action="slow_control_inst_config.php" method="post">');
	echo ('<input type="hidden" name="inst_reset" value='.$_POST['reset_inst'].'>');
	echo ('Really reset instrument:  '.$_POST['reset_inst'].'?    ');
	echo ('<input type="hidden" name="reset_inst" value="'.$_POST['reset_inst'].'">');	
	echo ('<input type="submit" name="really_reset" value="Yes">');
	echo ('</FORM>');
	echo ('</TH>');

	echo ('<TH align=center>');
	echo ('<FORM action="slow_control_inst_config.php" method="post">');
	echo ('<input type="submit" name="really_reset" value="No">');
	echo ('</FORM>');
	echo ('</TH>');
	echo ('</TR>');
	echo ('<TABLE>');
}

echo(' </body>');
echo ('</HTML>');

mysql_close($connection);
?>
