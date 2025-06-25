/* This is the header for dealing with the gpib interface. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006 */
/* GPL public licence. */
/* Make with cflag:  -I/usr/local/include/gpib/ */
/* and lib: -lgpib  */
#ifndef _SC_GPIB_H_
#define _SC_GPIB_H_

#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>

#include <ib.h>

#define TYPE_GPIB 488

#ifndef _IO_CHARS
#define _IO_CHARS
static char STX = 0x02;
static char ENQ = 0x05;
static char CR  = 0x0D;
static char LF  = 0x0A;
static char ACK = 0x06;
static char NAK = 0x15;
static char ETX = 0x03;
#endif  // _IO_CHARS

#endif


