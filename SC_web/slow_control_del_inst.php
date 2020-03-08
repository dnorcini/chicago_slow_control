<?php
// slow_control_del_inst.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "config";
include("master_db_login.php");
include("slow_control_page_setup.php");

if (isset($_POST['del_inst']))
{
    if (isset($_POST['really_del']))
    {
	$query = "DELETE FROM `sc_insts` WHERE `name` = \"".$_POST['del_inst']."\"  LIMIT 1";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	echo ('<br>');
	echo ('<br>');
	echo ('Sensor '.$_POST['del_inst'].' deleted.' );

	$mesg = "".$_POST['del_inst']." deleted by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);
	mysql_close($connection);
    }
    
    else
    {
	echo ('<br>');
	echo ('<TABLE  border="0" cellpadding="2">');
	echo ('<TR>');
	echo ('<TH align=left>');
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<input type="hidden" name="del_inst" value='.$_POST['del_inst'].'>');
	echo ('Really delete instrument:  '.$_POST['del_inst'].'?    ');
	echo ('<input type="submit" name="really_del" value="Yes">');
	echo ('</FORM>');
	echo ('</TH>');

	echo ('<TH align=center>');
	echo ('<FORM action="slow_control_inst_config.php" method="post">');
	echo ('<input type="submit" name="really_del" value="No">');
	echo ('</FORM>');
	echo ('</TH>');
	echo ('</TR>');
	echo ('<TABLE>');
    }
}
echo(' </body>');
echo ('</HTML>');
?>
