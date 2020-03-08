/* Program for reading a CryoCon24C temp. controller */
/* using ethernet */
/* and putting said readings in to a mysql database. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2012 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "CryoCon24C"

int inst_dev;


#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  // returns the temp (which_sens_type == 1)
  // or the heater power (which_sens_type == 2)

  char       cmd_string[64];
  char       ret_string[64];                      
  double     P_range;
  double     P_percent;


  if (strncmp(s_s->subtype, "Temp", 1) == 0)  // Read out value for temperature from A,B,C or D.
    {
      sprintf(cmd_string, "INPUT? %c\n", int_to_Letter(s_s->num));
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}	
    }

  else if (strncmp(s_s->subtype, "Heater", 1) == 0) // Read out Pwr and range of heater
    {
      sprintf(cmd_string, "LOOP %d:RANGE?\n", s_s->num);   // Loop is 1-4
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if (strncmp(ret_string, "LOW", 3) == 0)
	P_range = 0.5;
      else if (strncmp(ret_string, "MID", 3) == 0)
	P_range = 5.0;
      else if (strncmp(ret_string, "HI", 2) == 0)
	P_range = 50.0;
      else if (strncmp(ret_string, "5V", 2) == 0)
	P_range = 5.0;
      else if (strncmp(ret_string, "10V", 3) == 0)
	P_range = 10.0;
      else
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read range!\n", ret_string);
	  return(1);
	}
	
      msleep(400);
	
      sprintf(cmd_string, "LOOP %d:HTRREAD?\n", s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf%%", &P_percent) !=1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in HTRREAD!\n", ret_string);
	  return(1);
	}

      *val_out = P_range*P_percent/100.0;	
    }

  else if (strncmp(s_s->subtype, "Setpoint", 1) == 0) //Queries the setpoint 
    {
      if (s_s->num < 1 || s_s->num > 4) // Checks correct Loop number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, 2, 3, or 4. \n", s_s->num);
	  return(1);
	}
      sprintf(cmd_string, "LOOP %d:SETPt?\n", s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}	
    }
    
  else if (strncmp(s_s->subtype, "Mode", 1) == 0) // queries the mode of the control loop
    {
      if (s_s->num < 1 || s_s->num > 4) // Check correct Loop number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, 2, 3, or 4. \n", s_s->num);
	  return(1);
	}
      sprintf(cmd_string, "LOOP %d:TYPe?\n", s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));   
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if (strncmp(ret_string, "OFF", 3) == 0)
	*val_out = 0;
      else if (strncmp(ret_string, "PID", 3) == 0)
	*val_out = 1;
      else if (strncmp(ret_string, "MAN", 3) == 0)
	*val_out = 2;
      else if (strncmp(ret_string, "TAB", 3) == 0)
	*val_out = 3;
      else if (strncmp(ret_string, "RAMPP", 5) == 0)
	*val_out = 4;
      else if (strncmp(ret_string, "RAMPT", 5) == 0)
	*val_out = 5;
      else
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read mode!\n", ret_string);
	  return(1);
	}
    }
    
  else if (strncmp(s_s->subtype, "Relay", 5) == 0) // Queries status of relays
    {
      if (s_s->num < 0 || s_s->num > 1) // Check correct Relay number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 0 or 1. \n", s_s->num);
	  return(1);
	}
      sprintf(cmd_string, "RELays? %d\n", s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

    }
    
  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 
  msleep(600);

  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
  double     ret_val;

  if (strncmp(s_s->subtype, "Temp", 4) == 0)  // Set the control loop setpoint
    {
      if (s_s->new_set_val < 0 ) // check valid value for Temp (>0)
	{
	  fprintf(stderr, "%f is an incorrect temperature. Temp must be greater than 0... You Fool! \n", s_s->new_set_val);
	  return(1);
	}
    
      sprintf(cmd_string, "LOOP %d:SETPt %f\n", s_s->num, s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "LOOP %d:SETPt?\n", s_s->num); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
    }
  
  else if (strncmp(s_s->subtype, "Man", 3) == 0)  // Set the control loop setpoint
    {
      if ((s_s->new_set_val < 0 ) || (s_s->new_set_val > 100 ))  // check valid value for manual power setting
	{
	  fprintf(stderr, "%f is an incorrect setpoint. Power output is percentage of full scale between 0 and 100. \n", s_s->new_set_val);
	  return(1);
	}
    
      sprintf(cmd_string, "LOOP %d:PMAnual %f\n", s_s->num, s_s->new_set_val);
    
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "LOOP %d:PManual?\n", s_s->num); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
	  return(1);
	}
	
      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
    }
  

  else if (strncmp(s_s->subtype, "Mode", 4) == 0) // Set loop mode
    {
      if (s_s->new_set_val < 0.1) 
	sprintf(cmd_string, "LOOP %d:TYPe Off\n", s_s->num);
      else if (s_s->new_set_val < 1.1)
	sprintf(cmd_string, "LOOP %d:TYPe PID\n", s_s->num);
      else if (s_s->new_set_val < 2.1)
	sprintf(cmd_string, "LOOP %d:TYPe Man\n", s_s->num);
      else if (s_s->new_set_val < 3.1)
	sprintf(cmd_string, "LOOP %d:TYPe Table\n", s_s->num);
      else if (s_s->new_set_val < 4.1)
	sprintf(cmd_string, "LOOP %d:TYPe RampP\n", s_s->num);
      else if (s_s->new_set_val < 5.1)
	sprintf(cmd_string, "LOOP %d:TYPe RampT\n", s_s->num);
      else // print error for incorrect input.
	{
	  fprintf(stderr, "%f is an incorrect value for new_set_val. Must be 1 for HI, 2 for MID, 3 for LOW \n", s_s->new_set_val);
	  return(1);
	}
	
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
    }


  else if (strncmp(s_s->subtype, "Heater Range", 8) == 0) // Set heater range
    {
      if (s_s->new_set_val < 1.1) 
	sprintf(cmd_string, "LOOP 1:RANGe HI\n");
      else if (s_s->new_set_val < 2.1)
	sprintf(cmd_string, "LOOP 1:RANGe MID\n");
      else if (s_s->new_set_val < 3.1)
	sprintf(cmd_string, "LOOP 1:RANGe LOW\n");
      else // print error for incorrect input.
	{
	  fprintf(stderr, "%f is an incorrect value for new_set_val. Must be 1 for HI, 2 for MID, 3 for LOW \n", s_s->new_set_val);
	  return(1);
	}
	
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "LOOP 1:RANGE?\n"); // queries heater range
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      sleep(1);
    }
    
  else if (strncmp(s_s->subtype, "Relay", 5) == 0) // Set Relay Mode
    {	
      if (s_s->num < 0 || s_s->num > 1)
	{
	  fprintf(stderr, "%d is an incorrect Relay number. Must be 1 or 0 \n", s_s->num);
	  return(1);
	}
      if (s_s->new_set_val < 1.1) //new_set_val 1 = AUTo mode
	sprintf(cmd_string, "RELays %d:MODe AUTo\n", s_s->num);
      else if (s_s->new_set_val < 2.2) //new_set_val 2 = ON mode
	sprintf(cmd_string, "RELays %d:MODe ON\n", s_s->num);
      else if (s_s->new_set_val < 3.3) //new_set_val 3 = OFF mode
	sprintf(cmd_string, "RELays %d:MODe OFF\n", s_s->num);
      else //print error for incorrect input.
	{
	  fprintf(stderr, "%f is an incorrect value for new_set_val. Must be 1 for AUTo, 2 for ON, 3 for OFF \n", s_s->new_set_val);
	  return(1);
	}

      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
        
      sprintf(cmd_string, "RELays? %d\n", s_s->num);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      sleep(1);
    }
  
  else if (strncmp(s_s->subtype, "Master", 6) == 0) // Master control for all channels
    {
      if ((s_s->new_set_val > 0.5) && (s_s->new_set_val < 1.5))
	sprintf(cmd_string, "CONTROL");    // Start all loops
      else
	sprintf(cmd_string, "STOP");       // Stop all control loops
      
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
    }

  else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    } 

  return(0);
}

#include "main.h"
