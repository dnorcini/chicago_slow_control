<?php
// general_fns.php
// Part of the astro slow control system.  
// James Nikkel, 2006, 2010, 2016
// james.nikkel@yale.edu
//
// Presumably there are better ways to write 
// these functions, but here we are.
//

function make_unique($in_array)
{
  // Takes and array of strings in $in_array, finds all of the
  // unique values and returns them using $out_array.  
  // $out_array will have size between 1 and count($in_array).
  $out_array = [];

  $in_array = array_values($in_array);

  $out_array[] = $in_array[0];

  for ($i = 1; $i < count($in_array); $i++) {
    $comp = 0;
    for ($j = 0; $j < count($out_array); $j++)
      $comp += !strcmp($in_array[$i], $out_array[$j]);
    if ($comp == 0)
      $out_array[] = $in_array[$i];
  }
  return ($out_array);
}

function isnull($strng)
{
  if ($strng == "")
    return (1);
  if ($strng == " ")
    return (1);
  if ($strng == "null")
    return (1);
  if ($strng == "NULL")
    return (1);
  else
    return (0);
}

function check_hostname($allowed_host)
{
  if (strcmp($allowed_host, "all") == 0)
    return (1);

  $user_host_bits =  explode(".", $_SERVER['REMOTE_ADDR']);
  $allowed_host_bits =  explode(".", $allowed_host);

  for ($i = 0; $i < count($allowed_host_bits); $i++)
    if ((int)$user_host_bits[$i] != (int)$allowed_host_bits[$i])
      return (0);
  return (1);
}

function check_access($user_privs, $req_privs, $allowed_host_array)
{
  // Checks a user privilege list against the required list.
  // $user_privs is a comma separated string containing all the user
  // privileges.  $req_privs is a comma separated string containing 
  // all the required privileges.  $allowed_host_array is an
  // associated array of $allowed_host => $priv_name
  // Returns 1 if all privileges are satisfies, 0 otherwise. 

  $have_priv = 0;
  $req_privs = explode(",", $req_privs);
  foreach ($req_privs as $priv) {
    if (!str_contains($user_privs, $priv))
      return (0);
    else
      for ($i = 0; $i < count($allowed_host_array[0]); $i++) {
        if (strcmp($allowed_host_array[0][$i], $priv) == 0) {
          if (check_hostname($allowed_host_array[1][$i]))
            $have_priv++;
        }
      }
  }
  if ($have_priv > 0)
    return (1);
  else
    return (0);
}
