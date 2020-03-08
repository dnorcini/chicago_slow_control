<?php
  // login.php
  // James Nikkel, Yale University, 2009, 2010
  // james.nikkel@yale.edu
  //
if (!(get_magic_quotes_gpc()))
{
    $_POST['user_name'] = addslashes($_POST['user_name']);
    $_POST['password'] = addslashes($_POST['password']);
}
$query = "SELECT `user_name`, `shift_status`, `privileges` FROM `users` 
        WHERE `user_name`='".$_POST['user_name']."' AND `password`=MD5('".$_POST['password']."') LIMIT 1";
$result = mysql_query($query);
if (!$result)	
    die("Could not query the database.<br>" . mysql_error());
	
if (! ($row = mysql_fetch_array($result, MYSQL_ASSOC)))
    echo('Username or password incorrect.<br>');
	
else
{
    $_SESSION['user_name'] = $row['user_name'];
    $_SESSION['shift_status'] = $row['shift_status'];
    $_SESSION['privileges'] = $row['privileges'];
}
?>
