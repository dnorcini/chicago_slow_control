<?php
// get_users.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009, 2010
// james.nikkel@yale.edu
//

$query = "SELECT * FROM `users` ORDER BY `on_call` DESC, `shift_status` DESC, `affiliation`, `user_name`";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not get users table <br />" . mysql_error());
}

$users_user_name = array();
$users_full_name = array();
$users_affiliation = array();
$users_email = array();
$users_sms = array();
$users_phone = array();
$users_on_call = array();
$users_shift_status = array();
$users_privileges = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $users_user_name[] = $row['user_name'];
    $users_full_name[] = $row['full_name'];
    $users_affiliation[] = $row['affiliation'];
    $users_email[] = $row['email'];
    $users_sms[] = $row['sms'];
    $users_phone[] = $row['phone'];
    $users_on_call[] = (int)$row['on_call'];
    $users_shift_status[] = $row['shift_status'];
    $users_privileges[] = $row['privileges'];
} 
$users_full_name = array_combine($users_user_name, $users_full_name);
$users_affiliation = array_combine($users_user_name, $users_affiliation);
$users_email = array_combine($users_user_name, $users_email);
$users_sms = array_combine($users_user_name, $users_sms);
$users_phone = array_combine($users_user_name, $users_phone);
$users_on_call = array_combine($users_user_name, $users_on_call);
$users_shift_status = array_combine($users_user_name, $users_shift_status);
$users_privileges = array_combine($users_user_name, $users_privileges);
?>
