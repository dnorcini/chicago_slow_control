/* Program for reading out the SRS residual gas analyzer using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from the database */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "SRS_RGA"

int   inst_dev;

double SP_Parm = 1;     // mA/Torr
double ST_Parm = 1;     // mA/Torr

///////////////////////////////////////////////////////////////////////////////////////////////

int conv_to_current(char *val, size_t n)
{
  union {int iVal; unsigned char bytes[4]; } value;     //  Again with the union magic
  if (n<4)
    {
      fprintf(stderr, "conv_to_current val too small\n");
      return(-1);
    }
  value.bytes[0] = val[0];
  value.bytes[1] = val[1];
  value.bytes[2] = val[2];
  value.bytes[3] = val[3]; 

  return(value.iVal);
} 

int read_ion_val(char *val)
{
  //  returns the current in units of 10^-16 A
  int num_bytes = 4;
  int i = 0;
  int tries = 0;
  int rdstatus;
  do
    {
      // do tcp write/read
      rdstatus = read_tcp(inst_dev, &val[i], 1);
	
      if  (rdstatus == 0)
	i++;
      else
	{
	  tries++;
	  if (tries > 100)
	    return(1);
	  msleep(100);
	}
    } while (i < num_bytes);  
  return(0);
}

int wait_for_status_byte(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char     val[16];
  int      rdstatus; 
  int      status_byte;
  int      sleep_time = 200;        // (ms)
  time_t   start_time = time(NULL);
  time_t   cur_time = time(NULL);
  time_t   max_wait_time = 20*60;   // (s) Wait for 20 minutes

  memset(val, 0, sizeof(val));

  do
    {
      rdstatus = read_tcp(inst_dev, val, sizeof(val)-1);
	
      if (rdstatus == 0)
	{
	  sscanf(val, "%d", &status_byte);
	  return(status_byte);
	}
      else
	{
	  if (time(NULL) - cur_time > INST_TABLE_UPDATE_PERIOD)
	    {
	      mysql_inst_run_status(i_s);
	      cur_time = time(NULL);
	    }
	  msleep(sleep_time);
	  cur_time += sleep_time;
	}
    } while (time(NULL) - start_time < max_wait_time);
  fprintf(stderr, "wait_for_status_byte had to wait too long.  \n");
  return(1);  
}

