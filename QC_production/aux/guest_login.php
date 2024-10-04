<?php
// guest_login.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//
if (empty($_SESSION['user_name']))
{ 
    $user_name="guest";
    $password="guest";
    
    //check user/pass in DB
    $query = "SELECT `user_name` , `shift_status`, `privileges` FROM `users` 
        WHERE `user_name`='".$user_name."' AND `password`=MD5('".$password."') LIMIT 1";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    if (! ($row = mysql_fetch_array($result, MYSQL_ASSOC)))
    {
	echo "The guest account seems to be broken.  Please contact the administrator.";
	exit;
    }
    $_SESSION['user_name'] = $row['user_name'];
    $_SESSION['shift_status'] = $row['shift_status'];
    $_SESSION['privileges'] = $row['privileges'];
}
?>
