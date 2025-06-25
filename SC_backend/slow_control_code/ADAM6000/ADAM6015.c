/* Program for reading the an ADAM6015 7 channel rtd readout over TCP/modbus */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"
#include "ADAMMain.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "ADAM6015"


const uint16_t RTD_start_address = 0;
const uint16_t num_RTD = 7;


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  int       ret;
    
  //s_s->parm1 = 1;
  //s_s->parm2 = 0;

  ret = read_ADC_raw(s_s, RTD_start_address,  num_RTD, val_out);    

  *val_out = (double)(*val_out)* 400.0/65535.0;// - 200 + 273.15; // convert to K
  //*val_out = *val_out * 20 + 273.15; // convert to K
  *val_out = s_s->parm1 + (s_s->parm2 * (*val_out)) + (s_s->parm3 * (*val_out)  * (*val_out));

  return(ret);
}


#include "main.h"

