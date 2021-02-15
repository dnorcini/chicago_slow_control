/* Program for reading/controlling AgilentE3646 power supply */
/* and putting said readings in to a mysql database. */
/* defined below. */
/* D.Norcini, UChicago, 2020*/
/* Modified AgilentE3646 code for SRSDC205 by  J.Cuevas-Zepeda */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "SRSDC205"

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
  msleep(200);

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

  if (strncmp(s_s->subtype, "volt", 4) == 0)  // Read out value for voltage
   {
      sprintf(cmd_string, "VOLT?\r\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      sleep(30); //make slow so DAQ has time to send command

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read voltage!\n", ret_string);
	  return(1);
	}	
    }

  else if (strncmp(s_s->subtype, "onoff", 5) == 0)  // Read out value for voltage on/off
   {
      sprintf(cmd_string, "SOUT?\r\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      sleep(30); //make slow so DAQ has time to send command

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
          fprintf(stderr, "Bad return string: \"%s\" in read voltage on/off!\n", ret_string);
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


#include "main.h"
