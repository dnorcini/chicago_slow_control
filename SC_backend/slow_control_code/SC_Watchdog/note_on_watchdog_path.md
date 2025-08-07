# SC watchdog fix note:
Cinyu Zhu, 07/24/2025

Shortly after a desperate debugging on the Sentry PDU in may, an issue on the slow control Watchdog appeared:

While the slow control functions well in every other aspect, it can no longer restart a executable of a decive, and one has to start it manually in the terminal with `./CenterOne `

The issue turns out to be in in web interface - config instrument - run path.

For every other instrument, we put in the PATH: 'foldername/executablename' (WIENER_VME/WIENER_VME). However for watchdog, we put in PATH the absolute path (/home/damicm/SC_backend/slow_control_code/) to the slowcontrol folder. 

This is because in the backend, watchdog will search for instruments whose pid = -1 (not running), while controlled by watchdog = 1 and run = 1 (should be running), and run the executable by combining the path of watchdog/slowcontrol folder and path to that instrument together (/home/damicm/SC_backend/slow_control_code/WIENER_VME/WIENER_VME). 

Since I mistakenly changed the path of the watchdog to `home /damicm/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog`, there is no way it can find other instrument under that folder.

(While killing a process in SC uses `killall name`, therefore unaffected.)

Anyways, **the correct way run Slow Control with Watchdog** is:
1. make sure no other watchdog pid (if there is, kill them)
2. start the watchdog manually in terminal`/home/damic/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog`
3. The Watchdog will start all other instruments automatically.