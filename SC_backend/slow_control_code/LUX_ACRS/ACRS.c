/* This is the LUX Automatic Control and Recovery System (ACRS) */
/* using the MOXA 7112 embedded computer system */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "modbus.h"

#include "moxadevice.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "ACRS"

//  One can not set P_MAX above this value in PSIG!
#define P_MAX_MAX 30

// Modbus stuff:
#define SLAVE    0xFF
#define mod_port 502

#include "../ADAM6000/ADAM60XX.h"

///////////////////////////////////////////////////////////////////////////
/// ADAM module constants:

const uint16_t RTD_start_address = 0;
const uint16_t num_RTD = 7;

const uint16_t ADC_start_address = 0;
const uint16_t num_ADC = 8;

const uint16_t DAC_start_address = 10;
const uint16_t num_DAC = 2;

const uint16_t DO_start_address = 16; 
const uint16_t num_DO = 2;

const uint16_t Relay_start_address = 16;
const uint16_t num_Relay = 6;

///////////////////////////////////////////////////////////////////////////
///  Globals used to keep track of the recovery state:

//  File to store the sensors state in, in case DB connection is lost.
char SENSOR_FILE_NAME[128];

// If 1, then attempt to recover all the xenon back to the SRV
int FORCE_RECOVERY = 0;
//  If 1, then als dump the thermosyphon (and potentially do other things not yet thought of)
int CRASH_RECOVERY = 0;
// If 1, then open the valves to reduce the detector pressure
int PRESSURE_REDUCTION = 0;
// If FORCE_REC->new_set_val is set to -1, then disable the automatic control aspects
int ACRS_DISABLED = 0;

// We only want to request that the externally controlled systems (like other valves and the TS system)
// operate once per trigger, not in every loop interation.
int REQUESTED_VALVE_CLOSURE = 0;

// If the ACRS process looses contact with the slow control, 
// then a recovery is forced.  This is the time, in seconds, that 
// it waits for before recovery.
time_t FORCE_REC_MAX_TIME = (int)(60*60*99);
time_t LAST_DB_READ_TIME;      // set this on every successful DB read! { LAST_DB_READ_TIME = time(NULL); }

// Keep track if we can connect to the database, 0 if it's okay, last try time otherwise
time_t DB_CONN = 0;

// Time between re-tring the DB connection, in seconds
time_t DB_RETRY_TIME = 300;

// Percentage of LN2 left before recovery is forced
double FORCE_REC_LN2 = 0.2;

// Percentage of UPS battery  left before recovery is forced
double FORCE_REC_UPS = 0.2;

// Global Error condition set in main loop, 0 is okay, 
// 1 is "frustrated"  ie. ACRS wants to do something but is not allowed
// 9 is "error"  ie. there has been some read problem somewhere.
int GLOBAL_ERROR = 0;

///////////////////////////////////////////////////////////////////////////
///  Define Sensor Structures from the DB list

#define NUM_SENSORS 18
struct sensor_struct *P_DET_1;
struct sensor_struct *P_DET_2;
struct sensor_struct *P_DET_3;
struct sensor_struct *P_SRV_1;
struct sensor_struct *T_SRV_1;
struct sensor_struct *T_SRV_MAX;
struct sensor_struct *FLOW;
struct sensor_struct *F_CTRL;
struct sensor_struct *FORCE_REC;
struct sensor_struct *P_MAX;
struct sensor_struct *ERROR;
struct sensor_struct *TS_CTRL;
struct sensor_struct *SV49;
struct sensor_struct *SV50;
struct sensor_struct *CV01;
struct sensor_struct *CV82;
struct sensor_struct *DV04;
struct sensor_struct *DV06;


//////////////////////////////////////////////////////////
///  Utilities that are only really useful here:


