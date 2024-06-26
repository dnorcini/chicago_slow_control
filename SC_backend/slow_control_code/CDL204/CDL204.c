/* Program for reading a Cardinal Model 204 scale using the rs232 interface */
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

#define INSTNAME "CDL204"

int   comm_type = -1;
int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)  
{
    struct  termios    tbuf;  // serial line settings 
    char               UTI_mode[8];  // UTI mode
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
    tbuf.c_cc[VMIN] = 1; // character-by-character input
    tbuf.c_cc[VTIME]= 0; // no delay waiting for characters
    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stdout, "Unable to set device '%s' parameters\n", i_s->dev_address);
        exit(1);
    }
    
    wstatus = write(inst_dev, &LF, sizeof(char));     
    wstatus = write(inst_dev, "W", sizeof(char));   
    wstatus = write(inst_dev, &CR, sizeof(char));     
    sleep(1);
    rdstatus = read(inst_dev, &val, 1);
    rdstatus = read(inst_dev, &val, 32);
    
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
    char       val[64];          
    char       flags[8];
    int        range;
    char       motion[8];
    char       units[8];
    char       val_string[16];
    ssize_t wstatus, rdstatus;

    memset(val, 0, sizeof(val));
    sprintf(val, "\n");
    memset(flags, 0, sizeof(flags));
    memset(motion, 0, sizeof(motion));	      
    memset(val_string, 0, sizeof(val_string));
    memset(units, 0, sizeof(units));
    range = 0;

    wstatus = write(inst_dev, &LF, sizeof(char));     
    wstatus = write(inst_dev, "W", sizeof(char));   
    wstatus = write(inst_dev, &CR, sizeof(char));     
    msleep(100);
    rdstatus = read(inst_dev, &val, 1);
    rdstatus = read(inst_dev, &val, 32);
    //fprintf(stdout, "Ret from dev: \n%s\n", val);
    msleep(800);
    sscanf(val, "%1c%1dG%1c*%10c%3c", flags, &range, motion, val_string, units);
    sscanf(val_string, " %lf ", val_out);

    /*
    fprintf(stdout, "Return from dev (flags): \n %s \n", flags);
    fprintf(stdout, "Return from dev (range): \n %d \n", range);
    fprintf(stdout, "Return from dev (motion): \n %s \n", motion);
    fprintf(stdout, "Return from dev (val_string): \n %s \n", val_string);
    fprintf(stdout, "Return from dev (units): \n %s \n", units);

    fprintf(stdout, "Return from dev (double): \n %lf \n", *val_out);
    */

    return(0);
}

#include "main.h"

