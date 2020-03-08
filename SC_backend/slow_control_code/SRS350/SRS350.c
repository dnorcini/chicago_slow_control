/* Program for reading and controlling an SRS 350/365  high voltage power supply */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2010 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "gpib.h"

#define INSTNAME "SRS350"

int   inst_dev;

/// Define Status Bits:
#define STABLE 0
#define V_TRIP 1
#define I_TRIP 2
#define I_LIM  3
#define MAV    4
#define ESB    5
#define SRQ    6
#define HVON   7 

int  HV_sts = 0;       // 1:HV on,           0:HV off
int  Intlck_sts = 0;   // 1:Interlock open,  0:Interlock closes 
int  Flt_sts = 0;      // 1:Some Fault,      0:No Fault 
int  has_tripped = 0;
int  has_error = 0;
struct sensor_struct *V_CTRL_S_S;


//////////////////////////////////////////////////////////
///  Hardware dependent code:

double read_voltage(void)
{
    char   cmd_string[16];
    char   ret_string[16];       
    double V_out;
    sprintf(cmd_string, "VOUT?");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));  
    msleep(200);	
    ibrd(inst_dev, &ret_string, sizeof(ret_string)/sizeof(char));
    msleep(200);
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));   
    msleep(200);	
    ibrd(inst_dev, &ret_string,  sizeof(ret_string)/sizeof(char));
    sscanf(ret_string, "%le", &V_out);	
    return(V_out);
}

double read_current(void)
{
    char   cmd_string[16];
    char   ret_string[16];       
    double I_out;
    sprintf(cmd_string, "IOUT?");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));  
    msleep(200);	
    ibrd(inst_dev, &ret_string, sizeof(ret_string)/sizeof(char));
    msleep(200);
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));   
    msleep(200);	
    ibrd(inst_dev, &ret_string,  sizeof(ret_string)/sizeof(char));
    sscanf(ret_string, "%le", &I_out);	
    return(I_out);
}


double read_set_voltage(void)
{
    char   cmd_string[16];
    char   ret_string[16];       
    double V_out;
    sprintf(cmd_string, "VSET?");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));  
    msleep(200);	
    ibrd(inst_dev, &ret_string, sizeof(ret_string)/sizeof(char));
    msleep(200);
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));   
    msleep(200);	
    ibrd(inst_dev, &ret_string,  sizeof(ret_string)/sizeof(char));
    sscanf(ret_string, "%le", &V_out);	
    return(V_out);
}


int set_voltage(double V)
{
    char   cmd_string[16];
    sprintf(cmd_string, "VSET %d", (int)V);
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));  
    msleep(1000);
    return(0);
}


int raw_read_status(void)
{
    char   cmd_string[16];
    char   ret_string[16];       
    int    status_byte = -1;
    int    status_bits[8];
    struct sys_message_struct sys_message_struc;

    sprintf(cmd_string, "*STB?");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string)); 
    msleep(100);	
    ibrd(inst_dev, &ret_string,  sizeof(ret_string)/sizeof(char));	
  
    if (sscanf(ret_string, "%d", &status_byte) != 1)
	return(1);
  
    if (STB_to_Array(status_byte, status_bits))
	return(1);
  
    //fprintf(stdout, "status byte: %d\n", status_byte);
    //fprintf(stdout, "status bit 0, 1,2,7: %d, %d, %d, %d \n", 
    //	    status_bits[0], status_bits[1], status_bits[2], status_bits[7]);

    HV_sts = status_bits[7];
    Flt_sts = (status_bits[1] || status_bits[2]); 
  
    return(0);
}


int clear_faults(void)
{
    char   cmd_string[16];
    sprintf(cmd_string, "TCLR");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    msleep(1000);
    return(0);
}

int hvoff(void)
{
    char   cmd_string[16];
    int    i;
    int    max_tries = 10;
    
    for (i = 0; i < max_tries; i++)
    {
	sprintf(cmd_string, "HVOF");
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(1000);
	raw_read_status();
	if (HV_sts == 0)
	    return(0);
	msleep(200);
    }
    return(1);
}

int hvon(void)
{
    char   cmd_string[16];
    int    i;
    int    max_tries = 10;
 
    for (i = 0; i < max_tries; i++)
    {
	sprintf(cmd_string, "HVON");
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(1000);
	raw_read_status();
	if (HV_sts == 1)
	    return(0);
	msleep(200);
    }
    return(1);
}

///////////////////////////////////////////////////////////////////////////
///  Interface independent code:

int ramp_voltage(double V)
{
    double V_out;
    double dV;
    int    i = 0;
    int    n = 10;  // number of steps to ramp by.
  
    V_out = read_set_voltage();
    dV = (V - V_out);
    if (fabs(dV) < 100)
	return(set_voltage(V));
      
    dV /= n;
    do
    {
	i++;
	if (set_voltage(V_out+dV*i))
	    return(1);
	msleep(1000);
    } while (i<n);
    return(0);
}


int zero_v_ctrl(void)
{
    if (set_voltage(0))
	return(1);
    V_CTRL_S_S->last_set_time = V_CTRL_S_S->new_set_time = time(NULL);
    insert_mysql_sensor_data(V_CTRL_S_S->name, time(NULL), 0, 0);
    return(0);
}

