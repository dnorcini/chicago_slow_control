/* This is the slow control watchdog system. */
/* The idea is to read the instruments database and */
/* start, stop, or reset instruments when desired. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2012 */
/* James public licence. */

#include "SC_aux_fns.h"
#include "SC_db_interface_raw.h"
#include "SC_sensor_interface.h"
#include "SC_inst_interface.h"

#define INSTNAME "Watchdog"

int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out){return(0);}
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s){return(0);}

int check_instruments (struct inst_struct *wd_inst)
{
    char                       query_strng[1024];  
    char                       res_strng[1024];
    char                       inst_name[18];
    char                       inst_run_command[1024];
    struct inst_struct         i_s;
    struct sys_message_struct  this_sys_message_struc;
    int                        num_rows = 0;
    int                        num_cols = 0;
    int                        my_errors = 0;
    int                        sysstatus = 0;
    int                        i; 
    time_t                     last_update_interval = time(NULL) - WATCHDOG_PERIOD;

    ////////////////////////////////////////////////////////
    ///
    ///   Start instruments when requested
    ///
    ////////////////////////////////////////////////////////

    sprintf(query_strng, "SELECT `name` FROM `sc_insts` WHERE `run` = 1 AND `PID` = -1 AND `WD_ctrl` = 1");
    my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    
    for (i=0; i < num_rows; i++)
    {
	get_element(inst_name, res_strng, num_rows, num_cols, i, 0);
	my_errors += read_mysql_inst_struct(&i_s, inst_name);
	sprintf(inst_run_command, "%s/%s %s > /dev/null &", wd_inst->path, i_s.path, i_s.name); 
	sysstatus = system(inst_run_command);
    }
    
    ////////////////////////////////////////////////////////
    ///
    ///   Looks for heartbeat, kills without mercy those 
    ///   that do not pass these tests! 
    ///   First those that are suppose to be running but 
    ///   are not, then those that didn't quit when asked.
    ///
    ////////////////////////////////////////////////////////
    
    sprintf(query_strng, "SELECT `name` FROM `sc_insts` WHERE `PID` > 0 AND `run` = 1 AND `last_update_time` < %jd AND `WD_ctrl` = 1", (intmax_t)last_update_interval);
    my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    
    for (i=0; i < num_rows; i++)
    {
	get_element(inst_name, res_strng, num_rows, num_cols, i, 0);
	my_errors += read_mysql_inst_struct(&i_s, inst_name);
	sprintf(inst_run_command, "killall -e -q -9 \"%s/%s %s\" > /dev/null &", wd_inst->path, i_s.path, i_s.name); 
	sysstatus = system(inst_run_command);
	sleep(5);
	
	sprintf(this_sys_message_struc.ip_address, " ");
	sprintf(this_sys_message_struc.subsys, "%s", i_s.dev_type);
	sprintf(this_sys_message_struc.msgs, "Watchdog system restarting instrument: %s ", i_s.name);
	sprintf(this_sys_message_struc.type, "Warning");
	this_sys_message_struc.is_error = 0;
	insert_mysql_system_message(&this_sys_message_struc);
     
	unregister_inst(&i_s);   	
    }

    sprintf(query_strng, "SELECT `name` FROM `sc_insts` WHERE `PID` > 0 AND `run` = 0 AND `last_update_time` < %jd AND `WD_ctrl` = 1", (intmax_t)last_update_interval);
    my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    
    for (i=0; i < num_rows; i++)
    {
	get_element(inst_name, res_strng, num_rows, num_cols, i, 0);
	my_errors += read_mysql_inst_struct(&i_s, inst_name);
	sprintf(inst_run_command, "killall -e -q -9 \"%s/%s %s\" > /dev/null &", wd_inst->path, i_s.path, i_s.name); 
	sysstatus = system(inst_run_command);

	unregister_inst(&i_s);   	
    }
    
    if (sysstatus == -1)
      my_errors++;

    return(my_errors);
}


int main (int argc, char *argv[])
{
    char                       **my_argv;
    char                       inst_name[18];
    int                        i;
    struct inst_struct         this_inst;
    struct sys_message_struct  this_sys_message_struc;
    int                        error_count = 0;
    time_t                     time_between_checks = 5;       // seconds

    // save restart arguments
    my_argv = malloc(sizeof(char *) * (argc + 1));
    for (i = 0; i < argc; i++) 
    {
	my_argv[i] = strdup(argv[i]);
    }
    my_argv[i] = NULL;   

    sprintf(db_conf_file, DEF_DB_CONF_FILE);
    parse_CL_for_string(argc,  argv, INSTNAME, inst_name);
    
    read_mysql_inst_struct(&this_inst, inst_name);

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
    
    register_inst(&this_inst);
    mysql_inst_run_status(&this_inst);
    while (my_signal == 0)         //  main loop here!
    {
	while(check_instruments(&this_inst))
	{
	    error_count++;
	    if (error_count > 5)
	    {
	      sprintf(this_sys_message_struc.ip_address, " ");
	      sprintf(this_sys_message_struc.subsys, "%s", this_inst.dev_type);
	      sprintf(this_sys_message_struc.msgs, "Watchdog system failed!  Please fix and restart.");
	      sprintf(this_sys_message_struc.type, "Error");
	      this_sys_message_struc.is_error = 1;
	      insert_mysql_system_message(&this_sys_message_struc);

	      my_signal = SIGHUP;
	      break;
	    }
	    sleep(1);
	}
	mysql_inst_run_status(&this_inst);	
	sleep(time_between_checks);
    }
    
    /////////////  Clean up if we get a signal
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
	fprintf(stderr, "execv() failed.");
	exit(1);
    }

    for (i = 0; my_argv[i] != NULL; i++)
	free(my_argv[i]);
    
    free(my_argv);

    exit(0); 
}
