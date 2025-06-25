/* Program for reading a CryoMagnetics LM-500 level meter */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2010 */
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

#define INSTNAME "CryoMag_LM500"

int   inst_dev;

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
    
    sprintf(cmd_string, "INPUT? %c\n", int_to_letter(s_s->num));
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(400);  
    ibrd(inst_dev, &val, 10);
    sscanf(val, "%lf", val_out);	

}

#include "main.h"
