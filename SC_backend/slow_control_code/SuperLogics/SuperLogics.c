/* Program for reading the Superlogics bus system using the rs232 interface */
/* and putting said readings into a mysql database. */
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
#define INSTNAME "SuperLogics"

int inst_dev;

// This is the SuperLogics baud rate setting.  06 is 9600 baud
#define baudrate "06"


int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char                  cmd_string[64];   
    char                  val[64];                      
    int                   i;
    ssize_t               wstatus = 0;
    ssize_t               rdstatus = 0;
    struct  termios       tbuf;  
    struct sensor_struct  *s_s;

    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified: %s \n", i_s->dev_address);
        exit(1);
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
        exit(1);
    }

    for (i = 0; i < i_s->num_active_sensors; i++)
    {
	s_s = &s_s_a[i];
	sprintf(cmd_string, "~%.2s1", s_s->user1);
	//fprintf(stdout, "Command string: %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));  
	msleep(200);
	rdstatus = read(inst_dev, &val, 16);
	msleep(200);
	sprintf(cmd_string, "%%%.2s%.2s%.2s%.2s00", s_s->user1, s_s->user1, s_s->user2, baudrate);
	//fprintf(stdout, "Command string %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(400);  
    }
    
    tcflush(inst_dev, TCIFLUSH); 
    return(0);
}

void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    char           cmd_string[64];
    char           val[64];                      
    unsigned long  val_long = 0;
    ssize_t        wstatus = 0;
    ssize_t        rdstatus = 0;

    memset(val, 0, sizeof(val));

    tcflush(inst_dev, TCIFLUSH); 
    msleep(100);
    
    if (strncmp(s_s->type, "Counter", 1) == 0)                  // Counters
    {
	sprintf(cmd_string, "#%.2s%i", s_s->user1, s_s->num);
	//fprintf(stdout, "Command string %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);  
	rdstatus = read(inst_dev, &val, 16);
	//fprintf(stdout, "Return from dev: \n %s \n", val);
	sscanf(val, ">%x", &val_long);
	*val_out = (double)val_long;	
	//fprintf(stdout, "Counters num 1 out : %lf \n \n", *val_out);
	if (*val_out > 4e8)
	{
	    ; // do something to reset counter
	}
    }
    else if (strncmp(s_s->subtype, "ADC", 3) == 0)  // Analogue in
    {
	sprintf(cmd_string, "%%%.2s%.2s%.2s%.2s00", s_s->user1, s_s->user1, s_s->user2, baudrate);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(800);  

	sprintf(cmd_string, "#%.2s%i", s_s->user1, s_s->num);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);  
	rdstatus = read(inst_dev, &val, 16);
	sprintf(cmd_string, "#%.2s%i", s_s->user1, s_s->num);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);  
	rdstatus = read(inst_dev, &val, 16);
	//fprintf(stdout, "Return string from dev: \n %s \n", val);
	sscanf(val, ">%lf", val_out);
	//fprintf(stdout, "Return val from dev: \n %lf \n", *val_out);
	*val_out=linear_interp(*val_out, s_s->parm1, s_s->parm2);
	//fprintf(stdout, "Calculated ADC value: %lf \n \n", *val_out);
    }
    else if (strncmp(s_s->subtype, "DAC", 3) == 0)  // Analogue out
    {
	sprintf(cmd_string, "#%.2s8%i", s_s->user1, s_s->num);
	//fprintf(stdout, "DAC read: Command string %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);  
	rdstatus = read(inst_dev, &val, 16);
	//fprintf(stdout, "DAC read: Return from dev: \n %s \n", val);
	sscanf(val, ">%x", &val_long);
	*val_out = (double)val_long;
	//fprintf(stdout, "DAC current value: %lf \n \n", val_out);
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
    char       cmd_string[16];
    char       ret_val[16];
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (strncmp(s_s->subtype, "DAC", 3) == 0)  // Analogue out
    {
	if (s_s->new_set_val > 0)
	    sprintf(cmd_string, "$%.2s%i+%f", s_s->user1, s_s->num, s_s->new_set_val);
	else
	    sprintf(cmd_string, "$%.2s%i%f", s_s->user1, s_s->num, s_s->new_set_val);
	//fprintf(stdout, "DAC set: Command string %s \n", cmd_string);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);

	if (s_s->new_set_val > 0)
	    sprintf(cmd_string, "$%.2s%i+%f",s_s->user1, s_s->num, s_s->new_set_val);
	else
	    sprintf(cmd_string, "$%.2s%i%f", s_s->user1, s_s->num, s_s->new_set_val);
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);
    }
    else if (strncmp(s_s->type, "Counter", 1) == 0)
    {
	sprintf(cmd_string, "$0260");
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);
	sprintf(cmd_string, "$0261");
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);
	sprintf(cmd_string, "$0360");
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	msleep(200);
	sprintf(cmd_string, "$0361");
	wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
	wstatus = write(inst_dev, &CR, sizeof(char));
	
	msleep(200);  
	rdstatus = read(inst_dev, &ret_val, 16);
	msleep(200);
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);
}



#include "main.h"

