/* A simple server in the internet domain using TCP
   The port number is passed as an argument */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/select.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>

void error(const char *msg)
{
    perror(msg);
    exit(1);
}

int read_tcp(int fd, int *newsockfd, char *ret_string, size_t r_count)
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
  tv.tv_sec = 6;
  tv.tv_usec = 0;
  
  select_ret = select(fd+1, &rfds, NULL, NULL, &tv);
  if  (select_ret == -1)
    return(-1);					  			       
  
  if (select_ret == 0) // Timeout
      return(1);	
     
  while (select_ret) 
    {
      *newsockfd = accept(fd, 
			 (struct sockaddr *) &cli_addr,
			 &clilen);
      if (*newsockfd < 0)
	{
	  error("ERROR on accept");
	  return(-1);
	}

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
  
  if (rdstatus > 0)
    return(0);
  
  if (rdstatus == 0) 
    fprintf(stderr, "Connection closed\n");
  
  return(-1);
}


int main(int argc, char *argv[])
{
     int sockfd, newsockfd, portno;
     socklen_t clilen;
     char buffer[256];
     struct sockaddr_in serv_addr, cli_addr;
     int n;
     
     char       cmd_string[64];
     char       ret_string[64];
     char       rw_request[8];
     char       val_name[16];
     double     ret_val;
     int        read_status;

     if (argc < 2) {
         fprintf(stderr,"ERROR, no port provided\n");
         exit(1);
     }
     sockfd = socket(AF_INET, SOCK_STREAM, 0);
     if (sockfd < 0) 
        error("ERROR opening socket");
     bzero((char *) &serv_addr, sizeof(serv_addr));
     portno = atoi(argv[1]);
     serv_addr.sin_family = AF_INET;
     serv_addr.sin_addr.s_addr = INADDR_ANY;
     serv_addr.sin_port = htons(portno);
     if (bind(sockfd, (struct sockaddr *) &serv_addr,
              sizeof(serv_addr)) < 0) 
       error("ERROR on binding");
     printf("Listening:\n");
     listen(sockfd,5);
     clilen = sizeof(cli_addr);
     while (1)
       {
	 if (read_tcp(sockfd, &newsockfd,  ret_string,  sizeof(ret_string)/sizeof(char)) == 0)
	   {
	     printf("Read returned:\n%s\n", ret_string);
	     if (sscanf(ret_string, "%s %s = %lf", rw_request, val_name, &ret_val) == 3)
	       {
		 if ((strncmp(rw_request, "write", 2) == 0) ||(strncmp(rw_request, "WRITE", 2) == 0)) 
		   {
		     printf("Writing  %lf to \"%s\"\n", ret_val, val_name);
		     write(newsockfd,"OKAY", 4); 
		   }
		 else if ((strncmp(rw_request, "read", 2) == 0) ||(strncmp(rw_request, "READ", 2) == 0))
		   {
		     printf("Reading from \"%s\"\n", val_name);
		     write(newsockfd,"val = ", 4); 
		   }
		 else
		   {
		     printf("Bad command\n");
		     write(newsockfd,":(", 2); 
		   }
	       }
	     else if (sscanf(ret_string, "%s %s", rw_request, val_name) == 2)
	       {
		 if ((strncmp(rw_request, "read", 2) == 0) ||(strncmp(rw_request, "READ", 2) == 0))
		   {
		     printf("Reading from \"%s\"\n", val_name);
		     write(newsockfd,"val = 2 ", 8); 
		   }
	       }
	     else
	       write(newsockfd,":(", 2); 
	     
	     close(newsockfd);
	     
	   }
	 sleep(1);
	 
	 /* printf("Accepting:\n"); */
	 /* newsockfd = accept(sockfd,  */
	 /* 		    (struct sockaddr *) &cli_addr,  */
	 /* 		    &clilen); */
	 /* if (newsockfd < 0)  */
	 /*   error("ERROR on accept"); */
	 /* bzero(buffer,256); */
	 /* printf("Reading:\n"); */
	 /* n = read(newsockfd,buffer,255); */
	 /* if (n < 0) error("ERROR reading from socket"); */
	 /* printf("Here is the message: %s\n",buffer); */
	 /* n = write(newsockfd,"I got your message",18); */
	 /* if (n < 0) error("ERROR writing to socket"); */
	 /* close(newsockfd); */
       }
     close(sockfd);
     return 0; 
}
