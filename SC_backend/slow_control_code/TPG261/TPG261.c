/* Program for reading TPG261 single gauge controller + pressure gauge over ethernet */
/* using RS-232 seral server and putting said readings in to a mysql database. */
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
#define INSTNAME "TPG261"

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
  char       ret_string[64];    
  
  if (strncmp(s_s->subtype, "pressure", 8) == 0)  // Read out value for pressure (mbar)
    {

      double val_out1;
      double val_out2;
      
      sprintf(cmd_string, "COM,2 \r\n");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      sscanf(ret_string, "%*d,  %lE,%*d,  %lE ", &val_out1, &val_out2);

      fprintf(stdout, "return: %s\n", &ret_string);
      fprintf(stdout, "val1: %lE\n", &val_out1);
      fprintf(stdout, "val2: %lE\n", &val_out2);
      *val_out = val_out1;                                                                                          
    }
  
  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    }                     

  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    char       ret_string[64];
    double     ret_val;

    if (strncmp(s_s->subtype, "setreset", 8) == 0)  // set the reset flag                                            
      {
        sprintf(cmd_string, "RES,%i \r\n", (int)s_s->new_set_val);
        query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
        msleep(200);
        query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

        fprintf(stdout, "Reset return: %s\n", ret_string);
        /*                                                                                                           
          if(sscanf(ret_string, "%lf", &ret_val) != 1)                                                               
          {                                                                                                          
          fprintf(stderr, "Bad return string: \"%s\" in reset flag!\n", ret_string);                                 
          return(1);                                                                                                 
          }                        
	*/
      }

    else if (strncmp(s_s->subtype, "setgaugeonoff", 14) == 0)  // set the reset flag                                
      {
        sprintf(cmd_string, "SEN,%i \r\n", (int)s_s->new_set_val); //off=1, on=2... weird
        query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
        msleep(200);
        query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

        fprintf(stdout, "Onoff return: %s\n", ret_string);
	/*                                                                                                           
          if(sscanf(ret_string, "%lf", &ret_val) != 1)                                                               
          {                                                 
	  fprintf(stderr, "Bad return string: \"%s\" in reset flag!\n", ret_string);                                 
          return(1);                                                                                                 
          }                                                                                                          
        */
      }

    return(0);
}

#include "main.h"
