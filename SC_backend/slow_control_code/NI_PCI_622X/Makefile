SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include 

LDFLAGS =  -L../lib -lslow_control -lcomedi

PROGS = NI_PCI_622X

all: $(PROGS) 

CryoCon34: $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
