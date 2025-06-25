/* This is for any additional code for serial interface comms. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2010 */
/* James public licence. */

#include "serial.h"

int query_serial(int fd, char *cmd_string, size_t c_count, char *ret_string, size_t r_count)
{
    int i;

    for (i = 0; i < MAX_SERIAL_RETRIES; i++)
    {
      if (write(fd, cmd_string, c_count) < 0)
	{
	  fprintf(stderr, "To many retries in query_serial. \n");
	  return(1);
	}
      
	msleep(200);
	if (read(fd, ret_string, r_count) > 0)
	    return(0);
    }
    
    fprintf(stderr, "To many retries in query_serial. \n");
    return(1);
}

int write_serial(int fd, char *cmd_string, size_t c_count)
{
    int i;

    for (i = 0; i < MAX_SERIAL_RETRIES; i++)
    {
      if (write(fd, cmd_string, c_count) > 0)
	return(0);
    }
    
    fprintf(stderr, "To many retries in write_serial. \n");
    return(1);
}

int read_serial(int fd, char *ret_string, size_t r_count)
{
    int i;
    
    for (i = 0; i < MAX_SERIAL_RETRIES; i++)
      {
	if (read(fd, ret_string, r_count) > 0)
	  return(0);
      }
    
    fprintf(stderr, "To many retries in read_serial. \n");
    return(1);
}
