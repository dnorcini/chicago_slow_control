/* Program for reading the Trancell Ti-500E over an ethernet serial device server */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "Trancell_Scale"

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

  char  cmd_string[8];
  char  ret_string[32]; 
  int   ret_val;
  int   scan_ret;
  int   max_tries = 10;
  char  sign;
  float weight;
  char  units[2];
  int   i;

  sprintf(cmd_string, "P%c%c", CR, LF);

  //query_tcp(inst_dev, cmd_string, 3, ret_string, sizeof(ret_string)/sizeof(char));

  //for (i=0; i< max_retries; i++)
  do
    { 
      write_tcp(inst_dev, cmd_string, 1);
      msleep(100);
      read_tcp(inst_dev, ret_string, 30);
      
      i++;
      if (i == max_tries)
	return(1);
    } while ( sscanf(ret_string, "%*c%c%f %s %*s", &sign, &weight, units) != 3 );
  
  if (sign == '-')
    weight *= -1;
  *val_out = weight;
  
  msleep(1000);
      
  return(0);
}



#include "main.h"

