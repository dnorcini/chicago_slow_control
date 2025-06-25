/* Program for reading the Superlogics bus system using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2007 */
/* GPL public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_config_files.h"
#include "SC_aux_fns.h"

#include "rs232.h"

#define num_sensors 12

#define CONFIGFILE "/etc/SuperLogics.conf"


int main (int argc, char *argv[])
{
    FILE       *file_des;        // file descriptor for the config file 
    int        fd_dev;   /* FD for the open serial port */
    char       serial_port_name[256]; /* Name of the serial port*/
    char       cmd_string[16];
    char       val[16];
  
    int        i;
 
    struct  termios    tbuf;   //serial line settings 

    file_des = parse_command_line(argc, argv, CONFIGFILE);
    if (0 != retrieve_config_parms(file_des, "device_name", serial_port_name, 0))
    {
	fprintf(stderr, "Cound not find device parameter.\n");
	exit(1);
    }   
    fclose(file_des);

    if (( fd_dev = open(serial_port_name, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
        exit(1);
    }

    
    // set up the serial line parameters : 
    tbuf.c_cflag = CS8|CREAD|B9600|CLOCAL;
    tbuf.c_iflag = IGNBRK;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0;
    tbuf.c_cc[VTIME]= 0; 
    if (tcsetattr(fd_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stderr, "%s: Unable to set device '%s' parameters\n",argv[0], serial_port_name);
        exit(1);
    }

/*
   fprintf(stdout, "Turn on ADC module and all others off.  Press return when ready");
    getchar();
    sprintf(cmd_string, "~011");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));  
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);

    sprintf(cmd_string, "%%01010C0600");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);

    sprintf(cmd_string,"$012");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);
    

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    fprintf(stdout, "Turn on Counter 1 module and all others off.  Press return when ready");
    getchar();
    
    sprintf(cmd_string, "%%0102500600");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);

    sprintf(cmd_string, "~021");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));  
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);

    sprintf(cmd_string,"$022");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);


    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    fprintf(stdout, "Turn on Counter 2 module and all others off.  Press return when ready");
    getchar();

    sprintf(cmd_string, "%%0103500600");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);

    sprintf(cmd_string, "~031");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));  
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);
    
    sprintf(cmd_string,"$032");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);
*/
    

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    fprintf(stdout, "Turn on the DAC module and all others off.  Press return when ready");
    getchar();

    for (i=1; i<=9; i++)
    {
	
	sprintf(cmd_string, "~%02i1", i);
	fprintf(stdout, "Sending Command: %s \n", cmd_string);
	write(fd_dev, cmd_string, strlen(cmd_string));
	write(fd_dev, &CR, sizeof(char));  
	sleep(1);
	read(fd_dev, &val, 16);
	fprintf(stdout, "\n Return from dev: \n %s \n", val);
	msleep(100);
	
	sprintf(cmd_string, "%%%02i04320600", i);
	fprintf(stdout, "Sending Command: %s \n", cmd_string);
	write(fd_dev, cmd_string, strlen(cmd_string));
	write(fd_dev, &CR, sizeof(char));
	sleep(1);
	read(fd_dev, &val, 16);
	fprintf(stdout, "\n Return from dev: \n %s \n", val);
	msleep(100);
    }
    
    sprintf(cmd_string,"$042");
    fprintf(stdout, "Sending Command: %s \n", cmd_string);
    write(fd_dev, cmd_string, strlen(cmd_string));
    write(fd_dev, &CR, sizeof(char));
    sleep(1);
    read(fd_dev, &val, 16);
    fprintf(stdout, "\n Return from dev: \n %s \n", val);
    sleep(1);
    
    
    close(fd_dev);
    
    exit(0);
}

