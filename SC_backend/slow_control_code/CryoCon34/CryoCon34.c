/* Program for reading a CryoCon34 temp. controller */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "gpib.h"

#define INSTNAME "CryoCon34"

int   inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char  cmd_string[16];   
    
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
    // Reads out the temperature or heater power

    char       cmd_string[32];
    char       ret_string[32];                      
    double     P_range;
    double     P_percent;

    if (strncmp(s_s->subtype, "Temp", 1) == 0)  
    {
	sprintf(cmd_string, "INPUT? %c\n", int_to_letter(s_s->num));
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &ret_string, 10);
	if (sscanf(ret_string, "%lf", val_out) != 1)
	  return(1);
    }
    else if ((strncmp(s_s->subtype, "Power", 1) == 0)  || (strncmp(s_s->subtype, "Heater", 1) == 0) )   
    {
	sprintf(cmd_string, "LOOP %d:RANGE?\n", s_s->num+1);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &ret_string, 10);
	sscanf(ret_string, "%lf%*s", &P_range);
	msleep(400);
	sprintf(cmd_string, "LOOP %d:HTRREAD?\n", s_s->num+1);
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	msleep(400);  
	ibrd(inst_dev, &ret_string, 10);
	sscanf(ret_string, "%lf\%%*s", &P_percent);
	
	*val_out=P_range*P_percent/100.0;	
    }   
    else
    {
	fprintf(stderr, "Wrong type for %s \n", s_s->name);
	return(1);
    }

    return(0);
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    char       cmd_string[64];

    if (strncmp(s_s->subtype, "Temp", 1) == 0)  // Set Temp
      {
	if (s_s->new_set_val < 0 )
	  {
	    return(1);
	  }
	
	sprintf(cmd_string, "LOOP 1:SETPT %f\n", s_s->new_set_val);
	
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	sleep(1);
	
	return(0);
      }
    if (strncmp(s_s->subtype, "Control", 1) == 0)  // Master Control On/Off
      {
	if (s_s->new_set_val < 0.5 )
	  sprintf(cmd_string, "STOP");
	else
	  sprintf(cmd_string, "CONTROL");
	ibwrt(inst_dev, cmd_string, strlen(cmd_string));
	sleep(1);
      }
    else
      {
	fprintf(stderr, "Wrong type for %s \n", s_s->name);
	return(1);
      }
    
}

#include "main.h"