int Vote_on_Vals(double V1, double V2, double V3, double *V_out, double max_dev)
{
  double V_av, V_dev;
    
  V_av = (V1 + V2 + V3)/3.0;
  V_dev = sqrt(( ((V1-V_av)*(V1-V_av)) + ((V2-V_av)*(V2-V_av)) + ((V3-V_av)*(V3-V_av)) ) / (V_av * V_av));
    
  if (V_dev < max_dev)
    {  *V_out = V_av;  return(0); }
    
    
  if (abs(V1 - V2) < max_dev)
    { *V_out = (V1 + V2)/2.0; return(0); }
  if (abs(V1 - V3) < max_dev)
    { *V_out = (V1 + V3)/2.0; return(0); }
  if (abs(V2 - V3) < max_dev)
    { *V_out = (V2 + V3)/2.0; return(0); }
    
  fprintf(stderr, "Vote_on_Vals did not converge.  V1:%f, V2:%f, V3:%f\n", V1, V2, V3);
  *V_out = -1;
  return(1);
}


//////////////////////////////////////////////////////////
///  Hardware dependent code:

double Read_LN2_reserve(void)
{
  //  Returns the percentage of LN2 available to the
  //  recovery system.
  
  //  Not yet implemented, obviously.
  return(1.0);
}

double Read_UPS_reserve(void)
{
  //  Returns the percentage of UPS reserve available to the
  //  recovery system.
 
  //  Not yet implemented, obviously.
  return(1.0);
}


int Set_Flow_Rate(void)
{
  return(write_DAC_i(F_CTRL, DAC_start_address, num_DAC, F_CTRL->mb_inst_dev));
}


int Set_Pressure_Reduction_Valves(void)
{
  int ret_val = 0;
  
  if (PRESSURE_REDUCTION)
    {
      SV49->new_set_val = 1;  // Open all
      SV50->new_set_val = 1;
      CV01->new_set_val = 1;
      DV04->new_set_val = 0;   // This is a normally open valve!
      DV06->new_set_val = 1;
      
      
      ret_val += write_DO_i(CV01, Relay_start_address,  num_Relay, CV01->mb_inst_dev);
      ret_val += write_DO_i(DV04, Relay_start_address,  num_Relay, DV04->mb_inst_dev);
      ret_val += write_DO_i(DV06, Relay_start_address,  num_Relay, DV06->mb_inst_dev);
    }
  else
    {
      SV49->new_set_val = 0;  // Close all
      SV50->new_set_val = 0;
    }

  ret_val += write_DO_i(SV49, Relay_start_address,  num_Relay, SV49->mb_inst_dev);
  ret_val += write_DO_i(SV50, Relay_start_address,  num_Relay, SV50->mb_inst_dev);
    
  return(ret_val);
}

int Set_Forced_Recovery_Valves(void)
{
  int ret_val = 0;
  
  if (FORCE_RECOVERY)
    {
      DV04->new_set_val = 0; // Open since this is a Normally Open valve
      DV06->new_set_val = 0; // Close 
      CV82->new_set_val = 0; // Close
      
      ret_val += write_DO_i(DV04, Relay_start_address,  num_Relay, DV04->mb_inst_dev);
      ret_val += write_DO_i(DV06, Relay_start_address,  num_Relay, DV06->mb_inst_dev);
      ret_val += write_DO_i(CV82, Relay_start_address,  num_Relay, CV82->mb_inst_dev);

      if ((DB_CONN == 0) && (REQUESTED_VALVE_CLOSURE == 0))
	{
	  insert_mysql_sensor_data("Circ_Valve_2", time(NULL), 0, 0);
	  insert_mysql_sensor_data("Circ_Valve_3", time(NULL), 0, 0);
	  insert_mysql_sensor_data("Circ_Valve_4", time(NULL), 0, 0);
	  insert_mysql_sensor_data("Circ_Valve_6", time(NULL), 0, 0);
	  REQUESTED_VALVE_CLOSURE = 1;
	}
    }
  
  return(ret_val);
}


int Dump_TS(void)
{
  int ret_val = 0;
  
  return(ret_val);
}
///////////////////////////////////////////////////////////////////////////
///  Interface independent code:

