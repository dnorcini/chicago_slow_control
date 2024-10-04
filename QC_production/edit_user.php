<?php
// edit_user.php
// Part of the astro slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("page_setup.php");
include("aux/get_users.php");

if (isset($_POST['edit_user']))
{
    if (!get_magic_quotes_gpc())
        $_POST['edit_user'] = addslashes($_POST['edit_user']);
    $edit_user = $_POST['edit_user'];
}
else
    $edit_user = $_SESSION['user_name'];

///////////////////////  only allow non-personal edits for admin and managers (managers can only edit shift status)
if ((strpos($_SESSION['privileges'], "admin") === false))
   $edit_user = $_SESSION['user_name'];

if (isset($_POST['new_user_name']) && (strpos($_SESSION['privileges'], "admin") !== false))
{
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
	$_POST['new_user_name'] = addslashes($_POST['new_user_name']);
    
    $edit_user = $_POST['new_user_name'];
    $query = "INSERT into `users` (`user_name`) VALUES ('".$_POST['new_user_name']."')"; 
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (isset($_POST['change']))
{
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
    {
	$_POST['realname'] = addslashes($_POST['realname']);
	$_POST['affiliation'] = addslashes($_POST['affiliation']);
    }
    
    if (!empty($_POST['password']))
    {
	$query = "UPDATE `users` SET `password` = \"".md5($_POST['password'])."\" WHERE `user_name` = \"".$edit_user."\""; 
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }

    if (!empty($_POST['old_password']))
    {
	$query = "SELECT `user_name` FROM `users` 
        WHERE `user_name`='".$edit_user."' AND `password`=MD5('".$_POST['old_password']."') LIMIT 1";
	$result = mysql_query($query);
	if (! ($row = mysql_fetch_array($result, MYSQL_ASSOC)))
	    die ("Old Password incorrect <br />");
	if (strcmp($_POST['new_password1'],$_POST['new_password2']) != 0 )
	    die ("New Passwords are not identical <br />");
	
	$query = "UPDATE `users` SET `password` = \"".md5($_POST['new_password1'])."\" WHERE `user_name` = \"".$edit_user."\""; 
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
    
    if (!empty($_POST['realname']))
      {
	if (strcmp($_POST['realname'], $users_full_name[$edit_user]) != 0)
	  {
	    $query = "UPDATE `users` SET `full_name` = \"".$_POST['realname']."\" WHERE `user_name` = \"".$edit_user."\""; 
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	  }
      }
    
    if (!empty($_POST['affiliation']))
      { 
	if (strcmp($_POST['affiliation'], $users_affiliation[$edit_user]) != 0)
	  {
	    $query = "UPDATE `users` SET `affiliation` = \"".$_POST['affiliation']."\" WHERE `user_name` = \"".$edit_user."\""; 
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	  }
      }
    
    if (!empty($_POST['email']))
      {
	if (strcmp($_POST['email'], $users_email[$edit_user]) != 0)
	  {
	    $query = "UPDATE `users` SET `email` = \"".$_POST['email']."\" WHERE `user_name` = \"".$edit_user."\""; 
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	  }
      }
    
    if (!empty($_POST['sms']))
      {
	if (strcmp($_POST['sms'], $users_sms[$edit_user]) != 0)
	  {
	    $query = "UPDATE `users` SET `sms` = \"".$_POST['sms']."\" WHERE `user_name` = \"".$edit_user."\""; 
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	  }
      }
    
    if (!empty($_POST['privileges']))
      {
	$_POST['privileges'] = implode(",",$_POST['privileges']);
	if (strcmp($_POST['privileges'], $users_privileges[$edit_user]) != 0)
	  {
	    if (strpos($_SESSION['privileges'], "admin") !== false)
	      {
		$query = "UPDATE `users` SET `privileges` = \"".$_POST['privileges']."\" WHERE `user_name` = \"".$edit_user."\"";
		$result = mysql_query($query);
		if (!$result)
		  die ("Could not query the database <br />" . mysql_error());
	      }
	  }
      }
    header('Location: users.php');
 }
mysql_close($connection);


////////////////////   HTML starts here:
echo ('<br>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TABLE border="1" cellpadding="4" cellspacing="2">');
echo ('<TR>');

echo ('<TH align=left>');         // 1 
echo ('Full Name');                
echo ('</TH>');

if ((strcmp($_SESSION['user_name'], $edit_user) == 0) || (strpos($_SESSION['privileges'], "admin") !== false))
{
    echo ('<TH align=left>');     // 2
    echo ('Affiliation');   
    echo ('</TH>');
    
    echo ('<TH align=left>');     // 3
    echo ('Password');  
    echo ('</TH>');
    
    echo ('<TH align=left>');     // 4
    echo ('Email address');  
    echo ('</TH>');
 
}


if (strpos($_SESSION['privileges'], "admin") !== false)
  {
    echo ('<TH align=left>');     // 5
    echo ('Privileges');  
    echo ('</TH>');
  }

echo ('<TH align=left>'); 
echo ('</TH>');
echo ('<TBODY>');
echo ('</TR>');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ((strcmp($_SESSION['user_name'], $edit_user) == 0) || (strpos($_SESSION['privileges'], "admin") !== false))
{
    echo ('<TR>');
    echo ('<TD align=left>');
    echo ('<input type="text" name="realname" value="'.$users_full_name[$edit_user].'" size="16" autocomplete="off">');
    echo ('</TD>');
}
else
{
    echo ('<TR>');
    echo ('<TD align=left>');
    echo ($users_full_name[$edit_user]);
    echo ('</TD>');
}

if ((strcmp($_SESSION['user_name'], $edit_user) == 0) || (strpos($_SESSION['privileges'], "admin") !== false))
{
    echo ('<TD align=left>');
    echo ('<input type="text" name="affiliation" value="'.$users_affiliation[$edit_user].'" size="16" autocomplete="off">');
    echo ('</TD>');
    
    
    if (strcmp($edit_user, $_SESSION['user_name']) == 0)
    {
	echo ('<TH align=left>');
	echo ('Old: <input type="password" name="old_password" size="12" autocomplete="off"> <br>');
	echo ('New: <input type="password" name="new_password1" size="12" autocomplete="off"> <br>');
	echo ('New: <input type="password" name="new_password2" size="12" autocomplete="off">');
	echo ('</TH>');
    }
    else
    {
	echo ('<TH align=left>');
	echo ('<input type="password" name="password" size="12" autocomplete="off">');
	echo ('</TH>');
    }
    
    echo ('<TD align=left>');
    echo ('<input type="text" name="email" value="'.$users_email[$edit_user].'"  size="16" autocomplete="off">');
    echo ('</TD>');
 
}

if (strpos($_SESSION['privileges'], "admin") !== false)
{
    echo ('<TD align=left>');
    echo ('<SELECT name="privileges[]" style="font-size: 12pt" multiple=true size=4>');
    foreach ($privilege_array as $pa)
    {
	echo('<option ');
	if (strpos($users_privileges[$edit_user], $pa) !== false)
	    echo ('selected="selected"');
	echo(' value="'.$pa.'">  '.$pa.'  </option>');
    }
    echo ('</SELECT>');
    echo ('</TD>');
}

echo ('<TD align=left>');
echo ('<input type="hidden" name="edit_user" value="'.$edit_user.'">');
echo ('<input type="submit" name="change" value="Change">');
echo ('</TD>');

echo ('</TR>');

echo ('</TABLE>');
echo ('</FORM>');

echo(' </body>');
echo ('</HTML>');
?>
