/* Program for reading a lakeshore 218 temperature monitor using the rs232 interface */
/* and putting said readings into a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007 */
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

#include "serial.h"

#define INSTNAME "LS321_serial"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    char               cmd_string[16];   
    ssize_t            wstatus = 0;
    ssize_t            rdstatus = 0;
    struct  termios    tbuf;  /* serial line settings */
   
    if (( inst_dev = open(i_s->dev_address, (O_RDWR | O_NDELAY), 0)) < 0 ) 
    {
        fprintf(stderr, "Unable to open tty port specified:\n");
        exit(1);
    }
    
    /* set up the serial line parameters :  */
    tbuf.c_cflag = CS8|B9600|CREAD|PARODD;
    tbuf.c_iflag = INPCK|ISTRIP;
    tbuf.c_oflag = 0;
    tbuf.c_lflag = 0;
    tbuf.c_cc[VMIN] = 0; 
    tbuf.c_cc[VTIME]= 0;

    if (tcsetattr(inst_dev, TCSANOW, &tbuf) < 0) {
        fprintf(stderr, "Unable to set device '%s' parameters\n", i_s->dev_address);
        exit(1);
    }

    sprintf(cmd_string, "*CLS");
    wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    wstatus = write(inst_dev, &CR, sizeof(char));
    wstatus = write(inst_dev, &LF, sizeof(char));    
    msleep(1000);  
    //sprintf(cmd_string, "*IDN?");
    //wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    // wstatus = write(inst_dev, &CR, sizeof(char));
    //wstatus = write(inst_dev, &LF, sizeof(char));
    //msleep(1000);
    //char       val[33];                      
    //rdstatus = read(inst_dev, &val, 32); 
    //fprintf(stdout, "Return from dev: %s \n", val);
    //msleep(1000);  
    //char       val2[33];                      
    //sprintf(cmd_string, "CRDG? B");
    //wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
    //wstatus = write(inst_dev, &CR, sizeof(char));// \r 
    //wstatus = write(inst_dev, &LF, sizeof(char));// \n
    //rdstatus = read(inst_dev, &val2, 32); 
    //fprintf(stdout, "second Return from dev: %s \n", val2);
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s_a, double *val_out)
{
  // Reads out the current value of the Lakeshore temp monitor
    
    char       cmd_string[16];
    char       ret_string[16];
    char       val[33];                      
    ssize_t    wstatus = 0;
    ssize_t    rdstatus = 0;

    if (strncmp(s_s_a->subtype, "Temp", 4) == 0)  // Temperatures
    {
        //printf("yodel");
      
        /*
        sprintf(cmd_string, "*CLS");
        wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
        wstatus = write(inst_dev, &CR, sizeof(char));
        wstatus = write(inst_dev, &LF, sizeof(char));
        msleep(1000);
        sprintf(cmd_string, "*IDN?");
        wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
        wstatus = write(inst_dev, &CR, sizeof(char));
        wstatus = write(inst_dev, &LF, sizeof(char));
        msleep(1000);
	
        rdstatus = read(inst_dev, &val, 32);
        fprintf(stdout, "Return from dev: %s \n", val);
        */
        //msleep(1000);   
        //      sprintf(cmd_string, "CRDG?%i",s_s_a->num);
        //      sprintf(cmd_string, "CRDG?");
        //      sprintf(cmd_string, "KRDG?B");
        if (s_s_a->num == 1){
       	    sprintf(cmd_string, "KRDG?A");
        }
        else if (s_s_a->num == 2){
	    sprintf(cmd_string, "KRDG?B");
	}
	else{
	    fprintf(stderr, "%d wrong value for num. Must be 0, 1 or 2 \n", s_s_a->num);
            return(1);
	}
        //      sprintf(cmd_string, "CRDG? B\r\n");
	//fprintf(stdout, "Write to inst: %s \n", cmd_string);
        wstatus = write(inst_dev, cmd_string, strlen(cmd_string));
        wstatus = write(inst_dev, &CR, sizeof(char));
        wstatus = write(inst_dev, &LF, sizeof(char));
        
	msleep(300);  
	rdstatus = read(inst_dev, &val, 32); 
	/* for (int i =0 ; i< 17; ++i){ */
	/*   fprintf(stdout, "char = %c \n", val[i]); */
	/* } */
	/* fprintf(stdout, "hex = %a \n", val); */
	/* msleep(300);   */
	fprintf(stdout, "aaa = %s \n", val);
	sscanf(val, "%lf", val_out);
	msleep(1000);  
    }
    else if (strncmp(s_s_a->subtype, "Heater", 6) == 0) // Read out heater power (in current percentage here)
    {
      if (s_s_a->num < 1 || s_s_a->num > 2) // Checks correct Loop number
	{
	  fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s_a->num);
	  return(1);
	}
      sprintf(cmd_string, "HTR? %d\n", s_s_a->num);
      query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      msleep(200);
      query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
      
      if(sscanf(ret_string, "%lf", val_out) != 1)
	{
	  fprintf(stderr, "Bad return string: \"%s\" in read temperature!\n", ret_string);
	  return(1);
	}	
    }
    else
    {
	fprintf(stderr, "Wrong subtype for %s \n", s_s_a->name);
	return(1);
    }
    return(0);    
}


