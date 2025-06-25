/* Program for reading the UTI level meter with an Arduino board */
/* using ethernet */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2013 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "UTILM_RH"

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

   global_tcp_timeout = 20;
   
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

  char       cmd_string[32];
  char       ret_string[32];                      
  long int   int_val;

  sleep(5);

  sprintf(cmd_string, "GetCap\n"); // Read out value for Capacitance (microFarads).
  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "capout : %s \n", ret_string);

  if(sscanf(ret_string, "%d", &int_val) !=1) 
    return(1);	
    
  *val_out = (double)int_val/1000000.0;   // Convert from uF to pF
  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
 
  if (strncmp(s_s->subtype, "Mode", 1) == 0)  // Set the Mode
    {
      if (closest_int(s_s->new_set_val)  == 1 ) //num 1 = HI mode
	sprintf(cmd_string, "SetMode High\n");
      else if (closest_int(s_s->new_set_val) == 2 ) //num 2 = LOW mode
	sprintf(cmd_string, "SetMode Low\n");

      else //print error for incorrect input.
	{
	  fprintf(stderr, "%f is an incorrect value for new_set_val. Must be 1 for HI, 2 for LOW\n", s_s->new_set_val);
	  return(1);
	}
      //fprintf(stdout, "modein: %s \n", cmd_string);
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(3);
        
      sprintf(cmd_string, "GetCap\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      //fprintf(stdout, "modeout:%s \n", ret_string);
    }

  return(0);
}

#include "main.h"
