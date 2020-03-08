/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2007,2008, 2010 */
/* James public licence. */


This file decribes the organization of the database that the slow control
system uses to operate. 

Edit your MySQL configuration file (often /etc/my.cnf)
Set the port if the default of 3306 is problematic.

Set your operating system to start MySQL automatically on boot.  

Start MySQL and:
1) Set a password for the root user

2) Add user `control_user' and set a password

3) If you are going to do replication, set a replication user and password

4) The dabase proper:  control_DB_base.sql contains a DB dump of all the required tables.
   Import this into MySQL to make a DB to start with.   


