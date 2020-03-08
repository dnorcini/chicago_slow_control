<?php
// choose_times.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2011
// james.nikkel@yale.edu
//

echo ('<TABLE border="1" cellpadding="2" width=100%>');	
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD>');
if (($inst_PIDs[$my_inst_name] == -1) && ($inst_run[$my_inst_name] == 0))
{
    echo ('Program not running -- ');
    echo ('<input type="submit" name="dummy" value="Start Daemon" title="Start Daemon">');
    echo ('<input type="hidden" name="start_stop_inst" value="1" title="Start Daemon">');
}

else if (($inst_PIDs[$my_inst_name] == -1) && ($inst_run[$my_inst_name] == 1))
    echo ('Program set to run, but has not started yet.  Wait or go to config page.');	

else if (($inst_PIDs[$my_inst_name] > -1) && ($inst_run[$my_inst_name] == 0))
    echo ('Program set to quit, but has not ended yet.  Wait or go to config page.');	

else if (($inst_PIDs[$my_inst_name] > -1) && ($inst_run[$my_inst_name] == 1))
{
    if ($inst_last_update_times[$my_inst_name] < time() - 10*60)
    {
	echo ('<font color="red">');
	echo ('Program set to run, but is not updating.  Go to config page to reset.');	
   }
    else
    {
	echo ('Program currently running -- ');
	echo ('<input type="submit" name="dummy" value="Stop Daemon" title="Stop Daemon">');
	echo ('<input type="hidden" name="start_stop_inst" value="0" title="Stop Daemon">');
    }
}
echo ('</TD>');
echo ('</FORM>');
echo ('</TABLE>');
?>
