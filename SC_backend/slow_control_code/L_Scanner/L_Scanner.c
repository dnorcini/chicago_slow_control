/* Program for Reading out a Keyence IL 1000 laser rangefinder using the DL-EN1 interface*/
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2017 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "L_Scanner"

int inst_dev_1;   // Keyence laser rangefinder
int inst_dev_2;   // Arduino motor controller

double X1 = 10;
double X2 = 20;

double x_array[6000];
double y_array[6000];
char   x_text[42000];
char   y_text[42000];

int    do_scan = 0;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
  if ((inst_dev_1 = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
  
  if ((inst_dev_2 = connect_tcp_raw(i_s->user2, (int)i_s->parm2)) < 0)
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
   close(inst_dev_1);
   close(inst_dev_2);
}


int insert_mysql_xy_data(char *sensor_name, time_t t_in, double v_in, double r_in, char *x_text, char *y_text)
{
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = malloc((strlen(sensor_name)+strlen(x_text)+strlen(y_text)+1024) * sizeof(char));
  
  if (query_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  sprintf(query_strng, 
	  "INSERT INTO `sc_sens_%s` ( `time`, `value`, `rate`, `x_array`, `y_array`) VALUES ( %d, %f, %f, \"%s\", \"%s\")",
	  sensor_name, t_in, v_in, r_in, x_text, y_text
	  );
  ret_val += write_to_mysql(query_strng);
  
  free(query_strng);
  
  return(ret_val);
}


int read_y(double *y_val)
{
  char       cmd_string[64];
  char       ret_string[64];             
  int        return_int;
  
  sprintf(cmd_string, "M0%c%c", CR, LF);
	          
  query_tcp(inst_dev_1, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if(sscanf(ret_string, "M0,%d", &return_int) != 1)
    {
      fprintf(stdout, "Bad return string: \"%s\" in read_y!\n", ret_string);
      return(1);
    }
  if (return_int < 0)
    return(1);
  
  *y_val = (double)return_int/1000.0;
  return(0);
}


int read_x(double *x_val)
{
  char       cmd_string[16];
  char       ret_string[16];             
  int        return_int_1;
  int        return_int_2;
  
  sprintf(cmd_string, "%d R 0\n", 2);

  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if(sscanf(ret_string, "%d", &return_int_1) != 1)
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
      return(1);
    }
  msleep(50);
	    
  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if(sscanf(ret_string, "%d", &return_int_2) != 1)
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
      return(1);
    }

  if (return_int_1 !=return_int_2)
    return(1);

  if (return_int_1 < 0)
    return(1);
  
  *x_val = (double)return_int_1/100.0;
  
  return(0);
}

void home_x(void)
{
  char       cmd_string[64];
  sprintf(cmd_string, "%d H 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}

void halt_x(void)
{
  char       cmd_string[64];
  sprintf(cmd_string, "%d X 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}


void goto_x(double target_x)
{
  char       cmd_string[64]; 
  sprintf(cmd_string, "%d G %d\n", 2, (int)(10*target_x));
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}

int read_counter(long *counts)
{
  long       counts_1;
  long       counts_2;
  char       cmd_string[16]; 
  char       ret_string[16];             

  sprintf(cmd_string, "%d C 0\n", 0);

  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%ld", &counts_1) !=1) 
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read_counter.\n", ret_string);
      return(1);
    }

  msleep(5);
  
  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%ld", &counts_2) !=1) 
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read_counter.\n", ret_string);
      return(1);
    }

  if (counts_2 < 0)
    return(1);

  *counts = counts_2;
  return(0);
}

void reset_counter(void)
{
  char       cmd_string[64];
  sprintf(cmd_string, "%d I 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}


void scan(void)
{
  double x_val, y_val;
  double current_x = -1;
  double target_x;
  long   counts = 0;
  long   prev_counts = -1;
  int i;
  int tries;

  goto_x(X1);

  while (read_x(&x_val) != 0)
    {
      msleep(100);
    }
  
  reset_counter();
  
  goto_x(X2);
  msleep(10);
  
  i=0;
  tries = 0;
  while (tries<5)
    {
      if (read_counter(&counts) == 0)
	{
	  if (counts == prev_counts)
	    tries++;
	  prev_counts = counts; 
	  if ( read_y(&y_val)  == 0 )
	    {
	      current_x = (double)counts * 0.000625 + X1;
	      x_array[i] = current_x;
	      y_array[i] = y_val;
	      i++;
	    }
	  msleep(50);
	}
    }
  implode_from_double(x_text, sizeof(x_text), ",", 
		      x_array, 6000, "%.3f");
  implode_from_double(y_text, sizeof(y_text), ",", 
		      y_array, 6000, "%.3f");  
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  char       cmd_string[64];
  char       ret_string[64];             
  
  if (strncmp(s_s->subtype, "RX", 2) == 0)  // Read out current X position
    {
      s_s->data_type = DONT_AVERAGE_DATA;

      if ( read_x(val_out) == 0)
	{
	  add_val_sensor_struct(s_s, time(NULL), *val_out);
	  s_s->rate = 0.0;
	  write_temporary_sensor_data(s_s);
	  return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
	}      
      return(0);
      
    }
  else if (strncmp(s_s->subtype, "RY", 2) == 0)  // Read out current y position
    {
      s_s->data_type = DONT_AVERAGE_DATA;
      
      if ( read_y(val_out) == 0)
	{
	  add_val_sensor_struct(s_s, time(NULL), *val_out);
	  s_s->rate = 0.0;
	  write_temporary_sensor_data(s_s);
	  return(insert_mysql_sensor_data(s_s->name, s_s->times[s_s->index], s_s->vals[s_s->index], s_s->rate));
	}  
      
      return(0);
    }
  else if (strncmp(s_s->subtype, "Scan", 2) == 0)  // do the scan and dump to the DB
    {
      if (do_scan == 1)
	{ 
	  do_scan = 0;
	  s_s->data_type = DONT_AVERAGE_DATA_OR_INSERT;
	  scan();
	  insert_mysql_xy_data(s_s->name, time(NULL), 0, 0, x_text, y_text);
	}
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

  if (strncmp(s_s->subtype, "Goto", 2) == 0)  // goto 
    {
       if (s_s->new_set_val > 0 )  // only goto positive positions
	{
	  goto_x(s_s->new_set_val);	
	}
    }
  else if (strncmp(s_s->subtype, "Halt", 2) == 0)  // halt motion along x
    {
      halt_x();
    }
  else if (strncmp(s_s->subtype, "Home", 2) == 0)  // home x
    {
      home_x();
    }
 else if (strncmp(s_s->subtype, "X1", 2) == 0)  // Set X1 for scan
    {
      X1=s_s->new_set_val;
    }
  else if (strncmp(s_s->subtype, "X2", 2) == 0)  // Set X2 for scan
    {
      X2=s_s->new_set_val;
    }
  else if (strncmp(s_s->subtype, "Start", 2) == 0)  // do scan
    {
      do_scan = 1;
    }
  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);	    
      return(1);
    }
  return(0);
}


#include "main.h"
