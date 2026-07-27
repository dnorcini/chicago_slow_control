SHELL   = /bin/sh
CC      = gcc
CFLAGS += -O3 \
          -I../include \
          -I/usr/local/include \
          -I/path/to/dir \
          $(shell mariadb_config --cflags) 
LDFLAGS = -L/usr/local/lib64 -Wl,-rpath=/usr/local/lib64 \
          -lopen62541 -lpthread -lm -lrt \
          -L../lib -lslow_control

PROGS = HiCube_Neo

all: $(PROGS)

$(PROGS): $(PROGS).o
	$(CC) -o $@ $@.o $(LDFLAGS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)
