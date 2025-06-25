/* This is a library for general misc. functions */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006, 2007 */
/* James public licence. */

#include "SC_aux_fns.h"

int closest_int(double dbl_in)
{
  // Finds the closest integer to a double and returns it.

  int sign = 1;
  if (dbl_in < 0)
    {
      dbl_in *= -1;
      sign = -1;
    }
  if ( (dbl_in - (int)dbl_in) < 0.5 )
    return(sign * (int)dbl_in);
  else
    return(sign * ((int)dbl_in + 1));
}


int STB_to_Array(int STB, int *STB_array)
{
  int i;
  if ((STB > 255) || (STB<0))
    return(1);
  for (i = 0; i < 8; i++)
    {
      STB_array[i] = (STB % 2);
      STB /= 2;
    }
  return(0);
}

double average_array(double *array_in, int num, int discard)
{
  double Min = array_in[0];
  double Max = array_in[0];
  double Avg = array_in[0];
  int i;
  
  for (i=1; i<num; i++)
    {
      Avg += array_in[i];
      if (array_in[i] > Max)
	Max = array_in[i];
      if (array_in[i] < Min)
	Min = array_in[i];
    }
  if ( (discard == 1) && (num > 2))
    {  
      Avg -= Max;
      Avg -= Min;
      Avg /= (num-2);
    }
  else 
    Avg /= num;
  return(Avg);
}

int sum_int_array(int *array_in, int num)
{
  int i;
  int sum_val = 0;
  for (i=0; i<num; i++)
    sum_val += array_in[i];
  return(sum_val);
}

double sum_dbl_array(double *array_in, int num)
{
  int i;
  double sum_val = 0;
  for (i=0; i<num; i++)
    sum_val += array_in[i];
  return(sum_val);
}

void init_int_array(int *array_in, int num, int val)
{
  int i;
  for (i=0; i<num; i++)
    array_in[i] = val;
}

void init_dbl_array(double *array_in, int num, double val)
{
  int i;
  for (i=0; i<num; i++)
    array_in[i] = val;
}


void print_error (char *message)
{
  time_t now; 
  time(&now);
  fprintf(stderr, "%s", ctime(&now));
  fprintf(stderr, "  %s\n", message); 
}


double linear_interp(double x, double slope, double y0)
{
  double y;
    
  y = x * slope + y0;
    
  return(y);
}


int max_int(int array_in[], int num)
{
  int i;
  int maxVal = array_in[0];

  for(i=0; i<num; ++i)
    {
      if (array_in[i]>maxVal) maxVal=array_in[i];
    }
  return(maxVal);
}

int min_int(int array_in[], int num)
{   
  int i;
  int minVal = array_in[0];

  for(i=0; i<num; ++i)
    {
      if (array_in[i]<minVal) minVal=array_in[i];
    }
  return(minVal);
}

double max_double(double array_in[], int num)
{
  int i;
  double maxVal = array_in[0];

  for(i=0; i<num; ++i)
    {
      if (array_in[i]>maxVal) maxVal=array_in[i];
    }
  return(maxVal);
}

double min_double(double array_in[], int num)
{   
  int i;
  double minVal = array_in[0];

  for(i=0; i<num; ++i)
    {
      if (array_in[i]<minVal) minVal=array_in[i];
    }
  return(minVal);
}

int find_int_in_array(int needle, int haystack[], int num)
{
  int found = 0;
  int i;
  for(i=0; i<num; ++i)
    {
      if (needle == haystack[i])
	found=1;
    }
  return(found);
}


void msleep(double sleep_time)
{
  usleep((int)(sleep_time*1e3));
}


char int_to_letter(int n)   // converts an integer to a, b, c, d, ...
{
  if ((n < 1) || (n > 26))
    fprintf(stderr, "int_to_letter(n), 1<=n<=26, you provided n=%d", n);
  char c = n+96;
  return(c);
}

char int_to_Letter(int n)   // converts an integer to A, B, C, D, ...
{
  if ((n < 1) || (n > 26))
    fprintf(stderr, "int_to_Letter(n), 1<=n<=26, you provided n=%d", n);
  char c = n+64;
  return(c);
}

int is_null(char str[])
{
  if (strncmp(str, "NULL", 4) == 0)
    return(1);    
  if (strncmp(str, "null", 4) == 0)
    return(1);    
  if (strcmp(str, "") == 0)
    return(1);    
  else
    return(0);
}

