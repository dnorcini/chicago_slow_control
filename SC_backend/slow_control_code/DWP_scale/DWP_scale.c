/* Program for reading the Yale Level Meter (V005) using the ethernet interface */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "DWP_scale"

int   inst_dev;

////////////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{ 
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  msleep(200);

  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
  shutdown(inst_dev, SHUT_RDWR);
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{   
  // Reads out the current value of the device
  // at given sensor.  0 is the first, 1, second, ...

  char  big_string[10000];
  char  ret_string[12]; 
  int   ret_val;
  int   scan_ret;
  int   max_tries = 100;

  /*
    if ((inst_dev = connect_tcp(i_s)) < 0)
    {
    fprintf(stderr, "Connect failed. \n");
    my_signal = SIGTERM;
    return(1);
    }
    msleep(500);
  */

  //read_tcp(inst_dev,  ret_string, 16);
        
  //shutdown(inst_dev, SHUT_RDWR);
  /*
    if (sscanf(ret_string, "%*s\n%*s   %lf,kg", &ret_val) != 1)
    {
    fprintf(stderr, "Read failed for sensor %s.\n", s_s->name);
    fprintf(stderr, "Read returned: %s.\n", ret_string);

    return(1);
    }
  */
  read_tcp(inst_dev, big_string, sizeof(big_string)/sizeof(char));
  msleep(500);

  do
    {
      read_tcp(inst_dev,  ret_string, 10);
      scan_ret = (sscanf(ret_string, "'0 %d", &ret_val));
      max_tries--;
      msleep(200);
    } while ((scan_ret != 1) && (max_tries > 0));
    

  if ( max_tries < 1)
    {
      fprintf(stderr, "Read failed for sensor %s.\n", s_s->name);
      fprintf(stderr, "Read returned: %s.\n", ret_string);
      return(0);
    }

  //fprintf(stdout, "Read returned: %s.\n", ret_string);
  //fprintf(stdout, "val: %d.\n", ret_val);
    
  *val_out = (double)ret_val / 100.0; // + ((ret_val * s_s->parm3) * (ret_val * s_s->parm3));
      
  msleep(500);
      
  return(0);
}



#include "main.h"

