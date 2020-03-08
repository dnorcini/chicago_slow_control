/* Program for controlling the WIENER_MPOD HV crate using the SNMP interface */
/* Modified from the LeCroy1458 code by James Nikkel */
/* Tim Classen */
/* classen2@llnl.gov */

#include <string>
#include <iostream>
#include <sstream> 
#include <cstdio>
#include <cstdlib>

#include "SC_db_interface_raw.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#define INSTNAME "WIENER_MPOD"

int comm_type = -1;

int  HV_sts = 0;       // 1:HV on,           0:HV off
int  Intlck_sts = 0;   // 1:Interlock open,  0:Interlock closes 
int  Flt_sts = 0;      // 1:Some Fault,      0:No Fault 
int  has_error = 0;
const int total_channels = 160;


struct HV_crate_struct {
  char   name[18];          // DB table name
  int    time;              // DB insertion time 
  double value;             // OR of Status
  double rate;              // Not used
  char   Voltage_str[2048];  // MV
  double Voltages[total_channels];
  char   Current_str[2048];  // MC
  double Currents[total_channels]; 
  char   Status_str[2048];   // ST
  int    Statuses[total_channels];
  char   ipaddress[16];       // IP address (not in DB)
};

struct HV_ctrl_struct {
  char   name[18];          // DB table name
  int    time;              // DB change time
  double value;             // Not used
  double rate;              // Not used
  char   Enable_str[512];   // CE
  int    Enables[total_channels];
  char   Demand_V_str[2048]; // DV
  double Demand_Vs[total_channels];
  double Trip_Current;      // TC (apply to all channels in crate)
  double Ramp_Rate;         // RUP+RDN/2 (apply to all channels in crate)
  char   ipaddress[16];       //IP address (not in DB)
};



//////////////////////////////////////////////////////////
///  Utilities that are only really useful here:

int explode_status(char *str_in, char *delim, int* array_out, int num)
{
  char* token;
  char* running = strdup(str_in);
  int   i = 0;
  char temp[6], t1[6], t2[6], combined[6];
  char* tok;

  token = strsep(&running, delim);
  
  while (token)
    {
      if (i<num)
	if (sscanf(token, "%s", temp) > 0){
	  sprintf(t1,"%s",strtok (temp," "));
	  if (tok != NULL)
	    {
	      sprintf(t2,"%s",strtok (NULL," "));
	    }
	  else sprintf(t2,"00");
	  sprintf(combined,"%s%s",t1,t2);
	  array_out[i] = strtoul(combined,0,16); //convert hex value to base 10	      
	  i++;
	}
      token = strsep(&running, delim);
    }
  
  free(running);
  if (i<num)
    return(1);
  return(0);
}

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

int InvalidAddress(int channel){
  if(channel%100>15) return(1);
  if(channel<0 || channel>1015) return(1);
  return(0);
}

int ReadChannel(std::string cmd, std::string output){
    std::string delimiter1 = cmd+".u";
    std::string delimiter2 = " =";
    
    std::string getnum =output.substr(output.find(delimiter1)+delimiter1.length(),output.find(delimiter2));
    
    if(getnum.length()==0){
      std::cerr<<" No channel number found"<<std::endl;
        return -1;
    }
    
    std::stringstream intstring(getnum);

    int retval = 0;
    intstring >> retval;
    return retval;
}

double ReadVoltage(std::string output){
    std::string delimiter1 = "Float: ";
    std::string delimiter2 = " V";
    
    std::string getnum =output.substr(output.find(delimiter1)+delimiter1.length(),output.find(delimiter2));
    
    if(getnum.length()==0){
        std::cerr<<" No voltage found"<<std::endl;
        return -1;
    }
    
    std::stringstream doublestring(getnum);
    
    double retval = 0;
    doublestring >> retval;
    return retval;
}

double ReadCurrent(std::string output){
  std::string delimiter1 = "Float: ";
  std::string delimiter2 = " A";
  
  std::string getnum =output.substr(output.find(delimiter1)+delimiter1.length(),output.find(delimiter2));
  
  if(getnum.length()==0){
    std::cerr<<" No current found"<<std::endl;
    return -1;
  }
  
  std::stringstream doublestring(getnum);
  
  double retval = 0;
  doublestring >> retval;
  return retval;
}

char* ReadStatus(std::string output){
  std::string delimiter1 = " \"";    // with -Oq option
  
  std::string getnum =output.substr(output.find(delimiter1)+delimiter1.length(),output.find(delimiter1));
  
  if(getnum.length()==0){
    std::cerr<<" No status found"<<std::endl;
    return NULL;
  }
  
  return (char*)getnum.c_str();
}

