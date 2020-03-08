/* This is the header file that defines main() for all daemons */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2009 */
/* James public licence. */

#ifndef _SC_main_H_
#define _SC_main_H_


#ifndef _def_set_up_inst
// Generic set up placeholder: to be found in instrument specific code
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{return(0);}
#endif // set_up_inst

#ifndef  _def_clean_up_inst
// Generic clean up placeholder: to be found in instrument specific code
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{;}
#endif // clean_up_inst

#ifndef _def_read_sensor
// Generic read placeholder: to be found in instrument specific code
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *sensor_value) 
{return(0);}
#endif // read_sensor

#ifndef _def_set_sensor
// Generic set placeholder: to be found in instrument specific code
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s) 
{return(0);}
#endif // set_sensor


int main (int argc, char *argv[])
{
  char                   **my_argv;
  char                   inst_name[16];
  int                    i;
  struct inst_struct     this_inst;
  struct sensor_struct   *all_sensors;

  // save restart arguments
  my_argv = (char**)malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++) 
    my_argv[i] = strdup(argv[i]);
   
  my_argv[i] = NULL;   
    
  sprintf(db_conf_file, DEF_DB_CONF_FILE);

  parse_CL_for_string(argc,  argv, INSTNAME, inst_name);
  read_mysql_inst_struct(&this_inst, inst_name);
  generate_sensor_structs(&this_inst, &all_sensors);
    
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

  if (set_up_inst(&this_inst, all_sensors))
    {
      msleep(1000);
      my_signal = SIGHUP;
    }
  mysql_inst_run_status(&this_inst);
   
  while (my_signal == 0)     //  main loop here!
    {
      sensor_loop(&this_inst, all_sensors);
      mysql_inst_run_status(&this_inst);
    }
    
  /////////////  Clean up if we get a signal
  clean_up_inst(&this_inst, all_sensors);

  unregister_inst(&this_inst);
  free(all_sensors);

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


#endif // _SC_main_H_
