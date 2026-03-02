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

$LONG_QUERY_WARN_SECONDS = 92 * 24 * 3600; // ~3 months
$confirm_long_query = !empty($_POST['confirm_long_query']);

$t_min_p_new = $_SESSION['t_min_p'];
$t_max_p_new = $_SESSION['t_max_p'];
$t_max_now_new = $_SESSION['t_max_now'];

$posted_start = (isset($_POST['choose_time_start']) ? $_POST['choose_time_start'] : null);
$posted_stop  = (isset($_POST['choose_time_stop'])  ? $_POST['choose_time_stop']  : null);

if (!empty($posted_start))
{
    $choose_time_start = $posted_start;
    if (($choose_time_start == "all") || ($choose_time_start == "start"))
	$t_min_p_new = $_SESSION['t_min'];
    else if ($choose_time_start{0} == "-")
    {
	if ($choose_time_start{1} == "-")
	    $t_min_p_new -= str_to_delay(substr($choose_time_start, 2));          ///  -- move start time back by x
	else
	    $t_min_p_new = time() - str_to_delay(substr($choose_time_start, 1));  ///  -  subtract x from end of data
    }
    else if ($choose_time_start{0} == "+")
    {
	if ($choose_time_start{1} == "+")
	    $t_min_p_new += str_to_delay(substr($choose_time_start, 2));                     ///  ++  move start time forward by x
	else
	    $t_min_p_new = $_SESSION['t_min'] + str_to_delay(substr($choose_time_start, 1)); ///  +   add x from begining of the data
	$t_max_now_new = -1;
    }
    else
    {
	if (strtotime($choose_time_start) != false)
	    $t_min_p_new = strtotime($choose_time_start);
    }
}

if (!empty($posted_stop))
{
    $choose_time_stop = $posted_stop;
    if (($choose_time_stop == "now") || ($choose_time_stop == "all") || ($choose_time_stop == "end"))
    {
	$t_max_now_new = 1;
    }
    else if ($choose_time_stop{0} == "-")
    {
	if ($choose_time_stop{1} == "-")
	    $t_max_p_new -= str_to_delay(substr($choose_time_stop, 2));          ///  -- move end time back by x
	else
	    $t_max_p_new = time() - str_to_delay(substr($choose_time_stop, 1));  ///  -  subtract x from end of data
	$t_max_now_new = -1;
    }
    else if ($choose_time_stop{0} == "+")
    {
	if ($choose_time_stop{1} == "+")
	    $t_max_p_new += str_to_delay(substr($choose_time_stop, 2));                        ///  ++  move end time forward by x
	else
	    $t_max_p_new = $t_min_p_new + str_to_delay(substr($choose_time_stop, 1));          ///  +   add x from start time above
	$t_max_now_new = -1;
    }
    else
    {
	if (strtotime($choose_time_stop) != false)
	{
	    $t_max_p_new = strtotime($choose_time_stop);
	    $t_max_now_new = -1;
	}
    }
}

if ($t_min_p_new < $_SESSION['t_min'])
    $t_min_p_new = $_SESSION['t_min'];

if ($t_max_p_new > $_SESSION['t_max'])
{
    $t_max_now_new = 1;
    $t_max_p_new = $_SESSION['t_max'];
}

if ($t_max_now_new == 1)
    $t_max_p_new = time();

if ($t_min_p_new < $_SESSION['t_min'])
    $t_min_p_new = $_SESSION['t_min'];

$span = $t_max_p_new - $t_min_p_new;

$is_post = (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'POST'));
$fingerprint = $t_min_p_new . '-' . $t_max_p_new;
$already_confirmed = (!empty($_SESSION['long_query_confirmed_range']) && ($_SESSION['long_query_confirmed_range'] === $fingerprint));

if ($is_post && ($span > $LONG_QUERY_WARN_SECONDS) && !$confirm_long_query && !$already_confirmed)
{
    $days = (int)ceil($span / (24 * 3600.0));
    $msg = "You selected a time range of about ".$days." days. This query may take a long time. Continue?";

    echo('<div style="border:1px solid #cc8800; background:#fff3cd; color:#664d03; padding:10px; margin:10px 0;">');
    echo('<b>Warning:</b> ' . htmlspecialchars($msg, ENT_QUOTES) . '<br>');
    echo('If you continue, the page will submit again and run the query.');
    echo('</div>');

    echo('<form id="sc_long_query_confirm_form" action="'.$_SERVER['PHP_SELF'].'" method="post">');
    if (!empty($posted_start))
	echo('<input type="hidden" name="choose_time_start" value="'.htmlspecialchars($posted_start, ENT_QUOTES).'">');
    if (!empty($posted_stop))
	echo('<input type="hidden" name="choose_time_stop" value="'.htmlspecialchars($posted_stop, ENT_QUOTES).'">');
    echo('<input type="hidden" name="confirm_long_query" value="1">');
    echo('<noscript><input type="submit" value="Continue"></noscript>');
    echo('</form>');

    echo('<script type="text/javascript">');
    echo('(function(){');
    echo('var ok = confirm('.json_encode($msg).');');
    echo('if(ok){ document.getElementById("sc_long_query_confirm_form").submit(); }');
    echo('else { window.location = '.json_encode($_SERVER['PHP_SELF']).'; }');
    echo('})();');
    echo('</script>');

    $GLOBALS['SC_LONG_QUERY_CONFIRM_REQUIRED'] = 1;
    return;
}

$_SESSION['t_min_p'] = $t_min_p_new;
$_SESSION['t_max_p'] = $t_max_p_new;
$_SESSION['t_max_now'] = $t_max_now_new;

if (($_SESSION['t_max_p'] - $_SESSION['t_min_p']) > $LONG_QUERY_WARN_SECONDS)
{
    if ($confirm_long_query)
	$_SESSION['long_query_confirmed_range'] = $_SESSION['t_min_p'] . '-' . $_SESSION['t_max_p'];
}
else
{
    if (!empty($_SESSION['long_query_confirmed_range']))
	unset($_SESSION['long_query_confirmed_range']);
}

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
