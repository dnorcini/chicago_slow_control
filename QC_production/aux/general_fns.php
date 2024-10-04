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
    $out_array = array();

    $in_array = array_values($in_array);

    $out_array[] = $in_array[0]; 

    for ($i=1; $i < count($in_array); $i++)
    {
	$comp = 0;
	for ($j=0; $j < count($out_array); $j++)
	    $comp += !strcmp($in_array[$i], $out_array[$j]);
	if ($comp == 0)
	    $out_array[] = $in_array[$i]; 
    }
    return($out_array);
}

function make_new_zero_array($array_size)
{
    $out_array = array();
    for ($i=0; $i < $array_size; $i++)
	$out_array[] = 0;
    return($out_array);
}

function make_new_ones_array($array_size)
{
    $out_array = array();
    for ($i=0; $i < $array_size; $i++)
	$out_array[] = 1;
    return($out_array);
}

function make_new_index_array($array_size)
{
    $out_array = array();
    for ($i=0; $i < $array_size; $i++)
	$out_array[] = $i;
    return($out_array);
}

function make_new_data_array_dx($x0, $x1, $dxN)
{
    $N = ($x1 - $x0)*$dxN + 1;
    $out_array = array();
    for ($i=0; $i < $N; $i++)
      $out_array[] = $x0+$i/$dxN;
    return($out_array);
}

function make_new_data_array_N($x0, $x1, $N)
{
  $dxN = 1.0*($N - 1)/($x1 - $x0);
  $out_array = array();
  for ($i=0; $i < $N; $i++)
    $out_array[] = $x0+$i/$dxN;
  return($out_array);
}