int channel_to_index(int channel){
  int index = 0;
  int tmp = channel%100;
  index = 0.16*(channel - tmp) + tmp;
  if(index < 0 || index >= total_channels) index = -1;
  return index;
}

int index_to_channel(int index){
  int channel = 0;
  int tmp = channel%16;
  channel = 100*(channel-tmp)/16 + tmp;
  if(InvalidAddress(channel)) channel = -1;
  return channel;
}

int status_to_bitstring(char* status, char* bitstring, int len = 0){
  sprintf(bitstring,"");
  for (int i = 0; i < strlen(status); i++){
    switch (status [i]){
    case ' ': break;
    case '0': strcat(bitstring,"0000"); break;
    case '1': strcat(bitstring,"0001"); break;
    case '2': strcat(bitstring,"0010"); break;
    case '3': strcat(bitstring,"0011"); break;
    case '4': strcat(bitstring,"0100"); break;
    case '5': strcat(bitstring,"0101"); break;
    case '6': strcat(bitstring,"0110"); break;
    case '7': strcat(bitstring,"0111"); break;
    case '8': strcat(bitstring,"1000"); break;
    case '9': strcat(bitstring,"1001"); break;
    case 'a': strcat(bitstring,"1010"); break;
    case 'b': strcat(bitstring,"1011"); break;
    case 'c': strcat(bitstring,"1100"); break;
    case 'd': strcat(bitstring,"1101"); break;
    case 'e': strcat(bitstring,"1110"); break;
    case 'f': strcat(bitstring,"1111"); break;
    }
  }
  if(len>0){
    if(strlen(bitstring)>len)
      return(1);
    if(strlen(bitstring)<len){
      for(int i=strlen(bitstring);i<len;i++){
	strcat(bitstring,"0");
      }
    }
  }
  return(0);
}
//////////////////////////////////////////////////////////
///  Hardware dependent code:

int interpret_channel_status(char* bitstring, char* message){
  std::string stats[16];
  sprintf(message," ");

  stats[0] = "outputOn"; // 1 if channel is on
  stats[1] = "outputInhibit";  // external hardware inhibit of the channel
  stats[2] = "outputFailureMinSenseVoltage"; // sense vltage is too low 
  stats[3] = "outputFailureMaxSenseVoltage"; //sense voltage is too high
  stats[4] = "outputFailureMaxTerminalVoltage"; // terminal voltage is too high
  stats[5] = "outputFailureMaxCurrent"; //current is too high
  stats[6] = "outputFailureMaxTemperature"; // heat sink temperature is too high
  stats[7] = "outputFailureMaxPower"; // output power is too high
  stats[8] = " "; // not used
  stats[9] = "outputFailureTimeout"; // communication timeout
  stats[10] = "outputCurrentLimited"; // current limit is active
  stats[11] = "outputRampUp"; // output voltage is increasing
  stats[12] = "outputRampDown"; // Output voltage is decreasing
  stats[13] = "outputEnableKill"; // EnableKill is active
  stats[14] = "outputEmergencyOff";  // emergency off event is active
  stats[15] = "outputAdjusting"; // fine adjustment is working

  for(int i=0;i<16;i++){
    if(bitstring[1]=='1')
      sprintf(message,"%s ",stats[i].c_str());
  }
  if(bitstring[1] == '1') return(1);
  if(bitstring[2] == '1') return(1);
  if(bitstring[3] == '1') return(1);
  if(bitstring[4] == '1') return(1);
  if(bitstring[5] == '1') return(1);
  if(bitstring[6] == '1') return(1);
  if(bitstring[7] == '1') return(1);
  if(bitstring[9] == '1') return(1);
  if(bitstring[14] == '1') return(1);
  return(0);
}

int interpret_sys_status(char* bitstring){
  std::string stats[8];
  stats[0] = "mainOn"; // 1 if crate is on
  stats[1] = "mainInhibit";  // external hardware inhibit of the crate
  stats[2] = "localControlOnly"; // 
  stats[3] = "inputFailure"; //
  stats[4] = "outputFailure"; // 
  stats[5] = "fanTrayFailure"; //
  stats[6] = "sensorFailure"; // 
  stats[7] = "vmeSysfail"; // 
  if(bitstring[1] == '1') Intlck_sts=1;
  if(bitstring[3] == '1') Flt_sts=1;
  if(bitstring[4] == '1') Flt_sts=1;
  if(bitstring[5] == '1') Flt_sts=1;
  if(bitstring[6] == '1') Flt_sts=1;
  if(bitstring[7] == '1') Flt_sts=1;
  return bitstring[0] - '0';   // returns either 0 or 1 (1 if crate is on)
}

