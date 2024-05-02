/* Program for controlling the WIENER_VME64x crate using the SNMP interface */
/******************************/
/* D. Norcini, UChicago, 2024 */


#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

//#include "ethernet.h"

#define INSTNAME "WIENER_VME"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  char       cmd_string[64];

  // connection taken care of in net-snmp command
  /*  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
      }*/
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  char       cmd_string[512];
  char       ret_string[512];
  char       buffer[128];
  FILE*      tmp;

    if (strncmp(s_s->subtype, "outvolt",7) == 0)
    {
      if (s_s->num < 0 || s_s->num > 6 || s_s->num == 2 || s_s->num == 4) // Checks correct sensor number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must between 0, 1, 3, 5. \n", s_s->num);
	  return(1);
	}

      sprintf(cmd_string,"snmpwalk -v 2c -m +WIENER-CRATE-MIB -c public %s outputMeasurementSenseVoltage.u%i", i_s->dev_address, s_s->num);
      //sleep(300);

      // send snmp command to command line and store response in tmp file
      tmp = popen(cmd_string, "r");
      if (tmp == NULL)
	{
	  printf("Failed to run command\n" );
	  exit(1);
	}

      // read response one line at a time
      while (fgets(buffer, sizeof(buffer), tmp) != NULL)
	{
	  sprintf(ret_string,buffer);
	}

      // close tmp file
      pclose(tmp);

      // output in WIENER-CRATE-MIB::outputVoltage.u0 = Opaque: Float: 5.000000 V
      int data_length = strlen(ret_string); //C convert char to int
      memmove(&ret_string[0]+data_length-2, &ret_string[-1], sizeof(ret_string)-data_length); //remove null and units
      memmove (&ret_string[0], &ret_string[0]+data_length-12, data_length+6); //remove response string

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read output voltage!\n", ret_string);
	  return(1);
	}
    }

    else if (strncmp(s_s->subtype, "outcurr",7) == 0)
    {
      if (s_s->num < 0 || s_s->num > 6 || s_s->num == 2 || s_s->num == 4) // Checks correct sensor number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must between 0, 1, 3, 5. \n", s_s->num);
	  return(1);
	}

      sprintf(cmd_string,"snmpwalk -v 2c -m +WIENER-CRATE-MIB -c public %s outputMeasurementCurrent.u%i", i_s->dev_address, s_s->num);

      // send snmp command to command line and store response in tmp file
      tmp = popen(cmd_string, "r");
      if (tmp == NULL)
	{
	  printf("Failed to run command\n" );
	  exit(1);
	}

      // read response one line at a time
      while (fgets(buffer, sizeof(buffer), tmp) != NULL)
	{
	  sprintf(ret_string,buffer);
	}

      // close tmp file
      pclose(tmp);

       // output in WIENER-CRATE-MIB::outputVoltage.u0 = Opaque: Float: 5.000000 V
      int data_length = strlen(ret_string); //C convert char to int
      memmove(&ret_string[0]+data_length-2, &ret_string[-1], sizeof(ret_string)-data_length); //remove null and units
      memmove (&ret_string[0], &ret_string[0]+data_length-11, data_length+6); //remove response string

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read output current!\n", ret_string);
	  return(1);
	}
    }

    else if (strncmp(s_s->subtype, "fantemp",7) == 0)
    {
      sprintf(cmd_string,"snmpwalk -v 2c -m +WIENER-CRATE-MIB -c public %s fanAirTemperature.0", i_s->dev_address);

      // send snmp command to command line and store response in tmp file
      tmp = popen(cmd_string, "r");
      if (tmp == NULL)
	{
	  printf("Failed to run command\n" );
	  exit(1);
	}

      // read response one line at a time
      while (fgets(buffer, sizeof(buffer), tmp) != NULL)
	{
	  sprintf(ret_string,buffer);
	}

      // close tmp file
      pclose(tmp);

       // output in WIENER-CRATE-MIB::fanAirTemperatre.0 = INTEGER: 25 deg.C
      int data_length = strlen(ret_string); //C convert char to int
      memmove(&ret_string[0]+data_length-6, &ret_string[-1], sizeof(ret_string)-data_length); //remove null and units
      memmove (&ret_string[0], &ret_string[0]+data_length-9, data_length+2); //remove response string

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read fan temp!\n", ret_string);
	  return(1);
	}
    }

    else if (strncmp(s_s->subtype, "status",6) == 0)
    {
      sprintf(cmd_string,"snmpget -v 2c -m +WIENER-CRATE-MIB -c public %s sysMainSwitch.0", i_s->dev_address);

      // send snmp command to command line and store response in tmp file
      tmp = popen(cmd_string, "r");
      if (tmp == NULL)
        {
          printf("Failed to run command\n" );
          exit(1);
        }

      // read response one line at a time
      while (fgets(buffer, sizeof(buffer), tmp) != NULL)
        {
          sprintf(ret_string,buffer);
        }

      // close tmp file
      pclose(tmp);

       // output in WIENER-CRATE-MIB::sysMainSwitch.0 = INTEGER: on(1)
      int data_length = strlen(ret_string); //C convert char to int
      memmove(&ret_string[0]+data_length-2, &ret_string[-1], sizeof(ret_string)-data_length); //remove null and units
      memmove (&ret_string[0], &ret_string[0]+data_length-3, data_length+2); //remove response string

      if(sscanf(ret_string, "%lf", val_out) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in read status main switch!\n", ret_string);
          return(1);
        }
    }

    else
      {
	fprintf(stderr, "Wrong type for %s\n", s_s->name);
	return(1);
      }

  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  char       cmd_string[64];
  char       ret_string[64];
  double     ret_val;
  char       buffer[128];
  FILE*      tmp;

  if (strncmp(s_s->subtype, "setmain", 7) == 0)  // Set the control loop setpoint
    {
      /*
      if ((int)s_s->new_set_val != 0 || (int)s_s->new_set_val != 1)
	{
	  fprintf(stderr, "%i is an incorrect setting. Can only switch off/on or 0/1...! \n", (int)s_s->new_set_val);
	  return(1);
	}
      */
	sprintf(cmd_string,"snmpset -v 2c -m +WIENER-CRATE-MIB -c private %s sysMainSwitch.0 i %i", i_s->dev_address, (int)s_s->new_set_val);
	// send snmp command to command line and store response in tmp file
	tmp = popen(cmd_string, "r");
      	if (tmp == NULL)
	{
	  printf("Failed to run command\n" );
	  exit(1);
	}

      	// read response one line at a time
      	while (fgets(buffer, sizeof(buffer), tmp) != NULL)
	{
	  sprintf(ret_string,buffer);
	}

      	// close tmp file
      	pclose(tmp);

       	// output in WIENER-CRATE-MIB::outputVoltage.u0 = Opaque: Float: 5.000000 V
	int data_length = strlen(ret_string); //C convert char to int
      	memmove(&ret_string[0]+data_length-1, &ret_string[-1], sizeof(ret_string)-data_length); //remove null and units
      	memmove (&ret_string[0], &ret_string[0]+data_length-2, data_length+1); //remove response string


	if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read set main!\n", ret_string);
	  return(1);
	}

	if (s_s->new_set_val != 0)
	  if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	   {
	     fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	     return(1);
	   }
    }

	 else       // Print an error if invalid subtype is entered
    {
      fprintf(stderr, "Wrong type for %s \n", s_s->name);
      return(1);
    }

  return(0);
}
