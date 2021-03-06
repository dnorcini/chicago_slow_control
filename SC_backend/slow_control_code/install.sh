#!/bin/bash
#   Install script for the slow control libraries and
#   start up script.
#
#   James Nikkel <james.nikkel@yale.edu>
#
#   edited: D. Norcini, UChicago, 2020
#
#   Edit this if you want the library files somewhere else
_lib_path="/usr/local/lib"
#_lib_path="/home/damic/SC_backend/slow_control_code/lib/"

#  Edit this if you want the install script somewhere else in your path
_start_script="/usr/local/bin/slow_start.sh"

#ln -s $PWD/lib/lib* $_lib_path

ln -s /home/damic/SC_backend/slow_control_code/lib/lib* /usr/local/lib/

printf "#!/bin/bash\n" > $_start_script
printf "export LD_LIBRARY_PATH="$_lib_path"\n" >> $_start_script
#printf "$PWD/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog Watchdog\n" >> $_start_script
printf "/home/damic/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog Watchdog\n" >> $_start_script
printf "echo \"Watchdog is now running...\"\n" >> $_start_script

chmod u+x $_start_script

ldconfig


#   Remember to add the start script to your system startup system if
#   you want the slow control system to start automatically upon boot


