/* This is a header file for dealing with an ethernet interface. */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2012 */
/* James public licence. */
#ifndef _SC_ETHERNET_H_
#define _SC_ETHERNET_H_

#include <stdio.h>

#include <sys/select.h>
#include <sys/types.h>
#include <sys/socket.h>

#include <arpa/inet.h>
#include <netinet/in.h>
#include <netinet/ip.h>
#include <netinet/tcp.h>

#include "SC_sensor_interface.h"
#include "SC_aux_fns.h"

#define TYPE_ETH 793
#define MAX_ETH_RETRIES 10

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

////// Establishes a TCP connection, and return socket file descriptor
int connect_tcp(struct inst_struct *i_s);
int connect_tcp_raw(char *IP_address, int port);

int query_tcp(int fd, char *cmd_string, size_t c_count, char *ret_string, size_t r_count);
int write_tcp(int fd, char *cmd_string, size_t c_count);
int read_tcp(int fd, char *ret_string, size_t r_count);

int global_tcp_timeout = 5;   // seconds
#endif  //_SC_ETHERNET_H_
