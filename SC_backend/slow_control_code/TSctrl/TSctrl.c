/* Program for controlling the Thermosyphon system using */
/* ADAM6024 and ADAM6060 modules over TCP/modbus */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009, 2010 */
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
#define INSTNAME "TSctrl"

///  This array converts between the valve number
///  and the relay number and box
///  { valve #, relay #, module # }
///  module 1 is inst_dev_6060a
///  and module 2 is inst_dev_6060b
int valve_relay_conversion[10][3] = {{25,  0, 1},
				     {28,  1, 1},
				     {27,  2, 1},
				     {26,  3, 1},
				     {29,  4, 1},
				     {32,  5, 1},
				     {33,  0, 2},
				     {31,  1, 2},
				     {30,  2, 2},
				     {34,  3, 2}};


///  This array converts between the thermosyphon number
///  and the valve number.
///  { TS #, valve # }
int TS_valve_conversion[5][2] = {{1,  32},
				 {2,  34},
				 {3,  33},
				 {4,  30},
				 {5,  31}};


int fill_status = 0;  // -1 for empty, 1 for fill, 0 for off.

int TS_select = -1;  // TS can be 1 to 5


// These are for the 6024:
const uint16_t ADC_start_address = 0;
const uint16_t num_ADC = 6;

const uint16_t DAC_start_address = 10;
const uint16_t num_DAC = 2;

const uint16_t DI_start_address = 0;
const uint16_t num_DI = 2;

const uint16_t DO_start_address = 16;
const uint16_t num_DO = 2;

// These are for the two 6060s:
const uint16_t Relay_DI_start_address = 0;
const uint16_t num_Relay_DI = 6;

const uint16_t Relay_start_address = 16;
const uint16_t num_Relay = 6;

//////////////   Set up the three physical modules:

modbus_param_t inst_dev_6024;   // the ADC/DAC for pressure and flow
modbus_param_t inst_dev_6060a;  // the relays 0 - 5
modbus_param_t inst_dev_6060b;  // the relays 6 - 11


///////////////////////////////////////////////////
//  Helper fns

int get_int_col_val2(int in_array[][2], int cols, int rows, int want_col, int have_val, int have_col)
{
  int i;

  if ( have_col < 0 || want_col < 0 || have_col >= cols || want_col >= cols )
    return(-1);

  for (i=0; i<rows; i++)
    {
      if (in_array[i][have_col] == have_val)
	return(in_array[i][want_col]);
    }
  return(-1);
}

int get_int_col_val3(int in_array[][3], int cols, int rows, int want_col, int have_val, int have_col)
{
  int i;

  if ( have_col < 0 || want_col < 0 || have_col >= cols || want_col >= cols )
    return(-1);

  for (i=0; i<rows; i++)
    {
      if (in_array[i][have_col] == have_val)
	return(in_array[i][want_col]);
    }
  return(-1);
}


///////////////////////////////////////////////////
//   Hardware specific code

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
	  ret = force_single_coil(&inst_dev_6060a, SLAVE, channel_address, 0);
	  msleep(100);
	  ret = force_single_coil(&inst_dev_6060a, SLAVE, channel_address, 0);
	  msleep(100);
	  timeout++;
	  if (timeout > 10)
	    return(1);
	} while (ret < 0);
      do 
	{
	  ret = force_single_coil(&inst_dev_6060b, SLAVE, channel_address, 0);
	  msleep(100);
	  ret = force_single_coil(&inst_dev_6060b, SLAVE, channel_address, 0);
	  msleep(100);
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
  int      module_num;
 
  relay_num = get_int_col_val3(valve_relay_conversion, 3, 10, 1, valve_num, 0);
  module_num = get_int_col_val3(valve_relay_conversion, 3, 10, 2, valve_num, 0);

  if (relay_num < 0)
    {
      fprintf(stderr, "Bad valve to relay conversion in 'open_valve'. \n");
      return(-1);
    }

  channel_address = Relay_start_address + relay_num;

  do 
    {
      if (module_num == 1)
	ret = force_single_coil(&inst_dev_6060a, SLAVE, channel_address, 1);
      else
	ret = force_single_coil(&inst_dev_6060b, SLAVE, channel_address, 1);
      msleep(200);
      timeout++;
      if (timeout > 5)
	return(1);
    } while (ret < 0);	
  return(0);
}