int Find_Sensor_Structs(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  int i;
  int Found_Sensors[NUM_SENSORS];
  init_int_array(Found_Sensors, NUM_SENSORS, 0);

  struct sensor_struct *this_sensor_struc;
  
  for(i=0; i < i_s->num_active_sensors; i++)
    {
      this_sensor_struc = &s_s_a[i];
      if (!(is_null(this_sensor_struc->name)))
	{
	  if (strncmp(this_sensor_struc->user1, "P_DET_1", 7) == 0)
	    {  P_DET_1 = &s_s_a[i];    Found_Sensors[0] = 1;  }

	  if (strncmp(this_sensor_struc->user1, "P_DET_2", 7) == 0)
	    {  P_DET_2 = &s_s_a[i];    Found_Sensors[1] = 1;}

	  if (strncmp(this_sensor_struc->user1, "P_DET_3", 7) == 0)
	    {  P_DET_3 = &s_s_a[i];    Found_Sensors[2] = 1; }

	  if (strncmp(this_sensor_struc->user1, "P_SRV_1", 7) == 0)
	    {  P_SRV_1 = &s_s_a[i];    Found_Sensors[3] = 1; }

	  if (strncmp(this_sensor_struc->user1, "T_SRV_1", 7) == 0)
	    {  T_SRV_1 = &s_s_a[i];    Found_Sensors[4] = 1; }	

	  if (strncmp(this_sensor_struc->user1, "T_SRV_MAX", 7) == 0)
	    {  T_SRV_MAX = &s_s_a[i];    Found_Sensors[5] = 1; }	

	  if (strncmp(this_sensor_struc->user1, "FLOW", 4) == 0)
	    {  FLOW = &s_s_a[i];       Found_Sensors[6] = 1; }

	  if (strncmp(this_sensor_struc->user1, "F_CTRL", 6) == 0)
	    {  F_CTRL = &s_s_a[i];     Found_Sensors[7] = 1; }
	    
	  if (strncmp(this_sensor_struc->user1, "FORCE_REC", 9) == 0)
	    {  FORCE_REC = &s_s_a[i];  Found_Sensors[8] = 1; }

	  if (strncmp(this_sensor_struc->user1, "P_MAX", 5) == 0)
	    {  P_MAX = &s_s_a[i];      Found_Sensors[9] = 1;}

	  if (strncmp(this_sensor_struc->user1, "ERROR", 5) == 0)
	    {  ERROR = &s_s_a[i];      Found_Sensors[10] = 1;}
	  
	  if (strncmp(this_sensor_struc->user1, "TS_CTRL", 7) == 0)
	    {  TS_CTRL = &s_s_a[i];      Found_Sensors[11] = 1;}

	  if (strncmp(this_sensor_struc->user1, "SV49", 4) == 0)
	    {  SV49 = &s_s_a[i];         Found_Sensors[12] = 1;  }

	  if (strncmp(this_sensor_struc->user1, "SV50", 4) == 0)
	    {  SV50 = &s_s_a[i];         Found_Sensors[13] = 1;  }
	    
	  if (strncmp(this_sensor_struc->user1, "CV01", 4) == 0)
	    {  CV01 = &s_s_a[i];         Found_Sensors[14] = 1;  }
	  
	  if (strncmp(this_sensor_struc->user1, "CV82", 4) == 0)
	    {  CV82 = &s_s_a[i];         Found_Sensors[15] = 1;  }
	  
	  if (strncmp(this_sensor_struc->user1, "DV04", 4) == 0)
	    {  DV04 = &s_s_a[i];         Found_Sensors[16] = 1;  }
	    
	  if (strncmp(this_sensor_struc->user1, "DV06", 4) == 0)
	    {  DV06 = &s_s_a[i];         Found_Sensors[17] = 1;  }
	}
    }
  if (sum_int_array(Found_Sensors, NUM_SENSORS) == NUM_SENSORS)
    return(0);
    
  fprintf(stderr, "Find_Sensor_Structs did not find all sensors!:\n");
  if (!(Found_Sensors[0])) 	 fprintf(stderr, "   P_DET_1 not found.\n");
  if (!(Found_Sensors[1]))	 fprintf(stderr, "   P_DET_2 not found.\n");
  if (!(Found_Sensors[2]))	 fprintf(stderr, "   P_DET_3 not found.\n");
  if (!(Found_Sensors[3]))	 fprintf(stderr, "   P_SRV_1 not found.\n");
  if (!(Found_Sensors[4]))	 fprintf(stderr, "   T_SRV_1 not found.\n");
  if (!(Found_Sensors[5]))	 fprintf(stderr, "   T_SRV_MAX not found.\n");
  if (!(Found_Sensors[6])) 	 fprintf(stderr, "   FLOW not found.\n");
  if (!(Found_Sensors[7]))	 fprintf(stderr, "   F_CTRL not found.\n");
  if (!(Found_Sensors[8]))	 fprintf(stderr, "   FORCE_REC not found.\n");
  if (!(Found_Sensors[9]))	 fprintf(stderr, "   P_MAX not found.\n");
  if (!(Found_Sensors[10]))	 fprintf(stderr, "   ERROR not found.\n");
  if (!(Found_Sensors[11]))	 fprintf(stderr, "   TS_CTRL not found.\n");
  if (!(Found_Sensors[12]))	 fprintf(stderr, "   SV49 not found.\n");
  if (!(Found_Sensors[13]))	 fprintf(stderr, "   SV50 not found.\n");
  if (!(Found_Sensors[14]))	 fprintf(stderr, "   CV01 not found.\n");
  if (!(Found_Sensors[15]))	 fprintf(stderr, "   CV82 not found.\n");
  if (!(Found_Sensors[16]))	 fprintf(stderr, "   DV04 not found.\n");
  if (!(Found_Sensors[17]))	 fprintf(stderr, "   DV06 not found.\n");

  return(1);
}


