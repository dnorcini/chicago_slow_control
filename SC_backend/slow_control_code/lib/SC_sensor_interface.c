/* This is a library for the sensor structures fns. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2007, 2010 */
/* James public licence. */

#include "SC_sensor_interface.h"


int inc_index(int idx)
{
    ////////    increment indices using fixed rollover value
    idx++;
    if (idx == NUM_SAVED_VALS)
	idx = 0; 
    return(idx);
}

int dec_index(int idx)
{
    ////////    decrement indices using fixed rollover value
    if (idx == 0)
	idx = NUM_SAVED_VALS; 
    idx--;
    return(idx);
}


void init_sensor_struct(struct sensor_struct *s_s)
{
    int i;
    
    s_s->last_update_time = time(NULL) + 5;
    s_s->next_update_time = time(NULL) + s_s->update_period + 5;
    s_s->last_set_time = time(NULL);

    for (i=0; i<NUM_SAVED_VALS; i++)
    {
	s_s->times[i] = 0;
	s_s->vals[i]  = 0;
    }
    s_s->index = -1;
    s_s->rate = 0;
    s_s->avg = 0;
    s_s->min_val = DBL_MAX;
    s_s->max_val = DBL_MIN; 

    s_s->data_type = SCALAR_DATA;  // by default set data type to scalar for insertion into db.

    s_s->inst_dev = 0;

    // make sure that the directory the temporary data is stored in
    // exists
    mkdir(TEMP_DATA_DIR, S_IRWXU | S_IRWXG | S_IROTH | S_IXOTH);
}


void add_val_sensor_struct(struct sensor_struct *s_s, time_t time, double val)
{
    //  add new time and value pair to the sensor structure

    s_s->index = inc_index(s_s->index);     // index refers to last data writen to the structure
    s_s->times[s_s->index] = time;
    s_s->vals[s_s->index]  = val;
    
    s_s->min_val = min_double(s_s->vals, NUM_SAVED_VALS);
    s_s->max_val = max_double(s_s->vals, NUM_SAVED_VALS);
}


void avg_n_vals_sensor_struct(struct sensor_struct *s_s, int last_n, int disc_high_n, int disc_low_n)
{
    // Process the sensor structure to calculate the average and rate for the
    // last 'last_n' elements.  Set last_n to <= 0 to avgerage all values.
    
    int i;
    int val_index;       // running index of the average calculation
    int real_n = 0;      // actual number of averages
    double avg_val = 0;
    
    if ((last_n > NUM_SAVED_VALS) || (last_n <= 0))
	last_n = NUM_SAVED_VALS;
    
    val_index = s_s->index;
    for (i=0; i < last_n; i++)
    {
	if (s_s->times[val_index] > 0)
	{
	    if ((s_s->vals[val_index] == s_s->max_val) && (disc_high_n > 0))  // discard high value (only once!)
	    {
		val_index = dec_index(val_index);
		disc_high_n--;
	    }
	    else if ((s_s->vals[val_index] == s_s->min_val) && (disc_low_n > 0)) // discard low value (only once!)
	    {
		val_index = dec_index(val_index);
		disc_low_n--;
	    }
	    else
	    {
		avg_val += s_s->vals[val_index];
		real_n++;
		val_index = dec_index(val_index);
	    }
	}
	else
	    break;
    }
    if (real_n > 0)
    {
	avg_val/=real_n;
	s_s->avg = avg_val;
    }
    else
    {
	s_s->avg = s_s->vals[s_s->index];	
    }
}

