/* Program for reading MBus_WTH_LCD_ETH and MBus_WTH_LCD_ETH_EXT 
modbus-enabled temperature and humidity sensors  */
/* and putting said readings into mysql database */
/**********************/
/* adapted MBus_WTH_LCD_ETH for APC UPS for Modbus/TPC*/
/* D. Venegas-Vargas, JHU, 2025*/
/* Cinyu Zhu, JHU, 2025*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#include "modbus.h"

#define TEMP_REGISTER_C 101  // Temperature in Celsius x10
#define HUMIDITY_REGISTER 373 // Humidity in %RH x10 //this is the read-only mirror

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same as the default. 
#define INSTNAME "DataNab1" // The default instrument name (used for database entries or configuration)

#define SLAVE    1 //0x01 
#define mod_port 502 // The default port 

modbus_param_t inst_dev;

#define _def_set_up_inst
// This initializes and connects to the Modbus device:
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    // modbus_init_tcp: Initializes a Modbus TCP connection with the device address and port.
    modbus_init_tcp(&inst_dev, i_s->dev_address, mod_port);
    
    // modbus_connect: Attempts to connect. If unsuccessful, it exits with an error.
    if (modbus_connect(&inst_dev) == -1) 
    {
      fprintf(stderr, "ERROR Connection failed\n");
      exit(1);
    }
    
    return(0);
}
//----------------------------------------------------------------------//


//----------------------------------------------------------------------//
#define _def_clean_up_inst 
// This function Closes the Modbus connection using modbus_close.
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  modbus_close(&inst_dev);
}
//----------------------------------------------------------------------//

//---------------------------------------------------------------------------------//
#define _def_read_sensor
// This function reads data from the UPS based on the sensor's subtype:
// mbpoll -m tcp -a 1 -r 101 -c 1 -1 192.168.2.15 (Temp *10)
// mbpoll -m tcp -a 1 -r 373 -c 1 -1 192.168.2.15 (RH *10)

int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    uint16_t channel_address;
    uint16_t ret_val[2];
    int      max_tries = 5;
    int      tries = 0;
    int      ret;

    if (strncmp(s_s->subtype, "temperature", strlen("temperature")) == 0) {
        channel_address = TEMP_REGISTER_C;
    } 
    else if (strncmp(s_s->subtype, "humidity", strlen("humidity")) == 0) {
        channel_address = HUMIDITY_REGISTER;
    } 
    else {
        fprintf(stderr, "Unsupported subtype: %s\n", s_s->subtype);
        return 1; // Failure
    }

        while (tries < max_tries) {
        ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 1, ret_val);
        if (ret == 1) {
            *val_out = ret_val[0] / 10.0;
            return 0; // Success
        }
        msleep(100);
        tries++;
    }
    fprintf(stderr, "Failed to read %s after %d attempts.\n", s_s->subtype, max_tries);
    
    return(0);
}

#include "main.h"