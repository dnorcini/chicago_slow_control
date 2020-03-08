/* Program for controlling the LeCroy1458 HV crate using the ethernet interface */
/* The factory default network password is “lrs1450”. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2010 */
/* James public licence. */

#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

#define INSTNAME "LeCroy1458"

int comm_type = -1;
int inst_dev;

int  HV_sts = 0;       // 1:HV on,           0:HV off
int  Intlck_sts = 0;   // 1:Interlock open,  0:Interlock closes 
int  Flt_sts = 0;      // 1:Some Fault,      0:No Fault 
int  has_error = 0;


struct HV_slot_struct {
  char   name[18];          // DB table name
  int    time;              // DB insertion time 
  double value;             // OR of Status
  double rate;              // Not used
  char   Voltage_str[130];  // MV
  double Voltages[12];
  char   Current_str[130];  // MC
  double Currents[12]; 
  char   Status_str[130];   // ST
  int    Statuses[12];
  int    slot_number;       // Slot number (not in DB)
};

struct HV_ctrl_slot_struct {
  char   name[18];          // DB table name
  int    time;              // DB change time
  double value;             // Not used
  double rate;              // Not used
  char   Enable_str[130];   // CE
  int    Enables[12];
  char   Demand_V_str[130]; // DV
  double Demand_Vs[12];
  double Trip_Current;      // TC (apply to all channels in slot)
  double Ramp_Rate;         // RUP+RDN/2 (apply to all channels in slot)
  int    slot_number;       // Slot number (not in DB)
};



//////////////////////////////////////////////////////////
///  Utilities that are only really useful here:

int OR_array(int *array_in, int num)
{
  int i;
  for (i=0; i<num; i++)
    if (array_in[i])
      return(1);
  return(0);
}


int MAX_array(int *array_in, int num)
{
  int i;
  int max_val = array_in[0];
  for (i=1; i<num; i++)
    if (array_in[i] > max_val)
      max_val = array_in[i];
  return(max_val);
}



//////////////////////////////////////////////////////////
///  Hardware dependent code:

int read_voltages(struct HV_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d MV", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", HV_s->Voltages, 12))
    return(1);
  implode_from_double(HV_s->Voltage_str, sizeof(HV_s->Voltage_str), ",", 
		      HV_s->Voltages, 12, "%.1f");
  return(0);
}


int read_demand_voltages(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d DV", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", HV_s->Demand_Vs, 12))
    return(1);
  implode_from_double(HV_s->Demand_V_str, sizeof(HV_s->Demand_V_str), ",", 
		      HV_s->Demand_Vs, 12, "%.1f");
  return(0);
}

int set_demand_voltages(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[256];
  char   ret_string[128];   
  char   voltage_string[128];
  
  implode_from_double(voltage_string, sizeof(voltage_string), " ", 
		      HV_s->Demand_Vs, 12, "%.1f");
  sprintf(cmd_string, "LD S%d DV %s", HV_s->slot_number, voltage_string);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  return(0);
}


int read_currents(struct HV_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d MC", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", HV_s->Currents, 12))
    return(1);
  implode_from_double(HV_s->Current_str, sizeof(HV_s->Current_str), ",", 
		      HV_s->Currents, 12, "%.1f");
  return(0);
}

