SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include -I/usr/local/include/gpib/

LDFLAGS =  -L../lib -lgpib -lslow_control

PROGS = CryoMag_LM500

all: $(PROGS) 

CryoCon34: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