int read_voltages(struct HV_crate_struct *HV_s)
{

  char snmpcmd[512];
  int channel, index;
  std::string cmd = "outputMeasurementSenseVoltage";  // SenseVoltage is the actual voltage of the channel
  sprintf(snmpcmd,"snmpbulkget -v 2c -Cr%3i -OQs -m +WIENER-CRATE-MIB -c public %s %s",total_channels,HV_s->ipaddress,cmd.c_str());
 
  char buffer[128];
    
  FILE* pipe = popen(cmd.c_str(), "r");
  if (!pipe) return(1);
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      channel = ReadChannel(cmd, buffer);
      index = channel_to_index(index);
      if(index==-1)  // error on index
	return(1);
      
      // get voltage value
      HV_s->Voltages[index] = ReadVoltage(buffer);
    }
  }
   
  pclose(pipe);
  implode_from_double(HV_s->Voltage_str, sizeof(HV_s->Voltage_str), ",", 
		      HV_s->Voltages, total_channels, "%.1f");
  return(0);
}


int read_demand_voltages(struct HV_ctrl_struct *HV_s)
{
  /* char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d DV", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", HV_s->Demand_Vs, 12))
    return(1);
  implode_from_double(HV_s->Demand_V_str, sizeof(HV_s->Demand_V_str), ",", 
		      HV_s->Demand_Vs, 12, (char*)"%.1f");
  */
  return(0);
}

int set_demand_voltages(struct HV_ctrl_struct *HV_s)
{
  char snmpcmd[512];
  int channel, retval=0;
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "outputVoltage"; 
  for(int index = 0; index<total_channels; index++){
    if(HV_s->Enables[index]){
      channel = index_to_channel(index);
      if(channel==-1)
	return(1);
      sprintf(snmpcmd,"snmpset -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i F %f",HV_s->ipaddress,cmd.c_str(), channel, HV_s->Demand_Vs[channel]);
    
      pipe = popen(cmd.c_str(), "r");

      while (!feof(pipe)) {
	if (fgets(buffer, 128, pipe) != NULL){
	  result.assign(buffer);
	  if(result.find("Error")){
	    retval++;
	    break;
	  }
	}
      }
    }
  }
  return(retval);
}

int read_currents(struct HV_crate_struct *HV_s)
{

  char snmpcmd[512];
  int channel, index;
  std::string cmd = "outputMeasurementCurrent";  // SenseVoltage is the actual current of the channel
  sprintf(snmpcmd,"snmpbulkget -v 2c -Cr%31 -OQs -m +WIENER-CRATE-MIB -c public %s %s",total_channels, HV_s->ipaddress,cmd.c_str());
 
  char buffer[128];
    
  FILE* pipe = popen(cmd.c_str(), "r");
  if (!pipe) return(1);
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      channel = ReadChannel(cmd, buffer);
      index = channel_to_index(index);
      if(index==-1)  // error on index
	return(1);
      
      // get  value
      HV_s->Currents[index] = ReadCurrent(buffer);
    }
  }
   
  pclose(pipe);
  implode_from_double(HV_s->Current_str, sizeof(HV_s->Current_str), ",", 
		      HV_s->Currents, total_channels, "%.1f");
  return(0);
}

int read_trip_current(struct HV_ctrl_struct *HV_s)
{
  /*  char   cmd_string[32];
  char   ret_string[128];       
  double Currents[12];
  
  sprintf(cmd_string, "RC S%d TC", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_double(ret_string, " ", Currents, 12))
    return(1);
  HV_s->Trip_Current = average_array(Currents, 12, 1);
  */
  return(0);
}

int set_trip_current(struct HV_ctrl_struct *HV_s)
{
  char snmpcmd[512];
  int channel, retval=0;
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "outputCurrent"; 

  for(int index = 0; index<total_channels; index++){
    if(HV_s->Enables[index]){
      channel = index_to_channel(index);
      if(channel==-1)
	return(1);
      sprintf(snmpcmd,"snmpset  -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i F %f",HV_s->ipaddress,cmd.c_str(), channel, HV_s->Trip_Current);   
      pipe = popen(cmd.c_str(), "r");

      while (!feof(pipe)) {
	if (fgets(buffer, 128, pipe) != NULL){
	  result.assign(buffer);
	  if(result.find("Error")){
	    retval++;
	    break;
	  }
	}
      }
    }
  }
  return(retval);
}