void Check_Rec_State(void)
{
  //  This goes through the various external checks to see if we 
  //  should be in the forced recovery mode.

  FORCE_RECOVERY = 0;
  CRASH_RECOVERY = 0;

  if (FORCE_REC->new_set_val > 0.5)
    {
      FORCE_RECOVERY = 1;
      return;
    }
    
  if (Read_LN2_reserve() < FORCE_REC_LN2)
    {
      FORCE_RECOVERY = 1;
      CRASH_RECOVERY = 1;
      return;
    }
    
  if (Read_UPS_reserve() < FORCE_REC_UPS)
    {
      FORCE_RECOVERY = 1;
      CRASH_RECOVERY = 1;
      return;
    }
    
  if (time(NULL) - LAST_DB_READ_TIME > FORCE_REC_MAX_TIME)
    {
      FORCE_RECOVERY = 1;
      CRASH_RECOVERY = 1;
      return;
    }
  REQUESTED_VALVE_CLOSURE = 0;
}


void ACRS_sensor_loop(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  struct sensor_struct *this_sensor_struc;
  struct sys_message_struct this_sys_message_struc;
  int i, j;
  double sensor_value = 0;
  double rate_place_holder;
  int max_retries = 5;
  int sens_errors = 0;
  
  for(i=0; i < i_s->num_active_sensors; i++)
    {
      this_sensor_struc = &s_s_a[i];
      if (!(is_null(this_sensor_struc->name)))
	{
	  ///////////////////////////////////////////////////////////////////////// change setpoint
	  if (this_sensor_struc->settable) 
	    {
	      if ( (DB_CONN == 0) || (time(NULL) - DB_CONN > DB_RETRY_TIME) )
		{
		  DB_CONN = 0;
		  // Try to read mysql twice.  If first fails, wait 1 sec before trying again.
		  if (read_mysql_sensor_data(this_sensor_struc->name, &this_sensor_struc->new_set_time, 
					     &this_sensor_struc->new_set_val, &rate_place_holder))
		    {
		      msleep(1000);
		      if (read_mysql_sensor_data(this_sensor_struc->name, &this_sensor_struc->new_set_time, 
						 &this_sensor_struc->new_set_val, &rate_place_holder))
			DB_CONN = time(NULL);   // failed the mysql read
		    }
	      
		  if (DB_CONN == 0) // mysql read successful
		    {
		      LAST_DB_READ_TIME = time(NULL);
		      if (this_sensor_struc->new_set_time > this_sensor_struc->last_set_time)
			{
			  this_sensor_struc->last_set_time = this_sensor_struc->new_set_time;	
			  write_sens_struct_file(i_s, s_s_a, SENSOR_FILE_NAME);
			  j = 0;
			  while ( (sens_errors = set_sensor(i_s, this_sensor_struc)) != 0 )
			    {
			      j++;
			      if (j > max_retries)
				{
				  sprintf(this_sys_message_struc.ip_address, "");
				  sprintf(this_sys_message_struc.subsys, this_sensor_struc->type);
				  sprintf(this_sys_message_struc.msgs, "New setpoint: %s = %e could not be set.", 
					  this_sensor_struc->name , this_sensor_struc->new_set_val);
				  sprintf(this_sys_message_struc.type, "Setpoint");
				  this_sys_message_struc.is_error = 1;
				  insert_mysql_system_message(&this_sys_message_struc);
				  break;
				}
			      msleep(2000);
			    }
			  if (sens_errors == 0)
			    {
			      sprintf(this_sys_message_struc.ip_address, "");
			      sprintf(this_sys_message_struc.subsys, this_sensor_struc->type);
			      sprintf(this_sys_message_struc.msgs, "New setpoint: %s = %e.", 
				      this_sensor_struc->name , this_sensor_struc->new_set_val);
			      sprintf(this_sys_message_struc.type, "Setpoint");
			      this_sys_message_struc.is_error = 0;
			      insert_mysql_system_message(&this_sys_message_struc);
			    }
			}
		    }
		}
	    }
	  ////////////////////////////////////////////////////////////// read data and insert into db
	  else 
	    {
	      j = 0;
	      while ( (sens_errors = read_sensor(i_s, this_sensor_struc, &sensor_value)) != 0 )
		{
		  j++;
		  if (j > max_retries)
		    {
		      if (DB_CONN == 0)
			{
			  sprintf(this_sys_message_struc.ip_address, "");
			  sprintf(this_sys_message_struc.subsys, this_sensor_struc->type);
			  sprintf(this_sys_message_struc.msgs, "Sensor %s could not be read.", 
				  this_sensor_struc->name);
			  sprintf(this_sys_message_struc.type, "Error");
			  this_sys_message_struc.is_error = 1;
			  insert_mysql_system_message(&this_sys_message_struc);	
			}
		      break;
		    }
		  msleep(500);
		}
	      if (sens_errors == 0)
		{
		  add_val_sensor_struct(this_sensor_struc, time(NULL), sensor_value);
		  write_temporary_sensor_data(this_sensor_struc);
		  avg_n_vals_sensor_struct(this_sensor_struc, 5, 1, 1);
		  if (this_sensor_struc->next_update_time <= time(NULL))
		    {
		      diff_vals_sensor_struct(this_sensor_struc, NUM_DISC_HIGH, NUM_DISC_LOW);
		      write_sens_struct_file(i_s, s_s_a, SENSOR_FILE_NAME);
		      if (DB_CONN == 0)
			{
			  insert_mysql_sensor_data(this_sensor_struc->name, time(NULL), 
						   this_sensor_struc->avg, this_sensor_struc->rate);
			    
			  read_mysql_sensor_refresh_time(this_sensor_struc);
			}
		      this_sensor_struc->last_update_time = time(NULL);
		      this_sensor_struc->next_update_time = time(NULL) + this_sensor_struc->update_period;
		    }
		}
	    }
	}
      msleep(50);
    }
}


