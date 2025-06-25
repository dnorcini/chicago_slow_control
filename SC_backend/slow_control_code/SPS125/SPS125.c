/* Program for reading a Amrel SPS 125 power supply */
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

#include "gpib.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "SPS125"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char  cmd_string[16];   

    inst_dev = ibfind(i_s->dev_address); 
    ibclr(inst_dev);

    sprintf(cmd_string, "*CLS");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    ibclr(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{

    char       cmd_string[32];
    char       val[32];                      
    double     P_range;
    double     P_percent;
    
    
    if (strncmp(s_s->subtype, "voltage", 1) == 0)  
    {
	//fprintf(stdout, "MEAS:VOLT? 1");
	sprintf(cmd_string, "MEAS:VOLT? 1");
    }
    else if (strncmp(s_s->subtype, "current", 1) == 0)  
    {	
	sprintf(cmd_string, "MEAS:CURR? 1");
	//fprintf(stdout, "MEAS:CURR? 1");
    }
    else
    {
	fprintf(stderr, "Wrong type for %s \n", s_s->name);
	return(1);
    }
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(400); 
    ibrd(inst_dev, &val, 25);
    char * * p= NULL;
    *val_out = strtod(val,p);
    //fprintf(stdout, "out: %f \n", val_out);   

    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    //fprintf(stdout, "setting : %i  \n", s_s->type);
    char       cmd_string[64];
    if (s_s->new_set_val < 0 )
    {
	return(1);
    }
   
    if (strncmp(s_s->subtype, "voltage", 1) == 0)  
    {
	sprintf(cmd_string, "VOLT 1 %f\n", s_s->new_set_val);
    }
    else if (strncmp(s_s->subtype, "current", 1) == 0)
    {
	sprintf(cmd_string, "CURR 1 %f\n", s_s->new_set_val);
    }
    else if (strncmp(s_s->subtype, "main", 1) == 0)
    {
	if  (s_s->new_set_val == 1)
	    sprintf(cmd_string, "OUTP 1 ON");
	else
	    sprintf(cmd_string, "OUTP 1 OFF");
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    sleep(1);
    
    return(0);
}

#include "main.h"
