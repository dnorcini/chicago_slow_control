/* Program for reading a Keithley 485 picoameter */
/* and putting said readings in to a mysql database. */
/* Code by James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2011 */
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

#define INSTNAME "Keithley485"

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
    msleep(1000);
    
    sprintf(cmd_string, "REN\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(1000);  
    
    sprintf(cmd_string, "DCL\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(1000);  
 
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
    char   cmd_string[128];
    char   ret_string[128];                      
    double out;
    
    sprintf(cmd_string, "G0X\n");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  
    ibrd(inst_dev, &ret_string, 64);
    //fprintf(stdout, "%s\n", ret_string);
    sscanf(ret_string, "NDCA+%lf", &out);
    *val_out = out*1e9;
    
    return(0);
}


#include "main.h"
