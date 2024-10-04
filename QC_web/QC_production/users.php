<?php
// users.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("page_setup.php");


if ((isset($_POST['del_user'])) && (strpos($_SESSION['privileges'], "admin") !== false))
  {
    if (isset($_POST['really_del']))
      {
	mysql_close($connection);
	include("master_db_login.php");

	$query = "DELETE FROM `users` WHERE `user_name` = \"".$_POST['del_user']."\"  LIMIT 1";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
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
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<input type="submit" name="really_del" value="No">');
	echo ('</FORM>');
	echo ('</TH>');
	echo ('</TR>');
	echo ('<TABLE>');
      }
  }



include("aux/get_users.php");

mysql_close($connection);

echo ('<br>');

echo ('<TABLE border="1" cellpadding="4" cellspacing="2">');
echo ('<TR>');

echo ('<TH align=left>');
echo ('Proper name');  
echo ('</TH>');

echo ('<TH align=left>');
echo ('Username');  
echo ('</TH>');

echo ('<TH align=left>');
echo ('Affiliation');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Email address');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Edit');  
echo ('</TH>');

if (strpos($_SESSION['privileges'], "admin") !== false)
{
  echo ('<TH align=left>'); 
  echo ('Delete');  
  echo ('</TH>');
}


echo ('<TBODY>');
echo ('</TR>');

foreach ($users_user_name as $user_name) 
{
    echo ('<TR>');
    
    echo ('<TD align=left>');
    echo ($users_full_name[$user_name]);
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ($user_name);
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ($users_affiliation[$user_name]);
    echo ('</TD>');

    echo ('<TD align=left>');
    if (!isnull($users_email[$user_name]))
      echo ('<a href="mailto:'.$users_email[$user_name].'">'.$users_email[$user_name].'</a>');
    echo ('</TD>');
    
    if ((strpos($_SESSION['privileges'], "admin") !== false) 
	|| (strcmp($_SESSION['user_name'], $user_name) == 0))
    {
	echo ('<TD align=center>'); 
	echo ('<FORM action="edit_user.php" method="post">');
	echo ('<input type="image" src="pixmaps/edit.png" title="Edit User Information">');
	echo ('<input type="hidden" name="edit_user" value='.$user_name.'>');
	echo ('</FORM>');
	echo ('</TD>');
    }

    if (strpos($_SESSION['privileges'], "admin") !== false)
    {
	echo ('<TD align=center>'); 
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<input type="image" src="pixmaps/drop.png" title="Delete User">');
	echo ('<input type="hidden" name="del_user" value='.$user_name.'>');
	echo ('</FORM>');
	echo ('</TD>');
    }

    echo ('</TR>');
    
}
echo ('</TABLE>');

if (strpos($_SESSION['privileges'], "admin") !== false)
{
    echo ('<br>');
    echo ('<FORM action="edit_user.php" method="post">');
    echo ('Add new user: <input type="text" name="new_user_name" size = 32>');
    echo ('<BUTTON type="submit"> New </BUTTON>');
    echo ('</FORM>');
}

echo(' </body>');
echo ('</HTML>');
?>
