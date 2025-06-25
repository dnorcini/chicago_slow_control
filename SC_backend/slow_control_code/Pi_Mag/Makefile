SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include 

LDFLAGS =  -L../lib

LIBS = -lslow_control

PROGS = Pi_Mag


$all: 
	$(CC) $(CFLAGS) $(PROGS).c $(LDFLAGS) $(LIBS) -o $(PROGS) 
	rm -f $(PROGS).o

clean:
	rm -f *.o $(PROGS)	
