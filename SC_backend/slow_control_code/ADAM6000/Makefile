SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include

LDFLAGS = -L../lib -lslow_control -lm

PROGS = ADAM6015 ADAM6017 ADAM6024 ADAM6060 ADAM6052

all: $(PROGS) 

$(all): $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)	