void ACRS_loop(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  int ret_val = 0;
  double P_Det, P_delta;

  ACRS_sensor_loop(i_s, s_s_a);
  
  if (FORCE_REC->new_set_val < -0.5)
    ACRS_DISABLED = 1;
  else
    ACRS_DISABLED = 0;

  //   After reading out the P_MAX setting, check to see how close to the allowable
  //   limit, P_MAX_MAX, and reduce to that if over.
  if (P_MAX->new_set_val > P_MAX_MAX)
    {
      P_MAX->new_set_val = P_MAX_MAX;
      if (DB_CONN == 0)
	insert_mysql_sensor_data(P_MAX->name, time(NULL), P_MAX->new_set_val, 0);
    }
  Check_Rec_State();
  
  // If  Check_Rec_State sets either recovery bits, but ACRS is disabled, 
  // unset them, and set GLOBAL_ERROR to frustrated.
  if (FORCE_RECOVERY || CRASH_RECOVERY)   
    if (ACRS_DISABLED == 1)
      {
	GLOBAL_ERROR = 1;
	FORCE_RECOVERY = 0;
	CRASH_RECOVERY = 0;
      }

  if (CRASH_RECOVERY)
      ret_val += Dump_TS();
  
  ret_val += Set_Forced_Recovery_Valves();

  ret_val += Vote_on_Vals(P_DET_1->avg, P_DET_2->avg, P_DET_3->avg, &P_Det, 0.1);

  P_delta = P_MAX->parm1/100.0 *  P_MAX->new_set_val;

  if ((P_Det > P_MAX->new_set_val) || (FORCE_RECOVERY == 1))
    if (P_Det > P_SRV_1->avg)
      if (P_SRV_1->avg < P_MAX->new_set_val + P_delta)
	if (T_SRV_1->avg < T_SRV_MAX->new_set_val)
	  PRESSURE_REDUCTION = 1;
    
  if ((P_Det < P_MAX->new_set_val - P_delta) && (FORCE_RECOVERY == 0))
    PRESSURE_REDUCTION = 0;
  if (P_Det < P_SRV_1->avg)
    PRESSURE_REDUCTION = 0;
  if (P_SRV_1->avg > P_MAX->new_set_val + P_delta)
    PRESSURE_REDUCTION = 0;
  if (T_SRV_1->avg > T_SRV_MAX->new_set_val)
    PRESSURE_REDUCTION = 0;
    
  // If PRESSURE_REDUCTION ends up being 1, but ACRS is disabled, 
  // set it to 0, and set GLOBAL_ERROR to frustrated.
  if (PRESSURE_REDUCTION)   
    if (ACRS_DISABLED == 1)
      {
	GLOBAL_ERROR = 1;
	PRESSURE_REDUCTION = 0;
      }
  
  if (PRESSURE_REDUCTION)
    ret_val += Set_Flow_Rate();

  if (ACRS_DISABLED == 0)
    ret_val += Set_Pressure_Reduction_Valves();
    
  if (ret_val > 0)
    GLOBAL_ERROR = 9;
  else
    GLOBAL_ERROR = 0;

  msleep(2000);
}


