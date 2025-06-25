/* Program for reading out a CAEN HV ethernet module  */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@gmail.com */
/* Copyright 2019 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 

#define INSTNAME "CAEN_HV"

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

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{

  char       cmd_string[64];
  char       ret_string[64];                      
  
  int        module_address;
  double     *val_out;

  module_address = (int)i_s->parm1;

  if (strncmp(s_s->subtype, "VSet", 4) == 0)  // set the HV voltage
    {
      sprintf(cmd_string, "$BD:%d,CMD:SET,CH:%d,PAR:VSET,VAL:%f%c%c", module_address, s_s->num, s_s->new_set_val, CR, LF);   // Set voltage
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
       
      /* if(sscanf(ret_string, "#BD:%*d,CMD:OK,VAL:%lf", val_out) != 1) */
      /* 	{ */
      /* 	  fprintf(stderr, "Bad return string: \"%s\" in set VSet!\n", ret_string); */
      /* 	  return(1); */
      /* 	}  */

    }
  else if (strncmp(s_s->subtype, "OnOff", 3) == 0)
    {
      if (s_s->new_set_val > 0.5)
	sprintf(cmd_string, "$BD:%d,CMD:SET,CH:%d,PAR:ON%c%c", module_address, s_s->num, CR, LF);   // Turn channel on
      else
        sprintf(cmd_string, "$BD:%d,CMD:SET,CH:%d,PAR:OFF%c%c", module_address, s_s->num, CR, LF); // Turn channel off

      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
     
      
      /* if(sscanf(ret_string, "%lf", val_out) != 1) */
      /* 	{ */
      /* 	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string); */
      /* 	  return(1); */
      /* 	} */
      /* sleep(1); */
    }
  else
    {
      fprintf(stderr, "No such subtype %s in %s. \n", s_s->subtype, i_s->name);	
      return(1);
    }
  
    
  
  return(0);
  
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  sleep(2);

  char       cmd_string[64];
  char       ret_string[64];                      
 
  int        module_address;

  module_address = (int)i_s->parm1;
  
  if (strncmp(s_s->subtype, "VMon", 4) == 0)  // Get voltage monitor value
    {
      sprintf(cmd_string, "$BD:%d,CMD:MON,CH:%d,PAR:VMON%c%c", module_address, s_s->num, CR, LF);   // Turn channel on
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "#BD:%*d,CMD:OK,VAL:%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read Vmon!\n", ret_string);
	  return(1);
	} 
      
    }
  else if (strncmp(s_s->subtype, "IMon", 4) == 0)  //  Get current monitor value
    {
      sprintf(cmd_string, "$BD:%d,CMD:MON,CH:%d,PAR:IMON%c%c", module_address, s_s->num, CR, LF);   // Turn channel on
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "#BD:%*d,CMD:OK,VAL:%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read Vmon!\n", ret_string);
	  return(1);
	} 
      
    }
  else
    {
      fprintf(stderr, "No such subtype %s in read_sensor,  %s. \n", s_s->subtype, i_s->name);	
      return(1);
    }
  
  return(0);
}

#include "main.h"
