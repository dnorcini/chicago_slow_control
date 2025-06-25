/* This is the slow control remote watchdog system. */
/* The idea is to read the master database and */
/* make sure the master watchdog is always running. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2009 */
/* James public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_aux_fns.h"
#include "SC_db_interface_raw.h"
#include "SC_inst_interface.h"
#include "SC_alarms.h"

#define INSTNAME "Remote_WD"

#define MAIN_WD_NAME "Watchdog"

#define MAX_NUM_ADDRESSES 128
char address_list[MAX_NUM_ADDRESSES][33];
int  num_addresses;


int check_status(void)
{
    char                query_strng[1024];  
    char                res_strng[1024];
    char                inst_name[16];
    struct inst_struct  i_s;
    int                 num_rows = 0;
    int                 num_cols = 0;
    int                 my_errors = 0;
    int                 i; 
    time_t              last_update_interval = time(NULL) - WATCHDOG_PERIOD;
    time_t              now;

    my_errors += read_mysql_inst_struct(&i_s, MAIN_WD_NAME);
    
    if (abs(time(NULL) - i_s.last_update_time) > WATCHDOG_PERIOD)
    {
	// send off warning 

	fprintf(stdout, "%s Last Watchdog update time: %d seconds ago.\n",  ctime(&now), abs(time(NULL) - i_s.last_update_time)); 
	
	my_errors++;
    }
    return(my_errors);
}

int fill_address_list(void)
{
    ///  Loops through all the users looking for ones that are on call
    //   returns 0 if all is cool, > 0 otherwise
    int ret_val = 0;
    
    char query_strng[1024];
    char res_strng[1024];
    char address[32];           // this is the element of the db, also the email/sms address
    int  num_found =0;
    int  num_rows = 0;
    int  num_cols = 0;
    int  i;
    memset(address, 0, sizeof(address));
    
    sprintf(query_strng, "SELECT `email` FROM `users` WHERE `on_call` = 1 ");             // first find email addys
    ret_val += process_statement (query_strng, res_strng, &num_rows, &num_cols);
    for (i=0; i < num_rows; i++)
    {
	memset(address, 0, sizeof(address));
	sprintf(address, "\n");
	get_element(address, res_strng, num_rows, num_cols, i, 0);
	if ((strlen(address) > 3) && (strcmp(address, "NULL") != 0) && (i<MAX_NUM_ADDRESSES))
	{
	    sprintf(address_list[num_found], address);
	    num_found++;
	}
    }
    
    sprintf(query_strng, "SELECT `sms` FROM `users` WHERE `on_call` = 1 ");              // then instant message addys
    ret_val += process_statement (query_strng, res_strng, &num_rows, &num_cols);
    for (i=0; i < num_rows; i++)
    {
	memset(address, 0, sizeof(address));
	sprintf(address, "\n");
	get_element(address, res_strng, num_rows, num_cols, i, 0);
	if ((strlen(address) > 3) && (strcmp(address, "NULL") != 0) && (i<MAX_NUM_ADDRESSES))
	{
	    sprintf(address_list[num_found], address);
	    num_found++; 
	}
    }
    if (num_found == 0)
	ret_val++;
    else
	num_addresses = num_found;	

    return(ret_val);
}


void help_and_exit(void)
{
    fprintf(stdout, "Usage: Remote_Watchdog INSTNAME DB_CONF \n");
    fprintf(stdout, "where INSTNAME is the instrument name, and DB_CONF is the path to the master db ini file. \n");
    fprintf(stdout, "   or: Remote_Watchdog --help (-h) for this help. \n");
    exit(1);
}

int main (int argc, char *argv[])
{
    char                   **my_argv;
    char                   inst_name[16];
    char                   message[1024];
    int                    i;
    struct inst_struct     this_inst;
    int                    error_count = 0;
    char                   db_message[1024];
    time_t                 last_alert_time = time(NULL);   // initialize to start of program run.

    // save restart arguments
    my_argv = malloc(sizeof(char *) * (argc + 1));
    for (i = 0; i < argc; i++) 
	my_argv[i] = strdup(argv[i]);
    
    my_argv[i] = NULL;   

    if (argc > 1)
    {
	if ((strncasecmp(argv[1], "help", 4) == 0) || (strncasecmp(argv[1], "-h", 2) == 0) || (strncasecmp(argv[1], "--h", 3) == 0))
	    help_and_exit();
	else if (argc == 3)   // run with command line arguments
	{
	    sprintf(inst_name, argv[1]);
	    sprintf(db_conf_file, argv[2]);
	}
	else
	    help_and_exit();
	    
    }
    else    // try to run with defaults
    {
	sprintf(inst_name, INSTNAME);
	sprintf(db_conf_file, DEF_DB_CONF_FILE);
    }
 
    if (read_mysql_inst_struct(&this_inst, inst_name) > 0)
    {
	fprintf(stdout, "Could not find instrument or db.\n");
	help_and_exit();
    }	

    // detach current process
    daemonize(this_inst.name);

    my_signal = 0;
    // ignore these signals 
    signal(SIGINT, SIG_IGN);
    signal(SIGQUIT, SIG_IGN);    
    // install signal handler
    signal(SIGHUP, handler);
    signal(SIGINT, handler);
    signal(SIGQUIT, handler);
    signal(SIGTERM, handler);

    this_inst.PID = -1;
    register_inst(&this_inst);
    mysql_inst_run_status(&this_inst);
    while (my_signal == 0)            //  main loop here!
    {
	error_count=0;	
	while(fill_address_list())
	{
	    fprintf(stderr, "get addy error\n");
	    error_count++;
	    if (error_count > 5)
		break;
	    sleep(15);
	}
	while(check_status())
	{
	    fprintf(stderr, "get status error\n");
	    error_count++;
	    if (error_count > 5)
		break;
	    sleep(15);
	}
	if (error_count > 5)
	{
	    sprintf(message, "Remote watchdog: %s, is not able to communicate with master, or master Watchdog is down.\n", inst_name);
	    for (i = 0; i < num_addresses; i++)
		send_mail_message(address_list[i], message);
	}
	mysql_inst_run_status(&this_inst);	
	sleep(INST_TABLE_UPDATE_PERIOD);         // defined in SC_inst_interface.h
    }                                 // end main loop
    
    /////////////  Clean up if we get a stop or restart signal
    unregister_inst(&this_inst);

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
	free(my_argv[i]);
    
    free(my_argv);

    exit(0); 
}
