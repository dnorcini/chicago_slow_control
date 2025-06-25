/* Program for reading BK Precision 1856D counter */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2016 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"

#define INSTNAME "BK_1856D"

int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  struct  termios    tbuf;  // serial line settings 
  char               cmd_string[16];  
  char               val[64];       
  ssize_t wstatus, rdstatus;

  if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
      fprintf(stderr, "Unable to open tty port specified:\n");
      my_signal = SIGTERM;
      return(1);
    }
    
  // set up the serial line parameters : 8,1,N 
  tbuf.c_cflag = CS8|CREAD|B9600|CLOCAL;
  tbuf.c_iflag = IGNBRK;
  tbuf.c_oflag = 0;
  tbuf.c_lflag = 0;
  tbuf.c_cc[VMIN] = 0; 
  tbuf.c_cc[VTIME]= 0; 
  if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
    fprintf(stdout, "Unable to set device '%s' parameters\n", i_s->dev_address);
    exit(1);
  }

  sprintf(cmd_string, "R1");
  wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
  wstatus = write(inst_dev, &CR, sizeof(char));  
  sleep(1);

  sprintf(cmd_string, "F4");
  wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
  wstatus = write(inst_dev, &CR, sizeof(char));  
  sleep(1);

  sprintf(cmd_string, "D");
  wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
  wstatus = write(inst_dev, &CR, sizeof(char));  
  sleep(1);
  rdstatus = read(inst_dev, &val, 32);

  tcflush(inst_dev, TCIFLUSH); 
   
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
  // Reads out the counter
  
  s_s->data_type = COUNTER_DATA;   // changes the data type so that the SC does not try to average

  unsigned long  counts;
  char       ret_string[32];         
  ssize_t wstatus, rdstatus;

  sleep(1);

  wstatus = write(inst_dev, "D", sizeof(char));   
  wstatus = write(inst_dev, &CR, sizeof(char));  
  
  rdstatus = read(inst_dev, &ret_string, 32);
  fprintf(stdout, "Ret string: %s \n", ret_string);

   if (sscanf(ret_string, "%lu", &counts) !=1) 
    {
      fprintf(stderr, "Bad return string: \"%s\" \n", ret_string);
      return(1);
    }

  *val_out = (double)counts;
    
  if (counts > 0)
    {
      add_val_sensor_struct(s_s, time(NULL), (double)counts);
      
      if (s_s->times[s_s->index] - s_s->times[dec_index(s_s->index)] > 0 )
	s_s->rate = (s_s->vals[s_s->index] - s_s->vals[dec_index(s_s->index)]) /
	  ((double)(s_s->times[s_s->index] - s_s->times[dec_index(s_s->index)]));
      else
	s_s->rate = 0;
      
      write_temporary_sensor_data(s_s);
      
      return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
    }
  else 
    return(1);
}

#include "main.h"
