/* This is a header file for the sensor structures fns. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2009, 2010 */
/* James public licence. */

#ifndef _SC_inst_H_
#define _SC_inst_H_

#include "SC_aux_fns.h"
#include "SC_db_interface_raw.h"

#define INST_TABLE_UPDATE_PERIOD 30
#define WATCHDOG_PERIOD 600

// This is the generic instrument structure
struct inst_struct {
    char   name[18];       // this is the name of the table containing the data
    char   description[66];  
    char   subsys[18];
    int    run;            // this will be set to 1 if this instrument is suppose to be running
    int    restart;        // this will be set to 1 if this instrument is suppose to be restarted
    int    WD_ctrl;        // this will be set to 1 if this instrument is suppose to be controlled by the Watchdog
    char   path[258];      // path to the executable
    char   dev_type[18];
    char   dev_address[26];
    time_t start_time;
    time_t last_update_time;
    pid_t  PID;
    char   user1[18];
    char   user2[18];
    double parm1;
    double parm2;
  
    int    num_active_sensors;
};

int register_inst(struct inst_struct *i_s);

int unregister_inst(struct inst_struct *i_s);

int update_inst_state(struct inst_struct *i_s);

int read_mysql_inst_struct(struct inst_struct *i_s, char *inst_name);

int read_inst_struct_file(struct inst_struct *i_s, char *inst_file_name);

int write_inst_struct_file(struct inst_struct *i_s, char *inst_file_name);

int mysql_inst_run_status(struct inst_struct *i_s);

#endif
