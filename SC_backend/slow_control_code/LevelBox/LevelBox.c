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

#define INSTNAME "YLM"

int   inst_dev;

int read_val(char *val, size_t num_bytes)
{
    int       i = 0;
    int       tries = 0;
    ssize_t   rdstatus;
    do
    {
	rdstatus = read(inst_dev, &val[i], 1);
	if (rdstatus == -1)
	{
	  perror("read()");
	  return(1);
	}
	if (rdstatus == 1)
	{
	  //fprintf(stdout, "compare: %X %X \n", val[i], LF);
	  if (val[i] == LF)
	    return(0);
	  i++;
	  tries = 0;
	}
	else
	{
	  tries++;
	  if (tries > 100)
	    return(1);
	  msleep(100);
	}
    } while (i < num_bytes);  
    return(1);
}


////////////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    struct sensor_struct *this_sensor_struc;
    char  cmd_string[128];   
    char  ret_string[128];   
    int   i;
    
    if ((inst_dev = connect_tcp(i_s)) < 0)
    {
	fprintf(stderr, "Connect failed. \n");
	my_signal = SIGTERM;
	return(1);
    }
    
    //msleep(500);
    //sprintf(cmd_string, "init \n");
    //query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
    //msleep(2000);
    read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));

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

    int             chan = s_s->num;   
    int             read_chan;
    int             range;   // 0 for low range (0-2 pF), 1 for high range (0-12 pF)
    char            ret_string[128]; 
    char            cmd_string[128];
    double          ret_val;
    ssize_t         wstatus = 0;
    ssize_t         rdstatus = 0;

    if (strncmp(s_s->user1, "low", 2) == 0)
      range = 0;
    else if
      (strncmp(s_s->user1, "high", 2) == 0)
      range = 1;
    else
      {
	fprintf(stderr, "String field 1 for sensor %s must be 'low' or 'high' to set the range.\n", s_s->name);
	return(1);
      }

    //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
    //msleep(500);
    sprintf(cmd_string, "read %d %d\n", chan, range);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
    
    if (strncmp(ret_string, "ERR", 3) == 0)
      {
	fprintf(stderr, "Command string was in error! %s For sensor %s.\n", cmd_string, s_s->name);
	return(1);
      }    
    
    if (sscanf(ret_string, "READ %d %lf", &read_chan, &ret_val) != 2)
      {
	//fprintf(stderr, "Read failed for sensor %s.\n", s_s->name);
	return(1);
      }
    if (read_chan != chan)
      {
	fprintf(stderr, "Channel numbers failed to match for sensor %s.\n", s_s->name);
	fprintf(stderr, "Ret_string: %s \n --- \n", ret_string);
	return(1);
      }
    ret_val /= 1000;  // to output in pF.

    *val_out =  s_s->parm1 + (ret_val * s_s->parm2) + (ret_val * ret_val * s_s->parm3);
    
    msleep(200);
    
    return(0);
}



#include "main.h"

