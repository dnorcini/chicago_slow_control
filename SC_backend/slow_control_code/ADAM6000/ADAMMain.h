/* Main functions for the ADAM-60XX modules  */


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


#include "ADAM60XX.h"

int read_ADC(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out)
{
    return(read_ADC_i(s_s, start_address, num, val_out, inst_dev));
}

int read_ADC_raw(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out)
{
    return(read_ADC_raw_i(s_s, start_address, num, val_out, inst_dev));
}

int read_DAC(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out)
{
    return(read_DAC_i(s_s, start_address,  num, val_out, inst_dev));
}

int read_DI(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out)
{
    return(read_DI_i(s_s,  start_address, num, val_out, inst_dev));
}

int read_DO(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out)
{
    return(read_DO_i(s_s, start_address, num, val_out, inst_dev));
}

int write_DAC(struct sensor_struct *s_s, uint16_t start_address, uint16_t num)
{
    return(write_DAC_i(s_s, start_address, num, inst_dev));
}

int write_DO(struct sensor_struct *s_s, uint16_t start_address, uint16_t num)
{
    return(write_DO_i(s_s, start_address, num, inst_dev));
}
