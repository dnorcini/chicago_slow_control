/* Program for reading the Durridge RAD7 radon detector
 * and putting readings into a mysql database.
 *
 * Connection: serial-to-Ethernet converter (TCP), default 192.168.1.254:100.
 * Session: user normally presses Ctrl+C until '>' prompt appears.
 * Query:   "SPECIAL STATUS" <CR>
 * Parse:   line like:
 *   "> Last reading:   0544 15.4+-16.8 B"
 *           ^^^^^ ^^^^^
 *         concentration uncertainty
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <errno.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/time.h>

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "RAD7"

int   inst_dev;

/* RAD7 uses carriage return to terminate commands */
#ifndef CR
#define CR 13
#endif

/* ---------- small helpers ---------- */

/* Drain any pending bytes from the TCP receive buffer (replaces tcflush). */
static void tcp_drain(int fd)
{
    char tbuf[256];
    fd_set fds;
    struct timeval tv;
    FD_ZERO(&fds);
    FD_SET(fd, &fds);
    tv.tv_sec = 0; tv.tv_usec = 0;
    while (select(fd + 1, &fds, NULL, NULL, &tv) > 0) {
        if (read(fd, tbuf, sizeof(tbuf)) <= 0) break;
        FD_ZERO(&fds); FD_SET(fd, &fds);
        tv.tv_sec = 0; tv.tv_usec = 0;
    }
}

/* Read until we see a standalone prompt line: ">\r" or ">\n" (ignoring leading/trailing whitespace).
 * Returns bytes in buf (excluding NUL), or -1 on error, or 0 on timeout with no data.
 */
static ssize_t read_until_prompt_line(int fd, char *buf, size_t buflen, int timeout_ms)
{
    if (!buf || buflen < 2) return -1;
    buf[0] = '\0';

    size_t used = 0;
    int saw_any = 0;

    struct timeval start, now;
    gettimeofday(&start, NULL);

    /* Track the most recent line (for detecting prompt line) */
    char line[256];
    size_t line_used = 0;

    while (1)
    {
        unsigned char ch;
        ssize_t r = read(fd, &ch, 1);
        if (r < 0)
        {
            if (errno != EAGAIN && errno != EWOULDBLOCK) return -1;
        }
        else if (r == 1)
        {
            saw_any = 1;

            if (used + 1 < buflen)
            {
                buf[used++] = (char)ch;
                buf[used] = '\0';
            }

            /* Build current line */
            if (line_used + 1 < sizeof(line))
                line[line_used++] = (char)ch;

            if (ch == '\n' || ch == '\r')
            {
                line[line_used] = '\0';

                /* Trim whitespace from the line to test if it's just ">" */
                char *s = line;
                while (*s && isspace((unsigned char)*s)) s++;

                char *e = s + strlen(s);
                while (e > s && isspace((unsigned char)e[-1])) e--;
                *e = '\0';

                if (strcmp(s, ">") == 0)
                    return (ssize_t)used;

                /* reset line buffer */
                line_used = 0;
            }
        }

        gettimeofday(&now, NULL);
        long elapsed_ms = (now.tv_sec - start.tv_sec) * 1000L + (now.tv_usec - start.tv_usec) / 1000L;
        if (elapsed_ms >= timeout_ms)
            return saw_any ? (ssize_t)used : 0;

        msleep(5);
    }
}

/* Send Ctrl+C (ETX) and wait for '>' prompt.
 * Returns 0 on success, 1 on failure.
 */
static int rad7_enter_prompt(int fd)
{
    char rx[2048];

    /* Flush any prior junk */
    tcp_drain(fd);

    /* RAD7 sometimes needs more than one Ctrl+C depending on what it's doing */
    for (int tries = 0; tries < 5; tries++)
    {
        unsigned char etx = 0x03; /* Ctrl+C */
        (void)write(fd, &etx, 1);
        msleep(200);

        memset(rx, 0, sizeof(rx));
        ssize_t n = read_until_prompt_line(fd, rx, sizeof(rx), 5000);
        if (n < 0) return 1;

        /* Any '>' anywhere is good enough for "ready" */
        if (strchr(rx, '>') != NULL)
            return 0;
    }

    return 1;
}

/* Parse "Last reading:" line, extract concentration and uncertainty.
 * Returns 0 on success, 1 on failure.
 */
