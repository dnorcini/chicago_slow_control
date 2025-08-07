/* Program for reading Raritan PX3-5145R PDU */
/* and putting said readings into mysql database */
/**********************/
/* D. Norcini, UChicago, 2020*/

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "Raritan_PX3"

// These are modbus parameters:
#define SLAVE    0xFF
// default modbus port is 502, not used yet
#define mod_port 502

modbus_param_t inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    modbus_init_tcp(&inst_dev, i_s->dev_address, mod_port);
    
    if (modbus_connect(&inst_dev) == -1) 
    {
	fprintf(stderr, "ERROR Connection failed\n");
	exit(1);
    }
    
    return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    modbus_close(&inst_dev);
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    uint16_t channel_address;
    uint16_t ret_val[2];
    uint16_t mode[1];
    int      max_tries = 2;
    int      ret;

    // use RELATIVE address (modcon convention: holding register 40001 = 0)
    if (strncmp(s_s->subtype, "current", 7) == 0)
    {
      channel_address += 12298;
      do
        {
          ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
          msleep(100);
          max_tries++;
          if (max_tries > 10)
            return(1);
        }
        while (ret < 2);

        // use a union to convert from the two 16 bit bytes returned to a float
	union { float fVal; uint16_t bytes[2]; } value;

	//big endian = lowest register = most sig bits (so not sure why need to swap..)
	value.bytes[0] = ret_val[1]; 
        value.bytes[1] = ret_val[0];
	
        *val_out = value.fVal; //amps
        printf(val_out);


        return(0);
    }

    else if (strncmp(s_s->subtype, "power", 5) == 0)

    { 
      channel_address += 12306; 
        do
        {
          ret = read_holding_registers(&inst_dev, SLAVE, channel_address, 2, ret_val);
          msleep(100);
          max_tries++;
          if (max_tries > 10)
            return(1);
        }
        while (ret < 2);

        // use a union to convert from the two 16 bit bytes returned to a float                     
        union { float fVal; uint16_t bytes[2]; } value;

	//big endian = lowest register = most sig bits (so not sure why need to swap..)
        value.bytes[0] = ret_val[1]; 
        value.bytes[1] = ret_val[0];

        *val_out = value.fVal; //watts                                                               
	
        return(0);
    }
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{ 
    uint16_t channel_address;        
    uint8_t  ret_val[1];
    int      set_val;
    int      max_tries = 0;
    int      ret;
 
    if (strncmp(s_s->subtype, "outlet", 6) == 0)
      {
	uint16_t start_address = 256;
	
	if ((s_s->num > 8) || (s_s->num < 1))
	  {
	    fprintf(stderr, "Wrong value for num (%d != 1-%d) in %s \n", s_s->num, 8, s_s->name);
	    return(1);
	  }
	
	if (s_s->new_set_val < 0.5) {
	  set_val = 0;
	}

	else if (s_s->new_set_val > 0.5) {
	  set_val = 1;
	}
	
	s_s->new_set_val = set_val;
	
	channel_address = start_address + (s_s->num-1); //if looping through all outlets

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
}

// #include "main.h"
/* This is the header file that defines main() for all daemons */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2009 */
/* James public licence. */

#ifndef _SC_main_H_
#define _SC_main_H_


#ifndef _def_set_up_inst
// Generic set up placeholder: to be found in instrument specific code
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{return(0);}
#endif // set_up_inst

#ifndef  _def_clean_up_inst
// Generic clean up placeholder: to be found in instrument specific code
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{;}
#endif // clean_up_inst

#ifndef _def_read_sensor
// Generic read placeholder: to be found in instrument specific code
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *sensor_value) 
{return(0);}
#endif // read_sensor

#ifndef _def_set_sensor
// Generic set placeholder: to be found in instrument specific code
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s) 
{return(0);}
#endif // set_sensor


int main (int argc, char *argv[])
{
  char                   **my_argv;
  char                   inst_name[16] = {0};
  int                    i;
  struct inst_struct     this_inst;
  struct sensor_struct   *all_sensors;

  // save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++) 
    my_argv[i] = strdup(argv[i]);
   
  my_argv[i] = NULL;   
  printf("Command-line arguments:\n");
  for (int j = 0; j < argc; j++) {
    printf("  argv[%d] = %s\n", j, my_argv[j]);
}

    
  sprintf(db_conf_file, DEF_DB_CONF_FILE);
  printf("Using DB config file: %s\n", db_conf_file);


  parse_CL_for_string(argc,  argv, INSTNAME, inst_name);
  printf("Parsed instrument name: %s\n", inst_name);

  read_mysql_inst_struct(&this_inst, inst_name);
  printf("Instrument loaded from DB:\n");
  printf("  Name: %s\n", this_inst.name);
  printf("  PID: %d\n", this_inst.PID);

  generate_sensor_structs(&this_inst, &all_sensors);
  printf("Number of sensors initialized: %d\n", this_inst.num_active_sensors);

    
  // detach current process
  // daemonize(this_inst.name);
  
  my_signal = 0;
  // ignore these signals 
  signal(SIGINT, SIG_IGN);
  signal(SIGQUIT, SIG_IGN);    
  // install signal handler
  signal(SIGHUP, handler);
  signal(SIGINT, handler);
  signal(SIGQUIT, handler);
  signal(SIGTERM, handler);

  register_inst(&this_inst);

  if (set_up_inst(&this_inst, all_sensors))
    {
      msleep(1000);
      my_signal = SIGHUP;
    }
  mysql_inst_run_status(&this_inst);
   
  while (my_signal == 0)     //  main loop here!
    {
      sensor_loop(&this_inst, all_sensors);
      mysql_inst_run_status(&this_inst);
    }
    
  /////////////  Clean up if we get a signal
  clean_up_inst(&this_inst, all_sensors);

  unregister_inst(&this_inst);
  free(all_sensors);

  if (my_signal == SIGHUP)         ///  restart called
    {
      long fd;
      // close all files before restart
      for (fd = sysconf(_SC_OPEN_MAX); fd > 2; fd--) 
	{
	  int flag;
	  if ((flag = fcntl(fd, F_GETFD, 0)) != -1)
	    fcntl(fd, F_SETFD, flag | FD_CLOEXEC);
	}
      sleep(2);
      execv(my_argv[0], my_argv);
      fprintf(stderr, "execv() failed.");
      exit(1);
    }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);
    
  free(my_argv);

  exit(0);
}


#endif // _SC_main_H_
