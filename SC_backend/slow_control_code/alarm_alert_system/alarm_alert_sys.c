/* This is the slow control alarm alert system. */
/* The idea is to read the sensors database, and */
/* send out emails/SMS if any of the tripped alarm */
/* bits are set to 1. */
/* This program should probably be running on the webserver */
/* computer. */
/* Alarm_trip_sys monitors the db to set the trip bits and   */
/* should be located only on the master db computer.  */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007 */
/* James public licence. */

#include "SC_alarms.h"

#define INSTNAME "alarm_alert_sys"

int trigger_light(struct inst_struct *i_s)
{
  int ret = 0;
  if (!(is_null(i_s->user1)))
    ret += insert_mysql_sensor_data(i_s->user1, time(NULL), 1.0, 0.0);
  return(ret);
}

int trigger_siren(struct inst_struct *i_s)
{
  int ret = 0;
  if (!(is_null(i_s->user2)))
    ret += insert_mysql_sensor_data(i_s->user2, time(NULL), 1.0, 0.0);
  return(ret);
}

int turn_off_light_siren(struct inst_struct *i_s)
{
  int ret = 0;
  if (!(is_null(i_s->user1)))
    ret += insert_mysql_sensor_data(i_s->user1, time(NULL), 0.0, 0.0);
  if (!(is_null(i_s->user2)))
    ret += insert_mysql_sensor_data(i_s->user2, time(NULL), 0.0, 0.0);
  return(ret);
}

int proc_and_send_msg(char *message, char *query_strng)
{
    /// Takes the results of 'on_call_users' and 'all_users' and loops over the 
    /// email addresses to send the messages out.
    int ret_val = 0;
    
    char res_strng[1024];
    char address[64];           // this is the element of the db, also the email/sms address
    int  num_rows = 0;
    int  num_cols = 0;
    int  i;
  
    ret_val += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    for (i=0; i < num_rows; i++)
    {
	memset(address, 0, sizeof(address));
	sprintf(address, "\n");
	get_element(address, res_strng, num_rows, num_cols, i, 0);
	if ((strlen(address) > 3) && (strcmp(address, "NULL") != 0))
	    ret_val += send_mail_message(address, message);
    }
    
    return(ret_val);
}

int on_call_users(char *message)
{
    ///  Finds all the users that are on call, and sends the result to 'proc_and_send_msg' above
    int  ret_val = 0;
    char query_strng[1024];
    
    sprintf(query_strng, "SELECT `email` FROM `users` WHERE `on_call` = 1");  
    ret_val += proc_and_send_msg(message, query_strng);
    sleep(2);

    sprintf(query_strng, "SELECT `sms` FROM `users` WHERE `on_call` = 1"); 
    ret_val += proc_and_send_msg(message, query_strng);
    sleep(2);

    return(ret_val);
}

int all_users(char *message)
{
    /// Just collects all users and sends the result to 'proc_and_send_msg' above
    int ret_val = 0;
    
    char query_strng[1024];
   
    sprintf(query_strng, "SELECT `email` FROM `users` "); 
    ret_val += proc_and_send_msg(message, query_strng);
    sleep(2);

    sprintf(query_strng, "SELECT `sms` FROM `users` "); 
    ret_val += proc_and_send_msg(message, query_strng);
    sleep(2);

    return(ret_val);
}


