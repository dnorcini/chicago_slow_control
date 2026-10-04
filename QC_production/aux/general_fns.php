<?php
// general_fns.php
// Part of the astro slow control system.  
// James Nikkel, 2006, 2010, 2016
// james.nikkel@yale.edu
//

function check_access($user_privs, $req_privs)
{
  // Checks a user privilege list against the required list.
  // $user_privs is a comma separated string containing all the user
  // privileges.  $req_privs is a comma separated string containing 
  // all the required privileges.
  // Returns 1 if all privileges are satisfied, 0 otherwise. 

  $have_privs = array_map('trim', explode(",", (string)$user_privs));
  foreach (explode(",", (string)$req_privs) as $priv) {
    if (!in_array(trim($priv), $have_privs, true))
      return (0);
  }
  return (1);
}
