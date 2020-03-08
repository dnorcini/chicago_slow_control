/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2007,2008, 2009  */
/* James public licence. */

This is the slow control system c-code main directory.  The various sub directories
contain software for reading and writing slow control data to and from
a MySQL data base.  This file describes the back-end code.   For a more
general overview, go up one level from here and look in the Docs directory.
To learn about the database configuration, see Readme_DB.txt in the Docs
directory. 

The "lib" and "include" directores contains code that most of the instruments use such
as the db interface. 

The "alarm_trip_system" directory contains code for the alarm system that should run
on the same machine as the master databse.  All this does is monitors the
database and sets the 'alarm tripped' bit if the current value goes out of 
range, and writes an entry in the messages list.

The "alarm_alert_system" directory contains code for the alarm system that
should run on the same computer as the webserver.  It uses nail to send off
email alerts if any of the alarn bits get flipped to 1.  If /usr/bin/nail does
not exist, then the code has to be modified, or just install it.  It was far
easier to do this and rely on a properly configure local mail system than
implement a full smtp system.

Most of the other directories contain code to talk to a single instrument.
It it usually a bad idea to have multiple processes communicating with the 
same instrument even if it services multiple sensors (such as a temperature
monitor).  

The instrument programs can run on separate machines with
proper changes to the "/etc/slow_control_db_conf.ini" file.  

The best instrument example is probably the "CryoCon34" code as it has multiple
sensors as well as control capability.  In each instrument directory is a .c
file and a Makefile.  Running 'make' in the program directory will compile the
code for that instrument.  Running 'make' in the root directory (where this file
is) will make all libraries and instruments.

The only program that must be run manually is the Watchdog program.  
It must be run using its full path, not a relative path (ie must
start with '/')  When the watchdog is running, it monitors the database,
and starts programs that have their 'run' flag set to 1.  Make sure the
run flag for the Watchdog is set to 1 or is will immediately quit after 
starting.

Required external files:

1) "/etc/slow_control_db_conf.ini"
   (see Docs directory for an example)

2) GPIB instruments require a gpib kernel driver and library.  
   A current tar ball is in the extras directory

3) If you wish to use a general purpose IO card, one must first 
   globally install the comedi driver, along with comedilib.
   Obtain here: http://www.comedi.org/ 


 

