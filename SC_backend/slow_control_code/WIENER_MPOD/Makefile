SHELL = /bin/sh
#CC = gcc -O3
CC = g++ -O3
#CC = g++ -std=c++11 -O3
CXX = g++

CFLAGS = `mysql_config --cflags` -I../include
#CXXFLAGS = -std=c++11

LDFLAGS =  -L../lib -lslow_control 

PROGS = WIENER_MPOD

all: $(PROGS)

$(all): $(PROGS).o
	$(CXX) $(CFLAGS) $(LDFLAGS) -o $@ $@.o $(LIBS)
	rm -f $@.o

clean:
	rm -f *.o $(PROGS)

