/* Program for controlling the TACO colimator slewing system */
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
#define INSTNAME "TACO_drive"

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
      sprintf(cmd_string, "R 0\n", s_s->num);

      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if(sscanf(ret_string, "%d", &return_int) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
	  return(1);
	}      
      *val_out = (double)return_int/100.0;   ///  micro-controller returns val in degrees*10
    }
  
  msleep(500);

  return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
 
   if (strncmp(s_s->subtype, "S", 1) == 0)  // Set angle
    {
      if ((s_s->new_set_val > -180)  && (s_s->new_set_val < 180))
	{     
	  sprintf(cmd_string, "S %d\n", (int)(100*(s_s->new_set_val)));     /// mult by 10 for comms
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	}
    }
 

  
  return(0);
}

#include "main.h"