void avg_vals_sensor_struct(struct sensor_struct *s_s, int disc_high_n, int disc_low_n)
{
    // Process the sensor structure to calculate the average and rate for the
    // last set of elements since 'last_ut'. 
    
    int i;
    int val_index = s_s->index;       // running index of the average calculation
    int real_n = 0;                   // actual number of averages
    double avg_val = 0;
    
    for (i=0; i < NUM_SAVED_VALS; i++)
    {
	if (s_s->times[val_index] > s_s->last_update_time)
	{
	    if ((s_s->vals[val_index] == s_s->max_val) && (disc_high_n > 0))  // discard high value (only once!)
	    {
		val_index = dec_index(val_index);
		disc_high_n--;
	    }
	    else if ((s_s->vals[val_index] == s_s->min_val) && (disc_low_n > 0)) // discard low value (only once!)
	    {
		val_index = dec_index(val_index);
		disc_low_n--;
	    }
	    else
	    {
		avg_val += s_s->vals[val_index];
		val_index = dec_index(val_index);
		real_n++;
	    }
	}
	else
	    break;
    }
    if (real_n > 0)
    {
	avg_val/=real_n;
	s_s->avg = avg_val;
    }
    else
	s_s->avg = s_s->vals[s_s->index];	
}

void diff_vals_sensor_struct(struct sensor_struct *s_s, int disc_high_n, int disc_low_n)
{
    // Process the sensor structure to calculate the average and rate for the
    // last set of elements since 'last_ut'. 
    
    int i;
    int val_index = s_s->index;   // running index of the slope calculation
    int real_n    = 0;            // actual number of averages
    double sumx     = 0.0;
    double sumy     = 0.0;
    double sumx2    = 0.0;
    double sumy2    = 0.0;
    double sumxy    = 0.0;
    double sxx;
    double sxy;
    double slope;
    time_t cur_time = time(NULL);  // need to subtract this off to keep from rounding to oblivion.
    double x;
    double y;

    for (i=0; i < NUM_SAVED_VALS; i++)
    {
	if (s_s->times[val_index] > s_s->last_update_time)
	{
	    if ((s_s->vals[val_index] == s_s->max_val) && (disc_high_n > 0))  // discard high value (only disc_high_n times!)
	    {
		val_index = dec_index(val_index);
		disc_high_n--;
	    }
	    else if ((s_s->vals[val_index] == s_s->min_val) && (disc_low_n > 0)) // discard low value (only disc_low_n times!)
	    {
		val_index = dec_index(val_index);
		disc_low_n--;
	    }
	    else
	    {
		x = (double)(s_s->times[val_index]-cur_time);
		y = s_s->vals[val_index];
		
		sumx += x;
		sumy += y;
		sumx2 += x * x;
		sumy2 += y * y;
		sumxy += x * y;

		real_n++;
		val_index = dec_index(val_index);
	    }
	}
	else
	    break;
    }
    if (real_n > 1)
    {
	sxx = sumx2 - (sumx * sumx / real_n);
	sxy = sumxy - (sumx * sumy / real_n);
	    
	if (sxx != 0)
	    slope = sxy / sxx;
	else
	    slope = 0;
	s_s->rate = slope;
    }
    else                               /// if all else fails, take last 2 points for slope
    {
	val_index = s_s->index;
	sumx        = 0.0;
	sumy        = 0.0;
	sumx2       = 0.0;
	sumy2       = 0.0;
	sumxy       = 0.0;
	real_n    = 0;
	for (i=0; i < 2; i++)
	{      
	    x = (double)(s_s->times[val_index]-cur_time);
	    y = s_s->vals[val_index];
	    
	    sumx += x;
	    sumy += y;
	    sumx2 += x * x;
	    sumy2 += y * y;
	    sumxy += x * y;
	    
	    real_n++;
	    val_index = dec_index(val_index);
	}
	sxx = sumx2 - (sumx * sumx / real_n);
	sxy = sumxy - (sumx * sumy / real_n);
	
	if (sxx != 0)
	    slope = sxy / sxx;
	else
	    slope = 0;
	s_s->rate = slope;
    }
}

void write_temporary_sensor_data(struct sensor_struct *s_s)
{
    FILE *file_des;
    char file_name[256];
    int  val_index = s_s->index;  // running index
    int  i;
    
    sprintf(file_name, "%s/%s", TEMP_DATA_DIR, s_s->name);
    if ((file_des = fopen(file_name, "w")) == NULL)
    {
	fprintf(stderr, "Unable to open temp file: %s \n", file_name);
    }
    fprintf(file_des, "# %s:%s (%s) || Rate: %e (%s/sec) \n",  
	    s_s->name, s_s->description, s_s->units, s_s->rate, s_s->units);
    
    for (i=0; i < NUM_TEMP_VALS; i++)
    {
	val_index = dec_index(val_index);
    }
    for (i=0; i < NUM_TEMP_VALS; i++)
    {
      fprintf(file_des, "%lu, %e \n",  (unsigned long)s_s->times[val_index], s_s->vals[val_index]);
	val_index = inc_index(val_index);
    }
    fclose(file_des);
}

