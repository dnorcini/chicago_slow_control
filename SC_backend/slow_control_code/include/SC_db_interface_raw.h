/* This is the header file for a more raw interfacing with the MySQL database. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006 */
/* James public licence. */
#ifndef _SC_db_raw_H_
#define _SC_db_raw_H_

#include "SC_db_interface.h"

int process_result_set(MYSQL_RES *res_set, char *result_string, int *num_rows, int *num_cols);

int process_statement(char *stmt_str, char *result_string, int *num_rows, int *num_cols);

int get_element(char *element, char *res_string, int num_rows, int num_cols, int row_i, int col_j);

int read_mysql_string(char *stmt_str, char *result_string, size_t size);

int read_mysql_int(char *stmt_str, int *result_int);

int read_mysql_time(char *stmt_str, time_t *result_time);

int read_mysql_int_array(char *stmt_str, int *result_int_array, int *array_count);

int read_mysql_double(char *stmt_str, double *result_double);

int write_to_mysql(char *stmt_str);

#endif
