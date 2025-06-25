/* Program for reading a CryoCon24C temp. controller */
/* using ethernet */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2012 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "TTi_PowerSupply"

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
  double     val[2];
  int        i;

  if (s_s->num < 1 || s_s->num > 2) // Checks to see that the channel is 1 or 2 (for dual supplies)
    {
      fprintf(stderr, "%d is an incorrect value for num. Must be 1 or 2. \n", s_s->num);
      return(1);
    }
  
  if ((strncmp(s_s->subtype, "Power", 1) == 0) || (strncmp(s_s->subtype, "Resistance", 1) == 0))
    {
      sprintf(cmd_string, "V%dO?\n", s_s->num);
      
      for (i = 0; i < 2; i++)
	{
	  if (i == 1)
	    sprintf(cmd_string, "I%dO?\n", s_s->num);
	  
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	  msleep(200);
	  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
	  if(sscanf(ret_string, "%lf%*s", &val[i]) != 1)
	    {
	      fprintf(stderr, "Bad return string: \"%s\" in read_sensor!\n", ret_string);
	      return(1);
	    }
	}

      if (strncmp(s_s->subtype, "Power", 1) == 0)
	*val_out = val[0]*val[1];
      else  // For a resistance measurement
 	{
	  if (val[1] > 0)
	    *val_out = val[0]/val[1];
	  else
	    *val_out = 0;
	}
      
      msleep(1000);
      return(0);
    }
  
  if (strncmp(s_s->subtype, "Voltage", 1) == 0)  // Voltage output 
    {
      sprintf(cmd_string, "V%dO?\n", s_s->num);
    }
  else if (strncmp(s_s->subtype, "Current", 1) == 0) // Current output
    {
      sprintf(cmd_string, "I%dO?\n", s_s->num);
    }
  else if (strncmp(s_s->subtype, "Set_V", 5) == 0) // Queries the voltage setpoint 
    {
      sprintf(cmd_string, "V%d?\n", s_s->num);  
    }
  else if (strncmp(s_s->subtype, "Set_C", 5) == 0) // Queries the current setpoint 
    {
      sprintf(cmd_string, "I%d?\n", s_s->num);	  
    }
  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 


  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(200);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
  if(sscanf(ret_string, "%lf%*s", val_out) != 1)
    {
      fprintf(stderr, "Bad return string: \"%s\" in read_sensor!\n", ret_string);
      return(1);
    }	
  
  msleep(1000);

  return(0);
}

/*
#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
  double     ret_val;

  if (strncmp(s_s->subtype, "Temp", 4) == 0)  // Set the control loop setpoint
    {
      if (s_s->new_set_val < 0 ) // check valid value for Temp (>0)
	{
	  fprintf(stderr, "%f is an incorrect temperature. Temp must be greater than 0... You Fool! \n", s_s->new_set_val);
	  return(1);
	}
    
      sprintf(cmd_string, "LOOP %d:SETPt %f\n", s_s->num, s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "LOOP %d:SETPt?\n", s_s->num); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
    }
    

  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 

  return(0);
}
*/


#include "main.h"
