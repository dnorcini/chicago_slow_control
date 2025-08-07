/* Program for controlling a ServerTech PDU using SNMP */
/* Cinyu Zhu, Hopkins, 2025 */

#include "SC_db_interface.h"
#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#define SNMPGET_PATH "/usr/bin/snmpget"

#define INSTNAME "Raritan_PX3"

#define OID_BASE_READ ".1.3.6.1.4.1.13742.6.4.1.2.1.3.1"
#define OID_BASE_WRITE ".1.3.6.1.4.1.13742.6.4.1.2.1.2.1"
#define READ_COMMUNITY "public"
#define WRITE_COMMUNITY "private"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  char cmd_string[64];
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
  char cmd_string[256];
  char buffer[256], ret_string[256];
  FILE *tmp;

  // printf("in read sensor");
  if (strncmp(s_s->subtype, "outlet", 6) == 0)
  {
    if (s_s->num < 1 || s_s->num > 8)
    {
      fprintf(stderr, "Outlet index %d is invalid. Must be between 1 and 8.\n", s_s->num);
      return (1);
    }

    // Build SNMP command for outlet 4
    char full_oid[128];
    sprintf(full_oid, "%s.%d", OID_BASE_READ, s_s->num);
    sprintf(cmd_string, "snmpget -v2c -c %s %s %s", READ_COMMUNITY, i_s->dev_address, full_oid);
    tmp = popen(cmd_string, "r");
    if (tmp == NULL)
    {
      fprintf(stderr, "Failed to run command: %s\n", cmd_string);
      return 1;
    }

    while (fgets(buffer, sizeof(buffer), tmp) != NULL)
    {
      sprintf(ret_string, "%s", buffer);
    }
    pclose(tmp);

    // output process
    char *val_ptr = strstr(ret_string, "INTEGER:");

    if (val_ptr == NULL)
    {
      fprintf(stderr, "No INTEGER tag found in return string: \"%s\"\n", ret_string);
      return 1;
    }

    // Move pointer to the opening parenthesis
    char *paren_ptr = strchr(val_ptr, '(');
    if (paren_ptr == NULL)
    {
      fprintf(stderr, "No '(' found in return string: \"%s\"\n", val_ptr);
      return 1;
    }

    int state;
    if (sscanf(paren_ptr + 1, "%d", &state) != 1)
    {
      fprintf(stderr, "Failed to parse integer from: \"%s\"\n", paren_ptr);
      return 1;
    }

    // Output or store the result
    // printf("Outlet 4 state: %d\n", state);
    *val_out = (double)state;
    return 0;
  }

  fprintf(stderr, "Unsupported sensor subtype: %s\n", s_s->subtype);
  return 1;
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char cmd_string[512];
  char ret_string[512];
  char buffer[128];
  FILE *tmp;

  // set one sensor
  if (strncmp(s_s->subtype, "setmain", 7) == 0)
  {
    if (s_s->num < 1 || s_s->num > 8)
    {
      fprintf(stderr, "Outlet index %d is invalid. Must be between 1 and 8.\n", s_s->num);
      return 1;
    }

    int set_val = (int)s_s->new_set_val; // 1 = ON, 0 = OFF
    if (set_val != 0 && set_val != 1)
    {
      fprintf(stderr, "Invalid set value: %d. Must be 1 (on) or 2 (off).\n", set_val);
      return 1;
    }

    char full_oid[128];
    sprintf(full_oid, "%s.%d", OID_BASE_WRITE, s_s->num);

    sprintf(cmd_string, "snmpset -v2c -c %s %s %s i %d", WRITE_COMMUNITY, i_s->dev_address, full_oid, set_val);

    tmp = popen(cmd_string, "r");
    if (tmp == NULL)
    {
      fprintf(stderr, "Failed to run command: %s\n", cmd_string);
      return 1;
    }

    while (fgets(buffer, sizeof(buffer), tmp) != NULL)
    {
      sprintf(ret_string, "%s", buffer);
    }

    pclose(tmp);
    return 0;
  }
  else
  {
    fprintf(stderr, "Unsupported sensor subtype for setting: %s\n", s_s->subtype);
    return 1;
  }
}

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

  // temp for debug
  parse_CL_for_string(argc, argv, INSTNAME, inst_name);
  // strcpy(inst_name, "Raritan_PX3");

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
  signal(SIGHUP, handler);  // 1
  signal(SIGINT, handler);  // 2
  signal(SIGQUIT, handler); // 3
  signal(SIGTERM, handler); // 15

  register_inst(&this_inst);
  printf("register_inst \n");

  if (set_up_inst(&this_inst, all_sensors))
  {
    printf("at_set_up_inst \n");
    msleep(1000);
    my_signal = SIGHUP;
  }

  mysql_inst_run_status(&this_inst);
  printf("finish mysql_inst_run_status \n ");

  printf("my_signal is: %d\n", my_signal);

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
    fprintf(stderr, "execv() failed.");
    exit(1);
  }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);

  free(my_argv);

  exit(0);
}

#endif // _SC_main_H_
