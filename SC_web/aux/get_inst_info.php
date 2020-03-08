<?php
// get_inst_info.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2009, 2010
// james.nikkel@yale.edu
//

$query = "SELECT * FROM `sc_insts` ORDER BY `name`";
$result = mysql_query($query);
if (!$result)
{
    echo ("Could not find instruments table! <br>");
}
else
{
    $inst_names = array();
    $inst_descs = array();
    $inst_subsys = array();
    $inst_run  = array();
    $inst_restart = array();
    $inst_WD_ctrl = array();
    $inst_paths  = array();
    $inst_dev_types = array();
    $inst_dev_addys = array();
    $inst_start_times = array();
    $inst_last_update_times = array();
    $inst_PIDs = array();
    $inst_user1 = array();
    $inst_user2 = array();
    $inst_parm1 = array();
    $inst_parm2 = array();
    $inst_notes = array();
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
	$inst_names[] = $row['name'];
	$inst_descs[] = $row['description'];
	$inst_subsys[] = $row['subsys'];
	$inst_run[] = (int)$row['run'];
	$inst_restart[] = (int)$row['restart'];
	$inst_WD_ctrl[] = (int)$row['WD_ctrl'];
	$inst_paths[] = $row['path'];
	$inst_dev_types[] = $row['dev_type'];
	$inst_dev_addys[] = $row['dev_address'];
	$inst_start_times[] = (int)$row['start_time'];
	$inst_last_update_times[] = (int)$row['last_update_time'];
	$inst_PIDs[] = (int)$row['PID'];
	$inst_user1[] = $row['user1'];
	$inst_user2[] = $row['user2'];
	$inst_parm1[] = (double)$row['parm1'];
	$inst_parm2[] = (double)$row['parm2'];
	$inst_notes[] = $row['notes'];
    }
    
    $inst_descs = array_combine($inst_names, $inst_descs);
    $inst_subsys = array_combine($inst_names, $inst_subsys);
    $inst_run  = array_combine($inst_names, $inst_run);
    $inst_restart = array_combine($inst_names, $inst_restart);
    $inst_WD_ctrl = array_combine($inst_names, $inst_WD_ctrl);
    $inst_paths  = array_combine($inst_names, $inst_paths);
    $inst_dev_types = array_combine($inst_names, $inst_dev_types);
    $inst_dev_addys =array_combine($inst_names, $inst_dev_addys);
    $inst_start_times = array_combine($inst_names, $inst_start_times);
    $inst_last_update_times = array_combine($inst_names, $inst_last_update_times);
    $inst_PIDs = array_combine($inst_names, $inst_PIDs);
    $inst_user1 = array_combine($inst_names, $inst_user1);
    $inst_user2 = array_combine($inst_names, $inst_user2);
    $inst_parm1 = array_combine($inst_names, $inst_parm1);
    $inst_parm2 = array_combine($inst_names, $inst_parm2);
    $inst_notes = array_combine($inst_names, $inst_notes);
}
?>
	
