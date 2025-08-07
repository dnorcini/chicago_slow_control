# Debug Note for no response from Pressure Gauge in Slow Control
Cinyu Zhu, Hopkins, 7/1/2025

Sometimes it just happens....

usually can be fixed by reset the instrument and/or reset the watchdog (from the terminal not the interface)

**and sometimes it doesn't work**

## check the ethernet connection
`ping <ip address>`

## check the tcp connection
`nc <ip address> <port>`

(terminal command and SC can't work at the same time)

## check the backend .c file
- copy the content of `main.h` and replace the `#include main.h`
- save; `make clean`; `make`; `[path to the executable] [name of the inst]`(e.g. `./CenterThree CenterThree`) (repeat this many times)
- comment out `daemonize`; otherwise all scripts after that line will be detached and can't be print out in the terminal
- use `printf()` at anywhere possible to print the variables in the terminal.
- check the SQL database via phpmyadmin. check if write in and read out are performed correctly
- (it was not, the program is somehow reading another instrument `RIVATE` in the SQL?? -> fixed by changing the instrument name in 'instrument configuration' via web interface) this is also the reason why everytime I wanted to run the program in the commandline, it says `program is already running`, while there isn't a pid of CenterThree
- Anyways, after the issue is fixed, uncomment the `daemonize()` and run it again.
- then replace back to main.h; sudo kill all the pids; Reset the instrument in the SC config page

And hopefully this note would help next time it went wrong...


# update 7/15/25
confirmed from manual and with mike that the centerone gauge and our sensor (PTR90) and can't be turned off from the gauge control itself -> put it on sentry pdu outlet 8 for now

# update 7/16/25 changed its name to CenterOne
if you want to change the name of a instrument, the easiest way is to do it in phpmyadmin (so no need to create a new instrument), then update sensors and files