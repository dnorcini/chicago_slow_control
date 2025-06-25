
#include <stdlib.h>
#include <stdio.h>

#include <unistd.h>

#include <sys/select.h>
#include <sys/types.h>
#include <sys/socket.h>

#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/ip.h>
#include <netinet/tcp.h>

#include <errno.h>

#include <string.h>
#include <strings.h>

int inst_dev_1;
int inst_dev_2;

static char CR   __attribute__ ((unused)) = 0x0D;
static char LF   __attribute__ ((unused)) = 0x0A;

void msleep(double sleep_time)
{
  usleep((int)(sleep_time*1e3));
}

int connect_tcp_raw(char *IP_address, int port)
{
  int fd;
  int option;
  struct sockaddr_in addr;
    
  addr.sin_family = AF_INET;
  addr.sin_port = htons(port);
  addr.sin_addr.s_addr = inet_addr(IP_address);
  
  if ((fd = socket(AF_INET, SOCK_STREAM, 0)) < 0)
    {
      fprintf(stderr, "Could not make socket: %d \n", fd);
      exit(1);
    }
  
    // Set the TCP no delay flag
    // SOL_TCP = IPPROTO_TCP 
  option = 1;
  if (setsockopt(fd, IPPROTO_TCP, TCP_NODELAY, (const void *)&option, sizeof(int)) < 0)
    {
      fprintf(stderr, "Could not set option: %d \n", option);
      close(fd);
      exit(1);
    }
  
  // Set the IP low delay option
  option = IPTOS_LOWDELAY;
  if (setsockopt(fd, IPPROTO_TCP, IP_TOS, (const void *)&option, sizeof(int)) < 0)
    {
      fprintf(stderr, "Could not set option: %d \n", option);
      close(fd);
      exit(1);
    }
  
  if (connect(fd, (struct sockaddr *)&addr, sizeof(struct sockaddr_in)) < 0 )
    {
      fprintf(stderr, "Could not make connection to: %s:%d \n", IP_address, port);
      close(fd);
      exit(1);
    }
  return(fd);
}


int query_tcp(int fd, char *cmd_string, size_t c_count, char *ret_string, size_t r_count)
{
  size_t rdstatus;   
  int     select_ret;
  fd_set  rfds;
  struct timeval tv;
  
  bzero(ret_string, r_count);

  if (send(fd, cmd_string, c_count, 0) < 0)
    {
      fprintf(stderr, "send error in query_tcp\n");
      return(1);
    }
  
  FD_ZERO(&rfds);
  FD_SET(fd, &rfds);
  tv.tv_sec = 5;
  tv.tv_usec = 0;
  
  select_ret = 0;
  while ((select_ret = select(fd+1, &rfds, NULL, NULL, &tv)) == -1)
    { 
      if (errno == EINTR) 
	{					
	  fprintf(stderr, "A non blocked signal was caught.\n");		
	  FD_ZERO(&rfds);						
	  FD_SET(fd, &rfds);				
	} 
      else 
	{							
	  fprintf(stderr, "Select failure.\n"); 
	  return(1);					
	}								
    }								
  
  if (select_ret == 0) // Timeout
    {
      fprintf(stderr, "Timeout in query_tcp.\n");
      return(1);	
    }
  
  while (select_ret) 
    {
      rdstatus = recv(fd, ret_string, r_count, MSG_DONTWAIT);
      if (rdstatus == 0) 
	{
	  fprintf(stderr, "Connection closed.\n");
	  return(1);
	}
      else if (rdstatus < 0) 
	{
	  fprintf(stderr, "Socket failure.\n");
	  return(1);
	}
      select_ret = 0;
    }
  
  if (rdstatus > 0)
    return(0);
  
  if (rdstatus == 0) 
    fprintf(stderr, "Connection closed\n");
  
  return(1);
}



int write_tcp(int fd, char *cmd_string, size_t c_count)
{
  if (send(fd, cmd_string, c_count, 0) < 0)
    {
      fprintf(stderr, "send error in query_tcp\n");
      return(1);
    }
  
  return(0);
}



void set_up(void)
{
  if ((inst_dev_1 = connect_tcp_raw("192.168.1.5", 64000)) < 0)
    {
      printf("Connect 1 failed. \n");
      exit(1);
    }     
  
  if ((inst_dev_2 = connect_tcp_raw("192.168.1.97", 5000)) < 0)
    {
      printf("Connect 2 failed. \n");
      exit(1);
    }
}


void clean_up(void)
{
   close(inst_dev_1);
   close(inst_dev_2);
   exit(1);
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
      return(-1);
    }

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
      return(-1);
    }
  msleep(50);
	    
  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if(sscanf(ret_string, "%d", &return_int_2) != 1)
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read sensor!\n", ret_string);
      return(-1);
    }

  if (return_int_1 !=return_int_2)
    return(-1);

  if (return_int_1 < 0)
    return(-1);
  
  *x_val = (double)return_int_1/100.0;
  
  return(0);
}

