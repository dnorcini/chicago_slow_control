/* Program for controlling the MP320 D-D neutron generator  */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"
#include "ethernet.h"

#define INSTNAME "GFM373"

int   comm_type = -1;
int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)  
{
  struct  termios    tbuf;  // serial line settings 
  char               cmd_string[32];
  char               val[64];       

  if (strncmp(i_s->dev_type, "serial", 6) == 0) 
    {
      comm_type = TYPE_SERIAL;
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
      tbuf.c_cc[VMIN] = 0; // character-by-character input
      tbuf.c_cc[VTIME]= 0; // no delay waiting for characters
      if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stdout, "Unable to set device '%s' parameters\n", i_s->dev_address);
       	my_signal = SIGTERM;
	return(1);
      }
    }
  else if (strncmp(i_s->dev_type, "eth", 3) == 0) 
    {
      comm_type = TYPE_ETH;
      if ((inst_dev = connect_tcp(i_s)) < 0)
	{
	  fprintf(stderr, "Connect failed. \n");
	  my_signal = SIGTERM;
	  return(1);
	}
    }
  else
    {
      fprintf(stderr, "Device type must be serial or eth. \n");
      my_signal = SIGTERM;
      return(1);	
    }

  sleep(1);
  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)   
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{   
  char       cmd_string[16];
  char       ret_string[16];                      
  int        val_int;   
  int        query_status=0;
  
  msleep(500);
  sprintf(cmd_string, ">1T%c", CR);
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
  else                               
    query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
  
  if (query_status != 0)
    return(1);
  
  //fprintf(stdout, "out:: %s \n", ret_string); 
  sscanf(ret_string, "#%d", &val_int);
  *val_out = (0.001 * (double)val_int) * 6.0;

  return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  // the set part is for turning on and off the ion gauge only.
  // it checks to see if it is energized, then turns it on or off 
  // if necessary.
    
  double               mon_pressure;
  unsigned char        cmd_string[32];
  unsigned char        ret_string[32];
  int                  query_status=0;
  int                  i;
  struct sys_message_struct sys_message_struc;

  if (strncmp(s_s->subtype, "", 3) != 0)
    return(0);
  

  gauge_on = is_ion_gauge_on(s_s->num);
  if (gauge_on < 0)
    return(1);

  fprintf(stderr, "It's %d.\n", gauge_on);  

  if (s_s->new_set_val > 0.5 ) // turn on
    {
      fprintf(stderr, "Turning on ion gauge.\n");

      if (gauge_on == 1)
	return(0);
      else
	{



#include "main.h"
