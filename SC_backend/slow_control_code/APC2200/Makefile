SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mariadb_config --cflags` -I../include 

LDFLAGS =  -L../lib -lslow_control -I/usr/local/include 

PROGS = APC2200

all: $(PROGS) 

APC3000: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
