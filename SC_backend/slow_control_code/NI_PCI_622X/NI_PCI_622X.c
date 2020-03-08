/* Program for reading out the NI_PCI_622X ADC card using */
/* the comedi driver (http://www.comedi.org)              */
/* and putting said readings into a mysql database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007, 2008, 2009 */
/* James public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include <comedilib.h>

#define INSTNAME "NI_PCI_622X"

comedi_t *com_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    int i;
    int subdev; 
    struct sensor_struct *s_s;
    
    if ((com_dev = comedi_open(i_s->dev_address)) == NULL)
    {
	comedi_perror(i_s->dev_address);
	my_signal = SIGTERM;
	return(1);
    }
    
    subdev = comedi_find_subdevice_by_type(com_dev, COMEDI_SUBD_DIO, 0);
    for (i = 0; i < i_s->num_active_sensors; i++)
    {
	s_s = &s_s_a[i];
	if ((strncmp(s_s->subtype, "D", 1) == 0) && (s_s->settable))
	    comedi_dio_config(com_dev, subdev, s_s->num, COMEDI_OUTPUT);
    }
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    comedi_close(com_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    // Reads out the current value of the device
    // at given sensor.

    char       cmd_string[32];
    char       val[16];                      

    int subdev;         
    int chan = s_s->num;     
    int range = 0;
    comedi_range *cr;         
    int aref = 0;         
    
    lsampl_t data;
    int maxdata, rangetype;
    unsigned int dio_bit;

    if (strncmp(s_s->subtype, "A", 1) == 0)  // Analogue in
    {
	subdev = comedi_find_subdevice_by_type(com_dev, COMEDI_SUBD_AI, 0);	
	maxdata=comedi_get_maxdata(com_dev, subdev, chan);
	cr = comedi_get_range(com_dev, subdev, chan, range);
	comedi_data_read(com_dev, subdev, chan, range, aref, &data);

	*val_out = comedi_to_phys(data, cr, maxdata);
	*val_out = s_s->parm1 +  s_s->parm2 * (*val_out) +  s_s->parm3 * (*val_out)*(*val_out);
	//*val_out = linear_interp(*val_out, s_s->parm1, s_s->parm2);
    }
    else if (strncmp(s_s->subtype, "D", 1) == 0) // Digital IO
    {
	subdev = comedi_find_subdevice_by_type(com_dev, COMEDI_SUBD_DIO, 0);	
	comedi_dio_read(com_dev, subdev, chan, &dio_bit);
	*val_out = (double)dio_bit;
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return(1);
    }
    msleep(100);
    return(0);    
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    int subdev;
    int chan=s_s->num;
    int aref=0;
    int range=0;
    comedi_range *cr;
    int maxdata;
    lsampl_t val_out;
    double set_value;
    
    if (strncmp(s_s->subtype, "A", 1) == 0)  // Analogue out
    {
	subdev = comedi_find_subdevice_by_type(com_dev, COMEDI_SUBD_AO, 0);
	maxdata = comedi_get_maxdata(com_dev, subdev, chan);
	cr = comedi_get_range(com_dev, subdev, chan, range);
	set_value = s_s->parm1 +  s_s->parm2 * s_s->new_set_val + s_s->parm3 * s_s->new_set_val * s_s->new_set_val;
	//set_value = linear_interp(s_s->new_set_val, s_s->parm1, s_s->parm2);
	val_out = comedi_from_phys(set_value, cr, maxdata);
	comedi_data_write(com_dev, subdev, chan, range, aref, val_out);
    }
    else if (strncmp(s_s->subtype, "D", 1) == 0)  // Digital out
    {
	subdev=comedi_find_subdevice_by_type(com_dev, COMEDI_SUBD_DIO, 0);
	if (s_s->new_set_val > 0.5)
	    comedi_dio_write(com_dev, subdev, chan ,1);
	else
	    comedi_dio_write(com_dev, subdev, chan ,0);
    }
    else 
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
	return (1);
    }
    return(0);
}

#include "main.h"
