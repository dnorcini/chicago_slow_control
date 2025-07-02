# Add a New Instrument to the Slow Control System
Cinyu Zhu, Hopkins, June 2025

I made this note to record everything I have done to add **ServerTech Sentry PDU** to the system using SNMP communication. 

**Never forget reset instrument after each change of .c file!!!!**
**Everytime change the Sensor Config, you need to reset the instrument as well**


## 1. Setup and prepare the instrument
Switched POPS PDU - 3.3kW, C2WG08HC-0ABA2DAC
- manual: https://cdn10.servertech.com/assets/documents/documents/1076/original/301-9999-30_Switched_PRO2_Rev_F.pdf?1725909>
- MIB: https://cdn10.servertech.com/assets/documents/documents/815/original/Sentry4.mib
- OID tree: https://cdn10.servertech.com/assets/documents/documents/1041/original/Sentry4OIDTree.txt?1689970897

The default ip address of pdu: 192.168.1.254

but we can't access it directly from local machine

in cmd: `sudo ip addr add 192.168.1.10/24 dev enp3s0` to allocate this ip to your computer,
then you will be able to access PDU:

`ping 192.168.1.254` success

open the (https://192.168.1.254) at the browser

(you can control the outlets remotely from this web interface)

for JH2 chamber, we need to change the IP address

Login: default username: admn; password: admn

reset it to: username: admn; password: MyL...4..ur (JH2)

after login, change the IP address at: Configuration - Network - DCHP/IP
- change the IPv4 Address to 192.168.2.254
- change the IPv4 Gateway to 192.168.2.1
- reboost the PDU
- `sudo ip addr add 192.168.2.10/24 dev enp3s0` (again allocate this ip to your computer)

## 2. communication protocal and setup

Look into the instrument's manual, find the communication type they supported and set them up in Configuration on the website as well.

Sentry_PDU supports snmp

Working commands: 
- `snmpget -v2c -c public 192.168.2.254 .1.3.6.1.4.1.1718.4.1.8.5.1.1.1.1.#`
- `snmpset -v2c -c private 192.168.2.254 .1.3.6.1.4.1.1718.4.1.8.5.1.2.1.1.# i 1`(on) or 2(off)
- `snmpget -v2c -c public 192.168.2.254 .1.3.6.1.4.1.1718.4.1.8.3.1.3.1.1.1`
return: `iso.3.6.1.4.1.1718.4.1.8.3.1.3.1.1.1 = INTEGER: 15` (read outlet current, unit 0.01A, current = 0.15A)


(see more on OID tree)


## 3. Implement setting and reading functions
- in SC_backend/slow_control_code, mkdir Sentry_PDU
- create Sentry_PDU.c in the folder
- modify the content of .c file and MakeFile, take reference from another instrument using the same protocal
- make, a executable should be created
- (I don't know how to debug efficiently in this step) 
- **Most Important!! Never forget reset instrument after each change of .c file!!!!**

here the writing/reading community names are hardcoded as public/private:
todo: get it from `String field 1`?

## 4. add instrument and sensor on the SC_webpage

Config -> Edit Instrument Config -> New Instrument
- Runpath: Sentry_PDU/Sentry_PDU
- Devide type: ethernet
- User field & Parameter: doesn't really matter?
- Subsystem: choose to your liking
- Device address: the IP address you setup above

Config -> Edit Sensor Config -> New Sensor name:
- Type: select one (see below on how to add a new type)
- Subtype: has to match what you've written in .c file
- Number: which outlet
- Units: discrete
- Discrete Units: 1:2;ON:OFF (automatically replace the digital reading to text)
- control privileges: full
- string fields and parameter types: doesn't really matter

Checkboxes:
- Hide: to hide the instrument
- show rate: ?
- settable: if checked, sensor appears in 'contro;' tab, call function `set_sensor()`; if unchecked, sensor appears in 'text' tab, call function `read_sensor()`
- for PDU, one sensor to set and another to read

## 5. Run and Monitor
Wise way to restart the slowcontrol:
run $ sudo ~/SC_backend/Docs/slow_start.sh

everything is setup! You can use SC website to control the PDU (or other instrument)


## A. make a new category for the sensor

this is modified in frontend. 

(N.B. the php file running in realtime for the website is at `var/www/http/`)

add a new entry **PDU** in SQL database control, table sc_sensor_types

(easiest way: http://lsm-hopkins-ccd-sc2.in2p3.fr/phpmyadmin login username: root)

then **PDU** will appear in all the drop down menus

## B. feature: control multiple outlets all together
see example at:

func set_sensot (): Subtype == outletgroup

For some unknown reason, it will trigger the alarm `New setpoint: Sentry_Gr_OnOff = 1.000000e+00 could not be set`. When outlet status actually are set.....

(It seems like it was fixed by restart the watchdog and restart the instrument.)

It might be easier if we just click on each outlet quick enough.