int check_for_alerts(time_t *last_check_time, struct inst_struct *i_s)
{
    /// looks at the msg_log, and reports any new messages with an is_error = 1
    int ret_val = 0;
    
    char query_strng[1024];
    char message[4096];
    char submessage[1024];
    int  msg_id_array[64];
    int  num_msgs;
    int  i;
    time_t check_time;
    time_t alarm_time;
    struct global_struct       global_alarm_struct;
    struct sys_message_struct  this_sys_message_struc;

    check_time = time(NULL);
    sprintf(query_strng, "SELECT `msg_id` FROM `msg_log` WHERE `is_error` = 1 AND `time` > %lu ORDER BY `time` DESC", (unsigned long)*last_check_time);
    ret_val += read_mysql_int_array(query_strng, msg_id_array, &num_msgs);
    
    if (num_msgs > 0)
      {
	sprintf(message, "Slow control alert! \n");
	for (i = 0; i < num_msgs; i++)
	  {
	    ret_val += read_mysql_system_message(&this_sys_message_struc, msg_id_array[i]);
	    strcat(message, " ** ");
	    alarm_time = this_sys_message_struc.time;
	    strcat(message, ctime(&alarm_time));
	    strcat(message, " --- ");
	    strcat(message,this_sys_message_struc.subsys);
	    strcat(message, " --- ");
	    strcat(message,this_sys_message_struc.msgs);
	    strcat(message, " ** \n");
	  }

	read_mysql_global_var(&global_alarm_struct, global_alarm_struct_name);
	global_alarm_struct.int1 = 1;
	global_alarm_struct.int2 = 0;
	global_alarm_struct.int4 = 0;
	ret_val += set_mysql_global_var(&global_alarm_struct);	

	while (global_alarm_struct.int1)
	{
	    // deal with globals to see where we are in the alert escalation scheme 
	    // all of global_alarm_struct.int* should be 0 if no alarm has been sent out yet.
	    read_mysql_global_var(&global_alarm_struct, global_alarm_struct_name);
	    if (time(NULL) > global_alarm_struct.int2 + escalation_time)
	    {
		if (global_alarm_struct.int4 > 0)
		{
		    sprintf(submessage, "This is alert number %d for this problem!\n", global_alarm_struct.int4+1);
		    strcat(message, submessage);
		}
		if (global_alarm_struct.int4 < 2)
		  {
		    ret_val += on_call_users(message);	
		    ret_val += trigger_light(i_s);
		    ret_val += trigger_siren(i_s);
		  }
		else
		    ret_val += all_users(message);

		global_alarm_struct.int2 = time(NULL);
		global_alarm_struct.int4++;
		ret_val += set_mysql_global_var(&global_alarm_struct);	
	    }
	    sleep(5);
	    mysql_inst_run_status(i_s);
	    read_mysql_global_var(&global_alarm_struct, global_alarm_struct_name);
	    if (global_alarm_struct.int1 == 0)
	      ret_val += turn_off_light_siren(i_s);	
	}
	check_time = time(NULL);    
    }
    else
	check_time -= 2;	
    
    *last_check_time = check_time;

return(ret_val);
}


int main (int argc, char *argv[])
{ 
    char                       **my_argv;
    char                       inst_name[16];
    int                        i;
    struct inst_struct         this_inst;
    struct sys_message_struct  this_sys_message_struc;
    int                        error_count = 0;
    time_t                     last_check_time = time(NULL)-60;   // initialize to 1 minute before start of program run.
    time_t                     time_between_checks = 10;       // seconds

    // save restart arguments
    my_argv = malloc(sizeof(char *) * (argc + 1));
    for (i = 0; i < argc; i++) 
	my_argv[i] = strdup(argv[i]);
    
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

    while (my_signal == 0)                             //  main loop here!
    {
	while(check_for_alerts(&last_check_time, &this_inst))
	{
	    error_count++;
	    if (error_count > 12)
	      {
		fprintf(stderr, "Alert system failed!  Please fix and restart.\n");
		sprintf(this_sys_message_struc.ip_address, " ");
		sprintf(this_sys_message_struc.subsys, this_inst.dev_type);
		sprintf(this_sys_message_struc.msgs, "Alert system failed!  Please fix and restart.");
		sprintf(this_sys_message_struc.type, "Error");
		this_sys_message_struc.is_error = 1;
		insert_mysql_system_message(&this_sys_message_struc);
		
		my_signal = SIGHUP;
		break;
	      }
	    sleep(5);
	}
	error_count = 0;
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
	execv(my_argv[0], my_argv);
	fprintf(stderr, "execv() failed.");
	exit(1);
    }

    for (i = 0; my_argv[i] != NULL; i++)
	free(my_argv[i]);
    
    free(my_argv);

    exit(0); 
}

