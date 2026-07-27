/* Program for controlling an Eaton ePDU G3 using SNMP */
/* Cinyu Zhu, Hopkins, 2026 */

#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#define INSTNAME "EatonG3"

#define MAX_OUTLETS 8

// strappingIndex is always 0 for units that don't support cascaded "strapping" (EATON-EPDU-MIB, unitEntry.1)
#define STRAPPING_INDEX 0

// eatonEpdu = enterprises.534.6.6.7  (eaton.products.pduAgent.eatonEpdu, see EATON-OIDS.txt / EATON-EPDU-MIB.txt)
#define OID_BASE ".1.3.6.1.4.1.534.6.6.7"

// outlets.outletControlTable.outletControlEntry.outletControlStatus  (outlets(6).6.1.2)
#define OID_OUTLET_STATUS OID_BASE ".6.6.1.2"
// outlets.outletControlTable.outletControlEntry.outletControlOffCmd  (outlets(6).6.1.3)
#define OID_OUTLET_OFF_CMD OID_BASE ".6.6.1.3"
// outlets.outletControlTable.outletControlEntry.outletControlOnCmd  (outlets(6).6.1.4)
#define OID_OUTLET_ON_CMD OID_BASE ".6.6.1.4"
// inputs.inputPowerTable.inputPowerEntry.inputWatts, watts  (inputs(3).4.1.4)
// Indexed by strappingIndex.inputIndex.inputPowerIndex; this unit is single-phase, so
// inputIndex 1 / inputPowerIndex 1 (inputPowerMeasType phase1) is the only row and equals the total.
#define OID_TOTAL_WATTS OID_BASE ".3.4.1.4.0.1.1"

#define READ_COMMUNITY "public"
#define WRITE_COMMUNITY "private"

int inst_dev;

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  return (0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  close(inst_dev);
}

// Runs an snmpget for a fully qualified OID and returns the parsed INTEGER value in *val_out
int snmp_get_int(struct inst_struct *i_s, const char *full_oid, double *val_out)
{
  char cmd_string[512];
  char ret_string[512];
  char buffer[128];
  FILE *tmp;

  sprintf(cmd_string, "snmpget -v1 -c %s %s %s", READ_COMMUNITY, i_s->dev_address, full_oid);

  tmp = popen(cmd_string, "r");
  if (tmp == NULL)
  {
    fprintf(stderr, "Failed to run command: %s\n", cmd_string);
    return (1);
  }

  ret_string[0] = '\0';
  while (fgets(buffer, sizeof(buffer), tmp) != NULL)
  {
    sprintf(ret_string, "%s", buffer);
  }
  pclose(tmp);

  char *val_ptr = strstr(ret_string, "INTEGER:");
  if (val_ptr == NULL)
  {
    fprintf(stderr, "No INTEGER tag found in return string: \"%s\"\n", ret_string);
    return 1;
  }

  val_ptr += strlen("INTEGER:");

  int raw_val;
  if (sscanf(val_ptr, "%d", &raw_val) != 1)
  {
    fprintf(stderr, "Failed to parse integer from: \"%s\"\n", val_ptr);
    return 1;
  }

  *val_out = (double)raw_val;
  return 0;
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  char full_oid[128];

  // outletControlStatus: off(0), on(1), pendingOff(2), pendingOn(3)
  if (strncmp(s_s->subtype, "outlet", 6) == 0)
  {
    if (s_s->num < 1 || s_s->num > MAX_OUTLETS)
    {
      fprintf(stderr, "Outlet index %d is invalid. Must be between 1 and %d.\n", s_s->num, MAX_OUTLETS);
      return (1);
    }

    sprintf(full_oid, "%s.%d.%d", OID_OUTLET_STATUS, STRAPPING_INDEX, s_s->num);
    return snmp_get_int(i_s, full_oid, val_out);
  }

  // total real power drawn by the PDU across all outlets, in watts
  else if (strncmp(s_s->subtype, "totalpower", 10) == 0)
  {
    return snmp_get_int(i_s, OID_TOTAL_WATTS, val_out);
  }

  fprintf(stderr, "Unsupported sensor subtype: %s\n", s_s->subtype);
  return 1;
}

// Issues an immediate (0 second delay) on/off command to a single outlet
int set_outlet(struct inst_struct *i_s, int outlet, int set_val)
{
  char cmd_string[512];
  char ret_string[512];
  char buffer[128];
  FILE *tmp;

  const char *oid_base = (set_val == 1) ? OID_OUTLET_ON_CMD : OID_OUTLET_OFF_CMD;

  sprintf(cmd_string, "snmpset -v1 -c %s %s %s.%d.%d i 0",
          WRITE_COMMUNITY, i_s->dev_address, oid_base, STRAPPING_INDEX, outlet);

  tmp = popen(cmd_string, "r");
  if (tmp == NULL)
  {
    fprintf(stderr, "Failed to run command for outlet %d.\n", outlet);
    return 1;
  }

  ret_string[0] = '\0';
  while (fgets(buffer, sizeof(buffer), tmp) != NULL)
  {
    sprintf(ret_string, "%s", buffer);
  }
  pclose(tmp);

  return 0;
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  // set one outlet
  if (strncmp(s_s->subtype, "setmain", 7) == 0)
  {
    if (s_s->num < 1 || s_s->num > MAX_OUTLETS)
    {
      fprintf(stderr, "Outlet index %d is invalid. Must be between 1 and %d.\n", s_s->num, MAX_OUTLETS);
      return 1;
    }

    int set_val = (int)s_s->new_set_val; // 1 = ON, 2 = OFF
    if (set_val != 1 && set_val != 2)
    {
      fprintf(stderr, "Invalid set value: %d. Must be 1 (on) or 2 (off).\n", set_val);
      return 1;
    }

    return set_outlet(i_s, s_s->num, set_val);
  }

  // set multiple outlets all together
  else if (strncmp(s_s->subtype, "outletgroup", 11) == 0)
  {
    int set_val = (int)s_s->new_set_val;
    if (set_val != 1 && set_val != 2)
    {
      fprintf(stderr, "Invalid set value for outletgroup: %d\n", set_val);
      return 1;
    }

    if (is_null(s_s->user1))
    {
      fprintf(stderr, "user1 is empty; no outlet list provided.\n");
      return 1;
    }

    // Duplicate the string so strtok doesn't modify original
    char user_input_copy[128];
    strncpy(user_input_copy, s_s->user1, sizeof(user_input_copy));
    user_input_copy[sizeof(user_input_copy) - 1] = '\0';

    char *token = strtok(user_input_copy, ",");
    while (token != NULL)
    {
      int outlet = atoi(token);
      if (outlet < 1 || outlet > MAX_OUTLETS)
      {
        fprintf(stderr, "Invalid outlet number %d in user1.\n", outlet);
        token = strtok(NULL, ",");
        continue;
      }

      if (set_outlet(i_s, outlet, set_val))
        return 1;

      token = strtok(NULL, ",");
    }
    return 0;
  }
  else
  {
    fprintf(stderr, "Unsupported sensor subtype for setting: %s\n", s_s->subtype);
    return 1;
  }
}

#include "main.h"
