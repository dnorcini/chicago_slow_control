/*
  Read out of UTI chip
  Arduino ethernet.
*/

#include <SPI.h>
#include <Ethernet.h>


// Enter a MAC address and IP address for your controller below.
// The IP address will be dependent on your local network:
byte mac[] = { 0x90, 0xA2, 0xDA, 0x00, 0x5F, 0x54 };
byte ip[] = {192, 168, 1, 178};

// Initialize the Ethernet server library
// with the IP address and port you want to use
// (port 80 is default for HTTP):
EthernetServer server(5000);

char ret_string[128];
int  buff_pos = 0;

// Define Arduino Pins:
int SEL_1    = 3;
int SEL_2    = 5;
int SEL_3    = 6;
int SEL_4    = 7;

int UTI_PD   = 8;
int UTI_OUT  = 2;

int LED      = 9;

// Global reference capacitor value
float capREF = 100.0;

void SetUTImode(int mode_select)
{
  /* mode selector function,
     enables ouput pins to control whether the selectors are on or off refer to UTI
     manual for other modes and selector pins
     Mode 0 -- 5 cap, 0-2 pF
     Mode 1 -- 3 cap, 0-2 pF
     Mode 2 -- 5 cap, 0-12 pF
     Mode 3 -- External MUX, 0-2 pF
     Mode 4 -- 3 cap, 0-300 pF
     Mode 5 -- Platinum resistor (Pt100-Pt1000) 4 wire
     See UTI manual for the rest...
  */
  int i, j;

  int sel_bits[4];

  if ((mode_select > 15) || (mode_select < 0))
    mode_select = 0;

  for (i = 3; i >= 0; i--)
  {
    j = mode_select >> i;
    if (j & 1)
      sel_bits[i] = 1;
    else
      sel_bits[i] = 0;
  }

  digitalWrite(SEL_1, sel_bits[3] );
  digitalWrite(SEL_2, sel_bits[2] );
  digitalWrite(SEL_3, sel_bits[1] );
  digitalWrite(SEL_4, sel_bits[0] );

}

void TurnOnUTI(void)
{
  digitalWrite(UTI_PD, HIGH);
  delay(1000);
}

void TurnOffUTI(void)
{
  digitalWrite(UTI_PD, LOW);
}


int ReadUTI(float *cap)
{
  /*  Turns UTI chip on, takes times using the 'micros' function
      on the rising and falling edges of the 3 piece signal,
      idetifies each part and assigns them accordingly as
      toff, tx and tref reffering to the UTI manual.
      Sets 'cap' to a float corresponding to the calculated capacitance in pF.
      Returns 1 if good, 0 if there is some error.
  */

  int b = 0; //define array intergers
  int j;
  int i;

  unsigned long start_timer;

  //define array for 'time stamps' need 34 as we need 16 periods, as i=i+2:
  unsigned long width[34];
  unsigned long timep[16];
  float toff = 0;
  float tref = 0;
  float tx = 0; //define periods
  float time1 = 0;
  float time2 = 0;
  float time = 0;


  start_timer = micros();

  width[0] = 0;
  width[33] = 0;

  for (i = 0; i < 33; i += 2)
  {
    while (digitalRead(UTI_OUT) == LOW)
    {
      // takes time at rising edge
      width[i] = micros();
      // exit if it takes too long
      if (width[i] - start_timer > 1000000)
        return (0);
    }

    while  (digitalRead(UTI_OUT) == HIGH)
    {
      // takes time at falling edge
      width[i + 1] = micros();
      // exit if it takes too long.
      if (width[i + 1] - start_timer > 1000000)
        return (0);
    }
  }

  if (width[0] > width[33])
    return (0);

  j = 0;
  for (i = 0; i < 32; i += 2)
  {
    // calculates the time periods, time the board has been
    // running minus previous time, stored in array timep
    timep[j] = width[i + 2] - width[i];
    j++;
  }

  for (i = 0; i < 8; i++)
  {
    if ( (0.95 * timep[i + 1]) <= timep[i]  &&
         timep[i] <= (1.05 * timep[i + 1])  &&
         timep[i] < timep[i + 2]          &&
         timep[i] < timep[i + 3]          &&
         timep[i + 1] < timep[i + 2]        &&
         timep[i + 1] < timep[i + 3])
      // selects the starting position by defining the first period as
      // being the first two smallest periods which lie within 5% of each other
    {
      //picking the offset periods
      toff = timep[i] + timep[i + 1];
      tref = timep[i + 2];
      tx = timep[i + 3];

      *cap = ((tx - toff) / (tref - toff)) * capREF; //UTI 3 signal calculation
      return (1);
    }
  }
  return (0);
}

float ReadCap()
{
  //  Reads out and averages the capacitance value
  //  and returns the capacitance in pF.
  float cap = 0;
  float cap_sum = 0;
  int   i;
  int   max_averages = 10; // Loop five times for an average capacitance
  int   actual_averages = 0;

  TurnOnUTI();
  for (i = 0; i < max_averages; i++)
  {
    if (ReadUTI(&cap))    //function defined above
    {
      cap_sum += cap;
      actual_averages++;
      delay(10);         //wait periods to stop clogging
    }
  }
  TurnOffUTI();

  if (actual_averages > 0)
    return (cap_sum / actual_averages); //only divides by the number of succesful runs
  else
    return (-1);
}


void setup()
{
  pinMode (SEL_1,   OUTPUT);
  pinMode (SEL_2,   OUTPUT);
  pinMode (SEL_3,   OUTPUT);
  pinMode (SEL_4,   OUTPUT);
  pinMode (UTI_PD, OUTPUT);
  pinMode (UTI_OUT, INPUT);
  pinMode (LED,     OUTPUT);
  delay(1000);

  digitalWrite(LED,    LOW);
  digitalWrite(UTI_PD, LOW);

  SetUTImode(4); // Default to high capacitance (300 pf) mode.

  // start the Ethernet connection and the server:
  Ethernet.begin(mac, ip);
  server.begin();
}


void loop()
{
  // listen for incoming clients

  boolean done_read = false;
  unsigned int  mode_val;
  long capacitance;
  float capa;

  EthernetClient client = server.available();

  if (client)
  {
    char c = client.read();

    if (c == '\n')
    {
      ret_string[buff_pos] = '\0';
      done_read = true;
      buff_pos = 0;
    }
    else
    {
      ret_string[buff_pos] = c;
      buff_pos++;
    }

    if (done_read)
    {
      if (strncmp(ret_string, "Get", 3) == 0)
      {
        digitalWrite(LED, HIGH);
        capa = (ReadCap());
        capacitance = (long)(capa * 1000000.0); // sent int of capacitance in microFarads
        client.println(capacitance);
        digitalWrite(LED, LOW);
      }

      else if (sscanf(ret_string, "SetMode %d", &mode_val) == 1)
        SetUTImode(mode_val);
    }
  }
}


