/* Program for Reading out a Keyence IL 1000 laser rangefinder using the DL-EN1 interface*/
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2017 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "Keyence_IL1000"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  
  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  char       cmd_string[64];
  char       ret_string[64];             
  int        return_int;

  
  if (strncmp(s_s->subtype, "R", 1) == 0)  // Read out current source position
    {
      sprintf(cmd_string, "M0%c%c", CR, LF);
	    
      //s_s->data_type = DONT_AVERAGE_DATA_OR_INSERT;
      
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if(sscanf(ret_string, "M0,%d", &return_int) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
	  return(1);
	}
      
      *val_out = (double)return_int/1000.0;
      msleep(50);
    }
  return(0);
}

#include "main.h"
