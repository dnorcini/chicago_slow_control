/* Program for reading the Yale Level Meter (V001) using the rs232 interface */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"
#include "ethernet.h"

#define INSTNAME "YLM"

int   comm_type = -1;
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
    struct  termios    tbuf;  // serial line settings 

    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
	my_signal = SIGTERM;
	return(1);
    }

    // set up the serial line parameters : 8,1,N 
    tbuf.c_cflag = CS8|CREAD|B9600|CLOCAL;
    tbuf.c_iflag = IGNBRK;;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0; // character-by-character input
    tbuf.c_cc[VTIME]= 0; // no delay waiting for characters
    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stdout, "Unable to set serial device parameters\n");
        exit(1);
    }
    tcflush(inst_dev, TCIFLUSH);

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
    // Reads out the current value of the device
    // at given sensor.  0 is the first, 1, second, ...

    int             chan = s_s->num;     
    char            ret_string[256]; 
    char            cmd_string[256];
    double          ret_val = 0;
    ssize_t         wstatus = 0;
    ssize_t         rdstatus = 0;

    memset(ret_string, 0, sizeof(ret_string));
    memset(cmd_string, 0, sizeof(cmd_string));
    tcflush(inst_dev, TCIFLUSH);
    
    sprintf(cmd_string, "R%d%c", chan, LF);
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);
    rdstatus = read_val(ret_string, sizeof(ret_string));
    
    if (rdstatus > 0)
    {
	fprintf(stderr, "Read failed. \n");
	return(1);
    }
    
    if (strncmp(ret_string, "E", 1) == 0)
    {
	fprintf(stderr, "Command string was in error! %s \n", cmd_string);
	return(1);
    }
   
    sscanf(ret_string, "R%lf", &ret_val);	    
    
    if ((ret_val == 0) || (ret_val > 1000))
	return(1);
    else
	 *val_out = ret_val * s_s->parm1 + s_s->parm2;
    
    msleep(100);
    
    return(0);
}



#include "main.h"

