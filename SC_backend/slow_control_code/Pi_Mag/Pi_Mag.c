/* Program for reading a custom made Pi driven magnetometer */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2018 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "Pi_Mag"

int inst_dev;

int num_tries;


#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }

  num_tries = 0;
 
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
  float      field_val;
  
  if ( num_tries > 20)
    {
      close(inst_dev);
      msleep(2000);
       if ((inst_dev = connect_tcp(i_s)) < 0)
	 {
	   fprintf(stderr, "Connect failed. \n");
	   my_signal = SIGTERM;
	   return(1);
	 }
       
       num_tries = 0;
       msleep(2000);
    }

  if (s_s->num < 0 || s_s->num > 7)
    {
      fprintf(stderr, "Channel must be between 0 and 7 (inclusive), not %d.\n", s_s->num);
      return(1);
    }

  sprintf(cmd_string, "field%d", s_s->num);
  query_tcp(inst_dev, cmd_string, 6, ret_string, sizeof(ret_string)/sizeof(char));
  msleep(2000);
  query_tcp(inst_dev, cmd_string, 6, ret_string, sizeof(ret_string)/sizeof(char));
   
  if (sscanf(ret_string, "%f", &field_val) !=1)
    {
      fprintf(stderr, "Bad return string: \"%s\" \n", ret_string);
      num_tries++;
      return(1);
    }
  *val_out = field_val;
  msleep(2000);
  return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
 
  if (s_s->new_set_val > 0.5) 
    {
      sprintf(cmd_string, "1");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      insert_mysql_sensor_data(s_s->name, time(NULL), 0.0, 0.0);
    }

  return(0);
}

#include "main.h"
