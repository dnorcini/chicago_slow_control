/* Program for reading the Hornet vacuum gauge over rs485 interface */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2008, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"
#include "ethernet.h"

#define INSTNAME "Hornet"

#define safe_ion_pressure 5e-3

int comm_type = -1;
int inst_dev;


int is_ion_gauge_on(int inst_num)
{
  unsigned char        cmd_string[32];
  unsigned char        ret_string[32];
  int                  query_status;
  
  msleep(400);
  cmd_string[0] = 0x21;
  cmd_string[1] = inst_num;
  cmd_string[2] = 0x15;
  cmd_string[3] = 0x00;
  cmd_string[4] = Calculate_CRC8(cmd_string, (char)(4));
    
  if (comm_type == TYPE_SERIAL)	// do serial write/read
    query_status = query_serial(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));
  else                               // do tcp write/read
    query_status = query_tcp(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));

  if (query_status != 0)
    {
      fprintf(stderr, "is_ion_gauge_on: query failed.\n");
      return(-1);
    }
  if (ret_string[4] !=  Calculate_CRC8(ret_string, (char)(4)))  
    {
      fprintf(stderr, "is_ion_gauge_on: CRC failed.\n");
      return(-1); 
    }
  
  if (ret_string[3] == 1)
    return(1);
  else
    return(0);
}

double get_pressure(int inst_num, char *gauge_string)
{
  // Reads out the current value of the device
  // at given sensor.
  unsigned char        cmd_string[32];
  unsigned char        ret_string[32];                      
  int                  query_status;
  int                  gauge_type;
  
  msleep(400);
  if (strncmp(gauge_string, "ion", 3) == 0)
    gauge_type = 2;
  else if  (strncmp(gauge_string, "c1", 2) == 0)
    gauge_type = 3;
  else if  (strncmp(gauge_string, "c2", 2) == 0)
    gauge_type = 4;
  else
    {
      fprintf(stderr, "Wrong subtype %s in get_pressure.\n", gauge_string);
      return(1);
    }

  cmd_string[0] = 0x21;
  cmd_string[1] = inst_num;
  cmd_string[2] = gauge_type;
  cmd_string[3] = 0x00;
  cmd_string[4] = 0x00;
  cmd_string[5] = 0x00;
  cmd_string[6] = 0x00;
  cmd_string[7] = 0x00;
  cmd_string[8] = Calculate_CRC8(cmd_string, (char)(8));
    
  if (comm_type == TYPE_SERIAL)	// do serial write/read
    query_status = query_serial(inst_dev, cmd_string, 9, ret_string, sizeof(ret_string)/sizeof(char));
  else                               // do tcp write/read
    query_status = query_tcp(inst_dev, cmd_string, 9, ret_string, sizeof(ret_string)/sizeof(char));
  
  /*
  fprintf(stderr, "cmd_string: \n");
  fprintf(stderr, "%x  \n", cmd_string[0]);
  fprintf(stderr, "%x  \n", cmd_string[1]);
  fprintf(stderr, "%x  \n", cmd_string[2]);
  fprintf(stderr, "%x  \n", cmd_string[3]);
  fprintf(stderr, "%x  \n", cmd_string[4]);
  fprintf(stderr, "%x  \n", cmd_string[5]);
  fprintf(stderr, "%x  \n", cmd_string[6]);
  fprintf(stderr, "%x  \n", cmd_string[7]);
  fprintf(stderr, "%x  \n", cmd_string[8]);

  fprintf(stderr, "ret_string: \n");
  fprintf(stderr, "%x  \n", ret_string[0]);
  fprintf(stderr, "%x  \n", ret_string[1]);
  fprintf(stderr, "%x  \n", ret_string[2]);
  fprintf(stderr, "%x  \n", ret_string[3]);
  fprintf(stderr, "%x  \n", ret_string[4]);
  fprintf(stderr, "%x  \n", ret_string[5]);
  fprintf(stderr, "%x  \n", ret_string[6]);
  fprintf(stderr, "%x  \n", ret_string[7]);
  fprintf(stderr, "%x  \n", ret_string[8]);
  */

  if (query_status != 0)
    {
      fprintf(stderr, "get_pressure: query failed.\n");
      return(-1);
    }
  if (ret_string[8] !=  Calculate_CRC8(ret_string, (char)(8))) 
    {
      //fprintf(stderr, "get_pressure: CRC failed.\n");   
      return(-1); 
    }

  union { float fVal; unsigned char bytes[4]; } value; 
  
  value.bytes[0] = ret_string[4];
  value.bytes[1] = ret_string[5];
  value.bytes[2] = ret_string[6];
  value.bytes[3] = ret_string[7]; 
    
  return(value.fVal);
}