int read_trip_current(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       
  double Currents[12];
  
  sprintf(cmd_string, "RC S%d TC", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", Currents, 12))
    return(1);
  HV_s->Trip_Current = average_array(Currents, 12, 1);
  return(0);
}

int set_trip_current(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[256];
  char   ret_string[128];       
  char   trip_string[128];
  
  implode_from_one_double(trip_string, sizeof(trip_string), " ", 
			  HV_s->Trip_Current, 12, "%.1f");
  sprintf(cmd_string, "LD S%d TC %s", HV_s->slot_number, trip_string);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  return(0);
}

int read_ramp_rate(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       
  double RRates[12];
  double RUp;
  double RDown;
  
  sprintf(cmd_string, "RC S%d RUP", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", RRates, 12))
    return(1);
  RUp = average_array(RRates, 12, 1);
  msleep(100);
  
  sprintf(cmd_string, "RC S%d RDN", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", RRates, 12))
    return(1);
  RDown =  average_array(RRates, 12, 1);
  HV_s->Ramp_Rate = (RUp + RDown)/2;
  return(0);
}

int set_ramp_rate(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[256];
  char   ret_string[128];       
  char   ramp_string[128];
  
  implode_from_one_double(ramp_string, sizeof(ramp_string), " ", 
			  HV_s->Ramp_Rate, 12, "%.1f");
  sprintf(cmd_string, "LD S%d RUP %s", HV_s->slot_number, ramp_string);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  msleep(50);
  sprintf(cmd_string, "LD S%d RDN %s", HV_s->slot_number, ramp_string);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  return(0);
}

int read_channel_status(struct HV_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d ST", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_int(ret_string, " ", HV_s->Statuses, 12))
    return(1);
  implode_from_int(HV_s->Status_str, sizeof(HV_s->Status_str), ",", HV_s->Statuses, 12);
  return(0);
}

int read_channel_enable(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d CE", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_int(ret_string, " ", HV_s->Enables, 12))
    return(1);
  implode_from_int(HV_s->Enable_str, sizeof(HV_s->Enable_str), ",", HV_s->Enables, 12);
  return(0);
}

int set_channel_enable(struct HV_ctrl_slot_struct *HV_s)
{
  char   cmd_string[256];
  char   ret_string[128];       
  char   enable_string[128];
  
  implode_from_int(enable_string, sizeof(enable_string), " ", 
		   HV_s->Enables, 12);
  sprintf(cmd_string, "LD S%d CE %s", HV_s->slot_number, enable_string);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  return(0);
}

int read_HV_status(void)
{
  char   cmd_string[32];
  char   ret_string[32];       

  sprintf(cmd_string, "HVSTATUS");
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  if (sscanf(ret_string, "%*d HVSTATUS %s", ret_string) != 1)
    return(1);
  if (strncmp(ret_string, "HVOFF", 5) == 0)
    HV_sts = 0;
  else if  (strncmp(ret_string, "HVON", 4) == 0)
    HV_sts = 1;
  else
    return(1);
  
  return(0);
}

int hvoff(void)
{
  char   cmd_string[32];
  char   ret_string[32]; 
  int    i;
  int    max_tries = 10;

  for (i = 0; i < max_tries; i++)
    {
      sprintf(cmd_string, "HVOFF");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
      msleep(300);
      read_HV_status();
      if (HV_sts == 0)
	return(0);
      msleep(100);
    }
  return(1);
}

int hvon(void)
{
  char   cmd_string[32];
  char   ret_string[32]; 
  int    i;
  int    max_tries = 10;
  
  for (i = 0; i < max_tries; i++)
    {
      sprintf(cmd_string, "HVON");
      query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
      msleep(300);
      read_HV_status();
      if (HV_sts == 1)
	return(0);
      msleep(100);
    }
  return(1);
  
}


///////////////////////////////////////////////////////////////////////////
///  Interface independent code:

int init_HV_slot_struct(struct HV_slot_struct *HV_s, char *name, int slot_num)
{
  snprintf(HV_s->name, sizeof(HV_s->name), name);
  HV_s->slot_number = slot_num;
  if (read_voltages(HV_s))
    return(1);
  msleep(50);
  if (read_currents(HV_s))
    return(1);
  msleep(50);
  if (read_channel_status(HV_s))
    return(1);
  HV_s->value = MAX_array(HV_s->Statuses, 12);
  HV_s->rate = 0;
  HV_s->time = time(NULL);
  return(0);
}

int init_HV_ctrl_slot_struct(struct HV_ctrl_slot_struct *HV_s, char *name, int slot_num)
{
  snprintf(HV_s->name, sizeof(HV_s->name), name);
  HV_s->slot_number = slot_num;
  HV_s->value = 0;
  HV_s->rate = 0;
  if (read_channel_enable(HV_s))
    return(1);
  msleep(50);
  if (read_demand_voltages(HV_s))
    return(1);
  msleep(50);
  if (read_ramp_rate(HV_s))
    return(1);
  msleep(50);
  if (read_trip_current(HV_s))
    return(1);
  HV_s->time = time(NULL);
  return(0);
}

int write_to_slot(struct HV_ctrl_slot_struct *HV_s)
{
  if (set_demand_voltages(HV_s))
    return(1);
  msleep(50);
  if (set_channel_enable(HV_s))
    return(1);
  msleep(50);
  if (set_ramp_rate(HV_s))
    return(1);
  msleep(50);
  if (set_trip_current(HV_s))
    return(1);
  return(0);
}

int insert_mysql_HV_slot_data(struct HV_slot_struct *HV_s)
{
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = malloc((sizeof(struct sys_message_struct)+256)  * sizeof(char));  
  
  if (query_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  sprintf(query_strng, 
	  "INSERT INTO `sc_sens_%s` ( `time`, `value`, `rate`, `Voltage`, `Current`, `Status`) VALUES ( %d, %f, %f, \"%s\", \"%s\", \"%s\" )",
	  HV_s->name, HV_s->time, HV_s->value, HV_s->rate, 
	  HV_s->Voltage_str, HV_s->Current_str, HV_s->Status_str);
  ret_val += write_to_mysql(query_strng);
  
  free(query_strng);
  
  return(ret_val);
}


int insert_mysql_HV_slot_ctrl_data(struct HV_ctrl_slot_struct *HV_s)
{
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = malloc((sizeof(struct sys_message_struct)+256)  * sizeof(char));  
  
  if (query_strng == NULL)
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  sprintf(query_strng, 
	  "INSERT INTO `sc_sens_%s` ( `time`, `value`, `rate`, `Enable`, `Demand_V`, `Trip_Current`, `Ramp_Rate`) VALUES ( %d, %f, %f, \"%s\", \"%s\", %f, %f )",
	  HV_s->name, HV_s->time, HV_s->value, HV_s->rate, 
	  HV_s->Enable_str, HV_s->Demand_V_str, HV_s->Trip_Current, HV_s->Ramp_Rate);
  ret_val += write_to_mysql(query_strng);
  
  free(query_strng);
  
  return(ret_val);
}

int read_mysql_HV_slot_data(struct HV_slot_struct *HV_s, char *name)
{
  int   ret_val = 0;
  char  query_strng[1024];  

  sprintf(HV_s->name, name);
  sprintf(query_strng, "SELECT `time` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_int(query_strng, &HV_s->time);
  sprintf(query_strng, "SELECT `value` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->value);
  sprintf(query_strng, "SELECT `rate` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->rate);
  sprintf(query_strng, "SELECT `Voltage` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_string(query_strng, HV_s->Voltage_str, sizeof(HV_s->Voltage_str));
  sprintf(query_strng, "SELECT `Current` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_string(query_strng, HV_s->Current_str, sizeof(HV_s->Current_str));
  sprintf(query_strng, "SELECT `Status` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_string(query_strng, HV_s->Status_str, sizeof(HV_s->Status_str));
  explode_to_double(HV_s->Voltage_str, ",", HV_s->Voltages, 12);
  explode_to_double(HV_s->Current_str, ",", HV_s->Currents, 12);
  explode_to_int(HV_s->Status_str, ",", HV_s->Statuses, 12);
  return(ret_val);
}


int read_mysql_HV_slot_ctrl_data(struct HV_ctrl_slot_struct *HV_s, char *name)
{
  int   ret_val = 0;
  char  query_strng[1024];  

  sprintf(HV_s->name, name);
  sprintf(query_strng, "SELECT `time` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_int(query_strng, &HV_s->time);
  sprintf(query_strng, "SELECT `value` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->value);
  sprintf(query_strng, "SELECT `rate` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->rate);
  sprintf(query_strng, "SELECT `Enable` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_string(query_strng, HV_s->Enable_str, sizeof(HV_s->Enable_str));
  sprintf(query_strng, "SELECT `Demand_V` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_string(query_strng, HV_s->Demand_V_str, sizeof(HV_s->Demand_V_str));
  sprintf(query_strng, "SELECT `Trip_Current` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->Trip_Current);
  sprintf(query_strng, "SELECT `Ramp_Rate` FROM `sc_sens_%s` ORDER BY `time` DESC LIMIT 1", name);
  ret_val += read_mysql_double(query_strng, &HV_s->Ramp_Rate);
  explode_to_int(HV_s->Enable_str, ",", HV_s->Enables, 12);
  explode_to_double(HV_s->Demand_V_str, ",", HV_s->Demand_Vs, 12);
  return(ret_val);
}


int read_status(struct inst_struct *i_s)
{       
  struct sys_message_struct sys_message_struc;
  
  if (read_HV_status())
    return(1);
  
  if ((Flt_sts == 1 || Intlck_sts == 1 ) && (has_error == 0))
    {
      has_error = 1;
      sprintf(sys_message_struc.ip_address, " ");
      sprintf(sys_message_struc.subsys, i_s->subsys);
      if (Flt_sts)
	sprintf(sys_message_struc.msgs, "HV Supply: %s (%s) has tripped.", 
		i_s->description, i_s->name);
      else if (Intlck_sts)
	sprintf(sys_message_struc.msgs, "HV Supply: %s (%s) has open interlock.", 
		i_s->description, i_s->name);
      sprintf(sys_message_struc.type, "Alarm");
      sys_message_struc.is_error = 1;
      insert_mysql_system_message(&sys_message_struc);
      hvoff();
    }
  return(0);
}

int check_channel_status(struct HV_slot_struct *HV_s, struct sensor_struct *s_s)
{
  struct sys_message_struct sys_message_struc;
  int i;

  for (i=0; i<12; i++)
    if (( (HV_s->Statuses)[i] > 12) && (s_s->alarm_tripped == 0))
      {
	///  some sort of trip has occurred
	sprintf(sys_message_struc.msgs, "Channel: %d, Slot: %d on HV crate supply has tripped.", i, HV_s->slot_number);
	sprintf(sys_message_struc.type, "Alert");
	sys_message_struc.is_error = 0;
	insert_mysql_system_message(&sys_message_struc);
	s_s->alarm_tripped = 1;
      }
    else if  (( (HV_s->Statuses)[i] < 12) && (s_s->alarm_tripped == 1))
      s_s->alarm_tripped = 0;
  return(0);
}

///////////////////////////////////////////////////////////////////////////////////////////
///  Sensor system required code:

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  struct sensor_struct *s_s;
  struct HV_ctrl_slot_struct *HV_s;
  int   i;
  
  HV_s = malloc(sizeof(struct HV_ctrl_slot_struct));
  if (HV_s == NULL)		
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  

  comm_type = TYPE_ETH;
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
    }
    
  msleep(500);
  
  if (read_status(i_s))
    {
      fprintf(stderr, "read_status failed!\n");
      exit(1);
    }

  for(i=0; i < i_s->num_active_sensors; i++)
    {
      s_s = &s_s_a[i];
      if (!(is_null(s_s->name)))  
	{
	  s_s->data_type = ARRAY_DATA; 
	  if (s_s->settable)
	    {    
	      if ((strncmp(s_s->subtype, "Det", 3) == 0) || (strncmp(s_s->subtype, "Veto", 3) == 0))
		{
		  init_HV_ctrl_slot_struct(HV_s, s_s->name, s_s->num);
		  insert_mysql_HV_slot_ctrl_data(HV_s);
		  s_s->new_set_time = time(NULL);
		  s_s->last_set_time = s_s->new_set_time;
		}
	      else if (strncmp(s_s->subtype, "Master", 1) == 0)
		{
		  insert_mysql_sensor_data(s_s->name, time(NULL), HV_sts, 0);
		  s_s->new_set_time = time(NULL);
		  s_s->last_set_time = s_s->new_set_time;
		}
	    }
	}
    }
  free(HV_s);
  return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)   
{
  shutdown(inst_dev, SHUT_RDWR);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  // Reads out all the relevant 
  struct HV_slot_struct *HV_s;
  HV_s = malloc(sizeof(struct HV_slot_struct));
  if (HV_s == NULL)		
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  if (read_status(i_s))
    {
      free(HV_s);
      return(1);
    }
  msleep(50);
  if  ((strncmp(s_s->subtype, "Det", 3) == 0) || (strncmp(s_s->subtype, "Veto", 3) == 0))
    {
      if (init_HV_slot_struct(HV_s, s_s->name, s_s->num))
	{
	  free(HV_s);
	  return(1);
	}
      if (insert_mysql_HV_slot_data(HV_s))
	{
	  free(HV_s);
	  return(1);
	}
      check_channel_status(HV_s, s_s);
      free(HV_s);
    }
  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      free(HV_s);
      return(1);
    }
  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{ 
  struct HV_ctrl_slot_struct *HV_s;
  HV_s = malloc(sizeof(struct HV_ctrl_slot_struct));
  if (HV_s == NULL)		
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  
  if (strncmp(s_s->subtype, "Master", 1) == 0)  // Master  On/Off
    {
      if (read_status(i_s) == 1)
	{
	  free(HV_s);
	  return(1);
	}
      msleep(50);
      if (s_s->new_set_val < 0.5 )  // turn off
	{
	  free(HV_s);
	  if(hvoff() == 0)
	    return(0);
	  return(1);
	}
      else if  ((HV_sts == 0) && (has_error == 0))       // turn on
	{
	  free(HV_s);
	  if (hvon() == 0)
	    return(0);
	  return(1);
	}
      free(HV_s);
      return(0);
    }
  else if ((strncmp(s_s->subtype, "Det", 3) == 0) || (strncmp(s_s->subtype, "Veto", 3) == 0))
    {
      HV_s->slot_number = s_s->num;
      if (read_mysql_HV_slot_ctrl_data(HV_s, s_s->name))
	{
	  free(HV_s);
	  return(1);
	}
      if (write_to_slot(HV_s))
	{
	  free(HV_s);
	  return(1);
	}
    }
  else
    {
      fprintf(stderr, "Wrong subtype for %s \n", s_s->name);
      free(HV_s);
      return(1);
    }
  return(0);	    
}



#include "main.h"




