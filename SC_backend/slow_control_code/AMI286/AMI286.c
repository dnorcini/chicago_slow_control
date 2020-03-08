/* Program for reading an American Magnetics liquid level controler */
/* and putting said readings in to a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "serial.h"
#include "ethernet.h"

#define INSTNAME "AMI286"

int   comm_type = -1;
int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char               cmd_string[16];   
    char               ret_string[16];     
    int                query_status;
    struct  termios    tbuf;  /* serial line settings */


    if (strncmp(i_s->dev_type, "serial", 6) == 0) 
    {
	comm_type = TYPE_SERIAL;
	if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
	{
	    fprintf(stderr, "Unable to open tty port specified: %s \n", i_s->dev_address);
	    return(1);
	}

	/* set up the serial line parameters :  */
	tbuf.c_cflag = CS8|CREAD|B1200|CLOCAL;
	tbuf.c_iflag = IGNBRK;
	tbuf.c_oflag = 0;
	tbuf.c_lflag = 0;
	tbuf.c_cc[VMIN] = 0;
	tbuf.c_cc[VTIME]= 0; 

	if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
	    fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
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
    
    sprintf(cmd_string, "*CLS");
    if (comm_type == TYPE_SERIAL)	
	query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    else                            
	query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    msleep(500); 
    sprintf(cmd_string, "UNITS 0");                        // sets the output units to %
    if (comm_type == TYPE_SERIAL)
	query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    else                            
	query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    
    sleep(1);
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
    // Reads out the current value of the AMI level meter
    // at given sensor. 1 is the first, 2 the second, ...
    
    char       cmd_string[32];
    char       ret_string[32];
    int        query_status=0;
    double     ret_val_1;
    double     ret_val_2;
    int        val_int = 0;
   

    msleep(200); 
    if (strncmp(s_s->subtype, "Level", 3) == 0)  // Level 
    {
	do
	{
	    sprintf(cmd_string, "CH%d:LEVel?%c", s_s->num, LF);
	    if (comm_type == TYPE_SERIAL)	
		query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
	    else                            
		query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
	    
	    sscanf(ret_string, "%lf", &ret_val_1);
	    msleep(200); 
	    
	    if (comm_type == TYPE_SERIAL)	
		query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
	    else                            
		query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
	    
	    sscanf(ret_string, "%lf", &ret_val_2);
	    
	    if (query_status != 0)
		return(1);
	}
	while (abs(ret_val_1 - ret_val_2)/ret_val_2 > 0.05);
	*val_out = ret_val_2;

    }
    else if (strncmp(s_s->subtype, "Filling", 3) == 0)  // Fill state 
    {
	sprintf(cmd_string, "CH%d:FILL:STATE?%c", s_s->num, LF);
	if (comm_type == TYPE_SERIAL)	
	    query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
	else                            
	    query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));

        sscanf(ret_string, "%lf", &ret_val_1);
	val_int = (int)ret_val_1;
	if ((val_int == 1) || (val_int == 3))
	    *val_out = 1;
	else
	    *val_out = 0;
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    
    return(0);    
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[32];
    char       ret_string[32];
    int        query_status=0;

    int        fill_state;

    fill_state = (int)s_s->new_set_val;

    if ((fill_state < 0 ) || (fill_state > 2 ))
    {
	fprintf(stderr, "Fill state can only be 0, 1, or 2 for %s \n", s_s->name);
	return(1);
    }
    
    sprintf(cmd_string, "CH%d:FILL:STATE %d%c", s_s->num, fill_state, LF);
    if (comm_type == TYPE_SERIAL)	
	query_status = query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    else                            
	query_status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(ret_string));
    
    msleep(500);  
	
    return(0);
}


#include "main.h"
