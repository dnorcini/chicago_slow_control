/* This is a library for interfacing with the MySQL database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2009, 2010 */
/* James Public Licence. */

#include "SC_db_interface_raw.h"

void print_my_error (MYSQL *conn, char *message)
{
  // Standard Mysql error printing mechanism.
  print_error(message);
  if (conn != NULL)
    fprintf(stderr, "  Error %u (%s): %s\n", mysql_errno (conn), mysql_sqlstate(conn), mysql_error (conn));
}

int start_mysql_conn(void)
{
  //returns 0 on success, 1 on failure
  int ret_val=0;
  // initialize connection handler
  conn = mysql_init(NULL);
  if (conn == NULL)
    {
      print_my_error(NULL, "mysql_init() failed (probably out of memory)");
      ret_val=1;
    }
  
  if (mysql_options(conn, MYSQL_READ_DEFAULT_FILE, db_conf_file) != 0)
    {
      print_my_error(conn, "mysql_options() failed");
      mysql_close(conn);
      ret_val=1;
    }
  
  // connect to server 
  if (mysql_real_connect(conn, opt_host_name, opt_user_name, opt_password,
			 opt_db_name, opt_port_num, opt_socket_name, opt_flags) == NULL)
    {
      print_my_error(conn, "mysql_real_connect() failed");
      mysql_close(conn);
      ret_val=1;
    }
  return(ret_val);
}

int process_result_set(MYSQL_RES *res_set, char *result_string, int *num_rows, int *num_cols)
{
  // Returns a comma separated list of the row, column matching the processed statement
  int          ret_val = 0;
  MYSQL_ROW    row;
  char         res_temp[1024];
  unsigned int i;
  
  *num_rows = 0;
  *num_cols = 0;
  
  result_string[0] = 0;
  while ((row = mysql_fetch_row(res_set)) != NULL)
    {
      sprintf(res_temp, "%s , ", row[0] != NULL ? row[0] : "NULL");
      strcat(result_string, res_temp);
      *num_rows +=1;
      *num_cols =1;
      for (i = 1; i < mysql_num_fields (res_set); i++)
	{
	  sprintf(res_temp, "%s , ", row[i] != NULL ? row[i] : "NULL");
	  strcat(result_string, res_temp);
	  *num_cols +=1;
	}
    }
  
  if (mysql_errno(conn) != 0)
    {
      print_my_error(conn, "mysql_fetch_row() failed");
      ret_val = 1;
    }
  return(ret_val);
}

int process_statement(char *stmt_str, char *result_string, int *num_rows, int *num_cols)
{
  // Processes stmt_str from mysql db, and put into result_string. 
  // Returns 0 is all is well, 1 if there is an error.
  int ret_val = 0;
  MYSQL_RES *res_set;
  
  if( start_mysql_conn() == 1 ) //Connect Failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      // process rows and then free the result set 
      ret_val = process_result_set(res_set, result_string, num_rows, num_cols);
      mysql_free_result(res_set);
    }
  else  // no result set was returned.  This is normal for an insert, for example.
    {
      if (mysql_field_count(conn) != 0)  
	{
	  print_my_error(conn, "Could not retrieve result set");
	  mysql_close(conn);
	  return(1);
	}
    }
  mysql_close(conn);
  return(ret_val);
}

int write_to_mysql(char *stmt_str)
{
  // Processes stmt_str into mysql db and errors out if there are any results
  // Returns 0 is all is well, 1 if there is an error. 
  int ret_val = 0;
  MYSQL_RES *res_set;
  
  if( start_mysql_conn() == 1 )   // connect failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      print_my_error(conn, "Got unexpected results set!");
      mysql_close(conn);
      return(1);
    }
  else    // no result set was returned.  This is normal for an insert, for example.
    {
      if (mysql_field_count(conn) != 0)  
	{
	  print_my_error(conn, "Could not retrieve result set");
	  mysql_close(conn);
	  return(1);
	}
    }
  mysql_close(conn);
  return(ret_val);
}

