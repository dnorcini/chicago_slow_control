# Raritan PDU setup note
Cinyu Zhu, Hopkins, 6/30/2025

Legrand (btw, it is the mother company of Raritan and ServerTech)
BP 30076
Model 6 460 20

setup manual:
https://assets.legrand.com/pim/NP-FT-GT/LE11325AB.pdf

# wait, anyone knows the password?
- user: admin
- password: legrand

But on the day when we digged it out from a pile of ESD safe bags in the carbinet (06/25/2025), nobody knows.
After multiple attempts and messages, no, still nobody knows.

The only way to reset the machine to the factory defaults is via Serial Connection. The ethernet connection itself is not sufficient.

There are two ways establishing serial connection:

## via RS-232(RJ-45) port : 
need a "RJ-45" to "DB9 female" adapter/cable (e.g. blue Cisco adapter cable) (+ DB9 to usb cable)

RJ-45 ('CONSOLE port'): looks like an ethernet port, can take in a ethernet cable, but is NOT an ethernet port. Used to be a DB9 port in some older models (the commoner serial connection port, with needles or holes and two screws on both sides)

I find one cable that looks like RJ-45 to DB9 female; and plug that into the COM port (DB9 male) of SC2 computer, but no serial connection establed (see section below on how to establish the connection). Guess it's cable's fault.

## via usb-B (male) port: 
require a usb-B female to usb-A cable (which we do have!)

Note that there are many kinds of usb-B ports, (usb-B mini - old mp3 charger; usb-b standard - this machine or printer; usb-b 3.0, etc.), and of course they are not compatible to each other

plug the other side into the common usb-A female port of the computer

# Serial Connection and CLI interface
- install `screen` if the machine doesn't have it yet
- list all the serial connections: `ls -lt /dev/tty*` usually at `ttyS*` or `ttyUSB*`. Or just look at what appeared after the you plug it in.
- open the CLI interface: `sudo screen /dev/ttyACM0 115200`
- you should see prompts come up (or come up after pressing return key). If the screen is always blank -> there isn't a correct connection. If unable to open the screen -> no permission or the connection doesn't exit
- in username, type `factorydefaults`; then confirm eith `y` on the next prompt
- the machine should be reset!!!!!! 
- you can now login with the default credentials (admin/legrand) and continue the setup

# notes for debugging the .c scripts
- keep an eye on the sql entries and frontend along with the backend file
(e.g. activate run checkbox in instrument config)
- everything after daemonize won't be printed on the terminal (therefore comment out that line)
- for a process w/o daemonize; you can check the pid in another terminal, and SC web can also read it; and of course ctrl+C will kill the pid.

