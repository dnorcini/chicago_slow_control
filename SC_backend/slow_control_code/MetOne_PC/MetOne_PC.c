/* Program for reading the MetOne particle counter */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2017 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "MetOne_PC"

int inst_dev;

int comm_type;

//////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  
  char       cmd_string[64];
  char       ret_string[64];             
  
  sprintf(cmd_string, "U12%c", CR);   // start comms
  query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Startup:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Startup:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Startup:\n %s \n", ret_string);

  sprintf(cmd_string, "T30%c", CR);   // start comms
  query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Set time:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Set time:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Set time:\n %s \n", ret_string);

  //sprintf(cmd_string, "A0%c", CR);   // start comms
  //query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Turn off auto:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Turn off auto:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "Turn off auto:\n %s \n", ret_string);

  close(inst_dev);

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
  int        return_int1 = 0;
  int        return_int2 = 0;
  int        query_status = 0;
  int        tries = 0;

  s_s->data_type = DONT_AVERAGE_DATA_OR_INSERT;

  inst_dev = connect_tcp(i_s);
  
  sprintf(cmd_string, "U12%c", CR);   // start comms
  query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

  sleep(2);
  
  //while (return_int1 == 0)
  //{
      
  sprintf(cmd_string, "S");   // start counting
  query_status += query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "S Return string:\n %s \n", ret_string);
  //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
  //fprintf(stdout, "S Return string:\n%s\n", ret_string);
  sleep(35);
  
  while (tries < 5)
    {
      sprintf(cmd_string, "L");   // list output
      query_status += query_tcp(inst_dev, cmd_string,  strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      //read_tcp(inst_dev, ret_string, sizeof(ret_string)/sizeof(char));
      //fprintf(stdout, "L Return string:\n%s\n", ret_string);

      if(sscanf(ret_string, "L %*d/%*d/%*d,%*d:%*d:%*d,%*d,%*f,%d,%*f,%d,%*s", &return_int1, &return_int2) == 2)
	tries = 20;
      else
	tries++;
      sleep(1);
    }
  //}

  close(inst_dev);
  
  if (tries == 5)
     {
      fprintf(stderr, "Continued bad return value in read.\n");
      return(0);
    } 

  if (query_status != 0)
    {
      fprintf(stderr, "One of the queries failed.\n");
      //return(0);
    } 
 
  if (s_s->num == 1)
    *val_out = (double)return_int1;
  else
    *val_out = (double)return_int2;

  add_val_sensor_struct(s_s, time(NULL), *val_out);
  s_s->rate = 0;
  write_temporary_sensor_data(s_s);
  return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
      
}

	 
#include "main.h"
