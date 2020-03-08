/* Program for reading a CryoCon44 temp. controller */
/* using the ethernet or rs232 interface */
/* and putting said readings in to a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "rs232.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "CryoCon44"

int inst_dev;

int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char               cmd_string[16];   
    ssize_t            wstatus = 0;
    ssize_t            rdstatus = 0;
    struct  termios    tbuf;  /* serial line settings */
   
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
        exit(1);
    }
    
    /* set up the serial line parameters :  */
    tbuf.c_cflag = CS8|B9600|CREAD|PARODD;
    tbuf.c_iflag = INPCK|ISTRIP;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0; 
    tbuf.c_cc[VTIME]= 0;

    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
        exit(1);
    }

    sprintf(cmd_string, "*CLS");
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    wstatus = write(inst_dev, &CR, sizeof(char));
    wstatus = write(inst_dev, &LF, sizeof(char));    
    return(0);
}

void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    char       cmd_string[32];
    char       val[33];                      
    double     P_range;
    double     P_percent;
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (strncmp(s_s->type, "Temp", 4) == 0)       // temperatures
    {
	sprintf(cmd_string, "INPUT? %c\n", int_to_Letter(s_s->num));
	//fprintf(stdout, "in: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
	rdstatus = read(inst_dev, &val, 32); 
	//fprintf(stdout, "out: %s \n", val);
	sscanf(val, "%lf", val_out);	
    }

    else if (strncmp(s_s->type, "Power", 3) == 0)    // heaters 
    {
	sprintf(cmd_string, "LOOP %d:RANGE?\n", s_s->num);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
	rdstatus = read(inst_dev, &val, 32); 	
	sscanf(val, "%lf%*s", &P_range);
	msleep(400);
	sprintf(cmd_string, "LOOP %d:HTRREAD?\n", s_s->num+1);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	wstatus = write(inst_dev, &LF, sizeof(char));  
	msleep(400);  
	rdstatus = read(inst_dev, &val, 32); 	
	sscanf(val, "%lf\%%*s", &P_percent);
	
	*val_out = P_range*P_percent/100.0;	
    }
    else
    {
	fprintf(stderr, "Wrong type for %s \n", s_s->name);
	return(1);
    }
    
    return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];
    double     val_out = 0;  
    int        int_out = 0;
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (s_s->new_set_val < 0 )
    {
	return(1);
    }
    
    sprintf(cmd_string, "LOOP 1:SETPT %f\n", s_s->new_set_val);
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    wstatus = write(inst_dev, &CR, sizeof(char));
    wstatus = write(inst_dev, &LF, sizeof(char));  
    sleep(1);
    
    return(0);
}


#include "main.h"
