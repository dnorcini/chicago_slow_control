/* Program for reading a CryoCon44 temp. controller */
/* using ethernet */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
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

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "CryoCon44"

int inst_dev;

int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
    char  cmd_string[16];   

    inst_dev = ibfind(device_name); 
    ibclr(inst_dev);

    sprintf(cmd_string, "*CLS");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    
    return(inst_dev);
}

void clean_up_inst(int inst_dev, struct sensor_struct *s_s_a)
{
    ibclr(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // returns the temp (which_sens_type == 1)
    // or the heater power (which_sens_type == 2)

    char       cmd_string[32];
    char       val[32];                      
    double     P_range;
    double     P_percent;

    if (s_s->type == 1)       // temps are type 1
    {
	sprintf(cmd_string, "INPUT? %c\n", int_to_char(s_s->num));
	//fprintf(stdout, "in: %s \n", cmd_string);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 10);
	//fprintf(stdout, "out: %s \n", val);
	sscanf(val, "%lf", val_out);	
    }
    else if (s_s->type == 2)    // heaters are sensor type 2
    {
	sprintf(cmd_string, "LOOP %d:RANGE?\n", s_s->num+1);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 10);
	sscanf(val, "%lf%*s", &P_range);
	msleep(400);
	sprintf(cmd_string, "LOOP %d:HTRREAD?\n", s_s->num+1);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &val, 10);
	sscanf(val, "%lf\%%*s", &P_percent);
	
	*val_out = P_range*P_percent/100.0;	
    }
    else
    {
	fprintf(stderr, "Wrong type for %s \n", s_s->name);
	return(1);
    }
    
    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    
    if (s_s->new_set_val < 0 )
    {
	return(1);
    }
    
    sprintf(cmd_string, "LOOP 1:SETPT %f\n", s_s->new_set_val);
    
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    sleep(1);
    
    return(0);
}


#include "main.h"
