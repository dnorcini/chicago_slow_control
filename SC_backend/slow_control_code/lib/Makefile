SHELL = /bin/sh
CC = gcc -O3

CFLAGS = `mysql_config --cflags` -I../include 
LDFLAGS = `mysql_config --libs`

AR = ar
ARFLAGS = rv

LIB = libslow_control
SRCS = SC_db_interface.c SC_aux_fns.c SC_inst_interface.c SC_sensor_interface.c modbus.c serial.c ethernet.c
OBJS = SC_db_interface.o SC_aux_fns.o SC_inst_interface.o SC_sensor_interface.o modbus.o serial.o ethernet.o

all: $(LIB).a $(LIB).so 

$(LIB).so: $(SRCS)
	$(CC) -fPIC -c $(CFLAGS) $?			
	$(CC) -shared -o $(LIB).so $(OBJS) $(LDFLAGS)
	rm -f $(OBJS)

$(LIB).a: $(SRCS)
	$(CC) -c $(CFLAGS) $?
	$(AR) $(ARFLAGS) $@ *.o
	ranlib $(LIB).a
	rm -f $(OBJS)

clean:
	rm -f $(OBJS) $(LIB).a	$(LIB).so 