void sensor_loop(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  // This goes in the while loop for each interface.
  // simply loops over the  sensors, reads them out and fills up
  // the structure, then reports values to the database when the time is up.
  // requires that the function:
  //    int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out);
  // and
  //    int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s);
  // be defined to read out and control the instrument.
  
  struct sensor_struct *this_sensor_struc;
  struct sys_message_struct this_sys_message_struc;
  int i, j;
  double sensor_value = 0;
  double rate_place_holder;
  int max_retries = 5;
  int sens_errors = 0;
  
  for(i=0; i < i_s->num_active_sensors; i++)
    {
      this_sensor_struc = &s_s_a[i];
      if (!(is_null(this_sensor_struc->name)))  
	{
	  ///////////////////////////////////////////////////////////////////////// change setpoint
	  if (this_sensor_struc->settable)    
	    {
	      read_mysql_sensor_data(this_sensor_struc->name, &this_sensor_struc->new_set_time, 
				     &this_sensor_struc->new_set_val, &rate_place_holder);
	      if (this_sensor_struc->new_set_time > this_sensor_struc->last_set_time)  
		{				
		  this_sensor_struc->last_set_time = this_sensor_struc->new_set_time;
		  
		  j = 0;
		  while ( (sens_errors = set_sensor(i_s, this_sensor_struc)) != 0 )
		    {
		      j++;
		      if (j > max_retries)
			{
			  sprintf(this_sys_message_struc.ip_address, " ");
			  sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
			  sprintf(this_sys_message_struc.msgs, "New setpoint: %s = %e could not be set.", 
				  this_sensor_struc->name , this_sensor_struc->new_set_val);
			  sprintf(this_sys_message_struc.type, "Setpoint");
			  this_sys_message_struc.is_error = 1;
			  insert_mysql_system_message(&this_sys_message_struc);
			  break;
			}
		      msleep(2000);
		    }
		  if (sens_errors == 0)
		    {
		      sprintf(this_sys_message_struc.ip_address, " ");
		      sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
		      sprintf(this_sys_message_struc.msgs, "New setpoint: %s = %e.", 
			      this_sensor_struc->name , this_sensor_struc->new_set_val);
		      sprintf(this_sys_message_struc.type, "Setpoint");
		      this_sys_message_struc.is_error = 0;
		      insert_mysql_system_message(&this_sys_message_struc);
		    }
		}
	    }
	  ////////////////////////////////////////////////////////////// read data and insert into db
	  else if (this_sensor_struc->data_type == SCALAR_DATA)            
	    {
	      j = 0;
	      while ( (sens_errors = read_sensor(i_s, this_sensor_struc, &sensor_value)) != 0 )
		{
		  j++;
		  if (j > max_retries)
		    {
		      sprintf(this_sys_message_struc.ip_address, " ");
		      sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
		      sprintf(this_sys_message_struc.msgs, "Sensor %s could not be read.", 
			      this_sensor_struc->name);
		      sprintf(this_sys_message_struc.type, "Error");
		      this_sys_message_struc.is_error = 0;
		      insert_mysql_system_message(&this_sys_message_struc);	
		      break;
		    }
		  msleep(2000);
		}
	      if (sens_errors == 0)
		{
		  add_val_sensor_struct(this_sensor_struc, time(NULL), sensor_value);
		  write_temporary_sensor_data(this_sensor_struc);
		  if (this_sensor_struc->next_update_time <= time(NULL))
		    {
		      avg_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      diff_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      insert_mysql_sensor_data(this_sensor_struc->name, time(NULL), 
					       this_sensor_struc->avg, this_sensor_struc->rate);
		      read_mysql_sensor_refresh_time(this_sensor_struc);
		      this_sensor_struc->last_update_time = time(NULL);
		      this_sensor_struc->next_update_time = time(NULL) + this_sensor_struc->update_period;
		    }
		}
	    }
	  //////////////////////////////////////////////////////////////  read and insert data only once every update time; don't do the averaging
	  else if (this_sensor_struc->data_type == DONT_AVERAGE_DATA)            
	    {
	      if (this_sensor_struc->next_update_time <= time(NULL))
		{
		  j = 0;
		  while ( (sens_errors = read_sensor(i_s, this_sensor_struc, &sensor_value)) != 0 )
		    {
		      j++;
		      if (j > max_retries)
			{
			  sprintf(this_sys_message_struc.ip_address, " ");
			  sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
			  sprintf(this_sys_message_struc.msgs, "Sensor %s could not be read.", 
				  this_sensor_struc->name);
			  sprintf(this_sys_message_struc.type, "Error");
			  this_sys_message_struc.is_error = 0;
			  insert_mysql_system_message(&this_sys_message_struc);	
			  break;
			}
		      msleep(1000);
		    }
		  if (sens_errors == 0)
		    {
		      add_val_sensor_struct(this_sensor_struc, time(NULL), sensor_value);
		      write_temporary_sensor_data(this_sensor_struc);
		      diff_vals_sensor_struct(this_sensor_struc, 0, 0);
		      insert_mysql_sensor_data(this_sensor_struc->name, time(NULL), 
					       sensor_value, this_sensor_struc->rate);
		      read_mysql_sensor_refresh_time(this_sensor_struc);
		      this_sensor_struc->last_update_time = time(NULL);
		      this_sensor_struc->next_update_time = time(NULL) + this_sensor_struc->update_period;
		    }
		}
	    }
	  //////////////////////////////////////////////////////////////// read data and let read_sensor() insert data
	  else if (this_sensor_struc->data_type == ARRAY_DATA)           
	    {
	      if (this_sensor_struc->next_update_time <= time(NULL))
		{
		  j = 0;
		  while ((sens_errors = read_sensor(i_s, this_sensor_struc, &sensor_value)) != 0)
		    {
		      j++;
		      if (j > max_retries)
			{
			  sprintf(this_sys_message_struc.ip_address, " ");
			  sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
			  sprintf(this_sys_message_struc.msgs, "Sensor %s could not be read.", 
				  this_sensor_struc->name);
			  sprintf(this_sys_message_struc.type, "Error");
			  this_sys_message_struc.is_error = 0;
			  insert_mysql_system_message(&this_sys_message_struc);	
			  break;
			}
		      msleep(500);
		    }
		  if (sens_errors == 0)
		    {
		      add_val_sensor_struct(this_sensor_struc, time(NULL), sensor_value);
		      write_temporary_sensor_data(this_sensor_struc);
		      avg_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      diff_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      read_mysql_sensor_refresh_time(this_sensor_struc);
		      this_sensor_struc->last_update_time = time(NULL);
		      this_sensor_struc->next_update_time = time(NULL) + this_sensor_struc->update_period;
		    }
		}
	      msleep(500);
	    }
	  //////////////////////////////////////////////////////////////// let read_sensor() take care of inserting and calculating slope
	  else if (this_sensor_struc->data_type == DONT_AVERAGE_DATA_OR_INSERT)           
	    {
	      if (this_sensor_struc->next_update_time <= time(NULL))
		{
		  j = 0;
		  while ((sens_errors = read_sensor(i_s, this_sensor_struc, &sensor_value)) != 0)
		    {
		      j++;
		      if (j > max_retries)
			{
			  sprintf(this_sys_message_struc.ip_address, " ");
			  sprintf(this_sys_message_struc.subsys, "%s", this_sensor_struc->type);
			  sprintf(this_sys_message_struc.msgs, "Sensor %s could not be read.", 
				  this_sensor_struc->name);
			  sprintf(this_sys_message_struc.type, "Error");
			  this_sys_message_struc.is_error = 0;
			  insert_mysql_system_message(&this_sys_message_struc);	
			  break;
			}
		      msleep(500);
		    }
		  if (sens_errors == 0)
		    {
		      //add_val_sensor_struct(this_sensor_struc, time(NULL), sensor_value);
		      //write_temporary_sensor_data(this_sensor_struc);
		      //avg_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      //diff_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      read_mysql_sensor_refresh_time(this_sensor_struc);
		      this_sensor_struc->last_update_time = time(NULL);
		      this_sensor_struc->next_update_time = time(NULL) + this_sensor_struc->update_period;
		    }
		}
	      msleep(500);
	    }
	  
	}
      msleep(100);
    }
}

