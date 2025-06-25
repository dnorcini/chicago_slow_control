/* This is a header file for dealing with the rs232 interface. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2006 */
/* James public licence. */
#ifndef _SC_RS232_H_
#define _SC_RS232_H_

#include <stdio.h>
#include <termios.h>
#include <unistd.h>
#include <sys/ioctl.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <asm/ioctls.h>
#include <linux/serial.h>

#include "SC_aux_fns.h"

#define TYPE_SERIAL 232
#define MAX_SERIAL_RETRIES 10

#ifndef _IO_CHARS
#define _IO_CHARS
static char STX  __attribute__ ((unused)) = 0x02;
static char ENQ  __attribute__ ((unused)) = 0x05;
static char CR   __attribute__ ((unused)) = 0x0D;
static char LF   __attribute__ ((unused)) = 0x0A;
static char ACK  __attribute__ ((unused)) = 0x06;
static char NAK  __attribute__ ((unused)) = 0x15;
static char ETX  __attribute__ ((unused)) = 0x03;
#endif  // _IO_CHARS


//int connect_serial(struct inst_struct *i_s);
int query_serial(int fd, char *cmd_string, size_t c_count, char *ret_string, size_t r_count);
int write_serial(int fd, char *cmd_string, size_t c_count);
int read_serial(int fd, char *ret_string, size_t r_count);

#endif
