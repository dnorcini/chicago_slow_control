/* General helper functions for the ADAM-60XX modules  */


int read_ADC_raw_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out, modbus_param_t inst_dev)
{
    uint16_t channel_address;        
    uint16_t ret_val[1];
    int      max_tries = 0;
    int      ret;

    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 
    channel_address = start_address + s_s->num;
    do 
    {
	ret = read_input_registers(&inst_dev, SLAVE, channel_address, 1, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    }
    while (ret < 1);
    
    *val_out = (double)ret_val[0];
    return(0);
}


int read_ADC_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out, modbus_param_t inst_dev)
{
    read_ADC_raw_i(s_s, start_address, num, val_out, inst_dev);
    *val_out = *val_out * 20./65535. -10.0;
    *val_out = s_s->parm1 +  s_s->parm2 * (*val_out) +  s_s->parm3 * (*val_out)*(*val_out);
    
    if (strncmp(s_s->user1, "bool", 4) == 0) // Do boolean conversion
    {
	if (*val_out < 0.5)
	    *val_out = 0;
	else
	    *val_out = 1;
    }
    
    return(0);
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

int read_DAC_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out, modbus_param_t inst_dev)
{
    uint16_t channel_address;        
    uint16_t ret_val[1];
    int      max_tries = 0;
    int      ret;
    
    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 
    channel_address = start_address + s_s->num;
    do 
    {
	ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 1, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    }
    while (ret < 1);

    *val_out =  ((double)ret_val[0] * 10./4095. );
    *val_out = s_s->parm1 +  s_s->parm2 * (*val_out) +  s_s->parm3 * (*val_out)*(*val_out);
    
    return(0);
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

int read_DI_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out, modbus_param_t inst_dev)
{
    uint16_t channel_address;        
    uint8_t  ret_val[1];
    int      max_tries = 0;
    int      ret;
    
    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 
    channel_address = start_address + s_s->num;
    do 
    {
	ret = read_input_status(&inst_dev, SLAVE, channel_address, 1, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    }
    while (ret < 1);
    
    *val_out = (double)ret_val[0];
    return(0);
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

int read_DO_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, double *val_out, modbus_param_t inst_dev)
{
    uint16_t channel_address;        
    uint8_t  ret_val[1];
    int      max_tries = 0;
    int      ret;
    
    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 
    channel_address = start_address + s_s->num;
    do 
    {
	ret = read_coil_status(&inst_dev, SLAVE, channel_address, 1, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    }
    while (ret < 1);
    
    *val_out = (double)ret_val[0];
    return(0); 
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

int write_DAC_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, modbus_param_t inst_dev)
{ 
    uint16_t channel_address;        
    uint16_t ret_val[1];
    double   set_dbl;
    int      set_val;
    int      max_tries = 0;
    int      ret;
    
    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 

    set_dbl = s_s->parm1 +  s_s->parm2 * s_s->new_set_val + s_s->parm3 * s_s->new_set_val * s_s->new_set_val;

    if ((s_s->parm4 > 0) && (set_dbl > s_s->parm4))
      set_dbl = s_s->parm4;
    
    set_val = (int)(4095.0 * set_dbl / 10.0);  

    channel_address = start_address + s_s->num;

    do 
    {
	ret = preset_single_register(&inst_dev, SLAVE, channel_address, set_val);
	msleep(200);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    } 
    while (ret < 1);

    msleep(400);
    
    do 
    {
	ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 1, ret_val);
	msleep(100);
	max_tries++;
	if (max_tries > 10)
	    return(1);
    } 
    while (ret < 1);
    
    if (abs(ret_val[0] - set_val) > 2)
	return(1);
    
    return(0);   
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

int write_DO_i(struct sensor_struct *s_s, uint16_t start_address, uint16_t num, modbus_param_t inst_dev)
{ 
    uint16_t channel_address;        
    uint8_t  ret_val[1];
    int      set_val;
    int      max_tries = 0;
    int      ret;
    
    if ((s_s->num >= num) || (s_s->num < 0))
    {
	fprintf(stderr, "Wrong value for num (%d != 0-%d) in %s \n", s_s->num, num-1, s_s->name);
	return(1);
    } 
    if (s_s->new_set_val < 0.5)
	set_val = 0;
    else
	set_val = 1;

    s_s->new_set_val = set_val;
 
    channel_address = start_address + s_s->num;
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
