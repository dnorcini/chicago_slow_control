/* This is a header file for the sensor structures fns. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2007, 2010 */
/* James public licence. */

#ifndef _SC_sens_H_
#define _SC_sens_H_

#include "SC_aux_fns.h"
#include "SC_db_interface_raw.h"
#include "SC_inst_interface.h"
#include "modbus.h"

// This is the number of values that can be saved for each sensor instance
#define NUM_SAVED_VALS 128
// This is the number of high/low values that are discarded during calculations of averages and slopes
#define NUM_DISC_HIGH 1
#define NUM_DISC_LOW 1

//  This is where we put the fast aquire data for local display
//  Ensure that NUM_TEMP_VALS is less than NUM_SAVED_VALS
#define TEMP_DATA_DIR "/dev/shm/SC_data/"
#define NUM_TEMP_VALS 4

//  Data types:
#define SCALAR_DATA 0
#define ARRAY_DATA 1
#define DONT_AVERAGE_DATA 2
#define DONT_AVERAGE_DATA_OR_INSERT 3
#define COUNTER_DATA 3


// This is the generic sensor structure
struct sensor_struct {
    char   name[18];       // this is the name of the table containing the data
    char   description[66];
    char   type[18];
    char   subtype[18];
    int    num;
    char   instrument[18];
    char   units[18];
    char   discrete_vals[128];
    double al_set_val_low;
    double al_set_val_high;
    int    al_arm_val_low;
    int    al_arm_val_high;
    double al_set_rate_low;
    double al_set_rate_high;
    int    al_arm_rate_low;
    int    al_arm_rate_high;
    int    alarm_tripped;
    int    grace;
    int    last_trip;
    int    settable;
    int    show_rate;
    int    hide_sensor;
    int    update_period;
    char   num_format[18];
    char   user1[18];
    char   user2[18];
    char   user3[18];
    char   user4[18];
    double parm1;
    double parm2;
    double parm3;
    double parm4;

    //  internally tracked values: 
    time_t last_update_time;
    time_t next_update_time;
    time_t times[NUM_SAVED_VALS];
    double vals[NUM_SAVED_VALS];
    int    index;               // index refers to last data writen to the structure
    double max_val;
    double min_val;
    double rate;
    double avg;
    int    data_type;    // 0 for scalar, 1 for array, ... Defined above under `Data types'.
    int    inst_dev;     // For keeping track of device handles in more complex compound systems
    modbus_param_t mb_inst_dev;     // (modbus version, psi...)

    //  settables below:
    time_t last_set_time; 
    double last_set_val;
    time_t new_set_time;
    double new_set_val;
};



void init_sensor_struct(struct sensor_struct *s_s);

void add_val_sensor_struct(struct sensor_struct *s_s, time_t time, double val);

void avg_n_vals_sensor_struct(struct sensor_struct *s_s, int last_n, int disc_high_n, int disc_low_n);

void avg_vals_sensor_struct(struct sensor_struct *s_s, int disc_high_n, int disc_low_n);

void diff_vals_sensor_struct(struct sensor_struct *s_s, int disc_high_n, int disc_low_n);

void write_temporary_sensor_data(struct sensor_struct *s_s);

//  DB sensor-sensor stuff:

int generate_sensor_structs(struct inst_struct *i_s, struct sensor_struct **s_s_a);

int read_mysql_sensor_struct(struct sensor_struct *s_s, char *sensor_name);

int read_sens_struct_file(struct inst_struct *i_s, struct sensor_struct **s_s_a, char *sens_file_name);

int write_sens_struct_file(struct inst_struct *i_s, struct sensor_struct *s_s_a, char *sens_file_name);

int update_sensor_units(struct sensor_struct *s_s);

int update_mysql_sensor_refresh_time(struct sensor_struct *s_s);

int read_mysql_sensor_refresh_time(struct sensor_struct *s_s);

//  This is the new loop for reads and writes
void sensor_loop(struct inst_struct *i_s, struct sensor_struct *s_s_a);

// Generic read prototype: to be found in instrument specific code
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *sensor_value);

// Generic set prototype: to be found in instrument specific code
// returns 1 if there is a problem, 0 if all's good
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s);

int inc_index(int idx);
int dec_index(int idx);

#endif
