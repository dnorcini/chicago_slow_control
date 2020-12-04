/* Program for reading Raritan PX3-5145R PDU */
/* and putting said readings into mysql database */
/**********************/
/* D. Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "Raritan_PX3"

// These are modbus parameters:
#define SLAVE    0xFF
#define mod_port 5502

modbus_param_t inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    modbus_init_tcp(&inst_dev, i_s->dev_address, mod_port);
    
    if (modbus_connect(&inst_dev) == -1) 
    {
	fprintf(stderr, "ERROR Connection failed\n");
	exit(1);
    }
    
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    modbus_close(&inst_dev);
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    uint16_t channel_address;
    uint16_t ret_val[2];
    uint16_t mode[1];
    int      max_tries = 0;
    int      ret;

    //use RELATIVE address (modcon convention: holding register 40001 = 0)
    if (strncmp(s_s->subtype, "current", 7) == 0)
    {
      channel_address += 12298;
      do
        {
          ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
          msleep(100);
          max_tries++;
          if (max_tries > 10)
            return(1);
        }
        while (ret < 2);

        // use a union to convert from the two 16 bit bytes returned to a float
	union { float fVal; uint16_t bytes[2]; } value;

	//big endian = lowest register = most sig bits (so not sure why need to swap..)
	value.bytes[0] = ret_val[1]; 
        value.bytes[1] = ret_val[0];
	
        *val_out = value.fVal; //amps


        return(0);
    }

    else if (strncmp(s_s->subtype, "power", 5) == 0)
    { 
      channel_address += 12306; 
        do
        {
          ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
          msleep(100);
          max_tries++;
          if (max_tries > 10)
            return(1);
        }
        while (ret < 2);

        // use a union to convert from the two 16 bit bytes returned to a float                     
        union { float fVal; uint16_t bytes[2]; } value;

	//big endian = lowest register = most sig bits (so not sure why need to swap..)
        value.bytes[0] = ret_val[1]; 
        value.bytes[1] = ret_val[0];

        *val_out = value.fVal; //watts                                                               
	
        return(0);
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{ 
    uint16_t channel_address;        
    uint8_t  ret_val[1];
    int      set_val;
    int      max_tries = 0;
    int      ret;
 
    if (strncmp(s_s->subtype, "outlet", 6) == 0)
      {
	uint16_t start_address = 256;
	
	if ((s_s->num > 8) || (s_s->num < 1))
	  {
	    fprintf(stderr, "Wrong value for num (%d != 1-%d) in %s \n", s_s->num, 8, s_s->name);
	    return(1);
	  }
	
	if (s_s->new_set_val < 0.5) {
	  set_val = 0;
	}

	else if (s_s->new_set_val > 0.5) {
	  set_val = 1;
	}
	
	s_s->new_set_val = set_val;
	
	channel_address = start_address + (s_s->num-1); //if looping through all outlets

	do 
	  {
	    ret = force_single_coil(&inst_dev, SLAVE, channel_address, set_val);
	    msleep(200);
	    max_tries++;
	    if (max_tries > 10)
	      return(1);
	  }

	
	while (ret < 1);
	
	msleep(400);

	do 
	  {
	    ret = read_coil_status(&inst_dev, SLAVE, channel_address, 1, ret_val);
	    msleep(200);
	    max_tries++;
	    if (max_tries > 10)
	      return(1);
	  }

	while (ret < 1); 
	
	if (ret_val[0] != set_val)
	  return(1);
	
	return(0);
	}
}

#include "main.h"
