/* Program for controlling a ServerTech PDU using SNMP */
/* Cinyu Zhu, Hopkins, 2025 */

#include "SC_db_interface.h"
#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#define INSTNAME "Liebert"
int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  char cmd_string[64];
  return (0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  char cmd_string[512];
  char ret_string[512];
  char buffer[128];
  FILE *tmp;

  if (strncmp(s_s->subtype, "batterytime", 11) == 0)
  {
    sprintf(cmd_string, "snmpget -v2c -c public %s 1.3.6.1.4.1.476.1.42.3.9.20.1.20.1.2.1.4150", i_s->dev_address);
  }
  else if (strncmp(s_s->subtype, "outpower", 8) == 0)
  {
    sprintf(cmd_string, "snmpget -v2c -c public %s 1.3.6.1.4.1.476.1.42.3.9.20.1.20.1.2.1.4208", i_s->dev_address);
  }

   else if (strncmp(s_s->subtype, "batteryperc", 11) == 0)
  {
    sprintf(cmd_string, "snmpget -v2c -c public %s 1.3.6.1.4.1.476.1.42.3.9.20.1.20.1.2.1.4153", i_s->dev_address);
  }
  else
  {
    fprintf(stderr, "subtype %s does not exist\n", s_s->subtype);
    return 1;
  }

  tmp = popen(cmd_string, "r");
  if (tmp == NULL)
  {
    fprintf(stderr, "Failed to run command: %s\n", cmd_string);
    return (1);
  }
  // read response one line at a time
  while (fgets(buffer, sizeof(buffer), tmp) != NULL)
  {
    sprintf(ret_string, "%s", buffer);
  }
// close tmp file
pclose(tmp);

// output chopping
char *val_ptr = strstr(ret_string, "STRING:");
if (val_ptr == NULL)
{
  fprintf(stderr, "No STRING tag found in return string: \"%s\"\n", ret_string);
  return 1;
}

val_ptr += strlen("STRING:");

int raw_val;
if (sscanf(val_ptr, " \"%d\"", &raw_val) != 1)
{
  fprintf(stderr, "Failed to parse integer from: \"%s\"\n", val_ptr);
  return 1;
}

*val_out = (double)raw_val;
return 0;
  
}

#include "main.h"
