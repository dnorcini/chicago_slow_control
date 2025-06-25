/* Program for reading the MKS925C vacuum gauge over rs232 interface */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2008, 2009 */
/* James public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"

#define INSTNAME "MKS925C"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char               cmd_string[16];   
    struct  termios    tbuf;  /* serial line settings */
    char               val[64];
 
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified: %s \n", i_s->dev_address);
	my_signal = SIGTERM;
	return(1);
    }
    
    /* set up the serial line parameters :  */
    tbuf.c_cflag = CS8|B9600|CREAD|PARODD;
    tbuf.c_iflag = INPCK|ISTRIP;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0; 
    tbuf.c_cc[VTIME]= 0;

    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
	my_signal = SIGTERM;
	return(1);
    }

    write(inst_dev, &CR, sizeof(char));
    msleep(100);
    write(inst_dev, &LF, sizeof(char));    
    msleep(100);

    sprintf(cmd_string, "@255BR!9600;FF");
    write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);
    sprintf(cmd_string, "@254AD!001;FF");
    write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);
    read(inst_dev, &val, 32);
    fprintf(stdout,"inst str ret: %s\n",val);
    msleep(100);
    sprintf(cmd_string, "@001U!TORR;FF");
    write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);
    read(inst_dev, &val, 32);
    fprintf(stdout,"inst str ret: %s\n",val);
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // Reads out the pressure.  Duh.
 
    char   cmd_string[64];
    char   val[64];
    char   val2[16];

    sprintf(cmd_string, "@001PR1?;FF");
    write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(200);
    read(inst_dev, &val, 32);
    sscanf(val, "@001ACK%7s;%*s", val2); 
    *val_out = strtod(val2, NULL);

    return(0);    
}


#include "main.h"