///////////////////////////////////////////////////////////////////////////////////////////
/// Setup, Cleanup and Main:

int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  int i;
  struct sensor_struct *this_sensor_struc;
    
  if ( Find_Sensor_Structs(i_s, s_s_a) )
    exit(1);
    
  for(i=0; i < i_s->num_active_sensors; i++)
    {
      this_sensor_struc = &s_s_a[i];
      if (strncmp(this_sensor_struc->user4, "MODBUS", 6) == 0)
	{
	  modbus_init_tcp(&(this_sensor_struc->mb_inst_dev), this_sensor_struc->user2, mod_port);
    
	  if (modbus_connect(&(this_sensor_struc->mb_inst_dev)) == -1)
	    {
	      fprintf(stderr, "ERROR Modbus connection failed for %s.\n", this_sensor_struc->name);
	      exit(1);
	    }
	}
      if (strncmp(this_sensor_struc->user4, "RMODBUS", 7) == 0)
	{
	  modbus_init_tcp(&(this_sensor_struc->mb_inst_dev), this_sensor_struc->user2, mod_port);
    
	  if (modbus_connect(&(this_sensor_struc->mb_inst_dev)) == -1)
	    this_sensor_struc->inst_dev = 0;
	  else
	    this_sensor_struc->inst_dev = 1;
	}
    }
    
  return(0);

}