int update_sensor_units(struct sensor_struct *s_s)
{
   char   query_strng[1024];  

   sprintf(query_strng, "UPDATE `sc_sensors` SET `units`=\"%s\", `discrete_vals`=\"%s\" WHERE `name`=\"%s\" " ,
	   s_s->units, s_s->discrete_vals, s_s->name);
   return(write_to_mysql(query_strng));
}

int update_mysql_sensor_refresh_time(struct sensor_struct *s_s)
{
  char   query_strng[1024];  
  
   sprintf(query_strng, "UPDATE `sc_sensors` SET `update_period`=\"%d\" WHERE `name`=\"%s\" " ,
	   s_s->update_period, s_s->name);
   return(write_to_mysql(query_strng));
}

int read_sens_struct_file(struct inst_struct *i_s, struct sensor_struct **s_s_a, char *sens_file_name)
{
    // If the DB is not available, generate_sensor_structs will
    // not work, use read_sens_struct_file to read the structure from
    // a pre-filled file
    
    FILE *f;
    struct sensor_struct *sensors_array;
    int  num_active_sensors;
    
    num_active_sensors = i_s->num_active_sensors;

    sensors_array = malloc(num_active_sensors * sizeof(struct sensor_struct));
    if (sensors_array == NULL)		
    {
	fprintf(stderr, "Malloc failed\n");
	return(1);
    }

    if ((f=fopen(sens_file_name, "r")) == NULL)
        return(1);
    
    if (fread(sensors_array, sizeof(struct sensor_struct), num_active_sensors, f) != num_active_sensors)
    {
	fclose(f);
	return(1);
    }
    
    fclose(f);

    *s_s_a = &sensors_array[0];

    mkdir(TEMP_DATA_DIR, S_IRWXU | S_IRWXG | S_IROTH | S_IXOTH);

    return(0);
}

