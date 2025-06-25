/* This is the header file for interfacing with the MySQL database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006 */
/* James public licence. */

#ifndef _SC_db_H_
#define _SC_db_H_

#include <stdio.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <my_global.h>
//#include <my_sys.h>
//#include <my_getopt.h>
#include <mysql.h>
#include <unistd.h>

#include "SC_aux_fns.h"

char db_conf_file[1024]; 
#define DEF_DB_CONF_FILE "/etc/slow_control_db_conf.ini"

static char *opt_host_name __attribute__ ((unused)) = NULL;      /* server host (default=localhost) */
static char *opt_user_name __attribute__ ((unused)) = NULL;      /* username (default=login name) */
static char *opt_password __attribute__ ((unused))=  NULL;       /* password (default=none) */
static unsigned int opt_port_num __attribute__ ((unused)) = 0;   /* port number (use built-in value) */
static char *opt_socket_name __attribute__ ((unused)) = NULL;    /* socket name (use built-in value) */
static char *opt_db_name __attribute__ ((unused)) = NULL;        /* database name (default=none) */
static unsigned int opt_flags __attribute__ ((unused)) = 0;      /* connection flags (none) */
static int ask_password __attribute__ ((unused)) = 0;            /* whether to solicit password */
static MYSQL *conn __attribute__ ((unused));                     /* pointer to connection handler */

// The generic globals structure
struct global_struct {
  char    name[18];       // this is the name of the table containing the data
  int     int1;
  int     int2;
  int     int3;
  int     int4;
  double  double1;
  double  double2;
  double  double3;
  double  double4;
  char    string1[18];
  char    string2[18];
  char    string3[18];
  char    string4[18];
};


// Structure for containing systems messages
// type is:  "Alarm"
//           "Alert"
//           "Config."
//           "Error"
//           "Setpoint"
//           "Shifts"
//  and is defined in the table `msg_log_types`
//  'is_error' should be 1 for "Error" and "Alarm", 0 otherwise.
//  Alerts are sent out for is_error == 1
struct sys_message_struct {
  int     msg_id;
  int     time;              // time of insertion, set to 0 
  char    ip_address[18];
  char    subsys[18];
  char    msgs[1024];
  char    type[18];
  int     is_error;
};




// These all return 0 if they succeed and 1 if they fail, so it is sane to put them
// in a while loop with a sleep(1) in case the db is too busy at the time for the query. 

int insert_mysql_sensor_data (char *sensor_name, time_t t_in, double v_in, double r_in);  
int insert_mysql_sensor_array_data (char *sensor_name, time_t t_in, double v_in, double r_in, double x0, double x1, int dxN, double y0, char *y);   

int read_mysql_sensor_data (char *sensor_name, time_t *t_out, double *v_out, double *r_out); // read data from mysql

int read_mysql_system_message(struct sys_message_struct *sm_s, int mesg_id);
int insert_mysql_system_message(struct sys_message_struct *sm_s);   

int insert_mysql_fast_DAQ_end_time(time_t start_time);    // takes start_time from above and adds the end_time to the DAQ log.

double read_mysql_sensor_alarm_high (char *sensor_name);                // read the sensor alarm high setpoint 
double read_mysql_sensor_alarm_low (char *sensor_name);                 // read the sensor alarm low setpoint 

int read_mysql_global_var(struct global_struct *g_s, char *global_name);
int set_mysql_global_var(struct global_struct *g_s);

#endif
