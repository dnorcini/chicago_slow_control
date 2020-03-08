/* Program for reading a Pfeiffer TPG 262, 2 channel vacuum gauge using the rs232 interface */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"

#define INSTNAME "TPG26X"

int inst_dev;

///////////////////////////////////////////////////////////////////////////////////////////////
int read_to_eol(char *val)
{
    int num_bytes = (int)(sizeof(val)/sizeof(char));
    int i = 0;
    int tries = 0;
    int rdstatus;
    do
    {
	rdstatus = read(inst_dev, &val[i], 1);
	if (rdstatus == -1)
	{
	    fprintf(stderr, "Error: read() in read_to_eol\n");
	    return(1);
	}
	if (rdstatus == 1)
	{
	    if (i>0)
		if ((bcmp(&LF, &val[i], 1) == 0) && (bcmp(&CR, &val[i-1], 1) == 0))
		    break;
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
    return(0);
}
///////////////////////////////////////////////////////////////////////////////////////////////


#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    struct  termios    tbuf;  /* serial line settings */
    
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
        return(1);
    }

    /* set up the serial line parameters :  */
    tbuf.c_cflag = CS8|CREAD|B9600|CLOCAL;
    tbuf.c_iflag = IGNBRK;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0;
    tbuf.c_cc[VTIME]= 0; 

    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
        return(1);
    }
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
    // Reads out the current value of the device
    // at given sensor.
    
    char       val[32];                      
    double     val_out1, val_out2;

    msleep(400);
    read_to_eol(val);
    read_to_eol(val);
    fprintf(stdout, "Return from dev (string): %s \n", val);
    sscanf(val, "%*d, %lE,%*d, %lE ", &val_out1, &val_out2);
    if (s_s->num == 1)
	*val_out = val_out1;
    else if (s_s->num == 2)
	*val_out = val_out2;
    else
    {
	fprintf(stderr, "Channel number can only be 1 or 2.\n");
	return(1);
    }
	   
    fprintf(stdout, "Return from dev (double): \n %e, %e \n", val_out1, val_out2);
    fprintf(stdout, "Return from dev (double): \n %lf \n", *val_out);

    return(0);    
}


#include "main.h"
