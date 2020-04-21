/* Program for reading APC Smart UPS-SRT-3000VA */
/* and putting said readings into mysql database */
/**********************/
/* adapted ADAM6000 for APC UPS for Modbus/TPC*/
/* D. Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "APC3000"

// These are modbus parameters:
#define SLAVE    0xFF
#define mod_port 502

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
        
    if (strncmp(s_s->subtype, "battcharge", 10) == 0)
    {
      channel_address += 130;

	do 
	{
	  ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 1, ret_val);
	  msleep(100);
	  max_tries++;
	  if (max_tries > 10)
	    return(1);
	} 
	while (ret < 1);
	*val_out = ret_val[0]/512;
 
	return(0);
    }

    else if (strncmp(s_s->subtype, "powerout", 8) == 0)
    {
      channel_address += 145;

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
	value.bytes[0] = ret_val[0];
	value.bytes[1] = ret_val[1];    
	*val_out = value.fVal;
    
	return(0);
    }
     
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }

    return(0);
}

#include "main.h"