static int parse_last_reading(const char *reply, double *conc_out, double *unc_out)
{
    if (!reply || !conc_out || !unc_out) return 1;

    const char *p = strstr(reply, "Last reading:");
    if (!p) return 1;

    /* Move to the numbers; skip label */
    p += strlen("Last reading:");

    /* Typical: "   0544 15.4+-16.8 B"
       We want the token containing "+-" */
    char line[256];
    size_t i = 0;

    /* Copy until newline or end */
    while (*p && *p != '\n' && *p != '\r' && i < sizeof(line) - 1)
    {
        line[i++] = *p++;
    }
    line[i] = '\0';

    /* Find "+-" inside that line */
    char *pm = strstr(line, "+-");
    if (!pm) return 1;

    /* Split into left/right parts around "+-" */
    *pm = '\0';
    char *right = pm + 2;

    /* Left side ends with concentration; strtod will parse trailing spaces OK */
    /* But left side also contains run number (e.g. 0544). We want the LAST number on the left. */
    double conc = 0.0, unc = 0.0;
    int found = 0;

    /* Walk tokens on left side and keep last parsable number */
    char *saveptr = NULL;
    char leftcopy[256];
    strncpy(leftcopy, line, sizeof(leftcopy) - 1);
    leftcopy[sizeof(leftcopy) - 1] = '\0';

    for (char *tok = strtok_r(leftcopy, " \t", &saveptr); tok; tok = strtok_r(NULL, " \t", &saveptr))
    {
        char *endp = NULL;
        double v = strtod(tok, &endp);
        if (endp && endp != tok && *endp == '\0')
        {
            conc = v;
            found = 1;
        }
    }
    if (!found) return 1;

    /* Uncertainty is first number on right side */
    {
        char *endp = NULL;
        unc = strtod(right, &endp);
        if (!(endp && endp != right))
            return 1;
    }

    *conc_out = conc;
    *unc_out  = unc;
    return 0;
}

/* Parse temperature from a line like:
 *   "> 24.9`C RH: 4%  B:7.09V P: 10mA"
 * Returns 0 on success, 1 on failure.
 */
static int parse_temperature_c(const char *reply, double *temp_out)
{
    if (!reply || !temp_out) return 1;

    /* Find the backtick-C marker used by RAD7: "`C" */
    const char *m = strstr(reply, "`C");
    if (!m) return 1;

    /* Walk left to the start of the number (handles spaces and possible '>') */
    const char *p = m;
    while (p > reply && (isdigit((unsigned char)p[-1]) || p[-1] == '.' || p[-1] == '-' || p[-1] == '+'))
        p--;

    /* Parse number */
    char *endp = NULL;
    double t = strtod(p, &endp);
    if (!(endp && endp != p)) return 1;

    *temp_out = t;
    return 0;
}


/* ---------- required SC hooks ---------- */

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    if ((inst_dev = connect_tcp(i_s)) < 0)
    {
        fprintf(stderr, "RAD7: TCP connect failed.\n");
        my_signal = SIGTERM;
        return 1;
    }

    /* Set non-blocking so read_until_prompt_line's EAGAIN timeout loop works */
    int flags = fcntl(inst_dev, F_GETFL, 0);
    fcntl(inst_dev, F_SETFL, flags | O_NONBLOCK);

    /* Enter interactive prompt once at startup */
    if (rad7_enter_prompt(inst_dev) != 0)
    {
        fprintf(stderr, "RAD7: failed to reach '>' prompt during setup.\n");
        close(inst_dev);
        return 1;
    }

    return 0;
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    char cmd[64];
    char reply[8192];

    if (!val_out) return 1;

    /* Make sure we're at prompt (RAD7 can be in mid-output if something happened) */
    if (rad7_enter_prompt(inst_dev) != 0)
    {
        fprintf(stderr, "RAD7: failed to reach '>' prompt before query.\n");
        return 1;
    }

    memset(cmd, 0, sizeof(cmd));
    snprintf(cmd, sizeof(cmd), "SPECIAL STATUS%c", CR);

    tcp_drain(inst_dev);
    (void)write(inst_dev, cmd, strlen(cmd));

    /* RAD7 replies can take a moment */
    msleep(300);

    memset(reply, 0, sizeof(reply));
    ssize_t n = read_until_prompt_line(inst_dev, reply, sizeof(reply), 5000);
    if (n < 0)
    {
        fprintf(stderr, "RAD7: read error.\n");
        return 1;
    }
    if (n == 0)
    {
        fprintf(stderr, "RAD7: timed out waiting for reply.\n");
        return 1;
    }

    double conc = 0.0, unc = 0.0, temp_c = 0.0;
    if (parse_last_reading(reply, &conc, &unc) != 0)
    {
        /* Try once more: sometimes first read gets echo/partial */
        memset(reply, 0, sizeof(reply));
        n = read_until_prompt_line(inst_dev, reply, sizeof(reply), 5000);
        if (n <= 0 || parse_last_reading(reply, &conc, &unc) != 0)
        {
            fprintf(stderr, "RAD7: failed to parse 'reading' from reply (len=%zd)\n", n);
            return 1;
        }
    }
    /* Temperature is optional but usually present */
    if (parse_temperature_c(reply, &temp_c) != 0)
    {
        /* not fatal unless caller requests temp */
        temp_c = -9999;
    }


    if (s_s && s_s->subtype)
    {
       /* Always print both values for logging / debugging */
      printf("RAD7 readout: conc=%.3f  unc=%.3f  tempC=%.2f\n", conc, unc, temp_c);
      if (strncmp(s_s->subtype, "conc", 4) == 0)  
              *val_out = conc;
      else if (strncmp(s_s->subtype, "unc", 3) == 0)  
        *val_out = unc;
      else if (strncmp(s_s->subtype, "temp", 4) == 0)  
        *val_out = temp_c;
      else
      {
        fprintf(stderr, "RAD7: invalid sensor subtype");
        return 1;
      }
    }
    return 0;
}


#include "main.h"
