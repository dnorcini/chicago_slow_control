/* Program for reading the an ADAM6024 multi DAC/ADC over TCP/modbus */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009, 2010 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"
#include "ADAMMain.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "ADAM6052"

const uint16_t DI_start_address = 0;
const uint16_t num_DI = 2;

const uint16_t DO_start_address = 16;
const uint16_t num_DO = 2;


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{  
  if (strncmp(s_s->subtype, "DI", 2) == 0)       // Digital In
    return(read_DI(s_s, DI_start_address,  num_DI, val_out));

  else if (strncmp(s_s->subtype, "DO", 2) == 0)       // Digital Out
    return(read_DO(s_s, DO_start_address,  num_DO, val_out));

  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      return(1);
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{   	
  if (strncmp(s_s->subtype, "DO", 2) == 0)       // Digital out 
    return(write_DO(s_s, DO_start_address,  num_DO));

  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      return(1);
    }
}


#include "main.h"

