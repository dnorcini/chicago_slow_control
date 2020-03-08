/* Program for reading a ORTEC 974 100 MHz Quad counter using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2008 */
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

#define max_num_sensors 1

#define PIDFILE "/var/run/ORTEC974.pid"
#define CONFIGFILE "/etc/ORTEC974.conf"
#define LOCKFILE "/var/lock/"

int set_up_inst(char *device_name)  
{
    char       cmd_string[16];   
    char       val[32];
    int        inst_dev;       // device handle for the instrument 
    struct  termios    tbuf;  /* serial line settings */
    
    if (( inst_dev = open(device_name, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
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
        fprintf(stderr, "Unable to set device '%s' parameters\n", device_name);
        exit(1);
    }
    
    sleep(1);
    read(inst_dev, &val, 16);
    sleep(1);
    read(inst_dev, &val, 16);

    sprintf(cmd_string, "ENABLE_REMOTE");
    write(inst_dev, cmd_string, strlen(cmd_string));
    write(inst_dev, &CR, sizeof(char));
    sleep(1);
    read(inst_dev, &val, 16);


    return(inst_dev);
}

void clean_up_inst(int inst_dev)   
{
    close(inst_dev);
}


int set_sensor_val(int inst_dev, struct sensor_struct *s_s)
{
  return(1);
}
int toggle_sensor_val(int inst_dev, struct sensor_struct *s_s)
{
  return(1);
}

double read_sensor_val(int inst_dev, struct sensor_struct *s_s)
{
    // Reads out the current value of the device
    // at given sensor.  0 is the first, 1, second, ...
    
    char       cmd_string[32];
    char       val[64]; 
    int        val_int;
    double     val_out;   

    
    sprintf(cmd_string, "SHOW_VERSION");
    write(inst_dev, cmd_string, strlen(cmd_string));
    write(inst_dev, &LF, sizeof(char));
    msleep(500);
    read(inst_dev, &val, 64);

    fprintf(stdout, "Return from dev: %s \n", val);
    sscanf(val, "#%d", &val_int);
    val_out = ((double)val_int) * 1.0;
    fprintf(stdout, "flow: : %.3f \n", val_out);
    
    return(val_out);    
}


int main (int argc, char *argv[])
{
    char       **my_argv;
    FILE       *file_des;          // file descriptor for the config file
    char       pidfile[32];
    int        pid;
    char       device_name[64];
    int        inst_dev;
    char       config_val[16];
    int        i;

    // save restart arguments
    my_argv = malloc(sizeof(char *) * (argc + 1));
    for (i = 0; i < argc; i++) 
    {
	my_argv[i] = strdup(argv[i]);
    }
    my_argv[i] = NULL;   
    
    struct     sensor_struct s_s[max_num_sensors];
    struct     sensor_struct *all_sensors;
    all_sensors = &s_s[0];

    //////////////////////////////////////////////////   parse config file
    file_des = parse_command_line(argc, argv, CONFIGFILE);

    if (0 != retrieve_config_parms(file_des, "device_name", device_name, 0))
    {
	fprintf(stderr, "Cound not find device parameter.\n");
	exit(1);
    }   
    if (0 != retrieve_config_parms (file_des, "pid_file", pidfile, 0))
    {
        sprintf(pidfile, "%s", PIDFILE);
    }   
    for (i=0; i<max_num_sensors; i++)
    {
        retrieve_config_parm_enum(file_des, "sensor_names", config_val, i);
        init_sensor_struct(&all_sensors[i], config_val, i, 1);
	
	retrieve_config_parm_enum(file_des, "sensor_rate_names", config_val, i);
	sprintf(all_sensors[i].rate_name, "%s", config_val);
    }
   
    fclose(file_des);
    ///////////////////////////////////////////////////////  end config file stuff
    
    inst_dev = set_up_inst(device_name);
       
    // detach current process
    //daemonize();
    
    // ignore these signals 
    signal(SIGINT, SIG_IGN);
    signal(SIGQUIT, SIG_IGN);
    
    // create PID file here:
    if ((pid = pid_init(pidfile)) != 0) 
    {
	error("alarm_alert_sys already running as process %d", pid);
	exit(1);
    }
    
    // install signal handler
    signal(SIGHUP, handler);
    signal(SIGINT, handler);
    signal(SIGQUIT, handler);
    signal(SIGTERM, handler);
    
    sleep(1);
    while (my_signal == 0)                             //  main loop here!
    {
        sensor_read_loop(inst_dev, &all_sensors[0], max_num_sensors);
   
      	msleep(100);
    }

    clean_up_inst(inst_dev);
    pid_exit(pidfile);

    if (my_signal == SIGHUP)         ///  restart called
    {
	long fd;
	// close all files before restart
	for (fd = sysconf(_SC_OPEN_MAX); fd > 2; fd--) 
	{
	    int flag;
	    if ((flag = fcntl(fd, F_GETFD, 0)) != -1)
		fcntl(fd, F_SETFD, flag | FD_CLOEXEC);
	}
	sleep(2);
	execv(my_argv[0], my_argv);
	error("execv() failed: %s", strerror(errno));
	exit(1);
    }

    for (i = 0; my_argv[i] != NULL; i++)
    {
	free(my_argv[i]);
    }
    free(my_argv);

    exit(0);
}
