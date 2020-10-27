/* Program for reading/controlling CryoTel GT with AVC controller */
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
#define INSTNAME "CryoTelGT_AVC"

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

  if (strncmp(s_s->subtype, "tc", 2) == 0)  // Read out value for temp sensor
   {
      sprintf(cmd_string, "TC\r");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	      
      // output in TC\n<temp>
      int data_length = ret_string[0]-ret_string[1];
      memmove(&ret_string[0], &ret_string[2], sizeof(ret_string)-data_length);

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}	
    }
   
  else if (strncmp(s_s->subtype, "p", 1) == 0)  // Read out value for power (by controller)
    {
      sprintf(cmd_string, "P\r");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in P\n<power>
      int data_length = ret_string[0]-ret_string[0];
      memmove(&ret_string[0], &ret_string[1], sizeof(ret_string)-data_length);
      
      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}
    }

  else if (strncmp(s_s->subtype, "ttarget", 7) == 0)  // Read out value for target temp
    {
      sprintf(cmd_string, "TTARGET\r");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in TTARGET\n<power>                                                                                                               
      int data_length = ret_string[0]-ret_string[6];
      memmove(&ret_string[0], &ret_string[7], sizeof(ret_string)-data_length);

      if(sscanf(ret_string, "%lf", val_out) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
          return(1);
        }
    }

    
  else       // Print an error if invalid subtype is entered
    {
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
 
  if (strncmp(s_s->subtype, "setttarget", 10) == 0)  // Set the target temp
    {    
      sprintf(cmd_string, "TTARGET=%f\r", s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "TTARGET\r"); //queries set target temp
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in TTARGET\n<power>                                                                                                               
      int data_length = ret_string[0]-ret_string[6];
      memmove(&ret_string[0], &ret_string[7], sizeof(ret_string)-data_length);

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read set target temp!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New set target temp of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read set target temp!\n", ret_string);
          return(1);
        }
    }

  else if (strncmp(s_s->subtype, "setpwout", 8) == 0)  // Set output cooling power
    {
      sprintf(cmd_string, "PWOUT=%f\r", s_s->new_set_val);
      
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
      
      sprintf(cmd_string, "PWOUT\r"); //queries set cooling power
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      // output in PWOUT\n<power>                                                                                                               
      int data_length = ret_string[0]-ret_string[4];
      memmove(&ret_string[0], &ret_string[5], sizeof(ret_string)-data_length);
      
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read set cooling power!\n", ret_string);
	  return(1);
	}
      
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	  fprintf(stderr, "New set cooling power of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	  return(1);
	  }
      
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read set cooling power!\n", ret_string);
	  return(1);
	}
    }

  else if (strncmp(s_s->subtype, "setcooler", 9) == 0)  // Set the control mode
    {
      
      if (s_s->num < 0 || s_s->num > 2) // Checks correct Loop number
        {
          fprintf(stderr, "%d is an incorrect value for num. Must be 0, 1, or 2. \n", s_s->num);
          return(1);
        }
	    
      if ((int)s_s->new_set_val == 0) sprintf(cmd_string, "COOLER=OFF\r");
      else if ((int)s_s->new_set_val == 1) sprintf(cmd_string, "COOLER=ON\r");
      else if ((int)s_s->new_set_val == 2) sprintf(cmd_string, "COOLER=POWER\r");
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
      // no check if it returns right value, more complicated because not constant size string
    }

  
  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 

  return(0);
}

#include "main.h"
