//Turns on and off the SPS125 Power Supply output

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>


#include "gpib.h"

int set_up_inst(char *device_name)    
{
    char  cmd_string[16];   
    int   inst_dev;

    inst_dev = ibfind(device_name); 
    ibclr(inst_dev);

    sprintf(cmd_string, "*CLS");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    
    return(inst_dev);
}

int main (int argc, char *argv[])
{
    char       cmd_string[64];
    char       device_name[64];
    int        inst_dev;
    char       config_val[16];
    int        i;

    sprintf(device_name, "SPS125");

    inst_dev = set_up_inst(device_name);

    if (argc == 2)
    {
	sprintf(cmd_string, "OUTP 1 ON");
    }
    else
    {
	sprintf(cmd_string, "OUTP 1 OFF");
    }
    
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    sleep(1);

    exit(0);
}