int read_ramp_rate(struct HV_ctrl_struct *HV_s)
{
  /*  char   cmd_string[32];
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
  */
  return(0);
}

int set_ramp_rate(struct HV_ctrl_struct *HV_s)
{
  char snmpcmd[512];
  int channel, retval=0;
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "VoltageRiseRate";
  std::string cmd2 = "VoltageFallRate";

  for(int index = 0; index<total_channels; index++){
    if(HV_s->Enables[index]){
      channel = index_to_channel(index);
      if(channel==-1)
	return(1);
      sprintf(snmpcmd,"snmpset  -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i F %f",HV_s->ipaddress,cmd.c_str(), channel, HV_s->Ramp_Rate);
      pipe = popen(cmd.c_str(), "r");
     
      while (!feof(pipe)) {
	if (fgets(buffer, 128, pipe) != NULL){
	  result.assign(buffer);
	  if(result.find("Error")){
	    retval++;
	    break;
	  }
	}
      }
    
      sprintf(snmpcmd,"snmpset  -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i F %f",HV_s->ipaddress,cmd2.c_str(), channel, HV_s->Ramp_Rate);
      pipe = popen(cmd.c_str(), "r");

      while (!feof(pipe)) {
	if (fgets(buffer, 128, pipe) != NULL){
	  result.assign(buffer);
	  if(result.find("Error")){
	    retval++;
	    break;
	  }
	}
      }
    }
  }
  return(retval);
}


int read_channel_status(struct HV_crate_struct *HV_s)
{

  char snmpcmd[512];
  int channel, index;
  char status[16];
  char bitstr[16];
  std::string result;
  
  std::string cmd = "outputStatus";  // given in 1 or 2 hex numbers, to convert to a bit string
  sprintf(snmpcmd,"snmpbulkget  -v 2c -Cr%3i -O0qs -m +WIENER-CRATE-MIB -c public %s %s",total_channels, HV_s->ipaddress,cmd.c_str());  // -Oq simplifies output, good for this field, not helpful for others

  char buffer[128];

  FILE* pipe = popen(cmd.c_str(), "r");
  if (!pipe) return(1);
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      channel = ReadChannel(cmd, buffer);
      index = channel_to_index(index);
      if(index==-1)  // error on index
	return(1);
      
      // get status value
      strcat(HV_s->Status_str, ReadStatus(buffer));
      if (index<total_channels-1) strncat(HV_s->Status_str,",",strlen(HV_s->Status_str));
    }
  }
  explode_status(HV_s->Status_str,",",HV_s->Statuses,total_channels);
  pclose(pipe);
  return(0);
}

int reset_channel(struct HV_crate_struct *HV_s, int channel){
  if(InvalidAddress(channel)) return(1);
  char snmpcmd[512];
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "outputSwitch"; 

  sprintf(snmpcmd,"snmpset -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i i 10",HV_s->ipaddress,cmd.c_str(), channel);
  
  pipe = popen(cmd.c_str(), "r");
  
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      result.assign(buffer);
      if(result.find("Error")){
	return(1);
      }
    }    
  }

  sprintf(snmpcmd,"snmpset -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i i 2",HV_s->ipaddress,cmd.c_str(), channel);
  
  pipe = popen(cmd.c_str(), "r");
  
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      result.assign(buffer);
      if(result.find("Error")){
	return(1);
      }
    }    
  }
  return(0);
}

int reset_all(struct HV_crate_struct *HV_s){
  char snmpcmd[512];
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "groupSwitch.0"; 

  sprintf(snmpcmd,"snmpset -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s i 10",HV_s->ipaddress,cmd.c_str());
  
  pipe = popen(cmd.c_str(), "r");
  
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      result.assign(buffer);
      if(result.find("Error")){
	return(1);
      }
    }    
  }

  sprintf(snmpcmd,"snmpset -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s i 2",HV_s->ipaddress,cmd.c_str());
  
  pipe = popen(cmd.c_str(), "r");
  
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      result.assign(buffer);
      if(result.find("Error")){
	return(1);
      }
    }    
  }
  return(0);
}

