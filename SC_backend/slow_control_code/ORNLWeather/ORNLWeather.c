/* This is the alarm system for the slow control. */
/* that checks for free disk space.  It will insert an error */
/* if the free space % goes below a set value. */
/* This should be run on the master db computer. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include <stdio.h>

#define INSTNAME "ORNLWeather"

time_t last_read_time = 0;

float pressure;
int temperature;
int humidity;
float windspeed;
float precipitation;


int get_html_data(void)
{
  char sys_command[256];
  FILE *fp;
  char data_line[256];
 
  sprintf(sys_command, "cd /tmp; wget -q https://towers.ornl.gov/meteorology/LSS/orrm/data/LEDTOWAa.dat");
  if (system(sys_command) == -1)
    {
      fprintf(stderr, "Trouble getting Weather info from towers.ornl.gov\n");
      sleep(2);
      return(1);
    }
   
   
  if ((fp = fopen("/tmp/LEDTOWAa.dat", "r")) == NULL)
    {
      fprintf(stderr, "Can't open data file.\n");
      sleep(2);
      return(1);
    }

  // 07/05/18 1122 hr TOWA 15Temp  84F  29C  Soil 25CWFr  45 NE  EPZ-ABKX    WSp  2.5 MPH   PkWD  4.2BP 29.36 in  Stability ARH  66%  24h Prc 0.00 in
  fscanf(fp, "%*d/%*d/%*d %*d hr TOWA 15Temp %*dF %dC Soil %*dCWFr %*d %*s %*s WSp %f MPH PkWD %*fBP %f in Stability %*s %d%% 24h Prc %f in", 
	 &temperature, &windspeed, &pressure, &humidity, &precipitation);
  fclose(fp);
  
  sleep(2);
  sprintf(sys_command, "rm /tmp/LEDTOWAa.dat");
  if (system(sys_command) == -1)
    {
      fprintf(stderr, "Trouble deleting weather file.\n");
      sleep(2);
      return(1);
    }

  return(0);
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{

  if (time(NULL) - last_read_time > 20)  // only read out from site at most once every 10 minutes.
    {
      if (get_html_data())
	return(1);
      last_read_time = time(NULL);
    }
  
  if (strncmp(s_s->subtype, "Pressure", 4) == 0)  
    *val_out = 33.8637526*(double)pressure;    // convert inches to mbar
  if (strncmp(s_s->subtype, "Temperature", 4) == 0) 
    *val_out = (double)temperature;
  if (strncmp(s_s->subtype, "Humidity", 4) == 0)  
    *val_out = (double)humidity;
  if (strncmp(s_s->subtype, "Windspeed", 4) == 0) 
    *val_out = 0.44704*(double)windspeed;   // convert MPH to m/s
if (strncmp(s_s->subtype, "Precipitation", 4) == 0)  
  *val_out = 2.54*(double)precipitation;   // convert from inches to cm

  sleep(2);
  return(0);
}


#include "main.h"
