/* Program for reading a CryoCon34 temp. controller */
/* and putting said readings in to a mysql database. */
/* Operational parameters are taken from conf_file_name */
/* defined below. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006 */
/* GPL public licence. */

#include <stdlib.h>
#include <stdio.h>
#include <sys/fcntl.h>
#include <unistd.h>
#include <time.h>

#define num_sensors 4

#include "SC_db_interface_raw.h"

int read_mysql_sensor_data_greater_than_t0 (char *sensor_name, time_t *t_out, double *v_out, time_t t0, int N)
{
    /*   Reads sensor data from the database. */
    /*   Returns 0 is all is well, 1 if there is an error. */
    /*   Takes sensor_name and pointers to times/values and files them */
    /*   with N values from the mysql database where the time is greater */
    /*   than t0 */
    int ret_val = 0;
    char  *query_strng; 
    char  *res_strng;
    char  *el_ij;
    int num_rows = 0;
    int num_cols = 0;
    int i;

    query_strng = malloc((strlen(sensor_name)+128) * sizeof(char)); 
    res_strng = malloc(128 * sizeof(char)); 
    el_ij = malloc(512 * sizeof(char)); 
    if ((query_strng == NULL) || (res_strng == NULL))
    {
	fprintf(stderr, "Malloc failed\n");
	return(1);
    }

    sprintf(query_strng, "SELECT * FROM `%s` WHERE `time` >= %d ORDER BY `time` ASC LIMIT %d", sensor_name, t0, N);
    //fprintf(stdout, "%s \n", query_strng);

    ret_val += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    //fprintf(stdout, "%s \n", res_strng);

    for (i=0; i < num_rows; i++)
    {
	ret_val += get_element(el_ij, res_strng, num_rows, num_cols, i, 0);
	sscanf(el_ij, "%d , ", &t_out[i]);
	ret_val += get_element(el_ij, res_strng, num_rows, num_cols, i, 1);
	sscanf(el_ij, "%le ,", &v_out[i]);
    }
     
    free(query_strng);
    free(res_strng);
    free(el_ij);

    return(ret_val);
}

int main (int argc, char *argv[])
{
    FILE       *file_des;          // file descriptor for the config file
    char       cmd_string[16];
    char       c[64];        /* string received from the controller */
    char       val[10];
    char       sensor_names_in[num_sensors][16];
    char       sensor_names_out[num_sensors][16];
    int        sleep_times[num_sensors];
    int        i;
    
    float      Rate;    
    time_t     last_time = 0; 
    double     last_val = 0;
    time_t     new_time[2] = {0,0};
    double     new_val[2] = {0,0};
    char       *db_message;


    /////   Read in config file then close it.
    ////////////////////////////////////////////////////////////////////////////////////////////
    file_des = parse_command_line(argc, argv);
    
    for (i=0; i<num_sensors; i++)
    {
	if (i != retrieve_config_parms (file_des, "sensor_names_out", sensor_names_out[i], i))
	{
	    fprintf(stderr, "Cound not find sensor parameter.\n");
	    exit(1);
	}
	if (i != retrieve_config_parms (file_des, "sensor_names_in", sensor_names_in[i], i))
	{
	    fprintf(stderr, "Cound not find sensor parameter.\n");
	    exit(1);
	}
	
    }
    fclose(file_des);
    ////////////////////////////////////////////////////////////////////////////////////////////////


    while (1) {
	for(i=0; i<num_sensors; i++)
	{
	    if (strncmp(sensor_names_in[i], "NULL", 4) != 0)
	    {
		read_mysql_sensor_data(sensor_names_out[i], &last_time, &last_val);  
		read_mysql_sensor_data_greater_than_t0(sensor_names_in[i], new_time, new_val, last_time, 2);
		
		while (new_time[1] > last_time)
		{
		    if ((new_val[1]<new_val[0]) || (new_time[1]<=new_time[0]))
			Rate = 0;
		    else
			Rate = (new_val[1]-new_val[0])/(new_time[1]-new_time[0]);
		    
		    last_time = new_time[1];
		    insert_mysql_sensor_data(sensor_names_out[i], last_time, Rate);

		    //fprintf(stdout, "new time 1: %d, new time 2: %d \n",  new_time[0], new_time[1]);
		    
		    //fprintf(stdout, "last time: %d, Rate: %f \n",  last_time, Rate);

		    read_mysql_sensor_data_greater_than_t0(sensor_names_in[i], new_time, new_val, last_time, 2);
		    
		    usleep(100000);
		}

		sleep_times[i]=read_mysql_sensor_refresh_time(sensor_names_out[i]);	    
	    }
	    else
		sleep_times[i]=500;
	}  // end for
	sleep(min_int(sleep_times, num_sensors));
    }  // end outer while  (should never get out)
    exit(0);
}

