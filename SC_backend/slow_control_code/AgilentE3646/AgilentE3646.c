/* Program for reading/controlling AgilentE3646 power supply */
/* and putting said readings in to a mysql database. */
/* defined below. */
/* D.Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "AgilentE3646"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
  char cmd_string[64];

  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }

  sprintf(cmd_string, "*CLS\n");
  write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(600);
  
  sprintf(cmd_string, "SYST:INT RS232\n");
  write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(600);

  sprintf(cmd_string, "SYST:REM\n");
  write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(600);

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

  if (strncmp(s_s->subtype, "current", 7) == 0)  // Read out value for current
   {
      sprintf(cmd_string, "MEAS:CURR?\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(600);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", val_out) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read current!\n", ret_string);
          return(1);
        }
    }

  
  else if (strncmp(s_s->subtype, "radomir", 7) == 0)  // Read out value for voltage
   {
      sprintf(cmd_string, "MEAS:VOLT?\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(600);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	      
      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read voltage!\n", ret_string);
	  return(1);
	}	
    }

  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Subtype: %s\n", s_s->subtype);
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 
  msleep(600);

  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
  double     ret_val;

  if (strncmp(s_s->subtype, "setvolt", 7) == 0)  // Set the control loop set voltage
    {    
      sprintf(cmd_string, "VOLT %f\n", s_s->new_set_val);
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);

      sprintf(cmd_string, "VOLT?\n"); //queries control loop set voltage
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(600);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read set voltage!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New set voltage of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read set voltage!\n", ret_string);
          return(1);
        }
    }

  else if (strncmp(s_s->subtype, "setonoff", 8) == 0)  // Set output voltage on/off
    {      
      sprintf(cmd_string, "OUTP %i\n", (int)s_s->new_set_val); //need to convert to int, otherwise goes crazy
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
    }

  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 

  return(0);
}

#include "main.h"