function make_button($button_txt)
{
  $B1=$_POST['B1'];
  echo ('
     <FORM action="'.$_SERVER['PHP_SELF'].'" method="post">
     <P>
     <BUTTON name="B1" type="submit" value=1>
     '.$button_txt.'
     </BUTTON>
     </P>
     </FORM>  
    ');
  return($B1);
}

function TimeCallback($aVal) 
{
  return(Date('H:i:s',$aVal));
}

function DateCallback($aVal) 
{
  return(Date('M d,y',$aVal));
}

function DateCallback2($aVal) 
{
  return(Date('M d',$aVal));
}

function DateTimeCallback($aVal) 
{
  return(DateCallback($aVal)."\n".TimeCallback($aVal));
}

function str_to_delay($in_str)
{
    sscanf($in_str, "%d%s", $t_val, $t_type);
    if ($t_type{0} == "y")
	return($t_val*3600*24*365);
    if ($t_type{0} == "d")
	return($t_val*3600*24);
    if ($t_type{0} == "h")
	return($t_val*3600);
    if ($t_type{0} == "m")
	return($t_val*60);
    if ($t_type{0} == "s")
	return($t_val);
}

function reduce_array($in_array, $n)
{
  //  Takes $in_array and returns $out_array, such that the 
  //  size of $out_array is $n.
    
  $n_in = count($in_array);
    
  if ($n_in < 1.6*$n)
    return($in_array);
    
  $ratio = intval($n_in/$n);
  $out_array = array();
  for ($i = 0; $i < $n_in; $i+=$ratio)
    {
      $out_array[] = $in_array[$i];
    }

  return($out_array);
}

function format_num($num_in)
{
  //  Formats a number for writing on a page
  if ($num_in == 0)
    return("0");
  if (abs($num_in) < 1e-2)
    return(sprintf(" %1.3e ", $num_in));
  if (abs($num_in) >= 1e4)
    return(sprintf(" %1.3e ", $num_in));
  return(sprintf(" %1.3f ", $num_in));
}

function format_num2($num_in, $num_format)
{
  //  Formats a nunber according to $num_format,
  //  or falls back to format_num() if $num_format is null.
  if (strcmp($num_format, "null") == 0)
    {
      return(format_num($num_in));
    }
  else
    {
      return(sprintf($num_format, $num_in));
    }   
}

function integrate_array($x, $y)
{
  // Integrate an ordered pair of arrays using the trapezoid method.
  // Assumes the data is already sorted!
  $n=count($x);
  $integral = 0;
  for ($i = 0; $i < $n - 1; $i++)
    {
      $integral += ($x[$i+1] - $x[$i]) * ($y[$i+1] + $y[$i])/2.0; 
    }
  return($integral);
}

function mult_ab($a, $b)
{
    return($a * $b);
}


function add_ab($a, $b)
{
    return($a * $b);
}

function scale_array($array_in, $factor)
{
    for ($i = 0; $i < count($array_in); $i++)
    {
	$array_in[$i] *= $factor;
    }
    return($array_in);
}

function shift_array($array_in, $factor)
{
    for ($i = 0; $i < count($array_in); $i++)
    {
	$array_in[$i] += $factor;
    }
    return($array_in);
}

function linear_regression($x, $y)
{
  // Calculate the linear regression of an ordered pair of arrays.
  // Initially subtracts the minimum x value to prevent round-off errors for
  // large values.
    $n = 1.0*count($x);
    $x_min = 1.0*min($x);
    $x_max = 1.0*max($x);
    $y_min = 1.0*min($y);

    $x = shift_array($x, -1*$x_min);
    $y = shift_array($y, -1*$y_min);


    $sumx = 1.0*array_sum($x);
    $sumy = 1.0*array_sum($y);
    $sumxx = 1.0*array_sum( array_map("mult_ab", $x, $x) );
    $sumxy = 1.0*array_sum( array_map("mult_ab", $x, $y) );
    
    $slope = ($n*$sumxy -  $sumx*$sumy)/($n*$sumxx - $sumx*$sumx);
    
    $y_intercept = ($sumy - $slope*$sumx)/$n;
    $y_intercept -= $slope * $x_min;
    $y_intercept += $y_min;


    $y_fit_min = $slope * $x_min + $y_intercept;
    $y_fit_max = $slope * $x_max + $y_intercept;

    $output = array($y_fit_min, $y_fit_max, $slope, $y_intercept);
    return($output);
}

function interp_y($y, $slope, $y0)
{
    //y=mx+b;
    //x=(y-b)/m;
    return(($y-$y0)/$slope);
}

function interp_x($x, $slope, $y0)
{
    //y=mx+b;
    //x=(y-b)/m;
    return($x * $slope + $y0);
}

function geq(&$val, $key, $b)
{
    if ($val>=$b)
	$val = 1;
    else
	$val = 0;
}

function leq($a, $b)
{
    if ($a<=$b)
	return 1;
    return 0;
}

function closest_index($x, $x_array)
{
  $y_array = array_keys($x_array);
  return((int)find_y($x, $x_array, $y_array));
}

function find_y($x, $x_array, $y_array)
{
  if ($x < min($x_array))
    return $y_array[array_search(min($x_array), $x_array)];
  if ($x > max($x_array))
    return $y_array[array_search(max($x_array), $x_array)];
  if (in_array($x, $x_array))
    return $y_array[array_search($x, $x_array)];
 
  $xy_array = array_combine($y_array, $x_array);
  asort($xy_array);
  $x_array = array_values($xy_array);
  $y_array = array_keys($xy_array);

  $ind = $x_array;
  array_walk($ind, 'geq', $x);
  $ind = array_filter($ind);
  $ind = array_keys($ind);
  $ind = $ind[0];
    
  if (abs($x-$x_array[$ind]) < abs($x-$x_array[$ind - 1]))
    return($y_array[$ind]);
  return($y_array[$ind - 1]);
}

function abs_array($x)
{
  $n=count($x);
  for ($i = 0; $i < $n - 1; $i++)
    {
      $x[$i] = abs($x[$i]); 
    }
  return $x;
}

function make_data_file($x_array, $y_array, $file_name)
{
    touch($file_name);
    $f_h = fopen($file_name, "w");
    
    $n = count($x_array);

    for ($i = 0; $i < $n; $i++)
    {
	fwrite($f_h, $x_array[$i]);
	fwrite($f_h, " , ");
	fwrite($f_h, $y_array[$i]);
	fwrite($f_h, " \n ");
    }
    fclose($f_h);
}

function range_SI_units($units_in, $n)
{
  if ($n == 0)
    return($units_in);

  $SI_prefix = array(8 =>'y', 7 => 'z', 6 => 'a', 5 => 'f', 4 => 'p', 3 => 'n', 2 => 'u', 1 => 'm', 0 => '', 
		     -1 => 'k', -2 => 'M', -3 => 'G', -4 => 'T', -5 => 'P', -6 => 'E', -7 => 'Z', -8 => 'Y');
  $SI_flip = array_flip($SI_prefix);

  if (strlen($units_in) == 1)
    return($SI_prefix[$n] . $units_in);
    
    
  $prefix = substr($units_in, 0, 1);
  $units_out = substr($units_in, 1);
    
  if (in_array($prefix, $SI_prefix))
    {
      $n_in = 0;
	
      $n_in = $SI_flip[$prefix];
      return($SI_prefix[$n+$n_in] . $units_out);
    }
    
  else
    {
      return($SI_prefix[$n] . $units_in);
    }
}

function int_to_colour($n)
{  
  if ($n == 0)
    $c = "black";
  else if ($n == 1)
    $c = "white";
  else if ($n == 2)
    $c = "red";
  else if ($n == 3)
    $c = "green";
  else if ($n == 4)
    $c = "yellow";
  else if ($n == 5)
    $c = "magenta";
  else
    $c = "cyan";
  return $c;
}

function int_to_style($n)
{
  if ($n == 0)
    $s = "solid";
  else if ($n == 1)
    $s = "dotted";
  else
    $s = "dashed";
    
  return $s;
}

function string_to_array($string, $separator, $value = NULL)
{
  if (strlen($string) > 0) 
    {
      $splitter = explode($separator, $string);
      $index = array_shift($splitter); // get first element
      $function = __FUNCTION__;
      return array($index => $function(implode($separator, $splitter), $separator, $value));
    }
  return $value;
}


function interpolate_arrays($x_array, $y_array, $x_index, $y_index)
{
  // Generates new y_array so that the times that y is sampled at (y_index)
  // is the same as the times that x is sampled at (x_index)
    
  $y = $x_array;
    
  for ($i = 0; $i < count($x_index); $i++)
    {
      $y[$i] = find_y($x_index[$i], $y_index, $y_array);
    }
  return $y;
}

function isnull($strng)
{
  if ($strng == "")
    return(1);
  if ($strng == " ")
    return(1);
  if ($strng == "null")
    return(1);
  if ($strng == "NULL")
    return(1);
  else
    return(0);
}

function get_disc_units_val($value, $disc_array)
{
    $all_vals = explode(";",$disc_array);
    $av_v = explode(":", $all_vals[0]);
    foreach ($av_v as $temp_i => $temp) $av_v[$temp_i] = (double)$temp;  
    $av_s = explode(":", $all_vals[1]);
    $all_vals = array_combine($av_v, $av_s);
    
    return($all_vals[(string)$value]);
}

function check_hostname($allowed_host)
{
    if (strcmp($allowed_host, "all") == 0)
        return(1);
      
    $user_host_bits =  explode(".", $_SERVER['REMOTE_ADDR']);
    $allowed_host_bits =  explode(".", $allowed_host);
    
    for ($i = 0; $i < count($allowed_host_bits); $i++)
	if ((int)$user_host_bits[$i] != (int)$allowed_host_bits[$i])
	    return(0);
    return(1);
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
  foreach ($req_privs as $priv)
    {
      if (strpos($user_privs, $priv) === false)
	return(0);
      else
	for ($i = 0; $i < count($allowed_host_array[0]); $i++)
	  {
	    if (strcmp($allowed_host_array[0][$i], $priv) == 0)
	      {
		if (check_hostname($allowed_host_array[1][$i]))
		  $have_priv++;
	      }
	  }
    }
  if ($have_priv > 0)
    return(1);
  else
    return(0);
}

function any_in_array($needle_array, $haystack_array)
{
  foreach ($needle_array as $needle)
    if (in_array($needle, $haystack_array))
      return(true);
}
?>
