/* Program for controlling the differential pressure system using */
/* an ADAM6060 module over TCP/modbus */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009, 2010, 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

// These are modbus parameters:
#define SLAVE    0x01
#define mod_port 502

#include "../ADAM6000/ADAM60XX.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "DiffP"

///  This array converts between the valve number
///  and the relay number and box
///  { valve #, relay # }
int valve_relay_conversion[6][2] = {{10,  0},
				    {12,  1},
				    {11,  2},
				    {9,   3},
				    {6,   4},
				    {4,   5}};

// Valve states, -1:0:1:2:3:99;Error:All Closed:Bath-Evap:Bath-Cond:Evap-Cond:All Open

#define num_DiffP_valves  4   // 0 through 3

// These are for the 6060 relay box:
const uint16_t Relay_DI_start_address = 0;
const uint16_t num_Relay_DI = 6;

const uint16_t Relay_start_address = 16;
const uint16_t num_Relay = 6;

modbus_param_t inst_dev_6060;  // the relays 0 - 5


///////////////////////////////////////////////////
//  Helper fns

int compare(const void * a, const void * b)
{
  return ( *(int*)a - *(int*)b );
}

int get_col_val(int AB_array[][2], int num, int col, int ref_val)
{
  int i;
  int other_col = 0;
  if (col == 0)
    other_col = 1;

  for (i=0; i<num; i++)
    {
      if (AB_array[i][other_col] == ref_val)
	return(AB_array[i][col]);
    }
  return(-1);
}


///////////////////////////////////////////////////
//  Instrument specific 

int close_all_valves(void)
{
    uint16_t channel_address;     
    int      ret;
    int      i;
    int      timeout = 0;

    for (i = 0; i < num_Relay; i++)
    {
	channel_address = Relay_start_address + i;
	do 
	{
	    ret = force_single_coil(&inst_dev_6060, SLAVE, channel_address, 0);
	    msleep(200);
	    timeout++;
	    if (timeout > 10)
		return(1);
	} while (ret < 0);	
    }
    return(0);
}

int close_all_DiffP_valves(void)
{
    uint16_t channel_address;     
    int      ret;
    int      i;
    int      timeout = 0;

    for (i = 0; i < num_DiffP_valves; i++)
    {
	channel_address = Relay_start_address + i;
	do 
	{
	    ret = force_single_coil(&inst_dev_6060, SLAVE, channel_address, 0);
	    msleep(200);
	    timeout++;
	    if (timeout > 10)
		return(1);
	} while (ret < 0);	
    }
    return(0);
}


int open_valve(int valve_num)
{
    uint16_t channel_address;     
    int      ret;
    int      timeout = 0;
    int      relay_num;

    if ((valve_num < 4) || (valve_num > 12))
    {
	fprintf(stderr, "Valve out of range in DiffP control \n");
	return(1);
    }
    relay_num = get_col_val(valve_relay_conversion, num_Relay, 1, valve_num);

    if (relay_num < 0)
      {
	fprintf(stderr, "Bad valve to relay conversion in 'open_valve'. \n");
	return(-1);
      }

    channel_address = Relay_start_address + relay_num;
    do 
    {
      ret = force_single_coil(&inst_dev_6060, SLAVE, channel_address, 1);
      
      msleep(200);
      timeout++;
      if (timeout > 5)
	return(1);
    } while (ret < 0);	
    return(0);
}