//////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  struct termios       tbuf;  /* serial line settings */
   
  if (strncmp(i_s->dev_type, "serial", 6) == 0) 
    {
      comm_type = TYPE_SERIAL;
      if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
	{
	  fprintf(stderr, "Unable to open tty port specified: %s \n", i_s->dev_address);
	  my_signal = SIGTERM;
	  return(1);
	}
	
      /* set up the serial line parameters :  */
      tbuf.c_cflag = CS8|CREAD|B19200|CLOCAL;
      tbuf.c_iflag = IGNBRK;
      tbuf.c_oflag = 0;
      tbuf.c_lflag = 0;
      tbuf.c_cc[VMIN] = 0;
      tbuf.c_cc[VTIME]= 0; 
	
      if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
	fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
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
  // Reads out the current value of the device
  // at given sensor.
 
  int                  gauge_type;
  double               pressure;

  pressure =  get_pressure(s_s->num, s_s->subtype);
  msleep(200);
  pressure =  get_pressure(s_s->num, s_s->subtype);

  if (pressure < 0)
    return(1);
  
  *val_out = pressure;

  msleep(400);

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
  int                  query_status;
  int                  gauge_on = 0; // 1 for on, 0 for off
  struct sys_message_struct sys_message_struc;

  if (strncmp(s_s->subtype, "ion", 3) != 0)
    return(0);
  
  fprintf(stderr, "Setting ion gauge.\n");
  fprintf(stderr, "Check to see if on or off.\n");

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
	  if (!(is_null(s_s->user1)))
	    {
	      mon_pressure =  get_pressure(s_s->num, s_s->user1);
	      if (mon_pressure > safe_ion_pressure)
		{
		  sprintf(sys_message_struc.ip_address, "");
		  sprintf(sys_message_struc.subsys, s_s->type);
		  sprintf(sys_message_struc.msgs, "Can not turn on Hornet ion gauge: %s, %s pressure %.2e > %.1e ", 
			  s_s->name, s_s->user1, mon_pressure,  safe_ion_pressure);
		  sprintf(sys_message_struc.type, "Alert");
		  sys_message_struc.is_error = 0;
		  insert_mysql_system_message(&sys_message_struc);
		  return(0);
		}
	    }
	  
	  cmd_string[0] = 0x21;
	  cmd_string[1] = s_s->num;
	  cmd_string[2] = 0x05;
	  cmd_string[3] = 0x00;
	  cmd_string[4] = Calculate_CRC8(cmd_string, (char)(4));
	  
	  msleep(400);
	  if (comm_type == TYPE_SERIAL)      // do serial write/read
	    query_status = query_serial(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));
	  else                               // do tcp write/read
	    query_status = query_tcp(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));
	    
	  if (query_status != 0)
	    {
	      fprintf(stderr, "set_sensor: query failed.\n");
	      return(1);
	    }
	  
	  if (ret_string[4] !=  Calculate_CRC8(ret_string, (char)(4)))
	    {
	      fprintf(stderr, "set_sensor: CRC failed.\n");
	      return(1);    
	    }
	  
	  sleep(2);
	  if (is_ion_gauge_on(s_s->num) == 1)
	    return(0);
	  return(1);
	}
    }
  else  // turn off
    {
      fprintf(stderr, "Turning off ion gauge.\n");

      if (gauge_on == 0)
	return(0);
      else
	{
	  cmd_string[0] = 0x21;
	  cmd_string[1] = s_s->num;
	  cmd_string[2] = 0x06;
	  cmd_string[3] = 0x00;
	  cmd_string[4] = Calculate_CRC8(cmd_string, (char)(4));
	    
	  msleep(400);
	  if (comm_type == TYPE_SERIAL)	// do serial write/read
	    query_status = query_serial(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));
	  else                               // do tcp write/read
	    query_status = query_tcp(inst_dev, cmd_string, 5, ret_string, sizeof(ret_string)/sizeof(char));
	    
	  if (query_status != 0)
	    {
	      fprintf(stderr, "set_sensor: query failed.\n");
	      return(1);
	    }

	  if (ret_string[4] !=  Calculate_CRC8(ret_string, (char)(4)))   // repeats if the CRC check fails
	    {
	      fprintf(stderr, "set_sensor: CRC failed.\n");
	      return(1);     
	    } 
	 
	  sleep(2);
	  if (is_ion_gauge_on(s_s->num) == 0)
	    return(0);
	  return(1);
	} 
    }
  return(1); 
}

#include "main.h"