void parse_CL_for_string(int argc, char *argv[], char *default_string, char *string)
{ 
  // Parse the command line for the instrument program for the config file. 
    
  if (argc > 1)
    {
      if ((strncasecmp(argv[1], "help", 4) == 0) || (strncasecmp(argv[1], "-h", 2) == 0) || (strncasecmp(argv[1], "--h", 3) == 0))
	{
	  fprintf(stdout, "Usage: %s %s \n", argv[0], argv[0]);
	  fprintf(stdout, "   or: %s           (use default instrument entry: %s) \n", argv[0], string);
	  fprintf(stdout, "   or: %s --help (-h) for this help. \n", argv[0]);
	  exit(1);
	}
      else 
	{
	  sprintf(string, "%s", argv[1]);
	}
    }
  else
    {
      sprintf(string, "%s", default_string);
    }
}


int send_mail_message(char *address, char *message)
{
  int ret_val = 0;
    
  char message_file_name[64];
  char sys_command[256];
  int  message_file;    
  ssize_t w_status = 0;

  /*
  sys_command = malloc((strlen(message) + 128) * sizeof(char)); 
  if (sys_command == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  */

  sprintf(message_file_name , "/tmp/warning_message_file.txt");
  message_file = open(message_file_name, O_WRONLY|O_CREAT|O_TRUNC|O_APPEND,  S_IRWXU);  ///   Write the alarm message to a file 
  w_status += write(message_file, message, strlen(message));                    ///   as that appears to be the only way
  w_status += write(message_file, "\n", 1);                     
  close(message_file);  
    
  if (w_status < 0)
    fprintf(stderr, "Trouble writing message file in send_mail_message, w_status=%d\n", (int)w_status);
    

  sprintf(sys_command, "/bin/mail -s \"Slow control alarm\" %s < %s > /dev/null ", address,  message_file_name);
  if (system(sys_command) == -1)
    {
      fprintf(stderr, "Trouble sending with /bin/mail in send_mail_message\n");
      ret_val++;
    }

  sprintf(sys_command, "rm -f %s", message_file_name);               // delete message after sending 
  if (system(sys_command) == -1)
    {
      fprintf(stderr, "Trouble deleting message in send_mail_message\n");
      ret_val++;
    }

  //free(sys_command);
        
  return(ret_val + w_status);
} 

int explode_to_double(char *str_in, char *delim, double* array_out, int num)
{
  char* token;
  char* running = strdup(str_in);
  int   i = 0;
    
  token = strsep(&running, delim);
    
  while (token)
    {
      if (i<num)
	if (sscanf(token, "%lf", &array_out[i]) > 0)
	  i++;
      token = strsep(&running, delim);
    }
    
  free(running);
  if (i<num)
    return(1);
  return(0);
} 

int explode_to_int(char *str_in, char *delim, int* array_out, int num)
{
  char* token;
  char* running = strdup(str_in);
  int   i = 0;
    
  token = strsep(&running, delim);
    
  while (token)
    {
      if (i<num)
	if (sscanf(token, "%d", &array_out[i]) > 0)
	  i++;
      token = strsep(&running, delim);
    }
    
  free(running);
  if (i<num)
    return(1);
  return(0);
}
 
int implode_from_double(char *str_out, int str_sz, char *delim, 
			double* array_in, int num, char *format)
{
  int i;
  char temp[32];
  char format_str[32];
  sprintf(format_str, "%%s%s", format);
  sprintf(str_out, format, array_in[0]);
  for (i = 1; i<num; i++)
    {
      sprintf(temp, format_str, delim, array_in[i]);
      strncat(str_out, temp, str_sz-strlen(str_out)-1);
    }
  if (sizeof(str_out)-strlen(str_out) < 1)
    {
      fprintf(stderr, "Not all of array_in may have been written in implode.\n");
      return(1);
    }
  return(0);
}

int implode_from_int(char *str_out, int str_sz, char *delim, 
		     int* array_in, int num)
{
  int i;
  char temp[32];
  sprintf(str_out, "%d", array_in[0]);
  for (i = 1; i<num; i++)
    {
      sprintf(temp, "%s%d", delim, array_in[i]);
      strncat(str_out, temp, str_sz-strlen(str_out)-1);
    }
  if (sizeof(str_out)-strlen(str_out) < 1)
    {
      fprintf(stderr, "Not all of array_in may have been written in implode.\n");
      return(1);
    }
  return(0);
}