int write_sens_struct_file(struct inst_struct *i_s, struct sensor_struct *s_s_a, char *sens_file_name)
{
    
    // If the DB is not available, generate_sensor_structs will
    // not work, use read_sens_struct_file to read the structure from
    // a pre-filled file made with write_sens_struct_file.
    
    FILE *f;
    int  num_active_sensors;
    
    num_active_sensors = i_s->num_active_sensors;

    if ((f=fopen(sens_file_name, "w")) == NULL)
        return(1);
    
    
    if (fwrite(s_s_a, sizeof(struct sensor_struct), num_active_sensors, f) != num_active_sensors)
    {
	fclose(f);
	return(1);
    }
    
    fclose(f);
    return(0); 
}



int generate_sensor_structs(struct inst_struct *i_s, struct sensor_struct **s_s_a)
{
    char   query_strng[1024];  
    char   res_strng[1024];
    char   sensor_name[18];
    struct sensor_struct *sensors_array;
    int    num_rows = 0;
    int    num_cols = 0;
    int    my_errors = 0;
    int    i; 

    sprintf(query_strng, 
	    "SELECT `name` FROM `sc_sensors` WHERE `instrument` = \"%s\" AND `hide_sensor` = 0 ORDER BY `num` ASC", 
	    i_s->name);
    my_errors += process_statement(query_strng, res_strng, &num_rows, &num_cols);
    
    sensors_array = malloc(num_rows * sizeof(struct sensor_struct));
    if (sensors_array == NULL)		
    {
	fprintf(stderr, "Malloc failed\n");
	return(1);
    }

    i_s->num_active_sensors = num_rows;
    
    for (i=0; i < num_rows; i++)
    {
	get_element(sensor_name, res_strng, num_rows, num_cols, i, 0);
	my_errors += read_mysql_sensor_struct(&sensors_array[i], sensor_name);
	init_sensor_struct(&sensors_array[i]);
    }
    *s_s_a = &sensors_array[0];
    
    return(my_errors);
}

