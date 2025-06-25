#Adapted from SNOLAB radon monitoring code for DAMIC-M slow control (chicago_slow_control)
#Michelangelo Traina, CENPA - University of Washington, 2023

import serial
import time
import datetime
from time import gmtime, strftime

#set serial paramentes
ser = serial.Serial(
port='/dev/ttyS0',
baudrate=19200,
parity=serial.PARITY_NONE,
stopbits=serial.STOPBITS_ONE,
bytesize=serial.EIGHTBITS,
xonxoff=0,
rtscts=0
)

if not ser.isOpen():
    ser.open()

first = '\x03\r\n'
second = 'test status'+'\r\n '
#third = 'Yes'+'\r\n'
ser.write(first.encode())
time.sleep(2)
out=''
while ser.inWaiting() > 0:
    out += ser.read(1).decode("utf-8")


ser.write(second.encode())
time.sleep(2)
out=''
while ser.inWaiting() > 0:
    out += ser.read(1).decode("utf-8")
'''
ser.write(third.encode())
time.sleep(2)
out=''
while ser.inWaiting() > 0:
    out += ser.read(1).decode("utf-8")
'''   
 
print(out)
    
ser.close()


exit()
