<?php
  // alarm_button.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006, 2010, 2011
  // james.nikkel@yale.edu
  //

if (strpos($_SESSION['privileges'], "full") === false)
  {
    echo ('<br>');
    echo ('<br>');
    echo ('You do not have clearance to view this page');
    echo ('<br>');
    exit();
  }

//  $master_alarm_name and $master_alarm_on are defined in slow_control_page_setup.php
//  and read from the globals table. 

if (!(empty($_POST['ack_alarm'])) && ($master_alarm_on == 1))
  {
    mysql_close($connection);
    include("master_db_login.php");

    $query = "UPDATE `globals` SET `int1`=0, `int2`=0, `int3`=0, `int4`=0 WHERE `name` = \"".$master_alarm_name."\"";
    $result = mysql_query($query);  
    
    $query = "UPDATE `globals` SET `string4` = \"".$_SESSION['user_name']."\" WHERE `name` = \"".$master_alarm_name."\"";
    $result = mysql_query($query);  
    
    $ack_mesg = "Alarm acknowledged by ".$_SESSION['user_name'].".";
    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$ack_mesg."', 'Alarm', '0')"; 
    $result = mysql_query($query);

    $master_alarm_on = 0;
  }

if ($master_alarm_on)
  {
    echo ('<TABLE border="4" cellpadding="4" width=100%>');
    echo ('<TH>');
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<input type="submit" name="ack_alarm" value="Acknowledge Current Alarm" title="Acknowledge Current Alarm" style="font-size: 30pt; color: red">');
    echo ('</FORM>');
    echo ('</TH>');
    echo ('</TABLE>');
  }
?>
