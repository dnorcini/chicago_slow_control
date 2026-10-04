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

function dark_current_per_day($dark_current, $gain, $divisor)
{
  // Dark current [ADU/bin/img] -> [e-/pix/day], as shown on the edit pages:
  // dark_current / gain * 86400 / $divisor.
  // Returns "" when a value is missing or the gain is 0 (PHP 8 throws on / 0).
  if (!is_numeric($dark_current) || !is_numeric($gain) || $gain == 0)
    return "";
  return number_format($dark_current / $gain * 86400 / $divisor, 2);
}

function grade_tally($grades)
{
  // Pokemon tally of a die/module from its 4 amp/CCD grades, same rule as the
  // summary pages: 4 Science = Charizard, 3 = Charmeleon, 1-2 = Charmander,
  // 0 = Geodude. Returns "" until all four grades are filled in.
  if (count(array_filter($grades)) < 4)
    return "";
  $n_science = count(array_keys($grades, "Science", true));
  if ($n_science == 4) return "Charizard";
  if ($n_science == 3) return "Charmeleon";
  if ($n_science >= 1) return "Charmander";
  return "Geodude";
}
