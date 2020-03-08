SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include -I/usr/local/include/gpib/

LDFLAGS =  -L../lib -lgpib -lslow_control 
LDFLAGS2 =  -L../lib -lgpib

PROGS = SPS125 SPS125Output

all: $(PROGS) 

SPS125: SPS125.o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

SPS125Output: SPS125Output.o
	$(CC) $(CFLAGS) $(LDFLAGS2) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