int find_v_ctrl(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    int i;
    int not_found = 1;
    struct sensor_struct *this_sensor_struc;
  
    for(i=0; i < i_s->num_active_sensors; i++)
    {
	this_sensor_struc = &s_s_a[i];
	if (!(is_null(this_sensor_struc->name)))  
	    if (this_sensor_struc->settable)    
		if (strncmp(this_sensor_struc->subtype, "Voltage", 1) == 0)
		{
		    V_CTRL_S_S = &s_s_a[i];
		    not_found = 0;
		}
    }
    return(not_found);
}

int read_status(struct inst_struct *i_s)
{       
    int    status_byte;
    int    status_bits[8];
    struct sys_message_struct sys_message_struc;
  
    if (raw_read_status())
	return(1);
  
    if ((Flt_sts == 1 || Intlck_sts == 1 ) && (has_error == 0))
    {
	has_error = 1;
	sprintf(sys_message_struc.ip_address, "");
	sprintf(sys_message_struc.subsys, i_s->subsys);
	if (Flt_sts)
	    sprintf(sys_message_struc.msgs, "HV Supply: %s (%s) has tripped.", 
		    i_s->description, i_s->name);
	else if (Intlck_sts)
	    sprintf(sys_message_struc.msgs, "HV Supply: %s (%s) has open interlock.", 
		    i_s->description, i_s->name);
	sprintf(sys_message_struc.type, "Alarm");
	sys_message_struc.is_error = 1;
	insert_mysql_system_message(&sys_message_struc);
	zero_v_ctrl();
	hvoff();
    }
    return(0);
}


//////////////////////////////////////////////////////////////////////////////////
///  Sensor system required code:

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    struct sensor_struct *this_sensor_struc;
    char  cmd_string[16];   
    int   i;
    
    int ib_board_index = 0;
    int ib_pad = 0;
    int ib_sad = 0;
    int ib_timeout = T3s;
    int ib_send_eoi = 1;
    int ib_eos = 0xa;

    sscanf(i_s->dev_address, "%d", &ib_pad);
    //inst_dev = ibfind(i_s->dev_address); 
    inst_dev = ibdev(ib_board_index, ib_pad, ib_sad, ib_timeout, ib_send_eoi, ib_eos);
    ibclr(inst_dev);

    sprintf(cmd_string, "*CLS");
    ibwrt(inst_dev, cmd_string, strlen(cmd_string));
    
    msleep(500);

    if (find_v_ctrl(i_s, s_s_a) == 1)
    {
	fprintf(stderr, "Could not find Voltage control Handle for $s\n", i_s->name);
	return(1);
    }
    
    read_status(i_s);

    for(i=0; i < i_s->num_active_sensors; i++)
    {
	this_sensor_struc = &s_s_a[i];
	if (!(is_null(this_sensor_struc->name)))  
	    if (this_sensor_struc->settable)    
		if (strncmp(this_sensor_struc->subtype, "Voltage", 1) == 0)
		    insert_mysql_sensor_data(this_sensor_struc->name, 
					     time(NULL), read_set_voltage(), 0);
		else if (strncmp(this_sensor_struc->subtype, "Master", 1) == 0)
		    insert_mysql_sensor_data(this_sensor_struc->name, time(NULL), HV_sts, 0);
    }

    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    ibclr(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // Reads out the voltage or current
  
    if (read_status(i_s) == 1)
	return(1);
    msleep(500);
    if (strncmp(s_s->subtype, "Voltage", 1) == 0) 
    {
	*val_out = read_voltage();
    }
    else if (strncmp(s_s->subtype, "Current", 1) == 0)  
    {
	*val_out = read_current();   // returns current in A
	*val_out*=1e6;               // convert to uA
    }    
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{ 
    if (strncmp(s_s->subtype, "Master", 1) == 0)  // Master  On/Off
    {
	if (read_status(i_s) == 1)
	    return(1);
	msleep(200);
	if ((s_s->new_set_val < 0.5 ) && (HV_sts == 1))  // turn off
	{
	    if (ramp_voltage(0) == 0)
		if (zero_v_ctrl() == 0)
		    if(hvoff() == 0)
			return(0);
	    return(1);
	}
	else if  ((HV_sts == 0) && (has_error == 0))       // turn on
	{
	    if (zero_v_ctrl() == 0)
		if (hvon() == 0)
		    return(0);
	    return(1);
	}
	return(0);
    }
    else if (strncmp(s_s->subtype, "Clear", 1) == 0)     // Clear All Faults
    {
	if (has_error == 0)
	    return(0);
	if (zero_v_ctrl() == 0)
	    if (clear_faults() == 0)
	    {
		msleep(1000);
		if (read_status(i_s) == 0)
		{
		    has_error = 0;
		    return(0);
		}
	    }
	return(1);
    }
    else if (strncmp(s_s->subtype, "Voltage", 1) == 0) 
    {
	if (ramp_voltage(s_s->new_set_val) == 0)
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
