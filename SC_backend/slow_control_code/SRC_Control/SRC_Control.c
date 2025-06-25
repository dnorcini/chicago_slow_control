/* Program for controlling the PROSPECT belt driven source deployment system*/
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
#define INSTNAME "SRC_Control"

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
  int        return_int_1;
  int        return_int_2;
  
  if (strncmp(s_s->subtype, "R", 1) == 0)  // Read out current source position
    {
      s_s->data_type = DONT_AVERAGE_DATA_OR_INSERT;

      sprintf(cmd_string, "%d R 0\n", s_s->num);

      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if(sscanf(ret_string, "%d", &return_int_1) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
	  return(1);
	}
      msleep(500);
	    
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if(sscanf(ret_string, "%d", &return_int_2) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
	  return(1);
	}

      if (return_int_1 !=return_int_2)
	return(1);

      if (return_int_1 < 0)
	return(0);
      
      *val_out = (double)return_int_1/10.0 - s_s->parm1;   ///  micro-controller returns val in mm.  Convert to cm, and subtract zero.

      add_val_sensor_struct(s_s, time(NULL), *val_out);
      s_s->rate = 2.3;
      write_temporary_sensor_data(s_s);
      return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
    }
  return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
 
  if (strncmp(s_s->subtype, "H", 1) == 0)  // Go home
    {  
      if (s_s->new_set_val > 0.5) 
	{
          sprintf(cmd_string, "%d S %d\n", s_s->num, 1200);
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	  msleep(100);
  
	  sprintf(cmd_string, "%d H 0\n", s_s->num); 	   
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	  insert_mysql_sensor_data(s_s->name, time(NULL), 0.0, 0.0);
	}
    }
  else if (strncmp(s_s->subtype, "X", 1) == 0)  // Stop!
    {  
      if (s_s->new_set_val > 0.5) 
	{  
	  sprintf(cmd_string, "%d X 0\n", s_s->num); 	   
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	  insert_mysql_sensor_data(s_s->name, time(NULL), 0.0, 0.0);
	}
    }
  
  else if (strncmp(s_s->subtype, "G", 1) == 0)  // Goto specified position
    {
      if (s_s->new_set_val + s_s->parm1 > 0) 
	{     
	  sprintf(cmd_string, "%d G %d\n", s_s->num, (int)(10*(s_s->new_set_val + s_s->parm1)));     ///  parm1 sets zero to that value
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	}
    }
  else if (strncmp(s_s->subtype, "E", 1) == 0)  // Extent specified amount
    {
      sprintf(cmd_string, "%d E %d\n", s_s->num, (int)(10*s_s->new_set_val));
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	
    }
  else if (strncmp(s_s->subtype, "S", 1) == 0)  // Set speed
    {
      sprintf(cmd_string, "%d S %d\n", s_s->num, (int)s_s->new_set_val);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
    }

  
  return(0);
}

#include "main.h"
