/* Program for reading CenterOne/Two/Three controller + pressure gauge over ethernet */
/* and putting said readings in to a mysql database. */
/* defined below. */
/**********************/
/* D.Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default.
#define INSTNAME "CenterThree"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char cmd_string[64];

  if ((inst_dev = connect_tcp(i_s)) < 0)
  {
    log_err("Connect failed. \n");
    printf(stderr, "Connect failed. \n");
    my_signal = SIGTERM;
    return (1);
  }

  return (0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{

  char cmd_string[64];
  char awk_string[64];
  char ret_string[64];

  if (strncmp(s_s->subtype, "pressure", 8) == 0) // Read out value for pressure (mbar)
  {
    if (s_s->num < 1 || s_s->num > 3) // Checks correct sensor number
    {
      log_err("%d is an incorrect value for num. Must be 1, 2, or 3. \n", s_s->num);
      return (1);
    }

    sprintf(cmd_string, "PR%d\r\n", (int)s_s->num);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), awk_string, sizeof(awk_string) / sizeof(char));
    msleep(100);
    sprintf(cmd_string, "\x05"); // hex for <ENQ>
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string) / sizeof(char));
    printf("Received: '%s'\n", ret_string);
    sscanf(ret_string, "%*d, %lE", val_out);
  }

  else // Print an error if invalid subtype is entered
  {
    log_err("Wrong type for %s \n", s_s->name);
    return (1);
  }

  msleep(600);

  return (0);
}

// #define _def_set_sensor
// int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
// {
//     char       cmd_string[64];
//     char       awk_string[64];
//     char       ret_string[64];
//     double     ret_val;

//     if (strncmp(s_s->subtype, "setgauge1onoff", 14) == 0)  // set the reset flag
//       {
//       if (s_s->num != 1)
//         {
//           log_err("%d is an incorrect value for num. Must be channel 1.\n", s_s->num);
//           return(1);
//         }

//         sprintf(cmd_string, "HVC,%d,0,0 \r\n", (int)s_s->new_set_val);
//         log_out("command: %s\n", cmd_string);
//       	query_tcp(inst_dev, cmd_string, strlen(cmd_string), awk_string, sizeof(awk_string)/sizeof(char));
//         msleep(100);
//         sprintf(cmd_string, "\x05"); //hex for <ENQ>
//         query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

//         log_out("return: %s\n", ret_string);
//       }

//     return(0);
// }
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
{
  return (0);
}
#endif // set_up_inst

#ifndef _def_clean_up_inst
// Generic clean up placeholder: to be found in instrument specific code
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  ;
}
#endif // clean_up_inst

#ifndef _def_read_sensor
// Generic read placeholder: to be found in instrument specific code
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *sensor_value)
{
  return (0);
}
#endif // read_sensor

#ifndef _def_set_sensor
// Generic set placeholder: to be found in instrument specific code
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  return (0);
}
#endif // set_sensor

int main(int argc, char *argv[])
{
  char **my_argv;
  char inst_name[16] = {0};
  int i;
  struct inst_struct this_inst;
  struct sensor_struct *all_sensors;

  // save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++)
    my_argv[i] = strdup(argv[i]);

  my_argv[i] = NULL;
  printf("Command-line arguments:\n");
  for (int j = 0; j < argc; j++)
  {
    printf("  argv[%d] = %s\n", j, my_argv[j]);
  }

  sprintf(db_conf_file, DEF_DB_CONF_FILE);
  printf("Using DB config file: %s\n", db_conf_file);

  parse_CL_for_string(argc, argv, INSTNAME, inst_name);
  printf("Parsed instrument name: %s\n", inst_name);

  read_mysql_inst_struct(&this_inst, inst_name);
  printf("Instrument loaded from DB:\n");
  printf("  Name: %s\n", this_inst.name);
  printf("  PID: %d\n", this_inst.PID);

  generate_sensor_structs(&this_inst, &all_sensors);
  printf("Number of sensors initialized: %d\n", this_inst.num_active_sensors);

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

  while (my_signal == 0) //  main loop here!
  {
    sensor_loop(&this_inst, all_sensors);
    mysql_inst_run_status(&this_inst);
  }

  /////////////  Clean up if we get a signal
  clean_up_inst(&this_inst, all_sensors);

  unregister_inst(&this_inst);
  free(all_sensors);

  if (my_signal == SIGHUP) ///  restart called
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
    log_err("execv() failed.");
    exit(1);
  }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);

  free(my_argv);

  exit(0);
}

#endif //_SC_main_H_

// #include "main.h"