#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    char       cmd_string[16];
    char       ret_string[16];
    double     ret_val;

    if (strncmp(s_s_a->subtype, "Setpoint", 8) == 0)  // Set the control loop setpoint
    {
        if (s_s_a->new_set_val < 0 ) // check valid value for Temp (>0)
        {
	    fprintf(stderr, "%f is an incorrect temperature. Temp must be greater than 0... You Fool! \n", s_s_a->new_set_val);
	    return(1);
        }

        if (s_s_a->num < 1 || s_s_a->num > 2) // Checks correct Loop number
        {
	    fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s_a->num);
	    return(1);
        }
    
        sprintf(cmd_string, "SETP %d,%f\n", s_s_a->num, s_s_a->new_set_val);
    
        write(inst_dev, cmd_string, strlen(cmd_string));
        sleep(1);
	
        sprintf(cmd_string, "SETP? %d\n", s_s_a->num); //queries control loop setpoint
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
        msleep(200);
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

        if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
            fprintf(stderr, "Bad return string: \"%s\" in read setpoint!\n", ret_string);
            return(1);
        }
	
      /*      if (s_s->new_set_val != 0)
	if (fabs(ret_val - s_s->new_set_val)/s_s->new_set_val > 0.1)
	{
	    fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s->new_set_val, ret_val);
	    return(1);
	}
      */
    }

    else if (strncmp(s_s_a->subtype, "setramprate", 11) == 0)  // Set the control loop ramprate
    {
        if (s_s_a->new_set_val < 0 ) // check valid value for Ramp (>0)
	{
       	    fprintf(stderr, "%f is an incorrect ramprate. Ramprate must be greater than 0... You Fool! \n", s_s_a->new_set_val);
	    return(1);
	}

        if (s_s_a->num < 1 || s_s_a->num > 2) // Checks correct Loop number
	{
	    fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s_a->num);
	    return(1);
	}
    
        sprintf(cmd_string, "RAMP %d,1,%f\n", s_s_a->num, s_s_a->new_set_val);
    
        write(inst_dev, cmd_string, strlen(cmd_string));
        sleep(1);
	
        sprintf(cmd_string, "RAMP? %d\n", s_s_a->num); //queries control loop ramprate
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
        msleep(200);
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

        memmove(&ret_string[0], &ret_string[0]+2, sizeof(ret_string)-2); // output in n,+nnn (on/off,value)
	    
        if(sscanf(ret_string, "%lf", &ret_val) != 1)
	{
	    fprintf(stderr, "Bad return string: \"%s\" in read ramprate!\n", ret_string);
	    return(1);
	}
	
        if (s_s_a->new_set_val != 0)
	  if (fabs(ret_val - s_s_a->new_set_val)/s_s_a->new_set_val > 0.1)
	  {
	      fprintf(stderr, "New ramprate of: %f is not equal to read out value of %f\n", s_s_a->new_set_val, ret_val);
	      return(1);
	  }
    }   

    else if (strncmp(s_s_a->subtype, "heateronoff", 11) == 0)  // Set the heater on/off
    {
        if (s_s_a->num < 1 || s_s_a->num > 2) // Checks correct Loop number
        {
            fprintf(stderr, "%d is an incorrect value for num. Must be 1, or 2. \n", s_s_a->num);
            return(1);
        }
      
        sprintf(cmd_string, "RANGE %d,%i\n", s_s_a->num, (int)s_s_a->new_set_val);
        fprintf(stdout, "%s\n", cmd_string);
	    
        write(inst_dev, cmd_string, strlen(cmd_string));
        sleep(1);

        sprintf(cmd_string, "RANGE? %i\n", s_s_a->num); //queries control loop on/off
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
        msleep(200);
        query_serial(inst_dev, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));

        fprintf(stdout, "Heater status: %s\n", ret_string);
      
        if(sscanf(ret_string, "%lf", &ret_val) != 1)
        {
            fprintf(stderr, "Bad return string: \"%s\" in read heater onoff!\n", ret_string);
            return(1);
        }

        if (s_s_a->new_set_val != 0)
          if (fabs(ret_val - s_s_a->new_set_val)/s_s_a->new_set_val > 0.1)
          {
              fprintf(stderr, "New setpoint of: %f is not equal to read out value of %f\n", s_s_a->new_set_val, ret_val);
              return(1);
          }
    }
    
    else       // Print an error if invalid subtype is entered
    {
        fprintf(stderr, "Wrong type for %s \n", s_s_a->name);
        return(1);
    }

    return(0);
}

#include "main.h"

