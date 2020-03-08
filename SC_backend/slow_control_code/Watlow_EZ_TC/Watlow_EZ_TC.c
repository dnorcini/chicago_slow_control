/* Program for reading the a Watlow temperature controller over a TCP/modbus */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2010, 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "Watlow_EZ_TC"

// These are modbus parameters:
#define SLAVE    0xFF
#define mod_port 502

modbus_param_t inst_dev;

uint16_t conv_zone_to_offset(double zone)
{
    uint16_t offset = 0;
    if (zone > 1.5)
	offset = 5000;
    if (zone > 2.5)
	offset = 10000;
    if (zone > 3.5)
	offset = 15000;
    if (zone > 4.5)
	offset = 20000;
    return(offset);
}

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    struct sensor_struct *this_sensor_struc;
    int i;
    
    modbus_init_tcp(&inst_dev, i_s->dev_address, mod_port);
    
    if (modbus_connect(&inst_dev) == -1) 
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
	    if (strncmp(this_sensor_struc->subtype, "power", 3) == 0)
	    {
		sprintf(this_sensor_struc->units, "%%");
		sprintf(this_sensor_struc->discrete_vals, " ");
		update_sensor_units(this_sensor_struc);
	    }
	    else if  (strncmp(this_sensor_struc->subtype, "mode", 4) == 0)
	    {
		sprintf(this_sensor_struc->units, "discrete");
		sprintf(this_sensor_struc->discrete_vals, "0:1:2;Off:Manual:Auto");
		update_sensor_units(this_sensor_struc);	
	    }
	}
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
    
    if ((s_s->num < 1) || (s_s->num > 2))
    {
	fprintf(stderr, "Channel num can be 1 or 2 for sensor: %s \n", s_s->name);
	return(1);
    }
    
    channel_address = conv_zone_to_offset(s_s->parm1);
    
    if (strncmp(s_s->subtype, "temp", 3) == 0)
    {
	if (s_s->num == 1)
	    channel_address += 360;
	else 
	    channel_address += 440;
    }
    else if (strncmp(s_s->subtype, "setpoint", 3) == 0)
    {
	if (s_s->num == 1)
	    channel_address += 2172;
	else 
	    channel_address += 2252;
    }
    else if (strncmp(s_s->subtype, "power", 3) == 0)
    {
	if (s_s->num == 1)
	    channel_address += 1904;
	else 
	    channel_address += 1974;
    }
    else if (strncmp(s_s->subtype, "mode", 4) == 0)   // this is the only uint read
    {
	if (s_s->num == 1)
	    channel_address += 1882;
	else 
	    channel_address += 1952;
	
	do 
	{
	    ret = read_input_registers(&inst_dev, SLAVE, channel_address, 1, mode);
	    msleep(100);
	    max_tries++;
	    if (max_tries > 10)
		return(1);
	} 
	while (ret < 1);
	
	if (mode[0] == 62)       // off
	    *val_out = 0;
	else if (mode[0] == 54)  // manual (open loop)
	     *val_out = 1;
	else if (mode[0] == 10)  // auto (closed loop)
	     *val_out = 2;
	else
	{
	    fprintf(stderr, "Returned unknown mode for %s \n", s_s->name);
	    return(1);
	} 
	return(0);
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }


    ////////////////////  do float read:
    do 
    {
	ret = read_input_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
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
    

    if (strncmp(s_s->units, "K", 1) == 0)  // convert to Kelvin if requested 
	*val_out += 273.15;

    return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{   
    uint16_t channel_address;
    uint16_t set_val[2];
    uint16_t ret_val[2];
    uint16_t set_mode[1];
    uint16_t ret_mode[1];
    double   new_set;
    int      ret;
    int      max_tries = 0;

    if ((s_s->num < 1) || (s_s->num > 2))
    {
	fprintf(stderr, "Channel num can be 1 or 2 for sensor: %s \n", s_s->name);
	return(1);
    }

    channel_address = conv_zone_to_offset(s_s->parm1);
    new_set = s_s->new_set_val;

    if (strncmp(s_s->subtype, "mode", 4) == 0)           // set mode
    {
	if (s_s->num == 1)
	    channel_address += 1880;
	else 
	    channel_address += 1950;

	if (new_set == 0)       // off
	    set_mode[0] = 62;
	else if (new_set == 1)  // manual (open loop)
	    set_mode[0] = 54;
	else if (new_set == 2)  // auto (closed loop)
	    set_mode[0] = 10;
	else
	{
	    fprintf(stderr, "Unknown mode set for %s \n", s_s->name);
	    return(1);
	} 
	do 
	{
	    ret = preset_multiple_registers(&inst_dev, SLAVE, channel_address, 1, set_mode);
	    msleep(100);
	    max_tries++;
	    if (max_tries > 10)
		return(1);
	} 
	while (ret < 1);
	do 
	{
	    ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 1, ret_mode);
	    msleep(100);
	    max_tries++;
	    if (max_tries > 10)
		return(1);
	} 
	while (ret < 1);
	
	if (abs(ret_mode[0] - set_mode[0]) > 2)
	    return(1);
	return(0);
    }
    if (strncmp(s_s->subtype, "setpoint", 3) == 0)     // closed loop setpoint (C)
    {
	if (s_s->num == 1)
	    channel_address += 2160;
	else 
	    channel_address += 2240;
	
	if (strncmp(s_s->units, "K", 1) == 0)    // convert back to C if necessary
	    new_set -= 273.15;
    }
    else if (strncmp(s_s->subtype, "power", 3) == 0)      // open loop setpoint (% power)
    {
	if (s_s->num == 1)
	    channel_address += 2162;
	else 
	    channel_address += 2242;
    }
    else if (strncmp(s_s->subtype, "max_pow", 7) == 0)      //  Set the maximum output power (%)
      {
	if (s_s->num == 1)
	    channel_address += 898;
	else 
	    channel_address += 928;
      }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    
    // use a union to convert from the given float to two 16 bit bytes
    union { float fVal; uint16_t bytes[2]; } value; 
    value.fVal = new_set;
    set_val[0] = value.bytes[0];
    set_val[1] = value.bytes[1];
    
    do 
    {
	ret = preset_multiple_registers(&inst_dev, SLAVE, channel_address, 2, set_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    } 
    while (ret < 2);
    do 
    {
	ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    } 
    while (ret < 2);
    
    if ((abs(set_val[0] - ret_val[0]) > 2) || (abs(set_val[1] - ret_val[1]) > 2))
	return(1);
    return(0);
}


#include "main.h"

