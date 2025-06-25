/* Program for reading and controlling a Spellman 130 high voltage power supply */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2010 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "SL130"

int   inst_dev;

/// Define Command arguments
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

#define TEMP_MULT 0.0732         //  Celsius
#define HV_MULT 31.746031746     //  V
#define CUR_MULT 0.061500615       //  uA

struct sensor_struct *V_CTRL_S_S;


//////////////////////////////////////////////////////////
///  Hardware dependent code:

double read_temp(void)
{
    char   cmd_string[64];
    char   ret_string[64];       
    double Temp;
    int    Temp_int;

    sprintf(cmd_string, "%c20,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    sscanf(ret_string, "%*c20,%d,", &Temp_int);

    //fprintf(stdout,  "T:  %s  --- %d \n", ret_string, Temp_int);

    Temp = (double) (TEMP_MULT * Temp_int);

    return(Temp);    // Reads in C
}

double read_voltage(void)
{
    char   cmd_string[64];
    char   ret_string[64];       
    double V_out;
    int V_int;

    sprintf(cmd_string, "%c20,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    sscanf(ret_string, "%*c20,%*d,%*d,%d,", &V_int);
    
    //fprintf(stdout,  "V:  %s --- %d \n", ret_string, V_int);
    
    V_out = (double) (HV_MULT * V_int);

    return(V_out);     // Reads in V
}

double read_current(void)
{
    char   cmd_string[64];
    char   ret_string[64];       
    double I_out;
    int    I_int;
    
    sprintf(cmd_string, "%c20,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    sscanf(ret_string, "%*c20,%*d,%*d,%*d,%d,", &I_int);

    //fprintf(stdout,  "I:  %s  --- %d \n", ret_string, I_int);

    I_out = (double) (CUR_MULT * I_int);

    return(I_out);      // reads in uA
}


double read_set_voltage(void)
{
    char   cmd_string[64];
    char   ret_string[64];       
    double V_out;
    int V_int;

    sprintf(cmd_string, "%c14,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    sscanf(ret_string, "%*c14,%d,%*c", &V_int);

    //fprintf(stdout,  "V set:  %s  --- %d \n", ret_string, V_int);

    V_out = (double) (HV_MULT * V_int);
    
    return(V_out);  // Reads in V
}


int set_voltage(double V)
{
    char   cmd_string[64];
    char   ret_string[64];     
    int    V_int;
    
    V_int = (int)(V / HV_MULT);
    
    sprintf(cmd_string, "%c10,%d,%c", STX, V_int, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    
    //fprintf(stdout,  "Set V:  %s \n", cmd_string);
    //fprintf(stdout,  "Ret from Set V:  %s \n", ret_string);

    msleep(1000);
    return(0);
}

double read_set_current(void)
{
    char   cmd_string[64];
    char   ret_string[64];       
    double I_out;
    int I_int;

    sprintf(cmd_string, "%c15,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    sscanf(ret_string, "%*c15,%d,%*c", &I_int);

    //fprintf(stdout,  "I set:  %s  --- %d \n", ret_string, I_int);

    I_out = (double) (CUR_MULT * I_int);
    
    return(I_out);  // Reads in uA
}


int set_current(double I)
{
    char   cmd_string[64];
    char   ret_string[64];     
    int    I_int;
    
    I_int = (int)(I / CUR_MULT);
    
    sprintf(cmd_string, "%c11,%d,%c", STX, I_int, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));

    //fprintf(stdout,  "Set I:  %s \n", cmd_string);
    //fprintf(stdout,  "Ret from Set I:  %s \n", ret_string);

    msleep(1000);
    return(0);
}


int raw_read_status(void)
{
    char   cmd_string[64];
    char   ret_string[64];       

    sprintf(cmd_string, "%c22,%c", STX, ETX);

    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));    
    msleep(200);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
    msleep(200);
    
    //fprintf(stdout,  "Status:  %s \n", ret_string);

    if ( sscanf(ret_string, "%*c22,%d,%d,%d,%*c", &HV_sts, &Intlck_sts, &Flt_sts) != 3 )
	return(1);
    return(0);
}


int clear_faults(void)
{
    char   cmd_string[16];
    char   ret_string[16];     
    sprintf(cmd_string, "%c31,%c", STX, ETX);
    msleep(1000);
    return(0);
}

int hvoff(void)
{
    char   cmd_string[64];
    char   ret_string[64];     
    int    i;
    int    max_tries = 10;
    
    for (i = 0; i < max_tries; i++)
    {
	sprintf(cmd_string, "%c99,0,%c", STX, ETX);
	query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
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
    char   cmd_string[64];
    char   ret_string[64];     
    int    i;
    int    max_tries = 10;
 
    for (i = 0; i < max_tries; i++)
    {
	sprintf(cmd_string, "%c99,1,%c", STX, ETX);
	query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));
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
    char  ret_string[16];   
    int   i;
    
    if ((inst_dev = connect_tcp(i_s)) < 0)
    {
	fprintf(stderr, "Connect failed. \n");
	my_signal = SIGTERM;
	return(1);
    }
    
    msleep(500);
    sprintf(cmd_string, "%c85,1,%c", STX, ETX);
    query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string));

    if (find_v_ctrl(i_s, s_s_a) == 1)
    {
	fprintf(stderr, "Could not find Voltage control Handle for $s\n", i_s->name);
	return(1);
    }
    
    read_status(i_s);
    msleep(500);
    read_status(i_s);
    msleep(500);
    /*
    for(i=0; i < i_s->num_active_sensors; i++)
    {
	this_sensor_struc = &s_s_a[i];
	if (!(is_null(this_sensor_struc->name)))  
	    if (this_sensor_struc->settable)    
		if (strncmp(this_sensor_struc->subtype, "Voltage", 3) == 0)
		    insert_mysql_sensor_data(this_sensor_struc->name, 
					     time(NULL), 0.0, 0);
		else if (strncmp(this_sensor_struc->subtype, "Master", 3) == 0)
		    insert_mysql_sensor_data(this_sensor_struc->name, time(NULL), 0, 0);
    }
    */

    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    shutdown(inst_dev, SHUT_RDWR);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // Reads out the voltage or current
  
    if (read_status(i_s) == 1)
	return(1);
    msleep(500);
    if (strncmp(s_s->subtype, "Voltage", 3) == 0) // returns voltage in volts
    {
	*val_out = read_voltage();
    }
    else if (strncmp(s_s->subtype, "Current", 3) == 0)  // returns current in uA
    {
	*val_out = read_current();   
    }    
    else if (strncmp(s_s->subtype, "Temp", 3) == 0) // returns temperature in C
    {
	*val_out = read_temp();
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
    if (strncmp(s_s->subtype, "Master", 3) == 0)  // Master  On/Off
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
    else if (strncmp(s_s->subtype, "Clear", 3) == 0)     // Clear All Faults
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
    else if (strncmp(s_s->subtype, "Voltage", 3) == 0) // in V
    {
	if (ramp_voltage(s_s->new_set_val) == 0)       
	    return(0);
    }
    else if (strncmp(s_s->subtype, "Current", 3) == 0)  // in uA
    {
	if (set_current(s_s->new_set_val) == 0)   
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
