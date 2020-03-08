<?php
// slow_control_edit_user.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
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
if ((strpos($_SESSION['privileges'], "admin") === false) && ($shift_can_manage[$_SESSION['shift_status']] == 0))
   $edit_user = $_SESSION['user_name'];

if (isset($_POST['new_user_name']) && (strpos($_SESSION['privileges'], "admin") !== false))
{
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
	$_POST['new_user_name'] = addslashes($_POST['new_user_name']);
    $_POST['new_user_name'] = preg_replace('/\s+/', '', $_POST['new_user_name']);
    
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
	$_POST['email'] = addslashes($_POST['email']);
	$_POST['sms'] = addslashes($_POST['sms']);
	$_POST['phone'] = addslashes($_POST['phone']);
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
    
     
    if (!empty($_POST['phone']))
      {
	if (strcmp($_POST['phone'], $users_phone[$edit_user]) != 0)
	  {
	    $query = "UPDATE `users` SET `phone` = \"".$_POST['phone']."\" WHERE `user_name` = \"".$edit_user."\""; 
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	  }
      }
    
    if ( (isset($_POST['on_call']) && (!$users_on_call[$edit_user])) || (!isset($_POST['on_call']) && ($users_on_call[$edit_user])) )
      {
      //if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
	  {
	    if ($users_on_call[$edit_user])
	      $query = "UPDATE `users` SET `on_call` = 0 WHERE `user_name` = \"".$edit_user."\"";
	    else
	      $query = "UPDATE `users` SET `on_call` = 1 WHERE `user_name` = \"".$edit_user."\"";
	    $result = mysql_query($query);
	    if (!$result)
		die ("Could not query the database <br />" . mysql_error());
	    
	    $mesg = "On Call status of ".$edit_user." changed to ".$_POST['on_call']." by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Shifts', '0')"; 
	    $result = mysql_query($query);
	  }
      }

    if (!empty($_POST['shift_status']))
      {
	if (strcmp($_POST['shift_status'], $users_shift_status[$edit_user]) != 0)
	  {
	    if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
	      {
		$query = "UPDATE `users` SET `shift_status` = \"".$_POST['shift_status']."\" WHERE `user_name` = \"".$edit_user."\"";
		$result = mysql_query($query);
		if (!$result)
		  die ("Could not query the database <br />" . mysql_error());
	    
		$mesg = "Shift status of ".$edit_user." changed to ".$_POST['shift_status']." by ".$_SESSION['user_name'].".";
		$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Shifts', '0')"; 
		$result = mysql_query($query);
	      }
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
    header('Location: slow_control_users.php');
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
    
    echo ('<TH align=left>');     // 5
    echo ('SMS address');  
    echo ('</TH>');
    
    echo ('<TH align=left>');     // 6
    echo ('Phone Number');  
    echo ('</TH>');
}

//if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
  {
    echo ('<TH align=left>');     // 7
    echo ('On Call');  
    echo ('</TH>');
  }

if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
  {
    echo ('<TH align=left>');     // 8
    echo ('Shift Status');  
    echo ('</TH>');
  }

if (strpos($_SESSION['privileges'], "admin") !== false)
  {
    echo ('<TH align=left>');     // 9
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
    
    echo ('<TD align=left>');
    echo ('<input type="text" name="sms" value="'.$users_sms[$edit_user].'"  size="16" autocomplete="off">');
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ('<input type="text" name="phone" value="'.$users_phone[$edit_user].'" size="14" autocomplete="off">');
    echo ('</TD>');
}

//if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
  {
    echo ('<TD align=center>');
    echo ('<input type="checkbox" name="on_call" ');
    if ($users_on_call[$edit_user])
      echo ('value="1" checked="checked" />');
    else
      echo ('value="1" />');
    echo ('</TD>');
  }

if ((strpos($_SESSION['privileges'], "admin") !== false) || $shift_can_manage[$_SESSION['shift_status']])
  {
    echo ('<TD align=left>');
    echo ('<SELECT name="shift_status" >');
    foreach ($shift_status_array as $ssa)
      {
	echo('<option ');
	if (strcmp($users_shift_status[$edit_user], $ssa) == 0)
	    echo ('selected="selected"');
	echo(' value="'.$ssa.'"> '.$ssa.'</option>');
    }
    echo ('</SELECT>');
    echo ('</TD>');
  }

if (strpos($_SESSION['privileges'], "admin") !== false)
{
    echo ('<TD align=left>');
    echo ('<SELECT name="privileges[]" style="font-size: 12pt" multiple=true size=4 >');
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