void home_x(void)
{
  char cmd_string[64];
  sprintf(cmd_string, "%d H 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}

void halt_x(void)
{
  char cmd_string[64];
  sprintf(cmd_string, "%d X 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}


void goto_x(double target_x)
{
  char cmd_string[64]; 
  sprintf(cmd_string, "%d G %d\n", 2, (int)(10*target_x));
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}

void set_speed_x(int speed)
{
  int sleep_time;
  char cmd_string[64];
 
  if (speed <1)
    speed = 1;
  if (speed > 10)
    speed = 10;
  
  sleep_time = (int)1000/speed;
  
  sprintf(cmd_string, "%d S %d\n", 2, sleep_time);
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
      return(-1);
    }

  msleep(5);
  
  query_tcp(inst_dev_2, cmd_string, strlen(cmd_string), ret_string, sizeof(ret_string)/sizeof(char));
  if (sscanf(ret_string, "%ld", &counts_2) !=1) 
    {
      //fprintf(stderr, "Bad return string: \"%s\" in read_counter.\n", ret_string);
      return(-1);
    }

  //if (counts_1 != counts_2)
  // return(-1);

  if (counts_2 < 0)
    return(-1);

  *counts = counts_2;
  return(0);
}

void reset_counter(void)
{
  char       cmd_string[64];
  sprintf(cmd_string, "%d I 0\n", 2);
  write_tcp(inst_dev_2, cmd_string, strlen(cmd_string));
}

void scan(double X1, double X2)
{
  double x_val, y_val;
  double current_x = -1;
  double target_x;
  long   counts = 0;
  long   prev_counts = -1;
  int i;

  goto_x(X1);

  while (read_x(&x_val) != 0)
    {
      msleep(100);
    }
  
  reset_counter();
  
  goto_x(X2);
  msleep(10);
  
  i = 0;
  while (i<10)
    {
      if (read_counter(&counts) == 0)
	{
	  if (counts == prev_counts)
	    i++;
	  prev_counts = counts; 
	  read_y(&y_val);
	  current_x = (double)counts * 0.000625 + X1;
	  fprintf(stdout, "%lf, %lf \n", current_x, y_val);
	  msleep(50);
	}
    }
}

int main (int argc, char *argv[])
{
  double XXX, X1, X2;
  double x_val, y_val;
  int    speed;
  long   counts;
  int    max_tries = 10;
  int    i = 0;
  
  if (argc > 1)
    {
      if ((strncasecmp(argv[1], "help", 4) == 0) || (strncasecmp(argv[1], "-h", 2) == 0) || (strncasecmp(argv[1], "--h", 3) == 0))
	{
	  fprintf(stdout, "Usage: %s home            to 'home' the drive.\n", argv[0]);
	  fprintf(stdout, "   or: %s halt            to halt motion. \n", argv[0]);
	  fprintf(stdout, "   or: %s read            to read out the x and y values. \n", argv[0]);
	  fprintf(stdout, "   or: %s speed XX        to set the speed to between 1 (slow) and 10 (fast). \n", argv[0]);
	  fprintf(stdout, "   or: %s goto  XXX       to move the sensor to XXX(cm) \n", argv[0]);
	  fprintf(stdout, "   or: %s scan  X1 X2     to scan from X1 to X2 (cm) \n", argv[0]);
	  exit(1);
	}
      else if (strncasecmp(argv[1], "home", 4) == 0)
	{
	  set_up();
	  home_x();
	  clean_up();
	}
       else if (strncasecmp(argv[1], "halt", 4) == 0)
	{
	  set_up();
	  halt_x();
	  clean_up();
	}
      else if (strncasecmp(argv[1], "read", 4) == 0)
	{
	  set_up();
	  i = 0;
	  while ((read_x(&x_val) == -1) && (i < max_tries))
	    {
	      msleep(20);
	      i++;
	    }
	  i = 0;
	  while ((read_y(&y_val) == -1) && (i < max_tries))
	    {
	      msleep(20);
	      i++;
	    }
	   i = 0;
	  while ((read_counter(&counts) == -1) && (i < max_tries))
	    {
	      msleep(20);
	      i++;
	    }
	  fprintf(stdout, "Current X position: %lf (cm).\n", x_val);
	  fprintf(stdout, "Current Y position: %lf (mm).\n", y_val);
	  fprintf(stdout, "Current counts:     %ld  .\n",    counts);
	  
	  clean_up();
	}
       else if ((strncasecmp(argv[1], "speed", 4) == 0) && (argc > 2))
	{
	  if (sscanf(argv[2], "%d", &speed) == 1)
	    {
	      set_up();
	      set_speed_x(speed);
	      clean_up();
	    }
	  else
	    {
	      fprintf(stdout, "Bad target value in 'goto'. \n");
	      exit(1);
	    }
	}
      else if ((strncasecmp(argv[1], "goto", 4) == 0) && (argc > 2))
	{
	  if (sscanf(argv[2], "%lf", &XXX) == 1)
	    {
	      set_up();
	      goto_x(XXX);
	      clean_up();
	    }
	  else
	    {
	      fprintf(stdout, "Bad target value in 'goto'. \n");
	      exit(1);
	    }
	}
      else if  ((strncasecmp(argv[1], "scan", 4) == 0) && (argc > 3))
	{
	  if ((sscanf(argv[2], "%lf", &X1) == 1) && (sscanf(argv[3], "%lf", &X2) == 1))
	    {
	      set_up();
	      scan(X1, X2);
	      clean_up();
	    }
	  else
	    {
	      fprintf(stdout, "Bad values in 'scan'. \n");
	      exit(1);
	    }
	}
      else
	{
	  fprintf(stdout, "Bad command.  Try --help \n");
	  exit(1);
	}
    }
}
