#!/bin/bash
# add to /usr/local/bin/ to use slow_start service
export LD_LIBRARY_PATH=/usr/local/lib

#if ungraceful shutdown, reset Watchdog parameters in database so it can restart
mysql -u root -pMyLife4Aiur control -Bse "UPDATE sc_insts SET PID=-1, start_time=-1, last_update_time=-1 WHERE name='Watchdog';"
echo "Watchdog entry in database reset..."

#run Watchdog program
/home/damic/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog Watchdog
echo "Watchdog is now running..."
