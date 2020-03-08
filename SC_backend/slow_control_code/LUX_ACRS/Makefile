SHELL = /bin/sh

PREFIXPATH=/usr/local/arm-linux/bin
CC=$(PREFIXPATH)/arm-linux-gcc
STRIP=$(PREFIXPATH)/arm-linux-strip
AR = arm-linux-ar
ARFLAGS = rv
RANLIB = arm-linux-ranlib

CFLAGS =  -I../include -I/usr/local/arm-linux/include -I/usr/local/arm-linux/usr/include/mysql -fexpensive-optimizations -fomit-frame-pointer -frename-registers -DUNIV_LINUX
LDFLAGS = -L/usr/local/arm-linux/lib -L/usr/local/arm-linux/usr/lib -lmysqlclient

LIB = libslow_control_em
SRCS = ../lib/SC_db_interface.c ../lib/SC_aux_fns.c ../lib/SC_inst_interface.c ../lib/SC_sensor_interface.c ../lib/modbus.c
OBJS = SC_db_interface.o SC_aux_fns.o SC_inst_interface.o SC_sensor_interface.o modbus.o

PROGS = ACRS

all: $(LIB).a $(LIB).so $(PROGS)

$(LIB).so: $(SRCS)
	$(CC) -fPIC -c $(CFLAGS) $?			
	$(CC) -shared -o $(LIB).so $(OBJS) $(LDFLAGS)
	$(STRIP) -s $(LIB).so
	rm -f $(OBJS)

$(LIB).a: $(SRCS)
	$(CC) -c $(CFLAGS) $?
	$(AR) $(ARFLAGS) $@ *.o
	$(RANLIB) $(LIB).a
	$(STRIP) -s $(LIB).a
	rm -f $(OBJS)

$(PROGS): $(PROGS).o
	$(CC) $(CFLAGS) $(LDFLAGS) -L./ -lslow_control_em -o $@ $@.o -lmoxalib
	$(STRIP) -s $(PROGS)
	rm -f $@.o

clean:
	rm -f *.o $(OBJS) $(LIB).a	$(LIB).so $(PROGS) 
