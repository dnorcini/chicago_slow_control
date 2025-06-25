/* Program for reading CenterThree controller + pressure gauge over ethernet */
/* and putting said readings in to a mysql database. */
/* defined below. */
/**********************/
/* D.Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "CenterThree"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)  
{
  char       cmd_string[64];

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
  char       awk_string[64];
  char       ret_string[64];    
  
  if (strncmp(s_s->subtype, "pressure", 8) == 0)  // Read out value for pressure (mbar)
    {
      if (s_s->num < 1 || s_s->num > 3) // Checks correct sensor number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, 2, or 3. \n", s_s->num);
	  return(1);
	}
  
      sprintf(cmd_string, "PR%d\r\n", (int)s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), awk_string, sizeof(awk_string)/sizeof(char));
      msleep(100);
      sprintf(cmd_string, "\x05"); //hex for <ENQ>
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
     
      sscanf(ret_string, "%*d, %lE", val_out);                    
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
    char       awk_string[64];
    char       ret_string[64];
    double     ret_val;
  
    if (strncmp(s_s->subtype, "setgauge1onoff", 14) == 0)  // set the reset flag
      {
	if (s_s->num != 1)
	  {
	    fprintf(stderr, "%d is an incorrect value for num. Must be channel 1.\n", s_s->num);
	    return(1);
	  }
	
        sprintf(cmd_string, "HVC,%d,0,0 \r\n", (int)s_s->new_set_val);
        fprintf(stdout, "command: %s\n", cmd_string);
      	query_tcp(inst_dev, cmd_string, strlen(cmd_string), awk_string, sizeof(awk_string)/sizeof(char));
	msleep(100);
	sprintf(cmd_string, "\x05"); //hex for <ENQ>                                                               
	query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	
        fprintf(stdout, "return: %s\n", ret_string);
      }
    
    return(0);
}  

#include "main.h"
