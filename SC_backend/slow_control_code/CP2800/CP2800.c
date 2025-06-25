/* Program for communicating with the CryoMech CP2800 */
/* compressor */
/* James Nikkel */
/* james.nikkel@rhul.ac.uk */
/* Copyright 2012 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include "ethernet.h"

// This is the default instrument entry, but can be changed on the command line when run manually.
// When called with the watchdog, a specific instrument is always given even if it is the same
// as the default. 
#define INSTNAME "CP2800"

int inst_dev;

void escape_chars(unsigned char *input, unsigned char *output, int num_in, int *num_out)
{
  // Add escape character (0x07) to input for 0x02, 0x0D and 0x07
  int i;
  int j = 0;
  
  for (i=0; i < num_in; i++)
    {
      if (input[i] == 0x02)
	{
	  output[j] = 0x07;
	  j++;
	  output[j] = 0x30;
	  j++;
	}
      else if (input[i] == 0x0D)
	{
	  output[j] = 0x07;
	  j++;
	  output[j] = 0x31;
	  j++;
	}
      else if (input[i] == 0x07)
	{
	  output[j] = 0x07;
	  j++;
	  output[j] = 0x32;
	  j++;
	}
      else
	{
	  output[j] = input[i];
	  j++;
	}
    }
  *num_out = j;
}

int rm_escape_chars(unsigned char *input, unsigned char *output, int num_in, int *num_out)
{
  // Remove escape character (0x07) from input and convert back to 0x02, 0x0D and 0x07
  int i;
  int j = 0;
  
  for (i=0; i < num_in; i++)
    {
      if (input[i] == 0x07)
	{
	  i++;
	  if (input[i] == 0x30)
	    output[j] = 0x02;
	  else if (input[i] == 0x31)
	    output[j] = 0x0D;
	  else if (input[i] == 0x32)
	    output[j] = 0x07;
	  else 
	    {
	      fprintf(stderr, "Bad escape sequence in: %s \n", input);
	      return(1);
	    }
	  j++;
	}
      else if(input[i] == 0x0D)
	{
	  output[j] = '\0';
	  *num_out = j+1;
	  return(0);
	}
      else
	{
	  output[j] = input[i];
	  j++;
	}
    }
  fprintf(stderr, "Didn't get a terminator: %s \n", input);
  return(1);
}


void calc_chksum(unsigned char *input, int num_in, unsigned char *chksum1, unsigned char *chksum2)
{
  unsigned long  LCKSUM = 0;
  unsigned char  CKSUM;
  unsigned char  CKSUM1;
  unsigned char  CKSUM2;
  int i;

  for (i=0; i<num_in; i++)
      LCKSUM += input[i];
  
  CKSUM = LCKSUM  % 256;
  
  CKSUM2 = (CKSUM & 0x0f);  // least significant bits
  CKSUM1 = CKSUM - CKSUM2;  // most significant bits
  CKSUM1 /= 0x10;
  CKSUM1 =  (CKSUM1 & 0x0f);
  CKSUM1 += 0x30;
  CKSUM2 += 0x30;

  //fprintf(stdout, "Long Checksum: %ld, Checksum: %x \n", LCKSUM, CKSUM);

  *chksum1 = CKSUM1;
  *chksum2 = CKSUM2;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

#define _def_set_up_inst
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)    
{
   if ((inst_dev = connect_tcp(i_s)) < 0)
	{
	  fprintf(stderr, "Connect failed. \n");
	  my_signal = SIGTERM;
	  return(1);
	}
   return(0);
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
    close(inst_dev);
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{

  unsigned char  CMD_RSP[8];
  unsigned char  ESC_CMD_RSP[16];
  
  unsigned char  CKSUM1;
  unsigned char  CKSUM2;
  
  unsigned char  ret_string[32];    
  unsigned char  ret_string2[32];    

  unsigned char  hexvals[4];
  char           hex_string[16];

  unsigned long  ret_long = 0;
  int            i;
  int            num_cmd;
  int            num_esc_cmd;
  int            num_ret;

  CMD_RSP[0] = (int)i_s->parm1;  // Address (starts at 16!)
  CMD_RSP[1] = 0x80;             // Command OP CODE
  CMD_RSP[2] = 0x63;

  if (strncmp(s_s->subtype, "Temp", 1) == 0)  // Read out value for temperatures
    {
      if (s_s->num < 0 || s_s->num > 3)
	{
	  fprintf(stderr, "%s: Temperature sensor indices must be between 0 and 3 (inclusive).  Not: %d.\n", s_s->name, s_s->num);
	  return(1);
	}
      
      CMD_RSP[3] = 0x0D;       // 0x0D8F is hash to request system temperatures
      CMD_RSP[4] = 0x8F; 
      CMD_RSP[5] = s_s->num;   // Thermometer number, 0 through 3
      num_cmd = 6;
    }
  else if (strncmp(s_s->subtype, "Press", 1) == 0)  // Read out value for pressures
    {
      if (s_s->num < 0 || s_s->num > 1)
	{
	  fprintf(stderr, "%s: Pressure sensor indices must be between 0 and 1 (inclusive).  Not: %d.\n", s_s->name, s_s->num);
	  return(1);
	}
      
      CMD_RSP[3] = 0xAA;       // 0xAA50 is hash to request system pressures
      CMD_RSP[4] = 0x50; 
      CMD_RSP[5] = s_s->num;   
      num_cmd = 6;
    }
  else if (strncmp(s_s->subtype, "CTRL", 4) == 0)  
    {
      CMD_RSP[3] = 0x5F;      // 0x5F95 is hash to check compressor operation
      CMD_RSP[4] = 0x95; 
      CMD_RSP[5] = 0x00;   
      num_cmd = 6;
    }
  else
    {
      fprintf(stderr, "Control subtype, %s, not recognized. \n", s_s->subtype);
      return(1);
    }
  

  calc_chksum(CMD_RSP, num_cmd, &CKSUM1, &CKSUM2);
      
  num_esc_cmd = sizeof(ESC_CMD_RSP);
  escape_chars(CMD_RSP, ESC_CMD_RSP, num_cmd, &num_esc_cmd); // Properly escape the command sequence

  //fprintf(stdout, "\nChannel: %d on sensor: %s \n", s_s->num, s_s->name);
  //fprintf(stdout, "Sending: ");
  send(inst_dev, &STX, 1, 0);
  //fprintf(stdout, "|%x", STX); 
  for (i=0; i < num_esc_cmd; i++)
    {
      send(inst_dev, &ESC_CMD_RSP[i], 1, 0);
      //fprintf(stdout, "|%x", ESC_CMD_RSP[i]); 
    }
  send(inst_dev, &CKSUM1, 1, 0);
  //fprintf(stdout, "|%x", CKSUM1); 
  send(inst_dev, &CKSUM2, 1, 0);
  //fprintf(stdout, "|%x", CKSUM2); 
  send(inst_dev, &CR, 1, 0);
  //fprintf(stdout, "|%x| \n", CR); 

  msleep(500);
  recv(inst_dev, ret_string, 32, MSG_DONTWAIT);
  if (rm_escape_chars(ret_string, ret_string2, sizeof(ret_string)/sizeof(char), &num_ret))
    return(1);
      
  /*
    fprintf(stdout, "ret_string: ");
    for (i=0; i < 18; i++)
    fprintf(stdout, "|%x", ret_string2[i]);
    fprintf(stdout, "| \n");
  */

  if (ret_string2[0] != 0x02)
    return(1);

  for (i=0; i<sizeof(hexvals); i++)
    hexvals[i] = ret_string2[num_ret - 7 + i];
  
  sprintf(hex_string, "%02x%02x%02x%02x", hexvals[0],  hexvals[1],  hexvals[2], hexvals[3]);
  sscanf(hex_string, "%8x", &ret_long);

  /*
    fprintf(stdout, "hex vals: ");
    for (i=0; i < sizeof(hexvals); i++)
    fprintf(stdout, "|%x", hexvals[i]);
    fprintf(stdout, "| \n");
  */
  
  if ((strncmp(s_s->subtype, "Temp", 1) == 0) || (strncmp(s_s->subtype, "Press", 1) == 0))
    *val_out = (double)ret_long/10.0;  // since the temperature and pressures are returned in tenths.
  else
    *val_out = (double)ret_long;

  //fprintf(stdout, "Val out: %f \n", *val_out);
    
  sleep(1);
  return(0);
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
  
  unsigned char  CMD_RSP[16];
  unsigned char  ESC_CMD_RSP[32];
  
  unsigned char  CKSUM1;
  unsigned char  CKSUM2;

  unsigned char  ret_string[32];    

  int            num_cmd;
  int            num_esc_cmd;

  int            i;

  CMD_RSP[0] = (int)i_s->parm1;  // Address (starts at 16!)
  CMD_RSP[1] = 0x80;             // Command OP CODE
  CMD_RSP[2] = 0x61;
  
  if (strncmp(s_s->subtype, "CTRL", 4) == 0)  // Master On/Off Control
    {
      if (s_s->new_set_val < 0.5) // turn off
	{
	  CMD_RSP[3] = 0xC5;       // 0xC598 is hash to turn off compressor
	  CMD_RSP[4] = 0x98; 
	  CMD_RSP[5] = 0; 
	  CMD_RSP[6] = 0; 
	  CMD_RSP[7] = 0;
	  CMD_RSP[8] = 0;
	  CMD_RSP[9] = 0;
	  num_cmd = 10;
	}
      else  // turn on
	{
	 CMD_RSP[3] = 0xD5;       // 0xD501 is hash to turn on compressor
	 CMD_RSP[4] = 0x01; 
	 CMD_RSP[5] = 0; 
	 CMD_RSP[6] = 0; 
	 CMD_RSP[7] = 0;
	 CMD_RSP[8] = 0;
	 CMD_RSP[9] = 1;
	 num_cmd = 10; 
	}      
    }
  else
    {
      fprintf(stderr, "Control subtype, %s, not recognized. \n", s_s->subtype);
      return(1);
    }
  
  calc_chksum(CMD_RSP, num_cmd, &CKSUM1, &CKSUM2);
      
  num_esc_cmd = sizeof(ESC_CMD_RSP);
  escape_chars(CMD_RSP, ESC_CMD_RSP, num_cmd, &num_esc_cmd); // Properly escape the command sequence

  //fprintf(stdout, "\nChannel: %d on sensor: %s \n", s_s->num, s_s->name);
  //fprintf(stdout, "Sending: ");
  send(inst_dev, &STX, 1, 0);
  //fprintf(stdout, "|%x", STX); 
  for (i=0; i < num_esc_cmd; i++)
    {
      send(inst_dev, &ESC_CMD_RSP[i], 1, 0);
      //fprintf(stdout, "|%x", ESC_CMD_RSP[i]); 
    }
  send(inst_dev, &CKSUM1, 1, 0);
  //fprintf(stdout, "|%x", CKSUM1); 
  send(inst_dev, &CKSUM2, 1, 0);
  //fprintf(stdout, "|%x", CKSUM2); 
  send(inst_dev, &CR, 1, 0);
  //fprintf(stdout, "|%x| \n", CR); 

  msleep(500);
  recv(inst_dev, ret_string, 32, MSG_DONTWAIT);

  sleep(1);
  return(0);
}

#include "main.h"
