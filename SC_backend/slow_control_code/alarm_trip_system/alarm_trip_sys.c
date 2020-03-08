/* This is the slow control alarm tripping system. */
/* The idea is to read the sensors database, and */
/* change the alarm tripped bit to 1 if a sensor goes */
/* out of range. This should be run on the master db computer */
/* Alarm_alert_sys sends out alerts to people, and should be located  */
/* on the webserver computer.  */
/* James Nikkel */
/* james.nikkel@gmail.com */
/* Copyright 2006, 2007, 2011 */
/* James public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_alarms.h"

#define INSTNAME "alarm_trip_sys"


int alarm_tripped(char *sensor_name, int on_off)
{
  /// This function connects to the database and turns the alarm_tripped bit on or
  /// off on the given sensor depending on the value of on_off.
  /// This should probably not be run remotely as only one process should be monitoring
  /// the trip condition.
    
  int  ret_val = 0;
  int  num_rows = 0;
  int  num_cols = 0;
  char query_strng[1024];

  if (on_off)
    on_off = 1;
  else
    on_off = 0;

  sprintf(query_strng, "UPDATE `sc_sensors` SET `alarm_tripped` = %d WHERE `name` = \"%s\" ", on_off, sensor_name);
  ret_val += process_statement (query_strng, NULL, &num_rows, &num_cols);
    
  if (on_off == 0)
    {
      sprintf(query_strng, "UPDATE `sc_sensors` SET `last_trip` = %d WHERE `name` = \"%s\" ", -1, sensor_name);
      ret_val += process_statement (query_strng, NULL, &num_rows, &num_cols);
    }

  return(ret_val);
}


int check_for_trip_condition (struct sensor_struct *s_s)
{
  ///  Checks the given sensors to see if it is out of range.
  ///  Reads the _last_ sensor entry, so even if the data is very old, setting
  ///  an alarm may make it go off if the sensor is not updating.
  int       ret_val = 0;
  int       num_rows = 0;
  int       num_cols = 0;
  char      query_strng[1024];
  time_t    cur_time = 0;
  double    value = 0;
  double    rate  = 0;
  int       tripped = 0;
  struct sys_message_struct  this_sys_message_struc;

  read_mysql_sensor_data(s_s->name, &cur_time, &value, &rate);
   
  if ((s_s->al_arm_val_low) && (value < s_s->al_set_val_low))
    tripped = 1;
  if ((s_s->al_arm_val_high) && (value > s_s->al_set_val_high))
    tripped = 1;
  if ((s_s->al_arm_rate_low) && (rate < s_s->al_set_rate_low))
    tripped = 1;
  if ((s_s->al_arm_rate_high) && (rate > s_s->al_set_rate_high))
    tripped = 1;

  if ( !s_s->alarm_tripped && tripped )
    {
      if (s_s->last_trip == -1)
	{
	  s_s->last_trip = time(NULL);
	  sprintf(query_strng, "UPDATE `sc_sensors` SET `last_trip` = %d WHERE `name` = \"%s\" ", s_s->last_trip, s_s->name);
	  ret_val += process_statement (query_strng, NULL, &num_rows, &num_cols);
	}
      if (time(NULL) - s_s->grace > s_s->last_trip)
	{
	  ret_val += alarm_tripped (s_s->name, 1);
	  
	  if ((s_s->al_arm_val_low) && (value < s_s->al_set_val_low))
	    sprintf(this_sys_message_struc.msgs, "Alarm setpoint tripped on sensor %s: %s: %f (%s) < %f (%s)", 
		    s_s->name, s_s->description, value, s_s->units, s_s->al_set_val_low, s_s->units);
	  else if ((s_s->al_arm_val_high) && (value > s_s->al_set_val_high))
	    sprintf(this_sys_message_struc.msgs, "Alarm setpoint tripped on sensor %s: %s: %f (%s) > %f (%s)", 
		    s_s->name, s_s->description, value, s_s->units, s_s->al_set_val_high, s_s->units);  
	  else if ((s_s->al_arm_rate_low) && (rate < s_s->al_set_rate_low)) 
	    sprintf(this_sys_message_struc.msgs, "Alarm rate setpoint tripped on sensor %s: %s: %f (%s/s) < %f (%s/s)", 
		    s_s->name, s_s->description, rate, s_s->units, s_s->al_set_rate_low, s_s->units);
	  else if ((s_s->al_arm_rate_high) && (rate > s_s->al_set_rate_high))
	    sprintf(this_sys_message_struc.msgs, "Alarm rate setpoint tripped on sensor %s: %s: %f (%s/s) > %f (%s/s)", 
		    s_s->name, s_s->description, rate, s_s->units, s_s->al_set_rate_high, s_s->units);  
	  else
	    sprintf(this_sys_message_struc.msgs, "Unknown alarm tripped on sensor %s: %s: %f (%s)", 
		    s_s->name, s_s->description, value, s_s->units);  
	  sprintf(this_sys_message_struc.ip_address, " ");
	  sprintf(this_sys_message_struc.subsys, s_s->type);
	  sprintf(this_sys_message_struc.type, "Alarm");
	  this_sys_message_struc.is_error = 1;
	  ret_val += insert_mysql_system_message(&this_sys_message_struc);
	}
    }

  if (( s_s->last_trip != -1 )  && !tripped)
    {
      sprintf(query_strng, "UPDATE `sc_sensors` SET `last_trip` = %d WHERE `name` = \"%s\" ", -1, s_s->name);
      ret_val += process_statement (query_strng, NULL, &num_rows, &num_cols);
    }

  if ( s_s->alarm_tripped && !tripped)
    ret_val += alarm_tripped(s_s->name, 0);
	
  return(ret_val);
}


int check_sensors (void)
{
  ///  Loops over all the sensors that have armed alarms and then calls "check_for_trip_condition"
  ///  to see if any are out of range.
  char                  query_strng[1024];  
  char                  res_strng[1024];
  char                  sensor_name[16];
  struct sensor_struct  s_s;
  int                   num_rows = 0;
  int                   num_cols = 0;
  int                   my_errors = 0;
  int                   i; 

  sprintf(query_strng, 
	  "SELECT `name` FROM `sc_sensors` WHERE `hide_sensor` = 0 AND (`al_arm_val_low` = 1 OR `al_arm_val_high` = 1 OR `al_arm_rate_low` = 1 OR `al_arm_rate_high` = 1)");
  my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    
  for (i=0; i < num_rows; i++)
    {
      get_element(sensor_name, res_strng, num_rows, num_cols, i, 0);
      my_errors += read_mysql_sensor_struct(&s_s, sensor_name);
      my_errors += check_for_trip_condition(&s_s);
    }
    
  ///////////////////////////////////////////////////////////////
  ///
  ///   Turn off any tripped alarms where they are _not_ armed
  ///
  ////////////////////////////////////////////////////////////////

  sprintf(query_strng, 
	  "SELECT `name` FROM `sc_sensors` WHERE `alarm_tripped` = 1 AND `al_arm_val_low` = 0 AND `al_arm_val_high` = 0  AND `al_arm_rate_low` = 0 AND `al_arm_rate_high` = 0");
  my_errors += process_statement (query_strng, res_strng, &num_rows, &num_cols);
  for (i=0; i < num_rows; i++)
    {
      my_errors += get_element(sensor_name, res_strng, num_rows, num_cols, i, 0);
      my_errors += alarm_tripped(sensor_name, 0);
    }

  return(my_errors);
}

int check_instruments(void)
{
  ///  Loops over all instrument daemons that are set to run, and not controlled by the watchdog, and checks to see if
  ///  they have updated in the last 10 minutes.  If not, set an alarm!  Yay!
  struct inst_struct         i_s;
  struct sys_message_struct  this_sys_message_struc;
  char                  query_strng[1024];  
  char                  res_strng[1024];
  char                  inst_name[16];
  int                   num_rows = 0;
  int                   num_cols = 0;
  int                   my_errors = 0;
  int                   i; 
  time_t                alert_timeout = 60*10;   // 10 minutes
  
  sprintf(query_strng, "SELECT `name` FROM `sc_insts` WHERE `run` = 1 AND `WD_ctrl` = 0");
  my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
  for (i=0; i < num_rows; i++)
    {
      get_element(inst_name, res_strng, num_rows, num_cols, i, 0);
      my_errors += read_mysql_inst_struct(&i_s, inst_name);
      if (time(NULL) - i_s.last_update_time > alert_timeout)
	{
	  sprintf(this_sys_message_struc.ip_address, " ");
	  sprintf(this_sys_message_struc.subsys, i_s.dev_type);
	  sprintf(this_sys_message_struc.msgs, "Daemon: %s has not updated in %d minutes", i_s.name, (int)(i_s.last_update_time/60.0));
	  sprintf(this_sys_message_struc.type, "Alarm");
	  this_sys_message_struc.is_error = 1;
	  insert_mysql_system_message(&this_sys_message_struc);
	}
    }
  return(my_errors);
}

int main (int argc, char *argv[])
{
  char                       **my_argv;
  char                       inst_name[16];
  int                        i;
  struct inst_struct         this_inst;
  struct sys_message_struct  this_sys_message_struc;
  int                        error_count = 0;
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
  
  while (my_signal == 0)         //  main loop here!
    {
      while(check_sensors())
	{
	  error_count++;
	  if (error_count > 12)
	    {
	      sprintf(this_sys_message_struc.ip_address, " ");
	      sprintf(this_sys_message_struc.subsys, this_inst.dev_type);
	      sprintf(this_sys_message_struc.msgs, "Alert trip system failed!  Please fix and restart.");
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
