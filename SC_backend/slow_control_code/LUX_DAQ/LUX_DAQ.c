/* This is the alarm system for the slow control. */
/* that checks for free disk space.  It will insert an error */
/* if the free space % goes below a set value. */
/* This should be run on the master db computer. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#define INSTNAME "LUX_DAQ"

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // Set sensor to 1 if DAQ is aquiring, and 0 otherwise
    int is_running = 0;

    if (strncmp(s_s->subtype, "monitor", 3))
    {
	// check if running
	if (is_running)
	    *val_out = 1;
	else
	    *val_out = 0;
    }
    else
    {
	fprintf(stderr, "Wrong subtype %s in read_sensor for instrument %s.\n", s_s->subtype, i_s->name);
	return(1);
    }
    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    
}

int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{;}

void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{;}


#include "main.h"