///////////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  char       cmd_string[1024]; 
  char       ret_string[1024];
  int        status_byte;
  int        status = 0;
  
  global_tcp_timeout = 15;
  
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
    
  msleep(500);
    
  sprintf(cmd_string, "ADMIN%c", CR);  // Send username
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);

  sprintf(cmd_string, "ADMIN%c", CR);  // Send password
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);

  
  sprintf(cmd_string, "%c%c", CR, CR);  // Clear buffer
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);

  sprintf(cmd_string, "IN0%c", CR);   //  Send initilize command
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
  
  sprintf(cmd_string, "ID?%c", CR);    //  Check device ID
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  fprintf(stdout, "ID?--- %d: %s \n", status, ret_string);
  msleep(500);
   
  sprintf(cmd_string, "ER?%c", CR);   // Query status byte
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if(sscanf(ret_string, "%d", &status_byte) != 1)
    {
      fprintf(stderr, "Could not get status byte in set_up_inst.\n");
      return(1);
    }
  if (status_byte > 0)
    fprintf(stderr, "RGA Status Byte:  %d \n", status_byte);
  msleep(500);

  sprintf(cmd_string, "HV0%c", CR);   // Turn off electron multiplier
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
 
  sprintf(cmd_string, "EE70%c", CR);  //  Set electron energy to 70 eV
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);

  sprintf(cmd_string, "IE1%c", CR);   // Set ion energy to high
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
    
  sprintf(cmd_string, "VF90%c", CR);  // Set focus voltage to -100V
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
    
  sprintf(cmd_string, "FL1.0%c", CR); // Set filament current to 1 mA and turn on
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
   
  sprintf(cmd_string, "NF%d%c", (int)i_s->parm1, CR);          //  Set noise floor value from instrument parm1
  status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(1000);
   
  sprintf(cmd_string, "TP1%c", CR);   //  Request that the total pressure be measured.  
  status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(500);
   
  sprintf(cmd_string, "TP?%c", CR);   //  Read the total pressure value
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);

  sprintf(cmd_string, "MR2%c", CR);   //  Turn on RF and DC by requesting a single AMU = 2 (hydrogen) measurement 
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
    
  sprintf(cmd_string, "SP?%c", CR);   // get partial pressure conv. factor
  status += query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%lf", &SP_Parm) != 1)
    {
      fprintf(stderr, "Could not get SP value in set_up_inst.\n");
      return(1);
    }
  msleep(500);

  sprintf(cmd_string, "ST?%c", CR);            // get total pressure conv. factor
  status += query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%lf", &ST_Parm) != 1)
    {
      fprintf(stderr, "Could not get ST value in set_up_inst.\n");
      return(1);
    }
  msleep(500);       // ~10 SECONDS TO STARTUP!

  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
  char       cmd_string[1024];
  char       ret_string[1024];
  int        status = 0;
   
  sprintf(cmd_string, "MR0%c", CR);   //  Turn off RF and DC to quadrapole
  status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
  msleep(500);
    
  sprintf(cmd_string, "HV0%c", CR);   // Turn off electron multiplier
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  msleep(500);
 
  sprintf(cmd_string, "FL0%c", CR);    //  Turn off filament current
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  close(inst_dev);
    
  if (status > 0)
    fprintf(stderr, "Comm errors in clean_up_inst\n");

}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  // Reads out the current value of the device
  // at given sensor.
    
  char       cmd_string[1024];
  char       ret_string[1024];
    
  int        status = 0;
  double     CDEM_gain = 1;   // gain of the electron multiplier
  int        HV;
  int        start_mass = 1;
  int        stop_mass = 200; 
  int        ppAMU = 10;
  int        x0, x1, dxN, num_points;
  int        i;
  char       y_array[20000];
  char       y_temp[1024];
  int        y[10000];

  memset(y, 0, sizeof(y));
  
  
  //sprintf(cmd_string, "%c%c", CR, CR);  // Clear buffer
  //status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  //msleep(500);

  sprintf(cmd_string, "HV?%c", CR);         //  Read CEM voltage
  status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%d", &HV) != 1)
    {
      fprintf(stderr, "Could not read CEM voltage in read_sensor.\n");
      return(1);
    }
  msleep(500);
	  
  if (HV >=10)
    {
      sprintf(cmd_string, "MG?%c", CR);                             // get electron multiplier gain
      status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if (sscanf(ret_string, "%lf", &CDEM_gain) != 0)
	{
	  fprintf(stderr, "Could not get electron multiplier gain in read_sensor.\n");
	  return(1);
	}
      msleep(200);
    }
 
  if (strncmp(s_s->subtype, "single", 4) == 0)                // Single mass measurement
    {
      sprintf(cmd_string, "MR%d%c", s_s->num, CR);
      status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      *val_out = 1e-4/SP_Parm/CDEM_gain*conv_to_current(ret_string, 4);
      return(status);
    }
    
  else if (strncmp(s_s->subtype, "analog", 4) == 0)           // Analogue scan mode
    {
      s_s->data_type = ARRAY_DATA;
      
      start_mass =  closest_int(s_s->parm1);
      stop_mass  =  closest_int(s_s->parm2);
      ppAMU      =  closest_int(s_s->parm3);

      if (start_mass < 1)
	start_mass = 1;
      if  (stop_mass < 2)
	stop_mass = 2;
      if (stop_mass > 200)
	stop_mass = 200;
      if (start_mass > stop_mass)
	start_mass = stop_mass -1;
      
      if (ppAMU < 10)
	ppAMU = 10;
      if (ppAMU > 25)
	ppAMU = 25;

      sprintf(cmd_string, "MI%d%c", start_mass, CR);        // Set start mass
      status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      msleep(100);

      sprintf(cmd_string, "MI?%c", CR);        // Check set of start mass
      status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));	
      if (sscanf(ret_string, "%d", &x0) != 1)
	{
	  fprintf(stderr, "Could not get start mass in read_sensor.\n");
	  return(1);
	}	    
      if (x0 != start_mass)
	{
	  fprintf(stderr, "Could not set MI on %s (1 to MF) (%d != %d) \n ", s_s->name, x0, start_mass);
	  return(1);
	} 
      msleep(100);

      sprintf(cmd_string, "MF%d%c", stop_mass, CR);         // Set stop mass
      status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      msleep(100);
	
      sprintf(cmd_string, "MF?%c", CR);         // Check set of stop mass
      status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if (sscanf(ret_string, "%d", &x1) != 1)
	{
	  fprintf(stderr, "Could not get stop mass in read_sensor.\n");
	  return(1);
	}
      if (x1 != stop_mass)
	{
	  fprintf(stderr, "Could not set MF on %s (MI to 200) (%d != %d) \n ", s_s->name, x1, stop_mass);
	  return(1);
	} 
      msleep(100);

      sprintf(cmd_string, "SA%d%c", ppAMU, CR);        // Set number of points per AMU
      status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      msleep(100);
	
      sprintf(cmd_string, "SA?%c", CR);        // Check number of points per AMU
      status += query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if (sscanf(ret_string, "%d", &dxN) != 1)
	{
	  fprintf(stderr, "Could not get SA in read_sensor.\n");
	  return(1);
	}	
      if (dxN != ppAMU)
	{
	  fprintf(stderr, "Could not set SA on %s (10-25) (%d != %d) \n ", s_s->name, dxN, ppAMU);
	  return(1);
	} 
      msleep(100);

      sprintf(cmd_string, "AP?%c", CR);  // Count size of analog run
      status += query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      if (sscanf(ret_string, "%d", &num_points) != 1)
	{
	  fprintf(stderr, "Could not get AP in read_sensor.\n");
	  return(1);
	}
      msleep(100);

      ////////////////do trigger!
      //fprintf(stdout, "Starting analog run, %d to %d, (N = %d) \n ", x0, x1, num_points);
      sprintf(cmd_string, "SC1%c", CR);
      status += write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      msleep(100);
	
      memset(y_array, 0, sizeof(y_array));
      y_array[0] = 0;

      //  Read out points:
      int num_read = 0;
      for (i = 0; i < num_points-1; i++)
	{
	  memset(ret_string, 0, sizeof(ret_string));
	  if (read_ion_val(ret_string) == 0)
	    {
	      y[i] = conv_to_current(ret_string, sizeof(ret_string));
	      sprintf(y_temp, "%d,", y[i]);	    
	      strcat(y_array, y_temp);
	      num_read++;
	    }
	}
      memset(ret_string, 0, sizeof(ret_string));
      if (read_ion_val(ret_string) == 0)
	{
	  y[i] = conv_to_current(ret_string, sizeof(ret_string));
	  sprintf(y_temp, "%d", y[i]);
	  strcat(y_array, y_temp);
	  num_read++;
	}
      if (num_read < num_points)
	{
	  fprintf(stderr, "Only read out %d points.\n ", num_read);
	  return(1);
	}

      //  Now read total pressure value:
      memset(ret_string, 0, sizeof(ret_string));
      if (read_ion_val(ret_string))
	return(1);
	
      *val_out = 1e-4/ST_Parm/CDEM_gain * conv_to_current(ret_string, sizeof(ret_string));    // convert to pTorr
      status = insert_mysql_sensor_array_data(s_s->name, time(NULL), 
					      *val_out, s_s->rate, 
					      (double)x0, (double)x1, dxN, 1e-4/SP_Parm/CDEM_gain, y_array);
      return(status);
    }

  else if (strncmp(s_s->subtype, "histogram", 4) == 0)  // Histogram scan mode
    {
      // do stuff
      return(1);
    }
  else 
    {
      fprintf(stderr, "No such measurement type: %s \n", s_s->name);
      return(1);
    }

  return(status);    
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[1024];
  char       ret_string[1024];
  int        status;
  int        HV;
   
  if (strncmp(s_s->subtype, "CDEM", 4) == 0)  // Turn on electron multiplier and set to given value
    {
      if (s_s->new_set_val < 10)
	sprintf(cmd_string, "HV0%c", CR);       //  Turn off electron multiplier
      else if (s_s->new_set_val > 2490)
	{
	  fprintf(stderr, "Electron multiplier voltage must be 0, or between 10 and 2490\n");
	  return(1);
	}
      else
	sprintf(cmd_string, "HV%d%c", (int)s_s->new_set_val, CR);         // Set electron multiplier voltage in Volts
     
      status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      msleep(1000);

      sprintf(cmd_string, "HV?%c", CR);         
      status = query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
	
      sscanf(ret_string, "%d", &HV);
      if (abs(HV - (int)s_s->new_set_val) > s_s->new_set_val*0.05)     // make sure it is within 5%
	return(1);
      return(0);
    }
  else  if (strncmp(s_s->subtype, "CALIBRATE", 4) == 0)  // Run calibration routine
    {
      if (s_s->new_set_val == 1)
	{
	  mysql_inst_run_status(i_s);
	  sprintf(cmd_string, "CL%c", CR);         // Calibrate IV response
	  status = write_tcp(inst_dev, cmd_string,  strlen(cmd_string));
	  msleep(1000);
	  if ((status = wait_for_status_byte(i_s, s_s)) != 0)
	    {
	      fprintf(stderr, "CL status bit: %d \n", status);
	      return(1);
	    }
	    
	  mysql_inst_run_status(i_s);
	  msleep(1000);
	  sprintf(cmd_string, "CA%c", CR);         // Calibrate all
	  status = write_tcp(inst_dev, cmd_string, strlen(cmd_string));
	  msleep(1000);
	  if ((status = wait_for_status_byte(i_s, s_s)) != 0)
	    {
	      fprintf(stderr, "CA status bit: %d \n", status);
	      return(1);
	    }
	}
      else if (s_s->new_set_val == 0)
	return(0);
	
      insert_mysql_sensor_data(s_s->name, time(NULL), 0, 0);
      s_s->last_set_time = time(NULL);
      return(0);
    }
  else 
    {
      fprintf(stderr, "No such control type: %s \n", s_s->name);
      return(1);
    }
    
  return(0);
}


#include "main.h"