int fill_TS(void)
{
  int TS_valve;

  close_all_valves();

  if ((TS_select < 1) || (TS_select > 5))
    {
      fprintf(stderr, "ThermoSyphon out of range in TSctrl \n");
      return(1);
    }

  open_valve(25);  // Open valve to N2 bottle
  msleep(100);
  open_valve(25); 
  msleep(100);

  open_valve(28);  // Open valve to TS b
  msleep(100);
  open_valve(28);
  msleep(100);

  TS_valve =  get_int_col_val2(TS_valve_conversion, 2, 5, 1, TS_select, 0);

  open_valve(TS_valve);
  msleep(100);
  open_valve(TS_valve);
  msleep(100);

  return(0);
}

int pump_TS(void)
{
  int TS_valve;
  
  close_all_valves();
     
  if ((TS_select < 1) || (TS_select > 5))
    {
      fprintf(stderr, "ThermoSyphon out of range in TSctrl \n");
      return(1);
    }
    
  open_valve(27);  // Open valve to pump
  msleep(100);
  open_valve(27); 
  msleep(100);

  open_valve(29);  // Open valve to MFC
  msleep(100);
  open_valve(29);
  msleep(100);
  
  TS_valve =  get_int_col_val2(TS_valve_conversion, 2, 5, 1, TS_select, 0);

  open_valve(TS_valve);
  msleep(100);
  open_valve(TS_valve);
  msleep(100);

  return(0);
}

int dump_TS(void)
{
  // This will dump all the thermosyphon charges to the room
  int i;
  int TS_valve;

  close_all_valves();

  open_valve(26);  // Open valve to room
  msleep(100);
  open_valve(26); 
  msleep(100);

  open_valve(28); 
  msleep(100);
  open_valve(28);  
  msleep(100);
  
  ///   remove these two after leak check!
  open_valve(29); 
  msleep(100);
  open_valve(29);  
  msleep(100);

  open_valve(27); 
  msleep(100);
  open_valve(27);  
  msleep(100);

  for (i = 1; i <= 5; i++)
    {
      TS_valve =  get_int_col_val2(TS_valve_conversion, 2, 5, 1, i, 0);
      open_valve(TS_valve);
      msleep(100);
      open_valve(TS_valve);
      msleep(100);
    }
  
  return(0);
}


//////////////////////////////////////////////////////////////////////////////////
///  Sensor system required code:

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  modbus_init_tcp(&inst_dev_6024, i_s->dev_address, mod_port);
    
  if (modbus_connect(&inst_dev_6024) == -1) 
    {
      fprintf(stderr, "ERROR Connection failed\n");
      exit(1);
    }
  modbus_init_tcp(&inst_dev_6060a, i_s->user1, mod_port);
    
  if (modbus_connect(&inst_dev_6060a) == -1) 
    {
      fprintf(stderr, "ERROR Connection failed\n");
      exit(1);
    }
    
  modbus_init_tcp(&inst_dev_6060b, i_s->user2, mod_port);
    
  if (modbus_connect(&inst_dev_6060b) == -1) 
    {
      fprintf(stderr, "ERROR Connection failed\n");
      exit(1);
    }
    
  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  modbus_close(&inst_dev_6024);
  modbus_close(&inst_dev_6060a);
  modbus_close(&inst_dev_6060b);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{  
  msleep(200);
  if ((strncmp(s_s->subtype, "press", 4) == 0) || (strncmp(s_s->subtype, "flow", 4) == 0))
    return(read_ADC_i(s_s, ADC_start_address, num_ADC, val_out, inst_dev_6024));
    
  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      return(1);
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{       
  if (strncmp(s_s->subtype, "master", 6) == 0)      
    {
      // Set system to fill, empty, or off
      if (s_s->new_set_val > 0.5)
	fill_status = 1;
      else if (s_s->new_set_val < -0.5)
	{
	  if (s_s->new_set_val < -1.5)
	    fill_status = -9;
	  else
	    fill_status = -1;
	}
      else
	fill_status = 0;
	
      if (fill_status == 1)
	{
	  fill_TS();
	  return(0);
	}
      else if (fill_status == -1)
	{
	  pump_TS();
	  return(0);
	}

      else if (fill_status == -9)
	{
          dump_TS();
          return(0);
	}
      else
	{
	  close_all_valves();
	  return(0);
	}   
    }

  else if (strncmp(s_s->subtype, "select", 6) == 0)      
    {
      close_all_valves();
      TS_select = (int)(s_s->new_set_val+0.1);
      if ((TS_select < 1) || (TS_select > 5))
	TS_select = -1;
	   
      return(0);
    }

  else if (strncmp(s_s->subtype, "flow", 4) == 0)          
    return(write_DAC_i(s_s, DAC_start_address,  num_DAC, inst_dev_6024));

  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      return(1);
    }
}


#include "main.h"