int read_valve_state(void)
{
    //  This returns the vale states
    //  -1: 0: 1: 2: 3; Error: All Closed: Bath-Evap: Bath-Cond: Evap-Cond
    uint16_t channel_address;     
    int      ret;
    int      i;
    int      timeout = 0; 
    uint8_t  ret_val[1];
    int      open_valves[num_DiffP_valves];

    for (i = 0; i < num_DiffP_valves; i++)
    {
	channel_address = Relay_start_address + i;
	do 
	{
	    ret = read_coil_status(&inst_dev_6060, SLAVE, channel_address, 1, ret_val);  
	    msleep(200);
	    timeout++;
	    if (timeout > 10)
		return(-1);
	} while (ret < 0);
	if (ret_val[0] > 0.5)
	  open_valves[i] =  get_col_val(valve_relay_conversion, num_Relay, 0, i);
	else 
	  open_valves[i] = 0;
    }
    qsort(open_valves, num_DiffP_valves, sizeof(int), compare);
    
    if ( (open_valves[0] == 9) && (open_valves[1] == 10) && (open_valves[2] == 11) && (open_valves[3] == 12) )   // All open
	return(99);
    if ((open_valves[0] != 0) || (open_valves[1] != 0))
    {
	fprintf(stderr, "Valve state bad, too many valves open. \n");
	return(-1);
    }
    if ((open_valves[2] == 0) && (open_valves[3] == 0))   // All closed
	return(0);
    if ((open_valves[2] == 9) && (open_valves[3] == 12))  // Bath vs. Evaporator
	return(1);
    if ((open_valves[2] == 10) && (open_valves[3] == 11))   // Bath vs. Condenser
	return(2);
    if ((open_valves[2] == 11) && (open_valves[3] == 12))  // Evaporator vs. Condenser
	return(3);
    if ((open_valves[2] == 9) && (open_valves[3] == 10))  //  Bath vs. Bath
        return(4);
    
    fprintf(stderr, "Valve state bad, wrong valves open. \n");
    return(-1);
}

int set_valve_state(int state)
{
    int ret = 0;
    if (read_valve_state() == state)
	return(0);
    if (state < 0)
	return(1);
    
    ret += close_all_DiffP_valves();    // This covers state 0
    
    if (state == 1)           // Bath vs. Evaporator
    {
	ret += open_valve(9);
	ret += open_valve(12);	
    }
    else if (state == 2)           // Bath vs. Condenser
    {
	ret += open_valve(10);
	ret += open_valve(11);	
    }
    else if (state == 3)           // Evaporator vs. Condenser
    {
	ret += open_valve(11);
	ret += open_valve(12);	
    }	
    else if (state == 4)           // Bath vs. Bath
    {
	ret += open_valve(9);
	ret += open_valve(10);	
    }	
    
    else if (state == 99)
    {
      	ret += open_valve(9);
	ret += open_valve(10);	
	ret += open_valve(11);
	ret += open_valve(12);	
    }
    else
	return(1);
    return(ret);
}


//////////////////////////////////////////////////////////////////////////////////
///  Sensor system required code:

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    int i;
    struct sensor_struct *this_sensor_struc;

    modbus_init_tcp(&inst_dev_6060, i_s->dev_address, mod_port);
    
    if (modbus_connect(&inst_dev_6060) == -1) 
    {
	fprintf(stderr, "ERROR Connection failed\n");
	exit(1);
    }
    
    // loop over sensors and set the database entries to have the correct units
    // for the mode
    for(i=0; i < i_s->num_active_sensors; i++)
    {
	this_sensor_struc = &s_s_a[i];
	if (!(is_null(this_sensor_struc->name)))  
	{
	    if (strncmp(this_sensor_struc->subtype, "valve", 4) == 0)
	    {
	      if (this_sensor_struc->settable)
		{
		  sprintf(this_sensor_struc->units, "discrete");
		  sprintf(this_sensor_struc->discrete_vals, "0:1:2:3:4:99;All Closed:Bath-Evap:Bath-Cond:Evap-Cond:Bath-Bath:All Open");
		  update_sensor_units(this_sensor_struc);
		}
	      else
		{
		  sprintf(this_sensor_struc->units, "discrete");
		  sprintf(this_sensor_struc->discrete_vals, "-1:0:1:2:3:4:99;Error:All Closed:Bath-Evap:Bath-Cond:Evap-Cond:Bath-Bath:All Open");
		  update_sensor_units(this_sensor_struc);
		}
	    }
	}
    }
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    modbus_close(&inst_dev_6060);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{  
    msleep(200);
  
    if (strncmp(s_s->subtype, "valve", 4) == 0)
    {
	*val_out = read_valve_state();
	return(0);
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{       
    close_all_valves();
    return(set_valve_state((int)s_s->new_set_val));
}


#include "main.h"

