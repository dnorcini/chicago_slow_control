/* Program for reading the C-Stic control module at the Apollo LN2 dewar*/
/**********************/
/* Cinyu Zhu, Hopkins, October 2026*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#include "modbus.h"
#include <string.h>

#define INSTNAME "APOLLO"

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
	log_err("ERROR Connection failed\n");
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
    uint16_t channel_address = 0;
    uint16_t ret_val[2];
    uint32_t raw_val;
    float    f_val;
    int      max_tries = 0;
    int      ret;

    if (strncmp(s_s->subtype, "percentage", 10) == 0)
    {
      channel_address += 44; //register 40045-40046

        do
        {
          ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
          msleep(100);
          max_tries++;
          if (max_tries > 10)
            return(1);
        }

        while (ret < 2);
        //little endian = lowest register = least sig bits!
        raw_val = ((uint32_t)ret_val[1] << 16) | ret_val[0];
        memcpy(&f_val, &raw_val, sizeof(f_val));
        *val_out = ((double)f_val-4.0)/16*100; //convert the 4-20mA current to percentage

        // log_out("DEBUG ret_val[0]=0x%04x ret_val[1]=0x%04x raw_val=0x%08x f_val=%f val_out=%f\n",
        // 	ret_val[0], ret_val[1], raw_val, f_val, *val_out);
        return(0);
    }
     
    else
    {
	log_err("Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);
}

#include "main.h"