int read_mysql_string(char *stmt_str, char *result_string, size_t size)
{
  // Processes stmt_str from mysql db, and put into result_string.
  // Returns 0 is all is well, 1 if there is an error.
  int ret_val = 0;
  MYSQL_RES *res_set;
  MYSQL_ROW    row;
  
  if( start_mysql_conn() == 1 )  // connect Failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      // process set and write to string
      while ((row = mysql_fetch_row(res_set)) != NULL)
	{
	  snprintf(result_string, size, "%s", row[0]);
	}
      mysql_free_result(res_set);
    }
  else   // stmt_str should be written to always return something so error out if no result
    {
      mysql_close(conn);
      return(1);
    }
  mysql_close(conn);
  return(ret_val);
}

int read_mysql_int(char *stmt_str, int *result_int)
{
  // Processes stmt_str from mysql db, and put into result_string. 
  // Returns 0 is all is well, 1 if there is an error. 
  int ret_val = 0;
  MYSQL_RES *res_set;
  MYSQL_ROW    row;
  
  if( start_mysql_conn() == 1 ) // connect failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      // process set and write to int
      while ((row = mysql_fetch_row(res_set)) != NULL)
	{
	  sscanf(row[0], "%d",  result_int);
	}
      mysql_free_result(res_set);
    }
  else   // stmt_str should be written to always return something so error out if no result
    {
      mysql_close(conn);
      return(1);
    }
  mysql_close(conn);
  return(ret_val);
}

int read_mysql_time(char *stmt_str, time_t *result_time)
{
  // Processes stmt_str from mysql db, and put into result_string. 
  // Returns 0 is all is well, 1 if there is an error. 
  int ret_val = 0;
  MYSQL_RES *res_set;
  MYSQL_ROW    row;
  
  if( start_mysql_conn() == 1 ) // connect failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      // process set and write to int
      while ((row = mysql_fetch_row(res_set)) != NULL)
	{
	  sscanf(row[0], "%lu",  result_time);
	}
      mysql_free_result(res_set);
    }
  else   // stmt_str should be written to always return something so error out if no result
    {
      mysql_close(conn);
      return(1);
    }
  mysql_close(conn);
  return(ret_val);
}




int read_mysql_int_array(char *stmt_str, int result_int_array[], int *array_count)
{
  // Processes stmt_str from mysql db, and put into result_int_array. 
  // Returns 0 is all is well, 1 if there is an error. 
  int        ret_val = 0;
  MYSQL_RES  *res_set;
  MYSQL_ROW  row;
  int        i;

  if( start_mysql_conn() == 1 ) // connect failed
    return 1;
 
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  res_set = mysql_store_result(conn);
    
  *array_count = 0;
  if (res_set)            
    {
      *array_count = mysql_num_rows(res_set);
      if (*array_count > 64)
	*array_count = 64;
      //result_int_array = malloc(*array_count * sizeof(int));  
      if (result_int_array == NULL)
	{
	  fprintf(stderr, "Malloc failed\n");
	  return(1);
	}
      for (i = 0; i < *array_count; i++)
	{
	  if ((row = mysql_fetch_row(res_set)) != NULL)
	      sscanf(row[0], "%d",  &result_int_array[i]);
	  else 
	    result_int_array[i] = 0;
	}
      mysql_free_result(res_set);
    }

  mysql_close(conn);
  return(ret_val);
}

int read_mysql_double(char *stmt_str, double *result_double)
{
  // Processes stmt_str from mysql db, and put into result_string.
  // Returns 0 is all is well, 1 if there is an error.
  int ret_val = 0;
  MYSQL_RES *res_set;
  MYSQL_ROW    row;
  
  if( start_mysql_conn() == 1 ) // connect failed
    return 1;
  
  if (mysql_query(conn, stmt_str) != 0)   // the statement failed 
    {
      print_my_error(conn, "Could not execute statement");
      mysql_close(conn);
      return(1);
    }
  
  res_set = mysql_store_result(conn);
  if (res_set)            
    {
      // process set and write to int
      while ((row = mysql_fetch_row(res_set)) != NULL)
	{
	  sscanf(row[0], "%lf", result_double);
	}
      mysql_free_result(res_set);
    }
  else   // stmt_str should be written to always return something so error out if no result
    {
      mysql_close(conn);
      return(1);
    }
  mysql_close(conn);
  return(ret_val);
}