void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  return;
}


#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  int ret;

  if (strncmp(s_s->subtype, "ADC", 3) == 0)        
    return(read_ADC_i(s_s, ADC_start_address,  num_ADC, val_out, s_s->mb_inst_dev));
    
  if (strncmp(s_s->subtype, "LLADC", 3) == 0)  
    {      
      ret = read_ADC_i(s_s, ADC_start_address,  num_ADC, val_out, s_s->mb_inst_dev);
      *val_out = pow(10, (double)(*val_out) -5.0);
      return(ret);
    }
    
  if (strncmp(s_s->subtype, "RELAY", 5) == 0)
    return(read_DO_i(s_s, Relay_start_address,  num_Relay, val_out, s_s->mb_inst_dev));
    
  if (strncmp(s_s->subtype, "DO", 2) == 0)
    return(read_DO_i(s_s, DO_start_address,  num_DO, val_out, s_s->mb_inst_dev));

  if (strncmp(s_s->subtype, "TEMP", 4) == 0)  
    {
      ret = read_ADC_raw_i(s_s, RTD_start_address,  num_RTD, val_out, s_s->mb_inst_dev);    
      *val_out = (double)(*val_out) * 400.0/65535.0 - 200 + 273.15; 
      *val_out = s_s->parm1 + (s_s->parm2 * (*val_out)) + (s_s->parm3 * (*val_out)  * (*val_out));
      return(ret);
    }

  if (strncmp(s_s->subtype, "ERROR", 5) == 0)
    {
      *val_out = GLOBAL_ERROR;
      return(0);
    }
 
  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{ 
  if (strncmp(s_s->subtype, "FLOW", 4) == 0)        
    {
      ///  This subtype is used for the flow controllers. Make sure they are never set below
      ///  1 l/min, or they can get stuck open.
      if (s_s->new_set_val < 1)
	{
	  s_s->new_set_val = 1;
	  if (DB_CONN == 0)
	    insert_mysql_sensor_data(s_s->name, time(NULL), s_s->new_set_val, 0);
	}
      return(write_DAC_i(s_s, DAC_start_address, num_DAC, s_s->mb_inst_dev));
    }
  
  if (strncmp(s_s->subtype, "DAC", 3) == 0)        
    return(write_DAC_i(s_s, DAC_start_address, num_DAC, s_s->mb_inst_dev));
  
  if (strncmp(s_s->subtype, "RELAY", 5) == 0)  
    return(write_DO_i(s_s, Relay_start_address,  num_Relay, s_s->mb_inst_dev));
  
  if (strncmp(s_s->subtype, "DO", 2) == 0)  
    return(write_DO_i(s_s, DO_start_address,  num_DO, s_s->mb_inst_dev));

  return(0);
}


