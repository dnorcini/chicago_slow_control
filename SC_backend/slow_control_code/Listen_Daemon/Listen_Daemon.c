/* Listens on a port and takes data from connecting devices and dumps them to */
/* the DB. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2016 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"
 
#define INSTNAME "Listen_Daemon"


#ifndef _def_set_up_inst
// Generic set up placeholder: to be found in instrument specific code
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{return(0);}
#endif // set_up_inst

#ifndef  _def_clean_up_inst
// Generic clean up placeholder: to be found in instrument specific code
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{;}
#endif // clean_up_inst

#ifndef _def_read_sensor
// Generic read placeholder: to be found in instrument specific code
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *sensor_value) 
{return(0);}
#endif // read_sensor

#ifndef _def_set_sensor
// Generic set placeholder: to be found in instrument specific code
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s) 
{return(0);}
#endif // set_sensor


int wait_for_connect(int fd, int *newsockfd, char *ret_string, size_t r_count)
{
  ssize_t rdstatus;   
  int     select_ret;
  fd_set  rfds;
  struct timeval tv;
  socklen_t clilen;
  struct sockaddr_in cli_addr;

  bzero(ret_string, r_count);

  FD_ZERO(&rfds);
  FD_SET(fd, &rfds);
  tv.tv_sec = 10;
  tv.tv_usec = 0;
  
  select_ret = select(fd+1, &rfds, NULL, NULL, &tv);
  if  (select_ret == -1)
    return(-1);					  			       
  
  if (select_ret == 0) // Timeout
      return(1);	
     
  while (select_ret) 
    {
      *newsockfd = accept(fd, (struct sockaddr *) &cli_addr, &clilen);
      if (*newsockfd < 0)
	{
	  error("ERROR on accept");
	  return(-1);
	}

      msleep(50);
      rdstatus = recv(*newsockfd, ret_string, r_count, MSG_DONTWAIT);
      if (rdstatus == 0) 
	{
	  fprintf(stderr, "Connection closed.\n");
	  return(-1);
	}
      else if (rdstatus < 0) 
	{
	  fprintf(stderr, "Socket failure.\n");
	  return(-1);
	}
      select_ret = 0;
    }

  //fprintf(stdout, "Ret string:\n");
  //fprintf(stdout, ret_string);
  if (rdstatus > 0)
    return(0);
  
  if (rdstatus == 0) 
    fprintf(stderr, "Connection closed\n");
  
  return(-1);
}


int main (int argc, char *argv[])
{
  char                   **my_argv;
  char                   inst_name[16];
  int                    i;
  struct inst_struct     this_inst;
  struct sensor_struct   *all_sensors;

  int sockfd, newsockfd, portno;
  char buffer[256];
  struct sockaddr_in serv_addr;
  int n;
     
  char       cmd_string[64];
  char       ret_string[64];
  char       rw_request[8];
  char       sensor_name[16];
  double     sensor_value;
  double     sensor_rate;
  time_t     sensor_time;
  int        read_status;

  // save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++) 
    my_argv[i] = strdup(argv[i]);
   
  my_argv[i] = NULL;   
  
  sprintf(db_conf_file, DEF_DB_CONF_FILE);

  parse_CL_for_string(argc,  argv, INSTNAME, inst_name);
  read_mysql_inst_struct(&this_inst, inst_name);
  
  // detach current process
  daemonize(this_inst.name);

  my_signal = 0;
  // ignore these signals 
  signal(SIGINT, SIG_IGN);
  signal(SIGQUIT, SIG_IGN);    
  // install signal handler
  signal(SIGHUP, handler);
  signal(SIGINT, handler);
  signal(SIGQUIT, handler);
  signal(SIGTERM, handler);

  sscanf(this_inst.user1, "%d", &portno);

  sockfd = socket(AF_INET, SOCK_STREAM, 0);
  if (sockfd < 0) 
    {
      fprintf(stderr, "ERROR opening socket\n");
      exit(1);
    }
  bzero((char *) &serv_addr, sizeof(serv_addr));
  serv_addr.sin_family = AF_INET;
  serv_addr.sin_addr.s_addr = INADDR_ANY;
  serv_addr.sin_port = htons(portno);
  if (bind(sockfd, (struct sockaddr *) &serv_addr, sizeof(serv_addr)) < 0) 
    {
      fprintf(stderr, "ERROR on bind\n");
      exit(1);
    }
  
  listen(sockfd, 5);
  
  register_inst(&this_inst);

  if (set_up_inst(&this_inst, all_sensors))
    {
      msleep(1000);
      my_signal = SIGHUP;
    }
  mysql_inst_run_status(&this_inst);
  
  while (my_signal == 0)     //  main loop here!
    {
      if (wait_for_connect(sockfd, &newsockfd,  ret_string,  sizeof(ret_string)/sizeof(char)) == 0)
	{
	  if (sscanf(ret_string, "%s %s = %lf", rw_request, sensor_name, &sensor_value) == 3)
	    {
	      if (strncasecmp(rw_request, "write", 2) == 0)
		{
		  if (insert_mysql_sensor_data (sensor_name, time(NULL), sensor_value, 0.0) == 0)
		    write(newsockfd,":)", 2); 
		  else
		    write(newsockfd,":(", 2); 
		}
	      else if (strncasecmp(rw_request, "read", 2) == 0)
		{
		  if (read_mysql_sensor_data(sensor_name, &sensor_time, &sensor_value, &sensor_rate) == 0)
		    {
		      sprintf(cmd_string, "val = %lf | t = %d", sensor_value, sensor_time);
		      write(newsockfd, cmd_string, strlen(cmd_string)); 
		    }
		  else
		    write(newsockfd,":(", 2); 
		}
	      else
		write(newsockfd,":(", 2); 
	    }
	  else if (sscanf(ret_string, "%s %s", rw_request, sensor_name) == 2)
	    {
	      if (strncasecmp(rw_request, "read", 2) == 0)
		{
		  if (read_mysql_sensor_data(sensor_name, &sensor_time, &sensor_value, &sensor_rate) == 0)
		    {
		      sprintf(cmd_string, "val = %lf | t = %d", sensor_value, sensor_time);
		      write(newsockfd, cmd_string, strlen(cmd_string)); 
		    }
		  else
		    write(newsockfd,":(", 2);  
		}
	      else
		write(newsockfd,":(", 2);  
	    }
	  else
	    write(newsockfd,":(", 2); 
	  
	  close(newsockfd);    
	}
      mysql_inst_run_status(&this_inst);
    }
    
  /////////////  Clean up if we get a signal
  clean_up_inst(&this_inst, all_sensors);

  unregister_inst(&this_inst);
  free(all_sensors);

  if (my_signal == SIGHUP)         ///  restart called
    {
      long fd;
      // close all files before restart
      for (fd = sysconf(_SC_OPEN_MAX); fd > 2; fd--) 
	{
	  int flag;
	  if ((flag = fcntl(fd, F_GETFD, 0)) != -1)
	    fcntl(fd, F_SETFD, flag | FD_CLOEXEC);
	}
      sleep(2);
      execv(my_argv[0], my_argv);
      fprintf(stderr, "execv() failed.");
      exit(1);
    }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);
    
  free(my_argv);

  exit(0);
}


