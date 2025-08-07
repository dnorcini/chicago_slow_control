/* Program for controlling a ServerTech PDU using SNMP */
/* Cinyu Zhu, Hopkins, 2025 */

#include "SC_db_interface.h"
#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#define SNMPGET_PATH "/usr/bin/snmpget"

#define INSTNAME "Sentry_PDU"

#define OID_BASE_READ ".1.3.6.1.4.1.1718.4.1.8.5.1.1.1.1"
#define OID_BASE_WRITE ".1.3.6.1.4.1.1718.4.1.8.5.1.2.1.1"
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
  char cmd_string[512];
  char ret_string[512];
  char buffer[128];
  FILE *tmp;
  printf("in read sensor");

  if (strncmp(s_s->subtype, "outlet", 6) == 0)
  {
    if (s_s->num < 1 || s_s->num > 8)
    {
      fprintf(stderr, "Outlet index %d is invalid. Must be between 1 and 8.\n", s_s->num);
      return (1);
    }

    char full_oid[128];
    sprintf(full_oid, "%s.%d", OID_BASE_READ, s_s->num);

    sprintf(cmd_string, "snmpget -v2c -c %s %s %s", READ_COMMUNITY, i_s->dev_address, full_oid);

    // send snmp command to command line and store response in tmp file

    // tmp = popen("snmpget -v2c -c public 192.168.2.254 .1.3.6.1.4.1.1718.4.1.8.5.1.1.1.1.2", "r");
    tmp = popen(cmd_string, "r");
    if (tmp == NULL)
    {
      fprintf(stderr, "Failed to run command: %s\n", cmd_string);
      return (1);
    }

    // read response one line at a time
    while (fgets(buffer, sizeof(buffer), tmp) != NULL)
    {
      sprintf(ret_string, buffer);
    }
    // close tmp file
    pclose(tmp);

    // output chopping
    char *val_ptr = strstr(ret_string, "INTEGER:");
    if (val_ptr == NULL)
    {
      fprintf(stderr, "No INTEGER tag found in return string: \"%s\"\n", ret_string);
      return 1;
    }

    val_ptr += strlen("INTEGER:");

    int raw_val;
    if (sscanf(val_ptr, "%d", &raw_val) != 1)
    {
      fprintf(stderr, "Failed to parse integer from: \"%s\"\n", val_ptr);
      return 1;
    }

    *val_out = (double)raw_val;
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

    int set_val = (int)s_s->new_set_val; // 1 = ON, 2 = OFF
    if (set_val != 1 && set_val != 2)
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

  // set multiple outlets all together
  else if (strncmp(s_s->subtype, "outletgroup", 11) == 0)
  {
    int set_val = (int)s_s->new_set_val;
    if (set_val != 1 && set_val != 2)
    {
      fprintf(stderr, "Invalid set value for setpair: %d\n", set_val);
      return 1;
    }

    if (is_null(s_s->user1))
    {
      fprintf(stderr, "user1 is empty; no outlet list provided.\n");
      return 1;
    }

    // Duplicate the string so strtok doesn't modify original
    char user_input_copy[128];
    strncpy(user_input_copy, s_s->user1, sizeof(user_input_copy));
    user_input_copy[sizeof(user_input_copy) - 1] = '\0';

    char *token = strtok(user_input_copy, ",");
    while (token != NULL)
    {
      int outlet = atoi(token);
      if (outlet < 1 || outlet > 8)
      {
        fprintf(stderr, "Invalid outlet number %d in user1.\n", outlet);
        token = strtok(NULL, ",");
        continue;
      }

      sprintf(cmd_string, "snmpset -v2c -c %s %s %s.%d i %d",
              WRITE_COMMUNITY, i_s->dev_address, OID_BASE_WRITE, outlet, set_val);
      tmp = popen(cmd_string, "r");
      if (tmp == NULL)
      {
        fprintf(stderr, "Failed to run command for outlet %d.\n", outlet);
        return 1;
      }

      while (fgets(buffer, sizeof(buffer), tmp) != NULL)
      {
        sprintf(ret_string, "%s", buffer);
      }

      pclose(tmp);
      token = strtok(NULL, ",");
    }
    return 0;
  }
  else
  {
    fprintf(stderr, "Unsupported sensor subtype for setting: %s\n", s_s->subtype);
    return 1;
  }
}

// Todo: Solve the problem of no pid/no process after enable the daemonize in main
// but disable daemonize, everything works fine????
// why!!!!!

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
  char inst_name[16];
  int i;
  struct inst_struct this_inst;
  struct sensor_struct *all_sensors;

  // save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++)
    my_argv[i] = strdup(argv[i]);

  my_argv[i] = NULL;

  sprintf(db_conf_file, DEF_DB_CONF_FILE);

  parse_CL_for_string(argc, argv, INSTNAME, inst_name);
  read_mysql_inst_struct(&this_inst, inst_name);
  generate_sensor_structs(&this_inst, &all_sensors);

  // detach current process
  // daemonize(this_inst.name);

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
    fprintf(stderr, "execv() failed.");
    exit(1);
  }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);

  free(my_argv);

  exit(0);
}

#endif // _SC_main_H_
