srcdir=.

ROOT=
prefix=/usr/local
exec_prefix=${prefix}
bindir=$(ROOT)${exec_prefix}/bin
mandir=$(ROOT)${prefix}/man

CC=gcc
CFLAGS=-O3 -g -Wall -I$(srcdir)/.. -I.. -D_GNU_SOURCE  -I../include `giblib-config --cflags`
LDFLAGS =  -L../lib  `giblib-config --libs` 

INSTALL=/usr/bin/install -c
INSTALL_PROGRAM=${INSTALL}
INSTALL_DATA=${INSTALL} -m 644
INSTALL_DIR=mkdir -p

PROGS=webcam

OBJS=webcam.o

##########################################################################

all build: $(PROGS)

webcam: $(OBJS)
	$(CC) $(CFLAGS) $(LDFLAGS) -o $@ $(OBJS) 

install: all
	$(INSTALL_DIR) $(bindir)
	$(INSTALL_PROGRAM) webcam $(bindir)

clean:
	-rm -f $(OBJS) *.moc *~ core* *.bak TAGS webcam 

dep depend install:
