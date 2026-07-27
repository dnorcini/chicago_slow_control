/* ScioSense UFM-01 ultrasonic flow meter driver */
/* Connects via UART-to-Ethernet converter. */
/* The sensor streams one 32-byte frame per second automatically -- */
/* no commands needed; we listen, validate, and decode. */
/* Cinyu Zhu, Hopkins, 2026 */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#include "ethernet.h"

#define INSTNAME "ScioSense"

int inst_dev;

/* ---- cached decoded values, shared across sensors in one loop cycle ---- */
static double cached_flow  = 0.0;  /* instantaneous flow [l/h]   */
static double cached_accum = 0.0;  /* accumulated flow   [L]     */
static double cached_temp  = 0.0;  /* temperature        [deg C] */
static double cached_empty = 0.0;  /* empty-tube flag (0 or 1)   */
static time_t cache_time   = 0;

/* Decode one packed-BCD byte into 0..99 */
static int bcd_byte(unsigned char b)
{
    return ((b >> 4) & 0xF) * 10 + (b & 0xF);
}

/* Little-endian packed-BCD: bytes[0] is LSB, bytes[n-1] is MSB.
   Matches the bash bcd_to_int() that receives args LSB-first. */
static long long bcd_le(unsigned char *bytes, int n)
{
    long long v = 0;
    int i;
    for (i = n - 1; i >= 0; i--)
        v = v * 100 + bcd_byte(bytes[i]);
    return v;
}

/* Read up to n bytes from inst_dev with a timeout_s second deadline.
   Returns bytes read, 0 on timeout, -1 on error. */
static int timed_recv(unsigned char *buf, int n, int timeout_s)
{
    fd_set fds;
    struct timeval tv;
    tv.tv_sec  = timeout_s;
    tv.tv_usec = 0;
    FD_ZERO(&fds);
    FD_SET(inst_dev, &fds);
    int r = select(inst_dev + 1, &fds, NULL, NULL, &tv);
    if (r <= 0) return r;
    return read(inst_dev, buf, n);
}

/* Read and decode one valid UFM-01 frame into the cache globals.
   Frame layout (32 bytes):
     [0..1]  sync 0x3C 0x32
     [9..14] accumulated flow, little-endian BCD, raw / 1000 = litres
     [16..19] instantaneous flow, little-endian BCD, raw / 100 = l/h
     [20]    sign byte: 0x80 means negative
     [25..26] temperature, little-endian BCD, raw / 100 = deg C
     [28]    status: bit 5 (0x20) = empty tube
     [30]    checksum = sum(bytes[0..29]) & 0xFF
     [31]    stop byte 0x16
   Returns 0 on success, 1 on failure. */
static int read_ufm01_frame(void)
{
    unsigned char buf[128];
    int len = 0;
    int attempts;

    for (attempts = 0; attempts < 30; attempts++) {
        int n = timed_recv(buf + len, (int)sizeof(buf) - len, 3);
        if (n <= 0) {
            log_err("UFM-01: read timeout or connection lost\n");
            return 1;
        }
        len += n;

        /* Find sync bytes 0x3C 0x32 */
        int idx = -1, i;
        for (i = 0; i <= len - 2; i++) {
            if (buf[i] == 0x3C && buf[i+1] == 0x32) { idx = i; break; }
        }
        if (idx < 0) {
            /* No sync yet; keep last byte in case it was the first sync byte */
            buf[0] = buf[len - 1];
            len = 1;
            continue;
        }

        /* Shift buffer so frame starts at index 0 */
        if (idx > 0) {
            memmove(buf, buf + idx, len - idx);
            len -= idx;
        }

        if (len < 32) continue;  /* Need more bytes */

        /* Validate stop byte */
        if (buf[31] != 0x16) {
            memmove(buf, buf + 1, len - 1);
            len--;
            continue;
        }

        /* Validate checksum */
        int sum = 0;
        for (i = 0; i < 30; i++) sum += buf[i];
        if ((sum & 0xFF) != buf[30]) {
            memmove(buf, buf + 1, len - 1);
            len--;
            continue;
        }

        /* Decode */
        long long acc_raw  = bcd_le(&buf[9],  6);
        long long inst_raw = bcd_le(&buf[16], 4);
        if (buf[20] == 0x80) inst_raw = -inst_raw;
        long long temp_raw = bcd_le(&buf[25], 2);

        cached_accum = (double)acc_raw  / 1000.0;
        cached_flow  = (double)inst_raw / 100.0 / 60; //convert from L/h to L/min unit
        cached_temp  = (double)temp_raw / 100.0;
        cached_empty = (buf[28] & 0x20) ? 1.0 : 0.0;
        cache_time   = time(NULL);
        return 0;
    }

    log_err("UFM-01: failed to find valid frame after %d attempts\n", attempts);
    return 1;
}

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    if ((inst_dev = connect_tcp(i_s)) < 0) {
        log_err("UFM-01: TCP connect failed.\n");
        my_signal = SIGTERM;
        return 1;
    }
    return 0;
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    close(inst_dev);
}

/* Subtypes:
     "flow"  -- instantaneous flow rate [l/h]
     "accum" -- accumulated volume      [L]
     "temp"  -- fluid temperature       [deg C]
     "empty" -- empty-tube flag         (0=liquid, 1=empty) */
#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    /* Refresh cached frame if it is more than 1 second old */
    if (time(NULL) - cache_time > 1) {
        if (read_ufm01_frame()) {
            log_err("UFM-01: failed to read frame for sensor %s\n", s_s->name);
            return 1;
        }
    }

    if      (strncmp(s_s->subtype, "flow",  4) == 0) *val_out = cached_flow;
    else if (strncmp(s_s->subtype, "accum", 5) == 0) *val_out = cached_accum;
    else if (strncmp(s_s->subtype, "temp",  4) == 0) *val_out = cached_temp;
    else if (strncmp(s_s->subtype, "empty", 5) == 0) *val_out = cached_empty;
    else {
        log_err("UFM-01: unknown subtype \"%s\" for sensor %s\n",
                s_s->subtype, s_s->name);
        return 1;
    }
    return 0;
}

#include "main.h"
