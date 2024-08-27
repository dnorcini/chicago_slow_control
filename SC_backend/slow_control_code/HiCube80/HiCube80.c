/* Program for reading HiCube80 pump with over an ethernet RS-485 serial device server */
/* and putting said readings in to a mysql database. */
/* defined below. */
/**********************/
/* D.Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "HiCube80"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s)  
{
  char       cmd_string[64];

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

  char       cmd_string[64];
  char       ret_string[64];                      

  if (strncmp(s_s->subtype, "actualspd", 9) == 0)  // Read out value for Actual Rotation Speed (rpm)
    {
      
      sprintf(cmd_string, "0010039802=?115\r");
      //sprintf(cmd_string, "0010030902=?107\r");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in 001030906000000000
      int data_length = ret_string[9]-'0'; //C convert char to int
      memmove(&ret_string[0], &ret_string[0]+10, sizeof(ret_string)-10);
      memmove(&ret_string[0]+data_length, &ret_string[-1], sizeof(ret_string)-data_length); 

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read rotation speed!\n", ret_string);
	  return(1);
	}	
    }

else if (strncmp(s_s->subtype, "drvpower", 8) == 0)  // Read out value for drive power [W]
    {
      
      sprintf(cmd_string, "0010031602=?105\r");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in 001031606000000000
      int data_length = ret_string[9]-'0'; //C convert char to int
      memmove(&ret_string[0], &ret_string[0]+10, sizeof(ret_string)-10);
      memmove(&ret_string[0]+data_length, &ret_string[-1], sizeof(ret_string)-data_length); 

      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read drive power!\n", ret_string);
	  return(1);
	}	
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

  /*
  if (strncmp(s_s->subtype, "setrotspd", 9) == 0)  // set rotation speed
    {
      //should write a more general funcion, later...
      char set_data[10]; //convert new_set_val to char array with leading zeroes
      memset (set_data, 0, sizeof(set_data));
      snprintf(set_data, sizeof(set_data)-1, "%06d", (int)s_s->new_set_val); //%06d does the padding

      char checksum[6];
      int sum = (48*4) + (49*2) + 51 + 56;  //start with values we know in telegram
      for (int i = 0; i < 6; i++) {
	sum += ((set_data[i] - '0') + 48); //convert to int, then ASCII value
	fprintf(stdout, "data: %c, sum: %i", set_data[i], sum );
      }
      sum %= 256;
      memset (checksum, 0, sizeof(checksum));
      snprintf(checksum, sizeof(checksum)-1, "%03d", sum); //%03d does the padding
      
      sprintf(cmd_string, "00110308%s%s\r", set_data, checksum);

      fprintf(stdout, "command: %s\n", cmd_string);
     
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);

      sprintf(cmd_string, "0010030802=?106\r"); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in 001030806000000000
      int data_length = ret_string[9]-'0'; //C convert char to int
      //memmove(&ret_string[0], &ret_string[0]+10, sizeof(ret_string)-10);
      //memmove(&ret_string[0]+data_length, &ret_string[-1], sizeof(ret_string)-data_length);

      fprintf(stdout, "return: %s\n", ret_string);
	    
      if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in switch pump on/off!\n", ret_string);
          return(1);
        }

      if (s_s->new_set_val != 0)
        if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
          {
            fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
            return(1);
          }
    }
  */
  
  if (strncmp(s_s->subtype, "pumpgstatn", 9) == 0)  // set on/off pumping station
    {

      // false = 000000, true = 111111
      if ((int)s_s->new_set_val == 0) sprintf(cmd_string, "0011001006000000009\r");
      else if ((int)s_s->new_set_val == 1) sprintf(cmd_string, "0011001006111111015\r");
      
      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);
	
      sprintf(cmd_string, "0010001002=?096\r"); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in 001001006000000000
      int data_length = ret_string[9]-'0'; //C convert char to int
      memmove(&ret_string[0], &ret_string[0]+10, sizeof(ret_string)-10);
      memmove(&ret_string[0]+data_length-5, &ret_string[-1], sizeof(ret_string)-data_length);

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in switch pump on/off!\n", ret_string);
	  return(1);
	}

      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	  {
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	  }
    }

  else if (strncmp(s_s->subtype, "motorpump", 9) == 0)  // set on/off turbo motor pump
    {

      // false = 000000, true = 111111
      if ((int)s_s->new_set_val == 0) sprintf(cmd_string, "0011002306000000013\r");
      else if ((int)s_s->new_set_val == 1) sprintf(cmd_string, "0011002306111111019\r");

      write_tcp(inst_dev, cmd_string, strlen(cmd_string));
      sleep(1);

      sprintf(cmd_string, "0010002302=?100\r"); //queries control loop setpoint
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_tcp(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

      // output in 001001006000000000
      int data_length = ret_string[9]-'0'; //C convert char to int
      memmove(&ret_string[0], &ret_string[0]+10, sizeof(ret_string)-10);
       memmove(&ret_string[0]+data_length-5, &ret_string[-1], sizeof(ret_string)-data_length);

      if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
          fprintf(stderr, "Bad return string: \"%s\" in switch turbo on/off!\n", ret_string);
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

#include "main.h"
