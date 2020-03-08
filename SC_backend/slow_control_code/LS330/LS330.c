/* Program for reading a LakeShore331 temp. controller */
/* and putting said readings in to a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* Code by James Nikkel */
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

#define INSTNAME "LS330"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
    char cmd_string[16];   
    int ib_board_index = 0;
    int ib_pad = 0;
    int ib_sad = 0;
    int ib_timeout = T3s;
    int ib_send_eoi = 1;
    int ib_eos = 0xa;

    sscanf(i_s->dev_address, "%d", &ib_pad);
    inst_dev = ibdev(ib_board_index, ib_pad, ib_sad, ib_timeout, ib_send_eoi, ib_eos);

    ibclr(inst_dev);
    msleep(100);
    sprintf(cmd_string, "*CLS\r\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  
    sprintf(cmd_string, "SUNI K\r\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);
    sprintf(cmd_string, "CUNI K\r\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  
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
    char   cmd_string[32];
    char   val[32];                      
    int    P_percent;
    
    if ((s_s->num < 1) || (s_s->num > 2))
    {
	fprintf(stderr, "Channel number for %s must be 1 or 2 \n", s_s->name);
	return(1);
    }
    
    if (strncmp(s_s->subtype, "temp", 4) == 0)       // temperatures
    {
	sprintf(cmd_string, "SCHN %c\r\n", int_to_Letter(s_s->num));
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(600);
	sprintf(cmd_string, "SDAT?\r\n");
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(100);  
	ibrd(inst_dev, &val, 30);
	sscanf(val, "%lf", val_out);	
    }
    else if (strncmp(s_s->subtype, "power", 3) == 0)    // heaters 
    {
	sprintf(cmd_string, "HEAT?\r\n");
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(100);  
	ibrd(inst_dev, &val, 30);
	sscanf(val, "%d", &P_percent);
	*val_out = (double)P_percent;
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
    char cmd_string[64];
    
    if (s_s->new_set_val < 0 )
    {
	fprintf(stderr, "Setpoint for %s must be positive \n", s_s->name);
	return(1);
    }
    if (strncmp(s_s->subtype, "setpoint", 3) == 0)     // closed loop setpoint (C)
    {
	sprintf(cmd_string, "CCHN %c\r\n", int_to_Letter(s_s->num));
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(1200);
	sprintf(cmd_string, "SETP %f\n", s_s->new_set_val);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);  
}

#include "main.h"
