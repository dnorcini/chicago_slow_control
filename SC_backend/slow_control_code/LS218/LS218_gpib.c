/* Program for reading a lakeshore 218 temperature monitor using the gpib interface */
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

#include "gpib.h"

#define INSTNAME "LS218"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char  cmd_string[16];   
    int ib_board_index = 0;
    int ib_pad = 0;
    int ib_sad = 0;
    int ib_timeout= T3s;
    int ib_send_eoi = 1;
    int ib_eos = 0xa;

    sscanf(i_s->dev_address, "%d", &ib_pad);
    //inst_dev = ibfind(i_s->dev_address); 
    inst_dev = ibdev(ib_board_index, ib_pad, ib_sad, ib_timeout, ib_send_eoi, ib_eos);
    ibclr(inst_dev);

    sprintf(cmd_string, "*CLS");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    
    return(inst_dev);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    ibclr(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
 // Reads out the current value of the Lakeshore temp monitor
    // at given sensor.  0 is the first, 1, second, ...
    
    char       cmd_string[16];
    char       val[33];                      
    
    if (strncmp(s_s->type, "Temp", 2) == 0)  // read temperature
    { 
	sprintf(cmd_string, "KRDG?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 32);
	//fprintf(stdout, "Return from dev: %s \n", val);
	sscanf(val, "%lf", val_out);
	//fprintf(stdout, "Sensor: %d : %f \n", sensor_num, val_out);
    }
    else if (strncmp(s_s->subtype, "Relay", 2) == 0)  // read relay status
    {
	sprintf(cmd_string, "RELAY?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 32);
	//fprintf(stdout, "Return from dev: %s \n", val);
	sscanf(val, "%lf", val_out);
	//fprintf(stdout, "Sensor: %d : %f \n", sensor_num, val_out);
    }
    else if (strncmp(s_s->subtype, "DAC", 2) == 0)  // read ADC out
    {
	sprintf(cmd_string, "AOUT?%i", s_s->num);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 32);
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

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    double         val_out = 0;  
    if (strncmp(s_s->subtype, "DAC", 2) == 0)  // read ADC out
    {
	val_out = linear_interp(s_s->new_set_val, s_s->parm1, s_s->parm2);
	sprintf(cmd_string, "ANALOG %i,0,2,1,1,1,1,%lf", s_s->num+1, val_out);
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
    } 
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);
}

#include "main.h"