int implode_from_one_int(char *str_out, int str_sz, char *delim, 
			 int int_in, int num)
{
  int i;
  char temp[32];
  sprintf(str_out, "%d", int_in);
  for (i = 1; i<num; i++)
    {
      sprintf(temp, "%s%d", delim, int_in);
      strncat(str_out, temp, str_sz-strlen(str_out)-1);
    }
  if (sizeof(str_out)-strlen(str_out) < 1)
    {
      fprintf(stderr, "Not all wanted ints may have been written in implode.\n");
      return(1);
    }
  return(0);
}


int implode_from_one_double(char *str_out, int str_sz, char *delim, 
			    double double_in, int num, char *format)
{
  int i;
  char temp[32];
  char format_str[32];
  sprintf(format_str, "%%s%s", format);
  sprintf(str_out, format, double_in);
  for (i = 1; i<num; i++)
    {
      sprintf(temp, format_str, delim, double_in);
      strncat(str_out, temp, str_sz-strlen(str_out)-1);
    }
  if (sizeof(str_out)-strlen(str_out) < 1)
    {
      fprintf(stderr, "Not all wanted doubles  may have been written in implode.\n");
      return(1);
    }
  return(0);
}



//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////
////  Below here is partially lifted code


void handler(int sent_signal)
{
  my_signal = sent_signal;
}


void daemonize(char *stdio_file_name)
{
  // thanks to Petri Damsten, we now follow the guidelines from the UNIX Programming FAQ 
  // 1.7 How do I get my program to act like a daemon? 
  // http://www.erlenstar.demon.co.uk/unix/faq_2.html#SEC16 
  // especially the double-fork solved the 'lcd4linux dying when called from init' problem 
  //
  // This is lifted from lcd4linux.c 783 2007-03-22 Copyright (C) 1999, 2000, 2001, 2002, 2003 Michael Reinelt <reinelt@eunet.at> 

  pid_t i;
  char stdiopath[256];

  //FILE *f;

  //Step 1: fork() so that the parent can exit
  i = fork();
  if (i < 0) {
    fprintf(stderr, "fork(#1) failed in daemonize.\n");
    exit(1);
  }
  if (i != 0)
    exit(0);

  // Step 2: setsid() to become a process group and session group leader 
  setsid();

  // Step 3: fork() again so the parent (the session group leader) can exit 
  i = fork();
  if (i < 0) {
    fprintf(stderr, "fork(#2) failed in daemonize.\n");
    exit(1);
  }
  if (i != 0)
    exit(0);

  // Step 4: chdir("/") to ensure that our process doesn't keep any directory in use
  if (chdir("/") != 0) {
    fprintf(stderr, "chdir(\"/\") failed in daemonize.\n");
    exit(1);
  }

  // Step 5: umask(0) so that we have complete control over the permissions of anything we write
  umask(0);

  sprintf(stdiopath, "%s.%s", SC_STDOUT, stdio_file_name);
  stdout = freopen(stdiopath, "w", stdout);
  sprintf(stdiopath, "%s.%s", SC_STDERR, stdio_file_name);
  stderr = freopen(stdiopath, "a", stderr);

}



unsigned char Calculate_CRC8(unsigned char *cmd_string, char Length) 
{ 
  unsigned char CRC_Value; 
  unsigned char Counter; 
  unsigned char BitCounter; 
  unsigned char XOR_Byte; 
  unsigned char TransmitByte; 
    
  CRC_Value = 0xFF; 

  for(Counter = 0; Counter < Length; Counter++) 
    { 
      TransmitByte = *cmd_string; 
      cmd_string++; 
      BitCounter = 8; 
	
      while(BitCounter != 0) 
	{ 
	  BitCounter--; 
	  XOR_Byte = TransmitByte ^ CRC_Value; 
	    
	  if((XOR_Byte & 0x80) != 0) 
	    { 
	      CRC_Value ^= 0x0E; 
	      CRC_Value <<= 1; 
	      CRC_Value |= 1; 
	    } 
	  else 
	    { 
	      CRC_Value <<= 1; 
	    } 
	    
	  // Left shift the calculation byte. 
	  TransmitByte <<= 1; 
	} 
    } 
  return(CRC_Value); 
} 
