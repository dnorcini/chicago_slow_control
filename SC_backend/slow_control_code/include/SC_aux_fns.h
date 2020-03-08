/* This is a header file for general misc. fns. */
/* James Nikkel */
/* james.nikkel@gmail.com */
/* Copyright 2006, 2013 */
/* James public licence. */
//
// D.Norcini, KICP, 2020
// added <float.h> for DBL_MAX/MIX
#ifndef _SC_aux_H_
#define _SC_aux_H_

#include <math.h>
#include <stdlib.h>
#include <stdio.h>
#include <stdarg.h>
#include <string.h>
#include <unistd.h>
#include <fcntl.h>
#include <errno.h>
#include <signal.h>
#include <time.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <float.h>

//#define MIN(X,Y) ((X) < (Y) ?  (X) : (Y))
//#define MAX(X,Y) ((X) > (Y) ?  (X) : (Y))
#define ABS(X)	 (((X) < 0) ? -(X) : (X))
#define CLAMP(X, low, high)  (((X) > (high)) ? (high) : (((X) < (low)) ? (low) : (X)))

#define SC_STDOUT "/dev/shm/stdout"
#define SC_STDERR "/dev/shm/stderr"

int my_signal;

int closest_int(double dbl_in);

int STB_to_Array(int STB, int *STB_array);

///  Average array of doubles, X=average_array(Y, sizeof(Y)/sizeof(double), 1);
///  discard == 1 means that the high and low values in the average will be discarded
double average_array(double *array_in, int num, int discard);

void print_error(char *message);

int is_null(char str[]);

char int_to_letter(int n);

char int_to_Letter(int n);

double poly_interpolation (double x, double poly_parms[]);    // interpolates polynomial of given parms at x

double linear_interp(double x, double slope, double y0);

int max_int(int array_in[], int num);      // returns the largest integer in an array of ints
int min_int(int array_in[], int num);      // returns the smallest integer in an array of ints
double max_double(double array_in[], int num);
double min_double(double array_in[], int num);

int sum_int_array(int array_in[], int num);           // add up num elements from given array
double sum_dbl_array(double array_in[], int num);

void init_int_array(int array_in[], int num, int val);    // initialze num elements of given array to val
void init_dbl_array(double array_in[], int num, double val);


int find_int_in_array(int needle, int haystack[], int num);

void msleep(double sleep_time);            // sleeps for given number of milliseconds using nanosleep

void parse_CL_for_string(int argc, char *argv[], char *default_string, char *string); // Look for a single string from the command line

int send_mail_message(char *address, char *message);  /// send email to address containing message

int explode_to_double(char *str_in, char *delim, 
		      double* array_out, int num);
int explode_to_int(char *str_in, char *delim, 
		   int* array_out, int num);
int implode_from_double(char *str_out, int str_sz, char *delim, 
			double* array_in, int num, char *format);
int implode_from_int(char *str_out, int str_sz, char *delim, 
		     int* array_in, int num);
int implode_from_one_double(char *str_out, int str_sz, char *delim, 
			double double_in, int num, char *format);
int implode_from_one_int(char *str_out, int str_sz, char *delim, 
		     int int_in, int num);

void handler(int sent_signal);

void daemonize(char *stdio_file_name);

unsigned char Calculate_CRC8(unsigned char *cmd_string, char Length);

#endif