int read_channel_enable(struct HV_ctrl_struct *HV_s)
{
  /*  char   cmd_string[32];
  char   ret_string[128];       

  sprintf(cmd_string, "RC S%d CE", HV_s->slot_number);
  query_tcp(inst_dev, cmd_string, strlen(cmd_string)+1, ret_string, sizeof(ret_string));
  sscanf(ret_string, "%*d %*s %*s %[^!]", ret_string);
  if (explode_to_int(ret_string, " ", HV_s->Enables, 12))
    return(1);
  implode_from_int(HV_s->Enable_str, sizeof(HV_s->Enable_str), ",", HV_s->Enables, 12);
  return(0);*/
}

int read_HV_status(struct inst_struct *i_s)
{
  char snmpcmd[512];
  int channel, index;
  char bitstr[16], status[16];
  std::string cmd = "sysStatus.0";  // SenseVoltage is the actual voltage of the channelgiven in 1 or 2 hex numbers, to convert to a bit string
  sprintf(snmpcmd,"snmpget  -v 2c -O0qs -m +WIENER-CRATE-MIB -c public %s %s", i_s->user1,cmd.c_str());  // -Oq simplifies output, good for this field, not helpful for others

  char buffer[128];

  FILE* pipe = popen(cmd.c_str(), "r");
  if (!pipe) return(1);
  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      // get status value
      sprintf(status,"%s",ReadStatus(buffer));
      if(!status_to_bitstring(status, bitstr, 8)) 
	 HV_sts = interpret_sys_status(bitstr);
    }
  }
   
  pclose(pipe);
  return(0);
}

int hvoff(char* ipaddress)
{
  char snmpcmd[512];
  int channel, retval=0;
  char buffer[128];
  std::string result;

  std::string cmd = "groupSwitch";  
  sprintf(snmpcmd,"snmpset  -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.0 i 0",ipaddress,cmd.c_str());

  FILE* pipe = popen(cmd.c_str(), "r");

  while (!feof(pipe)) {
    if (fgets(buffer, 128, pipe) != NULL){
      result.assign(buffer);
      if(result.find("Error")){
	retval++;
	break;
      }
    }
  }
  return(retval);
}

 int hvon(char* ipaddress, struct HV_ctrl_struct *HV_s)
{
  char snmpcmd[512];
  int channel, retval=0;
  char buffer[128];
  std::string result;
  FILE* pipe;

  std::string cmd = "outputSwitch"; 
  for(int index = 0; index<total_channels; index++){
    if(HV_s->Enables[index]){
      channel = index_to_channel(index);
      if(channel==-1)
	return(1);
      sprintf(snmpcmd,"snmpset  -v 2c -OUqv -m +WIENER-CRATE-MIB -c guru %s %s.u%3i i 1",ipaddress,cmd.c_str(), channel);
      pipe = popen(cmd.c_str(), "r");
      
      while (!feof(pipe)) {
	if (fgets(buffer, 128, pipe) != NULL){
	  result.assign(buffer);
	  if(result.find("Error")){
	    retval++;
	    break;
	  }
	}
      }
    }
  }
  return(retval); 
}


///////////////////////////////////////////////////////////////////////////
///  Interface independent code:

int init_HV_crate_struct(struct HV_crate_struct *HV_s, char *name, char* ipaddress)
{
  snprintf(HV_s->name, sizeof(HV_s->name), name);
  sprintf(HV_s->ipaddress,"%s",ipaddress);
  if (read_voltages(HV_s))
    return(1);
  msleep(50);
  if (read_currents(HV_s))
    return(1);
  msleep(50);
  if (read_channel_status(HV_s))
    return(1);
  HV_s->value = MAX_array(HV_s->Statuses, total_channels);
  HV_s->rate = 0;
  HV_s->time = time(NULL);
  return(0);
}

