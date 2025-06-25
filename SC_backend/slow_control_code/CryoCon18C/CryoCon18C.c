/* Program for reading a CryoCon18C temp. monitor */
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
#define INSTNAME "CryoCon18C"

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
 
  if ((s_s->num < 1) || (s_s->num > 8))
    {
      fprintf(stderr, "Channel number must be 1 through 8, not %d.\n", s_s->num);
      return(1);
    }	

  sprintf(cmd_string, "INPUT? %c\n", int_to_Letter(s_s->num));
  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(200);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
  if(sscanf(ret_string, "%lf", val_out) != 1)
    {
      fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
      return(1);
    }	
   
  msleep(600);

  return(0);
}


#include "main.h"
