/*


*/
#include <stdint.h>
#include "SparkFunBME280.h"

#include "Wire.h"
#include "SPI.h"

#include <Ethernet.h>


BME280 pthSensor_A;   // 0x77   -- default
BME280 pthSensor_B;   // 0x76


// Enter a MAC address and IP address for your controller below.
// The IP address will be dependent on your local network:
byte mac[] = {
  0x90, 0xA2, 0xDA, 0x10, 0xD6, 0x67
};
IPAddress ip(192, 168, 1, 2);

// Initialize the Ethernet server library
// with the IP address and port you want to use
EthernetServer server(5000);

char ret_string[128];
int  buff_pos = 0;

void setup()
{
  //pinMode(53, OUTPUT);
  //pinMode(4, OUTPUT);
  //digitalWrite(4, HIGH);


  pthSensor_A.settings.commInterface = I2C_MODE;
  pthSensor_A.settings.I2CAddress = 0x77;
  pthSensor_A.settings.runMode = 3;
  pthSensor_A.settings.tStandby = 0;

  pthSensor_A.settings.filter = 0;
  pthSensor_A.settings.tempOverSample = 1;
  pthSensor_A.settings.pressOverSample = 1;
  pthSensor_A.settings.humidOverSample = 1;

  delay(10);
  pthSensor_A.begin();
  delay(100);


  pthSensor_B.settings.commInterface = I2C_MODE;
  pthSensor_B.settings.I2CAddress = 0x76;
  pthSensor_B.settings.runMode = 3;
  pthSensor_B.settings.tStandby = 0;

  pthSensor_B.settings.filter = 0;
  pthSensor_B.settings.tempOverSample = 1;
  pthSensor_B.settings.pressOverSample = 1;
  pthSensor_B.settings.humidOverSample = 1;

  delay(10);
  pthSensor_B.begin();
  delay(100);


  // start the Ethernet connection and the server:
  Ethernet.begin(mac, ip);
  server.begin();


  Serial.begin(9600);

  delay(200);
  Serial.write("M 1\r\n");
  delay(100);
  while (Serial.available() > 0)
  {
    char c = Serial.read();
  }
}


int read_O2_Sensor(unsigned int sensor, char *out_string)
{
  Serial.write("M 1\r\n");
  delay(100);
  while (Serial.available() > 0)
  {
    char c = Serial.read();
  }

  delay(100);

  if (sensor == 1)           // partial pressure
    Serial.write("O\r\n");
  else if (sensor == 2)      // percent O2
    Serial.write("%\r\n");
  else if (sensor == 3)   //  temperature
    Serial.write("T\r\n");
  else if (sensor == 4)   // barometric pressure
    Serial.write("P\r\n");
  else
    return (-1);

  delay(50);

  int ret_i;
  char ret_s[8];
  char ret_s2[8];

  char serial_string[32];
  int  serial_pos = 0;
  boolean done_read = false;
  while (Serial.available() > 0)
  {
    char c = Serial.read();
    if (c == '\n')
    {
      serial_string[serial_pos] = '\0';
      done_read = true;
      serial_pos = 0;
    }
    else
    {
      serial_string[serial_pos] = c;
      serial_pos++;
    }
  }

  if (done_read)
  {
    if (sscanf(serial_string, "%*s %[^.].%s\r", ret_s, ret_s2) == 2)
    {
      sprintf(out_string, "%s.%s", ret_s, ret_s2);
      return (0);
    }
    else if (sscanf(serial_string, "%*s %s\r", out_string) == 1)
      return (0);
    //else if (sscanf(serial_string, "%*s %d\r", &ret_i) == 1)
    //return ((float)ret_i);
    else
      return (-2);
  }
  return (-3);
}

void loop()
{
  unsigned int channel;
  int value = 0;
  int ret_status = 0;
  char value_string[16];

  boolean done_read = false;

  // listen for incoming clients
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
      if (sscanf(ret_string, "RA %d", &channel) == 1)
      {
        if (channel < 0 || channel > 6)
          client.println(-1);
        else
        {
          value = analogRead(channel);
          client.println(value);
        }
      }
      else if (sscanf(ret_string, "RD %d", &channel) == 1)
      {
        if (channel < 2 || channel > 9)
          client.println(-1);
        else
        {
          pinMode(channel, INPUT);
          value = digitalRead(channel);
          client.println(value);
        }
      }

      else if (strncmp(ret_string, "RDT_B", 5) == 0)
      {
        client.println(pthSensor_B.readTempC());
      }
      else if (strncmp(ret_string, "RDP_B", 5) == 0)
      {
        client.println(pthSensor_B.readFloatPressure()/100.);
      }
      else if (strncmp(ret_string, "RDH_B", 5) == 0)
      {
        client.println(pthSensor_B.readFloatHumidity());
      }
      else if (strncmp(ret_string, "RDT", 3) == 0)
      {
        client.println(pthSensor_A.readTempC());
      }
      else if (strncmp(ret_string, "RDP", 3) == 0)
      {
        client.println(pthSensor_A.readFloatPressure()/100.);
      }
      else if (strncmp(ret_string, "RDH", 3) == 0)
      {
        client.println(pthSensor_A.readFloatHumidity());
      }
      else if (sscanf(ret_string, "O2 %d", &channel) == 1)
      {
        ret_status = read_O2_Sensor(channel, value_string);
        if (ret_status == 0)
          client.println(value_string);
        else
          client.println(ret_status);
      }
      else if (sscanf(ret_string, "WD %d %d", &channel, &value) == 2)
      {
        if (channel < 2 || channel > 9)
          client.println(-1);
        else
        {
          pinMode(channel, OUTPUT);
          if (value > 0)
            digitalWrite(channel, HIGH);
          else
            digitalWrite(channel, LOW);
        }
      }
    }
  }
}