int insert_mysql_sensor_data (char *sensor_name, time_t t_in, double v_in, double r_in)
{
  //  Inserts sensor data into the database.
  //  Returns 0 is all is well, 1 if there is an error.
  char query_strng[512];  
  
  sprintf(query_strng, "INSERT INTO `sc_sens_%s` SET `time` = %lu, `value` = %e, `rate` = %e", sensor_name, (unsigned long)t_in, v_in, r_in);        
  return(write_to_mysql(query_strng));
}

int insert_mysql_sensor_array_data (char *sensor_name, time_t t_in, double v_in, double r_in, double x0, double x1, int dxN, double y0, char *y)
{
  //  Inserts sensor data into the database.
  //  Returns 0 is all is well, 1 if there is an error.
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = malloc((strlen(sensor_name)+strlen(y)+1024) * sizeof(char));  
  if (query_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  sprintf(query_strng, "INSERT INTO `sc_sens_%s` SET `time` = %lu, `value` = %e, `rate` = %e, `x0` = %e, `x1` = %e, `dxN` = %d, `y0` = %e, `y` = \"%s\"", 
	  sensor_name, (unsigned long)t_in, v_in, r_in, x0, x1, dxN, y0, y);
  ret_val += write_to_mysql(query_strng);
  
  free(query_strng);
  
  return(ret_val);
}

int read_mysql_sensor_data (char *sensor_name, time_t *t_out, double *v_out, double *r_out)
{
  // Reads sensor data from the database.
  // Returns 0 is all is well, 1 if there is an error. 
  char  query_strng[512]; 
  char  res_strng[512];
  int num_rows = 0;
  int num_cols = 0;

  sprintf(query_strng, "SELECT time, value, rate FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", sensor_name);
  if(process_statement(query_strng, res_strng, &num_rows, &num_cols))
      return(1);
  
  if (sscanf(res_strng, "%lu , %le , %le", t_out, v_out, r_out) == 3)
      return(0);
  return(1);
}


int insert_mysql_system_message(struct sys_message_struct *sm_s)
{
  // Inserts message  into the database.
  // Returns 0 is all is well, 1 if there is an error.
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = malloc((sizeof(struct sys_message_struct)+256)  * sizeof(char));  
  
  if (query_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  sprintf(query_strng, 
	  "INSERT INTO `msg_log` ( `time`, `ip_address`, `subsys`, `msgs`, `type`, `is_error`) VALUES ( %lu, \"%s\", \"%s\", \"%s\", \"%s\", %d)",
	  (unsigned long)time(NULL), sm_s->ip_address, sm_s->subsys, sm_s->msgs, sm_s->type, sm_s->is_error);
  ret_val += write_to_mysql(query_strng);
  
  free(query_strng);
  
  return(ret_val);
}

int read_mysql_system_message(struct sys_message_struct *sm_s, int mesg_id)
{
  // Reads system message from the database.
  // Returns 0 is all is well, 1 if there is an error.
  
  int  ret_val = 0;  
  char query_strng[1024];  

  sprintf(query_strng, "SELECT `time` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_int(query_strng, &sm_s->time);

  sprintf(query_strng, "SELECT `ip_address` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_string(query_strng, sm_s->ip_address, sizeof(sm_s->ip_address));
  
  sprintf(query_strng, "SELECT `ip_address` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_string(query_strng, sm_s->subsys, sizeof(sm_s->subsys));
  
  sprintf(query_strng, "SELECT `msgs` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_string(query_strng, sm_s->msgs, sizeof(sm_s->msgs));

  sprintf(query_strng, "SELECT `type` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_string(query_strng, sm_s->type, sizeof(sm_s->type));

  sprintf(query_strng, "SELECT `is_error` FROM `msg_log` WHERE `msg_id` = %d LIMIT 1", mesg_id);
  ret_val += read_mysql_int(query_strng, &sm_s->is_error);

  return(ret_val);
}

int get_element(char *element, char *res_string, int num_rows, int num_cols, int row_i, int col_j)
{
  //   Gets the individual element from process_statement at row i, and column j
  
  int i; 
  int n;
  int n_ij;
  char *format_strng;
  int ret_val;
  
  n = num_rows * num_cols;
  n_ij = num_cols*row_i + col_j;
  
  if ((row_i > num_rows) || (col_j > num_cols))
    {
      fprintf(stderr,"i and j are not contained in the array");
      return(1);
    }
  
  format_strng = malloc(n * 10 * sizeof(char));  
  if (format_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  format_strng[0] = 0;
  for (i=0; i<n; i++)
    {
      if (i == n_ij)
	strcat(format_strng, "%[^,] , ");
      else
	strcat(format_strng, "%*[^,] , ");
    }
  ret_val = sscanf(res_string, format_strng, element);
  free(format_strng);
  
  if (ret_val !=1)
    element[0] = 0;
  
  return(0);
}



int read_mysql_global_var(struct global_struct *g_s, char *global_name)
{
  // Opens the db, and reads in the values of the globals into 
  // the structure 'g_s'.  This  structure is defined in "SC_db_interface.h".
  
  char   query_strng[1024];  
  int    my_errors = 0;
  
  sprintf(query_strng, "SELECT `name` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_string(query_strng, g_s->name, sizeof(g_s->name));
  
  sprintf(query_strng, "SELECT `int1` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_int(query_strng, &g_s->int1);
  sprintf(query_strng, "SELECT `int2` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_int(query_strng, &g_s->int2);
  sprintf(query_strng, "SELECT `int3` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_int(query_strng, &g_s->int3);
  sprintf(query_strng, "SELECT `int4` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_int(query_strng, &g_s->int4);
  
  sprintf(query_strng, "SELECT `double1` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_double(query_strng, &g_s->double1);
  sprintf(query_strng, "SELECT `double2` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_double(query_strng, &g_s->double2);
  sprintf(query_strng, "SELECT `double3` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_double(query_strng, &g_s->double3);
  sprintf(query_strng, "SELECT `double4` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_double(query_strng, &g_s->double4);
  
  sprintf(query_strng, "SELECT `string1` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_string(query_strng, g_s->string1, sizeof(g_s->string1));
  sprintf(query_strng, "SELECT `string2` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_string(query_strng, g_s->string2, sizeof(g_s->string2));
  sprintf(query_strng, "SELECT `string3` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_string(query_strng, g_s->string3, sizeof(g_s->string3));
  sprintf(query_strng, "SELECT `string4` FROM `globals` WHERE `name` = \"%s\" LIMIT 1", global_name);
  my_errors += read_mysql_string(query_strng, g_s->string4, sizeof(g_s->string4));
  
  return(my_errors);
}

int set_mysql_global_var(struct global_struct *g_s)
{
  char   query_strng[1024];
  
  sprintf(query_strng, "UPDATE `globals` SET `int1`=%d, `int2`=%d, `int3`=%d, `int4`=%d, `double1`=%lf, `double2`=%lf, `double3`=%lf, `double4`=%lf, `string1`=\"%s\", `string2`=\"%s\",`string3`=\"%s\", `string4`=\"%s\" WHERE `name` = \"%s\" ", 
	  g_s->int1, g_s->int2, g_s->int3, g_s->int4,
	  g_s->double1, g_s->double2, g_s->double3, g_s->double4,
	  g_s->string1, g_s->string2, g_s->string3, g_s->string4,
	  g_s->name); 
  
  return(write_to_mysql(query_strng));
}