int read_mysql_sensor_refresh_time(struct sensor_struct *s_s)
{
    /*   Reads the database to get the sensor refresh time. */
    /*   Returns 0 is all is well, 1 if there is an error.  */
    char   query_strng[1024];  
    
    sprintf(query_strng, "SELECT update_period FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", s_s->name);
    return(read_mysql_int(query_strng, &s_s->update_period));
}

int read_mysql_sensor_struct(struct sensor_struct *s_s, char *sensor_name)
{
    /*
      Opens the db, and reads in the values of the sensor, 'sensor_name' into 
      the structure 's_s'.  This is the structure is defined in "SC_db_interface.h".
     */
    char   query_strng[1024];  
    int    my_errors = 0;
    
    sprintf(query_strng, "SELECT name FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->name, sizeof(s_s->name));
    
    sprintf(query_strng, "SELECT description FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->description, sizeof(s_s->description));

    sprintf(query_strng, "SELECT type FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->type, sizeof(s_s->type));
    
    sprintf(query_strng, "SELECT subtype FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->subtype, sizeof(s_s->subtype));

    sprintf(query_strng, "SELECT num FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->num);

    sprintf(query_strng, "SELECT instrument FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->instrument, sizeof(s_s->instrument));

    sprintf(query_strng, "SELECT units FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->units, sizeof(s_s->instrument));
    
    sprintf(query_strng, "SELECT discrete_vals FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->discrete_vals, sizeof(s_s->discrete_vals));
    
    sprintf(query_strng, "SELECT al_set_val_low FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->al_set_val_low);
    sprintf(query_strng, "SELECT al_set_val_high FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->al_set_val_high);
    sprintf(query_strng, "SELECT al_set_rate_low FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->al_set_rate_low);
    sprintf(query_strng, "SELECT al_set_rate_high FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->al_set_rate_high);
    
    sprintf(query_strng, "SELECT al_arm_val_low FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->al_arm_val_low);
    sprintf(query_strng, "SELECT al_arm_val_high FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->al_arm_val_high);
    sprintf(query_strng, "SELECT al_arm_rate_low FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->al_arm_rate_low);
    sprintf(query_strng, "SELECT al_arm_rate_high FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->al_arm_rate_high);
    
    sprintf(query_strng, "SELECT alarm_tripped FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->alarm_tripped);
    sprintf(query_strng, "SELECT grace FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->grace);
    sprintf(query_strng, "SELECT last_trip FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->last_trip);

    sprintf(query_strng, "SELECT settable FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->settable);    
    
    sprintf(query_strng, "SELECT show_rate FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->show_rate);    

    sprintf(query_strng, "SELECT hide_sensor FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->hide_sensor);    

    sprintf(query_strng, "SELECT update_period FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_int(query_strng, &s_s->update_period);

    sprintf(query_strng, "SELECT user1 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->user1, sizeof(s_s->user1));
    sprintf(query_strng, "SELECT user2 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->user2, sizeof(s_s->user2));
    sprintf(query_strng, "SELECT user3 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->user3, sizeof(s_s->user3));
    sprintf(query_strng, "SELECT user4 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_string(query_strng, s_s->user4, sizeof(s_s->user4));

    sprintf(query_strng, "SELECT parm1 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->parm1);
    sprintf(query_strng, "SELECT parm2 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->parm2);
    sprintf(query_strng, "SELECT parm3 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->parm3);
    sprintf(query_strng, "SELECT parm4 FROM `sc_sensors` WHERE `name` = \"%s\" LIMIT 1", sensor_name);
    my_errors += read_mysql_double(query_strng, &s_s->parm4);

    return(my_errors);    
}
