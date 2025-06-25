/* Program for reading a lakeshore 218 temperature monitor using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007 */
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

#include "rs232.h"

#define INSTNAME "LS218"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char               cmd_string[16];   
    ssize_t            wstatus = 0;
    ssize_t            rdstatus = 0;
    struct  termios    tbuf;  /* serial line settings */
   
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
        exit(1);
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
        exit(1);
    }

    sprintf(cmd_string, "*CLS");
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    wstatus = write(inst_dev, &CR, sizeof(char));
    wstatus = write(inst_dev, &LF, sizeof(char));    
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    close(inst_dev);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    double     val_out = 0;  
    int        int_out = 0;
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (strncmp(s_s->subtype, "DAC", 3) == 0)  // DACs
    {
	val_out = linear_interp(s_s->new_set_val, s_s->parm1, s_s->parm2);
	sprintf(cmd_string, "ANALOG %i,0,2,1,1,1,1,%f", s_s->num, val_out);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
    } 
    else if (strncmp(s_s->subtype, "Relay", 4) == 0)  // Relays
    {
	if (s_s->new_set_val > 0.5)
	    int_out = 1;
	
	sprintf(cmd_string, "RELAY %i,%i,1,0", s_s->num, int_out);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  // Reads out the current value of the Lakeshore temp monitor
    
    char       cmd_string[16];
    char       val[33];                      
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (strncmp(s_s->type, "Temp", 4) == 0)  // Temperatures
    {	
	sprintf(cmd_string, "KRDG?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(300);  
	rdstatus = read(inst_dev, &val, 32); 
	//fprintf(stdout, "Return from dev: %s \n", val);
	sscanf(val, "%lf", val_out);
	//fprintf(stdout, "Sensor: %d : %f \n", s_s->num+1, val_out);
    }
    else if (strncmp(s_s->subtype, "Relay", 4) == 0)  // Relays
    {
	sprintf(cmd_string, "RELAY?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
	rdstatus = read(inst_dev, &val, 32);
	//fprintf(stdout, "Return from dev: %s \n", val);
	sscanf(val, "%lf", val_out);
	//fprintf(stdout, "Sensor: %d : %f \n", sensor_num, val_out);
    }
    else if (strncmp(s_s->subtype, "DAC", 3) == 0)  // DACs
    {
	sprintf(cmd_string, "AOUT?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
	rdstatus = read(inst_dev, &val, 32);
	//fprintf(stdout, "Return from dev: %s \n", val);
	sscanf(val, "%lf", val_out);
	//fprintf(stdout, "Sensor: %d : %f \n", sensor_num, val_out);
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);    
}


#include "main.h"

