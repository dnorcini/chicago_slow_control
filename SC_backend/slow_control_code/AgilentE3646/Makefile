SHELL = /bin/sh
CC = gcc -Wl,--no-as-needed -O3

CFLAGS = `mariadb_config --cflags` -I../include 

LDFLAGS =  -L../lib -lslow_control -I/usr/local/include 

PROGS = AgilentE3646

all: $(PROGS) 

AgilentE3646: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
