/* Program for reading two Lakeshore temp. controllers */
/* switching between various sensors using a Keithley switch */
/* and putting said readings in to a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
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

#define INSTNAME "LS330Keithley"

int   inst_dev_s;                // the switch
int   inst_dev_TC_t;             // the top temperature controller
int   inst_dev_TC_b;             // the bottom temperature controller


//////////////////////////////////////////////////////////////
double read_therm(int inst_dev, char chan)
{ 
    char       cmd_string[32];
    char       val[32];                      
    double     val_out = 0;   // the temperature read from the sensor
     
    sprintf(cmd_string, "SCHN %c\r\n", chan);
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(600);
    sprintf(cmd_string, "SDAT?\r\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  
    ibrd(inst_dev, &val, 30);
    sscanf(val, "%lf", &val_out);	
    //val_out += (double)rand()/(double)RAND_MAX*1e-5;
    return(val_out);
}

double read_heater(int inst_dev)
{ 
    char       cmd_string[32];
    char       val[32];                      
    double     val_out = 0;   // the temperature read from the sensor
    int        P_percent =0;

    sprintf(cmd_string, "HEAT?\r\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  
    ibrd(inst_dev, &val, 30);
    sscanf(val, "%d", &P_percent);
    val_out=P_percent/1.0;
    //val_out += (double)rand()/(double)RAND_MAX*1e-5;
    return(val_out);
}
///////////////////////////////////////////////////////////////////////



#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
    char  cmd_string[16];   
    
    inst_dev_s = ibfind(i_s->dev_address); 
    ibclr(inst_dev_s);
    sprintf(cmd_string, "*CLS\r\n");
    ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));	
    msleep(100);  
    sprintf(cmd_string, ":open all");
    ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));

    inst_dev_TC_t = ibfind(i_s->user1); 
    inst_dev_TC_b = ibfind(i_s->user2); 
    ibclr(inst_dev_TC_t);
    ibclr(inst_dev_TC_b);

    sprintf(cmd_string, "*CLS\r\n");
    ibwrt(inst_dev_TC_t, cmd_string, strlen(cmd_string));
    ibwrt(inst_dev_TC_b, cmd_string, strlen(cmd_string));
    msleep(100);  

    sprintf(cmd_string, "CUNI K\r\n");
    ibwrt(inst_dev_TC_t, cmd_string, strlen(cmd_string));
    ibwrt(inst_dev_TC_b, cmd_string, strlen(cmd_string));
    msleep(100);  

    sprintf(cmd_string, "SUNI K\r\n");
    ibwrt(inst_dev_TC_t, cmd_string, strlen(cmd_string));
    ibwrt(inst_dev_TC_b, cmd_string, strlen(cmd_string));
    msleep(100);  

    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    ibclr(inst_dev_s);
    ibclr(inst_dev_TC_t);
    ibclr(inst_dev_TC_b);
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

    if (strncmp(s_s->type, "Temp", 4) == 0)       // temperatures
    {
	if (s_s->num == 0)
	{
	    sprintf(cmd_string, ":open all");
	    ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));
	    msleep(4000);
	    if (strncmp(s_s->user1, "t", 1) == 0)
            {	
		read_therm(inst_dev_TC_t, 'A');
		msleep(600);
		*val_out = read_therm(inst_dev_TC_t, 'A');
	    }
	    else if (strncmp(s_s->user1, "b", 1) == 0)
	    {
		read_therm(inst_dev_TC_b, 'A');
		msleep(600);
		*val_out = read_therm(inst_dev_TC_b, 'A');
  	    }			
	    else
	    {
		fprintf(stderr, "user field not set correctly for sensor: %s \n", s_s->name);
		return(1);
	    }
	}
	else
	{
	    if (strncmp(s_s->user1, "t", 1) == 0)
	    {
		sprintf(cmd_string, ":close (@1!%i)", (int)(s_s->num + 10));
		ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));
		msleep(4000);	    
		read_therm(inst_dev_TC_t, 'B'); 
		msleep(600);
		*val_out = read_therm(inst_dev_TC_t, 'B'); 
	    }
	    else if (strncmp(s_s->user1, "b", 1) == 0)
	    {
		sprintf(cmd_string, ":close (@1!%i)", (int)(s_s->num));
		ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));
		msleep(4000);	    
		read_therm(inst_dev_TC_b, 'B'); 
		msleep(600);
		*val_out = read_therm(inst_dev_TC_b, 'B'); 
	    }
	    else
	    {
		fprintf(stderr, "user field not set correctly for sensor: %s \n", s_s->name);
		return(1);
	    }
	    sprintf(cmd_string, ":open all");
	    ibwrt(inst_dev_s, cmd_string, strlen(cmd_string));
	}	
    }
    else if (strncmp(s_s->type, "Power", 3) == 0)    // heaters 
    {
	if (strncmp(s_s->user1, "t", 1) == 0)
	{
	    read_heater(inst_dev_TC_t); 
	    msleep(600);
	    *val_out = read_heater(inst_dev_TC_t);     
	}
	else if (strncmp(s_s->user1, "b", 1) == 0)
	{
	    read_heater(inst_dev_TC_b); 
	    msleep(600);
	    *val_out = read_heater(inst_dev_TC_b);   
	}
	else
	{
	    fprintf(stderr, "user field not set correctly for sensor: %s \n", s_s->name);
	    return(1);
	}
    }
    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    int        this_dev;

    if (s_s->new_set_val < 0 )
    {
	return(1);
    }
    
    if (strncmp(s_s->user1, "t", 1) == 0)
	this_dev = inst_dev_TC_t;
    else if (strncmp(s_s->user1, "b", 1) == 0)
	this_dev = inst_dev_TC_b;
    else
    {
	fprintf(stderr, "user field not set correctly for sensor: %s \n", s_s->name);
	return(1);
    }
    sprintf(cmd_string, "CCHN %c\r\n", int_to_Letter(s_s->num));
    ibwrt(this_dev, cmd_string, strlen(cmd_string));
    msleep(600);    
    sprintf(cmd_string, "SETP %f\n", s_s->new_set_val);
    ibwrt(this_dev, cmd_string, strlen(cmd_string));
    
    return(0);
}

#include "main.h"
