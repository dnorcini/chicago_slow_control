SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include 

LDFLAGS =  -L../lib -lslow_control -I/usr/local/include 

PROGS = TPG362

all: $(PROGS) 

TPG261: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
