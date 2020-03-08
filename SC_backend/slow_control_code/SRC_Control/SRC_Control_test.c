/* Program for controlling the PROSPECT belt driven source deployment system*/
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2017 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "SRC_Control_test"

double max_extension = 100; //(cm)
double min_extension = 50; //(cm)
double delta_extension = 50; //(cm)
double current_position = 10; //(cm)
int do_run = 0;
char *pos_sens_name;
time_t last_command_time = 0;
int time_between_commands = 30;  // (s)


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



void do_loop(void)
{
  if ((do_run==1) && (time(NULL) - time_between_commands > last_command_time))
    {
      last_command_time = time(NULL);
      insert_mysql_sensor_data(pos_sens_name, time(NULL), current_position, 0.0);
      current_position -= delta_extension;
      if  (current_position < min_extension)
	current_position = max_extension;
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  if (strncmp(s_s->subtype, "RUN", 3) == 0)  // Start run
    {  
      pos_sens_name = s_s->user1;
      min_extension = s_s->parm1;
      max_extension = s_s->parm2;
      delta_extension = s_s->parm3;
      current_position = max_extension;
      if (s_s->new_set_val > 0.5)   
	{
	  do_run = 1;
	} 
      else
	{
	  do_run = 0;
	}
    }
  return(0);
}

int main (int argc, char *argv[])
{
  char                   **my_argv;
  char                   inst_name[16];
  int                    i;
  struct inst_struct     this_inst;
  struct sensor_struct   *all_sensors;

  // save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
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
      do_loop();
      mysql_inst_run_status(&this_inst);
      sleep(2);
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
