/* Program for reading a LakeShore321 temp. controller */
/* and putting said readings in to a mysql database. */
/* defined below. */
/* Code by James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2015 */
/* James public licence. */
/**********************/
/* adapted LS336 for LS321*/
/* D.Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "LS321"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  char       cmd_string[64];

  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  
  sprintf(cmd_string, "*CLS\r\n");
  write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(100);

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
  double     P_range;
  double     P_percent;
  
  if (strncmp(s_s->subtype, "Temp", 4) == 0)
    {
      sprintf(cmd_string, "CDAT?\r\n"); 
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(100);

      fprintf(stdout, "Command string: %s", cmd_string);
      fprintf(stdout, "Return string: %s\n", ret_string);

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}	
    }
  /*
  else if (strncmp(s_s->subtype, "Heater", 6) == 0) // Read out heater power.
    {
      sprintf(cmd_string, "HEAT? %d\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}	
    }

  else if (strncmp(s_s->subtype, "Setpoint", 8) == 0) //Queries the setpoint 
    {
      sprintf(cmd_string, "SETP? %d\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}	
    }

  else if (strncmp(s_s->subtype, "Ramprate", 8) == 0) //Queries the ramprate
    {
      sprintf(cmd_string, "RAMPR? %d\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      memmove(&ret_string[0], &ret_string[0]+2, sizeof(ret_string)-2); // output in n,+nnn (on/off,value)
      
      if(sscanf(ret_string, "%lf", val_out) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read ramprate!\n", ret_string);
          return(1);
        }
    }
  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 
  msleep(600);
*/
  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
  double     ret_val;

  if (strncmp(s_s->subtype, "Setsetpoint", 11) == 0)  // Set the control loop setpoint
    {
      if (s_s->new_set_val < 0 ) // check valid value for Temp (>0)
	{
	  fprintf(stderr, "%f is an incorrect temperature. Temp must be greater than 0... You Fool! \n", s_s->new_set_val);
	  return(1);
	}

  /*  if (s_s->num < 1 || s_s->num > 2) // Checks correct Loop number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s->num);
	  return(1);
	} */
    
      sprintf(cmd_string, "SETP %d,%f\n", s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "SETP? %d\n"); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}
	
      /*      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
      */
    }
      
      
  else if (strncmp(s_s->subtype, "Setramprate", 11) == 0)  // Set the control loop ramprate
    {
      if (s_s->new_set_val < 0 ) // check valid value for Ramp (>0)
	{
	  fprintf(stderr, "%f is an incorrect ramprate. Ramprate must be greater than 0... You Fool! \n", s_s->new_set_val);
	  return(1);
	}

  /*  if (s_s->num < 1 || s_s->num > 2) // Checks correct Loop number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s->num);
	  return(1);
	} */
    
      sprintf(cmd_string, "RAMPR %d,1,%f\n", s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "RAMPR? %d\n"); //queries control loop ramprate
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      memmove(&ret_string[0], &ret_string[0]+2, sizeof(ret_string)-2); // output in n,+nnn (on/off,value)
	    
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read ramprate!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New ramprate of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
    }

  else if (strncmp(s_s->subtype, "Heateronoff", 11) == 0)  // Set the heater on/off
    {
     /* if (s_s->num < 1 || s_s->num > 2) // Checks correct Loop number
        {
          fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s->num);
          return(1);
        } */
      
      sprintf(cmd_string, "RANG %d,%i\n", (int)s_s->new_set_val);
      fprintf(stdout, "%s\n", cmd_string);
	    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);

      sprintf(cmd_string, "RANG? %i\n"); //queries control loop on/off
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      fprintf(stdout, "Heater status: %s\n", ret_string);
      
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read heater onoff!\n", ret_string);
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

#include "main.h"

