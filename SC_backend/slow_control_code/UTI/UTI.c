/* Program for reading a UTI capacitive level meter using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below or file specified on command line. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"
#include "ethernet.h"

#define INSTNAME "UTI"

int   comm_type = -1;
int   inst_dev;

///////////////////////////////////////////////////////////////////////////////
double conv_data (unsigned long r_1, unsigned long r_2, unsigned long r_3, double C_CA)
{
  double N_1;
  double N_2;
  double N_3;
  double C_DA;  // This is what we are looking for, in pF.
       
  N_1=(double)r_1;
  N_2=(double)r_2;
  N_3=(double)r_3; 

  if ((r_2-r_1)==0)
    return(0);
    
  C_DA=(N_3-N_1)/(N_2-N_1)*C_CA;
  return(C_DA);
}

//////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)  
{
  unsigned char      cmd_string[16];
  unsigned char      ret_string[32];
  int                query_status;
  struct  termios    tbuf;  // serial line settings 
  char               UTI_mode[8];  // UTI mode

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
      tbuf.c_cc[VMIN] = 1; // character-by-character input
      tbuf.c_cc[VTIME]= 0; // no delay waiting for characters
      if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stdout, "Unable to set device '%s' parameters\n", i_s->dev_address);
        exit(1);
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
 
  /*
  sprintf(cmd_string, "@");
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  else                               
    query_status = query_tcp(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  sleep(1);
  
  tcflush(inst_dev, TCIFLUSH); 
  */
  sprintf(cmd_string, "@"); 
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  else        
    query_status = send(inst_dev, cmd_string, 1, 0);
  sleep(1);

  sprintf(cmd_string, "%d", (int)i_s->parm1); 
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  else        
    query_status = send(inst_dev, cmd_string, 1, 0);
  sleep(1);
  
  sprintf(cmd_string, "f"); 
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  else                       
    query_status = send(inst_dev, cmd_string, 1, 0);
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
  unsigned char    cmd_string[16];
  unsigned char    ret_string[64];
  int              query_status;
  unsigned long    res1, res2, res3, res4, res5;      
  double           C_CA = s_s->parm1;

  msleep(1000);
  memset(ret_string, 0,  sizeof(ret_string));
  sprintf(ret_string, '\0');
  sprintf(cmd_string, "m"); 
  if (comm_type == TYPE_SERIAL)	
    query_status = query_serial(inst_dev, cmd_string, 1, ret_string, sizeof(ret_string)/sizeof(ret_string));
  else        
    {
      query_status = send(inst_dev, cmd_string, 1, 0);
      msleep(200);
      query_status = recv(inst_dev, ret_string, 35, MSG_DONTWAIT);
    }
  
  //fprintf(stdout, "UTI string out: %s \n", ret_string);

  
  if ((int)i_s->parm1 == 2)
    {
      sscanf(ret_string, "%x %x %x %x %x", &res3, &res2, &res4, &res1, &res5);
	
      if(s_s->num == 0)
	{
	  *val_out = conv_data (res1, res2, res3, C_CA);
	}
      else if(s_s->num == 1)
	{
	  *val_out = conv_data (res1, res2, res4, C_CA);
	}
      else if(s_s->num == 2)
	{
	  *val_out = conv_data (res1, res2, res5, C_CA);
	}
      else
	{
	  //fprintf(stderr, "Sensor number outside range: %s \n", s_s->name);
	  return(1);
	}
    }
  else if ((int)i_s->parm1 == 4)
    {
      sscanf(ret_string, "%x %x %x", &res1, &res2, &res3);
      //fprintf(stdout, "UTI vals: %d %d %d %f \n", res1, res2, res3, C_CA);
      *val_out = conv_data (res1, res2, res3, C_CA);
    }
  //fprintf(stdout, "UTI val out: %lf \n", *val_out);
	    
  //*val_out = linear_interp(*val_out, s_s->parm3, s_s->parm4);
    
  return(0);
}



#include "main.h"
