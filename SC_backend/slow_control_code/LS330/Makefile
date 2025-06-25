SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include -I/usr/local/include/gpib/

LDFLAGS =  -L../lib -lslow_control -lgpib

PROGS = LS330

all: $(PROGS) 

CryoCon34: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
