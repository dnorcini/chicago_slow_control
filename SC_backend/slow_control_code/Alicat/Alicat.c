/* Program for reading the Alicat flow controller */
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

#define INSTNAME "Alicat"

int   comm_type = -1;
int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    struct  termios  tbuf;  /* serial line settings */
    
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified: %s \n", i_s->dev_address);
        return(1);
    }

    /* set up the serial line parameters :  */
    tbuf.c_cflag = CS8|CREAD|B19200|CLOCAL;
    tbuf.c_iflag = IGNBRK;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0;
    tbuf.c_cc[VTIME]= 0; 
    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) 
    {
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
    char       ret_string[256]; 
    char       cmd_string[256];
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    memset(ret_string, 0, sizeof(ret_string));
    memset(cmd_string, 0, sizeof(cmd_string));
    tcflush(inst_dev, TCIFLUSH);

    sprintf(cmd_string, "A%c", CR);
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(200);  
    rdstatus = read(inst_dev, &ret_string, 18);
    memset(ret_string, 0, sizeof(ret_string));
    rdstatus = read(inst_dev, &ret_string, 18);
    char * * p = NULL;
    *val_out = strtod (ret_string, p);

    msleep(100);
    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    return (1);

    char       cmd_string[256];
    ssize_t    wstatus = 0;
    
    memset(cmd_string, 0, sizeof(cmd_string));
    tcflush(inst_dev, TCIFLUSH);
    
    if (s_s->new_set_val < 0 )
    {
	return(1);
    }

    sprintf(cmd_string, "%f%c", s_s->new_set_val, CR);

    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    msleep(100);  

    return(0);
}

#include "main.h"
