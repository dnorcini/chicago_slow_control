/* Program for reading a custom made Arduino POE counter */
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
#define INSTNAME "Arduino_Counter"

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
  // returns the temp (which_sens_type == 1)
  // or the heater power (which_sens_type == 2)

  char       cmd_string[64];
  char       ret_string[64];             
  unsigned long  counts;

  s_s->data_type = DONT_AVERAGE_DATA_OR_INSERT;

  if (s_s->num == 0)             // For Channel A
    sprintf(cmd_string, "0\n"); 
  else if (s_s->num == 1)        // For Channel B
    sprintf(cmd_string, "1\n"); 
  else
    {
      fprintf(stderr, "Channel must be 0 for A, or 1 for B, not %d.\n", s_s->num);
      return(1);
    }

  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(200);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

  if (sscanf(ret_string, "%lu", &counts) !=1) 
    {
      fprintf(stderr, "Bad return string: \"%s\" \n", ret_string);
      return(1);
    }
    
  *val_out = (double)counts;
 
  if (counts >= 0)
    {
      add_val_sensor_struct(s_s, time(NULL), (double)counts);
      
      if (s_s->times[s_s->index] - s_s->times[dec_index(s_s->index)] > 0 )
	s_s->rate = (s_s->vals[s_s->index] - s_s->vals[dec_index(s_s->index)]) /
	  ((double)(s_s->times[s_s->index] - s_s->times[dec_index(s_s->index)]));
      else
	s_s->rate = 0;
      
      write_temporary_sensor_data(s_s);
      
      return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
    }
  else 
    return(1);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
 
  if (s_s->new_set_val > 0.5) 
    {
      sprintf(cmd_string, "r\n"); 
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      insert_mysql_sensor_data(s_s->name, time(NULL), 0.0, 0.0);
    }

  return(0);
}

#include "main.h"
