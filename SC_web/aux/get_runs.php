<?php
// get_runs.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010
// james.nikkel@yale.edu
//

$show_num = 7;

$run_nums = array();
$run_start_ts = array();
$run_end_ts = array();
$run_file_paths = array();
$run_file_roots = array();
$run_notes = array();

$query = "SELECT * FROM `runs` ORDER BY `num` DESC LIMIT 1";
$result = mysql_query($query);
if (!$result)
    die ("Could not find runs table. <br />" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	$run_nums[] = (int)$row['num'];
	$run_start_ts[] = (int)$row['start_t'];
	$run_end_ts[] = (int)$row['end_t'];
	$run_file_paths[] = $row['file_path'];
	$run_file_roots[] = $row['file_root'];
	$run_notes[] = $row['note'];
}

if (!isset($_SESSION['run_indx_offset']))
  $_SESSION['run_indx_offset'] = $run_nums[0];

if (isset($_POST['run_next']))
    $_SESSION['run_indx_offset'] += ($show_num-1);
if (isset($_POST['run_prev']))
   $_SESSION['run_indx_offset'] -= ($show_num-1);
if (isset($_POST['run_first']))
    $_SESSION['run_indx_offset'] = $show_num+1;
if (isset($_POST['run_last']))
    $_SESSION['run_indx_offset'] = $run_nums[0];

if ($_SESSION['run_indx_offset'] > $run_nums[0])
    $_SESSION['run_indx_offset'] = $run_nums[0];
if ($_SESSION['run_indx_offset'] <  $show_num+1)
    $_SESSION['run_indx_offset'] = $show_num+1;

if (isset($_POST['search_num']))       // find number   
{
    $_POST['search_num'] = (int)$_POST['search_num'];
    if ($_POST['search_num'] > $run_nums[0] - (int)(($show_num-1)/2))
	$_POST['search_num'] = $run_nums[0]+1;
    else if ($_POST['search_num'] < (int)(($show_num-1)/2) + 1)
	$_POST['search_num'] = $show_num+1;
    else
	$_POST['search_num'] += (int)(($show_num-1)/2)+1;
    $query = "SELECT * FROM `runs` WHERE `num` < ".$_POST['search_num']." ORDER BY `num` DESC LIMIT ".$show_num;
}
else if (isset($_POST['search_text']))   // find text 
{
    if (!get_magic_quotes_gpc())   
	$_POST['search_text'] = addslashes($_POST['search_text']);
    $query = "SELECT * FROM `runs` WHERE `note` LIKE '%".$_POST['search_text']."%' ORDER BY `num` DESC LIMIT ".$show_num;
}
else
    $query = "SELECT * FROM `runs` WHERE `num` < ".$_SESSION['run_indx_offset']." ORDER BY `num` DESC LIMIT ".$show_num;
$result = mysql_query($query);
if (!$result)
    die ("Could not find runs table. <br />" . mysql_error());


while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $run_nums[] = (int)$row['num'];
    $run_start_ts[] = (int)$row['start_t'];
    $run_end_ts[] = (int)$row['end_t'];
    $run_file_paths[] = $row['file_path'];
    $run_file_roots[] = $row['file_root'];
    $run_notes[] = $row['note'];
}
$run_start_ts = array_combine($run_nums, $run_start_ts);
$run_end_ts = array_combine($run_nums, $run_end_ts);
$run_file_paths = array_combine($run_nums, $run_file_paths);
$run_file_roots = array_combine($run_nums, $run_file_roots);
$run_notes = array_combine($run_nums, $run_notes);
?>
