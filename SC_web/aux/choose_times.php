<?php
  // choose_times.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006, 2011
  // james.nikkel@yale.edu
  //

if (empty($_SESSION['t_max_now']))
{
    $_SESSION['t_max_now'] = 1;
}

if (!(empty($_POST['choose_time_start'])))
{
    $choose_time_start=$_POST['choose_time_start'];
    if (!(empty($choose_time_start)))
    {
	if (($choose_time_start == "all") || ($choose_time_start == "start"))
	    $_SESSION['t_min_p'] = $_SESSION['t_min'];
	else if ($choose_time_start{0} == "-")                                           
	{                      
	    if ($choose_time_start{1} == "-")                                            
		$_SESSION['t_min_p'] -= str_to_delay(substr($choose_time_start, 2));          ///  -- move start time back by x 
	    else
		$_SESSION['t_min_p'] = time() - str_to_delay(substr($choose_time_start, 1));  ///  -  subtract x from end of data
	}
	else if ($choose_time_start{0} == "+")                                           
	{
	    if ($choose_time_start{1} == "+")                                            
		$_SESSION['t_min_p'] += str_to_delay(substr($choose_time_start, 2));                     ///  ++  move start time forward by x 
	    else
		$_SESSION['t_min_p'] = $_SESSION['t_min'] + str_to_delay(substr($choose_time_start, 1)); ///  +   add x from begining of the data
	    $_SESSION['t_max_now'] = -1;
	}
	else
	{
	    if (strtotime($choose_time_start) != false)
		$_SESSION['t_min_p']=strtotime($choose_time_start);
	}
    }
}

if (!(empty($_POST['choose_time_stop'])))
{
    $choose_time_stop=$_POST['choose_time_stop'];
    if (!(empty($choose_time_stop)))
    {
	if (($choose_time_stop == "now") || ($choose_time_stop == "all") || ($choose_time_stop == "end"))
	{
	    $_SESSION['t_max_now'] = 1;
	}
	else if ($choose_time_stop{0} == "-")                                             
	{                                                           
	    if ($choose_time_stop{1} == "-")                                              
		$_SESSION['t_max_p'] -= str_to_delay(substr($choose_time_stop, 2));          ///  -- move end time back by x
	    else
		$_SESSION['t_max_p'] = time() - str_to_delay(substr($choose_time_stop, 1));  ///  -  subtract x from end of data  
	    $_SESSION['t_max_now'] = -1;
	}
	else if ($choose_time_stop{0} == "+")
	{
	    if ($choose_time_stop{1} == "+")
		$_SESSION['t_max_p'] += str_to_delay(substr($choose_time_stop, 2));                        ///  ++  move end time forward by x 
	    else
		$_SESSION['t_max_p'] = $_SESSION['t_min_p'] + str_to_delay(substr($choose_time_stop, 1));  ///  +   add x from start time above
	    $_SESSION['t_max_now'] = -1;
	}
	else
	{
	    if (strtotime($choose_time_stop) != false)
	    {
		$_SESSION['t_max_p']=strtotime($choose_time_stop);
		$_SESSION['t_max_now'] = -1;
	    }
	}
    }
}

if ($_SESSION['t_min_p'] < $_SESSION['t_min'])
    $_SESSION['t_min_p'] = $_SESSION['t_min'];


if ($_SESSION['t_max_p'] > $_SESSION['t_max'])
{
    $_SESSION['t_max_now'] = 1;
    $_SESSION['t_max_p'] = $_SESSION['t_max'];
}

if ($_SESSION['t_max_now'] == 1)
    $_SESSION['t_max_p'] = time();  


if ($_SESSION['t_min_p'] < $_SESSION['t_min'])
    $_SESSION['t_min_p'] = $_SESSION['t_min'];



echo ('<TABLE border="1" cellpadding="2" width=100%>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="choose_time_start" value="start">');
echo ('<TH>');
echo ('<input type="image" src="pixmaps/to_start.png" alt="To Start" title="Start plot at beginning of the data">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('Begin time/date: <input type="text" name="choose_time_start" value="'.date("G:i:s  M d, y", $_SESSION['t_min_p']).'" >');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('End time/date: <input type="text" name="choose_time_stop" value="'.date("G:i:s  M d, y", $_SESSION['t_max_p']).'" >');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="choose_time_stop" value="end">');
echo ('<TH>');
echo ('<input type="image" src="pixmaps/to_end.png" alt="To End" title="Stop plot at the end of the data">');
echo ('</TH>');
echo ('</FORM>');

echo ('<TH>');
if ($_SESSION['t_max'] < time() - 10*60)
  echo ('<font color="red">');
echo ('Last db update: '.date("M d, Y @ G:i:s", $_SESSION['t_max']));
echo ('</TH>');

echo ('</TABLE>');
?>