int main (int argc, char *argv[])
{
  char                   **my_argv;
  char                   inst_name[16];
  char                   inst_file_name[128];
  int                    i;
  struct inst_struct     this_inst;
  struct sensor_struct   *all_sensors;
    
  int                    swtd_fd;   // The MOXA watchdog file handle
    
  // Open up the watchdog device
  swtd_fd = swtd_open();
    
  // Save restart arguments
  my_argv = malloc(sizeof(char *) * (argc + 1));
  for (i = 0; i < argc; i++) 
    my_argv[i] = strdup(argv[i]);
   
  my_argv[i] = NULL;   
    
  sprintf(db_conf_file, DEF_DB_CONF_FILE);

  parse_CL_for_string(argc,  argv, INSTNAME, inst_name);
    
  sprintf(inst_file_name, "/var/sd/data/%s_inst_file", inst_name);
  sprintf(SENSOR_FILE_NAME, "/var/sd/data/%s_sens_file", inst_name);
    
  if (read_mysql_inst_struct(&this_inst, inst_name))
    {
      DB_CONN = time(NULL);
      if (read_inst_struct_file(&this_inst, inst_file_name))
	{
	  fprintf(stderr, "ERROR read_mysql_inst_struct_file failed for %s.\n", inst_name);
	  exit(1);
	}
      if (read_sens_struct_file(&this_inst, &all_sensors, SENSOR_FILE_NAME))
	{
	  fprintf(stderr, "ERROR read_sens_structs_file failed for %s.\n", inst_name);
	  exit(1);
	}
    }
  else
    {
      DB_CONN = 0;
      if (generate_sensor_structs(&this_inst, &all_sensors))
	{
	  DB_CONN = time(NULL);
	  if (read_sens_struct_file(&this_inst, &all_sensors, SENSOR_FILE_NAME))
	    {
	      fprintf(stderr, "ERROR read_sens_structs_file failed for %s.\n", inst_name);
	      exit(1);
	    }  
	}
      write_inst_struct_file(&this_inst, inst_file_name);
      write_sens_struct_file(&this_inst, all_sensors, SENSOR_FILE_NAME);
    }
    

  //  FIXME! We probably want to keep tracks up this between re-starts, so write to disk...
  LAST_DB_READ_TIME = time(NULL);   


  // Detach current process
  daemonize(this_inst.name);
    
  my_signal = 0;
  // ignore these signals 
  signal(SIGINT, SIG_IGN);
  signal(SIGQUIT, SIG_IGN);    
  // install signal handler
  signal(SIGHUP, handler);
  signal(SIGINT, handler);
  signal(SIGQUIT, handler);
  signal(SIGTERM, handler);

  unregister_inst(&this_inst);
  register_inst(&this_inst);

  set_up_inst(&this_inst, all_sensors);
  if (DB_CONN == 0)
    mysql_inst_run_status(&this_inst);

  fflush(stdout);
  fflush(stderr);

  // Enable the MOXA watchdog
  swtd_enable(swtd_fd, 60000);
    
  while (my_signal == 0)     //  main loop here!
    {
      ACRS_loop(&this_inst, all_sensors);
      if (DB_CONN == 0)
	mysql_inst_run_status(&this_inst);
      else
	{
	  fflush(stdout);
	  fflush(stderr);
	}
      swtd_ack(swtd_fd);
    }
    
  /////////////  Clean up if we get a signal
  clean_up_inst(&this_inst, all_sensors);
   
  unregister_inst(&this_inst);
  free(all_sensors);
    
  if (my_signal == SIGHUP)         ///  restart called
    {
      swtd_enable(swtd_fd, 100);
      sleep(200);

      long fd;
      // close all files before restart
      for (fd = sysconf(_SC_OPEN_MAX); fd > 2; fd--) 
	{
	  int flag;
	  if ((flag = fcntl(fd, F_GETFD, 0)) != -1)
	    fcntl(fd, F_SETFD, flag | FD_CLOEXEC);
	}
      sleep(2);
      execv(my_argv[0], my_argv);
      error("execv() failed: %s", strerror(errno));
      exit(1);
    }

  for (i = 0; my_argv[i] != NULL; i++)
    free(my_argv[i]);
    
  free(my_argv);
    
  swtd_disable(swtd_fd);
  swtd_close(swtd_fd);

  exit(0);
}




/*
  Valves:

  CV1   ACRS  192.168.99.14, #2    "Pred_1_VOp"
  CV2   Ext   192.168.54.8,  #2   
  CV3   Ext   192.168.54.8,  #3
  CV4   Ext   192.168.54.8,  #4
  CV6   Ext   192.168.54.7,  #5
  CV82  ACRS  192.168.99.14, #3

  DV4   ACRS  192.168.99.22, #0    "Pred_1_VOp"
  DV6   ACRS  192.168.99.22, #1

  SV49  ACRS  192.168.99.14, #0    "Pred_2_VOp"
  SV50  ACRS  192.168.99.14, #1    "Pred_2_VOp"


*/