int init_HV_ctrl_struct(struct HV_ctrl_struct *HV_s, char *name)
{
  snprintf(HV_s->name, sizeof(HV_s->name), name);
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

int write_to_crate(struct HV_ctrl_struct *HV_s)
{
  if (set_demand_voltages(HV_s))
    return(1);
  msleep(50);
  if (set_ramp_rate(HV_s))
    return(1);
  msleep(50);
  if (set_trip_current(HV_s))
    return(1);
  return(0);
}

int insert_mysql_HV_crate_data(struct HV_crate_struct *HV_s)
{
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = (char*)malloc((sizeof(struct sys_message_struct)+256)  * sizeof(char));  
  
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


int insert_mysql_HV_ctrl_data(struct HV_ctrl_struct *HV_s)
{
  int ret_val = 0;
  char *query_strng;  
  
  query_strng = (char*)malloc((sizeof(struct sys_message_struct)+256)  * sizeof(char));  
  
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

int read_mysql_HV_crate_data(struct HV_crate_struct *HV_s, char *name)
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
  explode_to_double(HV_s->Voltage_str, ",", HV_s->Voltages, total_channels);
  explode_to_double(HV_s->Current_str, ",", HV_s->Currents, total_channels);
  explode_status(HV_s->Status_str, ",", HV_s->Statuses, total_channels);
  return(ret_val);
}


int read_mysql_HV_ctrl_data(struct HV_ctrl_struct *HV_s, char *name)
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
  explode_to_int(HV_s->Enable_str, ",", HV_s->Enables, total_channels);
  explode_to_double(HV_s->Demand_V_str, ",", HV_s->Demand_Vs, total_channels);
  return(ret_val);
}



//EDIT
int read_status(struct inst_struct *i_s)
{       
  struct sys_message_struct sys_message_struc;
  
  if (read_HV_status(i_s))
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
      hvoff(i_s->user1);
    }
  return(0);
}


//EDIT
int check_channel_status(struct HV_crate_struct *HV_s, struct sensor_struct *s_s)
{
  struct sys_message_struct sys_message_struc;
  int i;
  char bitstr[16];
  char message[1024];
  int status;
  char hexstat[16];
  for (i=0; i<total_channels; i++){
    sprintf(hexstat,"%x",HV_s->Statuses[i]);
    if(status_to_bitstring(hexstat,bitstr,16))
      return(1);
    status = interpret_channel_status(bitstr,message);
    if ((status == 1)  && (s_s->alarm_tripped == 0))
      {
	///  some sort of trip has occurred
	//	sprintf(sys_message_struc.msgs, "Channel: %d, Slot: %d on HV crate supply has tripped.", i, HV_s->slot_number);
	sprintf(sys_message_struc.type, "Alert %s",message);
	sys_message_struc.is_error = 0;
	insert_mysql_system_message(&sys_message_struc);
	s_s->alarm_tripped = 1;
      }
    else if  ((status == 0) && (s_s->alarm_tripped == 1))
      s_s->alarm_tripped = 0;
  }
  return(0);
}
  
    ///////////////////////////////////////////////////////////////////////////////////////////
///  Sensor system required code:

//EDIT
#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
  struct sensor_struct *s_s;
  struct HV_ctrl_struct *HV_s;
  int   i;
  
  HV_s = (HV_ctrl_struct*)malloc(sizeof(struct HV_ctrl_struct));
  if (HV_s == NULL)		
    {
      fprintf(stderr, "Malloc failed\n");
      return(1);
    }
  

  /*  comm_type = TYPE_ETH;
  if ((inst_dev = connect_tcp(i_s)) < 0)
    {
      fprintf(stderr, "Connect failed. \n");
      my_signal = SIGTERM;
      return(1);
      }*/
    
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
	      if ((strncmp(s_s->subtype, "Det", 3) == 0))
		{
		  init_HV_ctrl_struct(HV_s, s_s->name);
		  insert_mysql_HV_ctrl_data(HV_s);
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
  //  shutdown(inst_dev, SHUT_RDWR);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
  // Reads out all the relevant 
  struct HV_crate_struct *HV_s;
  HV_s = (HV_crate_struct*)malloc(sizeof(struct HV_crate_struct));
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
  if  (strncmp(s_s->subtype, "Det", 3) == 0)
    {
      if (init_HV_crate_struct(HV_s, s_s->name, i_s->user1))  // user1 is the ipaddress of the MPOD crate
	{
	  free(HV_s);
	  return(1);
	}
      if (insert_mysql_HV_crate_data(HV_s))
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
  struct HV_ctrl_struct *HV_s;
  HV_s = (HV_ctrl_struct*)malloc(sizeof(struct HV_ctrl_struct));
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
	  if(hvoff(i_s->user1) == 0)
	    return(0);
	  return(1);
	}
      else if  ((HV_sts == 0) && (has_error == 0))       // turn on
	{
	  if (read_mysql_HV_ctrl_data(HV_s, s_s->name))
	    {
	      free(HV_s);
	      return(1);
	    }
	  if (hvon(i_s->user1, HV_s) == 0)
	    return(0);
	  return(1);
	}
      free(HV_s);
      return(0);
    }
  else if (strncmp(s_s->subtype, "Det", 3) == 0)
    {
      if (read_mysql_HV_ctrl_data(HV_s, s_s->name))
	{
	  free(HV_s);
	  return(1);
	}
      if (write_to_crate(HV_s))
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




