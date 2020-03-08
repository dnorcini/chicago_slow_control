<?php
// slow_control_del_contacts.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "admin";
include("master_db_login.php");
include("slow_control_page_setup.php");

if (isset($_POST['del_user']))
{
    if (isset($_POST['really_del']))
    {
	$query = "DELETE FROM `users` WHERE `user_name` = \"".$_POST['del_user']."\"  LIMIT 1";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	mysql_close($connection);
	echo ('<br>');
	echo ('<br>');
	echo ('User '.$_POST['del_user'].' deleted.' );
    }
    
    else
    {
	echo ('<br>');
	echo ('<TABLE  border="0" cellpadding="2">');
	echo ('<TR>');
	echo ('<TH align=left>');
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<input type="hidden" name="del_user" value='.$_POST['del_user'].'>');
	echo ('Really delete user:  '.$_POST['del_user'].'?    ');
	echo ('<input type="submit" name="really_del" value="Yes">');
	echo ('</FORM>');
	echo ('</TH>');

	echo ('<TH align=center>');
	echo ('<FORM action="slow_control_users.php" method="post">');
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
