/* This is a header file for the alarms systems */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#ifndef _SC_alarms_H_
#define _SC_alarms_H_

#include "SC_aux_fns.h"
#include "SC_inst_interface.h"
#include "SC_sensor_interface.h"
#include "SC_db_interface_raw.h"

// time in seconds to escalate to secondary then tertiary alarms  
#define escalation_time 600 

#define global_alarm_struct_name "Master_alarm"

//  The structure that keeps the global alarm information
/*
  Alarm programs declare:
  struct global_struct global_alarm_struct;

  global_alarm_struct information:
  first see SC_db_interface.h for global_struct
  int1 == 1 means an alarm has been sent, but not yet acknowledged.
      This is set by alarm_alert_sys.
      This can be set back to 0 _only_ by the php web front end.
  int2 is the time (UTC) when the last alert message was sent out.
  int4 counts number of alerts sent out.
      These are all set back to zero by the php front end when the 
      acknowledge button is pushed.

  string4 is the last person to acknowledge an alarm.  This information is
  inserted into the sys_log.
*/

///////////////////////////   holders for the read and set sensor functions:
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

#endif
